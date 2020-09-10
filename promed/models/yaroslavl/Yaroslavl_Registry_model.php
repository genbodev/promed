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
 * @copyright    Copyright (c) 2020 RT MIS Ltd.
 * @author       Stanislav Bykov
 * @version      29.04.2020
 */
require_once(APPPATH.'models/Registry_model.php');

class Yaroslavl_Registry_model extends Registry_model {
	public $scheme = "r76";
	public $region = "yaroslavl";

	private $_registryTypeList = [
		1 => [
			'RegistryType_id' => 1,
			'RegistryType_Name' => 'Стационар',
			'SP_Object' => 'EvnPS',
		],
		2 => [
			'RegistryType_id' => 2,
			'RegistryType_Name' => 'Поликлиника',
			'SP_Object' => 'EvnPL',
		],
		16 => [
			'RegistryType_id' => 16,
			'RegistryType_Name' => 'Стоматология',
			'SP_Object' => 'EvnPLStom',
		],
		6 => [
			'RegistryType_id' => 6,
			'RegistryType_Name' => 'Скорая помощь',
			'SP_Object' => 'SMP',
		],
		7 => [
			'RegistryType_id' => 7,
			'RegistryType_Name' => 'Дисп-ция взр. населения с 2013 года',
			'SP_Object' => 'EvnPLDD13',
		],
		11 => [
			'RegistryType_id' => 11,
			'RegistryType_Name' => 'Проф. осмотры взр. населения',
			'SP_Object' => 'EvnPLProf',
		],
		9 => [
			'RegistryType_id' => 9,
			'RegistryType_Name' => 'Дисп-ция детей-сирот с 2013 года',
			'SP_Object' => 'EvnPLOrp13',
		],
		12 => [
			'RegistryType_id' => 12,
			'RegistryType_Name' => 'Медосмотры несовершеннолетних',
			'SP_Object' => 'EvnPLProfTeen',
		],
		15 => [
			'RegistryType_id' => 15,
			'RegistryType_Name' => 'Параклинические услуги',
			'SP_Object' => 'EvnUslugaPar',
		],
	];

	private $registryErrorTypes = [];
	private $registryEvnNum = [];

	/**
	 * Yaroslavl_Registry_model constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return array
	 * Функция возвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	 */
	public function loadRegistryTypeNode($data) {
		return $this->_registryTypeList;
	}

	/**
	 * @param array $data
	 * @param bool $force
	 * @return bool|void
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
				$this->RegistryVolumePlanView = 'v_RegistryVolumePlan_PS';
				$this->RegistryEvnClass = 'EvnSection';
				break;

			case 2:
			case 16:
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryDataTempObject = 'RegistryData';
				$this->RegistryUslugaObject = 'RegistryUsluga';
				$this->RegistryVolumePlanView = 'v_RegistryVolumePlan';
				$this->RegistryEvnClass = 'EvnVizit';
				break;

			case 6:
				$this->RegistryDataObject = 'RegistryDataCmp';
				$this->RegistryDataTempObject = 'RegistryDataCmp';
				$this->RegistryUslugaObject = 'RegistryUsluga';
				$this->RegistryVolumePlanView = 'v_RegistryVolumePlan';
				$this->RegistryEvnClass = 'CmpCloseCard';
				break;

			case 7:
			case 9:
				$this->RegistryDataObject = 'RegistryDataDisp';
				$this->RegistryDataTempObject = 'RegistryDataTempDisp';
				$this->RegistryErrorObject = 'RegistryErrorDisp';
				$this->RegistryNoPolisObject = 'RegistryDispNoPolis';
				$this->RegistryUslugaObject = 'RegistryUsluga';
				$this->RegistryVolumePlanView = 'v_RegistryVolumePlan_Disp';
				$this->RegistryEvnClass = 'EvnPLDisp';
				break;

			case 11:
			case 12:
				$this->RegistryDataObject = 'RegistryDataProf';
				$this->RegistryDataTempObject = 'RegistryDataTempProf';
				$this->RegistryErrorObject = 'RegistryErrorProf';
				$this->RegistryNoPolisObject = 'RegistryProfNoPolis';
				$this->RegistryVolumePlanView = 'v_RegistryVolumePlan_Disp';
				$this->RegistryEvnClass = 'EvnPLDisp';
				break;

			case 15:
				$this->RegistryDataObject = 'RegistryDataPar';
				$this->RegistryDataTempObject = 'RegistryDataTempPar';
				$this->RegistryErrorObject = 'RegistryErrorPar';
				$this->RegistryNoPolisObject = 'RegistryParNoPolis';
				$this->RegistryUslugaObject = 'RegistryUsluga';
				$this->RegistryVolumePlanView = 'v_RegistryVolumePlan_Par';
				$this->RegistryEvnClass = 'EvnUslugaPar';
				break;

			default:
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryDataTempObject = 'RegistryDataTmp';
				$this->RegistryNoPolisObject = 'RegistryNoPolis';
				$this->RegistryUslugaObject = 'RegistryUsluga';
				$this->RegistryVolumePlanView = 'v_RegistryVolumePlan';
				$this->RegistryEvnClass = 'EvnVizit';
				break;
		}
	}

	/**
	 * @param array $data
	 * @return array
	 * Установка статуса импорта реестра в XML
	 */
	protected function _setXmlExportStatus(array $data = []) {
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
	 * @param array $data
	 * @return array|bool
	 * Получаем состояние реестра в данный момент и тип реестра
	 */
	protected function _getRegistryXmlExport(array $data = []) {
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
					R.Registry_id,
					R.Registry_xmlExportPath,
					R.RegistryType_id,
					R.RegistryStatus_id,
					KN.KatNasel_SysNick,
					null as Registry_PackNum,
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
	 * @param $type
	 * @return string
	 * Возвращает наименование объекта для хранимых процедур в зависимости от типа реестра
	 */
	protected function _getRegistryObjectName(string $type) {
		$result = '';

		if ( array_key_exists($type, $this->_registryTypeList) ) {
			$result = $this->_registryTypeList[$type]['SP_Object'];
		}

		return $result;
	}

	/**
	 * @param $data
	 * @return array|bool|false
	 * Функция возрвращает массив годов, в которых есть реестры
	 */
	public function getYearsList($data) {
		if ( 6 == (int)$data['RegistryStatus_id'] ) {
			// 6 - если запрошены удаленные реестры
			$query = "
				select distinct
					YEAR(Registry_begDate) as reg_year
				from
					{$this->scheme}.v_Registry_deleted with (nolock)
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
					{$this->scheme}.v_Registry with (nolock)
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
	 * @param $data
	 * @return array
	 * Функция формирует файлы в XML формате для выгрузки данных
	 */
	public function exportRegistryToXml(array $data) {
		try {
			$this->load->library('textlog', [
				'file' => 'exportRegistryToXml_' . date('Y-m-d') . '.log',
				'method' => __METHOD__,
			]);

			if (!defined('REGISTRY_CONFIG_PATH')) {
				define('REGISTRY_CONFIG_PATH', 'documents/registry/yaroslavl/');
			}

			if (!defined('REGISTRY_CONFIG_PATH') || !file_exists(REGISTRY_CONFIG_PATH . 'registry_export.xml')) {
				throw new Exception('Не найдена конфигурация экспорта');
			}
			
			set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
			ini_set("max_execution_time", "0");
			ini_set("max_input_time", "0");
			ini_set("default_socket_timeout", "999");

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

			$data['Status'] = '1';
			$this->_setXmlExportStatus($data);

			$data['KatNasel_SysNick'] = $registryData['KatNasel_SysNick'];
			$data['Registry_endMonth'] = $registryData['Registry_endMonth'];
			$data['Registry_PackNum'] = $registryData['Registry_PackNum'];
			$data['RegistryType_id'] = $registryData['RegistryType_id'];

			/*if ( empty($data['Registry_PackNum']) ) {
				$data['Registry_PackNum'] = $this->_setXmlPackNum($data);
			}*/

			$this->textlog->add('Тип реестра ' . $registryData['RegistryType_id'], 'INFO');
			$registryIsUnion = (13 == $registryData['RegistryType_id']);
			$inputParams = [
				'Registry_id' => $data['Registry_id'],
			];
			$simpleRegistryTypes = [];

			$this->load->library('XmlExporter');

			$this->xmlexporter->loadConfig(REGISTRY_CONFIG_PATH . 'registry_export.xml');
			$this->xmlexporter->setInputParams($inputParams);

			if ($this->xmlexporter->isSetDataProvidersConfig('commonDataProviders')) {
				$this->xmlexporter->runDataProviders('commonDataProviders');
			}

			$this->xmlexporter->prepareMasterData()->getError();

			if (!empty($err)) {
				throw new Exception($err);
			}

			if ($registryIsUnion) {
				$simpleRegistryTypes = $this->getUnionRegistryTypes($data['Registry_id']);
			}
			else {
				$simpleRegistryTypes[] = $registryData['RegistryType_id'];
			}

			foreach ($simpleRegistryTypes as $type) {
				$object = $this->_getRegistryObjectName($type);

				if ( !in_array($type, $this->getAllowedRegistryTypes()) || empty($object) ) {
					continue;
				}

				$this->xmlexporter->setInputParam('RegistryType_id', $type);

				if ($this->xmlexporter->isSetDataProvidersConfig('customDataProviders')) {
					$this->xmlexporter->runDataProviders('customDataProviders');
				}

				$err = $this->xmlexporter->prepareSlaveData()->getError();

				if (!empty($err)) {
					throw new Exception($err);
				}
			}

			$this->textlog->add('Сформировали массивы данных', 'INFO');

			$err = $this->xmlexporter->compile()->archive()->getError();

			if (!empty($err)) {
				throw new Exception($err);
			}

			$this->textlog->add('Сформировали файлы и архив', 'INFO');

			$data['Status'] = $this->xmlexporter->getLink();
			$this->_setXmlExportStatus($data);

			// Пишем информацию о выгрузке в историю
			$this->dumpRegistryInformation($data, 2);

			return [ 'success' => true, 'Link' => $this->xmlexporter->getLink() ];
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->_setXmlExportStatus($data);
			$this->textlog->add($e->getMessage(), 'ERROR');
			return [ 'success' => false, 'Error_Msg' => $e->getMessage() ];
		}
	}

	/**
	 * @param array $data
	 * @return int
	 * Получение номера выгружаемого файла реестра в отчетном периоде
	 */
	protected function _setXmlPackNum(array $data) {
		$response = $this->getFirstRowFromQuery("
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
								and RegistryType_id = :RegistryType_id
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
		", $data);

		$packNum = 0;

		if ( is_array($response) && count($response) > 0 && !empty($response['packNum']) ) {
			$packNum = $response['packNum'];
		}

		return $packNum;
	}

	/**
	 * @param array $data
	 * @return array
	 * Устанавливаем/снимаем признак удаления записей реестра
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

			$queryResp = $this->queryResult("
				select Evn_id
				from {$this->scheme}.v_{$this->RegistryDataObject} with (nolock)
				where {$EvnFilter}
					and Registry_id = :Registry_id
			", [
				'Registry_id' => $data['Registry_id']
			]);

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
	 * @param $data
	 * @return array|bool|false
	 */
	public function loadRegistry($data) {
		$filterList = ['R.Lpu_id = :Lpu_id'];
		$params = [ 'Lpu_id' => (isset($data['Lpu_id'])) ? $data['Lpu_id'] : $data['session']['lpu_id'] ];

		$this->setRegistryParamsByType($data);

		if ( !empty($data['Registry_id']) ) {
			$filterList[] = 'R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}

		if ( !empty($data['RegistryType_id']) ) {
			$filterList[] = 'R.RegistryType_id = :RegistryType_id';
			$params['RegistryType_id'] = $data['RegistryType_id'];
		}

		if ( !empty($data['RegistryStatus_id']) ) {
			// если оплаченные или удаленные
			if( 4 == (int)$data['RegistryStatus_id'] || 6 == (int)$data['RegistryStatus_id'] ) {
				if( $data['Registry_accYear'] > 0 ) {
					$filterList[] = 'YEAR(R.Registry_accDate) = :Registry_accYear';
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
					R.OrgSMO_id,
					OS.OrgSMO_Name,
					0 as Registry_Count,
					0 as Registry_ErrorCount,
					0 as RegistryErrorCom_IsData,
					0 as RegistryError_IsData,
					0 as RegistryNoPolis_IsData,
					0 as RegistryDataBadVol_IsData,
					0 as RegistryErrorBDZ_IsData,
					0 as RegistryErrorTFOMS_IsData,
					0 as Registry_Sum,
					0 as Registry_SumPaid,
					1 as Registry_IsProgress,
					1 as Registry_IsNeedReform,
					'' as Registry_updDate
				from {$this->scheme}.v_RegistryQueue R with (NOLOCK)
					left join v_DispClass DC with (NOLOCK) on DC.DispClass_id = R.DispClass_id 
					left join v_PayType PT with (NOLOCK) on PT.PayType_id = R.PayType_id
					left join v_KatNasel KN with (NOLOCK) on KN.KatNasel_id = R.KatNasel_id
					left join v_OrgSMO OS with (NOLOCK) on OS.OrgSMO_id = R.OrgSMO_id
				where " . implode(' and ', $filterList) . "
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
					$filterList[] = 'R.RegistryStatus_id = :RegistryStatus_id';
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
					R.OrgSMO_id,
					OS.OrgSMO_Name,
					isnull(R.Registry_RecordCount, 0) as Registry_Count,
					isnull(R.Registry_ErrorCount, 0) as Registry_ErrorCount,
					RegistryErrorCom.RegistryErrorCom_IsData,
					RegistryError.RegistryError_IsData,
					RegistryNoPolis.RegistryNoPolis_IsData,
					RegistryDataBadVol.RegistryDataBadVol_IsData,
					RegistryErrorBDZ.RegistryErrorBDZ_IsData,
					RegistryErrorTFOMS.RegistryErrorTFOMS_IsData,
					isnull(R.Registry_Sum, 0.00) as Registry_Sum,
					isnull(R.Registry_SumPaid, 0.00) as Registry_SumPaid,
					case when RQ.RegistryQueue_id is not null then 1 else 0 end as Registry_IsProgress,
					convert(varchar(10), R.Registry_updDT, 104) + ' ' + convert(varchar(8), R.Registry_updDT, 108) as Registry_updDate,
					convert(varchar, RQH.RegistryQueueHistory_endDT, 104) + ' ' + convert(varchar, RQH.RegistryQueueHistory_endDT, 108) as ReformTime,
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
					left join v_OrgSMO OS with (NOLOCK) on OS.OrgSMO_id = R.OrgSMO_id
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
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryDataBadVol_IsData from {$this->scheme}.{$this->RegistryVolumePlanView} RE with (NOLOCK) where RE.Registry_id = R.Registry_id and isnull(RegistryVolumePlan_isExcess,1) = 2) RegistryDataBadVol
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorBDZ_IsData from {$this->scheme}.v_RegistryErrorTFOMS RE with (NOLOCK) where RE.Registry_id = R.Registry_id and RE.RegistryErrorTfomsType_id = 2) RegistryErrorBDZ
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData from {$this->scheme}.v_RegistryErrorTFOMS RE with (NOLOCK) where RE.Registry_id = R.Registry_id and RE.RegistryErrorTfomsType_id in (1, 3)) RegistryErrorTFOMS
				where 
					" . implode(' and ', $filterList) . "
				order by
					R.Registry_endDate DESC,
					RQH.RegistryQueueHistory_endDT DESC
			";
		}

		return $this->queryResult($query, $params);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * Установка реестра в очередь на формирование
	 * Возвращает номер в очереди
	 */
	public function saveRegistryQueue($data) {
		if ( !in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) ) {
			return [[ 'success' => false, 'Error_Msg' => 'Данный функционал недоступен!' ]];
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
			'PayType_id' => $data['PayType_id'],
			'KatNasel_id' => $data['KatNasel_id'],
			'OrgSMO_id' => $data['OrgSMO_id'],
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

			$rgl = $this->getFirstResultFromQuery("
				select top 1 Registry_pid as \"Registry_pid\"
				from {$this->scheme}.v_RegistryGroupLink (nolock)
				where Registry_id = :Registry_id
			", $params);

			if ( $rgl !== false && !empty($rgl) ) {
				return [['success' => false, 'Error_Msg' => 'Предварительный реестр входит в объединенный реестр, переформирование невозможно']];
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
				and ISNULL(PayType_id, 0) = ISNULL(:PayType_id, 0)
				and ISNULL(KatNasel_id, 0) = ISNULL(:KatNasel_id, 0)
				and ISNULL(OrgSMO_id, 0) = ISNULL(:OrgSMO_id, 0)
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
				@OrgSMO_id = :OrgSMO_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @RegistryQueue_id as \"RegistryQueue_id\", @RegistryQueue_Position as \"RegistryQueue_Position\", @Error_Code as \"Error_Code\", @Error_Message as \"Error_Msg\";
		", $params);
	}

	/**
	 * @param $data
	 * @return array|bool
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
				r.OrgSMO_id,
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
	 * @param $data
	 * @return array
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
						RegistryType_id,
						RegistryStatus_id
					from {$this->scheme}.v_Registry with (NOLOCK)
					where Registry_id = :Registry_id
				", [
					'Registry_id' => $data['Registry_id']
				]);

				if ($r === false) {
					throw new Exception('Ошибка при получении данных реестра');
				}

				$RegistryType_id = $r['RegistryType_id'];
				$RegistryStatus_id = $r['RegistryStatus_id'];

				$data['RegistryType_id'] = $RegistryType_id;

				$this->setRegistryParamsByType($data);

				$fields = "";

				// если перевели в работу, то снимаем признак формирования
				if ($data['RegistryStatus_id'] == 3) {
					$fields .= "Registry_ExportPath = null, Registry_xmlExportPath = null, Registry_xmlExpDT = null, ";
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
							from {$this->scheme}.v_{$this->RegistryErrorObject} as RE with (NOLOCK)
								left join {$this->scheme}.v_{$this->RegistryDataObject} as RD on RD.Evn_id = RE.Evn_id
									and RD.Registry_id = RE.Registry_id
								left join {$this->scheme}.v_RegistryErrorType as RET on RET.RegistryErrorType_id = RE.RegistryErrorType_id
							where RE.Registry_id = :Registry_id
								and RET.RegistryErrorClass_id = 1
								and RET.RegistryErrorClass_id = 1
								and ISNULL(RD.RegistryData_deleted, 1) = 1
								and RD.Evn_id is not null
						) + (
							select count(*) as err
							from {$this->scheme}.v_{$this->RegistryErrorComObject} as REC with (NOLOCK)
								left join dbo.v_RegistryErrorType as RET on RET.RegistryErrorType_id = REC.RegistryErrorType_id
							where REC.Registry_id = :Registry_id
								and RET.RegistryErrorClass_id = 1
						) as errCnt
					", [
						'Registry_id' => $data['Registry_id']
					]);

					if ($errCnt !== false && !empty($errCnt)) {
						throw new Exception('Невозможно отметить реестр "К оплате", так как в нем присутствуют ошибки.<br/>Пожалуйста, исправьте ошибки по реестру и повторите операцию.');
					}
				}
				else if ( $RegistryStatus_id == 2 && $data['RegistryStatus_id'] == 4 ) {
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
				}
				else if ( $RegistryStatus_id == 2 && $data['RegistryStatus_id'] == 3 ) {
					$Registry_Num = $this->getFirstResultFromQuery("
						select top 1 R.Registry_Num as \"Registry_Num\"
						from {$this->scheme}.v_RegistryGroupLink as RGL with (nolock)
							inner join {$this->scheme}.v_Registry as R with (nolock) on R.Registry_id = RGL.Registry_pid
						where RGL.Registry_id = :Registry_id
					", [
						'Registry_id' => $data['Registry_id']
					], true);

					if (!empty($Registry_Num)) {
						throw new Exception('Реестр входит в объединённый реестр <b>' . $Registry_Num . '</b>. Для работы с предварительным реестром необходимо удалить объединённый.');
					}
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
	 * @param array $data
	 * @return array|bool|false
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

		if ( in_array($data['RegistryType_id'], [ 7, 9, 12 ]) ) {
			$joinList[] = "left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid";
			$fieldsList[] = "epd.DispClass_id";
		}

		if ( !empty($data['RegistryStatus_id']) && (6 == $data['RegistryStatus_id']) ) {
			$source_table = 'v_RegistryDeleted_Data';
		}
		else {
			$source_table = 'v_' . $this->RegistryDataObject;
		}

		$evnVizitPLSetDateField = 'Evn_setDate';
		$evnVizitPLDisDateField = ($this->RegistryType_id == 2 || $this->RegistryType_id == 16 ? 'EvnPL_LastVizitDT' : 'Evn_disDate');

		$query = "
			select
				-- select
				RD.Evn_id,
				RD.Evn_rid,
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
				convert(varchar(10), RD.{$evnVizitPLSetDateField}, 104) as Evn_setDate,
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
	 * @param array $data
	 * @return array|bool
	 * Получение ошибок перс. даных
	 */
	public function loadRegistryErrorBDZ($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		if ( !(($data['start'] >= 0) && ($data['limit'] >= 0)) ) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$filterList = [
			'RETFOMS.Registry_id = :Registry_id',
			'RETFOMS.RegistryErrorTfomsType_id = 2',
		];
		$params = [
			'Registry_id' => $data['Registry_id'],
		];

		$query = "
			select
				-- select
				RETFOMS.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
				RETFOMS.Registry_id as \"Registry_id\",
				RETFOMS.Evn_id as \"Evn_id\",
				ISNULL(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
				ISNULL(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\",
				RD.Server_id as \"Server_id\",
				RD.PersonEvn_id as \"PersonEvn_id\",
				RD.Person_id as \"Person_id\",
				RET.RegistryErrorType_Code as \"RegistryErrorType_Code\",
				RET.RegistryErrorType_Name as \"RegistryErrorType_Name\",
				RETFOMS.RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
				RD.Person_SurName as \"Person_SurName\",
				RD.Person_FirName as \"Person_FirName\",
				RD.Person_SecName as \"Person_SecName\"
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTFOMS as RETFOMS with (nolock)
				left join {$this->scheme}.v_{$this->RegistryDataObject} as RD on RD.Registry_id = RETFOMS.Registry_id
					and RD.Evn_id = RETFOMS.Evn_id
				left join {$this->scheme}.v_RegistryErrorType as RET on RET.RegistryErrorType_id = RETFOMS.RegistryErrorType_id
				-- end from
			where
				-- where
				" . implode(" and ", $filterList) . "
				-- end where
			order by
				-- order by
				RET.RegistryErrorType_Code
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * @param array $data
	 * @return array|bool
	 * Получение итогов проверки СМО / ТФОМС
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
			'RETFOMS.Registry_id = :Registry_id',
			'RETFOMS.RegistryErrorTfomsType_id in (1, 3)',
		];
		$joinList = [];
		$params = [
			'Registry_id' => $data['Registry_id'],
		];

		if ( isset($data['RegistryErrorType_Code']) ) {
			$filterList[] = "RET.RegistryErrorType_Code = :RegistryErrorType_Code";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}

		if ( !empty($data['Person_FIO']) ) 	{
			$filterList[] = "rtrim(RD.Person_SurName) + ' ' + rtrim(RD.Person_FirName) + ' ' + rtrim(isnull(RD.Person_SecName, '')) like :Person_FIO";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}

		if ( !empty($data['Evn_id']) ) {
			$filterList[] = "RETFOMS.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if ( in_array($this->RegistryType_id, [ 7, 9 ]) ) {
			$joinList[] = "left join v_EvnPLDisp as epd on epd.EvnPLDisp_id = RD.Evn_rid";
			$fieldsList[] = "epd.DispClass_id as \"DispClass_id\"";
		}

		if ( $this->RegistryType_id == 6 ) {
			$evn_object = 'CmpCallCard';
			$fieldsList[] = "null as \"Evn_rid\"";
			$fieldsList[] = "null as \"EvnClass_id\"";
		}
		else {
			$evn_object = 'Evn';
			$fieldsList[] = "Evn.Evn_rid as \"Evn_rid\"";
			$fieldsList[] = "Evn.EvnClass_id as \"EvnClass_id\"";
		}

		$query = "
			select
				-- select
				RETFOMS.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
				RETFOMS.Registry_id as \"Registry_id\",
				ISNULL(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\",
				RD.Server_id as \"Server_id\",
				RD.PersonEvn_id as \"PersonEvn_id\",
				RD.Person_id as \"Person_id\",
				RETFOMS.Evn_id as \"Evn_id\",
				RET.RegistryErrorType_Code as \"RegistryErrorType_Code\",
				RET.RegistryErrorType_Name as \"RegistryErrorType_Name\",
				RETFOMS.RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
				rtrim(isnull(RD.Person_SurName,'')) + ' ' + rtrim(isnull(RD.Person_FirName,'')) + ' ' + rtrim(isnull(RD.Person_SecName, '')) as \"Person_FIO\",
				convert(varchar(10), RD.Person_BirthDay, 104) as \"Person_BirthDay\",
				RD.LpuSection_name as \"LpuSection_Name\",
				RD.MedPersonal_Fio as \"MedPersonal_Fio\",
				null as \"MedSpec_Name\",
				" . implode(",", $fieldsList) . "
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTFOMS as RETFOMS with (nolock)
				left join {$this->scheme}.v_{$this->RegistryDataObject} as RD on RD.Registry_id = RETFOMS.Registry_id
					and RD.Evn_id = RETFOMS.Evn_id
				left join dbo.v_{$evn_object} as Evn on Evn.{$evn_object}_id = RETFOMS.Evn_id
				left join {$this->scheme}.v_RegistryErrorType as RET on RET.RegistryErrorType_id = RETFOMS.RegistryErrorType_id
				" . implode(" ", $joinList) . "
				-- end from
			where
				-- where
				" . implode(" and ", $filterList) . "
				-- end where
			order by
				-- order by
				RET.RegistryErrorType_Code
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * @param array $data
	 * @return array
	 * Импорт реестра
	 */
	public function importRegistry(array $data = []) {
		$response = [
			'Error_Code' => '',
			'Error_Msg' => '',
			'recErrCnt' => null,
			'recErrEvnCnt' => null,
			'Registry_id' => $data['Registry_id'],
			'result' => '',
			'success' => true,
		];

		$this->load->library('textlog', [
			'file' => 'importRegistry_' . date('Y_m_d') . '.log',
			'method' => __METHOD__,
		]);

		$this->textlog->add('Импорт реестра (Registry_id=' . $data['Registry_id'] . ')', 'INFO');
		$this->textlog->add('Тип импорта: ' . $data['importType'], 'INFO');

		try {
			$this->beginTransaction();

			if (!in_array($data['importType'], ['smo','tfoms'])) {
				throw new Exception('Недопустимый тип импорта', __LINE__);
			}

			if (!isset($_FILES['RegistryFile'])) {
				throw new Exception('Не выбран файл реестра!', __LINE__);
			}

			$allowed_types = explode('|','7z');

			$upload_path_parts = [
				IMPORTPATH_ROOT,
				'registry/',
				time() . '_' . $data['pmUser_id'] . '_' . $data['Registry_id'] . '/'
			];
			$upload_path = '';

			foreach ($upload_path_parts as $dir) {
				$upload_path .= $dir;

				if (!is_dir($upload_path)) {
					mkdir($upload_path);
				}
			} 

			if (!is_uploaded_file($_FILES['RegistryFile']['tmp_name'])) {
				$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];

				switch ($error) {
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

			// Проверяем имя файла
			$file_parts = explode('.', $_FILES['RegistryFile']['name']);

			if (count($file_parts) != 2) {
				throw new Exception('Неверное имя файла', __LINE__);
			}

			// Тип файла разрешен к загрузке?
			if (!in_array(strtolower($file_parts[1]), $allowed_types)) {
				throw new Exception('Выбранный для импорта файл не является 7z-архивом', __LINE__);
			}

			if (!@is_dir($upload_path)) {
				throw new Exception('Путь для загрузки файлов некорректен.', __LINE__);
			}

			// Имеет ли директория для загрузки права на запись?
			if (!is_writable($upload_path)) {
				throw new Exception('Загрузка файла невозможна из-за прав пользователя.', __LINE__);
			}

			// получаем данные реестра
			$registryData = $this->getFirstRowFromQuery("
				select top 1
					r.Registry_xmlExportPath as \"Registry_xmlExportPath\",
					kn.KatNasel_SysNick as \"KatNasel_SysNick\"
				from {$this->scheme}.v_Registry as r with (nolock)
					left join v_KatNasel as kn on kn.KatNasel_id = r.KatNasel_id
				where r.Registry_id = :Registry_id
			", [
				'Registry_id' => $data['Registry_id']
			], true);

			if (empty($registryData['Registry_xmlExportPath'])) {
				throw new Exception('Отсутствует информация об экспорте реестра', __LINE__);
			}

			$exportFile = basename($registryData['Registry_xmlExportPath']);

			$baseFileNamePart = substr($exportFile, 0, strpos($exportFile, '.'));

			if (
				substr($baseFileNamePart, 0, strlen($baseFileNamePart) - 2)
				!= substr($file_parts[0], 0, strlen($baseFileNamePart) - 2)
			) {
				throw new Exception('Неверное имя файла', __LINE__);
			}
			else if (substr($baseFileNamePart, - 2) == substr($file_parts[0], - 2)) {
				throw new Exception('Неверное имя файла', __LINE__);
			}

			$filename = 'bill.xml';
			$zip = new ZipArchive();

			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) !== TRUE) {
				throw new Exception('Ошибка при открытии архива.', __LINE__);
			}
			else if ($zip->numFiles != 1) {
				throw new Exception('Неверное количество файлов в архиве.', __LINE__);
			}
			else if ($zip->getNameIndex(0) != $filename) {
				throw new Exception('Неверное имя файла в архиве.', __LINE__);
			}

			$zip->extractTo($upload_path);
			$zip->close();

			// Грузим справочник типов ошибок
			$res = $this->_loadRegistryErrorTypes();

			if ($res === false) {
				throw new Exception('Отсутствует справочник типов ошибок.', __LINE__);
			}

			// Вытаскиваем Registry_EvnNum
			$err = $this->_setRegistryEvnNum($registryData['Registry_xmlExportPath']);

			if (!empty($err)) {
				throw new Exception($err, __LINE__);
			}

			$recErrCnt = 0;
			$recErrEvnCnt = 0;

			// Удаляем ранее загруженные ошибки
			$delResp = $this->_deleteRegistryErrorTFOMS($data['Registry_id']);

			if ($delResp === false || !is_array($delResp) || count($delResp) == 0) {
				throw new Exception('Ошибка при удалении записей результатов проверки ФЛК/МЭК', __LINE__);
			}
			else if (!empty($delResp['Error_Msg'])) {
				throw new Exception($delResp['Error_Msg'], __LINE__);
			}

			// Обработка файла
			libxml_use_internal_errors(true);

			$xml = new SimpleXMLElement(file_get_contents($upload_path . $filename));

			foreach (libxml_get_errors() as $error) {
				throw new Exception('Файл не является архивом реестра.', __LINE__);
			}

			libxml_clear_errors();

			if (!property_exists($xml, 'body')) {
				throw new Exception('Файл не является архивом реестра.', __LINE__);
			}
			
			$errorList = [];
			$evnsWithError = [];

			// Цикл по bill->patients->patient
			if (
				property_exists($xml->body, 'bill')
				&& property_exists($xml->body->bill, 'patients')
			) {
				foreach ($xml->body->bill->patients->patient as $patient) {
					if (
						!property_exists($patient, 'register_accounts')
						|| !property_exists($patient->register_accounts, 'personal_account')
					) {
						continue;
					}

					$xkey = $patient->register_accounts->personal_account['xkey'];

					if (empty($xkey)) {
						continue;
					}

					$xkey_replaced = str_replace('-', '', $xkey);

					$Evn_id = substr($xkey_replaced, 0, strpos($xkey_replaced, 'F'));

					if (!isset($this->registryEvnNum[$Evn_id])) {
						throw new Exception('Случай ' . $xkey . ' отсутствует в файле экспорта', __LINE__);
					}

					$evnHasError = false;

					if (property_exists($patient, 'an_p') && !empty($patient->an_p['an_p']) && $patient->an_p['an_p'] == '1') {
						$evnHasError = true;

						$errorType = $this->_getRegistryErrorType([
							'code' => 1,
							'num' => '5.1.4.01',
						]);

						if (count($errorType) > 0) {
							$errorList[] = [
								'Evn_id' => $Evn_id,
								'Registry_id' => $this->registryEvnNum[$Evn_id]['r'],
								'RegistryErrorTfomsType_id' => 2,
								'RegistryErrorClass_id' => 1,
								'RegistryErrorType_id' => $errorType['RegistryErrorType_id'],
							];
						}
					}

					if (
						property_exists($patient->register_accounts->personal_account, 'smo')
						&& (
							$registryData['KatNasel_SysNick'] == 'all'
							|| (
								in_array($registryData['KatNasel_SysNick'], ['inog','oblast'])
								&& !empty($patient->register_accounts->personal_account->smo['pay'])
								&& $patient->register_accounts->personal_account->smo['pay'] == '4'
							)
						)
					) {
						// Обработка an_pss
						if (property_exists($patient->register_accounts->personal_account->smo, 'an_pss')) {
							foreach ($patient->register_accounts->personal_account->smo->an_pss->an_ps as $an_ps) {
								if (!isset($an_ps['an_ps'])) {
									continue;
								}

								$evnHasError = true;

								$errorType = $this->_getRegistryErrorType([
									'stage' => 2,
									'code' => $an_ps['an_ps'],
								]);

								if (count($errorType) > 0) {
									$errorList[] = [
										'Evn_id' => $Evn_id,
										'Registry_id' => $this->registryEvnNum[$Evn_id]['r'],
										'RegistryErrorTfomsType_id' => 2,
										'RegistryErrorClass_id' => 1,
										'RegistryErrorType_id' => $errorType['RegistryErrorType_id'],
									];
								}
							}
						}

						// обработка an_mess и an_sums
						if (
							in_array($registryData['KatNasel_SysNick'], ['inog','oblast'])
							&& !empty($patient->register_accounts->personal_account->smo['pay'])
							&& $patient->register_accounts->personal_account->smo['pay'] == '4'
						) {
							// Обработка an_mess
							if (property_exists($patient->register_accounts->personal_account->smo, 'an_mess')) {
								foreach ($patient->register_accounts->personal_account->smo->an_mess->an_mes as $an_mes) {
									if (!isset($an_mes['an_mes'])) {
										continue;
									}

									$evnHasError = true;

									$errorType = $this->_getRegistryErrorType([
										'stage' => 3,
										'code' => $an_mes['an_mes'],
									]);

									if (count($errorType) == 0) {
										$errorType = $this->_getRegistryErrorType([
											'stage' => 3,
											'num' => $an_mes['an_mes'],
										]);
									}

									if (count($errorType) > 0) {
										$errorList[] = [
											'Evn_id' => $Evn_id,
											'Registry_id' => $this->registryEvnNum[$Evn_id]['r'],
											'RegistryErrorTfomsType_id' => 3,
											'RegistryErrorClass_id' => 1,
											'RegistryErrorType_id' => $errorType['RegistryErrorType_id'],
										];
									}
								}
							}

							// Обработка an_sums
							if (property_exists($patient->register_accounts->personal_account->smo, 'an_sums')) {
								foreach ($patient->register_accounts->personal_account->smo->an_sums->an_sum as $an_sum) {
									if (!isset($an_sum['an_sum'])) {
										continue;
									}

									$evnHasError = true;

									$errorType = $this->_getRegistryErrorType([
										'stage' => 3,
										'code' => $an_sum['an_sum'],
									]);

									if (count($errorType) == 0) {
										$errorType = $this->_getRegistryErrorType([
											'stage' => 3,
											'num' => $an_sum['an_sum'],
										]);
									}

									if (count($errorType) > 0) {
										$errorList[] = [
											'Evn_id' => $Evn_id,
											'Registry_id' => $this->registryEvnNum[$Evn_id]['r'],
											'RegistryErrorTfomsType_id' => 3,
											'RegistryErrorClass_id' => 1,
											'RegistryErrorType_id' => $errorType['RegistryErrorType_id'],
										];
									}
								}
							}
						}
					}

					if ($evnHasError === true && !in_array($Evn_id, $evnsWithError)) {
						$evnsWithError[] = $Evn_id;
					}
				}
			}

			// Цикл по flk->register->fact
			if (
				$registryData['KatNasel_SysNick'] == 'all'
				&& property_exists($xml->body, 'flk')
				&& property_exists($xml->body->flk, 'register')
			) {
				foreach ($xml->body->flk->register->fact as $fact) {
					if (/*empty($fact['pat']) ||*/ empty($fact['acc'])) {
						continue;
					}

					$xkey = str_replace('-', '', $fact['acc']);
					$xkey_replaced = str_replace('-', '', $xkey);
					$Evn_id = substr($xkey_replaced, 0, strpos($xkey_replaced, 'F'));

					if (!isset($this->registryEvnNum[$Evn_id])) {
						throw new Exception('Случай ' . $xkey . ' отсутствует в файле экспорта', __LINE__);
					}

					/*if (!empty($fact['man']) && $this->registryEvnNum[$Evn_id]['t'] == 2) {
						$xkey = str_replace('-', '', $fact['man']);
						$xkey_replaced = str_replace('-', '', $xkey);
						$Evn_id = substr($xkey_replaced, 0, strpos($xkey_replaced, 'F'));
					}*/

					$errorType = $this->_getRegistryErrorType([
						'stage' => 1,
						'code' => (string)$fact['test'],
						'name' => (string)$fact['fact'],
						'num' => (string)$fact['defect_code'],
					]);

					if (count($errorType) == 0) {
						$errorType = $this->_saveRegistryErrorType([
							'RegistryErrorType_Code' => (string)$fact['test'],
							'RegistryErrorType_Num' => (string)$fact['defect_code'],
							'RegistryErrorType_Name' => (string)$fact['fact'],
							'RegistryErrorClass_id' => 1,
							'RegistryErrorStageType_id' => 1,
							'pmUser_id' => $data['pmUser_id'],
						]);
					}

					if (count($errorType) > 0) {
						$errorList[] = [
							'Evn_id' => $Evn_id,
							'Registry_id' => $this->registryEvnNum[$Evn_id]['r'],
							'RegistryErrorTfomsType_id' => 1,
							'RegistryErrorClass_id' => 1,
							'RegistryErrorType_id' => $errorType['RegistryErrorType_id'],
						];

						if (!in_array($Evn_id, $evnsWithError)) {
							$evnsWithError[] = $Evn_id;
						}
					}
				}
			}

			// Сливаем данные из $errorList в r76.RegistryErrorTFOMS
			if (count($errorList) > 0) {
				$recErrCnt = count($errorList);
				$recErrEvnCnt = count($evnsWithError);
				$rowNumArray = [];
				$rowNumInsertQuery = "
					declare
						@Error_Code bigint = 0,
						@Error_Message varchar(4000) = '';
		
					set nocount on;
		
					begin try
						insert into {$this->scheme}.RegistryErrorTFOMS (
							Registry_id,
							Evn_id,
							RegistryErrorTfomsType_id,
							RegistryErrorClass_id,
							RegistryErrorType_id,
							pmUser_insID,
							pmUser_updID,
							RegistryErrorTFOMS_insDT,
							RegistryErrorTFOMS_updDT
						)
						values
						{values_array}
					end try
		
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
		
					set nocount off;
		
					select @Error_Code as Error_Code, @Error_Message as Error_Msg
				";

				foreach ($errorList as $record) {
					$rowNumArray[] = $record;

					if (count($rowNumArray) == 100) {
						$rowNumInsertQueryBody = '';

						foreach ($rowNumArray as $row) {
							if (!in_array($row['Evn_id'], $evnsWithError)) {
								$evnsWithError[] = $Evn_id;
							}

							$rowNumInsertQueryBody .= "({$row['Registry_id']}, {$row['Evn_id']}, {$row['RegistryErrorTfomsType_id']}, {$row['RegistryErrorClass_id']}, {$row['RegistryErrorType_id']}, {$data['pmUser_id']}, {$data['pmUser_id']}, getdate(), getdate()),";
						}

						$this->textlog->add("Добавляем 100 записей в {$this->scheme}.RegistryErrorTFOMS...");

						$result = $this->getFirstRowFromQuery(str_replace('{values_array}', trim($rowNumInsertQueryBody, ','), $rowNumInsertQuery), []);

						if ($result === false || !is_array($result) || !empty($result['Error_Msg'])) {
							throw new Exception('Ошибка при выполнении запроса', __LINE__);
						}

						$this->textlog->add("... выполнено");

						unset($rowNumArray);
						$rowNumArray = [];
					}
				}

				if ( count($rowNumArray) > 0 ) {
					$rowNumInsertQueryBody = '';

					foreach ($rowNumArray as $row) {
						if (!in_array($row['Evn_id'], $evnsWithError)) {
							$evnsWithError[] = $Evn_id;
						}

						$rowNumInsertQueryBody .= "({$row['Registry_id']}, {$row['Evn_id']}, {$row['RegistryErrorTfomsType_id']}, {$row['RegistryErrorClass_id']}, {$row['RegistryErrorType_id']}, {$data['pmUser_id']}, {$data['pmUser_id']}, getdate(), getdate()),";
					}

					$this->textlog->add("Добавляем " . count($rowNumArray) . " записей в {$this->scheme}.RegistryErrorTFOMS...");

					$result = $this->getFirstRowFromQuery(str_replace('{values_array}', trim($rowNumInsertQueryBody, ','), $rowNumInsertQuery), []);

					if ( $result === false || !is_array($result) || !empty($result['Error_Msg']) ) {
						throw new Exception('Ошибка при выполнении запроса', __LINE__);
					}

					$this->textlog->add("... выполнено");

					unset($rowNumArray);
				}
			}

			$response['result'] = 'выполнен успешно';
			$response['recErrCnt'] = $recErrCnt;
			$response['recErrEvnCnt'] = $recErrEvnCnt;

			$this->commitTransaction();
		}
		catch (Exception $e) {
			$this->rollbackTransaction();

			$this->textlog->add('Ошибка: ' . $e->getMessage(), 'ERROR');

			$response['Error_Code'] = $e->getCode();
			$response['Error_Msg'] = $e->getMessage();
			$response['result'] = 'не выполнен';
			$response['success'] = false;
		}

		return $response;
	}

	/**
	 * @param string $exportPath
	 * @return string
	 * Получение Registry_EvnNum
	 */
	private function _setRegistryEvnNum(string $exportPath) {
		$filename = basename($exportPath);
		$evnNumPath = str_replace('/' . $filename, '/evnnum.txt', $exportPath);

		if (!file_exists($evnNumPath)) {
			return 'Не найден файл связок номеров записей в реестре со случаями';
		}

		$fileContents = file_get_contents($evnNumPath);
		$exploded = explode(PHP_EOL, $fileContents);
		$this->registryEvnNum = [];

		foreach ($exploded as $one) {
			if (empty($one)) {
				continue;
			}

			$unjsoned = json_decode($one, true);

			if (is_array($unjsoned)) {
				foreach ($unjsoned as $key => $value) {
					$this->registryEvnNum[$key] = $value;
				}
			}
		}

		return '';
	}

	/**
	 * @param $Registry_id int
	 * @return array|bool
	 */
	private function _deleteRegistryErrorTFOMS(int $Registry_id) {
		return $this->getFirstRowFromQuery("
			declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '',
				@Registry_id bigint = :Registry_id;

			set nocount on;

			begin try
				delete from {$this->scheme}.RegistryErrorTFOMS with (rowlock) where Registry_id in (
					select Registry_id
					from {$this->scheme}.v_RegistryGroupLink with (nolock)
					where Registry_pid =  @Registry_id
				)
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
	 * @return bool
	 * Получение справочника типов ошибок
	 */
	private function _loadRegistryErrorTypes() {
		$result = true;

		$this->registryErrorTypes = $this->queryResult("
			select
				RegistryErrorType_id,
				RegistryErrorType_Code,
				RegistryErrorType_Name,
				RegistryErrorType_Descr,
				RegistryErrorClass_id,
				RegistryErrorStageType_id,
				RegistryErrorType_Num
			from {$this->scheme}.v_RegistryErrorType with (nolock)
			where (RegistryErrorType_begDT is null or RegistryErrorType_begDT <= dbo.tzGetdate())
				and (RegistryErrorType_endDT is null or RegistryErrorType_endDT >= dbo.tzGetdate())
		", []);

		if (!is_array($this->registryErrorTypes)) {
			$result = false;
		}

		if (count($this->registryErrorTypes) == 0 ) {
			$result = false;
		}

		return $result;
	}

	/**
	 * @param array $conditions
	 * @return array|mixed
	 * Получение записи из справочника типов ошибок
	 */
	private function _getRegistryErrorType(array $conditions) {
		$result = [];

		foreach ($this->registryErrorTypes as $row) {
			if (
				(empty($conditions['code']) || $conditions['code'] == $row['RegistryErrorType_Code'])
				&& (empty($conditions['num']) || $conditions['num'] == $row['RegistryErrorType_Num'])
				&& (empty($conditions['class']) || $conditions['class'] == $row['RegistryErrorClass_id'])
				&& (empty($conditions['stage']) || $conditions['stage'] == $row['RegistryErrorStageType_id'])
			) {
				$result = $row;
				break;
			}
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @return array
	 * Добавление записи в справочник типов ошибок
	 */
	private function _saveRegistryErrorType(array $data) {
		$result = [];

		$saveResponse = $this->getFirstRowFromQuery("
			declare
				@RegistryErrorType_id bigint,
				@Error_Code bigint,
				@Error_Message varchar(4000);

			exec {$this->scheme}.p_RegistryErrorType_ins
				@RegistryErrorType_id = @RegistryErrorType_id output,
				@RegistryErrorType_Code = :RegistryErrorType_Code,
				@RegistryErrorType_Name = :RegistryErrorType_Name,
				@RegistryErrorType_Descr = :RegistryErrorType_Descr,
				@RegistryErrorType_Num = :RegistryErrorType_Num,
				@RegistryErrorClass_id = :RegistryErrorClass_id,
				@RegistryErrorStageType_id = :RegistryErrorStageType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @RegistryErrorType_id as RegistryErrorType_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", [
			'RegistryErrorType_Code' => $data['RegistryErrorType_Code'],
			'RegistryErrorType_Name' => $data['RegistryErrorType_Name'],
			'RegistryErrorType_Descr' => $data['RegistryErrorType_Name'],
			'RegistryErrorType_Num' => (!empty($data['RegistryErrorType_Num']) ? $data['RegistryErrorType_Num'] : null),
			'RegistryErrorClass_id' => $data['RegistryErrorClass_id'],
			'RegistryErrorStageType_id' => $data['RegistryErrorStageType_id'],
			'pmUser_id' => $data['pmUser_id'],
		]);

		if (is_array($saveResponse) && !empty($saveResponse['RegistryErrorType_id'])) {
			$result = [
				'RegistryErrorType_id' => $saveResponse['RegistryErrorType_id'],
				'RegistryErrorType_Code' => $data['RegistryErrorType_Code'],
				'RegistryErrorType_Name' => $data['RegistryErrorType_Name'],
				'RegistryErrorType_Descr' => $data['RegistryErrorType_Name'],
				'RegistryErrorClass_id' => $data['RegistryErrorClass_id'],
				'RegistryErrorStageType_id' => $data['RegistryErrorStageType_id'],
				'RegistryErrorType_Num' => (!empty($data['RegistryErrorType_Num']) ? $data['RegistryErrorType_Num'] : null),
			];

			$this->registryErrorTypes[] = $result;
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @return array|bool|false
	 * Получение списка ошибок
	 */
	public function loadRegistryError($data = []) {
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

		if ( in_array($this->RegistryType_id, [ 7, 9, 12 ]) ) {
			$joinList[] = "left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = COALESCE(RD.Evn_rid, RE.Evn_rid, RE.Evn_id)";
			$fieldsList[] = "epd.DispClass_id";
		}

		$query = "
			select
				-- select
				ROW_NUMBER() OVER (ORDER by RE.Registry_id, RE.RegistryErrorType_id, RE.Evn_id) as RegistryError_id,
				RE.Evn_id,
				RE.Evn_rid,
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

	/**
	 * @param array $data
	 * @return array
	 * Сохранение объединённого реестра
	 */
	public function saveUnionRegistry(array $data = []) {
		$response = [
			'Error_Msg' => '',
			'Error_Code' => '',
			'Registry_id' => 0,
		];

		try {
			$this->beginTransaction();

			// проверка уникальности номера реестра по МО в одном году
			$registryDoubleByNum = $this->getFirstResultFromQuery("
				select top 1 Registry_id
				from {$this->scheme}.v_Registry (nolock)
				where
					RegistryType_id = 13
					and Lpu_id = :Lpu_id
					and Registry_Num = :Registry_Num
					and year(Registry_accDate) = year(:Registry_accDate)
					and Registry_id <> ISNULL(:Registry_id, 0)
			", [
				'Lpu_id' => $data['Lpu_id'],
				'Registry_accDate' => $data['Registry_accDate'],
				'Registry_id' => $data['Registry_id'],
				'Registry_Num' => $data['Registry_Num'],
			]);

			if ( !empty($registryDoubleByNum) ) {
				throw new Exception('Номер счета не должен повторяться в году');
			}

			// 1. сохраняем объединённый реестр
			$saveResponse = $this->getFirstRowFromQuery("
				declare
					@Error_Code bigint,
					@Error_Message varchar(4000),
					@Registry_id bigint = :Registry_id,
					@curdate datetime = dbo.tzGetDate();
	
				exec {$this->scheme}.p_Registry_" . (!empty($data['Registry_id']) ? "upd" : "ins") . " 
					@Registry_id = @Registry_id output,
					@RegistryType_id = 13,
					@RegistryStatus_id = 1,
					@Registry_Sum = NULL,
					@Registry_IsActive = 2,
					@Registry_Num = :Registry_Num,
					@Registry_accDate = :Registry_accDate,
					@Registry_begDate = :Registry_begDate,
					@Registry_endDate = :Registry_endDate,
					@RegistryGroupType_id = :RegistryGroupType_id,
					@OrgSMO_id = :OrgSMO_id,
					@KatNasel_id = :KatNasel_id,
					@PayType_id = :PayType_id,
					@Lpu_id = :Lpu_id,
					@OrgRSchet_id = :OrgRSchet_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
	
				select @Registry_id as Registry_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			", $data);
			
			if (!is_array($saveResponse) || count($saveResponse) == 0) {
				throw new Exception('Ошибка при выполнении запроса к БД', __LINE__);
			}

			$response['Registry_id'] = $saveResponse['Registry_id'];

			// 2. удаляем все связи
			$this->db->query(
				"delete {$this->scheme}.RegistryGroupLink with (rowlock) where Registry_pid = :Registry_id", 
				[ 'Registry_id' => $response['Registry_id'] ]
			);

			// 3. выполняем поиск реестров, которые войдут в объединённый
			$simpleRegistryList = $this->queryResult("
				select
					R.Registry_id
				from
					{$this->scheme}.v_Registry R with (nolock)
				where
					R.RegistryType_id <> 13
					and R.RegistryStatus_id = 2 -- к оплате
					and R.Lpu_id = :Lpu_id
					and R.Registry_begDate >= :Registry_begDate
					and R.Registry_endDate <= :Registry_endDate
					and R.PayType_id = :PayType_id
					and ISNULL(R.KatNasel_id, 0) = ISNULL(:KatNasel_id, 0)
					and ISNULL(R.OrgSMO_id, 0) = ISNULL(:OrgSMO_id, 0)
			", [
				'KatNasel_id' => $data['KatNasel_id'],
				'Lpu_id' => $data['Lpu_id'],
				'OrgSMO_id' => $data['OrgSMO_id'],
				'PayType_id' => $data['PayType_id'],
				'Registry_begDate' => $data['Registry_begDate'],
				'Registry_endDate' => $data['Registry_endDate'],
			]);

			// 4. сохраняем новые связи
			foreach ($simpleRegistryList as $one_reg) {
				$saveResponse = $this->getFirstRowFromQuery("
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000),
						@RegistryGroupLink_id bigint = null;
					exec {$this->scheme}.p_RegistryGroupLink_ins
						@RegistryGroupLink_id = @RegistryGroupLink_id output,
						@Registry_pid = :Registry_pid,
						@Registry_id = :Registry_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select @RegistryGroupLink_id as RegistryGroupLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				", [
					'Registry_pid' => $response['Registry_id'],
					'Registry_id' => $one_reg['Registry_id'],
					'pmUser_id' => $data['pmUser_id'],
				]);

				if ($saveResponse === false) {
					throw new Exception('Ошибка при добавлении связки предварительного и объединенного реестров');
				}
				else if (is_array($saveResponse) && !empty($saveResponse['Error_Msg'])) {
					throw new Exception($saveResponse['Error_Msg']);
				}
			}

			// пишем информацию о формировании реестра в историю
			$res = $this->dumpRegistryInformation([ 'Registry_id' => $response['Registry_id'] ], 1);

			if ($res === false) {
				throw new Exception('Ошибка при добавлении информации о формировании реестра');
			}
			else if (is_array($res) && !empty($res['Error_Msg'])) {
				throw new Exception($res['Error_Msg']);
			}

			$this->commitTransaction();
		}
		catch (Exception $e) {
			$this->rollbackTransaction();

			$response['Error_Code'] = $e->getCode();
			$response['Error_Msg'] = $e->getMessage();
		}
		
		return $response;
	}

	/**
	 * @param array $data
	 * @return array|false
	 * Загрузка формы редактирования объединённого реестра
	 */
	public function loadUnionRegistryEditForm(array $data = []) {
		return $this->queryResult("
			select
				R.Registry_id as \"Registry_id\",
				R.Registry_Num as \"Registry_Num\",
				convert(varchar(10), R.Registry_accDate, 104) as \"Registry_accDate\",
				convert(varchar(10), R.Registry_begDate, 104) as \"Registry_begDate\",
				convert(varchar(10), R.Registry_endDate, 104) as \"Registry_endDate\",
				R.Lpu_id as \"Lpu_id\",
				R.RegistryGroupType_id as \"RegistryGroupType_id\",
				R.KatNasel_id as \"KatNasel_id\",
				R.PayType_id as \"PayType_id\",
				R.OrgSMO_id as \"OrgSMO_id\",
				R.OrgRSchet_id as \"OrgRSchet_id\"
			from
				{$this->scheme}.v_Registry R (nolock)
			where
				R.Registry_id = :Registry_id
		", [
			'Registry_id' => $data['Registry_id']
		]);
	}

	/**
	 * @param array $data
	 * @return array|bool
	 * Загрузка списка объединённых реестров
	 */
	public function loadUnionRegistryGrid(array $data = []) {
		return $this->getPagingResponse("
			select 
				-- select
				R.Registry_id as \"Registry_id\",
				R.Lpu_id as \"Lpu_id\",
				R.Registry_Num as \"Registry_Num\",
				R.Registry_xmlExportPath as \"Registry_xmlExportPath\",
				R.KatNasel_id as \"KatNasel_id\",
				KN.KatNasel_SysNick as \"KatNasel_SysNick\",
				R.PayType_id as \"PayType_id\",
				PT.PayType_Name as \"PayType_Name\",
				PT.PayType_SysNick as \"PayType_SysNick\",
				RGT.RegistryGroupType_id as \"RegistryGroupType_id\",
				convert(varchar(10), R.Registry_accDate, 104) as \"Registry_accDate\",
				convert(varchar(10), R.Registry_begDate, 104) as \"Registry_begDate\",
				convert(varchar(10), R.Registry_endDate, 104) as \"Registry_endDate\",
				RGT.RegistryGroupType_Name as \"RegistryGroupType_Name\",
				KN.KatNasel_Name as \"KatNasel_Name\",
				OS.OrgSMO_Name as \"OrgSMO_Name\",
				ISNULL(RCnt.Registry_RecordCount, 0) as \"Registry_Count\",
				ISNULL(RCnt.Registry_Sum, 0.00) as \"Registry_Sum\",
				ISNULL(RCnt.Registry_SumPaid, 0.00) as \"Registry_SumPaid\",
				convert(varchar(10), R.Registry_updDT, 104) as \"Registry_updDate\"
				-- end select
			from 
				-- from
				{$this->scheme}.v_Registry R (nolock) -- объединённый реестр
				inner join v_RegistryGroupType RGT (nolock) on RGT.RegistryGroupType_id = R.RegistryGroupType_id
				left join v_KatNasel KN (nolock) on KN.KatNasel_id = R.KatNasel_id
				left join v_PayType PT (nolock) on PT.PayType_id = R.PayType_id
				left join v_OrgSMO OS (nolock) on OS.OrgSMO_id = R.OrgSMO_id
				outer apply (
					select top 1
						SUM(t2.Registry_Sum) as Registry_Sum,
						SUM(t2.Registry_SumPaid) as Registry_SumPaid,
						SUM(t2.Registry_RecordCount) as Registry_RecordCount
					from {$this->scheme}.v_RegistryGroupLink as t1 with (nolock)
						inner join {$this->scheme}.v_Registry as t2 on t2.Registry_id = t1.Registry_id
					where t1.Registry_pid = R.Registry_id
				) RCnt
				-- end from
			where
				-- where
				R.Lpu_id = :Lpu_id
				and R.RegistryType_id = 13
				-- end where
			order by
				-- order by
				R.Registry_endDate DESC,
				R.Registry_updDT DESC
				-- end order by
		", [
			'Lpu_id' => $data['Lpu_id']
		], $data['start'], $data['limit'], true);
	}

	/**
	 * @param array $data
	 * @return array|bool
	 * Загрузка списка предварительных реестров, входящих в объединённый
	 */
	public function loadUnionRegistryChildGrid(array $data = []) {
		return $this->getPagingResponse("
			select
				-- select
				R.Registry_id as \"Registry_id\",
				R.Lpu_id as \"Lpu_id\",
				R.RegistryType_id as \"RegistryType_id\",
				R.Registry_Num as \"Registry_Num\",
				convert(varchar(10), R.Registry_accDate, 104) as \"Registry_accDate\",
				convert(varchar(10), R.Registry_begDate, 104) as \"Registry_begDate\",
				convert(varchar(10), R.Registry_endDate, 104) as \"Registry_endDate\",
				RT.RegistryType_Name as \"RegistryType_Name\",
				KN.KatNasel_Name as \"KatNasel_Name\",
				OS.OrgSMO_Name as \"OrgSMO_Name\",
				ISNULL(R.Registry_RecordCount, 0) as \"Registry_Count\",
				ISNULL(R.Registry_Sum, 0.00) as \"Registry_Sum\",
				ISNULL(R.Registry_SumPaid, 0.00) as \"Registry_SumPaid\",
				convert(varchar(10), R.Registry_updDT, 104) as \"Registry_updDate\"
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryGroupLink as RGL with (nolock)
				inner join {$this->scheme}.v_Registry as R on R.Registry_id = RGL.Registry_id -- обычный реестр
				inner join v_RegistryType as RT on RT.RegistryType_id = R.RegistryType_id
				left join v_KatNasel KN on KN.KatNasel_id = R.KatNasel_id
				left join v_PayType PT on PT.PayType_id = R.PayType_id
				left join v_OrgSMO OS on OS.OrgSMO_id = R.OrgSMO_id
				-- end from
			where
				-- where
				RGL.Registry_pid = :Registry_id
				-- end where
			order by
				-- order by
				R.Registry_id
				-- end order by
		", [
			'Registry_id' => $data['Registry_id']
		], $data['start'], $data['limit'], true);
	}

	/**
	 * @param array $data
	 * @return array
	 * Удаление объединённого реестра
	 */
	public function deleteUnionRegistry(array $data = []) {
		$response = [
			'success' => true,
			'Error_Code' => '',
			'Error_Msg' => '',
		];

		try {
			$this->beginTransaction();

			// 1. выбираем и удаляем все связи
			$links = $this->queryResult("
				select RegistryGroupLink_id
				from {$this->scheme}.v_RegistryGroupLink with (ROWLOCK)
				where Registry_pid = :Registry_id
			", [
				'Registry_id' => $data['id'],
			]);

			foreach ($links as $row) {
				$delResult = $this->getFirstRowFromQuery("
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
		
					exec {$this->scheme}.p_RegistryGroupLink_del
						@RegistryGroupLink_id = :RegistryGroupLink_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
		
					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
				", [
					'RegistryGroupLink_id' => $row['RegistryGroupLink_id'],
					'pmUser_id' => $data['pmUser_id'],
				]);

				if (!is_array($delResult) || count($delResult) == 0) {
					throw new Exception('Ошибка при выполнении запроса к базе данных', __LINE__);
				}
				else if (!empty($delResult['Error_Msg'])) {
					throw new Exception($delResult['Error_Msg'], __LINE__);
				}
			}

			// 2. удаляем сам реестр
			$delResult = $this->getFirstRowFromQuery("
				declare
					@Error_Code bigint,
					@Error_Message varchar(4000);
	
				exec {$this->scheme}.p_Registry_del
					@Registry_id = :Registry_id,
					@pmUser_delID = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
	
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
			", [
				'Registry_id' => $data['id'],
				'pmUser_id' => $data['pmUser_id'],
			]);

			if (!is_array($delResult) || count($delResult) == 0) {
				throw new Exception('Ошибка при выполнении запроса к базе данных', __LINE__);
			}
			else if (!empty($delResult['Error_Msg'])) {
				throw new Exception($delResult['Error_Msg'], __LINE__);
			}

			$this->commitTransaction();
		}
		catch (Exception $e) {
			$this->rollbackTransaction();

			$response['success'] = false;
			$response['Error_Code'] = $e->getCode();
			$response['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array|bool|false
	 */
	public function loadRegistryDataBadVol(array $data) {
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
			'RVP.Registry_id = :Registry_id'
		];
		$joinList = [];
		$params = [
			'Registry_id' => $data['Registry_id']
		];

		if (!empty($data['Person_SurName'])) {
			$filterList[] = "RVP.Person_SurName like :Person_SurName";
			$params['Person_SurName'] = $data['Person_SurName']."%";
		}

		if (!empty($data['Person_FirName'])) {
			$filterList[] = "RVP.Person_FirName like :Person_FirName";
			$params['Person_FirName'] = $data['Person_FirName']."%";
		}

		if (!empty($data['Person_SecName'])) {
			$filterList[] = "RVP.Person_SecName like :Person_SecName";
			$params['Person_SecName'] = $data['Person_SecName']."%";
		}

		if (!empty($data['Evn_id'])) {
			$filterList[] = "RVP.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if (in_array($this->RegistryType_id, [ 7, 9, 12 ])) {
			$joinList[] = "left join dbo.v_EvnPLDisp as EPD with (nolock) on EPD.EvnPLDisp_id = RVP.Evn_id";
			$fieldsList[] = "EPD.DispClass_id as \"DispClass_id\"";
		}

		$query = "
			select
				-- select
				ROW_NUMBER() OVER (ORDER by RVP.Evn_id, RVP.RegistryVolumePlan_id) as \"RegistryDataBadVol_id\",
				RVP.VolumeType_name as \"VolumeType_Name\",
				convert(varchar(10), AV.AttributeValue_begDate, 104) + ' - '
					+ convert(varchar(10), AV.AttributeValue_endDate, 104) as \"Volume_Period\",
				RVP.RegistryVolumePlan_id as \"RegistryVolumePlan_id\",
				RVP.Evn_id as \"Evn_id\",
				E.Evn_rid as \"Evn_rid\",
				RVP.Registry_id as \"Registry_id\",
				E.EvnClass_id as \"EvnClass_id\",
				RD.RegistryType_id as \"RegistryType_id\",
				RVP.Person_id as \"Person_id\",
				E.Server_id as \"Server_id\",
				E.PersonEvn_id as \"PersonEvn_id\",
				RVP.VolumeType_Name as \"VolumeType_Name\",
				RVP.Person_FIO as \"Person_FIO\",
				convert(varchar(10), RVP.Person_BirthDay, 104) as \"Person_BirthDay\",
				RTrim(RVP.LpuSection_name) as \"LpuSection_Name\",
				RVP.MedPersonal_Fio as \"MedPersonal_Fio\",
				convert(varchar(10), RVP.Evn_setDate, 104) as \"Evn_setDate\",
				convert(varchar(10), RVP.Evn_disDate, 104) as \"Evn_disDate\",
				ISNULL(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
				RVP.VolumeType_id as \"VolumeType_id\",
				RVP.AttributeValue_id as \"AttributeValue_id\"
				" . (count($fieldsList) > 0 ? ',' . implode(',', $fieldsList) : '') . "
				-- end select
			from
				-- from
				{$this->scheme}.{$this->RegistryVolumePlanView} as RVP with (nolock)
				inner join dbo.AttributeValue as AV with (nolock) on AV.AttributeValue_id = RVP.AttributeValue_id
				left join {$this->scheme}.v_{$this->RegistryDataObject} as RD with (nolock) on RD.Registry_id = RVP.Registry_id
					and RD.Evn_id = RVP.Evn_id
				left join dbo.v_Evn as E with (nolock) on E.Evn_id = RVP.Evn_id
				" . (count($joinList) > 0 ? implode(' ', $joinList) : '') . "
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				and isnull(RVP.RegistryVolumePlan_isExcess,1) = 2
				-- end where
			order by
				-- order by
				RVP.VolumeType_name,
				RVP.Evn_id
				-- end order by
		";

		if (!empty($data['nopaging'])) {
			return $this->queryResult($query, $params);
		}

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}
}