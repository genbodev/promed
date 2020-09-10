<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * DrugDocument_model - модель для работы со справочниками для документов по медикаментам
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Farmacy
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			26.12.2013
 *
 * @property CI_DB_driver $db
 */
class DrugDocument_model extends swPgModel
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение списка видов заявки на медикаменты
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugDocumentClassList($data)
	{
		$query = "
			select
				DDC.DrugDocumentClass_id as \"DrugDocumentClass_id\",
				DDC.DrugDocumentClass_Code as \"DrugDocumentClass_Code\",
				DDC.DrugDocumentClass_Name as \"DrugDocumentClass_Name\",
				DDC.DrugDocumentClass_Nick as \"DrugDocumentClass_Nick\"
			from v_DrugDocumentClass DDC
			order by DDC.DrugDocumentClass_Code
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка статусов заявки на медикаменты
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugDocumentStatusList($data)
	{
		$params = [];
		$filter = "";
		if (!empty($data["DrugDocumentType_id"])) {
			$params["DrugDocumentType_id"] = $data["DrugDocumentType_id"];
			$filter = "where DDS.DrugDocumentType_id = :DrugDocumentType_id";
		}
		$query = "
			select
				DDS.DrugDocumentStatus_id as \"DrugDocumentStatus_id\",
				DDS.DrugDocumentStatus_Code as \"DrugDocumentStatus_Code\",
				DDS.DrugDocumentStatus_Name as \"DrugDocumentStatus_Name\",
				DDS.DrugDocumentType_id as \"DrugDocumentType_id\"
			from v_DrugDocumentStatus DDS
			{$filter}
			order by DDS.DrugDocumentStatus_Code
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка видов заявки на медикаменты
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugDocumentClassGrid($data)
	{
		$query = "
			select
				DDC.DrugDocumentClass_id as \"DrugDocumentClass_id\",
				DDC.DrugDocumentClass_Code as \"DrugDocumentClass_Code\",
				DDC.DrugDocumentClass_Name as \"DrugDocumentClass_Name\",
				DDC.DrugDocumentClass_Nick as \"DrugDocumentClass_Nick\"
			from v_DrugDocumentClass DDC
			order by DDC.DrugDocumentClass_Code
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return ["data" => $result->result("array")];
	}

	/**
	 * Получение списка статусов заявки на медикаменты
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugDocumentStatusGrid($data)
	{
		$params = [];
		$filter = "";
		if (!empty($data["DrugDocumentType_id"])) {
			$filter = "where DDS.DrugDocumentType_id = :DrugDocumentType_id";
			$params["DrugDocumentType_id"] = $data["DrugDocumentType_id"];
		}
		$query = "
			select
				DDS.DrugDocumentStatus_id as \"DrugDocumentStatus_id\",
				DDS.DrugDocumentStatus_Code as \"DrugDocumentStatus_Code\",
				DDS.DrugDocumentStatus_Name as \"DrugDocumentStatus_Name\",
				DDS.DrugDocumentType_id as \"DrugDocumentType_id\"
			from v_DrugDocumentStatus DDS
			{$filter}
			order by DDS.DrugDocumentStatus_Code
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return ["data" => $result->result("array")];
	}

	/**
	 * Возвращает данные для редактирования вида заявки на медикаменты
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugDocumentClassForm($data)
	{
		$params = ["DrugDocumentClass_id" => $data["DrugDocumentClass_id"]];
		$query = "
			select
				DDC.DrugDocumentClass_id as \"DrugDocumentClass_id\",
				DDC.DrugDocumentClass_Code as \"DrugDocumentClass_Code\",
				DDC.DrugDocumentClass_Name as \"DrugDocumentClass_Name\",
				DDC.DrugDocumentClass_Nick as \"DrugDocumentClass_Nick\"
			from v_DrugDocumentClass DDC
			where DDC.DrugDocumentClass_id = :DrugDocumentClass_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Возвращает данные для редактирования статуса заявки на медикаменты
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugDocumentStatusForm($data)
	{
		$params = ["DrugDocumentStatus_id" => $data["DrugDocumentStatus_id"]];
		$query = "
			select
				DDS.DrugDocumentStatus_id as \"DrugDocumentStatus_id\",
				DDS.DrugDocumentStatus_Code as \"DrugDocumentStatus_Code\",
				DDS.DrugDocumentStatus_Name as \"DrugDocumentStatus_Name\"
			from v_DrugDocumentStatus DDS
			where DDS.DrugDocumentStatus_id = :DrugDocumentStatus_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение вида заявки на медикаменты
	 * @param $data
	 * @return array|bool
	 */
	function saveDrugDocumentClass($data)
	{
		$procedure = (empty($data["DrugDocumentClass_id"])) ? "p_DrugDocumentClass_ins" : "p_DrugDocumentClass_upd";
		$params = [
			"DrugDocumentClass_id" => $data["DrugDocumentClass_id"],
			"DrugDocumentClass_Code" => $data["DrugDocumentClass_Code"],
			"DrugDocumentClass_Name" => $data["DrugDocumentClass_Name"],
			"DrugDocumentClass_Nick" => $data["DrugDocumentClass_Nick"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$selectString = "
		    drugdocumentclass_id as \"DrugDocumentClass_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    drugdocumentclass_id := :DrugDocumentClass_id,
			    drugdocumentclass_code := :DrugDocumentClass_Code,
			    drugdocumentclass_name := :DrugDocumentClass_Name,
			    drugdocumentclass_nick := :DrugDocumentClass_Nick,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение статуса заявки на медикаменты
	 * @param $data
	 * @return array|bool
	 */
	function saveDrugDocumentStatus($data)
	{
		$procedure = (empty($data["DrugDocumentStatus_id"])) ? "p_DrugDocumentStatus_ins" : "p_DrugDocumentStatus_upd";
		$params = [
			"DrugDocumentStatus_id" => $data["DrugDocumentStatus_id"],
			"DrugDocumentStatus_Code" => $data["DrugDocumentStatus_Code"],
			"DrugDocumentStatus_Name" => $data["DrugDocumentStatus_Name"],
			"DrugDocumentType_id" => $data["DrugDocumentType_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$selectString = "
		    drugdocumentstatus_id as \"DrugDocumentStatus_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    drugdocumentstatus_id := :DrugDocumentStatus_id,
			    drugdocumentstatus_code := :DrugDocumentStatus_Code,
			    drugdocumentstatus_name := :DrugDocumentStatus_Name,
			    drugdocumenttype_id := :DrugDocumentType_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Удаление вида заявки на медикаменты
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteDrugDocumentClass($data)
	{
		$params = ["DrugDocumentClass_id" => $data["DrugDocumentClass_id"]];
		$query = "
			select count(DocumentUc_id) as \"Count\"
			from v_DocumentUc DU
			where DU.DrugDocumentClass_id = :DrugDocumentClass_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			throw new Exception("Ошибка при проверке вида заявок на медикаменты");
		}
		$resp_arr = $result->result("array");
		if ($resp_arr[0]["Count"] > 0) {
			throw new Exception("Удаление невозможено! Существуют заявки на медикаменты данного вида!");
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_drugdocumentclass_del(drugdocumentclass_id := :DrugDocumentClass_id);
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Удаление статуса заявки на медикаменты
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteDrugDocumentStatus($data)
	{
		$params = ["DrugDocumentStatus_id" => $data["DrugDocumentStatus_id"]];
		$query = "
			select count(DocumentUc_id) as \"Count\"
			from v_DocumentUc DU
			where DU.DrugDocumentStatus_id = :DrugDocumentStatus_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			throw new Exception("Ошибка при проверке использования статусов");
		}
		$resp_arr = $result->result("array");
		if ($resp_arr[0]["Count"] > 0) {
			throw new Exception("Удаление невозможено! Существуют заявки на медикаменты с данным статусом!");
		}

		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_drugdocumentstatus_del(drugdocumentstatus_id := :DrugDocumentStatus_id);
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}
