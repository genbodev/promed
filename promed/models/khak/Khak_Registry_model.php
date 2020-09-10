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
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      12.11.2009
*/
require_once(APPPATH.'models/Registry_model.php');

class Khak_Registry_model extends Registry_model {
	public $scheme = "r19";
	public $region = "khak";

	public $registryEvnNum = [];
	public $registryEvnNumByNZAP = [];

	protected $zapCnt = 0;

	private $_IDCASE = 0;
	private $_IDSERV = 0;
	private $_N_ZAP = 0;
	private $_SL_ID = 0;
	private $_ZSL = 0;

	private $_registryTypeList = array(
		1 => array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар', 'SP_Object' => 'EvnPS'),
		2 => array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника', 'SP_Object' => 'EvnPL'),
		6 => array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь', 'SP_Object' => 'SMP'),
		7 => array('RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения', 'SP_Object' => 'EvnPLDD13'),
		9 => array('RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот', 'SP_Object' => 'EvnPLOrp13'),
		11 => array('RegistryType_id' => 11, 'RegistryType_Name' => 'Проф. осмотры взр. населения', 'SP_Object' => 'EvnPLProf'),
		12 => array('RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних', 'SP_Object' => 'EvnPLProfTeen'),
		14 => array('RegistryType_id' => 14, 'RegistryType_Name' => 'Высокотехнологичная медицинская помощь', 'SP_Object' => 'EvnHTM'),
		//15 => array('RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги', 'SP_Object' => 'EvnUslugaPar'),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 *	Функция возвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	 */
	public function loadRegistryTypeNode($data) {
		return $this->_registryTypeList;
	}

	/**
	 * Получение дополнительных полей для сохранения реестра
	 */
	function getSaveRegistryAdditionalFields() {
		return "
			@OrgSMO_id = :OrgSMO_id,
			@DispClass_id = :DispClass_id,
			@Registry_IsOnceInTwoYears = :Registry_IsOnceInTwoYears,
		";
	}

	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getReformRegistryAdditionalFields() {
		return ',OrgSMO_id,DispClass_id,Registry_IsOnceInTwoYears, ISNULL(Registry_IsZNO, 1) as Registry_IsZNO, ISNULL(Registry_IsFinanc, 1) as Registry_IsFinanc';
	}

	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getLoadRegistryQueueAdditionalFields() {
		return ', R.OrgSMO_id, R.DispClass_id, R.Registry_IsOnceInTwoYears, ISNULL(R.Registry_IsZNO, 1) as Registry_IsZNO';
	}

	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getLoadRegistryAdditionalFields() {
		return ', R.DispClass_id, R.Registry_IsOnceInTwoYears,ISNULL(R.Registry_IsZNO, 1) as Registry_IsZNO';
	}

	/**
	 *	Проверка вхождения случая в реестр
	 */
	function checkEvnInRegistry($data, $action = 'delete')
	{
		$filter = "(1=1)";

		if(isset($data['EvnPL_id'])) {
			$filter .= " and Evn_rid = :EvnPL_id";
			$data['RegistryType_id'] = 2;
		}
		if(isset($data['EvnPS_id'])) {
			$filter .= " and Evn_rid = :EvnPS_id";
			$data['RegistryType_id'] = 1;
		}
		if(isset($data['EvnPLStom_id'])) {
			$filter .= " and Evn_rid = :EvnPLStom_id";
			$data['RegistryType_id'] = 16;
		}
		if(isset($data['EvnVizitPL_id'])) {
			$filter .= " and Evn_id = :EvnVizitPL_id";
			$data['RegistryType_id'] = 2;
		}
		if(isset($data['EvnSection_id'])) {
			$filter .= " and Evn_id = :EvnSection_id";
			$data['RegistryType_id'] = 1;
		}
		if(isset($data['EvnVizitPLStom_id'])) {
			$filter .= " and Evn_id = :EvnVizitPLStom_id";
			$data['RegistryType_id'] = 16;
		}

		if(isset($data['EvnPLDispDop13_id'])) {
			$filter .= " and Evn_id = :EvnPLDispDop13_id";
			$data['RegistryType_id'] = 7;
		}

		if(isset($data['EvnPLDispProf_id'])) {
			$filter .= " and Evn_id = :EvnPLDispProf_id";
			$data['RegistryType_id'] = 11;
		}

		if(isset($data['EvnPLDispOrp_id'])) {
			$filter .= " and Evn_id = :EvnPLDispOrp_id";
			$data['RegistryType_id'] = 9;
		}

		if(isset($data['EvnPLDispTeenInspection_id'])) {
			$filter .= " and Evn_id = :EvnPLDispTeenInspection_id";
			$data['RegistryType_id'] = 12;
		}

		if (empty($filter)) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		//#51767
		if (in_array($data['RegistryType_id'], array(7,9,11,12))) {
			if ($action == 'edit') {
				return false;
			}

			$query = "
				select top 1 DC.DispClass_Code
				from v_Evn E with(nolock)
				inner join v_EvnPLDisp EPLD with(nolock) on EPLD.EvnPLDisp_id = E.Evn_id
				inner join v_DispClass DC with(nolock) on DC.DispClass_id = EPLD.DispClass_id
				where {$filter}
			";
			$result = $this->db->query($query, $data);

			if (!is_object($result)) {
				return $this->createError('', 'Ошибка при определении класса диспансеризации');
			}

			$resp = $result->result('array');

			if (is_array($resp) && count($resp) > 0 && in_array($resp[0]['DispClass_Code'], array(4,8,11,12))) {
				if (isset($data['EvnPLDispTeenInspection_id'])) {
					$data['Evn_id'] = $data['EvnPLDispTeenInspection_id'];
				} else {
					$data['Evn_id'] = $data['EvnPLDispOrp_id'];
				}


				$query = "
					select
						RD.Evn_id,
						R.Registry_Num,
						RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
					from
						{$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_EvnVizitDisp EVD with(nolock) on EVD.EvnVizitDisp_id = RD.Evn_id
					where
						EVD.EvnVizitDisp_pid = :Evn_id
						and R.RegistryStatus_id = 4
				";
				$resp = $this->queryResult($query, $data);
				$actiontxt = 'Удаление';
				switch($action) {
					case 'delete':
						$actiontxt = 'Удаление';
						break;
					case 'edit':
						$actiontxt = 'Редактирование';
						break;
				}

				if( is_array($resp) && count($resp) > 0 ) {
					return array(
						array('Error_Msg' => 'Запись используется в реестре '.$resp[0]['Registry_Num'].' от '.$resp[0]['Registry_accDate']. '.<br/>'.$actiontxt.' записи невозможно.')
					);
				} else {
					return false;
				}
			}
		}

		$query = "
			select top 1
				RD.Evn_id,
				R.Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
			from
				{$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
				left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
				--left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			where
				{$filter}
				and R.Lpu_id = :Lpu_id
			
			union
			
			select top 1
				RD.Evn_id,
				R.Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
			from
				{$this->scheme}.RegistryDataTmp RD with (nolock) -- в процессе формирования
				left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
			where
				{$filter}
				and R.Lpu_id = :Lpu_id
		";
		//echo getDebugSql($query, $data); exit;
		$res = $this->db->query($query, $data);
		if (!is_object($res)) {
			return array(
				array('Error_Msg' => 'Ошибка БД!')
			);
		}

		$actiontxt = 'Удаление';
		switch($action) {
			case 'delete':
				$actiontxt = 'Удаление';
				break;
			case 'edit':
				$actiontxt = 'Редактирование';
				break;
		}

		$resp = $res->result('array');
		if( count($resp) > 0 ) {
			return array(
				array('Error_Msg' => 'Запись используется в реестре '.$resp[0]['Registry_Num'].' от '.$resp[0]['Registry_accDate']. '.<br/>'.$actiontxt.' записи невозможно.')
			);
		} else {
			return false;
		}
	}

	/**
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	public function setRegistryParamsByType($data = array(), $force = false) {
		parent::setRegistryParamsByType($data, $force);

		switch ( $this->RegistryType_id ) {
			case 6:
				$this->RegistryDataObject = 'RegistryDataCmp';
				$this->RegistryDataObjectTable = 'RegistryDataCmp';
				$this->RegistryDataEvnField = 'CmpCloseCard_id';
				$this->RegistryNoPolisObject = 'RegistryCMPNoPolis';
				$this->RegistryNoPolis = 'RegistryCMPNoPolis';
				break;
		}
	}

	/**
	 * Установка статуса импорта реестра в XML
	 */
	public function SetXmlExportStatus($data) {
		if ( empty($data['Registry_id']) ) {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}

		$query = "
			update
				{$this->scheme}.Registry with (rowlock)
			set
				Registry_xmlExportPath = :Status,
				Registry_xmlExpDT = dbo.tzGetDate()
			where
				Registry_id = :Registry_id
		";
		
		$result = $this->db->query($query,
			array(
				'Registry_id' => $data['Registry_id'],
				'Status' => $data['Status']
			)
		);

		if ( !is_object($result) ) {
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}

		return true;
	}

	/**
	 * Получаем состояние реестра в данный момент, и тип реестра
	 */
	protected function _getRegistryXmlExport($data) {
		if ( empty($data['Registry_id']) ) {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}

		$this->setRegistryParamsByType($data);

		$query = "
			select
				RTrim(Registry_xmlExportPath) as Registry_xmlExportPath,
				R.RegistryType_id,
				R.RegistryStatus_id,
				kn.KatNasel_SysNick,
				R.Registry_pack,
				ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				ISNULL(R.Registry_Sum,0) - round(RDSum.RegistryData_ItogSum,2) as Registry_SumDifference,
				RDSum.RegistryData_Count as RegistryData_Count,
				IsNull(R.RegistryCheckStatus_id,0) as RegistryCheckStatus_id,
				IsNull(rcs.RegistryCheckStatus_Code,-1) as RegistryCheckStatus_Code,
				rcs.RegistryCheckStatus_Name as RegistryCheckStatus_Name,
				CONVERT(varchar(10), Registry_begDate, 120) as Registry_begDate,
				CONVERT(varchar(10), Registry_endDate, 120) as Registry_endDate,
				SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) as Registry_endMonth -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
			from {$this->scheme}.Registry R with (nolock)
				left join v_KatNasel kn (nolock) on kn.KatNasel_id = R.KatNasel_id
				outer apply(
					select
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(ISNULL(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
					where RD.Registry_id = R.Registry_id
				) RDSum
				left join RegistryCheckStatus rcs with (nolock) on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			where
				R.Registry_id = :Registry_id
		";

		$result = $this->db->query($query,
			array(
				'Registry_id' => $data['Registry_id']
			)
		);

		if (is_object($result)) {
			$r = $result->result('array');

			if ( is_array($r) && count($r) > 0 ) {
				return $r;
			}
			else {
				return array('success' => false, 'Error_Msg' => 'Ошибка при получении данных реестра');
			}
		}
		else {
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение данных реестра)');
		}
	}

	/**
	 * Возвращает наименование объекта для хранимых процедур в зависимости от типа реестра
	 */
	private function _getRegistryObjectName($type) {
		$result = '';

		if ( array_key_exists($type, $this->_registryTypeList) ) {
			$result = $this->_registryTypeList[$type]['SP_Object'];
		}

		return $result;
	}

	/**
	 * Устанавливает стартовое значение $this->_IDCASE
	 */
	public function setIDCASE($value) {
		$this->_IDCASE = $value;
		return true;
	}

	/**
	 *	Функция возрвращает массив годов, в которых есть реестры
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

		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			$response = array(array('reg_year' => date('Y')));
		}

		return $response;
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	public function exportRegistryToXml($data) {
		$this->load->library('parser');

		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");

		$this->load->library('textlog', array('file'=>'exportRegistryToXml_' . date('Y-m-d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('exportRegistryToXml: Запуск формирования реестра (Registry_id=' . $data['Registry_id'] . ')');
		
		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
		$res = $this->_getRegistryXmlExport($data);
		
		if ( !is_array($res) || count($res) == 0 ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return array('Error_Msg' => 'Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
		}

		$data['KatNasel_SysNick'] = $res[0]['KatNasel_SysNick'];
		$data['Registry_endMonth'] = $res[0]['Registry_endMonth'];
		$data['Registry_pack'] = $res[0]['Registry_pack'];
		$data['RegistryType_id'] = $res[0]['RegistryType_id'];

		if ( empty($data['Registry_pack']) ) {
			$data['Registry_pack'] = $this->_setXmlPackNum($data);
		}

		if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return array('Error_Msg' => 'Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
		}
		else if ( !empty($res[0]['Registry_xmlExportPath']) && $data['OverrideExportOneMoreOrUseExist'] == 1 ) // если уже выгружен реестр
		{
			$link = $res[0]['Registry_xmlExportPath'];
			$this->textlog->add('exportRegistryToXml: вернули ссылку ' . $link);
			return array('success' => true, 'Link' => $link);
		}

		$reg_endmonth = $res[0]['Registry_endMonth'];
		$type = $res[0]['RegistryType_id'];
		$this->textlog->add('exportRegistryToXml: Тип реестра ' . $res[0]['RegistryType_id']);

		if ( !in_array($type, $this->getAllowedRegistryTypes()) ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Данный тип реестров не обрабатывается.');
			return array('Error_Msg' => 'Данный тип реестров не обрабатывается.');
		}

		// Формирование XML в зависимости от типа. 
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try {
			$data['Status'] = '1';
			$this->SetXmlExportStatus($data);
			
			$SCHET = $this->_loadRegistrySCHETForXmlUsing($type, $data);

			switch ( $type ) {
				case 1:
				case 2:
				case 6:
					$pcode = 'L';
					$scode = 'H';
					break;

				case 7:
					$pcode = 'LP';
					$scode = 'DP';
					break;

				case 9:
					$pcode = 'LS';
					$scode = 'DS';
					break;

				case 11:
					$pcode = 'LO';
					$scode = 'DO';
					break;

				case 12:
					$pcode = 'LF';
					$scode = 'DF';
					break;

				case 14:
					$pcode = 'LT';
					$scode = 'HT';
					break;
			}

			$xml_file_body = "registry_khak_1_body";
			$xml_file_header = "registry_khak_1_header";
			$xml_file_footer = "registry_khak_1_footer";

			$xml_file_person_body = "registry_khak_2_body";
			$xml_file_person_header = "registry_khak_2_header";
			$xml_file_person_footer = "registry_khak_2_footer";

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];

			//Проверка на наличие созданной ранее директории
			if ( !file_exists(EXPORTPATH_REGISTRY . $out_dir) ) {
				mkdir( EXPORTPATH_REGISTRY . $out_dir );
			}
			$this->textlog->add('exportRegistryToXml: создали каталог ' . EXPORTPATH_REGISTRY . $out_dir);

			$Liter = ($data['KatNasel_SysNick'] == 'oblast' ? 'S' : 'T');
			$Plat = ($data['KatNasel_SysNick'] == 'oblast' ? $SCHET[0]['PLAT'] : '19');

			// архив
			$file_zip_sign = "NM" . $SCHET[0]['CODE_MO'] . $Liter . $Plat . "_" . $reg_endmonth . $data['Registry_pack'];
			// случаи
			$file_re_data_sign = $scode . "M" . $SCHET[0]['CODE_MO'] . $Liter . $Plat . "_" . $reg_endmonth . $data['Registry_pack'];
			$file_re_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";
			// временный файл для случаев
			$file_re_data_name_tmp = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . "_tmp.xml";
			// перс. данные
			$file_re_pers_data_sign = $pcode . "M" . $SCHET[0]['CODE_MO'] . $Liter . $Plat . "_" . $reg_endmonth . $data['Registry_pack'];
			$file_re_pers_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";                
            
			$this->textlog->add('exportRegistryToXml: Определили наименования файлов: ' . $file_re_data_name . ' и ' . $file_re_pers_data_name);
			$this->textlog->add('exportRegistryToXml: Создаем XML файлы на диске');

			// Заголовок для файла с перс. данными
			$ZGLV = array(
				array(
					'VERSION' => '3.2',
					'FILENAME' => $file_re_pers_data_sign,
					'FILENAME1' => $file_re_data_sign
				)
			);

			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/'.$xml_file_person_header, $ZGLV[0], true);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers);

			// Получаем данные
			$SD_Z = $this->_loadRegistryDataForXml($type, $data, $file_re_data_name_tmp, $file_re_pers_data_name, $xml_file_body, $xml_file_person_body);
			$this->textlog->add('exportRegistryToXml: _loadRegistryDataForXml: Выгрузили данные');
	
			if ( $SD_Z === false ) {
				throw new Exception($this->error_deadlock);
			}

			$this->textlog->add('exportRegistryToXml: Получили все данные из БД');
			$this->textlog->add('exportRegistryToXml: Количество записей реестра = ' . $SD_Z);

			$SCHET[0]['VERSION'] = '3.2';
			$SCHET[0]['SD_Z'] = $SD_Z;
			$SCHET[0]['FILENAME'] = $file_re_data_sign;

			// Заголовок файла с данными
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $xml_file_header, $SCHET[0], true, false);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml);
			unset($xml);

			// Тело файла с данными начитываем из временного (побайтно)
			if ( file_exists($file_re_data_name_tmp) ) {
				// Устанавливаем начитываемый объем данных
				$chunk = 10 * 1024 * 1024; // 10 MB

				$fh = @fopen($file_re_data_name_tmp, "rb");

				if ( $fh === false ) {
					throw new Exception('Ошибка при открытии файла');
				}

				while ( !feof($fh) ) {
					file_put_contents($file_re_data_name, fread($fh, $chunk), FILE_APPEND);
				}

				fclose($fh);

				unlink($file_re_data_name_tmp);
			}

			$this->textlog->add('Перегнали данные из временного файла со случаями в основной файл');

			// записываем footer
			$xml = $this->parser->parse_ext('export_xml/'.$xml_file_footer, array(), true, false);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			$xml_pers = $this->parser->parse_ext('export_xml/'.$xml_file_person_footer, array(), true);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
			unset($xml);
			unset($xml_pers);

			$this->textlog->add('exportRegistryToXml: создан '.$file_re_data_name);
			$this->textlog->add('exportRegistryToXml: создан '.$file_re_pers_data_name);

			$H_registryValidate = true;
			$L_registryValidate = true;

			if(array_key_exists('check_implementFLK', $data['session']['setting']['server']) && $data['session']['setting']['server']['check_implementFLK'] && $res[0]){
				$settingsFLK = $this->loadRegistryEntiesSettings($res[0]);
				if(count($settingsFLK) > 0){
					$upload_path = 'RgistryFields/';
					$settingsFLK = $settingsFLK[0];
					$tplEvnDataXSD = ($settingsFLK['FLKSettings_EvnData']) ? $settingsFLK['FLKSettings_EvnData'] : false;
					$tplPersonDataXSD = ($settingsFLK['FLKSettings_PersonData']) ? $settingsFLK['FLKSettings_PersonData'] : false;	

					if($tplEvnDataXSD){
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplEvnDataXSD); $dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$fileEvnDataXSD = IMPORTPATH_ROOT.$upload_path.$dirTpl."/".$tplEvnDataXSD;
						
						//Проверяем валидность 1го реестра
						//Путь до шаблона
						$H_xsd_tpl = $fileEvnDataXSD;
						//Файл с ошибками, если понадобится
						$H_validate_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_".$file_re_data_sign.'.html';
						//Проверка
						$H_registryValidate = $this->Reconciliation($file_re_data_name, $H_xsd_tpl, 'file', $H_validate_err_file);
					}
					
					if($tplPersonDataXSD){
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplPersonDataXSD); $dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$tplPersonDataXSD = IMPORTPATH_ROOT.$upload_path.$dirTpl."/".$tplPersonDataXSD;
						
						//Проверяем 2й реестр
						//Путь до шаблона
						$L_xsd_tpl = $tplPersonDataXSD;
						//Файл с ошибками, если понадобится
						$L_validate_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_".$file_re_pers_data_sign.'.html';
						//Проверка
						$L_registryValidate = $this->Reconciliation($file_re_pers_data_name, $L_xsd_tpl, 'file', $L_validate_err_file);
					}
				}
			}

			$base_name = $_SERVER["DOCUMENT_ROOT"]."/";

			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . ".zip";
			$file_evn_num_name = EXPORTPATH_REGISTRY . $out_dir . "/evnnum.txt";

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $file_re_data_name, $file_re_data_sign . ".xml" );
			$zip->AddFile( $file_re_pers_data_name, $file_re_pers_data_sign . ".xml" );
			$zip->close();
			$this->textlog->add('exportRegistryToXml: Упаковали в ZIP ' . $file_zip_name);
		
			$data['Status'] = $file_zip_name;
			$this->SetXmlExportStatus($data);
	
			/**/
			if(!$H_registryValidate  && !$L_registryValidate){
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				return array(
					'success' => false, 
					'Error_Msg' => 'Реестр не прошёл проверку ФЛК: <a target="_blank" href="'.$H_validate_err_file.'">отчёт H</a>
						<a target="_blank" href="'.$file_re_data_name.'">H файл реестра</a>,
						<a target="_blank" href="'.$L_validate_err_file.'">отчёт L</a> 
						<a target="_blank" href="'.$file_re_pers_data_name.'">L файл реестра</a>, 
						<a href="'.$file_zip_name.'" target="_blank">zip</a>',
					'Error_Code' => 20
				);
	
			}
			elseif(!$H_registryValidate){
				//Скинули статус 
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				unlink($file_re_pers_data_name);
				$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');
                
				return array(
				   'success' => false, 
				   'Error_Msg' => 'Файл H реестра не прошёл проверку ФЛК: <a target="_blank" href="'.$H_validate_err_file.'">отчёт H</a>
						(<a target="_blank" href="'.$file_re_data_name.'">H файл реестра</a>),
						<a href="'.$file_zip_name.'" target="_blank">zip</a>',
				   'Error_Code' => 21
				);
            }
            elseif(!$L_registryValidate){
                //Скинули статус  
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);                
		       
				unlink($file_re_data_name);
				$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');
                
				return array(
				   'success' => false, 
				   'Error_Msg' => 'Файл L реестра не прошёл ФЛК: <a target="_blank" href="'.$L_validate_err_file.'">отчёт L</a> 
						(<a target="_blank" href="'.$file_re_pers_data_name.'">L файл реестра</a>), 
						<a href="'.$file_zip_name.'" target="_blank">zip</a>',
				   'Error_Code' => 22
				);
            }
            else {
				unlink($file_re_data_name);
				unlink($file_re_pers_data_name);
				$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');

				$this->_saveRegistryEvnNum([
					'Registry_EvnNum' => $this->registryEvnNum,
					'FileName' => $file_evn_num_name
				]);

				// Пишем информацию о выгрузке в историю
				$this->dumpRegistryInformation($data, 2);

				$this->textlog->add('exportRegistryToXml: вернули ссылку ' . $file_zip_name);
				return array('success' => true, 'Link' => $file_zip_name);
			}
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->SetXmlExportStatus($data);
			$this->textlog->add("exportRegistryToXml: " . $e->getMessage());
			return array('success' => false, 'Error_Msg' => $e->getMessage());
		}
	}

	/**
	 * Получение данных о счете для выгрузки объединенного реестра в XML
	 */
	protected function _loadRegistrySCHETForXmlUsing($type, $data) {
		$object = $this->_getRegistryObjectName($type);

		$p_schet = $this->scheme . ".p_Registry_" . $object . "_expScet";

		$query = "exec {$p_schet} @Registry_id = :Registry_id";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return false;
		}

		if ( is_object($result) ) {
			$header = $result->result('array');

			if ( !empty($header[0]) ) {
				array_walk_recursive($header[0], 'ConvertFromUTF8ToWin1251', true);
				return array($header[0]);
			}
		}

		return false;
	}

	/**
	 * Обнуление счетчика записей
	 */
	private function _resetPersCnt() {
		$this->persCnt = 0;
		return true;
	}

	/**
	 * Обнуление счетчика записей
	 */
	private function _resetZapCnt() {
		$this->zapCnt = 0;
		return true;
	}

	/**
	 *	Получение данных для выгрузки реестров в XML
	 */
	protected function _loadRegistryDataForXml($type, $data, $file_re_data_name, $file_re_pers_data_name, $registry_data_template_body, $person_data_template_body) {
		$this->setRegistryParamsByType(array(
			'RegistryType_id' => $type
		));

		$object = $this->_getRegistryObjectName($type);
		$queryParams = array('Registry_id' => $data['Registry_id']);

		if ( empty($object) ) {
			return false;
		}

		$p_zsl = $this->scheme . ".p_Registry_" . $object . "_expSL";
		$p_sl = $this->scheme . ".p_Registry_" . $object . "_expVizit";
		$p_pers = $this->scheme . ".p_Registry_" . $object . "_expPac";

		if ( $type != 14 ) {
			$p_usl = $this->scheme . ".p_Registry_" . $object . "_expUsl";
		}

		if ( in_array($type, array(14,1,2)) ) {
			$p_lekpr = $this->scheme . ".p_Registry_" . $object . "_expLEK_PR";
			$p_bdiag = $this->scheme . ".p_Registry_" . $object . "_expBDIAG";
			$p_bprot = $this->scheme . ".p_Registry_" . $object . "_expBPROT";
			$p_napr = $this->scheme . ".p_Registry_" . $object . "_expNAPR";
			$p_cons = $this->scheme . ".p_Registry_" . $object . "_expCONS";
			$p_onkousl = $this->scheme . ".p_Registry_" . $object . "_expONKOUSL";
		}

		if ( in_array($type, array(1, 2, 7, 9, 11, 12)) ) {
			$p_ds2 = $this->scheme . ".p_Registry_" . $object . "_expDS2";
		}

		if ( in_array($type, array(1)) ) {
			$p_crit = $this->scheme . ".p_Registry_" . $object . "_expCRIT";
			$p_ds3 = $this->scheme . ".p_Registry_" . $object . "_expDS3";
		}

		if ( in_array($type, array(7, 9, 11, 12)) ) {
			$p_naz = $this->scheme . ".p_Registry_" . $object . "_expNAZ";
		}

		// люди
		$query = "exec {$p_pers} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_pac = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');
		if (!is_object($result_pac)) {
			return false;
		}

		// посещения
		$query = "exec {$p_sl} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
		$result_sluch = $this->db->query($query, $queryParams);
		$this->textlog->add('Выполнено');
		if (!is_object($result_sluch)) {
			return false;
		}

		$BDIAG = array();
		$BPROT = array();
		$CONS = array();
		$CRIT = array();
		$DS2 = array();
		$DS3 = array();
		$LEK_PR = array();
		$NAPR = array();
		$NAZ = array();
		$ONKOUSL = array();
		$PACIENT = array();
		$SL = array();
		$SL_KOEF = array();
		$USL = array();
		$ZAP = array();
		$ZSL = array();

		$netValue = toAnsi('НЕТ', true);

		$indexDS2 = 'DS2_DATA';
		if (in_array($type, array(7, 9, 11, 12))) {
			$indexDS2 = 'DS2_N_DATA';
		}

		// услуги
		if ( !empty($p_usl) ) {
			$query = "exec {$p_usl} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$result_usl = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if (!is_object($result_usl)) {
				return false;
			}
			// Формируем массив услуг
			while ( $usluga = $result_usl->_fetch_assoc() ) {
				array_walk_recursive($usluga, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($USL[$usluga['Evn_id']]) ) {
					$USL[$usluga['Evn_id']] = array();
				}

				// привязываем услуги к случаю
				$USL[$usluga['Evn_id']][] = $usluga;
			}
		}

		// Сведения о введённом противоопухолевом лекарственном препарате
		if ( !empty($p_lekpr) ) {
			$query = "exec {$p_lekpr} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}

			// Массив $LEK_PR
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($LEK_PR[$row['EvnUsluga_id']]) ) {
					$LEK_PR[$row['EvnUsluga_id']] = array();
				}

				$LEK_PR[$row['EvnUsluga_id']][] = $row;
			}
		}

		// Данные диагностического блока
		if ( !empty($p_bdiag) ) {
			$query = "exec {$p_bdiag} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}

			// Массив $BDIAG
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($BDIAG[$row['Evn_id']]) ) {
					$BDIAG[$row['Evn_id']] = array();
				}

				$BDIAG[$row['Evn_id']][] = $row;
			}
		}

		//Сведения об имеющихся противопоказаниях и отказах
		if ( !empty($p_bprot) ) {
			$query = "exec {$p_bprot} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}

			// Массив $BPROT
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($BPROT[$row['Evn_id']]) ) {
					$BPROT[$row['Evn_id']] = array();
				}

				$BPROT[$row['Evn_id']][] = $row;
			}
		}

		// Направления
		if ( !empty($p_napr) ) {
			$query = "exec {$p_napr} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}

			// Массив $NAPR
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($NAPR[$row['Evn_id']]) ) {
					$NAPR[$row['Evn_id']] = array();
				}

				$NAPR[$row['Evn_id']][] = $row;
			}
		}

		//Сведения о проведении консилиума
		if ( !empty($p_cons) ) {
			$query = "exec {$p_cons} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}

			// Массив $CONS
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($CONS[$row['Evn_id']]) ) {
					$CONS[$row['Evn_id']] = array();
				}

				$CONS[$row['Evn_id']][] = $row;
			}
		}

		//Сведения об услуге при лечении онкологического заболевания
		if ( !empty($p_onkousl) ) {
			$query = "exec {$p_onkousl} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}

			// Массив $ONKOUSL
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$row['LEK_PR_DATA'] = array();

				if ( !isset($ONKOUSL[$row['Evn_id']]) ) {
					$ONKOUSL[$row['Evn_id']] = array();
				}

				$ONKOUSL[$row['Evn_id']][] = $row;

			}
		}

		// диагнозы CRIT
		if ( !empty($p_crit) ) {
			$query = "exec {$p_crit} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}

			// Массив $CRIT
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($CRIT[$row['Evn_id']]) ) {
					$CRIT[$row['Evn_id']] = array();
				}

				$CRIT[$row['Evn_id']][] = $row;
			}
		}

		// диагнозы DS2
		if ( !empty($p_ds2) ) {
			$query = "exec {$p_ds2} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}

			// Массив $DS2
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($DS2[$row['Evn_id']]) ) {
					$DS2[$row['Evn_id']] = array();
				}

				$DS2[$row['Evn_id']][] = $row;
			}
		}

		// диагнозы DS3
		if ( !empty($p_ds3) ) {
			$query = "exec {$p_ds3} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}

			// Массив $DS3
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($DS3[$row['Evn_id']]) ) {
					$DS3[$row['Evn_id']] = array();
				}

				$DS3[$row['Evn_id']][] = $row;
			}
		}

		// назначения NAZ
		if ( !empty($p_naz) ) {
			$query = "exec {$p_naz} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}

			// Массив $NAZ
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($NAZ[$row['Evn_id']]) ) {
					$NAZ[$row['Evn_id']] = array();
				}

				$NAZ[$row['Evn_id']][] = $row;
			}
		}

		// ЗСЛ (ZSL)
		$query = "exec {$p_zsl} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
		$result_sl = $this->db->query($query, $queryParams);
		$this->textlog->add('Выполнено');
		if (!is_object($result_sl)) {
			return false;
		}

		while ( $row = $result_sl->_fetch_assoc() ) {
			array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
			$ZSL[$row['Evn_rid']] = $row;
		}

		// Формируем массив случаев
		while ( $record = $result_sluch->_fetch_assoc() ) {
			array_walk_recursive($record, 'ConvertFromUTF8ToWin1251', true);
			if ( !isset($SL[$record['Evn_rid']]) ) {
				$SL[$record['Evn_rid']] = array();
			}

			// привязываем случаи к законченному случаю
			$SL[$record['Evn_rid']][] = $record;
		}

		// Формируем массив пациентов
		while ( $pers = $result_pac->_fetch_assoc() ) {
			array_walk_recursive($pers, 'ConvertFromUTF8ToWin1251', true);
			if ( !empty($pers['ID_PAC']) ) {
				$pers['DOST'] = array();
				$pers['DOST_P'] = array();

				if ( $pers['NOVOR'] != '0' ) {
					if ( empty($pers['FAM_P']) ) {
						$pers['DOST_P'][] = array('DOST_P_VAL' => 2);
					}

					if ( empty($pers['IM_P']) ) {
						$pers['DOST_P'][] = array('DOST_P_VAL' => 3);
					}

					if ( empty($pers['OT_P']) || strtoupper($pers['OT_P']) == $netValue ) {
						$pers['DOST_P'][] = array('DOST_P_VAL' => 1);
					}
				}
				else {
					if ( empty($pers['FAM']) ) {
						$pers['DOST'][] = array('DOST_VAL' => 2);
					}

					if ( empty($pers['IM']) ) {
						$pers['DOST'][] = array('DOST_VAL' => 3);
					}

					if ( empty($pers['OT']) || strtoupper($pers['OT']) == $netValue ) {
						$pers['DOST'][] = array('DOST_VAL' => 1);
					}
				}

				$PACIENT[$pers['ID_PAC']] = $pers;
			}
		}

		// Соответствие полей в выгрузке и необходимых названий тегов в XML-файле
		// Реализовано, т.к. парсер неправильно подставляет значения для полей с одинаковыми названиями в блоках Z_SL, SL и USL
		$altKeys = array(
			 'LPU_USL' => 'LPU'
			,'LPU_1_USL' => 'LPU_1'
			,'P_OTK_USL' => 'P_OTK'
			,'PODR_USL' => 'PODR'
			,'PROFIL_USL' => 'PROFIL'
			,'DET_USL' => 'DET'
			,'TARIF_USL' => 'TARIF'
			,'PRVS_USL' => 'PRVS'
		);

		$SD_Z = 0;

		$KSG_KPG_FIELDS = array('N_KSG', 'VER_KSG', 'KSG_PG', 'N_KPG', 'KOEF_Z', 'KOEF_UP', 'BZTSZ', 'KOEF_D', 'KOEF_U', 'SL_K', 'IT_SL');
		$ID_SL_FIELDS = array('IDSL', 'Z_SL');
		$ONK_SL_FIELDS = array('DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD', 'K_FR', 'WEI', 'HEI', 'BSA');

		// Идём по случаям, как набираем 1000 записей -> пишем сразу в файл.
		$this->textlog->add('Начинаем обработку случаев');
		foreach ( $ZSL as $key => $oneZSL ) {
			if ( empty($oneZSL['Evn_rid']) ) {
				continue;
			}

			$key = $oneZSL['Evn_rid'];

			// привязывем случаи к законченному случаю
			$oneZSL['SL'] = array();

			$oneZSL['OS_SLUCH_DATA'] = array();
			$oneZSL['VNOV_M_DATA'] = array();

			if ( !empty($oneZSL['VNOV_M']) ) {
				$oneZSL['VNOV_M_DATA'][] = array('VNOV_M_VAL' => $oneZSL['VNOV_M']);
			}

			if ( !empty($oneZSL['OS_SLUCH']) ) {
				$oneZSL['OS_SLUCH_DATA'][] = array('OS_SLUCH' => $oneZSL['OS_SLUCH']);
			}

			if ( !empty($oneZSL['OS_SLUCH1']) ) {
				$oneZSL['OS_SLUCH_DATA'][] = array('OS_SLUCH' => $oneZSL['OS_SLUCH1']);
			}

			if ( array_key_exists('OS_SLUCH', $oneZSL) ) {
				unset($oneZSL['OS_SLUCH']);
			}

			if ( array_key_exists('OS_SLUCH1', $oneZSL) ) {
				unset($oneZSL['OS_SLUCH1']);
			}

			if ( isset($SL[$key]) ) {
				foreach ( $SL[$key] as $oneSL ) {
					$slKey = $oneSL['Evn_id'];

					$oneSL['CODE_MES1_DATA'] = array();
					$oneSL['CONS_DATA'] = array();
					$oneSL['DS2_DATA'] = array();
					$oneSL['DS2_N_DATA'] = array();
					$oneSL['DS3_DATA'] = array();
					$oneSL['NAPR_DATA'] = array();
					$oneSL['NAZ_DATA'] = array();
					$oneSL['ONK_SL_DATA'] = array();
					$oneSL['SANK'] = array();

					if ( isset($DS2[$slKey]) ) {
						$oneSL[$indexDS2] = $DS2[$slKey];
						unset($DS2[$slKey]);
					}
					else if ( !empty($oneSL['DS2']) ) {
						$oneSL[$indexDS2] = array(array(
							'DS2' => (!empty($oneSL['DS2']) ? $oneSL['DS2'] : null),
							'DS2_PR' => (!empty($oneSL['DS2_PR']) ? $oneSL['DS2_PR'] : null),
							'DS2_DN' => (!empty($oneSL['DS2_DN']) ? $oneSL['DS2_DN'] : null)
						));
					}

					if ( isset($DS3[$slKey]) ) {
						$oneSL['DS3_DATA'] = $DS3[$slKey];
						unset($DS3[$slKey]);
					}
					else if ( !empty($oneSL['DS3']) ) {
						$oneSL['DS3_DATA'] = array(array('DS3' => $oneSL['DS3']));
					}

					if ( isset($NAPR[$slKey]) ) {
						$oneSL['NAPR_DATA'] = $NAPR[$slKey];
						unset($NAPR[$slKey]);
					}

					if ( isset($CONS[$slKey]) ) {
						$oneSL['CONS_DATA'] = $CONS[$slKey];
						unset($CONS[$slKey]);
					}

					if ( isset($NAZ[$slKey]) ) {
						$oneSL['NAZ_DATA'] = $NAZ[$slKey];
						unset($NAZ[$slKey]);
					}

					if ( array_key_exists('DS2', $oneSL) ) {
						unset($oneSL['DS2']);
					}

					if ( array_key_exists('DS3', $oneSL) ) {
						unset($oneSL['DS3']);
					}

					$onkDS2 = false;
					$ONK_SL_DATA = array();

					if ( count($oneSL['DS2_DATA']) > 0 ) {
						foreach ( $oneSL['DS2_DATA'] as $ds2 ) {
							if ( empty($ds2['DS2']) ) {
								continue;
							}

							$code = substr($ds2['DS2'], 0, 3);

							if ( ($code >= 'C00' && $code <= 'C80') || $code == 'C97' ) {
								$onkDS2 = true;
							}
						}
					}

					if (
						(empty($oneSL['DS_ONK']) || $oneSL['DS_ONK'] != 1)
						&& (empty($oneSL['P_CEL']) || $oneSL['P_CEL'] != '1.3')
						&& !empty($oneSL['DS1'])
						&& (
							substr($oneSL['DS1'], 0, 1) == 'C'
							|| ($oneSL['DS1'] == 'D70' && $onkDS2 == true)
						)
					) {
						$hasONKOSLData = false;

						$ONK_SL_DATA['B_DIAG_DATA'] = array();
						$ONK_SL_DATA['B_PROT_DATA'] = array();
						$ONK_SL_DATA['ONK_USL_DATA'] = array();

						foreach ( $ONK_SL_FIELDS as $field ) {
							if ( isset($oneSL[$field]) ) {
								$hasONKOSLData = true;
								$ONK_SL_DATA[$field] = $oneSL[$field];
							}
							else {
								$ONK_SL_DATA[$field] = null;
							}

							if ( array_key_exists($field, $oneSL) ) {
								unset($oneSL[$field]);
							}
						}

						if ( isset($BDIAG[$slKey]) ) {
							$hasONKOSLData = true;
							$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$slKey];
							unset($BDIAG[$slKey]);
						}

						if ( isset($BPROT[$slKey]) ) {
							$hasONKOSLData = true;
							$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$slKey];
							unset($BPROT[$slKey]);
						}

						if ( isset($ONKOUSL[$slKey]) ) {
							foreach ( $ONKOUSL[$slKey] as $onkuslKey => $onkuslRow ) {
								if ( isset($LEK_PR[$onkuslRow['EvnUsluga_id']]) ) {
									$LEK_PR_DATA = array();

									foreach ( $LEK_PR[$onkuslRow['EvnUsluga_id']] as $row ) {
										if ( !isset($LEK_PR_DATA[$row['REGNUM']]) ) {
											$LEK_PR_DATA[$row['REGNUM']] = array(
												'REGNUM' => $row['REGNUM'],
												'CODE_SH' => (!empty($row['CODE_SH']) ? $row['CODE_SH'] : null),
												'DATE_INJ_DATA' => array(),
											);
										}

										$LEK_PR_DATA[$row['REGNUM']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $row['DATE_INJ']);
									}

									$ONKOUSL[$slKey][$onkuslKey]['LEK_PR_DATA'] = $LEK_PR_DATA;

									unset($LEK_PR[$onkuslRow['EvnUsluga_id']]);
								}
							}

							$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$slKey];
						}

						if ( $hasONKOSLData == false ) {
							$ONK_SL_DATA = array();
						}
					}

					if ( count($ONK_SL_DATA) > 0 ) {
						$oneSL['ONK_SL_DATA'][] = $ONK_SL_DATA;
					}

					$KSG_KPG_DATA = array();
					foreach ( $KSG_KPG_FIELDS as $index ) {
						if (isset($oneSL[$index])) {
							$KSG_KPG_DATA[$index] = $oneSL[$index];
							unset($oneSL[$index]);
						}
					}
					if ( count($KSG_KPG_DATA) > 0 ) {
						$KSG_KPG_DATA['CRIT_DATA'] = array();

						if ( isset($CRIT[$slKey]) ) {
							$KSG_KPG_DATA['CRIT_DATA'] = $CRIT[$slKey];
							unset($CRIT[$slKey]);
						}
						
						foreach($ID_SL_FIELDS as $index) {
							if (isset($oneSL[$index])) {
								$SL_KOEF[$index] = $oneSL[$index];
								unset($oneSL[$index]);
							}
						}

						if ( count($SL_KOEF) > 0 ) {
							$KSG_KPG_DATA['SL_KOEF_DATA'] = array($SL_KOEF);
						} else {
							$KSG_KPG_DATA['SL_KOEF_DATA'] = array();
						}

						$oneSL['KSG_KPG_DATA'] = array($KSG_KPG_DATA);
					}
					else {
						$oneSL['KSG_KPG_DATA'] = array();
					}

					// привязываем услуги к случаю
					if ( isset($USL[$slKey]) ) {
						$oneSL['USL'] = $USL[$slKey];
						unset($USL[$slKey]);
					}
					else {
						$oneSL['USL'] = array();
					}

					$oneZSL['SL'][] = $oneSL;

					$this->zapCnt++;

					if ( !isset($this->registryEvnNum[$this->zapCnt]) ) {
						$this->registryEvnNum[$this->zapCnt] = array();
					}

					$this->registryEvnNum[$this->zapCnt][] = $slKey;

				}

				unset($SL[$key]);
			}

			// иначе порядковый
			$this->_IDCASE++;
			$oneZSL['IDCASE'] = $this->_IDCASE;

			$ZAP[$key] = array(
				'N_ZAP' => $oneZSL['IDCASE'],
				'PACIENT' => array($PACIENT[$key]),
				'Z_SL_DATA' => array($oneZSL)
			);

			$ZAP[$key]['PR_NOV'] = (!empty($oneZSL['PR_NOV']) ? $oneZSL['PR_NOV'] : 0);

			// проапдейтить поле RegistryData_RowNum
			$this->db->query("
				update
					{$this->scheme}.{$this->RegistryDataObject} with (rowlock)
				set
					{$this->RegistryDataObject}_RowNum = :RegistryData_RowNum
				where
					Registry_id = :Registry_id
					and {$this->RegistryDataEvnField} = :Evn_id
			", array(
				'Registry_id' => $data['Registry_id'],
				'Evn_id' => $oneZSL['Evn_rid'],
				'RegistryData_RowNum' => $this->_IDCASE
			));

			unset($ZSL[$key]);

			if (count($ZAP) >= 1000) {
				$SD_Z += count($ZAP);
				// пишем в файл
				$parseTimeStart = time();
				$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_body, array('ZAP' => $ZAP), true, false, $altKeys, false);
				$parseTimeFinish = time();
				$this->textlog->add('Распарсили 1000 записей за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);
				unset($xml);
				unset($ZAP);
				$ZAP = array();
			}
		}

		if (count($ZAP) > 0) {
			$SD_Z += count($ZAP);
			// пишем в файл
			$parseTimeStart = time();
			$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_body, array('ZAP' => $ZAP), true, false, $altKeys, false);
			$parseTimeFinish = time();
			$this->textlog->add('Распарсили ' . count($ZAP) . ' записей за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			unset($xml);
			unset($ZAP);
		}

		unset($DS2);
		unset($NAZ);
		unset($SL_KOEF);
		unset($ZSL);
		unset($SL);
		unset($USL);

		$toFile = array();
		foreach($PACIENT as $onepac) {
			$toFile[] = $onepac;
			if (count($toFile) >= 1000) {
				// пишем в файл
				$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_body, array('PACIENT' => $toFile), true, false, array(), false);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
				unset($xml_pers);
				unset($toFile);
				$toFile = array();
			}
		}
		if (count($toFile) > 0) {
			// пишем в файл
			$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_body, array('PACIENT' => $toFile), true, false, array(), false);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
			unset($xml_pers);
			unset($toFile);
		}

		unset($toFile);
		unset($PACIENT);

		return $SD_Z;
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
					select top 1 Registry_pack
					from {$this->scheme}.v_Registry with (nolock)
					where Registry_id = :Registry_id
				);

				if ( @packNum is null )
					begin
						set @packNum = (
							select max(Registry_pack)
							from {$this->scheme}.v_Registry with (nolock)
							where Lpu_id = :Lpu_id
								and SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) = :Registry_endMonth
								and Registry_pack is not null
								and RegistryType_id = :RegistryType_id
						);

						set @packNum = ISNULL(@packNum, 0) + 1;

						update {$this->scheme}.Registry with (updlock)
						set Registry_pack = @packNum
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
	 * Загрузка данных о реестре для процедуры импорта
	 */
	public function loadRegistryForImportXml($data) {
		return $this->getFirstRowFromQuery("
			select top 1
				Registry_id,
				RegistryType_id,
				Registry_Num,
				convert(varchar(10), Registry_accDate, 104) as Registry_accDate,
				OrgSmo_id,
				Registry_xmlExportPath
			from
				{$this->scheme}.v_Registry (nolock)
			where
				Registry_id = :Registry_id
		", array(
			'Registry_id' => $data['Registry_id']
		));
	}

	/**
	 * Получение идентификаторов случая в реестре при импорте
	 */ 	
	public function checkErrorDataInRegistry($data) {
		if ( empty($data['SL_ID']) || empty($data['Registry_id']) ) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		return $this->getFirstRowFromQuery("
			select top 1
				rd.Evn_id,
				rd.Registry_id
			from
				{$this->scheme}.v_Registry r with (nolock)
				inner join {$this->scheme}.v_{$this->RegistryDataObject} rd with (nolock) on rd.Registry_id = r.Registry_id
			where
				r.Registry_id = :Registry_id
				and rd.Evn_id = :SL_ID
		", $data);
	}

	/**
	 * Удаление ошибок ТФОМС из реестра
	 */
	public function deleteRegistryErrorTFOMS($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		return $this->getFirstRowFromQuery("
			declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '',
				@Registry_id bigint = :Registry_id;

			set nocount on;

			begin try
				delete from {$this->scheme}.RegistryErrorTFOMS with (rowlock) where Registry_id = @Registry_id;
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch

			set nocount off

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", $data);
	}

	/**
	 * Добавление ошибки ТФОМС/СМО
	 */ 		
	public function setErrorFromImportRegistry($data) {
        $this->setRegistryParamsByType($data);

		$RegistryErrorType_id = $this->getFirstResultFromQuery("
			select top 1 RegistryErrorType_id
			from {$this->scheme}.RegistryErrorType with (nolock)
			where RegistryErrorType_Name = :COMMENT
		", array(
			'COMMENT' => $data['COMMENT'],
		), true);

		if ( empty($RegistryErrorType_id) ) {
			$resp = $this->getFirstRowFromQuery("
				declare
					@RegistryErrorType_id bigint,
					@Error_Code bigint,
					@Error_Message varchar(4000);

				exec {$this->scheme}.p_RegistryErrorType_ins
					@RegistryErrorType_id = @RegistryErrorType_id output,
					@RegistryErrorType_Code = :OSHIB,
					@RegistryErrorType_Name = :COMMENT,
					@RegistryErrorType_Descr = :COMMENT,
					@RegistryErrorClass_id = 1,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select
					@RegistryErrorType_id as RegistryErrorType_id,
					@Error_Code as Error_Code,
					@Error_Message as Error_Msg;
			", $data);

			if ( $resp === false ) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к БД (добавление нового типа ошибки)'));
			}
			else if ( !is_array($resp) || count($resp) == 0 ) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при добавлении нового типа ошибки'));
			}
			else if ( !empty($resp[0]['Error_Msg']) ) {
				return array(array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']));
			}

			$data['RegistryErrorType_id'] = $resp['RegistryErrorType_id'];
		}
		else {
			$data['RegistryErrorType_id'] = $RegistryErrorType_id;
		}

		return $this->getFirstRowFromQuery("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			
			exec {$this->scheme}.p_RegistryErrorTFOMS_ins
				@Registry_id = :Registry_id,
				@Evn_id = :Evn_id,
				@RegistryErrorType_id = :RegistryErrorType_id,
				@RegistryErrorType_Code = :OSHIB,
				@RegistryErrorTFOMS_FieldName = :IM_POL,
				@RegistryErrorTFOMS_BaseElement = :BAS_EL,
				@RegistryErrorTFOMS_Comment = :COMMENT,
				@RegistryErrorClass_id = 1,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				 
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $data);
	}

	/**
	 *	Помечаем запись реестра на удаление
	 */
	function deleteRegistryData($data)
	{
		$evn_list = $data['EvnIds'];

		foreach ($evn_list as $EvnId) {
			$data['Evn_id'] = $EvnId;

			$query = "
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
			";
			$res = $this->db->query($query, $data);
		}

		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Сохранение Registry_EvnNum
	 */
	protected function _saveRegistryEvnNum($data) {
		$toWrite = array();

		foreach ( $data['Registry_EvnNum'] as $key => $record ) {
			$toWrite[$key] = $record;

			if ( count($toWrite) >= 1000 ) {
				$str = json_encode($toWrite) . PHP_EOL;
				@file_put_contents($data['FileName'], $str, FILE_APPEND);
				$toWrite = array();
			}
		}

		if ( count($toWrite) > 0 ) {
			$str = json_encode($toWrite) . PHP_EOL;
			file_put_contents($data['FileName'], $str, FILE_APPEND);
		}

		return true;
	}

	/**
	 * Получение Registry_EvnNum
	 */
	public function setRegistryEvnNum($data) {
		if ( !empty($data['Registry_EvnNum']) ) {
			$this->registryEvnNum = json_decode($data['Registry_EvnNum'], true);
		}
		else if ( !empty($data['Registry_xmlExportPath']) ) {
			$filename = basename($data['Registry_xmlExportPath']);
			$evnNumPath = str_replace('/' . $filename, '/evnnum.txt', $data['Registry_xmlExportPath']);

			if ( file_exists($evnNumPath) ) {
				$fileContents = file_get_contents($evnNumPath);
				$exploded = explode(PHP_EOL, $fileContents);
				$this->registryEvnNum = [];

				foreach ( $exploded as $one ) {
					if ( !empty($one) ) {
						$unjsoned = json_decode($one, true);

						if ( is_array($unjsoned) ) {
							foreach ( $unjsoned as $key => $value ) {
								$this->registryEvnNum[$key] = $value;
							}
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Получение массива Evn_id, сгруппированного по N_ZAP
	 */
	public function setRegistryEvnNumByNZAP() {
		if ( is_array($this->registryEvnNum) && count($this->registryEvnNum) > 0 ) {
			$this->registryEvnNumByNZAP = [];

			foreach ( $this->registryEvnNum as $key => $record ) {
				if ( is_array($record) && !empty($record[0]['z']) ) {
					if ( !isset($this->registryEvnNumByNZAP[$record[0]['z']]) ) {
						$this->registryEvnNumByNZAP[$record[0]['z']] = [];
					}

					$this->registryEvnNumByNZAP[$record[0]['z']][] = $key;
				}
			}
		}

		return true;
	}
}