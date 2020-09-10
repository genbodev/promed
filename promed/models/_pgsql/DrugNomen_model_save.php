<?php


class DrugNomen_model_save
{
	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return bool|CI_DB_result
	 */
	public static function saveActmatters_LatName(DrugNomen_model $callObject, $data)
	{
		$query = "
			update rls.actmatters
			set
				ACTMATTERS_LatNameGen = :Actmatters_LatName,
				pmUser_updID = :pmUser_id
			where ACTMATTERS_ID = :Actmatters_id
			returning null as \"Error_Code\", null as \"Error_Msg\" 
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!$result) {
			return false;
		}
		return $result;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return bool|CI_DB_result
	 */
	public static function saveDrugComplexMnn_LatName(DrugNomen_model $callObject, $data)
	{
		$query = "
			update rls.DrugComplexMnn
			set
				DrugComplexMnn_LatName = :DrugComplexMnn_LatName,
				pmUser_updID = :pmUser_id
			where DrugComplexMnn_id = :DrugComplexMnn_id
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!$result) {
			return false;
		}
		return $result;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return bool|CI_DB_result
	 */
	public static function saveDrugTorg_NameLatin(DrugNomen_model $callObject, $data)
	{
		$query = "
			update rls.DrugPrep
			set
				DrugTorg_NameLatin = :DrugTorg_NameLatin,
				pmUser_updID = :pmUser_id
			where DrugPrepFas_id = :DrugPrepFas_id
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!$result) {
			return false;
		}
		return $result;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveTradenames_LatName(DrugNomen_model $callObject, $data)
	{
		$query = "
			select
				LATINNAMES_ID as \"LATINNAMES_ID\",
				NAME as \"NAME\"
			from rls.v_latinnames
			where LATINNAMES_ID = :Tradenames_LatName_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		$query = "
			select
			    latinnames_id as \"LATINNAMES_ID\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from rls.p_latinnames_upd(
			    latinnames_id := :LATINNAMES_ID,
			    name := :NAME,
			    latinnames_namegen := :LATINNAMES_NameGen,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LATINNAMES_ID" => $res[0]["LATINNAMES_ID"],
			"NAME" => $res[0]["NAME"],
			"LATINNAMES_NameGen" => $data["Tradenames_LatName"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveClsdrugforms_LatName(DrugNomen_model $callObject, $data)
	{
		$query = "
			select
				CLSDRUGFORMS_ID as \"CLSDRUGFORMS_ID\",
				PARENTID as \"PARENTID\",
				NAME as \"NAME\",
				FULLNAME as \"FULLNAME\"
			from rls.v_clsdrugforms
			where CLSDRUGFORMS_ID = :Clsdrugforms_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		$query = "
			select
			    clsdrugforms_id as \"Clsdrugforms_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from rls.p_clsdrugforms_upd(
			    clsdrugforms_id := :CLSDRUGFORMS_ID,
			    parentid := :PARENTID,
			    name := :NAME,
			    fullname := :FULLNAME,
			    clsdrugforms_namelatin := :CLSDRUGFORMS_NameLatin,
			    clsdrugforms_namelatinsocr := :CLSDRUGFORMS_NameLatinSocr,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"CLSDRUGFORMS_ID" => $res[0]["CLSDRUGFORMS_ID"],
			"PARENTID" => $res[0]["PARENTID"],
			"NAME" => $res[0]["NAME"],
			"FULLNAME" => $res[0]["FULLNAME"],
			"CLSDRUGFORMS_NameLatin" => $data["Clsdrugforms_LatName"],
			"CLSDRUGFORMS_NameLatinSocr" => $data["Clsdrugforms_LatNameSocr"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveUnit_LatName(DrugNomen_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		$key = strtoupper($data["Unit_table"]);
		$keys = [
			"MASSUNITS",
			"SIZEUNITS",
			"CONCENUNITS",
			"ACTUNITS",
		];
		if (!in_array($key, $keys)) {
			return false;
		}
		if ($key == "MASSUNITS") {
			$query = "
				select
					MASSUNITS_ID as \"MASSUNITS_ID\",
					FULLNAME as \"FULLNAME\",
					SHORTNAME as \"SHORTNAME\",
					DrugEdMass_id as \"DrugEdMass_id\"
				from rls.v_MassUnits
				where MASSUNITS_ID = :Unit_id
			";
			$result = $callObject->db->query($query, $data);
			if (!is_object($result)) {
				return false;
			}
			$res = $result->result("array");
			$query = "
				select
				    massunits_id as \"MASSUNITS_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from rls.p_massunits_upd(
				    massunits_id := :MASSUNITS_ID,
				    fullname := :FULLNAME,
				    shortname := :SHORTNAME,
				    drugedmass_id := :DrugEdMass_id,
				    massunits_namelatin := :MassUnits_NameLatin,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"MASSUNITS_ID" => $res[0]["MASSUNITS_ID"],
				"FULLNAME" => $res[0]["FULLNAME"],
				"SHORTNAME" => $res[0]["SHORTNAME"],
				"DrugEdMass_id" => $res[0]["DrugEdMass_id"],
				"MassUnits_NameLatin" => $data["Unit_LatName"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			return $result->result("array");
		} elseif ($key == "SIZEUNITS") {
			$query = "
				select
					SIZEUNITS_ID as \"SIZEUNITS_ID\",
					FULLNAME as \"FULLNAME\",
					SHORTNAME as \"SHORTNAME\",
					SHORTNAMELATIN as \"SHORTNAMELATIN\"
				from rls.v_sizeunits
				where SIZEUNITS_ID = :Unit_id
			";
			$result = $callObject->db->query($query, $data);
			if (!is_object($result)) {
				return false;
			}
			$res = $result->result("array");
			$query = "
				select
				    sizeunits_id as \"SIZEUNITS_ID\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from rls.p_sizeunits_upd(
				    sizeunits_id := :SIZEUNITS_ID,
				    fullname := :FULLNAME,
				    shortname := :SHORTNAME,
				    fullnamelatin := :FULLNAMELATIN,
				    shortnamelatin := :SHORTNAMELATIN,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"SIZEUNITS_ID" => $res[0]["SIZEUNITS_ID"],
				"FULLNAME" => $res[0]["FULLNAME"],
				"SHORTNAME" => $res[0]["SHORTNAME"],
				"FULLNAMELATIN" => $data["Unit_LatName"],
				"SHORTNAMELATIN" => $res[0]["SHORTNAMELATIN"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			return $result->result("array");
		} elseif ($key == "CONCENUNITS") {
			$query = "
				select concenunits_id as \"CONCENUNITS_ID\",
                fullname as \"FULLNAME\",
                shortname as \"SHORTNAME\",
                drugedvol_id as \"DrugEdVol_id\"
				from rls.v_CONCENUNITS
				where CONCENUNITS_ID = :Unit_id
			";
			$result = $callObject->db->query($query, $data);
			if (!is_object($result)) {
				return false;
			}
			$res = $result->result("array");
			$query = "
				select
				    concenunits_id as \"CONCENUNITS_ID\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from rls.p_concenunits_upd(
				    concenunits_id := :CONCENUNITS_ID,
				    fullname := :FULLNAME,
				    shortname := :SHORTNAME,
				    drugedvol_id := :DrugEdVol_id,
				    concenunits_namelatin := :CONCENUNITS_NameLatin,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"CONCENUNITS_ID" => $res[0]["CONCENUNITS_ID"],
				"FULLNAME" => $res[0]["FULLNAME"],
				"SHORTNAME" => $res[0]["SHORTNAME"],
				"DrugEdVol_id" => $res[0]["DrugEdVol_id"],
				"CONCENUNITS_NameLatin" => $data["Unit_LatName"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			return $result->result("array");
		} elseif ($key == "ACTUNITS") {
			$query = "
				select
					ACTUNITS_ID as \"ACTUNITS_ID\",
					FULLNAME as \"FULLNAME\",
					SHORTNAME as \"SHORTNAME\"
				from rls.v_ACTUNITS
				where ACTUNITS_ID = :Unit_id
			";
			$result = $callObject->db->query($query, $data);
			if (!is_object($result)) {
				return false;
			}
			$res = $result->result("array");
			$query = "
				select
				    actunits_id as \"ACTUNITS_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from rls.p_actunits_upd(
				    actunits_id := :ACTUNITS_ID,
				    fullname := :FULLNAME,
				    shortname := :SHORTNAME,
				    actunits_namelatin := :ACTUNITS_NameLatin,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"ACTUNITS_ID" => $res[0]["ACTUNITS_ID"],
				"FULLNAME" => $res[0]["FULLNAME"],
				"SHORTNAME" => $res[0]["SHORTNAME"],
				"ACTUNITS_NameLatin" => $data["Unit_LatName"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			return $result->result("array");
		}
		return false;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function saveDrugComplexMnnCode(DrugNomen_model $callObject, $data)
	{
		$response = ["success" => false];
		if (empty($data["DrugComplexMnn_id"])) {
			$data["DrugComplexMnn_id"] = null;
		}
		$procedure = (empty($data["DrugComplexMnnCode_id"])) ? "p_DrugComplexMnnCode_ins" : "p_DrugComplexMnnCode_upd";
		$selectString = "
		    drugcomplexmnncode_id as \"DrugComplexMnnCode_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from rls.{$procedure}(
			    drugcomplexmnncode_id := :DrugComplexMnnCode_id,
			    drugcomplexmnn_id := :DrugComplexMnn_id,
			    drugcomplexmnncode_code := CAST(:DrugComplexMnnCode_Code as varchar),
				DrugComplexMnnCode_DosKurs := :DrugComplexMnnCode_DosKurs,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return $response;
		}
		$arr = $result->result("array");
		if (!is_array($arr)) {
			return $response;
		}
		return ["success" => true, "DrugComplexMnnCode_id" => $arr[0]["DrugComplexMnnCode_id"]];
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function saveDrugTorgCode(DrugNomen_model $callObject, $data)
	{
		$response = ["success" => false];
		if (empty($data["Tradenames_id"])) {
			$data["Tradenames_id"] = null;
		}
		$procedure = (empty($data["DrugTorgCode_id"])) ? "p_DrugTorgCode_ins" : "p_DrugTorgCode_upd";
		$selectString = "
		    drugtorgcode_id as \"DrugTorgCode_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from rls.{$procedure}(
			    drugtorgcode_id := :DrugTorgCode_id,
			    tradenames_id := :Tradenames_id,
			    drugtorgcode_code := CAST(:DrugTorgCode_Code as varchar),
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return $response;
		}
		$arr = $result->result("array");
		if (!is_array($arr)) {
			return $response;
		}
		return ["success" => true, "DrugTorgCode_id" => $arr[0]["DrugTorgCode_id"]];
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function saveDrugPrepFasCode(DrugNomen_model $callObject, $data)
	{
		$response = ["success" => false];
		if (empty($data["DrugPrepFas_id"])) {
			$data["DrugPrepFas_id"] = null;
		}
		//ищем существующую запись с кодом
		if (empty($data["DrugPrepFasCode_id"])) {
			$query = "
                select dpfc.DrugPrepFasCode_id as \"DrugPrepFasCode_id\"
                from rls.v_DrugPrepFasCode dpfc
                where dpfc.DrugPrepFas_id = :DrugPrepFas_id
                  and coalesce(dpfc.Org_id, 0) = coalesce(:Org_id::bigint, 0)
                order by dpfc.DrugPrepFasCode_id
				limit 1
            ";
			$queryParams = [
				"DrugPrepFas_id" => $data["DrugPrepFas_id"],
				"Org_id" => $data["Org_id"]
			];
			$result = $callObject->getFirstRowFromQuery($query, $queryParams);
			$data["DrugPrepFasCode_id"] = !empty($result["DrugPrepFasCode_id"]) ? $result["DrugPrepFasCode_id"] : null;
		}
		$procedure = (empty($data["DrugPrepFasCode_id"]))?"p_DrugPrepFasCode_ins":"p_DrugPrepFasCode_upd";
		$selectString = "
		    drugprepfascode_id as \"DrugPrepFasCode_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from rls.{$procedure}(
			    drugprepfascode_id := :DrugPrepFasCode_id,
			    drugprepfascode_code := CAST(:DrugPrepFasCode_Code as varchar),
			    drugprepfas_id := :DrugPrepFas_id,
			    org_id := :Org_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return $response;
		}
		$arr = $result->result("array");
		if (!is_array($arr)) {
			return $response;
		}
		return ["success" => true, "DrugPrepFasCode_id" => $arr[0]["DrugPrepFasCode_id"]];
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function saveDrugMnnCode(DrugNomen_model $callObject, $data)
	{
		$response = ["success" => false];
		if (empty($data["Actmatters_id"])) {
			$data["Actmatters_id"] = null;
		}
		$procedure = (empty($data["DrugMnnCode_id"]))?"p_DrugMnnCode_ins":"p_DrugMnnCode_upd";
		$selectString = "
		    drugmnncode_id as \"DrugMnnCode_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from rls.{$procedure}(
			    drugmnncode_id := :DrugMnnCode_id,
			    actmatters_id := :Actmatters_id,
			    drugmnncode_code := CAST(:DrugMnnCode_Code as varchar),
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return $response;
		}
		$arr = $result->result("array");
		if (!is_array($arr)) {
			return $response;
		}
		return ["success" => true, "DrugMnnCode_id" => $arr[0]["DrugMnnCode_id"]];
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveDrugNomen(DrugNomen_model $callObject, $data)
	{
		$procedure = (empty($data["DrugNomen_id"])) ? "p_DrugNomen_ins" : "p_DrugNomen_upd";
		$selectString = "
		    drugnomen_id as \"DrugNomen_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from rls.{$procedure}(
			    drugnomen_id := :DrugNomen_id,
			    drug_id := :Drug_id,
			    drugnomen_code := :DrugNomen_Code,
			    drugnomen_name := (select Drug_Name from rls.v_Drug where Drug_id = :Drug_id limit 1),
			    drugnomen_nick := :DrugNomen_Nick,
			    drugtorgcode_id := :DrugTorgCode_id,
			    drugmnncode_id := :DrugMnnCode_id,
			    drugcomplexmnncode_id := :DrugComplexMnnCode_id,
			    prepclass_id := :PrepClass_id,
			    okpd_id := :Okpd_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveDrugNomenOrgLink(DrugNomen_model $callObject, $data)
	{
		$query = "
			select DNOL.DrugNomenOrgLink_id as \"DrugNomenOrgLink_id\"
			from rls.v_DrugNomenOrgLink DNOL
			where
			    DNOL.DrugNomen_id = :DrugNomen_id and
			    DNOL.Org_id = :DrugNomenOrgLink_Org_id
			order by DNOL.DrugNomenOrgLink_id
			limit 1
		";
		$dnol_data = $callObject->getFirstRowFromQuery($query, $data);
		$saved_data = [
			"DrugNomenOrgLink_id" => !empty($dnol_data["DrugNomenOrgLink_id"]) ? $dnol_data["DrugNomenOrgLink_id"] : null,
			"Org_id" => $data["DrugNomenOrgLink_Org_id"],
			"DrugNomen_id" => $data["DrugNomen_id"],
			"DrugNomenOrgLink_Code" => $data["DrugNomenOrgLink_Code"],
			"pmUser_id" => $data["pmUser_id"]
		];
		if (!empty($data["DrugNomenOrgLink_Code"])) {
			$procedure = !empty($saved_data["DrugNomenOrgLink_id"]) ? "p_DrugNomenOrgLink_upd" : "p_DrugNomenOrgLink_ins";
			$selectString = "
			    drugnomenorglink_id as \"DrugNomenOrgLink_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			";
			$query = "
				select {$selectString}
				from rls.{$procedure}(
				    drugnomenorglink_id := :DrugNomenOrgLink_id,
				    org_id := :Org_id,
				    drugnomen_id := :DrugNomen_id,
				    drugnomenorglink_code := :DrugNomenOrgLink_Code,
				    pmuser_id := :pmUser_id
				);
            ";
		} else {
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from rls.p_drugnomenorglink_del(drugnomenorglink_id := :DrugNomenOrgLink_id);
            ";
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $saved_data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function saveDrugPrepEdUcCount(DrugNomen_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		if (!isset($data["DrugPrepFas_id"])) {
			$query = "
				select DrugPrepFas_id as \"DrugPrepFas_id\"
				from rls.v_Drug
				where Drug_id = :Drug_id
			";
			$result = $callObject->db->query($query, $data);
			if (!is_object($result)) {
				return ["success" => false];
			}
			$result = $result->result("array");
			$data["DrugPrepFas_id"] = $result[0]["DrugPrepFas_id"];
		}
		$data["Region_id"] = $callObject->getRegionNumber();
		if (!isset($data["DrugPrepEdUcCount_id"])) {
			$query = "
				select count(*) as cnt
				from rls.v_DrugPrepEdUcCount
				where Region_id = :Region_id
				  and DrugPrepFas_id = :DrugPrepFas_id
				  and GoodsUnit_id = :GoodsUnit_id
				  and DrugPrepEdUcCount_Count = :DrugPrepEdUcCount_Count
				  and coalesce(Org_id, 0) = coalesce(:Org_id, 0)
			";
			$result = $callObject->db->query($query, $data);
			if (!is_object($result)) {
				return ["success" => false];
			}
			$result = $result->result("array");
			if ($result[0]["cnt"] > 0) {
				return ["success" => false, "Error_Code" => 400];
			}
		}
		$procedure = (empty($data["DrugPrepEdUcCount_id"]))?"rls.p_DrugPrepEdUcCount_ins" : "rls.p_DrugPrepEdUcCount_upd";
		$selectString = "
		    drugprepeduccount_id as \"DrugPrepEdUcCount_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    drugprepeduccount_id := :DrugPrepEdUcCount_id,
			    drugprepfas_id := :DrugPrepFas_id,
			    drugprepeduccount_count := :DrugPrepEdUcCount_Count,
			    org_id := :Org_id,
			    region_id := :Region_id,
			    goodsunit_id := :GoodsUnit_id,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $callObject->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result("array");
			if (isset($result[0]) && empty($result[0]["Error_Msg"])) {
				return ["success" => true];
			}
		}
		return ["success" => false];
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveDrugRMZ(DrugNomen_model $callObject, $data)
	{
		$procedure = (empty($data["DrugRMZ_id"]))?"p_DrugRMZ_ins":"p_DrugRMZ_upd";
		if (empty($data["DrugRMZ_id"])) {
			$data["DrugRMZ_id"] = null;
		}
		$selectString = "
		    drugrmz_id as \"DrugRMZ_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from rls.{$procedure}(
			    drugrmz_id := :DrugRMZ_id,
			    drugrpn_id := :DrugRPN_id,
			    drugrmz_coderzn := :DrugRMZ_CodeRZN,
			    drugrmz_regnum := :DrugRMZ_RegNum,
			    drugrmz_country := :DrugRMZ_Country,
			    drugrmz_form := :DrugRMZ_Form,
			    drugrmz_name := :DrugRMZ_Name,
			    drugrmz_dose := :DrugRMZ_Dose,
			    drugrmz_firm := :DrugRMZ_Firm,
			    drugrmz_mnn := :DrugRMZ_MNN,
			    drugrmz_regdate := :DrugRMZ_RegDate,
			    drugrmz_cond := :DrugRMZ_Cond,
			    drugrmz_pack := :DrugRMZ_Pack,
			    drugrmz_packsize := :DrugRMZ_PackSize,
			    drugrmz_ean13code := :DrugRMZ_EAN13Code,
			    drugrmz_firmpack := :DrugRMZ_FirmPack,
			    drugrmz_countrypack := :DrugRMZ_CountryPack,
			    drugrmz_userange := :DrugRMZ_UseRange,
			    drugrmz_godndate := :DrugRMZ_GodnDate,
			    drugrmz_godndateday := :DrugRMZ_GodnDateDay,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function saveDrugRMZLink(DrugNomen_model $callObject, $data)
	{
		if ($data["Drug_id"] <= 0 || $data["pmUser_id"] <= 0) {
			return ["success" => false];
		}
		if ($data["DrugRMZ_id"] <= 0) {
			$data["DrugRMZ_id"] = null;
		}
		if (empty($data["DrugRMZ_oldid"]) || $data["DrugRMZ_oldid"] <= 0) {
			$data["DrugRMZ_oldid"] = null;
		}
		if ($data["DrugRMZ_id"] != $data["DrugRMZ_oldid"]) {
			$query = "
				update rls.DrugRMZ
				set
					Drug_id = :Drug_id,
					pmUser_updID = :pmUser_id,
					DrugRMZ_updDT = tzgetdate()
				where DrugRMZ_id = :DrugRMZ_id;
			";
			$callObject->db->query($query, $data);
			if (!empty($data["DrugRMZ_oldid"])) {
				$query = "
					update rls.DrugRMZ
					set
						Drug_id = null,
						pmUser_updID = :pmUser_id,
						DrugRMZ_updDT = tzgetdate()
					where DrugRMZ_id = :DrugRMZ_oldid;
				";
				$callObject->db->query($query, $data);
			}
		}
		return ["success" => true];
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveDrugVznData(DrugNomen_model $callObject, $data)
	{
		//ищем по медикаменту существующую запись в справочнике
		$query = "
			select DrugVZN_id
			from rls.v_DrugVZN
			where Drug_id = :Drug_id
			order by DrugVZN_id
			limit 1
		";
		$id = $callObject->getFirstResultFromQuery($query, $data);
		$result = $callObject->saveObject("rls.DrugVZN", [
			"DrugVZN_id" => !empty($id) ? $id : null,
			"DrugFormMnnVZN_id" => !empty($data["DrugFormMnnVZN_id"]) ? $data["DrugFormMnnVZN_id"] : null,
			"Drug_id" => $data["Drug_id"],
			"DrugVZN_fid" => !empty($data["DrugVZN_fid"]) ? $data["DrugVZN_fid"] : null,
			"DrugFormVZN_id" => !empty($data["DrugFormVZN_id"]) ? $data["DrugFormVZN_id"] : null,
			"DrugDose_id" => !empty($data["DrugDose_id"]) ? $data["DrugDose_id"] : null,
			"DrugKolDose_id" => !empty($data["DrugKolDose_id"]) ? $data["DrugKolDose_id"] : null,
			"DrugRelease_id" => !empty($data["DrugRelease_id"]) ? $data["DrugRelease_id"] : null
		]);
		return $result;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function saveGoodsPackCount(DrugNomen_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		$data["Region_id"] = $callObject->getRegionNumber();
		if (!isset($data["GoodsPackCount_id"])) {
			$query = "
				select count(*) as cnt
				from v_GoodsPackCount
				where Region_id = :Region_id
				  and DrugComplexMnn_id = :DrugComplexMnn_id
				  and GoodsUnit_id = :GoodsUnit_id
				  and GoodsPackCount_Count = :GoodsPackCount_Count
				  and coalesce(Org_id, 0) = coalesce(:Org_id::bigint, 0)
			";
			$result = $callObject->db->query($query, $data);
			if (!is_object($result)) {
				return ["success" => false];
			}
			$result = $result->result("array");
			if ($result[0]["cnt"] > 0) {
				return ["success" => false, "Error_Code" => 400];
			}
		}
		$procedure = (empty($data["GoodsPackCount_id"]))?"dbo.p_GoodsPackCount_ins":"dbo.p_GoodsPackCount_upd";
		$selectString = "
		    goodspackcount_id as \"GoodsPackCount_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    goodspackcount_id := :GoodsPackCount_id,
			    drugcomplexmnn_id := :DrugComplexMnn_id,
			    tradenames_id := :TRADENAMES_ID,
			    goodspackcount_count := :GoodsPackCount_Count,
			    goodsunit_id := :GoodsUnit_id,
			    org_id := :Org_id,
			    region_id := :Region_id,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $callObject->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result("array");
			if (isset($result[0]) && empty($result[0]["Error_Msg"])) {
				return ["success" => true];
			}
		}
		return ["success" => false];
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function updateDrugRMZLink(DrugNomen_model $callObject, $data)
	{
		//массив запросов для связываний позиций справочников
		$query_array = [];
		//1. код EAN + № РУ + дата
		$query_array[] = "
            select
				drmz.DrugRMZ_id as \"DrugRMZ_id\",
				d.Drug_id as \"Drug_id\"
			from
				rls.DrugRMZ drmz
				left join lateral (
					select
						max(i_d.Drug_id) as Drug_id,
						count(i_d.Drug_id) as cnt
					from rls.v_Drug i_d
					where drmz.DrugRMZ_EAN13Code is not null
					  and i_d.Drug_Ean = drmz.DrugRMZ_EAN13Code
					  and i_d.Drug_RegNum = drmz.DrugRMZ_RegNum
					  and i_d.Drug_begDate = drmz.DrugRMZ_RegDate
				) as d on true
			where drmz.Drug_id is null
			  and coalesce(d.cnt, 0) = 1
        ";
		//2. код EAN + № РУ
		$query_array[] = "
            select
				drmz.DrugRMZ_id as \"DrugRMZ_id\",
				d.Drug_id as \"Drug_id\"
			from
				rls.DrugRMZ drmz
				left join lateral (
					select
						max(i_d.Drug_id) as Drug_id,
						count(i_d.Drug_id) as cnt
					from rls.v_Drug i_d
					where drmz.DrugRMZ_EAN13Code is not null
					  and i_d.Drug_Ean = drmz.DrugRMZ_EAN13Code
					  and i_d.Drug_RegNum = drmz.DrugRMZ_RegNum
				) as d on true
			where drmz.Drug_id is null
			  and coalesce(d.cnt, 0) = 1
        ";
		//3. код РУ + дата
		$query_array[] = "
            select
				drmz.DrugRMZ_id as \"DrugRMZ_id\",
				d.Drug_id as \"Drug_id\"
			from
				rls.DrugRMZ drmz
				left join lateral (
					select
						max(i_d.Drug_id) as Drug_id,
						count(i_d.Drug_id) as cnt
					from rls.v_Drug i_d
					where i_d.Drug_RegNum = drmz.DrugRMZ_RegNum
					  and i_d.Drug_begDate = drmz.DrugRMZ_RegDate
				) as d on true
			where drmz.Drug_id is null
			  and coalesce(d.cnt, 0) = 1
        ";
		//4. код РУ
		$query_array[] = "
            select
				drmz.DrugRMZ_id as \"DrugRMZ_id\",
				d.Drug_id as \"Drug_id\"
			from
				rls.DrugRMZ drmz
				left join lateral (
					select
						max(i_d.Drug_id) as Drug_id,
						count(i_d.Drug_id) as cnt
					from rls.v_Drug i_d
					where i_d.Drug_RegNum = drmz.DrugRMZ_RegNum
				) as d on true
			where drmz.Drug_id is null
			  and coalesce(d.cnt, 0) = 1
        ";
		//5. указан код EAN в спр. РЗН + код EAN + № РУ + лекарственная форма + дозировка + фасовка + страна фирмы производителя
		$query_array[] = "
            select
                drmz.DrugRMZ_id as \"DrugRMZ_id\",
                d.Drug_id as \"Drug_id\"
            from
                rls.DrugRMZ drmz
                left join lateral (
                    select
                        max(i_d.Drug_id) as Drug_id,
                        count(i_d.Drug_id) as cnt
                    from
                        rls.v_Drug i_d
                        left join rls.v_DrugComplexMnn i_dcm on i_dcm.DrugComplexMnn_id = i_d.DrugComplexMnn_id
                        left join rls.v_DrugComplexMnnDose i_dcmd on i_dcmd.DrugComplexMnnDose_id = i_dcm.DrugComplexMnnDose_id
                        left join rls.v_Prep i_p on i_p.Prep_id = i_d.DrugPrep_id
                        left join rls.v_FIRMS i_f on i_f.FIRMS_ID = i_p.FIRMID
                        left join rls.v_COUNTRIES i_c on i_c.COUNTRIES_ID = i_f.COUNTID
                    where
                        drmz.DrugRMZ_EAN13Code is not null and
                        i_d.Drug_Ean = drmz.DrugRMZ_EAN13Code and
                        i_d.Drug_RegNum = drmz.DrugRMZ_RegNum and
                        i_d.drugform_fullname = drmz.DrugRMZ_Form and
                        i_dcmd.DrugComplexMnnDose_Name = drmz.DrugRMZ_Dose and
                        i_d.Drug_Fas = drmz.DrugRMZ_PackSize and
                        i_c.NAME = drmz.DrugRMZ_Country
                ) as d on true
            where drmz.Drug_id is null
              and coalesce(d.cnt, 0) = 1
        ";
		//6. указан код EAN в спр. РЗН + код EAN + № РУ + лекарственная форма + дозировка + фасовка + фирма-производитель + страна фирмы производителя + фирма-упаковщик  (если записей более 1, то выбрать более позднюю запись)
		$query_array[] = "
            select
                drmz.DrugRMZ_id as \"DrugRMZ_id\",
                d.Drug_id as \"Drug_id\"
            from
                rls.DrugRMZ drmz
                left join lateral (
	                select
	                    max(i_d.Drug_id) as Drug_id,
	                    count(i_d.Drug_id) as cnt
	                from
	                    rls.v_Drug i_d
	                    left join rls.v_DrugComplexMnn i_dcm on i_dcm.DrugComplexMnn_id = i_d.DrugComplexMnn_id
	                    left join rls.v_DrugComplexMnnDose i_dcmd on i_dcmd.DrugComplexMnnDose_id = i_dcm.DrugComplexMnnDose_id
	                    left join rls.v_Nomen i_n on i_n.NOMEN_ID = i_d.Drug_id
	                    left join rls.v_Prep i_p on i_p.Prep_id = i_d.DrugPrep_id
	                    left join rls.v_FIRMS i_f on i_f.FIRMS_ID = i_p.FIRMID
	                    left join rls.v_COUNTRIES i_c on i_c.COUNTRIES_ID = i_f.COUNTID
	                    left join rls.v_FIRMS i_n_f on i_n_f.FIRMS_ID = i_n.FIRMID
	                where drmz.DrugRMZ_EAN13Code is not null
	                  and i_d.Drug_Ean = drmz.DrugRMZ_EAN13Code
	                  and i_d.Drug_RegNum = drmz.DrugRMZ_RegNum
	                  and i_d.drugform_fullname = drmz.DrugRMZ_Form
	                  and i_dcmd.DrugComplexMnnDose_Name = drmz.DrugRMZ_Dose
	                  and i_d.Drug_Fas = drmz.DrugRMZ_PackSize
	                  and i_f.FULLNAME = drmz.DrugRMZ_Firm
	                  and i_c.NAME = drmz.DrugRMZ_Country
	                  and i_n_f.FULLNAME = drmz.DrugRMZ_FirmPack
                ) as d on true
            where drmz.Drug_id is null
              and coalesce(d.cnt, 0) >= 1
        ";
		//7. не указан код EAN в спр. РЗН + № РУ + лекарственная форма + дозировка + фасовка + страна фирмы производителя
		$query_array[] = "
            select
                drmz.DrugRMZ_id as \"DrugRMZ_id\",
                d.Drug_id as \"Drug_id\"
            from
                rls.DrugRMZ drmz
                left join lateral (
                    select
                        max(i_d.Drug_id) as Drug_id,
                        count(i_d.Drug_id) as cnt
                    from
                        rls.v_Drug i_d
                        left join rls.v_DrugComplexMnn i_dcm on i_dcm.DrugComplexMnn_id = i_d.DrugComplexMnn_id
                        left join rls.v_DrugComplexMnnDose i_dcmd on i_dcmd.DrugComplexMnnDose_id = i_dcm.DrugComplexMnnDose_id
                        left join rls.v_Prep i_p on i_p.Prep_id = i_d.DrugPrep_id
                        left join rls.v_FIRMS i_f on i_f.FIRMS_ID = i_p.FIRMID
                        left join rls.v_COUNTRIES i_c on i_c.COUNTRIES_ID = i_f.COUNTID
                    where drmz.DrugRMZ_EAN13Code is null
                      and i_d.Drug_RegNum = drmz.DrugRMZ_RegNum
                      and i_d.drugform_fullname = drmz.DrugRMZ_Form
                      and i_dcmd.DrugComplexMnnDose_Name = drmz.DrugRMZ_Dose
                      and i_d.Drug_Fas = drmz.DrugRMZ_PackSize
                      and i_c.NAME = drmz.DrugRMZ_Country
                ) as d on true
            where drmz.Drug_id is null
              and coalesce(d.cnt, 0) = 1
        ";
		//8. не указан код EAN в спр. РЗН + № РУ + лекарственная форма + дозировка + фасовка + фирма-производитель + страна фирмы производителя + фирма-упаковщик (если записей более 1, то выбрать более позднюю запись)
		$query_array[] = "
            select
                drmz.DrugRMZ_id as \"DrugRMZ_id\",
                d.Drug_id as \"Drug_id\"
            from
                rls.DrugRMZ drmz
                left join lateral (
	                select
	                    max(i_d.Drug_id) as Drug_id,
	                    count(i_d.Drug_id) as cnt
	                from
	                    rls.v_Drug i_d
	                    left join rls.v_DrugComplexMnn i_dcm on i_dcm.DrugComplexMnn_id = i_d.DrugComplexMnn_id
	                    left join rls.v_DrugComplexMnnDose i_dcmd on i_dcmd.DrugComplexMnnDose_id = i_dcm.DrugComplexMnnDose_id
	                    left join rls.v_Nomen i_n on i_n.NOMEN_ID = i_d.Drug_id
	                    left join rls.v_Prep i_p on i_p.Prep_id = i_d.DrugPrep_id
	                    left join rls.v_FIRMS i_f on i_f.FIRMS_ID = i_p.FIRMID
	                    left join rls.v_COUNTRIES i_c on i_c.COUNTRIES_ID = i_f.COUNTID
	                    left join rls.v_FIRMS i_n_f on i_n_f.FIRMS_ID = i_n.FIRMID
	                where drmz.DrugRMZ_EAN13Code is null
	                  and i_d.Drug_RegNum = drmz.DrugRMZ_RegNum
	                  and i_d.drugform_fullname = drmz.DrugRMZ_Form
	                  and i_dcmd.DrugComplexMnnDose_Name = drmz.DrugRMZ_Dose
	                  and i_d.Drug_Fas = drmz.DrugRMZ_PackSize
	                  and i_f.FULLNAME = drmz.DrugRMZ_Firm
	                  and i_c.NAME = drmz.DrugRMZ_Country
	                  and i_n_f.FULLNAME = drmz.DrugRMZ_FirmPack
                ) as d on true
            where drmz.Drug_id is null
              and coalesce(d.cnt, 0) >= 1
        ";
		/**@var CI_DB_result $result */
		//получение данных и сохранение связей
		foreach ($query_array as $query) {
			$result = $callObject->db->query($query);
			if (is_object($result)) {
				$result = $result->result("array");
				foreach ($result as $rmz_data) {
					$callObject->saveDrugRMZLink([
						"pmUser_id" => $data["pmUser_id"],
						"DrugRMZ_id" => $rmz_data["DrugRMZ_id"],
						"Drug_id" => $rmz_data["Drug_id"]
					]);
				}
			}
		}
		return true;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function importDrugRMZFromCsv(DrugNomen_model $callObject, $data)
	{
		$start_data = false;
		$add_count = 0;
		//получаем максимальное значение DrugRPN_id из справочника
		$query = "
			select max(DrugRPN_id) as Max_id
			from rls.v_DrugRMZ
		";
		$max_id = $callObject->getFirstResultFromQuery($query);
		if (($h = fopen($data["FileFullName"], "r")) !== false) {
			while (($rec_data = fgetcsv($h, 1000, ";")) !== false) {
				if ($start_data && $rec_data[0] > $max_id) {
					$callObject->saveDrugRMZ([
						"DrugRPN_id" => iconv("cp1251", "UTF-8", $rec_data[0]),
						"DrugRMZ_CodeRZN" => iconv("cp1251", "UTF-8", $rec_data[1]),
						"DrugRMZ_RegNum" => iconv("cp1251", "UTF-8", $rec_data[2]),
						"DrugRMZ_Country" => iconv("cp1251", "UTF-8", $rec_data[3]),
						"DrugRMZ_Form" => iconv("cp1251", "UTF-8", $rec_data[4]),
						"DrugRMZ_Name" => iconv("cp1251", "UTF-8", $rec_data[5]),
						"DrugRMZ_Dose" => iconv("cp1251", "UTF-8", $rec_data[6]),
						"DrugRMZ_Firm" => iconv("cp1251", "UTF-8", $rec_data[7]),
						"DrugRMZ_MNN" => iconv("cp1251", "UTF-8", $rec_data[8]),
						"DrugRMZ_RegDate" => iconv("cp1251", "UTF-8", $rec_data[9]),
						"DrugRMZ_Cond" => iconv("cp1251", "UTF-8", $rec_data[10]),
						"DrugRMZ_Pack" => iconv("cp1251", "UTF-8", $rec_data[11]),
						"DrugRMZ_PackSize" => iconv("cp1251", "UTF-8", $rec_data[12]),
						"DrugRMZ_EAN13Code" => iconv("cp1251", "UTF-8", $rec_data[13]),
						"DrugRMZ_FirmPack" => iconv("cp1251", "UTF-8", $rec_data[14]),
						"DrugRMZ_CountryPack" => iconv("cp1251", "UTF-8", $rec_data[15]),
						"DrugRMZ_UseRange" => iconv("cp1251", "UTF-8", $rec_data[18]),
						"DrugRMZ_GodnDate" => iconv("cp1251", "UTF-8", $rec_data[19]),
						"DrugRMZ_GodnDateDay" => iconv("cp1251", "UTF-8", $rec_data[20]),
						"pmUser_id" => $data["pmUser_id"]
					]);
					$add_count++;
				}
				if ($rec_data[0] == "DrugID") {
					$start_data = true;
				}
			}
			fclose($h);
		}
		//обновление связей между таблицами rls.Drug и rls.DrugRMZ
		$callObject->updateDrugRMZLink(["pmUser_id" => $data["pmUser_id"]]);
		return ["success" => true, "data" => ["add_count" => $add_count]];
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return mixed|null
	 */
	public static function addDrugPrepFasCodeByDrugId(DrugNomen_model $callObject, $data)
	{
		/**@var mixed|null $code_id */
		$code_id = null;
		if (empty($data["Org_id"])) {
			$data["Org_id"] = null;
		}
		if (empty($data["DrugPrepFas_id"])) {
			$query = "
                select
                    d.DrugPrepFas_id as \"DrugPrepFas_id\",
                    d.DrugComplexMnn_id as \"DrugComplexMnn_id\",
                   	d.DrugTorg_id as \"DrugTorg_id\",
                   	d.DrugTorg_Name as \"DrugTorg_Name\",
                    dc.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
                    dcn.DrugComplexMnnName_Name as \"DrugComplexMnnName_Name\"
                from
                    rls.v_Drug d
                    left join rls.v_DrugComplexMnn dc on dc.DrugComplexMnn_id = d.DrugComplexMnn_id
                    left join rls.v_DrugComplexMnnName dcn on dcn.DrugComplexMnnName_id = dc.DrugComplexMnnName_id
                where d.Drug_id = :Drug_id
				limit 1
            ";
			$queryParams = ["Drug_id" => $data["Drug_id"]];
			$result = $callObject->getFirstRowFromQuery($query, $queryParams);
			$data["DrugPrepFas_id"] = (!empty($result["DrugPrepFas_id"]) ? $result["DrugPrepFas_id"] : null);
			$data["DrugComplexMnn_id"] = (!empty($result["DrugComplexMnn_id"]) ? $result["DrugComplexMnn_id"] : null);
			$data["TRADENAMES_ID"] = (!empty($result["DrugTorg_id"]) ? $result["DrugTorg_id"] : null);
			$rusname = $result["DrugComplexMnn_RusName"];
			$name = $result["DrugComplexMnnName_Name"];
			$torgname = $result["DrugTorg_Name"];
			$data["DrugPrepFasCode_Name"] = (stripos($rusname, $name) >= 0) ? str_ireplace($name, $torgname, $rusname) : $rusname;
		}
		//ищем существующую запись с кодом
		$query = "
            select dpfc.DrugPrepFasCode_id as \"DrugPrepFasCode_id\"
            from rls.v_DrugPrepFasCode dpfc
            where dpfc.DrugPrepFas_id = :DrugPrepFas_id
              and coalesce(dpfc.Org_id, 0) = coalesce(:Org_id, 0)
            order by dpfc.DrugPrepFasCode_id
			limit 1
        ";
		$queryParams = [
			"DrugPrepFas_id" => $data["DrugPrepFas_id"],
			"Org_id" => $data["Org_id"]
		];
		$result = $callObject->getFirstRowFromQuery($query, $queryParams);
		$code_id = !empty($result["DrugPrepFasCode_id"]) ? $result["DrugPrepFasCode_id"] : null;
		if (empty($code_id)) {
			//если код не найден, добавляем его
			$query = "
				select
				    drugprepfascode_id as \"DrugPrepFasCode_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from rls.p_drugprepfascode_ins(
				    drugprepfascode_code := CAST(:DrugPrepFasCode_Code as varchar),
				    drugprepfas_id := :DrugPrepFas_id,
				    org_id := :Org_id,
				    drugcomplexmnn_id := :DrugComplexMnn_id,
				    tradenames_id := :TRADENAMES_ID,
				    drugprepfascode_name := :DrugPrepFasCode_Name,
				    pmuser_id := :pmUser_id
				);
            ";
			$result = $callObject->getFirstRowFromQuery($query, $data);
			if (!empty($result["DrugPrepFasCode_id"])) {
				$code_id = $result["DrugPrepFasCode_id"];
			}
		}
		return $code_id;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $object
	 * @param $id
	 * @param $data
	 * @return bool|float|int|string|null
	 */
	public static function addNomenData(DrugNomen_model $callObject, $object, $id, $data)
	{
		$callObject->load->model('RlsDrug_model', 'RlsDrug_model');
		if (empty($object) || $id <= 0) {
			return null;
		}
		$code_tbl = null;
		$code_id = null;
		$object_array = [
			"Drug" => ["code_tbl" => "DrugNomen"],
			"TRADENAMES" => ["code_tbl" => "DrugTorgCode"],
			"ACTMATTERS" => ["code_tbl" => "DrugMnnCode"],
			"DrugComplexMnn" => ["code_tbl" => "DrugComplexMnnCode"]
		];
		if (!empty($object_array[$object])) {
			$code_tbl = $object_array[$object]["code_tbl"];
			if ($object == "Drug") {
				//для медикамента нужно предварительно добавить код группировочного торгового, так как этот код участвует в формировании кода медикамента
				$callObject->addDrugPrepFasCodeByDrugId([
					"Drug_id" => $id,
					"pmUser_id" => $data["pmUser_id"]
				]);
			}
			// Ищем запись в таблице номенклатурного справочника
			$query = "
                select {$code_tbl}_id as code_id
                from rls.v_{$code_tbl}
                where {$object}_id = :id;
            ";
			$code_id = $callObject->getFirstResultFromQuery($query, ["id" => $id]);
			if (empty($code_id)) { //добавляем запись в номенклатурный справочник
				//получаем новый код
				$new_code_data = $callObject->generateCodeForObject([
					"Object" => $code_tbl,
					"Drug_id" => $object == "Drug" ? $id : null
				]);
				$new_code = !empty($new_code_data[0]) && !empty($new_code_data[0][$code_tbl . "_Code"]) ? $new_code_data[0][$code_tbl . "_Code"] : null;
				if (!empty($new_code)) {
					if ($object == "Drug") {
						//получаем информацию о медикаменте
						$query = "
                            select
                                d.Drug_Name as \"Drug_Name\",
                                d.DrugTorg_Name as \"DrugTorg_Name\",
                                d.DrugTorg_id as \"Tradenames_id\",
                                DrugComplexMnnName.ActMatters_id as \"Actmatters_id\",
                                dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\",
								A.STRONGGROUPID as \"STRONGGROUPID\",
								A.NARCOGROUPID as \"NARCOGROUPID\",
								P.NTFRID as \"CLSNTFR_ID\",
								d.PrepType_id as \"PrepType_id\"
                            from
                                rls.v_Drug d
                                left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                                left join rls.DrugComplexMnnName on DrugComplexMnnName.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                            	left join rls.v_ACTMATTERS A on A.Actmatters_id = DrugComplexMnnName.ActMatters_id
								left join rls.Prep P on P.Prep_id = d.DrugPrep_id
                            where Drug_id = :id
                        ";
						$drug_data = $callObject->getFirstRowFromQuery($query, ["id" => $id]);
						if (is_array($drug_data)) {
							//добавляем запись в таблицу
							$selectString = "
							    {$code_tbl}_id as \"{$code_tbl}_id\",
							    error_code as \"Error_Code\",
							    error_message as \"Error_Msg\"
							";
							$query = "
								select {$selectString}
								from rls.p_{$code_tbl}_ins(
								    {$object}_id := :{$object}_id,
								    {$code_tbl}_code := :{$code_tbl}_Code,
								    drugnomen_name := :DrugNomen_Name,
								    drugnomen_nick := :DrugNomen_Nick,
								    drugtorgcode_id := :DrugTorgCode_id,
								    drugmnncode_id := :DrugMnnCode_id,
								    drugcomplexmnncode_id := :DrugComplexMnnCode_id,
								    prepclass_id := coalesce((select PrepClass_id from rls.v_PrepClass where PrepClass_Code = 2), :PrepClass_id),
								    okei_id := :okei_id,
								    region_id := null,
								    drugnds_id := :nds_id,
								    pmuser_id := :pmUser_id
								);
                            ";
							$params = [
								"DrugNomen_Name" => $drug_data["Drug_Name"],
								"DrugNomen_Nick" => $drug_data["DrugTorg_Name"],
								"DrugTorgCode_id" => $drug_data["Tradenames_id"] > 0 ? $callObject->addNomenData("TRADENAMES", $drug_data["Tradenames_id"], $data) : null,
								"DrugMnnCode_id" => $drug_data["Actmatters_id"] > 0 ? $callObject->addNomenData("ACTMATTERS", $drug_data["Actmatters_id"], $data) : null,
								"DrugComplexMnnCode_id" => $drug_data["DrugComplexMnn_id"] > 0 ? $callObject->addNomenData("DrugComplexMnn", $drug_data["DrugComplexMnn_id"], $data) : null,
								"PrepClass_id" => $callObject->RlsDrug_model->getDrugPrepClassId($drug_data),
								"" . $object . "_id" => $id,
								"" . $code_tbl . "_Code" => $new_code,
								"pmUser_id" => $data["pmUser_id"],
								"nds_id" => !empty($data["DrugNds_id"]) ? $data["DrugNds_id"] : null,
								"okei_id" => !empty($data["Okei_id"]) ? $data["Okei_id"] : null
							];
							$result = $callObject->getFirstRowFromQuery($query, $params);
							if (!empty($result)) {
								$code_id = $result[$code_tbl . "_id"];
							}
						}
					} else {
						//добавляем запись в таблицу
						$selectString = "
						    {$code_tbl}_id as \"{$code_tbl}_id\",
						    error_code as \"Error_Code\",
						    error_message as \"Error_Msg\"
						";
						$query = "
							select {$selectString}
							from rls.p_{$code_tbl}_ins(
                                {$object}_id = :{$object}_id,
                                {$code_tbl}_Code = :{$code_tbl}_Code,
							    region_id := null,
							    pmuser_id := :pmUser_id
							);
                        ";
						$params = [
							$object . "_id" => $id,
							$code_tbl . "_Code" => $new_code,
							"pmUser_id" => $data["pmUser_id"]
						];
						$result = $callObject->getFirstRowFromQuery($query, $params);
						if (!empty($result)) {
							$code_id = $result[$code_tbl . "_id"];
						}
						if ($object == "DrugComplexMnn") {
							//При добавлении в справочник комплексного МНН необходимо позаботится и о добавлении действующего вещества
							//получаем информацию о комплексном МНН
							$query = "
                                select DrugComplexMnnName.ActMatters_id as \"Actmatters_id\"
                                from
                                    rls.v_DrugComplexMnn
                                    left join rls.DrugComplexMnnName on DrugComplexMnnName.DrugComplexMnnName_id = v_DrugComplexMnn.DrugComplexMnnName_id
                                where DrugComplexMnn_id = :id;
                            ";
							$dcm_data = $callObject->getFirstRowFromQuery($query, ["id" => $id]);
							if (!empty($dcm_data["Actmatters_id"])) {
								$callObject->addNomenData("ACTMATTERS", $dcm_data["Actmatters_id"], $data);
							}
						}
					}
				}
			}
		}
		return $code_id;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function checkDrugNomen(DrugNomen_model $callObject, $data)
	{
		$response = ["success" => false, "Error_Msg" => ""];
		$data["DrugNomen_id"] = empty($data["DrugNomen_id"]) ? 0 : $data["DrugNomen_id"];
		$data["DrugMnnCode_id"] = empty($data["DrugMnnCode_id"]) ? 0 : $data["DrugMnnCode_id"];
		$data["DrugTorgCode_id"] = empty($data["DrugTorgCode_id"]) ? 0 : $data["DrugTorgCode_id"];
		$data["DrugComplexMnnCode_id"] = empty($data["DrugComplexMnnCode_id"]) ? 0 : $data["DrugComplexMnnCode_id"];
		$query_arr = [
			"
				select
					'Drug_id' as \"Field\",
				    COUNT(DN.DrugNomen_id) as \"Count\"
				from rls.v_DrugNomen DN
				where DN.Drug_id = :Drug_id
				  and DN.DrugNomen_id <> :DrugNomen_id
				limit 1
			",
			"
				select
					'DrugNomen_Code' as \"Field\",
				    COUNT(DN.DrugNomen_id) as \"Count\"
				from rls.v_DrugNomen DN
				where DN.DrugNomen_Code = :DrugNomen_Code
				  and DN.DrugNomen_id <> :DrugNomen_id
				limit 1
			"
		];
		if (!empty($data["Actmatters_id"])) {
			$query_arr[] = "
				select
					'DrugMnnCode_Code' as \"Field\",
				    COUNT(DMC.DrugMnnCode_id) as \"Count\"
				from rls.v_DrugMnnCode DMC
				where DMC.DrugMnnCode_Code = :DrugMnnCode_Code
				  and DMC.DrugMnnCode_id <> :DrugMnnCode_id
				limit 1
			";
		}
		if (!empty($data["Tradenames_id"])) {
			$query_arr[] = "
				select
					'DrugTorgCode_Code' as \"Field\",
				    COUNT(DTC.DrugTorgCode_id) as \"Count\"
				from rls.v_DrugTorgCode DTC
				where DTC.DrugTorgCode_Code = :DrugTorgCode_Code
				  and DTC.DrugTorgCode_id <> :DrugTorgCode_id
				limit 1
			";
		}
		if (!empty($data["DrugComplexMnn_id"])) {
			$query_arr[] = "
				select
					'DrugComplexMnnCode_Code' as \"Field\",
					COUNT(DCMC.DrugComplexMnnCode_id) as \"Count\"
				from rls.v_DrugComplexMnnCode DCMC
				where DCMC.DrugComplexMnnCode_Code = :DrugComplexMnnCode_Code
				  and DCMC.DrugComplexMnnCode_id <> :DrugComplexMnnCode_id
			";
		}
		$error_arr = [
			"Drug_id" => "Указанный препарат уже имеется в номенклатурном справочнике",
			"DrugNomen_Code" => "Код номенклатурной карточки должен быть уникальным",
			"DrugMnnCode_Code" => "Код МНН должен быть уникальным",
			"DrugTorgCode_Code" => "Код торг. наим. должен быть уникальным",
			"DrugComplexMnnCode_Code" => "Код компл. МНН должен быть уникальным"
		];
		foreach ($query_arr as $key => $query) {
			$result = $callObject->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception("Ошибка выполнения запроса к базе данных");
			}
			$res_arr = $result->result("array");
			if (!is_array($res_arr)) {
				throw new Exception("Ошибка выполнения запроса к базе данных");
			}
			if ($res_arr[0]["Count"] > 0) {
				$response["Error_Msg"] = $error_arr[$res_arr[0]["Field"]];
				throw new Exception($error_arr[$res_arr[0]["Field"]]);
			}
		}
		$response["success"] = true;
		return $response;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function generateCodeForObject(DrugNomen_model $callObject, $data)
	{
		$object = $data["Object"];
		$query = "";
		$params = [];
		if (empty($data["Org_id"])) {
			$data["Org_id"] = null;
		}
		switch ($object) {
			case "DrugNomen":
			case "DrugNomenOrgLink":
				$code = 0;
				$dpf_id = null;
				if (!empty($data["Drug_id"])) {
					$query = "
                        select
                            d.DrugPrepFas_id as \"DrugPrepFas_id\",
                            dpfc.DrugPrepFasCode_Code as \"DrugPrepFasCode_Code\"
                        from
                            rls.v_Drug d
                            left join rls.DrugPrepFasCode dpfc on dpfc.DrugPrepFas_id = d.DrugPrepFas_id
                        where d.Drug_id = :Drug_id::bigint
                          and coalesce(dpfc.Org_id, 0) = coalesce(:Org_id::bigint, 0)
                    ";
					$result = $callObject->getFirstRowFromQuery($query, $data);
					if (!empty($result["DrugPrepFasCode_Code"])) {
						$code = $result["DrugPrepFasCode_Code"];
					}
					if (!empty($result["DrugPrepFas_id"])) {
						$dpf_id = $result["DrugPrepFas_id"];
					}
				}
				if ($object == "DrugNomen") {
					$query = "
                        select
                            (max(coalesce(p.num::int8, 0)) + 1)::varchar as \"DrugNomen_Code\"
                        from (
                            select
                                substring(DN.DrugNomen_Code, strpos('.', DN.DrugNomen_Code) + 1, length(DN.DrugNomen_Code)) as num
                            from
                                rls.v_DrugNomen DN
                                left join rls.v_Drug D on D.Drug_id = DN.Drug_id
                            union
                            select '0'
                        ) p
                        where isnumeric(p.num) = 1
                    ";
				}
				if ($object == "DrugNomenOrgLink") {
					$query = "
                        select
                            '{$code}.'||(max(coalesce(p.num::int8, 0)) + 1)::varchar as \"DrugNomenOrgLink_Code\"
                        from (
                            select
                                substring(DNOL.DrugNomenOrgLink_Code, strpos('.', DNOL.DrugNomenOrgLink_Code), length(DNOL.DrugNomenOrgLink_Code)) as num
                            from
                                rls.v_DrugNomenOrgLink DNOL
                                left join rls.v_DrugNomen DN on DN.DrugNomen_id = DNOL.DrugNomen_id
                                left join rls.v_Drug D on D.Drug_id = DN.Drug_id
                            where DNOL.DrugNomenOrgLink_Code like '{$code}.%'
                              and (:Org_id is null or DNOL.Org_id = :Org_id)
                              and (:DrugPrepFas_id is null or D.DrugPrepFas_id = :DrugPrepFas_id)
                            union select '0'
                        ) p
                        where isnumeric(p.num) = 1
                    ";
				}
				$params["DrugPrepFas_id"] = $dpf_id;
				$params["Org_id"] = $data["Org_id"];
				break;
			case "DrugPrepFasCode":
				$dpf_id = null;
				if (!empty($data["Drug_id"])) {
					$query = "
                        select
                            d.DrugPrepFas_id as \"DrugPrepFas_id\",
                            dpfc.DrugPrepFasCode_Code as \"DrugPrepFasCode_Code\"
                        from
                            rls.v_Drug d
                            left join rls.DrugPrepFasCode dpfc on dpfc.DrugPrepFas_id = d.DrugPrepFas_id
                        where d.Drug_id = :Drug_id
                    ";
					$result = $callObject->getFirstRowFromQuery($query, $data);
					if (!empty($result["DrugPrepFas_id"])) {
						$dpf_id = $result["DrugPrepFas_id"];
					}
				}
				$query = "
                    select
                        (max(coalesce(p.DrugPrepFasCode_Code::int8, 0)) + 1) as \"{$object}_Code\"
                    from (
                        select dpfc.DrugPrepFasCode_Code
                        from rls.v_DrugPrepFasCode dpfc
                        where dpfc.DrugPrepFas_id = :DrugPrepFas_id
                          and coalesce(dpfc.Org_id, 0) = coalesce(:Org_id::bigint, 0)
                        union select '0'
                    ) p
                    where length(p.DrugPrepFasCode_Code) <= 18
                      and coalesce((
                            select
                            	case when strpos('.', p.DrugPrepFasCode_Code) > 0 Then 0 Else 1 End
                            where isnumeric(p.DrugPrepFasCode_Code) = 1
                        ), 0) = 1
					limit 1
                ";
				$params["DrugPrepFas_id"] = $dpf_id;
				$params["Org_id"] = $data["Org_id"];
				break;
			default:
				$query = "
                    select
                        (max(coalesce(DN.{$object}_Code::int8, 0)) + 1)::varchar as \"{$object}_Code\"
                    from rls.v_{$object} DN
                    where 
                      length(DN.{$object}_Code) <= 18
                      and 
                      coalesce((
                            select
                            	case when strpos('.', DN.{$object}_Code) > 0 Then 0 Else 1 End
                            Where isnumeric(DN.{$object}_Code) = 1
                        ), 0) = 1
					limit 1
                ";
				break;
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function deleteDrugMnnCode(DrugNomen_model $callObject, $data)
	{
		//проверяем наличие кода в номенклатурном справочнике
		$query = "
			select count(DrugNomen_id) as cnt
			from rls.v_DrugNomen
			where DrugMnnCode_id = :DrugMnnCode_id;
		";
		$queryParams = ["DrugMnnCode_id" => $data["id"]];
		$result = $callObject->getFirstResultFromQuery($query, $queryParams);
		if ($result > 0) {
			throw new Exception("Удаление невозможно, так как код используется в номенклатурном справочнике");
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from rls.p_drugmnncode_del(drugmnncode_id := :DrugMnnCode_id);
		";
		$queryParams = ["DrugMnnCode_id" => $data["id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function deleteDrugPrepEdUcCount(DrugNomen_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		if (isset($data['Org_id'])) {
			$query = "
				select coalesce(Org_id,0) as \"Org_id\"
				from rls.v_DrugPrepEdUcCount
				where DrugPrepEdUcCount_id = :DrugPrepEdUcCount_id
			";
			$result = $callObject->db->query($query, $data);
			if (is_object($result)) {
				$result = $result->result("array");
				if (!isSuperadmin() && $result[0]["Org_id"] != $data["Org_id"]) {
					return ["success" => false, "Error_Code" => 500];
				}
			}
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from rls.p_drugprepeduccount_del(drugprepeduccount_id := :DrugPrepEdUcCount_id);
		";
		$result = $callObject->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result("array");
			if (isset($result[0]) && empty($result[0]["Error_Msg"])) {
				return ["success" => true];
			}
		}
		return ["success" => false];
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function deleteDrugTorgCode(DrugNomen_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		//проверяем наличие кода в номенклатурном справочнике
		$query = "
			select count(DrugNomen_id) as cnt
			from rls.v_DrugNomen
			where DrugTorgCode_id = :DrugTorgCode_id;
		";
		$queryParams = ["DrugTorgCode_id" => $data["id"]];
		$result = $callObject->getFirstResultFromQuery($query, $queryParams);
		if ($result > 0) {
			throw new Exception("Удаление невозможно, так как код используется в номенклатурном справочнике");
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from rls.p_drugtorgcode_del(drugtorgcode_id := :DrugTorgCode_id);
		";
		$queryParams = ["DrugTorgCode_id" => $data["id"]];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function deleteGoodsPackCount(DrugNomen_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		$query = "
			select count(*) as cnt
			from v_WhsDocumentProcurementRequestSpec
			where GoodsUnit_id = :GoodsUnit_id
			  and DrugComplexMnn_id = :DrugComplexMnn_id
			  and WhsDocumentProcurementRequestSpec_Count = :GoodsPackCount_Count
		";
		$result = $callObject->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result("array");
			if ($result[0]["cnt"] > 0) {
				return ["success" => false, "Error_Code" => 400];
			}
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_goodspackcount_del(goodspackcount_id := :GoodsPackCount_id);
		";
		$result = $callObject->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result("array");
			if (isset($result[0]) && empty($result[0]["Error_Msg"])) {
				return ["success" => true];
			}
		}
		return ["success" => false];
	}
}