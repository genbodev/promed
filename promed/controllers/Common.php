<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * The New Generation of Medical Statistic Software
 *
 * @package				Common
 * @copyright			Copyright (c) 2009 Swan Ltd.
 * @author				Stas Bykov aka Savage (savage1981@gmail.com)
 * @link				http://swan.perm.ru/PromedWeb
 * @version				?
 */

/**
 * Класс контроллера для общих операций используемых во всех модулях
 *
 * @package		Common
 * @author		Stas Bykov aka Savage (savage1981@gmail.com)
 * @property Common_model dbmodel
 */
class Common extends swController {

	public $inputRules = array();

	private $moduleMethods = [
		'loadPersonData',
		'getCurrentDateTime'
	];
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'loadSystemErrorsViewWindow' => array(
				array(
					'field' => 'SystemError_id',
					'label' => 'Идентификатор ошибки',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadSystemErrorGrid' => array(
				array(
					'field' => 'SystemError_Code',
					'label' => 'Код ошибки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'SystemError_Error',
					'label' => 'Ошибка',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'SystemError_Date_From',
					'label' => 'Период от',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'SystemError_Date_To',
					'label' => 'Период до',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Лимит записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальная запись',
					'rules' => '',
					'type' => 'int'
				)
			),
			'checkActiveMQIsEmpty' => array(

			),
			'saveSystemError' => array(
				array(
					'field' => 'techInfo',
					'label' => 'Параметры окон',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'error',
					'label' => 'Ошибка',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'window',
					'label' => 'Окно',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'url',
					'label' => 'Url запроса',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'params',
					'label' => 'Параметры запроса',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadLpuSectionProfileList' => array(
				array(
					'field' => 'AddLpusectionProfiles',
					'label' => 'Грузить дополнительные профили отделения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedSpecOms_id',
					'label' => 'Специальность',
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
					'field' => 'LpuUnit_id',
					'label' => 'Подразделение',
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
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfileGRAPP_CodeIsNotNull',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionProfileGRKSS_CodeIsNotNull',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionProfileGRSZP_CodeIsNotNull',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				)
			),
			'loadLpuSectionProfileDopList' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'filterByKPG',
					'label' => 'Фильтровать по КПГ',
					'rules' => '',
					'type' => 'int'
				)
			),
			'setAnotherPersonForDocument' => array(
				array('field' => 'allowEvnStickTransfer', 'label' => 'Признак допустимости переноса ЛВН', 'rules' => '', 'type' => 'int', 'default' => 1),
				array('field' => 'ignoreAgeFioCheck', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 1),
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'),
				array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор карты СМП', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Server_id', 'label' => 'Непонятный, но нужный идентификатор', 'rules' => 'required', 'type' => 'int')
			),
			'checkEvnNotify' => array(
				array(
					'field' => 'Evn_id',
					'label' => 'Документ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusType_SysNick',
					'label' => 'Тип заболевания',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'EvnDiagPLSop_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
			),
			'checkEvnNotifyProf' => array(
				array(
					'field' => 'Evn_id',
					'label' => 'Документ',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'checkSuicideRegistry' => array(
				array(
					'field' => 'Evn_id',
					'label' => 'Документ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnClass_SysNick',
					'label' => 'Тип документа',
					'rules' => 'required|trim',
					'type' => 'string'
				),
			),
			'signedDocument' => array(
				array(
					'field' => 'id',
					'label' => 'Документ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'type',
					'label' => 'Тип документа',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'loadLpuSelectList' => array(
				array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
				array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			),
			'loadEvnPLEvnPSGrid' => array(
				array(
					'field' => 'EvnClass_id',
					'label' => 'Тип поиска',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_NumCard',
					'label' => 'Номер карты (талона)',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Evn_setDate_Range',
					'label' => 'Период',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'Lpu_eid',
					'label' => 'ЛПУ',
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
					'field' => 'EvnClass_SysNick',
					'label' => 'Сисник',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'printAll' => array(
				array(
					'field' => 'onePage',
					'label' => 'd',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'Person_id',
					'label' => 'd',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadGlobalStores' => array(
				array(
					'default' => 'all',
					'field' => 'mode',
					'label' => 'Вариант загрузки',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => '',
					'field' => 'stores',
					'label' => 'Списки',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'loadLpuFilialList' => array(
				array('default' => 'all', 'field' => 'mode', 'label' => 'Вариант загрузки филиалов', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
			),
			'loadLpuBuildingList' => array(
				array(
					'default' => 'all',
					'field' => 'mode',
					'label' => 'Вариант загрузки подразделений',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'МО',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadFederalKladrList' => array(
				array(
					'field'=>'Kladr_id',
					'label' => 'Kladr_id',
					'rules' => '',
					'type' => 'id'
					)
			),
			'loadFRMOSectionList' => array(
				array(
					'field' => 'FRMOSection_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'FRMOUnit_OID',
					'label' => 'OID подразделения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegisterMO_OID',
					'label' => 'OID МО',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => 'ban_percent',
					'type' => 'string'
				)
			),
			'loadFRMOUnitList' => array(
				array(
					'field' => 'FRMOUnit_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegisterMO_OID',
					'label' => 'OID МО',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'FRMOUnit_OID',
					'label' => 'OID подразделения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => 'ban_percent',
					'type' => 'string'
				)
			),
			'loadLpuSectionList' => array(
				array(
					//'default' => date('Y-m-d'),
					'field' => 'date',
					'label' => 'Дата',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'default' => 'all',
					'field' => 'mode',
					'label' => 'Вариант загрузки отделений',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'Тип группы отделений',
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
					'field' => 'LpuUnit_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Оргинизация (МО)',
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
					'field'	=> 'fromMZ',
					'label'	=> 'Запуск из АРМ Минздрава',
					'rules'	=> '',
					'type'	=> 'string',
					'default' => '1'
				),
				array(
					'field' => 'where',
					'label' => 'Условие',
					'rules' => '',
					'type' => 'string',
					'default' => ''
				),
			),
			'loadLpuSectionBedProfileList' => array(
				array(
					'default' => date('Y-m-d'),
					'field' => 'date',
					'label' => 'Дата',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionWard_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionBedProfile_id',
					'label' => 'Профиль коек',
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
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Is_Child',
					'label' => 'Является ребенком',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuSectionWardList' => array(
				array(
					'default' => date('Y-m-d'),
					'field' => 'date',
					'label' => 'Дата',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'default' => 'all',
					'field' => 'mode',
					'label' => 'Вариант загрузки отделений',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionWard_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionBedProfile_id',
					'label' => 'Профиль коек',
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
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Sex_id',
					'label' => 'пол',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadLpuUnitList' => array(
				array(
					'default' => date('Y-m-d'),
					'field' => 'date',
					'label' => 'Дата',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'default' => 'all',
					'field' => 'mode',
					'label' => 'Вариант загрузки отделений',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Группа отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadPostCombo' => array(),
			'loadLpuRegionList' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
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
					'field' => 'LpuUnit_id',
					'label' => 'Группа отделений',
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
					'field' => 'LpuRegionTypeList',
					'label' => 'Типы участков',
					'rules' => '',
					'type' => 'string'
				),
                array(
                    'field' => 'LpuRegionType_id',
                    'label' => 'Идентификатор типа участка',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'LpuRegionType_SysNick',
                    'label' => 'Псевдоним типа участка',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'LpuRegion_id',
                    'label' => 'Идентификатор участка',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
					'field' => 'LpuRegionType_ids',
					'label' => 'Типы участков',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuAttachType_id',
					'label' => 'Тип прикрепления',
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
					'default' => 0,
					'field' => 'showOpenerOnlyLpuRegions',
					'label' => 'Отображать только открытые участки или все', // 1 - только открытые, 0 - все
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'showCrossedLpuRegions',
					'label' => 'Отображать участки пересекающиеся с переданным периодом', // 1 - только пересекающиеся, 0 - все
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuRegion_begDate',
					'label' => 'Начало периода',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuRegion_endDate',
					'label' => 'Окончание периода',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadPersonCureHistoryList' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'filterData',
					'label' => 'Дополнительные фильтры',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadPersonData' => array(
				array(
					'field' => 'mode',
					'label' => 'Тип запроса',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LoadShort',
					'label' => 'Загружать сокращенный набор полей',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'loadFromDB',
					'label' => 'Не использовать кэш, загружать из БД',
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
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор состояния человека',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Идентификатор направления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'additionalFields',
					'label' => 'Дополнительные поля',
					'rules' => '',
					'type' => 'json_array'
				),
				array(
					'field' => 'isLis',
					'label' => 'флаг ЛИС',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadMedStatWorkPlace' => array(
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_From',
					'label' => 'Диагноз с',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Diag_To',
					'label' => 'Диагноз до',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'begDate',
					'label' => 'Начало периода',
					'rules' => 'required',
					'type' => 'date'
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
					'field' => 'EvnClass_id',
					'label' => 'Тип документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_NumCard',
					'label' => 'Номер документа',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveSystemErrorFixed' => array(
				array(
					'field' => 'SystemError_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'SystemError_Fixed',
					'label' => 'Исправлено',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'sendEmailTest' => array(
				array(
					'field' => 'to_email',
					'label' => 'e-mail',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'sendSMSTest' => array(
				array(
					'field' => 'to_phone',
					'label' => 'phone',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'loadResultDeseaseLeaveTypeList'=>array(

			),
			'loadTreatmentClassServiceTypeList'=>array(

			),
			'loadTreatmentClassVizitTypeList'=>array(

			),
			'loadMedicalCareKindLpuSectionProfileList'=>array(

			),
			'loadMedSpecLinkList'=>array(

			),
			'getLpuHTMList' => array(
				array(
					'field' => 'Region_id',
					'label' => 'Регион',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'На дату',
					'rules' => '',
					'type' => 'date'
				)
			),
			'SQLDebug' => array(
				array(
					'field' => 'database_type',
					'label' => 'Тип БД',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'output_type',
					'label' => 'Тип вывода',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'database_name',
					'label' => 'Имя БД',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Запрос',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'params',
					'label' => 'Параметры',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'getFullOutput',
					'label' => 'Получить данные полностью',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadWorkGraphGrid' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Сотрудник (место работы)',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field' => 'WorkGraph_Date',
					'label' => 'Дата дежурства',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				/*array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type'	=> 'int'
				),*/
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
				array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int')
			),
			'loadWorkGraphLpuSectionGrid' => array(
				array(
					'field'	=> 'WorkGraph_id',
					'label' => 'Идентификатор графика дежурств',
					'rules' => '',
					'type'	=> 'int'
				)
			),
			'LoadWorkGraphLpuSection' => array(
				array(
					'field'	=> 'WorkGraph_id',
					'label' => 'Идентификатор графика дежурств',
					'rules' => '',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type'	=> 'int'
				)
			),
			'saveWorkGraphLpuSection' => array(
				array(
					'field'	=> 'WorkGraphLpuSection_id',
					'label'	=> 'WorkGraphLpuSection_id',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'WorkGraph_id',
					'label'	=> 'Идентификатор дежурства',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'LpuBuilding_id',
					'label'	=> 'Подразделение',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'LpuSection_id',
					'label'	=> 'Отделение',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field' => 'LpuSectionList',
					'label'	=> 'LpuSectionList',
					'rules'	=> '',
					'type'	=> 'string'
				)
			),
			'saveWorkGraph' => array(
				array(
					'field'	=> 'WorkGraph_id',
					'label' => 'Идентификатор графика дежурств',
					'rules' => '',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'MedStaffFact_id',
					'label'	=> 'Идентификатор сотрудника',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'WorkGraph_begDate',
					'label'	=> 'Дата начала',
					'rules'	=> 'required',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'WorkGraph_endDate',
					'label'	=> 'Дата окончания',
					'rules'	=> 'required',
					'type'	=> 'string'
				),
				array(
					'field' => 'del_ids',
					'label' => 'Строки, подлежащие удалению',
					'rules'	=> '',
					'type'	=> 'string'
				)

			),
			'loadWorkGraphData' => array(
				array(
					'field'	=> 'WorkGraph_id',
					'label' => 'Идентификатор графика дежурств',
					'rules' => '',
					'type'	=> 'int'
				)
			),
			'deleteWorkGraph' => array(
				array(
					'field'	=> 'WorkGraph_id',
					'label' => 'Идентификатор графика дежурств',
					'rules' => 'required',
					'type'	=> 'int'
				)
			),
			'deleteWorkGraphLpuSection' => array(
				array(
					'field'	=> 'WorkGraphLpuSection_id',
					'label' => 'Идентификатор дежурства',
					'rules' => 'required',
					'type'	=> 'int'
				)
			),
			'deleteWorkGraphLpuSectionArray' => array(
				array(
					'field' => 'new_ids',
					'label' => 'Строки, подлежащие удалению',
					'rules'	=> '',
					'type'	=> 'string'
				)
			),
			'loadOrgHeadList' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules'	=> '',
					'type'	=> 'id'
				)
			),
			'loadDispUslugaComplex' => array(
				array(
					'field' => 'DispClass_id',
					'label' => 'DispClass_id',
					'rules' => 'required',
					'type'	=> 'id'
				)
			),
			'loadMedicalCareCases' => array(
				array(
					'field'	=> 'Person_id',
					'label'	=> 'Идентификатор пациента',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field' => 'MedCareCasesDate_Range',
					'label'	=> 'Диапазон дат',
					'rules'	=> 'trim',
					'type'	=> 'daterange'
				)
			),
			'parsePerfLog' => array(
				array(
					'field'	=> 'list',
					'label'	=> 'Список файлов',
					'rules'	=> '',
					'type'	=> 'string'
				)
			),
			'checkIsEvnPLExist' => array(
				array(
					'field'	=> 'Person_id',
					'label'	=> 'Идентификатор пациента',
					'rules'	=> 'required',
					'type'	=> 'id'
				),
				array(
					'field'	=> 'MedStaffFact_id',
					'label'	=> 'Идентификатор места работы врача',
					'rules'	=> 'required',
					'type'	=> 'id'				),
			),
		);

		$this->init();
	}

	/**
	 * Дополнительная инициализация
	 */
	private function init() {
		$isLis = isset($_REQUEST['isLis']) ? $_REQUEST['isLis'] : null;
		$method = $this->router->fetch_method();

		if (!$this->usePostgreLis || !in_array($method, $this->moduleMethods) || empty($isLis)) {
			$this->load->database();
			$this->load->model('Common_model', 'dbmodel');
		}
	}

	/**
	*	Проверка наличия записей в очереди ActiveMQ
	*/	
	function checkActiveMQIsEmpty() {
		$data = $this->ProcessInputData('checkActiveMQIsEmpty', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->checkActiveMQIsEmpty($data);
		$this->ProcessModelSave($response, true, 'Ошибка проверки наличия записей в очереди ActiveMQ')->ReturnData();
		
		return true;
	}

	/**
	*	Сохранение ошибки
	*/
	function saveSystemError() {
		$data = $this->ProcessInputData('saveSystemError', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveSystemError($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения данных по ошибке')->ReturnData();

		return true;
	}

	/**
	*	Сохранение признака исправлена ошибки
	*/
	function saveSystemErrorFixed() {
		$data = $this->ProcessInputData('saveSystemErrorFixed', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveSystemErrorFixed($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения данных по ошибке')->ReturnData();

		return true;
	}

	/**
	*	Получение ошибки
	*/
	function loadSystemErrorsViewWindow() {
		$data = $this->ProcessInputData('loadSystemErrorsViewWindow', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadSystemErrorsViewWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @comment
	 */
	function loadResultDeseaseLeaveTypeList() {
		$data = $this->ProcessInputData('loadResultDeseaseLeaveTypeList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadResultDeseaseLeaveTypeList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @comment
	 */
	function loadTreatmentClassServiceTypeList() {
		$data = $this->ProcessInputData('loadTreatmentClassServiceTypeList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadTreatmentClassServiceTypeList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @comment
	 */
	function loadTreatmentClassVizitTypeList() {
		$data = $this->ProcessInputData('loadTreatmentClassVizitTypeList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadTreatmentClassVizitTypeList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @comment
	 */
	function loadMedicalCareKindLpuSectionProfileList() {
		$data = $this->ProcessInputData('loadMedicalCareKindLpuSectionProfileList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadMedicalCareKindLpuSectionProfileList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @comment
	 */
	function loadMedSpecLinkList() {
		$data = $this->ProcessInputData('loadMedSpecLinkList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadMedSpecLinkList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @comment
	 */
	function getLpuHTMList() {
		$data = $this->ProcessInputData('getLpuHTMList', true, true);
		if ($data) {
			$response = $this->dbmodel->getLpuHTMList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	*	Получение списка ошибок
	*/
	function loadSystemErrorGrid() {
		$data = $this->ProcessInputData('loadSystemErrorGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadSystemErrorGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	*	Получение списка профилей
	*/
	function loadLpuSectionProfileList() {
		$data = $this->ProcessInputData('loadLpuSectionProfileList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuSectionProfileList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	*	Получение списка дополнительных профилей
	*/
	function loadLpuSectionProfileDopList() {
		$data = $this->ProcessInputData('loadLpuSectionProfileDopList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuSectionProfileDopList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Создание хэшей для XmlTemplateHtml
	 */
	function createHashesForXmlTemplate() {
		$this->dbmodel->createHashesForXmlTemplate();
	}
	
	/**
	* Проверка конфигов
	*/
	function testShowConfig()
	{
		if (!isSuperadmin()) {
			$this->ReturnError('Функционал только для суперадмина');
			return false;
		}
		
		$this->CI = get_instance();
		$this->CI->config->load('mongodb');
		echo $this->CI->config->item('mongo_db');
	}
	
	/**
	* Тест отправки сообщения по почте.
	*/
	function sendEmailTest() 
	{
		$data = $this->ProcessInputData('sendEmailTest', true, true);
		if ($data === false) { return false; }
		
		if (!isSuperadmin()) {
			$this->ReturnError('Функционал только для суперадмина');
			return false;
		}
		
		if( !isset($this->email) ) {
			$this->load->library('email');
		}
		$this->email->sendPromed($data['to_email'], 'Тема проверка.', 'тест тест тест', '');
		
		print_r($this->email->_debug_msg);
		
		/*
		echo $this->email->print_debugger();
		
		$this->email->sendKvrachu($data['to_email'], 'Тема проверка.', 'тест тест тест', '');
		
		echo $this->email->print_debugger();
		*/
		return true;
	}

	/**
	 * Парсит Registry_EvnNum в темповую таблицу
	 */
	function parseRegistryEvnNum() {
		if (!isSuperAdmin()) {
			$this->ReturnError('Ошибка доступа');
			return false;
		}
		die('disabled');
		$this->dbmodel->parseRegistryEvnNum();

		return true;
	}

	/**
	* Тест отправки SMS-сообщения.
	*/
	function sendSMSTest() 
	{
		$data = $this->ProcessInputData('sendSMSTest', true, true);
		if ($data === false) { return false; }
		
		if (!isSuperadmin()) {
			$this->ReturnError('Функционал только для суперадмина');
			return false;
		}
		
		$this->load->model('TimetableGraf_model', 'TimetableGraf_model');
		require_once("promed/libraries/MarketSMS.php");

		// Транспорт для отправки
		$transport = new MarketSMS(array(
			'login' => 'svanooo',
			'password' => 'rdhfxe'
		));

		$sms_id = $this->TimetableGraf_model->getSmsId(array(
			'UserNotify_Phone' => $data['to_phone'],
			'User_id' => $data['pmUser_id'],
			'text' => 'тест тест тест'
		));
		if ($sms_id === false) {
			DieWithError("Не удалось создать смс-сообщение!");
			return;
		}
		
		// Отправка
		return $transport->send(array(
			'id' => 'pm_' . $sms_id,
			'number' => $data['to_phone'],
			'text' => toUTF('тест тест тест')
		));
		
		print_r($transport->information);
		
		return true;
	}

	/**
	 *  Получение списка ЛПУ с особой сортировкой для выбора
	 */
	function loadLpuSelectList() {
		$data = $this->ProcessInputData('loadLpuSelectList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadLpuSelectList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка ТАП и КВС
	 *  Входящие данные: <фильтры>
	 *  На выходе: JSON-строка
	 *  Используется: журнал регистрации ЛВН
	 */
	function loadEvnPLEvnPSGrid() {
		$data = $this->ProcessInputData('loadEvnPLEvnPSGrid', true, true);
		if ($data) {
			$response = $this->dbmodel->loadEvnPLEvnPSGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 *  Получение данных глобальных справочников
	 *  Входящие данные: stores
	 *  На выходе: JSON-строка
	 *  Используется: вход в систему
	 */
	function loadGlobalStores() {
		$data = $this->ProcessInputData('loadGlobalStores', true, true);
		if ($data) {
			$this->load->library('textlog', array('file' => 'loadGlobalStores_' . date('Y-m-d') . '.log'));

			$this->load->model('MedService_model', 'MedService_model');
			$this->load->model('MedPersonal_model', 'MedPersonal_model');
			$response = array();
			if (!empty($data['Lpu_id'])) {
				$this->textlog->add('Lpu_id: ' . $data['Lpu_id'] . ', memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
				$response['LpuFilial'] = $this->dbmodel->loadLpuFilialList($data);
				$this->textlog->add('loadLpuFilialList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
				$response['LpuBuilding'] = $this->dbmodel->loadLpuBuildingList($data);
				$this->textlog->add('loadLpuBuildingList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
				$response['LpuUnit'] = $this->dbmodel->loadLpuUnitList($data);
				$this->textlog->add('loadLpuUnitList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
				$response['LpuSection'] = $this->dbmodel->loadLpuSectionList($data);
				$this->textlog->add('loadLpuSectionList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
				$response['LpuSectionWard'] = $this->dbmodel->loadLpuSectionWardList($data);
				$this->textlog->add('loadLpuSectionWardList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
				$response['MedStaffFact'] = $this->MedPersonal_model->loadMedStaffFactList(array_merge($data, array('ignoreDisableInDocParam' => 1, 'mode' => 'all')));
				$this->textlog->add('loadMedStaffFactList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
				$data['Contragent_id'] = null; // костыль для того чтобы службы загружались без всякой ненужной фильтрации по Contragent_id хранящемуся в сессии
				$response['MedService'] = $this->MedService_model->loadMedServiceList(array_merge($data, array('Lpu_isAll' => 0, 'mode' => 'all')));
				$this->textlog->add('loadMedServiceList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
				if (getRegionNick() == 'ekb') {
					$response['LpuDispContract'] = $this->dbmodel->loadLpuDispContractList($data);
					$this->textlog->add('loadLpuDispContractList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
				}
			}

			$response['FederalKladr'] = $this->dbmodel->loadFederalKladrList($data);
			$this->textlog->add('loadFederalKladrList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
			$response['ResultDeseaseLeaveType'] = $this->dbmodel->loadResultDeseaseLeaveTypeList($data);
			$this->textlog->add('loadResultDeseaseLeaveTypeList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
			$response['TreatmentClassServiceType'] = $this->dbmodel->loadTreatmentClassServiceTypeList($data);
			$this->textlog->add('loadTreatmentClassServiceTypeList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
			$response['TreatmentClassVizitType'] = $this->dbmodel->loadTreatmentClassVizitTypeList($data);
			$this->textlog->add('loadTreatmentClassVizitTypeList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
			$response['MedicalCareKindLpuSectionProfile'] = $this->dbmodel->loadMedicalCareKindLpuSectionProfileList($data);
			$this->textlog->add('loadMedicalCareKindLpuSectionProfileList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");

			if (getRegionNick() == 'pskov') {
				$response['LpuSectionProfileMedSpecOms'] = $this->dbmodel->loadLpuSectionProfileMedSpecOms();
				$this->textlog->add('loadLpuSectionProfileMedSpecOms, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
				$response['UslugaComplexMedSpec'] = $this->dbmodel->loadUslugaComplexMedSpec();
				$this->textlog->add('loadUslugaComplexMedSpec, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
			}

			if ($_SESSION['region']['nick'] == 'kareliya') {
				$response['MedSpecLink'] = $this->dbmodel->loadMedSpecLinkList($data);
				$this->textlog->add('loadMedSpecLinkList, memory: ' . (memory_get_usage() / 1024 / 1024) . " MB");
			}
			$this->ReturnData($response);
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение справочника филиалов
	 *  Входящие данные: $_POST['date']
	 *  На выходе: JSON-строка
	 */
	public function loadLpuFilialList() {
		$data = $this->ProcessInputData('loadLpuFilialList', true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuFilialList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Получение справочника подразделений
	 *  Входящие данные: $_POST['date']
	 *  На выходе: JSON-строка
	 *  Используется: форма потокового ввода рецептов
	 */
	function loadLpuBuildingList() {
		$data = $this->ProcessInputData('loadLpuBuildingList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadLpuBuildingList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение справочника отделений
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования адреса
	 */
	function loadFederalKladrList() {
		$data = $this->ProcessInputData('loadFederalKladrList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadFederalKladrList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 *  На выходе: JSON-строка
	 */
	function loadFRMOSectionList() {
		$data = $this->ProcessInputData('loadFRMOSectionList', true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadFRMOSectionList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 *  На выходе: JSON-строка
	 */
	function loadFRMOUnitList() {
		$data = $this->ProcessInputData('loadFRMOUnitList', true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadFRMOUnitList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 *  Получение справочника отделений
	 *  Входящие данные: $_POST['date']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования рецепта
	 *                форма потокового ввода рецептов
	 */
	function loadLpuSectionList() {
		$data = $this->ProcessInputData('loadLpuSectionList', false, true);
		$sessionParams = getSessionParams();
		$data['session'] = $sessionParams['session'];
		if ($data) {
			$response = $this->dbmodel->loadLpuSectionList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 *  Получение справочника палат
	 *  Входящие данные: $_POST['date']
	 *  На выходе: JSON-строка
	 *  Используется: везде
	 */
	function loadLpuSectionWardList() {
		$data = $this->ProcessInputData('loadLpuSectionWardList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadLpuSectionWardList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение справочника профиля коек
	 *  Входящие данные: $_POST['date']
	 *  На выходе: JSON-строка
	 *  Используется: везде
	 */
	function loadLpuSectionBedProfileList() {
		$data = $this->ProcessInputData('loadLpuSectionBedProfileList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadLpuSectionBedProfileList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 *  Получение списка группы отделений
	 *  Входящие данные: $_POST['date']
	 *  На выходе: JSON-строка
	 *  Используется: форма потокового ввода рецептов
	 */
	function loadLpuUnitList() {
		$data = $this->ProcessInputData('loadLpuUnitList', true);
		if ($data) {
			$response = $this->dbmodel->loadLpuUnitList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 *  Получение справочника участков
	 *  Входящие данные: или нет или $_POST['LpuUnit_id'] или $_POST['Lpu_id'] или $_POST['MedPersonal_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма поиска карты пациента
	 */
	function loadLpuRegionList() {
		$data = $this->ProcessInputData('loadLpuRegionList', true, true);
		if ($data === false) { return false;}

		$response = $this->dbmodel->loadLpuRegionList($data);

		if ( isset($_POST['add_without_region_line']) && $_POST['add_without_region_line'] == true ) {
			$this->OutData[] = array(
				"LpuRegion_id" => -1,
				"Lpu_id" => !empty($data['Lpu_id']) ? $data['Lpu_id'] : null,
				"LpuRegion_Name" => 'Без участка',
				"LpuRegion_Descr" => '',
				"LpuRegionType_id" => 0,
				"LpuRegionType_SysNick" => '',
				"LpuRegionType_Name" => ''
			);
		}

		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 *  Получение данных о льготнике
	 *  Входящие данные: $_POST['Person_id'],
	 *  На выходе: JSON-строка
	 *  Используется: окно просмотра истории лечения
	 */
	function loadPersonCureHistoryList() {
		$archive_database_enable = $this->config->item('archive_database_enable');
		// если по архивным - используем архивную БД
		if (!empty($archive_database_enable) && !empty($_REQUEST['useArchive'])) {
			$this->db = null;
			$this->load->database('archive', false);
		}

		$data = $this->ProcessInputData('loadPersonCureHistoryList', true);
		if ($data) {
			$response = $this->dbmodel->loadPersonCureHistoryList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 *  Получение данных о льготнике
	 *  Входящие данные: $_POST['Person_id'],
	 *                   $_POST['Server_id']
	 *  На выходе: JSON-строка
	 *  Используется: компонент sw.Promed.PersonInformatiobPanel
	 */
	function loadPersonData() {
		$logging = true; //логирование обращений к сессии и к бд
	
		$this->load->helper('Text');

		$data = array();
		$val  = array();

		$load_from_db = false; //загрузка из дб, игнорируя кэш
		$sess_var = 'PersData_Simple';
		$data['Person_id'] = 0;
		$data['Server_id'] = 0;
		$data['PersonEvn_id'] = 0;
		$data = $this->ProcessInputData('loadPersonData');

		if ( $data === false ) {
			return false;
		}
		if ( !empty($data['mode']) ) {
			$sess_var = 'PersData_'.$data['mode'];
		}
		if ( isset($data['loadFromDB']) && $data['loadFromDB'] == 'true' ) {
			$load_from_db = true;
		}
		// todo: Если есть Person_id и нет PersonEvn_id то ищем в MongoDB по Person_id, иначе ищем по паре PersonEvn_id/Server_id
		// Если данных в MongoDB нет, то читаем из БД / сохраняем в Mongo (на X времени)
		// Иначе читаем из MongoDB нужные данные

		if ($this->usePostgreLis && $data['isLis']) {
			$this->load->swapi('lis');
			$res = $this->lis->GET('Person', $data, 'single');
			if (!$this->isSuccessful($res)) {
				return $res;
			}

			$this->ProcessModelList([$res])->ReturnData();
		} else {
			// данные о человеке из сессии
			if (
				!$load_from_db &&
				isset($_SESSION[$sess_var]) &&
				((
						!isset($data['PersonEvn_id']) &&
						isset($data['Person_id']) &&
						isset($_SESSION[$sess_var]['Person_id']) &&
						$_SESSION[$sess_var]['Person_id'] == $data['Person_id']
					) ||
					(
						isset($data['PersonEvn_id']) &&
						isset($_SESSION[$sess_var]['PersonEvn_id']) &&
						$data['PersonEvn_id'] == $_SESSION[$sess_var]['PersonEvn_id'] &&
						isset($data['Server_id']) &&
						isset($_SESSION[$sess_var]['Server_id']) &&
						$data['Server_id'] == $_SESSION[$sess_var]['Server_id']
					))
			)
			{
				//пишем в лог id пользователя, данные для которого извлекли из бд
				$this->ReturnData(array($_SESSION[$sess_var]));
				return true;
			}
			// тянем из базы
			$response = $this->dbmodel->loadPersonData($data);
			$this->ProcessModelList($response)->ReturnData();
			$val = $this->GetOutData(0);
			// записываем в сессию
			$_SESSION[$sess_var] = $val;
			// если запрашивали на определенное событие, то пишем еще и идентификатор события добавления периодики
			if ( isset($data['PersonEvn_id']) && $data['PersonEvn_id'] > 0 && isset($data['Server_id']) )
			{
				$_SESSION[$sess_var]['PersonEvn_id'] = $data['PersonEvn_id'];
				$_SESSION[$sess_var]['Server_id'] = $data['Server_id'];
			}
		}

		return true;
	}
	
	/**
	 *  Получение данных от текущей дате, времени и пользователе
	 *  Входящие данные: нет
	 *  На выходе: JSON-строка
	 *  Используется: в формах потокового ввода
	 */
	function getCurrentTimeAndUser() {
		$this->load->helper('Text');
		$dateTime = $this->dbmodel->getCurrentDateTime();
		$val = array(
			'pmUser_Name' => $_SESSION['user'],
			'begDate' => $dateTime['date'],
			'begTime' => $dateTime['time']
		);
		$this->ReturnData($val);
	}


	/**
	*  Получение текущей даты и времени сервера
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: функция setCurrentDateTime
	*/
	function getCurrentDateTime() {
		if ($this->usePostgreLis && isset($_POST['ARMType']) && in_array($_POST['ARMType'], ['lis'])) {
			$this->load->swapi('lis');
			$dateTime = $this->lis->GET('Common/DateTime', 'single');
			if (!$this->isSuccessful($dateTime)) {
				return $dateTime;
			}
			if ($dateTime['data']) {
				$dateTime = $dateTime['data'];
			}
		} else {
			$dateTime = $this->dbmodel->getCurrentDateTime();
		}
		echo json_encode(
			array(
				'Date' => $dateTime['date'],
				'Time' => substr($dateTime['time'], 0, 5)
			)
		);
		return true;
	}
	
	/**
	 * Функция
	 */
	function showFm() {
		if (isset($_GET['name']) /*&& preg_match("/^([0-9a-z]+)$/i", $_GET['name'])*/)
		{
			$n = $_GET['name'];
		}
		else
		{
			echo 'неправильный параметр';
			return false;
		}
		$f = $_SERVER['DOCUMENT_ROOT'].'/promed/views/federal_mes/'.$n.'.htm';
		if($s = @file_get_contents($f))
		{
			if ( defined('USE_UTF') && USE_UTF == true ) {
				$s = str_replace('charset=windows-1251', 'charset=utf-8', $s);
				$s = toUTF($s, true);
			}

			echo $s;
		}
		else
		{
			echo 'файл не найден';
		}
		return true;
	}

	/**
	 *  Подписывание документа
	 *  Входящие данные: $_POST
	 *  На выходе: JSON-строка
	 *  Используется: общая функция signedDocument
	 */
	function signedDocument() {
		$data = $this->ProcessInputData('signedDocument', true);
		if ($data) {
			$response = $this->dbmodel->signedDocument($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение данных для проверки наличия извещения по специфике заболевания
	 *  Входящие данные: $_POST
	 *  На выходе: JSON-строка
	 *  Используется: общая функция checkEvnNotify
	 */
	function checkEvnNotify() {
		$data = $this->ProcessInputData('checkEvnNotify', true);
		if ($data) {
			$response = $this->dbmodel->checkEvnNotify($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение данных для проверки наличия извещения по специфике профзаболевания
	 *  Входящие данные: $_POST
	 *  На выходе: JSON-строка
	 *  Используется: общая функция checkEvnNotify
	 */
	function checkEvnNotifyProf() {
		$data = $this->ProcessInputData('checkEvnNotifyProf', true);
		if ($data) {
			$response = $this->dbmodel->checkEvnNotifyProf($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 *  Проверка существования записи в регистре по суицидам и необходимости внесения
	 *  Входящие данные: $_POST
	 *  На выходе: JSON-строка
	 */
	function checkSuicideRegistry() {
		$data = $this->ProcessInputData('checkSuicideRegistry', true);
		if ($data) {
			$response = $this->dbmodel->checkSuicideRegistry($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Загрузка списка направлений для АРМ мед. статистика
	 */
	function loadMedStatWorkPlace() {
		$data = $this->ProcessInputData('loadMedStatWorkPlace', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadMedStatWorkPlace($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 *	Сохранение документа или события и его дочерних событий для другого человека
	 *	@task	https://redmine.swan.perm.ru/issues/13357
	 */
	function setAnotherPersonForDocument() {
		$data = $this->ProcessInputData('setAnotherPersonForDocument', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->setAnotherPersonForDocument($data);
		$this->ProcessModelSave($response, true, 'Ошибка при смене пациента в документе')->ReturnData();

		return true;
	}
	
	/**
	*  Получение комбо специальностей врачей
	*/	
	function loadPostCombo() {
		$data = $this->ProcessInputData('loadPostCombo', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadPostCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *
	 * @return type 
	 */
	function printAll(){
		$data = $this->ProcessInputData('printAll', true);
		if ($data === false) { return false; }
		
		require_once('vendor/autoload.php');
		$mpdf = new \Mpdf\Mpdf([
			'mode' => 'utf-8',		// кодировка (по умолчанию UTF-8)
			'format' => 'A4',		// - формат документа
		]);


		$mpdf->SetDisplayMode('fullpage');

		// Adding CSS support$styles = file_get_contents('css/emk.css');
		$stylesheet =file_get_contents('css/emk.css');
		$mpdf->CSSselectMedia = 'pdf';
		$mpdf->WriteHTML($stylesheet, 1);
		$mpdf->list_indent_first_level = 1;
		
		if (isset($_POST['PrintArr'])) {
			ConvertFromWin1251ToUTF8($_POST['PrintArr']);
			$PrintArr = json_decode($_POST['PrintArr'], true);
			$s = count($PrintArr);
			foreach($PrintArr as $item){
				$s--;
				switch($item['type']){
					case 'EvnDirection':
						$data['EvnDirection_id']=$item['id'];
						$doc= $this->dbmodel->getEvnDirectionPrintData($data);
						$mpdf->WriteHTML($doc, 2);
						unset($data['EvnDirection_id']);
						break;
					case 'EvnRecept':
						$data['EvnRecept_id']=$item['id'];
						$doc= $this->dbmodel->getEvnReceptPrintData($data);
						$mpdf->WriteHTML($doc, 2);
						if($data['onePage']=='false'){
							$mpdf->AddPage();
						}
						$doc= $this->dbmodel->getEvnReceptDarkSidePrintData();
						$mpdf->WriteHTML($doc, 2);
						unset($data['EvnRecept_id']);
						break;
					case 'EvnXml':
						$data['EvnXml_id']=$item['id'];
						$data['noXmlType']=1;
						$doc= $this->dbmodel->getEvnXmlPrintData($data);
						
						$mpdf->WriteHTML($doc, 2);
						unset($data['EvnXml_id']);
						break;
					case 'EvnUslugaCommon':case 'EvnUslugaPar':
						$data['object']=$item['type'];
						$data['object_id']=$item['type'].'_id';
						$data['object_value']=$item['id'];
						$data['view_section']='main';
						$doc= $this->dbmodel->getEvnUslugaCommonPrintData($data);
						$mpdf->WriteHTML($doc, 2);
						unset($data['object']);
						unset($data['object_id']);
						unset($data['object_value']);
						unset($data['view_section']);
						break;
					case 'EvnUslugaOper':
						//print_r($data);
						$data['object']=$item['type'];
						$data['object_id']=$item['type'].'_id';
						$data['object_value']=$item['id'];
						$data['view_section']='main';
						$doc= $this->dbmodel->getEvnUslugaCommonPrintData($data);
						$mpdf->WriteHTML($doc, 2);
						unset($data['object']);
						unset($data['object_id']);
						unset($data['object_value']);
						unset($data['view_section']);
						break;
				}
				if($data['onePage']=='false'&&$s>0){
					$mpdf->AddPage();
				}
			}
			$name = EXPORTPATH_PDF_PRINT.'print'.swGenRandomString(32).'.pdf';
			while ( file_exists($name) ) {
				$name = EXPORTPATH_PDF_PRINT.'print'.swGenRandomString(32).'.pdf';
			}
			$mpdf->Output($name, 'F');
			
			$this->ReturnData(array('success'=>true,'path'=>$name));
			return true;
		}
		
	}
	
	/**
	 * SQLDebug - отладка SQL запросов
	 */
	function SQLDebug() {
		$data = $this->ProcessInputData('SQLDebug', true, true, false, false, false);
		if ($data === false) { return false; }
		
		if (!isSuperadmin()) {
			echo 'Функционал только для суперадмина';
			return false;
		}

		set_time_limit(60);
		
        echo "
			<html>
			<head>
				<title>Отладка SQL-запроса</title>
				<style type='text/css'>
					h1 { font-size: 24px; }
					table { border-collapse: collapse; }
					td { border: solid 1px black; padding: 2px 5px; text-align: left; font-size: 9pt; }
					tr.header td { font-weight: bolder; text-align: center; background-color: #dddddd; }
					td.header { font-weight: bolder; text-align: left; background-color: #dddddd; }
					td.null { background-color: #FFFFE1; }
				</style>
			</head>
			<body>
		";
		
		unset($this->db);
		
		$database = '';
		switch($data['database_type']) {
			case 0:
				$database = 'search';
			break;
			case 1:
				$database = 'registry';
			break;
			case 2:
				$database = 'default';
			break;
			case 3:
				$database = 'archive';
			break;
			case 4:
				$database = $data['database_name'];
			break;
		}
		
		if (empty($database)) {
			echo 'Не выбрана база данных';
			return false;
		}
		
		// подключаемся к нужной БД
		$this->load->database($database);
		
		$pattern = '/\bdelete\b|\bupdate\b|\bdrop\b|\binsert\b/i';
		$check = preg_match_all($pattern, $data['query'], $matches);
		if ($check === false) {
			echo 'Ошибка проверки безопасности запроса';
			return false;
		}
		
		if ($check > 0) {
			foreach ($matches as $match) {
				echo 'Обнаружено исользование '.$match[0].'<br>';
			}
			echo '<br><b>Выполнение запроса невозможно</b>';
			return false;
		}

		// выполняем запрос
		$response = $this->dbmodel->SQLDebug($data);

		if (is_array($response)) {
			if ($data['output_type'] == 1) {
				echo json_encode($response);
			} else {
				if (count($response) > 0) {
					echo "<table>";
					echo "<tr class='header'>";
					echo "<td></td>";
					foreach ($response[0] as $key => $value) {
						echo "<td>{$key}</td>";
					}
					echo "</tr>";
					$rownum = 0;
					foreach ($response as $row) {
						echo "<tr>";
						$rownum++;
						echo "<td>{$rownum}</td>";
						foreach ($row as $key => $val) {
							$vl = $val;
							if (isset($vl)) {
								if (is_object($vl) && get_class($vl) == 'DateTime') {
									$vl = $vl->format("Y-m-d H:i:s.u");
								} else if (empty($data['getFullOutput']) && strlen($vl) > 300) {
									$vl = mb_substr($vl, 0, 300) . '...';
								}

								echo '<td>' . htmlspecialchars($vl) . '</td>';
							} else {
								echo '<td class="null">NULL</td>';
							}
						}
						echo "</tr>";
					}
					echo "</table>";
				} else {
					echo 'Запрос не вернул записей';
				}
			}
		}
		
		echo "
			</body>
			</html>
		";
	}
	
	/**
	 * запрос на проверку подключения
	 */
	function checkConnect() 
	{
		$this->ReturnData(array('success'=>true));
	}

	/**
	 * Загрузка грида графиков дежурств
	 */
	function loadWorkGraphGrid() {
		$data = $this->ProcessInputData('loadWorkGraphGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadWorkGraphGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка списка отделений
	 */
	function loadWorkGraphLpuSectionGrid()
	{
		$data = $this->ProcessInputData('loadWorkGraphLpuSectionGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadWorkGraphLpuSectionGrid($data);
		//var_dump($response);die;
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение конкретного id строки графика дежурств
	 */
	function LoadWorkGraphLpuSection()
	{
		$data = $this->ProcessInputData('LoadWorkGraphLpuSection', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->LoadWorkGraphLpuSection($data);
		//var_dump($response);die;
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных графика дежурств
	 */
	function loadWorkGraphData()
	{
		$data = $this->ProcessInputData('loadWorkGraphData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadWorkGraphData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Удаление строки графика дежурств
	 */
	function deleteWorkGraph()
	{
		$data = $this->ProcessInputData('deleteWorkGraph', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteWorkGraph($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение графика дежурств
	 */
	function saveWorkGraph()
	{
		$data = $this->ProcessInputData('saveWorkGraph', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveWorkGraph($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение отделений для графика дежурств
	 */
	function saveWorkGraphLpuSection()
	{
		$data = $this->ProcessInputData('saveWorkGraphLpuSection', true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->saveWorkGraphLpuSection($data);
		$this->ProcessModelSave($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Удаление отделений из графика дежурств
	 */
	function deleteWorkGraphLpuSection(){
		$data = $this->ProcessInputData('deleteWorkGraphLpuSection', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteWorkGraphLpuSection($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Удаление списка отделений из графика дежурств
	 */
	function deleteWorkGraphLpuSectionArray(){
		$data = $this->ProcessInputData('deleteWorkGraphLpuSectionArray', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteWorkGraphLpuSectionArray($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка главных врачей
	 */
	function loadOrgHeadGLList()
	{
		$data = $this->ProcessInputData('loadOrgHeadList', true);
		if ($data === false) { return false; }
		$data['Post_id'] = 1;
		$response = $this->dbmodel->loadOrgHeadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Получение списка главных бухгалтеров
	 */
	function loadOrgHeadBUHList()
	{
		$data = $this->ProcessInputData('loadOrgHeadList', true);
		if ($data === false) { return false; }
		$data['Post_id'] = 2;
		$response = $this->dbmodel->loadOrgHeadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка работников (Person_id и ФИО)
	 */
	function loadMedPersList()
	{
		$data = $this->ProcessInputData('loadOrgHeadList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadMedPersList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка возможных усуг для диспанцеризаций и осмотров
	 */
	function loadDispUslugaComplex()
	{
		$data = $this->ProcessInputData('loadDispUslugaComplex', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadDispUslugaComplex($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	* Получение списка случаев оказания МП
	*/
	function loadMedicalCareCases() {
		$data = $this->ProcessInputData('loadMedicalCareCases', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMedicalCareCases($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка справочника связей между профилем и специальностью. Для пензы
	 *
	 * @return bool
	 */
	function loadLpuSectionProfileMedSpecOms()
	{
		$response = $this->dbmodel->loadLpuSectionProfileMedSpecOms();
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка справочника между услугой и специальностью. Для пензы
	 *
	 * @return bool
	 */
	function loadUslugaComplexMedSpec()
	{
		$response = $this->dbmodel->loadUslugaComplexMedSpec();
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Разбор логов и сохранение в БД
	 */
	function parsePerfLog() {
		if (!isSuperAdmin()) {
			echo 'Функционал только для суперадмина';
			return false;
		}

		$data = $this->ProcessInputData('parsePerfLog', true);
		if ($data === false) { return false; }

		$resp = $this->dbmodel->parsePerfLog($data);
		if (isset($data['list'])) {
			$resp['list'] = $data['list'];
		}
		$this->load->view('parsePerfLog', $resp);
	}

	/**
	 * Получение ближайшего созданного случая по врачу на дату
	 */
	function checkIsEvnPLExist() {

		$data = $this->ProcessInputData('checkIsEvnPLExist', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkIsEvnPLExist($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;

	}

	/**
	 * Получение списка использованных моделей
	 */
	function getModelList() {
		$resp = array();
		if (!empty($_SESSION['modelList'])) {
			foreach($_SESSION['modelList'] as $key => $value) {
				$resp[] = array(
					'Model_id' => $key,
					'Model_Name' => $value
				);
			}
		}
		$this->ReturnData($resp);
	}

	/**
	 * Сброс списка использованных моделей
	 */
	function resetModelList() {
		unset($_SESSION['modelList']);
		$this->ReturnData(array('success' => true));
	}
}