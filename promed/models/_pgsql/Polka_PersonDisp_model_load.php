<?php

class Polka_PersonDisp_model_load
{
	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $Diag_pid
	 * @return array|bool
	 */
	public static function loadDiagList(Polka_PersonDisp_model $callObject, $Diag_pid)
	{
		$where = ($Diag_pid == "null") ? "where Diag_pid is null" : "where Diag_pid = ?";
		$sql = "
			select
				Diag_Code as \"Diag_Code\",
				Diag_Name as \"Diag_Name\",
				DiagLevel_id as \"DiagLevel_id\",
				Diag_pid as \"Diag_pid\",
				Diag_id as \"Diag_id\"
			from v_Diag
			{$where}
			order by Diag_Code
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, [$Diag_pid]);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonDispEditForm(Polka_PersonDisp_model $callObject, $data)
	{
		$callObject->load->helper("MedStaffFactLink");
		$addselect = "";
		$addjoin = "";
		if ($callObject->getRegionNick() == "kz") {
			$addselect .= " ,dgl.DispGroup_id as \"DispGroup_id\"";
			$addjoin .= " left join r101.PersonDispGroupLink dgl on dgl.PersonDisp_id = PD.PersonDisp_id ";
		}
		$params = [
			"PersonDisp_id" => $data["PersonDisp_id"],
			"Lpu_id" => $data["Lpu_id"],
			"MedPersonal_id" => $data["session"]["medpersonal_id"]
		];
		if (!empty($data["session"]["medpersonal_id"])) {
			$params["MedPersonal_id"] = $data["session"]["medpersonal_id"];
			$accessType = "
				when mph_last.MedPersonal_id = :MedPersonal_id then 'edit'
			";
			$addjoin .= "
				left join lateral(
					select
						PDH_L.MedPersonal_id,
						PDH_L.LpuSection_id
					from v_PersonDispHist PDH_L
					where PDH_L.PersonDisp_id = PD.PersonDisp_id
					order by PDH_L.PersonDispHist_begDate desc
					limit 1				
				) as mph_last on true
			";

			$lpu_section_list = getLpuSectionListFromMSF($data["Lpu_id"], $data["session"]["medpersonal_id"]);
			$lpu_section_list_str = $lpu_section_list ? implode(",", $lpu_section_list) : "";
			if (!empty($lpu_section_list_str)) {
				$accessType .= "
					when mph_last.LpuSection_id in ({$lpu_section_list_str}) then 'edit'
				";
			}
		} else {
			$accessType = "
				when 1 = 0 then 'edit'
			";
		}
		$query = "
			select
				case
					{$accessType}
					else 'view'
				end as \"accessType\",
				PD.Person_id as \"Person_id\",
				PD.Server_id as \"Server_id\",
				PD.LpuSection_id as \"LpuSection_id\",
				PD.Lpu_id as \"Lpu_id\",
				PD.PersonDisp_NumCard as \"PersonDisp_NumCard\",
				to_char(PD.PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_begDate\",
				to_char(PD.PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\",
				to_char(PD.PersonDisp_NextDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_NextDate\",
				PD.MedPersonal_id as \"MedPersonal_id\",
				PD.Diag_id as \"Diag_id\",
				PD.Diag_pid as \"Diag_pid\",
				PD.Diag_nid as \"Diag_nid\",
				PD.DispOutType_id as \"DispOutType_id\",
				coalesce(PD.PersonDisp_IsDop, 1) as \"PersonDisp_IsDop\",
				to_char(PD.PersonDisp_DiagDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_DiagDate\",
				PD.DiagDetectType_id as \"DiagDetectType_id\",
				PD.DeseaseDispType_id as \"DeseaseDispType_id\",
				PD.Sickness_id as \"Sickness_id\",
				(select PregnancySpec_id from dbo.PregnancySpec ps where ps.PersonDisp_id = pd.PersonDisp_id limit 1) AS \"PregnancySpec_id\",
				PD.PersonDisp_IsTFOMS as \"PersonDisp_IsTFOMS\",
				PD.PersonDisp_IsSignedEP as \"PersonDisp_IsSignedEP\",
				PL.Label_id as \"Label_id\"
				{$addselect}
			from
				v_PersonDisp PD
				left join Sickness S on S.Sickness_id = PD.Sickness_id
				left join v_LabelObserveChart LOC on LOC.PersonDisp_id = PD.PersonDisp_id
				left join v_PersonLabel PL on PL.PersonLabel_id=LOC.PersonLabel_id
				{$addjoin}
			where PD.PersonDisp_id = :PersonDisp_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @return array|bool
	 */
	public static function loadSicknessList(Polka_PersonDisp_model $callObject)
	{
		$sql = "
			select
				1 as \"Sickness_id\",
				PrivilegeType_id as \"PrivilegeType_id\",
				Sickness_Name as \"Sickness_Name\",
				Sickness_id as \"Sickness_id\"
			from v_Sickness Sickness
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonDispGrid(Polka_PersonDisp_model $callObject, $data)
	{
		$query = "
			select
				PD.PersonDisp_id as \"PersonDisp_id\",
				PS.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				DG.Diag_Code as \"Diag_Code\",
				to_char(PD.PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_begDate\",
				to_char(PD.PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\",
				to_char(PD.PersonDisp_NextDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_NextDate\",
				LS.LpuSection_Code as \"LpuSection_Code\",
				MP.MedPersonal_Code as \"MedPersonal_Code\",
				LR.LpuRegion_Name as \"LpuRegion_Name\"
			from v_PersonDisp PD
				inner join v_PersonState PS on PS.Person_id = PD.Person_id and PS.Server_id = PD.Server_id
				left join v_Diag DG on PD.Diag_id = DG.Diag_id
				left join v_MedPersonal MP on MP.MedPersonal_id = PD.MedPersonal_id
				left join v_LpuSection LS on LS.LpuSection_id = PD.LpuSection_id
				left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuRegion LR on LR.LpuRegion_id = PD.LpuRegion_id
			where PD.Lpu_id = :Lpu_id 
			  and PS.Person_id = :Person_id 
			  and PS.Server_id = :Server_id 
		";
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"Person_id" => $data["Person_id"],
			"Server_id" => $data["Server_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDiagDispCardHistory(Polka_PersonDisp_model $callObject, $data)
	{
		$queryParams = ["PersonDisp_id" => $data["PersonDisp_id"]];
		$query = "
            select
                DDC.DiagDispCard_id as \"DiagDispCard_id\",
                to_char(DDC.PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"DiagDispCard_Date\",
                D.Diag_FullName as \"Diag_FullName\",
                D.Diag_id as \"Diag_id\"
            from
                v_DiagDispCard DDC
            	left join v_Diag D on D.Diag_id = DDC.Diag_id
            	left join v_SicknessDiag SD on SD.Diag_id = D.Diag_id
            where SD.Sickness_id = 9
              and DDC.PersonDisp_id = :PersonDisp_id
            order by DDC.PersonDisp_begDate
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDiagDispCardEditForm(Polka_PersonDisp_model $callObject, $data)
	{
		$queryParams = ["DiagDispCard_id" => $data["DiagDispCard_id"]];
		$query = "
            select
                DDC.DiagDispCard_id as \"DiagDispCard_id\",
                to_char(DDC.PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"DiagDispCard_Date\",
                DDC.PersonDisp_id as \"PersonDisp_id\",
                DDC.DiagDispCard_id as \"DiagDispCard_id\"
            from v_DiagDispCard DDC
            where DDC.DiagDispCard_id = :DiagDispCard_id
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonDispVizitList(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select
				PDV.PersonDispVizit_id as \"PersonDispVizit_id\",
				PDV.EvnVizitPL_id as \"EvnVizitPL_id\",
				PDV.PersonDispVizit_IsHomeDN as \"PersonDispVizit_IsHomeDN\",
				to_char(PDV.PersonDispVizit_NextDate, '{$callObject->dateTimeForm104}') as \"PersonDispVizit_NextDate\",
				to_char(PDV.PersonDispVizit_NextFactDate, '{$callObject->dateTimeForm104}') as \"PersonDispVizit_NextFactDate\"
			from v_PersonDispVizit PDV
			where PDV.PersonDisp_id = :PersonDisp_id
			order by PDV.PersonDispVizit_id
		";
		$sqlParams = ["PersonDisp_id" => $data["PersonDisp_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonDispVizit(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select
				PDV.PersonDispVizit_id as \"PersonDispVizit_id\",
				PDV.PersonDisp_id as \"PersonDisp_id\",
				PDV.PersonDispVizit_IsHomeDN as \"PersonDispVizit_IsHomeDN\",
				to_char(PDV.PersonDispVizit_NextDate, '{$callObject->dateTimeForm104}') as \"PersonDispVizit_NextDate\",
				to_char(PDV.PersonDispVizit_NextFactDate, '{$callObject->dateTimeForm104}') as \"PersonDispVizit_NextFactDate\"
			from v_PersonDispVizit PDV
			where PDV.PersonDispVizit_id = :PersonDispVizit_id
			limit 1
		";
		$sqlParams = ["PersonDispVizit_id" => $data["PersonDispVizit_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonDispSopDiaglist(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select
				PDSD.PersonDispSopDiag_id as \"PersonDispSopDiag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				PDDT.DopDispDiagType_Name as \"DopDispDiagType_Name\"
			from
				v_PersonDispSopDiag PDSD
				left join v_Diag D on PDSD.Diag_id  = D.Diag_id
				left join v_DopDispDiagType PDDT on PDSD.DopDispDiagType_id  = PDDT.DopDispDiagType_id
			where PDSD.PersonDisp_id = :PersonDisp_id
		";
		$sqlParams = ["PersonDisp_id" => $data["PersonDisp_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonDispSopDiag(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select
				PDSD.PersonDispSopDiag_id as \"PersonDispSopDiag_id\",
				PDSD.PersonDisp_id as \"PersonDisp_id\",
				PDSD.Diag_id as \"Diag_id\",
				PDSD.DopDispDiagType_id as \"DopDispDiagType_id\"
			from v_PersonDispSopDiag PDSD
			where PDSD.PersonDispSopDiag_id = :PersonDispSopDiag_id
		";
		$sqlParams = ["PersonDispSopDiag_id" => $data["PersonDispSopDiag_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonDispHistlist(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select
				PDSD.PersonDispHist_id as \"PersonDispHist_id\",
				PDSD.MedPersonal_id as \"MedPersonal_id\",
				PDSD.LpuSection_id as \"LpuSection_id\",
				D.Person_Fio as \"MedPersonal_Fio\",
				PDDT.LpuSection_Name as \"LpuSection_Name\",
				to_char(PersonDispHist_begDate, '{$callObject->dateTimeForm104}') as \"PersonDispHist_begDate\",
				to_char(PersonDispHist_endDate, '{$callObject->dateTimeForm104}') as \"PersonDispHist_endDate\"
			from
				v_PersonDispHist PDSD
				left join lateral(
				    select D2.Person_Fio
				    from v_MedPersonal D2
				    where D2.MedPersonal_id = PDSD.MedPersonal_id
				    limit 1
				) as D on true
				left join v_LpuSection PDDT on PDSD.LpuSection_id  = PDDT.LpuSection_id
			where PDSD.PersonDisp_id = :PersonDisp_id
		";
		$sqlParams = ["PersonDisp_id" => $data["PersonDisp_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonDispHist(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select
				PDSD.PersonDispHist_id as \"PersonDispHist_id\",
				PDSD.PersonDisp_id as \"PersonDisp_id\",
				PDSD.MedPersonal_id as \"MedPersonal_id\",
				PDSD.LpuSection_id as \"LpuSection_id\",
				to_char(PersonDispHist_begDate, '{$callObject->dateTimeForm104}') as \"PersonDispHist_begDate\",
				to_char(PersonDispHist_endDate, '{$callObject->dateTimeForm104}') as \"PersonDispHist_endDate\"
			from v_PersonDispHist PDSD
			where PDSD.PersonDispHist_id = :PersonDispHist_id
		";
		$sqlParams = ["PersonDispHist_id" => $data["PersonDispHist_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result|mixed
	 */
	public static function loadPersonDispTargetRateList(Polka_PersonDisp_model $callObject, $data)
	{
		$rateTypes = [
			53 => 130,
			54 => 90,
			55 => [102, 88],
			58 => 7,
			145 => 30,
			146 => 5,
			147 => 2
		];
		$rateTypesString = join(",", array_keys($rateTypes));
		$sql = "
			select
				RT.RateType_id as \"RateType_id\",
				RT.RateType_Name as \"RateType_Name\",
				PDFR.PersonDispFactRate_id as \"PersonDispFactRate_id\",
				PDFR.Rate_id as \"Rate_id\",
				PDTR.RateValue as \"TargetRate_Value\",
				PDFR.RateValue as \"FactRate_Value\",
				to_char(PDFR.PersonDispFactRate_setDT, '{$callObject->dateTimeForm104}') as \"FactRate_setDT\",
				PS.Sex_id as \"Sex_id\"
			from
				v_RateType RT
				inner join RateValueType RVT on RVT.RateValueType_id = RT.RateValueType_id
				left join lateral(
					select
						case
							when RVT.RateValueType_SysNick = 'int' then R.Rate_ValueInt::varchar
							when RVT.RateValueType_SysNick = 'float' then R.Rate_ValueFloat::decimal(16,3)::varchar
							when RVT.RateValueType_SysNick = 'string' then R.Rate_ValueStr
						end as RateValue
					from
						v_Rate R
						inner join v_PersonDispTargetRate PDTR on PDTR.Rate_did = R.Rate_id
					where R.RateType_id  = RT.RateType_id
					  and PDTR.PersonDisp_id = :PersonDisp_id
					order by PDTR.PersonDispTargetRate_setDT desc
					limit 1
				) as PDTR on true
				left join lateral(
					select
						case
							when RVT.RateValueType_SysNick = 'int' then R.Rate_ValueInt::varchar
							when RVT.RateValueType_SysNick = 'float' then R.Rate_ValueFloat::decimal(16,3)::varchar
							when RVT.RateValueType_SysNick = 'string' then R.Rate_ValueStr
						end as RateValue, 
						PDFR.PersonDispFactRate_setDT,
						PDFR.PersonDispFactRate_id,
						PDFR.Rate_id
					from
						v_Rate R
						inner join v_PersonDispFactRate PDFR on PDFR.Rate_id = R.Rate_id
					where R.RateType_id  = RT.RateType_id
					  and PDFR.PersonDisp_id = :PersonDisp_id
					order by
						PDFR.PersonDispFactRate_setDT desc,
					    PDFR.PersonDispFactRate_id desc
					limit 1
				) as PDFR on true
				left join lateral(
					select PS.Sex_id
					from
						v_PersonSex PS
						inner join v_PersonDisp PD on PD.Person_id = PS.Person_id
					where PD.PersonDisp_id = :PersonDisp_id
					limit 1
				) as PS on true
			where RT.RateType_id in ({$rateTypesString})
		";
		/**@var CI_DB_result $result */
		$sqlParams = ["PersonDisp_id" => $data["PersonDisp_id"]];
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		foreach ($result as &$r) {
			if (empty($r["TargetRate_Value"])) {
				$r["TargetRate_Value"] = ($r["RateType_id"] == 55) ? $rateTypes[$r["RateType_id"]][($r["Sex_id"] == 2)] : $rateTypes[$r["RateType_id"]];
			}
		}
		return $result;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function loadPersonDispTargetRate(Polka_PersonDisp_model $callObject, $data)
	{
		$rateTypes = [
			53 => 130,
			54 => 90,
			55 => [102, 88],
			58 => 7,
			145 => 30,
			146 => 5,
			147 => 2
		];
		$sql = "
			select
				RT.RateType_id as \"RateType_id\",
				RT.RateType_Name as \"RateType_Name\",
				RVT.RateValueType_SysNick as \"RateValueType_SysNick\",
				PDTR.RateValue as \"TargetRate_Value\",
				PS.Sex_id as \"Sex_id\"
			from
				v_RateType RT
				inner join RateValueType RVT on RVT.RateValueType_id = RT.RateValueType_id
				left join lateral(
					select
						case
							when RVT.RateValueType_SysNick = 'int' then R.Rate_ValueInt::varchar
							when RVT.RateValueType_SysNick = 'float' then R.Rate_ValueFloat::decimal(16,3)::varchar
							when RVT.RateValueType_SysNick = 'string' then R.Rate_ValueStr
						end as RateValue
					from
						v_Rate R
						inner join v_PersonDispTargetRate PDTR on PDTR.Rate_did = R.Rate_id
					where R.RateType_id  = RT.RateType_id
					  and PDTR.PersonDisp_id = :PersonDisp_id
					order by PDTR.PersonDispTargetRate_setDT desc
					limit 1
				) as PDTR on true
				left join lateral(
					select PS.Sex_id
					from
						v_PersonSex PS
						inner join v_PersonDisp PD on PD.Person_id = PS.Person_id
					where PD.PersonDisp_id = :PersonDisp_id
					limit 1
				) as PS on true
			where RT.RateType_id = :RateType_id
			limit 1
		";
		$sqlParams = [
			'PersonDisp_id' => $data['PersonDisp_id'],
			'RateType_id' => $data['RateType_id']
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		foreach ($result as &$r) {
			if (empty($r["TargetRate_Value"])) {
				$r["TargetRate_Value"] = ($r["RateType_id"] == 55) ? $rateTypes[$r["RateType_id"]][($r["Sex_id"] == 2)] : $rateTypes[$r["RateType_id"]];
			}
		}
		return $result;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonDispFactRateList(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select 
				PersonDispFactRate_id as \"PersonDispFactRate_id\",
				1 as \"RecordStatus_Code\",
				R.Rate_id as \"Rate_id\",
				case
					when RVT.RateValueType_SysNick = 'int' then R.Rate_ValueInt::varchar
					when RVT.RateValueType_SysNick = 'float' then R.Rate_ValueFloat::decimal(16,3)::varchar
					when RVT.RateValueType_SysNick = 'string' then R.Rate_ValueStr
				end as \"PersonDispFactRate_Value\", 
				to_char(PDFR.PersonDispFactRate_setDT, '{$callObject->dateTimeForm104}') as \"PersonDispFactRate_setDT\"
			from
				v_Rate R
				inner join v_PersonDispFactRate PDFR on PDFR.Rate_id = R.Rate_id
				inner join v_RateType RT on RT.RateType_id = R.RateType_id
				inner join RateValueType RVT on RVT.RateValueType_id = RT.RateValueType_id
			where R.RateType_id = :RateType_id
			  and PDFR.PersonDisp_id = :PersonDisp_id
			order by
				PDFR.PersonDispFactRate_setDT,
			    PersonDispFactRate_id
		";
		$sqlParams = [
			"PersonDisp_id" => $data["PersonDisp_id"],
			"RateType_id" => $data["RateType_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadPersonDispList(Polka_PersonDisp_model $callObject, $data)
	{
		if (!empty($data["PersonDisp_id"])) {
			$filter = "pd.PersonDisp_id = :PersonDisp_id";
		} else {
			$filter = "pd.Person_id = :Person_id and pd.Lpu_id = :Lpu_id";
			if (!empty($data["onDate"])) {
				$filter .= "
					and pd.PersonDisp_begDate <= :onDate
				";
				if (!in_array(getRegionNick(), ['krasnoyarsk','vologda'])) {
					$filter .= " and coalesce(pd.PersonDisp_endDate, :onDate) >= :onDate";
				}
			}
		}
		$query = "
			select
				pd.PersonDisp_id as \"PersonDisp_id\",
				coalesce(to_char(pd.PersonDisp_begDate, '{$callObject->dateTimeForm104}'), '...')||' - '||
				coalesce(to_char(pd.PersonDisp_endDate, '{$callObject->dateTimeForm104}'),'...') ||' '||
				coalesce(d.Diag_Code||' ', '')||
				coalesce(d.Diag_Name||' ', '')
				as \"PersonDisp_Name\"
			from
				v_PersonDisp pd
				left join v_Diag d on d.Diag_id = pd.Diag_id
			where {$filter}
			order by pd.PersonDisp_begDate desc
		";
		return $callObject->queryResult($query, $data);
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadPersonDispPanel(Polka_PersonDisp_model $callObject, $data)
	{
		$filters = ["PD.Person_id = :Person_id"];
		$select = "";
		if (!empty($data["person_in"])) {
			$filters = ["PD.Person_id in ({$data["person_in"]})"];
			$select = " ,PD.Person_id as \"Person_id\" ";
		}
		if (!haveARMType("spec_mz")) {
			$filters = array_merge(
				getAccessRightsDiagFilter("D.Diag_Code", true),
				getAccessRightsLpuFilter("PD.Lpu_id", true),
				getAccessRightsLpuBuildingFilter("LS.LpuBuilding_id", true),
				$filters
			);
		}
		$queryParams = ["Person_id" => $data["Person_id"]];
		$signFilter = "";
		if (!isLpuAdmin() && !empty($data["session"]["medpersonal_id"])) {
			$signFilter = " and PDH.MedPersonal_id = :MedPersonal_id";
			$queryParams["MedPersonal_id"] = $data["session"]["medpersonal_id"];
		} else if (isLpuAdmin() && !empty($data["Lpu_id"])) {
			$signFilter = " and PDHLS.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		$signAccess = (!empty($signFilter))
			?"
				case when exists(
					select PDH.PersonDispHist_id
					from
						v_PersonDispHist PDH
						left join v_LpuSection PDHLS on PDHLS.LpuSection_id = PDH.LpuSection_id
					where PDH.PersonDisp_id = PD.PersonDisp_id
					  and coalesce(PDH.PersonDispHist_begDate, tzGetDate()) <= tzGetDate()
					  and coalesce(PDH.PersonDispHist_endDate, tzGetDate()) >= tzGetDate()
					  {$signFilter}
					limit 1	
				) then 'edit' else 'view' end as \"signAccess\"
			"
			:"'view' as \"signAccess\"";
		$whereString = implode(" and ", $filters);
		$query = "
			select
				PD.PersonDisp_id as \"PersonDisp_id\",
				to_char(PD.PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_setDate\",
				to_char(PD.PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\",
				PD.PersonDisp_IsSignedEP as \"PersonDisp_IsSignedEP\",
				L.Lpu_Nick as \"Lpu_Nick\",
				D.Diag_Name as \"Diag_Name\",
				D.Diag_Code as \"Diag_Code\",
				LS.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
				,{$signAccess}
				{$select}
			from
				v_PersonDisp PD
				left join v_Diag D on D.Diag_id = PD.Diag_id
				left join v_Lpu L on L.Lpu_id = PD.Lpu_id
				left join v_LpuSection LS on LS.LpuSection_id = PD.LpuSection_id
			where {$whereString}
		";
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function loadPersonLabelList(Polka_PersonDisp_model $callObject, $data)
	{
		$select = ["pers_labels.PersLabels as \"PersLabels\""];
		$joinList = [
			"
				left join lateral(
					select string_agg(L_ls.Label_Name, '|' order by PL_ls.Label_id) as PersLabels
					from
						v_PersonLabel PL_ls
						inner join v_Label L_ls on L_ls.Label_id=PL_ls.Label_id
					where PL_ls.Person_id = PS.Person_id
					  and PL_ls.Label_id not in (1, 7)
					  and PL_ls.PersonLabel_disDate is null
				) as pers_labels on true
			"
		];
		$filter1 = ["PL.Label_id = :Label_id"];
		$filter2 = ["PS.Lpu_id = :Lpu_id"];
		$params = [
			"Label_id" => (!empty($data["Label_id"])) ? $data["Label_id"] : 1,
			"Lpu_id" => $data["MonitorLpu_id"]
		];
		if (!empty($data["DispOutType_id"])) {
			$filter2[] = "DOT.DispOutType_id = :DispOutType_id";
			$params["DispOutType_id"] = $data["DispOutType_id"];
		}
		if (!empty($data["outBegDate"]) && !empty($data["outEndDate"])) {
			$filter1[] = "((LOC.LabelObserveChart_endDate > :outBegDate and LOC.LabelObserveChart_endDate < :outEndDate) or LOC.LabelObserveChart_endDate is null)";
			$params["outBegDate"] = $data["outBegDate"];
			$params["outEndDate"] = $data["outEndDate"];
		}
		if (!empty($data["Diags"]) and count($data["Diags"]) > 0) {
			$diags = [];
			$alldiags = false;
			foreach ($data["Diags"] as $diag) {
				if (is_numeric($diag)) {
					$diags[] = $diag;
				}
				if ($diag == -1) {
					$alldiags = true;
				}
			}
			if ($alldiags) {
				$filter1[] = "PL.Diag_id in (select LD.Diag_id from LabelDiag LD where LD.Label_id=1 )";
			} else {
				if (count($diags) > 0) {
					$diags = implode(", ", $diags);
					$filter1[] = "PL.Diag_id in ({$diags})";
				}
			}
		}
		if (!empty($data["LabelInviteStatus_id"])) {
			$filter2[] = "LI.LabelInviteStatus_id = :LabelInviteStatus_id";
			$params["LabelInviteStatus_id"] = $data["LabelInviteStatus_id"];
		}
		if (!empty($data["Person_id"])) {
			$filter2[] = "PS.Person_id = :Person_id";
			$params["Person_id"] = $data["Person_id"];
		}
		switch ($data["status"]) {
			case "new":
				//Новые
				//	У человека есть запись о наличии метки (запись открыта)
				//	У человека нет связанной с меткой карты наблюдений в МО Пользователя (как открытой, так и закрытой)
				$filter1[] = "LOC.LabelObserveChart_id is null and PL.PersonLabel_disDate is null";
				break;
			case "on":
				//Включенные
				//	У человека есть открытая карта наблюдений в МО Пользователя
				$filter1[] = "LOC.LabelObserveChart_id is not null and LOC.LabelObserveChart_endDate is null";
				break;
			case "off":
				//Выбывшие
				//	У человека есть карта наблюдений в МО Пользователя с заполненной датой закрытия
				$filter1[] = "LOC.LabelObserveChart_id is not null and LOC.LabelObserveChart_endDate is not null";
				break;
			case "all":
				//Все пациенты
				//	У человека есть запись о наличии метки (запись открыта)
				//	У человека есть карта наблюдений в МО Пользователя (как открытая, так и закрытая)
				$filter1[] = "PL.PersonLabel_disDate is null and LOC.LabelObserveChart_id is not null";
				break;
		}
		$filter = array_merge($filter1, $filter2);
		$selectString = (count($select) > 0) ? "," . implode(",", $select) : "";
		$joinString = (count($joinList) > 0) ? implode(" ", $joinList) : "";
		$whereString = (count($filter) > 0) ? implode(" and ", $filter) : "";
		$query = "
			select 
				-- select
				PL.Person_id as \"Person_id\",
				PL.PersonLabel_id as \"PersonLabel_id\",
				PS.Server_id as \"Server_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Sex_id as \"Sex_id\",
				LI.LabelInvite_id as \"LabelInvite_id\",
				LI.LabelInviteStatus_id as \"LabelInviteStatus_id\",
				LI.LabelInvite_RefuseCause as \"LabelInvite_RefuseCause\",
				LI.FeedbackMethod_id as \"LabelInviteFeedbackMethod_id\",
				to_char(LI.LabelInvite_updDT, '{$callObject->dateTimeFormUnixDate}') as \"LabelInviteStatus_Date\",
				RTRIM(LTRIM(coalesce(PS.Person_Phone, ''))) as \"Person_Phone\",
				RTRIM(LTRIM(coalesce(LOC.LabelObserveChart_Phone, ''))) as \"Chart_Phone\",
				LOC.LabelObserveChart_Email as \"Chart_Email\",
				RTRIM(LTRIM(coalesce(UPPER(PS.Person_SurName),''))) as \"Person_SurName\",
				RTRIM(LTRIM(coalesce(UPPER(PS.Person_FirName),''))) as \"Person_FirName\",
				RTRIM(LTRIM(coalesce(UPPER(PS.Person_SecName),''))) as \"Person_SecName\",
				date_part('day', coalesce(PS.Person_deadDT, tzGetDate()) - PS.Person_BirthDay) as \"PersonAge\",
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeFormUnixDate}') as \"Person_BirthDay\",
				to_char(PS.Person_deadDT, '{$callObject->dateTimeFormUnixDate}') as \"Person_DeadDT\",
				L.Label_id as \"Label_id\",
				PM.PersonModel_id as \"PersonModel_id\",
				PM.PersonModel_Name as \"PersonModel_Name\",
				LOC.LabelObserveChart_id as \"Chart_id\",
				to_char(LOC.LabelObserveChart_begDate, '{$callObject->dateTimeForm104}') as \"Chart_begDate\",
				to_char(LOC.LabelObserveChart_endDate, '{$callObject->dateTimeForm104}') as \"Chart_endDate\",
				LOC.LabelObserveChart_IsAutoClose as \"Chart_IsAutoClose\",
				LOC.PersonDisp_id as \"ChartDisp_id\",
				DOT.DispOutType_Name as \"DispOutType_Name\",
				DOT.DispOutType_Code as \"DispOutType_Code\",
				to_char(ci.LabelObserveChartInfo_ObserveDate, '{$callObject->dateTimeFormUnixDate}') as \"lastObserveDate\",
				CM1.LabelObserveChartMeasure_Value as \"Rate1_Value\",
				CM2.LabelObserveChartMeasure_Value as \"Rate2_Value\",
				CM4.LabelObserveChartMeasure_Value as \"Rate4_Value\",
				CR1.LabelObserveChartRate_Min as \"Rate1_Min\",
				CR1.LabelObserveChartRate_Max as \"Rate1_Max\",
				CR2.LabelObserveChartRate_Min as \"Rate2_Min\",
				CR2.LabelObserveChartRate_Max as \"Rate2_Max\",
				CR4.LabelObserveChartRate_Min as \"Rate4_Min\",
				CR4.LabelObserveChartRate_Max as \"Rate4_Max\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				to_char(PD.PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_begDate\",
				to_char(PD.PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\",
				PDCh.PersonDisp_id as \"ChartPersonDisp_id\",
				PD.PersonDisp_id as \"PersonDisp_id\",
				case 
					when LOC.LabelObserveChart_id is null then 'new'
					when LOC.LabelObserveChart_endDate is not null then 'off'
					when LOC.LabelObserveChart_id is not null then 'on'
					else 'undefined'
				END as \"StatusNick\",
				pcard.Lpu_id as \"Lpu_id\",
				coalesce(pcard_lpu.Lpu_Nick, '') as \"Lpu_Nick\",
				pcard.LpuRegion_Name as \"AttachNum\",
				coalesce(pcard_lpu.Lpu_Nick, '')||right('          ' || coalesce(pcard.LpuRegion_Name,''), 10) as \"Attach\",
				pcard.PersonCard_begDate as \"AttachDate\",
				PL.PersonLabel_disDate as \"PersonLabel_disDate\",
				FM.FeedbackMethod_id as \"FeedbackMethod_id\",
				FM.FeedbackMethod_Name as \"FeedbackMethod_Name\",
				case 
					when LOC.LabelObserveChart_id is null  then 10 + coalesce(LI.LabelInviteStatus_id, 0)
					when LOC.LabelObserveChart_endDate is not null then 30
					when LOC.LabelObserveChart_id is not null then 20 + coalesce(LOC.PersonModel_id, 0)
				end as \"Status\",
				coalesce(PR.PersonRefuse_IsRefuse,1) - 1 as \"Person_IsRefuse\",
				case  when PersonPrivilegeFed.Person_id is not null then 1 else 0 end as \"Person_IsFedLgot\",
				case
					when regl.Lpu_id is null then 0
					when regl.Lpu_id = :Lpu_id then 1
					else 0
				END as \"Person_IsRegLgot\"
				{$selectString}
				-- end select
			from
				--from
				v_PersonLabel PL
				inner join v_PersonState PS on PS.Person_id = PL.Person_id
				inner join v_Label L on L.Label_id = PL.Label_id
				left join lateral(
					select
						LI1.LabelInvite_id, 
						LI1.LabelInviteStatus_id, 
						LI1.LabelInvite_updDT, 
						LI1.LabelInvite_RefuseCause,
						LI1.FeedbackMethod_id
					from
						v_LabelInvite LI1
						inner join MedStaffFactcache MSF on MSF.MedStaffFact_id = LI1.MedStaffFact_id
					where LI1.PersonLabel_id = PL.PersonLabel_id 
					  and LI1.LabelInviteStatus_id is not null
					  and MSF.Lpu_id = :Lpu_id
					order by LI1.LabelInvite_id desc
					limit 1
				) as LI on true
				left join lateral(
					select
						LOC3.PersonModel_id,
						LOC3.DispOutType_id,
						LOC3.LabelObserveChart_id,
						LOC3.PersonDisp_id,
						LOC3.LabelObserveChart_begDate,
						LOC3.LabelObserveChart_endDate,
						LOC3.LabelObserveChart_IsAutoClose,
						LOC3.LabelObserveChart_Phone,
						LOC3.LabelObserveChart_Email,
						LOC3.FeedbackMethod_id
					from
						v_LabelObserveChart LOC3 
						inner join MedStaffFactcache MSF on MSF.MedStaffFact_id = LOC3.MedStaffFact_id
					where LOC3.PersonLabel_id = PL.PersonLabel_id
					  and MSF.Lpu_id = :Lpu_id
					order by
						coalesce(LOC3.LabelObserveChart_endDate, tzGetDate()) DESC,
					    LabelObserveChart_id DESC
					limit 1
				) as LOC on true
				left join v_PersonModel PM on PM.PersonModel_id = LOC.PersonModel_id
				left join v_DispOutType DOT on DOT.DispOutType_id = LOC.DispOutType_id
				left join v_Diag D on D.Diag_id = PL.Diag_id
				left join FeedbackMethod FM on FM.FeedbackMethod_id = LOC.FeedbackMethod_id
				left join v_PersonRefuse PR ON PR.Person_id = PL.Person_id and PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = date_part('year', tzGetDate())
				left join lateral(
					select
						CI.LabelObserveChartInfo_id,
						CI.LabelObserveChartInfo_ObserveDate 
					from
						v_LabelObserveChartInfo CI
						inner join v_LabelObserveChartMeasure CM on CM.LabelObserveChartInfo_id = CI.LabelObserveChartInfo_id
					where CI.LabelObserveChart_id = LOC.LabelObserveChart_id
					  and CI.LabelObserveChartInfo_ObserveDate >= DATEADD('DAY', -1, getdate())
					order by
						CI.LabelObserveChartInfo_ObserveDate desc,
					    CI.TimeOfDay_id desc,
					    CI.LabelObserveChartInfo_id desc
					limit 1
				) CI on true
				left join v_LabelObserveChartRate CR1 on CR1.LabelObserveChart_id=LOC.LabelObserveChart_id and CR1.LabelRate_id = 1
				left join v_LabelObserveChartMeasure CM1 on CM1.LabelObserveChartRate_id = CR1.LabelObserveChartRate_id and CM1.LabelObserveChartInfo_id = CI.LabelObserveChartInfo_id
				left join v_LabelObserveChartRate CR2 on CR2.LabelObserveChart_id=LOC.LabelObserveChart_id and CR2.LabelRate_id = 2
				left join v_LabelObserveChartMeasure CM2 on CM2.LabelObserveChartRate_id = CR2.LabelObserveChartRate_id and CM2.LabelObserveChartInfo_id = CI.LabelObserveChartInfo_id
				left join v_LabelObserveChartRate CR4 on CR4.LabelObserveChart_id=LOC.LabelObserveChart_id and CR4.LabelRate_id = 4
				left join v_LabelObserveChartMeasure CM4 on CM4.LabelObserveChartRate_id = CR4.LabelObserveChartRate_id and CM4.LabelObserveChartInfo_id = CI.LabelObserveChartInfo_id
				left join lateral(
				    select
						PP.Person_id,
				        PP.PrivilegeType_id,
				        PT.PrivilegeType_Name
					from
						PersonPrivilege PP
						inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
					where PT.ReceptFinance_id = 1
					  and PP.PersonPrivilege_begDate <= tzGetDate() 
					  and coalesce(PP.PersonPrivilege_endDate, tzGetDate()) >= tzGetDate()::date
					  and PP.Person_id = PL.Person_id
					  and PersonPrivilege_deleted = 1
					limit 1
				) as PersonPrivilegeFed on true
				left join lateral(
					select Lpu_id
					from
						PersonPrivilege t1
						inner join PrivilegeType t2 on t2.PrivilegeType_id = t1.PrivilegeType_id
					where t1.Person_id = PL.Person_id
					  and t2.ReceptFinance_id = 2
					  and t1.PersonPrivilege_begDate <= tzGetDate()
					  and coalesce(t1.PersonPrivilege_endDate, tzGetDate()) >= tzGetDate()::date
					  and PersonPrivilege_deleted = 1
					order by (case when t1.Lpu_id = :Lpu_id then 1 else 0 end) desc
					limit 1
				) as regl on true
				left join v_PersonCard pcard on pcard.Person_id = PL.Person_id and LpuAttachType_id = 1
				left join v_Lpu pcard_lpu on pcard_lpu.lpu_id = pcard.lpu_id
				left join v_PersonDisp PDCh on PDCh.PersonDisp_id = LOC.PersonDisp_id and PDCh.PersonDisp_endDate is null and PDCh.Lpu_id=:Lpu_id
				left join lateral(
					select
						PD6.PersonDisp_id,
					    PD6.PersonDisp_begDate,
					    PD6.PersonDisp_endDate
					from v_PersonDisp PD6
					where PD6.Person_id = PL.Person_id
					  and PD6.Diag_id=PL.Diag_id
					  and PD6.Lpu_id=:Lpu_id
					  and PD6.PersonDisp_endDate is null
					order by PD6.PersonDisp_id desc
					limit 1
				) as PD on true
				{$joinString}
				--end from
			where
				--where
				{$whereString}
				--end where
			order by
				-- order by
				PS.Person_SurName, PS.Person_FirName, PS.Person_SecName
				-- end order by
		";
		if ($data["paging"]) {
			$result = $callObject->getPagingResponse($query, $params, $data["start"], $data["limit"], true, true);
			return $result;
		} else {
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $params);
			if (!is_object($result)) {
				return false;
			}
			return ["data" => $result->result_array()];
		}
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadLabelObserveChartMeasure(Polka_PersonDisp_model $callObject, $data)
	{
		/**
		 * @var CI_DB_result $info
		 * @var CI_DB_result $measures
		 * @var CI_DB_result $minimaxresult
		 * @var CI_DB_result $result
		 */
		$params = [
			'Person_id' => $data['Person_id']
		];

		$minimax = array(
			'minObserveDate' => 0,
			'maxObserveDate' => 0
		);

		if (!empty($data['Chart_id'])) {
			$params['Chart_id']	= $data['Chart_id'];

			//количество замеров для индикатора вкладки "показания"
			$totalcount = $callObject->getFirstResultFromQuery("
			select count(*)
			from v_LabelObserveChartInfo CI
			where LabelObserveChart_id = :Chart_id
			", $params);
	
			//границы временного промежутка всех измерений в карте наблюдения
			$minimax = $callObject->getFirstRowFromQuery("
			select
				to_char(min(CI.LabelObserveChartInfo_ObserveDate), '{$callObject->dateTimeFormUnixDate}') as \"minObserveDate\",
				to_char(max(CI.LabelObserveChartInfo_ObserveDate), '{$callObject->dateTimeFormUnixDate}') as \"maxObserveDate\"
			from v_LabelObserveChartInfo CI
			where LabelObserveChart_id = :Chart_id
			", $params);
			
		} else {

			//количество замеров для индикатора вкладки "показания"
			$totalcount = $callObject->getFirstResultFromQuery("
				select count(LOCI.LabelObserveChartInfo_id) 
				from v_LabelObserveChartInfo LOCI 
				left join v_LabelObserveChartMeasure locm on locm.LabelObserveChartInfo_id = LOCI.LabelObserveChartInfo_id
				left join v_LabelObserveChartRate locr on locr.LabelObserveChartRate_id = locm.LabelObserveChartRate_id
				where locr.Person_id = :Person_id
			", $params);
		}

		//период = 1 неделя
		$period = "day";
		$i = $data["start"];
		$k = 7;
		
		if ($data["limit"] == 2) {
			//период = 2 недели
			$k = 14;
		} else if ($data["limit"] == 3) {
			//период = месяц
			$k = 30;
			//~ $period = "month";
		}
		
		$rates = $callObject->queryResult("
			select 
				CR.LabelObserveChartRate_id as \"ChartRate_id\",
				CR.LabelObserveChartRate_Min as \"ChartRate_Min\",
				CR.LabelObserveChartRate_Max as \"ChartRate_Max\",
				CR.LabelObserveChartSource_id as \"LabelObserveChartSource_id\",
				CR.LabelObserveChartRate_IsShowValue as \"LabelObserveChartRate_IsShowValue\",
				CR.LabelObserveChartRate_IsShowEMK as \"LabelObserveChartRate_IsShowEMK\",
				CR.LabelRate_id as \"LabelRate_id\",
				RT.RateType_id as \"RateType_id\",
				RT.RateType_SysNick as \"RateType_SysNick\"
			from
				v_LabelObserveChartRate CR
				left join v_LabelRate LR on LR.LabelRate_id = CR.LabelRate_id
				left join v_RateType RT on RT.RateType_id = coalesce(LR.RateType_id,CR.RateType_id)
			WHERE Person_id = :Person_id
		", $params);

		$filter = "
			and date_part('{$period}', (select maxdate from mv) - CI.LabelObserveChartInfo_ObserveDate) >= " . ($i * $k) . " 
			and date_part('{$period}', (select maxdate from mv) - CI.LabelObserveChartInfo_ObserveDate) < " . (($i + 1) * $k);

		$chartInfo = $callObject->queryResult("
			with mv as (
				select max(LOCI.LabelObserveChartInfo_ObserveDate) as maxdate
				from LabelObserveChartInfo LOCI 
				left join v_LabelObserveChartMeasure locm on locm.LabelObserveChartInfo_id = LOCI.LabelObserveChartInfo_id
				left join v_LabelObserveChartRate locr on locr.LabelObserveChartRate_id = locm.LabelObserveChartRate_id
				where locr.Person_id = :Person_id 
			)
			select distinct
				CI.LabelObserveChartInfo_id as \"ChartInfo_id\",
				to_char(CI.LabelObserveChartInfo_ObserveDate, 'YYYY-MM-DD HH24:MI') as \"ObserveDate\",
				CI.TimeOfDay_id as \"TimeOfDay_id\",
				CI.LabelObserveChartSource_id,
				locs.LabelObserveChartSource_Name,
				CI.LabelObserveChartInfo_ObserveDate as \"LabelObserveChartInfo_ObserveDate\",
				CI.LabelObserveChartInfo_Complaint as \"Complaint\",
				CI.FeedbackMethod_id as \"FeedbackMethod_id\"
			from LabelObserveChartRate locr
			inner join v_LabelObserveChartMeasure locm on locm.LabelObserveChartRate_id = locr.LabelObserveChartRate_id
			inner join v_LabelObserveChartInfo CI on CI.LabelObserveChartInfo_id = locm.LabelObserveChartInfo_id
			left join v_LabelObserveChartSource locs on locs.LabelObserveChartSource_id = CI.LabelObserveChartSource_id
			WHERE (1=1)
				and locr.Person_id = :Person_id
				--and coalesce(CI.LabelObserveChartSource_id, 1) = 1
				--and (CI.FeedbackMethod_id is null or CI.FeedbackMethod_id < 4) 
				--and coalesce(locr.LabelObserveChartRate_IsShowEMK, 2) = 2
				{$filter}
			order by
				CI.LabelObserveChartInfo_ObserveDate DESC,
				CI.TimeOfDay_id ASC
		", $params);

		$measures = array();
		if (!empty($chartInfo)) {
			
			$ChartInfo_list = implode(',',array_column($chartInfo,'ChartInfo_id'));

			$measures = $callObject->queryResult("
				select 
					CM.LabelObserveChartMeasure_id as \"Measure_id\",
					CM.LabelObserveChartMeasure_Value as \"Value\",
					CM.LabelObserveChartInfo_id as \"ChartInfo_id\",
					CR.RateType_id as \"RateType_id\"
				from
					LabelObserveChartMeasure CM
					inner join LabelObserveChartRate CR on CR.LabelObserveChartRate_id = CM.LabelObserveChartRate_id
					inner join LabelRate LR on LR.LabelRate_id = CR.LabelRate_id
				where CM.LabelObserveChartInfo_id in ({$ChartInfo_list})
			", $params);
		}
		
		return [
			"info" => $chartInfo, //замеры
			"measures" => $measures, //отдельно данные по показателям к ним
			"rates" => $rates, //нормы
			"totalCount" => $totalcount, //количество замеров
			"minimax" => $minimax //временной промежуток на все замеры
		];
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadLabelInviteHistory(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select
				eventDate as \"eventDate\",
				eventType as \"eventType\",
				statusId as \"statusId\"
			from (
				select
					to_char(LIH.LabelInviteHistory_setDT, '{$callObject->dateTimeFormUnixDate}') AS eventDate,
					0 AS eventType,
					LIH.LabelInviteStatus_id AS statusId
				from
					PersonLabel PL
					inner join LabelInvite LI on LI.PersonLabel_id=PL.PersonLabel_id
					inner join LabelInviteHistory LIH on LIH.LabelInvite_id = LI.LabelInvite_id
				where PL.PersonLabel_id = :PersonLabel_id
				union all
				select
					to_char(LOC.LabelObserveChart_begDate, '{$callObject->dateTimeFormUnixDate}') AS eventDate,
					1 AS eventType,
					0 AS statusId
				from
					v_PersonLabel PL
					inner join LabelObserveChart LOC on LOC.PersonLabel_id = PL.PersonLabel_id
				where PL.PersonLabel_id = :PersonLabel_id
				  and LOC.LabelObserveChart_begDate is not null
				union all
				select
					to_char(LOC.LabelObserveChart_endDate, '{$callObject->dateTimeFormUnixDate}') AS eventDate,
					2 AS eventType,
					0 AS statusId
				from
					v_PersonLabel PL
					inner join LabelObserveChart LOC on LOC.PersonLabel_id = PL.PersonLabel_id
				where PL.PersonLabel_id = :PersonLabel_id
				  and LOC.LabelObserveChart_endDate is not null
			) it
		";
		$sqlParams = ["PersonLabel_id" => $data["PersonLabel_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function loadLabelMessages(Polka_PersonDisp_model $callObject, $data)
	{
		$params = ["Chart_id" => $data["Chart_id"]];
		$query = "
			select
				-- select
				to_char(LM.LabelMessage_sendDate, '{$callObject->dateTimeFormUnixDate}') as \"MessageDate\",
				LM.LabelMessage_Text as \"LabelMessage_Text\",
				LM.LabelMessageType_id as \"LabelMessageType_id\",
				FM.FeedbackMethod_Name as \"FeedbackMethod_Name\"
				-- end select
			from
				-- from
				v_LabelMessage LM
				inner join FeedbackMethod FM on FM.FeedbackMethod_id = LM.FeedbackMethod_id
				-- end from
			where
				-- where
				LM.LabelObserveChart_id = :Chart_id
				--end where
			order by
				-- order by
				LM.LabelMessage_sendDate DESC
				-- end order by
		";
		$messages = $callObject->getPagingResponse($query, $params, $data["start"], $data["limit"], true, true);
		return ["messages" => $messages];
	}
}