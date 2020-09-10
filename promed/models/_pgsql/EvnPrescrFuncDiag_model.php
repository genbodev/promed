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
 * Модель назначения "Функциональная диагностика"
 *
 * Назначения с типом "Функциональная диагностика" хранятся в таблицах EvnPrescrFuncDiag, EvnPrescrFuncDiagUsluga.
 * В назначении должна быть указана одна услуга или более.
 * Для каждой выбранной услуги создается запись в таблице EvnPrescrFuncDiagUsluga
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 * @property CI_DB_driver $db
 * @property EvnUsluga_model $eumodel
 */
class EvnPrescrFuncDiag_model extends EvnPrescrAbstract_model
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId()
	{
		return 12;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName()
	{
		return "EvnPrescrFuncDiag";
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
					["field" => "parentEvnClass_SysNick", "label" => "Системное имя род.события", "rules" => "", "type" => "string"],
					["field" => "signature", "label" => "Признак для подписания", "rules" => "", "type" => "int"],
					["field" => "EvnPrescrFuncDiag_id", "label" => "Идентификатор назначения", "rules" => "", "type" => "id"],
					["field" => "EvnPrescrFuncDiag_pid", "label" => "Идентификатор род.события", "rules" => "required", "type" => "id"],
					["field" => "MedService_id", "label" => "Служба", "rules" => "", "type" => "id"],
					["field" => "EvnPrescrFuncDiag_uslugaList", "label" => "Выбранные услуги", "rules" => "required", "type" => "string"],
					["field" => "EvnPrescrFuncDiag_setDate", "label" => "Плановая дата", "rules" => "", "type" => "date"],
					["field" => "EvnPrescrFuncDiag_IsCito", "label" => "Cito", "rules" => "", "type" => "string"],
					["field" => "EvnPrescrFuncDiag_Descr", "label" => "Комментарий", "rules" => "trim", "type" => "string"],
					["field" => "PersonEvn_id", "label" => "Идентификатор", "rules" => "required", "type" => "id"],
					["field" => "Server_id", "label" => "Идентификатор сервера", "rules" => "required", "type" => "int"],
					["field" => "StudyTarget_id", "label" => "Цель исследования", "rules" => "required", "type" => "id"],
					["field" => "FSIDI_id", "label" => "Инструментальная диагностика", "rules" => "", "type" => "id"],
					["field" => "Resource_id", "label" => "Тип ресурса", "rules" => "", "type" => "string"],
					["field" => "StudyTargetPayloadData", "label" => "Доп. параметры исследования", "rules" => "", "type" => "string"]
				];
				break;
			case "doLoad":
				$rules[] = [
					"field" => "EvnPrescrFuncDiag_id",
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
        $data["PrescriptionStatusType_id"] = null;
        if (empty($data["EvnPrescrFuncDiag_id"])) {
			$action = "ins";
			$data["EvnPrescrFuncDiag_id"] = null;
			$data["PrescriptionStatusType_id"] = 1;
		} else {
			$action = "upd";
			try {
				$o_data = $this->getAllData($data["EvnPrescrFuncDiag_id"]);
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
			foreach ($o_data as $k => $v) {
				if (!array_key_exists($k, $data)) {
					$data[$k] = $v;
				}
			}
			if (!empty($o_data['prescriptionstatustype_id'])) {
				$data['PrescriptionStatusType_id'] = $o_data['prescriptionstatustype_id'];
			}
		}
		$selectString = "
			evnprescrfuncdiag_id as \"EvnPrescrFuncDiag_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from p_evnprescrfuncdiag_{$action}(
			    evnprescrfuncdiag_id := :EvnPrescrFuncDiag_id,
			    evnprescrfuncdiag_pid := :EvnPrescrFuncDiag_pid,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnprescrfuncdiag_setdt := :EvnPrescrFuncDiag_setDT,
			    prescriptiontype_id := :PrescriptionType_id,
			    evnprescrfuncdiag_iscito := :EvnPrescrFuncDiag_IsCito,
			    prescriptionstatustype_id := :PrescriptionStatusType_id,
			    evnprescrfuncdiag_descr := :EvnPrescrFuncDiag_Descr,
			    studytarget_id := :StudyTarget_id,
			    resource_id := :Resource_id,
			    medservice_id := :MedService_id,
			    pmuser_id := :pmUser_id
			);
		";
		$data["EvnPrescrFuncDiag_setDT"] = null;
		if (!empty($data["EvnPrescrFuncDiag_setDate"])) {
			$data["EvnPrescrFuncDiag_setDT"] = $data["EvnPrescrFuncDiag_setDate"];
		}
		$data["EvnPrescrFuncDiag_IsCito"] = (empty($data["EvnPrescrFuncDiag_IsCito"]) || $data["EvnPrescrFuncDiag_IsCito"] != "on") ? 1 : 2;
		$data["PrescriptionType_id"] = $this->getPrescriptionTypeId();
		if (!isset($data["DopDispInfoConsent_id"])) {
			$data["DopDispInfoConsent_id"] = null;
		}
		if (!isset($data["Resource_id"])) {
			$data["Resource_id"] = null;
		}
		
		$db_query = $this->db->query($query, $data);

		if (!is_object($db_query)) {
			return false;
		}
		$result = $db_query->result("array");
		if (empty($result) || empty($result[0]) || empty($result[0]["EvnPrescrFuncDiag_id"]) || !empty($result[0]["Error_Msg"])) {
			return false;
		}
		// если выполнено сохранение формы "Назначение ФД: редактирование", сохраняем\обновляем зубы
		if ($action == "upd" && !empty($data["parentEvnClass_SysNick"]) && $data["parentEvnClass_SysNick"] == "EvnVizitPLStom") {
			$this->load->model("EvnUsluga_model", "eumodel");
			$this->eumodel->saveStudyTargetPayloadData([
				"pmUser_id" => $data["pmUser_id"],
				"EvnPrescrFuncDiag_id" => $data["EvnPrescrFuncDiag_id"],
				"StudyTargetPayloadData" => (!empty($data["StudyTargetPayloadData"]) ? json_decode(toUTF($data["StudyTargetPayloadData"]), true) : null)
			]);
		}
		$uslugalist = [];
		if (!empty($data["EvnPrescrFuncDiag_uslugaList"])) {
			$uslugalist = explode(",", $data["EvnPrescrFuncDiag_uslugaList"]);
			if (empty($uslugalist) || !is_numeric($uslugalist[0])) {
				$result[0]["Error_Msg"] = "Ошибка формата списка услуг";
			} else {
				$res = $this->clearEvnPrescrFuncDiagUsluga(
					["EvnPrescrFuncDiag_id" => $result[0]["EvnPrescrFuncDiag_id"]]
				);
				if (empty($res)) {
					$result[0]["Error_Msg"] = "Ошибка запроса списка выбранных услуг";
				}
				if (!empty($res) && !empty($res[0]) && !empty($res[0]["Error_Msg"])) {
					$result[0]["Error_Msg"] = $res[0]["Error_Msg"];
				}
			}
		}
		if (empty($result[0]["Error_Msg"]) && !empty($uslugalist)) {
			foreach ($uslugalist as $d) {
				$res = $this->saveEvnPrescrFuncDiagUsluga([
					"UslugaComplex_id" => $d,
					"EvnPrescrFuncDiag_id" => $result[0]["EvnPrescrFuncDiag_id"],
					"FSIDI_id" => $data["FSIDI_id"] ?? null,
					"pmUser_id" => $data["pmUser_id"]
				]);
				if (empty($res)) {
					$result[0]["Error_Msg"] = "Ошибка запроса при сохранении услуги";
					break;
				}
				if (!empty($res) && !empty($res[0]) && !empty($res[0]["Error_Msg"])) {
					$result[0]["Error_Msg"] = $res[0]["Error_Msg"];
					break;
				}
			}
			if (empty($result[0]["Error_Msg"])) {
				$query = "
					select XmlType.XmlType_id as \"EvnXmlDirType_id\"
					from XmlType
					where XmlType.XmlType_id =
						case
						    when exists (
								select uca.UslugaComplexAttribute_id
								from
									UslugaComplexAttribute uca
									inner join UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
										and ucat.UslugaComplexAttributeType_SysNick like 'kt'
								where uca.UslugaComplex_id = :UslugaComplex_id
							) then 19
							when exists (
								select uca.UslugaComplexAttribute_id
								from
									UslugaComplexAttribute uca
									inner join UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
										and ucat.UslugaComplexAttributeType_SysNick like 'mrt'
								where uca.UslugaComplex_id = :UslugaComplex_id
							) then 18
							else 2
						end
					limit 1
				";
				$queryParams = ["UslugaComplex_id" => $uslugalist[0]];
				$res = $this->getFirstRowFromQuery($query, $queryParams);

				if (!empty($res) && is_array($res)) {
					$result[0]["EvnXmlDir_id"] = null;
					$result[0]["EvnXmlDirType_id"] = $res["EvnXmlDirType_id"];
				}
			}
		}
		return $result;
	}

	/**
	 * Метод очистки списка услуг
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function clearEvnPrescrFuncDiagUsluga($data)
	{
		return $this->clearEvnPrescrTable([
			"object" => "EvnPrescrFuncDiagUsluga",
			"fk_pid" => "EvnPrescrFuncDiag_id",
			"pid" => $data["EvnPrescrFuncDiag_id"]
		]);
	}

	/**
	 * Сохранение назнач
	 * @param $data
	 * @return array|bool
	 */
	function saveEvnPrescrFuncDiagUsluga($data) {

    $code = !empty($data['EvnPrescrFuncDiagUsluga_id']) ? "upd" : "ins";
    $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            EvnPrescrFuncDiagUsluga_id as \"EvnPrescrFuncDiagUsluga_id\"
        from p_EvnPrescrFuncDiagUsluga_{$code}
            (
 				EvnPrescrFuncDiagUsluga_id := :EvnPrescrFuncDiagUsluga_id,
				EvnPrescrFuncDiag_id := :EvnPrescrFuncDiag_id,
				UslugaComplex_id := :UslugaComplex_id,
            	FSIDI_id := :FSIDI_id,
				pmUser_id := :pmUser_id
            )";


		$queryParams = array(
			'EvnPrescrFuncDiagUsluga_id' => (empty($data['EvnPrescrFuncDiagUsluga_id'])? NULL : $data['EvnPrescrFuncDiagUsluga_id'] ),
			'EvnPrescrFuncDiag_id' => $data['EvnPrescrFuncDiag_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'FSIDI_id' => $data['FSIDI_id'] ?? null,
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для формы редактирования
	 * @param array $data
	 * @return array|bool
	 */
	public function doLoad($data) {
		$query = "
			select
				case when EP.PrescriptionStatusType_id = 1 then 'edit' else 'view' end as \"accessType\",
				EP.EvnPrescrFuncDiag_id as \"EvnPrescrFuncDiag_id\",
				EP.EvnPrescrFuncDiag_pid as \"EvnPrescrFuncDiag_pid\",
				EPU.UslugaComplex_id as \"UslugaComplex_id\",
			    EPU.FSIDI_id as \"FSIDI_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				to_char(EP.EvnPrescrFuncDiag_setDT, 'dd.mm.yyyy') as \"EvnPrescrFuncDiag_setDate\",
				case when coalesce(EP.EvnPrescrFuncDiag_IsCito,1) = 1 then 'off' else 'on' end as \"EvnPrescrFuncDiag_IsCito\",
				EP.EvnPrescrFuncDiag_Descr as \"EvnPrescrFuncDiag_Descr\",
				EP.Person_id as \"Person_id\",
				EP.PersonEvn_id as \"PersonEvn_id\",
				EP.Server_id as \"Server_id\",
				coalesce(EP.StudyTarget_id,ED.StudyTarget_id) as \"StudyTarget_id\",
				tth.ToothNums as \"ToothNums\"
			from
				v_EvnPrescrFuncDiag EP
				inner join v_EvnPrescrFuncDiagUsluga EPU  on EP.EvnPrescrFuncDiag_id = EPU.EvnPrescrFuncDiag_id
				LEFT JOIN LATERAL (
					Select ED.EvnDirection_id, ED.StudyTarget_id
					from v_EvnPrescrDirection epd
					inner join v_EvnDirection_all ED  on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.EvnDirection_failDT is null
						and ED.EvnStatus_id not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescrFuncDiag_id
					order by epd.EvnPrescrDirection_insDT desc
                    limit 1
				) ED on true
				LEFT JOIN LATERAL (
					select (
						select
							STRING_AGG (coalesce(CAST(tneu.ToothNumEvnUsluga_ToothNum as VARCHAR),''), ', ')
						from v_EvnUsluga eu
						inner join v_ToothNumEvnUsluga tneu  on tneu.EvnUsluga_id = eu.EvnUsluga_id
						where eu.EvnPrescr_id = EP.EvnPrescrFuncDiag_id
					) as ToothNums
				) tth on true
			where
				EP.EvnPrescrFuncDiag_id = :EvnPrescrFuncDiag_id
		";

		$queryParams = array(
			'EvnPrescrFuncDiag_id' => $data['EvnPrescrFuncDiag_id']
		);

		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$tmp_arr = $result->result('array');
			if(count($tmp_arr) > 0)
			{
				$response = array();
				$uslugalist = array();
			}
			else
			{
				return $tmp_arr;
			}
			foreach($tmp_arr as $row) {
				if(!empty($row['UslugaComplex_id']))
				{
					$uslugalist[] = $row['UslugaComplex_id'];
				}
			}
			$response[0] = $tmp_arr[0];
			$response[0]['EvnPrescrFuncDiag_uslugaList'] = implode(',',$uslugalist);
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 * @param $section
	 * @param $evn_pid
	 * @param $sessionParams
	 * @return array|bool
	 */
	public function doLoadViewData($section, $evn_pid, $sessionParams)
	{
		$query = "
			select EP.EvnPrescr_id as \"EvnPrescr_id\"
			from v_EvnPrescr EP
			where EP.EvnPrescr_pid  = :EvnPrescr_pid
			  and EP.PrescriptionType_id = 12
			  and EP.PrescriptionStatusType_id != 3
		";
		$queryParams = [
			"EvnPrescr_pid" => $evn_pid,
			"Lpu_id" => $sessionParams["lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$list = $result->result("array");
		if (count($list) == 0) {
			return false;
		}
		// преобразуем list в массив для фильтра
		$evId = array();
		foreach ($list as $v) {
			$evId[] = $v["EvnPrescr_id"];
		}
		$list_evId = implode(",", $evId);
		if (count($evId) == 1) {
			$filterEv = "EP.EvnPrescr_id = :EvnPrescr_id";
			$queryParams["EvnPrescr_id"] = $evId[0];
		} else {
			$filterEv = "EP.EvnPrescr_id in (" . $list_evId . ")";
		}

		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = '';
		if ($sysnick) {
			$accessType = "case when {$sysnick}.Lpu_id = :Lpu_id AND coalesce({$sysnick}.{$sysnick}_IsSigned,1) = 1 then 'edit' else 'view' end as accessType";
			$addJoin = "left join v_{$sysnick} {$sysnick}  on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid";
		} else {
			$accessType = "'view' as accessType";
		}
		$addSelect = ' ';
		if (isset($addSelect)) {
			$addSelect .= "
				,EvnXmlDir.EvnXml_id as \"EvnXmlDir_id\"
				,EvnXmlDir.XmlType_id as \"EvnXmlDirType_id\"
			";
			$addJoin .= "
					LEFT JOIN LATERAL (
						select
                        EvnXml.EvnXml_id, XmlType.XmlType_id
						from XmlType
						left join EvnXml  on EvnXml.XmlType_id = XmlType.XmlType_id and EvnXml.Evn_id = ED.EvnDirection_id
						where XmlType.XmlType_id = case
						when exists (
							select  uca.UslugaComplexAttribute_id
							from UslugaComplexAttribute uca
							inner join UslugaComplexAttributeType ucat  on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							and ucat.UslugaComplexAttributeType_SysNick Ilike 'kt'
							where uca.UslugaComplex_id = UC.UslugaComplex_id
                            limit 1
						) then 19
						when exists (
							select uca.UslugaComplexAttribute_id
							from UslugaComplexAttribute uca
							inner join UslugaComplexAttributeType ucat  on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							and ucat.UslugaComplexAttributeType_SysNick Ilike 'mrt'
							where uca.UslugaComplex_id = UC.UslugaComplex_id
                            limit 1
						) then 18
						else 2 end
                        limit 1
					) EvnXmlDir on true";
		}
		$query = "
			select
				{$accessType},
				EP.EvnPrescr_id as \"EvnPrescr_id\",
			    'EvnPrescrFuncDiag' as object,
			    EP.EvnPrescr_pid as \"EvnPrescr_pid\",
			    EP.EvnPrescr_rid as \"EvnPrescr_rid\",
			    to_char(EP.EvnPrescr_setDT, '{$this->dateTimeForm104}') as \"EvnPrescr_setDate\",
			    null as \"EvnPrescr_setTime\",
			    coalesce(EP.EvnPrescr_IsExec, 1) as \"EvnPrescr_IsExec\",
			    case when EU.EvnUsluga_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\",
			    case when 2 = EP.EvnPrescr_IsExec
					then to_char(EP.EvnPrescr_updDT::date, '{$this->dateTimeForm104}')||' '||to_char(EP.EvnPrescr_updDT::date, '{$this->dateTimeForm108}') else null
				end as \"EvnPrescr_execDT\",
			    EP.PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
			    EP.PrescriptionType_id as \"PrescriptionType_id\",
			    EP.PrescriptionType_id as \"PrescriptionType_Code\",
			    coalesce(EP.EvnPrescr_IsCito, 1) as \"EvnPrescr_IsCito\",
				coalesce(EP.EvnPrescr_Descr, '') as \"EvnPrescr_Descr\",
			    case when ED.EvnDirection_id is null or coalesce(ED.EvnStatus_id, 16) in (12,13) then 1 else 2 end as \"EvnPrescr_IsDir\",
			    case when ED.EvnStatus_id is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 12 else ED.EvnStatus_id end as \"EvnStatus_id\",
			    case when EvnStatus.EvnStatus_Name is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 'Отменено' else EvnStatus.EvnStatus_Name end as \"EvnStatus_Name\",
			    coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name) as \"EvnStatusCause_Name\",
			    to_char(coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT)::date, '{$this->dateTimeForm104}') as \"EvnDirection_statusDate\",
			    ESH.EvnStatusCause_id as \"EvnStatusCause_id\",
			    ED.DirFailType_id as \"DirFailType_id\",
			    EQ.QueueFailCause_id as \"QueueFailCause_id\",
			    ESH.EvnStatusHistory_Cause as \"EvnStatusHistory_Cause\",
			    ED.EvnDirection_id as \"EvnDirection_id\",
			    EPFD.Resource_id as \"Resource_id\",
			    EQ.EvnQueue_id as \"EvnQueue_id\",
			    case when ED.EvnDirection_Num is null then '' else ED.EvnDirection_Num::varchar end as \"EvnDirection_Num\",
			    case
					when TTMS.TimetableMedService_id is not null then coalesce(MS.MedService_Name, '')||' / '||coalesce(Lpu.Lpu_Nick, '')
					when TTR.TimetableResource_id is not null then coalesce(MS.MedService_Name, '')||' / '||coalesce(R.Resource_Name, '')||' / '||coalesce(Lpu.Lpu_Nick, '')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and MS.LpuSection_id is null and MS.LpuUnit_id is null
								then coalesce(MS.MedService_Name, '')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
								then coalesce(MS.MedService_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
								then coalesce(MS.MedService_Name, '')||' / '||coalesce(LSPD.LpuSectionProfile_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
							else coalesce(LSPD.LpuSectionProfile_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
						end||' / '||coalesce(Lpu.Lpu_Nick, '')
				else '' end as \"RecTo\",
				case
					when TTMS.TimetableMedService_id is not null then coalesce(to_char(TTMS.TimetableMedService_begTime::date, '{$this->dateTimeForm104}'), '')||' '||coalesce(to_char(TTMS.TimetableMedService_begTime::time, '{$this->dateTimeForm108}'), '')
					when TTR.TimetableResource_id is not null then coalesce(to_char(TTR.TimetableResource_begTime::date, '{$this->dateTimeForm104}'), '')||' '||coalesce(to_char(TTR.TimetableResource_begTime::time, '{$this->dateTimeForm108}'), '')
					when EQ.EvnQueue_id is not null then 'В очереди с '||coalesce(to_char(EQ.EvnQueue_setDate::date, '{$this->dateTimeForm104}'), '')
				else '' end as \"RecDate\",
			    case
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when TTR.TimetableResource_id is not null then 'TimetableResource'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
				else '' end as \"timetable\",
			    coalesce(TTMS.TimetableMedService_id, TTR.TimetableResource_id, EQ.EvnQueue_id, null) as \"timetable_id\",
			    EP.EvnPrescr_pid as \"timetable_pid\",
			    LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
			    DT.DirType_Code as \"DirType_Code\",
			    EP.MedService_id as \"MedService_id\",
			    UC.UslugaComplex_id as \"UslugaComplex_id\",
			    UC.UslugaComplex_2011id as \"UslugaComplex_2011id\",
			    UC.UslugaComplex_Code as \"UslugaComplex_Code\",
			    UC.UslugaComplex_Name as \"UslugaComplex_Name\",
			    EPFDU.EvnPrescrFuncDiagUsluga_id as \"TableUsluga_id\",
			    etr.ElectronicTalon_id as \"ElectronicTalon_id\",
			    case
					when ((ED.Lpu_did is not null and ED.Lpu_did <> LpuSession.Lpu_id) or (EPFD.Lpu_id is not null and EPFD.Lpu_id <> LpuSession.Lpu_id)) then 2 else 1
				end as \"otherMO\",
			    EvnStatus.EvnStatus_SysNick as \"EvnStatus_SysNick\",
			    EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
			    ED.StudyTarget_id as \"StudyTarget_id\",
			    EPFD.Lpu_id as \"Lpu_id\"
				{$addSelect}
			from
				v_EvnPrescr EP
				inner join EvnPrescrFuncDiagUsluga EPFDU on EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				inner join v_EvnPrescrFuncDiag EPFD on EPFD.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EPFDU.UslugaComplex_id
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
					    ED.Resource_id,
					    ED.LpuSectionProfile_id,
					    ED.DirType_id,
					    ED.EvnStatus_id,
					    ED.EvnDirection_statusDate,
					    ED.DirFailType_id,
					    ED.EvnDirection_failDT,
						ED.StudyTarget_id
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
					select
					    EQ.EvnQueue_id,
				    	EQ.LpuUnit_did,
				    	EQ.LpuSectionProfile_did,
				    	Lpu_id,
				    	EQ.EvnQueue_setDate,
				    	EQ.EvnQueue_failDT,
				    	EQ.QueueFailCause_id
					from v_EvnQueue EQ
					where EQ.EvnDirection_id = ED.EvnDirection_id
				      and EQ.EvnQueue_recDT is null
					union
					select
				    	EQ.EvnQueue_id,
				    	EQ.LpuUnit_did,
				    	EQ.LpuSectionProfile_did,
				    	Lpu_id,
				    	EQ.EvnQueue_setDate,
				    	EQ.EvnQueue_failDT,
				    	EQ.QueueFailCause_id
					from v_EvnQueue EQ
					where EQ.EvnQueue_id = ED.EvnQueue_id
					  and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					  and EQ.EvnQueue_failDT is null
					limit 1
				) as EQ on true
				left join lateral (
					select
				    	ESH.EvnStatus_id,
				    	ESH.EvnStatusCause_id,
				    	ESH.pmUser_insID,
				    	ESH.EvnStatusHistory_Cause
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
				left join lateral (
					select
				    	TimetableResource_id,
				    	TimetableResource_begTime
					from v_TimetableResource_lite TTR
				    where TTR.EvnDirection_id = ED.EvnDirection_id
				    limit 1
				) as TTR on true
				left join v_Resource R on R.Resource_id = ED.Resource_id
				left join v_LpuSection LS on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				left join v_EvnUslugaPar EUP on EUP.EvnDirection_id = ED.EvnDirection_id
				left join v_LpuUnit LU on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id
				left join v_LpuSectionProfile LSPD on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
				left join v_Lpu LpuSession on LpuSession.Lpu_id = :Lpu_id
				left join lateral (
					select
				    	EvnUsluga_id,
				    	EvnUsluga_setDT
					from v_EvnUsluga
					where EP.EvnPrescr_IsExec = 2
				      and UC.UslugaComplex_id is not null
				      and EvnPrescr_id = EP.EvnPrescr_id
				    limit 1
				) as EU on true
				left join lateral (
					select etra.ElectronicTalon_id
					from
				    	v_ElectronicTalonRedirect etra
						inner join v_ElectronicTalon et on (et.ElectronicService_id = etra.ElectronicService_id and et.EvnDirection_uid = etra.EvnDirection_uid)
					where etra.EvnDirection_uid = ED.EvnDirection_id
				    limit 1
				) as etr on true
				{$addJoin}
			where
				{$filterEv}
				and EP.PrescriptionType_id = 12
				and EP.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescr_id,
				EP.EvnPrescr_setDT
		";
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$tmp_arr = $result->result("array");
		$response = [];
		$last_ep = null;
		$is_exe = null;
		$is_sign = null;
		$uslugaIdList = [];
		$uslugaList = [];
		foreach ($tmp_arr as $i => $row) {
			if ($last_ep != $row["EvnPrescr_id"]) {
				//это первая итерация с другим назначением
				$last_ep = $row["EvnPrescr_id"];
				$is_exe = false;
				$is_sign = false;
				$uslugaIdList = [];
				$uslugaList = [];
			}
			if (empty($uslugaList[$row["TableUsluga_id"]])) {
				$uslugaIdList[] = $row["UslugaComplex_id"];
				if ($this->options["prescription"]["enable_show_service_code"]) {
					$uslugaList[$row["TableUsluga_id"]] = $row["UslugaComplex_Code"] . " " . $row["UslugaComplex_Name"];
				} else {
					$uslugaList[$row["TableUsluga_id"]] = $row["UslugaComplex_Name"];
				}
			}
			if ($is_exe == false) {
				$is_exe = ($row["EvnPrescr_IsExec"] == 2);
			}
			if ($is_sign == false) {
				$is_sign = ($row["PrescriptionStatusType_id"] == 2);
			}
			if (empty($tmp_arr[$i + 1]) || $last_ep != $tmp_arr[$i + 1]["EvnPrescr_id"]) {
				if ($is_exe) $row["EvnPrescr_IsExec"] = 2;
				if ($is_sign) $row["PrescriptionStatusType_id"] = 2;
				$row["UslugaId_List"] = implode(",", $uslugaIdList);
				$row["Usluga_List"] = implode("<br />", $uslugaList);
				$row[$section . "_id"] = $row["EvnPrescr_id"] . "-" . $row["TableUsluga_id"];
				$response[] = $row;
			}
		}
		//загружаем документы
		$tmp_arr = [];
		$evnPrescrIdList = [];
		foreach ($response as $key => $row) {
			if (
				isset($row["EvnPrescr_IsExec"]) &&
				$row["EvnPrescr_IsExec"] == 2 &&
				isset($row["EvnPrescr_IsHasEvn"]) &&
				$row["EvnPrescr_IsHasEvn"] == 2
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
		return $response;
	}

	/**
	 * Очистка связанных зубов с назначения на ФД
	 * @param $data
	 */
	function delEvnUslugaToothNum($data)
	{
		$this->load->model("EvnUsluga_model", "eumodel");
		if (empty($data["EvnUsluga_id"]) && !empty($data["EvnPrescr_id"])) {
			$resp = $this->eumodel->getEvnUslugaByEvnPrescrId(["EvnPrescr_id" => $data["EvnPrescr_id"]]);
			if (!empty($resp[0]) && !empty($resp[0]["EvnUsluga_id"])) {
				$data["EvnUsluga_id"] = $resp[0]["EvnUsluga_id"];
			}
		}
		if (!empty($data["EvnUsluga_id"])) {
			// возмьем данные по существующим зубам для события_услуги
			$resp = $this->eumodel->getToothNumEvnUsluga(["EvnUsluga_id" => $data["EvnUsluga_id"]]);
			if (!empty($resp[0]) && empty($resp[0]["Error_Msg"])) {
				$related_tooths = array_column($resp, "ToothNumEvnUsluga_ToothNum", "ToothNumEvnUsluga_id");
				foreach ($related_tooths as $key => $value) {
					$this->eumodel->delToothNumEvnUsluga($key);
				}
			}
		}
	}

	/**
	 * Обработка после отмены назначения
	 * @param $data
	 */
	function onAfterCancel($data)
	{
		// очистка связанных зубов с назначения на ФД
		if (!empty($data["parentEvnClass_SysNick"]) && $data["parentEvnClass_SysNick"] == "EvnVizitPLStom"
		) {
			$this->delEvnUslugaToothNum($data);
		}
	}
}