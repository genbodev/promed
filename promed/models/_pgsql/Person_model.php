<?php
defined("BASEPATH") or die("No direct script access allowed");
require_once("Person_model_getCommon.php");
require_once("Person_model_get.php");
require_once("Person_model_load.php");
require_once("Person_model_check.php");
require_once("Person_model_common.php");
require_once("Person_model_edit.php");
require_once("Person_model_export.php");
require_once("Person_model_save.php");
/**
 * Person_model - модель, для работы с людьми
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       SWAN Developers
 * @version      ?
 * 
 * @property CI_DB_driver $db
 * @property CI_DB_driver $dbmodel
 * @property Options_model $Options_model
 * @property EvnPLDispDop13_model $EvnPLDispDop13_model
 * @property PersonIdentRequest_model $PersonIdentRequest_model
 * @property PersonIdentPackage_model $PersonIdentPackage_model
 * @property ServiceRPN_model $ServiceRPN_model
 * @property Replicator_model $Replicator_model
 */
class Person_model extends SwPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI";
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI";
	public $dateTimeForm110 = "MM-DD-YYYY";
	public $dateTimeForm112 = "YYYYMMDD";
	public $dateTimeFormUnixDate = "YYYY-MM-DD";
	public $dateTimeFormUnixTime = "HH24:MI:SS";
	public $dateTimeFormUnixFull = "YYYY-MM-DD HH24:MI:SS.MS";

	/**
	 * @var bool Загружать библиотеку swMongoCache?
	 */
	protected $loadMongoCacheLib = true;

	public $fromApi = false;
	public $exceptionOnValidation = false;

	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();
		if ($this->loadMongoCacheLib) {
			$this->load->library('swMongoCache');
		}
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function _setSaveResponse($key, $value){
		$this->_saveResponse[$key] = $value;
	}

	/**
	 * Сохранение данных о человеке
	 * Вызывается из формы редактирования человека
	 * @param $data
	 * @param bool $api
	 * @return array
	 * @throws Exception
	 */
	public function savePersonEditWindow($data, $api = false)
	{
		/**@var CI_DB_result $res */
		$this->load->library("textlog", ["file" => "Person_save.log"]);

		$IsSMPServer = $this->config->item("IsSMPServer");
		$IsLocalSMP = $this->config->item("IsLocalSMP");
		$is_superadmin = stripos($data["session"]["groups"], "SuperAdmin") !== false ? true : false;
		$needSnilsVerification = false;

		$region = "";
		if (isset($data["session"]["region"])) {
			$region = $data["session"]["region"]["nick"];
		}
		$isPerm = ($region == "perm") ? true : false;
		$isKrym = ($region == "krym") ? true : false;
		$is_ufa = ($region == "ufa") ? true : false;
		$is_kareliya = ($region == "kareliya") ? true : false;
		$is_kz = ($region == "kz") ? true : false;
		$is_ekb = ($region == "ekb") ? true : false;
		$is_astra = ($region == "astra") ? true : false;
		$is_bur = ($region == "buryatiya") ? true : false;
		$is_pskov = ($region == "pskov") ? true : false;
		$is_kaluga = ($region == "kaluga") ? true : false;
		$is_penza = ($region == "penza") ? true : false;
		$is_vologda = ($region == "vologda") ? true : false;
		if (($IsSMPServer || $IsLocalSMP ) && ($is_bur || $is_kareliya === true || $is_astra === true)  && empty($data['useSMP'])) {
			// подключаем основную бд
			unset($this->db);
			$this->db = $this->load->database("main", true);
		}
		if (!empty($data['useSMP']) && $data['useSMP'] == true && ($IsSMPServer || $IsLocalSMP ) && ($is_kareliya || $is_astra  || $is_ufa )) {
			// подключаем бд СМП
			unset($this->db);
			$this->db = $this->load->database("", true);
		}
		if (isset($data["UAddressSpecObject_Value"])) {
			if ($data["UAddressSpecObject_Value"] != "" && $data["UAddressSpecObject_id"] <= 0) {
				$data["UAddressSpecObject_id"] = $this->addSpecObject($data["UAddressSpecObject_Value"], $data["pmUser_id"]);
			}
		}
		if (isset($data["BAddressSpecObject_Value"])) {
			if ($data["BAddressSpecObject_Value"] != "" && $data["BAddressSpecObject_id"] <= 0) {
				$data["BAddressSpecObject_id"] = $this->addSpecObject($data["BAddressSpecObject_Value"], $data["pmUser_id"]);
			}
		}
		if (isset($data["PAddressSpecObject_Value"])) {
			if ($data["PAddressSpecObject_Value"] != "" && $data["PAddressSpecObject_id"] <= 0) {
				$data["PAddressSpecObject_id"] = $this->addSpecObject($data["PAddressSpecObject_Value"], $data["pmUser_id"]);
			}
		}
		if (empty($data["PersonIdentState_id"]) || !in_array(intval($data["PersonIdentState_id"]), [1, 2, 3, 4])) {
			$data["PersonIdentState_id"] = 0;
		}
		$person_is_identified =
			(($is_ufa === true || $is_kareliya === true || $is_pskov === true || $is_ekb === true) && $data["PersonIdentState_id"] != 0 && !empty($data["Person_identDT"]))
				? true
				: false;
		if (getRegionNick() == "kareliya" && $person_is_identified) {
			// проверяем полис, если закрыт, то признак БДЗ ставить не надо.
			if (!empty($data["Polis_endDate"]) && strtotime($data["Polis_endDate"]) < time()) {
				$person_is_identified = false;
			}
		}
		try {
			if ($this->regionNick == "vologda" && !empty($data["DocumentType_id"]) && !in_array($data["DocumentType_id"], [3, 9, 17, 19, 22]) && !empty($data["Document_begDate"]) && !empty($data["Person_BirthDay"]) && getCurrentAge($data["Person_BirthDay"], $data["Document_begDate"]) < 14) {
				throw new Exception("Дата выдачи документа должна соответствовать дате 14-летия пациента или должна быть позже. Укажите корректную дату выдачи и тип документа.");
			}
			$this->beginTransaction();
			$this->exceptionOnValidation = true;
			if (!isset($data['missSocStatus'])) {
				//Проверка на соответствие возраста соцстатусу https://redmine.swan.perm.ru/issues/40510
				$params_socstat = ["SocStatus_id" => $data["SocStatus_id"]];
				$query_socstat = "
					select
						SocStatus_SysNick as \"SocStatus_SysNick\",
					    SocStatus_AgeFrom as \"AgeFrom\",
					    SocStatus_AgeTo as \"AgeTo\"
					from v_SocStatus 
					where SocStatus_id = :SocStatus_id
				";
				/**@var CI_DB_result $result_socstat */
				$result_socstat = $this->db->query($query_socstat, $params_socstat);
				if (is_object($result_socstat)) {
					$result_socstat = $result_socstat->result_array();
					if (is_array($result_socstat) && count($result_socstat) > 0) {
						$socstat_sysnick = $result_socstat[0]["SocStatus_SysNick"];
						$days_diff = date_diff(new DateTime(), new DateTime($data["Person_BirthDay"]))->days; //Возраст в днях
						$years_diff = getCurrentAge($data["Person_BirthDay"]); //Возраст в годах
						$compare_param = $years_diff;
						if ($socstat_sysnick == "newborn") { //Если новорожденный, то в v_SocStatus диапазон возраста указан в днях, так что берем days_diff
							$compare_param = $days_diff;
						}
						if (isset($result_socstat[0]["AgeFrom"]) && isset($result_socstat[0]["AgeTo"])) {
							if (!(($compare_param >= $result_socstat[0]["AgeFrom"]) && ($compare_param <= $result_socstat[0]["AgeTo"]))) {
								$this->_saveResponse["type"] = "SocStatus";
								throw new Exception("Несоответствие социального статуса и возраста человека!");
							}
						}
					}
				}
			}
			$bdzData = [];
			$bdzFlag = false;
			$is_double = false;
			if ($person_is_identified && isset($data["BDZ_Guid"]) && $is_ufa) {
				$bdzData = $this->getBDZPersonData($data);
				if ($bdzData) {
					if ($data["mode"] == "add") {
						$data["mode"] = "edit";
						$data["Person_id"] = $bdzData["Person_id"];
						$data["Server_id"] = 0;
						$bdzFlag = true;
					} else {
						$is_double = true;
					}
				}
			}
			if ($bdzFlag) {
				foreach ($bdzData as $val => $item) {
					if ($val == "Person_BirthDay" && $item != "") {
						$item = date("Y-m-d", strtotime($item));
					}
					if ($val == "Person_deadDT" && $item != "") {
						$item = date("Y-m-d", strtotime($item));
					}
					if ($val == "Document_begDate" && $item != "") {
						$item = date("Y-m-d", strtotime($item));
					}
					if ($val == "Polis_begDate" && $item != "") {
						$item = date("Y-m-d", strtotime($item));
					}
					if ($val == "Polis_endDate" && $item != "") {
						$item = date("Y-m-d", strtotime($item));
					}
					if ($val == "PersonChild_invDate" && $item != "") {
						$item = date("Y-m-d", strtotime($item));
					}
					if (array_key_exists($val, $data)) {
						if (in_array($val, ["Person_SurName", "Person_FirName", "Person_SecName"])) {
							if (mb_strtoupper(trim((string)$data[$val])) !== mb_strtoupper(trim((string)$item))) {
								$newFields[$val] = $item;
							}
						} elseif (in_array($val, ["Person_SNILS"])) {
							if (trim((string)$data[$val]) !== str_replace("-", "", trim((string)$item))) {
								$newFields[$val] = $item;
							}
						} elseif (in_array($val, ["PersonPhone_Phone"])) {
							$replace_symbols = ["-", "(", ")", " "];
							if (trim((string)$data[$val]) !== str_replace($replace_symbols, "", trim((string)$item))) {
								$newFields[$val] = $item;
							}
						} else {
							if (trim((string)$data[$val]) !== trim((string)$item)) {
								$newFields[$val] = $item;

							}
						}
					}
				}
			} elseif ($data["mode"] != "add") {
				// оставим только изменившиеся поля
				$oldValues = explode("&", urldecode($data["oldValues"]));
				$newFields = [];
				foreach ($oldValues as $oldValue) {
					$val = explode("=", $oldValue);
					$fieldVal = "";
					$flag = false;
					foreach ($val as $item) {
						// первый пропускаем
						if (!$flag)
							$flag = true;
						else
							$fieldVal .= $item;
					}
					$item = toAnsi($item);
					if ($val[0] == "Person_BirthDay" && $item != "") {
						$item = date("Y-m-d", strtotime($item));
					}
					if ($val[0] == "Person_deadDT" && $item != "") {
						$item = date("Y-m-d", strtotime($item));
					}
					if ($val[0] == "Document_begDate" && $item != "") {
						$item = date("Y-m-d", strtotime($item));
					}
					if ($val[0] == "Polis_begDate" && $item != "") {
						$item = date("Y-m-d", strtotime($item));
					}
					if ($val[0] == "Polis_endDate" && $item != "") {
						$item = date("Y-m-d", strtotime($item));
					}
					if ($val[0] == "PersonChild_invDate" && $item != "") {
						$item = date("Y-m-d", strtotime($item));
					}
					if ($val[0] == "NationalityStatus_IsTwoNation" && $item != "") {
						$item = ($item == "true") ? 1 : 0;
					}
					if ($val[0] == "Person_IsUnknown" && $item != "") {
						if ($api) {
							$item = ($item == "true" || $item == 2) ? 1 : 0;
						} else {
							$item = ($item == "true") ? 1 : 0;
						}
					}
					if ($val[0] == "Person_IsAnonym" && $item != "") {
						$item = ($item == "true") ? 1 : 0;
					}
					if (array_key_exists($val[0], $data)) {
						if (in_array($val[0], ["Person_SurName", "Person_FirName", "Person_SecName"])) {
							if (getRegionNick() == "kz") {
								if (trim((string)$data[$val[0]]) !== trim((string)$item)) {
									$newFields[$val[0]] = $item;
								}
							} else {
								if (mb_strtoupper(trim((string)$data[$val[0]])) !== mb_strtoupper(trim((string)$item))) {
									$newFields[$val[0]] = $item;
								}
							}
						} else if (in_array($val[0], ["Person_SNILS"])) {
							if (trim((string)$data[$val[0]]) !== str_replace(["-", " "], "", trim((string)$item))) {
								$newFields[$val[0]] = $item;
							}
						} else {
							if (trim((string)$data[$val[0]]) !== trim((string)$item)) {
								$newFields[$val[0]] = $item;

							}
						}
					}
				}
				// атрибуты редактируются, очищаем инфо о человеке из сессии, но предварительно проверяем, он ли там записан, чтобы лишний раз в сессию не писать, экономим на спичках
				if (!isset($_SESSION)) {
					session_start();
				}
				if (isset($data["session"]["person"]) && isset($data["session"]["person"]["Person_id"]) && isset($data["Person_id"]) && $data["Person_id"] == $data["session"]["person"]["Person_id"]) {
					unset($_SESSION["person"]);
				}
				if (isset($data["session"]["person_short"]) && isset($data["session"]["person_short"]["Person_id"]) && isset($data["Person_id"]) && $data["Person_id"] == $data["session"]["person_short"]["Person_id"]) {
					unset($_SESSION["person_short"]);
				}
				session_write_close();
			}
			if ($data["mode"] == "add") {
				$newFields = $data;
				foreach ($newFields as $key => $value) {
					if (empty($value)) {
						unset($newFields[$key]);
					}
				}
				if ($api) {
					//параметры из API могут быть =0
					if (isset($data["Person_IsUnknown"]) && !isset($newFields["Person_IsUnknown"])) {
						$newFields["Person_IsUnknown"] = $data["Person_IsUnknown"];
					}
				}
			}
			$pid = $data["Person_id"];
			$server_id = $data["Server_id"];
			$flBDZ = false;
			$arr = ["Person_SurName", "Person_FirName", "Person_SecName", "Person_BirthDay", "PolisType", "OMSSprTerr_id", "Polis_Ser", "Polis_Num", "Federal_Num", "OrgSMO_id", "Polis_begDate", "Polis_endDate"];
			foreach ($arr as $value) {
				if (array_key_exists($value, $newFields)) {
					$flBDZ = true;
					break;
				}
			}
			$polisChange = false;
			if (array_key_exists("OMSSprTerr_id", $newFields) || array_key_exists("PolisType_id", $newFields) ||
				array_key_exists("Polis_Ser", $newFields) || array_key_exists("PolisFormType_id", $newFields) ||
				array_key_exists("Polis_Num", $newFields) || array_key_exists("OrgSMO_id", $newFields) ||
				array_key_exists("Polis_begDate", $newFields) || array_key_exists("Polis_endDate", $newFields) || array_key_exists("Federal_Num", $newFields)
			) {
				$polisChange = true;
			}
			$documentChange = false;
			if (array_key_exists("DocumentType_id", $newFields) || array_key_exists("Document_Ser", $newFields) ||
				array_key_exists("Document_Num", $newFields) || array_key_exists("OrgDep_id", $newFields) ||
				array_key_exists("Document_begDate", $newFields) || array_key_exists("KLCountry_id", $newFields) ||
				array_key_exists("NationalityStatus_IsTwoNation", $newFields)
			) {
				$documentChange = true;
			}
			$mainChange = false;//флаг для сброса идентификации при изменении основных данных
			if ($isPerm || $is_kareliya) {
				if (array_key_exists("Person_SurName", $newFields) || array_key_exists("Person_FirName", $newFields) ||
					array_key_exists("Person_SecName", $newFields) || array_key_exists("Person_BirthDay", $newFields) ||
					array_key_exists("Person_SNILS", $newFields) || $polisChange || $documentChange
				) {
					$mainChange = empty($data["Person_identDT"]);//не сбрасывать, если сохранение данных после идентификации
				}
			} elseif ($is_penza || $isKrym) {
				if (array_key_exists("Person_SurName", $newFields) || array_key_exists("Person_FirName", $newFields) ||
					array_key_exists("Person_SecName", $newFields) || array_key_exists("Person_BirthDay", $newFields) ||
					array_key_exists("Person_SNILS", $newFields) || array_key_exists("PersonSex_id", $newFields) ||
					$polisChange || $documentChange
				) {
					$mainChange = true;
				}
			} else {
				if (array_key_exists("Person_SurName", $newFields) || array_key_exists("Person_FirName", $newFields) ||
					array_key_exists("Person_SecName", $newFields) || array_key_exists("Person_BirthDay", $newFields) ||
					array_key_exists("PersonInn_Inn", $newFields) || array_key_exists("PersonSex_id", $newFields)
				) {
					$mainChange = true;
				}
			}
			if ((array_key_exists("Federal_Num", $newFields) && !empty($data["Federal_Num"]) && strlen($data["Federal_Num"]) !== 16) ||
				(!empty($data["Federal_Num"]) && strlen($data['Federal_Num']) !== 16)) {
				throw new Exception("Единый номер полиса должен иметь длину в 16 цифр");
			}
			// новая хранимка p_PersonAll_ins вызывается только при добавлении людей или обновления как минимум ФИО и ДР, иначе вызываются поштучные хранимки, так же вызывается  если регион Карелия изменено одно из полей ФИО ИЛИ ДР ИЛИ ПОЛИС.
			if (($data["mode"] == "add") || ($flBDZ == true && ($is_kareliya == true || $is_ufa == true || $is_astra == true)) || (array_key_exists("Person_SurName", $newFields) && array_key_exists("Person_FirName", $newFields) && array_key_exists("Person_SecName", $newFields) && array_key_exists("Person_BirthDay", $newFields))) {
				$queryParams = [
					"pmUser_id" => $data["pmUser_id"],
					"Server_id" => $person_is_identified ? 0 : $data["Server_id"],
					"Person_Guid" => !empty($data["Person_Guid"]) ? $data["Person_Guid"] : null,
					"Person_id" => $data["Person_id"],
					"Person_Comment" => !empty($data["Person_Comment"]) ? $data["Person_Comment"] : null
				];
				$subQuery = "";
				if ($person_is_identified) {
					$subQuery .= "
						,Person_identDT := :Person_identDT
						,PersonIdentState_id := :PersonIdentState_id
						,BDZ_Guid := :BDZ_Guid
					";
					$queryParams["BDZ_Guid"] = $data["BDZ_Guid"];
					$queryParams["Person_identDT"] = date("Y-m-d", $data["Person_identDT"]);
					$queryParams["PersonIdentState_id"] = $data["PersonIdentState_id"];
				}
				if (!empty($data["BDZ_id"])) {
					$subQuery .= ",BDZ_id = :BDZ_id";
					$queryParams["BDZ_id"] = $data["BDZ_id"];
				}
				if ($is_kareliya && !$person_is_identified) {
					$data["Person_IsInErz"] = null;
				} else if (isset($data["Person_IsInErz"]) && $data["Person_IsInErz"] == 1) {
					$data["Person_IsInErz"] = null;
				}
				$subQuery .= ",Person_IsInErz := :Person_IsInErz";
				$queryParams["Person_IsInErz"] = (isset($data["Person_IsInErz"])) ? $data["Person_IsInErz"] : null;
				if (array_key_exists("Person_SurName", $newFields)) {
					$subQuery .= ",PersonSurName_SurName := :PersonSurName_SurName";
					$queryParams["PersonSurName_SurName"] = $data["Person_SurName"];
				}
				if (array_key_exists("Person_FirName", $newFields)) {
					$subQuery .= ",PersonFirName_FirName := :PersonFirName_FirName";
					$queryParams["PersonFirName_FirName"] = $data["Person_FirName"];
				}
				if (array_key_exists("Person_SecName", $newFields)) {
					$subQuery .= ",PersonSecName_SecName := :PersonSecName_SecName";
					$queryParams["PersonSecName_SecName"] = $data["Person_SecName"];
				}
				if (array_key_exists("Person_BirthDay", $newFields)) {
					$subQuery .= ",PersonBirthDay_BirthDay := :PersonBirthDay_BirthDay";
					$queryParams["PersonBirthDay_BirthDay"] = $data["Person_BirthDay"];
				}
				if (array_key_exists("PersonSex_id", $newFields)) {
					$subQuery .= ",Sex_id := :Sex_id";
					$queryParams["Sex_id"] = $data["PersonSex_id"];
				}
				if (array_key_exists("Person_SNILS", $newFields)) {
					$subQuery .= ",PersonSnils_Snils := :PersonSnils_Snils";
					$queryParams["PersonSnils_Snils"] = $data["Person_SNILS"];
				}
				if (array_key_exists("SocStatus_id", $newFields)) {
					$subQuery .= ",SocStatus_id := :SocStatus_id";
					$queryParams["SocStatus_id"] = $data["SocStatus_id"];
				}
				if (array_key_exists("PersonPhone_Phone", $newFields)) {
					$subQuery .= ",PersonPhone_Phone := :PersonPhone_Phone";
					$queryParams["PersonPhone_Phone"] = $data["PersonPhone_Phone"];
				}
				if (array_key_exists("PersonInn_Inn", $newFields)) {
					$subQuery .= ",PersonInn_Inn := :PersonInn_Inn";
					$queryParams["PersonInn_Inn"] = $data["PersonInn_Inn"];
				}
				if ((isSuperadmin() || $person_is_identified) && array_key_exists("PersonSocCardNum_SocCardNum", $newFields)) {
					$subQuery .= ",PersonSocCardNum_SocCardNum := :PersonSocCardNum_SocCardNum";
					$queryParams["PersonSocCardNum_SocCardNum"] = $data["PersonSocCardNum_SocCardNum"];
				}
				if (array_key_exists("FamilyStatus_id", $newFields)) {
					$subQuery .= ",FamilyStatus_id := :FamilyStatus_id";
					$queryParams["FamilyStatus_id"] = $data["FamilyStatus_id"];
				}
				if (array_key_exists("PersonFamilyStatus_IsMarried", $newFields)) {
					$subQuery .= ",PersonFamilyStatus_IsMarried := :PersonFamilyStatus_IsMarried";
					$queryParams["PersonFamilyStatus_IsMarried"] = $data["PersonFamilyStatus_IsMarried"];
				}
				if (array_key_exists("Person_IsUnknown", $newFields)) {
					$subQuery .= ",Person_IsUnknown := :Person_IsUnknown";
					$queryParams["Person_IsUnknown"] = $data["Person_IsUnknown"] ? 2 : 1;
				}
				if (array_key_exists("Person_IsAnonym", $newFields)) {
					$subQuery .= ",Person_IsAnonym := :Person_IsAnonym";
					$queryParams["Person_IsAnonym"] = $data["Person_IsAnonym"] ? 2 : 1;
				}
				if (array_key_exists("Person_IsNotINN", $newFields)) {
					$subQuery .= ",Person_IsNotINN := :Person_IsNotINN";
					$queryParams["Person_IsNotINN"] = $data["Person_IsNotINN"];
				}
				if ($is_kareliya && $person_is_identified && !empty($data["Polis_begDate"])) {
					$subQuery .= ",PersonEvn_insDT := :PersonEvn_insDT";
					$queryParams["PersonEvn_insDT"] = $data["Polis_begDate"];
				}
				$subQuery .= ",pmUser_id := :pmUser_id";
				$query = "
					select
						Person_id as \"Pid\",
					    Person_Guid as \"Person_Guid\",
					    Error_Code as \"Error_Code\",
					    Error_Message as \"Error_Msg\"
					from dbo.p_PersonAll_ins(
						Person_id := :Person_id,
						Person_Guid := :Person_Guid,
						Server_id := :Server_id,
					    Person_Comment := :Person_Comment
					    {$subQuery}
					);
				";
				$res = $this->db->query($query, $queryParams);
				if (!is_object($res)) {
					throw new Exception("Ошибка при выполнении запроса к базе данных");
				}
				$rows = $res->result_array();
				if (!is_array($rows) || count($rows) == 0) {
					throw new Exception("Ошибки сохранения человека");
				} elseif (!empty($rows[0]["Error_Msg"])) {
					throw new Exception($rows[0]["Error_Msg"]);
				}
				$pid = $rows[0]["Pid"];
				$pguid = $rows[0]["Person_Guid"];
				if ($data["mode"] != "add" && empty($data["Person_IsInErz"])) {
					$query = "
						update 
							Person 
						set 
							Person_IsInErz = null, 
							Person_IsInFOMS = null 
						where 
							Person_id = :Person_id
					";
					$this->db->query($query, ["Person_id" => $data["Person_id"]]);
				}
				$needSnilsVerification = true;
			} else {
				// если не поменялось фио и др и это не добавление человека, то старый код..
				if ($person_is_identified) {
					// Проставляем человеку признак "из БДЗ
					$sql = "
						select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
						from dbo.p_Person_server(
							Person_id := :Person_id,
							Server_id := :Server_id,
							BDZ_Guid := :BDZ_Guid,
							pmUser_id := :pmUser_id
						);
					";
					$sqlParams = [
						"Person_id" => $pid,
						"Server_id" => 0,
						"BDZ_Guid" => $data["BDZ_Guid"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$res = $this->db->query($sql, $sqlParams);
					if (!is_object($res)) {
						throw new Exception("Ошибка при выполнении запроса к базе данных (проставление признака идентификации по сводной базе застрахованных)");
					}
					$response = $res->result_array();
					if (!is_array($response) || count($response) == 0) {
						throw new Exception("Ошибка при проставлении признака идентификации по сводной базе застрахованных");
					}
					if (!empty($response[0]["Error_Msg"])) {
						throw new Exception($response[0]["Error_Msg"]);
					}
					$sql = "
						select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
						from dbo.p_Person_ident(
							Person_id := :Person_id,
							Person_identDT := :Person_identDT,
							PersonIdentState_id := :PersonIdentState_id,
							pmUser_id := :pmUser_id
						);
					";
					$sqlParams = [
						"Person_id" => $pid,
						"Person_identDT" => date("Y-m-d", $data["Person_identDT"]),
						"PersonIdentState_id" => $data["PersonIdentState_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$res = $this->db->query($sql, $sqlParams);
					if (!is_object($res)) {
						throw new Exception("Ошибка при выполнении запроса к базе данных (обновление данных об идентификации по сводной базе застрахованных)");
					}
					$response = $res->result_array();
					if (!is_array($response) || count($response) == 0) {
						throw new Exception("Ошибка при обновлении данных об идентификации по сводной базе застрахованных");
					}
					if (!empty($response[0]["Error_Msg"])) {
						throw new Exception($response[0]["Error_Msg"]);
					}
				}
				if (($is_penza && $mainChange && $data["Person_IsInErz"] == 2) || (($is_kz || $is_kaluga || $is_kareliya) && ($mainChange || array_key_exists("Person_IsInErz", $newFields) || (!empty($data["Person_IsInErz"]) && $data["Person_IsInErz"] == 1))) || (array_key_exists("Person_IsUnknown", $newFields) || array_key_exists("Person_IsAnonym", $newFields) || array_key_exists("Person_IsNotINN", $newFields) || array_key_exists("BDZ_id", $newFields)) && !empty($data["Person_id"])) {
					$params = ["Person_id" => $data["Person_id"]];
					$sql = "
						select 
							P.Server_id as \"Server_id\",
							P.Person_id as \"Person_id\",
							P.Person_IsUnknown as \"Person_IsUnknown\",
							P.Person_IsAnonym as \"Person_IsAnonym\",
							P.Person_IsNotINN as \"Person_IsNotINN\",
							P.Person_IsDead as \"Person_IsDead\",
							P.Person_IsInErz as \"Person_IsInErz\",
							P.Person_IsInFOMS as \"Person_IsInFOMS\",
							P.BDZ_id as \"BDZ_id\",
							P.Lgot_id as \"Lgot_id\",
							P.ProMed_id as \"ProMed_id\",
							P.Person_Guid as \"Person_Guid\",
							to_char (P.Person_deadDT, '{$this->dateTimeFormUnixFull}') as \"Person_deadDT\",
							P.PersonCloseCause_id as \"PersonCloseCause_id\",
							to_char (P.Person_closeDT, '{$this->dateTimeFormUnixFull}') as \"Person_closeDT\",
							to_char (P.Person_MaxEvnDT, '{$this->dateTimeFormUnixFull}') as \"Person_MaxEvnDT\",
							to_char (P.Person_identDT, '{$this->dateTimeFormUnixFull}') as \"Person_identDT\",
							P.PersonIdentState_id as \"PersonIdentState_id\",
							P.Person_IsEncrypHIV as \"Person_IsEncrypHIV\"
						from Person P
						where P.Person_id = :Person_id
                        limit 1
					";
					$resp = $this->queryResult($sql, $params);
					if (!$this->isSuccessful($resp) || count($resp) == 0) {
						throw new Exception("Ошибка при запросе данных человека");
					}
					$resp[0]["BDZ_id"] = !empty($data["BDZ_id"]) ? $data["BDZ_id"] : null;
					if (array_key_exists("Person_IsUnknown", $newFields)) {
						$resp[0]["Person_IsUnknown"] = (isset($data["Person_IsUnknown"]) && $data["Person_IsUnknown"]) ? 2 : 1;
					}
					if (array_key_exists("Person_IsAnonym", $newFields)) {
						$resp[0]["Person_IsAnonym"] = (!empty($data["Person_IsAnonym"]) && $data["Person_IsAnonym"]) ? 2 : 1;
					}
					if (array_key_exists("Person_IsNotINN", $newFields)) {
						$resp[0]["Person_IsNotINN"] = (!empty($data["Person_IsNotINN"])) ? $data["Person_IsNotINN"] : null;
					}
					$resp[0]["pmUser_id"] = $data["pmUser_id"];
					if ($is_kareliya) {
						$resp[0]["Person_IsInErz"] = (($mainChange && !array_key_exists("Person_IsInErz", $newFields)) || empty($data["Person_IsInErz"])) ? null : $data["Person_IsInErz"];
					}
					if ($is_kz || $is_kaluga) {
						$resp[0]["Person_IsInErz"] = ($mainChange || empty($data["Person_IsInErz"]) || $data["Person_IsInErz"] == 1) ? null : $data["Person_IsInErz"];
					}
					if ($is_penza) {
						$resp[0]["Person_IsInErz"] = ($mainChange && $data["Person_IsInErz"] == 2) ? 1 : $data["Person_IsInErz"];
					}
					if ($is_kz && $mainChange) {
						$resp[0]['Person_IsInFOMS'] = null;
					}
					$sql = "
						select
							Person_id as \"Person_id\",
						    Error_Code as \"Error_Code\",
						    Error_Message as \"Error_Msg\"
						from dbo.p_Person_upd(
							Person_id := :Person_id,
							Server_id := :Server_id,
							Person_IsUnknown := :Person_IsUnknown,
							Person_IsAnonym := :Person_IsAnonym,
							Person_IsNotINN := :Person_IsNotINN,
							Person_IsDead := :Person_IsDead,
							Person_IsInErz := :Person_IsInErz,
							Person_IsInFOMS := :Person_IsInFOMS,
							BDZ_id := :BDZ_id,
							Lgot_id := :Lgot_id,
							ProMed_id := :ProMed_id,
							Person_Guid := :Person_Guid,
							Person_deadDT := :Person_deadDT,
							PersonCloseCause_id := :PersonCloseCause_id,
							Person_closeDT := :Person_closeDT,
							Person_MaxEvnDT := :Person_MaxEvnDT,
							Person_identDT := :Person_identDT,
							PersonIdentState_id := :PersonIdentState_id,
							Person_IsEncrypHIV := :Person_IsEncrypHIV,
							pmUser_id := :pmUser_id
						);
					";
					$res = $this->db->query($sql, $resp[0]);
					$this->ValidateInsertQuery($res);
				}
				if (array_key_exists("Person_SurName", $newFields)) {
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonSurName_ins(Server_id := ?,Person_id := ?,PersonSurName_SurName := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$server_id, $pid, $data["Person_SurName"], $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
					$needSnilsVerification = true;
				}
				if (array_key_exists("Person_FirName", $newFields)) {
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonFirName_ins(Server_id := ?,Person_id := ?,PersonFirName_FirName := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$server_id, $pid, $data["Person_FirName"], $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
					$needSnilsVerification = true;
				}
				if (array_key_exists("Person_SecName", $newFields)) {
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonSecName_ins(Server_id := ?,Person_id := ?,PersonSecName_SecName := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$server_id, $pid, $data["Person_SecName"], $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
				}
				if ((isSuperadmin() || $person_is_identified) && array_key_exists("PersonSocCardNum_SocCardNum", $newFields)) {
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonSocCardNum_ins(Server_id := ?,Person_id := ?,PersonSocCardNum_SocCardNum := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$server_id, $pid, $data["PersonSocCardNum_SocCardNum"], $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
				}
				if (array_key_exists("PersonPhone_Phone", $newFields)) {
					$replace_symbols = ["-", "(", ")", " "];
					if (str_replace($replace_symbols, "", $newFields["PersonPhone_Phone"]) != $data["PersonPhone_Phone"]) {
						$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonPhone_ins(Server_id := ?,Person_id := ?,PersonPhone_Phone := ?,PersonPhone_Comment := ?,pmUser_id := ?);";
						$res = $this->db->query($sql, [$server_id, $pid, $data["PersonPhone_Phone"], "", $data["pmUser_id"]]);
						$this->ValidateInsertQuery($res);
					}
				}
				if (array_key_exists("Person_Comment", $newFields)) {
					$query_upd = "update Person set Person_Comment = ? where Person_id = ?";
					$this->db->query($query_upd, [$data["Person_Comment"], $pid]);
				}
				if (array_key_exists("PersonInn_Inn", $newFields)) {
					$data["PersonInn_Inn"] = ($data["PersonInn_Inn"]) ? $data["PersonInn_Inn"] : null;
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonInn_ins(Server_id := ?,Person_id := ?,PersonInn_Inn := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$server_id, $pid, $data["PersonInn_Inn"], $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
				}
				if (array_key_exists("Person_BirthDay", $newFields)) {
					$date = empty($data["Person_BirthDay"]) ? null : $data["Person_BirthDay"];
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonBirthDay_ins(Server_id := ?,Person_id := ?,PersonBirthDay_BirthDay := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$server_id, $pid, $date, $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
				}
				if (array_key_exists("Person_SNILS", $newFields)) {
					$serv_id = ($is_superadmin || $person_is_identified) ? 1 : $server_id;
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonSnils_ins(Server_id := ?,Person_id := ?,PersonSnils_Snils := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$serv_id, $pid, $data["Person_SNILS"], $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
					$needSnilsVerification = true;
				}
				if (array_key_exists("PersonSex_id", $newFields)) {
					$Sex_id = (!isset($data["PersonSex_id"]) || !is_numeric($data["PersonSex_id"]) ? null : $data["PersonSex_id"]);
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonSex_ins(Server_id := ?,Person_id := ?,Sex_id := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$server_id, $pid, $Sex_id, $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
				}
				if (array_key_exists("SocStatus_id", $newFields)) {
					$SocStatus_id = (empty($data["SocStatus_id"]) ? null : $data["SocStatus_id"]);
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonSocStatus_ins(Server_id := ?,Person_id := ?,SocStatus_id := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$server_id, $pid, $SocStatus_id, $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
				}
				if (array_key_exists("FamilyStatus_id", $newFields) || array_key_exists("PersonFamilyStatus_IsMarried", $newFields)) {
					$serv_id = $server_id;
					$FamilyStatus_id = (empty($data["FamilyStatus_id"]) ? null : $data["FamilyStatus_id"]);
					$PersonFamilyStatus_IsMarried = (empty($data["PersonFamilyStatus_IsMarried"]) ? null : $data["PersonFamilyStatus_IsMarried"]);
					if (empty($PersonFamilyStatus_IsMarried) && empty($FamilyStatus_id)) {
						throw new Exception("Хотя бы одно из полей \"Семейное положение\" или \"Состоит в зарегистрированном браке\" должно быть заполнено");
					}
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonFamilyStatus_ins(Server_id := ?,Person_id := ?,FamilyStatus_id := ?,PersonFamilyStatus_IsMarried := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$serv_id, $pid, $FamilyStatus_id, $PersonFamilyStatus_IsMarried, $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
				}
			}
			if (array_key_exists("Person_deadDT", $newFields)) {
				$deadDT = $this->getFirstResultFromQuery("
					select to_char(Person_deadDT, '{$this->dateTimeForm120}') as \"Person_deadDT\"
					from Person 
					where Person_id = :Person_id
					limit 1
				", ["Person_id" => $pid]);
				if (empty($data["Person_deadDT"]) && !empty($deadDT)) {
					$funcParams = ["Person_id" => $pid, "pmUser_id" => $data["pmUser_id"]];
					$resp = $this->revivePerson($funcParams);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]["Error_Msg"]);
					}
				} elseif (!empty($data["Person_deadDT"])) {
					$funcParams = ["Person_id" => $pid, "Person_deadDT" => $data["Person_deadDT"], "pmUser_id" => $data["pmUser_id"]];
					$resp = $this->killPerson($funcParams);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]["Error_Msg"]);
					}
				}
			}
			$query_persdeputy = "
					select
						PersonDeputy_id as \"PersonDeputy_id\",
						DocumentDeputy_id as \"DocumentDeputy_id\"
					from v_PersonDeputy
					where Person_id = :Person_id
					limit 1
				";
			$result_persdeputy = $this->db->query($query_persdeputy, array('Person_id' => $data['Person_id']))->result('array');

			if (!empty($data['DeputyKind_id']) && !empty($data['DeputyPerson_id'])) {

				$procedure = !empty($result_persdeputy[0]['DocumentDeputy_id']) ? 'p_DocumentDeputy_upd' : 'p_DocumentDeputy_ins';

				$query = "
				select DocumentDeputy_id as \"DocumentDeputy_id\",
						Error_Code as \"ErrCode\",
						Error_Message as \"ErrMsg\"
				from {$procedure}(
						DocumentDeputy_id := :DocumentDeputy_id,
						DocumentAuthority_id := :DocumentAuthority_id,
						DocumentDeputy_Ser := :DocumentDeputy_Ser,
						DocumentDeputy_Num := :DocumentDeputy_Num,
						DocumentDeputy_Issue := :DocumentDeputy_Issue,
						DocumentDeputy_begDate := :DocumentDeputy_begDate,
						pmUser_id := :pmUser_id);
					";
				$queryParams = array(
					'DocumentDeputy_id' => (!empty($result_persdeputy[0]['DocumentDeputy_id']) ? $result_persdeputy[0]['DocumentDeputy_id'] : NULL),
					'DocumentAuthority_id' => $data['DocumentAuthority_id'],
					'DocumentDeputy_Ser' => $data['DocumentDeputy_Ser'],
					'DocumentDeputy_Num' => $data['DocumentDeputy_Num'],
					'DocumentDeputy_Issue' => $data['DocumentDeputy_Issue'],
					'DocumentDeputy_begDate' => $data['DocumentDeputy_begDate'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->db->query($query, $queryParams)->result('array');

				if ( is_array($result) ) {
					$procedure = !empty($result_persdeputy[0]['PersonDeputy_id']) ? 'p_PersonDeputy_upd' : 'p_PersonDeputy_ins';
					$query = "
					select Error_Code as \"ErrCode\",
					 		Error_Message as \"ErrMsg\"

					from {$procedure}(
						PersonDeputy_id := :PersonDeputy_id,
						Server_id := :Server_id,
						Person_id := :Person_id,
						Person_pid := :Person_pid,
						DeputyKind_id := :DeputyKind_id,
						pmUser_id := :pmUser_id,
						DocumentDeputy_id := :DocumentDeputy_id);
					";

					$this->db->query($query, array(
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'DeputyKind_id' => $data['DeputyKind_id'],
						'Person_pid' => $data['DeputyPerson_id'],
						'DocumentDeputy_id' => $result[0]['DocumentDeputy_id'],
						'PersonDeputy_id' => !empty($result_persdeputy[0]['PersonDeputy_id']) ? $result_persdeputy[0]['PersonDeputy_id'] : null,
						'pmUser_id' => $data['pmUser_id']));
				}
			} else {
				if(empty($data['DeputyPerson_id'])) {
					if (!empty($result_persdeputy[0]['PersonDeputy_id'])) {
						$query = "
							select Error_Code as \"ErrCode\",
							 Error_Message as \"ErrMsg\"
							from  p_PersonDeputy_del(
							PersonDeputy_id := :PersonDeputy_id)
						";
						$this->db->query($query, array('PersonDeputy_id' => $result_persdeputy[0]['PersonDeputy_id']));
					}

					if (!empty($result_persdeputy[0]['DocumentDeputy_id'])) {
						$query = "
						select Error_Code as \"ErrCode\",
						Error_Message as \"ErrMsg\"
						from p_DocumentDeputy_del(
						DocumentDeputy_id := :DocumentDeputy_id)
						";
						$this->db->query($query, array('DocumentDeputy_id' => $result_persdeputy[0]['DocumentDeputy_id']));
					}
				}
			}
			$terr_dop_change = ["P" => false, "U" => false, "B" => false];
			if (array_key_exists("PersonNationality_id", $newFields) ||
				array_key_exists("Ethnos_id", $newFields) ||
				($is_astra && isset($data["rz"]) && $data["rz"] != null) ||
				($is_ufa === true && array_key_exists("PPersonSprTerrDop_id", $newFields)) ||
				($is_ufa === true && array_key_exists("UPersonSprTerrDop_id", $newFields)) ||
				($is_ufa === true && array_key_exists("BPersonSprTerrDop_id", $newFields))
			) {
				//проверяем, есть ли уже запись на этого персона в этой таблице
				$sql = "
					select 
						PersonInfo_id as \"PersonInfo_id\",
						PersonInfo_InternetPhone as \"PersonInfo_InternetPhone\",
						UPersonSprTerrDop_id as \"UPersonSprTerrDop_id\",
						PPersonSprTerrDop_id as \"PPersonSprTerrDop_id\",
						BPersonSprTerrDop_id as \"BPersonSprTerrDop_id\",
						Person_BDZCode as \"Person_BDZCode\",
						PersonInfo_IsSetDeath as \"PersonInfo_IsSetDeath\",
						PersonInfo_IsParsDeath as \"PersonInfo_IsParsDeath\",
						PersonInfo_Email as \"PersonInfo_Email\"
					from PersonInfo
					where Person_id = :Person_id
					order by PersonInfo_updDT desc
					limit 1
				";
				$res = $this->db->query($sql, ["Person_id" => $pid]);
				if (is_object($res)) {
					$rows = $res->result_array();
					$procedure = (!is_array($rows) || count($rows) == 0) ? "p_PersonInfo_ins" : "p_PersonInfo_upd";
					if (!is_array($rows) || count($rows) == 0) {
						$rows = [[
							"PersonInfo_id" => null,
							"PersonInfo_InternetPhone" => null
						]];
						$terr_dop_change = ["P" => true, "U" => true, "B" => true];
					} else {
						if (empty($data["rz"])) {
							$data["rz"] = $rows[0]["Person_BDZCode"];
						}
						$terr_dop_change = [
							"P" => ($rows[0]["PPersonSprTerrDop_id"] != $data["PPersonSprTerrDop_id"]),
							"U" => ($rows[0]["UPersonSprTerrDop_id"] != $data["UPersonSprTerrDop_id"]),
							"B" => ($rows[0]["BPersonSprTerrDop_id"] != $data["BPersonSprTerrDop_id"])
						];
					}
					// выполняем хранимку
					$selectString = "Error_Message as \"ErrMsg\"";
					$sql = "
					   select {$selectString}
					   from dbo.{$procedure}(
						   Server_id := :Server_id,
						   PersonInfo_id := :PersonInfo_id,
						   Person_id := :Person_id,
						   UPersonSprTerrDop_id := :UPersonSprTerrDop_id,
						   PPersonSprTerrDop_id := :PPersonSprTerrDop_id,
						   BPersonSprTerrDop_id := :BPersonSprTerrDop_id,
						   PersonInfo_InternetPhone := :PersonInfo_InternetPhone,
						   Nationality_id := :Nationality_id,
						   Ethnos_id := :Ethnos_id,
						   PersonInfo_IsSetDeath := :PersonInfo_IsSetDeath,
						   PersonInfo_IsParsDeath := :PersonInfo_IsParsDeath,
						   PersonInfo_Email := :PersonInfo_Email,
						   Person_BDZCode := :rz,
						   pmUser_id := :pmUser_id
					   );
					";
					$sqlParams = [
						"Server_id" => $server_id,
						"PersonInfo_id" => $rows[0]["PersonInfo_id"],
						"Person_id" => $pid,
						"UPersonSprTerrDop_id" => (!empty($data["UPersonSprTerrDop_id"]) ? $data["UPersonSprTerrDop_id"] : null),
						"PPersonSprTerrDop_id" => (!empty($data["PPersonSprTerrDop_id"]) ? $data["PPersonSprTerrDop_id"] : null),
						"BPersonSprTerrDop_id" => (!empty($data["BPersonSprTerrDop_id"]) ? $data["BPersonSprTerrDop_id"] : null),
						"PersonInfo_InternetPhone" => (!empty($rows[0]["PersonInfo_InternetPhone"]) ? $rows[0]["PersonInfo_InternetPhone"] : null),
						"PersonInfo_IsSetDeath" => (!empty($rows[0]["PersonInfo_IsSetDeath"]) ? $rows[0]["PersonInfo_IsSetDeath"] : null),
						"PersonInfo_IsParsDeath" => (!empty($rows[0]["PersonInfo_IsParsDeath"]) ? $rows[0]["PersonInfo_IsParsDeath"] : null),
						"PersonInfo_Email" => (!empty($rows[0]["PersonInfo_Email"]) ? $rows[0]["PersonInfo_Email"] : null),
						"Nationality_id" => (!empty($data["PersonNationality_id"]) ? $data["PersonNationality_id"] : null),
						"Ethnos_id" => (!empty($data["Ethnos_id"]) ? $data["Ethnos_id"] : null),
						"rz" => (!empty($data["rz"]) ? $data["rz"] : null),
						"pmUser_id" => $data["pmUser_id"]
					];
					$res = $this->db->query($sql, $sqlParams);
					$this->ValidateInsertQuery($res);
				}
			}
			if (isSuperadmin() && array_key_exists("PersonRefuse_IsRefuse", $newFields)) {
				if (isset($data["PersonRefuse_IsRefuse"])) {
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonRefuse_ins (Person_id := CAST(? as bigint),PersonRefuse_IsRefuse := CAST(? as bigint),PersonRefuse_Year := CAST(date_part('year', dbo.tzGetDate()) as integer),pmUser_id := ?);";
					$res = $this->db->query($sql, [$pid, $data["PersonRefuse_IsRefuse"], $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
				}
			}
			if (array_key_exists("PersonHeight_Height", $newFields) || array_key_exists("HeightAbnormType_id", $newFields) || array_key_exists("PersonHeight_IsAbnorm", $newFields)) {
				$ins_dt = date("Y-m-d H:i:s", time());
				$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonHeight_ins(Server_id := ?,Person_id := ?,PersonHeight_setDT := ?,PersonHeight_Height := ?,HeightAbnormType_id := ?,PersonHeight_IsAbnorm := ?,Okei_id := 2,pmUser_id := ?);";
				$res = $this->db->query($sql, [$server_id, $pid, $ins_dt, $data["PersonHeight_Height"], $data["HeightAbnormType_id"], $data["PersonHeight_IsAbnorm"], $data["pmUser_id"]]);
				$this->ValidateInsertQuery($res);
			}
			if (array_key_exists("PersonWeight_Weight", $newFields) || (array_key_exists("Okei_id", $newFields) && !empty($data["PersonWeight_Weight"])) || array_key_exists("WeightAbnormType_id", $newFields) || array_key_exists("PersonWeight_IsAbnorm", $newFields)) {
				$ins_dt = date("Y-m-d H:i:s", time());
				$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonWeight_ins(Server_id := ?,Person_id := ?,PersonWeight_setDT := ?,PersonWeight_Weight := ?,WeightAbnormType_id := ?,PersonWeight_IsAbnorm := ?,WeightMeasureType_id := 3,Okei_id := ?,pmUser_id := ?);";
				$res = $this->db->query($sql, [$server_id, $pid, $ins_dt, $data["PersonWeight_Weight"], $data["WeightAbnormType_id"], $data["PersonWeight_IsAbnorm"], $data["Okei_id"], $data["pmUser_id"]]);
				$this->ValidateInsertQuery($res);
			}
			if (array_key_exists("PersonChildExist_IsChild", $newFields)) {
				$PersonChildExist_IsChild = (empty($data["PersonChildExist_IsChild"]) ? null : $data["PersonChildExist_IsChild"]);
				$ins_dt = date("Y-m-d H:i:s", time());
				$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonChildExist_ins(Server_id := ?,Person_id := ?,PersonChildExist_setDT := ?,PersonChildExist_IsChild := ?,pmUser_id := ?);";
				$res = $this->db->query($sql, [$server_id, $pid, $ins_dt, $PersonChildExist_IsChild, $data["pmUser_id"]]);
				$this->ValidateInsertQuery($res);
			}
			if (array_key_exists("PersonCarExist_IsCar", $newFields)) {
				$PersonCarExist_IsCar = (empty($data["PersonCarExist_IsCar"]) ? null : $data["PersonCarExist_IsCar"]);
				$ins_dt = date("Y-m-d H:i:s", time());
				$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonCarExist_ins(Server_id := ?,Person_id := ?,PersonCarExist_setDT := ?,PersonCarExist_IsCar := ?,pmUser_id := ?);";
				$res = $this->db->query($sql, [$server_id, $pid, $ins_dt, $PersonCarExist_IsCar, $data["pmUser_id"]]);
				$this->ValidateInsertQuery($res);
			}
			if (array_key_exists("Ethnos_id", $newFields) || array_key_exists("OnkoOccupationClass_id", $newFields)) {
				$Ethnos_id = (empty($data["Ethnos_id"]) ? null : $data["Ethnos_id"]);
				$OnkoOccupationClass_id = (empty($data["OnkoOccupationClass_id"]) ? null : $data["OnkoOccupationClass_id"]);
				$MorbusOnkoPerson_id = null;
				$procedure = "p_MorbusOnkoPerson_ins";
				$sql = "
					select MorbusOnkoPerson_id as \"MorbusOnkoPerson_id\"
					from v_MorbusOnkoPerson 
					where Person_id = ?
					order by MorbusOnkoPerson_insDT desc
					limit 1
				";
				$res = $this->db->query($sql, [$pid]);
				if (is_object($res)) {
					$resp = $res->result_array();
					if (count($resp) > 0) {
						$MorbusOnkoPerson_id = $resp[0]["MorbusOnkoPerson_id"];
						$procedure = "p_MorbusOnkoPerson_upd";
					}
				}
				$selectString = "Error_Message as \"ErrMsg\"";
				$sql = "select {$selectString} from dbo.{$procedure}(Person_id := ?,Ethnos_id := ?,OnkoOccupationClass_id := ?,pmUser_id := ?,MorbusOnkoPerson_id := ?);";
				$res = $this->db->query($sql, [$pid, $Ethnos_id, $OnkoOccupationClass_id, $data["pmUser_id"], $MorbusOnkoPerson_id]);
				$this->ValidateInsertQuery($res);
			}
			if (array_key_exists("Post_id", $newFields) || (isset($data["PostNew"]) && !empty($data["PostNew"])) || array_key_exists("Org_id", $newFields) || array_key_exists("OrgUnion_id", $newFields) || (!empty($data["OrgUnionNew"]))) {
				$Post_id = (empty($data["Post_id"]) ? null : $data["Post_id"]);
				$Org_id = (empty($data["Org_id"]) ? null : $data["Org_id"]);
				$OrgUnion_id = (empty($data["OrgUnion_id"]) ? null : $data["OrgUnion_id"]);
				if (isset($data["PostNew"]) && !empty($data["PostNew"])) {
					/**@var CI_DB_result $result */
					if (is_numeric($data["PostNew"])) {
						$numPostID = 1;
						$sql = "
							select Post_id as \"Post_id\"
							from v_Post
							where Post_id = ?
							limit 1
						";
						$result = $this->db->query($sql, [$data["PostNew"]]);
					} else {
						$sql = "
							select Post_id as \"Post_id\"
							from v_Post
							where Post_Name iLIKE ? and Server_id = ?
							limit 1
						";
						$result = $this->db->query($sql, [$data["PostNew"], $server_id]);
					}
					if (is_object($result)) {
						$sel = $result->result_array();
						if (isset($sel[0])) {
							if ($sel[0]["Post_id"] > 0) {
								$Post_id = $sel[0]["Post_id"];
							}
						} elseif (isset($numPostID)) {
							$Post_id = null;
						} else {
							$sql = "select Post_id as \"Post_id\" from dbo.p_Post_ins(Post_Name := ?,pmUser_id := ?,Server_id := ?);";
							$result = $this->db->query($sql, [$data["PostNew"], $data["pmUser_id"], $server_id]);
							if (is_object($result)) {
								$sel = $result->result_array();
								if ($sel[0]["Post_id"] > 0) {
									$Post_id = $sel[0]["Post_id"];
								}
							}
						}
					}
				}
				if (isset($data["OrgUnionNew"]) && !empty($data["OrgUnionNew"]) && !empty($data["Org_id"]) && is_numeric($data["Org_id"])) {
					if (is_numeric($data["OrgUnionNew"])) {
						$numOrgUnionID = 1;
						$sql = "
							select OrgUnion_id as \"OrgUnion_id\"
							from v_OrgUnion
							where OrgUnion_id = ?
						";
						$result = $this->db->query($sql, [$data["OrgUnionNew"]]);
					} else {
						$sql = "
							select OrgUnion_id as \"OrgUnion_id\"
							from v_OrgUnion
							where OrgUnion_Name iLIKE ? and Server_id = ? and Org_id = ?

						";
						$result = $this->db->query($sql, [$data["OrgUnionNew"], $server_id, $data["Org_id"]]);
					}
					if (is_object($result)) {
						$sel = $result->result_array();
						if (isset($sel[0])) {
							if ($sel[0]["OrgUnion_id"] > 0) {
								$OrgUnion_id = $sel[0]["OrgUnion_id"];
							}
						} elseif (isset($numOrgUnionID)) {
							$OrgUnion_id = null;
						} else {
							$sql = "select orgunion_id as \"OrgUnion_id\" from dbo.p_OrgUnion_ins(OrgUnion_Name := ?,Org_id := ?,pmUser_id := ?,Server_id := ?);";
							$result = $this->db->query($sql, [$data["OrgUnionNew"], $data["Org_id"], $data["pmUser_id"], $server_id]);
							if (is_object($result)) {
								$sel = $result->result_array();
								if ($sel[0]["OrgUnion_id"] > 0) {
									$OrgUnion_id = $sel[0]["OrgUnion_id"];
								}
							}
						}
					}
				}
				if (!isset($Org_id)) {
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonJob_del(Server_id := ?,Person_id := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$server_id, $pid, $data["pmUser_id"]]);
				} else {
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonJob_ins(Server_id := ?,Person_id := ?,Org_id := ?,OrgUnion_id := ?,Post_id := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$server_id, $pid, $Org_id, $OrgUnion_id, $Post_id, $data["pmUser_id"]]);
				}
				$this->ValidateInsertQuery($res);
			}
			if($polisChange){
				//получаем последние данные по полису
				$policy = $this->getLastPeriodicalsByPolicy(array('Person_id' => $pid));
				// #174743. Если в блоке полей «Полис» поле «Территория» не заполнено и в активном состоянии человека отсутствует полис,
				//то действия с полисом в активном состоянии человека и периодикой «Полис» не производятся.
				if(empty($data['OMSSprTerr_id']) && empty($policy['Polis_id'])) $polisChange = false;
			}
			if ($polisChange) {
				$OmsSprTerr_id = (empty($data["OMSSprTerr_id"]) ? null : $data["OMSSprTerr_id"]);
				$PolisType_id = (empty($data["PolisType_id"]) ? null : $data["PolisType_id"]);
				$OrgSmo_id = (empty($data["OrgSMO_id"]) ? null : $data["OrgSMO_id"]);
				$Polis_Ser = (empty($data["Polis_Ser"]) ? "" : $data["Polis_Ser"]);
				$PolisFormType_id = (empty($data["PolisFormType_id"]) ? null : $data["PolisFormType_id"]);
				$Polis_Num = (empty($data["Polis_Num"]) ? "" : $data["Polis_Num"]);
				$Polis_begDate = empty($data["Polis_begDate"]) ? null : $data["Polis_begDate"];
				$Polis_endDate = empty($data["Polis_endDate"]) ? null : $data["Polis_endDate"];
				if ($PolisType_id == 4) {
					$Polis_Num = (empty($data["Federal_Num"]) ? "" : $data["Federal_Num"]);
					if (array_key_exists("Federal_Num", $newFields)) {
						$newFields["Polis_Num"] = $data["Federal_Num"];
					}
				}
				if ($is_ufa && empty($OmsSprTerr_id) && (!empty($PolisType_id) || !empty($OrgSmo_id) || !empty($Polis_Ser) || !empty($Polis_Num) || !empty($Polis_begDate) || !empty($Polis_endDate))) {
					$this->textlog->add("Сохранение человека с полисными данными без указанной территории страхования. Person_id: {$pid}, PolisType_id: {$PolisType_id}, OrgSmo_id: {$OrgSmo_id}, Polis_Ser: {$Polis_Ser}, Polis_Num: {$PolisType_id}, Polis_Num: {$PolisType_id}, Polis_begDate: {$Polis_begDate}, Polis_endDate: {$Polis_endDate}");
					$PolisType_id = null;
					$OrgSmo_id = null;
					$Polis_Ser = "";
					$Polis_Num = "";
					$Polis_begDate = null;
					$Polis_endDate = null;
				}
				// если человек из БДЗ и можем добавлять иннотериториальный полис
				if (!$is_ekb && !$is_astra && !$is_vologda && !$is_superadmin && !($is_pskov && $data["PersonIdentState_id"] != 0 && !empty($data["Person_identDT"])) && !$person_is_identified && isset($data["Polis_CanAdded"]) && $data["Polis_CanAdded"] == 1) {
					// проверяем, иная ли территория
					$sql = "
						select KLRgn_id as \"KLRgn_id\"
						from OMSSprTerr
						where OMSSprTerr_id = ?
					";
					$res = $this->db->query($sql, [$OmsSprTerr_id]);
					$sel = $res->result_array();
					if (count($sel) == 0) {
						throw new Exception("Не найдены данные о регионе.");
					}
					$region = $data["session"]["region"];
					if (!(isset($region) && isset($region["number"]) && $region["number"] > 0 && isset($sel[0]["KLRgn_id"]) && $sel[0]["KLRgn_id"] > 0 && $sel[0]["KLRgn_id"] != $region["number"])) {
						throw new Exception("Регион инотериториального полиса совпадает с регионом текущей ЛПУ.");
					}
					//Изменился единый номер
					$Federal_Num = (empty($data["Federal_Num"]) ? "" : $data["Federal_Num"]);
					if (array_key_exists("Federal_Num", $newFields)) {
						if ($Federal_Num == "" && $PolisType_id == 4) {
							throw new Exception("Поле Ед. номер не может быть пустым");
						}
					}
					$fsql = "
						select
							PersonEvn_id as \"PersonEvn_id\",
							Server_id as \"Server_id\"
						from v_Person_all
						where PersonEvnClass_id = 16
						  and Person_id = :Person_id
						  and PersonEvn_insDT <= :begdate
						order by
							PersonEvn_insDT desc,
							PersonEvn_TimeStamp desc
						limit 1
					";
					/**@var CI_DB_result $fres */
					$fres = $this->db->query($fsql, ["Person_id" => $pid, "edNum" => $Federal_Num, "begdate" => $Polis_begDate]);
					if (is_object($fres)) {
						$fsel = $fres->result_array();
						if (count($fsel) == 0) {
							$checkEdNum = $this->checkPesonEdNumOnDate(["Person_id" => $pid, "begdate" => $Polis_begDate]);
							if ($checkEdNum === false) {
								$date = ConvertDateFormat($Polis_begDate, "d.m.Y");
								throw new Exception("На дату {$date} уже создан Ед. номер.");
							}
							$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonPolisEdNum_ins(Server_id := ?,Person_id := ?,PersonPolisEdNum_insDT := ?,PersonPolisEdNum_EdNum := ?,pmUser_id := ?);";
							if ($Federal_Num != "") {
								$res = $this->db->query($sql, [$server_id, $pid, $Polis_begDate, $Federal_Num, $data["pmUser_id"]]);
								$this->ValidateInsertQuery($res);
							}
						}
					}
					// сохраняем
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonPolis_ins(Server_id := ?,Person_id := ?,PersonPolis_insDT := ?,OmsSprTerr_id := ?,PolisType_id := ?,OrgSmo_id := ?,Polis_Ser := ?,PolisFormType_id :=?,Polis_Num := ?,Polis_begDate := ?,Polis_endDate := ?,pmUser_id := ?);";
					if (empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate) {
						$res = $this->db->query($sql, [$server_id, $pid, $Polis_begDate, $OmsSprTerr_id, $PolisType_id, $OrgSmo_id, $Polis_Ser, $PolisFormType_id, $Polis_Num, $Polis_begDate, $Polis_endDate, $data["pmUser_id"]]);
						$this->ValidateInsertQuery($res);
					}
					// выводим из рядов БДЗ (Устанавливаем сервер ид ЛПУ)
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_Person_server(Server_id := ?,BDZ_Guid := ?,Person_id := ?,pmUser_id := ?);";
					$BDZGUID = (isset($data["BDZ_Guid"])) ? $data["BDZ_Guid"] : null;
					$res = $this->db->query($sql, [$server_id, $BDZGUID, $pid, $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
				} else {
					// сохраняем как обычно
					if (isset($OmsSprTerr_id)) {
						$check = $this->checkPolisIntersection($data);
						// если изменились какие то поля кроме дат, то создаём новую периодику
						if (
							(!isset($check["PersonEvn_id"]) || !isset($check["Server_id"])) &&
							(
								array_key_exists("OMSSprTerr_id", $newFields)
								|| array_key_exists("PolisType_id", $newFields)
								|| array_key_exists("Polis_Ser", $newFields)
								|| array_key_exists("Polis_Num", $newFields)
								|| array_key_exists("OrgSMO_id", $newFields)
								|| array_key_exists("Federal_Num", $newFields)
								|| ($data["session"]["region"]["nick"] == "kareliya" && array_key_exists("Polis_begDate", $newFields) && strtotime($data["Polis_begDate"]) > strtotime($newFields["Polis_begDate"]))
							)
						) {
							// проверка есть ли предыдущий не закрытый полис и его закрытие (только если сохранение после идентификации).
							if ($check === false) {
								throw new Exception("Периоды полисов не могут пересекаться.");
							}
							$Federal_Num = (empty($data["Federal_Num"]) ? "" : $data["Federal_Num"]);
							if (array_key_exists("Federal_Num", $newFields)) {
								if ($Federal_Num == "" && $PolisType_id == 4) {
									throw new Exception("Поле Ед. номер не может быть пустым");
								}
							}
							$fsql = "
								select  
									PersonEvn_id as \"PersonEvn_id\",
									Server_id as \"Server_id\",
									Person_EdNum as \"Person_EdNum\"
								from v_Person_all
								where PersonEvnClass_id = 16
								  and Person_id = :Person_id
								  and PersonEvn_insDT = :begdate
								order by
									PersonEvn_insDT desc,
									PersonEvn_TimeStamp desc,
									case when Person_EdNum = :edNum then 0 else 1 end
								limit 1
							";
							$fres = $this->queryResult($fsql, ["Person_id" => $pid, "edNum" => $Federal_Num, "begdate" => $Polis_begDate]);
							if (!is_array($fres)) {
								throw new Exception("Ошибка при получении данныз для проверки ед.номера полиса");
							}
							if (count($fres) == 0) {
								$checkEdNum = $this->checkPesonEdNumOnDate(["Person_id" => $pid, "begdate" => $Polis_begDate]);
								if ($checkEdNum === false) {
									$date = ConvertDateFormat($Polis_begDate, "d.m.Y");
									throw new Exception("На дату {$date} уже создан Ед. номер.");
								}
								$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonPolisEdNum_ins(Server_id := ?,Person_id := ?,PersonPolisEdNum_insDT := ?,PersonPolisEdNum_EdNum := ?,pmUser_id := ?);";
								if ($Federal_Num != "") {
									$res = $this->db->query($sql, [$server_id, $pid, $Polis_begDate, $Federal_Num, $data["pmUser_id"]]);
									$this->ValidateInsertQuery($res);
								}
							} elseif ($fres[0]["Person_EdNum"] != $Federal_Num) {
								$date = ConvertDateFormat($Polis_begDate, "d.m.Y");
								throw new Exception("На дату {$date} уже создан Ед. номер.");
							}
							if (getRegionNick() == "buryatiya" && !empty($check["minDate"])) {
								// создаём 2 полиса
								if ($Polis_begDate < $check["minDate"]) {
									$sql = "
										select Polis_id as \"Polis_id\", Error_Message as \"ErrMsg\"
										from dbo.p_PersonPolis_ins(
											Polis_id := null,
											Server_id := :Server_id,
											Person_id := :Person_id,
											PersonPolis_insDT := :Polis_begDate,
											OmsSprTerr_id := :OmsSprTerr_id,
											PolisType_id := :PolisType_id,
											OrgSmo_id := :OrgSmo_id,
											Polis_Ser := :Polis_Ser,
											PolisFormType_id :=:PolisFormType_id,
											Polis_Num := :Polis_Num,
											Polis_begDate := :Polis_begDate,
											Polis_endDate := :Polis_endDate,
											Polis_Guid := :Polis_Guid,
											pmUser_id := :pmUser_id);
									";
									$sqlParams = [
										"Server_id" => ($person_is_identified) ? 0 : $server_id,
										"Person_id" => $pid,
										"OmsSprTerr_id" => $OmsSprTerr_id,
										"PolisType_id" => $PolisType_id,
										"OrgSmo_id" => $OrgSmo_id,
										"Polis_Ser" => $Polis_Ser,
										"PolisFormType_id" => $PolisFormType_id,
										"Polis_Num" => $Polis_Num,
										"Polis_Guid" => ($person_is_identified && isset($data["Polis_Guid"])) ? $data["Polis_Guid"] : null,
										"Polis_begDate" => $Polis_begDate,
										"Polis_endDate" => date("Y-m-d", strtotime($check["minDate"]) - 24 * 60 * 60),
										"pmUser_id" => $data["pmUser_id"]
									];
									$res = $this->db->query($sql, $sqlParams);
									$this->ValidateInsertQuery($res);
								}
								if (empty($Polis_endDate) || $Polis_endDate > $check["maxDate"]) {
									$sql = "
										select Polis_id as \"Polis_id\", Error_Message as \"ErrMsg\"
										from dbo.p_PersonPolis_ins(
											Polis_id := null,
											Server_id := :Server_id,
											Person_id := :Person_id,
											PersonPolis_insDT := :Polis_begDate,
											OmsSprTerr_id := :OmsSprTerr_id,
											PolisType_id := :PolisType_id,
											OrgSmo_id := :OrgSmo_id,
											Polis_Ser := :Polis_Ser,
											PolisFormType_id :=:PolisFormType_id,
											Polis_Num := :Polis_Num,
											Polis_begDate := :Polis_begDate,
											Polis_endDate := :Polis_endDate,
											Polis_Guid := :Polis_Guid,
											pmUser_id := :pmUser_id
										);
									";
									$sqlParams = [
										"Server_id" => ($person_is_identified) ? 0 : $server_id,
										"Person_id" => $pid,
										"OmsSprTerr_id" => $OmsSprTerr_id,
										"PolisType_id" => $PolisType_id,
										"OrgSmo_id" => $OrgSmo_id,
										"Polis_Ser" => $Polis_Ser,
										"PolisFormType_id" => $PolisFormType_id,
										"Polis_Num" => $Polis_Num,
										"Polis_Guid" => ($person_is_identified && isset($data["Polis_Guid"])) ? $data["Polis_Guid"] : null,
										"Polis_begDate" => date("Y-m-d", strtotime($check["maxDate"]) + 24 * 60 * 60),
										"Polis_endDate" => $Polis_endDate,
										"pmUser_id" => $data["pmUser_id"]
									];
									$res = $this->db->query($sql, $sqlParams);
									$this->ValidateInsertQuery($res);
								}
							} else {
								$allowCreate = true;
								if (getRegionNick() == "ufa") {
									$query = "
										select count(*) as \"cnt\"
										from v_PersonPolis
										where PersonEvnClass_id = 8
										  and Person_id = :Person_id
										  and PersonPolis_insDT::date = :Polis_begDate
										  and OmsSprTerr_id = :OmsSprTerr_id
										  and PolisType_id = :PolisType_id
										  and OrgSmo_id = :OrgSmo_id
										  and coalesce(Polis_Ser, '') = coalesce(:Polis_Ser, '')
										  and coalesce(PolisFormType_id, '') = coalesce(:PolisFormType_id, '')
										  and coalesce(Polis_Num, '') = coalesce(:Polis_Num, '')
										  and coalesce(Polis_endDate, '2000-01-01'::date) = coalesce(:Polis_endDate, '2000-01-01'::date)
										  and Polis_begDate = :Polis_begDate
                                        limit 1
									";
									$sqlParams = [
										"Server_id" => ($person_is_identified) ? 0 : $server_id,
										"Person_id" => $pid,
										"OmsSprTerr_id" => $OmsSprTerr_id,
										"PolisType_id" => $PolisType_id,
										"OrgSmo_id" => $OrgSmo_id,
										"Polis_Ser" => $Polis_Ser,
										"PolisFormType_id" => $PolisFormType_id,
										"Polis_Num" => $Polis_Num,
										"Polis_begDate" => $Polis_begDate,
										"Polis_endDate" => $Polis_endDate,
									];
									$cnt = $this->getFirstResultFromQuery($query, $sqlParams);
									if ($cnt === false) {
										throw new Exception("Ошибка при проверке существования полиса");
									}
									if ($cnt > 0) {
										$allowCreate = false;
									}
								}
								if ($allowCreate) {
									$sql = "
										select Polis_id as \"Polis_id\", Error_Message as \"ErrMsg\"
										from dbo.p_PersonPolis_ins(
											Polis_id := null,
											Server_id := :Server_id,
											Person_id := :Person_id,
											PersonPolis_insDT := :Polis_begDate,
											OmsSprTerr_id := :OmsSprTerr_id,
											PolisType_id := :PolisType_id,
											OrgSmo_id := :OrgSmo_id,
											Polis_Ser := :Polis_Ser,
											PolisFormType_id :=:PolisFormType_id,
											Polis_Num := :Polis_Num,
											Polis_begDate := :Polis_begDate,
											Polis_endDate := :Polis_endDate,
											Polis_Guid := :Polis_Guid,
											pmUser_id := :pmUser_id
										);
									";
									$sqlParams = [
										"Server_id" => ($person_is_identified) ? 0 : $server_id,
										"Person_id" => $pid,
										"OmsSprTerr_id" => $OmsSprTerr_id,
										"PolisType_id" => $PolisType_id,
										"OrgSmo_id" => $OrgSmo_id,
										"Polis_Ser" => $Polis_Ser,
										"PolisFormType_id" => $PolisFormType_id,
										"Polis_Num" => $Polis_Num,
										"Polis_Guid" => ($person_is_identified && isset($data["Polis_Guid"])) ? $data["Polis_Guid"] : null,
										"Polis_begDate" => $Polis_begDate,
										"Polis_endDate" => $Polis_endDate,
										"pmUser_id" => $data["pmUser_id"]
									];
									if (empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate) {
										$res = $this->db->query($sql, $sqlParams);
										$this->ValidateInsertQuery($res);
									}
								}
							}
						} else {
							// если изменились только даты полиса то обновляем периодику
							if (isset($check["PersonEvn_id"]) && isset($check["Server_id"])) {
								// периодика по полису, с которым пересекается полис, пришедший при идентификации
								$sel = [$check];
								$data["PersonEvn_id"] = $sel[0]["PersonEvn_id"];
							} else {
								// получаем последнюю периодику по полису
								$sql = "
									select
										PersonEvn_id as \"PersonEvn_id\",
										Server_id as \"Server_id\",
										Polis_id as \"Polis_id\"
									from v_Person_all
									where PersonEvnClass_id = 8
									  and Person_id = :Person_id
									order by
										PersonEvn_insDT desc,
										PersonEvn_TimeStamp desc
									limit 1
								";
								$sel = $this->queryResult($sql, ["Person_id" => $pid]);
								if (is_array($sel) && count($sel) > 0) {
									$data["PersonEvn_id"] = $sel[0]["PersonEvn_id"];
									$check = $this->checkPolisIntersection($data);
									if ($check === false) {
										throw new Exception("Периоды полисов не могут пересекаться.");
									}
								}
							}
							if (is_array($sel) && count($sel) > 0) {
								if (array_key_exists("Federal_Num", $newFields) && (!isset($check["Polis_Num"]) || (string)$data["Federal_Num"] !== (string)$check["Polis_Num"])) {
									$fsql = "
										select
											PersonEvn_id as \"PersonEvn_id\",
											Server_id as \"Server_id\"
										from v_Person_all
										where PersonEvnClass_id = 16
										  and Person_id = :Person_id
										order by
											PersonEvn_insDT desc,
											PersonEvn_TimeStamp desc
										limit 1
									";
									/**@var CI_DB_result $fres */
									$fres = $this->db->query($fsql, ["Person_id" => $pid]);
									if (is_object($fres)) {
										$fsel = $fres->result_array();
										if (count($fsel) > 0) {
											$Federal_Num = (empty($data["Federal_Num"]) ? "" : $data["Federal_Num"]);
											if ($Federal_Num == "" && $PolisType_id == 4) {
												throw new Exception("Поле Ед. номер не может быть пустым");
											}
											$sql = "
												select error_message as \"Error_Msg\"
												from dbo.p_PersonPolisEdNum_upd(
												    Server_id := (
												        select
												            case when (select Server_id from dbo.v_PersonEvn where Server_id = :serv_id and PersonEvn_id = :peid) is null
												                then (select Server_id from dbo.v_PersonEvn  where PersonEvn_id = :peid)
												                else (select Server_id from dbo.v_PersonEvn where Server_id = :serv_id and PersonEvn_id = :peid)
												            end
												    ),
												    Person_id := :pid,
												    PersonPolisEdNum_id := :peid,
												    PersonPolisEdNum_EdNum := :Federal_Num,
												    pmUser_id := :pmUser_id
												);
											";
											$sqlParams = [
												"serv_id" => $server_id,
												"pid" => $pid,
												"peid" => $fsel[0]["PersonEvn_id"],
												"Federal_Num" => $Federal_Num,
												"pmUser_id" => $data["pmUser_id"]
											];
											$res = $this->db->query($sql, $sqlParams);
											$this->ValidateInsertQuery($res);
										} else {
											// создаём
											$Federal_Num = (empty($data["Federal_Num"]) ? "" : $data["Federal_Num"]);
											if ($Federal_Num == "" && $PolisType_id == 4) {
												throw new Exception("Поле Ед. номер не может быть пустым");
											}
											$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonPolisEdNum_ins(Server_id := :serv_id,Person_id := :pid,PersonPolisEdNum_insDT := :PersonPolisEdNum_insDT,PersonPolisEdNum_EdNum := :Federal_Num,pmUser_id := :pmUser_id);";
											$sqlParams = [
												"serv_id" => $server_id,
												"pid" => $pid,
												"PersonPolisEdNum_insDT" => $Polis_begDate,
												"Federal_Num" => $Federal_Num,
												"pmUser_id" => $data["pmUser_id"]
											];
											$res = $this->db->query($sql, $sqlParams);
											$this->ValidateInsertQuery($res);
										}
									}
								}
								$sql = "
									select Error_Message as \"Error_Msg\"
									from dbo.p_PersonPolis_upd(
									    PersonPolis_id := :peid,
									    Server_id := (
									        select
									            case when (select Server_id from dbo.v_PersonEvn where Server_id = :serv_id and PersonEvn_id = :peid) is null
									                then (select Server_id from dbo.v_PersonEvn  where PersonEvn_id = :peid)
									                else (select Server_id from dbo.v_PersonEvn where Server_id = :serv_id and PersonEvn_id = :peid)
									            end
									    ),
									    Person_id := :Person_id,
									    OmsSprTerr_id := :OmsSprTerr_id,
									    PolisType_id := :PolisType_id,
									    OrgSmo_id := :OrgSmo_id,
									    Polis_Ser := :Polis_Ser,
									    PolisFormType_id :=:PolisFormType_id,
									    Polis_Num := :Polis_Num,
									    Polis_begDate := :Polis_begDate,
									    Polis_endDate := :Polis_endDate,
									    pmUser_id := :pmUser_id
									);
								";
								if (empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate) {
									$sqlParams = [
										"peid" => $sel[0]["PersonEvn_id"],
										"serv_id" => $sel[0]["Server_id"],
										"Person_id" => $pid,
										"OmsSprTerr_id" => $OmsSprTerr_id,
										"PolisType_id" => $PolisType_id,
										"OrgSmo_id" => $OrgSmo_id,
										"Polis_Ser" => $Polis_Ser,
										"PolisFormType_id" => $PolisFormType_id,
										"Polis_Num" => $Polis_Num,
										"Polis_begDate" => $Polis_begDate,
										"Polis_endDate" => $Polis_endDate,
										"pmUser_id" => $data["pmUser_id"]
									];
									$res = $this->db->query($sql, $sqlParams);
									$this->ValidateInsertQuery($res);
									$funcParams = [
										"person_is_identified" => $person_is_identified,
										"session" => $data["session"],
										"PersonEvn_id" => $sel[0]["PersonEvn_id"],
										"Date" => $Polis_begDate,
										"Server_id" => $sel[0]["Server_id"],
										"pmUser_id" => $data["pmUser_id"]
									];
									$this->editPersonEvnDate($funcParams);
								}
								if ($person_is_identified) {
									$sql = "
										select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
										from dbo.p_Polis_server(
											Polis_id := (select polis_id from v_PersonPolis where PersonPolis_id =:PersonEvn_id limit 1),
											Server_id := :Server_id,
											Polis_Guid := :Polis_Guid,
											pmUser_id := :pmUser_id
										);
									";
									$sqlParams = [
										"PersonEvn_id" => $sel[0]["PersonEvn_id"],
										"Server_id" => 0,
										"Polis_Guid" => (isset($data["Polis_Guid"])) ? $data["Polis_Guid"] : null,
										"pmUser_id" => $data["pmUser_id"]
									];
									$res = $this->db->query($sql, $sqlParams);
									if (!is_object($res)) {
										throw new Exception("Ошибка при выполнении запроса к базе данных (проставление признака идентификации по сводной базе застрахованных)");
									}
									$response = $res->result("array");
									if (!is_array($response) || count($response) == 0) {
										throw new Exception("Ошибка при проставлении признака идентификации по сводной базе застрахованных");
									}
								} else if (array_key_exists("Polis_begDate", $newFields)) {
									$sql = "
										select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
										from dbo.p_Polis_server(
											Polis_id := (select polis_id from v_PersonPolis  where PersonPolis_id = :PersonEvn_id limit 1),
											Server_id := :Server_id,
											Polis_Guid := :Polis_Guid,
											pmUser_id := :pmUser_id
										);
									";
									$sqlParams = [
										'PersonEvn_id' => $sel[0]['PersonEvn_id'],
										'Server_id' => 0,
										'Polis_Guid' => null,
										'pmUser_id' => $data['pmUser_id']
									];
									$this->db->query($sql, $sqlParams);
								}
							}
						}
					} elseif (empty($OmsSprTerr_id) && empty($PolisType_id) && empty($OrgSmo_id) && empty($Polis_Ser) && empty($Polis_Num) && empty($Polis_begDate) && empty($Polis_endDate)) {
						// получаем последнюю периодику по полису
						$sql = "
							select
								PersonPolis_id as \"PersonPolis_id\",
								Server_id as \"Server_id\",
								Polis_id as \"Polis_id\",
								Polis_endDate as \"Polis_endDate\"
							from v_PersonPolis
							where Person_id = :Person_id
							order by
								PersonPolis_insDT desc,
								PersonPolis_TimeStamp desc
							limit 1
						";
						$res = $this->db->query($sql, ["Person_id" => $pid]);
						if (is_object($res)) {
							$sel = $res->result_array();
							if (count($sel) > 0) {
								if(empty($sel[0]['Polis_endDate'])){
									//в ТЗ не описано, но по логике как подразумевается, что закрытый полис закрывать дважды не надо (согласно комента проектировщика)
									$sql = "update Polis set Polis_endDate = dbo.tzGetDate() where Polis_id = :Polis_id";
									$this->db->query($sql, array('Polis_id' => $sel[0]['Polis_id']));
								}
							}
						}
					}
				}
				if (!empty($data["Person_id"]) && $data["Person_id"] != 0 && $data["Person_id"] != null) {
					$sql = "select dbo.xp_PersonTransferEvn(Person_id := ?)";
					$this->db->query($sql, [$data["Person_id"]]);
				}
			} else {
				// если атрибуты полиса не менялись, но выбран полис иной территории, то надо снять признак БДЗ (для Карелии по краней мере)
				if (getRegionNick() == "kareliya") {
					$OmsSprTerr_id = (empty($data["OMSSprTerr_id"]) ? null : $data["OMSSprTerr_id"]);
					// проверяем, иная ли территория
					$sql = "
						select KLRgn_id
						from OMSSprTerr
						where OMSSprTerr_id = ?
					";
					$res = $this->db->query($sql, [$OmsSprTerr_id]);
					$sel = $res->result_array();
					if (count($sel) >= 1) {
						$regionNumber = getRegionNumber();
						if (!empty($regionNumber) && !empty($sel[0]["KLRgn_id"]) && $sel[0]["KLRgn_id"] != $regionNumber) {
							// выводим из рядов БДЗ (Устанавливаем сервер ид ЛПУ)
							$sql = "
								select Error_Message as \"ErrMsg\"
								from dbo.p_Person_server(
									Server_id := ?,
									BDZ_Guid := ?,
									Person_id := ?,
									pmUser_id := ?
								);
							";
							$BDZGUID = (isset($data["BDZ_Guid"])) ? $data["BDZ_Guid"] : null;
							$res = $this->db->query($sql, [$server_id, $BDZGUID, $pid, $data["pmUser_id"]]);
							$this->ValidateInsertQuery($res);
						}
					}
				}
			}
			if (array_key_exists("KLCountry_id", $newFields) || array_key_exists("NationalityStatus_IsTwoNation", $newFields) || array_key_exists("LegalStatusVZN_id", $newFields)) {
				//Изменились атрибуты гражданства
				$KLCountry_id = empty($data["KLCountry_id"]) ? null : $data["KLCountry_id"];
				$NationalityStatus_IsTwoNation = $data["NationalityStatus_IsTwoNation"] ? 2 : 1;
				$LegalStatusVZN_id = empty($data["LegalStatusVZN_id"]) ? null : $data["LegalStatusVZN_id"];
				$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonNationalityStatus_ins(Server_id := ?,Person_id := ?,KLCountry_id := ?,NationalityStatus_IsTwoNation := ?,LegalStatusVZN_id := ?,pmUser_id := ?);";
				$res = $this->db->query($sql, [$server_id, $pid, $KLCountry_id, $NationalityStatus_IsTwoNation, $LegalStatusVZN_id, $data["pmUser_id"]]);
				$this->ValidateInsertQuery($res);
			}
			if (array_key_exists("DocumentType_id", $newFields) || array_key_exists("Document_Ser", $newFields) || array_key_exists("Document_Num", $newFields) || array_key_exists("OrgDep_id", $newFields) || array_key_exists("Document_begDate", $newFields)) {
				//Изменились атрибуты документа
				$DocumentType_id = (empty($data["DocumentType_id"]) ? null : $data["DocumentType_id"]);
				$OrgDep_id = (empty($data["OrgDep_id"]) ? null : $data["OrgDep_id"]);
				$Document_Ser = (empty($data["Document_Ser"]) ? "" : $data["Document_Ser"]);
				$Document_Num = (empty($data["Document_Num"]) ? "" : $data["Document_Num"]);
				$Document_begDate = empty($data["Document_begDate"]) ? null : $data["Document_begDate"];
				if (isset($DocumentType_id)) {
					$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonDocument_ins(Server_id := ?,Person_id := ?,DocumentType_id := ?,OrgDep_id := ?,Document_Ser := ?,Document_Num := ?,Document_begDate := ?,pmUser_id := ?);";
					$res = $this->db->query($sql, [$server_id, $pid, $DocumentType_id, $OrgDep_id, $Document_Ser, $Document_Num, $Document_begDate, $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
				} elseif (empty($DocumentType_id) && empty($OrgDep_id) && empty($Document_Ser) && empty($Document_Num) && empty($Document_begDate)) {
					// получаем последнюю периодику по документу
					$sql = "
						select
							PersonDocument_id as \"PersonDocument_id\",
							Server_id as \"Server_id\",
							Document_id as \"Document_id\",
							Document_endDate as \"Document_endDate\"
						from v_PersonDocument
						where Person_id = :Person_id
						order by
							PersonDocument_insDT desc,
							PersonDocument_TimeStamp desc
					    limit 1
					";
					$res = $this->db->query($sql, ["Person_id" => $pid]);
					if (is_object($res)) {
						$sel = $res->result_array();
						if (count($sel) > 0) {
							$sql = "update Document set Document_endDate = dbo.tzGetDate() where Document_id = :Document_id";
							$this->db->query($sql, ["Document_id" => $sel[0]["Document_id"]]);
							$sql = "select PersonDocument_id as \"PersonEvn_id\", Error_Message as \"ErrMsg\" from dbo.p_PersonDocument_del(PersonDocument_id := null,Server_id := :Server_id,Person_id := :Person_id,pmUser_id := :pmUser_id);";
							$sqlParams = [
								"Server_id" => $sel[0]["Server_id"],
								"Person_id" => $pid,
								"pmUser_id" => $data["pmUser_id"]
							];
							$res = $this->db->query($sql, $sqlParams);
							$this->ValidateInsertQuery($res);
							$sel = $res->result_array();
							$data["PersonEvn_id"] = $sel[0]["PersonEvn_id"];
						}
					}
				}
			}
			if (array_key_exists("UKLCountry_id", $newFields) || array_key_exists("UKLRGN_id", $newFields) ||
				array_key_exists("UKLSubRGN_id", $newFields) ||
				array_key_exists("UKLCity_id", $newFields) || array_key_exists("UKLTown_id", $newFields) ||
				array_key_exists("UKLStreet_id", $newFields) ||
				array_key_exists("UAddress_House", $newFields) || array_key_exists("UAddress_Corpus", $newFields) ||
				array_key_exists("UAddress_Flat", $newFields) ||
				array_key_exists("UAddress_Zip", $newFields) ||
				array_key_exists("UAddress_Address", $newFields) ||
				($is_ufa === true && array_key_exists("UPersonSprTerrDop_id", $newFields) && $data["UPersonSprTerrDop_id"] > 0)
			) {
				//Изменились атрибуты адреса регистрации
				$KLCountry_id = (empty($data["UKLCountry_id"]) ? NULL : $data["UKLCountry_id"]);
				$KLRgn_id = (empty($data["UKLRGN_id"]) ? NULL : $data["UKLRGN_id"]);
				$KLRgnSocr_id = (empty($data["UKLRGNSocr_id"]) ? NULL : $data["UKLRGNSocr_id"]);
				$KLSubRgn_id = (empty($data["UKLSubRGN_id"]) ? NULL : $data["UKLSubRGN_id"]);
				$KLSubRgnSocr_id = (empty($data["UKLSubRGNSocr_id"]) ? NULL : $data["UKLSubRGNSocr_id"]);
				$KLCity_id = (empty($data["UKLCity_id"]) ? NULL : $data["UKLCity_id"]);
				$KLCitySocr_id = (empty($data["UKLCitySocr_id"]) ? NULL : $data["UKLCitySocr_id"]);
				$KLTown_id = (empty($data["UKLTown_id"]) ? NULL : $data["UKLTown_id"]);
				$KLTownSocr_id = (empty($data["UKLTownSocr_id"]) ? NULL : $data["UKLTownSocr_id"]);
				$KLStreet_id = (empty($data["UKLStreet_id"]) ? NULL : $data["UKLStreet_id"]);
				$KLStreetSocr_id = (empty($data["UKLStreetSocr_id"]) ? NULL : $data["UKLStreetSocr_id"]);
				$Address_Zip = (empty($data["UAddress_Zip"]) ? "" : $data["UAddress_Zip"]);
				$Address_House = (empty($data["UAddress_House"]) ? "" : $data["UAddress_House"]);
				$Address_Corpus = (empty($data["UAddress_Corpus"]) ? "" : $data["UAddress_Corpus"]);
				$Address_Flat = (empty($data["UAddress_Flat"]) ? "" : $data["UAddress_Flat"]);
				$Address_Address = (empty($data["UAddress_Address"]) ? (empty($data["UAddress_AddressText"]) ? "" : $data["UAddress_AddressText"]) : $data["UAddress_Address"]);
				$PersonSprTerrDop_id = (empty($data["UPersonSprTerrDop_id"]) ? NULL : $data["UPersonSprTerrDop_id"]);
				$AddressSpecObject_id = (empty($data["UAddressSpecObject_id"]) ? NULL : $data["UAddressSpecObject_id"]);
				$sql = "
					select count(*) as \"cnt\"
					from
						v_PersonState s 
						left join address a on a.address_id = s.UAddress_id
					where Person_id = :Person_id
					  and (
					      	coalesce(a.KLCountry_id, 0) != coalesce(:KLCountry_id, 0) or
							coalesce(a.KLRgn_id, 0) != coalesce(:KLRgn_id, 0) or 
							coalesce(a.KLSubRgn_id, 0) != coalesce(:KLSubRgn_id, 0) or
							coalesce(a.KLCity_id, 0) != coalesce(:KLCity_id, 0) or
							coalesce(a.KLTown_id, 0) != coalesce(:KLTown_id, 0) or
							coalesce(a.KLStreet_id, 0) != coalesce(:KLStreet_id, 0) or
							coalesce(a.Address_Zip, '') != coalesce(:Address_Zip, '') or
							coalesce(a.Address_House, '') != coalesce(:Address_House, '') or
							coalesce(a.Address_Corpus, '') != coalesce(:Address_Corpus, '') or
							coalesce(a.Address_Flat, '') != coalesce(:Address_Flat, '') or
							coalesce(a.Address_Address, '') != coalesce(:Address_Address, '') or
							coalesce(a.PersonSprTerrDop_id, 0) != coalesce(:PersonSprTerrDop_id, 0) or
							s.UAddress_id is null
					  )
				";
				$sqlParams = [
					"Person_id" => $pid,
					"KLCountry_id" => $KLCountry_id,
					"KLRgn_id" => $KLRgn_id,
					"KLSubRgn_id" => $KLSubRgn_id,
					"KLCity_id" => $KLCity_id,
					"KLTown_id" => $KLTown_id,
					"KLStreet_id" => $KLStreet_id,
					"Address_Zip" => $Address_Zip,
					"Address_House" => $Address_House,
					"Address_Corpus" => $Address_Corpus,
					"Address_Flat" => $Address_Flat,
					"Address_Address" => $Address_Address,
					"PersonSprTerrDop_id" => $PersonSprTerrDop_id
				];
				$result = $this->db->query($sql, $sqlParams);
				$result = $result->result_array();
				if ($result[0]["cnt"] == 1 || $terr_dop_change["U"]) {
					if (!empty($Address_Address) || !empty($KLRgn_id)) {
						// Сохранение данных стран кроме РФ, которые ранее отсутствовали
						list($KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id) = $this->saveAddressAll($server_id, $data["pmUser_id"], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $KLRgnSocr_id, $KLSubRgnSocr_id, $KLCitySocr_id, $KLTownSocr_id, $KLStreetSocr_id);
						// Сохранение непосредственно адреса (ИДов)
						$sql = "
							select Error_Message as \"ErrMsg\"
							from dbo.p_PersonUAddress_ins(
								Server_id := ?,
								Person_id := ?,
								KLCountry_id := ?,
								KLRgn_id := ?,
								KLSubRgn_id := ?,
								KLCity_id := ?,
								KLTown_id := ?,
								KLStreet_id := ?,
								Address_Zip := ?,
								Address_House := ?,
								Address_Corpus := ?,
								Address_Flat := ?,
								AddressSpecObject_id :=?,
								PersonSprTerrDop_id := ?,
								Address_Address := ?,
								pmUser_id := ?
							);
						";
						$res = $this->db->query($sql, [$server_id, $pid, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $AddressSpecObject_id, $PersonSprTerrDop_id, $Address_Address, $data["pmUser_id"]]);
						$this->ValidateInsertQuery($res);
					} else {
						$sql = "
							select
								Server_id as \"Server_id\",
								PersonUAddress_id as \"PersonUAddress_id\"
							from v_PersonUAddress
							where Person_id = :Person_id
							order by
								PersonUAddress_insDT desc,
								PersonUAddress_TimeStamp desc
							limit 1
						";
						$res = $this->db->query($sql, ["Person_id" => $pid]);
						if (is_object($res)) {
							$sel = $res->result_array();
							if (count($sel) > 0) {
								$sql = "select PersonUAddress_id as \"PersonEvn_id\", Error_Message as \"ErrMsg\" from dbo.p_PersonUAddress_del(PersonUAddress_id := null,Server_id := :Server_id,Person_id := :Person_id,pmUser_id := :pmUser_id);";
								$sqlParams = [
									"Server_id" => $sel[0]["Server_id"],
									"Person_id" => $pid,
									"pmUser_id" => $data["pmUser_id"]
								];
								$res = $this->db->query($sql, $sqlParams);
								$this->ValidateInsertQuery($res);
							}
						}
					}
				}
			}
			if (array_key_exists("BKLCountry_id", $newFields) || array_key_exists("BKLRGN_id", $newFields) ||
				array_key_exists("BKLSubRGN_id", $newFields) ||
				array_key_exists("BKLCity_id", $newFields) || array_key_exists("BKLTown_id", $newFields) ||
				array_key_exists("BKLStreet_id", $newFields) ||
				array_key_exists("BAddress_House", $newFields) || array_key_exists("BAddress_Corpus", $newFields) ||
				array_key_exists("BAddress_Flat", $newFields) ||
				array_key_exists("BAddress_Zip", $newFields) || array_key_exists("BAddress_Address", $newFields) ||
				($is_ufa === true && array_key_exists("BPersonSprTerrDop_id", $newFields) && $data["BPersonSprTerrDop_id"] > 0)
			) {
				$Address_Address = trim(empty($data["BAddress_Address"]) ? null : $data["BAddress_Address"]);
				$KLCountry_id = (empty($data["BKLCountry_id"]) ? null : $data["BKLCountry_id"]);
				$KLRgn_id = (empty($data["BKLRGN_id"]) ? null : $data["BKLRGN_id"]);
				$KLRgnSocr_id = (empty($data["BKLRGNSocr_id"]) ? null : $data["BKLRGNSocr_id"]);
				$KLSubRgn_id = (empty($data["BKLSubRGN_id"]) ? null : $data["BKLSubRGN_id"]);
				$KLSubRgnSocr_id = (empty($data["BKLSubRGNSocr_id"]) ? null : $data["BKLSubRGNSocr_id"]);
				$KLCity_id = (empty($data["BKLCity_id"]) ? null : $data["BKLCity_id"]);
				$KLCitySocr_id = (empty($data["BKLCitySocr_id"]) ? null : $data["BKLCitySocr_id"]);
				$KLTown_id = (empty($data["BKLTown_id"]) ? null : $data["BKLTown_id"]);
				$KLTownSocr_id = (empty($data["BKLTownSocr_id"]) ? null : $data["BKLTownSocr_id"]);
				$KLStreet_id = (empty($data["BKLStreet_id"]) ? null : $data["BKLStreet_id"]);
				$KLStreetSocr_id = (empty($data["BKLStreetSocr_id"]) ? null : $data["BKLStreetSocr_id"]);
				$Address_Zip = (empty($data["BAddress_Zip"]) ? "" : $data["BAddress_Zip"]);
				$Address_House = (empty($data["BAddress_House"]) ? "" : $data["BAddress_House"]);
				$Address_Corpus = (empty($data["BAddress_Corpus"]) ? "" : $data["BAddress_Corpus"]);
				$Address_Flat = (empty($data["BAddress_Flat"]) ? "" : $data["BAddress_Flat"]);
				$PersonSprTerrDop_id = (empty($data["BPersonSprTerrDop_id"]) ? null : $data["BPersonSprTerrDop_id"]);
				$AddressSpecObject_id = (empty($data["BAddressSpecObject_id"]) ? null : $data["BAddressSpecObject_id"]);
				// Сохранение данных стран кроме РФ, которые ранее отсутствовали
				list($KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id) = $this->saveAddressAll($server_id, $data["pmUser_id"], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $KLRgnSocr_id, $KLSubRgnSocr_id, $KLCitySocr_id, $KLTownSocr_id, $KLStreetSocr_id);
				$sql = "
					select 
						Address_id as \"Address_id\",
						PersonBirthPlace_id as \"PersonBirthPlace_id\"
					from PersonBirthPlace
					where Person_id = ?
				";
				$res = $this->db->query($sql, [$pid]);
				$sel = $res->result_array();
				if (count($sel) == 0) {
					$sql = "
						select Error_Message as \"ErrMsg\", Address_id as \"Address_id\"
						from dbo.p_Address_ins(
							Server_id := ?,
							Address_id := null,
							KLCountry_id := ?,
							KLRgn_id := ?,
							KLSubRgn_id := ?,
							KLCity_id := ?,
							KLTown_id := ?,
							KLStreet_id := ?,
							Address_Zip := ?,
							Address_House := ?,
							Address_Corpus := ?,
							Address_Flat := ?,
							AddressSpecObject_id := ?,
							PersonSprTerrDop_id := ?,
							Address_Address := ?,
							pmUser_id := ?
						); 
					";
					$res = $this->db->query($sql, [$server_id, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $AddressSpecObject_id, $PersonSprTerrDop_id, $Address_Address, $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
					$address_id = $res->result_array();
					$sql = "
						select Error_Message as \"ErrMsg\"
						from dbo.p_PersonBirthPlace_ins(
							Person_id := ?,
							Address_id := ?,
							pmUser_id := ?);
					";
					$res = $this->db->query($sql, [$pid, $address_id[0]["Address_id"], $data["pmUser_id"]]);
					$this->ValidateInsertQuery($res);
				} else {
					$arr = [$KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, $Address_Address];
					$delete = true;
					foreach ($arr as $key) {
						if (!empty($key)) {
							$delete = false;
						}
					}
					if (!$delete) {
						$sql = "
							select Error_Message as \"ErrMsg\"
							from dbo.p_Address_upd(
								Server_id := ?,
								Address_id := ?,
								KLAreaType_id := null,
								KLCountry_id := ?,
								KLRgn_id := ?,
								KLSubRgn_id := ?,
								KLCity_id := ?,
								KLTown_id := ?,
								KLStreet_id := ?,
								Address_Zip := ?,
								Address_House := ?,
								Address_Corpus := ?,
								Address_Flat := ?,
								AddressSpecObject_id := ?,
								PersonSprTerrDop_id := ?,
								Address_Address := ?,
								KLAreaStat_id := null,
								pmUser_id := ?
							);
						";
						$res = $this->db->query($sql, [$server_id, $sel[0]["Address_id"], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $AddressSpecObject_id, $PersonSprTerrDop_id, $Address_Address, $data["pmUser_id"]]);
						$this->ValidateInsertQuery($res);
					} else {
						$sql = "select Error_Message as \"ErrMsg\" from dbo.p_PersonBirthPlace_del(PersonBirthPlace_id := ?);";
						$res = $this->db->query($sql, [$sel[0]["PersonBirthPlace_id"]]);
						$this->ValidateInsertQuery($res);
					}
				}
			}
			if (array_key_exists("PKLCountry_id", $newFields) || array_key_exists("PKLRGN_id", $newFields) ||
				array_key_exists("PKLSubRGN_id", $newFields) ||
				array_key_exists("PKLCity_id", $newFields) || array_key_exists("PKLTown_id", $newFields) ||
				array_key_exists("PKLStreet_id", $newFields) ||
				array_key_exists("PAddress_House", $newFields) || array_key_exists("PAddress_Corpus", $newFields) ||
				array_key_exists("PAddress_Flat", $newFields) ||
				array_key_exists("PAddress_Zip", $newFields) ||
				array_key_exists("PAddress_Address", $newFields) ||
				($is_ufa === true && array_key_exists("PPersonSprTerrDop_id", $newFields) && $data["PPersonSprTerrDop_id"] > 0)
			) {
				$KLCountry_id = (empty($data["PKLCountry_id"]) ? null : $data["PKLCountry_id"]);
				$KLRgn_id = (empty($data["PKLRGN_id"]) ? null : $data["PKLRGN_id"]);
				$KLRgnSocr_id = (empty($data["PKLRGNSocr_id"]) ? null : $data["PKLRGNSocr_id"]);
				$KLSubRgn_id = (empty($data["PKLSubRGN_id"]) ? null : $data["PKLSubRGN_id"]);
				$KLSubRgnSocr_id = (empty($data["PKLSubRGNSocr_id"]) ? null : $data["PKLSubRGNSocr_id"]);
				$KLCity_id = (empty($data["PKLCity_id"]) ? null : $data["PKLCity_id"]);
				$KLCitySocr_id = (empty($data["PKLCitySocr_id"]) ? null : $data["PKLCitySocr_id"]);
				$KLTown_id = (empty($data["PKLTown_id"]) ? null : $data["PKLTown_id"]);
				$KLTownSocr_id = (empty($data["PKLTownSocr_id"]) ? null : $data["PKLTownSocr_id"]);
				$KLStreet_id = (empty($data["PKLStreet_id"]) ? null : $data["PKLStreet_id"]);
				$KLStreetSocr_id = (empty($data["PKLStreetSocr_id"]) ? null : $data["PKLStreetSocr_id"]);
				$Address_Zip = (empty($data["PAddress_Zip"]) ? "" : $data["PAddress_Zip"]);
				$Address_House = (empty($data["PAddress_House"]) ? "" : $data["PAddress_House"]);
				$Address_Corpus = (empty($data["PAddress_Corpus"]) ? "" : $data["PAddress_Corpus"]);
				$Address_Flat = (empty($data["PAddress_Flat"]) ? "" : $data["PAddress_Flat"]);
				$Address_Address = (empty($data["PAddress_Address"]) ? "" : $data["PAddress_Address"]);
				$PersonSprTerrDop_id = (empty($data["PPersonSprTerrDop_id"]) ? null : $data["PPersonSprTerrDop_id"]);
				$AddressSpecObject_id = (empty($data["PAddressSpecObject_id"]) ? null : $data["PAddressSpecObject_id"]);
				$sql = "
					select count(*) as \"cnt\"
					from
						v_PersonState s
						left join address a on a.Address_id = s.PAddress_id
					where Person_id = :Person_id
					  and (
							coalesce(a.KLCountry_id, 0) != coalesce(:KLCountry_id, 0) or
							coalesce(a.KLRgn_id, 0) != coalesce(:KLRgn_id, 0) or
							coalesce(a.KLSubRgn_id, 0) != coalesce(:KLSubRgn_id, 0) or
							coalesce(a.KLCity_id, 0) != coalesce(:KLCity_id, 0) or
							coalesce(a.KLTown_id, 0) != coalesce(:KLTown_id, 0) or
							coalesce(a.KLStreet_id, 0) != coalesce(:KLStreet_id, 0) or
							coalesce(a.Address_Zip, '') != coalesce(:Address_Zip, '') or
							coalesce(a.Address_House, '') != coalesce(:Address_House, '') or
							coalesce(a.Address_Corpus, '') != coalesce(:Address_Corpus, '') or
							coalesce(a.Address_Flat, '') != coalesce(:Address_Flat, '') or
							coalesce(a.PersonSprTerrDop_id, 0) != coalesce(:PersonSprTerrDop_id, 0) or
							s.PAddress_id is null
					      )
				";
				$sqlParams = [
					"Person_id" => $pid,
					"KLCountry_id" => $KLCountry_id,
					"KLRgn_id" => $KLRgn_id,
					"KLSubRgn_id" => $KLSubRgn_id,
					"KLCity_id" => $KLCity_id,
					"KLTown_id" => $KLTown_id,
					"KLStreet_id" => $KLStreet_id,
					"Address_Zip" => $Address_Zip,
					"Address_House" => $Address_House,
					"Address_Corpus" => $Address_Corpus,
					"Address_Flat" => $Address_Flat,
					"PersonSprTerrDop_id" => $PersonSprTerrDop_id,
					"Address_Address" => $Address_Address
				];
				$result = $this->db->query($sql, $sqlParams);
				$result = $result->result_array();
				if ($result[0]["cnt"] == 1 || $terr_dop_change["P"]) {
					if (!empty($Address_Address) || !empty($KLRgn_id)) {
						// Сохранение данных стран кроме РФ, которые ранее отсутствовали
						list($KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id) = $this->saveAddressAll($server_id, $data["pmUser_id"], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $KLRgnSocr_id, $KLSubRgnSocr_id, $KLCitySocr_id, $KLTownSocr_id, $KLStreetSocr_id);
						$sql = "
							select Error_Message as \"ErrMsg\"
							from dbo.p_PersonPAddress_ins(
								Server_id := ?,
								Person_id := ?,
								KLCountry_id := ?,
								KLRgn_id := ?,
								KLSubRgn_id := ?,
								KLCity_id := ?,
								KLTown_id := ?,
								KLStreet_id := ?,
								Address_Zip := ?,
								Address_House := ?,
								Address_Corpus := ?,
								Address_Flat := ?,
								AddressSpecObject_id := ?,
								PersonSprTerrDop_id := ?,
								Address_Address := ?,
								pmUser_id := ?
							);
						";
						$res = $this->db->query($sql, [$server_id, $pid, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $AddressSpecObject_id, $PersonSprTerrDop_id, $Address_Address, $data["pmUser_id"]]);
						$this->ValidateInsertQuery($res);
					} else {
						$sql = "
							select
								Server_id as \"Server_id\",
								PersonPAddress_id as \"PersonPAddress_id\"
							from v_PersonPAddress
							where Person_id = :Person_id
							order by
								PersonPAddress_insDT desc,
								PersonPAddress_TimeStamp desc
						";
						$res = $this->db->query($sql, ["Person_id" => $pid]);
						if (is_object($res)) {
							$sel = $res->result_array();
							if (count($sel) > 0) {
								$sql = "select PersonPAddress_id as \"PersonEvn_id\", Error_Message as \"ErrMsg\" from dbo.p_PersonPAddress_del(Server_id := :Server_id,Person_id := :Person_id,pmUser_id := :pmUser_id);";
								$sqlParams = [
									"Server_id" => $sel[0]["Server_id"],
									"Person_id" => $pid,
									"pmUser_id" => $data["pmUser_id"]
								];
								$res = $this->db->query($sql, $sqlParams);
								$this->ValidateInsertQuery($res);
							}
						}
					}
				}
			}
			//Изменились атрибуты специфики детства
			if (array_key_exists("ResidPlace_id", $newFields) || array_key_exists("PersonChild_IsManyChild", $newFields) || array_key_exists("PersonChild_IsBad", $newFields) ||
				array_key_exists("PersonChild_IsYoungMother", $newFields) || array_key_exists("PersonChild_IsIncomplete", $newFields) || array_key_exists("PersonChild_IsTutor", $newFields) ||
				array_key_exists("PersonChild_IsMigrant", $newFields) || array_key_exists("HealthKind_id", $newFields) || array_key_exists("FeedingType_id", $newFields) ||
				array_key_exists("PersonChild_CountChild", $newFields) || array_key_exists("PersonChild_IsInvalid", $newFields) || array_key_exists("InvalidKind_id", $newFields) ||
				array_key_exists("PersonChild_invDate", $newFields) || array_key_exists("HealthAbnorm_id", $newFields) || array_key_exists("HealthAbnormVital_id", $newFields) ||
				array_key_exists("Diag_id", $newFields) || array_key_exists("PersonSprTerrDop_id", $newFields)
			) {
				$resp = $this->savePersonChild(array_merge($data, ["Person_id" => $pid, "Server_id" => $server_id]));
				$this->ValidateInsertQuery($resp);
			}
			// Выбираем запись либо с Server_id больницы, либо если ее нет, с Server_id = 0
			$sql = "
				select  
					PersonEvn_id as \"PersonEvn_id\",
					Server_id  as \"Server_id\"
				from PersonState
				where Person_id = :Person_id
				order by Server_id desc
				limit 1
			";
			$resp = $this->getFirstRowFromQuery($sql, ["Person_id" => $pid], true);
			if ($resp === false) {
				throw new Exception("Ошибка при получени текущей периодики человека");
			}
			$peid = (!empty($resp)) ? $resp["PersonEvn_id"] : "NULL";
			if (!empty($resp)) {
				$server_id = $resp["Server_id"];
			}
			$this->_saveResponse = array_merge($this->_saveResponse, [
				"Person_id" => $pid, "PersonEvn_id" => $peid, "Server_id" => $server_id
			]);
			if (!empty($pguid)) {
				$this->_saveResponse["Person_Guid"] = $pguid;
			} else {
				$sql = "
					select Person_Guid  as \"Person_Guid\"
					from Person
					where Person_id = :Person_id
					limit 1
				";
				$this->_saveResponse["Person_Guid"] = $this->getFirstResultFromQuery($sql, ["Person_id" => $pid], true);
				if ($this->_saveResponse["Person_Guid"] === false) {
					throw new Exception("Ошибка при получени GUID человека");
				}
			}
			if ($is_double) {
				$dbl = $this->checkExistPersonDouble($bdzData["Person_id"], $data["Person_id"]);
				if ($dbl == true) {
					$this->addInfoMsg("Человек уже находится в очереди на объединение двойников");
				} else {
					$query = "
						select PersonDoubles_id as \"PersonDoubles_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
						from pd.p_PersonDoubles_ins(
						    PersonDoubles_id := null,
							Person_id := :Person_id,
							Person_did := :Person_did,
							pmUser_id := :pmUser_id);
					";
					$queryParams = [
						"Person_id" => $bdzData["Person_id"],
						"Person_did" => $data["Person_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$result = $this->queryResult($query, $queryParams);
					if (!is_array($result)) {
						throw new Exception("Ошибка при выполнении запроса к базе данных (объединение)");
					}
					$this->addInfoMsg("При идентификации обнаружен двойник. Отправлено в очередь на объединение");
				}
			}

			$procedure = (!empty($data['PersonEmployment_id'])) ? 'p_PersonEmployment_upd' : 'p_PersonEmployment_ins';
			$query = "
						select PersonEmployment_id as \"PersonEmployment_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
						from {$procedure}(
						    PersonEmployment_id := :PersonEmployment_id,
						    Person_id := :Person_id,
							Server_id := :Server_id,
							pmUser_id := :pmUser_id,
							Employment_id := :Employment_id);
		";

			$queryParams = array(
				'PersonEmployment_id' => (!empty($data['PersonEmployment_id']) ? $data['PersonEmployment_id']: NULL),
				'Person_id' => $pid,
				//'Person_id' => $data['Person_id'],
				'Server_id' => $data['Server_id'],
				'pmUser_id' => $data['pmUser_id'],
				'Employment_id' => $data['Employment_id']
			);
			
			$result = $this->db->query($query, $queryParams)->result('array');
			if (!empty($result[0]['Error_Msg'])) {
				throw new Exception($result[0]['Error_Msg']);
			}
			
			
			$procedure = (!empty($data['PersonEduLevel_id'])) ? 'p_PersonEduLevel_upd' : 'p_PersonEduLevel_ins';
			$query = "
						select PersonEduLevel_id as \"PersonEduLevel_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
						from {$procedure}(
						    PersonEduLevel_id := :PersonEduLevel_id,
						    Person_id := :Person_id,
							Server_id := :Server_id,
							pmUser_id := :pmUser_id,
							EducationLevel_id := :EducationLevel_id);
		";

			$queryParams = array(
				'PersonEduLevel_id' => (!empty($data['PersonEduLevel_id']) ? $data['PersonEduLevel_id']: NULL),
				'Person_id' => $pid,
				//'Person_id' => $data['Person_id'],
				'Server_id' => $data['Server_id'],
				'pmUser_id' => $data['pmUser_id'],
				'EducationLevel_id' => $data['EducationLevel_id'],
			);

			$result = $this->db->query($query, $queryParams)->result('array');
			if (!empty($result[0]['Error_Msg'])) {
				throw new Exception($result[0]['Error_Msg']);
			}
			
			
			if (!isset($data["Server_pid"])) {
				$data["Server_pid"] = 0;
			}
			$currDate = date_create($this->getCurrentDT()->format("Y-m-d"));
			$begDate = !empty($data["Polis_begDate"]) ? date_create($data["Polis_begDate"]) : null;
			$endDate = !empty($data["Polis_endDate"]) ? date_create($data["Polis_endDate"]) : null;
			$hasPolis = (!empty($begDate) && $begDate <= $currDate && (empty($endDate) || $endDate > $currDate));
			if (($isPerm && !empty($pid) && $mainChange && ($data["Server_pid"] != 0 || ($data["Server_pid"] == 0 && !$hasPolis))) || ($is_penza && !empty($pid) && $data["mode"] == "add")) {
				$this->isAllowTransaction = false;
				$funcParams = [
					"Person_id" => $pid,
					"pmUser_id" => $data["pmUser_id"],
					"PersonRequestSourceType_id" => ($data["mode"] == "add") ? 5 : 1,
				];
				$resp = $this->addPersonRequestData($funcParams);
				$this->isAllowTransaction = true;
				if (!$this->isSuccessful($resp) && !in_array($resp[0]["Error_Code"], [302, 303])) {
					throw new Exception($resp[0]["Error_Msg"]);
				}
			}
			if ($isKrym && !empty($pid) && ($mainChange || $data["mode"]) == "add") {
				//Сохранение записи для идентификации в ТФОМС
				$this->load->model("PersonIdentPackage_model");
				$this->isAllowTransaction = false;
				$funcParams = [
					"Person_id" => $pid,
					"pmUser_id" => $data["pmUser_id"],
					"PersonIdentPackageTool_id" => 1,
				];
				$this->PersonIdentPackage_model->addPersonIdentPackagePos($funcParams);
				$this->isAllowTransaction = true;
				if (!$this->isSuccessful($resp) && !in_array($resp[0]["Error_Code"], [302, 303])) {
					throw new Exception($resp[0]["Error_Msg"]);
				}
			}
			if ($is_kz && !empty($pid) && !empty($data["BDZ_id"]) && !empty($data["Person_identDT"]) && $data["Person_IsInErz"] == 2) {
				//Синхронизация прикреплений из сервиса РПН Казахстана
				$this->load->model("ServiceRPN_model");
				$this->ServiceRPN_model->saveSyncObject("Person", $pid, $data["BDZ_id"]);
				$funcParams = [
					"Person_id" => $pid,
					"Server_id" => $data["Server_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$resp = $this->ServiceRPN_model->syncPersonCards($funcParams);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]["Error_Msg"]);
				}
				$sql = "
					select L.Lpu_Nick as \"Lpu_Nick\"
					from
						v_PersonCard PC
						inner join v_Lpu L on L.Lpu_id = PC.Lpu_id
					where PC.Person_id = :Person_id
					limit 1
				";
				$Lpu_Nick = $this->getFirstResultFromQuery($sql, ["Person_id" => $pid], true);
				if ($Lpu_Nick === false) {
					throw new Exception("Ошибка при получении последнего прикрепления");
				}
				$this->_saveResponse["Lpu_Nick"] = $Lpu_Nick;
			}
		} catch (Exception $e) {
			if (isset($_REQUEST["isDebug"])) {
				$this->textlog->add("Строка: {$e->getLine()}. Ошибка: {$e->getMessage()}");
			}
			$this->rollbackTransaction();
			$this->_saveResponse["Error_Msg"] = $e->getMessage();
			$this->_saveResponse["Error_Code"] = $e->getCode();
			return [$this->_saveResponse];
		}
		$this->exceptionOnValidation = false;
		$this->commitTransaction();
		if (!empty($this->_saveResponse['Person_id']) && $needSnilsVerification) {
			$this->verifyPersonSnils([
				'Person_id' => $this->_saveResponse['Person_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}
		if (($IsLocalSMP === true || $IsSMPServer === true) && !$is_kareliya  && !$is_astra && !$is_ufa) {
			if (!empty($this->_saveResponse["Person_id"])) {
				// отправляем человека в основную БД через очередь ActiveMQ
				$this->load->model("Replicator_model");
				$funcParams = [
					"table" => "Person",
					"type" => ($data["mode"] == "add") ? "insert" : "update",
					"keyParam" => "Person_id",
					"keyValue" => $this->_saveResponse["Person_id"]
				];
				$this->Replicator_model->sendRecordToActiveMQ($funcParams);
			}
		}
		if (($IsLocalSMP === true || $IsSMPServer === true) && ($is_bur || $is_kareliya === true || $is_astra === true) &&
			!empty($this->_saveResponse['Person_id']) && empty($data['useSMP'])) {
			// персона с таким же Person_id создаем в БД СМП.
			$data["useSMP"] = true;
			$data["Person_id"] = $this->_saveResponse["Person_id"];
			$data["Person_Guid"] = $this->_saveResponse["Person_Guid"];
			$this->savePersonEditWindow($data);
		}
		return [$this->_saveResponse];
	}

	#region get
	/**
	 * @param $data
	 * @return array
	 */
	function getPersonIdentData($data)
	{
		return Person_model_get::getPersonIdentData($this, $data);
	}

	/**
	 * Загрузка СНИЛС
	 * @param $data
	 * @return bool|mixed
	 */
	function getPersonSnils($data)
	{
		return Person_model_get::getPersonSnils($this, $data);
	}

	/**
	 * Загрузка данных пациента
	 * @param $data
	 * @return array|false
	 */
	function getPersonCombo($data)
	{
		return Person_model_get::getPersonCombo($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getPersonEvalEditWindow($data)
	{
		return Person_model_get::getPersonEvalEditWindow($this, $data);
	}

	/**
	 * Запрос в форме поиска человека c использованием функции модификации запроса с плейсхолдерами
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function getPersonSearchGrid($data)
	{
		return Person_model_get::getPersonSearchGrid($this, $data);
	}

	/**
	 * Поиск человека в форме РПН: Прикрепление
	 * В отличие от предыдущего поиска человека добавлено больше фильтров
	 * @param $data
	 * @param bool $print
	 * @param bool $get_count
	 * @return array|bool
	 */
	function getPersonCardGrid($data, $print = false, $get_count = false)
	{
		return Person_model_get::getPersonCardGrid($this, $data, $print, $get_count);
	}

	/**
	 * Поиск людей
	 * @param $data
	 * @param bool $print
	 * @param bool $get_count
	 * @return array|bool
	 * @throws Exception
	 */
	function getPersonGrid($data, $print = false, $get_count = false)
	{
		return Person_model_get::getPersonGrid($this, $data, $print, $get_count);
	}

	/**
	 * Получение списка ЗЛ для автоприкрепления
	 * @param $data
	 * @return array|bool
	 */
	function getPersonGridPersonCardAuto($data)
	{
		return Person_model_get::getPersonGridPersonCardAuto($this, $data);
	}

	/**
	 * Запрос всех полей по выбранному человеку
	 * Используется в форме редактирования человека
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function getPersonEvnEditWindow($data)
	{
		return Person_model_get::getPersonEvnEditWindow($this, $data);
	}

	function getPersonAttach($data)
	{
		return Person_model_get::getPersonAttach($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	function getPersonEvnIdByEvnId($data)
	{
		return Person_model_get::getPersonEvnIdByEvnId($this, $data);
	}

	/**
	 * Используется в форме редактирования человека
	 * @param $data
	 * @return array|bool
	 */
	function getPersonEditWindow($data)
	{
		return Person_model_get::getPersonEditWindow($this, $data);
	}

	/**
	 * Получение списка двойников из БДЗ по ФИО + ДР с открытыми полисами на заданную дату
	 * @param $data
	 * @return array|bool
	 */
	function getPersonDoublesByFIODR($data)
	{
		return Person_model_get::getPersonDoublesByFIODR($this, $data);
	}

	/**
	 * Получение информации полиса
	 * @param $data
	 * @return array|bool
	 */
	function getPersonPolisInfo($data)
	{
		return Person_model_get::getPersonPolisInfo($this, $data);
	}

	/**
	 * Получение телефона
	 * @param $data
	 * @return array|bool
	 */
	function getPersonPhoneInfo($data)
	{
		return Person_model_get::getPersonPhoneInfo($this, $data);
	}

	/**
	 * Получение данных о месте работы
	 * @param $data
	 * @return array|bool
	 */
	function getPersonJobInfo($data)
	{
		return Person_model_get::getPersonJobInfo($this, $data);
	}

	/**
	 * Получение Person_id по ФИО + Полис + Д/Р, полученные из УЕК
	 * @param $data
	 * @return array|bool
	 */
	function getPersonByUecData($data)
	{
		return Person_model_get::getPersonByUecData($this, $data);
	}

	/**
	 * Получение Person_id по ФИО + Полис + Д/Р + Пол, прочитанные из штрих-кода
	 * @param $data
	 * @return array|bool
	 */
	function getPersonByBarcodeData($data)
	{
		return Person_model_get::getPersonByBarcodeData($this, $data);
	}

	/**
	 * Получение адреса человека
	 * @param $data
	 * @return array|bool
	 */
	function getPersonAddress($data)
	{
		return Person_model_get::getPersonAddress($this, $data);
	}

	/**
	 * Возвращает номер региона территории страхования
	 * @param $data
	 * @return bool|float|int|string
	 */
	function getPersonPolisRegionId($data)
	{
		return Person_model_get::getPersonPolisRegionId($this, $data);
	}

	/**
	 * Получение кода анонимного пациента в формате ККККГГННННН #140580
	 * с контролем переполнения
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getPersonAnonymCodeExt($data)
	{
		return Person_model_get::getPersonAnonymCodeExt($this, $data);
	}

	/**
	 * Получение кода анонимного пациента
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getPersonAnonymCode($data)
	{
		return Person_model_get::getPersonAnonymCode($this, $data);
	}

	/**
	 * Получение данных анонимного пациента
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getPersonAnonymData($data)
	{
		return Person_model_get::getPersonAnonymData($this, $data);
	}

	/**
	 * Получение данных по согласию на обработку перс.данных для ЭМК
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function getPersonLpuInfoPersData($data)
	{
		return Person_model_get::getPersonLpuInfoPersData($this, $data);
	}

	/**
	 * Получение списка свидетельств для ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function getPersonSvidInfo($data)
	{
		return Person_model_get::getPersonSvidInfo($this, $data);
	}

	/**
	 * Получение данных периодики
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function getPersonEvnAndPolisData($data)
	{
		return Person_model_get::getPersonEvnAndPolisData($this, $data);
	}

	/**
	 * Получение данных по сигнальной информации пациента
	 * @param $data
	 * @return array|mixed
	 */
	function getPersonSignalInfo($data)
	{
		return Person_model_get::getPersonSignalInfo($this, $data);
	}

	/**
	 * Получить записей человека
	 * @param $data
	 * @return array
	 */
	function getPersonRecords($data)
	{
		return Person_model_get::getPersonRecords($this, $data);
	}

	/**
	 * @param $data
	 * @return array|false
	 */
	function getPersonIdByPersonEvnId($data)
	{
		return Person_model_get::getPersonIdByPersonEvnId($this, $data);
	}

	/**
	 * Получаем все необходимые параметры по человеку для определения референсных значений
	 * @param $data
	 * @return array
	 */
	function getPersonDataForRefValues($data)
	{
		return Person_model_get::getPersonDataForRefValues($this, $data);
	}

	/**
	 * Получение данных о человеке для InnovaSysService_model
	 * @param $data
	 * @return array|false
	 */
	function getPersonForInnova($data)
	{
		return Person_model_get::getPersonForInnova($this, $data);
	}

	function getPersonEvn($data)
	{
		return Person_model_get::getPersonEvn($this, $data);
	}

	function getPersonMain($data) {
		return Person_model_get::getPersonMain($this, $data);
	}
	/**
	 * Получить данных о представителе пациента.
	 * @param $data
	 * @return array
	 */
	public function getPersonDeputy($data){
		return Person_model_get::getPersonDeputy($this, $data);
	}
	#endregion get
	#region getCommon
	/**
	 * Получение данных полиса. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getPolisForAPI($data)
	{
		return Person_model_getCommon::getPolisForAPI($this, $data);
	}

	/**
	 * По названию должности получаем идентификатор должности, если такой должности не существует, то создаем новую запись и возвращаем ее идентификатор
	 * @param $post_new
	 * @param array $data
	 * @return |null
	 */
	function getPostIdFromPostName($post_new, $data = [])
	{
		return Person_model_getCommon::getPostIdFromPostName($this, $post_new, $data);
	}
	
	/**
	 * Валидация СНИЛС
	 */
	function verifyPersonSnils(array $data):array {
		// получаем текущие данные по пациенту
		$resp_ps = $this->queryResult("
			select
				Person_id as \"Person_id\",
				Person_SurName as \"Person_SurName\",
				Person_FirName as \"Person_FirName\",
				Person_SecName as \"Person_SecName\",
				Sex_id as \"Sex_id\",
				Person_BirthDay as \"Person_BirthDay\"
			from
				v_PersonState
			where
				Person_id = :Person_id
		", [
			'Person_id' => $data['Person_id']
		]);

		if (empty($resp_ps[0]['Person_id'])) {
			return ['Error_Msg' => 'Указанный человек не найден в БД'];
		}

		$this->db->query("update PersonState set PersonState_IsSnils = :PersonState_IsSnils where Person_id = :Person_id", [
			'Person_id' => $data['Person_id'],
			'PersonState_IsSnils' => null
		]);

		$resp_psq = $this->queryResult("
			select
				PersonSnilsQueue_id as \"PersonSnilsQueue_id\"
			from
				v_PersonSnilsQueue
			where
				Person_id = :Person_id
				and Person_Snils is null -- только те, по которым не получен ответ
		", [
			'Person_id' => $data['Person_id']
		]);

		$this->load->model('PersonSnilsQueue_model');
		foreach($resp_psq as $one_psq) {
			$this->PersonSnilsQueue_model->deletePersonSnilsQueue([
				'PersonSnilsQueue_id' => $one_psq['PersonSnilsQueue_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		return $this->PersonSnilsQueue_model->savePersonSnilsQueue([
			'Person_id' => $data['Person_id'],
			'Person_SurName' => $resp_ps[0]['Person_SurName'],
			'Person_FirName' => $resp_ps[0]['Person_FirName'],
			'Person_SecName' => $resp_ps[0]['Person_SecName'],
			'Person_Sex' => $resp_ps[0]['Sex_id'],
			'Person_BirthDay' => $resp_ps[0]['Person_BirthDay'],
			'pmUser_id' => $data['pmUser_id']
		]);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getAddressByPersonId($data)
	{
		return Person_model_getCommon::getAddressByPersonId($this, $data);
	}

	/**
	 * Запрос для получения списка истории изменения всех периодик человека
	 * @param $data
	 * @return array|bool
	 */
	function getAllPeriodics($data)
	{
		return Person_model_getCommon::getAllPeriodics($this, $data);
	}

	/**
	 * Получение антропометрических данных человека
	 * @param $data
	 * @return array|bool
	 */
	function getAnthropometryViewData($data)
	{
		return Person_model_getCommon::getAnthropometryViewData($this, $data);
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function getBDZPersonData($data)
	{
		return Person_model_getCommon::getBDZPersonData($this, $data);
	}

	/**
	 * Получаем текущие значения полей блока "Место работы"
	 * @param $Person_id
	 * @return array
	 */
	function getCurrentPersonJob($Person_id)
	{
		return Person_model_getCommon::getCurrentPersonJob($this, $Person_id);
	}

	/**
	 * Получение диагнозов человека на диспансерном учете
	 * @param $data
	 * @return array|false
	 */
	function getDiagnosesPersonOnDisp($data)
	{
		return Person_model_getCommon::getDiagnosesPersonOnDisp($this, $data);
	}

	/**
	 * Получение данных документа. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getDocumentForAPI($data)
	{
		return Person_model_getCommon::getDocumentForAPI($this, $data);
	}

	/**
	 * Получить дату взятия биопсии из последнего случая с признаком ЗНО
	 * @param $data
	 * @return bool|float|int|string
	 */
	function getEvnBiopsyDate($data)
	{
		return Person_model_getCommon::getEvnBiopsyDate($this, $data);
	}

	/**
	 * Получение данных человека для объединения
	 * @param $data
	 * @return mixed
	 */
	function getInfoForDouble($data)
	{
		return Person_model_getCommon::getInfoForDouble($this, $data);
	}

	/**
	 * Получение данных последнего документа человека. Метод для API
	 * @param $data
	 * @return array|false
	 */
	function getLastDocumentForAPI($data)
	{
		return Person_model_getCommon::getLastDocumentForAPI($this, $data);
	}

	/**
	 * Получение данных последнего полиса человека. Метод для API
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getLastPolisForAPI($data)
	{
		return Person_model_getCommon::getLastPolisForAPI($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getMainFields($data)
	{
		return Person_model_getCommon::getMainFields($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getOrgSMO($data)
	{
		return Person_model_getCommon::getOrgSMO($this, $data);
	}

	/**
	 * Получаем последнюю периодику по полису
	 * @param $data
	 * @return array|bool
	 */
	function getLastPeriodicalsByPolicy($data)
	{
		return Person_model_getCommon::getLastPeriodicalsByPolicy($this, $data);
	}
	#endregion getCommon
	#region load
	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonEval($data)
	{
		return Person_model_load::loadPersonEval($this, $data);
	}

	/**
	 * Получение данных полиса
	 * @param $data
	 * @return array|bool
	 */
	function loadPolisData($data)
	{
		return Person_model_load::loadPolisData($this, $data);
	}

	/**
	 * Получение данных о документе
	 * @param $data
	 * @return array|bool
	 */
	function loadDocumentData($data)
	{
		return Person_model_load::loadDocumentData($this, $data);
	}

	/**
	 * Получение данных о гражданстве
	 * @param $data
	 * @return array|false
	 */
	function loadNationalityStatusData($data)
	{
		return Person_model_load::loadNationalityStatusData($this, $data);
	}

	/**
	 * Получение данных о работе
	 * @param $data
	 * @return array|bool
	 */
	function loadJobData($data)
	{
		return Person_model_load::loadJobData($this, $data);
	}

	/**
	 * Получение истории идентификации человека в ЦС ЕРЗ
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonRequestDataGrid($data)
	{
		return Person_model_load::loadPersonRequestDataGrid($this, $data);
	}

	/**
	 * Получение истории операций по согласию/отзыву согласия на обработку перс.данных
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function loadPersonLpuInfoList($data)
	{
		return Person_model_load::loadPersonLpuInfoList($this, $data);
	}

	/**
	 * Получение списка людей для API
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function loadPersonListForAPI($data)
	{
		return Person_model_load::loadPersonListForAPI($this, $data);
	}

	/**
	 * Получение списка сотрудников
	 * @param $data
	 * @return array|false
	 */
	function loadPersonWorkList($data)
	{
		return Person_model_load::loadPersonWorkList($this, $data);
	}

	/**
	 * Получение данных о сотруднике организации для редактирования
	 * @param $data
	 * @return array|false
	 */
	function loadPersonWorkForm($data)
	{
		return Person_model_load::loadPersonWorkForm($this, $data);
	}

	/**
	 * Получение списка сотрудников
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonWorkGrid($data)
	{
		return Person_model_load::loadPersonWorkGrid($this, $data);
	}

	/**
	 * Получение списка согласий пациента для ЭМК
	 * @param $data
	 * @return array|false
	 */
	function loadPersonLpuInfoPanel($data)
	{
		return Person_model_load::loadPersonLpuInfoPanel($this, $data);
	}

	/**
	 * Метод для API. Получение списка согласий пациента для ЭМК
	 * @param $data
	 * @return array|false
	 */
	function loadPersonLpuInfoPanelForAPI($data)
	{
		return Person_model_load::loadPersonLpuInfoPanelForAPI($this, $data);
	}
	#endregion load
	#region check
	/**
	 * @param $data
	 * @return array|bool
	 */
	public function checkChildrenDuplicates($data)
	{
		return Person_model_check::checkChildrenDuplicates($this, $data);
	}

	/**
	 * Выполняется проверка:
	 * Если минимальная дата по всем периодикам данного атрибута
	 * не совпадает с датой по данному атрибуту из таблицы PersonEvnClass_begDT,
	 * то добавление/изменение/удаление отменять и выводить сообщение
	 * «Дата начала самой ранней периодики должна быть равна < PersonEvnClass_begDT по данному атрибуту>».
	 * Если PersonEvnClass_begDT=Null, то проверку не выполнять.
	 * @param $Person_id
	 * @param $PersonEvnClass_id
	 * @param $PersonEvnClass_begDate
	 * @throws Exception
	 */
	function checkPeriodicBegDate($Person_id, $PersonEvnClass_id, $PersonEvnClass_begDate)
	{
		Person_model_check::checkPeriodicBegDate($this, $Person_id, $PersonEvnClass_id, $PersonEvnClass_begDate);
	}

	/**
	 * Проверка серии/номера документа на валидность
	 * @param $data
	 * @return string
	 */
	function checkDocument($data)
	{
		return Person_model_check::checkDocument($this, $data);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function checkPesonEdNumOnDate($data)
	{
		return Person_model_check::checkPesonEdNumOnDate($this, $data);
	}

	/**
	 * Проверка дат полиса на пересечение с другими полисами человека
	 * @param $data
	 * @param bool $attr
	 * @return array|bool|mixed|null
	 */
	function checkPolisIntersection($data, $attr = false)
	{
		return Person_model_check::checkPolisIntersection($this, $data, $attr);
	}

	/**
	 * Проверка активности территории полиса
	 * @param $data
	 * @return bool
	 */
	function checkOMSSprTerrDate($data)
	{
		return Person_model_check::checkOMSSprTerrDate($this, $data);
	}

	/**
	 * Проверка единого номера полиса на уникальность
	 * @task https://redmine.swan.perm.ru/issues/88654
	 * Вынесено в региональную модель для Перми по задаче https://redmine.swan.perm.ru/issues/93041
	 * @param $data
	 * @return bool
	 */
	function checkFederalNumUnique($data)
	{
		return Person_model_check::checkFederalNumUnique();
	}

	/**
	 * Проверка введенных данных по человеку на двойника (старая)
	 * @param $data
	 * @return array|bool|false
	 */
	function checkPersonDoubles($data)
	{
		return Person_model_check::checkPersonDoubles($this, $data);
	}

	/**
	 * Проверка на дублирование номеров СНИЛС
	 * @param $data
	 * @return array|bool|false
	 */
	function checkSnilsDoubles($data)
	{
		return Person_model_check::checkSnilsDoubles($this, $data);
	}

	/**
	 * Проверка на дубли согласно задаче redmine.swan.perm.ru/issues/93041 - Либо ЕНП + Фамилия + Год рождения, либо ЕНП + Имя + Отчество + Год рождения
	 * @param $params
	 * @return array|bool|false
	 */
	function check_ENP($params)
	{
		return Person_model_check::check_ENP($this, $params);
	}

	/**
	 * Проверка введенных данных по человеку на двойника по полису
	 * @param $data
	 * @return array|bool
	 */
	function checkPersonPolisDoubles($data)
	{
		return Person_model_check::checkPersonPolisDoubles($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function CheckSpecifics($data)
	{
		return Person_model_check::CheckSpecifics($this, $data);
	}

	/**
	 * @param $Person_id
	 * @param $Person_did
	 * @return bool
	 */
	function checkExistPersonDouble($Person_id, $Person_did)
	{
		return Person_model_check::checkExistPersonDouble($this, $Person_id, $Person_did);
	}

	/**
	 * Проверка на дублирование СНИЛС-а
	 * Возвращает true если дублей не найдено
	 * @param $data
	 * @return bool
	 */
	function checkPersonSnilsDoubles($data)
	{
		return Person_model_check::checkPersonSnilsDoubles($this, $data);
	}

	/**
	 * @param $ednum
	 * @return bool
	 */
	function checkEdNumFedSignature($ednum){
		if (!preg_match('/^\d{16}$/', $ednum)){
			return false;
		}

		$key = $ednum[strlen($ednum) - 1];

		$str_chet = '';
		$str_nechet = '';

		for ($i = 14; $i >= 0; $i--){
			if ($i % 2 === 0){
				$str_nechet .= $ednum[$i];
			}else{
				$str_chet .= $ednum[$i];
			}
		}

		$str_number = $str_chet . ((int)$str_nechet * 2);
		$summ = 0;

		for ($i = 0; $i < strlen($str_number); $i++){
			$summ += (int)$str_number[$i];
		}

		$number_key = $summ % 10 === 0 ? 0 : 10 - $summ % 10;
		if ($number_key === $key){
			return  true;
		}

		return false;
	}

	/**
	 * Проверка существования кода анонимного пациента, генерация нового кода
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkAnonimCodeUnique($data)
	{
		return Person_model_check::checkAnonimCodeUnique($this, $data);
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkPersonPhoneStatus($data)
	{
		return Person_model_check::checkPersonPhoneStatus($this, $data);
	}

	/**
	 * Проверяет есть ли на данного неизвестного человека
	 * Талон вызова СМП
	 * Карта закрытия вызова СМП
	 * Бирка
	 * Событие(смотрим в таблице Evn - сможем отследить направления, ТАП, КВС и т.д.)
	 * @param $data
	 * @return bool|float|int|string
	 */
	public function checkToDelPerson($data)
	{
		return Person_model_check::checkToDelPerson($this, $data);
	}

	/**
	 * Проверка наличия ЗНО в последнем случае
	 * @param $data
	 * @return array|bool
	 */
	function checkEvnZNO_last($data)
	{
		return Person_model_check::checkEvnZNO_last($this, $data);
	}

	/**
	 * Проверка наличие согласия на рецепт в электрнной форме для формы выписки льготных рецептов
	 * @param $data
	 * @return array|bool
	 */
	function isReceptElectronicStatus($data)
	{
		return Person_model_check::isReceptElectronicStatus($this, $data);
	}

	/**
	 * Проверка на то, что возраст пацаиента меньше заданного
	 * @param $data
	 * @return array|bool
	 */
	function checkPersonAgeIsLess($data)
	{
		return Person_model_check::checkPersonAgeIsLess($this, $data);
	}

	/**
	 * Проверка, что дата смерти не больше даты рождения
	 * @param $data
	 * @return array|bool
	 */
	function checkPersonDeathDate($data)
	{
		return Person_model_check::checkPersonDeathDate($this, $data);
	}
	#endregion check
	#region common

    function deletePersonHeight($data) {
        $query = "			
            SELECT
            error_code as \"Error_Code\",
            error_message as \"Error_Msg\"
			FROM p_PersonHeight_del(
				  PersonHeight_id => :PersonHeight_id
			)
		";

        $result = $this->db->query($query, array(
            'PersonHeight_id' => $data['PersonHeight_id']
        ));

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     *	Получение данных об измерениях роста человека
     */
    function getPersonHeightViewData($data) {
        $query = "
			select
				PH.Person_id as \"Person_id\",
				0 as \"Children_Count\",
				PH.PersonHeight_id as \"PersonHeight_id\",
				PH.PersonHeight_id as \"Anthropometry_id\",				
				to_char(PH.PersonHeight_setDT, 'DD.MM.YYYY') as \"PersonHeight_setDate\",
				cast(PH.PersonHeight_Height as float) as \"PersonHeight_Height\",
				COALESCE(HMT.HeightMeasureType_Name, '') as \"HeightMeasureType_Name\",
				COALESCE(IsAbnorm.YesNo_Name, '') as \"PersonHeight_IsAbnorm\",
				COALESCE(HAT.HeightAbnormType_Name, '') as \"HeightAbnormType_Name\",
				PH.pmUser_insID as \"pmUser_insID\",
				COALESCE(PU.pmUser_Name, '') as \"pmUser_Name\"
			from
				v_PersonHeight PH
				inner join HeightMeasureType HMT on HMT.HeightMeasureType_id = PH.HeightMeasureType_id
				left join YesNo IsAbnorm on IsAbnorm.YesNo_id = PH.PersonHeight_IsAbnorm
				left join HeightAbnormType HAT on HAT.HeightAbnormType_id = PH.HeightAbnormType_id
				left join v_pmUser PU on PU.pmUser_id = COALESCE(PH.pmUser_updID, PH.pmUser_insID)
			where
				PH.Person_id = :Person_id
			order by
				PH.PersonHeight_setDT
		";

        $result = $this->db->query($query, array(
            'Person_id' => $data['Person_id']
        ));

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     *
     * @param type $data
     * @return type
     */
    function loadPersonHeightEditForm($data) {
        $query = "
			select
				PH.PersonHeight_id as \"PersonHeight_id\",
				PH.Person_id as \"Person_id\",
				PH.Evn_id as \"Evn_id\",
				PH.Server_id as \"Server_id\",
				PH.HeightAbnormType_id as \"HeightAbnormType_id\",
				PH.HeightMeasureType_id as \"HeightMeasureType_id\",
				PH.PersonHeight_IsAbnorm as \"PersonHeight_IsAbnorm\",
				PH.PersonHeight_Height as \"PersonHeight_Height\",				
				to_char(PH.PersonHeight_setDT, 'DD.MM.YYYY') as \"PersonHeight_setDate\"
			from
				v_PersonHeight PH
			where (1 = 1)
				and PH.PersonHeight_id = :PersonHeight_id
				limit 1
		";
        $result = $this->db->query($query, array(
            'PersonHeight_id' => $data['PersonHeight_id']
        ));

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    function loadPersonHeightGrid($data) {
        $this->load->model('PersonWeight_model');
        $filter = "(1 = 1)";
        $join_str = "";

        if ( !empty($data['mode']) && $data['mode'] == 'child' ) {
            $filter .= "
				and (PHSD.PersonHeight_setDT is null or PH.PersonHeight_setDT <= PHSD.PersonHeight_setDT)
			";
            $join_str = "
				left join lateral(
					select
						PHtmp.PersonHeight_setDT
					from
						v_PersonHeight PHtmp
						inner join HeightMeasureType HMTtmp on HMTtmp.HeightMeasureType_id = PHtmp.HeightMeasureType_id
					where PHtmp.Person_id = :Person_id
						and HMTtmp.HeightMeasureType_Code = 2
						limit 1
				) PHSD on true
			";
        }
        $params = array(
            'Person_id' => $data['Person_id']
        );
        if ( isset($data['HeightMeasureType_id']) && (!empty($data['HeightMeasureType_id']))) {
            $filter = $filter.' AND PH.HeightMeasureType_id = :HeightMeasureType_id ';
            $params['HeightMeasureType_id'] = $data['HeightMeasureType_id'];
        }

        $query = "
			select
				PH.PersonHeight_id as \"PersonHeight_id\",
				PH.Person_id as \"Person_id\",
				PH.Evn_id as \"Evn_id\",
				PH.PersonHeight_IsAbnorm as \"PersonHeight_IsAbnorm\",
				PH.HeightAbnormType_id as \"HeightAbnormType_id\",
				PH.HeightMeasureType_id as \"HeightMeasureType_id\",
				COALESCE(HMT.HeightMeasureType_Code, 0) as \"HeightMeasureType_Code\",				
				to_char(PH.PersonHeight_setDT, 'DD.MM.YYYY') as \"PersonHeight_setDate\",
				PH.PersonHeight_Height as \"PersonHeight_Height\",
				1 as \"RecordStatus_Code\"
			from
				v_PersonHeight PH
				inner join HeightMeasureType HMT on HMT.HeightMeasureType_id = PH.HeightMeasureType_id
				" . $join_str . "
			where " . $filter . "
				and PH.Person_id = :Person_id
		";

        $result = $this->db->query($query, $params)->result('array');
        $weight_arr = $this->PersonWeight_model->loadPersonWeightPanel($data);

        $info = array();
        $measures = array();
        $minAndMaxDate = array();
        // возможно в будущем стоит переделать, сейчас делаю именно такую структуру, чтобы минимально переделывать jsку
        for ($i=0; $i < (count($result)+count($weight_arr)); $i++) {
            $info[] = array('ChartInfo_id'=>$i, 'ObserveDate'=> $result[$i]['PersonHeight_setDate'], 'TimeOfDay_id'=>1, 'Complaint'=>null, 'FeedbackMethod_id'=>null);
            $measures[] = array('Measure_id'=>$i, 'Value'=>round($result[$i]['PersonHeight_Height']), 'ChartInfo_id'=>$i, 'RateType_id'=>36);
        }
        foreach ($info as $item) {
            $minAndMaxDate[] = $item['ObserveDate'];
        }
        $minAndMaxDate = array('minObserveDate'=>min($minAndMaxDate), 'maxObserveDate'=>max($minAndMaxDate));


//		var_dump(array(
//			'info'=> $info,//замеры
//			'measures'=>$measures, //отдельно данные по показателям к ним
//			'rates'=>array(23), //нормы
//			'totalCount'=>6, //количество замеров
//			'minimax'=>array('minObserveDate'=>'2019-03-05', 'maxObserveDate'=>'2019-04-10'), //временной промежуток на все замеры
//			'result' => $result
//		));
//		die();
//		return $result;
//		$result['data'] = array();
//		$result['data']['info'] = array();
//		$result['data']['measures'] = array();
//		$result['data']['rates'] = array();
//		$result['data'][] = array('totalCount'=>6);
//		$result['data']['minimax'] = array('minObserveDate'=> '123', 'maxObserveDate'=>'321');

//		$info = array(
//			array('ChartInfo_id'=>1, 'ObserveDate'=> '2019-12-12 12:59', 'TimeOfDay_id'=>1, 'Complaint'=>null, 'FeedbackMethod_id'=>null),
//			array('ChartInfo_id'=>2, 'ObserveDate'=> '2019-21-12 15:03', 'TimeOfDay_id'=>2, 'Complaint'=>null, 'FeedbackMethod_id'=>null)
//		);
//		$measures = array(
//			array('Measure_id'=>1, 'Value'=>230, 'ChartInfo_id'=>1, 'RateType_id'=>38),
//			array('Measure_id'=>2, 'Value'=>222, 'ChartInfo_id'=>2, 'RateType_id'=>53),
//		);


        return array(
            'info'=> $info,//замеры
            'measures'=>$measures, //отдельно данные по показателям к ним
            'rates'=>array(23), //нормы
            'totalCount'=>count($measures), //количество замеров
            'minimax'=>$minAndMaxDate, //временной промежуток на все замеры
            'result'=>$result
        );
    }

    /**
     *
     * @param array $data
     * @return array
     */
    function savePersonHeight($data) {
        $procedure = "p_PersonHeight_ins";

        if ( !empty($data['PersonHeight_id']) ) {
            $procedure = "p_PersonHeight_upd";
        }else if($data['HeightMeasureType_id']==1){
            $query = 'select PersonHeight_id as "PersonHeight_id" from v_PersonHeight where HeightMeasureType_id=1 and Person_id=:Person_id';
            $result = $this->db->query($query, array('Person_id'=>$data['Person_id']));
            if ( is_object($result) ) {
                $res = $result->result('array');
                if(count($res)>0){
                    $procedure = "p_PersonHeight_upd";
                    $data['PersonHeight_id'] = $res[0]['PersonHeight_id'];
                }
            }
        }

        $query = "
			SELECT
			error_code as \"Error_Code\",
            error_message as \"Error_Msg\",
            PersonHeight_id as \"PersonHeight_id\"
			FROM " . $procedure . "(
				Server_id => :Server_id,				
				Person_id => :Person_id,
				PersonHeight_setDT => :PersonHeight_setDate,
				PersonHeight_Height => :PersonHeight_Height,
				PersonHeight_IsAbnorm => :PersonHeight_IsAbnorm,
				HeightAbnormType_id => :HeightAbnormType_id,
				HeightMeasureType_id => :HeightMeasureType_id,
				Okei_id => 2,
				Evn_id => :Evn_id,
				pmUser_id => :pmUser_id,
			)";

        $queryParams = array(
            'Server_id' => $data['Server_id'],
            'PersonHeight_id' => (!empty($data['PersonHeight_id']) ? $data['PersonHeight_id'] : NULL),
            'Person_id' => $data['Person_id'],
            'PersonHeight_setDate' => $data['PersonHeight_setDate'],
            'PersonHeight_Height' => $data['PersonHeight_Height'],
            'PersonHeight_IsAbnorm' => (!empty($data['PersonHeight_IsAbnorm']) ? $data['PersonHeight_IsAbnorm'] : NULL),
            'HeightAbnormType_id' => (!empty($data['HeightAbnormType_id']) ? $data['HeightAbnormType_id'] : NULL),
            'HeightMeasureType_id' => $data['HeightMeasureType_id'],
            'Evn_id'=>(!empty($data['Evn_id']) ? $data['Evn_id'] : NULL),
            'pmUser_id' => $data['pmUser_id']
        );

        $result = $this->db->query($query, $queryParams);

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения роста пациента)'));
        }
    }

    /**
     * Получение списка измерений массы пациента для ЭМК
     */
    function loadPersonHeightPanel($data) {

        $filter = " ph.Person_id = :Person_id ";

        // для оффлайн режима
        if (!empty($data['person_in'])) {
            $filter = " ph.Person_id in ({$data['person_in']}) ";
        }

        return $this->queryResult("
    		select
    			ph.PersonHeight_id as \"PersonHeight_id\",
    			ph.PersonHeight_Height as \"PersonHeight_Height\",
    			to_char(ph.PersonHeight_setDT, 'DD.MM.YYYY') as \"PersonHeight_setDate\",
    			wmt.HeightMeasureType_Name as \"HeightMeasureType_Name\",
    			wat.HeightAbnormType_Name as \"HeightAbnormType_Name\",
    			ph.Person_id as \"Person_id\"
    		from
    			v_PersonHeight ph
    			left join v_HeightMeasureType wmt on wmt.HeightMeasureType_id = ph.HeightMeasureType_id
    			left join v_HeightAbnormType wat on wat.HeightAbnormType_id = ph.HeightAbnormType_id
    		where {$filter}
    	", array(
            'Person_id' => $data['Person_id']
        ));
    }
	/**
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function mSendPersonCallNotify($data)
	{
		return Person_model_common::mSendPersonCallNotify($this, $data);
	}
	/**
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function addPersonPhoneHist($data)
	{
		return Person_model_common::addPersonPhoneHist($this, $data);
	}

	/**
	 * Добавление данных человека на идентификацию в ЦС ЕРЗ
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function addPersonRequestData($data)
	{
		return Person_model_common::addPersonRequestData($this, $data);
	}

	/**
	 * @param $value
	 * @param $pmUser_id
	 * @return array|false
	 * @throws Exception
	 */
	function addSpecObject($value, $pmUser_id)
	{
		return Person_model_common::addPersonRequestData($value, $pmUser_id);
	}

	/**
	 * Изменение признака подозрения на ЗНО
	 * @param $data
	 * @return array
	 */
	function changeEvnZNO($data)
	{
		return Person_model_common::changeEvnZNO($this, $data);
	}

	/**
	 * Обновляем "Организацию"
	 * @param $Person_id
	 * @param $new_Org_id
	 * @param $data
	 * @return bool
	 */
	static public function update_Org_id($Person_id, $new_Org_id, $data)
	{
		if (!is_numeric($Person_id) || empty($Person_id)) {
			return false;
		}
		if (!is_numeric($new_Org_id) || empty($new_Org_id)) {
			return false;
		}
		if (!is_array($data) || empty($data)) {
			return false;
		}
		if (!isset($data["Server_id"]) || empty($data["Server_id"])) {
			return false;
		}
		if (!isset($data["pmUser_id"]) || empty($data["pmUser_id"])) {
			return false;
		}
		$funcParams = [
			"Server_id" => $data["Server_id"],
			"Person_id" => $Person_id,
			"Org_id" => $new_Org_id,
			"pmUser_id" => $data["pmUser_id"]
		];
		self::update_PersonJob($funcParams);
		return true;
	}

	/**
	 * Обновляем "Должность"
	 * @param $Person_id
	 * @param $new_Post_id
	 * @param $data
	 * @return bool
	 */
	static public function update_Post_id($Person_id, $new_Post_id, $data)
	{
		if (!is_numeric($Person_id) || empty($Person_id)) {
			return false;
		}
		if (!is_numeric($new_Post_id) || empty($new_Post_id)) {
			return false;
		}
		if (!is_array($data) || empty($data)) {
			return false;
		}
		if (!isset($data["Server_id"]) || empty($data["Server_id"])) {
			return false;
		}
		if (!isset($data["pmUser_id"]) || empty($data["pmUser_id"])) {
			return false;
		}
		$funcParams = [
			"Server_id" => $data["Server_id"],
			"Person_id" => $Person_id,
			"Post_id" => $new_Post_id,
			"pmUser_id" => $data["pmUser_id"]
		];
		self::update_PersonJob($funcParams);
		return true;
	}

	/**
	 * Обновляем данные блока "Место работы": поля "Организация" (Org_id), "Должность" (Post_id) и "Подразделение" (OrgUnion_id)
	 * @param array $data
	 * @return bool
	 */
	public static function update_PersonJob($data = [])
	{
		$CI = &get_instance();
		if (!isset($data["Org_id"]) && !isset($data["OrgUnion_id"]) && !isset($data["Post_id"])) {
			return false;
		}
		$params = [
			"Server_id" => $data["Server_id"],
			"Person_id" => $data["Person_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$Org_idString = "";
		$OrgUnion_idString = "";
		$Post_idString = "";
		if (isset($data["Org_id"]) && !empty($data["Org_id"])) {
			$params["Org_id"] = $data["Org_id"];
			$Org_idString = (isset($params["Org_id"]) && !empty($params["Org_id"])) ? "Org_id := :Org_id," : "";
		}
		if (isset($data["OrgUnion_id"]) && !empty($data["OrgUnion_id"])) {
			$params["OrgUnion_id"] = $data["OrgUnion_id"];
			$OrgUnion_idString = (isset($params["OrgUnion_id"]) && !empty($params["OrgUnion_id"])) ? "OrgUnion_id := :OrgUnion_id," : "";
		}
		if (isset($data["Post_id"]) && !empty($data["Post_id"])) {
			$params["Post_id"] = $data["Post_id"];
			$Post_idString = (isset($params["Post_id"]) && !empty($params["Post_id"])) ? "Post_id := :Post_id," : "";
		}
		$query = "
			select Error_Message as \"ErrMsg\"
			from dbo.p_PersonJob_ins(
				Server_id := :Server_id,
				Person_id := :Person_id,
				{$Org_idString}
				{$OrgUnion_idString}
				{$Post_idString}
				pmUser_id := :pmUser_id
			);
		";
		$CI->db->query($query, $params);
		return true;
	}

	/**
	 * Обновление данных человека
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public function updatePerson($data)
	{
		return Person_model_common::updatePerson($this, $data);
	}

	/**
	 * Определение сервера по PersonEvn_id
	 * @param $data
	 * @return array
	 */
	function serverByPersonEvn($data)
	{
		return Person_model_common::serverByPersonEvn($this, $data);
	}

	/**
	 * Обновление статусов запроса на идентификацию в ЦС ЕРЗ
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function setPersonRequestDataStatus($data)
	{
		return Person_model_common::setPersonRequestDataStatus($this, $data);
	}

	/**
	 * Функция замены символов для поиска
	 * @param $value
	 * @param array $symbols
	 * @return string|string[]|null
	 */
	function prepareSearchSymbol($value, $symbols = [])
	{
		return Person_model_common::prepareSearchSymbol($value, $symbols);
	}

	/**
	 * "Воскрешает" человека
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function revivePerson($data)
	{
		return Person_model_common::revivePerson($this, $data);
	}

	/**
	 * "Убивает" человека
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function killPerson($data)
	{
		return Person_model_common::killPerson($this, $data);
	}

	/**
	 * Перечитать историю
	 * @param $data
	 * @return array
	 */
	function extendPersonHistory($data)
	{
		return Person_model_common::extendPersonHistory($this, $data);
	}

	/**
	 * Поиск пациента по полису/документам, удостоверяющим личность/СНИЛС/ФИО
	 * @param $paramType
	 * @param $data
	 * @return array|bool|false
	 * @throws Exception
	 */
	public function findPersonByParams($paramType, $data)
	{
		return Person_model_common::findPersonByParams($this, $paramType, $data);
	}
	#endregion common
	#region edit
	/**
	 * Удаление данных человека
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public function deletePerson($data)
	{
		return Person_model_edit::deletePerson($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function deletePersonEval($data)
	{
		return Person_model_edit::deletePersonEval($this, $data);
	}

	/**
	 * Удаление атрибута
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deletePersonEvnAttribute($data)
	{
		return Person_model_edit::deletePersonEvnAttribute($this, $data);
	}

	/**
	 * Удаление данных о сотруднике организации
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function deletePersonWork($data)
	{
		return Person_model_edit::deletePersonWork($this, $data);
	}

	/**
	 * Редактирование атрибута
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function editPersonEvnAttribute($data)
	{
		return Person_model_edit::editPersonEvnAttribute($this, $data);
	}

	/**
	 * Редактирование атрибута
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function editPersonEvnAttributeNew($data)
	{
		return Person_model_edit::editPersonEvnAttributeNew($this, $data);
	}

	/**
	 * Сохранение EvnDate
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function editPersonEvnDate($data)
	{
		return Person_model_edit::editPersonEvnDate($this, $data);
	}
	#endregion edit
	#region export
	/**
	 * Экспорт людей из картотеки
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function exportPersonCardForIdentification($data)
	{
		return Person_model_export::exportPersonCardForIdentification($this, $data);
	}

	/**
	 * Экспорт реестров неработающих застрахованных лиц
	 * @param $data
	 * @return bool|CI_DB_result|mixed
	 */
	function exportPersonPolisToXml($data)
	{
		return Person_model_export::exportPersonPolisToXml($this, $data);
	}

	/**
	 * Данные по профилактическим мероприятиям
	 * @param $data
	 * @return array|bool|string
	 * @throws Exception
	 */
	public function exportPersonProfData($data)
	{
		return Person_model_export::exportPersonProfData($this, $data);
	}

	/**
	 * Проверка документа
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function validateDocument($data)
	{
		return Person_model_export::validateDocument($this, $data);
	}

	/**
	 * Проверка гражданства
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function validateNationalityStatus($data)
	{
		return Person_model_export::validateNationalityStatus($this, $data);
	}

	/**
	 * Проверка результатов выполнения запроса, возврат ошибки, если что-то пошло не так.
	 * @param $res
	 * @throws Exception
	 */
	function ValidateInsertQuery($res)
	{
		Person_model_export::ValidateInsertQuery($res);
	}
	#endregion export
	#region save
	/**
	 * Сохранение СМО данных
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function savePersonSmoData($data)
	{
		return Person_model_save::savePersonSmoData($this, $data);
	}

	/**
	 * Метод редактирует или добавляет данные периодики человека, относительно определенного события добавления периодики.
	 *  $data['ObjectName'] - наименование сохраняемого объекта
	 *  $data['ObjectField'] - поля, сохраняемого объекта
	 *  $data['ObjectData'] - сохраняемое значение
	 *  $data['Server_id'] - идентификатор сервера
	 *  $data['Person_id'] - идентификатор персона
	 *  $data['PersonEvn_id'] - идентификатор события вставки периодики
	 *  $data['pmUser_id'] - идентификатор пользователя
	 *  $data['PersonEvnClass_id'] - идентификатор события вставки периодики
	 * @param $data
	 * @return bool|void
	 * @throws Exception
	 */
	function savePersonEvnSimpleAttr($data)
	{
		return Person_model_save::savePersonEvnSimpleAttr($this, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function savePersonEvalEditWindow($data)
	{
		return Person_model_save::savePersonEvalEditWindow($this, $data);
	}

	/**
	 * Метод редактирует или добавляет данные периодики человека, если последний атрибут с датой меньше заданной не совпадает с новым.
	 *  $data['ObjectName'] - наименование сохраняемого объекта
	 *  $data['ObjectField'] - поля, сохраняемого объекта
	 *  $data['ObjectData'] - сохраняемое значение
	 *  $data['Server_id'] - идентификатор сервера
	 *  $data['Person_id'] - идентификатор персона
	 *  $data['pmUser_id'] - идентификатор пользователя
	 *  $data['PersonEvnClass_id'] - идентификатор события вставки периодики
	 *  $data['insDT'] - заданная дата
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function savePersonEvnSimpleAttrNew($data)
	{
		return Person_model_save::savePersonEvnSimpleAttrNew($this, $data);
	}

	/**
	 * Сохранение атрибутов
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveAttributeOnDate($data)
	{
		return Person_model_save::saveAttributeOnDate($this, $data);
	}

	/**
	 * Сохранение адреса регистрации
	 * @param $data
	 * @return array|false
	 */
	function savePersonUAddress($data, $adressType = 'U')
	{
		return Person_model_save::savePersonUAddress($this, $data, $adressType);
	}

	/**
	 * Сохранение информации о человеке
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public function savePersonInfo($data)
	{
		return Person_model_save::savePersonInfo($this, $data);
	}

	/**
	 * Сохранение специфики детства
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function savePersonChild($data)
	{
		return Person_model_save::savePersonChild($this, $data);
	}

	/**
	 * Сохранение данных стран кроме РФ, которые ранее отсутствовали
	 * Эта функция сохраняет и возвращает ИД только 1 части адреса: "Регион", "Район", "Город", "Нас. пункт", "Улица"
	 * @param $srv_id
	 * @param $user_id
	 * @param $country
	 * @param $level
	 * @param $name
	 * @param $pid
	 * @param $socr_id
	 * @return mixed|null
	 * @throws Exception
	 */
	function saveAddressPart($srv_id, $user_id, $country, $level, $name, $pid, $socr_id)
	{
		return Person_model_save::saveAddressPart($this, $srv_id, $user_id, $country, $level, $name, $pid, $socr_id);
	}

	/**
	 * Сохранение адреса
	 * @param $srv_id
	 * @param $user_id
	 * @param $country
	 * @param $region
	 * @param $subregion
	 * @param $city
	 * @param $town
	 * @param $street
	 * @param $region_socr
	 * @param $subregion_socr
	 * @param $city_socr
	 * @param $town_socr
	 * @param $street_socr
	 * @return array
	 * @throws Exception
	 */
	function saveAddressAll($srv_id, $user_id, $country, $region, $subregion, $city, $town, $street, $region_socr, $subregion_socr, $city_socr, $town_socr, $street_socr)
	{
		return Person_model_save::saveAddressAll($this, $srv_id, $user_id, $country, $region, $subregion, $city, $town, $street, $region_socr, $subregion_socr, $city_socr, $town_socr, $street_socr);
	}

	/**
	 * Сохранение телефона пациента в промеде
	 * @param $data
	 * @return array|bool
	 */
	function savePersonPhoneInfo($data)
	{
		return Person_model_save::savePersonPhoneInfo($this, $data);
	}

	/**
	 * Сохранение согласия/отзыва согласия на обработку перс.данных
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function savePersonLpuInfo($data)
	{
		return Person_model_save::savePersonLpuInfo($this, $data);
	}

    /**
     * Сохранение согласия/отзыва согласия на обработку перс.данных
     */
    function saveElectroReceptInfo($data) {
        if (!empty($data['Refuse']) && empty($data['ReceptElectronic_id'])){
            return false;
        }

        if (!empty($data['Refuse'])) {
            $query = "			
			update
				ReceptElectronic
			set
				ReceptElectronic_endDT = dbo.tzGetDate(),
				ReceptElectronic_updDT = dbo.tzGetDate(),
				pmUser_updID = :pmUser_id
			where 
				Person_id = :Person_id and 
				ReceptElectronic_id = :ReceptElectronic_id
			RETURNING
				'' as \"Error_Code\",
                '' as \"Error_Msg\",
                ReceptElectronic_id as \"ReceptElectronic_id\"
			";
        } else {
            $query = "
            SELECT
            error_code as \"Error_Code\",
            error_message as \"Error_Msg\",
            ReceptElectronic_id as \"ReceptElectronic_id\"
            FROM            			
			dbo.p_ReceptElectronic_ins
			(
				Person_id => :Person_id,
				Lpu_id => :Lpu_id,
				ReceptElectronic_begDT => dbo.tzGetDate(),                  
				pmUser_id => :pmUser_id
			)";
        }

        $resp = $this->queryResult($query, $data);
        if (!is_array($resp)) {
            return $this->createError('Ошибка при сохранении согласия/отзыва на оформление рецепта в форме электронного документа');
        }
        return $resp;
    }

	/**
	 * Обновляем атрибут для апи (пока только полиса данные)
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function savePersonAttributeForApi($data)
	{
		return Person_model_save::savePersonAttributeForApi($this, $data);
	}

	/**
	 * Сохранение СНИЛС
	 */
	function savePersonSnils($data) {
		$params = [
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'Person_Snils' => $data['Person_Snils'],
			'pmUser_id' => $data['pmUser_id']
		];

		$sql = "
			select
				Error_Code as \"Error_Code\"
				Error_Message as \"Error_Msg\"
			from p_PersonSnils_ins(
				Server_id := :Server_id,
				Person_id := :Person_id,
				PersonSnils_Snils := :Person_Snils,
				pmUser_id := :pmUser_id
			)
		";

		return $this->queryResult($sql, $params);
	}

	function getPersonRecordsAll($data) {
		return Person_model_get::getPersonRecordsAll($this, $data);
	}
	#endregion save

	/**
	 * @param $data
	 * @return array
	 */
	function getPersonForMedSvid($data) {
		return Person_model_get::getPersonForMedSvid($this, $data);
	}
}
