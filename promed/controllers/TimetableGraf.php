<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TimetableGraf - работа с расписанием в поликлинике
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      30.11.2009
 *
 */
 
/**
 * Загрузка базового контроллера для работы с расписанием
 */
require_once("Timetable.php");

/**
 * @property TimetableGraf_model $dbmodel
 * @property EvnDirection_model $EvnDirection_model
 */
class TimetableGraf extends Timetable
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->inputRules += array(
			'updatePersonForFerRecord' => array(
				array(
					'default' => '',
					'field' => 'TimetableGraf_id',
					'label' => 'Бирка',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'setPersonInTimetableStac' => array(
				array(
					'default' => '',
					'field' => 'TimetableStac_id',
					'label' => 'Бирка',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getListByDay' => array(
				array(
					'default' => '',
					'field' => 'date_range',
					'label' => 'Период случаев',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'begDate',
					'label' => 'Дата начала периода расписания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Дата окончания периода расписания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Phone_all',
					'label' => 'Телефон',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_BirthDay',
					'label' => 'Дата рождения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Место работы врача',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Режим',
					'rules' => '',
					'type' => 'string',
					'default' => 'polka'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id',
					'default' => ''
				),
				array(
					'field' => 'ElectronicService_id',
					'label' => 'Пункт обслуживания',
					'rules' => '',
					'type' => 'id',
				),
				array(
					'field' => 'showLiveQueue',
					'label' => 'Живая очередь',
					'rules' => '',
					'type' => 'int',
				),
				array(
					'field' => 'MedStaffFactFilterType_id',
					'label' => 'Фильтр списка записанных пациентов',
					'rules' => '',
					'type' => 'id',
				),
			),
			'Apply' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableType_id',
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
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Evn_pid',
					'label' => 'Идентификатор родительного события',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор родительного события',
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
					'field' => 'LpuUnit_did',
					'label' => 'Группа отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_did',
					'label' => 'Идентификатор Мо',
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
					'field' => 'LpuSectionProfile_id',
					'label' => 'Идентификатор профиля отделения',
					'rules' => '',
					'type' => 'id'
				),
				// для подсчета использования расписания
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
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
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ARMType_id',
					'label' => 'Идентификатор типа АРМа',
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
					// кто направил, для записи должности врача
					'field' => 'From_MedStaffFact_id',
					'label' => 'Рабочее место врача',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					// Отвергнуть предупреждение
					'field' => 'OverrideWarning',
					'label' => 'Отвергнуть предупреждение',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnswerQueue',
					'label' => 'Ответ об отмене записи в очередь',
					'rules' => '',
					'type' => 'int'
				),
			),
			'Clear' => array(
				array(
					'field' => 'cancelType',
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
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnComment_Comment',
					'label' => 'Комментарий',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableGrafRecList_id',
					'label' => 'Идентификатор групповой бирки',
					'rules' => '',
					'type' => 'id'
				)
			),
			'setPersonMarkAppear' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonMark_Comment',
					'label' => 'Комментарий',
					'rules' => '',
					'type' => 'string'
				)
			),
			'unsetPersonMarkAppear' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonMark_Comment',
					'label' => 'Комментарий',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getListTimetable' => array(
				array(
					'field' => 'MedService_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'uslugaList',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'Тип подразделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Тип объединения ЛПУ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'mode',
					'label' => 'Вариант/Шаг',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'checkPersonByToday' => array(
				array(
					'field' => 'returnId',
					'label' => 'returnId',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Тип объединения ЛПУ',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор текущего отделения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор текущего места работы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkPersonByFuture' => array(
				array(
					'field' => 'returnId',
					'label' => 'returnId',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Тип объединения ЛПУ',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор текущего отделения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор текущего места работы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'Create' => array(
				array(
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Тип объединения ЛПУ',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор текущего отделения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор текущего места работы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getTopTimetable' => array(
			),
			'getTimetableGrafForEdit' => array(
				array(
					'field' => 'StartDay',
					'label' => 'Дата начала расписания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'CurMedStaffFact_id'
				),
				array(
					'field' => 'PanelID',
					'label' => 'Идентификатор панели на клиенте',
					'rules' => '',
					'type' => 'string',
					'default' => 'TTGSchedulePanel'
				),
				array(
					'field' => 'IsForDirection',
					'label' => 'Вывод расписания для направления?',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'readOnly',
					'label' => 'Только просмотр',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field'	=> 'filterByLpu',
					'label'	=> 'Фильтровать по МО',
					'rules'	=> '',
					'default' => 'true',
					'type'	=> 'string'
				),
				array(
					'field' => 'TimeTableGraf_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				)
			),
			'Delete' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableGrafGroup',
					'label' => 'Группа идентификаторов бирок поликлиники',
					'rules' => '',
					'type' => 'string'
				)
			),
			'ClearDay' => array(
				array(
					'field' => 'Day',
					'label' => 'Идентификатор дня',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Тип подразделения ЛПУ',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'CurMedStaffFact_id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
			),
			'createTTGSchedule' => array(
				array(
					'field' => 'CreateDateRange',
					'label' => 'Даты приёмов',
					'rules' => 'required',
					'type' => 'daterange'
				),
				array(
					'field' => 'CopyToDateRange',
					'label' => 'Вставить в диапазон',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'CurMedStaffFact_id'
				),
				array(
					'field' => 'ScheduleCreationType',
					'label' => 'Тип создания расписания',
					'rules' => '',
					'type' => 'int'
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
					'field' => 'TimeTableGraf_IsMultiRec',
					'label' => 'Флаг множественной записи',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'default' => 100,
					'type' => 'int',
					'field' => 'TimeTableGraf_PersRecLim',
					'label' => 'Максимальное число записанных',
					'rules' => ''
				),
			),
			'addTTGDop' => array(
				array(
					'field' => 'Day',
					'label' => 'Идентификатор дня',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'CurMedStaffFact_id'
				),
				array(
					'field' => 'StartTime',
					'label' => 'Начало приёма',
					'rules' => 'required',
					'type' => 'time'
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
			),
			'addTTSDop' => array(
				array(
					'field' => 'Day',
					'label' => 'Идентификатор дня',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'CurLpuSection_id'
				),
				array(
					'field' => 'StartTime',
					'label' => 'Начало приёма',
					'rules' => 'required',
					'type' => 'time'
				),
				array(
					'field' => 'TimetableExtend_Descr',
					'label' => 'Примечание на бирку',
					'rules' => '',
					'type' => 'string'
				),
			),
			'setTTGType' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableType_id',
					'label' => 'Тип бирки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableGrafGroup',
					'label' => 'Группа идентификаторов бирки',
					'rules' => '',
					'type' => 'string'
				),
			),
			'setTTSType' => array(
				array(
					'field' => 'TimetableStac_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableType_id',
					'label' => 'Тип бирки',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getTTGHistory' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ShowFullHistory',
					'label' => 'Показывать всю историю изменений на время',
					'rules' => '',
					'type' => 'id'
				),
			),
			'getTTDescrHistory' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки поликлиники',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableStac_id',
					'label' => 'Идентификатор койки стационара',
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
					'label' => 'Идентификатор бирки ресурса',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getTTSHistory' => array(
				array(
					'field' => 'TimetableStac_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getTTGForModeration' => array(
				array(
					'field' => 'Person_Surname',
					'label' => 'Фамилия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Firname',
					'label' => 'Имя',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Secname',
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Phone',
					'label' => 'Телефон',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ModerateType_id',
					'label' => 'Тип записи',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'StartDate',
					'label' => 'Дата записи',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'ZapDate',
					'label' => 'На какую дату записан',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'KLCity_id',
					'label' => 'Идентификатор города',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLTown_id',
					'label' => 'Идентификатор нас. пункта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TTGLpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'acceptMultiRecTTGModeration' => array(
				array(
					'field' => 'TimetableGraf_ids',
					'label' => 'Список идентификаторов записей',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'acceptRecTTGModeration' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор записи',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'confirmMultiRecTTGModeration' => array(
				array(
					'field' => 'TimetableGraf_ids',
					'label' => 'Список идентификаторов записей',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Moderator_Comment',
					'label' => 'Текст предупреждения',
					'rules' => '',
					'type' => 'string'
				)
			),
			'confirmRecTTGModeration' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор записи',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Moderator_Comment',
					'label' => 'Текст предупреждения',
					'rules' => '',
					'type' => 'string'
				)
			),
			'failRecTTGModeration' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор записи',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Moderator_Comment',
					'label' => 'Причина отказа',
					'rules' => '',
					'type' => 'string'
				)
			),
			'printPacList' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Day',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'isPeriod',
					'label' => 'флаг периода, либо ресурса',//если лаб. исследование, то указание на то, что дата конца периода есть
					'rules' => '',							//иначе указание на функц. исследование с/без даты
					'type' => 'id'
				),
				array(
					'field' => 'begDate',
					'label' => 'Начало периода',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Начало периода',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'ИД службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор секции ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
			),
			'editTTG' => array(
				array(
					'field' => 'selectedTTG',
					'label' => 'Набор идентификаторов бирок, которые редактируются',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'TimetableType_id',
					'label' => 'Тип бирки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableExtend_Descr',
					'label' => 'Примечание',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ChangeTTGType',
					'label' => 'Изменить тип бирки',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'ChangeTTGDescr',
					'label' => 'Изменить примечание',
					'rules' => '',
					'type' => 'checkbox'
				),
			),
			'getTTGInfo' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор интересующей бирки',
					'rules' => 'required',
					'type' => 'id'
				),
			)
		);
		
		// В конструкторе контроллера сразу открываем хелпер Reg
		$this->load->helper('Reg');
		
		$this->load->database();
		$this->load->model('TimetableGraf_model', 'dbmodel');
		$this->default_db = $this->db->database;
		
		$this->curDT = $this->dbmodel->getFirstRowFromQuery('select dbo.tzGetDate() as date');
		$this->curDT = $this->curDT['date'];
    }

	/**
	 * Получение данных для отображения и открытия самых часто используемых пользователем расписаний 
	 * @param integer $_SESSION['pmUser_id']
	 * @return string JSON-строка
	 * @author       Alexander Permyakov
	 */
	function getTopTimetable() {
		$data = $this->ProcessInputData('getTopTimetable',true,true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getTopTimetable($data);
		$outdata = $this->ProcessModelList($response, true, true)->getOutData();
		
		$response = array('success' => true,'data' => $outdata);
		$this->ReturnData($response);

		return true;
	}

	/**
	 * Создание экстренного посещения врача пациента без записи
	 * Входящие данные: $_POST['MedStaffFact_id'], $_POST['Person_id']
	 * На выходе json-строка.
	 */
	function Create() {
		$data = $this->ProcessInputData('Create', true);
		if ($data === false) { return false; }

		switch ($data['LpuUnitType_SysNick'])
		{
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
				echo json_return_errors('Не указан тип объединения');
				return false;
		}
		$data['TimetableGraf_factTime'] = date('d.m.Y')." ".date('H:i');
		$data['date'] = date('d.m.Y');
		$response = $this->dbmodel->Create($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}
	
	/**
	 * проверка - записан ли такой пациент на сегодня
	 * Входящие данные: $_POST['MedStaffFact_id'], $_POST['Person_id'], $_POST['LpuSection_id'], $_POST['LpuUnitType_SysNick']
	 */
	function checkPersonByFuture() {
		$data = $this->ProcessInputData('checkPersonByToday', true);
		if ($data === false) { return false; }

		switch ($data['LpuUnitType_SysNick'])
		{
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
				echo json_return_errors('Не указан тип объединения');
				return false;
		}
		$response = $this->dbmodel->checkPersonByToday($data);
		if (false === $response)
		{
			echo json_return_errors('Ошибка запроса БД');
			return false;
		}
		if ( is_array($response) && count($response) > 0 ) {
			if (empty($data['returnId']))
			{
				array_walk($response[0], 'ConvertFromWin1251ToUTF8');
				$result = $response[0];
			}
			else
			{
				$result = $response[0][$data['object'].'_id'];
			}
			$this->ReturnData(array('success' => true, 'result' => $result));
		}
		else {
			$this->ReturnData(array('success' => true));
		}
		return true;
	}
	
	/**
	 * проверка - записан ли такой пациент на сегодня
	 * Входящие данные: $_POST['MedStaffFact_id'], $_POST['Person_id'], $_POST['LpuSection_id'], $_POST['LpuUnitType_SysNick']
	 */
	function checkPersonByToday() {
		$data = $this->ProcessInputData('checkPersonByToday', true);
		if ($data === false) { return false; }

		switch ($data['LpuUnitType_SysNick'])
		{
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
				echo json_return_errors('Не указан тип объединения');
				return false;
		}
		$response = $this->dbmodel->checkPersonByToday($data);
		if (false === $response)
		{
			echo json_return_errors('Ошибка запроса БД');
			return false;
		}
		if ( is_array($response) && count($response) > 0 ) {
			if (empty($data['returnId']))
			{
				array_walk($response[0], 'ConvertFromWin1251ToUTF8');
				$result = $response[0];
			}
			else
			{
				$result = $response[0][$data['object'].'_id'];
			}
			$this->ReturnData(array('success' => true, 'result' => $result));
		}
		else {
			$this->ReturnData(array('success' => true));
		}
		return true;
	}
	/**
	 * Расписание на заданную дату
	 */
	function getListByDay() {
		$data = $this->ProcessInputData('getListByDay',true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getListByDay($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Вывод таблицы расписания
	 */
	function getSchedule() 
	{
		$data = $this->ProcessInputData('getListByDay',true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getSchedule($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Расписание. Список ЛПУ
	 */
	function getListTimetableLpu() {
		$this->getListTimetable('Lpu');
	}
	
	/**
	 * Расписание. Список подразделений
	 */
	function getListTimetableLpuUnit() {
		$this->getListTimetable('LpuUnit');
	}
	
	/**
	 * Расписание. Список отделений
	 */
	function getListTimetableLpuSection() {
		$this->getListTimetable('LpuSection');
	}
	
	/**
	 * Расписание. Список врачей
	 */
	function getListTimetableMedPersonal() {
		$this->getListTimetable('MedPersonal');
	}
	
	/**
	 * Расписание. Список служб
	 */
	function getListTimetableMedService() {
		$this->getListTimetable('MedService');
	}
	
	/**
	 * Расписание. Роутинг
	 */
	function getListTimetable($mode) {
		
		$paging = true;
		
		$data = $this->ProcessInputData('getListTimetable',false);
		if ($data === false) { return false; }
		
		switch ($mode)
		{
			case 'Lpu':
				$response = $this->dbmodel->getListTimetableLpu($data);
				break;
			case 'LpuUnit':
				$response = $this->dbmodel->getListTimetableLpuUnit($data);
				$paging = false;
				break;
			case 'LpuSection':
				$response = $this->dbmodel->getListTimetableLpuSection($data);
				break;
			case 'MedPersonal':
				$response = $this->dbmodel->getListTimetableMedPersonal($data);
				break;
			case 'MedService':
				$response = $this->dbmodel->getListTimetableMedService($data);
				break;
		}
		$val = array();
		if ($paging)
		{
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		}
		else
		{
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Создает бирку для незапланированного посещения
	 */
	protected function createUnscheduled(& $data) {
		$response = $this->dbmodel->addTTGUnscheduled($data);
		if (!empty($response['TimetableGraf_id'])) {
			$data['TimetableGraf_id'] = $response['TimetableGraf_id'];
		} else {
			$this->ReturnError("Ошибка создания дополнительной бирки: ".$response['Error_Msg']);
			return false;
		}
		
		return true;
	}
	
	/**
	 * Подсчитывает факт использования расписания
	 */
	protected function countApply($data) {
		if ( (1 != $data['TimetableObject_id'] AND !empty($data['LpuSection_id'])) OR (1 == $data['TimetableObject_id'] AND !empty($data['MedPersonal_id']) AND !empty($data['MedStaffFact_id'])) )
		{
			$response = $this->dbmodel->countApply($data);
			if (isset($response[0]['Error_Msg']))
			{
				$this->ReturnError("Ошибка счетчика записи: ".$response[0]['Error_Msg']);
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Запись человека на бирку поликлиники
	 */
	function Apply() {
		$this->load->model('EvnDirection_model');
		$this->inputRules['Apply'] = array_merge($this->inputRules['Apply'], $this->EvnDirection_model->getSaveRules(array(
			'lpuSectionProfileNotRequired' => true
		)));
		$data = $this->ProcessInputData('Apply',true);
		if ($data === false) {
			return false; 
		}
		$data['Day'] = TimeToDay(time()) ;
		$data['object'] = 'TimetableGraf';
		$data['TimetableObject_id'] = 1;
		
		// Проверка наличия блокирующего примечания
		$this->load->model("Annotation_model", "anmodel");
		$anncheck = $this->anmodel->checkBlockAnnotation($data);		
		if (is_array($anncheck) && count($anncheck)) {
			$this->ReturnData(array (
				'success' => false,
				'Error_Msg' => "Запись на бирку невозможна. См. примечание."
			));
			return false;
		}

		$response = $this->dbmodel->getTTGType($data);

		if (is_array($response) &&
				count($response) &&
				(empty($_SESSION['lpu_id']) || (!empty($_SESSION['lpu_id']) && $response[0]['Lpu_id'] == $_SESSION['lpu_id'])) &&
				$response[0]['TimeTableType_id'] == 8 && !getDocArms()
			) {
			$this->ReturnData([
				'success' => false,
				'Error_Msg' => "Записать на бирку с данным типом могут только врачи своей МО."
			]);
			return false;
		}

		$this->dbmodel->beginTransaction();
		
		do { // обертываем в цикл для возможности выхода при ошибке
		
			if ( !empty($data['Unscheduled']) ) {
				// В случае незапланированного приема создается дополнительная бирка с текущим временем и запись производится на нее
				if ( !$this->createUnscheduled($data) ) {
					$val = array (
						'success' => false
					);
					break;
				}
			}
			// При записи на время из очереди определяем идентификатор направления
			if ( isset($data['EvnQueue_id']) ) {
				$this->load->model("Queue_model", "qmodel");
				$res = $this->qmodel->getDirectionId($data['EvnQueue_id']);
				if ( $res !== false ) {
					$data['EvnDirection_id'] = $res;
				}
			}

			$response = $this->dbmodel->Apply($data);

			if (!isset($response['success'])) {
				if (!empty($response[0]['Error_Msg'])) {
					$this->ReturnError($response[0]['Error_Msg']);
				} else if (!empty($response['Error_Msg'])) {
					$this->ReturnError($response['Error_Msg']);
				} else {
					$this->ReturnError('Ошибка записи на бирку');
				}
				return false;
			}

			if ( $response['success'] ) {
				$data['EvnDirection_id'] = $response['EvnDirection_id'];
				
				$dataModerationAccept = array();
				$dataModerationAccept['Status'] = '1';
				$dataModerationAccept['TimetableGraf_id'] = $response['id'];
				$dataModerationAccept['pmUser_id'] = $data['pmUser_id'];
				$responseModeration = $this->dbmodel->setTimetableGrafModeration($dataModerationAccept);

				$val = array(
					'success' => true,
					'object' => $response['object'],
					'id' => $response['id']
				);

				$val['EvnDirection_id'] = $response['EvnDirection_id'];
				if (!empty($response['EvnDirection_TalonCode'])) {
					$val['EvnDirection_TalonCode'] = $response['EvnDirection_TalonCode'];
				}
				
				// Подсчитаем факт использования расписания
				if ( !$this->countApply($data) ) {
					$val = array (
						'success' => false
					);
					break;
				}

				// Генерируем уведомления
				if ( !$this->genNotice($data) ) {
					$val = array (
						'success' => false
					);
					break;
				}

				$this->dbmodel->commitTransaction();
			} elseif ( isset($response['queue']) ) {
				array_walk($response['queue'], 'ConvertFromWin1251ToUTF8');
				$val = array(
					'success' => false,
					'Person_id' => $response['Person_id'],
					'Server_id' => $response['Server_id'],
					'PersonEvn_id' => $response['PersonEvn_id'],
					'queue' => $response['queue']
				);
				break;
			} elseif ( isset($response['warning']) ) {
				$val = array(
					'success' => false,
					'Person_id' => $response['Person_id'],
					'Server_id' => $response['Server_id'],
					'PersonEvn_id' => $response['PersonEvn_id'],
					'warning' => toUTF($response['warning'])
				);
				break;
			} else {
				$val['success'] = false;
				$val['Error_Msg'] = toUTF($response['Error_Msg']);
				break;
			}
			
		} while (0);

		if ( !$val['success'] ) {
			// если что-то пошло не так, откатываем транзакцию
			$this->dbmodel->rollbackTransaction();
		}
		if(isset($data['EvnDirection_id'])){
			$val['EvnDirection_id']=$data['EvnDirection_id'];
		}
		$this->ReturnData($val);
	}
	
	/**
	 * Генерация уведомления
	 */
	function genNotice($data)
	{
		// Находим инфу о бирке
		$ttgInfo = $this->dbmodel->getTTGInfo($data);
		//var_dump( $ttgInfo ); exit();
		if( !is_array($ttgInfo) || count($ttgInfo) == 0 ) {
			$this->ReturnError('Не удалось отправить уведомление о записи на прием');
			return false;
		}

		$dateFormat = ConvertDateFormat($ttgInfo[0]['TimetableGraf_begTime'], 'd.m.Y H:i');

		$noticeData = array(
			'autotype' => 1
			,'Lpu_rid' => $data['Lpu_id']
			,'MedPersonal_rid' => $ttgInfo[0]['MedPersonal_id']
			,'pmUser_id' => $data['pmUser_id']
			,'type' => 1
			,'title' => 'Запись на прием'
			,'text' => 'Пациент ' .$ttgInfo[0]['Person_Fio']. ' записан на прием на ' .$dateFormat
		);
		$this->load->model('Messages_model', 'Messages_model');
		$noticeResponse = $this->Messages_model->autoMessage($noticeData);

		return true;
	}
	
	/**
	 * Освобождение бирки
	 */
	function Clear() {
		$data = $this->ProcessInputData('Clear',true);
		if ($data === false) { return false; }

		switch ($data['LpuUnitType_SysNick'])
		{
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
				var_dump($data['LpuUnitType_SysNick']);
				$val['success'] = false;
				$val['Error_Msg'] = toUTF("Неверно указаны входящие параметры!");
				$this->ReturnData($val);
				return false;
				break;
		}
		$response = $this->dbmodel->Clear($data);
		
		// Пересчитываем кэш по дню, когда была запись
		// Пересчет теперь прямо в хранимке
		
		if ( $response['success'] ) {
			$val['success'] = true;
		}
		else {
			$val['success'] = false;
			$val['Error_Msg'] = toUTF($response['Error_Msg']);
		}
		$this->ReturnData($val);
	}

	/**
	 * Получение расписания для редактирования в виде чистого HTML
	 */
	function getTimetableGrafForEdit()
	{
		$data = $this->ProcessInputData('getTimetableGrafForEdit', true, true);
		if ($data === false) { return false; }

		$data['forEdit'] = true;
		$response = $this->dbmodel->getTimetableGrafForEdit($data);
		$response['PanelID'] = $data['PanelID'];
		$response['readOnly'] = $data['readOnly'];

		if ( isset($response['success']) && !$response['success']) {
			$this->load->view(
				'reg/timetable_general_error',
				array(
					'Error_Msg' => $response['Error_Msg']
				)
			);
			return;
		}
		
		$this->load->model("MedPersonal_model", "mpmodel");
		$mpresponse = $this->mpmodel->getMedPersonInfoForReg($data);
		$mpresponse['PanelID'] = $data['PanelID'];
		$mpresponse['readOnly'] = $data['readOnly'];
		
		$this->load->model("Annotation_model", "anmodel");
		$mpannotation = $this->anmodel->getRegAnnotation($data);

		$this->load->view(
			'reg/timetable_general_css'
		);
	
		$this->load->view(
			'reg/medstafffact_comment',
			array(
				'data' => $mpannotation
			)
		);
		
		$this->load->view(
			'reg/timetablegraf_general_header',
			array(
				'data' => $response
			)
		);
		
		$response['curDT'] = $this->curDT;
		$response['mpdata'] = $mpresponse; // данные врача: ЛПУ и тип записи
		$response['pmUserData'] = $data;
		$response['PanelID'] = $data['PanelID'];
		
		$this->load->library("TTimetableGraf");
		$this->load->view(
			'reg/timetablegraf_edit_data',
			array(
				'data' => $response
			)
		);
		
		$this->load->view(
			'reg/timetablegraf_edit_footer',
			array(
				'data' => $response
			)
		);
	}
	
	
	/**
	 * Печать расписания в виде таблицы для редактирования
	 */
	function printTimetableGrafForEdit()
	{
		$data = $this->ProcessInputData('getTimetableGrafForEdit', true, true);
		//var_dump($data);
		if ($data) {
			$data['forEdit'] = true;
			$response = $this->dbmodel->getTimetableGrafForEdit($data);
			
			if ( isset($response['success']) && !$response['success']) {
				$this->load->view(
					'reg/timetable_general_error',
					array(
						'Error_Msg' => $response['Error_Msg']
					)
				);
				return;
			}
			
			$this->load->model("MedPersonal_model", "mpmodel");
			$mpresponse = $this->mpmodel->getMedPersonInfoForReg($data);
			$mpresponse['PanelID'] = $data['PanelID'];
			$mpresponse['readOnly'] = $data['readOnly'];
		
			$this->load->model("Annotation_model", "anmodel");
			$mpannotation = $this->anmodel->getRegAnnotation($data);

			$this->load->view(
				'reg/timetable_general_css'
			);
			
			$this->load->view(
				'reg/medstafffact_comment',
				array(
					'data' => $mpannotation
				)
			);
			
			$this->load->view(
				'reg/timetablegraf_general_header',
				array(
					'data' => $response
				)
			);
			
			$response['curDT'] = $this->curDT;
			$response['mpdata'] = $mpresponse; // данные врача: ЛПУ и тип записи
			$response['pmUserData'] = $data;
			$response['PanelID'] = $data['PanelID'];
			
			$this->load->library("TTimetableGraf");
			$this->load->view(
				'reg/timetablegraf_edit_data',
				array(
					'data' => $response
				),
				false, false, (defined('USE_UTF') && USE_UTF)
			);
		} else {
			return false;
		}
	}
	
	
	/**
	 * Удаление бирки поликлиники
	 */
	function Delete() {
		$data = $this->ProcessInputData('Delete', true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->Delete($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}
	
	
	/**
	 * Освобождение дня в поликлинике/параклинике/стационаре
	 */
	function ClearDay() {
		$data = $this->ProcessInputData('ClearDay', true, true);
		if ($data === false) { return false; }
		
		switch ($data['LpuUnitType_SysNick'])
		{
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
				$response = array(
					'Error_Msg' => "Неверно указаны входящие параметры!"
				);
				break;
		}
		$response = $this->dbmodel->ClearDay($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}
	
	
	/**
	 * Создание расписания в поликлинике
	 */
	function createTTGSchedule() {
		$data = $this->ProcessInputData('createTTGSchedule', true, true);

		 
		if ($data === false) { return false; }
		
		If ($data['ScheduleCreationType'] == 1) {
			$response = $this->dbmodel->createTTGSchedule($data);
		} else {
			$response = $this->dbmodel->copyTTGSchedule($data);
		}
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}
	
	
	/**
	 * Добавление дополнительной бирки в поликлинику
	 */
	function addTTGDop() {
		$data = $this->ProcessInputData('addTTGDop', true, true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->addTTGDop($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}

    /**
     * Смена типа бирки в поликлинике
     *
     * @throws Exception
     */
	function setTTGType() {
		$data = $this->ProcessInputData('setTTGType', true, true);
		if ($data === false) {
		    throw new Exception("Отправлены не корректные параметры запроса.");
		}
		$response = $this->dbmodel->setTTGType($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}
	
	
	/**
	 * Получение истории изменения бирки поликлиники
	 */
	function getTTGHistory() {
		
		/**
		 * Обработка результатов
		 */
		function ProcessData($row, $ctrl) {
			// Для интернет пользователей имя оператора делаем гиперссылкой
			If ($row['PMUser_Name'] == "@@inet") {
				if (!empty($row['RecMethodType_id']) && $row['RecMethodType_id'] == "3") {
					$row['PMUser_Name'] = "<a href='#' onClick='getWnd(\"swInetUserInfoWindow\").show({pmUser_id: " . $row['pmUser_id'] . "});'>Запись через мобильное приложение</a>";
				} else {
					$row['PMUser_Name'] = "<a href='#' onClick='getWnd(\"swInetUserInfoWindow\").show({pmUser_id: " . $row['pmUser_id'] . "});'>Запись через интернет</a>";
				}
			}
			return $row;
		}
		
		$data = $this->ProcessInputData('getTTGHistory', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getTTGHistory($data);
		$this->ProcessModelList($response, true, true, $response, 'ProcessData')->ReturnData();
	}
	
	
	/**
	 * Получение истории изменения примечаний по бирке
	 */
	function getTTDescrHistory() {
		$data = $this->ProcessInputData('getTTDescrHistory', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getTTDescrHistory($data);
		$this->ProcessModelList($response, true, true, $response)->ReturnData();
	}
	
	/**
	 * Функция связи идентификатора человека с биркой из фер
	 */
	function updatePersonForFerRecord() {
		$data = $this->ProcessInputData('updatePersonForFerRecord', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->updatePersonForFerRecord($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Функция связи идентификатора человека с биркой
	 */
	function setPersonInTimetableStac() {
		$data = $this->ProcessInputData('setPersonInTimetableStac', true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setPersonInTimetableStac($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}
	
	
	/**
	 * Получение расписания для записи в виде чистого HTML
	 */
	function getTimetableGraf()
	{
		$this->load->model("MedPersonal_model", "mpmodel");
		$this->load->model("Queue_model", "qmodel");
		
		$data = $this->ProcessInputData('getTimetableGrafForEdit', true, true);
		if ($data === false) { return false; }
		
		//Очистка заблокированных бирок
		$this->dbmodel->unlockByUser($data);
		$data['timetable_blocked'] = $this->qmodel->isTimetableBlockedByQueue($data);

		$response = $this->dbmodel->getTimetableGrafForEdit($data);
		$response['PanelID'] = $data['PanelID'];
		$response['readOnly'] = $data['readOnly'];
		if ( isset($response['success']) && !$response['success']) {
			$this->load->view(
				'reg/timetable_general_error',
				array(
					'Error_Msg' => $response['Error_Msg']
				)
			);
			return;
		}
		
		$this->load->view(
			'reg/timetable_general_css'
		);

		$mpresponse = $this->mpmodel->getMedPersonInfoForReg($data);
		$mpresponse['PanelID'] = $data['PanelID'];
		$mpresponse['readOnly'] = $data['readOnly'];
		
		$this->load->model("Annotation_model", "anmodel");
		$mpannotation = $this->anmodel->getRegAnnotation($data);
			
		$this->load->view(
			'reg/medstafffact_comment',
			array(
				'data' => $mpannotation
			)
		);

		$this->load->view(
			'reg/timetablegraf_general_header',
			array(
				'data' => $response
			)
		);
		$response['curDT'] = $this->curDT;
		$response['mpdata'] = $mpresponse; // данные врача: ЛПУ и тип записи
		$response['pmUserData'] = $data;
		$response['PanelID'] = $data['PanelID'];
		$response['timetable_blocked'] = $data['timetable_blocked'];

		if ( !isset($data['IsForDirection']) ) {
			$view = 'reg/timetablegraf_data';
		} else {
			$view = 'reg/timetablegraf_data_dir';
		}
		$this->load->library("TTimetableGraf");
		$this->load->view(
			$view,
			array(
				'data' => $response
			)
		);

		$this->load->model("Queue_model", "qmodel");
		$data['MedStaffFact_sid'] = $data['MedStaffFact_id'];
		$r = $this->qmodel->checkQueueOnFree($data);
		if (false === $r) {
			$response['checkQueue'] = 'true';
		} else {
			$response['checkQueue'] = 'false';
		}

		$view = 'reg/timetablegraf_footer';

		$this->load->view(
			$view,
			array(
				'data' => $response
			)
		);
		
	}
	
	/**
	 * Получение расписания для записи на один день
	 * $ForPrint - вариант для печати?
	 */
	function getTimetableGrafOneDay($ForPrint = false)
	{
		/**
		 * Дополнительная обработка данных
		 * Обрезаем лишнюю часть адреса
		 * Ищем участки у человека в ЛПУ записи
		 */
		function ProcessData($row, $ctrl) {
			if (isset($row['Person_Address'])) {
				$row['Person_Address'] = str_replace('РОССИЯ, ПЕРМСКИЙ КРАЙ, ', '', $row['Person_Address']);
			}
			
		
			// Пытаемся получить список участков обслуживающих человека в заданной ЛПУ
			$ctrl->load->model("Polka_PersonCard_model", "pcmodel");
			
			$row['Regions'] = $ctrl->pcmodel->getPersonRegionList($row['Person_id'], $row['Lpu_id'], isset($row['KLStreet_id']) ? $row['KLStreet_id'] : null, isset($row['Address_House']) ? $row['Address_House'] : null );

			return $row;
		}
			
		$this->load->model("MedPersonal_model", "mpmodel");

		$data = $this->ProcessInputData('getTimetableGrafForEdit', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getTimetableGrafOneDay($data);
		$response['PanelID'] = $data['PanelID'];
		
		if ( isset($response['success']) && !$response['success']) {
			$this->load->view(
				'reg/timetable_general_error',
				array(
					'Error_Msg' => $response['Error_Msg']
				)
			);
			return;
		}
		
		$mpresponse = $this->mpmodel->getMedPersonInfoForReg($data);
		$mpresponse['PanelID'] = $data['PanelID'];
		$mpresponse['readOnly'] = $data['readOnly'];
		
		$this->load->model("Annotation_model", "anmodel");
		$mpannotation = $this->anmodel->getRegAnnotation($data);

		if ( count($mpannotation) ) {
			$this->load->view(
				'reg/medstafffact_comment',
				array(
					'data' => $mpannotation
				),
				false,
				(defined('USE_UTF') && USE_UTF)
			);
		}
		
		if ( isset($response['day_comment']) && count($response['day_comment']) ) {
			$this->load->view(
				'reg/medstafffactday_comment',
				array(
					'data' => $response['day_comment']
				),
				false,
				(defined('USE_UTF') && USE_UTF)
			);
		}
		
		$response['curDT'] = $this->curDT;
		$response['mpdata'] = $mpresponse; // данные врача: ЛПУ и тип записи
		$response['pmUserData'] = $data;
		$response['PanelID'] = $data['PanelID'];
		$response['readOnly'] = $data['readOnly'];
		
		foreach($response['data'] as &$row) {
			$row = ProcessData($row, $this);
		}
		$this->load->library("TTimetableGraf");
		
		if ($ForPrint) {
			$view = 'reg/timetablegrafoneday_print';
		} else {
			$view = 'reg/timetablegrafoneday';
		}
		
		//Ufa, gaf #116387, для ГАУЗ РВФД
		$response['lpu_id'] = $data['Lpu_id'];
		
		$this->load->view(
			$view,
			array(
				'data' => $response,
				'regionNick' => $data['session']['region']['nick']
			),
			false,
			(defined('USE_UTF') && USE_UTF)
		);
	}
	/**
	 * Получение расписания для записи на один день
	 * $ForPrint - вариант для печати?
	 */
	function getTimetableGrafGroup($ForPrint = false)
	{
		/**
		 * Дополнительная обработка данных
		 * Обрезаем лишнюю часть адреса
		 * Ищем участки у человека в ЛПУ записи
		 */
		function ProcessData($row, $ctrl) {
			if (isset($row['Person_Address'])) {
				$row['Person_Address'] = str_replace('РОССИЯ, ПЕРМСКИЙ КРАЙ, ', '', $row['Person_Address']);
			}


			// Пытаемся получить список участков обслуживающих человека в заданной ЛПУ
			$ctrl->load->model("Polka_PersonCard_model", "pcmodel");

			$row['Regions'] = $ctrl->pcmodel->getPersonRegionList($row['Person_id'], $row['Lpu_id'], isset($row['KLStreet_id']) ? $row['KLStreet_id'] : null, isset($row['Address_House']) ? $row['Address_House'] : null );

			return $row;
		}

		$this->load->model("MedPersonal_model", "mpmodel");

		$data = $this->ProcessInputData('getTimetableGrafForEdit', true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getTimetableGrafGroup($data);
		$response['PanelID'] = $data['PanelID'];

		if ( isset($response['success']) && !$response['success']) {
			$this->load->view(
				'reg/timetable_general_error',
				array(
					'Error_Msg' => $response['Error_Msg']
				)
			);
			return;
		}

		$mpresponse = $this->mpmodel->getMedPersonInfoForReg($data);
		$mpresponse['PanelID'] = $data['PanelID'];
		$mpresponse['readOnly'] = $data['readOnly'];

		$this->load->model("Annotation_model", "anmodel");
		$mpannotation = $this->anmodel->getRegAnnotation($data);

		if ( count($mpannotation) ) {
			$this->load->view(
				'reg/medstafffact_comment',
				array(
					'data' => $mpannotation
				),
				false,
				(defined('USE_UTF') && USE_UTF)
			);
		}

		if ( isset($response['day_comment']) && count($response['day_comment']) ) {
			$this->load->view(
				'reg/medstafffactday_comment',
				array(
					'data' => $response['day_comment']
				),
				false,
				(defined('USE_UTF') && USE_UTF)
			);
		}

		$response['curDT'] = $this->curDT;
		$response['mpdata'] = $mpresponse; // данные врача: ЛПУ и тип записи
		$response['pmUserData'] = $data;
		$response['PanelID'] = $data['PanelID'];
		$response['readOnly'] = $data['readOnly'];

		foreach($response['data'] as &$row) {
			$row = ProcessData($row, $this);
		}
		$this->load->library("TTimetableGraf");

		if ($ForPrint) {
			$view = 'reg/timetablegrafoneday_print';
		} else {
			$view = 'reg/timetablegrafoneday';
		}
		//Ufa, gaf #116387, для ГАУЗ РВФД
		$response['lpu_id'] = $data['Lpu_id'];

		$this->load->view(
			$view,
			array(
				'data' => $response,
				'regionNick' => $data['session']['region']['nick']
			),
			false,
			(defined('USE_UTF') && USE_UTF)
		);
	}
	
	/**
	 * Печать расписания для записи на один день
	 */
	function printTimetableGrafOneDay()
	{
		$this->load->view(
			'reg/timetable_general_css'
		);
		
		$this->load->model("MedPersonal_model", "mpmodel");
		$data = array(
			'MedStaffFact_id' => $_GET['MedStaffFact_id'] // напрямую из GET!
		);
		$mpdata = $this->mpmodel->getMedPersonInfoForReg($data);
		$mpdata['date'] = $_GET['StartDay']; // напрямую из GET!
		$this->load->view(
			'reg/timetablegrafoneday_print_header',
			array('data' => $mpdata),
			false,
			(defined('USE_UTF') && USE_UTF)
		);
		$this->getTimetableGrafOneDay(true);
		$this->load->view(
			'reg/timetablegrafoneday_print_footer',
			array(),
			false,
			(defined('USE_UTF') && USE_UTF)
		);
	}
	
	
	/**
	 * Добавление дополнительной койки в стационар
	 */
	function addTTSDop() {
		$data = $this->ProcessInputData('addTTSDop', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->addTTSDop($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}
	
	
	/**
	 * Отметка, что человек явился
	 */
	function setPersonMarkAppear() {
		$data = $this->ProcessInputData('setPersonMarkAppear', true, true);
		if ($data === false) { return false; }
		$data['PersonMark_Status'] = 1; // ставим статус явки
		if ( !isset($data['PersonMark_Comment']) ) {
			$data['PersonMark_Comment'] = null;
		}
		$response = $this->dbmodel->setPersonMark($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}
	
	/**
	 * Отметка, что явка человека отменена
	 */
	function unsetPersonMarkAppear() {
		$data = $this->ProcessInputData('unsetPersonMarkAppear', true, true);
		if ($data === false) { return false; }
		$data['PersonMark_Status'] = 0; // убираем статус явки
		if ( !isset($data['PersonMark_Comment']) ) {
			$data['PersonMark_Comment'] = null;
		}
		$response = $this->dbmodel->setPersonMark($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}
	
	/**
	 * Поиск интернет-записи для модерации
	 */
	function getTTGForModeration() {
		$data = $this->ProcessInputData('getTTGForModeration', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getTTGForModeration($data);
		
		foreach ($response['data'] as &$data) {
			$data['LpuRegion_Name_Pr'] = $this->findLpuAddressRegions($data);
		}
		$this->ProcessModelMultiList($response, true, true, $response)->ReturnData();
	}

	/**
	 * Принять множество записей
	 */
	function acceptMultiRecTTGModeration() {
		$data = $this->ProcessInputData('acceptMultiRecTTGModeration', true, true);
		if ($data === false) { return false; }
		$Error_Msg = "";
		$params = array();
		
		$this->load->library('textlog', array('file'=>'TTGModeration.log'));
		$this->textlog->add("");

		$response = array('success' => true, 'error_list' => array());

		$TimetableGraf_ids = json_decode($data['TimetableGraf_ids']);		
		$this->textlog->add("accept: TimetableGraf_ids: {$data['TimetableGraf_ids']}");

		$this->load->helper('Notify');
		
		/*
		foreach($TimetableGraf_ids as $TimetableGraf_id) {
			$params = $data;
			$params['TimetableGraf_id'] = $TimetableGraf_id;
			$this->textlog->add("accept: start TimetableGraf_id: {$TimetableGraf_id}");

			$resp_arr = $this->acceptRecTTGModerationSendMail($params);

			if (!empty($resp_arr['Error_Msg'])) {
				$response['error_list'][] = array(
					'TimetableGraf_id' => $TimetableGraf_id,
					'Error_Msg' => toUTF($resp_arr['Error_Msg'])
				);
				$this->textlog->add("accept: send message error: {$resp_arr['Error_Msg']}");
			}

			//Изменение статуса
			$params['Status'] = '1';
			$this->textlog->add("accept: set status: {$params['Status']}");
			$resp_arr = $this->dbmodel->setTimetableGrafModeration($params);

			if (!empty($resp_arr['Error_Msg'])) {
				$response['error_list'][] = array(
					'TimetableGraf_id' => $TimetableGraf_id,
					'Error_Msg' => toUTF($resp_arr['Error_Msg'])
				);
				$this->textlog->add("accept: set status error: {$resp_arr['Error_Msg']}");
			}
			$this->textlog->add("accept: end TimetableGraf_id: {$TimetableGraf_id}");
		}

		$this->ProcessModelSave($response, true, $response)->ReturnData();
		 * 
		 */
		if($TimetableGraf_ids && is_array($TimetableGraf_ids)){
			if(count($TimetableGraf_ids) == 0 || count($TimetableGraf_ids) > 500){
				$Error_Msg = "количество передаваемых бирок на модерацию не должно превышать 500";
				$response['error_list'][] = array(
					'TimetableGraf_id' => $data['TimetableGraf_ids'],
					'Error_Msg' => toUTF($Error_Msg)
				);
				$this->textlog->add("accept: send message error: {$Error_Msg}");
				$this->ProcessModelSave($response, true, $response)->ReturnData();
				return false;
			}				
				
			//Изменение статуса
			$params['Status'] = '1';
			$params['TimetableGraf_ids'] = $TimetableGraf_ids;
			$params['pmUser_id'] = $data['pmUser_id'];
			$this->textlog->add("accept: set satus TimetableGraf_ids: {$data['TimetableGraf_ids']}");
			$resp_arr = $this->dbmodel->setMultipleTimetableGrafModeration($params);

			if (!empty($resp_arr['Error_Msg'])) {
				$response['error_list'][] = array(
					'TimetableGraf_id' => $data['TimetableGraf_ids'],
					'Error_Msg' => toUTF($resp_arr['Error_Msg'])
				);
				$this->textlog->add("accept: set status error: {$resp_arr['Error_Msg']}");
				$this->ProcessModelSave($response, true, $response)->ReturnData();
				return false;
			}
			$this->textlog->add("accept: end status TimetableGraf_ids: {$data['TimetableGraf_ids']}");
			// что бы не заставлять ждать пользователя отправим сообщение об успешном выполнении
			echo json_encode($response);
			// сообщение отправили, а теперь продолжим и отправим почту
			$error_send_message = array();
			$successfully_send_message = array();
			foreach($TimetableGraf_ids as $TimetableGraf_id) {
				$params = $data;
				$params['TimetableGraf_id'] = $TimetableGraf_id;
				$this->textlog->add("accept: start send mail TimetableGraf_id: {$TimetableGraf_id}");

				$resp_arr = $this->acceptRecTTGModerationSendMail($params);

				if (!empty($resp_arr['Error_Msg'])) {
					$response['error_list'][] = array(
						'TimetableGraf_id' => $TimetableGraf_id,
						'Error_Msg' => $resp_arr['Error_Msg']
					);
					//$this->textlog->add("accept: send message error: {$resp_arr['Error_Msg']}");
					$error_send_message[$TimetableGraf_id] = $response;
				}else{
					//$this->textlog->add("accept: send message successfully: {$TimetableGraf_id}");
					$successfully_send_message[] = $TimetableGraf_id;
				}
			}
			// запишем в логи ошибки и успех
			if(count($error_send_message) > 0){
				$msg = json_encode($error_send_message);
				$this->textlog->add("accept: send message error: {$msg}");
			}
			if(count($successfully_send_message) > 0){
				$msg = json_encode($successfully_send_message);
				$this->textlog->add("accept: send message successfully: {$msg}");
			}
		}else{
			$Error_Msg = "переданы некорректные данные";
			$response['error_list'][] = array(
					'TimetableGraf_id' => $data['TimetableGraf_ids'],
					'Error_Msg' => toUTF($Error_Msg)
				);
			$this->textlog->add("accept: send message error: {$Error_Msg}");
			$this->ProcessModelSave($response, true, $response)->ReturnData();
		}
	}
	
	/**
	 * Одобрение записи
	 */
	function acceptRecTTGModeration() {
		$data = $this->ProcessInputData('acceptRecTTGModeration', true, true);
		if ($data === false) { return false; }

		$this->load->helper('Notify');
		$this->acceptRecTTGModerationSendMail($data);

		$data['Status'] = '1';
		$response = $this->dbmodel->setTimetableGrafModeration($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}
	
	
	/**
	 * Отправка сообщения об одобрении записи
	 */	
	function acceptRecTTGModerationSendMail($data) {

		$response = $this->dbmodel->getTTGDataForMail($data);
		if (!empty($response['Error_Msg'])) { return $response; }
		if (!isset($response[0])) { return array('Error_Msg' => 'Пользователь не указал способ получения уведомлений.'); }
		$response = $response[0];

		$allowSendMessage = false;
		
		$arMonthOf = array(
			1 => "января",
			2 => "февраля",
			3 => "марта",
			4 => "апреля",
			5 => "мая",
			6 => "июня",
			7 => "июля",
			8 => "августа",
			9 => "сентября",
			10 => "октября",
			11 => "ноября",
			12 => "декабря"
		);
		
		$Person_FIO = trim($response['Person_Surname']).' '.trim($response['Person_Firname']).' '.trim($response['Person_Secname']);
		$StringDate = "{$response['TimetableGraf_begTime']->format('j')} ".$arMonthOf[$response['TimetableGraf_begTime']->format('n')]." в {$response['TimetableGraf_begTime']->format('H:i')}";
		$Person_Init = ucwords(trim($response['Person_Surname'])).' '.mb_substr($response['Person_Firname'],0,1).mb_substr($response['Person_Secname'],0,1);
		$MedPersonal_Init = ucwords($response['Med_Person_Surname']).' '.mb_substr($response['Med_Person_Firname'],0,1).mb_substr($response['Med_Person_Secname'],0,1);

		if ( isset($response['UserNotify_AcceptIsEmail']) && $response['UserNotify_AcceptIsEmail'] == 1 ) {
			$allowSendMessage = true;
			$this->load->library('email');
			try {
				$error_msg = "Ошибка при отправке письма!";
				set_error_handler(function() use ($error_msg) { throw new Exception($error_msg, 10); }, E_ALL & ~E_NOTICE);
				$this->textlog->add("accept: sending letter e-mail {$response['EMail']}");
				@$resultsend = $this->email->sendKvrachu($response['EMail'], 'Запись на прием одобрена', "Уважаемый(ая) {$response['FirstName']} {$response['MidName']}.
Посещение пациентом {$Person_FIO} врача {$response['ProfileSpec_Name_Rod']} {$response['MedPersonal_FIO']}  {$StringDate} одобрено модератором.
Ваше посещение может быть отменено только в экстренном случае.");
				$this->textlog->add("accept: mail sent");
				restore_error_handler();
				if (!$resultsend) {
					throw new Exception("Письмо не было отправлено!\nНе удалось выполнить отправление письма!", 20);
				}
			} catch (Exception $e) {
				$this->textlog->add("accept: send mail exception: {$e->getMessage()}");
			}
		}
		
		if ( isset($response['UserNotify_AcceptIsSMS']) && $response['UserNotify_AcceptIsSMS'] == 1 ) {
			$allowSendMessage = true;
			$data['text'] = "Пациент {$Person_Init} записан к врачу {$MedPersonal_Init} на {$StringDate}";
			$data['UserNotify_Phone'] = $response['UserNotify_Phone'];
			$data['User_id'] = $response['User_id'];
			try {
				$error_msg = "Ошибка при отправке смс-сообщения!";
				$this->textlog->add("accept: sending sms");
				set_error_handler(function() use ($error_msg) { throw new Exception($error_msg); }, E_ALL & ~E_NOTICE);
				sendNotifySMS($data);
				restore_error_handler();
			} catch (Exception $e) {
				$this->textlog->add("accept: send sms exception: {$e->getMessage()}");
			}
		}

		if (!$allowSendMessage) {
			return array('Error_Msg' => 'Пользователь не указал способ получения уведомлений.');
		} else {
			return array('Error_Msg' => '');
		}
	}

	/**
	 * Подтверждение множества записей
	 */
	function confirmMultiRecTTGModeration() {
		$data = $this->ProcessInputData('confirmMultiRecTTGModeration', true, true);
		if ($data === false) { return false; }

		$this->load->library('textlog', array('file'=>'TTGModeration.log'));
		$this->textlog->add("");

		$response = array('success' => true, 'error_list' => array());

		$TimetableGraf_ids = json_decode($data['TimetableGraf_ids']);
		$this->textlog->add("confirm: TimetableGraf_ids: {$data['TimetableGraf_ids']}");

		$this->load->helper('Notify');
		foreach($TimetableGraf_ids as $TimetableGraf_id) {
			$params = $data;
			$params['TimetableGraf_id'] = $TimetableGraf_id;
			$this->textlog->add("confirm: start TimetableGraf_id: {$TimetableGraf_id}");

			$resp_arr = $this->confirmRecTTGModerationSendMail($params);

			//Если не удалось послать сообщение, то запоминаем ошибку и переходим к следующей записи
			if (!empty($resp_arr['Error_Msg'])) {
				$response['error_list'][] = array(
					'TimetableGraf_id' => $TimetableGraf_id,
					'Error_Msg' => toUTF($resp_arr['Error_Msg'])
				);
				$this->textlog->add("confirm: send message error: {$resp_arr['Error_Msg']}");
				continue;
			}

			//Изменение статуса
			$params['Status'] = '2';
			$this->textlog->add("confirm: set status: {$params['Status']}");
			$resp_arr = $this->dbmodel->setTimetableGrafModeration($params);

			if (!empty($resp_arr['Error_Msg'])) {
				$response['error_list'][] = array(
					'TimetableGraf_id' => $TimetableGraf_id,
					'Error_Msg' => toUTF($resp_arr['Error_Msg'])
				);
				$this->textlog->add("confirm: set status error: {$resp_arr['Error_Msg']}");
			}
			$this->textlog->add("confirm: end TimetableGraf_id: {$TimetableGraf_id}");
		}

		$this->ProcessModelSave($response, true, true)->ReturnData();
	}

	/**
	 * Подтверждение записи
	 */
	function confirmRecTTGModeration() {
		$data = $this->ProcessInputData('confirmRecTTGModeration', true, true);
		if ($data === false) { return false; }

		$this->load->helper('Notify');
		$resp_arr = $this->confirmRecTTGModerationSendMail($data);
		if (empty($resp_arr['Error_Msg'])) {
			$data['Status'] = '2';
			$response = $this->dbmodel->setTimetableGrafModeration($data);
		} else {
			$response = $resp_arr;
		}

		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}

	/**
	 * Отправка сообщения о подтверждении
	 */
	function confirmRecTTGModerationSendMail($data) {
		$response = $this->dbmodel->getTTGDataForMail($data);
		if (!empty($response['Error_Msg'])) { return $response; }
		if (!isset($response[0])) { return array('Error_Msg' => 'Пользователь не указал способ получения уведомлений.'); }
		$response = $response[0];

		$allowSendMessage = false;

		if (!empty($data['Moderator_Comment'])) {
			if ( isset($response['UserNotify_AcceptIsEmail']) && $response['UserNotify_AcceptIsEmail'] == 1 ) {
				$allowSendMessage = true;
				$this->load->library('email');
				try {
					$error_msg = "Ошибка при отправке письма!";
					set_error_handler(function() use ($error_msg) { throw new Exception($error_msg, 10); }, E_ALL & ~E_NOTICE);
					@$resultsend = $this->email->sendKvrachu($response['EMail'], 'Предупреждение', $data['Moderator_Comment']);
					restore_error_handler();
					if (!$resultsend) {
						throw new Exception("Письмо не было отправлено!\nНе удалось выполнить отправление письма!", 20);
					}
				} catch (Exception $e) {
					$this->textlog->add("confirm: send mail exception: {$e->getMessage()}");
				}
			}

			if ( isset($response['UserNotify_AcceptIsSMS']) && $response['UserNotify_AcceptIsSMS'] == 1 ) {
				$allowSendMessage = true;
				$data['text'] = "Предупреждение. {$data['Moderator_Comment']}";
				$data['UserNotify_Phone'] = $response['UserNotify_Phone'];
				$data['User_id'] = $response['User_id'];
				try {
					$error_msg = "Ошибка при отправке смс-сообщения!";
					set_error_handler(function() use ($error_msg) { throw new Exception($error_msg); }, E_ALL & ~E_NOTICE);
					sendNotifySMS($data);
					restore_error_handler();
				} catch (Exception $e) {
					$this->textlog->add("confirm: send sms exception: {$e->getMessage()}");
				}
			}

			if (!$allowSendMessage) {
				return array('Error_Msg' => 'Пользователь запретил отправку сообщений.');
			}
		}
		return array('Error_Msg' => '');
	}
	
	/**
	 * Удаление записи
	 */
	function failRecTTGModeration() {
		$data = $this->ProcessInputData('failRecTTGModeration', true, true);
		if ($data === false) { return false; }

		$this->load->helper('Notify');
		$this->failRecTTGModerationSendMail($data);
		
		$data['Status'] = '1';
		$data['Person_id'] = NULL;
		$data['object'] = 'TimetableGraf';
		$this->dbmodel->setTimetableGrafModeration($data);
		$this->dbmodel->Clear($data);
		$this->OutData['success'] = true;
		$this->ReturnData();
	}
	
	
	/**
	 * Отправка сообщения об одобрении записи
	 */	
	function failRecTTGModerationSendMail($data) {
		
		$response = $this->dbmodel->getTTGDataForMail($data);
		if (!empty($response['Error_Msg'])) { return $response; }
		if (!isset($response[0])) { return array('Error_Msg' => 'Пользователь не указал способ получения уведомлений.'); }
		$response = $response[0];
		
		$arMonthOf = array(
			1 => "января",
			2 => "февраля",
			3 => "марта",
			4 => "апреля",
			5 => "мая",
			6 => "июня",
			7 => "июля",
			8 => "августа",
			9 => "сентября",
			10 => "октября",
			11 => "ноября",
			12 => "декабря"
		);
		
		$Person_FIO = trim($response['Person_Surname']).' '.trim($response['Person_Firname']).' '.trim($response['Person_Secname']);
		$StringDate = "{$response['TimetableGraf_begTime']->format('j')} ".$arMonthOf[$response['TimetableGraf_begTime']->format('n')]." в {$response['TimetableGraf_begTime']->format('H:i')}";
		$Person_Init = ucwords(trim($response['Person_Surname'])).' '.mb_substr($response['Person_Firname'], 0, 1).mb_substr($response['Person_Secname'], 0, 1);
		$MedPersonal_Init = ucwords($response['Med_Person_Surname']).' '.mb_substr($response['Med_Person_Firname'], 0, 1).mb_substr($response['Med_Person_Secname'], 0, 1);
		
		if (KVRACHU_TYPE == '2' || (isset($response['UserNotify_AcceptIsEmail']) && $response['UserNotify_AcceptIsEmail'] == 1 )) {
			$this->load->library('email');
			@$resultsend = $this->email->sendKvrachu($response['EMail'], 'Отказ в посещении поликлиники', "Уважаемый(ая) {$response['FirstName']} {$response['MidName']}.
Мы вынуждены отказать вам в посещении пациентом {$Person_FIO} врача {$response['MedPersonal_FIO']} {$StringDate} по следующей причине:
{$data['Moderator_Comment']}");
		}
		
		if ((KVRACHU_TYPE == '2' && isset($response['UserNotify_Phone']) && $response['UserNotify_Phone'] != '') || (isset($response['UserNotify_AcceptIsSMS']) && $response['UserNotify_AcceptIsSMS'] == 1 )) {
			$data['text'] = "В посещении пациентом {$Person_Init} врача {$MedPersonal_Init} отказано";
			if (!empty($data['Moderator_Comment'])) {
				$data['text'] .= " по причине: {$data['Moderator_Comment']}";
			}
			$data['UserNotify_Phone'] = $response['UserNotify_Phone'];
			$data['User_id'] = $response['User_id'];
			try {
				$error_msg = "Ошибка при отправке смс-сообщения!";
				set_error_handler(function() use ($error_msg) { throw new Exception($error_msg); }, E_ALL & ~E_NOTICE);
				sendNotifySMS($data);
				restore_error_handler();
			} catch (Exception $e) {
				log_message('error', __CLASS__.': SMS send error: '.$e->getMessage());
				return false;
			}
		}
		
	}
	
	/**
	 * Поиск участка по адресу
	 */
	function findLpuAddressRegions($data) {
		$arRegions = array();
		$response = $this->dbmodel->findLpuAddressRegions($data);
		foreach ( $response as $region ){
			if( ( $data['Address_House'] == '' ) || HouseMatchRange( trim( $data['Address_House'] ), trim( $region['LpuRegionStreet_HouseSet'] ) ) )
				$arRegions[] = $region['LpuRegion_Name'];
		}
		return join(', ', $arRegions);
	}
	
	/**
	 * Печать списка пациентов, записанных на определенного врача, с защитой персональных данных /поликлиника
	 */
	function printPacList() {
	
		$this->load->library('parser');
		
		$data = $this->ProcessInputData('printPacList', true);
		if ($data === false) { return false; }

		if (!empty($data['MedStaffFact_id'])) {
			$response = $this->dbmodel->printPacList($data);
		} else {
			$response = $this->dbmodel->printPacStacOrMSList($data);
		}

		$template = 'print_pac_list';
		
		// Таблица со списком
		$n = count($response['ttgData']);
		$printtimeTable = "<table cellpadding=0 cellspacing=0 id=printtimeTable>";
		for ($i = 0; $i < $n / 3; $i++) {
			$printtimeTable .= "<tr class='time' style='font-weight: bold; font-size: 18px;'>";
			$printtimeTable .= $this->PrintRec($response['ttgData'][$i]);
			if ( isset($response['ttgData'][($i + ceil($n / 3))]) )
				$printtimeTable .= $this->PrintRec($response['ttgData'][($i + ceil($n / 3))]);
			if ( isset($response['ttgData'][($i + 2 * ceil($n / 3) )]) ) {
				$printtimeTable .= $this->PrintRec($response['ttgData'][($i + 2 * ceil($n / 3) )]);
			} else {
				$printtimeTable .= $this->PrintRec(null);
			}
			$printtimeTable .= "</tr>";
		}
		$printtimeTable .= "</table>";

		$print_data = array(
			'printtimeTable' => $printtimeTable,
			'Print_Date' => date('d.m.Y H:i')
		);

		if (isset($data['Day'])) {
			$print_data['Day'] = date('d.m.Y', strtotime($data['Day']));
		} else {
			if ($data['begDate'] == $data['endDate']) {
				$print_data['Day'] = date('d.m.Y', strtotime($data['begDate']));
			} else {
				$print_data['begDate'] = date('d.m.Y', strtotime($data['begDate']));
				$print_data['endDate'] = date('d.m.Y', strtotime($data['endDate']));
			}
		}

		if (!empty($response[0])) {
			if (!empty($response[0]['MedPersonal_FIO'])) {
				$print_data['MedPersonal_FIO'] = returnValidHTMLString($response[0]['MedPersonal_FIO']);
			} else {
				$print_data['MedService_Name'] = returnValidHTMLString($response[0]['MedService_Name']);
			}
			$print_data['Lpu_Nick'] = returnValidHTMLString($response[0]['Lpu_Nick']);
			$print_data['Address_Address'] = returnValidHTMLString($response[0]['Address_Address']);
			$print_data['LpuSection_Name'] = returnValidHTMLString($response[0]['LpuSection_Name']);
		}
		
		if (!empty($response['OrgHead'])) {
			$print_data['OrgHead'] = "<br><div style='font-size:12pt;'><b>В случае возникновения претензий к качеству обслуживания просим обращаться к<br/>	- {$response['OrgHead'][0]['OrgHead_FIO']}; тел.: {$response['OrgHead'][0]['OrgHead_Phone']}, {$response['OrgHead'][0]['OrgHead_Mobile']}; {$response['OrgHead'][0]['OrgHead_Address']}</b></div><br/><br/>";
		} else {
			$print_data['OrgHead'] = '';
		}
        if (isset($response[0]) && strlen($response[0]['Kladr_Code']) == 13 && substr($response[0]['Kladr_Code'], 0, 11) == '02000001000') { //https://redmine.swan.perm.ru/issues/35106
			if ($data['session']['region']['nick'] == 'ufa') {
				$print_data['Info'] = '
					<div style="font-size:12pt;">
					<p>В случае предварительной записи на приём, медицинские документы (стат. талон и амбулаторная карта) должны быть заранее переданы соответствующему врачу.</p>
					<p>В случае превышения количества обратившихся экстренных больных числа зарезервированных под них бирок, возможен «сдвиг» во времени приёма.</p>
					<ul type="none">
					<p>Обращаем внимание, что записаться на первичный приём к врачу-терапевту, врачу-педиатру, врачу общей практики, врачу-стоматологу, врачу-гинекологу возможно:</p>
					<li>&mdash; по единому бесплатному номеру 13-01 (МТС, Билайн, МегаФон);</li>
					<li>&mdash; через Единый медицинский портал Республики Башкортостан  https://doctor.bashkortostan.ru.</li>
					</ul>
					<p>Записаться к другим врачам можно через регистратуру поликлиники и непосредственно у врача.</p>
					</div>';
            } else {
	            $print_data['Info'] = '
	                <div style="font-size:12pt;">
	                <p>Записаться можно по короткому номеру 1303 / (347) 276-13-03,
	                    через сайт doctor.ufacity.info, через регистратуру поликлиники и непосредственно у врача.</p>
	                <p>В случае предварительной записи на прием, медицинские документы (стат. талон и амбулаторная карта)
	                    должны быть заранее переданы соответствующему врачу.</p>
	                <p>Оставить свое мнение по поводу сервиса «Единая регистратура» можно
	                    на форуме официального сайта Администрации ГО г. Уфа РБ
	                    http://www.ufacity.info/forum/forum14/</p>
	                <p>В случае превышения количества обратившихся экстренных больных числа
	                    зарезервированных под них бирок, возможен «сдвиг» во времени приема.</p>
	                </div>';
	        }
        }
        else {
            $print_data['Info'] = '';
        }
		return $this->parser->parse($template, $print_data, false);
		
	}

	/**
	 * Вывод одной записи, то есть двух столбцов, время и инициалы
	 */
	function PrintRec($record) {
		// Не будем плодить сущности
		if (empty($record)) 
		{ 
			return "<td style='font-weight: bold; font-size: 20px;text-align: left;'></td>";
		} 
		else 
		{
			if (trim( $record['Person_Surname'] ) != '') {
				$PersonInitials = trim( mb_substr($record['Person_Surname'], 0, 1) ) . ". " . trim( mb_substr($record['Person_Firname'], 0, 1) ) . ". " . trim( mb_substr($record['Person_Secname'], 0, 1)."." );
			} else {
				$PersonInitials = '';
			}
			if (!empty($record['TimetableGraf_begTime'])) {
				$date = date('H:i, d.m.Y', strtotime($record["TimetableGraf_begTime"]));
			} else if (!empty($record["TimeTableStac_begTime"])) {
				$date = date('d.m.Y', strtotime($record["TimeTableStac_begTime"]));
			} else {
				$date = "б/з";
			}
			return "<td style='font-weight: bold; font-size: 20px;text-align: left;'>". $date ." - {$PersonInitials}</td>";
		}
	}
	
	/**
	 * Редактирование переданных бирок
	 */
	function editTTG() {
		$data = $this->ProcessInputData('editTTG', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->editTTGSet($data);
		$this->ProcessModelSave($response, true, 'Ошибка при редактировании бирок')->ReturnData();
	}
	
	/**
	 * Получение информации по бирке поликлиники
	 */
	function getTTGInfo() {
		$data = $this->ProcessInputData('getTTGInfo',true,true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getTTGInfo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
}