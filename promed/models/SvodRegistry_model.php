<?php defined('BASEPATH') or die ('No direct script access allowed');

class SvodRegistry_model extends swModel {
	protected $schema = "dbo"; //региональная схема

	private $Registry_id;//Идентификатор
	private $RegistryType_id;//тип реестра
	private $Lpu_id;//идентификатор справочника ЛПУ
	private $Registry_begDate;//начало периода
	private $Registry_endDate;//окончание периода
	private $KatNasel_id;//категория населения
	private $Registry_Num;//номер счета
	private $Registry_accDate;//дата счета
	private $RegistryStatus_id;//идентификатор статуса реестра
	private $Registry_Sum;//Registry_Sum
	private $Registry_IsActive;//Registry_IsActive
	private $Registry_ErrorCount;//количество ошибок в реестре
	private $Registry_ErrorCommonCount;//Registry_ErrorCommonCount
	private $Registry_RecordCount;//количество записей
	private $OrgRSchet_id;//идентификатор расчетного счета
	private $Registry_ExportPath;//Registry_ExportPath
	private $Registry_expDT;//Registry_expDT
	private $RegistryStacType_id;//тип реестра стационара
	private $Registry_RecordPaidCount;//Registry_RecordPaidCount
	private $Registry_KdCount;//Registry_KdCount
	private $Registry_KdPaidCount;//Registry_KdPaidCount
	private $Registry_xmlExportPath;//Registry_xmlExportPath
	private $Registry_xmlExpDT;//Registry_xmlExpDT
	private $RegistryCheckStatus_id;//Статус проверки реестра
	private $Registry_Task;//задание на обработку реестра
	private $LpuBuilding_id;//идентификатор подразделения
	private $PayType_id;//Тип оплаты
	private $Registry_SumPaid;//Реально оплаченная сумма реестра
	private $Registry_CheckStatusDate;//Дата установления статуса в Промед
	private $Registry_CheckStatusTFOMSDate;//Дата проставления статуса в ТФОМС
	private $Registry_sendDT;//Дата отправки реестра
	private $Registry_IsNeedReform;//Признак необходимости переформирования реестра
	private $DrugFinance_id;//Источник финансирования
	private $WhsDocumentCostItemType_id;//Статья расхода
	private $Org_id;//Организация
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Получение параметра
	 */
	public function getRegistry_id() { return $this->Registry_id;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_id($value) { $this->Registry_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistryType_id() { return $this->RegistryType_id;}

	/**
	 * Установка параметра
	 */
	public function setRegistryType_id($value) { $this->RegistryType_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getLpu_id() { return $this->Lpu_id;}

	/**
	 * Установка параметра
	 */
	public function setLpu_id($value) { $this->Lpu_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_begDate() { return $this->Registry_begDate;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_begDate($value) { $this->Registry_begDate = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_endDate() { return $this->Registry_endDate;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_endDate($value) { $this->Registry_endDate = $value; }

	/**
	 * Получение параметра
	 */
	public function getKatNasel_id() { return $this->KatNasel_id;}

	/**
	 * Установка параметра
	 */
	public function setKatNasel_id($value) { $this->KatNasel_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_Num() { return $this->Registry_Num;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_Num($value) { $this->Registry_Num = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_accDate() { return $this->Registry_accDate;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_accDate($value) { $this->Registry_accDate = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistryStatus_id() { return $this->RegistryStatus_id;}

	/**
	 * Установка параметра
	 */
	public function setRegistryStatus_id($value) { $this->RegistryStatus_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_Sum() { return $this->Registry_Sum;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_Sum($value) { $this->Registry_Sum = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_IsActive() { return $this->Registry_IsActive;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_IsActive($value) { $this->Registry_IsActive = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_ErrorCount() { return $this->Registry_ErrorCount;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_ErrorCount($value) { $this->Registry_ErrorCount = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_ErrorCommonCount() { return $this->Registry_ErrorCommonCount;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_ErrorCommonCount($value) { $this->Registry_ErrorCommonCount = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_RecordCount() { return $this->Registry_RecordCount;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_RecordCount($value) { $this->Registry_RecordCount = $value; }

	/**
	 * Получение параметра
	 */
	public function getOrgRSchet_id() { return $this->OrgRSchet_id;}

	/**
	 * Установка параметра
	 */
	public function setOrgRSchet_id($value) { $this->OrgRSchet_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_ExportPath() { return $this->Registry_ExportPath;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_ExportPath($value) { $this->Registry_ExportPath = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_expDT() { return $this->Registry_expDT;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_expDT($value) { $this->Registry_expDT = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistryStacType_id() { return $this->RegistryStacType_id;}

	/**
	 * Установка параметра
	 */
	public function setRegistryStacType_id($value) { $this->RegistryStacType_id = $value; }

	/**
	 * Установка параметра
	 */
	public function setRegistryEventType_id($value) { $this->RegistryEventType_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_RecordPaidCount() { return $this->Registry_RecordPaidCount;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_RecordPaidCount($value) { $this->Registry_RecordPaidCount = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_KdCount() { return $this->Registry_KdCount;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_KdCount($value) { $this->Registry_KdCount = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_KdPaidCount() { return $this->Registry_KdPaidCount;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_KdPaidCount($value) { $this->Registry_KdPaidCount = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_xmlExportPath() { return $this->Registry_xmlExportPath;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_xmlExportPath($value) { $this->Registry_xmlExportPath = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_xmlExpDT() { return $this->Registry_xmlExpDT;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_xmlExpDT($value) { $this->Registry_xmlExpDT = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistryCheckStatus_id() { return $this->RegistryCheckStatus_id;}

	/**
	 * Установка параметра
	 */
	public function setRegistryCheckStatus_id($value) { $this->RegistryCheckStatus_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_Task() { return $this->Registry_Task;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_Task($value) { $this->Registry_Task = $value; }

	/**
	 * Получение параметра
	 */
	public function getLpuBuilding_id() { return $this->LpuBuilding_id;}

	/**
	 * Установка параметра
	 */
	public function setLpuBuilding_id($value) { $this->LpuBuilding_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getPayType_id() { return $this->PayType_id;}

	/**
	 * Установка параметра
	 */
	public function setPayType_id($value) { $this->PayType_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_SumPaid() { return $this->Registry_SumPaid;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_SumPaid($value) { $this->Registry_SumPaid = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_CheckStatusDate() { return $this->Registry_CheckStatusDate;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_CheckStatusDate($value) { $this->Registry_CheckStatusDate = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_CheckStatusTFOMSDate() { return $this->Registry_CheckStatusTFOMSDate;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_CheckStatusTFOMSDate($value) { $this->Registry_CheckStatusTFOMSDate = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_sendDT() { return $this->Registry_sendDT;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_sendDT($value) { $this->Registry_sendDT = $value; }

	/**
	 * Получение параметра
	 */
	public function getRegistry_IsNeedReform() { return $this->Registry_IsNeedReform;}

	/**
	 * Установка параметра
	 */
	public function setRegistry_IsNeedReform($value) { $this->Registry_IsNeedReform = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugFinance_id() { return $this->DrugFinance_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugFinance_id($value) { $this->DrugFinance_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getWhsDocumentCostItemType_id() { return $this->WhsDocumentCostItemType_id;}

	/**
	 * Установка параметра
	 */
	public function setWhsDocumentCostItemType_id($value) { $this->WhsDocumentCostItemType_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getOrg_id() { return $this->Org_id;}

	/**
	 * Установка параметра
	 */
	public function setOrg_id($value) { $this->Org_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getpmUser_id() { return $this->pmUser_id;}

	/**
	 * Установка параметра
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Конструктор
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}

		//установка региональной схемы
		$config = get_config();
		$this->schema = $config['regions'][getRegionNumber()]['schema'];
	}

	/**
	 * Загрузка
	 */
	function load() {
		$q = "
			select
				Registry_id, RegistryType_id, Lpu_id, Registry_begDate, Registry_endDate, KatNasel_id, Registry_Num, Registry_accDate, RegistryStatus_id, Registry_Sum, Registry_IsActive, Registry_ErrorCount, Registry_ErrorCommonCount, Registry_RecordCount, OrgRSchet_id, Registry_ExportPath, Registry_expDT, RegistryStacType_id, Registry_RecordPaidCount, Registry_KdCount, Registry_KdPaidCount, Registry_xmlExportPath, Registry_xmlExpDT, RegistryCheckStatus_id, Registry_Task, LpuBuilding_id, PayType_id, Registry_SumPaid, Registry_CheckStatusDate, Registry_CheckStatusTFOMSDate, Registry_sendDT, Registry_IsNeedReform, DrugFinance_id, WhsDocumentCostItemType_id, Org_id
			from
				{$this->schema}.v_Registry
			where
				Registry_id = :Registry_id
		";
		$r = $this->db->query($q, array('Registry_id' => $this->Registry_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->Registry_id = $r[0]['Registry_id'];
				$this->RegistryType_id = $r[0]['RegistryType_id'];
				$this->Lpu_id = $r[0]['Lpu_id'];
				$this->Registry_begDate = $r[0]['Registry_begDate'];
				$this->Registry_endDate = $r[0]['Registry_endDate'];
				$this->KatNasel_id = $r[0]['KatNasel_id'];
				$this->Registry_Num = $r[0]['Registry_Num'];
				$this->Registry_accDate = $r[0]['Registry_accDate'];
				$this->RegistryStatus_id = $r[0]['RegistryStatus_id'];
				$this->Registry_Sum = $r[0]['Registry_Sum'];
				$this->Registry_IsActive = $r[0]['Registry_IsActive'];
				$this->Registry_ErrorCount = $r[0]['Registry_ErrorCount'];
				$this->Registry_ErrorCommonCount = $r[0]['Registry_ErrorCommonCount'];
				$this->Registry_RecordCount = $r[0]['Registry_RecordCount'];
				$this->OrgRSchet_id = $r[0]['OrgRSchet_id'];
				$this->Registry_ExportPath = $r[0]['Registry_ExportPath'];
				$this->Registry_expDT = $r[0]['Registry_expDT'];
				$this->RegistryStacType_id = $r[0]['RegistryStacType_id'];
				$this->Registry_RecordPaidCount = $r[0]['Registry_RecordPaidCount'];
				$this->Registry_KdCount = $r[0]['Registry_KdCount'];
				$this->Registry_KdPaidCount = $r[0]['Registry_KdPaidCount'];
				$this->Registry_xmlExportPath = $r[0]['Registry_xmlExportPath'];
				$this->Registry_xmlExpDT = $r[0]['Registry_xmlExpDT'];
				$this->RegistryCheckStatus_id = $r[0]['RegistryCheckStatus_id'];
				$this->Registry_Task = $r[0]['Registry_Task'];
				$this->LpuBuilding_id = $r[0]['LpuBuilding_id'];
				$this->PayType_id = $r[0]['PayType_id'];
				$this->Registry_SumPaid = $r[0]['Registry_SumPaid'];
				$this->Registry_CheckStatusDate = $r[0]['Registry_CheckStatusDate'];
				$this->Registry_CheckStatusTFOMSDate = $r[0]['Registry_CheckStatusTFOMSDate'];
				$this->Registry_sendDT = $r[0]['Registry_sendDT'];
				$this->Registry_IsNeedReform = $r[0]['Registry_IsNeedReform'];
				$this->DrugFinance_id = $r[0]['DrugFinance_id'];
				$this->WhsDocumentCostItemType_id = $r[0]['WhsDocumentCostItemType_id'];
				$this->Org_id = $r[0]['Org_id'];
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$where = array();
		$p = array();

		$where[] = 'RegistryType_id_ref.RegistryType_Code = 3'; //3 - Рецепты

		if (isset($filter['Registry_id']) && $filter['Registry_id']) {
			$where[] = 'v_Registry.Registry_id = :Registry_id';
			$p['Registry_id'] = $filter['Registry_id'];
		}
		if (isset($filter['Lpu_id']) && $filter['Lpu_id']) {
			$where[] = 'v_Registry.Lpu_id = :Lpu_id';
			$p['Lpu_id'] = $filter['Lpu_id'];
		}
		if (isset($filter['Registry_begDate']) && $filter['Registry_begDate']) {
			$where[] = 'v_Registry.Registry_begDate = :Registry_begDate';
			$p['Registry_begDate'] = $filter['Registry_begDate'];
		}
		if (isset($filter['Registry_endDate']) && $filter['Registry_endDate']) {
			$where[] = 'v_Registry.Registry_endDate = :Registry_endDate';
			$p['Registry_endDate'] = $filter['Registry_endDate'];
		}
		if (isset($filter['KatNasel_id']) && $filter['KatNasel_id']) {
			$where[] = 'v_Registry.KatNasel_id = :KatNasel_id';
			$p['KatNasel_id'] = $filter['KatNasel_id'];
		}
		if (isset($filter['Registry_Num']) && $filter['Registry_Num']) {
			$where[] = 'v_Registry.Registry_Num = :Registry_Num';
			$p['Registry_Num'] = $filter['Registry_Num'];
		}
		if (isset($filter['Registry_accDate']) && $filter['Registry_accDate']) {
			$where[] = 'v_Registry.Registry_accDate = :Registry_accDate';
			$p['Registry_accDate'] = $filter['Registry_accDate'];
		}
		if (isset($filter['RegistryStatus_id']) && $filter['RegistryStatus_id']) {
			$where[] = 'v_Registry.RegistryStatus_id = :RegistryStatus_id';
			$p['RegistryStatus_id'] = $filter['RegistryStatus_id'];
		}
		if (isset($filter['Registry_Sum']) && $filter['Registry_Sum']) {
			$where[] = 'v_Registry.Registry_Sum = :Registry_Sum';
			$p['Registry_Sum'] = $filter['Registry_Sum'];
		}
		if (isset($filter['Registry_IsActive']) && $filter['Registry_IsActive']) {
			$where[] = 'v_Registry.Registry_IsActive = :Registry_IsActive';
			$p['Registry_IsActive'] = $filter['Registry_IsActive'];
		}
		if (isset($filter['Registry_ErrorCount']) && $filter['Registry_ErrorCount']) {
			$where[] = 'v_Registry.Registry_ErrorCount = :Registry_ErrorCount';
			$p['Registry_ErrorCount'] = $filter['Registry_ErrorCount'];
		}
		if (isset($filter['Registry_ErrorCommonCount']) && $filter['Registry_ErrorCommonCount']) {
			$where[] = 'v_Registry.Registry_ErrorCommonCount = :Registry_ErrorCommonCount';
			$p['Registry_ErrorCommonCount'] = $filter['Registry_ErrorCommonCount'];
		}
		if (isset($filter['Registry_RecordCount']) && $filter['Registry_RecordCount']) {
			$where[] = 'v_Registry.Registry_RecordCount = :Registry_RecordCount';
			$p['Registry_RecordCount'] = $filter['Registry_RecordCount'];
		}
		if (isset($filter['OrgRSchet_id']) && $filter['OrgRSchet_id']) {
			$where[] = 'v_Registry.OrgRSchet_id = :OrgRSchet_id';
			$p['OrgRSchet_id'] = $filter['OrgRSchet_id'];
		}
		if (isset($filter['Registry_ExportPath']) && $filter['Registry_ExportPath']) {
			$where[] = 'v_Registry.Registry_ExportPath = :Registry_ExportPath';
			$p['Registry_ExportPath'] = $filter['Registry_ExportPath'];
		}
		if (isset($filter['Registry_expDT']) && $filter['Registry_expDT']) {
			$where[] = 'v_Registry.Registry_expDT = :Registry_expDT';
			$p['Registry_expDT'] = $filter['Registry_expDT'];
		}
		if (isset($filter['RegistryStacType_id']) && $filter['RegistryStacType_id']) {
			$where[] = 'v_Registry.RegistryStacType_id = :RegistryStacType_id';
			$p['RegistryStacType_id'] = $filter['RegistryStacType_id'];
		}
		if (isset($filter['Registry_RecordPaidCount']) && $filter['Registry_RecordPaidCount']) {
			$where[] = 'v_Registry.Registry_RecordPaidCount = :Registry_RecordPaidCount';
			$p['Registry_RecordPaidCount'] = $filter['Registry_RecordPaidCount'];
		}
		if (isset($filter['Registry_KdCount']) && $filter['Registry_KdCount']) {
			$where[] = 'v_Registry.Registry_KdCount = :Registry_KdCount';
			$p['Registry_KdCount'] = $filter['Registry_KdCount'];
		}
		if (isset($filter['Registry_KdPaidCount']) && $filter['Registry_KdPaidCount']) {
			$where[] = 'v_Registry.Registry_KdPaidCount = :Registry_KdPaidCount';
			$p['Registry_KdPaidCount'] = $filter['Registry_KdPaidCount'];
		}
		if (isset($filter['Registry_xmlExportPath']) && $filter['Registry_xmlExportPath']) {
			$where[] = 'v_Registry.Registry_xmlExportPath = :Registry_xmlExportPath';
			$p['Registry_xmlExportPath'] = $filter['Registry_xmlExportPath'];
		}
		if (isset($filter['Registry_xmlExpDT']) && $filter['Registry_xmlExpDT']) {
			$where[] = 'v_Registry.Registry_xmlExpDT = :Registry_xmlExpDT';
			$p['Registry_xmlExpDT'] = $filter['Registry_xmlExpDT'];
		}
		if (isset($filter['RegistryCheckStatus_id']) && $filter['RegistryCheckStatus_id']) {
			$where[] = 'v_Registry.RegistryCheckStatus_id = :RegistryCheckStatus_id';
			$p['RegistryCheckStatus_id'] = $filter['RegistryCheckStatus_id'];
		}
		if (isset($filter['Registry_Task']) && $filter['Registry_Task']) {
			$where[] = 'v_Registry.Registry_Task = :Registry_Task';
			$p['Registry_Task'] = $filter['Registry_Task'];
		}
		if (isset($filter['LpuBuilding_id']) && $filter['LpuBuilding_id']) {
			$where[] = 'v_Registry.LpuBuilding_id = :LpuBuilding_id';
			$p['LpuBuilding_id'] = $filter['LpuBuilding_id'];
		}
		if (isset($filter['PayType_id']) && $filter['PayType_id']) {
			$where[] = 'v_Registry.PayType_id = :PayType_id';
			$p['PayType_id'] = $filter['PayType_id'];
		}
		if (isset($filter['Registry_SumPaid']) && $filter['Registry_SumPaid']) {
			$where[] = 'v_Registry.Registry_SumPaid = :Registry_SumPaid';
			$p['Registry_SumPaid'] = $filter['Registry_SumPaid'];
		}
		if (isset($filter['Registry_CheckStatusDate']) && $filter['Registry_CheckStatusDate']) {
			$where[] = 'v_Registry.Registry_CheckStatusDate = :Registry_CheckStatusDate';
			$p['Registry_CheckStatusDate'] = $filter['Registry_CheckStatusDate'];
		}
		if (isset($filter['Registry_CheckStatusTFOMSDate']) && $filter['Registry_CheckStatusTFOMSDate']) {
			$where[] = 'v_Registry.Registry_CheckStatusTFOMSDate = :Registry_CheckStatusTFOMSDate';
			$p['Registry_CheckStatusTFOMSDate'] = $filter['Registry_CheckStatusTFOMSDate'];
		}
		if (isset($filter['Registry_sendDT']) && $filter['Registry_sendDT']) {
			$where[] = 'v_Registry.Registry_sendDT = :Registry_sendDT';
			$p['Registry_sendDT'] = $filter['Registry_sendDT'];
		}
		if (isset($filter['Registry_IsNeedReform']) && $filter['Registry_IsNeedReform']) {
			$where[] = 'v_Registry.Registry_IsNeedReform = :Registry_IsNeedReform';
			$p['Registry_IsNeedReform'] = $filter['Registry_IsNeedReform'];
		}
		if (isset($filter['DrugFinance_id']) && $filter['DrugFinance_id']) {
			$where[] = 'v_Registry.DrugFinance_id = :DrugFinance_id';
			$p['DrugFinance_id'] = $filter['DrugFinance_id'];
		}
		if (isset($filter['WhsDocumentCostItemType_id']) && $filter['WhsDocumentCostItemType_id']) {
			$where[] = 'v_Registry.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
			$p['WhsDocumentCostItemType_id'] = $filter['WhsDocumentCostItemType_id'];
		}
		if (isset($filter['Org_id']) && $filter['Org_id']) {
			$where[] = 'v_Registry.Org_id = :Org_id';
			$p['Org_id'] = $filter['Org_id'];
		}
		if (isset($filter['Registry_Date']) && $filter['Registry_Date']) {
			$where[] = ':Registry_Date between v_Registry.Registry_begDate and v_Registry.Registry_endDate';
			$p['Registry_Date'] = $filter['Registry_Date'];
		}
		if (isset($filter['Registry_insDT']) && $filter['Registry_insDT']) {
			$where[] = 'cast(v_Registry.Registry_insDT as date) = cast(:Registry_insDT as date)';
			$p['Registry_insDT'] = $filter['Registry_insDT'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			select
				v_Registry.Registry_id,
				FinDocument.FinDocument_id,
				v_Registry.RegistryType_id, v_Registry.Lpu_id,
				convert(varchar(10),v_Registry.Registry_begDate, 104) as Registry_begDate,
				convert(varchar(10),v_Registry.Registry_endDate, 104) as Registry_endDate,
				v_Registry.KatNasel_id, v_Registry.Registry_Num, v_Registry.Registry_accDate, v_Registry.RegistryStatus_id, v_Registry.Registry_Sum, v_Registry.Registry_IsActive, v_Registry.Registry_ErrorCount, v_Registry.Registry_ErrorCommonCount, v_Registry.Registry_RecordCount,
				v_Registry.OrgRSchet_id,
				isnull(Org_id_ref.Org_Name, Org.Org_Name) as Org_Name,
				v_Registry.Registry_ExportPath, v_Registry.Registry_expDT, v_Registry.RegistryStacType_id, v_Registry.Registry_RecordPaidCount, v_Registry.Registry_KdCount, v_Registry.Registry_KdPaidCount, v_Registry.Registry_xmlExportPath, v_Registry.Registry_xmlExpDT, v_Registry.RegistryCheckStatus_id, v_Registry.Registry_Task, v_Registry.LpuBuilding_id, v_Registry.PayType_id, v_Registry.Registry_SumPaid, v_Registry.Registry_CheckStatusDate, v_Registry.Registry_CheckStatusTFOMSDate, v_Registry.Registry_sendDT, v_Registry.Registry_IsNeedReform,
				v_Registry.DrugFinance_id, v_Registry.WhsDocumentCostItemType_id, v_Registry.Org_id,
				Lpu_id_ref.Lpu_Name Lpu_id_Name, KatNasel_id_ref.KatNasel_Name KatNasel_id_Name,
				RegistryStatus_id_ref.RegistryStatus_Code RegistryStatus_Code,
				RegistryStatus_id_ref.RegistryStatus_Name RegistryStatus_Name,
				Registry_IsActive_ref.YesNo_Name Registry_IsActive_Name, OrgRSchet_id_ref.OrgRSchet_Name OrgRSchet_id_Name, RegistryStacType_id_ref.RegistryStacType_Name RegistryStacType_id_Name, RegistryCheckStatus_id_ref.RegistryCheckStatus_Name RegistryCheckStatus_id_Name, LpuBuilding_id_ref.LpuBuilding_Name LpuBuilding_id_Name, PayType_id_ref.PayType_Name PayType_id_Name, Registry_IsNeedReform_ref.YesNo_Name Registry_IsNeedReform_Name,
				DrugFinance_id_ref.DrugFinance_Name DrugFinance_id_Name, WhsDocumentCostItemType_id_ref.WhsDocumentCostItemType_Name WhsDocumentCostItemType_id_Name, Org_id_ref.Org_Name Org_id_Name,
				FinDocument.FinDocument_Number,
				convert(varchar(10),FinDocument.FinDocument_Date, 104) as FinDocument_Date,
				FinDocument.FinDocument_Sum,
				(
					select
						sum(fd.FinDocument_Sum)
					from
						FinDocument fd with (nolock)
					where
						fd.Registry_id = v_Registry.Registry_id and
						fd.FinDocumentType_id = 2 -- Платежные поручения
				) as FinDocumentSpec_Sum,
				supply_doc.Num_List as WhsDocumentSupply_Num
			from
				{$this->schema}.v_Registry with (nolock)
				left join dbo.v_FinDocument FinDocument with (nolock) on FinDocument.Registry_id = v_Registry.Registry_id and FinDocument.FinDocumentType_id = 1 -- Счет
				left join dbo.v_Lpu Lpu_id_ref with (nolock) on Lpu_id_ref.Lpu_id = v_Registry.Lpu_id
				left join dbo.v_KatNasel KatNasel_id_ref with (nolock) on KatNasel_id_ref.KatNasel_id = v_Registry.KatNasel_id
				left join dbo.v_RegistryStatus RegistryStatus_id_ref with (nolock) on RegistryStatus_id_ref.RegistryStatus_id = v_Registry.RegistryStatus_id
				left join dbo.v_YesNo Registry_IsActive_ref with (nolock) on Registry_IsActive_ref.YesNo_id = v_Registry.Registry_IsActive
				left join dbo.v_OrgRSchet OrgRSchet_id_ref with (nolock) on OrgRSchet_id_ref.OrgRSchet_id = v_Registry.OrgRSchet_id
				left join dbo.v_Org Org with (nolock) on Org.Org_id = OrgRSchet_id_ref.Org_id
				left join dbo.v_RegistryStacType RegistryStacType_id_ref with (nolock) on RegistryStacType_id_ref.RegistryStacType_id = v_Registry.RegistryStacType_id
				left join dbo.v_RegistryCheckStatus RegistryCheckStatus_id_ref with (nolock) on RegistryCheckStatus_id_ref.RegistryCheckStatus_id = v_Registry.RegistryCheckStatus_id
				left join dbo.v_LpuBuilding LpuBuilding_id_ref with (nolock) on LpuBuilding_id_ref.LpuBuilding_id = v_Registry.LpuBuilding_id
				left join dbo.v_PayType PayType_id_ref with (nolock) on PayType_id_ref.PayType_id = v_Registry.PayType_id
				left join dbo.v_YesNo Registry_IsNeedReform_ref with (nolock) on Registry_IsNeedReform_ref.YesNo_id = v_Registry.Registry_IsNeedReform
				left join dbo.v_DrugFinance DrugFinance_id_ref with (nolock) on DrugFinance_id_ref.DrugFinance_id = v_Registry.DrugFinance_id
				left join dbo.v_WhsDocumentCostItemType WhsDocumentCostItemType_id_ref with (nolock) on WhsDocumentCostItemType_id_ref.WhsDocumentCostItemType_id = v_Registry.WhsDocumentCostItemType_id
				left join dbo.v_Org Org_id_ref with (nolock) on Org_id_ref.Org_id = v_Registry.Org_id
				left join dbo.v_RegistryType RegistryType_id_ref with (nolock) on RegistryType_id_ref.RegistryType_id = v_Registry.RegistryType_id
				outer apply (
					select
						replace(replace((
							select distinct
								wdu.WhsDocumentUc_Num+',' as 'data()'
							from
								{$this->schema}.v_RegistryDataRecept rdr with (nolock)
								inner join WhsDocumentUc wdu with (nolock) on wdu.WhsDocumentUc_id = rdr.WhsDocumentSupply_id
							where
								rdr.Registry_id = v_Registry.Registry_id
							for xml path('')
						)+',,', ',,,', ''), ',,', '') as Num_List
				) supply_doc
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save() {
		$procedure = 'p_Registry_ins';
		if ( $this->Registry_id > 0 ) {
			$procedure = 'p_Registry_upd';
		}
		$q = "
			declare
				@Registry_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Registry_Num varchar(10) = :Registry_Num;

			if (@Registry_Num is null)
			begin
				set @Registry_Num = (
					select
						max(
							cast(
								case
									when isnumeric(Registry_Num) = 1 then Registry_Num
									else '0'
								end
							as numeric)
						) + 1
					from
						{$this->schema}.Registry
				)
			end

			set @Registry_id = :Registry_id;

			exec {$this->schema}." . $procedure . "
				@Registry_id = @Registry_id output,
				@RegistryType_id = :RegistryType_id,
				@Lpu_id = :Lpu_id,
				@Registry_begDate = :Registry_begDate,
				@Registry_endDate = :Registry_endDate,
				@KatNasel_id = :KatNasel_id,
				@Registry_Num = @Registry_Num,
				@Registry_accDate = :Registry_accDate,
				@RegistryStatus_id = :RegistryStatus_id,
				@Registry_Sum = :Registry_Sum,
				@Registry_IsActive = :Registry_IsActive,
				@Registry_ErrorCount = :Registry_ErrorCount,
				@Registry_ErrorCommonCount = :Registry_ErrorCommonCount,
				@Registry_RecordCount = :Registry_RecordCount,
				@OrgRSchet_id = :OrgRSchet_id,
				@Registry_ExportPath = :Registry_ExportPath,
				@Registry_expDT = :Registry_expDT,
				@RegistryStacType_id = :RegistryStacType_id,
				@Registry_RecordPaidCount = :Registry_RecordPaidCount,
				@Registry_KdCount = :Registry_KdCount,
				@Registry_KdPaidCount = :Registry_KdPaidCount,
				@Registry_xmlExportPath = :Registry_xmlExportPath,
				@Registry_xmlExpDT = :Registry_xmlExpDT,
				@RegistryCheckStatus_id = :RegistryCheckStatus_id,
				@Registry_Task = :Registry_Task,
				@LpuBuilding_id = :LpuBuilding_id,
				@PayType_id = :PayType_id,
				@Registry_SumPaid = :Registry_SumPaid,
				@Registry_CheckStatusDate = :Registry_CheckStatusDate,
				@Registry_CheckStatusTFOMSDate = :Registry_CheckStatusTFOMSDate,
				@Registry_sendDT = :Registry_sendDT,
				@Registry_IsNeedReform = :Registry_IsNeedReform,
				@DrugFinance_id = :DrugFinance_id,
				@WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id,
				@Org_id = :Org_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Registry_id as Registry_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'Registry_id' => $this->Registry_id,
			'RegistryType_id' => $this->RegistryType_id,
			'Lpu_id' => $this->Lpu_id,
			'Registry_begDate' => $this->Registry_begDate,
			'Registry_endDate' => $this->Registry_endDate,
			'KatNasel_id' => $this->KatNasel_id,
			'Registry_Num' => $this->Registry_Num,
			'Registry_accDate' => $this->Registry_accDate,
			'RegistryStatus_id' => $this->RegistryStatus_id,
			'Registry_Sum' => $this->Registry_Sum,
			'Registry_IsActive' => $this->Registry_IsActive,
			'Registry_ErrorCount' => $this->Registry_ErrorCount,
			'Registry_ErrorCommonCount' => $this->Registry_ErrorCommonCount,
			'Registry_RecordCount' => $this->Registry_RecordCount,
			'OrgRSchet_id' => $this->OrgRSchet_id,
			'Registry_ExportPath' => $this->Registry_ExportPath,
			'Registry_expDT' => $this->Registry_expDT,
			'RegistryStacType_id' => $this->RegistryStacType_id,
			'Registry_RecordPaidCount' => $this->Registry_RecordPaidCount,
			'Registry_KdCount' => $this->Registry_KdCount,
			'Registry_KdPaidCount' => $this->Registry_KdPaidCount,
			'Registry_xmlExportPath' => $this->Registry_xmlExportPath,
			'Registry_xmlExpDT' => $this->Registry_xmlExpDT,
			'RegistryCheckStatus_id' => $this->RegistryCheckStatus_id,
			'Registry_Task' => $this->Registry_Task,
			'LpuBuilding_id' => $this->LpuBuilding_id,
			'PayType_id' => $this->PayType_id,
			'Registry_SumPaid' => $this->Registry_SumPaid,
			'Registry_CheckStatusDate' => $this->Registry_CheckStatusDate,
			'Registry_CheckStatusTFOMSDate' => $this->Registry_CheckStatusTFOMSDate,
			'Registry_sendDT' => $this->Registry_sendDT,
			'Registry_IsNeedReform' => $this->Registry_IsNeedReform,
			'DrugFinance_id' => $this->DrugFinance_id,
			'WhsDocumentCostItemType_id' => $this->WhsDocumentCostItemType_id,
			'Org_id' => $this->Org_id,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		//print getDebugSQL($q, $p); die;
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->Registry_id = $result[0]['Registry_id'];
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление
	 */
	function delete() {
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec {$this->schema}.p_Registry_del
				@Registry_id = :Registry_id,
				@pmUser_delID = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			 'Registry_id' => $this->Registry_id
			,'pmUser_id' => $this->pmUser_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение рецепта
	 */
	function saveRegistryDataRecept($data) {
		$procedure = 'p_RegistryDataRecept_ins';
		if ( $data['RegistryDataRecept_id'] > 0 ) {
			$procedure = 'p_RegistryDataRecept_upd';
		}
		$q = "
			declare
				@RegistryDataRecept_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @RegistryDataRecept_id = :RegistryDataRecept_id;
			exec {$this->schema}." . $procedure . "
				@RegistryDataRecept_id = @RegistryDataRecept_id output,
				@Registry_id = :Registry_id,
				@RegistryDataRecept_Snils = :RegistryDataRecept_Snils,
				@Polis_Ser = :Polis_Ser,
				@Polis_Num = :Polis_Num,
				@RegistryDataRecept_SurName = :RegistryDataRecept_SurName,
				@RegistryDataRecept_FirName = :RegistryDataRecept_FirName,
				@RegistryDataRecept_SecName = :RegistryDataRecept_SecName,
				@Sex_id = :Sex_id,
				@RegistryDataRecept_BirthDay = :RegistryDataRecept_BirthDay,
				@PrivilegeType_id = :PrivilegeType_id,
				@Document_Ser = :Document_Ser,
				@Document_Num = :Document_Num,
				@DocumentType_id = :DocumentType_id,
				@OmsSprTerr_id = :OmsSprTerr_id,
				@OrgSmo_id = :OrgSmo_id,
				@OrgSmo_OGRN = :OrgSmo_OGRN,
				@RegistryDataRecept_UAddOKATO = :RegistryDataRecept_UAddOKATO,
				@Lpu_id = :Lpu_id,
				@Lpu_OGRN = :Lpu_OGRN,
				@Lpu_f003mcod = :Lpu_f003mcod,
				@MedPersonalRec_id = :MedPersonalRec_id,
				@Diag_id = :Diag_id,
				@RegistryDataRecept_Ser = :RegistryDataRecept_Ser,
				@RegistryDataRecept_Num = :RegistryDataRecept_Num,
				@RegistryDataRecept_setDT = :RegistryDataRecept_setDT,
				@ReceptFinance_id = :ReceptFinance_id,
				@RegistryDataRecept_Persent = :RegistryDataRecept_Persent,
				@OrgFarmacy_id = :OrgFarmacy_id,
				@OrgFarmacy_OGRN = :OrgFarmacy_OGRN,
				@RegistryDataRecept_DrugNomCode = :RegistryDataRecept_DrugNomCode,
				@RegistryDataRecept_DrugKolvo = :RegistryDataRecept_DrugKolvo,
				@RegistryDataRecept_DrugDose = :RegistryDataRecept_DrugDose,
				@RegistryDataRecept_DrugCode = :RegistryDataRecept_DrugCode,
				@RegistryDataRecept_obrDate = :RegistryDataRecept_obrDate,
				@RegistryDataRecept_otpDate = :RegistryDataRecept_otpDate,
				@RegistryDataRecept_Price = :RegistryDataRecept_Price,
				@RegistryDataRecept_SchetType = :RegistryDataRecept_SchetType,
				@RegistryDataRecept_ProtoKEK = :RegistryDataRecept_ProtoKEK,
				@RegistryDataRecept_SpecialCase = :RegistryDataRecept_SpecialCase,
				@RegistryDataRecept_ReceptId = :RegistryDataRecept_ReceptId,
				@WhsDocumentSupply_id = :WhsDocumentSupply_id,
				@RegistryType_id = :RegistryType_id,
				@RegistryRecept_id = :RegistryRecept_id,
				@RegistryRecept_pid = :RegistryRecept_pid,
				@ReceptStatusFLKMEK_id = :ReceptStatusFLKMEK_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @RegistryDataRecept_id as RegistryDataRecept_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'RegistryDataRecept_id' => isset($data['RegistryDataRecept_id']) ? $data['RegistryDataRecept_id'] : null,
			'Registry_id' => isset($data['Registry_id']) ? $data['Registry_id'] : null,
			'RegistryDataRecept_Snils' => isset($data['RegistryDataRecept_Snils']) ? $data['RegistryDataRecept_Snils'] : null,
			'Polis_Ser' => isset($data['Polis_Ser']) ? $data['Polis_Ser'] : null,
			'Polis_Num' => isset($data['Polis_Num']) ? $data['Polis_Num'] : null,
			'RegistryDataRecept_SurName' => isset($data['RegistryDataRecept_SurName']) ? $data['RegistryDataRecept_SurName'] : null,
			'RegistryDataRecept_FirName' => isset($data['RegistryDataRecept_FirName']) ? $data['RegistryDataRecept_FirName'] : null,
			'RegistryDataRecept_SecName' => isset($data['RegistryDataRecept_SecName']) ? $data['RegistryDataRecept_SecName'] : null,
			'Sex_id' => isset($data['Sex_id']) ? $data['Sex_id'] : null,
			'RegistryDataRecept_BirthDay' => isset($data['RegistryDataRecept_BirthDay']) ? $data['RegistryDataRecept_BirthDay'] : null,
			'PrivilegeType_id' => isset($data['PrivilegeType_id']) ? $data['PrivilegeType_id'] : null,
			'Document_Ser' => isset($data['Document_Ser']) ? $data['Document_Ser'] : null,
			'Document_Num' => isset($data['Document_Num']) ? $data['Document_Num'] : null,
			'DocumentType_id' => isset($data['DocumentType_id']) ? $data['DocumentType_id'] : null,
			'OmsSprTerr_id' => isset($data['OmsSprTerr_id']) ? $data['OmsSprTerr_id'] : null,
			'OrgSmo_id' => isset($data['OrgSmo_id']) ? $data['OrgSmo_id'] : null,
			'OrgSmo_OGRN' => isset($data['OrgSmo_OGRN']) ? $data['OrgSmo_OGRN'] : null,
			'RegistryDataRecept_UAddOKATO' => isset($data['RegistryDataRecept_UAddOKATO']) ? $data['RegistryDataRecept_UAddOKATO'] : null,
			'Lpu_id' => isset($data['Lpu_id']) ? $data['Lpu_id'] : null,
			'Lpu_OGRN' => $data['Lpu_OGRN'],
			'Lpu_f003mcod' => isset($data['Lpu_f003mcod']) ? $data['Lpu_f003mcod'] : null,
			'MedPersonalRec_id' => isset($data['MedPersonalRec_id']) ? $data['MedPersonalRec_id'] : null,
			'Diag_id' => isset($data['Diag_id']) ? $data['Diag_id'] : null,
			'RegistryDataRecept_Ser' => $data['RegistryDataRecept_Ser'],
			'RegistryDataRecept_Num' => $data['RegistryDataRecept_Num'],
			'RegistryDataRecept_setDT' => isset($data['RegistryDataRecept_setDT']) ? $data['RegistryDataRecept_setDT'] : null,
			'ReceptFinance_id' => isset($data['ReceptFinance_id']) ? $data['ReceptFinance_id'] : null,
			'RegistryDataRecept_Persent' => isset($data['RegistryDataRecept_Persent']) ? $data['RegistryDataRecept_Persent'] : null,
			'OrgFarmacy_id' => isset($data['OrgFarmacy_id']) ? $data['OrgFarmacy_id'] : null,
			'OrgFarmacy_OGRN' => isset($data['OrgFarmacy_OGRN']) ? $data['OrgFarmacy_OGRN'] : null,
			'RegistryDataRecept_DrugNomCode' => isset($data['RegistryDataRecept_DrugNomCode']) ? $data['RegistryDataRecept_DrugNomCode'] : null,
			'RegistryDataRecept_DrugKolvo' => isset($data['RegistryDataRecept_DrugKolvo']) ? $data['RegistryDataRecept_DrugKolvo'] : null,
			'RegistryDataRecept_DrugDose' => isset($data['RegistryDataRecept_DrugDose']) ? $data['RegistryDataRecept_DrugDose'] : null,
			'RegistryDataRecept_DrugCode' => isset($data['RegistryDataRecept_DrugCode']) ? $data['RegistryDataRecept_DrugCode'] : null,
			'RegistryDataRecept_obrDate' => isset($data['RegistryDataRecept_obrDate']) ? $data['RegistryDataRecept_obrDate'] : null,
			'RegistryDataRecept_otpDate' => isset($data['RegistryDataRecept_otpDate']) ? $data['RegistryDataRecept_otpDate'] : null,
			'RegistryDataRecept_Price' => isset($data['RegistryDataRecept_Price']) ? $data['RegistryDataRecept_Price'] : null,
			'RegistryDataRecept_SchetType' => isset($data['RegistryDataRecept_SchetType']) ? $data['RegistryDataRecept_SchetType'] : null,
			'RegistryDataRecept_ProtoKEK' => isset($data['RegistryDataRecept_ProtoKEK']) ? $data['RegistryDataRecept_ProtoKEK'] : null,
			'RegistryDataRecept_SpecialCase' => isset($data['RegistryDataRecept_SpecialCase']) ? $data['RegistryDataRecept_SpecialCase'] : null,
			'RegistryDataRecept_ReceptId' => isset($data['RegistryDataRecept_ReceptId']) ? $data['RegistryDataRecept_ReceptId'] : null,
			'WhsDocumentSupply_id' => isset($data['WhsDocumentSupply_id']) ? $data['WhsDocumentSupply_id'] : null,
			'RegistryType_id' => isset($data['RegistryType_id']) ? $data['RegistryType_id'] : null,
			'RegistryRecept_id' => isset($data['RegistryRecept_id']) ? $data['RegistryRecept_id'] : null,
			'RegistryRecept_pid' => isset($data['RegistryRecept_pid']) ? $data['RegistryRecept_pid'] : null,
			'ReceptStatusFLKMEK_id' => isset($data['ReceptStatusFLKMEK_id']) ? $data['ReceptStatusFLKMEK_id'] : null,
			'pmUser_id' => $data['pmUser_id']
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Загрузка списка рецептов
	 */
	function loadRegistryDataReceptList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['Registry_id']) && $filter['Registry_id']) {
			$where[] = 'rdr.Registry_id = :Registry_id';
			$p['Registry_id'] = $filter['Registry_id'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			select
				rdr.RegistryDataRecept_id,
				rdr.RegistryDataRecept_Num,
				rdr.RegistryDataRecept_Ser,
				rsfm.ReceptStatusFLKMEK_Name,
				isnull(ynir.YesNo_Code, 0) as IsReceived,
				rs.RegistryStatus_Code
			from
				{$this->schema}.v_RegistryDataRecept rdr with (nolock)
				left join {$this->schema}.v_Registry r with (nolock) on r.Registry_id = rdr.Registry_id
				left join RegistryStatus rs with (nolock) on rs.RegistryStatus_id = r.RegistryStatus_id
				left join {$this->schema}.v_ReceptStatusFLKMEK rsfm on rsfm.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
				left join v_YesNo ynir with(nolock) on ynir.YesNo_id = rdr.RegistryDataRecept_IsReceived
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка списка статусов FLMEK
	 */
	function loadReceptStatusFLKMEKList($filter) {
		$q = "
			select
				rsfm.ReceptStatusFLKMEK_id,
				rsfm.ReceptStatusFLKMEK_Code,
				rsfm.ReceptStatusFLKMEK_Name
			from
				{$this->schema}.v_ReceptStatusFLKMEK rsfm with (nolock)
			order by
				rsfm.ReceptStatusFLKMEK_Code
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Установка признака "Получен экземпляр рецепта"
	 */
	function setReceptIsReceived($data) {
		$result = array(array('success' => true));

		$q = "
			declare
				@Status_id bigint = null;

			set @Status_id = (select top 1 ReceptStatusFLKMEK_id from ReceptStatusFLKMEK with(nolock) where ReceptStatusFLKMEK_code = :ReceptStatusFLKMEK_code);

			update
				{$this->schema}.RegistryDataRecept
			set
				RegistryDataRecept_IsReceived = (select top 1 YesNo_id from v_YesNo with(nolock) where YesNo_Code = :YesNo_Code),
				ReceptStatusFLKMEK_id = @Status_id
			where
				RegistryDataRecept_id = :RegistryDataRecept_id;
		";
		$p = array(
			'RegistryDataRecept_id' => $data['RegistryDataRecept_id'],
			'YesNo_Code' => $data['IsReceived'] == 'true' ? 1 : 0,
			'ReceptStatusFLKMEK_code' => $data['IsReceived'] == 'true' ? 5 : 3 //3 - годен к оплате; 5 - принят к оплате: рецепт получен;
		);
		$r = $this->db->query($q, $p);

		$q = "
			select
				rs.ReceptStatusFLKMEK_Name
			from
				{$this->schema}.RegistryDataRecept rdr with (nolock)
				left join ReceptStatusFLKMEK rs with(nolock) on rs.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
			where
				rdr.RegistryDataRecept_id = :RegistryDataRecept_id;
		";
		$result[0]['ReceptStatusFLKMEK_Name'] = $this->getFirstResultFromQuery($q, $p);

		$q = "
			declare
				@Registry_id bigint = null,
				@NewStatus_Code bigint = null,
				@total_cnt int = 0,
				@received_cnt int = 0;

			-- получаем ид реестра на оплату
			select @Registry_id = Registry_id from {$this->schema}.RegistryDataRecept where RegistryDataRecept_id = :RegistryDataRecept_id;

			-- получем общее количество рецептов и количество полученных бумажных экземпляров
			select
				@total_cnt = count(rdr.RegistryDataRecept_id),
				@received_cnt = sum(case when yn.YesNo_Code = 1 then 1 else 0 end)
			from
				{$this->schema}.RegistryDataRecept rdr with (nolock)
				left join v_YesNo yn with(nolock) on yn.YesNo_id = rdr.RegistryDataRecept_IsReceived
				left join {$this->schema}.v_ReceptStatusFLKMEK rsfm on rsfm.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
			where
				Registry_id = @Registry_id and
				rsfm.ReceptStatusFLKMEK_Code = 5; -- Принят к оплате

			-- если получены все рецепты готовые к оплате, устанавливаем статус 'К оплате' иначе ставим статус 'Сформирован'
			if (@total_cnt > 0 and @total_cnt = @received_cnt)
				begin
					set @NewStatus_Code = 2; -- К оплате
				end
			else
				begin
					set @NewStatus_Code = 1; -- Сформирован
				end

			-- устанавливаем новый статус реестру на оплату если это необходимо
			if (@NewStatus_Code is not null)
			begin
				-- обновляем данные реестра
				update
					{$this->schema}.Registry
				set
					RegistryStatus_id = (
						select top 1
							RegistryStatus_id
						from
							RegistryStatus with(nolock)
						where
							RegistryStatus_Code = @NewStatus_Code
					)
				where
					Registry_id = @Registry_id;
			end;
		";
		$p = array(
			'RegistryDataRecept_id' => $data['RegistryDataRecept_id']
		);
		$r = $this->db->query($q, $p);
		/*if ( is_object($r) ) {
			$result = $r->result('array');
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}*/

		return $result;
	}

	/**
	 * Установка признака "Получен экземпляр рецепта" для списка рецептов
	 */
	function setAllReceptsIsReceived($data) {
		$result = array(array('success' => true));

		$RegistryDataReceptList = json_decode($data['RegistryDataReceptList'], false);
		$id_list = implode(',', $RegistryDataReceptList);

		$q = "
			declare
				@Status_id bigint = null;

			set @Status_id = (select top 1 ReceptStatusFLKMEK_id from ReceptStatusFLKMEK with(nolock) where ReceptStatusFLKMEK_code = :ReceptStatusFLKMEK_code);

			update
				{$this->schema}.RegistryDataRecept
			set
				RegistryDataRecept_IsReceived = (select top 1 YesNo_id from v_YesNo with(nolock) where YesNo_Code = :YesNo_Code),
				ReceptStatusFLKMEK_id = @Status_id
			where
				RegistryDataRecept_id in (:id_list);
		";
		$p = array(
			'id_list' => $id_list,
			'YesNo_Code' => $data['IsReceived'] == 'true' ? 1 : 0,
			'ReceptStatusFLKMEK_code' => $data['IsReceived'] == 'true' ? 5 : 3 //3 - годен к оплате; 5 - принят к оплате: рецепт получен;
		);
		$r = $this->db->query($q, $p);

		$q = "
			select top 1
				rs.ReceptStatusFLKMEK_Name
			from
				{$this->schema}.RegistryDataRecept rdr with (nolock)
				left join ReceptStatusFLKMEK rs with(nolock) on rs.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
			where
				rdr.RegistryDataRecept_id in (:id_list);
		";
		$result[0]['ReceptStatusFLKMEK_Name'] = $this->getFirstResultFromQuery($q, $p);

		$q = "
			declare
				@Registry_id bigint = null,
				@NewStatus_Code bigint = null,
				@total_cnt int = 0,
				@received_cnt int = 0;

			-- получаем ид реестра на оплату
			select @Registry_id = Registry_id from {$this->schema}.RegistryDataRecept where RegistryDataRecept_id = :RegistryDataRecept_id;

			-- получем общее количество рецептов и количество полученных бумажных экземпляров
			select
				@total_cnt = count(rdr.RegistryDataRecept_id),
				@received_cnt = sum(case when yn.YesNo_Code = 1 then 1 else 0 end)
			from
				{$this->schema}.RegistryDataRecept rdr with (nolock)
				left join v_YesNo yn with(nolock) on yn.YesNo_id = rdr.RegistryDataRecept_IsReceived
				left join {$this->schema}.v_ReceptStatusFLKMEK rsfm on rsfm.ReceptStatusFLKMEK_id = rdr.ReceptStatusFLKMEK_id
			where
				Registry_id = @Registry_id and
				rsfm.ReceptStatusFLKMEK_Code = 5; -- Принят к оплате

			-- если получены все рецепты готовые к оплате, устанавливаем статус 'К оплате' иначе ставим статус 'Сформирован'
			if (@total_cnt > 0 and @total_cnt = @received_cnt)
				begin
					set @NewStatus_Code = 2; -- К оплате
				end
			else
				begin
					set @NewStatus_Code = 1; -- Сформирован
				end

			-- устанавливаем новый статус реестру на оплату если это необходимо
			if (@NewStatus_Code is not null)
			begin
				-- обновляем данные реестра
				update
					{$this->schema}.Registry
				set
					RegistryStatus_id = (
						select top 1
							RegistryStatus_id
						from
							RegistryStatus with(nolock)
						where
							RegistryStatus_Code = @NewStatus_Code
					)
				where
					Registry_id = @Registry_id;
			end;
		";
		$p = array(
			'RegistryDataRecept_id' => $RegistryDataReceptList[0]
		);
		$r = $this->db->query($q, $p);
		/*if ( is_object($r) ) {
			$result = $r->result('array');
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}*/

		return $result;
	}
}