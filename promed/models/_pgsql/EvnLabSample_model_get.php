<?php

class EvnLabSample_model_get
{
	/**
	 * Получение количества проб из лис с результатами
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function getEvnLabSampleFromLisWithResultCount(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select count(els.EvnLabSample_id) as cnt
			from v_EvnLabSample els
			where els.MedService_id = :MedService_id
			  and exists(
			      select Link_id
			      from lis.v_Link
			      where link_object = 'EvnLabSample' and object_id = els.EvnLabSample_id
			  ) -- отправлялась на анализатор
			  and els.EvnLabSample_StudyDT IS NOT NULL -- получен результат
			  and els.EvnLabSample_StudyDT = dbo.tzgetdate()
			limit 1
		";
		$count = $callObject->getFirstResultFromQuery($query, $data);
		if ($count === false) {
			throw new Exception("Ошибка при получении количества проб из лис с результатами");
		}
		return [["success" => true, "cnt" => $count]];
	}

	/**
	 * Получение нового номера пробы
	 * @param $data
	 * @param int $beginningOfNumbering
	 * @return array|string
	 */
	public static function getNewLabSampleNum(EvnLabSample_model $callObject, $data, $beginningOfNumbering = 0)
	{
		//Получаем список незакрытых проб данной ЛПУ и формируем из них массив
		$in_work_samples = $callObject->getInWorkSamples(["MedService_id" => $data["MedService_id"]]);
		$in_work_samples_array = [];
		foreach ($in_work_samples as $key => $value) {
			foreach ($value as $key2 => $value2) {
				array_push($in_work_samples_array, substr($value2, 5, 8));
			}
		}
		$callObject->elslog->add("Получили список незакрытых проб в службе и сформировали из них массив | Всего незакрытых проб = " . count($in_work_samples));
		// Получаем индивидуальный номер лаборатории
		$resp_ms = $callObject->getMedServiceCode(["MedService_id" => $data["MedService_id"]]);
		// Генерируем номер
		return $callObject->generateLabSampleNum([
			"Lpu_id" => $data["Lpu_id"],
			"MedService_id" => $data["MedService_id"],
			"MedService_Code" => $resp_ms[0]["MedService_Code"]
		], $in_work_samples_array, 0, $beginningOfNumbering);
	}

	/**
	 * Получение нормальных значений для качественного текста
	 * @param $AnalyzerTestRefValues_id
	 * @return false|string
	 */
	public static function getRefValuesForQualitativeTest(EvnLabSample_model $callObject, $AnalyzerTestRefValues_id)
	{
		$array = [];
		$query = "
			select qtaat.QualitativeTestAnswerAnalyzerTest_Answer as \"QualitativeTestAnswerAnalyzerTest_Answer\"
			from
				lis.v_QualitativeTestAnswerReferValue qtarv
				inner join lis.v_QualitativeTestAnswerAnalyzerTest qtaat on qtaat.QualitativeTestAnswerAnalyzerTest_id = qtarv.QualitativeTestAnswerAnalyzerTest_id
			where qtarv.AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, ["AnalyzerTestRefValues_id" => $AnalyzerTestRefValues_id]);
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as $respone) {
				$array[] = toUtf($respone["QualitativeTestAnswerAnalyzerTest_Answer"]);
			}
		}
		return json_encode($array);
	}

	/**
	 * метод-кастыль, чтобы обойти отсутствие v_morbus на lis db
	 * @param $response
	 * @return mixed
	 */
	public static function getMorbusNames(EvnLabSample_model $callObject, $response)
	{
		$morbus_ids = [];
		foreach ($response as $value) {
			$morbus_ids[] = trim($value["Morbus_id_Name"]);
		}
		if (empty($morbus_ids[0])) {
			foreach ($response as $key => $value) {
				$response[$key]["Morbus_id_Name"] = null;
			}
			return $response;
		}
		$morbus_idsString = implode(", ", $morbus_ids);
		$selectString = "
			Morbus_id as \"Morbus_id\",
			Morbus_Name as \"Morbus_Name\"
		";
		$fromString = "v_Mordus";
		$whereString = "where v_Morbus_id in ({$morbus_idsString})";
		$query = "
			select {$selectString}
			from {$fromString}
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		$result = $result->result_array();
		$names = [];
		foreach ($result as $name) {
			$names[$name["Morbus_id"]] = $name["Morbus_Name"];
		}
		foreach ($response as $key => $value) {
			$response[$key]["Morbus_id_Name"] = $names[$value["Morbus_id_Name"]];
		}
		return $response;
	}

	/**
	 * Получает данные для формирования заявки ЛИС и последующей отправки в ЛИС
	 * @param $data
	 * @return mixed|bool
	 */
	public static function getRequest2Data(EvnLabSample_model $callObject, $data)
	{
		// Запрос надо не то чтобы проверять,а выстраивать логику по новой по нормальному проектированию, и по новой смотрим есть ли сохраненный в заявке состав услуги:
		$query = "
			select elruc.UslugaComplex_id as \"UslugaComplex_id\"
			from
				v_EvnLabSample els
				inner join v_EvnLabRequestUslugaComplex elruc on elruc.EvnLabRequest_id = els.EvnLabRequest_id
			where els.EvnLabSample_id = :EvnLabSample_id
			limit 1
		";
		$queryParams = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		$UslugaComplex_id = $callObject->getFirstResultFromQuery($query, $queryParams);
		$filter_tests = (!empty($UslugaComplex_id))
			?"
				and uc.UslugaComplex_id in (
					select UslugaComplex_id
					from v_EvnLabRequestUslugaComplex
					where EvnLabRequest_id = lr.EvnLabRequest_id
				)
			"
			:"";
		$query = "
			select
				EvnLabSample.EvnClass_id as \"EvnClass_id\",
				EvnLabSample.EvnClass_Name as \"EvnClass_Name\",
				EvnLabSample.EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabSample.EvnLabSample_setDate as \"EvnLabSample_setDate\",
				EvnLabSample.EvnLabSample_settime as \"EvnLabSample_settime\",
				EvnLabSample.EvnLabSample_didDate as \"EvnLabSample_didDate\",
				EvnLabSample.EvnLabSample_diDTime as \"EvnLabSample_diDTime\",
				EvnLabSample.EvnLabSample_disDate as \"EvnLabSample_disDate\",
				EvnLabSample.EvnLabSample_distime as \"EvnLabSample_distime\",
				EvnLabSample.EvnLabSample_pid as \"EvnLabSample_pid\",
				EvnLabSample.EvnLabSample_rid as \"EvnLabSample_rid\",
				EvnLabSample.Lpu_id as \"Lpu_id\",
				EvnLabSample.Server_id as \"Server_id\",
				EvnLabSample.PersonEvn_id as \"PersonEvn_id\",
				EvnLabSample.EvnLabSample_setDT as \"EvnLabSample_setDT\",
				EvnLabSample.EvnLabSample_disDT as \"EvnLabSample_disDT\",
				EvnLabSample.EvnLabSample_didDT as \"EvnLabSample_didDT\",
				EvnLabSample.EvnLabSample_insDT as \"EvnLabSample_insDT\",
				EvnLabSample.EvnLabSample_updDT as \"EvnLabSample_updDT\",
				EvnLabSample.EvnLabSample_Index as \"EvnLabSample_Index\",
				EvnLabSample.EvnLabSample_Count as \"EvnLabSample_Count\",
				EvnLabSample.pmUser_insID as \"pmUser_insID\",
				EvnLabSample.pmUser_updID as \"pmUser_updID\",
				EvnLabSample.Person_id as \"Person_id\",
				EvnLabSample.Morbus_id as \"Morbus_id\",
				EvnLabSample.EvnLabSample_IsSigned as \"EvnLabSample_IsSigned\",
				EvnLabSample.pmUser_signID as \"pmUser_signID\",
				EvnLabSample.EvnLabSample_signDT as \"EvnLabSample_signDT\",
				EvnLabSample.EvnLabSample_IsArchive as \"EvnLabSample_IsArchive\",
				EvnLabSample.EvnLabSample_Guid as \"EvnLabSample_Guid\",
				EvnLabSample.EvnLabSample_IndexMinusOne as \"EvnLabSample_IndexMinusOne\",
				EvnLabSample.EvnStatus_id as \"EvnStatus_id\",
				EvnLabSample.EvnLabSample_StatusDate as \"EvnLabSample_StatusDate\",
				EvnLabSample.EvnLabSample_IsTransit as \"EvnLabSample_IsTransit\",
				EvnLabSample.EvnLabRequest_id as \"EvnLabRequest_id\",
				EvnLabSample.EvnLabSample_Num as \"EvnLabSample_Num\",
				EvnLabSample.RefSample_id as \"RefSample_id\",
				EvnLabSample.Lpu_did as \"Lpu_did\",
				EvnLabSample.LpuSection_did as \"LpuSection_did\",
				EvnLabSample.MedPersonal_did as \"MedPersonal_did\",
				EvnLabSample.MedPersonal_sdid as \"MedPersonal_sdid\",
				EvnLabSample.EvnLabSample_DelivDT as \"EvnLabSample_DelivDT\",
				EvnLabSample.Lpu_aid as \"Lpu_aid\",
				EvnLabSample.LpuSection_aid as \"LpuSection_aid\",
				EvnLabSample.MedPersonal_aid as \"MedPersonal_aid\",
				EvnLabSample.MedPersonal_said as \"MedPersonal_said\",
				EvnLabSample.LabSampleDefectiveType_id as \"LabSampleDefectiveType_id\",
				EvnLabSample.EvnLabSample_StudyDT as \"EvnLabSample_StudyDT\",
				EvnLabSample.EvnLabSample_Comment as \"EvnLabSample_Comment\",
				EvnLabSample.MedService_id as \"MedService_id\",
				EvnLabSample.LabSampleStatus_id as \"LabSampleStatus_id\",
				EvnLabSample.DefectCauseType_id as \"DefectCauseType_id\",
				EvnLabSample.EvnLabSample_IsLis as \"EvnLabSample_IsLis\",
				EvnLabSample.Analyzer_id as \"Analyzer_id\",
				EvnLabSample.EvnLabSample_Test as \"EvnLabSample_Test\",
				EvnLabSample.EvnLabSample_IsOutNorm as \"EvnLabSample_IsOutNorm\",
				EvnLabSample.EvnLabSample_Barcode as \"EvnLabSample_Barcode\",
				EvnLabSample.MedService_did as \"MedService_did\",
				EvnLabSample.EvnLabSample_AnalyzerDate as \"EvnLabSample_AnalyzerDate\",
				EvnLabSample.MedService_sid as \"MedService_sid\",
				ps.Person_id as \"Person_id\",
				m.MedPersonal_Code as \"MedPersonal_Code\",
				m.Person_FIO as \"MedPersonal_FIO\",
				COALESCE(ps.Person_SurName,'') as \"Person_SurName\",
				COALESCE(ps.Person_FirName,'') as \"Person_FirName\",
				COALESCE(ps.Person_SecName,'') as \"Person_SecName\",
				date_part('year', ps.Person_BirthDay) as \"BirthDay_Year\",
				date_part('month', ps.Person_BirthDay) as \"BirthDay_Month\",
				date_part('day', ps.Person_BirthDay) as \"BirthDay_Day\",
				Sex_Code as \"Sex_Code\",
				addr.KLCountry_Name as \"KLCountry_Name\",
				addr.KLCity_Name as \"KLCity_Name\",
				addr.KLStreet_Name as \"KLStreet_Name\",
				addr.Address_House as \"Address_House\",
				addr.Address_Flat as \"Address_Flat\",
				case when p.PolisType_id = 4 then '' else p.Polis_Ser end as \"Polis_Ser\",
				case when p.PolisType_id = 4 then ps.Person_EdNum else p.Polis_Num end as \"Polis_Num\",
				os.OrgSmo_Nick as \"OrgSmo_Nick\",
				target.target_id as \"target_id\",
				tests.ids as \"test_ids\",
				rm.RefMaterial_id as \"biomaterial_id\"
			from
				v_EvnLabSample EvnLabSample
				left join v_PersonState ps on ps.Person_id = EvnLabSample.Person_id
				left join v_address_all addr on addr.address_id = ps.UAddress_id
				left join v_sex s on s.sex_id = ps.sex_id
				left join v_polis p on p.polis_id = ps.polis_id
				left join v_orgsmo os on os.orgsmo_id = p.orgsmo_id
				left join lateral (
					select MedPersonal_Code, Person_FIO
					from v_medpersonal m
					where m.medpersonal_id = EvnLabSample.medpersonal_aid
                    limit 1
				) as m on true
				left join v_EvnLabRequest lr on lr.EvnLabRequest_id = EvnLabSample.EvnLabRequest_id
				left join v_evnUslugaPar eup on eup.EvnDirection_id = lr.EvnDirection_id
				left join v_UslugaComplex u on eup.UslugaComplex_id = u.UslugaComplex_id
				left join v_UslugaComplex uc2011 on u.UslugaComplex_2011id = uc2011.UslugaComplex_id
				left join lateral (
					Select id as target_id
					from lis._target target
					where target.code = uc2011.UslugaComplex_Code
					limit 1
				) as target on true
				left join dbo.v_RefSample r on r.RefSample_id = EvnLabSample.RefSample_id
				left join dbo.v_RefMaterial rm on r.RefMaterial_id = rm.RefMaterial_id
				left join lateral (
                    select (
                        select distinct
                            string_agg(t.id, ',')
                        from
                            v_UslugaTest UslugaTest
                            inner join v_UslugaComplex uc ON UslugaTest.UslugaComplex_id = uc.UslugaComplex_id
                            inner join v_UslugaComplexMedService ucms on ucms.UslugaComplex_id = uc.UslugaComplex_id and ucms.MedService_id = lr.MedService_id
                            inner join lis.v_AnalyzerTest at_child on at_child.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
                            inner join lis.v_AnalyzerTest at on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
                            inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id and (a.Analyzer_Code <> '000' or a.pmUser_insID <> 1)
                            inner join dbo.v_UslugaComplex ucgost ON ucgost.UslugaComplex_id = uc.UslugaComplex_2011id
                            left join lateral (
                                select id
                                from lis.v__test t
                                where t.code = ucgost.UslugaComplex_Code
                            ) as t on true
                        where UslugaTest_pid = eup.EvnUslugaPar_id
                          and EvnLabSample_id = EvnLabSample.EvnLabSample_id
                          {$filter_tests}
                    ) as ids
				) as tests on true
		    where EvnLabSample.EvnLabSample_id = :EvnLabSample_id
		";
		/**@var CI_DB_result $result */
		$queryParams = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result_array();
		return $response[0];
	}

	/**
	 * Получает данные для формирования заявки АсМло и последующей отправки в АсМло
	 * @param $data
	 * @return mixed|bool
	 */
	public static function getRequest2DataForAsMlo(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				els.EvnLabSample_id as \"EvnLabSample_id\",
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				COALESCE(els.MedService_id, elr.MedService_id) as \"MedService_id\",
				els.EvnLabSample_Num as \"EvnLabSample_Num\",
				els.EvnLabSample_BarCode as \"EvnLabSample_BarCode\",
				els.Analyzer_id as \"Analyzer_id\",
				rm.RefMaterial_id as \"RefMaterial_id\",
				rm.RefMaterial_Code as \"RefMaterial_Code\",
				els.Person_id as \"Person_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				ps.Sex_id as \"Sex_id\",
				to_char(ps.Person_BirthDay, 'YYYY-MM-DD') as \"Person_BirthDay\",
				els.EvnLabSample_DelivDT as \"EvnLabSample_DelivDT\",
				elr.Lpu_id as \"Lpu_id\",
				l.Lpu_Nick as \"Lpu_Nick\",
				MP.Person_Fio as \"MedPersonal_Fio\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				WEIGHT.PersonWeight_Weight as \"PersonWeight_Weight\",
				pa.Address_Address as \"Address_Address\"
			from
				v_EvnLabSample els
				left join v_PersonState ps on ps.Person_id = els.Person_id
				inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = elr.EvnDirection_id
				left join v_LpuSection ls on ls.LpuSection_id = ed.LpuSection_id
				left join v_MedPersonal mp on mp.MedPersonal_id = ed.MedPersonal_id
				left join v_Lpu l on l.Lpu_id = elr.Lpu_id
				left join v_RefSample r on r.RefSample_id = els.RefSample_id
				left join v_RefMaterial rm on r.RefMaterial_id = rm.RefMaterial_id
				left join v_Address pa on pa.Address_id = ps.PAddress_id
				left join lateral (
					select
						case when pw.Okei_id = 37
					    	then FLOOR(PersonWeight_Weight * 1000)
						    else FLOOR(PersonWeight_Weight)
						end as PersonWeight_Weight
					from v_PersonWeight pw
					where pw.Person_id = ps.person_id
					order by PersonWeight_setDT desc
					limit 1
				) as WEIGHT on true
		    where els.EvnLabSample_id = :EvnLabSample_id
			limit 1
		";
		$queryParams = ["EvnLabSample_id" => $data["EvnLabSample_id"]];
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_tests
		 */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		if (!empty($resp[0]["EvnLabSample_id"])) {
			$resp[0]["tests"] = [];
			$resp[0]["targets"] = [];
			$filter = "";
			if (!empty($data["onlyNew"]) && $data["onlyNew"] == 2) {
				$filter .= " and COALESCE(ut.UslugaTest_ResultValue, '') = ''";
			}
			// Получаем коды тестов
			$query = "
				select distinct
					ucgost.UslugaComplex_Code as \"UslugaComplex_Code\",
					ucpgost.UslugaComplex_Code as \"ParentUslugaComplex_Code\"
				from
					v_UslugaTest ut
					left join v_EvnUslugaPar eupp on eupp.EvnUslugaPar_id = ut.UslugaTest_pid
					left join v_UslugaComplex uc ON ut.UslugaComplex_id = uc.UslugaComplex_id
					left join v_UslugaComplex ucp ON eupp.UslugaComplex_id = ucp.UslugaComplex_id
					left join v_UslugaComplexMedService ucms on ucms.UslugaComplex_id = uc.UslugaComplex_id
					left join lis.v_AnalyzerTest at_child on at_child.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
					left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
					left join lis.v_Analyzer a on a.Analyzer_id = coalesce(at.Analyzer_id, at_child.Analyzer_id)
				  	left join lateral (
						select UslugaComplex_Code
						from v_UslugaComplex ucgost
						where ucgost.UslugaComplex_id = uc.UslugaComplex_2011id
						limit 1
					) as ucgost on true
					left join lateral(
						select UslugaComplex_Code
						from v_UslugaComplex ucpgost
						where ucpgost.UslugaComplex_id = ucp.UslugaComplex_2011id
						limit 1
					) as ucpgost on true
				where ut.EvnLabSample_id = :EvnLabSample_id
				  and ucms.MedService_id = :MedService_id
				  and (a.Analyzer_Code <> '000' or a.pmUser_insID <> 1)
				  {$filter}
			";
			$queryParams = [
				"EvnLabSample_id" => $data["EvnLabSample_id"],
				"MedService_id" => $resp[0]["MedService_id"],
				"EvnLabRequest_id" => $resp[0]["EvnLabRequest_id"]
			];
			$result_tests = $callObject->db->query($query, $queryParams);
			if (is_object($result_tests)) {
				$resp_tests = $result_tests->result_array();
				foreach ($resp_tests as $test) {
					if (!in_array($test["UslugaComplex_Code"], $resp[0]["tests"])) {
						$resp[0]["tests"][] = $test["UslugaComplex_Code"];
					}
					if (!in_array($test["ParentUslugaComplex_Code"], $resp[0]["targets"])) {
						$resp[0]["targets"][] = $test["ParentUslugaComplex_Code"];
					}
				}
			}
			return $resp[0];
		}
		return false;
	}

	/**
	 * Достаем услуги для проб
	 * @param $data
	 * @return array|false
	 */
	public static function getSampleUsluga(EvnLabSample_model $callObject, $data)
	{
		$EvnLabSample_ids = implode(",", json_decode($data["EvnLabSample_id"]));
		$filter = "els.EvnLabSample_id in ({$EvnLabSample_ids})";
		$query = "
			select distinct
				els.EvnLabSample_id as \"EvnLabSample_id\",
				ucp.UslugaComplex_id as \"UslugaComplex_id\",
				COALESCE(analyzertest.UslugaComplex_ParentName, ucp.UslugaComplex_Name) as \"ResearchName\"
			from
				v_EvnLabSample els
				inner join v_UslugaTest ut on ut.EvnLabSample_id = els.EvnLabSample_id
				inner join v_EvnUslugaPar eup on ut.UslugaTest_pid = eup.EvnUslugaPar_id and eup.EvnDirection_id is not null
				left join v_UslugaComplex ucp on ucp.UslugaComplex_id = eup.UslugaComplex_id
				left join lateral(
					select
						at_child.AnalyzerTest_id,
						at_child.AnalyzerTest_SysNick,
						at_child.AnalyzerTest_SortCode,
						uctest.UslugaComplex_Name,
						ucms_parent.UslugaComplex_Name as UslugaComplex_ParentName
					from
						lis.v_AnalyzerTest at_child
						left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = COALESCE(at_child.AnalyzerTest_pid, at.AnalyzerTest_id)
						inner join v_UslugaComplexMedService ucms_at on at_child.UslugaComplexMedService_id = ucms_at.UslugaComplexMedService_id
						left join v_UslugaComplexMedService ucms_parent on ucms_parent.UslugaComplexMedService_id = COALESCE(ucms_at.UslugaComplexMedService_pid, ucms_at.UslugaComplexMedService_id)
						inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
						left join v_UslugaComplex uctest on uctest.UslugaComplex_id = at_child.UslugaComplex_id
					where ucms_at.UslugaComplex_id = ut.UslugaComplex_id
					  and ucms_parent.UslugaComplex_id = eup.UslugaComplex_id
					  and ucms_at.MedService_id = els.MedService_id
					  and COALESCE(at.AnalyzerTest_IsNotActive, 1) = 1
					  and COALESCE(a.Analyzer_IsNotActive, 1) = 1
					  and (at.AnalyzerTest_endDT >= dbo.tzgetdate() or at.AnalyzerTest_endDT is null)
					  and (at_child.AnalyzerTest_endDT >= dbo.tzgetdate() or at_child.AnalyzerTest_endDT is null)
					  and (uctest.UslugaComplex_endDT >= dbo.tzgetdate() or uctest.UslugaComplex_endDT is null)
					order by at_child.AnalyzerTest_pid desc
                    limit 1
				) as analyzertest on true
			where {$filter}
		";
		return $callObject->queryResult($query);
	}

	/**
	 * Получение списка результатов пробы
	 * @param $data
	 * @return array|bool
	 */
	public static function getLabSampleResultGrid(EvnLabSample_model $callObject, $data)
	{
		if (!empty($data["EvnLabSample_id"])) {
			if (empty($data["EvnDirection_id"])) {
				$query = "
					select elr.EvnDirection_id as \"EvnDirection_id\"
					from
						v_EvnLabSample els
						inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
					where els.EvnLabSample_id = :EvnLabSample_id
				";
				$data["EvnDirection_id"] = $callObject->getFirstResultFromQuery($query, $data);
				if (empty($data["EvnDirection_id"])) {
					$data["EvnDirection_id"] = null;
				}
			}
		}
		$beforeQuery = "";
		$filter = "";
		$inPrescr = "1=1";
		if (empty($data["ingorePrescr"])) {
			// проверяем сохранен ли для исследования состав
			$query = "
				select elruc.EvnLabRequestUslugaComplex_id as \"EvnLabRequestUslugaComplex_id\"
				from
					v_EvnLabRequestUslugaComplex elruc
					inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = elruc.EvnLabRequest_id
				where elr.EvnDirection_id = :EvnDirection_id
				limit 1
			";
			$EvnLabRequestUslugaComplex_id = $callObject->getFirstResultFromQuery($query, $data);
			if (!empty($EvnLabRequestUslugaComplex_id)) {
				$beforeQuery = "
					with elruc as (
						select
							elruc.UslugaComplex_id,
							elruc.EvnLabSample_id,
							elruc.EvnUslugaPar_id
						from
							v_EvnLabRequestUslugaComplex elruc
							inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = elruc.EvnLabRequest_id
						where elr.EvnDirection_id = :EvnDirection_id
					)
				";
				$inPrescr = "
					uc.UslugaComplex_id in (
						select elruc.UslugaComplex_id
						from elruc
						where COALESCE(elruc.EvnLabSample_id, els.EvnLabSample_id) = els.EvnLabSample_id
						  and COALESCE(elruc.EvnUslugaPar_id, eup.EvnUslugaPar_id) = eup.EvnUslugaPar_id
					)
				";
			}
		}
		if (!empty($data["UslugaComplex_ids"])) {
			$filter .= "
				and uc.UslugaComplex_id IN (
					" . implode(",", $data["UslugaComplex_ids"]) . "
				)
			";
		}
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"EvnDirection_id" => $data["EvnDirection_id"],
			"EvnLabSample_id" => $data["EvnLabSample_id"]
		];
		if (!empty($data["needtests"])) {
			$uci = 0;
			$ucfilter = "";
			foreach ($data["needtests"] as $test) {
				$uci++;
				if (!empty($ucfilter)) {
					$ucfilter .= " or ";
				}
				$ucfilter .= "(uc.UslugaComplex_id = :UslugaComplex{$uci}_id and eup.EvnUslugaPar_id = :EvnUslugaPar{$uci}_pid)";
				$queryParams["UslugaComplex{$uci}_id"] = $test["UslugaComplex_id"];
				$queryParams["EvnUslugaPar{$uci}_pid"] = $test["UslugaTest_pid"];
			}
			if (!empty($ucfilter)) {
				$filter .= " and ({$ucfilter})";
			}
		}
		if (!empty($data["EvnUslugaPar_pid"])) {
			$filter .= "
				and eup.EvnUslugaPar_id = :EvnUslugaPar_pid
			";
			$queryParams["EvnUslugaPar_pid"] = $data["EvnUslugaPar_pid"];
		}
		$evnUslugaParBySampleList = [
			1 => [0],
			2 => []
		];

		$firstJoin = [];
		$firstWhere = [];
		$firstWhere[] = "UT.EvnLabSample_id = :EvnLabSample_id";

		if(!empty($data['formMode']) && $data['formMode'] == 'ifa') {
			$firstJoin[] = "left join v_EvnUslugaPar eupp with(nolock) on eupp.EvnUslugaPar_id = ut.UslugaTest_pid";
			$firstJoin[] = "left join v_UslugaComplex uc with(nolock) ON ut.UslugaComplex_id = uc.UslugaComplex_id";
			$firstJoin[] = "left join v_UslugaComplex ucp with(nolock) ON eupp.UslugaComplex_id = ucp.UslugaComplex_id";
			$firstJoin[] = "left join v_UslugaComplexMedService ucms with(nolock) on ucms.UslugaComplex_id = uc.UslugaComplex_id";
			$firstJoin[] = "left join lis.v_AnalyzerTest AnT with(nolock) on AnT.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id";
			$firstJoin[] = "inner join v_MethodsIFAAnalyzerTest MIAT with(nolock) on MIAT.AnalyzerTest_id = AnT.AnalyzerTest_id";
			$firstJoin[] = "inner join v_MethodsIFA MI with(nolock) on MI.MethodsIFA_id = MIAT.MethodsIFA_id";

			if(!empty($data['AnalyzerTest_id'])) {
				$firstWhere[] = 'AnT.AnalyzerTest_id = :AnalyzerTest_id';
				$queryParams['AnalyzerTest_id'] = $data['AnalyzerTest_id'];
			}

			if(!empty($data['MethodsIFA_id'])) {
				$firstWhere[] = 'MI.MethodsIFA_id = :MethodsIFA_id';
				$queryParams['MethodsIFA_id'] = $data['MethodsIFA_id'];
			}
		}

		$firstJoin[] = "left join v_BactMicroProbe bmp on bmp.UslugaTest_id = ut.UslugaTest_id";
		$firstJoin[] = "join v_BactMicroProbeAntibiotic bmpa on bmpa.UslugaTest_id = ut.UslugaTest_id";
		$firstWhere[] = 'bmp.BactMicroProbe_id is null';
		$firstWhere[] = 'bmpa.BactMicroProbeAntibiotic_id is null';

		$firstJoin = implode( ' ', $firstJoin );
		$firstWhere = implode( ' and ', $firstWhere );
		// 1-й select
		$query = "
			select UslugaTest_id as \"UslugaTest_id\"
			from v_UslugaTest
				{$firstJoin}
			where
				{$firstWhere}
		";
		$resp = $callObject->queryResult($query, $queryParams);
		if (is_array($resp) && count($resp) > 0) {
			foreach ($resp as $row) {
				if (!in_array($row["UslugaTest_id"], $evnUslugaParBySampleList[1])) {
					$evnUslugaParBySampleList[1][] = $row["UslugaTest_id"];
				}
			}
		}
		// 2-й select
		$query = "
			select EvnUslugaPar_id as \"EvnUslugaPar_id\"
			from v_EvnLabRequestUslugaComplex elruc
			where elruc.EvnLabSample_id = :EvnLabSample_id
			union all
			select EvnUslugaPar_id as \"EvnUslugaPar_id\"
			from v_EvnUslugaPar eup
			where eup.EvnLabSample_id = :EvnLabSample_id
			  and not exists(
				select elruc.EvnUslugaPar_id
				from v_EvnLabRequestUslugaComplex elruc
				where elruc.EvnLabSample_id = :EvnLabSample_id
			  )
		";
		$resp = $callObject->queryResult($query, $queryParams);
		if (is_array($resp) && count($resp) > 0) {
			foreach ($resp as $row) {
				if (!in_array($row["EvnUslugaPar_id"], $evnUslugaParBySampleList[2])) {
					$evnUslugaParBySampleList[2][] = $row["EvnUslugaPar_id"];
				}
			}
		}
		$subQuery = (count($evnUslugaParBySampleList[2]) > 0)
			? "
			union all
			Select
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				uc.UslugaComplex_id as \"UslugaComplex_id\",
				els.EvnLabSample_id as \"EvnLabSample_id\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaTest_Result as \"UslugaTest_Result\",
				ut.RefValues_id as \"RefValues_id\",
				ut.Unit_id as \"Unit_id\",
				rv.RefValues_Name || COALESCE(' (' || analyzer.Analyzer_Name || ')','') as \"RefValues_Name\",
				COALESCE(ut.UslugaTest_ResultLower,'') || ' - ' || COALESCE(ut.UslugaTest_ResultUpper,'') as \"UslugaTest_ResultNorm\",
				COALESCE(ut.UslugaTest_ResultLowerCrit,'') || ' - ' || COALESCE(ut.UslugaTest_ResultUpperCrit,'') as \"UslugaTest_ResultCrit\",
				ut.UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				ut.UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				ut.UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\",
				ut.UslugaTest_ResultLowerCrit as \"UslugaTest_ResultLowerCrit\",
				ut.UslugaTest_ResultUpperCrit as \"UslugaTest_ResultUpperCrit\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				ut.UslugaTest_Comment as \"UslugaTest_Comment\",
				els.EvnLabRequest_id as \"EvnLabRequest_id\",
				case when {$inPrescr} then 2 else 1 end as \"inPrescr\",
				case
					when ut.UslugaTest_ResultApproved = 2 then 'Одобрен'
					when ut.UslugaTest_ResultValue is not null and rtrim(ut.UslugaTest_ResultValue) <> '' then 'Выполнен'
					when (ut.UslugaTest_id is not null or {$inPrescr}) then 'Назначен'
					else 'Не назначен'
				end as \"UslugaTest_Status\",
				case
					when ut.UslugaTest_ResultApproved = 2 then 0
					when ut.UslugaTest_ResultValue is not null and rtrim(ut.UslugaTest_ResultValue) <> '' then 0
					when (ut.UslugaTest_id is not null or {$inPrescr}) then 0
					else 1
				end as \"SortStatus\",
				COALESCE(analyzertest.AnalyzerTest_SortCode, 999999999) as \"AnalyzerTest_SortCode\",
				to_char (ut.UslugaTest_setDT, 'HH24:MI DD.MM.YYYY') as \"UslugaTest_setDT\",
				ut.UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				coalesce(analyzertest.AnalyzerTest_SysNick, analyzertest.UslugaComplex_Name, uc.UslugaComplex_Name) as \"UslugaComplex_Name\",
				uc.UslugaComplex_ACode as \"UslugaComplex_ACode\",
				'' as \"EvnLabSample_Comment\",
				'' as \"DefectCauseType_Name\",
				COALESCE(ucms_usluga_parent.UslugaComplex_Name, ucp.UslugaComplex_Name) as \"ResearchName\",
				eup.EvnUslugaPar_id as \"UslugaTest_pid\",
				eup.UslugaComplex_id as \"UslugaComplexTarget_id\",
				eup.EvnUslugaPar_Comment as \"EvnUslugaPar_pComment\",
				analyzertest.Analyzer_IsAutoOk as \"Analyzer_IsAutoOk\"
			from
				v_EvnLabSample els
				inner join v_EvnUslugaPar eup on eup.EvnUslugaPar_id in (" . implode(',', $evnUslugaParBySampleList[2]) . ") and eup.EvnDirection_id is not null
				left join v_UslugaComplex ucp on ucp.UslugaComplex_id = eup.UslugaComplex_id
				left join v_UslugaComplexMedService ucms_usluga_parent on eup.UslugaComplex_id = ucms_usluga_parent.UslugaComplex_id and ucms_usluga_parent.MedService_id = els.MedService_id and ucms_usluga_parent.UslugaComplexMedService_pid is null -- исследование
				left join v_UslugaComplexMedService ucms_usluga on ucms_usluga.UslugaComplexMedService_pid = ucms_usluga_parent.UslugaComplexMedService_id
				left join v_UslugaComplex uc on COALESCE(ucms_usluga.UslugaComplex_id, ucp.UslugaComplex_id, ucms_usluga_parent.UslugaComplex_id) = uc.UslugaComplex_id
				left join v_UslugaTest ut on ut.UslugaTest_pid = eup.EvnUslugaPar_id and ut.UslugaComplex_id = uc.UslugaComplex_id and ut.EvnLabSample_id = els.EvnLabSample_id -- Результат по пробе
				left join v_RefValues rv on rv.RefValues_id = ut.RefValues_id
				left join lateral(
					select
						at_child.AnalyzerTest_id,
						at_child.AnalyzerTest_SysNick,
						at_child.AnalyzerTest_SortCode,
						uctest.UslugaComplex_Name,
						a.Analyzer_IsAutoOk
					from
						lis.v_AnalyzerTest at_child
						left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
						inner join lis.v_Analyzer a on a.Analyzer_id = COALESCE(at.Analyzer_id, at_child.Analyzer_id)
						left join v_UslugaComplex uctest on uctest.UslugaComplex_id = at_child.UslugaComplex_id
					where
						at_child.UslugaComplexMedService_id = COALESCE(ucms_usluga.UslugaComplexMedService_id, ucms_usluga_parent.UslugaComplexMedService_id)
						and COALESCE(at.UslugaComplexMedService_id, 0) = COALESCE(ucms_usluga.UslugaComplexMedService_pid, 0)
						and COALESCE(at_child.AnalyzerTest_IsNotActive, 1) = 1
						and COALESCE(at.AnalyzerTest_IsNotActive, 1) = 1
						and COALESCE(a.Analyzer_IsNotActive, 1) = 1
						and (at_child.AnalyzerTest_endDT >= dbo.tzgetdate() or at_child.AnalyzerTest_endDT is null)
						and (uctest.UslugaComplex_endDT >= dbo.tzgetdate() or uctest.UslugaComplex_endDT is null)
                    limit 1
				) analyzertest on true
				left join lateral(
					select Analyzer_Name
					from
						lis.v_Analyzer a
						inner join lis.v_AnalyzerTest at on at.Analyzer_id = a.Analyzer_id
						inner join lis.v_AnalyzerTestRefValues atrv on atrv.AnalyzerTest_id = at.AnalyzerTest_id
					where atrv.RefValues_id = ut.RefValues_id
					limit 1
				) analyzer on true
			where
				els.EvnLabSample_id = :EvnLabSample_id and ut.UslugaTest_id is null
				and (
					analyzertest.AnalyzerTest_id is not null or
					exists (
						select EvnLabRequestUslugaComplex_id
						from v_EvnLabRequestUslugaComplex elruc_child
						where elruc_child.EvnUslugaPar_id = eup.EvnUslugaPar_id
						  and elruc_child.EvnLabSample_id = els.EvnLabSample_id
						  and elruc_child.UslugaComplex_id = ucp.UslugaComplex_id
					)
				)
				{$filter}
			"
			: "";
		$query = "
			{$beforeQuery}
			select
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				uc.UslugaComplex_id as \"UslugaComplex_id\",
				els.EvnLabSample_id as \"EvnLabSample_id\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaTest_Result as \"UslugaTest_Result\",
				ut.RefValues_id as \"RefValues_id\",
				ut.Unit_id as \"Unit_id\",
				rv.RefValues_Name||COALESCE(' ('||analyzer.Analyzer_Name||')', '') as \"RefValues_Name\",
				COALESCE(ut.UslugaTest_ResultLower, '')||' - '||COALESCE(ut.UslugaTest_ResultUpper, '') as \"UslugaTest_ResultNorm\",
				COALESCE(ut.UslugaTest_ResultLowerCrit, '')||' - '||COALESCE(ut.UslugaTest_ResultUpperCrit, '') as \"UslugaTest_ResultCrit\",
				ut.UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				ut.UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				ut.UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\",
				ut.UslugaTest_ResultLowerCrit as \"UslugaTest_ResultLowerCrit\",
				ut.UslugaTest_ResultUpperCrit as \"UslugaTest_ResultUpperCrit\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				ut.UslugaTest_Comment as \"UslugaTest_Comment\",
				els.EvnLabRequest_id as \"EvnLabRequest_id\",
				case when {$inPrescr} then 2 else 1 end as \"inPrescr\",
				case
					when ut.UslugaTest_ResultApproved = 2 then 'Одобрен'
					when ut.UslugaTest_ResultValue is not null and rtrim(ut.UslugaTest_ResultValue) <> '' then 'Выполнен'
					when (ut.UslugaTest_id is not null or {$inPrescr}) then 'Назначен'
					else 'Не назначен'
				end as \"UslugaTest_Status\",
				case
					when ut.UslugaTest_ResultApproved = 2 then 0
					when ut.UslugaTest_ResultValue is not null and rtrim(ut.UslugaTest_ResultValue) <> '' then 0
					when (ut.UslugaTest_id is not null or {$inPrescr}) then 0
					else 1
				end as \"SortStatus\",
				COALESCE(analyzertest.AnalyzerTest_SortCode, analyzertest2.AnalyzerTest_SortCode, 999999999) as \"AnalyzerTest_SortCode\",
				to_char(ut.UslugaTest_setDT, 'HH24:MI DD.MM.YYYY') as \"UslugaTest_setDT\",
				ut.UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				coalesce(analyzertest.AnalyzerTest_SysNick, analyzertest.UslugaComplex_Name, uc.UslugaComplex_Name) as \"UslugaComplex_Name\",
				uc.UslugaComplex_ACode as \"UslugaComplex_ACode\",
				els.EvnLabSample_Comment as \"EvnLabSample_Comment\",
				dct.DefectCauseType_Name as \"DefectCauseType_Name\",
				COALESCE(analyzertest.UslugaComplex_ParentName, ucp.UslugaComplex_Name) as \"ResearchName\",
				eup.EvnUslugaPar_id as \"UslugaTest_pid\",
				eup.UslugaComplex_id as \"UslugaComplexTarget_id\",
				eup.EvnUslugaPar_Comment as \"EvnUslugaPar_pComment\",
				analyzertest.Analyzer_IsAutoOk as \"Analyzer_IsAutoOk\"
			from
				v_EvnLabSample els
				inner join v_UslugaTest ut on ut.UslugaTest_id in (" . implode(",", $evnUslugaParBySampleList[1]) . ")
				inner join v_EvnUslugaPar eup on ut.UslugaTest_pid = eup.EvnUslugaPar_id and eup.EvnDirection_id is not null
				left join v_UslugaComplex ucp on ucp.UslugaComplex_id = eup.UslugaComplex_id
				left join v_UslugaComplex uc on ut.UslugaComplex_id = uc.UslugaComplex_id
				left join v_RefValues rv on rv.RefValues_id = ut.RefValues_id
				left join lis.v_DefectCauseType dct on dct.DefectCauseType_id = els.DefectCauseType_id
				left join lateral(
					select
						at_child.AnalyzerTest_id,
						at_child.AnalyzerTest_SysNick,
						at_child.AnalyzerTest_SortCode,
						uctest.UslugaComplex_Name,
						ucms_parent.UslugaComplex_Name as UslugaComplex_ParentName,
						a.Analyzer_IsAutoOk
					from
						lis.v_AnalyzerTest at_child
						left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = COALESCE(at_child.AnalyzerTest_pid, at.AnalyzerTest_id)
						inner join v_UslugaComplexMedService ucms_at on at_child.UslugaComplexMedService_id = ucms_at.UslugaComplexMedService_id
						left join v_UslugaComplexMedService ucms_parent on ucms_parent.UslugaComplexMedService_id = COALESCE(ucms_at.UslugaComplexMedService_pid, ucms_at.UslugaComplexMedService_id)
						inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
						left join v_UslugaComplex uctest on uctest.UslugaComplex_id = at_child.UslugaComplex_id
					where
						ucms_at.UslugaComplex_id = ut.UslugaComplex_id
						and ucms_parent.UslugaComplex_id = eup.UslugaComplex_id
						and ucms_at.MedService_id = els.MedService_id
						and (at.analyzertest_isnotactive is null or at.analyzertest_isnotactive = 1)
                        and (a.analyzer_isnotactive is null or a.analyzer_isnotactive = 1)
						and (at.AnalyzerTest_endDT >= dbo.tzgetdate() or at.AnalyzerTest_endDT is null)
						and (at_child.AnalyzerTest_endDT >= dbo.tzgetdate() or at_child.AnalyzerTest_endDT is null)
						and (uctest.UslugaComplex_endDT >= dbo.tzgetdate() or uctest.UslugaComplex_endDT is null)
                    limit 1
				) as analyzertest on true
				left join lateral(
					select
						at_child.AnalyzerTest_id,
						at_child.AnalyzerTest_SysNick,
						at_child.AnalyzerTest_SortCode,
						uctest.UslugaComplex_Name,
						ucms_parent.UslugaComplex_Name as UslugaComplex_ParentName,
						a.Analyzer_IsAutoOk
					from
						lis.v_AnalyzerTest at_child
						inner join lis.v_Analyzer a  on a.Analyzer_id = at_child.Analyzer_id
						inner join v_UslugaComplexMedService ucms_at  on at_child.UslugaComplexMedService_id = ucms_at.UslugaComplexMedService_id
						left join v_UslugaComplexMedService ucms_parent  on ucms_parent.UslugaComplexMedService_id = coalesce(ucms_at.UslugaComplexMedService_pid, ucms_at.UslugaComplexMedService_id)
						left join v_UslugaComplex uctest  on at_child.UslugaComplex_id = uctest.UslugaComplex_id
					where at_child.Analyzer_id = els.Analyzer_id
					  and (at_child.AnalyzerTest_endDT > dbo.tzgetdate() or at_child.AnalyzerTest_endDT is null)
					  and at_child.UslugaComplex_id = ut.UslugaComplex_id
					limit 1
				) as analyzertest2 on true
				left join lateral(
					select Analyzer_Name
					from
						lis.v_Analyzer a
						inner join lis.v_AnalyzerTest at on at.Analyzer_id = a.Analyzer_id
						inner join lis.v_AnalyzerTestRefValues atrv on atrv.AnalyzerTest_id = at.AnalyzerTest_id
					where atrv.RefValues_id = ut.RefValues_id
                    limit 1
				) as analyzer on true
			where els.EvnLabSample_id = :EvnLabSample_id
			  {$filter}
			{$subQuery}
			order by
				\"SortStatus\", \"AnalyzerTest_SortCode\", \"UslugaComplex_Name\"
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Функция получает родительскую услугу (заказ) из v_EvnUsluga
	 * @param $EvnLabSample_id
	 * @param $UslugaComplex_id
	 * @return mixed|null
	 */
	public static function getEvnUslugaRoot(EvnLabSample_model $callObject, $EvnLabSample_id, $UslugaComplex_id)
	{
		$sql = "
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EvnUslugaPar_setDT as \"EvnUslugaPar_setDT\"
			from v_EvnUslugaPar eu
			where eu.EvnLabSample_id = :EvnLabSample_id
			  and eu.UslugaComplex_id = :UslugaComplex_id
			  and eu.EvnDirection_id is not null
		";
		$sqlParams = ["EvnLabSample_id" => $EvnLabSample_id, "UslugaComplex_id" => $UslugaComplex_id];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (is_object($result)) {
			$response = $result->result_array();
			if (count($response) > 0) {
				return $response[0];
			}
		}
		return null;
	}

	/**
	 * Функция получает родительскую услугу (заказ) из v_EvnUsluga
	 * @param $data
	 * @return array|bool
	 */
	public static function getEvnUslugasRoot(EvnLabSample_model $callObject, $data)
	{
		$filter = "";
		$queryParams = [];
		if (!empty($data["EvnDirection_id"])) {
			$filter .= " and elr.EvnDirection_id = :EvnDirection_id";
			$queryParams["EvnDirection_id"] = $data["EvnDirection_id"];
		} else if (!empty($data["EvnLabSample_id"])) {
			$filter .= " and els.EvnLabSample_id = :EvnLabSample_id";
			$queryParams["EvnLabSample_id"] = $data["EvnLabSample_id"];
		} else {
			return false;
		}
		$query = "
			select distinct
				eu.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				eu.EvnPrescr_id as \"EvnPrescr_id\",
				to_char(eu.EvnUslugaPar_setDT, 'yyyy-mm-dd') as \"EvnUslugaPar_setDT\",
				eu.UslugaComplex_id as \"UslugaComplex_id\"
			from
				v_EvnLabRequest elr
				inner join v_EvnLabSample els on els.EvnLabRequest_id = elr.EvnLabRequest_id
				inner join v_EvnUslugaPar eu on eu.EvnDirection_id = elr.EvnDirection_id
			where eu.EvnDirection_id is not null
			  {$filter}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result_array();
			if (count($response) > 0) {
				return $response;
			}
		}
		return [];
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	public static function getInfoLabSample(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				els.RefSample_id as \"RefSample_id\",
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				elr.EvnDirection_id as \"EvnDirection_id\",
				els.MedService_id as \"MedService_id\",
				elr.Person_id as \"Person_id\",
				elr.PersonEvn_id as \"PersonEvn_id\",
				els.EvnLabSample_setDT as \"EvnLabSample_setDT\",
				elr.Server_id as \"Server_id\",
				els.Analyzer_id as \"Analyzer_id\",
				ms.Lpu_id as \"Lpu_id\",
				ms.LpuSection_id as \"LpuSection_id\",
				elr.PayType_id as \"PayType_id\"
			from
				v_EvnLabSample els
				inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
				inner join v_MedService ms on ms.MedService_id = els.MedService_id
			where els.EvnLabSample_id = :EvnLabSample_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return [
			"RefSample_id" => $result[0]["RefSample_id"],
			"EvnLabRequest_id" => $result[0]["EvnLabRequest_id"],
			"EvnDirection_id" => $result[0]["EvnDirection_id"],
			"MedService_id" => $result[0]["MedService_id"],
			"Person_id" => $result[0]["Person_id"],
			"Analyzer_id" => $result[0]["Analyzer_id"],
			"PersonEvn_id" => $result[0]["PersonEvn_id"],
			"Server_id" => $result[0]["Server_id"],
			"Lpu_id" => $result[0]["Lpu_id"],
			"LpuSection_id" => $result[0]["LpuSection_id"],
			"PayType_id" => $result[0]["PayType_id"],
			"EvnLabSample_setDT" => $result[0]["EvnLabSample_setDT"],
			"MedPersonal_id" => $data["session"]["medpersonal_id"],
		];
	}

	/**
	 * Получение необходимых тестов для смены в них реф. значений после смены анализатора
	 * @param $data
	 * @return array|false
	 */
	public static function getLabSampleTestsForChangeAnalyzerValues(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				ut.UslugaComplex_id as \"UslugaComplex_id\",
				eupp.UslugaComplex_id as \"UslugaComplexTarget_id\"
			from
				v_UslugaTest ut
				left join v_EvnUslugaPar eupp on eupp.EvnUslugaPar_id = ut.UslugaTest_pid
				left join lis.v_AnalyzerTestRefValues atrv on atrv.RefValues_id = ut.RefValues_id
				left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = atrv.AnalyzerTest_id
			where ut.EvnLabSample_id = :EvnLabSample_id
			  and ut.EvnDirection_id is null
		";
		$queryParams = [
			"EvnLabSample_id" => $data["EvnLabSample_id"],
			"Analyzer_id" => $data["Analyzer_id"]
		];
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * Функция данные из заявки (EvnDirection_id, UslugaComplex_id, PayType_id)
	 * @param $data
	 * @return bool|mixed|null
	 */
	public static function getDataFromEvnLabRequest(EvnLabSample_model $callObject, $data)
	{
		$filter = "";
		$join = "";
		$queryParams = [];
		$MedServiceJoin = "ms.MedService_id = elr.MedService_id";
		if (!empty($data["EvnLabRequest_id"])) {
			$filter .= " and elr.EvnLabRequest_id = :EvnLabRequest_id";
			$queryParams["EvnLabRequest_id"] = $data["EvnLabRequest_id"];
		} else if (!empty($data["EvnLabSample_id"])) {
			$join .= "inner join v_EvnLabSample els on els.EvnLabRequest_id = elr.EvnLabRequest_id";
			$filter .= " and els.EvnLabSample_id = :EvnLabSample_id";
			$queryParams["EvnLabSample_id"] = $data["EvnLabSample_id"];
			$MedServiceJoin = "ms.MedService_id = COALESCE(els.MedService_id, elr.MedService_id)";
		} else {
			return false;
		}
		$query = "
			select
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				elr.EvnDirection_id as \"EvnDirection_id\",
				elr.UslugaComplex_id as \"UslugaComplex_id\",
				elr.PayType_id as \"PayType_id\",
				elr.Server_id as \"Server_id\",
				elr.PersonEvn_id as \"PersonEvn_id\",
				elr.Person_id as \"Person_id\",
				elr.Mes_id as \"Mes_id\",
				elr.Diag_id as \"Diag_id\",
				ms.MedService_id as \"MedService_id\",
				ms.Lpu_id as \"Lpu_id\",
				ms.LpuSection_id as \"LpuSection_id\"
			from
				v_EvnLabRequest elr
				{$join}
				inner join v_MedService ms on {$MedServiceJoin}
			where (1=1)
			  {$filter}
		";
		$result = $callObject->queryResult($query, $queryParams);
		if (!is_array($result) || count($result) == 0) {
			return null;
		}
		return $result[0];
	}

	/**
	 * Получение уникального номера лаборатории для генерации номера направления
	 * @param $data
	 * @return array|bool
	 */
	public static function getMedServiceCode(EvnLabSample_model $callObject, $data)
	{
		$query = "
   			select MedService_Code as \"MedService_Code\"
   			from v_MedService
   			where MedService_id = :MedService_id
   		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение номеров проб с прошлых дней, которые на данный момент ещё не закрыты
	 * @param $data
	 * @return array|bool
	 */
	public static function getInWorkSamples(EvnLabSample_model $callObject, $data)
	{
		$queryParams = ["MedService_id" => $data["MedService_id"]];
		$query = "
   			select count(EvnLabSample_Num) as \"count\"
   			from v_EvnLabSample ELS
	        where LabSampleStatus_id in (1,2,3,7)
		      and length(ELS.EvnLabSample_Num) = 12
		      and ELS.MedService_id = :MedService_id
   		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение количества просроченных проб
	 * @param $data
	 * @return array|bool
	 */
	public static function getOverdueSamples(EvnLabSample_model $callObject, $data)
	{
		$queryParams = ["MedService_id" => $data["MedService_id"]];
		$query = "
   			select count(EvnLabSample_Num) as \"count\"
   			from v_EvnLabSample
	        where LabSampleStatus_id in (1,2,3,7)
		      and EvnLabSample_insDT <= (dbo.tzGetDate() - interval '30 days')
		      and length(EvnLabSample_Num) = 12
			  and MedService_id = :MedService_id
   		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	public function getPersonBySample(EvnLabSample_model $callObject, $data)
    {
        $queryParams = array(
            'EvnLabSample_id' => $data['EvnLabSample_id']
        );

        $query = "
			select
				(Person_SurName || ' ' || Person_FirName || ' ' || Person_SecName) as \"Person_Fio\",
				Sex_id as \"Sex_id\",
				Person_Age as \"Person_Age\"
			from v_PersonState_all
			where Person_id = (select Person_id from v_EvnLabSample where EvnLabSample_id = :EvnLabSample_id limit 1)
			limit 1
		";

        $res = $callObject->db->query($query, $queryParams);
        return $res->result('array');
    }

	/**
	 * Получение вида оплаты по-умолчанию
	 * @return string
	 */
	public static function getPayTypeSysNick(EvnLabSample_model $callObject)
	{
		return ($callObject->getRegionNick() == "kz") ? "Resp" : "oms";
	}

	/**
	 * Получение пробы
	 * @param $data
	 * @return array|bool
	 */
	public static function getEvnLabSample(EvnLabSample_model $callObject, $data)
	{
		$queryParams = ["EvnUslugaPar_id" => $data["EvnUslugaPar_id"]];
		$query = "
   			select EvnLabSample_id as \"EvnLabSample_id\"
   			from v_EvnUslugaPar
	        where EvnUslugaPar_id = :EvnUslugaPar_id
			limit 1
   		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * получить перечень номеров без привязки к пробе
	 * @param $data
	 * @return array
	 */
	public static function getNewListEvnLabSampleNum(EvnLabSample_model $callObject, $data)
	{
		$startNum = 5001;
		$limit = 100;
		$arrayNum = [];
		$limit = ($data["quantity"] > $limit) ? $limit : $data["quantity"];
		$n = 1;
		while ($n <= $limit) {
			$arrayNum[] = $callObject->getNewLabSampleNum($data, $startNum);
			$n++;
		}
		return [
			"success" => true,
			"barcodesNums" => implode(",", $arrayNum)
		];
	}

	/**
	 * Получение списка всех тестов для анализа за выбранный день в выбранной лаборатории (для столбцов формы 250у)
	 * @param $data
	 * @return array|bool
	 */
	public static function getTestListForm250(EvnLabSample_model $callObject, $data)
	{
		$filterUslugaTest = "";
		if (!empty($data["UslugaComplex_id"])) {
			$funcParams = [
				"UslugaComplex_pid" => $data["UslugaComplex_id"],
				"MedService_id" => $data["MedService_id"]
			];
			$uslugaContent = $callObject->getUslugaComplexContent($funcParams);
			$filterUslugaTest = (is_array($uslugaContent))
				? " where usl.UslugaComplex_id IN (" . implode(",", $uslugaContent) . ")"
				: " where usl.UslugaComplex_id = :UslugaComplex_id ";
		}
		$query = "
			select distinct
				usl.UslugaComplex_id as \"UslugaComplex_id\",
				at.AnalyzerTest_SysNick as \"AnalyzerTest_SysNick\",
				at.AnalyzerTest_id as \"AnalyzerTest_id\",
				at.AnalyzerTestType_id as \"AnalyzerTestType_id\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				ucgost.UslugaComplex_Name as \"UslugaComplex_Name_Gost\",
				COALESCE(at.AnalyzerTest_SortCode, '777') as \"AnalyzerTest_SortCode\",
				at2.UslugaComplex_id as \"UslugaComplex_pid\"
			from (
					select distinct ut.UslugaComplex_id
					from (
						select EvnLabSample_id
						from v_EvnLabSample els0
						where els0.MedService_id = :MedService_id
						  and els0.EvnLabSample_setDT >= to_timestamp(:EvnLabSample_DelivDT, 'YYYY.MM.DD HH24:MI:SS')
						  and els0.EvnLabSample_setDT < INTERVAL '1 day' + to_timestamp(:EvnLabSample_DelivDT, 'YYYY.MM.DD HH24:MI:SS')
					) els
					left join dbo.v_UslugaTest ut ON ut.EvnLabSample_id = els.EvnLabSample_id
				) usl
				left join dbo.v_UslugaComplexMedService ucms ON ucms.MedService_id = :MedService_id AND ucms.UslugaComplex_id = usl.UslugaComplex_id
				left join lis.v_AnalyzerTest at ON at.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
				left join dbo.v_UslugaComplex uc ON uc.UslugaComplex_id = at.UslugaComplex_id
				left join dbo.v_UslugaComplex ucgost ON uc.UslugaComplex_2011id = ucgost.UslugaComplex_id
				left join lis.AnalyzerTest at2 ON at2.AnalyzerTest_id = at.AnalyzerTest_pid and ucms.MedService_id = :MedService_id
			{$filterUslugaTest}
			order by
				\"AnalyzerTest_SortCode\",
				usl.UslugaComplex_id,
				at.AnalyzerTest_SysNick desc,
				uc.UslugaComplex_Name DESC,
				\"UslugaComplex_Name_Gost\" desc
		";
		/**@var CI_DB_result $result */
		$queryParams = [
			"MedService_id" => $data["MedService_id"],
			"EvnLabSample_DelivDT" => $data["Date"],
			"UslugaComplex_id" => $data["UslugaComplex_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение состава услуги
	 * @param $data
	 * @return array
	 */
	public static function getUslugaComplexContent(EvnLabSample_model $callObject, $data)
	{
		$query = "
			SELECT ucms.UslugaComplex_id as \"UslugaComplex_id\"
			FROM v_UslugaComplexMedService ucms
			WHERE ucms.UslugaComplexMedService_pid in (
					SELECT ms.UslugaComplexMedService_id
					FROM v_UslugaComplexMedService ms
					WHERE ms.UslugaComplex_id = :UslugaComplex_id
					  AND ms.MedService_id = :MedService_id
				)
		";
		$queryParams = [
			"MedService_id" => $data["MedService_id"],
			"UslugaComplex_id" => $data["UslugaComplex_pid"]
		];
		/**@var CI_DB_result $res */
		$res = $callObject->db->query($query, $queryParams);
		$resp_uslugaContent = [];
		if (is_object($res)) {
			$resp_uslugaContent = $res->result_array();
		}
		$listUsluga = [];
		if (count($resp_uslugaContent) == 1) {
			$respUsluga = $resp_uslugaContent[0]["UslugaComplex_id"];
			if ($respUsluga != $data["UslugaComplex_pid"]) {
				$listUsluga[] = $respUsluga;
				$listUsluga[] = $data["UslugaComplex_pid"];
				$result = $listUsluga;
			} else {
				$result = $data["UslugaComplex_pid"];
			}
		} elseif (count($resp_uslugaContent) > 1) {
			for ($i = 0; $i < count($resp_uslugaContent); $i++) {
				$listUsluga[] = $resp_uslugaContent[$i]["UslugaComplex_id"];
			}
			$result = $listUsluga;
		} else {
			$result = $data["UslugaComplex_pid"];
		}
		return $result;
	}

	/**
	 * получаем данные о тесте
	 * @param $data
	 * @return array|bool
	 */
	public static function getUslugaTest(EvnLabSample_model $callObject, $data)
	{
		if (empty($data["UslugaTest_id"])) {
			return false;
		}
		$query = "
			select
				UslugaTest_id as \"UslugaTest_id\",
				UslugaTest_pid as \"UslugaTest_pid\",
				UslugaTest_rid as \"UslugaTest_rid\",
				UslugaTest_setDT as \"UslugaTest_setDT\",
				UslugaTest_disDT as \"UslugaTest_disDT\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnDirection_id as \"EvnDirection_id\",
				Usluga_id as \"Usluga_id\",
				PayType_id as \"PayType_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				UslugaTest_ResultAppDate as \"UslugaTest_ResultAppDate\",
				UslugaTest_ResultCancelReason as \"UslugaTest_ResultCancelReason\",
				UslugaTest_Comment as \"UslugaTest_Comment\",
				UslugaTest_ResultLowerCrit as \"UslugaTest_ResultLowerCrit\",
				UslugaTest_ResultUpperCrit as \"UslugaTest_ResultUpperCrit\",
				UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\",
				UslugaTest_ResultQualitativeText as \"UslugaTest_ResultQualitativeText\",
				RefValues_id as \"RefValues_id\",
				Unit_id as \"Unit_id\",
				UslugaTest_Kolvo as \"UslugaTest_Kolvo\",
				UslugaTest_Result as \"UslugaTest_Result\",
				EvnLabsample_id as \"EvnLabsample_id\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				UslugaTest_insDT as \"UslugaTest_insDT\",
				UslugaTest_updDT as \"UslugaTest_updDT\"
			from v_UslugaTest
			where UslugaTest_id = :UslugaTest_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param EvnLabSample_model $callObject
	 * @param $EvnLabSample_id
	 * @return bool|float|int|string
	 * @throws Exception
	 */
	public static function isLabSampleTestInHole(EvnLabSample_model $callObject, $EvnLabSample_id) {
		$params = [];
		$params['EvnLabSample_id'] = $EvnLabSample_id;
		$query = "
			select T.Tablet_id
			from v_EvnLabSample ELS
			inner join v_UslugaTest UT on UT.EvnLabSample_id = ELS.EvnLabSample_id
			inner join v_Hole H on UT.UslugaTest_id = H.UslugaTest_id
			inner join v_Tablet T on T.Tablet_id = H.Tablet_id
			where ELS.EvnLabSample_id = :EvnLabSample_id and T.Tablet_defectDT is null
		";
		$result = $callObject->getFirstResultFromQuery($query, $params, true);
		if($result === false) {
			throw new Exception('Ошибка при выполнении запроса');
		}
		return $result;
	}

	/**
	 * @param EvnLabSample_model $callObject
	 * @param $data
	 * @return string
	 */
	public static function getIFAEvnLabSamples(EvnLabSample_model $callObject, $data) {
		$where = [];
		$params = [];

		$params['begDate'] = $data['begDate'];
		$params['endDate'] = $data['endDate'];
		$params['MedService_id'] = $data['MedService_id'];

		$where[] = "els.EvnLabSample_setDT is not null";
		$where[] = "ELS.MedService_id = :MedService_id";
		$where[] = ":begDate <= COALESCE(
			cast(els.EvnLabSample_StudyDT as date),
			cast(els.EvnLabSample_setDate as date),
			cast(elr.EvnLabRequest_didDate as date)
		)";

		$where[] = ":endDate >= COALESCE(
			cast(els.EvnLabSample_StudyDT as date),
			cast(els.EvnLabSample_setDate as date),
			cast(elr.EvnLabRequest_didDate as date)
		)";

		if(!empty($data['AnalyzerTest_id'])) {
			$where[] = "AnT.AnalyzerTest_id = :AnalyzerTest_id";
			$params['AnalyzerTest_id'] = $data['AnalyzerTest_id'];
		}

		if(!empty($data['MethodsIFA_id'])) {
			$params['MethodsIFA_id'] = $data['MethodsIFA_id'];
			$where[] = "MI.MethodsiFA_id = :MethodsIFA_id";
		}

		$where = implode(' and ', $where);

		$query = "
			select DISTINCT
				UT.EvnLabSample_id as \"EvnLabSample_id\"
			from v_EvnLabSample ELS
			inner join v_EvnLabRequest ELR on ELR.EvnLabRequest_id = ELS.EvnLabRequest_id
			inner join v_UslugaTest UT on UT.EvnLabSample_id = ELS.EvnLabSample_id
			left join v_EvnUslugaPar eupp on eupp.EvnUslugaPar_id = ut.UslugaTest_pid
			left join v_UslugaComplex uc ON ut.UslugaComplex_id = uc.UslugaComplex_id
			left join v_UslugaComplex ucp ON eupp.UslugaComplex_id = ucp.UslugaComplex_id
			left join v_UslugaComplexMedService ucms on ucms.UslugaComplex_id = uc.UslugaComplex_id
			inner join lis.v_AnalyzerTest AnT on AnT.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
			inner join v_MethodsIFAAnalyzerTest MIAT on MIAT.AnalyzerTest_id = AnT.AnalyzerTest_id
			inner join v_MethodsIFA MI on MI.MethodsIFA_id = MIAT.MethodsIFA_id
			where {$where}
		";
		//echo getDebugSQL($query, $params); die;
		$result = $callObject->queryResult($query, $params);
		if(!is_array($result)) {
			return "";
		}
		$EvnLabSamples_ids = [];
		foreach ($result as $obj) {
			$EvnLabSamples_ids[] = $obj['EvnLabSample_id'];
		}

		return implode(',', $EvnLabSamples_ids);
	}

	/**
	 * @param EvnLabSample_model $callObject
	 * @param $UslugaTest_id
	 * @return mixed
	 */
	public static function testInHole (EvnLabSample_model $callObject, $UslugaTest_id) {
		$params = [];
		$params['UslugaTest_id'] = $UslugaTest_id;
		$query = "
			select Hole_id as \"Hole_id\"
			from v_Hole H
			inner join v_UslugaTest UT on UT.UslugaTest_id = H.UslugaTest_id
			where UT.UslugaTest_id = :UslugaTest_id
		";
		return $callObject->getFirstResultFromQuery($query, $params);
	}

	/**
	 * получить результаты услуги для портала
	 * @param $data
	 * @return array|false
	 */
	public static function getUslugaTestResultForPortal(EvnLabSample_model $callObject, $data)
	{
		$query = "
			select
				up.UslugaComplex_id as \"UslugaComplex_id\",
				up.UslugaTest_ResultValue as \"EvnUslugaPar_ResultValue\",
				up.UslugaTest_ResultLower as \"EvnUslugaPar_ResultLower\",
				up.UslugaTest_ResultUpper as \"EvnUslugaPar_ResultUpper\",
				up.UslugaTest_ResultLowerCrit as \"EvnUslugaPar_ResultLowerCrit\",
				up.UslugaTest_ResultUpperCrit as \"EvnUslugaPar_ResultUpperCrit\",
				up.UslugaTest_ResultUnit as \"EvnUslugaPar_ResultUnit\",
				up.UslugaTest_Comment as \"EvnUslugaPar_Comment\",
				up.UslugaTest_ResultQualitativeText as \"EvnUslugaPar_ResultQualitativeText\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\"
			from
				v_UslugaTest up
				left join v_UslugaComplex uc on up.UslugaComplex_id = uc.UslugaComplex_id
			where up.UslugaTest_pid = :UslugaTest_pid
			  and up.UslugaTest_setDT is not null
		";
		$queryParams = ["UslugaTest_pid" => $data["UslugaTest_pid"]];
		$result = $callObject->queryResult($query, $queryParams);
		return $result;
	}
}
