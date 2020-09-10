<?php

class Person_model_export
{
	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function exportPersonCardForIdentification(Person_model $callObject, $data)
	{
		set_time_limit(0);
		$filters = [];
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		if (!empty($data["soc_card_id"]) && mb_strlen($data["soc_card_id"]) >= 25) {
			$filters[] = "LEFT(ps.Person_SocCardNum, 19) = :SocCardNum";
			$queryParams["SocCardNum"] = mb_substr($data["soc_card_id"], 0, 19);
		}
		$fields = ["Person_SurName", "Person_FirName", "Person_SecName"];
		foreach ($fields as $field) {
			if (!empty($data[$field]) && $data[$field] != "_") {
				$filters[] = "ps.{$field} iLIKE :{$field}||'%'";
				$queryParams[$field] = rtrim($data[$field]);
			}
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
		if (!($data["PersonAge_From"] == 0 && $data["PersonAge_To"] == 200)) {
			$filters[] = "dbo.Age2(ps.Person_BirthDay,dbo.tzGetDate()) between :PersonAge_From and :PersonAge_To";
			$queryParams["PersonAge_From"] = $data["PersonAge_From"];
			$queryParams["PersonAge_To"] = $data["PersonAge_To"];
		}
		if (!empty($data["KLAreaType_id"]) || !empty($data["KLCountry_id"]) || !empty($data["KLRgn_id"]) || !empty($data["KLSubRgn_id"]) || !empty($data["KLCity_id"]) || !empty($data["KLTown_id"]) || !empty($data["KLStreet_id"]) || !empty($data["Address_House"])) {
			if ($data["AddressStateType_id"] == 1) {
				$fields = ["KLCountry_id", "KLRgn_id", "KLSubRgn_id", "KLCity_id", "KLTown_id", "KLStreet_id", "Address_House", "KLAreaType_id"];
				foreach ($fields as $field) {
					if (!empty($data[$field])) {
						$filters[] = "uaddr.{$field} = :{$field}";
						$queryParams[$field] = $data[$field];
					}
				}
			} else if ($data["AddressStateType_id"] == 2) {
				$fields = ["KLCountry_id", "KLRgn_id", "KLSubRgn_id", "KLCity_id", "KLTown_id", "KLStreet_id", "Address_House", "KLAreaType_id"];
				foreach ($fields as $field) {
					if (!empty($data[$field])) {
						$filters[] = "paddr.{$field} = :{$field}";
						$queryParams[$field] = $data[$field];
					}
				}
			} else {
				$fields = ["KLCountry_id", "KLRgn_id", "KLSubRgn_id", "KLCity_id", "KLTown_id", "KLStreet_id", "Address_House", "KLAreaType_id"];
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
			$queryParams["ReceptFinance_id"] = $data["RegisterSelector_id"];
			$filters[] = "
				exists (
					select PersonPrivilege_id
					from
						v_PersonPrivilege t1 
						inner join v_PrivilegeType t2 on t2.PrivilegeType_id = t1.PrivilegeType_id
					where t1.Person_id = ps.Person_id
					  and t2.ReceptFinance_id = :ReceptFinance_id
					  and t1.PersonPrivilege_begDate <= dbo.tzGetDate()
					  and (t1.PersonPrivilege_endDate is null or t1.PersonPrivilege_endDate >= dbo.tzGetDate()::date)
				)
			";
		}
		if (!empty($data["Refuse_id"])) {
			// Отказ от льготы
			$filters[] = ($data["Refuse_id"] == 1 ? "not " : "") . "exists (
					select PersonRefuse_IsRefuse
					from v_PersonRefuse 
					where Person_id = ps.Person_id
					  and PersonRefuse_IsRefuse = 2
					  and PersonRefuse_Year = date_part('YEAR',dbo.tzGetDate())
				)
			";
		}
		if (!empty($data["RefuseNextYear_id"])) {
			// Отказ от льготы на следующий год
			$filters[] = ($data["RefuseNextYear_id"] == 1 ? "not " : "") . "exists (
					select PersonRefuse_IsRefuse
					from v_PersonRefuse 
					where Person_id = ps.Person_id
					  and PersonRefuse_IsRefuse = 2
					  and PersonRefuse_Year = date_part('YEAR',dbo.tzGetDate()) + interval '1 day'
				)
			";
		}
		if (!empty($data["PersonCard_IsActualPolis"])) {
			// Есть действующий полис
			$filters[] = ($data["PersonCard_IsActualPolis"] == 1 ? "not " : "") . "exists (
					select Polis_id
					from v_Polis 
					where Polis_id = ps.Polis_id
					  and (Polis_endDate is null or Polis_endDate > dbo.tzGetDate()::date)
				)
			";
		}
		if (!empty($data["PersonCard_Code"]) || !empty($data["LpuRegion_id"]) || !empty($data["LpuRegionType_id"]) || !empty($data["LpuRegionType_id"]) || !empty($data["PersonCard_begDate"][0]) || !empty($data["PersonCard_begDate"][1]) || !empty($data["PersonCard_endDate"][0]) || !empty($data["PersonCard_endDate"][1]) || !empty($data["AttachLpu_id"]) || !empty($data["PersonCard_IsAttachCondit"]) || (!empty($data["PersonCardStateType_id"]) && $data["PersonCardStateType_id"] != 3)) {
			// Фильтры по прикреплению
			$personCardFilters = ["Person_id = ps.Person_id"];
			if (!empty($data["PersonCard_Code"])) {
				$personCardFilters[] = "PersonCard_Code = :PersonCard_Code";
				$queryParams["PersonCard_Code"] = $data["PersonCard_Code"];
			}
			if (!empty($data["LpuRegion_id"])) {
				if ($data["LpuRegion_id"] == -1) {
					$personCardFilters[] = "LpuRegion_id is null";
				} else {
					$personCardFilters[] = "LpuRegion_id = :LpuRegion_id";
					$queryParams["LpuRegion_id"] = $data["LpuRegion_id"];
				}
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
				$personCardFilters[] = "(PersonCard_endDate is null or PersonCard_endDate > dbo.tzGetDate())";
			}
			$PersonCardStateTypePrefix = (!empty($data["PersonCardStateType_id"]) && $data["PersonCardStateType_id"] == 1) ? "" : "_all";
			$personCardFiltersString = implode(" and ", $personCardFilters);
			$filters[] = "
				exists (
					select PersonCard_id
					from v_PersonCard{$PersonCardStateTypePrefix} 
					where {$personCardFiltersString}
				)
			";
		} else if ($callObject->regionNick == "ekb" && count($filters) > 1) {
			$filters[] = "
				exists (
					select PersonCard_id
					from v_PersonCard  
					where (PersonCard_endDate is null or PersonCard_endDate > dbo.tzGetDate())
				)
			";
		}
		if ($callObject->regionNick == "ekb" && count($filters) == 1) {
			$filters[] = "
				exists (
					select PersonCard_id
					from v_PersonCard  
					where Lpu_id = :Lpu_id
					  and (PersonCard_endDate is null or PersonCard_endDate > dbo.tzGetDate())
				)
			";
		}
		$filters_str = implode(" and ", $filters);
		if ($callObject->regionNick == "ekb") {
			$query = "
				-- addit with
				with PERS as (
					select
						PS.Person_id,
						PS.Person_BirthDay
					from
						v_PersonState PS
						left join Address uaddr on uaddr.Address_id = PS.UAddress_id
						left join Address paddr on paddr.Address_id = PS.PAddress_id
					where {$filters_str}
                    limit 100000
				),
				PERS1 as (
					select distinct
						PS.PersonEvn_id,
						PS.Server_id
					from
						PERS P
						left join v_PersonDeputy PD on PD.Person_id = P.Person_id
						left join lateral (
							select case when PD.Person_pid is not null and dbo.Age_newborn(P.Person_BirthDay, dbo.tzGetDate()) < 1 then 1 else 0 end as flag
						) as deputy on true
						inner join lateral (
							select *
							from v_PersonState 
							where Person_id in (P.Person_id, PD.Person_pid)
							order by case when (deputy.flag = 1 and Person_id = PD.Person_pid) or (deputy.flag = 0 and Person_id = P.Person_id) then 0 else 1 end
	                        limit 1
						) as PS on true
				)
				-- end addit with
				select
					-- select
					PS.Person_id as \"Person_id\",
					null as \"Evn_id\",
					null as \"CmpCallCard_id\",
					to_char(dbo.tzGetDate(), '{$callObject->dateTimeForm120}') as \"PersonIdentPackagePos_identDT\",
					to_char(dbo.tzGetDate(), '{$callObject->dateTimeForm120}') as \"PersonIdentPackagePos_identDT2\"
					-- end select
				from
					-- from
					PERS1 P
					inner join v_Person_all PS on PS.PersonEvn_id = P.PersonEvn_id and PS.Server_id = P.Server_id
					-- end from
				order by
					-- order by
					ps.Person_id
					-- end order by
			";
			$callObject->load->model("PersonIdentPackage_model");
			try {
				$callObject->PersonIdentPackage_model->beginTransaction();
				$stat = ["PackageCount" => 0, "PersonCount" => 0];
				$file_zip_name = $callObject->PersonIdentPackage_model->createCustomPersonIdentPackages($query, $queryParams, false, $stat);
				if ($stat["PersonCount"] == 0) {
					throw new Exception("Не найдены пациенты для экспорта");
				}
				$callObject->PersonIdentPackage_model->commitTransaction();
			} catch (Exception $e) {
				$callObject->PersonIdentPackage_model->rollbackTransaction();
				return $callObject->createError($e->getCode(), $e->getMessage());
			}
		} else {
			$query = "
				select
					-- select
					ps.Person_SurName as \"SName\",
				    ps.Person_FirName as \"Fi\",
				    coalesce(ps.Person_SecName, '-') as \"Si\",
				    to_char (ps.Person_BirthDay, 'YYYYMMDD') as \"BornDt\",
				    ps.Sex_id as \"Sex\",
				    to_char (dbo.tzGetDate(), 'YYYYMMDD') as \"EntrDt\",
				    to_char (dbo.tzGetDate(), 'YYYYMMDD') as \"ReleDt\"
					-- end select
				from
					-- from
					v_PersonState ps 
					left join Address uaddr on uaddr.Address_id = ps.UAddress_id
					left join Address paddr on paddr.Address_id = ps.PAddress_id
					-- end from
				where
					-- where
					{$filters_str}
					-- end where
				order by
					-- order by
					ps.Person_id
					-- end order by
				limit 100000
			";
			$out_dir = "ident_" . time();
			if (!file_exists(EXPORTPATH_PC)) {
				mkdir(EXPORTPATH_PC);
			}
			mkdir(EXPORTPATH_PC . $out_dir);
			$DBF = [
				["SName", "C", 40, 0],
				["Fi", "C", 40, 0],
				["Si", "C", 40, 0],
				["BornDt", "D", 8, 0],
				["Sex", "N", 1, 0],
				["EntrDt", "D", 8, 0],
				["ReleDt", "D", 8, 0]
			];
			$DBF_FILENAME = EXPORTPATH_PC . "{$out_dir}/QuerySCD.dbf";
			$h = dbase_create($DBF_FILENAME, $DBF);
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			if (is_object($result)) {
				$resp = $result->result_array();
				foreach ($resp as $row) {
					// засовываем в DBF-ку.
					array_walk($row, "ConvertFromUtf8ToCp866");
					dbase_add_record($h, array_values($row));
				}
			}
			dbase_close($h);
			// запаковываем DBF-ку
			$zip = new ZipArchive();
			$file_zip_name = EXPORTPATH_PC . "{$out_dir}/00" . date('dHi') . ".SCD";
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($DBF_FILENAME, "QuerySCD.dbf");
			$zip->close();
			unlink($DBF_FILENAME);
		}
		// отдаём пользователю то, что получилось
		if (!file_exists($file_zip_name)) {
			throw new Exception("Ошибка создания архива экспорта");
		}
		return [["Error_Msg" => "", "filename" => $file_zip_name]];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return bool|CI_DB_result
	 */
	public static function exportPersonPolisToXml(Person_model $callObject, $data)
	{
		$params = [
			"PersonPolis_Date" => $data["PersonPolis_Date"],
			"KLRgn_id" => $data["KLRgn_id"]
		];
		$bezrab_sysnick_str = "'child', 'child_yasli', 'child_doma', 'study', 'nrab', 'pen', 'bomzh', 'chwar'";
		$cross = "";
		$filter = "";
		if ($data["PersonPolis_Date"] == date("Y-m-d")) {
			// сегодня
			$table = "v_PersonState p";
		} else {
			$table = "v_Person P1";
			$filter = "and P1.Person_insDT <= :PersonPolis_Date";
			$cross = "
				inner join lateral (
					select 
						t.Person_id,
						t.Person_Snils,
						rtrim(t.Person_SurName) as Person_SurName,
						rtrim(t.Person_FirName) as Person_FirName,
						rtrim(t.Person_SecName) as Person_SecName,
						t.Person_BirthDay,
						t.Sex_id,
						t.Document_id,
						t.SocStatus_id,
						t.Polis_id,
						t.UAddress_id,
						t.Person_deadDT
					from v_Person_all t 
					where t.PersonEvn_insDT is not null
					  and t.PersonEvn_insDT <= :PersonPolis_Date
					  and t.Person_id = P1.Person_id
					order by t.PersonEvn_insDT desc
					limit 1
				) as P on true
			";
		}
		$selectString = "
				P.Person_id as \"person_id\", -- идентификатор застрахованного
				P.Person_Snils as \"snils\", -- СНИЛС
				P.Person_SurName as \"fam\", -- Фамилия
				P.Person_FirName as \"im\", -- Имя
				P.Person_SecName as \"ot\", -- Отчество
				to_char(P.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"dr\", -- Дата рождения
				(case
					when S.Sex_SysNick iLIKE 'woman' then 'Ж'
					when S.Sex_SysNick in ('man','issex') then 'М'
				end) as \"w\", -- Пол
				AB.Address_Address as \"address_r\", -- Адрес места рождения
				AU.Address_Zip as \"index\", -- Почтовый индекс места регистрации
				AU.Address_Address as \"address_reg\", -- Адрес места регистрации
				case when SC.SocStatus_SysNick in ({$bezrab_sysnick_str}) then 2 else 1 end as \"id_zl\", -- Отметка о статусе работающего лица
				SC.SocStatus_SysNick as \"SocStatus_SysNick\",
				DT.DocumentType_Name as \"name_doc\", -- Наименование документа, удостоверяющего личность
				D.Document_Ser as \"s_doc\", -- Серия документа
				D.Document_Num as \"n_doc\", -- Номер документа
				to_char(D.Document_begDate, '{$callObject->dateTimeForm104}') as \"data_doc \"-- Дата выдачи документа
		";
		$fromString = "
			{$table} 
			{$cross}
			inner join v_SocStatus SC on SC.SocStatus_id = P.SocStatus_id
			inner join v_Polis Polis on Polis.Polis_id = P.Polis_id
			inner join v_OMSSprTerr OST on OST.OMSSprTerr_id = Polis.OmsSprTerr_id
			inner join v_Sex S on S.Sex_id = P.Sex_id
			left join v_PersonBirthPlace PBP on PBP.Person_id = P.Person_id
			left join Address AB on AB.Address_id = PBP.Address_id
			left join Address AU on AU.Address_id = P.UAddress_id
			inner join v_Document D on D.Document_id = P.Document_id
			inner join v_DocumentType DT on DT.DocumentType_id = D.DocumentType_id
		";
		$whereString = "
				P.Person_deadDT is null
			{$filter}
			and Polis.Polis_begDate <= :PersonPolis_Date
			and (Polis.Polis_endDate is null or Polis.Polis_endDate > :PersonPolis_Date)
			and SC.SocStatus_SysNick in ({$bezrab_sysnick_str})
			and OST.KLRgn_id = :KLRgn_id
		";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		$callObject->db->query_timeout = 7200; // 2 часа
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function exportPersonProfData(Person_model $callObject, $data)
	{
		$db = $callObject->load->database('default', true); // получаем коннект к БД
		$db->close(); // коннект должен быть закрыт
		$db->char_set = "windows-1251"; // ставим правильную кодировку (файл выгружается в windows-1251)

		$filterList = [];
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"Year" => $data["Year"],
			"Month" => sprintf("%02d", $data["Month"])
		];
		$filterDispList = "
			and (
				case when EPLD.EvnPLDisp_IsFinish = 2
					then EPLD.EvnPLDisp_disDT
					else EPLD.EvnPLDisp_setDT
				end between (select PredPredDate from cte) and (select PredDate from cte) - interval '1 day'
			)
		";
		if (($data["Year"] == 2017 && $data["Month"] == 11) || $data["Month"] == 1) {
			// В первый месяц выгружаем всех
			$exportVariant = 1;
			$filterList[] = "pc.PersonCard_begDate <= (select Date from cte)";
			$filterList[] = "(pc.PersonCard_endDate > (select Date from cte) or pc.PersonCard_endDate is null)";
			if ($data["Year"] == 2017 && $data["Month"] == 11 && getRegionNick() == "kareliya") {
				$filterDispList = " and (EPLD.EvnPLDisp_setDT >= (select BegYear from cte) and EPLD.EvnPLDisp_disDT <= (select PredPredDateEnd from cte))";
			}
		} else {
			// В остальные месяцы тянем прикрепленных за предыдущий месяц
			$exportVariant = 2;
			$filterList[] = "pc.PersonCard_begDate between (select PredDate from cte) + interval '1 day' and (select Date from cte)";
			$filterList[] = "(pc.PersonCard_endDate > (select Date from cte) or pc.PersonCard_endDate is null)";
		}
		$callObject->load->model("EvnPLDispDop13_model", "EvnPLDispDop13_model");
		$dateX = $callObject->EvnPLDispDop13_model->getNewDVNDate();
		$personPrivilegeCodeList = $callObject->EvnPLDispDop13_model->getPersonPrivilegeCodeList("{$queryParams["Year"]}-01-01");
		$exportVariantString = ($exportVariant == 2) ? " and DispClass_1.EvnPLDispDop13_id is null" : "";
		if (!empty($dateX) && $dateX <= "{$queryParams["Year"]}-{$queryParams["Month"]}-01") {
			$dvnCondition = "
				when PS.Person_Age >= 40 {$exportVariantString} then 1
				when PS.Person_Age >= 18 and PS.Person_Age % 3 = 0 {$exportVariantString} then 1
			";
		} else {
			$dvnCondition = "
				when PS.Person_Age >= 21 and PS.Person_Age % 3 = 0 {$exportVariantString} then 1
			";
		}
		if(count($personPrivilegeCodeList) > 0){
			$personPrivilegeCodeListString = implode("','", $personPrivilegeCodeList);
			$whereString = "
				where t1.Person_id = PS.Person_id
				  and t1.PrivilegeType_Code in ('{$personPrivilegeCodeListString}')
			";
			$leftJoinLateralPP = "
				select t1.PersonPrivilege_id
				from v_PersonPrivilege t1 
				{$whereString}
			";
		} else {
			$leftJoinLateralPP = "select null as PersonPrivilege_id";
		}
		$filterListString = (count($filterList) > 0) ? "and " . implode(" and ", $filterList) : "";
		$query = "
			with cte as (
				select
					(:Year||:Month||'01')::timestamp as \"Date\",
					(:Year||:Month||'01')::timestamp - interval '1 month' AS PredDate,
					(:Year||:Month||'01')::timestamp - interval '2 month'	AS PredPredDate,
					(:Year||'01'||'01')::timestamp AS BegYear,
				    (:Year||:Month||'01')::timestamp - interval '1 month' - interval '1 day' AS PredPredDateEnd,
				    date_part('month',dbo.tzGetDate()) - interval '1 day') / 3 + 1 AS childDispKv,
					:Lpu_id AS Lpu_id,
					(:Year || '-12-31')::date AS DateEndYear
            )
			select
				1 as exportVariant as \"exportVariant\",
				SMO.Org_id as \"Org_id\",
				SMO.Orgsmo_f002smocod as \"SMO\",
				PS.Person_id as \"ID_PAC\", -- Уникальный в пределах МО идентификатор гражданина
				rtrim(upper(PS.Person_SurName)) as \"FAM\", -- Фамилия
				rtrim(upper(PS.Person_FirName)) as \"IM\", -- Имя
				COALESCE(rtrim(upper(case when replace(PS.Person_Secname, ' ', '') = '---' then '' else PS.Person_Secname end)), '') as \"OT\", -- Отчество
				case when PS.Sex_id = 3 then 1 else PS.Sex_id end as \"W\", -- Пол
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm120}') as \"DR\", -- Дата рождения
				PT.PolisType_CodeF008 as \"VPOLIS\",
				rtrim(case when PLS.PolisType_id = 4 then '' else PLS.Polis_Ser end) as \"SPOLIS\",
				rtrim(case when PLS.PolisType_id = 4 then PS.Person_EdNum else PLS.Polis_Num end) as \"NPOLIS\",
				to_char(PC.PersonCard_begDate, '{$callObject->dateTimeForm120}') as \"DATE\",
				case
					when (PC.PersonCardAttach_id is not null) then 2
					when (PC.PersonCardAttach_id is null and COALESCE(PC.PersonCard_IsAttachCondit, 1) = 2) then 1
					else 0
				end as \"SP_PRIK\",
				case
					when CCC.CardCloseCause_Code is null then 1
					when CCC.CardCloseCause_Code = 1 then 2
					when CCC.CardCloseCause_Code = 7 then 4
					when ADDRESSCHANGE.PersonUAddress_id is not null then 3 -- Выгружать, если с момента прикрепления к предыдущей МО адрес изменялся
					else 0
				end as \"T_PRIK\",
				right('00'||COALESCE(left(LPS.LpuSection_Code, 2), ''), 2) as \"KOD_PODR\",
				PC.LpuRegion_Name as \"NUM_UCH\",
				case
					when LRT.LpuRegionType_SysNick = 'ter' then 1
					when LRT.LpuRegionType_SysNick = 'ped' then 2
					when LRT.LpuRegionType_SysNick = 'vop' then 3
					when LRT.LpuRegionType_SysNick = 'feld' then 3
					else null
				end as \"TIP_UCH\",
				MEDSnils.Person_Snils as \"SNILS_VR\",
				COALESCE(DD.DISP, 0) as \"DISP\",
				case
					when DD.DISP is null then null
					when dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) < 3 then (select childDispKv from cte)
					else (date_part('MONTH', PS.Person_BirthDay) - 1) / 3 + 1
				end as \"DISP_KV\",
				PI.PersonInfo_InternetPhon as \"PHONE1\",
				null as \"PHONE2\",
				to_char (case
					when DD.DISP in (4, 5) and DispClass_2.EvnPLDispDop13_IsRefusal = 2 then DispClass_2.EvnPLDispDop13_setDT
					when DD.DISP in (1, 2) and DispClass_1.EvnPLDispDop13_IsRefusal = 2 then DispClass_1.EvnPLDispDop13_setDT
					when DD.DISP = 3 and DispClass_5.EvnPLDisp_IsRefusal = 2 then DispClass_5.EvnPLDisp_setDT
					when DD.DISP = 7 and DispClassChildSecond.EvnPLDispTeenInspection_IsRefusal = 2 then DispClassChildSecond.EvnPLDispTeenInspection_setDT
					when DD.DISP = 6 and DispClassChildFirst.EvnPLDispTeenInspection_IsRefusal = 2 then DispClassChildFirst.EvnPLDispTeenInspection_setDT
				end, '{$callObject->dateTimeForm120}') as \"REJECT_DATE\",
				null as \"DISP_FACT\",
				null as \"DATE_NPM\",
				null as \"DATE_OPM\",
				null as \"DISP2_NPM\"
			from
				v_PersonCard_all PC
				left join lateral (
					select Person_id, Server_pid, Polis_id, Person_EdNum, UAddress_id, Person_SurName, Person_FirName, Person_Secname, Sex_id, Person_BirthDay, Person_deadDT, dbo.Age2(Person_BirthDay, (select DateEndYear from cte)) as Person_Age
					from v_Person_all P 
					where P.Person_id = PC.Person_id
					  and P.PersonEvn_insDT::date <= (select Date from cte)
					order by P.PersonEvn_insDT desc, P.PersonEvn_id desc
                    limit 1
				) as PS on true
				left join lateral (
					select PersonInfo_InternetPhone
					from v_PersonInfo 
					where Person_id = PC.Person_id
					  and PersonInfo_InternetPhone is not null
                    limit 1
				) as PI on true
				left join lateral (
					select PersonPrivilegeWOW_id
					from v_PersonPrivilegeWOW 
					where Person_id = PS.Person_id
                    limit 1
				) as PPWOW on true
				left join lateral (
					{$leftJoinLateralPP}
                    limit 1
				) as PP ON true
				left join lateral (
					select EvnPLDispDop13_id, EvnPLDispDop13_IsFinish, EvnPLDispDop13_IsTwoStage, EvnPLDispDop13_IsRefusal, EvnPLDispDop13_setDT
					from v_EvnPLDispDop13
					where Person_id = PS.Person_id
					  and date_part('YEAR', EvnPLDispDop13_setDT) = :Year
					  and DispClass_id = 1
				) as DispClass_1 ON true
				left join lateral (
					select EvnPLDispDop13_id, EvnPLDispDop13_IsRefusal, EvnPLDispDop13_setDT
					from v_EvnPLDispDop13 
					where Person_id = PS.Person_id
					  and date_part('YEAR', EvnPLDispDop13_setDT) = :Year
					  and DispClass_id = 2
                    limit 1
				) DispClass_2 ON true
				left join lateral (
					select EvnPLDisp_id, EvnPLDisp_IsRefusal, EvnPLDisp_setDT
					from v_EvnPLDisp 
					where Person_id = PS.Person_id
					  and date_part('YEAR', EvnPLDisp_setDT) = :Year
					  and DispClass_id = 5
                    limit 1
				) DispClass_5 ON true
				left join lateral (
					select EvnPLDisp_id
					from v_EvnPLDisp 
					where Person_id = PS.Person_id
					  and DispClass_id = 5
					  and date_part('YEAR', EvnPLDisp_setDT) = date_part('YEAR', (select Date from cte)) - 1
                    limit 1
				) DispClass_5_LastYear ON true
				left join lateral (
					select EvnPLDispTeenInspection_id, EvnPLDispTeenInspection_IsFinish, EvnPLDispTeenInspection_IsTwoStage, EvnPLDispTeenInspection_IsRefusal, EvnPLDispTeenInspection_setDT
					from v_EvnPLDispTeenInspection 
					where Person_id = PS.Person_id
					  and date_part('YEAR', EvnPLDispTeenInspection_setDT) = :Year
					  and DispClass_id in (10)
                    limit 1
				) DispClassChildFirst ON true
				left join lateral (
					select EvnPLDispTeenInspection_id, EvnPLDispTeenInspection_IsRefusal, EvnPLDispTeenInspection_setDT
					from v_EvnPLDispTeenInspection 
					where Person_id = PS.Person_id
					  and date_part('YEAR', EvnPLDispTeenInspection_setDT) = :Year
					  and DispClass_id in (12)
                    limit 1
				) DispClassChildSecond ON true
				left join lateral (
					select 
						case
							when PPWOW.PersonPrivilegeWOW_id is not null and COALESCE(DispClass_1.EvnPLDispDop13_IsFinish, 1) = 2 and COALESCE(DispClass_1.EvnPLDispDop13_IsTwoStage, 1) = 2 and DispClass_2.EvnPLDispDop13_id is null then 5
							when PP.PersonPrivilege_id is not null and COALESCE(DispClass_1.EvnPLDispDop13_IsFinish, 1) = 2 and COALESCE(DispClass_1.EvnPLDispDop13_IsTwoStage, 1) = 2 and DispClass_2.EvnPLDispDop13_id is null then 4
							when COALESCE(DispClass_1.EvnPLDispDop13_IsFinish, 1) = 2 and COALESCE(DispClass_1.EvnPLDispDop13_IsTwoStage, 1) = 2 and DispClass_2.EvnPLDispDop13_id is null then 4
							when COALESCE(DispClassChildFirst.EvnPLDispTeenInspection_IsFinish, 1) = 2 and COALESCE(DispClassChildFirst.EvnPLDispTeenInspection_IsTwoStage, 1) = 2 and DispClassChildSecond.EvnPLDispTeenInspection_id is null then 7
							when PS.Person_Age >= 18 and PPWOW.PersonPrivilegeWOW_id is not null " . ($exportVariant == 2 ? "and DispClass_1.EvnPLDispDop13_id is null" : "") . " then 2
							when PS.Person_Age >= 18 and PP.PersonPrivilege_id is not null " . ($exportVariant == 2 ? "and DispClass_1.EvnPLDispDop13_id is null" : "") . "  then 1
							{$dvnCondition}
							when PS.Person_Age >= 18 and DispClass_5.EvnPLDisp_id is null " . ($exportVariant == 2 ? "and DispClass_5_LastYear.EvnPLDisp_id is null" : "") . "  then 3
							when PS.Person_Age < 18 " . ($exportVariant == 2 ? "and COALESCE(DispClassChildFirst.EvnPLDispTeenInspection_IsTwoStage, 1) = 1" : "") . "  then 6
							else 0
						end as DISP
				) DD ON true
				inner join v_Lpu L on L.Lpu_id = PC.Lpu_id
				inner join v_Polis PLS on PLS.Polis_id = PS.Polis_id
				inner join v_PolisType PT on PT.PolisType_id = PLS.PolisType_id
				inner join v_OrgSMO SMO on SMO.OrgSMO_id = PLS.OrgSmo_id and SMO.KLRgn_id = 10
				left join lateral (
					select CardCloseCause_id, PersonCard_begDate
					from v_PersonCard_all t 
					where t.Person_id = PC.Person_id
					  and t.PersonCard_id != PC.PersonCard_id
					  and t.PersonCard_endDate = PC.PersonCard_begDate
					order by t.PersonCard_begDate desc
                    limit 1
				) PCL ON true
				left join lateral (
					select pua.PersonUAddress_id
					from v_PersonUAddress pua 
					where pua.Person_id = pc.Person_id
					  and pua.PersonUAddress_insDate >= PCL.PersonCard_begDate
					  and pua.PersonUAddress_insDate <= (select Date from cte)
                    limit 1
				) ADDRESSCHANGE ON true
				left join v_CardCloseCause CCC on CCC.CardCloseCause_id = PCL.CardCloseCause_id
				left join Address A on A.Address_id = PS.UAddress_id
				left join lateral (
					select MedPers.Person_Snils
					from
						v_MedStaffRegion MSR 
						inner join v_MedPersonal MedPers on MedPers.MedPersonal_id = MSR.MedPersonal_id
						inner join v_MedStaffFact msf on msf.MedPersonal_id = MedPers.MedPersonal_id
					where MSR.LpuRegion_id = PC.LpuRegion_id
					  and MedPers.Person_Snils is not null
					  and msf.Lpu_id = (select Lpu_id from cte)
					  and (msf.WorkData_begDate is null or msf.WorkData_begDate::date <= (select Date from cte))
					  and (msf.WorkData_endDate is null or msf.WorkData_endDate::date >= (select Date from cte))
					  and (MSR.MedStaffRegion_begDate is null or MSR.MedStaffRegion_begDate::date <= (select Date from cte))
					  and (MSR.MedStaffRegion_endDate is null or MSR.MedStaffRegion_endDate::date >= (select Date from cte))
					order by MSR.MedStaffRegion_isMain desc
                    limit 1
				) as MEDSnils ON true
				left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegionType LRT on LRT.LpuRegionType_id = PC.LpuRegionType_id
				left join v_LpuSection LPS on LPS.LpuSection_id = LR.LpuSection_id
			where PC.LpuAttachType_id = 1
				and DD.DISP is not null
				and (PLS.Polis_endDate is null or PLS.Polis_endDate >= (select Date from cte))
				and ((PLS.PolisType_id = 4 and PS.Person_EdNum is not null) or (PLS.PolisType_id <> 4 and PLS.Polis_Num is not null))
				and PT.PolisType_CodeF008 is not null
				and PC.Lpu_id = (select Lpu_id from cte)
				and (PS.Person_deadDT is null or PS.Person_deadDT > (select Date from cte))
				{$filterListString}
			union all
			select
				2 as \"exportVariant\",
				SMO.Org_id as \"Org_id\",
				SMO.Orgsmo_f002smocod as \"SMO\",
				PS.Person_id as \"ID_PAC\", -- Уникальный в пределах МО идентификатор гражданина
				rtrim(upper(PS.Person_SurName)) as \"FAM\", -- Фамилия
				rtrim(upper(PS.Person_FirName)) as \"IM\", -- Имя
				COALESCE(rtrim(upper(case when replace(PS.Person_Secname, ' ', '') = '---' then '' else PS.Person_Secname end)), '') as \"OT\", -- Отчество
				case when PS.Sex_id = 3 then 1 else PS.Sex_id end as \"W\", -- Пол
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm120}') as \"DR\", -- Дата рождения
				PT.PolisType_CodeF008 as \"VPOLIS\",
				rtrim(case when PLS.PolisType_id = 4 then '' else PLS.Polis_Ser end) as \"SPOLIS\",
				rtrim(case when PLS.PolisType_id = 4 then PS.Person_EdNum else PLS.Polis_Num end) as \"NPOLIS\",
				to_char(PC.PersonCard_begDate, '{$callObject->dateTimeForm120}') as \"DATE\",
				case
					when (PC.PersonCardAttach_id is not null) then 2
					when (PC.PersonCardAttach_id is null and COALESCE(PC.PersonCard_IsAttachCondit, 1) = 2) then 1
					else 0
				end as \"SP_PRIK\",
				case
					when CCC.CardCloseCause_Code is null then 1
					when CCC.CardCloseCause_Code = 1 then 2
					when CCC.CardCloseCause_Code = 7 then 4
					when ADDRESSCHANGE.PersonUAddress_id IS NOT NULL then 3 -- Выгружать, если с момента прикрепления к предыдущей МО адрес изменялся
					else 0
				end as \"T_PRIK\",
				right('00'||COALESCE(left(LPS.LpuSection_Code, 2), ''), 2) as \"KOD_PODR\",
				LR.LpuRegion_Name as \"NUM_UCH\",
				case
					when LRT.LpuRegionType_SysNick = 'ter' then 1
					when LRT.LpuRegionType_SysNick = 'ped' then 2
					when LRT.LpuRegionType_SysNick = 'vop' then 3
					when LRT.LpuRegionType_SysNick = 'feld' then 3
					else null
				end as \"TIP_UCH\",
				MEDSnils.Person_Snils as \"SNILS_VR\",
				DD.\"DISP\",
				case
					when DD.DISP in (4, 5, 7) then (MONTH(COALESCE(EPLDF.EvnPLDisp_disDT, EPLD.EvnPLDisp_disDT)) - 1) / 3 + 1
					when dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) < 3 then (select childDispKv from cte)
					else (date_part('MONTH', PS.Person_BirthDay) - 1) / 3 + 1
				end as \"DISP_KV\",
				PI.PersonInfo_InternetPhon as \"PHONE1\",
				null as \"PHONE2\",
				case when EPLD.EvnPLDisp_IsRefusal = 2 then to_char(EPLD.EvnPLDisp_setDT, '{$callObject->dateTimeForm120}') else null end as \"REJECT_DATE\",
				case
					when EPLD.DispClass_id = 1 and PPWOW.PersonPrivilegeWOW_id is not null then 2
					when EPLD.DispClass_id = 1 then 1
					when EPLD.DispClass_id = 2 and PPWOW.PersonPrivilegeWOW_id is not null then 5
					when EPLD.DispClass_id = 2 then 4
					when EPLD.DispClass_id = 5 then 3
					when EPLD.DispClass_id in (6, 9, 10) then 6
					when EPLD.DispClass_id in (11, 12) then 7
				end as \"DISP_FACT\",
				to_char(EPLD.EvnPLDisp_setDT, '{$callObject->dateTimeForm120}') as \"DATE_NPM\",
				to_char(EPLD.EvnPLDisp_disDT, '{$callObject->dateTimeForm120}') as \"DATE_OPM\",
				case
					when IsTwoStageAdult.EvnPLDispDop13_IsTwoStage = 2 then 1
					when IsTwoStageChild.EvnPLDispTeenInspection_IsTwoStage = 2 then 1
					else 0
				end as \"DISP2_NPM\"
			from
				v_EvnPLDisp EPLD 
				left join lateral (
					select EvnPLDispDop13_IsTwoStage
					from EvnPLDispDop13 
					where EvnPLDisp_id = EPLD.EvnPLDisp_id
                    limit 1
				) IsTwoStageAdult ON true
				left join lateral (
					select EvnPLDispTeenInspection_IsTwoStage
					from EvnPLDispTeenInspection 
					where EvnPLDisp_id = EPLD.EvnPLDisp_id
                    limit 1
				) IsTwoStageChild ON true
				inner join v_Person_all PS on PS.PersonEvn_id = EPLD.PersonEvn_id and PS.Server_id = EPLD.Server_id
				inner join v_Polis PLS on PLS.Polis_id = PS.Polis_id
				inner join v_PolisType PT on PT.PolisType_id = PLS.PolisType_id
				inner join v_OrgSMO SMO on SMO.OrgSMO_id = PLS.OrgSmo_id and SMO.KLRgn_id = 10
				left join lateral (
					select PersonInfo_InternetPhone
					from v_PersonInfo 
					where Person_id = PS.Person_id
					  and PersonInfo_InternetPhone is not null
                    limit 1
				) PI ON true
				left join lateral (
					select PersonPrivilegeWOW_id
					from v_PersonPrivilegeWOW 
					where Person_id = PS.Person_id
                    limit 1
				) PPWOW ON true
				left join lateral (
					{$leftJoinLateralPP}
                    limit 1
				) PP ON true
				inner join lateral (
					select Lpu_id, PersonCard_id, LpuRegion_id, PersonCard_begDate, LpuRegionType_id, PersonCardAttach_id, PersonCard_IsAttachCondit
					from v_PersonCard_all 
					where Person_id = PS.Person_id
					  and LpuAttachType_id = 1
					  and PersonCard_begDate <= (select PredDate from cte)
					  and (PersonCard_endDate is null or PersonCard_endDate > (select PredDate from cte))
                    limit 1
				) PC ON true
				inner join v_Lpu L  on L.Lpu_id = PC.Lpu_id
				left join lateral (
					select
						CardCloseCause_id,
						PersonCard_begDate
					from v_PersonCard_all t 
					where t.Person_id = PS.Person_id
					  and t.PersonCard_id != PC.PersonCard_id
					  and t.PersonCard_endDate = PC.PersonCard_begDate
					order by t.PersonCard_begDate desc
                    limit 1
				) PCL ON TRUE
				left join lateral (
					select PersonUAddress_id
					from v_PersonUAddress
					where Person_id = PS.Person_id
					  and PersonUAddress_insDate >= PCL.PersonCard_begDate
					  and PersonUAddress_insDate <= (select PredDate from cte)
                    limit 1
				) ADDRESSCHANGE ON true
				left join v_CardCloseCause CCC on CCC.CardCloseCause_id = PCL.CardCloseCause_id
				left join Address A on A.Address_id = PS.UAddress_id
				left join lateral (
					select MedPers.Person_Snils
					from
						v_MedStaffRegion MSR
						inner join v_MedPersonal MedPers on MedPers.MedPersonal_id = MSR.MedPersonal_id
						inner join v_MedStaffFact msf on msf.MedPersonal_id = MedPers.MedPersonal_id
					where MSR.LpuRegion_id = PC.LpuRegion_id
					  and MedPers.Person_Snils is not null
					  and msf.Lpu_id = (select Lpu_id from cte)
					  and (msf.WorkData_begDate is null or msf.WorkData_begDate::date <= (select Date from cte))
					  and (msf.WorkData_endDate is null or msf.WorkData_endDate::date >= (select Date from cte))
					  and (MSR.MedStaffRegion_begDate is null or MSR.MedStaffRegion_begDate::date <= (select Date from cte))
					  and (MSR.MedStaffRegion_endDate is null or MSR.MedStaffRegion_endDate::date >= (select Date from cte))
					order by MSR.MedStaffRegion_isMain desc
                    limit 1
				) as MEDSnils ON true
				left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegionType LRT on LRT.LpuRegionType_id = PC.LpuRegionType_id
				left join v_LpuSection LPS on LPS.LpuSection_id = LR.LpuSection_id
				left join v_EvnPLDisp EPLDF on EPLDF.EvnPLDisp_id = EPLD.EvnPLDisp_fid and EPLD.DispClass_id in (2, 12) -- карта первого этапа
				left join lateral (
					select
						case
							when EPLD.DispClass_id = 1 and PPWOW.PersonPrivilegeWOW_id is not null and IsTwoStageAdult.EvnPLDispDop13_IsTwoStage = 2 then 5
							when EPLD.DispClass_id = 1 and IsTwoStageAdult.EvnPLDispDop13_IsTwoStage = 2 then 4
							when EPLD.DispClass_id = 1 and PPWOW.PersonPrivilegeWOW_id is not null then 2
							when EPLD.DispClass_id = 1 then 1
							when EPLD.DispClass_id = 2 and PPWOW.PersonPrivilegeWOW_id is not null then 5
							when EPLD.DispClass_id = 2 then 4
							when EPLD.DispClass_id = 5 then 3
							when EPLD.DispClass_id in (11, 12) then 7
							when EPLD.DispClass_id in (6, 9, 10) and IsTwoStageChild.EvnPLDispTeenInspection_IsTwoStage = 2 then 7
							when EPLD.DispClass_id in (6, 9, 10) then 6
						end as DISP
				) DD ON true
			where EPLD.Lpu_id = (select Lpu_id from cte)
			  and EPLD.DispClass_id in (1, 2, 5, 10, 12)
			  and DD.DISP is not null
			  {$filterDispList}
			  and (PLS.Polis_endDate is null or PLS.Polis_endDate >= (select Date from cte))
			  and ((PLS.PolisType_id = 4 and PS.Person_EdNum is not null) or (PLS.PolisType_id <> 4 and PLS.Polis_Num is not null))
			  and PT.PolisType_CodeF008 is not null
			order by exportVariant
		";
		/**@var CI_DB_result $result */
		$result = $db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$PERS = $result->result_array();
		if (!is_array($PERS) || count($PERS) == 0) {
			throw new Exception("Отсутствуют данные для выгрузки");
		}
		$response = [];
		$response["SMO"] = [];
		// Получаем данные МО
		$query = "
			select 
				L.Org_id as \"Org_id\",
				L.Lpu_f003mcod as \"CODE_MO\",
				PassT.PassportToken_tid as \"ID_MO\",
				to_char(dbo.tzGetDate(), '{$callObject->dateTimeForm120}') as \"DATA\"
			from
				v_Lpu L 
				left join fed.v_PassportToken PassT on PassT.Lpu_id = L.Lpu_id
			where L.Lpu_id = :Lpu_id
			limit 1
		";
		$result = $db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$ZGLV = $result->result_array();
		if (!is_array($ZGLV) || count($ZGLV) == 0) {
			throw new Exception("Ошибка при получении кода МО!");
		}
		$SMO = [];
		$SMO_PERS = [];
		foreach ($PERS as $row) {
			if (empty($row["DISP"])) {
				continue;
			}
			$smo_code = $row["SMO"];
			if (!empty($row["PHONE1"])) {
				$row["PHONE1"] = preg_replace("/[^\d]/ui", "", $row["PHONE1"]); // выпиливаем из телефона всё, кроме цифр
				$row["PHONE1"] = substr(trim($row["PHONE1"], "+"), 0, 11);
			}
			if (!in_array($smo_code, $SMO)) {
				$SMO[] = $smo_code;
			}
			if (!array_key_exists($smo_code, $SMO_PERS)) {
				$SMO_PERS[$smo_code] = [];
			}
			$row["DISP_FACT_LIST"] = [];
			if ($row["exportVariant"] == 1) {
				$SMO_PERS[$smo_code][$row["ID_PAC"]] = $row;
			} elseif (!array_key_exists($row["ID_PAC"], $SMO_PERS[$smo_code])) {
				$SMO_PERS[$smo_code][$row["ID_PAC"]] = $row;
			}
			unset($SMO_PERS[$smo_code][$row["ID_PAC"]]["DISP_FACT"]);
			unset($SMO_PERS[$smo_code][$row["ID_PAC"]]["DATE_NPM"]);
			unset($SMO_PERS[$smo_code][$row["ID_PAC"]]["DATE_OPM"]);
			unset($SMO_PERS[$smo_code][$row["ID_PAC"]]["DISP2_NPM"]);
			if (!empty($row["DISP_FACT"]) && empty($row["REJECT_DATE"])) {
				if ($row["DISP2_NPM"] == 0 && getRegionNick() == "kareliya") {
					if ($row["DISP_FACT"] == 1 && !empty($row["DATE_OPM"])) {
						$SMO_PERS[$smo_code][$row["ID_PAC"]]["DISP_FACT_LIST"][] = [
							"DISP_FACT" => $row["DISP_FACT"],
							"DATE_NPM" => $row["DATE_NPM"],
							"DATE_OPM" => $row["DATE_OPM"],
							"DISP2_NPM" => $row["DISP2_NPM"]
						];
					} else {
						$SMO_PERS[$smo_code][$row["ID_PAC"]]["DISP_FACT_LIST"][] = [
							"DISP_FACT" => $row["DISP_FACT"],
							"DATE_NPM" => $row["DATE_NPM"],
							"DATE_OPM" => $row["DATE_OPM"],
							"DISP2_NPM" => null
						];
					}
				} else {
					$SMO_PERS[$smo_code][$row["ID_PAC"]]["DISP_FACT_LIST"][] = [
						"DISP_FACT" => $row["DISP_FACT"],
						"DATE_NPM" => $row["DATE_NPM"],
						"DATE_OPM" => $row["DATE_OPM"],
						"DISP2_NPM" => $row["DISP2_NPM"]
					];
				}
			}
		}
		for ($i = 0; $i < count($SMO); $i++) {
			$smo_code = $SMO[$i];
			$item = [];
			$itemZGLV = $ZGLV;
			$itemZGLV[0]["SMO"] = $smo_code;
			$itemZGLV[0]["ZAP"] = count($SMO_PERS[$smo_code]);
			$item["PERS"] = $SMO_PERS[$smo_code];
			$item["ZGLV"] = $itemZGLV;
			unset($SMO_PERS[$smo_code]);
			$response["SMO_PERS"][] = $item;
			unset($item);
		}
		return $response;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function validateDocument(Person_model $callObject, $data)
	{
		$params = [
			"DocumentType_id" => $data["DocumentType_id"],
			"Person_id" => $data["Person_id"],
			"PersonEvn_id" => !empty($data["PersonEvn_id"]) ? $data["PersonEvn_id"] : null,
			"PersonEvn_insDT" => !empty($data["PersonEvn_insDT"]) ? $data["PersonEvn_insDT"] : "2000-01-01",
		];
		if (empty($params["DocumentType_id"])) {
			return [["success" => true]];
		}
		if (
			($callObject->regionNick == 'vologda' || $callObject->fromApi)
			&& !in_array($data["DocumentType_id"], [3, 9, 17, 19, 22])
			&& !empty($data["Document_begDate"])
		) {
			$query = "
				select to_char(Person_Birthday, '{$callObject->dateTimeForm120}') as \"Person_Birthday\"
				from v_PersonState 
				where Person_id = :Person_id
				limit 1  
			";
			$Person_Birthday = $callObject->getFirstResultFromQuery($query, $data);
			if ($Person_Birthday !== false && !empty($Person_Birthday) && getCurrentAge($Person_Birthday, $data['Document_begDate']) < 14) {
				throw new Exception("Дата выдачи документа должна соответствовать дате 14-летия пациента или должна быть позже. Укажите корректную дату выдачи и тип документа.", 163758);
			}
		}
		$query = "
			with cte as (
				select PD.PersonDocument_insDT AS dt
				from v_PersonDocument PD 
				where PD.PersonDocument_id = :PersonEvn_id
                limit 1
            )
            select 
				PD.PersonEvn_insDT as \"PersonEvn_insDT\",
				DT.DocumentType_Code as \"DocumentType_Code\",
				nationalityBefore.KLCountry_id as \"beforeKLCountry_id\",
				nationalityAfter.KLCountry_id as \"afterKLCountry_id\"
			from
				(select cast(:Person_id as bigint) as Person_id, (select dt from cte) as PersonEvn_insDT) as PD
				left join v_DocumentType DT  on DT.DocumentType_id = :DocumentType_id
				left join lateral(
					select 
						PNS.NationalityStatus_id,
						PNS.KLCountry_id
					from v_PersonNationalityStatus PNS 
					where PNS.Person_id = PD.Person_id
					  and PNS.PersonNationalityStatus_insDT <= PD.PersonEvn_insDT
					order by PNS.PersonNationalityStatus_insDT desc
                    limit 1
				) nationalityBefore ON true
				left join lateral(
					select 
						PNS.NationalityStatus_id,
						PNS.KLCountry_id
					from v_PersonNationalityStatus PNS 
					where PNS.Person_id = PD.Person_id
					  and PNS.PersonNationalityStatus_insDT >= PD.PersonEvn_insDT
					  and PNS.NationalityStatus_id <> nationalityBefore.NationalityStatus_id
					order by PNS.PersonNationalityStatus_insDT desc
                    limit 1
				) nationalityAfter ON true
                limit 1
		";
		$resp = $callObject->getFirstRowFromQuery($query, $params);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при получени данных гражданства, период которых пересекаются с периодом документа.");
		}
		if ($resp["DocumentType_Code"] == 22) {
			if (!empty($resp["beforeKLCountry_id"]) || !empty($resp["afterKLCountry_id"])) {
				if ($data["type"] == "upd") {
					throw new Exception("В заданном периоде указано Гражданство. Гражданство должно быть пустым. Удалите соответствующую периодику или измените даты начала.");
				} else if ($data["type"] == "ins") {
					//добавить пустую периодику гражданства
					$callObject->exceptionOnValidation = true;
					try {
						/** @var DateTime $PersonEvn_insDT */
						$PersonEvn_insDT = $resp["PersonEvn_insDT"];
						$funcParams = [
							"Person_id" => $data["Person_id"],
							"PersonEvnClass_id" => 23,
							"EvnType" => "NationalityStatus",
							"Date" => $PersonEvn_insDT->format("d.m.Y"),
							"Time" => $PersonEvn_insDT->format("H:i"),
							"pmUser_id" => $data["pmUser_id"],
							"Server_id" => $data["Server_id"],
						];
						$resp = $callObject->saveAttributeOnDate($funcParams);
					} catch (Exception $e) {
						throw new Exception($e->getMessage(), $e->getCode());
					}
					$callObject->exceptionOnValidation = false;
					if (!$callObject->isSuccessful($resp)) {
						return $resp;
					}
				}
			}
		}
		return [["success" => true]];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function validateNationalityStatus(Person_model $callObject, $data)
	{
		$params = [
			"KLCountry_id" => $data["KLCountry_id"],
			"Person_id" => $data["Person_id"],
			"PersonEvn_id" => !empty($data["PersonEvn_id"]) ? $data["PersonEvn_id"] : null,
			"PersonEvn_insDT" => !empty($data["PersonEvn_insDT"]) ? $data["PersonEvn_insDT"] : "2000-01-01",
		];
		if (empty($params["KLCountry_id"])) {
			return [["success" => true]];
		}
		$query = "
			with cte as (
				select PD.PersonDocument_insDT AS dt
				from v_PersonDocument PD 
				where PD.PersonDocument_id = :PersonEvn_id
                limit 1
			)
			select 
				documentBefore.DocumentType_Code as \"beforeDocumentType_Code\",
				documentAfter.DocumentType_Code as \"afterDocumentType_Code\"
			from
				(select cast(:Person_id as bigint) as Person_id, (select dt from cte) as PersonEvn_insDT) as PNS
				left join lateral (
					select 
						PD.Document_id,
						DT.DocumentType_Code
					from
						v_PersonDocument PD 
						left join v_DocumentType DT on DT.DocumentType_id = PD.DocumentType_id
					where PD.Person_id = PNS.Person_id
					  and COALESCE(PD.Document_begDate, PD.PersonDocument_insDT) <= PNS.PersonEvn_insDT
					order by COALESCE(PD.Document_begDate, PD.PersonDocument_insDT) desc
                    limit 1
				) documentBefore ON true
				left join lateral (
					select
						PD.Document_id,
						DT.DocumentType_Code
					from
						v_PersonDocument PD 
						left join v_DocumentType DT on DT.DocumentType_id = PD.DocumentType_id
					where PD.Person_id = PNS.Person_id
					  and COALESCE(PD.Document_begDate, PD.PersonDocument_insDT) >= PNS.PersonEvn_insDT
					  and PD.Document_id <> documentBefore.Document_id
					order by COALESCE(PD.Document_begDate, PD.PersonDocument_insDT) asc
                    limit 1
				) documentAfter ON true
                limit 1
		";
		$resp = $callObject->getFirstRowFromQuery($query, $params, true);
		if ($resp === false) {
			throw new Exception("Ошибка при получени данных документов, период которых пересекаются с периодом гражданства.");
		} else if (is_array($resp) && count($resp) > 0) {
			if ($resp["beforeDocumentType_Code"] == 22 || $resp["afterDocumentType_Code"] == 22) {
				throw new Exception("В заданном периоде указан документ лица без гражданства. Гражданство должно быть пустым.");
			}
		}
		return array(array("success" => true));
	}

	/**
	 * @param $res
	 * @throws Exception
	 */
	public static function ValidateInsertQuery($res)
	{
		$error_text = "";
		$arr = [];
		if (is_array($res)) {
			$arr = $res;
		}
		if (is_object($res)) {
			/**@var CI_DB_result $res */
			$arr = $res->result_array();
		}
		if (count($arr) > 0) {
			foreach ($arr as $rows) {
				$err = null;
				if (!empty($rows["ErrMsg"])) {
					$err = $rows["ErrMsg"];
				}
				if (!empty($rows["Error_Msg"])) {
					$err = $rows["Error_Msg"];
				}
				if ($err) {
					$err = addslashes($err);
					$error_text = "Произошла ошибка при сохранении данных. <p style=\"color: red\">{$err}</p>";
				}
			}
		} else {
			$error_text = "Непонятная ошибка при сохранении данных.";
		}
		if (!empty($error_text)) {
			throw new Exception($error_text);
		}
	}
}