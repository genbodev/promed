<?php
defined("BASEPATH") or die ("No direct script access allowed");

/**
 * Class DrugNonpropNames_model
 *
 * @property CI_DB_driver $db
 */
class DrugNonpropNames_model extends swPgModel
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает данные для постраничного вывода
	 * @param $q
	 * @param $p
	 * @return array|bool
	 */
	function returnPagingData($q, &$p)
	{
		$get_count_result = $this->db->query(getCountSQLPH($q), $p);
		if (!is_object($get_count_result)) {
			return false;
		}
		$get_count_result = $get_count_result->result("array");
		/**@var CI_DB_result $result */
		$result = $this->db->query(getLimitSQLPH($q, $p["start"], $p["limit"]), $p);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return [
			"data" => $result,
			"totalCount" => $get_count_result[0]["cnt"]
		];
	}

	/**
	 * Получение списка непатентованных наименований
	 * @param $data
	 * @return array|bool|false
	 */
	function loadDrugNonpropNamesList($data)
	{
		$filterArray = [];
		$params = [];
		if (!empty($data["query"])) {
			$filterArray[] = "DNN.DrugNonpropNames_Nick ilike :query";
			$params["query"] = "%" . $data["query"] . "%";
		}
		if (!empty($data["DrugNonpropNames_Nick"])) {
			$filterArray[] = "DNN.DrugNonpropNames_Nick ilike :DrugNonpropNames_Nick";
			$params["DrugNonpropNames_Nick"] = "%" . $data["DrugNonpropNames_Nick"] . "%";
		}
		if (!empty($data["DrugNonpropNames_Code"])) {
			$filterArray[] = "DNN.DrugNonpropNames_Code = :DrugNonpropNames_Code";
			$params["DrugNonpropNames_Code"] = $data["DrugNonpropNames_Code"];
		}
		if (!empty($data["DrugNonpropNames_Property"])) {
			$filterArray[] = "DNN.DrugNonpropNames_Property ilike :DrugNonpropNames_Property";
			$params["DrugNonpropNames_Property"] = "%" . $data["DrugNonpropNames_Property"] . "%";
		}
		if (!empty($data["RlsActmatters_id"])) {
			$filterArray[] = "
				exists (
					select *
					from
						rls.PREP_ACTMATTERS PA
						inner join rls.v_Prep Prep on Prep.Prep_id = PA.PREPID
					where PA.MATTERID = :RlsActmatters_id
					  and Prep.DrugNonpropNames_id = DNN.DrugNonpropNames_id
				)
			";
			$params["RlsActmatters_id"] = $data["RlsActmatters_id"];
		}
		$whereString = (count($filterArray) != 0) ? "where  
                                                    -- where 
                                                    " . implode(" and ", $filterArray) . 
                                                    " 
                                                    -- end where
                                                    " : "";
		$query = "
			select
            -- select
				DNN.DrugNonpropNames_id as \"DrugNonpropNames_id\",
				DNN.DrugNonpropNames_Name as \"DrugNonpropNames_Name\",
				DNN.DrugNonpropNames_Nick as \"DrugNonpropNames_Nick\",
				DNN.DrugNonpropNames_Code as \"DrugNonpropNames_Code\",
				DNN.DrugNonpropNames_Property as \"DrugNonpropNames_Property\"
            -- end select
			from
            -- from
                rls.v_DrugNonpropNames DNN
            -- end from
			    {$whereString}
			order by
            -- order by
                DNN.DrugNonpropNames_Name
            -- end order by
		";
		$params["start"] = $data["start"];
		$params["limit"] = $data["limit"];
		if (!empty($data["forCombo"])) {
			return $this->queryResult($query, $params);
		}
		return $this->returnPagingData($query, $params);
	}

	/**
	 * Сохранение непатентованного наименования
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	function saveDrugNonpropNames($data)
	{
		$procedure = (empty($data["DrugNonpropNames_id"])) ? "rls.p_DrugNonpropNames_ins" : "rls.p_DrugNonpropNames_upd";
		$params = [
			"DrugNonpropNames_id" => (!empty($data["DrugNonpropNames_id"]) ? $data["DrugNonpropNames_id"] : null),
			"DrugNonpropNames_Name" => (!empty($data["DrugNonpropNames_Name"]) ? $data["DrugNonpropNames_Name"] : null),
			"DrugNonpropNames_Nick" => (!empty($data["DrugNonpropNames_Nick"]) ? $data["DrugNonpropNames_Nick"] : null),
			"DrugNonpropNames_Code" => (!empty($data["DrugNonpropNames_Code"]) ? $data["DrugNonpropNames_Code"] : null),
			"DrugNonpropNames_Property" => (!empty($data["DrugNonpropNames_Property"]) ? $data["DrugNonpropNames_Property"] : null),
			"pmUser_id" => (!empty($data["pmUser_id"]) ? $data["pmUser_id"] : 1)
		];
		return $this->execCommonSP($procedure, $params);
	}

	/**
	 * Удаление непатентованного наименования
	 * @param $data
	 * @return array|mixed
	 * @throws Exception
	 */
	function deleteDrugNonpropNames($data)
	{
		if (empty($data['id'])) {
			throw new Exception("Не указан идентификатор Непатентованного наименования");
		}
		$params = [
			"DrugNonpropNames_id" => (!empty($data["id"]) ? $data["id"] : null)
		];
		return $this->execCommonSP("rls.p_DrugNonpropNames_del", $params);
	}

	/**
	 * Проверка связанных значений для непатентованого наименования
	 * @param $data
	 * @return array|false
	 */
	function checkDrugNonpropNames($data)
	{

		$query = "
			select count(1) as \"Count\"
			from rls.v_Prep prep
			where prep.DrugNonpropNames_id = :DrugNonpropNames_id
		";
		$res = $this->queryResult($query, $data);
		if (count($res) == 1 && $res[0]["Count"] > 0) {
			$query = "
				select table_description as \"tbl_desc\"
				from v_columns 
				where table_name like 'prep'
			";
			return $this->queryResult($query);
		}
		return [[]];
	}
}