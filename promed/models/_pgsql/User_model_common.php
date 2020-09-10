<?php

class User_model_common
{
	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function addGroupLink(User_model $callObject, $data)
	{
		$params = [
			"pmUserCache_id" => $data["id"],
			"pmUserCacheGroup_id" => $data["group"],
			"pmUser_id" => pmAuthUser::find($data["session"]["login"])->pmuser_id
		];
		$query = "
			select
			    pmusercachegrouplink_id as \"pmUserCacheGroup_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_pmusercachegrouplink_ins(
			    pmusercachegroup_id := :pmUserCacheGroup_id,
			    pmusercache_id := :pmUserCache_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function removeGroupLink(User_model $callObject, $data)
	{
		$params = ["PMUser_id" => $data["id"]];
		$query = "
			delete from pmUserCacheGroupLink where pmUserCache_id = :PMUser_id
		";
		$callObject->db->query($query, $params);
		return [["success" => true, "Error_Msg" => ""]];
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function blockUsers(User_model $callObject, $data)
	{
		$params = ["pmUser_Blocked" => $data["pmUser_Blocked"] ? 1 : 0];
		$pmuser_ids = json_decode($data["pmUser_ids"], true);
		if (count($pmuser_ids) == 0) {
			throw new Exception("Не были переданы идентификаторы пользователей");
		}
		$pmuser_idsString = implode(",", $pmuser_ids);
		$query = "
			update pmUserCache
			set pmUser_Blocked = :pmUser_Blocked
			where pmUser_id in ({$pmuser_idsString})
		";
		$callObject->db->query($query, $params);
		return [["success" => true, "Error_Msg" => ""]];
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function changePassword(User_model $callObject, $data)
	{
		if (!empty($data["session"]["login"])) {
			$user = pmAuthUser::find($data["session"]["login"]);
			if (empty($user)) {
				throw new Exception("Пользователь не найден");
			}
			if ("{MD5}" . base64_encode(md5($data["old_password"], TRUE)) != $user->pass) {
				throw new Exception("Старый пароль введён неверно");
			}
			if ($data["new_password"] != $data["new_password_two"]) {
				throw new Exception("Пароли не совпадают");
			}
			$check = $callObject->checkPassword($data["new_password"], $data["old_password"], $user);
			if (!empty($check["Error_Msg"])) {
				return $check;
			}
			$user->pass = "{MD5}" . base64_encode(md5($data["new_password"], TRUE));
			$user->password_temp = 0;
			$user->password_date = time();
			$user->post();
			return ["Error_Msg" => ""];
		}
		throw new Exception("Ошибка доступа");
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function checkExistUserInOrg(User_model $callObject, $data)
	{
		$query = "
			select pmUser_id as \"pmUser_id\"
			from
				pmUserCache puc
				inner join pmUserCacheOrg puco on puco.pmUserCache_id = puc.pmUser_id
			where puco.Org_id = :Org_id
			  and coalesce(puc.pmUser_deleted, 1) = 1
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result("array");
		return (count($resp) > 0) ? true : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @param $pmuser_id
	 * @return array
	 * @throws Exception
	 */
	public static function checkLoginDuplicate(User_model $callObject, $data, $pmuser_id)
	{
		global $config;
		$dupl_login = $callObject->config->item("DUPL_LOGIN_DISABLED");
		if (!empty($config["session_driver"]) && $config["session_driver"] == "mongodb" && $dupl_login) {
			// тянем все активные сессии из монго, по каждой разбираем сессию, фильтруем, считаем кол-во юзеров по АРМам.
			switch (checkMongoDb()) {
				case "mongo":
					$callObject->load->library("swMongodb", ["config_file" => "mongodbsessions"], "swmongodb");
					break;
				case "mongodb":
					$callObject->load->library("swMongodbPHP7", ["config_file" => "mongodbsessions"], "swmongodb");
					break;
			}
			$table = (isset($config["mongodb_session_settings"]) && isset($config["mongodb_session_settings"]["table"])) ? $config["mongodb_session_settings"]["table"] : "Session";
			$wheres = ["logged" => 1];
			if (!empty($pmuser_id) && is_numeric($pmuser_id)) {
				$wheres["pmuser_id"] = $pmuser_id;
			}
			$items = $callObject->swmongodb->where_gt("updated", time() - 7200)->where($wheres)->get($table); // только залогиненные и активные последние 2 часа
			if (!empty($items) && count($items) > 0) {
				throw new Exception("<br>Пользователь уже выполнил вход в систему,<br>авторизация под данной учетной записью недоступна.");
			}
		}
		// заглушка
		return [];
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function checkShownMsgArms(User_model $callObject, $data)
	{
		if (!empty($data["session"]["login"])) {
			$res = array("Error_Msg" => "");
			$user = pmAuthUser::find($data["session"]["login"]);
			if (empty($user)) {
				throw new Exception("Пользователь не найден");
			}
			if (empty($_SESSION["CurARM"]) && empty($data["curARMType"])) {
				throw new Exception("Арм не определён");
			}
			$date = date("Y-m-d");
			$currArm = $_SESSION["CurARM"];
			$add_arm = (!empty($currArm) && $currArm["ARMType"]) ? $currArm["ARMType"] : $data["curARMType"];
			$shown_armlist = [];
			if (!empty($user->shown_armlist)) {
				$saved_shown_armlist = json_decode($user->shown_armlist, true);
				if (is_array($saved_shown_armlist)) {
					$shown_armlist = $saved_shown_armlist;
				}
			}
			if (!empty($shown_armlist["Date"]) && $shown_armlist["Date"] != $date && !empty($shown_armlist["Arms"])) {
				unset($shown_armlist["Arms"]);
			}
			$shown_armlist["Date"] = $date;
			if (empty($shown_armlist["Arms"])) {
				$shown_armlist["Arms"] = [];
			}
			if (!empty($add_arm) && !in_array($add_arm, $shown_armlist["Arms"])) {
				array_push($shown_armlist["Arms"], $add_arm);
				$res["showMsg"] = true;
				$user->shown_armlist = json_encode($shown_armlist);
				$user->post();
			}
			return $res;
		}
		throw new Exception("Ошибка доступа");
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function checkOnIssetReportARM(User_model $callObject, &$data)
	{
		$query = "
			select ReportARM_id as \"ReportARM_id\"
			from rpt.v_ReportARM
			where ARMType_id = :ARMType_id
			  and Report_id = :Report_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (isset($result[0]) && $result[0]["ReportARM_id"] > 0) {
			$data["ReportARM_id"] = $result[0]["ReportARM_id"];
		}
		return count($result) >= 1;
	}

	/**
	 * @param User_model $callObject
	 * @param $new_password
	 * @param $old_password
	 * @param $user
	 * @return array
	 * @throws Exception
	 */
	public static function checkPassword(User_model $callObject, $new_password, $old_password, &$user)
	{
		// Новый пароль соответствует указанным требованиям
		$callObject->load->model("Options_model");
		$options = $callObject->Options_model->getOptionsGlobals(["session" => ["login" => ""]]);
		$minLength = 6;
		if (!empty($options["globals"]["password_minlength"])) {
			$minLength = intval($options["globals"]["password_minlength"]);
		}
		if (mb_strlen($new_password) < $minLength) {
			throw new Exception("Длина пароля должна быть не менее {$minLength} символов");
		}
		if (!empty($options["globals"]["password_haslowercase"])) {
			if (preg_match("/[a-zа-я]/u", $new_password) == false) {
				throw new Exception("Пароль должен содержать хотя бы одну строчную букву");
			}
		}
		if (!empty($options["globals"]["password_hasuppercase"])) {
			if (preg_match("/[A-ZА-Я]/u", $new_password) == false) {
				throw new Exception("Пароль должен содержать хотя бы одну прописную букву");
			}
		}
		if (!empty($options["globals"]["password_hasnumber"])) {
			if (preg_match("/[0-9]/u", $new_password) == false) {
				throw new Exception("Пароль должен содержать хотя бы одну цифру");
			}
		}
		if (!empty($options["globals"]["password_hasspec"])) {
			if (preg_match("/[^A-Z^А-Я^a-z^а-я^0-9]/u", $new_password) == false) {
				throw new Exception("Пароль должен содержать хотя бы один спецсимвол");
			}
		}
		// Новый пароль отличается от старого на количество символов (>=), указанного в параметрах системы
		$minDiff = 1;
		if (!empty($options["globals"]["password_mindifference"])) {
			$minDiff = intval($options["globals"]["password_mindifference"]);
		}
		if (!empty($old_password)) {
			$diff = 0;
			for ($i = 0; $i < mb_strlen($new_password); $i++) {
				$o = $callObject->char($old_password, $i);
				$n = $callObject->char($new_password, $i);
				if (empty($o) || mb_strtolower($n) != mb_strtolower($o)) {
					$diff++;
				}
				if (mb_strlen($new_password) < mb_strlen($old_password)) {
					$diff += mb_strlen($old_password) - mb_strlen($new_password);
				}
			}
			if ($diff < $minDiff) {
				throw new Exception("Новый пароль должен отличаться от старого на {$minDiff} " . ru_word_case("символ", "символа", "символов", $minDiff));
			}
		}
		$password_last = [];
		if (!empty($user->password_last)) {
			$passwordLast = json_decode($user->password_last);
			if (is_array($passwordLast)) {
				$password_last = $passwordLast;
			}
		}
		if (!empty($user->password_temp) && $user->password_temp != 0){
			array_push($password_last, $user->password_temp);
		}

		$error = "Новый пароль не должен совпадать с одним из предыдущих.";
		$pass = "{MD5}" . base64_encode(md5($new_password, TRUE));
		$checkAllPasswords = $callObject->getFirstResultFromQuery("select DataStorage_Value from v_DataStorage DS where DS.DataStorage_Name = 'check_passwords_all' and DS.DataStorage_Value = '1' limit 1");

		if (getRegionNick() == 'kz' || $checkAllPasswords) {
			if (in_array($pass, $password_last)) {
				return array('Error_Msg' => $error);
			}
		} else {
			$countCheckPasswords = $callObject->getFirstResultFromQuery("select DataStorage_Value from v_DataStorage DS where DS.DataStorage_Name = 'count_check_passwords' limit 1");
			if ($countCheckPasswords != '') {
				$tmp_passwords = array_slice($password_last, '-'.$countCheckPasswords);
				if (in_array($pass, $tmp_passwords)) {
					return array('Error_Msg' => $error);
				}
			}
		}

		array_push($password_last, $pass);
		if (count($password_last) > 4) {
			// выкидываем первый
			array_shift($password_last);
		}
		$user->password_last = json_encode($password_last);
		return ["Error_Msg" => ""];
	}

	/**
	 * @param User_model $callObject
	 * @param $time
	 * @param int $temp
	 * @return array
	 * @throws Exception
	 */
	public static function checkPasswordDate(User_model $callObject, $time, $temp = 0)
	{
		// Новый пароль соответствует указанным требованиям
		$callObject->load->model("Options_model");
		$options = $callObject->Options_model->getOptionsGlobals(["session" => ["login" => ""]]);
		if ($temp == 1) {
			if (!empty($options["globals"]["password_tempexpirationperiod"])) {
				$days = intval($options["globals"]["password_tempexpirationperiod"]);
				$secs = $days * 24 * 60 * 60;
				if ($time + $secs - time() <= 0) {
					throw new Exception("Срок временного пароля истек. Обратитесь к администратору.");
				}
			}
		} else {
			if (!empty($options["globals"]["password_expirationperiod"])) {
				$days = intval($options["globals"]["password_expirationperiod"]);
				$secs = $days * 24 * 60 * 60;
				if ($time + $secs - time() <= 0) {
					throw new Exception("Срок действия пароля истек. Необходимо ввести новый пароль.");
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function checkSaveGroupDB(User_model $callObject, $data)
	{
		$query = "
			select pmUserCacheGroup_id as \"pmUserCacheGroup_id\"
			from pmUserCacheGroup 
			where pmUserCacheGroup_SysNick = :pmUserCacheGroup_SysNick
			  and pmUserCacheGroup_id != coalesce(:pmUserCacheGroup_id, 0)
		";
		$params = [
			"pmUserCacheGroup_SysNick" => $data["Group_Code"],
			"pmUserCacheGroup_id" => $data["pmUserCacheGroup_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 */
	public static function createGroupFromFlag(User_model $callObject, $data)
	{
		set_time_limit(0);
		$i = 0;
		// запрос на выборку пользователей из LDAP
		$ldap_users = new pmAuthUsers("(&(medsvidgrantadd=1)(organizationalstatus=1))");
		foreach ($ldap_users->users as $user) {
			if ($user) {
				$query = "
					select 1 as ex
					from pmusercache
					where pmUser_groups ilike '%MedSvidDeath%'
					  and pmUser_id = :pmUser_id
					limit 1
				";
				$queryParams = ["pmUser_id" => $user->pmuser_id];
				$existGroup = $callObject->getFirstResultFromQuery($query, $queryParams);
				if (!$existGroup) {
					$i++;
					echo "{$i} | User id = {$user->pmuser_id}, login = {$user->login}, name = " . toAnsi($user->surname . " " . $user->firname);
					$callObject->textlog->add("{$i} | User id = {$user->pmuser_id}, login = {$user->login}, name = " . toAnsi($user->surname . " " . $user->firname));
					$user->addGroup("MedSvidDeath");
					// просто сохраняем новый атрибут
					foreach (array_values($user->groups) as $group) {
						if ($group->name == "MedSvidDeath") {
							ldap_insertattr($group->id, ["member" => $user->id]);
						}
					}
					// Перекешируем
					$callObject->ReCacheUserData($user);
					echo "группа MedSvidDeath успешно добавлена<br/>";
				}
			}
		}
		$msg = "Всего обработано {$i} записей, выполнение успешно завершено.<br/>";
		$callObject->textlog->add($msg);
		echo $msg;
	}

	/**
	 * @param User_model $callObject
	 * @param $res
	 * @param null $groups
	 * @return array|mixed|string
	 */
	public static function defineARMType(User_model $callObject, $res, $groups = null)
	{
		$LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS = $callObject->config->item("LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS");
		$ALLOW_EXTJS6_ARMS_FOR_ALL = $callObject->config->item('ALLOW_EXTJS6_ARMS_FOR_ALL');

		if (!is_array($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS)) {
			$LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS = [];
		}
		// Проверка на АРМы служб
		if (isset($res["MedServiceType_id"]) && ($res["MedServiceType_id"] > 0)) {
			switch ($res["MedServiceType_id"]) {
				case 11:
					return (havingGroup("lpucadradmin", $groups)) ? "lpucadradmin" : "lpucadrview";
					break;
				case 16:
					$return_array = [];
					$return_array[] = $res["MedServiceType_SysNick"];
					if (!empty($res["Lpu_id"]) && (!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res["Lpu_id"], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
						$return_array[] = "regpol6";
					}
					return $return_array;
					break;
				case 18:
					$return_array = [];
					if (havingGroup("ppdmedserviceoper", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"];
					}
					if (havingGroup("dispnmp", $groups)) {
						$return_array[] = "dispnmp";
					}
					return (sizeof($return_array) == 1) ? $return_array[0] : $return_array;
					break;
				case 19:
					$return_array = [];
					if (havingGroup("smpmedserviceoper", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "reg";
					}
					if (havingGroup("smpdispatchdirections", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "dispatchdirect";
					}
					if (havingGroup("smpcalldispath", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "dispatchcall";
					}
					if (havingGroup("smpinteractivemap", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "interactivemap";
					}
					if (havingGroup("smpadmin", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "admin";
					}
					if (havingGroup("smpheadduty", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "headduty";
					}
					if (havingGroup("smpheadbrig", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "headbrig";
					}
					if (havingGroup("smpdispatchstation", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "dispatchstation";
					}
					if (havingGroup("smpheaddoctor", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "headdoctor";
					}
					if (($cnt = sizeof($return_array))) {
						return ($cnt == 1) ? $return_array[0] : $return_array;
					}
					break;
				case 20:
					if (havingGroup("minzdravdlo", $groups)) {
						return $res["MedServiceType_SysNick"];
					}
					break;
				case 27:
					if (havingGroup("ouzuser", $groups) || havingGroup("ouzadmin", $groups) || havingGroup("ouzchief", $groups)) {
						return $res["MedServiceType_SysNick"];
					}
					break;
				case 40:
				case 42:
				case 43:
				case 44:
				case 45:
				case 46:
				case 47:
				case 48:
				case 54:
					$return_array = [];
					if (havingGroup("bsmesecretary", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "bsmesecretary";
					}
					if (havingGroup("bsmehead", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "bsmehead";
					}
					if (havingGroup("bsmeexpert", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "bsmeexpert";
					}
					if (havingGroup("bsmeexpertassistant", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "bsmeexpertassistant";
					}
					if (havingGroup("bsmedprthead", $groups)) {
						$return_array[] = $res["MedServiceType_SysNick"] . "bsmedprthead";
					}
					if (($cnt = sizeof($return_array))) {
						return ($cnt == 1) ? $return_array[0] : $return_array;
					}
					break;
				case 57:
					return str_replace("_", "", $res["MedServiceType_SysNick"]);
					break;
				case 61:
					if (havingGroup("lvn", $groups)) {
						return $res["MedServiceType_SysNick"];
					}
					break;
				case 63:
					if (havingGroup("zmk", $groups)) {
						return $res["MedServiceType_SysNick"];
					}
					break;
				case 64:
					return $res["MedServiceType_SysNick"];
					break;
				case 66:
					return "paidservice";
					break;
				default:
					return strtolower($res["MedServiceType_SysNick"]);
					break;
			}
		}

		// Проверка на АРМы отделений
		if (!empty($res["MedStaffFact_id"]) && !empty($res["LpuUnitType_SysNick"])) {
			//Загрузка дополнительных профилей для отделений Екатеринбурга
			if ($_SESSION["region"]["nick"] == "ekb") {
				$callObject->load->model("LpuStructure_model", "lsmodel");
				$profile_list = $callObject->lsmodel->loadLpuSectionProfileList([
					"LpuSection_id" => $res["LpuSection_id"],
					"additionWithDefault" => 2
				]);
			}
			switch ($res["LpuUnitType_SysNick"]) {
				case "polka":
				case "ccenter":
				case "traumcenter":
				case "fap":
					switch ($_SESSION["region"]["nick"]) {
						case "ekb":
							$_REGION = $callObject->config->item($_SESSION["region"]["nick"]);
							$return_array = [];
							foreach ($profile_list as $item) {
								$temp = (isset($_REGION["STOM_LSP_CODE_LIST"]) && in_array($item["LpuSectionProfile_Code"], $_REGION["STOM_LSP_CODE_LIST"], true)) ? "stom" : "common";
								if (!in_array($temp, $return_array)) {
									$return_array[] = $temp;
									if ($temp == "common" && (!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res["Lpu_id"], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
										if (empty($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res["Lpu_id"]]["LpuSection"]) || in_array($res["LpuSection_id"], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res["Lpu_id"]]["LpuSection"])) {
											$return_array[] = "polka";
										}
									}
									if ($temp == "stom" && (!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res["Lpu_id"], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
										if (empty($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res["Lpu_id"]]["LpuSection"]) || in_array($res["LpuSection_id"], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res["Lpu_id"]]["LpuSection"])) {
											$return_array[] = "stom6";
										}
									}
								}
							}
							return (count($return_array) == 1) ? $return_array[0] : $return_array;
							break;
						case "ufa":
						case "perm":
						case "penza":
						case "by":
						case "khak":
						case "astra":
						case "kareliya":
						case "buryatiya":
						case "pskov":
						case "samara":
						case "kaluga":
						case "krym":
						case "kz":
						case "komi":
						case "vologda":
						case "krasnoyarsk":
						case "yaroslavl":
						case "yakutiya":
							$return_array = array();
							$isPhys = false;
							$_REGION = $callObject->config->item($_SESSION["region"]["nick"]);
							if (isset($_REGION['STOM_LSP_CODE_LIST']) && in_array($res['LpuSectionProfile_Code'], $_REGION['STOM_LSP_CODE_LIST'], true)) {
								// АРМ стоматолога
								$return_array[] = 'stom';
	
								if ((!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res['Lpu_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
									if (empty($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection']) || in_array($res['LpuSection_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection'])) {
										$return_array[] = 'stom6';
									}
								}
							} else {
								// АРМ терапевта или физиотерапевта
								switch ($_SESSION["region"]["nick"]) {
									case 'kz':
										if ($res['LpuSectionProfile_Code'] == '1013') {
											$isPhys = true;
										}
										break;
									case 'ufa':
										if (in_array($res['LpuSectionProfile_Code'], ['572', '672', '10008'])) {
											$isPhys = true;
										}
										break;
									default:
										if ($res['LpuSectionProfile_Code'] == '109') {
											$isPhys = true;
										}
										break;
								}
	
								if (!$isPhys && ($callObject->regionNick != 'msk' || !havingGroup('OperLLO'))) {//если есть группа OperLLO, то АРМ врача не доступен #183310
									$return_array[] = 'common';
	
									if ((!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res['Lpu_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
										if (empty($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection']) || in_array($res['LpuSection_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection'])) {
											$return_array[] = 'polka';
										}
									}
								} else if ($isPhys && !empty($res['LpuSection_id'])) {
									$isPhysMedService = $callObject->getFirstResultFromQuery("
											select
												MedService_id as \"MedService_id\"
											from v_MedService
											where MedServiceType_id = 13
												and LpuSection_id = :LpuSection_id 
											limit 1
										", [
										'LpuSection_id' => $res['LpuSection_id']
									]);
									if ($isPhysMedService !== false && !empty($isPhysMedService)) {
										$return_array[] = 'phys';
									}
								}
							}
							return $return_array;
							break;
						default:
							$return_array = [];
							if (substr($res["LpuSectionProfile_Code"], 0, 2) == "18") {
								$return_array[] = "stom";
								if ((!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res["Lpu_id"], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
									if (empty($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res["Lpu_id"]]["LpuSection"]) || in_array($res["LpuSection_id"], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res["Lpu_id"]]["LpuSection"])) {
										$return_array[] = "stom6";
									}
								}
							} else if ($callObject->regionNick != 'msk' || !havingGroup('OperLLO')) { //если есть группа OperLLO, то АРМ врача не доступен #183310
								$return_array[] = "common";
								if ((!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res["Lpu_id"], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
									if (empty($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res["Lpu_id"]]["LpuSection"]) || in_array($res["LpuSection_id"], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res["Lpu_id"]]["LpuSection"])) {
										$return_array[] = "polka";
									}
								}
							}
							return $return_array;
							break;

					}
					break;
				case "stac":
				case "hstac":
				case "pstac":
				case "dstac":
					if ($_SESSION["region"]["nick"] == "ekb") {
						$return_array = [];
						foreach ($profile_list as $item) {
							$temp = "";
							// Доп. условие реализовано по задаче https://redmine.swan.perm.ru/issues/30589 для учета региональных особенностей
							if ($item["LpuSectionProfile_SysNick"] == "priem" || $callObject->getStacPriemAdditionalCondition($res)) {
								$temp = "stacpriem";
							} else {
								// коды специальностей одинаковы (на Уфе тоже новый ЕРМП)
								$headnursecond = (in_array($res["PostMed_Code"], ["116"]) && in_array($res["LpuUnitType_id"], ["1", "6", "9"]));
								$stacnursecond = in_array($res["PostMed_Code"], ["126"]) && in_array($res["LpuUnitType_id"], ["1", "6", "9"]);
								if ($stacnursecond) {
									$temp = "stacnurse";
								} elseif ($headnursecond) {
									$temp = "headnurse";
								} elseif (in_array($res["PostKind_id"], [1, 10])) {
									$temp = "stac";
								}
							}
							if (!empty($temp) && !in_array($temp, $return_array)) {
								$return_array[] = $temp;
							}
						}
						return (count($return_array) == 1) ? $return_array[0] : $return_array;
					}
					// Доп. условие реализовано по задаче https://redmine.swan.perm.ru/issues/30589 для учета региональных особенностей
					if ($res["LpuSectionProfile_SysNick"] == "priem" || $callObject->getStacPriemAdditionalCondition($res)) {
						return "stacpriem";
					}
					if ($_SESSION["region"]["nick"] == "kz") {
						$headnursecond_postcode = ($callObject->config->item("IS_DEBUG") === "1" ? "116" : "108");
						$stacnursecond_postcode = ($callObject->config->item("IS_DEBUG") === "1" ? "126" : "117");
					} else {
						$headnursecond_postcode = "116";
						$stacnursecond_postcode = "126";
					}
					// коды специальностей одинаковы (на Уфе тоже новый ЕРМП)
					$headnursecond = (in_array($res["PostMed_Code"], [$headnursecond_postcode]) && in_array($res["LpuUnitType_id"], ["1", "6", "9"]));
					$stacnursecond = in_array($res["PostMed_Code"], [$stacnursecond_postcode]) && in_array($res["LpuUnitType_id"], ["1", "6", "9"]);
					if ($stacnursecond) {
						return "stacnurse";
					} else if ($headnursecond) {
						return "headnurse";
					} else if (in_array($res["PostKind_id"], [1, 10]) || $_SESSION["region"]["nick"] == "ufa") {
						return "stac";
					} else {
						return "";
					}
					break;

				// Это для Астрахани
				// https://redmine.swan.perm.ru/issues/30665
				case "priem":
					return "stacpriem";
					break;
			}
		}
		return "";
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deleteARMinDB(User_model $callObject, $data)
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_armtype_del(armtype_id := :ARMType_id);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deleteGroupDB(User_model $callObject, $data)
	{
		$params = ["pmUserCacheGroup_id" => $data["pmUserCacheGroup_id"]];
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_pmusercachegroup_del(pmusercachegroup_id := :pmUserCacheGroup_id);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function deleteReportARM(User_model $callObject, $data)
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from rpt.p_reportarm_del(reportarm_id := :ReportARM_id);
		";

		if (!empty($data['idField']) && $data['idField'] == 'ReportContentParameter_id') {
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from rpt.p_ReportContentParameterLink_del(ReportContentParameterLink_id := :ReportContentParameterLink_id);
			";
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $user
	 * @param bool $delete
	 * @return bool
	 */
	public static function deleteUserOfCache(User_model $callObject, $user, $delete = false)
	{
		if (!$user || !$user['pmUser_id']) {
			return false;
		}
		/**@var CI_DB_result $result */
		$queryParams = [
			"pmUser_id" => $user["pmUser_id"],
			"pmUser_delID" => !empty($user["pmUser_delID"]) ? $user["pmUser_delID"] : null
		];
		if ($delete) {
			$query = "	
				delete from pmUserCacheOrg where pmUserCache_id = :pmUser_id;
				delete from pmUserCache where pmUser_id = :pmUser_id;
			";
			$result = $callObject->db->query($query, $queryParams);
			return (is_object($result)) ? true : false;
		} else {
			$query = "
				select pmUser_deleted as \"pmUser_deleted\"
				from pmUserCache
				where pmUser_id = :pmUser_id
			";
			$result = $callObject->db->query($query, $queryParams);
			$result = $result->result_array();
			if(@$result[0]["pmUser_deleted"] != 2) {
				$query = "	
					update pmUserCache
					set
						pmUser_deleted = 2,
						pmUser_updID = :pmUser_delID,
						pmUser_delDT = dbo.tzGetDate()
					where pmUser_id = :pmUser_id
				";
				$result = $callObject->db->query($query, $queryParams);
				return (is_object($result)) ? true : false;
			}
		}
		return true;
	}

	/**
	 * @param User_model $callObject
	 * @return bool
	 */
	public static function isHeadMedSpecMedPersonal(User_model $callObject)
	{
		$response = false;
		if (!empty($_SESSION["medpersonal_id"]) && is_numeric($_SESSION["medpersonal_id"])) {
			$query = "
				select hmc.HeadMedSpec_id as \"HeadMedSpec_id\"
                from
                    dbo.v_MedPersonal mp
                    left join persis.v_MedWorker mw on mw.Person_id = mp.Person_id
                    left join dbo.v_HeadMedSpec hmc on hmc.MedWorker_id = mw.MedWorker_Id
                where mp.MedPersonal_id = :MedPersonal_id
				limit 1
			";
			$queryParams = ["MedPersonal_id" => $_SESSION["medpersonal_id"]];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			if (is_object($result)) {
				$res = $result->result("array");
				if (is_array($res) && count($res) > 0 && !empty($res[0]["HeadMedSpec_id"])) {
					$response = true;
				}
			}
		}
		return $response;
	}

	/**
	 * @param User_model $callObject
	 * @return mixed|bool
	 */
	public static function isHeadWithMedService(User_model $callObject)
	{
		$response = false;
		if (!empty($_SESSION["lpu_id"]) && is_numeric($_SESSION["lpu_id"]) && !empty($_SESSION["medpersonal_id"]) && is_numeric($_SESSION["medpersonal_id"])) {
			$query = "
				select
					msmp.MedStaffFact_id as \"MedStaffFact_id\",
					MS.LpuSection_id as \"LpuSection_id\",
					msmp.MedPersonal_id as \"MedPersonal_id\",
					coalesce(ls.LpuSection_FullName, '') as \"LpuSection_Name\",
					coalesce(ls.LpuSection_Name, '') as \"LpuSection_Nick\",
					'Руководитель аптеки' as \"PostMed_Name\",
					6 as \"PostMed_Code\",
					null as \"PostMed_id\",
					lb.LpuBuilding_id as \"LpuBuilding_id\",
					coalesce(lb.LpuBuilding_Name, '') as \"LpuBuilding_Name\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					lu.LpuUnitSet_id as \"LpuUnitSet_id\",
					coalesce(lu.LpuUnit_Name, '') as \"LpuUnit_Name\",
					null as \"Timetable_isExists\", 
					lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					MS.LpuUnitType_id as \"LpuUnitType_id\",
					lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
					lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					MS.MedService_id as \"MedService_id\",
					MS.MedService_Nick as \"MedService_Nick\",
					MS.MedService_Name as \"MedService_Name\",
					MS.MedServiceType_id as \"MedServiceType_id\",
					mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
					ms.MedService_IsExternal as \"MedService_IsExternal\",
					msmp.Person_Fio as \"MedPersonal_FIO\",
					Lpu.Org_id as \"Org_id\",
					Lpu.Lpu_id as \"Lpu_id\",
					Lpu.Lpu_Nick as \"Org_Nick\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					null as \"MedStaffFactLink_id\",
					null as \"MedStaffFactLink_begDT\",
					null as \"MedStaffFactLink_endDT\",
					null as \"MedicalCareKind_id\",
					null as \"PostKind_id\",
					null as \"MedStaffFactCache_IsDisableInDoc\"
				from 
					v_MedService MS
					inner join lateral (
						select
							msf.MedPersonal_id,
						    msf.MedStaffFact_id,
						    msf.Person_Fio
						from
							v_MedStaffFact msf
							left join v_MedPersonal mp on msf.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = MS.Lpu_id
							left join v_PostMed ps on ps.PostMed_id = msf.Post_id
						where msf.MedPersonal_id = :MedPersonal_id
						  and ps.PostMed_Code = 6
					    limit 1
					) as msmp on true
					left join v_Lpu lpu on lpu.Lpu_id = MS.Lpu_id
					left join v_LpuSection ls on ls.LpuSection_id = MS.LpuSection_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb on lb.LpuBuilding_id = coalesce(ls.LpuBuilding_id, MS.LpuBuilding_id)
					left join v_LpuUnit lu on lu.LpuUnit_id = coalesce(ls.LpuUnit_id, MS.LpuUnit_id)
					left join v_LpuUnitType lut on lut.LpuUnitType_id = coalesce(lu.LpuUnitType_id, MS.LpuUnitType_id)
					left join v_MedServiceType mst on mst.MedServiceType_id = MS.MedServiceType_id
				where MS.Lpu_id = :Lpu_id
				  and MS.MedService_begDT::date <= tzGetDate()::date
				  and (MS.MedService_endDT::date >= tzGetDate()::date or MS.MedService_endDT is null) 
				  and msmp.MedPersonal_id = :MedPersonal_id
				  and mst.MedServiceType_SysNick = 'rpo'
				limit 1
			";
			$params = [
				"Lpu_id" => $_SESSION["lpu_id"],
				"MedPersonal_id" => $_SESSION["medpersonal_id"]
			];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $params);
			if (!is_object($result)) {
				return false;
			}
			$result = $result->result("array");
			return (count($result) > 0) ? $result[0] : false;
		}
		return $response;
	}

	/**
	 * @param User_model $callObject
	 * @return bool
	 */
	public static function isPathoMorphoUser(User_model $callObject)
	{
		$response = false;
		if (!empty($_SESSION["lpu_id"]) && is_numeric($_SESSION["lpu_id"]) && !empty($_SESSION["medpersonal_id"]) && is_numeric($_SESSION["medpersonal_id"])) {
			$query = "
				select t1.MedPersonal_id as \"MedPersonal_id\"
				from
					v_MedServiceMedPersonal t1
					inner join v_MedService t2 on t2.MedService_id = t1.MedService_id
					inner join v_MedServiceType t3 on t3.MedServiceType_id = t2.MedServiceType_id
				where t1.MedPersonal_id = :MedPersonal_id
				  and t2.Lpu_id = :Lpu_id
				  and t3.MedServiceType_SysNick = 'patb'
				  and t1.MedServiceMedPersonal_begDT <= dbo.tzGetDate()
				  and (t1.MedServiceMedPersonal_endDT is null or t1.MedServiceMedPersonal_endDT >= dbo.tzGetDate())
				  and t2.MedService_begDT <= dbo.tzGetDate()
				  and (t2.MedService_endDT is null or t2.MedService_endDT >= dbo.tzGetDate())
				limit 1
			";
			$queryParams = [
				"Lpu_id" => $_SESSION["lpu_id"],
				"MedPersonal_id" => $_SESSION["medpersonal_id"]
			];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			if (is_object($result)) {
				$res = $result->result("array");
				if (is_array($res) && count($res) > 0 && !empty($res[0]["MedPersonal_id"])) {
					$response = true;
				}
			}
		}
		return $response;
	}

	/**
	 * @param User_model $callObject
	 * @return bool
	 */
	public static function isMedStatUser(User_model $callObject)
	{
		$response = false;
		if (!empty($_SESSION["lpu_id"]) && is_numeric($_SESSION["lpu_id"]) && !empty($_SESSION["medpersonal_id"]) && is_numeric($_SESSION["medpersonal_id"])) {
			$query = "
				select t1.MedPersonal_id as \"MedPersonal_id\"
				from
					v_MedServiceMedPersonal t1
					inner join v_MedService t2 on t2.MedService_id = t1.MedService_id
					inner join v_MedServiceType t3 on t3.MedServiceType_id = t2.MedServiceType_id
				where t1.MedPersonal_id = :MedPersonal_id
				  and t2.Lpu_id = :Lpu_id
				  and t3.MedServiceType_SysNick = 'mstat'
				  and t1.MedServiceMedPersonal_begDT <= dbo.tzGetDate()
				  and (t1.MedServiceMedPersonal_endDT is null or t1.MedServiceMedPersonal_endDT >= dbo.tzGetDate())
				  and t2.MedService_begDT <= dbo.tzGetDate()
				  and (t2.MedService_endDT is null or t2.MedService_endDT >= dbo.tzGetDate())
				limit 1
			";
			$queryParams = [
				"Lpu_id" => $_SESSION["lpu_id"],
				"MedPersonal_id" => $_SESSION["medpersonal_id"]
			];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			if (is_object($result)) {
				$result = $result->result("array");
				if (is_array($result) && count($result) > 0 && !empty($result[0]["MedPersonal_id"])) {
					$response = true;
				}
			}
		}
		return $response;
	}

	/**
	 * @param User_model $callObject
	 * @return array
	 */
	public static function loadARMList(User_model $callObject)
	{
		$region_nick = getRegionNick();
		$clientExt6 = "ext6";
		$useExt6Only = $callObject->config->item("USE_EXTJS6_ONLY");
		if (!empty($useExt6Only)) {
			$clientExt6 = "ext6only";
		}
		//в БД есть таблица ARMType, возможно стоит начать её использовать?
		return [
			"common" => ["Arm_id" => 1, "Arm_Name" => "АРМ врача поликлиники", "Arm_Form" => "swMPWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"stom" => ["Arm_id" => 2, "Arm_Name" => "АРМ стоматолога", "Arm_Form" => "swWorkPlaceStomWindow", "client" => "ext2", "ShowMainMenu" => "1"],
			"stac" => ["Arm_id" => 3, "Arm_Name" => "АРМ врача стационара", "Arm_Form" => "swMPWorkPlaceStacWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"stacpriem" => ["Arm_id" => 4, "Arm_Name" => "АРМ врача приемного отделения", "Arm_Form" => "swMPWorkPlacePriemWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"stacnurse" => ["Arm_id" => 5, "Arm_Name" => "АРМ постовой медсестры", "Arm_Form" => "swEvnPrescrJournalWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"microbiolab" => ["Arm_id" => 1050, "Arm_Name" => "АРМ Бактериолога", "Arm_Form" => "swBacteriologistWorkPlaceWindow", "client"=> "ext2", "ShowMainMenu" => "2"],
			"lab" => ["Arm_id" => 7, "Arm_Name" => "АРМ лаборанта", "Arm_Form" => "swAssistantWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"vk" => ["Arm_id" => 8, "Arm_Name" => "АРМ врача ВК", "Arm_Form" => "swVKWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"mse" => ["Arm_id" => 9, "Arm_Name" => "АРМ МСЭ", "Arm_Form" => "swMseWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"polkallo" => ["Arm_id" => 10, "Arm_Name" => "АРМ врача ЛЛО поликлиники", "Arm_Form" => "swWorkPlacePolkaLLOWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"pzm" => ["Arm_id" => 11, "Arm_Name" => "АРМ сотрудника пункта забора биоматериала", "Arm_Form" => "swAssistantWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"func" => ["Arm_id" => 12, "Arm_Name" => "АРМ диагностики", "Arm_Form" => "swWorkPlaceFuncDiagWindow", "client" => "ext2", "ShowMainMenu" => "1"],
			"superadmin" => ["Arm_id" => 13, "Arm_Name" => "АРМ администратора ЦОД", "Arm_Form" => "swAdminWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"lpuadmin" => ["Arm_id" => 14, "Arm_Name" => "АРМ администратора МО", "Arm_Form" => "swLpuAdminWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"orgadmin" => ["Arm_id" => 14, "Arm_Name" => "АРМ администратора организации", "Arm_Form" => "swOrgAdminWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"htm" => ["Arm_id" => 61, "Arm_Name" => "АРМ ВМП", "Arm_Form" => "swHTMWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"patb" => ["Arm_id" => 16, "Arm_Name" => "АРМ патологоанатома", "Arm_Form" => "swWorkPlacePathoMorphologyWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"merch" => ["Arm_id" => 17, "Arm_Name" => "АРМ товароведа", "Arm_Form" => "swWorkPlaceMerchandiserWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"dpoint" => ["Arm_id" => 18, "Arm_Name" => "АРМ провизора", "Arm_Form" => "swWorkPlaceDistributionPointWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"ooa" => ["Arm_id" => 18, "Arm_Name" => "АРМ провизора общего отдела", "Arm_Form" => "swWorkPlaceDistributionPointCommonWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"lpucadrview" => ["Arm_id" => 19, "Arm_Name" => "АРМ специалиста отдела кадров", "Arm_Form" => "swWorkPlaceHRWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"mstat" => ["Arm_id" => 20, "Arm_Name" => "АРМ медицинского статистика", "Arm_Form" => "swWorkPlaceMedStatWindow", "client" => "ext2", "ShowMainMenu" => "1"],
			"prock" => ["Arm_id" => 21, "Arm_Name" => "АРМ медсестры процедурного кабинета", "Arm_Form" => "swWorkPlaceProcCabinetWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"stachelpdesc" => ["Arm_id" => 22, "Arm_Name" => "АРМ сотрудника справочного стола стационара", "Arm_Form" => "swWorkPlaceStacHelpDeskWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"smpreg" => ["Arm_id" => 24, "Arm_Name" => "АРМ оператора СМП", "Arm_Form" => "swWorkPlaceSMPWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"regpol" => ["Arm_id" => 25, "Arm_Name" => "АРМ регистратора поликлиники", "Arm_Form" => "swWorkPlacePolkaRegWindow", "client" => "ext2", "ShowMainMenu" => "1"],
			"callcenter" => ["Arm_id" => 26, "Arm_Name" => "АРМ оператора call-центра", "Arm_Form" => "swWorkPlaceCallCenterWindow", "client" => "ext2", "ShowMainMenu" => "1"],
			"sprst" => ["Arm_id" => 27, "Arm_Name" => "АРМ сотрудника справочного стола стационара", "Arm_Form" => "swWorkPlaceStacHelpDeskWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"lpucadradmin" => ["Arm_id" => 28, "Arm_Name" => "АРМ специалиста отдела кадров/администратора", "Arm_Form" => "swWorkPlaceHRWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"minzdravdlo" => ["Arm_id" => 29, "Arm_Name" => "АРМ специалиста ЛЛО ОУЗ", "Arm_Form" => "swWorkPlaceMinzdravDLOWindow", "client" => "ext2", "ShowMainMenu" => "1"],
			"slneotl" => ["Arm_id" => 30, "Arm_Name" => "АРМ оператора НМП", "Arm_Form" => "swWorkPlacePPDWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"dispnmp" => ["Arm_id" => 142, "Arm_Name" => "АРМ диспетчера НМП", "Arm_Form" => "swWorkPlaceSMPDispatcherStationWindow", "client" => "ext4", "ShowMainMenu" => "2"],
			"smpdispatchcall" => ["Arm_id" => 32, "Arm_Name" => "АРМ диспетчера по приёму вызовов", "Arm_Form" => "swWorkPlaceSMPDispatcherCallWindow", "client" => "ext4", "ShowMainMenu" => "2"],
			"dispcallnmp" => ["Arm_id" => 140, "Arm_Name" => "АРМ диспетчера по приёму вызовов НМП", "Arm_Form" => "swWorkPlaceSMPDispatcherCallWindow", "client" => "ext4", "ShowMainMenu" => "2"],
			"smpheadduty" => ["Arm_id" => 33, "Arm_Name" => "АРМ старшего смены СМП", "Arm_Form" => "swWorkPlaceSMPHeadDutyWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"smpheadbrig" => ["Arm_id" => 34, "Arm_Name" => "АРМ старшего бригады СМП", "Arm_Form" => "swWorkPlaceSMPHeadBrigWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"leadermo" => ["Arm_id" => 35, "Arm_Name" => "АРМ руководителя МО", "Arm_Form" => "swWorkPlaceLeaderMOWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"mekllo" => ["Arm_id" => 36, "Arm_Name" => "АРМ МЭК ЛЛО", "Arm_Form" => "swWorkPlaceMEKLLOWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"spesexpertllo" => ["Arm_id" => 93, "Arm_Name" => "АРМ специалиста по экспертизе ЛЛО", "Arm_Form" => "swWorkPlaceSpecMEKLLOWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"adminllo" => ["Arm_id" => 94, "Arm_Name" => $region_nick == "msk" ? "АРМ сотрудника ситуационного центра по ЛЛО" : "АРМ администратора ЛЛО", "Arm_Form" => "swWorkPlaceAdminLLOWindow", "client" => "ext2", "ShowMainMenu" => "1"],
			"smpadmin" => ["Arm_id" => 37, "Arm_Name" => "АРМ администратора СМП", "Arm_Form" => "swWorkPlaceSMPAdminWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"touz" => ["Arm_id" => 38, "Arm_Name" => "АРМ специалиста ТОУЗ", "Arm_Form" => "swWorkPlaceTOUZWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"reglab" => ["Arm_id" => 41, "Arm_Name" => "АРМ регистрационной службы лаборатории", "Arm_Form" => "swAssistantWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"konsult" => ["Arm_id" => 39, "Arm_Name" => "АРМ сотрудника службы консультативного приема", "Arm_Form" => "swWorkPlaceConsultPriemWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"foodserv" => ["Arm_id" => 40, "Arm_Name" => "АРМ сотрудника пищеблока", "Arm_Form" => "swCookWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"headnurse" => ["Arm_id" => 137, "Arm_Name" => "АРМ старшей медсестры", "Arm_Form" => "swHeadNurseWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"tfoms" => ["Arm_id" => 138, "Arm_Name" => "АРМ пользователя ТФОМС", "Arm_Form" => "swWorkPlaceTFOMSWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"smo" => ["Arm_id" => 139, "Arm_Name" => "АРМ пользователя СМО", "Arm_Form" => "swWorkPlaceSMOWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"vac" => ["Arm_id" => 51, "Arm_Name" => "АРМ медсестры кабинета вакцинации", "Arm_Form" => "amm_WorkPlaceVacCabinetWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"epidem" => ["Arm_id" => 52, "Arm_Name" => "АРМ эпидемиолога", "Arm_Form" => "amm_WorkPlaceEpidemWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"remoteconsultcenter" => ["Arm_id" => 53, "Arm_Name" => "АРМ сотрудника центра удалённой консультации", "Arm_Form" => "swWorkPlaceTelemedWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"epidem_mo" => ["Arm_id" => 54, "Arm_Name" => "АРМ эпидемиолога МО", "Arm_Form" => "amm_WorkPlaceEpidemWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"zags" => ["Arm_id" => 55, "Arm_Name" => "АРМ сотрудника ЗАГС", "Arm_Form" => "swZagsWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"smpdispatchstation" => ["Arm_id" => 56, "Arm_Name" => "АРМ диспетчера подстанции СМП", "Arm_Form" => "swWorkPlaceSMPDispatcherStationWindow", "client" => "ext4", "ShowMainMenu" => "2"],
			"dispdirnmp" => ["Arm_id" => 141, "Arm_Name" => "АРМ диспетчера направлений НМП", "Arm_Form" => "swWorkPlaceSMPDispatcherStationWindow", "client" => "ext4", "ShowMainMenu" => "2"],
			"nmpgranddoc" => ["Arm_id" => 143, "Arm_Name" => "АРМ старшего врача НМП", "Arm_Form" => "swWorkPlaceSMPHeadDoctorWindow", "client" => "ext4", "ShowMainMenu" => "2"],
			"operblock" => ["Arm_id" => 57, "Arm_Name" => "АРМ сотрудника оперблока", "Arm_Form" => "swWorkPlaceOperBlockWindow", "client" => "ext6", "ShowMainMenu" => "2"],
			"smpheaddoctor" => ["Arm_id" => 58, "Arm_Name" => "АРМ старшего врача СМП", "Arm_Form" => "swWorkPlaceSMPHeadDoctorWindow", "client" => "ext4", "ShowMainMenu" => "2"],
			"pmllo" => ["Arm_id" => 59, "Arm_Name" => "АРМ поставщика", "Arm_Form" => "swWorkPlaceSupplierWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"zmk" => ["Arm_id" => 63, "Arm_Name" => $region_nick == "ufa" ? "Единый диспетчерский центр СМП и АБ – мониторинг СМП" : "АРМ Центра медицины катастроф", "Arm_Form" => "swWorkPlaceCenterDisasterMedicineWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"spec_mz" => ["Arm_id" => 111, "Arm_Name" => "АРМ специалиста Минздрава", "Arm_Form" => "swWorkPlaceMZSpecWindow", "client" => "ext2", "ShowMainMenu" => "1"],
			"communic" => ["Arm_id" => 112, "Arm_Name" => "АРМ специалиста МИРС", "Arm_Form" => "swWorkPlaceCommunicWindow", "client" => "ext2", "ShowMainMenu" => "1"],
			"phys" => ["Arm_id" => 250, "Arm_Name" => "АРМ врача физиотерапевта", "Arm_Form" => "swWorkPlacePhysWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"forenbiodprtwithmolgenlabbsmesecretary" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 1), "Arm_Name" => "АРМ регистратора БСМЭ", "Arm_Form" => "swBSMEForenBioSecretaryWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenbiodprtwithmolgenlabbsmehead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 2), "Arm_Name" => "АРМ руководителя БСМЭ", "Arm_Form" => "swBSMEDefaultWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenbiodprtwithmolgenlabbsmeexpert" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 3), "Arm_Name" => "АРМ эксперта", "Arm_Form" => "swBSMEForenBioExpertWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenbiodprtwithmolgenlabbsmedprthead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 4), "Arm_Name" => "АРМ заведующего отделением БСМЭ", "Arm_Form" => "swBSMEForenBioDprtHeadWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenchemdprtbsmesecretary" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 5), "Arm_Name" => "АРМ регистратора БСМЭ", "Arm_Form" => "swBSMEForenChemSecretaryWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenchemdprtbsmehead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 6), "Arm_Name" => "АРМ руководителя БСМЭ", "Arm_Form" => "swBSMEDefaultWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenchemdprtbsmeexpert" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 7), "Arm_Name" => "АРМ эксперта", "Arm_Form" => "swDefaultExpertWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenchemdprtbsmedprthead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 8), "Arm_Name" => "АРМ заведующего отделением БСМЭ", "Arm_Form" => "swBSMEForenChemDprtHeadWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"medforendprtbsmesecretary" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 9), "Arm_Name" => "АРМ регистратора БСМЭ", "Arm_Form" => "swBSMEForenCrimSecretaryWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"medforendprtbsmehead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 10), "Arm_Name" => "АРМ руководителя БСМЭ", "Arm_Form" => "swBSMEDefaultWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"medforendprtbsmeexpert" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 11), "Arm_Name" => "АРМ эксперта", "Arm_Form" => "swDefaultExpertWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"medforendprtbsmedprthead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 12), "Arm_Name" => "АРМ заведующего отделением БСМЭ", "Arm_Form" => "swBSMEForenCrimDprtHeadWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenhistdprtbsmesecretary" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 13), "Arm_Name" => "АРМ регистратора БСМЭ", "Arm_Form" => "swBSMEDefaultWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenhistdprtbsmehead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 14), "Arm_Name" => "АРМ руководителя БСМЭ", "Arm_Form" => "swBSMEDefaultWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenhistdprtbsmeexpert" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 15), "Arm_Name" => "АРМ эксперта", "Arm_Form" => "swDefaultExpertWorkPlace", "client" => "ext4", "ShowMainMenu" => "12"],
			"forenhistdprtbsmedprthead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 16), "Arm_Name" => "АРМ заведующего отделением БСМЭ", "Arm_Form" => "swBSMEForenHistDprtHeadWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"organmethdprtbsmesecretary" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 17), "Arm_Name" => "АРМ регистратора БСМЭ", "Arm_Form" => "swBSMEDefaultWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"organmethdprtbsmehead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 18), "Arm_Name" => "АРМ руководителя БСМЭ", "Arm_Form" => "swBSMEDefaultWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"organmethdprtbsmeexpert" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 19), "Arm_Name" => "АРМ эксперта", "Arm_Form" => "swDefaultExpertWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"organmethdprtbsmedprthead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 20), "Arm_Name" => "АРМ заведующего отделением БСМЭ", "Arm_Form" => "swDefaultDprtHeadWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenmedcorpsexpdprtbsmesecretary" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 21), "Arm_Name" => "АРМ регистратора БСМЭ", "Arm_Form" => "swBSMEForenCorpSecretaryWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenmedcorpsexpdprtbsmehead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 22), "Arm_Name" => "АРМ руководителя БСМЭ", "Arm_Form" => "swBSMEDefaultWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenmedcorpsexpdprtbsmeexpert" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 23), "Arm_Name" => "АРМ эксперта", "Arm_Form" => "swDefaultExpertWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenmedcorpsexpdprtbsmedprthead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 24), "Arm_Name" => "АРМ заведующего отделением БСМЭ", "Arm_Form" => "swDefaultDprtHeadWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenmedexppersdprtbsmesecretary" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 25), "Arm_Name" => "АРМ регистратора БСМЭ", "Arm_Form" => "swBSMEForenPersSecretaryWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenmedexppersdprtbsmehead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 26), "Arm_Name" => "АРМ руководителя БСМЭ", "Arm_Form" => "swBSMEDefaultWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenmedexppersdprtbsmeexpert" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 27), "Arm_Name" => "АРМ эксперта", "Arm_Form" => "swBSMEForenPersExpertWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenmedexppersdprtbsmedprthead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 28), "Arm_Name" => "АРМ заведующего отделением БСМЭ", "Arm_Form" => "swBSMEForenPersDprtHeadWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"commcomplexpbsmesecretary" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 29), "Arm_Name" => "АРМ регистратора БСМЭ", "Arm_Form" => "swBSMEForenComplexSecretaryWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"commcomplexpbsmehead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 30), "Arm_Name" => "АРМ руководителя БСМЭ", "Arm_Form" => "swBSMEDefaultWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"commcomplexpbsmeexpert" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 31), "Arm_Name" => "АРМ эксперта", "Arm_Form" => "swDefaultExpertWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"commcomplexpbsmedprthead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 32), "Arm_Name" => "АРМ заведующего отделением БСМЭ", "Arm_Form" => "swDefaultDprtHeadWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenareadprtbsmesecretary" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 33), "Arm_Name" => "АРМ регистратора БСМЭ", "Arm_Form" => "swBSMEForenAreaDprtSecretaryWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenareadprtbsmehead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 34), "Arm_Name" => "АРМ руководителя БСМЭ", "Arm_Form" => "swBSMEDefaultWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenareadprtbsmeexpert" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 35), "Arm_Name" => "АРМ эксперта", "Arm_Form" => "swDefaultExpertWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenareadprtbsmedprthead" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 36), "Arm_Name" => "АРМ заведующего отделением БСМЭ", "Arm_Form" => "swDefaultDprtHeadWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"forenmedexppersdprtbsmeexpertassistant" => ["Arm_id" => (User_model::BSME_ARM_ID_PREF + 43), "Arm_Name" => "АРМ лаборанта БСМЭ", "Arm_Form" => "swBSMEForenPersExpertAssistantWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"zakup" => ["Arm_id" => 60, "Arm_Name" => ($_SESSION["region"]["nick"] == "ufa") ? "АРМ специалиста ГКУ" : "АРМ специалиста по закупам", "Arm_Form" => "swWorkPlaceGKUWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"lvn" => ["Arm_id" => 95, "Arm_Name" => "АРМ регистратора ЛВН", "Arm_Form" => "swWorkPlaceEvnStickRegWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"hn" => ["Arm_id" => 96, "Arm_Name" => "АРМ главной медсестры МО", "Arm_Form" => "swWorkPlaceHeadNurseWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"mzchieffreelancer" => ["Arm_id" => 97, "Arm_Name" => "АРМ главного внештатного специалиста при МЗ", "Arm_Form" => "swWorkPlaceMinzdravChiefFreelancerWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"rpo" => ["Arm_id" => 98, "Arm_Name" => "АРМ специалиста РПО", "Arm_Form" => "swWorkPlaceRPOWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"lpupharmacyhead" => ["Arm_id" => 99, "Arm_Name" => "АРМ заведующего аптекой МО", "Arm_Form" => "swWorkPlaceLpuPharmacyHeadWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"paidservice" => ["Arm_id" => 100, "Arm_Name" => "АРМ администратора платных услуг", "Arm_Form" => "swPaidServiceWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"polka" => ["Arm_id" => 101, "Arm_Name" => "АРМ врача поликлиники (ExtJS 6)", "Arm_Form" => "swWorkPlacePolkaWindow", "client" => $clientExt6, "ShowMainMenu" => "1"],
			"stom6" => ["Arm_id" => 104, "Arm_Name" => "АРМ стоматолога (ExtJS 6)", "Arm_Form" => "swWorkPlaceStomWindowExt6", "client" => $clientExt6, "ShowMainMenu" => "1"],
			"lpuadmin6" => ["Arm_id" => 105, "Arm_Name" => "АРМ администратора МО (ExtJS 6)", "Arm_Form" => "swLpuAdminWorkPlaceWindowExt6", "client" => $clientExt6, "ShowMainMenu" => "1"],
			"regpol6" => ["Arm_id" => 106, "Arm_Name" => "АРМ регистратора поликлиники (ExtJS 6)", "Arm_Form" => "swWorkPlacePolkaRegWindowExt6", "client" => $clientExt6, "ShowMainMenu" => "1"],
			"smpinteractivemap" => ["Arm_id" => 102, "Arm_Name" => "АРМ интерактивной карты", "Arm_Form" => "swInteractiveMapWorkPlace", "client" => "ext4", "ShowMainMenu" => "2"],
			"profosmotr" => ["Arm_id" => 103, "Arm_Name" => "АРМ профилактического осмотра", "Arm_Form" => "swProfServiceWorkPlaceWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			"reanimation" => ["Arm_id" => 127, "Arm_Name" => "АРМ врача реаниматолога", "Arm_Form" => "swMPWorkPlaceStacWindow", "client" => "ext2", "ShowMainMenu" => "2"],
			'regpolprivate6'=>array('Arm_id' => 128, 'Arm_Name' => 'АРМ регистратора частной поликлиники (ExtJS 6)', 'Arm_Form' => 'swWorkPlacePolkaRegPrivateWindowExt6', 'client'=>$clientExt6, 'ShowMainMenu' => '1'),
			'lpuuser'=>array('Arm_id' => 253, 'Arm_Name' => 'АРМ пользователя МО', 'Arm_Form' => 'swUserWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'lpuuser6'=>array('Arm_id' => 254, 'Arm_Name' => 'АРМ пользователя МО (ExtJS 6)', 'Arm_Form' => 'swLpuUserWorkPlaceWindowExt6', 'client'=>'ext6', 'ShowMainMenu' => '2')
		];
	}

	/**
	 * @param User_model $callObject
	 * @return array
	 */
	public static function loadGroups(User_model $callObject)
	{
		$query = "
			select
				pmUserCacheGroup_id as \"pmUserCacheGroup_id\",
				pmUserCacheGroup_Code as \"Group_id\",
				pmUserCacheGroup_Name as \"Group_Name\",
				pmUserCacheGroup_SysNick as \"Group_Code\",
				pmUserCacheGroup_ParallelSessions as \"Group_ParallelSessions\",
				case when pmUserCacheGroup_IsBlocked = 2 then 'true' else 'false' end as \"Group_IsBlocked\",
				pmUserCache.PMUser_Login as \"pmUser_Name\",
				(
				    select count(0)
				    from pmUserCacheGroupLink 
					where pmUserCacheGroup_id = pmUserCacheGroup.pmUserCacheGroup_id
				) as \"Group_UserCount\"
			from
				pmUserCacheGroup
				left join pmUserCache on pmUserCache.pmUser_id = pmUserCacheGroup.pmUser_insID
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		return $result->result("array");
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function loadOnlineUsersList(User_model $callObject, $data)
	{
		global $config;
		// тянем список армов
		$ARMList = $callObject->loadARMList();
		$ARMList["_noarm_"] = [
			"Arm_id" => -1,
			"Arm_Name" => "Пользователи, работающие без АРМ"
		];
		// тянем список организаций
		$OrgList = [];
		$resp_org = $callObject->queryResult("
			select
				Org_id as \"Org_id\",
				Org_Nick as \"Org_Nick\"
			from Org
			where Org_IsAccess = 2
		");
		foreach ($resp_org as $one_org) {
			$OrgList[$one_org["Org_id"]] = $one_org["Org_Nick"];
		}
		if (!empty($config["session_driver"]) && $config["session_driver"] == "mongodb") {
			// тянем все активные сессии из монго, по каждой разбираем сессию, фильтруем, считаем кол-во юзеров по АРМам.
			switch (checkMongoDb()) {
				case "mongo":
					$callObject->load->library("swMongodb", ["config_file" => "mongodbsessions"], "swmongodb");
					break;
				case "mongodb":
					$callObject->load->library("swMongodbPHP7", ["config_file" => "mongodbsessions"], "swmongodb");
					break;
			}
			$table = (isset($config["mongodb_session_settings"]) && isset($config["mongodb_session_settings"]["table"])) ? $config["mongodb_session_settings"]["table"] : "Session";
			$wheres = ["logged" => 1];
			if (!empty($data["Org_id"]) && is_numeric($data["Org_id"])) {
				$wheres["org_id"] = $data["Org_id"];
			}
			if (!empty($data["OrgType_id"]) && is_numeric($data["Org_id"])) {
				$wheres["orgtype_id"] = $data["OrgType_id"];
			}
			if (!empty($data["ARMType_SysNick"]) && $data["ARMType_SysNick"] != "null") {
				$wheres["armtype"] = ($data["ARMType_SysNick"] == "_noarm_") ? null : $data["ARMType_SysNick"];
			}
			$items = $callObject->swmongodb->where_gt("updated", time() - 1800)->where($wheres)->get($table); // только залогиненные и активные последние полчаса
			$counts = [];
			foreach ($items as $item) {
				if (empty($item["armtype"])) {
					$item["armtype"] = "_noarm_";
				}
				if (empty($counts["{$item["armtype"]}_{$item["org_id"]}"])) {
					$counts["{$item["armtype"]}_{$item["org_id"]}"] = [
						"count" => 1,
						"armtype" => $item["armtype"],
						"org_id" => $item["org_id"]
					];
				} else {
					$counts["{$item["armtype"]}_{$item["org_id"]}"]["count"]++;
				}
			}
			$resp = [];
			$id = 0;
			foreach ($counts as $key => $value) {
				$org = "Организация не определена ({$value["org_id"]})";
				if (!empty($OrgList[$value["org_id"]])) {
					$org = $OrgList[$value["org_id"]];
				}
				$id++;
				if (!empty($ARMList[$value["armtype"]])) {
					$resp[] = [
						"OnlineUsers_id" => $id,
						"ARMType_Name" => $ARMList[$value["armtype"]]["Arm_Name"],
						"Org_Nick" => $org,
						"Users_Count" => $value["count"]
					];
				} else {
					$resp[] = [
						"OnlineUsers_id" => $id,
						"ARMType_Name" => "АРМ не определён ({$value["armtype"]})",
						"Org_Nick" => $org,
						"Users_Count" => $value["count"]
					];
				}
			}
			return $resp;
		}
		// заглушка
		return [];
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadPMUserCacheOrgList(User_model $callObject, $data)
	{
		$params = ["Org_id" => $data["Org_id"]];
		$filters = ["PUO.Org_id = :Org_id"];

		if (!empty($data["pmUserCacheOrg_id"])) {
			$filters[] = "PUO.pmUserCacheOrg_id = :pmUserCacheOrg_id";
			$params["pmUserCacheOrg_id"] = $data["pmUserCacheOrg_id"];
		}
		if (!empty($data["query"])) {
			$filters[] = "(PU.pmUser_Login ilike :query||'%' or PU.pmUser_Name ilike :query||'%')";
			$params["query"] = $data["query"];
		}
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$query = "
			select
				PUO.pmUserCacheOrg_id as \"pmUserCacheOrg_id\",
				PUO.Org_id as \"Org_id\",
				PU.pmUser_id as \"pmUser_id\",
				rtrim(PU.pmUser_Login) as \"pmUser_Login\",
				rtrim(PU.pmUser_Name) as \"pmUser_Name\"
			from
				v_pmUserCacheOrg PUO
				inner join v_pmUserCache PU on PU.pmUser_id = PUO.pmUserCache_id
			{$whereString}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @throws Exception
	 */
	public static function recacheGroupFromDB(User_model $callObject, $data)
	{
		set_time_limit(0);
		$queryParams = [];
		$filter = "";
		if (isset($data["user"])) {
			// Если пользователь известен, то перекеширование групп сделаем только по этому пользователю
			$queryParams["pmUser_login"] = $data["user"];
			$filter .= "and pmUser_login = :pmUser_login";
		}
		$sql = "
			select
				pmUser_id as \"pmUser_id\",
			    pmUser_login as \"pmUser_login\",
			    pmUser_groups as \"pmUser_groups\"
			from v_pmusercache
			where pmUser_groups is not null
			  and pmUser_groups != '[]' {$filter}
		";
		$result = $callObject->db->query($sql, $queryParams);
		$max = 10;
		$i = 0;
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса");
		}
		$rows = $result->result("array");
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				// перебираем найденные записи
				if (isset($row["pmUser_groups"])) {
					$groupsDB = json_decode($row["pmUser_groups"]);
					if (count($groupsDB) > 0) {
						// Если есть группы в БД, тогда и смотрим в LADP
						$user = pmAuthUser::find($row["pmUser_login"]);
						$callObject->textlog->add("Check user " . $row["pmUser_login"]);
						if ($user && (empty($user->groups) || count($user->groups) == 0)) { // это условие можно убрать, в любом случае одинаковые группы не сохранятся
							// log
							$i++;
							echo $i . " | User id = " . $user->pmuser_id . ", login = " . $user->login . ", name = " . toAnsi($user->surname . " " . $user->firname) . ", группы: ";
							$callObject->textlog->add($i . " | User id = " . $user->pmuser_id . ", login = " . $user->login . ", name = " . toAnsi($user->surname . " " . $user->firname) . ", группы: ");
							// выбираем группы из БД
							foreach ($groupsDB as $group) {
								echo " " . $group->name;
								$user->addGroup($group->name);
							}
							// просто пересохраняем атрибуты
							foreach (array_values($user->groups) as $group) {
								ldap_insertattr($group->id, ["member" => $user->id]);
							}
							echo "<br/>";
						}
						unset($user);
						if ($i >= $max) { // прерываем
							break;
						}
					}
				}
			}
		}
		$msg = ($i >= $max) ? "Всего обработано {$i} записей, выполнение прервано, для остальных записей запустите функционал повторно<br/>" : "Всего обработано {$i} записей, выполнение успешно завершено.<br/>";
		$callObject->textlog->add($msg);
		echo $msg;
	}

	/**
	 * @param User_model $callObject
	 * @param $user
	 * @param $orgs
	 */
	public static function ReCacheUserOrgs(User_model $callObject, $user, $orgs)
	{
		$queryParams = ["pmUser_id" => $user["pmuser_id"]];
		//Если существет связь пользователя с сотрудником организации, то эти организации не перехешировать
		$sql = "
			select Org_id as \"Org_id\"
			from v_pmUserCacheOrg PUO
			where PUO.pmUserCache_id = :pmUser_id
			  and exists(
				select *
				from v_PersonWork PW
				where PW.pmUserCacheOrg_id = PUO.pmUserCacheOrg_id
			  )
		";
		$except_org_ids = $callObject->queryList($sql, $queryParams);
		$except_org_ids_str = implode(",", $except_org_ids);
		$filter = count($except_org_ids) > 0 ? " and Org_id not in ({$except_org_ids_str})" : "";
		// удалим те что были
		$sql = "
			delete from pmUserCacheOrg where pmUserCache_id = :pmUser_id {$filter}
		";
		$callObject->db->query($sql, $queryParams);
		// добавим новые
		foreach ($orgs as $org) {
			if (in_array($org, $except_org_ids)) {
				continue;
			}
			$sql = "
				select
				    pmusercacheorg_id as \"pmUserCacheOrg_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_pmusercacheorg_ins(
				    pmusercache_id := :pmUser_id,
				    org_id := cast(cast(:Org_id as numeric) as bigint),
				    pmuser_id := :pmUser_updID
				);
			";
			$queryParams["Org_id"] = $org;
			$queryParams["pmUser_updID"] = isset($_SESSION["pmuser_id"]) ? $_SESSION["pmuser_id"] : 1;
			$callObject->db->query($sql, $queryParams);
		}
	}

	/**
	 * @param User_model $callObject
	 * @param $user
	 * @param $orgs
	 * @return bool
	 */
	public static function ReCacheOrgUserData(User_model $callObject, $user, $orgs)
	{
		// сначала проверим есть ли пользователь в кэше и добавим его, если нет..
		$queryParams = [
			"pmUser_id" => $user["pmuser_id"],
			"pmUser_Login" => toAnsi($user["login"]),
			"pmUser_Name" => toAnsi(mb_substr($user["surname"] . " " . $user["firname"] . " " . $user["secname"], 0, 100)),
			"pmUser_surName" => toAnsi(mb_substr($user["surname"], 0, 40)),
			"pmUser_firName" => toAnsi(mb_substr($user["firname"], 0, 30)),
			"pmUser_secName" => toAnsi(mb_substr($user["secname"], 0, 30)),
			"pmUser_updID" => $_SESSION["pmuser_id"]
		];
		$query = "select 1 from pmUserCache where pmUser_id = :pmUser_id";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		$result = $result->result_array();
		$exists = count($result) > 0;
		if ($exists == false) {
			$query = "
				-- удаляем все записи которые ссылауются на pmUserCache
				delete from dbo.timejournal WHERE pmuser_tid in (select pmuser_id from pmUserCache WHERE pmUser_Login = :pmUser_Login);
				delete from dbo.datastorage WHERE pmuser_lid in (select pmuser_id from pmUserCache WHERE pmUser_Login = :pmUser_Login);
				delete from dbo.personwork WHERE pmusercacheorg_id in (
					select pmusercacheorg_id from dbo.pmusercacheorg WHERE pmusercache_id in (select pmuser_id from pmUserCache WHERE pmUser_Login = :pmUser_Login));
				delete from dbo.pmusercacheorg WHERE pmusercache_id in (select pmuser_id from pmUserCache WHERE pmUser_Login = :pmUser_Login);
				delete from pmUserCache WHERE pmUser_Login = :pmUser_Login;
				insert into pmUserCache(
					pmUser_id,
					pmUser_Login,
					pmUser_Name,
					pmUser_surName,
					pmUser_firName,
					pmUser_secName,
					pmUser_updID,
					pmUserCache_updDT
				) values(
					:pmUser_id,
					:pmUser_Login,
					:pmUser_Name,
					:pmUser_surName,
					:pmUser_firName,
					:pmUser_secName,
					:pmUser_updID,
					tzgetdate()
				);
			";
		} else {
			$query = "
				delete from pmUserCacheOrg
				where pmUserCache_id in (
				        select puc.PMUser_id
				        from pmUserCache puc
				        where puc.PMUser_Login = :pmUser_Login
				            and puc.pmUser_id <> :pmUser_id
				    );
				delete from pmUserCache
				where pmUser_Login = :pmUser_Login
				  and pmUser_id <> :pmUser_id;
				update pmUserCache
				set
					pmUser_Login = :pmUser_Login,
					pmUser_Name = :pmUser_Name,
					pmUser_surName = :pmUser_surName,
					pmUser_firName = :pmUser_firName,
					pmUser_secName = :pmUser_secName,
					pmUser_updID = :pmUser_updID,
					pmUserCache_updDT = dbo.tzGetDate()
				where pmUser_id = :pmUser_id;
			";
		}
		$callObject->db->query($query, $queryParams);
		$callObject->ReCacheUserOrgs($user, $orgs);
		return true;
	}

	/**
	 * @param User_model $callObject
	 * @param $user
	 * @param null $data
	 * @return bool
	 */
	public static function ReCacheUserData(User_model $callObject, $user, $data = null)
	{
		if (empty($data)) {
			$data = getSessionParams();
		}
		/**@var CI_DB_result $result */
		$queryParams = ["pmUser_id" => $user->pmuser_id];
		$query = "
			select 1 from pmUserCache where pmUser_id = :pmUser_id
		";
		$result = $callObject->db->query($query, $queryParams);
		$result = $result->result_array();
		$exists = count($result) > 1;
		if ($exists == false) {
			$query = "
				-- удаляем все записи которые ссылауются на pmUserCache
				delete from dbo.datastorage WHERE pmuser_lid in (select pmuser_id from pmUserCache WHERE pmUser_Login = :pmUser_Login);
				delete from dbo.personwork WHERE pmusercacheorg_id in (
					select pmusercacheorg_id from dbo.pmusercacheorg WHERE pmusercache_id in (select pmuser_id from pmUserCache WHERE pmUser_Login = :pmUser_Login));
				delete from dbo.pmusercacheorg WHERE pmusercache_id in (select pmuser_id from pmUserCache WHERE pmUser_Login = :pmUser_Login);
				delete from pmUserCache WHERE pmUser_Login = :pmUser_Login;
				insert into pmUserCache(
					pmUser_id,
					pmUser_Login,
					pmUser_Name,
					pmUser_surName,
					pmUser_firName,
					pmUser_secName,
					pmUser_Email,
					pmUser_Avatar,
					pmUser_About,
					pmUser_Blocked,
					Lpu_id, 
					MedPersonal_id,
					pmUser_insID,
					pmUserCache_insDT,
					pmUser_updID,
					pmUserCache_updDT,
					pmUser_groups,
					pmUser_deleted,
					pmUser_desc,
					pmUser_AccessMatrix,
					pmUser_Phone,
					pmUser_PhoneAct,
					pmUser_IsMessage,
					pmUser_IsSMS,
					pmUser_IsEmail,
					pmUser_GroupType,
					pmUser_PolkaGroupType,
					pmUser_EvnClass
				)
				values(
					:pmUser_id,
					:pmUser_Login,
					:pmUser_Name,
					:pmUser_surName,
					:pmUser_firName,
					:pmUser_secName,
					:pmUser_Email,
					:pmUser_Avatar,
					:pmUser_About,
					:pmUser_Blocked,
					:Lpu_id,
					:MedPersonal_id,
					:pmUser_insID,
					dbo.tzGetDate(),
					:pmUser_updID,
					dbo.tzGetDate(),
					:pmUser_groups,
					1,
					:pmUser_desc,
					:pmUser_AccessMatrix,
					:pmUser_Phone,
					:pmUser_PhoneAct,
					 cast(:pmUser_IsMessage as integer),
					CAST(:pmUser_IsSMS as integer),
					CAST(:pmUser_IsEmail as integer),
					:pmUser_GroupType,
					:pmUser_PolkaGroupType,
					:pmUser_EvnClass
				);
			";
		} else {
			$query = "
				delete from pmUserCacheOrg
				where pmUserCache_id in (
				        select puc.PMUser_id
				        from pmUserCache puc
				        where puc.PMUser_Login = :pmUser_Login
				            and puc.pmUser_id <> :pmUser_id
				    );
				delete from pmUserCache
				where pmUser_Login = :pmUser_Login
				  and pmUser_id <> :pmUser_id;
				UPDATE pmUserCache
				SET
					pmUser_Login = :pmUser_Login,
					pmUser_Name = :pmUser_Name,
					pmUser_surName = :pmUser_surName,
					pmUser_firName = :pmUser_firName,
					pmUser_secName = :pmUser_secName,
					pmUser_Email = :pmUser_Email,
					pmUser_Avatar = :pmUser_Avatar,
					pmUser_About = :pmUser_About,
					pmUser_Blocked = :pmUser_Blocked,
					Lpu_id = :Lpu_id,
					MedPersonal_id = :MedPersonal_id,
					pmUser_updID = :pmUser_updID,
					pmUserCache_updDT = dbo.tzGetDate(),
					pmUser_groups = :pmUser_groups,
					pmUser_delDT = null,
					pmUser_desc = :pmUser_desc,
					pmUser_AccessMatrix = :pmUser_AccessMatrix,
					pmUser_Phone = :pmUser_Phone,
					pmUser_PhoneAct = :pmUser_PhoneAct,
					pmUser_IsMessage = cast(:pmUser_IsMessage as integer),
					pmUser_IsSMS = CAST(:pmUser_IsSMS as integer),
					pmUser_IsEmail = CAST(:pmUser_IsEmail as integer),
					pmUser_GroupType = :pmUser_GroupType,
					pmUser_PolkaGroupType = :pmUser_PolkaGroupType,
					pmUser_EvnClass = :pmUser_EvnClass
				where pmUser_id = :pmUser_id;
			";
		}
		$settings = @unserialize($user->settings);
		$queryParams["pmUser_id"] = $user->pmuser_id;
		$queryParams["pmUser_Login"] = toAnsi($user->login);
		$queryParams["pmUser_Name"] = toAnsi($user->surname . " " . $user->firname);
		$queryParams["pmUser_surName"] = empty($user->surname) ? null : mb_substr(toAnsi($user->surname), 0, 40);
		$queryParams["pmUser_firName"] = empty($user->firname) ? null : mb_substr(toAnsi($user->firname), 0, 30);
		$queryParams["pmUser_secName"] = empty($user->secname) ? null : mb_substr(toAnsi($user->secname), 0, 30);
		$queryParams["pmUser_Email"] = toAnsi($user->email);
		$queryParams["pmUser_Avatar"] = toAnsi($user->avatar);
		$queryParams["pmUser_About"] = toAnsi($user->about);
		$queryParams["pmUser_Blocked"] = empty($user->blocked) ? 0 : $user->blocked;
		$queryParams["pmUser_desc"] = toAnsi($user->desc);
		$queryParams["pmUser_AccessMatrix"] = toAnsi($user->deniedarms);
		$queryParams["pmUser_Phone"] = $user->phone;
		$queryParams["pmUser_PhoneAct"] = $user->phone_act;
		$queryParams["pmUser_IsMessage"] = !empty($settings["notice"]["evn_notify_is_message"]) ? $settings["notice"]["evn_notify_is_message"] : 0;
		$queryParams["pmUser_IsSMS"] = !empty($settings["notice"]["evn_notify_is_sms"]) ? $settings["notice"]["evn_notify_is_sms"] : 0;
		$queryParams["pmUser_IsEmail"] = !empty($settings["notice"]["evn_notify_is_email"]) ? $settings["notice"]["evn_notify_is_email"] : 0;
		$queryParams["pmUser_GroupType"] = !empty($settings["notice"]["evn_notify_person_group_type"]) ? $settings["notice"]["evn_notify_person_group_type"] : 1;
		$queryParams["pmUser_PolkaGroupType"] = !empty($settings["notice"]["evn_notify_person_polka_group_type"]) ? $settings["notice"]["evn_notify_person_polka_group_type"] : 2;
		$callObject->load->model("MedPersonal_model", "MedPersonal_model");
		// проверка на существование MedPersonal_id в бд. (если не существует, записываем NULL в кэш.)
		if ($callObject->MedPersonal_model->checkMedPersonalExist($user->medpersonal_id)) {
			$queryParams["MedPersonal_id"] = empty($user->medpersonal_id) || !is_numeric($user->medpersonal_id) ? null : $user->medpersonal_id;
		} else {
			$queryParams["MedPersonal_id"] = null;
		}
		$queryParams["pmUser_insID"] = $data["pmUser_id"]; // берем из сессии, так как из контроллера передаются только данные по редактируемому человеку
		$queryParams["pmUser_updID"] = $data["pmUser_id"]; // берем из сессии, так как из контроллера передаются только данные по редактируемому человеку
		$groups = [];
		foreach ($user->groups as $g) {
			$groups[]["name"] = $g->name;
		}
		$queryParams["pmUser_groups"] = json_encode($groups);
		$callObject->load->model("Org_model", "orgmodel");
		$queryParams["Lpu_id"] = (isset($user->org[0])) ? $callObject->orgmodel->getLpuOnOrg(["Org_id" => $user->org[0]["org_id"]]) : null;
		$EvnClass_arr = [];
		$notice_settings_arr = ["evn_notify_is_message", "evn_notify_is_sms", "evn_notify_is_email", "evn_notify_person_group_type", "evn_notify_person_polka_group_type"];
		if (!empty($settings["notice"])) {
			foreach ($settings["notice"] as $key => $value) {
				if (!in_array($key, $notice_settings_arr)) {
					if ($value) {
						$EvnClass_arr[] = ["sysnick" => $key];
					}
				}
			}
		}
		$queryParams["pmUser_EvnClass"] = json_encode($EvnClass_arr);
		$callObject->db->query($query, $queryParams);
		
		//Обновление групп
		$callObject->removeGroupLink([
			'id' => $user->pmuser_id
		]);
		foreach($groups as $group) {
			if ( isSuperAdmin() || $group['name'] != 'SuperAdmin' ) {
				$group_id = $callObject->getFirstResultFromQuery("
					select pmUserCacheGroup_id
					from v_pmUserCacheGroup
					where pmUserCacheGroup_SysNick = :name
					limit 1
				", $group);
				if ($group_id) {
					$callObject->addGroupLink(array_merge($data, [
						'group' => $group_id,
						'id' => $user->pmuser_id
					]));
				}
			}
		}
		
		// перекэшируем организации пользователя
		$orgs = [];
		foreach ($user->org as $org) {
			$orgs[] = $org["org_id"];
		}
		$userdata = [];
		$userdata["pmuser_id"] = $user->pmuser_id;
		$callObject->ReCacheUserOrgs($userdata, $orgs);
		return true;
	}

	/**
	 * @param User_model $callObject
	 * @param $user
	 * @return bool
	 */
	public static function restoreUserOfCache(User_model $callObject, $user)
	{
		if (!$user || !$user["pmUser_id"]) {
			return false;
		}
		/**@var CI_DB_result $result */
		$queryParams = ["pmUser_id" => $user["pmUser_id"]];
		$query = "
			select pmUser_deleted as \"pmUser_deleted\"
			from pmUserCache
			where pmUser_id = :pmUser_id
		";
		$result = $callObject->db->query($query, $queryParams);
		$result = $result->result_array();
		if (@$result[0]["pmUser_deleted"] != null) {
			$query = "	
				update pmUserCache
				set
					pmUser_deleted = 1,
					pmUser_delDT = dbo.tzGetDate()
				where pmUser_id = :pmUser_id
			";
			$result = $callObject->db->query($query, $queryParams);
			return (is_object($result)) ? true : false;
		}
		return true;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveARMinDB(User_model $callObject, $data)
	{
		$procedure = "p_ARMType_" . (!empty($data["ARMType_id"]) ? "upd" : "ins");
		$selectString = "
		    armtype_id as \"ARMType_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    armtype_id := :ARMType_id,
			    armtype_code := :ARMType_Code,
			    armtype_name := :ARMType_Name,
			    armtype_sysnick := :ARMType_SysNick,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveGroupDB(User_model $callObject, $data)
	{
		$procedure = ((!isset($data["pmUserCacheGroup_id"])) || ($data["pmUserCacheGroup_id"] <= 0)) ? "p_pmUserCacheGroup_ins" : "p_pmUserCacheGroup_upd";
		$selectString = "
		    pmusercachegroup_id as \"pmUserCacheGroup_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    pmusercachegroup_id := :pmUserCacheGroup_id,
			    pmusercachegroup_code := :pmUserCacheGroup_SysNick,
			    pmusercachegroup_name := :pmUserCacheGroup_Name,
			    pmusercachegroup_parallelsessions := :pmUserCacheGroup_ParallelSessions,
			    pmusercachegroup_sysnick := :pmUserCacheGroup_SysNick,
			    pmusercachegroup_isblocked := :pmUserCacheGroup_IsBlocked,
			    pmuser_id := :pmUser_id
			);
		";
		$params = [
			"pmUserCacheGroup_id" => $data["pmUserCacheGroup_id"],
			"pmUserCacheGroup_Code" => $data["Group_id"],
			"pmUserCacheGroup_SysNick" => $data["Group_Code"],
			'pmUserCacheGroup_ParallelSessions' => $data['Group_ParallelSessions'],
			"pmUserCacheGroup_Name" => $data["Group_Name"],
			"pmUserCacheGroup_IsBlocked" => !empty($data['Group_IsBlocked']) ? 2 : 1,
			"pmUser_id" => pmAuthUser::find($data["session"]["login"])->pmuser_id
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @param $roles
	 * @return bool
	 */
	public static function saveObjectRole(User_model $callObject, $data, $roles)
	{
		$role = pmAuthGroups::loadRole($data["Role_id"]);
		$role = array_merge($role, [$data["node"] => $roles]);
		pmAuthGroups::saveRole($data["Role_id"], $role);
		return true;
	}

	/**
	 * @param User_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function saveReportARM(User_model $callObject, $data)
	{
		$proc = 'p_ReportARM_ins';
		$field = 'Report_id';
		$keyField = 'ReportARM_id';
		if (!empty($data['idField']) && $data['idField'] == 'ReportContentParameter_id') {
			$proc = 'p_ReportContentParameterLink_ins';
			$field = $data['idField'];
			$keyField = "ReportContentParameterLink_id";
		}
		$query = "
			select
			    {$keyField} as \"{$keyField}\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from rpt.{$proc} (
			    armtype_id := :ARMType_id,
			    {$field} := :{$field},
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param User_model $model
	 * @param $data
	 * @return array
	 */
	public static function getFirstLpuUnitTypeSysNickByMedStaffFact($model, $data) {

		$result = array();

		if (!empty($data['MedStaffFact_id'])) {
			$result = $model->getFirstResultFromQuery("
				select
					lut.LpuUnitType_SysNick
				from v_MedStaffFact msf
				left join v_LpuUnit lu on lu.LpuUnit_id = msf.LpuUnit_id
				left join v_LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
				where msf.MedStaffFact_id = :MedStaffFact_id
				limit 1
			", array('MedStaffFact_id' => $data['MedStaffFact_id']));
		}

		return $result;
	}
}
