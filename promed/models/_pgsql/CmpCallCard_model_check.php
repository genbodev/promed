<?php


class CmpCallCard_model_check
{
	/**
	 * Проверка и устнановка статуса карте при ее сохранении
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @throws Exception
	 */
	public static function checkCallStatusOnSave(CmpCallCard_model $callObject, $data)
	{
		//получаем код типа вызова
		if (!empty($data["CmpCallType_id"]) && empty($data["CmpCallType_Code"])) {
			$typeCardQuery = "
				select *
				from v_CmpCallType
				where CmpCallType_id = :CmpCallType_id
				limit 1
			";
			$typeCard = $callObject->db->query($typeCardQuery, $data)->row_array();
			if (!empty($typeCard["CmpCallType_Code"])) {
				$data["CmpCallType_Code"] = $typeCard["CmpCallType_Code"];
			}
		}
		// Если Тип вызова «Консультативное», «Консультативный», «Справка», «Абонент отключился»,
		//то автоматически вызову присваивается статус «Закрыто»
		if (!empty($data["CmpCallType_Code"]) && in_array($data["CmpCallType_Code"], [6, 15, 16, 17])) {
			$data["CmpCallCardStatusType_id"] = 6;
		}
		$callObject->setStatusCmpCallCard($data);
	}

	/**
	 * функция либо возвращает ид персон, либо создает оный при его отсутствиипри
	 * при неизвестном пациенте сохраняем неизвестного и вставляет новый ид в талон
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return bool|mixed
	 */
	public static function checkUnknownPerson(CmpCallCard_model $callObject, $data)
	{
		if ((!empty($data["Person_IsUnknown"]) && $data["Person_IsUnknown"] == 1) || (empty($data["Person_Age"]) && empty($data["Person_Birthday"])) || empty($data["Person_SurName"])) {
			return false;
		}
		//при неизвестном пациенте сохраняем неизвестного и вставляет новый ид в талон
		$socstatus_Ids = [
			"vologda" => 304,
			"ufa" => 2,
			"krasnoyarsk" => 10000173,
			"yaroslavl" => 10000266,
			"buryatiya" => 10000083,
			"kareliya" => 51,
			"khak" => 32,
			"astra" => 10000053,
			"kaluga" => 231,
			"penza" => 224,
			"perm" => 2,
			"pskov" => 25,
			"saratov" => 10000035,
			"ekb" => 10000072,
			"msk" => 60,
			"krym" => 262,
			"kz" => 91,
			"by" => 201
		];
		if (!empty($data["Person_id"])) {
			return $data["Person_id"];
		}
		$callObject->load->model("Person_model", "Person_model");
		$Person_BirthDay = (empty($data["Person_BirthDay"])) ? "01.01." . (date("Y") - $data["Person_Age"]) : $data["Person_BirthDay"];
		$funcParams = [
			"Server_id" => $data["Server_id"],
			"NationalityStatus_IsTwoNation" => false,
			"Polis_CanAdded" => 0,
			"Person_SurName" => !empty($data["Person_SurName"]) ? $data["Person_SurName"] : "",
			"Person_FirName" => !empty($data["Person_FirName"]) ? $data["Person_FirName"] : "",
			"Person_SecName" => !empty($data["Person_SecName"]) ? $data["Person_SecName"] : "",
			"Person_BirthDay" => $Person_BirthDay,
			"Person_IsUnknown" => 2,
			"PersonSex_id" => $data["Sex_id"],
			"SocStatus_id" => $socstatus_Ids[getRegionNick()],
			"session" => $data["session"],
			"mode" => "add",
			"pmUser_id" => $data["pmUser_id"],
			"Person_id" => null,
			"Polis_begDate" => null
		];
		$result = $callObject->Person_model->savePersonEditWindow($funcParams);
		if (!empty($result[0]["Person_id"])) {
			return $result[0]["Person_id"];
		}
		//Неизвестный, но не удалось сохранить человека
		return true;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return mixed
	 */
	public static function checkCmpCallCardNumber(CmpCallCard_model $callObject, $data)
	{
		$where = [
			"CCC.Lpu_id=:Lpu_id",
			"CCC.CmpCallCard_Numv = :CmpCallCard_Numv",
			"CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod"
		];
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"CmpCallCard_Numv" => $data["CmpCallCard_Numv"],
			"CmpCallCard_Ngod" => $data["CmpCallCard_Ngod"]
		];
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$sql = "
			select
				CmpCallCard_Numv,
				CmpCallCard_Ngod
			from v_CmpCallCard CCC
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return $data;
		}
		$res = $callObject->getCmpCallCardNumber($data);
		return $res[0];
	}

	/**
	 * функция проверяет изменения по карте и регистрирует событие Корректировка вызова
	 * @param CmpCallCard_model $callObject
	 * @param $oldCard
	 * @param $newCard
	 * @return bool
	 * @throws Exception
	 */
	public static function checkChangesCmpCallCard(CmpCallCard_model $callObject, $oldCard, $newCard)
	{
		$changed = false;
		$editableParameters = [
			"CmpCallCard_Tper",
			"CmpCallCard_Vyez",
			"CmpCallCard_Przd",
			"CmpCallCard_Tisp",
			"CmpCallCard_HospitalizedTime",
			"CmpCallCard_Comm"
		];
		foreach ($newCard as $key => $value) {
			if (isset($oldCard[$key])) {
				if ($oldCard[$key] instanceof DateTime && !($newCard[$key] instanceof DateTime)) {
					$newCard[$key] = new DateTime($newCard[$key]);
				}
				if ($oldCard[$key] != $newCard[$key]) {
					$changed = true;
					break;
				}
			} elseif (in_array($key, $editableParameters)) {
				$changed = true;
				break;
			}
		};
		if ($changed) {
			$eventParams = [
				"CmpCallCard_id" => $newCard["CmpCallCard_id"],
				"CmpCallCardEventType_Code" => 32,
				"CmpCallCardEvent_Comment" => 'Корректировка вызова',
				"pmUser_id" => $newCard["pmUser_id"]
			];
			$callObject->setCmpCallCardEvent($eventParams);
		}
		return $changed;
	}

	/**
	 * Проверка оплаты диагноза по ОМС
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function checkDiagFinance(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["Diag_id"])) {
			return false;
		}
		if (isset($data["Person_id"])) {
			$query = "
				select
					IsOms.YesNo_Code as \"DiagFinance_IsOms\",
					IsAlien.YesNo_Code as \"DiagFinance_IsAlien\",
					df.Sex_id as \"Diag_Sex\",
					a.PersonAgeGroup_Code as \"DiagFinanceAgeGroup_Code\",
					case when age2(p.Person_BirthDay, tzgetdate()) < 18 then 2 else 1 end as \"PersonAgeGroup_Code\",
					p.Sex_id as \"Sex_id\",
					age2(p.Person_BirthDay, tzgetdate()) as \"Age\"
				from
					v_DiagFinance df
					left join PersonAgeGroup a on a.PersonAgeGroup_id = df.PersonAgeGroup_id
					left join YesNo IsAlien on IsAlien.YesNo_id = df.DiagFinance_IsAlien
					left join YesNo IsOms on IsOms.YesNo_id = df.DiagFinance_IsOms
					left join lateral (
						select
							ost.OmsSprTerr_Code,
							ps.Sex_id,
							ps.Person_BirthDay
						from
							v_PersonState ps
							left join v_Polis pls on pls.Polis_id = ps.Polis_id
							left join v_OmsSprTerr ost on ost.OmsSprTerr_id = pls.OmsSprTerr_id
						where ps.Person_id = :Person_id
						limit 1
					) as p on true
				where df.Diag_id = :Diag_id
				limit 1
			";
			$queryParams = [
				"Diag_id" => $data["Diag_id"],
				"Person_id" => $data["Person_id"]
			];
		} elseif (isset($data["Age"]) && isset($data["Sex_id"])) {
			$Age = $data["Age"];
			$Sex_id = $data["Sex_id"];
			$query = "
				select
					IsOms.YesNo_Code as \"DiagFinance_IsOms\",
					IsAlien.YesNo_Code as \"DiagFinance_IsAlien\",
					df.Sex_id as \"Diag_Sex\",
					a.PersonAgeGroup_Code as \"DiagFinanceAgeGroup_Code\",
					case when '{$Age}' < 18 then 2 else 1 end as \"PersonAgeGroup_Code\",
					'{$Sex_id}' as \"Sex_id\",
					'{$Age}' as \"Age\"
				from
					v_DiagFinance df
					left join PersonAgeGroup a on a.PersonAgeGroup_id = df.PersonAgeGroup_id
					left join YesNo IsAlien on IsAlien.YesNo_id = df.DiagFinance_IsAlien
					left join YesNo IsOms on IsOms.YesNo_id = df.DiagFinance_IsOms
				where df.Diag_id = :Diag_id
				limit 1
			";
			$queryParams = ["Diag_id" => $data["Diag_id"]];
		} else {
			return false;
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return $result->result('array');
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function checkDuplicateCmpCallCard(CmpCallCard_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		$filterArray = ["(1 = 1 )"];
		$queryParams = [];
		$addressResponse = [];
		$personResponse = [];
		//если не заполнен ни город ни нас пункт - не тужимся
		if (!empty($data["KLCity_id"]) || !empty($data["KLTown_id"])) {
			if (!empty($data["CmpCallCard_prmDate"])) {
				$queryParams["CmpCallCard_prmDT"] = $data["CmpCallCard_prmDate"];
				if (!empty($data["CmpCallCard_prmTime"])) {
					$queryParams["CmpCallCard_prmDT"] .= " " . $data["CmpCallCard_prmTime"] . ":00";
                    $filterArray[] = " and cast(CCC.CmpCallCard_prmDT as date) >= dateadd('day',-1,:CmpCallCard_prmDT)";
                } else {
                    $filterArray[] = " and cast(CCC.CmpCallCard_prmDT as date) = :CmpCallCard_prmDT";
                }
			}
			if (!empty($data["CmpCallCard_id"])) {
				$filterArray[] = "CCC.CmpCallCard_id != :CmpCallCard_id";
				$queryParams["CmpCallCard_id"] = $data["CmpCallCard_id"];
			}
			if (!empty($data["CmpCallCard_Numv"])) {
				$filterArray[] = "CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
				$queryParams["CmpCallCard_Numv"] = $data["CmpCallCard_Numv"];
			}
			if (!empty($data["CmpCallCard_Ngod"])) {
				$filterArray[] = "CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
				$queryParams["CmpCallCard_Ngod"] = $data["CmpCallCard_Ngod"];
			}
			if (!empty($data["KLSubRgn_id"])) {
				$filterArray[] = "CCC.KLSubRgn_id = :KLSubRgn_id";
				$queryParams["KLSubRgn_id"] = $data["KLSubRgn_id"];
			}
			if (!empty($data["KLCity_id"])) {
				$filterArray[] = "CCC.KLCity_id = :KLCity_id";
				$queryParams["KLCity_id"] = $data["KLCity_id"];
			}
			if (!empty($data["KLTown_id"])) {
				$filterArray[] = "CCC.KLTown_id = :KLTown_id";
				$queryParams["KLTown_id"] = $data["KLTown_id"];
			}
			if (!empty($data["KLStreet_id"])) {
				$filterArray[] = "CCC.KLStreet_id = :KLStreet_id";
				$queryParams["KLStreet_id"] = $data["KLStreet_id"];
			}
			if (!empty($data["CmpCallCard_Dom"])) {
				$filterArray[] = "((CCC.CmpCallCard_Dom = :CmpCallCard_Dom) or (CCC.CmpCallCard_Dom is null))";
				$queryParams["CmpCallCard_Dom"] = $data["CmpCallCard_Dom"];
			}
			if (!empty($data["CmpCallCard_Kvar"])) {
				$filterArray[] = "CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar";
				$queryParams["CmpCallCard_Kvar"] = $data["CmpCallCard_Kvar"];
			} else {
				$filterArray[] = "CCC.CmpCallCard_Kvar is null";
			}
			if (!empty($data["CmpCallCard_Podz"])) {
				$filterArray[] = "((CCC.CmpCallCard_Podz = :CmpCallCard_Podz) or (CCC.CmpCallCard_Podz is null))";
				$queryParams["CmpCallCard_Podz"] = $data["CmpCallCard_Podz"];
			}
			if (!empty($data["CmpCallCard_Etaj"])) {
				$filterArray[] = "((CCC.CmpCallCard_Etaj = :CmpCallCard_Etaj) or (CCC.CmpCallCard_Etaj is null))";
				$queryParams["CmpCallCard_Etaj"] = $data["CmpCallCard_Etaj"];
			}
			$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
			$query = "
				select
					CCC.CmpCallCard_id as \"CallCard_id\",
				    coalesce(CCC.Person_SurName, '')||' '||coalesce(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
				    CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				    CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				    to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
				    rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
				    rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
				    case when RGN.KLRgn_FullName is not null then RGN.KLRgn_FullName||', ' else '' end +
						case when SRGN.KLSubRgn_FullName is not null then SRGN.KLSubRgn_FullName||', ' else ' г.'||City.KLCity_Name end||
						case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
						case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
						case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end||
						case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end||
						case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end
				    as \"Adress_Name\"
				from
					v_CmpCallCard CCC
					left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
					left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
					left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
					left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
					left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
					left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
					left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				{$whereString}
			";
			$result = $callObject->db->query($query, $queryParams);
			$addressResponse = $result->result("array");
		}
		//если не нашли по адресу, ищем по пациенту
		if (count($addressResponse) == 0) {
			$filterArray = ["(1 = 1)"];
			$queryParams = [];
			if (!empty($data["CmpCallCard_prmDate"])) {
				$queryParams["CmpCallCard_prmDT"] = $data["CmpCallCard_prmDate"];
				if (!empty($data["CmpCallCard_prmTime"])) {
					$queryParams["CmpCallCard_prmDT"] .= " " . $data["CmpCallCard_prmTime"] . ":00";
                    $filterArray[] = " and cast(CCC.CmpCallCard_prmDT as date) >= dateadd('day', -1, :CmpCallCard_prmDT)";
				}  else {
					$filterArray[] = " and cast(CCC.CmpCallCard_prmDT as date) = :CmpCallCard_prmDT";
				}
			}
			if (!empty($data["CmpCallCard_id"])) {
				$filterArray[] = "CCC.CmpCallCard_id != :CmpCallCard_id";
				$queryParams["CmpCallCard_id"] = $data["CmpCallCard_id"];
			}
			if (!empty($data["Person_SurName"])) {
				$filterArray[] = "(CCC.Person_SurName like :Person_SurName or CCC.Person_SurName is null)";
				$queryParams["Person_SurName"] = $data["Person_SurName"] . "%";
			}
			if (!empty($data["Person_FirName"])) {
				$filterArray[] = "(CCC.Person_FirName like :Person_FirName or CCC.Person_FirName is null)";
				$queryParams["Person_FirName"] = $data["Person_FirName"] . "%";
			}
			if (!empty($data["Person_SecName"])) {
				$filterArray[] = "(CCC.Person_SecName like :Person_SecName or CCC.Person_SecName is null)";
				$queryParams["Person_SecName"] = $data["Person_SecName"] . "%";
			}
			if (!empty($data["Person_BirthDay"])) {
				$filterArray[] = "(CCC.Person_BirthDay = :Person_BirthDay or CCC.Person_BirthDay is null)";
				$queryParams["Person_BirthDay"] = $data["Person_BirthDay"];
			}
			if (!empty($data["Person_Age"])) {
				$filterArray[] = "(CCC.Person_Age = :Person_Age or CCC.Person_Age is null)";
				$queryParams["Person_Age"] = $data["Person_Age"];
			}
			if (!empty($data["Sex_id"])) {
				$filterArray[] = "CCC.Sex_id = :Sex_id";
				$queryParams["Sex_id"] = $data["Sex_id"];
			}
			if (!empty($data["Person_PolisSer"])) {
				$filterArray[] = "(CCC.Person_PolisSer = :Person_PolisSer or CCC.Person_PolisSer is null)";
				$queryParams["Person_PolisSer"] = $data["Person_PolisSer"];
			}
			if (!empty($data["Person_PolisNum"])) {
				$filterArray[] = "(CCC.Person_PolisNum = :Person_PolisNum or CCC.Person_PolisNum is null)";
				$queryParams["Person_PolisNum"] = $data["Person_PolisNum"];
			}
			if (!empty($data["CmpCallCard_Numv"])) {
				$filterArray[] = "CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
				$queryParams["CmpCallCard_Numv"] = $data["CmpCallCard_Numv"];
			}
			if (!empty($data["CmpCallCard_Ngod"])) {
				$filterArray[] = "CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
				$queryParams["CmpCallCard_Ngod"] = $data["CmpCallCard_Ngod"];
			}
			$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
			$query = "
				select
					CCC.CmpCallCard_id as \"CallCard_id\",
				    coalesce(CCC.Person_SurName, '')||' '||coalesce(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
				    CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				    to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
				    rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
				    rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
				    case when RGN.KLRgn_FullName is not null then RGN.KLRgn_FullName||', ' else '' end||
						case when SRGN.KLSubRgn_FullName is not null then SRGN.KLSubRgn_FullName||', ' else ' г.'||City.KLCity_Name end||
						case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
						case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
						case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end||
						case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end||
						case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end
				    as \"Adress_Name\"
				from
					v_CmpCallCard CCC
					left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
					left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
					left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
					left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
					left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
					left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
					left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				{$whereString}
			";
			$result = $callObject->db->query($query, $queryParams);
			$personResponse = $result->result("array");
		}
		$response = [];
		if ($personResponse || $addressResponse) {
			$response = $personResponse ? $personResponse : $addressResponse;
		}
		if (count($response) == 0) {
			return false;
		}
		return ["data" => $response];
	}

	/**
	 * Поиск дублей по населенному пункту, улице, дому, квартире (за последние 24 часа)
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkDuplicateCmpCallCardByAddress(CmpCallCard_model $callObject, $data)
	{
		$filterArray = [];
		$queryParams = [];
		if (!empty($data["CmpCallCard_prmDate"])) {
			$queryParams["CmpCallCard_prmDT"] = $data["CmpCallCard_prmDate"];
			if (!empty($data["CmpCallCard_prmTime"])) {
				$queryParams["CmpCallCard_prmDT"] .= " " . $data["CmpCallCard_prmTime"] . ":00.000";
				$filterArray[] = "CCC.CmpCallCard_prmDT as datetime >= (:CmpCallCard_prmDT - (24||' hours')::interval)";
			}
		}
		if (!empty($data["CmpCallCard_id"])) {
			$queryParams["CmpCallCard_id"] = $data["CmpCallCard_id"];
			$filterArray[] = "CCC.CmpCallCard_id != :CmpCallCard_id";
		}
		if (!empty($data["UnformalizedAddressDirectory_id"]) && (int)$data["UnformalizedAddressDirectory_id"]) {
			// если искомый адрес - объект, то будем искать по его ID
			$arrFields = ["UnformalizedAddressDirectory_id"];
		} else {
			$arrFields = [
				"KLSubRgn_id",
				"KLCity_id",
				"KLTown_id",
				"KLStreet_id",
				"CmpCallCard_UlicSecond",
				"CmpCallCard_Dom",
				"CmpCallCard_Korp",
				"CmpCallCard_Kvar",
			];
		}
		foreach ($arrFields as $field) {
			if (!empty($data[$field])) {
				$filterArray[] = "CCC.$field = :$field";
				$queryParams[$field] = $data[$field];
			} else {
				$filterArray[] = "CCC.$field is null";
			}
		}
		$filterArray[] = "CTYP.CmpCallCardStatusType_Code not in (4,5,6,9)";
		$filterArray[] = "CCT.CmpCallType_Code in (1, 2, 4, 9)";
		$filterArray[] = "(C112.CmpCallCard112StatusType_id is null or C112.CmpCallCard112StatusType_id in (3,4,5))";
		$whereString = implode(" and ", $filterArray);
		$query = "
            select
            	PS.Person_id as \"Person_id\",
                PS.PersonEvn_id as \"PersonEvn_id\",
                coalesce(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\",
                coalesce(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\",
                coalesce(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\",
                to_char(coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
                RGN.KLRgn_id as \"KLRgn_id\",
                SRGN.KLSubRgn_id as \"KLSubRgn_id\",
                City.KLCity_id as \"KLCity_id\",
                coalesce(City.KLSocr_Nick||' '||City.KLCity_Name, '') as \"KLCity_Name\",
                Town.KLTown_id as \"KLTown_id\",
                coalesce(Town.KLSocr_Nick||' '||Town.KLTown_Name, '') as \"KLTown_Name\",
                Street.KLStreet_id as \"KLStreet_id\",
                Street.KLStreet_FullName as \"KLStreet_FullName\",
                CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\",
                CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\",
                CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
                CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\",
                CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\",
                CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
                CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
                CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\",
                CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
                CCC.CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
                CCC.Person_Age as \"Person_Age\",
                CCC.Sex_id as \"Sex_id\",
                CCC.CmpCallerType_id as \"CmpCallerType_id\",
                CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
                CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
                CCC.CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\",
                CCC.MedService_id as \"MedService_id\",
                UAD.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
                UAD.UnformalizedAddressType_id as \"UnformalizedAddressType_id\",
                UAD.UnformalizedAddressDirectory_Dom as \"UnformalizedAddressDirectory_Dom\",
                UAD.UnformalizedAddressDirectory_Name as \"UnformalizedAddressDirectory_Name\",
                case when coalesce(CCC.KLStreet_id, 0) = 0 then
                    case when coalesce(CCC.UnformalizedAddressDirectory_id, 0) = 0 then null
                    else 'UA.'||CCC.UnformalizedAddressDirectory_id::varchar end
                    else 'ST.'||CCC.KLStreet_id::varchar
                end as \"StreetAndUnformalizedAddressDirectory_id\",
                CCC.CmpLpu_id as \"lpuLocalCombo\",
                CCC.LpuBuilding_id as \"LpuBuilding_id\",
                CCC.CmpCallCard_id as \"CallCard_id\",
                to_char(CCCST_T.CmpCallCardStatus_insDT, '{$callObject->dateTimeForm113}') as \"CmpCallCardStatus_insDT\",
                to_char(CCC.CmpCallCard_Tper, '{$callObject->dateTimeForm113}') as \"CmpCallCard_Tper\",
                CCC.EmergencyTeam_id as \"EmergencyTeam_id\",
                coalesce(CCC.Person_SurName, '')||' '||coalesce(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
                CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
                CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
                to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
                rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
                CR.CmpReason_id as \"CmpReason_id\",
                rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
                overlay(
                    case when SRGN.KLSubRgn_FullName is not null then ', '||SRGN.KLSubRgn_FullName else coalesce(', г.'||City.KLCity_Name, '') end
                    ||coalesce(', '||Town.KLTown_FullName, '')
                    ||coalesce(', '||Street.KLStreet_FullName, '')
                    ||coalesce(', д.'||CCC.CmpCallCard_Dom, '')
                    ||coalesce(', к.'||CCC.CmpCallCard_Korp, '')
                    ||coalesce(', кв.'||CCC.CmpCallCard_Kvar, '')
                    ||coalesce(', комн.'||CCC.CmpCallCard_Comm, '')
                    ||coalesce(', место: '||UAD.UnformalizedAddressDirectory_Name, '') placing '' from 1 for 2
                ) as \"Adress_Name\"
            from
            	v_CmpCallCard CCC
                left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
                left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
                left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
                left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
                left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
                left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
                left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
                left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
                left join (
                	select
						min(CCCS.CmpCallCardStatus_insDT) as CmpCallCardStatus_insDT,
                        CCCS.CmpCallCard_id as CmpCallCard_id
                	from
                    	v_CmpCallCardStatus CCCS
                      	inner join v_CmpCallCardStatusType CCCST on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id
                      	left join v_CmpCallCard VCCC on CCCS.CmpCallCard_id = VCCC.CmpCallCard_id
                    where CCCST.CmpCallCardStatusType_Code = 2
                      and VCCC.Lpu_ppdid is not null
                    group by CCCS.CmpCallCard_id
                ) CCCST_T on CCCST_T.CmpCallCard_id = CCC.CmpCallCard_id
                left join v_PersonState PS on PS.Person_id = CCC.Person_id
                left join v_CmpCallCardLockList CCCLL on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id
                left join v_CmpCallCardStatusType CTYP on CCC.CmpCallCardStatusType_id = CTYP.CmpCallCardStatusType_id
                left join v_CmpCallCard112 C112 on CCC.CmpCallCard_id = C112.CmpCallCard_id
            where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return ["data" => $result->result("array")];
	}

	/**
	 * Поиск дублей по id пользователя (за последние 24 часа)
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkDuplicateCmpCallCardByFIO(CmpCallCard_model $callObject, $data)
	{
		$filterArray = [];
		$queryParams = [];
		if (!empty($data["CmpCallCard_prmDate"])) {
			$queryParams["CmpCallCard_prmDT"] = $data["CmpCallCard_prmDate"];
			if (!empty($data["CmpCallCard_prmTime"])) {
				$queryParams["CmpCallCard_prmDT"] .= " " . $data["CmpCallCard_prmTime"] . ":00.000";
				$filterArray[] = "CCC.CmpCallCard_prmDT as datetime >= (:CmpCallCard_prmDT - (1||' days')::interval)";
			}
		}
		if (!empty($data["CmpCallCard_id"])) {
			$queryParams["CmpCallCard_id"] = $data["CmpCallCard_id"];
			$filterArray[] = "CCC.CmpCallCard_id != :CmpCallCard_id";
		}
		$filterArray[] = "CTYP.CmpCallCardStatusType_Code not in (4,5,6,9)";
		$filterArray[] =" and CCT.CmpCallType_Code in (1, 2, 4, 9)";
		$filterArray[] = "(C112.CmpCallCard112StatusType_id is null or C112.CmpCallCard112StatusType_id in (3,4,5))";
		if (empty($data["Person_id"])) {
			return false;
		}
		$filterArray[] = "CCC.Person_id = :Person_id";
		$queryParams["Person_id"] = $data["Person_id"];
		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray):"";
		$query = "
			select
				PS.Person_id as \"Person_id\",
			    PS.PersonEvn_id as \"PersonEvn_id\",
			    coalesce(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\",
				coalesce(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\",
				coalesce(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\",
				to_char(coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
				RGN.KLRgn_id as \"KLRgn_id\",
			    SRGN.KLSubRgn_id as \"KLSubRgn_id\",
				City.KLCity_id as \"KLCity_id\",
				coalesce(City.KLSocr_Nick||' '||City.KLCity_Name, '') as \"KLCity_Name\",
			    Town.KLTown_id as \"KLTown_id\",
			    coalesce(Town.KLSocr_Nick||' '||Town.KLTown_Name, '') as \"KLTown_Name\",
			    Street.KLStreet_id as \"KLStreet_id\",
			    Street.KLStreet_FullName as \"KLStreet_FullName\",
				CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\",
			    CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\",
			    CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
				CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\",
			    CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\",
			    CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
			    CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
			    CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\",
			    CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
			    CCC.CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
			    CCC.MedService_id as \"MedService_id\",
				CCC.Person_Age as \"Person_Age\",
			    CCC.Sex_id as \"Sex_id\",
			    CCC.CmpCallerType_id as \"CmpCallerType_id\",
			    CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
			    CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
			    UAD.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
			    UAD.UnformalizedAddressType_id as \"UnformalizedAddressType_id\",
			    UAD.UnformalizedAddressDirectory_Dom as \"UnformalizedAddressDirectory_Dom\",
			    UAD.UnformalizedAddressDirectory_Name as \"UnformalizedAddressDirectory_Name\",
			    case when coalesce(CCC.KLStreet_id, 0) = 0 then
			    	case when coalesce(CCC.UnformalizedAddressDirectory_id, 0) = 0 then null
					else 'UA.'||CCC.UnformalizedAddressDirectory_id::varchar end
    				else 'ST.'||CCC.KLStreet_id::varchar
				end as \"StreetAndUnformalizedAddressDirectory_id\",
				CCC.CmpLpu_id as \"lpuLocalCombo\",
				CCC.LpuBuilding_id as \"LpuBuilding_id\",
				CCC.CmpCallCard_id as \"CallCard_id\",
				to_char(CCCST_T.CmpCallCardStatus_insDT, '{$callObject->dateTimeForm113}') as \"CmpCallCardStatus_insDT\",
				to_char(CCC.CmpCallCard_Tper, '{$callObject->dateTimeForm113}') as \"CmpCallCard_Tper\",
				CCC.EmergencyTeam_id as \"EmergencyTeam_id\",
				coalesce(CCC.Person_SurName, '')||' '||coalesce(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
				rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
				CR.CmpReason_id as \"CmpReason_id\",
				rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
				overlay(
				    case when SRGN.KLSubRgn_FullName is not null then ', '||SRGN.KLSubRgn_FullName else coalesce(', г.'||City.KLCity_Name, '') end
				    ||coalesce(', '||Town.KLTown_FullName, '')
				    ||coalesce(', '||Street.KLStreet_FullName, '')
				    ||coalesce(', д.'||CCC.CmpCallCard_Dom, '')
				    ||coalesce(', к.'||CCC.CmpCallCard_Korp, '')
				    ||coalesce(', кв.'||CCC.CmpCallCard_Kvar, '')
				    ||coalesce(', комн.'||CCC.CmpCallCard_Comm, '')
				    ||coalesce(', место: '||UAD.UnformalizedAddressDirectory_Name, '') placing '' from 1 for 2
				) as \"Adress_Name\"
			from
            	v_CmpCallCard CCC
            	left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
                    left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
                    left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
                    left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
                    left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
                    left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
                    left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
                    left join (
                    	select
                        	min(CCCS.CmpCallCardStatus_insDT) as CmpCallCardStatus_insDT,
                            CCCS.CmpCallCard_id as CmpCallCard_id
                    	from
                        	v_CmpCallCardStatus CCCS
                          	inner join v_CmpCallCardStatusType CCCST on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id
                          	left join v_CmpCallCard VCCC on CCCS.CmpCallCard_id = VCCC.CmpCallCard_id
                          	where CCCST.CmpCallCardStatusType_Code = 2
                              and VCCC.Lpu_ppdid is not null
                          	group by CCCS.CmpCallCard_id
                    ) CCCST_T on CCCST_T.CmpCallCard_id = CCC.CmpCallCard_id
                    left join v_PersonState PS on PS.Person_id = CCC.Person_id
                    left join v_CmpCallCardLockList CCCLL on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id
                    left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
                    left join v_CmpCallCardStatusType CTYP on CCC.CmpCallCardStatusType_id = CTYP.CmpCallCardStatusType_id
                    left join v_CmpCallCard112 C112 on CCC.CmpCallCard_id = C112.CmpCallCard_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return ["data" => $result->result("array")];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function checkEmergencyStandart(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				df.DiagFinance_id as \"DiagFinance_id\",
				df.DiagFinance_IsOms as \"DiagFinance_IsOms\",
				person.poliseIsOvertime as \"poliseIsOvertime\",
				case when (df.DiagFinance_IsOms = 2 and person.poliseIsOvertime = 2)
				    then 817
				    else case when (df.DiagFinance_IsOms = 2)
				        then null
				        else 818
				    end
				end as \"EmergencyStandart_Code\"
			from
				v_DiagFinance df				
				left join YesNo as IsOms on IsOms.YesNo_id = df.DiagFinance_IsOms
				left join lateral (
					select
						pls.Polis_begDate,
						pls.Polis_endDate,
						ps.Polis_id,
						case when (ps.Polis_id is not null)
						    then
								case when (((pls.Polis_begDate is null) or (pls.Polis_begDate <= tzgetdate())) and ((pls.Polis_endDate is null) or (pls.Polis_endDate >= tzgetdate())))
								    then 1
								    else 2
								end
							else 2
						end as poliseIsOvertime
					from
						v_PersonState as ps
						left join v_Polis as pls on pls.Polis_id = ps.Polis_id
					where ps.Person_id = :Person_id
					limit 1
				) as person on true
			where df.Diag_id = :Diag_id
			limit 1
		";
		$queryParams = [
			"Diag_id" => $data["Diag_id"],
			"Person_id" => $data["Person_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkLockCmpCallCard(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["CmpCallCard_id"]) || !isset($data["pmUser_id"])) {
			return false;
		}
		$query = "
			select distinct
				CCCLL.CmpCallCard_id as \"CmpCallCard_id\",
				CCCLL.CmpCallCardLockList_id as \"CmpCallCardLockList_id\",
				'' as \"Error_Msg\"
			from v_CmpCallCardLockList CCCLL
			where CCCLL.CmpCallCard_id = :CmpCallCard_id
			  and 60 - datediff('ss', CCCLL.CmpCallCardLockList_updDT, tzgetdate()) > 0
			  and CCCLL.pmUser_insID != :pmUser_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (is_object($result)) {
			return $result->result("array");
		}
		return false;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param null $Person_id
	 * @return bool|float|int|string|null
	 */
	public static function checkPersonCmpCallCard(CmpCallCard_model $callObject, $Person_id = null)
	{
		if (empty($Person_id)) {
			return null;
		}
		$query = "
			select CmpCallCard_id as \"CmpCallCard_id\"
			from v_CmpCallCard
			where Person_id = :Person_id
			limit 1
		";
		$queryParams = ["Person_id" => $Person_id];
		return $callObject->getFirstResultFromQuery($query, $queryParams, null);
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param null $Person_id
	 * @return bool|float|int|string|null
	 */
	public static function checkPersonCmpCloseCard(CmpCallCard_model $callObject, $Person_id = null)
	{
		if (empty($Person_id)) {
			return null;
		}
		$query = "
			select CmpCloseCard_id as \"CmpCloseCard_id\"
			from v_CmpCloseCard
			where Person_id = :Person_id
			limit 1
		";
		$queryParams = ["Person_id" => $Person_id];
		return $callObject->getFirstResultFromQuery($query, $queryParams, null);
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkRelated112Call(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["CmpCallCard_id"])) {
			return false;
		}
		$query = "
			select
				C112.CmpCallCard112_id as \"Card112_id\",
				C112.CmpCallCard112_Guid as \"Card112_Guid\",
				coalesce(rC112.CmpCallCard112_id, pC112.CmpCallCard112_id) as \"secondCard112_id\",
				coalesce(rC112.CmpCallCard112_Guid, pC112.CmpCallCard112_Guid) as \"secondCard112_Guid\",
				coalesce(rCST.CmpCallCardStatusType_Code, pCST.CmpCallCardStatusType_Code) as \"secondCardStatus_Code\",
			    rC.CmpCallCard_id as \"rCmpCallCard_id\",
			    pC.CmpCallCard_id as \"pCmpCallCard_id\"
			from
				v_CmpCallCard C
				LEFT JOIN v_CmpCallType CCT on CCT.CmpCallType_id = C.CmpCallType_id
				LEFT JOIN v_CmpCallCard112 C112 on C112.CmpCallCard_id = C.CmpCallCard_id
				LEFT JOIN v_CmpCallCard rC on (rC.CmpCallCard_id = C.CmpCallCard_rid and CCT.CmpCallType_Code in (1, 9) and C112.CmpCallCard_id is null)
				LEFT JOIN v_CmpCallCard112 rC112 on rC112.CmpCallCard_id = rC.CmpCallCard_id
				LEFT JOIN v_CmpCallCard pC on pC.CmpCallCard_rid = C.CmpCallCard_id
				LEFT JOIN v_CmpCallCard112 pC112 on pC112.CmpCallCard_id = pC.CmpCallCard_id
				LEFT JOIN v_CmpCallCardStatusType CST on CST.CmpCallCardStatusType_id = C.CmpCallCardStatusType_id
				LEFT JOIN v_CmpCallCardStatusType rCST on rCST.CmpCallCardStatusType_id = rC.CmpCallCardStatusType_id
				LEFT JOIN v_CmpCallCardStatusType pCST on pCST.CmpCallCardStatusType_id = pC.CmpCallCardStatusType_id
				LEFT JOIN v_CmpCallType pCCT on pCCT.CmpCallType_id = pC.CmpCallType_id
			where C.CmpCallCard_id = :CmpCallCard_id
			  and (
			      	C112.CmpCallCard112_id is not null or
			      	(pC112.CmpCallCard_id is not null and pCST.CmpCallCardStatusType_Code is null) or
			      	(pCST.CmpCallCardStatusType_Code <> 10 or pCCT.CmpCallType_Code = 17) or
			      	rC112.CmpCallCard112_id is not null
			      )
		";
		$queryParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		return $callObject->getFirstRowFromQuery($query, $queryParams);
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function checkRuleUniqueness(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["Lpu_id"]) || empty($data["CmpReason_id"]) || !isset($data["CmpCallPlaceType_Array"]) || !is_array($data["CmpCallPlaceType_Array"]) || sizeof($data["CmpCallPlaceType_Array"]) == 0) {
			throw new Exception("Проверка уникальости правила: Не указаны обязательные параметры");
		}
		$addWhere = "";
		$queryParams = [
			"CmpReason_id" => $data["CmpReason_id"],
			"CmpUrgencyAndProfileStandart_UntilAgeOf" => $data["CmpUrgencyAndProfileStandart_UntilAgeOf"],
			"Lpu_id" => $data["Lpu_id"],
		];
		$editRuleClause = "";
		//В случае редактирования, а не добавления правила, необходимо исключить из выборки конфликтных правил редактируемое правило
		if (!empty($data["CmpUrgencyAndProfileStandart_id"])) {
			$editRuleClause = "and CUPS.CmpUrgencyAndProfileStandart_id != :CmpUrgencyAndProfileStandart_id";
			$queryParams["CmpUrgencyAndProfileStandart_id"] = $data["CmpUrgencyAndProfileStandart_id"];
		}
		foreach ($data["CmpCallPlaceType_Array"] as $key => $value) {
			$addWhere .= " or CUPSRF.CmpCallPlaceType_id = :CmpCallPlaceType_$key";
			$queryParams["CmpCallPlaceType_$key"] = $value["CmpCallPlaceType_id"];
		}
		$query = "
			select
				CUPS.CmpUrgencyAndProfileStandart_id as \"CmpUrgencyAndProfileStandart_id\",
				CUPSRF.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf as \"CmpUrgencyAndProfileStandart_UntilAgeOf\"
			from
				v_CmpUrgencyAndProfileStandartRefPlace CUPSRF
				left join v_CmpUrgencyAndProfileStandart CUPS on CUPSRF.CmpUrgencyAndProfileStandart_id = CUPS.CmpUrgencyAndProfileStandart_id
			where CUPS.CmpReason_id = :CmpReason_id
			  and CUPS.Lpu_id = :Lpu_id
			  and coalesce(CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf, 0) = coalesce(:CmpUrgencyAndProfileStandart_UntilAgeOf, 0)
			  and ((1=0) $addWhere)
			  $editRuleClause
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function checkSendReactionToActiveMQ(CmpCallCard_model $callObject, $data)
	{
		$related112Call = $callObject->checkRelated112Call($data);
		if (empty($related112Call["Card112_id"]) && empty($related112Call["secondCard112_id"])) {
			return false;
		}
		if (empty($data["EmergencyTeamStatus_id"]) && isset($data["EmergencyTeamStatus_Code"])) {
			$callObject->load->model("EmergencyTeam_model4E", "ETModel");
			$data["EmergencyTeamStatus_id"] = $callObject->ETModel->getEmergencyTeamStatusIdByCode($data["EmergencyTeamStatus_Code"]);
		}
		//События смены статуса бригады
		if (!empty($data["EmergencyTeamStatus_id"]) && !empty($data["EmergencyTeam_id"])) {
			$query = "
				select
					ets.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
					ets.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\",
					ets.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
					et.EmergencyTeam_id as \"EmergencyTeam_id\",
					et.EmergencyTeam_Num as \"EmergencyTeam_Num\",
					replace(to_char(tzgetdate(), '{$callObject->dateTimeForm126}'), ' ', '') as \"EmergencyTeamStatusHistory_insDT\",
					mp.MedPersonal_TabCode as \"MedPersonal_TabCode\"
				from
					v_EmergencyTeamStatus ets
					inner join v_CmpCallCard ccc on ccc.CmpCallCard_id = :CmpCallCard_id
					left join v_EmergencyTeam et on et.EmergencyTeam_id = :EmergencyTeam_id
					left join v_pmUserCache puc on puc.pmUser_id = :pmUser_id
					left join v_MedPersonal mp on mp.MedPersonal_id = puc.MedPersonal_id
					left join lateral (
						select
							coalesce(MP_HS.Person_Fin||', ', '')||
							coalesce(MP_A1.Person_Fin||', ', '')||
							coalesce(MP_A2.Person_Fin||', ', '')||
							coalesce(MP_D.Person_Fin||', ', '') as Membership
						from
							v_MedPersonal MP_HS
							left join v_MedPersonal MP_A1 on ET.EmergencyTeam_Assistant1 = MP_A1.MedPersonal_id
							left join v_MedPersonal MP_A2 on ET.EmergencyTeam_Assistant2 = MP_A2.MedPersonal_id
							left join v_MedPersonal MP_D on ET.EmergencyTeam_Driver = MP_D.MedPersonal_id
						where ET.EmergencyTeam_HeadShift = MP_HS.MedPersonal_id
					) as mbs on true
				where ets.EmergencyTeamStatus_id = :EmergencyTeamStatus_id
				limit 1
			";
			$queryParams = [
				"EmergencyTeamStatus_id" => $data["EmergencyTeamStatus_id"],
				"EmergencyTeam_id" => $data["EmergencyTeam_id"],
				"CmpCallCard_id" => $data["CmpCallCard_id"],
				"pmUser_id" => $_SESSION["pmuser_id"]
			];
			$resp_ets = $callObject->queryResult($query, $queryParams);
			if (!empty($resp_ets[0])) {
				switch ($resp_ets[0]["EmergencyTeamStatus_Code"]) {
					case 36:
						$ActionType = "Notification";
						$Remark = "Назначена бригада";
						$reactionType = "add";
						break;
					case 1:
						$ActionType = "Departure";
						$Remark = "Выезд бригады на вызов";
						$reactionType = "add";
						break;
					case 2:
						$ActionType = "Arrival";
						$Remark = "Прибытие бригады на вызов";
						$reactionType = "add";
						break;
					case 4:
						if (isset($related112Call["secondCardStatus_Code"]) && !in_array($related112Call["secondCardStatus_Code"], [4, 5, 6, 9])) {
							//если нет связанного с Карточкой 112 вызова который еще НЕ обслужен.
							$ActionType = "Solution";
							$Remark = "Продолжение обслуживания вызова спецбригадой";
							$reactionType = "add";
						} else {
							//в таком случае отправляем FinishReaction
							$reactionParams = [
								"Card112_Guid" => $related112Call["Card112_Guid"] ? $related112Call["Card112_Guid"] : $related112Call["secondCard112_Guid"],
								"EmergencyTeam_id" => $data["EmergencyTeam_id"],
								"EmergencyTeam_Num" => $resp_ets[0]["EmergencyTeam_Num"],
								"operator" => $resp_ets[0]["MedPersonal_TabCode"],
								"EmergencyTeamStatusHistory_insDT" => $resp_ets[0]["EmergencyTeamStatusHistory_insDT"],
								"CmpCallCard_id" => $data["CmpCallCard_id"],
							];
							$reactionType = "finish";
						}
						break;
				}
				if (isset($ActionType) && isset($Remark)) {
					$reactionParams = [
						"CmpCallCard_id" => $data["CmpCallCard_id"],
						"Card112_Guid" => $related112Call["Card112_Guid"] ? $related112Call["Card112_Guid"] : $related112Call["secondCard112_Guid"],
						"EmergencyTeamStatus_id" => $resp_ets[0]["EmergencyTeamStatus_id"],
						"EmergencyTeamStatus_Name" => $resp_ets[0]["EmergencyTeamStatus_Name"],
						"actionType" => $ActionType,
						"remark" => $Remark,
						"EmergencyTeam_id" => $resp_ets[0]["EmergencyTeam_id"],
						"EmergencyTeam_Num" => $resp_ets[0]["EmergencyTeam_Num"],
						"EmergencyTeamStatusHistory_insDT" => $resp_ets[0]["EmergencyTeamStatusHistory_insDT"],
						"MedPersonal_TabCode" => $resp_ets[0]["MedPersonal_TabCode"]
					];
				}
			}
		} else {
			//События смены статуса вызова
			$query = "
				select
					ccc.Lpu_ppdid as \"Lpu_ppdid\",
					ccc.EmergencyTeam_id as \"EmergencyTeam_id\",
					st.CmpCallCardStatusType_Code as \"CmpCallCardStatusType_Code\",
					ct.CmpCallType_Code as \"CmpCallType_Code\",
					l.lpu_Nick as \"lpuNmp_Nick\",
					mp.MedPersonal_TabCode as \"MedPersonal_TabCode\",
					replace(to_char(tzgetdate(), '{$callObject->dateTimeForm126}'), ' ', '') as \"EmergencyTeamStatusHistory_insDT\"
				from
					v_CmpCallCard ccc
					left join v_CmpCallCardStatusType st on st.CmpCallCardStatusType_id = ccc.CmpCallCardStatusType_id
					left join v_CmpCallType ct on ct.CmpCallType_id = ccc.CmpCallType_id
					left join v_Lpu l on l.Lpu_id = ccc.lpu_ppdid
					left join v_pmUserCache puc on puc.pmUser_id = :pmUser_id
					left join v_MedPersonal mp on mp.MedPersonal_id = puc.MedPersonal_id
				where ccc.CmpCallCard_id = :CmpCallCard_id
				limit 1
			";
			$queryParams = [
				"CmpCallCard_id" => $data["CmpCallCard_id"],
				"pmUser_id" => $_SESSION["pmuser_id"]
			];
			$resp_ets = $callObject->queryResult($query, $queryParams);
			$callObject->load->model("Options_model", "opmodel");
			$o = $callObject->opmodel->getOptionsGlobals(getSessionParams());
			$g_options = $o["globals"];
			//События смены статуса вызова в НМП
			if (!empty($resp_ets[0]["Lpu_ppdid"]) && empty($resp_ets[0]["EmergencyTeam_id"])) {
				switch ($resp_ets[0]["CmpCallCardStatusType_Code"]) {
					case 1:
						$ActionType = (!empty($g_options["smp_default_system112"]) && $g_options["smp_default_system112"] == 2) ? "Departure" : "Notification";
						$Remark = "Передан на обслуживание в службу НМП";
						$reactionType = "add";
						break;
					case 2:
						$ActionType = (!empty($g_options["smp_default_system112"]) && $g_options["smp_default_system112"] == 2) ? "Arrival" : "Solution";
						$Remark = "Подтвержен прием вызова службой НМП».";
						$reactionType = "add";
						break;
					case 4:
						$reactionParams = [
							"Card112_Guid" => $related112Call["Card112_Guid"] ? $related112Call["Card112_Guid"] : $related112Call["secondCard112_Guid"],
							"operator" => $resp_ets[0]["MedPersonal_TabCode"],
							"CmpCallCard_id" => $data["CmpCallCard_id"],
						];
						$reactionType = "finish";
						break;
				}
				if (isset($ActionType) && isset($Remark)) {
					$reactionParams = [
						"CmpCallCard_id" => $data["CmpCallCard_id"],
						"Card112_Guid" => $related112Call["Card112_Guid"] ? $related112Call["Card112_Guid"] : $related112Call["secondCard112_Guid"],
						"actionType" => $ActionType,
						"remark" => $Remark,
						"lpuNmp_Nick" => $resp_ets[0]["lpuNmp_Nick"],
						"Lpu_ppdid" => $resp_ets[0]["Lpu_ppdid"],
						"MedPersonal_TabCode" => $resp_ets[0]["MedPersonal_TabCode"],
						"EmergencyTeamStatusHistory_insDT" => $resp_ets[0]["EmergencyTeamStatusHistory_insDT"] //отправим текущую дату т.к поле обязательное
					];
				}
			}
			//Общие события с вызовом
			switch ($resp_ets[0]["CmpCallCardStatusType_Code"]) {
				case 1:
					if (!empty($g_options["smp_default_system112"]) && $g_options["smp_default_system112"] == 2) {
						//только для "ЕДДС-ПРОТЕЙ"
						$reactionParams = [
							"CmpCallCard_id" => $data["CmpCallCard_id"],
							"Card112_Guid" => $related112Call["Card112_Guid"] ? $related112Call["Card112_Guid"] : $related112Call["secondCard112_Guid"],
							"actionType" => "Notification",
							"remark" => "Передан из 112",
							"MedPersonal_TabCode" => $resp_ets[0]["MedPersonal_TabCode"],
							"EmergencyTeamStatusHistory_insDT" => $resp_ets[0]["EmergencyTeamStatusHistory_insDT"] //отправим текущую дату т.к поле обязательное
						];
						$reactionType = "add";
					}
					break;
				case 5:
					//Отказ от вызова
					$reactionParams = [
						"Card112_Guid" => $related112Call["Card112_Guid"] ? $related112Call["Card112_Guid"] : $related112Call["secondCard112_Guid"],
						"operator" => $resp_ets[0]["MedPersonal_TabCode"],
						"CmpCallCard_id" => $data["CmpCallCard_id"],
					];
					$reactionType = "finish";
					break;
				case 9:
					//При дубле необходимо отправить реагирование на последнее событие первичного вызова
					//Получим последнее событие первичного
					$query = "
						select
							C.CmpCallCard_id as \"CmpCallCard_id\",
							ETSH.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
							ETSH.EmergencyTeam_id as \"EmergencyTeam_id\"
						from
							v_CmpCallCard C
							inner join v_CmpCallCard rC on rC.CmpCallCard_rid = C.CmpCallCard_id
							left join v_CmpCallCardEvent CE on C.CmpCallCard_id = CE.CmpCallCard_id
							left join v_EmergencyTeamStatusHistory ETSH on CE.EmergencyTeamStatusHistory_id = ETSH.EmergencyTeamStatusHistory_id
						where rC.CmpCallCard_id = :CmpCallCard_id
						order by CE.CmpCallCardEvent_updDT desc
						limit 1
					";
					$queryParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
					$event = $callObject->queryResult($query, $queryParams);
					if (count($event) > 0) {
						//Вызовем метод проверки отправки реагирования на последнее событие
						$callObject->checkSendReactionToActiveMQ([
							"CmpCallCard_id" => $event[0]["CmpCallCard_id"],
							"EmergencyTeamStatus_id" => $event[0]["EmergencyTeamStatus_id"],
							"EmergencyTeam_id" => $event[0]["EmergencyTeam_id"],
							"duplicate" => $data["CmpCallCard_id"]
						]);
					}
					break;
			}
			if (!empty($data["resetTeam"]) && $data["resetTeam"] === true) {
				//Отдельное условие для отклонения бригады
				//Название бригады
				$team = $callObject->getFirstRowFromQuery("select EmergencyTeam_id,EmergencyTeam_Num from v_EmergencyTeam where EmergencyTeam_id = :EmergencyTeam_id", $data);
				//статус Свободно
				$status = $callObject->getFirstRowFromQuery("select EmergencyTeamStatus_id,EmergencyTeamStatus_Name from v_EmergencyTeamStatus where EmergencyTeamStatus_Code = 13");
				$reactionParams = [
					"CmpCallCard_id" => $data["CmpCallCard_id"],
					"Card112_Guid" => $related112Call["Card112_Guid"] ? $related112Call["Card112_Guid"] : $related112Call["secondCard112_Guid"],
					"EmergencyTeamStatus_id" => $status["EmergencyTeamStatus_id"],
					"EmergencyTeamStatus_Name" => $status["EmergencyTeamStatus_Name"],
					"actionType" => "Solution",
					"remark" => "Отклонение бригады с вызова",
					"EmergencyTeam_id" => $team["EmergencyTeam_id"],
					"EmergencyTeam_Num" => $team["EmergencyTeam_Num"],
					"MedPersonal_TabCode" => $resp_ets[0]["MedPersonal_TabCode"],
					"EmergencyTeamStatusHistory_insDT" => $resp_ets[0]["EmergencyTeamStatusHistory_insDT"] //отправим текущую дату т.к поле обязательное
				];
				$reactionType = "add";
			}
		}
		if (!isset($reactionParams) || !isset($reactionType)) { return false;}
		//Фильтр на случай рекурсивного вызова метода для конкретного дубля (не отправляем реагирование повторно для других дублей)
		$dupFilter = "";
		if (isset($data["duplicate"])) {
			$dupFilter = " and C.CmpCallCard_id = :CmpCallCard_id";
		}
		//Проверим на количество дублирующих (и отменяющих) вызовов из 112
		$query = "
			select C112.CmpCallCard112_Guid as \"Card112_Guid\"
			from
				v_CmpCallCard C
				left join v_CmpCallCard112 C112 on C112.CmpCallCard_id = C.CmpCallCard_id
				left join v_CmpCallCardStatusType CST on CST.CmpCallCardStatusType_id = C.CmpCallCardStatusType_id
			where C.CmpCallCard_rid = :CmpCallCard_rid
			  and C112.CmpCallCard112_id is not null
			  and CST.CmpCallCardStatusType_Code <> 10
			  {$dupFilter}
		";
		$queryParams = [
			"CmpCallCard_rid" => $data["CmpCallCard_id"],
			"CmpCallCard_id" => isset($data["duplicate"]) ? $data["duplicate"] : null,
		];
		$Duplicates = $callObject->queryResult($query, $queryParams);
		//Если есть дубли то отправляем реагирование для каждого
		if (count($Duplicates) > 0) {
			foreach ($Duplicates as $dup) {
				$reactionParams["Card112_Guid"] = $dup["Card112_Guid"];
				$callObject->sendReactionToActiveMQ($reactionParams, $reactionType);
			}
			//Если первичный тоже из 112 (но не отправляем повторно при регистрации дубля)
			if (!empty($related112Call["Card112_Guid"]) && !isset($data["duplicate"])) {
				$reactionParams["Card112_Guid"] = $related112Call["Card112_Guid"];
				$callObject->sendReactionToActiveMQ($reactionParams, $reactionType);
			}
		} else {
			$callObject->sendReactionToActiveMQ($reactionParams, $reactionType);
			//В случае когда и первичный и потомок из 112 отправляем второе реагирование с guid потомка
			if (!empty($related112Call["Card112_Guid"]) && !empty($related112Call["secondCard112_Guid"]) && $related112Call["secondCardStatus_Code"] != 10) {
				$reactionParams["Card112_Guid"] = $related112Call["secondCard112_Guid"];
				$callObject->sendReactionToActiveMQ($reactionParams, $reactionType);
			}
		}
		return true;
	}
}