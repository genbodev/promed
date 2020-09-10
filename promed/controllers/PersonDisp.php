<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonDisp - контроллер для выполнения операций с диспансерной картотекой пациентов.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  Polka
 * @access	   public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author	   Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 * @version	  30.06.2009
 *
 * @property-read Polka_PersonDisp_model $dbmodel
 */

class PersonDisp extends swController
{
	public $inputRules = array();

	/**
	 * Description
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('Polka_PersonDisp_model', 'dbmodel');
		
		$this->inputRules = array(
			'exportPersonDispForPeriod' => array(
				array('field' => 'ExportDateRange', 'label' => 'Период выгрузки', 'rules' => 'trim|required', 'type' => 'daterange'),
			),
			'loadPersonDispPanel' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getLabelObserveCharts' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getPersonLabelCounts' => array(
				array(
					'field' => 'MonitorLpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'outBegDate',
					'label' => 'Начало периода исключения',
					'rules' => 'trim',
					'type' => 'datetime'
				),
				array(
					'field' => 'outEndDate',
					'label' => 'Конец периода исключения',
					'rules' => 'trim',
					'type' => 'datetime'
				),
				array(
					'field' => 'Label_id',
					'label' => 'Метка',
					'rules' => 'trim',
					'type' => 'int'
				),
			),
			'createPersonLabel' => array(
				array(
					'field' => 'Label_id',
					'label' => 'Идентификатор метки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор врача',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'dateConsent',
					'label' => 'Дата согласия',
					'rules' => 'trim',
					'type' => 'datetime'
				),
				array(
					'field' => 'phone',
					'label' => 'Телефон пациента',
					'rules' => 'trim',
					'type' => 'string'
				),				
			),
			'createLabelObserveChart' => array(
				array(
					'field' => 'PersonDisp_id',
					'label' => 'Идентификатор контрольной карты диспансерного наблюдения',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор пользователя',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PersonLabel_id',
					'label' => 'Идентификатор метки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Label_id',
					'label' => 'Идентификатор метки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_Phone',
					'label' => 'Телефон пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'dateConsent',
					'label' => 'Дата согласия',
					'rules' => 'trim',
					'type' => 'datetime'
				),
				array(
					'field' => 'allowMailing',
					'label' => 'Согласие на рассылку',
					'rules' => 'trim',
					'type' => 'boolean'
				)
			),
			'InviteInMonitoring' => array(
				array(
					'field' => 'isSingle',
					'label' => 'Тип приглашения: единичное или рассылка',
					'rules' => 'trim',
					'type' => 'boolean'
				),
				array(
					'field' => 'Persons',
					'label' => 'Пациенты',
					'rules' => 'trim',
					'type' => 'json_array'
				),
				array(
					'field' => 'FeedbackMethod',
					'label' => 'Канал связи',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MessageText',
					'label' => 'Текст сообщения',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'MessageTitle',
					'label' => 'Заголовок',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'FeedbackMethod_id',
					'label' => 'Канал связи',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы сотрудника, создавшего приглашения',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'RemindToMonitoring' => array(
				array(
					'field' => 'Persons',
					'label' => 'Пациенты',
					'rules' => 'trim',
					'type' => 'json_array'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'ChangeLabelInviteStatus' => array(
				array(
					'field' => 'LabelInvite_id',
					'label' => 'Идентификатор приглашения',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'LabelInviteStatus_id',
					'label' => 'Идентификатор статуса',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'RefuseCause',
					'label' => 'Причина',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы врача',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'removePersonFromMonitoring' => array(
				array(
					'field' => 'Label_id',
					'label' => 'Идентификатор метки',
					'rules' => 'trim',
					'type' 	=> 'int'
				),
				array(
					'field' => 'Chart_id',
					'label' => 'Идентификатор карты наблюдения',
					'rules' => 'required',
					'type' 	=> 'int'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' 	=> 'int'
				),
				array(
					'field' => 'DispOutType_id',
					'label' => 'Причина исключения',
					'rules' => 'required',
					'type' 	=> 'int'
				),
				array(
					'field' => 'endDate',
					'label' => 'Дата исключения',
					'rules' => 'required',
					'type' 	=> 'datetime'
				),
			),
			'savePersonChartInfo' => array(
				array(
					'field' => 'Chart_id',
					'label' => 'Идентификатор карты наблюдения',
					'rules' => 'required',
					'type' 	=> 'int'
				),
				array(
					'field' => 'Chart_begDate',
					'label' => 'Дата согласия',
					'rules' => 'trim',
					'type' 	=> 'datetime'
				),
				array(
					'field' => 'PersonModel_id',
					'label' => 'Тип модели пациента',
					'rules' => 'trim',
					'type' 	=> 'int'
				),
				array(
					'field' => 'email',
					'label' => 'Смс',
					'rules' => 'trim',
					'type' 	=> 'string'
				),
				array(
					'field' => 'sms',
					'label' => 'Смс',
					'rules' => 'trim',
					'type' 	=> 'string'
				),
				array(
					'field' => 'voice',
					'label' => 'Смс',
					'rules' => 'trim',
					'type' 	=> 'string'
				)
			),
			'loadPersonLabelList' => array(
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'MonitorLpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'status',
					'label' => 'Статус',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'paging',
					'label' => 'Pagination',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'DispOutType_id',
					'label' => 'Причина исключения',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LabelInviteStatus_id',
					'label' => 'Статус приглашения',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'outBegDate',
					'label' => 'Начало периода исключения',
					'rules' => 'trim',
					'type' => 'datetime'
				),
				array(
					'field' => 'outEndDate',
					'label' => 'Конец периода исключения',
					'rules' => 'trim',
					'type' => 'datetime'
				),
				array(
					'field' => 'Diags',
					'label' => 'Диагнозы',
					'rules' => 'trim',
					'type' => 'json_array'
				),
				array(
					'field' => 'Label_id',
					'label' => 'Метка',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'fio',
					'label' => 'ФИО пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'trim',
					'type' => 'int'
				)
			),
			'getPersonChartInfo' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' 	=> 'int'
				),
				array(
					'field' => 'Chart_id',
					'label' => 'Идентификатор карты наблюдения',
					'rules' => '',
					'type' 	=> 'int'
				)
			),
			'getPersonDataFromPortal' => array(
				array(
					'field' => 'Person_ids',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' 	=> 'json_array'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' 	=> 'int'
				)
			),
			'savePersonChartFeedback' => array(
				array(
					'field' => 'FeedbackMethod_id',
					'label' => 'Способ обратной связи',
					'rules' => 'required',
					'type' 	=> 'int'
				),
				array(
					'field' => 'Chart_id',
					'label' => 'Идентификатор карты наблюдения',
					'rules' => 'required',
					'type' 	=> 'int'
				)
			),
			'saveLabelObserveChartRate' => array(
				array(
					'field' => 'Chart_id',
					'label' => 'Идентификатор карты наблюдения',
					'rules' => 'required',
					'type' 	=> 'int'
				),
				array(
					'field' => 'LabelRate_id',
					'label' => 'Идентификатор целевого показателя',
					'rules' => 'required',
					'type' 	=> 'int'
				),
				array(
					'field' => 'LabelRateMin',
					'label' => 'Минимальное значение',
					'rules' => 'required',
					'type' 	=> 'float'
				),
				array(
					'field' => 'LabelRateMax',
					'label' => 'Максимальное значение',
					'rules' => 'required',
					'type' 	=> 'float'
				),
			),
			'loadLabelObserveChartMeasure' => array(
				array(
					'field' => 'Chart_id',
					'label' => 'Идентификатор карты наблюдения',
					'rules' => '',
					'type' 	=> 'int'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' 	=> 'int'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => 1,
					'field' => 'limit',
					'label' => 'Период',
					'rules' => 'trim',
					'type' => 'int'
				)
			),
			'loadObserveChartMeasure' => array(
				array(
					'field' => 'Chart_id',
					'label' => 'Идентификатор карты наблюдения',
					'rules' => 'required',
					'type' 	=> 'int'
				),
				array(
					'default' => 0,
					'field' => 'fromdate',
					'label' => 'Период',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'default' => 14,
					'field' => 'todate',
					'label' => 'Период',
					'rules' => 'required',
					'type' => 'date'
				),
			),
			'saveLabelObserveChartMeasure' => array(
				array(
					'field' => 'ChartInfo_id',
					'label' => 'Идентификатор измерения',
					'rules' => 'trim',
					'type' 	=> 'int'
				),
				array(
					'field' => 'Chart_id',
					'label' => 'Идентификатор карты наблюдения',
					'rules' => 'required',
					'type' 	=> 'int'
				),
				array(
					'field' => 'ObserveDate',
					'label' => 'Дата',
					'rules' => 'trim',
					'type' 	=> 'datetime'
				),
				array(
					'field' => 'ObserveTime_id',
					'label' => 'Время',
					'rules' => 'trim',
					'type' 	=> 'int'
				),
				array(
					'field' => 'FeedbackMethod_id',
					'label' => 'Способ обратной связи',
					'rules' => 'trim',
					'type' 	=> 'int'
				),
				array(
					'field' => 'Complaint',
					'label' => 'Комментарий',
					'rules' => 'trim',
					'type' 	=> 'string'
				),
				array(
					'field' => 'RateMeasures', 
					'label' => 'Показатели', 
					'rules' => '', 
					'type' 	=> 'json_array'
				)
			),
			'deleteLabelObserveChartMeasure' => array(
				array(
					'field' => 'ChartInfo_id',
					'label' => 'Идентификатор измерения',
					'rules' => 'required',
					'type' 	=> 'int'
				)
			),
			'loadLabelInviteHistory' => array(
				array(
					'field' => 'PersonLabel_id',
					'label' => 'Идентификатор метки',
					'rules' => 'required',
					'type'  => 'int'
				)
			),
			'sendLabelMessage' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type'  => 'int'
				),
				array(
					'field' => 'Chart_id',
					'label' => 'Идентификатор карты наблюдения',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MessageText',
					'label' => 'Текст сообщения',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'FeedbackMethod_id',
					'label' => 'Способ обратной связи',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'phone',
					'label' => 'Номер телефона',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'email',
					'label' => 'Электронная почта',
					'rules' => 'trim',
					'type' => 'string'
				),
			),
			'loadLabelMessages' => array(
				array(
					'field' => 'Chart_id',
					'label' => 'Идентификатор карты наблюдения',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'start',
					'label' => 'start',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'limit',
					'label' => 'limit',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'exportPersonDispCard' => array(
				array('field' => 'Year',
					'label' => 'Год',
					'rules' => 'required',
					'type' => 'int'
				),
				array('field' => 'Month',
					'label' => 'Месяц',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadPersonDispList' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'PersonDisp_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				)
			),
			'savePersonDispMedicament' => array(
				array(
					'field' => 'Course',
					'label' => 'Месячный курс',
					'rules' => 'trim',
					'type' => 'float'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PersonDispMedicament_id',
					'label' => 'Идентификатор назначенного медикамента',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PersonDisp_id',
					'label' => 'Идентификатор карты',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'action',
					'label' => 'Режим сохранения',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Course_begDate',
					'label' => 'Дата начала',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'Course_endDate',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'date'
				)
			),
			'loadPersonDispGrid' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getPersonDispHistoryList' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'GetFilterTree' => array(
				array(
					'field' => 'object',
					'label' => 'Объект',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'node',
					'label' => 'Идентификатор узла',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'object_id',
					'label' => 'Идентификатор объекта',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'view_one_doctor',
					'label' => 'Признак загрузки только одного врача АРМа',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				)
			),
			'GetListByTree' => array(
				array(
					'field' => 'view_all_id',
					'label' => 'Показывать карты ДУ',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'view_mp_id',
					'label' => 'Врач является',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'view_mp_onDate',
					'label' => 'На дату',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'object',
					'label' => 'Объект',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'id',
					'label' => 'Идентификатор объекта',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'DiagLevel_id',
					'label' => 'Уровень диагноза',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field'	=> 'disp_med_personal',
					'label'	=> 'Поставивший врач',
					'rules'	=> '',
					'type'	=> 'id'
				),
				array(
					'field'	=> 'hist_med_personal',
					'label'	=> 'Ответственный врач',
					'rules'	=> '',
					'type'	=> 'id'
				),
				array(
					'field'	=> 'check_mph',
					'label'	=> 'Учитывать историю ответственных врачей',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field' => 'start',
					'label' => 'Начальная запись',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'limit',
					'label' => 'Количество',
					'rules' => 'trim',
					'type' => 'int'
				)
			),
			'getPersonDispMedicamentList' => array(
				array(
					'field' => 'PersonDisp_id',
					'label' => 'Идентификатор карты',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'savePersonDisp' => array(
				array(
					'field' => 'HumanUID',
					'label' => 'идентификатор человека в КЗ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PersonDisp_NumCard',
					'label' => 'Номер карты',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_nid',
					'label' => 'Диагноз новый',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_pid',
					'label' => 'Диагноз старый',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DispOutType_id',
					'label' => 'Причина исключения',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonDisp_begDate',
					'label' => 'Дата начала',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'PersonDisp_endDate',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'PersonDisp_NextDate',
					'label' => 'Дата начала',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'PersonDisp_id',
					'label' => 'Идентификатор карты',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'default' => 1,
					'field' => 'PersonDisp_IsDop',
					'label' => 'По результатам доп. писп.',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Sickness_id',
					'label' => 'Идентификатор карты',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'default' => 'add',
					'field' => 'action',
					'label' => 'Режим сохранения',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => '[]',
					'field' => 'medicaments',
					'label' => 'Медикаменты',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'ignoreExistsPersonDisp',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonDisp_DiagDate',
					'label' => 'Дата установления диагноза',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'DiagDetectType_id',
					'label' => 'Заболевание выявлено',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DeseaseDispType_id',
					'label' => 'Диагноз установлен',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonDisp_IsTFOMS',
					'label' => 'Признак отправки данных в ТФОМС',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegister_id',
					'label' => 'Идентификатор регистра',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'DispGroup_id',
					'label' => 'Диспансерная группа',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PersonPregnancy_id',
					'label' => 'Идентификатор регистра беременных',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadPersonDispEditForm' => array(
				array(
					'field' => 'PersonDisp_id',
					'label' => 'Идентификатор карты',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deletePersonDisp' => array(
				array(
					'field' => 'PersonDisp_id',
					'label' => 'Идентификатор карты',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deletePersonDispMedicament' => array(
				array(
					'field' => 'PersonDispMedicament_id',
					'label' => 'Идентификатор карты',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getPersonDispSignalViewData' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UserMedStaffFact_id',
					'label' => 'Идентификатор рабочего места текущего врача',
					'rules' => '',
					'type' => 'id'
				)
			),
            'loadDiagDispCardHistory' => array(
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Идентификатор карты',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
            'loadDiagDispCardEditForm' => array(
                array(
                    'field' => 'DiagDispCard_id',
                    'label' => 'Идентификатор строки в истории диагнозов',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
            'deleteDiagDispCard' => array(
                array(
                    'field' => 'DiagDispCard_id',
                    'label' => 'Идентификатор строки из истории диагнозов',
                    'rules' => 'required',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Идентификатор карты',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'saveDiagDispCard' => array(
                array(
                    'field' => 'DiagDispCard_id',
                    'label' => 'Идентификатор строки из истории диагнозов',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'DiagDispCard_Date',
                    'label' => 'Дата установки диагноза',
                    'rules' => 'trim|required',
                    'type'  => 'date'
                ),
                array(
                    'field' => 'Diag_id',
                    'label' => 'Идентификатор диагноза',
                    'ruled' => 'required',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Идентификатор карты ДУ',
                    'rules' => 'required',
                    'type'  => 'int'
                )
            ),

            'loadPersonDispVizitList' => array(
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Диспансерная карта',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
            'loadPersonDispVizit' => array(
                array(
                    'field' => 'PersonDispVizit_id',
                    'label' => 'Идентификатор контроля посещений',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
            'savePersonDispVizit' => array(
                array(
                    'field' => 'PersonDispVizit_id',
                    'label' => 'Идентификатор контроля посещений',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Диспансерная карта',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'PersonDispVizit_NextDate',
                    'label' => 'Назначено явиться',
                    'rules' => '',
                    'type'  => 'date'
                ),
	            array(
		            'field' => 'PersonDispVizit_IsHomeDN',
		            'label' => 'ДН на дому',
		            'rules' => '',
		            'type' => 'checkbox'
	            ),
	            array(
                    'field' => 'PersonDispVizit_NextFactDate',
                    'label' => 'Явился',
                    'rules' => '',
                    'type'  => 'date'
                ),
            ),
            'delPersonDispVizit' => array(
                array(
                    'field' => 'PersonDispVizit_id',
                    'label' => 'Идентификатор контроля посещений',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
			
            'loadPersonDispSopDiaglist' => array(
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Диспансерная карта',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
            'loadPersonDispSopDiag' => array(
                array(
                    'field' => 'PersonDispSopDiag_id',
                    'label' => 'Идентификатор сопутствующего диагноза',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
			'getPersonDispNumber' => array(

			),
            'savePersonDispSopDiag' => array(
                array(
                    'field' => 'PersonDispSopDiag_id',
                    'label' => 'Идентификатор сопутствующего диагноза',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Диспансерная карта',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'Diag_id',
                    'label' => 'Диагноз',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'DopDispDiagType_id',
                    'label' => 'Характер заболевания',
                    'rules' => '',
                    'type'  => 'id'
                ),
            ),
            'delPersonDispSopDiag' => array(
                array(
                    'field' => 'PersonDispSopDiag_id',
                    'label' => 'Идентификатор сопутствующего диагноза',
                    'rules' => '',
                    'type'  => 'id'
                )
            ),
            'loadPersonDispHistlist' => array(
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Диспансерная карта',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
            'loadPersonDispHist' => array(
                array(
                    'field' => 'PersonDispHist_id',
                    'label' => 'Идентификатор ответственного врача',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
            'savePersonDispHist' => array(
                array(
                    'field' => 'PersonDispHist_id',
                    'label' => 'Идентификатор ответственного врача',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Диспансерная карта',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'MedPersonal_id',
                    'label' => 'Идентификатор мед.сотрудника',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'LpuSection_id',
                    'label' => 'Идентификатор отделения',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                 array(
                    'field' => 'PersonDispHist_begDate',
                    'label' => 'Начала периода',
                    'rules' => 'required',
                    'type'  => 'date'
                ),
                  array(
                    'field' => 'PersonDispHist_endDate',
                    'label' => 'Окончание периода',
                    'rules' => '',
                    'type'  => 'date'
                ),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор ответственного врача',
					'rules' => '',
					'type' => 'id'
				)
            ),
            'deletePersonDispHist' => array(
                array(
                    'field' => 'PersonDispHist_id',
                    'label' => 'Идентификатор ответственного врача',
                    'rules' => '',
                    'type'  => 'id'
                )
            ),
			
            'loadPersonDispTargetRateList' => array(
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Диспансерная карта',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),			
            'loadPersonDispTargetRate' => array(
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Диспансерная карта',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'RateType_id',
                    'label' => 'Показатель',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),			
            'savePersonDispTargetRate' => array(
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Диспансерная карта',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'RateType_id',
                    'label' => 'Показатель',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'TargetRate_Value',
                    'label' => 'Целевое значение',
                    'rules' => 'required',
                    'type'  => 'float'
                ),
                array(
                    'field' => 'RateValueType_SysNick',
                    'label' => 'Тип значения',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'PersonDispFactRateData',
                    'label' => 'Фактические значения',
                    'rules' => '',
                    'type'  => 'string'
                )
            ),
			'deletePersonDispFactRate' => array(
				array(
                    'field' => 'PersonDispFactRate_id',
                    'label' => 'Показатель',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'Rate_id',
                    'label' => 'Показатель',
                    'rules' => '',
                    'type'  => 'id'
                )
			),
            'loadPersonDispFactRateList' => array(
                array(
                    'field' => 'PersonDisp_id',
                    'label' => 'Диспансерная карта',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'RateType_id',
                    'label' => 'Показатель',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
			'getAvailabilityDispensaryCardCauseDeath' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'setResponsibleReplacementOptionsDoctor' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор нового ответственного врача',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'personDispList',
					'label' => 'список контрольных карт дисп.наблюдения',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
                    'field' => 'MedPersonal_id',
                    'label' => 'Идентификатор мед.сотрудника',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
				array(
                    'field' => 'LpuSection_id',
                    'label' => 'Идентификатор отделения',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
			),
			'checkPersonLabelObserveChartRates' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadMorbusOnkoSelectList' => [
				['field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'],
				['field' => 'PersonRegister_id', 'label' => 'Идентификатор записи регистра', 'rules' => 'required', 'type' => 'id'],
			],
			'savePersonRegisterDispLink' => [
				['field' => 'PersonDisp_id', 'label' => 'Диспансерная карта', 'rules' => 'required', 'type' => 'id'],
				['field' => 'PersonRegister_id', 'label' => 'Идентификатор записи регистра', 'rules' => 'required', 'type' => 'id'],
			],
			'setHypertensionRisk' => array(),
			'setIsDeviant' => array()
		);
	}

	/**
	 * Функция получения данных по дисп. учету человека при открытии ЭМК #12461
	 */
	function getPersonDispSignalViewData()
	{
		$data = $this->ProcessInputData('getPersonDispSignalViewData', true, false);
		if ($data) {
			$this->load->library('swFilterResponse'); 
			$response = $this->dbmodel->getPersonDispSignalViewData($data);
			//print_r($data['Person_id']); die;
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Функция получения списка истории диспансеризации пациентов.
	 * Входящие данные: $_POST с фильтрами
	 * На выходе: JSON-строка
	 */
	function getPersonDispHistoryList()
	{
		$this->load->helper('Text');
		$data = $this->ProcessInputData('getPersonDispHistoryList', true, true);
		if ($data) {
			$response = $this->dbmodel->getPersonDispHistoryList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Функция получения дерева фильтров.
	 *  Входящие данные: $_POST с фильтрами
	 *  На выходе: JSON-строка
	 */
	function GetFilterTree()
	{
		$this->load->helper('Main');

		$data = $this->ProcessInputData('GetFilterTree', true);
		if ($data === false) return false;
		
		if (!isset($data['object']) || $data['object'] == 'razdel') {
			if ($data['node'] == 'root') {
				$val = array();
				$val[] = array('text' =>
							   toUTF(
									 "Заболевания"
							   ),
							   'id' => 'razdel_sickness',
							   'leaf' => false,
							   'object' => 'razdel',
							   'cls' => 'folder'
				);
				$val[] = array('text' =>
							   toUTF(
									 "МКБ10"
							   ),
							   'id' => 'razdel_diag',
							   'leaf' => false,
							   'object' => 'razdel',
							   'cls' => 'folder'
				);
				$val[] = array('text' =>
							   toUTF(
									 "Структура ЛПУ"
							   ),
							   'id' => 'razdel_struct',
							   'leaf' => false,
							   'object' => 'razdel',
							   'cls' => 'folder'
				);
				$val[] = array('text' =>
							   toUTF(
									 "Врачи"
							   ),
							   'id' => 'razdel_medpersonal',
							   'leaf' => false,
							   'object' => 'razdel',
							   'cls' => 'folder'
				);
				$this->ReturnData($val);
			}
			else
			{
				switch ($data['node'])
				{
					case 'razdel_sickness':
						// 7 ноз.
						$val = array();
						$val[] = array('text' => toUTF(
													   'ОБЩИЕ'
						),
									   'id' => 'Common',
									   'leaf' => true,
									   'object' => 'Common',
									   'object_id' => '100500',
									   'cls' => 'folder');
						$info = $this->dbmodel->loadSicknessList($data);
						if ($info != false && count($info) > 0) {
							foreach ($info as $rows)
							{
								$val[] = array('text' =>
											   toUTF(
													 trim($rows['Sickness_Name'])
											   ),
											   'id' => 'Sickness_' . $rows['Sickness_id'],
											   'leaf' => true,
											   'object' => 'Sickness',
											   'object_id' => $rows['Sickness_id'],
											   'cls' => 'folder');
							}
							$this->ReturnData($val);
						}
						else
						{
							echo json_encode(array());
						}
						break;
					case 'razdel_diag':
						// диагнозы
						$info = $this->dbmodel->loadDiagList('null');
						if ($info != false && count($info) > 0) {
							$val = array();
							foreach ($info as $rows)
							{
								$val[] = array('text' =>
											   toUTF(
													 trim($rows['Diag_Code']) . '. ' . trim($rows['Diag_Name'])
											   ),
											   'id' => 'Diag_' . $rows['Diag_id'],
											   'leaf' => false,
											   'object' => 'Diag',
											   'object_id' => $rows['Diag_id'],
											   'DiagLevel_id' => $rows['DiagLevel_id'],
											   'cls' => 'folder');
							}
							$this->ReturnData($val);
						}
						break;
					case 'razdel_medpersonal':
						// мед. песонал
						$this->load->model("MedPersonal_model", "mpmodel");
						$data['from'] = 'PersonDisp';
						//$info = $this->mpmodel->getMedPersonalList($data);
						$info = $this->mpmodel->getMedStaffFactPersonalList($data);
						if ($info != false && count($info) > 0) {
							$val = array();
							foreach ($info as $rows)
							{
								$val[] = array('text' =>
											   toUTF(
													 trim($rows['MedPersonal_FIO'])
											   ),
											   'id' => 'MedPersonal_' . $rows['MedPersonal_id'],
											   'leaf' => true,
											   'object' => 'MedPersonal',
											   'object_id' => $rows['MedPersonal_id'],
											   'cls' => 'file');
							}
							$this->ReturnData($val);
						}
						else
						{
							echo json_encode(array());
						}
						break;
					case 'razdel_struct':
						// структура ЛПУ
						// здания
						$this->load->model("LpuStructure_model", "lsmodel");
						$data['object_id'] = $data['Lpu_id'];
						$info = $this->lsmodel->GetLpuBuildingNodeList($data);
						$val = array();
						// здания
						if ($info != false && count($info) > 0) {
							foreach ($info as $rows)
							{
								$val[] = array('text' =>
											   toUTF(
													 trim($rows['LpuBuilding_Name'])
											   ),
											   'id' => 'LpuBuilding_' . $rows['LpuBuilding_id'],
											   'leaf' => false,
											   'object' => 'LpuBuilding',
											   'object_id' => $rows['LpuBuilding_id'],
											   'iconCls' => 'lpu16');
							}
						}
						// участки
						$val[] = array('text' =>
									   toUTF(
											 "Участки"
									   ),
									   'id' => 'razdel_lpuregiontype',
									   'leaf' => false,
									   'object' => 'razdel',
									   'cls' => 'folder'
						);
						$this->ReturnData($val);
						break;
					case 'razdel_lpuregiontype':
						$this->load->model("LpuStructure_model", "lsmodel");
						$data['object_id'] = $data['Lpu_id'];
						$info = $this->lsmodel->GetLpuRegionTypeNodeList($data);
						if ($info != false && count($info) > 0) {
							foreach ($info as $rows)
							{
								$val[] = array('text' =>
											   toUTF(
													 trim($rows['LpuRegionType_Name'])
											   ),
											   'id' => 'LpuRegionType_' . $rows['LpuRegionType_id'],
											   'leaf' => false,
											   'object' => 'LpuRegionType',
											   'object_id' => $rows['LpuRegionType_id'],
											   'cls' => 'folder');
							}
						}
						$this->ReturnData($val);
						break;
				}
			}
		}
		else
		{
			switch ($data['object'])
			{
				case 'Diag':
					$Diag_pid = $data['object_id'];
					// диагнозы
					$info = $this->dbmodel->loadDiagList($Diag_pid);
					if ($info != false && count($info) > 0) {
						$val = array();
						foreach ($info as $rows)
						{
							$val[] = array('text' =>
										   toUTF(
												 trim($rows['Diag_Code']) . '. ' . trim($rows['Diag_Name'])
										   ),
										   'id' => 'Diag_' . $rows['Diag_id'],
										   'leaf' => false,
										   'object' => 'Diag',
										   'object_id' => $rows['Diag_id'],
										   'DiagLevel_id' => $rows['DiagLevel_id'],
										   'cls' => 'folder');
						}
						$this->ReturnData($val);
					}
					else
					{
						echo json_encode(array());
					}
					break;
				case 'LpuRegionType':
					$this->load->model("LpuStructure_model", "lsmodel");
					$info = $this->lsmodel->GetLpuRegionNodeList($data);
					$val = array();
					// участки
					if ($info != false && count($info) > 0) {
						foreach ($info as $rows)
						{
							$name = '' . $rows['LpuRegion_Name'] . ' ';
							// Описание 
							if ((!empty($rows['LpuRegion_Descr'])) && ($rows['LpuRegion_Descr'] != 'Null'))
								$name = $name . ' (' . $rows['LpuRegion_Descr'] . ') ';
							$val[] = array('text' =>
										   toUTF($name),
										   'id' => 'LpuRegion_' . $rows['LpuRegion_id'],
										   'leaf' => true,
										   'object' => 'LpuRegion',
										   'object_id' => $rows['LpuRegion_id'],
										   'iconCls' => 'lpu-region16');
						}
					}
					$this->ReturnData($val);
					break;
				case 'LpuBuilding':
					$this->load->model("LpuStructure_model", "lsmodel");
					$info = $this->lsmodel->GetLpuUnitTypeNodeList($data);
					$val = array();
					if ($info != false && count($info) > 0) {
						foreach ($info as $rows)
						{
							$val[] = array('text' =>
										   toUTF(trim($rows['LpuUnitType_Name'])),
										   'id' => 'LpuUnitType_' . $data['object_id'] . "_" . $rows['LpuUnitType_id'],
										   'leaf' => false,
										   'object' => 'LpuUnitType',
										   'object_id' => $data['object_id'] . "_" . $rows['LpuUnitType_id'],
										   'cls' => 'folder');
						}
					}
					$this->ReturnData($val);
					break;
				case 'LpuUnitType':
					$this->load->model("LpuStructure_model", "lsmodel");
					$arr = explode('_', $data['object_id']);
					$data['object_id'] = $arr[0];
					$data['LpuUnitType_id'] = $arr[1];
					$info = $this->lsmodel->GetLpuUnitNodeList($data);
					$val = array();
					if ($info != false && count($info) > 0) {
						foreach ($info as $rows)
						{
							$val[] = array('text' =>
										   toUTF(trim($rows['LpuUnit_Name'])),
										   'id' => 'LpuUnit_' . $rows['LpuUnit_id'],
										   'leaf' => ($rows['leafcount'] > 0 ? false : true),
										   'object' => 'LpuUnit',
										   'object_id' => $rows['LpuUnit_id'],
										   'iconCls' => 'lpu-unit16');
						}
					}
					$this->ReturnData($val);
					break;
				case 'LpuUnit':
					$this->load->model("LpuStructure_model", "lsmodel");
					$info = $this->lsmodel->GetLpuSectionNodeList($data);
					$val = array();
					if ($info != false && count($info) > 0) {
						foreach ($info as $rows)
						{
							$val[] = array('text' =>
										   toUTF(trim($rows['LpuSection_Name'])),
										   'id' => 'LpuSection_' . $rows['LpuSection_id'],
										   'leaf' => ($rows['leafcount'] > 0 ? false : true),
										   'object' => 'LpuSection',
										   'object_id' => $rows['LpuSection_id'],
										   'iconCls' => 'lpu-section16');
						}
					}
					$this->ReturnData($val);
					break;
				case 'LpuSection':
					$this->load->model("LpuStructure_model", "lsmodel");
					$info = $this->lsmodel->GetLpuSectionPidNodeList($data);
					$val = array();
					if ($info != false && count($info) > 0) {
						foreach ($info as $rows)
						{
							$val[] = array('text' =>
										   toUTF(trim($rows['LpuSection_Name'])),
										   'id' => 'LpuSection_' . $rows['LpuSection_id'],
										   'leaf' => true,
										   'object' => 'LpuSectionPid',
										   'object_id' => $rows['LpuSection_id'],
										   'iconCls' => 'lpu-section16');
						}
					}
					$this->ReturnData($val);
					break;
			}
		}
	}

	/**
	 *  Функция добавления медикаментов.
	 *  Входящие данные: $_POST с данными формы
	 *  На выходе: JSON-строка
	 */
	function savePersonDispMedicament()
	{
		$data = $this->ProcessInputData('savePersonDispMedicament', true);
		if ($data) {
			$response = $this->dbmodel->savePersonDispMedicament($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}


	/**
	 *  Функция получения списка диспансерных карт пациентов, в зависимости от фильтров.
	 *  Входящие данные: $_POST с фильтрами
	 *  На выходе: JSON-строка
	 */
	function GetListByTree()
	{
		$this->load->helper('Text');
		$data = $this->ProcessInputData('GetListByTree', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getPersonDispListByTree($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
				}

	/**
	 *  Функция получения списка медикаментов пациентов.
	 *  На выходе: JSON-строка
	 */
	function getPersonDispMedicamentList()
	{
		$this->load->helper('Text');
		
		$data = $this->ProcessInputData('getPersonDispMedicamentList', true);
		if ($data === false) return false;
		
		$PersonDisp_id = $data['PersonDisp_id'];
		$response = $this->dbmodel->getPersonDispMedicamentList($PersonDisp_id);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция сохранения диспансерной карты пациента.
	 *  Входящие данные: $_POST с данными карты
	 */
	function savePersonDisp()
	{
		$this->load->helper('Date');
		$this->load->helper('Text');
		$this->load->helper('Main');

		$val = array();
		$data = $this->ProcessInputData('savePersonDisp', true);
		if ($data === false) return false;

		$response = $this->dbmodel->savePersonDisp($data);
		$this->ProcessModelSave($response, true)->ReturnData();

	}

	/**
	 * Загрузка данных в окно редактирования/добавления диспансерной карты пациента
	 */
	function loadPersonDispEditForm()
	{
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadPersonDispEditForm', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadPersonDispEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 * Удаление диспансерной карты пациента
	 */
	function deletePersonDisp()
	{
		$this->load->helper('Text');

		$data = $this->ProcessInputData('deletePersonDisp', true);
		if ($data === false) return false;

		// проверка назначенных медикаментов
		$cnt_resp = $this->dbmodel->getPersonDispMedicamentCount($data['PersonDisp_id']);
		$medicament_flag = false;
		if (is_array($cnt_resp) && count($cnt_resp) > 0) {
			if ($cnt_resp[0]['cnt'] > 0) {
				$val = array('success' => false, 'Error_Msg' => 'Нельзя удалить дисп. карту, так как имеются назначения медикаментов.');
			}
			else
			{
				$medicament_flag = true;
			}
		}
		else
		{
			$val = array('success' => false, 'Error_Msg' => 'В какой-то момент времени что-то пошло не так');
		}

		if ($medicament_flag) {
			$response = $this->dbmodel->deletePersonDisp($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
	}

	/**
	 * Удаление назначенного медикамента в диспансерной карте пациента
	 */
	function deletePersonDispMedicament()
	{
		$this->load->helper('Text');

		$data = $this->ProcessInputData('deletePersonDispMedicament', true);
		if ($data === false) return false;
		
		$info = $this->dbmodel->deletePersonDispMedicament($data);
	}

	/**
	 * Загрузка грида Диспансерный учет
	 */
	function loadPersonDispGrid()
	{
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadPersonDispGrid', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadPersonDispGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

    /**
     * Получение истории изменений диагонозов карты ДУ
     */
    function loadDiagDispCardHistory(){
        $data = $this->ProcessInputData('loadDiagDispCardHistory',true);
        if ($data === false){
            return false;
        }
        $response = $this->dbmodel->loadDiagDispCardHistory($data);
        $this->ProcessModelList($response,true,true)->ReturnData();
    }

    /**
     *  Загрузыка данных для формы "Диагноз в карте ДУ"
     */
    function loadDiagDispCardEditForm()
    {
        $data = $this->ProcessInputData('loadDiagDispCardEditForm', true);
        if ($data === false) return false;

        $response = $this->dbmodel->loadDiagDispCardEditForm($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }

    /**
     * Удаление строки из истории изменений диагонозов в карте ДУ
     */
    function deleteDiagDispCard(){
        $data = $this->ProcessInputData('deleteDiagDispCard',true);
        if($data === false){
            return false;
        }
        $response = $this->dbmodel->deleteDiagDispCard($data);
        $this->ProcessModelSave($response, true)->ReturnData();
        return true;
    }

    /**
     * Добавление/изменение строки из истории изменений диагонозов в карте ДУ
     */
    function saveDiagDispCard(){
        $data = $this->ProcessInputData('saveDiagDispCard');
        if($data === false){
            return false;
        }
        $response = $this->dbmodel->saveDiagDispCard($data);
        $this->ProcessModelSave($response, true)->ReturnData();
        return true;
    }
	
	/**
	 * Загрузка списка Контроля посещений
	 */
	function loadPersonDispVizitList() {
		$data = $this->ProcessInputData('loadPersonDispVizitList', true, false);
        if ($data === false) return false;
		
		$response = $this->dbmodel->loadPersonDispVizitList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
        return true;
	}
	
	/**
	 * Загрузка Контроля посещений
	 */
	function loadPersonDispVizit() {
		$data = $this->ProcessInputData('loadPersonDispVizit', true, false);
        if ($data === false) return false;
		
		$response = $this->dbmodel->loadPersonDispVizit($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
        return true;
	}

    /**
     * Сохранение Контроля посещений
     */
    function savePersonDispVizit(){
        $data = $this->ProcessInputData('savePersonDispVizit');
        if($data === false){
            return false;
        }
        $response = $this->dbmodel->savePersonDispVizit($data);
        $this->ProcessModelSave($response, true)->ReturnData();
        return true;
    }
	
	/**
	 * Удаление Контроля посещений
	 */
	function delPersonDispVizit() {
		$data = $this->ProcessInputData('delPersonDispVizit', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->delPersonDispVizit($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении')->ReturnData();
	}

	/**
	 * Загрузка списка Сопутствующих диагнозов
	 */
	function loadPersonDispSopDiaglist() {
		$data = $this->ProcessInputData('loadPersonDispSopDiaglist', true, false);
        if ($data === false) return false;
		
		$response = $this->dbmodel->loadPersonDispSopDiaglist($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
        return true;
	}
	
	/**
	 * Загрузка Сопутствующих диагнозов
	 */
	function loadPersonDispSopDiag() {
		$data = $this->ProcessInputData('loadPersonDispSopDiag', true, false);
        if ($data === false) return false;
		
		$response = $this->dbmodel->loadPersonDispSopDiag($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
        return true;
	}

    /**
     * Сохранение Сопутствующих диагнозов
     */
    function savePersonDispSopDiag(){
        $data = $this->ProcessInputData('savePersonDispSopDiag');
        if($data === false){
            return false;
        }
        $response = $this->dbmodel->savePersonDispSopDiag($data);
        $this->ProcessModelSave($response, true)->ReturnData();
        return true;
    }


	/**
	 * Получение номера карты
	 */
	function getPersonDispNumber() {
		$data = $this->ProcessInputData('getPersonDispNumber', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getPersonDispNumber($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}
	
	/**
	 * Удаление Сопутствующих диагнозов
	 */
	function delPersonDispSopDiag() {
		$data = $this->ProcessInputData('delPersonDispSopDiag', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->delPersonDispSopDiag($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении')->ReturnData();
	}

	/**
	 * Загрузка списка врачей, ответственных за наблюдение
	 */
	function loadPersonDispHistlist() {
		$data = $this->ProcessInputData('loadPersonDispHistlist', true, false);
        if ($data === false) return false;
		
		$response = $this->dbmodel->loadPersonDispHistlist($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
        return true;
	}

	/**
	 * Загрузка Ответственного врача
	 */
	function loadPersonDispHist() {
		$data = $this->ProcessInputData('loadPersonDispHist', true, false);
        if ($data === false) return false;
		
		$response = $this->dbmodel->loadPersonDispHist($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
        return true;
	}

	/**
     * Сохранение Ответственного врача
     */
    function savePersonDispHist(){
        $data = $this->ProcessInputData('savePersonDispHist');
        if($data === false){
            return false;
        }
        $response = $this->dbmodel->savePersonDispHist($data);
        $this->ProcessModelSave($response, true)->ReturnData();
        return true;
    }
	
	/**
	 * Удаление Ответственного врача
	 */
	function deletePersonDispHist() {
		$data = $this->ProcessInputData('deletePersonDispHist', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->deletePersonDispHist($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении')->ReturnData();
	}

	/**
	 * Загрузка списка Целевых показателей
	 */
	function loadPersonDispTargetRateList() {
		$data = $this->ProcessInputData('loadPersonDispTargetRateList', true, false);
        if ($data === false) return false;
		
		$response = $this->dbmodel->loadPersonDispTargetRateList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
        return true;
	}

	/**
	 * Загрузка Целевых показателей
	 */
	function loadPersonDispTargetRate() {
		$data = $this->ProcessInputData('loadPersonDispTargetRate', true, false);
        if ($data === false) return false;
		
		$response = $this->dbmodel->loadPersonDispTargetRate($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
        return true;
	}
	
    /**
     * Сохранение Целевых показателей
     */
    function savePersonDispTargetRate(){
        $data = $this->ProcessInputData('savePersonDispTargetRate');
        if($data === false){
            return false;
        }
        $response = $this->dbmodel->savePersonDispTargetRate($data);
        $this->ProcessModelSave($response, true)->ReturnData();
        return true;
    }
    
    /**
     * Удаление фактического показателя
     */
    function deletePersonDispFactRate(){
		$data = $this->ProcessInputData('deletePersonDispFactRate');
		if($data === false){
            return false;
        }
        $response = $this->dbmodel->deletePersonDispFactRate($data);
        //~ $this->ProcessModelSave($response, true)->ReturnData();
        return true;
	}

	/**
	 * Загрузка списка Фактических показателей
	 */
	function loadPersonDispFactRateList() {
		$data = $this->ProcessInputData('loadPersonDispFactRateList', true, false);
        if ($data === false) return false;
		
		$response = $this->dbmodel->loadPersonDispFactRateList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
        return true;
	}

	/**
	 * Получение списка дис. карт пациента
	 * Используется: форма редактирования посещения
	 */
	function loadPersonDispList() {
		$data = $this->ProcessInputData('loadPersonDispList', true);
		if ($data === false) { return false; }

		$info = $this->dbmodel->loadPersonDispList($data);
		$this->ProcessModelList($info, true, true)->ReturnData();

		return true;
	}

	/**
	 * Выгрузка списка карт диспансерного наблюдения за период
	 */
	public function exportPersonDispForPeriod() {
		$this->ReturnError('Регион не поддерживается');
		return true;
	}

	/**
	 * Выгрузка списка карт диспансерного наблюдения
	 */
	public function exportPersonDispCard() {
		set_time_limit(0);

		$data = $this->ProcessInputData('exportPersonDispCard', true);
		if ($data === false) { return false; }

		if ( !isSuperadmin() && !isLpuAdmin($data['Lpu_id']) ) {
			$this->ReturnError('Функционал недоступен');
			return false;
		}

		$this->load->library('textlog', array('file' => 'exportPersonDispCard_' . date('Y-m-d') . '.log'));
		$this->textlog->add("\n\r");
		$this->textlog->add("exportPersonDispForPeriod: Запуск" . "\n\r");
		$this->textlog->add("Регион: " . $data['session']['region']['nick'] . "\n\r");

		$this->textlog->add("Задействовано памяти до выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

		$this->load->model("Polka_PersonCard_model", "pcmodel");

		$fileInfo = $this->pcmodel->getInfoForAttachesFile(array('AttachesLpu_id' => $data['Lpu_id']));

		$exportAllData = $this->dbmodel->exportPersonDispCard($data);

		$links = array();

		if ( $exportAllData == false ) {
			$this->ReturnError('Данные не найдены');
			return false;
		}

		$this->load->library('parser');

		foreach ( $exportAllData as $key => $value ) {
			$exportData = $value;
			$codeSmo = $key;

			if ( !is_array($exportData) ) {
				$this->ReturnError('Ошибка при получении данных');
				return false;
			}
			
			$this->textlog->add("Задействовано памяти после выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

			$path = EXPORTPATH_ROOT . "person_disp_list/";

			if ( !file_exists($path) ) {
				mkdir($path);
			}

			$out_dir = "disp_" . time() . "_" . $data['pmUser_id'] . $codeSmo;

			mkdir($path . $out_dir);

			$file_name = "DS" . $codeSmo . $fileInfo[0]['Lpu_f003mcod'] . "_" . date_format(date_create(date('Ymd')), 'Ymd');

			$file_path = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . ".xml";

			while ( file_exists($file_path) ) {
				$file_path = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . ".xml";
			}

			$file_path_tmp = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . "_tmp.xml";

			while ( file_exists($file_path) ) {
				$file_path_tmp = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . "_tmp.xml";
			}

			// Основные данные
			$i = 0;
			$toFile = array();
			$template = "person_dispcard_body";

			foreach ( $exportData as $row ) {
				$i++;
				$toFile[] = $row;

				if ( count($toFile) == 1000 ) {
					array_walk_recursive($toFile, 'ConvertFromUTF8ToWin1251', true);
					$xml = $this->parser->parse_ext('export_xml/' . $template, array('PERS' => $toFile), true, false, array(), false);
					$xml = str_replace('&', '&amp;', $xml);
					$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
					file_put_contents($file_path_tmp, $xml, FILE_APPEND);
					unset($toFile);
					unset($xml);
					$toFile = array();
				}
			}

			if ( count($toFile) > 0 ) {
				array_walk_recursive($toFile, 'ConvertFromUTF8ToWin1251', true);
				$xml = $this->parser->parse_ext('export_xml/' . $template, array('PERS' => $toFile), true, false, array(), false);
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_path_tmp, $xml, FILE_APPEND);
				unset($toFile);
				unset($xml);
			}

			// Пишем данные в основной файл

			// Заголовок файла
			$template = 'person_dispcard_header';

			$zglv = array(
				 'FILENAME' => $file_name
				,'DATA' => date('Y-m-d')
				,'CODE_MO' => $fileInfo[0]['Lpu_f003mcod']
				,'Q_ZAP' => $i
				,'SMO' => $codeSmo
				,'MONTH' => $data['Month']
				,'YEAR' => $data['Year']
				,'EMP' => ($i == 0 ? 0 : 1)
			);

			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $template, $zglv, true, false, array(), false);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_path, $xml, FILE_APPEND);

			// Тело файла начитываем из временного
			// Заменяем простую, но прожорливую конструкцию, на чтение побайтно
			// https://redmine.swan.perm.ru/issues/51529
			// file_put_contents($file_path, file_get_contents($file_path_tmp), FILE_APPEND);

			if ( file_exists($file_path_tmp) ) {
				$fh = fopen($file_path_tmp, "rb");

				if ( $fh === false ) {
					$this->ReturnError('Ошибка при открытии файла');
					return false;
				}

				// Устанавливаем начитываемый объем данных
				$chunk = 10 * 1024 * 1024; // 10 MB

				while ( !feof($fh) ) {
					file_put_contents($file_path, fread($fh, $chunk), FILE_APPEND);
				}

				fclose($fh);

				unlink($file_path_tmp);
			}

			// Конец файла
			$template = 'person_dispcard_footer';

			$xml = $this->parser->parse('export_xml/' . $template, array(), true);
			$xml = str_replace('&', '&amp;', $xml);
			file_put_contents($file_path, $xml, FILE_APPEND);

			$file_zip_sign = $file_name;
			$file_zip_name = $path . $out_dir . "/" . $file_zip_sign . ".zip";
			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_path, $file_name . ".xml");
			$zip->close();

			unlink($file_path);
			
			$links[] = $file_zip_name;
		}

		if ( !empty($links) ) {
			$this->ReturnData(array('success' => true, 'Links' => $links));
		}
		else {
			$this->ReturnError('Ошибка создания архива!');
		}

		return true;
	}

	/**
	 * Получение списка дисп.учета пациента для ЭМК
	 */
	function loadPersonDispPanel() {
		$data = $this->ProcessInputData('loadPersonDispPanel', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonDispPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}
	
	/**
	 * Получение списка пациентов для дистанционного мониторинга
	 * Используется: форма дистанционный мониторинг (RemoteMonitoringWindow)
	 */
	function loadPersonLabelList() {
		$data = $this->ProcessInputData('loadPersonLabelList', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonLabelList($data);
		if($data['paging'])
			$this->ProcessModelMultiList($response, true, true)->ReturnData(); //постраничный вывод
		else
			//~ $this->ProcessModelList($response, true, true)->ReturnData(); //простой вывод
			$this->ReturnData( $response );
		
		return false;
	}
	
	/**
	 * Количество пациентов в каждой вкладке
	 * Используется: форма дистанционный мониторинг (RemoteMonitoringWindow)
	 */
	function getPersonLabelCounts() {
		$data = $this->ProcessInputData('getPersonLabelCounts', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonLabelCounts($data);
		
		$this->ReturnData( $response );
		
		return false;
	}
	
	/**
	 * Создать карту наблюдения
	 * Используется: форма дистанционный мониторинг (RemoteMonitoringWindow)
	 */
	function createLabelObserveChart() {
		$data = $this->ProcessInputData('createLabelObserveChart', true, true, true);
		if ($data === false) { return false; }
		
		$n = $this->dbmodel->checkOpenedLabelObserveChart($data);
		if($n>0) {
			$result = array('success'=> true, 'Error_Msg'=>'У пациента уже есть открытая карта наблюдения в данном МО');
			$this->ReturnData($result);
		} else {
			$response = $this->dbmodel->createLabelObserveChart($data);
			$this->ProcessModelSave($response)->ReturnData();
		}
		return false;
	}
	
	/**
	 * Получение информации для карты наблюдения пациента
	 * Используется: форма дистанционный мониторинг
	 */
	function getPersonChartInfo() {
		$data = $this->ProcessInputData('getPersonChartInfo', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonChartInfo($data);
		$this->ReturnData($response);
	}
	
	/**
	 * Загрузка данных для формы приглашения в дистанционный мониторинг
	 */
	function getPersonDataFromPortal() {
		$data = $this->ProcessInputData('getPersonDataFromPortal', true, true, true);
		if ($data === false) { return false; }

		$resPersons = $this->dbmodel->getPersonDataFromPortal($data);
		$resHealth = $this->dbmodel->getLpuBuildingHealth($data);
		
		$result = array('success'=>true, 'persons' => $resPersons, 'healthcab' => $resHealth);
		
		$this->ReturnData($result);
	}
	
	/**
	 * Сохранить целевой показатель в карте наблюдения
	 * Используется: форма дистанционный мониторинг
	 */
	function saveLabelObserveChartRate() {
		$data = $this->ProcessInputData('saveLabelObserveChartRate', true, true, true);
		
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLabelObserveChartRate($data);
		$this->ReturnData($response);
	}
	
	/**
	 * Сохранить измерения в карте наблюдения
	 * Используется: форма дистанционный мониторинг (RemoteMonitoringWindow)
	 */
	function saveLabelObserveChartMeasure() {	
		$data = $this->ProcessInputData('saveLabelObserveChartMeasure', true, true, true);
		
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveLabelObserveChartMeasure($data);
		
		$this->ReturnData($response);
	}
	
	/**
	 * Получение измерений для карты наблюдения
	 * Используется: форма дистанционный мониторинг (RemoteMonitoringWindow)
	 */
	function loadLabelObserveChartMeasure() {
		$data = $this->ProcessInputData('loadLabelObserveChartMeasure', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLabelObserveChartMeasure($data);
		
		$result = array('success'=>true, 'data' => $response);
		$this->ReturnData($result);
		
		return true;
	}
	
	/**
	 * Удалить измерение
	 * Используется: форма дистанционный мониторинг (RemoteMonitoringWindow)
	 */
	function deleteLabelObserveChartMeasure() {
		$data = $this->ProcessInputData('deleteLabelObserveChartMeasure', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteLabelObserveChartMeasure($data);
		
		$result = array('success'=>true, 'data' => $response);
		$this->ReturnData($result);
		
		return true;
	}
	
	/**
	 * Сохранить некоторые поля в карте наблюдения
	 */
	function savePersonChartInfo() {
		$data = $this->ProcessInputData('savePersonChartInfo', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->savePersonChartInfo($data);
		
		$result = array('success'=>true, 'data' => $response);
		$this->ReturnData($result);
		
		return true;
	}
	
	/**
	 * Удалить из программы Дистанционный мониторинг
	 */
	function removePersonFromMonitoring() {
		$data = $this->ProcessInputData('removePersonFromMonitoring', true, true, true);
		if ($data === false) { return false; }
		
		$cnt = $this->dbmodel->getMeasuresNumberAfterDate($data);
		if($cnt>0) $result = array('success' => true, 'Error_Msg' => 'Неверная дата исключения: есть замеры позже указанной даты');
		else {
			$response = $this->dbmodel->removePersonFromMonitoring($data);
			if(!$response) $result = array('success'=>true, 'Error_Msg' => 'Что-то пошло не так');
			else $result = array('success'=>true, 'Error_Msg' => '');
		}
		$this->ReturnData($result);
		
		return true;
	}
	
	/**
	 * Пригласить в программу дист.мониторинга
	 */
	function InviteInMonitoring() {
		$data = $this->ProcessInputData('InviteInMonitoring', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->InviteInMonitoring($data);
		$this->ReturnData( $response );
		return true;
	}
	
	/**
	 * ДМ: Изменение статуса приглашения в дист.мониторинг
	 */
	function ChangeLabelInviteStatus() {
		$data = $this->ProcessInputData('ChangeLabelInviteStatus', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->ChangeLabelInviteStatus($data);

		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * ДМ: История включений в программу дистанционного мониторинга
	 * Используется: форма "История включения в программу" (InviteHistoryWindow)
	 */
	function loadLabelInviteHistory() {
		$data = $this->ProcessInputData('loadLabelInviteHistory', true, true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadLabelInviteHistory($data);
		$this->ReturnData( $response );
		return true;
	}
	
	/**
	 * ДМ: Отправить пациенту напоминание
	 */
	function RemindToMonitoring() {
		$data = $this->ProcessInputData('RemindToMonitoring', true, true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->remindToMonitoring($data);
		if($response==false) $result = array('success'=>false, 'Error_Msg' => 'В процессе отправки возникли ошибки');
		elseif($response['Error_Msg']) {
			$result = array('success'=>false, 'Error_Msg' => $response['Error_Msg']);
		}
		else $result = array('success'=>true, 'Error_Msg' => '');
		$this->ReturnData( $result );
		
		return true;
	}
	
	/**
	 * ДМ: Отправить пациенту сообщение
	 */
	function sendLabelMessage() {
		$data = $this->ProcessInputData('sendLabelMessage', true, true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->sendLabelMessage($data);

		if($response==false) $result = array('success'=>false, 'Error_Msg' => $response['Error_Msg']);
		else $result = array('success'=>true, 'Error_Msg' => '');
		$this->ReturnData( $result );
		return true;
	}
	
	/**
	 * Получить сообщения в карте наблюдения
	 * Используется: таблица во вкладке "сообщения" карты наблюдения дист.мониторинга.
	 */
	function loadLabelMessages() {
		$data = $this->ProcessInputData('loadLabelMessages', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLabelMessages($data);
		
		$result = array('success'=>true, 'data' => $response);
		$this->ReturnData($result);
		
		return true;
	}
	
	/**
	 * Создать метку для пациента
	 */
	function createPersonLabel() {
		$data = $this->ProcessInputData('createPersonLabel', true, true, true);
		if ($data === false) { return false; }
		
		if($data['Label_id']==7) { //по температуре сразу создаем и карту наблюдения
			$data2 = array_merge($data, array(
				'Person_id' => $data['Person_id'],
				'PersonDisp_id' => null,
				'allowMailing' => true,
				'Person_Phone' => $data['phone']
			));
			$n = $this->dbmodel->checkOpenedLabelObserveChartByPerson($data2);
			if($n>0) {
				$result = array('success'=> true, 'Error_Msg'=>'У пациента уже есть открытая карта наблюдения в данном МО');
				$this->ReturnData($result);
			} else {
				$response = $this->dbmodel->createPersonLabel($data);
				if(!$response) {
					$result = array('success'=> true, 'Error_Msg'=>'Ошибка при создании метки');
					$this->ReturnData($result);
				} else {
					$data2['PersonLabel_id'] = $response['PersonLabel_id'];
					$response = $this->dbmodel->createLabelObserveChart($data2);
					$this->ProcessModelList($response, true, true)->ReturnData();
				}
			}
		} else {
			$response = $this->dbmodel->createPersonLabel($data);
			if(!$response) {
				$result = array('success'=> true, 'Error_Msg'=>'Ошибка при создании метки');
				$this->ReturnData($result);
			} else {
				$result = array('success'=>true, 'data' => $response);
				$this->ReturnData($result);
			}
		}
		return true;
	}
	
	/**
	 * Заполнение меток пациентов
	 */
	function setLabels() {
		$pmuser_id = 1;
		if(isset($_SESSION['pmuser_id'])) {
			$pmuser_id = $_SESSION['pmuser_id'];
		}
		if(getRegionNick()!='vologda') {
			return false;
		}
		
		$res = $this->dbmodel->setLabels($pmuser_id);
		if ($res === false) {
			throw new Exception('Ошибка при установке меток');
		}
		echo "Скрипт выполнен.";
	}
	
	/**
	 * Получить список открытых карт наблюдения по пациенту
	 * Используется: вкладка "мониторинг" в ЭМК
	 */
	function getLabelObserveCharts() {
		$data = $this->ProcessInputData('getLabelObserveCharts', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLabelObserveCharts($data);
		
		$result = array('success'=>true, 'data' => $response);
		$this->ReturnData($result);
		
		return true;
	}

	/**
	 * Получить список открытых карт наблюдения по пациенту
	 * Используется: вкладка "мониторинг" в ЭМК
	 */
	function checkPersonLabelObserveChartRates() {
		$data = $this->ProcessInputData('checkPersonLabelObserveChartRates', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkPersonLabelObserveChartRates($data);
		$result = array('success'=>true, 'data' => $response);
		$this->ReturnData($result);

		return true;
	}
	
	/**
	 * наличие диспансерной карты с причиной снятия Смерть
	 */
	function getAvailabilityDispensaryCardCauseDeath(){
		$data = $this->ProcessInputData('getAvailabilityDispensaryCardCauseDeath', true, true, true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getAvailabilityDispensaryCardCauseDeath($data);
		
		$result = array('success'=>true, 'data' => $response);
		$this->ReturnData($result);
		
		return true;
	}
	
	/**
	 *  Замена ответственного врача
	 */
	function setResponsibleReplacementOptionsDoctor(){
		$data = $this->ProcessInputData('setResponsibleReplacementOptionsDoctor', true, true, true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->setResponsibleReplacementOptionsDoctor($data);
		
		$result = array('success'=>true, 'data' => $response);
		$this->ReturnData($result);
		
		return true;
	}

	/**
	 *  Список карт д-учёта, подходящих для связи с записью регистра
	 */
	function loadMorbusOnkoSelectList(){
		$data = $this->ProcessInputData('loadMorbusOnkoSelectList');
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadMorbusOnkoSelectList($data);
		
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	
	/**
	 *  Cвязь с записью регистра
	 */
	function savePersonRegisterDispLink(){
		$data = $this->ProcessInputData('savePersonRegisterDispLink', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->savePersonRegisterDispLink($data);
		
		$this->ProcessModelSave( $response, true, true )->ReturnData();
		return true;
	}

	/**
	 * #198097
	 * Выставление риска для АГ
	 */
	function setHypertensionRisk() {
		$result =  $this->dbmodel->setHypertensionRisk();
		$this->ReturnData($result);
		
		return true;
	}

	/**
	 * #198097
	 * Выставление признака превышения
	 */
	function setIsDeviant() {
		$result =  $this->dbmodel->setIsDeviant();
		$this->ReturnData($result);
		
		return true;
	}
}
