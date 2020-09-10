<?php
defined("BASEPATH") or die ("No direct script access allowed");

/**
 * Class WhsDocumentSupply_model
 * @property CI_DB_driver $db
 */
class WhsDocumentSupply_model extends swPgModel
{
	private $WhsDocumentDelivery_id;//WhsDocumentDelivery_id
	private $WhsDocumentUc_pid;//WhsDocumentUc_pid
	private $WhsDocumentUc_Num;//WhsDocumentUc_Num
	private $WhsDocumentUc_Name;//WhsDocumentUc_Name
	private $WhsDocumentType_id;//WhsDocumentType_id
	private $WhsDocumentUc_Date;//WhsDocumentUc_Date
	private $Org_aid;//Org_aid
	private $Org_sid;//Org_sid
	private $Org_cid;//Org_cid
	private $Org_pid;//Org_pid
	private $Org_rid;//Org_rid
	private $WhsDocumentUc_Sum;//WhsDocumentUc_Sum
	private $WhsDocumentSupply_id;//Идентификатор
	private $WhsDocumentUc_id;//Идентификатор документа учета
	private $WhsDocumentSupply_ProtNum;//Номер протокола аукциона
	private $WhsDocumentSupply_ProtDate;//Дата протокола аукциона
	private $WhsDocumentSupplyType_id;//Тип поставки
	private $WhsDocumentSupply_BegDate;//Дата начала действия
	private $WhsDocumentSupply_ExecDate;//Дата исполнения обязательств Поставщиком
	private $DrugFinance_id;//Источник финансирования
	private $WhsDocumentCostItemType_id;//Статья расходов
	private $BudgetFormType_id;//Целевая статья
	private $WhsDocumentPurchType_id;//Вид закупа
	private $pmUser_id;//Идентификатор пользователя системы Промед
	private $WhsDocumentStatusType_id;//Статус документа (вычисляемое свойство)
	private $FinanceSource_id;//Источник оплаты
	private $DrugNds_id;//Ставка НДС
	private $DrugRequest_id;//Заявка
	private $WhsDocumentSupplySpec_id;//WhsDocumentSupplySpec_id
	private $WhsDocumentDelivery_setDT;//WhsDocumentDelivery_setDT
	private $WhsDocumentDelivery_Kolvo;//WhsDocumentDelivery_Kolvo
	private $Okei_id;//Okei_id

	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI";
	/**
	 * @return mixed
	 */
	public function getWhsDocumentUc_pid()
	{
		return $this->WhsDocumentUc_pid;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentUc_pid($value)
	{
		$this->WhsDocumentUc_pid = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentUc_Num()
	{
		return $this->WhsDocumentUc_Num;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentUc_Num($value)
	{
		$this->WhsDocumentUc_Num = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentUc_Name()
	{
		return $this->WhsDocumentUc_Name;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentUc_Name($value)
	{
		$this->WhsDocumentUc_Name = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentType_id()
	{
		return $this->WhsDocumentType_id;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentType_id($value)
	{
		$this->WhsDocumentType_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentUc_Date()
	{
		return $this->WhsDocumentUc_Date;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentUc_Date($value)
	{
		$this->WhsDocumentUc_Date = $value;
	}

	/**
	 * @return mixed
	 */
	public function getOrg_aid()
	{
		return $this->Org_aid;
	}

	/**
	 * @param $value
	 */
	public function setOrg_aid($value)
	{
		$this->Org_aid = $value;
	}

	/**
	 * @return mixed
	 */
	public function getOrg_sid()
	{
		return $this->Org_sid;
	}

	/**
	 * @param $value
	 */
	public function setOrg_sid($value)
	{
		$this->Org_sid = $value;
	}

	/**
	 * @return mixed
	 */
	public function getOrg_cid()
	{
		return $this->Org_cid;
	}

	/**
	 * @param $value
	 */
	public function setOrg_cid($value)
	{
		$this->Org_cid = $value;
	}

	/**
	 * @return mixed
	 */
	public function getOrg_pid()
	{
		return $this->Org_pid;
	}

	/**
	 * @param $value
	 */
	public function setOrg_pid($value)
	{
		$this->Org_pid = $value;
	}

	/**
	 * @return mixed
	 */
	public function getOrg_rid()
	{
		return $this->Org_rid;
	}

	/**
	 * @param $value
	 */
	public function setOrg_rid($value)
	{
		$this->Org_rid = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentUc_Sum()
	{
		return $this->WhsDocumentUc_Sum;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentUc_Sum($value)
	{
		$this->WhsDocumentUc_Sum = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupply_id()
	{
		return $this->WhsDocumentSupply_id;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupply_id($value)
	{
		$this->WhsDocumentSupply_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentUc_id()
	{
		return $this->WhsDocumentUc_id;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentUc_id($value)
	{
		$this->WhsDocumentUc_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupply_ProtNum()
	{
		return $this->WhsDocumentSupply_ProtNum;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupply_ProtNum($value)
	{
		$this->WhsDocumentSupply_ProtNum = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupply_ProtDate()
	{
		return $this->WhsDocumentSupply_ProtDate;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupply_ProtDate($value)
	{
		$this->WhsDocumentSupply_ProtDate = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupplyType_id()
	{
		return $this->WhsDocumentSupplyType_id;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupplyType_id($value)
	{
		$this->WhsDocumentSupplyType_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupply_BegDate()
	{
		return $this->WhsDocumentSupply_BegDate;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupply_BegDate($value)
	{
		$this->WhsDocumentSupply_BegDate = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentSupply_ExecDate()
	{
		return $this->WhsDocumentSupply_ExecDate;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentSupply_ExecDate($value)
	{
		$this->WhsDocumentSupply_ExecDate = $value;
	}

	/**
	 * @return mixed
	 */
	public function getDrugFinance_id()
	{
		return $this->DrugFinance_id;
	}

	/**
	 * @param $value
	 */
	public function setDrugFinance_id($value)
	{
		$this->DrugFinance_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentCostItemType_id()
	{
		return $this->WhsDocumentCostItemType_id;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentCostItemType_id($value)
	{
		$this->WhsDocumentCostItemType_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getBudgetFormType_id()
	{
		return $this->BudgetFormType_id;
	}

	/**
	 * @param $value
	 */
	public function setBudgetFormType_id($value)
	{
		$this->BudgetFormType_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentPurchType_id()
	{
		return $this->WhsDocumentPurchType_id;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentPurchType_id($value)
	{
		$this->WhsDocumentPurchType_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getpmUser_id()
	{
		return $this->pmUser_id;
	}

	/**
	 * @param $value
	 */
	public function setpmUser_id($value)
	{
		$this->pmUser_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentStatusType_id()
	{
		return $this->WhsDocumentStatusType_id;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentStatusType_id($value)
	{
		$this->WhsDocumentStatusType_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getFinanceSource_id()
	{
		return $this->FinanceSource_id;
	}

	/**
	 * @param $value
	 */
	public function setFinanceSource_id($value)
	{
		$this->FinanceSource_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getDrugNds_id()
	{
		return $this->DrugNds_id;
	}

	/**
	 * @param $value
	 */
	public function setDrugNds_id($value)
	{
		$this->DrugNds_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getDrugRequest_id()
	{
		return $this->DrugRequest_id;
	}

	/**
	 * @param $value
	 */
	public function setDrugRequest_id($value)
	{
		$this->DrugRequest_id = $value;
	}

	/**
	 * @throws Exception
	 */
	function __construct()
	{
		parent::__construct();
		if (!isset($_SESSION["pmuser_id"])) {
			throw new Exception("Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)");
		}
		$this->setpmUser_id($_SESSION["pmuser_id"]);
		//установка региональной схемы
		$config = get_config();
		$this->schema = $config["regions"][getRegionNumber()]["schema"];
	}

	/**
	 * Подписание ГК
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function sign($data)
	{
		$suppliers_ostat_control = !empty($data["options"]["drugcontrol"]["suppliers_ostat_control"]);
		// Стартуем транзакцию
		$this->beginTransaction();
		$org_sid = null; // идентификатор организации поставщика
		$type_code = null; // код типа ГК
		// Получаем идентификатор организации соответствующей минздраву
		$mzorg_id = $this->getMinzdravDloOrgId();
		// Получаем информацию о ГК
		$query = "
			select
				wds.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				wds.WhsDocumentType_id as \"WhsDocumentType_id\",
				wds.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				to_char(wds.WhsDocumentUc_Date, '{$this->dateTimeForm104}') \"WhsDocumentUc_Date\",
				wds.WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\",
				wdst.WhsDocumentStatusType_Name as \"WhsDocumentStatusType_Name\",
				wds.Org_sid as \"Org_sid\",
				wds.Org_pid as \"Org_pid\",
				wds.Org_rid as \"Org_rid\",
				wds.Org_cid as \"Org_cid\",
				ot_r.OrgType_id as \"OrgType_rCode\",
				wdt.WhsDocumentType_Code as \"WhsDocumentType_Code\"
			from
				v_WhsDocumentSupply wds
				left join v_WhsDocumentStatusType wdst on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id
				left join v_WhsDocumentType wdt on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
				left join v_Org o_r on  o_r.Org_id = wds.Org_rid
				left join v_OrgType ot_r on ot_r.OrgType_id = o_r.OrgType_id
			where wds.WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
		$queryParams = ["WhsDocumentSupply_id" => $data["WhsDocumentSupply_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка запроса данных о ГК");
		}
		// если WhsDocumentType_id = 3 (Контракт на поставку) или WhsDocumentType_id = 6 (Контракт на поставку и отпуск), то need_xp_DrugOstatRegistry = true
		$WhsDocumentSupply = $result->result("array");
		if (empty($WhsDocumentSupply[0]["WhsDocumentSupply_id"])) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка получения данных о ГК");
		} else {
			if (!empty($WhsDocumentSupply[0]["WhsDocumentStatusType_id"]) && !in_array($WhsDocumentSupply[0]["WhsDocumentStatusType_id"], [1])) {
				$this->rollbackTransaction();
				throw new Exception("Нельзя подписать документ со статусом {$WhsDocumentSupply[0]["WhsDocumentStatusType_Name"]}");
			}
			$supply_data = $WhsDocumentSupply[0];
			$type_code = $WhsDocumentSupply[0]["WhsDocumentType_Code"];
		}
		// Обновляем статус документа
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_whsdocumentuc_sign(
			    whsdocumentuc_id := :WhsDocumentUc_id,
			    whsdocumentstatustype_id := 2,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"WhsDocumentUc_id" => $WhsDocumentSupply[0]["WhsDocumentUc_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка запроса обновления статуса документа");
		}
		$WhsDocumentUcStatus = $result->result("array");
		if (!empty($WhsDocumentUcStatus[0]["Error_Msg"])) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка обновления статуса документа");
		}
		//определяем для каких организаций нужно создать остатки
		$org_array = [];
		//создаются остатки поставщика, если задано настройками
		if ($suppliers_ostat_control && !empty($supply_data["Org_sid"])) {
			$org_array[] = $supply_data["Org_sid"];
		}
		//создаются остатки Минздрава, если заказчик или плательщик является Минздравом
		if (($supply_data["Org_cid"] == $mzorg_id || $supply_data["Org_cid"] == $mzorg_id) && !empty($mzorg_id)) {
			$org_array[] = $mzorg_id;
		}
		$org_array = array_unique($org_array);
		if (in_array($type_code, [3, 6]) && count($org_array) > 0) {
			//дальнейшие действия производятся только для типов "Контракт на поставку" и "Контракт на поставку и отпуск", а также при условии что список организаций для которых нужно создать остатки не пуст
			//генерация уникального имени партии
			$ds_name = null;
			$query = "
                select count(DrugShipment_id) as cnt
                from DrugShipment ds
                where DrugShipment_Name = :DrugShipment_Name;
            ";
			for ($i = 0; $i < 5; $i++) {
				$tmp_ds_name = $WhsDocumentSupply[0]["WhsDocumentUc_Num"] . ($i > 0 ? "/" . $i : "") . " от " . $WhsDocumentSupply[0]["WhsDocumentUc_Date"];
				$ds_count = $this->getFirstResultFromQuery($query, ["DrugShipment_Name" => $tmp_ds_name]);
				if ($ds_count == 0) {
					$ds_name = $tmp_ds_name;
					break;
				}
			}
			if (empty($ds_name)) {
				$ds_name = "{$WhsDocumentSupply[0]["WhsDocumentUc_Num"]}/{$data["WhsDocumentSupply_id"]} от {$WhsDocumentSupply[0]["WhsDocumentUc_Date"]}";
			}
			// 1) Создать партию по указанному ГК (dbo.DrugShipment)
			$query = "
				select
				    drugshipment_id as \"DrugShipment_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_drugshipment_ins(
				    drugshipment_setdt := tzgetdate(),
				    drugshipment_name := :DrugShipment_Name,
				    whsdocumentsupply_id := :WhsDocumentSupply_id,
				    accounttype_id := 0,
				    drugshipment_pid := 0,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"WhsDocumentSupply_id" => $data["WhsDocumentSupply_id"],
				"DrugShipment_Name" => $ds_name,
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				$this->rollbackTransaction();
				throw new Exception("Ошибка запроса создания партии по ГК");
			}
			$DrugShipment = $result->result("array");
			if (empty($DrugShipment[0]["DrugShipment_id"])) {
				$this->rollbackTransaction();
				throw new Exception("Ошибка создания партии по ГК");
			}
			// 2) Сформировать регистр остатков по субсчету Доступно
			// получаем строки из WhsDocumentSupplySpec
			$query = "
				select
					wdss.Drug_id as \"Drug_id\",
					wdss.Okei_id as \"Okei_id\",
					wdss.WhsDocumentSupplySpec_KolvoUnit as \"WhsDocumentSupplySpec_KolvoUnit\",
					wdss.WhsDocumentSupplySpec_SumNDS as \"WhsDocumentSupplySpec_SumNDS\",
					wdss.WhsDocumentSupplySpec_PriceNDS as \"WhsDocumentSupplySpec_PriceNDS\"
				from v_WhsDocumentSupplySpec wdss
				where wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id
			";
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				$this->rollbackTransaction();
				throw new Exception("Ошибка запроса получения данных из спецификации документа");
			}
			$WhsDocumentSupplySpec = $result->result("array");
			// для каждой строки WhsDocumentSupplySpec вызываем xp_DrugOstatRegistry_count
			foreach ($WhsDocumentSupplySpec as $WhsDocumentSupplySpecOne) {
				if (empty($WhsDocumentSupplySpecOne["Drug_id"])) {
					$this->rollbackTransaction();
					throw new Exception("Подписание не возможно, т.к. не указан медикамент");
				}
				$query = "
					select
					    error_code as \"Error_Code\",
					    error_message as \"Error_Msg\"
					from xp_drugostatregistry_count(
					    contragent_id := null,
					    org_id := :Org_id,
					    drugshipment_id := :DrugShipment_id,
					    drug_id := :Drug_id,
					    prepseries_id := null,
					    subaccounttype_id := 1,
					    okei_id := :Okei_id,
					    drugostatregistry_kolvo := :DrugOstatRegistry_Kolvo,
					    drugostatregistry_sum := :DrugOstatRegistry_Sum,
					    drugostatregistry_cost := :DrugOstatRegistry_Cost,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"DrugShipment_id" => $DrugShipment[0]["DrugShipment_id"],
					"Drug_id" => $WhsDocumentSupplySpecOne["Drug_id"],
					"Okei_id" => $WhsDocumentSupplySpecOne["Okei_id"],
					"DrugOstatRegistry_Kolvo" => $WhsDocumentSupplySpecOne["WhsDocumentSupplySpec_KolvoUnit"],
					"DrugOstatRegistry_Sum" => $WhsDocumentSupplySpecOne["WhsDocumentSupplySpec_SumNDS"],
					"DrugOstatRegistry_Cost" => $WhsDocumentSupplySpecOne["WhsDocumentSupplySpec_PriceNDS"],
					"pmUser_id" => $data["pmUser_id"]
				];
				//создаем остатки для организаций
				foreach ($org_array as $org_id) {
					$queryParams["Org_id"] = $org_id;
					$result = $this->db->query($query, $queryParams);
					if (!is_object($result)) {
						$this->rollbackTransaction();
						throw new Exception("Ошибка запроса создания регистра остатков");
					}
					$DrugOstatRegistry = $result->result("array");
					if (!empty($DrugOstatRegistry[0]["Error_Msg"])) {
						$this->rollbackTransaction();
						throw new Exception("Ошибка создания регистра остатков");
					}
				}
			}
		}
		//если поставщик еще не зарегистрирован в системе как контрагент, добавляем новый контрагент
		$result = $this->saveContragent([
			"Org_id" => $supply_data["Org_sid"],
			"ContragentType_Code" => 1,
			"Server_id" => $data["Server_id"],
			"pmUser" => $data["pmUser_id"]
		]);
		if (empty($result["Contragent_id"]) || !empty($result["Error_Msg"])) {
			$this->rollbackTransaction();
			throw new Exception((!empty($result["Error_Msg"])) ? $result["Error_Msg"] : "Ошибка сохранения контрагента");
		} else {
			$result = $this->saveContragentOrg([
				"Org_id" => $supply_data["Org_pid"],
				"Contragent_id" => $result["Contragent_id"],
				"pmUser" => $data["pmUser_id"]
			]);
			if (!empty($result["Error_Msg"])) {
				$this->rollbackTransaction();
				throw new Exception($result["Error_Msg"]);
			}
		}
		if ($mzorg_id > 0 && $supply_data["Org_pid"] == $mzorg_id && $supply_data["OrgType_rCode"] == 5) {
			//если плательщик - минздрав, а получатель - РАС
			$result = $this->saveContragent([
				"Org_id" => $supply_data["Org_rid"],
				"ContragentType_Code" => 6,
				"Server_id" => $data["Server_id"],
				"pmUser" => $data["pmUser_id"]
			]);
			if (empty($result["Contragent_id"]) || !empty($result["Error_Msg"])) {
				$this->rollbackTransaction();
				throw new Exception((!empty($result["Error_Msg"])) ? $result["Error_Msg"] : "Ошибка сохранения контрагента");
			} else {
				$result = $this->saveContragentOrg([
					"Org_id" => $mzorg_id,
					"Contragent_id" => $result["Contragent_id"],
					"pmUser" => $data["pmUser_id"]
				]);
				if (!empty($result["Error_Msg"])) {
					$this->rollbackTransaction();
					throw new Exception($result["Error_Msg"]);
				}
			}
		}
		$this->commitTransaction();
		return [["Error_Msg" => ""]];
	}

	/**
	 * Снятие подписания с ГК
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function unsign($data)
	{
		// Стартуем транзакцию
		$this->beginTransaction();
		// Получаем информацию о ГК
		$query = "
			select
				wds.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				wds.WhsDocumentType_id as \"WhsDocumentType_id\",
				wds.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				to_char(wds.WhsDocumentUc_Date, '{$this->dateTimeForm104}') as \"WhsDocumentUc_Date\",
				wds.WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\",
				wdst.WhsDocumentStatusType_Name as \"WhsDocumentStatusType_Name\",
				wds.Org_sid as \"Org_sid\",
				wds.Org_pid as \"Org_pid\",
				wds.Org_rid as \"Org_rid\",
				wds.Org_cid as \"Org_cid\",
				ot_r.OrgType_id as \"OrgType_rCode\",
				wdt.WhsDocumentType_Code as \"WhsDocumentType_Code\"
			from
				v_WhsDocumentSupply wds
				left join v_WhsDocumentStatusType wdst on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id
				left join v_WhsDocumentType wdt on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
				left join v_Org o_r on  o_r.Org_id = wds.Org_rid
				left join v_OrgType ot_r on ot_r.OrgType_id = o_r.OrgType_id
			where wds.WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
		$queryParams = ["WhsDocumentSupply_id" => $data["WhsDocumentSupply_id"]];
		$supply_data = $this->getFirstRowFromQuery($query, $queryParams);
		if (count($supply_data) < 1) {
			throw new Exception("Ошибка получения данных о ГК");
		}
		//прорверка статуса
		if ($supply_data["WhsDocumentStatusType_id"] != 2) {
			throw new Exception("Нельзя снять подписание с документа со статусом {$supply_data["WhsDocumentStatusType_Name"]}");
		}
		//проверка наличия доп. соглашений, документов учета или разнарядок связанных с ГК
		$query = "
        	select
            	(select count(du.DocumentUc_id) as cnt from v_DocumentUc du where du.WhsDocumentUc_id = :WhsDocumentUc_id) as doc_cnt,
            	(
                	select count(dus.DocumentUcStr_id) as cnt
                    from
                        v_DrugShipment ds
                        left join v_DrugShipmentLink dsl on dsl.DrugShipment_id = ds.DrugShipment_id
                        inner join v_DocumentUcStr dus on dus.DocumentUcStr_id = dsl.DocumentUcStr_id
                    where ds.WhsDocumentSupply_id = :WhsDocumentSupply_id
                ) as str_cnt,
				(select count(wdoad.WhsDocumentOrderAllocationDrug_id) as cnt from v_WhsDocumentOrderAllocationDrug wdoad where wdoad.WhsDocumentUc_pid = :WhsDocumentUc_id) as alc_cnt,
                (select count(wdord.WhsDocumentOrderReserveDrug_id) as cnt from v_WhsDocumentOrderReserveDrug wdord where wdord.WhsDocumentUc_pid = :WhsDocumentUc_id) as res_cnt,
				(
                	select count(wds.WhsDocumentSupply_id) as cnt
                    from
                        v_WhsDocumentSupply wds
                        left join v_WhsDocumentType wdt on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
                    where wds.WhsDocumentUc_pid = :WhsDocumentUc_id
                      and wdt.WhsDocumentType_Code::int8 = 13
            	) as sup_cnt
		";
		$queryParams = [
			"WhsDocumentUc_id" => $supply_data["WhsDocumentUc_id"],
			"WhsDocumentSupply_id" => $supply_data["WhsDocumentSupply_id"]
		];
		$response = $this->getFirstRowFromQuery($query, $queryParams);
		if (!empty($response["doc_cnt"]) || !empty($response["str_cnt"])) {
			throw new Exception("Снять подпись с контракта не возможно, т.к. есть документы связанные с этим контрактом");
		}
		if (!empty($response["alc_cnt"])) {
			throw new Exception("Снять подпись с контракта не возможно, т.к. есть разнарядки связанные с этим контрактом.");
		}
		if (!empty($response["res_cnt"])) {
			throw new Exception("Снять подпись с контракта не возможно, т.к. есть распоряжения на включение в резерв связанные с этим контрактом");
		}
		if (!empty($response["sup_cnt"])) {
			throw new Exception("К контракту заключены дополнительные соглашения, снять подпись с контракта не возможно");
		}
		//получение списка остатков по партиям
		$query = "
			select
				dor.Org_id as \"Org_id\",
				dor.DrugShipment_id as \"DrugShipment_id\",
				dor.Drug_id as \"Drug_id\",
				dor.Okei_id as \"Okei_id\",
				dor.DrugOstatRegistry_Kolvo as \"DrugOstatRegistry_Kolvo\",
				dor.DrugOstatRegistry_Sum as \"DrugOstatRegistry_Sum\",
				dor.DrugOstatRegistry_Cost as \"DrugOstatRegistry_Cost\"
            from
				v_DrugShipment ds
				inner join v_DrugOstatRegistry dor on dor.DrugShipment_id = ds.DrugShipment_id
				left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
			where ds.WhsDocumentSupply_id = :WhsDocumentSupply_id
			  and sat.SubAccountType_Code = 1
			  and dor.DrugOstatRegistry_Kolvo > 0
		";
		$queryParams = ["WhsDocumentSupply_id" => $supply_data["WhsDocumentSupply_id"]];
		$drug_array = $this->queryResult($query, $queryParams);
		//перерасчет остатков
		if (is_array($drug_array)) {
			foreach ($drug_array as $drug_data) {
				$query = "
					select
					    error_code as \"Error_Code\",
					    error_message as \"Error_Msg\"
					from xp_drugostatregistry_count(
					    contragent_id := null,
					    org_id := :Org_id,
					    drugshipment_id := :DrugShipment_id,
					    drug_id := :Drug_id,
					    prepseries_id := null,
					    subaccounttype_id := 1,
					    okei_id := :Okei_id,
					    drugostatregistry_kolvo := :DrugOstatRegistry_Kolvo,
					    drugostatregistry_sum := :DrugOstatRegistry_Sum,
					    drugostatregistry_cost := :DrugOstatRegistry_Cost,
					    pmuser_id := :pmUser_id
					);
                ";
				$queryParams = [
					"Org_id" => $drug_data["Org_id"],
					"DrugShipment_id" => $drug_data["DrugShipment_id"],
					"Drug_id" => $drug_data["Drug_id"],
					"Okei_id" => $drug_data["Okei_id"],
					"DrugOstatRegistry_Kolvo" => $drug_data["DrugOstatRegistry_Kolvo"] * (-1),
					"DrugOstatRegistry_Sum" => $drug_data["DrugOstatRegistry_Sum"] * (-1),
					"DrugOstatRegistry_Cost" => $drug_data["DrugOstatRegistry_Cost"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$response = $this->getFirstRowFromQuery($query, $queryParams);
				if (!empty($response["Error_Msg"])) {
					$this->rollbackTransaction();
					throw new Exception($response["Error_Msg"]);
				}
			}
		}
		
        // Обновляем статус документа
        $save_result = $this->saveObject('WhsDocumentUc', array(
            'WhsDocumentUc_id' => $supply_data['WhsDocumentUc_id'],
            'WhsDocumentStatusType_id' => 1, //1 - Новый
            'pmUser_id' => $data['pmUser_id']
        ));

        if (empty($save_result["WhsDocumentUc_id"])) {
            $this->rollbackTransaction();
            throw new Exception("Ошибка запроса обновления статуса документа");
        } else if (!empty($save_result["Error_Msg"])) {
            $this->rollbackTransaction();
            throw new Exception($save_result["Error_Msg"]);
        }
        
		$this->commitTransaction();
        
		return [['Error_Msg' => '']];
	}

	/**
	 * Подписание дополнительного соглашения
	 * @param $data
	 * @return array
	 */
	function signWhsDocumentSupplyAdditional($data)
	{
		$result = ["Error_Msg" => null];
		$ost_edit_enabled = false; //по умолчанию изменение регистра остатков отключено
		$suppliers_ostat_control = !empty($data["options"]["drugcontrol"]["suppliers_ostat_control"]);
		$sat_id = $this->getObjectIdByCode("SubAccountType", 1); // 1 - Доступно
		$okei_id = $this->getObjectIdByCode("Okei", 778); // 778 - Упаковка
		// Получаем идентификатор организации соответствующей минздраву
		$mzorg_id = $this->getMinzdravDloOrgId();
		// Стартуем транзакцию
		$this->beginTransaction();
		try {
			// получение информации о доп. соглашении
			$query = "
				select
					wds.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
					wds.WhsDocumentUc_id as \"WhsDocumentUc_id\",
					p_wds.WhsDocumentSupply_id as \"ParentWhsDocumentSupply_id\",
					p_wds.WhsDocumentUc_pid as \"ParentWhsDocumentUc_pid\",
					ds.DrugShipment_id as \"DrugShipment_id\",
					wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\",
					(case when p_wdt.WhsDocumentType_Code = '3' then p_wds.Org_sid else p_wds.Org_cid end) as \"Org_id\", -- если ГК на поставку, то по лицевому счету Поставщика; ГК на поставку и отпуск, то по лицевому счету Минздрава
					p_wds.Org_sid as \"Org_sid\", -- поставщик (родительский контракт)
					p_wds.Org_cid as \"Org_cid\" -- заказчик (родительский контракт)
				from
					v_WhsDocumentSupply wds
					left join v_WhsDocumentSupply p_wds on p_wds.WhsDocumentUc_id = wds.WhsDocumentUc_pid
					left join v_WhsDocumentType p_wdt on p_wdt.WhsDocumentType_id = p_wds.WhsDocumentType_id
					left join v_WhsDocumentStatusType wdst on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id
					left join lateral (
						select i_ds.DrugShipment_id
						from v_DrugShipment i_ds
						where i_ds.WhsDocumentSupply_id = p_wds.WhsDocumentSupply_id
						order by DrugShipment_id desc
						limit 1
					) ds on true
				where wds.WhsDocumentSupply_id = :WhsDocumentSupply_id
			";
			$queryParams = ["WhsDocumentSupply_id" => $data["WhsDocumentSupply_id"]];
			$supply_data = $this->getFirstRowFromQuery($query, $queryParams);
			if (!is_array($supply_data) || count($supply_data) < 1) {
				throw new Exception("Ошибка получения данных о дополнительном соглашении");
			}
			if ($supply_data["Org_cid"] == $mzorg_id) {
				//если заказчик по контракту Минздрав региона, то при исполнении доп. соглашения, производится перерасчет остатков
				$ost_edit_enabled = true;
			}
			//определяем для каких организаций нужно отредактировать остатки
			$org_array = [];
			//редактируются остатки поставщика, если задано настройками
			if ($suppliers_ostat_control && !empty($supply_data["Org_sid"])) {
				$org_array[] = $supply_data["Org_sid"];
			}
			//редактируются остатки Минздрава, если заказчик или плательщик является Минздравом
			if (($supply_data["Org_cid"] == $mzorg_id || $supply_data["Org_cid"] == $mzorg_id) && !empty($mzorg_id)) {
				$org_array[] = $mzorg_id;
			}
			$org_array = array_unique($org_array);
			if (empty($supply_data["DrugShipment_id"])) {
				throw new Exception("Не найдена партия для родительского контракта");
			}
			if ($supply_data["WhsDocumentStatusType_Code"] == 2) {
				throw new Exception("Документ уже подписан");
			}
			// обработка списка организаций
			foreach ($org_array as $org_id) {
				// получение списка различий между спецификациями контракта и доп. соглашения, также получение даныых об остатках
				$query = "
					with wdss as (
						select
							Drug_id,
							WhsDocumentSupplySpec_PriceNDS as Price,
							sum(WhsDocumentSupplySpec_KolvoUnit) as Kolvo
						from v_WhsDocumentSupplySpec wdss
						where wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id
						group by
							Drug_id,
							WhsDocumentSupplySpec_PriceNDS
					),
					p_wdss as (
						select
							Drug_id,
							WhsDocumentSupplySpec_PriceNDS as Price,
							sum(WhsDocumentSupplySpec_KolvoUnit) as Kolvo
						from v_WhsDocumentSupplySpec
						where WhsDocumentSupply_id = :ParentWhsDocumentSupply_id
						group by
							Drug_id,
							WhsDocumentSupplySpec_PriceNDS
					)
					select
						coalesce(wdss.Drug_id, p_wdss.Drug_id) as \"Drug_id\",
						coalesce(wdss.Price, p_wdss.Price) as \"Price\",
						(coalesce(wdss.Kolvo, 0) - coalesce(p_wdss.Kolvo, 0)) as \"Kolvo\",
						(coalesce(dor.DrugOstatRegistry_Kolvo, 0) + coalesce(wdss.Kolvo, 0) - coalesce(p_wdss.Kolvo, 0)) as \"CheckKolvo\",
						dor.Contragent_id as \"Contragent_id\",
						dor.Storage_id as \"Storage_id\",
						dor.PrepSeries_id as \"PrepSeries_id\",
						dor.Okei_id as \"Okei_id\"
					from
						wdss
						full outer join p_wdss on wdss.Drug_id = p_wdss.Drug_id and wdss.Price = p_wdss.Price
						left join lateral (
							select
								i_dor.DrugOstatRegistry_Kolvo,
								i_dor.Contragent_id,
								i_dor.Storage_id,
								i_dor.PrepSeries_id,
								i_dor.Okei_id
							from v_DrugOstatRegistry i_dor
							where i_dor.SubAccountType_id = :SubAccountType_id
							  and i_dor.DrugShipment_id = :DrugShipment_id
							  and i_dor.Drug_id = p_wdss.Drug_id
							  and i_dor.DrugOstatRegistry_Cost = p_wdss.Price
							  and i_dor.Org_id = :Org_id
							limit 1
						) as dor on true
					where coalesce(wdss.Kolvo, 0) <> coalesce(p_wdss.Kolvo, 0);
				";
				$queryParams = [
					"WhsDocumentSupply_id" => $data["WhsDocumentSupply_id"],
					"ParentWhsDocumentSupply_id" => $supply_data["ParentWhsDocumentSupply_id"],
					"SubAccountType_id" => $sat_id,
					"DrugShipment_id" => $supply_data["DrugShipment_id"],
					"Org_id" => $org_id
				];
				$diff_array = $this->queryResult($query, $queryParams);
				//проверка возможности внесения изменений в регистр остатков
				if ($ost_edit_enabled) {
					foreach ($diff_array as $diff) {
						if (!empty($diff["CheckKolvo"]) && $diff["CheckKolvo"] < 0) {
							$err_msg = "Исполнение дополнительного соглашения невозможно: ЛС, исключаемые из контракта уже ";
							$err_msg .= ($org_id == $mzorg_id ? "выданы в разнарядку на поставку." : "поставлены.");
							throw new Exception($err_msg);
							break;
						}
					}
				}
				//редактирование регистра остатков
				if ($ost_edit_enabled) {
					foreach ($diff_array as $diff) {
						$query = "
							select
							    error_code as \"Error_Code\",
							    error_message as \"Error_Msg\"
							from xp_drugostatregistry_count(
							    contragent_id := :Contragent_id,
							    org_id := :Org_id,
							    drugshipment_id := :DrugShipment_id,
							    drug_id := :Drug_id,
							    prepseries_id := :PrepSeries_id,
							    subaccounttype_id := :SubAccountType_id,
							    okei_id := :Okei_id,
							    drugostatregistry_kolvo := :DrugOstatRegistry_Kolvo,
							    drugostatregistry_sum := :DrugOstatRegistry_Sum,
							    storage_id := null,
							    drugostatregistry_cost := :DrugOstatRegistry_Cost,
							    pmuser_id := :pmUser_id
							);
						";
						$queryParams = [
							"Contragent_id" => $diff["Contragent_id"],
							"Org_id" => $org_id,
							"DrugShipment_id" => $supply_data["DrugShipment_id"],
							"Drug_id" => $diff["Drug_id"],
							"PrepSeries_id" => $diff["PrepSeries_id"],
							"SubAccountType_id" => $sat_id,
							"Okei_id" => !empty($diff["Okei_id"]) ? $diff["Okei_id"] : $okei_id,
							"DrugOstatRegistry_Kolvo" => $diff["Kolvo"],
							"DrugOstatRegistry_Sum" => $diff["Kolvo"] * $diff["Price"],
							"DrugOstatRegistry_Cost" => $diff["Price"],
							"pmUser_id" => $data["pmUser_id"]
						];
						$query_result = $this->getFirstRowFromQuery($query, $queryParams);
						if ($query_result === false || !empty($query_result["Error_Msg"])) {
							throw new Exception("При рассчете остатков произошла ошибка.");
							break;
						}
					}
				}
			}
			//получение данных спецификации контракта
			$query = "
				select
					wdss.WhsDocumentSupplySpec_id as \"OldSpec_id\",
					add_wdss.WhsDocumentSupplySpec_id as \"DopSpec_id\"
				from
					v_WhsDocumentSupplySpec wdss
					left join lateral (
						select WhsDocumentSupplySpec_id
						from v_WhsDocumentSupplySpec i_wdss
						where i_wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id
						  and i_wdss.Drug_id = wdss.Drug_id
						  and coalesce(i_wdss.WhsDocumentSupplySpec_PriceNDS, 0) = coalesce(wdss.WhsDocumentSupplySpec_PriceNDS, 0)
						order by WhsDocumentSupplySpec_id
						limit 1
					) as add_wdss on true
				where wdss.WhsDocumentSupply_id = :ParentWhsDocumentSupply_id;
			";
			$queryParams = [
				"ParentWhsDocumentSupply_id" => $supply_data["ParentWhsDocumentSupply_id"],
				"WhsDocumentSupply_id" => $supply_data["WhsDocumentSupply_id"]
			];
			$supply_spec = $this->queryResult($query, $queryParams);
			//получение данных спецификации доп. соглашения
			$query = "
				select
					WhsDocumentSupplySpec_id as \"WhsDocumentSupplySpec_id\",
					Drug_id as \"Drug_id\",
					WhsDocumentSupplySpec_Price as \"WhsDocumentSupplySpec_Price\",
					WhsDocumentSupplySpec_PriceNDS as \"WhsDocumentSupplySpec_PriceNDS\",
					WhsDocumentSupplySpec_SuppPrice as \"WhsDocumentSupplySpec_SuppPrice\"
				from v_WhsDocumentSupplySpec
				where WhsDocumentSupply_id = :WhsDocumentSupply_id;
			";
			$queryParams = ["WhsDocumentSupply_id" => $supply_data["WhsDocumentSupply_id"]];
			$additional_spec = $this->queryResult($query, $queryParams);
			//сохранение изменений спецификации контракта в архиве
			foreach ($additional_spec as $spec) {
				$query = "
					select
					    whsdocumentucpricehistory_id as \"WhsDocumentUcPriceHistory_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Msg\"
					from p_whsdocumentucpricehistory_ins(
					    whsdocumentucpricehistory_id := :WhsDocumentUcPriceHistory_id,
					    whsdocumentuc_id := :WhsDocumentUc_id,
					    drug_id := :Drug_id,
					    whsdocumentucpricehistory_price := :WhsDocumentUcPriceHistory_Price,
					    whsdocumentucpricehistory_pricends := :WhsDocumentUcPriceHistory_PriceNDS,
					    whsdocumentuc_sid := :WhsDocumentUc_sid,
					    whsdocumentucpricehistory_suppprice := :WhsDocumentUcPriceHistory_SuppPrice,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"WhsDocumentUcPriceHistory_id" => null,
					"WhsDocumentUc_id" => $supply_data["WhsDocumentUc_id"],
					"Drug_id" => $spec["Drug_id"],
					"WhsDocumentUcPriceHistory_Price" => $spec["WhsDocumentSupplySpec_Price"],
					"WhsDocumentUcPriceHistory_PriceNDS" => $spec["WhsDocumentSupplySpec_PriceNDS"],
					"WhsDocumentUc_sid" => $supply_data["ParentWhsDocumentUc_pid"],
					"WhsDocumentUcPriceHistory_SuppPrice" => !empty($spec["WhsDocumentSupplySpec_SuppPrice"]) ? $spec["WhsDocumentSupplySpec_SuppPrice"] : null,
					"pmUser_id" => $data["pmUser_id"]
				];
				$response = $this->getFirstRowFromQuery($query, $queryParams);
				if (!empty($response["Error_Msg"])) {
					throw new Exception($response["Error_Msg"]);
				}
			}
			//перезапись спецификации контракта
			//копирование спецификации из доп соглашения а контракт
			foreach ($additional_spec as $spec) {
				$query = "
					select
					    whsdocumentsupply_id,
					    whsdocumentsupplyspec_poscode,
					    drugcomplexmnn_id,
					    firmnames_id,
					    whsdocumentsupplyspec_kolvoform,
					    drugpack_id,
					    okei_id,
					    whsdocumentsupplyspec_kolvounit,
					    whsdocumentsupplyspec_count,
					    whsdocumentsupplyspec_price,
					    whsdocumentsupplyspec_nds,
					    whsdocumentsupplyspec_sumnds,
					    whsdocumentsupplyspec_pricends,
					    whsdocumentsupplyspec_shelflifepersent,
					    drug_id,
					    whsdocumentprocurementrequestspec_id,
					    drug_did,
					    goodsunit_id,
					    whsdocumentsupplyspec_goodsunitqty,
					    whsdocumentsupplyspec_suppprice,
					    retailmarkup_id,
					    commercialofferdrug_id,
					    drugnds_id,
					    drugrequestpurchasespec_id
					from whsdocumentsupplyspec
					where whsdocumentsupplyspec_id = {$spec["WhsDocumentSupplySpec_id"]}
				";
				$tempRow = $this->getFirstRowFromQuery($query);
				$query = "
					select
					    whsdocumentsupplyspec_id as \"WhsDocumentSupplySpec_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Msg\"
					from p_whsdocumentsupplyspec_ins(
					    whsdocumentsupply_id := :WhsDocumentSupply_id,
					    whsdocumentsupplyspec_poscode := :whsdocumentsupplyspec_poscode,
					    drugcomplexmnn_id := :drugcomplexmnn_id,
					    firmnames_id := :firmnames_id,
					    whsdocumentsupplyspec_kolvoform := :whsdocumentsupplyspec_kolvoform,
					    drugpack_id := :drugpack_id,
					    okei_id := :okei_id,
					    whsdocumentsupplyspec_kolvounit := :whsdocumentsupplyspec_kolvounit,
					    whsdocumentsupplyspec_count := :whsdocumentsupplyspec_count,
					    whsdocumentsupplyspec_price := :whsdocumentsupplyspec_price,
					    whsdocumentsupplyspec_nds := :whsdocumentsupplyspec_nds,
					    whsdocumentsupplyspec_sumnds := :whsdocumentsupplyspec_sumnds,
					    whsdocumentsupplyspec_pricends := :whsdocumentsupplyspec_pricends,
					    whsdocumentsupplyspec_shelflifepersent := :whsdocumentsupplyspec_shelflifepersent,
					    drug_id := :drug_id,
					    whsdocumentprocurementrequestspec_id := :whsdocumentprocurementrequestspec_id,
					    drug_did := :drug_did,
					    goodsunit_id := :goodsunit_id,
					    whsdocumentsupplyspec_goodsunitqty := :whsdocumentsupplyspec_goodsunitqty,
					    whsdocumentsupplyspec_suppprice := :whsdocumentsupplyspec_suppprice,
					    retailmarkup_id := :retailmarkup_id,
					    commercialofferdrug_id := :commercialofferdrug_id,
					    drugnds_id := :drugnds_id,
					    drugrequestpurchasespec_id := :drugrequestpurchasespec_id,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"whsdocumentsupplyspec_poscode" => (string)$tempRow["whsdocumentsupplyspec_poscode"],
					"drugcomplexmnn_id" => $tempRow["drugcomplexmnn_id"],
					"firmnames_id" => $tempRow["firmnames_id"],
					"whsdocumentsupplyspec_kolvoform" => $tempRow["whsdocumentsupplyspec_kolvoform"],
					"drugpack_id" => $tempRow["drugpack_id"],
					"okei_id" => $tempRow["okei_id"],
					"whsdocumentsupplyspec_kolvounit" => $tempRow["whsdocumentsupplyspec_kolvounit"],
					"whsdocumentsupplyspec_count" => $tempRow["whsdocumentsupplyspec_count"],
					"whsdocumentsupplyspec_price" => $tempRow["whsdocumentsupplyspec_price"],
					"whsdocumentsupplyspec_nds" => $tempRow["whsdocumentsupplyspec_nds"],
					"whsdocumentsupplyspec_sumnds" => $tempRow["whsdocumentsupplyspec_sumnds"],
					"whsdocumentsupplyspec_pricends" => $tempRow["whsdocumentsupplyspec_pricends"],
					"whsdocumentsupplyspec_shelflifepersent" => $tempRow["whsdocumentsupplyspec_shelflifepersent"],
					"drug_id" => $tempRow["drug_id"],
					"whsdocumentprocurementrequestspec_id" => $tempRow["whsdocumentprocurementrequestspec_id"],
					"drug_did" => $tempRow["drug_did"],
					"goodsunit_id" => $tempRow["goodsunit_id"],
					"whsdocumentsupplyspec_goodsunitqty" => $tempRow["whsdocumentsupplyspec_goodsunitqty"],
					"whsdocumentsupplyspec_suppprice" => $tempRow["whsdocumentsupplyspec_suppprice"],
					"retailmarkup_id" => $tempRow["retailmarkup_id"],
					"commercialofferdrug_id" => $tempRow["commercialofferdrug_id"],
					"drugnds_id" => $tempRow["drugnds_id"],
					"drugrequestpurchasespec_id" => $tempRow["drugrequestpurchasespec_id"],
					"WhsDocumentSupply_id" => $supply_data["ParentWhsDocumentSupply_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$response = $this->getFirstRowFromQuery($query, $queryParams);
				//сопоставляем идентификаторы новых строк ГК со старыми
				if (!empty($response["WhsDocumentSupplySpec_id"])) {
					foreach ($supply_spec as $key => $s_spec) {
						if ($s_spec["DopSpec_id"] == $spec["WhsDocumentSupplySpec_id"]) {
							$supply_spec[$key]["NewSpec_id"] = $response["WhsDocumentSupplySpec_id"];
						}
					}
				}
			}
			//удаляем старую спецификацию ГК, по возможности сохраняя график поставки
			foreach ($supply_spec as $spec) {
				//удаление или редактирование строки графика поставки
				//удаление или редактирование списка синонимов
				if (!empty($spec['NewSpec_id']) && $spec['NewSpec_id'] > 0) {
					$query = "
						update WhsDocumentDelivery
						set
							WhsDocumentSupplySpec_id = :NewWhsDocumentSupplySpec_id,
							WhsDocumentDelivery_updDT = dbo.tzGetDate(),
							pmUser_updID = :pmUser_id
						where WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id;
					";
					$queryParams = [
						"WhsDocumentSupplySpec_id" => $spec["OldSpec_id"],
						"NewWhsDocumentSupplySpec_id" => $spec["NewSpec_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->db->query($query, $queryParams);
					$query = "
						update WhsDocumentSupplySpecDrug
						set
							WhsDocumentSupplySpec_id = :NewWhsDocumentSupplySpec_id,
							WhsDocumentSupplySpecDrug_updDT = dbo.tzGetDate(),
							pmUser_updID = :pmUser_id
						where WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id;
					";
					$queryParams = [
						"WhsDocumentSupplySpec_id" => $spec["OldSpec_id"],
						"NewWhsDocumentSupplySpec_id" => $spec["NewSpec_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->db->query($query, $queryParams);
				} else {
					$query = "
						delete from WhsDocumentDelivery
						where WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id;
					";
					$queryParams = ["WhsDocumentSupplySpec_id" => $spec["OldSpec_id"]];
					$this->db->query($query, $queryParams);
					$query = "
						delete from WhsDocumentSupplySpecDrug
						where WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id;
					";
					$queryParams = ["WhsDocumentSupplySpec_id" => $spec["OldSpec_id"]];
					$this->db->query($query, $queryParams);
				}
				//удаление строки из спецификации ГК
				$funcParams = [
					"WhsDocumentSupplySpec_id" => $spec["OldSpec_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$response = $this->deleteObject("WhsDocumentSupplySpec", $funcParams);
				if (!empty($response["Error_Msg"])) {
					throw new Exception($response["Error_Msg"]);
				}
			}
			//изменение статуса доп. соглашения
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_whsdocumentsupply_upd(
                	whsdocumentstatustype_id := :WhsDocumentStatusType_id,
                    whsdocumentsupply_id := :WhsDocumentSupply_id,
                    pmuser_id := :pmUser_id
            	);
			";
			$queryParams = [
				"WhsDocumentSupply_id" => $supply_data["WhsDocumentSupply_id"],
				"WhsDocumentStatusType_id" => $this->getObjectIdByCode("WhsDocumentStatusType", 2),
				"pmUser_id" => $data["pmUser_id"]
			];
			$response = $this->getFirstRowFromQuery($query, $queryParams);
			if (!empty($response["Error_Msg"])) {
				throw new Exception($response["Error_Msg"]);
			}
			$this->commitTransaction();
		} catch (Exception $e) {
			$result["Error_Msg"] = $e->getMessage();
			$this->rollbackTransaction();
		}
		return $result;
	}

	/**
	 * Загрузка данных ГК
	 * @return array|bool|CI_DB_result
	 */
	function load()
	{
		$query = "
			select
				wds.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
				wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				wds.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				wds.WhsDocumentType_id as \"WhsDocumentType_id\",
				wdt.WhsDocumentType_Name as \"WhsDocumentType_Name\",
				wds.WhsDocumentUc_Date as \"WhsDocumentUc_Date\",
				wds.Org_sid as \"Org_sid\",
				wds.Org_cid as \"Org_cid\",
				wds.Org_pid as \"Org_pid\",
				wds.Org_rid as \"Org_rid\",
				wds.WhsDocumentUc_Sum::decimal(16,2) as \"WhsDocumentUc_Sum\",
				wds.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				wds.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wds.WhsDocumentSupply_ProtNum as \"WhsDocumentSupply_ProtNum\",
				wds.WhsDocumentSupply_ProtDate as \"WhsDocumentSupply_ProtDate\",
				wds.WhsDocumentSupplyType_id as \"WhsDocumentSupplyType_id\",
				wds.WhsDocumentSupply_BegDate as \"WhsDocumentSupply_BegDate\",
				wds.WhsDocumentSupply_ExecDate as \"WhsDocumentSupply_ExecDate\",
				wds.DrugFinance_id as \"DrugFinance_id\",
				wds.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				wds.BudgetFormType_id as \"BudgetFormType_id\",
				wds.FinanceSource_id as \"FinanceSource_id\",
				wds.WhsDocumentPurchType_id as \"WhsDocumentPurchType_id\",
				coalesce(wds.WhsDocumentStatusType_id, 1) as \"WhsDocumentStatusType_id\",
				coalesce(nds.Code, dnds.DrugNds_Code) as \"DrugNds_Code\",
				((wds.WhsDocumentUc_Sum / (100 + nds.Code)) * nds.Code)::decimal(16,2) as \"Nds_Sum\",
				wds.DrugNds_id as \"DrugNds_id\",
				wds.DrugRequest_id as \"DrugRequest_id\",
				case when wdu.WhsDocumentUc_ImportDT is null then 'false' else 'true' end as \"ImportDT_Exists\"
			from
				v_WhsDocumentSupply wds
				left join WhsDocumentType wdt on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
				left join v_DrugNds dnds on dnds.DrugNds_id = wds.DrugNds_id
				left join lateral (
					select WhsDocumentSupplySpec_NDS::decimal(4,0) as Code
					from v_WhsDocumentSupplySpec wdss
					where wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
				    limit 1
				) as nds on true
				left join v_WhsDocumentUc wdu on wdu.WhsDocumentUc_id = wds.WhsDocumentUc_id
			where
				WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
		$queryParams = ["WhsDocumentSupply_id" => $this->WhsDocumentSupply_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (!isset($result[0])) {
			return false;
		}
		$this->WhsDocumentUc_pid = $result[0]["WhsDocumentUc_pid"];
		$this->WhsDocumentUc_Num = $result[0]["WhsDocumentUc_Num"];
		$this->WhsDocumentUc_Name = $result[0]["WhsDocumentUc_Name"];
		$this->WhsDocumentType_id = $result[0]["WhsDocumentType_id"];
		$this->WhsDocumentUc_Date = $result[0]["WhsDocumentUc_Date"];
		$this->Org_sid = $result[0]["Org_sid"];
		$this->Org_cid = $result[0]["Org_cid"];
		$this->Org_pid = $result[0]["Org_pid"];
		$this->Org_rid = $result[0]["Org_rid"];
		$this->WhsDocumentUc_Sum = $result[0]["WhsDocumentUc_Sum"];
		$this->WhsDocumentSupply_id = $result[0]["WhsDocumentSupply_id"];
		$this->WhsDocumentUc_id = $result[0]["WhsDocumentUc_id"];
		$this->WhsDocumentSupply_ProtNum = $result[0]["WhsDocumentSupply_ProtNum"];
		$this->WhsDocumentSupply_ProtDate = $result[0]["WhsDocumentSupply_ProtDate"];
		$this->WhsDocumentSupplyType_id = $result[0]["WhsDocumentSupplyType_id"];
		$this->WhsDocumentSupply_BegDate = $result[0]["WhsDocumentSupply_BegDate"];
		$this->WhsDocumentSupply_ExecDate = $result[0]["WhsDocumentSupply_ExecDate"];
		$this->DrugFinance_id = $result[0]["DrugFinance_id"];
		$this->WhsDocumentCostItemType_id = $result[0]["WhsDocumentCostItemType_id"];
		$this->BudgetFormType_id = $result[0]["BudgetFormType_id"];
		$this->WhsDocumentPurchType_id = $result[0]["WhsDocumentPurchType_id"];
		$this->WhsDocumentStatusType_id = $result[0]["WhsDocumentStatusType_id"];
		$this->FinanceSource_id = $result[0]["FinanceSource_id"];
		$this->DrugNds_id = $result[0]["DrugNds_id"];
		$this->DrugRequest_id = $result[0]["DrugRequest_id"];
		return $result;
	}

	/**
	 * Загрузка данных дополнительного соглашения
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function loadWhsDocumentSupplyAdditional($data)
	{
		$query = "
			select
				wds.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wds.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
				wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				wds.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				wds.WhsDocumentType_id as \"WhsDocumentType_id\",
				wds.WhsDocumentUc_Date as \"WhsDocumentUc_Date\",
				wds.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				wds.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wds.WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\",
				wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\",
				case when wdu.WhsDocumentUc_ImportDT is null then 'false' else 'true' end as \"ImportDT_Exists\",
				coalesce(to_char(wdu.WhsDocumentUc_ImportDT, '{$this->dateTimeForm120}'), '') as \"ImportDT\"
			from
				v_WhsDocumentSupply wds
				left join v_WhsDocumentStatusType wdst on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id
				left join v_WhsDocumentUc wdu on wdu.WhsDocumentUc_id = wds.WhsDocumentUc_id
			where WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
		$queryParams = ["WhsDocumentSupply_id" => $data["WhsDocumentSupply_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return (isset($result[0])) ? $result : false;
	}

	/**
	 * Получение списка ГК
	 * @param $filter
	 * @return array|bool|null
	 */
	function loadList($filter)
	{
		$select = [];
		$join = [];
		$where = [];
		$params = [];
		$region = $_SESSION["region"]["nick"];
		//колонки "Отгружено" и "Оплачено"
		if ($region == "saratov") {
			$select[] = "registry_sum.FinDocument_Sum as \"FinDocument_Sum\"";
			$select[] = "registry_sum.RegistryDataRecept_Sum as \"RegistryDataRecept_Sum\"";
			$join[] = "
                left join lateral (
                    select
                        sum(i_fin_doc.FinDocument_Sum) as \"FinDocument_Sum\",
                        sum(i_rec_data.RegistryDataRecept_Sum) as \"RegistryDataRecept_Sum\"
                    from
                        {$this->schema}.RegistryLLO i_rllo
                        left join lateral (
                            select sum(i_fd.FinDocument_Sum) as \"FinDocument_Sum\"
                            from
                                {$this->schema}.v_RegistryLLOFinDocument i_rllo_fd
                                left join {$this->schema}.FinDocument i_fd on i_fd.FinDocument_id = i_rllo_fd.FinDocument_id
                            where i_rllo_fd.RegistryLLO_id = i_rllo.RegistryLLO_id
                              and i_fd.FinDocumentType_id = :PayFinDocumentType_id
                        ) as i_fin_doc on true
                        left join lateral (
                            select sum(i_rdr.RegistryDataRecept_Sum) as \"RegistryDataRecept_Sum\"
                            from {$this->schema}.v_RegistryDataRecept i_rdr
                            where i_rdr.RegistryLLO_id = i_rllo.RegistryLLO_id
                        ) as i_rec_data on true
                    where
                        i_rllo.RegistryLLO_id in (
                            select i_rdr.RegistryLLO_id
                            from
                                v_WhsDocumentSupply i_wds
                                left join v_DrugShipment i_ds on i_ds.WhsDocumentSupply_id = i_wds.WhsDocumentSupply_id
                                left join v_DrugShipmentlink i_dsl on i_dsl.DrugShipment_id = i_ds.DrugShipment_id
                                left join v_DocumentUcStr i_dus on i_dus.DocumentUcStr_oid = i_dsl.DocumentUcStr_id
                                left join v_DocumentUc i_du on i_du.DocumentUc_id = i_dus.DocumentUc_id
                                left join {$this->schema}.v_RegistryDataRecept i_rdr on i_rdr.ReceptOtov_id = i_dus.ReceptOtov_id
                            where i_wds.WhsDocumentSupply_id = v_WhsDocumentSupply.WhsDocumentSupply_id
                              and i_du.DrugDocumentType_id = :DocRealDrugDocumentType_id
                              and i_rdr.ReceptOtov_id is not null
                        )
                ) as registry_sum on true
            ";
			$params["PayFinDocumentType_id"] = $this->getObjectIdByCode("FinDocumentType", 2); //2 - платежное поручение
			$params["DocRealDrugDocumentType_id"] = $this->getObjectIdByCode("DrugDocumentType", 11); //11 - Реализация
		}
		if (isset($filter["WhsDocumentUc_pid"]) && $filter["WhsDocumentUc_pid"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentUc_pid = :WhsDocumentUc_pid";
			$params["WhsDocumentUc_pid"] = $filter["WhsDocumentUc_pid"];
		}
		if (isset($filter["WhsDocumentUc_Num"]) && $filter["WhsDocumentUc_Num"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentUc_Num like '%'||:WhsDocumentUc_Num||'%'";
			$params["WhsDocumentUc_Num"] = $filter["WhsDocumentUc_Num"];
		}
		if (isset($filter["WhsDocumentUc_Name"]) && $filter["WhsDocumentUc_Name"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentUc_Name = :WhsDocumentUc_Name";
			$params["WhsDocumentUc_Name"] = $filter["WhsDocumentUc_Name"];
		}
		if (isset($filter["WhsDocumentType_id"]) && $filter["WhsDocumentType_id"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentType_id = :WhsDocumentType_id";
			$params["WhsDocumentType_id"] = $filter["WhsDocumentType_id"];
		}
		if (!empty($filter["WhsDocumentType_Code"])) {
			$where[] = "WhsDocumentType_ref.WhsDocumentType_Code::int8 = :WhsDocumentType_Code::int8";
			$params["WhsDocumentType_Code"] = $filter["WhsDocumentType_Code"];
		} else {
			$where[] = "WhsDocumentType_ref.WhsDocumentType_Code::int8 <> 13"; //исключаем доп соглашения из списка
		}
		if (isset($filter["WhsDocumentUc_Date"]) && $filter["WhsDocumentUc_Date"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentUc_Date = :WhsDocumentUc_Date";
			$params["WhsDocumentUc_Date"] = $filter["WhsDocumentUc_Date"];
		}
		if (isset($filter["WhsDocumentUc_DateRange"]) && !empty($filter["WhsDocumentUc_DateRange"][0]) && !empty($filter["WhsDocumentUc_DateRange"][1])) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentUc_Date >= :WhsDocumentUc_Date_startdate::date";
			$where[] = "v_WhsDocumentSupply.WhsDocumentUc_Date <= :WhsDocumentUc_Date_enddate::date";
			$params["WhsDocumentUc_Date_startdate"] = $filter["WhsDocumentUc_DateRange"][0];
			$params["WhsDocumentUc_Date_enddate"] = $filter["WhsDocumentUc_DateRange"][1];
		}
		if (!empty($filter["begDate"])) {
			// дата конца должна быть больше
			$where[] = "(v_WhsDocumentSupply.WhsDocumentSupply_ExecDate >= :begDate::date or v_WhsDocumentSupply.WhsDocumentSupply_ExecDate is null)";
			$params["begDate"] = $filter["begDate"];
		}
		if (!empty($filter["endDate"])) {
			// дата начала должна быть меньше
			$where[] = "v_WhsDocumentSupply.WhsDocumentUc_Date <= :endDate::date";
			$params["endDate"] = $filter["endDate"];
		}
		if (!empty($filter["mode"]) && $filter["mode"] == "supplier") {
			$params["Org_sid"] = isset($filter["session"]["org_id"]) ? $filter["session"]["org_id"] : null;
			// только где текущая организация - поставщик
			$where[] = "v_WhsDocumentSupply.Org_sid = :Org_sid";
		}
		if (isset($filter["Org_sid"]) && $filter["Org_sid"]) {
			$where[] = "v_WhsDocumentSupply.Org_sid = :Org_sid";
			$params["Org_sid"] = $filter["Org_sid"];
		}
		if (isset($filter["Org_cid"]) && $filter["Org_cid"]) {
			$where[] = "v_WhsDocumentSupply.Org_cid = :Org_cid";
			$params["Org_cid"] = $filter["Org_cid"];
		}
		if (isset($filter["Org_pid"]) && $filter["Org_pid"]) {
			$where[] = "v_WhsDocumentSupply.Org_pid = :Org_pid";
			$params["Org_pid"] = $filter["Org_pid"];
		}
		if (isset($filter["Org_rid"]) && $filter["Org_rid"]) {
			$where[] = "v_WhsDocumentSupply.Org_rid = :Org_rid";
			$params["Org_rid"] = $filter["Org_rid"];
		}
		if (isset($filter["WhsDocumentUc_Sum"]) && $filter["WhsDocumentUc_Sum"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentUc_Sum = :WhsDocumentUc_Sum";
			$params["WhsDocumentUc_Sum"] = $filter["WhsDocumentUc_Sum"];
		}
		if (isset($filter["WhsDocumentSupply_id"]) && $filter["WhsDocumentSupply_id"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentSupply_id = :WhsDocumentSupply_id";
			$params["WhsDocumentSupply_id"] = $filter["WhsDocumentSupply_id"];
		}
		if (isset($filter["WhsDocumentUc_id"]) && $filter["WhsDocumentUc_id"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentUc_id = :WhsDocumentUc_id";
			$params["WhsDocumentUc_id"] = $filter["WhsDocumentUc_id"];
		}
		if (isset($filter["WhsDocumentSupply_ProtNum"]) && $filter["WhsDocumentSupply_ProtNum"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentSupply_ProtNum = :WhsDocumentSupply_ProtNum";
			$params["WhsDocumentSupply_ProtNum"] = $filter["WhsDocumentSupply_ProtNum"];
		}
		if (isset($filter["WhsDocumentSupply_ProtDate"]) && $filter["WhsDocumentSupply_ProtDate"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentSupply_ProtDate = :WhsDocumentSupply_ProtDate";
			$params["WhsDocumentSupply_ProtDate"] = $filter["WhsDocumentSupply_ProtDate"];
		}
		if (isset($filter["WhsDocumentSupplyType_id"]) && $filter["WhsDocumentSupplyType_id"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentSupplyType_id = :WhsDocumentSupplyType_id";
			$params["WhsDocumentSupplyType_id"] = $filter["WhsDocumentSupplyType_id"];
		}
		if (isset($filter["WhsDocumentSupply_ExecDate"]) && $filter["WhsDocumentSupply_ExecDate"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentSupply_ExecDate = :WhsDocumentSupply_ExecDate";
			$params["WhsDocumentSupply_ExecDate"] = $filter["WhsDocumentSupply_ExecDate"];
		}
		if (isset($filter["DrugFinance_id"]) && $filter["DrugFinance_id"]) {
			$where[] = "v_WhsDocumentSupply.DrugFinance_id = :DrugFinance_id";
			$params["DrugFinance_id"] = $filter["DrugFinance_id"];
		}
		if (isset($filter["FinanceSource_id"]) && $filter["FinanceSource_id"]) {
			$where[] = "v_WhsDocumentSupply.FinanceSource_id = :FinanceSource_id";
			$params["FinanceSource_id"] = $filter["FinanceSource_id"];
		}
		if (isset($filter["WhsDocumentCostItemType_id"]) && $filter["WhsDocumentCostItemType_id"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$params["WhsDocumentCostItemType_id"] = $filter["WhsDocumentCostItemType_id"];
		}
		if (isset($filter["WhsDocumentStatusType_id"]) && $filter["WhsDocumentStatusType_id"]) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentStatusType_id = :WhsDocumentStatusType_id"; //заменить на нормальную проверку, как только появится признак подписания договора
			$params["WhsDocumentStatusType_id"] = $filter["WhsDocumentStatusType_id"];
		}
		if (!empty($filter["BudgetFormType_id"])) {
			$where[] = "v_WhsDocumentSupply.BudgetFormType_id = :BudgetFormType_id";
			$params["BudgetFormType_id"] = $filter["BudgetFormType_id"];
		}
		if (!empty($filter["WhsDocumentPurchType_id"])) {
			$where[] = "v_WhsDocumentSupply.WhsDocumentPurchType_id = :WhsDocumentPurchType_id";
			$params["WhsDocumentPurchType_id"] = $filter["WhsDocumentPurchType_id"];
		}
        if (!empty($filter['WhsDocumentUc_KBK'])) {
            $where[] = 'v_WhsDocumentSupply.WhsDocumentSupply_KBK = :WhsDocumentUc_KBK';
            $params['WhsDocumentUc_KBK'] = $filter['WhsDocumentUc_KBK'];
        }
        
		$selectString = count($select) > 0 ? ", " . implode(", ", $select) : "";
		$joinString = implode(" ", $join);
		$whereString = implode(" and ", $where);
		if (strlen($whereString)) {
			$whereString = "
				where
					-- where
					{$whereString}
					-- end where
			";
		}
		$fromString = "
			-- from
			v_WhsDocumentSupply
			left join v_WhsDocumentSupplyType WhsDocumentSupplyType_ref on WhsDocumentSupplyType_ref.WhsDocumentSupplyType_id = v_WhsDocumentSupply.WhsDocumentSupplyType_id
			left join v_DrugFinance DrugFinance_ref on DrugFinance_ref.DrugFinance_id = v_WhsDocumentSupply.DrugFinance_id
			left join v_FinanceSource FinanceSource_ref on FinanceSource_ref.FinanceSource_id = v_WhsDocumentSupply.FinanceSource_id
			left join v_WhsDocumentCostItemType WhsDocumentCostItemType_ref on WhsDocumentCostItemType_ref.WhsDocumentCostItemType_id = v_WhsDocumentSupply.WhsDocumentCostItemType_id
			left join v_Org Org_sid_ref on Org_sid_ref.Org_id = v_WhsDocumentSupply.Org_sid
			left join v_Org Org_cid_ref on Org_cid_ref.Org_id = v_WhsDocumentSupply.Org_cid
			left join v_Org Org_pid_ref on Org_pid_ref.Org_id = v_WhsDocumentSupply.Org_pid
			left join v_Org Org_rid_ref on Org_rid_ref.Org_id = v_WhsDocumentSupply.Org_rid
			left join v_WhsDocumentStatusType WhsDocumentStatusType_ref on WhsDocumentStatusType_ref.WhsDocumentStatusType_id = coalesce(v_WhsDocumentSupply.WhsDocumentStatusType_id, 1)
			left join v_WhsDocumentType WhsDocumentType_ref on WhsDocumentType_ref.WhsDocumentType_id = v_WhsDocumentSupply.WhsDocumentType_id
			left join WhsDocumentUc parentDoc on parentDoc.WhsDocumentUc_id = v_WhsDocumentSupply.WhsDocumentUc_pid
            left join dbo.WhsDocumentUc on WhsDocumentUc.WhsDocumentUc_id = v_WhsDocumentSupply.WhsDocumentUc_id
            left join v_BudgetFormType BudgetFormType_ref on BudgetFormType_ref.BudgetFormType_id = v_WhsDocumentSupply.BudgetFormType_id
			left join v_CommercialOffer co on co.CommercialOffer_id = v_WhsDocumentSupply.CommercialOffer_id
			left join lateral (
				select
					i_dr.DrugRequest_Name,
					i_wdprs.WhsDocumentProcurementRequestSpec_Name
				from
					v_WhsDocumentProcurementRequest i_wdpr
					left join v_WhsDocumentProcurementRequestSpec i_wdprs on i_wdprs.WhsDocumentProcurementRequest_id = i_wdpr.WhsDocumentProcurementRequest_id
					left join v_DrugRequestPurchaseSpec i_drps on i_drps.DrugRequestPurchaseSpec_id = i_wdprs.DrugRequestPurchaseSpec_id
					left join v_DrugRequest i_dr on i_dr.DrugRequest_id = i_drps.DrugRequest_id
				where i_wdpr.WhsDocumentUc_id = v_WhsDocumentSupply.WhsDocumentUc_pid
				order by i_dr.DrugRequest_id
				limit 1
			) as SvodDrugRequest on true
			left join v_DrugRequest dr on dr.DrugRequest_id = v_WhsDocumentSupply.DrugRequest_id
			{$joinString}
			-- end from
		";
		$selectSource = "
			-- select
			v_WhsDocumentSupply.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
			v_WhsDocumentSupply.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
			v_WhsDocumentSupply.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
			v_WhsDocumentSupply.WhsDocumentType_id as \"WhsDocumentType_id\",
			to_char(v_WhsDocumentSupply.WhsDocumentUc_Date, '{$this->dateTimeForm104}') as \"WhsDocumentUc_Date\",
			v_WhsDocumentSupply.Org_sid as \"Org_sid\",
			v_WhsDocumentSupply.Org_cid as \"Org_cid\",
			v_WhsDocumentSupply.Org_pid as \"Org_pid\",
			v_WhsDocumentSupply.Org_rid as \"Org_rid\",
			Org_sid_ref.Org_Name as \"Org_sid_Name\",
			coalesce(Org_sid_ref.Org_Nick, Org_sid_ref.Org_Name) as \"Org_sid_Nick\",
			Org_cid_ref.Org_Name as \"Org_cid_Name\",
			Org_pid_ref.Org_Name as \"Org_pid_Name\",
			Org_rid_ref.Org_Name as \"Org_rid_Name\",
			WhsDocumentStatusType_ref.WhsDocumentStatusType_Name as \"WhsDocumentStatusType_Name\",
			to_char(coalesce(v_WhsDocumentSupply.WhsDocumentSupply_BegDate, v_WhsDocumentSupply.WhsDocumentUc_Date), '{$this->dateTimeForm104}')||' - '||coalesce(to_char(v_WhsDocumentSupply.WhsDocumentSupply_ExecDate, '{$this->dateTimeForm104}'), '') as \"ActualDateRange\",
			case when (select count(WhsDocumentDelivery_id) from WhsDocumentDelivery where WhsDocumentSupply_id = v_WhsDocumentSupply.WhsDocumentSupply_id) > 0
				then v_WhsDocumentSupply.WhsDocumentSupply_id
				else null
			end as \"GraphLink\",
			null as \"FinanceInf\",
			coalesce(WhsDocumentSupply_ProtNum||' ', '')||coalesce('('||to_char(v_WhsDocumentSupply.WhsDocumentSupply_ProtDate, '{$this->dateTimeForm104}')||')', '') as \"ProtInf\",
			parentDoc.WhsDocumentUc_Name||coalesce(' / '||SvodDrugRequest.DrugRequest_Name, '') as \"WhsDocumentUc_pName\",
			rtrim(
			    coalesce('Лот № '||parentDoc.WhsDocumentUc_Num, '')||
			    coalesce(' от '||to_char(parentDoc.WhsDocumentUc_Date, '{$this->dateTimeForm104}'), '')||
			    ' '||coalesce(SvodDrugRequest.WhsDocumentProcurementRequestSpec_Name, '')
			) as \"WhsDocumentProcurementRequest_Name\",
			coalesce(dr.DrugRequest_Name, SvodDrugRequest.DrugRequest_Name) as \"DrugRequest_Name\",
			v_WhsDocumentSupply.WhsDocumentUc_Sum::decimal(15, 2) as \"WhsDocumentUc_Sum\",
			v_WhsDocumentSupply.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
			v_WhsDocumentSupply.WhsDocumentUc_id as \"WhsDocumentUc_id\",
			v_WhsDocumentSupply.WhsDocumentSupply_ProtNum as \"WhsDocumentSupply_ProtNum\",
			v_WhsDocumentSupply.WhsDocumentSupply_ProtDate as \"WhsDocumentSupply_ProtDate\",
			v_WhsDocumentSupply.WhsDocumentSupplyType_id as \"WhsDocumentSupplyType_id\",
			v_WhsDocumentSupply.WhsDocumentSupply_ExecDate as \"WhsDocumentSupply_ExecDate\",
			v_WhsDocumentSupply.DrugFinance_id as \"DrugFinance_id\",
			v_WhsDocumentSupply.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
			v_WhsDocumentSupply.WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\",
			v_WhsDocumentSupply.WhsDocumentUc_Name as \"WhsDocumentUc_pid_Name\",
			WhsDocumentSupplyType_ref.WhsDocumentSupplyType_Name as \"WhsDocumentSupplyType_Name\",
			DrugFinance_ref.DrugFinance_Name as \"DrugFinance_Name\",
			case when FinanceSource_ref.FinanceSource_Name is not null and DrugFinance_ref.DrugFinance_Name is not null
				then rtrim(coalesce(DrugFinance_ref.DrugFinance_Name, '')||', '||coalesce(FinanceSource_ref.FinanceSource_Nick,FinanceSource_ref.FinanceSource_Name))
				else rtrim(coalesce(DrugFinance_ref.DrugFinance_Name, '')||coalesce(FinanceSource_ref.FinanceSource_Nick,''))
			end as \"DrugFinanceSource_Name\",
			WhsDocumentCostItemType_ref.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
			WhsDocumentType_ref.WhsDocumentType_Name as \"WhsDocumentType_Name\",
			BudgetFormType_ref.BudgetFormType_Name as \"BudgetFormType_Name\",
			co.CommercialOffer_id as \"CommercialOffer_id\",
			v_WhsDocumentSupply.WhsDocumentSupply_KBK as \"WhsDocumentSupply_KBK\",
			case when WhsDocumentUc.WhsDocumentUc_ImportDT is  NULL then 1 else 2 end as \"isImport\"
			{$selectString}
			-- end select
		";
		$orderByString = "
			-- order by
			v_WhsDocumentSupply.WhsDocumentUc_Num
			-- end order by
		";
		$query = "
			select {$selectSource}
			from {$fromString}
			{$whereString}
			order by {$orderByString}
		";
		/**@var CI_DB_result $result */
		if (!empty($filter["limit"])) {
			$result = $this->db->query(getLimitSQLPH($query, $filter["start"], $filter["limit"]), $params);
			$count = $this->getFirstResultFromQuery(getCountSQLPH($query), $params);
			return (is_object($result) && $count !== false) ? ["data" => $result->result("array"), "totalCount" => $count] : false;
		} else {
			$result = $this->db->query($query, $params);
			return (is_object($result)) ? $result->result("array") : null;
		}
	}

	/**
	 * Сохранение
	 * @return array|CI_DB_result
	 * @throws Exception
	 */
	function save()
	{
		$procedure = ($this->WhsDocumentSupply_id > 0) ? "p_WhsDocumentSupply_upd" : "p_WhsDocumentSupply_ins";
		$selectString = "
		    whsdocumentsupply_id as \"WhsDocumentSupply_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    whsdocumentuc_id := :WhsDocumentUc_id,
			    whsdocumentuc_pid := :WhsDocumentUc_pid,
			    whsdocumentuc_num := :WhsDocumentUc_Num,
			    whsdocumentuc_name := :WhsDocumentUc_Name,
			    whsdocumenttype_id := :WhsDocumentType_id,
			    whsdocumentuc_date := :WhsDocumentUc_Date,
			    whsdocumentuc_sum := :WhsDocumentUc_Sum,
			    whsdocumentstatustype_id := :WhsDocumentStatusType_id,
			    org_aid := :Org_aid,
			    whsdocumentsupply_id := :WhsDocumentSupply_id,
			    whsdocumentsupply_protnum := :WhsDocumentSupply_ProtNum,
			    whsdocumentsupply_protdate := :WhsDocumentSupply_ProtDate,
			    whsdocumentsupplytype_id := :WhsDocumentSupplyType_id,
			    whsdocumentsupply_execdate := :WhsDocumentSupply_ExecDate,
			    drugfinance_id := :DrugFinance_id,
			    whsdocumentcostitemtype_id := :WhsDocumentCostItemType_id,
			    org_sid := :Org_sid,
			    org_cid := :Org_cid,
			    org_pid := :Org_pid,
			    org_rid := :Org_rid,
			    whsdocumentpurchtype_id := :WhsDocumentPurchType_id,
			    budgetformtype_id := :BudgetFormType_id,
			    financesource_id := :FinanceSource_id,
			    drugnds_id := :DrugNds_id,
			    whsdocumentsupply_begdate := :WhsDocumentSupply_BegDate,
			    drugrequest_id := :DrugRequest_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"WhsDocumentUc_pid" => $this->WhsDocumentUc_pid,
			"WhsDocumentUc_Num" => $this->WhsDocumentUc_Num,
			"WhsDocumentUc_Name" => $this->WhsDocumentUc_Name,
			"WhsDocumentType_id" => $this->WhsDocumentType_id,
			"WhsDocumentUc_Date" => $this->WhsDocumentUc_Date,
			"WhsDocumentStatusType_id" => $this->WhsDocumentStatusType_id,
			"Org_aid" => $this->Org_aid,
			"Org_sid" => $this->Org_sid,
			"Org_cid" => $this->Org_cid,
			"Org_pid" => $this->Org_pid,
			"Org_rid" => $this->Org_rid,
			"WhsDocumentUc_Sum" => $this->WhsDocumentUc_Sum,
			"WhsDocumentSupply_id" => $this->WhsDocumentSupply_id,
			"WhsDocumentUc_id" => $this->WhsDocumentUc_id,
			"WhsDocumentSupply_ProtNum" => $this->WhsDocumentSupply_ProtNum,
			"WhsDocumentSupply_ProtDate" => $this->WhsDocumentSupply_ProtDate,
			"WhsDocumentSupplyType_id" => $this->WhsDocumentSupplyType_id,
			"WhsDocumentSupply_BegDate" => $this->WhsDocumentSupply_BegDate,
			"WhsDocumentSupply_ExecDate" => $this->WhsDocumentSupply_ExecDate,
			"DrugFinance_id" => $this->DrugFinance_id,
			"WhsDocumentCostItemType_id" => $this->WhsDocumentCostItemType_id,
			"BudgetFormType_id" => $this->BudgetFormType_id,
			"WhsDocumentPurchType_id" => $this->WhsDocumentPurchType_id,
			"FinanceSource_id" => $this->FinanceSource_id,
			"DrugNds_id" => $this->DrugNds_id,
			"DrugRequest_id" => $this->DrugRequest_id,
			"pmUser_id" => $this->pmUser_id
		];
		/**
		 */
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result("array");
		if (!($this->WhsDocumentSupply_id > 0)) {
			$query = "
				select
				    whsdocumentsupplyspec_id as \"WhsDocumentSupplySpec_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_whsdocumentsupplyspec_ins(
					whsdocumentsupply_id := :WhsDocumentSupply_id,
					drugnds_id := :DrugNds_id,
				    okei_id := :Okei_id,
					pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"WhsDocumentSupply_id" => $result[0]["WhsDocumentSupply_id"],
				"DrugNds_id" => $this->DrugNds_id,
				"Okei_id" => $this->Okei_id,
				"pmUser_id" => $this->pmUser_id,
			];
			$this->db->query($query, $queryParams);
		}
		$this->WhsDocumentSupply_id = $result[0]["WhsDocumentSupply_id"];
		return $result;
	}

	/**
	 * Удаление
	 * @return array|bool
	 */
	function delete()
	{
		/**@var CI_DB_result $result */
		$query = "
			delete from WhsDocumentDelivery
			where WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
		$queryParams = ["WhsDocumentSupply_id" => $this->WhsDocumentSupply_id];
		$this->db->query($query, $queryParams);
		$query = "
			delete from WhsDocumentSupplySpecDrug
			where
				WhsDocumentSupplySpec_id in (
				    select WhsDocumentSupplySpec_id
                    from WhsDocumentSupplySpec
                    where WhsDocumentSupply_id = :WhsDocumentSupply_id
				)
		";
		$queryParams = ["WhsDocumentSupply_id" => $this->WhsDocumentSupply_id];
		$this->db->query($query, $queryParams);
		$query = "
			delete from WhsDocumentSupplySpec
			where WhsDocumentSupply_id = :WhsDocumentSupply_id
		";
		$queryParams = ["WhsDocumentSupply_id" => $this->WhsDocumentSupply_id];
		$this->db->query($query, $queryParams);
		$query = "
			delete from WhsDocumentUcPriceHistory
			where WhsDocumentUc_id = :WhsDocumentSupply_id
		";
		$queryParams = ["WhsDocumentSupply_id" => $this->WhsDocumentSupply_id];
		$this->db->query($query, $queryParams);
		//удаляем дополнительные соглашения
		$query = "
			delete from WhsDocumentUc
			where WhsDocumentUc_pid = :WhsDocumentSupply_id
		";
		$queryParams = ["WhsDocumentSupply_id" => $this->WhsDocumentSupply_id];
		$this->db->query($query, $queryParams);
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_whsdocumentsupply_del(
				whsdocumentsupply_id := :WhsDocumentSupply_id,
				pmUser_id := :pmUser_id
			);
		";
		$queryParams = [
			"WhsDocumentSupply_id" => $this->WhsDocumentSupply_id,
			"pmUser_id" => $this->pmUser_id
		];
		$result = $this->db->query($query, $queryParams);
		return (is_object($result))?$result->result("array"):false;
	}

	/**
	 * Получение списка лотов
	 * @param $filter
	 * @return array|bool
	 */
	function loadWhsDocumentProcurementRequestList($filter)
	{
		$whereString = "where WhsDocumentUc_pid is not null";
		if (isset($filter["WhsDocumentSupply_id"]) && $filter["WhsDocumentSupply_id"]) {
			$whereString .= " and WhsDocumentSupply_id <> :WhsDocumentSupply_id";
		}
		$query = "
			select
				wdpr.WhsDocumentProcurementRequest_id as \"WhsDocumentProcurementRequest_id\",
				wdpr.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				wdpr.DrugFinance_id as \"DrugFinance_id\",
				wdpr.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				wdpr.BudgetFormType_id as \"BudgetFormType_id\",
				wdprsi.WhsDocumentProcurementRequestSpec_Name as \"WhsDocumentProcurementRequestSpec_Name\",
				wdpr.Org_aid as \"Org_id\",
				orgt.OrgType_SysNick as \"OrgType_SysNick\"
			from
				v_WhsDocumentProcurementRequest wdpr
				left join lateral (
					select count(WhsDocumentProcurementRequestSpec_id) as Count
					from WhsDocumentProcurementRequestSpec wdprs
					where wdprs.WhsDocumentProcurementRequest_id = wdpr.WhsDocumentProcurementRequest_id
				) as spec_count on true
				left join v_WhsDocumentProcurementRequestSpec wdprsi on wdprsi.WhsDocumentProcurementRequest_id = wdpr.WhsDocumentProcurementRequest_id
				left join Org org on org.Org_id = wdpr.Org_aid
				left join OrgType orgt on orgt.OrgType_id = org.OrgType_id
			where wdpr.WhsDocumentUc_id not in (
					select WhsDocumentUc_pid
					from v_WhsDocumentSupply
					{$whereString}
				)
			  and spec_count.Count > 0
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $filter);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Получение спецификации лота
	 * @param $filter
	 * @return array|bool
	 */
	function loadWhsDocumentProcurementRequestSpecList($filter)
	{
		$query = "
			select
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequestSpec_id as \"WhsDocumentProcurementRequestSpec_id\",
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequest_id as \"WhsDocumentProcurementRequest_id\",
				v_WhsDocumentProcurementRequestSpec.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				v_WhsDocumentProcurementRequestSpec.Drug_id as \"Drug_id\",
				v_WhsDocumentProcurementRequestSpec.Okei_id as \"Okei_id\",
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequestSpec_Kolvo as \"WhsDocumentProcurementRequestSpec_Kolvo\",
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequestSpec_PriceMax as \"WhsDocumentProcurementRequestSpec_PriceMax\",
				v_WhsDocumentProcurementRequestSpec.pmUser_insID as \"pmUser_insID\",
				v_WhsDocumentProcurementRequestSpec.pmUser_updID as \"pmUser_updID\",
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequestSpec_insDT as \"WhsDocumentProcurementRequestSpec_insDT\",
				v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequestSpec_updDT as \"WhsDocumentProcurementRequestSpec_updDT\"
			from dbo.v_WhsDocumentProcurementRequestSpec
			where v_WhsDocumentProcurementRequestSpec.WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $filter);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Получение графика поставки
	 * @param $filter
	 * @return array|bool
	 */
	function loadWhsDocumentDeliveryList($filter)
	{
		$whereString = (isset($filter["WhsDocumentSupply_id"]) && $filter["WhsDocumentSupply_id"])
			? "where v_WhsDocumentDelivery.WhsDocumentSupply_id = :WhsDocumentSupply_id"
			: "";
		$query = "
			select
				v_WhsDocumentDelivery.WhsDocumentDelivery_id as \"WhsDocumentDelivery_id\",
			    v_WhsDocumentDelivery.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
			    v_WhsDocumentDelivery.WhsDocumentSupplySpec_id as \"WhsDocumentSupplySpec_id\",
			    v_WhsDocumentDelivery.WhsDocumentDelivery_setDT as \"WhsDocumentDelivery_setDT\",
			    v_WhsDocumentDelivery.Okei_id as \"Okei_id\",
			    v_WhsDocumentDelivery.WhsDocumentDelivery_Kolvo as \"WhsDocumentDelivery_Kolvo\",
			    Okei_id_ref.Okei_Name as \"Okei_id_Name\"
			from
				v_WhsDocumentDelivery
				left join dbo.v_WhsDocumentSupply WhsDocumentSupply_id_ref on WhsDocumentSupply_id_ref.WhsDocumentSupply_id = v_WhsDocumentDelivery.WhsDocumentSupply_id
				left join dbo.v_WhsDocumentSupplySpec WhsDocumentSupplySpec_id_ref on WhsDocumentSupplySpec_id_ref.WhsDocumentSupplySpec_id = v_WhsDocumentDelivery.WhsDocumentSupplySpec_id
				left join dbo.v_Okei Okei_id_ref on Okei_id_ref.Okei_id = v_WhsDocumentDelivery.Okei_id
			{$whereString}
			order by
				v_WhsDocumentDelivery.WhsDocumentSupplySpec_id,
				v_WhsDocumentDelivery.WhsDocumentDelivery_setDT
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $filter);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Сохраненеи графика поставки
	 * @return array|CI_DB_result
	 * @throws Exception
	 */
	function saveWhsDocumentDelivery()
	{
		$procedure = ($this->WhsDocumentDelivery_id > 0) ? "p_WhsDocumentDelivery_upd" : "p_WhsDocumentDelivery_ins";
		$selectString = "
		    whsdocumentdelivery_id as \"WhsDocumentDelivery_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    whsdocumentdelivery_id := :WhsDocumentDelivery_id,
			    whsdocumentsupply_id := :WhsDocumentSupply_id,
			    whsdocumentsupplyspec_id := :WhsDocumentSupplySpec_id,
			    whsdocumentdelivery_setdt := :WhsDocumentDelivery_setDT,
			    okei_id := :Okei_id,
			    whsdocumentdelivery_kolvo := :WhsDocumentDelivery_Kolvo,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"WhsDocumentDelivery_id" => $this->WhsDocumentDelivery_id,
			"WhsDocumentSupply_id" => $this->WhsDocumentSupply_id,
			"WhsDocumentSupplySpec_id" => $this->WhsDocumentSupplySpec_id,
			"WhsDocumentDelivery_setDT" => $this->WhsDocumentDelivery_setDT,
			"Okei_id" => $this->Okei_id,
			"WhsDocumentDelivery_Kolvo" => $this->WhsDocumentDelivery_Kolvo,
			"pmUser_id" => $this->pmUser_id,
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result("array");
		$this->WhsDocumentDelivery_id = $result[0]["WhsDocumentDelivery_id"];
		return $result;
	}

	/**
	 * Удаление графика поставки
	 * @return array|bool
	 */
	function deleteWhsDocumentDelivery()
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_whsdocumentdelivery_del(whsdocumentdelivery_id := :WhsDocumentDelivery_id);
		";
		$queryParams = ["WhsDocumentDelivery_id" => $this->WhsDocumentDelivery_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Загрузка спецификации ГК
	 * @param $filter
	 * @return array|bool
	 */
	function loadWhsDocumentSupplyList($filter)
	{
		if ($filter["WhsDocumentType_id"] == 12) {
			// Документ на включение
			$query = "
				select
					wds.WhsDocumentUc_id as \"WhsDocumentUc_id\",
					wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
					wds.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				    fin_year.yr as \"WhsDocumentSupply_Year\"
				from
					v_WhsDocumentSupply wds
					inner join v_DrugShipment ds on ds.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
					inner join v_DrugOstatRegistry dor on dor.DrugShipment_id = ds.DrugShipment_id
					left join (
                        select date_part('year', coalesce(max(i_wdd.WhsDocumentDelivery_setDT), wds.WhsDocumentUc_Date)) as yr
                        from v_WhsDocumentDelivery i_wdd
                        where i_wdd.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                    ) as fin_year on true
				where
					dor.DrugOstatRegistry_Kolvo > 0 and
					dor.Drug_id is not null
				group by
					wds.WhsDocumentUc_id,
					wds.WhsDocumentUc_Num,
					wds.WhsDocumentUc_Name,
					fin_year.yr
			";
		} elseif ($filter["WhsDocumentType_id"] == 13) {
			// Документ на исключение
			$query = "
				select
					wds.WhsDocumentUc_id as \"WhsDocumentUc_id\",
					wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
					wds.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				    fin_year.yr as \"WhsDocumentSupply_Year\"
				from
					v_WhsDocumentSupply wds
					inner join v_WhsDocumentOrderReserveDrug wdord on wdord.WhsDocumentUc_pid = wds.WhsDocumentSupply_id
					left join lateral (
                        select date_part('year', coalesce(max(i_wdd.WhsDocumentDelivery_setDT), wds.WhsDocumentUc_Date)) as yr
                        from v_WhsDocumentDelivery i_wdd
                        where i_wdd.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                    ) as fin_year on true
				where wdord.WhsDocumentOrderReserveDrug_Kolvo > 0
				group by
					wds.WhsDocumentUc_id,
					wds.WhsDocumentUc_Num,
					wds.WhsDocumentUc_Name,
					fin_year.yr
			";
		}
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $filter);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Загрузка списка доп. соглашений
	 * @param $filter
	 * @return array|bool
	 */
	function loadWhsDocumentSupplyAdditionalList($filter)
	{
		$where = [];
		$params = [];
		$where[] = "wdt.WhsDocumentType_Code::int8 = 13"; //доп соглашения
		if (isset($filter["ParentWhsDocumentSupply_id"]) && $filter["ParentWhsDocumentSupply_id"]) {
			$where[] = "p_wds.WhsDocumentSupply_id = :ParentWhsDocumentSupply_id";
			$params["ParentWhsDocumentSupply_id"] = $filter["ParentWhsDocumentSupply_id"];
		}
		if (isset($filter["WhsDocumentUc_Num"]) && $filter["WhsDocumentUc_Num"]) {
			$where[] = "p_wds.WhsDocumentUc_Num like '%'||:WhsDocumentUc_Num||'%'";
			$params["WhsDocumentUc_Num"] = $filter["WhsDocumentUc_Num"];
		}
		if (isset($filter["WhsDocumentUc_DateRange"]) && !empty($filter["WhsDocumentUc_DateRange"][0]) && !empty($filter["WhsDocumentUc_DateRange"][1])) {
			$where[] = "p_wds.WhsDocumentUc_Date >= :WhsDocumentUc_Date_startdate::date";
			$where[] = "p_wds.WhsDocumentUc_Date <= :WhsDocumentUc_Date_enddate::date";
			$params["WhsDocumentUc_Date_startdate"] = $filter["WhsDocumentUc_DateRange"][0];
			$params["WhsDocumentUc_Date_enddate"] = $filter["WhsDocumentUc_DateRange"][1];
		}
		if (isset($filter["Org_sid"]) && $filter["Org_sid"]) {
			$where[] = "p_wds.Org_sid = :Org_sid";
			$params["Org_sid"] = $filter["Org_sid"];
		}
		if (isset($filter["DrugFinance_id"]) && $filter["DrugFinance_id"]) {
			$where[] = "p_wds.DrugFinance_id = :DrugFinance_id";
			$params["DrugFinance_id"] = $filter["DrugFinance_id"];
		}
		if (isset($filter["WhsDocumentCostItemType_id"]) && $filter["WhsDocumentCostItemType_id"]) {
			$where[] = "p_wds.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$params["WhsDocumentCostItemType_id"] = $filter["WhsDocumentCostItemType_id"];
		}
		if (isset($filter["WhsDocumentStatusType_id"]) && $filter["WhsDocumentStatusType_id"]) {
			$where[] = "wds.WhsDocumentStatusType_id = :WhsDocumentStatusType_id"; //заменить на нормальную проверку, как только появится признак подписания договора
			$params["WhsDocumentStatusType_id"] = $filter["WhsDocumentStatusType_id"];
		}
		//фильтр по организации
		$org_filter_fields = [
			"OrgFilter_Org_sid",
			"OrgFilter_Org_cid",
			"OrgFilter_Org_pid"
		];
		$org_filter_exists = false;
		foreach ($org_filter_fields as $of_field) {
			if (!empty($filter[$of_field])) {
				$org_filter_exists = true;
				break;
			}
		}
		if ($org_filter_exists) {
			$of_type = $filter["OrgFilter_Type"] == "or" ? "or" : "and";
			$of_filter = [];
			foreach ($org_filter_fields as $of_field) {
				//сборка условия по конкретному фильтру
				$of_id_array = explode(',', $filter[$of_field]);
				//если переденно енсколько идентификаторов через запятую, то разбиваем строку на массив идентификаторов
				$of_sub_filter = [];
				foreach ($of_id_array as $of_id) {
					if (!empty($of_id) && $of_id > 0) { //если идентификатор не пустой, то собираем фрагмент условия
						$of_sub_filter[] = "p_wds." . preg_replace("/OrgFilter_/", "", $of_field) . " = " . $of_id;
					}
				}
				if (count($of_sub_filter) > 0) {
					//собираем условия по одному фильтру (всегда собираем через "или")
					$of_filter[] = count($of_sub_filter) > 1 ? "(" . join(" or ", $of_sub_filter) . ")" : join(" or ", $of_sub_filter);
				}
			}
			//собираем условие по всем фильтрам организации
			$where[] = "(" . join(" " . $of_type . " ", $of_filter) . ")";
		}
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
			 		-- where
			 		{$where_clause}
			 		-- end where
			";
		}
		$query = "
			select
				-- select
				wds.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wds.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				wds.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				to_char(wds.WhsDocumentUc_Date, '{$this->dateTimeForm104}') as \"WhsDocumentUc_Date\",
				coalesce(p_wds.WhsDocumentUc_Num, '')||' '||coalesce(to_char(p_wds.WhsDocumentUc_Date, '{$this->dateTimeForm104}'), '') as \"WhsDocumentUc_pNum\",
				coalesce(org_s.Org_Nick, org_s.Org_Name) as \"Org_Nick\",
				wds.WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\",
				wdst.WhsDocumentStatusType_Name as \"WhsDocumentStatusType_Name\",
				to_char(wds.WhsDocumentUc_Date, '{$this->dateTimeForm104}') as \"ActualDateRange\",
				coalesce(p_wds.WhsDocumentSupply_ProtNum||' ', '')||coalesce('('||to_char(p_wds.WhsDocumentSupply_ProtDate, '{$this->dateTimeForm104}')||')', '') as \"ProtInf\",
				ppDoc.WhsDocumentUc_Name||coalesce(' / '||SvodDrugRequest.DrugRequest_Name, '') as \"WhsDocumentUc_ppName\",
				df.DrugFinance_Name as \"DrugFinance_Name\",
				wdcit.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\"
				-- end select
			from
				-- from
				v_WhsDocumentSupply wds
				left join v_WhsDocumentSupply p_wds on p_wds.WhsDocumentUc_id = wds.WhsDocumentUc_pid
				left join v_DrugFinance df on df.DrugFinance_id = p_wds.DrugFinance_id
				left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = p_wds.WhsDocumentCostItemType_id
				left join v_Org org_s on org_s.Org_id = p_wds.Org_sid
				left join v_WhsDocumentType wdt on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
				left join v_WhsDocumentStatusType wdst on wdst.WhsDocumentStatusType_id = coalesce(wds.WhsDocumentStatusType_id, 1)
				left join WhsDocumentUc ppDoc on ppDoc.WhsDocumentUc_id = p_wds.WhsDocumentUc_pid
				left join lateral (
					select dr.DrugRequest_Name
					from
						DrugRequestPurchaseSpec drps
						left join DrugRequest dr on dr.DrugRequest_id = drps.DrugRequest_id
					where drps.WhsDocumentUc_id = ppDoc.WhsDocumentUc_id
					limit 1
				) as SvodDrugRequest on true
				-- end from
			{$where_clause}
			order by
				-- order by
				wds.WhsDocumentUc_Num
				-- end order by
		";
		/**@var CI_DB_result $result */
		if (!empty($filter["limit"])) {
			$result = $this->db->query(getLimitSQLPH($query, $filter["start"], $filter["limit"]), $params);
			$count = $this->getFirstResultFromQuery(getCountSQLPH($query), $params);
			return (is_object($result) && $count !== false) ? ["data" => $result->result("array"), "totalCount" => $count] : false;
		} else {
			$result = $this->db->query($query, $params);
			return (is_object($result)) ? $result->result("array") : false;
		}
	}

	/**
	 * Загрузка списка доп. соглашений (используется в гриде на форме редактирования контракта)
	 * @param $filter
	 * @return array|bool
	 */
	function loadWhsDocumentSupplyAdditionalShortList($filter)
	{
		// Документ на включение
		$query = "
			select
				WhsDocumentUc_id as \"WhsDocumentUc_id\",
				WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				to_char(WhsDocumentUc_Date, '{$this->dateTimeForm104}') as \"WhsDocumentUc_Date\"
			from WhsDocumentUc
			where WhsDocumentUc_pid = :WhsDocumentSupply_id;
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $filter);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Загрузка данных для комбобокса "Номер ГК"
	 * @param $data
	 * @return array|bool
	 */
	function loadWhsDocumentSupplyCombo($data)
	{
		$filterArray = [];
		$WhsDocumentType_ids = implode(",", json_decode($data["WhsDocumentType_ids"]));
		$filterArray[] = "wds.WhsDocumentType_id in ({$WhsDocumentType_ids})";
		if (!empty($data["WhsDocumentSupply_id"])) {
			$filterArray[] = "wds.WhsDocumentSupply_id = :WhsDocumentSupply_id";
		} else {
			if (!empty($data["Org_id"])) {
				$filterArray[] = "(wds.Org_cid = :Org_id OR wds.Org_pid = :Org_id OR wds.Org_sid = :Org_id)";
			}
			if ($data["WhsDocumentCostItemType_id"] > 0) {
				$filterArray[] = "wds.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			}
			if ($data["DrugFinance_id"] > 0) {
				$filterArray[] = "wds.DrugFinance_id = :DrugFinance_id";
			}
			if (!empty($data["query"])) {
				$data["query"] = "%{$data["query"]}%";
				$filterArray[] = "wds.WhsDocumentUc_Num like :query";
			}
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$query = "
			select
				wds.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				wds.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				wds.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
                sup.Org_Name as \"Supplier_Name\"
			from
				v_WhsDocumentSupply wds
                left join v_Org sup on sup.Org_id = wds.Org_sid
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Загрузка данных для комбобокса "Контракт"
	 * @param $data
	 * @return array|false
	 */
	function loadWhsDocumentSupplySecondCombo($data)
	{
		$filterArray = [];
		$params = [];
		$WhsDocumentType_ids = implode(",", json_decode($data["WhsDocumentType_ids"]));
		$filterArray[] = "WDS.WhsDocumentType_id in ({$WhsDocumentType_ids})";
		if ($data["WhsDocumentSupply_id"]) {
			$filterArray[] = "WDS.WhsDocumentSupply_id = :WhsDocumentSupply_id";
			$params["WhsDocumentSupply_id"] = $data["WhsDocumentSupply_id"];
		} else {
			if (!empty($data["Org_cid"])) {
				$filterArray[] = "WDS.Org_cid = :Org_cid";
				$params["Org_cid"] = $data["Org_cid"];
			}
			if (!empty($data["DrugFinance_id"])) {
				$filterArray[] = "WDS.DrugFinance_id = :DrugFinance_id";
				$params["DrugFinance_id"] = $data["DrugFinance_id"];
			}
			if (!empty($data["WhsDocumentCostItemType_id"])) {
				$filterArray[] = "WDS.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
				$params["WhsDocumentCostItemType_id"] = $data["WhsDocumentCostItemType_id"];
			}
			if (!empty($data["query"])) {
				$filterArray[] = "WDS.WhsDocumentUc_Num like :WhsDocumentUc_Num||'%'";
				$params["WhsDocumentUc_Num"] = $data["query"];
			}
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$query = "
			select
				WDS.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				WDS.WhsDocumentUc_Num as \"WhsDocumentSupply_Num\",
				to_char(WDS.WhsDocumentUc_Date, '{$this->dateTimeForm104}') as \"WhsDocumentSupply_Date\",
				WDS.WhsDocumentSupply_ProtNum as \"WhsDocumentSupply_ProtNum\",
				WDPR.WhsDocumentProcurementRequest_id as \"WhsDocumentProcurementRequest_id\",
				WDPR.WhsDocumentUc_Name as \"WhsDocumentProcurementRequest_Name\",
				DR.DrugRequest_id as \"DrugRequest_id\",
				DR.DrugRequest_Name as \"DrugRequest_Name\"
			from
				v_WhsDocumentSupply WDS
				left join v_WhsDocumentProcurementRequest WDPR on WDPR.WhsDocumentUc_id = WDS.WhsDocumentUc_pid
				left join lateral(
					select DrugRequest_id
					from v_DrugRequestPurchaseSpec
					where WhsDocumentUc_id = WDPR.WhsDocumentUc_id
				    limit 1
				) as DRPS on true
				left join v_DrugRequest DR on DR.DrugRequest_id = DRPS.DrugRequest_id
			{$whereString}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение доп. соглашений из JSON
	 */
	function saveSupplyAdditionalFromJSON($data)
	{
		if (!empty($data["json_str"]) && $data["WhsDocumentSupply_id"] > 0) {
			ConvertFromWin1251ToUTF8($data["json_str"]);
			$dt = (array)json_decode($data["json_str"]);
			foreach ($dt as $record) {
				$record = (array)$record;
				array_walk($record, "ConvertFromUTF8ToWin1251");
				$record["WhsDocumentUc_Date"] = !empty($record["WhsDocumentUc_Date"]) ? join("-", array_reverse(explode('.', $record["WhsDocumentUc_Date"]))) : "null";
				$record["pmUser_id"] = $data["pmUser_id"];
				$record["WhsDocumentUc_pid"] = $data["WhsDocumentSupply_id"];
				$record["WhsDocumentType_id"] = 14;
				switch ($record["state"]) {
					case "add":
						$query = "
							select
							    whsdocumentuc_id as \"WhsDocumentUc_id\",
							    error_code as \"Error_Code\",
							    error_message as \"Error_Msg\"
							from p_whsdocumentuc_ins(
							    whsdocumentuc_pid := :WhsDocumentUc_pid,
							    whsdocumentuc_num := :WhsDocumentUc_Num,
							    whsdocumentuc_name := :WhsDocumentUc_Name,
							    whsdocumenttype_id := :WhsDocumentType_id,
							    whsdocumentuc_date := :WhsDocumentUc_Date
							);						
						";
						$result = $this->db->query($query, $record);
						$arr = $result->result("array");
						if (!empty($arr[0]["WhsDocumentUc_id"])) {
							$this->updateSupplyAdditionalPriceHistoryLink([
								"WhsDocumentUc_id" => $record["WhsDocumentUc_pid"],
								"WhsDocumentUc_sid" => $arr[0]["WhsDocumentUc_id"],
								"pmUser_id" => $record["pmUser_id"]
							]);
						}
						break;
					case "edit":
						$query = "
							update WhsDocumentUc
							set
								WhsDocumentUc_Num = :WhsDocumentUc_Num,
							    WhsDocumentUc_Name = :WhsDocumentUc_Name,
							    WhsDocumentUc_Date = :WhsDocumentUc_Date,
							    pmUser_updID = :pmUser_id,
							    WhsDocumentUc_updDT = tzgetdate()
							where WhsDocumentUc_id = :WhsDocumentUc_id;
						";
						$this->db->query($query, $record);
						$response["updated"][] = $record["WhsDocumentUc_id"];
						break;
					case "delete":
						$this->clearSupplyAdditionalPriceHistoryLink([
							"WhsDocumentUc_sid" => $record["WhsDocumentUc_id"],
							"pmUser_id" => $record["pmUser_id"]
						]);
						$query = "
							delete from WhsDocumentUc
							where WhsDocumentUc_id = :WhsDocumentUc_id;
						";
						$this->db->query($query, $record);
						break;
				}
			}
		}
	}

	/**
	 * Обновление ссылки на доп. соглашение в периодике цен по ГК
	 * @param $data
	 */
	function updateSupplyAdditionalPriceHistoryLink($data)
	{
		$query = "
			update WhsDocumentUcPriceHistory
			set
				WhsDocumentUc_sid = :WhsDocumentUc_sid,
				pmUser_updID = :pmUser_id,
				WhsDocumentUcPriceHistory_updDT = tzgetdate()
			where WhsDocumentUc_id = :WhsDocumentUc_id
			  and WhsDocumentUcPriceHistory_begDT::date = tzgetdate()::date;
		";
		$this->db->query($query, $data);
	}

	/**
	 * Очистка ссылок на доп. соглашение в периодике цен по ГК
	 * @param $data
	 */
	function clearSupplyAdditionalPriceHistoryLink($data)
	{
		if (!empty($data["WhsDocumentUc_sid"])) {
			$query = "
				update WhsDocumentUcPriceHistory
				set
					WhsDocumentUc_sid = null,
					pmUser_updID = :pmUser_id
				where WhsDocumentUc_sid = :WhsDocumentUc_sid
			";
			$this->db->query($query, $data);
		}
	}

	/**
	 * Получение предельной цены (с учетом НДС) для конкретного медикамента
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function getMaxSalePrice($data)
	{
		$vSupplyDate = "coalesce(:WhsDocumentUc_Date, tzgetdate())";
		$vPrice = "
			(
			    select DrugSalePrice_Price
                from rls.v_DrugSalePrice
                where Drug_id = :Drug_id and (DrugSalePrice_relDT is null or DrugSalePrice_relDT <= {$vSupplyDate})
                order by DrugSalePrice_relDT desc
                limit 1
			)
		";
		$vPriceDate = "
			(
			    select to_char(DrugSalePrice_relDT, '{$this->dateTimeForm104}') as DrugSalePrice_relDT
                from rls.v_DrugSalePrice
                where Drug_id = :Drug_id and (DrugSalePrice_relDT is null or DrugSalePrice_relDT <= {$vSupplyDate})
                order by DrugSalePrice_relDT desc
                limit 1
			)
		";
		$vIsNarco = "
			(
				select YesNo_id
				from YesNo
				where YesNo_Code = (
						select case when coalesce(am.NARCOGROUPID, 0) > 0 then 1 else 0 end
						from
							rls.v_Drug d
							left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
							left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
							left join rls.ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ActMatters_id
						where Drug_id = :Drug_id
				)
			)
		";
		$vWholeSale = "
			(
				select DrugMarkup_Wholesale
				from v_DrugMarkup
				where {$vPrice} between DrugMarkup_MinPrice and DrugMarkup_MaxPrice
				  and DrugMarkup_IsNarkoDrug = {$vIsNarco}
				  and DrugMarkup_begDT <= {$vSupplyDate}
				  and (DrugMarkup_endDT is null or DrugMarkup_endDT >= {$vSupplyDate})
			)
		";
		$vRetail = "
			(
				select DrugMarkup_Retail
				from v_DrugMarkup
				where {$vPrice} between DrugMarkup_MinPrice and DrugMarkup_MaxPrice
				  and DrugMarkup_IsNarkoDrug = {$vIsNarco}
				  and DrugMarkup_begDT <= {$vSupplyDate}
				  and (DrugMarkup_endDT is null or DrugMarkup_endDT >= {$vSupplyDate})
			)
		";
		$query = "
			select
				{$vPrice} as MakerPrice,
				{$vPriceDate} as MakerPriceDate,
				round(({$vPrice} + ({$vPrice}*{$vWholeSale}/100) + ({$vPrice}*{$vRetail}/100))*1.1, 2) as MaxRetailPriceNDS,
				round(({$vPrice} + ({$vPrice}*{$vWholeSale}/100))*1.1, 2) as MaxWholeSalePriceNDS
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return (isset($result[0])) ? $result : false;
	}

	/**
	 * Сохранение контрагента, в том случае, если подобного ему еще нет в БД.
	 * Используется при подписании ГК.
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveContragent($data)
	{
		if (!isset($data["Org_id"]) || !isset($data["ContragentType_Code"])) {
			return false;
		}
		$contragent_id = null;
		$mzorg_id = null;
		//ищем контрагент
		$query = "
			select Contragent_id
			from
				v_Contragent c
				left join v_ContragentType ct on ct.ContragentType_id = c.ContragentType_id
			where ct.ContragentType_Code = :ContragentType_Code
			  and Org_id = :Org_id
			  and Lpu_id is null
			order by Contragent_insDT desc
			limit 1
		";
		$queryParams = [
			"ContragentType_Code" => $data["ContragentType_Code"],
			"Org_id" => $data["Org_id"]
		];
		$result = $this->getFirstResultFromQuery($query, $queryParams);
		if (!empty($result) && $result > 0) {
			$contragent_id = $result;
		}
		//если не находим контрагента, то добавляем его в бд
		if ($contragent_id <= 0) {
			$query = "
				select
				    contragent_id as \"Contragent_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_contragent_ins(
				    server_id := :Server_id,
				    lpu_id := null,
				    contragenttype_id := (select ContragentType_id from v_ContragentType where ContragentType_Code = :ContragentType_Code),
				    contragent_code := (select coalesce(max(Contragent_Code), 10) + 1 from v_Contragent),
				    contragent_name := (select coalesce(Org_Name, 'Контрагент') from v_Org where Org_id = :Org_id),
				    org_id := :Org_id,
				    orgfarmacy_id := null,
				    lpusection_id := null,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"Server_id" => $data["Server_id"],
				"ContragentType_Code" => $data["ContragentType_Code"],
				"Org_id" => $data["Org_id"],
				"pmUser_id" => $this->getpmUser_id()
			];
			$result = $this->getFirstRowFromQuery($query, $queryParams);
			if (empty($result["Contragent_id"])) {
				throw new Exception("Ошибка при сохранении контрагента");
			}
			$contragent_id = $result["Contragent_id"];
		}
		return ["Contragent_id" => $contragent_id, "Error_Msg" => null];
	}

	/**
	 * Сохранение связи контрагента с организацией, в том случае если еще нет в БД
	 * Используется при подписании ГК.
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveContragentOrg($data)
	{
		if (!isset($data["Org_id"]) || !isset($data["Contragent_id"])) {
			return false;
		}
		$contragentorg_id = null;
		//проверяем связь контрагента с минздравом
		if ($data["Org_id"] > 0 && $data["Contragent_id"] > 0) {
			//ищем существующую связь
			$query = "
				select count(ContragentOrg_id) as cnt
				from v_ContragentOrg
				where Contragent_id = :Contragent_id
				  and Org_id = :Org_id
			";
			$queryParams = [
				"Contragent_id" => $data["Contragent_id"],
				"Org_id" => $data["Org_id"]
			];
			$mz_link = $this->getFirstResultFromQuery($query, $queryParams);
			if (empty($mz_link) || $mz_link < 1) {
				//добавляем связь
				$query = "
					select
					    contragentorg_id as \"ContragentOrg_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Msg\"
					from p_contragentorg_ins(
					    contragent_id := :Contragent_id,
					    org_id := :Org_id,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"Contragent_id" => $data["Contragent_id"],
					"Org_id" => $data["Org_id"],
					"pmUser_id" => $this->getpmUser_id()
				];
				$result = $this->getFirstRowFromQuery($query, $queryParams);

				if (empty($result["ContragentOrg_id"])) {
					throw new Exception("Ошибка при сохранении связи организации и контрагента");
				}
				$contragentorg_id = $result["ContragentOrg_id"];
			}
		}
		return ["ContragentOrg_id" => $contragentorg_id, "Error_Msg" => null];
	}

	/**
	 * Генерация номера для ГК
	 * @param $data
	 * @return array|bool
	 */
	function generateNum($data)
	{
		$query = "
			select coalesce(max(WhsDocumentUc_Num::int8), 0) + 1 as \"WhsDocumentUc_Num\"
			from v_WhsDocumentUc
			where WhsDocumentUc_Num not like '%.%'
			  and WhsDocumentUc_Num not like '%,%'
			  and isnumeric(WhsDocumentUc_Num) = 1
			  and length(WhsDocumentUc_Num) <= 18
			  and WhsDocumentType_id in (
					select WhsDocumentType_id
					from v_WhsDocumentType
					where WhsDocumentType_Code in (3, 6, 18)
				)
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Удаление произвольного обьекта.
	 * @param string $object_name
	 * @param array $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteObject($object_name, $data)
	{
		$selectString = "
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from p_{$object_name}_del(
			    {$object_name}_id := :{$object_name}_id
			);
		";
		$result = $this->getFirstRowFromQuery($query, $data);
		if (!$result || !is_array($result)) {
			throw new Exception("При удалении произошла ошибка");
		}
		if (empty($result["Error_Message"])) {
			$result["success"] = true;
		}
		return $result;
	}

	/**
	 * Получение идентификатора обьекта по коду
	 * @param $object_name
	 * @param $code
	 * @return bool|float|int|string|null
	 */
	function getObjectIdByCode($object_name, $code)
	{
		$query = "
			select {$object_name}_id
			from v_{$object_name}
			where {$object_name}_Code = :code
			limit 1
		";
		$queryParams = ["code" => $code];
		$result = $this->getFirstResultFromQuery($query, $queryParams);
		return $result && $result > 0 ? $result : false;
	}

	/**
	 * Получение идентификатора организации соответствующей Минздраву.
	 * @return bool|float|int|string
	 */
	function getMinzdravDloOrgId()
	{
		$query = "select GetMinzdravDloOrgId() as Org_id;";
		$org_id = $this->getFirstResultFromQuery($query);
		return $org_id;
	}

	/**
	 * Загрузка синонима
	 * @param $data
	 * @return array|bool
	 */
	function loadWhsDocumentSupplySpecDrug($data)
	{
		$query = "
			select
                wdssd.WhsDocumentSupplySpecDrug_id as \"WhsDocumentSupplySpecDrug_id\",
                wdss.WhsDocumentSupplySpec_id as \"WhsDocumentSupplySpec_id\",
                wdss.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
                wdssd.Drug_id as \"Drug_id\",
                wdssd.Drug_sid as \"Drug_sid\",
                wdssd.WhsDocumentSupplySpecDrug_Coeff as \"WhsDocumentSupplySpecDrug_Coeff\",
                wdssd.WhsDocumentSupplySpecDrug_Price as \"WhsDocumentSupplySpecDrug_Price\",
                wdssd.WhsDocumentSupplySpecDrug_PriceSyn as \"WhsDocumentSupplySpecDrug_PriceSyn\"
            from
                v_WhsDocumentSupplySpecDrug wdssd
                left join v_WhsDocumentSupplySpec wdss on wdss.WhsDocumentSupplySpec_id = wdssd.WhsDocumentSupplySpec_id
            where wdssd.WhsDocumentSupplySpecDrug_id = :WhsDocumentSupplySpecDrug_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Получение списка синонимов
	 * @param $filter
	 * @return array|bool
	 */
	function loadWhsDocumentSupplySpecDrugList($filter)
	{
		$where = [];
		$params = [];
		if ($filter["WhsDocumentSupply_id"] > 0) {
			$where[] = "wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id";
			$params["WhsDocumentSupply_id"] = $filter["WhsDocumentSupply_id"];
		}
		if ($filter["WhsDocumentSupplySpec_id"] > 0) {
			$where[] = "wdssd.WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id";
			$params["WhsDocumentSupplySpec_id"] = $filter["WhsDocumentSupplySpec_id"];
		}
		$whereString = implode(" and ", $where);
		if (strlen($whereString) > 0) {
			$whereString = "
				where
				    -- where
					{$whereString}
					-- end where
			";
		}
		$query = "
			select
			    -- select
                wdssd.WhsDocumentSupplySpecDrug_id as \"WhsDocumentSupplySpecDrug_id\",
                wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
                d.Drug_Name as \"Drug_Name\",
                wdss.WhsDocumentSupplySpec_KolvoUnit as \"WhsDocumentSupplySpec_KolvoUnit\",
                wdssd.WhsDocumentSupplySpecDrug_Price as \"WhsDocumentSupplySpecDrug_Price\",
                wdssd.WhsDocumentSupplySpecDrug_Coeff as \"WhsDocumentSupplySpecDrug_Coeff\",
                d_s.Drug_Name as \"Drug_NameSyn\",
                coalesce(wdss.WhsDocumentSupplySpec_KolvoUnit * wdssd.WhsDocumentSupplySpecDrug_Coeff, 0) as \"WhsDocumentSupplySpecDrug_KolvoUnit\",
                wdssd.WhsDocumentSupplySpecDrug_PriceSyn as \"WhsDocumentSupplySpecDrug_PriceSyn\"
                -- end select
            from
                -- from
                v_WhsDocumentSupplySpecDrug wdssd
                left join v_WhsDocumentSupplySpec wdss on wdss.WhsDocumentSupplySpec_id = wdssd.WhsDocumentSupplySpec_id
                left join v_WhsDocumentSupply wds on wds.WhsDocumentSupply_id = wdss.WhsDocumentSupply_id
                left join rls.v_Drug d on d.Drug_id = wdssd.Drug_id
                left join rls.v_Drug d_s on d_s.Drug_id = wdssd.Drug_sid
                -- end from
            {$whereString}
            order by
                -- order by
                wds.WhsDocumentUc_Num
                -- end order by
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query(getLimitSQLPH($query, $filter["start"], $filter["limit"]), $params);
		$count = $this->getFirstResultFromQuery(getCountSQLPH($query), $params);
		if (!is_object($result) || $count == false) {
			return false;
		}
		return ["data" => $result->result("array"), "totalCount" => $count];
	}

	/**
	 * Получение сопутствующих данных для синонима
	 * @param $data
	 * @return array|bool
	 */
	function getWhsDocumentSupplySpecDrugContext($data)
	{
		$query = "
			select
                d.DrugComplexMnn_id as \"DrugComplexMnn_id\",
                coalesce(dn.DrugNomen_Code, 'Нет') as \"DrugNomen_Code\",
                am.RUSNAME as \"Actmatters_Name\",
                (case when dsp.Price is not null then 1 else 0 end) as \"IsJnvlp\",
                dsp.Price as \"MakerPrice\",
                cast(round((dsp.Price + (dsp.Price*dm.Wholesale/100) + (dsp.Price*dm.Retail/100))*1.1, 2) as decimal(10,2)) as \"MaxRetailPriceNDS\",
                cast(round((dsp.Price + (dsp.Price*dm.Wholesale/100))*1.1, 2) as decimal(10,2)) as \"MaxWholeSalePriceNDS\"
            from
                rls.v_Drug d
                left join rls.v_Nomen n on n.NOMEN_ID = d.Drug_id
                left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
                left join lateral (
                    select i_dn.DrugNomen_Code
                    from rls.v_DrugNomen i_dn
                    where i_dn.Drug_id = d.Drug_id
                    order by i_dn.DrugNomen_id
                    limit 1
                ) dn on true
                left join lateral (
                    select i_dsp.DrugSalePrice_Price as Price
                    from rls.v_DrugSalePrice i_dsp
                    where i_dsp.Drug_id = d.Drug_id
                    	and (i_dsp.DrugSalePrice_relDT is null
                    		or i_dsp.DrugSalePrice_relDT <= coalesce(:Date, dbo.tzgetdate())
                        )
                    order by i_dsp.DrugSalePrice_relDT desc
                    limit 1
                ) dsp on true
                left join lateral (
                    select (case when coalesce(am.NARCOGROUPID, 0) = 2 then 1 else 0 end) as Code
                ) IsNarko on true
                left join lateral (
                    select
                        DrugMarkup_Wholesale as Wholesale,
                        DrugMarkup_Retail as Retail
                    from v_DrugMarkup dm
                        left join v_YesNo is_narko on is_narko.YesNo_id = DrugMarkup_IsNarkoDrug
                    where
                        dsp.Price between DrugMarkup_MinPrice and DrugMarkup_MaxPrice
                        and coalesce(is_narko.YesNo_Code, 0) = IsNarko.Code
                        and (dsp.Price is null
                            or (DrugMarkup_begDT <= coalesce(:Date, dbo.tzgetdate())
                            	and (DrugMarkup_endDT is null
                            		or DrugMarkup_endDT >= coalesce(:Date, dbo.tzgetdate())
                            	)
                            )
                       	)
                ) dm on true
            where
                d.Drug_id = :Drug_id;
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Загрузка данных для комбобокса "Контракт" на формах ввода синонимов
	 * @param $filter
	 * @return array|bool
	 */
	function loadSynonymSupplyCombo($filter)
	{
		$where = [];
		$params = [];
		if ($filter["WhsDocumentSupply_id"] > 0) {
			$where[] = "wds.WhsDocumentSupply_id = :WhsDocumentSupply_id";
			$params["WhsDocumentSupply_id"] = $filter["WhsDocumentSupply_id"];
		} else {
			if (!empty($filter["query"])) {
				$where[] = "wds.WhsDocumentUc_Name like :WhsDocumentUc_Name";
				$params["WhsDocumentUc_Name"] = "%" . $filter["query"] . "%";
			}
		}
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause) > 0) {
			$where_clause = "
				where
					{$where_clause}
			";
		}
		$query = "
            select
                wds.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
                wds.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
                coalesce(df.DrugFinance_Name, '') as \"DrugFinance_Name\",
                coalesce(wdcit.WhsDocumentCostItemType_Name, '') as \"WhsDocumentCostItemType_Name\",
                coalesce(o_s.Org_Name, '') as \"Supplier_Name\",
                to_char(wds.WhsDocumentUc_Date, '{$this->dateTimeForm104}') as \"WhsDocumentUc_Date\"
            from
                v_WhsDocumentSupply wds
                left join v_Drugfinance df on df.DrugFinance_id = wds.DrugFinance_id
                left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = wds.WhsDocumentCostItemType_id
                left join v_Org o_s on o_s.Org_id = wds.Org_sid
            {$where_clause}
            order by WhsDocumentUc_Name
			limit 100
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Загрузка данных для комбобокса "Медикамент (из контракта)" на формах ввода синонимов
	 * @param $filter
	 * @return array|bool
	 */
	function loadSynonymSupplySpecCombo($filter)
	{
		$where = [];
		$params = [];
		if ($filter["WhsDocumentSupplySpec_id"] > 0) {
			$where[] = "wdss.WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id";
			$params["WhsDocumentSupplySpec_id"] = $filter["WhsDocumentSupplySpec_id"];
		} else {
			$where[] = "wdss.Drug_id is not null";
			if ($filter["WhsDocumentSupply_id"] > 0) {
				$where[] = "wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id";
				$params["WhsDocumentSupply_id"] = $filter["WhsDocumentSupply_id"];
			}
			if (!empty($filter["query"])) {
				$where[] = "d.Drug_Name like :Drug_Name";
				$params["Drug_Name"] = "%" . $filter["query"] . "%";
			}
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause) > 0) {
			$where_clause = "
				where {$where_clause}
			";
		}
		$query = "
            select
                wdss.WhsDocumentSupplySpec_id as \"WhsDocumentSupplySpec_id\",
                d.Drug_id as \"Drug_id\",
                d.Drug_Name as \"Drug_Name\",
                dcmn.Actmatters_id as \"Actmatters_id\",
                wdss.WhsDocumentSupplySpec_PriceNDS as \"WhsDocumentSupplySpec_PriceNDS\",
                wdss.WhsDocumentSupplySpec_KolvoUnit as \"WhsDocumentSupplySpec_KolvoUnit\"
            from
                v_WhsDocumentSupplySpec wdss
                left join rls.v_Drug d on d.Drug_id = wdss.Drug_id
                left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
            {$where_clause}
            order by d.Drug_Name
			limit 100
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Загрузка данных для комбобокса "Медикамент" на формах ввода синонимов
	 * @param $filter
	 * @return array|bool
	 */
	function loadSynonymDrugCombo($filter)
	{
		$where = [];
		$params = [];
		if ($filter["Drug_id"] > 0) {
			$where[] = "d.Drug_id = :Drug_id";
			$params["Drug_id"] = $filter["Drug_id"];
		} else {
			if (!empty($filter["query"])) {
				$where[] = "d.Drug_Name like :Drug_Name";
				$params["Drug_Name"] = $filter["query"] . "%";
			}
			if (!empty($filter["Actmatters_id"])) {
				$where[] = "dcmn.Actmatters_id = :Actmatters_id";
				$params["Actmatters_id"] = $filter["Actmatters_id"];
			}
		}
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause) > 0) {
			$where_clause = "
				where {$where_clause}
			";
		}
		$query = "
            select
                d.Drug_id as \"Drug_id\",
                d.Drug_Name as \"Drug_Name\"
            from
                rls.v_Drug d
                left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
            {$where_clause}
            order by d.Drug_Name
			limit 250
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Определение факта использования синонима в документах учета или рецептах
	 * Возвращает количество документов/рецептов, а также данные первого документа или рецепта
	 * @param $data
	 * @return array
	 */
	function checkSynonymUsage($data)
	{
		$doc_data = ["cnt" => 0];
		$supply_id = null;
		$drug_id = null;
		if (!empty($data["WhsDocumentSupplySpecDrug_id"])) {
			$query = "
                select
                    wdss.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
                    wdssd.Drug_sid as \"Drug_sid\"
                from
                    v_WhsDocumentSupplySpecDrug wdssd
                    left join v_WhsDocumentSupplySpec wdss on wdss.WhsDocumentSupplySpec_id = wdssd.WhsDocumentSupplySpec_id
                where wdssd.WhsDocumentSupplySpecDrug_id = :WhsDocumentSupplySpecDrug_id;
            ";
			$queryParams = ["WhsDocumentSupplySpecDrug_id" => $data["WhsDocumentSupplySpecDrug_id"]];
			$result = $this->getFirstRowFromQuery($query, $queryParams);
			if (!empty($result["WhsDocumentSupply_id"])) {
				$supply_id = $result["WhsDocumentSupply_id"];
				$drug_id = $result["Drug_sid"];
			}
		}
		if ($supply_id > 0 && $drug_id > 0) {
			//получение списка документов и рецептов в которых используется данный синоним
			$query = "
                select
                    to_char(du.DocumentUc_setDate, '{$this->dateTimeForm104}') as \"Date\",
                    du.DocumentUc_Num as \"Num\",
                    o.Org_Name as \"Name\"
                from
                    v_DocumentUcStr dus
                    left join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
                    left join v_DrugDocumentType ddt on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                    left join v_DrugShipmentLink dsl on dsl.DocumentUcStr_id = dus.DocumentUcStr_id
                    left join v_DrugShipment ds on ds.DrugShipment_id = dsl.DrugShipment_id
                    left join v_Org o on o.Org_id = du.Org_id
                where dus.Drug_id = :Drug_id
                  and WhsDocumentSupply_id = :WhsDocumentSupply_id
                union all
                select
                    to_char(ro.EvnRecept_setDate, '{$this->dateTimeForm104}') as \"Date\",
                    coalesce(ro.EvnRecept_Ser, '')||coalesce(' '||ro.EvnRecept_Num, '') as \"Num\",
                    coalesce(ps.Person_SurName, '')||coalesce(' '||ps.Person_FirName, '')||coalesce(' '||ps.Person_SecName, '') as \"Name\"
                from
                    v_DocumentUcStr dus
                    left join v_DocumentUcStr dus2 on dus2.DocumentUcStr_id = dus.DocumentUcStr_oid
                    left join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
                    left join v_DrugDocumentType ddt on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                    left join v_DrugShipmentLink dsl on dsl.DocumentUcStr_id = dus2.DocumentUcStr_id
                    left join v_DrugShipment ds on ds.DrugShipment_id = dsl.DrugShipment_id
                    left join v_ReceptOtovUnSub ro on ro.ReceptOtov_id = dus.ReceptOtov_id
                    left join v_PersonState_all ps on ps.Person_id = ro.Person_id
                where dus.Drug_id = :Drug_id
                  and ddt.DrugDocumentType_SysNick = 'DocReal'
                  and ds.WhsDocumentSupply_id = :WhsDocumentSupply_id
                  and ro.ReceptOtov_id is not null
            ";
			$queryParams = [
				"WhsDocumentSupply_id" => $supply_id,
				"Drug_id" => $drug_id
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$doc_array = $result->result("array");
				if (count($doc_array) > 0) {
					$doc_data["cnt"] = count($doc_array);
					$doc_data["date"] = $doc_array[0]["Date"];
					$doc_data["num"] = $doc_array[0]["Num"];
					$doc_data["name"] = $doc_array[0]["Name"];
				}
			}
		}
		return $doc_data;
	}

	/**
	 * Загрузка списка позиций лота
	 * @param $filter
	 * @return array|bool
	 */
	function loadWhsDocumentProcurementRequestSpecCombo($filter)
	{
		$where = [];
		$params = [];
		if ($filter["WhsDocumentProcurementRequestSpec_id"] > 0) {
			$where[] = "wdprs.WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id";
			$params["WhsDocumentProcurementRequestSpec_id"] = $filter["WhsDocumentProcurementRequestSpec_id"];
		} else {
			if (!empty($filter["query"])) {
				$where[] = "dn.Drug_Name like :Drug_Name";
				$params["Drug_Name"] = $filter["query"] . "%";
			}
			if (!empty($filter["WhsDocumentProcurementRequest_id"])) {
				$where[] = "wdprs.WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id";
				$params["WhsDocumentProcurementRequest_id"] = $filter["WhsDocumentProcurementRequest_id"];
			}
			if (!empty($filter["Org_id"])) {
				$where[] = "wdpr.Org_aid = :Org_id";
				$params["Org_id"] = $filter["Org_id"];
			}
		}
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause) > 0) {
			$where_clause = "
				where {$where_clause}
			";
		}
		$query = "
            select
                wdprs.WhsDocumentProcurementRequestSpec_id as \"WhsDocumentProcurementRequestSpec_id\",
                dn.Drug_Name as \"Drug_Name\",
                wdprs.DrugComplexMnn_id as \"DrugComplexMnn_id\",
                tn.TRADENAMES_ID as \"Tradenames_id\",
                tn.NAME as \"Tradenames_Name\",
                wdprs.Okei_id as \"Okei_id\",
                wdprs.WhsDocumentProcurementRequestSpec_Kolvo as \"WhsDocumentProcurementRequestSpec_Kolvo\",
                wdprs.GoodsUnit_id as \"GoodsUnit_id\",
                wdprs.WhsDocumentProcurementRequestSpec_Count as \"WhsDocumentProcurementRequestSpec_Count\",
                ct.NAME as \"Country_Name\"
            from
                v_WhsDocumentProcurementRequestSpec wdprs
                left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = wdprs.DrugComplexMnn_id
                left join rls.v_TRADENAMES tn on tn.TRADENAMES_ID = wdprs.Tradenames_id
                left join rls.v_COUNTRIES ct on ct.COUNTRIES_ID = wdprs.COUNTRIES_ID
                left join v_WhsDocumentProcurementRequest wdpr on wdprs.WhsDocumentProcurementRequest_id = wdpr.WhsDocumentProcurementRequest_id
                left join lateral (
                    select
                        (case
                            when wdprs.DrugComplexMnn_id is not null then dcm.DrugComplexMnn_RusName
                            else tn.NAME
                        end) as Drug_Name
                ) as dn on true
            {$where_clause}
            order by dn.Drug_Name
			limit 250
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Загрузка списка МНН
	 * @param $filter
	 * @return array|bool
	 */
	function loadActmattersCombo($filter)
	{
		$where = [];
		$params = [];
		if (!empty($filter["Actmatters_id"])) {
			$where[] = "am.ACTMATTERS_id = :Actmatters_id";
			$params["Actmatters_id"] = $filter["Actmatters_id"];
		} else {
			if (!empty($filter["query"])) {
				$where[] = "am.RUSNAME like :RUSNAME";
				$params["RUSNAME"] = "%" . $filter["query"] . "%";
			}
		}
		if (!empty($filter["DrugComplexMnn_id"]) && empty($filter["Actmatters_id"])) {
			$query = "
                select
                    dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\",
                    dcmn.ACTMATTERS_id as \"Actmatters_id\",
                    am.RUSNAME as \"Actmatters_Name\"
                from
                    rls.v_DrugComplexMnn dcm
                    left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                    left join rls.ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
                where dcm.DrugComplexMnn_id = :DrugComplexMnn_id;
            ";
			$queryParams = ["DrugComplexMnn_id" => $filter["DrugComplexMnn_id"]];
			$mnn_data = $this->getFirstRowFromQuery($query, $queryParams);

			$where_str = "";
			//если наименование МНН сложное (есть + в названии), делим его на части и добавляем в список условий
			if (!empty($mnn_data["Actmatters_Name"]) && strpos($mnn_data["Actmatters_Name"], "+") !== false) {
				$part_array = preg_split("/\+|\[|\]/", $mnn_data["Actmatters_Name"]); //делим строку по символам "+", "[", "]"
				for ($i = 0; $i < count($part_array); $i++) {
					$part_array[$i] = trim(preg_replace('/\*/', '', $part_array[$i]));
					if (!empty($part_array[$i])) {
						$part_array[$i] = "amn.TrimName = '{$part_array[$i]}'";
					} else {
						unset($part_array[$i]);
					}
				}
				$where_str = join($part_array, " or ");
			}
			//если есть конкретный идентификатор МНН, добавляем его в список условий
			if (!empty($mnn_data["Actmatters_id"])) {
				if (!empty($where_str)) {
					$where_str .= " or ";
				}
				$where_str .= "am.ACTMATTERS_ID = :Actmatters_id";
				$params["Actmatters_id"] = $mnn_data["Actmatters_id"];
			}
			if (!empty($where_str)) {
				$where[] = "({$where_str})";
			}
		}
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause) > 0) {
			$where_clause = "
				where
					{$where_clause}
			";
		}
		$query = "
            select
                p.Actmatters_id as \"Actmatters_id\",
                p.Actmatters_Name as \"Actmatters_Name\"
            from (
                select
                	am.ACTMATTERS_ID as Actmatters_id,
                    am.RUSNAME as Actmatters_Name
                from
                    rls.v_Actmatters am
                    left join lateral (
                        select
                            ltrim(
                                rtrim(
                                    replace(
                                        am.RUSNAME, '*', ''
                                    )
                                )
                            ) as TrimName
                    ) as amn on true
                {$where_clause}                
                	union all
                select 0 as Actmatters_id,
                'Нет' as Actmatters_Name
                order by Actmatters_Name
                limit 250
            ) p
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Загрузка списка медикаментов
	 * @param $filter
	 * @return array|bool
	 */
	function loadDrugCombo($filter)
	{
		$where = [];
		$params = [];
		if ($filter["Drug_id"] > 0) {
			$where[] = "d.Drug_id = :Drug_id";
			$params["Drug_id"] = $filter["Drug_id"];
		} else {
			if ($filter["DrugComplexMnn_id"] > 0) {
				$where[] = "d.DrugComplexMnn_id = :DrugComplexMnn_id";
				$params["DrugComplexMnn_id"] = $filter["DrugComplexMnn_id"];
			} else if (!empty($filter["Actmatters_id"]) || $filter["Actmatters_id"] === "0") {
				$where[] = $filter["Actmatters_id"] > 0 ? "dcmn.Actmatters_id = :Actmatters_id" : "dcmn.Actmatters_id is null";
				$params["Actmatters_id"] = $filter["Actmatters_id"];
			} else {
				return false;
			}

			if (!empty($filter["query"])) {
				$where[] = "d.Drug_Name like :Drug_Name";
				$params["Drug_Name"] = preg_replace("/\s/", "%", $filter["query"]) . "%";
			}
		}
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause) > 0) {
			$where_clause = "
				where {$where_clause}
			";
		}
		$query = "
            select
                d.Drug_id as \"Drug_id\",
                d.Drug_Name as \"Drug_Name\",
                dn.DrugNomen_Code as \"DrugNomen_Code\",
                dn.DrugNds_id as \"DrugNds_id\",
                dcm.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
                fn.NAME as \"Firm_Name\",
                c.NAME as \"Country_Name\",
				rc.REGNUM as \"Reg_Num\",
				rceff.FULLNAME as \"Reg_Firm\",
				rceffc.NAME as \"Reg_Country\",
				to_char(rc.REGDATE, '{$this->dateTimeForm104}')||coalesce(' - '||to_char(rc.ENDDATE, '{$this->dateTimeForm104}'), '') as \"Reg_Period\",
				to_char(rc.Reregdate, '{$this->dateTimeForm104}') as \"Reg_ReRegDate\",
				d.DrugForm_Name as \"DrugForm_Name\",
				d.Drug_Fas as \"Drug_Fas\",
				d.Drug_Dose as \"Drug_Dose\",
				d.DrugTorg_Name as \"DrugTorg_Name\",
				d.DrugComplexMnn_id as \"DrugComplexMnn_id\"
            from
                rls.v_Drug d
                left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.PREP p on p.Prep_id = d.DrugPrep_id
				left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
				left join rls.FIRMS f on f.FIRMS_ID = p.FIRMID
				left join rls.FIRMNAMES fn on fn.FIRMNAMES_ID = f.NAMEID
				left join rls.v_COUNTRIES c on c.COUNTRIES_ID = f.COUNTID
				left join rls.REGCERT_EXTRAFIRMS rcef on rcef.CERTID = rc.REGCERT_ID
				left join rls.v_FIRMS rceff on rceff.FIRMS_ID = rcef.FIRMID
                left join rls.v_COUNTRIES rceffc on rceffc.COUNTRIES_ID = rceff.COUNTID
                left join lateral (
                    select
                        i_dn.DrugNomen_Code,
                        i_dn.DrugNds_id
                    from rls.v_DrugNomen i_dn
                    where i_dn.Drug_id = d.Drug_id
                    order by i_dn.DrugNomen_id
                    limit 1
                ) as dn on true
            {$where_clause}
            order by d.Drug_Name
			limit 250
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Загрузка списка медикаментов
	 */
	function loadDrugRequestCombo($data)
	{
		$join = [];
		$where = [
			"drc.DrugRequestCategory_SysNick = 'svod'",
			"drs.DrugRequestStatus_Code = 3"
		];
		$params = [];
		if ($data["DrugRequest_id"] > 0) {
			$where[] = "dr.DrugRequest_id = :DrugRequest_id";
			$params["DrugRequest_id"] = $data["DrugRequest_id"];
		} else {
			if (!empty($data["query"])) {
				$where[] = "dr.DrugRequest_Name like :DrugRequest_Name";
				$params["DrugRequest_Name"] = "%" . preg_replace("/\s/", "%", $data["query"]) . "%";
			}

			if ($data["mode"] == "lpu_user") {
				$join[] = "
            		left join lateral (
						select i_lpu_dr.DrugRequest_id
						from
							v_DrugRequestPurchase i_drp
							left join v_DrugRequest i_reg_dr on i_reg_dr.DrugRequest_id = i_drp.DrugRequest_lid
							left join v_DrugRequest i_lpu_dr on i_lpu_dr.DrugRequest_Version is null
								and i_lpu_dr.DrugRequestPeriod_id = i_reg_dr.DrugRequestPeriod_id
								and coalesce(i_lpu_dr.PersonRegisterType_id, 0) = coalesce(i_reg_dr.PersonRegisterType_id, 0)
								and coalesce(i_lpu_dr.DrugRequestKind_id, 0) = coalesce(i_reg_dr.DrugRequestKind_id, 0)
								and coalesce(i_lpu_dr.DrugGroup_id, 0) = coalesce(i_reg_dr.DrugGroup_id, 0)
								and i_lpu_dr.Lpu_id is not null
								and i_lpu_dr.MedPersonal_id is null
						where i_drp.DrugRequest_id = dr.DrugRequest_id
						  and i_lpu_dr.Lpu_id = :Lpu_id
						limit 1
            		) as lpu_dr on true
            	";
				$where[] = "lpu_dr.DrugRequest_id is null";
				$where[] = "dr.PersonRegisterType_id is null";
				$params["Lpu_id"] = $data["Lpu_id"];
			} else {
				$where[] = "dr.PersonRegisterType_id is not null";
			}
		}
		$join_clause = implode(" ", $join);
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause) > 0) {
			$where_clause = "
				where {$where_clause}
			";
		}
		$query = "
            select
                dr.DrugRequest_id as \"DrugRequest_id\",
                dr.DrugRequest_Name as \"DrugRequest_Name\",
                (case when wdprs.WhsDocumentProcurementRequest_id is not null then 1 else 0 end) as \"WhsDocumentProcurementRequest_Exists\"
            from
                v_DrugRequest dr
                left join v_DrugRequestCategory drc on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
                left join v_DrugRequestStatus drs on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                left join lateral (
                	select i_wdprs.WhsDocumentProcurementRequest_id 
					from
						v_DrugRequestPurchaseSpec i_drps
						left join v_WhsDocumentProcurementRequestSpec i_wdprs on i_wdprs.DrugRequestPurchaseSpec_id = i_drps.DrugRequestPurchaseSpec_id
					where i_drps.DrugRequest_id = dr.DrugRequest_id
					  and i_wdprs.WhsDocumentProcurementRequest_id is not null
					limit 1
                ) as wdprs on true
                {$join_clause}
            {$where_clause}
            order by dr.DrugRequest_insDT desc
			limit 250
		";
		$result = $this->queryResult($query, $params);
		return $result;
	}
}