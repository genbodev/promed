<?php

class CmpCallCard_model_get
{
	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCmpCallCardSmpInfo(CmpCallCard_model $callObject, $data)
	{
		$callObject->RefuseOnTimeout($data);
		$callObject->unlockCmpCallCard($data);
		if (empty($data["CmpCallCard_id"])) {
			return false;
		}
		$queryParams["CmpCallCard_id"] = $data["CmpCallCard_id"];
		$selectString = "
			CCC.CmpCallCard_id as \"CmpCallCard_id\",
			CLC.CmpCloseCard_id as \"CmpCloseCard_id\",
			PS.Person_id as \"Person_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			PS.Server_id as \"Server_id\",
			coalesce(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\",
			coalesce(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\",
			coalesce(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\",
			coalesce(CCC.Person_Age, 0) as \"Person_Age\",
			CCC.pmUser_insID as \"pmUser_insID\",
			to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
			CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
			CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
			'<img src=\" ../img / grid / lock . png\">'||coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
			to_char(coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
			rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
			rtrim(coalesce(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as \"CmpLpu_Name\",
			rtrim(coalesce(CD.CmpDiag_Code, '')) as \"CmpDiag_Name\",
			rtrim(coalesce(D.Diag_Code, '')) as \"StacDiag_Name\",
			CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
			SLPU.Lpu_Nick as \"SendLpu_Nick\",
			ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
			CCC.Lpu_id as \"Lpu_id\",
			1 as \"CmpCallCard_isLocked\",
			coalesce(RGN.KLRgn_FullName, '')||
				case when SRGN.KLSubRgn_FullName is not null then ', '||SRGN.KLSubRgn_FullName else ', г.'||City.KLCity_Name end||
				case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
				case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
				case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end||
				case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end||
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end||
				case when CCC.CmpCallCard_Comm is not null then '</br>'||CCC.CmpCallCard_Comm else ''
			end as Adress_Name,
		    case when CCC.CmpCallCardStatusType_id = 1 and CCC.Lpu_ppdid is not null
				then to_char(
				    (select coalesce((select DS.DataStorage_Value from DataStorage DS where DS.DataStorage_Name = 'cmp_waiting_ppd_time' and DS.Lpu_id = 0 limit 1), 20)) - (DATEDIFF('mi', CCC.CmpCallCard_updDT, tzgetdate())||' minutes')::interval,
				    '{$callObject->dateTimeForm108}'
				)
				else '00'||':'||'00'
			end as \"PPD_WaitingTime\",
			case
				when CCC.CmpCallCardStatusType_id = 3 then
					case
						when coalesce(CCCStatusHist.CmpMoveFromNmpReason_id, 0) = 0 then CCC.CmpCallCardStatus_Comment
						else CCCStatusHist.CmpMoveFromNmpReason_Name
					end
				when CCC.CmpCallCardStatusType_id = 5 then
					CCC.CmpCallCardStatus_Comment
				when CCC.CmpCallCardStatusType_id = 4 then
					case when EPLD.diag_FullName is not null then 'Диагноз: '||EPLD.diag_FullName else '' end||
					case when RC.ResultClass_Name is not null then '<br />Результат: '||RC.ResultClass_Name else '' end||
					case when DT.DirectType_Name is not null then '<br />Направлен: '||DT.DirectType_Name else '' end
			end	as \"PPDResult\",
			to_char(ServeDT.ServeDT, '{$callObject->dateTimeForm104}') as \"ServeDT\",
			case when CCC.CmpCallCardStatusType_id in (2, 3, 4) then PMC.PMUser_Name||to_char(CCC.CmpCallCard_updDT, '{$callObject->dateTimeForm104}') else '' end as \"PPDUser_Name\",
			case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
				then
					case
						when CCC.CmpCallCardStatusType_id is null then 1
						when CCC.Lpu_ppdid is null
							then
								case
									when CCC.CmpCallCardStatusType_id in (1, 2) then CCC.CmpCallCardStatusType_id+1
									when CCC.CmpCallCardStatusType_id in (4) then CCC.CmpCallCardStatusType_id
									when CCC.CmpCallCardStatusType_id in (6) then 10
									when CCC.CmpCallCardStatusType_id in (5) then 9
									when CCC.CmpCallCardStatusType_id in (3) then 7
								end
							else
								case
									when CCC.CmpCallCardStatusType_id in (1, 2, 3, 4, 5, 6) then CCC.CmpCallCardStatusType_id+4
								end
					end
				else 10
			end as \"Admin_CmpGroup_id\",
			case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
				then
					case
						when CCC.CmpCallCardStatusType_id is null then '01'
						when CCC.Lpu_ppdid is null
							then
								case
									when CCC.CmpCallCardStatusType_id in (1, 2) then '0'||(CCC.CmpCallCardStatusType_id + 1)::varchar
									when CCC.CmpCallCardStatusType_id in (4) then '0'||CCC.CmpCallCardStatusType_id::varchar
									when CCC.CmpCallCardStatusType_id in (6) then '10'
									when CCC.CmpCallCardStatusType_id in (5) then '09'
									when CCC.CmpCallCardStatusType_id in (3) then '07'
								end
							else
								case
									when CCC.CmpCallCardStatusType_id in (1,2,3,4,5) then '0'||(CCC.CmpCallCardStatusType_id + 4)::varchar
									when CCC.CmpCallCardStatusType_id in (6) then ('10')
								end
					END
				else '10'
			end as \"Admin_CmpGroupName_id\",
			case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
				then
					case
						WHEN CCC.CmpCallCardStatusType_id is NULL then 1
						WHEN CCC.Lpu_ppdid IS NULL THEN
							CASE
								WHEN CCC.CmpCallCardStatusType_id = 4 THEN 4
								WHEN CCC.CmpCallCardStatusType_id = 6 THEN 10
								WHEN CCC.CmpCallCardStatusType_id = 3 THEN 8
								WHEN CCC.CmpCallCardStatusType_id in (1, 2) THEN CCC.CmpCallCardStatusType_id + 1
								ELSE CCC.CmpCallCardStatusType_id + 4
							END
						ELSE
							CASE
								WHEN CmpCallCardStatusType_id = 4 THEN 7
								WHEN CmpCallCardStatusType_id = 3 THEN 8
								ELSE CCC.CmpCallCardStatusType_id + 4
							END
						END
				else 9
			end as \"HeadDuty_CmpGroup_id\",
			case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
				then
					case
						WHEN CCC.CmpCallCardStatusType_id is NULL then '01'
						WHEN CCC.Lpu_ppdid IS NULL THEN
							CASE
								WHEN CCC.CmpCallCardStatusType_id = 4 THEN '04'
								WHEN CCC.CmpCallCardStatusType_id = 6 THEN '10'
								WHEN CCC.CmpCallCardStatusType_id = 3 THEN '08'
								WHEN CCC.CmpCallCardStatusType_id in (1, 2) THEN '0'||(CCC.CmpCallCardStatusType_id + 1)::varchar
								ELSE  '0'||(CCC.CmpCallCardStatusType_id + 4)::varchar
							END
						ELSE
							CASE
								WHEN CmpCallCardStatusType_id = 4 THEN '07'
								WHEN CmpCallCardStatusType_id = 3 THEN '08'
								WHEN CmpCallCardStatusType_id = 6 THEN '10'
								ELSE '0'||(CCC.CmpCallCardStatusType_id + 4)::varchar
							END
						END
				else '09'
			end as \"HeadDuty_CmpGroupName_id\",
			case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
				then
					case
						WHEN CCC.Lpu_ppdid IS NULL THEN
							CASE
								WHEN CCC.CmpCallCardStatusType_id = 4 THEN 3
								WHEN CCC.CmpCallCardStatusType_id = 6 THEN 9
								WHEN CCC.CmpCallCardStatusType_id = 3 THEN 7
								WHEN CCC.CmpCallCardStatusType_id in (1, 2) THEN CCC.CmpCallCardStatusType_id
								ELSE CCC.CmpCallCardStatusType_id + 3
							END
						ELSE
							CASE
								WHEN CmpCallCardStatusType_id = 4 THEN 6
								WHEN CmpCallCardStatusType_id = 3 THEN 7
								ELSE CCC.CmpCallCardStatusType_id + 3
							END
						END
				else 9
			end as \"DispatchDirect_CmpGroup_id\",
			case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
				then
					case
						when CCC.CmpCallCardStatusType_id is NULL then 1
						when CCC.Lpu_ppdid IS NULL
							then
								case
									when CCC.CmpCallCardStatusType_id in (1, 2) then CCC.CmpCallCardStatusType_id+1
									when CCC.CmpCallCardStatusType_id in (4) then CCC.CmpCallCardStatusType_id
									when CCC.CmpCallCardStatusType_id in (6) then 10
									when CCC.CmpCallCardStatusType_id in (5) then 9
									when CCC.CmpCallCardStatusType_id in (3) then 7
								end
							else
								case
									when CCC.CmpCallCardStatusType_id in (1,2,3,4,5,6) then CCC.CmpCallCardStatusType_id+4
								end
					END
				else 10
			end as \"DispatchCall_CmpGroup_id\",
			case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
				then
					case
						when CCC.CmpCallCardStatusType_id is NULL then '01'
						when CCC.Lpu_ppdid IS NULL
							then
								case
									when CCC.CmpCallCardStatusType_id in (1, 2) then '0'||(CCC.CmpCallCardStatusType_id + 1)::varchar
									when CCC.CmpCallCardStatusType_id in (4) then '0'||CCC.CmpCallCardStatusType_id::varchar
									when CCC.CmpCallCardStatusType_id in (6) then '10'
									when CCC.CmpCallCardStatusType_id in (5) then '09'
									when CCC.CmpCallCardStatusType_id in (3) then '07'
								end
							else
								case
									when CCC.CmpCallCardStatusType_id in (1, 2, 3, 4, 5) then '0'||(CCC.CmpCallCardStatusType_id + 4)::varchar
									when CCC.CmpCallCardStatusType_id in (6) then ('10')
								end
					END
				else '10'
			end as \"DispatchCall_CmpGroupName_id\",
			case when CCC.pmUser_insID = :pmUser_id then 1 else 0 end as \"Owner\"
		";
		$query = "
			select {$selectString}
			from
				v_CmpCallCard CCC
				left join lateral (
					select CmpCallCardStatus_insDT as ServeDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 4
					  and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as ServeDT on true
				left join lateral (
					select CmpCallCardStatus_insDT as ToDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 2
					  and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as ToDT on true
				left join lateral (
					select
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id,
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name
					from
						v_CmpCallCardStatus
						left join v_CmpMoveFromNmpReason on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id
					where CmpCallCardStatusType_id = 3
					  and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as CCCStatusHist
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpLpu CL on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_Lpu L on L.Lpu_id = CCC.CmpLpu_id
				left join CmpDiag CD on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D on D.Diag_id = CCC.Diag_sid
				left join v_Lpu SLPU on SLPU.Lpu_id = CCC.Lpu_ppdid
				left join lateral (
					select *
					from v_EvnPL AS t1
					where t1.CmpCallCard_id = CCC.CmpCallCard_id
					  and t1.Lpu_id = CCC.Lpu_ppdid
					  and t1.EvnPL_setDate >= CCC.CmpCallCard_prmDT::date
					  and CCC.Lpu_ppdid is not null
					limit 1
				) as EPL on true
				left join v_Diag EPLD on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT on DT.DirectType_id = EPL.DirectType_id
				left join v_pmUserCache PMC on PMC.PMUser_id = CCC.pmUser_updID
				left join {$callObject->schema}.v_CmpCloseCard CLC on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_CmpCallCardlockList CCCLL on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id and (60 - datediff('ss', CCCLL.CmpCallCardLockList_updDT, tzgetdate())) > 0
				left join v_EmergencyTeam ET on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
			where CCC.CmpCallCard_id = :CmpCallCard_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Тестовый эксперимент по получению параметров для инсерта в sql запрос
	 * inputProcedure - процедура для инсерта
	 * params - параметры для вставки
	 * exceptedFields исключающие поля (поля не для сохранения)
	 * isPostgresql - параметр для конвертации запроса в Postgresql формат
	 * filterSqlParams - массив для фильтров
	 * возвращает список параметров(array/string(Postgresql)), значения параметров в sql (string)
	 */
    public static function getParamsForSQLQuery(CmpCallCard_model $callObject, $inputProcedure, $params, $exceptedFields = null, $isPostgresql = false)
    {
        $paramsArray = [];
        $sqlParams = "";
        $paramsPosttgress = "";
        $filterSqlParams = [];

		//если процедура передана вместе со схемой, уберем схему
		if (strrpos($inputProcedure, '.')) {
			$inputProcedure = explode('.', $inputProcedure)[1];
		}
		
        //автоматический сбор полей с процедуры
        $queryFields = $callObject->db->query("
			select
				name as \"Parameter_name\",
				type.typname as \"Type\"
			from (
				select 
					unnest(p.proargnames) as name,
					unnest(p.proargtypes) as type_oid
				from 
					pg_catalog.pg_proc p
				where 
					lower(p.proname) = lower('{$inputProcedure}')
			) params
			left join pg_catalog.pg_type type on type.oid = params.type_oid");
        $allFields = $queryFields->result_array();
		
		//сформируем массив с информацией о именах ключей в верхнем регистре
		$params_key_arr = array();
		foreach ($params as $key => $value) {
			$params_key_arr[strtolower($key)] = $key;
		}
		
		//приведем в массиве с параметрами ключи к нижнему регистру
		$params = array_change_key_case($params);
		
		//приведем в массиве с исключающими полями значения к нижнему регистру
		if (is_array($exceptedFields)) {
			$exceptedFields = array_map('strtolower', $exceptedFields);
		}
		
        //получаем список всех возможных полей
        foreach ($allFields as $fieldVal)
        {
            $field = ltrim($fieldVal["Parameter_name"], ":");

            //получение значений параметров
            if( isset($params[$field]) && !empty($params[$field]) ){
                //небольшая ремарка для полей boolean-овского типа
                if($params[$field] == 'true') $params[$field] = 2;
                if($params[$field] == 'false') $params[$field] = 1;
                //
                $paramsArray[$params_key_arr[$field]] = $params[$field];
                //список полей и значений которые определены
                if( empty($exceptedFields) || !(in_array($field, $exceptedFields)) ) {
                    if($isPostgresql){
                        if ($paramsPosttgress) {
							$paramsPosttgress .= ",\r\n";
						}
						$paramsPosttgress .= $params[$field];
                    }
                    else{
                        if ($sqlParams) {
							$sqlParams .= ",\r\n";
						}
						$sqlParams .= $params_key_arr[$field]." := :".$params_key_arr[$field];
                    }
                }
            }
        }

        //список параметров, значения параметров
        return array(
            "paramsArray" => ($isPostgresql)?$paramsPosttgress:$paramsArray,
            "sqlParams" => $sqlParams
        );
    }

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getCmpCallCardNumber(CmpCallCard_model $callObject, $data)
	{
		$params = $callObject->getDatesToNumbersDayYear($data);
		$whereString = "where CCC.Lpu_id=:Lpu_id";
		$params["Lpu_id"] = $data["Lpu_id"] ? $data["Lpu_id"] : $data["session"]["lpu_id"];
		$sql = "
			select
				coalesce(max(case when (CCC.CmpCallCard_prmDT >= :startDateTime and CCC.CmpCallCard_prmDT < :endDateTime) then CmpCallCard_Numv else null end), 0) + 1 as \"CmpCallCard_Numv\",
				coalesce(max(case when (CCC.CmpCallCard_prmDT >= :firstDayCurrentYearDateTime and CCC.CmpCallCard_prmDT < :firstDayNextYearDateTime) then CmpCallCard_Ngod else null end), 0) + 1 as \"CmpCallCard_Ngod\"
			from v_CmpCallCard CCC
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * Возвращает параметры начала и окончания дня/года из настроек
	 * startDateTime - начало дня
	 * endDateTime - конец дня
	 * firstDayCurrentYearDateTime - начало года
	 * firstDayNextYearDateTime - конец года
	 *
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	public static function getDatesToNumbersDayYear(CmpCallCard_model $callObject, $data)
	{
		$callObject->load->model("Options_model", "opmodel");
		$o = $callObject->opmodel->getOptionsGlobals($data);
		$g_options = $o["globals"];

		//дата приема вызова
		$prmDateVal = !empty($data["CmpCallCard_prmDT"]) ? $data["CmpCallCard_prmDT"] : (!empty($data["CmpCallCard_prmDate"]) ? $data["CmpCallCard_prmDate"] : "");
		if (!empty($prmDateVal)) {
			$prmDateObj = new DateTime($prmDateVal);
		} else {
			$prmDateQuery = $callObject->dbmodel->getFirstRowFromQuery("select to_char(tzGetDate(), '{$callObject->dateTimeForm104}')||to_char(tzGetDate(), '{$callObject->dateTimeForm108}') as datetime");
			$prmDateObj = new DateTime($prmDateQuery["datetime"]);
		}
		$prmDate = $prmDateObj->format("Y-m-d");
		$prmYear = (int)$prmDateObj->format("Y");
		//по задачке #112257 день у нас начинается с времени в настройках, вот так то
		//дата приема вызова с часами из опций
		$optionsDateTime = $prmDate . " " . $g_options["day_start_call_time"] . ":00";
		//если дата еще не наступила по времени - ищем между "вчера" и "сегодня"
		$dateTime = new DateTime($optionsDateTime);
		if ($prmDateObj < $dateTime) {
			$start = $dateTime->modify("-1 day");
			$params["startDateTime"] = $start->format("Y-m-d H:i:s");
			$params["endDateTime"] = $optionsDateTime;
		} else {
			//если дата еще наступила по времени - ищем между "сегодня" и "завтра"
			$params["startDateTime"] = $optionsDateTime;
			$end = $dateTime->modify("+1 day");
			$params["endDateTime"] = $end->format("Y-m-d H:i:s");
		}

		//для выборки по году - ищем между 1 января текущего года с временем и 1 января след. года с временем
		$params["firstDayCurrentYearDateTime"] = $prmYear . "-01-01 " . $g_options["day_start_call_time"] . ":00";
		$params["firstDayNextYearDateTime"] = ($prmYear + 1) . "-01-01 " . $g_options["day_start_call_time"] . ":00";
		return $params;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @return array|bool
	 */
	public static function getResults(CmpCallCard_model $callObject)
	{
		$query = "
			select
				RES.CmpPPDResult_id as \"CmpPPDResult_id\",
			    RES.CmpPPDResult_Name as \"CmpPPDResult_Name\",
			    RES.CmpPPDResult_Code as \"CmpPPDResult_Code\"
			from v_CmpPPDResult RES
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @return array|bool
	 */
	public static function getRejectPPDReasons(CmpCallCard_model $callObject)
	{
		$query = "
			select
				RES.CmpPPDResult_id as \"CmpPPDResult_id\",
			    RES.CmpPPDResult_Code - 10 as \"CmpPPDResult_Code\",
			    RES.CmpPPDResult_Name as \"CmpPPDResult_Name\"
			from CmpPPDResult RES
			where CmpPPDResult_Code in (11, 12, 13, 14, 15, 16)
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @return array|bool
	 */
	public static function getMoveFromNmpReasons(CmpCallCard_model $callObject)
	{
		$query = "
			select
				CMFNR.CmpMoveFromNmpReason_id as \"CmpMoveFromNmpReason_id\",
			    CMFNR.CmpMoveFromNmpReason_Name as \"CmpMoveFromNmpReason_Name\",
			    0 as \"requiredTextField\"
			from v_CmpMoveFromNmpReason CMFNR
			union
			select
				null as \"CmpMoveFromNmpReason_id\",
			    'Другая причина (указать)' as \"CmpMoveFromNmpReason_Name\",
			    1 as \"requiredTextField\"
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @return array|bool
	 */
	public static function getReturnToSmpReasons(CmpCallCard_model $callObject)
	{
		$query = "
			select
				CRTSR.CmpReturnToSmpReason_id as \"CmpReturnToSmpReason_id\",
			    CRTSR.CmpReturnToSmpReason_Name as \"CmpReturnToSmpReason_Name\",
			    0 as \"requiredTextField\"
			from v_CmpReturnToSmpReason CRTSR
			union
			select
				null as \"CmpReturnToSmpReason_id\",
			    'Другая причина (указать)' as \"CmpReturnToSmpReason_Name\",
			    1 as \"requiredTextField\"
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
	
	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCombo(CmpCallCard_model $callObject, $data, $object) {

		$sql = "
			select
				{$object}_Name as \"{$object}_Name\",
			    {$object}_id as \"{$object}_id\"
			from
				{$callObject->comboSchema}.v_{$object}";

		$query = $callObject->db->query($sql);
		$result = $query->result_array();

		$content = '';
		foreach ($result as $value){

			if($value[$object.'_id']==$data){
				$content.='<div class="innerwrapper">'.$value[$object.'_Name']. ' <div class="v_ok"></div></div>';
			}
			else $content.='<div class="innerwrapper">'.$value[$object.'_Name']. ' <div class="v_no"></div></div> ';
		}

		return $content;
	}
	

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCombox(CmpCallCard_model $callObject, $data)
	{
		$selectString = "
			CMB.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\",
		    CMB.CmpCloseCardCombo_Code as \"CmpCloseCardCombo_Code\",
		    CMB.ComboName as \"ComboName\",
		    CMB.isLoc as \"isLoc\"
		";
		$whereString = "
				Parent_id = '0'
			and ComboSys = :combo_id
		";
		$query = "
			select {$selectString}
			from {$callObject->comboSchema}.v_CmpCloseCardCombo CMB
			where {$whereString}
		";
		$queryParams = ["combo_id" => $data["combo_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		$selectString = "
			CMB.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\",
		    CMB.CmpCloseCardCombo_Code as \"CmpCloseCardCombo_Code\",
		    CMB.ComboName as \"ComboName\",
		    CMB.ComboAdd as \"ComboAdd\",
		    CMB.isLoc as \"isLoc\"
		";
		$whereString = "Parent_id = '{$res[0]["CmpCloseCardCombo_id"]}'";
		$query = "
			select {$selectString}
			from {$callObject->comboSchema}.v_CmpCloseCardCombo CMB
			where {$whereString}
		";
		$result = $callObject->db->query($query);
		$res2 = $result->result("array");
		$ret = [];
		foreach ($res2 as $combo) {
			if ($combo["isLoc"] == "1") {
				if ($res[0]["isLoc"] == "2") {
					$ret[] = ["boxLabel" => $combo["ComboName"] . " " . $combo["ComboAdd"], "id" => "CMPCLOSE_CB_{$combo["CmpCloseCardCombo_Code"]}", "name" => $data["combo_id"], "inputValue" => $combo["CmpCloseCardCombo_Code"]];
				} else {
					$ret[] = ["boxLabel" => $combo["ComboName"] . " " . $combo["ComboAdd"], "id" => "CMPCLOSE_CB_{$combo["CmpCloseCardCombo_Code"]}", "name" => $data["combo_id"] . "[]", "inputValue" => $combo["CmpCloseCardCombo_Code"]];
				}
			} else {
				$wid = strlen($combo["ComboName"] . " " . $combo["ComboAdd"]);
				if ($wid < 10) {
					$wl = 50;
				}
				if ($wid >= 10) {
					$wl = 120;
				}
				if ($wid > 20) {
					$wl = 400;
				}
				if ($res[0]["isLoc"] == "2") {
					$add = ($combo["ComboAdd"] != "") ? ", <i>" . $combo["ComboAdd"] . "</i>" : "";
					$ret[] = [
						"boxLabel" => $combo["ComboName"] . $add,
						"id" => "CMPCLOSE_CB_{$combo["CmpCloseCardCombo_Code"]}",
						"name" => $data["combo_id"],
						"inputValue" => "2",
						"value" => "2"
					];
				}
				switch ($combo['CmpCloseCardCombo_id']) {
					default:
						$ret[] = [
							"labelWidth" => $wl,
							"labelAlign" => "left",
							"name" => 'ComboValue[' . $combo['CmpCloseCardCombo_Code'] . ']',
							"xtype" => 'textfield',
							"ctCls" => "left",
							"id" => "CMPCLOSE_ComboValue_{$combo['CmpCloseCardCombo_Code']}",
							"style" => "text-align: left",
							"fieldLabel" => ($res[0]['isLoc'] != '2') ? ($combo['ComboName'] . ' ' . $combo['ComboAdd']) : ''
						];
				}
			}
			if ($data["combo_id"] == "ResultUfa_id") {
				// 3 level additional
				$selectString = "
					CMB.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\",
				    CMB.CmpCloseCardCombo_Code as \"CmpCloseCardCombo_Code\",
				    CMB.ComboName as \"ComboName\",
				    CMB.ComboAdd as \"ComboAdd\",
				    CMB.isLoc as \"isLoc\"
				";
				$whereString = "Parent_id = '{$combo["CmpCloseCardCombo_id"]}'";
				$query3 = "
					select {$selectString}
					from {$callObject->comboSchema}.v_CmpCloseCardCombo CMB
					where {$whereString}
				";
				$result3 = $callObject->db->query($query3);
				$res3 = $result3->result("array");
				foreach ($res3 as $r3) {
					$ret[] = [
						"labelWidth" => "200",
						"name" => "ComboValue[" . $r3["CmpCloseCardCombo_Code"] . "]",
						"xtype" => "textfield",
						"ctCls" => "left",
						"id" => "CMPCLOSE_ComboValue_{$r3["CmpCloseCardCombo_Code"]}",
						"style" => "text-align: left;",
						"styleLabel" => "width: 200px;",
						"labelStyle" => "width: 280px;",
						"fieldLabel" => $r3["ComboName"] . " " . $r3["ComboAdd"]
					];
				}
			}
		}
		return $ret;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @return array|bool
	 */
	public static function getComboxAll(CmpCallCard_model $callObject)
	{
		$selectString = "
			CMB.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\",
			CMB.CmpCloseCardCombo_Code as \"CmpCloseCardCombo_Code\",
			CMB.ComboName as \"ComboName\",
			CMB.ComboAdd as \"ComboAdd\",
			coalesce(parentCMB.ComboSys, grandParentCMB.ComboSys, grandGrandParentCMB.ComboSys, null) as \"ComboSys\",
			CMB.Parent_id as \"Parent_id\",
			parentCMB.Parent_id as \"grandParent_id\",
			parentCMB.CmpCloseCardCombo_Code as \"ParentCombo_Code\",
			parentCMB.isLoc as \"parentLoc\",
			CMB.isLoc as \"isLoc\",
			CMB.CmpCloseCardCombo_ItemType as \"CmpCloseCardCombo_ItemType\",
			CMB.CmpCloseCardCombo_ItemSort as \"CmpCloseCardCombo_ItemSort\"
		";
		$fromString = "
			{$callObject->comboSchema}.v_CmpCloseCardCombo CMB
			left join {$callObject->comboSchema}.v_CmpCloseCardCombo parentCMB on parentCMB.CmpCloseCardCombo_id = CMB.Parent_id
			left join {$callObject->comboSchema}.v_CmpCloseCardCombo grandParentCMB on grandParentCMB.CmpCloseCardCombo_id = parentCMB.Parent_id
			left join {$callObject->comboSchema}.v_CmpCloseCardCombo grandGrandParentCMB on grandGrandParentCMB.CmpCloseCardCombo_id = grandParentCMB.Parent_id
		";
		$whereString = "
				CMB.Parent_id > 0
			and (CMB.CmpCloseCardCombo_IsClose = 1 or CMB.CmpCloseCardCombo_IsClose is null)
		";
		$orderByString = "\"ComboSys\", CMB.CmpCloseCardCombo_ItemSort";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			order by {$orderByString}
				
		";
		$query = $callObject->db->query($sql);
		if (!is_object($query)) {
			return false;
		}
		// Пояснение того, что такое происходит:
		// Выбираем элементы подчиненные родителю, CMB.Parent_id > 0
		// Элементы у которых нет деда grandParent_id - 2го уровня
		// Обрабатываем их в соответствии с полями isLoc
		// Элементы у которых есть дед grandParent_id - 3го уровня
		// Далее пояснения:
		$ret = [];
		foreach ($query->result_array() as $combo) {
			//обработка 2 уровня
			if ($combo["grandParent_id"] == 0) {
				//заготовка поля для ввода
				$wid = strlen($combo["ComboName"] . " " . $combo["ComboAdd"]);
				if ($wid < 10) {
					$wl = 50;
				} elseif ($wid >= 10) {
					$wl = 120;
				} elseif ($wid > 20) {
					$wl = 400;
				}
				//текстовое поле, чтобы его не создавать 100500 раз
				$txtField = [
					"labelWidth" => $wl,
					"width" => 300,
					"labelAlign" => "left",
					"name" => "ComboValue_" . $combo["CmpCloseCardCombo_Code"],
					"xtype" => "textfield",
					"ctCls" => "left",
					"maxLength" => 50,
					"id" => "CMPCLOSE_ComboValue_" . $combo["CmpCloseCardCombo_Code"],
					"style" => "text-align: left",
					"fieldLabel" => ($combo["parentLoc"] != "2" ? $combo["ComboName"] . " " . $combo["ComboAdd"] : ""),
					"hidden" => ($combo["parentLoc"] != "2" ? true : false),
				];
				//конец заготовки поля для ввода
				switch ($combo["isLoc"]) {
					case "0":
					{
						//чекбокс/комбобокс с текстовым полем
						$ret[$combo["ComboSys"]][] = [
							"boxLabel" => $combo["ComboName"] . ($combo["ComboAdd"] != "" ? ", <i>" . $combo["ComboAdd"] . "</i>" : ""),
							"id" => "CMPCLOSE_CB_" . $combo["CmpCloseCardCombo_Code"],
							"name" => "ComboCheck_" . $combo["ComboSys"],
							"code" => $combo["CmpCloseCardCombo_Code"],
							"inputValue" => $combo["CmpCloseCardCombo_Code"]
						];
						$txtField["labelStyle"] = "height: 0;";
						$txtField["parent_code"] = $combo["CmpCloseCardCombo_Code"];
						$ret[$combo["ComboSys"]][] = $txtField;
						break;
					}
					case "1":
					{
						// обычный чекбокс или радио
						$ret[$combo["ComboSys"]][] = [
							"boxLabel" => $combo["ComboName"] . " " . $combo["ComboAdd"],
							"id" => "CMPCLOSE_CB_{$combo["CmpCloseCardCombo_Code"]}",
							"name" => "ComboCheck_" . $combo["ComboSys"],
							"inputValue" => $combo["CmpCloseCardCombo_Code"],
							"code" => $combo["CmpCloseCardCombo_Code"]
						];
						break;
					}
					case "3":
					{
						//текстовое поле с лейблом
						$ret[$combo["ComboSys"]][] = [
							"xtype" => "label",
							"text" => $combo["ComboName"] . ":",
							"style" => "display: block; text-align: left;",
						];
						$ret[$combo["ComboSys"]][] = $txtField;
						break;
					}
					case "4":
					{
						//комбобокс с 2 лейблами DS
						$ret[$combo["ComboSys"]][] = [
							"boxLabel" => $combo["ComboName"] . ($combo["ComboAdd"] != "" ? ", <i>" . $combo["ComboAdd"] . "</i>" : ""),
							"id" => "CMPCLOSE_CB_" . $combo["CmpCloseCardCombo_Code"],
							"name" => "ComboCheck_" . $combo["ComboSys"],
							"inputValue" => $combo["CmpCloseCardCombo_Code"],
							"code" => $combo["CmpCloseCardCombo_Code"],
							"type" => "dsComboRadioCmpParent"
						];
						$ret[$combo["ComboSys"]][] = [
							"xtype" => "label",
							"text" => "D",
							"id" => "CMPCLOSE_CBCD_" . $combo["CmpCloseCardCombo_Code"],
							"style" => "text-align: right; width: 15px;",
							"parent_code" => $combo["CmpCloseCardCombo_Code"],
							"type" => "dsComboRadioCmp"
						];
						$ret[$combo["ComboSys"]][] = [
							"xtype" => "swequalitytypecombo",
							"width" => 30,
							"id" => "CMPCLOSE_CBC_" . $combo["CmpCloseCardCombo_Code"],
							"name" => "ComboCmp_" . $combo["CmpCloseCardCombo_Code"],
							"hiddenName" => "ComboCmp_" . $combo["CmpCloseCardCombo_Code"],
							"parent_code" => $combo["CmpCloseCardCombo_Code"],
							"type" => "dsComboRadioCmp"
						];
						$ret[$combo["ComboSys"]][] = [
							"xtype" => "label",
							"text" => "S",
							"id" => "CMPCLOSE_CBCS_" . $combo["CmpCloseCardCombo_Code"],
							"style" => "text-align: right; width: 15px;",
							"parent_code" => $combo["CmpCloseCardCombo_Code"],
							"type" => "dsComboRadioCmp"
						];
						break;
					}
					case "5":
					{
						//radiogroup
						//формируем радиогруппу
						$radioItems = [];
						foreach ($ret[$combo["ComboSys"]] as $key => $value) {
							if (isset($value["parent_code"]) && $value["parent_code"] == $combo["CmpCloseCardCombo_Code"]) {
								$radioItems[] = $value;
								unset($ret[$combo["ComboSys"]][$key]);
							}
						};
						$ret[$combo["ComboSys"]][] = [
							"xtype" => "radiogroup",
							"columns" => 2,
							"vertical" => true,
							"width" => "100%",
							"items" => $radioItems
						];
						$ret[$combo["ComboSys"]] = array_values($ret[$combo["ComboSys"]]);
						break;
					}
					case "6":
					{
						//чекбокс/комбобокс с комбобоксом осложнение
						$ret[$combo["ComboSys"]][] = [
							"boxLabel" => $combo["ComboName"] . ($combo["ComboAdd"] != "" ? ", <i>" . $combo["ComboAdd"] . "</i>" : ""),
							"id" => "CMPCLOSE_CB_" . $combo["CmpCloseCardCombo_Code"],
							"name" => "ComboCheck_" . $combo["ComboSys"],
							"code" => $combo["CmpCloseCardCombo_Code"],
							"itemCls" => "leftSequelaCheck",
							"inputValue" => $combo["CmpCloseCardCombo_Code"]
						];
						if (getRegionNick() == "perm") {
							$comboField = [
								"comboSubject" => "SequelaDegreeType",
								"allowBlank" => false,
								"labelWidth" => 100,
								"width" => 100,
								"listWidth" => 130,
								"labelAlign" => "left",
								"name" => "ComboValue_" . $combo["CmpCloseCardCombo_Code"],
								"hiddenName" => "ComboValue_" . $combo["CmpCloseCardCombo_Code"],
								"xtype" => "swcommonsprcombo",
								"itemCls" => "rightSequelaCombo",
								"id" => "CMPCLOSE_ComboValue_" . $combo["CmpCloseCardCombo_Code"],
								"hidden" => ($combo["parentLoc"] != "2" ? true : false),
								"parent_code" => $combo["CmpCloseCardCombo_Code"],
								"style" => "margin-left: 10px",
								"value" => 2
							];
							$ret[$combo["ComboSys"]][] = $comboField;
						}
						break;
					}
					default:
					{
						//просто текстовое поле
						$ret[$combo["ComboSys"]][] = $txtField;
						break;
					}
				}
			} else {
				//3 уровень
				//элемент родителя radioGroup - опрелеляются как радио
				//parentLoc = 5 - радиогруппа
				if ($combo["parentLoc"] == 5) {
					$ret[$combo["ComboSys"]][] = [
						"boxLabel" => $combo["ComboName"] . " " . $combo["ComboAdd"],
						"id" => "CMPCLOSE_CB_{$combo["CmpCloseCardCombo_Code"]}",
						//"name"=> $combo["ComboSys"] . "_" . $combo[ "ParentCombo_Code" ],
						"name" => "ComboCheck_" . $combo["ParentCombo_Code"],
						"inputValue" => $combo["CmpCloseCardCombo_Code"],
						"code" => $combo["CmpCloseCardCombo_Code"],
						"parent_code" => $combo["ParentCombo_Code"]
					];
					continue;
				}
				//далее самоопределяющиеся по типу элементы
				$item = [
					"xtype" => "textfield",
					"name" => "ComboValue_" . $combo["CmpCloseCardCombo_Code"],
					"hiddenName" => "ComboValue_" . $combo["CmpCloseCardCombo_Code"],
					"id" => "CMPCLOSE_ComboValue_" . $combo["CmpCloseCardCombo_Code"],
					"cls" => "ResultUfa-parent-" . $combo["CmpCloseCardCombo_Code"],
					"parent_code" => $combo["ParentCombo_Code"],
					"code" => $combo["CmpCloseCardCombo_Code"]
				];
				switch ($combo["CmpCloseCardCombo_ItemType"]) {
					case "swdatetimefield":
						$item["xtype"] = "swdatetimefield";
						$item["dateFieldWidth"] = 80; // ширина поля
						$item["dateLabelWidth1"] = "235px"; // ширина обертки лэйбла и поля
						$item["dateLabelStyle"] = "width: 115px;";
						$item["dateLabel"] = $combo["ComboName"] . " " . $combo["ComboAdd"];
						$item["timeLabelWidth"] = 50; // ширина лэйбла
						$item["timeLabelWidth1"] = "145px"; // ширина обертки лэйбла и поля
						$item["timeLabel"] = "Время";
						//индивидуальная правка ширины лейбла для поля остальное
						if ($combo["ComboSys"] == "ResultOther_id") {
							$item["dateLabelStyle"] = "";
							$item["dateLabelWidth1"] = "220px";
						}
						//индивидуальная правка ширины лейбла для поля остальное
						if ($combo["ComboSys"] == "Result_id") {
							$item["hiddenName"] = "ComboValue_" . $combo["CmpCloseCardCombo_Code"];
							$item["dateLabel"] = "Дата";
							$item["dateLabelStyle"] = "";
							$item["dateLabelWidth1"] = "220px";
						}
						break;
					case "swtimefield":
						$item["xtype"] = "swtimefield";
						$item["dateFieldWidth"] = 80; // ширина поля
						$item["timeLabelWidth1"] = "140px"; // ширина обертки лэйбла и поля
						$item["timeLabel"] = "Время";
						break;
					case "textfield":
						$item["fieldLabel"] = $combo["ComboName"] . " " . $combo["ComboAdd"];
						$item["labelStyle"] = "width: 130px";
						break;
					case "swlpucombo":
						$item["fieldLabel"] = $combo["ComboName"];
						$item["xtype"] = "swlpuopenedcombo";
						$item["forceselection"] = true;
						$item["editable"] = true;
						$item["ctxSerach"] = true;
						$item["listWidth"] = 400;
						$item["autoLoad"] = true;
						$item["labelStyle"] = ($combo["ComboName"] == "МО") ? "width: 110px" : "width: 200px";
						break;
					case "sworgcombo":
						$item["fieldLabel"] = $combo["ComboName"];
						$item["labelStyle"] = "width: 180px;";
						$item["xtype"] = "sworgcomboex";
						$item["enableKeyEvents"] = true;
						$item["triggerAction"] = "none";
						$item["width"] = 320;
						$item["enableOrgType"] = false;
						$item["defaultOrgType"] = 11;
						$item["autoLoad"] = true;
						break;
					//адресный триггер
					case "addresstriggerfield":
						$item["fieldLabel"] = "Адрес посещения";
						$item["enableKeyEvents"] = true;
						$item["width"] = 320;
						$item["ctCls"] = "addresstriggerfield";
						$item["cls"] = "addresstriggerfield";
						$item["xtype"] = "swtripletriggerfield";
						break;
					case "hidden":
						$item["xtype"] = "hidden";
						break;
					//в результате оказания смп
					case "swdieplace":
						$item["fieldLabel"] = "Место";
						$item["xtype"] = "swcommonsprcombo";
						$item["comboSubject"] = "CmpLethalType";
						$item["listWidth"] = 300;
						//$item["name"] = "CmpLethalType_id";
						//$item["hiddenName"] = "CmpLethalType_id";
						$item["autoLoad"] = true;
						break;
					//для состава бригады
					case "swmedpersonalcombo":
						$item["fieldLabel"] = $combo["ComboName"];
						$item["xtype"] = "swmedpersonalcombo";
						$item["listWidth"] = 400;
						$item["labelStyle"] = "width: 50px; display: none;";
						$item["labelWidth"] = 50;
						$item["allowBlank"] = "true";
						break;
					case "swemergencyteamorepenvcombo":
						$item["fieldLabel"] = "Номер бригады СМП";
						$item["labelStyle"] = "width: 200px";
						$item["xtype"] = "swemergencyteamorepenvcombo";
						$item["allowBlank"] = "true";
						$item["listWidth"] = 400;
						break;
					case "swEmergencyTeamCCC":
						$item["fieldLabel"] = "Номер бригады СМП";
						$item["labelStyle"] = "width: 200px";
						$item["xtype"] = "swEmergencyTeamCCC";
						$item["allowBlank"] = "true";
						$item["listWidth"] = 400;
						break;
					case "swdiagcombo":
						$item["xtype"] = "swdiagcombo";
						$item["checkAccessRights"] = true;
						$item["labelStyle"] = "width: 200px";
						break;
					case "checkbox":
						$item["xtype"] = "checkbox";
						$item["boxLabel"] = $combo["ComboName"];
						$item["id"] = "CMPCLOSE_CB_{$combo["CmpCloseCardCombo_Code"]}";
						$item["name"] = "ComboCheck_" . $combo["ComboSys"];
						$item["style"] = "margin-left: 50px";
						$item["inputValue"] = $combo["CmpCloseCardCombo_Code"];
						break;
					default:
						$item["labelStyle"] = "width: 200px";
						break;
				}
				$ret[$combo["ComboSys"]][] = $item;
			}
		}
		return $ret;
	}

	/**
	 * Список значений для комбика по ComboSys или CmpCloseCardCombo_Code
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getComboValuesList(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["ComboSys"]) && empty($data["CmpCloseCardCombo_Code"])) {
			return false;
		}
		if ($data["ComboSys"]) {
			$parent_id = $callObject->getComboIdByComboSys($data);
		}
		if ($data["CmpCloseCardCombo_Code"]) {
			$parent_id = $callObject->getComboIdByCode($data);
		}
		if (empty($parent_id)) {
			return false;
		}
		$selectString = "
			CMB.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\",
		    CMB.CmpCloseCardCombo_Code as \"CmpCloseCardCombo_Code\",
		    CMB.ComboName as \"ComboName\",
		    CMB.CmpCloseCardCombo_ItemSort as \"CmpCloseCardCombo_ItemSort\"
		";
		$whereString = "Parent_id = :combo_id";
		$query = "
			select {$selectString}
			from {$callObject->comboSchema}.v_CmpCloseCardCombo CMB
			where {$whereString}
		";
		$queryParams = ["combo_id" => $parent_id];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Печать результата
	 * @param CmpCallCard_model $callObject
	 * @param $CmpCloseCard
	 * @return string
	 */
	public static function getResultCmpForPrint(CmpCallCard_model $callObject, $CmpCloseCard)
	{
		$content = '<div class="wrapper110">';
		$selectString = "
			cr.CmpResult_Name as \"ComboName\",
			cr.CmpResult_id as \"CmpCloseCardCombo_id\",
			cr.CmpResult_Code as \"CmpCloseCardCombo_Code\",
			case when CR.CmpResult_id = ccc.CmpResult_id then 1 else 0 end as \"flag\"
		";
		$fromString = "
			{$callObject->comboSchema}.v_CmpResult cr
			left join {$callObject->schema}.v_CmpCloseCard ccc on ccc.CmpCloseCard_id = :CmpCloseCard_id
		";
		$query = "
			select {$selectString}
			from {$fromString}
		";
		$queryParams = ["CmpCloseCard_id" => $CmpCloseCard];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (is_object($result)) {
			$result = $result->result("array");
			foreach ($result as $res) {
				$fflag = (($res["flag"] == 1) ? '<div class="v_ok"></div>' : '<div class="v_no"></div>');
				$content .= '<div class="innerwrapper">' . $res['ComboName'] . ' ' . $fflag . '</div>';
			}
		}
		$content .= '</div>';
		return $content;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $CmpCloseCard
	 * @param $SysName
	 * @return bool|string
	 */
	public static function getComboRel(CmpCallCard_model $callObject, $CmpCloseCard, $SysName)
	{
		$selectString = "
			CMB.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\",
		    CMB.CmpCloseCardCombo_Code as \"CmpCloseCardCombo_Code\",
		    CMB.ComboName as \"ComboName\"
		";
		$whereString = "
				Parent_id = '0'
			and ComboSys = :combo_id
		";
		$query = "
			select {$selectString}
			from {$callObject->comboSchema}.v_CmpCloseCardCombo CMB
			where {$whereString}
		";
		$queryParams = ["combo_id" => $SysName];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		if (empty($res[0])) {
			return false;
		}
		$comboid = $res[0]["CmpCloseCardCombo_id"];
		
		$selectString = "
			Ccombo.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\",
			Ccombo.CmpCloseCardCombo_Code as \"CmpCloseCardCombo_Code\",
			CCombo.ComboName as \"ComboName\",
			RL.Localize as \"Localize\",
			case when coalesce(RL.CmpCloseCardRel_id, 0) = 0 then 0 else 1 end as \"flag\"
		";
		$fromString = "
			{$callObject->comboSchema}.v_CmpCloseCardCombo CCombo
			left join {$callObject->schema}.v_CmpCloseCardRel RL on RL.CmpCloseCard_id = :CmpCloseCard_id and RL.CmpCloseCardCombo_id = CCombo.CmpCloseCardCombo_id
		";
		$whereString = "CCombo.Parent_id = :ComboId";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		$queryParams = [
			"CmpCloseCard_id" => $CmpCloseCard,
			"ComboId" => $comboid
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		$content = '<div class="wrapper110">';
		foreach ($result as $res) {
			if ($res["CmpCloseCardCombo_Code"] == "111" && (int)$res["Localize"] > 0) {
				$query3 = "
					select L.Lpu_Name as \"Lpu_Name\" 
					from v_Lpu as L
					where L.Lpu_id = :Lpuid
				";
				$queryParams3 = ["Lpuid" => $res["Localize"]];
				$result3 = $callObject->db->query($query3, $queryParams3);
				if (is_object($result3)) {
					$result3 = $result3->result("array");
					if (count($result3) > 0) $res["Localize"] = $result3[0]["Lpu_Name"];
				}
			}
			$fflag = (($res["flag"] == 1) ? '<div class="v_ok"></div>' : '<div class="v_no"></div>');
			if ($SysName == "AgeType_id") {
				if ($res["flag"] == 1) $content .= $res["ComboName"] . " <u>" . $res["Localize"] . "</u>";
			} else {
				$content .= '<div class="innerwrapper">' . $res["ComboName"] . " " . $fflag . "<u>" . $res["Localize"] . "</u></div>";
			}
			if ($SysName == "ResultUfa_id") {
				$selectString = "
					CCombo.ComboName as \"ComboName\",
					CCombo.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\",
					CCombo.CmpCloseCardCombo_Code as \"CmpCloseCardCombo_Code\",
					RL.Localize as \"Localize\",
					case when coalesce(RL.CmpCloseCardRel_id, 0) = 0 then 0 else 1 end as \"flag\"
				";
				$fromString = "
					{$callObject->comboSchema}.v_CmpCloseCardCombo CCombo
					left join {$callObject->schema}.v_CmpCloseCardRel RL on RL.CmpCloseCard_id = :CmpCloseCard_id and RL.CmpCloseCardCombo_id = CCombo.CmpCloseCardCombo_id
				";
				$whereString = "CCombo.Parent_id = :ComboId";
				$query2 = "
					select {$selectString}
					from {$fromString}
					where {$whereString}
				";
				$queryParams2 = [
					"CmpCloseCard_id" => $CmpCloseCard,
					"ComboId" => $res["CmpCloseCardCombo_id"]
				];
				$result2 = $callObject->db->query($query2, $queryParams2);
				if (is_object($result2)) {
					$result2 = $result2->result("array");
					foreach ($result2 as $res2) {
						if ($res2["CmpCloseCardCombo_Code"] == "241") {
							$query4 = "select L.Lpu_Name as \"Lpu_Name\" from v_Lpu as L where L.Lpu_id = :Lpuid";
							$queryParams4 = ["Lpuid" => (int)$res2["Localize"]];
							$result4 = $callObject->db->query($query4, $queryParams4);
							if (is_object($result4)) {
								$result4 = $result4->result("array");
								if (count($result4) > 0) $res2["Localize"] = $result4[0]["Lpu_Name"];
							}
						}
						if ($res2["CmpCloseCardCombo_Code"] == "243") {
							$query4 = "
								select D.Diag_FullName as \"Diag_FullName\"
								from v_Diag as D
								where D.Diag_id = :Diagid
							";
							$queryParams4 = ["Diagid" => (int)$res2["Localize"]];
							$result4 = $callObject->db->query($query4, $queryParams4);
							if (is_object($result4)) {
								$result4 = $result4->result("array");
								if (count($result4) > 0) $res2["Localize"] = $result4[0]["Diag_FullName"];
							}
						}
						$fflag2 = (($res2["flag"] == 1) ? '<div class="v_ok"></div>' : '<div class="v_no"></div>');
						if (strpos($res2["Localize"], "GMT+") > 1) {
							$res2["Localize"] = $callObject->peopleDate($res2["Localize"]);
						}
						$content .= '<div class="innerwrapper">' . $res2["ComboName"] . " " . $fflag2 . "<u>" . $res2["Localize"] . "</u></div>";
					}
				}
			}
		}
		$content .= '</div>';
		return $content;
	}

    /**
	 * @param CmpCallCard_model $callObject
	 * @param $CmpCloseCard
	 * @param $SysName
	 * @return bool|string
	 */
	public static function getComboRelEMK(CmpCallCard_model $callObject, $CmpCloseCard, $SysName)
	{
		$selectString = "
			CMB.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\",
		    CMB.CmpCloseCardCombo_Code as \"CmpCloseCardCombo_Code\",
		    CMB.ComboName as \"ComboName\"
		";
		$whereString = "Parent_id = '0' and ComboSys = :combo_id";
		$query = "
			select {$selectString}
			from {$callObject->comboSchema}.v_CmpCloseCardCombo CMB
			where {$whereString}
		";
		$queryParams = ["combo_id" => $SysName];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		if (count($res) == 0) {
			return false;
		}
		$comboid = $res[0]["CmpCloseCardCombo_id"];
		$selectString = "
			Ccombo.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\",
			Ccombo.CmpCloseCardCombo_Code as \"CmpCloseCardCombo_Code\",
			CCombo.ComboName as \"ComboName\",
			RL.Localize as \"Localize\",
		    case when coalesce(RL.CmpCloseCardRel_id,0) = 0 then 0 else 1 end as \"flag\"
		";
		$fromString = "
			{$callObject->comboSchema}.v_CmpCloseCardCombo CCombo (nolock)
			left join {$callObject->schema}.v_CmpCloseCardRel RL on RL.CmpCloseCard_id = :CmpCloseCard_id and RL.CmpCloseCardCombo_id = CCombo.CmpCloseCardCombo_id
		";
		$whereString = "CCombo.Parent_id = :ComboId";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		$queryParams = [
			"CmpCloseCard_id" => $CmpCloseCard,
			"ComboId" => $comboid
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		$content = "";
		foreach ($result as $res) {
			if ($res["CmpCloseCardCombo_Code"] == "111" && (int)$res["Localize"] > 0) {
				$query3 = "
					select L.Lpu_Name as \"Lpu_Name\"
					from v_Lpu as L
					where L.Lpu_id = :Lpuid
				";
				$queryParams3 = ["Lpuid" => $res["Localize"]];
				$result3 = $callObject->db->query($query3, $queryParams3);
				if (is_object($result3)) {
					$result3 = $result3->result("array");
					if (count($result3) > 0) $res["Localize"] = $result3[0]["Lpu_Name"];
				}
			}
			if ($res["flag"] == 1) $content = $res["ComboName"] . " <u>" . $res["Localize"] . "</u>";
		}
		return $content;
	}

	/**
	 * Возвращает идентификатор из справочника статусов бригад по его коду
	 * @param CmpCallCard_model $callObject
	 * @param $emergencyTeamStatus_id
	 * @return mixed|bool
	 */
	public static function getCmpCallCardEventTypeIdByEmergencyTeamStatusId(CmpCallCard_model $callObject, $emergencyTeamStatus_id)
	{
		$sql = "
			select CmpCallCardEventType_id as \"CmpCallCardEventType_id\"
			from v_EmergencyTeamStatus
			where EmergencyTeamStatus_id=:EmergencyTeamStatus_id
			limit 1
		";
		$sqlParams = ["EmergencyTeamStatus_id" => $emergencyTeamStatus_id];
		/**@var CI_DB_result $query */
		$query = $callObject->db->query($sql, $sqlParams);
		if (!is_object($query)) {
			return false;
		}
		$result = $query->first_row("array");
		return $result["CmpCallCardEventType_id"];
	}

	/**
	 * Запрос на выборку параметров карты для последующей обработки
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return bool|mixed
	 */
	public static function getCardParamsForEvent(CmpCallCard_model $callObject, $data)
	{
		$backToHospitalTime = ($callObject->regionNick == "kz")
			?"to_char(ClCCC.ToHospitalTime, '{$callObject->dateTimeForm104}')||' '||to_char(ClCCC.ToHospitalTime, '{$callObject->dateTimeForm108}') as \"ToHospitalTime\""
			:"to_char(ClCCC.BackTime, '{$callObject->dateTimeForm104}')||' '||to_char(ClCCC.BackTime, '{$callObject->dateTimeForm108}') as \"BackTime\"";
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				CCC.CmpCallCard_rid as \"CmpCallCard_rid\",
				CCC.CmpCallCardStatus_id as \"CmpCallCardStatus_id\",
				CCC.LpuBuilding_id as \"LpuBuilding_id\",
				CCC.LpuSection_id as \"LpuSection_id\",
				CCC.EmergencyTeam_id as \"EmergencyTeam_id\",
				CCC.Lpu_ppdid as \"Lpu_ppdid\",
				CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
				ET.EmergencyTeamStatusHistory_id as \"EmergencyTeamStatusHistory_id\",
				ETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\",
				CCT.CmpCallType_Code as \"CmpCallType_Code\",
				{$backToHospitalTime}
			from
				v_CmpCallCard CCC
				left join v_EmergencyTeam ET on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join v_EmergencyTeamStatus ETS on ET.EmergencyTeamStatus_id = ETS.EmergencyTeamStatus_id
				left join v_CmpCloseCard ClCCC on CCC.CmpCallCard_id = ClCCC.CmpCallCard_id
				left join v_CmpCallType CCT on CCC.CmpCallType_id = CCT.CmpCallType_id
			where CCC.CmpCallCard_id = :CmpCallCard_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (!is_array($result) || count($result) == 0) {
			return false;
		}
		return $result[0];
	}

	/**
	 * Возвращает дополнительную информацию по карте вызова
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getAdditionalCallCardInfo(CmpCallCard_model $callObject, $data)
	{
		$selectString = "
			case
				when CCrT.CmpCallerType_id is not null then 'Вызывает: '||CCrT.CmpCallerType_Name
				when CCC.CmpCallCard_Ktov is not null then 'Вызывает: '||CCC.CmpCallCard_Ktov
				else ''
			end||case when CCC.CmpCallCard_Telf is not null then 'Телефон: '||CCC.CmpCallCard_Telf else '' end as CallerInfo,
			coalesce(CCC.Sex_id,0) as \"SexId\",
		    case
		        when datediff('yy', coalesce(PS.Person_BirthDay, coalesce(CCC.Person_BirthDay,'01.01.2000')), tzgetdate()) > 1 then AgeTypeValue.CmpCloseCardCombo_id
				else
				    case
				        when datediff('mm', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), tzgetdate()) > 1 then AgeTypeValue.CmpCloseCardCombo_id + 1
						else AgeTypeValue.CmpCloseCardCombo_id + 2
				    end
			end as \"AgeTypeValue\",
		    case
		        when datediff('yy', coalesce(PS.Person_BirthDay, coalesce(CCC.Person_BirthDay,'01.01.2000')), tzgetdate()) > 1 then
					case when coalesce(PS.Person_BirthDay, coalesce(CCC.Person_BirthDay, 0)) = 0 then ''
					else datediff('yy', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), tzgetdate()) end
				else
				    case
				        when datediff('mm', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), tzgetdate()) > 1 then datediff('mm', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), tzgetdate())
						else datediff('dd', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), tzgetdate())
				    end
			end as \"Age\"
		";
		$fromString = "
			v_CmpCallCard CCC
			left join v_PersonState PS on PS.Person_id = CCC.Person_id
			left join v_CmpCallerType CCrT on CCrT.CmpCallerType_id=CCC.CmpCallerType_id
			left join lateral (
				select CCCC.CmpCloseCardCombo_id
				from {$callObject->comboSchema}.v_CmpCloseCardCombo CCCC
				where CCCC.Parent_id = 218
				order by CCCC.CmpCloseCardCombo_id
			    limit 1
			) as AgeTypeValue on true
		";
		$whereString = "CCC.CmpCallCard_id = :CmpCallCard_id";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		$sqlParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return mixed|bool
	 */
	public static function getCmpCallCardNgod(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["CmpCallCard_id"])) {
			return false;
		}
		$query = "
			select CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
			from v_CmpCallCard CCC
			where CCC.CmpCallCard_id = :CmpCallCard_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$retrun = $result->result("array");
		return $retrun[0]["CmpCallCard_Ngod"];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuAddressTerritory(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["Lpu_id"])) {
			return false;
		}
		$query = "
			select
                coalesce(RGN.KLRGN_id, '0') as \"KLRGN_id\",
				coalesce(SRGN.KLSubRGN_id, '0') as \"KLSubRGN_id\",
				coalesce(City.KLCity_id, '0') as \"KLCity_id\",
				coalesce(Town.KLTown_id, '0') as \"KLTown_id\"
			from
				v_Lpu Lpu
				left join v_OrgServiceTerr OST on OST.Org_id = Lpu.Org_id
				left join v_KLRgn RGN on RGN.KLRgn_id = OST.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = OST.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = OST.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = OST.KLTown_id
			where Lpu.Lpu_id = :Lpu_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getDispatchCallUsers(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["Lpu_id"])) {
			return false;
		}
		if ($data["NotDubli"] == "true") {
			$query = "
				select
					PM.pmUser_Name as \"pmUser_id\",
					PM.pmUser_Name as \"pmUser_Name\"
				from pmusercache PM
				where PM.pmUser_groups like '%{\"name\":\"smpcalldispath\"}%'
				  and PM.Lpu_id = :Lpu_id
				  group by PM.pmUser_Name
			";
		} else {
			$query = "
				select
					PM.pmUser_id as \"pmUser_id\",
					PM.pmUser_Name as \"pmUser_Name\"
				from pmusercache PM
				where PM.pmUser_groups like '%{\"name\":\"smpcalldispath\"}%'
				  and PM.Lpu_id = :Lpu_id
			";
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getAddressForNavitel(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["CmpCallCard_id"])) {
			return false;
		}
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
			    coalesce(RGN.KLRgn_FullName, '')||
					coalesce(', '||SRGN.KLSubRgn_FullName||' район', '')||
					coalesce(' город '||City.KLCity_Name, '')||
					coalesce(', '||Town.KLTown_FullName, '')||
					coalesce(', улица '||Street.KLStreet_Name, '')||
					coalesce(', дом '||CCC.CmpCallCard_Dom, '')
			    as \"Address_Name\"
			from
				v_CmpCallCard CCC
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
			where CCC.CmpCallCard_id = :CmpCallCard_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getAddressForOsmGeocode(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["CmpCallCard_id"])) {
			return false;
		}
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
			    coalesce(RGN.KLRgn_FullName, '') as \"Rgn_Name\",
				coalesce(City.KLCity_Name, coalesce(Town.KLTown_FullName, '')) as \"City_Name\",
				coalesce(Street.KLStreet_Name, '') as \"Street_Name\",
				coalesce(CCC.CmpCallCard_Dom, '') as \"House_Name\"
			from
				v_CmpCallCard CCC
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
			where CCC.CmpCallCard_id = :CmpCallCard_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Возвращает адрес из талона вызова, в т.ч. неформализованные
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCmpCallCardAddress(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["CmpCallCard_id"]) || !$data["CmpCallCard_id"]) {
			return false;
		}
		$sql = "
			select
				coalesce(RGN.KLRgn_FullName, '') as \"Rgn_Name\",
				coalesce(City.KLCity_Name, coalesce(Town.KLTown_FullName, '')) as \"City_Name\",
				coalesce(Street.KLStreet_Name, '') as \"Street_Name\",
				coalesce(CCC.CmpCallCard_Dom, '') as \"House_Name\",
				UnformalizedAddressDirectory_Name as \"UName\",
				UnformalizedAddressDirectory_lat as \"ULat\",
				UnformalizedAddressDirectory_lng as \"ULng\",
				coalesce(URGN.KLRgn_FullName, '') as \"URgn_Name\",
				coalesce(UCITY.KLCity_Name, coalesce(UTOWN.KLTown_FullName, '')) as \"UCity_Name\",
				coalesce(USTREET.KLStreet_Name, '') as \"UStreet_Name\",
				coalesce(UAD.UnformalizedAddressDirectory_Dom, '') as \"UHouse_Name\"
			from
				v_CmpCallCard CCC
				left join v_KLRgn RGN on RGN.KLRgn_id=CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id=CCC.KLSubRgn_id
				left join v_KLCity CITY on CITY.KLCity_id=CCC.KLCity_id
				left join v_KLTown TOWN on TOWN.KLTown_id=CCC.KLTown_id
				left join v_KLStreet STREET on STREET.KLStreet_id=CCC.KLStreet_id
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id=CCC.UnformalizedAddressDirectory_id
				left join v_KLRgn URGN on URGN.KLRgn_id=UAD.KLRgn_id
				left join v_KLSubRgn USRGN on USRGN.KLSubRgn_id=UAD.KLSubRgn_id
				left join v_KLCity UCITY on UCITY.KLCity_id=UAD.KLCity_id
				left join v_KLTown UTOWN on UTOWN.KLTown_id=UAD.KLTown_id
				left join v_KLStreet USTREET on USTREET.KLStreet_id=UAD.KLStreet_id
			where CCC.CmpCallCard_id=:CmpCallCard_id
		";
		/**@var CI_DB_result $result */
		$sqlParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getUnformalizedAddressStreetKladrParams(CmpCallCard_model $callObject, $data)
	{
		if (
			!isset($data["administrative_area_level_1"]) || !$data["administrative_area_level_1"] ||
			!isset($data["administrative_area_level_2"]) || !$data["administrative_area_level_2"] ||
			!isset($data["route"]) || !$data["route"] ||
			!isset($data["street_number"]) || !$data["street_number"]
		) {
			return false;
		}
		$addresses = [
			"administrative_area_level_1" => $data["administrative_area_level_1"],
			"administrative_area_level_2" => $data["administrative_area_level_2"],
			"route" => $data["route"],
			"street_number" => $data["street_number"]
		];
		//Первый вариант
		$querySocr = "
			select KLS.KLSocr_Name as \"KLSocr_Name\"
			from v_KLSocr KLS
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($querySocr);
		if (!is_object($result)) {
			return false;
		}
		$resultSocr = $result->result("array");
		$resultSocr = toUTFR($resultSocr);
		$addresses = toUTFR($addresses);
		foreach ($resultSocr as $socr) {
			foreach ($addresses as $type => $comp) {
				$addresses["$type"] = mb_strtoupper(preg_replace('#\s?' . $socr['KLSocr_Name'] . '\s?#ui', "", $comp));
			}
		}
		$addresses = toAnsiR($addresses);
		//Получаем код территории
		$queryTerritoryParams = ["KLArea_Name" => $addresses["administrative_area_level_1"]];
		$queryTerritory = "
			select KLA.KLArea_id as \"KLArea_id\"
			from v_KLArea KLA
			where KLA.KLArea_Name like :KLArea_Name
			  and KLAreaLevel_id = 1
			";
		$result = $callObject->db->query($queryTerritory, $queryTerritoryParams);
		if (!is_object($result)) {
			return false;
		}
		$resultTerritory = $result->result("array");
		if (sizeof($resultTerritory) != 1) {
			return false;
		}
		//Получаем код города
		$querySubTerritoryParams = [
			"KLArea_Name" => $addresses["administrative_area_level_2"],
			"KLArea_pid" => $resultTerritory[0]["KLArea_id"]
		];
		$querySubTerrytory = "
			select KLA.KLArea_id as \"KLArea_id\"
			from v_KLArea KLA
			where KLA.KLArea_Name like :KLArea_Name
			  and KLA.KLArea_pid = :KLArea_pid
			  and KLA.KLAreaLevel_id = 3
		";
		$result = $callObject->db->query($querySubTerrytory, $querySubTerritoryParams);
		if (!is_object($result)) {
			return false;
		}
		$resultSubTerritory = $result->result("array");
		if (sizeof($resultSubTerritory) != 1) {
			return false;
		}
		//Получаем код улицы
		$queryStreetParams = [
			"KLStreet_Name" => $addresses["route"],
			"KLArea_pid" => $resultSubTerritory[0]["KLArea_id"]
		];
		$queryStreet = "
			select KLS.KLStreet_id as \"KLStreet_id\"
			from v_KLStreet KLS
			where KLS.KLStreet_Name like :KLStreet_Name
			  and KLS.KLArea_id = :KLArea_pid
			";
		$result = $callObject->db->query($queryStreet, $queryStreetParams);
		if (!is_object($result)) {
			return false;
		}
		$resultStreet = $result->result("array");
		if (sizeof($resultStreet) != 1) {
			return false;
		}
		return [[
			"success" => true,
			"KL" => $resultTerritory[0]["KLArea_id"],
			"KLCity_id" => $resultSubTerritory[0]["KLArea_id"],
			"KLStreet_id" => $resultStreet[0]["KLStreet_id"]
		]];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getDecigionTree(CmpCallCard_model $callObject, $data)
	{
		$CurArmType = (!empty($data["session"]["CurArmType"]) ? $data["session"]["CurArmType"] : "");
		if (!in_array($CurArmType, array("dispcallnmp", "dispdirnmp", "dispnmp"))) {
			$callObject->load->model("CmpCallCard_model4E", "CmpCallCard_model4E");
			$OperDepartamentOptions = $callObject->CmpCallCard_model4E->getOperDepartamentOptions($data);
			if ($OperDepartamentOptions && $OperDepartamentOptions["LpuBuildingType_id"] == 28) {
				$data["Lpu_id"] = $OperDepartamentOptions["Lpu_id"];
			};
		}
		if (!isset($data["Lpu_id"])) {
			throw new Exception("Не задан обязательный параметр: идентификатор ЛПУ");
		}
		$query = "
			select
				ADT.AmbulanceDecigionTree_id as \"AmbulanceDecigionTree_id\",
				ADT.AmbulanceDecigionTree_nodeid as \"AmbulanceDecigionTree_nodeid\",
				ADT.AmbulanceDecigionTree_nodepid as \"AmbulanceDecigionTree_nodepid\",
				ADT.AmbulanceDecigionTree_Type as \"AmbulanceDecigionTree_Type\",
				ADT.AmbulanceDecigionTree_Text as \"AmbulanceDecigionTree_Text\",
				ADT.CmpReason_id as \"CmpReason_id\"
			from v_AmbulanceDecigionTree ADT
			where coalesce(ADT.Lpu_id, 0) = :Lpu_id
			order by ADT.AmbulanceDecigionTree_nodeid
		";

		if (!in_array($CurArmType, ['dispcallnmp', 'dispdirnmp', 'dispnmp'])) {
			$params = [
				'Lpu_id'=>$data['Lpu_id']
			];
		} else {
			if (empty($data['session']['CurARM']['MedService_id'])) {
				$params = [
					'Lpu_id'=>$data['Lpu_id']
				];
			} else {
				$params = [
					'Lpu_id' => $callObject->getNMPLpu($data)
				];
			}
		}

		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result_count = $callObject->db->query(getCountSQL($query), $data);
		$count = 0;
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			if (is_array($cnt_arr) && isset($cnt_arr[0]) && isset($cnt_arr[0]["cnt"])) {
				$count = $cnt_arr[0]["cnt"];
			}
			unset($cnt_arr);
		}
		$params["Lpu_id"] = ($count == 0) ? 0 : $params["Lpu_id"];
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Возвращает массив ID МО выбранных в АРМ
	 * @return array|bool
	 */
	public static function getSelectedLpuId()
	{
		$user = pmAuthUser::find($_SESSION["login"]);
		$settings = unserialize($user->settings);
		if (!isset($settings["lpuWorkAccess"]) || !is_array($settings["lpuWorkAccess"]) || $settings["lpuWorkAccess"][0] == "") {
			return false;
		}
		return $settings["lpuWorkAccess"];
	}

	/**
	 * Получение списка подстанций СМП
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCmpCallPlaces(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				CCPT.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CCPT.CmpCallPlaceType_Name as \"CmpCallPlaceType_Name\",
				CCPT.CmpCallPlaceType_Code as \"CmpCallPlaceType_Code\",
				case when (coalesce(CmpUrgencyAndProfileStandartRefPlace_id,0) = 0) then 'false' else 'true' end as is_checked
			from
				v_CmpCallPlaceType CCPT
				left join v_CmpUrgencyAndProfileStandartRefPlace CUPSRF on CUPSRF.CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id and CUPSRF.CmpCallPlaceType_id = CCPT.CmpCallPlaceType_id
			order by
				case when isnumeric(CCPT.CmpCallPlaceType_Code||'e0') = 1
				    then right('000'||CCPT.CmpCallPlaceType_Code, 3)
				    else CCPT.CmpCallPlaceType_Code
				end;
		";
		$queryParams = ["CmpUrgencyAndProfileStandart_id" => (empty($data["CmpUrgencyAndProfileStandart_id"])) ? 0 : $data["CmpUrgencyAndProfileStandart_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return [
			"data" => $result,
			"totalCount" => sizeof($result)
		];
	}

	/**
	 * Получение справочника нормативов назначения профилей бригад и срочности вызова
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getCmpUrgencyAndProfileStandart(CmpCallCard_model $callObject, $data)
	{
		$accFilter = '(1=1)';

		if (!empty($data['Lpu_id'])) {
			$accFilter .= ' and CUPS.Lpu_id = :Lpu_id';
		}

		if (!empty($data['CmpCallCardAcceptor_id'])) {
			$accFilter .= ' and CCCA.CmpCallCardAcceptor_id in (:CmpCallCardAcceptor_id)';
		}
		$withString = "
			with CUPS_A as (
			    select
					CUPS.Lpu_id,
			        CUPS.CmpUrgencyAndProfileStandart_id,
			        CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf,
			        CUPS.CmpUrgencyAndProfileStandart_Urgency,
			        CUPS.CmpReason_id,
			        CR.CmpReason_Code,
			        CCCA.CmpCallCardAcceptor_id,
			        CCCA.CmpCallCardAcceptor_Code,
			        CCCA.CmpCallCardAcceptor_Name,
			        CUPS.CmpUrgencyAndProfileStandart_HeadDoctorObserv,
			        CASE WHEN coalesce(CUPS.CmpUrgencyAndProfileStandart_HeadDoctorObserv, 1) = 1 THEN 'Нет' ELSE 'Да' END CmpUrgencyAndProfileStandart_HeadDoctorObserv_YesNo,
			        CUPS.CmpUrgencyAndProfileStandart_MultiVictims,
			        CASE WHEN coalesce(CUPS.CmpUrgencyAndProfileStandart_MultiVictims, 1) = 1 THEN 'Нет' ELSE 'Да' END CmpUrgencyAndProfileStandart_MultiVictims_YesNo
			    from
			        v_CmpUrgencyAndProfileStandart CUPS
			        left join v_CmpReason CR on CUPS.CmpReason_id = CR.CmpReason_id
			        left join v_CmpCallCardAcceptor CCCA on CCCA.CmpCallCardAcceptor_id=CUPS.CmpCallCardAcceptor_id
			    where {$accFilter}
			), CCP as (
			    select
			        CUPS_A.CmpUrgencyAndProfileStandart_id,
			        overlay(
			            (
			                select ' '||CCPT.CmpCallPlaceType_Code
			                from
			                    v_CmpUrgencyAndProfileStandartRefPlace CUPSRP
								left join v_CmpCallPlaceType CCPT on CCPT.CmpCallPlaceType_id = CUPSRP.CmpCallPlaceType_id
							where CUPSRP.CmpUrgencyAndProfileStandart_id = CUPS_A.CmpUrgencyAndProfileStandart_id
						) placing '' from 1 for 1
					) as CmpUrgencyAndProfileStandart_PlaceSequence
			    from CUPS_A
			), CUPSRSP_A as (
			    select
			        CUPS_A.CmpUrgencyAndProfileStandart_id,
			        overlay(
			            (
			                select
			                    case when coalesce(PREV.ProfilePriority, 0) = coalesce(CUPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority, 0)
			                        then
			                            case when coalesce(PREV.ProfilePriority, 0) = 0
			                                then ' '||ETS.EmergencyTeamSpec_Code
			                                else ' + '||ETS.EmergencyTeamSpec_Code
			                            end
			                        else
			                            case when coalesce(CUPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority, 0) = 0
			                            then ' - '||ETS.EmergencyTeamSpec_Code
			                            else ' '||ETS.EmergencyTeamSpec_Code
			                            end
			                        end
			                from
			                    v_CmpUrgencyAndProfileStandartRefSpecPriority CUPSRSP
			                    left join v_EmergencyTeamSpec ETS on CUPSRSP.EmergencyTeamSpec_id = ETS.EmergencyTeamSpec_id
			                    left join lateral (
			                        select CUPSRSP_PREV.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority as ProfilePriority
			                        from v_CmpUrgencyAndProfileStandartRefSpecPriority CUPSRSP_PREV
			                        where CUPSRSP_PREV.CmpUrgencyAndProfileStandartRefSpecPriority_id = CUPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_id - 1
			                        limit 1
			                    ) as PREV on true
			                where CUPSRSP.CmpUrgencyAndProfileStandart_id = CUPS_A.CmpUrgencyAndProfileStandart_id
			                order by coalesce(CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority, 999)
						) placing '' from 1 for 1
			        ) as CmpUrgencyAndProfileStandart_ProfileSequence
			    from CUPS_A
			)
		";
		$selectString = "
		    CUPS_A.Lpu_id as \"Lpu_id\",
		    CUPS_A.CmpUrgencyAndProfileStandart_id as \"CmpUrgencyAndProfileStandart_id\",
		    CUPS_A.CmpUrgencyAndProfileStandart_UntilAgeOf as \"CmpUrgencyAndProfileStandart_UntilAgeOf\",
		    CUPS_A.CmpUrgencyAndProfileStandart_Urgency as \"CmpUrgencyAndProfileStandart_Urgency\",
		    CUPS_A.CmpCallCardAcceptor_id as \"CmpCallCardAcceptor_id\",
		    CUPS_A.CmpCallCardAcceptor_Code as \"CmpCallCardAcceptor_Code\",
		    CUPS_A.CmpCallCardAcceptor_Name as \"CmpCallCardAcceptor_Name\",
		    CUPS_A.CmpUrgencyAndProfileStandart_HeadDoctorObserv as \"CmpUrgencyAndProfileStandart_HeadDoctorObserv\",
		    CUPS_A.CmpUrgencyAndProfileStandart_HeadDoctorObserv_YesNo as \"CmpUrgencyAndProfileStandart_HeadDoctorObserv_YesNo\",
		    CUPS_A.CmpUrgencyAndProfileStandart_MultiVictims as \"CmpUrgencyAndProfileStandart_MultiVictims\",
		    CUPS_A.CmpUrgencyAndProfileStandart_MultiVictims_YesNo as \"CmpUrgencyAndProfileStandart_MultiVictims_YesNo\",
		    CUPS_A.CmpReason_id as \"CmpReason_id\",
		    CUPS_A.CmpReason_Code as \"CmpReason_Code\",
		    CCP.CmpUrgencyAndProfileStandart_PlaceSequence as \"CmpUrgencyAndProfileStandart_PlaceSequence\",
		    CUPSRSP_A.CmpUrgencyAndProfileStandart_ProfileSequence as \"CmpUrgencyAndProfileStandart_ProfileSequence\"
		";
		$fromString = "
		    CUPS_A
		    left join CUPSRSP_A on CUPS_A.CmpUrgencyAndProfileStandart_id = CUPSRSP_A.CmpUrgencyAndProfileStandart_id
		    left join CCP on CUPS_A.CmpUrgencyAndProfileStandart_id = CCP.CmpUrgencyAndProfileStandart_id
		";
		$orderByString = "CUPS_A.CmpReason_id";
		$query = "
			{$withString}
			select {$selectString}
			from {$fromString}
			order by {$orderByString}
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $callObject->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $data);
		$result_count = $callObject->db->query(getCountSQLPH($query), $data);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			return false;
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * Получене списка мест, привязанных к правилу
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getCmpUrgencyAndProfileStandartPlaces(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["CmpUrgencyAndProfileStandart_id"])) {
			throw new Exception("Не задан обязательный параметр: идентификатор правила");
		}
		$query = "
			select
				CCPT.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CCPT.CmpCallPlaceType_Name as \"CmpCallPlaceType_Name\",
				CCPT.CmpCallPlaceType_Code as \"CmpCallPlaceType_Code\"
			from
				v_CmpUrgencyAndProfileStandartRefPlace CUPSRF
				left join v_CmpCallPlaceType CCPT on CUPSRF.CmpCallPlaceType_id = CCPT.CmpCallPlaceType_id
			where CUPSRF.CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id
		";
		$queryParams = ["CmpUrgencyAndProfileStandart_id" => $data["CmpUrgencyAndProfileStandart_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получене списка мест, привязанных к правилу
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCmpUrgencyAndProfileStandartSpecPriority(CmpCallCard_model $callObject, $data)
	{
		$queryParams = [];
		if (empty($data["CmpUrgencyAndProfileStandart_id"])) {
			$query = "
				select
					ETS.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
					ETS.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
					ETS.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
					1 as \"ProfilePriority\"
				from v_EmergencyTeamSpec ETS
			";
		} else {
			$queryParams["CmpUrgencyAndProfileStandart_id"] = $data["CmpUrgencyAndProfileStandart_id"];
			$query = "
				select
					ETS.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
					ETS.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
					ETS.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
					CUPSSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority as \"ProfilePriority\"
				from
					v_CmpUrgencyAndProfileStandart CUPS
					left join v_CmpUrgencyAndProfileStandartRefSpecPriority CUPSSP on CUPSSP.CmpUrgencyAndProfileStandart_id= CUPS.CmpUrgencyAndProfileStandart_id
					left join v_EmergencyTeamSpec ETS on CUPSSP.EmergencyTeamSpec_id = ETS.EmergencyTeamSpec_id
				where CUPS.CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id
			";
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получения ID комбобокса по его коду
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function getComboIdByCode(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select CMB.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\"
			from v_CmpCloseCardCombo CMB
			where CMB.CmpCloseCardCombo_Code = :CmpCloseCardCombo_Code
		";
		$queryParams = ["CmpCloseCardCombo_Code" => $data];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		$ComboRetrun = $result->result("array");
		if (sizeof($ComboRetrun)) {
			return $ComboRetrun[0]["CmpCloseCardCombo_id"];
		}
		return false;
	}

	/**
	 * Получение ID комбобокса по ComboSys
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return mixed|bool
	 */
	public static function getComboIdByComboSys(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["ComboSys"])) {
			return false;
		}
		$query = "
			select CMB.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\"
			from v_CmpCloseCardCombo CMB
			where CMB.ComboSys = :ComboSys
		";
		$queryParams = ["ComboSys" => $data["ComboSys"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		$ComboRetrun = $result->result("array");
		if (sizeof($ComboRetrun)) {
			return $ComboRetrun[0]["CmpCloseCardCombo_id"];
		}
		return false;
	}

	/**
	 * Получение списка подстанций СМП
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCmpCallDiagnosesFields(CmpCallCard_model $callObject, $data)
	{
		$selectString = "
			ClCCC.Diag_id as \"Diag_id\",
			D.Diag_FullName as \"d_name\",
			ClCCC.Diag_uid as \"Diag_uid\",
			DU.Diag_FullName as \"du_name\",
			ClCCC.Diag_sid as \"Diag_sid\",
			DS.Diag_FullName as \"ds_name\",
			CCC.Lpu_hid as \"Lpu_hid\",
			LB.Lpu_Nick as \"mh_name\",
			CR.CmpResult_id as \"CmpResult_id\",
			coalesce(CR.CmpResult_Name, CRCB.ComboName) as \"cr_name\"
		";
		$fromString = "
			v_CmpCallCard CCC
			left join {$callObject->schema}.v_CmpCloseCard ClCCC on CCC.CmpCallCard_id = ClCCC.CmpCallCard_id
			left join v_Diag D on ClCCC.Diag_id = D.Diag_id
			left join v_Diag DU on ClCCC.Diag_uid = DU.Diag_id
			left join v_Diag DS on ClCCC.Diag_sid = DS.Diag_id
			left join v_CmpResult CR on ClCCC.CmpResult_id = CR.CmpResult_id
			left join lateral (
				select CLCCB.ComboName
				from
			    	{$callObject->schema}.v_CmpCloseCardRel CLCR
					left join {$callObject->comboSchema}.v_CmpCloseCardCombo CLCCB on CLCCB.CmpCloseCardCombo_id = CLCR.CmpCloseCardCombo_id
					left join {$callObject->comboSchema}.v_CmpCloseCardCombo pCLCCB on pCLCCB.CmpCloseCardCombo_id = CLCCB.Parent_id
				where CLCR.CmpCloseCard_id = ClCCC.CmpCloseCard_id
			      and pCLCCB.CmpCloseCardCombo_Code = 223
			    limit 1
			) as CRCB on true
			left join v_Lpu LB on CCC.Lpu_hid = LB.Lpu_id
		";
		$whereString = "CCC.CmpCallCard_id = :CmpCallCard_id";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		$sqlParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * Функция используется для доп.аутентификации пользователя при socket-соединении NodeJS для армов СМП
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getPmUserInfo(CmpCallCard_model $callObject, $data)
	{
		$callObject->load->model("CmpCallCard_model4E", "CmpCallCard_model4E");
		$OperDepartament = $callObject->CmpCallCard_model4E->getOperDepartament($data);
		return [
			[
				"pmuser_id" => $data["pmUser_id"],
				"Lpu_id" => $data["Lpu_id"],
				"CurMedService_id" => isset($data["session"]["CurMedService_id"]) ? $data["session"]["CurMedService_id"] : null,
				"OperDepartament" => isset($OperDepartament["LpuBuilding_pid"]) ? $OperDepartament["LpuBuilding_pid"] : null
			]
		];
	}

	/**
	 * Получение списка параметров хранимой процедуры
	 * @param CmpCallCard_model $callObject
	 * @param $sp
	 * @param $schema
	 * @return array|bool
	 */
	public static function getStoredProcedureParamsList(CmpCallCard_model $callObject, $sp, $schema)
	{
		//TODO 111
		$query = "
			SELECT 
                name as \"name\" 
            FROM (
            SELECT 
                   unnest(proargnames) as name
            FROM pg_proc p
                 LEFT OUTER JOIN pg_description ds ON ds.objoid = p.oid
                 INNER JOIN pg_namespace n ON p.pronamespace = n.oid
            WHERE p.proname = :name AND
                  n.nspname = :schema
            ) t
            WHERE t.name not in ('pmuser_id', 'error_code', 'error_message', 'isreloadcount')
		";
		$queryParams = ["name" => $sp, "schema" => $schema];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$outputData = [];
		$response = $result->result("array");
		foreach ($response as $row) {
			$outputData[] = str_replace("@", "", $row["name"]);
		}
		return $outputData;
	}

	/**
	 * Получение идентификатора типа документа по коду
	 * @param CmpCallCard_model $callObject
	 * @param $object_name
	 * @param $code
	 * @return bool|float|int|string
	 */
	public static function getObjectIdByCode(CmpCallCard_model $callObject, $object_name, $code)
	{
		$schema = "dbo";
		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}
		$query = "
			select {$object_name}_id
			from {$schema}.{$object_name}
			where {$object_name}_Code = :code;
			limit 1
		";
		$queryParams = ["code" => $code];
		$result = $callObject->getFirstResultFromQuery($query, $queryParams);
		return $result && $result > 0 ? $result : false;
	}

	/**
	 * Получение следующего номера произвольного обьекта.
	 * @param CmpCallCard_model $callObject
	 * @param $object_name
	 * @param $num_field
	 * @return bool|float|int|string
	 */
	public static function getObjectNextNum(CmpCallCard_model $callObject, $object_name, $num_field)
	{
		$query = "
			select coalesce(max({$num_field}::int8), 0) + 1 as num
			from {$object_name}
			where length({$num_field}) <= 6
			  and coalesce(
			    (
					select case when strpos('.', {$num_field}) > 0 then 0 else 1 end
					where isnumeric({$num_field}||'e0') = 1
				), 0
			  ) = 1
		";
		$num = $callObject->getFirstResultFromQuery($query);
		return !empty($num) && $num > 0 ? $num : 0;
	}

	/**
	 * Поиск подходящего документа по заданнам параметрам. Если документ не найден - создается новый.
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return bool|float|int|mixed|string|null
	 * @throws Exception
	 */
	public static function getDocSMPForCmpCallCardDrug(CmpCallCard_model $callObject, $data)
	{
		$id = null;
		$type_id = $callObject->getObjectIdByCode("DrugDocumentType", (!empty($data["DrugDocumentType_Code"]) ? $data["DrugDocumentType_Code"] : 25)); //25 - Списание медикаментов со склада на пациента. СМП
		if (empty($type_id)) {
			return null;
		}
		$query = "
            select du.DocumentUc_id as \"DocumentUc_id\"
            from v_DocumentUc du
            where du.DrugDocumentType_id = :DrugDocumentType_id
              and du.DrugDocumentStatus_id = :DrugDocumentStatus_id
              and du.Contragent_sid = :Contragent_sid
              and du.Storage_sid = :Storage_sid
              and coalesce(du.StorageZone_sid, 0) = coalesce(:StorageZone_sid, 0)
              and coalesce(du.DrugFinance_id, 0) = coalesce(:DrugFinance_id, 0)
              and coalesce(du.WhsDocumentCostItemType_id, 0) = coalesce(:WhsDocumentCostItemType_id, 0)
              and du.DocumentUc_setDate = :DocumentUc_setDate
        ";
		$idParams = [
			"DrugDocumentType_id" => $type_id,
			"DrugDocumentStatus_id" => $callObject->getObjectIdByCode("DrugDocumentStatus", 1), //1 - Новый
			"Contragent_sid" => $data["Contragent_id"],
			"Storage_sid" => $data["Storage_id"],
			"StorageZone_sid" => (!empty($data["StorageZone_id"]) ? $data["StorageZone_id"] : null),
			"DrugFinance_id" => $data["DrugFinance_id"],
			"WhsDocumentCostItemType_id" => $data["WhsDocumentCostItemType_id"],
			"DocumentUc_setDate" => date("Y-m-d")
		];
		if ($type_id == 26 && !empty($data["StorageZoneLiable"])) {
			$idParams["DrugDocumentStatus_id"] = $callObject->getObjectIdByCode("DrugDocumentStatus", 4);
		}
		$id = $callObject->getFirstResultFromQuery($query, $idParams);
		if (empty($id)) {
			$docParams = [
				"DrugDocumentType_id" => $type_id,
				"DrugDocumentStatus_id" => $callObject->getObjectIdByCode("DrugDocumentStatus", 1), //1 - Новый
				"DocumentUc_Num" => $callObject->getObjectNextNum("DocumentUc", "DocumentUc_Num"),
				"DocumentUc_setDate" => date("Y-m-d"),
				"DocumentUc_didDate" => date("Y-m-d"),
				"Lpu_id" => $data["Lpu_id"],
				"Contragent_id" => $data["Contragent_id"],
				"Contragent_sid" => $data["Contragent_id"],
				"Org_id" => $data["Org_id"],
				"Storage_sid" => $data["Storage_id"],
				"StorageZone_sid" => (!empty($data["StorageZone_id"]) ? $data["StorageZone_id"] : null),
				"DrugFinance_id" => $data["DrugFinance_id"],
				"WhsDocumentCostItemType_id" => $data["WhsDocumentCostItemType_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			if ($type_id == 26) {
				$docParams["EmergencyTeam_id"] = (!empty($data["EmergencyTeam_id"]) ? $data["EmergencyTeam_id"] : null);
				if (!empty($data["StorageZoneLiable"])) {
					$docParams["DrugDocumentStatus_id"] = $callObject->getObjectIdByCode("DrugDocumentStatus", 4); // Исполнен
				}
			}
			$response = $callObject->saveObject("DocumentUc", $docParams);
			if (is_array($response) && !empty($response["DocumentUc_id"])) {
				$id = $response["DocumentUc_id"];
			}
		}
		return $id;
	}

	/**
	 * Получение значений по умолчанию для формы использования медикаментов
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function getCmpCallCardDrugDefaultValues(CmpCallCard_model $callObject, $data)
	{
		$query = "
             select
                msf.MedStaffFact_id as \"MedStaffFact_id\",
                et.LpuBuilding_id as \"LpuBuilding_id\",
                s.Storage_id as \"Storage_id\",
                m.Mol_id as \"Mol_id\",
                case when sz.StorageZone_id is not null then sz.StorageZone_id else sz_last.StorageZone_id end as \"StorageZone_id\"
            from
            	v_EmergencyTeam et
                left join lateral (
                    select i_msf.MedStaffFact_id
                    from
                        v_MedPersonal i_mp
                        left join v_MedStaffFact i_msf on i_msf.MedPersonal_id = i_mp.MedPersonal_id
                    where i_mp.MedPersonal_id = et.EmergencyTeam_HeadShift
                    order by i_msf.Person_Fio
                    limit 1
                ) as msf on true
                left join lateral (
                    select Storage_id
                    from v_StorageStructLevel i_ssl
                    where i_ssl.LpuBuilding_id = et.LpuBuilding_id
                    order by i_ssl.StorageStructLevel_id
                    limit 1
                ) as s on true
                left join lateral (
                    select Mol_id
                    from v_Mol i_m
                    where i_m.Storage_id = s.Storage_id
                    order by i_m.Mol_id
                    limit 1
                ) as m on true
				left join lateral (
                    select i_sz.StorageZone_id
                    from v_StorageZoneLiable i_sz
                    where i_sz.StorageZoneLiable_ObjectName = 'Бригада СМП'
                      and i_sz.StorageZoneLiable_ObjectId = et.EmergencyTeam_id
                      and i_sz.StorageZoneLiable_endDate is null
                    order by i_sz.StorageZone_id
				    limit 1
                ) as sz on true
				left join lateral (
                    select i_szl.StorageZone_id
                    from v_StorageZoneLiable i_szl
                    where i_szl.StorageZoneLiable_ObjectName = 'Бригада СМП'
                      and i_szl.StorageZoneLiable_ObjectId = et.EmergencyTeam_id
                      and i_szl.StorageZoneLiable_endDate is not null
                      and exists (
                        	select dsz.DrugStorageZone_id 
                        	from v_DrugStorageZone dsz 
                        	where dsz.StorageZone_id = i_szl.StorageZone_id
                        	  and dsz.DrugStorageZone_Count > 0
                      )
                    order by i_szl.StorageZoneLiable_endDate desc
             		limit 1
                ) as sz_last on true
            where EmergencyTeam_id = :EmergencyTeam_id;
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		$result[0]["success"] = true;
		return $result;
	}

	/**
	 * Получение из Wialon пройденного расстояния бригадой за промежуток времени
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getTheDistanceInATimeInterval(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["EmergencyTeam_id"])) {
			return false;
		}
		$callObject->load->model("GeoserviceTransport_model", "GeoserviceTransport_model");
		// узнаем использует ли служба геосервис «Wialon»
		$geoservis = $callObject->GeoserviceTransport_model->getGeoserviceType();
		if (count($geoservis) == 0 || $geoservis[0]["ApiServiceType_Name"] != "Wialon") {
			return false;
		}
		$callObject->load->model("EmergencyTeam_model4E", "ETModel");
		// получим Id транспорта бригады
		$emergencyTeamFields = $callObject->ETModel->loadEmergencyTeam(["EmergencyTeam_id" => $data["EmergencyTeam_id"]]);
		$transportID = $emergencyTeamFields[0]["GeoserviceTransport_id"];
		if (!$transportID) {
			return false;
		}
		$param = [
			"tarnsportID" => $transportID,
			"GoTime" => DateTime::createFromFormat("d.m.Y H:i", $data["GoTime"]),
			"EndTime" => DateTime::createFromFormat("d.m.Y H:i", $data["EndTime"])
		];
		$callObject->load->model("Wialon_model", "Wialon_model");
		try {
			$callObject->Wialon_model->init();
			$result = $callObject->Wialon_model->getTheDistanceTraveled($param);
			if (!$result) {
				return false;
			}
			return [
				"success" => true,
				"data" => floatval($result)
			];
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * получение списка полей в разделе Услуги для формы 110
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getUslugaFields(CmpCallCard_model $callObject, $data)
	{
		$countSql = "";
		$counParam = "null as \"CmpCallCardUsluga_Kolvo\",";
		$where = [];
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"acceptTime" => date("Y-m-d H:i:s", strtotime($data["acceptTime"]))
		];
		if (!empty($data["CmpCallCard_id"])) {
			$countSql = "
				left join lateral (
                    select cccu.CmpCallCardUsluga_Kolvo
					from v_CmpCallCardUsluga cccu
					where CCCU.CmpCallCard_id = :CmpCallCard_id
					  and uc.UslugaComplex_id = CCCU.UslugaComplex_id
					limit 1
				) as kolvo on true
			";
			$counParam = "kolvo.CmpCallCardUsluga_Kolvo as \"CmpCallCardUsluga_Kolvo\",";
			$params["CmpCallCard_id"] = $data["CmpCallCard_id"];
		}
		if (!empty($data["UslugaComplex_Code"])) {
			$where[] = "uc.UslugaComplex_Code = :UslugaComplex_Code";
			$params["UslugaComplex_Code"] = $data["UslugaComplex_Code"];
		}
		if (!empty($data["Lpu_id"])) {
			$where[] = "coalesce(Lpu.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id";
		}
		$where[] = "avis.AttributeVision_TableName = 'dbo.VolumeType'";
		$where[] = "vt.VolumeType_Code = 'UslugaSMP'";
		$where[] = "avis.AttributeVision_IsKeyValue = 2";
		$where[] = "coalesce(av.AttributeValue_begDate, :acceptTime) <= :acceptTime";
		$where[] = "coalesce(av.AttributeValue_endDate, :acceptTime) >= :acceptTime";
		$where[] = "coalesce(uc.UslugaComplex_begDT, :acceptTime) <= :acceptTime";
		$where[] = "coalesce(uc.UslugaComplex_endDT, :acceptTime) >= :acceptTime";
		$where[] = "coalesce( uc.UslugaComplex_Nick, uc.UslugaComplex_Name, 'none') != 'none'";
		$whereString = (count($where) != 0)?"where ".implode(" and ", $where) : "";
		$sql = "
			select
				av.AttributeValue_id as \"AttributeValue_id\",
                Lpu.Attribute_id as \"Attribute_id\",
                av.AttributeValue_ValueIdent as \"UslugaComplex_id\",
                coalesce( uc.UslugaComplex_Nick, uc.UslugaComplex_Name, null) as \"UslugaComplex_Name\",
				uc.UslugaCategory_id as \"UslugaCategory_id\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
                Lpu.AttributeValue_ValueIdent as \"Lpu_id\",
                {$counParam}
				case
					when a.AttributeValueType_id = 1 then av.AttributeValue_ValueInt::varchar
					when a.AttributeValueType_id = 2 then av.AttributeValue_ValueFloat::varchar
					when a.AttributeValueType_id = 3 then av.AttributeValue_ValueFloat::varchar
					when a.AttributeValueType_id = 4 then av.AttributeValue_ValueBoolean::varchar
					when a.AttributeValueType_id = 5 then av.AttributeValue_ValueString::varchar
					when a.AttributeValueType_id = 6 then av.AttributeValue_ValueIdent::varchar
					when a.AttributeValueType_id = 7 then to_char(av.AttributeValue_ValueDate, '{$callObject->dateTimeForm104}')
					when a.AttributeValueType_id = 8 then av.AttributeValue_ValueIdent::varchar
				end as \"AttributeValue_Value\",
				to_char(av.AttributeValue_begDate, '{$callObject->dateTimeForm104}') as \"AttributeValue_begDate\",
				to_char(av.AttributeValue_endDate, '{$callObject->dateTimeForm104}') as \"AttributeValue_endDate\",
				av.AttributeValue_ValueText as \"AttributeValue_ValueText\"
			from
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a on a.Attribute_id = av.Attribute_id
                left join v_UslugaComplex uc on uc.UslugaComplex_id = av.AttributeValue_ValueIdent
				left join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
				left join lateral (
					select
						t1.AttributeValue_ValueIdent,
					    t1.Attribute_id
					from
						v_AttributeValue t1
						inner join v_Attribute t2 on t2.Attribute_id = t1.Attribute_id
					where t1.AttributeValue_rid = av.AttributeValue_id
					  and t2.Attribute_SysNick = 'Lpu'
				    limit 1
				) as Lpu on true
				{$countSql}
			{$whereString}
			order by uc.UslugaComplex_Nick
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Возвращает поля экспертной оценки для карты закрытия вызова 110у
	 * @param CmpCallCard_model $callObject
	 * @return mixed|bool
	 */
	public static function getExpertResponseFields(CmpCallCard_model $callObject)
	{
		$query = "
			select
				CMPCloseCardExpertResponseType_id as \"ExpertResponseType_id\",
	            CMPCloseCardExpertResponseType_Name as \"ExpertResponseType_Name\"
			from v_CMPCloseCardExpertResponseType
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		$response["ExpertResponseTypes"] = $result->result("array");
		$sql = "
			select
				av.AttributeValue_id as \"AttributeValue_id\",
				av.AttributeValue_ValueString as \"AttributeValue_Value\",
				a.Attribute_id as \"Attribute_id\",
				a.Attribute_Code as \"Attribute_Code\",
				avchild.AttributeValue_ValueString as \"AttributeValue_Text\"
			from
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a on a.Attribute_id = av.Attribute_id
				left join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
				left join v_AttributeValue avchild on avchild.AttributeValue_rid = av.AttributeValue_id
			where avis.AttributeVision_TableName = 'dbo.VolumeType'
			  and vt.VolumeType_Code = 'CMPCloseCardExpResp'
			  and coalesce(av.AttributeValue_endDate, tzgetdate()) >= tzgetdate()
			  and avis.AttributeVision_IsKeyValue = 2
			order by av.AttributeValue_ValueString
		";
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		$response["Attributes"] = $res;
		return $response;
	}

	/**
	 * Возвращает оценки карты 110у
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getCmpCloseCardExpertResponses(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["CmpCloseCard_id"])) {
			return false;
		}
		$selectString = "
			CER.CMPCloseCardExpertResponse_id as \"CMPCloseCardExpertResponse_id\",
            CER.AttributeValue_id as \"AttributeValue_id\",
            CER.CMPCloseCardExpertResponseType_id as \"CMPCloseCardExpertResponseType_id\",
            CER.CMPCloseCardExpertResponse_Comment as \"CMPCloseCardExpertResponse_Comment\",
            	coalesce(MP.Person_SurName, '') || ' '||
                coalesce(case when rtrim(MP.Person_FirName) = 'null' then ' ' else substring(MP.Person_FirName, 1, 1)||'.' end, '')||
                coalesce(case when rtrim(MP.Person_SecName) = 'null' then ' ' else substring(MP.Person_SecName, 1, 1)||'.' end, '')
            as \"Person_FIO\",
            to_char(CER.CMPCloseCardExpertResponse_insDT, '{$callObject->dateTimeForm104}')||' '||to_char(CER.CMPCloseCardExpertResponse_insDT, '{$callObject->dateTimeForm108}') as \"ResponseDT\"
		";
		$fromString = "
			{$callObject->schema}.v_CMPCloseCardExpertResponse CER
			left join v_pmUser pmU on pmU.pmUser_id = CER.pmUser_updID
			left join v_Medpersonal MP on pmU.pmUser_Medpersonal_id = MP.MedPersonal_id
		";
		$whereString = "CER.CMPCloseCard_id = :CmpCloseCard_id";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $data);
		return $result->result("array");
	}

	/**
	 * Возвращает список федеральных результатов для карты 110у
	 * @param CmpCallCard_model $callObject
	 * @return array
	 */
	public static function getFedLeaveTypeList(CmpCallCard_model $callObject)
	{
		$sql = "
			select
				LeaveType_id as \"LeaveType_id\",
				LeaveType_Name as \"LeaveType_Name\",
				LeaveType_Code as \"LeaveType_Code\"
			from fed.v_LeaveType
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		return $result->result("array");
	}

	/**
	 * возвращает признак источника карты CmpCallCard
	 * @param CmpCallCard_model $callObject
	 * @param $cmpCallCardID
	 * @return bool|int
	 */
	public static function getCallCardInputTypeCode(CmpCallCard_model $callObject, $cmpCallCardID)
	{
		$query = "
			select CT.CmpCallCardInputType_Code as \"CmpCallCardInputType_Code\"
			from
				v_CmpCallCard CCC
				left join v_CmpCallCardInputType CT on CT.CmpCallCardInputType_id = CCC.CmpCallCardInputType_id
			where CmpCallCard_id = :CmpCallCard_id
			  and CT.CmpCallCardInputType_Code is not null
			limit 1
		";
		$queryParams = ["CmpCallCard_id" => $cmpCallCardID];
		/**@var CI_DB_result $result */
		$result = $callObject->queryResult($query, $queryParams);
		if (!is_array($result) || count($result) == 0) {
			return false;
		}
		return (int)$result[0]["CmpCallCardInputType_Code"];
	}

	/**
	 * список пациентов для журнала расхождения
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getPatientDiffList(CmpCallCard_model $callObject, $data)
	{
		$filter = "";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		if (!empty($data["begDate"])) {
			$filter .= " and C.CmpCallCard_prmDT >= :begDate";
			$queryParams["begDate"] = $data["begDate"];
		}
		if (!empty($data["endDate"])) {
			$filter .= " and C.CmpCallCard_prmDT <= :endDate";
			$queryParams["endDate"] = $data["endDate"];
		}
		$sql = "
			select
				C.CmpCallCard_id as \"CmpCallCard_id\",
				EPS.Person_id as \"Person_id\",
				EPS.PersonEvn_id as \"PersonEvn_id\",
				EPS.Server_id as \"Server_id\",
				CC.CmpCloseCard_id as \"CmpCloseCard_id\",
				to_char(C.CmpCallCard_prmDT, '{$callObject->dateTimeForm104}')||' '||substring(to_char(C.CmpCallCard_prmDT, '{$callObject->dateTimeForm108}'), 1, 5) as \"CmpCallCard_prmDT\",
				C.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				coalesce(MP.Person_SurName||' ', '')||coalesce(MP.Person_FirName||' ', '')||coalesce(MP.Person_SecName, '') as \"CmpCallCard_Dspp\",
				coalesce(PSv.Person_SurName||' ', '')||coalesce(PSv.Person_FirName||' ', '')||coalesce(PSv.Person_SecName, '') as \"Person_Fio_v\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				coalesce(PS.Person_SurName||' ', '')||coalesce(PS.Person_FirName||' ', '')||coalesce(PS.Person_SecName, '') as \"Person_Fio\"
			from
				v_CmpCallCard C
				left join v_CmpCloseCard CC on CC.CmpCallCard_id = C.CmpCallCard_id
				left join v_MedPersonal MP on MP.MedPersonal_id = C.MedPersonal_id
				inner join v_PersonState PSv on PSv.Person_id = C.Person_id
				inner join v_EvnPS EPS on EPS.CmpCallCard_id = C.CmpCallCard_id
				inner join v_Lpu Lpu on Lpu.Lpu_id = EPS.Lpu_id
				inner join v_PersonState PS on PS.Person_id = EPS.Person_id
			where C.Person_id != EPS.Person_id
			  and C.Lpu_id = :Lpu_id
			  {$filter}
		";
		return $callObject->queryResult($sql, $queryParams);
	}

	/**
	 * Получение информации о диагнозах
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getSidOoidDiags(CmpCallCard_model $callObject, $data)
	{
		$params = ["CmpCloseCard_id" => $data["CmpCloseCard_id"]];
		$query = "
			select
                cccd.Diag_id as \"Diag_id\",
                cccd.DiagSetClass_id as \"DiagSetClass_id\"
			from v_cmpclosecarddiag cccd				
			where cccd.CmpCloseCard_id = :CmpCloseCard_id;
		";
		$response = $callObject->queryResult($query, $params);
		return $response;
	}

	/**
	 * Получение списка номеров карт(110/у), которых запросил СМО
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getSmoQueryCallCards(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				CmpSmoQueryCardNumbers_id as \"id\",
				CmpSmoQueryCardNumbers_CardNumber as \"CardNumber\",
				CmpSmoQueryCardNumbers_insDT::varchar as \"insDate\"                                
			from r2.v_CmpSmoQueryCardNumber				
			where CmpSmoQueryCardNumbers_SmoID = :OrgSmo_id;
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получаем флаг опер отдела "Включить функцию «Контроль вызовов»"
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool|false
	 */
	public static function getIsCallControllFlag(CmpCallCard_model $callObject, $data)
	{
		$callObject->load->model("CmpCallCard_model4E", "CmpCallCard_model4E");
		$operLpuBuilding = $callObject->CmpCallCard_model4E->getOperDepartament($data);
		if (empty($operLpuBuilding["LpuBuilding_pid"])) {
			return false;
		}
		$query = "
			select case when coalesce(SmpUnitParam_IsCallControll, 1) = 1 then 'false' else 'true' end as \"SmpUnitParam_IsCallControll\"
			from v_SmpUnitParam
			where LpuBuilding_id = :LpuBuilding_pid
			order by SmpUnitParam_id desc
			limit 1
		";
		return $callObject->queryResult($query, $operLpuBuilding);
	}

    /**
     * Получение списка МО с обслуживанием на дому
     * @param $callObject
     * @param $data
     * @return boolean
     */
    public static function getLpuWithOperSmp($callObject){
        $sql = "
			SELECT DISTINCT
                lpu.Lpu_id as \"Lpu_id\",
                lpu.Lpu_Name as \"Lpu_Name\",
                lpu.Lpu_Nick as \"Lpu_Nick\"
            FROM
                v_SmpUnitParam sup
                LEFT JOIN v_SmpUnitType sut ON sup.SmpUnitType_id = sut.SmpUnitType_id
                INNER JOIN v_LpuBuilding lb ON(lb.LpuBuilding_id=sup.LpuBuilding_id)
                LEFT JOIN v_lpu lpu on lpu.Lpu_id = lb.Lpu_id
            WHERE
                coalesce (sup.LpuBuilding_pid, 1) = 1
            and
                sut.SmpUnitType_Code = 4
            and
                LB.LpuBuildingType_id in (27, 28)
            and
                lb.LpuBuilding_begDate <= dbo.tzGetDate()
            and
                (lb.LpuBuilding_endDate is null or lb.LpuBuilding_endDate > dbo.tzGetDate())
		";
        $result = $callObject->db->query($sql);
        if (is_object($result)) {
            return $result->result_array();
        } else {
            return false;
        }
    }
    
   	/**
     * Полчение дерева решений приналижащего определнное структуре
     */
	public static function getConcreteDecigionTree(CmpCallCard_model $callObject, $data){

		$query = "
			SELECT
				ADT.AmbulanceDecigionTree_id as \"AmbulanceDecigionTree_id\",
				ADT.AmbulanceDecigionTree_nodeid as \"AmbulanceDecigionTree_nodeid\",
				ADT.AmbulanceDecigionTree_nodepid as \"AmbulanceDecigionTree_nodepid\",
				ADT.AmbulanceDecigionTree_Type as \"AmbulanceDecigionTree_Type\",
				ADT.AmbulanceDecigionTree_Text as \"AmbulanceDecigionTree_Text\",
				ADT.CmpReason_id as \"CmpReason_id\",
			    ADTR.AmbulanceDecigionTreeRoot_id as \"AmbulanceDecigionTreeRoot_id\"
			FROM
				v_AmbulanceDecigionTree ADT
			left join v_AmbulanceDecigionTreeRoot ADTR on ADT.AmbulanceDecigionTreeRoot_id = ADTR.AmbulanceDecigionTreeRoot_id
			WHERE
				ADTR.AmbulanceDecigionTreeRoot_id = :AmbulanceDecigionTreeRoot_id
			";

		$result = $callObject->db->query($query, $data);

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
     * Получение стркутуры деревьев МО
     */
	public static function getDecigionTreeLpu(CmpCallCard_model $callObject, $data){

		$filter = '';
		if($data['adminRegion'] == 'false'){
			$callObject->load->model('CmpCallCard_model4E');
			$OperDepartament = $callObject->CmpCallCard_model4E->getOperDepartament($data);
			$filter = " and LB.LpuBuilding_id = {$OperDepartament['LpuBuilding_pid']}";
		}

		$sql = "
			SELECT DISTINCT
					lpu.Lpu_id as \"Lpu_id\",
					lpu.Lpu_Name as \"Lpu_Name\",
			        lpu.Lpu_Name as \"text\",
					lpu.Lpu_Nick as \"Lpu_Nick\",
			        ADTR.AmbulanceDecigionTreeRoot_id as \"AmbulanceDecigionTreeRoot_id\",
			        case when ADTR.AmbulanceDecigionTreeRoot_id is not null then 'true' else 'false' end as \"issetTree\"
				FROM
					v_SmpUnitParam sup
					LEFT JOIN v_SmpUnitType sut  ON sup.SmpUnitType_id = sut.SmpUnitType_id
					INNER JOIN v_LpuBuilding lb ON lb.LpuBuilding_id=sup.LpuBuilding_id
					LEFT JOIN v_lpu lpu on lpu.Lpu_id = lb.Lpu_id
					LEFT JOIN LATERAL(
						SELECT
							AmbulanceDecigionTreeRoot_id
						FROM v_AmbulanceDecigionTreeRoot
						WHERE Lpu_id = lpu.Lpu_id and LpuBuilding_id is null
                        limit 1
					) as ADTR on true
				WHERE
					COALESCE(sup.LpuBuilding_pid, 1) = 1
					AND lpu.Lpu_id is not null
					AND sut.SmpUnitType_Code = 4
					AND LB.LpuBuildingType_id in (27, 28)
					AND lb.LpuBuilding_begDate <= dbo.tzGetDate()
					AND (lb.LpuBuilding_endDate is null or lb.LpuBuilding_endDate > dbo.tzGetDate()) {$filter}
		";


		$result = $callObject->db->query($sql);

		if (is_object($result)) {
			return $result->result_array();
		} else {
			return false;
		}
	}
	
	/**
     * Получение стркутуры деревьев подстанции
     */
	public static function getDecigionTreeRegion(CmpCallCard_model $callObject, $data){
		$sql = "
			SELECT
				 KLA.KLArea_FullName  as \"text\",
			     ADTR.AmbulanceDecigionTreeRoot_id as \"AmbulanceDecigionTreeRoot_id\",
		    	 case when ADTR.AmbulanceDecigionTreeRoot_id is not null then 'true' else 'false' end as \"issetTree\"
			FROM v_KLArea KLA
			LEFT JOIN LATERAL(
				SELECT
					AmbulanceDecigionTreeRoot_id
				FROM AmbulanceDecigionTreeRoot
				WHERE Region_id = KLA.KLArea_id AND LpuBuilding_id IS NULL AND Lpu_id IS NULL
                limit 1
			) as ADTR on true
			WHERE KLA.KLArea_id = :Region_id
            limit 1
		";

		$result = $callObject->db->query($sql,array('Region_id' => $data['session']['region']['number']));

		if (is_object($result)) {
			return $result->result_array()[0];
		} else {
			return false;
		}
	}

	/**
     * Получение стркутуры деревьев подстанции
     */
	public static function getDecigionTreeLpuBuilding(CmpCallCard_model $callObject, $data){

		$filter = '';
		if($data['adminRegion'] == 'false'){
			$callObject->load->model('CmpCallCard_model4E');
			$OperDepartament = $callObject->CmpCallCard_model4E->getOperDepartament($data);
			$filter = " and LB.LpuBuilding_id = {$OperDepartament['LpuBuilding_pid']}";
		}

		$sql = "
		SELECT DISTINCT
		     LB.LpuBuilding_id as \"LpuBuilding_id\",
		     LB.LpuBuilding_Name as \"text\",
		     LB.Lpu_id as \"Lpu_id\",
		     ADTR.AmbulanceDecigionTreeRoot_id as \"AmbulanceDecigionTreeRoot_id\",
		     case when ADTR.AmbulanceDecigionTreeRoot_id is not null then 'true' else 'false' end as \"issetTree\"
		from v_LpuBuilding LB
			LEFT JOIN v_SmpUnitParam sup ON sup.LpuBuilding_id = LB.LpuBuilding_id
			LEFT JOIN v_SmpUnitType sut ON sup.SmpUnitType_id = sut.SmpUnitType_id
			LEFT JOIN LATERAL(
				SELECT
					AmbulanceDecigionTreeRoot_id
				FROM AmbulanceDecigionTreeRoot
				WHERE LpuBuilding_id = LB.LpuBuilding_id
                limit 1
			) as ADTR on true
		where sut.SmpUnitType_Code = 4
		and lb.LpuBuilding_begDate <= dbo.tzGetDate()
		and (lb.LpuBuilding_endDate is null or lb.LpuBuilding_endDate > dbo.tzGetDate()) {$filter}";

		$result = $callObject->db->query($sql);

		if (is_object($result)) {
			return $result->result_array();
		} else {
			return false;
		}

	}

	/**
     * Получение структуры для которых существует дерево решений
     */
	public static function getStructuresIssetTree(CmpCallCard_model $callObject, $data)
	{
		switch ($data['level']) {
			case 'LpuBuilding':
				$filter[] = 'ADTR.LpuBuilding_id is not null';
				$filter[] = 'ADTR.Region_id = dbo.GetRegion()';
				break;
			case 'Lpu':
				$filter[] = 'ADTR.Lpu_id is not null';
				$filter[] = 'ADTR.LpuBuilding_id IS NULL';
				$filter[] = 'ADTR.Region_id = dbo.GetRegion()';

				break;
			case 'Region':
				$filter[] = 'ADTR.Lpu_id IS NULL';
				$filter[] = 'ADTR.LpuBuilding_id IS NULL';
				$filter[] = 'ADTR.Region_id = dbo.GetRegion()';
				break;
			default:
				$filter[] = 'ADTR.Lpu_id IS NULL';
				$filter[] = 'ADTR.LpuBuilding_id IS NULL';
				$filter[] = 'ADTR.Region_id IS NULL';
				break;
		}

		$sql = "
			SELECT
				ADTR.Lpu_id as \"Lpu_id\",
				ADTR.LpuBuilding_id as \"LpuBuilding_id\",
				ADTR.Region_id as \"Region_id\",
			    ADTR.AmbulanceDecigionTreeRoot_id as \"AmbulanceDecigionTreeRoot_id\",
				coalesce(LB.lpubuilding_name, Lpu.Lpu_name, KLA.KLArea_FullName, 'Базовое дерево') as \"text\"
			from AmbulanceDecigionTreeRoot ADTR
			left join v_Lpu Lpu on Lpu.Lpu_id = ADTR.Lpu_id
			left join v_LpuBuilding LB on LB.LpuBuilding_id = ADTR.LpuBuilding_id
			left join v_KLArea KLA on  ADTR.Region_id = KLA.KLArea_id
			WHERE
			" . implode(' and ', $filter);

		$result = $callObject->db->query($sql);

		if (is_object($result)) {
			return $result->result_array();
		} else {
			return false;
		}
	}

	/**
	 * получаем МО опер отдела НМП, на котором текущая служба
	 */
	public static function getNMPLpu(CmpCallCard_model $callObject, $data)
	{
		$MedService_id = $data['session']['CurARM']['MedService_id'];

		if (empty($MedService_id)) {
			return false;
		}

		$query = "
			SELECT
				LB.Lpu_id as \"Lpu_id\"
			FROM v_MedService MS
				left join v_LpuBuilding MSLB on MSLB.LpuBuilding_id = MS.LpuBuilding_id
				left join v_SmpUnitParam SUP on SUP.LpuBuilding_id = MSLB.LpuBuilding_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = SUP.LpuBuilding_pid
			WHERE MS.MedService_id = :MedService_id
		";

		$result = $callObject->db->query($query, array('MedService_id' => $MedService_id))->result_array();

		if (isset($result[0]) && isset($result[0]['Lpu_id'])) {
			return $result[0]['Lpu_id'];
		}

		return false;
	}
}