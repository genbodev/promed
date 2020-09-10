<?php

class EvnReanimatPeriod_model_save
{
	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function moveToReanimation(EvnReanimatPeriod_model $callObject, $data)
	{
		$callObject->load->model("ReanimatRegister_model", "ReanimatRegister_model");
		//возвращаемый объект
		$ReturnObject = [
			"Status" => "",
			"Message" => "",
			"EvnReanimatPeriod_id" => "",
			"fork" => 0
		];
		$pmUser_id = $callObject->sessionParams["pmuser_id"];
		$LpuSection_id = 0;
		$EvnPS_id = 0;
		$EvnSection_id = 0;
		$MedService_id = 0;

		$params = [
			"Person_id" => isset($data["Person_id"]) ? $data["Person_id"] : null,
			"Lpu_id" => isset($data["Lpu_id"]) ? $data["Lpu_id"] : null
		];

		//Выборка определяющая не находится ли пациент в данный момент в реанимации
		$query = "
			select
	            EvnClass_id as \"EvnClass_id\",
                EvnReanimatPeriod_IsSigned as \"EvnReanimatPeriod_IsSigned\",
                pmUser_signID as \"pmUser_signID\",
                EvnReanimatPeriod_signDT as \"EvnReanimatPeriod_signDT\",
                EvnReanimatPeriod_IsArchive as \"EvnReanimatPeriod_IsArchive\",
                EvnReanimatPeriod_Guid as \"EvnReanimatPeriod_Guid\",
                EvnReanimatPeriod_IndexMinusOne as \"EvnReanimatPeriod_IndexMinusOne\",
                EvnStatus_id as \"EvnStatus_id\",
                EvnReanimatPeriod_statusDate as \"EvnReanimatPeriod_statusDate\",
                EvnReanimatPeriod_IsTransit as \"EvnReanimatPeriod_IsTransit\",
                MedService_id as \"MedService_id\",
                LpuSection_id as \"LpuSection_id\",
                EvnClass_Name as \"EvnClass_Name\",
                ReanimResultType_id as \"ReanimResultType_id\",
                ReanimReasonType_id as \"ReanimReasonType_id\",
                LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
                EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
                EvnReanimatPeriod_setDate as \"EvnReanimatPeriod_setDate\",
                EvnReanimatPeriod_setTime as \"EvnReanimatPeriod_setTime\",
                EvnReanimatPeriod_didDate as \"EvnReanimatPeriod_didDate\",
                EvnReanimatPeriod_didTime as \"EvnReanimatPeriod_didTime\",
                EvnReanimatPeriod_disDate as \"EvnReanimatPeriod_disDate\",
                EvnReanimatPeriod_disTime as \"EvnReanimatPeriod_disTime\",
                EvnReanimatPeriod_pid as \"EvnReanimatPeriod_pid\",
                EvnReanimatPeriod_rid as \"EvnReanimatPeriod_rid\",
                Lpu_id as \"Lpu_id\",
                Server_id as \"Server_id\",
                PersonEvn_id as \"PersonEvn_id\",
                EvnReanimatPeriod_setDT as \"EvnReanimatPeriod_setDT\",
                EvnReanimatPeriod_disDT as \"EvnReanimatPeriod_disDT\",
                EvnReanimatPeriod_didDT as \"EvnReanimatPeriod_didDT\",
                EvnReanimatPeriod_insDT as \"EvnReanimatPeriod_insDT\",
                EvnReanimatPeriod_updDT as \"EvnReanimatPeriod_updDT\",
                EvnReanimatPeriod_Index as \"EvnReanimatPeriod_Index\",
                EvnReanimatPeriod_Count as \"EvnReanimatPeriod_Count\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                Person_id as \"Person_id\",
                Morbus_id as \"Morbus_id\"		 
			from v_EvnReanimatPeriod ERP 
			where ERP.Person_id = :Person_id
			  and ERP.EvnReanimatPeriod_setDate <= getdate()
			  and ERP.EvnReanimatPeriod_disDate is null
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			$ReturnObject["Status"] = "Oshibka";
			$ReturnObject["Message"] = "Ошибка при выполнении запроса к базе данных";
			return $ReturnObject;
		}
		$resp = $result->result("array");
		//если есть открытый реанимационный период пациента, т.е. он уже в реанимации
		if (count($resp) > 0) {
			$ReturnObject["Status"] = "AlreadyInReanimation";
			$ReturnObject["Message"] = "Данный пациент уже находится в реанимации";
			return $ReturnObject;
		}
		// из АРМ стационара:
		if ($data["ARMType"] == "stac") {
			// поиск реанимационной службы, она не передана в параметрах
			$query = "        
				select MS.MedService_id as \"MedService_id\" 
				from
					v_MedService MS 
					inner join dbo.MedServiceType MST on MS.MedServiceType_id = MST.MedServiceType_id
				where MS.Lpu_id = :Lpu_id  
				  and MS.MedService_endDT is null
				  and MST.MedServiceType_SysNick = 'reanimation'			
			";
			$result = $callObject->db->query($query, $params);
			// ошибка работы с БД при поиске карт выбывшего из стационара
			if (!is_object($result)) {
				$ReturnObject["Status"] = "Oshibka";
				$ReturnObject["Message"] = "Ошибка при выполнении запроса к базе данных";
				return $ReturnObject;
			}
			$resp = $result->result("array");
			//если не найдены службы реанимации у данной МО
			if (count($resp) == 0) {
				$ReturnObject["Status"] = "NoReanimatMedService";
				$ReturnObject["Message"] = "В данной МО нет ни одной службы с типом Реанимация!";
				return $ReturnObject;
			} else if (count($resp) > 1) {
				$ReturnObject["Status"] = "ManyReanimatMedService";
				$ReturnObject["Message"] = "В МО больше одной служб реанимации";
				$ReturnObject["Server_id"] = $data["Server_id"];
				$ReturnObject["Person_id"] = $data["Person_id"];
				$ReturnObject["PersonEvn_id"] = $data["PersonEvn_id"];
				$ReturnObject["EvnPS_id"] = $data["EvnPS_id"];
				$ReturnObject["EvnSection_id"] = $data["EvnSection_id"];
				$ReturnObject["LpuSection_id"] = $data["LpuSection_id"];
				return $ReturnObject;
			}
			$LpuSection_id = $data["LpuSection_id"];
			$EvnPS_id = $data["EvnPS_id"];
			$EvnSection_id = $data["EvnSection_id"];
			$MedService_id = $resp[0]["MedService_id"];
		}
		// из АРМ реаниматолога:
		if ($data["ARMType"] == "reanimation") {
			$query = "
				select
					EPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				    EPS.EvnPS_id as \"EvnPS_id\",
				    EPS.EvnPS_setDT as \"EvnPS_setDT\",
				    EPS.LpuSection_id as \"LpuSection_id\",
				    ES.EvnSection_id as \"EvnSection_id\"
				from
					v_EvnPS EPS 
					inner join v_EvnSection ES on ES.EvnSection_pid = EPS.EvnPS_id
				where EPS.Lpu_id = :Lpu_id
				  and EPS.Person_id = :Person_id
				  and EPS.EvnPS_setDate <= getdate()
				  and EPS.EvnPS_disDate is null
				  and EPS.LpuSection_id is not null
				  and ES.EvnSection_setDate <= getdate()
				  and ES.EvnSection_disDate is null
				order by
					EPS.EvnPS_setDate desc,
				    ES.EvnSection_setDate desc
			";
			$result = $callObject->db->query($query, $params);
			// ошибка работы с БД при поиске карт выбывшего из стационара
			if (!is_object($result)) {
				$ReturnObject["Status"] = "Oshibka";
				$ReturnObject["Message"] = "Ошибка при выполнении запроса к базе данных";
				return $ReturnObject;
			}
			$resp = $result->result("array");
			//если найдена больше чем одна карта выбывшего из стационара
			if (count($resp) > 1) {
				$ReturnObject["Status"] = "ManyEvnPS";
				$ReturnObject["Message"] = "У пациента несколько карт выбывшего из стационара";
				$ReturnObject["Server_id"] = $data["Server_id"];
				$ReturnObject["Person_id"] = $data["Person_id"];
				$ReturnObject["PersonEvn_id"] = $data["PersonEvn_id"];
				return $ReturnObject;
			}
			$LpuSection_id = $resp[0]["LpuSection_id"];
			$EvnPS_id = $resp[0]["EvnPS_id"];
			$EvnSection_id = $resp[0]["EvnSection_id"];
			$MedService_id = $data["MedService_id"];
		}
		// после выбора карты ВС или реанимационных медслужб если их было много:
		if ($data["ARMType"] == "FromManyEvnPS") {
			$LpuSection_id = $data["LpuSection_id"];
			$EvnPS_id = $data["EvnPS_id"];
			$EvnSection_id = $data["EvnSection_id"];
			$MedService_id = $data["MedService_id"];
		}
		// формирование реанимационного периода
		$params["LpuSection_id"] = isset($LpuSection_id) ? $LpuSection_id : null;
		$params["EvnPS_id"] = isset($EvnPS_id) ? $EvnPS_id : null;
		$params["EvnSection_id"] = isset($EvnSection_id) ? $EvnSection_id : null;
		$params["MedService_id"] = isset($MedService_id) ? $MedService_id : null;

		$params["Server_id"] = isset($data["Server_id"]) ? $data["Server_id"] : null;
		$params["PersonEvn_id"] = isset($data["PersonEvn_id"]) ? $data["PersonEvn_id"] : null;
		$params["pmUser_id"] = isset($pmUser_id) ? $pmUser_id : null;

		$params["ReanimatAgeGroup_id"] = $callObject->getFirstResultFromQuery("
			select
				case 
					when cast(cast(getdate() as date) as timestamp) - interval '29 day' < PS.Person_BirthDay then 1
					when cast(cast(getdate() as date) as timestamp) - interval '29 day' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '1 year' < PS.Person_BirthDay then 2
					when cast(cast(getdate() as date) as timestamp) - interval '1 year' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '4 year' < PS.Person_BirthDay then 3
					when cast(cast(getdate() as date) as timestamp) - interval '4 year' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '18 year' < PS.Person_BirthDay then 4
				else 5 end
			from
				v_PersonState PS
				inner join PersonEvn PE on PE.Person_id = PS.Person_id
			where
				PE.PersonEvn_id = :PersonEvn_id
		", $params);
		
		$query = "
			select
				EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnReanimatPeriod_ins(
				EvnReanimatPeriod_pid := :EvnSection_id, 
				EvnReanimatPeriod_rid := :EvnPS_id, 
				Lpu_id := :Lpu_id, 
				Server_id := :Server_id, 
				MedService_id := :MedService_id,
				LpuSection_id := :LpuSection_id,
				ReanimResultType_id := null,
				ReanimReasonType_id := 1,
				LpuSectionBedProfile_id := null, 
				ReanimatAgeGroup_id := :ReanimatAgeGroup_id,
				PersonEvn_id := :PersonEvn_id, 
				EvnReanimatPeriod_setDT := getdate(),
				EvnReanimatPeriod_disDT := null,
				EvnReanimatPeriod_didDT := null,
				Morbus_id := null,
				EvnReanimatPeriod_IsSigned := null,
				pmUser_signID := null,
				EvnReanimatPeriod_signDT := null,
				EvnStatus_id := null,
				EvnReanimatPeriod_statusDate := null,
				isReloadCount := null,
				pmUser_id := :pmUser_id
			);
		";
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			$ReturnObject["Status"] = "Oshibka";
			$ReturnObject["Message"] = "Ошибка обращения к БД при формировании реанимационного периода";
			$ReturnObject["fork"] = 0;
			return $ReturnObject;
		}
		$response = $result->result("array");
		if (!is_array($response) || count($response) == 0 || empty($response[0]["EvnReanimatPeriod_id"])) {
			$ReturnObject["Status"] = "Oshibka";
			$ReturnObject["Message"] = "Ошибка обращения к БД при формировании реанимационного периода";
			$ReturnObject["fork"] = 1;
		} else if (!empty($response[0]["Error_Message"])) {
			$ReturnObject["Status"] = "Oshibka";
			$ReturnObject["Message"] = $response[0]["Error_Message"];
			$ReturnObject["fork"] = 2;
		} else {
			$data["EvnSection_id"] = $EvnSection_id;
			$data["EvnReanimatPeriod_id"] = $response[0]["EvnReanimatPeriod_id"];
			$dyrdyn = $callObject->ReanimatRegister_model->ReanimatRegisterSet($data, 1);
			if (!$dyrdyn) {
				$ReturnObject["Status"] = "Oshibka";
				$ReturnObject["Message"] = "Ошибка обращения к БД при формировании записи регистра реанимации";
				$ReturnObject["fork"] = 3;
			} else {
				$ReturnObject["Status"] = $dyrdyn["Status"];
				$ReturnObject["Message"] = "Пациент перевендён в реанимацию. <br> Запись в реестр реанимации: <br>" . $dyrdyn["Message"];
				$ReturnObject["EvnReanimatPeriod_id"] = $data["EvnReanimatPeriod_id"];
				$ReturnObject["fork"] = 3;
			}
		}
		return $ReturnObject;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function moveToReanimationFromPriem(EvnReanimatPeriod_model $callObject, $data)
	{
		$callObject->load->model("ReanimatRegister_model", "ReanimatRegister_model");
		$ReturnObject = ["EvnReanimatPeriod_id" => "",
			"Status" => "",
			"Message" => "",
			"fork" => 0
		];
		$pmUser_id = $callObject->sessionParams["pmuser_id"];
		$LpuSection_id = 0;
		$EvnSection_id = 0;
		$MedService_id = 0;
		$EvnPS_id = $data["EvnPS_id"];
		//Выборка определяющая не находится ли пациент в данный момент в реанимации
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select
		        EvnClass_id as \"EvnClass_id\",
                EvnReanimatPeriod_IsSigned as \"EvnReanimatPeriod_IsSigned\",
                pmUser_signID as \"pmUser_signID\",
                EvnReanimatPeriod_signDT as \"EvnReanimatPeriod_signDT\",
                EvnReanimatPeriod_IsArchive as \"EvnReanimatPeriod_IsArchive\",
                EvnReanimatPeriod_Guid as \"EvnReanimatPeriod_Guid\",
                EvnReanimatPeriod_IndexMinusOne as \"EvnReanimatPeriod_IndexMinusOne\",
                EvnStatus_id as \"EvnStatus_id\",
                EvnReanimatPeriod_statusDate as \"EvnReanimatPeriod_statusDate\",
                EvnReanimatPeriod_IsTransit as \"EvnReanimatPeriod_IsTransit\",
                MedService_id as \"MedService_id\",
                LpuSection_id as \"LpuSection_id\",
                EvnClass_Name as \"EvnClass_Name\",
                ReanimResultType_id as \"ReanimResultType_id\",
                ReanimReasonType_id as \"ReanimReasonType_id\",
                LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
                EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
                EvnReanimatPeriod_setDate as \"EvnReanimatPeriod_setDate\",
                EvnReanimatPeriod_setTime as \"EvnReanimatPeriod_setTime\",
                EvnReanimatPeriod_didDate as \"EvnReanimatPeriod_didDate\",
                EvnReanimatPeriod_didTime as \"EvnReanimatPeriod_didTime\",
                EvnReanimatPeriod_disDate as \"EvnReanimatPeriod_disDate\",
                EvnReanimatPeriod_disTime as \"EvnReanimatPeriod_disTime\",
                EvnReanimatPeriod_pid as \"EvnReanimatPeriod_pid\",
                EvnReanimatPeriod_rid as \"EvnReanimatPeriod_rid\",
                Lpu_id as \"Lpu_id\",
                Server_id as \"Server_id\",
                PersonEvn_id as \"PersonEvn_id\",
                EvnReanimatPeriod_setDT as \"EvnReanimatPeriod_setDT\",
                EvnReanimatPeriod_disDT as \"EvnReanimatPeriod_disDT\",
                EvnReanimatPeriod_didDT as \"EvnReanimatPeriod_didDT\",
                EvnReanimatPeriod_insDT as \"EvnReanimatPeriod_insDT\",
                EvnReanimatPeriod_updDT as \"EvnReanimatPeriod_updDT\",
                EvnReanimatPeriod_Index as \"EvnReanimatPeriod_Index\",
                EvnReanimatPeriod_Count as \"EvnReanimatPeriod_Count\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                Person_id as \"Person_id\",
                Morbus_id as \"Morbus_id\"		 
			from v_EvnReanimatPeriod ERP 
			where
				ERP.Person_id = :Person_id
				and ERP.EvnReanimatPeriod_setDate <= getdate()
				and ERP.EvnReanimatPeriod_disDate is null
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			$ReturnObject["Status"] = "Oshibka";
			$ReturnObject["Message"] = "Ошибка при выполнении запроса к базе данных";
			return $ReturnObject;
		}
		$resp = $result->result("array");
		//если есть открытый реанимационный период пациента, т.е. он уже в реанимации
		if (count($resp) > 0) {
			$ReturnObject["Status"] = "AlreadyInReanimation";
			$ReturnObject["Message"] = "Данный пациент уже находится в реанимации";
			return $ReturnObject;
		}
		if ($data["ARMType"] == "priem") {
			//находим движение в профильном отделении и код самого отделения
			$params = ["EvnPS_id" => $data["EvnPS_id"]];
			$query = "
				select
					Child.EvnSection_id as \"EvnSection_id\",
				    Child.LpuSection_id as \"LpuSection_id\"
				from
					v_EvnPS EvnPS 
					left join lateral (
						select 
							ES.EvnSection_id, ES.LpuSection_id
						from v_EvnSection ES
						where
							ES.EvnSection_pid = EvnPS.EvnPS_id
							and coalesce(ES.EvnSection_IsPriem, 1) = 1
						limit 1
					) as Child on true
				where EvnPS_id = :EvnPS_id
			";
			$result = $callObject->db->query($query, $params);
			if (!is_object($result)) {
				$ReturnObject["Status"] = "Oshibka";
				$ReturnObject["Message"] = "Ошибка при выполнении запроса к базе данных";
				return $ReturnObject;
			}
			$resp = $result->result("array");
			//если выборка пустая
			if (count($resp) == 0) {
				$ReturnObject["Status"] = "AlreadyInReanimation";
				$ReturnObject["Message"] = "Не найдена запись движения в профильном отделении";
				return $ReturnObject;
			}
			$LpuSection_id = $resp[0]["LpuSection_id"];
			$EvnSection_id = $resp[0]["EvnSection_id"];
			// поиск реанимационной службы, она не передана в параметрах
			$params = ["Lpu_id" => $data["Lpu_id"]];
			$query = "
				select 
					MS.MedService_id as \"MedService_id\"
				from v_MedService MS
					inner join MedServiceType MST on MS.MedServiceType_id = MST.MedServiceType_id
				where 
					MS.Lpu_id = :Lpu_id
					and MS.MedService_endDT is null
					and MST.MedServiceType_SysNick = 'reanimation'
			";
			$result = $callObject->db->query($query, $params);
			// ошибка работы с БД при поиске карт выбывшего из стационара
			if (!is_object($result)) {
				$ReturnObject["Status"] = "Oshibka";
				$ReturnObject["Message"] = "Ошибка при выполнении запроса к базе данных";
				return $ReturnObject;
			}
			$resp = $result->result("array");
			//если не найдены службы реанимации у данной МО
			if (count($resp) == 0) {
				$ReturnObject["Status"] = "NoReanimatMedService";
				$ReturnObject["Message"] = "В данной МО нет ни одной службы с типом Реанимация!";
				return $ReturnObject;
			} else if (count($resp) > 1) {
				$ReturnObject["Status"] = "ManyReanimatMedService";
				$ReturnObject["Message"] = "В МО больше одной служб реанимации";
				$ReturnObject["Server_id"] = $data["Server_id"];
				$ReturnObject["Person_id"] = $data["Person_id"];
				$ReturnObject["PersonEvn_id"] = $data["PersonEvn_id"];
				$ReturnObject["EvnPS_id"] = $data["EvnPS_id"];
				$ReturnObject["EvnSection_id"] = $EvnSection_id;
				$ReturnObject["LpuSection_id"] = $LpuSection_id;
				return $ReturnObject;
			}
			$MedService_id = $resp[0]["MedService_id"];
		}
		if ($data["ARMType"] == "FromManyEvnPS") {
			$LpuSection_id = $data["LpuSection_id"];
			$EvnPS_id = $data["EvnPS_id"];
			$EvnSection_id = $data["EvnSection_id"];
			$MedService_id = $data["MedService_id"];
		}
		// формирование реанимационного периода
		$params = [
			"EvnSection_id" => $EvnSection_id,
			"EvnPS_id" => $EvnPS_id,
			"Person_id" => $data["Person_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"LpuSection_id" => $LpuSection_id,
			"MedService_id" => $MedService_id,
			"Lpu_id" => $data["Lpu_id"],
			"pmUser_id" => $pmUser_id
		];

		$params["ReanimatAgeGroup_id"] = $callObject->getFirstResultFromQuery("
			select
				case 
					when cast(cast(getdate() as date) as timestamp) - interval '29 day' < PS.Person_BirthDay then 1
					when cast(cast(getdate() as date) as timestamp) - interval '29 day' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '1 year' < PS.Person_BirthDay then 2
					when cast(cast(getdate() as date) as timestamp) - interval '1 year' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '4 year' < PS.Person_BirthDay then 3
					when cast(cast(getdate() as date) as timestamp) - interval '4 year' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '18 year' < PS.Person_BirthDay then 4
			    else 5 end
			from
				v_PersonState PS
				inner join PersonEvn PE on PE.Person_id = PS.Person_id
			where
				PE.PersonEvn_id = :PersonEvn_id
		", $params);
		
		$query = "
			select
				EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnReanimatPeriod_ins(
				EvnReanimatPeriod_pid := :EvnSection_id, 
				EvnReanimatPeriod_rid := :EvnPS_id, 
				Lpu_id := :Lpu_id, 
				Server_id := :Server_id, 
				MedService_id := :MedService_id,
				LpuSection_id := :LpuSection_id,
				ReanimResultType_id := null,
				ReanimReasonType_id := 1,
				LpuSectionBedProfile_id := null, 
				ReanimatAgeGroup_id := :ReanimatAgeGroup_id,
				PersonEvn_id := :PersonEvn_id, 
				EvnReanimatPeriod_setDT := getdate(),
				EvnReanimatPeriod_disDT := null,
				EvnReanimatPeriod_didDT := null,
				Morbus_id := null,
				EvnReanimatPeriod_IsSigned := null,
				pmUser_signID := null,
				EvnReanimatPeriod_signDT := null,
				EvnStatus_id := null,
				EvnReanimatPeriod_statusDate := null,
				isReloadCount := null,
				pmUser_id := :pmUser_id
			);
		";
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			$ReturnObject["Status"] = "Oshibka";
			$ReturnObject["Message"] = "Ошибка обращения к БД при формировании реанимационного периода";
			$ReturnObject["fork"] = 0;
			return $ReturnObject;
		}
		$response = $result->result("array");
		if (!is_array($response) || count($response) == 0 || empty($response[0]["EvnReanimatPeriod_id"])) {
			$ReturnObject["Status"] = "Oshibka";
			$ReturnObject["Message"] = "Ошибка обращения к БД при формировании реанимационного периода";
			$ReturnObject["fork"] = 1;
		} else if ((!empty($response[0]["Error_Code"])) || (!empty($response[0]["Error_Msg"]))) {
			$ReturnObject["Status"] = "Oshibka";
			$ReturnObject["Message"] = $response[0]["Error_Code"] . " " . $response[0]["Error_Msg"];
			$ReturnObject["fork"] = 2;
		} else {
			$data["EvnSection_id"] = $EvnSection_id;
			$data["EvnReanimatPeriod_id"] = $response[0]["EvnReanimatPeriod_id"];
			$dyrdyn = $callObject->ReanimatRegister_model->ReanimatRegisterSet($data, 1);
			if (!$dyrdyn) {
				$ReturnObject["Status"] = "Oshibka";
				$ReturnObject["Message"] = "Ошибка обращения к БД при формировании записи регистра реанимации";
				$ReturnObject["fork"] = 3;
			} else {

				$ReturnObject["Status"] = "DoneSuccessfully";
				$ReturnObject["Message"] = "Пациент перевендён в реанимацию. <br> Запись в реестр реанимации: <br>";
				$ReturnObject["EvnReanimatPeriod_id"] = $data["EvnReanimatPeriod_id"];
				$ReturnObject["fork"] = 4;
			}
		}
		return ($ReturnObject);
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function moveToReanimationOutPriem(EvnReanimatPeriod_model $callObject, $data)
	{
		$callObject->load->model("ReanimatRegister_model", "ReanimatRegister_model");
		$ReturnObject = ["EvnReanimatPeriod_id" => "",
			"Status" => "",
			"Message" => "",
			"fork" => 0
		];
		$pmUser_id = $callObject->sessionParams["pmuser_id"];
		$MedService_id = $data["MedService_id"];
		//Выборка определяющая не находится ли пациент в данный момент в реанимации
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select 
		        EvnClass_id as \"EvnClass_id\",
                EvnReanimatPeriod_IsSigned as \"EvnReanimatPeriod_IsSigned\",
                pmUser_signID as \"pmUser_signID\",
                EvnReanimatPeriod_signDT as \"EvnReanimatPeriod_signDT\",
                EvnReanimatPeriod_IsArchive as \"EvnReanimatPeriod_IsArchive\",
                EvnReanimatPeriod_Guid as \"EvnReanimatPeriod_Guid\",
                EvnReanimatPeriod_IndexMinusOne as \"EvnReanimatPeriod_IndexMinusOne\",
                EvnStatus_id as \"EvnStatus_id\",
                EvnReanimatPeriod_statusDate as \"EvnReanimatPeriod_statusDate\",
                EvnReanimatPeriod_IsTransit as \"EvnReanimatPeriod_IsTransit\",
                MedService_id as \"MedService_id\",
                LpuSection_id as \"LpuSection_id\",
                EvnClass_Name as \"EvnClass_Name\",
                ReanimResultType_id as \"ReanimResultType_id\",
                ReanimReasonType_id as \"ReanimReasonType_id\",
                LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
                EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
                EvnReanimatPeriod_setDate as \"EvnReanimatPeriod_setDate\",
                EvnReanimatPeriod_setTime as \"EvnReanimatPeriod_setTime\",
                EvnReanimatPeriod_didDate as \"EvnReanimatPeriod_didDate\",
                EvnReanimatPeriod_didTime as \"EvnReanimatPeriod_didTime\",
                EvnReanimatPeriod_disDate as \"EvnReanimatPeriod_disDate\",
                EvnReanimatPeriod_disTime as \"EvnReanimatPeriod_disTime\",
                EvnReanimatPeriod_pid as \"EvnReanimatPeriod_pid\",
                EvnReanimatPeriod_rid as \"EvnReanimatPeriod_rid\",
                Lpu_id as \"Lpu_id\",
                Server_id as \"Server_id\",
                PersonEvn_id as \"PersonEvn_id\",
                EvnReanimatPeriod_setDT as \"EvnReanimatPeriod_setDT\",
                EvnReanimatPeriod_disDT as \"EvnReanimatPeriod_disDT\",
                EvnReanimatPeriod_didDT as \"EvnReanimatPeriod_didDT\",
                EvnReanimatPeriod_insDT as \"EvnReanimatPeriod_insDT\",
                EvnReanimatPeriod_updDT as \"EvnReanimatPeriod_updDT\",
                EvnReanimatPeriod_Index as \"EvnReanimatPeriod_Index\",
                EvnReanimatPeriod_Count as \"EvnReanimatPeriod_Count\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                Person_id as \"Person_id\",
                Morbus_id as \"Morbus_id\"		
			from v_EvnReanimatPeriod ERP
			where ERP.Person_id = :Person_id
			  and ERP.EvnReanimatPeriod_setDate <= getdate()
			  and ERP.EvnReanimatPeriod_disDate is null
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			$ReturnObject["Status"] = "Oshibka";
			$ReturnObject["Message"] = "Ошибка при выполнении запроса к базе данных";
			return $ReturnObject;
		}
		$resp = $result->result("array");
		//если есть открытый реанимационный период пациента, т.е. он уже в реанимации
		if (count($resp) > 0) {
			$ReturnObject["Status"] = "AlreadyInReanimation";
			$ReturnObject["Message"] = "Данный пациент уже находится в реанимации";
			return $ReturnObject;
		}
		//находим движение в профильном отделении и код самого отделения
		$params = ["EvnPS_id" => $data["EvnPS_id"]];
		$query = "
			select
				Child.EvnSection_id as \"EvnSection_id\",
			    Child.LpuSection_id as \"LpuSection_id\"
			from
				v_EvnPS EvnPS 
				left join lateral(
					select
						ES.EvnSection_id,
					    ES.LpuSection_id
					from v_EvnSection ES
					where ES.EvnSection_pid = EvnPS.EvnPS_id
					  and coalesce(ES.EvnSection_IsPriem, 1) = 1
					limit 1
				) as Child on true
			where EvnPS_id = :EvnPS_id
		";
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			$ReturnObject["Status"] = "Oshibka";
			$ReturnObject["Message"] = "Ошибка при выполнении запроса к базе данных";
			return $ReturnObject;
		}
		$resp = $result->result("array");
		//если выборка пустая
		if (count($resp) == 0) {
			$ReturnObject["Status"] = "AlreadyInReanimation";
			$ReturnObject["Message"] = "Не найдена запись движения в профильном отделении";
			return $ReturnObject;
		}
		$LpuSection_id = $resp[0]["LpuSection_id"];
		$EvnSection_id = $resp[0]["EvnSection_id"];
		// формирование реанимационного периода
		$params = [
			"EvnSection_id" => $EvnSection_id,
			"EvnPS_id" => $data["EvnPS_id"],
			"Person_id" => $data["Person_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"LpuSection_id" => $LpuSection_id,
			"MedService_id" => $MedService_id,
			"Lpu_id" => $data["Lpu_id"],
			"pmUser_id" => $pmUser_id
		];

		$params["ReanimatAgeGroup_id"] = $callObject->getFirstResultFromQuery("
			select
				case
					when cast(cast(getdate() as date) as timestamp) - interval '29 day' < PS.Person_BirthDay then 1
					when cast(cast(getdate() as date) as timestamp) - interval '29 day' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '1 year' < PS.Person_BirthDay then 2
					when cast(cast(getdate() as date) as timestamp) - interval '1 year' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '4 year' < PS.Person_BirthDay then 3
					when cast(cast(getdate() as date) as timestamp) - interval '4 year' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '18 year' < PS.Person_BirthDay then 4
				else 5 end
			 from
			 	v_PersonState PS
				inner join PersonEvn PE on PE.Person_id = PS.Person_id
			 where
			 	PE.PersonEvn_id = :PersonEvn_id
		", $params);
		
		$query = "
			select
                		EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
                		Error_Code as \"Error_Code\",
                		Error_Message as \"Error_Msg\"
			from	p_EvnReanimatPeriod_ins(
				EvnReanimatPeriod_pid := :EvnSection_id, 
				EvnReanimatPeriod_rid := :EvnPS_id, 
				Lpu_id := :Lpu_id, 
				Server_id := :Server_id, 
				MedService_id := :MedService_id,
				LpuSection_id := :LpuSection_id,
				ReanimResultType_id := null,
				ReanimReasonType_id := 1,
				LpuSectionBedProfile_id := null, 
				ReanimatAgeGroup_id := :ReanimatAgeGroup_id,
				PersonEvn_id := :PersonEvn_id, 
				EvnReanimatPeriod_setDT := getdate(),
				EvnReanimatPeriod_disDT := null,
				EvnReanimatPeriod_didDT := null,
				Morbus_id := null,
				EvnReanimatPeriod_IsSigned := null,
				pmUser_signID := null,
				EvnReanimatPeriod_signDT := null,
				EvnStatus_id := null,
				EvnReanimatPeriod_statusDate := null,
				isReloadCount := null,
				pmUser_id := :pmUser_id

			);
		";
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			$ReturnObject["Status"] = "Oshibka";
			$ReturnObject["Message"] = "Ошибка обращения к БД при формировании реанимационного периода";
			$ReturnObject["fork"] = 0;
			return $ReturnObject;
		}
		if (is_object($result)) {
			$response = $result->result_array();
			if (!is_array($response) || count($response) == 0 || empty($response[0]["EvnReanimatPeriod_id"])) {
				$ReturnObject["Status"] = "Oshibka";
				$ReturnObject["Message"] = "Ошибка обращения к БД при формировании реанимационного периода";
				$ReturnObject["fork"] = 1;
			} else if ((!empty($response[0]["Error_Code"])) || (!empty($response[0]["Error_Msg"]))) {
				$ReturnObject["Status"] = "Oshibka";
				$ReturnObject["Message"] = $response[0]["Error_Code"] . " " . $response[0]["Error_Msg"];
				$ReturnObject["fork"] = 2;
			} else {
				$data["EvnSection_id"] = $EvnSection_id;
				$data["EvnReanimatPeriod_id"] = $response[0]["EvnReanimatPeriod_id"];
				$dyrdyn = $callObject->ReanimatRegister_model->ReanimatRegisterSet($data, 1);
				if (!$dyrdyn) {
					$ReturnObject["Status"] = "Oshibka";
					$ReturnObject["Message"] = "Ошибка обращения к БД при формировании записи регистра реанимации";
					$ReturnObject["fork"] = 3;
				} else {
					$ReturnObject["Status"] = "DoneSuccessfully";
					$ReturnObject["Message"] = "Пациент перевендён в реанимацию. <br> Запись в реестр реанимации: <br>";// "Пациент переведён в реанимацию";
					$ReturnObject["EvnReanimatPeriod_id"] = $data["EvnReanimatPeriod_id"];
					$ReturnObject["fork"] = 4;
				}
			}
		}
		return ($ReturnObject);
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function ReanimatPeriodDirectLink_Save(EvnReanimatPeriod_model $callObject, $data)
	{
		$Response = ["success" => "true", "Error_Msg" => ""];
		$params = [
			"EvnDirection_id" => $data["EvnDirection_id"],
			"pmUser_id" => $callObject->sessionParams['pmuser_id'], //текущий пользователь,
			"EvnReanimatPeriod_id" => $data["EvnReanimatPeriod_id"]
		];
		$query = "
			select
			    reanimatperioddirectlink_id as \"ReanimatPeriodDirectLink_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_reanimatperioddirectlink_ins(
			    evnreanimatperiod_id := :EvnReanimatPeriod_id,
			    evndirection_id := :EvnDirection_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$EvnScaleResult = $result->result("array");
		if (empty($EvnScaleResult[0]["ReanimatPeriodDirectLink_id"]) || !empty($EvnScaleResult[0]["Error_Code"]) && !empty($EvnScaleResult[0]["Error_Message"])) {
			$Response["success"] = "false";
			$Response["Error_Msg"] = trim($EvnScaleResult[0]["Error_Code"] . " " . $EvnScaleResult[0]["Error_Message"]);
		}
		return $Response;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function ReanimatPeriodPrescrLink_Save(EvnReanimatPeriod_model $callObject, $data)
	{
		$Response = ["success" => "true", "Error_Msg" => ""];
		$params = [
			"EvnPrescr_id" => $data["EvnPrescr_id"],
			"pmUser_id" => $callObject->sessionParams['pmuser_id'], //текущий пользователь,				
			"EvnReanimatPeriod_id" => $data["EvnReanimatPeriod_id"]
		];
		$query = "
			select
			    reanimatperiodprescrlink_id as \"ReanimatPeriodPrescrLink_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_reanimatperiodprescrlink_ins(
			    evnreanimatperiod_id := :EvnReanimatPeriod_id,
			    evnprescr_id := :EvnPrescr_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$EvnScaleResult = $result->result("array");
		if (empty($EvnScaleResult[0]["ReanimatPeriodPrescrLink_id"]) || !empty($EvnScaleResult[0]["Error_Code"]) || !empty($EvnScaleResult[0]["Error_Message"])) {
			$Response["success"] = "false";
			$Response["Error_Msg"] = trim($EvnScaleResult[0]["Error_Code"] . " " . $EvnScaleResult[0]["Error_Message"]);
		}
		return $Response;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function EvnReanimatCondition_Save(EvnReanimatPeriod_model $callObject, $data)
	{
		$Response = [
			"success" => "true",
			"Error_Msg" => ""
		];
		$result = null;
		$data["EvnReanimatCondition_setDate"] .= " " . $data["EvnReanimatCondition_setTime"] . ":00";
		$data["EvnReanimatCondition_disDate"] = (($data["EvnReanimatCondition_disDate"] != "") && $data["EvnReanimatCondition_disTime"] != "") ? $data["EvnReanimatCondition_disDate"] . " " . $data["EvnReanimatCondition_disTime"] . ":00" : null;
		$params = [
			"EvnReanimatCondition_id" => $data["EvnReanimatCondition_id"],
			"EvnReanimatCondition_pid" => $data["EvnReanimatCondition_pid"],
			"EvnReanimatCondition_rid" => $data["EvnReanimatCondition_rid"],
			"Lpu_id" => $data["Lpu_id"],
			"Person_id" => $data["Person_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"Server_id" => isset($data["Server_id"]) ? $data["Server_id"] : null,
			"EvnReanimatCondition_setDT" => isset($data["EvnReanimatCondition_setDate"]) ? $data["EvnReanimatCondition_setDate"] : null,
			"EvnReanimatCondition_disDT" => isset($data["EvnReanimatCondition_disDate"]) ? $data["EvnReanimatCondition_disDate"] : null,
			"ReanimStageType_id" => $data["ReanimStageType_id"],
			"ReanimConditionType_id" => isset($data["ReanimConditionType_id"]) ? $data["ReanimConditionType_id"] : null,
			"EvnReanimatCondition_Complaint" => isset($data["EvnReanimatCondition_Complaint"]) ? $data["EvnReanimatCondition_Complaint"] : null,
			"SkinType_id" => isset($data["SkinType_id"]) ? $data["SkinType_id"] : null,
			"EvnReanimatCondition_SkinTxt" => isset($data["EvnReanimatCondition_SkinTxt"]) ? $data["EvnReanimatCondition_SkinTxt"] : null,
			"ConsciousType_id" => isset($data["ConsciousType_id"]) ? $data["ConsciousType_id"] : null,
			"BreathingType_id" => isset($data["BreathingType_id"]) ? $data["BreathingType_id"] : null,
			"EvnReanimatCondition_IVLapparatus" => isset($data["EvnReanimatCondition_IVLapparatus"]) ? $data["EvnReanimatCondition_IVLapparatus"] : null,
			"EvnReanimatCondition_IVLparameter" => isset($data["EvnReanimatCondition_IVLparameter"]) ? $data["EvnReanimatCondition_IVLparameter"] : null,
			"EvnReanimatCondition_Auscultatory" => isset($data["EvnReanimatCondition_Auscultatory"]) ? $data["EvnReanimatCondition_Auscultatory"] : null,
			"HeartTonesType_id" => isset($data["HeartTonesType_id"]) ? $data["HeartTonesType_id"] : null,
			"HemodynamicsType_id" => isset($data["HemodynamicsType_id"]) ? $data["HemodynamicsType_id"] : null,
			"EvnReanimatCondition_Pressure" => isset($data["EvnReanimatCondition_Pressure"]) ? $data["EvnReanimatCondition_Pressure"] : null,
			"EvnReanimatCondition_HeartFrequency" => isset($data["EvnReanimatCondition_HeartFrequency"]) ? $data["EvnReanimatCondition_HeartFrequency"] : null,
			"EvnReanimatCondition_StatusLocalis" => isset($data["EvnReanimatCondition_StatusLocalis"]) ? $data["EvnReanimatCondition_StatusLocalis"] : null,
			"AnalgesiaType_id" => isset($data["AnalgesiaType_id"]) ? $data["AnalgesiaType_id"] : null,
			"EvnReanimatCondition_AnalgesiaTxt" => isset($data["EvnReanimatCondition_AnalgesiaTxt"]) ? $data["EvnReanimatCondition_AnalgesiaTxt"] : null,
			"EvnReanimatCondition_Diuresis" => isset($data["EvnReanimatCondition_Diuresis"]) ? $data["EvnReanimatCondition_Diuresis"] : null,
			"UrineType_id" => isset($data["UrineType_id"]) ? $data["UrineType_id"] : null,
			"EvnReanimatCondition_UrineTxt" => isset($data["EvnReanimatCondition_UrineTxt"]) ? $data["EvnReanimatCondition_UrineTxt"] : null,
			"EvnReanimatCondition_Conclusion" => isset($data["EvnReanimatCondition_Conclusion"]) ? $data["EvnReanimatCondition_Conclusion"] : null,
			"ReanimArriveFromType_id" => isset($data["ReanimArriveFromType_id"]) ? $data["ReanimArriveFromType_id"] : null,
			"EvnReanimatCondition_HemodynamicsTxt" => isset($data["EvnReanimatCondition_HemodynamicsTxt"]) ? $data["EvnReanimatCondition_HemodynamicsTxt"] : null,
			"EvnReanimatCondition_NeurologicStatus" => isset($data["EvnReanimatCondition_NeurologicStatus"]) ? $data["EvnReanimatCondition_NeurologicStatus"] : null,
			"EvnReanimatCondition_sofa" => isset($data["EvnReanimatCondition_sofa"]) ? $data["EvnReanimatCondition_sofa"] : null,
			"EvnReanimatCondition_apache" => isset($data["EvnReanimatCondition_apache"]) ? $data["EvnReanimatCondition_apache"] : null,
			"EvnReanimatCondition_Saturation" => isset($data["EvnReanimatCondition_Saturation"]) ? $data["EvnReanimatCondition_Saturation"] : null,
			"EvnReanimatCondition_OxygenFraction" => isset($data["EvnReanimatCondition_OxygenFraction"]) ? $data["EvnReanimatCondition_OxygenFraction"] : null,
			"EvnReanimatCondition_OxygenPressure" => isset($data["EvnReanimatCondition_OxygenPressure"]) ? $data["EvnReanimatCondition_OxygenPressure"] : null,
			"EvnReanimatCondition_PaOFiO" => isset($data["EvnReanimatCondition_PaOFiO"]) ? $data["EvnReanimatCondition_PaOFiO"] : null,
			//"NutritiousType_id" => isset($data["NutritiousType_id"]) ? $data["NutritiousType_id"] : null,
			//"EvnReanimatCondition_NutritiousTxt" => isset($data["EvnReanimatCondition_NutritiousTxt"]) ? $data["EvnReanimatCondition_NutritiousTxt"] : null,
			"EvnReanimatCondition_Temperature" => isset($data["EvnReanimatCondition_Temperature"]) ? $data["EvnReanimatCondition_Temperature"] : null,
			"EvnReanimatCondition_InfusionVolume" => isset($data["EvnReanimatCondition_InfusionVolume"]) ? $data["EvnReanimatCondition_InfusionVolume"] : null,
			"EvnReanimatCondition_DiuresisVolume" => isset($data["EvnReanimatCondition_DiuresisVolume"]) ? $data["EvnReanimatCondition_DiuresisVolume"] : null,
			"EvnReanimatCondition_CollectiveSurvey" => isset($data["EvnReanimatCondition_CollectiveSurvey"]) ? $data["EvnReanimatCondition_CollectiveSurvey"] : null,
			"EvnReanimatCondition_SyndromeType" => isset($data["EvnReanimatCondition_SyndromeType"]) ? $data["EvnReanimatCondition_SyndromeType"] : null,
			"EvnReanimatCondition_ConsTxt" => isset($data["EvnReanimatCondition_ConsTxt"]) ? $data["EvnReanimatCondition_ConsTxt"] : null,
			"SpeechDisorderType_id" => isset($data["SpeechDisorderType_id"]) ? $data["SpeechDisorderType_id"] : null,
			"EvnReanimatCondition_rass" => isset($data["EvnReanimatCondition_rass"]) ? $data["EvnReanimatCondition_rass"] : null,
			"EvnReanimatCondition_Eyes" => isset($data["EvnReanimatCondition_Eyes"]) ? $data["EvnReanimatCondition_Eyes"] : null,
			"EvnReanimatCondition_WetTurgor" => isset($data["EvnReanimatCondition_WetTurgor"]) ? $data["EvnReanimatCondition_WetTurgor"] : null,
			"EvnReanimatCondition_waterlow" => isset($data["EvnReanimatCondition_waterlow"]) ? $data["EvnReanimatCondition_waterlow"] : null,
			"SkinType_mid" => isset($data["SkinType_mid"]) ? $data["SkinType_mid"] : null,
			"EvnReanimatCondition_MucusTxt" => isset($data["EvnReanimatCondition_MucusTxt"]) ? $data["EvnReanimatCondition_MucusTxt"] : null,
			"EvnReanimatCondition_IsMicrocDist" => isset($data["EvnReanimatCondition_IsMicrocDist"]) ? $data["EvnReanimatCondition_IsMicrocDist"] : null,
			"EvnReanimatCondition_IsPeriphEdem" => isset($data["EvnReanimatCondition_IsPeriphEdem"]) ? $data["EvnReanimatCondition_IsPeriphEdem"] : null,
			"EvnReanimatCondition_Reflexes" => isset($data["EvnReanimatCondition_Reflexes"]) ? $data["EvnReanimatCondition_Reflexes"] : null,
			"EvnReanimatCondition_BreathFrequency" => isset($data["EvnReanimatCondition_BreathFrequency"]) ? $data["EvnReanimatCondition_BreathFrequency"] : null,
			"BreathAuscult_List" => isset($data["BreathAuscult_List"]) ? json_decode($data["BreathAuscult_List"], true) : null,
			"EvnReanimatCondition_HeartTones" => isset($data["EvnReanimatCondition_HeartTones"]) ? $data["EvnReanimatCondition_HeartTones"] : null,
			"EvnReanimatCondition_IsHemodStab" => isset($data["EvnReanimatCondition_IsHemodStab"]) ? $data["EvnReanimatCondition_IsHemodStab"] : null,
			"EvnReanimatCondition_Tongue" => isset($data["EvnReanimatCondition_Tongue"]) ? $data["EvnReanimatCondition_Tongue"] : null,
			"EvnReanimatCondition_Paunch" => isset($data["EvnReanimatCondition_Paunch"]) ? $data["EvnReanimatCondition_Paunch"] : null,
			"EvnReanimatCondition_PaunchTxt" => isset($data["EvnReanimatCondition_PaunchTxt"]) ? $data["EvnReanimatCondition_PaunchTxt"] : null,
			"PeristalsisType_id" => isset($data["PeristalsisType_id"]) ? $data["PeristalsisType_id"] : null,
			"EvnReanimatCondition_VBD" => isset($data["EvnReanimatCondition_VBD"]) ? $data["EvnReanimatCondition_VBD"] : null,
			"EvnReanimatCondition_Defecation" => isset($data["EvnReanimatCondition_Defecation"]) ? $data["EvnReanimatCondition_Defecation"] : null,
			"EvnReanimatCondition_DefecationTxt" => isset($data["EvnReanimatCondition_DefecationTxt"]) ? $data["EvnReanimatCondition_DefecationTxt"] : null,
			"LimbImmobilityType_id" => isset($data["LimbImmobilityType_id"]) ? $data["LimbImmobilityType_id"] : null,
			"EvnReanimatCondition_MonopLoc" => isset($data["EvnReanimatCondition_MonopLoc"]) ? $data["EvnReanimatCondition_MonopLoc"] : null,
			"EvnReanimatCondition_mrc" => isset($data["EvnReanimatCondition_mrc"]) ? $data["EvnReanimatCondition_mrc"] : null,
			"EvnReanimatCondition_MeningSign" => isset($data["EvnReanimatCondition_MeningSign"]) ? $data["EvnReanimatCondition_MeningSign"] : null,
			"EvnReanimatCondition_MeningSignTxt" => isset($data["EvnReanimatCondition_MeningSignTxt"]) ? $data["EvnReanimatCondition_MeningSignTxt"] : null,
			"EvnReanimatCondition_glasgow" => isset($data["EvnReanimatCondition_glasgow"]) ? $data["EvnReanimatCondition_glasgow"] : null,			//BOB - 24.01.2019
			"EvnReanimatCondition_four" => isset($data["EvnReanimatCondition_four"]) ? $data["EvnReanimatCondition_four"] : null,			//BOB - 24.01.2019
			"EvnReanimatCondition_SyndromeTxt" => isset($data["EvnReanimatCondition_SyndromeTxt"]) ? $data["EvnReanimatCondition_SyndromeTxt"] : null,			//BOB - 24.01.2019
			"EvnReanimatCondition_Doctor" => isset($data["EvnReanimatCondition_Doctor"]) ? $data["EvnReanimatCondition_Doctor"] : null,			//BOB - 24.01.2019
			"pmUser_id" => $data["pmUser_id"]
		];
		$procedure = ($params["EvnReanimatCondition_id"] == null) ? "p_EvnReanimatCondition_ins" : "p_EvnReanimatCondition_upd";
		$selectString = "
			evnreanimatcondition_id as \"EvnReanimatCondition_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
				evnreanimatcondition_id := :EvnReanimatCondition_id,
				evnreanimatcondition_pid := :EvnReanimatCondition_pid,
				lpu_id := :Lpu_id,
				server_id := :Server_id,
				personevn_id := :PersonEvn_id,
				evnreanimatcondition_setdt := :EvnReanimatCondition_setDT,
				evnreanimatcondition_disdt := :EvnReanimatCondition_disDT,
				evnreanimatcondition_complaint := :EvnReanimatCondition_Complaint,
				reanimconditiontype_id := :ReanimConditionType_id,
				skintype_id := :SkinType_id,
				evnreanimatcondition_skintxt := :EvnReanimatCondition_SkinTxt,
				conscioustype_id := :ConsciousType_id,
				breathingtype_id := :BreathingType_id,
				evnreanimatcondition_ivlapparatus := :EvnReanimatCondition_IVLapparatus,
				evnreanimatcondition_ivlparameter := :EvnReanimatCondition_IVLparameter,
				evnreanimatcondition_auscultatory := :EvnReanimatCondition_Auscultatory,
				hearttonestype_id := :HeartTonesType_id,
				hemodynamicstype_id := :HemodynamicsType_id,
				evnreanimatcondition_pressure := :EvnReanimatCondition_Pressure,
				evnreanimatcondition_heartfrequency := :EvnReanimatCondition_HeartFrequency,
				evnreanimatcondition_statuslocalis := :EvnReanimatCondition_StatusLocalis,
				analgesiatype_id := :AnalgesiaType_id,
				evnreanimatcondition_diuresis := :EvnReanimatCondition_Diuresis,
				urinetype_id := :UrineType_id,
				evnreanimatcondition_urinetxt := :EvnReanimatCondition_UrineTxt,
				evnreanimatcondition_conclusion := :EvnReanimatCondition_Conclusion,
				reanimstagetype_id := :ReanimStageType_id,
				evnreanimatcondition_analgesiatxt := :EvnReanimatCondition_AnalgesiaTxt,
				reanimarrivefromtype_id := :ReanimArriveFromType_id,
				evnreanimatcondition_hemodynamicstxt := :EvnReanimatCondition_HemodynamicsTxt,
				evnreanimatcondition_neurologicstatus := :EvnReanimatCondition_NeurologicStatus,
				evnreanimatcondition_sofa := :EvnReanimatCondition_sofa,
				evnreanimatcondition_apache := :EvnReanimatCondition_apache,
				evnreanimatcondition_saturation := :EvnReanimatCondition_Saturation,
				evnreanimatcondition_oxygenfraction := :EvnReanimatCondition_OxygenFraction,
				evnreanimatcondition_oxygenpressure := :EvnReanimatCondition_OxygenPressure,
				evnreanimatcondition_paofio := :EvnReanimatCondition_PaOFiO,
				evnreanimatcondition_temperature := :EvnReanimatCondition_Temperature,
				evnreanimatcondition_infusionvolume := :EvnReanimatCondition_InfusionVolume,
				evnreanimatcondition_diuresisvolume := :EvnReanimatCondition_DiuresisVolume,
				evnreanimatcondition_collectivesurvey := :EvnReanimatCondition_CollectiveSurvey,
				evnreanimatcondition_syndrometype := :EvnReanimatCondition_SyndromeType,
				evnreanimatcondition_constxt := :EvnReanimatCondition_ConsTxt,
				speechdisordertype_id := :SpeechDisorderType_id,
				evnreanimatcondition_rass := :EvnReanimatCondition_rass,
				evnreanimatcondition_eyes := :EvnReanimatCondition_Eyes,
				evnreanimatcondition_wetturgor := :EvnReanimatCondition_WetTurgor,
				evnreanimatcondition_waterlow := :EvnReanimatCondition_waterlow,
				skintype_mid := :SkinType_mid,
				evnreanimatcondition_mucustxt := :EvnReanimatCondition_MucusTxt,
				evnreanimatcondition_ismicrocdist := :EvnReanimatCondition_IsMicrocDist,
				evnreanimatcondition_isperiphedem := :EvnReanimatCondition_IsPeriphEdem,
				evnreanimatcondition_reflexes := :EvnReanimatCondition_Reflexes,
				evnreanimatcondition_breathfrequency := :EvnReanimatCondition_BreathFrequency,
				evnreanimatcondition_hearttones := :EvnReanimatCondition_HeartTones,
				evnreanimatcondition_ishemodstab := :EvnReanimatCondition_IsHemodStab,
				evnreanimatcondition_tongue := :EvnReanimatCondition_Tongue,
				evnreanimatcondition_paunch := :EvnReanimatCondition_Paunch,
				evnreanimatcondition_paunchtxt := :EvnReanimatCondition_PaunchTxt,
				peristalsistype_id := :PeristalsisType_id,
				evnreanimatcondition_vbd := :EvnReanimatCondition_VBD,
				evnreanimatcondition_defecation := :EvnReanimatCondition_Defecation,
				evnreanimatcondition_defecationtxt := :EvnReanimatCondition_DefecationTxt,
				limbimmobilitytype_id := :LimbImmobilityType_id,
				evnreanimatcondition_monoploc := :EvnReanimatCondition_MonopLoc,
				evnreanimatcondition_mrc := :EvnReanimatCondition_mrc,
				evnreanimatcondition_meningsign := :EvnReanimatCondition_MeningSign,
				evnreanimatcondition_meningsigntxt := :EvnReanimatCondition_MeningSignTxt,
				EvnReanimatCondition_glasgow := :EvnReanimatCondition_glasgow,
				EvnReanimatCondition_four := :EvnReanimatCondition_four,
				EvnReanimatCondition_SyndromeTxt := :EvnReanimatCondition_SyndromeTxt,
				EvnReanimatCondition_Doctor := :EvnReanimatCondition_Doctor,
				pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$EvnScaleResult = $result->result("array");
		if (empty($EvnScaleResult[0]["EvnReanimatCondition_id"]) || !empty($EvnScaleResult[0]["Error_Code"]) || !empty($EvnScaleResult[0]["Error_Message"])) {
			$Response["success"] = "false";
			$Response["Error_Msg"] = trim($EvnScaleResult[0]["Error_Code"] . " " . $EvnScaleResult[0]["Error_Message"]);
			return $Response;
		}
		//СОХРАНЕНИЕ АУСКУЛЬТАТИВНОГО
		if (isset($params['BreathAuscult_List'])) {
			foreach ($params['BreathAuscult_List'] as $BreathAuscult) {
				$BreathAuscult['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;
				$BreathAuscult['EvnReanimatCondition_id'] = $EvnScaleResult[0]['EvnReanimatCondition_id'];
				switch($BreathAuscult['BA_RecordStatus']) {
					case 0: $procedure = 'p_BreathAuscultative_ins';break;
					case 2: $procedure = 'p_BreathAuscultative_upd';break;
					default: $procedure = null;
				}
				if ($procedure == null) {
					throw new Exception("Не пришел обязательный параметр: BA_RecordStatus");
				}
				$selectString = "
				    breathauscultative_id as \"BreathAuscultative_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				";
				$query = "
					select {$selectString}
					from {$procedure}(
					    breathauscultative_id := :BreathAuscultative_id,
					    evnreanimatcondition_id := :EvnReanimatCondition_id,
					    sidetype_id := :SideType_id,
					    breathauscultative_auscult := :BreathAuscultative_Auscult,
					    breathauscultative_ausculttxt := :BreathAuscultative_AuscultTxt,
					    breathauscultative_rale := :BreathAuscultative_Rale,
					    breathauscultative_raletxt := :BreathAuscultative_RaleTxt,
					    breathauscultative_ispleudrain := :BreathAuscultative_IsPleuDrain,
					    breathauscultative_pleudraintxt := :BreathAuscultative_PleuDrainTxt,
					    pmuser_id := :pmUser_id
					);
				";
				$result = $callObject->db->query($query, $BreathAuscult);
				if (!is_object($result)) {
					return false;
				}
				$BreathAuscultResult = $result->result("array");
				if (empty($BreathAuscultResult[0]["BreathAuscultative_id"]) || !empty($BreathAuscultResult[0]["Error_Code"]) || !empty($BreathAuscultResult[0]["Error_Message"])) {
					$Response["success"] = "false";
					$Response["Error_Msg"] = trim($BreathAuscultResult[0]["Error_Code"] . " " . $BreathAuscultResult[0]["Error_Message"]);
					print_r(array($BreathAuscultResult, $Response));exit;
					return $Response;
				}
			}
		}
		return $Response;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function EvnReanimatCondition_Del(EvnReanimatPeriod_model $callObject, $data)
	{
		$Response = ["success" => "true", "Error_Msg" => ""];
		$pmUser_id = $callObject->sessionParams["pmuser_id"];
		$queryParams = [
			"EvnReanimatCondition_id" => $data["EvnReanimatCondition_id"],
			"pmUser_id" => $pmUser_id
		];
		$query = "
			select BreathAuscultative_id as \"BreathAuscultative_id\"
			from BreathAuscultative
			where EvnReanimatCondition_id = :EvnReanimatCondition_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$BreathAuscultative = $result->result("array");
		foreach ($BreathAuscultative as $BreathAuscult) {
			$queryParams['BreathAuscultative_id'] = $BreathAuscult['BreathAuscultative_id'];
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_breathauscultative_del(
				    breathauscultative_id := :BreathAuscultative_id,
				    pmuser_id := :pmUser_id
				);
			";
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_evnreanimatcondition_del(
			    evnreanimatcondition_id := :EvnReanimatCondition_id,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$EvnScaleResult = $result->result("array");
		if (($EvnScaleResult[0]["Error_Code"] != null) || ($EvnScaleResult[0]["Error_Message"] != null)) {
			$Response["success"] = "false";
			$Response["Error_Msg"] = $EvnScaleResult[0]["Error_Code"] . " " . $EvnScaleResult[0]["Error_Message"];
		}
		return $Response;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $arg
	 * @return array|bool
	 */
	public static function EvnReanimatPeriod_Save(EvnReanimatPeriod_model $callObject, $arg)
 	{
		$Response = ["success" => "true", "Error_Msg" => ""];
		$arg["EvnReanimatPeriod_setDate"] .= " " . $arg["EvnReanimatPeriod_setTime"] . ":00";
		$arg["EvnReanimatPeriod_disDate"] = (($arg["EvnReanimatPeriod_disDate"] != "") && $arg["EvnReanimatPeriod_disTime"] != "") ? $arg["EvnReanimatPeriod_disDate"] . " " . $arg["EvnReanimatPeriod_disTime"] . ":00" : null;
		$params = [
			"EvnReanimatPeriod_id" => $arg["EvnReanimatPeriod_id"],
			"EvnReanimatPeriod_pid" => $arg["EvnReanimatPeriod_pid"],
			"EvnReanimatPeriod_setDT" => $arg["EvnReanimatPeriod_setDate"],
			"EvnReanimatPeriod_disDT" => $arg["EvnReanimatPeriod_disDate"]
		];

  
        $query = "
            CREATE OR REPLACE FUNCTION pg_temp.exp_Query( out _err_status text,out _err_message text )
            LANGUAGE 'plpgsql'

            AS $$
            DECLARE
              	_EvnReanimatPeriod_id bigint  := :EvnReanimatPeriod_id;
				_EvnReanimatPeriod_pid bigint := :EvnReanimatPeriod_pid;
				_EvnReanimatPeriod_setDT TIMESTAMP := :EvnReanimatPeriod_setDT;
				_EvnReanimatPeriod_disDT TIMESTAMP := :EvnReanimatPeriod_disDT;

				_EvnSection_setDT TIMESTAMP;
				_EvnSection_disDT TIMESTAMP;
				_EvnSection_IsInReg bigint;
				_Person_id bigint;

				_EvnReanimatPeriod_id_other bigint;
				_EvnReanimatPeriod_setDT_other TIMESTAMP;
				_EvnReanimatPeriod_disDT_other TIMESTAMP;

				_Child_setDT_Min  TIMESTAMP;
				_Child_setDT_Max  TIMESTAMP;
				_Child_disDT_Max  TIMESTAMP;


            BEGIN

				_err_status := 'norm';
				_err_message := '';

	            if ((_EvnReanimatPeriod_disDT is not null) and (_EvnReanimatPeriod_setDT > _EvnReanimatPeriod_disDT)) then
					_err_status := 'err';
					_err_message := '~Дата начало РП превышает дату окончания РП!';
                else
                    begin
                        select EvnSection_setDT,
                               EvnSection_disDT,
                               EvnSection_IsInReg,
                               Person_id
                            into
                                _EvnSection_setDT,
                                _EvnSection_disDT,
                                _EvnSection_IsInReg,
                                _Person_id
                            from dbo.v_EvnSection
                            where EvnSection_id = _EvnReanimatPeriod_pid;

                            if (COALESCE(_EvnSection_IsInReg, 1) <> 1 ) then
                                _err_status := 'err';
                                _err_message := '~Случай лечения уже в реестре ОМП, изменения РП невозможны!';
                            else
                                begin
                                    if not ((_EvnReanimatPeriod_setDT >= _EvnSection_setDT) and ((_EvnReanimatPeriod_setDT < _EvnSection_disDT) or (_EvnSection_disDT is null))) then
                                        _err_status := 'err';
                                        _err_message := _err_message || '~Начало РП вне периода Движения';
                                    end if;
                                    _EvnReanimatPeriod_id_other := null;
                                    _EvnReanimatPeriod_disDT_other := null;
                                    select
                                        EvnReanimatPeriod_id,
                                        EvnReanimatPeriod_disDT
                                    into
                                        _EvnReanimatPeriod_id_other,
                                        _EvnReanimatPeriod_disDT_other
                                    from dbo.v_EvnReanimatPeriod
                                    where Person_id = _Person_id
                                    and EvnReanimatPeriod_setDT < _EvnReanimatPeriod_setDT
                                    and EvnReanimatPeriod_id <> _EvnReanimatPeriod_id
                                    order by EvnReanimatPeriod_setDT desc
                                    limit 1 ;
                                    if ((_EvnReanimatPeriod_id_other is not null) and (_EvnReanimatPeriod_setDT <= _EvnReanimatPeriod_disDT_other)) then
                                         _err_status := 'err';
                                        _err_message := _err_message || '~Начало РП раньше или равно окончанию предыдущего РП';
                                    end if;

                                    select
                                        Evn_setDT into _Child_setDT_Min
                                    from v_Evn
                                    where Evn_pid = _EvnReanimatPeriod_id
                                    order by Evn_setDT
                                    limit 1;

                                    if ((_Child_setDT_Min is not null) and  (_EvnReanimatPeriod_setDT > _Child_setDT_Min)) then
                                        _err_status := 'err';
                                        _err_message := _err_message || '~Начало РП позже начала дочернего события';
                                    end if;

                                    _EvnReanimatPeriod_id_other := null;
                                    _EvnReanimatPeriod_setDT_other := null;

                                    select
                                        EvnReanimatPeriod_id,
                                        EvnReanimatPeriod_setDT
                                     into
                                        _EvnReanimatPeriod_id_other
                                        _EvnReanimatPeriod_setDT_other
                                    from dbo.v_EvnReanimatPeriod
                                    where Person_id = _Person_id and EvnReanimatPeriod_setDT > _EvnReanimatPeriod_setDT
                                    and EvnReanimatPeriod_id <> _EvnReanimatPeriod_id
                                    order by EvnReanimatPeriod_setDT asc
                                    limit 1 ;

                                    if (_EvnReanimatPeriod_disDT is null) then
                                                begin
                                                    if (_EvnSection_disDT is not null) then
                                                        _err_status := 'err';
                                                        _err_message := _err_message || '~Окончание РП обязательно должно быть если есть дата окончания Движения';
                                                    end if;
                                                    if (_EvnReanimatPeriod_id_other is not null) then
                                                        _err_status := 'err';
                                                        _err_message := _err_message || '~Окончание РП отсутствует при наличии следующего РП';
                                                    end if;
                                                end;
                                        else
                                                begin
                                                    if not ((_EvnReanimatPeriod_disDT >= _EvnSection_setDT) and ((_EvnReanimatPeriod_disDT < _EvnSection_disDT) or (_EvnSection_disDT is null))) then
                                                        _err_status := 'err';
                                                        _err_message := _err_message || '~Окончание РП вне периода Движения';
                                                    end if;
                                                    if ((_EvnReanimatPeriod_id_other is not null) and (_EvnReanimatPeriod_disDT >= _EvnReanimatPeriod_setDT_other)) then
                                                           _err_status := 'err';
                                                        _err_message :=_err_message || '~Окончание РП позже или равно началу следующего РП';
                                                    end if;

                                                    select
                                                        Evn_setDT into _Child_setDT_Max
                                                    from v_Evn
                                                    where Evn_pid = _EvnReanimatPeriod_id
                                                    order by Evn_setDT desc
                                                    limit 1 ;

                                                    select
                                                        Evn_disDT into _Child_disDT_Max
                                                    from v_Evn
                                                    where Evn_pid = _EvnReanimatPeriod_id
                                                    order by Evn_disDT desc
                                                    limit 1 ;

                                                    if ((_Child_disDT_Max is not null) and (_EvnReanimatPeriod_disDT < _Child_disDT_Max) or ((_Child_setDT_Max is not null) and (_EvnReanimatPeriod_disDT < _Child_setDT_Max))) then
                                                        _err_status = 'err';
                                                        _err_message = _err_message || '~Окончание РП раньше окончания или начала дочернего события';
                                                    end if;
                                                end;
                                     end if;
                                end;
                            end if;
                    end;
               end if;

            END;
            $$;
        ";


		/**@var CI_DB_result $result */
    
		$result = $callObject->db->query($query, $params);

		$query = "
            select
                _err_status as \"err_status\",
                _err_message as \"err_message\"                
            from 
                pg_temp.exp_Query()
            ";
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		$resultArray = $result->result("array");
		if ($resultArray[0]["err_status"] == "err") {
			$Response["success"] = "false";
			$Response["Error_Msg"] = $resultArray[0]["err_message"];
			return $Response;
		}

        //BOB - 08.10.2019
        //log_message('debug', 'EvnReanimatPeriod_model=>EvnReanimatPeriod_Save isset($resultArray[0][EvnReanimatPeriod_setDT_other]) = '.print_r(isset($resultArray[0]['EvnReanimatPeriod_setDT_other']), 1)); //BOB - 17.05.2018
        $LastRP = isset($resultArray[0]['EvnReanimatPeriod_setDT_other']) ? 0 : 1; //BOB - 08.10.2019 - true - если сохраняемая запись РП последняя, т.е. при контроле не была найдена болоее поздняя
        //log_message('debug', 'EvnReanimatPeriod_model=>EvnReanimatPeriod_Save $LastRP_2 = '.print_r($LastRP, 1)); 
        //BOB - 08.10.2019	


        $params = [
			"EvnReanimatPeriod_id" => $arg["EvnReanimatPeriod_id"],
			"EvnReanimatPeriod_pid" => $arg["EvnReanimatPeriod_pid"],
			"EvnReanimatPeriod_setDT" => $arg["EvnReanimatPeriod_setDate"],
			"EvnReanimatPeriod_disDT" => $arg["EvnReanimatPeriod_disDate"],
			"ReanimReasonType_id" => $arg["ReanimReasonType_id"],
			"ReanimResultType_id" => $arg["ReanimResultType_id"],
			"LpuSectionBedProfile_id" => $arg["LpuSectionBedProfile_id"],
			"ReanimatAgeGroup_id" => $arg["ReanimatAgeGroup_id"],		//BOB - 23.01.2020
			"Lpu_id" => $arg["Lpu_id"],
			"Server_id" => $arg["Server_id"],
			"PersonEvn_id" => $arg["PersonEvn_id"],
			"pmUser_id" => $arg["pmUser_id"]
		];
		$query = "
			select
			    evnreanimatperiod_id as \"EvnReanimatPeriod_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_evnreanimatperiod_upd(
			    evnreanimatperiod_id := :EvnReanimatPeriod_id,
			    evnreanimatperiod_pid := :EvnReanimatPeriod_pid,
			    evnreanimatperiod_setdt := :EvnReanimatPeriod_setDT,
			    evnreanimatperiod_disdt := :EvnReanimatPeriod_disDT,
			    reanimreasontype_id := :ReanimReasonType_id,
			    reanimresulttype_id := :ReanimResultType_id,
			    lpusectionbedprofile_id := :LpuSectionBedProfile_id,
			    reanimatagegroup_id := :ReanimatAgeGroup_id,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$resultArray = $result->result("array");
		if ((empty($resultArray[0]["EvnReanimatPeriod_id"])) || (!empty($resultArray[0]["Error_Code"])) || (!empty($resultArray[0]["Error_Message"]))) {
			$Response["success"] = "false";
			$Response["Error_Msg"] = $resultArray[0]["Error_Code"] . "~" . $resultArray[0]["Error_Message"];
			//!!!!здесь д.б. return $Response;
		}
		
			//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $params));
			
		$arg['ReanimatRegister_IsPeriodNow'] = 2;	//BOB - 08.10.2019

		
		
		//Если закрытие РП
		if ($arg["EvnReanimatPeriod_disDate"] != null) {
			$params = ["EvnReanimatAction_pid" => $arg["EvnReanimatPeriod_id"]];
			$query = "
				select
				    EvnReanimatAction_id as \"EvnReanimatAction_id\",
				    ReanimatActionType_id as \"ReanimatActionType_id\",
				    UslugaComplex_id as \"UslugaComplex_id\",
				    EvnUsluga_id as \"EvnUsluga_id\",
				    ReanimDrugType_id as \"ReanimDrugType_id\",
				    EvnReanimatAction_DrugDose as \"EvnReanimatAction_DrugDose\",
				    EvnDrug_id as \"EvnDrug_id\",
				    EvnReanimatAction_MethodCode as \"EvnReanimatAction_MethodCode\",
				    EvnReanimatAction_ObservValue as \"EvnReanimatAction_ObservValue\",
				    ReanimatCathetVeins_id as \"ReanimatCathetVeins_id\",
				    CathetFixType_id as \"CathetFixType_id\",
				    EvnReanimatAction_CathetNaborName as \"EvnReanimatAction_CathetNaborName\",
				    NutritiousType_id as \"NutritiousType_id\",
				    EvnReanimatAction_DrugUnit as \"EvnReanimatAction_DrugUnit\",
				    EvnReanimatAction_MethodTxt as \"EvnReanimatAction_MethodTxt\",
				    EvnReanimatAction_NutritVol as \"EvnReanimatAction_NutritVol\",
				    EvnReanimatAction_NutritEnerg as \"EvnReanimatAction_NutritEnerg\",
				    EvnReanimatAction_setDT as \"EvnReanimatAction_setDT\"
				from
				    v_EvnReanimatAction
				where
				    EvnReanimatAction_pid = :EvnReanimatAction_pid
				  and EvnReanimatAction_disDT is null
			";
			$result = $callObject->db->query($query, $params);
			if (!is_object($result)) {
				return false;
			}
			$Response = $result->result("array");
			foreach ($Response as &$row) {
				$params = [
					"EvnReanimatAction_id" => $row["EvnReanimatAction_id"],
					"EvnReanimatAction_pid" => $arg["EvnReanimatPeriod_id"],
					"EvnReanimatAction_disDT" => $arg["EvnReanimatPeriod_disDate"],

                    "ReanimatActionType_id" => $row["ReanimatActionType_id"],
                    "UslugaComplex_id" => $row["UslugaComplex_id"],
                    "EvnUsluga_id" => $row["EvnUsluga_id"],
                    "ReanimDrugType_id" => $row["ReanimDrugType_id"],
                    "EvnReanimatAction_DrugDose" => $row["EvnReanimatAction_DrugDose"],
                    "EvnDrug_id" => $row["EvnDrug_id"],
                    "EvnReanimatAction_MethodCode" => $row["EvnReanimatAction_MethodCode"],
                    "EvnReanimatAction_ObservValue" => $row["EvnReanimatAction_ObservValue"],
                    "ReanimatCathetVeins_id" => $row["ReanimatCathetVeins_id"],
                    "CathetFixType_id" => $row["CathetFixType_id"],
                    "EvnReanimatAction_CathetNaborName" => $row["EvnReanimatAction_CathetNaborName"],
                    "NutritiousType_id" => $row["NutritiousType_id"],
                    "EvnReanimatAction_DrugUnit" => $row["EvnReanimatAction_DrugUnit"],						 
                    "EvnReanimatAction_MethodTxt" => $row["EvnReanimatAction_MethodTxt"],
                    "EvnReanimatAction_NutritVol" => $row["EvnReanimatAction_NutritVol"],  
                    "EvnReanimatAction_NutritEnerg" => $row["EvnReanimatAction_NutritEnerg"],

                    "EvnReanimatAction_setDT" => $row["EvnReanimatAction_setDT"],
					
                    
					"Lpu_id" => $arg["Lpu_id"],
					"Server_id" => $arg["Server_id"],
					"PersonEvn_id" => $arg["PersonEvn_id"],
					"pmUser_id" => $arg["pmUser_id"]
				];
				$query = "
					select
					    evnreanimataction_id as \"EvnReanimatAction_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Msg\"
					from p_evnreanimataction_upd(
					    evnreanimataction_id := :EvnReanimatAction_id,
					    evnreanimataction_pid := :EvnReanimatAction_pid,
                        evnreanimataction_disdt := :EvnReanimatAction_disDT,
					    lpu_id := :Lpu_id,
					    server_id := :Server_id,
					    personevn_id := :PersonEvn_id,
                        ReanimatActionType_id := :ReanimatActionType_id,
                        UslugaComplex_id := :UslugaComplex_id,
                        EvnUsluga_id := :EvnUsluga_id,
                        ReanimDrugType_id := :ReanimDrugType_id,
                        EvnReanimatAction_DrugDose := :EvnReanimatAction_DrugDose,
                        EvnDrug_id := :EvnDrug_id,
                        EvnReanimatAction_MethodCode := :EvnReanimatAction_MethodCode,
                        EvnReanimatAction_ObservValue := :EvnReanimatAction_ObservValue,
                        ReanimatCathetVeins_id := :ReanimatCathetVeins_id,
                        CathetFixType_id := :CathetFixType_id,
                        EvnReanimatAction_CathetNaborName := :EvnReanimatAction_CathetNaborName,
                        NutritiousType_id := :NutritiousType_id,
                        EvnReanimatAction_DrugUnit := :EvnReanimatAction_DrugUnit,						 
                        EvnReanimatAction_MethodTxt := :EvnReanimatAction_MethodTxt,
                        EvnReanimatAction_NutritVol := :EvnReanimatAction_NutritVol,  
                        EvnReanimatAction_NutritEnerg := :EvnReanimatAction_NutritEnerg,
                        
                        EvnReanimatAction_setDT := :EvnReanimatAction_setDT, 
					    
					    pmuser_id := :pmUser_id
					);
				";
				$result = $callObject->db->query($query, $params);
				if (!is_object($result)) {
					return false;
				}
			}
            // снятие помеоки "в РП сейчас"
            $arg['ReanimatRegister_IsPeriodNow'] = 1;
//			$callObject->load->model("ReanimatRegister_model", "ReanimatRegister_model");
//			$Response = $callObject->ReanimatRegister_model->ReanimatRegisterEndRP($arg);
		}

        //BOB - 08.10.2019 если сохраняемая запись последняя, только тогда лезем корёжить регистр
        if ($LastRP) {
            $callObject->load->model('ReanimatRegister_model', 'ReanimatRegister_model');			//BOB - 08.10.2019
            $Response = $callObject->ReanimatRegister_model->ReanimatRegisterEndRP($arg);
        }
		return $Response;
	}
	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function EvnScale_Save(EvnReanimatPeriod_model $callObject, $data)
	{
		$Response = ["success" => "true", "Error_Msg" => ""];
		$data["EvnScale_setDate"] .= " " . $data["EvnScale_setTime"] . ":00";
		$params = [
			"EvnScale_pid" => $data["EvnScale_pid"],
			"EvnScale_rid" => $data["EvnScale_rid"],
			"EvnScale_setDT" => isset($data["EvnScale_setDate"]) ? $data["EvnScale_setDate"] : null,
			"Lpu_id" => isset($data["Lpu_id"]) ? $data["Lpu_id"] : null,
			"Person_id" => isset($data["Person_id"]) ? $data["Person_id"] : null,
			"PersonEvn_id" => isset($data["PersonEvn_id"]) ? $data["PersonEvn_id"] : null,
			"Server_id" => isset($data["Server_id"]) ? $data["Server_id"] : null,
			"ScaleType_id" => isset($data["ScaleType_id"]) ? $data["ScaleType_id"] : null,
			"EvnScale_Result" => isset($data["EvnScale_Result"]) ? $data["EvnScale_Result"] : null,
			"EvnScale_ResultTradic" => isset($data["EvnScale_ResultTradic"]) ? $data["EvnScale_ResultTradic"] : null,
			"pmUser_id" => isset($data["pmUser_id"]) ? $data["pmUser_id"] : null,
			"ScaleParameter" => isset($data["ScaleParameter"]) ? json_decode($data["ScaleParameter"], true) : null
		];
		$query = "
			select
			    evnscale_id as \"EvnScale_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_evnscale_ins(
			    evnscale_pid := :EvnScale_pid,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnscale_setdt := :EvnScale_setDT,
			    scaletype_id := :ScaleType_id,
			    evnscale_result := :EvnScale_Result,
			    evnscale_resulttradic := :EvnScale_ResultTradic,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$EvnScaleResult = $result->result("array");
		if (($EvnScaleResult[0]["EvnScale_id"]) && ($EvnScaleResult[0]["Error_Code"] == null) && ($EvnScaleResult[0]["Error_Message"] == null)) {
			//если SOFA
			if ($params["ScaleType_id"] == 5) {
				$query = "
					update EvnSection set
					EvnSection_SofaScalePoints = :EvnScale_Result
					where evn_id = (select Evn_pid from Evn where Evn_id = :EvnScale_pid); 
					update Evn set
					Evn_updDT = getdate()
					where Evn_id = (select Evn_pid from Evn where Evn_id = :EvnScale_pid) 
				";
				$result = $callObject->db->query($query, $params);
			}
			$params["EvnScale_id"] = $EvnScaleResult[0]["EvnScale_id"];
			//если ВАШ
			if ($params["ScaleType_id"] == 19) {
				$query = "
					select
						ScaleParameterType_id as \"ScaleParameterType_id\",
					    ScaleParameterResult_id as \"ScaleParameterResult_id\"
					from v_Scale S
					where S.ScaleType_id = :ScaleType_id
					  and S.ScaleParameterResult_Value = :EvnScale_Result
				";
				$result = $callObject->db->query($query, $params);
				if (!is_object($result)) {
					return false;
				}
				$EvnScaleResult = $result->result("array");
				$params["ScaleParameter"][0]["ScaleParameterType_id"] = $EvnScaleResult[0]["ScaleParameterType_id"];
				$params["ScaleParameter"][0]["ScaleParameterResult_id"] = $EvnScaleResult[0]["ScaleParameterResult_id"];
			}
			foreach ($params["ScaleParameter"] as $ScaleParameter) {
				$params["ScaleParameterType_id"] = $ScaleParameter["ScaleParameterType_id"];
				$params["ScaleParameterResult_id"] = $ScaleParameter["ScaleParameterResult_id"];

				$query = "
					select
					    scaleparameter_id as \"ScaleParameter_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					from p_scaleparameter_ins(
					    evnscale_id := :EvnScale_id,
					    scaleparametertype_id := :ScaleParameterType_id,
					    scaleparameterresult_id := :ScaleParameterResult_id,
					    pmuser_id := :pmUser_id
					);
				";
				$result = $callObject->db->query($query, $params);
				$ScaleParameterResult = $result->result("array");
				if (!empty($ScaleParameterResult[0]["Error_Code"]) || !empty($ScaleParameterResult[0]["Error_Message"])) {
					$Response["success"] = "false";
					$Response["Error_Msg"] = trim($ScaleParameterResult[0]["Error_Code"] . " " . $ScaleParameterResult[0]["Error_Message"]);
					break;
				}
			}
		} else {
			$Response['success'] = 'false';
			$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'] . ' ' . $EvnScaleResult[0]['Error_Message'];
		}
		if (!is_object($result)) {
		    return false;
        }
		
        return $Response;
    }

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function EvnScales_Del(EvnReanimatPeriod_model $callObject, $data)
	{
		$Response = ["success" => "true", "Error_Msg" => ""];
		$pmUser_id = $callObject->sessionParams["pmuser_id"];
		$queryParams = [
			"EvnScale_id" => $data["EvnScale_id"],
			"pmUser_id" => $pmUser_id
		];
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_evnscale_del(
			    evnscale_id := :EvnScale_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$EvnScaleResult = $result->result("array");
		if (!empty($EvnScaleResult[0]["Error_Code"]) || !empty($EvnScaleResult[0]["Error_Message"])) {
			$Response["success"] = "false";
			$Response["Error_Msg"] = trim($EvnScaleResult[0]["Error_Code"] . " " . $EvnScaleResult[0]["Error_Message"]);
		}
		return $Response;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function EvnReanimatAction_Del(EvnReanimatPeriod_model $callObject, $data)
	{
		$Response = ["success" => "true", "Error_Msg" => ""];
		$pmUser_id = $callObject->sessionParams["pmuser_id"];
		$queryParams = [
			"EvnReanimatAction_id" => $data["EvnReanimatAction_id"],
			"pmUser_id" => $pmUser_id
		];
		$query = "		
			select
				EvnUsluga_id as \"EvnUsluga_id\",
			    EvnDrug_id as \"EvnDrug_id\"
			from v_EvnReanimatAction
			where EvnReanimatAction_id = :EvnReanimatAction_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$EvnScaleResult = $result->result("array");
		//если не найдена запись мероприятия
		if (count($EvnScaleResult) == 0) {
			throw new Exception("Запись реанимационного мероприятия не найдена");
		}
		//если код услуги непустой, то - удаление услуги
		if ($EvnScaleResult[0]["EvnUsluga_id"] != null) {
			$queryParams["EvnUsluga_id"] = $EvnScaleResult[0]["EvnUsluga_id"];
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_evnusluga_del(
				    evnusluga_id := :EvnUsluga_id,
				    pmuser_id := :pmUser_id
				);
			";
			$callObject->db->query($query, $queryParams);
		}
		//если код медикамента непустой, то - удаление медикамента
		if ($EvnScaleResult[0]["EvnDrug_id"] != null) {
			$queryParams["EvnDrug_id"] = $EvnScaleResult[0]["EvnDrug_id"];
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_evndrug_del(
				    evndrug_id := :EvnDrug_id,
				    pmuser_id := :pmUser_id
				);
			";
			$callObject->db->query($query, $queryParams);
		}
		//удаление параметров ИВЛ
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_ivlparameter_del(
			    ivlparameter_id := (
			    	select IVLParameter_id
			    	from IVLParameter
			    	where EvnReanimatAction_id = :EvnReanimatAction_id
			    	limit 1
			    ),
			    pmuser_id := :pmUser_id,
			    isremove := 1
			);		
		";
		$callObject->db->query($query, $queryParams);
		//удаление параметров сердечно-лёгочная реанимация
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_reanimatcardpulm_del(
			    reanimatcardpulm_id := (
			    	select ReanimatCardPulm_id
			    	from ReanimatCardPulm
			    	where EvnReanimatAction_id = :EvnReanimatAction_id
			    	limit 1
			    ),
			    pmuser_id := :pmUser_id,
			    isremove := 1
			);
		";
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result->result("array");
		//удаление измерений
		$query = "
			select RateVCHD_id as \"RateVCHD_id\"
			from v_RateVCHD
			where EvnReanimatAction_id = :EvnReanimatAction_id
		";
		$queryRows = $callObject->queryResult($query, $queryParams);
		foreach ($queryRows as $queryRow) {
			$query = "
				select * from p_ratevchd_del(
				    ratevchd_id := {$queryRow["RateVCHD_id"]},
				    pmuser_id := {$pmUser_id},
				    isremove := 1
				);
			";
			$callObject->db->query($query);
		}
		$query = "
			select RateSPO2_id as \"RateSPO2_id\"
			from v_RateSPO2
			where EvnReanimatAction_id = :EvnReanimatAction_id
		";
		$queryRows = $callObject->queryResult($query, $queryParams);
		foreach ($queryRows as $queryRow) {
			$query = "
				select * from p_ratespo2_del(
				    ratespo2_id := {$queryRow["RateSPO2_id"]},
				    pmuser_id := {$pmUser_id},
				    isremove := 1
				);
			";
			$callObject->db->query($query);
		}
		$query = "
			select RateHemodynam_id as \"RateHemodynam_id\"
			from v_RateHemodynam
			where EvnReanimatAction_id = :EvnReanimatAction_id
		";
		$queryRows = $callObject->queryResult($query, $queryParams);
		foreach ($queryRows as $queryRow) {
			$query = "
				select * from p_ratehemodynam_del(
				    ratehemodynam_id := {$queryRow["RateHemodynam_id"]},
				    pmuser_id := {$pmUser_id},
				    isremove := 1
				);
			";
			$callObject->db->query($query);
		}
		//удаление мероприятия
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_evnreanimataction_del(
			    evnreanimataction_id := :EvnReanimatAction_id,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$EvnScaleResult = $result->result("array");
		if (!empty($EvnScaleResult[0]["Error_Code"]) || !empty($EvnScaleResult[0]["Error_Message"])) {
			$Response["success"] = "false";
			$Response["Error_Msg"] = trim($EvnScaleResult[0]["Error_Code"] . " " . $EvnScaleResult[0]["Error_Message"]);
		}
		return $Response;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function EvnReanimatAction_Save_(EvnReanimatPeriod_model $callObject, $data)
	{
		$Response = ["success" => "true", "Error_Msg" => ""];
		$EvnUslugaCommonResult = null;
		$DrugNameResult = null;
		$EvnReanimatAction_id_Rate = null;
		$data["EvnReanimatAction_setDate"] .= " " . $data["EvnReanimatAction_setTime"] . ":00";
		$data["EvnReanimatAction_disDate"] = (($data["EvnReanimatAction_disDate"] != "") && $data["EvnReanimatAction_disTime"] != "") ? $data["EvnReanimatAction_disDate"] . " " . $data["EvnReanimatAction_disTime"] . ":00" : null;
		$params = [
			"EvnReanimatAction_id" => $data["EvnReanimatAction_id"],
			"ReanimatActionType_SysNick" => $data["ReanimatActionType_SysNick"],
			"EvnReanimatAction_pid" => isset($data["EvnReanimatAction_pid"]) ? $data["EvnReanimatAction_pid"] : null,
			"EvnReanimatAction_setDT" => isset($data["EvnReanimatAction_setDate"]) ? $data["EvnReanimatAction_setDate"] : null,
			"EvnReanimatAction_disDT" => isset($data["EvnReanimatAction_disDate"]) ? $data["EvnReanimatAction_disDate"] : null,
			"Lpu_id" => isset($data["Lpu_id"]) ? $data["Lpu_id"] : null,
			"Server_id" => isset($data["Server_id"]) ? $data["Server_id"] : null,
			"PersonEvn_id" => isset($data["PersonEvn_id"]) ? $data["PersonEvn_id"] : null,
			"pmUser_id" => isset($data["pmUser_id"]) ? $data["pmUser_id"] : null,
			"ReanimatActionType_id" => isset($data["ReanimatActionType_id"]) ? $data["ReanimatActionType_id"] : null,
			"UslugaComplex_id" => $data["ReanimatActionType_id"] != 3 ? (isset($data["UslugaComplex_id"]) ? $data["UslugaComplex_id"] : null) : null,
			"NutritiousType_id" => $data["ReanimatActionType_id"] == 3 ? (isset($data["UslugaComplex_id"]) ? $data["UslugaComplex_id"] : null) : null,
			"ReanimDrugType_id" => isset($data["ReanimDrugType_id"]) ? $data["ReanimDrugType_id"] : null,
			"EvnReanimatAction_DrugDose" => isset($data["EvnReanimatAction_DrugDose"]) ? $data["EvnReanimatAction_DrugDose"] : null,
			"EvnReanimatAction_MethodCode" => isset($data["EvnReanimatAction_MethodCode"]) ? $data["EvnReanimatAction_MethodCode"] : null,
			"EvnReanimatAction_ObservValue" => isset($data["EvnReanimatAction_ObservValue"]) ? $data["EvnReanimatAction_ObservValue"] : null,
			"ReanimatCathetVeins_id" => isset($data["ReanimatCathetVeins_id"]) ? $data["ReanimatCathetVeins_id"] : null,
			"CathetFixType_id" => isset($data["CathetFixType_id"]) ? $data["CathetFixType_id"] : null,
			"EvnReanimatAction_CathetNaborName" => isset($data["EvnReanimatAction_CathetNaborName"]) ? $data["EvnReanimatAction_CathetNaborName"] : null,
			"EvnReanimatAction_DrugUnit" => isset($data["EvnReanimatAction_DrugUnit"]) ? $data["EvnReanimatAction_DrugUnit"] : null,
			"LpuSection_id" => isset($data["LpuSection_id"]) ? $data["LpuSection_id"] : null,
			"IVLParameter" => isset($data["IVLParameter"]) ? json_decode($data["IVLParameter"], true) : null,
			"CardPulm" => isset($data["CardPulm"]) ? json_decode($data["CardPulm"], true) : null,
			"Rate_List" => isset($data["Rate_List"]) ? json_decode($data["Rate_List"], true) : null,
			"EvnReanimatAction_MethodTxt" => isset($data["EvnReanimatAction_MethodTxt"]) ? $data["EvnReanimatAction_MethodTxt"] : null,
			"EvnReanimatAction_NutritVol" => isset($data["EvnReanimatAction_NutritVol"]) ? $data["EvnReanimatAction_NutritVol"] : null,
			"EvnReanimatAction_NutritEnerg" => isset($data["EvnReanimatAction_NutritEnerg"]) ? $data["EvnReanimatAction_NutritEnerg"] : null
		];
		if ($params["EvnReanimatAction_id"] == "New_GUID_Id") {
			// если типы мероприятий, содержащие услуги
			if (in_array($params["ReanimatActionType_SysNick"], ["lung_ventilation", "hemodialysis", "endocranial_sensor", "epidural_analgesia", "catheterization_veins"])) {
				$params["MedPersonal_id"] = isset($data["MedPersonal_id"]) ? $data["MedPersonal_id"] : null;
				$params["MedStaffFact_id"] = isset($data["MedStaffFact_id"]) ? $data["MedStaffFact_id"] : null;
				$params["PayType_id"] = isset($data["PayType_id"]) ? $data["PayType_id"] : null;
				$params["Diag_id"] = isset($data["Diag_id"]) ? $data["Diag_id"] : null;
				$query = "
					select
					    evnuslugacommon_id as \"EvnUslugaCommon_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					from p_evnuslugacommon_ins(
					    evnuslugacommon_pid := :EvnReanimatAction_pid,
					    lpu_id := :Lpu_id,
					    server_id := :Server_id,
					    personevn_id := :PersonEvn_id,
					    evnuslugacommon_setdt := :EvnReanimatAction_setDT,
					    evnuslugacommon_disdt := :EvnReanimatAction_disDT,
					    paytype_id := :PayType_id,
					    medpersonal_id := :MedPersonal_id,
					    uslugaplace_id := 1,
					    lpusection_uid := :LpuSection_id,
					    evnuslugacommon_kolvo := 1,
					    uslugacomplex_id := :UslugaComplex_id,
					    uslugacomplextariff_id := (
							select uct.UslugaComplexTariff_id 
							from dbo.UslugaComplexTariff UCT
							where UCT.UslugaComplex_id = :UslugaComplex_id
							  and UCT.UslugaComplexTariff_begDate <= :EvnReanimatAction_setDT
							  and (UCT.UslugaComplexTariff_endDate >=  :EvnReanimatAction_setDT or UCT.UslugaComplexTariff_endDate is null)
					    ),
					    medstafffact_id := :MedStaffFact_id,
					    diagsetclass_id := 1,
					    diag_id := :Diag_id,
					    pmuser_id := :pmUser_id
					);
				";
				/**@var CI_DB_result $result */
				$result = $callObject->db->query($query, $params);
				if (!is_object($result)) {
					return false;
				}
				$EvnUslugaCommonResult = $result->result("array");
				if (empty($EvnUslugaCommonResult[0]["EvnUslugaCommon_id"]) || !empty($EvnUslugaCommonResult[0]["Error_Code"]) || !empty($EvnUslugaCommonResult[0]["Error_Message"])) {
					$Response["success"] = "false";
					$Response["Error_Msg"] = trim($EvnUslugaCommonResult[0]["Error_Code"] . " " . $EvnUslugaCommonResult[0]["Error_Message"]);
					return $Response;
				}
			}
			$EvnUsluga_id = ($EvnUslugaCommonResult != null) ? $EvnUslugaCommonResult[0]["EvnUslugaCommon_id"] : null;
			// если типы мероприятий, содержащие медикамент
			if ((in_array($params["ReanimatActionType_SysNick"], ["vazopressors", "epidural_analgesia", "antifungal_therapy", "catheterization_veins", "invasive_hemodynamics"])) && ($params["ReanimDrugType_id"] != 12)) {
				//определяю наименование по коду
				$query = "		
					select ReanimDrugType_name as \"ReanimDrugType_name\"
					from ReanimDrugType
					where ReanimDrugType_id = :ReanimDrugType_id
				";
				$result = $callObject->db->query($query, $params);
				if (!is_object($result)) {
					return false;
				}
				$DrugNameResult = $result->result("array");
				//выбор Drug_id для выбранного медикамента минимального, т.к. без разницы какой, лишь бы было что сохранить в EvnDrug
				$BaseWhere = "";
				switch ($DrugNameResult[0]["ReanimDrugType_name"]) {
					case "Адреналин":
					case "Мезатон":
					case "Норадреналин":
					case "Новокаин":
					case "Лидокаин":
					case "Дофамин":
						$BaseWhere = " and D.DrugTorg_Name = '" . $DrugNameResult[0]['ReanimDrugType_name'] . "'";
						break;
					case "Добутамин":
						$BaseWhere = " and D.DrugTorg_Name ilike '%" . $DrugNameResult[0]['ReanimDrugType_name'] . "%'";
						break;
					case "Ропивакаин":
					case "Каспофунгин":
					case "Микафунгин":
					case "Анидулафунгин":
						$BaseWhere = "
							and D.DrugComplexMnn_id in (
								select DrugComplexMnn_id
								from rls.DrugComplexMnn DCM
								where DCM.DrugComplexMnn_RusName ilike '%" . $DrugNameResult[0]['ReanimDrugType_name'] . "%'
							)
						";
						break;
				}
				$query = "
					select min(Drug_id) as \"Drug_id\"
					from rls.Drug D
					inner join rls.DrugPrep DP on DP.DrugPrepFas_id = D.DrugPrepFas_id
					where (1=1) {$BaseWhere}  
					  and D.Drug_begDate <=  getdate()
					  and (D.Drug_endDate >= getdate() or  D.Drug_endDate is null)
					  and not (D.Drug_Dose is null and Drug_Volume is null and D.Drug_Mass is null and Drug_Fas is null and DP.Drug_Size is null)
				";
				$result = $callObject->db->query($query);
				if (!is_object($result)) {
					return false;
				}
				$DrugNameResult = $result->result("array");
				$params["Drug_id"] = isset($DrugNameResult[0]["Drug_id"]) ? $DrugNameResult[0]["Drug_id"] : null;
				$query = "
					select
					    evndrug_id as \"EvnDrug_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					from p_evndrug_ins(
					    evndrug_pid := :EvnReanimatAction_pid,
					    lpu_id := :Lpu_id,
					    server_id := :Server_id,
					    personevn_id := :PersonEvn_id,
					    evndrug_setdt := :EvnReanimatAction_setDT,
					    drug_id := :Drug_id,
					    lpusection_id := :LpuSection_id,
					    pmuser_id := :pmUser_id
					);
				";
				$result = $callObject->db->query($query, $params);
				if (!is_object($result)) {
					return false;
				}
				$DrugNameResult = $result->result("array");
				if (empty($DrugNameResult[0]["EvnDrug_id"]) || !empty($DrugNameResult[0]["Error_Code"]) || !empty($DrugNameResult[0]["Error_Message"])) {
					$Response["success"] = "false";
					$Response["Error_Msg"] = trim($DrugNameResult[0]["Error_Code"] . " " . $DrugNameResult[0]["Error_Message"]);
					return $Response;
				}
			}
			$EvnDrug_id = ($DrugNameResult != null) ? $DrugNameResult[0]["EvnDrug_id"] : null;
			$params["EvnUsluga_id"] = isset($EvnUsluga_id) ? $EvnUsluga_id : null;
			$params["EvnDrug_id"] = isset($EvnDrug_id) ? $EvnDrug_id : null;
			$query = "
				select
				    evnreanimataction_id as \"EvnReanimatAction_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_evnreanimataction_ins(
				    evnreanimataction_pid := :EvnReanimatAction_pid,
				    lpu_id := :Lpu_id,
				    server_id := :Server_id,
				    personevn_id := :PersonEvn_id,
				    evnreanimataction_setdt := :EvnReanimatAction_setDT,
				    evnreanimataction_disdt := :EvnReanimatAction_disDT,
				    evnreanimataction_diddt := null,
				    morbus_id := 0,
				    evnreanimataction_issigned := 0,
				    pmuser_signid := 0,
				    evnreanimataction_signdt := null,
				    evnstatus_id := 0,
				    evnreanimataction_statusdate := null,
				    evnreanimataction_istransit := 0,
				    reanimatactiontype_id := :ReanimatActionType_id,
				    uslugacomplex_id := :UslugaComplex_id,
				    evnusluga_id := :EvnUsluga_id,
				    reanimdrugtype_id := :ReanimDrugType_id,
				    evndrug_id := :EvnDrug_id,
				    evnreanimataction_methodcode := :EvnReanimatAction_MethodCode,
				    evnreanimataction_drugdose := :EvnReanimatAction_DrugDose,
				    evnreanimataction_observvalue := :EvnReanimatAction_ObservValue,
				    reanimatcathetveins_id := :ReanimatCathetVeins_id,
				    cathetfixtype_id := :CathetFixType_id,
				    evnreanimataction_cathetnaborname := :EvnReanimatAction_CathetNaborName,
				    nutritioustype_id := :NutritiousType_id,
				    evnreanimataction_drugunit := :EvnReanimatAction_DrugUnit,
				    evnreanimataction_methodtxt := :EvnReanimatAction_MethodTxt,
				    evnreanimataction_nutritvol := :EvnReanimatAction_NutritVol,
				    evnreanimataction_nutritenerg := :EvnReanimatAction_NutritEnerg,
				    pmuser_id := :pmUser_id
				);
			";
			$result = $callObject->db->query($query, $params);
			if (!is_object($result)) {
				return false;
			}
			$EvnScaleResult = $result->result("array");
			if (($EvnScaleResult[0]["EvnReanimatAction_id"]) && ($EvnScaleResult[0]["Error_Code"] == null) && ($EvnScaleResult[0]["Error_Message"] == null)) {
				$EvnReanimatAction_id_Rate = $EvnScaleResult[0]["EvnReanimatAction_id"];
				//если нутритивная поддержка - закрываем предыдущую нутритивную поддержку
				if (in_array($params["ReanimatActionType_SysNick"], array("nutrition"))) {
					$params["EvnReanimatAction_id_Prev"] = $EvnScaleResult[0]["EvnReanimatAction_id"];
					$query = "
						select
							ERA.EvnReanimatAction_id as \"EvnReanimatAction_id\",
						    ERA.EvnReanimatAction_setDT as \"EvnReanimatAction_setDT\",
						    ERA.EvnReanimatAction_disDT as \"ReanimatNutritionType_Name\"
						from  v_EvnReanimatAction ERA
						where EvnReanimatAction_pid = :EvnReanimatAction_pid
						  and EvnReanimatAction_id <> :EvnReanimatAction_id_Prev
						  and ReanimatActionType_SysNick = :ReanimatActionType_SysNick
						  and ERA.EvnReanimatAction_setDT <= :EvnReanimatAction_setDT
						  and ERA.EvnReanimatAction_disDT is null
						 order by EvnReanimatAction_setDT desc	
					";
					$result = $callObject->db->query($query, $params);
					if (!is_object($result)) {
						return false;
					}
					$EvnScaleResult = $result->result("array");
					if (sizeof($EvnScaleResult) > 0) {
						$params["EvnReanimatAction_id_Prev"] = $EvnScaleResult[0]["EvnReanimatAction_id"];
						$query = "
							select
							    evnreanimataction_id as \"EvnReanimatAction_id\",
							    error_code as \"Error_Code\",
							    error_message as \"Error_Msg\"
							from p_evnreanimataction_upd(
							    evnreanimataction_id := :EvnReanimatAction_id_Prev,
							    evnreanimataction_pid := :EvnReanimatAction_pid,
							    lpu_id := :Lpu_id,
							    server_id := :Server_id,
							    personevn_id := :PersonEvn_id,
							    evnreanimataction_disdt := :EvnReanimatAction_setDT,
							    pmuser_id := :pmUser_id
							);
						";
						$result = $callObject->db->query($query, $params);
						if (!is_object($result)) {
							return false;
						}
						$EvnScaleResult = $result->result("array");
					}
				}
				//если ИВЛ - сохраняю параметры
				if (in_array($params["ReanimatActionType_SysNick"], array("lung_ventilation"))) {
					if (isset($params["IVLParameter"])) {
						$params["EvnReanimatAction_id"] = $EvnScaleResult[0]["EvnReanimatAction_id"];
						$params["IVLParameter_Apparat"] = $params["IVLParameter"]["IVLParameter_Apparat"];
						$params["IVLRegim_id"] = $params["IVLParameter"]["IVLRegim_id"];
						$params["IVLParameter_TubeDiam"] = $params["IVLParameter"]["IVLParameter_TubeDiam"];
						$params["IVLParameter_FiO2"] = $params["IVLParameter"]["IVLParameter_FiO2"];
						$params["IVLParameter_PcentMinVol"] = $params["IVLParameter"]["IVLParameter_PcentMinVol"];
						$params["IVLParameter_TwoASVMax"] = $params["IVLParameter"]["IVLParameter_TwoASVMax"];
						$params["IVLParameter_FrequSet"] = $params["IVLParameter"]["IVLParameter_FrequSet"];
						$params["IVLParameter_VolInsp"] = $params["IVLParameter"]["IVLParameter_VolInsp"];
						$params["IVLParameter_PressInsp"] = $params["IVLParameter"]["IVLParameter_PressInsp"];
						$params["IVLParameter_PressSupp"] = $params["IVLParameter"]["IVLParameter_PressSupp"];
						$params["IVLParameter_FrequTotal"] = $params["IVLParameter"]["IVLParameter_FrequTotal"];
						$params["IVLParameter_VolTe"] = $params["IVLParameter"]["IVLParameter_VolTe"];
						$params["IVLParameter_VolE"] = $params["IVLParameter"]["IVLParameter_VolE"];
						$params["IVLParameter_TinTet"] = $params["IVLParameter"]["IVLParameter_TinTet"];
						$params["IVLParameter_VolTrig"] = $params["IVLParameter"]["IVLParameter_VolTrig"];
						$params["IVLParameter_PressTrig"] = $params["IVLParameter"]["IVLParameter_PressTrig"];
						$params["IVLParameter_PEEP"] = $params["IVLParameter"]["IVLParameter_PEEP"];
						$query = "
							select
							    ivlparameter_id as \"EvnReanimatAction_id\",
							    error_code as \"Error_Code\",
							    error_message as \"Error_Msg\"
							from p_ivlparameter_ins(
							    evnreanimataction_id := :EvnReanimatAction_id,
							    ivlparameter_apparat := :IVLParameter_Apparat,
							    ivlregim_id := :IVLRegim_id,
							    ivlparameter_tubediam := :IVLParameter_TubeDiam,
							    ivlparameter_fio2 := :IVLParameter_FiO2,
							    ivlparameter_frequset := :IVLParameter_FrequSet,
							    ivlparameter_volinsp := :IVLParameter_VolInsp,
							    ivlparameter_pressinsp := :IVLParameter_PressInsp,
							    ivlparameter_presssupp := :IVLParameter_PressSupp,
							    ivlparameter_frequtotal := :IVLParameter_FrequTotal,
							    ivlparameter_volte := :IVLParameter_VolTe,
							    ivlparameter_vole := :IVLParameter_VolE,
							    ivlparameter_tintet := :IVLParameter_TinTet,
							    ivlparameter_voltrig := :IVLParameter_VolTrig,
							    ivlparameter_presstrig := :IVLParameter_PressTrig,
							    ivlparameter_peep := :IVLParameter_PEEP,
							    ivlparameter_pcentminvol := :IVLParameter_PcentMinVol,
							    ivlparameter_twoasvmax := :IVLParameter_TwoASVMax,
							    pmuser_id := :pmUser_id
							);
						";
						$result = $callObject->db->query($query, $params);
						if (!is_object($result)) {
							return false;
						}
						$EvnScaleResult = $result->result("array");
					}
				}
				//если сердечно-лёгочная реанимация - сохраняю параметры
				if (in_array($params["ReanimatActionType_SysNick"], array("card_pulm"))) {
					if (isset($params["CardPulm"])) {
						$params["EvnReanimatAction_id"] = $EvnScaleResult[0]["EvnReanimatAction_id"];
						$params["ReanimatCardPulm_ClinicalDeath"] = substr($params["CardPulm"]["ReanimatCardPulm_ClinicalDeathDate"], 0, 10) . " " . $params["CardPulm"]["ReanimatCardPulm_ClinicalDeathTime"] . ":00";
						if (!isset($params["CardPulm"]["ReanimatCardPulm_BiologDeathDate"]) || !isset($params["CardPulm"]["ReanimatCardPulm_BiologDeathTime"])) {
							$params["ReanimatCardPulm_BiologDeath"] = null;
						} else {
							$params["ReanimatCardPulm_BiologDeath"] = substr($params["CardPulm"]["ReanimatCardPulm_BiologDeathDate"], 0, 10) . " " . $params["CardPulm"]["ReanimatCardPulm_BiologDeathTime"] . ":00";
						}
						$params["ReanimatCardPulm_IsPupilDilat"] = $params["CardPulm"]["ReanimatCardPulm_IsPupilDilat"];
						$params["ReanimatCardPulm_IsCardMonitor"] = $params["CardPulm"]["ReanimatCardPulm_IsCardMonitor"];
						$params["ReanimatCardPulm_StopCardActType"] = $params["CardPulm"]["ReanimatCardPulm_StopCardActType"];
						$params["IVLRegim_id"] = isset($params["CardPulm"]["IVLRegim_id"]) ? $params["CardPulm"]["IVLRegim_id"] : null;
						$params["ReanimatCardPulm_FiO2"] = isset($params["CardPulm"]["ReanimatCardPulm_FiO2"]) ? $params["CardPulm"]["ReanimatCardPulm_FiO2"] : null;
						$params["ReanimatCardPulm_IsCardTonics"] = $params["CardPulm"]["ReanimatCardPulm_IsCardTonics"];
						$params["ReanimatCardPulm_CardTonicDose"] = isset($params["CardPulm"]["ReanimatCardPulm_CardTonicDose"]) ? $params["CardPulm"]["ReanimatCardPulm_CardTonicDose"] : null;
						$params["ReanimatCardPulm_CathetVein"] = $params["CardPulm"]["ReanimatCardPulm_CathetVein"];
						$params["ReanimatCardPulm_TrachIntub"] = isset($params["CardPulm"]["ReanimatCardPulm_TrachIntub"]) ? $params["CardPulm"]["ReanimatCardPulm_TrachIntub"] : null;
						$params["ReanimatCardPulm_Auscultatory"] = $params["CardPulm"]["ReanimatCardPulm_Auscultatory"];
						$params["ReanimatCardPulm_AuscultatoryTxt"] = isset($params["CardPulm"]["ReanimatCardPulm_AuscultatoryTxt"]) ? $params["CardPulm"]["ReanimatCardPulm_AuscultatoryTxt"] : null;
						$params["ReanimatCardPulm_CardMassage"] = isset($params["CardPulm"]["ReanimatCardPulm_CardMassage"]) ? $params["CardPulm"]["ReanimatCardPulm_CardMassage"] : null;
						$params["ReanimatCardPulm_DefibrilCount"] = isset($params["CardPulm"]["ReanimatCardPulm_DefibrilCount"]) ? $params["CardPulm"]["ReanimatCardPulm_DefibrilCount"] : null;
						$params["ReanimatCardPulm_DefibrilMin"] = isset($params["CardPulm"]["ReanimatCardPulm_DefibrilMin"]) ? $params["CardPulm"]["ReanimatCardPulm_DefibrilMin"] : null;
						$params["ReanimatCardPulm_DefibrilMax"] = isset($params["CardPulm"]["ReanimatCardPulm_DefibrilMax"]) ? $params["CardPulm"]["ReanimatCardPulm_DefibrilMax"] : null;
						$params["ReanimDrugType_id"] = isset($params["CardPulm"]["ReanimDrugType_id"]) ? $params["CardPulm"]["ReanimDrugType_id"] : null;
						$params["ReanimatCardPulm_DrugDose"] = isset($params["CardPulm"]["ReanimatCardPulm_DrugDose"]) ? $params["CardPulm"]["ReanimatCardPulm_DrugDose"] : null;
						$params["ReanimatCardPulm_DrugSposob"] = isset($params["CardPulm"]["ReanimatCardPulm_DrugSposob"]) ? $params["CardPulm"]["ReanimatCardPulm_DrugSposob"] : null;
						$params["ReanimDrugType_did"] = isset($params["CardPulm"]["ReanimDrugType_did"]) ? $params["CardPulm"]["ReanimDrugType_did"] : null;
						$params["ReanimatCardPulm_dDrugDose"] = isset($params["CardPulm"]["ReanimatCardPulm_dDrugDose"]) ? $params["CardPulm"]["ReanimatCardPulm_dDrugDose"] : null;
						$params["ReanimatCardPulm_dDrugSposob"] = isset($params["CardPulm"]["ReanimatCardPulm_dDrugSposob"]) ? $params["CardPulm"]["ReanimatCardPulm_dDrugSposob"] : null;
						$params["ReanimatCardPulm_DrugTxt"] = isset($params["CardPulm"]["ReanimatCardPulm_DrugTxt"]) ? $params["CardPulm"]["ReanimatCardPulm_DrugTxt"] : null;
						$params["ReanimatCardPulm_IsEffective"] = $params["CardPulm"]["ReanimatCardPulm_IsEffective"];
						$params["ReanimatCardPulm_Time"] = $params["CardPulm"]["ReanimatCardPulm_Time"];
						$params["ReanimatCardPulm_DoctorTxt"] = $params["CardPulm"]["ReanimatCardPulm_DoctorTxt"];
						$query = "
							select
							    reanimatcardpulm_id as \"ReanimatCardPulm_id\",
							    error_code as \"Error_Code\",
							    error_message as \"Error_Msg\"
							from p_reanimatcardpulm_ins(
							    evnreanimataction_id := :EvnReanimatAction_id,
							    reanimatcardpulm_clinicaldeath := :ReanimatCardPulm_ClinicalDeath,
							    reanimatcardpulm_ispupildilat := :ReanimatCardPulm_IsPupilDilat,
							    reanimatcardpulm_iscardmonitor := :ReanimatCardPulm_IsCardMonitor,
							    reanimatcardpulm_stopcardacttype := :ReanimatCardPulm_StopCardActType,
							    ivlregim_id := :IVLRegim_id,
							    reanimatcardpulm_fio2 := :ReanimatCardPulm_FiO2,
							    reanimatcardpulm_iscardtonics := :ReanimatCardPulm_IsCardTonics,
							    reanimatcardpulm_cardtonicdose := :ReanimatCardPulm_CardTonicDose,
							    reanimatcardpulm_cathetvein := :ReanimatCardPulm_CathetVein,
							    reanimatcardpulm_trachintub := :ReanimatCardPulm_TrachIntub,
							    reanimatcardpulm_auscultatory := :ReanimatCardPulm_Auscultatory,
							    reanimatcardpulm_auscultatorytxt := :ReanimatCardPulm_AuscultatoryTxt,
							    reanimatcardpulm_cardmassage := :ReanimatCardPulm_CardMassage,
							    reanimatcardpulm_defibrilcount := :ReanimatCardPulm_DefibrilCount,
							    reanimatcardpulm_defibrilmin := :ReanimatCardPulm_DefibrilMin,
							    reanimatcardpulm_defibrilmax := :ReanimatCardPulm_DefibrilMax,
							    reanimdrugtype_id := :ReanimDrugType_id,
							    reanimatcardpulm_drugdose := :ReanimatCardPulm_DrugDose,
							    reanimatcardpulm_drugsposob := :ReanimatCardPulm_DrugSposob,
							    reanimdrugtype_did := :ReanimDrugType_did,
							    reanimatcardpulm_ddrugdose := :ReanimatCardPulm_dDrugDose,
							    reanimatcardpulm_ddrugsposob := :ReanimatCardPulm_dDrugSposob,
							    reanimatcardpulm_drugtxt := :ReanimatCardPulm_DrugTxt,
							    reanimatcardpulm_iseffective := :ReanimatCardPulm_IsEffective,
							    reanimatcardpulm_time := :ReanimatCardPulm_Time,
							    reanimatcardpulm_biologdeath := :ReanimatCardPulm_BiologDeath,
							    reanimatcardpulm_doctortxt := :ReanimatCardPulm_DoctorTxt,
							    pmuser_id := :pmUser_id
							);
						";
						$result = $callObject->db->query($query, $params);
						if (!is_object($result)) {
							return false;
						}
						$EvnScaleResult = $result->result('array');

					}
				}
			}
		} else {
			$query = "
				select
				    evnreanimataction_id as \"EvnReanimatAction_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				from p_evnreanimataction_upd(
				    evnreanimataction_id := :EvnReanimatAction_id,
				    evnreanimataction_pid := :EvnReanimatAction_pid,
				    lpu_id := :Lpu_id,
				    server_id := :Server_id,
				    personevn_id := :PersonEvn_id,
				    evnreanimataction_disdt := :EvnReanimatAction_disDT,
				    pmuser_id := :pmUser_id
				);
			";
			$result = $callObject->db->query($query, $params);
			if (!is_object($result)) {
				return false;
			}
			$EvnScaleResult = $result->result("array");
			if (($EvnScaleResult[0]["EvnReanimatAction_id"]) && ($EvnScaleResult[0]["Error_Code"] == null) && ($EvnScaleResult[0]["Error_Message"] == null)) {
				$EvnReanimatAction_id_Rate = $EvnScaleResult[0]["EvnReanimatAction_id"];
			}
		}
		if (isset($EvnReanimatAction_id_Rate) && isset($params["Rate_List"])) {
			$StepsToChange = "";
			if ($params["ReanimatActionType_SysNick"] == "endocranial_sensor") {
				$RateType = "VCHD";
				$StepsToChange = "RateVCHD_StepsToChange := :Rate_StepsToChange,";
			} elseif ($params["ReanimatActionType_SysNick"] == "invasive_hemodynamics") {
				$RateType = "Hemodynam";
			} else {
				$RateType = "SPO2";
			}
			foreach ($params["Rate_List"] as $Rate) {
				$Rate["pmUser_id"] = isset($data["pmUser_id"]) ? $data["pmUser_id"] : null;
				$Rate["EvnReanimatAction_id"] = $EvnReanimatAction_id_Rate;
				$Rate["Rate_setDT"] = substr($Rate["Rate_setDate"], 6, 4) . "-" . substr($Rate["Rate_setDate"], 3, 2) . "-" . substr($Rate["Rate_setDate"], 0, 2) . " " . $Rate["Rate_setTime"] . ":00";
				switch ($Rate['Rate_RecordStatus']) {
					case 0:
						//добавление нового измерения
						$selectString = "
							{$RateType}_id as \"EvnReanimatAction_id\",
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						";
						$query = "
							select {$selectString}
							from p_rate{$RateType}_ins(
							    evnreanimataction_id := :EvnReanimatAction_id,
							    rate{$RateType}_value := :Rate_Value,{$StepsToChange}
							    rate{$RateType}_setdt := :Rate_setDT,
							    pmuser_id := :pmUser_id
							);
						";
						$result = $callObject->db->query($query, $Rate);
						break;
					case 2:
						//изменение измерения
						$selectString = "
							{$RateType}_id as \"EvnReanimatAction_id\",
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						";
						$query = "
							select {$selectString}
							from p_rate{$RateType}_upd(
							    {$RateType}_id := :Rate_id,
							    rate{$RateType}_value := :Rate_Value,{$StepsToChange}
							    rate{$RateType}_setdt := :Rate_setDT,
							    pmuser_id := :pmUser_id
							);
						";
						$result = $callObject->db->query($query, $Rate);
						break;
					case 3:
						//удалениен измерения
						$selectString = "
							:Rate_id as \"EvnReanimatAction_id\",
							error_code as \"Error_Code\",
							error_message as \"Error_Message\"
						";
						$query = "
							select {$selectString}
							from p_rate{$RateType}_del(
							    {$RateType}_id := :Rate_id,
							    pmuser_id := :pmUser_id,
							    isremove := 1
							);
						";
						$result = $callObject->db->query($query, $Rate);
						break;
				}
			}
		}
		if (!is_object($result)) {
			return false;
		}
		if (sizeof($EvnScaleResult) > 0) {
			if (empty($EvnScaleResult[0]["EvnReanimatAction_id"]) || !empty($EvnScaleResult[0]["Error_Code"]) || !empty($EvnScaleResult[0]["Error_Message"])) {
				$Response["success"] = "false";
				$Response["Error_Msg"] = trim($EvnScaleResult[0]["Error_Code"] . " " . $EvnScaleResult[0]["Error_Message"]);
			}
		}
		return $Response;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function EvnReanimatAction_Save(EvnReanimatPeriod_model $callObject, $data)
	{
		$Response = ["success" => "true", "Error_Msg" => ""];

		$EvnUslugaCommonResult = null;
		$EvnUsluga_id = null;
		$DrugNameResult = null;
		$EvnDrug_id = null;
		$EvnReanimatAction_id_Rate = null;
		$data["EvnReanimatAction_setDate"] .= " " . $data["EvnReanimatAction_setTime"] . ":00";
		if(($data["EvnReanimatAction_disDate"] == "") || $data["EvnReanimatAction_disTime"] == "") {
			$data["EvnReanimatAction_disDate"] = null;
		} else {
			$data["EvnReanimatAction_disDate"] =$data["EvnReanimatAction_disDate"]." ".$data["EvnReanimatAction_disTime"].":00";
		}

		$params = [
			"EvnReanimatAction_id" => $data["EvnReanimatAction_id"],
			"ReanimatActionType_SysNick" => $data["ReanimatActionType_SysNick"],
			"EvnReanimatAction_pid" => isset($data["EvnReanimatAction_pid"]) ? $data["EvnReanimatAction_pid"] : null,
			"EvnSection_id" => isset($data["EvnSection_id"]) ? $data["EvnSection_id"] : null,   //BOB - 02.09.2018
			"EvnReanimatAction_setDT" => isset($data["EvnReanimatAction_setDate"]) ? $data["EvnReanimatAction_setDate"] : null,
			"EvnReanimatAction_disDT" => isset($data["EvnReanimatAction_disDate"]) ? $data["EvnReanimatAction_disDate"] : null,
			"Lpu_id" => isset($data["Lpu_id"]) ? $data["Lpu_id"] : null,
			"Server_id" => isset($data["Server_id"]) ? $data["Server_id"] : null,
			"PersonEvn_id" => isset($data["PersonEvn_id"]) ? $data["PersonEvn_id"] : null,
			"pmUser_id" => isset($data["pmUser_id"]) ? $data["pmUser_id"] : null,
			"ReanimatActionType_id" => isset($data["ReanimatActionType_id"]) ? $data["ReanimatActionType_id"] : null,
			"UslugaComplex_id" => $data["ReanimatActionType_id"] != 3 ? (isset($data["UslugaComplex_id"]) ? $data["UslugaComplex_id"] : null) : null,
			"NutritiousType_id" => $data["ReanimatActionType_id"] == 3 ? (isset($data["UslugaComplex_id"]) ? $data["UslugaComplex_id"] : null) : null,
			"ReanimDrugType_id" => isset($data["ReanimDrugType_id"]) ? $data["ReanimDrugType_id"] : null,
			"EvnReanimatAction_DrugDose" => isset($data["EvnReanimatAction_DrugDose"]) ? $data["EvnReanimatAction_DrugDose"] : null,
			"EvnReanimatAction_MethodCode" => isset($data["EvnReanimatAction_MethodCode"]) ? $data["EvnReanimatAction_MethodCode"] : null,
			"EvnReanimatAction_ObservValue" => isset($data["EvnReanimatAction_ObservValue"]) ? $data["EvnReanimatAction_ObservValue"] : null,
			"ReanimatCathetVeins_id" => isset($data["ReanimatCathetVeins_id"]) ? $data["ReanimatCathetVeins_id"] : null,
			"CathetFixType_id" => isset($data["CathetFixType_id"]) ? $data["CathetFixType_id"] : null,
			"EvnReanimatAction_CathetNaborName" => isset($data["EvnReanimatAction_CathetNaborName"]) ? $data["EvnReanimatAction_CathetNaborName"] : null,
			"EvnReanimatAction_DrugUnit" => isset($data["EvnReanimatAction_DrugUnit"]) ? $data["EvnReanimatAction_DrugUnit"] : null,
			"LpuSection_id" => isset($data["LpuSection_id"]) ? $data["LpuSection_id"] : null,
			"IVLParameter" => isset($data["IVLParameter"]) ? json_decode($data["IVLParameter"], true) : null,
			"CardPulm" => isset($data["CardPulm"]) ? json_decode($data["CardPulm"], true) : null,
			"Rate_List" => isset($data["Rate_List"]) ? json_decode($data["Rate_List"], true) : null,
			"EvnReanimatAction_MethodTxt" => isset($data["EvnReanimatAction_MethodTxt"]) ? $data["EvnReanimatAction_MethodTxt"] : null,  //BOB - 03.11.2018  метод - вариант пользователя
			"EvnReanimatAction_NutritVol" => isset($data["EvnReanimatAction_NutritVol"]) ? $data["EvnReanimatAction_NutritVol"] : null,  //BOB - 03.11.2018  объём питания
			"EvnReanimatAction_NutritEnerg" => isset($data["EvnReanimatAction_NutritEnerg"]) ? $data["EvnReanimatAction_NutritEnerg"] : null, //BOB - 03.11.2018  энеогия питания
			"EvnUsluga_id" => isset($data["EvnUsluga_id"]) ? $data["EvnUsluga_id"] : null, //BOB - 04.07.2019
			"EvnDrug_id" => isset($data["EvnDrug_id"]) ? $data["EvnDrug_id"] : null, //BOB - 04.07.2019
		];
		$NewEvnReanimatAction = $params["EvnReanimatAction_id"] == "New_GUID_Id";
		if (in_array($params["ReanimatActionType_SysNick"], ["lung_ventilation", "hemodialysis", "endocranial_sensor", "epidural_analgesia", "catheterization_veins"])) {
			// если типы мероприятий, содержащие услуги
			$params["MedPersonal_id"] = isset($data["MedPersonal_id"]) ? $data["MedPersonal_id"] : null;
			$params["MedStaffFact_id"] = isset($data["MedStaffFact_id"]) ? $data["MedStaffFact_id"] : null;
			$params["PayType_id"] = isset($data["PayType_id"]) ? $data["PayType_id"] : null;     //BOB - 21.03.2018
			$params["Diag_id"] = isset($data["Diag_id"]) ? $data["Diag_id"] : null;
			
			 //BOB - 02.09.2018
             $params["EvnUslugaCommon_disDT"] = isset($params["EvnReanimatAction_disDT"]) ? $params["EvnReanimatAction_disDT"] : null;
             if (in_array($params["ReanimatActionType_SysNick"],array("epidural_analgesia","catheterization_veins")))
              $params["EvnUslugaCommon_disDT"] = $params["EvnReanimatAction_setDT"];

			
			//если старая запись - сначалап удаляю EvnUslugaCommon
			if (!$NewEvnReanimatAction) {
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from dbo.p_EvnUslugaCommon_del(
						EvnUslugaCommon_id := :EvnUsluga_id,
						pmUser_id := :pmUser_id
					);
				";
				$result = $callObject->db->query($query, $params);
				if (!is_object($result)) {
					return false;
				}
			}
			$query = "
				with mv as (
					select
						uct.UslugaComplexTariff_id
					from dbo.UslugaComplexTariff UCT
					where UCT.UslugaComplex_id = :UslugaComplex_id
						and UCT.UslugaComplexTariff_begDate <= :EvnReanimatAction_setDT
						and (UCT.UslugaComplexTariff_endDate >= :EvnReanimatAction_setDT
							or UCT.UslugaComplexTariff_endDate is null
						)
				)
				
				select
					EvnUslugaCommon_id as \"EvnUslugaCommon_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_EvnUslugaCommon_ins(
					EvnUslugaCommon_pid := :EvnSection_id,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					PersonEvn_id := :PersonEvn_id,
					EvnUslugaCommon_setDT := :EvnReanimatAction_setDT,
					EvnUslugaCommon_disDT := :EvnUslugaCommon_disDT,
					MedPersonal_id := :MedPersonal_id,
					MedStaffFact_id := :MedStaffFact_id,
					LpuSection_uid := :LpuSection_id,
					UslugaComplex_id := :UslugaComplex_id,
					UslugaPlace_id := 1,
					UslugaComplexTariff_id := (select UslugaComplexTariff_id from mv),
					PayType_id := :PayType_id,
					EvnUslugaCommon_Kolvo := 1,
					DiagSetClass_id := 1,
					Diag_id := :Diag_id,
					pmUser_id := :pmUser_id
				);
			";
			$result = $callObject->db->query($query, $params);
			if (!is_object($result)) {
				return false;
			}

			$EvnUslugaCommonResult = $result->result("array");
			if (($EvnUslugaCommonResult[0]["EvnUslugaCommon_id"] == null) || ($EvnUslugaCommonResult[0]["Error_Code"])|| ($EvnUslugaCommonResult[0]["Error_Msg"])){
				$Response["success"] = "false";
				$Response["Error_Msg"] = $EvnUslugaCommonResult[0]["Error_Code"] . " " . $EvnUslugaCommonResult[0]["Error_Message"];
				return $Response;
			}
			$EvnUsluga_id = ($EvnUslugaCommonResult != null) ? $EvnUslugaCommonResult[0]["EvnUslugaCommon_id"] : null;
		}

		// если типы мероприятий, содержащие медикамент
		if (in_array($params["ReanimatActionType_SysNick"], ["vazopressors", "epidural_analgesia", "antifungal_therapy", "catheterization_veins", "invasive_hemodynamics"])) {
			//если старая запись - сначалап удаляю EvnDrug
			if (!$NewEvnReanimatAction) {
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnDrug_del(
						EvnDrug_id := :EvnDrug_id,
						pmUser_id := :pmUser_id
					);
				";
				$result = $callObject->db->query($query, $params);
				if (!is_object($result)) {
					return false;
				}
			}
			//если не "Под наркозом"
			if ($params["ReanimDrugType_id"] != 12) {

				//определяю наименование по коду
				$query = "		
					select ReanimDrugType_name as \"ReanimDrugType_name\"
					from ReanimDrugType
					where ReanimDrugType_id = :ReanimDrugType_id
				";
				$result = $callObject->db->query($query, $params);
				if (!is_object($result)) {
					return false;
				}
				$DrugNameResult = $result->result("array");
				//выбор Drug_id для выбранного медикамента минимального, т.к. без разницы какой, лишь бы было что сохранить в EvnDrug
				$BaseWhere = "";
				switch ($DrugNameResult[0]["ReanimDrugType_name"]) {
					case "Адреналин":
					case "Мезатон":
					case "Норадреналин":
					case "Новокаин":
					case "Лидокаин":
					case "Дофамин":
						$BaseWhere = " and D.DrugTorg_Name = '" . $DrugNameResult[0]['ReanimDrugType_name'] . "'";
						break;
					case "Добутамин":
						$BaseWhere = " and D.DrugTorg_Name ilike '%" . $DrugNameResult[0]['ReanimDrugType_name'] . "%'";
						break;
					case "Ропивакаин":
					case "Каспофунгин":
					case "Микафунгин":
					case "Анидулафунгин":
						$BaseWhere = "
							and D.DrugComplexMnn_id in (
								select DrugComplexMnn_id
								from rls.DrugComplexMnn DCM
								where DCM.DrugComplexMnn_RusName ilike '%" . $DrugNameResult[0]['ReanimDrugType_name'] . "%')
						";
						break;
				}
				$query = "
					select min(Drug_id) as \"Drug_id\"
					from
						rls.Drug D
						inner join rls.DrugPrep DP on DP.DrugPrepFas_id = D.DrugPrepFas_id
					where (1=1) {$BaseWhere}  
					  and D.Drug_begDate <=  dbo.tzgetdate()
					  and (D.Drug_endDate >= dbo.tzgetdate() or D.Drug_endDate is null)
					  and not (D.Drug_Dose is null and Drug_Volume is null and D.Drug_Mass is null and Drug_Fas is null and DP.Drug_Size is null)
				";
				$result = $callObject->db->query($query);
				if (!is_object($result)) {
					return false;
				}
				$DrugNameResult = $result->result("array");
				$params["Drug_id"] = isset($DrugNameResult[0]["Drug_id"]) ? $DrugNameResult[0]["Drug_id"] : null;
				$query = "
					select
						EvnDrug_id as \"EvnDrug_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnDrug_ins(
						EvnDrug_pid := :EvnReanimatAction_pid,
						EvnDrug_setDT := :EvnReanimatAction_setDT,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						PersonEvn_id := :PersonEvn_id,
						LpuSection_id := :LpuSection_id,
						Drug_id := :Drug_id,
						pmUser_id := :pmUser_id
					);
				";
				$result = $callObject->db->query($query, $params);
				if (!is_object($result)) {
					return false;
				}
				$DrugNameResult = $result->result("array");
				if (($DrugNameResult[0]["EvnDrug_id"] == null) || ($DrugNameResult[0]["Error_Code"])|| ($DrugNameResult[0]["Error_Msg"])) {
					$Response["success"] = "false";
					$Response["Error_Msg"] = $DrugNameResult[0]["Error_Code"] . " " . $DrugNameResult[0]["Error_Msg"];
					return $Response;
				}
				$EvnDrug_id = ($DrugNameResult != null) ? $DrugNameResult[0]["EvnDrug_id"] : null;
			}
		}
		$params["EvnUsluga_id"] = isset($EvnUsluga_id) ? $EvnUsluga_id : null;
		$params["EvnDrug_id"] = isset($EvnDrug_id) ? $EvnDrug_id : null;
		//если новая запись
		if ($NewEvnReanimatAction) {
			$query = "
				select
					EvnReanimatAction_id as \"EvnReanimatAction_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from dbo.p_EvnReanimatAction_ins(
					EvnReanimatAction_pid := :EvnReanimatAction_pid,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					ReanimatActionType_id := :ReanimatActionType_id,
					UslugaComplex_id := :UslugaComplex_id,
					EvnUsluga_id := :EvnUsluga_id,
					ReanimDrugType_id := :ReanimDrugType_id,
					EvnReanimatAction_DrugDose := :EvnReanimatAction_DrugDose,
					EvnDrug_id := :EvnDrug_id,
					EvnReanimatAction_MethodCode := :EvnReanimatAction_MethodCode,
					EvnReanimatAction_ObservValue := :EvnReanimatAction_ObservValue,
					ReanimatCathetVeins_id := :ReanimatCathetVeins_id,
					CathetFixType_id := :CathetFixType_id,
					EvnReanimatAction_CathetNaborName := :EvnReanimatAction_CathetNaborName,
					NutritiousType_id := :NutritiousType_id,
					EvnReanimatAction_DrugUnit := :EvnReanimatAction_DrugUnit,
					EvnReanimatAction_MethodTxt := :EvnReanimatAction_MethodTxt,
					EvnReanimatAction_NutritVol := :EvnReanimatAction_NutritVol,
					EvnReanimatAction_NutritEnerg := :EvnReanimatAction_NutritEnerg,
					PersonEvn_id := :PersonEvn_id,
					EvnReanimatAction_setDT := :EvnReanimatAction_setDT,
					EvnReanimatAction_disDT := :EvnReanimatAction_disDT,
					pmUser_id := :pmUser_id
				);
			";
			$result = $callObject->db->query($query, $params);
			if (!is_object($result)) {
				return false;
			}
			$EvnScaleResult = $result->result("array");
		} else {
			// существующая запись - для сохранения даты окончания
			$query = "
				select
					EvnReanimatAction_id as \"EvnReanimatAction_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from dbo.p_EvnReanimatAction_upd(
					EvnReanimatAction_id := :EvnReanimatAction_id,
					EvnReanimatAction_pid := :EvnReanimatAction_pid,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					PersonEvn_id := :PersonEvn_id,
					ReanimatActionType_id := :ReanimatActionType_id,
					UslugaComplex_id := :UslugaComplex_id,
					EvnUsluga_id := :EvnUsluga_id,
					ReanimDrugType_id := :ReanimDrugType_id,
					EvnReanimatAction_DrugDose := :EvnReanimatAction_DrugDose,
					EvnDrug_id := :EvnDrug_id,
					EvnReanimatAction_MethodCode := :EvnReanimatAction_MethodCode,
					EvnReanimatAction_ObservValue := :EvnReanimatAction_ObservValue,
					ReanimatCathetVeins_id := :ReanimatCathetVeins_id,
					CathetFixType_id := :CathetFixType_id,
					EvnReanimatAction_CathetNaborName := :EvnReanimatAction_CathetNaborName,
					NutritiousType_id := :NutritiousType_id,
					EvnReanimatAction_DrugUnit := :EvnReanimatAction_DrugUnit,
					EvnReanimatAction_MethodTxt := :EvnReanimatAction_MethodTxt,
					EvnReanimatAction_NutritVol := :EvnReanimatAction_NutritVol,
					EvnReanimatAction_NutritEnerg := :EvnReanimatAction_NutritEnerg,
					EvnReanimatAction_setDT := :EvnReanimatAction_setDT,
					EvnReanimatAction_disDT := :EvnReanimatAction_disDT,
					pmUser_id := :pmUser_id
				);
			";
			$result = $callObject->db->query($query, $params);
			if (!is_object($result)) {
				return false;
			}
			$EvnScaleResult = $result->result("array");
		}
		if (($EvnScaleResult[0]["EvnReanimatAction_id"]) && ($EvnScaleResult[0]["Error_Code"] == null) && ($EvnScaleResult[0]["Error_Msg"] == null)) {
			$EvnReanimatAction_id_Rate = $EvnScaleResult[0]["EvnReanimatAction_id"];
			//если нутритивная поддержка - закрываем предыдущую нутритивную поддержку
			if (in_array($params["ReanimatActionType_SysNick"], ["nutrition"])) {
				$params["EvnReanimatAction_id_Prev"] = $EvnScaleResult[0]["EvnReanimatAction_id"];
				$query = "
					select
						ERA.EvnReanimatAction_id as \"EvnReanimatAction_id\",
						ERA.EvnReanimatAction_setDT as \"EvnReanimatAction_setDT\",
						ERA.EvnReanimatAction_disDT as \"ReanimatNutritionType_Name\"
					from v_EvnReanimatAction ERA
					where EvnReanimatAction_pid = :EvnReanimatAction_pid
						and EvnReanimatAction_id <> :EvnReanimatAction_id_Prev
						and ReanimatActionType_SysNick = :ReanimatActionType_SysNick
						and ERA.EvnReanimatAction_setDT <= :EvnReanimatAction_setDT
						and ERA.EvnReanimatAction_disDT is null
					order by EvnReanimatAction_setDT desc	
				";
				$result = $callObject->db->query($query, $params);
				if (!is_object($result)) {
					return false;
				}
				$EvnScaleResult = $result->result("array");
				if (sizeof($EvnScaleResult) > 0) {
					$params["EvnReanimatAction_id_Prev"] = $EvnScaleResult[0]["EvnReanimatAction_id"];
					//BOB - 12.09.2019
					$query = "
							update Evn
							set
								Evn_disDT = :EvnReanimatAction_setDT,
								Evn_updDT = dbo.tzGetDate(),
								pmUser_updID = :pmUser_id
							where Evn_id = :EvnReanimatAction_id_Prev;
					";
					$result = $callObject->db->query($query, $params);
					// запрос для формирования $EvnScaleResult - для единообразия алгоритмов - используется в конце метода
					$query = "
						select
							:EvnReanimatAction_id_Prev as \"EvnReanimatAction_id\",
							cast(null as int) as \"Error_Code\",
							cast(null as carchar) as \"Error_Msg\"
					";
					$result = $callObject->db->query($query, $params);
					if (!is_object($result)) {
						return false;
					}
					$EvnScaleResult = $result->result("array");
				}
			}
			//если нутритивная поддержка - закрываем предыдущую нутритивную поддержку
			//если ИВЛ - сохраняю параметры
			if (in_array($params["ReanimatActionType_SysNick"], array("lung_ventilation")) && (isset($params["IVLParameter"]))) {
				$params["IVLParameter_id"] = isset($params["IVLParameter"]["IVLParameter_id"]) ? $params["IVLParameter"]["IVLParameter_id"] : null;
				$params["EvnReanimatAction_id"] = $EvnReanimatAction_id_Rate;
				$params["IVLParameter_Apparat"] = $params["IVLParameter"]["IVLParameter_Apparat"];
				$params["IVLRegim_id"] = $params["IVLParameter"]["IVLRegim_id"];
				$params["IVLParameter_TubeDiam"] = $params["IVLParameter"]["IVLParameter_TubeDiam"];
				$params["IVLParameter_FiO2"] = $params["IVLParameter"]["IVLParameter_FiO2"];
				$params["IVLParameter_PcentMinVol"] = $params["IVLParameter"]["IVLParameter_PcentMinVol"];
				$params["IVLParameter_TwoASVMax"] = $params["IVLParameter"]["IVLParameter_TwoASVMax"];
				$params["IVLParameter_FrequSet"] = $params["IVLParameter"]["IVLParameter_FrequSet"];
				$params["IVLParameter_VolInsp"] = $params["IVLParameter"]["IVLParameter_VolInsp"];
				$params["IVLParameter_PressInsp"] = $params["IVLParameter"]["IVLParameter_PressInsp"];
				$params["IVLParameter_PressSupp"] = $params["IVLParameter"]["IVLParameter_PressSupp"];
				$params["IVLParameter_FrequTotal"] = $params["IVLParameter"]["IVLParameter_FrequTotal"];
				$params["IVLParameter_VolTe"] = $params["IVLParameter"]["IVLParameter_VolTe"];
				$params["IVLParameter_VolE"] = $params["IVLParameter"]["IVLParameter_VolE"];
				$params["IVLParameter_TinTet"] = $params["IVLParameter"]["IVLParameter_TinTet"];
				$params["IVLParameter_VolTrig"] = $params["IVLParameter"]["IVLParameter_VolTrig"];
				$params["IVLParameter_PressTrig"] = $params["IVLParameter"]["IVLParameter_PressTrig"];
				$params["IVLParameter_PEEP"] = $params["IVLParameter"]["IVLParameter_PEEP"];
				$action = $NewEvnReanimatAction ? "ins" : "upd";
				$query = "
					select
						IVLParameter_id as \"EvnReanimatAction_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from dbo.p_IVLParameter_{$action}(
						IVLParameter_id := :IVLParameter_id,
						EvnReanimatAction_id := :EvnReanimatAction_id,
						IVLParameter_Apparat := :IVLParameter_Apparat,
						IVLRegim_id := :IVLRegim_id,
						IVLParameter_TubeDiam := :IVLParameter_TubeDiam,
						IVLParameter_FiO2 := :IVLParameter_FiO2,
						IVLParameter_FrequSet := :IVLParameter_FrequSet,
						IVLParameter_VolInsp := :IVLParameter_VolInsp,
						IVLParameter_PressInsp := :IVLParameter_PressInsp,
						IVLParameter_PressSupp := :IVLParameter_PressSupp,
						IVLParameter_FrequTotal := :IVLParameter_FrequTotal,
						IVLParameter_VolTe := :IVLParameter_VolTe,
						IVLParameter_VolE := :IVLParameter_VolE,
						IVLParameter_TinTet := :IVLParameter_TinTet,
						IVLParameter_VolTrig := :IVLParameter_VolTrig,
						IVLParameter_PressTrig := :IVLParameter_PressTrig,
						IVLParameter_PEEP := :IVLParameter_PEEP,
						IVLParameter_PcentMinVol := :IVLParameter_PcentMinVol,
						IVLParameter_TwoASVMax := :IVLParameter_TwoASVMax,
						pmUser_id := :pmUser_id
					);
				";
				$result = $callObject->db->query($query, $params);
				if (!is_object($result)) {
					return false;
				}
				$EvnScaleResult = $result->result("array");
			}
			//если сердечно-лёгочная реанимация - сохраняю параметры
			if (in_array($params["ReanimatActionType_SysNick"], ["card_pulm"]) && (isset($params["CardPulm"]))) {
				$params["ReanimatCardPulm_id"] = isset($params["CardPulm"]["ReanimatCardPulm_id"]) ? $params["CardPulm"]["ReanimatCardPulm_id"] : null;
				$params["EvnReanimatAction_id"] = $EvnReanimatAction_id_Rate;
				$params["ReanimatCardPulm_ClinicalDeath"] = substr($params["CardPulm"]["ReanimatCardPulm_ClinicalDeathDate"], 0, 10) . " " . $params["CardPulm"]["ReanimatCardPulm_ClinicalDeathTime"] . ":00";
				if (!isset($params["CardPulm"]["ReanimatCardPulm_BiologDeathDate"]) || !isset($params["CardPulm"]["ReanimatCardPulm_BiologDeathTime"])) {
					$params["ReanimatCardPulm_BiologDeath"] = null;
				} else {
					$params["ReanimatCardPulm_BiologDeath"] = substr($params["CardPulm"]["ReanimatCardPulm_BiologDeathDate"], 0, 10) . " " . $params["CardPulm"]["ReanimatCardPulm_BiologDeathTime"] . ":00";
				}
				$params["ReanimatCardPulm_IsPupilDilat"] = $params["CardPulm"]["ReanimatCardPulm_IsPupilDilat"];
				$params["ReanimatCardPulm_IsCardMonitor"] = $params["CardPulm"]["ReanimatCardPulm_IsCardMonitor"];
				$params["ReanimatCardPulm_StopCardActType"] = $params["CardPulm"]["ReanimatCardPulm_StopCardActType"];
				$params["IVLRegim_id"] = isset($params["CardPulm"]["IVLRegim_id"]) ? $params["CardPulm"]["IVLRegim_id"] : null;
				$params["ReanimatCardPulm_FiO2"] = isset($params["CardPulm"]["ReanimatCardPulm_FiO2"]) ? $params["CardPulm"]["ReanimatCardPulm_FiO2"] : null;
				$params["ReanimatCardPulm_IsCardTonics"] = $params["CardPulm"]["ReanimatCardPulm_IsCardTonics"];
				$params["ReanimatCardPulm_CardTonicDose"] = isset($params["CardPulm"]["ReanimatCardPulm_CardTonicDose"]) ? $params["CardPulm"]["ReanimatCardPulm_CardTonicDose"] : null;
				$params["ReanimatCardPulm_CathetVein"] = $params["CardPulm"]["ReanimatCardPulm_CathetVein"];
				$params["ReanimatCardPulm_TrachIntub"] = isset($params["CardPulm"]["ReanimatCardPulm_TrachIntub"]) ? $params["CardPulm"]["ReanimatCardPulm_TrachIntub"] : null;
				$params["ReanimatCardPulm_Auscultatory"] = $params["CardPulm"]["ReanimatCardPulm_Auscultatory"];
				$params["ReanimatCardPulm_AuscultatoryTxt"] = isset($params["CardPulm"]["ReanimatCardPulm_AuscultatoryTxt"]) ? $params["CardPulm"]["ReanimatCardPulm_AuscultatoryTxt"] : null;
				$params["ReanimatCardPulm_CardMassage"] = isset($params["CardPulm"]["ReanimatCardPulm_CardMassage"]) ? $params["CardPulm"]["ReanimatCardPulm_CardMassage"] : null;
				$params["ReanimatCardPulm_DefibrilCount"] = isset($params["CardPulm"]["ReanimatCardPulm_DefibrilCount"]) ? $params["CardPulm"]["ReanimatCardPulm_DefibrilCount"] : null;
				$params["ReanimatCardPulm_DefibrilMin"] = isset($params["CardPulm"]["ReanimatCardPulm_DefibrilMin"]) ? $params["CardPulm"]["ReanimatCardPulm_DefibrilMin"] : null;
				$params["ReanimatCardPulm_DefibrilMax"] = isset($params["CardPulm"]["ReanimatCardPulm_DefibrilMax"]) ? $params["CardPulm"]["ReanimatCardPulm_DefibrilMax"] : null;
				$params["ReanimDrugType_id"] = isset($params["CardPulm"]["ReanimDrugType_id"]) ? $params["CardPulm"]["ReanimDrugType_id"] : null;
				$params["ReanimatCardPulm_DrugDose"] = isset($params["CardPulm"]["ReanimatCardPulm_DrugDose"]) ? $params["CardPulm"]["ReanimatCardPulm_DrugDose"] : null;
				$params["ReanimatCardPulm_DrugSposob"] = isset($params["CardPulm"]["ReanimatCardPulm_DrugSposob"]) ? $params["CardPulm"]["ReanimatCardPulm_DrugSposob"] : null;
				$params["ReanimDrugType_did"] = isset($params["CardPulm"]["ReanimDrugType_did"]) ? $params["CardPulm"]["ReanimDrugType_did"] : null;
				$params["ReanimatCardPulm_dDrugDose"] = isset($params["CardPulm"]["ReanimatCardPulm_dDrugDose"]) ? $params["CardPulm"]["ReanimatCardPulm_dDrugDose"] : null;
				$params["ReanimatCardPulm_dDrugSposob"] = isset($params["CardPulm"]["ReanimatCardPulm_dDrugSposob"]) ? $params["CardPulm"]["ReanimatCardPulm_dDrugSposob"] : null;
				$params["ReanimatCardPulm_DrugTxt"] = isset($params["CardPulm"]["ReanimatCardPulm_DrugTxt"]) ? $params["CardPulm"]["ReanimatCardPulm_DrugTxt"] : null;
				$params["ReanimatCardPulm_IsEffective"] = $params["CardPulm"]["ReanimatCardPulm_IsEffective"];
				$params["ReanimatCardPulm_Time"] = $params["CardPulm"]["ReanimatCardPulm_Time"];
				$params["ReanimatCardPulm_DoctorTxt"] = $params["CardPulm"]["ReanimatCardPulm_DoctorTxt"];
				$action = $NewEvnReanimatAction ? "ins" : "upd";
				$query = "
					select
						ReanimatCardPulm_id as \"EvnReanimatAction_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_ReanimatCardPulm_{$action}(
						ReanimatCardPulm_id := :ReanimatCardPulm_id,
						EvnReanimatAction_id := :EvnReanimatAction_id,
						ReanimatCardPulm_ClinicalDeath := :ReanimatCardPulm_ClinicalDeath,
						ReanimatCardPulm_IsPupilDilat := :ReanimatCardPulm_IsPupilDilat,
						ReanimatCardPulm_IsCardMonitor := :ReanimatCardPulm_IsCardMonitor,
						ReanimatCardPulm_StopCardActType := :ReanimatCardPulm_StopCardActType,
						IVLRegim_id := :IVLRegim_id,
						ReanimatCardPulm_FiO2 := :ReanimatCardPulm_FiO2,
						ReanimatCardPulm_IsCardTonics := :ReanimatCardPulm_IsCardTonics,
						ReanimatCardPulm_CardTonicDose := :ReanimatCardPulm_CardTonicDose,
						ReanimatCardPulm_CathetVein := :ReanimatCardPulm_CathetVein,
						ReanimatCardPulm_TrachIntub := :ReanimatCardPulm_TrachIntub,
						ReanimatCardPulm_Auscultatory := :ReanimatCardPulm_Auscultatory,
						ReanimatCardPulm_AuscultatoryTxt := :ReanimatCardPulm_AuscultatoryTxt,
						ReanimatCardPulm_CardMassage := :ReanimatCardPulm_CardMassage,
						ReanimatCardPulm_DefibrilCount := :ReanimatCardPulm_DefibrilCount,
						ReanimatCardPulm_DefibrilMin := :ReanimatCardPulm_DefibrilMin,
						ReanimatCardPulm_DefibrilMax := :ReanimatCardPulm_DefibrilMax,
						ReanimDrugType_id := :ReanimDrugType_id,
						ReanimatCardPulm_DrugDose := :ReanimatCardPulm_DrugDose,
						ReanimatCardPulm_DrugSposob := :ReanimatCardPulm_DrugSposob,
						ReanimDrugType_did := :ReanimDrugType_did,
						ReanimatCardPulm_dDrugDose := :ReanimatCardPulm_dDrugDose,
						ReanimatCardPulm_dDrugSposob := :ReanimatCardPulm_dDrugSposob,
						ReanimatCardPulm_DrugTxt := :ReanimatCardPulm_DrugTxt,
						ReanimatCardPulm_IsEffective := :ReanimatCardPulm_IsEffective,
						ReanimatCardPulm_Time := :ReanimatCardPulm_Time,
						ReanimatCardPulm_BiologDeath := :ReanimatCardPulm_BiologDeath,
						ReanimatCardPulm_DoctorTxt := :ReanimatCardPulm_DoctorTxt,
						pmUser_id := :pmUser_id
					);
				";
				$result = $callObject->db->query($query, $params);
				if (!is_object($result)) {
					return false;
				}
				$EvnScaleResult = $result->result("array");
			}
			//СОХРАНЕНИЕ ИЗМЕРЕНИЙ
			if (isset($EvnReanimatAction_id_Rate) && isset($params["Rate_List"])) {
				$StepsToChange = "";
				if ($params["ReanimatActionType_SysNick"] == "endocranial_sensor") {
					$RateType = "VCHD";
					$StepsToChange = "RateVCHD_StepsToChange := :Rate_StepsToChange,";
				} elseif ($params["ReanimatActionType_SysNick"] == "invasive_hemodynamics") {
					$RateType = "Hemodynam";
				} else {
					$RateType = "SPO2";
				}
				foreach ($params["Rate_List"] as $Rate) {
					$Rate["pmUser_id"] = isset($data["pmUser_id"]) ? $data["pmUser_id"] : null;
					$Rate["EvnReanimatAction_id"] = $EvnReanimatAction_id_Rate;
					$Rate["Rate_setDT"] = substr($Rate["Rate_setDate"], 6, 4) . "-" . substr($Rate["Rate_setDate"], 3, 2) . "-" . substr($Rate["Rate_setDate"], 0, 2) . " " . $Rate["Rate_setTime"] . ":00";
					switch ($Rate["Rate_RecordStatus"]) {
						case 0:
							//добавление нового измерения
							$query = "
								select
									Rate{$RateType}_id as \"EvnReanimatAction_id\",
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"
								from p_Rate{$RateType}_ins(
									EvnReanimatAction_id := :EvnReanimatAction_id,
									Rate{$RateType}_Value := :Rate_Value,
									{$StepsToChange}
									Rate{$RateType}_setDT := :Rate_setDT,
								 	pmUser_id := :pmUser_id
								);
							";
							$result = $callObject->db->query($query, $Rate);
							break;
						case 2:
							//изменение измерения
							$query = "
								select
									Rate{$RateType}_id as \"EvnReanimatAction_id\",
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"
								from dbo.p_Rate{$RateType}_upd(
									Rate{$RateType}_id := :Rate_id,
									EvnReanimatAction_id := :EvnReanimatAction_id,
									Rate{$RateType}_Value := :Rate_Value,
									{$StepsToChange}
									Rate{$RateType}_setDT := :Rate_setDT,
								 	pmUser_id := :pmUser_id
								);
							";
							$result = $callObject->db->query($query, $Rate);
							break;
						case 3:
							//удалениен измерения
							$query = "
								select
									:Rate_id as \"EvnReanimatAction_id\",
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"
								from p_Rate{$RateType}_del(
									Rate{$RateType}_id := :Rate_id,
									IsRemove := 1,
								 	pmUser_id := :pmUser_id
								);
							";
							$result = $callObject->db->query($query, $Rate);
							break;
					}
				}
			}
		}
		if (!empty($EvnScaleResult)) {
			if (empty($EvnScaleResult[0]["EvnReanimatAction_id"]) || !empty($EvnScaleResult[0]["Error_Code"]) || !empty($EvnScaleResult[0]["Error_Msg"])) {
				$Response["success"] = "false";
				$Response["Error_Msg"] = trim($EvnScaleResult[0]["Error_Code"] . " " . $EvnScaleResult[0]["Error_Msg"]);
			}
		}
		// подумать что возвращать: ссылки на привязанные записи , а может просто сообщение
		if (is_object($result)) {
			return $Response;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка реанимационных служб по МО
	 */
	public static function getReanimationServices($thisModel, $data) {

		$params['Lpu_id'] = $data['Lpu_id'];

		$result = $thisModel->queryResult("        
			select 
				ms.MedService_id as \"MedService_id\",
				ms.MedService_Nick as \"MedService_Nick\"
			from v_MedService ms
			inner join v_MedServiceType mst on ms.MedServiceType_id = mst.MedServiceType_id
			where (1=1) 
				and ms.Lpu_id = :Lpu_id  
				and mst.MedServiceType_SysNick = 'reanimation'
				and ms.MedService_endDT is null
		", $params);

		return $result;
	}

	/**
	 * Перевод пациента в реанимацию из АРМа мобильного стационара
	 */
	public static function mMoveToReanimation($thisModel, $data) {

		//Выборка определяющая не находится ли пациент в данный момент в реанимации
		$reanimated = $thisModel->getFirstResultFromQuery("
			select
				EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\"
			from v_EvnReanimatPeriod ERP
			where (1=1)
				and ERP.Person_id = :Person_id
				and ERP.EvnReanimatPeriod_setDate <= dbo.tzGetDate()
				and ERP.EvnReanimatPeriod_disDate is null
			limit 1
		", array('Person_id' => $data['Person_id']));

		//если есть открытый реанимационный период пациента, т.е. он уже в реанимации
		if (!empty($reanimated)) {
			return array('Error_Msg' => 'Данный пациент уже находится в реанимации');
		}

		// загрузим так же периодику пациента
		$periodic = $thisModel->getFirstRowFromQuery("
			select
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\"
			from v_PersonEvn
			where Person_id = :Person_id
			order by PersonEvn_id desc
			limit 1
		", array('Person_id' => $data['Person_id']));

		// и данные по движению
		$EvnPS_id = $thisModel->getFirstResultFromQuery("
			select
				EvnSection_pid as \"EvnPS_id\"
			from v_EvnSection
			where EvnSection_id = :EvnSection_id
			limit 1
		", array('EvnSection_id' => $data['EvnSection_id']));

		$params = array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedService_id' => $data['MedService_id'],
			'MedPersonal_id' =>  !empty($data['session']['medpersonal_id']) ? $data['session']['medpersonal_id'] : null,
			'LpuSection_id' => $data['LpuSection_id'],
			'EvnPS_id' => !empty($EvnPS_id) ? $EvnPS_id : null,
			'EvnSection_id' => $data['EvnSection_id'],
			'Server_id' => $periodic['Server_id'],
			'PersonEvn_id' => $periodic['PersonEvn_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$params["ReanimatAgeGroup_id"] = $callObject->getFirstResultFromQuery("
			select
				case
					when cast(cast(getdate() as date) as timestamp) - interval '29 day' < PS.Person_BirthDay then 1
					when cast(cast(getdate() as date) as timestamp) - interval '29 day' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '1 year' < PS.Person_BirthDay then 2
					when cast(cast(getdate() as date) as timestamp) - interval '1 year' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '4 year' < PS.Person_BirthDay then 3
					when cast(cast(getdate() as date) as timestamp) - interval '4 year' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '18 year' < PS.Person_BirthDay then 4
		        else 5 end
			from
				v_PersonState PS
				inner join PersonEvn PE on PE.Person_id = PS.Person_id
			where
				PE.PersonEvn_id = :PersonEvn_id
		 ", $params);
		
		$query = "
			select
				EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnReanimatPeriod_ins(
				EvnReanimatPeriod_pid := :EvnSection_id, 
				EvnReanimatPeriod_rid := :EvnPS_id, 
				Lpu_id := :Lpu_id, 
				Server_id := :Server_id, 
				MedService_id := :MedService_id,
				LpuSection_id := :LpuSection_id,
				ReanimResultType_id := null,
				ReanimReasonType_id := 1,
				LpuSectionBedProfile_id := null, 
				ReanimatAgeGroup_id := :ReanimatAgeGroup_id,
				PersonEvn_id := :PersonEvn_id, 
				EvnReanimatPeriod_setDT := getdate(),
				EvnReanimatPeriod_disDT := null,
				EvnReanimatPeriod_didDT := null,
				Morbus_id := null,
				EvnReanimatPeriod_IsSigned := null,
				pmUser_signID := null,
				EvnReanimatPeriod_signDT := null,
				EvnStatus_id := null,
				EvnReanimatPeriod_statusDate := null,
				isReloadCount := null,
				pmUser_id := :pmUser_id
			);
		";

		$thisModel->beginTransaction();
		$saveResult = $thisModel->getFirstRowFromQuery($query, $params);

		if (empty($saveResult['EvnReanimatPeriod_id'])) {

			$thisModel->rollbackTransaction();
			$error = !empty($saveResult['Error_Msg']) ? ': '.$saveResult['Error_Msg'] : '';
			$result['Error_Msg'] = 'Ошибка при сохранении реанимационного периода'.$error;

		} else {

			$params['EvnReanimatPeriod_id'] = $saveResult['EvnReanimatPeriod_id'];

			$thisModel->load->model('ReanimatRegister_model', 'ReanimatRegister_model');
			$registerResult = $thisModel->ReanimatRegister_model->mSaveReanimatRegister($params);

			if (!empty($registerResult['ReanimatRegister_id'])) {
				$result = array(
					'ReanimatRegister_id' => $registerResult['ReanimatRegister_id'],
					'EvnReanimatPeriod_id' => $saveResult['EvnReanimatPeriod_id']
				);

				$thisModel->sendCallReanimateTeamMessage(array(
					'MedService_id' => $data['MedService_id'],
					'pmUser_id' => $data['pmUser_id'],
					'Person_id' => $data['Person_id'],
					'LpuSection_id' => $data['LpuSection_id']
				));

			} else {
				$thisModel->rollbackTransaction();
				$rr_error = !empty($registerResult['Error_Msg']) ? ': '.$registerResult['Error_Msg'] : '';
				$result['Error_Msg'] = 'Ошибка при сохранении реанимационного регистра'.$rr_error;
			}
		}

		$thisModel->commitTransaction();
		return $result;
	}

	/**
	 * Отправка сообщения всему персоналу на службе реанимации
	 */
	public static function sendCallReanimateTeamMessage($thisModel, $data) {

		$recepients = $thisModel->queryResult("
			select
				pmUser_id as \"pmUser_id\"
			from v_pmUserCache puc
				inner join v_MedServiceMedPersonal msmp on msmp.MedPersonal_id = puc.MedPersonal_id
			where (1=1)
				and msmp.MedService_id = :MedService_id
				and coalesce(puc.pmUser_deleted, 1) = 1
		", array('MedService_id' => $data['MedService_id']));

		if (!empty($recepients)) {

			$LpuSection_Name = $thisModel->getFirstResultFromQuery("
				select LpuSection_Name from v_LpuSection
				where LpuSection_id = :LpuSection_id
				limit 1
			", array('LpuSection_id' => $data['LpuSection_id']));

			$Person_FullName = $thisModel->getFirstResultFromQuery("
				select (coalesce(Person_SurName, '') || ' ' || coalesce(Person_FirName, '') || ' ' || coalesce(Person_SecName, '')) as Person_FullName 
				from v_PersonState
				where Person_id = :Person_id
				limit 1
			", array('Person_id' => $data['Person_id']));

			$message = "Отделение '{$LpuSection_Name}' запрашивает реанимационную группу для пациента {$Person_FullName}";
			$noticeData = array(
				'autotype' => 5,
				'pmUser_id' => $data['pmUser_id'],
				'type' => 1,
				'title' => 'Запрос реанимационной группы',
				'text' => $message
			);

			foreach($recepients as $medpersonal) {

				$noticeData['User_rid'] = $medpersonal['pmUser_id'];

				$thisModel->load->model('Messages_model');
				$thisModel->Messages_model->autoMessage($noticeData);
			}
		}
	}
}