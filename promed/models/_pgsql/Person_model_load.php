<?php

class Person_model_load
{
	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonEval(Person_model $callObject, $data)
	{
		$query = "
			select
				'PersonHeight_id' as \"PersonEvalClass\",
				PH.PersonHeight_id as \"PersonEvalClass_id\",
				'PersonHeight' || PH.PersonHeight_id as \"PersonEval_id\",
				'Рост(см)' as \"EvalType\",
				PH.Person_id as \"Person_id\",
				to_char(PH.PersonHeight_setDT, '{$callObject->dateTimeForm104}') as \"PersonEval_setDT\",
				cast(PH.PersonHeight_Height as float) as \"PersonEval_value\",
				coalesce(IsAbnorm.YesNo_Name, '') as \"PersonEval_isAbnorm\",
				coalesce(HAT.HeightAbnormType_Name, '') as \"EvalAbnormType\",
				coalesce(HMT.HeightMeasureType_Name, '') as \"EvalMeasureType\",
				coalesce(cast(HMT.HeightMeasureType_Code as varchar), '') as \"EvalMeasureTypeCode\"
			from v_PersonHeight PH
				inner join HeightMeasureType HMT on HMT.HeightMeasureType_id = PH.HeightMeasureType_id
				left join YesNo IsAbnorm on IsAbnorm.YesNo_id = PH.PersonHeight_IsAbnorm
				left join HeightAbnormType HAT on HAT.HeightAbnormType_id = PH.HeightAbnormType_id
			where Person_id = :Person_id
			
			union all
			select
				'PersonWeight_id' as \"PersonEvalClass\",
				PW.PersonWeight_id as \"PersonEvalClass_id\",
				'PersonWeight' || PW.PersonWeight_id as \"PersonEval_id\",
				'Вес(кг)' as \"EvalType\",
				PW.Person_id as \"Person_id\",
				to_char(PW.PersonWeight_setDT, '{$callObject->dateTimeForm104}') as \"PersonEval_setDT\",
				case when pw.Okei_id = 36
						 then cast(pw.PersonWeight_Weight as float) / 1000
					 else pw.PersonWeight_Weight
					end as \"PersonEval_value\",
				coalesce(IsAbnorm.YesNo_Name, '') as \"PersonEval_isAbnorm\",
				coalesce(WAT.WeightAbnormType_Name, '') as \"EvalAbnormType\",
				coalesce(WMT.WeightMeasureType_Name, '') as \"EvalMeasureType\",
				coalesce(cast(WMT.WeightMeasureType_Code as varchar), '') as \"EvalMeasureTypeCode\"
			from v_PersonWeight PW
				inner join WeightMeasureType WMT on WMT.WeightMeasureType_id = PW.WeightMeasureType_id
				left join YesNo IsAbnorm on IsAbnorm.YesNo_id = PW.PersonWeight_IsAbnorm
				left join WeightAbnormType WAT on WAT.WeightAbnormType_id = PW.WeightAbnormType_id
			where Person_id = :Person_id
			
			union all
			select
				'HeadCircumference_id' as \"PersonEvalClass\",
				HC.HeadCircumference_id as \"PersonEvalClass_id\",
				'HeadCircumference' || HC.HeadCircumference_id as \"PersonEval_id\",
				'Окружность головы (см)' as \"EvalType\",
				PC.Person_id as \"Person_id\",
				to_char(HC.HeadCircumference_insDT, '{$callObject->dateTimeForm104}') as \"PersonEval_setDT\",
				cast(HC.HeadCircumference_Head as float) as \"PersonEval_value\",
				'' as \"PersonEval_isAbnorm\",
				'' as \"EvalAbnormType\",
				coalesce(HMT.HeightMeasureType_Name, '') as \"EvalMeasureType\",
				coalesce(cast(HMT.HeightMeasureType_Code as varchar), '') as \"EvalMeasureTypeCode\"
			from v_HeadCircumference HC
				inner join HeightMeasureType HMT on HMT.HeightMeasureType_id = HC.HeightMeasureType_id
				left join v_PersonChild PC on PC.PersonChild_id = HC.PersonChild_id
			where PC.Person_id = :Person_id
			
			union all
			select
				'ChestCircumference_id' as \"PersonEvalClass\",
				CC.ChestCircumference_id as \"PersonEvalClass_id\",
				'ChestCircumference' || CC.ChestCircumference_id as \"PersonEval_id\",
				'Окружность груди (см)' as \"EvalType\",
				PC.Person_id as \"Person_id\",
				to_char(CC.ChestCircumference_insDT, '{$callObject->dateTimeForm104}') as \"PersonEval_setDT\",
				cast(CC.ChestCircumference_Chest as float) as \"PersonEval_value\",
				'' as \"PersonEval_isAbnorm\",
				'' as \"EvalAbnormType\",
				coalesce(HMT.HeightMeasureType_Name, '') as \"EvalMeasureType\",
				coalesce(cast(HMT.HeightMeasureType_Code as varchar), '') as \"EvalMeasureTypeCode\"
			from v_ChestCircumference CC
				inner join HeightMeasureType HMT on HMT.HeightMeasureType_id = CC.HeightMeasureType_id
				left join v_PersonChild PC on PC.PersonChild_id = CC.PersonChild_id
			where PC.Person_id = :Person_id
		";
		$params = ["Person_id" => $data["Person_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение данных полиса
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPolisData(Person_model $callObject, $data)
	{
		$sql = "
			select
				VP.OMSSprTerr_id as \"OMSSprTerr_id\",
				VP.OrgSMO_id as \"OrgSMO_id\",
				VP.Polis_Ser as \"Polis_Ser\",
				case when VP.PolisType_id = 4 then '' else VP.Polis_Num end as \"Polis_Num\",
				to_char(VP.Polis_begDate, '{$callObject->dateTimeForm104}') as \"Polis_begDate\",
				to_char(VP.Polis_endDate, '{$callObject->dateTimeForm104}') as \"Polis_endDate\",
				VP.PolisType_id as \"PolisType_id\",
				VP.PolisFormType_id as \"PolisFormType_id\",
				VP.BDZ_id as \"BDZ_id\",
				OST.KLRgn_id as \"KLRgn_id\",
				pst.Person_edNum as \"Federal_Num\",
				a.PersonEvn_id as \"FederalEvn_id\",
				a.server_id as \"FederalServer_id\",
				VP.Polis_Guid as \"Polis_Guid\"
			from
				v_Person_all ps 
				left join Polis VP on vp.Polis_id = ps.Polis_id
				left join v_PersonState pst on pst.Person_id = ps.Person_id
				LEFT JOIN v_OmsSprTerr OST on OST.OMSSprTerr_id = VP.OMSSprTerr_id
				LEFT JOIN LATERAL (
					select
							PersonEvn_id,
							Server_id
					from v_PersonEvn 
					where PersonEvnClass_id = 16
					  and PersonEvn_insDT <= VP.Polis_begDate
					  and Person_id = :Person_id
					order by
						PersonEvn_insDT desc,
						PersonEvn_TimeStamp desc
		            limit 1
				) as a ON true
			where ps.Person_id = :Person_id
			  and ps.PersonEvn_id = :PersonEvn_id
		";
		/**@var CI_DB_result $result */
		$sqlParams = [
			"PersonEvn_id" => $data["PersonEvn_id"],
			"Person_id" => $data["Person_id"],
			"Server_id" => $data["Server_id"]
		];
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение данных о документе
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDocumentData(Person_model $callObject, $data)
	{
		$sql = "
			select
				DocumentType_id as \"DocumentType_id\",
				Document_Ser as \"Document_Ser\",
				Document_Num as \"Document_Num\",
				OrgDep_id as \"OrgDep_id\",
				to_char(Document_begDate, '{$callObject->dateTimeForm104}') as \"Document_begDate\"
			from v_Document 
			where Document_id = :Document_id
		";
		$sqlParams = ["Document_id" => $data["Document_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение данных о гражданстве
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadNationalityStatusData(Person_model $callObject, $data)
	{
		$sql = "
			select
				KLCountry_id as \"KLCountry_id\",
				case when NationalityStatus_IsTwoNation = 2 then 1 else 0 end as \"NationalityStatus_IsTwoNation\",
				LegalStatusVZN_id as \"LegalStatusVZN_id\"
			from v_NationalityStatus 
			where NationalityStatus_id = :NationalityStatus_id
		";
		return $callObject->queryResult($sql, $data);
	}

	/**
	 * Получение данных о работе
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadJobData(Person_model $callObject, $data)
	{
		$sql = "
			select
				Org_id as \"Org_id\",
				OrgUnion_id as \"OrgUnion_id\",
				Post_id as \"Post_id\"
			from v_Job 
			where Job_id = :Job_id
		";
		$sqlParams = ["Job_id" => $data["Job_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение истории идентификации человека в ЦС ЕРЗ
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonRequestDataGrid(Person_model $callObject, $data)
	{
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select
				-- select
				PRD.PersonRequestData_id as \"PersonRequestData_id\",
				PRD.Person_id as \"Person_id\",
				PRST.PersonRequestSourceType_id as \"PersonRequestSourceType_id\",
				PRST.PersonRequestSourceType_Name as \"PersonRequestSourceType_Name\",
				to_char(PRD.PersonRequestData_insDT, '{$callObject->dateTimeForm104}')||' '||to_char(PRD.PersonRequestData_insDT, '{$callObject->dateTimeForm108}') as \"PersonRequestData_insDT\",
				to_char(PRD.PersonRequestData_csDT, '{$callObject->dateTimeForm104}')||' '||to_char(PRD.PersonRequestData_csDT, '{$callObject->dateTimeForm108}') as \"PersonRequestData_csDT\",
				coalesce(EPL.EvnPL_id, EPS.EvnPS_id, CCC.CmpCallCard_id) as \"Evn_id\",
				case 
					when EPL.EvnPL_id is not null then 'EvnPL'
					when EPS.EvnPS_id is not null then 'EvnPS'
					when CCC.CmpCallCard_id is not null then 'CmpCallCard'
				end \"EvnClass\",
				case 
					when EPL.EvnPL_id is not null then 'ТАП / №'||EPL.EvnPL_NumCard::varchar(1)
					when EPS.EvnPS_id is not null then 'КВС / №'||EPS.EvnPS_NumCard::varchar(1)
					when CCC.CmpCallCard_id is not null then 'СМП / №'||CCC.CmpCallCard_Ngod::varchar(1)
				end as \"Evn_Name\",
				PRDS.PersonRequestDataStatus_id as \"PersonRequestDataStatus_id\",
				PRDS.PersonRequestDataStatus_Name as \"PersonRequestDataStatus_Name\",
				PNIC.PersonNoIdentCause_id as \"PersonNoIdentCause_id\",
				PNIC.PersonNoIdentCause_Name as \"PersonNoIdentCause_Name\",
				case when PNIC.PersonNoIdentCause_Name is not null then PNIC.PersonNoIdentCause_Name else PRD.PersonRequestData_Error end as \"NoIdentCause\"
				-- end select
			from
				-- from
				erz.v_PersonRequestData PRD 
				left join erz.v_PersonRequestSourceType PRST on PRST.PersonRequestSourceType_id = PRD.PersonRequestSourceType_id
				left join erz.v_PersonRequestDataStatus PRDS on PRDS.PersonRequestDataStatus_id = PRD.PersonRequestDataStatus_id
				left join erz.v_PersonNoIdentCause PNIC on PNIC.PersonNoIdentCause_id = PRD.PersonNoIdentCause_id
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = PRD.Evn_id
				left join v_EvnPL EPL on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
				left join v_EvnSection ES on ES.EvnSection_id = PRD.Evn_id
				left join v_EvnPS EPS on EPS.EvnPS_id = ES.EvnSection_pid
				left join v_CmpCallCard CCC on CCC.CmpCallCard_id = PRD.Evn_id
				-- end from
			where
				-- where
				PRD.Person_id = :Person_id
				-- end where
			order by
				-- order by
				PRD.PersonRequestData_insDT desc,
				PRD.PersonRequestData_csDT desc
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
	 * Получение истории операций по согласию/отзыву согласия на обработку перс.данных
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function loadPersonLpuInfoList(Person_model $callObject, $data)
	{
		$query = "
			select 
				pi.PersonLpuInfo_id as \"PersonLpuInfo_id\",
				COALESCE(to_char(pi.PersonLpuInfo_setDT, '{$callObject->dateTimeForm104}'), '') as \"PersonLpuInfo_setDT\",
				case when COALESCE(pi.PersonLpuInfo_IsAgree,2) = 1 then 'Отзыв согласия' else 'Согласие' end as \"Doc_type\",
				pu.PMUser_Name as \"PMUser_Name\"
			from
				v_PersonLpuInfo pi 
				left join v_pmUserCache pu  on pu.PMUser_id = pi.pmUser_insID
			where pi.Person_id = :Person_id
			  and pi.Lpu_id = :Lpu_id
			order by pi.PersonLpuInfo_setDT desc
		";

		$resp = $callObject->queryResult($query, $data);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при получении данных согласия/отзыва согласия на обработку перс.данных");
		}
		return $resp;
	}

	/**
	 * Получение списка людей для API
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadPersonListForAPI(Person_model $callObject, $data)
	{
		$offset = !empty($data["offset"]) ? $data["offset"] : 0;
		$params = [];
		$filters = [];
		if (!empty($data["Person_id"])) {
			$filters[] = "PS.Person_id = :Person_id";
			$params["Person_id"] = $data["Person_id"];
		}
		if (!empty($data["PersonSurName_SurName"])) {
			$filters[] = "PS.Person_SurName = :Person_SurName";
			$params["Person_SurName"] = $data["PersonSurName_SurName"];
		}
		if (!empty($data["PersonFirName_FirName"])) {
			$filters[] = "PS.Person_FirName = :Person_FirName";
			$params["Person_FirName"] = $data["PersonFirName_FirName"];
		}
		if (!empty($data["PersonSecName_SecName"])) {
			$filters[] = "PS.Person_SecName = :Person_SecName";
			$params["Person_SecName"] = $data["PersonSecName_SecName"];
		}
		if (!empty($data["PersonBirthDay_BirthDay"])) {
			$filters[] = "PS.Person_BirthDay = :Person_BirthDay";
			$params["Person_BirthDay"] = $data["PersonBirthDay_BirthDay"];
		}
		if (!empty($data["PersonSnils_Snils"])) {
			$filters[] = "PS.Person_Snils = :Person_Snils";
			$params["Person_Snils"] = $data["PersonSnils_Snils"];
		}
		if (!empty($data["Polis_Ser"])) {
			$filters[] = "PS.Polis_Ser = :Polis_Ser";
			$params["Polis_Ser"] = $data["Polis_Ser"];
		}
		if (!empty($data["Polis_Num"])) {
			$filters[] = "PS.Polis_Num = :Polis_Num";
			$params["Polis_Num"] = $data["Polis_Num"];
		}
		if (!empty($data["Person_pid"])) {
			$filters[] = "PD.Person_pid = :Person_pid";
			$filters[] = "coalesce(PS.Polis_Num, PS.Person_EdNum) = :Polis_Num"; // Person_EdNum проверяем если только Polis_Num пустой, но сам по себе такой фильтр работает медленно, поэтому дублируем через or
			$params["Person_pid"] = $data["Person_pid"];
		}
		if (!empty($data["DeputyKind_id"])) {
			$filters[] = "PD.DeputyKind_id = :DeputyKind_id";
			$params["DeputyKind_id"] = $data["DeputyKind_id"];
		}
		if (!empty($data["Person_isUnknown"])) {
			$filters[] = "P.Person_isUnknown = :Person_isUnknown";
			$params["Person_isUnknown"] = $data["Person_isUnknown"];
		}
		if (!empty($data["Person_iin"])) {
			$filters[] = "PS.Person_Inn = :Person_Inn";
			$params["Person_Inn"] = $data["Person_iin"];
		}
		if (count($params) == 0) {
			throw new Exception("Не передан ни один из параметров поиска", 6);
		}
		$filters_str = implode(" and ", $filters);
		$query = "
			select
				-- select
				PS.Person_id as \"Person_id\",
				PS.Person_SurName as \"PersonSurName_SurName\",
				PS.Person_FirName as \"PersonFirName_FirName\",
				PS.Person_SecName as \"PersonSecName_SecName\",
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm120}') as \"PersonBirthDay_BirthDay\",
				PS.Sex_id as \"Person_Sex_id\",
				PS.Person_Phone as \"PersonPhone_Phone\",
				PS.Person_Snils as \"PersonSnils_Snils\",
				PS.Person_Inn as \"PersonInn_Inn\",
				PS.SocStatus_id as \"SocStatus_id\",
				PS.Polis_id as \"Polis_id\",
				PS.PolisType_id as \"PolisType_id\",
				PS.Polis_Ser as \"Polis_Ser\",
				PS.Polis_Num as \"Polis_Num\",
				PS.UAddress_id as \"UAddress_id\",
				PS.PAddress_id as \"PAddress_id\",
				PBP.Address_id as \"BAddress_id\",
				J.Org_id as \"Org_id\",
				J.Post_id as \"Post_id\",
				P.BDZ_guid as \"BDZ_guid\",
				P.BDZ_id as \"BDZ_id\",
				PD.Person_pid as \"Person_pid\",
				COALESCE(PD.DeputyKind_id, 0) as \"DeputyKind_id\",
				P.Person_isUnknown as \"Person_isUnknown\"
				-- end select
			from
				--from
				v_PersonState PS 
				left join v_Person P on P.Person_id = PS.Person_id
				left join v_PersonBirthPlace PBP on PBP.Person_id = PS.Person_id
				left join v_Job J on J.Job_id = PS.Job_id
				left join v_PersonDeputy PD on PD.Person_id = PS.Person_id
				--end from
			where
				-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				PS.Person_id
				-- end order by
		";
		$result = $callObject->queryResult(getLimitSQLPH($query, $offset, 100), $params);
		$result_count = $callObject->queryResult(getCountSQLPH($query), $params);
		if (!is_array($result) || !is_array($result_count)) {
			return false;
		}
		return ["totalCount" => $result_count[0]["cnt"], "data" => $result];
	}

	/**
	 * Получение списка сотрудников
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadPersonWorkList(Person_model $callObject, $data)
	{
		$where = [];
		if (!empty($data["PersonWork_id"])) {
			$where[] = "pw.PersonWork_id = :PersonWork_id";
		} else {
			if (!empty($data["Person_id"])) {
				$where[] = "pw.Person_id = :Person_id";
			}
			if (!empty($data["Post_id"])) {
				$where[] = "pw.Post_id = :Post_id";
			}
			if (!empty($data["Org_id"])) {
				$where[] = "pw.Org_id = :Org_id";
			}
			if (!empty($data["query"])) {
				$where[] = "nm.PersonWork_Name iLIKE :query||'%'";
			}
		}
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
					{$where_clause}
			";
		}
		$query = "
			select 
                pw.PersonWork_id as \"PersonWork_id\",
                nm.PersonWork_Name as \"PersonWork_Name\",
                pw.Person_id as \"Person_id\",
                pw.Post_id as \"Post_id\"
            from
                v_PersonWork pw 
                left join v_PersonState ps on ps.Person_id = pw.Person_id
                left join v_Post p on p.Post_id = pw.Post_id
                left join lateral (
                    select
                        (
                            coalesce(ltrim(rtrim(ps.Person_SurName)), '') ||
                            coalesce(' '||ltrim(rtrim(ps.Person_FirName)), '') ||
                            coalesce(' '||ltrim(rtrim(ps.Person_SecName)), '') ||
                            coalesce(' '||to_char(ps.Person_BirthDay, '{$callObject->dateTimeForm104}'), '') ||
                            coalesce(' '||p.Post_Name, '')
                        ) as PersonWork_Name
                ) as nm ON true
            {$where_clause}
            order by
                pw.PersonWork_id
			limit 1
		";
		$resp = $callObject->queryResult($query, $data);
		return $resp;
	}

	/**
	 * Получение данных о сотруднике организации для редактирования
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadPersonWorkForm(Person_model $callObject, $data)
	{
		$params = ["PersonWork_id" => $data["PersonWork_id"]];
		$query = "
			select 
				PW.PersonWork_id as \"PersonWork_id\",
				to_char(PW.PersonWork_begDate, '{$callObject->dateTimeForm104}') as \"PersonWork_begDate\",
				to_char(PW.PersonWork_endDate, '{$callObject->dateTimeForm104}') as \"PersonWork_endDate\",
				PW.Org_id as \"Org_id\",
				PW.OrgStruct_id as \"OrgStruct_id\",
				PW.Person_id as \"Person_id\",
				(
					COALESCE(ltrim(rtrim(PS.Person_SurName)), '') ||
					COALESCE(' '||ltrim(rtrim(PS.Person_FirName)), '') ||
					COALESCE(' '||ltrim(rtrim(PS.Person_SecName)), '')
				) as \"Person_Fio\",
				PW.Post_id as \"Post_id\",
				PW.pmUserCacheOrg_id as \"pmUserCacheOrg_id\"
			from
				v_PersonWork PW 
				left join v_PersonState PS on PS.Person_id = PW.Person_id
			where PW.PersonWork_id = :PersonWork_id
			limit 1
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * Получение списка сотрудников
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonWorkGrid(Person_model $callObject, $data)
	{
		$params = ["Org_id" => $data["Org_id"]];
		$filters = ["PW.Org_id = :Org_id"];
		if (!empty($data["OrgStruct_id"])) {
			$filters[] = "PW.OrgStruct_id = :OrgStruct_id";
			$params["OrgStruct_id"] = $data["OrgStruct_id"];
		}
		$filters_str = implode(" and ", $filters);
		$query = "
			select
				-- select
				PW.PersonWork_id as \"PersonWork_id\",
				to_char(PW.PersonWork_begDate, '{$callObject->dateTimeForm104}') as \"PersonWork_begDate\",
				to_char(PW.PersonWork_endDate, '{$callObject->dateTimeForm104}') as \"PersonWork_endDate\",
				PS.Person_id as \"Person_id\",
				(
					COALESCE(ltrim(rtrim(PS.Person_SurName)), '') ||
					COALESCE(' '||ltrim(rtrim(PS.Person_FirName)), '') ||
					COALESCE(' '||ltrim(rtrim(PS.Person_SecName)), '')
				) as \"Person_Fio\",
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				P.Post_id as \"Post_id\",
				P.Post_Name as \"Post_Name\"
				-- end select
			from
				-- from
				v_PersonWork PW 
				inner join v_PersonState PS  on PS.Person_id = PW.Person_id
				left join v_Post P  on P.Post_id = PW.Post_id
				-- end from
			where
				-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				\"Person_Fio\"
				-- end order by
		";
		return $callObject->getPagingResponse($query, $params, $data["start"], $data["limit"], true);
	}

	/**
	 * Получение списка согласий пациента для ЭМК
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadPersonLpuInfoPanel(Person_model $callObject, $data)
	{
		return $callObject->queryResult("
			select 
				'PersonLpuInfo' as \"PersonLpuInfoType\",
				PersonLpuInfo_id as \"PersonLpuInfo_id\",
				PersonLpuInfo_IsAgree as \"PersonLpuInfo_IsAgree\",
				PersonLpuInfo_setDate as \"PersonLpuInfo_setDate\",
				Lpu_Nick as \"Lpu_Nick\"
			from (
				select
					PersonLpuInfo_id,
					PersonLpuInfo_IsAgree,
					to_char(PersonLpuInfo_setDT, 'dd.mm.yyyy') as PersonLpuInfo_setDate,
					Lpu_Nick
				from	
					v_PersonLpuInfo pli
					left join v_Lpu l on l.Lpu_id = pli.Lpu_id
				where
					Person_id = :Person_id and pli.Lpu_id = :Lpu_id 
				order by PersonLpuInfo_id desc
				limit 1
			) as PLI_Last

			union all
			select  
				'ReceptElectronic' as \"PersonLpuInfoType\",
				ReceptElectronic_id as \"ReceptElectronic_id\",
				ReceptElectronic_IsAgree as \"ReceptElectronic_IsAgree\",
				ReceptElectronic_setDate as \"ReceptElectronic_setDate\",
				Lpu_Nick as \"Lpu_Nick\"
			from (
				select
					ReceptElectronic_id,
					case when ReceptElectronic_endDT is null or cast(ReceptElectronic_endDT as timestamp) > dbo.tzGetDate() then 2 else 1 end as ReceptElectronic_IsAgree,
					case when ReceptElectronic_endDT is null then to_char(ReceptElectronic_begDT, 'dd.mm.yyyy') else to_char(ReceptElectronic_begDT, 'dd.mm.yyyy') || ' - ' || to_char(ReceptElectronic_endDT, 'dd.mm.yyyy') end as ReceptElectronic_setDate,
					Lpu_Nick
				from
					v_ReceptElectronic re
					left join v_Lpu l on l.Lpu_id = re.Lpu_id
				where
					Person_id = :Person_id and re.Lpu_id = :Lpu_id 
				order by ReceptElectronic_id desc
				limit 1
			) as RE_Last
		", [
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id']
		]);
	}

	/**
	 * Метод для API. Получение списка согласий пациента для ЭМК
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadPersonLpuInfoPanelForAPI(Person_model $callObject, $data)
	{
		$filter = " PLI.Person_id = :Person_id ";
		$select = "";
		if (!empty($data["person_in"])) {
			$filter = " PLI.Person_id in ({$data["person_in"]}) ";
			$select = " ,PLI.Person_id ";
		}
		$query = "
			select
				PLI.PersonLpuInfo_id as \"PersonLpuInfo_id\",
				to_char(PLI.PersonLpuInfo_setDT, '{$callObject->dateTimeForm104}') as \"PersonLpuInfo_setDate\",
				PLI.PersonLpuInfo_IsAgree as \"PersonLpuInfo_IsAgree\",
				YN.YesNo_Name as \"PersonLpuInfo_IsAgreeText\",
				'На обработку персональных данных' as \"PersonLpuInfo_Type\"
				{$select}
			from
				v_PersonLpuInfo PLI
				left join v_YesNo YN  on YN.YesNo_id = PLI.PersonLpuInfo_IsAgree
			where {$filter}
		";
		$queryParams = ["Person_id" => $data["Person_id"]];
		return $callObject->queryResult($query, $queryParams);
	}
}