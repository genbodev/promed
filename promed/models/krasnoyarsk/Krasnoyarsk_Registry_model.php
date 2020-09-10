<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Registry_model - модель для работы с таблицей Registry
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @author       Bykov Stas aka Savage (savage@swan-it.ru)
 * @version      07.12.2019
 */
require_once(APPPATH.'models/Registry_model.php');

class Krasnoyarsk_Registry_model extends Registry_model {
	public $scheme = "r24";
	public $region = "krasnoyarsk";

	private $_IDCASE = 0;
	private $_IDSERV = 0;

	protected $LpuAttachList = [];
	protected $OrgSMOList = [];
	protected $PolisTypeList = [];
	protected $RegistryErrorTfomsType = [];
	protected $registryEvnNum = [];

	protected $_importState = [
		'currentEvnId' => null,
		'sluchEvnList' => [],
		'BDZErrCnt' => -1,
		'TFOMSErrCnt' => -1,
		'TFOMSRejArr' => [],
		'TFOMSWarnCnt' => -1,
		'Registry_Sum' => 0,
		'Registry_SumPaid' => 0,
	];
	protected $SLUCHErrorList = [];

	protected $persCnt = 0;
	protected $zapCnt = 0;

	protected $exportPersonDataFile = '';
	protected $exportPersonDataBodyTemplate;
	protected $exportPersonDataFooterTemplate;
	protected $exportPersonDataHeaderTemplate;
	protected $exportSluchDataFile = '';
	protected $exportSluchDataFileTmp = '';
	protected $exportSluchDataBodyTemplate;
	protected $exportSluchDataFooterTemplate;
	protected $exportSluchDataHeaderTemplate;
	protected $exportSluchDataSchetTemplate;

	private $_registryTypeList = [
		1 => [
			'RegistryType_id' => 1,
			'RegistryType_Name' => 'Стационар',
			'SP_Object' => 'EvnPS',
			'exportParams' => [
				'data' => [
					'version' => '2.0',
					'code' => 'R',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/mp/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_pl_body',
					'footerTemplate' => 'registry_krasnoyarsk_pl_footer',
					'headerTemplate' => 'registry_krasnoyarsk_pl_header',
					'schetTemplate' => 'registry_krasnoyarsk_pl_schet',
				],
				'pers' => [
					'version' => '1.0',
					'code' => 'L',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/mp/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_pers_body',
					'footerTemplate' => 'registry_krasnoyarsk_pers_footer',
					'headerTemplate' => 'registry_krasnoyarsk_pers_header',
				],
				'method' => '_loadRegistryDataForXml'
			]
		],
		2 => [
			'RegistryType_id' => 2,
			'RegistryType_Name' => 'Поликлиника',
			'SP_Object' => 'EvnPL',
			'exportParams' => [
				'data' => [
					'version' => '2.0',
					'code' => 'V',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/mp/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_pl_body',
					'footerTemplate' => 'registry_krasnoyarsk_pl_footer',
					'headerTemplate' => 'registry_krasnoyarsk_pl_header',
					'schetTemplate' => 'registry_krasnoyarsk_pl_schet',
				],
				'pers' => [
					'version' => '1.0',
					'code' => 'L',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/mp/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_pers_body',
					'footerTemplate' => 'registry_krasnoyarsk_pers_footer',
					'headerTemplate' => 'registry_krasnoyarsk_pers_header',
				],
				'method' => '_loadRegistryDataForXml'
			]
		],
		16 => [
			'RegistryType_id' => 16,
			'RegistryType_Name' => 'Стоматология',
			'SP_Object' => 'EvnPLStom',
			'exportParams' => [
				'data' => [
					'version' => '2.0',
					'code' => 'V',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/mp/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_pl_body',
					'footerTemplate' => 'registry_krasnoyarsk_pl_footer',
					'headerTemplate' => 'registry_krasnoyarsk_pl_header',
					'schetTemplate' => 'registry_krasnoyarsk_pl_schet',
				],
				'pers' => [
					'version' => '1.0',
					'code' => 'L',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/mp/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_pers_body',
					'footerTemplate' => 'registry_krasnoyarsk_pers_footer',
					'headerTemplate' => 'registry_krasnoyarsk_pers_header',
				],
				'method' => '_loadRegistryDataForXml'
			]
		],
		7 => [
			'RegistryType_id' => 7,
			'RegistryType_Name' => 'Дисп-ция взр. населения',
			'SP_Object' => 'EvnPLDD13',
			'exportParams' => [
				'data' => [
					'version' => '1.0',
					'code' => 'DV',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/dd/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_disp_body',
					'footerTemplate' => 'registry_krasnoyarsk_disp_footer',
					'headerTemplate' => 'registry_krasnoyarsk_disp_header',
					'schetTemplate' => 'registry_krasnoyarsk_disp_schet',
				],
				'pers' => [
					'version' => '1.0',
					'code' => 'L',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/dd/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_pers_body',
					'footerTemplate' => 'registry_krasnoyarsk_pers_footer',
					'headerTemplate' => 'registry_krasnoyarsk_pers_header',
				],
				'method' => '_loadRegistryDataForXmlDisp'
			]
		],
		9 => [
			'RegistryType_id' => 9,
			'RegistryType_Name' => 'Дисп-ция детей-сирот',
			'SP_Object' => 'EvnPLOrp13',
			'exportParams' => [
				'data' => [
					'version' => '1.0',
					'code' => 'DS',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/dd/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_disp_body',
					'footerTemplate' => 'registry_krasnoyarsk_disp_footer',
					'headerTemplate' => 'registry_krasnoyarsk_disp_header',
					'schetTemplate' => 'registry_krasnoyarsk_disp_schet',
				],
				'pers' => [
					'version' => '1.0',
					'code' => 'L',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/dd/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_pers_body',
					'footerTemplate' => 'registry_krasnoyarsk_pers_footer',
					'headerTemplate' => 'registry_krasnoyarsk_pers_header',
				],
				'method' => '_loadRegistryDataForXmlDisp'
			]
		],
		11 => [
			'RegistryType_id' => 11,
			'RegistryType_Name' => 'Проф. осмотры взр. населения',
			'SP_Object' => 'EvnPLProf',
			'exportParams' => [
				'data' => [
					'version' => '1.0',
					'code' => 'PV',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/dd/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_disp_body',
					'footerTemplate' => 'registry_krasnoyarsk_disp_footer',
					'headerTemplate' => 'registry_krasnoyarsk_disp_header',
					'schetTemplate' => 'registry_krasnoyarsk_disp_schet',
				],
				'pers' => [
					'version' => '1.0',
					'code' => 'L',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/dd/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_pers_body',
					'footerTemplate' => 'registry_krasnoyarsk_pers_footer',
					'headerTemplate' => 'registry_krasnoyarsk_pers_header',
				],
				'method' => '_loadRegistryDataForXmlDisp'
			]
		],
		12 => [
			'RegistryType_id' => 12,
			'RegistryType_Name' => 'Медосмотры несовершеннолетних',
			'SP_Object' => 'EvnPLProfTeen',
			'exportParams' => [
				'data' => [
					'version' => '1.0',
					'code' => 'PD',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/dd/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_disp_body',
					'footerTemplate' => 'registry_krasnoyarsk_disp_footer',
					'headerTemplate' => 'registry_krasnoyarsk_disp_header',
					'schetTemplate' => 'registry_krasnoyarsk_disp_schet',
				],
				'pers' => [
					'version' => '1.0',
					'code' => 'L',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/dd/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_pers_body',
					'footerTemplate' => 'registry_krasnoyarsk_pers_footer',
					'headerTemplate' => 'registry_krasnoyarsk_pers_header',
				],
				'method' => '_loadRegistryDataForXmlDisp'
			]
		],
		//14 => [ 'RegistryType_id' => 14, 'RegistryType_Name' => 'Высокотехнологичная медицинская помощь', 'SP_Object' => 'EvnHTM' ],
		15 => [
			'RegistryType_id' => 15,
			'RegistryType_Name' => 'Параклинические услуги',
			'SP_Object' => 'EvnUslugaPar',
			'exportParams' => [
				'data' => [
					'version' => '2.0',
					'code' => 'R',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/mp/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_pl_body',
					'footerTemplate' => 'registry_krasnoyarsk_pl_footer',
					'headerTemplate' => 'registry_krasnoyarsk_pl_header',
					'schetTemplate' => 'registry_krasnoyarsk_pl_schet',
				],
				'pers' => [
					'version' => '1.0',
					'code' => 'L',
					'xmlns' => 'http://krasmed.ru/xsd/ext/reestr/mp/mo/2020-01-01',
					'bodyTemplate' => 'registry_krasnoyarsk_pers_body',
					'footerTemplate' => 'registry_krasnoyarsk_pers_footer',
					'headerTemplate' => 'registry_krasnoyarsk_pers_header',
				],
				'method' => '_loadRegistryDataForXml'
			]
		],
	];

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Функция возвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	 */
	public function loadRegistryTypeNode($data) {
		return $this->_registryTypeList;
	}

	/**
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	public function setRegistryParamsByType($data = [], $force = false) {
		parent::setRegistryParamsByType($data, $force);

		switch ( $this->RegistryType_id ) {
			case 1:
			case 14:
				$this->RegistryDataObject = 'RegistryDataEvnPS';
				$this->RegistryDataTempObject = 'RegistryDataTempEvnPS';
				$this->RegistryErrorObject = 'RegistryErrorEvnPS';
				$this->RegistryNoPolisObject = 'RegistryEvnPSNoPolis';
				$this->RegistryUslugaObject = 'RegistryUslugaEvnPS';
				$this->RegistryEvnClass = 'EvnSection';
				break;

			case 2:
			case 16:
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryDataTempObject = 'RegistryData';
				$this->RegistryUslugaObject = 'RegistryUsluga';
				$this->RegistryEvnClass = 'EvnVizit';
				break;

			case 6:
				$this->RegistryDataObject = 'RegistryDataCmp';
				$this->RegistryDataTempObject = 'RegistryDataTempCmp';
				$this->RegistryDataEvnField = 'CmpCloseCard_id';
				$this->RegistryNoPolisObject = 'RegistryCmpNoPolis';
				$this->RegistryUslugaObject = 'RegistryUsluga';
				$this->RegistryEvnClass = 'CmpCloseCard';
				break;

			case 7:
			case 9:
				$this->RegistryDataObject = 'RegistryDataDisp';
				$this->RegistryDataTempObject = 'RegistryDataTempDisp';
				$this->RegistryErrorObject = 'RegistryErrorDisp';
				$this->RegistryNoPolisObject = 'RegistryDispNoPolis';
				$this->RegistryUslugaObject = 'RegistryUsluga';
				$this->RegistryEvnClass = 'EvnPLDisp';
				break;

			case 11:
			case 12:
				$this->RegistryDataObject = 'RegistryDataProf';
				$this->RegistryDataTempObject = 'RegistryDataTempProf';
				$this->RegistryErrorObject = 'RegistryErrorProf';
				$this->RegistryNoPolisObject = 'RegistryProfNoPolis';
				$this->RegistryUslugaObject = 'RegistryUsluga';
				$this->RegistryEvnClass = 'EvnPLDisp';
				break;

			case 15:
				$this->RegistryDataObject = 'RegistryDataPar';
				$this->RegistryDataTempObject = 'RegistryDataTempPar';
				$this->RegistryErrorObject = 'RegistryErrorPar';
				$this->RegistryNoPolisObject = 'RegistryParNoPolis';
				$this->RegistryUslugaObject = 'RegistryUsluga';
				$this->RegistryEvnClass = 'EvnUslugaPar';
				break;

			default:
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryDataTempObject = 'RegistryDataTmp';
				$this->RegistryNoPolisObject = 'RegistryNoPolis';
				$this->RegistryUslugaObject = 'RegistryUsluga';
				$this->RegistryEvnClass = 'EvnVizit';
				break;
		}
	}

	/**
	 * Установка статуса импорта реестра в XML
	 */
	protected function _setXmlExportStatus($data = []) {
		$response = [
			'success' => true,
			'Error_Msg' => '',
		];

		try {
			if ( empty($data['Registry_id']) ) {
				throw new Exception('Пустые значения входных параметров', __LINE__);
			}

			$result = $this->getFirstRowFromQuery("
				declare @Err_Msg varchar(400);
	
				set nocount on;
	
				begin try
					update
						{$this->scheme}.Registry with (rowlock)
					set
						Registry_xmlExportPath = :Status,
						Registry_xmlExpDT = dbo.tzGetDate()
					where
						Registry_id = :Registry_id
				end try
				
				begin catch
					set @Err_Msg = error_message();
				end catch
	
				set nocount off;
	
				select @Err_Msg as Error_Msg;
			", [
				'Registry_id' => $data['Registry_id'],
				'Status' => $data['Status'],
			]);

			if ( $result === false || !is_array($result) || count($result) == 0 ) {
				throw new Exception('Ошибка при выполнении запроса к базе данных', __LINE__);
			}
			else if ( !empty($result['Error_Msg']) ) {
				throw new Exception($result['Error_Msg'], __LINE__);
			}
		}
		catch ( Exception $e ) {
			$response['success'] = false;
			$response['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Получаем состояние реестра в данный момент и тип реестра
	 */
	protected function _getRegistryXmlExport($data = []) {
		$response = [
			'success' => true,
			'Error_Msg' => '',
		];

		try {
			if ( empty($data['Registry_id']) ) {
				throw new Exception('Пустые значения входных параметров', __LINE__);
			}

			$this->setRegistryParamsByType($data);

			$result = $this->getFirstRowFromQuery("
				select
					R.Registry_xmlExportPath,
					R.RegistryType_id,
					R.RegistryStatus_id,
					KN.KatNasel_SysNick,
					R.Registry_PackNum,
					ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
					RDSum.RegistryData_Count as RegistryData_Count,
					CONVERT(varchar(10), Registry_begDate, 120) as Registry_begDate,
					CONVERT(varchar(10), Registry_endDate, 120) as Registry_endDate,
					SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) as Registry_endMonth
				from {$this->scheme}.v_Registry as R with (nolock)
					left join v_KatNasel as KN on KN.KatNasel_id = R.KatNasel_id
					outer apply(
						select
							COUNT(RD.Evn_id) as RegistryData_Count,
							SUM(ISNULL(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
						from {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
						where RD.Registry_id = R.Registry_id
					) RDSum
				where
					R.Registry_id = :Registry_id
			", [
				'Registry_id' => $data['Registry_id']
			]);

			if ( $result === false || !is_array($result) || count($result) == 0 ) {
				throw new Exception('Ошибка при выполнении запроса к базе данных (получение данных реестра)', __LINE__);
			}

			$response = $result;
		}
		catch ( Exception $e ) {
			$response['success'] = false;
			$response['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Возвращает наименование объекта для хранимых процедур в зависимости от типа реестра
	 */
	protected function _getRegistryObjectName($type) {
		$result = '';

		if ( array_key_exists($type, $this->_registryTypeList) ) {
			$result = $this->_registryTypeList[$type]['SP_Object'];
		}

		return $result;
	}

	/**
	 * Функция возрвращает массив годов, в которых есть реестры
	 */
	public function getYearsList($data) {
		if ( 6 == (int)$data['RegistryStatus_id'] ) {
			// 6 - если запрошены удаленные реестры
			$query = "
				select distinct
					YEAR(Registry_begDate) as reg_year
				from
					{$this->scheme}.v_Registry_deleted with(nolock)
				where
					Lpu_id = :Lpu_id
					and RegistryType_id = :RegistryType_id
			";
		}
		else {
			$query = "
				select distinct
					YEAR(Registry_begDate) as reg_year
				from
					{$this->scheme}.v_Registry with(nolock)
				where
					Lpu_id = :Lpu_id
					and RegistryStatus_id = :RegistryStatus_id
					and RegistryType_id = :RegistryType_id
			";
		}

		$result = $this->queryResult($query, $data);

		if ( $result === false ) {
			return false;
		}

		if ( !is_array($result) || count($result) == 0 ) {
			$result = [[ 'reg_year' => date('Y') ]];
		}

		return $result;
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 */
	public function exportRegistryToXml($data) {
		try {
			$this->load->library('parser');

			set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
			ini_set("max_execution_time", "0");
			ini_set("max_input_time", "0");
			ini_set("default_socket_timeout", "999");

			$this->load->library('textlog', [
				'file' => 'exportRegistryToXml_' . date('Y-m-d') . '.log',
				'method' => __METHOD__,
			]);
			$this->textlog->add('Запуск формирования реестра (Registry_id=' . $data['Registry_id'] . ')', 'INFO');

			// Проверяем наличие и состояние реестра
			$registryData = $this->_getRegistryXmlExport($data);

			if ( !empty($registryData['Error_Msg']) ) {
				throw new Exception($registryData['Error_Msg'], __LINE__);
			}

			if ( $registryData['Registry_xmlExportPath'] == '1' ) {
				throw new Exception('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			}

			// если уже выгружен реестр
			if ( !empty($registryData['Registry_xmlExportPath']) && $data['OverrideExportOneMoreOrUseExist'] == 1 ) {
				$link = $registryData['Registry_xmlExportPath'];
				$this->textlog->add('Вернули ссылку ' . $link, 'INFO');
				return [ 'success' => true, 'Link' => $link ];
			}

			$data['KatNasel_SysNick'] = $registryData['KatNasel_SysNick'];
			$data['Registry_endMonth'] = $registryData['Registry_endMonth'];
			$data['Registry_PackNum'] = $registryData['Registry_PackNum'];
			$data['RegistryType_id'] = $registryData['RegistryType_id'];

			if ( empty($data['Registry_PackNum']) ) {
				$data['Registry_PackNum'] = $this->_setXmlPackNum($data);
			}

			$reg_endmonth = $registryData['Registry_endMonth'];
			$type = $registryData['RegistryType_id'];
			$this->textlog->add('Тип реестра ' . $type, 'INFO');

			if ( !in_array($type, $this->getAllowedRegistryTypes()) ) {
				throw new Exception('Данный тип реестров не обрабатывается.');
			}

			$data['Status'] = '1';
			$this->_setXmlExportStatus($data);

			$SCHET = $this->_loadRegistrySCHETForXmlUsing($type, $data);

			if ( $SCHET === false ) {
				throw new Exception('Ошибка при получении данных заголовка реестра.');
			}

			if ( !array_key_exists('exportParams', $this->_registryTypeList[$type]) ) {
				throw new Exception('Экспорт данного типа реестров недоступен');
			}

			$pcode = $this->_registryTypeList[$type]['exportParams']['pers']['code'];
			$scode = $this->_registryTypeList[$type]['exportParams']['data']['code'];

			$this->exportPersonDataBodyTemplate = $this->_registryTypeList[$type]['exportParams']['pers']['bodyTemplate'];
			$this->exportPersonDataFooterTemplate = $this->_registryTypeList[$type]['exportParams']['pers']['footerTemplate'];
			$this->exportPersonDataHeaderTemplate = $this->_registryTypeList[$type]['exportParams']['pers']['headerTemplate'];
			$this->exportSluchDataBodyTemplate = $this->_registryTypeList[$type]['exportParams']['data']['bodyTemplate'];
			$this->exportSluchDataFooterTemplate = $this->_registryTypeList[$type]['exportParams']['data']['footerTemplate'];
			$this->exportSluchDataHeaderTemplate = $this->_registryTypeList[$type]['exportParams']['data']['headerTemplate'];
			$this->exportSluchDataSchetTemplate = $this->_registryTypeList[$type]['exportParams']['data']['schetTemplate'];

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];

			//Проверка на наличие созданной ранее директории
			if ( !file_exists(EXPORTPATH_REGISTRY . $out_dir) ) {
				mkdir( EXPORTPATH_REGISTRY . $out_dir );
			}
			$this->textlog->add('Создали каталог ' . EXPORTPATH_REGISTRY . $out_dir, 'INFO');

			// случаи
			$file_re_data_sign = $scode . "M" . $SCHET['CODE_MO'] . 'T24' . "_" . $reg_endmonth . $data['Registry_PackNum'];
			$this->exportSluchDataFile = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";
			// временный файл для случаев
			$this->exportSluchDataFileTmp = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . "_tmp.xml";

			// перс. данные
			$file_re_pers_data_sign = $pcode . "M" . $SCHET['CODE_MO'] . 'T24' . "_" . $reg_endmonth . $data['Registry_PackNum'];
			$this->exportPersonDataFile = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";
			// архив
			$file_zip_sign = $file_re_data_sign;
			// связь номеров записей в файле экспорта и случая
			$file_evn_num_name = EXPORTPATH_REGISTRY . $out_dir . "/evnnum.txt";

			$this->textlog->add('Определили наименования файлов: ' . $this->exportSluchDataFile . ' и ' . $this->exportPersonDataFile, 'INFO');
			$this->textlog->add('Создаем XML файлы на диске', 'INFO');

			$registryExportDT = date('Y-m-d') . 'T' . date('H:i:s');

			// Заголовок для файла с перс. данными
			$PERS_LIST_HEADER = [
				'VERSION' => $this->_registryTypeList[$type]['exportParams']['pers']['version'],
				'FILENAME' => $file_re_pers_data_sign,
				'FILENAME1' => $file_re_data_sign,
				'DATA' => $registryExportDT,
				'xmlns' => $this->_registryTypeList[$type]['exportParams']['pers']['xmlns'],
			];

			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/' . $this->exportPersonDataHeaderTemplate, $PERS_LIST_HEADER, true);
			file_put_contents($this->exportPersonDataFile, $xml_pers);

			// Получаем данные
			$xmlMethod = $this->_registryTypeList[$type]['exportParams']['method'];
			$response = $this->$xmlMethod($type, $data);
			$this->textlog->add('_loadRegistryDataForXml: Выгрузили данные', 'INFO');

			if ( $response === false ) {
				throw new Exception('Ошибка при поолучении данных для выгрузки');
			}

			$this->textlog->add('Получили все данные из БД', 'INFO');
			$this->textlog->add('Количество записей реестра: ' . $this->zapCnt, 'INFO');

			$ZL_LIST_HEADER = [
				'VERSION' => $this->_registryTypeList[$type]['exportParams']['data']['version'],
				'FILENAME' => $file_re_data_sign,
				'ASSOCIATED_FILENAME' => $file_re_pers_data_sign,
				'DATA' => $registryExportDT,
				'PONAME' => 'Promed',
				'POVER' => '1.0',
				'xmlns' => $this->_registryTypeList[$type]['exportParams']['data']['xmlns'],
				'SD_Z' => $this->zapCnt,
			];

			// Заголовок файла с данными
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/' . $this->exportSluchDataHeaderTemplate, $ZL_LIST_HEADER, true);
			file_put_contents($this->exportSluchDataFile, $xml);

			$SCHET['MO_FIN_DATA'] = [];

			if ( !empty($SCHET['SUMMAPF']) || !empty($SCHET['SUMMAF']) ) {
				$SCHET['MO_FIN_DATA'][] = [
					'SUMMAPF' => $SCHET['SUMMAPF'] ?? null,
					'SUMMAFAP' => $SCHET['SUMMAFAP'] ?? null,
					'SUMMAF' => $SCHET['SUMMAF'] ?? null,
				];

				unset($SCHET['SUMMAPF']);
				unset($SCHET['SUMMAFAP']);
				unset($SCHET['SUMMAF']);
			}

			// Информация о счете
			$xml = $this->parser->parse_ext('export_xml/' . $this->exportSluchDataSchetTemplate, $SCHET, true, false);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($this->exportSluchDataFile, $xml, FILE_APPEND);
			unset($xml);

			// Тело файла с данными начитываем из временного (побайтно)
			if ( file_exists($this->exportSluchDataFileTmp) ) {
				// Устанавливаем начитываемый объем данных
				$chunk = 10 * 1024 * 1024; // 10 MB

				$fh = @fopen($this->exportSluchDataFileTmp, "rb");

				if ( $fh === false ) {
					throw new Exception('Ошибка при открытии файла');
				}

				while ( !feof($fh) ) {
					file_put_contents($this->exportSluchDataFile, fread($fh, $chunk), FILE_APPEND);
				}

				fclose($fh);

				unlink($this->exportSluchDataFileTmp);
			}

			$this->textlog->add('Перегнали данные из временного файла со случаями в основной файл', 'INFO');

			// записываем footer
			$xml = $this->parser->parse_ext('export_xml/' . $this->exportSluchDataFooterTemplate, [], true, false);
			file_put_contents($this->exportSluchDataFile, $xml, FILE_APPEND);
			$xml_pers = $this->parser->parse_ext('export_xml/' . $this->exportPersonDataFooterTemplate, [], true);
			file_put_contents($this->exportPersonDataFile, $xml_pers, FILE_APPEND);
			unset($xml);
			unset($xml_pers);

			$this->textlog->add('Создан ' . $this->exportSluchDataFile, 'INFO');
			$this->textlog->add('Создан ' . $this->exportPersonDataFile, 'INFO');

			$H_registryValidate = true;
			$L_registryValidate = true;

			if ( array_key_exists('check_implementFLK', $data['session']['setting']['server']) && $data['session']['setting']['server']['check_implementFLK'] ) {
				$settingsFLK = $this->loadRegistryEntiesSettings($registryData);

				if ( count($settingsFLK) > 0 ) {
					$upload_path = 'RgistryFields/';
					$settingsFLK = $settingsFLK[0];
					$tplEvnDataXSD = ($settingsFLK['FLKSettings_EvnData']) ? $settingsFLK['FLKSettings_EvnData'] : false;
					$tplPersonDataXSD = ($settingsFLK['FLKSettings_PersonData']) ? $settingsFLK['FLKSettings_PersonData'] : false;

					if ( $tplEvnDataXSD ) {
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplEvnDataXSD);
						$dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$fileEvnDataXSD = IMPORTPATH_ROOT . $upload_path . $dirTpl . "/" . $tplEvnDataXSD;

						//Проверяем валидность 1го реестра
						//Путь до шаблона
						$H_xsd_tpl = $fileEvnDataXSD;
						//Файл с ошибками, если понадобится
						$H_validate_err_file = EXPORTPATH_REGISTRY . $out_dir . "/err_" . $file_re_data_sign . '.html';
						//Проверка
						$H_registryValidate = $this->Reconciliation($this->exportSluchDataFile, $H_xsd_tpl, 'file', $H_validate_err_file);
					}

					if ( $tplPersonDataXSD ) {
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplPersonDataXSD);
						$dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$tplPersonDataXSD = IMPORTPATH_ROOT . $upload_path . $dirTpl . "/" . $tplPersonDataXSD;

						//Проверяем 2й реестр
						//Путь до шаблона
						$L_xsd_tpl = $tplPersonDataXSD;
						//Файл с ошибками, если понадобится
						$L_validate_err_file = EXPORTPATH_REGISTRY . $out_dir . "/err_" . $file_re_pers_data_sign . '.html';
						//Проверка
						$L_registryValidate = $this->Reconciliation($this->exportPersonDataFile, $L_xsd_tpl, 'file', $L_validate_err_file);
					}
				}
			}

			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . ".zip";

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($this->exportSluchDataFile, $file_re_data_sign . ".xml");
			$zip->AddFile($this->exportPersonDataFile, $file_re_pers_data_sign . ".xml");
			$zip->close();
			$this->textlog->add('Упаковали в ZIP ' . $file_zip_name, 'INFO');

			$data['Status'] = $file_zip_name;
			$this->_setXmlExportStatus($data);

			if ( !$H_registryValidate  && !$L_registryValidate ) {
				$data['Status'] = NULL;
				$this->_setXmlExportStatus($data);

				return [
					'success' => false,
					'Error_Msg' => 'Реестр не прошёл проверку ФЛК: <a target="_blank" href="' . $H_validate_err_file . '">отчёт H</a>
						<a target="_blank" href="' . $this->exportSluchDataFile . '">H файл реестра</a>,
						<a target="_blank" href="' . $L_validate_err_file . '">отчёт L</a> 
						<a target="_blank" href="' . $this->exportPersonDataFile . '">L файл реестра</a>, 
						<a href="' . $file_zip_name . '" target="_blank">zip</a>',
					'Error_Code' => 20
				];
			}
			else if ( !$H_registryValidate ) {
				//Скинули статус
				$data['Status'] = NULL;
				$this->_setXmlExportStatus($data);

				unlink($this->exportPersonDataFile);
				$this->textlog->add('Почистили папку за собой', 'INFO');

				return [
					'success' => false,
					'Error_Msg' => 'Файл H реестра не прошёл проверку ФЛК: <a target="_blank" href="' . $H_validate_err_file . '">отчёт H</a>
						(<a target="_blank" href="' . $this->exportSluchDataFile . '">H файл реестра</a>),
						<a href="' . $file_zip_name . '" target="_blank">zip</a>',
					'Error_Code' => 21
				];
			}
			else if ( !$L_registryValidate ) {
				//Скинули статус
				$data['Status'] = NULL;
				$this->_setXmlExportStatus($data);

				unlink($this->exportSluchDataFile);
				$this->textlog->add('Почистили папку за собой', 'INFO');

				return [
					'success' => false,
					'Error_Msg' => 'Файл L реестра не прошёл ФЛК: <a target="_blank" href="' . $L_validate_err_file . '">отчёт L</a> 
						(<a target="_blank" href="' . $this->exportPersonDataFile . '">L файл реестра</a>), 
						<a href="' . $file_zip_name . '" target="_blank">zip</a>',
					'Error_Code' => 22
				];
			}

			unlink($this->exportSluchDataFile);
			unlink($this->exportPersonDataFile);
			$this->textlog->add('Почистили папку за собой', 'INFO');

			$this->_saveRegistryEvnNum($file_evn_num_name);

			// Пишем информацию о выгрузке в историю
			$this->dumpRegistryInformation($data, 2);

			$this->textlog->add('Вернули ссылку ' . $file_zip_name, 'INFO');
			return [ 'success' => true, 'Link' => $file_zip_name ];
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->_setXmlExportStatus($data);
			$this->textlog->add($e->getMessage(), 'ERROR');
			return [ 'success' => false, 'Error_Msg' => $e->getMessage() ];
		}
	}

	/**
	 * Получение данных о счете для выгрузки объединенного реестра в XML
	 */
	protected function _loadRegistrySCHETForXmlUsing($type, $data) {
		$object = $this->_getRegistryObjectName($type);
		$p_schet = $this->scheme . ".p_Registry_" . $object . "_expScet";

		$result = $this->getFirstRowFromQuery("exec {$p_schet} @Registry_id = :Registry_id", $data);

		if ( $result === false || !is_array($result) || count($result) == 0 ) {
			return false;
		}

		array_walk_recursive($result, 'ConvertFromUTF8ToWin1251', true);

		return $result;
	}

	/**
	 * Получение данных для выгрузки реестров в XML
	 * Типы реестров: стационар, поликлиника
	 */
	protected function _loadRegistryDataForXml($type, $data) {
		$object = $this->_getRegistryObjectName($type);

		$fn_pers = $this->scheme.".p_Registry_{$object}_expPac";
		$fn_sluch = $this->scheme.".p_Registry_{$object}_expSL";
		$fn_sluch_med = $this->scheme.".p_Registry_{$object}_expVizit";

		if ( in_array($type, [ 1, 16 ]) ) {
			$fn_usl = $this->scheme . ".p_Registry_{$object}_expUsl";
		}

		if ( in_array($type, [ 1, 2, 16 ]) ) {
			$fn_dir_out = $this->scheme.".p_Registry_{$object}_expDirOUT";
			$fn_ds2 = $this->scheme.".p_Registry_{$object}_expDS2";
		}

		if ( in_array($type, [ 1, 2 ]) ) {
			$fn_bdiag = $this->scheme.".p_Registry_{$object}_expBDIAG";
			$fn_bprot = $this->scheme.".p_Registry_{$object}_expBPROT";
			$fn_cons = $this->scheme.".p_Registry_{$object}_expCONS";
			$fn_napr_out = $this->scheme.".p_Registry_{$object}_expNAPR";
			$fn_onkousl = $this->scheme.".p_Registry_{$object}_expONKOUSL";
		}

		if ( in_array($type, [ 1/*, 14*/ ]) ) {
			$fn_ds3 = $this->scheme.".p_Registry_{$object}_expDS3";
			//$fn_napr = $this->scheme.".p_Registry_{$object}_expNAPR";
		}

		if ( in_array($type, [ 1 ]) ) {
			//$p_crit = $this->scheme.".p_Registry_{$object}_expCRIT";
			$fn_dopk = $this->scheme.".p_Registry_{$object}_expKSG_Dop";
			//$p_kslp = $this->scheme.".p_Registry_{$object}_expKSLP";
		}

		$queryParams = [
			'Registry_id' => $data['Registry_id'],
		];

		$BDIAG = [];
		$BPROT = [];
		$CONS = [];
		$CRIT = [];
		$DIR_OUT = [];
		$DOP_K = [];
		$DS2 = [];
		$DS3 = [];
		$LEK_PR = [];
		$NAPR_OUT = [];
		$ONKOUSL = [];
		$SL_KOEF = [];
		$USL = [];

		$DIR_IN_FIELDS = [ 'ID_DIR', 'NUM_DIR', 'DATE_DIR', 'EXTR', 'NPR_MO', 'R_NPR_MO', 'NAPR_V', 'MET_ISSL',
			'NAPR_USL'
		];
		$DIR_OUT_FIELDS = [ 'ID_DIR', 'NUM_DIR', 'DATE_DIR', 'EXTR', 'NPR_MO', 'R_NPR_MO', 'NAZ_SP', 'NAPR_V',
			'MET_ISSL', 'NAPR_USL'
		];
		$DISPN_FIELDS = [ 'MKB', 'DATE_MKB', 'FIRST', 'GROUP_DN', 'DISPD', 'DISPD_END', 'DN', 'DATE_NEXT' ];
		$HMP_FIELDS = [ 'VID_HMP', 'METOD_HMP', 'TAL_NUM', 'TAL_D', 'TAL_P' ];
		$NAPR_IN_FIELDS = [ 'NAPR_V', 'MET_ISSL', 'NAPR_USL' ];
		$NAPR_OUT_FIELDS = [ 'NAPR_V', 'MET_ISSL', 'NAPR_USL' ];
		$DOCUM_FIELDS = [ 'DOCTYPE', 'DOCSER', 'DOCNUM', 'DOCDATE', 'DOCORG' ];
		$DOCUM_P_FIELDS = [ 'DOCTYPE_P', 'DOCSER_P', 'DOCNUM_P', 'DOCDATE_P', 'DOCORG_P' ];
		$OMS_FIELDS = [ 'VPOLIS', 'SPOLIS', 'NPOLIS', 'SMO_OK', 'SMO', 'SMO_OGRN', 'SMO_NAME' ];
		$ONK_SL_FIELDS = [ 'DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD', 'K_FR', 'WEI', 'HEI', 'BSA' ];
		$PACIENT_FIELDS = [ 'ID_PAC', 'IND_CFOND', 'VPOLIS', 'SPOLIS', 'NPOLIS', 'SMO_OK', 'SMO', 'SMO_OGRN',
			'SMO_NAME', 'NOVOR', 'INV', 'VNOV_D', 'C_OKSM', 'KONT', 'MSE', 'COMENTP'
		];
		$PREDST_FIELDS = [ 'FAM_P', 'IM_P', 'OT_P', 'W_P', 'DR_P', 'DOCTYPE_P', 'DOCSER_P', 'DOCNUM_P', 'DOCDATE_P',
			'DOCORG_P'
		];

		$altKeys = [
			 'CODE_MD_LP' => 'CODE_MD'
			,'CODE_MD_OPER' => 'CODE_MD'
			,'CODE_MD_USL' => 'CODE_MD'
			,'CODE_USL_SM' => 'CODE_USL'
			,'CRIT_VAL' => 'CRIT'
			,'DATE_1_OPER' => 'DATE_1'
			,'DATE_2_OPER' => 'DATE_2'
			,'DATE_1_SM' => 'DATE_1'
			,'DATE_2_SM' => 'DATE_2'
			,'DATE_INJ_VAL' => 'DATE_INJ'
			,'DOCTYPE_P' => 'DOCTYPE'
			,'DOCSER_P' => 'DOCSER'
			,'DOCNUM_P' => 'DOCNUM'
			,'DOCDATE_P' => 'DOCDATE'
			,'DOCORG_P' => 'DOCORG'
			,'DET_USL' => 'DET'
			,'ID_MED_TF_USL' => 'ID_MED_TF'
			,'LPU_1_USL' => 'LPU_1'
			,'PODR_USL' => 'PODR'
			,'PROFIL_USL' => 'PROFIL'
			,'PRVS_SM' => 'PRVS'
			,'PRVS_USL' => 'PRVS'
			,'REM_VAL' => 'REM'
			,'TARIF_USL' => 'TARIF'
			,'VIDPOM_OSM' => 'VIDPOM'
			,'VNOV_M_VAL' => 'VNOV_M'
			,'VNOV_M_SM_VAL' => 'VNOV_M'
		];

		// Добавляем в $altKeys поля для DIR_OUT
		foreach ($DIR_OUT_FIELDS as $field) {
			$altKeys[$field . '_DO'] = $field;
		}

		// Добавляем в $altKeys поля для NAPR_OUT
		foreach ($NAPR_OUT_FIELDS as $field) {
			$altKeys[$field . '_DO'] = $field;
		}

		$netValue = toAnsi('НЕТ', true);

		// Сведения о проведении консилиума (CONS)
		if ( !empty($fn_cons) ) {
			$query = "select * from {$fn_cons}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $CONS
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($CONS[$row['Evn_id']]) ) {
					$CONS[$row['Evn_id']] = [];
				}

				$CONS[$row['Evn_id']][] = $row;
			}
		}

		// Сведения о классификационных критериях (CRIT)
		if ( !empty($p_crit) ) {
			$query = "exec {$p_crit} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $CRIT
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($CRIT[$row['Evn_id']]) ) {
					$CRIT[$row['Evn_id']] = [];
				}

				$CRIT[$row['Evn_id']][] = $row;
			}
		}

		// Дополнительные сведения для определения КСГ (DOP_K)
		if ( !empty($fn_dopk) ) {
			$query = "select * from  {$fn_dopk}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $DOP_K
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($DOP_K[$row['Evn_id']]) ) {
					$DOP_K[$row['Evn_id']] = [];
				}
				else {
					// @task https://redmine.swan-it.ru/issues/198638
					$row['INDBAR'] = null;
					$row['SOFA'] = null;
					$row['SRM'] = null;
				}

				$DOP_K[$row['Evn_id']][] = $row;
			}
		}

		// Диагнозы (DS2)
		if ( !empty($fn_ds2) ) {
			$query = "select * from  {$fn_ds2}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $DS2
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($DS2[$row['Evn_id']]) ) {
					$DS2[$row['Evn_id']] = [];
				}

				$DS2[$row['Evn_id']][] = $row;
			}
		}

		// Диагнозы (DS3)
		if ( !empty($fn_ds3) ) {
			$query = "select * from  {$fn_ds3}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $DS3
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($DS3[$row['Evn_id']]) ) {
					$DS3[$row['Evn_id']] = [];
				}

				$DS3[$row['Evn_id']][] = $row;
			}
		}

		// КСЛП (SL_KOEF)
		if ( !empty($p_kslp) ) {
			$query = "exec {$p_kslp} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $SL_KOEF
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($SL_KOEF[$row['Evn_id']]) ) {
					$SL_KOEF[$row['Evn_id']] = [];
				}

				$SL_KOEF[$row['Evn_id']][] = $row;
			}
		}

		// Сведения о введённом противоопухолевом лекарственном препарате (LEK_PR)
		if ( !empty($p_lek_pr) ) {
			$query = "exec {$p_lek_pr} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $LEK_PR
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($LEK_PR[$row['EvnUsluga_id']]) ) {
					$LEK_PR[$row['EvnUsluga_id']] = [];
				}

				$LEK_PR[$row['EvnUsluga_id']][] = $row;
			}
		}

		// Диагностический блок (BDIAG)
		if ( !empty($fn_bdiag) ) {
			$query = "select * from {$fn_bdiag}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $BDIAG
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($BDIAG[$row['Evn_id']]) ) {
					$BDIAG[$row['Evn_id']] = [];
				}

				$BDIAG[$row['Evn_id']][] = $row;
			}
		}

		// Сведения об имеющихся противопоказаниях и отказах (BPROT)
		if ( !empty($fn_bprot) ) {
			$query = "select * from {$fn_bprot}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $BPROT
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($BPROT[$row['Evn_id']]) ) {
					$BPROT[$row['Evn_id']] = [];
				}

				$BPROT[$row['Evn_id']][] = $row;
			}
		}

		// Направления (DIR_OUT)
		if ( !empty($fn_dir_out) ) {
			$query = "select * from  {$fn_dir_out}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $DIR_OUT
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($DIR_OUT[$row['Evn_id']]) ) {
					$DIR_OUT[$row['Evn_id']] = [];
				}

				$dir_out = [
					'NAPR_OUT_DATA' => []
				];

				foreach ($row as $key => $value) {
					if ($key == 'EvnDirection_id') {
						$dir_out[$key] = $value;
					}
					else {
						$dir_out[$key . '_DO'] = $value;
					}
				}

				$DIR_OUT[$row['Evn_id']][] = $dir_out;
			}
		}

		// Направления (NAPR_OUT)
		if ( !empty($fn_napr_out) ) {
			$query = "select * from  {$fn_napr_out}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $NAPR_OUT
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($NAPR_OUT[$row['EvnDirection_id']]) ) {
					$NAPR_OUT[$row['EvnDirection_id']] = [];
				}

				$napr_out = [];

				foreach ($row as $key => $value) {
					if ($key == 'EvnDirection_id') {
						$napr_out[$key] = $value;
					}
					else {
						$napr_out[$key . '_DO'] = $value;
					}
				}

				$NAPR_OUT[$row['EvnDirection_id']][] = $napr_out;
			}
		}

		// Сведения об услуге при лечении онкологического заболевания (ONKOUSL)
		if ( !empty($fn_onkousl) ) {
			$query = "select * from {$fn_onkousl}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $ONKOUSL
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($ONKOUSL[$row['Evn_id']]) ) {
					$ONKOUSL[$row['Evn_id']] = [];
				}

				$ONKOUSL[$row['Evn_id']][] = $row;
			}
		}

		// Услуги (USL)
		if ( !empty($fn_usl) ) {
			$query = "select * from {$fn_usl}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$resultUSL = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($resultUSL) ) {
				return false;
			}

			// Массив $USL
			while ( $row = $resultUSL->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if (!isset($USL[$row['Evn_id']])) {
					$USL[$row['Evn_id']] = [];
				}

				$USL[$row['Evn_id']][] = $row;

				/*if (!empty($row['EvnUsluga_kolvo'])) {
					$row['KOL_USL'] = number_format(($row['KOL_USL'] / $row['EvnUsluga_kolvo']), 2, '.', '');
					for ($i = 0; $i < $row['EvnUsluga_kolvo']; $i++) {
						$this->_IDSERV++;
						$row['IDSERV'] = $this->_IDSERV;
						$USL[$row['Evn_id']][] = $row;
					}
				} else {
					$this->_IDSERV++;
					$row['IDSERV'] = $this->_IDSERV;
					$USL[$row['Evn_id']][] = $row;
				}*/
			}
		}

		// 2. джойним сразу посещения + пациенты и гребем постепенно то что получилось, сразу записывая в файл
		$result = $this->db->query("
			set nocount on;
			select * into #sluch from {$fn_sluch} (:Registry_id);
			select * into #sluch_med from {$fn_sluch_med} (:Registry_id);
			select * into #pers from {$fn_pers} (:Registry_id);
			set nocount off;
			
			select
				null as 'fields_part_1',
				s.*,
				s.MaxEvn_id as MaxEvn_sid,
				null as 'fields_part_2',
				sm.*,
				sm.Evn_id as Evn_smid,
				sm.Registry_id as Registry_smid,
				null as 'fields_part_3',
				p.*
			from
				#sluch s (nolock)
				inner join #sluch_med sm (nolock) on sm.MaxEvn_id = s.MaxEvn_id
				inner join #pers p (nolock) on p.MaxEvn_id = s.MaxEvn_id
			order by
				p.FAM, p.IM, p.OT, p.ID_PAC, sm.MaxEvn_id, sm.RegistryData_RowNum
		", $queryParams, true);

		if ( !is_object($result) ) {
			return false;
		}

		$PERS_ARRAY = [];
		$ZAP_ARRAY = [];

		$recKeys = []; // ключи для данных

		$prevID_PAC = null;

		while ( $one_rec = $result->_fetch_assoc() ) {
			array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);

			if ( count($recKeys) == 0 ) {
				$recKeys = $this->_getKeysForRec($one_rec);

				if ( count($recKeys) < 3 ) {
					$this->textlog->add('Ошибка, неверное количество частей в запросе', 'ERROR');
					return false;
				}
			}

			$sl_key = $one_rec['MaxEvn_sid'];
			$sl_med_key = $one_rec['Evn_smid'];

			$SLUCH = array_intersect_key($one_rec, $recKeys[1]);
			$SLUCH_MED = array_intersect_key($one_rec, $recKeys[2]);
			$PERS = array_intersect_key($one_rec, $recKeys[3]);

			$SLUCH_MED['Evn_id'] = $one_rec['Evn_smid'];
			$SLUCH_MED['Registry_id'] = $one_rec['Registry_smid'];

			// если нагребли больше 100 записей и предыдущий пациент был другим, то записываем всё что получилось в файл.
			if ( count($ZAP_ARRAY) >= 100 && $PERS['ID_PAC'] != $prevID_PAC ) {
				// пишем в файл случаи
				$xml = $this->parser->parse_ext(
					'export_xml/' . $this->exportSluchDataBodyTemplate,
					[ 'ZAP_DATA' => $ZAP_ARRAY ],
					true,
					false,
					$altKeys
				);

				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($this->exportSluchDataFileTmp, $xml, FILE_APPEND);
				unset($xml);
				unset($ZAP_ARRAY);
				$ZAP_ARRAY = [];

				// пишем в файл пациентов
				$xml_pers = $this->parser->parse_ext(
					'export_xml/' . $this->exportPersonDataBodyTemplate,
					[ 'PERS_DATA' => $PERS_ARRAY ],
					true,
					false,
					$altKeys
				);

				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($this->exportPersonDataFile, $xml_pers, FILE_APPEND);
				unset($xml_pers);
				unset($PERS_ARRAY);
				$PERS_ARRAY = [];
			}

			$prevID_PAC = $PERS['ID_PAC'];

			$SLUCH_MED['CONS_DATA'] = [];
			$SLUCH_MED['DIR_OUT_DATA'] = [];
			$SLUCH_MED['DISPN_DATA'] = [];
			$SLUCH_MED['DOP_K_DATA'] = [];
			$SLUCH_MED['DS2_DATA'] = [];
			$SLUCH_MED['DS3_DATA'] = [];
			$SLUCH_MED['HMP_DATA'] = [];
			$SLUCH_MED['KOEF_DATA'] = [];
			$SLUCH_MED['LP_DATA'] = [];
			$SLUCH_MED['ONK_SL_DATA'] = [];
			$SLUCH_MED['OPER_DATA'] = [];
			$SLUCH_MED['PAIN_DATA'] = [];
			$SLUCH_MED['SANK_DATA'] = [];
			$SLUCH_MED['USL_DATA'] = [];
			$SLUCH_MED['VNOV_M_SM_DATA'] = [];

			$ONK_SL_DATA = [];

			if ( isset($USL[$sl_med_key]) ) {
				foreach ( $USL[$sl_med_key] as $row ) {
					$this->_IDSERV++;
					$row['IDSERV'] = $this->_IDSERV;
					$row['SANK_USL_DATA'] = [];
					$SLUCH_MED['USL_DATA'][] = $row;
				}
				unset($USL[$sl_med_key]);
			}

			if ( isset($DIR_OUT[$sl_med_key]) ) {
				$SLUCH_MED['DIR_OUT_DATA'] = $DIR_OUT[$sl_med_key];
				unset($DIR_OUT[$sl_med_key]);
			}

			if ( isset($DOP_K[$sl_med_key]) ) {
				$SLUCH_MED['DOP_K_DATA'] = $DOP_K[$sl_med_key];
				unset($DOP_K[$sl_med_key]);
			}

			if ( isset($DS2[$sl_med_key]) ) {
				$SLUCH_MED['DS2_DATA'] = $DS2[$sl_med_key];
				unset($DS2[$sl_med_key]);
			}
			else if ( !empty($SLUCH_MED['DS2'])) {
				$SLUCH_MED['DS2_DATA'] = [[ 'DS2' => $SLUCH_MED['DS2'] ]];
			}

			if ( array_key_exists('DS2', $SLUCH_MED) ) {
				unset($SLUCH_MED['DS2']);
			}

			if ( isset($DS3[$sl_med_key]) ) {
				$SLUCH_MED['DS3_DATA'] = $DS3[$sl_med_key];
				unset($DS3[$sl_med_key]);
			}
			else if ( !empty($SLUCH_MED['DS3']) ) {
				$SLUCH_MED['DS3_DATA'] = [[ 'DS3' => $SLUCH_MED['DS3'] ]];
			}

			if ( array_key_exists('DS3', $SLUCH_MED) ) {
				unset($SLUCH_MED['DS3']);
			}

			$HMP_DATA = $this->_makeArrayForXML($SLUCH_MED, $HMP_FIELDS);

			if ( count($HMP_DATA) > 0 ) {
				$SLUCH_MED['HMP_DATA'][] = $HMP_DATA;
			}

			$DISPN_DATA = $this->_makeArrayForXML($SLUCH_MED, $DISPN_FIELDS);

			if ( count($DISPN_DATA) > 0 ) {
				$SLUCH_MED['DISPN_DATA'][] = $DISPN_DATA;
			}

			$onkDS2 = false;

			if ( count($SLUCH_MED['DS2_DATA']) > 0 ) {
				foreach ( $SLUCH_MED['DS2_DATA'] as $ds2 ) {
					if ( empty($ds2['DS2']) ) {
						continue;
					}

					$code = substr($ds2['DS2'], 0, 3);

					if ( ($code >= 'C00' && $code <= 'C80') || $code == 'C97' ) {
						$onkDS2 = true;
					}
				}
			}

			$IsZNO = (
				in_array($type, [ 1, 2 ])
				&& !empty($SLUCH_MED['DS1'])
				&& (
					substr($SLUCH_MED['DS1'], 0, 1) == 'C'
					|| (substr($SLUCH_MED['DS1'], 0, 3) >= 'D00' && substr($SLUCH_MED['DS1'], 0, 3) <= 'D09')
					|| ($SLUCH_MED['DS1'] == 'D70' && $onkDS2 == true)
				)
			);

			if (
				count($SLUCH_MED['DIR_OUT_DATA']) > 0
				&& ($IsZNO === true || $SLUCH_MED['DS_ONK'] == 1)
			) {
				foreach ($SLUCH_MED['DIR_OUT_DATA'] as $key => $row) {
					if (isset($NAPR_OUT[$row['EvnDirection_id']])) {
						$SLUCH_MED['DIR_OUT_DATA'][$key]['NAPR_OUT_DATA'] = $NAPR_OUT[$row['EvnDirection_id']];
						unset($NAPR_OUT[$row['EvnDirection_id']]);
					}
				}
			}

			if ($IsZNO) {
				// Цепляем CONS
				if ( isset($CONS[$sl_med_key]) ) {
					$SLUCH_MED['CONS_DATA'] = $CONS[$sl_med_key];
					unset($CONS[$sl_med_key]);
				}

				// Цепляем ONK_SL
				$hasONKOSLData = false;
				$ONK_SL_DATA['B_DIAG_DATA'] = [];
				$ONK_SL_DATA['B_PROT_DATA'] = [];
				$ONK_SL_DATA['ONK_USL_DATA'] = [];

				foreach ( $ONK_SL_FIELDS as $field ) {
					if ( isset($SLUCH_MED[$field]) ) {
						$hasONKOSLData = true;
						$ONK_SL_DATA[$field] = $SLUCH_MED[$field];
					}
					else {
						$ONK_SL_DATA[$field] = null;
					}

					if ( array_key_exists($field, $SLUCH_MED) ) {
						unset($SLUCH_MED[$field]);
					}
				}

				if ( isset($BDIAG[$sl_med_key]) ) {
					$hasONKOSLData = true;
					$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$sl_med_key];
					unset($BDIAG[$sl_med_key]);
				}

				if ( isset($BPROT[$sl_med_key]) ) {
					$hasONKOSLData = true;
					$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$sl_med_key];
					unset($BPROT[$sl_med_key]);
				}

				if ( isset($ONKOUSL[$sl_med_key]) ) {
					$hasONKOSLData = true;
					$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$sl_med_key];
					unset($ONKOUSL[$sl_med_key]);
				}

				if ( $hasONKOSLData == false ) {
					$ONK_SL_DATA = [];
				}
			}

			if ( count($ONK_SL_DATA) > 0 ) {
				$SLUCH_MED['ONK_SL_DATA'][] = $ONK_SL_DATA;
			}

			if ( isset($ZAP_ARRAY[$sl_key]) ) {
				// если уже есть законченный случай, значит добавляем в него SL
				$ZAP_ARRAY[$sl_key]['SLUCH_MED_DATA'][$sl_med_key] = $SLUCH_MED;
			}
			else {
				// иначе создаём новый ZAP
				$this->persCnt++;
				$this->zapCnt++;
				$this->_IDCASE++;

				$VNOV_M = [];
				
				if ( !empty($SLUCH['VNOV_M1']) ) {
					$VNOV_M[] = ['VNOV_M_VAL' => $SLUCH['VNOV_M1']];
				}

				if ( !empty($SLUCH['VNOV_M2']) ) {
					$VNOV_M[] = ['VNOV_M_VAL' => $SLUCH['VNOV_M2']];
				}

				$SLUCH['DIR_IN_DATA'] = [];
				$SLUCH['REM_DATA'] = [];
				$SLUCH['SLUCH_MED_DATA'] = [];
				$SLUCH['VNOV_M_DATA'] = $VNOV_M;

				$DOCUM_DATA = $this->_makeArrayForXML($PERS, $DOCUM_FIELDS);

				$PERS['DOCUM_DATA'] = (count($DOCUM_DATA) > 0 ? [ $DOCUM_DATA ] : []);
				$PERS['DOST'] = [];
				$PERS['OSPR_DATA'] = [];
				$PERS['PREDST_DATA'] = [];

				if ( $PERS['NOVOR'] == '0' ) {
					if ( empty($PERS['FAM']) ) {
						$PERS['DOST'][] = ['DOST_VAL' => 2];
					}

					if ( empty($PERS['IM']) ) {
						$PERS['DOST'][] = ['DOST_VAL' => 3];
					}

					if ( empty($PERS['OT']) || mb_strtoupper($PERS['OT'], 'windows-1251') == $netValue ) {
						$PERS['DOST'][] = ['DOST_VAL' => 1];
					}
				}
				else {
					$PREDST_DATA = $this->_makeArrayForXML($PERS, $PREDST_FIELDS);

					if ( count($PREDST_DATA) > 0 ) {
						$DOCUM_P_DATA = $this->_makeArrayForXML($PREDST_DATA, $DOCUM_P_FIELDS);

						$PREDST_DATA['DOST_P'] = [];
						$PREDST_DATA['DOCUM_P_DATA'] = (count($DOCUM_P_DATA) > 0 ? [ $DOCUM_P_DATA ] : []);

						if ( empty($PREDST_DATA['FAM_P']) ) {
							$PREDST_DATA['DOST_P'][] = ['DOST_P_VAL' => 2];
						}

						if ( empty($PREDST_DATA['IM_P']) ) {
							$PREDST_DATA['DOST_P'][] = ['DOST_P_VAL' => 3];
						}

						if ( empty($PREDST_DATA['OT_P']) || mb_strtoupper($PREDST_DATA['OT_P'], 'windows-1251') == $netValue ) {
							$PREDST_DATA['DOST_P'][] = ['DOST_P_VAL' => 1];
						}

						$PERS['PREDST_DATA'][] = $PREDST_DATA;
					}
				}

				$SLUCH['N_ZAP'] = $this->zapCnt;
				$SLUCH['ID_CASE'] = $this->_IDCASE;
				$SLUCH['ID_ZAP'] = $SLUCH_MED['ID_MED_USL'];
				$SLUCH['PR_NOV'] = (isset($data['Registry_IsRepeated']) && $data['Registry_IsRepeated'] == 2 ? 1 : 0);
				$SLUCH['PACIENT_DATA'] = [];
				$SLUCH['SLUCH_MED_DATA'][$sl_med_key] = $SLUCH_MED;

				$PACIENT = $this->_makeArrayForXML($PERS, $PACIENT_FIELDS, false);

				if ( count($PACIENT) > 0 ) {
					$OMS_DATA = $this->_makeArrayForXML($PACIENT, $OMS_FIELDS);
					$PACIENT['OMS_DATA'] = (count($OMS_DATA) > 0 ? [ $OMS_DATA ] : []);
					$SLUCH['PACIENT_DATA'][] = $PACIENT;
				}

				if ( !isset($PERS_ARRAY[$PERS['ID_PAC']]) ) {
					$PERS_ARRAY[$PERS['ID_PAC']] = $PERS;
				}

				$DIR_IN_DATA = $this->_makeArrayForXML($SLUCH, $DIR_IN_FIELDS);

				if ( count($DIR_IN_DATA) > 0 ) {
					$NAPR_IN_DATA = $this->_makeArrayForXML($DIR_IN_DATA, $NAPR_IN_FIELDS);
					$DIR_IN_DATA['NAPR_IN_DATA'] = (count($NAPR_IN_DATA) > 0 ? [ $NAPR_IN_DATA ] : []);
					$SLUCH['DIR_IN_DATA'][] = $DIR_IN_DATA;
				}

				$ZAP_ARRAY[$sl_key] = $SLUCH;
			}

			if ( !isset($this->registryEvnNum[$sl_med_key]) ) {
				$this->registryEvnNum[$sl_med_key] = [];
			}

			$this->registryEvnNum[$sl_med_key][] = [
				'e' => $sl_med_key,
				'r' => $SLUCH_MED['Registry_id'],
				't' => $type,
				'z' => $this->zapCnt,
				'i' => $this->_IDCASE,
			];
		}

		// записываем оставшееся
		if ( count($ZAP_ARRAY) > 0 ) {
			// пишем в файл случаи
			$xml = $this->parser->parse_ext(
				'export_xml/' . $this->exportSluchDataBodyTemplate,
				[ 'ZAP_DATA' => $ZAP_ARRAY ],
				true,
				false,
				$altKeys
			);

			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($this->exportSluchDataFileTmp, $xml, FILE_APPEND);
			unset($ZAP_ARRAY);
			unset($xml);

			// пишем в файл пациентов
			$xml_pers = $this->parser->parse_ext(
				'export_xml/' . $this->exportPersonDataBodyTemplate,
				[ 'PERS_DATA' => $PERS_ARRAY ],
				true,
				false,
				$altKeys
			);

			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($this->exportPersonDataFile, $xml_pers, FILE_APPEND);
			unset($PERS_ARRAY);
			unset($xml_pers);
		}

		return true;
	}

	/**
	 * Получение данных для выгрузки реестров в XML
	 * Типы реестров: ДВН, ДДМ, ПОВН, МОН
	 */
	protected function _loadRegistryDataForXmlDisp($type, $data) {
		$object = $this->_getRegistryObjectName($type);

		$fn_pers = $this->scheme.".p_Registry_{$object}_expPac";
		$fn_sluch = $this->scheme.".p_Registry_{$object}_expVizit";
		$fn_usl = $this->scheme.".p_Registry_{$object}_expUsl";

		$fn_ds1 = $this->scheme.".p_Registry_{$object}_expDS";

		if (in_array($type, [7,11])) {
			$fn_nazr = $this->scheme.".p_Registry_{$object}_expNAZ";
			$fn_usllab = $this->scheme.".p_Registry_{$object}_expUslLab";
		}

		$queryParams = [
			'Registry_id' => $data['Registry_id'],
		];

		$DS1 = [];
		$NAZR = [];
		$USL_LAB = [];

		$DOCUM_FIELDS = [ 'DOCTYPE', 'DOCSER', 'DOCNUM', 'DOCDATE', 'DOCORG' ];
		$DOCUM_P_FIELDS = [ 'DOCTYPE_P', 'DOCSER_P', 'DOCNUM_P', 'DOCDATE_P', 'DOCORG_P' ];
		$OBSL_FIELDS = [ 'ID_ANAL', 'IDANALYS', 'LABBEGIN', 'TARIF', 'SL_ALL', 'SP_ALL', 'PRVS', 'CODE_MD', 'REF',
			'PR_OTK', 'IDMU', 'MU_NAME', 'VAR', 'TYP_VIS', 'COMENTOBS' 
		];
		$OMS_FIELDS = [ 'VPOLIS', 'SPOLIS', 'NPOLIS', 'SMO_OK', 'SMO', 'SMO_OGRN', 'SMO_NAME' ];
		$OSM_FIELDS = [ 'ID_OL', 'PRVS', 'VIDPOM', 'CODE_USL', 'PCOD', 'SS_V', 'DLOOK', 'LMKB', 'L_FIRST', 'L_LAST',
			'L_MAIN', 'L_PREDV', 'DS_ONK', 'L_GROUP', 'TARIF', 'SL_ALL', 'SP_ALL', 'REF', 'PR_OTK', 'IDMU', 'MU_NAME',
			'TYP_VIS', 'COMENTOSM'
		];
		$PACIENT_FIELDS = [ 'ID_PAC', 'DISP', 'IND_CFOND', 'VPOLIS', 'SPOLIS', 'NPOLIS', 'SMO_OK', 'SMO', 'SMO_OGRN',
			'SMO_NAME', 'NOVOR', 'AGE_GR', 'KONT', 'C_KAT', 'NORD', 'COMENTP'
		];
		$PREDST_FIELDS = [ 'FAM_P', 'IM_P', 'OT_P', 'W_P', 'DR_P', 'DOCTYPE_P', 'DOCSER_P', 'DOCNUM_P', 'DOCDATE_P',
			'DOCORG_P'
		];
		$SDIAG_FIELDS = [ 'LMKB', 'L_FIRST', 'L_LAST', 'L_MAIN', 'L_PREDV', 'DS_ONK' ];

		$altKeys = [
			 'CODE_MD_LP' => 'CODE_MD'
			,'DIR_TO_VAL' => 'DIR_TO'
			,'DOCTYPE_P' => 'DOCTYPE'
			,'DOCSER_P' => 'DOCSER'
			,'DOCNUM_P' => 'DOCNUM'
			,'DOCDATE_P' => 'DOCDATE'
			,'DOCORG_P' => 'DOCORG'
			,'PR_OTK_SLUCH' => 'PR_OTK'
			,'TARIF_SLUCH' => 'TARIF'
			,'VIDPOM_SLUCH' => 'VIDPOM'
		];

		// Добавляем в $altKeys поля для OSM
		foreach ($OSM_FIELDS as $field ) {
			$altKeys[$field . '_OSM'] = $field;
		}
		
		// Добавляем в $altKeys поля для OBSL
		foreach ($OBSL_FIELDS as $field ) {
			$altKeys[$field . '_OBSL'] = $field;
		}
		
		$netValue = toAnsi('НЕТ', true);

		// Диагнозы (DS1)
		if (!empty($fn_ds1)) {
			$query = "select * from {$fn_ds1}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$result = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($result) ) {
				return false;
			}

			// Массив $DS1
			while ( $row = $result->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($DS1[$row['Evn_id']]) ) {
					$DS1[$row['Evn_id']] = [];
				}

				$DS1[$row['Evn_id']][] = $row;
			}
		}

		// Назначения (NAZR)
		if (!empty($fn_nazr)) {
			$query = "select * from {$fn_nazr}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$result = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($result) ) {
				return false;
			}

			// Массив $NAZR
			while ( $row = $result->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($NAZR[$row['Evn_id']]) ) {
					$NAZR[$row['Evn_id']] = [];
				}

				$NAZR[$row['Evn_id']][] = $row;
			}
		}

		// Лаб. услуги (USL_LAB)
		if (!empty($fn_usllab)) {
			$query = "select * from {$fn_usllab}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams), 'DEBUG');
			$result = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено', 'DEBUG');
			if ( !is_object($result) ) {
				return false;
			}

			// Массив $USL_LAB
			while ( $row = $result->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($USL_LAB[$row['EvnUsluga_id']]) ) {
					$USL_LAB[$row['EvnUsluga_id']] = [];
				}

				$USL_LAB[$row['EvnUsluga_id']][] = $row;
			}
		}

		// 2. джойним сразу карты + услуги + пациенты и гребем постепенно то, что получилось, сразу записывая в файл
		$result = $this->db->query("
			set nocount on;
			select * into #pers from {$fn_pers} (:Registry_id);
			select * into #sluch from {$fn_sluch} (:Registry_id);
			select * into #usl from {$fn_usl} (:Registry_id);
			set nocount off;
			
			select
				null as 'fields_part_1',
				s.*,
				s.Evn_id as Evn_sid,
				s.ID_ZAP as ID_ZAP_SLUCH,
				null as 'fields_part_2',
				u.*,
				null as 'fields_part_3',
				p.*,
				p.ID_ZAP as ID_ZAP_PERS
			from
				#sluch s (nolock)
				inner join #usl u (nolock) on u.Evn_id = s.Evn_id
				inner join #pers p (nolock) on p.Evn_id = s.Evn_id
			order by
				p.FAM, p.IM, p.OT, p.ID_PAC, s.Evn_id
		", $queryParams, true);

		if ( !is_object($result) ) {
			return false;
		}

		$PERS_ARRAY = [];
		$ZAP_ARRAY = [];

		$recKeys = []; // ключи для данных

		$prevID_PAC = null;

		while ( $one_rec = $result->_fetch_assoc() ) {
			array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);

			if ( count($recKeys) == 0 ) {
				$recKeys = $this->_getKeysForRec($one_rec);

				if ( count($recKeys) < 3 ) {
					$this->textlog->add('Ошибка, неверное количество частей в запросе', 'ERROR');
					return false;
				}
			}

			$sl_key = $one_rec['Evn_id'];

			$SLUCH = array_intersect_key($one_rec, $recKeys[1]);
			$USL = array_intersect_key($one_rec, $recKeys[2]);
			$PERS = array_intersect_key($one_rec, $recKeys[3]);

			$PERS['ID_ZAP'] = $PERS['ID_ZAP_PERS'];
			$SLUCH['ID_ZAP'] = $SLUCH['ID_ZAP_SLUCH'];

			// если нагребли больше 100 записей и предыдущий пациент был другим, то записываем всё что получилось в файл.
			if ( count($ZAP_ARRAY) >= 100 && $PERS['ID_PAC'] != $prevID_PAC ) {
				// пишем в файл случаи
				$xml = $this->parser->parse_ext(
					'export_xml/' . $this->exportSluchDataBodyTemplate,
					[ 'ZAP_DATA' => $ZAP_ARRAY ],
					true,
					false,
					$altKeys
				);

				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($this->exportSluchDataFileTmp, $xml, FILE_APPEND);
				unset($xml);
				unset($ZAP_ARRAY);
				$ZAP_ARRAY = [];

				// пишем в файл пациентов
				$xml_pers = $this->parser->parse_ext(
					'export_xml/' . $this->exportPersonDataBodyTemplate,
					[ 'PERS_DATA' => $PERS_ARRAY ],
					true,
					false,
					$altKeys
				);

				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($this->exportPersonDataFile, $xml_pers, FILE_APPEND);
				unset($xml_pers);
				unset($PERS_ARRAY);
				$PERS_ARRAY = [];
			}

			$prevID_PAC = $PERS['ID_PAC'];

			if ( !isset($ZAP_ARRAY[$sl_key]) ) {
				$this->persCnt++;
				$this->zapCnt++;
				$this->_IDCASE++;

				$SLUCH['DIR_TO_DATA'] = [];
				$SLUCH['DS1_DATA'] = [];
				$SLUCH['NAZR_DATA'] = [];
				$SLUCH['OBSL_DATA'] = [];
				$SLUCH['OSM_DATA'] = [];
				$SLUCH['PACIENT_DATA'] = [];
				$SLUCH['RISK_DATA'] = [];
				$SLUCH['SANK_DATA'] = [];

				$SLUCH['N_ZAP'] = $this->zapCnt;
				$SLUCH['ID_CASE'] = $this->_IDCASE;
				//$SLUCH['PR_NOV'] = (isset($data['Registry_IsRepeated']) && $data['Registry_IsRepeated'] == 2 ? 1 : 0); из expVizit

				$DOCUM_DATA = $this->_makeArrayForXML($PERS, $DOCUM_FIELDS);

				$PERS['DOCUM_DATA'] = (count($DOCUM_DATA) > 0 ? [$DOCUM_DATA] : []);
				$PERS['DOST'] = [];
				$PERS['OSPR_DATA'] = [];
				$PERS['PREDST_DATA'] = [];

				if ($PERS['NOVOR'] == '0') {
					if (empty($PERS['FAM'])) {
						$PERS['DOST'][] = ['DOST_VAL' => 2];
					}

					if (empty($PERS['IM'])) {
						$PERS['DOST'][] = ['DOST_VAL' => 3];
					}

					if (empty($PERS['OT']) || mb_strtoupper($PERS['OT'], 'windows-1251') == $netValue) {
						$PERS['DOST'][] = ['DOST_VAL' => 1];
					}
				} else {
					$PREDST_DATA = $this->_makeArrayForXML($PERS, $PREDST_FIELDS);

					if (count($PREDST_DATA) > 0) {
						$DOCUM_P_DATA = $this->_makeArrayForXML($PREDST_DATA, $DOCUM_P_FIELDS);

						$PREDST_DATA['DOST_P'] = [];
						$PREDST_DATA['DOCUM_P_DATA'] = (count($DOCUM_P_DATA) > 0 ? [$DOCUM_P_DATA] : []);

						if (empty($PREDST_DATA['FAM_P'])) {
							$PREDST_DATA['DOST_P'][] = ['DOST_P_VAL' => 2];
						}

						if (empty($PREDST_DATA['IM_P'])) {
							$PREDST_DATA['DOST_P'][] = ['DOST_P_VAL' => 3];
						}

						if (empty($PREDST_DATA['OT_P']) || mb_strtoupper($PREDST_DATA['OT_P'], 'windows-1251') == $netValue) {
							$PREDST_DATA['DOST_P'][] = ['DOST_P_VAL' => 1];
						}

						$PERS['PREDST_DATA'][] = $PREDST_DATA;
					}
				}

				if (isset($DS1[$sl_key])) {
					$SLUCH['DS1_DATA'] = $DS1[$sl_key];
					unset($DS1[$sl_key]);
				}

				if (isset($NAZR[$sl_key])) {
					$SLUCH['NAZR_DATA'] = $NAZR[$sl_key];
					unset($NAZR[$sl_key]);
				}

				$PACIENT = $this->_makeArrayForXML($PERS, $PACIENT_FIELDS, false);

				if (count($PACIENT) > 0) {
					$OMS_DATA = $this->_makeArrayForXML($PACIENT, $OMS_FIELDS);
					$PACIENT['OMS_DATA'] = (count($OMS_DATA) > 0 ? [$OMS_DATA] : []);
					$SLUCH['PACIENT_DATA'][] = $PACIENT;
				}

				if (!isset($PERS_ARRAY[$PERS['ID_PAC']])) {
					$PERS_ARRAY[$PERS['ID_PAC']] = $PERS;
				}

				$ZAP_ARRAY[$sl_key] = $SLUCH;
			}
			
			if(!isset($ZAP_ARRAY[$sl_key]['OBSL_DATA'])){
				$ZAP_ARRAY[$sl_key]['OBSL_DATA'] = [];
			}
			
			if(!isset($ZAP_ARRAY[$sl_key]['OSM_DATA'])){
				$ZAP_ARRAY[$sl_key]['OSM_DATA'] = [];
			}
			
			$OBSL_DATA = [];
			$OSM_DATA = [];
			
			switch ($USL['uslType']) {
				case 'OBSL':
					$usl = $this->_makeArrayForXML($USL, $OBSL_FIELDS, false);
					foreach ($usl as $key => $value) {
						$OBSL_DATA[$key . '_OBSL'] = $value;
					}
					$OBSL_DATA['SANK_OBS_DATA'] = [];
					$OBSL_DATA['USL_DIAG_DATA'] = [];
					if (isset($USL['PATF'])) {
						$OBSL_DATA['USL_DIAG_DATA'][] = [
							'PATF' => $USL['PATF'],
							'COD_PATF_DATA' => []
						];
					}
					$OBSL_DATA['USL_LAB_DATA'] = (isset($USL['EvnUsluga_id']) && isset($USL_LAB[$USL['EvnUsluga_id']])) ? $USL_LAB[$USL['EvnUsluga_id']] : [];
					$ZAP_ARRAY[$sl_key]['OBSL_DATA'][] = $OBSL_DATA;
					break;

				case 'OSM':
					$usl = $this->_makeArrayForXML($USL, $OSM_FIELDS, false);
					foreach ($usl as $key => $value) {
						$OSM_DATA[$key . '_OSM'] = $value;
					}
					$OSM_DATA['SANK_OSM_DATA'] = [];
					$OSM_DATA['SDIAG_DATA'] = [];

					$SDIAG_DATA = $this->_makeArrayForXML($usl, $SDIAG_FIELDS, false);

					if (count($SDIAG_DATA) > 0) {
						$OSM_DATA['SDIAG_DATA'][] = $SDIAG_DATA;
					}
					$ZAP_ARRAY[$sl_key]['OSM_DATA'][] = $OSM_DATA;
					break;
			}

			
			if ( !isset($this->registryEvnNum[$sl_key]) ) {
				$this->registryEvnNum[$sl_key] = [];
			}

			$this->registryEvnNum[$sl_key][] = [
				'e' => $sl_key,
				'r' => $data['Registry_id'],
				't' => $type,
				'z' => $this->zapCnt,
				'i' => $this->_IDCASE,
			];
		}

		// записываем оставшееся
		if ( count($ZAP_ARRAY) > 0 ) {
			// пишем в файл случаи
			$xml = $this->parser->parse_ext(
				'export_xml/' . $this->exportSluchDataBodyTemplate,
				[ 'ZAP_DATA' => $ZAP_ARRAY ],
				true,
				false,
				$altKeys
			);

			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($this->exportSluchDataFileTmp, $xml, FILE_APPEND);
			unset($ZAP_ARRAY);
			unset($xml);

			// пишем в файл пациентов
			$xml_pers = $this->parser->parse_ext(
				'export_xml/' . $this->exportPersonDataBodyTemplate,
				[ 'PERS_DATA' => $PERS_ARRAY ],
				true,
				false,
				$altKeys
			);

			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($this->exportPersonDataFile, $xml_pers, FILE_APPEND);
			unset($PERS_ARRAY);
			unset($xml_pers);
		}

		return true;
	}

	/**
	 * Получение номера выгружаемого файла реестра в отчетном периоде
	 */
	protected function _setXmlPackNum($data) {
		$query = "
			declare
				 @packNum int
				,@Err_Msg varchar(400);

			set nocount on;

			begin try
				set @packNum = (
					select top 1 Registry_PackNum
					from {$this->scheme}.v_Registry with (nolock)
					where Registry_id = :Registry_id
				);

				if ( @packNum is null )
					begin
						set @packNum = (
							select max(Registry_PackNum)
							from {$this->scheme}.Registry with (nolock)
							where Lpu_id = :Lpu_id
								and SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) = :Registry_endMonth
								and Registry_PackNum is not null
						);

						set @packNum = ISNULL(@packNum, 0) + 1;

						update {$this->scheme}.Registry with (updlock)
						set Registry_PackNum = @packNum
						where Registry_id = :Registry_id
					end
			end try
			
			begin catch
				set @Err_Msg = error_message();
				set @packNum = null;
			end catch

			set nocount off;

			select @packNum as packNum, @Err_Msg as Error_Msg;
		";
		$result = $this->db->query($query, $data);

		// echo getDebugSQL($query, $data);

		$packNum = 0;

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 && !empty($response[0]['packNum']) ) {
				$packNum = $response[0]['packNum'];
			}
		}

		return $packNum;
	}

	/**
	 * @descr Устанавливаем/снимаем признак удаления записей реестра
	 * @param array $data
	 * @return bool
	 */
	public function deleteRegistryData($data = []) {
		$response = [
			'Error_Code' => '',
			'Error_Msg' => '',
			'success' => true,
		];

		try {
			$this->beginTransaction();

			if (
				(!is_array($data['Evn_ids']) || count($data['Evn_ids']) == 0)
				&& (!is_array($data['Evn_rids']) || count($data['Evn_rids']) == 0)
			) {
				throw new Exception('Не выбраны случаи', __LINE__);
			}

			$this->setRegistryParamsByType($data);

			$RegistryData_deleted = ($data['RegistryData_deleted'] == 1 ? 2 : 1);

			if ( is_array($data['Evn_rids']) && count($data['Evn_rids']) > 0 ) {
				$EvnFilter = "Evn_rid in (" . implode(",", $data['Evn_rids']) . ")";
			}
			else {
				$EvnFilter = "Evn_id in (" . implode(",", $data['Evn_ids']) . ")";
			}

			if ($this->RegistryType_id == 1) {
				$queryResp = $this->queryResult("
					with EvnList as (
						select
							Evn_rid,
							EvnSection_NumGroup
						from {$this->scheme}.v_{$this->RegistryDataObject} with (nolock)
						where {$EvnFilter}
							and Registry_id = :Registry_id
					)

					select RD.Evn_id
					from {$this->scheme}.v_{$this->RegistryDataObject} as RD with (nolock)
					where exists (select top 1 Evn_rid from EvnList where Evn_rid = RD.Evn_rid and EvnSection_NumGroup = RD.EvnSection_NumGroup)
						and RD.Registry_id = :Registry_id
				", [
					'Registry_id' => $data['Registry_id']
				]);
			}
			else {
				$queryResp = $this->queryResult("
					select Evn_id
					from {$this->scheme}.v_{$this->RegistryDataObject} with (nolock)
					where {$EvnFilter}
						and Registry_id = :Registry_id
				", [
					'Registry_id' => $data['Registry_id']
				]);
			}

			foreach ( $queryResp as $row ) {
				$delResp = $this->getFirstRowFromQuery("
					Declare @Error_Code bigint;
					Declare @Error_Message varchar(4000);
	
					exec {$this->scheme}.p_RegistryData_del
						@Evn_id = :Evn_id,
						@Registry_id = :Registry_id,
						@RegistryType_id = :RegistryType_id,
						@RegistryData_deleted = :RegistryData_deleted,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
	
					select
						@Error_Code as Error_Code,
						@Error_Message as Error_Msg;
				", [
					'Evn_id' => $row['Evn_id'],
					'Registry_id' => $data['Registry_id'],
					'RegistryType_id' => $data['RegistryType_id'],
					'RegistryData_deleted' => $RegistryData_deleted,
				]);

				if ( $delResp === false || !is_array($delResp) || count($delResp) == 0 ) {
					throw new Exception('Ошибка при ' . ($RegistryData_deleted == 2 ? 'удалении' : 'восстановлении') . ' записи реестра', __LINE__);
				}
				else if ( !empty($delResp['Error_Msg']) ) {
					throw new Exception($delResp['Error_Msg'], $delResp['Error_Code']);
				}
			}

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();

			$response['Error_Code'] = $e->getCode();
			$response['Error_Msg'] = $e->getMessage();
			$response['success'] = false;
		}

		return $response;
	}

	/**
	 * comment
	 */
	public function loadRegistry($data) {
		$filter = "(1=1)";
		$params = [ 'Lpu_id' => (isset($data['Lpu_id'])) ? $data['Lpu_id'] : $data['session']['lpu_id'] ];
		$filter .= ' and R.Lpu_id = :Lpu_id';

		$this->setRegistryParamsByType($data);

		if ( !empty($data['Registry_id']) ) {
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}

		if ( !empty($data['RegistryType_id']) ) {
			$filter .= ' and R.RegistryType_id = :RegistryType_id';
			$params['RegistryType_id'] = $data['RegistryType_id'];
		}

		if ( !empty($data['RegistryStatus_id']) ) {
			// если оплаченные или удаленные
			if( 4 == (int)$data['RegistryStatus_id'] || 6 == (int)$data['RegistryStatus_id'] ) {
				if( $data['Registry_accYear'] > 0 ) {
					$filter .= ' and YEAR(R.Registry_accDate) = :Registry_accYear';
					$params['Registry_accYear'] = $data['Registry_accYear'];
				}
			}
		}

		$loadDeleted = (!empty($data['RegistryStatus_id']) && $data['RegistryStatus_id'] == 6);

		if ( !empty($data['RegistryStatus_id']) && $data['RegistryStatus_id'] == 5 ) {
			$query = "
				select 
					R.RegistryQueue_id as Registry_id,
					R.RegistryType_id,
					5 as RegistryStatus_id,
					2 as Registry_IsActive,
					RTrim(R.Registry_Num)+' / в очереди: ' + LTrim(cast(RegistryQueue_Position as varchar)) as Registry_Num,
					convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
					convert(varchar(10), R.Registry_begDate, 104) as Registry_begDate,
					convert(varchar(10), R.Registry_endDate, 104) as Registry_endDate,
					R.Lpu_id,
					DC.DispClass_id,
					DC.DispClass_Name,
					R.PayType_id,
					PT.PayType_Name,
					PT.PayType_SysNick,
					R.KatNasel_id,
					KN.KatNasel_Name,
					KN.KatNasel_SysNick,
					R.OrgRSchet_id,
					0 as Registry_Count,
					0 as Registry_ErrorCount,
					0 as RegistryErrorCom_IsData,
					0 as RegistryError_IsData,
					0 as RegistryNoPolis_IsData,
					0 as RegistryErrorTFOMS_IsData,
					0 as RegistryErrorTfomsBDZ_IsData,
					0 as Registry_Sum,
					0 as Registry_SumPaid,
					1 as Registry_IsProgress,
					1 as Registry_IsNeedReform,
					'' as Registry_updDate,
					0 as RegistryCheckStatus_Code,
					'' as RegistryCheckStatus_Name
				from {$this->scheme}.v_RegistryQueue R with (NOLOCK)
					left join v_DispClass DC with (NOLOCK) on DC.DispClass_id = R.DispClass_id 
					left join v_PayType PT with (NOLOCK) on PT.PayType_id = R.PayType_id
					left join v_KatNasel KN with (NOLOCK) on KN.KatNasel_id = R.KatNasel_id
				where {$filter}
			";
		}
		else {
			$source_table = 'v_Registry';

			if ( !empty($data['RegistryStatus_id']) ) {
				if ( $loadDeleted ) {
					// если запрошены удаленные реестры
					$source_table = 'v_Registry_deleted';
				}
				else {
					$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
					$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
				}
			}

			$query = "
				select 
					R.Registry_id,
					R.RegistryType_id,
					R.RegistryStatus_id,
					R.Registry_IsActive,
					ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
					RTrim(R.Registry_Num) as Registry_Num,
					convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
					convert(varchar(10), R.Registry_insDT, 104) as Registry_insDT,
					convert(varchar(10), R.Registry_begDate, 104) as Registry_begDate,
					convert(varchar(10), R.Registry_endDate, 104) as Registry_endDate,
					R.Lpu_id,
					DC.DispClass_id,
					DC.DispClass_Name,
					R.PayType_id,
					PT.PayType_Name,
					PT.PayType_SysNick,
					R.KatNasel_id,
					KN.KatNasel_Name,
					KN.KatNasel_SysNick,
					R.OrgRSchet_id,
					isnull(R.Registry_RecordCount, 0) as Registry_Count,
					isnull(R.Registry_ErrorCount, 0) as Registry_ErrorCount,
					RegistryErrorCom.RegistryErrorCom_IsData,
					RegistryError.RegistryError_IsData,
					RegistryNoPolis.RegistryNoPolis_IsData,
					RegistryErrorTFOMS.RegistryErrorTFOMS_IsData,
					RegistryErrorTfomsBDZ.RegistryErrorTfomsBDZ_IsData,
					isnull(R.Registry_Sum, 0.00) as Registry_Sum,
					isnull(R.Registry_SumPaid, 0.00) as Registry_SumPaid,
					case when RQ.RegistryQueue_id is not null then 1 else 0 end as Registry_IsProgress,
					convert(varchar(10), R.Registry_updDT, 104) + ' ' + convert(varchar(8), R.Registry_updDT, 108) as Registry_updDate,
					convert(varchar, RQH.RegistryQueueHistory_endDT, 104) + ' ' + convert(varchar, RQH.RegistryQueueHistory_endDT, 108) as ReformTime,
					RCS.RegistryCheckStatus_Code,
					RCS.RegistryCheckStatus_Name,
					STUFF(
						(SELECT distinct
							',' + CAST(t1.MedPersonal_id as varchar)
						FROM
							{$this->scheme}.v_{$this->RegistryDataObject} t1 with (nolock)
						WHERE
							t1.Registry_id = R.Registry_id
							and t1.MedPersonal_id is not null
						FOR XML PATH ('')
						), 1, 1, ''
					) as MedPersonalList
				from {$this->scheme}.{$source_table} R with (NOLOCK)
					left join v_DispClass DC with (NOLOCK) on DC.DispClass_id = R.DispClass_id
					left join v_PayType PT with (NOLOCK) on PT.PayType_id = R.PayType_id
					left join v_KatNasel KN with (NOLOCK) on KN.KatNasel_id = R.KatNasel_id
					left join dbo.v_RegistryCheckStatus RCS with (NOLOCK) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
					outer apply(
						select top 1 RegistryQueue_id
						from {$this->scheme}.v_RegistryQueue with (NOLOCK)
						where Registry_id = R.Registry_id
					) RQ
					outer apply(
						select top 1 RegistryQueueHistory_endDT
						from {$this->scheme}.RegistryQueueHistory with (NOLOCK)
						where Registry_id = R.Registry_id
							and RegistryQueueHistory_endDT is not null
						order by RegistryQueueHistory_id desc
					) RQH
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorCom_IsData from {$this->scheme}.v_{$this->RegistryErrorComObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryErrorCom
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryError_IsData from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryError
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData from {$this->scheme}.v_{$this->RegistryNoPolisObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryNoPolis
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData from {$this->scheme}.v_RegistryErrorTFOMS RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryErrorTFOMS
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTfomsBDZ_IsData from {$this->scheme}.v_RegistryErrorTfomsBDZ RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryErrorTfomsBDZ
				where 
					{$filter}
				order by
					R.Registry_endDate DESC,
					RQH.RegistryQueueHistory_endDT DESC
			";
		}

		return $this->queryResult($query, $params);
	}

	/**
	 * Установка реестра в очередь на формирование
	 * Возвращает номер в очереди
	 */
	public function saveRegistryQueue($data) {
		if ( !in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) ) {
			return [[ 'success' => false, 'Error_Msg' => 'Данный функционал пока не доступен!' ]];
		}

		// Сохранение нового реестра
		if ( empty($data['Registry_id']) ) {
			$data['Registry_IsActive'] = 2;
			$operation = 'insert';
		}
		else {
			$operation = 'update';
		}

		if ( $operation == 'update' ) {
			$rq = $this->loadRegistryQueue($data);

			if ( is_array($rq) && count($rq) > 0 && $rq[0]['RegistryQueue_Position'] > 0 ) {
				return [[ 'success' => false, 'Error_Msg' => '<b>Запрос МО по данному реестру уже находится в очереди на формирование.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания текущего формирования реестра.' ]];
			}
		}

		$params = [
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['Lpu_id'],
			'RegistryType_id' => $data['RegistryType_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'Registry_Num' => $data['Registry_Num'],
			'Registry_IsActive' => $data['Registry_IsActive'] ?? null,
			'Registry_accDate' => $data['Registry_accDate'],
			'pmUser_id' => $data['pmUser_id'],
			'DispClass_id' => $data['DispClass_id'],
			'PayType_id' => $this->getFirstResultFromQuery("select top 1 PayType_id from dbo.v_PayType with (nolock) where PayType_SysNick = 'oms'", []),
			'KatNasel_id' => $data['KatNasel_id'],
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'OrgRSchet_id' => $data['OrgRSchet_id'],
		];

		if ( !empty($data['Registry_id']) ) {
			$rq = $this->getFirstResultFromQuery("
				select top 1 RegistryQueue_id as \"RegistryQueue_id\" 
				from {$this->scheme}.v_RegistryQueue (nolock)
				where Registry_id = :Registry_id
			", $params);

			if ( $rq !== false && !empty($rq) ) {
				return [['success' => false, 'Error_Msg' => 'Запрос МО по данному реестру уже находится в очереди на формирование.']];
			}
		}

		$rq = $this->getFirstResultFromQuery("
			select top 1
				RegistryQueue_id as \"RegistryQueue_id\" 
			from
				{$this->scheme}.v_RegistryQueue (nolock)
			where
				Lpu_id = :Lpu_id
				and RegistryType_id = :RegistryType_id
				and ISNULL(KatNasel_id, 0) = ISNULL(:KatNasel_id, 0)
				and Registry_begDate <= :Registry_endDate
				and :Registry_begDate <= Registry_endDate
				and (LpuBuilding_id is null or :LpuBuilding_id is null or LpuBuilding_id = :LpuBuilding_id)
				and ISNULL(DispClass_id, 0) = ISNULL(:DispClass_id, 0)
		", $params);

		if ( $rq !== false && !empty($rq) ) {
			return [[ 'success' => false, 'Error_Msg' => 'Реестр с указанными параметрами уже формируется. Дождитесь окончания формирования реестра.' ]];
		}

		return $this->getFirstRowFromQuery("
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@RegistryQueue_id bigint = null,
				@RegistryQueue_Position bigint = null;

			exec {$this->scheme}.p_RegistryQueue_ins
				@RegistryQueue_id = @RegistryQueue_id output,
				@RegistryQueue_Position = @RegistryQueue_Position output,
				@Registry_id = :Registry_id,
				@RegistryType_id = :RegistryType_id,
				@Lpu_id = :Lpu_id,
				@Registry_begDate = :Registry_begDate,
				@Registry_endDate = :Registry_endDate,
				@Registry_Num = :Registry_Num,
				@Registry_accDate = :Registry_accDate, 
				@RegistryStatus_id = :RegistryStatus_id,
				@DispClass_id = :DispClass_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@PayType_id = :PayType_id,
				@KatNasel_id = :KatNasel_id,
				@OrgRSchet_id = :OrgRSchet_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @RegistryQueue_id as \"RegistryQueue_id\", @RegistryQueue_Position as \"RegistryQueue_Position\", @Error_Code as \"Error_Code\", @Error_Message as \"Error_Msg\";
		", $params);
	}

	/**
	 * comment
	 */
	public function reformRegistry($data) {
		$row = $this->getFirstRowFromQuery("
			select
				r.Registry_id,
				r.Lpu_id,
				r.LpuBuilding_id,
				r.RegistryType_id,
				r.RegistryStatus_id,
				r.DispClass_id,
				convert(varchar,r.Registry_begDate,112) as Registry_begDate,
				convert(varchar,r.Registry_endDate,112) as Registry_endDate,
				r.Registry_Num,
				r.Registry_IsActive,
				r.PayType_id,
				r.KatNasel_id,
				r.OrgRSchet_id,
				convert(varchar,r.Registry_accDate,112) as Registry_accDate,
				rgl.Registry_pid
			from
				{$this->scheme}.v_Registry r with (NOLOCK)
				outer apply (
					select top 1 Registry_pid
					from {$this->scheme}.v_RegistryGroupLink (nolock)
					where Registry_id = r.Registry_id
				) rgl
			where
				r.Registry_id = :Registry_id
		", $data);

		if ( $row === false || !is_array($row) || count($row) == 0 ) {
			return [ 'success' => false, 'Error_Msg' => 'Реестр не найден в базе. Возможно, он был удален.' ];
		}

		if ( !empty($row['Registry_pid']) ) {
			// Переформировать/удалить предварительный реестр можно только в случае если он (предварительный реестр) не ссылается ни на один объединенный реестр.
			return [ 'Error_Msg' => 'Предварительный реестр входит в объединенный реестр, переформирование невозможно' ];
		}

		$data = array_merge($data, $row);

		return $this->saveRegistryQueue($data);
	}

	/**
	 * @param array $data
	 * @param array $fields
	 * @param bool $unsetSourceField
	 * @return array
	 */
	protected function _makeArrayForXML(&$data = [], $fields = [], $unsetSourceField = true) {
		$dataExists = false;
		$result = [];

		foreach ( $fields as $field ) {
			if ( isset($data[$field]) ) {
				$dataExists = true;
				$result[$field] = $data[$field];
			}
			else {
				$result[$field] = null;
			}

			if ( $unsetSourceField == true && array_key_exists($field, $data) ) {
				unset($data[$field]);
			}
		}

		if ( $dataExists == false ) {
			$result = [];
		}

		return $result;
	}

	/**
	 * Получает ключи
	 */
	protected function _getKeysForRec($rec) {
		$recKeys = [];
		$part = 1;

		foreach($rec as $key => $value) {
			if (strpos($key, 'fields_part_') !== false) {
				$part = intval(str_replace('fields_part_', '', $key));
				continue;
			}
			if (!isset($recKeys[$part])) {
				$recKeys[$part] = [];
			}
			$recKeys[$part][$key] = null;
		}

		return $recKeys;
	}

	/**
	 * Установка статуса реестра
	 */
	public function setRegistryStatus($data) {
		$response = [
			'success' => true,
			'Error_Msg' => '',
			'RegistryStatus_id' => 0,
		];

		try {
			$this->beginTransaction();

			if ( empty($data['Registry_ids']) || empty($data['RegistryStatus_id']) ) {
				throw new Exception('Пустые значения входных параметров');
			}

			$registryStatusList = $this->getAllowedRegistryStatuses();

			if ( !in_array($data['RegistryStatus_id'], $registryStatusList) ) {
				throw new Exception('Недопустимый статус реестра');
			}

			foreach ( $data['Registry_ids'] as $Registry_id ) {
				$data['Registry_id'] = $Registry_id;

				if ($this->checkRegistryInArchive($data)) {
					throw new Exception('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
				}

				if ($data['RegistryStatus_id'] == 3 && $this->checkRegistryIsBlocked($data)) {
					throw new Exception('Реестр заблокирован, запрещено менять статус на "В работе".');
				}


				$r = $this->getFirstRowFromQuery("
					select top 1
						r.RegistryType_id,
						r.RegistryStatus_id,
						rcs.RegistryCheckStatus_Code
					from {$this->scheme}.v_Registry r with (NOLOCK)
						left join dbo.v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
					where r.Registry_id = :Registry_id
				", [
					'Registry_id' => $data['Registry_id']
				]);

				if ($r === false) {
					throw new Exception('Ошибка при получении данных реестра');
				}

				$RegistryCheckStatus_Code = $r['RegistryCheckStatus_Code'];
				$RegistryType_id = $r['RegistryType_id'];
				$RegistryStatus_id = $r['RegistryStatus_id'];

				$data['RegistryType_id'] = $RegistryType_id;

				$this->setRegistryParamsByType($data);

				$fields = "";

				// если перевели в работу, то снимаем признак формирования
				// @task https://jira.is-mis.ru/browse/PROMEDWEB-5854
				// обнуляем сумму, сбрасываем статус
				if ($data['RegistryStatus_id'] == 3) {
					$fields .= "
						Registry_ExportPath = null,
						Registry_xmlExportPath = null,
						Registry_xmlExpDT = null,
						Registry_SumPaid = null,
						RegistryCheckStatus_id = null,
					";
				}

				// если переводим "к оплате" и проверка установлена, и это не суперадмин, то проверяем на ошибки
				if (
					$RegistryStatus_id == 3 && $data['RegistryStatus_id'] == 2
					&& (in_array($RegistryType_id, $this->getAllowedRegistryTypes($data)))
					&& isset($data['session']['setting']['server']['check_registry_exists_errors'])
					&& $data['session']['setting']['server']['check_registry_exists_errors'] == 1
					&& !isSuperadmin()
				) {
					$errCnt = $this->getFirstResultFromQuery("
						select (
							select count(*) as err
							from {$this->scheme}.v_{$this->RegistryErrorObject} RegistryError with (NOLOCK)
								left join {$this->scheme}.v_{$this->RegistryDataObject} rd with (NOLOCK) on rd.Evn_id = RegistryError.Evn_id
								left join RegistryErrorType with (NOLOCK) on RegistryErrorType.RegistryErrorType_id = RegistryError.RegistryErrorType_id
							where RegistryError.Registry_id = :Registry_id
								and rd.Registry_id = :Registry_id
								and RegistryErrorType.RegistryErrorClass_id = 1
								and RegistryError.RegistryErrorClass_id = 1
								and IsNull(rd.RegistryData_deleted,1) = 1
								and rd.Evn_id is not null
						) + (
							select count(*) as err
							from {$this->scheme}.v_{$this->RegistryErrorComObject} RegistryErrorCom with (NOLOCK)
								left join RegistryErrorType with (NOLOCK) on RegistryErrorType.RegistryErrorType_id = RegistryErrorCom.RegistryErrorType_id
							where Registry_id = :Registry_id
								and RegistryErrorType.RegistryErrorClass_id = 1
						) as errCnt
					", [
						'Registry_id' => $data['Registry_id']
					]);

					if ($errCnt !== false && !empty($errCnt)) {
						throw new Exception('Невозможно отметить реестр "К оплате", так как в нем присутствуют ошибки.<br/>Пожалуйста, исправьте ошибки по реестру и повторите операцию.');
					}
				}
				else if ( $RegistryStatus_id == 2 && $data['RegistryStatus_id'] == 4 ) {
					if (2 != $RegistryCheckStatus_Code) {
						throw new Exception('Для перевода реестра в оплаченные его статус должен быть "Проверен ТФОМС"');
					}

					$result = $this->getFirstRowFromQuery("
						declare
							@Error_Code bigint,
							@Error_Message varchar(4000)
						exec {$this->scheme}.p_Registry_setPaid
							@Registry_id = :Registry_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;
						select @Error_Code as Error_Code, @Error_Message as Error_Msg;
					", $data);

					if ($result === false) {
						throw new Exception('Ошибка при отметке оплаты реестра');
					}
					else if (!empty($result['Error_Msg'])) {
						throw new Exception($result['Error_Msg']);
					}

					/*$res = $this->getFirstRowFromQuery("
						declare
							@Error_Code bigint,
							@Error_Message varchar(4000);
		
						exec {$this->scheme}.p_RegistryData_Refresh
							@Registry_id = :Registry_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;
		
						select @Error_Code as Error_Code, @Error_Message as Error_Msg;
					", $data);

					if ($res === false) {
						throw new Exception('Ошибка при пересчете реестра');
					}
					else if (!empty($res['Error_Msg'])) {
						throw new Exception($res['Error_Msg']);
					}*/
				}
				else if ($RegistryStatus_id == 4 && $data['RegistryStatus_id'] != 4) {
					$result = $this->getFirstRowFromQuery("
						declare
							@Error_Code bigint,
							@Error_Message varchar(4000)
						exec {$this->scheme}.p_Registry_setUnPaid
							@Registry_id = :Registry_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;
						select @Error_Code as Error_Code, @Error_Message as Error_Msg;
					", $data);

					if ($result === false) {
						throw new Exception('Ошибка при отмене оплаты реестра');
					}
					else if (!empty($result['Error_Msg'])) {
						throw new Exception($result['Error_Msg']);
					}
				}

				$updateResponse = $this->getFirstRowFromQuery("
					declare
						@Error_Code bigint = 0,
						@Error_Message varchar(4000) = '',
						@RegistryStatus_id bigint =  :RegistryStatus_id
					set nocount on;
					begin try
						update {$this->scheme}.Registry with (rowlock)
						set
							RegistryStatus_id = @RegistryStatus_id,
							Registry_updDT = dbo.tzGetDate(),
							{$fields}
							pmUser_updID = :pmUser_id
						where
							Registry_id = :Registry_id
					end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
					set nocount off
					select @RegistryStatus_id as RegistryStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
				", [
					'Registry_id' => $data['Registry_id'],
					'RegistryStatus_id' => $data['RegistryStatus_id'],
					'pmUser_id' => $data['pmUser_id']
				]);

				if ( $updateResponse === false || !is_array($updateResponse) ) {
					throw new Exception('Ошибка при выполнении запроса к базе данных');
				}
				else if ( !empty($updateResponse['Error_Msg']) ) {
					throw new Exception($updateResponse['Error_Msg']);
				}

				if ( $data['RegistryStatus_id'] == 4 ) {
					// пишем информацию о смене статуса в историю
					$res = $this->dumpRegistryInformation([ 'Registry_id' => $data['Registry_id'] ], 4);

					if ($res === false) {
						throw new Exception('Ошибка при добавлении информации о смене статуса реестра');
					}
					else if (is_array($res) && !empty($res['Error_Msg'])) {
						throw new Exception($res['Error_Msg']);
					}
				}
			}

			$response['RegistryStatus_id'] = $data['RegistryStatus_id'];

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();

			$response['success'] = false;
			$response['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Получение списка случаев реестра
	 */
	public function loadRegistryData($data = []) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		if ( (isset($data['start']) && (isset($data['limit']))) && (!(($data['start'] >= 0) && ($data['limit'] >= 0))) ) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$fieldsList = [];
		$filterList = [
			'R.Registry_id = :Registry_id',
		];
		$joinList = [];
		$params = [
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['session']['lpu_id'],
		];

		if ( !empty($data['Person_SurName']) ) {
			$filterList[] = "RD.Person_SurName like :Person_SurName";
			$params['Person_SurName'] = trim($data['Person_SurName']) . "%";
		}

		if ( !empty($data['Person_FirName']) ) {
			$filterList[] = "RD.Person_FirName like :Person_FirName";
			$params['Person_FirName'] = trim($data['Person_FirName']) . "%";
		}

		if ( !empty($data['Person_SecName']) ) {
			$filterList[] = "RD.Person_SecName like :Person_SecName";
			$params['Person_SecName'] = trim($data['Person_SecName']) . "%";
		}

		if ( !empty($data['Polis_Num']) ) {
			$filterList[] = "RD.Polis_Num = :Polis_Num";
			$params['Polis_Num'] = $data['Polis_Num'];
		}

		if ( !empty($data['Evn_id']) ) {
			$filterList[] = "RD.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if ( !empty($data['MedPersonal_id']) ) {
			$filterList[] = "RD.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		$fieldsList[] = "RD.RegistryData_KdFact as RegistryData_Uet";

		if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$joinList[] = "left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid";
			$fieldsList[] = "epd.DispClass_id";
		}

		if ( !empty($data['RegistryStatus_id']) && (6 == $data['RegistryStatus_id']) ) {
			$source_table = 'v_RegistryDeleted_Data';
		}
		else {
			$source_table = 'v_' . $this->RegistryDataObject;
		}

		$evnVizitPLSetDateField = ($this->RegistryType_id == 7 ? 'Evn_didDate' : 'Evn_setDate');
		$evnVizitPLDisDateField = ($this->RegistryType_id == 2 ? 'EvnPL_LastVizitDT' : 'Evn_disDate');

		$query = "
			select
				-- select
				RD.Evn_id,
				RD.Evn_rid,
				cast(RD.RegistryData_RowNum as varchar) + '_' + cast(RD.Evn_id as varchar) as MaxEvn_id,
				RD.EvnClass_id,
				RD.Registry_id,
				RD.RegistryType_id,
				RD.Person_id,
				RD.Server_id,
				PersonEvn.PersonEvn_id,
				" . (count($fieldsList) > 0 ? implode(",", $fieldsList) . "," : "") . "
				RD.RegistryData_deleted,
				RD.NumCard,
				RD.Person_FIO,
				convert(varchar(10), RD.Person_BirthDay, 104) as Person_BirthDay,
				CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				RD.LpuSection_name as LpuSection_Name,
				RD.MedPersonal_Fio,
				convert(varchar(10), RD.{$evnVizitPLSetDateField}, 104) as EvnVizitPL_setDate,
				convert(varchar(10), RD.{$evnVizitPLDisDateField}, 104) as Evn_disDate,
				RD.RegistryData_Tariff,
				RD.RegistryData_ItogSum as RegistryData_Sum,
				RegistryError.Err_Count as Err_Count,
				RegistryErrorTFOMS.ErrTfoms_Count as ErrTfoms_Count
				-- end select
			from
				-- from
				{$this->scheme}.v_Registry R with (NOLOCK)
				inner join {$this->scheme}.{$source_table} RD with (NOLOCK) on RD.Registry_id = R.Registry_id
				left join v_Evn e (nolock) on e.Evn_id = rd.Evn_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = RD.LpuSection_id
				" . implode(" ", $joinList) . "
				outer apply (
					select top 1 RE.Evn_id as Err_Count
					from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK)
					where RD.Evn_id = RE.Evn_id
						and RD.Registry_id = RE.Registry_id
				) RegistryError
				outer apply (
					select top 1 RET.RegistryErrorTFOMS_id as ErrTfoms_Count
					from {$this->scheme}.v_RegistryErrorTFOMS RET with (nolock)
					where RET.Evn_id = RD.Evn_id
						and RET.Registry_id = RD.Registry_id
						and ISNULL(RET.RegistryErrorTfomsLevel_id, 1) = 1
				) RegistryErrorTFOMS
				outer apply (
					select top 1 PersonEvn_id
					from v_PersonEvn PE with (NOLOCK)
					where RD.Person_id = PE.Person_id
						and PE.PersonEvn_insDT <= isnull(RD.{$evnVizitPLDisDateField}, RD.{$evnVizitPLSetDateField})
					order by PersonEvn_insDT desc
				) PersonEvn
			-- end from
			where
				-- where
				" . implode(" and ", $filterList) . "
				-- end where
			order by
				-- order by
				RD.Person_FIO
				-- end order by
		";

		if ( !empty($data['nopaging']) ) {
			return $this->queryResult($query, $params);
		}

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Сохранение Registry_EvnNum
	 */
	protected function _saveRegistryEvnNum($fileName = null) {
		if ( empty($fileName) ) {
			return false;
		}

		$toWrite = [];

		foreach ( $this->registryEvnNum as $key => $record ) {
			$toWrite[$key] = $record;

			if ( count($toWrite) == 1000 ) {
				$str = json_encode($toWrite) . PHP_EOL;
				@file_put_contents($fileName, $str, FILE_APPEND);
				$toWrite = [];
			}
		}

		if ( count($toWrite) > 0 ) {
			$str = json_encode($toWrite) . PHP_EOL;
			file_put_contents($fileName, $str, FILE_APPEND);
		}

		return true;
	}

	/**
	 * Получение списка результатов ФЛК/МЭК
	 */
	public function loadRegistryErrorTFOMS($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		if ( !(($data['start'] >= 0) && ($data['limit'] >= 0)) ) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$fieldsList = [];
		$filterList = [
			'RET.Registry_id = :Registry_id'
		];
		$joinList = [];
		$params = [
			'Registry_id' => $data['Registry_id']
		];

		if ( isset($data['RegistryErrorTfomsType_Code']) ) {
			$filterList[] = "RETT.RegistryErrorTfomsType_Code = :RegistryErrorTfomsType_Code";
			$params['RegistryErrorTfomsType_Code'] = $data['RegistryErrorTfomsType_Code'];
		}

		if ( isset($data['RegistryErrorTfomsClass_id']) ) {
			$filterList[] = "RETT.RegistryErrorTfomsClass_id = :RegistryErrorTfomsClass_id";
			$params['RegistryErrorTfomsClass_id'] = $data['RegistryErrorTfomsClass_id'];
		}

		if ( !empty($data['Person_FIO']) ) 	{
			$filterList[] = "rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) like :Person_FIO";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}

		if ( !empty($data['Evn_id']) ) {
			$filterList[] = "RET.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if ( !empty($data['MedPersonal_id']) ) {
			$filterList[] = "RD.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if ( in_array($this->RegistryType_id, [ 7, 9 ]) ) {
			$joinList[] = "left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid";
			$fieldsList[] = "epd.DispClass_id";
		}

		if ( $this->RegistryType_id == 6 ) {
			$evn_object = 'CmpCallCard';
			$fieldsList[] = "null as Evn_rid";
			$fieldsList[] = "null as EvnClass_id";
		}
		else {
			$evn_object = 'Evn';
			$fieldsList[] = "Evn.Evn_rid";
			$fieldsList[] = "Evn.EvnClass_id";
		}

		$query = "
			select
				-- select
				RET.RegistryErrorTFOMS_id,
				RET.Registry_id,
				cast(RET.RegistryErrorTfomsLevel_id as varchar) + '_' + cast(RET.Evn_id as varchar) as MaxEvn_id,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist,
				ps.Server_id,
				ps.PersonEvn_id,
				RET.Evn_id,
				ps.Person_id,
				RD.NumCard,
				RETT.RegistryErrorTfomsType_Code,
				RETL.RegistryErrorTFOMSLevel_Name,
				RETT.RegistryErrorTfomsType_Name,
				RETT.RegistryErrorTfomsType_Descr,
				RET.RegistryErrorTFOMS_FieldName,
				rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				RD.LpuSection_name as LpuSection_Name,
				RD.MedPersonal_Fio,
				convert(varchar(10), RD.Evn_setDate, 104) as Evn_setDate,
				convert(varchar(10), RD.Evn_disDate, 104) as Evn_disDate,
				" . implode(",", $fieldsList) . "
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTFOMS as RET with (nolock)
				left join {$this->scheme}.v_{$this->RegistryDataObject} as RD on RD.Registry_id = RET.Registry_id
					and RD.Evn_id = RET.Evn_id
				left join dbo.v_{$evn_object} as Evn on Evn.{$evn_object}_id = RET.Evn_id
				left join dbo.v_Person_bdz as ps on ps.PersonEvn_id = RD.PersonEvn_id
					and ps.Server_id = RD.Server_id
				left join dbo.RegistryErrorTfomsLevel as RETL on RETL.RegistryErrorTfomsLevel_id = RET.RegistryErrorTFOMSLevel_id
				left join {$this->scheme}.RegistryErrorTfomsType as RETT on RETT.RegistryErrorTfomsType_id = RET.RegistryErrorTfomsType_id
				" . implode(" ", $joinList) . "
				-- end from
			where
				-- where
				" . implode(" and ", $filterList) . "
				-- end where
			order by
				-- order by
				RETT.RegistryErrorTfomsType_Code
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Получение списка результатов проверки по БДЗ
	 */
	public function loadRegistryErrorTfomsBDZ($data = []) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		if ( !(($data['start'] >= 0) && ($data['limit'] >= 0)) ) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$fieldsList = [];
		$filterList = [
			'RETB.Registry_id = :Registry_id'
		];
		$joinList = [];
		$params = [
			'Registry_id' => $data['Registry_id']
		];

		if ( isset($data['RegistryErrorTfomsType_Code']) ) {
			$filterList[] = "RETT.RegistryErrorTfomsType_Code = :RegistryErrorTfomsType_Code";
			$params['RegistryErrorTfomsType_Code'] = $data['RegistryErrorTfomsType_Code'];
		}

		if ( !empty($data['Person_FIO']) ) 	{
			$filterList[] = "rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) like :Person_FIO";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}

		if ( !empty($data['Evn_id']) ) {
			$filterList[] = "RETB.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if ( in_array($this->RegistryType_id, [ 7, 9 ]) ) {
			$joinList[] = "left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid";
			$fieldsList[] = "epd.DispClass_id";
		}

		if ( $this->RegistryType_id == 6 ) {
			$evn_object = 'CmpCallCard';
			$fieldsList[] = "null as Evn_rid";
			$fieldsList[] = "null as EvnClass_id";
		}
		else {
			$evn_object = 'Evn';
			$fieldsList[] = "Evn.Evn_rid";
			$fieldsList[] = "Evn.EvnClass_id";
		}

		$query = "
			select
				-- select
				RETB.RegistryErrorTfomsBDZ_id,
				RETB.Registry_id,
				RETB.Evn_id,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id is not null then 1 else 2 end as RegistryData_notexist,
				ps.Server_id,
				ps.PersonEvn_id,
				ps.Person_id,
				rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				RETT.RegistryErrorTfomsType_Code,
				RETT.RegistryErrorTfomsType_Descr,
				'<div>Серия: ' + ISNULL(RETB.Polis_Ser, '') + '</div>'
					+ '<div>Номер: ' + ISNULL(RETB.Polis_Num, '') + '</div>'
				as Person_Polis,
				convert(varchar(10), RETB.Polis_begDate, 104) as Polis_begDate,
				convert(varchar(10), RETB.Polis_endDate, 104) as Polis_endDate,
				case when OS.OrgSMO_id is not null then '<div>' + OS.OrgSMO_Nick + ' (' + OS.Orgsmo_f002smocod + ')</div>' else '' end
					+ case when KLArea.KLArea_id is not null then '<div>' + KLArea.KLArea_FullName + '</div>' else '' end
				as OrgSMO_Data,
				L.Lpu_Nick as LpuAttach_Name,
				RETB.RegistryErrorTfomsBDZ_Comment,
				" . implode(",", $fieldsList) . "
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTfomsBDZ as RETB with (nolock)
				left join {$this->scheme}.v_{$this->RegistryDataObject} as RD on RD.Registry_id = RETB.Registry_id
					and RD.Evn_id = RETB.Evn_id
				left join dbo.v_{$evn_object} as Evn on Evn.{$evn_object}_id = RETB.Evn_id
				left join dbo.v_Person_bdz as ps on ps.PersonEvn_id = RD.PersonEvn_id
					and ps.Server_id = RD.Server_id
				left join {$this->scheme}.RegistryErrorTfomsType as RETT on RETT.RegistryErrorTfomsType_id = RETB.RegistryErrorTfomsType_id
				left join v_OrgSmo as OS on OS.OrgSMO_id = RETB.OrgSMO_id
				left join v_KLArea as KLArea on KLArea.KLArea_id = OS.KLRgn_id
				left join v_Lpu as L on L.Lpu_id = RETB.Lpu_id
				" . implode(" ", $joinList) . "
				-- end from
			where
				-- where
				" . implode(" and ", $filterList) . "
				-- end where
			order by
				-- order by
				RETT.RegistryErrorTfomsType_Code
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * @param array $data
	 * @descr Импорт реестра из ТФОМС
	 * @return array
	 */
	public function importRegistryFromTFOMS($data = []) {
		$response = [
			'Error_Code' => '',
			'Error_Msg' => '',
			'recBDZAll' => -1,
			'recTFOMSErr' => -1,
			'recTFOMSRej' => -1,
			'recTFOMSWarn' => -1,
			'success' => true,
		];

		try {
			$this->beginTransaction();

			$this->textlog->add('Запуск', 'INFO');

			if ( !isset($_FILES['RegistryFile']) ) {
				throw new Exception('Не выбран файл реестра!', __LINE__);
			}

			$allowed_types = explode('|','zip');
			$upload_path = './' . IMPORTPATH_ROOT . $_SESSION['lpu_id'] . '/';

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
				throw new Exception('Выбранный для импорта файл не является ZIP-архивом', __LINE__);
			}

			// Правильно ли указана директория для загрузки?
			if ( !@is_dir($upload_path) ) {
				mkdir($upload_path);
			}

			if ( !@is_dir($upload_path) ) {
				throw new Exception('Путь для загрузки файлов некорректен.', __LINE__);
			}

			// Имеет ли директория для загрузки права на запись?
			if ( !is_writable($upload_path) ) {
				throw new Exception('Загрузка файла невозможна из-за прав пользователя.', __LINE__);
			}

			// получаем данные реестра
			$registryData = $this->_loadRegistryForImport($data);

			if ( !is_array($registryData) || count($registryData) == 0 ) {
				throw new Exception('Ошибка чтения данных реестра', __LINE__);
			}

			$this->textlog->add('Получили данные реестра', 'INFO');

			if ( !isset($this->_registryTypeList[$registryData['RegistryType_id']]['exportParams']) ) {
				throw new Exception('Импорт данного типа реестров не поддерживается', __LINE__);
			}

			$baseFileName = 'T24M' . $registryData['Lpu_f003mcod'] . '_' . $registryData['Registry_endMonth'] . $registryData['Registry_PackNum'];

			if (
				strtolower($this->_registryTypeList[$registryData['RegistryType_id']]['exportParams']['data']['code'] . $baseFileName . '.zip')
				!= strtolower(substr($_FILES["RegistryFile"]["name"], -(strlen($baseFileName) + 4) - strlen($this->_registryTypeList[$registryData['RegistryType_id']]['exportParams']['data']['code'])))
			) {
				throw new Exception('Импортируемый архив не соответствует выбранному реестру', __LINE__);
			}

			$this->setRegistryParamsByType($registryData);

			$XmlFiles = [
				'H' => '',
				'L' => '',
			];

			$zip = new ZipArchive();

			if ( $zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE ) {
				for ( $i = 0; $i < $zip->numFiles; $i++ ) {
					$filename = $zip->getNameIndex($i);

					if ( strtolower($this->_registryTypeList[$registryData['RegistryType_id']]['exportParams']['data']['code'] . $baseFileName . '.xml') == strtolower($filename) ) {
						$XmlFiles['H'] = $filename;
					}
					else if ( strtolower($this->_registryTypeList[$registryData['RegistryType_id']]['exportParams']['pers']['code'] . $baseFileName . '.xml') == strtolower($filename) ) {
						$XmlFiles['L'] = $filename;
					}
				}

				$zip->extractTo($upload_path);
				$zip->close();
			}
			else {
				throw new Exception('Ошибка при открытии архива.', __LINE__);
			}

			unlink($_FILES["RegistryFile"]["tmp_name"]);

			if ( empty($XmlFiles['H']) && empty($XmlFiles['L']) ) {
				throw new Exception('Выбранный для импорта архив не содержит XML-файлов с результатами проверки.', __LINE__);
			}

			libxml_use_internal_errors(true);

			$delResp = $this->_deleteRegistryErrorTFOMS($data['Registry_id']);

			if ( $delResp === false || !is_array($delResp) || count($delResp) == 0 ) {
				throw new Exception('Ошибка при удалении записей результатов проверки ФЛК/МЭК', __LINE__);
			}
			else if ( !empty($delResp['Error_Msg']) ) {
				throw new Exception($delResp['Error_Msg'], __LINE__);
			}

			$delResp = $this->_deleteRegistryErrorTfomsBDZ($data['Registry_id']);

			if ( $delResp === false || !is_array($delResp) || count($delResp) == 0 ) {
				throw new Exception('Ошибка при удалении записей результатов проверки по БДЗ', __LINE__);
			}
			else if ( !empty($delResp['Error_Msg']) ) {
				throw new Exception($delResp['Error_Msg'], __LINE__);
			}

			$RegistryCheckStatus_id = $this->getFirstResultFromQuery("
				select top 1 RegistryCheckStatus_id
				from dbo.v_RegistryCheckStatus with (nolock)
				where RegistryCheckStatus_Code = 2
			", [], true);

			// Файл со случаями
			if ( !empty($XmlFiles['H']) ) {
				$this->textlog->add('Начало обработки файла ' . $XmlFiles['H'], 'INFO');

				$this->_importState['TFOMSErrCnt'] = 0;
				$this->_importState['TFOMSWarnCnt'] = 0;

				$xml = new SimpleXMLElement(file_get_contents($upload_path . $XmlFiles['H']));

				foreach ( libxml_get_errors() as $error ) {
					throw new Exception('Файл не является архивом реестра.', __LINE__);
				}

				libxml_clear_errors();

				if ( !property_exists($xml, 'SCHET') ) {
					throw new Exception('Неверный формат загруженного файла', __LINE__);
				}
				else if ( !property_exists($xml->SCHET, 'ZAP') ) {
					throw new Exception('Неверный формат загруженного файла', __LINE__);
				}

				if (property_exists($xml->SCHET, 'SUMMAP')) {
					$this->_importState['Registry_SumPaid'] = (float)$xml->SCHET->SUMMAP->__toString();
				}

				// Цикл по ZAP
				foreach ( $xml->SCHET->ZAP as $onezap ) {
					if ( !property_exists($onezap, 'SLUCH') ) {
						continue;
					}

					// Цикл по SLUCH
					foreach ( $onezap->SLUCH as $onesluch ) {
						$this->_importState['sluchEvnList'] = [];
						$this->SLUCHErrorList = [];

						// Определение случаев и ошибок для реестов по ДВН, ДДС, ПОВН и МОН
						if (in_array($registryData['RegistryType_id'], [7,9,11,12])) {
							if (!property_exists($onezap, 'ID_ZAP')) {
								continue;
							}

							$ID_ZAP = $onezap->ID_ZAP->__toString();
							$match = [];

							if (!preg_match("/^(\d+)F.+$/", $ID_ZAP, $match)) {
								continue;
							}

							$this->_importState['currentEvnId'] = $match[1];

							// проверяем наличие случая в реестре
							$Evn_id = $this->getFirstResultFromQuery("
								select top 1 Evn_id
								from {$this->scheme}.v_{$this->RegistryDataObject} with (nolock)
								where Evn_id = :Evn_id
									and Registry_id = :Registry_id
							", [
								'Evn_id' => $this->_importState['currentEvnId'],
								'Registry_id' => $data['Registry_id'],
							]);

							if ($Evn_id === false) {
								$this->_importState['currentEvnId'] = null;
							}
							else {
								$this->_importState['sluchEvnList'][] = $match[1];
							}

							if (property_exists($onesluch, 'SANK')) {
								foreach ($onesluch->SANK as $onesank) {
									if (property_exists($onesank, 'S_OSN')) {
										$S_OSN = $onesank->S_OSN->__toString();

										if (empty($S_OSN) || $S_OSN == '0') {
											continue;
										}

										$errorTypeRecord = $this->_getRegistryErrorTfomsType(1, [
											'code' => $S_OSN,
											'class' => 1, // Причина отказа в оплате
										]);

										if (count($errorTypeRecord) > 0) {
											$this->SLUCHErrorList[] = [
												'Evn_id' => $this->_importState['currentEvnId'],
												'RegistryErrorTfomsType_id' => $errorTypeRecord['RegistryErrorTfomsType_id'],
												'RegistryErrorTfomsLevel_id' => null,
												'RegistryErrorTfoms_FieldName' => null,
											];

											if (!empty($this->_importState['currentEvnId']) && !in_array($this->_importState['currentEvnId'], $this->_importState['TFOMSRejArr'])) {
												$this->_importState['TFOMSRejArr'][] = $this->_importState['currentEvnId'];
											}
										}
									}

									if (property_exists($onesank, 'COMENTSL')) {
										$COMENT = $onesank->COMENTSL->__toString();

										if ( !empty($COMENT) ) {
											$this->_processComentTag($COMENT, [ $this->_importState['currentEvnId'] ]);
										}
									}
								}
							}

							if (!empty($this->_importState['currentEvnId'])) {
								$params = [
									'Registry_id' => $data['Registry_id'],
									'Evn_id' => $this->_importState['currentEvnId'],
									'TARIF' => 0,
									'SUMV' => 0,
								];

								if (property_exists($onesluch, 'TARIF')) {
									$TARIF = (float)$onesluch->TARIF->__toString();

									if ( !empty($TARIF) ) {
										$params['TARIF'] = $TARIF;
									}
								}

								if (property_exists($onesluch, 'SUMV')) {
									$SUMV = (float)$onesluch->SUMV->__toString();

									if ( !empty($SUMV) ) {
										$params['SUMV'] = $SUMV;
									}
								}

								if (!empty($params['TARIF']) || !empty($params['SUMV'])) {
									$updResult = $this->_setRegistryDataParams($params);

									if ( $updResult === false || !is_array($updResult) || count($updResult) == 0 ) {
										throw new Exception('Ошибка при обновлении информации о тарифе и сумме для случая', __LINE__);
									}
									else if ( !empty($updResult['Error_Msg']) ) {
										throw new Exception($updResult['Error_Msg'], __LINE__);
									}

									$this->_importState['Registry_Sum'] += $params['SUMV'];
								}
							}
						}
						// END OF Определение случаев и ошибок для реестов по ДВН, ДДС, ПОВН и МОН

						// Определение случаев и ошибок для реестов по поликлинике, стоматологии стационару
						// и параклиническим услугам
						else {
							if ( !property_exists($onesluch, 'SLUCH_MED') ) {
								continue;
							}

							// Цикл по SLUCH_MED в ZAP->SLUCH
							foreach ( $onesluch->SLUCH_MED as $onesluchmed ) {
								if (!property_exists($onesluchmed, 'ID_MED_USL')) {
									continue;
								}

								$ID_MED_USL = $onesluchmed->ID_MED_USL->__toString();
								$match = [];

								if (!preg_match("/^(\d+)F.+$/", $ID_MED_USL, $match)) {
									continue;
								}

								$this->_importState['currentEvnId'] = $match[1];

								// проверяем наличие случая в реестре
								$Evn_id = $this->getFirstResultFromQuery("
									select top 1 Evn_id
									from {$this->scheme}.v_{$this->RegistryDataObject} with (nolock)
									where Evn_id = :Evn_id
										and Registry_id = :Registry_id
								", [
									'Evn_id' => $this->_importState['currentEvnId'],
									'Registry_id' => $data['Registry_id'],
								]);

								if ($Evn_id === false) {
									$this->_importState['currentEvnId'] = null;
								}
								else {
									$this->_importState['sluchEvnList'][] = $match[1];
								}

								if (property_exists($onesluchmed, 'SANK')) {
									foreach ($onesluchmed->SANK as $onesank) {
										if (property_exists($onesank, 'S_OSN')) {
											$S_OSN = $onesank->S_OSN->__toString();

											if (!empty($S_OSN) && $S_OSN != '0') {
												$errorTypeRecord = $this->_getRegistryErrorTfomsType(1, [
													'code' => $S_OSN,
													'class' => 1, // Причина отказа в оплате
												]);

												if (count($errorTypeRecord) > 0) {
													$this->SLUCHErrorList[] = [
														'Evn_id' => $this->_importState['currentEvnId'],
														'RegistryErrorTfomsType_id' => $errorTypeRecord['RegistryErrorTfomsType_id'],
														'RegistryErrorTfomsLevel_id' => null,
														'RegistryErrorTfoms_FieldName' => null,
													];

													if (!empty($this->_importState['currentEvnId']) && !in_array($this->_importState['currentEvnId'], $this->_importState['TFOMSRejArr'])) {
														$this->_importState['TFOMSRejArr'][] = $this->_importState['currentEvnId'];
													}
												}
											}
										}

										if (property_exists($onesank, 'COMENTSL')) {
											$COMENT = $onesank->COMENTSL->__toString();

											if ( !empty($COMENT) ) {
												$this->_processComentTag($COMENT, [ $this->_importState['currentEvnId'] ]);
											}
										}
									}
								}

								if (!empty($this->_importState['currentEvnId'])) {
									$params = [
										'Registry_id' => $data['Registry_id'],
										'Evn_id' => $this->_importState['currentEvnId'],
										'TARIF' => 0,
										'SUMV' => 0,
									];

									if (property_exists($onesluchmed, 'TARIF')) {
										$TARIF = (float)$onesluchmed->TARIF->__toString();

										if ( !empty($TARIF) ) {
											$params['TARIF'] = $TARIF;
										}
									}

									if (property_exists($onesluchmed, 'SUMV')) {
										$SUMV = (float)$onesluchmed->SUMV->__toString();

										if ( !empty($SUMV) ) {
											$params['SUMV'] = $SUMV;
										}
									}

									if (!empty($params['TARIF']) || !empty($params['SUMV'])) {
										$updResult = $this->_setRegistryDataParams($params);

										if ( $updResult === false || !is_array($updResult) || count($updResult) == 0 ) {
											throw new Exception('Ошибка при обновлении информации о тарифе и сумме для случая', __LINE__);
										}
										else if ( !empty($updResult['Error_Msg']) ) {
											throw new Exception($updResult['Error_Msg'], __LINE__);
										}

										$this->_importState['Registry_Sum'] += $params['SUMV'];
									}
								}

								/*if (property_exists($onesluchmed, 'COMENTSM')) {
									$COMENT = $onesluchmed->COMENTSM->__toString();
	
									if ( !empty($COMENT) ) {
										$this->_processComentTag($COMENT, [ $this->_importState['currentEvnId'] ]);
									}
								}*/

								if (property_exists($onesluchmed, 'USL') && in_array($registryData['RegistryType_id'], [1,2,16])) {
									foreach ($onesluchmed->USL as $oneusl) {
										/*if (property_exists($oneusl, 'COMENT_U')) {
											$COMENT = $oneusl->COMENT_U->__toString();
	
											if ( !empty($COMENT) ) {
												$this->_processComentTag($COMENT, [ $this->_importState['currentEvnId'] ]);
											}
										}*/

										$EvnUsluga_id = null;

										$ID_USL = $oneusl->ID_USL->__toString();
										$match = [];

										if (preg_match("/^(\d+)F.+$/", $ID_USL, $match)) {
											$EvnUsluga_id = $match[1];
										}

										if (!empty($EvnUsluga_id) && !empty($this->_importState['currentEvnId'])) {
											$params = [
												'Registry_id' => $data['Registry_id'],
												'Evn_id' => $this->_importState['currentEvnId'],
												'EvnUsluga_id' => $EvnUsluga_id,
												'TARIF' => 0,
												'SUMV_USL' => 0,
											];

											if (property_exists($oneusl, 'TARIF')) {
												$TARIF = (float)$oneusl->TARIF->__toString();

												if ( !empty($TARIF) ) {
													$params['TARIF'] = $TARIF;
												}
											}

											if (property_exists($oneusl, 'SUMV_USL')) {
												$SUMV_USL = (float)$oneusl->SUMV_USL->__toString();

												if ( !empty($SUMV_USL) ) {
													$params['SUMV_USL'] = $SUMV_USL;
												}
											}

											if (!empty($params['TARIF']) || !empty($params['SUMV_USL'])) {
												$updResult = $this->_setRegistryUslugaParams($params);

												if ( $updResult === false || !is_array($updResult) || count($updResult) == 0 ) {
													throw new Exception('Ошибка при обновлении информации о тарифе и сумме для услуги случая', __LINE__);
												}
												else if ( !empty($updResult['Error_Msg']) ) {
													throw new Exception($updResult['Error_Msg'], __LINE__);
												}
											}
										}
									}
								}
							}

							/*if (property_exists($onesluch, 'COMENTL')) {
								$COMENT = $onesluch->COMENTL->__toString();
	
								if ( !empty($COMENT) ) {
									$this->_processComentTag($COMENT, $this->_importState['sluchEvnList']);
								}
							}*/
						}
						// END OF Определение случаев и ошибок для реестов по поликлинике, стоматологии стационару
						// и параклиническим услугам
					}

					foreach ($this->SLUCHErrorList as $error) {
						$insResp = $this->getFirstRowFromQuery("
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
				
							exec {$this->scheme}.p_RegistryErrorTFOMS_ins
								@Registry_id = :Registry_id,
								@Evn_id = :Evn_id,
								@RegistryErrorTfomsType_id = :RegistryErrorTfomsType_id,
								@RegistryErrorTfomsLevel_id = :RegistryErrorTfomsLevel_id,
								@RegistryErrorTfoms_FieldName = :RegistryErrorTfoms_FieldName,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
								 
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						", [
							'Registry_id' => $data['Registry_id'],
							'Evn_id' => $error['Evn_id'],
							'RegistryErrorTfomsType_id' => $error['RegistryErrorTfomsType_id'],
							'RegistryErrorTfomsLevel_id' => $error['RegistryErrorTfomsLevel_id'],
							'RegistryErrorTfoms_FieldName' => $error['RegistryErrorTfoms_FieldName'],
							'pmUser_id' => $data['pmUser_id'],
						]);

						if ( $insResp === false || !is_array($insResp) || count($insResp) == 0 ) {
							throw new Exception('Ошибка при добавлении записи в результаты проверки ФЛК/МЭК', __LINE__);
						}
						else if ( !empty($insResp['Error_Msg']) ) {
							throw new Exception($insResp['Error_Msg'], __LINE__);
						}
					}

					// Обновляем суммы и статус реестра 
					$updResp = $this->getFirstRowFromQuery("
						declare
							@ErrCode int,
							@ErrMsg varchar(400);
			
						set nocount on;
			
						begin try
							update {$this->scheme}.Registry with (updlock)
							set
								Registry_Sum = :Registry_Sum,
								Registry_SumPaid = :Registry_SumPaid,
								RegistryCheckStatus_id = :RegistryCheckStatus_id
							where
								Registry_id = :Registry_id
						end try
						begin catch
							set @ErrCode = error_number();
							set @ErrMsg = error_message();
						end catch
			
						set nocount off;
			
						select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
					", [
						'Registry_id' => $data['Registry_id'],
						'Registry_Sum' => $this->_importState['Registry_Sum'],
						'Registry_SumPaid' => $this->_importState['Registry_SumPaid'],
						'RegistryCheckStatus_id' => $RegistryCheckStatus_id,
					]);

					if ($updResp === false || !is_array($updResp) || count($updResp) == 0) {
						throw new Exception('Ошибка при обновлении статуса и информации о суммах по реестру', __LINE__);
					}
					else if (!empty($updResp['Error_Msg'])) {
						throw new Exception($updResp['Error_Msg'], __LINE__);
					}
				}

				$response['recTFOMSErr'] = $this->_importState['TFOMSErrCnt'];
				$response['recTFOMSRej'] = count($this->_importState['TFOMSRejArr']);
				$response['recTFOMSWarn'] = $this->_importState['TFOMSWarnCnt'];
			}

			// Файл с перс. данными
			if ( !empty($XmlFiles['L']) ) {
				$this->textlog->add('Начало обработки файла ' . $XmlFiles['L'], 'INFO');

				$this->_importState['BDZErrCnt'] = 0;

				$xml = new SimpleXMLElement(file_get_contents($upload_path . $XmlFiles['L']));

				foreach ( libxml_get_errors() as $error ) {
					throw new Exception('Файл не является архивом реестра.', __LINE__);
				}

				libxml_clear_errors();

				if ( !property_exists($xml, 'PERS') ) {
					throw new Exception('Неверный формат загруженного файла', __LINE__);
				}

				// Цикл по PERS
				foreach ( $xml->PERS as $onepers ) {
					if (!property_exists($onepers, 'ID_PAC')) {
						continue;
					}
					else if (!property_exists($onepers, 'OSPR')) {
						continue;
					}

					$ID_PAC = $onepers->ID_PAC->__toString();
					$match = [];
					$notEmptyElementsCnt = 0;

					$RegistryErrorTfomsBDZRecord = [
						'Registry_id' => $data['Registry_id'],
						'RegistryErrorTfomsType_id' => null,
						'Evn_id' => null,
						'Person_id' => null,
						'RegistryErrorTfomsBDZ_Comment' => null,
						'OrgSMO_id' => null,
						'KLArea_id' => null,
						'PolisType_id' => null,
						'Polis_Ser' => null,
						'Polis_Num' => null,
						'Polis_begDate' => null,
						'Polis_endDate' => null,
						'Lpu_id' => null,
						'pmUser_id' => $data['pmUser_id'],
					];

					if (property_exists($onepers->OSPR, 'CODSK_OUR')) {
						$CODSK_OUR = $onepers->OSPR->CODSK_OUR->__toString();

						if (!empty($CODSK_OUR)) {
							$notEmptyElementsCnt++;

							$OrgSMOData = $this->_getOrgSMOData($CODSK_OUR);

							$RegistryErrorTfomsBDZRecord['OrgSMO_id'] = $OrgSMOData['OrgSMO_id'];
							$RegistryErrorTfomsBDZRecord['KLArea_id'] = $OrgSMOData['KLArea_id'];
						}
					}

					if (property_exists($onepers->OSPR, 'DATE_E_OUR')) {
						$DATE_E_OUR = $onepers->OSPR->DATE_E_OUR->__toString();

						if (!empty($DATE_E_OUR)) {
							$notEmptyElementsCnt++;

							if ($DATE_E_OUR == date('Y-m-d', strtotime($DATE_E_OUR))) {
								$RegistryErrorTfomsBDZRecord['Polis_endDate'] = $DATE_E_OUR;
							}
						}
					}

					if (property_exists($onepers->OSPR, 'DATE_N_OUR')) {
						$DATE_N_OUR = $onepers->OSPR->DATE_N_OUR->__toString();

						if (!empty($DATE_N_OUR)) {
							$notEmptyElementsCnt++;

							if ($DATE_N_OUR == date('Y-m-d', strtotime($DATE_N_OUR))) {
								$RegistryErrorTfomsBDZRecord['Polis_begDate'] = $DATE_N_OUR;
							}
						}
					}

					if (property_exists($onepers->OSPR, 'NPOLIS_OUR')) {
						$NPOLIS_OUR = $onepers->OSPR->NPOLIS_OUR->__toString();

						if (!empty($NPOLIS_OUR)) {
							$notEmptyElementsCnt++;
							$RegistryErrorTfomsBDZRecord['Polis_Num'] = $NPOLIS_OUR;
						}
					}

					if (property_exists($onepers->OSPR, 'REASON')) {
						$REASON = $onepers->OSPR->REASON->__toString();

						if (!empty($REASON)) {
							$notEmptyElementsCnt++;
							$RegistryErrorTfomsBDZRecord['RegistryErrorTfomsBDZ_Comment'] = $REASON;
						}
					}

					if (property_exists($onepers->OSPR, 'RESULT')) {
						$RESULT = $onepers->OSPR->RESULT->__toString();

						if (!empty($RESULT)) {
							$notEmptyElementsCnt++;

							$errorTypeRecord = $this->_getRegistryErrorTfomsType(2, [
								'code' => $RESULT,
							]);

							if (count($errorTypeRecord) > 0) {
								$RegistryErrorTfomsBDZRecord['RegistryErrorTfomsType_id'] = $errorTypeRecord['RegistryErrorTfomsType_id'];
							}
						}
					}

					if (property_exists($onepers->OSPR, 'SPOLIS_OUR')) {
						$SPOLIS_OUR = $onepers->OSPR->SPOLIS_OUR->__toString();

						if (!empty($SPOLIS_OUR)) {
							$notEmptyElementsCnt++;
							$RegistryErrorTfomsBDZRecord['Polis_Ser'] = $SPOLIS_OUR;
						}
					}

					if (property_exists($onepers->OSPR, 'VPOLIS_OUR')) {
						$VPOLIS_OUR = $onepers->OSPR->VPOLIS_OUR->__toString();

						if (!empty($VPOLIS_OUR)) {
							$notEmptyElementsCnt++;
							$RegistryErrorTfomsBDZRecord['PolisType_id'] = $this->_getPolisTypeByCodeF008($VPOLIS_OUR);
						}
					}

					if (property_exists($onepers->OSPR, 'CODE_POUR')) {
						$CODE_POUR = $onepers->OSPR->CODE_POUR->__toString();

						if (!empty($CODE_POUR)) {
							$notEmptyElementsCnt++;
							$RegistryErrorTfomsBDZRecord['Lpu_id'] = $this->_getLpuAttachByVolumeCode($CODE_POUR);
						}
					}

					if ($notEmptyElementsCnt == 0) {
						continue;
					}

					// ИД события (Evn_id в таблице типа r24.RegistryData) для искомых случаев
					// совпадает с частью значения тега PERS.ID_PAC слева до символа «E».
					if ( preg_match("/^(\d+)E(\d+)F(\d*)$/", $ID_PAC, $match) ) {
						$RegistryErrorTfomsBDZRecord['Evn_id'] = $match[1];
						$RegistryErrorTfomsBDZRecord['Person_id'] = $match[2];

						// проверяем наличие случая в реестре
						$Evn_id = $this->getFirstResultFromQuery("
							select top 1 Evn_id
							from {$this->scheme}.v_{$this->RegistryDataObject} with (nolock)
							where Evn_id = :Evn_id
								and Registry_id = :Registry_id
						", [
							'Evn_id' => $RegistryErrorTfomsBDZRecord['Evn_id'],
							'Registry_id' => $data['Registry_id'],
						]);

						if ($Evn_id === false) {
							$RegistryErrorTfomsBDZRecord['Evn_id'] = null;
						}
					}

					$insResp = $this->getFirstRowFromQuery("
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
			
						exec {$this->scheme}.p_RegistryErrorTfomsBDZ_ins
							@Evn_id = :Evn_id,
							@Registry_id = :Registry_id,
							@Person_id = :Person_id,
							@RegistryErrorTfomsType_id = :RegistryErrorTfomsType_id,
							@RegistryErrorTfomsBDZ_Comment = :RegistryErrorTfomsBDZ_Comment,
							@KLArea_id = :KLArea_id,
							@OrgSMO_id = :OrgSMO_id,
							@PolisType_id = :PolisType_id,
							@Polis_Ser = :Polis_Ser,
							@Polis_Num = :Polis_Num,
							@Polis_begDate = :Polis_begDate,
							@Polis_endDate = :Polis_endDate,
							@Lpu_id = :Lpu_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
							 
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					", $RegistryErrorTfomsBDZRecord);

					if ( $insResp === false || !is_array($insResp) || count($insResp) == 0 ) {
						throw new Exception('Ошибка при добавлении записи в результаты проверки по БДЗ', __LINE__);
					}
					else if ( !empty($insResp['Error_Msg']) ) {
						throw new Exception($insResp['Error_Msg'], __LINE__);
					}

					$this->_importState['BDZErrCnt']++;
				}

				// Обновляем статус реестра
				$updResp = $this->getFirstRowFromQuery("
					declare
						@ErrCode int,
						@ErrMsg varchar(400);
		
					set nocount on;
		
					begin try
						update {$this->scheme}.Registry with (updlock)
						set RegistryCheckStatus_id = :RegistryCheckStatus_id
						where Registry_id = :Registry_id
					end try
					begin catch
						set @ErrCode = error_number();
						set @ErrMsg = error_message();
					end catch
		
					set nocount off;
		
					select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				", [
					'Registry_id' => $data['Registry_id'],
					'RegistryCheckStatus_id' => $RegistryCheckStatus_id,
				]);

				if ($updResp === false || !is_array($updResp) || count($updResp) == 0) {
					throw new Exception('Ошибка при обновлении статуса реестра', __LINE__);
				}
				else if (!empty($updResp['Error_Msg'])) {
					throw new Exception($updResp['Error_Msg'], __LINE__);
				}

				$response['recBDZAll'] = $this->_importState['BDZErrCnt'];
			}

			$res = $this->dumpRegistryInformation($data, 3);

			if ($res === false) {
				throw new Exception('Ошибка при добавлении информации о смене статуса реестра');
			}
			else if (is_array($res) && !empty($res['Error_Msg'])) {
				throw new Exception($res['Error_Msg']);
			}

			$response['Registry_id'] = $data['Registry_id'];

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();

			$this->textlog->add('Ошибка: ' . $e->getMessage() . ' (строка ' . $e->getCode() . ')', 'ERROR');

			$response['Error_Code'] = $e->getCode();
			$response['Error_Msg'] = $e->getMessage();
			$response['success'] = false;
		}

		return $response;
	}
	/**
	 * @param $data
	 * @return array|bool
	 */
	protected function _loadRegistryForImport($data = []) {
		return $this->getFirstRowFromQuery("
			select
				r.Registry_id,
				r.RegistryType_id,
				r.RegistryStatus_id,
				r.Registry_Num,
				convert(varchar(10), r.Registry_accDate, 104) as Registry_accDate,
				r.Registry_xmlExportPath,
				l.Lpu_f003mcod,
				r.Registry_PackNum,
				SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) as Registry_endMonth,
				kn.KatNasel_SysNick
			from
				{$this->scheme}.v_Registry as r (nolock)
				inner join v_Lpu as l on l.Lpu_id = r.Lpu_id
				left join KatNasel as kn on kn.KatNasel_id = r.KatNasel_id
			where
				r.Registry_id = :Registry_id
		", [
			'Registry_id' => $data['Registry_id']
		]);
	}

	/**
	 * @param $Registry_id int
	 * @return array|bool
	 */
	protected function _deleteRegistryErrorTFOMS($Registry_id = null) {
		if ( empty($Registry_id) ) {
			return false;
		}

		return $this->getFirstRowFromQuery("
			declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '',
				@Registry_id bigint = :Registry_id;

			set nocount on;

			begin try
				delete from {$this->scheme}.RegistryErrorTFOMS with (rowlock) where Registry_id = @Registry_id
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch

			set nocount off;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", [
			'Registry_id' => $Registry_id,
		]);
	}

	/**
	 * @param $Registry_id int
	 * @return array|bool
	 */
	protected function _deleteRegistryErrorTfomsBDZ($Registry_id = null) {
		if ( empty($Registry_id) ) {
			return false;
		}

		return $this->getFirstRowFromQuery("
			declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '',
				@Registry_id bigint = :Registry_id;

			set nocount on;

			begin try
				delete from {$this->scheme}.RegistryErrorTfomsBDZ with (rowlock) where Registry_id = @Registry_id
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch

			set nocount off;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", [
			'Registry_id' => $Registry_id,
		]);
	}

	/**
	 * Получение Registry_EvnNum
	 */
	protected function _setRegistryEvnNum($data = []) {
		if ( empty($data['Registry_xmlExportPath']) ) {
			return 'Не указан путь до выгруженного реестра';
		}

		$filename = basename($data['Registry_xmlExportPath']);
		$evnNumPath = str_replace('/' . $filename, '/evnnum.txt', $data['Registry_xmlExportPath']);

		if ( !file_exists($evnNumPath) ) {
			return 'Не найден файл связок номеров записей в реестре со случаями';
		}

		$fileContents = file_get_contents($evnNumPath);
		$exploded = explode(PHP_EOL, $fileContents);
		$this->registryEvnNum = [];

		foreach ( $exploded as $one ) {
			if ( empty($one) ) {
				continue;
			}

			$unjsoned = json_decode($one, true);

			if ( is_array($unjsoned) ) {
				foreach ( $unjsoned as $key => $value ) {
					$this->registryEvnNum[$key] = $value;
				}
			}
		}

		return true;
	}

	/**
	 *
	 */
	protected function _getRegistryErrorTfomsType($RegistryErrorStageType_id, $filters = []) {
		$result = [];

		if ( count($this->RegistryErrorTfomsType) == 0 ) {
			$resp = $this->queryResult("
				declare @date datetime = dbo.tzGetdate();

				select
					RegistryErrorTfomsType_id,
					RegistryErrorTfomsType_Code,
					RegistryErrorTfomsType_Name,
					RegistryErrorTfomsType_Descr,
					RegistryErrorTfomsType_FieldName,
					RegistryErrorTfomsLevel_id,
					RegistryErrorStageType_id,
					RegistryErrorTfomsClass_id
				from {$this->scheme}.v_RegistryErrorTfomsType with (nolock)
				where (RegistryErrorTfomsType_begDate is null or RegistryErrorTfomsType_begDate <= @date)
					and (RegistryErrorTfomsType_endDate is null or RegistryErrorTfomsType_endDate >= @date)
			", []);

			foreach ( $resp as $row ) {
				if ( !isset($this->RegistryErrorTfomsType[$row['RegistryErrorStageType_id']]) ) {
					$this->RegistryErrorTfomsType[$row['RegistryErrorStageType_id']] = [];
				}

				$this->RegistryErrorTfomsType[$row['RegistryErrorStageType_id']][] = $row;
			}
		}

		if ( isset($this->RegistryErrorTfomsType[$RegistryErrorStageType_id]) ) {
			foreach ( $this->RegistryErrorTfomsType[$RegistryErrorStageType_id] as $row ) {
				if (
					(empty($filters['code']) || $filters['code'] == $row['RegistryErrorTfomsType_Code'])
					&& (empty($filters['fieldName']) || $filters['fieldName'] == $row['RegistryErrorTfomsType_FieldName'])
					&& (empty($filters['class']) || $filters['class'] == $row['RegistryErrorTfomsClass_id'])
				) {
					$result = $row;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * @param null $Orgsmo_f002smocod
	 * @return array|mixed
	 */
	protected function _getOrgSMOData($Orgsmo_f002smocod = null) {
		$response = [
			'OrgSMO_id' => null,
			'KLArea_id' => null,
		];

		if ( empty($Orgsmo_f002smocod) ) {
			return $response;
		}

		if ( !array_key_exists($Orgsmo_f002smocod, $this->OrgSMOList) ) {
			$OrgSMOData = $this->getFirstRowFromQuery("
				select top 1 OrgSMO_id, KLRgn_id as KLArea_id from v_OrgSMO with (nolock) where Orgsmo_f002smocod = :Orgsmo_f002smocod
			", [
				'Orgsmo_f002smocod' => $Orgsmo_f002smocod
			]);

			if ( $OrgSMOData !== false && is_array($OrgSMOData) && count($OrgSMOData) > 0 ) {
				$this->OrgSMOList[$Orgsmo_f002smocod] = $OrgSMOData;
			}
			else {
				$this->OrgSMOList[$Orgsmo_f002smocod] = $response;
			}
		}

		return $this->OrgSMOList[$Orgsmo_f002smocod];
	}

	/**
	 * @param null $PolisType_CodeF008
	 * @return array|mixed
	 */
	protected function _getPolisTypeByCodeF008($PolisType_CodeF008) {
		if ( count($this->PolisTypeList) == 0 ) {
			$queryResult = $this->queryResult("
				select PolisType_id, PolisType_CodeF008 from v_PolisType with (nolock) where PolisType_CodeF008 is not null
			", []);

			if ( $queryResult !== false && is_array($queryResult) && count($queryResult) > 0 ) {
				foreach ( $queryResult as $row ) {
					$this->PolisTypeList[$row['PolisType_CodeF008']] = $row['PolisType_id'];
				}
			}
		}

		return (isset($this->PolisTypeList[$PolisType_CodeF008]) ? $this->PolisTypeList[$PolisType_CodeF008] : null);
	}

	/**
	 * @param null $code
	 * @return array|mixed
	 */
	protected function _getLpuAttachByVolumeCode($code) {
		if ( !array_key_exists($code, $this->LpuAttachList) ) {
			$Lpu_id = $this->getFirstResultFromQuery("
				declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = 'Код МО (рег.)');
				declare @curDate datetime = dbo.tzGetDate();

				SELECT top 1
					Lpu.AttributeValue_ValueIdent as Lpu_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
					) Lpu
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and av.AttributeValue_ValueFloat = :code
					and ISNULL(av.AttributeValue_begDate, @curDate) <= @curDate
					and ISNULL(av.AttributeValue_endDate, @curDate) >= @curDate
			", [
				'code' => $code
			]);

			if ( $Lpu_id !== false && !empty($Lpu_id) ) {
				$this->LpuAttachList[$code] = $Lpu_id;
			}
			else {
				$this->LpuAttachList[$code] = null;
			}
		}

		return $this->LpuAttachList[$code];
	}

	protected function _processComentTag($COMENT = '', $evnList = []) {
		if ( empty($COMENT) || $COMENT == '0' ) {
			return false;
		}

		if (count($evnList) == 0) {
			$evnList[] = null;
		}

		$errorData = explode("$", trim($COMENT, '$'));

		for ( $i = 0; $i < count($errorData); $i = $i + 3 ) {
			if ( empty($errorData[$i]) || empty($errorData[$i+1]) || empty($errorData[$i+2]) ) {
				continue;
			}

			$thirdFieldArray = explode(',', $errorData[$i+2]);

			$errorTypeRecord = $this->_getRegistryErrorTfomsType(1, [
				'code' => $errorData[$i+1], // код ошибки в наборе
				'fieldName' => $thirdFieldArray[0], // имя элемента в наборе
				'class' => 2, // Ошибка
			]);

			if ( count($errorTypeRecord) == 0 ) {
				// Получаем запись с кодом 0
				$errorTypeRecord = $this->_getRegistryErrorTfomsType(1, [
					'code' => '000', // код ошибки в наборе
				]);
			}

			if ( count($errorTypeRecord) > 0 && in_array($errorData[$i], [ 2, 3 ]) ) {
				$errorTypeRecord['RegistryErrorTfomsLevel_id'] = (intval($errorData[$i]) == 2 ? 1 : 2);
			}

			if ( count($errorTypeRecord) == 0 ) {
				continue;
			}

			foreach ( $evnList as $Evn_id ) {
				$this->SLUCHErrorList[] = [
					'Evn_id' => $Evn_id,
					'RegistryErrorTfomsType_id' => $errorTypeRecord['RegistryErrorTfomsType_id'],
					'RegistryErrorTfomsLevel_id' => $errorTypeRecord['RegistryErrorTfomsLevel_id'],
					'RegistryErrorTfoms_FieldName' => $thirdFieldArray[0],
				];

				if (
					!empty($Evn_id)
					&& !in_array($this->_importState['currentEvnId'], $this->_importState['TFOMSRejArr'])
					&& $errorTypeRecord['RegistryErrorTfomsLevel_id'] == 1
				) {
					$this->_importState['TFOMSRejArr'][] = $Evn_id;
				}

				if ( $errorTypeRecord['RegistryErrorTfomsLevel_id'] == 1 ) {
					$this->_importState['TFOMSErrCnt']++;
				}
				else if ( $errorTypeRecord['RegistryErrorTfomsLevel_id'] == 2 ) {
					$this->_importState['TFOMSWarnCnt']++;
				}
			}
		}

		return true;
	}

	/**
	 * @param array $params
	 * @return array
	 * @descr Сохранение значений тарифа и итоговой суммы в случае реестра
	 */
	protected function _setRegistryDataParams($params = []) {
		return $this->queryResult("
			declare
				@ErrCode int,
				@ErrMsg varchar(400);

			set nocount on;

			begin try
				update {$this->scheme}.{$this->RegistryDataObject} with (updlock)
				set
					RegistryData_Tariff = :TARIF,
					RegistryData_ItogSum = :SUMV,
					RegistryData_updDT = dbo.tzGetDate()
				where
					Registry_id = :Registry_id
					and {$this->RegistryDataEvnField} = :Evn_id
			end try
			begin catch
				set @ErrCode = error_number();
				set @ErrMsg = error_message();
			end catch

			set nocount off;

			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		", $params);
	}

	/**
	 * @param array $params
	 * @return array
	 * @descr Сохранение значений тарифа и итоговой суммы для услуги в случае реестра
	 */
	protected function _setRegistryUslugaParams($params = []) {
		return $this->queryResult("
			declare
				@ErrCode int,
				@ErrMsg varchar(400);

			set nocount on;

			begin try
				update {$this->scheme}.{$this->RegistryUslugaObject} with (updlock)
				set
					RegistryUsluga_TARIF = :TARIF,
					RegistryUsluga_SUMV = :SUMV_USL,
					RegistryUsluga_updDT = dbo.tzGetDate()
				where
					Registry_id = :Registry_id
					and {$this->RegistryDataEvnField} = :Evn_id
					and EvnUsluga_id = :EvnUsluga_id
			end try
			begin catch
				set @ErrCode = error_number();
				set @ErrMsg = error_message();
			end catch

			set nocount off;

			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		", $params);
	}

	/**
	 * Получение списка ошибок
	 */
	public function loadRegistryError($data) {
		if (empty($data['Registry_id'])) {
			return false;
		}

		if (empty($data['nopaging'])) {
			if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
				return false;
			}
		}

		$this->setRegistryParamsByType($data);

		$fieldsList = [];
		$filterList = [
			'RE.Registry_id = :Registry_id'
		];
		$joinList = [];
		$params = [
			'Registry_id' => $data['Registry_id']
		];

		if (!empty($data['Person_SurName'])) {
			$filterList[] = "RE.Person_SurName like :Person_SurName";
			$params['Person_SurName'] = $data['Person_SurName']."%";
		}

		if (!empty($data['Person_FirName'])) {
			$filterList[] = "RE.Person_FirName like :Person_FirName";
			$params['Person_FirName'] = $data['Person_FirName']."%";
		}

		if (!empty($data['Person_SecName'])) {
			$filterList[] = "RE.Person_SecName like :Person_SecName";
			$params['Person_SecName'] = $data['Person_SecName']."%";
		}

		if (!empty($data['RegistryErrorType_id'])) {
			$filterList[] = "RE.RegistryErrorType_id = :RegistryErrorType_id";
			$params['RegistryErrorType_id'] = $data['RegistryErrorType_id'];
		}

		if (!empty($data['Evn_id'])) {
			$filterList[] = "RE.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if (!empty($data['MedPersonal_id'])) {
			$filterList[] = "RE.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if ( in_array($this->RegistryType_id, array(7, 9, 12, 17)) ) {
			$joinList[] = "left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = COALESCE(RD.Evn_rid, RE.Evn_rid, RE.Evn_id)";
			$fieldsList[] = "epd.DispClass_id";
		}

		$query = "
			select
				-- select
				ROW_NUMBER() OVER (ORDER by RE.Registry_id, RE.RegistryErrorType_id, RE.Evn_id) as RegistryError_id,
				RE.Evn_id,
				RE.Evn_rid,
				cast(ROW_NUMBER() OVER (ORDER by RE.Registry_id, RE.RegistryErrorType_id, RE.Evn_id) as varchar) + '_' + cast(RE.Evn_id as varchar)  as MaxEvn_id,
				RE.Person_id,
				RE.Server_id,
				RE.PersonEvn_id,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist,
				RE.Registry_id,
				RE.EvnClass_id,
				RE.RegistryErrorType_id,
				RE.RegistryErrorType_Code,
				RE.RegistryErrorClass_id,
				RTrim(RE.RegistryErrorClass_Name) as RegistryErrorClass_Name,
				RTrim(RE.RegistryErrorType_Name) as RegistryErrorType_Name,
				RE.RegistryErrorType_Descr,
				RTrim(RE.Person_FIO) as Person_FIO,
				convert(varchar(10), RE.Person_BirthDay, 104) as Person_BirthDay,
				CASE WHEN RE.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				RTrim(RE.LpuSection_name) as LpuSection_Name,
				MP.Person_Fio as MedPersonal_Fio,
				convert(varchar(10), RE.Evn_setDate, 104) as Evn_setDate,
				convert(varchar(10), RE.Evn_disDate, 104) as Evn_disDate
				" . (count($fieldsList) > 0 ? ',' . implode(',', $fieldsList) : '') . "
				-- end select
			from
				-- from
				{$this->scheme}.v_{$this->RegistryErrorObject} as RE with (NOLOCK)
				left join {$this->scheme}.v_{$this->RegistryDataObject} as RD with (nolock) on RD.Registry_id = RE.Registry_id
					and RD.Evn_id = RE.Evn_id
				outer apply(
					select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = RE.MedPersonal_id
				) MP
				" . (count($joinList) > 0 ? implode(' ', $joinList) : '') . "
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_id
				-- end order by
		";

		if (!empty($data['nopaging'])) {
			return $this->queryResult($query, $params);
		}

		$response = [
			'data' => [],
			'totalCount' => $this->getFirstResultFromQuery(getCountSQLPH($query), $params),
		];

		if ($response['totalCount'] === false) {
			$response['totalCount'] = 0;
		}

		if ($response['totalCount'] > 0) {
			$response['data'] = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		}

		if ($response['data'] === false) {
			$response['data'] = [];
		}

		return $response;
	}
}