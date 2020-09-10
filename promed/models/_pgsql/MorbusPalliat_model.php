<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
* MorbusPalliat_model - модель для MorbusPalliat
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Пермяков Александр
* @version      10.2012
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
 *
 * @property CI_DB_driver $db
 */
class MorbusPalliat_model extends SwPgModel
{
	protected $_MorbusType_id = 6;

	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm120 = "YYYY-MM-DD";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return "Palliat";
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusTypeId()
	{
		if (empty($this->_MorbusType_id)) {
			$this->load->library("swMorbus");
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->getMorbusTypeSysNick());
			if (empty($this->_MorbusType_id)) {
				throw new Exception("Не удалось определить тип заболевания", 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return "MorbusPalliat";
	}

	/**
	 * Создание специфики заболевания
	 * @param $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		if (empty($data["MorbusBase_id"]) ||
			empty($data["Person_id"]) ||
			empty($data["Morbus_id"]) || empty($data["Diag_id"]) || empty($data["Morbus_setDT"]) ||
			empty($data["mode"]) ||
			false == in_array($data["mode"], ["onBeforeViewData", "onBeforeSavePersonRegister", "onBeforeSaveEvnNotify"])
		) {
			throw new Exception("Переданы неправильные параметры", 500);
		}
		$this->setParams($data);
		$tableName = $this->tableName();
		$addFields = "
			{$tableName}_id := (
				select {$tableName}_id
				from v_{$tableName}
				where Morbus_id = :Morbus_id
				limit 1
			),
			Morbus_id := :Morbus_id,
		";
		$queryParams = [];
		$queryParams["pmUser_id"] = $this->promedUserId;
		$queryParams["Morbus_id"] = $data["Morbus_id"];
		$queryParams["Lpu_id"] = isset($data["Lpu_id"]) ? $data["Lpu_id"] : $this->sessionParams["lpu_id"];

		$vkdata = $this->checkEvnVk($data);
		if ($vkdata !== false) {
			$addFields .= "
				MorbusPalliat_VKDate := :MorbusPalliat_VKDate,
				MorbusPalliat_IsFamCare := :MorbusPalliat_IsFamCare,
				PalliativeType_id := :PalliativeType_id,
				MorbusPalliat_IsTIR := :MorbusPalliat_IsTIR,
				MorbusPalliat_TextTIR := :MorbusPalliat_TextTIR,
			";
			$queryParams["MorbusPalliat_VKDate"] = $vkdata["MorbusPalliat_VKDate"];
			$queryParams["MorbusPalliat_IsFamCare"] = $vkdata["MorbusPalliat_IsFamCare"];
			$queryParams["PalliativeType_id"] = $vkdata["PalliativeType_id"];
			$queryParams["MorbusPalliat_IsTIR"] = $vkdata["MorbusPalliat_IsTIR"];
			$queryParams["MorbusPalliat_TextTIR"] = $vkdata["MorbusPalliat_TextTIR"];
		}
		$addFields .= "
			pmUser_id := :pmUser_id
		";
		$query = "
			select
				{$tableName}_id as \"{$tableName}_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_{$tableName}_ins(
			    {$addFields}
			);
		";
		if($isAllowTransaction == true) {
			$this->beginTransaction();
		}
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			if($isAllowTransaction == true) {
				$this->rollbackTransaction();
			}
			throw new Exception("Ошибка БД", 500);
		}
		$resp = $result->result("array");
		if (!empty($resp[0]["Error_Msg"])) {
			if($isAllowTransaction == true) {
				$this->rollbackTransaction();
			}
			throw new Exception($resp[0]["Error_Msg"], 500);
		}
		if (empty($resp[0][$tableName . "_id"])) {
			if($isAllowTransaction == true) {
				$this->rollbackTransaction();
			}
			throw new Exception("Не удалось создать объект {$tableName}", 500);
		}
		if ($vkdata !== false) {
			$vkdata["MorbusPalliat_id"] = $resp[0]["MorbusPalliat_id"];
			$vkdata["pmUser_id"] = $this->promedUserId;
			$this->saveMainSyndromeLink($vkdata);
			$this->saveTechnicInstrumRehabLink($vkdata);
			foreach ($vkdata["PalliatFamilyCareList"] as $item) {
				$item["MorbusPalliat_id"] = $vkdata["MorbusPalliat_id"];
				$item["pmUser_id"] = $this->promedUserId;
				$this->savePalliatFamilyCare($item);
			}
		}
		$this->_saveResponse[$tableName . "_id"] = $resp[0][$tableName . "_id"];
		return $this->_saveResponse;
	}

	/**
	 * Загрузка формы редактирования записи регистра
	 * @param $data
	 * @return array|false
	 */
	public function load($data)
	{
		$params = ["MorbusPalliat_id" => $data["MorbusPalliat_id"]];
		$selectString = "
			MO.MorbusPalliat_id as \"MorbusPalliat_id\",
			MO.Morbus_id as \"Morbus_id\",
			MO.MorbusPalliat_IsIVL as \"MorbusPalliat_IsIVL\",
			MO.MorbusPalliat_IsAnesthesia as \"MorbusPalliat_IsAnesthesia\",
			MO.MorbusPalliat_IsZond as \"MorbusPalliat_IsZond\",
			MO.ViolationsDegreeType_id as \"ViolationsDegreeType_id\",
			case when MO.AnesthesiaType_id is null and MO.MorbusPalliat_IsAnesthesia = 1 then -1 else MO.AnesthesiaType_id end as \"AnesthesiaType_id\",
			MO.Lpu_sid as \"Lpu_sid\",
			MO.Lpu_aid as \"Lpu_aid\",
			M.Diag_id as \"Diag_id\",
			M.Person_id as \"Person_id\",
			to_char(MO.MorbusPalliat_VKDate, '{$this->dateTimeForm104}') as \"MorbusPalliat_VKDate\",
			to_char(MO.MorbusPalliat_DiagDate, '{$this->dateTimeForm104}') as \"MorbusPalliat_DiagDate\",
			MO.RecipientInformation_id as \"RecipientInformation_id\",
			MO.MorbusPalliat_IsFamCare as \"MorbusPalliat_IsFamCare\",
			MO.PalliativeType_id as \"PalliativeType_id\",
			to_char(MO.MorbusPalliat_StomPrescrDate, '{$this->dateTimeForm104}') as \"MorbusPalliat_StomPrescrDate\",
			to_char(MO.MorbusPalliat_StomSetDate, '{$this->dateTimeForm104}') as \"MorbusPalliat_StomSetDate\",
			case when MO.MorbusPalliat_VLbegDate is not null then
				to_char(MO.MorbusPalliat_VLbegDate, '{$this->dateTimeForm104}')||' - '||to_char(MO.MorbusPalliat_VLendDate, '{$this->dateTimeForm104}')
			end as \"MorbusPalliat_VLDateRange\",
			MRL.MethodRaspiratAssist as \"MethodRaspiratAssist\",
			MO.MorbusPalliat_IsTIR as \"MorbusPalliat_IsTIR\",
			to_char(MO.MorbusPalliat_VKTIRDate, '{$this->dateTimeForm104}') as \"MorbusPalliat_VKTIRDate\",
			to_char(MO.MorbusPalliat_TIRDate, '{$this->dateTimeForm104}') as \"MorbusPalliat_TIRDate\",
			MO.MorbusPalliat_TextTIR as \"MorbusPalliat_TextTIR\",
			PEVKD.MainSyndrome as \"MainSyndrome\",
			MO.PalliatIndicatChangeCondit_id as \"PalliatIndicatChangeCondit_id\",
			MO.MorbusPalliat_OtherIndicatChangeCondit as \"MorbusPalliat_OtherIndicatChangeCondit\",
			to_char(MO.MorbusPalliat_ChangeConditDate, '{$this->dateTimeForm104}') as \"MorbusPalliat_ChangeConditDate\",
			to_char(MO.MorbusPalliat_SocialProtDate, '{$this->dateTimeForm104}') as \"MorbusPalliat_SocialProtDate\",
			MO.MorbusPalliat_SocialProt as \"MorbusPalliat_SocialProt\"
		";
		$fromString = "
			v_MorbusPalliat MO
			left join v_Morbus M on M.Morbus_id = MO.Morbus_id
			left join lateral (
				select
					string_agg(cast(MethodRaspiratAssist_id as varchar), ',') as MethodRaspiratAssist
				from MethodRaspiratAssistLink
				where MorbusPalliat_id = MO.MorbusPalliat_id
				limit 1
			) as MRL on true
			left join lateral (
				select
					string_agg(cast(MainSyndrome_id as varchar), ',') as MainSyndrome
				from MainSyndromeLink
				where MorbusPalliat_id = MO.MorbusPalliat_id
				limit 1
			) as PEVKD on true
		";
		$whereString = "
			MO.MorbusPalliat_id = :MorbusPalliat_id
		";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $resp;
		}
		$query = "
			select MedProductCard_id as \"MedProductCard_id\"
			from MedProductCardLink
			where MorbusPalliat_id = :MorbusPalliat_id
		";
		$params = ["MorbusPalliat_id" => $data["MorbusPalliat_id"]];
		$resp[0]["MedProductCard"] = $this->queryResult($query, $params);
		$query = "
			select
				coalesce(TechnicInstrumRehab_id, 9999) as \"id\",
				to_char(TechnicInstrumRehabLink_TIRDate, '{$this->dateTimeForm104}') as \"date\"
			from TechnicInstrumRehabLink
			where MorbusPalliat_id = :MorbusPalliat_id
		";
		$params = ["MorbusPalliat_id" => $data["MorbusPalliat_id"]];
		$tir = $this->queryResult($query, $params);
		$resp[0]["TechnicInstrumRehab"] = json_encode($tir);
		if (!empty($data["Evn_id"])) {
			$resp[0]["Evn_id"] = $data["Evn_id"];
		}
		return $resp;
	}

	/**
	 * Сохранение формы редактирования записи регистра
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public function save($data)
	{
		$this->beginTransaction();
		if (!empty($data["MorbusPalliat_id"])) {
			$proc = "p_MorbusPalliat_upd";
			$sql = "
				select Diag_id as \"Diag_id\"
				from v_MorbusPalliat
				where MorbusPalliat_id = :MorbusPalliat_id
				limit 1
			";
			$sqlParams = ["MorbusPalliat_id" => $data["MorbusPalliat_id"]];
			$data["Diag_id"] = $this->getFirstResultFromQuery($sql, $sqlParams);
		} else {
			$data["MorbusPalliat_id"] = null;
			$proc = "p_MorbusPalliat_ins";
		}
		$data["MorbusPalliat_IsAnesthesia"] = null;
		if (!empty($data["AnesthesiaType_id"])) {
			if ($data["AnesthesiaType_id"] < 0) {
				$data["AnesthesiaType_id"] = null;
				$data["MorbusPalliat_IsAnesthesia"] = 1;
			} else {
				$data["MorbusPalliat_IsAnesthesia"] = 2;
			}
		}
		if (!empty($data["TechnicInstrumRehab_id"]) && $data["TechnicInstrumRehab_id"] < 0) {
			$data["TechnicInstrumRehab_id"] = null;
		}
		if (isset($data["MorbusPalliat_VLDateRange"]) && !empty($data["MorbusPalliat_VLDateRange"][0])) {
			$data["MorbusPalliat_VLbegDate"] = $data["MorbusPalliat_VLDateRange"][0];
			$data["MorbusPalliat_VLendDate"] = $data["MorbusPalliat_VLDateRange"][1];
		} else {
			$data["MorbusPalliat_VLbegDate"] = null;
			$data["MorbusPalliat_VLendDate"] = null;
		}
		$PalliatFamilyCare = json_decode($data["PalliatFamilyCare"], true);
		$selectString = "
			morbuspalliat_id as \"MorbusPalliat_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    MorbusPalliat_id := :MorbusPalliat_id,
				Morbus_id := :Morbus_id,
				MorbusPalliat_IsIVL := :MorbusPalliat_IsIVL,
				MorbusPalliat_IsAnesthesia := :MorbusPalliat_IsAnesthesia,
				MorbusPalliat_IsZond := :MorbusPalliat_IsZond,
				ViolationsDegreeType_id := :ViolationsDegreeType_id,
				AnesthesiaType_id := :AnesthesiaType_id,
				Lpu_sid := :Lpu_sid,
				Lpu_aid := :Lpu_aid,
				MorbusPalliat_VKDate := :MorbusPalliat_VKDate,
				MorbusPalliat_DiagDate := :MorbusPalliat_DiagDate,
				RecipientInformation_id := :RecipientInformation_id,
				MorbusPalliat_IsFamCare := :MorbusPalliat_IsFamCare,
				PalliativeType_id := :PalliativeType_id,
				MorbusPalliat_StomPrescrDate := :MorbusPalliat_StomPrescrDate,
				MorbusPalliat_StomSetDate := :MorbusPalliat_StomSetDate,
				MorbusPalliat_VLbegDate := :MorbusPalliat_VLbegDate,
				MorbusPalliat_VLendDate := :MorbusPalliat_VLendDate,
				MorbusPalliat_IsTIR := :MorbusPalliat_IsTIR,
				MorbusPalliat_VKTIRDate := :MorbusPalliat_VKTIRDate,
				MorbusPalliat_TIRDate := :MorbusPalliat_TIRDate,
				MorbusPalliat_TextTIR := :MorbusPalliat_TextTIR,
				PalliatIndicatChangeCondit_id := :PalliatIndicatChangeCondit_id,
				MorbusPalliat_OtherIndicatChangeCondit := :MorbusPalliat_OtherIndicatChangeCondit,
				MorbusPalliat_ChangeConditDate := :MorbusPalliat_ChangeConditDate,
				MorbusPalliat_SocialProtDate := :MorbusPalliat_SocialProtDate,
				MorbusPalliat_SocialProt := :MorbusPalliat_SocialProt,
				pmUser_id := :pmUser_id
			);
		";
		$resp = $this->queryResult($query, $data);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		$data["MorbusPalliat_id"] = $resp[0]["MorbusPalliat_id"];
		$this->saveMethodRaspiratAssist($data);
		$this->saveMedProductCard($data);
		$this->saveMainSyndromeLink($data);
		$this->saveTechnicInstrumRehabLink($data);
		if (!empty($data["Evn_id"])) {
			$query = "
				select MorbusPalliatEvn_id as \"MorbusPalliatEvn_id\"
				from v_MorbusPalliatEvn
				where Evn_id = :Evn_id
				order by MorbusPalliatEvn_id desc
				limit 1
			";
			$data["MorbusPalliatEvn_id"] = $this->getFirstResultFromQuery($query, $data, true);
			$proc = (empty($data["MorbusPalliatEvn_id"]))?"p_MorbusPalliatEvn_ins":"p_MorbusPalliatEvn_upd";
			$selectString = "
				morbuspalliatevn_id as \"MorbusPalliatEvn_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			";
			$query = "
				select {$selectString}
				from {$proc}(
				    MorbusPalliatEvn_id := :MorbusPalliatEvn_id,
					Evn_id := :Evn_id,
					MorbusPalliatEvn_IsIVL := :MorbusPalliat_IsIVL,
					MorbusPalliatEvn_IsAnesthesia := :MorbusPalliat_IsAnesthesia,
					MorbusPalliatEvn_IsZond := :MorbusPalliat_IsZond,
					ViolationsDegreeType_id := :ViolationsDegreeType_id,
					AnesthesiaType_id := :AnesthesiaType_id,
					Lpu_sid := :Lpu_sid,
					Lpu_aid := :Lpu_aid,
					MorbusPalliatEvn_VKDate := :MorbusPalliat_VKDate,
					MorbusPalliatEvn_DiagDate := :MorbusPalliat_DiagDate,
					RecipientInformation_id := :RecipientInformation_id,
					MorbusPalliatEvn_IsFamCare := :MorbusPalliat_IsFamCare,
					PalliativeType_id := :PalliativeType_id,
					MorbusPalliatEvn_StomPrescrDate := :MorbusPalliat_StomPrescrDate,
					MorbusPalliatEvn_StomSetDate := :MorbusPalliat_StomSetDate,
					MorbusPalliatEvn_VLbegDate := :MorbusPalliat_VLbegDate,
					MorbusPalliatEvn_VLendDate := :MorbusPalliat_VLendDate,
					MorbusPalliatEvn_IsTIR := :MorbusPalliat_IsTIR,
					MorbusPalliatEvn_VKTIRDate := :MorbusPalliat_VKTIRDate,
					MorbusPalliatEvn_TIRDate := :MorbusPalliat_TIRDate,
					TechnicInstrumRehab_id := :TechnicInstrumRehab_id,
					MorbusPalliatEvn_TextTIR := :MorbusPalliat_TextTIR,
					PalliatIndicatChangeCondit_id := :PalliatIndicatChangeCondit_id,
					MorbusPalliatEvn_OtherIndicatChangeCondit := :MorbusPalliat_OtherIndicatChangeCondit,
					MorbusPalliatEvn_ChangeConditDate := :MorbusPalliat_ChangeConditDate,
					MorbusPalliatEvn_SocialProt := :MorbusPalliat_SocialProt,
					pmUser_id := :pmUser_id
				);
			";
			$resp = $this->queryResult($query, $data);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		if (!empty($PalliatFamilyCare) && is_array($PalliatFamilyCare)) {
			foreach ($PalliatFamilyCare as $item) {
				$item["MorbusPalliat_id"] = $data["MorbusPalliat_id"];
				$item["pmUser_id"] = $data["pmUser_id"];
				switch ($item["RecordStatus_Code"]) {
					case 0:
						$item["PalliatFamilyCare_id"] = null;
						$resp = $this->savePalliatFamilyCare($item);
						break;
					case 2:
						$resp = $this->savePalliatFamilyCare($item);
						break;
					case 3:
						$resp = $this->deletePalliatFamilyCare($item);
						break;
				}
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}
		}
		$this->commitTransaction();
		return [[
			"success" => true,
			"MorbusPalliat_id" => $data["MorbusPalliat_id"],
		]];
	}

	/**
	 * Сохранение
	 * @param $data
	 * @return bool
	 */
	function saveMainSyndromeLink($data)
	{
		$sql = "
			select MainSyndromeLink_id as \"MainSyndromeLink_id\"
			from MainSyndromeLink
			where MorbusPalliat_id = :MorbusPalliat_id
		";
		$sqlParams = ["MorbusPalliat_id" => $data["MorbusPalliat_id"]];
		$tmp = $this->queryList($sql, $sqlParams);
		foreach ($tmp as $row) {
			$sql = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_mainsyndromelink_del(mainsyndromelink_id := ?);
			";
			$sqlParams = [$row];
			$this->db->query($sql, $sqlParams);
		}
		if (empty($data["MainSyndrome"])) {
			return false;
		}
		$data["MainSyndrome"] = explode(",", $data["MainSyndrome"]);
		foreach ($data["MainSyndrome"] as $row) {
			$sql = "
				select
					mainsyndromelink_id as \"MainSyndromeLink_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_mainsyndromelink_ins(
				    mainsyndromelink_id := :MainSyndromeLink_id,
				    mainsyndrome_id := :MainSyndrome_id,
				    morbuspalliat_id := :MorbusPalliat_id,
				    pmuser_id := :pmUser_id
				);
			";
			$sqlParams = [
				"MainSyndromeLink_id" => null,
				"MorbusPalliat_id" => $data["MorbusPalliat_id"],
				"MainSyndrome_id" => $row,
				"pmUser_id" => $data["pmUser_id"],
			];
			$this->queryResult($sql, $sqlParams);
		}
		return true;
	}

	/**
	 * Сохранение
	 * @param $data
	 * @return bool
	 */
	function saveTechnicInstrumRehabLink($data)
	{
		$sql = "
			select TechnicInstrumRehabLink_id as \"TechnicInstrumRehabLink_id\"
			from TechnicInstrumRehabLink
			where MorbusPalliat_id = :MorbusPalliat_id
		";
		$sqlParams = ["MorbusPalliat_id" => $data["MorbusPalliat_id"]];
		$tmp = $this->queryList($sql, $sqlParams);
		foreach ($tmp as $row) {
			$sql = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_technicinstrumrehablink_del(technicinstrumrehablink_id := ?);
			";
			$sqlParams = [$row];
			$this->db->query($sql, $sqlParams);
		}
		if (empty($data["TechnicInstrumRehab"])) {
			return false;
		}
		$data["TechnicInstrumRehab"] = json_decode($data["TechnicInstrumRehab"], true);
		foreach ($data["TechnicInstrumRehab"] as $row) {
			$sql = "
				select
					technicinstrumrehablink_id as \"TechnicInstrumRehabLink_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_technicinstrumrehablink_ins(
				    technicinstrumrehablink_id := :TechnicInstrumRehabLink_id,
				    technicinstrumrehab_id := :TechnicInstrumRehab_id,
				    morbuspalliat_id := :MorbusPalliat_id,
				    technicinstrumrehablink_tirdate := :TechnicInstrumRehabLink_TIRDate,
				    pmuser_id := :pmUser_id
				);
			";
			$params = [
				"TechnicInstrumRehabLink_id" => null,
				"MorbusPalliat_id" => $data["MorbusPalliat_id"],
				"TechnicInstrumRehab_id" => ($row["id"] != 9999) ? $row["id"] : null,
				"TechnicInstrumRehabLink_TIRDate" => $row["date"],
				"pmUser_id" => $data["pmUser_id"],
			];
			$this->queryResult($sql, $params);
		}
		return true;
	}

	/**
	 * Сохранение Метода респираторной поддержки
	 * @param $data
	 * @return bool
	 */
	public function saveMethodRaspiratAssist($data)
	{
		$sql = "
			select MethodRaspiratAssistLink_id as \"MethodRaspiratAssistLink_id\"
			from MethodRaspiratAssistLink
			where MorbusPalliat_id = :MorbusPalliat_id
		";
		$sqlParams = ["MorbusPalliat_id" => $data['MorbusPalliat_id']];
		$tmp = $this->queryList($sql, $sqlParams);
		foreach ($tmp as $row) {
			$sql = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_methodraspiratassistlink_del(methodraspiratassistlink_id := ?);
			";
			$sqlParams = [$row];
			$this->db->query($sql, $sqlParams);
		}
		if (empty($data["MethodRaspiratAssist"])) {
			return false;
		}
		$data["MethodRaspiratAssist"] = explode(",", $data["MethodRaspiratAssist"]);
		foreach ($data["MethodRaspiratAssist"] as $row) {
			$sql = "
				select
					methodraspiratassistlink_id as \"MethodRaspiratAssistLink_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_methodraspiratassistlink_ins(
				    methodraspiratassistlink_id := :MethodRaspiratAssistLink_id,
				    methodraspiratassist_id := :MethodRaspiratAssist_id,
				    morbuspalliat_id := :MorbusPalliat_id,
				    pmuser_id := :pmUser_id
				);
			";
			$sqlParams = [
				"MethodRaspiratAssistLink_id" => null,
				"MorbusPalliat_id" => $data["MorbusPalliat_id"],
				"MethodRaspiratAssist_id" => $row,
				"pmUser_id" => $data["pmUser_id"],
			];
			$this->queryResult($sql, $sqlParams);
		}
		return true;
	}

	/**
	 * @param $data
	 */
	function saveMedProductCard($data)
	{
		$mpc_list = [];
		$sql = "
			select MedProductCardLink_id as \"MedProductCardLink_id\"
			from MedProductCardLink
			where MorbusPalliat_id = :MorbusPalliat_id
		";
		$sqlParams = ["MorbusPalliat_id" => $data["MorbusPalliat_id"]];
		$MedProductCardLink = $this->queryList($sql, $sqlParams);
		$data["MedProductCard"] = (array)$data["MedProductCard"];
		// добавляем/обновляем
		foreach ($data["MedProductCard"] as $mpc) {
			if (empty($mpc->MedProductCard_id)) {
				continue;
			}
			$procedure = empty($mpc->MedProductCardLink_id) ? "p_MedProductCardLink_ins" : "p_MedProductCardLink_upd";
			$selectString = "
				medproductcardlink_id as \"MedProductCardLink_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			";
			$sql = "
				select {$selectString}
				from {$procedure}(
				    medproductcardlink_id := :MedProductCardLink_id,
				    medproductcard_id := :MedProductCard_id,
				    morbuspalliat_id := :MorbusPalliat_id,
				    pmuser_id := :pmUser_id
				);
			";
			$sqlParams = [
				"MedProductCardLink_id" => $mpc->MedProductCardLink_id,
				"MedProductCard_id" => $mpc->MedProductCard_id,
				"MorbusPalliat_id" => $data["MorbusPalliat_id"],
				"pmUser_id" => $data["pmUser_id"],
			];
			$this->queryResult($sql, $sqlParams);
			if (!empty($mpc->MedProductCardLink_id)) {
				$mpc_list[] = $mpc->MedProductCardLink_id;
			}
		}
		// то, что было в БД, но уже нет на форме - удаляем
		$delmpc = array_diff($MedProductCardLink, $mpc_list);
		foreach ($delmpc as $mpc) {
			$sql = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_medproductcardlink_del(
				    medproductcardlink_id := :MedProductCardLink_id
				);
			";
			$sqlParams = ["MedProductCardLink_id" => $mpc];
			$this->queryResult($sql, $sqlParams);
		}
	}

	/**
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function savePalliatFamilyCare($data)
	{
		$params = [
			"PalliatFamilyCare_id" => !empty($data["PalliatFamilyCare_id"]) ? $data["PalliatFamilyCare_id"] : null,
			"MorbusPalliat_id" => $data["MorbusPalliat_id"],
			"FamilyRelationType_id" => !empty($data["FamilyRelationType_id"]) ? $data["FamilyRelationType_id"] : null,
			"PalliatFamilyCare_Age" => !empty($data["PalliatFamilyCare_Age"]) ? $data["PalliatFamilyCare_Age"] : null,
			"PalliatFamilyCare_Phone" => !empty($data["PalliatFamilyCare_Phone"]) ? $data["PalliatFamilyCare_Phone"] : null,
			"EvnVK_id" => !empty($data["EvnVK_id"]) ? $data["EvnVK_id"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];
		if (empty($params["PalliatFamilyCare_id"])) {
			$proc = "p_PalliatFamilyCare_ins";
		} else {
			$proc = "p_PalliatFamilyCare_upd";
		}
		$selectString = "
			palliatfamilycare_id as \"PalliatFamilyCare_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    palliatfamilycare_id := :PalliatFamilyCare_id,
			    familyrelationtype_id := :FamilyRelationType_id,
			    palliatfamilycare_age := :PalliatFamilyCare_Age,
			    palliatfamilycare_phone := :PalliatFamilyCare_Phone,
			    morbuspalliat_id := :MorbusPalliat_id,
			    evnvk_id := :EvnVK_id,
			    pmuser_id := :pmUser_id
			);
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при сохранении сведений о родственниках, осуществляющих уход за пациентом");
		}
		return $resp;
	}

	/**
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function deletePalliatFamilyCare($data)
	{
		$params = ["PalliatFamilyCare_id" => $data["PalliatFamilyCare_id"],];
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_palliatfamilycare_del(palliatfamilycare_id := :PalliatFamilyCare_id);
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при уделении сведений о родственниках, осуществляющих уход за пациентом");
		}
		return $resp;
	}

	/**
	 * @param $data
	 * @return array|false
	 */
	function loadPalliatFamilyCareList($data)
	{
		$params = ["MorbusPalliat_id" => $data["MorbusPalliat_id"],];
		$query = "
			select
				PalliatFamilyCare_id as \"PalliatFamilyCare_id\",
				MorbusPalliat_id as \"MorbusPalliat_id\",
				FamilyRelationType_id as \"FamilyRelationType_id\",
				PalliatFamilyCare_Age as \"PalliatFamilyCare_Age\",
				PalliatFamilyCare_Phone as \"PalliatFamilyCare_Phone\",
				EvnVK_id as \"EvnVK_id\",
				1 as \"RecordStatus_Code\"
			from v_PalliatFamilyCare
			where MorbusPalliat_id = :MorbusPalliat_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @param $data
	 * @return array|false
	 */
	function loadLpuList($data)
	{
		$params = [
			"PalliativeType_id" => $data["PalliativeType_id"],
			"Date" => !empty($data["Date"]) ? $data["Date"] : $this->currentDT->format("Y-m-d"),
		];
		$query = "
			select distinct
				Lpu.Lpu_id as \"Lpu_id\",
				Lpu.Org_id as \"Org_id\",
				Lpu.Org_tid as \"Org_tid\",
				Lpu.Lpu_IsOblast as \"Lpu_IsOblast\",
				rtrim(Lpu.Lpu_Name) as \"Lpu_Name\",
				rtrim(Lpu.Lpu_Nick) as \"Lpu_Nick\",
				Lpu.Lpu_Ouz as \"Lpu_Ouz\",
				Lpu.Lpu_RegNomC as \"Lpu_RegNomC\",
				Lpu.Lpu_RegNomC2 as \"Lpu_RegNomC2\",
				Lpu.Lpu_RegNomN2 as \"Lpu_RegNomN2\",
				Lpu.Lpu_isDMS as \"Lpu_isDMS\",
				to_char(Lpu.Lpu_DloBegDate, '{$this->dateTimeForm104}') as \"Lpu_DloBegDate\",
				to_char(Lpu.Lpu_DloEndDate, '{$this->dateTimeForm104}') as \"Lpu_DloEndDate\",
				to_char(Lpu.Lpu_BegDate, '{$this->dateTimeForm104}') as \"Lpu_BegDate\",
				to_char(Lpu.Lpu_EndDate, '{$this->dateTimeForm104}') as \"Lpu_EndDate\",
				coalesce(LpuLevel.LpuLevel_Code, 0) as \"LpuLevel_Code\",
				coalesce(Org.Org_IsAccess, 1) as \"Lpu_IsAccess\",
				coalesce(Org.Org_IsNotForSystem, 1) as \"Lpu_IsNotForSystem\",
				coalesce(Lpu.Lpu_IsMse, 1) as \"Lpu_IsMse\"
			from
				v_Lpu Lpu
				inner join v_Org Org on Org.Org_id = Lpu.Org_id
				left join LpuLevel on LpuLevel.LpuLevel_id = Lpu.LpuLevel_id
			where Lpu.Lpu_endDate is null
				and 1 = case when :PalliativeType_id = 5 and exists(
							select *
							from v_LpuUnit LU
							where LU.Lpu_id = Lpu.Lpu_id
								and coalesce(LU.LpuUnit_isPallCC, 1) = 2
								and :Date between LU.LpuUnit_begDate and coalesce(LU.LpuUnit_endDate, :Date)
						) then 1
						when exists (
							select *
							from v_LpuSection LS
							where LS.Lpu_id = Lpu.Lpu_id
								and LS.PalliativeType_id = :PalliativeType_id
								and :Date between LS.LpuSection_setDate and coalesce(LS.LpuSection_disDate, :Date)
						) then 1
					end
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @param $data
	 * @return array|false
	 */
	function loadMainSyndromeList($data)
	{
		$query = "
			select
				MS.MainSyndrome_id as \"MainSyndrome_id\",
				MS.MainSyndrome_Code as \"MainSyndrome_Code\",
				MS.MainSyndrome_Name as \"MainSyndrome_Name\"
			from v_MainSyndrome MS
		";
		return $this->queryResult($query);
	}

	/**
	 * @param $data
	 * @return array|false
	 */
	function loadMedProductCardList($data)
	{
		$params = [
			"Lpu_did" => !empty($data["Lpu_did"]) ? $data["Lpu_did"] : null,
		];
		$filters = "";
		if (!empty($data["query"])) {
			$filters .= " and MPClass.MedProductClass_Name ilike :query || '%'";
			$params["query"] = $data["query"];
		}
		$query = "
			select distinct
				MPCard.MedProductCard_id as \"MedProductCard_id\",
				MPClass.MedProductClass_id as \"MedProductClass_id\",
				MPClass.MedProductClass_Name as \"MedProductClass_Name\"
			from
				passport.v_MedProductCard MPCard
				inner join passport.v_MedProductClass MPClass on MPClass.MedProductClass_id = MPCard.MedProductClass_id
				inner join passport.v_CardType CardType on CardType.CardType_id = MPClass.CardType_id
				inner join LpuBuilding LB on MPCard.LpuBuilding_id = LB.LpuBuilding_id
			where LB.Lpu_id = :Lpu_did
			  and CardType.CardType_Code between 826 and 831
			  {$filters}
			order by MPClass.MedProductClass_Name
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @param $data
	 * @return array|false
	 */
	function getIdForEmk($data)
	{
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select MO.MorbusPalliat_id as \"MorbusPalliat_id\"
			from
				v_MorbusPalliat MO
			    inner join v_Morbus M on M.Morbus_id = MO.Morbus_id
			where M.Person_id = :Person_id
			order by MO.MorbusPalliat_id desc
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Проверка наличия протокола ВК и загрузка данных из него
	 * @param $data
	 * @return array|bool
	 */
	function checkEvnVk($data)
	{
		$selectString = "
			EVK.EvnVK_id as \"EvnVK_id\",
			to_char(EVK.EvnVK_setDT, '{$this->dateTimeForm120}') as \"MorbusPalliat_VKDate\",
			PEVK.PalliatEvnVK_id as \"PalliatEvnVK_id\",
			PEVK.PalliativeType_id as \"PalliativeType_id\",
			PEVK.PalliatEvnVK_TextTIR as \"MorbusPalliat_TextTIR\",
			PEVKD.PalliatEvnVKMainSyndrome as \"MainSyndrome\",
			PEVKD.PalliatEvnVKTechnicInstrumRehab as \"TechnicInstrumRehab\"
		";
		$fromString = "
			v_EvnVK EVK
			inner join PalliatEvnVK PEVK on PEVK.EvnVK_id = EVK.EvnVK_id
			left join lateral (
				select (
					select
						string_agg(cast(MainSyndrome_id as varchar), ',')
					from PalliatEvnVKMainSyndromeLink
					where PalliatEvnVK_id = PEVK.PalliatEvnVK_id
				    limit 1
				) as PalliatEvnVKMainSyndrome,
				(
					select
						string_agg(cast(TechnicInstrumRehab_id as varchar), ',')
					from PalliatEvnVKTechnicInstrumRehabLink
					where PalliatEvnVK_id = PEVK.PalliatEvnVK_id
				    limit 1
				) as PalliatEvnVKTechnicInstrumRehab
			) as PEVKD on true
		";
		$whereString = "
				EVK.Person_id = :Person_id
			and EVK.Diag_id = :Diag_id
			and EVK.CauseTreatmentType_id = 21
		";
		$orderByString = "
			EvnVK_setDate desc
		";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			order by {$orderByString}
			limit 1
		";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"Diag_id" => $data["Diag_id"]
		];
		$vk_data = $this->getFirstRowFromQuery($sql, $sqlParams);
		if ($vk_data === false) {
			return false;
		}
		$sql = "
			select
				FamilyRelationType_id as \"FamilyRelationType_id\",
			    PalliatFamilyCare_Age as \"PalliatFamilyCare_Age\",
			    PalliatFamilyCare_Phone as \"PalliatFamilyCare_Phone\"
			from PalliatFamilyCare
			where EvnVK_id = :EvnVK_id
			  and FamilyRelationType_id is not null
			limit 1
		";
		$sqlParams = ["EvnVK_id" => $vk_data["EvnVK_id"]];
		$vk_data["PalliatFamilyCareList"] = $this->queryResult($sql, $sqlParams);
		$vk_data["MorbusPalliat_IsFamCare"] = count($vk_data["PalliatFamilyCareList"]) ? 2 : 1;
		$vk_data["MorbusPalliat_IsTIR"] = !empty($vk_data["TechnicInstrumRehab"]) ? 2 : 1;
		$vk_data["TechnicInstrumRehab"] = empty($vk_data["TechnicInstrumRehab"]) ? [] : explode(",", $vk_data["TechnicInstrumRehab"]);
		foreach ($vk_data["TechnicInstrumRehab"] as &$row) {
			$row = ["id" => $row, "date" => null];
		}
		$vk_data["TechnicInstrumRehab"] = json_encode($vk_data["TechnicInstrumRehab"]);
		return $vk_data;
	}
    /**
     * @param array $data
     * @return array|false
     */
    function checkCanInclude($data) {

        $query = "
			select
				ps.Person_id as \"Person_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				ps.Person_Firname as \"Person_Firname\",
				ps.Person_Secname as \"Person_Secname\",
				ps.Person_Surname as \"Person_Surname\",
			    to_char(ps.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\"
			from v_PersonState ps
				left join v_PersonRegisterType prt on prt.PersonRegisterType_SysNick = 'palliat'
				left join v_PersonRegister pr on
					pr.Person_id = ps.Person_id and
					pr.PersonRegisterType_id = prt.PersonRegisterType_id and
					pr.PersonRegister_disDate is null
			where
				ps.Person_id = :Person_id and
				pr.PersonRegister_id is null
		";

        return $this->queryResult($query, $data);
    }
    
	/**
	 * @param $data
	 * @return array|false
	 */
	function getDirectionMSE($data) {
		$params = array(
			'Person_id' => $data['Person_id']
		);
		$query = "
		select
	        EvnPrescrMse.EvnPrescrMse_id as \"EvnPrescrMse_id\",
	        EvnPrescrMse.Person_id as \"Person_id\",
	        EvnPrescrMse.Server_id as \"Server_id\",
	        EvnPrescrMse.EvnVK_id as \"EvnVK_id\",
	        EM.EvnMse_id as \"EvnMse_id\",
	        '№ ' || cast(EM.EvnMse_NumAct as varchar(10)) || ' от ' || to_char(EM.EvnMse_setDT, 'DD.MM.YYYY') as \"EvnMse\",
	        'Направление на МСЭ' as \"EvnClass_Name\",
	        coalesce(to_char(EvnPrescrMse.EvnPrescrMse_issueDT, 'DD.MM.YYYY'),'') as \"date_beg\"
		from
			v_EvnPrescrMse EvnPrescrMse
	        left join v_EvnMse EM on EvnPrescrMse.EvnPrescrMse_id = EM.EvnPrescrMse_id
	        left join v_EvnQueue eq on eq.EvnQueue_id = EvnPrescrMse.EvnQueue_id
			left join lateral (
				select MDAT.MseDirectionAimType_Code as MseDirectionAimType_Code
				from v_MseDirectionAimTypeLink MDATL
				left join v_MseDirectionAimType MDAT on MDATL.MseDirectionAimType_id = MDAT.MseDirectionAimType_id
				where (1=1)
				and MDATL.EvnPrescrMse_id = EvnPrescrMse.EvnPrescrMse_id
				and MDAT.MseDirectionAimType_Code = 1 -- цель направления Установление группы инвалидности
				limit 1
			) as M on true
		where (1=1)
	          and EvnPrescrMse.Person_id = :Person_id
	          and (eq.EvnQueue_id is null or eq.EvnQueue_failDT is null)
	          and coalesce(EvnPrescrMse.EvnPrescrMse_IsArchive, 1) = 1 -- только актуальные
	          and M.MseDirectionAimType_Code is not null
		order by EvnPrescrMse.EvnPrescrMse_issueDT desc
		limit 1";

		return $this->queryResult($query, $params);
	}

}