<?php
/**
 * Timetable_model - модель с базовыми методами для работы с расписанием
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan-it.ru/
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @author       Artem Mityushov (a.mityushov@swan-it.ru)
 * @version      06.08.2019
 *
 * @property CI_DB_driver $db
 * @property TimetableQuote_model $tqmodel
 * @property UserPortal_model $UserPortal_model
 * @property EvnDirection_model $edmodel
 * @property bool $usePostgreLis
 * @property SwServiceApi $lis
 * @property EvnLabRequest_model $EvnLabRequest_model
 * @property EvnUsluga_model $eumodel
 * @property Queue_model $Queue_model
 * @property EvnPrescr_model $EvnPrescr_model
 * @property Mse_model msemodel
 * @property Annotation_model $anmodel
 * @property Resource_model $resmodel
 * @property CI_Config $config
 */

class Timetable_model extends SwPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm108 = "HH24:MI:SS";
	/**
	 * Сокращенные дни недели
	 */
	var $arShortWeekDayName = [
		0 => "ВС",
		1 => "ПН",
		2 => "ВТ",
		3 => "СР",
		4 => "ЧТ",
		5 => "ПТ",
		6 => "СБ",
	];

	/**
	 * Кэш данных по бирке поликлиники
	 */
	protected $TTGData = NULL;

	/**
	 * Кэш данных по бирке стационара
	 */
	protected $TTSData = NULL;

	/**
	 * Кэш данных по бирке службы
	 */
	protected $TTMSData = NULL;

	/**
	 * Кэш данных по бирке службы
	 */
	protected $TTMSOData = NULL;
	/**
	 * Кэш данных по бирке службы
	 */
	protected $TTRData = NULL;

	/**
	 * Проверка, что бирка существует и занята
	 * проверяет любой тип бирки, в зависимости от пришедших данных
	 * @param $data
	 * @param $checkOneInGroup boolean флаг проверки при смене типа бирки (запись хотя бы одного)
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetableOccupied($data, $checkOneInGroup = false) {
		//print_r($data);exit();
		$tt_data = $this->getRecordData($data);

		if (!isset($tt_data[$data['object'].'_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			);
		}

		// При смене типа бирки, если хотя бы один пациент на нее записан, замена типа не производится
		if($checkOneInGroup && $data['object']=='TimetableGraf'&&!empty($tt_data['TimetableType_id'])&&$tt_data['TimetableType_id']==14&&!empty($tt_data['TimeTableGraf_countRec']))
			return true;

		if (
			(!isset($tt_data['Person_id'])&&$data['object']!='TimetableMedServiceOrg'&&empty($data['TimetableGrafRecList_id']))
			||($data['object']=='TimetableMedServiceOrg'&&!isset($tt_data['Org_id']))
			||($data['object']=='TimetableGraf'&&!empty($tt_data['TimetableType_id'])&&$tt_data['TimetableType_id']==14&&empty($tt_data['TimeTableGraf_countRec']))
		) {
			return array(
				'success' => false,
				'Error_Msg' => 'Выбранная вами бирка уже свободна.'
			);
		}
		/*if ( !empty($tt_data['Slot_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Удаление бирки не возможно, данные используются в федеральной электронной регистратуре.'
			);
		}*/
		if ( !empty($tt_data['EvnVizit_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Освобождение бирки невозможно, поскольку прием уже осуществлен.'
			);
		}
		if (
			(empty($data['cancelType']) || (!empty($data['cancelType']) && $data['cancelType'] != 'cancel'))
			&&$data['object']=='TimetableGraf'&&!empty($tt_data['TimetableType_id'])
			&&$tt_data['TimetableType_id']==14&&!empty($tt_data['TimeTableGraf_countRec'])
		) {
			return array(
				'success' => false,
				'Error_Msg' => 'На выбранную Вами бирку уже записан(ы) пациенты. Смена статуса невозможна.'
			);
		}
		return true;
	}

	/**
	 * Проверка, что все из переданного массива бирок существуют и свободны проверяет любой тип бирки, в зависимости от пришедших данных
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetablesFree($data)
	{
		/**@var CI_DB_result $result */
		// Название объекта
		$prefix = $data["object"];
		if (!isset($data[$data["object"] . "Group"]) || count($data[$prefix . "Group"]) == 0) {
			return [
				"success" => false,
				"Error_Msg" => "Не задано ни одной бирки"
			];
		}
		// Если задана группа бирок, то проверяем всю группу
		$prefixString = implode(", ", $data[$prefix . "Group"]);
		$prefixFieldName = "Person_id";
		$sql = "
			SELECT count(*) as cnt, count({$prefixFieldName}) as recorded
			FROM {$prefix}
			WHERE {$prefix}_id in ({$prefixString})
		";
		$result = $this->db->query($sql);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result("array");
		if (!isset($result[0]) || $result[0]["cnt"] != count($data[$prefix . "Group"])) {
			// количество вернувшихся бирок не равно количеству проверяемых, значит одна из бирок не существует
			return [
				"success" => false,
				"Error_Msg" => "Одной из переданных бирок не существует"
			];
		}
		if (isset($res[0]["recorded"]) && $result[0]["recorded"] > 0) {
			// среди записанных бирок есть хоть одна занятая
			return [
				"success" => false,
				"Error_Msg" => "Среди бирок есть занятая бирка"
			];
		}
		return true;
	}

	/**
	 * Проверка прав на освобождение бирки
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkHasRightsToClearRecord($data)
	{
		/**@var CI_DB_result $result */
		$session = $data["session"];
		$joinArray = [
			"left join v_pmUser pu on t.pmUser_updId = pu.pmUser_id",
			"left join v_Lpu l on l.Lpu_id = pu.Lpu_id",
			"left join v_EvnDirection_all ED on ED.EvnDirection_id = t.EvnDirection_id",
		];
		$dataObjectName = $data["object"];
		$selectionArray = [
			"t.{$dataObjectName}_id as \"{$dataObjectName}_id\"",
			"t.Person_id as \"Person_id\"",
			"t.pmUser_updId as \"pmUser_updId\"",
			"t.EvnDirection_id as \"EvnDirection_id\"",
			"ED.MedPersonal_id as \"MedPersonal_id\"",
			"pu.Lpu_id as \"Lpu_id\"",
			"l.Org_id as \"Org_id\"",
			"
				CASE
					WHEN coalesce(ED.Lpu_did, ED.Lpu_id) = ED.Lpu_sid THEN 'both'
					WHEN coalesce(ED.Lpu_did, ED.Lpu_id) = :Lpu_id THEN 'incoming'
					ELSE 'outcoming'
				END as \"DirectionFrom\"
			"
		];
		if ("TimetableGraf" == $data["object"] && (!isSuperAdmin())) {
			$selectionArray[] = "coalesce(ev.EvnVizit_id,0) as \"EvnVizit_id\"";
			$joinArray[] = "left join v_EvnVizit ev on t.TimetableGraf_id = ev.TimetableGraf_id";
		}
		if (!isSuperAdmin()) {
			switch ($data["object"]) {
				case "TimetableGraf":
					$selectionArray[] = "to_char(t.TimetableGraf_begTime, '{$this->dateTimeForm104}') as \"Timetable_Date\"";
					break;
				case "TimetablePar":
					$selectionArray[] = "to_char(t.TimetablePar_begTime, '{$this->dateTimeForm104}') as \"Timetable_Date\"";
					break;
				case "TimetableStac":
					$selectionArray[] = "to_char(t.TimetableStac_setDate, '{$this->dateTimeForm104}') as \"Timetable_Date\"";
					break;
				case "TimetableMedService":
					$selectionArray[] = "to_char(t.TimetableMedService_begTime, '{$this->dateTimeForm104}') as \"Timetable_Date\"";
					break;
				case "TimetableResource":
					$selectionArray[] = "to_char(t.TimetableResource_begTime, '{$this->dateTimeForm104}') as \"Timetable_Date\"";
					break;
			}
		}
		if ($data["object"] == "TimetableGraf") {
			$selectionArray[] = "coalesce(MSF.MedPersonal_id, ED.MedPersonal_did) as \"MedPersonal_did\"";
			$selectionArray[] = "
				(
					select rl.Person_id
					from v_TimeTableGrafRecList rl
					where rl.TimetableGraf_id = t.TimetableGraf_id
					limit 1 
				) AS \"Person_gid\"
			";
			$joinArray[] = "left join v_MedStaffFact MSF on MSF.MedStaffFact_id = t.MedStaffFact_id";
		} else {
			$selectionArray[] = "ED.MedPersonal_did as \"MedPersonal_did\"";
		}

		$selectionString = implode(", ", $selectionArray);
		$joinString = implode("\n ", $joinArray);
		$sql = "
			select {$selectionString}
			FROM {$dataObjectName} t
			{$joinString}
			WHERE t.{$dataObjectName}_id = :Id
		";
		$sqlParams = [
			"Id" => $data[$data["object"] . "_id"],
			"Lpu_id" => $session["lpu_id"]
		];
		$result = $this->getFirstRowFromQuery($sql, $sqlParams);

		if ( !isset($result[$data["object"]."_id"]) ) {
			return [
				"success" => false,
				"Error_Msg" => "Бирка с таким идентификатором не существует."
			];
		}
		if ( empty($result["Person_id"]) && empty($result["Person_gid"])) {
			return [
				"success" => false,
				"Error_Msg" => "Бирка с таким идентификатором была освобождена ранее."
			];
		}
		$MedPersonal_id = $session["medpersonal_id"];
		$rules = [
			"incoming" => [
				// входящее направление, когда текущее Lpu_id равно EvnDirection.Lpu_did
				"allCanCancel" => (!empty($session["setting"]["server"]["evn_direction_cancel_right_mo_where_adressed"]) && $session["setting"]["server"]["evn_direction_cancel_right_mo_where_adressed"] == 2) ? true : false, // если есть глобальная настройка
				"hasGroupThatCanCancel" => in_array("toCurrMoDirCancel", explode("|", $session["groups"])) ? true : false, // если есть группа у пользователя
				"relates" => $result["MedPersonal_did"] == $MedPersonal_id ? true : false // если к нему
			],
			"outcoming" => [
				// исходящее Lpu_id = EvnDirection.Lpu_sid
				"allCanCancel" => (!empty($session["setting"]["server"]["evn_direction_cancel_right_mo_where_created"]) && $session["setting"]["server"]["evn_direction_cancel_right_mo_where_created"] == 2) ? true : false, // если есть глобальная настройка
				"hasGroupThatCanCancel" => in_array("currMoDirCancel", explode("|", $session["groups"])) ? true : false, // если есть группа у пользователя
				"relates" => $result["MedPersonal_id"] == $MedPersonal_id ? true : false // если создал
			]
		];
		$rules["both"] = [
			// внутри одной мо, Lpu_id = EvnDirection.Lpu_did = EvnDirection.Lpu_sid. Или правила для входяших, или для исходящих
			"allCanCancel" => $rules["incoming"]["allCanCancel"] || $rules["outcoming"]["allCanCancel"],
			"hasGroupThatCanCancel" => $rules["incoming"]["hasGroupThatCanCancel"] || $rules["outcoming"]["hasGroupThatCanCancel"],
			"relates" => $rules["incoming"]["relates"] || $rules["outcoming"]["relates"]
		];
		$userHasRightToCancel = $rules[$result["DirectionFrom"]]["allCanCancel"] || $rules[$result["DirectionFrom"]]["hasGroupThatCanCancel"] || $rules[$result["DirectionFrom"]]["relates"];

		/**@var pmAuthUser $_USER */
		global $_USER;
		$isNeedCheckUser = true;
		$this->load->helper("Reg");
		if (!empty($data["cancelType"]) && $data["cancelType"] == "decline") {
			// отклонение направления в МО, в которое направили
			// проверку на пользователя надо убрать, иначе ничего нельзя будет отклонить #68892
			$isNeedCheckUser = false;
		} else {
			// отмена направления в МО, из которого направили
			if (isCZAdmin() ||
				isLpuRegAdmin($result["Org_id"]) ||
				isInetUser($result["pmUser_updId"]) ||
				($_USER->belongsToOrg($result["Org_id"]) && getRegionNick() == "pskov")
			) {
				// разрешить отменять записи созданные другими пользователями
				$isNeedCheckUser = false;
			}
			if ($isNeedCheckUser &&
				!empty($data["session"]) &&
				!empty($data["session"]["CurArmType"]) &&
				"regpol" == $data["session"]["CurArmType"] &&
				(IsFerUser($result["pmUser_updId"]) || IsFerPerson($result["Person_id"]))
			) {
				// разрешить отменять записи пришедшие из ФЭР в АРМ регистратора поликлиники #69681
				$isNeedCheckUser = false;
			}
		}
		if ($isNeedCheckUser && !$userHasRightToCancel) {
			return [
				"success" => false,
				"Error_Msg" => "У вас нет прав отменить запись на прием, <br/>так как она сделана не вами."
			];
		}
		if (!empty($res["EvnVizit_id"])) {
			return [
				"success" => false,
				"Error_Msg" => "У вас нет прав отменить запись на прием, <br/>так как по ней был заведен ТАП."
			];
		}
		return true;
	}

	/**
	 * Проверка существования записи на бирку перед созданием записи в очередь
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkRecordExists($data)
	{
		$params = [
			"LpuUnit_id" => $data["LpuUnit_did"],
			"LpuSectionProfile_id" => $data["LpuSectionProfile_did"],
			"Person_id" => $data["Person_id"]
		];
		$filter = "";
		// по тому же направлению не нужно выдавать предупреждений, чтобы сразу выполнялось перенаправление.
		if (!empty($data["EvnDirection_id"])) {
			$params["EvnDirection_id"] = $data["EvnDirection_id"];
			$filter = " and ttg.EvnDirection_id != :EvnDirection_id";
		}
		$selectPersonFio = "ps.Person_SurName||' '||ps.Person_FirName||' '||coalesce(ps.Person_SecName,'') as \"Person_Fio\"";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data["session"])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh on peh.Person_id = ps.Person_id";
			$selectPersonFio = "
				case when peh.PersonEncrypHIV_Encryp is null 
					then ps.Person_SurName||' '||ps.Person_FirName||' '||coalesce(ps.Person_SecName,'') 
					else peh.PersonEncrypHIV_Encryp 
				end as \"Person_Fio\"
			";
		}
		$query = "
			select ttg.EvnDirection_id as \"EvnDirection_id\"
			      ,ttg.TimetableGraf_id as \"TimetableGraf_id\"
			      ,ED.ARMType_id as \"ARMType_id\"
			      ,ED.EvnStatus_id as \"EvnStatus_id\"
			      ,case coalesce(ED.Lpu_did, ED.Lpu_id)
			          when ED.Lpu_sid then 'both'
			          when {$data['session']['lpu_id']} then 'incoming'
			          else 'outcoming'
			       end as \"RecordDirection\"
			      ,ED.MedPersonal_id as \"MedPersonal_id\"
			      ,ED.MedPersonal_did as \"MedPersonal_did\"
			      ,{$selectPersonFio}
			      ,msf.Person_Fin as \"MedPersonal_Fin\"
			      ,l.Lpu_Nick as \"Lpu_Nick\"
			      ,to_char(ttg.TimetableGraf_begTime, '{$this->dateTimeForm104}') as \"TimetableGraf_Date\"
			      ,to_char(ttg.TimetableGraf_begTime, '{$this->dateTimeForm108}') as \"TimetableGraf_Time\"
			from v_TimetableGraf_lite ttg
			     left join v_PersonState ps on ps.Person_id = ttg.Person_id
			     left join v_MedStaffFact msf on msf.MedStaffFact_id = ttg.MedStaffFact_id
			     left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			     left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
			     left join v_Lpu l on l.Lpu_id = lu.Lpu_id
			     left join v_EvnDirection_all ED on ED.EvnDirection_id = ttg.EvnDirection_id
			     {$joinPersonEncrypHIV}
			where lu.LpuUnit_id = :LpuUnit_id
			  and ls.LpuSectionProfile_id = :LpuSectionProfile_id
			  and ttg.Person_id = :Person_id
			  and ttg.TimetableType_id is not null
			  and ttg.TimetableGraf_factTime is null
			  and cast(ttg.TimetableGraf_begTime as date) >= tzgetdate()
			  {$filter}
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result("array");
		if (count($result) == 0) {
			return false;
		}
		$result = $result[0];
		$response = [
			"TimetableGraf_id" => $result["TimetableGraf_id"],
			"EvnDirection_id" => $result["EvnDirection_id"],
			"EvnStatus_id" => $result["EvnStatus_id"],
			"MedPersonal_id" => $result["MedPersonal_id"],
			"MedPersonal_did" => $result["MedPersonal_did"],
			"ARMType_id" => $result["ARMType_id"],
			"RecordDirection" => $result["RecordDirection"]
		];
		$med_personal_fin = empty($result["MedPersonal_Fin"]) ? "" : ", врач: {$result['MedPersonal_Fin']}";
		$response["warning"] = "
					Пациент {$result['Person_Fio']} уже имеет запись по этому профилю на {$result['TimetableGraf_Date']}, {$result['TimetableGraf_Time']}
					<br/>в ЛПУ: {$result['Lpu_Nick']}{$med_personal_fin}.
					<br/>Отменить запись?
				";
		return $response;
	}

	/**
	 * Проверка существования записи в очередь перед созданием записи на бирку
	 * Контроль на нахождение пациента в очереди либо на бирке (койке) по какому-либо профилю должен применяться только при записи на прием к врачу, либо на стационарный профиль.
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkQueueExists($data)
	{
		$params = [
			"LpuUnit_did" => isset($data["LpuUnit_did"]) ? $data["LpuUnit_did"] : null,
			"LpuSectionProfile_did" => isset($data["LpuSectionProfile_id"]) ? $data["LpuSectionProfile_id"] : null,
			"Person_id" => $data["Person_id"]
		];
		$filterString = "";
		if (!empty($data["EvnDirection_id"])) {
			// по тому же направлению не нужно выдавать предупреждений, чтобы сразу выполнялась запись из очереди.
			$params["EvnDirection_id"] = $data["EvnDirection_id"];
			$filterString .= " and eq.EvnDirection_id != :EvnDirection_id";
		}
		if (!empty($data["MedService_id"])) {
			// если известна служба, проверяем её
			$params["MedService_id"] = $data["MedService_id"];
			$filterString .= " and ed.MedService_id = :MedService_id";
		}
		$selectPersonFio = "ps.Person_SurName||' '||ps.Person_FirName||' '||coalesce(ps.Person_SecName,'') as \"Person_Fio\"";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data["session"])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh on peh.Person_id = ps.Person_id";
			$selectPersonFio = "
				case when peh.PersonEncrypHIV_Encryp is null 
					then ps.Person_SurName||' '||ps.Person_FirName||' '||coalesce(ps.Person_SecName, '') 
					else peh.PersonEncrypHIV_Encryp 
				end as \"Person_Fio\"
			";
		}
		$query = "
			select ed.EvnDirection_id as \"EvnDirection_id\"
			      ,ed.EvnDirection_pid as \"EvnDirection_pid\"
			      ,coalesce(ed.EvnDirection_IsAuto, 1) as \"EvnDirection_IsAuto\"
			      ,ed.EvnStatus_SysNick as \"EvnStatus_SysNick\"
			      ,eq.EvnQueue_id as \"EvnQueue_id\"
			      ,epd.EvnPrescr_id as \"EvnPrescr_id\"
			      ,{$selectPersonFio}
			      ,mp.Person_Fin as \"MedPersonal_Fin\"
			      ,l.Lpu_Nick as \"Lpu_Nick\"
			      ,to_char(eq.EvnQueue_setDate, '{$this->dateTimeForm104}') as \"EvnQueue_Date\"
			from v_EvnQueue eq
			     left join lateral (
			          select ed.EvnDirection_id
			                ,ed.EvnDirection_pid
			                ,es.EvnStatus_SysNick
			                ,ed.EvnDirection_IsAuto
			                ,ed.MedService_id
			          from v_EvnDirection_all ed
			               left join v_EvnStatus es on es.EvnStatus_id = ed.EvnStatus_id
			          where ed.EvnDirection_id = eq.EvnDirection_id
			            and es.EvnStatus_SysNick = 'Queued'
			          limit 1
			     ) as ed on true
			     left join v_PersonState ps on ps.Person_id = eq.Person_id
			     left join v_MedPersonal mp on mp.MedPersonal_id = eq.MedPersonal_did
			     left join v_LpuUnit lu on lu.LpuUnit_id = eq.LpuUnit_did
			     left join v_Lpu l on l.Lpu_id = lu.Lpu_id
			     left join v_EvnPrescrDirection epd on epd.EvnDirection_id = ed.EvnDirection_id
			{$joinPersonEncrypHIV}
			where eq.LpuUnit_did = :LpuUnit_did
			  and ed.EvnDirection_id is not null
			  and eq.LpuSectionProfile_did = :LpuSectionProfile_did
			  and eq.Person_id = :Person_id
			  and eq.EvnQueue_recDT is null
			  and eq.EvnQueue_failDT is null
			{$filterString}
			limit 1
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result("array");
		if (count($result) == 0) {
			return false;
		}
		$result = $result[0];
		$response = [
			"EvnDirection_id" => $result["EvnDirection_id"],
			"EvnDirection_pid" => $result["EvnDirection_pid"],
			"EvnDirection_IsAuto" => $result["EvnDirection_IsAuto"],
			"EvnStatus_SysNick" => $result["EvnStatus_SysNick"],
			"EvnQueue_id" => $result["EvnQueue_id"],
			"EvnPrescr_id" => $result["EvnPrescr_id"],
		];
		$med_personal_fin = empty($result["MedPersonal_Fin"]) ? "" : ", врач: {$result['MedPersonal_Fin']}";
		$response["warning"] = "
					Пациент {$result['Person_Fio']} находится в очереди по этому профилю с {$result['EvnQueue_Date']}
					<br/>в ЛПУ: {$result['Lpu_Nick']}{$med_personal_fin}.
					<br/>Исключить пациента из очереди по данному профилю?
				";
		return $response;
	}

	/**
	 * Проверка записи на резервную бирку чужого МО
	 * @param $data
	 * @return bool
	 */
	function checkRecordTTGReserveOtherLpu($data)
	{
		$ttg_data = $this->getRecordTTGData($data);
		return ($ttg_data["TimetableType_id"] != 2);
	}

	/**
	 * Запись человека на бирку
	 * @param $data
	 * @param bool $checkTimetableFree
	 * @return array|bool
	 * @throws Exception
	 */
	function Apply($data, $checkTimetableFree = true)
	{
		$res = null;
		$tt_data = null;
		if ($checkTimetableFree && true !== ($res = $this->checkTimetableFree($data))) {
			return $res;
		}
		$this->load->helper("Reg");
		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			if (true !== ($res = $this->checkNotArchive($data))) {
				return [
					"success" => false,
					"Error_Msg" => "Запись на архивные даты запрещена"
				];
			}
		}
		if (empty($data["IgnoreCheckAlreadyHasRecordOnThisTime"])) {
			$res = $this->checkAlreadyHasRecordOnThisTime($data);
			if (is_array($res) && isset($res["info"])) {
				if ("TimetableMedService" == $data["object"]) {
					return [
						"success" => false,
						"alreadyHasRecordOnThisTime" => "У пациента уже есть запись на выбранное время. ".$res["info"]
					];
				} else {
					return[
						"success" => false,
						"Error_Msg" => "У пациента уже есть запись на выбранное время. ".$res["info"]
					];
				}
			}
		}
		switch ($data["object"]) {
			case "TimetableGraf":
				// Расписание, бирки по поликлинике ???
				$tt_data = $this->getRecordTTGData($data);
				if (!IsCZUser() && IsOtherLpuRegUser($tt_data["Org_id"])) {
					if (true !== ($res = $this->checkRecordTTGOtherLpu($data))) {
						return [
							"success" => false,
							"Error_Msg" => "Запись на выбранное время в чужую МО невозможна"
						];
					}
					if (true !== ($res = $this->checkRecordTTGReserveOtherLpu($data))) {
						return [
							"success" => false,
							"Error_Msg" => "Запись на резервную бирку в чужую МО невозможна"
						];
					}
				}
				$this->load->model("TimetableQuote_model", "tqmodel");
				// Если передан врач, то запись от него
				if (!empty($data["From_MedStaffFact_id"])) {
					$tt_data["From_MedStaffFact_id"] = $data["From_MedStaffFact_id"];
				}
				if (($err = $this->tqmodel->checkTimetableQuote($data, $tt_data, "ttg")) !== true) {
					return [
						"success" => false,
						"Error_Msg"=> ($err === false) ? "Запись невозможна. Превышена квота записи для вашей МО." : $err
					];
				}
				if (!empty($tt_data) &&
					!empty($tt_data["TimetableType_id"]) &&
					$tt_data["TimetableType_id"] == 14
				) {
					if (!empty($tt_data["TimeTableGraf_countRec"]) &&
						intval($tt_data["TimeTableGraf_countRec"]) >= intval($tt_data["TimeTableGraf_PersRecLim"])
					) {
						return [
							"success" => false,
							"Error_Msg"=> "Запись невозможна. Превышено количество записей в группу."
						];
					}
				}
				break;
			case "TimetableMedService":
				// Расписание службы
				$tt_data = $this->getRecordTTMSData($data);
				$this->load->model("TimetableQuote_model", "tqmodel");
				if (($err = $this->tqmodel->checkTimetableQuote($data, $tt_data, "ttms")) !== true) {
					return [
						"success" => false,
						"Error_Msg"=> ($err === false) ? "Запись невозможна. Превышена квота записи для вашей МО." : $err
					];
				}
				$data["AnswerQueue"] = false;
				break;
			case "TimetableMedServiceOrg":
				// Расписание службы для организаций
				$tt_data = $this->getRecordTTMSOData($data);
				$data["AnswerQueue"] = false;
				$tt_data["TimetableType_id"] = 1;
				$tt_data["object"] = "TimetableMedServiceOrg";
				break;
			case "TimetableResource":
				// Расписание ресурсов
				$tt_data = $this->getRecordTTRData($data);
				$this->load->model("TimetableQuote_model", "tqmodel");
				// Если передан врач, то запись от него
				if (!empty($data["From_MedStaffFact_id"])) {
					$tt_data["From_MedStaffFact_id"] = $data["From_MedStaffFact_id"];
				}
				if (($err = $this->tqmodel->checkTimetableQuote($data, $tt_data, "ttr")) !== true) {
					return [
						"success" => false,
						"Error_Msg"=> ($err === false) ? "Запись невозможна. Превышена квота записи для вашей МО." : $err
					];
				}
				$data["AnswerQueue"] = false;
				break;
			case "TimetableStac":
				// Расписание, бирки по стационару
				$tt_data = $this->getRecordTTSData($data);
				break;
		}
		if (!empty($data["MedService_id"]) &&
			!empty($tt_data["MedService_id"]) &&
			$data["MedService_id"] != $tt_data["MedService_id"] &&
			(empty($tt_data["MedServiceType_SysNick"]) || $tt_data["MedServiceType_SysNick"] != "pzm")
		) {
			// бирка должна быть от той службы, куда ведётся запись когда записываем на бирку пункта забора, то служба куда направили будет лабораторией.
			return [
				"success" => false,
				"Error_Msg" => "Некорректно выбрана бирка."
			];
		}
		if (empty($data["ignoreCanRecord"]) && !canRecord($tt_data, $data)) {
			return [
				"success" => false,
				"Error_Msg" => "Извините, запись на бирку запрещена."
			];
		}
		if (!(IsLpuAdmin($data["Lpu_id"]) || IsCZUser())) {
			// запись на прошедшее время разрешена пользователям ЦЗ и администраторам своего МО
			if (true !== ($res = $this->checkPastTimeRecord($tt_data))) {
				return $res;
			}
		}
		if (!isset($data["AnswerQueue"]) || $data["AnswerQueue"]) {
			$queue = $this->checkQueueExists($data);
			if ($queue) {
				return [
					"Person_id" => $data["Person_id"],
					"Server_id" => $data["Server_id"],
					"PersonEvn_id" => $data["PersonEvn_id"],
					"success" => false,
					"queue" => $queue
				];
			}
		}
		if (empty($data["OverrideWarning"])) {
			// Дополнительные проверки при записи
			$warnings = [];
			switch ($data["object"]) {
				case "TimetableGraf":
					$warnings = $this->checkWarningsTTG($data);
					break;
			}
			if (count($warnings) > 0) {
				return [
					"Person_id" => $data["Person_id"],
					"Server_id" => $data["Server_id"],
					"PersonEvn_id" => $data["PersonEvn_id"],
					"success" => false,
					"warning" => implode("<br/>", $warnings) . "<br/> Продолжить запись?"
				];
			}
		}
		if (isset($data["EmergencyData_id"])) {
			// Если бронируем койку, то вариант только один - мы записываем в стационар
			$sql = "
				select error_code as \"Error_Code\",
				       error_message as \"Error_Message\"
				from p_timetablestac_reservbed(
					timetablestac_id := :TimetableStac_id,
				    person_id := :Person_id,
				    recclass_id := :RecClass_id,
				    emergencydata_id := :EmergencyData_id,
				    evn_pid := :Evn_pid,
				    pmuser_id := :pmUser_id
				);			
			";
			$sqlParams = [
				"Person_id" => $data["Person_id"],
				"pmUser_id" => $data["pmUser_id"],
				"Evn_pid" => $data["Evn_pid"],
				"EmergencyData_id" => $data["EmergencyData_id"],
				"RecClass_id" => 3, //Запись из ПромедВеб
				"TimetableStac_id" => $data[$data["object"] . "_id"]
			];
			$result = $this->db->query($sql, $sqlParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка при выполнении запроса к базе данных");
			}
			$result = $result->result("array");
			if (count($result) > 0 && empty($result[0]["Error_Msg"])) {
				// отправка STOMP-сообщения
				sendFerStompMessage([
					"id" => $data[$data["object"] . "_id"],
					"timeTable" => $data["object"],
					"action" => "Reserv",
					"setDate" => date("c")
				], "Rule");
			}
		} else {
			if (isset($data["redirectEvnDirection"]) &&
				$data["redirectEvnDirection"] == 600 &&
				empty($data["EvnQueue_id"])
			) {
				return [
					"success" => false,
					"Error_Code" => 400,
					"Error_Msg" => "Параметр EvnQueue_id обязателен для правильной работы записи из очереди",
				];
			}
			if ($data["object"] === "TimetableMedServiceOrg") {
				$tt_data["pmUser_id"] = $data["pmUser_id"];
				$tt_data["Org_id"] = $data["Org_id"];
				$this->recordTimetableMedServiceOrgAuto($tt_data);
				$res = [$this->getRecordTTMSOData($data)];
			} else {
				if ($data["object"] === "TimetableStac") {
					$data["Evn_id"] = $data["Evn_pid"]; // какая-то нечеловеческая логика
				}
				if ($data["object"] === "TimetableGraf" &&
					$tt_data["TimetableGraf_begTime"] instanceof DateTime &&
					empty($this->config->config["USER_PORTAL_IS_ALLOW_NOTIFY_ABOUT_RECORD_CANCEL"] === false)
				) {
					if (!empty($tt_data["Person_id"])) {
						$Person_id = $tt_data["Person_id"];
					} else if (!empty($data["Person_id"])) {
						$Person_id = $data["Person_id"];
					} else {
						return [
							"success" => false,
							"Error_Msg" => "Не указан идентификатор пациента",
						];
					}
					$this->load->model("UserPortal_model");
					$this->UserPortal_model->notifyAboutRecordCancel($Person_id, $data["TimetableGraf_id"], $tt_data["TimetableGraf_begTime"]);
				}
				// Вместо обычной записи всегда создаем направление, запись происходит во время его создания
				$this->load->model("EvnDirection_model", "edmodel");

				if (!empty($data['PrescriptionType_Code']) && $data['PrescriptionType_Code'] == 11 && !empty($data['IncludeInDirection'])) {
					// значит нам туда дорога, зна-чит-нам-ту-да-до-ро-га (Лабораторная диагностика)
					$result = $this->edmodel->includeInDirection($data);
				}

				if (empty($data['IncludeInDirection'])) {
					if (empty($data["EvnQueue_id"]) && $data["object"] == "TimetableStac" && $data["EvnDirection_IsAuto"] == 2) {
						$this->recordTimetableStacAuto($data);
						$res = array($this->getRecordTTSData($data));
				} elseif (isset($data["redirectEvnDirection"]) && $data["redirectEvnDirection"] == 600) {
						$res = $this->edmodel->applyEvnDirectionFromQueue($data);
					} elseif ($this->usePostgreLis && $data["DirType_id"] == "10" && $data['MedServiceType_SysNick'] == 'lab') {
						$this->load->swapi("lis");
						$res = $this->lis->POST("EvnDirection", $data, "list");
					} else {
						$res = $this->edmodel->saveEvnDirection($data);
					}
				} else {
					$res = [$result];
				}

				if (!$this->isSuccessful($res)) {
					return $res;
				}
				// При записи на бирку стационара нужно обновить продолжительность лечения у бирки
				if ($data['object']=='TimetableStac' && !empty($data['TimetableStac_id']) && !empty($data['EvnDirection_setDate'])) {
					$this->load->model('EvnSection_model', 'esmodel');
					
					$healDuration = $this->esmodel->getAverageDateStatement(array(
						'Person_id' => $data['Person_id'],
						'Evn_setDT' => $data['EvnDirection_setDate'],
						'Diag_id' => $data['Diag_id'],
						'LpuSection_id' => $data['LpuSection_did']
					))[0];
					
					if (!empty($healDuration['Duration'])) {
						$this->saveObject('TimeTableStac', array(
							'TimeTableStac_id' => $data['TimetableStac_id'],
							'TimeTableStac_CureDuration' => $healDuration['Duration']
						));
					}
					else {
						return array(
							'success' => false,
							'Error_Msg' => 'Не удалось определить продолжительность лечения',
						);
					}
				}
				$data["EvnDirection_id"] = $res[0]["EvnDirection_id"];
				if (!empty($res[0]["EvnLabRequest_id"])) {
					$data["EvnLabRequest_id"] = $res[0]["EvnLabRequest_id"];
				}
				if (!empty($data["TimetableMedService_id"]) && $data["DirType_id"] == "10") {
					$resp = $this->recordTimetableMedService($data);
					if (!$this->isSuccessful($resp)) {
						return $resp;
					}
					if (!empty($data["EvnLabRequest_id"])) {
						$sql = "
							select TimetableMedService_begTime as \"TimetableMedService_begTime\"
							from v_TimetableMedService_lite
							where TimetableMedService_id = :TimetableMedService_id
							limit 1						
						";
						$sqlParams = ["TimetableMedService_id" => $data["TimetableMedService_id"]];
						$prmTime = $this->getFirstResultFromQuery($sql, $sqlParams);
						if (empty($prmTime)) {
							throw new Exception("Ошибка при получении времени записи");
						}
						if ($this->usePostgreLis) {
							$this->load->swapi("lis");
							$lisParams = [
								"EvnLabRequest_id" => $data["EvnLabRequest_id"],
								"EvnLabRequest_prmTime" => $prmTime
							];
							$resp = $this->lis->PATCH("EvnLabRequest/prmTime", $lisParams, "list");
						} else {
							$this->load->model("EvnLabRequest_model");
							$modelParams = [
								"EvnLabRequest_id" => $data["EvnLabRequest_id"],
								"EvnLabRequest_prmTime" => $prmTime
							];
							$resp = $this->EvnLabRequest_model->saveEvnLabRequestPrmTime($modelParams);
						}
						if (!$this->isSuccessful($resp)) {
							return $resp;
						}
					}
				}
				if (is_array($res) && count($res) > 0 && empty($res[0]["Error_Msg"])) {
					// отправка STOMP-сообщения
					sendFerStompMessage([
						"id" => $data[$data["object"] . "_id"],
						"timeTable" => $data["object"],
						"action" => "RecPatient",
						"setDate" => date("c")
					], "Rule");
				}
				// сохраняем заказ, если есть необходимость
				if ($this->usePostgreLis && !empty($data['DirType']) && $data["DirType_id"] == "10") {
					$this->load->swapi("lis");
					$order = $this->lis->POST("EvnUsluga/Order", $data, "single");
					if (!$this->isSuccessful($order)) {
						return [$order];
					}
				} else {
					$this->load->model("EvnUsluga_model", "eumodel");
					try {
						$order = $this->eumodel->saveUslugaOrder($data);
					} catch (Exception $e) {
						return [
							"success" => false,
							"Error_Msg" => $e->getMessage()
						];
					}
				}
				if (isset($order["EvnUsluga_id"])) {
					$data["EvnUsluga_id"] = $order["EvnUsluga_id"];
					$data["EvnUslugaPar_id"] = $order["EvnUsluga_id"];
				}
			}
		}
		if (!empty($data["PrescriptionType_Code"]) &&
			$data["PrescriptionType_Code"] == 11 &&
			!empty($res[0]["EvnDirection_id"])
		) {
			$UslugaComplexListByPrescr = $this->edmodel->getUslugaComplexByPrescrId($data["EvnPrescr_id"]);
			if (!is_array($UslugaComplexListByPrescr)) {
				return $this->createError("","Ошибка при получении услуг по назначению");
			}

			// значит нам туда дорога, зна-чит-нам-ту-да-до-ро-га (Лабораторная диагностика)
			$this->load->model("Queue_model", "Queue_model");
			$uslugaList = $this->Queue_model->getUslugaWithoutDirectoryList($data);

			$firstUslugaName = '';
			if(!empty($data['order'])){
				$uslugaFromData = json_decode($data['order']);
				$firstUslugaName = (!empty($uslugaFromData->UslugaComplex_Name)?$uslugaFromData->UslugaComplex_Name:'').'<br>';
			}

			if (!empty($uslugaList) && is_array($uslugaList) && count($uslugaList) > 0) {
				$this->load->model( 'EvnDirection_model', 'EvnDirection_model' );
				$msg = "Услуги: <br>{$firstUslugaName}";
				foreach($uslugaList as $usluga){
					$msg .= $usluga['UslugaComplex_Name'].'<br>';

					$params = array(
						'EvnPrescr_id' => $usluga['EvnPrescr_id'],
						'EvnDirection_id' => $data['EvnDirection_id'],
						'UslugaComplex_id' => $usluga['UslugaComplex_id'],
						'checked' => !empty($usluga['checked'])?(trim($usluga['checked'],',')):'',
						'pmUser_id' => $data['pmUser_id'],
						'Lpu_id' => $data['Lpu_id']
					);

					$resp = $this->EvnDirection_model->includeEvnPrescrInDirection($params);

					if (!$this->isSuccessful($resp)) {
						return $resp;
					}
				}
				$msg .= " были объединены в одно направление";
			}
		}
		if (!is_array($res)) {
			return [
				"success" => false,
				"Error_Msg" => "Ошибка записи."
			];
		}
		if (!empty($res[0]["Error_Msg"])) {
			return [
				"success" => false,
				"Error_Code" => isset($res[0]["Error_Code"]) ? $res[0]["Error_Code"] : null,
				"Error_Msg" => $res[0]["Error_Msg"],
			];
		}
		return [
			"success" => true,
			"object" => $data["object"],
			"id" => $data[$data["object"] . "_id"],
			"EvnDirection_id" => isset($res[0]["EvnDirection_id"]) ? $res[0]["EvnDirection_id"] : null,
			"EvnDirection_TalonCode" => isset($res[0]["EvnDirection_TalonCode"]) ? $res[0]["EvnDirection_TalonCode"] : null,
			"addingMsg" => (!empty($msg)) ? $msg : null
		];
	}

	/**
	 * @param $data
	 * @throws Exception
	 */
	function recordTimetableMedServiceOrgAuto($data)
	{
		$query = "
			select
				timetablemedserviceorg_id as \"TimetableMedServiceOrg_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Message\"
			from p_timetablemedserviceorg_upd(
			    timetablemedserviceorg_id := :TimetableMedServiceOrg_id,
			    org_id := :Org_id,
			    medservice_id := :MedService_id,
			    timetablemedserviceorg_day := :TimetableMedServiceOrg_Day,
			    timetablemedserviceorg_begtime := :Timetable_Date,
			    timetablemedserviceorg_time := :TimetableMedServiceOrg_Time,
				timetablemedserviceorg_facttime := :TimetableMedServiceOrg_factTime,
				pmuser_id := :pmUser_id
			);
		";
		if (!isset($data["TimetableMedServiceOrg_factTime"])) {
			$data["TimetableMedServiceOrg_factTime"] = new DateTime();
		}
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
	}

	/**
	 * @param $data
	 * @throws Exception
	 */
	function recordTimetableStacAuto($data)
	{
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Message\"
			from p_timetablestac_record(
			             timetablestac_id := :TimetableStac_id,
			             person_id := :Person_id,
			             recclass_id := 3,
			             evn_id := :Evn_id,
			             recmethodtype_id := 1,
			             pmuser_id := :pmUser_id
			         );
		";
		$queryParams = [
			"TimetableStac_id" => $data["TimetableStac_id"],
			"Evn_id" => $data["Evn_id"],
			"Person_id" => $data["Person_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
	}

	/**
	 * Запись на службу
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function recordTimetableMedService($data)
	{
		$query = "
			select
				evndirection_taloncode as \"EvnDirection_TalonCode\",
				error_code as \"Error_Code\",
				error_message as \"Error_Message\"
			from p_timetablemedservice_record(
			    timetablemedservice_id := :TimetableMedService_id,
			    person_id := :Person_id,
			    recclass_id := 1,
			    evn_id := :EvnDirection_pid,
			    evndirection_id := :EvnDirection_id,
			    timetablemedservice_isauto := :EvnDirection_IsAuto,
				pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"TimetableMedService_id" => $data["TimetableMedService_id"],
			"Person_id" => $data["Person_id"],
			"EvnDirection_pid" => $data["EvnDirection_pid"],
			"EvnDirection_id" => $data["EvnDirection_id"],
			"EvnDirection_IsAuto" => $data["EvnDirection_IsAuto"],
			"pmUser_id" => $data["pmUser_id"],
		];
		$result = $this->queryResult($query, $queryParams);
		if (!is_array($result)) {
			throw new Exception("Ошибка при записи на бирку");
		}
		return $result;
	}

	/**
	 * Подготовка к записи
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function recordEvnDirection($data)
	{
		$query = "
			update TimeTableMedService
			set Person_id = :Person_id
			   ,RecClass_id = :RecClass_id
			   ,Evn_id = :Evn_id
			   ,EvnDirection_id = :EvnDirection_id
			   ,RecMethodType_id = null
			   ,pmUser_updID = :pmUser_id
			   ,TimeTableMedService_updDT = tzgetdate()
			where TimeTableMedService_id = :TimetableMedService_id
		";
		$this->db->query($query, $data);
		$data['EvnDirection_pid'] = $data['Evn_id'];
		return $this->recordTimetableMedService($data);
	}

	/**
	 * Проверка, что бирка существует и свободна
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkTimetableFree($data)
	{
		$tt_data = $this->getRecordData($data);
		if (!isset($tt_data["{$data["object"]}_id"])) {
            return array(
                'success' => false,
                'Error_Msg' => 'Бирка с таким идентификатором не существует.'
            );
		}
		if (isset($tt_data["Person_id"])) {
            return array(
                'success' => false,
                'Error_Msg' => 'Выбранная вами бирка уже занята.'
            );
		}
		return true;
	}

	/**
	 * Проверка на запись на прошедшее время
	 * @param $tt_data
	 * @return bool
	 * @throws Exception
	 */
	function checkPastTimeRecord($tt_data)
	{
		if (!isset($tt_data["Timetable_Date"])) {
			return [
				"success" => false,
				"Error_Msg" => "Ошибка при получении даты бирки."
			];
		}
		$cur_date = new DateTime(date("d.m.Y H:i"));
		$check_date = new DateTime($tt_data["Timetable_Date"]);
		if ($check_date < $cur_date) {
			return [
				"success" => false,
				"Error_Msg" => "Вы не можете записать пациента на прошедшее время."
			];
		}
		return true;
	}

	/**
	 * Освобождение бирки
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function Clear($data)
	{
		$tt_data = null;
		if (true !== ($res = $this->checkTimetableOccupied($data))) {
			return $res;
		}
		if (true !== ($res = $this->checkHasRightsToClearRecord($data))) {
			return $res;
		}
		$this->beginTransaction();
		try {
			$queryParams = [
				$data["object"] . "_id" => $data[$data["object"] . "_id"],
				"pmUser_id" => $data["pmUser_id"],
			];
			if (!empty($data["TimetableGraf_id"])) {
				//смотрим есть ли связанный лист ожидания по бирке, со статусом ожидания подтверждения
				$sql = "
					select EvnQueue_id as \"EvnQueue_id\"
					from v_EvnQueue
					where EvnQueueStatus_id = 2
					  and TimetableGraf_id = :TimetableGraf_id
					  and RecMethodType_id = 1
					limit 1
				";
				$sqlParams = ["TimetableGraf_id" => $data["TimetableGraf_id"]];
				$EvnQueue_id = $this->getFirstResultFromQuery($sql, $sqlParams);
				if (!empty($EvnQueue_id)) {
					$data["dontCancelDirection"] = true;
				}
			}
			if (empty($data["dontCancelDirection"])) {
				if (!empty($data["EvnStatusCause_id"])) {
					// значит DirFailType_id вычисляем на основе EvnStatusCause_id
					$sql = "
						select escl.DirFailType_id as \"DirFailType_id\"
						from v_EvnStatusCauseLink escl
						where escl.EvnStatusCause_id = :EvnStatusCause_id
						limit 1
					";
					$sqlParams = ["EvnStatusCause_id" => $data["EvnStatusCause_id"]];
					$data["DirFailType_id"] = $this->getFirstResultFromQuery($sql, $sqlParams);
				}
				$sql = "
					select
						TMS.EvnDirection_id as \"EvnDirection_id\",
					    MST.MedServiceType_SysNick as \"MedServiceType_SysNick\"
					from v_TimetableMedService TMS
					     left join v_UslugaComplexMedService UCMS on UCMS.UslugaComplexMedService_id = TMS.UslugaComplexMedService_id
					     left join v_MedService MS on MS.MedService_id = coalesce(TMS.MedService_id, UCMS.MedService_id)
					     left join v_MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
					where TMS.TimetableMedService_id = :TimetableMedService_id
					limit 1
				";
				$data["TimetableMedService_id"] = empty($data["TimetableMedService_id"]) ? null : $data["TimetableMedService_id"];
				$resp = $this->getFirstRowFromQuery($sql, $data);
				if ($this->usePostgreLis && in_array($resp["MedServiceType_SysNick"], ["lab", "pzm"])) {
					$this->load->swapi("lis");
					$err = $this->lis->POST("EvnDirection/cancelByRecord", $data, "single");
				} else {
					$this->load->model("EvnDirection_model", "edmodel");
					$err = $this->edmodel->cancelEvnDirectionbyRecord($data);
				}

				if (!empty($err)) {
					$this->rollbackTransaction();
					return array(
						'success' => false,
						'Error_Msg' => $err
					);
				}
			}
			if ($data["object"] === "TimetableGraf" && !empty($data["TimetableGrafRecList_id"])) {
				$err = $this->cancelTimetableGrafRecList($data);
				if (!empty($err[0]) && !empty($err[0]["Error_Msg"])) {
					$this->rollbackTransaction();
					return [
						"success" => false,
						"Error_Msg" => $err[0]["Error_Msg"]
					];
				}
			}
			if ($data["object"] === "TimetableMedService") {
				$this->load->model("Mse_model", "msemodel");
				$err = $this->msemodel->cancelEvnPrescrbyRecord($data);
				if (!empty($err)) {
					$this->rollbackTransaction();
					return [
						"success" => false,
						"Error_Msg" => $err
					];
				}
			}
			$resp = $this->execCommonSP("p_" . $data["object"] . "_cancel", $queryParams);
			if (!$resp) {
				throw new Exception("Ошибки запроса к БД.");
			}
			if (count($resp) == 0 || !empty($resp[0]["Error_Msg"])) {
				throw new Exception($resp[0]["Error_Msg"]);
			}
			if (!empty($data["TimetableGraf_id"])) {
				// удаляем Источник записи
				$swUpdateParams = [
					"TimetableGraf_id" => $queryParams["TimetableGraf_id"],
					"pmUser_id" => $queryParams["pmUser_id"],
					"RecMethodType_id" => null
				];
				$tmp = $this->swUpdate("TimetableGraf", $swUpdateParams, true);
				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception("Ошибка запроса к БД при удалении источника записи бирки", 500);
				}
			}
			if (!empty($data["TimetableGraf_id"])) {
				$tt_data = $this->getRecordTTGData($data);
			}
			if (!empty($data["TimetableMedService_id"])) {
				$ttms_data = $this->getRecordTTMSData($data);
			}
			if ($data["object"] === "TimetableGraf" &&
				!empty($this->config->config["USER_PORTAL_IS_ALLOW_NOTIFY_ABOUT_RECORD_CANCEL"]) &&
				empty($data["EvnQueue_id"]) && empty($EvnQueue_id)
			) {
				// для бирки связанной листом ожидания не отправляем это уведомление
				$this->load->model("UserPortal_model");
				$this->UserPortal_model->notifyAboutRecordCancel($tt_data["Person_id"], $data["TimetableGraf_id"]);
			}
			if (!empty($EvnQueue_id) && !empty($tt_data)) {
				// Если бирка связана с листом ожидания в статусе ожидает подверждения то возвращаем его в пред. состояние
				$queueModelPParams = [
					"EvnQueue_id" => $EvnQueue_id,
					"Person_id" => $tt_data["Person_id"],
					"pmUser_id" => $data["pmUser_id"],
					"EvnQueueAction" => "clear"
				];
				$this->load->model("Queue_model");
				$this->Queue_model->getBackEvnQueue($queueModelPParams);
			}
			if (isset($tt_data["TimetableGraf_facttime"])) {
				// Удаление бирки если она была незапланированной
				$this->DeleteTTG($data);
			} else if (isset($ttms_data["TimetableMedService_facttime"])) {
				// Удаление бирки если она была незапланированной
				$this->Delete($data);
			}
			if ($data["object"] == "TimetableGraf" && !empty($data["TimetableGraf_id"])) {
				// если на бирке есть примечание "Интеграция с ФЭР", надо его удалить.
				$sql = "
					select TimetableExtend_id as \"TimetableExtend_id\"
					from v_TimetableExtend
					where TimetableGraf_id = :TimetableGraf_id
					  and pmUser_updID = 999900
					limit 1
				";
				$sqlParams = ["TimetableGraf_id" => $data["TimetableGraf_id"]];
				$resp_te = $this->queryResult($sql, $sqlParams);
				if (!empty($resp_te[0]["TimetableExtend_id"])) {
					$sql = "
						select
							error_code as \"Error_Code\",
							error_message as \"Error_Message\"
						from p_timetableextend_del(timetableextend_id := :TimetableExtend_id)
					";
					$sqlParams = ["TimetableExtend_id" => $resp_te[0]["TimetableExtend_id"]];
					$this->db->query($sql, $sqlParams);
				}
			}
			$this->commitTransaction();
			// отправка STOMP-сообщения
			sendFerStompMessage([
				"id" => $data[$data["object"] . "_id"],
				"timeTable" => $data["object"],
				"action" => "FreeTag_CancelDirect",
				"setDate" => date("c")
			], "Rule");
			return ["success" => true];
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return [
				"success" => false,
				"Error_Msg" => "Ошибка при освобождении бирки: " . $e->getMessage()
			];
		}
	}

	/**
	 * Временная блокировка бирки для записи
	 * Блокирует бирку поликлиники или стационара или службы
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function lock($data)
	{
		if (empty($data["TimetableGraf_id"]) &&
			empty($data["TimetableStac_id"]) &&
			empty($data["TimetableMedService_id"]) &&
			empty($data["TimetableResource_id"])
		) {
			return [["Error_Code"=>400, "Error_Msg"=>"Не указана бирка для блокировки"]];
		}
		$queryParams = [
			"pmUser_id" => $data["pmUser_id"],
			"TimetableGraf_id" => empty($data["TimetableGraf_id"]) ? null : $data["TimetableGraf_id"],
			"TimetableStac_id" => empty($data["TimetableStac_id"]) ? null : $data["TimetableStac_id"],
			"TimetableMedService_id" => empty($data["TimetableMedService_id"]) ? null : $data["TimetableMedService_id"],
			"TimetableResource_id" => empty($data["TimetableResource_id"]) ? null : $data["TimetableResource_id"],
		];
		if (($resp = $this->execCommonSP("p_TimetableLock_block", $queryParams))) {
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Снимает временную блокировку с бирки поликлиники или стационара или службы
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function unlock($data)
	{
		if (empty($data["TimetableGraf_id"]) &&
			empty($data["TimetableStac_id"]) &&
			empty($data["TimetableMedService_id"]) &&
			empty($data["TimetableResource_id"])
		) {
			return array(array('Error_Code'=>400, 'Error_Msg'=>'Не указана бирка для снятия блокировки'));
		}
		$queryParams = [
			"pmUser_id" => $data["pmUser_id"],
			"TimetableGraf_id" => empty($data["TimetableGraf_id"]) ? null : $data["TimetableGraf_id"],
			"TimetableStac_id" => empty($data["TimetableStac_id"]) ? null : $data["TimetableStac_id"],
			"TimetableMedService_id" => empty($data["TimetableMedService_id"]) ? null : $data["TimetableMedService_id"],
			"TimetableResource_id" => empty($data["TimetableResource_id"]) ? null : $data["TimetableResource_id"],
		];
		if (($resp = $this->execCommonSP("p_TimetableLock_unblock", $queryParams))) {
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Проверка, что бирка не из архивной даты
	 * @param $data
	 * @return bool
	 */
	function checkNotArchive($data)
	{
		$archive_database_date = $this->config->item("archive_database_date");
		$this->load->helper("Reg");
		switch ($data["object"]) {
			case "TimetableGraf":
				$sql = "
					select TimetableGraf_Day as \"Day\"
					from v_TimetableGraf_lite ttg
					where ttg.TimetableGraf_id = :TimetableGraf_id
				";
				$sqlParams = ["TimetableGraf_id" => $data["TimetableGraf_id"]];
				$res = $this->db->query($sql, $sqlParams);
				if (is_object($res)) {
					$resp = $res->result("array");
					if (!empty($resp[0]["Day"]) && DayMinuteToTime($resp[0]["Day"], 0) < strtotime($archive_database_date)) {
						return false;
					}
				}
				break;
			case "TimetableMedService":
				$sql = "
					select TimetableMedService_Day as \"Day\"
					from v_TimetableMedService_lite ttms
					where ttms.TimetableMedService_id = :TimetableMedService_id
				";
				$sqlParams = ["TimetableMedService_id" => $data["TimetableMedService_id"]];
				$res = $this->db->query($sql, $sqlParams);
				if (is_object($res)) {
					$resp = $res->result("array");
					if (!empty($resp[0]["Day"]) && DayMinuteToTime($resp[0]["Day"], 0) < strtotime($archive_database_date)) {
						return false;
					}
				}
				break;
			case "TimetableStac":
				$sql = "
					select TimetableStac_Day as \"Day\"
					from v_TimetableStac_lite tts
					where tts.TimetableStac_id = :TimetableStac_id
				";
				$sqlParams = ["TimetableStac_id" => $data["TimetableStac_id"]];
				$res = $this->db->query($sql, $sqlParams);
				if (is_object($res)) {
					$resp = $res->result("array");
					if (!empty($resp[0]["Day"]) && DayMinuteToTime($resp[0]["Day"], 0) < strtotime($archive_database_date)) {
						return false;
					}
				}
				break;
		}
		return true;
	}

	/**
	 * Проверка что человек не умер
	 * @param $data
	 * @return bool
	 */
	function checkPersonIsDeath($data)
	{
		$sql = "
			select ps.Person_id as \"Person_id\"
			from v_PersonState ps
			where ps.Person_id = :Person_id
			  and (ps.Person_IsDead = 2 or ps.Person_deadDT is not null)
			limit 1
		";
		$sqlParams = ["Person_id" => $data["Person_id"]];
		$person = $this->getFirstResultFromQuery($sql, $sqlParams);
		return !empty($person) ? true : false;
	}

	/**
	 * Проверка, чтобы у человека уже не было записи на то же время
	 * @param $data
	 * @return array|bool
	 */
	function checkAlreadyHasRecordOnThisTime($data)
	{
		switch ($data["object"]) {
			case "TimetableGraf":
				$sql = "
					select
						ttg.TimetableGraf_id as \"TimetableGraf_id\",
					    l.Lpu_Nick as \"Lpu_Nick\",
					    rtrim(msf.Person_Surname)||' '||msf.Person_Firname||coalesce(' '||msf.Person_Secname, '') as \"MedPersonal_FIO\"
					from v_TimetableGraf_lite ttg
					     inner join v_Medstafffact msf on ttg.MedStaffFact_id = msf.MedStaffFact_id
					     left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
					     left join v_LpuUnit_ER lu on lu.LpuUnit_id = ls.LpuUnit_id
					     left join v_Lpu l on l.Lpu_id = lu.Lpu_id
					where ttg.Person_id = :Person_id
					  and ttg.TimetableGraf_begtime = (
					      select TimetableGraf_begTime
					      from v_TimetableGraf_lite ttg1
					      where ttg1.TimetableGraf_id = :TimetableGraf_id
					      )
					order by ttg.TimetableGraf_begtime desc
				";
				$sqlParams = [
					"Person_id" => $data["Person_id"],
					"TimetableGraf_id" => $data["TimetableGraf_id"],
				];
				$res = $this->db->query($sql, $sqlParams);
				if (is_object($res)) {
					$res = $res->result("array");
					if (isset($res[0]["TimetableGraf_id"])) {
						return [
							"info" => "МО: " . $res[0]["Lpu_Nick"] . ", врач: " . $res[0]["MedPersonal_FIO"]
						];
					}
				}
				break;
			case "TimetableMedService":
				$sql = "
					select
						ttms.TimetableMedService_id as \"TimetableMedService_id\",
					    l.Lpu_Nick as \"Lpu_Nick\",
					    MedService_Name as \"MedService_Name\"
					from v_TimetableMedService_lite ttms
					     left join v_Person_ER p on ttms.Person_id = p.Person_id
					     left join v_MedService ms on ttms.MedService_id = ms.MedService_id
					     left join v_Lpu l on l.Lpu_id = ms.Lpu_id
					where ttms.Person_id = :Person_id
					  and ttms.TimetableMedService_begtime = (
					      select TimetableMedService_begTime
					      from v_TimetableMedService_lite ttms1
					      where ttms1.TimetableMedService_id = :TimetableMedService_id
					      )
					order by ttms.TimetableMedService_begtime desc
				";
				$sqlParams = [
					"Person_id" => $data["Person_id"],
					"TimetableMedService_id" => $data["TimetableMedService_id"],
				];
				$res = $this->db->query($sql, $sqlParams);
				if (is_object($res)) {
					$res = $res->result("array");
					if (isset($res[0]["TimetableMedService_id"])) {
						return [
							"info" => "МО: " . $res[0]["Lpu_Nick"] . ", служба: " . $res[0]["MedService_Name"]
						];
					}
				}
				break;
			case "TimetableStac":
				//TO-DO
				break;
		}
		return true;
	}

	/**
	 * Проверка на дополнительные предупреждения при записи в поликлинику
	 * @param $data
	 * @return array
	 */
	function checkWarningsTTG($data)
	{
		$warnings = [];
		$ttg_data = $this->getRecordTTGData($data);
		$params = ["Person_id" => $data["Person_id"]];
		$filter = "";
		if (!empty($data["EvnDirection_id"])) {
			// по тому же направлению не нужно выдавать предупреждений, чтобы сразу выполнялась перезапись.
			$params["EvnDirection_id"] = $data["EvnDirection_id"];
			$filter .= " and ttg.EvnDirection_id != :EvnDirection_id";
		}
		// А также информацию о всех существующих записях на человека
		$sql = "
			select
				l.Lpu_id as \"Lpu_id\",
			    ttg.TimetableGraf_Day as \"TimetableGraf_Day\",
			    msf.MedSpecOms_id as \"MedSpecOms_id\",
			    lsp.LpuSectionProfile_id as \"Profile_id\",
			    l.Lpu_Nick as \"Lpu_Nick\",
			    rtrim(msf.Person_Surname)||' '||left(msf.Person_Firname, 1)||'. '||left(msf.Person_Secname, 1)||'.' as \"MedPersonal_FIO\"
			from v_TimetableGraf_lite ttg
			     inner join v_Medstafffact msf on ttg.MedStaffFact_id = msf.MedStaffFact_id
			     left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			     left join v_LpuSectionProfile lsp on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			     left join v_LpuUnit_ER lu on lu.LpuUnit_id = ls.LpuUnit_id
			     left join v_Lpu l on l.Lpu_id = lu.Lpu_id
			where ttg.Person_id = :Person_id
			{$filter}
			order by ttg.TimetableGraf_begtime desc
		";
		$res = $this->db->query($sql, $params);
		if (is_object($res)) {
			$res = $res->result("array");
			// Предупреждение о записи одного человека на один день в одну МО, на один профиль.
			foreach ($res as $old_record) {
				if (
					$data["session"]["region"]["nick"] == "ekb" &&
					$old_record["Lpu_id"] == $ttg_data["Lpu_id"] &&
					$old_record["MedSpecOms_id"] == $ttg_data["MedSpecOms_id"] &&
					$old_record["TimetableGraf_Day"] == $ttg_data["TimetableGraf_Day"]
				) {
					$warnings[] = "Уже есть запись пациента на этот день по той же специальности в МО: " . $old_record["Lpu_Nick"] . ", врач: " . $old_record["MedPersonal_FIO"];
				} else if (
					$data["session"]["region"]["nick"] != "ekb" &&
					$old_record["Lpu_id"] == $ttg_data["Lpu_id"] &&
					$old_record["Profile_id"] == $ttg_data["LpuSectionProfile_id"] &&
					$old_record["TimetableGraf_Day"] == $ttg_data["TimetableGraf_Day"]
				) {
					$warnings[] = "Уже есть запись пациента на этот день по этому профилю в МО: " . $old_record["Lpu_Nick"] . ", врач: " . $old_record["MedPersonal_FIO"];
				}
			}
		}
		return $warnings;
	}

	/**
	 * Получение данных по бирке поликлиники
	 * @param $data
	 * @return bool|null
	 */
	function getRecordTTGData($data)
	{
		if (!isset($data["TimetableGraf_id"])) {
			return false;
		}
		if (empty($this->TTGData) || $this->TTGData["TimetableGraf_id"] != $data["TimetableGraf_id"]) {
			// Получаем информацию о бирке, куда записываемся
			$sqlParams = ["TimetableGraf_id" => $data["TimetableGraf_id"]];
			$res = $this->queryResult("
				select
					ttg.TimetableGraf_id as \"TimetableGraf_id\",
				    ttg.TimetableGraf_Day as \"TimetableGraf_Day\",
				    DATE_PART('day',ttg.TimetableGraf_begTime::timestamp - dbo.tzGetdate()::timestamp) as \"DateDiff\",
				    l.Lpu_id as \"Lpu_id\",
				    l.Org_id as \"Org_id\",
				    ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				    ls.LpuSection_id as \"LpuSection_id\",
				    ls.LpuUnit_id as \"LpuUnit_id\",
				    msf.MedStaffFact_id as \"MedStaffFact_id\",
				    msf.MedPersonal_id as \"MedPersonal_id\",
				    msf.MedSpecOms_id as \"MedSpecOms_id\",
				    msf.MedStaffFact_IsDirRec as \"MedStaffFact_IsDirRec\",
				    msf.Rectype_id as \"RecType_id\",
				    ttg.TimetableType_id as \"TimetableType_id\",
				    ttg.TimeTableGraf_PersRecLim as \"TimeTableGraf_PersRecLim\",
				    ttg.TimeTableGraf_countRec as \"TimeTableGraf_countRec\",
				    coalesce(ttg.TimetableGraf_begtime, ttg.TimetableGraf_facttime) as \"Timetable_Date\",
				    ttg.TimetableGraf_facttime as \"TimetableGraf_facttime\",
				    ttg.TimetableGraf_begTime as \"TimetableGraf_begTime\",
				    ttg.Person_id as \"Person_id\",
				    ev.EvnVizit_id as \"EvnVizit_id\"
				from v_TimetableGraf_lite ttg
				     left join v_Medstafffact msf on ttg.MedStaffFact_id = msf.MedStaffFact_id
				     left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				     left join v_LpuSectionProfile lsp on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
				     left join v_LpuUnit_ER lu on lu.LpuUnit_id = ls.LpuUnit_id
				     left join v_Lpu l on l.Lpu_id = lu.Lpu_id
				     left join v_EvnVizit ev on ev.TimetableGraf_id = ttg.TimetableGraf_id
				where ttg.TimetableGraf_id = :TimetableGraf_id
				order by ttg.TimetableGraf_begtime desc
			",$sqlParams);

			if (!isset($res[0])) {
				return false;
			}
			$this->TTGData = $res[0];
		}
		return $this->TTGData;
	}

	/**
	 * Получение данных по бирке
	 * @param $data
	 * @return array|false
	 */
	function getTimetableData($data)
	{
		$object = $data["object"];
		$selectArray = [
			"tt.{$object}_id as \"{$object}_id\"",
			"tt.{$object}_begTime as \"{$object}_begTime\"",
			"p.Person_Fio as \"Person_Fio\"",
			"to_char(p.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\"",
			"ed.EvnDirection_TalonCode as \"EvnDirection_TalonCode\"",
			"
				case when tt.Person_id is not null then
					case
						when pu.pmUser_id is not null then rtrim(pu.pmUser_Name)
						else 'Запись через интернет'
					end
				end as \"pmUser_Name\"
			"
		];
		$fromArray = [
			"{$object} as tt",
			"left join v_EvnDirection_all ed on ed.{$object}_id = tt.{$object}_id",
			"left join v_pmUser pu on pu.pmUser_id = tt.pmUser_updId",
			"
				left join lateral (
					select Person_Fio
						  ,Person_BirthDay
			        from v_Person_all
			        where Person_id = tt.Person_id
			        limit 1
			    ) as p on true
			",
		];
		$whereArray = [
			"tt.{$object}_id = :{$object}_id"
		];
		$selectString = implode(", ", $selectArray);
		$whereString = implode(" and ", $whereArray);
		$fromString = implode(" ", $fromArray);
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			limit 1
		";
		$sqlParams = [
			"{$object}_id" => $data["{$object}_id"]
		];
		$resp = $this->queryResult($sql, $sqlParams);
		if (!empty($resp[0])) {
			$resp = $resp[0];
			if (!empty($resp["{$object}_begTime"]) && $resp["{$object}_begTime"] instanceof DateTime) {
				/**@var DateTime $begTime */
				$begTime = $resp["{$object}_begTime"];
				$resp["{$object}_begTime"] = $begTime->format("d.m.Y H:i:s");
			}
		}
		return $resp;
	}

	/**
	 * Получение данных по бирке стационара
	 * @param $data
	 * @return bool|null
	 */
	function getRecordTTSData($data)
	{
		if (!isset($data["TimetableStac_id"])) {
			return false;
		}
		if (empty($this->TTSData) || $this->TTSData["TimetableStac_id"] != $data["TimetableStac_id"]) {
			// Получаем информацию о бирке, куда записываемся
			$sql = "
				select
					tts.TimetableStac_id as \"TimetableStac_id\",
				    tts.TimetableStac_Day as \"TimetableStac_Day\",
				    l.Lpu_id as \"Lpu_id\",
				    ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				    tts.TimetableType_id as \"TimetableType_id\",
				    cast(to_char(tts.TimetableStac_setDate, 'YYYYMMDD')||:time as timestamp)  as \"Timetable_Date\",
				    tts.Person_id as \"Person_id\",
				    l.Org_id as \"Org_id\",
				    lu.LpuUnit_id as \"LpuUnit_id\",
				    tts.EvnDirection_id as \"EvnDirection_id\"
				from v_TimetableStac_lite tts
				     left join v_Person_ER p on tts.Person_id = p.Person_id
				     left join v_LpuSection ls on ls.LpuSection_id = tts.LpuSection_id
				     left join v_LpuSectionProfile lsp on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
				     left join v_LpuUnit_ER lu on lu.LpuUnit_id = ls.LpuUnit_id
				     left join v_Lpu l on l.Lpu_id = lu.Lpu_id
				     left join Address on Address.Address_id = lu.Address_id
				     left join KLStreet on KLStreet.KLStreet_id = Address.KLStreet_id
				where tts.TimetableStac_id = :TimetableStac_id
				order by tts.TimetableStac_setDate desc
			";
			$sqlParams = [
				"TimetableStac_id" => $data["TimetableStac_id"],
				"time" => " 23:59:59.000"
			];
			$res = $this->db->query($sql, $sqlParams);
			$res = $res->result("array");
			if (!isset($res[0])) {
				return false;
			}
			$this->TTSData = $res[0];
		}
		return $this->TTSData;
	}

	/**
	 * Получение данных по бирке службы
	 * @param $data
	 * @return bool|null
	 */
	function getRecordTTMSData($data)
	{
		if (!isset($data["TimetableMedService_id"])) {
			return false;
		}
		if (empty($this->TTMSData) || $this->TTMSData["TimetableMedService_id"] != $data["TimetableMedService_id"]) {
			// Получаем информацию о бирке, куда записываемся
			$sql = "
				select
					ttms.TimetableMedService_id as \"TimetableMedService_id\",
				    ttms.TimetableMedService_Day as \"TimetableMedService_Day\",
				    l.Lpu_id as \"Lpu_id\",
				    ttms.TimetableType_id as \"TimetableType_id\",
					Coalesce(to_char(ttms.TimetableMedService_begTime, 'YYYYMMDD HH24:MI'),'') as \"TimetableMedService_DT\",
				    ttms.TimetableMedService_begTime as \"Timetable_Date\",
				    ttms.Person_id as \"Person_id\",
				    l.Org_id as \"Org_id\",
				    ttms.TimetableMedService_facttime as \"TimetableMedService_facttime\",
				    ms.LpuUnit_id as \"LpuUnit_id\",
				    ms.MedService_id as \"MedService_id\",
				    ms.MedService_Name as \"MedService_Name\",
				    mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
				    ucms.UslugaComplex_id as \"UslugaComplex_id\"
				from v_TimetableMedService_lite ttms
				     left join UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = ttms.UslugaComplexMedService_id
				     --left join v_Person_ER p on ttms.Person_id = p.Person_id
				     left join v_MedService ms on ms.MedService_id = coalesce(ucms.MedService_id, ttms.MedService_id)
				     left join v_MedServiceType mst on ms.MedServiceType_id = mst.MedServiceType_id
				     left join v_Lpu l on l.Lpu_id = ms.Lpu_id
				where ttms.TimetableMedService_id = :TimetableMedService_id
				order by ttms.TimetableMedService_begTime desc
			";
			$sqlParams = ["TimetableMedService_id" => $data["TimetableMedService_id"]];
			$res = $this->db->query($sql, $sqlParams);
			$res = $res->result("array");
			if (!isset($res[0])) {
				return false;
			}
			$this->TTMSData = $res[0];
		}
		return $this->TTMSData;
	}

	/**
	 * Получение данных по бирке службы
	 * @param $data
	 * @return bool|null
	 */
	function getRecordTTMSOData($data)
	{
		if (!isset($data["TimetableMedServiceOrg_id"])) {
			return false;
		}
		if (empty($this->TTMSOData) || $this->TTMSOData["TimetableMedServiceOrg_id"] != $data["TimetableMedServiceOrg_id"]) {
			// Получаем информацию о бирке, куда записываемся
			$sql = "
				select
					ttms.TimetableMedServiceOrg_id as \"TimetableMedServiceOrg_id\",
				    ttms.TimetableMedServiceOrg_Day as \"TimetableMedServiceOrg_Day\",
				    l.Lpu_id as \"Lpu_id\",
				    ttms.TimetableMedServiceOrg_begTime as \"Timetable_Date\",
				    ttms.TimetableMedServiceOrg_Time as \"TimetableMedServiceOrg_Time\",
				    ttms.MedService_id as \"MedService_id\",
				    ttms.Org_id as \"Org_id\",
				    ms.LpuUnit_id as \"LpuUnit_id\"
				from TimetableMedServiceOrg ttms
				     left join v_Org p on ttms.Org_id = p.Org_id
				     left join v_MedService ms on ms.MedService_id = ttms.MedService_id
				     left join v_Lpu l on l.Lpu_id = ms.Lpu_id
				where ttms.TimetableMedServiceOrg_id = :TimetableMedServiceOrg_id
				order by ttms.TimetableMedServiceOrg_begTime desc
			";
			$sqlParams = ["TimetableMedServiceOrg_id" => $data["TimetableMedServiceOrg_id"]];
			$res = $this->db->query($sql, $sqlParams);
			$res = $res->result("array");
			if (!isset($res[0])) {
				return false;
			}
			$this->TTMSOData = $res[0];
		}
		return $this->TTMSOData;
	}

	/**
	 * Получение данных по бирке ресурса
	 * @param $data
	 * @return bool|null
	 */
	function getRecordTTRData($data)
	{
		if (!isset($data["TimetableResource_id"])) {
			return false;
		}
		if (empty($this->TTRData) || $this->TTRData["TimetableResource_id"] != $data["TimetableResource_id"]) {
			// Получаем информацию о бирке, куда записываемся
			$sql = "
				select
					ttms.TimetableResource_id as \"TimetableResource_id\",
				    ttms.TimetableResource_Day as \"TimetableResource_Day\",
				    l.Lpu_id as \"Lpu_id\",
				    ttms.TimetableType_id as \"TimetableType_id\",
				    ttms.TimetableResource_begTime as \"Timetable_Date\",
				    ttms.Person_id as \"Person_id\",
				    l.Org_id as \"Org_id\",
				    ms.LpuUnit_id as \"LpuUnit_id\",
				    ms.MedService_id as \"MedService_id\",
				    ttms.Resource_id as \"Resource_id\"
				from v_TimetableResource_lite ttms
				     inner join Resource r on r.Resource_id = ttms.Resource_id
				     left join v_MedService ms on ms.MedService_id = r.MedService_id
				     left join v_Lpu l on l.Lpu_id = ms.Lpu_id
				where ttms.TimetableResource_id = :TimetableResource_id
				order by ttms.TimetableResource_begTime desc
			";
			$sqlParams = ["TimetableResource_id" => $data["TimetableResource_id"]];
			$res = $this->db->query($sql, $sqlParams);
			$res = $res->result("array");
			if (!isset($res[0])) {
				return false;
			}
			$this->TTRData = $res[0];
		}
		return $this->TTRData;
	}

	/**
	 * Получение данных по бирке, автоматически определяем тип бирки
	 * @param $data
	 * @return bool|null
	 */
	function getRecordData($data)
	{
		switch ($data["object"]) {
			case "TimetableGraf":
				return $this->getRecordTTGData($data);
				break;
			case "TimetableStac":
				return $this->getRecordTTSData($data);
				break;
			case "TimetableResource":
				return $this->getRecordTTRData($data);
				break;
			case "TimetableMedService":
				return $this->getRecordTTMSData($data);
				break;
			case "TimetableMedServiceOrg":
				return $this->getRecordTTMSOData($data);
				break;
		}
		return false;
	}

	/**
	 * Проверка, что на данную поликлиническую бирку в чужую МО разрешена запись
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkRecordTTGOtherLpu($data)
	{
		$ttg_data = $this->getRecordTTGData($data);
		$current_day = new Datetime(date("Y-m-d"));
		$Timetable_Date = DateTime::createFromFormat('Y-m-d', $ttg_data["Timetable_Date"]);
		$day_diff = $current_day->diff($Timetable_Date)->days;
		return
			(
				(in_array($ttg_data["TimetableType_id"], [1, 5])) ||
				(in_array($ttg_data["TimetableType_id"], [4]) && IsCZUser())
			) &&
			(
				($day_diff > 1) || //запись на послезавтра или в пределах ближайших max_day дней
				($day_diff == 1 && date("H:i") < getCloseNextDayRecordTime()) //запись на завтра, но до getCloseNextDayRecordTime() часов
			);
	}

	/**
	 * Проверка времени записи перед блокировкой бирки
	 * Если добавляемое назначение имеет разницу по времени менее 15 минут
	 * бирки с каким-либо уже имеющимся в списке справа,
	 * выдавать предупреждение "Существует назначение, близкое по времени записи к создаваемому."
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkBeforeLock($data)
	{
		$sql = "
			select ttms.TimetableMedService_id as \"TimetableMedService_id\"
			from v_TimetableMedService_lite ttms
			where ttms.TimetableMedService_Day = (
			        select TimetableMedService_Day
			        from v_TimetableMedService_lite
			        where TimetableMedService_id = :TimetableMedService_id
			    )
			  and ttms.TimetableMedService_begTime between (
			        select TimetableMedService_begTime - (14 ||' minutes')::interval
			        from v_TimetableMedService_lite
			        where TimetableMedService_id = :TimetableMedService_id
			    ) and (
			        select TimetableMedService_begTime + (14 ||' minutes')::interval
			        from v_TimetableMedService_lite
			        where TimetableMedService_id = :TimetableMedService_id
			    )
			  and ttms.Person_id = :Person_id
		";
		$queryParams = [
			"TimetableMedService_id" => $data["TimetableMedService_id"],
			"Person_id" => $data["Person_id"],
		];
		$res = $this->db->query($sql, $queryParams);
		$response = [["Error_Msg" => null, "Error_Code" => null]];
		if (!is_object($res)) {
			throw new Exception("Ошибка запроса к БД", 500);
		}
		$res = $res->result("array");
		if (count($res) > 0) {
			$response[0]["Alert_Msg"] = "Существует назначение, близкое по времени записи к создаваемому";
		}
		return $response;
	}

	/**
	 * Снимает временную блокировку с бирок заблокированных переданным пользователем
	 * @param $data
	 * @return bool|mixed
	 * @throws Exception
	 */
	function unlockByUser($data)
	{
		$queryParams = ["pmUser_id" => $data["pmUser_id"]];
		if (($resp = $this->execCommonSP("p_TimetableLock_unblockByUser", $queryParams))) {
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Снимает временную блокировку с бирок заблокированных переданным пользователем
	 * @param $data
	 * @return array
	 */
	function getApplyDataForApi($data)
	{
		$query = "
			select
				ttg.TimetableGraf_id as \"TimetableGraf_id\",
			    ttg.MedStaffFact_id as \"MedStaffFact_id\",
			    ttg.TimetableGraf_begTime as \"time\",
			    cast(ttg.TimetableGraf_begTime as date) as \"EvnDirection_setDate\",
			    msf.Lpu_id as \"Lpu_did\",
			    msf.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			    msf.LpuUnit_id as \"LpuUnit_did\",
			    msf.LpuSection_id as \"LpuSection_did\",
			    msf.MedPersonal_id as \"MedPersonal_did\"
			from v_TimetableGraf_lite ttg
			     left join v_MedStaffFact msf on msf.MedStaffFact_id = ttg.MedStaffFact_id
			where TimetableGraf_id = :TimetableGraf_id
			limit 1
		";
		$res = $this->queryResult($query, $data);
		if (!empty($res) && !empty($res[0])) {
			return $res[0];
		} else {
			return [];
		}
	}

	/**
	 * запись на бирку универсальная для апи
	 * перенес доп. логику из контролллера в единую модель
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function applyForApi($data)
	{
		$this->load->helper("Reg");
		$data["Day"] = TimeToDay(time());
		$data["object"] = $data["tt"];
		// Проверка наличия блокирующего примечания
		$this->load->model("Annotation_model", "anmodel");
		$anncheck = $this->anmodel->checkBlockAnnotation($data);
		if (is_array($anncheck) && count($anncheck)) {
			throw new Exception("Запись на бирку невозможна. См. примечание.", 6);
		}
		$this->beginTransaction();
		try {
			$apply_result = $this->Apply($data);

			if (isset($apply_result[0])) {
				$apply_result = $apply_result[0];
			}

			if (isset($apply_result["success"]) && $apply_result["success"]) {
				$data["EvnDirection_id"] = $apply_result["EvnDirection_id"];
				$resp = [
					"object" => $apply_result["object"],
					"id" => $apply_result["id"],
					"EvnDirection_id" => $apply_result["EvnDirection_id"],
					"EvnDirection_TalonCode" => !empty($apply_result["EvnDirection_TalonCode"]) ? $apply_result["EvnDirection_TalonCode"] : null
				];
				// сохраняем заказ, если есть необходимость
				if (empty($data["redirectEvnDirection"])) {
					$this->load->model("EvnUsluga_model", "eumodel");
					try {
						$this->eumodel->saveUslugaOrder($data);
					} catch (Exception $e) {
						throw new Exception(toUTF($e->getMessage(), 6));
					}
				}
				if ($data["object"] == "TimetableResource") {
					$this->load->model("Resource_model", "resmodel");
					// Отправка данных направлений с типом функциональная диагностика в сторонние сервисы
					$this->resmodel->transferDirection($data, $resp);
				}
				$this->commitTransaction();
			} elseif (!empty($apply_result["queue"])) {
				array_walk($apply_result["queue"], "ConvertFromWin1251ToUTF8");
				throw new Exception(array($apply_result["queue"]), 6);
			} elseif (!empty($apply_result["warning"])) {
				throw new Exception(toUTF($apply_result["warning"]), 777);
			} elseif (!empty($apply_result["alreadyHasRecordOnThisTime"])) {
				throw new Exception(toUTF($apply_result["alreadyHasRecordOnThisTime"]), 6);
			} else {
				throw new Exception(toUTF($apply_result["Error_Msg"]), 6);
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			throw new Exception($e->getMessage(), $e->getCode());
		}
		return $resp;
	}

	/**
	 * запись на бирку универсальная для апи
	 * перенес доп. логику из контролллера в единую модель
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function ApplyPortal($data) {
		$result = null;
		$tt_data = null;
		$data["object"] = "TimetableGraf";
		$this->load->helper("Reg");
		$timetable = $this->getRecordTTGData($data);
		$StandardValidation = $this->StandardApplyValidations($timetable, $data);
		if ($StandardValidation['success'] == false) {
			return $StandardValidation;
		}

		$BegTimeApplyValidations = $this->BegTimeApplyValidations($timetable, $data);
		if ($BegTimeApplyValidations['success'] == false) {
			return $BegTimeApplyValidations;
		}
		$this->load->model('Person_model');
		$patient = $this->Person_model->getPersonMain($data);
		$ApplyValidateRegional = $this->ApplyValidateRegional(array(
			'patient' => $patient,
			'data' => $data,
			'object' => $timetable
		));
		if ($ApplyValidateRegional['success'] == false) {
			return $ApplyValidateRegional;
		}
		$recordData = array(
			'Person_id' => $data['Person_id'],
			'TimetableGraf_id' => $data['TimetableGraf_id'],
			'pmuser_id' => $data['pmuser_id'],
			'isElectronicQueue' => false,
			'autoModeration' => true,
			'record_platform' => 1
		);

		$setResult = $this->setRecord($recordData); // занимаем бирку

		if (!empty($setResult)) {
			if (!empty($setResult['Error_Code'])) {
				$result['success'] = false;
				$result['error'] = !empty($setResult['Error_Msg']) ? $setResult['Error_Msg'] : 'Ошибка записи на бирку (код ' . $setResult['Error_Msg'] . ')';
				return $result;
			} else {
				$result['success'] = true;
				return $result;
			}
		}
	}

	/**
	 * Разделенные проверки времени записи
	 */
	function BegTimeApplyValidations($timetable, $data)
	{
		$result = array();
		$result['success'] = false;
		$result['error'] = array();
		$this->load->helper("Reg");

		$now = new DateTime();
		$max_day = $this->getRecordDayCount();
		//return $max_day;

		if (!empty($data['object'])) {
			$object = $data['object'];
		} else {
			$object = 'TimetableGraf';
		}

		// разрешенные типы пользователей бирок

		// 8 - интернет пользователи
		// 9 - пользователи инфомата
		// 4 - регистраторы своей МО

		$ttg_allowed_types = array(1,2,3,8,9);

		// если есть полномочия регистратора, добавляем их
		if (!empty($data['registerPermissionEnabled'])) {
			$ttg_allowed_types[] = 4;
		}

		$time = date('H:i', strtotime($timetable["{$object}_begTime"]));// время
		// тип бирки (обычная, резервная, платная)
		loadLibrary('TTimetableTypes');
		$TimetableType = TTimetableTypes::instance()->getTimetableType($timetable['TimetableType_id']);

		//95. если айди персоны пуст (дубликат, нет?)
		if ($timetable['Person_id'] == "") {
			/*
			96. и если запись на этот тип бирки разрешен для
			Для врачей своей ЛПУ
			Запись через инфомат
			(для АРМ Холла) Регистратор своей МО
			*/
			if (in_array($timetable['TimetableType_id'], $ttg_allowed_types)) {
				/*
				97. и если тип записи к врачу НЕ:
				Только через регистратуру ЛПУ
				Прием по "живой очереди"
				Врачи ведущие только платный приём
				Без записи
				8 уже давно выпилили из базы
				*/
				if (!in_array($timetable['RecType_id'], array(2, 3, 5, 6, 8))) {
					$err = '';

					if ($timetable['DateDiff'] > $max_day) {
						$err .= "Запись на выбранный день еще не открыта. Она откроется в ближайшее время.";
					}

					if ($timetable['DateDiff'] == 1 && $now->format('H:i') > getCloseNextDayRecordTime()) {
						$err .= "Запись на завтра уже закрыта. Запишитесь на другой день.";
					}

					if ($timetable['DateDiff'] == $max_day && $now->format('H:i') < getShowNewDayTime()) {
						$err .= "Запись на выбранный день еще не открыта. Попробуйте записаться позднее.";
					}

					if (
						!empty($data['allow_record_today']) &&
						(
						($timetable['DateDiff'] == 0 && date('H:i', strtotime('+15 minutes')) > $time)
						)
					) {
						$err .= "До времени приема осталось меньше 15 минут. Запишитесь на более позднее время.";
					}
				} else {
					$recType_Name = $this->GetRecTypeMethod($timetable);
					$FirstlowerCase = mb_strtolower(mb_substr($recType_Name, 0, 1));
					$result['success'] = false;
					$result['error'] = 'Тип записи к данному врачу: ' .$FirstlowerCase.mb_substr($recType_Name, 1);
					return $result;
				}
			} else {
				$TimetableTypeName = $TimetableType->name;
				$FirstlowerCase = mb_strtolower(mb_substr($TimetableTypeName, 0, 1));
				$result['success'] = false;
				$result['error'] = 'Тип записи на данную бирку: .'.$FirstlowerCase.mb_substr($TimetableTypeName, 1);
				return $result;
			}
		} else {
			$result['success'] = false;
			$result['error'] = 'Данная бирка уже занята. Пожалуйста, попробуйте записаться на другое время.';
			return $result;
		}

		if (!empty($err)) {
			$result['success'] = false;
			$result['error'] = $err;
		} else {
			$result['success'] = true;
		}
		return $result;
	}

	/**
	 * Основные проверки при записи на бирку
	 */
	function StandardApplyValidations($timetable, $data) {
		//return $timetable;
		//return $data;
		$result = array();
		$result['success'] = false;
		$result['error'] = array();
		if (empty($timetable)) { // бирка не существует
			$result['error'] = "Время не найдено. Выберите другое время.";
			return $result;
		}
		if (!empty($timetable['Person_id'])) { // на эту бирку уже записались
			$result['error'] = "Время уже занято. Выберите другое время.";
			return $result;
		}
		$this->load->model("Annotation_model");
		// комментарий блокирует бирку
		$blockedAnnotation = $this->Annotation_model->checkBlockAnnotation($data);


		// комментарий блокирует бирку
		if (!empty($blockedAnnotation)) {
			$result['error'] = "Запись на выбранную бирку невозможна. Причина:" . '<br> ' . $blockedAnnotation['Annotation_Comment'];
			return $result;
		}

		$result['success'] = true;

		return $result;
	}

	/**
	 * Комплекс региональных проверок при записи на прием
	 */
	function ApplyValidateRegional($data) {

		$patient = $data['patient'];
		$object = $data['object'];
		$result = array();
		$result['success'] = false;
		$result['error'] = "Запись не прошла региональную проверку";
		//Запись не прошла региональную проверку
		$flag_only_false = true; // Все проверки отключены, значит можно записаться

		// Модель для работы с пациентом
		$this->load->model('Person_model');
		/* @var $person_model Model_Common_Person */

		$restriction_configs = json_decode($data['data']['restriction_configs'], true);
		//return $restriction_configs;

		//Если нет конфигов совсем, значит и ограничений никаких нет, расходимся
		if(!isset($restriction_configs) || !$restriction_configs)
		{
			$result['success'] = true;
			$result['error'] = null;
			return $result;
		}
		$data['patient']['attach_type'] = 1;
		$patient['attach'] = $this->Person_model->getPersonAttach($data['patient']);

		foreach($restriction_configs['restriction_configs'] as $restriction){
			if(empty($restriction['value']))
				continue;

			$flag_only_false = false; // Если все проверки отключены, до этого места не доберутся

			switch($restriction['code']){
				case 'attaching_point':
					if(isset($restriction['profiles']['value'])
						&& $restriction['profiles']['value']
						&& is_array($restriction['profiles']['profiles_array'])
						&& count($restriction['profiles']['profiles_array']) > 0)
					{
						// 1.1 пункт
						// Проверка на профили, к которым разрешена запись независимо от прикрепления
						if(in_array($object['LpuSectionProfile_id'],$restriction['profiles']['profiles_array'])){
							$result['success'] = true;
							$result['error'] = null;
							return $result;
						}
						$result['error'] = "По данному профилю необходимо обратиться в МО прикрепления";
					}
					if(isset($restriction['patient_without_attaching']['value'])){
						// 1.2 пункт
						// Проверка на запись для неприкрепленного населения
						if($restriction['patient_without_attaching']['value']
							&& !(isset($patient['attach']['Lpu_id']) && $patient['attach']['Lpu_id']))
						{
							$result['success'] = true;
							$result['error'] = null;
							return $result;
						}
						elseif(!(isset($patient['attach']['Lpu_id']) && $patient['attach']['Lpu_id']))
							$result['error'] = "Запись без актуального прикрепления недоступна";
						//Запись без актуального прикрепления недоступна
					}
					if(isset($restriction['mo_without_attaching']['value'])
						&& $restriction['mo_without_attaching']['value'])
					{
						// 1.4 пункт
						// Проверка на запись в МО без прикрепленного населения
						if(!($this->isAssignNasel($object['Lpu_id']))){
							// не проставлен чекбокс "МО имеет приписное население"
							$result['success'] = true;
							$result['error'] = null;
							return $result;
						}
					}

					if (isset($patient['attach']['Lpu_id']) && $patient['attach']['Lpu_id'] == $object['Lpu_id']){
						$result['success'] = true;
						$result['error'] = null;
						return $result;
					}
					else
						$result['error'] = "Запись невозможна, т.к. Вы должны быть прикреплены к данному МО";
					//Запись невозможна, т.к. Вы должны быть прикреплены к данному МО.

					break;
				case 'mo_service_area':
					// 2 пункт
					// Проверка, что выбранная больница обслуживает территорию проживания человека
					if ( isset($patient['attach']['Terr_id']) ) {
						$this->load->model('Lpu_model');
						if ($this->Lpu_model->TerrInLpuServiceTerr($patient['attach']['Terr_id'], $object['Lpu_id'])){
							$result['success'] = true;
							$result['error'] = null;
							return $result;
						}
						else
							$result['error'] = "Выбранная МО не обслуживает адрес проживания человека";
					}
					break;
				case 'banned_profiles_list':
					// 3 пункт
					// Проверка на профиль, к которому запрещена запись (безусловно)
					// Грузим названия профилей, если нет в региональных берется стандартное
					// Если профиль есть и в стандратной и в региональной, его название берется из регионального конфига
					$profiles_name = $restriction_configs['profiles_list'];

					if(in_array($object['LpuSectionProfile_id'],$restriction['profiles_array']))
					{
						$profile_name = (isset($profiles_name[$object['LpuSectionProfile_id']]))?$profiles_name[$object['LpuSectionProfile_id']]:'';
						$result['error'] = "Запись на профиль .$profiles_name. ограничена";
						//Запись на профиль $profile_name ограничена
						return $result;
					}
					break;
			}
		}
		if($flag_only_false){ // если все условия были отключены, значит ни одной проверки не работает, можно записывать
			$result['success'] = true;
			$result['error'] = null;
		}

		return $result;
	}

	/**
	 * Проверка на проставленный чекбокс в Паспорте МО (вкладка "Справочная информация")
	 * "МО имеет приписное население"
	 * @param int $lpu_id идентификатор ЛПУ
	 */
	function isAssignNasel($lpu_id){
		$isAssignNasel = false;
		$result = $this->getFirstRowFromQuery("
						select
							Lpu_id  as \"Lpu_id\",
							PasportMO_IsAssignNasel as \"PasportMO_IsAssignNasel\"
						from
							fed.v_PasportMO
						where
							Lpu_id = :Lpu_id
						order by PasportMO_id desc",array('Lpu_id' => $lpu_id)
		);
			// Если чекбокс "МО имеет приписное население" в паспорте МО проставлен
		if(isset($result['PasportMO_IsAssignNasel']) && $result['PasportMO_IsAssignNasel'] == 2) {
			$isAssignNasel = true;
		}
		return $isAssignNasel;
	}

	/**
	 * Получение количества дней, на сколько вперед разрешена запись
	 */
	public function getRecordDayCount() {
		$result = $this->getFirstRowFromQuery("select dbo.PolRecordDayCount() as \"RecordDayCount\"");
		if (count($result) > 0 ) {
			return $result['RecordDayCount'];
		} else {
			return false;
		}
	}


	/**
	 * Динамическая подстановка типа записи
	 */
	function GetRecTypeMethod($timetable) {
		$params = array(
			'RecType_id' => $timetable['RecType_id'],
		);

		$result = $this->getFirstRowFromQuery("
            select RecType_Name as \"RecType_Name\"
            from v_RecType
            where RecType_id = :RecType_id
        ",$params);

		return $result;
	}

	function isLocked($data) {

		$lockedRecords = $this->getLockedRecords($data);
		return (isset($lockedRecords[$data['Timetablegraf_id']]) && ($lockedRecords[$data['record_id']] != $data['user_id']));
	}
	/**
	 * получить заблоированные бирки
	 */
	function getLockedRecords($data) {

		$object = $data['object'];

		return $this->queryResult("
            select
                {$object}_id,
                pmUser_insID
            from TimetableLock
        ");
	}

	/**
	 * Создание расписания для вывода на страницу записи
	 *
	 * @param int $doctor_id Идентификатор врача
	 * @param boolean $allow_record_today Разрешаем запись на сегодня или на завтра после времени закрытия записи или на понедельник после времени закрытия записи в пятницу
	 * @param object $data сюда будем писать параметры
	 */
	public function MakeTimetable($doctor_id, $allow_record_today = false, $data = NULL) {

		// Начальная дата
		$start_date = time();

		// Модель для работы с пациентом
		$this->load->model('Person_model');
		$this->load->model('MedStaffFact_model');
		$this->load->helper("Reg");

		// Врач
		$doctor = $this->MedStaffFact_model->getDoctorInfo($doctor_id);

		$block_timetable = false;

		//todo пока на платные бирки очереди не будет
//		$this->load->model('EvnQueue_model');
//
//		// ставим блокировку записи на бирки
//		$block_timetable = $this->EvnQueue_model->isTimetableBlocked(
//			(object)array(
//				'MedStaffFact_id' => $doctor_id,
//				'Lpu_id' => $doctor['Lpu_id'],
//				'LpuSectionProfile_id' => $doctor['LpuSectionProfile_id']
//			));

		// Максимальный возможный день и неделя, максимальное время, с запасом для отображения
		$max_day = $this->getRecordDayCount();
		$end_date = strtotime('+' . (floor(($max_day+16) / 16)*16) . ' days', $start_date);
		// Получаем расписание из базы
		$timetable_items = $this->getTimetable($doctor_id, $start_date, $end_date);
		$msfDayDescriptions = array();
		$msfTtgDescriptions = array();

		$this->load->model('Annotation_model');
		// Получаем комменты на расписание
		$annotations = $this->Annotation_model->getDoctorAnnotationsByPeriod(array(
			'doctor_id' => $doctor_id,
			'start_date' => $start_date,
			'end_date' => $end_date
		));

		// обрабатываем их немного
		foreach ($annotations as $annotation) {

			if (!empty($annotation['Annotation_begDate'])) {

				// если не указаны поля времени начала и окончания, значит это комменты на день
				if (empty($annotation['Annotation_begTime']) && empty($annotation['Annotation_endTime'])) {

					// группируем по дню только коммент
					if (!empty($msfDayDescriptions[$annotation['day_id']]))
						$msfDayDescriptions[$annotation['day_id']] .= '<br>'.$annotation['Annotation_Comment'];
					else
						$msfDayDescriptions[$annotation['day_id']] = $annotation['Annotation_Comment'];
				} else {
					// иначе это комменты на бирку
					$annBegTime = (!empty($annotation['Annotation_begTime'])  ?
						DateTime::createFromFormat('H:i:s', $annotation['Annotation_begTime'])->format('H:i:s') :
						DateTime::createFromFormat('Y-m-d',$annotation['Annotation_begDate'])->format('H:i:s'));
					$annEndTime = (!empty($annotation['Annotation_endTime'])  ?
						DateTime::createFromFormat('H:i:s', $annotation['Annotation_endTime'])->format('H:i:s') :
						(!empty($annotation['Annotation_endDate']) ? DateTime::createFromFormat('Y-m-d',$annotation['Annotation_endDate'])->format('H:i:s') : '00:00:00'));
					// формируем здесь адекватную дату начала в формате Y-m-d H:i:s
					// т.к. поле $annotation->Annotation_begTime хранит в себе неверную дату

					$realBegDatetime =  DateTime::createFromFormat('Y-m-d', $annotation['Annotation_begDate'])->format('Y-m-d').' '.$annBegTime;
					$annotation['realBegDatetime'] = $realBegDatetime;

					$annotation['realBegTime'] = $annBegTime;

					// формируем здесь адекватную дату окончания аннотации в формате Y-m-d H:i:s
					// т.к. поле $annotation->Annotation_endTime хранит в себе неверную дату

					if (!empty($annotation['Annotation_endDate'])) {

						$realEndDatetime =  DateTime::createFromFormat('Y-m-d', $annotation['Annotation_endDate'])->format('Y-m-d').' '.$annEndTime;
					} else {
						// бывает такое (очень редко) что дату окончания не указывают
						// тогда мы будем считать этот коммент бессрочным
						// будем продлевать дату окончания постоянно на две недели вперед от даты начала
						$realEndDatetime =  DateTime::createFromFormat('Y-m-d', $annotation['Annotation_begDate'])->add(new DateInterval('P14D'))->format('Y-m-d').' '.$annBegTime;

					}
					$annotation['realEndDatetime'] = $realEndDatetime;
					$annotation['realEndTime'] = $annEndTime;

					// группируем по дню весь объект
					$msfTtgDescriptions[$annotation['day_id']][] = $annotation;
				}
			}
		}

		// Массив для хранения расписания по дням
		$timetable_days_raw = array();
		foreach ($timetable_items as $timetable_item) {
			$timetable_days_raw[$timetable_item['TimeTableGraf_Day']][] = $timetable_item;
		}
		$days_range = range(TimeToDay($start_date), TimeToDay($end_date)-1);

		$showNewDayTime = getShowNewDayTime();
		$closeNextDayRecordTime = getCloseNextDayRecordTime();

		$this->load->library('calendar');
		$timetable_days = array_values($timetable_days_raw);

		$cur_date = new Datetime();
		// Последняя дата в расписании доступная для записи
		$fin_date =  strtotime($cur_date->format('Y-m-d').'+' . $max_day . ' days');
		foreach ($timetable_days as &$timetable_day) {
			foreach ($timetable_day as &$timetable_item) {
				$timetable_item['time'] = DateTime::createFromFormat('Y-m-d H:i:s',$timetable_item['TimetableGraf_begTime'])->format('H:i');
				$timetable_item['date'] = DateTime::createFromFormat('Y-m-d H:i:s',$timetable_item['TimetableGraf_begTime'])->format('Y-m-d');
				$seldate = strtotime($timetable_item['date']);

				// Читаемая дата, время
				$dt = DateTime::createFromFormat('Y-m-d H:i:s',$timetable_item['TimetableGraf_begTime']);

				$timetable_item['datetime_readable_desc'] = $this->calendar->get_day_names()[$dt->format('w')] . $dt->format(', j ') . $this->calendar->get_month_name($dt->format('m'));

				$timetable_item['datetime_readable'] = $dt->format('j ') .
					$this->calendar->get_month_name($dt->format('m')) . ', ' .
					$this->calendar->get_day_names()[$dt->format('w')] . ' ' .
					$dt->format('H:i');

				// Классы стилей для вывода ячеек расписания
				$class = '';
				$tooltip = '';

				$timetable_item['is_free'] = false;
				$timetable_item['my_record'] = false;
				$timetable_item['annot'] = false;

				// если в хранилище есть комменты на этот день
				if (!empty($msfTtgDescriptions[$timetable_item['TimeTableGraf_Day']])) {

					// то для каждой аннотации на этот день смотрим
					foreach ($msfTtgDescriptions[$timetable_item['TimeTableGraf_Day']] as $annotation) {

						// и если бирка входит в промежуток времени на коммент
						if (
							$timetable_item['TimetableGraf_begTime'] >= $annotation['realBegDatetime']
							&& $timetable_item['TimetableGraf_begTime'] <= $annotation['realEndDatetime']
							&& $timetable_item['time'].":00" >= $annotation['realBegTime']
							&& $timetable_item['time'].":00" <= $annotation['realEndTime']
						) {

							// добавляем этот коммент к бирке
							if (!empty($timetable_item['annot']))
								$timetable_item['annot'] .= '<br>'.$annotation['Annotation_Comment'];
							else
								$timetable_item['annot'] = $annotation['Annotation_Comment'];
						}
					}
				}

				$timetable_item['MedStaffFactDay_Descr'] = (!empty($msfDayDescriptions[$timetable_item['TimeTableGraf_Day']]) ? $msfDayDescriptions[$timetable_item['TimeTableGraf_Day']] : '');

				if ($seldate == $fin_date && $cur_date->format('H:i') < $showNewDayTime) {
					// Если мы рассматриваем день равный последней доступной дате для записи и время меньше времени закрытия записи, то сообщаем, что скоро можно будет записаться
					$class .= 'miss' ;
					$tooltip = "Запись будет возможна сегодня после $showNewDayTime";
				} elseif ($timetable_item['DateDiff'] > $max_day || $seldate > $fin_date) {
					// Запись возможна только на max_day дней вперед
					$class .= ' miss grayCell';
					$tooltip = "Запись возможна только на $max_day дней вперед";
				} elseif ($timetable_item['DateDiff'] == 0 && ($allow_record_today && $timetable_item['time'] <= date('H:i', strtotime('+15 minutes')))) {
					// Запись на текущее время невозможна
					$class .= ' miss';
					$tooltip = "Время истекло. Запись недоступна";
				}
				elseif (($timetable_item['DateDiff'] == 0 && !$allow_record_today) || (isset($allow_record_today) && $timetable_item['IsFuture'] == 0)) {
					// Запись на текущий день невозможна
					$class .= ' miss';
					$tooltip = "Запись на текущий день невозможна";

				}  elseif ($timetable_item['DateDiff'] == 1 && $cur_date->format('H:i') > $closeNextDayRecordTime && !$allow_record_today) {
					// Запись на следующий день возможна только до Utils::getCloseNextDayRecordTime() текущего дня
					$class .= ' miss';
					$tooltip = "Запись на следующий день возможна только до $closeNextDayRecordTime текущего дня" ;
				} elseif ($timetable_item['DateDiff'] < 0) {
					// Дата посещения прошла
					$class .= ' miss';
					$tooltip = "Дата посещения прошла";
				} elseif (!$allow_record_today && ((IsWorkDay($cur_date)
							&& $cur_date->format('H:i') > $closeNextDayRecordTime)
						|| !IsWorkDay($cur_date)
					) && $timetable_item['TimetableGraf_begTime']->format('Y-m-d') == NextWorkDay($cur_date)->format('Y-m-d'))
				{
					// Запись на понедельник возможна только до Utils::getCloseNextDayRecordTime() предыдущей пятницы
					$class .= ' miss';
					$tooltip = "Запись была возможна до $closeNextDayRecordTime предыдущего рабочего дня";
				} elseif (($timetable_item['DateDiff'] > 0 || ($allow_record_today && $timetable_item['DateDiff'] >= 0)) && $timetable_item['DateDiff'] <= $max_day) {
					if (!empty($timetable_item['Person_id']) || !empty($timetable_item['TimetableLock_lockTime'])) {
						// Бирка уже занята
						$class .= ' busy';
						$tooltip = "Время занято";
						$auth_user = null;//Auth::instance()->get_user();
						$user_id = isset($auth_user->id) ? $auth_user->id : NULL;
						if (isset($auth_user) && $timetable_item['pmUser_updID'] == $user_id && !empty($user_id)) {
							$timetable_item['my_record'] = true;
							$class .= ' reserved';
							$timetable_item['person'] = $this->Person_model->getPersonMain($timetable_item['Person_id']);
							$tooltip = "Запись:" .$timetable_item['person']['Person_FIO']."<br/>Нажмите для отмены записи";
						}
					} else {
						loadLibrary('TTimetableTypes');
						$TimetableType = $this->TimetableType = TTimetableTypes::instance()->getTimetableType($timetable_item['TimetableType_id']);
						if ($doctor['RecType_id'] == 3) {
							// Прием по живой очереди
							$class .= ' miss';
							$tooltip = "Запись невозможна, врач ведет прием в порядке очереди.";
						} elseif ($doctor['RecType_id'] == 5 || $doctor['RecType_id'] == 6 ) {
							// Время заблокировано
							$class .= ' busy';
							$tooltip = "Время заблокировано";
						} elseif ( $timetable_item['TimetableType_id'] == 2) {
							// резервные бирки
							if (!empty($data['enableReservedRecords'])) {
								$class .= ' free';
								$tooltip = null;
								$timetable_item['is_free'] = true;
							} else {
								$class .= ' busy';
								$tooltip = "Время, запись на которое осуществляется в поликлинике.";
							}
						} elseif (!$TimetableType->inSources(array(8,9))) {
							// Время, запись на которое осуществляется в поликлинике.
							$class .= ' busy';
							$tooltip = "Время, запись на которое осуществляется в поликлинике.";
						}
						elseif ( $timetable_item['TimetableType_id'] == 3 ) { // Платный прием
							if ( isset($data['allow_pay']) && $data['allow_pay'] == true ) {
								$class .= ' free';
								$tooltip = $tooltip = "Платный прием";
								$timetable_item['is_free'] = true;
							} else {
								$class .= ' busy';
								$tooltip = "Время заблокировано"."<br/>" . "Только платный прием";
							}
						}
						elseif ($block_timetable && ($timetable_item['TimetableType_id'] == 1 || $timetable_item['TimetableType_id'] == 11)) {
							// если в включена автоматическая обработка очереди
							// и есть люди находящиеся в очереди
							$class .= ' busy';
							$tooltip = "Время занято";
						} else {
							// Время свободно
							$class .= ' free';
							$tooltip = null;
							$timetable_item['is_free'] = true;
						}
					}
				}

				if ($timetable_item['annot']) { // есть примечания на бирку
					$class .= ' annot';
					$class .= ' ttg-annot-' . hash('crc32', $timetable_item['annot']); // класс для идентификации одинаковых приемечаний
					$tooltip .= (empty($tooltip) ? '' : '<br>') . htmlspecialchars($timetable_item['annot']);


					$timetable_item['MedStaffFactDay_Descr'] .= '<br>'.$timetable_item['annot'];
				}

				$timetable_item['class'] = $class;
				$timetable_item['tooltip'] = $tooltip;
			}

		}

		$days = array();
		$day_x = time();

		foreach($days_range as $day_counter) {
			$day = array();
			$day['date'] = date('Y-m-d', $day_x); // Число, месяц;
			$day['datetime'] = new DateTime($day['date']);

			$day['date_readable'] = date('j', $day_x) . ' ' . mb_substr(mb_strtolower($this->calendar->get_month_name(date('m', $day_x))), 0, 3); // Число, месяц
			$day['date_readable_full'] = date('j', $day_x) . ' ' . mb_strtolower($this->calendar->get_month_name(date('m', $day_x))) .' '. date('Y', $day_x); // Число, месяц, год
			$day['day_of_week'] = $this->calendar->get_day_names('short')[date('w', $day_x)]; // День недели
			$day['day_of_week_full'] = mb_strtolower($this->calendar->get_day_names('long')[date('w', $day_x)]); // День недели
			$day['is_today'] = (date('j F', $day_x) == date('j F')) ? true : false; // Признак сегодня
			$day['day_num'] = date('j', $day_x);
			$day['month_add'] = mb_strtolower($this->calendar->get_month_name(date('m', $day_x)));

			$day_id = TimeToDay($day_x);
			$day['id'] = $day_id;
			$day['description'] = (!empty($msfDayDescriptions[$day_id]) ? $msfDayDescriptions[$day_id] : '');
			$day_x = strtotime('+1 day', $day_x);

			$day['timetable'] = array();
			$day['timetable_is_free'] = false;
			foreach ($timetable_days as &$timetable_day) {
				foreach ($timetable_day as &$timetable_item) {
					if ($timetable_item['date'] == $day['date']) {
						$day['timetable'][] = $timetable_item;
						if ($timetable_item['is_free']) {
							$day['timetable_is_free'] = true;
						}
					}
				}
			}
			$days[] = $day;
		}
		return $days;
	}

	/**
	 * Отмена направления из групповой бирки
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	function cancelTimetableGrafRecList($data)
	{
		$queryParams = [
			"TimetableGrafRecList_id" => $data["TimetableGrafRecList_id"],
			"pmUser_id" => $data["pmUser_id"],
		];
		$cancelProc = "p_TimeTableGrafRecList_cancel";
		$resp = $this->execCommonSP($cancelProc, $queryParams);
		// если указан статус очереди, то при отмене шлем пуш и емэйл для пользователя и оповещение в портал
		return $resp;
	}

	/**
	 * Получить расписание врача
	 *
	 * @param int $doctor_id id врача
	 * @param int $start_date timestamp начала расписания
	 * @param int $end_date окончание расписания
	 */
	public function getTimetable($doctor_id, $start_date, $end_date) {
		//todo сделать функцию day_sql для дат
		$this->load->helper("Reg");
		$params = array(
			'doctor_id' => $doctor_id,
			'TimeTableGraf_Day_Start' => TimeToDay($start_date),
			'TimeTableGraf_Day_End' => TimeToDay($end_date)
		);
		$result = $this->queryResult("
		select
			t.TimetableGraf_id as \"TimetableGraf_id\",
			t.Person_id as \"Person_id\",
			t.TimeTableGraf_Day as \"TimeTableGraf_Day\",
			t.TimetableGraf_begTime as \"TimetableGraf_begTime\",
			t.TimetableType_id as \"TimetableType_id\",
			t.pmUser_updID as \"pmUser_updID\",
			case when t.TimetableGraf_begTime > (dbo.tzGetDate() + INTERVAL '15 minute') then 1 else 0 end as \"IsFuture\",
			(DATE_PART('day', dbo.tzGetdate()::timestamp - t.TimetableGraf_begTime::timestamp)) as \"DateDiff\",
			ttl.TimetableLock_lockTime as \"TimetableLock_lockTime\"
		from
			v_TimeTableGraf_lite t
		left join TimetableLock ttl on t.TimetableGraf_id = ttl.TimetableGraf_id
		where
			t.TimeTableGraf_Day >= :TimeTableGraf_Day_Start and
			t.TimeTableGraf_Day < :TimeTableGraf_Day_End and
			t.MedStaffFact_Id = :doctor_id and
            t.TimetableGraf_begTime is not null and
            t.TimetableType_id != 12
		order by t.TimetableGraf_begTime",$params);

		return $result;
	}

	/**
	 * Записать человека на заданное время в расписании
	 * При необходимости получить код бронирования ЭО
	 * @param object $data параметры
	 */
	public function setRecord($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'TimeTableGraf_id' => $data['TimetableGraf_id'],
			'pmuser_id' => $data['pmuser_id'],
			'RecClass_id' => 1,
			'RecMethodType_id' => !empty($data['record_platform']) ? $data['record_platform'] : 1,
			'TimeTableGraf_isAuto' => null,
			'TimetableGrafModeration_Status' => null,
			'TimetableGraf_IsModerated' => null,
		);

		if (!empty($data['isElectronicQueue'])) {
			// для получения кода брони обязательно указывать 3
			$params['TimeTableGraf_isAuto'] = '3';
		}

		if (!empty($data['autoModeration'])) {
			$params['TimetableGrafModeration_Status'] = 3;
			$params['TimetableGraf_IsModerated'] = 1;
		}

		$result = $this->getFirstRowFromQuery("
			select
				evndirection_taloncode as \"EvnDirection_TalonCode\" ,
				error_code as \"Error_code\" ,
				error_message as \"Error_Message\"
			from p_TimeTableGraf_ModerateRecord(
				timetablegraf_id => :TimeTableGraf_id,
				person_id => :Person_id,
				recclass_id => :RecClass_id,
				timetablegraf_ismoderated => :TimetableGraf_IsModerated,
				timetablegrafmoderation_status => :TimetableGrafModeration_Status,
				recmethodtype_id => :RecMethodType_id,
				pmuser_id => :pmuser_id,
				timetablegraf_isauto => :TimeTableGraf_isAuto,
				evndirection_taloncode => null,
				error_code => null,
				error_message => null
			)
		",$params);
		return $result;
	}

	/**
	 * Получить статистику по свободным биркам на ближайшие 2 недели
	 *
	 * @param int $doctor_id ID врача
	 * @param boolean $allow_record_today Разрешаем запись на сегодня или на завтра после времени закрытия записи или на понедельник после времени закрытия записи в пятницу
	 * @param boolean $allow_pay
	 * @param object $data все параметры сюда кладем
	 * @return stdClass
	 */
	public function getFirstFreeDateStatistics($doctor_id, $allow_record_today = false, $allow_pay = false, $data = NULL)
	{
		$this->load->helper("Reg");
		$first_day = day_sql(time());
		$cur_date = new Datetime();
		$max_day = $this->getRecordDayCount();;
		$last_day = $first_day + $max_day - 1;
		$this->load->library('calendar');
		$days_name = $this->calendar->get_day_names('short');

		// Есть сегодня не рабочий день или завтра не рабочий день, то находим дату окончания выходных и праздников и от нее считаем first_day
		$next_date = clone $cur_date;
		$next_date->modify('+1 day');
		if (!IsWorkDay($cur_date) || (!IsWorkDay($next_date))) {
			$work_date = NextWorkDay($cur_date);
			$first_day = day_sql(strtotime($work_date->format('Y-m-d')));
		}
		if (date('H:i') > getCloseNextDayRecordTime()) {
			$first_day++;
		}
		if (date('H:i') > getShowNewDayTime()) {
			$last_day++;
		}

		$filter_today = '';

		// если разрешена запись на сегодня,
		if ($allow_record_today) {

			// то учитываем сегодняшний день
			$first_day--;
			// учитываем текущее время
			$filter_today = "and ttg.TimeTableGraf_begTime > DATEADD(minute, 15, dbo.tzGetDate()) ";
		}

		$type_filter = " ttg.TimeTableType_id in (1,11";
		$type_attribute_filter = " ttal.TimetableTypeAttribute_id in (8,9";

		// для дисп учета
		$ttal = "and ttal.TimetableTypeAttributeLink_id is not null";
		if (!empty($data->enableReservedRecords)) {
			$type_filter .= ",2";
			$ttal = "";
		}

		// если разрешена запись на платные бирки
		// то берем обычные свободные и платные свободные
		if ($allow_pay) {
			$type_filter .= ',3';
		}

		$type_filter .= ') ';
		$type_attribute_filter .= ') ';

		$groupBy = "";

		$select = "
        	min(ttg.first_free_date) as first_free_date,
			sum(ttg.total) as total,
        ";
		$params = array(
			'doctor_id' => $doctor_id['MedStaffFact_list'],
			'TimeTableGraf_Day_Start' => $first_day,
			'TimeTableGraf_Day_End' => $last_day);

		$result = $this->getFirstRowFromQuery("
			select
				{$select}
				sum(mpd.MedpersonalDay_FreeRec) as free,
				sum(mpd.MedPersonalDay_InetRec) as inet,
				sum(mpd.MedpersonalDay_ReservRec) as reserved,
				sum(mpd.MedpersonalDay_PayRec) as pay
			from MedPersonalDay mpd
			LEFT JOIN LATERAL (
				select 
					count(ttg.TimeTableGraf_id) as total,
					min(case when ttg.Person_id is null {$ttal} and {$type_filter} {$filter_today} then ttg.TimeTableGraf_begTime else null end) as first_free_date
				from v_TimetableGraf_lite ttg 
				left join TimetableTypeAttributeLink ttal  on ttal.TimetableType_id = ttg.TimetableType_id and {$type_attribute_filter}
				left join v_EvnQueue queue  on queue.EvnDirection_id = ttg.EvnDirection_id
				left join v_EvnDirection ed  on ed.EvnDirection_id = queue.EvnDirection_id
				left join v_MedStaffFact msf  on msf.MedStaffFact_id = ed.MedStaffFact_id
				where mpd.Day_id = ttg.TimetableGraf_Day 
					and mpd.MedStaffFact_id = ttg.MedStaffFact_id
					and coalesce(queue.EvnQueueStatus_id, 0) != 1
			) as ttg on true
			where mpd.MedStaffFact_id = :doctor_id
				and mpd.Day_id > :TimeTableGraf_Day_Start
				and mpd.Day_id <= :TimeTableGraf_Day_End
			{$groupBy}
		", $params);

		if (count($result) != 0) {
			$res = array();
			if (is_array($result['first_free_date'])) {
				$res['FirstFreeDate'] = $result['first_free_date']->format('d.m.Y') . ' ' . $days_name[$result['first_free_date']->format('w')] . ' ' . $result['first_free_date']->format('H:i');
				$res['FirstFreeFullDate'] = $result['first_free_date'];
			} else {
				$res['FirstFreeDate'] = null;
				$res['FirstFreeFullDate'] = null;
			}

			$res['Total'] = $result['total'];
			$res['Free'] = $result['free'];
			$res['Reserved'] = $result['reserved'];
			$res['Pay'] = $result['pay'];
			$res['Inet'] = $result['inet'];
			$result = $res;
		} else {
			$result = false;
		}

		return $result;
	}

	/**
	 * Отменить запись
	 *
	 * @param int $record_id id записи
	 */
	function Cancel($record_id, $patient_id = null, $data)
	{
		$user_id = 0;
		if(!empty($data['pmuser_id'])) {
			$user_id = $data['pmuser_id'];
		}

		$now = new DateTime();
		$result = array();
		$result['success'] = false;
		$result['error'] = '';
		$this->load->helper("Reg");

		$q_query = $this->getFirstRowFromQuery("
			select
				ttg.pmUser_updId as \"pmUser_updId\",
				ttg.Person_id as \"Person_id\",
				ttg.MedStaffFact_id as \"MedStaffFact_id\",
				ttg.TimeTableGraf_Day as \"TimeTableGraf_Day\",
				ttg.TimetableGraf_id as \"TimetableGraf_id\",
				ttg.TimetableGraf_begTime as \"TimetableGraf_begTime\",
				dbo.tzGetDate()::timestamp - TimetableGraf_begTime::timestamp as \"DateDiff\",
				msf.Person_FIO as \"MedPersonal_FIO\",
				ps.Person_SurName || ' ' || ps.Person_FirName as \"Person_FIO\",
			    queue.EvnQueue_id as \"EvnQueue_id\",
			    ed.pmUser_insId as \"pmUser_insId\"
			from v_TimetableGraf_lite ttg
			left join v_MedStaffFact msf on msf.MedStaffFact_id = ttg.MedStaffFact_id
			left join v_PersonState ps  on ps.Person_id = ttg.Person_id
			left join v_EvnDirection ed on ttg.evndirection_id = ed.evndirection_id
			LEFT JOIN LATERAL(
			  select
			  	EvnQueue_id 
			  from v_EvnQueue q 
			  where q.TimetableGraf_id = ttg.TimetableGraf_id
			  	and q.RecMethodType_id = 1
			  	and q.EvnDirection_id = ttg.EvnDirection_id
			  	and q.Person_id = ttg.Person_id
			  	and q.EvnQueueStatus_id = 3
			  	limit 1
			) queue	on true
			where ttg.TimetableGraf_id = :TimetableGraf_id", array('TimetableGraf_id' => $record_id));
		if (count($q_query) < 1) {
			$result['error'] = "Время не найдено. Выберите другое время.";
		} else {
			$q = $q_query;
			if (empty($patient_id) && $q['pmUser_insId'] != $user_id
				//&& $q['Person_id'] != $data['esia_person']
				&& empty($q['EvnQueue_id'])) {
				$result['error'] = "Вы не можете освободить эту бирку, так как она занята не вами.1";
			} else {
				if (!empty($patient_id) && $patient_id != $q['Person_id']) {
					$result['error'] = "Вы не можете освободить эту бирку, так как она занята не вами.2";
				} elseif (empty($q['Person_id'])) {
					$result['error'] = "Время уже свободно. Выберите другое время.";
				} else {
					if ($now->format('Y-m-d H:i:s') >= DateTime::createFromFormat('Y-m-d H:i:s', $q['TimetableGraf_begTime'])->format('Y-m-d H:i:s')) {
						$result['error'] = "Время посещения уже прошло, отмена невозможна";
						return $result;
					}
					//todo подгрузить из конфига
					//$data['allow_cancel_any_time'] = true;
					if (!$data['allow_cancel_any_time']) {
						if (($now->format('H:i') >= getCloseNextDayRecordTime() && $q['DateDiff'] == 1) || $q['DateDiff'] == 0) {
							$result['error'] = "Отменять запись можно не позднее" . getCloseNextDayRecordTime() . "дня предшествующего посещению!";
							return $result;
						}
					}


					$this->cancelRecord($record_id, $user_id);
					$this->UnblockTime($record_id, $user_id);

					$result['success'] = true;
					$result['error'] = '';
					$result['dataSend'] = array(
						'Person_FIO' => $q_query['Person_FIO'],
						'MedPersonal_FIO' => $q_query['MedPersonal_FIO']
					);
				}
			}
		}
		return $result;
	}

	/**
	 * Отменить запись на конкретную бирку
	 *
	 * @param int $record_id id записи
	 * @param int $user_id Идентификатор человека, от которого была запись
	 */
	public function cancelRecord($record_id, $user_id = null, $cancelEvnDirection = true)
	{
		if ($cancelEvnDirection) {

			$evnData = $this->getFirstRowFromQuery("
			select
				EvnDirection_id as \"EvnDirection_id\"
			from v_TimeTableGraf_lite
			where
				TimeTableGraf_id = :ttg_id
		", array('ttg_id' => $record_id));


			if (count($evnData) > 0) {
				$queryParams = array(
					':EvnDirection_id' => $evnData['EvnDirection_id'],
					':DirFailType_id' => 5,
					':EvnStatusCause_id' => 1,
					':EvnComment_Comment' => null,
					':pmUser_id' => $user_id
				);

				$this->getFirstRowFromQuery("
					select
					    error_code,
					    error_message
					from p_EvnDirection_cancel(
					    EvnDirection_id => :EvnDirection_id,
					    DirFailType_id => :DirFailType_id,
					    EvnComment_Comment => :EvnComment_Comment,
					    EvnStatusCause_id => :EvnStatusCause_id,
					    medstafffact_fid  => null,
					    pmUser_id => :pmUser_id,
					    error_code => null,
					    Error_Message => null
					)", $queryParams);

			}
		}
		$params = array(
			'record_id' => $record_id,
			'user_id' => $user_id
		);
		$result = $this->queryResult("
				select
				        error_code,
					    error_message
				from p_TimeTableGraf_cancel(
				        timetablegraf_id => :record_id,
				        pmuser_id => :user_id,
				        iscancelpersonrecord => null,
				        error_code => null,
				        error_message => null
				    )", $params);
		return $result;
	}

	/**
	 * Снимает блокировку у переданной бирки
	 *
	 * @param int $record_id id записи
	 */
	function UnblockTime($record_id, $user_id)
	{
		if (!isset($user_id)) {
			$result = $this->queryResult("
			delete from TimetableLock
			where
				TimetableGraf_id = :record_id", array('record_id' => $record_id));

			return $result;
		} else {
			$params = array(
				'record_id' => $record_id,
				'pmUser_id' => $user_id
			);
			$result = $this->queryResult("
				delete from TimetableLock
				where
					pmUser_insID = :pmUser_id and
					TimetableGraf_id = :record_id", $params);

			return $result;
		}
	}
}