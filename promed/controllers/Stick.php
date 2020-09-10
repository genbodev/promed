<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Stick - контроллер для работы с ЛВН
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2010-2011 Swan Ltd.
* @author		Stas Bykov aka Savage (savage@swan.perm.ru)
* @version		09.09.2010
*
* @property Stick_model $dbmodel
*/
class Stick extends swController {
	public $inputRules = array(

		'getEvnPSFromEvnLink' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			)
		),

		'getBegEndDatesInStac' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'required',
				'type' => 'id'
			)
		),

		'getEvnSectionList' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'required',
				'type' => 'id'
			)
		),

		'getEvnSectionDatesForEvnStick' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnStickPanel' => array(
			array(
				'field' => 'EvnStick_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => '',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => '',
				'rules' => 'required',
				'type' => 'id'
			),
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
		),
		'deleteEvnStick' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_mid',
				'label' => 'Идентификатор учетного документа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreStickFromFSS',
				'label' => 'Игнорировать проверку',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'ignoreStickHasProlongation',
				'label' => 'Игнорировать проверку',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'ignoreStickHasPrevious',
				'label' => 'Игнорировать проверку',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'StickCauseDel_id',
				'label' => 'Причина прекращения действия ЭЛН',
				'rules' => '',
				'type' => 'id'
			)
		),
		'undoDeleteEvnStick' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'checkELN' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'checkSanatorium' => array(),
		'CheckEvnStickDie' => array(
			array(
				'field' => 'EvnStick_pid',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickLeaveType_id',
				'label' => 'Идентификатор исхода',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_disDate',
				'label' => 'Дата исхода',
				'rules' => 'trim',
				'type' => 'date'
			)
		),
		'deleteEvnStickStudent' => array(
			array(
				'field' => 'EvnStickStudent_id',
				'label' => 'Идентификатор справки учащегося',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickStudent_mid',
				'label' => 'Идентификатор учетного документа',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteEvnStickWorkRelease' => array(
			array(
				'field' => 'EvnStickWorkRelease_id',
				'label' => 'Идентификатор освобождения от работы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnStickChange' => array(
			array(
				'field' => 'EvnStick_mid',
				'label' => 'Идентификатор родительского события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			// Нужно отображать только существующие ЛВН
			array(
				'default' => 0, //0 - новые/существующие, 1 - существующие
				'field' => 'StickExisting',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			)
		),
		'getEvnStickOriginalsList' => array(
			array(
				'field' => 'EvnStick_mid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_oid',
				'label' => 'Идентификатор оригинала',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getEvnStickMainList' => array(
			array(
				'field' => 'EvnStick_mid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnStickCarePersonGrid' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickBase_IsFSS',
				'label' => 'ЛВН из ФСС',
				'rules' => '',
				'type' => 'checkbox'
			)
		),
		'loadEvnStickDopEditForm' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_pid',
				'default' => 0,
				'label' => 'Идентификатор учетного документа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LoadForPrintStick',
				'default' => 0,
				'label' => 'Флаг загрузки различных периодик врачей / пациентов',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'delDocsView',
				'label' => 'Просмотр удаленных документов',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnStickEditForm' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_pid',
				'default' => 0,
				'label' => 'Идентификатор учетного документа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LoadForPrintStick',
				'default' => 0,
				'label' => 'Флаг загрузки различных периодик врачей / пациентов',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'delDocsView',
				'label' => 'Просмотр удаленных документов',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnStickGrid' => array(
			array(
				'field' => 'EvnStick_pid',
				'label' => 'Идентификатор учетного документа',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'checkWorkReleaseMedStaffFact' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор учетного документа',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		// Загрузка списка выбора первичного ЛВН для ЛВН-продолжения
		'loadEvnStickList' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
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
				'field' => 'StickWorkType_id',
				'label' => 'Тип занятости',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickOriginal_prid',
				'label' => 'Идентификатор предыдущего ЛВН из оригинала ЛВН',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getWorkReleaseSumPeriod' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => '',
				'type' => 'id',
				'default' => null
			),
			array(
				'field' => 'getEvnPS24',
				'label' => 'флаг получения периодово кругл. стационара отдельно от остальных',
				'rules' => '',
				'type' => 'boolean'
			)
		),
		'loadEvnStickSearchGrid' => array(
			array(
				'field' => 'EvnStickBase_begDate',
				'label' => 'Начало периода освобождения',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'default' => 0,
				'field' => 'archiveStart',
				'label' => 'Номер стартовой архивной записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnStickBase_endDate',
				'label' => 'Окончание периода освобождения',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnStickBase_Num',
				'label' => 'Номер ЛВН',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStickBase_Ser',
				'label' => 'Серия ЛВН',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Person_Firname',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'StickType_id',
				'label' => 'Тип листа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SearchType_id',
				'label' => 'Режим поиска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_IsClosed',
				'label' => 'Флаг закрытия',
				'rules' => '',
				'type' => 'id'
			),
            array(
                'field' => 'CurLpuSection_id',
                'label' => 'CurLpuSection_id',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'CurLpuUnit_id',
                'label' => 'CurLpuUnit_id',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'CurLpuBuilding_id',
                'label' => 'CurLpuBuilding_id',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'MedPersonal1_id',
                'label' => 'Врач 1',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'MedPersonal2_id',
                'label' => 'Врач 2',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'MedPersonal3_id',
                'label' => 'Врач 3',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'EvnStick_IsNeedSign',
                'label' => 'Нуждается в ЭП',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'RegistryESType_id',
                'label' => 'Не включен в реестр с типом',
                'rules' => '',
                'type' => 'id'
            ),
			array('field' => 'LvnType', 'label' => 'Вид ЛВН', 'rules' => '', 'type' => 'int'),
			// Параметры страничного вывода
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			),//Сигнальная информация
                    array(
                'field' => 'SignalInfo',
                'label' => 'Сигнаяльная информация',
                'rules' => '',
                'type' => 'id'
            )
		),
		'loadEvnStickStudentEditForm' => array(
			array(
				'field' => 'EvnStickStudent_id',
				'label' => 'Идентификатор справки учащегося',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnStickWorkReleaseGrid' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickDop_pid',
				'label' => 'Идентификатор основного ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LoadSummPeriod',
				'label' => 'Флаг загрузки общего периода',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'StickWorkType_id',
				'label' => 'Тип занятости',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreRegAndPaid',
				'label' => 'Флаг загрузки без учёта признаков "в реестре" и "оплачено"',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadEvnStickStudentWorkReleaseGrid' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'updateEvnStickWorkReleaseGrid' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_pid',
				'label' => 'Идентификатор основного ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LoadSummPeriod',
				'label' => 'Флаг загрузки общего периода',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreRegAndPaid',
				'label' => 'Флаг загрузки без учёта признаков "в реестре" и "оплачено"',
				'rules' => '',
				'type' => 'int'
			)
		),
		'getEvnStickPridValues' => array(
			array(
				'field' => 'EvnStick_prid',
				'label' => 'Идентификатор предыдущего ЛВН',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnStickOriginInfo' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор дубликата ЛВН',
				'rules' => 'required',
				'type' => 'id'
			)
		),

		'getEvnStickInfo' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnStickProdValues' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор первичного ЛВН',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnStick' => array(
			array(
				'field' => 'EvnStick_BirthDate',
				'label' => 'Предполагаемая дата родов',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_IsOriginal',
				'label' => 'Оригинал',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_disDate',
				'label' => 'Дата закрытия ЛВН',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickFSSData_id',
				'label' => 'Идентификатор запроса в ФСС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickBase_IsFSS',
				'label' => 'ЛВН из ФСС',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'EvnStick_irrDate',
				'label' => 'Дата нарушения режима',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_StickDT',
				'label' => 'Дата изменения причины нетрудоспособности',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_IsDisability',
				'label' => 'Установлена группа инвалидности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InvalidGroupType_id',
				'label' => 'Установлена/изменена группа инвалидности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_IsRegPregnancy',
				'label' => 'Поставлена на учет в ранние сроки беременности (до 12 недель)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_mid',
				'label' => 'Идентификатор учетного документа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_mseDate',
				'label' => 'Дата направления в бюро МСЭ',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_mseExamDate',
				'label' => 'Дата освидетельствования в бюро МСЭ',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_mseRegDate',
				'label' => 'Дата регистрации документов в бюро МСЭ',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_Num',
				'label' => 'Номер ЛВН',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryESStorage_id',
				'label' => 'Номер ЛВН в хранилище',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_nid',
				'label' => 'Идентификатор следующего ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickDop_pid',
				'label' => 'Идентификатор основного ЛВН',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnStick_prid',
				'label' => 'Идентификатор предыдущего ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_Ser',
				'label' => 'Серия ЛВН',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_setDate',
				'label' => 'Дата выдачи',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_sstBegDate',
				'label' => 'Дата начала СКЛ',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_sstEndDate',
				'label' => 'Дата окончания СКЛ',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_sstNum',
				'label' => 'Номер путевки',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_stacBegDate',
				'label' => 'Дата начала лечения в стационаре',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_stacEndDate',
				'label' => 'Дата окончания лечения в стационаре',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'evnStickCarePersonData',
				'label' => 'Список пациентов, нуждающихся в уходе',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStickBase_consentDT',
				'label' => 'Дата выдачи',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'evnStickWorkReleaseData',
				'label' => 'Список освобождений от работы',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'link',
				'label' => 'Признак необходимости добавить связку ЛВН с учетным документом',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'Направлен в другое ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы врача, закрывшего ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач, закрывший ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_did',
				'label' => 'Санаторий',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_OrgNick',
				'label' => 'Наименование организации для печати',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека, которому выдан ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека, которому выдан ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Post_Name',
				'label' => 'Должность',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'StickCause_did',
				'label' => 'Изм. причина нетрудоспособности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickCause_id',
				'label' => 'Причина нетрудоспособности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickCauseDopType_id',
				'label' => 'Доп. причина нетрудоспособности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickIrregularity_id',
				'label' => 'Нарушение режима',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickLeaveType_id',
				'label' => 'Исход ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickOrder_id',
				'label' => 'Порядок выдачи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickWorkType_id',
				'label' => 'Тип занятости',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickParentClass',
				'label' => 'Тип учётного документа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_oid',
				'label' => 'Идентификатор оригинала',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_adoptDate',
				'label' => 'Дата усыновления/удочерения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_regBegDate',
				'label' => 'Дата начала перевода на другую работу',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_regEndDate',
				'label' => 'Дата окончания перевода на другую работу',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'StickRegime_id',
				'label' => 'Идентификатор режима',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreStickOrderCheck',
				'label' => 'Игнорирование проверки первичного ЛВН',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'ignoreQuestionPrevInFSS',
				'label' => 'Пропуск вопроса при смене первичного ЛВН(ЛВН первичный принят в ФСС)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreQuestionChangePrev',
				'label' => 'Пропуск вопроса при смене первичного ЛВН',
				'rules' => '',
				'type' => 'int'
			),



			array(
				'field' => 'Signatures_id',
				'label' => 'Подпись исхода',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Signatures_iid',
				'label' => 'Подпись режима',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'doUpdateJobInfo',
				'label' => 'Флаг согласия обновления данных место работы',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreCheckChangeJobInfo',
				'label' => 'флаг игнорирования проверки изменения места работы',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreStickLeaveTypeCheck',
				'label' => 'Флаг игнорирования проверки совпадения исхода с ЛВН по совместительству',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreCheckFieldStickOrder',
				'label' => 'флаг игнорирования проверки совпадения поля "Порядок выдачи" с ЛВН по совместительству/основному',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreCheckFieldStickCause',
				'label' => 'флаг игнорирования проверки совпадения поля "Причина нетрудоспособности" с ЛВН по совместительству/основному',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreCheckSummWorkRelease',
				'label' => 'флаг игнорирования проверки продолжительности ЛВН по уходу',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'isHasDvijeniaInStac24',
				'label' => 'флаг наличия движений в круглосуточном стационаре',
				'rules' => '',
				'type' => 'boolean'
			)
		),
		'saveEvnStickDop' => array(
			array(
				'field' => 'EvnStick_Num',
				'label' => 'Номер ЛВН',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryESStorage_id',
				'label' => 'Номер ЛВН в хранилище',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_Ser',
				'label' => 'Серия ЛВН',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_setDate',
				'label' => 'Дата выдачи',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickFSSData_id',
				'label' => 'Идентификатор запроса в ФСС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickBase_IsFSS',
				'label' => 'ЛВН из ФСС',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'EvnStick_prid',
				'label' => 'Идентификатор предыдущего ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_mid',
				'label' => 'Идентификатор учетного документа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'StickCause_did',
				'label' => 'Код изменения нетрудоспособности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_StickDT',
				'label' => 'Дата изменения причины нетрудоспособности',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStickDop_pid',
				'label' => 'Идентификатор основного ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_OrgNick',
				'label' => 'Наименование организации для печати',
				'rules' => '',
				'type' => 'string'
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
			),
			array(
				'field' => 'Post_Name',
				'label' => 'Должность',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'StickOrder_id',
				'label' => 'Порядок выдачи',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'StickWorkType_id',
				'label' => 'Тип занятости',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'StickParentClass',
				'label' => 'Тип учётного документа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_oid',
				'label' => 'Идентификатор оригинала',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_IsOriginal',
				'label' => 'Оригинал',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickBase_consentDT',
				'label' => 'Дата выдачи',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'evnStickWorkReleaseData',
				'label' => 'Список освобождений от работы',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'StickCause_id',
				'label' => 'Причина нетрудоспособности',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_disDate',
				'label' => 'Дата закрытия ЛВН',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Signatures_id',
				'label' => 'Подпись исхода',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Signatures_iid',
				'label' => 'Подпись режима',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreCheckEvnStickOrg',
				'label' => 'Игнорирование проверки места работы при выписке ЛВН по совместительству',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'link',
				'label' => 'Признак необходимости добавить связку ЛВН с учетным документом',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы врача, закрывшего ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач, закрывший ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickLeaveType_id',
				'label' => 'Исход ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickOrder_id',
				'label' => 'Порядок выдачи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickCause_id',
				'label' => 'Причина нетрудоспособности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreStickLeaveTypeCheck',
				'label' => 'Флаг игнорирования проверки совпадения исхода с первичным ЛВН',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreQuestionPrevInFSS',
				'label' => 'Пропуск вопроса при смене первичного ЛВН(ЛВН первичный принят в ФСС)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreQuestionChangePrev',
				'label' => 'Пропуск вопроса при смене первичного ЛВН',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreCheckFieldStickOrder',
				'label' => 'флаг игнорирования проверки совпадения поля "Порядок выдачи" с ЛВН по совместительству/основному',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreCheckFieldStickCause',
				'label' => 'флаг игнорирования проверки совпадения поля "Причина нетрудоспособности" с ЛВН по совместительству/основному',
				'rules' => '',
				'type' => 'int'
			)

		),
		'saveEvnStickStudent' => array(
			array(
				'field' => 'EvnStick_ContactDescr',
				'label' => 'Описание контакта',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор справки учащегося',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_IsContact',
				'label' => 'Признак наличия контакта с инфекционными больными',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_Num',
				'label' => 'Номер справки учащегося',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_setDate',
				'label' => 'Дата выдачи',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Место работы врача',
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
				'field' => 'Org_id',
				'label' => 'Организация',
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
			),
			array(
				'field' => 'StickCause_id',
				'label' => 'Причина нетрудоспособности',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'StickRecipient_id',
				'label' => 'Получатель справки',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickStudent_begDT',
				'label' => 'Дата начала освобождения от физкультуры',
				'label' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStickStudent_Days',
				'label' => 'Длительность освобождения',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Единицы измерения длительности освобождения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'doUpdateJobInfo',
				'label' => 'Признак того, что место учебы отсутствует в форме Человек',
				'rules' => 'trim',
				'type' => 'int'
			)
		),
		'selectEvnStickType' => array(
			array(
				'field' => 'evnStickType',
				'label' => 'Тип ЛВН',
				'rules' => 'required',
				'type' => 'int'
			)
		),



		'loadEvnStickViewForm' => array(
			array(
				'field' => 'evnStickType',
				'label' => 'Тип ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Stick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnStickWorkRelease' => array(
			array(
				'field' => 'EvnStickBase_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickWorkRelease_begDate',
				'label' => 'С какого числа',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStickWorkRelease_endDate',
				'label' => 'По какое число',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStickWorkRelease_id',
				'label' => 'Идентификатор освобождения от работы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение (Врач 1)',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач 1',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal2_id',
				'label' => 'Врач 2',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal3_id',
				'label' => 'Врач 3',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'MedStaffFact_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact2_id',
				'label' => 'MedStaffFact2_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact3_id',
				'label' => 'MedStaffFact3_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Override30Day',
				'label' => 'Позволить выписку больше чем на 30 дней',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStickWorkRelease_IsPredVK',
				'label' => 'Флаг председатель ВК',
				'default' => 0,
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStickWorkRelease_IsDraft',
				'label' => 'Флаг черновика',
				'default' => 0,
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Org_id',
				'label' => 'МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Post_id',
				'label' => 'Должность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Signatures_mid',
				'label' => 'Подпись врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Signatures_wid',
				'label' => 'Подпись ВК',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVK_id',
				'label' => 'Протокол ВК',
				'rules' => '',
				'type' => 'id',
				'default' => null
			),
			array(
				'field' => 'EvnStickWorkRelease_IsSpecLpu',
				'label' => 'Специализированное МО',
				'rules' => '',
				'type' => 'id',
				'default' => null
			)
		),
		'getEvnStickSetdate' => array(
			array(
				'field' => 'EvnStick_mid',
				'label' => 'Идентификатор учетного документа',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnStickStudent' => array(
			array(
				'field' => 'EvnStickStudent_id',
				'label' => 'Идентификатор справки учащегося',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'preview',
				'label' => 'preview',
				'rules' => '',
				'type' => 'int'
			)
		),
		'checkLastEvnStickInStacData' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор учетного документа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_setDate',
				'label' => 'Дата ЛВН',
				'rules' => 'required',
				'type' => 'date'
			)
		),
		'searchEvnStick' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
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
				'field' => 'Evn_id',
				'label' => 'Идентификатор ТАП/КВС',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getEvnStickWorkReleaseCalculation' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'StickCause_id',
				'label' => 'Идентификатор причины нетрудоспособности',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadClosedEvnStickGrid' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnStickPids' => array(
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 50,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			),
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
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_pidType_id',
				'label' => 'Тип документа',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnStick_pidNum',
				'label' => 'Номер',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_pidIsFinish',
				'label' => 'Случай закончен',
				'rules' => '',
				'type' => 'string'
			),
            array(
                'field' => 'EvnStick_pidDate',
                'label' => 'Период дат начала',
                'rules' => '',
                'type'  => 'daterange'
            ),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Polis_Ser',
				'label' => 'Серия полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Номер полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Code',
				'label' => 'Ед. номер',
				'rules' => '',
				'type' => 'string'
			),
            array(
                'field' => 'CurMedService_id',
                'label' => '',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'CurLpuSection_id',
                'label' => '',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'CurLpuBuilding_id',
                'label' => '',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'CurLpuUnit_id',
                'label' => '',
                'rules' => '',
                'type' => 'string'
            ),
			array('field' => 'LvnType', 'label' => 'Вид ЛВН', 'rules' => '', 'type' => 'int')
		),
		'loadEvnStickForARM' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Evn_id',
				'rules' => '',
				'type' => 'int'
			)
		),
        'getMedServiceParent' => array(
            array(
                'field' => 'MedService_id',
                'label' => 'Идентификатор службы',
                'rules' => '',
                'type' => 'int'
            )
        ),
		'WorkReleaseMedStaffFactCheck' => array(
			array(
				'field' => 'EvnStickBase_id',
				'label' => 'EvnStickBase_id',
				'rules' => '',
				'type' => 'int'
			)
		),
		'getWorkReleaseSslHash' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Evn_id',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'SignObject',
				'label' => 'SignObject',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SignedToken',
				'label' => 'SignedToken',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_Num',
				'label' => 'Номер ЛВН',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'needHash',
				'label' => 'Признак необходимости подсчёта хэша',
				'rules' => '',
				'type' => 'int'
			)
		),
		'signWorkRelease' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Evn_id',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'SignObject',
				'label' => 'SignObject',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'xml',
				'label' => 'Запрос',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'signType',
				'label' => 'Тип подписи',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SignedData',
				'label' => 'SignedData',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Hash',
				'label' => 'Hash',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'SignedToken',
				'label' => 'SignedToken',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'updateInRegistryESData',
				'label' => 'Флаг обновления данных в RegistryESData',
				'rules' => '',
				'type' => 'int'
			)
		),
		'getEvnStickSignStatus' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'EvnStick_id',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'SignObject',
				'label' => 'SignObject',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'setSignStatus' => array(
			array(
				'field' => 'Signatures_id',
				'label' => 'Signatures_id',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'SignObject',
				'label' => 'SignObject',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SignaturesStatus_id',
				'label' => 'SignaturesStatus_id',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'verifyEvnStickSign' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Evn_id',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'SignObject',
				'label' => 'SignObject',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_Num',
				'label' => 'EvnStick_Num',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'needVerifyOpenSSL',
				'label' => 'Признак необходимости верификации с помощью OpenSSL',
				'rules' => '',
				'type' => 'int'
			)
		),
		'checkEvnStickNumDouble' => array(
			array(
				'field' => 'EvnStickNum',
				'label' => 'Номер ЛВН',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_id',
				'label' => 'Номер ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
		),
		'loadStickVersionList' => array(
			array(
				'field' => 'Signatures_id',
				'label' => 'Signatures_id',
				'rules' => 'required',
				'type' => 'int'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('Stick_model', 'dbmodel');
	}

	/**
	*  Функция проверки наличия ЛВН закрытого в предыдущий день в стационаре
	*  На выходе: JSON-строка
	*  Используется: форма ЛВН
	*/
	function checkLastEvnStickInStacData()
	{
		$data = $this->ProcessInputData('checkLastEvnStickInStacData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkLastEvnStickInStacData($data);
		$this->ProcessModelSave($response, true, 'Ошибка запроса на получение данных о последнем ЛВН в стационаре')->ReturnData();

		return true;
	}

	/**
	*  Функция получения даты для нового ЛВН 
	*  На выходе: JSON-строка
	*  Используется: форма ЭМК (swPersonEmkWindow.js)
	*/
	function getEvnStickSetdate() {
		$data = $this->ProcessInputData('getEvnStickSetdate', true);
		if ($data) {
			$response = $this->dbmodel->getEvnStickSetdate($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	*  Удаление ЛВН
	*  Входящие данные: $_POST['EvnStick_id'], $_POST['EvnStick_mid']
	*  На выходе: JSON-строка
	*  Используется: поиск ЛВН
	*/
	function deleteEvnStick() {
		$data = $this->ProcessInputData('deleteEvnStick', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->deleteEvnStick($data);
		$this->ProcessModelSave($response, true, 'При удалении ЛВН возникли ошибки')->ReturnData();
		
		return true;
	}

	/**
	*  Отмена удаления ЛВН
	*/
	function undoDeleteEvnStick() {
		$data = $this->ProcessInputData('undoDeleteEvnStick', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->undoDeleteEvnStick($data);
		$this->ProcessModelSave($response, true, 'При отмене удаления ЛВН возникли ошибки')->ReturnData();

		return true;
	}


	/**
	*  Удаление справки учащегося
	*  Входящие данные: $_POST['EvnStickStudent_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования ТАП
	*/
	function deleteEvnStickStudent() {
		$data = $this->ProcessInputData('deleteEvnStickStudent', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->deleteEvnStickStudent($data);
		$this->ProcessModelSave($response, true, 'При удалении справки учащегося возникли ошибки')->ReturnData();

		return true;
	}


	/**
	*  Удаление освобождения от работы
	*  Входящие данные: $_POST['EvnStickWorkRelease_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования ЛВН
	*/
	function deleteEvnStickWorkRelease() {
		$data = $this->ProcessInputData('deleteEvnStickWorkRelease', true);
		if ($data === false) {
			return false;
		}
		
		$response = $this->dbmodel->deleteEvnStickWorkRelease($data);
		$this->ProcessModelSave($response, true, 'При удалении освобождения от работы возникли ошибки')->ReturnData();

		return true;
	}


	/**
	 *	Получение списка ЛВН для добавления к учетному документу
	 */
	function getEvnStickChange(){
		$val  = array();

		$data = $this->ProcessInputData('getEvnStickChange', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getEvnStickChange($data);

		if ( is_array($response) ) {
			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		
		if ( $data['StickExisting'] == 0 ) {
			$val = array_merge(array(
				0 => array(
					'EvnStick_id' => -1,
					'EvnStickDoc' => toUTF('Новый'),
					'StickType_Name' => toUTF('ЛВН')
				),
				1 => array(
					'EvnStick_id' => -2,
					'EvnStickDoc' => toUTF('Новый'),
					'StickType_Name' => toUTF('Справка учащ-ся')
				)
			), $val);
		}

		$this->ReturnData($val);

		return true;
	}

	/**
	 *	Получение списка оригиналов ЛВН.
	 */
	function getEvnStickOriginalsList(){
		$data = $this->ProcessInputData('getEvnStickOriginalsList', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			$response = $this->dbmodel->getEvnStickOriginalsList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение списка ЛВН по основному месту работы
	 */
	function getEvnStickMainList(){
		$data = $this->ProcessInputData('getEvnStickMainList', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			$response = $this->dbmodel->getEvnStickMainList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}


	/**
	*  Получение номера справки учащегося
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования справки учащегося
	*/
	function getEvnStickStudentNumber() {
		$data = getSessionParams();

		if ( $data['Lpu_id'] == 0 ) {
			$this->ReturnData(array('success' => false));
			return true;
		}

		$response = $this->dbmodel->getEvnStickStudentNumber($data);
		$val = $this->ProcessModelList($response, true, true)->GetOutData(0);
		$this->ReturnData($val);
		
		return true;
	}


	/**
	*  Получение списка пациентов, нуждающихся в уходе
	*  Входящие данные: <фильтры>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования ЛВН
	*/
	function loadEvnStickCarePersonGrid() {
		$data = $this->ProcessInputData('loadEvnStickCarePersonGrid', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			$response = $this->dbmodel->loadEvnStickCarePersonGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}

	/**
	*  Получение различных периодик для человека в ЛВН
	*  Входящие данные: $_POST['EvnStick_id']
	*  На выходе: JSON-строка
	*  Используется: форма печати ЛВН
	*/
	function getPersonEvnRecords($params) {
		$data = $this->ProcessInputData(NULL, true);
		
		$outdata = array();
		
		if (isset($params[0])) {
			$params = $params[0];
			
			$data['Person_id'] = $params['Person_id'];
			$data['type'] = 3;
			$data['PersonEvn_insDTstart'] = date('Y-m-d',strtotime($params['EvnStick_setDate']));
			if(!empty($params['EvnStick_disDate'])) {
				$data['PersonEvn_insDTend'] = date('Y-m-d',strtotime($params['EvnStick_disDate']));
			}
			
			$EvnRecords = $this->dbmodel->getPersonEvnRecords($data);
			if ( is_array($EvnRecords) && count($EvnRecords) > 0 ) {
				// пациента
				$k=0;
				foreach ($EvnRecords as $EvnRecord) {
					$outdata['Person'][$k] = array('Server_id' => $EvnRecord['Server_id'],'PersonEvn_id' => $EvnRecord['PersonEvn_id'],'Person_Descr' => $EvnRecord['Person_Descr']);
					$k++;
				}
			}
			
			// если продолжение то подгружаем списки освобождений и ухода с основного ЛВН
			if (!empty($params['EvnStickDop_pid']))	{
				$params['EvnStick_id'] = $params['EvnStickDop_pid'];
			}
			
			/* врачей пока решили не выводить.
			$response = $this->dbmodel->loadEvnStickWorkReleaseGrid(array('EvnStick_id' => $params['EvnStick_id'], 'Lpu_id' => $data['session']['lpu_id']));						
			$period = 0;
			foreach ($response as $oneres) {
				// первых врачей 3 периодов
				$period++;
				if (!empty($oneres['Person_id'])) {
					$data['Person_id'] = $oneres['Person_id'];
					$data['type'] = 1;
					$EvnRecords = $this->dbmodel->getPersonEvnRecords($data);
					if ( is_array($EvnRecords) && count($EvnRecords) > 0 ) {
						$k=0;
						foreach ($EvnRecords as $EvnRecord) {
							$outdata['MedPerson'.$period][$k] = array('Server_id' => $EvnRecord['Server_id'],'PersonEvn_id' => $EvnRecord['PersonEvn_id'],'Person_Descr' => $EvnRecord['Person_Descr']);
							$k++;
						}
					}
				}
			}*/
			
			$response = $this->dbmodel->loadEvnStickCarePersonGrid(array('EvnStick_id' => $params['EvnStick_id'], 'Lpu_id' => $data['session']['lpu_id'], 'session' => $data['session']));
			$period = 0;
			foreach ($response as $oneres) {
				$period++;
				if ($period<3) {
					// первых 2 пациентов нуждающихся в уходе
					$data['Person_id'] = $oneres['Person_id'];
					$data['type'] = 2;
					$EvnRecords =  $this->dbmodel->getPersonEvnRecords($data);
					if ( is_array($EvnRecords) && count($EvnRecords) > 0 ) {
						$k=0;
						foreach ($EvnRecords as $EvnRecord) {
							$outdata['CarePerson'.$period][$k] = array('Server_id' => $EvnRecord['Server_id'],'PersonEvn_id' => $EvnRecord['PersonEvn_id'],'Person_Descr' => $EvnRecord['Person_Descr']);
							$k++;
						}
					}
				}
			}
		}

		return $outdata;
	}

	/**
	*  Получение данных для формы редактирования ЛВН
	*  Входящие данные: $_POST['EvnStick_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования ЛВН
	*/
	function loadEvnStickDopEditForm() {
		$data = $this->ProcessInputData('loadEvnStickDopEditForm', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEvnStickDopEditForm($data);
		$outdata = $this->ProcessModelList($response, true, true)->getOutData();
		if (isset($outdata[0]) && !empty($outdata[0]['EvnStick_prid'])) {
			$res = $this->dbmodel->getWorkReleaseSumPeriod(array('EvnStick_id' => $outdata[0]['EvnStick_prid']));
			$outdata[0]['WorkReleaseSumm'] = !empty($res[0]['WorkReleaseSumm']) ? $res[0]['WorkReleaseSumm'] : 0;
		}
		
		if (isset($outdata[0]) && $data['LoadForPrintStick'] == 1) {
			$personEvnRecords = $this->getPersonEvnRecords($outdata);
			if (count($personEvnRecords) > 0) {
				array_walk_recursive($personEvnRecords, 'ConvertFromWin1251ToUTF8');
				$outdata[0]['PersonEvnRecords'] = $personEvnRecords;
			}
		}
		
		$this->ReturnData($outdata);

		return true;
	}

	/**
	 * Функция проверки совпадения (Для КВС, в которой уже добавлен исход госпитализации с результатом Смерть требуется, чтобы дата закрытия ЛВН равнялась дате смерти и исход ЛВН был равен Смерть.)
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования ЛВН
	 */
	function CheckEvnStickDie() {
		$bool = true;
		
		$data = $this->ProcessInputData('CheckEvnStickDie', true);
		if ($data === false) { return false; }
		
		$bool = $this->dbmodel->CheckEvnStickDie($data);
		$this->ReturnData(array('success' => $bool));
		return $bool;
	}
	
	/**
	 * Функция провери номера на занятость существующими ЛВН в текущем году и со свободными из хранилища номеров
	 * Входящие данные: номер ЛВН
	 * На выходе: JSON-строка
	 * Используется: форма редактирования ЛВН
	 */
	function checkEvnStickNumDouble() {
		
		$data = $this->ProcessInputData('checkEvnStickNumDouble', true);
		if ($data === false) { return false; }
		
		$result = $this->dbmodel->checkEvnStickNumDouble($data);
		$this->ReturnData($result);
		return true;
	}
	
	/**
	*  Получение данных для формы редактирования ЛВН
	*  Входящие данные: $_POST['EvnStick_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования ЛВН
	*/
	function loadEvnStickEditForm() {
		$data = $this->ProcessInputData('loadEvnStickEditForm', true);
		if ($data === false) {
			return false;
		}

		
		if($data['delDocsView'] && $data['delDocsView'] == 1)
			$response = $this->dbmodel->loadEvnStickEditFormForDelDocs($data);
		else
			$response = $this->dbmodel->loadEvnStickEditForm($data);
		
		$outdata = $this->ProcessModelList($response, true, true)->getOutData();
		if (isset($outdata[0]) && !empty($outdata[0]['EvnStick_prid'])) {
			$res = $this->dbmodel->getWorkReleaseSumPeriod(array('EvnStick_id' => $outdata[0]['EvnStick_prid']));
			$outdata[0]['WorkReleaseSumm'] = !empty($res[0]['WorkReleaseSumm']) ? $res[0]['WorkReleaseSumm'] : 0;
		}
		
		if (isset($outdata[0]) && $data['LoadForPrintStick'] == 1) {
			$personEvnRecords = $this->getPersonEvnRecords($outdata);
			if (count($personEvnRecords) > 0) {
				array_walk_recursive($personEvnRecords, 'ConvertFromWin1251ToUTF8');
				$outdata[0]['PersonEvnRecords'] = $personEvnRecords;
			}
		}
		
		$this->ReturnData($outdata);
		
		return true;
	}


	/**
	 * Ищем "Дату начала" и "Дату окончания" для блока "Лечение в стационаре" на форме редактирования ЛВН
	 * @return bool
	 */
	function getBegEndDatesInStac(){
		$data = $this->ProcessInputData('getBegEndDatesInStac', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			$response = $this->dbmodel->getBegEndDatesInStac($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}
	/**
	 * Проверяет есть ли среди освобождений от работы то, в котором указано рабочее место из МО пользователя
	 * @return bool
	 */
	function checkWorkReleaseMedStaffFact() {
		$data = $this->ProcessInputData('checkWorkReleaseMedStaffFact', true, true);
		$Lpu_list = $this->dbmodel->getWorkReleaseLpuList($data);
		
		$userLpu_id = $data['session']['lpu_id'];
		$response = array(
			'MedStaffFactInUserLpu' => false
		);

		foreach ($Lpu_list as $rec) {
			if($rec['Lpu_id'] == $userLpu_id) {
				$response['MedStaffFactInUserLpu'] = true;
				break;
			}
		}
		$this->ReturnData($response);
		return true;
	}

	/**
	 * Ищем движения в найденной КВС
	 * @return bool
	 */
	function getEvnSectionList(){
		$data = $this->ProcessInputData('getEvnSectionList', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			$response = $this->dbmodel->getEvnSectionList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Ищем КВС (EvnPS_id) через EvnLink (в случае если ЛВН связана с КВС, но создана через ТАП)
	 * Входящие данные: $_POST['EvnStick_id']
	 * На выходе: JSON-строка
	 * @return bool
	 */
	function getEvnPSFromEvnLink(){
		$data = $this->ProcessInputData('getEvnPSFromEvnLink', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			$response = $this->dbmodel->getEvnPSFromEvnLink($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}


	/**
	*  Получение даты начала и даты выписки из движений для выбранной и связанных ЛВН.
	*  Входящие данные: $_POST['EvnStick_id']
	*  На выходе: JSON-строка
	*/
	function getEvnSectionDatesForEvnStick() {
		$data = $this->ProcessInputData('getEvnSectionDatesForEvnStick', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			$response = $this->dbmodel->getEvnSectionDatesForEvnStick($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	*  Получение списка листов нетрудоспособности
	*  Входящие данные: $_POST['EvnStick_mid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования талона амбулаторного пациента
	*                форма редактирования стоматологического талона амбулаторного пациента
	*                форма редактирования карты выбывшего из стационара
	*/
	function loadEvnStickGrid() {
		$data = $this->ProcessInputData('loadEvnStickGrid', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			$response = $this->dbmodel->loadEvnStickGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}


	/**
	*  Получение суммарного периода освобождений для цепочки ЛВН (Первичный -> Продолжение -> .. )
	*  Входящие данные: <фильтры>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования ЛВН
	*/
	function getWorkReleaseSumPeriod() {
		$data = $this->ProcessInputData('getWorkReleaseSumPeriod', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getWorkReleaseSumPeriod($data);
		if (!empty($data['getEvnPS24'])) {
			$this->ProcessModelList($response, true, true)->ReturnData();
		} else {
			$this->ProcessModelSave($response, true)->ReturnData();
		}
		return true;
	}
	
	
	/**
	*  Получение списка первичных ЛВН для ЛВН-продолжения
	*  Входящие данные: <фильтры>
	*  На выходе: JSON-строка
	*  Используется: форма просмотра списка ЛВН
	*/
	function loadEvnStickList() {
		$data = $this->ProcessInputData('loadEvnStickList', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			$response = $this->dbmodel->loadEvnStickList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}


	/**
	 *  Поиск ЛВН
	 *  Входящие данные: <фильтры>
	 *  На выходе: JSON-строка
	 *  Используется: форма поиска ЛВН
	 */
	function searchEvnStick() {
		$data = $this->ProcessInputData('searchEvnStick', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			$response = $this->dbmodel->searchEvnStick($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}


	/**
	*  Получение данных для формы редактирования справки учащегося
	*  Входящие данные: $_POST['EvnStickStudent_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования справки учащегося
	*/
	function loadEvnStickStudentEditForm() {
		$data = $this->ProcessInputData('loadEvnStickStudentEditForm', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEvnStickStudentEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	*  Получение списка освобождений от работы
	*  Входящие данные: <фильтры>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования ЛВН
	*/
	function loadEvnStickWorkReleaseGrid() {
		$data = $this->ProcessInputData('loadEvnStickWorkReleaseGrid', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			$response = $this->dbmodel->loadEvnStickWorkReleaseGrid($data);
			$retresponse = array();
			
			// если LoadSummPeriod = 1 то загружаем только 1 общий период (для дубликата).
			if ( ! empty($data['LoadSummPeriod']) && $data['LoadSummPeriod'] = 1) {
				foreach ($response as $oneres) {


					if(
						(empty($retresponse[0]['EvnStickWorkRelease_begDate'])) ||
						strtotime($oneres['EvnStickWorkRelease_begDate']) < strtotime($retresponse[0]['EvnStickWorkRelease_begDate'])
					){
						// С какого числа
						$retresponse[0]['EvnStickWorkRelease_begDate'] = $oneres['EvnStickWorkRelease_begDate'];					
					}

					
					if(
						(empty($retresponse[0]['EvnStickWorkRelease_endDate'])) ||
						strtotime($oneres['EvnStickWorkRelease_endDate']) > strtotime($retresponse[0]['EvnStickWorkRelease_endDate'])
					){
						$retresponse[0]['accessType'] = 'edit';
						$retresponse[0]['EvnStickWorkRelease_id'] = -999;
						$retresponse[0]['EvnStickBase_id'] = $oneres['EvnStickBase_id'];
						$retresponse[0]['LpuSection_id'] = $oneres['LpuSection_id'];
						$retresponse[0]['MedPersonal_id'] = $oneres['MedPersonal_id'];
						$retresponse[0]['MedPersonal2_id'] = $oneres['MedPersonal2_id'];
						$retresponse[0]['MedPersonal3_id'] = $oneres['MedPersonal3_id'];
						$retresponse[0]['MedStaffFact_id'] = $oneres['MedStaffFact_id'];
						$retresponse[0]['MedStaffFact2_id'] = $oneres['MedStaffFact2_id'];
						$retresponse[0]['MedStaffFact3_id'] = $oneres['MedStaffFact3_id'];

						// По какое число
						$retresponse[0]['EvnStickWorkRelease_endDate'] = $oneres['EvnStickWorkRelease_endDate'];

						// МО
						$retresponse[0]['Org_Name'] = $oneres['Org_Name'];

						// Врач
						$retresponse[0]['MedPersonal_Fio'] = $oneres['MedPersonal_Fio'];

						$retresponse[0]['RecordStatus_Code'] = 0;
						$retresponse[0]['EvnStickWorkRelease_IsPredVK'] = 1;

						// Статус
						$retresponse[0]['EvnStickWorkRelease_IsDraft'] = $oneres['EvnStickWorkRelease_IsDraft'];

						$retresponse[0]['Org_id'] = $oneres['Org_id'];
						$retresponse[0]['EvnVK_id'] = $oneres['EvnVK_id'];
						$retresponse[0]['EvnVK_descr'] = $oneres['EvnVK_descr'];
						$retresponse[0]['Post_id'] = $oneres['Post_id'];
					}
				}
				
				
			} else {
				$retresponse = $response;
			}
			
			$this->ProcessModelList($retresponse, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}


	/**
	*  Получение списка освобождений от работы
	*  Входящие данные: <фильтры>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования ЛВН
	*/
	function loadEvnStickStudentWorkReleaseGrid() {
		$data = $this->ProcessInputData('loadEvnStickStudentWorkReleaseGrid', true, true);

		if ( is_array($data) && count($data) > 0 ) {
			$response = $this->dbmodel->loadEvnStickStudentWorkReleaseGrid($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Обновление списка освобождений от работы для работы по совместительству
	 */
	function updateEvnStickWorkReleaseGrid () {
		$data = $this->ProcessInputData('updateEvnStickWorkReleaseGrid', true, true);
		if ($data === false) {
			return false;
		}

		$data['EvnStick_id'] = $data['EvnStick_pid'];

		$response = $this->dbmodel->loadEvnStickWorkReleaseGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных о предыдущем ЛВН
	 */
	function getEvnStickPridValues() {
		$data = $this->ProcessInputData('getEvnStickPridValues', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnStickPridValues($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении данных о предыдущем ЛВН')->ReturnData();
	}

	/**
	 * Получение данных о оригинале ЛВН
	 */
	function getEvnStickOriginInfo() {
		$data = $this->ProcessInputData('getEvnStickOriginInfo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnStickOriginInfo($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении данных о оригинале ЛВН')->ReturnData();
	}

	/**
	 * Получение данных о ЛВН
	 */
	function getEvnStickInfo() {
		$data = $this->ProcessInputData('getEvnStickInfo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnStickInfo($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении данных о оригинале ЛВН')->ReturnData();
	}


	/**
	 * Получение данных о продолжении ЛВН
	 */
	function getEvnStickProdValues() {
		$data = $this->ProcessInputData('getEvnStickProdValues', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnStickProdValues($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении данных о предыдущем ЛВН')->ReturnData();
	}
	
	/**
	*  Сохранение ЛВН / дополнительного ЛВН
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования ЛВН
	*/
	function saveEvnStick() {
		//die;
		$val  = array();

		$data = $this->ProcessInputData('selectEvnStickType', true);
		if ($data === false) {
			return false;
		}
		$this->load->model('StickFSSData_model', 'StickFSSData_model');
		

		$isKZ = ($data['session']['region']['nick']=='kz');
		// $IsUfa = ($_SESSION['region']['nick'] == 'ufa');

		switch ( $data['evnStickType'] ) {
			case 1:
				$err = getInputParams($data, $this->inputRules['saveEvnStick'], false);

				if (empty($err) && empty($data['EvnStickBase_IsFSS'])) { // checkbox
					if (empty($data['EvnStick_IsOriginal'])) {
						$this->ReturnError('Поле "Оригинал" обязательно для заполнения');
						return false;
					}
					if (empty($data['StickCause_id'])) {
						$this->ReturnError('Поле "Причина нетрудоспособности" обязательно для заполнения');
						return false;
					}
					if (empty($data['EvnStick_setDate'])) {
						$this->ReturnError('Поле "Дата выдачи" обязательно для заполнения');
						return false;
					}
					if (empty($data['StickOrder_id'])) {
						$this->ReturnError('Поле "Порядок выдачи" обязательно для заполнения');
						return false;
					}
					if (empty($data['StickWorkType_id'])) {
						$this->ReturnError('Поле "Тип занятости" обязательно для заполнения');
						return false;
					}
				}
			break;

			case 2:
				$err = getInputParams($data, $this->inputRules['saveEvnStickDop'], false);
			break;

			default:
				echo json_return_errors('Неверный тип ЛВН');
				return false;
			break;
		}

		$isEln = $this->dbmodel->checkELN($data);

		if ( sw_strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		if ( !empty($data['link']) && $data['link'] == 1 ) {
			if ( empty($data['EvnStick_id']) ) {
				echo json_return_errors('Не указан идентификатор ЛВН');
				return false;
			}
		}

		$carePersonList = array();
		$evnStickCarePerson = array();
		$evnStickWorkRelease = array();
		
		/*
		if ( in_array($data['StickWorkType_id'], array(1, 2)) && !$IsUfa ) {
			if ( empty($data['Org_id']) ) {
				echo json_return_errors('Поле "Место работы" обязательно для заполнения');
				return false;
			}

			if ( empty($data['EvnStick_OrgNick']) ) {
				echo json_return_errors('Поле "Наименование для печати" обязательно для заполнения');
				return false;
			}
		}
		*/

		if ( $data['evnStickType'] == 1 ) {
			if ( !empty($data['StickCause_id']) && $data['StickCause_id'] == $data['StickCause_did'] ) {
				echo json_return_errors('Поля "Причина нетрудоспособности" и "Код изм. нетрудоспособности" не могут иметь одинаковые значения');
				return false;
			}

			if ( !empty($data['StickLeaveType_id']) ) {
				// Поле врач необязательно для ЛВН где МО в последнем периоде освобождения не совпадает с текущей МО.
				/*if ( empty($data['MedPersonal_id']) ) {
					echo json_return_errors('Поле "Врач" обязательно для заполнения при заполненном исходе ЛВН');
					return false;
				}*/
				if ( empty($data['EvnStick_disDate']) ) {
					echo json_return_errors('Поле "Дата" обязательно для заполнения при заполненном исходе ЛВН');
					return false;
				}
			} else {
				$data['MedStaffFact_id'] = null;
				$data['MedPersonal_id'] = null;
				$data['EvnStick_disDate'] = null;
				$data['Lpu_oid'] = null;
			}

			// Обработка EvnStickCarePerson
			if ( !empty($data['evnStickCarePersonData']) ) {
				// Обработка списка пациентов, нуждающихся в уходе
				$evnStickCarePersonData = json_decode(toUTF($data['evnStickCarePersonData']), true);

				if ( is_array($evnStickCarePersonData) ) {
					// Обработка входных данных
					foreach ( $evnStickCarePersonData as $key => $array ) {
						if ( !isset($array['RecordStatus_Code']) || !is_numeric($array['RecordStatus_Code']) || !in_array($array['RecordStatus_Code'], array(0, 1, 2, 3)) ) {
							continue;
						}

						if ( empty($array['EvnStickCarePerson_id']) || !is_numeric($array['EvnStickCarePerson_id']) ) {
							continue;
						}

						// Правильность заполнения полей проверяем только для добавляемых или редактируемых записей
						if ( $array['RecordStatus_Code'] != 3 ) {
							if ( empty($array['Person_id']) || !is_numeric($array['Person_id']) ) {
								echo json_return_errors('Не указан пациент, нуждающийся в уходе');
								return false;
							}
							else if ( in_array($array['Person_id'], $carePersonList) ) {
								echo json_return_errors('Пациент не может быть указан дважды в списке нуждающихся в уходе');
								return false;
							}

							if ( (empty($array['RelatedLinkType_id']) || !is_numeric($array['RelatedLinkType_id'])) && !$isKZ ) {
								echo json_return_errors('Не указана родственная связь у пациента, нуждающегося в уходе');
								return false;
							}

							if ( $array['Person_id'] == $data['Person_id'] ) {
								echo json_return_errors('Пациент, которому выдается ЛВН, не может быть указан в списке пациентов, нуждающихся в уходе.');
								return false;
							}

							// находим минимальный возраст пациента в списке ухода
							if (empty($minCarePersonAge) || $minCarePersonAge > $array['Person_Age']) {
								$minCarePersonAge = $array['Person_Age'];
								$minCarePersonAge_id = $array['Person_id'];
							}

							$carePersonList[] = $array['Person_id'];
						}

						$evnStickCarePerson[] = array(
							'EvnStickCarePerson_id' => $array['EvnStickCarePerson_id'],
							'Person_id' => $array['Person_id'],
							'pmUser_id' => $data['pmUser_id'],
							'RecordStatus_Code' => $array['RecordStatus_Code'],
							'RelatedLinkType_id' => $array['RelatedLinkType_id']
						);
					}
				}
			}

			// Проверка записей о пациентах, нуждающихся в уходе
			$cnt = 0;

			// Проверка количества записей
			foreach ( $evnStickCarePerson as $key => $array ) {
				// Записи на удаление не учитываем
				if ( $array['RecordStatus_Code'] == 3 ) {
					continue;
				}

				$cnt++;
			}

			if ( $cnt > 2 ) {
				echo json_return_errors('Количество записей о пациентах, нуждающихся в уходе, не может превышать двух.');
				return false;
			}
		}

		if ( in_array($data['evnStickType'], array(1,2)) ) {
			// Обработка EvnStickWorkRelease
			if ( !empty($data['evnStickWorkReleaseData']) ) {
				// Обработка списка освобождений от работы
				$evnStickWorkReleaseData = json_decode(toUTF($data['evnStickWorkReleaseData']), true);

				if ( is_array($evnStickWorkReleaseData) ) {
					$existEndDate =  false;
					// Обработка входных данных
					foreach ( $evnStickWorkReleaseData as $key => $array ) {
						if ( !isset($array['RecordStatus_Code']) || !is_numeric($array['RecordStatus_Code']) || !in_array($array['RecordStatus_Code'], array(0, 1, 2, 3)) ) {
							continue;
						}

						if ( empty($array['EvnStickWorkRelease_id']) || !is_numeric($array['EvnStickWorkRelease_id']) ) {
							continue;
						}

						// Правильность заполнения полей проверяем только для добавляемых или редактируемых записей
						if ( $array['RecordStatus_Code'] != 3 ) {
							if ( empty($array['EvnStickWorkRelease_IsDraft']) ) {
								if (empty($array['LpuSection_id']) || !is_numeric($array['LpuSection_id'])) {
									echo json_return_errors('Не указано отделение в освобождении от работы');
									return false;
								}

								if ( empty($array['MedPersonal_id']) || !is_numeric($array['MedPersonal_id']) ) {
									echo json_return_errors('Не указан врач в освобождении от работы');
									return false;
								}
							} else {
								if (empty($array['Org_id']) || !is_numeric($array['Org_id'])) {
									echo json_return_errors('Не указано МО в освобождении от работы');
									return false;
								}
							}

							if ( empty($array['EvnStickWorkRelease_begDate']) ) {
								echo json_return_errors('Не указана дата начала периода освобождения от работы');
								return false;
							}
							else if ( CheckDateFormat($array['EvnStickWorkRelease_begDate']) != 0 ) {
								echo json_return_errors('Неверный формат даты начала периода освобождения от работы');
								return false;
							}

							if ( empty($array['EvnStickWorkRelease_endDate']) ) {
								echo json_return_errors('Не указана дата окончания периода освобождения от работы');
								return false;
							}
							else if ( CheckDateFormat($array['EvnStickWorkRelease_endDate']) != 0 ) {
								echo json_return_errors('Неверный формат даты окончания периода освобождения от работы');
								return false;
							}
						}
						if ( !empty($data['StickLeaveType_id']) ) {
							//Заполнен исход ЛВН, в таких случаях нужно проверять дату окончания в последнем периоде освобождения от работы
							if (strtotime($data['EvnStick_disDate']) <= strtotime($array['EvnStickWorkRelease_endDate'])) {
								echo json_return_errors('Дата исхода должна быть позже даты окончания последнего периода освобождения от работы');
								return false;
							}
						}						
						// проверяем наличие периода освобождения у которого дата окончания совпадает с датой окончания лечения в стационаре
						if ( 
							!empty($data['EvnStick_stacEndDate']) 
							&& strtotime( $array['EvnStickWorkRelease_endDate']) == strtotime($data['EvnStick_stacEndDate'])
						) {
							$existEndDate = true;
						}

						$evnStickWorkRelease[] = array(
							'EvnStickWorkRelease_begDate' => $array['EvnStickWorkRelease_begDate'],
							'EvnStickWorkRelease_endDate' => $array['EvnStickWorkRelease_endDate'],
							'EvnStickWorkRelease_id' => $array['EvnStickWorkRelease_id'],
							'EvnStickBase_id' => $array['EvnStickBase_id'],
							'LpuSection_id' => $array['LpuSection_id'],
							'MedPersonal_id' => $array['MedPersonal_id'],
							'MedPersonal2_id' => $array['MedPersonal2_id'],
							'MedPersonal3_id' => $array['MedPersonal3_id'],
							'MedStaffFact_id' => $array['MedStaffFact_id'],
							'MedStaffFact2_id' => $array['MedStaffFact2_id'],
							'MedStaffFact3_id' => $array['MedStaffFact3_id'],
							'pmUser_id' => $data['pmUser_id'],
							'RecordStatus_Code' => $array['RecordStatus_Code'],
							'EvnStickWorkRelease_IsPredVK' => $array['EvnStickWorkRelease_IsPredVK'],
							'EvnStickWorkRelease_IsDraft' => $array['EvnStickWorkRelease_IsDraft'],
							'EvnStickWorkRelease_IsSpecLpu' => $array['EvnStickWorkRelease_IsSpecLpu'],
							'Org_id' => $array['Org_id'],
							'Post_id' => $array['Post_id'],
							'Signatures_mid' => $array['Signatures_mid'],
							'Signatures_wid' => $array['Signatures_wid'],
							'EvnVK_id' => $array['EvnVK_id']
						);
					}

					if(	
						getRegionNick() != 'kz'
						&& $data['EvnStick_IsOriginal'] == 1 // оригинал 
						&& !empty($data['EvnStick_stacEndDate']) 
						&& count($evnStickWorkRelease) > 0
						&& !$existEndDate
					) {
						$StickCause_SysNick = $this->dbmodel->getStickCauseSysnick($data);
						if ( $StickCause_SysNick != 'pregn' ) {
							echo json_return_errors("Дата окончания пребывания в стационаре должна совпадать с датой окончания одного из периодов нетрудоспособности");
							return false;
						}
						
					}
				}
			}

			if (array_key_exists('EvnStick_pid', $data) && empty($data['EvnStickBase_IsFSS'])) {
				$resp = $this->dbmodel->checkEvnStickPerson($data['EvnStick_pid'], $data['Person_id'], $evnStickCarePerson);
				if (!empty($resp[0]['Error_Msg'])) {
					echo json_return_errors($resp[0]['Error_Msg']);
					return false;
				}

				if ($data['EvnStick_mid'] != $data['EvnStick_pid']) {
					$resp = $this->dbmodel->checkEvnStickPerson($data['EvnStick_mid'], $data['Person_id'], $evnStickCarePerson);
					if (!empty($resp[0]['Error_Msg'])) {
						echo json_return_errors($resp[0]['Error_Msg']);
						return false;
					}
				}
			}

			// Проверка записей об освобождении от работы
			$cnt = 0;
			$maxEndDate = null;
			$minBegDate = null;

			// Проверка количества записей
			foreach ( $evnStickWorkRelease as $key => $array ) {
				// Записи на удаление не учитываем
				if ( $array['RecordStatus_Code'] == 3 ) {
					continue;
				}

				$cnt++;
			}


			$data['PridStickLeaveType_Code'] = null;
			$data['StickCause_SysNick'] = null;
			$data['StickLeaveType_Code'] = null;
			$data['NextStickCause_SysNick'] = null;

			$this->dbmodel->getEvnStickPridData($data);

			$workReleaseCanBeEmpty = false;
			if (
				$data['StickOrder_id'] == 2
				&& $data['PridStickLeaveType_Code'] == '37'
				&& $data['StickCause_SysNick'] == 'dolsan'
			) {
				$workReleaseCanBeEmpty = true;
			}

			if ( !empty($data['StickFSSData_id']) ) {
				$StickFSSData = $this->StickFSSData_model->getStickFssData(array('StickFSSData_id' => $data['StickFSSData_id']));
			}

			if ( 
				! empty($data['StickFSSData_id']) 
				&& (
					(isset($StickFSSData['StickFSSDataStatus_id']) && $StickFSSData['StickFSSDataStatus_id'] != 4) // ЭЛН подтверждён
					|| !empty($StickFSSData['MedPersonal_FirstFIO']) // присутствует первое освобождениие от работы
				)
			) {
				$workReleaseCanBeEmpty = true;
			}


			if ( ! $workReleaseCanBeEmpty && $cnt == 0 ) {
				echo json_return_errors('Должно быть заполнено хотя бы одно освобождение от работы');
				return false;
			}
			else if ( $cnt > ($isKZ? 4:3) ) {
				echo json_return_errors('Количество записей об освобождении от работы не может превышать '.($isKZ?'четырех':'трех'));
				return false;
			}

			if ( ! empty($data['StickFSSData_id'])) {
				if ($cnt == 0 && empty($data['StickLeaveType_id'])) {
					echo json_return_errors('Должно быть заполнено хотя бы одно освобождение от работы или исход ЛВН');
					return false;
				}
			}

			$WorkReleaseSumm = 0;
			$curWorkReleaseSumm = 0;
			if (!empty($data['EvnStick_prid'])) {
				$resp = $this->dbmodel->getWorkReleaseSumPeriod(array('EvnStick_id' => $data['EvnStick_prid']));
				if (!empty($resp[0]['WorkReleaseSumm'])) {
					$WorkReleaseSumm = $resp[0]['WorkReleaseSumm'];
				}
			}

			// наличие пред. ВК в последнем освобождении
			$lastCheckVK = false;
			$lastIsDraft = false;
			foreach ( $evnStickWorkRelease as $key => $array ) {
				// Записи на удаление не учитываем
				if ( $array['RecordStatus_Code'] == 3 ) {
					continue;
				}

				$begDate = new DateTime(ConvertDateEx($array['EvnStickWorkRelease_begDate']));
				$endDate = new DateTime(ConvertDateEx($array['EvnStickWorkRelease_endDate']));
				$curWorkReleaseSumm += $begDate->diff($endDate)->days;

				// Сравниваем даты начала и окончания периода освобождения от работы
				$compareResult = swCompareDates($array['EvnStickWorkRelease_begDate'], $array['EvnStickWorkRelease_endDate']);

				if ( $compareResult[0] == -1 ) {
					echo json_return_errors('Дата начала периода не может быть больше даты окончания. Проверьте даты в записях об освобождении от работы.');
					return false;
				}

				// Максимальная дата окончания
				if ( $maxEndDate == null ) {
					$maxEndDate = $array['EvnStickWorkRelease_endDate'];
					if ( !empty($array['EvnStickWorkRelease_IsPredVK']) && !empty($array['MedStaffFact3_id']) ) {
						$checkVK = true;
					} else {
						$checkVK = false;
					}
					$lastCheckVK = $checkVK;
					$lastIsDraft = $array['EvnStickWorkRelease_IsDraft'] == 1;
					
				}
				else {
					$compareResult = swCompareDates($array['EvnStickWorkRelease_endDate'], $maxEndDate);

					if ( $compareResult[0] == -1 ) {
						$maxEndDate = $array['EvnStickWorkRelease_endDate'];

						// проверяем заполнен ли председатель ВК в последнем освобождении
						if ( !empty($array['EvnStickWorkRelease_IsPredVK']) && !empty($array['MedStaffFact3_id']) ) {
							$checkVK = true;
						} else {
							$checkVK = false;
						}
						$lastCheckVK = $checkVK;
						$lastIsDraft = $array['EvnStickWorkRelease_IsDraft'] == 1;
					}
				}

				// Минимальная дата начала
				if ( $minBegDate == null ) {
					$minBegDate = $array['EvnStickWorkRelease_begDate'];
				}
				else {
					$compareResult = swCompareDates($array['EvnStickWorkRelease_begDate'], $minBegDate);

					if ( $compareResult[0] == 1 ) {
						$minBegDate = $array['EvnStickWorkRelease_begDate'];
					}
				}
			}

			$WorkReleaseSumm += $curWorkReleaseSumm;
			if (
				empty($data['ignoreCheckSummWorkRelease']) && !empty($minCarePersonAge) && $minCarePersonAge < 15
				&& !empty($data['StickCause_SysNick']) && in_array($data['StickCause_SysNick'], array('uhod', 'uhodnoreb', 'zabrebmin', 'uhodreb', 'rebinv'))
			) {
				$queryParams = array(
					'Person_id' => $minCarePersonAge_id
				);

				switch ($data['StickCause_SysNick']) {
					case 'uhod':
					case 'uhodnoreb':
						$summLimit = 60;
						break;
					case 'zabrebmin':
					case 'uhodreb':
						$summLimit = 90;
						break;
					case 'rebinv':
						$summLimit = 120;
						break;

				}

				if (!empty($data['EvnStick_id'])) {
					$queryParams['exceptEvnStick_id'] = $data['EvnStick_id'];
				}

				$resp_SummPer = $this->dbmodel->getEvnStickWorkReleaseCalculation($queryParams);
				$summPeriod = $resp_SummPer['SumDaysCount'] + $curWorkReleaseSumm;
				if ($summLimit < $summPeriod) {
					echo json_encode(array(
						'Alert_Msg' => "Число календарных дней в текущем календарном году по всем завершенным случаям ухода за пациентом {$resp_SummPer['Person_Fio']}, {$resp_SummPer['Person_BirthDay']} составляет {$summPeriod} дней. В соответствии с п.35 Приказа МЗ РФ от 29.06.2011 №624н \"Об утверждении порядка выдачи листков нетрудоспособности\" число дней не должно превышать {$summLimit}. Продолжить сохранение?",
						'Error_Msg' => 'YesNo',
						'Error_Code' => 108,
						'success' => false
					));
					return false;
				}
			}

			// #191658 добавил контроль заполненности ВК при сохранении ЛВН т.к. есть возможность обойти контроль при сохранении освобождений
			if (
				!$lastCheckVK // пред. ВК в последнем освобождении
				&& !$lastIsDraft // в последнем освобождении не черновик
				&& (
					$data['EvnStick_IsOriginal'] == 2 // дубликат
					|| (
						$WorkReleaseSumm > 15
						&& (
							in_array(getRegionNick(), array('vologda', 'kareliya')) || $data['StickParentClass'] != 'EvnPS' 
							|| (
								$data['StickParentClass'] == 'EvnPS'
								&& !empty($data['isHasDvijeniaInStac24'])
								&& $data['isHasDvijeniaInStac24'] == 'false'
							)
						)
						&& (
							$data['StickCause_SysNick'] == 'desease'
							|| $data['StickCause_SysNick'] == 'trauma'
							|| $data['StickCause_SysNick'] == 'accident'
							|| $data['StickCause_SysNick'] == 'protstac'
							|| $data['StickCause_SysNick'] == 'prof'
							|| $data['StickCause_SysNick'] == 'dolsan'
							|| $data['StickCause_SysNick'] == 'uhodnoreb'
							|| $data['StickCause_SysNick'] == 'inoe'
							|| $data['StickCause_SysNick'] == 'uhodreb'
							|| $data['StickCause_SysNick'] == 'rebinv'
						)
					)
				)
			) {
				echo json_encode(array(
					'Error_Msg' => 'Общий период освобождения от работы превышает 15 дней, необходимо заполнить сведения о ВК',
					'success' => false
				));
				return false;
			}


			$VKprot = false;

			// Проверяем пересечение периодов
			foreach ( $evnStickWorkRelease as $key => $array ) {
				// Записи на удаление не учитываем
				if ( $array['RecordStatus_Code'] == 3 ) {
					continue;
				}

				$crossDates = false;

				// данная проверка некорректна, если откредактировать ЛВН и освобождения будет срабатывать, даже если нет пересечений, т.к. данные ещё не сохранены в БД.
				/*//Проверка если в базе есть освобождение с пересекающейся датой
				$is_intersect = $this->dbmodel->getFirstResultFromQuery("
					select top 1 EvnStickWorkRelease_id
					from v_EvnStickWorkRelease with (nolock)
					where EvnStickBase_id = :EvnStickBase_id
						and EvnStickWorkRelease_begDT < :EvnStickWorkRelease_endDate
						and EvnStickWorkRelease_endDT > :EvnStickWorkRelease_begDate
				", $array);

				if ($is_intersect !== false) {
					$crossDates = true;
				}*/
				if (!empty($array['EvnStickWorkRelease_IsPredVK'])) {
					$VKprot = true;
				}

				foreach ( $evnStickWorkRelease as $keyTmp => $arrayTmp ) {
					// Записи на удаление и совпадающие с текущей записью основного цикла не учитываем
					if ( $arrayTmp['RecordStatus_Code'] == 3 || $keyTmp == $key ) {
						continue;
					}

					$compareResult = swCompareDates($array['EvnStickWorkRelease_begDate'], $arrayTmp['EvnStickWorkRelease_begDate']);

					// Даты начала совпадают -> пересечение
					if ( $compareResult[0] == 0 ) {
						$crossDates = true;
					}
					else {
						// $array - ранний период
						if ( $compareResult[0] == 1 ) {
							$compareResult = swCompareDates($array['EvnStickWorkRelease_endDate'], $arrayTmp['EvnStickWorkRelease_begDate']);
						}
						// $array - поздний период
						else if ( $compareResult[0] == -1 ) {
							$compareResult = swCompareDates($arrayTmp['EvnStickWorkRelease_begDate'], $array['EvnStickWorkRelease_endDate']);
						}
						else {
							echo json_return_errors($compareResult[1]);
							return false;
						}

						// Дата окончания раннего периода больше даты начала позднего периода -> пересечение
						if ( $compareResult[0] == 0 || $compareResult[0] == -1 ) {
							$crossDates = true;
						}
					}
				}

				if ( $crossDates == true ) {
					echo json_return_errors('Обнаружено пересечение прериодов освобождения от работы!<br/>Проверьте указанные сроки и исправьте.');
					return false;
				}
			}

			if(!empty($data['EvnStick_mseDate']) && !$VKprot) {
				echo json_encode(array(
					'Alert_Msg' => 'При направлении в бюро МСЭ необходимо заполнить  данные о ВК в периоде нетрудоспособности.',
					'Error_Code' => 201,
					'success' => false
				));
				return false;
			}

			$response = $this->dbmodel->getEvnStickWorkReleaseDateLimits(array(
				'EvnStickBase_id' => $data['EvnStick_id'],
				'EvnStickBase_prid' => $data['EvnStick_prid']
			));

			if ( ! empty($response['Error_Msg']) ) {
				echo json_return_errors($response['Error_Msg']);
				return false;
			}


			if ( ! empty($response['minBegDate']) ) {
				$compareResult = swCompareDates($maxEndDate, $response['minBegDate']);

				if ( $compareResult[0] == 0 ) {
					if (getRegionNick() == 'ufa' && ($data['StickLeaveType_Code'] == '37' && $data['NextStickCause_SysNick'] == 'dolsan')) {
						// это исключение и ошибку выдавать не нужно
					} else {
						echo json_return_errors('Максимальная дата освобождения от работы по текущему ЛВН равна минимальной дате начала периодов освобождения от работы в ЛВН-продолжении');
						return false;
					}
				}
				else if ( $compareResult[0] == -1 ) {
					echo json_return_errors('Максимальная дата освобождения от работы по текущему ЛВН меньше минимальной даты начала периодов освобождения от работы в ЛВН-продолжении');
					return false;
				}
			}

			if ( ! empty($response['maxEndDate']) ) {
				$compareResult = swCompareDates($response['maxEndDate'], $minBegDate);

				if ($compareResult[0] == 0) {
					if (getRegionNick() == 'ufa' && ($data['PridStickLeaveType_Code'] == '37' && $data['StickCause_SysNick'] == 'dolsan')) {
						// это исключение и ошибку выдавать не нужно
					} else {
                        echo json_return_errors('Минимальная дата освобождения от работы по текущему ЛВН равна максимальной дате окончания периодов освобождения от работы в предыдущем ЛВН');
                        return false;
                    }
				}
				else if ( $compareResult[0] == -1 ) {
					echo json_return_errors('Минимальная дата освобождения от работы по текущему ЛВН меньше максимальной даты окончания периодов освобождения от работы в предыдущем ЛВН');
					return false;
				}
			}
		}


		// Проверка места работы при выписке ЛВН по совместительству
		if ( $data['evnStickType'] == 2 && empty($data['ignoreCheckEvnStickOrg']) && empty($data['EvnStick_id'])) {
			// Проверяем организацию на дубли
			$response = $this->dbmodel->checkEvnStickOrg($data);

			if ( !is_array($response) || count($response) == 0 ) {
				echo json_return_errors('Ошибка при проверке организации на предмет использования в других ЛВН');
				return false;
			}
			else if ( $response[0]['cnt'] > 0 ) {
				$this->ReturnData(array(
					'Error_Msg' => 'YesNo',
					'Error_Code' => 103,
					'Alert_Msg' => 'При выписке больничного листа по совместительству в блоке "Место работы" должны быть указаны данные организации, в которой работник работает по совместительству. Продолжить сохранение?'
				));
				return false;
			}
		}

		// Проверяем уникальность серии и номера ЛВН
		$checkResult = $this->dbmodel->checkEvnStickSerNum($data);

		if ( $checkResult['success'] === false ) {
			echo json_return_errors($checkResult['Error_Msg']);
			return false;
		}

		// Стартуем транзакцию
		$this->dbmodel->beginTransaction();

		// Для типа занятости "Основная работа" и "Работа по совместительству" проверяем и, в случае необходимости,
		// обновляем поле Org.Org_StickNick, а также организацию на предмет использования в других ЛВН
		if (!empty($data['Org_id'])) {
			if ( in_array($data['StickWorkType_id'], array(1, 2)) ) {
			
				// $data['EvnStick_OrgNick'] = toAnsi($data['EvnStick_OrgNick']);
				if(empty($data['EvnStickBase_IsFSS'])) {
					if ( !$isEln && sw_strlen($data['EvnStick_OrgNick']) > 29 ) {
						$this->ReturnError('Длина наименования организации для печати превышает 29 символов');
						$this->dbmodel->rollbackTransaction();
						return false;
					}
					if ( $isEln && sw_strlen($data['EvnStick_OrgNick']) > 255 ) {
						$this->ReturnError('Длина наименования организации для печати превышает 255 символов');
						$this->dbmodel->rollbackTransaction();
						return false;
					}
				}

				/*$response = $this->dbmodel->getOrgStickNick($data);

				if ( !is_array($response) || count($response) == 0 || count($response[0]) == 0 ) {
					$this->ReturnError('Ошибка при получении данных по выбранной организации');
					$this->dbmodel->rollbackTransaction();
					return false;
				}

				if ( $response[0]['Org_StickNick'] != $data['EvnStick_OrgNick'] ) {
					$response[0]['Org_StickNick'] = $data['EvnStick_OrgNick'];

					$response = $this->dbmodel->updateOrgStickNick($response[0]);

					if ( !is_array($response) || count($response) == 0 ) {
						echo json_return_errors('Ошибка при обновлении наименования организации для печати ЛВН');
						$this->dbmodel->rollbackTransaction();
						return false;
					}
					else if ( !empty($response[0]['Error_Msg']) ) {
						echo json_return_errors($response[0]['Error_Msg']);
						$this->dbmodel->rollbackTransaction();
						return false;
					}
				}*/
			}
		}
		
		// Сохраняем ЛВН
		switch ( $data['evnStickType'] ) {
			case 1:
				$response = $this->dbmodel->saveEvnStick($data);
			break;

			case 2:
				$response = $this->dbmodel->saveEvnStickDop($data);
			break;
		}

		if ( !is_array($response) || count($response) == 0 ) {
			echo json_return_errors('Ошибка при сохранении ЛВН');
			$this->dbmodel->rollbackTransaction();
			return false;
		}

		$val = $response[0];

		if ( !empty($val['Error_Msg']) ) {
			$this->ReturnData($val);
			$this->dbmodel->rollbackTransaction();
			return false;
		}

		if ( !empty($data['link']) && $data['link'] == 1 ) {
			// Добавляем связку учетного документа и ЛВН
			$response = $this->dbmodel->addEvnLink(array(
				'EvnStickBase_id' => $data['EvnStick_id'],
				'EvnStickBase_mid' => $data['EvnStick_mid'],
				'pmUser_id' => $data['pmUser_id']
			));

			if ( !is_array($response) || count($response) == 0 ) {
				echo json_return_errors('Ошибка при проверке наличия в БД добавляемой связки документа с ЛВН');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				echo json_return_errors($response[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return false;
			}
			// находим связанные ЛВН по совместительству
			$response = $this->dbmodel->getEvnStickDopByPid($data);
			if(!empty($response)) {
				//добавляем ссылки на ЛВН по совместительству
				foreach ($response as $rec) { 
					$response = $this->dbmodel->addEvnLink(array(
						'EvnStickBase_id' => $rec['EvnStickDop_id'],
						'EvnStickBase_mid' => $data['EvnStick_mid'],
						'pmUser_id' => $data['pmUser_id']
					));

					if ( !is_array($response) || count($response) == 0 ) {
						echo json_return_errors('Ошибка при проверке наличия в БД добавляемой связки документа с ЛВН');
						$this->dbmodel->rollbackTransaction();
						return false;
					}
					else if ( !empty($response[0]['Error_Msg']) ) {
						echo json_return_errors($response[0]['Error_Msg']);
						$this->dbmodel->rollbackTransaction();
						return false;
					}
				}
			}
			
			
		}
		//Удаляем связку с учётным документом если ни один период нетрудоспособности не совпадает с диапазоном дат в учётном документе, только если этот документ не родительский.
		$response = $this->dbmodel->removeEvnLink($data, $evnStickWorkRelease, $val['EvnStick_id']);
		if ( !empty($response[0]['Error_Msg']) ) {
			echo json_return_errors($response[0]['Error_Msg']);
			$this->dbmodel->rollbackTransaction();
			return false;
		}

		// Удаление записей о пациентах, нуждающихся в уходе
		foreach ( $evnStickCarePerson as $key => $array ) {
			if ( $array['RecordStatus_Code'] != 3 ) {
				continue;
			}

			$response = $this->dbmodel->deleteEvnStickCarePerson($array);

			if ( !is_array($response) || count($response) == 0 ) {
				echo json_return_errors('Ошибка при удалении пациента, нуждающегося в уходе');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				echo json_return_errors($response[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}

		// Сохранение записей о пациентах, нуждающихся в уходе
		foreach ( $evnStickCarePerson as $key => $array ) {
			if ( $array['RecordStatus_Code'] == 1 || $array['RecordStatus_Code'] == 3 ) {
				continue;
			}

			$array['Evn_id'] = $val['EvnStick_id'];

			$response = $this->dbmodel->saveEvnStickCarePerson($array);

			if ( !is_array($response) || count($response) == 0 ) {
				echo json_return_errors('Ошибка при сохранении пациента, нуждающегося в уходе');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				echo json_return_errors($response[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}

		// Удаление записей об освобождении от работы
		foreach ( $evnStickWorkRelease as $key => $array ) {
			if ( $array['RecordStatus_Code'] != 3 ) {
				continue;
			}

			$response = $this->dbmodel->deleteEvnStickWorkRelease($array);

			if ( !is_array($response) || count($response) == 0 ) {
				echo json_return_errors('Ошибка при удалении освобождения от работы');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				echo json_return_errors($response[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}

		// Сохранение записей об освобождении от работы
		foreach ( $evnStickWorkRelease as $key => $array ) {
			if ( $array['RecordStatus_Code'] == 1 || $array['RecordStatus_Code'] == 3 ) {
				continue;
			}

			$array['EvnStickBase_id'] = $val['EvnStick_id'];
			$array['EvnStickWorkRelease_begDate'] = ConvertDateFormat($array['EvnStickWorkRelease_begDate']);
			$array['EvnStickWorkRelease_endDate'] = ConvertDateFormat($array['EvnStickWorkRelease_endDate']);

			$response = $this->dbmodel->saveEvnStickWorkRelease($array, 'local');

			if ( !is_array($response) || count($response) == 0 ) {
				echo json_return_errors('Ошибка при сохранении освобождения от работы');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				echo json_return_errors($response[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}

		$error = $this->dbmodel->checkStickFSS($data);
		if( !empty($error) && !empty($error[0]['Error_Msg'])) {
			echo json_return_errors($error[0]['Error_Msg']);
			$this->dbmodel->rollbackTransaction();
			return false; 
		}

		$val['success'] = true;
		$this->dbmodel->commitTransaction();

		if (!empty($val['EvnStick_id'])) {
			// кэшируем статус
			$this->dbmodel->ReCacheEvnStickStatus(array(
				'EvnStick_id' => $val['EvnStick_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Сохранение справки учащегося
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования справки учащегося
	*/
	function saveEvnStickStudent() {
		$val  = array();

		$data = $this->ProcessInputData('saveEvnStickStudent', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->saveEvnStickStudent($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении справки учащегося')->ReturnData();

		return true;
	}

	/**
	*  Получение списка направлений на патологогистологическое исследование
	*  Входящие данные: <фильтры>
	*  На выходе: JSON-строка
	*  Используется: поиск ЛВН
	*/
	function loadEvnStickSearchGrid() {
		$archive_database_enable = $this->config->item('archive_database_enable');
		// если по архивным - используем архивную БД
		if (!empty($archive_database_enable) && !empty($_REQUEST['useArchive'])) {
			$this->db = null;
			$this->load->database('archive', false);
		}

		$data = $this->ProcessInputData('loadEvnStickSearchGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnStickSearchGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}


	/**
	*  Печать справки учащегося
	*  Входящие данные: $_GET['EvnStickStudent_id']
	*  На выходе: форма для печати справки учащегося
	*  Используется: форма редактирования справки учащегося
	*/
	function printEvnStickStudent() {
		$this->load->library('parser');

		$mode = '';

		$data = $this->ProcessInputData('printEvnStickStudent', true);
		if ($data === false) { return false; }
		/* не используется, закомментил
		$data['IsUfa']              = false;
		
		if ( $_SESSION['region']['nick'] == 'ufa' ) {
			$data['IsUfa'] = true;
		}
		*/
		if ( (isset($_GET['preview'])) && ($data['preview'] == 1) ) {
			$mode = 'preview';
		}

		// Получаем данные по справке учащегося
		$response = $this->dbmodel->getEvnStickStudentFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по справке учащегося';
			return false;
		}

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
		$arMonthBirth = array(
			1 => "январь",
			2 => "февраль",
			3 => "март",
			4 => "апрель",
			5 => "май",
			6 => "июнь",
			7 => "июль",
			8 => "август",
			9 => "сентябрь",
			10 => "октябрь",
			11 => "ноябрь",
			12 => "декабрь"
		);
		$block_underline = 'border-bottom: 2px solid #000;';
		$template = 'print_evn_stick_student';
		$text_underline = 'text-decoration: underline;';
		if ($response[0]['Person_Age'] < 1) {

			$person_birthay_month = isset($arMonthOf[$response[0]['Person_Birthday_Month']]) ? $arMonthOf[$response[0]['Person_Birthday_Month']] : '&nbsp;';
			$person_birthay_monthday = $response[0]['Person_Birthday_Day'].'&nbsp;'.$person_birthay_month;
		}
		else{
			$person_birthay_monthday = isset($arMonthBirth[$response[0]['Person_Birthday_Month']]) ? $arMonthBirth[$response[0]['Person_Birthday_Month']] : '&nbsp;';
		}
		$print_data = array(
			'EvnStickStudent_ContactDescr_1' => '&nbsp;',
			'EvnStickStudent_ContactDescr_2' => '&nbsp;',
			'EvnStickStudent_IsContact_0' => '',
			'EvnStickStudent_IsContact_1' => '',
			'EvnStickStudent_setDay' => ($response[0]['EvnStickStudent_setDate_Day'] > 0 ? sprintf('%02d', $response[0]['EvnStickStudent_setDate_Day']) : '&nbsp;'),
			'EvnStickStudent_setMonth' => isset($arMonthOf[$response[0]['EvnStickStudent_setDate_Month']]) ? $arMonthOf[$response[0]['EvnStickStudent_setDate_Month']] : '&nbsp;',
			'EvnStickStudent_setYear' => $response[0]['EvnStickStudent_setDate_Year'],
			'EvnStickWorkRelease_begDate_1' => '&nbsp;',
			'EvnStickWorkRelease_begDate_2' => '&nbsp;',
			'EvnStickWorkRelease_endDate_1' => '&nbsp;',
			'EvnStickWorkRelease_endDate_2' => '&nbsp;',
			'mode' => $mode,
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'MedPersonal_Fin' => returnValidHTMLString($response[0]['MedPersonal_Fin']),
			'Org_Name_1' => '&nbsp;',
			'Org_Name_2' => '&nbsp;',
			'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
			'Person_Birthay' => $person_birthay_monthday.'&nbsp;'.$response[0]['Person_Birthday_Year'],
			'StickCause_Name' => returnValidHTMLString($response[0]['StickCause_Name']),
			'StickRecipient_1' => '',
			'StickRecipient_2' => '',
			'StickRecipient_3' => ''
		);

		$print_data['EvnStickStudent_IsContact_' . $response[0]['EvnStickStudent_IsContact']] = $block_underline;

		if ( sw_strlen($print_data['MedPersonal_Fin']) > 0 ) {
			$print_data['MedPersonal_Fin'] = "( " . $print_data['MedPersonal_Fin'] . " )";
		}

		$print_data['MZRegion'] = getRegionNick() !== 'kz' ? 'РФ' : 'СССР';

		switch ( $response[0]['StickRecipient_Code'] ) {
			case 1:
				$print_data['StickRecipient_1'] = $block_underline;
			break;

			case 2:
			case 3:
			case 4:
				$print_data['StickRecipient_2'] = $block_underline;
			break;

			case 5:
				$print_data['StickRecipient_3'] = $block_underline;
			break;
		}

		$contact_descr = $response[0]['EvnStickStudent_ContactDescr'];
		$contact_descr = preg_replace("/[ \n]+/", ' ', $contact_descr);
		$org_name = $response[0]['Org_Name'];
		$org_name = preg_replace("/[ \n]+/", ' ', $org_name);

		$contact_descr_array = explode(' ', $contact_descr);
		$org_name_array = explode(' ', $org_name);

		if ( count($contact_descr_array) > 0 ) {
			$i = 1;
			$print_data['EvnStickStudent_ContactDescr_1'] = $contact_descr_array[0];
			$print_data['EvnStickStudent_ContactDescr_2'] = '';

			while ( $i < count($contact_descr_array) && sw_strlen($print_data['EvnStickStudent_ContactDescr_1'] . ' ' . $contact_descr_array[$i]) <= 68 ) {
				$print_data['EvnStickStudent_ContactDescr_1'] .= ' ' . $contact_descr_array[$i];
				$i++;
			}

			for ( $j = $i; $j < count($contact_descr_array); $j++ ) {
				$print_data['EvnStickStudent_ContactDescr_2'] .= $contact_descr_array[$j] . ' ';
			}

			if ( sw_strlen(trim($print_data['EvnStickStudent_ContactDescr_2'])) == 0 ) {
				$print_data['EvnStickStudent_ContactDescr_2'] = '&nbsp;';
			}
		}

		if ( count($org_name_array) > 0 ) {
			$i = 1;
			$print_data['Org_Name_1'] = $org_name_array[0];
			$print_data['Org_Name_2'] = '';

			while ( $i < count($org_name_array) && sw_strlen($print_data['Org_Name_1'] . ' ' . $org_name_array[$i]) <= 68 ) {
				$print_data['Org_Name_1'] .= ' ' . $org_name_array[$i];
				$i++;
			}

			for ( $j = $i; $j < count($org_name_array); $j++ ) {
				$print_data['Org_Name_2'] .= $org_name_array[$j] . ' ';
			}

			if ( sw_strlen(trim($print_data['Org_Name_2'])) == 0 ) {
				$print_data['Org_Name_2'] = '&nbsp;';
			}
		}

		// Освобождение от занятий/посещений
		$response_eswr = $this->dbmodel->getEvnStickStudentWorkReleaseFields($data);
		if ( is_array($response_eswr) ) {
			for ( $i = 1; $i <= count($response_eswr); $i++ ) {
				$print_data['EvnStickWorkRelease_begDate_' . $i] = ($response_eswr[$i - 1]['EvnStickWorkRelease_begDay'] > 0 ? sprintf('%02d', $response_eswr[$i - 1]['EvnStickWorkRelease_begDay']) : '&nbsp;');
				$print_data['EvnStickWorkRelease_begDate_' . $i] .= ' ' . (isset($arMonthOf[$response_eswr[$i - 1]['EvnStickWorkRelease_begMonth']]) ? $arMonthOf[$response_eswr[$i - 1]['EvnStickWorkRelease_begMonth']] : '&nbsp;');
				$print_data['EvnStickWorkRelease_begDate_' . $i] .= ' ' . $response_eswr[$i - 1]['EvnStickWorkRelease_begYear'] . 'г.';
				$print_data['EvnStickWorkRelease_endDate_' . $i] = returnValidHTMLString($response_eswr[$i - 1]['EvnStickWorkRelease_endDay']);
				$print_data['EvnStickWorkRelease_endDate_' . $i] .= ' ' . (isset($arMonthOf[$response_eswr[$i - 1]['EvnStickWorkRelease_endMonth']]) ? $arMonthOf[$response_eswr[$i - 1]['EvnStickWorkRelease_endMonth']] : '&nbsp;');
				$print_data['EvnStickWorkRelease_endDate_' . $i] .= ' ' . $response_eswr[$i - 1]['EvnStickWorkRelease_endYear'] . 'г.';
			}
		}

		return $this->parser->parse($template, $print_data);
	}


	/**
	*  Сохранение освобождения от работы
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования освобождения от работы
	*/
	function saveEvnStickWorkRelease() {
		$val  = array();

		$data = $this->ProcessInputData('saveEvnStickWorkRelease', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->saveEvnStickWorkRelease($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении освобождения от работы')->ReturnData();

		return true;
	}
	
	
	/**
	*  Получение ссылки для печати ЛВН из АРМ врача
	*  Входящие данные: $_POST['Stick_id'], $_POST['evnStickType']
	*  На выходе: JSON-строка с HTML в элементе 'html'
	*  Используется: электронная медицинская карта
	*/
	function loadStickViewForm() {
		$data = $this->ProcessInputData('loadEvnStickViewForm', true);
		if ($data === false) {
			return false;
		}
		
		$url = "/?c=Stick&m=printEvnStick&preview=1&evnStickType=" . $data['evnStickType'];

		switch ( $data['evnStickType'] ) {
			case 1:
				$url .= "&EvnStick_id=" . $data['Stick_id'];
			break;

			case 2:
				$url .= "&EvnStickDop_id=" . $data['Stick_id'];
			break;
		}

		$this->ReturnData(array("success"=>true, "html" => $url));

		return true;
	}

	/**
	 * Получение списка ЛВН с расчетом дней нетрудоспособности по уходу за ребенком
	 */
	function loadClosedEvnStickGrid() {
		$data = $this->ProcessInputData('loadClosedEvnStickGrid', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadClosedEvnStickGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных расчета дней нетрудоспособности по уходу за ребенком
	 */
	function getEvnStickWorkReleaseCalculation() {
		$data = $this->ProcessInputData('getEvnStickWorkReleaseCalculation', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getEvnStickWorkReleaseCalculation($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении данных')->ReturnData();

		return true;
	}

	/**
	 * Получение списка случаев лечения для АРМа регистратора ЛВН
	 */
	function loadEvnStickPids() {
		$data = $this->ProcessInputData('loadEvnStickPids',true);
		if($data === false)
			return false;
		$response = $this->dbmodel->loadEvnStickPids($data);

		$this->ProcessModelMultiList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка ЛВН для выбранного ТАП или КВС для АРМа регистратора ЛВН
	 */
	function loadEvnStickForARM(){
		$data = $this->ProcessInputData('loadEvnStickForARM',true);
		if($data === false)
			return false;
		$response = $this->dbmodel->loadEvnStickForARM($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

    /**
     * Получаем уровень, в которм лежит наша служба
     */
    function getMedServiceParent() {
        $data = $this->ProcessInputData('getMedServiceParent', true);
        if ($data) {
            $response = $this->dbmodel->getMedServiceParent($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверка является ли ЛВН электронным
     */
    function checkELN() {
	    $data = $this->ProcessInputData('checkELN');

    	if($data) {
	    	$response = $this->dbmodel->checkELN($data);
	    	$this->returnData(array(
	    		'isELN' => $response,
	    		'success' => 'true'
	    	));
	    	return true;
		} else {
			return false;
		}
    }

    /**
     * Проверка является ли текущая МО санаторием
     */
    function checkSanatorium() {
    	$data = $this->ProcessInputData('checkSanatorium', true);
    	if (!$data) {return false;}
    	
    	$response = $this->dbmodel->isSanatorium($data);
    	if (!empty($response['Error_Msg'])) {
    		$response['success'] = false;
    		$this->returnData($response);

    		return false;
    	} else {
    		$this->returnData(array(
    			'success' => true,
    			'isSanatorium' => $response
    		));

    		return true;
    	}
    }

	/**
	 * Проверка инфы о врачах в освобождениях от работы (https://redmine.swan.perm.ru/issues/83780)
	 */
	function WorkReleaseMedStaffFactCheck() {
		$data = $this->ProcessInputData('WorkReleaseMedStaffFactCheck', true);
		if ($data) {
			$response = $this->dbmodel->WorkReleaseMedStaffFactCheck($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Хэш для подписи 
	*/
	function getWorkReleaseSslHash () {
		$data = $this->ProcessInputData('getWorkReleaseSslHash', true);
		if ($data) {
			$response = $this->dbmodel->getWorkReleaseSslHash($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Подписание освобождения
	*/
	function signWorkRelease () {
		$data = $this->ProcessInputData('signWorkRelease', true);
		if ($data) {
			$response = $this->dbmodel->signWorkRelease($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Статус подписи
	*/
	function getEvnStickSignStatus () {
		$data = $this->ProcessInputData('getEvnStickSignStatus', true);
		if ($data) {
			$response = $this->dbmodel->getEvnStickSignStatus($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Верификация подписи
	*/
	function verifyEvnStickSign () {
		$data = $this->ProcessInputData('verifyEvnStickSign', true);
		if ($data) {
			$response = $this->dbmodel->verifyEvnStickSign($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Обновление статуса подписи
	*/
	function setSignStatus () {
		$data = $this->ProcessInputData('setSignStatus', true);
		if ($data) {
			$response = $this->dbmodel->setSignStatus($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Список версий
	 */
	function loadStickVersionList() {
		$data = $this->ProcessInputData('loadStickVersionList', true);
		if ($data) {
			$response = $this->dbmodel->loadStickVersionList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка ЛВН в ЭМК
	 */
	function loadEvnStickPanel() {
		$data = $this->ProcessInputData('loadEvnStickPanel', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnStickPanel($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
}
}
