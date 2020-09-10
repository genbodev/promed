<?php

/**
 * Class Resource_model
 *
 * @property CI_DB_driver $db
 */
class Resource_model extends swPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm108 = "HH24:MI:SS";
	private $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Загрузка грида ресурсов
	 * @param $data
	 * @return array|bool
	 */
	function loadResourceMedServiceGrid($data)
	{
		$query = "
			select
				-- select
				Res.Resource_id as \"Resource_id\",
				Res.MedService_id as \"MedService_id\",
				Res.Resource_Name as \"Resource_Name\",
				to_char(Res.Resource_begDT, '{$this->dateTimeForm104}') as \"Resource_begDT\",
				to_char(Res.Resource_endDT, '{$this->dateTimeForm104}') as \"Resource_endDT\"
				-- end select
			from
			    -- from
				v_Resource Res
				-- end from
			where
			    -- where
				Res.MedService_id = :MedService_id
				-- end where
			order by
			    -- order by
				Res.Resource_Name
				-- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $this->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
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
	 * Загрузка комбо ресурсов
	 * @param $data
	 * @return array|false
	 */
	function loadResourceList($data)
	{
		$filterArray = [];
		$queryParams = [];
		if (!empty($data["Resource_id"])) {
			$filterArray[] = "Res.Resource_id = :Resource_id";
			$queryParams["Resource_id"] = $data["Resource_id"];
		} else if (!empty($data["MedService_id"])) {
			$filterArray[] = "Res.MedService_id = :MedService_id";
			$queryParams["MedService_id"] = $data["MedService_id"];
		} else {
			return [];
		}
		if (!empty($data["UslugaComplex_ids"]) && is_array($data["UslugaComplex_ids"])) {
			$i = 0;
			foreach ($data["UslugaComplex_ids"] as $oneUslugaComplex) {
				$i++;
				$field = "UslugaComplex{$i}_id";
				$queryParams[$field] = $oneUslugaComplex;
				$filterArray[] = "
					exists(
						select ucr.UslugaComplexResource_id
						from
							v_UslugaComplexResource ucr
							inner join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = ucr.UslugaComplexMedService_id
						where ucr.Resource_id = Res.Resource_id
						  and ucms.UslugaComplex_id = :{$field}
						limit 1
					)
				";
			}
		}
		if (!empty($data["UslugaComplex_id"])) {
			$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
			$filterArray[] = "
				exists(
					select ucr.UslugaComplexResource_id
					from
						v_UslugaComplexResource ucr
						inner join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = ucr.UslugaComplexMedService_id
					where ucr.Resource_id = Res.Resource_id
					  and ucms.UslugaComplex_id = :UslugaComplex_id
					limit 1
				)
			";
		}
		if (!empty($data["onDate"])) {
			$queryParams["onDate"] = $data["onDate"];
			$filterArray[] = "coalesce(Resource_begDT, :onDate) <= :onDate";
			$filterArray[] = "coalesce(Resource_endDT, :onDate) >= :onDate";
		}
		$selectString = "
			Res.Resource_id as \"Resource_id\",
			Res.Resource_Name as \"Resource_Name\",
			Res.MedService_id as \"MedService_id\",
			MS.MedService_Name as \"MedService_Name\"
		";
		$fromString = "
			v_Resource Res
			left join v_MedService MS on MS.MedService_id = Res.MedService_id
		";
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$query = "
			select {$selectString}
			from {$fromString}
			{$whereString}
		";
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Отправка данных направлений с типом функциональная диагностика в сторонние сервисы
	 * @param $data
	 * @param null $return_data
	 * @throws Exception
	 */
	public function transferDirection($data, &$return_data = null)
	{
		if (!empty($return_data)) {
			$return_data["archimed_status"] = 0;
		}
		if (!isset($data["EvnPrescr_id"])) {
			if (isset($return_data["archimed_status"])) {
				$return_data["archimed_status"] = 1;
			}
			return;
		}
		$EvnPrescr_id = $data["EvnPrescr_id"];
		// Получаем данные направления но только с типом функциональная диагностика
		$sql = "
			select
				ed.EvnDirection_id as \"EvnDirection_id\",
				ed.EvnDirection_Descr as \"EvnDirection_Descr\",
				ed.MedService_id as \"MedService_id\",
				ps.Person_id as \"Person_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				to_char(ps.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
				ps.Sex_id as \"Sex_id\",
				a.Address_Address as \"Address_Address\",
				to_char(ttms.TimetableMedService_begTime, '{$this->dateTimeForm104}') as \"TimetableMedService_begTime_Date\",
				to_char(ttms.TimetableMedService_begTime, '{$this->dateTimeForm108}') as \"TimetableMedService_begTime_Time\",
				uc.UslugaComplex_id as \"UslugaComplex_id\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				mp.MedPersonal_id as \"MedPersonal_id\",
				mp.Person_Fio as \"MedPersonal_Person_Fio\"
			from
				v_EvnPrescrFuncDiag epfd
				inner join v_EvnPrescrFuncDiagUsluga epfdu ON epfdu.EvnPrescrFuncDiag_id=epfd.EvnPrescrFuncDiag_id
				inner join v_UslugaComplex uc ON uc.UslugaComplex_id=epfdu.UslugaComplex_id
				inner join v_EvnPrescrDirection epd ON epd.EvnPrescr_id=epfd.EvnPrescrFuncDiag_id
				inner join v_EvnDirection_all ed ON ed.EvnDirection_id=epd.EvnDirection_id
				left join v_MedPersonal mp ON mp.MedPersonal_id=ed.MedPersonal_id
				inner join v_PersonState ps ON ps.Person_id=ed.Person_id
				left join v_Address a ON a.Address_id=ps.UAddress_id
				left join v_TimeTableMedService_lite ttms ON ttms.TimeTableMedService_id=ed.TimeTableMedService_id
			where epfd.EvnPrescrFuncDiag_id=:EvnPrescr_id
			limit 1
		";
		$sqlParams = ["EvnPrescr_id" => $EvnPrescr_id];
		$person = $this->db->query($sql, $sqlParams)->row_array();
		if (empty($person)) {
			if (isset($return_data["archimed_status"])) {
				$return_data["archimed_status"] = 2;
			}
			return;
		}
		// Отправляем в АрхиМед
		$access = $this->retrieveAccessData($person["MedService_id"]);
		if (empty($access["MedService_WialonURL"]) || empty($access["MedService_WialonPort"])) {
			if (isset($return_data["archimed_status"])) {
				$return_data["archimed_status"] = 3;
			}
			return;
		}
		// Данные для отправки
		$send_data = [
			"PATIENT_ID" => $person["Person_id"], // ID пациента в БД ПроМед
			"PATIENT_NAME" => trim(trim($person["Person_SurName"]) . " " . trim($person["Person_FirName"]) . " " . trim($person["Person_SecName"])), // ФИО пациента;
			"PATIENT_DATEOFBIRTH" => $person["Person_BirthDay"], // дата рождения пациента d.m.Y
			"PATIENT_SEX" => $person["Sex_id"] == 1 ? "м" : ($person["Sex_id"] == 2 ? "ж" : ""), // пол пациента м / ж
			"PATIENT_HOME_ADDRESS" => $person["Address_Address"], // домашний адрес пациента
			"PRESCRIPTIO_ID" => $person["EvnDirection_id"], // ID направления в БД ПроМед
			"STUDY_DATE" => (string)$person["TimetableMedService_begTime_Date"], // дата, на которую назначено исследование d.m.Y
			"STUDY_TIME" => preg_replace("#([0-9]{2}.[0-9]{2}).[0-9]{2}$#", "$1", $person["TimetableMedService_begTime_Time"]), // время, на которое назначено исследование H:i
			"STUDY_TYPE_ID" => "", // ID вида исследования ПроМеда (Рентген, УЗИ, КТ и пр.).
			"STUDY_LIST" => [ // список исследований (предоставляемых услуг?) из БД ПроМед;
				[
					"STUDY_ID" => $person["UslugaComplex_id"], // ID исследования (услуги) в БД ПроМед;
					"STUDY_NAME" => $person["UslugaComplex_Name"] // наименование исследования (услуги) в БД ПроМед;
				]
			],
			"DOCTOR_ID" => $person["MedPersonal_id"], // ID врача, назначившего исследование;
			"DOCTOR_NAME" => $person["MedPersonal_Person_Fio"], // ФИО врача, назначившего исследование;
			"STUDY_PURPOSE" => (string)$person["EvnDirection_Descr"], // цель исследования
		];
		// JSON_UNESCAPED_UNICODE для php 5.3
		$send_data_json = json_encode($send_data);
		$send_data_json = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
			return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
		}, $send_data_json);
		$this->load->helper("CURL");
		$result = CURL(
			$access["MedService_WialonURL"] . ":" . $access["MedService_WialonPort"] . "/STUDY_PRESCRIPTION_PM/",
			$send_data_json,
			"POST",
			null,
			[
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTPHEADER => ["Content-Type: application/json; charset=UTF-8"]
			]
		);
		if (isset($return_data["archimed_status"])) {
			$return_data["archimed_status"] = $result ? true : false;
		}
	}

	/**
	 * Возвращает данные для аутентификации в АрхиМед и пр.
	 * @param $MedService_id
	 * @return array|bool
	 */
	protected function retrieveAccessData($MedService_id)
	{
		if (empty($MedService_id)) {
			return false;
		}
		$sql = "
			select
				ms.MedService_id as \"MedService_id\",
				ms.MedService_WialonURL as \"MedService_WialonURL\",
				ms.MedService_WialonPort as \"MedService_WialonPort\"
			from
				v_MedService ms
				inner join v_MedServiceType mst on mst.MedServiceType_id=ms.MedServiceType_id
			where ms.MedService_id=:MedService_id
			  and mst.MedServiceType_Code = 3
		";
		$sqlParams = ["MedService_id" => $MedService_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $sqlParams);
		return $result->row_array();
	}

	/**
	 * Сохранение ресурса
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function saveResource($data)
	{
		try {
			$this->beginTransaction();
			if (!empty($data["Resource_id"])) {
				$action = "upd";
				$query = "
					select
						Resource_Name as \"Resource_Name\",
						ResourceType_id as \"ResourceType_id\", 
						MedService_id as \"MedService_id\",
						to_char(Resource_begDT, '{$this->dateTimeForm120}') as \"Resource_begDT\",
						to_char(Resource_endDT, '{$this->dateTimeForm120}') as \"Resource_endDT\"
					from v_Resource
					where Resource_id = :Resource_id
				";
				$resourceData = $this->getFirstRowFromQuery($query, $data);
				if ($resourceData === false || !is_array($resourceData) || count($resourceData) == 0) {
					throw new Exception("Ошибка при получении данных ресурса из БД");
				}
				$data["MedService_id"] = $resourceData["MedService_id"];
			} else {
				$action = "ins";
				$data["Resource_id"] = null;
			}
			$selectString = "
				resource_id as \"Resource_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Message\"
			";
			$query = "
				select {$selectString}
				from p_resource_{$action}(
				    resource_id := :Resource_id,
				    resource_name := :Resource_Name,
				    resourcetype_id := :ResourceType_id,
				    resource_begdt := :Resource_begDT,
				    resource_enddt := :Resource_endDT,
				    pmuser_id := :pmUser_id
				);
			";
			$response = $this->getFirstRowFromQuery($query, $data);
			if ($response === false) {
				throw new Exception("Ошибка при выполнении запроса к БД");
			}
			$data["Resource_id"] = $response["Resource_id"];
			// сохранение связей с мед. изделиями
			$rsp = $this->_saveMedProductCardResourceGrid($data);
			if (!empty($rsp)) {
				throw new Exception($rsp);
			}
			$this->commitTransaction();
			return $response;
		} catch (Exception $e) {
			$this->rollbackTransaction();
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * Сохранение таблицы связи ресурса с медицинским изделием
	 * @param $data
	 * @return string
	 */
	protected function _saveMedProductCardResourceGrid($data)
	{
		$MedProductCardResourceData = json_decode($data["MedProductCardResourceData"], true);
		if ($data["ResourceType_id"] == 3 && count($MedProductCardResourceData) == 0) {
			return "Массив карточек мед. изделий обязателен для заполнения";
		}
		$query = "
			select MedProductCardResource_id as \"MedProductCardResource_id\"
			from passport.v_MedProductCardResource
			where Resource_id = :Resource_id
		";
		$rsp = $this->queryResult($query, $data);
		foreach ($rsp as $row) {
			$resp = $this->_deleteMedProductCardResource($row);
			if (!empty($resp)) {
				return "Ошибка при удалении медицинского изделия";
			}
		}
		if ($data["ResourceType_id"] != 3) {
			return "";
		}
		foreach ($MedProductCardResourceData as $MedProductCardResource) {
			$MedProductCardResource["Resource_id"] = $data["Resource_id"];
			$MedProductCardResource["pmUser_id"] = $data["pmUser_id"];
			$MedProductCardResource["MedProductCardResource_begDT"] = (!empty($MedProductCardResource["begDT"]) ? $MedProductCardResource["begDT"] : null);
			$MedProductCardResource["MedProductCardResource_endDT"] = (!empty($MedProductCardResource["endDT"]) ? $MedProductCardResource["endDT"] : null);
			$resp = $this->_saveMedProductCardResource($MedProductCardResource);
			if (!empty($resp)) {
				return "Ошибка при сохранении медицинского изделия";
			}
		}
		return "";
	}

	/**
	 * Сохранение связи ресурса с медицинским изделием
	 * @param $data
	 * @return string
	 */
	protected function _saveMedProductCardResource($data)
	{
		$check = $this->_checkMedProductCardResource($data);
		if (!empty($check)) {
			return $check;
		}
		$params = [
			"MedProductCardResource_id" => (!empty($data["MedProductCardResource_id"]) && $data["MedProductCardResource_id"] > 0 ? $data["MedProductCardResource_id"] : null),
			"MedProductCard_id" => $data["MedProductCard_id"],
			"Resource_id" => $data["Resource_id"],
			"MedProductCardResource_begDT" => $data["MedProductCardResource_begDT"],
			"MedProductCardResource_endDT" => empty($data["MedProductCardResource_endDT"]) ? NULL : $data["MedProductCardResource_endDT"],
			"pmUser_id" => $data["pmUser_id"],
		];
		$action = (!empty($params["MedProductCardResource_id"]))?"upd":"ins";
		$selectString = "
			medproductcardresource_id as \"MedProductCardResource_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from passport.p_medproductcardresource_{$action}(
			    medproductcardresource_id := :MedProductCardResource_id,
			    medproductcard_id := :MedProductCard_id,
			    resource_id := :Resource_id,
			    medproductcardresource_begdt := :MedProductCardResource_begDT,
			    medproductcardresource_enddt := :MedProductCardResource_endDT,
			    pmuser_id := :pmUser_id
			);
		";
		$rsp = $this->getFirstRowFromQuery($query, $params);
		if ($rsp === false || !is_array($rsp) || count($rsp) == 0) {
			return "Ошибка при сохранении связи ресурса с медицинским изделием";
		} else if (!empty($rsp["Error_Msg"])) {
			return (string)$rsp["Error_Msg"];
		}
		return "";
	}

	/**
	 * Удаление связи ресурса с медицинским изделием
	 * @param $data
	 * @return string
	 */
	protected function _deleteMedProductCardResource($data)
	{
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Message\"
			from passport.p_medproductcardresource_del(medproductcardresource_id := :MedProductCardResource_id);
		";
		$rsp = $this->getFirstRowFromQuery($query, $data);
		if ($rsp === false) {
			return "Ошибка при удалении связи ресурса с медицинским изделием";
		} else if (!empty($rsp["Error_Msg"])) {
			return (string)$rsp["Error_Msg"];
		}
		return "";
	}

	/**
	 * Проверка дублирования связи ресурса с медицинским изделием
	 * @param $data
	 * @return string
	 */
	protected function _checkMedProductCardResource($data)
	{
		$query = "
			select MedProductCardResource_id as \"MedProductCardResource_id\"
			from passport.v_MedProductCardResource
			where Resource_id = :Resource_id
			  and MedProductCardResource_id != coalesce(:MedProductCardResource_id, 0)
			  and (
			      	(MedProductCardResource_begDT <= :MedProductCardResource_begDT and (MedProductCardResource_endDT > :MedProductCardResource_endDT or MedProductCardResource_endDT is null)) or
			      	(:MedProductCardResource_begDT between MedProductCardResource_begDT and MedProductCardResource_endDT) or
			      	(MedProductCardResource_begDT > :MedProductCardResource_begDT and :MedProductCardResource_endDT is null)
				)
			limit 1
		";
		$params = [
			"Resource_id" => $data["Resource_id"],
			"MedProductCardResource_id" => $data["MedProductCardResource_id"],
			"MedProductCardResource_begDT" => $data["MedProductCardResource_begDT"],
			"MedProductCardResource_endDT" => empty($data["MedProductCardResource_endDT"]) ? NULL : $data["MedProductCardResource_endDT"]
		];
		$rsp = $this->getFirstResultFromQuery($query, $params, true);
		if ($rsp === false) {
			return "Не удалось проверить пересечение медицинских изделий";
		} else if (!empty($rsp)) {
			return "В один период времени Ресурс может быть связан только с одним медицинским изделием";
		}
		return "";
	}
}