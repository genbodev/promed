<?php

class Polka_PersonCard_model_common
{
	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadAttachedList(Polka_PersonCard_model $callObject, $data)
	{
		$filterList = [];
		$queryParams = [];
		if (!empty($data["AttachLpu_id"])) {
			$filterList[] = "PC.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["AttachLpu_id"];
		}
		$whereString = (count($filterList) > 0) ? "and " . implode(" and ", $filterList) : "";
		$query = "
			select
				PC.PersonCard_Code as \"ID_PAC\",
				rtrim(upper(PS.Person_SurName)) as \"FAM\",
				rtrim(upper(PS.Person_FirName)) as \"IM\",
				coalesce(rtrim(upper(case when replace(PS.Person_Secname, ' ', '') = '---' or PS.Person_Secname = '' then 'НЕТ' else PS.Person_Secname end)), 'НЕТ') as \"OT\",
				PS.Sex_id as \"W\",
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm120}') as \"DR\",
				PT.PolisType_CodeF008 as \"VPOLIS\",
				rtrim(case when PLS.PolisType_id = 4 then '' else PLS.Polis_Ser end) as \"SPOLIS\",
				rtrim(case when PLS.PolisType_id = 4 then PS.Person_EdNum else PLS.Polis_Num end) as \"NPOLIS\"
			from
				v_PersonState PS 
				inner join v_PersonCard PC  on PC.Person_id = PS.Person_id
				inner join v_Polis PLS  on PLS.Polis_id = ps.Polis_id
				inner join v_PolisType PT  on PT.PolisType_id = PLS.PolisType_id
			where PC.LpuAttachType_id = 1
			  and (PC.CardCloseCause_id is null or PC.CardCloseCause_id <> 4)
			  and (PLS.Polis_endDate is null or PLS.Polis_endDate > dbo.tzGetDate())
			  and (
				(PLS.PolisType_id = 4 and dbo.getRegion() <> 2 and PS.Person_EdNum is not null) or
				((PLS.PolisType_id <> 4 or dbo.getRegion() = 2) and PLS.Polis_Num is not null)
			  )
			  and PT.PolisType_CodeF008 is not null
			  {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$PERS = $result->result_array();
		if (!is_array($PERS) || count($PERS) == 0) {
			throw new Exception("Список выгрузки пуст!");
		}
		$ZGLV = [
			[
				"CODE_MO" => "",
				"SMO" => "",
				"ZAP" => 0
			]
		];
		// Получаем код МО
		if (!empty($data['AttachLpu_id'])) {
			$query = "
				select 
					Lpu_f003mcod as \"CODE_MO\",
				    null as SMO
				from v_Lpu
				where Lpu_id = :Lpu_id
				limit 1
			";
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			$ZGLV = $result->result_array();
			if (!is_array($ZGLV) || count($ZGLV) == 0) {
				throw new Exception("Ошибка при получении кода МО!");
			}
		}
		$data = [];
		$data["Error_Code"] = 0;
		$ZGLV[0]["ZAP"] = count($PERS);
		$data["PERS"] = $PERS;
		$data["ZGLV"] = $ZGLV;
		return $data;
	}

	/**
	 * Список прикрепленного населения к указанной СМО на указанную дату
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadAttachedListCSV(Polka_PersonCard_model $callObject, $data)
	{
		$callObject->_resetAttachedListCSV();
		$filterList = [];
		$queryParams = [];
		$callObject->csvFrameIsQuote = ($callObject->getRegionNick() == "pskov") ? true : false;
		if (!empty($data["AttachLpu_id"])) {
			$callObject->_createLpu_RegNum($data["AttachLpu_id"]);
			$filterList[] = "PC.Lpu_id = :AttachLpu_id";
			$queryParams["AttachLpu_id"] = $data["AttachLpu_id"];
		}
		// Если нужно выбрать от даты до текущей
		if (!empty($data["AttachPeriod"]) && $data["AttachPeriod"] == 2 && !empty($data["AttachPeriod_FromDate"])) {
			$filterList[] = "
				(
					(PC.PersonCard_begDate >= :AttachPeriod_FromDate AND PC.PersonCard_begDate <= :curDT) or
					(PC.PersonCard_endDate >= :AttachPeriod_FromDate AND PC.PersonCard_endDate <= :curDT)
				)
			";
			$queryParams["AttachPeriod_FromDate"] = $data["AttachPeriod_FromDate"];
		}
		$where = " PC.LpuAttachType_id = 1 ";
		if ($callObject->getRegionNick() == "pskov") {
			$where = " ((PC.LpuAttachType_id = 1 and PS.Person_IsBDZ = 0) or ((PC.LpuAttachType_id = 1 or PC.LpuAttachType_id = 2) and PS.Person_IsBDZ = 1)) ";
			array_push($filterList , '
                (
                    PLS.PolisType_id != 2
                )
            ');
		}
		$dataOrgSMO = $callObject->_getDataOrgSMO();
		$dataPolisType = $callObject->_getDataPolisType();
		$queryParams["curDT"] = $callObject->tzGetDate();

		$filter1 = (!empty($data["AttachLpu_id"])) ? "where PS.Lpu_id = :AttachLpu_id" : "";
		$filter2 = (count($filterList) > 0) ? "and " . implode(" and ", $filterList) : "";
		$query = "
			with UDocument as (
				select
					D.Document_id,
					DT.DocumentType_id,
					DT.DocumentType_Code,
					D.Document_begDate,
					D.OrgDep_id,
					Org.Org_Name
				from
					Document D
					inner join v_PersonState PS on D.Document_id = PS.Document_id
					left join DocumentType DT on DT.DocumentType_id = D.DocumentType_id
					left join v_OrgDep OD on D.OrgDep_id = OD.OrgDep_id
					left join v_Org Org on Org.Org_id = OD.Org_id
				{$filter1}
			),
			UPolis as (
				select
					Polis_id,
					OrgSMO_id,
					PolisType_id,
					Polis_Ser,
					Polis_Num,
					Polis_endDate
				from v_Polis PLS
			)
			select
				case when PLS.PolisType_id IN (1,3) and COALESCE(PLS.Polis_endDate, :curDT) >= :curDT then null else PS.Person_edNum end as \"Person_edNum\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_Secname as \"Person_Secname\",
				PS.Person_BirthDay as \"DR\",
				PS.Document_Ser as \"Document_Ser\",
				PS.Document_Num as \"Document_Num\",
				PS.Person_Snils as \"Person_Snils\",
				PS.Person_id as \"Person_id\",
				PS.Document_id as \"Document_id\",
				PC.PersonCardAttach_id as \"PersonCardAttach_id\",
				PC.PersonCard_begDate as \"PersonCard_begDate\",
				PC.PersonCard_endDate as \"PersonCard_endDate\",
				PC.Person_id as \"Person_id\",
				PC.Lpu_id as \"Lpu_id\",
				PC.LpuRegion_id as \"LpuRegion_id\",
				PC.LpuAttachType_id as \"LpuAttachType_id\",
				PC.CardCloseCause_id as \"CardCloseCause_id\",
				PLS.Polis_id as \"Polis_id\",
				PLS.PolisType_id as \"PolisType_id\",
				PLS.Polis_Ser as \"Polis_Ser\",
				PLS.Polis_Num as \"Polis_Num\",
				PLS.Polis_endDate as \"Polis_endDate\",
				PLS.OrgSMO_id as \"OrgSMO_id\",
				PToken.PassportToken_tid as \"PassportToken_tid\",
				LR.LpuRegion_Name as \"LpuRegion_Name\",
				PKind.code,
				PRDR.Person_Birthplace as \"Person_Birthplace\",
				D.DocumentType_id as \"DocumentType_id\",
				D.DocumentType_Code as \"DocumentType_Code\",
				D.Document_begDate as \"Document_begDate\",
				D.Org_Name as \"Org_Name\",
				L.Lpu_f003mcod as \"Lpu_f003mcod\",
				PersonCard_IsAttachCondit as \"PersonCard_IsAttachCondit\",
				MPSnils.Person_Snils as \"MedPersonal_Snils\"
			from 
				v_PersonState_all PS
				inner join v_PersonCard PC  on PC.Person_id = PS.Person_id
				left join UPolis PLS on PLS.Polis_id = PS.Polis_id
				left join UDocument D on D.Document_id = PS.Document_id
				left join lateral (
					SELECT PRDR.Person_Birthplace
					FROM 
						erz.v_PersonRequestDataResult PRDR 
						left join erz.v_PersonRequestData PRD  on PRD.Person_id = PS.Person_id
					where PRDR.PersonRequestData_id = PRD.PersonRequestData_id
					limit 1
				) as PRDR on true
				left join v_Lpu L on L.Lpu_id = PC.Lpu_id
				left join fed.v_PassportToken PToken on L.Lpu_id = PToken.Lpu_id
				left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuSection LS on LR.LpuSection_id = LS.LpuSection_id
				left join lateral (
					select 1 as PolisFormType_Code
				) PFT on true
				left join lateral (
					select MP.Person_Snils
					from
						v_MedPersonal MP
						inner join v_MedStaffRegion MSR  on MSR.LpuRegion_id = LR.LpuRegion_id and MSR.MedPersonal_id = MP.MedPersonal_id
					order by MSR.MedStaffRegion_endDate
					limit 1
				) as MPSnils on true
				left join lateral (
					select PK.code as code
					from
						v_MedPersonal MP 
						inner join v_MedStaffRegion MSR on MSR.LpuRegion_id = LR.LpuRegion_id and MSR.MedPersonal_id = MP.MedPersonal_id
						inner join v_MedStaffFact MSF on MSR.MedStaffFact_id = MSF.MedStaffFact_id
						inner join persis.v_PostKind PK on PK.id = MSF.PostKind_id
					limit 1
				) as PKind on true
			WHERE {$where}
				{$filter2}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!isset($result->result_id)) {
			return false;
		}
		$curDate = new DateTime();
		// Создаем и открываем файл CSV для записи
		$callObject->_doFopenCSV();
		// Создаем и открываем файл ошибок для записи
		$callObject->_doFopenError();
		$i_error = 0;
		$errors_person = [];
		$csv_person = [];
		while ($row = sqlsrv_fetch_array($result->result_id, SQLSRV_FETCH_ASSOC)) {
			$doContinue = false;
			if ($row["CardCloseCause_id"] == 4) {
				$doContinue = true;
			}
			if ($doContinue != true) {
				if (empty($row["Polis_id"])) {
					$doContinue = true;
				}
			}
			if ($doContinue != true) {
				if (!empty($row["Polis_endDate"]) && $row["Polis_endDate"] <= $curDate) {
					$doContinue = true;
				}
			}
			if ($doContinue != true) {
				if (!isset($dataPolisType[$row["PolisType_id"]])) {
					$doContinue = true;
				}
			}
			if ($doContinue != true) {
				$KLRgn_id = "";
				if (isset($dataOrgSMO[$row["OrgSMO_id"]]) && !empty($dataOrgSMO[$row["OrgSMO_id"]]) && isset($dataOrgSMO[$row["OrgSMO_id"]]["KLRgn_id"]) && !empty($dataOrgSMO[$row["OrgSMO_id"]]["KLRgn_id"])) {
					$KLRgn_id = $dataOrgSMO[$row["OrgSMO_id"]]["KLRgn_id"];
				}
				if ($KLRgn_id != $callObject->getRegionNumber() && empty($row["DocumentType_id"])) {
					$doContinue = true;
				}
			}
			if ($doContinue) {
				continue;
			}
			$data = $callObject->_processingRowToFile($row, $dataPolisType);
			if (!empty($row["MedPersonal_Snils"])) {
				unset($data["polis_info"]);
				if (isset($csv_person[$row["Person_id"]])) {
					continue;
				}
				$callObject->_putRowToFileCSV($data);
				$csv_person[$row["Person_id"]] = true;
			} else {
				if (isset($errors_person[$row["Person_id"]])) {
					continue;
				}
				$i_error += 1;
				$error = $callObject->_processingRowToFileError($data, $i_error);
				$callObject->_putRowToFileError($error);
				$errors_person[$row["Person_id"]] = true;
			}
		}
		unset($errors_person);
		if ($i_error != 0) {
			$callObject->_renameFileError($i_error);
		}
		$attached_list_dir = $callObject->_getDirExport();
		$attached_list_file_name = $callObject->_getFileNameCSV();
		$attached_list_file_path = $callObject->_getFilePathCSV();
		$callObject->_doFcloseCSV();
		$attached_list_errors_file_name = $callObject->_getFileNameError();
		$attached_list_errors_file_path = $callObject->_getFilePathError();
		$callObject->_doFcloseError();

		$callObject->_resetAttachedListCSV();
		$arrData = [
			"attached_list_dir" => $attached_list_dir,
			"attached_list_file_name" => $attached_list_file_name,
			"attached_list_file_path" => $attached_list_file_path,
			"attached_list_errors_file_name" => $attached_list_errors_file_name,
			"attached_list_errors_file_path" => $attached_list_errors_file_path
		];
		if ($callObject->csvFrameIsQuote) {
			$callObject->delDummyStrCharacters($arrData);
		}
		return $arrData;
	}

	/**
	 * Получение данных для редактирования заявления на прикрепление
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonCardAttachForm(Polka_PersonCard_model $callObject, $data)
	{
		$params = ["PersonCardAttach_id" => $data["PersonCardAttach_id"]];
		$query = "
			select 
				PCA.PersonCardAttach_id as \"PersonCardAttach_id\",
				PCA.Lpu_aid as \"Lpu_aid\",
				to_char(PCA.PersonCardAttach_setDate, '{$callObject->dateTimeForm104}') as \"PersonCardAttach_setDate\",
				LR.LpuRegion_id as \"LpuRegion_id\",
				LRT.LpuRegionType_id as \"LpuRegionType_id\",
				P.Person_id as \"Person_id\",
				GA.GetAttachmentCase_id as \"GetAttachmentCase_id\",
				GA.GetAttachment_IsCareHome as \"GetAttachment_IsCareHome\",
				GA.GetAttachment_Number as \"GetAttachment_Number\"
			from
				v_PersonCardAttach PCA 
				inner join lateral (
					select Object_sid
					from ObjectSynchronLog 
					where ObjectSynchronLogService_id = 2
					  and Object_Name = 'PersonCardAttach'
					  and Object_id = PCA.PersonCardAttach_id
					order by Object_setDT desc
					limit 1
				) as OSL_Attach on true
				inner join r101.v_GetAttachment GA on GA.GetAttachment_id = OSL_Attach.Object_sid
				inner join lateral (
					select Object_id
					from ObjectSynchronLog 
					where ObjectSynchronLogService_id = 2
					  and Object_Name = 'LpuRegion'
					  and Object_sid = GA.GetTerrService_id
					order by Object_setDT desc
					limit 1
				) as OSL_LpuRegion on true
				inner join v_LpuRegion LR on LR.LpuRegion_id = OSL_LpuRegion.Object_id
				inner join v_LpuRegionType LRT on LRT.Region_id = 101 and LRT.LpuRegionType_Code = GA.GetTerrServiceProfile_id::varchar
				inner join Person P on P.BDZ_id = GA.Person_id
			where PCA.PersonCardAttach_id = :PersonCardAttach_id
			limit 1
		";
		$result = $callObject->queryResult($query, $params);
		return $result;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonCardMedicalInterventGrid(Polka_PersonCard_model $callObject, $data)
	{
		$params = ["PersonCard_id" => $data["PersonCard_id"]];
		$query = "
			select
				COALESCE(PCMI.PersonCardMedicalIntervent_id, -1) as \"PersonCardMedicalIntervent_id\",
				MIT.MedicalInterventType_id as \"MedicalInterventType_id\",
				MIT.MedicalInterventType_Code as \"MedicalInterventType_Code\",
				MIT.MedicalInterventType_Name as \"MedicalInterventType_Name\",
				(CASE WHEN PCMI.PersonCardMedicalIntervent_id is null THEN 0 ELSE 1 END) as \"PersonMedicalIntervent_IsRefuse\"
			from
				v_MedicalInterventType MIT 
				left join lateral (
					select t.PersonCardMedicalIntervent_id
					from v_PersonCardMedicalIntervent t 
					where (t.PersonCard_id = :PersonCard_id or t.PersonCard_id is null)
					  and t.MedicalInterventType_id = MIT.MedicalInterventType_id
					limit 1
				) as PCMI on true
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPersonCardAttachGrid(Polka_PersonCard_model $callObject, $data)
	{
		$filters = [];
		$queryParams = [];
		if (!empty($data["Person_SurName"])) {
			$filters[] = "PS.Person_SurName ilike :Person_SurName||'%'";
			$queryParams["Person_SurName"] = rtrim($data["Person_SurName"]);
		}
		if (!empty($data["Person_FirName"])) {
			$filters[] = "PS.Person_FirName ilike :Person_FirName||'%'";
			$queryParams["Person_FirName"] = rtrim($data["Person_FirName"]);
		}
		if (!empty($data["Person_SecName"])) {
			$filters[] = "PS.Person_SecName ilike :Person_SecName||'%'";
			$queryParams["Person_SecName"] = rtrim($data["Person_SecName"]);
		}
		if (!empty($data["Lpu_id"])) {
			$filters[] = "(PCA.Lpu_id = :Lpu_id or PCA.Lpu_aid = :Lpu_id)";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		if (!empty($data["PersonCardAttachStatusType_id"])) {
			$filters[] = "PCAST.PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id";
			$queryParams["PersonCardAttachStatusType_id"] = $data["PersonCardAttachStatusType_id"];
		}
		$whereString = (count($filters) != 0) ? "where\n -- where \n". implode(" and ", $filters) ." -- end where\n" : "1 = 1";
		$query = "
			select
				-- select
				PCA.PersonCardAttach_id as \"PersonCardAttach_id\",
				to_char(cast(PCA.PersonCardAttach_setDate as timestamp), '{$callObject->dateTimeForm104}') as \"PersonCardAttach_setDate\",
				rtrim(coalesce(PS.Person_SurName, ''))||' '||rtrim(coalesce(PS.Person_FirName, ''))||' '||rtrim(coalesce(PS.Person_SecName, '')) as \"Person_Fio\",
				LPU_N.Lpu_Nick as \"Lpu_N_Nick\",
				LPU_O.Lpu_Nick as \"Lpu_O_Nick\",
				PCAST.PersonCardAttachStatusType_Name as \"PersonCardAttachStatusType_Name\"
				-- end select
			from
				-- from
				v_PersonCardAttach PCA 
				inner join v_PersonState as PS  on PS.Person_id = PCA.Person_id
				left join v_Lpu LPU_N on LPU_N.Lpu_id = PCA.Lpu_aid
				left join v_Lpu LPU_O on LPU_O.Lpu_id = PCA.Lpu_id
				left join lateral (
					select PersonCardAttachStatusType_id
					from v_PersonCardAttachStatus
					where PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PersonCardAttachStatus_id desc
					limit 1
				) as PCAS on true
				left join lateral (select * from v_PersonCardAttachStatusType PCAST where PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id limit 1) PCAST on true
				-- end from
				{$whereString}
			order by
				-- order by
				\"PersonCardAttach_setDate\" desc
				-- end order by
		";
		return $callObject->getPagingResponse($query, $queryParams, $data["start"], $data["limit"], true);
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function printAttachedList(Polka_PersonCard_model $callObject, $data)
	{
		$params = ["OrgSMO_id" => $data["OrgSMO_id"]];
		$query = "
            select OrgSMO_Name as \"OrgSMO_Name\"
            from v_OrgSmo
            where OrgSMO_id = :OrgSMO_id
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function ExportPCToDBF(Polka_PersonCard_model $callObject, $data)
	{
		$fields = "";
		$joins = "";
		if (getRegionNick() == "ufa") {
			$fields = "
				,att.LpuAttachType_Name as \"AttachType\"
				,case when pc.PersonCard_IsAttachCondit = 2 then 'Да' else 'Нет' end as \"Condit\"
			";
			$joins = "left join v_LpuAttachType att  on att.LpuAttachType_id = pc.LpuAttachType_id";
		}
		$sql = "
			select
				pc.PersonCardState_Code as \"PersonCardState_Code\",
				Person_Surname as \"Person_Surname\",
				Person_Firname as \"Person_Firname\",
				Person_Secname as \"Person_Secname\",
				date_part('year', Person_BirthDay)::varchar||right('0'||date_part('mm', Person_BirthDay)::varchar, 2)||right('0'||date_part('day', Person_BirthDay)::varchar,2) as \"Person_BirthDay\",
				rtrim(a.Address_Address) as \"UAddress_Address\",
				rtrim(a1.Address_Address) as \"PAddress_Address\",
				case when pol.PolisType_id = 4 then '' else pol.Polis_Ser end as \"Polis_Ser\",
				case when pol.PolisType_id = 4 then p.Person_EdNum else pol.Polis_Num end as \"Polis_Num\",
				OrgSmo_Name as \"OrgSmo_Name\", 
				LpuRegion_Name as \"LpuRegion_Name\",
				date_part('year', PersonCardState_begDate)::varchar||right('0'||date_part('mm', PersonCardState_begDate)::varchar, 2)||right('0'||date_part('day', PersonCardState_begDate)::varchar, 2) as \"PersonCard_begDate\",
				date_part('year', personcardstate_enddate)::varchar||right('0'||date_part('mm', personcardstate_enddate)::varchar, 2)||right('0'||date_part('day', personcardstate_enddate)::varchar, 2) as \"PersonCard_endDate\",
				ss.SocStatus_Code as \"SocStatus_Code\",
				ss.SocStatus_Name as \"SocStatus_Name\",
				case when p.Person_IsBDZ = 1 then 'Да' else 'Нет' end as \"BDZ\"
				{$fields}
			from
				PersonCardState pc
				inner join v_PersonState_all p  on p.person_id=pc.person_id
				left join SocStatus ss  on ss.SocStatus_id = p.SocStatus_id
				left join Polis pol  on p.Polis_id = pol.Polis_id
				left join v_OrgSmo os  on os.OrgSMO_id = pol.OrgSmo_id
				left join address a  on p.uaddress_id=a.address_id
				left join address a1  on p.paddress_id=a1.address_id
				left join v_lpuregion lr  on lr.LpuRegion_id=pc.LpuRegion_id
				{$joins}
			where pc.Lpu_id = :Lpu_id
			order by 
				Person_Surname,
				Person_Firname,
				Person_Secname
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return CI_DB_result
	 */
	public static function exportPersonAttaches(Polka_PersonCard_model $callObject, $data)
	{
		if (isset($data["ExportDateRange"][0])) {
			$begDate = $data["ExportDateRange"][0];
		}
		if (isset($data["ExportDateRange"][1])) {
			$endDate = $data["ExportDateRange"][1];
		}
		if (isset($data["AttachesLpu_id"])) {
			$AttachesLpu_id = $data["AttachesLpu_id"];
		}
		$query = "
			select
				coalesce(FAM, '') as \"FAM\",
				coalesce(IM, '') as \"IM\",
				coalesce(OT, '') as \"OT\",
				to_char(DR, '{$callObject->dateTimeForm104}') as \"DR\",
				W as \"W\",
				coalesce(SPOL, '') as \"SPOL\",
				coalesce(NPOL, '') as \"NPOL\",
				coalesce(Q, '') as \"Q\",
				coalesce(LPU, '') as \"LPU\",
				to_char(LPUDZ, '{$callObject->dateTimeForm104}') as \"LPUDZ\",
				to_char(LPUDT, '{$callObject->dateTimeForm104}') as \"LPUDT\",
				to_char(LPUDX, '{$callObject->dateTimeForm104}') as \"LPUDX\",
				LPUTP as \"LPUTP\",
				coalesce(OKATO, '') as \"OKATO\",
				coalesce(RNNAME, '') as \"RNNAME\",
				coalesce(NPNAME, '') as \"NPNAME\",
				coalesce(UL, '') as \"UL\",
				coalesce(DOM, '') as \"DOM\",
				coalesce(KORP, '') as \"KORP\",
				coalesce(KV, '') as \"KV\",
				coalesce(STATUS, '') as \"STATUS\",
				coalesce(STATUS, '') as \"ERR\",
				coalesce(STATUS, '') as \"RSTOP\"
			from r3.PersonCard_List({$AttachesLpu_id}, '{$begDate}', '{$endDate}')
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, []);
		return $result;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return bool|CI_DB_result
	 */
	public static function exportPersonCardForPeriod(Polka_PersonCard_model $callObject, $data)
	{
		$query = "
			select
				ps.Person_EdNum as \"Enp\",
				ps.Person_Surname as fam,
				ps.Person_Firname as im,
				ps.Person_Secname as ot,
				ps.Person_Birthday as dr,
				sx.Sex_fedid as w,
				dt.DocumentType_Code as doctype,
				d.Document_Ser as docser,
				d.Document_Num as docnum,
				ps.Person_Snils as snils,
				pt.PolisType_CodeF008 as vpolis,
				pls.Polis_Ser as spolis,
				pls.Polis_Num as npolis,
				l.Lpu_f003mcod as codmof,
				case 
					when pc.PersonCardAttach_id is not null then 2 -- по заявлению застрахованного лица
					else 1 -- по территориально-участковому принципу
				end as attach_type,
				pc.PersonCard_begDate as attach_dt_mo,
				null as detach_codmof,
				pc.PersonCard_endDate as detach_dt_mo,
				ccc.CardCloseCause_Code as detach_mo_cause,
				BCODE.AttributeValue_ValueString as cod_podr,
				LCODE.AttributeValue_ValueString as cod_otd,
				lr.LpuRegion_Name as cod_uch,
				null as cod_pun,
				msr.typeD as typed_vr1,
				msr.Person_Snils as snils_vr1,
				pc.PersonCard_begDate as attach_dt_vr1,
				BFCODE.AttributeValue_ValueString as cod_podr_f,
				LFCODE.AttributeValue_ValueString as cod_otd_f, 
				lrf.LpuRegion_Name as cod_uch_f,
				null as cod_pun_f,
				msrf.typeD as typed_vr2,
				msrf.Person_Snils as snils_vr2,
				case when pc.LpuRegion_fapid is not null then pc.PersonCard_begDate end as attach_dt_vr2,
				case when pc.LpuRegion_fapid is not null then pc.PersonCard_endDate end as detach_dt_vr2,
				ccc.CardCloseCause_Code as detach_vr2_cause
			from
				dbo.v_PersonCard_all pc 
				inner join dbo.v_PersonState ps  on ps.Person_id = pc.Person_id
				inner join dbo.v_Sex sx  on sx.Sex_id = ps.Sex_id
				inner join dbo.v_Lpu l  on pc.Lpu_id = l.Lpu_id
				inner join dbo.v_Document d  on d.Document_id = ps.Document_id
				inner join dbo.v_DocumentType dt  on dt.DocumentType_id = d.DocumentType_id
				inner join dbo.v_Polis pls  on pls.Polis_id = ps.Polis_id
				inner join dbo.v_PolisType pt  on pt.PolisType_id = pls.PolisType_id
				left join dbo.v_LpuRegion lr  on lr.LpuRegion_id = pc.LpuRegion_id
				left join dbo.v_LpuRegionType lrt  on lrt.LpuRegionType_id = pc.LpuRegionType_id
				left join lateral (
					select AV.AttributeValue_ValueString
					from
						v_AttributeValue AV
						inner join v_AttributeSignValue ASV on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						inner join v_AttributeSign AS1 on AS1.AttributeSign_id = ASV.AttributeSign_id
						inner join v_Attribute A on A.Attribute_id = AV.Attribute_id
					where AS1.AttributeSign_TableName ilike 'dbo.LpuSection'
					  and ASV.AttributeSignValue_TablePKey = lr.LpuSection_id
					  and AS1.AttributeSign_id = 1
					  and a.Attribute_SysNick = 'Section_Code'
					  limit 1
				) as LCODE on true
				left join lateral (
					select AV.AttributeValue_ValueString
					from
						v_AttributeValue AV 
						inner join v_AttributeSignValue ASV  on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						inner join v_AttributeSign AS1 on AS1.AttributeSign_id = ASV.AttributeSign_id
						inner join v_Attribute A  on A.Attribute_id = AV.Attribute_id
					where AS1.AttributeSign_TableName ilike 'dbo.LpuSection'
					  and ASV.AttributeSignValue_TablePKey = lr.LpuSection_id
					  and AS1.AttributeSign_id = 1
					  and a.Attribute_SysNick = 'Building_Code'
					limit 1
				) as BCODE on true
				left join dbo.v_CardCloseCause ccc on pc.CardCloseCause_id = ccc.CardCloseCause_id
				left join dbo.v_LpuRegion lrf on lrf.LpuRegion_id = pc.LpuRegion_fapid
				left join lateral (
					select AV.AttributeValue_ValueString
					from
						v_AttributeValue AV
						inner join v_AttributeSignValue ASV  on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						inner join v_AttributeSign AS1 on AS1.AttributeSign_id = ASV.AttributeSign_id
						inner join v_Attribute A  on A.Attribute_id = AV.Attribute_id
					where AS1.AttributeSign_TableName ilike 'dbo.LpuSection'
					  and ASV.AttributeSignValue_TablePKey = lrf.LpuSection_id
					  and AS1.AttributeSign_id = 1
					  and a.Attribute_SysNick = 'Section_Code'
					limit 1
				) as LFCODE on true
				left join lateral (
					select AV.AttributeValue_ValueString
					from
						v_AttributeValue AV 
						inner join v_AttributeSignValue ASV  on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						inner join v_AttributeSign AS1 on AS1.AttributeSign_id = ASV.AttributeSign_id
						inner join v_Attribute A  on A.Attribute_id = AV.Attribute_id
					where AS1.AttributeSign_TableName ilike 'dbo.LpuSection'
					  and ASV.AttributeSignValue_TablePKey = lrf.LpuSection_id
					  and AS1.AttributeSign_id = 1
					  and a.Attribute_SysNick = 'Building_Code'
					limit 1
				) as BFCODE on true		
				left join lateral (
					select 
						p.Person_Snils,
						case when ms.MedSpec_pid = 196 then 2 when COALESCE(ms.MedSpec_pid, 0) <> 196 then 1 end as typeD
					from
						v_MedStaffRegion MedStaffRegion 
						inner join v_MedStaffFact MedStaffFact  on MedStaffFact.MedStaffFact_id = MedStaffRegion.MedStaffFact_id
						left join MedSpecOms mso on mso.MedSpecOms_id = MedStaffFact.MedSpecOms_id
						left join fed.MedSpec ms on ms.MedSpec_id = mso.MedSpec_id 
						inner join v_PersonState p on p.Person_id = MedStaffFact.Person_id 
					where MedStaffFact.Lpu_id = pc.Lpu_id
					  and MedStaffRegion.LpuRegion_id = pc.LpuRegion_id
					  and cast(MedStaffRegion.MedStaffRegion_begDate as date) <= :ExportDateRange_1
					  and coalesce(MedStaffRegion.MedStaffRegion_endDate, :ExportDateRange_1) >= :ExportDateRange_0   
					order by
						MedStaffRegion.MedStaffRegion_isMain desc,
						MedStaffRegion.MedStaffRegion_begDate desc
					limit 1
				) as msr on true
				left join lateral (
					select 
						p.Person_Snils,
						2 as typeD
					from
						v_MedStaffRegion MedStaffRegion
						inner join v_MedStaffFact MedStaffFact  on MedStaffFact.MedStaffFact_id = MedStaffRegion.MedStaffFact_id
						left join MedSpecOms mso on mso.MedSpecOms_id = MedStaffFact.MedSpecOms_id
						left join fed.MedSpec ms on ms.MedSpec_id = mso.MedSpec_id and ms.MedSpec_pid = 196
						inner join v_PersonState p on p.Person_id = MedStaffFact.person_id
					where MedStaffFact.Lpu_id = pc.Lpu_id
					  and MedStaffRegion.LpuRegion_id = pc.LpuRegion_fapid
					  and MedStaffRegion.MedStaffRegion_begDate::date <= :ExportDateRange_1
					  and coalesce(MedStaffRegion.MedStaffRegion_endDate, :ExportDateRange_1) >= :ExportDateRange_0   
					order by
						MedStaffRegion.MedStaffRegion_isMain desc,
						MedStaffRegion.MedStaffRegion_begDate desc
					limit 1
				) as msrf on true
			where pc.LpuAttachType_id = 1
			  and pc.PersonCard_begDate between :ExportDateRange_0 and :ExportDateRange_1
			  and l.Lpu_id = :Lpu_id
		";
		$queryParams = [
			"ExportDateRange_0" => $data["ExportDateRange"][0],
			"ExportDateRange_1" => $data["ExportDateRange"][1],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $sStreet
	 * @param $sHouse
	 * @param $Lpu_id
	 * @return array
	 */
	public static function FindAddressRegionsIDByAddress(Polka_PersonCard_model $callObject, $sStreet, $sHouse, $Lpu_id)
	{
		$arRegions = [];
		$sql = "
			select
				LpuRegionStreet_HouseSet as \"LpuRegionStreet_HouseSet\",
				LpuRegion_id as \"LpuRegion_id\"
			from LpuRegionStreet 
			where LpuRegion_id in (select LpuRegion_id from v_LpuRegion  where Lpu_id = :Lpu_id)
			  and (KLStreet_id = :Street or :Street = '')
		";
		$queryParams = [
			"Lpu_id" => $Lpu_id,
			"Street" => $sStreet
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (is_object($result)) {
			$res = $result->result_array();
			foreach ($res as $row) {
				if (($sHouse == "") || HouseMatchRange(trim($sHouse), trim($row["LpuRegionStreet_HouseSet"]))) {
					$arRegions[] = $row["LpuRegion_id"];
				}
			}
		}
		return $arRegions;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $Person_id
	 * @param $Lpu_id
	 * @return array
	 */
	public static function FindAddressRegionsIDByPersonCard(Polka_PersonCard_model $callObject, $Person_id, $Lpu_id)
	{
		$arRegions = [];
		$sql = "
			select LpuRegion_id as \"LpuRegion_id\"
			from v_PersonCard 
			where Person_id = :Person_id
			  and Lpu_id = :Lpu_id
			  and LpuRegion_id is not null
		";
		$queryParams = [
			"Person_id" => $Person_id,
			"Lpu_id" => $Lpu_id
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (is_object($result)) {
			$res = $result->result_array();
			foreach ($res as $row) {
				if (isset($row["LpuRegion_id"])) {
					$arRegions[] = $row["LpuRegion_id"];
				}
			}
		}
		return $arRegions;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function closePersonCard(Polka_PersonCard_model $callObject, $data)
	{
		// проверяем на заявку
		if (!isset($data["cancelDrugRequestCheck"]) || ($data["cancelDrugRequestCheck"] != 2)) {
			$sql = "
				select count(*) as cnt
				from
					v_DrugRequestRow DRR 
					inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id
						and DR.Lpu_id = :Lpu_id
					    and DRR.Person_id = (
					    	select Person_id
							from v_PersonCard 
					    	where PersonCard_id = :PersonCard_id
							  and Lpu_id = :Lpu_id
					    	limit 1
					    )
					inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
					left join lateral (
						select MAX(DrugRequestPeriod_begDate) max_date
						from DrugRequestPeriod 
					) as DRP_MD on true
				where DRP.DrugRequestPeriod_begDate = DRP_MD.max_date
			";
			$sqlParams = [
				"PersonCard_id" => $data["PersonCard_id"],
				"Lpu_id" => $data["Lpu_id"]
			];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$res = $result->result_array();
				if ($res[0]["cnt"] != 0) {
					return [[
						"success" => false,
						"Error_Msg" => "На данного пациента заявлены медикаменты в Вашем ЛПУ. Возможно после открепления пациент централизованно вновь будет условно прикреплен к Вашей ЛПУ. Открепить пациента?",
						"Error_Code" => 666
					]];
				}
			}
		}
		$sql = "
			select
				rtrim(pc.PersonCard_Code) as \"PersonCard_Code\",
				pc.Person_id as \"Person_id\",
				pc.LpuAttachType_id as \"LpuAttachType_id\",
				pc.LpuRegionType_id as \"LpuRegionType_id\",
   				to_char(pc.PersonCard_begDate, '{$callObject->dateTimeFormUnixDate}') as \"PersonCard_begDate\",
   				to_char(pc.PersonCard_endDate, '{$callObject->dateTimeFormUnixDate}') as \"PersonCard_endDate\",
				pc.CardCloseCause_id as \"CardCloseCause_id\",
				pc.Lpu_id as \"Lpu_id\",
				pc.LpuRegion_id as \"LpuRegion_id\",
				case when pl.Polis_begDate <= dbo.tzGetDate() and (pl.Polis_endDate is null or pl.Polis_endDate > dbo.tzGetDate()) then 'true' else 'false' end as \"Person_HasPolis\",
				case when ps.Person_deadDT is null then 'false' else 'true' end as \"Person_IsDead\",
				pc.PersonCardAttach_id as \"PersonCardAttach_id\",
				lat.LpuAttachType_SysNick as \"LpuAttachType_SysNick\",
				ccc.CardCloseCause_SysNick as \"CardCloseCause_SysNick\"
			from
				v_PersonCard pc 
				left join v_PersonState ps on ps.Person_id = pc.Person_id
				left join v_Polis pl on pl.Polis_id = ps.Polis_id
			    left join v_LpuAttachType lat  on lat.LpuAttachType_id = pc.LpuAttachType_id
				left join v_CardCloseCause ccc  on ccc.CardCloseCause_id = pc.CardCloseCause_id
			where pc.PersonCard_id = :PersonCard_id
			  and pc.Lpu_id = :Lpu_id
		";
		$sqlParams = [
			"PersonCard_id" => $data["PersonCard_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result_array();
		if (count($res) == 0) {
			throw new Exception("В вашем ЛПУ нет этой карты.");
		}
        $close_date = date('Ymd');

        //закрытие активных льгот
        if ($res[0]['LpuAttachType_SysNick'] == 'main' && $res[0]['CardCloseCause_SysNick'] == 'deregister') { //если прикрепление основное и причина закрытия "Изменение регистрации (выезд в другой регион)", то закрываем все активные льготы пациента
            $callObject->load->model('Privilege_model', 'Privilege_model');
            $priv_close_result = $callObject->Privilege_model->closeAllActivePrivilegesForPerson(array(
                'Person_id' => $res[0]['Person_id'],
                'PersonPrivilege_endDate' => $close_date,
                'PrivilegeCloseType_id' => $callObject->getObjectIdByCode('PrivilegeCloseType', '2') //2 - Переезд в другой регион
            ));
            if (!empty($priv_close_result['Error_Msg'])) {
                return [['success '=> false, 'Error_Msg' => !empty($priv_close_result['Error_Msg']) ? $priv_close_result['Error_Msg'] : 'При закрытии льго произошла ошибка']];
            }
        }

		$queryParams = [
			"PersonCard_id" => $data["PersonCard_id"],
			"Lpu_id" => $data["Lpu_id"],
			"Server_id" => $data["Server_id"],
			"Person_id" => $res[0]["Person_id"],
			"PersonCard_begDate" => $res[0]["PersonCard_begDate"],
			"PersonCard_endDate" => $close_date,
			"PersonCard_Code" => $res[0]["PersonCard_Code"],
			"PersonCardAttach_id" => $res[0]["PersonCardAttach_id"],
			"LpuRegion_id" => $res[0]["LpuRegion_id"],
			"LpuAttachType_id" => $res[0]["LpuAttachType_id"],
			"CardCloseCause_id" => ($res[0]["Person_IsDead"] == "true") ? 2 : 5,
			"pmUser_id" => $data["pmUser_id"]
		];
		$sql = "
            select
            	PersonCard_id as \"PersonCard_id\",
            	Error_Code as \"Error_Code\",
            	Error_Message as \"Error_Msg\"
            from p_PersonCard_upd(
	        	PersonCard_id := :PersonCard_id, 
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				Person_id := :Person_id,
				PersonCard_begDate := :PersonCard_begDate,
				PersonCard_endDate := :PersonCard_endDate,
				PersonCard_Code := :PersonCard_Code,
				PersonCardAttach_id := :PersonCardAttach_id,
				LpuRegion_id := :LpuRegion_id,
				LpuAttachType_id := :LpuAttachType_id,
				CardCloseCause_id := :CardCloseCause_id,
				PersonCard_IsAttachCondit := null,
				PersonCard_IsAttachAuto := null,
				PersonCard_AttachAutoDT := null,
				pmUser_id := :pmUser_id
	        )
		";
		$result = $callObject->db->query($sql, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function allowAddPrivilegeChild(Polka_PersonCard_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		$params = ["Person_id" => $data["Person_id"]];
		if ($callObject->getRegionNick() == "perm") {
			$query = "
				select Person_id as \"Person_id\"
				from v_PersonState p 
				where Person_id = :Person_id
				  and Server_pid > 0
				limit 1
			";
			$result = $callObject->db->query($query, $params);
			if (!is_object($result)) {
				return false;
			}
			$res = $result->result_array();
			if (is_array($res) && count($res) > 0 && !empty($res[0]["Person_id"])) {
				return false;
			}
		}
		$query = "
			select PersonCard_id as \"PersonCard_id\"
			from v_PersonCard 
			where Person_id = :Person_id
			limit 1
		";
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result_array();
		if (is_array($res) && count($res) > 0 && !empty($res[0]["PersonCard_id"])) {
			return false;
		}
		$query = "
			select pp.PersonPrivilege_id as \"PersonPrivilege_id\"
			from
				v_PersonPrivilege pp 
				inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
			where pp.Person_id = :Person_id
			  and pt.PrivilegeType_SysNick = 'child_und_three_year'
			limit 1
		";
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result_array();
		return !(is_array($res) && count($res) > 0 && !empty($res[0]['PersonPrivilege_id']));
	}
}