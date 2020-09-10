<?php

class Person_model_get
{
	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getPersonIdentData(Person_model $callObject, $data)
	{
		/**@var CI_DB_result $res */
		$result = [["success" => true]];
		if (isset($data["KLStreet_Name"]) && isset($data["KLStreet_Name"])) {
			$query = "
				select  
					COALESCE(KLRegion.KLArea_id, 0) as \"KLRegion_id\",
					COALESCE(KLSubRegion.KLArea_id, 0) as \"KLSubRegion_id\",
					case when KLCity.KLAreaLevel_id = 3 then KLCity.KLArea_id end as \"KLCity_id\",
					case when KLCity.KLAreaLevel_id = 4 then KLCity.KLArea_id end as \"KLTown_id\",
					KLRegion.KLCountry_id as \"KLCountry_id\",
					KLStreet.KLStreet_id as \"KLStreet_id\"
				from
					KLStreet 
					left join KLArea KLCity on KLCity.KLAreaLevel_id in (3, 4) and KLStreet.KLArea_id = KLCity.KLArea_id
					LEFT JOIN KLArea KLSubRegion ON KLSubRegion.KLAreaLevel_id = 2 and KLCity.KLArea_pid = KLSubRegion.KLArea_id 
					LEFT JOIN KLArea KLRegion ON KLRegion.KLAreaLevel_id = 1 and (KLSubRegion.KLArea_pid = KLRegion.KLArea_id or KLCity.KLArea_pid = KLRegion.KLArea_id)
				where KLStreet_Name = :KLStreet_Name
				  and LOWER(KLStreet.KLAdr_Ocatd) LIKE LOWER(:KLAdr_Ocatd)||'%'
			";
			/**@var CI_DB_result $res */
			$res = $callObject->db->query($query, $data);
			if (is_object($res)) {
				$res = $res->result_array();
				if (count($res) >= 1) {
					$result[0] = $res[0];
				}
			}
		}
		if (isset($data["Org_Name"])) {
			$query = "
				select od.OrgDep_id as \"Org_id\"
				from
					v_OrgDep od
					inner join Org og  on og.Org_id = od.Org_id
				where LOWER(od.OrgDep_Name) LIKE LOWER(:Org_Name)
			";
			$res = $callObject->db->query($query, $data);
			if (is_object($res)) {
				$res = $res->result_array();
				if (count($res) >= 1) {
					$result[0]["OrgDep_id"] = $res[0]["Org_id"];
				}
			}
		}
		$result[0]["success"] = true;
		return $result;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return mixed|bool
	 */
	public static function getPersonSnils(Person_model $callObject, $data)
	{
		$query = "
			select Person_Snils as \"Person_Snils\"
			from v_PersonState 
			where Person_id = :Person_id
		";
		$queryParams = ["Person_id" => $data["Person_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		if (empty($resp[0])) {
			return false;
		}
		$resp[0]["Error_Msg"] = "";
		return $resp[0];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getPersonCombo(Person_model $callObject, $data)
	{
		$query = "
			select
				PS.Person_id as \"Person_id\",
				coalesce(PS.Person_SurName, '')||coalesce(' '||PS.Person_FirName, '')||coalesce(' '||PS.Person_SecName, '') as \"Person_Fio\"
			from v_PersonState PS 
			where PS.Person_id = :Person_id
		";
		$queryParams = ["Person_id" => $data["Person_id"]];
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonEvalEditWindow(Person_model $callObject, $data)
	{
		if ($data["EvalType"]) {
			$type = $data["EvalType"];
		}
		$selectString = "
			Person{$type}_id as \"PersonEval_id\",
			Person_id as \"Person_id\",
			Evn_id as \"Evn_id\",
			Person{$type}_{$type} as \"Person{$type}_{$type}\",
			Person{$type}_IsAbnorm as \"Person{$type}_IsAbnorm\",
			{$type}AbnormType_id as \"{$type}AbnormType_id\",
			{$type}MeasureType_id as \"{$type}MeasureType_id\",
			to_char(Person{$type}_setDT, '{$callObject->dateTimeForm104}') as \"PersonEval_setDT\",
			Okei_id as \"Okei_id\"
		";
		$query = "
			select {$selectString}
			from v_Person{$type}
			where Person{$type}_id = :PersonEval_id	
		";
		$params = ["PersonEval_id" => $data["PersonEval_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getPersonSearchGrid(Person_model $callObject, $data)
	{
		$join = "";
		$queryParams = [];
		$filters = ["1=1"];
		$filters_with = ["1=1"];
		$orderFirst = "";
		$extraSelect = "";
		$includePerson_ids = '';
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		if (isset($data["PersonRegisterType_id"]) && $data["PersonRegisterType_id"] > 0 && isset($data["DrugRequestPeriod_id"]) && $data["DrugRequestPeriod_id"] > 0) {
			$queryParams["PersonRegisterType_id"] = $data["PersonRegisterType_id"];
			$queryParams["DrugRequestPeriod_id"] = $data["DrugRequestPeriod_id"];
			$query = "
				select PersonRegisterType_SysNick as \"PersonRegisterType_SysNick\"
				from v_PersonRegisterType
				where PersonRegisterType_id = :PersonRegisterType_id
			";
			$person_register_nick = $callObject->getFirstResultFromQuery($query, $queryParams);
			$is_common = (mb_strpos($person_register_nick, "common") !== false); //флаг поиска как для общерапевтической группы
			if (getRegionNick() == "ufa" && in_array($person_register_nick, ["diab_fl", "diab_rl", "orphan"])) {
				$is_common = true;
			}
			if (!$is_common) {
				//если морбус не является общетерапевтическим
				$filters_with[] = "
					ps.Person_id in (
						select Person_id
						from v_PersonRegister 
						where (PersonRegister_disDate is null or PersonRegister_disDate > (select DrugRequestPeriod_begDate from v_DrugRequestPeriod where DrugRequestPeriod_id = :DrugRequestPeriod_id))
						  and PersonRegisterType_id = :PersonRegisterType_id
					)
				";
			} else {
				switch ($person_register_nick) {
					case "common_fl": //ОНЛС: общетерапевтическая группа
					case "diab_fl": //Диабет (ОНЛП)
						$filters[] = "fedl.Person_id is not null"; //только федеральные льготники
						break;
					case "common_rl": //РЛО: общетерапевтическая группа
					case "diab_rl": //Диабет (РЛО)
						$filters[] = "regl.OwnLpu is not null"; //только региональные льготники
						break;
					case "orphan": //Орфанное
					case "common": //Общетерапевтическая группа
						$filters[] = "(fedl.Person_id is not null or regl.OwnLpu is not null)";
						break;
				}
			}
		}

		// добавляем выбранных двойников к ответу
		if ( !empty($data['Double_ids']) ) {
			$arr = json_decode($data['Double_ids']);
			$err = false;
			if (is_array($arr)) {
				foreach ($arr as $item) {
					if (!is_integer(0 + $item) ) {
						$err = true;
						break;
					}
				}
			} else {
				$err = true;
			}
			
			if (!$err && count($arr) > 0) {
				$Person_idsStr = implode(', ', $arr);
				$includePerson_ids = " or ps.Person_id in ({$Person_idsStr}) ";
			}
		}

		// отображение только женщин
		if (mb_strtolower($data["searchMode"]) == "women_only") {
			$filters_with[] = "ps.Sex_id = 2";
			$data["searchMode"] = "all";
		} else if (mb_strtolower($data["searchMode"]) == "men_only") {
			$filters_with[] = "ps.Sex_id = 1";
			$data["searchMode"] = "all";
		}
		// отображение только зашифрованных ВИЧ-инфицированных
		if (mb_strtolower($data["searchMode"]) == "encryponly") {
			$filters_with[] = "ps.Person_IsEncrypHIV = 2";
			$data["searchMode"] = "all";
		}
		// если ищем по ДД, то добавляем еще один inner join c PersonDopDisp
		if (mb_strtolower($data["searchMode"]) == "dd") {
			$queryParams["Year"] = (isset($data["Year"]) && (int)$data["Year"] > 1970) ? (int)$data["Year"] : date("Y");
			$join .= " inner join PersonDopDisp pdd on pdd.Person_id = ps.Person_id and pdd.Lpu_id = :Lpu_id and pdd.PersonDopDisp_Year = :Year ";
			$filters[] = "pdd.PersonDopDisp_Year = :Year";
			$data["searchMode"] = "all";
		}
		// только не включенные в регистр ФМБА
		if (mb_strtolower($data["searchMode"]) == "fmba") {
			$filters_with[] = "
				not exists (
					select *
					from
						v_PersonRegister PerReg 
						left join v_PersonRegisterType PerRegT on PerRegT.PersonRegisterType_id = PerReg.PersonRegisterType_id 
					where PerReg.Person_id = ps.Person_id
					  and PerRegT.PersonRegisterType_SysNick = 'fmba'
				)
			";
			$data["searchMode"] = "all";
		}
		// поиск для регистра главных внештатных специалистов
		if (mb_strtolower($data["searchMode"]) == "hms") {
			$doctorCodes = ($callObject->getRegionNick() == "kz")
				?"(2, 3, 5, 6, 104, 105, 109, 171, 172, 173, 178, 10008, 10209, 10214, 10227, 10228, 10229)"
				:"(6, 48, 111, 216, 262, 263, 264, 287, 10002, 10236, 10240)";
			$extraSelect .= ",mw.MedWorker_id as \"MedWorker_id\"";
			$join .= "
				inner join persis.v_MedWorker mw  on mw.Person_id = ps.Person_id 
				left join lateral (
					select msf.MedStaffFact_id
					from
						v_MedStaffFact msf 
						left join persis.Post p  on p.id = msf.Post_id
					where msf.Person_id = ps.Person_id 
					  and msf.PostOccupationType_id = 1 
					  and (msf.PostKind_id = 1 or p.code in {$doctorCodes})
					limit 1
				) as MSFp ON true
			";
			$filters[] = "MSFp.MedStaffFact_id is not null";
			$data["searchMode"] = "all";
		}
		// только прикреплённые
		if (mb_strtolower($data["searchMode"]) == "att") {
			$filters[] = "pcard.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
			$data["searchMode"] = "all";
		}
		// только прикреплённые (основное или служебное)
		if (mb_strtolower($data["searchMode"]) == "att_1_4") {
			$filters_with[] = "
                exists (
                    select i_pc.PersonCard_id
                    from v_PersonCard_all i_pc 
                    where i_pc.Lpu_id = :Lpu_id
                      and (:LpuRegion_id is null or i_pc.LpuRegion_id = :LpuRegion_id)
                      and i_pc.Person_id = ps.Person_id
                      and i_pc.LpuAttachType_id in (1, 4)
                      and (i_pc.PersonCard_begDate is null or i_pc.PersonCard_begDate <= dbo.tzGetDate())
                      and (i_pc.PersonCard_endDate is null or i_pc.PersonCard_endDate >= dbo.tzGetDate())
                )
			";
			$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
			$queryParams["LpuRegion_id"] = $data["LpuRegion_id"];
			$data["searchMode"] = "all";
		} else if (!empty($data["LpuRegion_id"])) {
			$filters_with[] = "
                exists (
                    select i_pc.PersonCard_id
                    from v_PersonCard_all i_pc 
                    where i_pc.Person_id = ps.Person_id
                      and i_pc.LpuRegion_id = :LpuRegion_id
                      and (i_pc.PersonCard_begDate is null or i_pc.PersonCard_begDate <= dbo.tzGetDate())
                      and (i_pc.PersonCard_endDate is null or i_pc.PersonCard_endDate >= dbo.tzGetDate())
                )
			";
			$queryParams["LpuRegion_id"] = $data["LpuRegion_id"];
		}
		// только прикреплённые и в объёме
		if (mb_strtolower($data["searchMode"]) == "att_vol") {
			$allowWithoutAttach = false;
			// проверяем наличие объёма "Без прикрепления"
			$resp_vol = $callObject->queryResult("
				select av.AttributeValue_id as \"AttributeValue_id\"
				from
					v_AttributeVision avis 
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a on a.Attribute_id = av.Attribute_id
				where avis.AttributeVision_TableName = 'dbo.VolumeType'
				  and av.AttributeValue_ValueIdent = :Lpu_id
				  and avis.AttributeVision_TablePKey = (select VolumeType_id from v_VolumeType where VolumeType_Code = 'ОН_Б_ПРИК' limit 1)
				  and avis.AttributeVision_IsKeyValue = 2
				  and coalesce(av.AttributeValue_begDate, dbo.tzGetDate()) <= dbo.tzGetDate()
				  and coalesce(av.AttributeValue_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
                limit 1
			", ["Lpu_id" => $data["Lpu_id"]]);
			if (!empty($resp_vol[0]["AttributeValue_id"])) {
				// Если МО имеет объем открытый объем «ОН_Б_ПРИК», то проверку на прикрепление к разрешенным МО не проводим
				$allowWithoutAttach = true;
			}
			if (!$allowWithoutAttach) {
				$data["VolumeType_id"] = 88; // Мед. осмотры несовершеннолетних в чужой МО
				if (!empty($data["VolumeType_id"])) {
					$filters[] = "
						(
							pcard.Lpu_id = :Lpu_id or
							exists (
								select av.AttributeValue_id
								from
									v_AttributeVision avis 
									inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
									inner join v_Attribute a on a.Attribute_id = av.Attribute_id
									inner join lateral (
										select av2.AttributeValue_ValueIdent
										from
											v_AttributeValue av2 
											inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
										where av2.AttributeValue_rid = av.AttributeValue_id
										  and a2.Attribute_TableName = 'dbo.Lpu'
										  and COALESCE(av2.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id
									    limit 1
									) as MOFILTER ON true
								WHERE avis.AttributeVision_TableName = 'dbo.VolumeType'
								  and avis.AttributeVision_TablePKey = :VolumeType_id
								  and avis.AttributeVision_IsKeyValue = 2
								  and COALESCE(av.AttributeValue_begDate, dbo.tzGetDate()) <= dbo.tzGetDate()
								  and COALESCE(av.AttributeValue_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
								  and av.AttributeValue_ValueIdent = pcard.Lpu_id
							)
						)
					";
					$queryParams["VolumeType_id"] = $data["VolumeType_id"];
				} else {
					$filters[] = "pcard.Lpu_id = :Lpu_id";
				}
			}
			$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
			$data["searchMode"] = "all";
		}
		// только прикреплённые или старше 3 лет
		if (mb_strtolower($data["searchMode"]) == "attbefore3") {
			$filters[] = "(pcard.Lpu_id = :Lpu_id or (dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) >= 3))";
			$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
			$data["searchMode"] = "all";
		}
		if ((!empty($data["showAll"]) && $data["showAll"] == 1) || in_array($data['searchMode'], ['palliat'])) {
			$filters_with[] .= "ps.Person_deadDT is null";
		}
		if (!empty($data["isNotDead"]) && $data["isNotDead"] == 2) {
			$callObject->load->model("Options_model", "Options_model");
			$limit_days_after_death_to_create_cal = $callObject->Options_model->getOptionsGlobals($data, "limit_days_after_death_to_create_call");
			$daysByDeath = $limit_days_after_death_to_create_cal ? $limit_days_after_death_to_create_cal : 0;
			$filters_with[] = "((ps.Person_deadDT <= dbo.tzGetDate() and datediff('day', dbo.tzGetDate(), ps.Person_deadDT::timestamp) <= {$daysByDeath}) or ps.Person_deadDT is null)";
		}
		if (mb_strtolower($data["searchMode"]) == "dt6") {
			$filters[] = "(dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) < 6) and pcard.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
			$data["searchMode"] = "all";
		}
		// только старше 14 лет и не умершие
		if (mb_strtolower($data["searchMode"]) == "older14notdead") {
			$filters_with[] = "
				((dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) >= 14)
				and
				(ps.Person_deadDT is null) and (ps.Person_IsDead is null))
			";
			$data["searchMode"] = "all";
		}
		if (mb_strtolower($data["searchMode"]) == "dt14") {
			$filters_with[] = "(dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) between 13 and 15)";
			$data["searchMode"] = "all";
		}
		if (mb_strtolower($data["searchMode"]) == "geriatrics") {
			$filters_with[] = "(dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) >= 60)";
			if (!isSuperAdmin() && !havingGroup("GeriatryRegistryFullAccess")) {
				$filters[] = "pcard.Lpu_id = :Lpu_id";
				$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
			}
			$data["searchMode"] = "all";
		}
		// если ищем по регистру детей-сирот, то добавляем еще один inner join c PersonDopDisp
		if (mb_strtolower($data["searchMode"]) == "ddorp") {
			$queryParams["Year"] = (isset($data["Year"]) && (int)$data["Year"] > 1970) ? (int)$data["Year"] : date("Y");
			$join .= " inner join v_persondisporp ddorp on ddorp.Person_id=ps.Person_id and ddorp.Lpu_id = :Lpu_id and ddorp.PersonDispOrp_Year = :Year and ddorp.CategoryChildType_id <= 7";
			$filters[] = "ddorp.PersonDispOrp_Year = :Year";
			$filters[] = "ddorp.CategoryChildType_id <= 7";
			$data["searchMode"] = "all";
		}
		// если ищем по открытым КВС
		if (mb_strtolower($data["searchMode"]) == "hasopenevnps") {
			$filters_with[] = "
				exists (
					select es.EvnSection_id
					from v_EvnSection es
					where es.Lpu_id = :Lpu_id
					  and es.Person_id = ps.Person_id
					  and es.LeaveType_id is null
					  and COALESCE(es.EvnSection_IsPriem, 1) = 1
				)
			";
			$data["searchMode"] = "all";
		}
		// если ищем по картам первого этапа детей-сирот, то добавляем еще один inner join c EvnPLDispOrp
		if (mb_strtolower($data["searchMode"]) == "ddorpsec") {
			$queryParams["Year"] = (isset($data["Year"]) && (int)$data["Year"] > 1970) ? (int)$data["Year"] : date("Y");
			$join .= "
				inner join v_EvnPLDispOrp epldorp on epldorp.Person_id = ps.Person_id
					and epldorp.Lpu_id = :Lpu_id
					and epldorp.EvnPLDispOrp_IsTwoStage = 2
					and epldorp.EvnPLDispOrp_IsFinish = 2
					and epldorp.DispClass_id in (3,7)
					and not exists(
						select EvnPLDispOrp_id
						from v_EvnPLDispOrp epldorpsec
						where epldorpsec.EvnPLDispOrp_fid = epldorp.EvnPLDispOrp_id
					)
			";
			$filters[] = "epldorp.EvnPLDispOrp_IsTwoStage = 2";
			$filters[] = "epldorp.EvnPLDispOrp_IsFinish = 2";
			$filters[] = "epldorp.DispClass_id in (3,7)";
			$data["searchMode"] = "all";
		}
		// периодический осмотр
		if (mb_strtolower($data["searchMode"]) == "ddorpperiod") {
			$queryParams["Year"] = (isset($data["Year"]) && (int)$data["Year"] > 1970) ? (int)$data["Year"] : date("Y");
			$join .= " inner join v_persondisporp ddorp on ddorp.Person_id = ps.Person_id and ddorp.Lpu_id = :Lpu_id and ddorp.PersonDispOrp_Year = :Year and ddorp.CategoryChildType_id = 8";
			$filters[] = "ddorp.PersonDispOrp_Year = :Year";
			$filters[] = "ddorp.CategoryChildType_id = 8";
			$data["searchMode"] = "all";
		}
		// ДВН 2
		if (mb_strtolower($data["searchMode"]) == "dddispclass2") {
			$queryParams["Year"] = (isset($data["Year"]) && (int)$data["Year"] > 1970) ? (int)$data["Year"] : date("Y");
			$filters_with[] = "
				not exists(
					select EvnPLDispDop13_id
					from v_EvnPLDispDop13 
					where Person_id = ps.Person_id
					  and date_part('YEAR', EvnPLDispDop13_consDT) = :Year
				)
			";
			$callObject->load->model("EvnPLDispDop13_model");
			$dateX = $callObject->EvnPLDispDop13_model->getNewDVNDate();
			$maxage = 999;
			$personPrivilegeCodeList = $callObject->EvnPLDispDop13_model->getPersonPrivilegeCodeList($queryParams["Year"] . "-01-01");
			if (!empty($dateX) && $dateX <= date("Y-m-d")) {
				$add_filter = "
					dbo.Age2(PS.Person_BirthDay, cast(:Year::varchar||'-12-31' as date)) >= 40 or
					(
						dbo.Age2(PS.Person_BirthDay, cast(:Year::varchar)||'-12-31' as date)) >= 18 and
						and ((dbo.Age2(PS.Person_BirthDay, cast(:Year::varchar || '-12-31' sa date) - interval '18 days') % 3 = 0)
					)
				";
			} else {
				$add_filter = "
					(dbo.Age2(PS.Person_BirthDay, cast(:Year::varchar||'-12-31' as date)) - interval '21 days' >= 0 and
					(dbo.Age2(PS.Person_BirthDay, cast(:Year::varchar||'-12-31' as date)) - interval '21 days') % 3 = 0)
				";
			}
			if (in_array(getRegionNick(), ["ufa", "ekb", "kareliya", "penza", "astra"])) {
				$add_filter .= "
					or exists (select PersonPrivilegeWOW_id from v_PersonPrivilegeWOW where Person_id = PS.Person_id)
				";
			}
			if (count($personPrivilegeCodeList) > 0) {
				$add_filter .= "
					or (
						(dbo.Age2(PS.Person_BirthDay, cast(:Year::varchar||'-12-31' as date)) BETWEEN 18 AND {$maxage}) and
						exists (
							select pp.PersonPrivilege_id
							from
								v_PersonPrivilege pp 
								inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
							where pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
							  and pp.Person_id = PS.Person_id
							  and pp.PersonPrivilege_begDate <= cast(:Year::varchar||'-12-31' as date)
							  and (pp.PersonPrivilege_endDate > cast(:Year::varchar||'-12-31' as date) or pp.PersonPrivilege_endDate is null)
						)
					)
				";
			}
			$filters_with[] = "
				({$add_filter}) and
				dbo.Age2(PS.Person_BirthDay, cast(:Year::varchar||'-12-31' as date)) <= {$maxage}
			";
			$data["searchMode"] = "all";
		}
		// если ищем по картам первого этапа профосмотра несовершеннолетних, то добавляем еще один inner join c EvnPLDispTeenInspection
		if (mb_strtolower($data["searchMode"]) == "evnpldtipro") {
			$queryParams["Year"] = (isset($data["Year"]) && (int)$data["Year"] > 1970) ? (int)$data["Year"] : date("Y");
			$join .= "
				inner join v_EvnPLDispTeenInspection epldti on epldti.Person_id = ps.Person_id
					and epldti.Lpu_id = :Lpu_id
					and epldti.EvnPLDispTeenInspection_IsTwoStage = 2
					and epldti.EvnPLDispTeenInspection_IsFinish = 2
					and epldti.DispClass_id = 10 and not exists(
						select EvnPLDispTeenInspection_id
						from v_EvnPLDispTeenInspection epldtisec
						where epldtisec.EvnPLDispTeenInspection_fid = epldti.EvnPLDispTeenInspection_id
					)
			";
			$filters[] = "epldti.EvnPLDispTeenInspection_IsTwoStage = 2";
			$filters[] = "epldti.EvnPLDispTeenInspection_IsFinish = 2";
			$filters[] = "epldti.DispClass_id = 10";
			$data["searchMode"] = "all";
		}
		// если ищем по картам первого этапа предвосмотра несовершеннолетних, то добавляем еще один inner join c EvnPLDispTeenInspection
		if (mb_strtolower($data["searchMode"]) == "evnpldtipre") {
			$queryParams["Year"] = (isset($data["Year"]) && (int)$data["Year"] > 1970) ? (int)$data["Year"] : date("Y");
			$join .= "
				inner join v_EvnPLDispTeenInspection epldti on epldti.Person_id = ps.Person_id
					and epldti.Lpu_id = :Lpu_id
					and epldti.EvnPLDispTeenInspection_IsTwoStage = 2
					and epldti.EvnPLDispTeenInspection_IsFinish = 2
					and epldti.DispClass_id = 9 and not exists(
						select EvnPLDispTeenInspection_id
						from v_EvnPLDispTeenInspection epldtisec
						where epldtisec.EvnPLDispTeenInspection_fid = epldti.EvnPLDispTeenInspection_id
					)
			";
			$filters[] = "epldti.EvnPLDispTeenInspection_IsTwoStage = 2";
			$filters[] = "epldti.EvnPLDispTeenInspection_IsFinish = 2";
			$filters[] = "epldti.DispClass_id = 9";
			$data["searchMode"] = "all";
		}
		// если ищем для скринингового исследования, то возраст пациента на конец выбранного года должен соответствовать
		if (mb_strtolower($data["searchMode"]) == "evnpldispscreen") {
			//$queryParams["Year"] = (isset($data["Year"]) && (int)$data["Year"] > 1970) ? (int)$data["Year"] : date("Y");
			//$filters_with[] = "(dbo.Age2(ps.Person_BirthDay, cast(:Year::varchar || '-12-31' as date)) in (30,34,38,40,42,44,46,48,50,52,54,56,58,60,62,64,66,68,70))";
			$data["searchMode"] = "all";
		}
		if (mb_strtolower($data["searchMode"]) == "wow") {
			$join .= " inner join PersonPrivilegeWOW PPW on PPW.Person_id = ps.Person_id";
			$data["searchMode"] = "all";
		}
		if ((mb_strtolower($data["searchMode"]) == "attachrecipients") && !isMinZdrav() && !isOnko() && !isRA() && !isPsih() && !isOnkoGem() && !isGuvd()) {
			// только по льготникам и прикрепленным
			$data["searchMode"] = "all";
			$filters[] = "Lpu.Lpu_id = :Lpu_id";
			$filters[] = "(fedl.Person_id is not null or regl.OwnLpu is not null)";
			$filterscard = "pc.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
		} else {
			$filterscard = "(1=1)";
		}
		if ((mb_strtolower($data["searchMode"]) == "withlgotonly") && !isMinZdrav() && !isOnko() && !isRA() && !isPsih() && !isOnkoGem() && !isGuvd()) {
			// только по льготникам
			$data["searchMode"] = "all";
			$filters[] = "(fedl.Person_id is not null or regl.OwnLpu is not null)";
		}
		//Поиск в арм смо. Для Перми - последние 3 года.
		if ((mb_strtolower($data["searchMode"]) == "smo" || mb_strtolower($data["searchMode"]) == "smo3") && !empty($data["session"]["org_id"])) {
			//#PROMEDWEB-5677 информация про СМО может быть не СМО id, а Org_id
			$filtertmp = "(pls.OrgSmo_id = :OrgSmo_id or vos.Org_id = :OrgSmo_id)";
			$queryParams["OrgSmo_id"] = $data["session"]["org_id"];
			if (mb_strtolower($data["searchMode"]) == "smo3") {
				$filtertmp .= " and (pls.Polis_endDate >= dateadd('year', -3, getdate()) or pls.Polis_endDate is null) and pls.Polis_begDate <= getdate()";
			}
			$filters[] = "({$filtertmp})";
			$data["searchMode"] = "all";
		}
        if (mb_strtolower($data['searchMode']) == 'erssnils') {
            $filters_with[] = "ps.Person_Snils is not null";
            $data['searchMode'] = 'ers';
        }
        if (mb_strtolower($data['searchMode']) == 'ers') {
            $filters_with[] = "ps.Sex_id = 2";
            $filters_with[] = "dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) >= 14";
            $data['searchMode'] = 'all';
        }

		if (isset($data["ParentARM"]) && ($data["ParentARM"] == "smpdispatchcall" || $data["ParentARM"] == "smpadmin" || $data["ParentARM"] == "smpdispatchdirect") && !empty($data["PersonAge_AgeFrom"]) && empty($data["PersonAge_AgeTo"])) {
			$filters_with[] = "(dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) between cast(:PersonAge_AgeFrom as date) - interval '5 day' and cast(:PersonAge_AgeFrom as date) + interval '5 day')";
			$extraSelect .= "
				,ABS(:PersonAge_AgeFrom - (datediff('year', dbo.tzGetDate(), ps.Person_BirthDay)||
				 case when date_part('month', ps.Person_BirthDay) > date_part('month', dbo.tzGetDate()) or (date_part('month', ps.Person_BirthDay = date_part('month', dbo.tzGetDate()) and date_part('day', ps.Person_BirthDay) > date_part('day', dbo.tzGetDate()))
				then -1 else 0 end)) as \"YearDifference\"
			";
			$orderFirst .= "YearDifference ASC,";
		} else {
			if (!empty($data["PersonAge_AgeFrom"])) {
				$filters_with[] = "(dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) >= :PersonAge_AgeFrom)";
			}
			if (!empty($data["PersonAge_AgeTo"])) {
				$filters_with[] = "(dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) <= :PersonAge_AgeTo)";
			}
		}
		if (!empty($data["PersonBirthYearFrom"])) {
			$filters_with[] = "(date_part('year', ps.Person_BirthDay) >= :PersonBirthYearFrom)";
			$queryParams["PersonBirthYearFrom"] = $data["PersonBirthYearFrom"];
		}
		if (!empty($data["PersonBirthYearTo"])) {
			$filters_with[] = "(date_part('year', ps.Person_BirthDay) <= :PersonBirthYearTo)";
			$queryParams["PersonBirthYearTo"] = $data["PersonBirthYearTo"];
		}
		$queryParams["PersonAge_AgeFrom"] = $data["PersonAge_AgeFrom"];
		$queryParams["PersonAge_AgeTo"] = $data["PersonAge_AgeTo"];
		if (!empty($data["personBirtDayFrom"])) {
			$filters_with[] = "(ps.Person_BirthDay >= cast(:personBirtDayFrom as date))";
			$queryParams["personBirtDayFrom"] = $data["personBirtDayFrom"];
		}
		if (!empty($data["personBirtDayTo"])) {
			$filters_with[] = "(ps.Person_BirthDay <= cast(:personBirtDayTo as date))";
			$queryParams["personBirtDayTo"] = $data["personBirtDayTo"];
		}
		if (!empty($data["EvnUdost_Ser"]) || !empty($data["EvnUdost_Num"])) {
			$join .= " inner join v_EvnUdost eu on eu.Person_id = ps.Person_id and EvnUdost_disDate is null";
			if (!empty($data["EvnUdost_Ser"])) {
				$join .= " and eu.EvnUdost_Ser = :EvnUdost_Ser ";
				$filters[] = "eu.EvnUdost_Ser = :EvnUdost_Ser";
				$queryParams["EvnUdost_Ser"] = $data["EvnUdost_Ser"];
			}
			if (!empty($data["EvnUdost_Num"])) {
				$join .= " and eu.EvnUdost_Num = :EvnUdost_Num ";
				$filters[] = "eu.EvnUdost_Num = :EvnUdost_Num";
				$queryParams["EvnUdost_Num"] = $data["EvnUdost_Num"];
			}
		}
		if (!empty($data["PersonCard_id"])) {
			$filters_with[] = "exists (select PersonCard_id from v_PersonCard_all where Person_id = PS.Person_id and PersonCard_id = :PersonCard_id)";
			$queryParams["PersonCard_id"] = $data["PersonCard_id"];
		}
		if (!empty($data["PersonCard_Code"])) {
			$filters_with[] = "exists (select PersonCard_id from v_PersonCard_all where Person_id = PS.Person_id and PersonCard_Code = :PersonCard_Code and (PersonCard_endDate is null or PersonCard_endDate >= dbo.tzGetDate()) and Lpu_id = :Lpu_id)";
			$queryParams["PersonCard_Code"] = $data["PersonCard_Code"];
		}
		if (!empty($data["EvnPS_NumCard"]) && $data["EvnPS_NumCard"] != "") {
			$join .= " inner join v_EvnPS eps1  on eps1.Person_id=ps.Person_id and rtrim(eps1.EvnPS_NumCard) = :EvnPS_NumCard and eps1.Lpu_id = :Lpu_id ";
			$filters[] = "rtrim(eps1.EvnPS_NumCard) = :EvnPS_NumCard";
			$queryParams["EvnPS_NumCard"] = $data["EvnPS_NumCard"];
		}
		if (!empty($data["Person_id"])) {
			$filters_with[] = "ps.Person_id = :Person_id";
			$queryParams["Person_id"] = $data["Person_id"];
		}
		$extraSelect .= ",'edit' as \"accessType\"";
		$isSearchByEncryp = false;
		$select_person_data = "
			,COALESCE(ps.Person_SurName, '') as \"PersonSurName_SurName\"
			,COALESCE(ps.Person_FirName, '') as \"PersonFirName_FirName\"
			,COALESCE(ps.Person_SecName, '') as \"PersonSecName_SecName\"
			,pls.Polis_Ser as \"Polis_Ser\"
			,pls.PolisFormType_id as \"PolisFormType_id\"
			,pls.OrgSMO_id as \"OrgSMO_id\"
			,pls.OMSSprTerr_id as \"OMSSprTerr_id\"
			,to_char(pls.Polis_endDate, '{$callObject->dateTimeForm104}') as \"Polis_endDate\"
			,ps.Person_Snils as \"Person_Snils\"
			,case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end as \"Polis_Num\"
			,ps.Document_Ser as \"Document_Ser\"
			,ps.Document_Num as \"Document_Num\"
			,ps.Person_edNum as \"Polis_EdNum\"
			,dbo.Age(ps.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\"
			,to_char(ps.Person_BirthDay::timestamp, '{$callObject->dateTimeForm104}') as \"PersonBirthDay_BirthDay\"
			,to_char(ps.Person_deadDT::timestamp, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
			,ps.Sex_id as \"Sex_id\"
			,org.org_nick  as \"Lpu_Nick\"
			,lpu.Lpu_id as \"CmpLpu_id\"
			,pcard1.PersonCardState_Code as \"PersonCard_Code\"
		";
		if (isEncrypHIVRegion($callObject->regionNick)) {
			if (allowPersonEncrypHIV($data["session"])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data["PersonSurName_SurName"]);
				if ($isSearchByEncryp) {
					$extraSelect .= ",'list' as \"accessType\"";
					$select_person_data = "
						,peh.PersonEncrypHIV_Encryp as \"PersonSurName_SurName\"
						,'' as \"PersonFirName_FirName\"
						,'' as \"PersonSecName_SecName\"
						,'' as \"Polis_Ser\"
						,'' as \"Polis_Num\"
						,'' as \"PolisFormType_id\"
						,'' as \"OrgSMO_id\"
						,'' as \"OMSSprTerr_id\"
						,null as \"Polis_endDate\"
						,'' as \"Document_Ser\"
						,'' as \"Document_Num\"
						,'' as \"Polis_EdNum\"
						,null as \"Person_Age\"
						,null as \"PersonBirthDay_BirthDay\"
						,null as \"Person_deadDT\"
						,null as \"Sex_id\"
						,'' as \"Lpu_Nick\"
						,null as \"CmpLpu_id\"
						,'' as \"PersonCard_Code\"
					";
				}
			} else {
				$filters_with[] = "
					not exists(
						select peh.PersonEncrypHIV_Encryp
						from
							v_PersonEncrypHIV peh 
							inner join v_EncrypHIVTerr eht on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id and COALESCE(eht.EncrypHIVTerr_Code, 0) = 20
						where peh.Person_id = ps.Person_id
					)
				";
			}
		}
		if (!isSuperAdmin() && strlen($data["PersonSurName_SurName"]) > 0) {
			$data["PersonSurName_SurName"] = trim(str_replace(["%", "_"], "", $data["PersonSurName_SurName"]));
			if (strlen($data["PersonSurName_SurName"]) == 0) {
				throw new Exception("Поле Фамилия обязательно для заполнения (использование знаков % и _  недопустимо).");
			}
		}
		if (!empty($data["PersonSurName_SurName"])) {
			if (allowPersonEncrypHIV($data["session"]) && $isSearchByEncryp) {
				$join .= " inner join v_PersonEncrypHIV peh on peh.Person_id = ps.Person_id and LOWER(peh.PersonEncrypHIV_Encryp) LIKE LOWER(:Person_SurName)";
				$filters[] = "1=1";
				$filters[] = "LOWER(peh.PersonEncrypHIV_Encryp) LIKE LOWER(:Person_SurName)";
			} else {
				$filters_with[] = "LOWER(ps.Person_SurNameR) LIKE LOWER(:Person_SurName)||'%'";
			}
			$queryParams["Person_SurName"] = $callObject->prepareSearchSymbol($data["PersonSurName_SurName"]);
		}
		if (!empty($data["PersonFirName_FirName"])) {
			$filters_with[] = "LOWER(ps.Person_FirNameR) LIKE LOWER(:Person_FirName)||'%'";
			$queryParams["Person_FirName"] = $callObject->prepareSearchSymbol($data["PersonFirName_FirName"]);
		}
		if (!empty($data["PersonSecName_SecName"])) {
			$filters_with[] = "LOWER(ps.Person_SecNameR) LIKE LOWER(:Person_SecName)||'%'";
			$queryParams["Person_SecName"] = $callObject->prepareSearchSymbol($data["PersonSecName_SecName"]);
		}
		if (!empty($data["PersonBirthDay_BirthDay"])) {
			$filters_with[] = "ps.Person_BirthDay = :Person_BirthDay";
			$queryParams["Person_BirthDay"] = $data["PersonBirthDay_BirthDay"];
		}
		if (!empty($data["Person_Snils"])) {
			$filters_with[] = "ps.Person_Snils = :Person_Snils";
			$queryParams["Person_Snils"] = $data["Person_Snils"];
		}
		if (!empty($data["Person_Inn"])) {
			$filters_with[] = "ps.Person_Inn = :Person_Inn";
			$queryParams["Person_Inn"] = $data["Person_Inn"];
		}
		if (!empty($data["Polis_Ser"])) {
			$filters_with[] = "ps.Polis_Ser = :Polis_Ser";
			$queryParams["Polis_Ser"] = $data["Polis_Ser"];
		}
		if (!empty($data["Polis_Num"])) {
			$filters_with[] = "ps.Polis_Num = :Polis_Num";
			$queryParams["Polis_Num"] = $data["Polis_Num"];
		}
		if (!empty($data["Polis_EdNum"])) {
			$filters_with[] = "ps.Person_edNum = :Polis_edNum";
			$queryParams["Polis_edNum"] = $data["Polis_EdNum"];
		}
		if (!empty($data["Sex_id"])) {
			$filters_with[] = "ps.Sex_id = :Sex_id";
			$queryParams["Sex_id"] = $data["Sex_id"];
		}
		if (isset($data["Person_ids"]) && !empty($data["Person_ids"])) {
			$filters_with[] = "ps.Person_id in (" . $data["Person_ids"] . ")";
		}
		if (isset($data["soc_card_id"]) && mb_strlen($data["soc_card_id"]) >= 25) {
			$queryParams["SocCardNum"] = mb_substr($data["soc_card_id"], 0, 19);
			$join .= " inner join PersonSocCardNum pscn on ps.PersonSocCardNum_id = pscn.PersonSocCardNum_id and LEFT(pscn.PersonSocCardNum_SocCardNum, 19) = :SocCardNum ";
			$filters[] = "LEFT(pscn.PersonSocCardNum_SocCardNum, 19) = :SocCardNum";
		}
		if (!empty($data['PersonRefuse_IsRefuse']) && !empty($data['DrugRequestPeriod_id']) && !empty($data['PersonRegisterType_id'])) {
			//проверяем морбус проверку нужно осуществлять только по общетерапевтическому морбусу с федеральным финансированием
			$query = "
				select ltrim(rtrim(PersonRegisterType_SysNick)) as \"PersonRegisterType_SysNick\"
				from v_PersonRegisterType
				where PersonRegisterType_id = :PersonRegisterType_id;
			";
			$person_register_nick = $callObject->getFirstResultFromQuery($query, ["PersonRegisterType_id" => $data["PersonRegisterType_id"]]);
			if (in_array($person_register_nick, ["common", "common_fl"])) {
				//получаем год из периода заявки
				$query = "
					select date_part('year',DrugRequestPeriod_begDate) as \"DrugRequestPeriod_Year\"
					from v_DrugRequestPeriod 
					where DrugRequestPeriod_id = :DrugRequestPeriod_id;
				";
				$drp_year = $callObject->getFirstResultFromQuery($query, ["DrugRequestPeriod_id" => $data["DrugRequestPeriod_id"]]);
				$filters[] = "COALESCE(drp_refuse.PersonRefuse_IsRefuse, 1) = :PersonRefuse_IsRefuse";
				$queryParams["DrugRequestPeriod_Year"] = $drp_year > 0 ? $drp_year : null;
				$queryParams["PersonRefuse_IsRefuse"] = $data["PersonRefuse_IsRefuse"];
				$join = " left join v_PersonRefuse drp_refuse  on drp_refuse.Person_id = ps.Person_id and drp_refuse.PersonRefuse_Year = :DrugRequestPeriod_Year";
			}
		}
		if (!empty($data["getPersonWorkFields"]) && $data["getPersonWorkFields"] == 1) {
			$extraSelect .= "
				,O.Org_id as \"Person_Work_id\"
				,O.Org_Nick as \"Person_Work\"
			";
			$join .= "
				left join v_Job J on ps.Job_id = J.Job_id
				left join v_Org O on O.Org_id = J.Org_id
			";
		}
		$filters_with[] = "COALESCE(ps.Person_IsUnknown, 1) <> 2 ";
		if (count($filters) <= 1 && count($filters_with) <= 1 && empty($data["EvnPS_NumCard"])) {
			throw new Exception(toUtf("Не указан ни один фильтр. Для поиска заполните хотя бы один параметр в блоке фильтров."));
		}
		$isBDZ = "
			CASE WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= dbo.tzGetDate()
				THEN 'orange'
				ELSE CASE WHEN ps.PersonCloseCause_id = 2 and ps.Person_closeDT is not null
					THEN 'red'
					ELSE CASE WHEN ps.Server_pid = 0
						THEN 'true'
						ELSE 'false'
					END
				END
			END as \"Person_IsBDZ\",
		";
		if (getRegionNick() == "perm") {
			$isBDZ = "
				case
					when ps.Server_pid = 0 then 'true'
					when ps.Person_IsInErz = 1 then 'blue'
					when ps.Server_pid = 0 and pls.Polis_endDate < cast(dbo.tzGetDate() as date)
						then case when ps.Person_deadDT is null
							then 'yellow'
							else 'red'
						end
					when ps.Server_pid = 2 and ps.Person_IsInErz <> 1 then 'false'
				end as \"Person_IsBDZ\",
			";
		}
		if (getRegionNick() == "penza") {
			$isBDZ = "
				case
					when ps.Person_IsInErz = 1 then 'orange'
					when ps.Person_IsInErz = 2 then 'true'
					else 'false'
				end as \"Person_IsBDZ\",
			";
		}
		if (getRegionNick() == "kz") {
			$isBDZ = "
				case
					when ps.Person_IsInErz = 1 then 'red'
					when ps.Person_IsInErz = 2 then 'true'
					else 'false'
				end as \"Person_IsBDZ\",
				case
					when per.Person_IsInFOMS = 1 then 'orange'
					when per.Person_IsInFOMS = 2 then 'true'
					else 'false'
				end as \"Person_IsInFOMS\",
			";
		}
		$selectString = "
			ps.Person_id as \"Person_id\",
			ps.Server_id as \"Server_id\",
			ps.PersonEvn_id as \"PersonEvn_id\",
			ps.Person_IsInErz as \"Person_IsInErz\",
			ps.Person_Phone as \"Person_Phone\",
			ps.Person_Inn as \"Person_Inn\",
			CASE WHEN COALESCE(OC.OftenCallers_id,0) = 0 THEN 1 ELSE 2 END as \"Person_isOftenCaller\",
			CASE WHEN PersonRefuse.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as \"Person_IsRefuse\",
			CASE WHEN (ps.Person_deadDT is not null) or (ps.Person_IsDead = 2) THEN 'true' ELSE 'false' END as \"Person_IsDead\",
			CASE WHEN fedl.Person_id is not null then 'true' else 'false' end as \"Person_IsFedLgot\",
			CASE WHEN regl.OwnLpu = 1 THEN 'true' ELSE CASE WHEN regl.OwnLpu is not null THEN 'gray' ELSE 'false' END END as \"Person_IsRegLgot\",
			CASE WHEN disp.OwnLpu = 1 THEN 'true' ELSE CASE WHEN disp.OwnLpu is not null THEN 'gray' ELSE 'false' END END as \"Person_Is7Noz\",
			uaddr.Address_Address as \"UAddress_AddressText\",
			paddr.Address_Address as \"PAddress_AddressText\",
			{$isBDZ}
			CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as \"PersonQuarantine_IsOn\",
			to_char(PQ.PersonQuarantine_begDT, 'DD.MM.YYYY') as \"PersonQuarantine_begDT\",
			CASE WHEN exists (
				select PersonCard_id
				from v_PersonCard
				where Person_id = ps.Person_id
				  and LpuAttachType_id = 5
				  and PersonCard_endDate >= dbo.tzGetdate()
				  and CardCloseCause_id is null
			) THEN 'true' ELSE 'false' END as \"PersonCard_IsDms\"
			{$select_person_data}
			{$extraSelect}
		";
		$fromString = "
			ps 
			{$join}
			left join Person per on per.Person_id = ps.Person_id
			left join lateral (
					select PQ.*
					from v_PersonQuarantine PQ
					where PQ.Person_id = ps.Person_id 
					and PQ.PersonQuarantine_endDT is null
					limit 1
			) PQ on true
			left join lateral (
				select PersonCardState_Code
				from PersonCardState 
				where Person_id = ps.Person_id
				  and Lpu_id = :Lpu_id
				  and LpuAttachType_id = 1
                   limit 1
			) as pcard1 ON true
			left join Polis pls  on pls.Polis_id = ps.Polis_id
			left join v_OrgSMO vos on vos.OrgSmo_id = pls.OrgSmo_id
			--Информаия про СМО refs #PROMEDWEB-5677				
			left join lateral (
			    select 
					pc.Person_id as PersonCard_Person_id,
					pc.Lpu_id
				from v_PersonCard pc 
				where pc.Person_id = ps.Person_id
				  and LpuAttachType_id = 1
				  and {$filterscard}
				order by PersonCard_begDate desc
                   limit 1
			) as pcard ON true
			LEFT JOIN lpu ON pcard.Lpu_id=lpu.Lpu_id
			LEFT JOIN org ON org.org_id = lpu.org_id 
			left join lateral (
				select PersonRefuse_IsRefuse
				from v_PersonRefuse pr 
				where pr.Person_id = ps.Person_id
				  and pr.PersonRefuse_IsRefuse = 2
				  and pr.PersonRefuse_Year = date_part('YEAR', dbo.tzGetdate())
                   limit 1
			) as PersonRefuse ON true
			left join lateral (
				select Person_id
				from
			    	v_personprivilege pp 
					left join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
				where pp.person_id = ps.person_id
				  and pt.ReceptFinance_id = 1
				  and pp.personprivilege_begdate <= dbo.tzGetdate()
				  and COALESCE(pp.personprivilege_enddate, dbo.tzGetdate()) >= dbo.tzGetdate()::date
                   limit 1
			) as fedl ON true
			left join lateral (
				select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
				from
			    	v_personprivilege pp 
					left join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
				where pp.person_id = ps.person_id
				  and pt.ReceptFinance_id = 2
				  and pp.personprivilege_begdate <= dbo.tzGetdate()
				  and COALESCE(pp.personprivilege_enddate, dbo.tzGetdate()) >= dbo.tzGetdate()::date
			) as regl ON true
			left join lateral (
				select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
				from v_PersonDisp
				where Person_id = ps.Person_id
				  and COALESCE(PersonDisp_endDate, dbo.tzGetdate()+interval '1 day') > dbo.tzGetdate()
				  and Sickness_id in (1,3,4,5,6,7,8)
			) as disp ON true
			left join lateral (
				select OftenCallers_id
				from v_OftenCallers 
				where Person_id = ps.Person_id
                   limit 1
			) OC ON true
			left join v_Address uaddr on ps.UAddress_id = uaddr.Address_id
			left join v_Address paddr on ps.PAddress_id = paddr.Address_id
		";
		$whereString = "(".Implode(' and ', $filters)."){$includePerson_ids}";
		$where_withString = "WHERE ".Implode(' and ', $filters_with);
		$orderByString = "
			{$orderFirst}
			ps.Person_SurNameR ASC, 
			ps.Person_FirNameR ASC, 
			ps.Person_SecNameR ASC
		";
		$sql = "
			-- addit with
			WITH ps AS (
			SELECT * FROM v_PersonState ps
			$where_withString
			) 
			-- end addit with
			select
			-- select
				{$selectString}
			-- end select
			from
			-- from
				{$fromString}
			-- end from
			where
			-- where
			{$whereString}
			-- end where
			order by 
			-- order by
				{$orderByString}
			-- end order by
			limit 1000
		";
		return $callObject->getPagingResponse($sql, $queryParams, $data["start"], $data["limit"], true);
	}

	/**
	 * Поиск человека в форме РПН: Прикрепление
	 * В отличие от предыдущего поиска человека добавлено больше фильтров
	 * @param Person_model $callObject
	 * @param $data
	 * @param bool $print
	 * @param bool $get_count
	 * @return array|bool
	 */
	public static function getPersonCardGrid(Person_model $callObject, $data, $print = false, $get_count = false)
	{
		$filters = [];
		$queryParams = [];
		$mongoParams = [];
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		if (!empty($data["soc_card_id"]) && mb_strlen($data["soc_card_id"]) >= 25) {
			$filters[] = "left(ps.Person_SocCardNum, 19) = :SocCardNum";
			$queryParams["SocCardNum"] = mb_substr($data["soc_card_id"], 0, 19);
		}
		if (!empty($data["Person_SurName"]) && $data["Person_SurName"] != "_") {
			$filters[] = "LOWER(ps.Person_SurName) LIKE LOWER(:Person_SurName)||'%'";
			$queryParams["Person_SurName"] = rtrim($data["Person_SurName"]);
			$mongoParams["Person_SurName"] = (checkMongoDb() == "mongodb")
				? new MongoDB\BSON\Regex("^{$data["Person_SurName"]}", "i")
				: new MongoRegex("/^{$data["Person_SurName"]}/i");
		}
		if (!empty($data["Person_FirName"]) && $data["Person_FirName"] != "_") {
			$filters[] = "LOWER(ps.Person_FirName) LIKE LOWER(:Person_FirName)||'%'";
			$queryParams["Person_FirName"] = rtrim($data["Person_FirName"]);
			$mongoParams["Person_FirName"] = (checkMongoDb() == "mongodb")
				? new MongoDB\BSON\Regex("^{$data["Person_FirName"]}", "i")
				: new MongoRegex("/^{$data["Person_FirName"]}/i");
		}
		if (!empty($data["Person_SecName"]) && $data["Person_SecName"] != "_") {
			$filters[] = "LOWER(ps.Person_SecName) LIKE LOWER(:Person_SecName)||'%'";
			$queryParams["Person_SecName"] = rtrim($data["Person_SecName"]);
			$mongoParams["Person_SecName"] = (checkMongoDb() == "mongodb")
				? new MongoDB\BSON\Regex("^{$data["Person_SecName"]}", "i")
				: new MongoRegex("/^{$data["Person_SecName"]}/i");
		}
		if (!empty($data["Person_BirthDay"][0]) || !empty($data["Person_BirthDay"][1])) {
			if (!empty($data["Person_BirthDay"][0])) {
				$filters[] = "ps.Person_BirthDay >= :Person_BirthDayStart";
				$queryParams["Person_BirthDayStart"] = $data["Person_BirthDay"][0];
			}
			if (!empty($data["Person_BirthDay"][1])) {
				$filters[] = "ps.Person_BirthDay <= :Person_BirthDayEnd";
				$queryParams["Person_BirthDayEnd"] = $data["Person_BirthDay"][1];
			}
		}
		if (!empty($data["Person_Snils"])) {
			$filters[] = "ps.Person_Snils = :Person_Snils";
			$queryParams["Person_Snils"] = $data["Person_Snils"];
		}
		if (!empty($data["Person_Inn"])) {
			$filters[] = "ps.Person_Inn = :Person_Inn";
			$queryParams["Person_Inn"] = $data["Person_Inn"];
		}
		if (!($data["PersonAge_From"] == 0 && $data["PersonAge_To"] == 200)) {
			$filters[] = "(dbo.Age2(ps.Person_BirthDay,dbo.tzGetDate()) between :PersonAge_From and :PersonAge_To)";
			$queryParams["PersonAge_From"] = $data["PersonAge_From"];
			$queryParams["PersonAge_To"] = $data["PersonAge_To"];
		}
		if (!empty($data["KLAreaType_id"]) || !empty($data["KLCountry_id"]) || !empty($data["KLRgn_id"]) || !empty($data["KLSubRgn_id"]) || !empty($data["KLCity_id"]) || !empty($data["KLTown_id"]) || !empty($data["KLStreet_id"]) || !empty($data["Address_House"]) || !empty($data["Address_Corpus"])) {
			if ($data["AddressStateType_id"] == 1) {
				$fields = ["KLCountry_id", "KLRgn_id", "KLSubRgn_id", "KLCity_id", "KLTown_id", "KLStreet_id", "Address_House", "Address_Corpus", "KLAreaType_id"];
				foreach ($fields as $field) {
					if (!empty($data[$field])) {
						$filters[] = "uaddr.{$field} = :{$field}";
						$queryParams[$field] = $data[$field];
					}
				}
			} elseif ($data["AddressStateType_id"] == 2) {
				$fields = ["KLCountry_id", "KLRgn_id", "KLSubRgn_id", "KLCity_id", "KLTown_id", "KLStreet_id", "Address_House", "KLAreaType_id"];
				foreach ($fields as $field) {
					if (!empty($data[$field])) {
						$filters[] = "paddr.{$field} = :{$field}";
						$queryParams[$field] = $data[$field];
					}
				}
			} else {
				$fields = ["KLCountry_id", "KLRgn_id", "KLSubRgn_id", "KLCity_id", "KLTown_id", "KLStreet_id", "Address_House", "Address_Corpus", "KLAreaType_id"];
				foreach ($fields as $field) {
					if (!empty($data[$field])) {
						$filters[] = "uaddr.{$field} = :{$field}";
						$filters[] = "paddr.{$field} = :{$field}";
						$queryParams[$field] = $data[$field];
					}
				}
			}
		}
		if (!empty($data["RegisterSelector_id"]) && in_array($data["RegisterSelector_id"], [1, 2])) {
			// Вхождение в регистр льготников
			$filters[] = "
				exists (
					select PersonPrivilege_id
					from
						v_PersonPrivilege t1 
						inner join v_PrivilegeType t2 on t2.PrivilegeType_id = t1.PrivilegeType_id
					where t1.Person_id = ps.Person_id
					  and t2.ReceptFinance_id = :ReceptFinance_id
					  and t1.PersonPrivilege_begDate <= dbo.tzGetDate()
					  and coalesce(t1.PersonPrivilege_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()::date
				)
			";
			$queryParams["ReceptFinance_id"] = $data["RegisterSelector_id"];
		}
		if (!empty($data["Refuse_id"])) {
			// Отказ от льготы
			$filters[] = ($data["Refuse_id"] == 1 ? "not " : "") . "exists (
				select PersonRefuse_IsRefuse
				from v_PersonRefuse 
				where Person_id = ps.Person_id
				  and PersonRefuse_IsRefuse = 2
				  and PersonRefuse_Year = date_part('YEAR', dbo.tzGetDate())
			)";
		}
		if (!empty($data["RefuseNextYear_id"])) {
			// Отказ от льготы на следующий год
			$filters[] = ($data["RefuseNextYear_id"] == 1 ? "not " : "") . "exists (
				select PersonRefuse_IsRefuse
				from v_PersonRefuse 

				where
					Person_id = ps.Person_id
					and PersonRefuse_IsRefuse = 2
					and PersonRefuse_Year = date_part('YEAR',dbo.tzGetDate() + interval '1 day')
			)";
		}
		if (!empty($data["PersonCard_IsActualPolis"])) {
			// Есть действующий полис
			$filters[] = ($data["PersonCard_IsActualPolis"] == 1 ? "not " : "") . "exists (
				select Polis_id as \"Polis_id\"
				from v_Polis 
				where Polis_id = ps.Polis_id
				  and coalesce(Polis_endDate, dbo.tzGetDate()+interval '1 day') > dbo.tzGetDate()::date
			)";
		}
		if (!empty($data["dontShowUnknowns"])) {
			// #158923 показывать ли неизвестных в РПН: Прикрепление
			$filters[] = "coalesce(ps.Person_IsUnknown, 1) != 2";
		}
		if (!empty($data["PersonCard_Code"]) || !empty($data["LpuRegion_id"]) || !empty($data["LpuRegion_Fapid"]) || !empty($data["LpuRegionType_id"]) || !empty($data["LpuRegionType_id"]) || !empty($data["PersonCard_begDate"][0]) || !empty($data["PersonCard_begDate"][1]) || !empty($data["PersonCard_endDate"][0]) || !empty($data["PersonCard_endDate"][1]) || !empty($data["AttachLpu_id"]) || !empty($data["PersonCard_IsAttachCondit"]) || (!empty($data["PersonCardStateType_id"]) && $data["PersonCardStateType_id"] != 3)) {
			// Фильтры по прикреплению
			$personCardFilters = ["Person_id = ps.Person_id"];
			if (!empty($data["PersonCard_Code"])) {
				$personCardFilters[] = "PersonCard_Code = :PersonCard_Code";
				$queryParams["PersonCard_Code"] = $data["PersonCard_Code"];
			}
			if (!empty($data["LpuRegion_id"])) {
				$personCardFilters[] = ($data["LpuRegion_id"] == -1) ? "LpuRegion_id is null" : "LpuRegion_id = :LpuRegion_id";
				if ($data["LpuRegion_id"] != -1) {
					$queryParams["LpuRegion_id"] = $data["LpuRegion_id"];
				}
			}
			if (!empty($data["LpuRegion_Fapid"])) {
				$personCardFilters[] = "LpuRegion_fapid = :LpuRegion_Fapid";
				$queryParams["LpuRegion_Fapid"] = $data["LpuRegion_Fapid"];
			}
			if (!empty($data["LpuRegionType_id"])) {
				$personCardFilters[] = "LpuRegionType_id = :LpuRegionType_id";
				$queryParams["LpuRegionType_id"] = $data["LpuRegionType_id"];
			}
			if (!empty($data["PersonCard_begDate"][0])) {
				$personCardFilters[] = "PersonCard_begDate::date >= :PersonCard_begDateStart";
				$queryParams["PersonCard_begDateStart"] = $data["PersonCard_begDate"][0];
			}
			if (!empty($data["PersonCard_begDate"][1])) {
				$personCardFilters[] = "PersonCard_begDate::date <= :PersonCard_begDateEnd";
				$queryParams["PersonCard_begDateEnd"] = $data["PersonCard_begDate"][1];
			}
			if (!empty($data["PersonCard_endDate"][0])) {
				$personCardFilters[] = "PersonCard_endDate::date >= :PersonCard_endDateStart";
				$queryParams["PersonCard_endDateStart"] = $data["PersonCard_endDate"][0];
			}
			if (!empty($data["PersonCard_endDate"][1])) {
				$personCardFilters[] = "PersonCard_endDate::date <= :PersonCard_endDateEnd";
				$queryParams["PersonCard_endDateEnd"] = $data["PersonCard_endDate"][1];
			}
			if (!empty($data["AttachLpu_id"])) {
				$personCardFilters[] = "Lpu_id = :AttachLpu_id";
				$queryParams["AttachLpu_id"] = $data["AttachLpu_id"];
			}
			if (!empty($data["PersonCard_IsAttachCondit"])) {
				$personCardFilters[] = "COALESCE(PersonCard_IsAttachCondit, 1) = :PersonCard_IsAttachCondit";
				$queryParams["PersonCard_IsAttachCondit"] = $data["PersonCard_IsAttachCondit"];
			}
			if (!empty($data["PersonCardStateType_id"]) && $data["PersonCardStateType_id"] == 1) {
				$personCardFilters[] = "coalesce(PersonCard_endDate, dbo.tzGetDate()+interval '1 day') > dbo.tzGetDate()";
			}
			$filters[] = "
				exists (
					select PersonCard_id
					from v_PersonCard" . (!empty($data["PersonCardStateType_id"]) && $data["PersonCardStateType_id"] == 1 ? "" : "_all") . " 
					where " . implode(" and ", $personCardFilters) . "
				)
			";
		}
		// Выборка людей
		$limit = !$print ? "limit 1000" : "";
		$whereString = " (1=1) " . (empty($filters) ? "" : " and " . implode(" and ", $filters));
		$orderByString = "ps.Person_id";
		$query = "
			select
				-- select
				ps.Person_id as \"Person_id\",
				ps.Server_id as \"Server_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				coalesce(paddr.Address_Nick, paddr.Address_Address) as \"Person_PAddress\",
				coalesce(uaddr.Address_Nick, uaddr.Address_Address) as \"Person_UAddress\",
				to_char(ps.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"PersonBirthDay\",
				to_char(ps.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\",
				null as \"Person_IsBDZ\",
				null as \"Lpu_Nick\",
				null as \"Person_IsRefuse\",
				null as \"Person_IsFedLgot\",
				null as \"Person_IsRegLgot\",
				null as \"Person_Is7Noz\",
				null as \"PersonCard_IsDms\"
				-- end select
			from
				-- from
				v_PersonState ps 
				left join Address uaddr on uaddr.Address_id = ps.UAddress_id
				left join Address paddr on paddr.Address_id = ps.PAddress_id
				-- end from
			where
				-- where
				{$whereString}
				-- end where
			order by
				-- order by
				{$orderByString}
				-- end order by
			{$limit}
		";
		$response = $callObject->getPagingResponse($query, $queryParams, $data["start"], $data["limit"], true);
		$personIdList = [];
		foreach ($response["data"] as $row) {
			$personIdList[] = $row["Person_id"];
		}
		$isPerm = $data["session"]["region"]["nick"] == "perm";
		$isBDZ = "
			case when pls.Polis_endDate is not null and pls.Polis_endDate <= dbo.tzGetDate()
				then 'orange'
				else case
					when ps.PersonCloseCause_id = 2 and ps.Person_closeDT is not null
					then 'red'
					else case when ps.Server_pid = 0
						then 'true'
						else 'false'
					end
				end
			end as \"Person_IsBDZ\",
		";
		if ($isPerm) {
			$isBDZ = "
				case
					when ps.Server_pid = 0 then 'true'
					when ps.Person_IsInErz = 1 then 'blue'
					when ps.Server_pid = 0 and pls.Polis_endDate < dbo.tzGetDate()::date then
						case when ps.Person_deadDT is null then 'yellow' else 'red' end
					when ps.Server_pid = 2 and ps.Person_IsInErz <> 1 then 'false'
				end as \"Person_IsBDZ\",
			";
		}
		if (count($personIdList) > 0) {
			$personIdListString = implode(", ", $personIdList);
			$selectString = "
				ps.Person_id as \"Person_id\",
				l.Lpu_Nick as \"Lpu_Nick\",
				{$isBDZ}
				case when ref.PersonRefuse_IsRefuse = 2 then 'true' else 'false' end as \"Person_IsRefuse\",
				case when fedl.Person_id is not null then 'true' else 'false' end as \"Person_IsFedLgot\",
				case
					when regl.Lpu_id is null then 'false'
					when regl.Lpu_id = :Lpu_id then 'true'
					else 'gray'
				end as \"Person_IsRegLgot\",
				case
					when disp.Lpu_id is null then 'false'
					when disp.Lpu_id = :Lpu_id then 'true'
					else 'gray'
				end as \"Person_Is7Noz\",
				case when dms.PersonCard_id is not null then 'true' else 'false' end as \"PersonCard_IsDms\"
			";
			$query = "
				select {$selectString}
				from
					v_PersonState ps 
					left join v_Polis pls on pls.Polis_id = ps.Polis_id
					left join lateral (
						select Lpu_id
						from v_PersonCard 
						where Person_id = ps.Person_id
						  and LpuAttachType_id = 1
						  and PersonCard_begDate <= dbo.tzGetDate()
						  and coalesce(PersonCard_endDate, dbo.tzGetDate()+interval '1 day') > dbo.tzGetDate()::date
						order by PersonCard_begDate desc
                       	limit 1
					) as pc ON true
					left join v_Lpu l  on l.Lpu_id = pc.Lpu_id
					left join lateral (
						select PersonRefuse_IsRefuse
						from v_PersonRefuse 
						where Person_id = ps.Person_id
						  and PersonRefuse_IsRefuse = 2
						  and PersonRefuse_Year = date_part('YEAR', dbo.tzGetDate())
                        limit 1
					) as ref ON true
					left join lateral (
						select Person_id
						from
					    	v_PersonPrivilege t1 
							inner join v_PrivilegeType t2 on t2.PrivilegeType_id = t1.PrivilegeType_id
						where t1.Person_id = ps.Person_id
						  and t2.ReceptFinance_id = 1
						  and t1.PersonPrivilege_begDate <= dbo.tzGetDate()
						  and coalesce(t1.PersonPrivilege_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()::date
                        limit 1
					) as fedl ON true
					left join lateral (
						select Lpu_id
						from
					    	v_PersonPrivilege t1 
							inner join v_PrivilegeType t2 on t2.PrivilegeType_id = t1.PrivilegeType_id
						where t1.Person_id = ps.Person_id
						  and t2.ReceptFinance_id = 2
						  and t1.PersonPrivilege_begDate <= dbo.tzGetDate()
						  and coalesce(t1.PersonPrivilege_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()::date
						order by
							case when t1.Lpu_id = :Lpu_id then 1 else 0 end desc
                        limit 1
					) as regl ON true
					left join lateral (
						select PersonCard_id
						from v_PersonCard 
						where Person_id = ps.Person_id
						  and LpuAttachType_id = 5
						  and PersonCard_endDate >= dbo.tzGetDate()
						  and CardCloseCause_id is null
                        limit 1
					) as dms ON true
					left join lateral (
						select Lpu_id
						from v_PersonDisp 
						where Person_id = ps.Person_id
						  and coalesce(PersonDisp_endDate, dbo.tzGetDate()+ interval '1 day') >= dbo.tzGetDate()::date
						  and Sickness_id in (1, 3, 4, 5, 6, 7, 8)
						order by
							case when Lpu_id = :Lpu_id then 1 else 0 end desc
                        limit 1
					) as disp ON true
				where ps.Person_id in ({$personIdListString})
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			$res = $result->result_array();
			if (!is_array($res) || count($res) == 0) {
				return false;
			}
			foreach ($res as $row) {
				foreach ($response["data"] as $key => $array) {
					if ($row["Person_id"] == $array["Person_id"]) {
						$response["data"][$key] = array_merge($response["data"][$key], $row);
					}
				}
			}
		}
		return $response;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @param bool $print
	 * @param bool $get_count
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getPersonGrid(Person_model $callObject, $data, $print = false, $get_count = false)
	{
		$filters = [
			'1 = 1'
		];
		$queryParams = [];
		$filterfio = "";
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		if (!empty($data["Person_id"])) {
			$queryParams["Person_id"] = $data["Person_id"];
			$filterfio .= "and ps.Person_id = :Person_id ";
		}
		$isSearchByEncryp = false;
		$select_person_data = "
			case when PC.Lpu_id = :Lpu_id then PC.PersonCard_Code else null end as \"PersonCard_Code\",
			PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
			PAC.PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\",
			COALESCE('<a href=''#'' onClick=''getWnd(\"swPolisInfoWindow\").show({Person_id:' || ps.Person_id::varchar || '});''>' || case when pls.PolisType_id = 4 and COALESCE(ps.Person_EdNum, '') != ''
				then ps.Person_EdNum
				else COALESCE(ps.Polis_Ser, '') || ' ' || COALESCE(ps.Polis_Num, '')
			end ||'</a>','') as \"Person_PolisInfo\",
			(select COALESCE(Person_Inn,'') from v_PersonState  where Person_id = ps.Person_id limit 1) as \"Person_Inn\",
			case when dbo.getPersonPhones(ps.Person_id, '<br />') != ''
				then COALESCE('<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' || ps.Person_id::varchar || '});''>'|| dbo.getPersonPhones(ps.Person_id, '<br />') ||'</a>','')
				else '<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' || ps.Person_id::varchar || '});''>'|| 'Отсутствует' ||'</a>'
			end as \"Person_Phone\",
			rtrim(PS.Person_SurName) as \"Person_Surname\",
			rtrim(PS.Person_FirName) as \"Person_Firname\",
			rtrim(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_BirthDay::timestamp, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			to_char(PS.Person_DeadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\",
			case when PS.Person_id is not null then dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) end as \"Person_Age\",
			coalesce(AttachLpu.Lpu_Nick, 'Не прикреплен') as \"AttachLpu_Name\",
			coalesce(AttachLpu.Lpu_id, 0) as \"AttachLpu_id\",
			to_char(cast(PC.PersonCard_begDate as timestamp), '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\",
			to_char(cast(PC.PersonCard_endDate as timestamp), '{$callObject->dateTimeForm104}') as \"PersonCard_endDate\",
			PC.LpuAttachType_Name as \"LpuAttachType_Name\",
			PC.LpuRegionType_Name as \"LpuRegionType_Name\",
			LR.LpuRegion_Name as \"LpuRegion_Name\",
			coalesce(LR_Fap.LpuRegion_Name,'') as \"LpuRegion_FapName\",
			coalesce(paddr.Address_Nick, paddr.Address_Address) as \"Person_PAddress\",
			coalesce(uaddr.Address_Nick, uaddr.Address_Address) as \"Person_UAddress\",
		";
		$join = "";
		if (isEncrypHIVRegion($callObject->regionNick)) {
			if (allowPersonEncrypHIV($data["session"])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data["Person_Surname"]);
				if ($isSearchByEncryp) {
					$select_person_data = "
						'' as \"PersonCard_Code\",
						'' as \"Person_PolisInfo\",
						'' as \"Person_Inn\",
						'' as \"Person_Phone\",
						peh.PersonEncrypHIV_Encryp as \"Person_Surname\",
						'' as \"Person_Firname\",
						'' as \"Person_Secname\",
						null as \"Person_Birthday\",
						null as \"Person_deadDT\",
						null as \"Person_Age\",
						'' as \"AttachLpu_Name\",
						null as \"AttachLpu_id\",
						null as \"PersonCard_begDate\",
						null as \"PersonCard_endDate\",
						'' as \"LpuAttachType_Name\",
						'' as \"LpuRegionType_Name\",
						'' as \"LpuRegion_Name\",
						'' as \"Person_PAddress\",
						'' as \"Person_UAddress\",
					";
				}
			} else {
				//Не отображать анонимных шифрованных пациентов
				$filters[] = "
					not exists(
						select peh.PersonEncrypHIV_Encryp
						from
							v_PersonEncrypHIV peh 
							inner join v_EncrypHIVTerr eht  on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id and coalesce(eht.EncrypHIVTerr_Code, 0) = 20
						where peh.Person_id = ps.Person_id
					)
				";
			}
		}
		if (!empty($data["Person_Surname"]) && $data["Person_Surname"] != "_") {
			if (allowPersonEncrypHIV($data["session"]) && $isSearchByEncryp) {
				$join .= " inner join v_PersonEncrypHIV peh on peh.Person_id = ps.Person_id and LOWER(peh.PersonEncrypHIV_Encryp) LIKE LOWER(:Person_Surname)";
			} else {
				$filterfio .= "and LOWER(ps.Person_SurNameR) LIKE LOWER(:Person_Surname)||'%' ";
			}
			$queryParams["Person_Surname"] = $callObject->prepareSearchSymbol($data["Person_Surname"]);
		}
		if (!empty($data["Person_Firname"]) && $data["Person_Firname"] != "_") {
			$queryParams["Person_Firname"] = $callObject->prepareSearchSymbol($data["Person_Firname"]);
			$filterfio .= "and LOWER(ps.Person_FirnameR) LIKE LOWER(:Person_Firname)||'%' ";
		}
		if (!empty($data["Person_Secname"]) && $data["Person_Secname"] != "_") {
			$queryParams["Person_Secname"] = $callObject->prepareSearchSymbol($data["Person_Secname"]);
			$filterfio .= "and LOWER(ps.Person_SecnameR) LIKE LOWER(:Person_Secname)||'%' ";
		}
		if (!empty($data["Person_Birthday"])) {
			$filters[] = "ps.Person_Birthday = :Person_Birthday";
			$queryParams["Person_Birthday"] = $data["Person_Birthday"];
		}
		if (!empty($data["Person_Code"])) {
			$filters[] = "ps.Person_EdNum = :Person_EdNum";
			$queryParams["Person_EdNum"] = $data["Person_Code"];
		}
		if (!empty($data["Person_Inn"])) {
			$filters[] = "exists (select Person_id from v_PersonState where Person_id = ps.Person_id and Person_Inn = :Person_Inn limit 1)";
			$queryParams["Person_Inn"] = $data["Person_Inn"];
		}
		if (!empty($data["Polis_Num"])) {
			$filters[] = "pls.Polis_Num = :Polis_Num";
			$queryParams["Polis_Num"] = $data["Polis_Num"];
		}
		if (!empty($data["Polis_Ser"])) {
			$filters[] = "pls.Polis_Ser = :Polis_Ser";
			$queryParams["Polis_Ser"] = $data["Polis_Ser"];
		}
		if (!empty($data["dontShowUnknowns"])) {
			// #158923 показывать ли неизвестных в АРМ регистратора поликлиники
			$filters[] = "coalesce(PS.Person_IsUnknown, 1) != 2";
		}
		// Фильтр по адресу
		if (!empty($data["Address_Street"]) || !empty($data["Address_House"])) {
			if ((empty($data["Person_Surname"]) || !preg_match("/^[\w\-\s]+$/iu", $data["Person_Surname"])) && (empty($data["Person_Firname"]) || !preg_match("/^[\w\-\s]+$/iu", $data["Person_Firname"])) && (empty($data["Person_Secname"]) || !preg_match("/^[\w\-\s]+$/iu", $data["Person_Secname"]))) {
				// Для поиска по адресу требуется заполнить хотя бы одно поле из ФИО
				return false;
			}
			$addrFilters = [];
			if (!empty($data["Address_Street"])) {
				$addrFilters[] = "LOWER(ks.KLStreet_Name) LIKE LOWER(:Address_Street)||'%'";
				$queryParams["Address_Street"] = $data["Address_Street"];
			}
			if (!empty($data["Address_House"])) {
				$addrFilters[] = "a.Address_House = :Address_House";
				$queryParams["Address_House"] = $data["Address_House"];
			}
			$addrFiltersString = implode(" and ", $addrFilters);
			$filters[] = "
				exists(
					select Address_id
					from
						Address a 
						left join v_KLStreet ks on ks.KLStreet_id = a.KLStreet_id
					where a.Address_id in (ps.UAddress_id, ps.PAddress_id)
					  and {$addrFiltersString}
				)
			";
		}
		$orderby = "";
		// Фильтры по прикреплению
		if (!empty($data["PersonCard_Code"])) {
			$personCardFilters = ["Person_id = ps.Person_id"];
			$personCardFilters[] = "Lpu_id = :Lpu_id";
			if (!empty($data["PartMatchSearch"])) {
				// включен чекбокс "Поиск по частичному совпадению"
				if (!empty($callObject->config->config["blockSlowDownFunctions"])) {
					throw new Exception("Функционал поиска по частичному совпадению временно заблокирован. Приносим извинения за доставленные неудобства.");
				}
				$personCardFilters[] = "LOWER(PersonAmbulatCard_Num) LIKE '%'||LOWER(:PersonCard_Code)||'%'";
				$orderby = "case when coalesce(strpos(:PersonCard_Code, pc.PersonCard_Code), 0) > 0 then strpos(:PersonCard_Code, pc.PersonCard_Code) else 99 end,";
			} else {
				$personCardFilters[] = "PersonAmbulatCard_Num = :PersonCard_Code";
			}
			$personCardFiltersString = implode(" and ", $personCardFilters);
			$queryParams["PersonCard_Code"] = $data["PersonCard_Code"];
			$filters[] = "
				exists (
					select PersonAmbulatCard_id
					from v_PersonAmbulatCard 
					where {$personCardFiltersString}
				)
			";
		}
		if (count($queryParams) <= 1) {
			throw new Exception(toUtf("Не указан ни один фильтр. Для поиска заполните хотя бы один параметр в блоке фильтров."));
		}
		$isPerm = $data["session"]["region"]["nick"] == "perm";
		$isBDZ = "
			case when pls.Polis_endDate is not null and pls.Polis_endDate <= dbo.tzGetDate()
				then 'orange'
				else case when ps.PersonCloseCause_id = 2 and ps.Person_closeDT is not null
					then 'red'
					else case when ps.Server_pid = 0
						then 'true'
						else 'false'
					end
				end
			end as \"Person_IsBDZ\",
		";
		if ($isPerm) {
			$isBDZ = "
				case  when ps.Server_pid = 0
					then case when ps.Person_IsInErz = 1
						then 'blue' 
                        else case when pls.Polis_endDate is not null and pls.Polis_endDate <= dbo.tzGetDate()
                            then case when ps.Person_deadDT is not null
                                then 'red'
                                else 'yellow'
                            end
                            else 'true'
                        end
					end 
                    else 'false'
				end as \"Person_IsBDZ\",
            ";
		}
		if (getRegionNick() == 'kz') {
			$isBDZ ="case
				when pers.Person_IsInFOMS = 1 then 'orange'
				when pers.Person_IsInFOMS = 2 then 'true'
				else 'false'
			end as \"Person_IsBDZ\",";
		}
		// Основной поисковый запрос
		$selectString = "
			PC.PersonCard_id as \"PersonCard_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			{$select_person_data}
			case when PS.Person_DeadDT is not null  then 'true' else 'false' end as \"Person_IsDead\",
			case when coalesce(PC.PersonCard_IsAttachCondit, 1) = 2 then 'true' else 'false' end as \"PersonCard_IsAttachCondit\",
			case when persdata.agree = 2 then 'V' when persdata.agree = 1 then 'X' else '' end as \"PersonLpuInfo_IsAgree\",
			NA.NewslatterAccept_id as \"NewslatterAccept_id\",
			coalesce(to_char(NA.NewslatterAccept_begDate, '{$callObject->dateTimeForm104}'), 'Отсутствует') as \"NewslatterAccept\",
			case when PC.PersonCardAttach_id is null then 'false' else 'true' end as \"PersonCardAttach\",
			case when PS.Person_IsRefuse = 1 then 'true' else 'false' end as \"Person_IsRefuse\",
			case when PRef.PersonRefuse_IsRefuse = 2 then 'true' else 'false' end as \"Person_NextYearRefuse\",
			case when PS.Person_IsFedLgot = 1 then 'true' else 'false' end as \"Person_IsFedLgot\",
			case when PS.Person_IsRegLgot = 1 then 'true' else 'false' end as \"Person_IsRegLgot\",
			{$isBDZ}
			case when exists(
				select * 
				from v_PersonQuarantine PQ
				where PQ.Person_id = PS.Person_id
				and PQ.PersonQuarantine_endDT is null
			) then 'true' else 'false' end as \"PersonQuarantine_IsOn\",
			case 
				when disp.OwnLpu = 1 then 'true'
				when disp.OwnLpu is not null then 'gray'
				else 'false'
			end as \"Person_Is7Noz\"
		";
		$whereString = implode(" and ", $filters);
		$query = "
			select
				-- select
				{$selectString}
				-- end select
			from
				-- from
				v_PersonState_All PS 
                left join lateral (
                    select 
                        case 
                            when LpuAttachType_id = 4 and Lpu_id = :Lpu_id then PersonCard_id 
                            when LpuAttachType_id = 1 then PersonCard_id
                            when LpuAttachType_id in (2, 3) and Lpu_id = :Lpu_id then PersonCard_id
                            else null
                        end as PersonCard_id
                    from v_PersonCard_all 
                    where Person_id = PS.Person_id
					  and PersonCard_endDate is null
					  and LpuAttachType_id is not null
                    order by
						case when LpuAttachType_id = 4 and Lpu_id = :Lpu_id then 0 else LpuAttachType_id end,
						PersonCard_begDate
                    limit 1
                ) as PersonCard on true
				left join v_PersonCard_all PC on PC.PersonCard_id = PersonCard.PersonCard_id
                left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
                left join v_LpuRegion LR_Fap on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
				left join v_Lpu AttachLpu on AttachLpu.Lpu_id = PC.Lpu_id 
                left join Address uaddr on uaddr.Address_id = ps.UAddress_id
                left join Address paddr on paddr.Address_id = ps.PAddress_id
                left join v_Polis pls on pls.Polis_id = ps.Polis_id
				left join v_Person pers on pers.Person_id = ps.Person_id
				left join PersonRefuse PRef on (PRef.Person_id = ps.Person_id and PRef.PersonRefuse_Year=date_part('YEAR',dbo.tzGetDate()+interval '1 day' )) 
				left join v_NewslatterAccept NA on NA.Person_id = PS.Person_id and NA.Lpu_id = :Lpu_id and NA.NewslatterAccept_endDate is null
				left join lateral (
                    select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
                    from PersonDisp 
                    where Person_id = ps.Person_id
					  and (PersonDisp_endDate is null or PersonDisp_endDate > dbo.tzGetDate())
                      and Sickness_id in (1, 3, 4, 5, 6, 7, 8)
                ) as disp ON true
				left join lateral (
					select PersonLpuInfo_IsAgree as agree
					from v_PersonLpuInfo pli 
					where pli.Person_id = PS.Person_id
				      and pli.Lpu_id = :Lpu_id
					order by pli.PersonLpuInfo_setDT desc
                    limit 1
				) as persdata ON true
				left join lateral (
					select
						PersonAmbulatCard_id,
						PersonAmbulatCard_Num
					from v_PersonAmbulatCard 
					where Person_id = PS.Person_id
				      and Lpu_id = :Lpu_id
				      and coalesce(PersonAmbulatCard_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
					order by Person_id desc
                    limit 1
				) as PAC ON true
				{$join}
				-- end from
			where
				-- where
				{$whereString} {$filterfio} 
				-- end where
			order by
				-- order by
				{$orderby}
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName
				-- end order by
		";
		return $callObject->getPagingResponse($query, $queryParams, $data["start"], $data["limit"], true);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonGridPersonCardAuto(Person_model $callObject, $data)
	{
		$params = [];
		$and = "";
		$orgFilter = "(1 = 1)";
		if (!empty($data["Sex_id"])) {
			$and .= " and PS.Sex_id = :Sex_id";
			$params["Sex_id"] = $data["Sex_id"];
		}
		if (!empty($data["PSPCAOrg_id"])) {
			$orgFilter .= " and O.Org_id = :Org_id";
			$params["Org_id"] = $data["PSPCAOrg_id"];
		}
		if (!empty($data["PSPCALpuRegion_id"])) {
			if ($data["PSPCALpuRegion_id"] == -1) {
				$and .= " and PC.LpuRegion_id is null";
			} else {
				$and .= " and PC.LpuRegion_id = :LpuRegion_id";
				$params["LpuRegion_id"] = $data["PSPCALpuRegion_id"];
			}
		}
		if (!empty($data["LpuRegion_Fapid"])) {
			$and .= " and PC.LpuRegion_fapid = :LpuRegion_Fapid";
			$params["LpuRegion_Fapid"] = $data["LpuRegion_Fapid"];
		}
		if (!empty($data["PSPCALpuRegionType_id"])) {
			$and .= " and PC.LpuRegionType_id = :LpuRegionType_id";
			$params["LpuRegionType_id"] = $data["PSPCALpuRegionType_id"];
		}
		if (!empty($data["PersonAge_Min"])) {
			$and .= " and (dbo.Age2(ps.Person_BirthDay,dbo.tzGetDate()) >= :PersonAge_Min)";
			$params["PersonAge_Min"] = $data["PersonAge_Min"];
		}
		if (!empty($data["PersonAge_Max"])) {
			$and .= " and (dbo.Age2(ps.Person_BirthDay,dbo.tzGetDate()) <= :PersonAge_Max)";
			$params["PersonAge_Max"] = $data["PersonAge_Max"];
		}
		//Адрес проживания
		if (!empty($data["PKLCountry_id"])) {
			$and .= " and PA.KLCountry_id = :PKLCountry_id";
			$params["PKLCountry_id"] = $data["PKLCountry_id"];
		}
		if (!empty($data["PKLRGN_id"])) {
			$and .= " and PA.KLRGN_id = :PKLRGN_id";
			$params["PKLRGN_id"] = $data["PKLRGN_id"];
		}
		if (!empty($data["PKLSubRGN_id"])) {
			$and .= " and PA.KLSubRGN_id = :PKLSubRGN_id";
			$params["PKLSubRGN_id"] = $data["PKLSubRGN_id"];
		}
		if (!empty($data["PKLCity_id"])) {
			$and .= " and PA.KLCity_id = :PKLCity_id";
			$params["PKLCity_id"] = $data["PKLCity_id"];
		}
		if (!empty($data["PKLTown_id"])) {
			$and .= " and PA.KLTown_id = :PKLTown_id";
			$params["PKLTown_id"] = $data["PKLTown_id"];
		}
		if (!empty($data["PKLStreet_id"])) {
			$and .= " and PA.KLStreet_id = :PKLStreet_id";
			$params["PKLStreet_id"] = $data["PKLStreet_id"];
		}
		if (!empty($data["PAddress_House"])) {
			$and .= " and PA.Address_House = :PAddress_House";
			$params["PAddress_House"] = $data["PAddress_House"];
		}
		if (!empty($data["PAddress_Corpus"])) {
			$and .= " and PA.Address_Corpus = :PAddress_Corpus";
			$params["PAddress_Corpus"] = $data["PAddress_Corpus"];
		}
		if (!empty($data["PAddress_Flat"])) {
			$and .= " and PA.Address_Flat = :PAddress_Flat";
			$params["PAddress_Flat"] = $data["PAddress_Flat"];
		}
		//Адрес регистрации
		if (!empty($data["UKLCountry_id"])) {
			$and .= " and UA.KLCountry_id = :UKLCountry_id";
			$params["UKLCountry_id"] = $data["UKLCountry_id"];
		}
		if (!empty($data["UKLRGN_id"])) {
			$and .= " and UA.KLRGN_id = :UKLRGN_id";
			$params["UKLRGN_id"] = $data["UKLRGN_id"];
		}
		if (!empty($data["UKLSubRGN_id"])) {
			$and .= " and UA.KLSubRGN_id = :UKLSubRGN_id";
			$params["UKLSubRGN_id"] = $data["UKLSubRGN_id"];
		}
		if (!empty($data["UKLCity_id"])) {
			$and .= " and UA.KLCity_id = :UKLCity_id";
			$params["UKLCity_id"] = $data["UKLCity_id"];
		}
		if (!empty($data["UKLTown_id"])) {
			$and .= " and UA.KLTown_id = :UKLTown_id";
			$params["UKLTown_id"] = $data["UKLTown_id"];
		}
		if (!empty($data["UKLStreet_id"])) {
			$and .= " and UA.KLStreet_id = :UKLStreet_id";
			$params["UKLStreet_id"] = $data["UKLStreet_id"];
		}
		if (!empty($data["UAddress_House"])) {
			$and .= " and UA.Address_House = :UAddress_House";
			$params["UAddress_House"] = $data["UAddress_House"];
		}
		if (!empty($data["UAddress_Corpus"])) {
			$and .= " and UA.Address_Corpus = :UAddress_Corpus";
			$params["UAddress_Corpus"] = $data["UAddress_Corpus"];
		}
		if (!empty($data["UAddress_Flat"])) {
			$and .= " and UA.Address_Flat = :UAddress_Flat";
			$params["UAddress_Flat"] = $data["UAddress_Flat"];
		}
		$query = "
			-- addit with
			with LpuTable as (
				select Lpu_id, Lpu_Nick
				from
					v_lpu AS L 
					left join v_Org O  on O.Org_id = L.Org_id
				where {$orgFilter}
			)
			-- end addit with
			select
        	-- select
        		0 as \"Is_Checked\",
                PS.Person_id as \"Person_id\",
                coalesce(PS.Person_SurName,'') || ' ' || coalesce(PS.Person_FirName,'') || ' ' || coalesce(PS.Person_SecName,'') as \"Person_FIO\",
                to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
                case when PC.PersonCard_id is not null then 'Да' else 'Нет' end as \"PersonStatus\",
				case when PC.PersonCard_id is not null then coalesce(L.Lpu_Nick,'') else '' end as \"Lpu_Name\",
				case when PC.PersonCard_id is not null then coalesce(PC.LpuRegion_Name,'') else '' end as \"LpuRegion_Name\",
				case when PC.PersonCard_id is not null then coalesce(LR.LpuRegion_Name,'') else '' end as \"LpuRegion_FapName\",
				case when PC.PersonCard_id is not null then coalesce(PC.LpuRegionType_Name,'') else '' end as \"LpuRegionType_Name\",
                coalesce(UA.Address_Address,'') as \"UAddress_Name\",
                coalesce(PA.Address_Address,'') as \"PAddress_Name\",
                S.Sex_Name as \"Sex_Name\",
                CASE WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= dbo.tzGetdate()
                    THEN 'yellow'
                    ELSE CASE WHEN ps.PersonCloseCause_id = 2 and Person_closeDT is not null
                        THEN 'red'
                        ELSE CASE WHEN ps.Server_pid = 0
                            THEN 'true'
                            ELSE 'false'
						END
					END
				END as \"Person_IsBDZ\"
			-- end select
            from
            -- from
	            LpuTable L
				inner join v_PersonCard PC on PC.Lpu_id=L.Lpu_id and PC.LpuAttachType_id = 1
				inner join v_PersonState PS on PS.Person_id = PC.Person_id
				left join Sex S on S.Sex_id = PS.Sex_id
				LEFT JOIN lateral (select * from LpuRegion LR where LR.LpuRegion_id = PC.LpuRegion_fapid limit 1) LR on true
				LEFT JOIN lateral (select * from Address UA where UA.Address_id = PS.UAddress_id limit 1) UA on true
				LEFT JOIN lateral (select * from Address PA where PA.Address_id = PS.PAddress_id limit 1) PA on true
				LEFT JOIN lateral (select * from Polis pls where pls.Polis_id = ps.Polis_id limit 1) pls on true
            -- end from
            where
            -- where
                    PS.Person_deadDT is null
				and (COALESCE(PS.Person_SurName, '') <> '' or COALESCE(PS.Person_FirName, '') <> '' or COALESCE(PS.Person_SecName, '') <> '')
                and PC.LpuAttachType_id = 1
				{$and}
            -- end where
            order by
            -- order by
                PS.Person_SurName,
                PS.Person_FirName,
                PS.Person_SecName
            -- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
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
		$response = [];
		$response["data"] = $result->result_array();
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getPersonEvnEditWindow(Person_model $callObject, $data)
	{
		$sql = "
			select
				vper.Person_SurName as \"Person_SurName\",
				vper.Person_SecName as \"Person_SecName\",
				vper.Person_FirName as \"Person_FirName\",
				case when PersonPrivilegeFed.Person_id is not null then 1 else 0 end as \"Person_IsFedLgot\",
				vper.Server_pid as \"Server_pid\",
				vper.Person_id as \"Person_id\",
				to_char(vper.Person_BirthDay::timestamp, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				vper.Sex_id as \"PersonSex_id\",
				vper.Person_Snils as \"Person_SNILS\",
				vper.SocStatus_id as \"SocStatus_id\",
				vper.FamilyStatus_id as \"FamilyStatus_id\",
				vper.PersonFamilyStatus_IsMarried as \"PersonFamilyStatus_IsMarried\",
				vper.Person_edNum as \"Federal_Num\",
				vper.UAddress_id as \"UAddress_id\",
				baddr.Address_id as \"Address_id\",
				baddr.KLCountry_id as \"BKLCountry_id\",
				baddr.KLRGN_id as \"BKLRGN_id\",
				baddr.KLSubRGN_id as \"BKLSubRGN_id\",
				baddr.KLCity_id as \"BKLCity_id\",
				baddr.KLTown_id as \"BKLTown_id\",
				baddr.KLStreet_id as \"BKLStreet_id\",
				baddr.Address_House as \"BAddress_House\",
				baddr.Address_Corpus as \"BAddress_Corpus\",
				baddr.Address_Flat as \"BAddress_Flat\",
				baddr.Address_Address as \"BAddress_AddressText\",
				baddr.Address_Address as \"BAddress_Address\",
				baddr.PersonSprTerrDop_id as \"BPersonSprTerrDop_id\",
				uaddr.Address_Zip as \"UAddress_Zip\",
				uaddr.KLCountry_id as \"UKLCountry_id\",
				uaddr.KLRGN_id as \"UKLRGN_id\",
				uaddr.KLSubRGN_id as \"UKLSubRGN_id\",
				uaddr.KLCity_id as \"UKLCity_id\",
				uaddr.KLTown_id as \"UKLTown_id\",
				uaddr.KLStreet_id as \"UKLStreet_id\",
				uaddr.Address_House as \"UAddress_House\",
				uaddr.Address_Corpus as \"UAddress_Corpus\",
				uaddr.Address_Flat as \"UAddress_Flat\",
				uaddr.Address_Address as \"UAddress_AddressText\",
				uaddr.Address_Address as \"UAddress_Address\",
				uaddr.PersonSprTerrDop_id as \"UPersonSprTerrDop_id\",
				vper.PAddress_id as \"PAddress_id\",
				paddr.Address_Zip as \"PAddress_Zip\",
				paddr.KLCountry_id as \"PKLCountry_id\",
				paddr.KLRGN_id as \"PKLRGN_id\",
				paddr.KLSubRGN_id as \"PKLSubRGN_id\",
				paddr.KLCity_id as \"PKLCity_id\",
				paddr.KLTown_id as \"PKLTown_id\",
				paddr.KLStreet_id as \"PKLStreet_id\",
				paddr.Address_House as \"PAddress_House\",
				paddr.Address_Corpus as \"PAddress_Corpus\",
				paddr.Address_Flat as \"PAddress_Flat\",
				paddr.Address_Address as \"PAddress_AddressText\",
				paddr.Address_Address as \"PAddress_Address\",
				paddr.PersonSprTerrDop_id as \"PPersonSprTerrDop_id\",
				pol.OmsSprTerr_id as \"OMSSprTerr_id\",
				pol.PolisType_id as \"PolisType_id\",
				pol.Polis_Ser as \"Polis_Ser\",
				pol.PolisFormType_id as \"PolisFormType_id\",
				case when pol.PolisType_id = 4 then '' else pol.Polis_Num end as \"Polis_Num\",
				pol.OrgSmo_id as \"OrgSMO_id\",
				to_char(pol.Polis_begDate::timestamp, '{$callObject->dateTimeForm104}') as \"Polis_begDate\",
				to_char(pol.Polis_endDate::timestamp, '{$callObject->dateTimeForm104}') as \"Polis_endDate\",
				doc.DocumentType_id as \"DocumentType_id\",
				doc.Document_Ser as \"Document_Ser\",
				doc.Document_Num as \"Document_Num\",
				doc.OrgDep_id as \"OrgDep_id\",
				ns.KLCountry_id as \"KLCountry_id\",
				ns.LegalStatusVZN_id as \"LegalStatusVZN_id\",
				case when ns.NationalityStatus_IsTwoNation = 2 then 1 else 0 end as \"NationalityStatus_IsTwoNation\",
				pjob.Org_id as \"Org_id\",
				pjob.OrgUnion_id as \"OrgUnion_id\",
				pjob.Post_id as \"Post_id\",
				to_char(doc.Document_begDate::timestamp, '{$callObject->dateTimeForm104}') as \"Document_begDate\",
				PDEP.DeputyKind_id as \"DeputyKind_id\",
				PDEP.Person_pid as \"DeputyPerson_id\",
				case when PDEPSTATE.Person_id is not null THEN PDEPSTATE.Person_SurName || ' ' || PDEPSTATE.Person_FirName || ' ' || COALESCE(PDEPSTATE.Person_SecName, '') ELSE '' END as \"DeputyPerson_Fio\",
				ResidPlace_id as \"ResidPlace_id\",
				PersonChild_id as \"PersonChild_id\",
				PersonChild_IsManyChild as \"PersonChild_IsManyChild\",
				PersonChild_IsBad as \"PersonChild_IsBad\",
				PersonChild_IsYoungMother as \"PersonChild_IsYoungMother\",
				PersonChild_IsIncomplete as \"PersonChild_IsIncomplete\",
				PersonChild_IsInvalid as \"PersonChild_IsInvalid\",
				PersonChild_IsTutor as \"PersonChild_IsTutor\",
				PersonChild_IsMigrant as \"PersonChild_IsMigrant\",
				HealthKind_id as \"HealthKind_id\",
				ph.PersonHeight_IsAbnorm as \"PersonHeight_IsAbnorm\",
				ph.HeightAbnormType_id as \"HeightAbnormType_id\",
				pw.WeightAbnormType_id as \"WeightAbnormType_id\",
				pw.PersonWeight_IsAbnorm as \"PersonWeight_IsAbnorm\",
				PCh.PersonSprTerrDop_id as \"PersonSprTerrDop_id\",
				FeedingType_id as \"FeedingType_id\",
				PersonChild_CountChild as \"PersonChild_CountChild\",
				InvalidKind_id as \"InvalidKind_id\",
				to_char(PersonChild_invDate::timestamp, '{$callObject->dateTimeForm104}') as \"PersonChild_invDate\",
				HealthAbnorm_id as \"HealthAbnorm_id\",
				HealthAbnormVital_id as \"HealthAbnormVital_id\",
				Diag_id as \"Diag_id\",
				null as \"Person_deadDT\",
				null as \"Person_closeDT\",
				case
					when length(vper.PersonPhone_Phone) = 10 then '(' || left(vper.PersonPhone_Phone, 3) || ')-' || substring(vper.PersonPhone_Phone, 4, 3) || '-' ||
						substring(vper.PersonPhone_Phone, 7, 2) || '-' || right(vper.PersonPhone_Phone, 2)
					else ''
				end as \"PersonPhone_Phone\",
				rtrim(per.Person_Comment) as \"Person_Comment\",
				rtrim(vper.PersonInn_Inn) as \"PersonInn_Inn\",
				rtrim(vper.PersonSocCardNum_SocCardNum) as \"PersonSocCardNum_SocCardNum\",
				rtrim(pr.PersonRefuse_IsRefuse::text) as \"PersonRefuse_IsRefuse\",
				rtrim(pce.PersonCarExist_IsCar::text) as \"PersonCarExist_IsCar\",
				rtrim(pche.PersonChildExist_IsChild::text) as \"PersonChildExist_IsChild\",
				ph.PersonHeight_Height as \"PersonHeight_Height\",
				COALESCE(pw.Okei_id, 37) as \"Okei_id\",
				pw.PersonWeight_Weight as \"PersonWeight_Weight\",
				pi.Ethnos_id as \"Ethnos_id\",
				mop.OnkoOccupationClass_id as \"OnkoOccupationClass_id\",
				per.BDZ_id as \"BDZ_id\",
				per.BDZ_Guid as \"BDZ_Guid\",
				pol.Polis_Guid as \"Polis_Guid\",
				IsUnknown.YesNo_Code as \"Person_IsUnknown\",
				IsAnonym.YesNo_Code as \"Person_IsAnonym\",
				COALESCE(per.Person_IsNotINN, 1) as \"Person_IsNotINN\"
			from
				v_Person_all vper
				left join v_Person per  on per.Person_id=vper.Person_id
				left join v_PersonRefuse PR  on PR.Person_id = vper.Person_id and PR.PersonRefuse_Year = date_part('YEAR',dbo.tzGetDate())
				left join v_Address uaddr  on vper.UAddress_id = uaddr.Address_id
				left join v_Address paddr  on vper.PAddress_id = paddr.Address_id
				left join PersonBirthPlace pbp  on vper.Person_id = pbp.Person_id
				left join v_Address baddr  on pbp.Address_id = baddr.Address_id
				left join Polis pol  on pol.Polis_id=vper.Polis_id
				left join Document doc  on doc.Document_id=vper.Document_id
				left join NationalityStatus ns  on ns.NationalityStatus_id = vper.NationalityStatus_id
				left join PersonInfo pi  on pi.Person_id = vper.Person_id
				left join Job pjob  on vper.Job_id = pjob.Job_id
				left join PersonDeputy PDEP  on PDEP.Person_id = vper.Person_id
				left join v_PersonState PDEPSTATE  on PDEPSTATE.Person_id = PDEP.Person_pid
				left join PersonChild PCh  on PCh.Person_id = vper.Person_id
				left join v_YesNo IsUnknown  on IsUnknown.YesNo_id = COALESCE(per.Person_IsUnknown,1)
				left join v_YesNo IsAnonym  on IsAnonym.YesNo_id = COALESCE(per.Person_IsAnonym,1)
				left join lateral (
					select OnkoOccupationClass_id
					from v_MorbusOnkoPerson 
					where Person_id = :Person_id
					order by MorbusOnkoPerson_insDT desc
	                limit 1
				) as mop ON true
				left join lateral (
					select PersonCarExist_IsCar
					from PersonCarExist 
					where Person_id = :Person_id
					order by PersonCarExist_setDT desc
	                limit 1
				) as pce ON true
				left join lateral (
					select PersonChildExist_IsChild
					from PersonChildExist 
					where Person_id = :Person_id
					order by PersonChildExist_setDT desc
	                limit 1
				) as pche ON true
				left join lateral (
					select 
						PersonHeight_Height,
						PersonHeight_IsAbnorm,
						HeightAbnormType_id
					from PersonHeight 
					where Person_id = :Person_id
					order by PersonHeight_setDT desc
	                limit 1
				) as ph ON true
				left join lateral (
					select
						PersonWeight_Weight,
						WeightAbnormType_id,
						PersonWeight_IsAbnorm,
						Okei_id
					from PersonWeight 
					where Person_id = :Person_id
					order by PersonWeight_setDT desc
	                limit 1
				) as pw ON true
				left join lateral (
					select pp.Person_id
					from
						v_PersonPrivilege pp 
						inner join v_PrivilegeType pt  on pt.PrivilegeType_id = pp.PrivilegeType_id
					where pt.ReceptFinance_id = 1
					  and pp.PersonPrivilege_begDate <= dbo.tzGetDate()
					  and (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= dbo.tzGetDate()::date)
					  and pp.Person_id = vper.Person_id
	                limit 1
				) as PersonPrivilegeFed ON true
			where vper.Person_id= :Person_id
			  and vper.PersonEvn_id= :PersonEvn_id
			limit 1
		";
		$sqlParams = [
			"Person_id" => $data["person_id"],
			"PersonEvn_id" => $data["PersonEvn_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$return = $result->result_array();
		if (empty($return[0])) {
			throw new Exception("Не удалось найти периодику по указанному случаю, возможно она была изменена. Попробуйте обновить список случаев реестра.");
		}
		if ($return[0]["Server_pid"] == 3) {
			// если порожден электронной регистратурой, то отправляем сразу его с открытым на редактирование
			$return[0]["Servers_ids"] = "[3]";
			return $return;
		}
		$sql = "
			select distinct Server_id as \"Server_id\"
			FROM v_Person_all 
			where Person_id = :Person_id
			union all
			select case when exists(
				select personprivilege_id 
				from
					personprivilege reg 
					left join PrivilegeType pt on pt.PrivilegeType_id = reg.PrivilegeType_id
				where reg.person_id = :Person_id
				  and pt.ReceptFinance_id = 1
				  and reg.personprivilege_begdate <= dbo.tzGetDate()
				  and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= dbo.tzGetDate())
			) then 1 end as \"Server_id\"
		";
		$sqlParams = ["Person_id" => $data["person_id"]];
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$servers = $result->result_array();
		$servers_arr = [];
		$sys_server_flag = false;
		foreach ($servers as $row) {
			if ($row["Server_id"] != "") {
				if ($return[0]["Server_pid"] > 0 && $row["Server_id"] == 0) {
					continue;
				}
				$servers_arr[] = $row["Server_id"];
				if ($row["Server_id"] == 1 || $row["Server_id"] == 0) {
					$sys_server_flag = true;
				}
			}
		}
		if ($sys_server_flag === true) {
			$servers_new_arr = [];
			foreach ($servers_arr as $row) {
				if ($return[0]["Server_pid"] > 0 && $row == 0) {
					continue;
				}
				if ($row == 1 || $row == 0) {
					$servers_new_arr[] = $row;
				}
			}
			$servers_arr = $servers_new_arr;
		}
		// если суперадмин, то отсылаем его для предоставления возможности редактирования недоступных полей
		$return[0]["Servers_ids"] = (preg_match("/SuperAdmin/u", $data["session"]["groups"]))
			? "['SuperAdmin']"
			: "[" . implode(", ", $servers_arr) . "]";
		return $return;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function getPersonEvnIdByEvnId(Person_model $callObject, $data)
	{
		$sql = "
			select
				PersonEvn_id as \"PersonEvn_id\",
				Server_id  as \"Server_id\"
			from v_Evn
			where Evn_id = :Evn_id
		";
		$params = ["Evn_id" => $data["Evn_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return (count($result) > 0) ? $result : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonEditWindow(Person_model $callObject, $data)
	{
		$snils_sepatator = ($data["session"]["region"]["nick"] == "astra") ? "' '" : "'-'";
		$sql = "
			select
				vper.Person_SurName as \"Person_SurName\",
				vper.Person_SecName as \"Person_SecName\",
				vper.Person_FirName as \"Person_FirName\",
				vper.PersonState_IsSnils as \"PersonState_IsSnils\",
				case when PersonPrivilegeFed.Person_id is not null then 1 else 0 end as \"Person_IsFedLgot\",
				vper.Server_pid as \"Server_pid\",
				vper.Person_IsInErz as \"Person_IsInErz\",
				vper.PersonIdentState_id as \"PersonIdentState_id\",
				vper.Person_id as \"Person_id\",
				to_char(vper.Person_BirthDay::timestamp, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				vper.Sex_id as \"PersonSex_id\",
				case
					when length(vper.Person_Snils) = 11 then left(vper.Person_Snils, 3) || '-' || substring(vper.Person_Snils, 4, 3) || '-' || 
						substring(vper.Person_Snils, 7, 3) 
                        || {$snils_sepatator} 
                        || right(vper.Person_Snils, 2)
					else vper.Person_Snils
				end as \"Person_SNILS\",
				vper.SocStatus_id as \"SocStatus_id\",
				vper.FamilyStatus_id as \"FamilyStatus_id\",
				vper.PersonFamilyStatus_IsMarried as \"PersonFamilyStatus_IsMarried\",
				vper.Person_edNum as \"Federal_Num\",
				vper.UAddress_id as \"UAddress_id\",
				vper.PersonEduLevel_id as \"PersonEduLevel_id\",
				vper.EducationLevel_id as \"EducationLevel_id\",
				vper.PersonEmployment_id as \"PersonEmployment_id\",
				vper.Employment_id as \"Employment_id\",
				uaddr.PersonSprTerrDop_id as \"UPersonSprTerrDop_id\",
				uaddr.Address_Zip as \"UAddress_Zip\",
				uaddr.KLCountry_id as \"UKLCountry_id\",
				uaddr.KLRGN_id as \"UKLRGN_id\",
				uaddr.KLSubRGN_id as \"UKLSubRGN_id\",
				uaddr.KLCity_id as \"UKLCity_id\",
				uaddr.KLTown_id as \"UKLTown_id\",
				uaddr.KLStreet_id as \"UKLStreet_id\",
				uaddr.Address_House as \"UAddress_House\",
				uaddr.Address_Corpus as \"UAddress_Corpus\",
				uaddr.Address_Flat as \"UAddress_Flat\",
				uaddrsp.AddressSpecObject_id as \"UAddressSpecObject_id\",
				uaddrsp.AddressSpecObject_Name as \"UAddressSpecObject_Value\",
				uaddr.Address_Address as \"UAddress_AddressText\",
				uaddr.Address_Address as \"UAddress_Address\",
				baddr.PersonSprTerrDop_id as \"BPersonSprTerrDop_id\",
				baddr.Address_id as \"Address_id\",
				baddr.KLCountry_id as \"BKLCountry_id\",
				baddr.KLRGN_id as \"BKLRGN_id\",
				baddr.KLSubRGN_id as \"BKLSubRGN_id\",
				baddr.KLCity_id as \"BKLCity_id\",
				baddr.KLTown_id as \"BKLTown_id\",
				baddr.KLStreet_id as \"BKLStreet_id\",
				baddr.Address_House as \"BAddress_House\",
				baddr.Address_Corpus as \"BAddress_Corpus\",
				baddr.Address_Flat as \"BAddress_Flat\",
				baddrsp.AddressSpecObject_id as \"BAddressSpecObject_id\",
				baddrsp.AddressSpecObject_Name as \"BAddressSpecObject_Value\",
				baddr.Address_Zip as \"BAddress_Zip\",
				baddr.Address_Address as \"BAddress_AddressText\",
				baddr.Address_Address as \"BAddress_Address\",
				pcc.PolisCloseCause_Code as \"polisCloseCause\",
				vper.PAddress_id as \"PAddress_id\",
				paddr.PersonSprTerrDop_id as \"PPersonSprTerrDop_id\",
				paddr.Address_Zip as \"PAddress_Zip\",
				paddr.KLCountry_id as \"PKLCountry_id\",
				paddr.KLRGN_id as \"PKLRGN_id\",
				paddr.KLSubRGN_id as \"PKLSubRGN_id\",
				paddr.KLCity_id as \"PKLCity_id\",
				paddr.KLTown_id as \"PKLTown_id\",
				paddr.KLStreet_id as \"PKLStreet_id\",
				paddr.Address_House as \"PAddress_House\",
				paddr.Address_Corpus as \"PAddress_Corpus\",
				paddr.Address_Flat as \"PAddress_Flat\",
				paddrsp.AddressSpecObject_id as \"PAddressSpecObject_id\",
				paddrsp.AddressSpecObject_Name as \"PAddressSpecObject_Value\",
				paddr.Address_Address as \"PAddress_AddressText\",
				paddr.Address_Address as \"PAddress_Address\",
				pi.Nationality_id as \"PersonNationality_id\",
				pol.OmsSprTerr_id as \"OMSSprTerr_id\",
				pol.PolisType_id as \"PolisType_id\",
				pol.Polis_Ser as \"Polis_Ser\",
				pol.PolisFormType_id as \"PolisFormType_id\",
				case when pol.PolisType_id = 4 then '' else pol.Polis_Num end as \"Polis_Num\",
				pol.OrgSmo_id as \"OrgSMO_id\",
				to_char(pol.Polis_begDate::timestamp, '{$callObject->dateTimeForm104}') as \"Polis_begDate\",
				to_char(pol.Polis_endDate::timestamp, '{$callObject->dateTimeForm104}') as \"Polis_endDate\",
				doc.DocumentType_id as \"DocumentType_id\",
				doc.Document_Ser as \"Document_Ser\",
				doc.Document_Num as \"Document_Num\",
				doc.OrgDep_id as \"OrgDep_id\",
				ns.KLCountry_id as \"KLCountry_id\",
				ns.LegalStatusVZN_id as \"LegalStatusVZN_id\",
				case when ns.NationalityStatus_IsTwoNation = 2 then 1 else 0 end as \"NationalityStatus_IsTwoNation\",
				pjob.Org_id as \"Org_id\",
				pjob.OrgUnion_id as \"OrgUnion_id\",
				pjob.Post_id as \"Post_id\",
				to_char(doc.Document_begDate::timestamp, '{$callObject->dateTimeForm104}') as \"Document_begDate\",
				PDEP.DeputyKind_id as \"DeputyKind_id\",
				PDEP.Person_pid as \"DeputyPerson_id\",
				DDEP.DocumentAuthority_id as \"DocumentAuthority_id\",
				DDEP.DocumentDeputy_Ser as \"DocumentDeputy_Ser\",
				DDEP.DocumentDeputy_Num as \"DocumentDeputy_Num\",
				DDEP.DocumentDeputy_Issue as \"DocumentDeputy_Issue\",
				to_char(DDEP.DocumentDeputy_begDate::timestamp, '{$callObject->dateTimeForm104}') as \"DocumentDeputy_begDate\",
				case when PDEPSTATE.Person_id is not null THEN PDEPSTATE.Person_SurName || ' ' || PDEPSTATE.Person_FirName || ' ' || COALESCE(PDEPSTATE.Person_SecName, '') ELSE '' END as \"DeputyPerson_Fio\",
				ResidPlace_id as \"ResidPlace_id\",
				PersonChild_id as \"PersonChild_id\",
				PersonChild_IsManyChild as \"PersonChild_IsManyChild\",
				PersonChild_IsBad as \"PersonChild_IsBad\",
				PersonChild_IsYoungMother as \"PersonChild_IsYoungMother\",
				PersonChild_IsIncomplete as \"PersonChild_IsIncomplete\",
				PersonChild_IsInvalid as \"PersonChild_IsInvalid\",
				PersonChild_IsTutor as \"PersonChild_IsTutor\",
				PersonChild_IsMigrant as \"PersonChild_IsMigrant\",
				HealthKind_id as \"HealthKind_id\",
				ph.PersonHeight_IsAbnorm as \"PersonHeight_IsAbnorm\",
				ph.HeightAbnormType_id as \"HeightAbnormType_id\",
				pw.WeightAbnormType_id as \"WeightAbnormType_id\",
				pw.PersonWeight_IsAbnorm as \"PersonWeight_IsAbnorm\",
				PCh.PersonSprTerrDop_id as \"PersonSprTerrDop_id\",
				FeedingType_id as \"FeedingType_id\",
				PersonChild_CountChild as \"PersonChild_CountChild\",
				InvalidKind_id as \"InvalidKind_id\",
				to_char(PersonChild_invDate::timestamp, '{$callObject->dateTimeForm104}') as \"PersonChild_invDate\",
				HealthAbnorm_id as \"HealthAbnorm_id\",
				HealthAbnormVital_id as \"HealthAbnormVital_id\",
				Diag_id as \"Diag_id\",
				to_char(vper.Person_deadDT::timestamp, '{$callObject->dateTimeForm104}') as \"Person_deadDT\",
				to_char(vper.Person_closeDT::timestamp, '{$callObject->dateTimeForm104}') as \"Person_closeDT\",
				case
					when length(vper.Person_Phone) = 10 then '(' || left(vper.Person_Phone, 3) || ')-' || substring(vper.Person_Phone, 4, 3) || '-' || 
						substring(vper.Person_Phone, 7, 2) || '-' || right(vper.Person_Phone, 2)
					else ''
				end as \"PersonPhone_Phone\",
				case
					when COALESCE(PPH.PersonPhoneStatus_id, 1) = 3 and length(PPH.PersonPhone_Phone) = 10
					then '(' || left(PPH.PersonPhone_Phone, 3) || ')-' || substring(PPH.PersonPhone_Phone, 4, 3) || '-' || 
						substring(PPH.PersonPhone_Phone, 7, 2) || '-' || right(PPH.PersonPhone_Phone, 2)
					else ''
				end as \"PersonPhone_VerifiedPhone\",
				rtrim(per.Person_Comment) as \"Person_Comment\",
				rtrim(pi.PersonInfo_InternetPhone) as \"PersonInfo_InternetPhone\",
				rtrim(vper.Person_Inn) as \"PersonInn_Inn\",
				rtrim(vper.Person_SocCardNum) as \"PersonSocCardNum_SocCardNum\",
				rtrim(Ref.PersonRefuse_IsRefuse::text) as \"PersonRefuse_IsRefuse\",
				rtrim(pce.PersonCarExist_IsCar::text) as \"PersonCarExist_IsCar\",
				rtrim(pche.PersonChildExist_IsChild::text) as \"PersonChildExist_IsChild\",
				ph.PersonHeight_Height as \"PersonHeight_Height\",
				COALESCE(pw.Okei_id, 37) as \"Okei_id\",
				pw.PersonWeight_Weight as \"PersonWeight_Weight\",
				CASE WHEN vper.Server_pid = 0 and pol.Polis_endDate is not null and pol.Polis_endDate < dbo.tzGetDate() THEN 1 ELSE 0 END as \"Polis_CanAdded\",
				pi.Ethnos_id as \"Ethnos_id\",
				mop.OnkoOccupationClass_id as \"OnkoOccupationClass_id\",
				per.BDZ_id as \"BDZ_id\",
				per.BDZ_Guid as \"BDZ_Guid\",
				pol.Polis_Guid as \"Polis_Guid\",
				IsUnknown.YesNo_Code as \"Person_IsUnknown\",
				IsAnonym.YesNo_Code as \"Person_IsAnonym\",
				COALESCE(per.Person_IsNotINN, 1) as \"Person_IsNotINN\",
				case when PCitySocr.KLSocr_Nick in ('Г', 'ПГТ') then 1 else 0 end as \"CitizenType\"
			from
				v_PersonState vper 
				left join v_Person per  on per.Person_id=vper.Person_id
				left join v_Address uaddr  on vper.UAddress_id = uaddr.Address_id
				left join v_AddressSpecObject uaddrsp  on uaddr.AddressSpecObject_id = uaddrsp.AddressSpecObject_id
				left join v_Address paddr  on vper.PAddress_id = paddr.Address_id
				left join v_AddressSpecObject paddrsp  on paddr.AddressSpecObject_id = paddrsp.AddressSpecObject_id
				left join v_KLRgn PRgn  on PRgn.KLRgn_id = paddr.KLRgn_id
				left join v_KLCity PCity  on PCity.KLCity_id = paddr.KLCity_id
				left join v_KLTown PTown  on PTown.KLTown_id = paddr.KLTown_id
				left join v_KLSocr PCitySocr  on PCitySocr.KLSocr_id = coalesce(PTown.KLSocr_id, PCity.KLSocr_id, PRgn.KLSocr_id)
				left join PersonBirthPlace pbp  on vper.Person_id = pbp.Person_id
				left join v_Address baddr  on pbp.Address_id = baddr.Address_id
				left join v_AddressSpecObject baddrsp  on baddr.AddressSpecObject_id = baddrsp.AddressSpecObject_id
				left join Polis pol  on pol.Polis_id=vper.Polis_id
				left join v_PolisCloseCause pcc  on pol.PolisCloseCause_id = pcc.PolisCloseCause_id
				left join Document doc  on doc.Document_id=vper.Document_id
				left join NationalityStatus ns  on ns.NationalityStatus_id = vper.NationalityStatus_id
				left join PersonInfo pi  on pi.Person_id = vper.Person_id
				left join Job pjob  on vper.Job_id = pjob.Job_id
				left join PersonDeputy PDEP  on PDEP.Person_id = vper.Person_id
				left join DocumentDeputy DDEP on DDEP.DocumentDeputy_id = PDEP.DocumentDeputy_id
				left join v_PersonState PDEPSTATE  on PDEPSTATE.Person_id = PDEP.Person_pid
				left join PersonChild PCh  on PCh.Person_id = vper.Person_id
				left join v_YesNo IsUnknown  on IsUnknown.YesNo_id = COALESCE(per.Person_IsUnknown,1)
				left join v_YesNo IsAnonym  on IsAnonym.YesNo_id = COALESCE(per.Person_IsAnonym,1)
				left join lateral (
					select pp.Person_id
					from
						v_PersonPrivilege pp 
						inner join v_PrivilegeType pt  on pt.PrivilegeType_id = pp.PrivilegeType_id
					where pt.ReceptFinance_id = 1
					  and pp.PersonPrivilege_begDate <= dbo.tzGetDate()
					  and (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= dbo.tzGetDate()::date)
					  and pp.Person_id = vper.Person_id
	                limit 1
				) as PersonPrivilegeFed ON true
				left join lateral (
					select OnkoOccupationClass_id
					from v_MorbusOnkoPerson 
					where Person_id = :Person_id
					order by MorbusOnkoPerson_insDT desc
	                limit 1
				) as mop ON true
				left join lateral (
					select PersonRefuse_IsRefuse
					from v_PersonRefuse 
					where Person_id = :Person_id
					  and PersonRefuse_Year = date_part('year', dbo.tzGetDate())
					order by PersonRefuse_insDT desc
	                limit 1
				) as Ref ON true
				left join lateral (
					select PersonCarExist_IsCar
					from PersonCarExist 
					where Person_id = :Person_id
					order by PersonCarExist_setDT desc
	                limit 1
				) as pce ON true
				left join lateral (
					select PersonChildExist_IsChild
					from PersonChildExist 
					where Person_id = :Person_id
					order by PersonChildExist_setDT desc
	                limit 1
				) as pche ON true
				left join lateral (
					select 
						PersonHeight_Height,
						PersonHeight_IsAbnorm,
						HeightAbnormType_id
					from PersonHeight 
					where Person_id = :Person_id
					order by PersonHeight_setDT desc
	                limit 1
				) as ph ON true
				left join lateral (
					select 
						PersonWeight_Weight,
						WeightAbnormType_id,
						PersonWeight_IsAbnorm,
						Okei_id
					from PersonWeight 
					where Person_id = :Person_id
					order by PersonWeight_setDT desc
	                limit 1
				) as pw ON true
				left join lateral (
					select 
						PP.PersonPhone_Phone,
						PPH.PersonPhoneStatus_id
					from
						v_PersonPhoneHist PPH 
						left join v_PersonPhone PP  on PP.PersonPhone_id = PPH.PersonPhone_id
					where PPH.Person_id = vper.Person_id
					order by PPH.PersonPhoneHist_insDT desc
	                limit 1
				) as PPH ON true
			where vper.Person_id= :Person_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$sqlParams = ["Person_id" => $data["person_id"]];
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$return = $result->result_array();
		if (count($return) == 0) {
			return false;
		}
		if ($return[0]["Server_pid"] == 3) {
			// если порожден электронной регистратурой, то отправляем сразу его с открытым на редактирование
			$return[0]["Servers_ids"] = "[3]";
			return $return;
		}
		$sql = "
			select distinct Server_id as \"Server_id\"
			from v_Person_all 
			where Person_id = :Person_id
			union all
			select case when exists(
				select PersonPrivilege_id
				from
					v_PersonPrivilege reg 
					inner join PrivilegeType pt on pt.PrivilegeType_id = reg.PrivilegeType_id
				where reg.person_id = :Person_id
				  and pt.ReceptFinance_id = 1
				  and reg.personprivilege_begdate <= dbo.tzGetDate()
				  and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= dbo.tzGetDate())
			) then 1 end
		";
		$sqlParams = ["Person_id" => $data["person_id"]];
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$servers = $result->result_array();
		$servers_arr = [];
		$sys_server_flag = false;
		foreach ($servers as $row) {
			if ($row["Server_id"] != "") {
				if ($return[0]["Server_pid"] > 0 && $row["Server_id"] == 0) {
					continue;
				}
				$servers_arr[] = $row["Server_id"];
				if ($row["Server_id"] == 1 || $row["Server_id"] == 0) {
					$sys_server_flag = true;
				}
			}
		}
		if ($sys_server_flag === true) {
			$servers_new_arr = [];
			foreach ($servers_arr as $value) {
				if ($return[0]["Server_pid"] > 0 && $value == 0) {
					continue;
				}
				if ($value == 1 || $value == 0) {
					$servers_new_arr[] = $value;
				}
			}
			$servers_arr = $servers_new_arr;
		}
		$return[0]["Servers_ids"] = (preg_match("/SuperAdmin/u", $data["session"]["groups"]))
			? "['SuperAdmin']"
			: "[" . implode(", ", $servers_arr) . "]";
		return $return;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonDoublesByFIODR(Person_model $callObject, $data)
	{
		$query = "
			select 
				PS.Person_id as \"Person_id\",
				P.Polis_Ser as \"Polis_Ser\",
				P.Polis_Num as \"Polis_Num\"
			from
				v_PersonState PS 
				inner join Polis P  on P.Polis_id = PS.Polis_id
			where PS.Person_id <> :Person_id
			  and PS.Server_pid = 0
			  and PS.Person_BirthDay = cast(:Person_Birthday as date)
			  and PS.Person_FirName = :Person_Firname
			  and PS.Person_SecName = :Person_Secname
			  and PS.Person_SurName = :Person_Surname
			  and (P.Polis_begDate is null or P.Polis_begDate <= cast(:Date as date))
			  and (P.Polis_endDate is null or P.Polis_endDate > cast(:Date as date))
			limit 1
		";
		$queryParams = [
			"Date" => $data["Date"],
			"Person_id" => $data["Person_id"],
			"Person_Birthday" => $data["Person_Birthday"],
			"Person_Firname" => $data["Person_Firname"],
			"Person_Secname" => $data["Person_Secname"],
			"Person_Surname" => $data["Person_Surname"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonPolisInfo(Person_model $callObject, $data)
	{
		$queryParams = ["Person_id" => $data["Person_id"]];
		$query = "
			select
                (coalesce(PS.Person_SurName, '')||' '||coalesce(PS.Person_FirName, '')||' '||coalesce(PS.Person_SecName, '')) as \"Polis_FIO\",
                SMO.OrgSMO_Name as \"PolisOrgSmo\",
				case when Polis.PolisType_id = 4 and coalesce(PS.Person_EdNum, '') != ''
				    then PS.Person_EdNum
					else trim(coalesce(Polis.Polis_Ser, '')||' '||coalesce(Polis.Polis_Num, ''))
				end as \"PolisSerNum\",
                coalesce(to_char(Polis.Polis_begDate::timestamp, '{$callObject->dateTimeForm104}'), 'Действует') as \"Polis_begDate\",
                coalesce(to_char(Polis.Polis_endDate::timestamp, '{$callObject->dateTimeForm104}'),'Действует') as \"Polis_endDate\"
            from
                v_PersonState_All PS 
                left join v_Polis Polis on Polis.Polis_id = PS.Polis_id
                left join v_OrgSMO SMO on SMO.OrgSmo_id = Polis.OrgSmo_id
            where PS.Person_id = :Person_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonPhoneInfo(Person_model $callObject, $data)
	{
		$queryParams = ["Person_id" => $data["Person_id"]];
		$query = "
            select
                PS.Person_id as \"Person_id\",
                PS.Server_pid as \"Server_id\",
                PS2.PersonPhone_id as \"PersonPhone_id\",
				case when length(rtrim(PS.Person_Phone)) = 10
				    then '(' || left(rtrim(PS.Person_Phone), 3) || ')-' || substring(rtrim(PS.Person_Phone), 4, 3) || '-' || substring(rtrim(PS.Person_Phone), 7, 2) || '-' || right(rtrim(PS.Person_Phone), 2)
					else ''
				end as \"Phone_Promed\",
                rtrim(PIF.PersonInfo_InternetPhone) as \"Phone_Site\",
                (coalesce(PS.Person_SurName, '')||' '||coalesce(PS.Person_FirName, '')||' '||coalesce(PS.Person_SecName, '')) as \"Person_FIO\"
            from
                v_PersonState PS
                left join v_PersonInfo PIf on PIf.Person_id = PS.Person_id
                left join PersonState PS2 on PS2.Person_id = PS.Person_id
            where PS.Person_id = :Person_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonJobInfo(Person_model $callObject, $data)
	{
		$select = "";
		$join = "";
		$queryParams = ["Person_id" => $data["Person_id"]];
		if (!empty($data["fromMobile"])) {
			$select = ",
				o.Org_Name as \"Org_Name\",
				o.Org_StickNick as \"Org_StickNick\",
				o.Org_Nick as \"Org_Nick\"
			";
			$join = " left join v_Org o on o.Org_id = pjob.Org_id ";
		}
		$selectString = "
			pjob.Org_id as \"Org_id\",
			pjob.OrgUnion_id as \"OrgUnion_id\",
			pjob.Post_id as \"Post_id\",
			p.Post_Name as \"Post_Name\"
			{$select}
		";
		$fromString = "
			v_PersonState vper 
			left join Job pjob  on vper.Job_id = pjob.Job_id
			left join v_Post p  on p.Post_id = pjob.Post_id
			{$join}
		";
		$whereString = "vper.Person_id= :Person_id";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonByUecData(Person_model $callObject, $data)
	{
		$query = "
			select 
				PS.Person_id as \"Person_id\", 
				PS.Server_id as \"Server_id\", 
				PS.PersonEvn_id as \"PersonEvn_id\"
			from v_PersonState PS 
			where PS.Person_BirthDay = :Person_BirthDay
			  and PS.Person_FirName = :Person_FirName
			  and PS.Person_SecName = :Person_SecName
			  and PS.Person_SurName = :Person_SurName
			  and PS.Person_EdNum = :Polis_Num
			order by case when ps.Server_pid = 0 THEN 1 ELSE 0 END desc
			limit 1
		";
		$queryParams = [
			"Person_BirthDay" => $data["Person_BirthDay"],
			"Person_FirName" => $data["Person_FirName"],
			"Person_SecName" => $data["Person_SecName"],
			"Person_SurName" => $data["Person_SurName"],
			"Polis_Num" => $data["Polis_Num"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		if (count($resp) > 0) {
			return [$resp[0]];
		}
		// если не нашли ищем только по фио и ДР среди тех у кого не указан ЕНП
		$query = "
			select
				PS.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				PS.PersonEvn_id as \"PersonEvn_id\"
			from v_PersonState PS
			where PS.Person_BirthDay = :Person_BirthDay
			  and PS.Person_FirName = :Person_FirName
			  and PS.Person_SecName = :Person_SecName
			  and PS.Person_SurName = :Person_SurName
			  and PS.Person_EdNum IS NULL
			order by case when ps.Server_pid = 0 THEN 1 ELSE 0 END desc
			limit 1
		";
		$queryParams = [
			"Person_BirthDay" => $data["Person_BirthDay"],
			"Person_FirName" => $data["Person_FirName"],
			"Person_SecName" => $data["Person_SecName"],
			"Person_SurName" => $data["Person_SurName"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		return (count($resp) > 0) ? [$resp[0]] : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonByBarcodeData(Person_model $callObject, $data)
	{
		$filterList = [];
		$queryParams = $data;
		$query_oms = "
			select 
				PS.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				1 as \"resultType\"
			from v_PersonState PS 
			where PS.Polis_Num = :Polis_Num
			limit 1
		";
		/**
		 * @var CI_DB_result $result_oms
		 * @var CI_DB_result $result_person
		 */
		$result_oms = $callObject->db->query($query_oms, $queryParams);
		$fields = ["Person_Surname", "Person_Firname", "Person_Secname", "Person_Birthday", "Polis_Num"];
		foreach ($fields as $field) {
			if (!empty($data[$field])) {
				$filterList[] = "PS.{$field} = :{$field}";
			}
		}
		$whereString = implode(" and ", $filterList);
		$query_person = "
			select 
				PS.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				2 as \"resultType\"
			from v_PersonState PS 
			where {$whereString}
			limit 1
		";
		$result_person = $callObject->db->query($query_person, $queryParams);
		//Теперь проверяем, что из этого получилось
		if (is_object($result_oms) && count($result_oms->result_array()) == 1) {
			//Если нашли по коду ОМС, то возвращаем с resultType=1
			$response_oms = $result_oms->result_array();
			return $response_oms;
		} else if (is_object($result_person) && count($result_person->result_array()) == 1) {
			//Если нашли по ФИО и ДР, то возвращаем с resultType=2
			$response_person = $result_person->result_array();
			return $response_person;
		} else {
			return false;
		}
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonAddress(Person_model $callObject, $data)
	{
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select
				URegion.KLRgn_Name as \"UKLRgn_Name\",
				UCity.KLCity_Name as \"UKLCity_Name\",
				UPSTD.PersonSprTerrDop_Name as \"UPersonSprTerrDop_Name\",
				UStreet.KLStreet_Name as \"UKLStreet_Name\",
				UAddr.Address_House as \"UAddress_House\",
				UAddr.Address_Corpus as \"UAddress_Corpus\",
				UAddr.Address_Flat as \"UAddress_Flat\"
			from
				v_PersonState PS
				left join v_Address UAddr on UAddr.Address_id = PS.UAddress_id
				left join v_KLRgn URegion on URegion.KLRgn_id = UAddr.KLRgn_id
				left join v_KLCity UCity on UCity.KLCity_id = UAddr.KLCity_id
				left join v_KLStreet UStreet on UStreet.KLStreet_id = UAddr.KLStreet_id
				left join v_PersonInfo PInfo on PInfo.Person_id = PS.Person_id
				left join v_PersonSprTerrDop UPSTD on UPSTD.PersonSprTerrDop_Code = PInfo.UPersonSprTerrDop_id
			where PS.Person_id = :Person_id
		";
		return $callObject->getFirstRowFromQuery($query, $params);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return bool|float|int|string
	 */
	public static function getPersonPolisRegionId(Person_model $callObject, $data)
	{
		$params = [
			"PersonEvn_id" => $data["PersonEvn_id"],
			"Server_id" => $data["Server_id"],
		];
		$query = "
			select Terr.KLRgn_id as \"KLRgn_id\"
			from
				v_Person_all PS 
				left join v_Polis P  on P.Polis_id = PS.Polis_id
				left join OMSSprTerr Terr  on Terr.OMSSprTerr_id = P.OmsSprTerr_id
			where PS.PersonEvn_id = :PersonEvn_id
			  and PS.Server_id = :Server_id
			limit 1
		";
		return $callObject->getFirstResultFromQuery($query, $params);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function getPersonAnonymCodeExt(Person_model $callObject, $data)
	{
		$query = "
			select 
				Lpu_f003mcod as \"Lpu_f003mcod\",
			    right(to_char(dbo.tzGetDate(), 'MMDDYYYY'), 2) as \"curYear\"
			from v_Lpu 
			where Lpu_id = :Lpu_id
			limit 1
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $qmcod */
		$qmcod = $callObject->db->query($query, $queryParams);
		if (!is_object($qmcod)) {
			throw new Exception("Ошибка поиска федерального реестрого кода МО");
		}
		$response = $qmcod->result_array();
		if (!isset($response[0])) {
			throw new Exception("Не найден федеральный реестровый код МО");
		}
		$f003mcod = $response[0]["Lpu_f003mcod"];
		$curYear = $response[0]["curYear"];
		unset($response, $qmcod);
		$f003mcod = substr(str_pad($f003mcod, 4, "0", STR_PAD_LEFT), -4);
		$query = "
			SELECT PS.Person_SurName as \"Person_SurName\"
			FROM v_Person P 
			INNER JOIN v_PersonState PS  ON PS.Person_id = P.Person_id
			WHERE P.Person_IsAnonym = 2
			  AND LEFT(PS.Person_SurName, 6) = :Code
		";
		$result = $callObject->db->query($query, ["Code" => $f003mcod . $curYear]);
		if ($result === false) {
			throw new Exception("Ошибка получения порядкого номера анонимного пациента в МО");
		}
		$codeArr = [];
		$maxCode = 0;
		foreach ($result->result() as $row) {
			$start = mb_strlen($row->Person_SurName) - 5;
			if ($start != 6) continue;// длина ключа не равна 11 или 9 - не правильный код анонимного пациента
			$code = (int)substr($row->Person_SurName, $start);
			if ($maxCode < $code) {
				$maxCode = $code;
			}
			$codeArr[$code] = true;
		}
		$result->free_result();
		unset($result);
		$maxCode++;
		$missed = 0;
		if ($maxCode > 99999) {
			for ($i = 1; $i <= 99999; $i++) {
				if (!array_key_exists($i, $codeArr)) {
					$missed = $i;
					break;
				}
			}
			if (!$missed) {
				throw new Exception("Все номера от 1 до 99999 на текущий год заняты");
			}
			$maxCode = $missed;
		}
		unset($codeArr);
		return [[
			"success" => true,
			"Person_AnonymCode" => $f003mcod . $curYear . str_pad("" . $maxCode, 5, "0", STR_PAD_LEFT),
			"Person_IsAnonym" => true,
			"Error_Msg" => ""
		]];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function getPersonAnonymCode(Person_model $callObject, $data)
	{
		$query = "
			select Lpu_f003mcod as \"Lpu_f003mcod\"
			from v_Lpu
			where Lpu_id = :Lpu_id
			limit 1
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		$Lpu_f003mcod = $callObject->getFirstResultFromQuery($query, $queryParams);
		if ($Lpu_f003mcod === false) {
			throw new Exception("Ошибка поиска федерального реестрого кода МО");
		}
		if (empty($Lpu_f003mcod)) {
			throw new Exception("Не найден федеральный реестровый код МО");
		}
		$params = [
			"Lpu_f003mcod" => $Lpu_f003mcod,
			"Person_id" => !empty($data["Person_id"]) ? $data["Person_id"] : null
		];
		$query = "
			with cte as (
			    select COALESCE((
			        select right(PS.Person_SurName, 5)::int8
			        from
			            v_Person P
			            inner join v_PersonState PS  on PS.Person_id = P.Person_id
			        where P.Person_id = :Person_id
			          and P.Person_IsAnonym = 2
			          and dbo.isnumeric(PS.Person_Surname||'e0') = 1
			        limit 1
			    ), 0) AS number1,
			    COALESCE((
			        select max(right(PS.Person_SurName, 5))::int8 + 1
			        from
			            v_Person P
			            inner join v_PersonState PS  on PS.Person_id = P.Person_id
			        where LOWER(PS.Person_SurName) LIKE right('0000'||LOWER(:Lpu_f003mcod), 4)||lpad('[0-9]', 5)
			          and dbo.isnumeric(PS.Person_Surname||'e0') = 1
			          and P.Person_IsAnonym = 2
			        limit 1
			    ), 1) AS number2
			)
			select
			    right(:Lpu_f003mcod::varchar, 4)||
			    right('00000' ||
			    (
			        SELECT
			            CASE WHEN :Person_id IS NOT NULL
			                THEN (SELECT CASE WHEN number1 = 0 THEN number2 ELSE number1 END FROM cte)
			                ELSE (SELECT CASE WHEN number1 = 0 THEN number2 ELSE 0 END FROM cte)
			            END
			    )::varchar(1), 5) as \"Person_AnonymCode\"
    	";
		$Person_AnonymCode = $callObject->getFirstResultFromQuery($query, $params);
		if ($Person_AnonymCode === false) {
			throw new Exception("Ошибка получения порядкого номера анонимного пациента в МО");
		}
		return [[
			"success" => true,
			"Person_AnonymCode" => $Person_AnonymCode,
			"Error_Msg" => ""
		]];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function getPersonAnonymData(Person_model $callObject, $data)
	{
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select 
				P.Person_SurName as \"Person_SurName\",
				P.Person_FirName as \"Person_FirName\",
				P.Person_SecName as \"Person_SecName\"
			from v_Person_all P
			where P.Person_id = :Person_id
			  and dbo.isnumeric(P.Person_SurName||'e0') = 0
			order by P.PersonEvn_insDT desc
			limit 1
		";
		$resp = $callObject->getFirstRowFromQuery($query, $params);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при получении данных анонимного пациента");
		}
		return [[
			"success" => true,
			"PersonAnonymData" => $resp,
			"Error_Msg" => ""
		]];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function getPersonLpuInfoPersData(Person_model $callObject, $data)
	{
		$query = "
			select
				pi.Person_id as \"Person_id\",
				coalesce(to_char(pi.PersonLpuInfo_setDT, '{$callObject->dateTimeForm104}'), '') as \"PersonLpuInfo_setDT\",
				case when coalesce(pi.PersonLpuInfo_IsAgree,2) = 1 then 'отзыва согласия' else 'согласия' end as \"caption\"
			from v_PersonLpuInfo pi 
			where pi.Person_id = :Person_id
			  and pi.Lpu_id = :Lpu_id
			order by pi.PersonLpuInfo_setDT desc
            limit 1
		";
		$resp = $callObject->queryResult($query, $data);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при получении данных согласия/отзыва согласия на обработку перс.данных");
		}
		return (count($resp) > 0)
			? $resp
			: [["Person_id" => $data["Person_id"], "PersonLpuInfo_setDT" => "", "caption" => "согласия"]];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonSvidInfo(Person_model $callObject, $data)
	{
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select 
				Person_id as \"Person_id\",
				Server_id as \"Server_id\",
				PersonSvid_id as \"PersonSvid_id\",
				PersonSvidInfo_id as \"PersonSvidInfo_id\",
				PersonSvid_IsSigned as \"PersonSvid_IsSigned\",
				PersonSvid_Object as \"PersonSvid_Object\",
				PersonSvidType_Code as \"PersonSvidType_Code\",
				PersonSvidType_Name as \"PersonSvidType_Name\",
				PersonSvid_Ser as \"PersonSvid_Ser\",
				PersonSvid_Num as \"PersonSvid_Num\",
				PersonSvid_GiveDate as \"PersonSvid_GiveDate\"
            from (
	            select 
					BS.Person_id,
					BS.Server_id,
					BS.BirthSvid_id as PersonSvid_id,
					BS.BirthSvid_id as PersonSvidInfo_id,
					BS.BirthSvid_IsSigned::integer as PersonSvid_IsSigned,
					'BirthSvid' as PersonSvid_Object,
					'birth' as PersonSvidType_Code,
					'Свидетельство о рождении' as PersonSvidType_Name,
					coalesce(BS.BirthSvid_Ser,'') as PersonSvid_Ser,
					coalesce(BS.BirthSvid_Num,'') as PersonSvid_Num,
					to_char(BS.BirthSvid_GiveDate, '{$callObject->dateTimeForm104}') as PersonSvid_GiveDate
				from v_BirthSvid BS 
				where BS.Person_id = :Person_id
				  and coalesce(BS.BirthSvid_IsBad, 1) = '1'
				order by 5
				limit 1
            ) t
			
			union
            select 
				Person_id as \"Person_id\",
				Server_id as \"Server_id\",
				PersonSvid_id as \"PersonSvid_id\",
				PersonSvidInfo_id as \"PersonSvidInfo_id\",
				PersonSvid_IsSigned::integer as \"PersonSvid_IsSigned\",
				PersonSvid_Object as \"PersonSvid_Object\",
				PersonSvidType_Code as \"PersonSvidType_Code\",
				PersonSvidType_Name as \"PersonSvidType_Name\",
				PersonSvid_Ser as \"PersonSvid_Ser\",
				PersonSvid_Num as \"PersonSvid_Num\",
				PersonSvid_GiveDate as \"PersonSvid_GiveDate\"
            from (
				select
					PDS.Person_id,
					PDS.Server_id,
					PDS.PntDeathSvid_id as PersonSvid_id,
					PDS.PntDeathSvid_id as PersonSvidInfo_id,
					null as PersonSvid_IsSigned,
					'PntDeathSvid' as PersonSvid_Object,
					'pntdeath' as PersonSvidType_Code,
					'Свидетельство о перинатальной смерти' as PersonSvidType_Name,
					coalesce(PDS.PntDeathSvid_Ser,'') as PersonSvid_Ser,
					coalesce(PDS.PntDeathSvid_Num,'') as PersonSvid_Num,
					to_char(PDS.PntDeathSvid_GiveDate, '{$callObject->dateTimeForm104}') as PersonSvid_GiveDate
				from v_PntDeathSvid PDS 
				where PDS.Person_id = :Person_id
				  and coalesce(PDS.PntDeathSvid_IsBad, 1) = '1'
				order by 5
				limit 1
            ) t
			
			union
            select
				Person_id as \"Person_id\",
				Server_id as \"Server_id\",
				PersonSvid_id as \"PersonSvid_id\",
				PersonSvidInfo_id as \"PersonSvidInfo_id\",
				PersonSvid_IsSigned::integer as \"PersonSvid_IsSigned\",
				PersonSvid_Object as \"PersonSvid_Object\",
				PersonSvidType_Code as \"PersonSvidType_Code\",
				PersonSvidType_Name as \"PersonSvidType_Name\",
				PersonSvid_Ser as \"PersonSvid_Ser\",
				PersonSvid_Num as \"PersonSvid_Num\",
				PersonSvid_GiveDate as \"PersonSvid_GiveDate\"
            from (
				select
					DS.Person_id,
					DS.Server_id,
					DS.DeathSvid_id as PersonSvid_id,
					DS.DeathSvid_id as PersonSvidInfo_id,
					null as PersonSvid_IsSigned,
					'DeathSvid' as PersonSvid_Object,
					'death' as PersonSvidType_Code,
					'Свидетельство о смерти' as PersonSvidType_Name,
					coalesce(DS.DeathSvid_Ser,'') as PersonSvid_Ser,
					coalesce(DS.DeathSvid_Num,'') as PersonSvid_Num,
					to_char(DS.DeathSvid_GiveDate, '{$callObject->dateTimeForm104}') as PersonSvid_GiveDate
					from v_DeathSvid DS 
				where DS.Person_id = :Person_id
				  and coalesce(DS.DeathSvid_IsBad, 1) = '1'
				order by 5
	            limit 1
			) t
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function getPersonEvnAndPolisData(Person_model $callObject, $data)
	{
		$params = ["PersonEvn_id" => $data["PersonEvn_id"]];
		$query = "
			select
				PD.Person_id as \"Person_id\",
				PD.PersonEvn_id as \"PersonEvn_id\",
				pa.Polis_id as \"Polis_id\",
				to_char(pol.Polis_begDate, '{$callObject->dateTimeForm104}') as \"Polis_begDate\",
				to_char(pol.Polis_endDate, '{$callObject->dateTimeForm104}') as \"Polis_endDate\"
			from
				v_PersonEvn PD 
				inner join v_Person_all pa on pa.PersonEvn_id = PD.PersonEvn_id and pa.Person_id = PD.Person_id
				inner join v_Polis pol on pa.Polis_id = pol.Polis_id
			where PD.PersonEvn_id = :PersonEvn_id
			  and PD.PersonEvnClass_id = 8
		";
		$resp = $callObject->queryResult($query, $params);
		//Для Уфы проверка пересечений полисов отключена
		if (is_array($resp) && count($resp) > 0 && getRegionNick() != "ufa") {
			$query = "
				select pa.PersonEvn_id as \"PersonEvn_id\"
				from
					v_Person_all pa
					inner join v_Polis pol on pa.Polis_id = pol.Polis_id
				where pa.Person_id = :Person_id
				  and pa.PersonEvnClass_id = 8
				  and pol.Polis_id <> coalesce(:Polis_id::bigint, 0)
				  and (pol.Polis_begDate < cast(:Polis_endDate as date) or cast(:Polis_endDate as date) is null)
				  and pol.Polis_endDate > cast(:Polis_begDate as date)
			";
			$params = $resp[0];
			if (!empty($params["Polis_begDate"])) {
				$params["Polis_begDate"] = date("Y-m-d", strtotime($params["Polis_begDate"]));
			}
			if (!empty($params["Polis_endDate"])) {
				$params["Polis_endDate"] = date("Y-m-d", strtotime($params["Polis_endDate"]));
			}
			$response = $callObject->queryResult($query, $params);
			if (is_array($response) && count($response) > 0) {
				throw new Exception("Периоды полисов не могут пересекаться!");
			}
		}
		return $resp;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return mixed|array
	 */
	public static function getPersonSignalInfo(Person_model $callObject, $data)
	{
		$query = "
			select
				pmh.PersonMedHistory_id as \"PersonMedHistory_id\",
				pmh.PersonMedHistory_Descr as \"PersonMedHistory_Descr\",
				pmh.PersonMedHistory_Text as \"PersonMedHistory_Text\",
				to_char(pmh.PersonMedHistory_setDT, 'YYYY-MM-DD') as \"PersonMedHistory_setDT\",
				pbg.PersonBloodGroup_id as \"PersonBloodGroup_id\",
				pbg.BloodGroupType_id as \"BloodGroupType_id\",
				bgt.BloodGroupType_Code as \"BloodGroupType_Code\",
				bgt.BloodGroupType_Name as \"BloodGroupType_Name\",
				pbg.RhFactorType_id as \"RhFactorType_id\",
				rft.RhFactorType_Code as \"RhFactorType_Code\",
				rft.RhFactorType_Name as \"RhFactorType_Name\",
				to_char(pbg.PersonBloodGroup_setDT, 'YYYY-MM-DD') as \"PersonBloodGroup_setDT\",
				ph.PersonHeight_id as \"PersonHeight_id\",
				to_char(ph.PersonHeight_setDT, 'YYYY-MM-DD') as \"PersonHeight_setDT\",
				ph.PersonHeight_Height as \"PersonHeight_Height\",
				case when ph.PersonHeight_IsAbnorm = 2 then 1 else 0 end as \"PersonHeight_IsAbnorm\",
				ph.HeightAbnormType_id as \"HeightAbnormType_id\",
				ph.HeightMeasureType_id as \"HeightMeasureType_id\",
				pw.PersonWeight_id as \"PersonWeight_id\",
				to_char(pw.PersonWeight_setDT, 'YYYY-MM-DD') as \"PersonWeight_setDT\",
				pw.PersonWeight_Weight as \"PersonWeight_Weight\",
				case when pw.PersonWeight_IsAbnorm = 2 then 1 else 0 end as \"PersonWeight_IsAbnorm\",
				pw.WeightAbnormType_id as \"WeightAbnormType_id\",
				pw.WeightMeasureType_id as \"WeightMeasureType_id\",
				pw.Okei_id  as \"Okei_id\"
			from
				v_PersonState ps 
				left join lateral (select * from v_PersonMedHistory pmh where pmh.Person_id = ps.Person_id order by pmh.PersonMedHistory_setDT desc limit 1) as pmh on true
				left join lateral (select * from v_PersonBloodGroup pbg where pbg.Person_id = ps.Person_id order by pbg.PersonBloodGroup_setDT desc limit 1) as pbg on true
				left join lateral (select * from v_PersonHeight ph where ph.Person_id = ps.Person_id order by ph.PersonHeight_setDT desc limit 1) as ph on true
				left join lateral (select * from v_PersonWeight pw where pw.Person_id = ps.Person_id order by pw.PersonWeight_setDT desc limit 1) as pw on true
				left join v_BloodGroupType BGT on BGT.BloodGroupType_id = PBG.BloodGroupType_id
				left join v_RhFactorType RFT on RFT.RhFactorType_id = PBG.RhFactorType_id
			where ps.Person_id = :Person_id
            limit 1
		";
		$queryParams = ["Person_id" => $data["Person_id"]];
		$resp = $callObject->queryResult($query, $queryParams);
		if (empty($resp[0])) {
			return false;
		}
		$query = "
			select
				par.PersonAllergicReaction_id as \"PersonAllergicReaction_id\",
				par.AllergicReactionLevel_id as \"AllergicReactionLevel_id\",
				arl.AllergicReactionLevel_Name as \"AllergicReactionLevel_Name\",
				par.AllergicReactionType_id as \"AllergicReactionType_id\",
				art.AllergicReactionType_Name as \"AllergicReactionType_Name\",
				par.DrugMnn_id as \"DrugMnn_id\",
				par.PersonAllergicReaction_Kind as \"PersonAllergicReaction_Kind\",
				to_char(par.PersonAllergicReaction_setDT, 'YYYY-MM-DD') as \"PersonAllergicReaction_setDT\"
			from
				v_PersonAllergicReaction par 
				left join v_AllergicReactionLevel arl on arl.AllergicReactionLevel_id = par.AllergicReactionLevel_id
				left join v_AllergicReactionType art on art.AllergicReactionType_id = par.AllergicReactionType_id
			where par.Person_id = :Person_id
		";
		$resp[0]["PersonAllergicReactionList"] = $callObject->queryResult($query, $queryParams);
		$query = "
			select
				pt.PrivilegeType_id as \"PrivilegeType_id\",
				pt.PrivilegeType_Name as \"PrivilegeType_Name\",
				pt.PrivilegeType_Code as \"PrivilegeType_Code\",
				to_char(pp.PersonPrivilege_begDate, 'YYYY-MM-DD') as \"PersonPrivilege_begDate\",
				to_char(pp.PersonPrivilege_endDate, 'YYYY-MM-DD') as \"PersonPrivilege_endDate\"
			from
				v_PersonPrivilege pp
				inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
			where pp.Person_id = :Person_id
		";
		$resp[0]["PrivilegeTypeList"] = $callObject->queryResult($query, $queryParams);
		$query = "
			select
				pd.Diag_id as \"Diag_id\",
				to_char(pd.PersonDisp_begDate, 'YYYY-MM-DD') as \"PersonDisp_begDate\",
				to_char(pd.PersonDisp_endDate, 'YYYY-MM-DD') as \"PersonDisp_endDate\",
				d.Diag_Code as \"Diag_Code\",
				pd.DispOutType_id as \"DispOutType_id\",
				dot.DispOutType_Name as \"DispOutType_Name\",
				pd.LpuSection_id as \"LpuSection_id\",
				ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				pd.MedPersonal_id as \"MedPersonal_id\"
			from
				v_PersonDisp pd 
				left join v_Diag d  on d.Diag_id = pd.Diag_id
				left join v_DispOutType dot  on dot.DispOutType_id = pd.DispOutType_id
				left join v_LpuSection ls  on ls.LpuSection_id = pd.LpuSection_id
			where pd.Person_id = :Person_id
		";
		$resp[0]["PersonDispList"] = $callObject->queryResult($query, $queryParams);
		$query = "
			select
				epldd13.EvnPLDispDop13_id as \"EvnPLDisp_id\",
				epldd13.DispClass_id as \"DispClass_id\",
				epldd13.HealthKind_id as \"HealthKind_id\",
				epldd13.Lpu_id as \"Lpu_id\",
				to_char(epldd13.EvnPLDispDop13_consDT, 'YYYY-MM-DD') as \"EvnPLDisp_consDT\",
				to_char(epldd13.EvnPLDispDop13_setDT, 'YYYY-MM-DD') as \"EvnPLDisp_setDT\",
				case when epldd13.EvnPLDispDop13_IsEndStage = 2 then 1 else 0 end as \"EvnPLDisp_IsEndStage\"
			from v_EvnPLDispDop13 epldd13
			where epldd13.Person_id = :Person_id
			union all
			select
				epldp.EvnPLDispProf_id as \"EvnPLDisp_id\",
				epldp.DispClass_id as \"DispClass_id\",
				epldp.HealthKind_id as \"HealthKind_id\",
				epldp.Lpu_id as \"Lpu_id\",
				to_char(epldp.EvnPLDispProf_consDT, 'YYYY-MM-DD') as \"EvnPLDisp_consDT\",
				to_char(epldp.EvnPLDispProf_setDT, 'YYYY-MM-DD') as \"EvnPLDisp_setDT\",
				case when epldp.EvnPLDispProf_IsEndStage = 2 then 1 else 0 end as \"EvnPLDisp_IsEndStage\"
			from v_EvnPLDispProf epldp
			where epldp.Person_id = :Person_id
			union all
			select
				epldti.EvnPLDispTeenInspection_id as \"EvnPLDisp_id\",
				epldti.DispClass_id as \"DispClass_id\",
				null as \"HealthKind_id\",
				epldti.Lpu_id as \"Lpu_id\",
				to_char(epldti.EvnPLDispTeenInspection_consDT, 'YYYY-MM-DD') as \"EvnPLDisp_consDT\",
				to_char(epldti.EvnPLDispTeenInspection_setDT, 'YYYY-MM-DD') as \"EvnPLDisp_setDT\",
				case when epldti.EvnPLDispTeenInspection_IsFinish = 2 then 1 else 0 end as \"EvnPLDisp_IsEndStage\"
			from v_EvnPLDispTeenInspection epldti 
			where epldti.Person_id = :Person_id
			union all
			select
				epldo.EvnPLDispOrp_id as \"EvnPLDisp_id\",
				epldo.DispClass_id as \"DispClass_id\",
				null as \"HealthKind_id\",
				epldo.Lpu_id as \"Lpu_id\",
				to_char(epldo.EvnPLDispOrp_consDT, 'YYYY-MM-DD') as \"EvnPLDisp_consDT\",
				to_char(epldo.EvnPLDispOrp_setDT, 'YYYY-MM-DD') as \"EvnPLDisp_setDT\",
				case when epldo.EvnPLDispOrp_IsFinish = 2 then 1 else 0 end as \"EvnPLDisp_IsEndStage\"
			from v_EvnPLDispOrp epldo 
			where epldo.Person_id = :Person_id
		";
		$resp[0]["EvnPLDispList"] = $callObject->queryResult($query, $queryParams);
		if (!empty($resp[0]["EvnPLDispList"])) {
			foreach ($resp[0]["EvnPLDispList"] as $key => $value) {
				$query = "
					select
						eddd.Diag_id as \"Diag_id\",
						d.Diag_Code as \"Diag_Code\"
					from
						v_EvnDiagDopDisp eddd 
						left join v_Diag d  on d.Diag_id = eddd.Diag_id
					where eddd.EvnDiagDopDisp_pid = :EvnPLDisp_id
				";
				$resp[0]["EvnPLDispList"][$key]["EvnPLDispDiagList"] = $callObject->queryResult($query, ["EvnPLDisp_id" => $value["EvnPLDisp_id"]]);
				unset($resp[0]["EvnPLDispList"][$key]["EvnPLDisp_id"]);
			}
		}
		$query = "
			select
				eds.Diag_id as \"Diag_id\",
				d.Diag_Code as \"Diag_Code\",
				eds.Lpu_id as \"Lpu_id\",
				lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				to_char(eds.EvnDiagSpec_setDT, 'YYYY-MM-DD') as \"EvnDiag_setDT\",
				eds.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_EvnDiagSpec eds 
				left join v_Diag d  on d.Diag_id = eds.Diag_id
				left join v_MedStaffFact msf  on msf.MedStaffFact_id = eds.MedStaffFact_id
				left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
			where eds.Person_id = :Person_id
			union all
			select
				es.Diag_id as \"Diag_id\",
				d.Diag_Code as \"Diag_Code\",
				es.Lpu_id as \"Lpu_id\",
				lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				to_char(es.EvnSection_setDT, 'YYYY-MM-DD') as \"EvnDiag_setDT\",
				es.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_EvnSection es 
				left join v_Diag d  on d.Diag_id = es.Diag_id
				left join v_MedStaffFact msf  on msf.MedStaffFact_id = es.MedStaffFact_id
				left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
			where es.Person_id = :Person_id
			union all
			select
				edps.Diag_id as \"Diag_id\",
				d.Diag_Code as \"Diag_Code\",
				edps.Lpu_id as \"Lpu_id\",
				null as \"LpuSectionProfile_Name\",
				to_char(edps.EvnDiagPS_setDT, 'YYYY-MM-DD') as \"EvnDiag_setDT\",
				null as \"MedStaffFact_id\"
			from
				v_EvnDiagPS edps 
				left join v_Diag d  on d.Diag_id = edps.Diag_id
			where edps.Person_id = :Person_id
			union all
			select
				evpl.Diag_id as \"Diag_id\",
				d.Diag_Code as \"Diag_Code\",
				evpl.Lpu_id as \"Lpu_id\",
				lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				to_char(evpl.EvnVizitPL_setDT, 'YYYY-MM-DD') as \"EvnDiag_setDT\",
				evpl.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_EvnVizitPL evpl 
				left join v_Diag d  on d.Diag_id = evpl.Diag_id
				left join v_MedStaffFact msf  on msf.MedStaffFact_id = evpl.MedStaffFact_id
				left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
			where evpl.Person_id = :Person_id
			union all
			select
				edpls.Diag_id as \"Diag_id\",
				d.Diag_Code as \"Diag_Code\",
				edpls.Lpu_id as \"Lpu_id\",
				null as \"LpuSectionProfile_Name\",
				to_char(edpls.EvnDiagPLSop_setDT, 'YYYY-MM-DD') as \"EvnDiag_setDT\",
				null as \"MedStaffFact_id\"
			from
				v_EvnDiagPLSop edpls 
				left join v_Diag d  on d.Diag_id = edpls.Diag_id
			where edpls.Person_id = :Person_id
		";
		$resp[0]["PersonDiagOsnList"] = $callObject->queryResult($query, $queryParams);
		$query = "
			select
				to_char(euo.EvnUslugaOper_setDT, 'YYYY-MM-DD') as \"EvnUslugaOper_setDT\",
				to_char(euo.EvnUslugaOper_disDT, 'YYYY-MM-DD') as \"EvnUslugaOper_disDT\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				euo.Lpu_id as \"Lpu_id\"
			from
				v_EvnUslugaOper euo 
				left join v_UslugaComplex uc  on uc.UslugaComplex_id = euo.UslugaComplex_id
			where euo.Person_id = :Person_id
		";
		$resp[0]["EvnUslugaOperList"] = $callObject->queryResult($query, $queryParams);
		$query = "
			select
				ed.EvnDirection_id as \"EvnDirection_id\",
				ed.MedStaffFact_id as \"MedStaffFact_id\",
				to_char(ed.EvnDirection_statusDate, 'YYYY-MM-DD') as \"EvnDirection_failDT\",
				to_char(ed.EvnDirection_setDate, 'YYYY-MM-DD') as \"EvnDirection_setDate\",
				esh.EvnStatusCause_id as \"EvnStatusCause_id\",
				puc.pmUser_Name as \"pmUser_Name\"
			from
				v_EvnDirection_all ed 
				left join lateral (
					select 
						esh.EvnStatusCause_id,
						esh.pmUser_insID
					from v_EvnStatusHistory esh 
					where esh.Evn_id = ed.EvnDirection_id
					order by esh.EvnStatusHistory_insDT desc
                       limit 1
				) as esh ON true
				left join v_pmUserCache puc  on puc.pmUser_id = esh.pmUser_insID
			where ed.EvnStatus_id in (12, 13)
			  and ed.Person_id = :Person_id
		";
		$resp[0]["EvnDirectionFailList"] = $callObject->queryResult($query, $queryParams);
		$query = "
			select
				to_char(es.EvnStick_setDate, 'YYYY-MM-DD') as \"EvnStick_setDate\",
				es.StickWorkType_id as \"StickWorkType_id\",
				es.EvnStick_Num as \"EvnStick_Num\",
				es.EvnStick_Ser as \"EvnStick_Ser\",
				es.StickOrder_id as \"StickOrder_id\"
			from v_EvnStick es 
			where es.Person_id = :Person_id
		";
		$resp[0]["OpenEvnStickList"] = $callObject->queryResult($query, $queryParams);
		return $resp[0];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getPersonRecords(Person_model $callObject, $data)
	{
		$object = $data["tt"];
		$select = "";
		$join = "";
		switch ($object) {
			case "TimetableGraf":
				$select = ",
                    tt.TimetableGraf_IsModerated as \"is_moderated\",
                    rtrim(msf.Person_Surname)||' '||rtrim(msf.Person_Firname)||' '||rtrim(msf.Person_Secname) as \"MedPersonal_FIO\",
                    lsp.ProfileSpec_Name as \"ProfileSpec_Name\"
                ";
				$join = "
                    left join v_Medstafffact msf on tt.MedStaffFact_id = msf.MedStaffFact_id
                    left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
                    left join v_LpuSectionProfile lsp on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
                    left join v_LpuUnit_ER lu on lu.LpuUnit_id = ls.LpuUnit_id
                    left join v_Lpu lpu on lpu.Lpu_id = lu.Lpu_id
                ";
				break;
			case "TimetableMedService":
				$select = ",
                    mst.MedServiceType_Name as \"MedServiceType_Name\",
                    uc.UslugaComplex_Nick as \"UslugaComplex_Nick\"
                ";
				$join = "
                    left join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = tt.UslugaComplexMedService_id
                    left join v_UslugaComplex uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
                    left join v_MedService ms on ms.MedService_id = ucms.MedService_id
                    left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
                    left join v_Lpu lpu on lpu.Lpu_id = ms.Lpu_id
                    left join v_LpuUnit lu on ms.LpuUnit_id = lu.LpuUnit_id
                    left join v_LpuBuilding lpub on lpub.LpuBuilding_id = ms.LpuBuilding_id
                ";
				break;
			case "TimetableResource":
				break;
		}
		$selectString = "
			tt.{$object}_id as \"Timetable_id\",
			datediff('minute',dbo.tzGetDate(),tt.{$object}_begTime) as \"datediff\",
			to_char(tt.{$object}_begTime, '{$callObject->dateTimeForm104}') as \"Timetable_Date\",
			left(to_char(tt.{$object}_begTime, '{$callObject->dateTimeForm108}'), 5) as \"Timetable_Time\",
			lpu.Lpu_id as \"Lpu_id\",
			lpu.Lpu_Nick as \"Lpu_Nick\",
			lu.LpuUnit_Name as \"LpuUnit_Name\",
			rtrim(str.KLStreet_Name)||' '||rtrim(a.Address_House) as \"LpuUnit_Address\",
			p.Person_id as \"Person_id\"
			{$select}
		";
		$fromString = "
           	v_{$object} tt
           	left join v_Person_ER p  on tt.Person_id = p.Person_id
           	{$join}
           	left join v_Address a  on a.Address_id = lu.Address_id
           	left join v_KLStreet str  on str.KLStreet_id = a.KLStreet_id
			left join v_EvnDirection_all  as ed on ed.EvnDirection_id = tt.EvnDirection_id
			left join v_ElectronicTalon  as et on et.EvnDirection_id = ed.EvnDirection_id
		";
		$whereString = "
				tt.Person_id = :Person_id
			and tt.{$object}_begTime is not null
			and coalesce(lpu.Lpu_IsTest, 1) = 1
		";
		$orderByString = "tt.{$object}_begTime desc";
		$query = "
			select {$selectString}
			from {$fromString}
            where {$whereString}
            order by {$orderByString}
		";

		$result = $callObject->queryResult($query, $data);
		$response = ["future_records" => [], "complete_records" => []];
		if (!empty($result)) {
			foreach ($result as $record) {
				$diff = $record["datediff"];
				unset($record["datediff"]);
				if ($diff >= 0) {
					$response["complete_records"][] = $record;
				} else {
					$response["future_records"][] = $record;
				}
			}
		}
		return $response;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getPersonIdByPersonEvnId(Person_model $callObject, $data)
	{
		$params = ["PersonEvn_id" => $data["PersonEvn_id"]];
		$query = "
			select Person_id as \"Person_id\"
			from v_PersonEvn 
			where PersonEvn_id = :PersonEvn_id
			limit 1
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getPersonDataForRefValues(Person_model $callObject, $data)
	{
		$person = [
			"Sex_id" => null,
			"Person_AgeYear" => 0,
			"Person_AgeMonth" => 0,
			"Person_AgeDay" => 0,
			"Person_AgeWeek" => 0,
			"TimeOfDay" => 0,
			"Pregnancy_Value" => 0,
			"HormonalPhaseType_id" => null
		];
		// получаем все необходимые параметры по человеку + ограничения из назначений
		$query = "
			select
				Sex_id as \"Sex_id\",
				datediff('year',cast(:EvnLabSample_setDT as timestamp), Person_BirthDay) as \"Person_AgeYear\",
				datediff('month',cast(:EvnLabSample_setDT as timestamp), Person_BirthDay) as \"Person_AgeMonth\",
				datediff('day',cast(:EvnLabSample_setDT as timestamp), Person_BirthDay) as \"Person_AgeDay\",
				datediff('week',cast(:EvnLabSample_setDT as timestamp), Person_BirthDay) as \"Person_AgeWeek\",
				date_part('hour',cast(:EvnLabSample_setDT as timestamp)) as \"TimeOfDay\",
				preg.EvnPrescrLimit_ValuesNum as \"Pregnancy_Value\",
				phaze.EvnPrescrLimit_Values as \"HormonalPhaseType_id\"
			from
				v_PersonState
				left join lateral (
					select EvnPrescrLimit_ValuesNum
					from
						v_EvnPrescrLimit epl 
						inner join v_LimitType lt on lt.LimitType_id = epl.LimitType_id
					where lt.LimitType_SysNick = 'PregnancyUnitType'
					  and epl.EvnPrescr_id = :EvnPrescr_id
                    limit 1
				) as preg ON true
				left join lateral (
					select EvnPrescrLimit_Values
					from
						v_EvnPrescrLimit epl
						inner join v_LimitType lt on lt.LimitType_id = epl.LimitType_id
					where lt.LimitType_SysNick = 'HormonalPhaseType'
					  and epl.EvnPrescr_id = :EvnPrescr_id
                    limit 1
				) as phaze on true
			where Person_id = :Person_id
            limit 1
		";
		/**@var CI_DB_result $result_person */
		$result_person = $callObject->db->query($query, $data);
		if (is_object($result_person)) {
			$resp_person = $result_person->result_array();
			if (count($resp_person) > 0) {
				$person["Sex_id"] = $resp_person[0]["Sex_id"];
				$person["Person_AgeYear"] = $resp_person[0]["Person_AgeYear"];
				$person["Person_AgeMonth"] = $resp_person[0]["Person_AgeMonth"];
				$person["Person_AgeDay"] = $resp_person[0]["Person_AgeDay"];
				$person["Person_AgeWeek"] = $resp_person[0]["Person_AgeWeek"];
				$person["TimeOfDay"] = $resp_person[0]["TimeOfDay"];
				$person["Pregnancy_Value"] = $resp_person[0]["Pregnancy_Value"];
				$person["HormonalPhaseType_id"] = $resp_person[0]["HormonalPhaseType_id"];
			}
		}
		return $person;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getPersonForInnova(Person_model $callObject, $data)
	{
		$query = "
			select
				ps.Person_id as \"Code\",
				pac.PersonAmbulatCard_Num as \"Card\",
				ps.Person_FirName as \"FirstName\",
				ps.Person_SecName as \"MiddleName\",
				ps.Person_SurName as \"LastName\",
				date_part('year',ps.Person_Birthday) as \"BirthYear\",
				date_part('month',ps.Person_Birthday) as \"BirthMonth\",
				date_part('day',ps.Person_Birthday) as \"BirthDay\",
				to_char(ps.Person_Birthday, '{$callObject->dateTimeForm120}') as \"BirthDate\",
				coalesce(ps.Sex_id, 0) as \"Sex\",
				coalesce(case when ps.Polis_Ser is not null then ps.Polis_Ser else '' end, '') as \"PolicySeries\",
				coalesce(case when ps.Polis_Num is not null then ps.Polis_Num else '' end, '') as \"PolicyNumber\",
				pa.Address_Address as \"pAddress\",
				pa.Address_House as \"Building\",
				pa.Address_Flat as \"Flat\"
			from
				v_PersonState ps 
				left join v_Address pa on ps.PAddress_id = pa.Address_id
				left join v_PersonAmbulatCard pac on ps.Person_id = pac.Person_id
			where ps.Person_id = :Person_id
			order by pac.PersonAmbulatCard_id desc
			limit 1
		";
		return $callObject->queryResult($query, $data);
	}

	public static function getPersonEvn(Person_model $callObject, $data)
	{
		return $callObject->getFirstResultFromQuery("
			select
				PersonEvn_id 
			from 
				v_PersonEvn 
			where 
				Person_id = :Person_id and 
				Server_id = :Server_id
			order by PersonEvn_id desc
			limit 1
		",
		[
			"Person_id" => $data["Person_id"],
			"Server_id"=>$data["Server_id"]
		]);
	}
	public static function getPersonMain(Person_model $callObject, $data) {
		$result = $callObject->getFirstRowFromQuery("
        		select
                    p.Person_id as \"Person_id\",
					p.Person_SurName as \"Person_Surname\",
					p.Person_FirName as \"Person_Firname\",
					p.Person_SecName as \"Person_Secname\",
					p.Sex_id as \"PersonSex_id\",
					Person_BirthDay as \"Person_BirthDate\",
					Polis_Ser,
					Polis_Num,
					rtrim(p.Person_Surname)||' '||rtrim(p.Person_Firname)||coalesce(' '||rtrim(p.Person_Secname), '') as \"Person_FIO\",
					case 
						when p.Polis_id is not null and coalesce(p.Polis_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
						then 0 else 1 
					end as \"PolisIsClosed\",
					coalesce(pers.Person_IsUnknown, 1) as \"Person_IsUnknown\"
                from
                	v_Person_ER p
                	left join v_Person pers on pers.Person_id = p.Person_id
				where
					p.Person_id = :Person_id",array("Person_id" => $data["Person_id"]));
		//return $data;

		if (!empty($result)) {
			// Начальная обработка результатов
			$result['Person_Surname'] = ucwords($result['Person_Surname']);
			$result['Person_Firname'] = ucwords($result['Person_Firname']);
			$result['Person_Secname'] = ucwords($result['Person_Secname']);
			$result['Person_FIO'] = ucwords($result['Person_FIO']);
			$result['Person_SecureFIO'] = $result['Person_Firname'].' '.$result['Person_Secname'].' '.mb_substr($result['Person_Surname'],0,1).'.';
		}

		return $result;
	}

	/**
	 * Определение прикрепления
	 *
	 * @param int $patient пациент
	 * @param int $type_id id типа прикрепления (LpuAttachType)
	 */
	public static function getPersonAttach(Person_model $callObject, $data) {
		$attach = array();

		// Пытаемся извлечь прикрепление из карты
		$params = array(
			'person_id'=>$data['Person_id'],
			'type_id' => $data['attach_type']
		);
		$result = $callObject->getFirstRowFromQuery("
			select
				Terr.ERTerr_id  as \"Terr_id\",
				pc.Lpu_id as \"Lpu_id\",
				pc.LpuRegion_id as \"LpuRegion_id\",
                l.Lpu_Nick as \"Lpu_Nick\",
                lr.LpuRegion_Name
			from v_PersonCard pc
			left join LpuRegionStreet lrs  on lrs.LpuRegion_id = pc.LpuRegion_id
            left outer join v_Lpu l on pc.Lpu_id = l.Lpu_id
            left outer join v_LpuRegion lr on pc.LpuRegion_id = lr.LpuRegion_id
			LEFT JOIN LATERAL ( 
				select
					ERTerr_id
				from ERTerr Terr
				where
					(
						((lrs.KLCountry_id = Terr.KLCountry_id) or (Terr.KLCountry_id is null)) and
						((lrs.KLRGN_id = Terr.KLRGN_id) or (Terr.KLRGN_id is null)) and
						((lrs.KLSubRGN_id = Terr.KLSubRGN_id) or (Terr.KLSubRGN_id is null)) and
						((lrs.KLCity_id = Terr.KLCity_id) or (Terr.KLCity_id is null)) and
						((lrs.KLTown_id = Terr.KLTown_id) or (Terr.KLTown_id is null))
					)
				limit 1
			) as Terr on true
			where pc.Person_id = :person_id
				and pc.LpuAttachType_id = :type_id
                and pc.PersonCard_endDate is null
				and coalesce(l.Lpu_IsTest, 1) = 1
				limit 1
		",$params);

		if (!empty($result)) {
			$attach = $result;
			$callObject->load->model('LpuRegionStreets_model');
			$attach['doctors'] = $callObject->LpuRegionStreets_model->getLpuRegionMedStaffFactList($attach['LpuRegion_id']);
		}
		return $attach;
	}

	/**
	 * Получение записей (будущих\прошедших) пациента для портала КВРАЧУ
	 */
	public static function getPersonRecordsAll(Person_model $callObject,$data) {

		if (!empty($data['pastRecords'])) {
			$data['sign'] = "<";
		}

		if (!empty($data['futureRecords'])) {
			if (!empty($data['showTodayRecords'])) {
				$data['sign'] = ">=";
			} else {
				$data['sign'] = ">";
			}
		}
		$user_id = 0;
		//todo грузить с портала
		if(!empty($data['pmuser_id'])) {
			$user_id = $data['pmuser_id'];
		}
		// Если вошли по ЕСИА показываем все записи человека (включая записи в промед)
		if (!is_null($data['pmuser_id']) && $data['pmuser_id'] == 0) {
			$user_id = 0;
		}

		$filter = "";

		$params = array(
			'user_id' => $user_id,
		);

		$signFilter = '';

		if (!empty($data['futureRecords'])) {
			$signFilter = " or (DATE_PART('day',dbo.tzGetdate()::timestamp - tt.TimetableGraf_begTime::timestamp)) = 0 and et.ElectronicTalon_id is not null and et.ElectronicTalonStatus_id < 4) ";
		}

		if (!empty($data['pastRecords'])) {
			$signFilter = " or (DATE_PART('day',dbo.tzGetdate()::timestamp - tt.TimetableGraf_begTime::timestamp)) < 0 and et.ElectronicTalon_id is not null) ";
		}

		if (!empty($data['sign'])) {
			if (empty($data['showTodayRecords'])) {
				$filter = " and (tt.TimetableGraf_begTime {$data['sign']} dbo.tzGetdate() and et.ElectronicTalon_id is null {$signFilter}";
			} else {
				$filter = " and (cast(tt.TimetableGraf_begTime as date) {$data['sign']} cast(dbo.tzGetdate() as date) and et.ElectronicTalon_id is null {$signFilter}";
			}
		}

			$select = ",
				'Timetable' as \"viewGroup\",
				'TimetableGraf' as object,
                tt.TimetableGraf_IsModerated as \"TimetableGraf_IsModerated\",
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.MedSpecOms_id as \"MedSpecOms_id \",
				msf.MedStaffFactCache_IsPaidRec as \"MedStaffFactCache_IsPaidRec \",
				msf.MedStaffFactCache_CostRec as  \"MedStaffFactCache_CostRec\",
				rtrim(msf.Person_Surname)||' '||left(msf.Person_Firname,1)||'. '||left(msf.Person_Secname,1)||'.' as \"MedPersonal_FIO\",
				rtrim(msf.Person_Surname)||' '||rtrim(msf.Person_Firname)||' '||rtrim(msf.Person_Secname) as \"MedPersonal_FullFIO\",
				lsp.ProfileSpec_Name as \"ProfileSpec_Name\",
				lsp.LpuSectionProfile_id as \"Profile_id\",
				case when (
				select
					count(AV.AttributeValue_id)
				from
					v_AttributeValue AV 
					inner join v_Attribute A  on A.Attribute_id = AV.Attribute_id and A.Attribute_SysNick in ('portalzno', 'EarlyDetect')
					left join v_AttributeVision AVI  on AVI.Attribute_id = A.Attribute_id
					left join v_AttributeSignValue ASV  on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
				where
					AV.AttributeValue_TableName = 'dbo.LpuSection'
					and AV.AttributeValue_ValueBoolean::integer = 1
					and AV.AttributeValue_TablePKey = ls.LpuSection_id
					and coalesce(AVI.AttributeVision_begDate, '2000-01-01') <= dbo.tzGetDate()
					and coalesce(AVI.AttributeVision_endDate, '2030-01-01') >= dbo.tzGetDate()
					and coalesce(A.Attribute_begDate, '2000-01-01') <= dbo.tzGetDate()
					and coalesce(A.Attribute_endDate, '2030-01-01') >= dbo.tzGetDate()
					and coalesce(ASV.AttributeSignValue_begDate, '2000-01-01') <= dbo.tzGetDate()
					and coalesce(ASV.AttributeSignValue_endDate, '2030-01-01') >= dbo.tzGetDate()
				) = 2 then 1 else 0 end as cabinetDetectionZNO
            ";

			$join = "
                left join v_MedstaffFact msf  on tt.MedStaffFact_id = msf.MedStaffFact_id
                left join v_LpuSection ls  on ls.LpuSection_id = msf.LpuSection_id
                left join v_LpuSectionProfile lsp  on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
                left join v_LpuUnit_ER lu on lu.LpuUnit_id = ls.LpuUnit_id
                left join v_Lpu lpu on lpu.Lpu_id = lu.Lpu_id
            ";
        $sql = "select distinct
					tt.TimetableGraf_id as \"TimetableGraf_id\",
                    tt.TimetableGraf_Day as \"TimetableGraf_Day\",
                    tt.TimetableGraf_updDT as \"TimetableGraf_updDT\",
                    tt.TimetableGraf_begTime as \"TimetableGraf_begTime\",
                    to_char(tt.TimetableGraf_begTime, '{$callObject->dateTimeForm104}') as \"RecordSetDate\",
                    to_char(tt.TimetableGraf_begTime,'{$callObject->dateTimeForm108}') as \"RecordSetTime\",
                    to_char(tt.TimetableGraf_begTime, '{$callObject->dateTimeForm104} {$callObject->dateTimeForm108}') as \"sortField\",
                    (DATE_PART('day', tt.TimetableGraf_begTime::timestamp - dbo.tzGetdate()::timestamp) * 24 + 
               		DATE_PART('hour', tt.TimetableGraf_begTime::timestamp - dbo.tzGetdate()::timestamp)) * 60 +
               		DATE_PART('minute', tt.TimetableGraf_begTime::timestamp - dbo.tzGetdate()::timestamp) as \"DateDiff\",
					(DATE_PART('year', dbo.tzGetdate()::date) - DATE_PART('year', tt.TimetableGraf_begTime::date)) * 12 +
              		(DATE_PART('month', dbo.tzGetdate()::date) - DATE_PART('month', tt.TimetableGraf_begTime::date)) as \"MonthDiff\",
                    lpu.Lpu_id as \"Lpu_id\",
                    lpu.Lpu_Nick as \"Lpu_Nick\",
                    lpu.Lpu_Name as \"Lpu_Name\",
                    lu.LpuUnit_id as \"LpuUnit_id\",
                    lu.LpuUnit_Name as \"LpuUnit_Name\",
                    lu.LpuUnit_Phone as \"LpuUnit_Phone\",
                    rtrim(str.KLStreet_Name)||' '||rtrim(a.Address_House) as \"LpuUnit_Address\",
                    rtrim(p.Person_Surname)||' '||rtrim(p.Person_Firname)||coalesce (' '||rtrim(p.Person_Secname), '') as \"Person_FIO\",
                    p.Person_id as \"Person_id\",
					ed.EvnDirection_TalonCode as \"EvnDirection_TalonCode\",
					et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
					'pm_paid' as source_system
	                {$select}
                from v_TimetableGraf tt 
                left join v_Person_ER p  on tt.Person_id = p.Person_id
                {$join}
                left join v_Address a  on a.Address_id = lu.Address_id
                left join v_KLStreet str  on str.KLStreet_id = a.KLStreet_id
				left join v_EvnDirection_all as ed on ed.EvnDirection_id = tt.EvnDirection_id
				left join v_ElectronicTalon  as et on et.EvnDirection_id = ed.EvnDirection_id
				left join v_EvnQueue q  on q.TimetableGraf_id = tt.TimetableGraf_id
				left join v_EvnQueue_RecRequest eqr on eqr.EvnDirection_id = tt.EvnDirection_id
                where (1=1)
                and (1 = CASE
                		 -- если есть связанная заявка
						  WHEN eqr.EvnQueue_id is not null
						  	THEN case when (eqr.pmUser_insID = :user_id or :user_id = 0) then 1
							else 0 end
						  WHEN eqr.EvnQueue_id is null
						   	THEN case when (tt.pmUser_updID = :user_id or :user_id = 0) then 1
							else 0 end
						  ELSE 0
				   	END) 
			    and tt.Person_id in ({$data['Person_list']})
                    and tt.TimetableGraf_begTime is not null
                    {$filter}
                    and coalesce(lpu.Lpu_IsTest, 1) = 1
                    and (q.EvnQueueStatus_id = 3 or q.EvnQueueStatus_id is NULL)

                order by tt.TimetableGraf_begTime desc";
		$result = $callObject->queryResult($sql, $params);
			
		return $result;
	}

	/**
	 * Получить данных пациента для medSvid.
	 * @param Person_model $callObject
	 * @param $data
	 * @return mixed|null
	 */
	 public static function getPersonForMedSvid(Person_model $callObject, $data)
	 {
		 $sql = "
			select 
				PersonEvn_id as \"PersonEvn_id\",  
				to_char(Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
				dbo.Age2(ps.Person_BirthDay, (EXTRACT(YEAR FROM dbo.tzGetDate())||'-12-31')::date ) as \"Person_AgeEndYear\",
				(DATE_PART('year', dbo.tzGetDate()) - DATE_PART('year', ps.Person_BirthDay))*12  +
				(DATE_PART('month', dbo.tzGetDate()) - DATE_PART('month',  ps.Person_BirthDay)) -
				 CASE WHEN EXTRACT(DAY from ps.Person_BirthDay) >  EXTRACT(DAY from dbo.tzGetDate()) THEN 1 ELSE 0 END as \"Person_AgeMonths\",
				DATE_PART('day', dbo.tzGetDate() - ps.Person_BirthDay) as \"Person_AgeDays\",
				Sex_id
			from v_PersonState PS
			where PS.Person_id = :Person_id
			limit 1;
		";
		 $resp = $callObject->dbmodel->getFirstRowFromQuery($sql, $data);
		 return (!empty($resp)) ? $resp : null;
	 }

	/**
	 * Получить данных о представителе пациента.
	 * @param Person_model $callObject
	 * @param $data
	 * @return mixed|null
	 */
	public static function getPersonDeputy(Person_model $callObject, $data){
		$query = "
			SELECT
				 PA.Person_Fio as \"Deputy_Fio\",
				 PA.PersonPhone_Phone as \"Deputy_Phone\",
			     PD.Person_pid as \"Deputy_id\"
			FROM dbo.v_PersonDeputy PD
			LEFT JOIN dbo.v_Person_all PA ON PA.Person_id = PD.Person_pid
			WHERE pd.Person_id = :Person_id
			limit 1
		";

		$resp = $callObject->queryResult($query, ['Person_id' => $data['Person_id']]);
		return $resp;
	}
}