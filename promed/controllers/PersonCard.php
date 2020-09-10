<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PersonCard - контроллер для выполенния операций с картотекой пациентов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      01.06.2009
*/

/**
 * @property Polka_PersonCard_model $pcmodel
 */
class PersonCard extends swController {
	public $file_log;
	public $file_log_access;

	/**
	 * Запись в лог
	 */
	function writeLog($string) {
		$f = fopen($this->file_log, $this->file_log_access);
		fputs($f, $string);
		fclose($f);
	}

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
			'exportPersonCardForPeriod' => array(
				array('field' => 'ExportDateRange', 'label' => 'Период выгрузки', 'rules' => 'trim|required', 'type' => 'daterange'),
				array('field' => 'FileCreationDate', 'label' => 'Дата формирования файла', 'rules' => 'trim|required', 'type' => 'date'),
				array('field' => 'ReportDate', 'label' => 'Отчетная дата', 'rules' => 'trim|required', 'type' => 'date'),
				array('field' => 'PackageNum', 'label' => 'Порядковый номер пакета', 'rules' => 'trim|required', 'type' => 'int'),
			),

			'getPersonCard' => array(
				array(
					'field' => 'PersonCard_id',
					'label' => 'Идентификатор карты',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),

			'getLpuRegion' => array(  //Получение номера участка; для формы "РПН: Добавление" в рамках задачи 9295
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type'  => 'int'
				),
				array(
					'field' => 'LpuAttachType_id',
					'label' => 'Тип прикрепления',
					'rules' => '',
					'type'  => 'int'
				)
			),
			'getLpuRegionByAddress' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type'  => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label'	=> 'Идентификатор МО',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field' => 'LpuRegionType_id',
					'label'	=> 'Тип участка',
					'rules'	=> '',
					'type'	=> 'int'
				)
			),
			'printMedCard' => array(
				array(
					'field' => 'PersonCard_id',
					'label' => 'ид. карты',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'ид. человека',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PersonAmbulatCard_Num',
					'label' => 'Номер амбулаторной карты',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'cancelDrugRequestCheck',
					'label' => 'Отмена проверки',
					'rules' => 'trim',
					'type' => 'id'
				)
			),
			'closePersonCard' => array(
				array(
					'field' => 'PersonCard_id',
					'label' => 'Идентификатор карты',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'cancelDrugRequestCheck',
					'label' => 'Отмена проверки',
					'rules' => 'trim',
					'type' => 'id'
				)
			),
			'deletePersonCard' => array(
				array(
					'field' => 'PersonCard_id',
					'label' => 'Идентификатор карты',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'isLastAttach',
					'label' => 'Последнее ли прикрепление',
					'rules' => '',
					'type' => 'int'
				),
				array( //https://redmine.swan.perm.ru/issues/52919
					'field' => 'tryOpen',
					'label' => 'Открыть закрытое',
					'rules' => '',
					'type' => 'id'
				)
			),
			'deleteDmsPersonCard' => array(
				array(
					'field' => 'PersonCard_id',
					'label' => 'Идентификатор карты',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'checkPersonCardCode' => array(
				array(
					'default' => '',
					'field' => 'PersonCard_Code',
					'label' => 'Номер карты',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'PersonCard_id',
					'label' => 'Идентификатор карты',
					'rules' => 'trim',
					'type' => 'id'
				)
			),
			'checkAttachPosible' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonAge',
					'label' => 'Возраст человека',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuAttachType_id',
					'label' => 'Тип прикрепления',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuRegionType_id',
					'label' => 'Тип участка',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'checkPersonCard' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'LPU_CODE',
					'label' => 'Код ЛПУ',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array (
					'field' => 'LPUDX',
					'label' => 'Дата закрытия',
					'rules' => '',
					'type' => 'date'
				)
			),
			'savePersonCard' => array(
				array(
						'default' => '',
						'field' => 'action',
						'label' => 'Тип операции',
						'rules' => 'trim|required',
						'type' => 'string'
					),
				array(
						'field' => 'PersonAmbulatCard_id',
						'label' => 'Тип операции',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Person_id',
						'label' => 'Идентификатор человека',
						'rules' => 'trim|required',
						'type' => 'id'
					),
				array(
						'field' => 'Person_Birthday',
						'label' => 'Дата рождения',
						'rules' => '',
						'type'  => 'date'
					),
				array(
						'field' => 'Server_id',
						'label' => 'Идентификатор сервера',
						'rules' => '',
						'type' => 'int',
                        'default' => 0
					),
				array(
						'field' => 'PersonCard_id',
						'label' => 'Идентификатор карты',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'PersonCard_Code',
						'label' => 'Код карты',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'LpuRegion_id',
						'label' => 'Участок',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'MedStaffFact_id',
						'label' => 'Врач',
						'rules' => 'trim',
						'type' => 'id'
					),
                array(
                    'field' => 'LpuRegion_Fapid',
                    'label' => 'ФАП Участок',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
				array(
						'field' => 'LpuAttachType_id',
						'label' => 'Тип прикрепления',
						'rules' => 'trim|required',
						'type' => 'id'
					),
				array(
						'field' => 'PersonCard_IsAttachCondit',
						'label' => 'Усл. прикрепл.',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'CardCloseCause_id',
						'label' => 'Причина закрытия карты',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Lpu_id',
						'label' => 'ЛПУ прикрепления',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => 1,
						'field' => 'isDMS',
						'label' => 'По ДМС',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'PersonCard_begDate',
						'label' => 'Дата прикрепления',
						'rules' => 'trim|required',
						'type' => 'date'
					),
				array(
						'field' => 'PersonCard_endDate',
						'label' => 'Дата открепления',
						'rules' => 'trim',
						'type' => 'date'
					),
				array(
						'field' => 'OverrideCardUniqueness',
						'label' => 'Сохраняем карту с повторяющимся номером?',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
					'field' => 'PersonCardAttach_id',
					'label' => 'Ид. заявления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'user_id',
					'label' => 'идентификатор человека на портале', // нужен для отправки смс
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'isPersonCardAttach',
					'label' => 'Заявление?',
					'rules' => 'required',
					'type' => 'checkbox'
				),
				array(
					'field' => 'PersonCardAttach_IsSMS',
					'label' => 'Уведомить о прикреплении по SMS',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'PersonCardAttach_SMS',
					'label' => 'Телефон',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PersonCardAttach_IsEmail',
					'label' => 'Уведомить о прикреплении по e-mail',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'PersonCardAttach_Email',
					'label' => 'E-mail',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'files',
					'label' => 'Файлы',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'isPersonCardMedicalIntervent',
					'label' => 'Отказ от видов медицинских вмешательств',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonCardMedicalInterventData',
					'label' => 'Данные отказа от видов медицинских вмешательств',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'lastAttachIsNotInOurLpu',
					'label' => 'Последнее прикрепление в текущей МО',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PersonAge',
					'label' => 'Возраст человека',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuRegionType_id',
					'label' => 'Тип участка',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Agreed',
					'label' => 'Agreed',
					'rules' => '',
					'type' => 'id',
					'default' => '0'
				),
				array(
					'field' => 'setIsAttachCondit',
					'label' => 'Проставить условное прикрепление',
					'rules' => '',
					'type' => 'int'
				),
                array(
                    'field' => 'allowEditLpuRegion',
                    'label' => 'allowEditLpuRegion',
                    'rules' => '',
                    'type'  => 'int'
                )
			),
            'savePersonCardAuto' => array(
                array(
                    'field' => 'PC_type',
                    'label' => 'PC_type',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'ЛПУ',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'LpuRegionType_id',
                    'label' => 'Тип участка',
                    'rulse' => 'required',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'LpuRegion_id',
                    'label' => 'Участок',
                    'rulse' => 'required',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'LpuRegion_Fapid',
                    'label' => 'ФАП участок',
                    'rulse' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IsAttachCondit',
                    'label' => 'Условное прикрепление (флаг)',
                    'rules' => '',
                    'type'  => 'checkbox'
                ),
                array(
                    'field' => 'PersonCardAttach',
                    'label' => 'Заявление',
                    'rules' => '',
                    'type'  => 'checkbox'
                ),
                array(
                    'field' => 'Person_ids_array',
                    'label' => 'Массив идетнификаторов ЗЛ',
                    'rules' => 'required',
                    'type'  => 'string'
                )
            ),
			'savePersonCardDms' => array(
				array(
						'default' => '',
						'field' => 'action',
						'label' => 'Тип операции',
						'rules' => 'trim|required',
						'type' => 'string'
					),
				array(
						'default' => '',
						'field' => 'PersonCard_DmsPolisNum',
						'label' => 'Номер договора',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'default' => '',
						'field' => 'PersonCard_DmsBegDate',
						'label' => 'Номер договора',
						'rules' => 'trim',
						'type' => 'date'
					),
				array(
						'default' => '',
						'field' => 'PersonCard_DmsEndDate',
						'label' => 'Номер договора',
						'rules' => 'trim',
						'type' => 'date'
					),
				array(
						'field' => 'OrgSMO_id',
						'label' => 'Идентификатор СМО',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Person_id',
						'label' => 'Идентификатор человека',
						'rules' => 'trim|required',
						'type' => 'id'
					),
				array(
						'field' => 'Server_id',
						'label' => 'Идентификатор сервера',
						'rules' => 'trim|required',
						'type' => 'int'
					),
				array(
						'field' => 'PersonCard_id',
						'label' => 'Идентификатор карты',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'LpuAttachType_id',
						'label' => 'Тип прикрепления',
						'rules' => 'trim|required',
						'type' => 'id'
					),
				array(
						'field' => 'CardCloseCause_id',
						'label' => 'Причина закрытия карты',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Lpu_id',
						'label' => 'ЛПУ прикрепления',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => 1,
						'field' => 'isDMS',
						'label' => 'По ДМС',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'PersonCard_begDate',
						'label' => 'Дата прикрепления',
						'rules' => 'trim|required',
						'type' => 'date'
					),
				array(
						'field' => 'PersonCard_endDate',
						'label' => 'Дата открепления',
						'rules' => 'trim',
						'type' => 'date'
					),
				array(
						'field' => 'OverrideCardUniqueness',
						'label' => 'Сохраняем карту с повторяющимся номером?',
						'rules' => 'trim',
						'type' => 'int'
					)
			),
			'GetDetailList' => array(
				array(
						'field' => 'StartDate',
						'label' => 'Дата начала',
						'rules' => 'trim|required',
						'type' => 'date'
					),
				array(
						'field' => 'EndDate',
						'label' => 'Дата конца',
						'rules' => 'trim|required',
						'type' => 'date'
					),
				array(
						'field' => 'LpuRegion_id',
						'label' => 'Участок',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'mode',
						'label' => 'Режим просмотра',
						'rules' => 'trim|required',
						'type' => 'string'
					),
				array(
						'field' => 'start',
						'label' => 'С',
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
			'getPersonCardStateGrid' => array(
				array(
						'field' => 'Period',
						'label' => 'Период дат',
						'rules' => 'required',
						'type' => 'daterange'
					),
				array(
						'field' => 'LpuRegionType_id',
						'label' => 'Тип участка',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'LpuAttachType_id',
						'label' => 'Тип прикрепления',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => 1,
						'field' => 'LpuMotion_id',
						'label' => 'Движение',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => null,
						'field' => 'FromLpu_id',
						'label' => 'Прикрепился из',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => null,
						'field' => 'ToLpu_id',
						'label' => 'Открепился в',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => null,
						'field' => 'LpuRegion_id',
						'label' => 'Участок',
						'rules' => 'trim',
						'type' => 'int'
					)
			),
			'getPersonCardCode' => array(
				array(
						'field' => 'Person_id',
						'label' => 'Идентификатор человека',
						'rules' => 'trim|required',
						'type' => 'id'
					),
				array(
						'field' => 'Lpu_id',
						'label' => 'Новое ЛПУ',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'CheckFond',
						'label' => 'Проверка на фондодержание',
						'rules' => 'trim',
						'type' => 'id'
					)
			),
			'getPersonCardHistoryList' => array(
				array(
					'default' => 'common_region',
					'field' => 'AttachType',
					'label' => 'Тип прикрепления',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim',
					'type' => 'id'
				)
			),
			'checkIfPersonCardIsExists' => array(
				array(
					'default' => 0,
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim',
					'type' => 'id'
				)
			),
			'printStatement' =>array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'trim|required',
					'type' => 'int'
				)
			),
			'printPersonCardAttach' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'int' ),
				array('field' => 'PersonCardAttach_IsHimself', 'label' => 'заявления о выборе МО лично пациентом', 'rules' => 'required', 'type' => 'int' ),
				array('field' => 'LpuRegion_id', 'label' => 'Идентификатор участка', 'rules' => '', 'type' => 'id' )
			),
			'printPersonCardAttachKz' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'PCPDW_Deputy_id', 'label' => 'Идентификатор представителя', 'rules' => '', 'type' => 'id' ),
				array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int' )
			),
			'loadPersonCardAttachGrid' => array(
				array('field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => 'trim',	'type' => 'string' ),
				array('field' => 'Person_FirName', 'label' => 'Имя', 'rules' => 'trim', 'type' => 'string' ),
				array('field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => 'trim', 'type' => 'string' ),
				array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'int' ),
				array('field' => 'Lpu_aid', 'label' => 'МО, принявшее заявление', 'rules' => '', 'type' => 'int'),
				array('field' => 'PersonCardAttach_setDate_Range', 'label' => 'Период подачи заявления', 'rules' => '', 'type' => 'daterange'),
				array('field' => 'Person_BirthDay_Range', 'label' => 'Дата рождения', 'rules' => '', 'type' => 'daterange'),
				array('field' => 'PersonCardAttachStatusType_id', 'label' => 'Состояние', 'rules' => '', 'type' => 'id' ),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Старт',
					'rules' => 'trim',
					'type' => 'int'
				),
				array('field' => 'RecMethodType_id', 'label' => 'Идентификатор источника записи', 'rules' => '', 'type' => 'id' ),
			),
			'loadPersonCardAttachForm' => array(
				array(
					'field' => 'PersonCardAttach_id',
					'label' => 'Идентификатор заявления',
					'rules' => 'required',
					'type'  => 'id'
				)
			),
			'checkPersonCardActive' => array(
				array(
					'field'	=> 'Person_id',
					'label'	=> 'Идентификатор человека',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'Lpu_id',
					'label'	=> 'Идентификатор МО',
					'rules'	=> 'required',
					'type'	=> 'int'
				)
			),
			'savePersonCardAttachForm' => array(
				array(
					'field'	=> 'PersonCardAttach_id',
					'label'	=> 'Идентификатор заявления',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'PersonCardAttach_setDate',
					'label'	=> 'Дата заявления',
					'rules'	=> 'required',
					'type'	=> 'date'
				),
				array(
					'field'	=> 'Person_id',
					'label'	=> 'Идентификатор человека',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'Lpu_aid',
					'label'	=> 'Идентификатор МО',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'LpuRegionType_id',
					'label'	=> 'Тип участка',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'LpuRegion_id',
					'label'	=> 'Участок',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'LpuRegion_fapid',
					'label'	=> 'ФАП участок',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'PersonAmbulatCard_id',
					'label'	=> 'Амбулаторная карта',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'MedStaffFact_id',
					'label'	=> 'Участковый врач',
					'rules' => 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'PersonCardAttach_ExpNameFile',
					'label'	=> 'Файл экспорта',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'PersonCardAttach_ExpNumRow',
					'label'	=> 'Номер в файле экспорта',
					'rules' => '',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'RecMethodType_id',
					'label'	=> 'Идентификатор источника записи',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field' => 'files',
					'label' => 'Файлы',
					'rules' => '',
					'type'  => 'string'
				),
			),
			'deletePersonCardAttach' => array(
				array(
					'field' => 'PersonCardAttach_id',
					'label'	=> 'Идентификатор заявления',
					'rules'	=> 'required',
					'type'	=> 'int'
				)
			),
			'changePersonCardAttachStatus' => array(
				array(
					'field' => 'PersonCardAttachStatusType_id',
					'label'	=> 'Идентификатор статуса',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				 array(
                    'field' => 'PersonCardAttach_ids_array',
                    'label' => 'Массив идетнификаторов заявлений',
                    'rules' => 'required',
                    'type'  => 'string'
                )
			),
			'cancelPersonCardAttach' => array(
				array(
					'field' => 'PersonCardAttach_CancelReason',
					'label' => 'Причина отказа',
					'rules' => 'required',
					'type'  => 'string'
				),
				array(
					'field' => 'PersonCardAttach_ids_array',
					'label' => 'Массив идетнификаторов заявлений',
					'rules' => 'required',
					'type'  => 'string'
				)
			),
			'savePersonCardByAttach' => array(
				array(
                    'field' => 'PersonCardAttach_ids_array',
                    'label' => 'Массив идетнификаторов заявлений',
                    'rules' => 'required',
                    'type'  => 'string'
                )
			),
			'importAnswerFromSMO' => array(
				array(
					'field' => 'ImportFile',
					'label' => 'Файл',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadPersonCardMedicalInterventGrid' => array(
				array(
					'field' => 'PersonCard_id',
					'label' => 'Идентификатор карты',
					'rules' => '',
					'type' => 'id'
				)
			),
            'loadAttachedList' => array(
                array(
                    'field' => 'AttachLpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgSMO_id',
                    'label' => 'Идентификатор страховой организации',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Date_upload',
                    'label' => 'Дата выгрузки',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                   'field' => 'PackageNum',
                    'label' => 'Номер пакета',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'loadAttachedListCSV' => array(
                array(
                    'field' => 'AttachLpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
					'field' => 'AttachPeriod',
					'label' => 'Период выборки', // 1 - все данные, 2 - изменения от даты до текущей
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field'	=> 'AttachPeriod_FromDate',
					'label'	=> 'Дата',
					'rules'	=> 'trim',
					'type'	=> 'date'
                )
            ),
            'printAttachedList' => array(
                array(
                    'field' => 'm04',
                    'label' => 'Мальчики от 0 до 4 лет',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'w04',
                    'label' => 'Девочки от 0 до 4',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'm517',
                    'label' => 'Мальчики от 5 до 17',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'w517',
                    'label' => 'Девочки от 5 до 17',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'm1859',
                    'label' => 'Мужчины от 18 до 59',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'w1854',
                    'label' => 'Женщины от 18 до 54',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'm60',
                    'label' => 'Мужчины 60+',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'w55',
                    'label' => 'Женщины 55+',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OrgSmo_id',
                    'label' => 'Идентификатор страховой организации',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'count_ppl',
                    'label' => 'Количество прикрепленных людей',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
			'getPersonInfoKVRACHU' => array(
				array('field' => 'Person_id', 'label' => 'Персон', 'rules' => 'required', 'type' => 'id' )
			),
			'uploadFiles' => array(
				
			),
			'deleteFile' => array(
				array('field' => 'url', 'label' => 'Путь к файлу', 'rules' => 'trim|required', 'type' => 'string'),
				array(
					'field' => 'pmMediaData_id',
					'label' => 'pmMediaData_id',
					'rules' => '',
					'type' 	=> 'int'
				)
			),
			'exportPersonAttaches' => array( //Экспорт прикрепленного населения. https://redmine.swan.perm.ru/issues/55170
				array(
					'field' => 'AttachesLpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type'	=> 'int'
				),
				array(
					'field' => 'ExportDateRange',
					'label' => 'Период выгрузки',
					'rules'	=> 'trim',
					'type'	=> 'daterange'
				)
			),
			'checkPersonDisp' => array(
					array(
							'field' => 'Person_id',
							'label' => 'Идентификатор пациента',
							'rules' => 'required',
							'type'	=> 'int'
					),
					array(
							'field' => 'Lpu_id',
							'label' => 'Идентификатор МО',
							'rules'	=> 'required',
							'type'	=> 'id'
					)
			),
			'sendMessages' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type'	=> 'int'
				),
				array(
					'field' => 'Lpu_old_id',
					'label' => 'Идентификатор старого МО',
					'default' => 0,
					'rules'	=> '',
					'type'	=> 'id'
				),
				array(
					'field' => 'Lpu_new_id',
					'label' => 'Идентификатор нового МО',
					'rules'	=> 'required',
					'type'	=> 'id'
				),
				array(
					'field' => 'pmUser_id',
					'label' => 'Идентификатор пользователя',
					'rules'	=> '',
					'type'	=> 'id'
				)
			)
		);
		
		$this->load->database();
		$this->load->model("Polka_PersonCard_model", "pcmodel");
		$this->default_db = $this->db->database;
		$this->load->library('textlog', array('file'=>'PersonCardNotifiaction.log'));
		$this->file_log = PROMED_LOGS.'PersonCard.log';
		$this->file_log_access = 'a';
	}
	
	/**
	 * Проверка возможно-ли прикрепление
	 */
	function checkAttachPosible($data)
	{
		if ( (int) $data['LpuAttachType_id'] != 1 ) {
			return array('success' => true, 'lastAttachIsNotInOurLpu' => true);
		}

		$response = $this->pcmodel->checkAttachPosible($data);

   		// типа проверка прошла успешно
		if ( $response === true ) {
			return array('success' => true, 'lastAttachIsNotInOurLpu' => true);
		} else {
			return array('success' => false, 'Error_Code' => $response[0]['Error_Code'], 'Error_Msg' => $response[0]['Error_Msg'], 'Cancel_Error_Handle' => true);
		}
	}

	/**
	 * Печать заявления на прикрепление
	 * Входящие данные: $_GET['Person_id']
	 *					$_GET['Server_id']
	 * На выходе: форма для печати заявления на прикрепление
	 * Используется: форма редактирования карты пациента
	 */
	function printStatement()
	{
		$this->load->helper('Options');
		$this->load->helper('Text');
		$this->load->library('parser');
		$this->load->model('Common_model', 'dbmodel');

		$data = $this->ProcessInputData('printStatement', true);
		if ($data === false) { return false; }

		$template_name = 'statement_template'; 
		
		//получаем регон
		$region_nick = isset($_SESSION['region']) && isset($_SESSION['region']['nick']) ? $_SESSION['region']['nick'] : 'undefined';

		// Получаем данные по человеку
		$response = $this->dbmodel->loadPersonData($data, 'AttachStatement');

		if (!is_array($response) || count($response) == 0)
		{
			echo 'Ошибка при получении данных по человеку';
			return true;
		}

		$parse_data = array();
		$parse_data['person_FIO'] = $response[0]['Person_Surname']. " " .$response[0]['Person_Firname']. " " .$response[0]['Person_Secname'];
		$parse_data['org_smo_name'] = $response[0]['OrgSmo_Name'];
		$parse_data['polis_ser'] = $response[0]['Polis_Ser'];
		$parse_data['polis_num'] = $response[0]['Polis_Num'];
		$parse_data['polis_beg_date'] = $response[0]['Polis_begDate'];
		$parse_data['date'] = date('d.m.Y');
		$parse_data['statement_template_title'] = "Заявление на прикрепление: ".$parse_data['person_FIO'];

		if ($region_nick == 'perm') {
			$parse_data['information_block_1'] = "к Методическим рекомендациям «О порядке оказания медицинской помощи на территории Пермского края медицинскими учреждениями (организациями) независимо от формы собственности в системе обязательного медицинского страхования, в том числе в условиях фондодержания, оплаты по подушевым нормативам амбулаторно-поликлинической помощи»";
			$parse_data['information_block_2'] = "настоящим подтверждаю выбор Вашего медицинского учреждения (организации) для получения первичной медико-санитарной помощи по участковому принципу и согласие на использование моих персональных данных при их обработке в соответствии с действующим законодательством Российской Федерации, включая передачу моих персональных данных для обработки в Пермский краевой фонд обязательного медицинского страхования в целях обязательного медицинского страхования.";
		}
		
		if ($region_nick == 'ufa') {
			$parse_data['information_block_1'] = "к Методическим рекомендациям «О порядке оказания медицинской помощи на территории республики Башкортостан медицинскими учреждениями (организациями) независимо от формы собственности в системе обязательного медицинского страхования, в том числе в условиях фондодержания, оплаты по подушевым нормативам амбулаторно-поликлинической помощи»";
			$parse_data['information_block_2'] = "настоящим подтверждаю выбор Вашего медицинского учреждения (организации) для получения первичной медико-санитарной помощи по участковому принципу и согласие на использование моих персональных данных при их обработке в соответствии с действующим законодательством Российской Федерации, включая передачу моих персональных данных для обработки в Республиканский фонд обязательного медицинского страхования Республики Башкортостан и другие медицинские организации РБ в целях обязательного медицинского страхования.";
		}
		if ($region_nick == 'pskov') {
			$parse_data['information_block_1'] = "к Методическим рекомендациям «О порядке оказания медицинской помощи на территории Псковской области медицинскими учреждениями (организациями) независимо от формы собственности в системе обязательного медицинского страхования, в том числе в условиях фондодержания, оплаты по подушевым нормативам амбулаторно-поликлинической помощи»";
			$parse_data['information_block_2'] = "настоящим подтверждаю выбор Вашего медицинского учреждения (организации) для получения первичной медико-санитарной помощи по участковому принципу и согласие на использование моих персональных данных при их обработке в соответствии с действующим законодательством Российской Федерации, включая передачу моих персональных данных для обработки в ТЕРРИТОРИАЛЬНЫЙ ФОНД ОБЯЗАТЕЛЬНОГО МЕДИЦИНСКОГО СТРАХОВАНИЯ ПСКОВСКОЙ ОБЛАСТИ в целях обязательного медицинского страхования.";
		}
		
		$address_splited_names = SplitString($response[0]['Person_PAddress'], 40);
		$parse_data['person_address1'] = $address_splited_names[0];
		if ( count($address_splited_names) > 1 )
			$parse_data['person_address2'] = $address_splited_names[1];
		else
			$parse_data['person_address2'] = "&nbsp;";
		$this->load->model('User_model', 'lpumodel');

		// Получаем данные по ЛПУ
		$response = $this->lpumodel->getCurrentLpuName($data);

		if (!is_array($response) || count($response) == 0)
		{
			echo 'Ошибка при получении данных по ЛПУ';
			return true;
		}
		$lpu_splited_names = SplitString($response[0]['Lpu_Name'], 35);

		$parse_data['lpu_name1'] = $lpu_splited_names[0];
		if ( count($lpu_splited_names) > 1 )
			$parse_data['lpu_name2'] = $lpu_splited_names[1];
		else
			$parse_data['lpu_name2'] = "&nbsp;";
		// array_walk($data, 'htmlspecialchars');

		$res = $this->parser->parse($template_name, $parse_data);
	}
	
	/**
	 * Печать заявления на прикрепление (новый метод)
	 * На выходе: форма для печати заявления на прикрепление
	 * Используется: форма редактирования карты пациента
	 */
	function printPersonCardAttach() {
		$data = $this->ProcessInputData('printPersonCardAttach', true);
		if ($data === false) { return false; }

		//print_r($data); die();
		$this->load->library('parser');
		$this->load->model('Common_model', 'dbmodel');
	    $template = "PersonCardAttach_is".($data['PersonCardAttach_IsHimself'] == 1 ? "NOT" : "")."Himself_template";

		//Проверяем регион:
		$is_perm = $_SESSION['region']['nick'] == 'perm';
		
		$response = $this->dbmodel->loadPersonData($data, 'AttachStatement');
		$parseData = array();
		if ($is_perm) $parseData['Perm_Head'] = "Утверждено приказом
												Министерства здравоохранения
												Пермского края от 21.12.2012 № 1342н";
		else $parseData['Perm_Head'] = "";
		
		$parseData['Person_FIO'] = $response[0]['Person_Surname']. " " .$response[0]['Person_Firname']. " " .$response[0]['Person_Secname'];
		$parseData['OrgSmo_Name'] = $response[0]['OrgSmo_Name'];
		$parseData['Polis_Ser'] = $response[0]['Polis_Ser'];
		$parseData['Polis_Num'] = $response[0]['Polis_Num'];
        $parseData['OrgSmo_Name'] = $response[0]['OrgSmo_Name'];
		$parseData['Polis_begDate'] = $response[0]['Polis_begDate'];
		$parseData['date'] = date('d.m.Y');
		$parseData['statement_template_title'] = "Заявление на прикрепление: " . $parseData['Person_FIO'];
		$parseData['Person_RAddress'] = $response[0]['Person_RAddress'];
		$parseData['Person_PAddress'] = $response[0]['Person_PAddress'];
		$parseData['Lpu_Nick'] = $response[0]['Lpu_Nick'];
		$parseData['Person_BirthYear'] = mb_substr($response[0]['Person_Birthday'], 6, strlen($response[0]['Person_Birthday']));
		
		// @task https://redmine.swan-it.ru/issues/194242 - поля свидетельства о рождении
		$parseData['DocumentType_Name'] = ($response[0]['DocumentType_id'] == 3) ? $response[0]['DocumentType_Name'] : '';
		$parseData['Document_Num'] = ($response[0]['DocumentType_id'] == 3) ? $response[0]['Document_Num'] : '';
		$parseData['Document_Ser'] = ($response[0]['DocumentType_id'] == 3) ? $response[0]['Document_Ser'] : '';
		
		//print_r($response); die();
		$this->load->model('User_model', 'umodel');
		// Получаем данные по ЛПУ
		$response = $this->umodel->getCurrentLpuName($data);
		$parseData['Lpu_Name'] = $response[0]['Lpu_Name'];
		$parseData['Lpu_Address'] = $response[0]['Lpu_Address'];
		$parseData['OrgHead_FIO'] = $response[0]['Person_Surname']. " " .$response[0]['Person_Firname']. " " .$response[0]['Person_Secname'];

		// Участковый врач:
		$parseData['MedStaffRegion_Fio'] = '';
		$parseData['MedStaffRegionPost_id'] = 0;

		if (!empty($data['LpuRegion_id']))
		{
			$response = $this->dbmodel->getDistrictDoctor($data['LpuRegion_id']);

			if (!empty($response[0]['Person_Fio']))
			{
				$parseData['MedStaffRegion_Fio'] = $response[0]['Person_Fio'];
				$parseData['MedStaffRegionPost_id'] = $response[0]['Dolgnost_id'];
			}
		}

		
		// если выбрана Форма заявления о выборе МО представителем пациента, то находим представителя
		if( $data['PersonCardAttach_IsHimself'] == 1 ) {
			$this->load->model('Mse_model', 'Mse_model');
			$deputyData = $this->Mse_model->getDeputyKind(array('Person_id' => $data['Person_id']));
			if(is_array($deputyData) && count($deputyData) > 0 ) {
				$parseData['Deputy_Fio'] = $deputyData[0]['Person_Fio'];
			} else {
				DieWithError("У пациента нет законного представителя!");
				return;
			}
			//print_r($deputyData); die();
		}

		$res = $this->parser->parse($template, $parseData);
	}
	/**
	 * Печать заявления на прикрепление для Карелии
	 * На выходе: форма для печати заявления на прикрепление
	 * Используется: форма редактирования карты пациента
	 */
	function printPersonCardAttachKareliya() {
		$data = $this->ProcessInputData('printPersonCardAttach', true);
		if ($data === false) { return false; }
		$this->load->library('parser');
		$this->load->model('Common_model', 'dbmodel');
		//массив данных для печатной формы
		$parseData = array();
		$template = "PersonCardAttach_templateKareliya";
		//пролучаем персональные данные о застрахованном лице
		$response = $this->dbmodel->loadPersonData($data, 'AttachStatementKareliya');				
		$parseData['Person_FIO'] = $response[0]['Person_Surname']. " " .$response[0]['Person_Firname']. " " .$response[0]['Person_Secname'];
		$parseData['statement_template_title'] = $data['PersonCardAttach_IsHimself'] == 2 ?
			"Личное заявление на прикрепление: " . $parseData['Person_FIO'] :
			"Заявление представителя на прикрепление: " . $parseData['Person_FIO'];
		$parseData['Person_BirthYear'] = $response[0]['Person_Birthday'];
		$parseData['Sex_Name'] = $response[0]['Sex_Name'];
		$parseData['Person_BAddress'] = $response[0]['Person_BAddress'];
		if($response[0]['DocumentType_id'] == 11){
			$parseData['PersonDocVid'] = $response[0]['Document_Ser'] . ' ' . $response[0]['Document_Num'] .
				', ' . $response[0]['Document_begDate'] . ', ' . $response[0]['OrgDep_Name'];
			$parseData['PersonDoc']  = '';
		}
		else {
			$parseData['PersonDoc'] = $response[0]['Document_Ser'] . ' ' . $response[0]['Document_Num'] .
				', ' . $response[0]['Document_begDate'] . ', ' . $response[0]['OrgDep_Name'];
			$parseData['PersonDocVid'] = '';
		}
		$parseData['Person_PAddress'] = $response[0]['Person_PAddress'];
		$parseData['Person_RAddress'] = $response[0]['Person_RAddress'];
		$parseData['Person_Phone'] = '';
		preg_match_all('!\d+!', $response[0]['Person_Phone'], $out);		
		foreach ($out as $telephone)
			foreach ($telephone as $t)
				$parseData['Person_Phone'] = $t == '' ? '' : ' т. 8' . $t;			
		$parseData['Polis'] = $response[0]['Polis_Ser'] .' '. $response[0]['Polis_Num'];		
		$parseData['OrgSmo_Name'] = $response[0]['OrgSmo_Name'];
		$parseData['Person_Snils'] = $response[0]['Person_Snils'];
		$parseData['Lpu_Nick'] = $response[0]['Lpu_Nick'];
		// Получаем данные по ЛПУ
		$this->load->model('User_model', 'umodel');		
		$response = $this->umodel->getCurrentLpuName($data);
		$parseData['Lpu_Name'] = $response[0]['Lpu_Name'];
		$parseData['Lpu_Nick'] = $response[0]['Lpu_Nick'];
		$parseData['Lpu_Address'] = $response[0]['Lpu_Address'];
		$parseData['Ruk_MO_FIO'] = $response[0]['Person_Surname']. " " .$response[0]['Person_Firname']. " " .$response[0]['Person_Secname'];
		//получение МО предыдущего прикрепления
		$this->load->model('Common_model', 'dbmodel');
		$response = $this->dbmodel->getPersonLastAtachment(array('Person_id' => $data['Person_id']));
		$parseData['LpuAtachName'] = $response[0]['Org_Name'] . ' (' . $response[0]['Org_Nick'] . ')';
		//дата подачи заявления
		$parseData['CurentDateDay'] = date('d');
		$parseData['CurentDateMonth'] = date('m');
		$parseData['CurentDateYear'] = date('Y');		
		//данные о представителе
		$parseData['Deputy_Fio'] = '';
		$parseData['DeputyDoc'] = '';
		$parseData['DeputyReasonDoc'] = '<br>';
		$parseData['DeputyPhone'] = '';
		$parseData['DeputyKind_Name'] = '<br>';
		$parseData['DeputyFatherOrMather'] = 'отец, мать';
		$parseData['DeputyReason'] = 'несовершеннолетний ребенок, недееспособность, попечительство';		
		// если выбрана Форма заявления о выборе МО представителем пациента, то находим представителя
		if( $data['PersonCardAttach_IsHimself'] == 1 ) {
			$this->load->model('Mse_model', 'Mse_model');
			$deputyData = $this->Mse_model->getDeputyKindKareliya(array('Person_id' => $data['Person_id']));
			if(is_array($deputyData) && count($deputyData) > 0 ) {
				$parseData['Deputy_Fio'] = $deputyData[0]['Person_Fio'];				
				preg_match_all('!\d+!', $deputyData[0]['PersonPhone_Phone'], $out);
				foreach ($out as $telephone)
					foreach ($telephone as $t)
						$parseData['DeputyPhone'] = $t == '' ? '' : ' т. 8' . $t;				
				$parseData['DeputyDoc'] = $deputyData[0]['Document_Ser'] . ' ' . $deputyData[0]['Document_Num'].', ' .
					$deputyData[0]['Document_begDate'] . ', ' . $deputyData[0]['Org_Name'];
				switch ($deputyData[0]['DeputyKind_id'])
				{
					case 1:
						$parseData['DeputyKind_Name'] = $deputyData[0]['DeputyKind_Name'];
						break;
					case 2:
						$parseData['DeputyReason'] = '<u>несовершеннолетний ребенок</u>, недееспособность, попечительство';						
						switch($deputyData[0]['Sex_id'])
						{
							case 1:
								$parseData['DeputyFatherOrMather'] = '<u>отец</u>, мать';
								break;
							case 2:
								$parseData['DeputyFatherOrMather'] = 'отец, <u>мать</u>';
								break;
							default:
								break;
						}
						break;
					case 3:
						$parseData['DeputyReason'] = 'несовершеннолетний ребенок, <u>недееспособность</u>, попечительство';
						$parseData['DeputyKind_Name'] = $deputyData[0]['DeputyKind_Name'];
						break;
					case 4:
						$parseData['DeputyReason'] = 'несовершеннолетний ребенок, недееспособность, <u>попечительство</u>';
						$parseData['DeputyKind_Name'] = $deputyData[0]['DeputyKind_Name'];
						break;
					default:
						break;
				}
			}
			else {
				DieWithError("У пациента нет законного представителя!");
				return;
			}
		}
		$res = $this->parser->parse($template, $parseData);
	}
	/**
	 * Печать заявления на прикрепление для Казахстана
	 * На выходе: форма для печати заявления на прикрепление
	 * Используется: форма редактирования карты пациента
	 */
	function printPersonCardAttachKz()
	{
		$data = $this->ProcessInputData('printPersonCardAttachKz', true);
		if ($data === false) { return false; }
		//$this->printPersonCardAttach();
		$this->load->library('parser');
		$this->load->model('Common_model', 'dbmodel');
		$template = "PersonCardAttachKz";
		$response = $this->dbmodel->loadPersonDataForAttachKz($data);
		$parseData = array();

		$parseData['Person_FIO'] = $response[0]['Person_Surname']. " " .$response[0]['Person_Firname']. " " .$response[0]['Person_Secname'];
		$parseData['Person_Inn'] = $response[0]['Person_Inn'];
		$parseData['date'] = date('d.m.Y');;

		$parseData['Person_PAddress'] = $response[0]['Person_PAddress'];
		//$parseData['Lpu_Nick'] = $response[0]['Lpu_Nick'];

		$parseData['Document_SerNum'] = $response[0]['Document_Ser']." ".$response[0]['Document_Num'];
		$parseData['OrgDep_Name'] = $response[0]['OrgDep_Name'];
		$parseData['Document_begDate'] = $response[0]['Document_begDate'];
		$parseData['Person_Birthday'] = $response[0]['Person_Birthday'];
		$parseData['LpuAttach_Name'] = $response[0]['LpuAttach_Name'];
		$parseData['PersonCard_begDate'] = $response[0]['PersonCard_begDate'];

		$parseData['upperFromDep'] = '';
		$parseData['Me'] = 'меня';
		$parseData['Person_FIOFrom'] = $parseData['Person_FIO'];
		//Если печатаем от имени представителя, то дергаем данные по ид-шнику представителя
		if(isset($data['PCPDW_Deputy_id']) && $data['PCPDW_Deputy_id'] > 0){
			$data_dep = array();
			$data_dep['Person_id'] = $data['PCPDW_Deputy_id'];
			$response_dep = $this->dbmodel->loadPersonDataForAttachKz($data_dep);
			$parseData['Person_FIOFrom'] = $response_dep[0]['Person_Surname']. " " .$response_dep[0]['Person_Firname']. " " .$response_dep[0]['Person_Secname'];
			$parseData['Person_Inn'] = $response_dep[0]['Person_Inn'];
			$parseData['Person_PAddress'] = $response_dep[0]['Person_PAddress'];
			$parseData['Document_SerNum'] = $response_dep[0]['Document_Ser']." ".$response_dep[0]['Document_Num'];
			$parseData['OrgDep_Name'] = $response_dep[0]['OrgDep_Name'];
			$parseData['Document_begDate'] = $response_dep[0]['Document_begDate'];
			$parseData['upperFromDep'] = 'представителя';
			$parseData['Me'] = '';
		}

		$this->load->model('User_model', 'umodel');
		$response = $this->umodel->getCurrentLpuName($data);
		$parseData['Lpu_Name'] = $response[0]['Lpu_Name'];
		$parseData['Lpu_Address'] = $response[0]['Lpu_Address'];
		$parseData['OrgHead_FIO'] = $response[0]['Person_Surname']. " " .$response[0]['Person_Firname']. " " .$response[0]['Person_Secname'];

		$res = $this->parser->parse($template, $parseData);
	}

	/**
	 * Печать заявления на прикрепление для Екстеринбурга
	 * На выходе: форма для печати заявления на прикрепление
	 * Используется: форма редактирования карты пациента
	 */
	function printPersonCardAttachEkb()
	{
		$data = $this->ProcessInputData('printPersonCardAttach', true);
		$isDeputy = ($data['PersonCardAttach_IsHimself'] == 1);

		if ($data === false)
			return false;

		$this->load->library('parser');
		$this->load->model('Common_model', 'dbmodel');

		//массив данных для печатной формы
		$parseData = array();
		$template = ($isDeputy ? "PersonCardAttach_Deputy_templateEkb" : "PersonCardAttach_templateEkb");

		//пролучаем персональные данные о застрахованном лице
		$response = $this->dbmodel->loadPersonData($data, 'AttachStatement',
			[
				'Sex_Name',
				'PolisType_id',
				'KLCountry_Name',
				'Person_BAddress',
				'OrgDep_Name',
				'Person_Phone',
				'PAddress_id',
				'UAddress_id'
			]);

// 		print_r($response);
		$parseData['Person_FIO'] = $response[0]['Person_Surname']. " " .$response[0]['Person_Firname']. " " .$response[0]['Person_Secname'];
		$parseData['Person_Birthday'] = $response[0]['Person_Birthday'];
		$parseData['Sex_Name'] = mb_substr($response[0]['Sex_Name'], 0, 1);
		$parseData['KLCountry_Name'] = $response[0]['KLCountry_Name'];
		$parseData['Person_BAddress'] = $response[0]['Person_BAddress'];
		$parseData['Person_PAddress_id'] = $response[0]['PAddress_id'];
		$parseData['Person_PAddress'] = $response[0]['Person_PAddress'];
		$parseData['Person_RAddress_id'] = $response[0]['UAddress_id'];
		$parseData['Person_RAddress'] = $response[0]['Person_RAddress'];

		$parseData['DocumentType_Name'] = $response[0]['DocumentType_Name'];
		$parseData['Document_Ser'] = $response[0]['Document_Ser'];
		$parseData['Document_Num'] = $response[0]['Document_Num'];
		$parseData['Document_begDate'] = $response[0]['Document_begDate'];
		$parseData['OrgDep_Name'] = $response[0]['OrgDep_Name'];
		$parseData['Person_Phone'] = $response[0]['Person_Phone'];

// Полис:
		$parseData['PolisType_id'] = $response[0]['PolisType_id'];
		$parseData['Polis_Num'] = $response[0]['Polis_Ser'] . ' ' . $response[0]['Polis_Num'];
		$parseData['OrgSmo_Name'] = $response[0]['OrgSmo_Name'];
		$parseData['Polis_begDate'] = $response[0]['Polis_begDate'];

		if ($isDeputy)
		{
			$this->load->model('Mse_model', 'Mse_model');
			$deputyData = $this->Mse_model->getDeputyKindKareliya(['Person_id' => $data['Person_id']]);

			if (is_array($deputyData) && count($deputyData) > 0)
			{
				$parseData['DeputyPerson_FIO'] = $deputyData[0]['Person_Fio'];

				switch ($deputyData[0]['DeputyKind_id'])
				{
					case 2:
						$parseData['DeputyReason'] = 'несовершеннолетний ребенок';
						break;
					case 3:
						$parseData['DeputyReason'] = 'недееспособность';
						break;
					case 4:
						$parseData['DeputyReason'] = 'попечительство';
						break;
				}

				$response = $this->dbmodel->loadPersonData(['Person_id' => $deputyData[0]['Person_id']], 'AttachStatement',
					[
						'Sex_Name',
						'PolisType_id',
						'KLCountry_Name',
						'Person_BAddress',
						'OrgDep_Name',
						'Person_Phone'
					]);

				$parseData['DeputyDocumentType_Name'] = $response[0]['DocumentType_Name'];
				$parseData['DeputyDocument_Ser'] = $response[0]['Document_Ser'];
				$parseData['DeputyDocument_Num'] = $response[0]['Document_Num'];
				$parseData['DeputyDocument_begDate'] = $response[0]['Document_begDate'];
				$parseData['DeputyOrgDep_Name'] = $response[0]['OrgDep_Name'];
				$parseData['DeputyPerson_Phone'] = $response[0]['Person_Phone'];
			}
			else
			{
				DieWithError("У пациента нет законного представителя!");
				return;
			}
		}

		// Получаем данные по ЛПУ
		$this->load->model('User_model', 'umodel');
		$response = $this->umodel->getCurrentLpuName($data);
		$parseData['Lpu_Name'] = $response[0]['Lpu_Name'];
		$parseData['Lpu_Address'] = $response[0]['Lpu_UAddress'];

// Участковый врач:
		$parseData['MedStaffRegion_Fio'] = '';
		$parseData['MedStaffRegionPost_id'] = 0;

		if (!empty($data['LpuRegion_id']))
		{
			$response = $this->dbmodel->getDistrictDoctor($data['LpuRegion_id']);

			if (!empty($response[0]['Person_Fio']))
			{
				$parseData['MedStaffRegion_Fio'] = $response[0]['Person_Fio'];
				$parseData['MedStaffRegionPost_id'] = $response[0]['Dolgnost_id'];
			}
		}

// Предыдущая МО:
		$parseData['PrevLpu_Name'] = '';
		$parseData['PrevLpu_Address'] = '';

		$response = $this->dbmodel->getPrevAttachLpu($data['Person_id']);

		if (!empty($response[0]))
		{
			$parseData['PrevLpu_Name'] = $response[0]['Lpu_Name'];
			$parseData['PrevLpu_Address'] = $response[0]['Lpu_UAddress'];
		}

// 		print_r($response);
		$res = $this->parser->parse($template, $parseData);
	}

	/**
	 * Функция получения списка истории карт пациентов.
	 * Входящие данные: $_POST с фильтрами
	 * На выходе: JSON-строка
	 */
	function getPersonCardHistoryList()
	{
		$this->load->helper('Text');
		$data = $this->ProcessInputData('getPersonCardHistoryList', true);
		if ($data === false) { return false; }
		
		$info = $this->pcmodel->getPersonCardHistoryList($data);
		$this->ProcessModelList($info, true, true)->ReturnData();
	}

	/**
	 * Функция сохранения карты пациента.
	 * Входящие данные: $_POST с данными карты
	 * Используется: форма редактирования карты пациента
	 */
	function savePersonCard()
	{
		$this->load->helper('Options');
		$this->load->model("Options_model", "opmodel");
		$val  = array();
		$global_options = $this->opmodel->getOptionsGlobals(getSessionParams());
		$globalOptions = $global_options['globals'];

		$data = $this->ProcessInputData('savePersonCard', false);
		if ($data === false) { return false; }
		$msf_id = null;
		if(isset($data['MedStaffFact_id']))
			$msf_id = $data['MedStaffFact_id'];

		if ( isSuperadmin() && isset($data['Lpu_id']) && $data['Lpu_id'] > 0 ) {
			$lpu_id = $data['Lpu_id'];
		}
		$data = array_merge($data, getSessionParams());
		$data['MedStaffFact_id'] = $msf_id;
		if ( isSuperadmin() && isset($lpu_id) && (int)$lpu_id > 0 ) {
			$data['Lpu_id'] = $lpu_id;
		}

        if($data['allowEditLpuRegion'] == 1)
        {
            $response = $this->pcmodel->savePersonCardLpuRegion($data);
        }
        else
        {
            if ( $data['PersonCard_endDate'] != NULL )
            {
                $compare_result = swCompareDates($_POST['PersonCard_endDate'], '31.12.2039');
                if (-1 == $compare_result[0])
                {
                    $this->ReturnError('Дата открепления не должна быть больше 31.12.2039');
                    return false;
                }

                $compare_result = swCompareDates('01.01.1900', $_POST['PersonCard_endDate']);
                if (-1 == $compare_result[0])
                {
                    $this->ReturnError('Дата открепления должна быть больше 01.01.1900');
                    return false;
                }
            }
            //если прикрепляем задним числом то проставляем дату закрытия - датой прикрепленияа
            $compare_dates = swCompareDates($_POST['PersonCard_begDate'], date('d.m.Y'));
            if (1 == $compare_dates[0]) {
                $data['OldPersonCard_endDate'] = date('Y-m-d', strtotime($_POST['PersonCard_begDate']));
            }

            //die(var_dump($data['OldPersonCard_endDate']));
            // проверка общих обязательных полей
            if ( !in_array($data['LpuAttachType_id'], array(3, 4)) && empty($data['LpuRegion_id']) && $data['PersonCard_IsAttachCondit'] != 2 )
            {
                $val = array('success' => false, 'Error_Msg' => 'Не заданы обязательные поля', 'Cancel_Error_Handle' => true);
                array_walk($val, 'ConvertFromWin1251ToUTF8');
                $this->ReturnData($val);
                return false;
            }

            //https://redmine.swan.perm.ru/issues/70824
            //Для Перми проверим фондодержание
            if($_SESSION['region']['nick'] == 'perm' && $data['LpuAttachType_id'] == '1') {
                $lpu_fond_check = $this->pcmodel->checkLpuFondHolder($data);
            }
            else {
                $lpu_fond_check = true;
            }
            if($lpu_fond_check === false)
            {
                //$this->ReturnError('Для данного типа участка нет открытого периода по фондодержанию. Прикрепление не возможно', 400);
                //return false;
                $val = array('success' => false, 'Error_Code' => 333, 'Error_Msg' => 'Для данного типа участка нет открытого периода по фондодержанию. Прикрепление не возможно', 'Cancel_Error_Handle' => true);
                array_walk($val, 'ConvertFromWin1251ToUTF8');
                $this->ReturnData($val);
                return false;
            }

            //Проверим, а есть ли вообще у человека хотя бы одно активное основное прикрепление
            $resp_check = $this->pcmodel->checkAttachExists($data);
            if($resp_check === true)
            if ($data['LpuAttachType_id'] == 1 && $data['action'] == 'add' && $data['lastAttachIsNotInOurLpu'] == 'true') {
                $checkAttachPosible = $this->checkAttachPosible($data);
                if (is_array($checkAttachPosible)) {
                    $this->ReturnData($checkAttachPosible);
                    return false;
                }
            }
            //Добавляем льготу https://redmine.swan.perm.ru/issues/60393
            if (!empty($data['Person_Birthday'])){
                $birthday = strtotime($data['Person_Birthday']);

                if (!in_array(getRegionNick(),array('perm','kareliya','kz')) && strtotime("+3 year", $birthday) > time()){ //Пациенту меньше 3 лет
                    $resp_allow = $this->pcmodel->allowAddPrivilegeChild($data);

                    if($resp_allow === true){
                        $this->load->model( "Privilege_model", "ppmodel" );

                        $priv_data = array();

                        $priv_data['PrivilegeType_id'] = $this->ppmodel->getPrivilegeTypeIdBySysNick('child_und_three_year', date('Y-m-d'));

                        if ($priv_data['PrivilegeType_id'] === false) {
                            $this->ReturnError('Ошибка при добавлении льготы (получение идентификатора категории льготы)', 400);
                            return false;
                        }

                        $priv_data['Lpu_id'] = $data['Lpu_id'];
                        $priv_data['pmUser_id'] = $data['pmUser_id'];
                        $priv_data['PersonPrivilege_id'] = 0;
                        $priv_data['Person_id'] = $data['Person_id'];
                        $priv_data['Server_id'] = $data['Server_id'];
                        $priv_data['Privilege_begDate'] = date("Y-m-d", $birthday);
                        $priv_data['Privilege_endDate'] = date("Y-m-d", strtotime("+3 year", $birthday) - 60 * 60 * 24);
                        $priv_data['session'] = $data['session'];

                        $res = $this->ppmodel->savePrivilege($priv_data);

                        if ( is_array($res) && count($res) > 0 ) {
                            if ( !empty($res[0]['Error_Msg']) ) {

                                if(empty($res[0]['Error_Code']) || $res[0]['Error_Code']!='priv_exists_fed' || $res[0]['Error_Code']== 'priv_exists')
                                {
                                    $this->ReturnError($res[0]['Error_Msg'], 400);
                                    return false;
                                }
                            }
                        }
                        else {
                            $this->ReturnError('Ошибка при добавлении льготы', 400);
                            return false;
                        }
                    }
                }
            }
            $psData = $this->pcmodel->getPersonData(array(
                'Person_id' => $data['Person_id'],
                'LpuAttachType_id' => $data['LpuAttachType_id']
            ));
            //print_r($psData); die();

            $isDebug = (int)$this->config->item('IS_DEBUG');

            $this->pcmodel->beginTransaction();
            if( $data['isPersonCardAttach'] ) {
                // Сохраняем заявление
                $resp = $this->pcmodel->savePersonCardAttach(array(
                    'PersonCardAttach_id' => $data['PersonCardAttach_id']
                    ,'PersonCardAttach_setDate' => $data['PersonCard_begDate']
                    ,'Lpu_id' => $psData[0]['Lpu_id']
                    ,'Lpu_aid' => $data['Lpu_id']
                    ,'Person_id' => $psData[0]['Person_id']
                    ,'Address_id' => $psData[0]['PAddress_id']
                    ,'Polis_id' => $psData[0]['Polis_id']
                    ,'PersonCardAttach_IsSMS' => $data['PersonCardAttach_IsSMS']
                    ,'PersonCardAttach_SMS' => $data['PersonCardAttach_SMS']
                    ,'PersonCardAttach_IsEmail' => $data['PersonCardAttach_IsEmail']
                    ,'PersonCardAttach_Email' => $data['PersonCardAttach_Email']
                    ,'PersonCardAttach_IsHimself' => null
                    ,'PersonAmbulatCard_id'=>$data['PersonAmbulatCard_id']
                    ,'pmUser_id' => $data['pmUser_id']
                ));
                if( !is_array($resp) || !empty($resp[0]['Error_Msg']) ) {
                    DieWithError("Ошибка! Не удалось сохранить заявление!");
                    return;
                }
                $data['PersonCardAttach_id'] = $resp[0]['PersonCardAttach_id'];
            }

			//Слава костылям! https://redmine.swan.perm.ru/issues/74657
			if(!empty($data['PersonCard_id']) && $data['PersonCard_id'] > 0 && !empty($data['PersonCard_endDate']))
			{
				if( isset($data['isPersonCardMedicalIntervent']) ) {
					// Сохраняем отказ от видов медицинского вмешательства
					$respPCMI = $this->pcmodel->savePersonCardMedicalInterventData(array(
						'PersonCard_id' => $data['PersonCard_id'],
						'PersonCardMedicalInterventData' => $data['PersonCardMedicalInterventData'],
						'pmUser_id' => $data['pmUser_id'],
						'isPersonCardMedicalIntervent' => $data['isPersonCardMedicalIntervent']
					));
					if( !is_array($respPCMI) || !empty($respPCMI[0]['Error_Msg']) ) {
						$this->pcmodel->rollbackTransaction();
						DieWithError("Ошибка! Не удалось сохранить отказ от видов медицинского вмешательства!");
						return;
					}
				}
			}
            $response = $this->pcmodel->savePersonCard($data);
			//var_dump($response);die;
            $this->writeLog($this->createPersonCardLogText($data, $response));
            if( !is_array($response) || ( isset($response[0]) && !empty($response[0]['Error_Msg']) ) ) {
                if (!in_array($response[0]['Error_Code'], array(6,333,666))) {	//исключить отправку с кодами 6, 333, 666
                    try {
                        $this->sendEmailLogToSupport($data, $globalOptions);
                    } catch(Exception $e) {
                        $this->textlog->add("savePersonCard: Ошибка при отправке лога в саппорт");
                    }
                }
                $this->pcmodel->rollbackTransaction();
                $this->ReturnError($response[0]['Error_Msg'], $response[0]['Error_Code']);
                return;
            }
			
			if(!empty($data['PersonAmbulatCard_id'])){
				//Прикрепление амбулаторной карты к картохранилищу
				$this->load->model("PersonAmbulatCard_model", "pacmodel");
				$resUpdAttachmentAmbulatCard = $this->pacmodel->saveAttachmentAmbulatoryCardToCardStore($data);
			}
			
			//https://redmine.swan.perm.ru/issues/75030 - удаление файлов происходит теперь при нажатии на "Сохранить". Пробегаемся по pmMediaData и ищем файлы, которых уже нет на форме
			$this->load->model("PMMediaData_model", "mdmodel");
			if( empty($data['files']) && empty($res[0]['Error_Msg'] )&& $data['action'] == 'edit'){
				$par_MediaData = array(
					'ObjectID' => $data['PersonCard_id'],
					'pmMediaData_ObjectName' => 'PersonCard'
				);
				$res_cur_MediaData = $this->mdmodel->getpmMediaData($par_MediaData);
				foreach ($res_cur_MediaData as $md)
				{
					$res_del = $this->mdmodel->deletepmMediaData(array('pmMediaData_id' => $md['pmMediaData_id'], 'pmUser_id'=>$data['pmUser_id']));
				}
			}
            if( !empty($data['files']) && /*!empty($data['PersonCardAttach_id']) &&*/ empty($response[0]['Error_Msg']) ) {
                $files = explode("|", $data['files']);
				$filesNotSave = array();
                $rootDir = IMPORTPATH_ROOT . "personcardattaches/";
                $folderName = $rootDir . $data['PersonCardAttach_id'] . "/";
                if( !is_dir($folderName) ) {
                    if( !mkdir($folderName) ) {
                        DieWithError("Ошибка! Не удалось создать папку для хранения файлов заявления!");
                        return;
                    }
                }

				//https://redmine.swan.perm.ru/issues/75030 - удаление файлов происходит теперь при нажатии на "Сохранить". Пробегаемся по pmMediaData и ищем файлы, которых уже нет на форме
				if(isset($data['PersonCard_id']) && $data['action'] == 'edit'){
					$file_names = array(); $file_full_path = array();
					foreach($files as $file){
						$f = explode("::",$file);
						$file_names[] = $f[0];
						$file_full_path[] = $f[1];
					}
					$par_MediaData = array(
						'ObjectID' => $data['PersonCard_id'],
						'pmMediaData_ObjectName' => 'PersonCard'
					);
					$res_cur_MediaData = $this->mdmodel->getpmMediaData($par_MediaData);
					foreach ($res_cur_MediaData as $md)
					{
						if(!in_array($md['pmMediaData_FilePath'],$file_full_path))
						{
							$res_del = $this->mdmodel->deletepmMediaData(array('pmMediaData_id' => $md['pmMediaData_id'], 'pmUser_id'=>$data['pmUser_id']));
						}else{
							$filesNotSave[] = $md['pmMediaData_FilePath'];
						}
					}
				}

				//Закончили удаление файлов, теперь добавляем новые.
				//var_dump($response[0]);die;
                foreach($files as $file) {
                    $f = explode("::", $file);
					if(in_array($f[1], $filesNotSave)){
						//уже запись есть об этом файле
						continue;
					}
					$nameF = $f[0];
					$expansion = preg_replace("/.*?\./", '', $nameF);					
					$nameMD = md5($nameF).time();
					$nameF = $nameMD.".".$expansion;
					
                    //$file_name = $folderName . $f[0];
					$file_name = $folderName . $nameF;
                    $file_tmp_name = $f[1];
					
					$file_tmp_name=mb_convert_encoding($file_tmp_name,'cp1251');
					
                    if( is_file($file_tmp_name) ) {
                        if( !@rename( $file_tmp_name, iconv("UTF-8", "cp1251",$file_name) ) ) {
                            DieWithError("Ошибка! Не удалось сохранить файл!");
                            return;
                        }
                        $rsp = $this->mdmodel->savepmMediaData(array(
                            'pmMediaData_id' => isset($data['pmMediaData_id']) ? $data['pmMediaData_id'] : null,
                            //'ObjectName' => 'PersonCardAttach',
                            'ObjectName' => 'PersonCard',
                            //'ObjectID' => $data['PersonCardAttach_id'],
                            //'ObjectID' => isset($data['PersonCard_id'])?$data['PersonCard_id']:$response[0]['PersonCard_id'],
							'ObjectID' => isset($response[0]['PersonCard_id'])?$response[0]['PersonCard_id']:$data['PersonCard_id'],
                            'orig_name' => $f[0],
                            'file_name' => $file_name,
                            'description' => 'filesize = ' . filesize(iconv("UTF-8", "cp1251",$file_name)),
                            'pmUser_id' => $data['pmUser_id']
                        ));
                    }
                }
            }
            if( isset($data['isPersonCardMedicalIntervent']) ) {
                // Сохраняем отказ от видов медицинского вмешательства
                $respPCMI = $this->pcmodel->savePersonCardMedicalInterventData(array(
                    'PersonCard_id' => $response[0]['PersonCard_id'],
                    'PersonCardMedicalInterventData' => $data['PersonCardMedicalInterventData'],
                    'pmUser_id' => $data['pmUser_id'],
                    'isPersonCardMedicalIntervent' => $data['isPersonCardMedicalIntervent']
                ));
                if( !is_array($respPCMI) || !empty($respPCMI[0]['Error_Msg']) ) {
                    $this->pcmodel->rollbackTransaction();
                    DieWithError("Ошибка! Не удалось сохранить отказ от видов медицинского вмешательства!");
                    return;
                }
            }

			if (getRegionNick() == 'perm') {
				$this->load->model( "Privilege_model", "ppmodel");
				$this->ppmodel->autoCreatePersonPrivilege(array('Person_id' => $data['Person_id']));
			}

            if( $data['isPersonCardAttach'] && $data['action'] == 'add' && empty($response[0]['Error_Msg']) ) {
                $this->load->model("Org_model", "Org_model");
                if( !empty($psData[0]['Lpu_id']) ) {
                    $orgid = $this->Org_model->getOrgOnLpu(array('Lpu_id' => $psData[0]['Lpu_id'])); // Lpu_id != Org_id, поэтому получаем Org_id
                    $lpuOldData = $this->Org_model->getOrgData(array('Org_id' => $orgid));
                }
                $orgid = $this->Org_model->getOrgOnLpu(array('Lpu_id' => $data['Lpu_id'])); // Lpu_id != Org_id, поэтому получаем Org_id
                $lpuNewData = $this->Org_model->getOrgData(array('Org_id' => $orgid));

                $notice_settings = $this->getPersonNotificationSettings($data['Person_id']);

                if ( $globalOptions['inform_person_personcard_attach_sms'] && !empty($notice_settings) && $notice_settings !== false && !empty($notice_settings['Phones']) ) {
                    $sms_message = $psData[0]['Person_FIO'] . " прикреплен(а) в " . $lpuNewData[0]['Org_Nick'] . " с " . date("d.m.Y");

                    try{
                        foreach ($notice_settings['Phones'] as $user_id => $phone) {
                            $sendResult = $this->sendSmsMessage(array(
                                'phone' => $phone,
                                'text' => $sms_message,
                                'user_id' => $user_id
                            ));
                            if( !$sendResult ) {
                                throw new Exception("Ошибка при отправке смс-сообщения!");
                            }
                        }
                    } catch(Exception $e) {
                        $response[0]['SMS_Error_Msg'] = $e->getMessage();
                    }
                }

                $subject = "Прикрепление гражданина " . $psData[0]['Person_FIO'] . ".";
                $message_end = "\n\nЭто письмо сгенерировано автоматически, отвечать на него не нужно. РИАМС \"ПромедВеб\"";

                $this->textlog->add("");
                $this->textlog->add("savePersonCard: Прикрепление Person_id = {$data['Person_id']}");
                try{
                    if ( isset($lpuOldData) && count($lpuOldData) == 1 ) {
                        $this->textlog->add("savePersonCard: Начало уведомлений МО");
                        $this->textlog->add("savePersonCard: isDebug = ".($isDebug?'true':'false'));

                        if (!$isDebug) {
                            //1. От МО к МО2
                            $message = "Требуется подтверждение корректности информации в заявлении о прикреплении №" . $data['PersonCard_Code'] . " от " . date("d.m.Y") . " пациента " . $psData[0]['Person_FIO'];
                            if ($globalOptions['request_personcard_correction_message']) {
                                $this->sendMessageToRegService($data['pmUser_id'], $psData[0]['Lpu_id'], $subject, $message);
                            }
                            $message .= $message_end;
                            if (!empty($lpuOldData[0]['Org_Email']) && $globalOptions['request_personcard_correction_email']) {
                                $this->sendEmailMessage($lpuNewData[0]['Org_Nick'], $lpuOldData[0]['Org_Email'], $subject, $message);
                            }
                            $this->textlog->add("savePersonCard: Выполнено 1. От МО к МО2");

                            //2. От МО2 к МО
                            $message = "Данные, указанные в заявлении на прикрепление №" . $data['PersonCard_Code'] . " от " . date("d.m.Y") . " пациента " . $psData[0]['Person_FIO'] . " : корректны";
                            if ($globalOptions['request_personcard_correction_message']) {
                                $this->sendMessageToRegService($data['pmUser_id'], $data['Lpu_id'], $subject, $message);
                            }
                            $message .= $message_end;
                            if (!empty($lpuNewData[0]['Org_Email']) && $globalOptions['request_personcard_correction_email']) {
                                $this->sendEmailMessage($lpuOldData[0]['Org_Nick'], $lpuNewData[0]['Org_Email'], $subject, $message);
                            }
                            $this->textlog->add("savePersonCard: Выполнено 2. От МО2 к МО");

                            //4. От МО к МО2
                            $message = $psData[0]['Person_FIO'] . " прикреплен(а) в " . $lpuNewData[0]['Org_Nick'] . " с " . date("d.m.Y");
                            $message .= $message_end;
                            if(getRegionNick() == 'kareliya')
                            {
	                            $this->textlog->add("savePersonCard: Попытка отправки письма по пациенту ".$psData[0]['Person_FIO']);
	                            $this->textlog->add("Org_Nick_new: ".$lpuNewData[0]['Org_Nick']);
	                            $this->textlog->add("Org_Email_old: ".$lpuOldData[0]['Org_Email']);
	                            $this->textlog->add("inform_lpu_personcard_attach_email - ".$globalOptions['inform_lpu_personcard_attach_email']);
	                            $this->textlog->add("inform_lpu_personcard_attach_email_with_xml - ".$globalOptions['inform_lpu_personcard_attach_email_with_xml']);
							}
                            if (!empty($lpuOldData[0]['Org_Email']) && ($globalOptions['inform_lpu_personcard_attach_email'] || $globalOptions['inform_lpu_personcard_attach_email_with_xml'])) {
                                $attachments = array();
                                if(getRegionNick() == 'kareliya')
									$this->textlog->add("Формируем xml-файл");
                                if ($globalOptions['inform_lpu_personcard_attach_email_with_xml']) {
                                	if(getRegionNick() == 'kareliya')
										$this->textlog->add("Формируем xml-файл #2");
                                    $attachments[] = $this->createPersonCardInformXml(array(
                                        'MO' => $lpuNewData[0]['Lpu_f003mcod'],
                                        'MO_NAME' => $lpuNewData[0]['Org_Nick'],
                                        'FAM' => $psData[0]['Person_SurName'],
                                        'IM' => $psData[0]['Person_FirName'],
                                        'OT' => $psData[0]['Person_SecName'],
                                        'DR' => $psData[0]['Person_BirthDay'],
                                        'SNILS' => $psData[0]['Person_Snils'],
                                        'DP' => $data['PersonCard_begDate']
                                    ));
                                }
                                if (!$globalOptions['inform_lpu_personcard_attach_email']) {
                                    $message = '';
                                }
                                if(getRegionNick() == 'kareliya')
                                	$this->textlog->add("savePersonCard: Начинаем отправку письма");
                                $this->sendEmailMessage($lpuNewData[0]['Org_Nick'], $lpuOldData[0]['Org_Email'], $subject, $message, $attachments);
                                if(getRegionNick() == 'kareliya')
                                	$this->textlog->add("savePersonCard: Закончили отправку письма");
                            }
                            $this->textlog->add("savePersonCard: Выполнено 3. От МО к МО2");

                            //5. От МО к СМО гражданина
                            $message = $psData[0]['Person_FIO'] . " прикреплен(а) в " . $lpuNewData[0]['Org_Nick'] . " с " . date("d.m.Y");
                            $message .= $message_end;
                            if (!empty($psData[0]['OrgSmo_Email']) && $globalOptions['inform_smo_personcard_attach_email']) {
                                $this->sendEmailMessage($lpuNewData[0]['Org_Nick'], $psData[0]['OrgSmo_Email'], $subject, $message);
                            }
                            $this->textlog->add("savePersonCard: Выполнено 3. От МО к СМО");
                        } else {
                            $message = "Требуется подтверждение корректности информации в заявлении о прикреплении №" . $data['PersonCard_Code'] . " от " . date("d.m.Y") . " пациента " . $psData[0]['Person_FIO'];
                            if ($globalOptions['request_personcard_correction_message']) {
                                $this->sendMessageToRegService($data['pmUser_id'], $psData[0]['Lpu_id'], $subject, $message);
                            }

                            $message = "Данные, указанные в заявлении на прикрепление №" . $data['PersonCard_Code'] . " от " . date("d.m.Y") . " пациента " . $psData[0]['Person_FIO'] . " : корректны";
                            if ($globalOptions['request_personcard_correction_message']) {
                                $this->sendMessageToRegService($data['pmUser_id'], $data['Lpu_id'], $subject, $message);
                            }
                        }

                        $this->textlog->add("savePersonCard: Окончание уведомлений МО");
                    }

                    if( $globalOptions['inform_person_personcard_attach_email'] && !empty($notice_settings) && $notice_settings !== false && !empty($notice_settings['Emails']) ) {
                        //3. От МО гражданину
                        $message = $psData[0]['Person_FIO'] . " прикреплен(а) в " . $lpuNewData[0]['Org_Nick'] . " с " . date("d.m.Y");
                        $message .= $message_end;

                        foreach ($notice_settings['Emails'] as $user_id => $email) {
                            $this->sendEmailMessage($lpuNewData[0]['Org_Nick'], $email, $subject, $message);
                        }
                    }
                } catch(Exception $e) {
                    $response[0]['Email_Error_Msg'] = $e->getMessage();
                    $this->textlog->add("savePersonCard: exception: {$response[0]['Email_Error_Msg']}");
                    if ($e->getCode() == 20) {
                        $this->textlog->add("savePersonCard: email debugger:\n".$this->email->print_debugger());
                    }
                }
            }
            $this->pcmodel->commitTransaction();
        }
		//$this->pcmodel->commitTransaction();
		$outdata = $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->GetOutData();
		$outdata['Cancel_Error_Handle'] = true;
		$this->ReturnData($outdata);
	}

	/**
	*	Тестовая отправка письма
	*/
	function setMessageTest()
	{
		if( !isset($this->email) ) {
			$this->load->library('email');
		}
		$to_email = 'sergey.khorev@yandex.ru';
		$subject = 'Тест';
		$message = 'Тестовое сообщение';
		$from_name = 'ПЕРМЬ ГП 2.';
		$attachments[] = $this->createPersonCardInformXml(array(
            'MO' => '590701',
            'MO_NAME' => 'Тестовая МОшка',
            'FAM' => 'Ололоев',
            'IM' => 'Трололой',
            'OT' => 'Ололоевич',
            'DR' => '2001-12-05',
            'SNILS' => '00-00-0000-0000',
            'DP' => '2017-02-03'
        ));
		$error_msg = "Письмо не было отправлено!\nНе удалось выполнить соединение с почтовым сервером!";
		set_error_handler(function() use ($error_msg) { throw new Exception($error_msg, 10); }, E_ALL);
		$result = $this->email->sendPromed($to_email, $subject, $message, $from_name, $attachments);
		$this->textlog->add("Тестовая отправка: params: $to_email, $subject, $message, $from_name");
		restore_error_handler();
		if (!$result) {
			throw new Exception("Письмо не было отправлено!\nНе удалось выполнить отправление письма!", 20);
		}
	}

	/**
	 * Рассылка уведомлений
	 */
	function sendMessages(){
		$data = $this->ProcessInputData('sendMessages', true);

		$person_id = $data['Person_id'];
		$old_lpu_id = $data['Lpu_old_id'];
		$new_lpu_id = $data['Lpu_new_id'];
		$pmUser_id = $data['pmUser_id'];
		$this->load->helper('Options');
		$this->load->model("Options_model", "opmodel");
		$val  = array();
		$global_options = $this->opmodel->getOptionsGlobals(getSessionParams());
		$globalOptions = $global_options['globals'];
		$messages_data = $this->pcmodel->getDataForMessages($person_id, $old_lpu_id, $new_lpu_id);

		$person_data = $messages_data['person_data'][0];
		$old_lpu_data = $messages_data['old_lpu_data'][0];
		$new_lpu_data = $messages_data['new_lpu_data'][0];


		$subject = "Прикрепление гражданина " . $person_data['Person_FIO'] . ".";
		$message_end = "\n\nЭто письмо сгенерировано автоматически, отвечать на него не нужно. РИАМС \"ПромедВеб\"";
		$message = "Требуется подтверждение корректности информации в заявлении о прикреплении №" . $person_data['PersonCard_Code'] . " от " . date("d.m.Y") . " пациента " . $person_data['Person_FIO'];
		//От МО к МО2
		if($old_lpu_id!=0){
			if ($globalOptions['request_personcard_correction_message']) {
				$this->sendMessageToRegService($data['pmUser_id'], $old_lpu_id, $subject, $message);
			}
			$message .= $message_end;
			if (!empty($old_lpu_data['Org_Email']) && $globalOptions['request_personcard_correction_email']) {
				$this->sendEmailMessage($new_lpu_data['Org_Nick'], $old_lpu_data['Org_Email'], $subject, $message);
			}
		}

		//От МО2 к МО
		$message = "Данные, указанные в заявлении на прикрепление №" . $person_data['PersonCard_Code'] . " от " . date("d.m.Y") . " пациента " . $person_data['Person_FIO'] . " : корректны";
		if ($globalOptions['request_personcard_correction_message']) {
			$this->sendMessageToRegService($data['pmUser_id'], $new_lpu_id, $subject, $message);
		}
		$message .= $message_end;
		if (!empty($new_lpu_data['Org_Email']) && $globalOptions['request_personcard_correction_email'] && $old_lpu_id != 0) {
			$this->sendEmailMessage($old_lpu_data['Org_Nick'], $new_lpu_data['Org_Email'], $subject, $message);
		}

		//От МО к МО2
		$message = $person_data['Person_FIO'] . " прикреплен(а) в " . $new_lpu_data['Org_Nick'] . " с " . date("d.m.Y");
		$message .= $message_end;
		if (!empty($old_lpu_data['Org_Email']) && ($globalOptions['inform_lpu_personcard_attach_email'] || $globalOptions['inform_lpu_personcard_attach_email_with_xml'])) {
			$attachments = array();
			if ($globalOptions['inform_lpu_personcard_attach_email_with_xml']) {
				$attachments[] = $this->createPersonCardInformXml(array(
					'MO' => $new_lpu_data['Lpu_f003mcod'],
					'MO_NAME' => $new_lpu_data['Org_Nick'],
					'FAM' => $person_data['Person_SurName'],
					'IM' => $person_data['Person_FirName'],
					'OT' => $person_data['Person_SecName'],
					'DR' => $person_data['Person_BirthDay'],
					'SNILS' => $person_data['Person_Snils'],
					'DP' => $person_data['PersonCard_begDate']
				));
			}
			if (!$globalOptions['inform_lpu_personcard_attach_email']) {
				$message = '';
			}
			$this->sendEmailMessage($new_lpu_data['Org_Nick'], $old_lpu_data['Org_Email'], $subject, $message, $attachments);
		}

		// От МО к СМО гражданина
		$message = $person_data['Person_FIO'] . " прикреплен(а) в " . $new_lpu_data['Org_Nick'] . " с " . date("d.m.Y");
		$message .= $message_end;
		if (!empty($person_data['OrgSmo_Email']) && $globalOptions['inform_smo_personcard_attach_email']) {
			$this->sendEmailMessage($new_lpu_data['Org_Nick'], $person_data['OrgSmo_Email'], $subject, $message);
		}

		//От МО гражданину
		$notice_settings = $this->getPersonNotificationSettings($person_id);
		if( $globalOptions['inform_person_personcard_attach_email'] && !empty($notice_settings) && $notice_settings !== false && !empty($notice_settings['Emails']) ) {
			$message = $person_data['Person_FIO'] . " прикреплен(а) в " . $new_lpu_data['Org_Nick'] . " с " . date("d.m.Y");
			$message .= $message_end;

			foreach ($notice_settings['Emails'] as $user_id => $email) {
				$this->sendEmailMessage($new_lpu_data['Org_Nick'], $email, $subject, $message);
			}
		}
	}

	/**
	 * Создание xml для информирования о прикреплении
	 */
	function createPersonCardInformXml($data) {
		$data['DATA'] = date('Y-m-d');
		$data['MO'] = !empty($data['MO']) ? $data['MO'] : 'NOCODE';

		$templ = 'person_card_email_inform';

		$this->load->library('parser');
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n" . $this->parser->parse('export_xml/' . $templ, $data, true);
		if(getRegionNick() == 'kareliya')
		{
			$this->textlog->add("Формируем xml-файл #3 MO ". $data['MO']);
			$this->textlog->add("Формируем xml-файл #3 MO_NAME ". $data['MO_NAME']);
			$this->textlog->add("Формируем xml-файл #3 FAM ". $data['FAM']);
			$this->textlog->add("Формируем xml-файл #3 IM ". $data['IM']);
			$this->textlog->add("Формируем xml-файл #3 OT ". $data['OT']);
			$this->textlog->add("Формируем xml-файл #3 DR ". $data['DR']);
			$this->textlog->add("Формируем xml-файл #3 SNILS ". $data['SNILS']);
			$this->textlog->add("Формируем xml-файл #3 DP ". $data['DP']);
		}

		$lpu_code = $data['MO'];

		$file_name = $lpu_code.'_'.date('Ymd');
		$out_dir = $file_name.'_'.time();
		$path = EXPORTPATH_ROOT.'person_card_inform/' . $out_dir;
		$file_path = $path.'/'.$file_name.'.xml';
		mkdir($path, 0777, true);
		if(getRegionNick() == 'kareliya')
		{
			$this->textlog->add("Файл - ". $file_path);
		}
		file_put_contents($file_path, $xml);

		//Не архивировать xml https://redmine.swan.perm.ru/issues/61021#note-36
		/*$file_zip_name = EXPORTPATH_ROOT.'person_card_inform/' . $out_dir . '/' . $file_name . '.zip';
		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFromString($file_name.'.xml', $xml);
		$zip->close();*/

		return $file_path;
	}

    /**
     * Автоматическое прикрепление (на входе - параметры прикрепления и массив ЗЛ, которых необнодимо прикрепить.
     * На выходе - лог о результатах прикреплений
     */
    function savePersonCardAuto(){
        $data = $this->ProcessInputData('savePersonCardAuto', true);
        $data['PersonCardAttach'] = 0;
        $data['IsAttachCondit'] = 0;
		if($data['PC_type'] == '1'){
			$data['PersonCardAttach'] = 1;
			$data['IsAttachCondit'] = 0;
		}
		if($data['PC_type'] == '0'){
			$data['PersonCardAttach'] = 0;
			$data['IsAttachCondit'] = 1;
		}
        if ($data === false) { return false; }
        $person_ids_array = $dt = (array) json_decode($data['Person_ids_array']);;
        $params = $data;
        $result_string = array();
        for ($i=0; $i<count($person_ids_array); $i++){
            $params['Person_id'] = $person_ids_array[$i];

            $psData = $this->pcmodel->getPersonData(array(
                'Person_id' => $params['Person_id'],
                'LpuAttachType_id' => 1
            ));
			//Добавление льготы для детей от 3 лет, при условии что они БДЗшные и это прикрепление будет для них первым
			$result_add_3year_priv = '';
			$params['Person_Birthday'] = $psData[0]['Person_BirthDay'];
			if (!empty($params['Person_Birthday'])){
				$birthday = strtotime($params['Person_Birthday']);
				if (!in_array(getRegionNick(), array('perm','kareliya','kz')) && strtotime("+3 year", $birthday) > time()){ //Пациенту меньше 3 лет
					$resp_allow = $this->pcmodel->allowAddPrivilegeChild($params);
					if($resp_allow === true){
						$this->load->model( "Privilege_model", "ppmodel" );

						$priv_data = array();

						$priv_data['PrivilegeType_id'] = $this->ppmodel->getPrivilegeTypeIdBySysNick('child_und_three_year', date('Y-m-d'));

						if ($priv_data['PrivilegeType_id'] === false) {
							$result_add_3year_priv = ' (льгота "Дети до 3 лет" НЕ ДОБАВЛЕНА; причина - ошибка в получении идентификатора категории льготы)';
						}

						$priv_data['Lpu_id'] = $data['Lpu_id'];
						$priv_data['pmUser_id'] = $data['pmUser_id'];
						$priv_data['PersonPrivilege_id'] = 0;
						$priv_data['Person_id'] = $params['Person_id'];
						$priv_data['Server_id'] = $data['Server_id'];
						$priv_data['Privilege_begDate'] = date("Y-m-d", $birthday);
						$priv_data['Privilege_endDate'] = date("Y-m-d", strtotime("+3 year", $birthday) - 60 * 60 * 24);
						$priv_data['session'] = $data['session'];

						$res = $this->ppmodel->savePrivilege($priv_data);

						if ( is_array($res) && count($res) > 0 ) {
							if ( !empty($res[0]['Error_Msg']) ) {

								if(empty($res[0]['Error_Code']) || $res[0]['Error_Code']!='priv_exists_fed' || $res[0]['Error_Code']== 'priv_exists')
								{
									$result_add_3year_priv = ' (льгота "Дети до 3 лет" НЕ ДОБАВЛЕНА; причина - ошибка при добавлении льготы [2])';
								}
							}
							else
							{
								$result_add_3year_priv = ' (льгота "Дети до 3 лет" ДОБАВЛЕНА';
							}
						}
						else {
							$result_add_3year_priv = ' (льгота "Дети до 3 лет" НЕ ДОБАВЛЕНА; причина - ошибка при добавлении льготы [2])';
						}
					}
					else
						$result_add_3year_priv = ' (льгота "Дети до 3 лет" НЕ ДОБАВЛЕНА; причина - невозможно добавить эту льготу данному человеку)';
				}
			}
			//Закончили добалять льготу, движемся дальше
            $isDebug = (int)$this->config->item('IS_DEBUG');
            $params['PersonCardAttach_id'] = null;
            $params['PersonCard_begDate'] = date('Y-m-d H:i:00.000');
            if( $data['PersonCardAttach'] ) {
                // Сохраняем заявление
                $resp = $this->pcmodel->savePersonCardAttach(array(
					'PersonAmbulatCard_id' => ''
					,'PersonCardAttach_id' => $params['PersonCardAttach_id']
					,'PersonCardAttach_setDate' => $params['PersonCard_begDate']
					,'Lpu_id' => $psData[0]['Lpu_id']
					,'Lpu_aid' => $data['Lpu_id']
					,'Address_id' => $psData[0]['PAddress_id']
					,'Polis_id' => $psData[0]['Polis_id']
					,'Person_id' => $psData[0]['Person_id']
					,'PersonCardAttach_IsSMS' => 0
					,'PersonCardAttach_SMS' => ''
					,'PersonCardAttach_IsEmail' => 0
					,'PersonCardAttach_Email' => ''
					,'PersonCardAttach_IsHimself' => null
					,'pmUser_id' => $data['pmUser_id']
                ));
                if( !is_array($resp) || !empty($resp[0]['Error_Msg']) ) {
                }
                $params['PersonCardAttach_id'] = $resp[0]['PersonCardAttach_id'];
            }
            $result = $this->pcmodel->savePersonCardAuto($params);
			if(strpos($result[0]['string'],'НЕ ПРИКРЕПЛЕН') >= 0){ //Удаляем созданное ранее заявление
				$this->pcmodel->deletePersonCardAttach($params);
			}
			if(strpos($result[0]['string'],'prev_params') >= 0) {
				$this->pcmodel->deletePersonCardAttach($params);
				$result[0]['string'] = str_replace('prev_params','',$result[0]['string']);
			}
			if (getRegionNick() == 'perm') {
				$this->load->model( "Privilege_model", "ppmodel");
				$this->ppmodel->autoCreatePersonPrivilege(array('Person_id' => $params['Person_id']));
			}
            $result_string[$i] = $result[0]['string'].$result_add_3year_priv;
        }
        $this->ReturnData($result_string);
    }

	/**
	 * Возвращает массивы телефонных номеров и email для рассылки сообщений
	 */
	function getPersonNotificationSettings($person_id) {
		if (!$this->config->item('USER_PORTAL_NOTIFICATION')) {
			return false;
		}
		$this->load->model("UserPortal_model", "upmodel");
		$notice_settings = $this->upmodel->getPromedPersonNotificationSettings($person_id);

		$phones = array();
		$emails = array();
		$response = array();
        if(is_array($notice_settings)){
            foreach($notice_settings as $notify) {
                $user_id = $notify['user_id'];
                if ($notify['attach_sms'] == 1 && !empty($notify['phone'])) {
                    $phones[$user_id] = $notify['phone'];
                }
                if ($notify['attach_email'] == 1 && !empty($notify['email'])) {
                    $emails[$user_id] = $notify['email'];
                }
            }
            $response['Phones'] = array_unique($phones);
            $response['Emails'] = array_unique($emails);

            return $response;
        }
        else return false;
	}

	/**
	 * Формироание текста для записи в лог
	 */
	function createPersonCardLogText($data, $response) {
		$this->load->model('Lpu_model');
		$text_arr = array();

		$text_arr[] = 'Дата: '.date('d.m.Y H:i');
		$text_arr[] = 'Пользователь: '.$data['session']['login'];
		$text_arr[] = 'ИД пациента: '.$data['Person_id'];

		$res = $this->Lpu_model->getDataForPersonCardLogText($data);
		if (!empty($response[0]['Error_Msg'])) {
			$text_arr[] = 'Текст ошибки: '.$response[0]['Error_Msg'];
		}
		$text_arr = array_merge($text_arr, $res);
		$text_arr[] = "\n";

		return implode("\n", $text_arr);
	}

	/**
	 * Отправка лога в саппорт
	 */
	function sendEmailLogToSupport($data, $globalOptions) {
		$email_list = explode(',', $globalOptions['person_card_log_email_list']);

		if (count($email_list) == 0) {
			return;
		}

		$date = date('d.m.Y H:i');
		$Lpu_Nick = $data['session']['setting']['server']['lpu_nick'];
		$pmUser_Login = $data['session']['login'];

		$subject = "Ошибки прикрепления";
		$msg = $date."\n";
		$msg .= $Lpu_Nick."\n";
		$msg .= $pmUser_Login."\n";
		$msg .= "\nОшибки прикрепления во вложенном файле.";

		foreach($email_list as $email) {
			$email = trim($email);

			$this->sendEmailMessage($Lpu_Nick, $email, $subject, $msg, array($this->file_log));
		}
	}

	/**
	 * Рассылка сообщений работникам регистратуры ЛПУ при прикреплении пациента
	 */
	function sendMessageToRegService($from_pmuser_id, $to_lpu_id, $subject, $message) {
		if (!isset($this->Messages_model)) {
			$this->load->model('Messages_model', 'Messages_model');
		}
		if (!isset($this->MedService_model)) {
			$this->load->model('MedService_model', 'MedService_model');
		}

		$MedPersonalList = $this->MedService_model->loadMedServiceMedPersonalList(array(
			'Lpu_id' => $to_lpu_id,
			'MedServiceType_SysNick' => 'regpol'
		));

		$noticeResponse = array();
		$sentMedPersonalIds = array();
		foreach($MedPersonalList as $MedPersonal) {
			if (in_array($MedPersonal['MedPersonal_id'], $sentMedPersonalIds)) {
				continue;
			}
			$noticeData = array(
				'Lpu_rid' => $to_lpu_id,
				'MedPersonal_rid' => $MedPersonal['MedPersonal_id'],
				'autotype' => 5,
				'pmUser_id' => $from_pmuser_id,
				'type' => 6,
				'title' => $subject,
				'text' => $message
			);
			$noticeResponse[] = $this->Messages_model->autoMessage($noticeData);
			$sentMedPersonalIds[] = $MedPersonal['MedPersonal_id'];
		}

		return $noticeResponse;
	}

	/**
	* Отправляет сообщение по эл. почте
	*/
	function sendEmailMessage($from_name = "", $to_email, $subject, $message, $attachments = array()) {
		if( !isset($this->email) ) {
			$this->load->library('email');
		}
		if( empty($to_email) ) {
			return;
		}

		//set_error_handler(function() use ($error_msg) { throw new Exception($error_msg, 10); }, E_ALL);
		// create_function устарела начиная с 7.2 заменил на анонимную функцию #175136
		set_error_handler(function (){
			$error_msg = "Письмо не было отправлено!\nНе удалось выполнить соединение с почтовым сервером!";
			throw new Exception($error_msg, 10);
		}, E_ALL);
		$result = $this->email->sendPromed($to_email, $subject, $message, $from_name, $attachments);
		$this->textlog->add("emailSendPromed: params: $to_email, $subject, $message, $from_name");
		restore_error_handler();
		/*if (!$result) {
			throw new Exception("Письмо не было отправлено!\nНе удалось выполнить отправление письма!", 20);
		}*/
	}

	/**
	* Отправляет sms-сообщение
	*/
	function sendSmsMessage($data) {
		if (empty($data)) {
			return false;
		}

		$error_msg = "Ошибка при отправке смс-сообщения!";

		set_error_handler(function() use ($error_msg) { throw new Exception($error_msg); }, E_ALL & ~E_NOTICE);
		$this->load->helper('Notify');
		sendNotifySMS(array(
			'User_id' => $data['user_id'],
			'UserNotify_Phone' => $data['phone'],
			'text' => $data['text']
		));
		restore_error_handler();

		return true;
	}
	
	/**
	*	Загружает файл на сервер
	*/
	function uploadFiles() {
		if(!isset($_FILES['file'])) {
			echo json_encode(array('success' => false, 'error' => toUTF('Ошибка загрузки файла! file not transferred')));
			return false;
		}
		if((int)$_FILES['file']['size'] > 2097152) {
			echo json_encode(array('success' => false, 'error' => toUTF('Запрещено загружать файлы размером более 2 мб!')));
			return false;
		}
		if($_FILES['file']['tmp_name'] == '') {
			echo json_encode(array('success' => false, 'error' => toUTF('Ошибка загрузки файла! missing file')));
			return false;
		}
		$newfile = explode(DIRECTORY_SEPARATOR,$_FILES['file']['tmp_name']);

		$attachFilesDir = IMPORTPATH_ROOT . "personcardattaches/";
		if(!is_dir(IMPORTPATH_ROOT)){
			if( !mkdir(IMPORTPATH_ROOT) ) return;
		}
		if( !is_dir($attachFilesDir) ) {
			if( !mkdir($attachFilesDir) ) {
				return;
			}
		}
		
		//$newname = $attachFilesDir . str_replace('.', '', $newfile[count($newfile)-1]).rand(1,10000).'.tmp';
		// говорят в #140222, что тут двойной слеш может появиться, проверим
		//$newname = preg_replace('|([/]+)|s', '/', $newname);
		
		$filename = uniqid();
		$newname = $attachFilesDir.$filename.'.tmp';
		
		$flag = @rename($_FILES['file']['tmp_name'], $newname);
		if(!$flag) {
			$rightsAttachFilesDir = substr(decoct(fileperms($attachFilesDir)), -3);
			$writable = (is_writable($attachFilesDir)) ? 1 : 0;
			echo json_encode(array('success' => false, 'error' => toUTF('Ошибка загрузки файла!!! rightsAttachFilesDir: '.$rightsAttachFilesDir.'.  is_writable: '.$writable.'. File: '.$newname)));
			return false;
		}
		
		$val = array(
			'name'		=> toUTF($_FILES['file']['name']),
			'tmp_name'	=> toUTF($newname),
			'size'		=> $_FILES['file']['size'],
			'success'	=> true
		);
		$this->ReturnData($val);
	}
	
	
	/**
	*	Удаляет файл с сервера
	*/
	function deleteFile() {
		$data = $this->ProcessInputData('deleteFile', true);
		if( $data === false ) return false;
		$data['url'] = iconv("UTF-8", "cp1251",$data['url']);
		if( file_exists($data['url']) ) {
			$this->load->model("PMMediaData_model", "mdmodel");
			$resp = $this->mdmodel->getpmMediaData(array(
				'ObjectID' => (int) trim(mb_substr(dirname($data['url']), strlen(IMPORTPATH_ROOT . "personcardattaches/")))
			));
			if(is_array($resp) && isset($resp[0]['pmMediaData_id'])) {
				// Удаляем файл из pmMediaData
				$resp = $this->mdmodel->deletepmMediaData(array('pmMediaData_id' => $resp[0]['pmMediaData_id'], 'pmUser_id'=>$data['pmUser_id']));
			}
			$this->ReturnData(array('success' => unlink($data['url'])));
		} else {
			DieWithError('Файла с таким именем не существует!');
			return false;
		}
	}

	/**
	 * Удалить файл из PersonCard
	 */
	function deleteFileFromPersonCard(){
		$data = $this->ProcessInputData('deleteFile',true);
		if( $data === false ) return false;
		$data['url'] = iconv("UTF-8", "cp1251",$data['url']);
		$this->load->model("PMMediaData_model","mdmodel");
		$resp = $this->mdmodel->deletepmMediaData(array('pmMediaData_id' => $data['pmMediaData_id'], 'pmUser_id'=>$data['pmUser_id']));
		if(file_exists($data['url'])){
			unlink($data['url']);
		}
		$this->ReturnData(array('success' => true));
	}

	/**
	 * Функция сохранения DMS-карты пациента.
	 * Входящие данные: $_POST с данными карты
	 * Используется: форма редактирования карты пациента
	 */
	function savePersonCardDms()
	{
		$this->load->helper('Options');
		$this->load->model("Options_model", "opmodel");
		
        $data = array();
        $val  = array();

		$data = $this->ProcessInputData('savePersonCardDms', false);
		if ($data === false) { return false; }

		$global_options = $this->opmodel->getOptionsGlobals($data);
		$globalOptions = $global_options['globals'];
		
		if ( isSuperadmin() && isset($data['Lpu_id']) && $data['Lpu_id'] > 0 ) {
			$lpu_id = $data['Lpu_id'];
		}
		
		$data = array_merge($data, getSessionParams());

		if ( isSuperadmin() && isset($lpu_id) && (int)$lpu_id > 0 ) {
			$data['Lpu_id'] = $lpu_id;
		}
		
		//$data['PersonCard_begDate'] = date('Y-m-d H:i:00.000');

        if ( $data['PersonCard_endDate'] != NULL )
        {
			$compare_result = swCompareDates($_POST['PersonCard_endDate'], '31.12.2039');
			if (-1 == $compare_result[0])
			{
				$val = array('success' => false, 'Error_Msg' => 'Дата открепления не должна быть больше 31.12.2039');
				array_walk($val, 'ConvertFromWin1251ToUTF8');
				$this->ReturnData($val);
				return false;
			}

			$compare_result = swCompareDates('01.01.1900', $_POST['PersonCard_endDate']);
			if (-1 == $compare_result[0])
			{
				$val = array('success' => false, 'Error_Msg' => 'Дата открепления должна быть больше 01.01.1900');
				array_walk($val, 'ConvertFromWin1251ToUTF8');
				$this->ReturnData($val);
				return false;
			}
        }				
		
		// забираем настройку возможности прикрепления только по истечении года после последнего прикрепления
		// IVP 01.09.2010 - настройка теперь в настройках сервера хранится в константах
		
		//$options = getOptions();
		//$data['check_attach_if_year_expire'] = false;

        $response = $this->pcmodel->savePersonCardDms($data);
		$this->writeLog($this->createPersonCardLogText($data, $response));
		if (!empty($response[0]['Error_Msg']) && !in_array($response[0]['Error_Code'], array(6,333,666))) {	//исключить отправку с кодами 6, 333, 666
			try {
				$this->sendEmailLogToSupport($data, $globalOptions);
			} catch(Exception $e) {
				//
			}
		}
		$outdata = $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->GetOutData();
		$outdata['Cancel_Error_Handle'] = true;
		$this->ReturnData($outdata);	
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function checkPersonCard(){
		$data = $this->ProcessInputData('checkPersonCard', true);
		if ($data === false) { return false; }
		
		$response = $this->pcmodel->checkPersonCard($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Получение данных карты человека
	 */
	function getPersonCard()
	{
		$data = $this->ProcessInputData('getPersonCard', true);
		if ($data === false) { return false; }
		
		$response = $this->pcmodel->getPersonCard($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получентие номера участка, в рамках задачи 9295
	 */
	function getLpuRegion()
	{
		$data = $this->ProcessInputData('getLpuRegion', true);
		if ($data === false) {return false;}

		$response = $this->pcmodel->getLpuRegion($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Получение участка по адресу человека
	*/
	function getLpuRegionByAddress(){
		$data = $this->ProcessInputData('getLpuRegionByAddress', true);
		if ($data === false) {return false;}

		$response = $this->pcmodel->getLpuRegionByAddress($data);
		$this->ProcessModelList($response, true, true)->ReturnData();	
	}

	/**
	 * Закрытие карты
	 */
	function closePersonCard()
	{
		$data = $this->ProcessInputData('closePersonCard', true);
		if ($data === false) { return false; }

		$response = $this->pcmodel->closePersonCard($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Открепление, только если пациент не из БДЗ и прикрепление соответствует ЛПУ текущего пользователя.
	function closePersonCardNotBdz()
	{
		$data = $this->ProcessInputData('closePersonCard', true);
		if ($data === false) { return false; }

		$response = $this->pcmodel->closePersonCardNotBdz($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	 */

	/**
	 * Получение кода карты
	 */
	function getPersonCardCode()
	{
		$data = $this->ProcessInputData('getPersonCardCode', false);
		if ($data === false) { return false; }

		if ( isSuperadmin() && isset($data['Lpu_id']) && $data['Lpu_id'] > 0 ) {
			$lpu_id = $data['Lpu_id'];
		}
		
		$data = array_merge($data, getSessionParams());

		if ( isSuperadmin() && isset($lpu_id) && (int)$lpu_id > 0 ) {
			$data['Lpu_id'] = $lpu_id;
		}

		$info = $this->pcmodel->getPersonCardCode($data);
		$outdata = $this->ProcessModelList($info, true, true)->GetOutData();
		//$outdata[0]['success'] = true;
		$this->ReturnData($outdata[0]);
	}

	/**
	 * Проверка существует ли карта
	 */
	/* нигде не используется? закомментил.
	function checkIfPersonCardIsExists()
	{
		$data = $this->ProcessInputData('checkIfPersonCardIsExists', true);
		if ($data === false) { return false; }

		$info = $this->pcmodel->checkIfPersonCardIsExists($data);
		if ( $info && $info[0]['cnt'] > 0 )
			echo "true";
		else
			echo "false";

	}
	*/

	/**
	 * Проверка кода карты 
	 */
	function checkPersonCardCode()
	{
		$data = $this->ProcessInputData('checkPersonCardCode', true);
		if ($data === false) { return false; }
		
		$info = $this->pcmodel->checkPersonCardCode($data);
		if ($info) { echo $info[0]['chck']; }
	}

	/**
	 * Удаление прикрепления
	 */
	function deletePersonCard() {
		$data = $this->ProcessInputData('deletePersonCard', true);
		if ($data === false) { return false; }
		$this->load->model("Options_model", "opmodel");
		$data = array_merge($data, getSessionParams());
		$global_options = $this->opmodel->getOptionsGlobals($data);
		$globalOptions = $global_options['globals'];
		//$globalOptions = $this->pcmodel->globalOptions['globals'];

		$isDebug = (int)$this->config->item('IS_DEBUG');
		
		$pcAttachData = $this->pcmodel->getPersonCardAttachOnPersonCard($data);
		if( !is_array($pcAttachData) || empty($pcAttachData[0]['Person_id']) ) {
			DieWithError("Ошибка БД!");
			return;
		}

		// Проверяем что прикрепление не последнее
		if ( !isset($data['isLastAttach']) || ($data['isLastAttach'] != 2) ) {

			$attachCount = $this->pcmodel->getFirstResultFromQuery("
				select count(*)
				from
					PersonCard PC (nolock)
					 inner join LpuAttachType LAT with (nolock) on PC.LpuAttachType_id = LAT.LpuAttachType_id
				where
					PC.Person_id = (select top 1 Person_id from PersonCard where PersonCard_id = :PersonCard_id)
					and LAT.LpuAttachType_SysNick in ('main', 'gin', 'stom')", $data);
			if ($attachCount == 1){
				$this->ProcessModelList(array(array('success'=>false, 'Error_Msg'=>'Данное прикрепление является последним для текущего пациента. Удалить прикрепление?', 'Error_Code' => 777)), true, true)->ReturnData();
				return;
			}
		}

		$psData = $this->pcmodel->getPersonData(array(
			'Person_id' => $pcAttachData[0]['Person_id'],
			'LpuAttachType_id' => $pcAttachData[0]['LpuAttachType_id']
		));
		$this->load->model("Org_model", "Org_model");
		$orgid = $this->Org_model->getOrgOnLpu(array('Lpu_id' => $psData[0]['Lpu_id'])); // Lpu_id != Org_id, поэтому получаем Org_id
		$lpuNewData = $this->Org_model->getOrgData(array('Org_id' => $orgid));
		
		$this->pcmodel->beginTransaction();

		$response = $this->pcmodel->deleteAllPersonCardMedicalIntervent($data);
		if( !is_array($response) || !empty($response[0]['Error_Msg']) ) {
			$this->pcmodel->rollbackTransaction();
			DieWithError("Ошибка! Не удалось удалить отказ от видов медицинских вмешательств!");
			return;
		}

		$response = $this->pcmodel->deletePersonCard($data);
		if( !is_array($response) || !empty($response[0]['Error_Msg']) ) {
			$this->pcmodel->rollbackTransaction();
			DieWithError("Ошибка! Не удалось удалить прикрепление!");
			return;
		}

		if($data['tryOpen'] == 1){ //https://redmine.swan.perm.ru/issues/52919
			$this->pcmodel->commitTransaction();
			$outdata = $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->GetOutData();
			$outdata['Cancel_Error_Handle'] = true;
			$this->ReturnData($outdata);
			return true;
		}

		if( !empty($pcAttachData[0]['PersonCardAttach_id']) && empty($pcAttachData[0]['PersonCard_endDate'])) {
			$rp = $this->pcmodel->deletePersonCardAttach($pcAttachData[0]);
			if( !is_array($rp) || !empty($rp[0]['Error_Msg']) ) {
				$this->pcmodel->rollbackTransaction();
				DieWithError("Ошибка! Не удалось удалить заявление!");
				return;
			}
			
			$this->load->model("PMMediaData_model", "mdmodel");
			$resp = $this->mdmodel->getpmMediaData(array(
				'ObjectID' => $pcAttachData[0]['PersonCardAttach_id']
			));
			if( is_array($resp) ) {
				$rootDir = IMPORTPATH_ROOT . "personcardattaches/";
				$attachFilesDir = $rootDir . $pcAttachData[0]['PersonCardAttach_id'] . "/";
				if(is_dir($attachFilesDir)) {
					if( $op = opendir($attachFilesDir) ) {
						while (false !== ($file = readdir($op))) {
							$file_url = $attachFilesDir . $file;
							if( is_file($file_url) ) {
								if (!@unlink($file_url) ) {
									DieWithError("Ошибка! Не удалось удалить файл!");
									return;
								}
							}
						}
						closedir($op);
						rmdir($attachFilesDir);
					}
				}
				foreach($resp as $r) {
					$r['pmUser_id']=$data['pmUser_id'];
					$delResp = $this->mdmodel->deletepmMediaData($r);
				}
			}
		}


		/*$response = $this->pcmodel->deletePersonCard($data);
		if( !is_array($response) || !empty($response[0]['Error_Msg']) ) {
			$this->pcmodel->rollbackTransaction();
			DieWithError("Ошибка! Не удалось удалить прикрепление!");
			return;
		}

		if($data['tryOpen'] == 1){ //https://redmine.swan.perm.ru/issues/52919
			$this->pcmodel->commitTransaction();
			$outdata = $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->GetOutData();
			$outdata['Cancel_Error_Handle'] = true;
			$this->ReturnData($outdata);
			return true;
		}*/

		$this->pcmodel->commitTransaction();
		
		// По новой получеам информацию об ЛПУ прикрепления, так как последняя запись уже удалена, а необходимо узнать информацию о предпоследней
		$psData = $this->pcmodel->getPersonData(array(
			'Person_id' => $pcAttachData[0]['Person_id'],
			'LpuAttachType_id' => $pcAttachData[0]['LpuAttachType_id']
		));
		$orgid = $this->Org_model->getOrgOnLpu(array('Lpu_id' => $psData[0]['Lpu_id'])); // Lpu_id != Org_id, поэтому получаем Org_id
		$lpuOldData = $this->Org_model->getOrgData(array('Org_id' => $orgid));

		$notice_settings = $this->getPersonNotificationSettings($pcAttachData[0]['Person_id']);

		$this->textlog->add("");
		$this->textlog->add("deletePersonCard: Отмена прикрепления Person_id = {$pcAttachData[0]['Person_id']}");
		
		// отправка сообщений по e-mail
		$subject = "Отмена прикрепления гражданина " . $psData[0]['Person_FIO'] . ".";
		$message_end = "\n\nЭто письмо сгенерировано автоматически, отвечать на него не нужно. РИАМС \"ПромедВеб\"";

		$this->textlog->add("deletePersonCard: Начало уведомлений");
		$this->textlog->add("deletePersonCard: isDebug = ".($isDebug?'true':'false'));

		try{
			if (!$isDebug) {
				if(is_array($lpuNewData) && count($lpuNewData) > 0 )
				{
					// От МО к МО2
					$message = "Пациент " . ((!empty($psData[0]) && !empty($psData[0]['Person_FIO']))?$psData[0]['Person_FIO']:'') . " перекреплен ошибочно из " . ((!empty($lpuOldData[0]) && !empty($lpuOldData[0]['Org_Nick']))?$lpuOldData[0]['Org_Nick']:'') . " в " . ((!empty($lpuNewData[0]) && !empty($lpuNewData[0]['Org_Nick']))?$lpuNewData[0]['Org_Nick']:'') . ". Дата: " . date("d.m.Y") . ". Прикрепление отменено";
					$message .= $message_end;
					if (count($lpuOldData) > 0 && !empty($lpuOldData[0]['Org_Email']) && ($globalOptions['inform_lpu_personcard_attach_email'] || $globalOptions['inform_lpu_personcard_attach_email_with_xml'])) {
						$attachments = array();
						if ($globalOptions['inform_lpu_personcard_attach_email_with_xml']) {
							$attachments[] = $this->createPersonCardInformXml(array(
								'MO' => $lpuNewData[0]['Lpu_f003mcod'],
								'MO_NAME' => $lpuNewData[0]['Org_Nick'],
								'FAM' => $psData[0]['Person_SurName'],
								'IM' => $psData[0]['Person_FirName'],
								'OT' => $psData[0]['Person_SecName'],
								'DR' => $psData[0]['Person_BirthDay'],
								'SNILS' => $psData[0]['Person_Snils'],
								'DP' => date('Y-m-d')
							));
						}
						if (!$globalOptions['inform_lpu_personcard_attach_email']) {
							$message = '';
						}

						$this->sendEmailMessage($lpuNewData[0]['Org_Nick'], $lpuOldData[0]['Org_Email'], $subject, $message, $attachments);
					}
					$this->textlog->add("deletePersonCard: Выполнено 1. От МО к МО2");

					// От МО к CМО
					if (!empty($psData[0]['OrgSmo_Email']) && $globalOptions['inform_smo_personcard_attach_email']) {
						$this->sendEmailMessage($lpuNewData[0]['Org_Nick'], $psData[0]['OrgSmo_Email'], $subject, $message);
					}
					$this->textlog->add("deletePersonCard: Выполнено 2. От МО к CМО");

					if ($globalOptions['inform_person_personcard_attach_email'] && !empty($notice_settings) && $notice_settings !== false && !empty($notice_settings['Emails'])) {
						// от МО на e-mail гражданина
						foreach($notice_settings['Emails'] as $user_id => $email) {
							$this->sendEmailMessage($lpuNewData[0]['Org_Nick'], $email, $subject, $message);
						}
					}
				}
			}
		} catch(Exception $e){
			$response[0]['Email_Error_Msg'] = $e->getMessage();
			$this->textlog->add("deletePersonCard: exception: {$response[0]['Email_Error_Msg']}");
			if ($e->getCode() == 20) {
				$this->textlog->add("savePersonCard: email debugger:\n".$this->email->print_debugger());
			}
		}

		$this->textlog->add("deletePersonCard: Окончание уведомлений");

		// отправка sms-сообщений
		if($globalOptions['inform_person_personcard_attach_sms'] && count($lpuOldData) > 0 && !empty($notice_settings) && $notice_settings !== false && !empty($notice_settings['Phones'])) {
			$sms_message = "Пациент " . $psData[0]['Person_FIO'] . " прикреплен к " . $lpuOldData[0]['Org_Nick'] . " с " . $psData[0]['PersonCard_begDate'];
			try{
				foreach($notice_settings['Phones'] as $user_id => $phone) {
					$sendResult = $this->sendSmsMessage(array(
						'phone' => $phone,
						'text' => $sms_message,
						'user_id' => $user_id
					));
					if( !$sendResult ) {
						throw new Exception("Ошибка при отправке смс-сообщения!");
					}
				}
			} catch(Exception $e) {
				$response[0]['SMS_Error_Msg'] = $e->getMessage();
			}
		}
		
		$outdata = $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->GetOutData();
		$outdata['Cancel_Error_Handle'] = true;
		$this->ReturnData($outdata);
	}
	
	/**
	 * Удаление прикрепления ДМС
	 */
	function deleteDmsPersonCard()
	{
		$data = $this->ProcessInputData('deleteDmsPersonCard', true);
		if ($data === false) { return false; }
		
		$response = $this->pcmodel->deleteDmsPersonCard($data);
		$outdata = $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->GetOutData();
		$outdata['Cancel_Error_Handle'] = true;
		$this->ReturnData($outdata);		
	}

	/**
	 * Получение списка прикреплений
	 */
	function getPersonCardStateGrid()
	{
		unset($this->db);
		$this->load->database('search');
		$data = $this->ProcessInputData('getPersonCardStateGrid', true);
		if ($data === false) { return false; }
		
		$response = $this->pcmodel->getPersonCardStateGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Функция получения списка карт пациентов, в зависимости от фильтров.
	 * Входящие данные: $_POST с фильтрами
	 * На выходе: JSON-строка
	 * Используется: форма редактирования рецепта
	 */
	function GetDetailList()
	{
		$data = $this->ProcessInputData('GetDetailList', true);
		if ($data === false) { return false; }

		$info = $this->pcmodel->getPersonCardDetailList($data);
		if (isset($data['start']) && isset($data['limit'])) {
			$this->ProcessModelList($info, true, true)->ReturnLimitData(NULL, $data['start'], $data['limit']);
		} else {
			$this->ProcessModelList($info, true, true)->ReturnData();
		}
	}
	
	/**
	 * Функция выводит форму №025/у-04 (медицинская карта)
	 */
	function printMedCard() {
		$this->load->library('parser');

		$view = '';
		$val = array();

		$data = $this->ProcessInputData('printMedCard', true);
		if ($data === false) { return false; }

		if(!(bool)$data['PersonCard_id'] && !(bool)$data['Person_id'] && $_SESSION['region']['nick'] === 'kz'){// #137782 распечатка бланка для Казахстана. И похоже, других регионов не будет. Другие печатаются через Birt
			$lpu = $this->pcmodel->queryResult('select top 1 v_Lpu.Lpu_Name from v_Lpu with (nolock) where v_Lpu.Lpu_id = '.(int)$data['Lpu_id'], array());
			$lpu = (!empty($lpu))?$lpu[0]['Lpu_Name']:'&nbsp;';
			$print_data = array(
				'PersonCard_Code' => '&nbsp;'// № амб. карты
				,'Person_SurName' => '&nbsp;'// Фамилия пациента
				,'Person_FirName' => '&nbsp;'// Имя пациента
				,'Person_SecName' => '&nbsp;'// Отчество пациента
				,'Person_BirthDay' => '&nbsp;'// дата рождения
				,'Ethnos_Name'   => '&nbsp;'
				,'Person_Inn'   => '&nbsp;'
				,'Lpu_Name' => $lpu// наименование ЛПУ
				,'Document_Num' => '&nbsp;'// номер документа удостоверяющего личность
				,'Person_Phone' => '&nbsp;'// телефон
				,'Job_Name' => '&nbsp;'// место работы
				,'OrgUnion_Name' => '&nbsp;'// подразделение
				,'Post_Name' => '&nbsp;'// должность
				,'Region' => '&nbsp;'
				,'City' => '&nbsp;'
				,'TerrDop' => '&nbsp;'
				,'Street' => '&nbsp;'
				,'House' => '&nbsp;'
				,'Corpus' => '&nbsp;'
				,'Flat' => '&nbsp;'
			);
			$view = 'print_form025u_for_kz';
			$val['result'] = $this->parser->parse($view, $print_data, true);
			$this->ProcessModelSave($val)->ReturnData();
			return true;
		}

		$response = $this->pcmodel->getMedCard($data, false, true);// var_export($response); exit;
		$outdata = $this->ProcessModelList($response, false)->GetOutData();// var_export($outdata); exit;
		
		if ( !empty($outdata['Error_Msg']) ) {
			echo $outdata['Error_Msg'];
			return false;
		}

		$outdata[0]['Sex_NameFull'] = $outdata[0]['Sex_Name'];
		$outdata[0]['Sex_Name3s'] = mb_substr($outdata[0]['Sex_Name'],0,3);
		$outdata[0]['Sex_Name'] = mb_substr($outdata[0]['Sex_Name'],0,1);

		$print_data = array(
			'PersonCard_Code' => !empty($data['PersonAmbulatCard_Num']) ? $data['PersonAmbulatCard_Num']: (!empty($outdata[0]['PersonCard_Code']) ? $outdata[0]['PersonCard_Code'] : '&nbsp;'), //№ амб. карты
			'Person_SurName' => (!empty($outdata[0]['Person_SurName']) ? $outdata[0]['Person_SurName'] : '&nbsp;'), //Фамилия пациента
			'Person_FirName' => (!empty($outdata[0]['Person_FirName']) ? $outdata[0]['Person_FirName'] : '&nbsp;'), //Имя пациента
			'Person_SecName' => (!empty($outdata[0]['Person_SecName']) ? $outdata[0]['Person_SecName'] : '&nbsp;'), //Отчество пациента
			'Person_BirthDay' => (!empty($outdata[0]['Person_BirthDay']) ? $outdata[0]['Person_BirthDay'] : '&nbsp;'), //дата рождения
            'Ethnos_Name'   => (!empty($outdata[0]['Ethnos_Name']) ? $outdata[0]['Ethnos_Name'] : '&nbsp;'), //дата рождения
            'Person_Inn'   => (!empty($outdata[0]['Person_Inn']) ? $outdata[0]['Person_Inn'] : '&nbsp;'), //дата рождения
			'Lpu_Name' => (!empty($outdata[0]['Lpu_Name']) ? $outdata[0]['Lpu_Name'] : '&nbsp;'), //наименование ЛПУ
			'Lpu_OGRN' => (!empty($outdata[0]['Lpu_OGRN']) ? $outdata[0]['Lpu_OGRN'] : '&nbsp;'), //ОГРН
			'Org_OKPO' => (!empty($outdata[0]['Org_OKPO']) ? $outdata[0]['Org_OKPO'] : '&nbsp;'), //ОКПО
			'LpuRegion_Name' => (!empty($outdata[0]['LpuRegion_Name']) ? $outdata[0]['LpuRegion_Name'] : '&nbsp;'), //Участок
			'Sex_Name' => (!empty($outdata[0]['Sex_Name']) ? $outdata[0]['Sex_Name'] : '&nbsp;'), //Пол
			'Sex_Name3s' => (!empty($outdata[0]['Sex_Name3s']) ? $outdata[0]['Sex_Name3s'] : '&nbsp;'), //Пол
			'Sex_NameFull' => (!empty($outdata[0]['Sex_NameFull']) ? $outdata[0]['Sex_NameFull'] : '&nbsp;'), //Пол
			'Sex_Code' => (!empty($outdata[0]['Sex_Code']) ? $outdata[0]['Sex_Code'] : 0), //Пол
			'Address_Address' => (!empty($outdata[0]['Address_Address']) ? $outdata[0]['Address_Address'] : '&nbsp;'), //Адрес ЛПУ
			'Person_Snils' => (!empty($outdata[0]['Person_Snils']) ? $outdata[0]['Person_Snils'] : '&nbsp;'), //СНИЛС
			'OrgSMO_Nick' => (!empty($outdata[0]['OrgSMO_Nick']) ? $outdata[0]['OrgSMO_Nick'] : '&nbsp;'), //СМО
			'Polis_Ser' => (!empty($outdata[0]['Polis_Ser']) ? $outdata[0]['Polis_Ser'] : '&nbsp;'), //Серия страх. полиса
			'Polis_Num' => (!empty($outdata[0]['Polis_Num']) ? $outdata[0]['Polis_Num'] : '&nbsp;'), //Номер страх. полиса
			'PAddress_Address' => (!empty($outdata[0]['PAddress_Address']) ? $outdata[0]['PAddress_Address'] : '&nbsp;'), //адрес проживания
			'UAddress_Address' => (!empty($outdata[0]['UAddress_Address']) ? $outdata[0]['UAddress_Address'] : '&nbsp;'), //адрес регистрации
			'KLAreaType_Name' => (!empty($outdata[0]['KLAreaType_Name']) ? $outdata[0]['KLAreaType_Name'] : '&nbsp;'), // житель
			'DocumentType_Name' => (!empty($outdata[0]['DocumentType_Name']) ? $outdata[0]['DocumentType_Name'] : '&nbsp;'), //наименование документа удостоверяющего личность
			'Document_Num' => (!empty($outdata[0]['Document_Num']) ? $outdata[0]['Document_Num'] : '&nbsp;'), //номер документа удостоверяющего личность
			'Document_Ser' => (!empty($outdata[0]['Document_Ser']) ? $outdata[0]['Document_Ser'] : '&nbsp;'), //серия документа удостоверяющего личность			
			'Person_Phone' => (!empty($outdata[0]['Person_Phone']) ? $outdata[0]['Person_Phone'] : '&nbsp;'), //телефон			
			'Job_Name' => (!empty($outdata[0]['Job_Name']) ? $outdata[0]['Job_Name'] : '&nbsp;'), // место работы			
			'OrgUnion_Name' => (!empty($outdata[0]['OrgUnion_Name']) ? $outdata[0]['OrgUnion_Name'] : '&nbsp;'), // подразделение		
			'Post_Name' => (!empty($outdata[0]['Post_Name']) ? $outdata[0]['Post_Name'] : '&nbsp;'), // должность		
			'EvnUdost' => (!empty($outdata[0]['EvnUdost_Ser']) ? $outdata[0]['EvnUdost_Ser'] . ' ' . $outdata[0]['EvnUdost_Num'] : '&nbsp;'), // Документ, удостоверяющий право на льготное обеспечение		
			'EvnUdost_Date' => (!empty($outdata[0]['EvnUdost_Date']) ? $outdata[0]['EvnUdost_Date'] : '&nbsp;'), // Дата выдачи документа на льготы
			'PrivilegeType_Code' => (is_array($outdata[1]) ? join(", ", array_unique ($outdata[1])) : ""), // Коды льгот
			'SocStatus_Name' => (!empty($outdata[0]['SocStatus_Name']) ? $outdata[0]['SocStatus_Name'] : '&nbsp;'), // Соц. статус
			'InvalidGroupType_Name' => (!empty($outdata[0]['InvalidGroupType_Name']) ? $outdata[0]['InvalidGroupType_Name'] : '&nbsp;'), // Группа инвалидности
		);

		switch ( $_SESSION['region']['nick'] ) {
			case 'ufa':
				$this->load->model("EvnPL_model", "EvnPL_model");
				$response = $this->EvnPL_model->getPersonPrivilegeFedUfa(array('Person_id' => $outdata[0]['Person_id']));
				//var_dump($response);die;
				$print_data['PrivilegeType_Name'] = '';
				if ( is_array($response) && count($response) > 0 ) {
					$print_data['PrivilegeType_Name'] = $response[0]['PrivilegeType_Name'];
					if($response[0]['flag_end'] == 1)
					{
						$print_data['PrivilegeType_Name'] = '';
						$print_data['PrivilegeType_Code'] = '';
					}
				}
				$view = 'print_form025u_for_ufa';
			break;

			case 'ekb':
				$print_data['barcode_string'] = (string)$outdata[0]['PersonCard_id'];

				$this->load->model("Polka_PersonDisp_model", "Polka_PersonDisp_model");
				$responseDisp = $this->Polka_PersonDisp_model->getPersonDispHistoryListForPrint(array('Lpu_id' => $outdata[0]['Lpu_id'], 'Person_id' => $outdata[0]['Person_id']));

				if ( is_array($responseDisp) && count($responseDisp) > 0 ) {
					$ids = array();
					$begDate = '';
					foreach ($responseDisp as $key) {
						if( ($key['PersonDisp_id'] !== false) && !in_array($key['PersonDisp_id'], $ids) ) {
							$arr = array();
							$arr['Diag_Code'] = $key['Diag_Code'];
							$arr['Diag_Name'] = $key['Diag_Name'];
							$arr['PersonDisp_begDate'] = $key['PersonDisp_begDate'];
							if( empty($begDate) || (strtotime($begDate) > strtotime($key['PersonDisp_begDate'])) ) $begDate = $key['PersonDisp_begDate'];
							$arr['PersonDisp_endDate'] = $key['PersonDisp_endDate'];
							$arr['MedPersonal_FIO'] = $key['MedPersonal_FIO'];
							$arr['Med_Post_Name'] = $key['Post_Name'];
							if(!empty($key['PersonDisp_endDate'])){
								$arr['MedPersonal_FIO_end'] = $key['MedPersonal_FIO'];
								$arr['Med_Post_Name_end'] = $key['Post_Name'];
							} else {
								$arr['MedPersonal_FIO_end'] = null;
								$arr['Med_Post_Name_end'] = null;
							}
							$print_data['disp_list'][] = $arr;
							array_push($ids, $key['PersonDisp_id']);
						}
					}

					$responseAdr = $this->Polka_PersonDisp_model->getPersonDispHistoryListAdresses(array('Person_id' => $outdata[0]['Person_id']));
					if ( is_array($responseAdr) && count($responseAdr) > 0 ) {
						foreach ($responseAdr as $key) {
							$adr = array();
							$adr['PAddress'] = $key['PAddress_Address'];
							$adr['PAdr_Date'] = $key['PersonPAddress_begDT']->format('d.m.Y');
							if( strtotime($adr['PAdr_Date']) < strtotime($begDate) ){
								$print_data['adr_list'][0] = $adr; 
							} else {
								$print_data['adr_list'][] = $adr;
							}
						}
					} else {
						$adr = array();
						$adr['PAddress'] = null;
						$adr['PAdr_Date'] = null;
						$print_data['adr_list'][] = $adr;
					}

				} else {
					$arr = array();
					$arr['Diag_Code'] = null;
					$arr['Diag_Name'] = null;
					$arr['PersonDisp_begDate'] = null;
					$arr['PersonDisp_endDate'] = null;
					$arr['MedPersonal_FIO'] = null;
					$arr['Med_Post_Name'] = null;
					$print_data['disp_list'][] = $arr;
					$adr = array();
					$adr['PAddress'] = null;
					$adr['PAdr_Date'] = null;
					$print_data['adr_list'][] = $adr;
				}

				$view1 = 'print_form025u_for_ekb_list_1';
				$view2 = 'print_form025u_for_ekb_list_2';
			break;

			case 'kz':
				$this->load->model("Person_model", "Person_model");
				$addr = $this->Person_model->getPersonAddress(array('Person_id' => $outdata[0]['Person_id']));
				$print_data['Region'] = (!empty($addr['UKLRgn_Name']) ? $addr['UKLRgn_Name'] : '&nbsp;');
				$print_data['City'] = (!empty($addr['UKLCity_Name']) ? $addr['UKLCity_Name'] : '&nbsp;');
				$print_data['TerrDop'] = (!empty($addr['USprTerrDop_Name']) ? $addr['USprTerrDop_Name'] : '&nbsp;');
				$print_data['Street'] = (!empty($addr['UKLStreet_Name']) ? $addr['UKLStreet_Name'] : '&nbsp;');
				$print_data['House'] = (!empty($addr['UAddress_House']) ? $addr['UAddress_House'] : '&nbsp;');
				$print_data['Corpus'] = (!empty($addr['UAddress_Corpus']) ? $addr['UAddress_Corpus'] : '&nbsp;');
				$print_data['Flat'] = (!empty($addr['UAddress_Flat']) ? $addr['UAddress_Flat'] : '&nbsp;');

				$view = 'print_form025u_for_kz';
			break;

			default:
				$view = 'print_form025u';
			break;
		}

		if ($_SESSION['region']['nick'] == 'ekb') {
			$val['result1'] = $this->parser->parse($view1,$print_data,true);
			$val['result2'] = $this->parser->parse($view2,$print_data,true);
		} else {
			$val['result'] = $this->parser->parse($view,$print_data,true);
		}
		$val['Error_Msg'] = '';

		$this->ProcessModelSave($val)->ReturnData();
		return true;
	}
	
	/**
	 * Получение количества людей прикрепленных к ЛПУ
	 */
	function getPersonCardCount()
	{
		$data = getSessionParams();
		
		$response = $this->pcmodel->getPersonCardCount($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Выгрузка картотеки ЛПУ в формат DBF
	 */
	function ExportPCToDBF()
	{
		$data = getSessionParams();

		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		$pc_data = $this->pcmodel->ExportPCToDBF($data);
		if ( !is_array($pc_data) || !(count($pc_data) > 0) )
		{
			echo "Картотека пуста.";
			return true;
		}
		// формируем массивы с описанием полей бд
		$pc_def = array(
			array( "CARDNUM", "C", 10, 0 ),
			array( "FIRSTNAME", "C", 50, 0 ),
			array( "SURNAME", "C", 50, 0 ),
			array( "SECNAME", "C", 50, 0 ),
			array( "BIRTHDAY", "D", 8, 0 ),
			array( "ADDRESS", "C", 250, 0 ),
			array( "PADDRESS", "C", 250, 0 ),
			array( "POLSER", "C", 10, 0 ),
			array( "POLNUM", "C", 16, 0 ),
			array( "SMONAME", "C", 50, 0 ),
			array( "REGNAME", "C", 50, 0 ),
			array( "BEGDATE", "D", 8, 0 ),
			array( "ENDDATE", "D", 8, 0 ),
			array( "SOCSTATC", "C", 5, 0 ),
			array( "SOCSTATN", "C", 30, 0 ),
			array( "BDZ", "C", 3, 0 )
		);
		if(getRegionNick() == 'ufa'){
			$pc_def[] = array( "AttachType", "C", 20, 0 );
			$pc_def[] = array( "Condit", "C", 3, 0 );
		}

		$ts = time();
		$file_pc_sign = "pc_".$ts;
		$file_pc_name = EXPORTPATH_PC.$file_pc_sign.".dbf";
		$file_zip_name = EXPORTPATH_PC.$file_pc_sign.".zip";

		$h = dbase_create( $file_pc_name, $pc_def );
		foreach ($pc_data as $row)
		{
			array_walk($row, 'ConvertFromUtf8ToCp866');
			
			dbase_add_record( $h, array_values($row) );
		}
		dbase_close ($h);
		
		$zip=new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $file_pc_name );
		$zip->close();
		unlink($file_pc_name);

		$val = array("success" => true, "url" => "/" . $file_zip_name );
		$this->ReturnData($val);
	}
	
	
	
	/**
	*	Получение списка заявлений о выборе МО 
	*/
	function loadPersonCardAttachGrid() {
		$data = $this->ProcessInputData('loadPersonCardAttachGrid', true);
		if( $data === false ) return false;
		
		$response = $this->pcmodel->loadPersonCardAttachGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Получение данных для редактирования заявления на прикрепление
	 */
	function loadPersonCardAttachForm() {
		$data = $this->ProcessInputData('loadPersonCardAttachForm', true);
		if ($data === false) { return false; }

		$response = $this->pcmodel->loadPersonCardAttachForm($data);

		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 *	Проверка наличия активного прикрепления
	 */
	function checkPersonCardActive() {
		$data = $this->ProcessInputData('checkPersonCardActive', true);
		if($data === false)
			return false;
		$response = $this->pcmodel->checkPersonCardActive($data);
		//var_dump($response);die;
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 *	Проверка изменения прикрепления в течение года
	 */
	function checkPersonCardDate() {
		$data = $this->ProcessInputData('checkPersonCardActive', true);
		if($data === false)
			return false;
		$data['LpuRegionType_id'] = null;
		$data['LpuAttachType_id'] = 1;
		$data['PersonAge'] = null;
		$checkAttachPosible = $this->pcmodel->checkAttachPosible($data);
		
		if(is_array($checkAttachPosible) && isset($checkAttachPosible[0]['Error_Msg']) && ($checkAttachPosible[0]['Error_Msg'] == 'Нельзя прикреплять пациента чаще 1 раза в год.'))
			$ans = array('res' => 2);
		else
			$ans = array('res' => 0);
		$this->ProcessModelList($ans)->ReturnData();
		return true;
	}

	/**
	 *	Сохранение заявления о выборе МО
	 */
	function savePersonCardAttachForm() {
		$data = $this->ProcessInputData('savePersonCardAttachForm', true);
		if($data === false)
			return false;
		$response = $this->pcmodel->savePersonCardAttachForm($data);
		
		$this->load->model("PMMediaData_model", "mdmodel");
		if( empty($data['files']) && empty($res[0]['Error_Msg'])){
			$par_MediaData = array(
				'ObjectID' => $data['PersonCardAttach_id'],
				'pmMediaData_ObjectName' => 'PersonCardAttach'
			);
			$res_cur_MediaData = $this->mdmodel->getpmMediaData($par_MediaData);
			foreach ($res_cur_MediaData as $md)
			{
				$res_del = $this->mdmodel->deletepmMediaData(array('pmMediaData_id' => $md['pmMediaData_id']));
			}
		}
		if( !empty($data['files']) && empty($response[0]['Error_Msg']) ) {
			$files = explode("|", $data['files']);
			$rootDir = IMPORTPATH_ROOT . "personcardattaches/";
			$folderName = $rootDir . $response[0]['PersonCardAttach_id'] . "/";
			if( !is_dir($folderName) ) {
				if( !mkdir($folderName) ) {
					DieWithError("Ошибка! Не удалось создать папку для хранения файлов заявления!");
					return false;
				}
			}

			if(!empty($data['PersonCardAttach_id'])){
				$file_names = array();
				foreach($files as $file){
					$f = explode("::",$file);
					$file_names[] = $f[0];
				}
				$par_MediaData = array(
					'ObjectID' => $data['PersonCardAttach_id'],
					'pmMediaData_ObjectName' => 'PersonCardAttach'
				);
				$res_cur_MediaData = $this->mdmodel->getpmMediaData($par_MediaData);
				foreach ($res_cur_MediaData as $md)
				{
					if(!in_array($md['pmMediaData_FileName'],$file_names))
					{
						$res_del = $this->mdmodel->deletepmMediaData(array('pmMediaData_id' => $md['pmMediaData_id']));
					}
				}
			}

			foreach($files as $file) {
				$f = explode("::", $file);
				$file_name = $folderName . $f[0];
				$file_tmp_name = $f[1];
				if( is_file($file_tmp_name) ) {
					if( !@rename( $file_tmp_name, iconv("UTF-8", "cp1251",$file_name) ) ) {
						DieWithError("Ошибка! Не удалось сохранить файл заявления!");
						return false;
					}
					$rsp = $this->mdmodel->savepmMediaData(array(
						'pmMediaData_id' => isset($data['pmMediaData_id']) ? $data['pmMediaData_id'] : null,
						'ObjectName' => 'PersonCardAttach',
						'ObjectID' => $response[0]['PersonCardAttach_id'],
						'orig_name' => $f[0],
						'file_name' => $file_name,
						'description' => 'filesize = ' . filesize(iconv("UTF-8", "cp1251",$file_name)),
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	*	Удаление заявления о выборе МО
	*/
	function deletePersonCardAttach()
	{
		$data = $this->ProcessInputData('deletePersonCardAttach', true);
		if( $data === false ) return false; 
		$response = $this->pcmodel->deletePersonCardAttach($data);
		$result = $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->GetOutData();
		$this->ReturnData($result);
	}

	/**
	 *	Изменение статуса заявления
	 */
	function changePersonCardAttachStatus()
	{
		$data = $this->ProcessInputData('changePersonCardAttachStatus', true);
		if( $data === false ) return false; 
		$PersonCardAttach_ids_array = $dt = (array) json_decode($data['PersonCardAttach_ids_array']);;
        $params = $data;
        $result_string = array();
        for ($i=0; $i<count($PersonCardAttach_ids_array); $i++){
            $params['PersonCardAttach_id'] = $PersonCardAttach_ids_array[$i];
            $result = $this->pcmodel->changePersonCardAttachStatus($params);
            $result_string[$i] = $result['string'];
        }
        //var_dump($result_string);die;
		$this->ReturnData($result_string);
	}
	
	/**
	 *	Отказ в прикреплении
	 */
	function cancelPersonCardAttach()
	{
		$data = $this->ProcessInputData('cancelPersonCardAttach', true);
		if( $data === false ) return false; 
		$PersonCardAttach_ids_array = $dt = (array) json_decode($data['PersonCardAttach_ids_array']);
        $params = $data;
        $result_string = array();
        for ($i=0; $i<count($PersonCardAttach_ids_array); $i++){
            $params['PersonCardAttach_id'] = $PersonCardAttach_ids_array[$i];
            $result = $this->pcmodel->cancelPersonCardAttach($params);
            $result_string[$i] = $result['string'];
        }
        
		$this->ReturnData($result_string);
	}

	/**
	 *	Добавление прикрепления на основе заявления
	 */
	function savePersonCardByAttach()
	{
		$data = $this->ProcessInputData('savePersonCardByAttach', true);
		if( $data === false ) return false; 
		$PersonCardAttach_ids_array = $dt = (array) json_decode($data['PersonCardAttach_ids_array']);;
        $params = $data;
        $result_string = array();
        $k=-1;
		for ($i=0; $i<count($PersonCardAttach_ids_array); $i++){
            $params['PersonCardAttach_id'] = $PersonCardAttach_ids_array[$i];
            $result_check_pc_at = $this->pcmodel->checkPersonCardByAttach($params);
            if(count($result_check_pc_at)>0 && isset($result_check_pc_at[0]['PersonCard_id']))
            {
            	$k++;
            	$result_string[$k] = 'Заявление от '.$result_check_pc_at[0]['PersonCardAttach_setDate'].' ('. $result_check_pc_at[0]['Person_FIO'].') '.' уже связано с прикреплением.';
            }
            else
            {
            	$result_check_status = $this->pcmodel->checkAttachStatus($params);
            	$typeCodesEnable = getRegionNick()=='perm' ? array(1) : array(2,3);
            	if(getRegionNick()=='perm' and $result_check_status[0]['PersonCardAttachStatusType_id']==23) {
					$k++;
					$result_string[$k] = 'Пациент '.$result_check_status[0]['Person_FIO'].' отказался от заявления о прикреплении';
				} else
            	if(!in_array($result_check_status[0]['PersonCardAttachStatusType_Code'], $typeCodesEnable)){
            		$k++;
            		$result_string[$k] = 'Заявление от '.$result_check_status[0]['PersonCardAttach_setDate'].' ('. $result_check_status[0]['Person_FIO'].') '.' имеет статус "'.$result_check_status[0]['PersonCardAttachStatusType_Name'].'". Прикрепление невозможно.';
            	}
            	else
            	{
            		$result_add = $this->pcmodel->addPersonCardByAttach($params);
            	}
            }
            //$result_string[$i] = $result['string'];
        }
        $this->ReturnData($result_string);
	}

	/**
	 * Получение списка заявлений о выборе МО
	 */
	function loadPersonCardMedicalInterventGrid() {
		$data = $this->ProcessInputData('loadPersonCardMedicalInterventGrid', true);
		if( $data === false ) return false;

		$response = $this->pcmodel->loadPersonCardMedicalInterventGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Получение данных нотификации по пациенту 
	 */
	function getPersonInfoKVRACHU() {
		/* Специально все сломал, так как подход совершенно неправильный
		у нас может быть несколько аккаунтов на сайте, на которые добавлен один человек,
		нельзя просто так взять телефон с одного аккаунта и отправить СМС  на него.
		К тому же нельзя это делать без согласия пользователя. 
		К тому же по коду сообщение отправляется даже на неактивированный телефон.
		В общем надо все переосмысливать и переделывать, такое не прокатит.
		*/
		return false;
		
		/*unset($this->db);
		$this->load->database('kvrachu');
		$this->load->model("Polka_PersonCard_model", "pcmodel");
		$data = $this->ProcessInputData('getPersonInfoKVRACHU', true);
		if( $data === false ) return false;
		$response = $this->pcmodel->getPersonInfoKVRACHU($data);
		$this->ProcessModelList($response, true, true)->ReturnData();*/
	}


    /**
     * Функция возвращает в XML список прикрепленного населения к указанной СМО на указанную дату
     */
    function loadAttachedList()
    {
        $data = $this->ProcessInputData('loadAttachedList', true);
        if ($data === false) { return false; }

		if ( !isExpPop() ) {
			$this->ReturnError('Функционал недоступен');
			return false;
		}

        set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$attached_list_data = $this->pcmodel->loadAttachedList($data);

        if ($attached_list_data === false) {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
            return false;
        }

        if ( ($attached_list_data['Error_Code']) && ($attached_list_data['Error_Code'] == 1) ) {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данные по прикрепленному населению при указанных параметрах в базе данных отсутствуют.')));
            return false;
        }

		array_walk_recursive($attached_list_data, 'ConvertFromUTF8ToWin1251', true);

        $this->load->library('parser');

        if ( ! file_exists(EXPORTPATH_ATACHED_LIST)){
			mkdir( EXPORTPATH_ATACHED_LIST );
		}


        if ($data['session']['region']['nick'] == 'khak') {
            // каталог в котором лежат выгружаемые файлы
            $out_dir = "khak_xml_attachedList_". date('ym');
            if ( ! file_exists(EXPORTPATH_ATACHED_LIST.$out_dir)){
				mkdir( EXPORTPATH_ATACHED_LIST.$out_dir );
			}


            //Сканируем папку с файлами по маске, находим последний файл и присваеваем новому файлу счётчик на один больше
            $filenames = scandir(EXPORTPATH_ATACHED_LIST.$out_dir);

            $filename_counter = "001";

            foreach($filenames as $filename) {
                if (preg_match('/^NI_019100_.......\.zip/i', $filename)) {
                    if (intval(mb_substr($filename,14,3)) >= intval($filename_counter)){
                        $filename_counter = str_pad(intval(mb_substr($filename,14,3)) + 1,3, 0, STR_PAD_LEFT);
                    }
                    else {
                    	$filename_counter = str_pad($filename_counter, 3, 0, STR_PAD_LEFT);
                    }
                }
            }

            $attached_list_file_name = "NI_019100_". date('ym'). $filename_counter;
        }
        else {
            // каталог в котором лежат выгружаемые файлы
            $out_dir = "re_xml_".time()."_"."attachedList";
            mkdir( EXPORTPATH_ATACHED_LIST.$out_dir );
            $attached_list_file_name = "ATT_LIST";
        }
        // файл-перс. данные
        $attached_list_file_path = EXPORTPATH_ATACHED_LIST.$out_dir."/".$attached_list_file_name.".xml";

        $attached_list_data['ZGLV'][0]['FILENAME'] = $attached_list_file_name;

        //Для разных регионов
        $rgn = (in_array($_SESSION['region']['nick'], array('astra','khak')))?'_'.$_SESSION['region']['nick']:'';
        $templ = "person".$rgn;

        $xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/'.$templ, $attached_list_data, true);
        $xml = str_replace('&', '&amp;', $xml);

        file_put_contents($attached_list_file_path, $xml);

        $file_zip_sign = $attached_list_file_name;
        $file_zip_name = EXPORTPATH_ATACHED_LIST.$out_dir."/".$file_zip_sign.".zip";
        $zip = new ZipArchive();
        $zip->open($file_zip_name, ZIPARCHIVE::CREATE);
        $zip->AddFile( $attached_list_file_path, $attached_list_file_name . ".xml" );
        $zip->close();

        unlink($attached_list_file_path);

        if (file_exists($file_zip_name))
        {
            $this->ReturnData(array('success' => true,'Link' => $file_zip_name/*, 'Doc' => $attached_list_data['DOC']*/));
        }
        else {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива реестра!')));
        }
        return true;
    }

    /**
     * Функция возвращает в CSV список прикрепленного населения к указанной МО на текущую дату
     */
    function loadAttachedListCSV(){

        $data = $this->ProcessInputData('loadAttachedListCSV', true);
        if ($data === false) { return false; }

		if ( ! isExpPop() ) {
			$this->ReturnError('Функционал недоступен');
			return false;
		}

		if ( ! isSuperAdmin() ) {
			$data['AttachLpu_id'] = $data['Lpu_id'];
		}

		if ( ! isSuperAdmin() && empty($data['AttachLpu_id']) ) {
			$this->ReturnError('Не указан идентификатор МО');
			return false;
		}

        set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$attached_list_data = $this->pcmodel->loadAttachedListCSV($data);


		// -------------------------------------------------------------------------------------------------------------
		// Проверка данных
        if ($attached_list_data === false) {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
            return false;
        }

        if ( ! is_array($attached_list_data) || ! is_array($attached_list_data) ) {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данные по прикрепленному населению при указанных параметрах в базе данных отсутствуют.')));
            return false;
        }
		// -------------------------------------------------------------------------------------------------------------



		$attached_list_dir = $attached_list_data['attached_list_dir'];

		$attached_list_file_name = $attached_list_data['attached_list_file_name'];
		$attached_list_file_path = $attached_list_data['attached_list_file_path'];

		$attached_list_errors_file_name = $attached_list_data['attached_list_errors_file_name'];
		$attached_list_errors_file_path = $attached_list_data['attached_list_errors_file_path'];



		$exist_file_list = file_exists($attached_list_file_path);
		$size_file_list = filesize($attached_list_file_path);
		$exist_file_errors = file_exists($attached_list_errors_file_path);
		$size_file_errors = filesize($attached_list_errors_file_path);


		if (( ! $exist_file_errors || $size_file_list == 0) && ( ! $exist_file_list || $size_file_errors == 0)){
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('В базе не найдены данные по заданным параметрам.')));
			return false;
		}



		// -------------------------------------------------------------------------------------------------------------
		// Добавляем файлы в архив
		$file_zip_sign = $attached_list_file_name;
		$file_zip_name = $attached_list_dir."/".$file_zip_sign.".zip";

		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		if (file_exists($attached_list_file_path)){
			$zip->AddFile( $attached_list_file_path, $attached_list_file_name . ".csv" );
		}
		if (file_exists($attached_list_errors_file_path)){
			$zip->AddFile( $attached_list_errors_file_path, $attached_list_errors_file_name . ".txt" );
		}
		$zip->close();
		// -------------------------------------------------------------------------------------------------------------




		if (file_exists($attached_list_file_path)){
			unlink($attached_list_file_path);
		}

		if (file_exists($attached_list_errors_file_path)){
			unlink($attached_list_errors_file_path);
		}




		// -------------------------------------------------------------------------------------------------------------
		// Вывод результата
        if (file_exists($file_zip_name)){
			$result = array('success' => true,'Link' => $file_zip_name);

			// Есть ошибки
			if ($exist_file_errors && $size_file_list != 0) {
				$result['Alert_Msg'] = toUtf('Не все население выгружено, т.к. у врача, к которому прикреплено население, отсутствует СНИЛС.');
			}

			$this->ReturnData($result);
        }
        else {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания файла выгрузки.')));
        }
		// -------------------------------------------------------------------------------------------------------------


        return true;
    }


    /**
     * Печать отчета по сводным показателям
     * Входящие данные: params
     * На выходе: форма отчета по сводным показателям
     * Используется: форма выгрузки прикрепленного населения
     */
    function printAttachedList(/*$count_ppl = false, $m04 = false, $w04 = false, $m517 = false, $w517 = false, $m1859 = false, $w1854 = false, $m60 = false, $w55 = false, $OrgSMO_id = false,*/ $ReturnString = false) {
        //var_dump($_GET);

        $this->load->helper('Options');
        $this->load->library('parser');

        $data = $this->ProcessInputData('printAttachedList', true);
        if ($data === false) { return false; }
        //var_dump($data);
        // Получаем настройки
        //$options = getOptions();
        $response = $this->dbmodel->printAttachedList($_GET);

        if ( !is_array($response) || count($response) == 0 ) {
            echo 'Ошибка при получении названия СМО';
            return true;
        }

        $template = 'attached_list_template_list_a4';

        $print_data = array(
            'm04' =>returnValidHTMLString($data['m04']),
            'w04' =>returnValidHTMLString($data['w04']),
            'm517' =>returnValidHTMLString($data['m517']),
            'w517' =>returnValidHTMLString($data['w517']),
            'm1859' =>returnValidHTMLString($data['m1859']),
            'w1854' =>returnValidHTMLString($data['w1854']),
            'm60' =>returnValidHTMLString($data['m60']),
            'w55' =>returnValidHTMLString($data['w55']),
            'OrgSMO_Name' =>returnValidHTMLString($response[0]['OrgSMO_Name']),
            'count_ppl' =>returnValidHTMLString($data['count_ppl'])
        );

        return $this->parser->parse($template, $print_data, $ReturnString);
    }

	/**
	 * Выгрузка списка прикрепленного населения за период
	 */
	function exportPersonAttaches()
	{
		$data = $this->ProcessInputData('exportPersonAttaches', true);
		if ($data === false) { return false; }
		$this->load->model('Polka_PersonCard_model', 'pcmodel');
		$fileInfo = $this->pcmodel->getInfoForAttachesFile($data);
		$result = $this->pcmodel->exportPersonAttaches($data);
		$attachesFields = array(
			array( "FAM",		"C",	40,	0),
			array( "IM",		"C",	40,	0),
			array( "OT",		"C",	40,	0),
			array( "DR",		"C",	10,	0),
			array( "W",			"C",	1,	0),
			array( "SPOL",		"C",	20,	0),
			array( "NPOL",		"C",	20,	0),
			array( "Q",			"C",	5,	0),
			array( "LPU",		"C",	6,	0),
			array( "LPUDZ",		"C",	10,	0),
			array( "LPUDT",		"C",	10,	0),
			array( "LPUDX",		"C",	10,	0),
			array( "LPUTP",		"C",	1,	0),
			array( "OKATO",		"C",	11,	0),
			array( "RNNAME",	"C",	80,	0),
			array( "NPNAME",	"C",	80,	0),
			array( "UL",		"C",	80,	0),
			array( "DOM",		"C",	7,	0),
			array( "KORP",		"C",	6,	0),
			array( "KV",		"C",	6,	0),
			array( "STATUS",	"C",	1,	0),
			array( "ERR",		"C",	40,	0),
			array( "RSTOP",		"C",	1,	0)
		);
		$out_dir = "/att_".time();
		if ( !file_exists(EXPORTPATH_ATTACHES) )
			mkdir( EXPORTPATH_ATTACHES );
		mkdir( EXPORTPATH_ATTACHES.$out_dir );

		// формируем DBF-ки
		$attachesFileName = EXPORTPATH_ATTACHES.$out_dir."/MO2".$fileInfo[0]['Lpu_f003mcod'].$fileInfo[0]['file_date'].".dbf";
		$h = dbase_create( $attachesFileName, $attachesFields );
		if (is_object($result)) {
			//$result->_data_seek(0);
			while ($row = $result->_fetch_assoc()) {
				array_walk($row, 'ConvertFromUtf8ToCp866');
				dbase_add_record( $h, array_values($row) );
			}
		}
		dbase_close ($h);

		$zip = new ZipArchive();
		$file_zip_name = EXPORTPATH_ATTACHES.$out_dir."/MO2".$fileInfo[0]['Lpu_f003mcod'].$fileInfo[0]['file_date'].".zip";
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $attachesFileName, "MO2".$fileInfo[0]['Lpu_f003mcod'].$fileInfo[0]['file_date'].".dbf" );
		$zip->close();
		unlink($attachesFileName);
		if (file_exists($file_zip_name))
		{
			$this->ReturnData(array('success' => true, 'filename' => $file_zip_name));
		}
		else
		{
			$this->ReturnError('Ошибка создания архива экспорта');
		}

		return true;
	}

	/**
	 * Проверка наличия дисп карт в другом МО (по гинекологии) https://redmine.swan.perm.ru/issues/72643
	 */
	function checkPersonDisp()
	{
		$data = $this->ProcessInputData('checkPersonDisp', true);
		if ($data === false) {
			return false;
		}
		$this->load->model('Polka_PersonCard_model', 'pcmodel');
		$response = $this->pcmodel->checkPersonDisp($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	 * Выгрузка списка прикрепленного населения за период
	 */
	function exportPersonCardForPeriod() {
		set_time_limit(0);
		ini_set("memory_limit", "2048M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "220");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "220M");

		$data = $this->ProcessInputData('exportPersonCardForPeriod', true);
		if ($data === false) { return false; }

		if ( !isSuperadmin() && !isLpuAdmin($data['Lpu_id']) ) {
			$this->ReturnError('Функционал недоступен');
			return false;
		}

		$this->load->library('textlog', array('file' => 'exportPersonCardForPeriod.log'));
		$this->textlog->add("\n\r");
		$this->textlog->add("exportPersonCardForPeriod: Запуск" . "\n\r");
		$this->textlog->add("Регион: " . $data['session']['region']['nick'] . "\n\r");

		$this->textlog->add("Задействовано памяти до выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

		$fileInfo = $this->pcmodel->getInfoForAttachesFile(array('AttachesLpu_id' => $data['Lpu_id']));
		$exportData = $this->pcmodel->exportPersonCardForPeriod($data);

		if ( !is_object($exportData) ) {
			$this->ReturnError('Ошибка при получении данных');
			return false;
		}

		$row = $exportData->_fetch_assoc();

		if ( !is_array($row) || count($row) == 0 ) {
			$this->ReturnError('Отсутствуют данные для выгрузки');
			return false;
		}

		$this->textlog->add("Задействовано памяти после выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

		$this->load->library('parser');

		$path = EXPORTPATH_ROOT . "attach_list/";

		if ( !file_exists($path) ) {
			mkdir($path);
		}

		$out_dir = "att_" . time() . "_" . $data['pmUser_id'];

		mkdir($path . $out_dir);

		$file_name = $fileInfo[0]['Lpu_f003mcod'] . "_PR_" . date_format(date_create($data['ReportDate']), 'Ymd') . "_" . sprintf('%03d', $data['PackageNum']);

		$file_path = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . ".xml";

		while ( file_exists($file_path) ) {
			$file_path = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . ".xml";
		}

		$file_path_tmp = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . "_tmp.xml";

		while ( file_exists($file_path) ) {
			$file_path_tmp = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . "_tmp.xml";
		}

		// Основные данные
		$i = 1;
		$mainData = array();
		$row['nomer_z'] = $i;

		foreach ( $row as $key => $value ) {
			if ( $value instanceof DateTime ) {
				$row[$key] = $value->format('Ymd');
			}
		}

		if (!empty($row['docser']) && !empty($row['doctype']) && $row['doctype'] == '14') {
			$row['docser'] = str_replace(' ', '', $row['docser']);
			$row['docser'] = mb_substr($row['docser'],0,2).' '.mb_substr($row['docser'],2,2);
		}
		if (empty($row['docser'])) {
			$row['docser'] = '1';
		}
		if (empty($row['docnum'])) {
			$row['docnum'] = '1';
		}

		$mainData[] = $row;

		$template = "person_card_body";

		while ( $row = $exportData->_fetch_assoc() ) {
			$i++;
			$row['nomer_z'] = $i;

			foreach ( $row as $key => $value ) {
				if ( $value instanceof DateTime ) {
					$row[$key] = $value->format('Ymd');
				}
			}

			if (!empty($row['docser']) && !empty($row['doctype']) && $row['doctype'] == '14') {
				$row['docser'] = str_replace(' ', '', $row['docser']);
				$row['docser'] = mb_substr($row['docser'],0,2).' '.mb_substr($row['docser'],2,2);
			}
			if (empty($row['docser'])) {
				$row['docser'] = '1';
			}
			if (empty($row['docnum'])) {
				$row['docnum'] = '1';
			}

			$mainData[] = $row;

			if ( count($mainData) == 1000 ) {
				array_walk_recursive($mainData, 'ConvertFromUTF8ToWin1251', true);
				$xml = $this->parser->parse('export_xml/' . $template, array('zl' => $mainData), true);
				$xml = str_replace('&', '&amp;', $xml);

				file_put_contents($file_path_tmp, $xml, FILE_APPEND);

				unset($xml);

				$mainData = array();

				$this->textlog->add("Задействовано памяти после выполнения записи в файл " . $i . " записей: " . memory_get_usage() . "\n\r");
			}
		}

		if ( count($mainData) > 0 ) {
			array_walk_recursive($mainData, 'ConvertFromUTF8ToWin1251', true);
			$xml = $this->parser->parse('export_xml/' . $template, array('zl' => $mainData), true);
			$xml = str_replace('&', '&amp;', $xml);

			file_put_contents($file_path_tmp, $xml, FILE_APPEND);

			unset($xml);
		}

		$this->textlog->add("Задействовано памяти после записи в файл " . $i . " записей: " . memory_get_usage() . "\n\r");

		unset($exportData);
		unset($mainData);

		$this->textlog->add("Задействовано памяти после очистки результатов запроса: " . memory_get_usage() . "\n\r");

		// Пишем данные в основной файл

		// Заголовок файла
		$template = 'person_card_header';

		$zglv = array(
			 'filename' => $file_name
			,'data' => str_replace('-', '', $data['FileCreationDate'])
			,'codmof' => $fileInfo[0]['Lpu_f003mcod']
			,'dn' => str_replace('-', '', $data['ExportDateRange'][0])
			,'dk' => str_replace('-', '', $data['ExportDateRange'][1])
			,'dt_report' => str_replace('-', '', $data['ReportDate'])
			,'nfile' => $data['PackageNum']
			,'nrec' => $i
		);

		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . $this->parser->parse('export_xml/' . $template, $zglv, true);
		$xml = str_replace('&', '&amp;', $xml);
		file_put_contents($file_path, $xml, FILE_APPEND);

		// Тело файла начитываем из временного
		// Заменяем простую, но прожорливую конструкцию, на чтение побайтно
		// https://redmine.swan.perm.ru/issues/51529
		// file_put_contents($file_path, file_get_contents($file_path_tmp), FILE_APPEND);

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

		// Конец файла
		$template = 'person_card_footer';

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
		unlink($file_path_tmp);

		if ( file_exists($file_zip_name) ) {
			$this->ReturnData(array('success' => true, 'Link' => $file_zip_name, 'Count' => $i));
		}
		else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Ошибка создания архива!'));
		}

		return true;
	}
	
	/**
	 * Справочник "Источник записи" (не весь)
	 */
	function getRecMethodTypeCombo() {
		$response = $this->pcmodel->getRecMethodTypeCombo();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}
?>