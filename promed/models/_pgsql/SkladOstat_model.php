<?php

/**
 * Class SkladOstat_model
 *
 * @property CI_DB_driver $db
 */
class SkladOstat_model extends swPgModel
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function run($data)
	{
		$DBF = $this->DBFtoArray($data);
		switch ($data["typeAction"]) {
			case "SkladOst":
				$query = "delete from raw.SkladOstat where 1 = 1";
				//Перед каждой загрузкой очищаем таблицу.
				$this->db->query($query);
				foreach ($DBF as $val) {
					$this->importSkladOst($val);
				}
				break;
			case "LpuSectionOTD":
				foreach ($DBF as $val) {
					$this->importMed($val);
				}
				break;
		}
		return ["success" => true];
	}

	/**
	 * @param $val
	 * @return array|bool
	 */
	function importMed($val)
	{
		$queryParams = [
			"DOC_RN" => $val["DOC_RN"],
			"Lpu_id" => $val["Lpu_id"],
			"pmUser_id" => $val["pmUser_id"],
			"SKLAD" => $val["SKLAD"],
			"GDMD_RN" => $val["GDMD_RN"],
			"KOL" => $val["KOL"],
			"SKLAD_RN" => $val["SKLAD_RN"],
			"OTD_RN" => $val["OTD_RN"],
			"DOC_DATE" => $val["DOC_DATE"]
		];
		/**@var CI_DB_result $result */
		$query = "
			select Contragent_id as \"Contragent_id\"
			from v_contragent
			where contragent_name = :SKLAD||'-'||:SKLAD_RN::varchar
			limit 1
		";
		$result = $this->queryResult($query, $queryParams);
		if (!is_array($result)) {
			return false;
		}
		$resultRow = $result[0];
		if ($resultRow["Contragent_id"] == null) {
			$query = "
				select
				    contragent_id as \"Contragent_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_contragent_ins(
				    server_id := :Lpu_id,
				    lpu_id := :Lpu_id,
				    contragenttype_id := 1,
				    contragent_code := 1001,
				    contragent_name := :SKLAD||'-'||:SKLAD_RN::varchar,
				    org_id := null,
				    orgfarmacy_id := null,
				    lpusection_id := null,
				    pmuser_id := :pmUser_id
				);
			";
			$result1 = $this->queryResult($query, $queryParams);
			if (!is_array($result1)) {
				return false;
			}
			$result1Row = $result1[0];
			$queryParams["Contragent_id"] = $result1Row["Contragent_id"];
		} else {
			$queryParams["Contragent_id"] = $resultRow["Contragent_id"];
		}
		$query = "
			select DocumentUc_id as \"DocumentUc_id\"
			from DocumentUc
			where DocumentUc_setDate = :DOC_DATE::date
			  and Contragent_sid = :Contragent_id
			  and Lpu_id = :Lpu_id
			  and DrugFinance_id = 1
			  and WhsDocumentCostItemType_id = 14
			limit 1
		";
		$result2 = $this->queryResult($query, $queryParams);
		if (!is_array($result2)) {
			return false;
		}
		$result2Row = $result2[0];
		if ($result2Row["DocumentUc_id"] == null) {
			$query = "
				select
				    documentuc_id as \"DocumentUc_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_documentuc_ins(
				    documentuc_num := 'Склад/'||:SKLAD,
				    documentuc_setdate := :DOC_DATE::date,
				    documentuc_diddate := :DOC_DATE::date,
				    documentuc_dognum := null,
				    documentuc_dogdate := null,
				    documentuc_invnum := null,
				    documentuc_invdate := null,
				    documentuc_sum := null,
				    documentuc_sumr := null,
			    	documentuc_sumnds := null,
				    documentuc_sumndsr := null,
				    lpu_id := :Lpu_id,
				    contragent_id := :Contragent_id,
				    contragent_sid := :Contragent_id,
				    mol_sid := null,
				    contragent_tid := (
						select Contragent_id
						from
							Contragent c
							left join LpuSectionOTD lp on lp.LpuSection_id=c.LpuSection_id 
						where lp.LpuSectionOTD_OTDRN=:OTD_RN
						limit 1
				 	),
				    mol_tid := (
						select mol_id
						from v_mol
						where Contragent_id = (
							select Contragent_id
							from
								Contragent c
								left join LpuSectionOTD lp on lp.LpuSection_id=c.LpuSection_id 
							where lp.LpuSectionOTD_OTDRN=:OTD_RN
							limit 1)
						limit 1
				 	),
				    drugfinance_id := 1,
				    drugdocumenttype_id := 1,
				    drugdocumentstatus_id := null,
				    whsdocumentcostitemtype_id := 14,
				    pmuser_id := :pmUser_id
				);
			";
			$result3 = $this->queryResult($query, $queryParams);
			if (!is_array($result3)) {
				return false;
			}
			$result3Row = $result3[0];
			$queryParams["DocUc_id"] = $result3Row["DocumentUc_id"];
			$query = "
				select
				    documentucstr_id as \"DocumentUcStr_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_documentucstr_ins(
				    documentuc_id := :DocUc_id,
				    drug_id := (
						select d.drug_id
						from
							rls.v_Drug d
							inner join drugLink dl on dl.drug_id=d.Drug_id
						where dl.DrugLink_RN = :GDMD_RN
						limit 1
				    ),
				    drugfinance_id := 1,
				    drugnds_id := null,
				    drugproducer_id := null,
				    documentucstr_price := null,
				    documentucstr_pricer := 0,
				    documentucstr_count := :KOL,
				    documentucstr_sum := null,
				    documentucstr_sumnds := null,
				    documentucstr_sumndsr := null,
				    documentucstr_ser := null,
				    documentucstr_certnum := null,
				    documentucstr_certdate := null,
				    documentucstr_certgodndate := null,
				    documentucstr_certorg := null,
				    documentucstr_islab := null,
				    druglabresult_name := null,
					documentucstr_rashcount := null,
				    documentucstr_regdate := null,
				    documentucstr_regprice := null,
				    documentucstr_godndate := null,
				    documentucstr_setdate := :DOC_DATE::date,
				    documentucstr_decl := null,
				    documentucstr_barcod := null,
				    documentucstr_certnm := null,
				    documentucstr_certdm := null,
				    documentucstr_ntu := null,
				    documentucstr_nzu := null,
				    documentucstr_reason := null,
				    evnrecept_id := null,
				    pmuser_id := :pmUser_id
				);
			";
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			return $result->result("array");
		}
		return true;
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function DBFtoArray($data)
	{
		$fields_mapping = [];
		if (!is_file($data["file"])) {
			throw new Exception($data["file"] . " не найден");
		}
		$dbf = dbase_open($data["file"], 0);
		if ($dbf === false) {
			throw new Exception("Не удалось открыть файл " . $data["file"]);
		}
		$dbf_header = dbase_get_header_info($dbf);
		if ($dbf_header === false) {
			throw new Exception("Информация в заголовке базы данных " . $data["file"] . " не может быть прочитана");
		}
		$ddl = [];
		$conv = [];
		$fields_mapping_empty = (0 == count($fields_mapping)) ? true : false;
		foreach ($dbf_header as $dbf_field) {
			switch ($dbf_field["type"]) {
				case "character":
					$dbf_field["type"] = "VARCHAR(" . $dbf_field["length"] . ")";
					$conv[] = $dbf_field["name"];
					break;
				default:
					$dbf_field["type"] = "VARCHAR(4000)";
					break;
			}
			$ddl[] = "[" . $dbf_field["name"] . "] " . $dbf_field["type"];
			if ($fields_mapping_empty) {
				$fields_mapping[$dbf_field["name"]] = $dbf_field["name"];
			}
		}
		$cnt = dbase_numrecords($dbf);
		$values = [];
		for ($i = 1; $i <= $cnt; $i++) {
			$row = dbase_get_record_with_names($dbf, $i);
			foreach ($fields_mapping as $source_field => $destination_field) {
				$values[$i][$source_field] = $row[$source_field];
			}
			$values[$i]["pmUser_id"] = $data["pmUser_id"];
			$values[$i]["Lpu_id"] = $data["Lpu_id"];
		}
		dbase_close($dbf);
		return $values;
	}

	/**
	 * @param $value
	 * @throws Exception
	 */
	function importSkladOst($value)
	{
		$query = "
			select
			    skladostat_id as \"id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from raw.p_skladostat_ins(
			    skladostat_id := null,
			    skladostat_sklad := :SKLAD,
			    skladostat_skladrn := :SKLAD_RN,
			    skladostat_gdmd := :GDMD,
			    skladostat_gdmdrn := :GDMD_RN,
			    skladostat_rls := :RLS,
			    skladostat_mea := :MEA,
			    skladostat_kol := :KOL,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"SKLAD" => $value["STORE"],
			"SKLAD_RN" => $value["SKLAD_RN"],
			"GDMD" => $value["GDMD"],
			"GDMD_RN" => $value["GDMD_RN"],
			"RLS" => $value["RLS"],
			"MEA" => $value["MEA"],
			"KOL" => $value["KOL"],
			"pmUser_id" => $value["pmUser_id"],
		];
		$result = $this->getFirstRowFromQuery($query, $queryParams);
		if (empty($result["id"]) || !empty($result["Error_Message"])) {
			throw new Exception("Не удалось сохранить запись в таблицу." . getDebugSQL($query, $queryParams));
		}
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadOstatGrid($data)
	{
		$filterArray = [];
		$queryParams = [];
		if (!empty($data["SkladOstat_Sklad"])) {
			$filterArray[] = "SkladOstat_Sklad like :SkladOstat_Sklad";
			$queryParams["SkladOstat_Sklad"] = $data["SkladOstat_Sklad"] . "%";
		}
		if (!empty($data["SkladOstat_Gdmd"])) {
			$filterArray[] = "SkladOstat_Gdmd like :SkladOstat_Gdmd";
			$queryParams["SkladOstat_Gdmd"] = $data["SkladOstat_Gdmd"] . "%";
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$query = "
			select
				SkladOstat_Sklad as \"SkladOstat_Sklad\",
				SkladOstat_SkladRn as \"SkladOstat_SkladRn\",
				SkladOstat_Gdmd as \"SkladOstat_Gdmd\",
				SkladOstat_GdmdRn as \"SkladOstat_GdmdRn\",
				rls.DrugLink_Name as \"SkladOstat_Rls\",
				SkladOstat_Mea as \"SkladOstat_Mea\",
				SkladOstat_Kol as \"SkladOstat_Kol\"
			from
				raw.SkladOstat
				left join lateral (
					select DrugLink_Name 
					from  drugLink
					where DrugLink_RN = SkladOstat_GdmdRn
					limit 1
				) as rls on true
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}