<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirection - контроллер для работы с направлениями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       SWAN developers
 * @version      09.12.2011
 * @property EvnDirection_model dbmodel
 * @property EvnDirectionAll_model $EvnDirectionAll_model
 */
class EvnDirection extends swController {
	public $inputRules = array(
		'getEvnDirectionInfo' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Направление',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnDirectionNumber' => array(
			array('field' => 'year', 'label' => 'Год', 'rules' => 'required', 'type' => 'int'),
			//array('field' => 'DirType_id', 'label' => 'Тип направления', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnDirectionData' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Направление',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnDirection' => [
			[
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор случая',
				'rules' => 'required',
				'type' => 'id'
			]
		],
		'saveEvnDirectionPid' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Направление',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Случай',
				'rules' => '',
				'type' => 'id'
			)
		),
		'setPayType' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Направление',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			)
		),
		'includeEvnPrescrInDirection' => array(
			array(
				'field' => 'EvnPrescr_id',
				'label' => 'Назначение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Направление',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'checked',
				'label' => 'Заказанные услуги',
				'rules' => '',
				'type' => 'json_array'
			),
            array(
                'field' => 'order',
                'label' => 'Детали заявки',
                'rules' => 'required',
                'type' => 'string'
            ),
		),
		'includeToDirection' => array(
			array(
				'field' => 'EvnPrescr_id',
				'label' => 'Назначение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Родительское событие',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор добавляемой услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexMedService_pid',
				'label' => 'Идентификатор родительской услуги на службе',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'checked',
				'label' => 'Заказанные услуги',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Направление',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaList',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'string'
				//'type' => 'json_array'
			),
			array(
				'field' => 'checked',
				'label' => 'Заказанные услуги',
				'rules' => '',
				'type' => 'json_array'
			)
		),
		'checkEvnDirectionExists' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Пациент',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Родительское событие',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescr_id',
				'label' => 'Назначение',
				'rules' => '',
				'type' => 'id'
			)
		),
		'checkEQMedStaffFactLink' => array(),
		'loadSMOWorkplaceJournal' => array(
			array('field' => 'OrgSmo_id', 'label' => 'СМО', 'rules' => '', 'type' => 'int'),
			array('field' => 'Person_Surname', 'label' => 'Фамилия', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_Firname', 'label' => 'Имя', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_Secname', 'label' => 'Отчество', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_Birthday', 'label' => 'Дата', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'EvnDirection_setDate', 'label' => 'Дата выписки направления', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'Lpu_did', 'label' => 'МО госпитализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_did', 'label' => 'Профиль', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirection_failDate', 'label' => 'Дата отмены направления', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'DirType_id', 'label' => 'Тип направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'DirFailType_id', 'label' => 'Причина отмены направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_sid', 'label' => 'МО направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_setDate', 'label' => 'Дата госпитализации', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'PrehospType_id', 'label' => 'Тип госпитализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospArrive_id', 'label' => 'Кем доставлен', 'rules' => '', 'type' => 'id'),
			array('field' => 'LeaveType_id', 'label' => 'Исход госпитализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPS_disDate', 'label' => 'Дата окончания случая', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'Over20DaysInQueue', 'label' => 'Признак нахождения в очереди более 20 дней', 'rules' => '', 'type' => 'int'),
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int')
		),
		'getDataEvnDirection'=>array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'EvnDirection_id',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnDirectionForPrint'=>array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			),
			[
				'field' => 'PrescriptionType_id',
				'label' => 'Тип назначения',
				'rules' => '',
				'type' => 'id'
			]

		),
		'loadPathoMorphologyWorkPlace' => array(
			array(
				'field' => 'begDate',
				'label' => 'Начало периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'endDate',
				'label' => 'Окончание периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Search_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Search_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Search_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Search_BirthDay',
				'label' => 'Дата рождения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Место работы врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirectionType_id',
				'label' => 'Тип направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_Ser',
				'label' => 'Серия направления',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_Num',
				'label' => 'Номер направления',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Search_CorpseRecieptDate',
				'label' => 'Дата принятия тела',
				'rules' => 'trim',
				'type' => 'daterange',
				'default' => ''
			),
			array(
				'field' => 'Search_CorpseGiveawayDate',
				'label' => 'Дата выдачи тела',
				'rules' => 'trim',
				'type' => 'daterange',
				'default' => ''
			),
		),
		'loadBaseJournal' => array(
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'loadAddFields','label' => 'Грузить историю направлений с доп. полями (Дата направления, Сотрудник, добавивший запись)','rules' => '','type' => 'int'),
			array('field' => 'beg_date','label' => 'Дата начала периода','rules' => '','type' => 'date'),
			array('field' => 'end_date','label' => 'Дата конца периода','rules' => '','type' => 'date'),
			array('field' => 'RecordDate_from','label' => 'Дата начала периода по записям','rules' => '','type' => 'date'),
			array('field' => 'RecordDate_to','label' => 'Дата конца периода по записям','rules' => '','type' => 'date'),
			array('field' => 'VizitDate_from','label' => 'Дата начала периода по визитам','rules' => '','type' => 'date'),
			array('field' => 'VizitDate_to','label' => 'Дата конца периода по визитам','rules' => '','type' => 'date'),
			array('field' => 'Person_Birthday','label' => 'ДР пациента','rules' => '','type' => 'date'),
			array('field' => 'Person_SurName','label' => 'Фамилия','rules' => 'trim','type' => 'string'),
			array('field' => 'Person_FirName','label' => 'Имя','rules' => 'trim','type' => 'string'),
			array('field' => 'Person_SecName','label' => 'Отчество','rules' => 'trim','type' => 'string'),
			array('field' => 'DirType_id','label' => 'Идентификатор типа направления','rules' => 'trim','type' => 'string'),
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'trim','type' => 'id'),
			array('field' => 'LpuSectionProfile_did','label' => 'Идентификатор профиля','rules' => 'trim','type' => 'id'),
			array('field' => 'LpuUnit_did','label' => 'Тип группы отделения','rules' => 'trim','type' => 'id'),
			array('field' => 'IsConfirmed','label' => 'Госпитализация подтверждена ','rules' => 'trim','type' => 'id'),
			array('field' => 'IsHospitalized','label' => 'Пациент госпитализирован ','rules' => 'trim','type' => 'id'),
			array('field' => 'SearchType','label' => 'Тип поиска','rules' => '','type' => 'string'),
			array('field' => 'useCase','label' => 'Вариант поиска','rules' => '','type' => 'string'),
			array('field' => 'winType','label' => 'Тип формы','rules' => '','type' => 'string'),
			array('field' => 'dateRangeMode','label' => 'Режим фильтра по периоду','rules' => '','type' => 'string'),
			array('field' => 'MedPersonal_did','label' => '','rules' => '','type' => 'id'),
			array('field' => 'MedService_did','label' => '','rules' => '','type' => 'id'),
			array('field' => 'LpuSection_did','label' => '','rules' => '','type' => 'id'),
			array('field' => 'UslugaComplex_id','label' => 'Идентификатор услуги','rules' => '','type' => 'id'),

			array('field' => 'Lpu_did','label' => '','rules' => '','type' => 'id'),
			array('field' => 'MedPersonal_sid','label' => '','rules' => '','type' => 'id'),
			array('field' => 'LpuSection_sid','label' => '','rules' => '','type' => 'id'),
			array('field' => 'Lpu_sid','label' => '','rules' => '','type' => 'id'),
			array('field' => 'Org_sid','label' => '','rules' => '','type' => 'id'),
			
			array('field' => 'PayType_id','label' => '','rules' => '','type' => 'id'),
			
			array('field' => 'Diag_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'KlDistrict_sid','label' => 'Округ','rules' => '','type' => 'id'),
			array('field' => 'EvnStatus_id','label' => '','rules' => '','type' => 'string'),
			array('field' => 'EvnDirection_IsAuto','label' => '','rules' => '','type' => 'id'),
			array('field' => 'EvnDirection_Num','label' => 'Номер направления','rules' => '','type' => 'id'),
			array('field' => 'pmUser_id','label' => 'Пользователь','rules' => '','type' => 'int'),
			array('field' => 'onlySQL','label' => 'Вывести SQL-запрос','rules' => 'ban_percent','type' => 'string','default' => 'off'),
			array('field' => 'eQueueOnly', 'label' => 'Только по электронным направлениям', 'rules' => '', 'type' => 'string'),
			array('field' => 'Referral_id', 'label' => 'Передано в БГ', 'rules' => '', 'type' => 'int'),
			array('field' => 'onlyWaitingList', 'label' => 'Только листы ожидания', 'rules' => '', 'type' => 'checkbox')
		),
		'loadTimetableRecords' => array(
			array(
				'field' => 'onlyCallCenterUsers',
				'label' => 'Только пользователи call-центра',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'RecordDate_from',
				'label' => 'Дата записи с',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'RecordDate_to',
				'label' => 'Дата записи по',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'VizitDate_from',
				'label' => 'Дата визита с',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'VizitDate_to',
				'label' => 'Дата визита по',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'RecLpu_id',
				'label' => 'МО записи',
				'rules' => '',
				'type' => 'id'
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
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Пользователь',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnDirection_Num',
				'label' => 'Номер направления',
				'rules' => '',
				'type' => 'int'
			)
		),
		'deleteEvnDirection' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnDirection' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrintResearchDirections',
				'label' => 'Флаг печати исследований',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PrintMnemonikaDirections',
				'label' => 'Флаг печати мнемоник в тестах',
				'rules' => '',
				'type' => 'int'
			),
			[
				'field' => 'PrescriptionType_id',
				'label' => 'Тип назначения',
				'rules' => '',
				'type' => 'id'
			]
		),
		'loadEvnDirectionGrid' => array(
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'includeDeleted','label' => 'Включая удаленные','rules' => '','type' => 'int'),
			array('field' => 'EvnDirection_pid','label' => 'Идентификатор родительского события','rules' => 'required','type' => 'id')
		),
		'loadHospDirectionGrid' => array(
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('field' => 'beg_date','label' => 'Дата начала периода','rules' => 'required','type' => 'date'),
			array('field' => 'end_date','label' => 'Дата конца периода','rules' => 'required','type' => 'date'),
			array('field' => 'Person_BirthDay','label' => 'ДР пациента','rules' => '','type' => 'date'),
			array('field' => 'Person_Fio','label' => 'ФИО пациента','rules' => 'trim','type' => 'string'),
			array('field' => 'DirType_id','label' => 'Идентификатор типа направления','rules' => 'trim','type' => 'id'),
			array('field' => 'LpuSectionProfile_id','label' => 'Идентификатор профиля','rules' => 'trim','type' => 'id'),
			array('field' => 'IsConfirmed','label' => 'Госпитализация подтверждена ','rules' => 'trim','type' => 'id'),
			array('field' => 'IsHospitalized','label' => 'Пациент госпитализирован ','rules' => 'trim','type' => 'id')
		),
		'loadEvnDirectionJournal' => array(
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('field' => 'beg_date','label' => 'Дата начала периода','rules' => 'required','type' => 'date'),
			array('field' => 'end_date','label' => 'Дата конца периода','rules' => 'required','type' => 'date'),
			array('field' => 'Person_BirthDay','label' => 'ДР пациента','rules' => '','type' => 'date'),
			array('field' => 'Person_SurName','label' => 'Фамилия пациента','rules' => 'ban_percent|trim','type' => 'russtring'),
			array('field' => 'Person_FirName','label' => 'Имя пациента','rules' => 'ban_percent|trim','type' => 'russtring'),
			array('field' => 'Person_INN','label' => 'ИИН','rules' => 'ban_percent|trim','type' => 'string'),
			array('field' => 'Person_SecName','label' => 'Отчество пациента','rules' => 'ban_percent|trim','type' => 'russtring'),
			array('field' => 'DirType_id','label' => 'Идентификатор типа направления','rules' => 'trim','type' => 'id'),
			array('field' => 'LpuSectionProfile_id','label' => 'Идентификатор профиля','rules' => 'trim','type' => 'id'),
			array('field' => 'IsConfirmed','label' => 'Госпитализация подтверждена ','rules' => 'trim','type' => 'id'),
			array('field' => 'isCanceled','label' => 'Направление отменено','rules' => '','type' => 'boolean'),
			array('field' => 'Diag_id','label' => 'Идентификатор диагноза','rules' => 'trim','type' => 'id'),
			array('field' => 'MedPersonalProfile_id','label' => 'Идентификатор профиля направившего врача','rules' => 'trim','type' => 'id'),
			array('field' => 'Lpu_sid','label' => 'Идентификатор направившей МО','rules' => 'trim','type' => 'id'),
			array('field' => 'EvnStatusCause_id', 'label' => 'Причина отмены направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrehospStatus_id','label' => 'Статус госпитализации','rules' => 'trim','type' => 'id'),
			array('field' => 'EvnPS_setDate','label' => 'Дата госпитализации','rules' => '','type' => 'date'),
			array('field' => 'LeaveType_id','label' => 'Исход госпитализации','rules' => 'trim','type' => 'id'),
			array('field' => 'PrehospWaifRefuseCause_id','label' => 'Отказ','rules' => 'trim','type' => 'id')
		),
		'loadEvnDirectionPanel' => array(
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DopDispInfoConsent_id',
				'label' => 'Идентификатор согласия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirType',
				'label' => 'Типы направлений',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadEvnDirectionPanel_EvnPLDispDop' => array(
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DopDispInfoConsent_id',
				'label' => 'Идентификатор согласия',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnDirectionList' => array(
			array(
				'field' => 'useCase',
				'label' => 'Вариант использования',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'TimetableGraf_id',
				'label' => 'Идентификатор бирки',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор ТАП/КВС',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'onDate',
				'label' => 'Дата',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'parentClass',
				'label' => 'Тип формы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор рабочего места',
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
				'field' => 'formType',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DirType_id',
				'label' => 'Тип направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'Идентификатор',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Идентификатор',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_did',
				'label' => 'Идентификатор',
				'rules' => 'trim',
				'type' => 'id'
			),
		),
		'checkRecordByPerson' => array(
			
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор рабочего места',
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
				'field' => 'formType',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'string'
			)
		),
		'checkOnkoDiagforDiagnosisResult' => array(
			array('field' => 'Diag_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'EvnDirection_setDate', 'label' => 'Дата выписки направления', 'rules' => 'trim', 'type' => 'date')
		),
		'loadPagedGrid' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор объекта высшего уровня',
				'rules' => '',
				'type' => 'id'
			),
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
			)
		),
		'loadEvnDirectionEditForm' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Посещение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableGraf_id',
				'label' => 'Расписание',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirType_id',
				'label' => 'Тип направления',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveEvnDirectionAuto' => array(
			array( 'field' => 'EvnQueue_id','label' => 'Идентификатор записи в очереди', 'rules' => 'required', 'type' => 'id' )
		),
		'getEvnDirectionCommitList'=>array(
			array('field'=>'Person_id','label'=>'Идентификатор пользователя','rules'=>'required','type'=>'id'),
			array('field'=>'LpuSection_id','label'=>'Идентификатор отделения','rules'=>'required','type'=>'id')
		),
		'saveEvnDirection' => array(
			array(
				'field' => 'redirectEvnDirection',
				'label' => 'Признак перенаправления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OuterKzDirection',
				'label' => 'Признак внешнего направления для Казахстана',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentType_id',
				'label' => 'Тип предстоящего лечения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_sid',
				'label' => 'Направившая МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_IsNeedOper',
				'label' => 'Необходимость операционного вмешательства',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'DirType_id',
				'label' => 'Тип направления',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_Code',
				'label' => 'Код врача',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedicalCareFormType_id',
				'label' => 'Форма помощи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StudyTarget_id',
				'label' => 'Цель исследования',
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
				'field' => 'ConsultingForm_id',
				'label' => 'Формы оказания консультации ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'МО куда направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_oid',
				'label' => 'Организация направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_Descr',
				'label' => 'Обоснование',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Служба ',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableMedService_id',
				'label' => 'Бирка раписания службы',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_did',
				'label' => 'Услуга',
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
				'field' => 'RemoteConsultCause_id',
				'label' => 'Цель консультирования ',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_IsAuto',
				'label' => 'Признак является ли направление системным Да/нет ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrescriptionType_Code',
				'label' => 'Идентификатор ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescr_id',
				'label' => 'Идентификатор ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_Num',
				'label' => 'Номер направления',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_setDate',
				'label' => 'Дата выписки направления',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirection_desDT',
				'label' => 'Желаемая дата направления',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirection_setDateTime',
				'label' => 'Время',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение, которое направило',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_did',
				'label' => 'Отделение куда направили',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_did',
				'label' => 'Условия оказания медицинской помощи',
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
				// кто направил, для записи должности врача
				'field' => 'From_MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_zid',
				'label' => 'Зав. отделением',
				'rules' => 'trim',
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
				'field' => 'TimetablePar_id',
				'label' => 'Идентификатор бирки поликлиники',
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
			array( 'field' => 'EvnQueue_id','label' => 'Идентификатор записи в очереди', 'rules' => '', 'type' => 'id' ),
			array( 'field' => 'QueueFailCause_id','label' => 'Причина отмены направления из очереди', 'rules' => '', 'type' => 'id' ),
			array(
				'field' => 'EvnUsluga_id',
				'label' => 'Сохраненный заказ',
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
				'field' => 'toQueue',
				'label' => 'Флаг одновременной постановки в очередь',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_IsCito',
				'label' => 'Cito',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_IsReceive',
				'label' => 'К себе',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ConsultationForm_id',
				'label' => 'Форма оказания консультации',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'GetBed_id',
				'label' => 'Профиль койки',
				'rules' => '',
				'type'  => 'id'
			),
			array('field' => 'IgnoreCheckHospitalOffice', 'label' => 'Игнорировать проверку', 'rules' => '', 'type'  => 'id'),
			array('field' => 'EvnLinkAPP_StageRecovery', 'label' => 'Тип оплаты', 'rules' => '', 'type'  => 'id'),
			array('field' => 'PurposeHospital_id', 'label' => 'Тип оплаты', 'rules' => '', 'type'  => 'id'),
			array('field' => 'ReasonHospital_id', 'label' => 'Тип оплаты', 'rules' => '', 'type'  => 'id'),
			array('field' => 'Diag_cid', 'label' => 'Тип оплаты', 'rules' => '', 'type'  => 'id'),
			array('field' => 'PayTypeKAZ_id', 'label' => 'Тип оплаты', 'rules' => '', 'type'  => 'id'),
			array('field' => 'ScreenType_id', 'label' => 'Вид скрининга', 'rules' => '', 'type'  => 'id'),
			array('field' => 'TreatmentClass_id', 'label' => 'Повод обращения', 'rules' => '', 'type'  => 'id'),
			array('field' => 'CVIConsultRKC_id', 'label' => 'Необходимось консультации в РКЦ', 'rules' => '', 'type'  => 'id'),
			array('field' => 'RepositoryObserv_sid', 'label' => 'Наблюдение где возникла необходимось консультации в РКЦ', 'rules' => '', 'type'  => 'id'),
			array('field' => 'isRKC', 'label' => 'Наблюдение где возникла необходимось консультации в РКЦ', 'rules' => '', 'type'  => 'boolean')
		),
		'loadEvnDirectionViewForm' => array(
			array(
					'field' => 'EvnDirection_id',
					'label' => 'Идентификатор электронного направления',
					'rules' => 'required',
					'type' => 'id'
				)
		),
		'getDirectionIf' => array(
			array(
				'field' => 'TimetableGraf_id',
				'label' => 'Расписание',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Место работы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'setDate',
				'label' => 'Дата',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Посещение',
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
				'field' => 'EvnPL_id',
				'label' => 'Талон',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getDirectionStomIf' => array(
			array(
				'field' => 'TimetableGraf_id',
				'label' => 'Расписание',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Место работы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'setDate',
				'label' => 'Дата',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnVizitPLStom_id',
				'label' => 'Посещение',
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
				'field' => 'EvnPLStom_id',
				'label' => 'Талон',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getEvnDirection_id' => array(
			array(
				'field' => 'TimetableGraf_id',
				'label' => 'Расписание',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'setDate',
				'label' => 'Дата',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Место работы',
				'rules' => '',
				'type' => 'id'
			)
		),
		'countEvnVizitPL' => array(
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Место работы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
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
				'field' => 'setDate',
				'label' => 'Дата',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Посещение',
				'rules' => '',
				'type' => 'id'
			)
		),
		'setConfirmed' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required|trim',
				'type' => 'id'
			),
			array(
				'field' => 'Hospitalisation_setDT',
				'label' => 'Дата и время подтверждения',
				'rules' => 'required|trim',
				'type' => 'datetime'
			)
		),
		'getEvnDirectionList' => array(
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('field' => 'beg_date','label' => 'Дата начала периода','rules' => 'required','type' => 'date'),
			array('field' => 'end_date','label' => 'Дата конца периода','rules' => 'required','type' => 'date'),
			array('field' => 'Person_BirthDay','label' => 'ДР пациента','rules' => '','type' => 'date'),
			array('field' => 'Person_Fio','label' => 'ФИО пациента','rules' => 'trim','type' => 'string'),
			array('field' => 'DirType_id','label' => 'Идентификатор типа направления','rules' => 'trim','type' => 'id'),
			array('field' => 'LpuSectionProfile_id','label' => 'Идентификатор профиля','rules' => 'trim','type' => 'id'),
			array('field' => 'IsConfirmed','label' => 'Госпитализация подтверждена ','rules' => 'trim','type' => 'id'),
			array('field' => 'IsHospitalized','label' => 'Пациент госпитализирован ','rules' => 'trim','type' => 'id'),
			array('field' => 'PrehospStatus_id','label' => 'Статус госпитализации','rules' => 'trim','type' => 'id')
		),
		'loadDirectionDataForLeave' => array(
			array(
				'field' => 'EvnDirection_rid',
				'label' => 'Идентификатор случая лечения',
				'rules' => 'required|trim',
				'type' => 'id'
			),
			array(
				'field' => 'rootEvnClass_SysNick',
				'label' => 'Класс случая лечения',
				'rules' => 'trim',
				'type' => 'string',
				'default' => 'EvnPS',
			)
		),
		'getInfoEvnDirectionfromBg' => array (
			array('field' => 'id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id')
		),
		'loadDirectionDataForZNO' => array(
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Идентификатор случая посещения',
				'rules' => 'required|trim',
				'type' => 'id'
			),
			array(
				'field' => 'typeofdirection',
				'label' => 'Тип направления',
				'rules' => 'trim',
				'type' => 'string',
				'default' => 'all'
			)
		),
		'getLinkedXmlForEvnDirection' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required|trim',
				'type' => 'id'
			),
		),
	);

	private $moduleMethods = [
		'cancel'
	];
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->init();
	}

	/**
	 * Дополнительная инициализация
	 */
	private function init(){
		$dirType = isset($_REQUEST['DirType_id']) ? $_REQUEST['DirType_id'] : null;
		$method = $this->router->fetch_method();

		if (!$this->usePostgreLis || !in_array($method, $this->moduleMethods) || $dirType !== '10') {
			$this->load->database();
			$this->load->model('EvnDirection_model', 'dbmodel');
		}
	}

	/**
	* Автоматическое создание эл.направления
	* Входящие данные: EvnQueue_id
	* На выходе: JSON-строка
	* Используется: АРМ приемного отделения
	*/
	function saveEvnDirectionAuto() {
		$data = $this->ProcessInputData('saveEvnDirectionAuto', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvnDirectionAuto($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}

	/**
	* Получение данных по направлению для формы отмены направления
	*/
	function getEvnDirectionInfo() {
		$data = $this->ProcessInputData('getEvnDirectionInfo', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getEvnDirectionInfo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных по направлению для новой EMK
	 */
	function getEvnDirection() {
		$data = $this->ProcessInputData('getEvnDirection', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getEvnDirection($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	* Сохранение ссылки на случай
	*/
	function saveEvnDirectionPid() {
		$data = $this->ProcessInputData('saveEvnDirectionPid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvnDirectionPid($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения связи направления со случаем')->ReturnData();

		return true;
	}

	/**
	* Получение данные направления
	*/
	function getEvnDirectionData() {
		$data = $this->ProcessInputData('getEvnDirectionData', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getEvnDirectionData($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения данных направления')->ReturnData();

		return true;
	}

	/**
	* Сохранение вида оплаты направлений
	*/
	function setPayType() {
		$data = $this->ProcessInputData('setPayType', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->setPayType($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	* Подтверждает госпитализацию по направлению.
	* Входящие данные: EvnDirection_id, Hospitalisation_setDT
	* Используется форма подтверждения госпитализации swHospDirectionConfirmWindow.js
	*/
	function setConfirmed() {
		$data = $this->ProcessInputData('setConfirmed', true, true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->setConfirmed($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	*  Удаление направления
	*  Входящие данные: $_POST['EvnDirection_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования посещения
	*/
	function deleteEvnDirection() {
		$data = $this->ProcessInputData('deleteEvnDirection', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteEvnDirection($data);
		$this->ProcessModelSave($response, true, 'При удалении направления возникли ошибки')->ReturnData();

		return true;
	}


	/**
	*  Получение номера направления
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления
	*/
	public function getEvnDirectionNumber() {
		$data = $this->ProcessInputData('getEvnDirectionNumber', true, true);
		if ( $data === false ) { return false; }

		if ( $data['Lpu_id'] == 0 ) {
			$this->ReturnData(array('success' => false));
			return true;
		}

		$response = $this->dbmodel->getEvnDirectionNumber($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения номера направления')->ReturnData();
		
		return true;
	}


	/**
	*  Получение списка направлений на госпитализацию
	*  На выходе: JSON-строка
	*  Используется: форма журнала направлений на госпитализацию
	*/
	function loadHospDirectionGrid() {
		$data = $this->ProcessInputData('loadHospDirectionGrid', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadHospDirectionGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}


	/**
	*  Получение списка направлений на госпитализацию для журнала в АРМ СМО/ТФОМС
	*  На выходе: JSON-строка
	*  Используется: журнала в АРМ СМО и АРМ ТФОМС
	*/
	function loadSMOWorkplaceJournal() {
		$data = $this->ProcessInputData('loadSMOWorkplaceJournal', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadSMOWorkplaceJournal($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 * Включение назначения в существующее направление
	 */
	function includeEvnPrescrInDirection() {
		$data = $this->ProcessInputData('includeEvnPrescrInDirection', true, true);
		if ( $data === false ) { return false; }

        if ($this->usePostgreLis) {
            $this->load->swapi('lis');
            $response = $this->lis->GET('EvnDirection/includeEvnPrescrInDirection', $data, 'single');

            if (!$this->isSuccessful($response)) {
                return $response;
            }
        } else {
            $response = $this->dbmodel->includeEvnPrescrInDirection($data);
        }

		$this->ProcessModelSave($response, true, "Ошибка включения назначения в существующее направление")->ReturnData();

		return true;
	}


	/**
	*  Проверка наличия направления в ту же службу
	*/
	function checkEvnDirectionExists() {
		$data = $this->ProcessInputData('checkEvnDirectionExists', true, true);
		if ( $data === false ) { return false; }

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$response = $this->lis->GET('EvnDirection/EvnDirectionExists', $data, 'single');

			if (!$this->isSuccessful($response)) {
				return $response;
			}
		} else {
			$response = $this->dbmodel->checkEvnDirectionExists($data);
		}

		$this->ProcessModelSave($response, true, "Ошибка проверки наличия направления")->ReturnData();

		return true;
	}


	/**
	*  Получение списка направлений для журнала направлений
	*  На выходе: JSON-строка
	*  Используется: форма журнала направлений
	*/
	function loadEvnDirectionJournal() {
		$archive_database_enable = $this->config->item('archive_database_enable');
		// если по архивным - используем архивную БД
		if (!empty($archive_database_enable) && !empty($_REQUEST['useArchive'])) {
			$this->db = null;
			$this->load->database('archive', false);
		}

		$data = $this->ProcessInputData('loadEvnDirectionJournal', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionJournal($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	*  Получение списка направлений
	*  Входящие данные: $_POST['EvnDirection_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования посещения
	*/
	function loadEvnDirectionGrid() {
		$data = $this->ProcessInputData('loadEvnDirectionGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}


	/**
	*  Получение списка направлений
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма выбора направления
	*/
	function loadEvnDirectionList() {
		$data = $this->ProcessInputData('loadEvnDirectionList', true, true);
		if ( $data === false ) { return false; }
		if (!empty($data['useCase'])) {
			$this->load->model('EvnDirectionAll_model');
			$response = $this->EvnDirectionAll_model->loadEvnDirectionList($data);
			
			if (getRegionNick() == 'kz') {
				$this->load->model('ExchangeBL_model');
				
				try {
					$response = array_merge($response, $this->ExchangeBL_model->getRefferalByPerson($data));
				} catch (Exception $e) {
					$response = array_merge($response);
				}
			}
			
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		$response = $this->dbmodel->loadEvnDirectionList($data);
		
		if (getRegionNick() == 'kz') {
			$this->load->model('ExchangeBL_model');
			
			try {
				$response = array_merge($response, $this->ExchangeBL_model->getRefferalByPerson($data));
			} catch (Exception $e) {
				$response = array_merge($response);
			}
		} 
		
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 *  Получение списка направлений для панели направлений в ЭМК
	 */
	function loadEvnDirectionPanel() {
		$data = $this->ProcessInputData('loadEvnDirectionPanel', true, true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadEvnDirectionPanel($data);
		if ($this->usePostgreLis && !empty($data['DirType']) && $data['DirType'] === 'swGridEvnDirectionCommon') {
			$this->load->swapi('lis');
			// Подгрузим также направления на лабораторную диагностику
			$responsePG = $this->lis->GET('EvnDirection/loadEvnDirectionPanel', $data, 'list');
			
			if (!empty($responsePG) && is_array($responsePG)) {
				//во избежание дублирующих записей по направлениям
				$ids = [];
				foreach ($response as $resp) {
					if (!in_array($resp['EvnDirection_id'], $ids)) {
						$ids[] = $resp['EvnDirection_id'];
					}
				}
				foreach ($responsePG as $resp) {
					if (!in_array($resp['EvnDirection_id'], $ids)) {
						$ids[] = $resp['EvnDirection_id'];
						array_push($response, $resp);
					}
				}
			};
		}

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 *  Получение списка направлений для ДВН
	 */
	function loadEvnDirectionPanel_EvnPLDispDop() {
		$data = $this->ProcessInputData('loadEvnDirectionPanel_EvnPLDispDop', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionPanel_EvnPLDispDop($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *
	 * @return type 
	 */
	function checkRecordByPerson() {
		$data = $this->ProcessInputData('checkRecordByPerson', true, true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->checkRecordByPerson($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	

	/**
	 * Загрузка списка ЛПУ для окна выбора бирки
	 */
	function loadLpuGrid() {
		$data = $this->ProcessInputData('loadPagedGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadLpuGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 * Загрузка списка зданий ЛПУ для окна выбора бирки
	 */
	function loadLpuBuildingGrid() {
		$data = $this->ProcessInputData('loadPagedGrid', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadLpuBuildingGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 * Загрузка списка групп отделений ЛПУ для окна выбора бирки
	 */
	function loadLpuUnitGrid() {
		$data = $this->ProcessInputData('loadPagedGrid', true);
		if ( $data === false ) { return false; }
	
		$response = $this->dbmodel->loadLpuUnitGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 * Загрузка списка врачей для окна выбора бирки
	 */
	function loadMedStaffFactGrid() {
		$data = $this->ProcessInputData('loadPagedGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadMedStaffFactGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	*  Сохранение направления
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления
	*/
	function saveEvnDirection() {
		$data = $this->ProcessInputData('saveEvnDirection', true);
		if ( $data === false ) { return false; }

		if (empty($data['LpuSectionProfile_id']) && $data['DirType_id'] != 9 && $data['DirType_id'] != 26 && !in_array(getRegionNick(), array('astra', 'ekb')) && empty($data['OuterKzDirection'])) { // для ВК профиль не обязателен (refs #83337)
			$this->ReturnError('Поле "Профиль" обязательно для заполнения');
			return false;
		}
		
		if (empty($data['ConsultingForm_id']) && getRegionNick() == 'ufa' && (17 == $data['DirType_id']) ) {
			$this->ReturnError('Поле "Формы оказания консультации" обязательно для заполнения');
			return false;
		}

		if (empty($data['Lpu_did']) && empty($data['Org_oid']) && $data['DirType_id'] != 26 && empty($data['OuterKzDirection'])) {
			$this->ReturnError('Поле "МО куда направлен" обязательно для заполнения');
			return false;
		}
		
		/*if (empty($data['MedPersonal_Code']) && in_array($data['DirType_id'], array(1,5,10,16)) && getRegionNick() == 'ekb') { // (refs #110233)
			$this->ReturnError('Поле "Код врача" обязательно для заполнения');
			return false;
		}*/

		if (empty($data['Diag_id']) && $data['EvnDirection_IsReceive'] != 2 && $data['DirType_id'] != 9 && empty($data['OuterKzDirection'])) {
			$this->ReturnError('Поле "Диагноз" обязательно для заполнения');
			return false;
		}

		if (2 == $data['EvnDirection_IsReceive'] && 17 == $data['DirType_id']) {
			if (empty($data['Diag_id'])) {
				$this->ReturnError('Поле Диагноз обязательно для заполнения.');
				return false;
			}
			if (empty($data['MedService_id']) && !empty($data['MedService_isEnabled'])) {
				$this->ReturnError('Поле Служба обязательно для заполнения.');
				return false;
			}
			if (empty($data['RemoteConsultCause_id'])) {
				$this->ReturnError('Поле Цель консультации обязательно для заполнения.');
				return false;
			}
		} else {
			if (2 != $data['EvnDirection_IsAuto'] && empty($data['From_MedStaffFact_id']) && empty($data['OuterKzDirection'])) {
				$this->ReturnError('Поле Рабочее место врача обязательно для заполнения.');
				return false;
			}
			if (empty($data['MedPersonal_id']) && empty($data['OuterKzDirection'])) {
				$this->ReturnError('Поле Врач обязательно для заполнения.');
				return false;
			}
			if (2 != $data['EvnDirection_IsAuto'] && empty($data['MedPersonal_zid']) && $data['Lpu_did'] != $data['Lpu_sid'] && empty($data['OuterKzDirection'])) {
				$this->ReturnError('Поле Зав. отделением обязательно для заполнения.');
				return false;
			}
		}

		$response = $this->dbmodel->saveEvnDirection($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении направления')->ReturnData();
		
		return true;
	}
	
	
	/**
	*  Получение данных по направлению
	*  Входящие данные: $_POST['EvnDirection_id']
	*  На выходе: JSON-строка
	*  Используется: форма просмотра направления
	*/
	function loadEvnDirectionEditForm() {
		$data = $this->ProcessInputData('loadEvnDirectionEditForm', true);

		if ( $data === false ) { return false; }

		if ($this->usePostgreLis) {
			$response = $this->dbmodel->loadEvnDirectionEditForm($data);
			if (empty($response) || !is_array($response)) {
				$this->load->swapi('lis');
				$response = $this->lis->GET('EvnDirection/loadEDEditForm', $data, 'single');
				if ($this->isSuccessful($response)) {
					$addition = $this->dbmodel->additionForEDEditFormPostgre($response);
					if (isset($addition[0])) {
						$response = array_merge($response, $addition[0]);
					}
				}

				$response = [$response];
			}
		} else {
			$response = $this->dbmodel->loadEvnDirectionEditForm($data);
		}
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	
	/**
	 * Загрузка информации о направлении
	 */
	function getDirectionIf()
	{
		$data = $this->ProcessInputData('getDirectionIf', true);
		if ( $data === false ) { return false; }
		
		// Если посещение не новое (открыто на редактирование), то просто читаем направление 
		if ($data['EvnPL_id']>0)
		{
			$response = $this->dbmodel->getDirectionEvnPLIf($data);
		}
		else if ($data['EvnDirection_id']>0)
		{
			$response = $this->dbmodel->loadEvnDirectionFull($data);
		}
		else if ($data['TimetableGraf_id']>0)
		{
			$data['EvnDirection_IsAuto'] = 1;
			$response = $this->dbmodel->loadEvnDirectionFull($data);
		}
		else 
		{
			$response = $this->dbmodel->getDirectionIf($data);
		}
		
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	
	/**
	 * Загрузка информации о направлении для стоматологических талонов
	 */
	function getDirectionStomIf()
	{
		$data = $this->ProcessInputData('getDirectionStomIf', true);
		if ( $data === false ) { return false; }
		
		// Если посещение не новое (открыто на редактирование), то просто читаем направление 
		if ($data['EvnPLStom_id']>0)
		{
			$response = $this->dbmodel->getDirectionEvnPLStomIf($data);
		}
		elseif ($data['EvnDirection_id']>0)
		{
			$response = $this->dbmodel->loadEvnDirectionStomFull($data);
		}
		else 
		{
			$response = $this->dbmodel->getDirectionStomIf($data);
		}
		
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	*  Печать электронного направления
	*  Входящие данные: $_GET['EvnDirection_id']
	*  На выходе: форма для печати ЛВН
	*  Используется: форма поиска ЛВН
	*                форма редактирования ЛВН
	*/
	function printEvnDirection() {
		$this->load->library('parser');
		
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
			12 => "декабря",
		);

		$data = $this->ProcessInputData('printEvnDirection', true);
		if ( $data === false ) { return false; }

		if (empty($data['PrescriptionType_id'])) {
			$data['PrescriptionType_id'] = $this->dbmodel->getPrescriptionTypeByEvnDirection($data);
		}
		// Получаем данные по направлению
		if ($this->usePostgreLis && (empty($data['PrescriptionType_id']) || ($data['PrescriptionType_id'] && $data['PrescriptionType_id'] == 11))) {
			$this->load->swapi('lis');
			$res = $this->lis->GET('EvnDirection/Fields', $data, 'single');
			if (!$this->isSuccessful($res))
				return $res;

			if (!empty($res)) {
				//получение доп. полей, которых нет в БД ЛИС
				$response = $this->dbmodel->getEvnDirectionFieldsForPostge($res);
			} else {
				//в pg данные не нашлись, ищем в ms
				$response = $this->dbmodel->getEvnDirectionFields($data);
			}
		} else {
			$response = $this->dbmodel->getEvnDirectionFields($data);
		}

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по направлению';
			return false;
		}
		
		$print_data = $response;
		$print_data['EvnDirection_Num'] = str_pad($print_data['EvnDirection_Num'], 6, "0", STR_PAD_LEFT);
		$print_data['Lpu_f003mcod_last4'] = substr($print_data['Lpu_f003mcod'], -4);
		/*
		if(getRegionNick() == 'kareliya'){
			//Карелия. В формате «KKKKKKГГГГхххххх», где KKKKKK – код МО, ГГГГ – текущий год, хххххх – порядковый номер внутри МО
			$print_data['EvnDirection_Num'] = $print_data['Lpu_f003mcod'].date('Y').$print_data['EvnDirection_Num'];
		}
		 */
		$dirstring = "";
		$dirstring .= ( $print_data['DirType_id'] == 1 || $print_data['DirType_id'] == 5 ) ? "<u>на госпитализацию</u>," : "на госпитализацию,";
		$dirstring .= ( $print_data['DirType_id'] == 4 ) ? " <u>восстановительное лечение</u>," : " восстановительное лечение,";
		$dirstring .= ( $print_data['DirType_id'] == 2 ) ? " <u>обследование</u>," : " обследование,";
		$dirstring .= ( in_array($print_data['DirType_id'], array(3, 11)) ) ? " <u>консультацию</u>," : " консультацию,";
		$dirstring .= ( $print_data['DirType_id'] == 6 ) ? " <u>осмотр с целью госпитализации,</u>" : " осмотр с целью госпитализации,";
		$dirstring .= ( $print_data['DirType_id'] == 10 ) ? " <u>исследование</u>," : " исследование,";
		$dirstring .= ( $print_data['DirType_id'] == 9) ? " <u>на врачебную комиссию,</u> " : " на врачебную комиссию,";
		$dirstring .= ( in_array($print_data['DirType_id'], array(15, 20))) ? " <u>на процедуру</u> " : " на процедуру";
		if ($print_data['DirType_id'] == 26) $dirstring = "в органы социальной защиты";
		$print_data['dirstring'] = $dirstring;

		// Определяем, печатать направления на исследования или нет, и нужна ли мнемоника у тестов
		$printResearches = ( $data['PrintResearchDirections'] != null );
		$printWithMnemonika = ( $data['PrintMnemonikaDirections'] != null );

		$printResearches = $printResearches || $printWithMnemonika;

		$print_data['printResearches'] = $printResearches;
		$print_data['printWithMnemonika'] = $printWithMnemonika;


		if ( $printResearches === true ) {

			$lab_services = $print_data['Usluga_Name'];

			if (!empty($lab_services) && count($lab_services) > 0) {

				foreach ($lab_services as $complex_key => $lab_service) {

					//т.к. приходят строки с пробелами
					$lab_service = trim($lab_service);

					if (!empty($lab_service)) {

						//разбиваем услугу на код и название
						list($lab_svc_code, $lab_svc_title) = explode(' ', $lab_service, 2);

						$print_data['lab_services'][$complex_key]['service_code'] = $lab_svc_code;
						$print_data['lab_services'][$complex_key]['service_name'] = $lab_svc_title;

						//присваиваем саб услуги
						if (isset($print_data['SubServices']) && isset($print_data['SubServices'][$complex_key])) {

							$print_data['lab_services'][$complex_key]['subservices'] = $print_data['SubServices'][$complex_key];


							// Если установлена настройка печати тестов с мнемоникой, то проверяем, у всех ли тестов она есть
							if ( $printWithMnemonika === true )
							{
								// по умолчанию флаг печати мнемоник рамках конкретного исследования равен true
								$print_data['lab_services'][$complex_key]['mnemonika'] = true;

								// Смотрим все тесты в рамках одного исследования (услуги)
								foreach ($print_data['SubServices'][$complex_key] as $test)
								{

									// Если хотя бы у одного теста нет мнемоники, то для этого исследования не печатаем мнемоники
									if ( empty ($test['AnalyzerTest_SysNick']) )
									{
										// не печатаем мнемоники, простая печать исследований
										$print_data['lab_services'][$complex_key]['mnemonika'] = false;
										break 1;
									}

								}
							} else
							{
								// Если опция печати мнемоники отключена
								$print_data['lab_services'][$complex_key]['mnemonika'] = false;
							}


						} else
						{
							$print_data['lab_services'][$complex_key]['subservices'] = array();
						}

					}
				}
			}
		}

		If ($print_data['DirType_id'] != 5)
			$HospType = 1;
		else
			$HospType = 2; 
		$hospstring = "";
		$hospstring .= ( $HospType == 1 ) ? " <u>плановая</u>," : " плановая,";
		$hospstring .= ( $HospType == 2 ) ? " <u>экстренная</u>" : " экстренная";
		if (!( $print_data['DirType_id'] == 1 || $print_data['DirType_id'] == 5 ))
            $hospstring = "плановая, экстренная";
		$print_data['hospstring'] = $hospstring;
		
		$MedicalCareFormType = "плановая, неотложная";
		if($print_data['MedicalCareFormType_Code'] == 3){
			$MedicalCareFormType = "<u>плановая</u>, неотложная";
		}elseif ($print_data['MedicalCareFormType_Code'] == 2) {
			$MedicalCareFormType = "плановая, <u>неотложная</u>";		}
		$print_data['MedicalCareFormType'] = $MedicalCareFormType;
		
		if ( isset($print_data['SectionContact_Phone']) && trim($print_data['SectionContact_Phone']) != '' ){
			$print_data['Сontact_Phone'] = "Контактные телефоны : {$print_data['SectionContact_Phone']}";
		} else if(isset($print_data['Contact_Phone']) && trim($print_data['Contact_Phone']) != ''){
			$print_data['Сontact_Phone'] = "Контактные телефоны : {$print_data['Contact_Phone']}";
		} else {
			$print_data['Сontact_Phone'] = "Контактные телефоны : {$print_data['Lpu_Phone']}";
		}

		$print_data['RecMP'] .= "&nbsp;";

        /*if ($print_data['MedPersonal_did'] == '') {
            $print_data['RecDate'] = $print_data['RecDate']."&nbsp;";
        } else {
            $print_data['RecDate'] = "Живая очередь";
        }*/
		if($print_data['EvnQueue_id'] == 0 || $print_data['EvnStatus_SysNick'] != 'Queued'){ //https://redmine.swan.perm.ru/issues/83820
			$print_data['RecDate'] = $print_data['RecDate']."&nbsp;";
		}
		else {
			$print_data['RecDate'] = "Поставлен в очередь";
		}
		if($print_data['EvnPrescr_IsCito'] == 2 ){
			$print_data['RecDate'] .= " <b>Cito!</b>";
		}
		If ($print_data['TimetableGraf_id'])
				$print_data['TType'] = "Врач";
		else
				$print_data['TType'] = "Отделение";
		
		$print_data['JobPost'] = $print_data['Job_Name']."&nbsp;".$print_data['Post_Name'];

		if($data['session']['region']['nick'] == 'astra' && $print_data['DirType_id'] == 9 && isset($print_data['CauseTreatmentType_Name'])){
			$print_data['EvnDirection_Descr'] = $print_data['CauseTreatmentType_Name'];
		}
		if($data['session']['region']['nick'] == 'astra' && $print_data['DirType_id'] == 9 && ($print_data['TType'] == "Отделение")){
			$print_data['LpuSectionProfile_Name'] = $print_data['LpuSectionProfile_NameAstra'];
			$print_data['RecMP'] = $print_data['LpuSection_NameAstra'];
		}

		$print_data['Dir_Day'] = str_pad($print_data['Dir_Day'], 2, "0", STR_PAD_LEFT);
		$print_data['Dir_Month'] = str_pad($arMonthOf[$print_data['Dir_Month']], 16, " ", STR_PAD_BOTH);
		$print_data['Dir_Year'] = $print_data['Dir_Year']; 
		$print_data['MedDol'] = str_pad($print_data['PostMed_Name'], 30, "_", STR_PAD_RIGHT);
		$print_data['region_nick'] = (isset($data['session']) && isset($data['session']['region']) && isset($data['session']['region']['nick']))?$data['session']['region']['nick']:null;

		if (!empty($print_data['Usluga_Name'])) {
			$uslugi = '';
			foreach ($print_data['Usluga_Name'] as $value) {
				$uslugi .= $value.'; ';
			}
			$uslugi = substr($uslugi, 0, -1);

			$reStr = $uslugi;
			$arrUslugi = array();
			for ($strLen = strlen($reStr); $strLen > 0; $strLen = strlen($reStr)) {
				if ($strLen <= 100) {
					$arrUslugi[] = substr($reStr, 0);
					$reStr = '';
				} else {
					$endStr = strrpos($reStr, ' ', ($strLen - 100) * -1);
					$arrUslugi[] = substr($reStr, 0, $endStr);
					$reStr = substr($reStr, $endStr);
				}
			}
			$print_data['Usluga_Name'] = $arrUslugi;
		}

		//$this->load->view('print_evndirection', $print_data);

		if($print_data['region_nick']=='kz')
		{
			return $this->parser->parse('print_evndirection_kz', $print_data);
		}
		else if ($print_data['region_nick']=='vologda')
		{
			return $this->parser->parse('print_evndirection_vologda', $print_data);
		}
		else
		{
			return $this->parser->parse('print_evndirection', $print_data, false, false, (defined('USE_UTF') && USE_UTF));
		}
	}
	
	
	/**
	*  Получение ссылки для печати электронного направления из АРМ врача
	*  Входящие данные: $_POST['EvnDirection_id']
	*  На выходе: JSON-строка с HTML в элементе 'html'
	*  Используется: электронная медицинская карта
	*/
	function loadEvnDirectionViewForm() {
		$data = $this->ProcessInputData('loadEvnDirectionViewForm', true);
		if ( $data === false ) { return false; }

		$this->ReturnData(array("success"=>true, "html" => "/?c=EvnDirection&m=printEvnDirection&EvnDirection_id=".$data['EvnDirection_id']));
		return true;
	}


	/**
	 * Загрузка списка направлений для АРМ патологоанатома
	 */
	function loadPathoMorphologyWorkPlace() {
		$data = $this->ProcessInputData('loadPathoMorphologyWorkPlace', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadPathoMorphologyWorkPlace($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 * Получение данных направления для печати
	 */
	function getEvnDirectionForPrint() {
		$data = $this->ProcessInputData('getEvnDirectionForPrint', true);
		if ( $data === false ) { return false; }

		if (empty($data['PrescriptionType_id'])) {
			$data['PrescriptionType_id'] = $this->dbmodel->getPrescriptionTypeByEvnDirection($data);
		}
		if ($this->usePostgreLis && (empty($data['PrescriptionType_id']) || ($data['PrescriptionType_id'] && $data['PrescriptionType_id'] == 11))) {
			$this->load->swapi('lis');
			$response = $this->lis->GET('EvnDirection/EvnDirectionForPrint', $data, 'single');
			if (!$this->isSuccessful($response)) {
				$response = $this->dbmodel->getEvnDirectionForPrint($data);
			}
		} else {
			$response = $this->dbmodel->getEvnDirectionForPrint($data);
		}

		$this->ProcessModelSave($response, true, 'Ошибка получения данных направления')->ReturnData();

		return true;
	}
	
	/**
	 * получения списка направлений при принятии без записи
	 */
	function getEvnDirectionCommitList(){
		$data = $this->ProcessInputData('getEvnDirectionCommitList', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getEvnDirectionCommitList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	/**
	 * Получение списка направления для формы журнала направления для регистраторов
	 */
	function getEvnDirectionList() {
		$data = $this->ProcessInputData('getEvnDirectionList', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getEvnDirectionList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 *
	 * @return type 
	 */
	function getDataEvnDirection(){
		$data = $this->ProcessInputData('getDataEvnDirection', true, true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->getDataEvnDirection($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Убрать в очередь и освободить время приема
	 */
	function returnToQueue()
	{
		$this->load->model('EvnDirectionAll_model');
		$this->inputRules['returnToQueue'] = $this->EvnDirectionAll_model->getInputRules('returnToQueue');
		$data = $this->ProcessInputData('returnToQueue', true, true);
		if ( $data === false ) { return false; }
		$response = $this->EvnDirectionAll_model->returnToQueue($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Проверить связь рабочего места с ЭО
	 */
	function checkEQMedStaffFactLink() {
		$data = $this->ProcessInputData('checkEQMedStaffFactLink', true, true);
		if(empty($data['session']['CurMedStaffFact_id'])) {	return false; }

		$result = $this->dbmodel->checkEQMedStaffFactLink($data);
		$response = array(
			'MedStaffFactLinked' => $result,  
			'success' => true
		);
		$this->ReturnData($response);
		return true;
	}

	/**
	 *  Проверка связи онкологического диагноза с результатом диагностики
	 */
	function checkOnkoDiagforDiagnosisResult() {
		$data = $this->ProcessInputData('checkOnkoDiagforDiagnosisResult', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->checkOnkoDiagforDiagnosisResult($data);
		$this->ReturnData($response);

		return true;
	}

	/**
	 * Отменить исходящее направление c указанием причины
	 */
	function cancel()
	{
		$this->load->model('EvnDirectionAll_model');
		$this->inputRules['cancel'] = $this->EvnDirectionAll_model->getInputRules('cancel');
		$data = $this->ProcessInputData('cancel', true, true);
		if ( $data === false ) { return false; }

		if ($this->usePostgreLis && !empty($data['DirType_id']) && $data['DirType_id'] == 10) {
			$this->load->swapi('lis');
			$response = $this->lis->POST('EvnDirection/cancel', $data, 'list');
			// Если postgre справилась, надо ошмётки направления (связки) удалить из MS
			$this->load->model('EvnPrescr_model');
			$respEP = $this->EvnPrescr_model->findAndDeleteEvnPrescrDirection($data);
		} else {
			$response = $this->EvnDirectionAll_model->cancel($data);
		}

		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Отклонить входящее направление c указанием причины и освободить время приема
	 */
	function reject()
	{
		$this->load->model('EvnDirectionAll_model');
		$this->inputRules['reject'] = $this->EvnDirectionAll_model->getInputRules('reject');
		$data = $this->ProcessInputData('reject', true, true);
		if ( $data === false ) { return false; }
		$response = $this->EvnDirectionAll_model->reject($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function loadBaseJournal()
	{
		$data = $this->ProcessInputData('loadBaseJournal', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadBaseJournal($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	*  Получение списка записей для формы поиска записей на прием (swTimetableRecordsSearchWindow)
	*  На выходе: JSON-строка
	*/
	function loadTimetableRecords() {
		$data = $this->ProcessInputData('loadTimetableRecords', true);
		if ($data === false)
		{
			return false;
		}
		$response = $this->dbmodel->loadTimetableRecords($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 *  Получение данных направления для заполнения полей исхода/закрытия случая лечения
	 */
	function loadDirectionDataForLeave() {
		$data = $this->ProcessInputData('loadDirectionDataForLeave', true, true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadDirectionDataForLeave($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 *  Получение данных направления для проверки формы подозрение на ЗНО
	 */
	function loadDirectionDataForZNO() {
		$data = $this->ProcessInputData('loadDirectionDataForZNO', true, true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadDirectionDataForZNO($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Печать маршрутного листа для РВФД Ufa, gaf #116422, для ГАУЗ РВФД
	 */	
	function printRVFDRouteList()
	{		
		$data = $this->ProcessInputData(null, true, true, false, true, true);			
		
		//$this->load->model('Person_model', 'dbmodel');
		$response = $this->dbmodel->getPersonFIOAddr($_GET['Person_id']);
		
		echo '<div style="font-size:11pt!important; padding-left:145px;">ТАЛОН</div>';
		echo '<div style="font-size:11pt!important; padding-left:100px;">для прохождения УМО</div>';
		echo '<pre></pre>';
		echo '<div style="font-size:11pt!important">Ф.И.О. '.$response['Person_SurName'].' '.$response['Person_FirName'].' '.$response['Person_SecName'].'</div>';
		echo '<div style="font-size:11pt!important">Дата рождения: '.$response['Person_BirthDay'].'</div>';
		//echo '<pre><font size="3px" face="Times New Roman">2.Адрес '.$response['UAddress_AddressText'].'</font></pre>';		
		echo '<div style="font-size:11pt!important">Вид спорта: '.$response['Job_Name'].'</div><br>';
		
		$this->load->model('EvnDirection_model', 'dbmodel2');
		$response2 = $this->dbmodel2->getEvnDirectionRVFD($_GET['Person_id']);	
		
		$lab_date = '';
		$dia_date = '';
		foreach ($response2 as $test){
									
			if ($test['LpuSectionProfile_Name'] === 'ЛАБОРАТОРНАЯ ДИАГНОСТИКА' && $lab_date == ''){
				$lab_date = $test['EvnDirection_RecDate'];
			}
			
			if ($test['LpuSectionProfile_Name'] === 'ДИАГНОСТИКА' && $dia_date == ''){
				$dia_date = $test['EvnDirection_RecDate'];
			}
			
		}
		
		if ($lab_date == ''){
			echo '<div style="font-size:11pt!important"><b>1.Каб. 200 лаборатория</b>  явиться - Не назначено</div><br>';
		}else{
			$datetime_dia = new DateTime($lab_date);					
			echo '<div style="font-size:11pt!important"><b>1.Каб. 200 лаборатория</b> явиться - <b>'.date_format($datetime_dia, 'd.m.Y').' в '.date_format($datetime_dia, 'H:i').'</b></div><br>';
		}

		
		if ($dia_date == ''){
			echo '<div style="font-size:11pt!important"><b>2.Каб. 202 ЭКГ</b>  явиться - Не назначено</div><br>';
			echo '<div style="font-size:11pt!important"><b>3.Каб. 202 антропометрия</b> явиться - Не назначено</div><br>';
			echo '<div style="font-size:11pt!important"><b>4.Каб. 204 офтальмолог</b> явиться - Не назначено</div><br>';
			echo '<div style="font-size:11pt!important"><b>5.Каб. 205 ЛОР</b> явиться - Не назначено</div><br>';
			echo '<div style="font-size:11pt!important"><b>6.Каб. 207 невролог</b> явиться - Не назначено</div><br>';
			echo '<div style="font-size:11pt!important"><b>7.Каб. 208 дерматолог</b> явиться - Не назначено</div><br>';
			echo '<div style="font-size:11pt!important"><b>8.Каб. 210 травматолог-ортопед</b> явиться - Не назначено</div><br>';
			echo '<div style="font-size:11pt!important"><b>9.Каб. 212 стоматолог</b> явиться - Не назначено</div><br>';
			echo '<div style="font-size:11pt!important"><b>10.Каб. 203 врач спортивной медицины</b> - Не назначено</div><br>';
		
		}else{
			$datetime_dia = new DateTime($dia_date);			
		
			echo '<div style="font-size:11pt!important"><b>2.Каб. 202 ЭКГ</b> явиться - <b>'.date_format($datetime_dia, 'd.m.Y').' в '.date_format($datetime_dia, 'H:i').'</b></div><br>';
			echo '<div style="font-size:11pt!important"><b>3.Каб. 202 антропометрия</b> явиться - <b>'.date_format($datetime_dia, 'd.m.Y').' в '.date_format($datetime_dia->modify('+8 minutes'), 'H:i').'</b></div><br>';
			echo '<div style="font-size:11pt!important"><b>4.Каб. 204 офтальмолог</b> явиться - <b>'.date_format($datetime_dia, 'd.m.Y').' в '.date_format($datetime_dia->modify('+8 minutes'), 'H:i').'</b></div><br>';
			echo '<div style="font-size:11pt!important"><b>5.Каб. 205 ЛОР</b> явиться - <b>'.date_format($datetime_dia, 'd.m.Y').' в '.date_format($datetime_dia->modify('+8 minutes'), 'H:i').'</b></div><br>';
			echo '<div style="font-size:11pt!important"><b>6.Каб. 207 невролог</b> явиться - <b>'.date_format($datetime_dia, 'd.m.Y').' в '.date_format($datetime_dia->modify('+8 minutes'), 'H:i').'</b></div><br>';
			echo '<div style="font-size:11pt!important"><b>7.Каб. 208 дерматолог</b> явиться - <b>'.date_format($datetime_dia, 'd.m.Y').' в '.date_format($datetime_dia->modify('+8 minutes'), 'H:i').'</b></div><br>';
			echo '<div style="font-size:11pt!important"><b>8.Каб. 210 травматолог-ортопед</b> явиться - <b>'.date_format($datetime_dia, 'd.m.Y').' в '.date_format($datetime_dia->modify('+8 minutes'), 'H:i').'</b></div><br>';
			echo '<div style="font-size:11pt!important"><b>9.Каб. 212 стоматолог</b> явиться - <b>'.date_format($datetime_dia, 'd.m.Y').' в '.date_format($datetime_dia->modify('+8 minutes'), 'H:i').'</b></div><br>';
			echo '<div style="font-size:11pt!important"><b>10.Каб. 203 врач спортивной медицины</b> - <b>'.date_format($datetime_dia, 'd.m.Y').' в '.date_format($datetime_dia->modify('+8 minutes'), 'H:i').'</b></div><br>';
		}				

		
		return true;
	}

	/**
	 * Метод возвращает информацию о направлении, переданном в БГ
	 */
	function getInfoEvnDirectionfromBg()
	{
		$data = $this->ProcessInputData('getInfoEvnDirectionfromBg',true);
		if ($data === false)return false;

		$response = $this->dbmodel->getInfoEvnDirectionfromBg($data);
		$this->ReturnData(array($response));
		return true;
	}


	/**
	 * Метод возвращает справочник форм оказания консультаций
	 */
	function getConsultingFormList() {
		$response = $this->dbmodel->getConsultingFormList();
		$this->ReturnData($response);
		return true;
	}

	/**
	 * Включение назначения в существующее направление
	 */
	function includeToDirection() {
		$data = $this->ProcessInputData('includeToDirection', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->includeToDirection($data);
		$this->ProcessModelSave($response, true, "Ошибка включения назначения в существующее направление")->ReturnData();

		return true;
	}

	/*
	 * Метод возвращает документы прикрепленные к направлению
	 */
	function getLinkedXmlForEvnDirection()
	{
		$this->load->model('EvnXmlBase_model');
		$data = $this->ProcessInputData('getLinkedXmlForEvnDirection', true, true);
		if ( $data === false ) { return false; }
		$response = $this->EvnXmlBase_model->getEvnXmlForEvnDirectionList($data['EvnDirection_id']);
		$this->ReturnData($response);

		return true;
	}
}
