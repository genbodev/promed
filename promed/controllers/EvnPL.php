<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPL - контроллер для работы с талонами амбулаторного пациента (ТАП)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package				Polka
 * @copyright			Copyright (c) 2009 Swan Ltd.
 * @author				Stas Bykov aka Savage (savage1981@gmail.com)
 * @link				http://swan.perm.ru/PromedWeb
 * @version				?
 */

/**
 * Класс контроллера для работы с ТАП
 *
 * @package		Common
 * @author		Stas Bykov aka Savage (savage1981@gmail.com)
 * @property Morbus_model Morbus
 * @property MorbusOnkoSpecifics_model MorbusOnkoSpecifics
 * @property EvnPL_model dbmodel
 * @property PersonIdentRequest_model identmodel
 * @property EvnUsluga_model $uslmodel
 * @property EvnVizitPL_model $EvnVizitPL_model
 * @property EvnVizitPLWOW_model $EvnVizitPLWOW_model
 * @property Common_model $Common_model
 */
class EvnPL extends swController
{
	public $baseobject = 'EvnPL';
	protected $inputRules = array(
		'getEvnPLDate' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона амбулаторного пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnVizitPLCombo' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона амбулаторного пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnPLNumber' => array(
			array('field' => 'year', 'label' => 'Год', 'rules' => '', 'type' => 'int')
		),
		'openEvnPL' => array(
			array('field' => 'EvnPL_id', 'label' => 'Идентификатор талона амбулаторного пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'options', 'label' => 'Дополнительные опции', 'rules' => '', 'type' => 'string'),
		),
		'checkIsAssignNasel' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
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
				'field' => 'setDate',
				'label' => 'Дата',
				'rules' => '',
				'type' => 'date'
			)
		),
		'checkLpuHasConsPriemVolume' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'),
			array('field' => 'setDate', 'label' => 'Дата', 'rules' => '', 'type' => 'date'),
		),
		'deleteEvnDiagPL' => array(
			array(
				'field' => 'EvnDiagPL_id',
				'label' => 'Идентификатор диагноза',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteEvnPL' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона амбулаторного пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnPLDiagPanel' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона амбулаторного пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteEvnVizitPL' => array(
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения пациентом поликлиники',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteSpecific' => array(
			array(
				'field' => 'EvnSpecific_id',
				'label' => 'Идентификатор специфики',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEmkEvnPLEditForm' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'loadLast',
				'label' => 'Загружать последнее',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnDiagPLGrid' => array(
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnPLAbortData' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnPLEditForm' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'delDocsView',
				'label' => 'Просмотр удаленных документов',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnVizitPLGrid' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'FormType',
				'label' => 'Тип посещения',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadEvnPLStreamList' => array(
			array(
				'default' => NULL,
				'field' => 'begDate',
				'label' => 'Дата начала',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'default' => NULL,
				'field' => 'begTime',
				'label' => 'Время начала',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальная запись',
				'rules' => '',
				'type' => 'int'
			),
		),
		'saveEvnDiagPL' => array(
			array(
				'field' => 'DeseaseType_id',
				'label' => 'Характер заболевания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDiagPL_id',
				'label' => 'Идентификатор диагноза',
				'rules' => 'trim|required',
				'type' => 'int'
			),
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения пациентом поликлиники',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDiagPL_setDate',
				'label' => 'Дата установки диагноза',
				'rules' => 'trim|required',
				'type' => 'date'
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
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
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
		'saveEvnPLAbort' => array(
			array(
				'field' => 'AbortPlace_id',
				'label' => 'Место',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AbortType_id',
				'label' => 'Тип аборта',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона амбулаторного пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLAbort_id',
				'label' => 'Идентификатор сведений об аборте',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLAbort_IsHIV',
				'label' => 'Обследована на ВИЧ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLAbort_IsInf',
				'label' => 'Наличие ВИЧ-инфекции',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_fedid',
				'label' => 'Фед. результат',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLAbort_IsMed',
				'label' => 'Медикаментозный',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLAbort_PregCount',
				'label' => 'Которая беременность',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLAbort_PregSrok',
				'label' => 'Срок',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLAbort_setDate',
				'label' => 'Дата аборта',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
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
		'addEvnVizitPL' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор ТАП',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => 'required',
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
				'field' => 'TimetableGraf_id',
				'label' => 'Бирка',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Направление',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreDayProfileDuplicateVizit',
				'label' => 'Игнорировать дубликаты посещений',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'ignoreCheckMorbusOnko',
				'label' => 'Признак игнорирования проверки перед удалением специфики',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreControl122430',
				'label' => 'Признак игнорирования проверки по задаче 122430',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'vizit_kvs_control_check',
				'label' => 'Признак игнорирования пересечений КВС',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreEvnUslugaCountCheck',
				'label' => 'Признак игнорирования количества услуг',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreMesUslugaCheck',
				'label' => 'Признак игнорирования проверки МЭС',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreControl59536',
				'label' => 'Признак игнорирования проверки по задаче 59536',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreEvnDirectionProfile',
				'label' => 'Признак игнорирования проверки соответсвия профиля направления профилю посещения',
				'rules' => '',
				'type' => 'int'
			)
		),
		'saveEvnVizitFromEMK' => array(
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPL_setDate',
				'label' => 'Дата посещения',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnVizitPL_setTime',
				'label' => 'Время посещения',
				'rules' => 'required',
				'type' => 'time'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_sid',
				'label' => 'Средний мед. персонал',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentClass_id',
				'label' => 'Вид обращения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ServiceType_id',
				'label' => 'Место',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'VizitType_id',
				'label' => 'Цель посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'VizitClass_id',
				'label' => 'Прием',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedicalCareKind_id',
				'label' => 'Вид мед. помощи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_uid',
				'label' => 'Код посещения',
				'rules' => '',
				'type' => 'id'
			),
			[
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			],
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Основной диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DeseaseType_id',
				'label' => 'Характер заболевания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreDayProfileDuplicateVizit',
				'label' => 'Игнорировать дубликаты посещений',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'ignoreCheckMorbusOnko',
				'label' => 'Признак игнорирования проверки перед удалением специфики',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreControl122430',
				'label' => 'Признак игнорирования проверки по задаче 122430',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreDiagDispCheck',
				'label' => 'Признак игнорирования проверки наличи карты диспансеризации при диагнозе из определенной группы',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Diag_spid',
				'label' => 'Подозрение на диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PainIntensity_id',
				'label' => 'Подозрение на ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TumorStage_id',
				'label' => 'Стадия выявленного ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPL_IsZNO',
				'label' => 'Подозрение на ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispProfGoalType_id',
				'label' => 'В рамках дисп./мед.осмотра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HealthKind_id',
				'label' => 'Группа здоровья',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Mes_id',
				'label' => 'МЭС',
				'rules' => '',
				'type' => 'id'
			),
			[
				'field' => 'PregnancyEvnVizitPL_Period',
				'label' => 'Срок беременности',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'ProfGoal_id',
				'label' => 'Цель профосмотра',
				'rules' => '',
				'type' => 'id'
			]
		),
		'loadEvnPLViewForm' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnPLPerm' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnPLPskov' => array(
			array(
				'default' => NULL,
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона',
				'rules' => '',
				'type' => 'id'
			)
		),
		'printEvnPLHakasiya' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnPLUfa' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnPLBlankPerm' => array(
			array(
				'default' => NULL,
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			)
		),
		'printEvnPLBlankUfa' => array(
			array(
				'default' => NULL,
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => NULL,
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => NULL,
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => NULL,
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => NULL,
				'field' => 'ServiceType_id',
				'label' => 'Место',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => NULL,
				'field' => 'TimetableGraf_id',
				'label' => 'Идентификатор записи',
				'rules' => '',
				'type' => 'id'
			)
		),
		'printEvnPLBlankMsk' => array(
			array(
				'default' => NULL,
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => NULL,
				'field' => 'TimetableGraf_id',
				'label' => 'Идентификатор записи',
				'rules' => '',
				'type' => 'id'
			)
		),
		'printEvnPLBlankPskov' => array(
			array(
				'default' => NULL,
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => NULL,
				'field' => 'TimetableGraf_id',
				'label' => 'Идентификатор записи',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnPLFinishForm' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор ТАП',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getLastVizitDT' => array(
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор посещения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDiagPLStom_id', 'label' => 'Идентификатор стомат. заболевания', 'rules' => '', 'type' => 'id'),
		),
		'checkEvnPlOnDelete' => [
			['field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'],
			['field' => 'Evn_type', 'label' => 'Тип события', 'rules' => 'required', 'type' => 'string']
		],
		'saveEvnPLFinishForm' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор ТАП',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_IsFinish',
				'label' => 'Случай закончен',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_IsSurveyRefuse',
				'label' => 'Отказ от прохождения медицинских обследований',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultClass_id',
				'label' => 'Результат лечения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InterruptLeaveType_id',
				'label' => 'Случай прерван',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_id',
				'label' => 'Исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_UKL',
				'label' => 'УКЛ',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPL_IsFirstDisable',
				'label' => 'Впервые выявленная инвалидность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Впервые выявленная инвалидность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirectType_id',
				'label' => 'Направление',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirectClass_id',
				'label' => 'Куда направлен',
				'rules' => '',
				'type' => 'id'
			),
			['field' => 'LpuSection_oid', 'label' => 'Отделение направления', 'rules' => '', 'type' => 'id'],
			['field' => 'Lpu_oid', 'label' => 'МО направления', 'rules' => '', 'type' => 'id'],
			array(
				'field' => 'Diag_lid',
				'label' => 'Закл. диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_concid',
				'label' => 'Закл. внешняя причина',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospTrauma_id',
				'label' => 'Вид травмы (внеш. возд)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_IsUnlaw',
				'label' => 'Противоправная',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_IsUnport',
				'label' => 'Нетранспортабельность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_fedid',
				'label' => 'Фед. результат',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_fedid',
				'label' => 'Фед. исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreEvnDirectionProfile',
				'label' => 'Признак игнорирования проверки соответсвия профиля направления профилю посещения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreMorbusOnkoDrugCheck',
				'label' => 'Признак игнорирования проверки препаратов в онко заболевании',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreParentEvnDateCheck',
				'label' => 'Признак игнорирования проверки периода выполенения услуги',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'vizit_intersection_control_check',
				'label' => 'Признак игнорирования контроля пересечения посещений',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreMesUslugaCheck',
				'label' => 'Признак игнорирования проверки МЭС',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreFirstDisableCheck',
				'label' => 'Признак игнорирования проверки первичности инвалидности',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreCheckEvnUslugaChange',
				'label' => 'Признак игнорирования проверки изменения привязок услуг',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreCheckB04069333',
				'label' => 'Признак игнорирования проверок по услуге B04.069.333',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreCheckTNM',
				'label' => 'Признак игнорирования проверок по соответствию диагноза и TNM',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreDiagDispCheck',
				'label' => 'Признак игнорирования проверки наличи карты диспансеризации при диагнозе из определенной группы',
				'rules' => '',
				'type' => 'int'
			)
		),
		'checkEvnVizitsPL' => array(
			array('field' => 'closeAPL', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля', 'rules' => '', 'type' => 'id'),
		),
		'saveEvnDiagHSNDetails' => array(
			array(
				'field' => 'DiagHSNDetails_id',
				'label' => 'Идентификатор диагноза ХСН',
				'rules' => '',
				'type' => 'id'
		),
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
		),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор основного диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HSNStage_id',
				'label' => 'Идентификатор стадии ХСН для основного диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HSNFuncClass_id',
				'label' => 'Идентификатор функционального класса для основного диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_agid',
				'label' => 'Идентификатор осложнения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ComplDiagHSNStage_id',
				'label' => 'Идентификатор стадии ХСН для осложнения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ComplDiagHSNFuncClass_id',
				'label' => 'Идентификатор функционального класса для осложнения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getLastHsnDetails' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			)
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnPL_model', 'dbmodel');
	}


	/**
	 * Удаление сопутствующего диагноза
	 * Входящие данные: $_POST['EvnDiagPL_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования посещения пациентом поликлиники
	 */
	function deleteEvnDiagPL()
	{
		$data = $this->ProcessInputData('deleteEvnDiagPL', true);
		if ($data === false) {
			return false;
		}
		$this->load->model('EvnPL_model', 'EvnPL_model');
		$DiagData = $this->EvnPL_model->getDiagData(['EvnDiag_id' => $data['EvnDiagPL_id']]);

		$response = $this->dbmodel->deleteEvnDiagPL($data);

		if (!empty($response) && empty($response['Error_Msg']) && !empty($DiagData)) {
			$params = $data;
			$params['EvnPL_id'] = $DiagData['EvnDiag_rid'];
			$params['source'] = 'EvnPL';
			$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
			$this->CVIRegistry_model->saveCVIEvent($params);
		}
		$this->ProcessModelSave($response, true, 'При удалении сопутствующего диагноза возникли ошибки')->ReturnData();

		return true;
	}


	/**
	 * Удаление талона амбулаторного пациента
	 * Входящие данные: $_POST['EvnPL_id']
	 * На выходе: JSON-строка
	 * Используется: форма поиска талона амбулаторного пациента
	 */
	function deleteEvnPL()
	{
		$data = $this->ProcessInputData('deleteEvnPL', true);
		if ($data === false) {
			return false;
		}
		/*
		// Проверка есть ли в реестрах записи об этом случае
		unset($this->db);
		$this->load->database('registry');
		$this->load->model('Registry_model', 'Reg_model');
		$registryData = $this->Reg_model->checkEvnInRegistry($data);
		if(is_array($registryData)) {
			$this->ProcessModelSave($registryData, true)->ReturnData();
			return false;
		}
		//var_dump($registryData); exit(); //
		unset($this->db);
		
		$this->load->database();
		*/
		$this->load->model('Stick_model', 'stmodel');
		// https://redmine.swan.perm.ru/issues/5992
		// Пункт 5
		$response = $this->stmodel->checkEvnDeleteAbility(array('Evn_id' => $data['EvnPL_id']));
		/*
		if ( is_array($response) && count($response) > 0 ) {
			$error = '<div>Удаление ТАП невозможно в виду использования ЛВН:</div>';

			foreach ( $response as $array ) {
				$error .= "<div>"
					. $array['EvnStick_Ser'] . " "
					. $array['EvnStick_Num'] . ", выдан "
					. $array['EvnStick_setDate'] . " в "
					. $array['ParentEvn_Type'] . " № "
					. $array['ParentEvn_NumCard']
					. "</div>"
				;
			}

			echo json_return_errors($error);
			return false;
		}
		*/

		if (is_array($response) && count($response) > 0) {
			$error = '<div>Удаление ТАП невозможно, документ содержит ЛВН ';

			$first = true;
			foreach ($response as $array) {
				if (!$first) {
					$error .= ", ";
				}
				$error .= "№" . $array['EvnStick_Ser'] . " " . $array['EvnStick_Num'] . " дата " . $array['EvnStick_setDate'];
				$first = false;
			}

			$error .= "</div>";

			echo json_return_errors($error);
			return false;
		}

		$response = $this->dbmodel->deleteEvnPL($data);
		$this->ProcessModelSave($response, true, 'При удалении талона возникли ошибки')->ReturnData();

		return true;
	}


	/**
	 * Удаление посещения пациентом поликлиники
	 * Входящие данные: $_POST['EvnVizitPL_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function deleteEvnVizitPL()
	{
		$this->load->model('EvnVizit_model', 'evmodel');
		$this->load->model('Polka_PersonDisp_model', 'PersonDisp_model');

		$data = $this->ProcessInputData('deleteEvnVizitPL', true);
		if ($data === false) {
			return false;
		}

		$result = $this->PersonDisp_model->getPersonDispVizitId($data);
		if (!empty($result['PersonDispVizit_id'])) {
			$this->PersonDisp_model->delPersonDispVizit($data);
		}

		$response = $this->evmodel->deleteEvnVizitPL($data);
		$this->ProcessModelSave($response, true, 'При удалении посещения пациентом поликлиники возникли ошибки');
		$this->ReturnData();
		return true;
	}


	/**
	 * Удаление сведений об аборте
	 * Входящие данные: $_POST['EvnSpecific_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function deleteEvnPLAbort()
	{
		$data = $this->ProcessInputData('deleteSpecific', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->deleteEvnPLAbort($data);
		$this->ProcessModelSave($response, true, 'При удалении сведений об аборте возникли ошибки')->ReturnData();

		return true;
	}

	/**
	 * Получение номера талона амбулаторного пациента
	 * Входящие данные: нет
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function getEvnPLNumber()
	{
		$data = $this->ProcessInputData('getEvnPLNumber', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getEvnPLNumber($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение даты ТАП (используется для печати ТАП)
	 */
	function getEvnPLDate()
	{
		$data = $this->ProcessInputData('getEvnPLDate', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getEvnPLDate($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка посещений ТАП
	 */
	function loadEvnVizitPLCombo()
	{
		$data = $this->ProcessInputData('loadEvnVizitPLCombo', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEvnVizitPLCombo($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения списка посещений ТАП')->ReturnData();

		return true;
	}

	/**
	 * Получение списка сопутствующих диагнозов
	 * Входящие данные: $_POST['EvnVizitPL_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования посещения пациентом поликлиники
	 */
	function loadEvnDiagPLGrid()
	{
		$data = $this->ProcessInputData('loadEvnDiagPLGrid', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEvnDiagPLGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных по специфике "Сведения об аборте"
	 * Входящие данные: $_POST['EvnPL_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function loadEvnPLAbortData()
	{
		$data = $this->ProcessInputData('loadEvnPLAbortData', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEvnPLAbortData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных для формы редактирования ТАП, вызываемой из ЭМК
	 * Входящие данные: $_POST['EvnPL_id'], $_POST['EvnVizitPL_id']
	 * На выходе: JSON-строка
	 * Используется: дополнительная форма редактирования ТАП
	 */
	function loadEmkEvnPLEditForm()
	{
		$data = $this->ProcessInputData('loadEmkEvnPLEditForm', true, true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEmkEvnPLEditForm($data);
		$this->ProcessModelList($response)->ReturnData();

		return true;
	}


	/**
	 * Получение данных для формы редактирования ТАП
	 * Входящие данные: $_POST['EvnPL_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования ТАП
	 */
	function loadEvnPLEditForm()
	{
		$data = $this->ProcessInputData('loadEvnPLEditForm', true, true);
		if ($data === false) {
			return false;
		}

		if($data['delDocsView'] && $data['delDocsView'] == 1)
			$response = $this->dbmodel->loadEvnPLEditFormForDelDocs($data);
		else
			$response = $this->dbmodel->loadEvnPLEditForm($data);
		
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}


	/**
	 * Получение списка ТАП для потокового ввода
	 * Входящие данные: $_POST['begDate'],
	 *                  $_POST['begTime']
	 * На выходе: JSON-строка
	 * Используется: форма потокового ввода ТАП
	 */
	function loadEvnPLStreamList()
	{
		$data = $this->ProcessInputData('loadEvnPLStreamList', true);
		if ($data === false) {
			return false;
		}

		$outdata = array();
		$response = $this->dbmodel->loadEvnPLStreamList($data);
		$outdata['data'] = $this->ProcessModelList($response, true, true)->GetOutData();
		$this->ReturnData($outdata);

		return true;
	}


	/**
	 * Получение списка посещений пациентом поликлиники
	 * Входящие данные: $_POST['EvnPL_id'],
	 *                  $_POST['EvnVizitPL_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 *               электронный паспорт здоровья
	 */
	function loadEvnVizitPLGrid()
	{
		$data = $this->ProcessInputData('loadEvnVizitPLGrid', true, true);
		if ($data === false) {
			return false;
		}

		if ((!isset($data['EvnPL_id'])) && (!isset($data['EvnVizitPL_id']))) {
			echo json_return_errors('Не задан идентификатор родительского события');
			return false;
		}

		$response = $this->dbmodel->loadEvnVizitPLGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	 * Печать талона амбулаторного пациента
	 * Входящие данные: $_GET['EvnPL_id']
	 * На выходе: форма для печати талона амбулаторного пациента
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function printEvnPL()
	{
		switch ($_SESSION['region']['nick']) {
			case 'perm':
				$this->printEvnPLPerm();
				break;

			case 'ufa':
				$this->printEvnPLUfa();
				break;

			case 'khak':
				$this->printEvnPLHakasiya();
				break;

			case 'pskov':
				$this->printEvnPLPskov();
				break;

			case 'kareliya':
				$this->printEvnPLKareliya();
				break;

			case 'astra':
				$this->printEvnPLAstra();
				break;

			default:
				//echo "Не указан регион";
				$this->printEvnPLPerm();
				break;
		}

		return true;
	}


	/**
	 * Печать талона амбулаторного пациента (Пермский край)
	 * Входящие данные: $_GET['EvnPL_id']
	 * На выходе: форма для печати талона амбулаторного пациента
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function printEvnPLPerm($EvnPL_id = null, $ReturnString = false)
	{
		$this->load->helper('Options');
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLPerm', true);
		if ($data === false) {
			return false;
		}

		// Получаем настройки
		$options = getOptions();

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLFieldsPerm($data);

		if (!is_array($response) || count($response) == 0) {
			echo 'Ошибка при получении данных по талону';
			return true;
		}

		$evn_diag_pl_osn_data = array();
		$evn_diag_pl_sop_data = array();
		$evn_stick_data = array();
		$evn_stick_work_release_data = array();
		$evn_vizit_pl_data = array();
		$person_disp_data = array();

		$response_temp = $this->dbmodel->getEvnVizitPLDataPerm($data);
		if (is_array($response_temp)) {
			$evn_vizit_pl_data = $response_temp;

			if (count($evn_vizit_pl_data) < 4) {
				for ($i = count($evn_vizit_pl_data); $i < 4; $i++) {
					$evn_vizit_pl_data[$i] = array(
						'EVPL_EvnVizitPL_setDate' => '&nbsp;',
						'EVPL_LpuSection_Code' => '&nbsp;',
						'EVPL_MedPersonal_Fio' => '&nbsp;',
						'EVPL_MidMedPersonal_Code' => '&nbsp;',
						'EVPL_EvnVizitPL_Name' => '&nbsp;',
						'EVPL_ServiceType_Name' => '&nbsp;',
						'EVPL_VizitType_Name' => '&nbsp;',
						'EVPL_PayType_Name' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->dbmodel->getEvnDiagPLOsnData($data);
		if (is_array($response_temp)) {
			$evn_diag_pl_osn_data = $response_temp;

			if (count($evn_diag_pl_osn_data) < 2) {
				for ($i = count($evn_diag_pl_osn_data); $i < 2; $i++) {
					$evn_diag_pl_osn_data[$i] = array(
						'EvnDiagPL_setDate' => '&nbsp;',
						'LpuSection_Name' => '&nbsp;',
						'MedPersonal_Code' => '&nbsp;',
						'Diag_Code' => '&nbsp;',
						'DeseaseType_Name' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->dbmodel->getEvnDiagPLSopData($data);
		if (is_array($response_temp)) {
			$evn_diag_pl_sop_data = $response_temp;

			if (count($evn_diag_pl_sop_data) < 2) {
				for ($i = count($evn_diag_pl_sop_data); $i < 2; $i++) {
					$evn_diag_pl_sop_data[$i] = array(
						'EvnDiagPL_setDate' => '&nbsp;',
						'LpuSection_Name' => '&nbsp;',
						'MedPersonal_Code' => '&nbsp;',
						'Diag_Code' => '&nbsp;',
						'DeseaseType_Name' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->dbmodel->getEvnStickData($data);
		if (is_array($response_temp)) {
			$evn_stick_data = $response_temp;

			if (count($evn_stick_data) < 2) {
				for ($i = count($evn_stick_data); $i < 2; $i++) {
					$evn_stick_data[$i] = array(
						'EvnStick_begDate' => '&nbsp;',
						'EvnStick_endDate' => '&nbsp;',
						'StickType_Name' => '&nbsp;',
						'EvnStick_Ser' => '&nbsp;',
						'EvnStick_Num' => '&nbsp;',
						'StickCause_Name' => '&nbsp;',
						'StickIrregularity_Name' => '&nbsp;',
						'Sex_Name' => '&nbsp;',
						'EvnStick_Age' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->dbmodel->getEvnStickWorkReleaseData($data);
		if (is_array($response_temp)) {
			$evn_stick_work_release_data = $response_temp;

			if (count($evn_stick_work_release_data) < 4) {
				for ($i = count($evn_stick_work_release_data); $i < 4; $i++) {
					$evn_stick_work_release_data[$i] = array(
						'EvnStick_SerNum' => '&nbsp;',
						'EvnStickWorkRelease_begDate' => '&nbsp;',
						'EvnStickWorkRelease_endDate' => '&nbsp;',
						'MedPersonal_Fio' => '&nbsp;'
					);
				}
			}
		}

		for ($i = count($person_disp_data); $i < 2; $i++) {
			$person_disp_data[$i] = array(
				'PersonDisp_Name' => '&nbsp;',
				'Diag_Code' => '&nbsp;',
				'PersonDisp_nextDate' => '&nbsp;',
				'PersonDisp_begDate' => '&nbsp;',
				'PersonDisp_endDate' => '&nbsp;',
				'DispOutType_Name' => '&nbsp;'
			);
		}

		$template = 'evn_pl_template_list_a4_perm';

		$print_data = array(
			'DirectOrg_Name' => returnValidHTMLString($response[0]['DirectOrg_Name']),
			'DirectClass_Name' => returnValidHTMLString($response[0]['DirectClass_Name']),
			'DirectType_Name' => returnValidHTMLString($response[0]['DirectType_Name']),
			'Document_begDate' => returnValidHTMLString($response[0]['Document_begDate']),
			'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
			'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
			'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
			'EvnDiagPLOsnData' => $evn_diag_pl_osn_data,
			'EvnDiagPLSopData' => $evn_diag_pl_sop_data,
			'EvnPL_IsFinish' => $response[0]['EvnPL_IsFinish'] == 1 ? 'X' : '&nbsp;',
			'EvnPL_IsNotFinish' => $response[0]['EvnPL_IsFinish'] == 1 ? '&nbsp;' : 'X',
			'EvnPL_IsUnlaw' => $response[0]['EvnPL_IsUnlaw'] == 1 ? 'X' : '&nbsp;',
			'EvnPL_IsUnport' => $response[0]['EvnPL_IsUnport'] == 1 ? 'X' : '&nbsp;',
			'EvnPL_NumCard' => returnValidHTMLString($response[0]['EvnPL_NumCard']),
			'EvnPL_UKL' => $response[0]['EvnPL_UKL'] > 0 ? $response[0]['EvnPL_UKL'] : '&nbsp;',
			'EvnPLTemplateTitle' => 'Печать талона амбулаторного пациента',
			'EvnStickData' => $evn_stick_data,
			'EvnStickWorkReleaseData' => $evn_stick_work_release_data,
			'EvnUdost_Num' => returnValidHTMLString($response[0]['EvnUdost_Num']),
			'EvnUdost_Ser' => returnValidHTMLString($response[0]['EvnUdost_Ser']),
			'EvnVizitPLData' => $evn_vizit_pl_data,
			'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
			'Org_Name' => returnValidHTMLString($response[0]['Org_Name']),
			'OrgDep_Name' => returnValidHTMLString($response[0]['OrgDep_Name']),
			'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
			'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name']),
			'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
			'Person_Fio' => mb_strtoupper(returnValidHTMLString($response[0]['Person_Fio'])),
			'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
			'PersonDispData' => $person_disp_data,
			'PersonPrivilege_begDate' => returnValidHTMLString($response[0]['PersonPrivilege_begDate']),
			'Polis_begDate' => returnValidHTMLString($response[0]['Polis_begDate']),
			'Polis_endDate' => returnValidHTMLString($response[0]['Polis_endDate']),
			'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
			'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
			'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name']),
			'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
			'PrehospDiag_Code' => returnValidHTMLString($response[0]['PrehospDiag_Code']),
			'PrehospDiag_Name' => returnValidHTMLString($response[0]['PrehospDiag_Name']),
			'PrehospDirect_Name' => returnValidHTMLString($response[0]['PrehospDirect_Name']),
			'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name']),
			'PrehospTrauma_Name' => returnValidHTMLString($response[0]['PrehospTrauma_Name']),
			'PrivilegeType_Name' => returnValidHTMLString($response[0]['PrivilegeType_Name']),
			'ResultClass_Name' => returnValidHTMLString($response[0]['ResultClass_Name']),
			'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
			'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
			'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
		);

		if (defined('USE_UTF') && USE_UTF) {
			$print_data['Person_Fio'] = mb_strtoupper(returnValidHTMLString($response[0]['Person_Fio']));
		}

		return $this->parser->parse($template, $print_data, $ReturnString, false, (defined('USE_UTF') && USE_UTF));
	}

	/**
	 * Печать талона амбулаторного пациента (Псков)
	 * Входящие данные: $_GET['EvnPL_id']
	 * На выходе: форма для печати талона амбулаторного пациента
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function printEvnPLPskov($EvnPL_id = null, $ReturnString = false)
	{
		$this->load->helper('Options');
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLPskov', true);
		if ($data === false) {
			return false;
		}

		// Получаем настройки
		$options = getOptions();

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLFieldsPskov($data);
		$response_usluga = $this->dbmodel->getEvnUslugaDataPskov($data);
		if (!is_array($response) || count($response) == 0) {
			echo 'Ошибка при получении данных по талону';
			return true;
		}

		$evn_diag_pl_osn_data = array();
		$evn_diag_pl_sop_data = array();
		$evn_stick_data = array();
		$evn_stick_work_release_data = array();
		$evn_vizit_pl_data = array();
		$person_disp_data = array();

		$response_temp = $this->dbmodel->getEvnVizitPLDataPerm($data);
		if (is_array($response_temp)) {
			$evn_vizit_pl_data = $response_temp;

			if (count($evn_vizit_pl_data) < 4) {
				for ($i = count($evn_vizit_pl_data); $i < 4; $i++) {
					$evn_vizit_pl_data[$i] = array(
						'EVPL_EvnVizitPL_setDate' => '&nbsp;',
						'EVPL_LpuSection_Code' => '&nbsp;',
						'EVPL_MedPersonal_Fio' => '&nbsp;',
						'EVPL_MidMedPersonal_Code' => '&nbsp;',
						'EVPL_EvnVizitPL_Name' => '&nbsp;',
						'EVPL_ServiceType_Name' => '&nbsp;',
						'EVPL_VizitType_Name' => '&nbsp;',
						'EVPL_PayType_Name' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->dbmodel->getEvnDiagPLOsnData($data);
		if (is_array($response_temp)) {
			$evn_diag_pl_osn_data = $response_temp;

			if (count($evn_diag_pl_osn_data) < 2) {
				for ($i = count($evn_diag_pl_osn_data); $i < 2; $i++) {
					$evn_diag_pl_osn_data[$i] = array(
						'EvnDiagPL_setDate' => '&nbsp;',
						'LpuSection_Name' => '&nbsp;',
						'MedPersonal_Code' => '&nbsp;',
						'Diag_Code' => '&nbsp;',
						'DeseaseType_Name' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->dbmodel->getEvnDiagPLSopData($data);
		if (is_array($response_temp)) {
			$evn_diag_pl_sop_data = $response_temp;

			if (count($evn_diag_pl_sop_data) < 2) {
				for ($i = count($evn_diag_pl_sop_data); $i < 2; $i++) {
					$evn_diag_pl_sop_data[$i] = array(
						'EvnDiagPL_setDate' => '&nbsp;',
						'LpuSection_Name' => '&nbsp;',
						'MedPersonal_Code' => '&nbsp;',
						'Diag_Code' => '&nbsp;',
						'DeseaseType_Name' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->dbmodel->getEvnStickData($data);
		if (is_array($response_temp)) {
			$evn_stick_data = $response_temp;

			if (count($evn_stick_data) < 2) {
				for ($i = count($evn_stick_data); $i < 2; $i++) {
					$evn_stick_data[$i] = array(
						'EvnStick_begDate' => '&nbsp;',
						'EvnStick_endDate' => '&nbsp;',
						'StickType_Name' => '&nbsp;',
						'EvnStick_Ser' => '&nbsp;',
						'EvnStick_Num' => '&nbsp;',
						'StickCause_Name' => '&nbsp;',
						'StickIrregularity_Name' => '&nbsp;',
						'Sex_Name' => '&nbsp;',
						'EvnStick_Age' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->dbmodel->getEvnStickWorkReleaseData($data);
		if (is_array($response_temp)) {
			$evn_stick_work_release_data = $response_temp;

			if (count($evn_stick_work_release_data) < 4) {
				for ($i = count($evn_stick_work_release_data); $i < 4; $i++) {
					$evn_stick_work_release_data[$i] = array(
						'EvnStick_SerNum' => '&nbsp;',
						'EvnStickWorkRelease_begDate' => '&nbsp;',
						'EvnStickWorkRelease_endDate' => '&nbsp;',
						'MedPersonal_Fio' => '&nbsp;'
					);
				}
			}
		}

		for ($i = count($person_disp_data); $i < 2; $i++) {
			$person_disp_data[$i] = array(
				'PersonDisp_Name' => '&nbsp;',
				'Diag_Code' => '&nbsp;',
				'PersonDisp_nextDate' => '&nbsp;',
				'PersonDisp_begDate' => '&nbsp;',
				'PersonDisp_endDate' => '&nbsp;',
				'DispOutType_Name' => '&nbsp;'
			);
		}

		$template = 'evn_pl_template_list_a4_pskov';

		if ($response[0]['EvnPL_IsFinish'] == 0) {
			$response[0]['MedPersonalLast_TabCode'] = '';
			$response[0]['MedPersonalLast_Fio'] = '';
		}
		$print_data = array(
			'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
			'OrgUnion_Name' => returnValidHTMLString($response[0]['OrgUnion_Name']),
			'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
			'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
			'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
			'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
			'Document_begDate' => returnValidHTMLString($response[0]['Document_begDate']),
			'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name']),
			'KLAreaType_Code' => returnValidHTMLString($response[0]['KLAreaType_Code']),
			'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
			'SocStatus_Code' => returnValidHTMLString($response[0]['SocStatus_Code']),
			'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
			'PrivilegeType_Name' => '',
			'PersonPrivilege_begDate' => '',
			'DeseaseTypeSop_Code' => returnValidHTMLString($response[0]['DeseaseTypeSop_Code']),
			'DiagAgg_Code' => returnValidHTMLString($response[0]['DiagAgg_Code']),
			'DiagSop_Code' => returnValidHTMLString($response[0]['DiagSop_Code']),
			'EvnPL_NumCard' => returnValidHTMLString($response[0]['EvnPL_NumCard']),
			'EvnPL_IsFinish' => returnValidHTMLString($response[0]['EvnPL_IsFinish']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'Lpu_OGRN' => returnValidHTMLString($response[0]['Lpu_OGRN']),
			'Sex_id' => returnValidHTMLString($response[0]['Sex_id']),
			'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
			'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
			'Lpu_Address' => returnValidHTMLString($response[0]['Lpu_Address']),
			'EvnPL_setDate' => returnValidHTMLString($response[0]['EvnPL_setDate']),
			'EvnPLTemplateTitle' => 'Печать талона амбулаторного пациента',
			'EvnStick_Age' => returnValidHTMLString($response[0]['EvnStick_Age']),
			'EvnStick_begDate' => returnValidHTMLString($response[0]['EvnStick_begDate']),
			'EvnStick_endDate' => returnValidHTMLString($response[0]['EvnStick_endDate']),
			'EvnStick_Open' => returnValidHTMLString($response[0]['EvnStick_Open']),
			'EvnStick_Sex' => returnValidHTMLString($response[0]['EvnStick_Sex']),
			'FinalDiag_Code' => returnValidHTMLString($response[0]['FinalDiag_Code']),
			'FinalDeseaseType_Code' => returnValidHTMLString($response[0]['FinalDeseaseType_Code']),
			'KlareaType_id' => returnValidHTMLString($response[0]['KlareaType_id']),
			'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
			'LpuSectionProfile_Code' => returnValidHTMLString($response[0]['LpuSectionProfile_Code']),
			'LpuSectionProfile_Name' => returnValidHTMLString($response[0]['LpuSectionProfile_Name']),
			'MedPersonal_Fio' => returnValidHTMLString($response[0]['MedPersonal_Fio']),
			'MedPersonal2_Fio' => returnValidHTMLString($response[0]['MedPersonalLast_Fio']),
			'MedPersonal_TabCode' =>
				(!empty($response[0]['MedPersonal_TabCode']))
					? '<td style="border: 1px solid" colspan="10">' . returnValidHTMLString($response[0]['MedPersonal_TabCode']) . '</td>'
					: '<td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td>',
			'MedPersonal2_TabCode' =>
				(!empty($response[0]['MedPersonalLast_TabCode']))
					? '<td style="border: 1px solid" colspan="10">' . returnValidHTMLString($response[0]['MedPersonalLast_TabCode']) . '</td>'
					: '<td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td>',
			'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
			'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
			'PayType_Code' => returnValidHTMLString($response[0]['PayType_Code']),
			'Person_Address' => returnValidHTMLString($response[0]['PAddress_Name']),
			'Person_Docum' => returnValidHTMLString($response[0]['DocumentType_Name']) . ' ' . returnValidHTMLString($response[0]['Document_Ser']) . ' ' . returnValidHTMLString($response[0]['Document_Num']),
			'Person_Fio' => mb_strtoupper(returnValidHTMLString($response[0]['Person_Fio'])),
			'Person_INN' => returnValidHTMLString($response[0]['Person_INN']),
			'Person_Snils' => returnValidHTMLString($response[0]['Person_Snils']),
			'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
			'Polis_begDate' => returnValidHTMLString($response[0]['Polis_begDate']),
			'Polis_endDate' => returnValidHTMLString($response[0]['Polis_endDate']),
			'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
			'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
			'PrehospDiag_Code' => returnValidHTMLString($response[0]['PrehospDiag_Code']),
			'PrehospDiag_regDate' => returnValidHTMLString($response[0]['PrehospDiag_regDate']),
			'ServiceType_Code' => returnValidHTMLString($response[0]['ServiceType_Code']),
			'ResultClass_Code' => returnValidHTMLString($response[0]['ResultClass_Code']),
			'PrehospTrauma_Code' => returnValidHTMLString($response[0]['PrehospTrauma_Code'])
		);
		for ($i = 1; $i <= 51; $i++) {
			for ($j = 1; $j <= 7; $j++)
				$print_data['c' . $i . $j] = '&nbsp;';
		}

		for ($i = 1; $i <= count($response_usluga); $i++) {
			for ($j = 1; $j <= strlen($response_usluga[$i - 1]['UslugaComplex_Code']); $j++)
				$print_data['c' . $i . $j] = $response_usluga[$i - 1]['UslugaComplex_Code'][$j - 1];
		}
		if (!empty($response[0]['PrivilegeType_Name'])) {
			$print_data['PrivilegeType_Name'] = returnValidHTMLString($response[0]['PrivilegeType_Name']) . ', установлена ' . returnValidHTMLString($response[0]['PersonPrivilege_begDate']);
		}

		switch ($response[0]['VizitType_SysNick']) {
			case 'desease':
				$print_data['VizitType_Code'] = 1;
				break;

			case 'consul':
				$print_data['VizitType_Code'] = 2;
				break;

			case 'disp':
				$print_data['VizitType_Code'] = 3;
				break;

			case 'prof':
				$print_data['VizitType_Code'] = 5;
				break;

			case 'dd':
				$print_data['VizitType_Code'] = 10;
				break;

			case 'soc':
				$print_data['VizitType_Code'] = 11;
				break;

			default:
				$print_data['VizitType_Code'] = 0;
		}

		return $this->parser->parse($template, $print_data, $ReturnString);
	}

	/**
	 * Печать талона амбулаторного пациента (Карелия)
	 * Входящие данные: $_GET['EvnPL_id']
	 * На выходе: форма для печати талона амбулаторного пациента
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function printEvnPLKareliya($EvnPL_id = null, $ReturnString = false)
	{
		$this->load->helper('Options');
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLPskov', true);
		if ($data === false) {
			return false;
		}

		// Получаем настройки
		$options = getOptions();

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLFieldsKareliya($data);

		if (!is_array($response) || count($response) == 0) {
			echo 'Ошибка при получении данных по талону';
			return true;
		}

		$template = 'evn_pl_template_list_a4_kareliya';

		$print_data = array(
			'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
			'OrgUnion_Name' => returnValidHTMLString($response[0]['OrgUnion_Name']),
			'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
			'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
			'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
			'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
			'Document_begDate' => returnValidHTMLString($response[0]['Document_begDate']),
			'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name']),
			'KLAreaType_Code' => returnValidHTMLString($response[0]['KLAreaType_Code']),
			'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
			'SocStatus_Code' => returnValidHTMLString($response[0]['SocStatus_Code']),
			'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
			'PrivilegeType_Name' => '',
			'PersonPrivilege_begDate' => '',
			'DeseaseTypeSop_Code' => returnValidHTMLString($response[0]['DeseaseTypeSop_Code']),
			'DiagAgg_Code' => returnValidHTMLString($response[0]['DiagAgg_Code']),
			'DiagSop_Code' => returnValidHTMLString($response[0]['DiagSop_Code']),
			'EvnPL_NumCard' => returnValidHTMLString($response[0]['EvnPL_NumCard']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'LpuAddress' => returnValidHTMLString($response[0]['LpuAddress']),
			'Lpu_OGRN' => returnValidHTMLString($response[0]['Lpu_OGRN']),
			'Sex_Code' => returnValidHTMLString($response[0]['Sex_Code']),
			'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
			'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
			'EvnPL_setDate' => returnValidHTMLString($response[0]['EvnPL_setDate']),
			'EvnPLTemplateTitle' => 'Печать талона амбулаторного пациента',
			'EvnStick_Age' => returnValidHTMLString($response[0]['EvnStick_Age']),
			'EvnStick_begDate' => returnValidHTMLString($response[0]['EvnStick_begDate']),
			'EvnStick_endDate' => returnValidHTMLString($response[0]['EvnStick_endDate']),
			'EvnStick_Open' => returnValidHTMLString($response[0]['EvnStick_Open']),
			'EvnStick_Sex' => returnValidHTMLString($response[0]['EvnStick_Sex']),
			'FinalDiag_Code' => returnValidHTMLString($response[0]['FinalDiag_Code']),
			'FinalDeseaseType_Code' => returnValidHTMLString($response[0]['FinalDeseaseType_Code']),
			'KlareaType_id' => returnValidHTMLString($response[0]['KlareaType_id']),
			'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
			'LpuSectionProfile_Code' => returnValidHTMLString($response[0]['LpuSectionProfile_Code']),
			'LpuSectionProfile_Name' => returnValidHTMLString($response[0]['LpuSectionProfile_Name']),
			'MedPersonal_Fio' => returnValidHTMLString($response[0]['MedPersonal_Fio']),
			'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
			'PayType_Code' => returnValidHTMLString($response[0]['PayType_Code']),
			'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name']),
			'Person_Docum' => returnValidHTMLString($response[0]['DocumentType_Name']) . ' ' . returnValidHTMLString($response[0]['Document_Ser']) . ' ' . returnValidHTMLString($response[0]['Document_Num']),
			'Person_Fio' => mb_strtoupper(returnValidHTMLString($response[0]['Person_Fio'])),
			'Person_INN' => returnValidHTMLString($response[0]['Person_INN']),
			'Person_Snils' => returnValidHTMLString($response[0]['Person_Snils']),
			'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
			'Polis_begDate' => returnValidHTMLString($response[0]['Polis_begDate']),
			'Polis_endDate' => returnValidHTMLString($response[0]['Polis_endDate']),
			'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
			'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
			'PrehospDiag_Code' => returnValidHTMLString($response[0]['PrehospDiag_Code']),
			'PrehospDiag_regDate' => returnValidHTMLString($response[0]['PrehospDiag_regDate']),
			'ServiceType_Code' => returnValidHTMLString($response[0]['ServiceType_Code']),
			'ResultClass_Code' => returnValidHTMLString($response[0]['ResultClass_Code']),
			'PrehospTrauma_Code' => returnValidHTMLString($response[0]['PrehospTrauma_Code']),
			'MedicalCareKind_Code' => returnValidHTMLString($response[0]['MedicalCareKind_Code'])
		);
		$print_data['vizitDataPol_1'] = '&nbsp;';// $print_data['vizitDataPol_2'] = '&nbsp;'; $print_data['vizitDataPol_3'] = '&nbsp;';
		$print_data['DaysPol_Count_1'] = '&nbsp;';
		$print_data['DaysPol_Count_2'] = '&nbsp;';
		$print_data['DaysPol_Count_3'] = '&nbsp;';
		$print_data['Pol_1'] = '&nbsp;';
		$print_data['Pol_2'] = '&nbsp;';
		$print_data['Pol_3'] = '&nbsp;';
		$print_data['vizitDataHome_1'] = '&nbsp;';
		$print_data['vizitDataHome_2'] = '&nbsp;';
		$print_data['DaysHome_Count_1'] = '&nbsp;';
		$print_data['DaysHome_Count_2'] = '&nbsp;';
		$print_data['Home_1'] = '&nbsp;';
		$print_data['Home_2'] = '&nbsp;';
		$print_data['vizitDataHomeAct_1'] = '&nbsp;';
		$print_data['vizitDataHomeAct_2'] = '&nbsp;';
		$print_data['DaysHomeAct_Count_1'] = '&nbsp;';
		$print_data['DaysHomeAct_Count_2'] = '&nbsp;';
		$print_data['HomeAct_1'] = '&nbsp;';
		$print_data['HomeAct_2'] = '&nbsp;';
		$print_data['lrn1'] = '&nbsp;';
		$print_data['lrn2'] = '&nbsp;';
		$print_data['lrn3'] = '&nbsp;';
		//$print_data['mpc1'] = '&nbsp;'; $print_data['mpc2'] = '&nbsp;'; $print_data['mpc3'] = '&nbsp;';$print_data['mpc4'] = '&nbsp;'; $print_data['mpc5'] = '&nbsp;'; $print_data['mpc5'] = '&nbsp;'; $print_data['mpc5'] = '&nbsp;';
		for ($i = 1; $i <= 7; $i++) {
			$print_data['mpc' . $i] = '&nbsp;';
			$print_data['mpcl' . $i] = '&nbsp;';
		}
		$print_data['vtc1'] = '&nbsp;';
		$print_data['vtc2'] = '&nbsp;';
		if (!empty($response[0]['LpuRegion_Name'])) {
			for ($i = 1; $i <= 3; $i++) {
				if (isset($response[0]['LpuRegion_Name'][$i - 1])) {
					$print_data['lrn' . $i] = $response[0]['LpuRegion_Name'][$i - 1];
				}
			}
		}
		if (!empty($response[0]['MedPersonal_Code'])) {
			for ($i = 1; $i <= 7; $i++) {
				if (isset($response[0]['MedPersonal_Code'][$i - 1])) {
					$print_data['mpc' . $i] = $response[0]['MedPersonal_Code'][$i - 1];
				}
			}
		}
		if (!empty($response[0]['MedPersonal_Code_Last'])) {
			for ($i = 1; $i <= 7; $i++) {
				if (isset($response[0]['MedPersonal_Code_Last'][$i - 1])) {
					$print_data['mpcl' . $i] = $response[0]['MedPersonal_Code_Last'][$i - 1];
				}
			}
		}
		if (!empty($response[0]['VizitType_Code'])) {
			for ($i = 1; $i <= 5; $i++) {
				if (isset($response[0]['VizitType_Code'][$i - 1])) {
					$print_data['vtc' . $i] = $response[0]['VizitType_Code'][$i - 1];
				}
			}
		}
		$response_vizit = $this->dbmodel->getEvnVizitPLDataKarelya($data, 1); //Посещения для "Поликлиника"

		$pol_days = 0;
		$pol_vizit = '';
		//var_dump($response_vizit);
		//die;
		if (is_array($response_vizit)) {
			for ($i = 1; $i <= count($response_vizit); $i++) {
				if (isset($response_vizit[$i - 1])) {
					//$print_data['vizitDataPol_'.$i] = $response_vizit[$i-1]['EVPL_EvnVizitPL_setDate'] . ' ' . $response_vizit[$i-1]['EVPL_MedPersonal_Fio'];
					//$print_data['DaysPol_Count_'.$i] = ($response_vizit[$i-1]['Days_Count']==0)?1:$response_vizit[$i-1]['Days_Count'];
					//$print_data['Pol_'.$i] = '1';
					//$pol_vizit .= $response_vizit[$i-1]['EVPL_EvnVizitPL_setDate'] . ' ' . $response_vizit[$i-1]['EVPL_MedPersonal_Fio'].'<br>';
					$pol_vizit .= $response_vizit[$i - 1]['EVPL_EvnVizitPL_setDate'] . ' - ' . $response_vizit[$i - 1]['EVPL_MedPersonal_Code'] . ' &nbsp;&nbsp;';
					$pol_days += ($response_vizit[$i - 1]['Days_Count'] == 0) ? 1 : $response_vizit[$i - 1]['Days_Count'];
				}
			}
			$print_data['vizitDataPol_1'] = $pol_vizit;
			$print_data['DaysPol_Count_1'] = ($pol_days == 0) ? '' : $pol_days;
			$print_data['Pol_1'] = (($i - 1) <= 0) ? '' : ($i - 1);
		}
		$home_days = 0;
		$home_vizit = '';
		$response_vizit = $this->dbmodel->getEvnVizitPLDataKarelya($data, 2); //Посещения для "На дому"
		if (is_array($response_vizit)) {
			for ($i = 1; $i <= count($response_vizit); $i++) {
				if (isset($response_vizit[$i - 1])) {
					//$print_data['vizitDataHome_'.$i] = $response_vizit[$i-1]['EVPL_EvnVizitPL_setDate'] . ' ' . $response_vizit[$i-1]['EVPL_MedPersonal_Fio'];
					//$print_data['DaysHome_Count_'.$i] = ($response_vizit[$i-1]['Days_Count']==0)?1:$response_vizit[$i-1]['Days_Count'];
					//$print_data['Home_'.$i] = '1';
					//$home_vizit .= $response_vizit[$i-1]['EVPL_EvnVizitPL_setDate'] . ' ' . $response_vizit[$i-1]['EVPL_MedPersonal_Fio'].'<br>';
					$home_vizit .= $response_vizit[$i - 1]['EVPL_EvnVizitPL_setDate'] . ' - ' . $response_vizit[$i - 1]['EVPL_MedPersonal_Code'] . ' &nbsp;&nbsp;';
					$home_days += ($response_vizit[$i - 1]['Days_Count'] == 0) ? 1 : $response_vizit[$i - 1]['Days_Count'];
				}
			}
			$print_data['vizitDataHome_1'] = $home_vizit;
			$print_data['DaysHome_Count_1'] = ($home_days == 0) ? '' : $home_days;
			$print_data['Home_1'] = (($i - 1) <= 0) ? '' : ($i - 1);
		}
		$homeact_days = 0;
		$homeact_vizit = '';
		$response_vizit = $this->dbmodel->getEvnVizitPLDataKarelya($data, 3); //Посещения для "Актив на дому"
		if (is_array($response_vizit)) {
			for ($i = 1; $i <= count($response_vizit); $i++) {
				if (isset($response_vizit[$i - 1])) {
					//$print_data['vizitDataHomeAct_'.$i] = $response_vizit[$i-1]['EVPL_EvnVizitPL_setDate'] . ' ' . $response_vizit[$i-1]['EVPL_MedPersonal_Fio'];
					//$print_data['DaysHomeAct_Count_'.$i] = ($response_vizit[$i-1]['Days_Count']==0)?1:$response_vizit[$i-1]['Days_Count'];
					//$print_data['HomeAct_'.$i] = '1';
					//$homeact_vizit .= $response_vizit[$i-1]['EVPL_EvnVizitPL_setDate'] . ' ' . $response_vizit[$i-1]['EVPL_MedPersonal_Fio'].'<br>';
					$homeact_vizit .= $response_vizit[$i - 1]['EVPL_EvnVizitPL_setDate'] . ' - ' . $response_vizit[$i - 1]['EVPL_MedPersonal_Code'] . '&nbsp;&nbsp;';
					$homeact_days += ($response_vizit[$i - 1]['Days_Count'] == 0) ? 1 : $response_vizit[$i - 1]['Days_Count'];
				}
			}
			$print_data['vizitDataHomeAct_1'] = $homeact_vizit;
			$print_data['DaysHomeAct_Count_1'] = ($homeact_days == 0) ? '' : $homeact_days;
			$print_data['HomeAct_1'] = (($i - 1) <= 0) ? '' : ($i - 1);
		}

		for ($i = 1; $i <= 4; $i++) {
			for ($j = 1; $j <= 14; $j++)
				$print_data['u' . $i . $j] = '&nbsp;';
			for ($j = 1; $j <= 2; $j++)
				$print_data['uk' . $i . $j] = '&nbsp;';
		}
		$response_usluga = $this->dbmodel->getEvnUslugaDataPskov($data); //Коды выполненных услуг
		// Группируем услуги по коду выполненных услуг
		$buffer = array();
		foreach ($response_usluga as $usluga) {
			$buffer[$usluga['UslugaComplex_Code']] ['UslugaComplex_Code'] = $usluga['UslugaComplex_Code'];
			if (isset($buffer[$usluga['UslugaComplex_Code']] ['EvnUsluga_Kolvo'])) {
				$buffer[$usluga['UslugaComplex_Code']] ['EvnUsluga_Kolvo'] += $usluga['EvnUsluga_Kolvo'];
			} else {
				$buffer[$usluga['UslugaComplex_Code']] ['EvnUsluga_Kolvo'] = $usluga['EvnUsluga_Kolvo'];
			}
		}
		// Удалим коды выполненных услуг из ключей
		$response_usluga = array_values($buffer);

		if (is_array($response_usluga)) {
			for ($i = 1; $i <= 4; $i++) {
				if (isset($response_usluga[$i - 1])) {
					for ($j = 1; $j <= 14; $j++) {
						if (mb_strlen($response_usluga[$i - 1]['UslugaComplex_Code']) >= $j) {
							if (isset($response_usluga[$i - 1]['UslugaComplex_Code'][$j - 1]))
								$print_data['u' . $i . $j] = $response_usluga[$i - 1]['UslugaComplex_Code'][$j - 1];
							else
								$print_data['u' . $i . $j] = '';
						}
					}
					for ($j = 1; $j <= 2; $j++) {
						$kolvo = strval($response_usluga[$i - 1]['EvnUsluga_Kolvo']);
						if (isset($kolvo[$j - 1]))
							$print_data['uk' . $i . $j] = $kolvo[$j - 1];
						else
							$print_data['uk' . $i . $j] = '';
					}
				}
			}
		}

		for ($i = 1; $i <= 5; $i++) {
			$print_data['diagType' . $i] = '&nbsp;';
			$print_data['Diag_Code' . $i] = '&nbsp;';
			$print_data['DeseaseType_Code' . $i] = '&nbsp;';
			$print_data['IsDisp' . $i] = '&nbsp;';
			$print_data['Disp_Date' . $i] = '&nbsp;';
			$print_data['DOT_Zdorov' . $i] = '&nbsp;';
			$print_data['DOT_Other' . $i] = '&nbsp;';
		}
		$response_diag = $this->dbmodel->getEvnDiagDataKarelya($data);
		if (is_array($response_diag)) {
			for ($i = 1; $i <= 5; $i++) {
				if (isset($response_diag[$i - 1])) {
					$print_data['diagType' . $i] = $response_diag[$i - 1]['diagType'];
					$print_data['Diag_Code' . $i] = $response_diag[$i - 1]['Diag_Code'];
					$print_data['DeseaseType_Code' . $i] = $response_diag[$i - 1]['DeseaseType_Code'];
					$print_data['IsDisp' . $i] = $response_diag[$i - 1]['IsDisp'];
					$print_data['Disp_Date' . $i] = $response_diag[$i - 1]['Disp_Date'];
					$print_data['DOT_Zdorov' . $i] = $response_diag[$i - 1]['DOT_Zdorov'];
					$print_data['DOT_Other' . $i] = $response_diag[$i - 1]['DOT_Other'];
				}
			}
		}

		//Документ временной нетрудоспособности
		for ($i = 1; $i <= 8; $i++) {
			$print_data['sb' . $i] = '&nbsp;';
			$print_data['se' . $i] = '&nbsp;';
		}
		$print_data['StickCause_Type'] = '&nbsp;';
		$print_data['Person_Age'] = '&nbsp;';
		$response_stick = $this->dbmodel->getEvnPLStickKareliya($data);
		if (sizeof($response_stick) > 0) {
			$print_data['StickType_Code'] = returnValidHTMLString($response_stick[0]['StickType_Code']);
			$print_data['StickCause_Type'] = returnValidHTMLString($response_stick[0]['StickCause_Type']);
			$print_data['Person_Age'] = returnValidHTMLString($response_stick[0]['Person_Age']);
			if (isset($response_stick[0]['Stick_Beg'])) {
				$response_stick[0]['Stick_Beg'] = str_replace('.', '', $response_stick[0]['Stick_Beg']);
				for ($i = 1; $i <= 8; $i++) {
					$print_data['sb' . $i] = $response_stick[0]['Stick_Beg'][$i - 1];
				}
			}
			if (isset($response_stick[0]['Stick_End']) && $response_stick[0]['StickLeaveType'] != -1) {
				$response_stick[0]['Stick_End'] = str_replace('.', '', $response_stick[0]['Stick_End']);
				for ($i = 1; $i <= 8; $i++) {
					$print_data['se' . $i] = $response_stick[0]['Stick_End'][$i - 1];
				}
			}
		}
		$print_data['MedicalCareKind_Code1'] = '&nbsp';
		$print_data['MedicalCareKind_Code2'] = '&nbsp';
		if (!empty($response[0]['MedicalCareKind_Code'])) {
			for ($i = 1; $i <= strlen($response[0]['MedicalCareKind_Code']); $i++) {
				$print_data['MedicalCareKind_Code' . $i] = $response[0]['MedicalCareKind_Code'][$i - 1];
			}
		}
		$print_data['ResultClass_Code1'] = '&nbsp;';
		$print_data['ResultClass_Code2'] = '&nbsp;';
		$print_data['ResultClass_Code3'] = '&nbsp;';
		$print_data['ResultDeseaseType_Code1'] = '&nbsp;';
		$print_data['ResultDeseaseType_Code2'] = '&nbsp;';
		$print_data['ResultDeseaseType_Code3'] = '&nbsp;';
		if (isset($response[0]['ResultClass_Code'])) {
			if (strlen($response[0]['ResultClass_Code']) == 3) {
				$print_data['ResultClass_Code1'] = $response[0]['ResultClass_Code'][0];
				$print_data['ResultClass_Code2'] = $response[0]['ResultClass_Code'][1];
				$print_data['ResultClass_Code3'] = $response[0]['ResultClass_Code'][2];
			}
		}
		if (isset($response[0]['ResultDeseaseType_Code'])) {
			if (strlen($response[0]['ResultDeseaseType_Code']) == 3) {
				$print_data['ResultDeseaseType_Code1'] = $response[0]['ResultDeseaseType_Code'][0];
				$print_data['ResultDeseaseType_Code2'] = $response[0]['ResultDeseaseType_Code'][1];
				$print_data['ResultDeseaseType_Code3'] = $response[0]['ResultDeseaseType_Code'][2];
			}
		}
		if (!empty($response[0]['PrivilegeType_Name'])) {
			$print_data['PrivilegeType_Name'] = returnValidHTMLString($response[0]['PrivilegeType_Name']) . ', установлена ' . returnValidHTMLString($response[0]['PersonPrivilege_begDate']);
		}

		$print_data['Document'] = (strlen($print_data['Document_Num']))
			? "тип док-та <u>&nbsp;&nbsp;&nbsp;{$response[0]['DocumentType_Code']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u> серия <u>{$print_data['Document_Ser']}</u> номер <u>{$print_data['Document_Num']}</u>"
			: 'тип док-та ______________ серия _______ номер ______';

		switch ($response[0]['VizitType_SysNick']) {
			case 'desease':
				$print_data['VizitType_Code'] = 1;
				break;

			case 'consul':
				$print_data['VizitType_Code'] = 2;
				break;

			case 'disp':
				$print_data['VizitType_Code'] = 3;
				break;

			case 'prof':
				$print_data['VizitType_Code'] = 5;
				break;

			case 'dd':
				$print_data['VizitType_Code'] = 10;
				break;

			case 'soc':
				$print_data['VizitType_Code'] = 11;
				break;

			default:
				$print_data['VizitType_Code'] = 0;
		}

		switch ($response[0]['PayType_SysNick']) {
			case 'oms':
				$print_data['PayType_Code'] = 1;
				break;

			case 'bud':
			case 'fbud':
			case 'subrf':
				$print_data['PayType_Code'] = 2;
				break;

			case 'money':
				$print_data['PayType_Code'] = 3;
				break;

			case 'dms':
				$print_data['PayType_Code'] = 4;
				break;

			default:
				$print_data['PayType_Code'] = 5;
		}

		return $this->parser->parse($template, $print_data, $ReturnString);
	}

	/**
	 * Печать талона амбулаторного пациента (Астрахань)
	 * Входящие данные: $_GET['EvnPL_id']
	 * На выходе: форма для печати талона амбулаторного пациента
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function printEvnPLAstra($EvnPL_id = null, $ReturnString = false)
	{
		$this->load->helper('Options');
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLPskov', true);
		if ($data === false) {
			return false;
		}

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLFieldsPskov($data);

		// Приводим код оплаты в соответствие с формой
		// https://redmine.swan.perm.ru/issues/39608
		switch ($response[0]['PayType_Code']) {
			case 1:
			case 7:
			case 8:
				$response[0]['PayType_Code'] = 1;
				break;

			case 2:
				$response[0]['PayType_Code'] = 4;
				break;

			case 3:
			case 4:
			case 9:
				$response[0]['PayType_Code'] = 2;
				break;

			case 5:
				$response[0]['PayType_Code'] = 3;
				break;

			case 6:
				$response[0]['PayType_Code'] = 5;
				break;
		}

		$response_usluga = $this->dbmodel->getEvnUslugaDataPskov($data);
		if (!is_array($response) || count($response) == 0) {
			echo 'Ошибка при получении данных по талону';
			return true;
		}
		$response_recept = $this->dbmodel->getEvnPLReceptFieldsAstra($data);

		$template = 'evn_pl_template_list_a4_astra';

		if (!empty($response[0]['ResultClass_Code']) && $response[0]['ResultClass_Code'] == 5) {
			$response[0]['ResultClass_Code'] = 3;
		}
		$response[0]['ResultClass_Code'] = $response[0]['ResultClass_Code'] * 10;//В одном <td> подчеркиваются все span-ы с одинаковым значением, даже если селекторы у них разные. поэтому так.
		if (!empty($response[0]['DirectType_Code'])) {
			if ($response[0]['DirectType_Code'] == 2)
				$response[0]['DirectType_Code'] = 1;
			if ($response[0]['DirectType_Code'] == 4)
				$response[0]['DirectType_Code'] = 3;
			if (($response[0]['DirectType_Code'] == 6) && ($response[0]['DirectClass_id'] == 2))
				$response[0]['DirectType_Code'] = 8;

		}
		$print_data = array(
			'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
			'OrgUnion_Name' => returnValidHTMLString($response[0]['OrgUnion_Name']),
			'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
			'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
			'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
			'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
			'Document_begDate' => returnValidHTMLString($response[0]['Document_begDate']),
			'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name']),
			'KLAreaType_Code' => returnValidHTMLString($response[0]['KLAreaType_Code']),
			'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
			'SocStatus_Code' => returnValidHTMLString($response[0]['SocStatus_Code']),
			'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
			'PrivilegeType_Name' => '',
			'PrivilegeType_CodeStr' => returnValidHTMLString($response[0]['PrivilegeType_CodeStr']),
			'PersonPrivilege_begDate' => '',
			'DeseaseTypeSop_Code' => returnValidHTMLString($response[0]['DeseaseTypeSop_Code']),
			'DiagAgg_Code' => returnValidHTMLString($response[0]['DiagAgg_Code']),
			'DiagSop_Code' => returnValidHTMLString($response[0]['DiagSop_Code']),
			'EvnPL_NumCard' => returnValidHTMLString($response[0]['EvnPL_NumCard']),
			'EvnPL_IsFinish' => returnValidHTMLString($response[0]['EvnPL_IsFinish']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'Lpu_OGRN' => returnValidHTMLString($response[0]['Lpu_OGRN']),
			'Sex_id' => returnValidHTMLString($response[0]['Sex_id']),
			'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
			'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
			'Person_BirthdayStr' => returnValidHTMLString($response[0]['Person_BirthdayStr']),
			'Lpu_Address' => returnValidHTMLString($response[0]['Lpu_Address']),
			'EvnPL_setDate' => returnValidHTMLString($response[0]['EvnPL_setDate']),
			'EvnPLTemplateTitle' => 'Печать талона амбулаторного пациента',
			'EvnStick_Age' => returnValidHTMLString($response[0]['EvnStick_Age']),
			'EvnStick_begDate' => returnValidHTMLString($response[0]['EvnStick_begDate']),
			'EvnStick_endDate' => returnValidHTMLString($response[0]['EvnStick_endDate']),
			'EvnStick_Open' => returnValidHTMLString($response[0]['EvnStick_Open']),
			'EvnStick_Sex' => returnValidHTMLString($response[0]['EvnStick_Sex']),
			'FinalDiag_Code' => returnValidHTMLString($response[0]['FinalDiag_Code']),
			'FinalDeseaseType_Code' => returnValidHTMLString($response[0]['FinalDeseaseType_Code']),
			'KlareaType_id' => returnValidHTMLString($response[0]['KlareaType_id']),
			'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
			'LpuSectionProfile_Code' => returnValidHTMLString($response[0]['LpuSectionProfile_Code']),
			'LpuSectionProfile_Name' => returnValidHTMLString($response[0]['LpuSectionProfile_Name']),
			'MedPersonal_Fio' => returnValidHTMLString($response[0]['MedPersonal_Fio']),
			'MedPersonal2_Fio' => returnValidHTMLString($response[0]['MedPersonal2_Fio']),
			'MedPersonal_TabCode' => returnValidHTMLString($response[0]['MedPersonal_TabCode']),
			'MedPersonal2_TabCode' => returnValidHTMLString($response[0]['MedPersonal2_TabCode']),
			'PayType_Code' => returnValidHTMLString($response[0]['PayType_Code']),
			'Person_Address' => returnValidHTMLString($response[0]['PAddress_Name']),
			'Person_Docum' => returnValidHTMLString($response[0]['DocumentType_Name']) . ' ' . returnValidHTMLString($response[0]['Document_Ser']) . ' ' . returnValidHTMLString($response[0]['Document_Num']),
			'Person_Fio' => mb_strtoupper(returnValidHTMLString($response[0]['Person_Fio'])),
			'Person_INN' => returnValidHTMLString($response[0]['Person_INN']),
			'Person_Snils' => returnValidHTMLString($response[0]['Person_Snils']),
			'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
			'Polis_begDate' => returnValidHTMLString($response[0]['Polis_begDate']),
			'Polis_endDate' => returnValidHTMLString($response[0]['Polis_endDate']),
			'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
			'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
			'PrehospDiag_Code' => returnValidHTMLString($response[0]['PrehospDiag_Code']),
			'PrehospDiag_regDate' => returnValidHTMLString($response[0]['PrehospDiag_regDate']),
			'ServiceType_Code' => returnValidHTMLString($response[0]['ServiceType_Code']),
			'VizitType_Code' => returnValidHTMLString($response[0]['VizitType_Code']),
			'ResultClass_Code' => returnValidHTMLString($response[0]['ResultClass_Code']),
			'DirectType_Code' => returnValidHTMLString($response[0]['DirectType_Code']),
			'PrehospTrauma_Code' => returnValidHTMLString($response[0]['PrehospTrauma_Code']),
			'EvnVizitPL_Date' => returnValidHTMLString($response[0]['EvnVizitPL_Date'])
		);

		if (allowPersonEncrypHIV($data['session']) && !empty($response[0]['PersonEncrypHIV_Encryp'])) {
			$print_data['Person_Fio'] = returnValidHTMLString($response[0]['PersonEncrypHIV_Encryp']);

			$person_fields = array('OrgJob_Name', 'OrgUnion_Name', 'Post_Name', 'DocumentType_Name', 'Document_Ser', 'Document_Num',
				'Document_begDate', 'UAddress_Name', 'KLAreaType_Code', 'KLAreaType_Name', 'SocStatus_Code', 'SocStatus_Name',
				'PrivilegeType_Name', 'PrivilegeType_CodeStr', 'PersonPrivilege_begDate', 'Sex_id', 'Sex_Name',
				'Person_Birthday', 'LpuRegion_Name', 'Person_Address', 'Person_Docum', 'Person_INN', 'Person_Snils',
				'PersonCard_Code', 'Polis_begDate', 'Polis_endDate', 'Polis_Num', 'Polis_Ser'
			);

			foreach ($person_fields as $field) {
				$print_data[$field] = '';
			}
		}

		for ($i = 1; $i <= 13; $i++) {
			$print_data['ogrn' . $i] = '&nbsp;';
		}
		for ($i = 1; $i <= 6; $i++) {
			$print_data['EVPLD' . $i] = '&nbsp;';
		}
		for ($i = 1; $i <= 3; $i++) {
			$print_data['PrivC' . $i] = '&nbsp;';
		}
		for ($i = 1; $i <= 18; $i++) {
			$print_data['PSN' . $i] = '&nbsp;';
		}
		for ($i = 1; $i <= 20; $i++) {
			$print_data['PSnils' . $i] = '&nbsp;';
		}
		for ($i = 1; $i <= 8; $i++) {
			$print_data['PB' . $i] = '&nbsp;';
		}
		for ($i = 1; $i <= 10; $i++) {
			$print_data['MPTC' . $i] = '&nbsp;';
			$print_data['MP2TC' . $i] = '&nbsp;';
		}
		for ($i = 1; $i <= 5; $i++) {
			$print_data['FDC' . $i] = '&nbsp;';
		}
		for ($i = 1; $i <= 4; $i++) {
			$print_data['ReceptField' . $i] = '&nbsp;';
		}
		if (!empty($print_data['Lpu_OGRN']) && $print_data['Lpu_OGRN'] != '&nbsp;') {
			for ($i = 1; $i <= 13; $i++) {
				if (isset($print_data['Lpu_OGRN'][$i - 1])) {
					$print_data['ogrn' . $i] = $print_data['Lpu_OGRN'][$i - 1];
				}
			}
		}
		if (!empty($print_data['EvnVizitPL_Date']) && $print_data['EvnVizitPL_Date'] != '&nbsp;') {
			for ($i = 1; $i <= 6; $i++) {
				if (isset($print_data['EvnVizitPL_Date'][$i - 1])) {
					$print_data['EVPLD' . $i] = $print_data['EvnVizitPL_Date'][$i - 1];
				}
			}
		}
		if (!empty($print_data['PrivilegeType_CodeStr']) && $print_data['PrivilegeType_CodeStr'] != '&nbsp;') {
			for ($i = 1; $i <= 3; $i++) {
				if (isset($print_data['PrivilegeType_CodeStr'][$i - 1])) {
					$print_data['PrivC' . $i] = $print_data['PrivilegeType_CodeStr'][$i - 1];
				}
			}
		}

		if ($print_data['Polis_Ser'] == '&nbsp;') {
			$print_data['Polis_Ser'] = '';
		}
		if ($print_data['Polis_Num'] == '&nbsp;') {
			$print_data['Polis_Num'] = '';
		}
		$polisSerNum = htmlspecialchars($print_data['Polis_Ser'], ENT_QUOTES, 'windows-1251') . htmlspecialchars($print_data['Polis_Num'], ENT_QUOTES, 'windows-1251');
		if (!empty($polisSerNum)) {
			for ($i = 1; $i <= 18; $i++) {
				if (isset($polisSerNum[$i - 1])) {
					$print_data['PSN' . $i] = $polisSerNum[$i - 1];
				}
			}
		}
		if (!empty($print_data['Person_Snils']) && $print_data['Person_Snils'] != '&nbsp;') {
			for ($i = 1; $i <= 20; $i++) {
				if (isset($print_data['Person_Snils'][$i - 1])) {
					$print_data['PSnils' . $i] = $print_data['Person_Snils'][$i - 1];
				}
			}
		}
		if (!empty($print_data['Person_BirthdayStr']) && $print_data['Person_BirthdayStr'] != '&nbsp;') {
			for ($i = 1; $i <= 8; $i++) {
				if (isset($print_data['Person_BirthdayStr'][$i - 1])) {
					$print_data['PB' . $i] = $print_data['Person_BirthdayStr'][$i - 1];
				}
			}
		}
		if (!empty($print_data['MedPersonal_TabCode']) && $print_data['MedPersonal_TabCode'] != '&nbsp;') {
			for ($i = 1; $i <= 10; $i++) {
				if (isset($print_data['MedPersonal_TabCode'][$i - 1])) {
					$print_data['MPTC' . $i] = $print_data['MedPersonal_TabCode'][$i - 1];
				}
			}
		}
		if (!empty($print_data['MedPersonal2_TabCode']) && $print_data['MedPersonal2_TabCode'] != '&nbsp;') {
			for ($i = 1; $i <= 10; $i++) {
				if (isset($print_data['MedPersonal2_TabCode'][$i - 1])) {
					$print_data['MP2TC' . $i] = $print_data['MedPersonal2_TabCode'][$i - 1];
				}
			}
		}

		if (!empty($print_data['FinalDiag_Code']) && $print_data['FinalDiag_Code'] != '&nbsp;') {
			for ($i = 1; $i <= 5; $i++) {
				if (isset($print_data['FinalDiag_Code'][$i - 1])) {
					$print_data['FDC' . $i] = $print_data['FinalDiag_Code'][$i - 1];
				}
			}
		}

		for ($i = 1; $i <= 51; $i++) {
			for ($j = 1; $j <= 7; $j++)
				$print_data['c' . $i . $j] = '&nbsp;';
		}

		for ($i = 1; $i <= count($response_usluga); $i++) {
			for ($j = 1; $j <= strlen($response_usluga[$i - 1]['UslugaComplex_Code']); $j++)
				$print_data['c' . $i . $j] = $response_usluga[$i - 1]['UslugaComplex_Code'][$j - 1];
		}

		for ($i = 1; $i <= count($response_recept); $i++) {
			$print_data['ReceptField' . $i] = $response_recept[$i - 1]['EvnRecept_Ser'] . ' ' . $response_recept[$i - 1]['EvnRecept_Num'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $response_recept[$i - 1]['EvnRecept_setDate'];
		}

		/*switch ( $print_data['VizitType_SysNick'] ) {
			case 'desease':
				$print_data['VizitType_Code'] = 1;
				break;

			case 'consul':
				$print_data['VizitType_Code'] = 2;
				break;

			case 'disp':
				$print_data['VizitType_Code'] = 3;
				break;

			case 'prof':
				$print_data['VizitType_Code'] = 5;
				break;

			case 'dd':
				$print_data['VizitType_Code'] = 10;
				break;

			case 'soc':
				$print_data['VizitType_Code'] = 11;
				break;

			default:
				$print_data['VizitType_Code'] = 0;
		}*/

		return $this->parser->parse($template, $print_data, $ReturnString);
	}

	/**
	 * Печать талона амбулаторного пациента (Хакасия)
	 * Входящие данные: $_GET['EvnPL_id']
	 * На выходе: форма для печати талона амбулаторного пациента
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function printEvnPLHakasiya($EvnPL_id = null, $ReturnString = false)
	{
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLUfa', true);
		if ($data === false) {
			return false;
		}

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLFieldsHakasiya($data);

		if (!is_array($response) || count($response) == 0) {
			echo 'Ошибка при получении данных по талону';
			return true;
		}

		$evn_recept_data = array(
			array(
				'ER_EvnRecept_setDate' => '&nbsp;',
				'ER_EvnRecept_Ser' => '&nbsp;',
				'ER_EvnRecept_Num' => '&nbsp;',
				'ER_Diag_Code' => '&nbsp;',
				'ER_Drug_Name' => '&nbsp;',
				'ER_Drug_Dose' => '&nbsp;',
				'ER_EvnRecept_Kolvo' => '&nbsp;'
			),
			array(
				'ER_EvnRecept_setDate' => '&nbsp;',
				'ER_EvnRecept_Ser' => '&nbsp;',
				'ER_EvnRecept_Num' => '&nbsp;',
				'ER_Diag_Code' => '&nbsp;',
				'ER_Drug_Name' => '&nbsp;',
				'ER_Drug_Dose' => '&nbsp;',
				'ER_EvnRecept_Kolvo' => '&nbsp;'
			),
			array(
				'ER_EvnRecept_setDate' => '&nbsp;',
				'ER_EvnRecept_Ser' => '&nbsp;',
				'ER_EvnRecept_Num' => '&nbsp;',
				'ER_Diag_Code' => '&nbsp;',
				'ER_Drug_Name' => '&nbsp;',
				'ER_Drug_Dose' => '&nbsp;',
				'ER_EvnRecept_Kolvo' => '&nbsp;'
			),
			array(
				'ER_EvnRecept_setDate' => '&nbsp;',
				'ER_EvnRecept_Ser' => '&nbsp;',
				'ER_EvnRecept_Num' => '&nbsp;',
				'ER_Diag_Code' => '&nbsp;',
				'ER_Drug_Name' => '&nbsp;',
				'ER_Drug_Dose' => '&nbsp;',
				'ER_EvnRecept_Kolvo' => '&nbsp;'
			)
		);

		$evn_usluga_data = array(
			array(
				'EU_EvnUsluga_setDate1' => '&nbsp;',
				'EU_Usluga_Code1' => '&nbsp;',
				'EU_EvnPL_UKL1' => '&nbsp;',
				'EU_EvnUsluga_setDate2' => '&nbsp;',
				'EU_Usluga_Code2' => '&nbsp;',
				'EU_EvnPL_UKL2' => '&nbsp;'
			),
			array(
				'EU_EvnUsluga_setDate1' => '&nbsp;',
				'EU_Usluga_Code1' => '&nbsp;',
				'EU_EvnPL_UKL1' => '&nbsp;',
				'EU_EvnUsluga_setDate2' => '&nbsp;',
				'EU_Usluga_Code2' => '&nbsp;',
				'EU_EvnPL_UKL2' => '&nbsp;'
			),
			array(
				'EU_EvnUsluga_setDate1' => '&nbsp;',
				'EU_Usluga_Code1' => '&nbsp;',
				'EU_EvnPL_UKL1' => '&nbsp;',
				'EU_EvnUsluga_setDate2' => '&nbsp;',
				'EU_Usluga_Code2' => '&nbsp;',
				'EU_EvnPL_UKL2' => '&nbsp;'
			),
			array(
				'EU_EvnUsluga_setDate1' => '&nbsp;',
				'EU_Usluga_Code1' => '&nbsp;',
				'EU_EvnPL_UKL1' => '&nbsp;',
				'EU_EvnUsluga_setDate2' => '&nbsp;',
				'EU_Usluga_Code2' => '&nbsp;',
				'EU_EvnPL_UKL2' => '&nbsp;'
			)
		);

		$response_temp = $this->dbmodel->getEvnReceptData($data);
		if (is_array($response_temp)) {
			for ($i = 0; $i < (count($response_temp) <= 4 ? count($response_temp) : 4); $i++) {
				$evn_recept_data[$i]['ER_EvnRecept_setDate'] = $response_temp[$i]['ER_EvnRecept_setDate'];
				$evn_recept_data[$i]['ER_EvnRecept_Ser'] = $response_temp[$i]['ER_EvnRecept_Ser'];
				$evn_recept_data[$i]['ER_EvnRecept_Num'] = $response_temp[$i]['ER_EvnRecept_Num'];
				$evn_recept_data[$i]['ER_Diag_Code'] = $response_temp[$i]['ER_Diag_Code'];
				$evn_recept_data[$i]['ER_Drug_Name'] = $response_temp[$i]['ER_Drug_Name'];
				$evn_recept_data[$i]['ER_EvnRecept_Kolvo'] = $response_temp[$i]['ER_EvnRecept_Kolvo'];
			}
		}

		$response_temp = $this->dbmodel->getEvnUslugaDataUfa($data);
		if (is_array($response_temp)) {
			for ($i = 0; $i < (count($response_temp) <= 4 ? count($response_temp) : 4); $i++) {
				$evn_usluga_data[$i]['EU_EvnUsluga_setDate1'] = $response_temp[$i]['EvnUsluga_setDate'];
				$evn_usluga_data[$i]['EU_Usluga_Code1'] = $response_temp[$i]['Usluga_Code'];
				$evn_usluga_data[$i]['EU_EvnPL_UKL1'] = $response_temp[$i]['EvnPL_UKL'];
			}

			if (count($response_temp) > 4) {
				for ($i = 0; $i < (count($response_temp) - 4 <= 4 ? count($response_temp) - 4 : 4); $i++) {
					$evn_usluga_data[$i]['EU_EvnUsluga_setDate2'] = $response_temp[$i]['EvnUsluga_setDate'];
					$evn_usluga_data[$i]['EU_Usluga_Code2'] = $response_temp[$i]['Usluga_Code'];
					$evn_usluga_data[$i]['EU_EvnPL_UKL2'] = $response_temp[$i]['EvnPL_UKL'];
				}
			}
		}

		$highlight_style = 'font-weight: bold;';
		$template = 'evn_pl_template_list_a4_hakasiya';

		if ($response[0]['SocStatus_Code'] > 10) {
			$response[0]['SocStatus_Code'] = 11;
		}

		$print_data = array(
			'DeseaseTypeSop_1' => '',
			'DeseaseTypeSop_2' => '',
			'DeseaseTypeSop_Code' => returnValidHTMLString($response[0]['DeseaseTypeSop_Code']),
			'DiagAgg_Code' => returnValidHTMLString($response[0]['DiagAgg_Code']),
			'DiagSop_Code' => returnValidHTMLString($response[0]['DiagSop_Code']),
			'EvnPL_NumCard' => returnValidHTMLString($response[0]['EvnPL_NumCard']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'Lpu_OGRN' => returnValidHTMLString($response[0]['Lpu_OGRN']),
			'Sex_id' => returnValidHTMLString($response[0]['Sex_id']),
			'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
			'Lpu_Address' => returnValidHTMLString($response[0]['Lpu_Address']),
			'DirectType_1' => '',
			'DirectType_2' => '',
			'DirectType_3' => '',
			'DirectType_31' => '',
			'EvnPL_setDate' => returnValidHTMLString($response[0]['EvnPL_setDate']),
			'EvnPLTemplateTitle' => 'Печать талона амбулаторного пациента',
			'EvnReceptData' => $evn_recept_data,
			'EvnStick_Age' => returnValidHTMLString($response[0]['EvnStick_Age']),
			'EvnStick_begDate' => returnValidHTMLString($response[0]['EvnStick_begDate']),
			'EvnStick_endDate' => returnValidHTMLString($response[0]['EvnStick_endDate']),
			'EvnStick_Open' => returnValidHTMLString($response[0]['EvnStick_Open']),
			'EvnStick_Sex1' => '',
			'EvnStick_Sex2' => '',
			'EvnUslugaData' => $evn_usluga_data,
			'FinalDeseaseType_0' => '',
			'FinalDeseaseType_1' => '',
			'FinalDeseaseType_2' => '',
			'FinalDeseaseType_3' => '',
			'FinalDeseaseType_4' => '',
			'FinalDeseaseType_5' => '',
			'FinalDeseaseType_6' => '',
			'FinalDiag_Code' => returnValidHTMLString($response[0]['FinalDiag_Code']),
			'FinalDeseaseType_Code' => returnValidHTMLString($response[0]['FinalDeseaseType_Code']),
			'KlareaType_id' => returnValidHTMLString($response[0]['KlareaType_id']),
			'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
			'LpuSectionProfile_Code' => returnValidHTMLString($response[0]['LpuSectionProfile_Code']),
			'LpuSectionProfile_Name' => returnValidHTMLString($response[0]['LpuSectionProfile_Name']),
			'MedPersonal_Fio' => returnValidHTMLString($response[0]['MedPersonal_Fio']),
			'MedPersonal_TabCode' => returnValidHTMLString($response[0]['MedPersonal_TabCode']),
			'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
			'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
			'PayType_Code' => returnValidHTMLString($response[0]['PayType_Code']),
			'Person_Address' => returnValidHTMLString($response[0]['PAddress_Name']),
			'Person_Docum' => returnValidHTMLString($response[0]['DocumentType_Name']) . ' ' . returnValidHTMLString($response[0]['Document_Ser']) . ' ' . returnValidHTMLString($response[0]['Document_Num']),
			'Person_Fio' => mb_strtoupper(returnValidHTMLString($response[0]['Person_Fio'])),
			'Person_INN' => returnValidHTMLString($response[0]['Person_INN']),
			'Person_Snils' => returnValidHTMLString($response[0]['Person_Snils']),
			'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
			'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name']),
			'Polis_begDate' => returnValidHTMLString($response[0]['Polis_begDate']),
			'Polis_endDate' => returnValidHTMLString($response[0]['Polis_endDate']),
			'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
			'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
			'PrehospDiag_Code' => returnValidHTMLString($response[0]['PrehospDiag_Code']),
			'PrehospDiag_regDate' => returnValidHTMLString($response[0]['PrehospDiag_regDate']),
			'PrehospTrauma_1' => '',
			'PrehospTrauma_2' => '',
			'PrehospTrauma_21' => '',
			'PrehospTrauma_3' => '',
			'PrehospTrauma_4' => '',
			'PrehospTrauma_6' => '',
			'PrehospTrauma_7' => '',
			'PrehospTrauma_8' => '',
			'PrehospTrauma_81' => '',
			'PrehospTrauma_9' => '',
			'PrehospTrauma_10' => '',
			'PrehospTrauma_11' => '',
			'PrivilegeType_Code' => returnValidHTMLString($response[0]['PrivilegeType_Code']),
			'ResultClass_1' => '',
			'ResultClass_2' => '',
			'ResultClass_3' => '',
			'ResultClass_4' => '',
			'ResultClass_5' => '',
			'ResultClass_Code' => returnValidHTMLString($response[0]['ResultClass_Code']),
			'ServiceType_Code' => returnValidHTMLString($response[0]['ServiceType_Code']),
			'SocStatus_Code' => returnValidHTMLString($response[0]['SocStatus_Code']),
			'StickCause_1' => '',
			'StickCause_2' => '',
			'StickCause_3' => '',
			'StickCause_4' => '',
			'StickCause_5' => '',
			'StickCause_6' => '',
			'StickType_1' => '',
			'StickType_11' => '',
			'StickType_2' => '',
			'StickType_3' => '',
			'VizitType_1' => '',
			'VizitType_2' => '',
			'VizitType_3' => '',
			'VizitType_4' => '',
			'VizitType_41' => '',
			'VizitType_5' => ''
		);

		switch ($response[0]['VizitType_SysNick']) {
			case 'desease':
				$print_data['VizitType_Code'] = 1;
				$print_data['VizitType_1'] = $highlight_style;
				break;

			case 'prof':
				$print_data['VizitType_Code'] = 2;
				$print_data['VizitType_2'] = $highlight_style;
				break;

			case 'patron':
				$print_data['VizitType_Code'] = 3;
				$print_data['VizitType_3'] = $highlight_style;
				break;

			default:
				$print_data['VizitType_Code'] = 4;
		}

		switch ($response[0]['DirectType_SysNick']) {
			case 'stac':
				$print_data['DirectType_1'] = $highlight_style;
				break;

			case 'dstac':
				$print_data['DirectType_2'] = $highlight_style;
				break;

			case 'kons':
				$print_data['DirectType_3'] = $highlight_style;
				break;

			case 'other':
				$print_data['DirectType_31'] = $highlight_style;
				break;
		}

		switch ($response[0]['ResultClass_SysNick']) {
			case 'vizdor':
				$print_data['ResultClass_1'] = $highlight_style;
				break;

			case 'better':
				$print_data['ResultClass_2'] = $highlight_style;
				break;

			case 'dinam':
				$print_data['ResultClass_3'] = $highlight_style;
				break;

			case 'worse':
				$print_data['ResultClass_4'] = $highlight_style;
				break;

			case 'die':
				$print_data['ResultClass_5'] = $highlight_style;
				break;
		}

		switch ($response[0]['StickType_SysNick']) {
			case 'spravka':
				$print_data['StickType_1'] = $highlight_style;
				break;

			case 'blist':
				$print_data['StickType_11'] = $highlight_style;
				break;

			case 'dinam':
				$print_data['StickType_2'] = $highlight_style;
				break;

			case 'worse':
				$print_data['StickType_3'] = $highlight_style;
				break;
		}

		switch ($response[0]['FinalDeseaseType_SysNick']) {
			case 'good':
				$print_data['FinalDeseaseType_0'] = $highlight_style;
				break;

			case 'sharp':
				$print_data['FinalDeseaseType_1'] = $highlight_style;
				break;

			case 'hrnew':
				$print_data['FinalDeseaseType_2'] = $highlight_style;
				break;

			case 'hrold':
				$print_data['FinalDeseaseType_3'] = $highlight_style;
				break;

			case 'hrobostr':
				$print_data['FinalDeseaseType_4'] = $highlight_style;
				break;

			case 'otrav':
				$print_data['FinalDeseaseType_5'] = $highlight_style;
				break;

			case 'trauma':
				$print_data['FinalDeseaseType_6'] = $highlight_style;
				break;
		}

		switch ($response[0]['DeseaseTypeSop_SysNick']) {
			case 'sharp':
			case 'hrnew':
				$print_data['DeseaseTypeSop_1'] = $highlight_style;
				break;

			case 'hrold':
				$print_data['DeseaseTypeSop_2'] = $highlight_style;
				break;
		}

		switch ($response[0]['PrehospTrauma_Code']) {
			case 1:
				$print_data['PrehospTrauma_1'] = $highlight_style;
				break;

			case 2:
				$print_data['PrehospTrauma_2'] = $highlight_style;
				break;

			case 3:
				$print_data['PrehospTrauma_21'] = $highlight_style;
				break;

			case 4:
				$print_data['PrehospTrauma_3'] = $highlight_style;
				break;

			case 5:
				$print_data['PrehospTrauma_4'] = $highlight_style;
				break;

			case 6:
				$print_data['PrehospTrauma_6'] = $highlight_style;
				break;

			case 7:
				$print_data['PrehospTrauma_7'] = $highlight_style;
				break;

			case 8:
				$print_data['PrehospTrauma_8'] = $highlight_style;
				break;

			case 9:
				$print_data['PrehospTrauma_81'] = $highlight_style;
				break;

			case 10:
				$print_data['PrehospTrauma_9'] = $highlight_style;
				break;

			case 11:
				$print_data['PrehospTrauma_10'] = $highlight_style;
				break;

			case 12:
				$print_data['PrehospTrauma_11'] = $highlight_style;
				break;
		}

		switch ($response[0]['StickCause_SysNick']) {
			case 'desease':
				$print_data['StickCause_1'] = $highlight_style;
				break;

			case 'uhod':
				$print_data['StickCause_2'] = $highlight_style;
				break;

			case 'karantin':
				$print_data['StickCause_3'] = $highlight_style;
				break;

			case 'abort':
				$print_data['StickCause_4'] = $highlight_style;
				break;

			case 'pregn':
				$print_data['StickCause_5'] = $highlight_style;
				break;

			case 'kurort':
				$print_data['StickCause_6'] = $highlight_style;
				break;
		}

		switch ($response[0]['EvnStick_Sex']) {
			case 1:
				$print_data['EvnStick_Sex1'] = $highlight_style;
				break;

			case 2:
				$print_data['EvnStick_Sex2'] = $highlight_style;
				break;
		}

		return $this->parser->parse($template, $print_data);
	}

	/**
	 * Печать талона амбулаторного пациента (Башкирия)
	 * Входящие данные: $_GET['EvnPL_id']
	 * На выходе: форма для печати талона амбулаторного пациента
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function printEvnPLUfa()
	{
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLUfa', true);
		if ($data === false) {
			return false;
		}

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLFieldsUfa($data);

		if (!is_array($response) || count($response) == 0) {
			echo 'Ошибка при получении данных по талону';
			return true;
		}

		$evn_recept_data = array(
			array(
				'ER_EvnRecept_setDate' => '&nbsp;',
				'ER_EvnRecept_Ser' => '&nbsp;',
				'ER_EvnRecept_Num' => '&nbsp;',
				'ER_Diag_Code' => '&nbsp;',
				'ER_Drug_Name' => '&nbsp;',
				'ER_Drug_Dose' => '&nbsp;',
				'ER_EvnRecept_Kolvo' => '&nbsp;'
			),
			array(
				'ER_EvnRecept_setDate' => '&nbsp;',
				'ER_EvnRecept_Ser' => '&nbsp;',
				'ER_EvnRecept_Num' => '&nbsp;',
				'ER_Diag_Code' => '&nbsp;',
				'ER_Drug_Name' => '&nbsp;',
				'ER_Drug_Dose' => '&nbsp;',
				'ER_EvnRecept_Kolvo' => '&nbsp;'
			),
			array(
				'ER_EvnRecept_setDate' => '&nbsp;',
				'ER_EvnRecept_Ser' => '&nbsp;',
				'ER_EvnRecept_Num' => '&nbsp;',
				'ER_Diag_Code' => '&nbsp;',
				'ER_Drug_Name' => '&nbsp;',
				'ER_Drug_Dose' => '&nbsp;',
				'ER_EvnRecept_Kolvo' => '&nbsp;'
			),
			array(
				'ER_EvnRecept_setDate' => '&nbsp;',
				'ER_EvnRecept_Ser' => '&nbsp;',
				'ER_EvnRecept_Num' => '&nbsp;',
				'ER_Diag_Code' => '&nbsp;',
				'ER_Drug_Name' => '&nbsp;',
				'ER_Drug_Dose' => '&nbsp;',
				'ER_EvnRecept_Kolvo' => '&nbsp;'
			)
		);

		$evn_vizit_data = array(
			array(
				'EVPL_EvnVizitPL_setDate1' => '&nbsp;',
				'EVPL_UslugaComplex_Code1' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL1' => '&nbsp;',
				'EVPL_EvnVizitPL_setDate2' => '&nbsp;',
				'EVPL_UslugaComplex_Code2' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL2' => '&nbsp;'
			),
			array(
				'EVPL_EvnVizitPL_setDate1' => '&nbsp;',
				'EVPL_UslugaComplex_Code1' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL1' => '&nbsp;',
				'EVPL_EvnVizitPL_setDate2' => '&nbsp;',
				'EVPL_UslugaComplex_Code2' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL2' => '&nbsp;'
			),
			array(
				'EVPL_EvnVizitPL_setDate1' => '&nbsp;',
				'EVPL_UslugaComplex_Code1' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL1' => '&nbsp;',
				'EVPL_EvnVizitPL_setDate2' => '&nbsp;',
				'EVPL_UslugaComplex_Code2' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL2' => '&nbsp;'
			),
			array(
				'EVPL_EvnVizitPL_setDate1' => '&nbsp;',
				'EVPL_UslugaComplex_Code1' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL1' => '&nbsp;',
				'EVPL_EvnVizitPL_setDate2' => '&nbsp;',
				'EVPL_UslugaComplex_Code2' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL2' => '&nbsp;'
			)
		);

		$response_temp = $this->dbmodel->getEvnReceptData($data);
		if (is_array($response_temp)) {
			for ($i = 0; $i < (count($response_temp) <= 4 ? count($response_temp) : 4); $i++) {
				$evn_recept_data[$i]['ER_EvnRecept_setDate'] = $response_temp[$i]['ER_EvnRecept_setDate'];
				$evn_recept_data[$i]['ER_EvnRecept_Ser'] = $response_temp[$i]['ER_EvnRecept_Ser'];
				$evn_recept_data[$i]['ER_EvnRecept_Num'] = $response_temp[$i]['ER_EvnRecept_Num'];
				$evn_recept_data[$i]['ER_Diag_Code'] = $response_temp[$i]['ER_Diag_Code'];
				$evn_recept_data[$i]['ER_Drug_Name'] = $response_temp[$i]['ER_Drug_Name'];
				$evn_recept_data[$i]['ER_EvnRecept_Kolvo'] = $response_temp[$i]['ER_EvnRecept_Kolvo'];
			}
		}

		$response_temp = $this->dbmodel->getEvnVizitPLDataUfa($data);
		if (is_array($response_temp)) {
			for ($i = 0; $i < (count($response_temp) <= 4 ? count($response_temp) : 4); $i++) {
				$evn_vizit_data[$i]['EVPL_EvnVizitPL_setDate1'] = $response_temp[$i]['EvnVizitPL_setDate'];
				$evn_vizit_data[$i]['EVPL_UslugaComplex_Code1'] = $response_temp[$i]['UslugaComplex_Code'];
				$evn_vizit_data[$i]['EVPL_EvnVizitPL_UKL1'] = $response_temp[$i]['EvnVizitPL_UKL'];
			}

			if (count($response_temp) > 4) {
				for ($i = 0; $i < ((count($response_temp) - 4) <= 4 ? (count($response_temp) - 4) : 4); $i++) {
					$evn_vizit_data[$i]['EVPL_EvnVizitPL_setDate2'] = $response_temp[$i + 4]['EvnVizitPL_setDate'];
					$evn_vizit_data[$i]['EVPL_UslugaComplex_Code2'] = $response_temp[$i + 4]['UslugaComplex_Code'];
					$evn_vizit_data[$i]['EVPL_EvnVizitPL_UKL2'] = $response_temp[$i + 4]['EvnVizitPL_UKL'];
				}
			}
		}

		$highlight_style = 'font-weight: bold;';
		$template = 'evn_pl_template_list_a4_ufa';

		$print_data = array(
			'DeseaseTypeSop_1' => '',
			'DeseaseTypeSop_2' => '',
			'DiagAgg_Code' => returnValidHTMLString($response[0]['DiagAgg_Code']),
			'DiagSop_Code' => returnValidHTMLString($response[0]['DiagSop_Code']),
			'DirectType_1' => '',
			'DirectType_2' => '',
			'DirectType_3' => '',
			'DirectType_31' => '',
			'EvnPL_setDate' => returnValidHTMLString($response[0]['EvnPL_setDate']),
			'EvnPLTemplateTitle' => 'Печать талона амбулаторного пациента',
			'EvnReceptData' => $evn_recept_data,
			'EvnStick_Age' => returnValidHTMLString($response[0]['EvnStick_Age']),
			'EvnStick_begDate' => returnValidHTMLString($response[0]['EvnStick_begDate']),
			'EvnStick_endDate' => returnValidHTMLString($response[0]['EvnStick_endDate']),
			'EvnStick_Sex1' => '',
			'EvnStick_Sex2' => '',
			'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
			'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'EvnVizitData' => $evn_vizit_data,
			'FinalDeseaseType_0' => '',
			'FinalDeseaseType_1' => '',
			'FinalDeseaseType_2' => '',
			'FinalDeseaseType_3' => '',
			'FinalDeseaseType_4' => '',
			'FinalDeseaseType_5' => '',
			'FinalDeseaseType_6' => '',
			'FinalDiag_Code' => returnValidHTMLString($response[0]['FinalDiag_Code']),
			'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
			'LpuSectionProfile_Code' => returnValidHTMLString($response[0]['LpuSectionProfile_Code']),
			'LpuSectionProfile_Name' => returnValidHTMLString($response[0]['LpuSectionProfile_Name']),
			'MedPersonal_Fio' => returnValidHTMLString($response[0]['MedPersonal_Fio']),
			'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
			'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
			'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name']),
			'Person_Address' => returnValidHTMLString($response[0]['PAddress_Name']),
			'Person_Docum' => returnValidHTMLString($response[0]['Document_Ser']) . ' ' . returnValidHTMLString($response[0]['Document_Num']) . ' ' . returnValidHTMLString($response[0]['Document_begDate']),
			'Person_Fio' => mb_strtoupper(returnValidHTMLString($response[0]['Person_Fio'])),
			'Person_INN' => returnValidHTMLString($response[0]['Person_INN']),
			'Person_Snils' => returnValidHTMLString($response[0]['Person_Snils']),
			'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
			'Polis_begDate' => returnValidHTMLString($response[0]['Polis_begDate']),
			'Polis_endDate' => returnValidHTMLString($response[0]['Polis_endDate']),
			'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
			'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
			'PrehospDiag_Code' => returnValidHTMLString($response[0]['PrehospDiag_Code']),
			'PrehospDiag_regDate' => returnValidHTMLString($response[0]['PrehospDiag_regDate']),
			'PrehospTrauma_1' => '',
			'PrehospTrauma_2' => '',
			'PrehospTrauma_21' => '',
			'PrehospTrauma_3' => '',
			'PrehospTrauma_4' => '',
			'PrehospTrauma_6' => '',
			'PrehospTrauma_7' => '',
			'PrehospTrauma_8' => '',
			'PrehospTrauma_81' => '',
			'PrehospTrauma_9' => '',
			'PrehospTrauma_10' => '',
			'PrehospTrauma_11' => '',
			'ResultClass_1' => '',
			'ResultClass_2' => '',
			'ResultClass_3' => '',
			'ResultClass_4' => '',
			'ResultClass_5' => '',
			'ServiceType_Name' => returnValidHTMLString($response[0]['ServiceType_Name']),
			'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
			'StickCause_1' => '',
			'StickCause_2' => '',
			'StickCause_3' => '',
			'StickCause_4' => '',
			'StickCause_5' => '',
			'StickCause_6' => '',
			'StickType_1' => '',
			'StickType_11' => '',
			'StickType_2' => '',
			'StickType_3' => '',
			'VizitType_1' => '',
			'VizitType_2' => '',
			'VizitType_3' => '',
			'VizitType_4' => '',
			'VizitType_41' => '',
			'VizitType_5' => '',
			'PrivilegeType_0' => '',
			'PrivilegeType_81' => '',
			'PrivilegeType_82' => '',
			'PrivilegeType_83' => '',
			'PrivilegeType_84' => '',
			'PersonDeputy_Fio' => returnValidHTMLString($response[0]['PersonDeputy_Fio']),
		);

		$response_temp = $this->dbmodel->getPersonPrivilegeFedUfa(array('Person_id' => $response[0]['Person_id']));
		if (is_array($response_temp) && count($response_temp) > 0) {
			$epl_setDate = date("Y-m-d", strtotime($response[0]['EvnPL_setDate']));
			if ($epl_setDate >= $response_temp[0]['PersonPrivilege_begDate'] && $epl_setDate <= $response_temp[0]['PersonPrivilege_endDate']) {
				switch ($response_temp[0]['PrivilegeType_Code']) {
					case '81':
						$print_data['PrivilegeType_81'] = $highlight_style . ' text-decoration: underline;';
						break;
					case '82':
						$print_data['PrivilegeType_82'] = $highlight_style . ' text-decoration: underline;';
						break;
					case '83':
						$print_data['PrivilegeType_83'] = $highlight_style . ' text-decoration: underline;';
						break;
					case '84':
						$print_data['PrivilegeType_84'] = $highlight_style . ' text-decoration: underline;';
						break;
				}
			}
		}

		switch ($response[0]['VizitType_SysNick']) {
			case 'desease':
				$print_data['VizitType_1'] = $highlight_style;
				break;

			case 'prof':
				$print_data['VizitType_2'] = $highlight_style;
				break;

			case 'patron':
				$print_data['VizitType_3'] = $highlight_style;
				break;

			case 'disp':
				$print_data['VizitType_4'] = $highlight_style;
				break;

			case 'sert':
				$print_data['VizitType_41'] = $highlight_style;
				break;

			case 'rehab':
				$print_data['VizitType_5'] = $highlight_style;
				break;
		}

		switch ($response[0]['DirectType_SysNick']) {
			case 'stac':
				$print_data['DirectType_1'] = $highlight_style;
				break;

			case 'dstac':
				$print_data['DirectType_2'] = $highlight_style;
				break;

			case 'kons':
				$print_data['DirectType_3'] = $highlight_style;
				break;

			case 'other':
				$print_data['DirectType_31'] = $highlight_style;
				break;
		}

		switch ($response[0]['ResultClass_SysNick']) {
			case 'vizdor':
				$print_data['ResultClass_1'] = $highlight_style;
				break;

			case 'better':
				$print_data['ResultClass_2'] = $highlight_style;
				break;

			case 'dinam':
				$print_data['ResultClass_3'] = $highlight_style;
				break;

			case 'worse':
				$print_data['ResultClass_4'] = $highlight_style;
				break;

			case 'die':
				$print_data['ResultClass_5'] = $highlight_style;
				break;
		}

		switch ($response[0]['StickType_SysNick']) {
			case 'spravka':
				$print_data['StickType_1'] = $highlight_style;
				break;

			case 'blist':
				$print_data['StickType_11'] = $highlight_style;
				break;

			case 'dinam':
				$print_data['StickType_2'] = $highlight_style;
				break;

			case 'worse':
				$print_data['StickType_3'] = $highlight_style;
				break;
		}

		switch ($response[0]['FinalDeseaseType_SysNick']) {
			case 'good':
				$print_data['FinalDeseaseType_0'] = $highlight_style;
				break;

			case 'sharp':
				$print_data['FinalDeseaseType_1'] = $highlight_style;
				break;

			case 'hrnew':
				$print_data['FinalDeseaseType_2'] = $highlight_style;
				break;

			case 'hrold':
				$print_data['FinalDeseaseType_3'] = $highlight_style;
				break;

			case 'hrobostr':
				$print_data['FinalDeseaseType_4'] = $highlight_style;
				break;

			case 'otrav':
				$print_data['FinalDeseaseType_5'] = $highlight_style;
				break;

			case 'trauma':
				$print_data['FinalDeseaseType_6'] = $highlight_style;
				break;
		}

		switch ($response[0]['DeseaseTypeSop_SysNick']) {
			case 'sharp':
			case 'hrnew':
				$print_data['DeseaseTypeSop_1'] = $highlight_style;
				break;

			case 'hrold':
				$print_data['DeseaseTypeSop_2'] = $highlight_style;
				break;
		}

		switch ($response[0]['PrehospTrauma_Code']) {
			case 1:
				$print_data['PrehospTrauma_1'] = $highlight_style;
				break;

			case 2:
				$print_data['PrehospTrauma_2'] = $highlight_style;
				break;

			case 3:
				$print_data['PrehospTrauma_21'] = $highlight_style;
				break;

			case 4:
				$print_data['PrehospTrauma_3'] = $highlight_style;
				break;

			case 5:
				$print_data['PrehospTrauma_4'] = $highlight_style;
				break;

			case 6:
				$print_data['PrehospTrauma_6'] = $highlight_style;
				break;

			case 7:
				$print_data['PrehospTrauma_7'] = $highlight_style;
				break;

			case 8:
				$print_data['PrehospTrauma_8'] = $highlight_style;
				break;

			case 9:
				$print_data['PrehospTrauma_81'] = $highlight_style;
				break;

			case 10:
				$print_data['PrehospTrauma_9'] = $highlight_style;
				break;

			case 11:
				$print_data['PrehospTrauma_10'] = $highlight_style;
				break;

			case 12:
				$print_data['PrehospTrauma_11'] = $highlight_style;
				break;
		}

		switch ($response[0]['StickCause_SysNick']) {
			case 'desease':
				$print_data['StickCause_1'] = $highlight_style;
				break;

			case 'uhod':
				$print_data['StickCause_2'] = $highlight_style;
				break;

			case 'karantin':
				$print_data['StickCause_3'] = $highlight_style;
				break;

			case 'abort':
				$print_data['StickCause_4'] = $highlight_style;
				break;

			case 'pregn':
				$print_data['StickCause_5'] = $highlight_style;
				break;

			case 'kurort':
				$print_data['StickCause_6'] = $highlight_style;
				break;
		}

		switch ($response[0]['EvnStick_Sex']) {
			case 1:
				$print_data['EvnStick_Sex1'] = $highlight_style;
				break;

			case 2:
				$print_data['EvnStick_Sex2'] = $highlight_style;
				break;
		}

		return $this->parser->parse($template, $print_data);
	}

	/**
	 * Печать талона амбулаторного пациента
	 * Входящие данные: $_GET['Person_id']
	 * На выходе: форма для печати бланка талона амбулаторного пациента
	 * Используется: форма редактирования бланка талона амбулаторного пациента
	 */
	function printEvnPLBlank()
	{
		switch ($_SESSION['region']['nick']) {
			case 'perm':
				$this->printEvnPLBlankPerm();
				break;

			case 'khak':
				$this->printEvnPLBlankMsk('evn_pl_template_blank_a4_hakasiya');
				break;
			case 'msk':
				$this->printEvnPLBlankMsk();
				break;

			case 'ufa':
				$this->printEvnPLBlankUfa();
				break;

			case 'pskov':
				$this->printEvnPLBlankPskov();
				break;

			case 'kareliya':
				$this->printEvnPLBlankPskov($tpl = 'evn_pl_template_blank_a4_kareliya');
				break;

			case 'astra':
				$this->printEvnPLBlankAstra();
				break;

			default:
				// по умолчанию тоже Пермский бланк 
				$this->printEvnPLBlankPerm();
				//echo "Не указан регион";
				break;
		}

		return true;
	}

	/**
	 * Печать талона амбулаторного пациента (Пермский край)
	 * Входящие данные: $_GET['Person_id']
	 * На выходе: форма для печати бланка талона амбулаторного пациента
	 * Используется: форма редактирования бланка талона амбулаторного пациента
	 */
	function printEvnPLBlankPerm()
	{
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLBlankPerm', true);
		if ($data === false) {
			return false;
		}

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLBlankFieldsPerm($data);

		if (!is_array($response) || count($response) == 0) {
			echo 'Ошибка при получении данных из БД';
			return false;
		}

		$template = 'evn_pl_template_blank_a4_perm';

		$print_data = array(
			'Document_begDate' => strlen($response[0]['Document_begDate']) > 0 ? htmlspecialchars($response[0]['Document_begDate']) : '&nbsp;',
			'Document_Num' => strlen($response[0]['Document_Num']) > 0 ? htmlspecialchars($response[0]['Document_Num']) : '&nbsp;',
			'Document_Ser' => strlen($response[0]['Document_Ser']) > 0 ? htmlspecialchars($response[0]['Document_Ser']) : '&nbsp;',
			'DocumentType_Name' => strlen($response[0]['DocumentType_Name']) > 0 ? htmlspecialchars($response[0]['DocumentType_Name']) : '&nbsp;',
			'EvnPLTemplateBlankTitle' => (defined('USE_UTF') && USE_UTF) ? toUtf('Печать талона', true) : 'Печать талона',
			'EvnUdost_Num' => strlen($response[0]['EvnUdost_Num']) > 0 ? htmlspecialchars($response[0]['EvnUdost_Num']) : '&nbsp;',
			'EvnUdost_Ser' => strlen($response[0]['EvnUdost_Ser']) > 0 ? htmlspecialchars($response[0]['EvnUdost_Ser']) : '&nbsp;',
			'PersonPrivilege_begDate' => strlen($response[0]['PersonPrivilege_begDate']) > 0 ? htmlspecialchars($response[0]['PersonPrivilege_begDate']) : '&nbsp;',
			'PrivilegeType_Name' => strlen($response[0]['PrivilegeType_Name']) > 0 ? htmlspecialchars($response[0]['PrivilegeType_Name']) : '&nbsp;',
			'KLAreaType_Name' => strlen($response[0]['KLAreaType_Name']) > 0 ? htmlspecialchars($response[0]['KLAreaType_Name']) : '&nbsp;',
			'Lpu_Name' => strlen($response[0]['Lpu_Name']) > 0 ? htmlspecialchars($response[0]['Lpu_Name']) : '&nbsp;',
			'LpuRegion_Name' => strlen($response[0]['LpuRegion_Name']) > 0 ? htmlspecialchars($response[0]['LpuRegion_Name']) : '&nbsp;',
			'Org_Name' => strlen($response[0]['Org_Name']) > 0 ? htmlspecialchars($response[0]['Org_Name']) : '&nbsp;',
			'OrgDep_Name' => strlen($response[0]['OrgDep_Name']) > 0 ? htmlspecialchars($response[0]['OrgDep_Name']) : '&nbsp;',
			'OrgSmo_Name' => strlen($response[0]['OrgSmo_Name']) > 0 ? htmlspecialchars($response[0]['OrgSmo_Name']) : '&nbsp;',
			'PAddress_Name' => strlen($response[0]['PAddress_Name']) > 0 ? htmlspecialchars($response[0]['PAddress_Name']) : '&nbsp;',
			'Person_Birthday' => strlen($response[0]['Person_Birthday']) > 0 ? htmlspecialchars($response[0]['Person_Birthday']) : '&nbsp;',
			'Person_Fio' => strlen(trim($response[0]['Person_Fio'])) > 0 ? mb_strtoupper(htmlspecialchars($response[0]['Person_Fio'])) : '&nbsp;',
			'PersonCard_Code' => strlen($response[0]['PersonCard_Code']) > 0 ? htmlspecialchars($response[0]['PersonCard_Code']) : '&nbsp;',
			'Polis_begDate' => strlen($response[0]['Polis_begDate']) > 0 ? htmlspecialchars($response[0]['Polis_begDate']) : '&nbsp;',
			'Polis_endDate' => strlen($response[0]['Polis_endDate']) > 0 ? htmlspecialchars($response[0]['Polis_endDate']) : '&nbsp;',
			'Polis_Num' => strlen($response[0]['Polis_Num']) > 0 ? htmlspecialchars($response[0]['Polis_Num']) : '&nbsp;',
			'Polis_Ser' => strlen($response[0]['Polis_Ser']) > 0 ? htmlspecialchars($response[0]['Polis_Ser']) : '&nbsp;',
			'PolisType_Name' => strlen($response[0]['PolisType_Name']) > 0 ? htmlspecialchars($response[0]['PolisType_Name']) : '&nbsp;',
			'Post_Name' => strlen($response[0]['Post_Name']) > 0 ? htmlspecialchars($response[0]['Post_Name']) : '&nbsp;',
			'Sex_Name' => strlen($response[0]['Sex_Name']) > 0 ? htmlspecialchars($response[0]['Sex_Name']) : '&nbsp;',
			'SocStatus_Name' => strlen($response[0]['SocStatus_Name']) > 0 ? htmlspecialchars($response[0]['SocStatus_Name']) : '&nbsp;',
			'UAddress_Name' => strlen($response[0]['UAddress_Name']) > 0 ? htmlspecialchars($response[0]['UAddress_Name']) : '&nbsp;'
		);

		if (defined('USE_UTF') && USE_UTF) {
			if (strlen(trim($response[0]['Person_Fio'])) == 0) {
				$print_data['Person_Fio'] = '&nbsp;';
			} else {
				$print_data['Person_Fio'] = mb_strtoupper($response[0]['Person_Fio']);
			}
		}

		return $this->parser->parse($template, $print_data, false, (defined('USE_UTF') && USE_UTF));
	}

	/**
	 * Печать талона амбулаторного пациента (Башкирия)
	 * Входящие данные: $_GET['Person_id']
	 * На выходе: форма для печати бланка талона амбулаторного пациента
	 * Используется: форма редактирования бланка талона амбулаторного пациента
	 */
	function printEvnPLBlankUfa()
	{
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLBlankUfa', true);
		if ($data === false) {
			return false;
		}

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLBlankFieldsUfa($data);

		if (!is_array($response)) {
			echo 'Ошибка при получении данных из БД';
			return false;
		}

		$template = 'evn_pl_template_blank_a4_ufa';

		$print_data = array(
			'EvnPL_setDate' => date('d.m.Y'),
			'EvnPLBlankTemplateTitle' => 'Печать талона амбулаторного пациента',
			'Lpu_Name' => '&nbsp',
			'LpuRegion_Name' => '&nbsp',
			'LpuSectionProfile_Code' => '&nbsp',
			'LpuSectionProfile_Name' => '&nbsp',
			'MedPersonal_Fio' => '&nbsp',
			'OrgJob_Name' => '&nbsp',
			'OrgSmo_Name' => '&nbsp',
			'PayType_Name' => '&nbsp',
			'Person_Address' => '&nbsp',
			'Person_Birthday' => '&nbsp',
			'Person_Docum' => '&nbsp',
			'Person_Fio' => '&nbsp',
			'Person_INN' => '&nbsp',
			'Person_Snils' => '&nbsp',
			'PersonCard_Code' => '&nbsp',
			'Polis_begDate' => '&nbsp',
			'Polis_endDate' => '&nbsp',
			'Polis_Num' => '&nbsp',
			'Polis_Ser' => '&nbsp',
			'ServiceType_Name' => '&nbsp',
			'Sex_Name' => '&nbsp',
			'SocStatus_Name' => '&nbsp',
			'PrivilegeType_0' => '',
			'PrivilegeType_81' => '',
			'PrivilegeType_82' => '',
			'PrivilegeType_83' => '',
			'PrivilegeType_84' => '',
			'PersonDeputy_Fio' => ''
		);

		if (count($response) == 1) {
			$print_data['Lpu_Name'] = returnValidHTMLString($response[0]['Lpu_Name']);
			$print_data['LpuRegion_Name'] = returnValidHTMLString($response[0]['LpuRegion_Name']);
			$print_data['LpuSectionProfile_Code'] = returnValidHTMLString($response[0]['LpuSectionProfile_Code']);
			$print_data['LpuSectionProfile_Name'] = returnValidHTMLString($response[0]['LpuSectionProfile_Name']);
			$print_data['MedPersonal_Fio'] = returnValidHTMLString($response[0]['MedPersonal_Fio']);
			$print_data['OrgJob_Name'] = returnValidHTMLString($response[0]['OrgJob_Name']);
			$print_data['PayType_Name'] = returnValidHTMLString($response[0]['PayType_Name']);
			$print_data['Person_Address'] = returnValidHTMLString($response[0]['PAddress_Name']);
			$print_data['Person_Birthday'] = returnValidHTMLString($response[0]['Person_Birthday']);
			$print_data['Person_Docum'] = returnValidHTMLString($response[0]['Document_Ser']) . ' ' . returnValidHTMLString($response[0]['Document_Num']) . ' ' . returnValidHTMLString($response[0]['Document_begDate']);
			$print_data['Person_Fio'] = mb_strtoupper(returnValidHTMLString($response[0]['Person_Fio']));
			$print_data['Person_INN'] = returnValidHTMLString($response[0]['Person_INN']);
			$print_data['Person_Snils'] = returnValidHTMLString($response[0]['Person_Snils']);
			$print_data['PersonCard_Code'] = returnValidHTMLString($response[0]['PersonCard_Code']);
			if ($response[0]['Polis_endDate']) {
				$print_data['Polis_Num'] = '';
				$print_data['Polis_Ser'] = '';
				$print_data['Polis_begDate'] = '';
				$print_data['Polis_endDate'] = '';
				$print_data['OrgSmo_Name'] = '';
			} else {
				$print_data['OrgSmo_Name'] = returnValidHTMLString($response[0]['OrgSmo_Name']);
				$print_data['Polis_begDate'] = returnValidHTMLString($response[0]['Polis_begDate']);
				$print_data['Polis_endDate'] = returnValidHTMLString($response[0]['Polis_endDate']);
				$print_data['Polis_Num'] = returnValidHTMLString($response[0]['Polis_Num']);
				$print_data['Polis_Ser'] = returnValidHTMLString($response[0]['Polis_Ser']);
			}
			$print_data['ServiceType_Name'] = returnValidHTMLString($response[0]['ServiceType_Name']);
			$print_data['Sex_Name'] = returnValidHTMLString($response[0]['Sex_Name']);
			$print_data['SocStatus_Name'] = returnValidHTMLString($response[0]['SocStatus_Name']);
			$print_data['PersonDeputy_Fio'] = returnValidHTMLString($response[0]['PersonDeputy_Fio']);
		}

		$highlight_style = 'font-weight: bold; text-decoration: underline;';
		$response = $this->dbmodel->getPersonPrivilegeFedUfa(array('Person_id' => $data['Person_id']));
		//$print_data['PrivilegeType_Name'] = null;
		if (is_array($response) && count($response) > 0) {
			//$print_data['PrivilegeType_Name'] = $response[0]['PrivilegeType_Name'];
			switch ($response[0]['PrivilegeType_Code']) {
				case '81':
					$print_data['PrivilegeType_81'] = $highlight_style;
					break;
				case '82':
					$print_data['PrivilegeType_82'] = $highlight_style;
					break;
				case '83':
					$print_data['PrivilegeType_83'] = $highlight_style;
					break;
				case '84':
					$print_data['PrivilegeType_84'] = $highlight_style;
					break;
			}
		}

		return $this->parser->parse($template, $print_data);
	}

	/**
	 * Печать талона амбулаторного пациента (Московская область)
	 * Входящие данные: $_GET['Person_id']
	 * На выходе: форма для печати бланка талона амбулаторного пациента
	 * Используется: форма редактирования бланка талона амбулаторного пациента
	 */
	function printEvnPLBlankMsk($tpl = 'evn_pl_template_blank_a4_msk')
	{
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLBlankMsk', true);
		if ($data === false) {
			return false;
		}

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLBlankFieldsMsk($data);

		if (!is_array($response) || count($response) == 0) {
			echo 'Ошибка при получении данных из БД';
			return false;
		}

		$template = $tpl;

		foreach ($response[1] as &$num) {
			$num = sprintf('%1$03d', $num);
		}

		$TimetableType = '';
		switch ($response[0]['TimetableType_id']) {
			case "1":
				$TimetableType = 'Обычная бирка';
				break;
			case "2":
				$TimetableType = 'Резервная бирка';
				break;
			case "3":
				$TimetableType = 'Платная бирка';
				break;
			case "4":
				$TimetableType = 'Ветеранская бирка';
				break;
			case "5":
				$TimetableType = 'Внешняя бирка';
				break;
		}
		$print_data = array(
			'Document_begDate' => strlen($response[0]['Document_begDate']) > 0 ? htmlspecialchars($response[0]['Document_begDate']) : '&nbsp;',
			'Document_Num' => strlen($response[0]['Document_Num']) > 0 ? htmlspecialchars($response[0]['Document_Num']) : '&nbsp;',
			'Document_Ser' => strlen($response[0]['Document_Ser']) > 0 ? htmlspecialchars($response[0]['Document_Ser']) : '&nbsp;',
			'DocumentType_Name' => strlen($response[0]['DocumentType_Name']) > 0 ? htmlspecialchars($response[0]['DocumentType_Name']) : '&nbsp;',
			'EvnPLTemplateBlankTitle' => 'Печать талона',
			'EvnUdost_Num' => strlen($response[0]['EvnUdost_Num']) > 0 ? htmlspecialchars($response[0]['EvnUdost_Num']) : '&nbsp;',
			'EvnUdost_Ser' => strlen($response[0]['EvnUdost_Ser']) > 0 ? htmlspecialchars($response[0]['EvnUdost_Ser']) : '&nbsp;',
			'PersonPrivilege_begDate' => strlen($response[0]['PersonPrivilege_begDate']) > 0 ? htmlspecialchars($response[0]['PersonPrivilege_begDate']) : '&nbsp;',
			'PrivilegeType_Name' => strlen($response[0]['PrivilegeType_Name']) > 0 ? htmlspecialchars($response[0]['PrivilegeType_Name']) : '&nbsp;',
			'KLAreaType_Name' => strlen($response[0]['KLAreaType_Name']) > 0 ? htmlspecialchars($response[0]['KLAreaType_Name']) : '&nbsp;',
			'KLAreaType_id' => strlen($response[0]['KLAreaType_id']) > 0 ? htmlspecialchars($response[0]['KLAreaType_id']) : '&nbsp;',
			'Lpu_Name' => strlen($response[0]['Lpu_Name']) > 0 ? htmlspecialchars($response[0]['Lpu_Name']) : '&nbsp;',
			'LpuAddress' => strlen($response[0]['LpuAddress']) > 0 ? htmlspecialchars($response[0]['LpuAddress']) : '&nbsp;',
			'Lpu_OGRN' => strlen($response[0]['Lpu_OGRN']) > 0 ? htmlspecialchars($response[0]['Lpu_OGRN']) : '&nbsp;',
			'LpuRegion_Name' => strlen($response[0]['LpuRegion_Name']) > 0 ? htmlspecialchars($response[0]['LpuRegion_Name']) : '&nbsp;',
			'Org_Name' => strlen($response[0]['Org_Name']) > 0 ? htmlspecialchars($response[0]['Org_Name']) : '&nbsp;',
			'OrgDep_Name' => strlen($response[0]['OrgDep_Name']) > 0 ? htmlspecialchars($response[0]['OrgDep_Name']) : '&nbsp;',
			'OrgSmo_Name' => strlen($response[0]['OrgSmo_Name']) > 0 ? htmlspecialchars($response[0]['OrgSmo_Name']) : '&nbsp;',
			'PAddress_Name' => strlen($response[0]['PAddress_Name']) > 0 ? htmlspecialchars($response[0]['PAddress_Name']) : '&nbsp;',
			'Person_Birthday' => strlen($response[0]['Person_Birthday']) > 0 ? htmlspecialchars($response[0]['Person_Birthday']) : '&nbsp;',
			'Person_Fio' => strlen(trim($response[0]['Person_Fio'])) > 0 ? mb_strtoupper(htmlspecialchars($response[0]['Person_Fio'])) : '&nbsp;',
			'Person_Snils' => strlen($response[0]['Person_Snils']) > 0 ? htmlspecialchars($response[0]['Person_Snils']) : '&nbsp;',
			'PersonCard_Code' => strlen($response[0]['PersonCard_Code']) > 0 ? htmlspecialchars($response[0]['PersonCard_Code']) : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
			'Polis_begDate' => strlen($response[0]['Polis_begDate']) > 0 ? htmlspecialchars($response[0]['Polis_begDate']) : '&nbsp;',
			'Polis_endDate' => strlen($response[0]['Polis_endDate']) > 0 ? htmlspecialchars($response[0]['Polis_endDate']) : '&nbsp;',
			'Polis_Num' => strlen($response[0]['Polis_Num']) > 0 ? htmlspecialchars($response[0]['Polis_Num']) : '&nbsp;',
			'Polis_Ser' => strlen($response[0]['Polis_Ser']) > 0 ? htmlspecialchars($response[0]['Polis_Ser']) : '&nbsp;',
			'PolisType_Name' => strlen($response[0]['PolisType_Name']) > 0 ? htmlspecialchars($response[0]['PolisType_Name']) : '&nbsp;',
			'Post_Name' => strlen($response[0]['Post_Name']) > 0 ? htmlspecialchars($response[0]['Post_Name']) : '&nbsp;',
			'Sex_Name' => strlen($response[0]['Sex_Name']) > 0 ? htmlspecialchars($response[0]['Sex_Name']) : '&nbsp;',
			'SocStatus_Name' => strlen($response[0]['SocStatus_Name']) > 0 ? htmlspecialchars($response[0]['SocStatus_Name']) : '&nbsp;',
			'SocStatus_Code' => strlen($response[0]['SocStatus_Code']) > 0 ? htmlspecialchars($response[0]['SocStatus_Code']) : '&nbsp;',
			'UAddress_Name' => strlen($response[0]['UAddress_Name']) > 0 ? htmlspecialchars($response[0]['UAddress_Name']) : '&nbsp;',
			'PrivilegeType_Code' => count($response[1]) > 0 ? join(", ", array_unique($response[1])) : '&nbsp;',
			'TimetableGraf_recDate' => strlen($response[0]['TimetableGraf_recDate']) > 0 ? htmlspecialchars($response[0]['TimetableGraf_recDate']) : '&nbsp;',
			'MSF_Fio' => strlen($response[0]['MSF_Fio']) > 0 ? htmlspecialchars($response[0]['MSF_Fio']) : '&nbsp;',
			'MedPersonal_TabCode' => strlen($response[0]['MedPersonal_TabCode']) > 0 ? '</td><td width=202><b>' . htmlspecialchars($response[0]['MedPersonal_TabCode']) . '</b>' : '</td> <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block">',
			'TimetableType' => $TimetableType
		);

		return $this->parser->parse($template, $print_data);
	}

	/**
	 * Печать талона амбулаторного пациента (Псковская область)
	 * Входящие данные: $_GET['Person_id']
	 * На выходе: форма для печати бланка талона амбулаторного пациента
	 * Используется: форма редактирования бланка талона амбулаторного пациента
	 */
	function printEvnPLBlankPskov($tpl = 'evn_pl_template_blank_a4_pskov')
	{
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLBlankPskov', true);
		if ($data === false) {
			return false;
		}

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLBlankFieldsPskov($data);

		if (!is_array($response) || count($response) == 0) {
			echo 'Ошибка при получении данных из БД';
			return false;
		}

		$template = $tpl;

		foreach ($response[1] as &$num) {
			$num = sprintf('%1$03d', $num);
		}

		$print_data = array(
			'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
			'OrgUnion_Name' => returnValidHTMLString($response[0]['OrgUnion_Name']),
			'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
			'Document_begDate' => strlen($response[0]['Document_begDate']) > 0 ? htmlspecialchars($response[0]['Document_begDate']) : '&nbsp;',
			'Document_Num' => strlen($response[0]['Document_Num']) > 0 ? htmlspecialchars($response[0]['Document_Num']) : '&nbsp;',
			'Document_Ser' => strlen($response[0]['Document_Ser']) > 0 ? htmlspecialchars($response[0]['Document_Ser']) : '&nbsp;',
			'DocumentType_Name' => strlen($response[0]['DocumentType_Name']) > 0 ? htmlspecialchars($response[0]['DocumentType_Name']) : '&nbsp;',
			'EvnPLTemplateBlankTitle' => 'Печать талона',
			'EvnUdost_Num' => strlen($response[0]['EvnUdost_Num']) > 0 ? htmlspecialchars($response[0]['EvnUdost_Num']) : '&nbsp;',
			'EvnUdost_Ser' => strlen($response[0]['EvnUdost_Ser']) > 0 ? htmlspecialchars($response[0]['EvnUdost_Ser']) : '&nbsp;',
			'PersonPrivilege_begDate' => strlen($response[0]['PersonPrivilege_begDate']) > 0 ? htmlspecialchars($response[0]['PersonPrivilege_begDate']) : '&nbsp;',
			'PrivilegeType_Name' => strlen($response[0]['PrivilegeType_Name']) > 0 ? htmlspecialchars($response[0]['PrivilegeType_Name']) : '&nbsp;',
			'PrivilegeType_Name' => strlen($response[0]['PrivilegeType_Name']) > 0 ? htmlspecialchars($response[0]['PrivilegeType_Name']) : '&nbsp;',
			'KLAreaType_Code' => strlen($response[0]['KLAreaType_Code']) > 0 ? htmlspecialchars($response[0]['KLAreaType_Code']) : '&nbsp;',
			'KLAreaType_Name' => strlen($response[0]['KLAreaType_Name']) > 0 ? htmlspecialchars($response[0]['KLAreaType_Name']) : '&nbsp;',
			'Lpu_Name' => strlen($response[0]['Lpu_Name']) > 0 ? htmlspecialchars($response[0]['Lpu_Name']) : '&nbsp;',
			'LpuAddress' => strlen($response[0]['LpuAddress']) > 0 ? htmlspecialchars($response[0]['LpuAddress']) : '&nbsp;',
			'Lpu_OGRN' => strlen($response[0]['Lpu_OGRN']) > 0 ? htmlspecialchars($response[0]['Lpu_OGRN']) : '&nbsp;',
			'LpuRegion_Name' => strlen($response[0]['LpuRegion_Name']) > 0 ? htmlspecialchars($response[0]['LpuRegion_Name']) : '&nbsp;',
			'MedPersonal_Code' =>
				(!empty($response[0]['MedPersonal_Code']))
					? '<td style="border: 1px solid" colspan="10">' . returnValidHTMLString($response[0]['MedPersonal_Code']) . '</td>'
					: '<td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td>',
			'OrgDep_Name' => strlen($response[0]['OrgDep_Name']) > 0 ? htmlspecialchars($response[0]['OrgDep_Name']) : '&nbsp;',
			'OrgSmo_Name' => strlen($response[0]['OrgSmo_Name']) > 0 ? htmlspecialchars($response[0]['OrgSmo_Name']) : '&nbsp;',
			'PAddress_Name' => strlen($response[0]['PAddress_Name']) > 0 ? htmlspecialchars($response[0]['PAddress_Name']) : '&nbsp;',
			'Person_Birthday' => strlen($response[0]['Person_Birthday']) > 0 ? htmlspecialchars($response[0]['Person_Birthday']) : '&nbsp;',
			'Person_Fio' => strlen(trim($response[0]['Person_Fio'])) > 0 ? mb_strtoupper(htmlspecialchars($response[0]['Person_Fio'])) : '&nbsp;',
			'Person_Snils' => strlen($response[0]['Person_Snils']) > 0 ? htmlspecialchars($response[0]['Person_Snils']) : '&nbsp;',
			'PersonCard_Code' => strlen($response[0]['PersonCard_Code']) > 0 ? htmlspecialchars($response[0]['PersonCard_Code']) : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
			'Polis_begDate' => strlen($response[0]['Polis_begDate']) > 0 ? htmlspecialchars($response[0]['Polis_begDate']) : '&nbsp;',
			'Polis_endDate' => strlen($response[0]['Polis_endDate']) > 0 ? htmlspecialchars($response[0]['Polis_endDate']) : '&nbsp;',
			'Polis_Num' => strlen($response[0]['Polis_Num']) > 0 ? htmlspecialchars($response[0]['Polis_Num']) : '&nbsp;',
			'Polis_Ser' => strlen($response[0]['Polis_Ser']) > 0 ? htmlspecialchars($response[0]['Polis_Ser']) : '&nbsp;',
			'PolisType_Name' => strlen($response[0]['PolisType_Name']) > 0 ? htmlspecialchars($response[0]['PolisType_Name']) : '&nbsp;',
			'Post_Name' => strlen($response[0]['Post_Name']) > 0 ? htmlspecialchars($response[0]['Post_Name']) : '&nbsp;',
			'Sex_Code' => strlen($response[0]['Sex_Code']) > 0 ? htmlspecialchars($response[0]['Sex_Code']) : '&nbsp;',
			'Sex_Name' => strlen($response[0]['Sex_Name']) > 0 ? htmlspecialchars($response[0]['Sex_Name']) : '&nbsp;',
			'SocStatus_Code' => strlen($response[0]['SocStatus_Code']) > 0 ? htmlspecialchars($response[0]['SocStatus_Code']) : '&nbsp;',
			'SocStatus_Name' => strlen($response[0]['SocStatus_Name']) > 0 ? htmlspecialchars($response[0]['SocStatus_Name']) : '&nbsp;',
			'UAddress_Name' => strlen($response[0]['UAddress_Name']) > 0 ? htmlspecialchars($response[0]['UAddress_Name']) : '&nbsp;',
			'PrivilegeType_Code' => count($response[1]) > 0 ? join(", ", array_unique($response[1])) : '&nbsp;',
			'TimetableGraf_recDate' => strlen($response[0]['TimetableGraf_recDate']) > 0 ? htmlspecialchars($response[0]['TimetableGraf_recDate']) : '&nbsp;',
			'MSF_Fio' => strlen($response[0]['MSF_Fio']) > 0 ? htmlspecialchars($response[0]['MSF_Fio']) : '&nbsp;',
			'MedPersonal_TabCode' => strlen($response[0]['MedPersonal_TabCode']) > 0 ? '</td><td style="border: 1px solid;" colspan="10"><b>' . htmlspecialchars($response[0]['MedPersonal_TabCode']) . '</b>' : '</td> <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block"></td>  <td class="block">'
		);
		$print_data['lrn1'] = '&nbsp;';
		$print_data['lrn2'] = '&nbsp;';
		$print_data['lrn3'] = '&nbsp;';
		if (!empty($response[0]['LpuRegion_Name'])) {
			for ($i = 1; $i <= 3; $i++) {
				if (isset($response[0]['LpuRegion_Name'][$i - 1])) {
					$print_data['lrn' . $i] = $response[0]['LpuRegion_Name'][$i - 1];
				}
			}
		}

		$print_data['Document'] = (strlen($print_data['Document_Num']))
			? "тип док-та <u>&nbsp;&nbsp;&nbsp;{$response[0]['DocumentType_Code']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u> серия <u>{$print_data['Document_Ser']}</u> номер <u>{$print_data['Document_Num']}</u>"
			: 'тип док-та ______________ серия _______ номер ______';

		return $this->parser->parse($template, $print_data);
	}

	/**
	 * @return bool|string
	 * Печать бланка ТАП для Астрахани
	 */
	function printEvnPLBlankAstra()
	{
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLBlankPskov', true);
		if ($data === false) {
			return false;
		}

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLBlankFieldsPskov($data);

		if (!is_array($response) || count($response) == 0) {
			echo 'Ошибка при получении данных из БД';
			return false;
		}

		$template = 'evn_pl_template_blank_a4_astra';

		$print_data = array(
			'Document_begDate' => strlen($response[0]['Document_begDate']) > 0 ? htmlspecialchars($response[0]['Document_begDate']) : '&nbsp;',
			'Document_Num' => strlen($response[0]['Document_Num']) > 0 ? htmlspecialchars($response[0]['Document_Num']) : '&nbsp;',
			'Document_Ser' => strlen($response[0]['Document_Ser']) > 0 ? htmlspecialchars($response[0]['Document_Ser']) : '&nbsp;',
			'DocumentType_Name' => strlen($response[0]['DocumentType_Name']) > 0 ? htmlspecialchars($response[0]['DocumentType_Name']) : '&nbsp;',
			'EvnPLTemplateBlankTitle' => 'Печать талона',
			'EvnUdost_Num' => strlen($response[0]['EvnUdost_Num']) > 0 ? htmlspecialchars($response[0]['EvnUdost_Num']) : '&nbsp;',
			'EvnUdost_Ser' => strlen($response[0]['EvnUdost_Ser']) > 0 ? htmlspecialchars($response[0]['EvnUdost_Ser']) : '&nbsp;',
			'PersonPrivilege_begDate' => strlen($response[0]['PersonPrivilege_begDate']) > 0 ? htmlspecialchars($response[0]['PersonPrivilege_begDate']) : '&nbsp;',
			'PrivilegeType_Name' => strlen($response[0]['PrivilegeType_Name']) > 0 ? htmlspecialchars($response[0]['PrivilegeType_Name']) : '&nbsp;',
			'KLAreaType_Name' => strlen($response[0]['KLAreaType_Name']) > 0 ? htmlspecialchars($response[0]['KLAreaType_Name']) : '&nbsp;',
			'Lpu_Name' => strlen($response[0]['Lpu_Name']) > 0 ? htmlspecialchars($response[0]['Lpu_Name']) : '&nbsp;',
			'Lpu_Address' => strlen($response[0]['Lpu_UAddress']) > 0 ? htmlspecialchars($response[0]['Lpu_UAddress']) : '&nbsp;',
			'LpuRegion_Name' => strlen($response[0]['LpuRegion_Name']) > 0 ? htmlspecialchars($response[0]['LpuRegion_Name']) : '&nbsp;',
			'OrgDep_Name' => strlen($response[0]['OrgDep_Name']) > 0 ? htmlspecialchars($response[0]['OrgDep_Name']) : '&nbsp;',
			'OrgSmo_Name' => strlen($response[0]['OrgSmo_Name']) > 0 ? htmlspecialchars($response[0]['OrgSmo_Name']) : '&nbsp;',
			'PAddress_Name' => strlen($response[0]['PAddress_Name']) > 0 ? htmlspecialchars($response[0]['PAddress_Name']) : '&nbsp;',
			'Person_Birthday' => strlen($response[0]['Person_Birthday']) > 0 ? htmlspecialchars($response[0]['Person_Birthday']) : '&nbsp;',
			'Person_BirthdayStr' => strlen($response[0]['Person_BirthdayStr']) > 0 ? htmlspecialchars($response[0]['Person_BirthdayStr']) : '&nbsp;',
			'Person_Fio' => strlen(trim($response[0]['Person_Fio'])) > 0 ? mb_strtoupper(htmlspecialchars($response[0]['Person_Fio'])) : '&nbsp;',
			'PersonCard_Code' => strlen($response[0]['PersonCard_Code']) > 0 ? htmlspecialchars($response[0]['PersonCard_Code']) : '&nbsp;',
			'Polis_begDate' => strlen($response[0]['Polis_begDate']) > 0 ? htmlspecialchars($response[0]['Polis_begDate']) : '&nbsp;',
			'Polis_endDate' => strlen($response[0]['Polis_endDate']) > 0 ? htmlspecialchars($response[0]['Polis_endDate']) : '&nbsp;',
			'Polis_Num' => strlen($response[0]['Polis_Num']) > 0 ? htmlspecialchars($response[0]['Polis_Num']) : '&nbsp;',
			'Polis_Ser' => strlen($response[0]['Polis_Ser']) > 0 ? htmlspecialchars($response[0]['Polis_Ser']) : '&nbsp;',
			'PolisType_Name' => strlen($response[0]['PolisType_Name']) > 0 ? htmlspecialchars($response[0]['PolisType_Name']) : '&nbsp;',
			'Post_Name' => strlen($response[0]['Post_Name']) > 0 ? htmlspecialchars($response[0]['Post_Name']) : '&nbsp;',
			'Sex_Code' => strlen($response[0]['Sex_Code']) > 0 ? htmlspecialchars($response[0]['Sex_Code']) : '&nbsp;',
			'SocStatus_Code' => strlen($response[0]['SocStatus_Code']) > 0 ? htmlspecialchars($response[0]['SocStatus_Code']) : '&nbsp;',
			'UAddress_Name' => strlen($response[0]['UAddress_Name']) > 0 ? htmlspecialchars($response[0]['UAddress_Name']) : '&nbsp;',
			'Person_Snils' => strlen($response[0]['Person_Snils']) > 0 ? htmlspecialchars($response[0]['Person_Snils']) : '&nbsp;',
			'PrivilegeType_Code' => strlen($response[0]['PrivilegeType_Code']) > 0 ? htmlspecialchars($response[0]['PrivilegeType_Code']) : '&nbsp;',
			'Lpu_OGRN' => strlen($response[0]['Lpu_OGRN']) > 0 ? htmlspecialchars($response[0]['Lpu_OGRN']) : '&nbsp;',

		);

		if (allowPersonEncrypHIV($data['session']) && !empty($response[0]['PersonEncrypHIV_Encryp'])) {
			$print_data['Person_Fio'] = strlen(trim($response[0]['PersonEncrypHIV_Encryp'])) > 0 ? htmlspecialchars($response[0]['PersonEncrypHIV_Encryp']) : '&nbsp;';

			$person_fields = array('OrgJob_Name', 'OrgUnion_Name', 'Post_Name', 'DocumentType_Name', 'Document_Ser', 'Document_Num',
				'Document_begDate', 'UAddress_Name', 'KLAreaType_Code', 'KLAreaType_Name', 'SocStatus_Code', 'SocStatus_Name',
				'PrivilegeType_Name', 'PrivilegeType_Code', 'PersonPrivilege_begDate', 'Sex_id', 'Sex_Name', 'Sex_Code',
				'Person_Birthday', 'Person_BirthdayStr', 'LpuRegion_Name', 'Person_Address', 'Person_Docum', 'Person_INN', 'Person_Snils',
				'PersonCard_Code', 'Polis_begDate', 'Polis_endDate', 'Polis_Num', 'Polis_Ser'
			);

			foreach ($person_fields as $field) {
				$print_data[$field] = '';
			}
		}

		for ($i = 1; $i <= 6; $i++) {
			$print_data['PCD' . $i] = '&nbsp;';
		}
		for ($i = 1; $i <= 13; $i++) {

			$print_data['ogrn' . $i] = '&nbsp;';
		}
		if (!empty($print_data['Lpu_OGRN']) && $print_data['Lpu_OGRN'] != '&nbsp;') {
			for ($i = 1; $i <= 13; $i++) {
				if (isset($print_data['Lpu_OGRN'][$i - 1])) {
					$print_data['ogrn' . $i] = $print_data['Lpu_OGRN'][$i - 1];
				}
			}
		}

		for ($i = 1; $i <= 8; $i++) {
			$print_data['PB' . $i] = '&nbsp;';
		}
		if (!empty($print_data['Person_BirthdayStr']) && $print_data['Person_BirthdayStr'] != '&nbsp;') {
			for ($i = 1; $i <= 8; $i++) {
				if (isset($print_data['Person_BirthdayStr'][$i - 1])) {
					$print_data['PB' . $i] = $print_data['Person_BirthdayStr'][$i - 1];
				}
			}
		}

		for ($i = 1; $i <= 20; $i++) {
			$print_data['PSnils' . $i] = '&nbsp;';
		}
		if (!empty($print_data['Person_Snils']) && $print_data['Person_Snils'] != '&nbsp;') {
			for ($i = 1; $i <= 20; $i++) {
				if (isset($print_data['Person_Snils'][$i - 1])) {
					$print_data['PSnils' . $i] = $print_data['Person_Snils'][$i - 1];
				}
			}
		}

		for ($i = 1; $i <= 3; $i++) {
			$print_data['PrivC' . $i] = '&nbsp;';
		}
		if (!empty($print_data['PrivilegeType_Code']) && $print_data['PrivilegeType_Code'] != '&nbsp;') {
			for ($i = 1; $i <= 3; $i++) {
				if (isset($print_data['PrivilegeType_Code'][$i - 1])) {
					$print_data['PrivC' . $i] = $print_data['PrivilegeType_Code'][$i - 1];
				}
			}
		}


		for ($i = 1; $i <= 18; $i++) {
			$print_data['PSN' . $i] = '&nbsp;';
		}
		if ($print_data['Polis_Ser'] == '&nbsp;') {
			$print_data['Polis_Ser'] = '';
		}
		if ($print_data['Polis_Num'] == '&nbsp;') {
			$print_data['Polis_Num'] = '';
		}
		$polisSerNum = htmlspecialchars($print_data['Polis_Ser'], ENT_QUOTES, 'windows-1251') . htmlspecialchars($print_data['Polis_Num'], ENT_QUOTES, 'windows-1251');
		if (!empty($polisSerNum)) {
			for ($i = 1; $i <= 18; $i++) {
				if (isset($polisSerNum[$i - 1])) {
					$print_data['PSN' . $i] = $polisSerNum[$i - 1];
				}
			}
		}
		return $this->parser->parse($template, $print_data);


	}

	/**
	 * Сохранение талона амбулаторного пациента
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: новая форма редактирования талона амбулаторного пациента
	 */
	function saveEvnPL()
	{
		$this->inputRules['saveEvnPL'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('saveEvnPL', true);
		if ($data === false) {
			return false;
		}
		if (empty($data['isAutoCreate'])) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		} else {
			// если с формы пришло EvnPL_IsFinish=2,
			// то при сохранении ТАП талон будет закрыт
			// при отмене сохранения будет удален открытый ТАП
			$data['EvnPL_IsFinish'] = 1;
			$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
		}
		$className = get_class($this->dbmodel);
		$instance = new $className();
		$instance->applyData($data);
		// надо обслужить направление, которое было выбрано в ТАП, созданный без направления
		if (!$instance->isNewRecord && !empty($data['EvnDirection_id']) && !empty($instance->evnVizitList)) {
			$first_EvnVizitPL_id = 0;
			foreach ($instance->evnVizitList as $id => $row) {
				$first_EvnVizitPL_id = $id;
				break;
			}
			if ($first_EvnVizitPL_id && empty($instance->evnVizitList[$first_EvnVizitPL_id]['EvnDirection_id'])) {
				$instance->setEvnVizitInputData(array(
					'session' => $data['session'],
					'scenario' => $data['scenario'],
					'EvnVizitPL_id' => $first_EvnVizitPL_id,
					'EvnDirection_vid' => $data['EvnDirection_id'],
					//параметры для игнорирования проверок
					'ignore_vizit_kvs_control' => 1,
					'ignore_vizit_intersection_control' => 1,
					'ignoreMesUslugaCheck' => 1,
					'ignoreControl59536' => 1,
					'ignoreControl122430' => 1,
					'ignoreEvnDirectionProfile' => 1,
					'ignoreMorbusOnkoDrugCheck' => 1,
					'ignoreCheckEvnUslugaChange' => 1,
					'ignoreCheckB04069333' => 1,
					'ignoreCheckTNM' => 1,
					'ignoreLpuSectionProfileVolume' => 1,
					'ignoreDayProfileDuplicateVizit' => 1,
				));
			}
		}
		$response = $instance->doSave();

		// Создание случая КВИ
		if (!empty($response['EvnPL_id']) && empty($response['Error_Msg'])) {
			$params = $data;
			$params['source'] = 'EvnPL';
			$params['EvnPL_id'] = $response['EvnPL_id'];
			$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
			$this->CVIRegistry_model->saveCVIEvent($params);
		}
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении талона амбулаторного пациента')
			->ReturnData();
		return true;
	}

	/**
	 * Сохранение сопутствующего диагноза
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: новая форма редактирования диагноза
	 */
	function saveEvnDiagPL()
	{
		$data = $this->ProcessInputData('saveEvnDiagPL', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->saveEvnDiagPL($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении сопутствующего диагноза')->ReturnData();

		return true;
	}

	/**
	 * Проверка наличия приписного населения у МО, а так же прикрепелния человека к данной МО
	 */
	function checkIsAssignNasel()
	{
		$data = $this->ProcessInputData('checkIsAssignNasel', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->checkIsAssignNasel($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Проверка наличия действующей записи в объеме «Консультативный прием»
	 */
	public function checkLpuHasConsPriemVolume()
	{
		$data = $this->ProcessInputData('checkLpuHasConsPriemVolume', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->checkLpuHasConsPriemVolume($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение сведений об аборте
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования сведений об аборте
	 */
	function saveEvnPLAbort()
	{

		$data = $this->ProcessInputData('saveEvnPLAbort', true);
		if ($data === false) {
			return false;
		}

		if (isset($data['EvnPLAbort_PregSrok']) && $data['EvnPLAbort_PregSrok'] > 28) {
			echo json_return_errors('Максимальный срок беременности 28 недель');
			return false;
		}

		$response = $this->dbmodel->checkEvnPLAbortPersonSex($data['PersonEvn_id']);

		switch ($response) {
			case -3:
				echo json_return_errors('Ошибка при определении пола пациента');
				return false;
				break;

			case -2:
				echo json_return_errors('Во время проверки пола пациента возникли ошибки');
				return false;
				break;

			case -1:
				echo json_return_errors('Ошибка при выполнении запроса к БД (проверка пола пациента)');
				return false;
				break;

			case 0:
				echo json_return_errors('У выбранного пациента мужской пол. Сохранение невозможно');
				return false;
				break;

			case 1:
				break;

			default:
				echo json_return_errors('Во время проверки пола пациента возникли ошибки');
				return false;
				break;
		}

		$response = $this->dbmodel->saveEvnPLAbort($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении сведений об аборте')->ReturnData();

		return true;
	}

	/**
	 * Сохранение посещения пациентом поликлиники
	 * На выходе: JSON-строка
	 * Используется: стандартная форма редактирования посещения пациентом поликлиники
	 * @return bool
	 */
	function saveEvnVizitPL()
	{
		if (isset($_POST['FormType']) && $_POST['FormType'] == 'EvnVizitPLWow') {
			$this->load->model('EvnVizitPLWOW_model');
			$this->inputRules['saveEvnVizitPL'] = $this->EvnVizitPLWOW_model->getInputRules(swModel::SCENARIO_DO_SAVE);
			$data = $this->ProcessInputData('saveEvnVizitPL', true);
			if ($data === false) {
				return false;
			}
			if (empty($data['isAutoCreate'])) {
				$data['scenario'] = swModel::SCENARIO_DO_SAVE;
			} else {
				$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
			}
			$response = $this->EvnVizitPLWOW_model->doSave($data);
			$this->ProcessModelSave($response, false, 'Ошибка при сохранении посещения пациентом поликлиники')
				->ReturnData();
		} else {
			$this->load->model('EvnVizitPL_model');
			$this->inputRules['saveEvnVizitPL'] = $this->EvnVizitPL_model->getInputRules(swModel::SCENARIO_DO_SAVE);
			$data = $this->ProcessInputData('saveEvnVizitPL', true);
			if ($data === false) {
				return false;
			}
			if (empty($data['isAutoCreate'])) {
				$data['scenario'] = swModel::SCENARIO_DO_SAVE;
			} else {
				$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
			}

			$response = $this->EvnVizitPL_model->doSave($data);

			if (!empty($evplId = $response['EvnVizitPL_id']) && $evplId > 0 && empty($response['Error_Code']))
			{
				if (isset($data['MorbusPregnancyPresent']) && $data['MorbusPregnancyPresent'] == 2)
				{
				$this->load->model('MorbusPregnancy_model', 'preg_model');
					$data['Evn_id'] = $evplId;
				$this->preg_model->saveMorbusPregnancy($data);
			}

				$evplId = (!empty($data['EvnVizitPL_id']) ? $data['EvnVizitPL_id'] : $evplId);

				if (!empty($data['EvnVizitPL_setDate']))
				{
				$this->load->model('Polka_PersonDisp_model', 'PersonDisp_model');
					$params = array(
						'PersonDispVizit_NextFactDate' => $data['EvnVizitPL_setDate'],
						'EvnVizitPL_id' => $evplId,
						'PersonDisp_id' => !empty($data['PersonDisp_id']) ? $data['PersonDisp_id'] : null,
						'pmUser_id' => $data['pmUser_id']
					);
				$this->PersonDisp_model->savePersonDispEvnVizitPL($params);
			}

				$this->saveEvnDiagHSNDetails(
					array(
						'EvnVizitPL_id' => $evplId,
						'pmUser_id' => $data['pmUser_id']
					));
			}

			$this->ProcessModelSave($response,
									false,
									'Ошибка при сохранении посещения пациентом поликлиники')->ReturnData();
		}
		return true;
	}

	/**
	 * Получение стадии ХСН
	 */
	function getHsnStage() {
		$response = $this->dbmodel->getHsnStage();
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 * Получение функционального класса ХСН
	 */
	function getHSNFuncClass() {
		$response = $this->dbmodel->getHSNFuncClass();
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 * Сохранение детализации диагноза ХСН по пациенту в рамках события
	 */
	function saveEvnDiagHSNDetails($params)
	{
		$data = $this->ProcessInputData('saveEvnDiagHSNDetails', false);

		if ($data === false)
			return false;

		$data['saveEvnVizitPL'] = $params;
		$data['Evn_id']= $params['EvnVizitPL_id'];
		$data['pmUser_id'] = $params['pmUser_id'];

		$this->dbmodel->saveEvnDiagHSNDetails($data);
	}

	/**
	 * Получение последней детализации диагноза ХСН по пациенту
	 */
	function getLastHsnDetails()
	{
		$data = $this->ProcessInputData('getLastHsnDetails', false);

		if ($data === false)
			return false;

		$response = $this->dbmodel->getLastHsnDetails($data);

		$this->ProcessModelList($response)->ReturnData();

		return true;
	}

	function saveEvnVizitFromEMK()
	{
		$data = $this->ProcessInputData('saveEvnVizitFromEMK', false);
		if ($data === false) {
			return false;
		}

		$session = getSessionParams();
		$data['session'] = $session['session'];
		$data['Lpu_id'] = $session['Lpu_id'];
		$data['pmUser_id'] = $session['pmUser_id'];

		$this->load->model('EvnVizitPL_model');
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$data['vizit_kvs_control_check'] = 1;
		$response = $this->EvnVizitPL_model->doSave($data);
		$this->ProcessModelSave($response, false, 'Ошибка при сохранении посещения пациентом поликлиники')->ReturnData();

		return true;
	}

	/**
	 * Завершение случая лечения
	 * На выходе: JSON-строка
	 * Используется: стандартная форма редактирования посещения пациентом поликлиники
	 * @return bool
	 */
	function saveEvnPLFinishForm()
	{
		$data = $this->ProcessInputData('saveEvnPLFinishForm', false);
		if ($data === false) {
			return false;
		}

		$session = getSessionParams();
		$data['session'] = $session['session'];
		$data['Lpu_id'] = $session['Lpu_id'];
		$data['pmUser_id'] = $session['pmUser_id'];

		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$response = $this->dbmodel->doSave($data);

		if (!empty($response['EvnPL_id']) && empty($response['Error_Msg'])) {
			$params = $data;
			$params['source'] = 'EvnPL';
			$params['EvnPL_id'] = $response['EvnPL_id'];
			$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
			$this->CVIRegistry_model->saveCVIEvent($params);
		}

		$this->ProcessModelSave($response, false, 'Ошибка при сохранении завершения случая лечения')->ReturnData();

		return true;
	}

	/**
	 * Загрузка формы завершения случая лечения
	 */
	function loadEvnPLFinishForm()
	{
		$data = $this->ProcessInputData('loadEvnPLFinishForm', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEvnPLFinishForm($data);
		$this->ProcessModelList($response, false, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка диагнозов ТАП для ЭМК
	 */
	function loadEvnPLDiagPanel()
	{
		$data = $this->ProcessInputData('loadEvnPLDiagPanel', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEvnPLDiagPanel($data);
		$this->ProcessModelList($response, false, true)->ReturnData();

		return true;
	}

	/**
	 * Добавление нового посещения из ЭМК
	 */
	function addEvnVizitPL()
	{
		$data = $this->ProcessInputData('addEvnVizitPL', true);
		if ($data === false) {
			return false;
		}

		if(getRegionNick() == 'vologda'){
			$data['closeAPL'] = 0;
			$arrVizit = $this->dbmodel->checkEvnVizitsPL($data);
			
			if(isset($arrVizit[0])) {
				foreach ($arrVizit as $id => $row) {
					$LpuSectionProfile_Code = $this->dbmodel->getProfileCode($data['LpuSectionProfile_id']);
					$options = $this->dbmodel->getGlobalOptions();
					if (
						!in_array($LpuSectionProfile_Code, $options['globals']['exceptionprofiles'])
						&& ($LpuSectionProfile_Code != $arrVizit[$id]['LpuSectionProfile_Code'])
						&& $arrVizit[$id]['MedStaffFact_id'] != $data['session']['CurARM']['MedStaffFact_id']
					) {
						throw new Exception('Добавление посещения невозможно, т.к. в рамках текущего ТАП специалистом другого профиля уже добавлено посещение.');
						return false;
					}
				}
			}
		}
		
		$this->load->model('EvnVizitPL_model');
		$response = $this->EvnVizitPL_model->addEvnVizitPL($data);
		$this->ProcessModelSave($response, false, 'Ошибка при сохранении посещения пациентом поликлиники')->ReturnData();

		return true;
	}

	/**
	 * Получить дату окончания случая лечения
	 */
	function getLastVizitDT()
	{
		$data = $this->ProcessInputData('getLastVizitDT');
		//if($data === false || (empty($data['EvnSection_id']) && empty($data['EvnVizitPL_id']) && empty($data['EvnDiagPLStom_id']))) { return false; }

		if ($data === false) {
			return false;
		}
		if (empty($data['EvnSection_id']) && empty($data['EvnVizitPL_id']) && empty($data['EvnDiagPLStom_id'])) {
			$this->ReturnData(array('success' => false));
			return false;
		}

		$response = $this->dbmodel->getLastVizitDT($data);
		$this->ReturnData(array('endTreatDate' => $response));

		return true;
	}

	/**
	 * Получение данных талона для просмотра
	 * Входящие данные: $_POST['EvnPL_id']
	 * На выходе: JSON-строка с HTML в элементе 'html'
	 * Используется: форма просмотра и печати посещения поликлиники
	 */
	function loadEvnPLViewForm()
	{
		$data = $this->ProcessInputData('loadEvnPLViewForm', true);
		if ($data === false) {
			return false;
		}

		/*$str = $this->printEvnPLPerm($data['EvnPL_id'], TRUE);
		$this->ReturnData(array("success"=>true, "html" => toUTF($str)));*/
		$this->ReturnData(array("success" => true, "html" => "/?c=EvnPL&m=printEvnPL&EvnPL_id=" . $data['EvnPL_id']));
		return true;
	}

	/**
	 * Сохранение талона амбулаторного пациента и посещения пациентом поликлиники
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: дополнительная форма редактирования талона амбулаторного пациента
	 */
	function saveEmkEvnPL()
	{
		$this->load->model('EvnVizitPL_model');
		$this->inputRules['saveEmkEvnPL'] = array_merge(array(
			array(
				'field' => 'action',
				'label' => 'Действие',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_vid',
				'label' => 'Направление посещения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedicalCareKind_vid',
				'label' => 'Вид мед. помощи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'VizitActiveType_id',
				'label' => 'Вид активного посещения',
				'rules' => '',
				'type' => 'id'
			)
		),
			$this->dbmodel->getInputRules(EvnPL_model::SCENARIO_DO_SAVE),
			$this->EvnVizitPL_model->getInputRules(EvnPL_model::SCENARIO_DO_SAVE)
		);

		$regNick = getRegionNick();

		if ($regNick == 'buryatiya' || $regNick == 'kz') {
			// делаем поле не обязательным
			$this->inputRules['saveEmkEvnPL']['VizitType_id']['rules'] = 'trim';
		}

		if (getRegionNick() == 'kz') {
			// делаем поле не обязательным
			$this->inputRules['saveEmkEvnPL']['PayType_id']['rules'] = 'trim';
		}

		$data = $this->ProcessInputData('saveEmkEvnPL', true, false);
		if ($data === false) {
			return false;
		}

		if ($regNick == 'buryatiya' && empty($data['isAutoCreate']) && empty($data['VizitType_id'])) {
			$this->ReturnError('Поле "Цель посещения" обязательно для заполнения.');
			return false;
		}

		if ($regNick == 'penza' && empty($data['VizitType_id']) && $data['action'] == 'closeEvnPL') {
			$this->ReturnError('Поле "Цель посещения" обязательно для заполнения.');
			return false;
		}

		if (!empty($data['isAutoCreate']) && !empty($data['EvnPL_IsFinish']) && $data['EvnPL_IsFinish'] == 2) {
			$data['EvnPL_IsFinish'] = 1;
		}

		/*
		var_export($this->inputRules['saveEmkEvnPL']);
		if (isset($data['session'])) unset($data['session']);
		var_export($data);  exit;
		*/
		$className = get_class($this->dbmodel);
		/**
		 * @var EvnPL_model $instance
		 */
		$instance = new $className();
		switch (true) {
			case ($data['action'] == 'addEvnPL'):
				// Создание ТАП и посещения
				if (!empty($data['isAutoCreate'])) {
					$data['EvnPL_IsFinish'] = 1;
					$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
				} else {
					$data['scenario'] = swModel::SCENARIO_DO_SAVE;
				}

				$data['EvnPL_setDate'] = $data['EvnVizitPL_setDate'];
				if (empty($data['MedStaffFact_did'])) {
					$data['MedStaffFact_did'] = $data['MedStaffFact_id']; // в хранимке нет MedStaffFact_id
				}

				$instance->applyData($data);
				$data['EvnDirection_vid'] = $data['EvnDirection_id'];
				$instance->setEvnVizitInputData($data);
				$response = $instance->doSave($data);
				break;
			case (in_array($data['action'], array('addEvnVizitPL', 'editEvnVizitPL'))):
				//@todo добавить свойство addOnly
				if (isset($data['Lpu_id']) && $data['action'] == 'editEvnVizitPL') unset($data['Lpu_id']);
				if (!empty($data['isAutoCreate'])) {
					$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
				} else {
					$data['scenario'] = swModel::SCENARIO_DO_SAVE;
				}
				$data['ignoreCheckNum'] = 1;//Т.к. в форме скрыто поле ввода номера ТАП
				$this->dbmodel->applyData($data);
				$this->dbmodel->setEvnVizitInputData($data);
				$response = $this->dbmodel->doSave();
				break;
			case (in_array($data['action'], array('editEvnPL'))):
				if (isset($data['Lpu_id'])) unset($data['Lpu_id']);
				$data['scenario'] = EvnPL_model::SCENARIO_DO_SAVE;
				$instance->applyData($data);
				// надо обслужить направление, которое было выбрано в ТАП, созданный без направления, по аналогии с self::saveEvnPL
				if (!$instance->isNewRecord && !empty($data['EvnDirection_id']) && !empty($instance->evnVizitList)) {
					$first_EvnVizitPL_id = $data['EvnVizitPL_id'];// - это должно быть первое посещение
					/*или так
					foreach ($instance->evnVizitList as $id => $row) {
						$first_EvnVizitPL_id = $id;
						break;
					}
					*/
					if ($first_EvnVizitPL_id && empty($instance->evnVizitList[$first_EvnVizitPL_id]['EvnDirection_id'])) {
						$instance->setEvnVizitInputData(array(
							'session' => $data['session'],
							'scenario' => $data['scenario'],
							'EvnVizitPL_id' => $first_EvnVizitPL_id,
							'EvnDirection_vid' => $data['EvnDirection_id'],
							//параметры для игнорирования проверок
							'ignore_vizit_kvs_control' => 1,
							'ignore_vizit_intersection_control' => 1,
							'ignoreMesUslugaCheck' => 1,
							'ignoreControl59536' => 1,
							'ignoreControl122430' => 1,
							'ignoreEvnDirectionProfile' => 1,
							'ignoreMorbusOnkoDrugCheck' => 1,
							'ignoreLpuSectionProfileVolume' => 1,
						));
					}
				}
				$response = $instance->doSave();
				break;
			case (in_array($data['action'], array('closeEvnPL'))):
				if (isset($data['Lpu_id'])) unset($data['Lpu_id']);
				$data['scenario'] = EvnPL_model::SCENARIO_DO_SAVE;
				$response = $instance->doSave($data);
				break;
			default:
				$response = array('Error_Msg' => 'Неправильные параметры');
		}

		if (!empty($response['EvnVizitPL_id']) &&
			($evplId = $response['EvnVizitPL_id']) &&
			$evplId > 0 &&
			empty($response['Error_Code']))
		{
			$evplId =
				(!empty($data['EvnVizitPL_id']) ?
					$data['EvnVizitPL_id'] :
					$evplId);

			if (!empty($data['EvnVizitPL_setDate']))
			{
			$this->load->model('Polka_PersonDisp_model', 'PersonDisp_model');
				$params = array(
					'PersonDispVizit_NextFactDate' => $data['EvnVizitPL_setDate'],
					'EvnVizitPL_id' => $evplId,
					'PersonDisp_id' => !empty($data['PersonDisp_id']) ?
											$data['PersonDisp_id'] :
											null,
					'pmUser_id' => $data['pmUser_id']
				);

			$this->PersonDisp_model->savePersonDispEvnVizitPL($params);
		}

		// Создание случая КВИ
		if (!empty($response['EvnPL_id']) && empty($response['Error_Msg'])) {
			$params = $data;
			$params['source'] = 'EvnPL';
			$params['EvnPL_id'] = $response['EvnPL_id'];
			$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
			$this->CVIRegistry_model->saveCVIEvent($params);
		}

			$this->saveEvnDiagHSNDetails(
				array(
					'EvnVizitPL_id' => $evplId,
					'pmUser_id' => $data['pmUser_id']
				));
		}

		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Функция отмены закрытия случая
	 * Входящие данные: POST['EvnPL_id']
	 *  На выходе: JSON-строка
	 *  Используется: ЭМК
	 */
	function openEvnPL()
	{
		$data = $this->ProcessInputData('openEvnPL', true);
		if ($data === false) {
			return false;
		}
		$data['EvnPL_IsFinish'] = '1';
		$data['scenario'] = EvnPL_model::SCENARIO_DO_SAVE;
		if (!empty($data['options'])) {
			$options = json_decode($data['options'], true);
			$data = array_merge($data, $options);
		}
		if (isset($data['Server_id'])) unset($data['Server_id']);
		if (isset($data['Lpu_id'])) unset($data['Lpu_id']);
		$response = $this->dbmodel->doSave($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Тест синхронизации
	 */
	function testEvnPLSync()
	{
		if (!isSuperAdmin()) {
			return false;
		}

		if (method_exists($this->dbmodel, 'testEvnPLSync')) {
			$response = $this->dbmodel->testEvnPLSync();
			$this->ProcessModelSave($response, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Метод проверяет в ТАП может ли текущий пользователь удалить случай
	 * @return bool
	 */
	function checkEvnPlOnDelete()
	{
		$data = $this->ProcessInputData('checkEvnPlOnDelete', true);
		if ($data === false) {
			return false;
		}

		try{
			$response = $this->dbmodel->checkEvnPlOnDelete($data);
			$result['success'] = true;
		} catch (Exception $e) {
			$result['success'] = false;
			$response = false;
		}

		$result['canDelete'] = $response;

		$this->ProcessModelSave($result, true)->ReturnData($result);
		return true;
	}

	/**
	 * Метод проверки посещений
	 * @return bool
	 */
	function checkEvnVizitsPL()
	{
		$data = $this->ProcessInputData('checkEvnVizitsPL', true);
		if ($data === false) {
			return false;
		}

		$arrVizit = $this->dbmodel->checkEvnVizitsPL($data);
	
		if(isset($arrVizit[0])) {
			foreach ($arrVizit as $id => $row) {
				$errortext = [
					'Добавление посещения невозможно, т.к. в рамках текущего ТАП специалистом другого профиля уже добавлено посещение.',
					'Профиль отделения текущего посещения должен соответствовать профилю отделения других посещений в этом ТАП.',
					'Закрытие случая АПЛ невозможно, т.к. в рамках одного ТАП для всех посещений должен быть указан один профиль отделения.'
				];
	
				if ( $data['closeAPL'] == '2' && $arrVizit[0]['LpuSectionProfile_Code'] != $arrVizit[$id]['LpuSectionProfile_Code']) { 
					throw new Exception($errortext[$data['closeAPL']]);
					return false;
				} 
				if ( $data['closeAPL'] !== '2' ) {
					$LpuSectionProfile_Code = $this->dbmodel->getProfileCode($data['LpuSectionProfile_id']);
					$options = $this->dbmodel->getGlobalOptions();
					if ( 
						!in_array($LpuSectionProfile_Code, $options['globals']['exceptionprofiles']) 
						&& ($LpuSectionProfile_Code != $arrVizit[$id]['LpuSectionProfile_Code'])
					) {//Если профиль не в списке допустимых для повторения
						throw new Exception(
							$errortext[ ($arrVizit[$id]['MedStaffFact_id'] == $data['session']['CurARM']['MedStaffFact_id']) ? $data['closeAPL'] : 0 ]
						);
						return false;
					}
				}
			}
		}
		return true;
	}
}