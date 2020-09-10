<?php

class EvnLabSample_model_load
{
	/**
	 * Получение списка референсных значений пробы
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function loadRefValues(EvnLabSample_model $callObject, $data)
	{
		$resp = [];
		$callObject->load->model("AnalyzerTestRefValues_model");
		$UslugaComplex_ids = json_decode($data["UslugaComplex_ids"], true);
		if (!array_key_exists("EvnPrescr_id", $data)) {
			$res = $callObject->common->GET("EvnPrescr/PrescrByDirection", $data, "single");
			if (!$callObject->isSuccessful($res)) {
				return $res;
			}
			$data["EvnPrescr_id"] = $res["EvnPrescr_id"];
		}
		if (empty($data["EvnPrescr_id"])) {
			$data["EvnPrescr_id"] = null;
		}
		if (!array_key_exists("PersonData", $data)) {
			$person = $callObject->common->GET("Person/PersonDataForRefValues", $data);
			if (!$callObject->isSuccessful($person)) {
				return $person;
			}
			$person = $person["data"][0];
		} else {
			$person = $data["PersonData"];
		}
		if (is_array($UslugaComplex_ids) && count($UslugaComplex_ids) > 0) {
			// одним запросом получаем референсные значения для всех услуг
			$refvalues = [];
			$query = "
				select
					ucms_at.UslugaComplex_id as \"UslugaComplex_id\",
					at.AnalyzerTestType_id as \"AnalyzerTestType_id\",
					atrv.AnalyzerTestRefValues_id as \"AnalyzerTestRefValues_id\",
					rv.RefValues_id as \"RefValues_id\",
					a.Analyzer_id as \"Analyzer_id\",
					rv.Unit_id as \"Unit_id\",
					rv.RefValues_Name||COALESCE(' ('||a.Analyzer_Name||')', '') as \"RefValues_Name\",
					'' as \"UslugaTest_ResultQualitativeNorms\",
					case when att.AnalyzerTestType_Code in ('1','3') then rv.RefValues_LowerLimit::varchar else '' end as \"UslugaTest_ResultLower\",
					case when att.AnalyzerTestType_Code in ('1','3') then rv.RefValues_UpperLimit::varchar else '' end as \"UslugaTest_ResultUpper\",
					case when att.AnalyzerTestType_Code in ('1','3') then rv.RefValues_BotCritValue::varchar else '' end as \"UslugaTest_ResultLowerCrit\",
					case when att.AnalyzerTestType_Code in ('1','3') then rv.RefValues_TopCritValue::varchar else '' end as \"UslugaTest_ResultUpperCrit\",
					u.Unit_Name as \"UslugaTest_ResultUnit\",
					rv.RefValues_Description as \"UslugaTest_Comment\"
				from
					lis.v_AnalyzerTest at
					inner join v_UslugaComplexMedService ucms_at on ucms_at.UslugaComplexMedService_id = at.UslugaComplexMedService_id
					inner join lis.v_AnalyzerTestType att on att.AnalyzerTestType_id = at.AnalyzerTestType_id
					inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
					left join lis.v_AnalyzerTestRefValues atrv on atrv.AnalyzerTest_id = at.AnalyzerTest_id
					left join v_RefValues rv on rv.RefValues_id = atrv.RefValues_id
					left join lis.v_Unit u on u.Unit_id = rv.Unit_id
					left join lateral(
						select count(*) as cnt
						from
							v_LimitValues l
							inner join v_LimitType lt on lt.LimitType_id = l.LimitType_id
						where l.RefValues_id = rv.RefValues_id
							and (
								(l.Limit_Values IS NOT NULL AND lt.LimitType_IsCatalog = 2) or
								((l.Limit_ValuesTo IS NOT NULL OR l.Limit_ValuesFrom IS NOT NULL) AND lt.LimitType_IsCatalog = 1)
							)
					) as LIMIT_alias on true
				where a.MedService_id = :MedService_id
				  and ucms_at.UslugaComplex_id IN ('" . implode("','", $UslugaComplex_ids) . "')
				  and (
				        at.AnalyzerTest_pid is null or
				        exists(
                        	select AnalyzerTest_id 
                            from
                                lis.v_AnalyzerTest at_parent 
                                inner join v_UslugaComplexMedService ucms_at_parent on ucms_at_parent.UslugaComplexMedService_id = at_parent.UslugaComplexMedService_id 
                        	where at_parent.AnalyzerTest_id = at.AnalyzerTest_pid 
                        	  and ucms_at_parent.UslugaComplex_id = :UslugaComplexTarget_id
                        )
                  )
				order by
					case when a.Analyzer_id = COALESCE(CAST(:Analyzer_id as bigint), 0) then 0 else 1 end,
					LIMIT_alias.cnt desc,
					rv.RefValues_Name
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $data);
			if (is_object($result)) {
				$resp_rv = $result->result_array();
				foreach ($resp_rv as $respone) {
					if ($respone["AnalyzerTestType_id"] == 2 && !empty($respone["AnalyzerTestRefValues_id"])) {
						$respone["UslugaTest_ResultQualitativeNorms"] = $callObject->AnalyzerTestRefValues_model->getRefValuesForQualitativeTestJSON($respone["AnalyzerTestRefValues_id"]);
					}
					$refvalues[$respone["UslugaComplex_id"]][] = $respone;
				}
			}
			foreach ($refvalues as $ucid => $rvs) {
				// если услуга на двух и более анализаторах и среди них нет текущего анализатора, то автоматически нормы не берём.
				$analyzers = [];
				foreach ($rvs as $rv) {
					if ($rv["Analyzer_id"] == $data["Analyzer_id"]) { // если есть текущий анализатор, значит реф. значения будем брать.
						$analyzers = [];
						break;
					}
					if (!in_array($rv["Analyzer_id"], $analyzers)) {
						$analyzers[] = $rv["Analyzer_id"];
					}
				}
				if (count($analyzers) > 1) {
					unset($refvalues[$ucid]);
				}
			}
			foreach ($UslugaComplex_ids as $UslugaComplex_id) {
				$data["UslugaComplexTest_id"] = $UslugaComplex_id;
				$rv = null;
				if (!empty($data["Analyzer_id"])) {
					$data["OrderByLimit"] = true;
					if (!empty($refvalues[$UslugaComplex_id])) {
						$Analyzer_id = null;
						foreach ($refvalues[$UslugaComplex_id] as $refvalue) {
							if ($Analyzer_id != null && $Analyzer_id != $refvalue["Analyzer_id"]) {
								// если изменился анализатор, значит перешли с основного, а раз были реф.значения на оснвном то с ручных методик их не берём
								break;
							}
							$Analyzer_id = $refvalue["Analyzer_id"];
							if (!empty($refvalue["RefValues_id"])) {
								$limit_ok = true;
								// каждое референсное значение проверяем на ограничения
								$query = "
									select
										l.LimitType_id as \"LimitType_id\",
										lt.LimitType_SysNick as \"LimitType_SysNick\",
										l.Limit_Values as \"Limit_Values\",
										l.Limit_ValuesFrom as \"Limit_ValuesFrom\",
										l.Limit_ValuesTo as \"Limit_ValuesTo\"
									from
										v_LimitValues l
										inner join v_LimitType lt on lt.LimitType_id = l.LimitType_id
									where l.RefValues_id = :RefValues_id
									  and (
											(l.Limit_Values IS NOT NULL AND lt.LimitType_IsCatalog = 2) or
											((l.Limit_ValuesTo IS NOT NULL OR l.Limit_ValuesFrom IS NOT NULL) AND lt.LimitType_IsCatalog = 1)
									  )
								";
								/**@var CI_DB_result $result_limits */
								$result_limits = $callObject->db->query($query, $refvalue);
								if (is_object($result_limits)) {
									$resp_limits = $result_limits->result_array();
									foreach ($resp_limits as $resp_limit) {
										if ($resp_limit["LimitType_SysNick"] == "PregnancyUnitType") {
											if (!isset($person["Pregnancy_Value"])) {
												$limit_ok = false;
											}
											if (!empty($resp_limit["Limit_ValuesFrom"]) && $resp_limit["Limit_ValuesFrom"] > $person["Pregnancy_Value"]) {
												$limit_ok = false;
											}
											if (!empty($resp_limit["Limit_ValuesTo"]) && $resp_limit["Limit_ValuesTo"] < $person["Pregnancy_Value"]) {
												$limit_ok = false;
											}
										}
										if ($resp_limit["LimitType_SysNick"] == "HormonalPhaseType") {
											if (!isset($person["HormonalPhaseType_id"])) {
												$limit_ok = false;
											}
											if ($person["HormonalPhaseType_id"] != $resp_limit["Limit_Values"]) {
												$limit_ok = false;
											}
										}
										if ($resp_limit["LimitType_SysNick"] == "Sex" && $person["Sex_id"] != $resp_limit["Limit_Values"]) {
											$limit_ok = false;
										}
										if ($resp_limit["LimitType_SysNick"] == "AgeUnit") {
											switch ($resp_limit["Limit_Values"]) {
												case 1:
													if (!empty($resp_limit["Limit_ValuesFrom"]) && $resp_limit["Limit_ValuesFrom"] > $person["Person_AgeYear"]) {
														$limit_ok = false;
													}
													if (!empty($resp_limit["Limit_ValuesTo"]) && $resp_limit["Limit_ValuesTo"] < $person["Person_AgeYear"]) {
														$limit_ok = false;
													}
													break;
												case 2:
													if (!empty($resp_limit["Limit_ValuesFrom"]) && $resp_limit["Limit_ValuesFrom"] > $person["Person_AgeMonth"]) {
														$limit_ok = false;
													}
													if (!empty($resp_limit["Limit_ValuesTo"]) && $resp_limit["Limit_ValuesTo"] < $person["Person_AgeMonth"]) {
														$limit_ok = false;
													}
													break;
												case 3:
												case 4:
													if (!empty($resp_limit["Limit_ValuesFrom"]) && $resp_limit["Limit_ValuesFrom"] > $person["Person_AgeDay"]) {
														$limit_ok = false;
													}
													if (!empty($resp_limit["Limit_ValuesTo"]) && $resp_limit["Limit_ValuesTo"] < $person["Person_AgeDay"]) {
														$limit_ok = false;
													}
													break;
												case 5:
													if (!empty($resp_limit["Limit_ValuesFrom"]) && $resp_limit["Limit_ValuesFrom"] > $person["Person_AgeWeek"]) {
														$limit_ok = false;
													}
													if (!empty($resp_limit["Limit_ValuesTo"]) && $resp_limit["Limit_ValuesTo"] < $person["Person_AgeWeek"]) {
														$limit_ok = false;
													}
													break;
											}
										}
										if ($resp_limit["LimitType_id"] == 7) {
											if (!empty($resp_limit["Limit_ValuesFrom"]) && $resp_limit["Limit_ValuesFrom"] > $person["TimeOfDay"]) {
												$limit_ok = false;
											}
											if (!empty($resp_limit["Limit_ValuesTo"]) && $resp_limit["Limit_ValuesTo"] < $person["TimeOfDay"]) {
												$limit_ok = false;
											}
										}
									}
								}
								if ($limit_ok) {
									$rv = $refvalue;
									break;
								}
							}
						}
						if (!empty($rv)) {
							$rv["UslugaComplex_id"] = $UslugaComplex_id;
							$resp[] = $rv;
						}
					}
				}
			}
		}
		return $resp;
	}

	/**
	 * Загрузка
	 * @param EvnLabSample_model $callObject
	 * @param null $field
	 * @param null $value
	 * @param string $selectFields
	 * @param bool $addNameEntries
	 * @return bool|mixed
	 */
	public static function load(EvnLabSample_model $callObject, $field = null, $value = null, $selectFields = "*", $addNameEntries = true)
	{
		$query = "
			select
				t.EvnClass_id as \"EvnClass_id\",
				t.EvnClass_Name as \"EvnClass_Name\",
				t.EvnLabSample_id as \"EvnLabSample_id\",
				t.EvnLabSample_pid as \"EvnLabSample_pid\",
				t.EvnLabSample_rid as \"EvnLabSample_rid\",
				t.Lpu_id as \"Lpu_id\",
				t.Server_id as \"Server_id\",
				t.PersonEvn_id as \"PersonEvn_id\",
				t.EvnLabSample_setDate as \"EvnLabSample_setDate\",
				t.EvnLabSample_setTime as \"EvnLabSample_setTime\",
				t.EvnLabSample_didDate as \"EvnLabSample_didDate\",
				t.EvnLabSample_diDTime as \"EvnLabSample_diDTime\",
				t.EvnLabSample_disDate as \"EvnLabSample_disDate\",
				t.EvnLabSample_disTime as \"EvnLabSample_disTime\",
				t.EvnLabSample_AnalyzerDate as \"EvnLabSample_AnalyzerDate\",				
				t.EvnLabSample_StatusDate as \"EvnLabSample_StatusDate\",
				to_char(t.EvnLabSample_setDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_setDT\",
				to_char(t.EvnLabSample_disDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_disDT\",
				to_char(t.EvnLabSample_didDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_didDT\",
				to_char(t.EvnLabSample_insDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_insDT\",
				to_char(t.EvnLabSample_updDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_updDT\",
				to_char(t.EvnLabSample_signDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_signDT\",
				to_char(t.EvnLabSample_DelivDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_DelivDT\",
				to_char(t.EvnLabSample_StudyDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_StudyDT\",
				t.EvnLabSample_Index as \"EvnLabSample_Index\",
				t.EvnLabSample_Count as \"EvnLabSample_Count\",
				t.pmUser_insID as \"pmUser_insID\",
				t.pmUser_updID as \"pmUser_updID\",
				t.Person_id as \"Person_id\",
				t.Morbus_id as \"Morbus_id\",
				t.EvnLabSample_IsSigned as \"EvnLabSample_IsSigned\",
				t.pmUser_signID as \"pmUser_signID\",
				t.EvnLabSample_IsArchive as \"EvnLabSample_IsArchive\",
				t.EvnLabSample_Guid as \"EvnLabSample_Guid\",
				t.EvnLabSample_IndexMinusOne as \"EvnLabSample_IndexMinusOne\",
				t.EvnStatus_id as \"EvnStatus_id\",
				t.EvnLabSample_IsTransit as \"EvnLabSample_IsTransit\",
				t.EvnLabRequest_id as \"EvnLabRequest_id\",
				t.EvnLabSample_Num as \"EvnLabSample_Num\",
				t.RefSample_id as \"RefSample_id\",
				t.Lpu_did as \"Lpu_did\",
				t.LpuSection_did as \"LpuSection_did\",
				t.MedPersonal_did as \"MedPersonal_did\",
				t.MedPersonal_sdid as \"MedPersonal_sdid\",
				t.Lpu_aid as \"Lpu_aid\",
				t.LpuSection_aid as \"LpuSection_aid\",
				t.MedPersonal_aid as \"MedPersonal_aid\",
				t.MedPersonal_said as \"MedPersonal_said\",
				t.LabSampleDefectiveType_id as \"LabSampleDefectiveType_id\",
				t.EvnLabSample_Comment as \"EvnLabSample_Comment\",
				t.MedService_id as \"MedService_id\",
				t.LabSampleStatus_id as \"LabSampleStatus_id\",
				t.DefectCauseType_id as \"DefectCauseType_id\",
				t.EvnLabSample_IsLis as \"EvnLabSample_IsLis\",
				t.Analyzer_id as \"Analyzer_id\",
				t.EvnLabSample_Test as \"EvnLabSample_Test\",
				t.EvnLabSample_IsOutNorm as \"EvnLabSample_IsOutNorm\",
				t.EvnLabSample_Barcode as \"EvnLabSample_Barcode\",
				t.MedService_did as \"MedService_did\",
				t.MedService_sid as \"MedService_sid\",
				r.RefMaterial_Name as \"RefMaterial_Name\",
				Lpu_id_ref.Lpu_Name as \"Lpu_id_Name\",
				Morbus_id_ref.Morbus_Name as \"Morbus_id_Name\",
				EvnLabSample_IsSigned_ref.YesNo_Name as \"EvnLabSample_IsSigned_Name\",
				RefSample_id_ref.RefSample_Name as \"RefSample_id_Name\",
				Lpu_did_ref.Lpu_Name as \"Lpu_did_Name\",
				LpuSection_did_ref.LpuSection_Name as \"LpuSection_did_Name\",
				Lpu_aid_ref.Lpu_Name as \"Lpu_aid_Name\",
				LpuSection_aid_ref.LpuSection_Name as \"LpuSection_aid_Name\",
				LabSampleDefectiveType_id_ref.LabSampleDefectiveType_Name as \"LabSampleDefectiveType_id_Name\",
				case
					when t.DefectCauseType_id is null
						then 0
						else 1
				end as \"EvnLabSample_IsDefect\"
			from
				dbo.v_EvnLabSample t
				left join v_Lpu Lpu_id_ref on Lpu_id_ref.Lpu_id = t.Lpu_id
				left join v_Morbus Morbus_id_ref on Morbus_id_ref.Morbus_id = t.Morbus_id
				left join v_YesNo EvnLabSample_IsSigned_ref on EvnLabSample_IsSigned_ref.YesNo_id = t.EvnLabSample_IsSigned
				left join v_RefSample RefSample_id_ref on RefSample_id_ref.RefSample_id = t.RefSample_id
				left join v_Lpu Lpu_did_ref on Lpu_did_ref.Lpu_id = t.Lpu_did
				left join v_LpuSection LpuSection_did_ref on LpuSection_did_ref.LpuSection_id = t.LpuSection_did
				left join v_Lpu Lpu_aid_ref on Lpu_aid_ref.Lpu_id = t.Lpu_aid
				left join v_LpuSection LpuSection_aid_ref on LpuSection_aid_ref.LpuSection_id = t.LpuSection_aid
				left join v_LabSampleDefectiveType LabSampleDefectiveType_id_ref on LabSampleDefectiveType_id_ref.LabSampleDefectiveType_id = t.LabSampleDefectiveType_id
				left join  dbo.v_RefSample s on s.RefSample_id = t.RefSample_id
				left join  dbo.v_RefMaterial r on s.RefMaterial_id = r.RefMaterial_id
			where EvnLabSample_id = :EvnLabSample_id
		";
		/**@var CI_DB_result $result */
		$queryParams = ["EvnLabSample_id" => $callObject->EvnLabSample_id];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		// тут ещё был код который наследовался в абстрактной модели и потерялся 12 июля, возвращаю его сюда
		$response = $result->result_array();
		$response = $callObject->getMorbusNames($response);
		if (isset($response[0])) {
			$callObject->setRawLoadResult($response[0]);
			$callObject->assign($response[0]);
		}

		return $response;
	}

	/**
	 * Возвращает список проб для еще не созданой заявки по выбранной комплексной услуге, службе и заявке
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|CI_DB_result|mixed
	 */
	public static function loadLabSampleFrame(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				els.EvnLabSample_id as \"EvnLabSample_id\",
				els.RefSample_id as \"RefSample_id\",
				els.EvnLabSample_Num as \"EvnLabSample_Num\",
				els.MedService_id as \"MedService_id\",
				substring(els.EvnLabSample_Num from 9 for 4) as \"EvnLabSample_ShortNum\",
				els.EvnLabSample_BarCode as \"EvnLabSample_BarCode\",
				rm.RefMaterial_Name as \"RefMaterial_Name\",
				to_char (els.EvnLabSample_setDT, 'HH24:MI dd.mm.yyyy') as \"EvnLabSample_setDT\"
			from
				v_EvnLabSample els
				left join v_RefSample rs on rs.RefSample_id = els.RefSample_id
				left join v_RefMaterial rm on rm.RefMaterial_id = rs.RefMaterial_id
			where els.EvnLabRequest_id = :EvnLabRequest_id
		";
		/**@var CI_DB_result $result */
		$params = ["EvnLabRequest_id" => $data["EvnLabRequest_id"]];
		$result = $callObject->db->query($query, $params);
		$result = $result->result_array();
		return $result;
	}

	/**
	 * Загрузка списка проб для рабочего списка
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadEvnLabSampleListForWorksheet(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				-- select
				els.EvnLabSample_id as \"EvnLabSample_id\",
				els.EvnLabSample_Num as \"EvnLabSample_Num\",
				COALESCE(ps.Person_SurName||' ','') || COALESCE(ps.Person_FirName||' ','') || COALESCE(ps.Person_SecName,'') as \"EvnLabSample_Fio\",
				awels.AnalyzerWorksheetEvnLabSample_X::varchar||' '||awels.AnalyzerWorksheetEvnLabSample_Y::varchar as \"EvnLabSample_Position\"
				-- end select
			from
				-- from
				v_EvnLabSample els
				inner join lis.v_AnalyzerWorksheetEvnLabSample awels on els.EvnLabSample_id = awels.EvnLabSample_id
				left join v_PersonState ps on ps.Person_id = els.Person_id
				-- end from
			where
				-- where
				awels.AnalyzerWorksheet_id = :AnalyzerWorksheet_id
				-- end where
			order by
				-- order by
				awels.AnalyzerWorksheetEvnLabSample_X, awels.AnalyzerWorksheetEvnLabSample_Y
				-- end order by						
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$params = ["AnalyzerWorksheet_id" => $data["AnalyzerWorksheet_id"]];
		$result = $callObject->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $params);
		$result_count = $callObject->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result_array();
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		return [
			"data" => $result->result_array(),
			"totalCount" => $count
		];
	}

	/**
	 * Функция чтения рабочего журнала проб
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function loadWorkList(EvnLabSample_model $callObject, $data)
	{
		try {
			$filter = "";
			$msfilter = "";

			if(!empty($data['formMode']) && $data['formMode'] == 'ifa') {
				$EvnLabSamples_ids = $callObject->getIFAEvnLabSamples($data);
				if (empty($EvnLabSamples_ids)) {
					return false;
				}
				$filter .= " and els.EvnLabSample_id in ({$EvnLabSamples_ids})";
			}

			if (!empty($data["EvnDirection_IsCito"])) {
				$filter .= " and (COALESCE(elr.EvnLabRequest_IsCito, 1) = :EvnDirection_IsCito)";
			}
			if (!empty($data["EvnLabSample_IsOutNorm"])) {
				$filter .= " and (COALESCE(els.EvnLabSample_IsOutNorm, 1) = :EvnLabSample_IsOutNorm)";
			}
			if (!empty($data["Person_ShortFio"])) {
				if (allowPersonEncrypHIV()) {
					$filter .= " and (COALESCE(ps.Person_SurName, '') || COALESCE(' '|| SUBSTRING(ps.Person_FirName from 1 for 1) || '.','') || COALESCE(' '|| SUBSTRING(ps.Person_SecName from 1 for 1) || '.','') LIKE upper(:Person_ShortFio) || '%' or peh.PersonEncrypHIV_Encryp LIKE :Person_ShortFio || '%')";
				} else {
					$filter .= " and COALESCE(ps.Person_SurName, '') || COALESCE(' '|| SUBSTRING(ps.Person_FirName from 1 for 1) || '.','') || COALESCE(' '|| SUBSTRING(ps.Person_SecName from 1 for 1) || '.','') LIKE upper(:Person_ShortFio) || '%'";
				}
			}
			if (!empty($data["EvnDirection_Num"])) {
				$filter .= " and ed.EvnDirection_Num LIKE '%' || :EvnDirection_Num || '%'";
			}
			if (!empty($data["EvnLabSample_BarCode"])) {
				$filter .= " and els.EvnLabSample_BarCode LIKE :EvnLabSample_BarCode || '%'";
			}
			if (!empty($data["EvnLabSample_ShortNum"])) {
				$filter .= " and substring(els.EvnLabSample_Num from 9 for 4) = :EvnLabSample_ShortNum";
			}
			if (!empty($data["LabSampleStatus_id"])) {
				$filter .= " and els.LabSampleStatus_id = :LabSampleStatus_id";
			}
			if (!empty($data["MedServiceType_SysNick"]) && $data["MedServiceType_SysNick"] == "reglab") {
				$msfilter = " or els.MedService_id in (select MSL.MedService_lid from MedServiceLink MSL where msl.MedService_id = :MedService_id)";
			}
			$allow_encryp = allowPersonEncrypHIV() ? "1" : "0";
			$addWhenBeg = "when 1=0 then null";
			$addWhenEnd = "when 1=0 then null";
			$withoutDateStatus = [];
			if (empty($data["filterNewELSByDate"])) {
				$withoutDateStatus[] = 1;
			}
			if (empty($data["filterWorkELSByDate"])) {
				$withoutDateStatus[] = 2;
				$withoutDateStatus[] = 7;
			}
			if (empty($data["filterDoneELSByDate"])) {
				$withoutDateStatus[] = 3;
			}
			if (!empty($withoutDateStatus)) {
				$addWhenBeg = "when COALESCE(els.LabSampleStatus_id, 1) IN (" . implode(",", $withoutDateStatus) . ") then :begDate";
				$addWhenEnd = "when COALESCE(els.LabSampleStatus_id, 1) IN (" . implode(",", $withoutDateStatus) . ") then :endDate";
			}
			$datefilter = "
				and (:begDate::timestamp <= case
					{$addWhenBeg}
					else COALESCE(to_char(els.EvnLabSample_StudyDT,'dd.mm.yyyy')::timestamp, els.EvnLabSample_setDate, elr.EvnLabRequest_didDate) end)
				and (:endDate::timestamp >= case
					{$addWhenEnd}
					else COALESCE(to_char(els.EvnLabSample_StudyDT,'dd.mm.yyyy')::timestamp, els.EvnLabSample_setDate, elr.EvnLabRequest_didDate) end)
			";
			$query = "
				with tempTable as (
					select
						els.EvnLabSample_id,
						substring(els.EvnLabSample_Num from 9 for 4) as EvnLabSample_ShortNum,
						CASE WHEN COALESCE(elr.EvnLabRequest_IsCito, 1) = 2 THEN '!' else '' END AS EvnDirection_IsCito,
						els.LabSampleStatus_id,
						els.EvnLabSample_Num,
						els.EvnLabSample_BarCode,
						els.EvnLabRequest_id,
						els.MedService_id,
						rm.RefMaterial_id,
						rm.RefMaterial_Name,
						ed.EvnDirection_id,
						ed.EvnDirection_Num,
						case
							when 1 = ed.PrehospDirect_id then COALESCE(ls.LpuSection_Name, Lpu.Lpu_Nick) -- 1 Отделение ЛПУ (Если не выбрали то ЛПУ)
							when 2 = ed.PrehospDirect_id then Lpu.Lpu_Nick -- 2 Другое ЛПУ --Lpu_sid - Направившее ЛПУ
							when ed.PrehospDirect_id in ( 3, 4, 5, 6 ) then Org.Org_nick -- 3 Другая организация -- 4 Военкомат -- 5 Скорая помощь -- 6 Администрация -- Org_sid - Направившая организация
							when 7 = ed.PrehospDirect_id then 'Пункт помощи на дому' --7Пункт помощи на дому
							else COALESCE(ls.LpuSection_Name, Lpu_Nick)
						end as PrehospDirect_Name,
						COALESCE(LSS.LabSampleStatus_SysNick, 'new') as ProbaStatus,
						ps.Person_id,
						case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null then PEH.PersonEncrypHIV_Encryp
							else COALESCE(ps.Person_SurName, '') || COALESCE(' '|| ps.Person_FirName,'') || COALESCE(' '|| ps.Person_SecName,'')
						end as Person_FIO,
						case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null then PEH.PersonEncrypHIV_Encryp
							else COALESCE(ps.Person_SurName, '') || COALESCE(' '|| SUBSTRING(ps.Person_FirName from 1 for 1) || '.','') || COALESCE(' '|| SUBSTRING(ps.Person_SecName from 1 for 1) || '.','')
						end as Person_ShortFio,
						to_char(PS.Person_Birthday, 'dd.mm.yyyy') as Person_Birthday,
						case when {$allow_encryp}=1 then PEH.PersonEncrypHIV_Encryp end as PersonEncrypHIV_Encryp,
						to_char (els.EvnLabSample_setDT, 'HH24:MI dd.mm.yyyy') as EvnLabSample_setDT,
						els.EvnLabSample_StudyDT as EvnLabSample_StudyDT,
						elr.EvnLabRequest_BarCode as EvnLabRequest_BarCode,
						elr.UslugaComplex_id as UslugaComplexTarget_id,
						a.Analyzer_id,
						a.Analyzer_Name,
						a.Analyzer_2wayComm,
						AWELS.AnalyzerWorksheetEvnLabSample_id,
						link.lis_id as lis_id, -- идентификатор объекта в LIS
						ls.LpuSection_Code,
						ms.MedService_Nick,
						COALESCE(els.EvnLabSample_IsOutNorm, 1) as EvnLabSample_IsOutNorm
					from
						dbo.v_EvnLabSample els
						inner join v_EvnLabRequest elr on els.EvnLabRequest_id = elr.EvnLabRequest_id
						left join v_MedService ms on ms.MedService_id = elr.MedService_id
						left join v_EvnDirection_all ed on ed.EvnDirection_id = elr.EvnDirection_id
						left join v_LpuSection ls on ls.LpuSection_id = ed.LpuSection_id
						left join v_Lpu Lpu on Lpu.Lpu_id = ed.Lpu_sid
						left join v_Org Org on Org.Org_id = ed.Org_sid
						left join v_PersonState ps on elr.Person_id = ps.Person_id
						left join v_PersonEncrypHIV peh on peh.Person_id = ps.Person_id
						left join v_LabSampleStatus lss on lss.LabSampleStatus_id = els.LabSampleStatus_id
						left join v_RefSample rs on rs.RefSample_id = els.RefSample_id
						left join v_RefMaterial rm on rm.RefMaterial_id = rs.RefMaterial_id
						left join lateral (
							select link.lis_id 
							from lis.v_Link link 
							where link.object_id = els.EvnLabSample_id and link.link_object = 'EvnLabSample'
							limit 1
						) as link on true
						left join lis.v_Analyzer a on a.Analyzer_id = els.Analyzer_id
						left join lateral(
							select AnalyzerWorksheetEvnLabSample_id
							from lis.v_AnalyzerWorksheetEvnLabSample
							where EvnLabSample_id = els.EvnLabSample_id
							limit 1    
						) as AWELS on true
					where els.Lpu_id = :Lpu_id
					  and els.EvnLabSample_setDT is not null
					  and (els.MedService_id = :MedService_id {$msfilter})
					  {$filter}
					  {$datefilter}
				)
                select
					table1.EvnLabSample_id as \"EvnLabSample_id\",
					table1.EvnLabSample_ShortNum as \"EvnLabSample_ShortNum\",
					table1.EvnDirection_IsCito as \"EvnDirection_IsCito\",
					table1.LabSampleStatus_id as \"LabSampleStatus_id\",
					table1.EvnLabSample_Num as \"EvnLabSample_Num\",
					table1.EvnLabSample_BarCode as \"EvnLabSample_BarCode\",
					table1.EvnLabRequest_id as \"EvnLabRequest_id\",
					table1.MedService_id as \"MedService_id\",
					table1.RefMaterial_id as \"RefMaterial_id\",
					table1.RefMaterial_Name as \"RefMaterial_Name\",
					table1.EvnDirection_id as \"EvnDirection_id\",
					table1.EvnDirection_Num as \"EvnDirection_Num\",
					table1.PrehospDirect_Name as \"PrehospDirect_Name\",
					table1.ProbaStatus as \"ProbaStatus\",
					table1.Person_id as \"Person_id\",
					table1.Person_FIO as \"Person_FIO\",
					table1.Person_ShortFio as \"Person_ShortFio\",
					table1.Person_Birthday as \"Person_Birthday\",
					table1.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",
					table1.EvnLabSample_setDT as \"EvnLabSample_setDT\",
					table1.EvnLabSample_StudyDT as \"EvnLabSample_StudyDT\",
					table1.EvnLabRequest_BarCode as \"EvnLabRequest_BarCode\",
					table1.UslugaComplexTarget_id as \"UslugaComplexTarget_id\",
					table1.Analyzer_id as \"Analyzer_id\",
					table1.Analyzer_Name as \"Analyzer_Name\",
					table1.Analyzer_2wayComm as \"Analyzer_2wayComm\",
					table1.AnalyzerWorksheetEvnLabSample_id as \"AnalyzerWorksheetEvnLabSample_id\",
					table1.lis_id, -- идентификатор объекта в LIS
					table1.LpuSection_Code as \"LpuSection_Code\",
					table1.MedService_Nick as \"MedService_Nick\",
					table1.EvnLabSample_IsOutNorm as \"EvnLabSample_IsOutNorm\",
					ut.cnt as \"EvnLabSample_Tests\"
				from
					tempTable as table1
					left join lateral(
						select count(*) as cnt
						from v_UslugaTest ut
						where ut.EvnLabSample_id = table1.EvnLabSample_id
						  and ut.EvnDirection_id is null
					) as ut on true
			";
			return $callObject->queryResult($query, $data);
		} catch (Exception $e) {
			log_message("error", $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Функция чтения журнала отбраковки проб
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadDefectList(EvnLabSample_model $callObject, $data)
	{
		try {
			$filter = "";
			if (!empty($data["EvnDirection_IsCito"])) {
				$filter .= " and (COALESCE(elr.EvnLabRequest_IsCito, 1) = :EvnDirection_IsCito)";
			}
			if (!empty($data["UslugaComplex_id"])) {
				$filter .= "
					and exists(
						select eu.EvnUslugaPar_id
						from v_EvnUslugaPar eu
						where eu.EvnLabSample_id = els.EvnLabSample_id and eu.UslugaComplex_id = :UslugaComplex_id

					)
				";
			}
			if (!empty($data["RefMaterial_id"])) {
				$filter .= " and rm.RefMaterial_id = :RefMaterial_id";
			}
			if (!empty($data["DefectCauseType_id"])) {
				$filter .= " and els.DefectCauseType_id = :DefectCauseType_id";
			}
			if (!empty($data["MedService_id"]) && $callObject->regionNick == "vologda") {
				$filter .= " and els.MedService_did = :MedService_sid";
			}
			$query = "
				SELECT
					els.EvnLabSample_id as \"EvnLabSample_id\",
					CASE WHEN COALESCE(elr.EvnLabRequest_IsCito, 1) = 2 THEN '!' else '' END AS \"EvnDirection_IsCito\",
					cast(lss.LabSampleStatus_Code as varchar) || '. ' || lss.LabSampleStatus_Name as \"EvnLabSample_Status\",
					EvnLabSample_BarCode as \"EvnLabSample_BarCode\",
					rm.RefMaterial_id as \"RefMaterial_id\",
					RefMaterial_Name as \"RefMaterial_Name\",
					ed.EvnDirection_Num as \"EvnDirection_Num\",
					els.DefectCauseType_id as \"DefectCauseType_id\",
					CASE WHEN (els.MedService_did = els.MedService_sid) THEN 2 else 0 END AS \"MedService_flag\", --флаг: если проба забракована там же, где взята, то 2, иначе 0
					dct.DefectCauseType_Name as \"DefectCauseType_Name\",
					null as \"EvnLabSample_UslugaList\", -- список исследований, но его еще надо будет как-то получать
					els.EvnLabSample_setDT as \"EvnLabSample_setDT\",
					els.EvnLabSample_StudyDT as \"EvnLabSample_StudyDT\",
					link.lis_id as \"lis_id\" -- идентификатор объекта в LIS
				FROM
					v_EvnLabSample els
					inner join v_EvnLabRequest elr on els.EvnLabRequest_id = elr.EvnLabRequest_id
					left join lis.v_DefectCauseType dct on dct.DefectCauseType_id = els.DefectCauseType_id
					left join v_EvnDirection_all ed on ed.EvnDirection_id = elr.EvnDirection_id
					left join v_LabSampleStatus lss on lss.LabSampleStatus_id = els.LabSampleStatus_id
					left join v_RefSample rs on rs.RefSample_id = els.RefSample_id
					left join v_RefMaterial rm on rm.RefMaterial_id = rs.RefMaterial_id
					left join lis.v_Link link on link.object_id = els.EvnLabSample_id and link.link_object = 'EvnLabSample'
				WHERE els.Lpu_id = :Lpu_id
				  {$filter}
				  and els.DefectCauseType_id IS NOT NULL
				  and ((COALESCE(els.EvnLabSample_setDT,elr.EvnLabRequest_didDT)::date BETWEEN :begDate and :endDate) or :begDate is null or :endDate is null)
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $data);
			return (is_object($result)) ? $result->result_array() : false;
		} catch (Exception $e) {
			log_message("error", $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Функция читает список проб
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadList(EvnLabSample_model $callObject, $data)
	{
		$sql = "
			select
				EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabSample_pid as \"EvnLabSample_pid\",
				EvnLabSample_rid as \"EvnLabSample_rid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				to_char (EvnLabSample_setDT, 'dd.mm.yyyy') as \"EvnLabSample_setDT\",
				to_char (EvnLabSample_disDT, 'dd.mm.yyyy') as \"EvnLabSample_disDT\",
				to_char (EvnLabSample_didDT, 'dd.mm.yyyy') as \"EvnLabSample_didDT\",
				to_char (EvnLabSample_insDT, 'dd.mm.yyyy') as \"EvnLabSample_insDT\",
				to_char (EvnLabSample_updDT, 'dd.mm.yyyy') as \"EvnLabSample_updDT\",
				EvnLabSample_Index as \"EvnLabSample_Index\",
				EvnLabSample_Count as \"EvnLabSample_Count\",
				Morbus_id as \"Morbus_id\",
				EvnLabSample_IsSigned as \"EvnLabSample_IsSigned\",
				pmUser_signID as \"pmUser_signID\",
				to_char (EvnLabSample_signDT, 'dd.mm.yyyy') as \"EvnLabSample_signDT\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				EvnLabSample_Num as \"EvnLabSample_Num\",
				EvnLabSample_Comment as \"EvnLabSample_Comment\",
				RefSample_id as \"RefSample_id\",
				Lpu_did as \"Lpu_did\",
				LpuSection_did as \"LpuSection_did\",
				MedPersonal_did as \"MedPersonal_did\",
				MedPersonal_sdid as \"MedPersonal_sdid\",
				to_char (EvnLabSample_DelivDT, 'dd.mm.yyyy') as \"EvnLabSample_DelivDT\",
				Lpu_aid as \"Lpu_aid\",
				LpuSection_aid as \"LpuSection_aid\",
				MedPersonal_aid as \"MedPersonal_aid\",
				MedPersonal_said as \"MedPersonal_said\",
				to_char (EvnLabSample_StudyDT, 'dd.mm.yyyy') as \"EvnLabSample_StudyDT\",
				LabSampleDefectiveType_id as \"LabSampleDefectiveType_id\",
				Analyzer_id as \"Analyzer_id\",
				pmUser_insID as \"pmUser_id\",
				1 as \"RecordStatus_Code\"
			from v_EvnLabSample els
			where els.EvnLabRequest_id = :EvnLabRequest_id
		";
		$params = ["EvnLabRequest_id" => $data["EvnLabRequest_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Сохранение параметров исследования
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadResearchEditForm(EvnLabSample_model $callObject, $data)
	{
		// можно начитывать и делать p_EvnUslugaPar_upd
		$query = "
			select
				COALESCE(ls.Lpu_id, elr.Lpu_id) as \"Lpu_aid\",
				eup.LpuSection_uid as \"LpuSection_aid\",
				eup.MedPersonal_id as \"MedPersonal_aid\",
				eup.MedPersonal_sid as \"MedPersonal_said\",
				eup.EvnLabSample_id as \"EvnLabSample_id\",
				TO_CHAR(eup.EvnUslugaPar_setDT, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
				to_char(eup.EvnUslugaPar_updDT, 'HH24:MI:SS') as \"EvnUslugaPar_setTime\",
				eup.EvnDirection_id as \"EvnDirection_id\",
				to_char(dbo.tzGetDate(), 'dd.mm.yyyy') as \"EvnUslugaPar_maxDate\",
				eup.EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\",
				COALESCE(eup.evnuslugapar_ispaid, 1) as \"EvnUslugaPar_IsPaid\",
				COALESCE(eup.evnuslugapar_indexrep, 0) as \"EvnUslugaPar_IndexRep\",
				COALESCE(eup.evnuslugapar_indexrepinreg, 1) as \"EvnUslugaPar_IndexRepInReg\"
			from
				v_EvnUslugaPar eup
				left join v_EvnLabRequest elr on elr.EvnDirection_id = eup.EvnDirection_id
				left join v_LpuSection ls on ls.LpuSection_id = eup.LpuSection_uid
			where eup.EvnUslugaPar_id = :EvnUslugaPar_id
		";
		$queryParams = ["EvnUslugaPar_id" => $data["EvnUslugaPar_id"]];
		$resp = $callObject->queryResult($query, $queryParams);
		if (empty($resp[0]["EvnUslugaPar_setDate"]) && !empty($resp[0]["EvnLabSample_id"])) {
			// берём с пробы
			$query = "
				select
					Lpu_aid as \"Lpu_aid\",
					LpuSection_aid as \"LpuSection_aid\",
					MedPersonal_aid as \"MedPersonal_aid\",
					MedPersonal_said as \"MedPersonal_said\",
					EvnLabSample_id as \"EvnLabSample_id\",
					null as \"EvnUslugaPar_setDate\",
					null as \"EvnUslugaPar_setTime\",
					EvnLabSample_id as \"EvnLabSample_id\",
					to_char (dbo.tzGetDate(), 'dd.mm.yyyy') as \"EvnUslugaPar_maxDate\"
				from v_EvnLabSample
				where EvnLabSample_id = :EvnLabSample_id
			";
			$queryParams = ["EvnLabSample_id" => $resp[0]["EvnLabSample_id"]];
			$resp = $callObject->queryResult($query, $queryParams);
		}
		if (!empty($resp[0])) {
			$resp[0]["EvnUslugaPar_minDate"] = null;
			if (!empty($resp[0]["EvnDirection_id"])) {
				$res = $callObject->common->GET("EvnPrescr/EvnPrescrInsDate", ["EvnDirection_id" => $resp[0]["EvnDirection_id"]], "single");
				if (!$callObject->isSuccessful($res)) {
					return $res;
				}
				if (!empty($res["EvnPrescr_insDate"])) {
					$resp[0]["EvnUslugaPar_minDate"] = $res["EvnPrescr_insDate"];
				}
			}
		}
		return $resp;
	}

	/**
	 * Получение списка проб-кандидатов
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadListForCandiPicker(EvnLabSample_model $callObject, $data)
	{
		$queryParams = ["AnalyzerWorksheet_id" => $data["AnalyzerWorksheet_id"]];
		$where = [];
		if (isset($data["EvnLabRequest_BarCode"])) {
			$where[] = "Right(elr.EvnLabRequest_BarCode, 12) ilike :EvnLabRequest_BarCode||'%'";
			$p["EvnLabRequest_BarCode"] = $data["EvnLabRequest_BarCode"];
		}
		if (isset($data["EvnLabSample_Num"])) {
			$where[] = "ves.EvnLabSample_Num ilike '%'||:EvnLabSample_Num||'%'";
			$p["EvnLabSample_Num"] = $data["EvnLabSample_Num"];
		}
		if (!empty($data["MedService_id"])) {
			$where[] = "elr.MedService_id = :MedService_id";
			$p["MedService_id"] = $data["MedService_id"];
		}
		$where = implode(" and ", $where);
		if (strlen($where)) {
			$where = $where . " and ";
		}
		$query = "
			select Analyzer_id as \"Analyzer_id\"
			from lis.v_AnalyzerWorksheet
			where AnalyzerWorksheet_id = :AnalyzerWorksheet_id
		";
		$queryParams["Analyzer_id"] = $callObject->getFirstResultFromQuery($query, $queryParams);
		if (empty($queryParams["Analyzer_id"])) {
			$queryParams["Analyzer_id"] = null;
		}
		$query = "
			select
				EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabSample_Num as \"EvnLabSample_Num\",
				RefMaterial_Name as \"RefMaterial_Name\",
				EvnLabSample_Comment as \"EvnLabSample_Comment\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				elr.EvnLabRequest_BarCode as \"EvnLabRequest_BarCode\",
				to_char (ves.EvnLabSample_setDT, 'dd.mm.yyyy') as \"EvnLabSample_setDT\"
			from
				v_EvnLabSample ves
				inner join dbo.v_EvnLabRequest elr on elr.EvnLabRequest_id = ves.EvnLabRequest_id
				inner join dbo.v_UslugaComplex uc on uc.UslugaComplex_id = elr.UslugaComplex_id
				inner join dbo.v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
				left join dbo.v_RefSample RefSample_id_ref on RefSample_id_ref.RefSample_id = ves.RefSample_id
				left join dbo.v_RefMaterial rm on rm.RefMaterial_id = RefSample_id_ref.RefMaterial_id
				left join lateral(
					select m_child.UslugaComplexMedService_id
					from
						v_UslugaComplexMedService m
						inner join v_UslugaComplexMedService m_child on m.UslugaComplexMedService_id = m_child.UslugaComplexMedService_pid
					where m.MedService_id = elr.MedService_id
					  and m.UslugaComplex_id = elr.UslugaComplex_id
					  and m_child.RefSample_id = ves.RefSample_id
					  and not exists(
					      select AnalyzerTest_id
					      from lis.v_AnalyzerTest
					      where UslugaComplex_id = m_child.UslugaComplex_id and Analyzer_id = :Analyzer_id
					  )
					limit 1
                ) as UC_SOST_NOTINANALYZER on true
			where
				{$where}
			  uc.UslugaComplex_id in (
					select at.UslugaComplex_id
					from lis.v_AnalyzerTest at
					where at.Analyzer_id = :Analyzer_id
			  )
			  and UC_SOST_NOTINANALYZER.UslugaComplexMedService_id is null
			  and not exists (
				select 1
				from
					lis.v_AnalyzerWorksheetEvnLabSample ws
					inner join lis.v_AnalyzerWorksheet w ON ws.AnalyzerWorksheet_id = w.AnalyzerWorksheet_id
				where ws.EvnLabSample_id = ves.EvnLabSample_id
				  and w.AnalyzerWorksheetStatusType_id IN ( 1, 2 ) 
			  )
			  and not exists (
				select 1
				from v_UslugaTest ut
				where ut.EvnLabSample_id = ves.EvnLabSample_id
				  and ut.UslugaTest_ResultValue is not null
				  and ut.UslugaTest_ResultValue <> ''
			  )
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Загрузка пробы-кандидата
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadBarCode(EvnLabSample_model $callObject, $data)
	{
		$queryParams = ["AnalyzerWorksheet_id" => $data["AnalyzerWorksheet_id"]];
		$filter = "(1=1)";
		if (isset($data["EvnLabSample_Num"])) {
			$filter .= "and ves.EvnLabSample_Num = :EvnLabSample_Num ";
			$queryParams["EvnLabSample_Num"] = $data["EvnLabSample_Num"];
		}
		/**
		ucc.uslugacomplex_id in (
		SELECT uc.UslugaComplex_id
		FROM v_UslugaComplex uc
		WHERE uc.UslugaComplex_Code IN (
		SELECT vuc.UslugaComplex_Code
		FROM
		lis.v_AnalyzerWorksheet w
		LEFT JOIN lis.v_AnalyzerWorksheetType wt ON w.AnalyzerWorksheetType_id = wt.AnalyzerWorksheetType_id
		LEFT JOIN lis.v_AnalyzerTestWorksheetType twt ON wt.AnalyzerWorksheetType_id = twt.AnalyzerWorksheetType_id
		INNER JOIN lis.v_AnalyzerTestUslugaComplex tu ON twt.AnalyzerTest_id = tu.AnalyzerTest_id
		INNER JOIN dbo.v_UslugaComplex vuc ON tu.UslugaComplex_id = vuc.UslugaComplex_id
		WHERE w.AnalyzerWorksheet_id = 1
		)
		) AND
		 */
		//v_UslugaComplexComposition ucc
		$query = "       								
			select
	            EvnLabSample_Num as \"EvnLabSample_Num\",
	            EvnLabSample_id as \"EvnLabSample_id\"
	        from v_EvnLabSample ves
	        where {$filter} and
	          NOT EXISTS (
	              SELECT 1
	              FROM
	                  lis.v_AnalyzerWorksheetEvnLabSample ws
	                  INNER JOIN lis.v_AnalyzerWorksheet w ON ws.AnalyzerWorksheet_id = w.AnalyzerWorksheet_id
	              WHERE ws.EvnLabSample_id = ves.EvnLabSample_id
	                AND w.AnalyzerWorksheetStatusType_id in (1, 2)
	          )
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение списка взятых проб из лис с результатами (для формы 250у)
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadSampleListForm250(EvnLabSample_model $callObject, $data)
	{
		$filterUslugaTest = "";
		if (!empty($data["UslugaComplex_id"])) {
			$funcParams = [
				"UslugaComplex_pid" => $data["UslugaComplex_id"],
				"MedService_id" => $data["MedService_id"]
			];
			$uslugaContent = $callObject->getUslugaComplexContent($funcParams);
			$filterUslugaTest = (is_array($uslugaContent))
				? " WHERE ut.UslugaComplex_id IN (" . implode(",", $uslugaContent) . ")"
				: " WHERE ut.UslugaComplex_id = :UslugaComplex_id ";
		}
		$query = "
			SELECT
				els.EvnLabSample_id as \"EvnLabSample_id\",
				els.EvnLabSample_DelivDT as \"EvnLabSample_DelivDT\",
				els.EvnLabSample_Num as \"EvnLabSample_Num\",
				els.EvnLabSample_Comment as \"EvnLabSample_Comment\",
				els.LpuSection_did as \"LpuSection_did\",
				els.Person_id as \"Person_id\",
				els.EvnLabRequest_id as \"EvnLabRequest_id\",
				elr.EvnLabRequest_Ward as \"EvnLabRequest_Ward\",
				COALESCE('Палата '||elr.EvnLabRequest_Ward||'; ', '') || COALESCE('Отделение '||sec.LpuSection_Name, '') as \"LpuSection\",
				elr.Diag_id as \"Diag_id\",
				diag.Diag_Name as \"DiagName\",
				COALESCE(ps.Person_SurName||' ', '') || COALESCE(ps.Person_FirName||' ', '') || COALESCE(ps.Person_SecName, '') as \"PatientName\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				CASE ut.UslugaTest_ResultApproved	
					WHEN 2 THEN ut.UslugaTest_ResultValue || COALESCE(' '||ut.UslugaTest_ResultUnit, '')	
					ELSE '+'
				END as \"UslugaTest_ResultValue\",
				ut.UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				ut.UslugaComplex_id as \"UslugaComplex_id\"
			FROM (
					SELECT
						els0.EvnLabSample_id,
						els0.EvnLabSample_DelivDT,
						els0.EvnLabSample_Num,
						els0.EvnLabSample_Comment,
						els0.LpuSection_did,
						els0.Person_id,
						els0.EvnLabRequest_id
					FROM v_EvnLabSample els0 
					WHERE els0.MedService_id = :MedService_id
					  AND els0.EvnLabSample_setDT >= to_timestamp(:EvnLabSample_DelivDT, 'YYYY.MM.DD HH24:MI:SS')
					  AND els0.EvnLabSample_setDT < INTERVAL '1 day' + to_timestamp(:EvnLabSample_DelivDT, 'YYYY.MM.DD HH24:MI:SS')
			) els
			LEFT JOIN dbo.v_EvnLabRequest elr ON els.EvnLabRequest_id = elr.EvnLabRequest_id
			LEFT JOIN dbo.v_UslugaTest ut ON ut.EvnLabSample_id = els.EvnLabSample_id AND ut.UslugaTest_pid IS NOT NULL
			LEFT JOIN dbo.v_PersonState ps ON ps.Person_id = els.Person_id
			LEFT JOIN dbo.v_Diag diag ON diag.Diag_id = elr.Diag_id
			LEFT JOIN dbo.v_EvnDirection_all da ON da.EvnDirection_id = elr.EvnDirection_id
			LEFT JOIN dbo.v_LpuSection sec ON sec.LpuSection_id = da.LpuSection_id
			{$filterUslugaTest}
		";
		$queryParams = [
			"MedService_id" => $data["MedService_id"],
			"EvnLabSample_DelivDT" => $data["Date"],
			"UslugaComplex_id" => $data["UslugaComplex_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение проб для сортировки по патологиям
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function loadPathologySamples(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabSample_IsOutNorm as \"EvnLabSample_IsOutNorm\"
			from v_EvnLabSample
			where EvnLabSample_id in({$data["EvnLabSample_id"]})
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		return $result->result_array();
	}

    public static function loadResearchHistory(EvnLabSample_model $callObject, $data)
    {
        $queryParams = [
            'EvnLabSample_id' => $data['EvnLabSample_id'],
            'MinDate' => $data['MinDate'],
            'MaxDate' => $data['MaxDate'],
            'Server_id' => $data['Server_id']
        ];

        $codes = $data['Codes'];

        $where = "";
        if ($data['MinDate'] != "" && $data['MaxDate'] != "") {
            $where = "and ut.UslugaTest_setDT BETWEEN cast(:MinDate as datetime) AND cast(:MaxDate as datetime)";
        }
        $query = "
			select
				ut.UslugaComplex_id as \"UslugaComplex_id\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				cast(ut.UslugaTest_ResultLower as varchar) || ' - ' || cast(ut.UslugaTest_ResultUpper as varchar) as \"UslugaTest_RefValues\",
				to_char(ut.UslugaTest_CheckDT, 'dd.mm.yyyy') as \"UslugaTest_CheckDT\",
				ut.UslugaTest_Comment as \"UslugaTest_Comment\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				(uc.UslugaComplex_Code || ' ' || uc.UslugaComplex_Name) as \"UslugaComplex_Name\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				(select count(*) from v_UslugaTestHistory where UslugaTest_id = ut.UslugaTest_id) as \"UslugaTestHistory_Count\"
			from 
			    v_UslugaTest ut
                inner join v_Lpu lpu on lpu.Lpu_id = ut.Lpu_id
                inner join v_UslugaComplex uc on uc.UslugaComplex_id = ut.UslugaComplex_id
                inner join v_EvnLabSample els on els.EvnLabSample_id = ut.EvnLabSample_id
			where
				els.Person_id = select Person_id from v_EvnLabSample where EvnLabSample_id = :EvnLabSample_id limit 1
            and 
                ut.UslugaTest_CheckDT is not NULL
            and 
                uc.UslugaComplex_id in($codes)
            $where
			order by 
                ut.UslugaComplex_id ASC, 
                ut.UslugaTest_CheckDT DESC
		";

        $res = $callObject->db->query($query, $queryParams);
        $res = $res->result('array');

        if (count($res) === 0) {
            return array('Error_Msg' => 'Отсутствуют данные для отображения');
        }
        return $res;
    }

	public static function loadLabResearchResultHistory(EvnLabSample_model $callObject, $data) {
		$queryParams = array(
			'UslugaTest_id' => $data['UslugaTest_id']
		);

		$query = "
			select
				uth.UslugaTestHistory_id as \"UslugaTestHistory_id\",
				(uc.UslugaComplex_Code || ' ' || uc.UslugaComplex_Name) as \"UslugaComplex_Name\",
				uth.UslugaTest_id as \"UslugaTest_id\",
				uth.UslugaTestHistory_Result as \"UslugaTestHistory_Result\",
				uth.UslugaTestHistory_Comment as \"UslugaTestHistory_Comment\",
				to_char(uth.UslugaTestHistory_CheckDT, 'dd.mm.yyyy hh24:mi:ss') as \"UslugaTestHistory_CheckDT\",
				mp.Person_Fio as \"Person_Fio\"
			from v_UslugaTestHistory uth
				left join v_pmUserCache pmu on pmu.PMUser_id = uth.pmUser_insID
				left join v_MedPersonal mp on mp.MedPersonal_id = pmu.MedPersonal_id
					and mp.Lpu_id = pmu.Lpu_id
				inner join v_UslugaTest ut on ut.UslugaTest_id = uth.UslugaTest_id
				inner join UslugaComplex uc on uc.UslugaComplex_id = ut.UslugaComplex_id
			where uth.UslugaTest_id = :UslugaTest_id
			order by uth.UslugaTestHistory_CheckDT ASC
		";

		$res = $callObject->db->query($query, $queryParams);
		$res = $res->result('array');

		if (count($res) === 0) {
			return array('Error_Msg' => 'Отсутствуют данные для отображения');
		}
		return $res;
	}
}
