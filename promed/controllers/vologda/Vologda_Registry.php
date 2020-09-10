<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry - операции с реестрами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @region       Vologda
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Bykov Stas aka Savage
* @version      06.11.2018
*/
require_once(APPPATH.'controllers/Registry.php');

class Vologda_Registry extends Registry {
	public $db = "registry";
	public $scheme = "r35";

	/**
	* comment
	*/
	function __construct() {
		parent::__construct();

		// Инициализация класса и настройки
		$this->inputRules['exportUnionRegistryToXmlCheckExist'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['saveRegistry'] = array(
			array(
				'field' => 'OrgSmo_id',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_Num',
				'label' => 'Номер счета',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistrySubType_id',
				'label' => 'Подтип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_accDate',
				'label' => 'Дата счета',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_begDate',
				'label' => 'Начало периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_endDate',
				'label' => 'Окончание периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_IsNotInsur',
				'label' => 'Незастрахованные лица',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsActive',
				'label' => 'Признак активного реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsZNO',
				'label' => 'ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsOnceInTwoYears',
				'label' => 'Раз в 2 года',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsRepeated',
				'label' => 'Повторная подача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsNew',
				'label' => 'Признак нового реестра',
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
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_cid',
				'label' => 'МО-контрагент',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Тип диспансеризации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_isPersFin',
				'label' => 'Подушевое финансирование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_did',
				'label' => 'Направившая организация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsFAP',
				'label' => 'ФАП',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['saveUnionRegistry'] = array(
			array(
				'field' => 'Registry_IsNew',
				'label' => 'Признак нового реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgSmo_id',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_Num',
				'label' => 'Номер счета',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistrySubType_id',
				'label' => 'Подтип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryGroupType_id',
				'label' => 'Тип объединенного реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_accDate',
				'label' => 'Дата счета',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_begDate',
				'label' => 'Начало периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_endDate',
				'label' => 'Окончание периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_IsNotInsur',
				'label' => 'Незастрахованные лица',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Тип диспансеризации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_isPersFin',
				'label' => 'Подушевое финансирование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsZNO',
				'label' => 'ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsOnceInTwoYears',
				'label' => 'Раз в 2 года',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsZNO',
				'label' => 'ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsRepeated',
				'label' => 'Повторная подача',
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
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_did',
				'label' => 'Направившая организация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_cid',
				'label' => 'МО-контрагент',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsFAP',
				'label' => 'ФАП',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['loadUnionRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
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
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStacType_id',
				'label' => 'Тип реестра стационара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_accYear',
				'label' => 'год',
				'rules' => '',
				'type' => 'int'
			),
		);

		$this->inputRules['deleteUnionRegistry'] = array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор финального реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['deleteUnionRegistryData'] = array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id'),
		);

		$this->inputRules['deleteUnionRegistryWithData'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор финального реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);
	
		$this->inputRules['loadRegistryData'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
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
				'field' => 'OrgSmo_id',
				'label' => 'СМО',
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
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
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
					'field' => 'Polis_Num',
					'label' => 'Полис',
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
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'filterRecords',
				'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
				'rules' => '',
				'default' => 1,
				'type' => 'int'
			),
			array(
				'field' => 'NumCard',
				'label' => 'Номер талона',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_disDate',
				'label' => 'Дата выписки',
				'rules' => '',
				'type' => 'date'
			)
		);

		$this->inputRules['reformUnionRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['loadRegistryErrorCom'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
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
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['loadRegistryErrorBDZ'] = array(
			array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int'),
			array('default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int'),
			array('field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id'),
		);

		$this->inputRules['loadRegistryErrorTFOMS'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
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
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'ФИО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryErrorType_Code',
				'label' => 'Код ошибка',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['loadRegistryError'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
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
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
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
				'field' => 'RegistryError_Code',
				'label' => 'Код ошибки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryErrorType_id',
				'label' => 'Ошибка',
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
			),
		);
		
		$this->inputRules['deleteRegistry'] = array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['importRegistryFromXml'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'Registry_IsNew', 'label' => 'Признак нового реестра', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Идентификатор типа реестра', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RegistryFile', 'label' => 'Файл', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'ignoreRegistryIdError', 'label' => 'Признак игнорирования несовпадения идентификатора реестра', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'ignoreDSCHETError', 'label' => 'Признак игнорирования несовпадения идентификатора реестра', 'rules' => '', 'type' => 'int' ],
		];

		$this->inputRules['importRegistryFromTFOMS'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'Registry_IsNew', 'label' => 'Признак нового реестра', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RegistryFile', 'label' => 'Файл', 'rules' => '', 'type' => 'string' ],
		];
		
		$this->inputRules['printRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);
		
		$this->inputRules['loadUnionRegistryChildGrid'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => 'required',
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
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Идентификатор типа реестра',
				'rules' => '',
				'type' => 'id'
			)
		);
		
		$this->inputRules['exportRegistryToXml'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsNew',
				'label' => 'Признак нового',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OverrideExportOneMoreOrUseExist',
				'label' => 'Скачать с сервера или перезаписать',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'withSign',
				'label' => 'Флаг выгрузки с подписанием',
				'rules' => '',
				'type' => 'int'
			),
		);

		$this->inputRules['exportRegistryToXmlCheckVolume'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['loadUnionRegistryGrid'] = array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
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
			),
			array(
				'field' => 'Registry_IsNew',
				'label' => 'Признак новых реестров',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['loadUnionRegistryErrorTFOMS'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'ФИО',
				'rules' => '',
				'type' => 'string'
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
				'field' => 'RegistryErrorType_Code',
				'label' => 'Код ошибки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryErrorClass_id',
				'label' => 'Вид ошибки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryErrorTFOMS_Comment',
				'label' => 'Комментарий',
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
		);

		$this->inputRules['loadUnionRegistryData'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
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
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
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
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NumCard',
				'label' => 'Номер карты',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistrySubType_id',
				'label' => 'Подтип реестра',
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
				'field' => 'Polis_Num',
				'label' => 'Полис',
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
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'filterRecords',
				'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
				'rules' => '',
				'default' => 1,
				'type' => 'int'
			),
		);

		$this->inputRules['setUnionRegistryStatus'] = array(
			array(
				'default' => null,
				'field' => 'Registry_ids',
				'label' => 'Идентификаторы реестров',
				'rules' => 'required',
				'type' => 'json_array',
				'assoc' => true
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['setRegistryStatus'] = array(
			array(
				'default' => null,
				'field' => 'Registry_ids',
				'label' => 'Идентификаторы реестров',
				'rules' => 'required',
				'type' => 'json_array',
				'assoc' => true
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsNew',
				'label' => 'Признак нового реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'is_manual',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['loadUnionRegistryEditForm'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор объединённого реестра', 'rules' => 'required', 'type' => 'id' ]
		];

		$this->inputRules['getLpuSidList'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ]
		];
	}

	/**
	 * Изменение статуса счета-реестра
	 * Входящие данные: ID рееестра и значение статуса
	 * На выходе: JSON-строка
	 * Используется: форма просмотра реестра (счета)
	 */
	public function setRegistryStatus() {
		$data = $this->ProcessInputData('setRegistryStatus', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setRegistryStatus($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * Получение списка данных объединённого реестра
	 */
	public function loadUnionRegistryData() {
		$data = $this->ProcessInputData('loadUnionRegistryData', true);
		if ($data === false) { return false; }

		$data['RegistrySubType_id'] = 2;

		$response = $this->dbmodel->loadRegistryData($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Функция проверяет, есть ли уже выгруженный файл реестра
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	public function exportUnionRegistryToXmlCheckExist() {
		$data = $this->ProcessInputData('exportUnionRegistryToXmlCheckExist', true);
		if ($data === false) { return false; }

		$res = $this->dbmodel->GetRegistryXmlExport($data);

		if (is_array($res) && count($res) > 0) {
			if ($res[0]['Registry_xmlExportPath'] == '1')
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'inprogress'));
				return true;
			}
			else if (strlen($res[0]['Registry_xmlExportPath']) > 0 && $res[0]['RegistryStatus_id'] == 4) // если уже выгружен реестр и оплаченный
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'onlyexists'));
				return true;
			}
			else if (strlen($res[0]['Registry_xmlExportPath']) > 0)
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'exists'));
				return true;
			}
			else {
				$this->ReturnData(array('success' => true, 'exportfile' => 'empty'));
				return true;
			}
		}
		else {
			$this->ReturnError('Ошибка получения данных по реестру');
			return false;
		}
	}

	/**
	 * Удаление финального реестра
	 */
	public function deleteUnionRegistry() {
		$data = $this->ProcessInputData('deleteUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Удаление данных из реестра по СМО
	 */
	public function deleteUnionRegistryData() {
		$data = $this->ProcessInputData('deleteUnionRegistryData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteUnionRegistryData($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Удаление финального реестра (с удалением случаев из предварительных реестров).
	 */
	public function deleteUnionRegistryWithData() {
		$data = $this->ProcessInputData('deleteUnionRegistryWithData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteUnionRegistryWithData($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}
	
	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	public function exportRegistryToXml() {
		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportRegistryToXml($data);
		$this->ProcessModelSave($response, true, 'Ошибка при экспорте реестров')->ReturnData();
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	public function exportUnionRegistryToXml() {
		$this->exportRegistryToXml();
	}

	/**
	 * Импорт реестра из XML
	 */
	public function importRegistryFromXml() {
		set_time_limit(0);

		$this->load->library('textlog', array('file' => 'importRegistryFromXml_' . date('Y_m_d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('Запуск');

		try {
			//$this->dbmodel->beginTransaction();

			$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
			$allowed_types = explode('|','zip|rar|xml');
			
			$data = $this->ProcessInputData('importRegistryFromXml', true);
			if ($data === false) { return false; }
			
			if (!isset($_FILES['RegistryFile'])) {
				throw new Exception('Не выбран файл реестра!', __LINE__);
			}
			
			if (!is_uploaded_file($_FILES['RegistryFile']['tmp_name']))
			{
				$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];
				switch($error)
				{
					case 1:
						$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
						break;
					case 2:
						$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
						break;
					case 3:
						$message = 'Этот файл был загружен не полностью.';
						break;
					case 4:
						$message = 'Вы не выбрали файл для загрузки.';
						break;
					case 6:
						$message = 'Временная директория не найдена.';
						break;
					case 7:
						$message = 'Файл не может быть записан на диск.';
						break;
					case 8:
						$message = 'Неверный формат файла.';
						break;
					default :
						$message = 'При загрузке файла произошла ошибка.';
						break;
				}

				throw new Exception($message, __LINE__);
			}
			
			// Тип файла разрешен к загрузке?
			$x = explode('.', $_FILES['RegistryFile']['name']);
			$file_data['file_ext'] = end($x);
			if (!in_array($file_data['file_ext'], $allowed_types)) {
				throw new Exception('Данный тип файла не разрешен.', __LINE__);
			}

			// Правильно ли указана директория для загрузки?
			if (!@is_dir($upload_path)) {
				mkdir( $upload_path );
			}
			if (!@is_dir($upload_path)) {
				throw new Exception('Путь для загрузки файлов некорректен.', __LINE__);
			}
			
			// Имеет ли директория для загрузки права на запись?
			if (!is_writable($upload_path)) {
				throw new Exception('Загрузка файла не возможна из-за прав пользователя.', __LINE__);
			}

			// получаем данные реестра
			$registrydata = $this->dbmodel->loadRegistryForImportXml($data);
			if (!is_array($registrydata) || count($registrydata) == 0) {
				throw new Exception('Ошибка чтения данных реестра', __LINE__);
			}

			$data['OrgSmo_id'] = $registrydata['OrgSmo_id'];
			$data['RegistryGroupType_id'] = $registrydata['RegistryGroupType_id'];
			$data['RegistrySubType_id'] = $registrydata['RegistrySubType_id'];
			$data['Registry_IsNotInsur'] = $registrydata['Registry_IsNotInsur'];
			$data['RegistryType_id'] = $registrydata['RegistryType_id'];
			$data['Registry_xmlExportPath'] = $registrydata['Registry_xmlExportPath'];
			$data['KatNasel_SysNick'] = $registrydata['KatNasel_SysNick'];

			$this->dbmodel->setRegistryEvnNum($data);

			if ($file_data['file_ext'] == 'xml') {
				$xmlfile = $_FILES['RegistryFile']['name'];
				if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile)){
					throw new Exception('Не удаётся переместить файл.', __LINE__);
				}
			} else {
				$mask = '/^[H|C|D|T][Y|V|P|S|T|O]?M\d+.*xml/i';

				$zip = new ZipArchive();
				if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) 
				{
					$xmlfile = "";
					for($i=0; $i<$zip->numFiles; $i++){
						$filename = $zip->getNameIndex($i);
						if ( preg_match($mask, $filename) > 0 ) {
							$xmlfile = $filename;
					}
				}

					$zip->extractTo( $upload_path );
					$zip->close();
				}
				unlink($_FILES["RegistryFile"]["tmp_name"]);
			}
					
					
			if ( empty($xmlfile) ) {
				throw new Exception('Файл не является архивом реестра.', __LINE__);
			}

			$this->textlog->add('Обрабатываем файл ' . $xmlfile);

			// Счётчики
			$recall = 0;
			$recerr = 0;

			$data['RegistryErrorTFOMSType_id'] = 3; // Ошибки МЭК

			// Удаляем ответ по этому реестру, если он уже был загружен
			$this->dbmodel->deleteRegistryErrorTFOMS($data);

			libxml_use_internal_errors(true);

			// Читаем XML-файл
			$xmlString = file_get_contents($upload_path.$xmlfile);
			$xml = new SimpleXMLElement($xmlString);
			unset($xmlString);

			if (
				!is_object($xml) || !property_exists($xml, 'SCHET')
				|| !property_exists($xml->SCHET, 'CODE') || strlen($xml->SCHET->CODE) == 0
				|| !property_exists($xml->SCHET, 'NSCHET') || strlen($xml->SCHET->NSCHET) == 0
			) {
				throw new Exception('XML файл не является файлом реестра.', __LINE__);
			}

			$export_file_name_array = explode('/', $data['Registry_xmlExportPath']);
			$export_file = array_pop($export_file_name_array);

			// В ответе фонда меняются местами отправитель и получатель, нужно проверять иначе
			if ( !empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2 && !empty($data['KatNasel_SysNick']) ) {
				// Проверка соответствия файла реестру
				$FILENAME = $xml->ZGLV->FILENAME->__toString();

				if ( empty($FILENAME) ) {
					throw new Exception('Ошибка при получении имени исходного файла из загруженного файла, импорт не произведен', __LINE__);
				}

				if ( $data['KatNasel_SysNick'] == 'oblast' ) {
					$orgSenderType = 'S';
				}
				else {
					$orgSenderType = 'T';
				}

				if ( !preg_match("/^(\w{1,2}){$orgSenderType}(\d+)M(\d+)_(\d+)$/", $FILENAME, $fileParts) ) {
					throw new Exception('Неверное имя файла', __LINE__);
				}

				$FILENAME = $fileParts[1] . 'M' . $fileParts[3] . $orgSenderType . $fileParts[2] . '_' . $fileParts[4];

				if ( $FILENAME != mb_substr($export_file, 0, mb_strlen($FILENAME)) ) {
					throw new Exception('Имя загружаемого файла не соответствует имени выгруженного файла, импорт не произведен', __LINE__);
				}
			}

			// Проверяем соответствие шапки реестра
			if ( empty($data['ignoreRegistryIdError']) ) {
				$CODE = (string) $xml->SCHET->CODE;
				if ( $registrydata['Registry_id'] != trim($CODE) ) {
					$data['Alert_Code'] = 'REGISTRY_ID';
					throw new Exception('Не совпадает идентификатор реестра и импортируемого файла, продолжить импорт?', __LINE__);
				}
			}
			$NSCHET = (string) $xml->SCHET->NSCHET;
			if ( trim($registrydata['Registry_Num']) != trim($NSCHET) ) {
				throw new Exception('Не совпадает номер реестра и импортируемого файла, импорт не произведен', __LINE__);
			}
			if ( empty($data['ignoreDSCHETError']) ) {
				$DSCHET = (string) $xml->SCHET->DSCHET;
				if ( $registrydata['Registry_accDate'] != date('d.m.Y', strtotime(trim($DSCHET))) ) {
					$data['Alert_Code'] = 'DSCHET';
					throw new Exception('Не совпадает дата реестра и импортируемого файла, продолжить импорт?', __LINE__);
				}
			}

			// Идём по случаям
			foreach ($xml->ZAP as $onezap) {
				// Идём по законченным случаям
				foreach($onezap->Z_SL as $onezsl) {
					$recall++;

					// Идём по случаям
					$SANK_DATA = [];

					$params = array(
						'Registry_IsNew' => $data['Registry_IsNew'],
						'Registry_id' => $data['Registry_id'],
						'RegistryType_id' => $data['RegistryType_id'],
						'RegistrySubType_id' => $data['RegistrySubType_id'],
						'RegistryErrorTFOMSType_id' => $data['RegistryErrorTFOMSType_id'],
						'session' => $data['session']

					);

					foreach ($onezsl->SL as $onesl) {
						if (empty($onesl->SL_ID)) {
							continue;
						}

						$SL_ID = (string)$onesl->SL_ID;
						$NHISTORY = (string)$onesl->NHISTORY;

						if (count($SANK_DATA) == 0) {
							$params['SL_ID'] = $SL_ID;
							// если ещё не добавляли ошибок к реестру, то проверяем соотвествие параметра реестру
							$evnData = $this->dbmodel->checkErrorDataInRegistry($params);
							if ( $evnData === false || !is_array($evnData) || !isset($evnData['Evn_id']) ) {
								throw new Exception('Идентификатор SL_ID = "' . $SL_ID . '" для случая № "' . $NHISTORY . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен', __LINE__);
							}

							$params['Registry_id'] = $evnData['Registry_id'];
							$params['RegistryType_id'] = $evnData['RegistryType_id'];
							$params['Evn_id'] = $evnData['Evn_id'];
							$params['RegistrySubType_id'] = $evnData['RegistrySubType_id'];
						}

						// ошибка на уровне случая
						if ( property_exists($onesl, 'SANK_SL') ) {
							foreach ($onesl->SANK_SL as $onesank) {
								$S_OSN = (string)$onesank->S_OSN;
								$S_COM = (string)$onesank->S_COM;

								$d = [
									'S_OSN' => $S_OSN,
									'SL_ID' => $SL_ID,
									'S_COM' => $S_COM
								];

								if ( in_array($S_OSN . "||" . $SL_ID . "||" . $S_COM, $SANK_DATA) ) {
									continue;
								}

								$response = $this->dbmodel->setErrorFromSMOImportRegistry($d, $params);

								if (!is_array($response)) {
									throw new Exception('Ошибка при обработке реестра!', __LINE__);
								}
								else if (!empty($response['Error_Msg'])) {
									throw new Exception($response['Error_Msg'], __LINE__);
								}

								$SANK_DATA[] = $S_OSN . "||" . $SL_ID . "||" . $S_COM;
							}
						}

						// ошибка на уровне законченного случая
						if ( property_exists($onezsl, 'SANK') ) {
							foreach ($onezsl->SANK as $onesank) {
								$SANK_SL_ID = (string)$onesank->SL_ID;

								if ( !empty($SANK_SL_ID) && $SANK_SL_ID != $SL_ID ) {
									continue;
								}

								$S_OSN = (string)$onesank->S_OSN;
								$S_COM = (string)$onesank->S_COM;

								$d = [
									'S_OSN' => $S_OSN,
									'SL_ID' => $SL_ID,
									'S_COM' => $S_COM
								];

								if ( in_array($S_OSN . "||" . $SL_ID . "||" . $S_COM, $SANK_DATA) ) {
									continue;
								}

								$response = $this->dbmodel->setErrorFromSMOImportRegistry($d, $params);

								if (!is_array($response)) {
									throw new Exception('Ошибка при обработке реестра!', __LINE__);
								}
								else if (!empty($response['Error_Msg'])) {
									throw new Exception($response['Error_Msg'], __LINE__);
								}

								$SANK_DATA[] = $S_OSN . "||" . $SL_ID . "||" . $S_COM;
							}
						}
					}

					if ( count($SANK_DATA) > 0 ) {
						$recerr++; // записей с ошибками
					}
				}
			}

			$this->textlog->add('Обрабатка файла ' . $xmlfile . ' завершена');

			// Пишем информацию об импорте в историю
			$data['Registry_RecordCount'] = $recall;
			$data['Registry_ErrorCount'] = $recerr;
			$this->dbmodel->dumpRegistryInformation($data, 3);

			//$this->dbmodel->commitTransaction();

			$this->ReturnData([ 'success' => true, 'Registry_id' => $data['Registry_id'], 'recAll' => $recall, 'recErr' => $recerr, 'Message' => 'Реестр успешно загружен.' ]);
		}
		catch ( Exception $e ) {
			//$this->dbmodel->rollbackTransaction();

			if ( !empty($data['Alert_Code']) ) {
				$this->textlog->add('Предупреждение (' . $e->getCode() . '): ' . $e->getMessage());
				$this->ReturnData([ 'success' => true, 'Alert_Msg' => $e->getMessage(), 'Alert_Code' => $data['Alert_Code'] ]);
			}
			else {
				$this->textlog->add('Ошибка (' . $e->getCode() . '): ' . $e->getMessage());
				$this->ReturnError($e->getMessage(), $e->getCode());
			}
		}

		$this->textlog->add('Финиш');

		return true;
	}

	/**
	 * Импорт реестра из ТФОМС
	 */
	public function importRegistryFromTFOMS() {
		set_time_limit(0);

		$this->load->library('textlog', array('file' => 'importRegistryFromTFOMS_' . date('Y_m_d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('Запуск');

		try {
			$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
			$allowed_types = explode('|','zip|rar|xml');

			$data = $this->ProcessInputData('importRegistryFromTFOMS', true);
			if ( $data === false ) { return false; }

			if ( !isset($_FILES['RegistryFile']) ) {
				throw new Exception('Не выбран файл реестра!', __LINE__);
			}

			if ( !is_uploaded_file($_FILES['RegistryFile']['tmp_name']) ) {
				$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];

				switch ( $error ) {
					case 1:
						$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
						break;
					case 2:
						$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
						break;
					case 3:
						$message = 'Этот файл был загружен не полностью.';
						break;
					case 4:
						$message = 'Вы не выбрали файл для загрузки.';
						break;
					case 6:
						$message = 'Временная директория не найдена.';
						break;
					case 7:
						$message = 'Файл не может быть записан на диск.';
						break;
					case 8:
						$message = 'Неверный формат файла.';
						break;
					default :
						$message = 'При загрузке файла произошла ошибка.';
						break;
				}

				throw new Exception($message, __LINE__);
			}

			// Тип файла разрешен к загрузке?
			$x = explode('.', $_FILES['RegistryFile']['name']);
			$file_data['file_ext'] = end($x);

			if ( !in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
				throw new Exception('Данный тип файла не разрешен.', __LINE__);
			}

			// Правильно ли указана директория для загрузки?
			if ( !@is_dir($upload_path) ) {
				mkdir( $upload_path );
			}

			if ( !@is_dir($upload_path) ) {
				throw new Exception('Путь для загрузки файлов некорректен.', __LINE__);
			}

			// Имеет ли директория для загрузки права на запись?
			if ( !is_writable($upload_path) ) {
				throw new Exception('Загрузка файла не возможна из-за прав пользователя.', __LINE__);
			}

			// получаем данные реестра
			$registrydata = $this->dbmodel->loadRegistryForImportXml($data);
			if (!is_array($registrydata) || count($registrydata) == 0) {
				throw new Exception('Ошибка чтения данных реестра', __LINE__);
			}

			$this->textlog->add('Получили данные реестра');

			$data['Registry_IsZNO'] = $registrydata['Registry_IsZNO'];
			$data['Registry_xmlExportPath'] = $registrydata['Registry_xmlExportPath'];
			$data['RegistryType_id'] = $registrydata['RegistryType_id'];
			$data['RegistrySubType_id'] = $registrydata['RegistrySubType_id'];

			$Registry_IsNew = (!empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2);

			$this->dbmodel->setRegistryParamsByType($data);

			$XmlFiles = [
				'C' => '',
				'DF' => '',
				'DO' => '',
				'DP' => '',
				'DS' => '',
				'DU' => '',
				'DV' => '',
				'H' => '',
				'T' => '',
				'L' => '',
				'LC' => '',
				'LF' => '',
				'LO' => '',
				'LP' => '',
				'LS' => '',
				'LT' => '',
				'LU' => '',
				'LV' => '',
				'V' => '',
			];

			if ( strtolower($file_data['file_ext']) == 'xml' ) {
				$xmlfile = $_FILES['RegistryFile']['name'];

				if ( !move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path . $xmlfile) ) {
					throw new Exception('Не удаётся переместить файл.', __LINE__);
				}

				if ( array_key_exists(strtoupper(substr($xmlfile, 0, 2)), $XmlFiles) ) {
					$XmlFiles[strtoupper(substr($xmlfile, 0, 2))] = $xmlfile;
				}
				else if ( array_key_exists(strtoupper(substr($xmlfile, 0, 1)), $XmlFiles) ) {
					$XmlFiles[strtoupper(substr($xmlfile, 0, 1))] = $xmlfile;
				}
			}
			else {
				// там должны быть файлы c*.xml, d*.xml, h*.xml, l*.xml, t*.xml и v*.xml
				// если их нет -> файл не является архивом реестра
				$zip = new ZipArchive();

				if ( $zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE ) {
					for ( $i = 0; $i < $zip->numFiles; $i++ ) {
						$filename = $zip->getNameIndex($i);

						if ( preg_match('/.*\.xml/i', $filename) > 0 ) {
							if ( array_key_exists(strtoupper(substr($filename, 0, 2)), $XmlFiles) ) {
								$XmlFiles[strtoupper(substr($filename, 0, 2))] = $filename;
							}
							else if ( array_key_exists(strtoupper(substr($filename, 0, 1)), $XmlFiles) ) {
								$XmlFiles[strtoupper(substr($filename, 0, 1))] = $filename;
							}
						}
					}

					$zip->extractTo($upload_path);
					$zip->close();
				}

				unlink($_FILES["RegistryFile"]["tmp_name"]);
			}

			if (
				(
					($data['Registry_IsZNO'] == 2 && empty($XmlFiles['C']))
					|| ($data['Registry_IsZNO'] != 2 && empty($XmlFiles['H']))
				)
				&& (
					($data['Registry_IsZNO'] == 2 && empty($XmlFiles['LC']))
					|| ($data['Registry_IsZNO'] != 2 && empty($XmlFiles['L']))
				)
				&& empty($XmlFiles['DF']) && empty($XmlFiles['LF'])
				&& empty($XmlFiles['DO']) && empty($XmlFiles['LO'])
				&& empty($XmlFiles['DP']) && empty($XmlFiles['LP'])
				&& empty($XmlFiles['DS']) && empty($XmlFiles['LS'])
				&& empty($XmlFiles['DU']) && empty($XmlFiles['LU'])
				&& empty($XmlFiles['DV']) && empty($XmlFiles['LV'])
				&& empty($XmlFiles['T']) && empty($XmlFiles['LT'])
				&& empty($XmlFiles['V'])
			) {
				throw new Exception('Файл не является архивом реестра.', __LINE__);
			}

			libxml_use_internal_errors(true);

			$export_file_name_array = explode('/', $data['Registry_xmlExportPath']);
			$export_file = array_pop($export_file_name_array);

			$recall = 0;
			$recErr = 0;

			foreach ( $XmlFiles as $XmlFileType => $xmlfile ) {
				if ( empty($xmlfile) ) {
					continue;
				}

				$this->textlog->add('Начало обработки файла ' . $xmlfile);

				$xml = new SimpleXMLElement(file_get_contents($upload_path . $xmlfile));

				foreach ( libxml_get_errors() as $error ) {
					throw new Exception('Файл не является архивом реестра.', __LINE__);
				}

				libxml_clear_errors();

				switch ( $XmlFileType ) {
					// Файл с результатом проверки ФЛК
					case 'V':
						$data['RegistryErrorTFOMSType_id'] = 1;

						// проверка соответствия файла реестру
						$FNAME_I = $xml->FNAME_I->__toString();

						if ( empty($FNAME_I) ) {
							throw new Exception('Ошибка при получении имени исходного файла из загруженного файла, импорт не произведен', __LINE__);
						}

						if ( $FNAME_I != mb_substr($export_file, 0, mb_strlen($FNAME_I)) ) {
							throw new Exception('Не совпадает название файла, импорт не произведен (V)', __LINE__);
						}

						$this->dbmodel->setRegistryEvnNum($data);

						if ( $Registry_IsNew == true && isset($this->dbmodel->registryEvnNum) && is_array($this->dbmodel->registryEvnNum) ) {
							$this->dbmodel->setRegistryEvnNumByNZAP($data);
						}

						$this->dbmodel->deleteRegistryErrorTFOMS($data);

						foreach ( $xml->PR as $onepr ) {

							if ($onepr->count() == 0) {
								throw new Exception('По результатам проверки реестра ошибок не обнаружено, импорт данных не требуется.');
							}

							$params = [
								'N_ZAP' => $onepr->N_ZAP->__toString(),
								'IDCASE' => $onepr->IDCASE->__toString(),
								'SL_ID' => $onepr->SL_ID->__toString(),
								'ID_PAC' => $onepr->ID_PAC->__toString(),
								'OSHIB' => $onepr->OSHIB->__toString(),
								'IM_POL' => $onepr->IM_POL->__toString(),
								'ZN_POL' => $onepr->ZN_POL->__toString(),
								'BAS_EL' => $onepr->BAS_EL->__toString(),
								'COMMENT' => $onepr->COMMENT->__toString(),
								'NSCHET' => $onepr->NSCHET->__toString(),
								'RegistryErrorClass_id' => 1,
								'RegistryErrorTFOMSType_id' => $data['RegistryErrorTFOMSType_id'],
								'RegistrySubType_id' => $data['RegistrySubType_id'],
								'Registry_IsNew' => $data['Registry_IsNew'],
								'pmUser_id' => $data['pmUser_id'],
							];

							$recall++;
							$recErr++;
							$SL_ID_array = [];

							if ( !empty($params['SL_ID']) ) {
								$SL_ID_array[] = $params['SL_ID'];
							}
							else if ( !empty($params['N_ZAP']) && isset($this->dbmodel->registryEvnNum) && is_array($this->dbmodel->registryEvnNum) ) {
								if ( $Registry_IsNew == true && isset($this->dbmodel->registryEvnNumByNZAP[$params['N_ZAP']]) ) {
									$SL_ID_array = $this->dbmodel->registryEvnNumByNZAP[$params['N_ZAP']];
								}
								else if ( isset($this->dbmodel->registryEvnNum[$params['N_ZAP']]) ) {
									$SL_ID_array = $this->dbmodel->registryEvnNum[$params['N_ZAP']];
								}
							}

							foreach ( $SL_ID_array as $SL_ID ) {
								$params['SL_ID'] = $SL_ID;
								$params['Registry_id'] = $data['Registry_id'];
								$evnData = $this->dbmodel->checkErrorDataInRegistry($params);

								if ( $evnData === false ) {
									$this->dbmodel->deleteRegistryErrorTFOMS($params);
									throw new Exception('Номер записи N_ZAP="' . $params['N_ZAP'] . '", SL_ID="' . $params['SL_ID'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен', __LINE__);
								}

								$params['Evn_id'] = $evnData['Evn_id'];
								$params['Registry_id'] = $evnData['Registry_id'];
								$params['RegistryType_id'] = $evnData['RegistryType_id'];

								$response = $this->dbmodel->setErrorFromImportRegistry($params, $data);

								if ( !is_array($response) || count($response) == 0 ) {
									throw new Exception('Ошибка при обработке реестра!', __LINE__);
								}
								else if ( !empty($response['Error_Msg']) ) {
									throw new Exception($response['Error_Msg'], __LINE__);
								}
							}
						}
						break;

					// Исходный файл с данными случаев
					case 'C':
					case 'DF':
					case 'DO':
					case 'DP':
					case 'DS':
					case 'DU':
					case 'DV':
					case 'H':
					case 'T':
						$data['RegistryErrorTFOMSType_id'] = 2;
						$OmsSprTerr_id = $this->dbmodel->getFirstResultFromQuery("select top 1 OmsSprTerr_id from v_OmsSprTerr with (nolock) where KLRgn_id = 35", []);
						$PolisFormType_id = $this->dbmodel->getFirstResultFromQuery("select top 1 PolisFormType_id from v_PolisFormType with (nolock) where PolisFormType_Code = 1", []);

						if ( $OmsSprTerr_id === false ) {
							throw new Exception('Ошибка при определении территории страхования', __LINE__);
						}

						if ( $PolisFormType_id === false ) {
							throw new Exception('Ошибка при определении формы изготовления полиса', __LINE__);
						}

						if ( !property_exists($xml, 'ZGLV') ) {
							throw new Exception('Неверный формат загруженного файла', __LINE__);
						}
						else if ( !property_exists($xml, 'ZAP') ) {
							throw new Exception('Неверный формат загруженного файла', __LINE__);
						}

						// проверка соответствия файла реестру
						$FILENAME = $xml->ZGLV->FILENAME->__toString();

						if ( empty($FILENAME) ) {
							throw new Exception('Ошибка при получении имени исходного файла из загруженного файла, импорт не произведен', __LINE__);
						}

						// В ответе фонда меняются местами отправитель и получатель, нужно проверять иначе
						if ( !preg_match("/^(\w{1,2})T(\d+)M(\d+)_(\d+)$/", $FILENAME, $fileParts) ) {
							throw new Exception('Неверное имя файла', __LINE__);
						}

						$FILENAME = $fileParts[1] . 'M' . $fileParts[3] . 'T' . $fileParts[2] . '_' . $fileParts[4];

						if ( $FILENAME != mb_substr($export_file, 0, mb_strlen($FILENAME)) ) {
							throw new Exception('Не совпадает название файла, импорт не произведен (' . $XmlFileType . ')', __LINE__);
						}

						$this->dbmodel->deleteRegistryErrorTFOMS($data);

						$this->dbmodel->setRegistryEvnNum($data);

						foreach ( $xml->ZAP as $onezap ) {
							if ( !property_exists($onezap, 'PACIENT') ) {
								continue;
							}
							else if ( !property_exists($onezap, 'Z_SL') ) {
								continue;
							}
							else if ( !property_exists($onezap, 'N_ZAP') ) {
								continue;
							}

							$params = [];
							$personData = [];

							$params['N_ZAP'] = $onezap->N_ZAP->__toString();
							$params['RegistryErrorClass_id'] = 2;
							$params['RegistryErrorTFOMSType_id'] = $data['RegistryErrorTFOMSType_id'];
							$params['RegistrySubType_id'] = $data['RegistrySubType_id'];
							$params['Registry_IsNew'] = $data['Registry_IsNew'];
							$params['pmUser_id'] = $data['pmUser_id'];

							foreach ( $onezap->PACIENT as $onepacient ) {
								$personData = [
									'ID_PAC' => $onepacient->ID_PAC->__toString(),
									'VPOLIS' => $onepacient->VPOLIS->__toString(),
									'SPOLIS' => $onepacient->SPOLIS->__toString(),
									'NPOLIS' => $onepacient->NPOLIS->__toString(),
									'ST_OKATO' => $onepacient->ST_OKATO->__toString(),
									'SMO' => $onepacient->SMO->__toString(),
									'SMO_OGRN' => $onepacient->SMO_OGRN->__toString(),
									'SMO_OK' => $onepacient->SMO_OK->__toString(),
									'SMO_NAM' => $onepacient->SMO_NAM->__toString(),
								];
							}

							if ( count($personData) == 0 || (empty($personData['VPOLIS']) && empty($personData['SPOLIS']) && empty($personData['NPOLIS'])) ) {
								continue;
							}

							if ( !empty($personData['SPOLIS']) ) {
								$personData['VPOLIS'] = 1;
							}
							else if ( strlen($personData['NPOLIS']) == 9 ) {
								$personData['VPOLIS'] = 3;
							}
							else if ( strlen($personData['NPOLIS']) == 16 ) {
								$personData['VPOLIS'] = 4;
							}
							else if ( empty($personData['VPOLIS']) ) {
								continue;
							}

							foreach ( $onezap->Z_SL as $onezsl ) {
								if ( !property_exists($onezsl, 'SL') ) {
									continue;
								}

								foreach ( $onezsl->SL as $onesl ) {
									if ( !property_exists($onesl, 'COMENTSL') ) {
										continue;
									}

									$COMENTSL = $onesl->COMENTSL->__toString();

									if ( empty($COMENTSL) ) {
										continue;
									}

									$errorCodes = [];
									$Polis_begDate = null;
									$Polis_endDate = null;

									if ( $COMENTSL == ';692;' ) {
										$errorCodes[] = '692';
										$Polis_begDate = '2012-12-27';
									}
									else {
										$commentParts = explode(';', $COMENTSL);

										foreach ( $commentParts as $element ) {
											if ( empty($element) ) {
												continue;
											}

											if ( preg_match('/^\d+$/', $element) ) {
												$errorCodes[] = $element;
											}
											else if ( preg_match('/^\d{4}-\d{2}-\d{2}$/', $element) ) {
												if ( empty($Polis_begDate) ) {
													$Polis_begDate = $element;
												}
												else {
													$Polis_endDate = $element;
												}
											}
										}
									}

									if ( count($errorCodes) == 0 && empty($Polis_begDate) ) {
										continue;
									}

									$params['BAS_EL'] = null;
									$params['COMMENT'] = 'Уточните данные пациента';
									$params['IM_POL'] = null;
									$params['SL_ID'] = $onesl->SL_ID->__toString();
									$params['Registry_id'] = $data['Registry_id'];

									$evnData = $this->dbmodel->checkErrorDataInRegistry($params);

									if ( $evnData === false ) {
										$this->dbmodel->deleteRegistryErrorTFOMS($params);
										throw new Exception('Номер записи N_ZAP="' . $params['N_ZAP'] . '", SL_ID="' . $params['SL_ID'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен', 100022);
									}

									$params['Registry_id'] = $evnData['Registry_id'];
									$params['RegistryType_id'] = $evnData['RegistryType_id'];
									$params['Evn_id'] = $evnData['Evn_id'];

									$recall++;

									if ( !empty($Polis_begDate) ) {
										// Обновляем данные страхования
										$response = $this->dbmodel->updatePersonPolis([
											'pmUser_id' => $data['pmUser_id'],
											'Person_id' => $evnData['Person_id'],
											'VPOLIS' => $personData['VPOLIS'],
											'SPOLIS' => $personData['SPOLIS'],
											'NPOLIS' => $personData['NPOLIS'],
											'SMO' => $personData['SMO'],
											'BEGDT' => $Polis_begDate,
											'ENDDT' => $Polis_endDate,
											'OmsSprTerr_id' => $OmsSprTerr_id,
											'PolisFormType_id' => $PolisFormType_id,
										]);

										if ( !empty($response) ) {
											throw new Exception($response, __LINE__);
										}
									}
									else if ( count($errorCodes) > 0 ) {
										foreach ( $errorCodes as $errorCode ) {
											$params['OSHIB'] = $errorCode;

											$recErr++;

											// Сохраняем ошибку
											$response = $this->dbmodel->setErrorFromImportRegistry($params, $data);

											if ( !is_array($response) || count($response) == 0 ) {
												throw new Exception('Ошибка при обработке реестра!', __LINE__);
											}
											else if ( !empty($response['Error_Msg']) ) {
												throw new Exception($response['Error_Msg'], __LINE__);
											}
										}
									}
								}
							}
						}
						break;

					// Исходный файл с перс. данными
					case 'L':
					case 'LC':
					case 'LF':
					case 'LO':
					case 'LP':
					case 'LS':
					case 'LU':
					case 'LT':
					case 'LV':
						$data['RegistryErrorTFOMSType_id'] = 2;

						if ( !property_exists($xml, 'ZGLV') ) {
							throw new Exception('Неверный формат загруженного файла', __LINE__);
						}

						// проверка соответствия файла реестру
						$FILENAME = $xml->ZGLV->FILENAME1->__toString();

						if ( empty($FILENAME) ) {
							throw new Exception('Ошибка при получении имени исходного файла из загруженного файла, импорт не произведен', __LINE__);
						}

						// В ответе фонда меняются местами отправитель и получатель, нужно проверять иначе
						if ( !preg_match("/^(\w{1,2})T(\d+)M(\d+)_(\d+)$/", $FILENAME, $fileParts) ) {
							throw new Exception('Неверное имя файла', __LINE__);
						}

						$FILENAME = $fileParts[1] . 'M' . $fileParts[3] . 'T' . $fileParts[2] . '_' . $fileParts[4];

						if ( $FILENAME != mb_substr($export_file, 0, mb_strlen($FILENAME)) ) {
							throw new Exception('Не совпадает название файла, импорт не произведен (' . $XmlFileType . ')', __LINE__);
						}

						//$this->dbmodel->deleteRegistryErrorTFOMS($data);

						foreach ( $xml->PERS as $onepers ) {
							if ( !property_exists($onepers, 'ID_PAC') ) {
								continue;
							}
							else if ( !property_exists($onepers, 'COMENTP') ) {
								continue;
							}

							$COMENTP = $onepers->COMENTP->__toString();
							$ID_PAC = $onepers->ID_PAC->__toString();

							$recall++;

							$commentParts = explode(';', $COMENTP);

							if ( empty($commentParts[1]) ) {
								continue;
							}

							// Проверяем ФИО, ДР
							$personData = [
								'FAM' => property_exists($onepers, 'FAM') ? $onepers->FAM->__toString() : null,
								'IM' => property_exists($onepers, 'IM') ? $onepers->IM->__toString() : null,
								'OT' => property_exists($onepers, 'OT') ? $onepers->OT->__toString() : null,
								'DR' => property_exists($onepers, 'DR') ? $onepers->DR->__toString() : null,
							];

							$currentPersonData = $this->dbmodel->getPersonDataByPersonEvnId($ID_PAC, $data['Registry_id'], $data['RegistrySubType_id'], $Registry_IsNew);

							if ( $currentPersonData === false ) {
								$this->dbmodel->deleteRegistryErrorTFOMS($data);
								throw new Exception('Данные пациента не найдены', 100037);
							}

							$hasErrors = false;
							$paramsList = array_keys($personData);

							foreach ( $paramsList as $paramName ) {
								if ( $personData[$paramName] == $currentPersonData[$paramName] ) {
									continue;
								}

								$hasErrors = true;

								switch ( $paramName ) {
									case 'FAM':
										$response = $this->dbmodel->updatePersonSurname([
											'pmUser_id' => $data['pmUser_id'],
											'Server_id' => 0,
											'Person_id' => $currentPersonData['Person_id'],
											'Evn_setDT' => $currentPersonData['Evn_setDT'],
											'FAM' => $personData[$paramName],
										]);

										if ( !empty($response) ) {
											throw new Exception($response, __LINE__);
										}
										break;

									case 'IM':
										$response = $this->dbmodel->updatePersonFirname([
											'pmUser_id' => $data['pmUser_id'],
											'Server_id' => 0,
											'Person_id' => $currentPersonData['Person_id'],
											'Evn_setDT' => $currentPersonData['Evn_setDT'],
											'IM' => $personData[$paramName],
										]);

										if ( !empty($response) ) {
											throw new Exception($response, __LINE__);
										}
										break;

									case 'OT':
										$response = $this->dbmodel->updatePersonSecname([
											'pmUser_id' => $data['pmUser_id'],
											'Server_id' => 0,
											'Person_id' => $currentPersonData['Person_id'],
											'Evn_setDT' => $currentPersonData['Evn_setDT'],
											'OT' => $personData[$paramName],
										]);

										if ( !empty($response) ) {
											throw new Exception($response, __LINE__);
										}
										break;

									case 'DR':
										$response = $this->dbmodel->updatePersonBirthday([
											'pmUser_id' => $data['pmUser_id'],
											'Server_id' => 0,
											'Person_id' => $currentPersonData['Person_id'],
											'Evn_setDT' => $currentPersonData['Evn_setDT'],
											'DR' => $personData[$paramName],
										]);

										if ( !empty($response) ) {
											throw new Exception($response, __LINE__);
										}
										break;
								}
							}

							if ( $hasErrors === true ) {
								$recErr++;
							}
						}
						break;
				}

				$this->textlog->add('Обработка файла ' . $xmlfile . ' завершена');
			}

			// Пишем информацию об импорте в историю
			$this->dbmodel->dumpRegistryInformation($data, 3);

			$this->ReturnData([ 'success' => true, 'Registry_id' => $data['Registry_id'], 'recErr' => $recErr, 'recAll' => $recall, 'Message' => 'Реестр успешно загружен.' ]);
		}
		catch ( Exception $e ) {
			$this->textlog->add('Ошибка (' . $e->getCode() . '): ' . $e->getMessage());
			$this->ReturnError($e->getMessage(), $e->getCode());
		}

		$this->textlog->add('Финиш');

		return true;
	}

	/**
	 *  Функция возвращает запись/записи реестра
	 *  Входящие данные: _POST['Registry_id'],
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования реестра (счета)
	 */
	public function loadUnionRegistry() {
		$data = $this->ProcessInputData('loadUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistry($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Удаление реестра
	 */
	public function deleteRegistry() {
		$this->load->model('Utils_model', 'umodel');
		
		$data = $this->ProcessInputData('deleteRegistry', true);
		if ($data === false) { return false; }

		if ($this->dbmodel->checkRegistryInGroupLink(array('Registry_id' => $data['id']))) {
			$this->ReturnError('Реестр включён в реестр по СМО, удаление невозможно.');
			return false;
		}

		$result = $this->checkDeleteRegistry($data);
		if (!$result) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Извините, удаление реестра невозможно!'));
		}

		$sch = "r35";
		$object = "Registry";
		$id = $data['id'];
		
		$response = $this->umodel->ObjectRecordDelete($data, $object, false, $id, $sch);
		if (isset($response[0]['Error_Message'])) { $response[0]['Error_Msg'] = $response[0]['Error_Message']; } else { $response[0]['Error_Msg'] = ''; }
		
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Функция возвращает данные для дерева реестров в json-формате
	 */
	public function loadRegistryTree() {
		if ( !empty($_POST['Registry_IsNew']) && $_POST['Registry_IsNew'] == 2 ) {
			$this->_loadRegistryTreeNew();
			return true;
		}

		/**
		 * Получение ветки дерева реестров
		 */
		function getRegistryTreeChild($childrens, $field, $lvl, $node_id = "") {
			$val = array();
			$i = 0;

			if (!empty($node_id)) {
				$node_id = "/".$node_id;
			}

			if ( $childrens != false && count($childrens) > 0 ) {
				foreach ($childrens as $rows) {
					$node = array(
						'text'=>toUTF(trim($rows[$field['name']])),
						'id'=>$field['object'].".".$lvl.".".$rows[$field['id']].$node_id,
						//'new'=>$rows['New'],
						'object'=>$field['object'],
						'object_id'=>$field['id'],
						'object_value'=>$rows[$field['id']],
						'leaf'=>$field['leaf'],
						'iconCls'=>$field['iconCls'],
						'cls'=>$field['cls']
					);
					//$val[] = array_merge($node,$lrt,$lst);
					$val[] = $node;
				}

			}

			return $val;
		}

		// TODO: Тут надо поменять на ProcessInputData
		$data = $_POST;
		$data = array_merge($data, getSessionParams());
		$c_one = array();
		$c_two = array();

		// Текущий уровень
		if ( !isset($data['level']) || !is_numeric($data['level']) ) {
			$val = array();
			$this->ReturnData($val);
			return false;
		}

		$node = "";

		if ( isset($data['node']) ) {
			$node = $data['node'];
		}

		$response = array();

		switch ( $data['level'] ) {
			case 0: // Уровень Root. ЛПУ
				$this->load->model("LpuStructure_model", "lsmodel");
				$childrens = $this->lsmodel->GetLpuNodeList($data);

				$field = array('object' => "Lpu",'id' => "Lpu_id", 'name' => "Lpu_Name", 'iconCls' => 'lpu-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;

			case 1: // Уровень 1. Типочки
				$childrens = $this->dbmodel->loadRegistryTypeNode($data);
				$field = array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;

			case 2: // Уровень 2. Предварительные/финальные
				$childrens = array(
					array('RegistrySubType_id' => 1, 'RegistrySubType_Name' => 'Предварительные реестры'),
					array('RegistrySubType_id' => 2, 'RegistrySubType_Name' => 'Реестры по СМО'),
				);
				$field = Array('object' => "RegistrySubType",'id' => "RegistrySubType_id", 'name' => "RegistrySubType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;

			case 3: // Уровень 3. Статусы реестров
				$data['RegistrySubType_id'] = null;
				if ( preg_match('/RegistrySubType\.[0-9]\.([0-9]*)\//ui', $node, $matches) ) {
					$data['RegistrySubType_id'] = intval($matches[1]);
				}
				$childrens = $this->dbmodel->loadRegistryStatusNode($data);
				$field = Array('object' => "RegistryStatus",'id' => "RegistryStatus_id", 'name' => "RegistryStatus_Name", 'iconCls' => 'regstatus-16', 'leaf' => true, 'cls' => "file");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
		}

		if ( count($c_two) > 0 ) {
			$c_one = array_merge($c_one, $c_two);
		}

		$this->ReturnData($c_one);

		return true;
	}

	/**
	 * Функция возвращает данные для дерева реестров в json-формате
	 */
	private function _loadRegistryTreeNew() {
		/**
		 *	Получение ветки дерева реестров
		 */
		function getRegistryTreeChild($childrens, $field, $lvl, $node_id = "") {
			$val = [];

			if ( !empty($node_id) ) {
				$node_id = "/".$node_id;
			}

			if ( $childrens != false && count($childrens) > 0 ) {
				foreach ( $childrens as $rows ) {
					$node = [
						'text' => trim($rows[$field['name']]),
						'id' => $field['object'] . "." . $lvl . "." . $rows[$field['id']] . $node_id,
						'object' => $field['object'],
						'object_id' => $field['id'],
						'object_value' => $rows[$field['id']],
						'leaf' => $field['leaf'],
						'iconCls' => $field['iconCls'],
						'cls' => $field['cls'],
					];

					$val[] = $node;
				}
			}

			return $val;
		}

		// TODO: Тут надо поменять на ProcessInputData
		$data = array_merge($_POST, getSessionParams());
		$c_one = [];
		$c_two = [];

		// Текущий уровень
		if ( !isset($data['level']) || !is_numeric($data['level']) ) {
			$val = [];
			$this->ReturnData($val);
			return false;
		}

		$node = "";

		if ( isset($data['node']) ) {
			$node = $data['node'];
		}

		switch ( $data['level'] ) {
			case 0: // Уровень Root. МО
				$this->load->model("LpuStructure_model", "lsmodel");
				$childrens = $this->lsmodel->GetLpuNodeList($data);
				$field = [
					'object' => "Lpu",
					'id' => "Lpu_id",
					'name' => "Lpu_Name",
					'iconCls' => 'lpu-16',
					'leaf' => false,
					'cls' => "folder"
				];
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;

			case 1: // Уровень 1. Объединённые реестры
				$childrens = [
					[
						'RegistryType_id' => 13,
						'RegistryType_Name' => 'Объединённые реестры'
					]
				];
				$field = [
					'object' => "RegistryType",
					'id' => "RegistryType_id",
					'name' => "RegistryType_Name",
					'iconCls' => 'regtype-16',
					'leaf' => false,
					'cls' => "folder"
				];
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;

			case 2: // Уровень 2. Типочки
				$childrens = $this->dbmodel->loadRegistryTypeNode($data);
				$field = [
					'object' => "RegistryType",
					'id' => "RegistryType_id",
					'name' => "RegistryType_Name",
					'iconCls' => 'regtype-16',
					'leaf' => false,
					'cls' => "folder"
				];
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			case 3: // Уровень 3. Статусы реестров
				$childrens = $this->dbmodel->loadRegistryStatusNode($data);
				$field = [
					'object' => "RegistryStatus",
					'id' => "RegistryStatus_id",
					'name' => "RegistryStatus_Name",
					'iconCls' => 'regstatus-16',
					'leaf' => true,
					'cls' => "file"
				];
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
		}

		if ( count($c_two) > 0 ) {
			$c_one = array_merge($c_one, $c_two);
		}

		$this->ReturnData($c_one);
		return true;
	}

	/**
	 * Загрузка списка обычных реестров, входящих в объединённый
	 */
	public function loadUnionRegistryChildGrid() {
		$data = $this->ProcessInputData('loadUnionRegistryChildGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryChildGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Функция возвращает ошибки данных реестра по версии ТФОМС :)
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
	 */
	public function loadUnionRegistryErrorTFOMS() {
		$data = $this->ProcessInputData('loadUnionRegistryErrorTFOMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryErrorTFOMS($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение объединённого реестра
	 */
	public function saveUnionRegistry() {
		$data = $this->ProcessInputData('saveUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();

		return true;
	}

	/**
	 * Загрузка списка объединённых реестров
	 */
	public function loadUnionRegistryGrid() {
		$data = $this->ProcessInputData('loadUnionRegistryGrid', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadUnionRegistryGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Переформирование финального реестра
	 */
	public function reformUnionRegistry() {
		$data = $this->ProcessInputData('reformUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->reformUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при переформировании реестра')->ReturnData();

		return true;
	}

	/**
	 * Изменение статуса счета-реестра
	 * Входящие данные: ID рееестра и значение статуса
	 * На выходе: JSON-строка
	 * Используется: форма просмотра реестра (счета)
	 */
	public function setUnionRegistryStatus() {
		$data = $this->ProcessInputData('setUnionRegistryStatus', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setUnionRegistryStatus($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();

		return true;
	}

	/**
	 * Загрузка списка ошибок перс. данных
	 */
	public function loadRegistryErrorBDZ() {
		$data = $this->ProcessInputData('loadRegistryErrorBDZ', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryErrorBDZ($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка формы редактирования объединённого реестра
	 */
	public function loadUnionRegistryEditForm()
	{
		$data = $this->ProcessInputData('loadUnionRegistryEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение id Lpu участника взаиморасчтов для печати листа согласования
	 */
	public function getLpuSidList(){
		$data = $this->ProcessInputData('getLpuSidList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuSidList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
