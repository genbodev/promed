<?php
defined("BASEPATH") or die ("No direct script access allowed");
require_once("EvnPrescrAbstract_model.php");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		09.2013
 *
 * Модель назначения "Лабораторная диагностика"
 *
 * Назначения с типом "Лабораторная диагностика" хранятся в таблицах EvnPrescrLabDiag, EvnPrescrLabDiagUsluga
 * В назначении должна быть указана только одна услуга.
 * Если услуга имеет состав (UslugaComplexComposition), то могут выбраны все или лишь некоторые простые услуги из её состава.
 * Для каждой выбранной простой услуги из состава создается запись в EvnPrescrLabDiagUsluga.
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 * @property EvnDirectionAll_model $EvnDirectionAll_model
 * @property EvnLabRequest_model $EvnLabRequest_model
 * @property EvnDirection_model $EvnDirection_model
 */
class EvnPrescrLabDiag_model extends EvnPrescrAbstract_model
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";

	public function __construct()
	{
		parent::__construct();
		if ($this->usePostgreLis) {
			$this->load->swapi("lis");
		}
	}

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId()
	{
		return 11;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName()
	{
		return "EvnPrescrLabDiag";
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $scenario
	 * @return array
	 */
	public function getInputRules($scenario)
	{
		$rules = [];
		switch ($scenario) {
			case "doSave":
				$rules = [
					["field" => "parentEvnClass_SysNick", "label" => "Системное имя род.события", "rules" => "", "default" => "EvnSection", "type" => "string"],
					["field" => "signature", "label" => "Признак для подписания", "rules" => "", "type" => "int"],
					["field" => "EvnPrescrLabDiag_id", "label" => "Идентификатор назначения", "rules" => "", "type" => "id"],
					["field" => "EvnPrescrLabDiag_pid", "label" => "Идентификатор род.события", "rules" => "required", "type" => "id"],
					["field" => "MedService_id", "label" => "Служба", "rules" => "", "type" => "id"],
					["field" => "UslugaComplex_id", "label" => "Услуга", "rules" => "required", "type" => "id"],
                    ["field" => "UslugaComplexMedService_pid", "label" => "Связь услуги и службы", "rules" => "", "type" => "id"],
                    ["field" => "EvnPrescrLabDiag_uslugaList", "label" => "Список услуг, выбранных из состава комплексной услуги", "rules" => "", "type" => "string"],
					["field" => "EvnPrescrLabDiag_setDate", "label" => "Плановая дата", "rules" => "", "type" => "date"],
					["field" => "EvnPrescrLabDiag_IsCito", "label" => "Cito", "rules" => "", "type" => "string"],
					["field" => "EvnPrescrLabDiag_Descr", "label" => "Комментарий", "rules" => "trim", "type" => "string"],
					["field" => "EvnDirection_id", "label" => "Идентификатор", "rules" => "", "type" => "id"],
					["field" => "DopDispInfoConsent_id", "label" => "Идентификатор согласия карты диспансеризации", "rules" => "", "type" => "id"],
					["field" => "PersonEvn_id", "label" => "Идентификатор", "rules" => "required", "type" => "id"],
					["field" => "Server_id", "label" => "Идентификатор сервера", "rules" => "required", "type" => "int"],
					["field" => "EvnPrescrLimitData", "label" => "Ограничения", "rules" => "", "type" => "string"],
					["field" => "EvnUslugaOrder_id", "label" => "Идентификатор заказа", "rules" => "", "type" => "id"],
					["field" => "EvnUslugaOrder_UslugaChecked", "label" => "Измененный состав для обновления заказа", "rules" => "", "type" => "string"],
					["field" => "StudyTarget_id", "label" => "Цель исследования", "rules" => "", "type" => "string"],
					['field' => 'MedService_pzmid','label' => 'Пункт забора','rules' => '','type' => 'string'],
					["field" => "EvnPrescrLabDiag_CountComposit", "label" => "количество сохраненных услуг в составной услуге", "rules" => "", "type" => "int"],
					["field" => "isExt6", "label" => "Сохранение из формы Ext6", "rules" => "", "type" => "int"],
					["field" => "HIVContingentTypeFRMIS_id", "label" => "Код контингента ВИЧ", "rules" => "", "type" => "int"],
					["field" => "CovidContingentType_id", "label" => "Код контингента COVID", "rules" => "", "type" => "int"],
					["field" => "UslugaComplexContent_ids", "label" => "Измененный состав для обновления заказа", "rules" => "", "type" => "string"]
				];
				break;
			case "doLoad":
				$rules[] = [
					"field" => "EvnPrescrLabDiag_id",
					"label" => "Идентификатор назначения",
					"rules" => "required",
					"type" => "id"
				];
				break;
		}
		return $rules;
	}

	/**
	 * Сохранение назначения
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array|bool
	 * @throws Exception
	 */
	public function doSave($data = [], $isAllowTransaction = true)
	{
		if (empty($data["EvnPrescrLabDiag_id"])) {
			$action = "ins";
			$data["EvnPrescrLabDiag_id"] = null;
			$data["PrescriptionStatusType_id"] = 1;
            $EvnPrescr_id = NULL;
		} else {
			$action = "upd";
            $EvnPrescr_id = $data['EvnPrescrLabDiag_id'];
			$o_data = $this->getAllData($data["EvnPrescrLabDiag_id"]);
			if (!empty($o_data["Error_Msg"])) {
				return [$o_data];
			}
			foreach ($o_data as $k => $v) {
				if (!isset($data[$k])) {
					$data[$k] = $v;
				}
			}
		}
        if(!isset($data['UslugaComplexMedService_pid'])) $data['UslugaComplexMedService_pid'] = null;
		if (empty($data["EvnPrescrLabDiag_CountComposit"])) {
			$data["EvnPrescrLabDiag_CountComposit"] = null;
		}
		$selectString = "
			evnprescrlabdiag_id as \"EvnPrescrLabDiag_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		if (!isset($data['PrescriptionStatusType_id']) && isset($data['EvnPrescrLabDiag_id'])) {
			$data['PrescriptionStatusType_id'] = $this->dbmodel->getFirstResultFromQuery("SELECT PrescriptionStatusType_id FROM dbo.evnprescrlabdiag WHERE evn_id = ".$data['EvnPrescrLabDiag_id']."  limit 1");
		}
		$query = "
			select {$selectString}
			from p_evnprescrlabdiag_{$action}(
			    evnprescrlabdiag_id := :EvnPrescrLabDiag_id,
			    evnprescrlabdiag_pid := :EvnPrescrLabDiag_pid,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnprescrlabdiag_setdt := :EvnPrescrLabDiag_setDate,
			    prescriptiontype_id := :PrescriptionType_id,
			    evnprescrlabdiag_iscito := :EvnPrescrLabDiag_IsCito,
			    prescriptionstatustype_id := :PrescriptionStatusType_id,
			    evnprescrlabdiag_descr := :EvnPrescrLabDiag_Descr,
			    dopdispinfoconsent_id := :DopDispInfoConsent_id,
			    studytarget_id := :StudyTarget_id,
			    medservice_id := :MedService_id,
			    UslugaComplexMedService_id := :UslugaComplexMedService_pid,
			    evnprescrlabdiag_countcomposit := :EvnPrescrLabDiag_CountComposit,
			    uslugacomplex_id := :UslugaComplex_id,
			    medservice_pzmid := :MedService_pzmid,
			    pmuser_id := :pmUser_id
			);
		";
		$data["EvnPrescrLabDiag_IsCito"] = (empty($data["EvnPrescrLabDiag_IsCito"]) || $data["EvnPrescrLabDiag_IsCito"] != "on") ? 1 : 2;
		$data["PrescriptionType_id"] = $this->getPrescriptionTypeId();
		if (empty($data["DopDispInfoConsent_id"])) {
			$data["DopDispInfoConsent_id"] = null;
		}
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$trans_result = $result->result("array");
		if (empty($trans_result) || empty($trans_result[0]) || empty($trans_result[0]["EvnPrescrLabDiag_id"]) || !empty($trans_result[0]["Error_Msg"])) {
			return false;
		}
		$EvnPrescr_id = $trans_result[0]["EvnPrescrLabDiag_id"];
		$uslugalist = [];
		if (!empty($data["EvnPrescrLimitData"])) {
			$data["EvnPrescrLimitData"] = toUtf($data["EvnPrescrLimitData"]);
			$limitdata = json_decode($data["EvnPrescrLimitData"], true);
			foreach ($limitdata as $limit) {
				$limit["EvnPrescrLimit_id"] = null;
				$limit["LimitType_IsCatalog"] = 1;
				$limit["EvnPrescr_id"] = $EvnPrescr_id;
				$limit["pmUser_id"] = $data["pmUser_id"];
				if (!empty($limit["LimitType_id"])) {
					// 1. ищем запись для соответвующего LimitType_id и RefValues_id
					$query = "
						select
							epl.EvnPrescrLimit_id as \"EvnPrescrLimit_id\",
							coalesce(lt.LimitType_IsCatalog, 1) as \"LimitType_IsCatalog\"
						from
							v_LimitType lt
							left join v_EvnPrescrLimit epl on epl.LimitType_id = lt.LimitType_id and epl.EvnPrescr_id = :EvnPrescr_id
						where lt.LimitType_id = :LimitType_id
						limit 1
					";
					$result = $this->db->query($query, $limit);
					if (is_object($result)) {
						$resp = $result->result("array");
						if (!empty($resp[0]["EvnPrescrLimit_id"])) {
							$limit["EvnPrescrLimit_id"] = $resp[0]["EvnPrescrLimit_id"];
						}
						if (!empty($resp[0]["LimitType_IsCatalog"])) {
							$limit["LimitType_IsCatalog"] = $resp[0]["LimitType_IsCatalog"];
						}
					}
					// 2. сохраняем
					$procedure = (!empty($limit["EvnPrescrLimit_id"])) ? "p_EvnPrescrLimit_upd" : "p_EvnPrescrLimit_ins";
					if (empty($limit["EvnPrescrLimit_ValuesNum"])) {
						$limit["EvnPrescrLimit_ValuesNum"] = null;
					}
					if (empty($limit["EvnPrescrLimit_Values"])) {
						$limit["EvnPrescrLimit_Values"] = null;
					}
					$selectString = "
						evnprescrlimit_id as \"EvnPrescrLimit_id\",
						error_code as \"Error_Code\",
						error_message as \"Error_Msg\"
					";
					$query = "
						select {$selectString}
						from {$procedure}(
						    evnprescrlimit_id := :EvnPrescrLimit_id,
						    evnprescr_id := :EvnPrescr_id,
						    limittype_id := :LimitType_id,
						    evnprescrlimit_values := :EvnPrescrLimit_Values,
						    evnprescrlimit_valuesnum := :EvnPrescrLimit_ValuesNum,
						    pmuser_id := :pmUser_id
						);
					";
					$result = $this->db->query($query, $limit);
					if (!is_object($result)) {
						throw new Exception("Ошибка запроса к БД");
					}
					$res = $result->result("array");
					if (empty($res) || empty($res[0]) || !empty($res[0]["Error_Msg"])) {
						throw new Exception($res[0]["Error_Msg"]);
					}
				}
			}
		}
		if (!empty($data["EvnPrescrLabDiag_uslugaList"])) {
			$uslugalist = explode(",", $data["EvnPrescrLabDiag_uslugaList"]);
			if (empty($uslugalist) || !is_numeric($uslugalist[0])) {
				throw new Exception("Ошибка формата списка услуг");
			}
			if (isset($data["EvnPrescrLabDiag_id"])) {
				//запрос на поиск уже заведенных лаб. услуг по назначению,
				//которые есть в заказе, но содержатся в других параклинических услугах
				$uslugalistString = implode(", ", $uslugalist);
				$query = "
					with uslugas as (
						select
							euc.UslugaComplex_id,
							euc.EvnUslugaPar_id
						from
							v_EvnLabRequestUslugaComplex euc
							left join v_EvnLabRequest elr on euc.EvnLabRequest_id = elr.EvnLabRequest_id
						where
							elr.EvnDirection_id in (
								select EvnDirection_id
								from v_EvnPrescrDirection
								where EvnPrescr_id = :EvnPrescrLabDiag_id
								limit 1
							) 	
					)
					select 
					    UslugaComplex_id as \"UslugaComplex_id\",
					    EvnUslugaPar_id as \"EvnUslugaPar_id\"
					from uslugas u1
					where u1.EvnUslugaPar_id not in (
						select EvnUslugaPar_id
						from uslugas
						where UslugaComplex_id in ({$uslugalistString})
					)
				";
				$res = $this->queryResult($query, $data);
				if ($res) {
					$existingIds = [];
					foreach ($res as $re) {
						$existingIds[] = $re["UslugaComplex_id"];
					}
				}
			}

			$res = $this->clearEvnPrescrLabDiagUsluga(["EvnPrescrLabDiag_id" => $EvnPrescr_id]);
			if (empty($res)) {
				throw new Exception("Ошибка запроса списка выбранных услуг");
			}
			if (!empty($res) && !empty($res[0]) && !empty($res[0]['Error_Msg'])) {
				throw new Exception($res[0]["Error_Msg"]);
			}
		}
		if (!empty($uslugalist)) {
			foreach ($uslugalist as $d) {
				$res = $this->saveEvnPrescrLabDiagUsluga([
					"UslugaComplex_id" => $d,
					"EvnPrescrLabDiag_id" => $EvnPrescr_id,
					"pmUser_id" => $data["pmUser_id"]
				]);
				if (empty($res)) {
					throw new Exception("Ошибка запроса при сохранении услуги");
				}
				if (!empty($res) && !empty($res[0]) && !empty($res[0]["Error_Msg"])) {
					throw new Exception($res[0]["Error_Msg"]);
				}
			}
		}
		if(!empty($data['EvnUslugaOrder_UslugaChecked']) && empty($data['EvnUslugaOrder_id']) && !empty($data['EvnDirection_id'])) {
			//попробуем достать EvnUslugaOrder_id для следующего блока, если из формы параметр не пришел.
			$EvnUslugaOrder_id = null;
			if ($this->usePostgreLis) {
				$resEvnUslugaOrder_id = $this->lis->GET('EvnUsluga/EvnUslugaParByEvnDirection', array('EvnDirection_id' => $data['EvnDirection_id']));
				if($resEvnUslugaOrder_id['error_code'] != 0) {
					$trans_good = false;
					$trans_result[0]['Error_Msg'] = 'Ошибка получения идентификатора услуги в ЛИС';
				} else $EvnUslugaOrder_id = $resEvnUslugaOrder_id['data'];
			} else {
				$sql = "
					select
						eup.EvnUslugaPar_id
					from
						v_EvnUslugaPar eup
					where 
						eup.EvnDirection_id = :EvnDirection_id
				";
				$EvnUslugaOrder_id = $this->getFirstResultFromQuery($sql, $data);
			}
			$data['EvnUslugaOrder_id'] = $EvnUslugaOrder_id;
		}
		if (!empty($data["EvnUslugaOrder_id"]) && !empty($data["EvnUslugaOrder_UslugaChecked"])) {
			$uslugalist = explode(",", $data["EvnUslugaOrder_UslugaChecked"]);
			if (!empty($existingIds)) {
				$uslugalist = array_merge($uslugalist, $existingIds);
			}
			if (empty($uslugalist) || !is_numeric($uslugalist[0])) {
				throw new Exception("Ошибка формата списка заказанных услуг");
			} else {
				if ($this->usePostgreLis) {
					$resp = $this->lis->PATCH("EvnLabRequest/EvnUslugaPar/Result", [
						"EvnUslugaPar_id" => $data["EvnUslugaOrder_id"],
						"EvnUslugaPar_Result" => json_encode($uslugalist),
					], "list");
				} else {
					$this->load->model("EvnLabRequest_model");
					$resp = $this->EvnLabRequest_model->updateEvnUslugaParResult([
						"EvnUslugaPar_id" => $data["EvnUslugaOrder_id"],
						"EvnUslugaPar_Result" => json_encode($uslugalist),
						"pmUser_id" => $data["pmUser_id"]
					]);
				}
				if (!$this->isSuccessful($resp)) {
					throw new Exception((!empty($resp[0]['Error_Msg']))?$resp[0]['Error_Msg']:'Ошибка изменения состава');
				}
			}
		}
		// Обновление тестов в заказе по-новому для новой формы, РАБОТАЕТ!!!
		if ($action === "upd" && !empty($uslugalist) && !empty($data["EvnUslugaOrder_id"]) && !empty($data["EvnDirection_id"])) {
			if ($this->usePostgreLis) {
				$resp = $this->lis->POST("EvnLabRequest/Content", $data, "single");
			} else {
				$this->load->model("EvnLabRequest_model");
				$resp = $this->EvnLabRequest_model->saveEvnLabRequestContent($data);
			}
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp["Error_Msg"]);
			}
		}
		if ($action === "upd" && !empty($uslugalist) && !empty($data["EvnUslugaOrder_id"]) && empty($data["isExt6"])) {
			if ($this->usePostgreLis) {
				$resp = $this->lis->POST("EvnLabRequest/EvnUslugaPar/recache", [
					"EvnUslugaPar_id" => $data["EvnUslugaOrder_id"],
					"uslugaList" => json_encode($uslugalist)
				], "list");
			} else {
				$this->load->model("EvnLabRequest_model");
				$resp = $this->EvnLabRequest_model->ReCacheEvnUslugaPar([
					"EvnUslugaPar_id" => $data["EvnUslugaOrder_id"],
					"uslugaList" => json_encode($uslugalist),
					"pmUser_id" => $data["pmUser_id"]
				]);
			}
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]["Error_Msg"]);
			}
		}
		return $trans_result;
	}

	/**
	 * Метод очистки списка услуг
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function clearEvnPrescrLabDiagUsluga($data)
	{
		return $this->clearEvnPrescrTable([
			"object" => "EvnPrescrLabDiagUsluga",
			"fk_pid" => "EvnPrescrLabDiag_id",
			"pid" => $data["EvnPrescrLabDiag_id"]
		]);
	}

	/**
	 * Сохранение назнач
	 * @param $data
	 * @return array|bool
	 */
	function saveEvnPrescrLabDiagUsluga($data)
	{
		$code = !empty($data['EvnPrescrLabDiagUsluga_id']) ? "upd" : "ins";
		$selectString = "
			evnprescrlabdiagusluga_id as \"EvnPrescrLabDiagUsluga_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from p_evnprescrlabdiagusluga_{$code}(
			    evnprescrlabdiagusluga_id := :EvnPrescrLabDiagUsluga_id,
			    evnprescrlabdiag_id := :EvnPrescrLabDiag_id,
			    uslugacomplex_id := :UslugaComplex_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnPrescrLabDiagUsluga_id" => (empty($data["EvnPrescrLabDiagUsluga_id"]) ? NULL : $data["EvnPrescrLabDiagUsluga_id"]),
			"EvnPrescrLabDiag_id" => $data["EvnPrescrLabDiag_id"],
			"UslugaComplex_id" => $data["UslugaComplex_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение данных для формы редактирования
	 * @param array $data
	 * @return array|bool
	 */
	public function doLoad($data)
	{
		// если создана заявка и она не в статусе новая, то только просмотр назначения
		$query = "
			select
				case when EP.PrescriptionStatusType_id = 1 and LR.EvnStatus_id = 1
					then 'edit' else 'view'
				end as \"accessType\",
				EP.EvnPrescrLabDiag_id as \"EvnPrescrLabDiag_id\",
				EP.EvnPrescrLabDiag_pid as \"EvnPrescrLabDiag_pid\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				EP.UslugaComplex_id as \"UslugaComplex_id\",
				UCC.UslugaComplex_id as \"UslugaComplex_sid\",
				to_char(EP.EvnPrescrLabDiag_setDT, '{$this->dateTimeForm104}') as \"EvnPrescrLabDiag_setDate\",
				case when coalesce(EP.EvnPrescrLabDiag_IsCito, 1) = 1 then 'off' else 'on' end as \"EvnPrescrLabDiag_IsCito\",
				EP.EvnPrescrLabDiag_Descr as \"EvnPrescrLabDiag_Descr\",
				EP.Person_id as \"Person_id\",
				EP.PersonEvn_id as \"PersonEvn_id\",
				EP.Server_id as \"Server_id\",
				ED.MedService_id as \"MedService_id\",
				EP.StudyTarget_id as \"StudyTarget_id\"
			from 
				v_EvnPrescrLabDiag EP
				left join v_EvnPrescrLabDiagUsluga UCC on UCC.EvnPrescrLabDiag_id = EP.EvnPrescrLabDiag_id
				left join lateral (
					select
						coalesce(ED.EvnDirection_id, epd.EvnDirection_id) as EvnDirection_id,
						ED.MedService_id
					from
						v_EvnPrescrDirection epd
						inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
							and ED.EvnDirection_failDT is null
							and coalesce(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescrLabDiag_id
					order by epd.EvnPrescrDirection_insDT desc
					limit 1
				) as ED on true
				left join v_EvnLabRequest LR on LR.EvnDirection_id = ED.EvnDirection_id
			where EP.EvnPrescrLabDiag_id = :EvnPrescrLabDiag_id
		";
		$queryParams = ["EvnPrescrLabDiag_id" => $data["EvnPrescrLabDiag_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$tmp_arr = $result->result("array");
		if (count($tmp_arr) == 0) {
			return $tmp_arr;
		}
		$response = [];
		$uslugalist = [];
		foreach ($tmp_arr as $row) {
			if (!empty($row["UslugaComplex_sid"])) {
				$uslugalist[] = $row["UslugaComplex_sid"];
			}
		}
		$response[0] = $tmp_arr[0];
		$response[0]["EvnPrescrLabDiag_uslugaList"] = implode(",", $uslugalist);
		return $response;
	}

	/***
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 * @param $section
	 * @param $evn_pid
	 * @param $sessionParams
	 * @param array $excepts
	 * @return array|bool
	 */
	public function doLoadViewData($section, $evn_pid, $sessionParams, $excepts = [])
	{
		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = "";
		$filter = "";
		$except_ids = [];
		$testFilter = getAccessRightsTestFilter("UC.UslugaComplex_id");
		$filterAccessRightsDenied = getAccessRightsTestFilter("UCMPp.UslugaComplex_id", false, true);
		foreach ($excepts as $item) {
			if (!empty($item["EvnPrescr_id"])) {
				$except_ids[] = $item["EvnPrescr_id"];
			}
		}
		if (count($except_ids) > 0) {
			$except_ids = implode(",", $except_ids);
			$filter .= " and EP.EvnPrescr_id not in ({$except_ids})";
		}
		$ucpCondition = " and UCp.UslugaComplex_id is null ";
		if (!$sysnick) {
			$ucpCondition = "";
		}
		if (!empty($testFilter)) {
			$filter .= "
				and (
					ED.MedPersonal_id = :MedPersonal_id
					or exists (
						select Evn_id
						from v_Evn
						where Evn_id = :EvnPrescr_pid
						  and EvnClass_sysNick = 'EvnSection'
						  and Evn_setDT <= EP.EvnPrescr_setDT
						  and (Evn_disDT is null or Evn_disDT >= EP.EvnPrescr_setDT)
						limit 1
					) or ($testFilter {$ucpCondition})
				)
			";
		}
		if ($sysnick) {
			$accessType = "
				case when {$sysnick}.Lpu_id = :Lpu_id
					and coalesce({$sysnick}.{$sysnick}_IsSigned, 1) = 1
					and LR.EvnStatus_id = 1
					and coalesce(EP.EvnPrescr_IsExec, 1) = 1
				then 'edit' else 'view'
				end as \"accessType\"
			";
			$addJoin = "
				left join v_{$sysnick} {$sysnick} on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid
				left join lateral (
					select UCMPp.UslugaComplex_id
					from
						v_UslugaComplexMedService UCMPp
						inner join v_EvnLabRequestUslugaComplex ELRUC on UCMPp.UslugaComplex_id = ELRUC.UslugaComplex_id and ELRUC.EvnLabRequest_id = LR.EvnLabRequest_id
						inner join v_EvnLabSample ELS on ELS.EvnLabSample_id = ELRUC.EvnLabSample_id and ELS.LabSampleStatus_id in (4,6)
					where
						UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
						" . ((!empty($filterAccessRightsDenied)) ? "and " . $filterAccessRightsDenied : '') . "
					limit 1
				) as UCp on true
			";
		} else {
			$accessType = "
				'view' as \"accessType\"
			";
		}
		$UslugaComplex_Code = "UC.UslugaComplex_Code as \"UslugaComplex_Code\"";
		$UslugaComplex_Name = "coalesce(ucms.UslugaComplex_Name, UC.UslugaComplex_Name) as \"UslugaComplex_Name\"";

		if (!empty($this->options["prescription"]["enable_grouping_by_gost2011"]) || $this->options["prescription"]["service_name_show_type"] == 2) {
			$UslugaComplex_Code = "UC11.UslugaComplex_Code as \"UslugaComplex_Code\"";
			$UslugaComplex_Name = "UC11.UslugaComplex_Name as \"UslugaComplex_Name\"";
		}
		$query = "
			select
				{$accessType},
				EP.EvnPrescr_id as \"EvnPrescr_id\",
			    'EvnPrescrLabDiag' as \"object\",
			    EP.EvnPrescr_pid as \"EvnPrescr_pid\",
			    EP.EvnPrescr_rid as \"EvnPrescr_rid\",
			    to_char(EP.EvnPrescr_setDT, '{$this->dateTimeForm104}') as \"EvnPrescr_setDate\",
			    null as \"EvnPrescr_setTime\",
			    coalesce(EP.EvnPrescr_IsExec, 1) as \"EvnPrescr_IsExec\",
			    case when EU.EvnUsluga_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\",
			    case when 2 = EP.EvnPrescr_IsExec
					then to_char(EP.EvnPrescr_updDT, '{$this->dateTimeForm104}')||' '||to_char(EP.EvnPrescr_updDT, '{$this->dateTimeForm108}') else null
				end as \"EvnPrescr_execDT\",
			    EP.PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
			    EP.PrescriptionType_id as \"PrescriptionType_id\",
			    EP.PrescriptionType_id as \"PrescriptionType_Code\",
			    coalesce(EP.EvnPrescr_IsCito, 1) as \"EvnPrescr_IsCito\",
			    coalesce(EP.EvnPrescr_Descr, '') as \"EvnPrescr_Descr\",
			    case when ED.EvnDirection_id is null or coalesce(ED.EvnStatus_id, 16) in (12,13) then 1 else 2 end as \"EvnPrescr_IsDir\",
			    case when ED.EvnStatus_id is null and (ED.DirFailType_id > 0 or EQ.QueueFailCause_id > 0) then 12 else ED.EvnStatus_id end as \"EvnStatus_id\",
			    case when EvnStatus.EvnStatus_Name is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0) then 'Отменено' else EvnStatus.EvnStatus_Name end as \"EvnStatus_Name\",
			    coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name) as \"EvnStatusCause_Name\",
			    to_char(coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT), '{$this->dateTimeForm104}') as \"EvnDirection_statusDate\",
			    ESH.EvnStatusCause_id as \"EvnStatusCause_id\",
			    ED.DirFailType_id as \"DirFailType_id\",
			    EQ.QueueFailCause_id as \"QueueFailCause_id\",
			    ESH.EvnStatusHistory_Cause as \"EvnStatusHistory_Cause\",
			    ED.EvnDirection_id as \"EvnDirection_id\",
			    EQ.EvnQueue_id as \"EvnQueue_id\",
			    case when ED.EvnDirection_Num is null then '' else ED.EvnDirection_Num::varchar end as \"EvnDirection_Num\",
				case
					when TTMS.TimetableMedService_id is not null then coalesce(MS.MedService_Name, '')||' / '||coalesce(Lpu.Lpu_Nick, '')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
								then coalesce(MS.MedService_Name, '')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
								then coalesce(MS.MedService_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
								then coalesce(MS.MedService_Name, '')||' / '||coalesce(LSPD.LpuSectionProfile_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
							else coalesce(LSPD.LpuSectionProfile_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
						end||' / '||coalesce(Lpu.Lpu_Nick, '')
				else '' end as \"RecTo\",
				case
					when TTMS.TimetableMedService_id is not null then coalesce(to_char(TTMS.TimetableMedService_begTime::date, '{$this->dateTimeForm104}'), '')||' '||coalesce(to_char(TTMS.TimetableMedService_begTime::date, '{$this->dateTimeForm108}'), '')
					when EQ.EvnQueue_id is not null then 'В очереди с '||coalesce(to_char(EQ.EvnQueue_setDate::date, '{$this->dateTimeForm104}'), '')
				else '' end as \"RecDate\",
				case
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
				else '' end as \"timetable\",
			    coalesce(CAST(TTMS.TimetableMedService_id as text), CAST(EQ.EvnQueue_id as text), '') as \"timetable_id\",
				EP.EvnPrescr_pid as \"timetable_pid\",
				LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				DT.DirType_Code as \"DirType_Code\",
				EP.MedService_id as \"MedService_id\",
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_2011id as \"UslugaComplex_2011id\",
				UCMS.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				case when exists(
					select ucms2.UslugaComplexMedService_id
					from
				    	v_UslugaComplexMedService ucms2
						inner join lis.v_AnalyzerTest at2 on at2.UslugaComplexMedService_id = ucms2.UslugaComplexMedService_id
						inner join lis.v_Analyzer a2 on a2.Analyzer_id = at2.Analyzer_id
					where ucms2.UslugaComplexMedService_pid = UCMS.UslugaComplexMedService_id
					  and coalesce(at2.AnalyzerTest_IsNotActive, 1) = 1
				      and coalesce(a2.Analyzer_IsNotActive, 1) = 1
				) then 1 else 0 end as \"isComposite\",
				EP.EvnPrescr_CountComposit as \"EvnPrescr_CountComposit\",
				composition.cnt as \"EvnPrescr_MaxCountComposit\",
				{$UslugaComplex_Code},
				{$UslugaComplex_Name},
				null as \"TableUsluga_id\",
				case 
					when Lpu.Lpu_id is not null and Lpu.Lpu_id <> LpuSession.Lpu_id then 2 else 1
				end as \"otherMO\",
				EvnStatus.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EP.StudyTarget_id as \"StudyTarget_id\",
				EPLD.UslugaComplexMedService_id as \"UslugaComplexMedService_pid\",
				EPLD.MedService_pzmid as \"MedService_pzmid\"
			from
				v_EvnPrescr EP
				inner join EvnPrescrLabDiag EPLD on EPLD.evn_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EPLD.UslugaComplex_id
				left join v_UslugaComplex UC11 on UC11.UslugaComplex_id = UC.UslugaComplex_2011id
				left join lateral (
					select
				    	ED.EvnDirection_id,
				    	coalesce(ED.Lpu_sid, ED.Lpu_id) Lpu_id,
						ED.EvnQueue_id,
						ED.EvnDirection_Num,
						ED.EvnDirection_IsAuto,
				    	ED.LpuSection_did,
				    	ED.LpuUnit_did,
						ED.Lpu_did,
						ED.MedService_id,
				    	ED.LpuSectionProfile_id,
				    	ED.DirType_id,
						ED.EvnStatus_id,
						ED.EvnDirection_statusDate,
				    	ED.DirFailType_id,
				    	ED.EvnDirection_failDT,
				    	ED.MedPersonal_id
					from
				    	v_EvnPrescrDirection epd
						inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by 
						case when coalesce(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end,
				    	epd.EvnPrescrDirection_insDT desc
				    limit 1
				) as ED on true
				left join lateral (
					select
				    	TimetableMedService_id,
				    	TimetableMedService_begTime
					from v_TimetableMedService_lite TTMS
				    where TTMS.EvnDirection_id = ED.EvnDirection_id
				    limit 1
				) as TTMS on true
				left join lateral (
					select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ
					where EQ.EvnDirection_id = ED.EvnDirection_id
					  and EQ.EvnQueue_recDT is null
					union
					select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ
					where EQ.EvnQueue_id = ED.EvnQueue_id
					  and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					  and EQ.EvnQueue_failDT is null
				    limit 1
				) as EQ on true
				left join lateral (
					select ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
					from EvnStatusHistory ESH
					where ESH.Evn_id = ED.EvnDirection_id
					  and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
				    limit 1
				) as ESH on true
				left join EvnStatus on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				left join EvnStatusCause on EvnStatusCause.EvnStatusCause_id = ESH.EvnStatusCause_id
				left join v_DirFailType DFT on DFT.DirFailType_id = ED.DirFailType_id
				left join v_QueueFailCause QFC on QFC.QueueFailCause_id = EQ.QueueFailCause_id
				left join v_MedService MS on MS.MedService_id = ED.MedService_id
				left join v_LpuSection LS on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				left join lateral (
					select EUP.EvnUslugaPar_id
					from v_EvnUslugaPar EUP
					where EUP.EvnDirection_id = ED.EvnDirection_id
					  and EUP.EvnPrescr_id = EP.EvnPrescr_id
				    limit 1
				) as EUP on true 
				left join v_LpuUnit LU on coalesce(ED.LpuUnit_did, EQ.LpuUnit_did, MS.LpuUnit_id) = LU.LpuUnit_id
				left join v_LpuSectionProfile LSPD on coalesce(ED.LpuSectionProfile_id, EQ.LpuSectionProfile_did, LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
				left join v_Lpu LpuSession on LpuSession.Lpu_id = :Lpu_id
				left join lateral (
					select EvnUsluga_id, EvnUsluga_setDT
				    from v_EvnUsluga
					where EP.EvnPrescr_IsExec = 2
				      and UC.UslugaComplex_id is not null
				      and EvnPrescr_id = EP.EvnPrescr_id
				    limit 1
				) as EU on true
				left join v_EvnLabRequest LR on LR.EvnDirection_id = ED.EvnDirection_id
				left join v_UslugaComplexMedService UCMS on UCMS.MedService_id = LR.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				left join lateral (
					select COUNT(ucoa.UslugaComplex_id) as cnt
					from v_UslugaComplexMedService ucmsoa
						inner join v_UslugaComplex ucoa on ucmsoa.UslugaComplex_id = ucoa.UslugaComplex_id
						inner join lis.v_AnalyzerTest lat on lat.UslugaComplexMedService_id = ucmsoa.UslugaComplexMedService_id
						inner join v_UslugaComplex ucato on ucato.UslugaComplex_id = lat.UslugaComplex_id
					where ucmsoa.UslugaComplexMedService_pid = case when UCMS.UslugaComplexMedService_id is null then EPLD.UslugaComplexMedService_id else UCMS.UslugaComplexMedService_id end
						and coalesce(lat.AnalyzerTest_endDT, dbo.tzGetDate()) >= dbo.tzGetDate()
						and coalesce(ucato.UslugaComplex_endDT, dbo.tzGetDate()) >= dbo.tzGetDate()
				) composition on true
				{$addJoin}
			where EP.EvnPrescr_pid  = :EvnPrescr_pid
			  and EP.PrescriptionType_id = 11
			  and EP.PrescriptionStatusType_id != 3
			  {$filter}
			order by
				EP.EvnPrescr_id,
				EP.EvnPrescr_setDT
		";
		$queryParams = [
			"EvnPrescr_pid" => $evn_pid,
			"Lpu_id" => $sessionParams["lpu_id"],
			"MedPersonal_id" => $sessionParams["medpersonal_id"],
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$tmp_arr = $result->result("array");
		$response = [];
		foreach ($tmp_arr as $i => $row) {
			$row["UslugaId_List"] = $row["UslugaComplex_id"];
			if ($this->options["prescription"]["enable_show_service_code"]) {
				$row["Usluga_List"] = $row["UslugaComplex_Code"] . " " . $row["UslugaComplex_Name"];
			} else {
				$row["Usluga_List"] = $row["UslugaComplex_Name"];
			}
			$row[$section . "_id"] = $row["EvnPrescr_id"] . "-0";
			$response[] = $row;
		}
		//загружаем документы
		$tmp_arr = [];
		$evnPrescrIdList = [];
		foreach ($response as $key => $row) {
			if (isset($row["EvnPrescr_IsExec"]) &&
				$row["EvnPrescr_IsExec"] === 2 &&
				isset($row["EvnPrescr_IsHasEvn"]) &&
				$row["EvnPrescr_IsHasEvn"] === 2
			) {
				$response[$key]["EvnXml_id"] = null;
				$id = $row["EvnPrescr_id"];
				$evnPrescrIdList[] = $id;
				$tmp_arr[$id] = $key;
			}
		}
		if (count($evnPrescrIdList) > 0) {
			$evnPrescrIdList = implode(",", $evnPrescrIdList);
			$query = "
				WITH EvnPrescrEvnXml as (
					select
						doc.EvnXml_id,
						EU.EvnPrescr_id
					from
						v_EvnUsluga EU
						inner join v_EvnXml doc on doc.Evn_id = EU.EvnUsluga_id
					where EU.EvnPrescr_id in ({$evnPrescrIdList})
				)
				select
					EvnXml_id as \"EvnXml_id\",
					EvnPrescr_id as \"EvnPrescr_id\"
				from EvnPrescrEvnXml
				order by EvnPrescr_id
			";
			$result = $this->db->query($query);
			if (is_object($result)) {
				$evnPrescrIdList = $result->result("array");
				foreach ($evnPrescrIdList as $row) {
					$id = $row["EvnPrescr_id"];
					if (isset($tmp_arr[$id])) {
						$key = $tmp_arr[$id];
						if (isset($response[$key])) {
							$response[$key]["EvnXml_id"] = $row["EvnXml_id"];
						}
					}
				}
			}
		}
		foreach ($response as $key => $row) {
			$response[$key]["EvnLabSampleDefect"] = $this->getEvnLabSamplesDefect(["EvnPrescr_id" => $row["EvnPrescr_id"]]);
		}
		return $response;
	}

	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 * @param $section
	 * @param $evn_pid
	 * @param $sessionParams
	 * @return array|bool
	 * @throws Exception
	 */
	public function doLoadViewDataPostgres($section, $evn_pid, $sessionParams)
	{
		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = "";
		$accessType = "
			'view' as \"accessType\"
		";
		if ($sysnick) {
			$accessType = "
				case when {$sysnick}.Lpu_id = :Lpu_id
					and coalesce({$sysnick}.{$sysnick}_IsSigned, 1) = 1
					and coalesce(EP.EvnPrescr_IsExec, 1) = 1
				then 'edit' else 'view' end as \"accessType\"
			";
			$addJoin = "
				left join v_{$sysnick} {$sysnick} on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid
			";
		}
		$UslugaComplex_Code = "UC.UslugaComplex_Code as \"UslugaComplex_Code\"";
		$UslugaComplex_Name = "coalesce(UCMS.UslugaComplex_Name, UC.UslugaComplex_Name) as \"UslugaComplex_Name\"";

		if (!empty($this->options["prescription"]["enable_grouping_by_gost2011"]) || $this->options["prescription"]["service_name_show_type"] == 2) {
			$UslugaComplex_Code = "UC11.UslugaComplex_Code as \"UslugaComplex_Code\"";
			$UslugaComplex_Name = "UC11.UslugaComplex_Name as \"UslugaComplex_Name\"";
		}
		$query = "
			select
				{$accessType},
				EP.EvnPrescr_id as \"EvnPrescr_id\",
				'EvnPrescrLabDiag' as \"object\",
				EP.EvnPrescr_pid as \"EvnPrescr_pid\",
				EP.EvnPrescr_rid as \"EvnPrescr_rid\",
				to_char(EP.EvnPrescr_setDT, '{$this->dateTimeForm104}') as \"EvnPrescr_setDate\",
				null as \"EvnPrescr_setTime\",
				coalesce(EP.EvnPrescr_IsExec, 1) as \"EvnPrescr_IsExec\",
				case when 2 = EP.EvnPrescr_IsExec
					then to_char(EP.EvnPrescr_updDT, '{$this->dateTimeForm104}')||' '||to_char(EP.EvnPrescr_updDT, '{$this->dateTimeForm108}') else null
				end as \"EvnPrescr_execDT\",
				EP.EvnPrescr_setDT as \"EvnPrescr_setDT\",
				EP.PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
				EP.PrescriptionType_id as \"PrescriptionType_id\",
				EP.PrescriptionType_id as \"PrescriptionType_Code\",
				coalesce(EP.EvnPrescr_IsCito, 1) as \"EvnPrescr_IsCito\",
				coalesce(EP.EvnPrescr_Descr, '') as \"EvnPrescr_Descr\",
				1 as \"EvnPrescr_IsDir\",
				'Отсутствует направление' as \"Usluga_List\",
				EP.EvnPrescr_pid as \"timetable_pid\",
				EP.MedService_id as \"MedService_id\",
				EP.EvnPrescr_CountComposit as \"EvnPrescr_CountComposit\",
				composition.cnt as \"EvnPrescr_MaxCountComposit\",
				null as \"TableUsluga_id\",
				epd.EvnDirection_id as \"EvnDirection_id\",
				EPLD.Lpu_id as \"Lpu_id\",
				CASE 
					when EPLD.Lpu_id is not null and EPLD.Lpu_id <> LpuSession.Lpu_id then 2 else 1
				end as \"otherMO\",
				:MedPersonal_id as \"MedPersonal_id\",
				EPLD.UslugaComplex_id as \"UslugaComplex_id\",
				{$UslugaComplex_Code},
				{$UslugaComplex_Name},
				ttms.TimetableMedService_id as \"TimetableMedService_id\",
				to_char(ttms.TimetableMedService_begTime, 'dd.mm.yyyy hh24:mi:ss') as \"TimetableMedService_begTime\",
				EPLD.UslugaComplexMedService_id as \"UslugaComplexMedService_pid\",
				EPLD.MedService_pzmid as \"MedService_pzmid\"
			from
				v_EvnPrescr EP
				inner join v_EvnPrescrLabDiag EPLD on EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join v_EvnPrescrDirection epd on ep.EvnPrescr_id = epd.EvnPrescr_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EPLD.UslugaComplex_id
				left join v_UslugaComplex UC11 on UC11.UslugaComplex_id = UC.UslugaComplex_2011id
				left join v_Lpu LpuSession on LpuSession.Lpu_id = :Lpu_id
				left join lateral (
					Select
						ED.EvnDirection_id,
						ED.MedService_id
					from v_EvnPrescrDirection epd
					inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by 
						case when coalesce(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT desc
					limit 1
				) ED on true
				left join v_UslugaComplexMedService UCMS on UCMS.MedService_id = ED.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				left join lateral (
					select
						TimetableMedService_id,
						TimetableMedService_begTime
					from v_TimetableMedService_lite TTMS
					where TTMS.EvnDirection_id = epd.EvnDirection_id
					limit 1
			  	) as ttms on true
			  	left join lateral (
					select COUNT(ucoa.UslugaComplex_id) as cnt
					from v_UslugaComplexMedService ucmsoa
					inner join v_UslugaComplex ucoa on ucmsoa.UslugaComplex_id = ucoa.UslugaComplex_id
					inner join lis.v_AnalyzerTest lat on lat.UslugaComplexMedService_id = ucmsoa.UslugaComplexMedService_id
					where ucmsoa.UslugaComplexMedService_pid = case when UCMS.UslugaComplexMedService_id is null then EPLD.UslugaComplexMedService_id else UCMS.UslugaComplexMedService_id end
				) composition on true
				{$addJoin}
			where EP.EvnPrescr_pid  = :EvnPrescr_pid
			  and EP.PrescriptionType_id = 11
			  and EP.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescr_id,
				EP.EvnPrescr_setDT		
		";
		$queryParams = [
			"EvnPrescr_pid" => $evn_pid,
			"Lpu_id" => $sessionParams["lpu_id"],
			"MedPersonal_id" => $sessionParams["medpersonal_id"],
		];

		$tmp_arr = $this->queryResult($query, $queryParams);
		if (!is_array($tmp_arr)) {
			return false;
		}

		$EvnPrescrList = [];
		$listByDirection = [];
		foreach ($tmp_arr as $idx => $item) {
			if (!empty($item["EvnPrescr_id"])) {
				$EvnPrescrList[] = $item;
				$listByDirection[$item["EvnPrescr_id"]] = $item;
			} else {
				$listByDirection[-$idx] = $item;
			}
		}
		if (count($EvnPrescrList) > 0) {
			$resp_lis = null;
			$resp = [];
			if ($this->usePostgreLis) {
				$resp_lis = $this->lis->POST("EvnDirection/loadView", [
					"EvnPrescrList" => $EvnPrescrList,
					"sysnick" => $sysnick
				], "list");
				if (!$this->isSuccessful($resp_lis)) {
					return $resp_lis;
				}
			}
			if (is_array($resp_lis)) {
				$this->load->model("EvnDirection_model");
				$resp = $this->EvnDirection_model->doLoadView([
					"EvnPrescrList" => $EvnPrescrList,
					"sysnick" => $sysnick,
					"excepts" => $resp_lis
				]);
				if (!is_array($resp)) {
					throw new Exception("Ошибка при получении данных направлений");
				}
				$resp = array_merge($resp, $resp_lis);
			}
			foreach ($resp as $item) {
				$key = $item["EvnPrescr_id"];
				if (!isset($listByDirection[$key])) {
					continue;
				}
				if ($item["EvnLabRequestStatus"] != 1) {
					$item["accessType"] = "view";
				}
				if ($this->options["prescription"]["enable_show_service_code"]) {
					$item["Usluga_List"] = $item["UslugaComplex_Code"] . " " . $item["UslugaComplex_Name"];
				} else {
					$item["Usluga_List"] = $item["UslugaComplex_Name"];
				}
				$listByDirection[$key] = array_merge($listByDirection[$key], $item);
			}
		}
		$tmp_arr = [];
		$evnPrescrIdList = [];
		foreach ($listByDirection as $key => &$item) {
			unset($item["EvnPrescr_setDT"]);
			unset($item["TimeTableMedService_id"]);
			unset($item["TimetableMedService_begTime"]);
			$item[$section . "_id"] = $item["EvnPrescr_id"] . "-0";
			if ($item["EvnPrescr_IsExec"] == 2 && $item["EvnPrescr_IsHasEvn"] == 2) {
				$item["EvnXml_id"] = null;
				if (!empty($item["EvnPrescr_id"])) {
					$id = $item["EvnPrescr_id"];
					$evnPrescrIdList[] = $id;
					$tmp_arr[$id] = $key;
				}
			}
		}
		unset($item);
		//загружаем документы
		if (count($evnPrescrIdList) > 0) {
			$evnPrescrIdList = implode(",", $evnPrescrIdList);
			$query = "
				with EvnPrescrEvnXml as (
					select
						doc.EvnXml_id,
						EU.EvnPrescr_id
					from
						v_EvnUsluga EU
						inner join v_EvnXml doc on doc.Evn_id = EU.EvnUsluga_id
					where EU.EvnPrescr_id in ({$evnPrescrIdList})
				)
				select
					EvnXml_id as \"EvnXml_id\",
					EvnPrescr_id as \"EvnPrescr_id\"
				from EvnPrescrEvnXml
				order by EvnPrescr_id
			";
			$evnPrescrIdList = $this->queryResult($query);
			if (!is_array($evnPrescrIdList)) {
				return false;
			}
			if (!empty($evnPrescrIdList)) {
				foreach ($evnPrescrIdList as $item) {
					$id = $item["EvnPrescr_id"];
					if (isset($tmp_arr[$id])) {
						$key = $tmp_arr[$id];
						if (isset($listByDirection[$key])) {
							$listByDirection[$key]["EvnXml_id"] = $item["EvnXml_id"];
						}
					}
				}
			} else {
				foreach ($listByDirection as $key => $value) {
					if (!empty($value["EvnUslugaPar_id"])) {
						$query = "
							select EvnXml_id
							from v_EvnXml
							where Evn_id = :EvnUslugaPar_id
							limit 1
						";
						$evnXml_id = $this->getFirstResultFromQuery($query, $value);
						if (!empty($evnXml_id))
							$listByDirection[$key]["EvnXml_id"] = $evnXml_id;
					}
				}
			}
		}
		return array_values($listByDirection);
	}

	/**
	 * Возвращает бракованные пробы по номеру назначения
	 * @param $data
	 * @return array|bool
	 */
	function getEvnLabSamplesDefect($data)
	{
		if (!isset($data["EvnPrescr_id"]) || empty($data["EvnPrescr_id"])) {
			return false;
		}
		$query = "
			select 
				ELS.EvnLabSample_id as \"EvnLabSample_id\",
				DCT.DefectCauseType_Name as \"DefectCauseType_Name\"
			from
				v_EvnPrescrLabDiag EPLD
				inner join v_EvnPrescrDirection EPD on EPD.EvnPrescr_id = EPLD.EvnPrescrLabDiag_id
				inner join v_EvnLabRequest ELR on ELR.EvnDirection_id = EPD.EvnDirection_id
				inner join v_EvnLabSample ELS on ELS.EvnLabRequest_id = ELR.EvnLabRequest_id
				inner join lis.v_DefectCauseType DCT on DCT.DefectCauseType_id = ELS.DefectCauseType_id
			where EPLD.EvnPrescrLabDiag_id = :EvnPrescr_id
			  and ELS.LabSampleStatus_id = 5
		";
		$queryParams = ["EvnPrescr_id" => $data["EvnPrescr_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array|false
	 */
	function getEvnPrescrLabDiagDescr($data)
	{
		$query = "
			select epr.EvnPrescrLabDiag_Descr as \"EvnPrescrLabDiag_Descr\"
			from
				v_EvnUslugaPar eup
				left join v_EvnPrescrLabDiag epr on epr.EvnPrescrLabDiag_id = eup.EvnPrescr_id
			where eup.EvnDirection_id = :EvnDirection_id
		";
		$res = $this->queryResult($query, $data);
		return $res;
	}
}