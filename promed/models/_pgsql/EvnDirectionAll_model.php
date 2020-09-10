<?php
defined("BASEPATH") or die ("No direct script access allowed");
require_once("EvnAbstract_model.php");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 *
 * EvnDirectionAll_model - Модель события выписки направления
 *
 * При выписке направления создается или электронное или системное направление,
 * с записью на бирку расписания или с постановкой в очередь.
 *
 * Системное направление - системное событие направления пациента,
 * которое создается автоматически и не редактируется пользователем.
 * Электронное направление - учетный документ,
 * данные которого пользователь вводит в форме редактирования.
 *
 * @property-read int $rid КВС или ТАП
 * @property-read int $pid Движение в отделении или посещение
 * @property-read int $TimetableStac_id
 * @property-read int $TimetableGraf_id
 * @property-read int $TimetableMedService_id
 * @property-read int $EvnQueue_id
 *
 * @property-read array $timetableMedServiceData
 * @property-read array $timetableResourceData
 * @property-read array $timetableStacData
 * @property-read array $timetableGrafData
 * @property-read int $currentDay
 *
 * @property TimetableMedService_model $TimetableMedService_model
 * @property TimetableGraf_model $TimetableGraf_model
 * @property TimetableStac_model $TimetableStac_model
 * @property EvnQueue_model $EvnQueue_model
 * @property EmergencyData_model $EmergencyData_model
 * @property Registry_model $Registry_model
 * @property CI_DB_driver $db
 * @property EvnDirectionExt_model $EvnDirectionExt_model
 *
 * @property int $DirType_id
 * @property int $DirFailType_id
 * @property int $LpuSectionProfile_id
 * @property int $MedService_id
 * @property int $Resource_id
 * @property int $MedPersonal_did
 * @property int $LpuSection_did
 * @property int $RecMethodType_id
 * @property int $LpuUnit_did
 *
 * Сейчас бизнес логика выписки направления находится в EvnDirection_model и во многих других моделях
 * 49	EvnDirectionHistologic	    Направление на патологогистологическое исследование
 * 56	EvnDirectionMorfoHistologic	Направление на патоморфогистологическое исследование трупа
 * 88	EvnDirectionTub	            Направление на проведение микроскопических исследований на туберкулез
 * 117	EvnDirectionHTM	            Направление на ВМП
 * 135	EvnDirectionForensic	    Поручение о проведении экспертизы
 */
class EvnDirectionAll_model extends EvnAbstract_model
{
	const EVN_STATUS_DIRECTION_IN_QUEUE = 'Queued'; // 10 Поставлено в очередь
	const EVN_STATUS_DIRECTION_REJECTED = 'Declined'; // 13 Отклонено
	const EVN_STATUS_DIRECTION_SERVICED = 'Serviced'; // 15 Обслужено
	const EVN_STATUS_DIRECTION_RECORDED = 'DirZap'; // 17 Записано
	const EVN_STATUS_DIRECTION_INPROC = 'InProc'; // 51 В обработке
	private $_timetableGrafData;
	private $_timetableMedServiceData;
	private $_timetableResourceData;
	private $_timetableStacData;
	private $_evnqueue_id;

	private $DirType_id;
	private $DirFailType_id;
//	private $LpuSectionProfile_id;
	private $MedService_id;
	private $Resource_id;
	private $MedPersonal_did;
	private $LpuSection_did;
	private $RecMethodType_id;

	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm108 = "HH24:MI:SS";

	/**
	 * @return int
	 */
	function getCurrentDay()
	{
		$this->load->helper("Reg");
		return TimeToDay($this->currentDT->getTimestamp());
	}

	/**
	 * Возвращает правильную модель для Уфы
	 * @return string
	 */
	function getRegistryModel()
	{
		if ($this->getRegionNumber() == 2) {
			return "RegistryUfa_model";
		} else {
			return "Registry_model";
		}
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function getTimetableGrafData()
	{
		if (empty($this->TimetableGraf_id)) {
			return [];
		}
		if (empty($this->_timetableGrafData)) {
			$this->_timetableGrafData = $this->loadTimetableGrafData($this->TimetableGraf_id);
		}
		return $this->_timetableGrafData;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function getTimetableMedServiceData()
	{
		if (empty($this->TimetableMedService_id)) {
			return [];
		}
		if (empty($this->_timetableMedServiceData)) {
			$this->_timetableMedServiceData = $this->loadTimetableMedServiceData($this->TimetableMedService_id);
		}
		return $this->_timetableMedServiceData;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function getTimetableResourceData()
	{
		if (empty($this->TimetableResource_id)) {
			return [];
		}
		if (empty($this->_timetableResourceData)) {
			$this->_timetableResourceData = $this->loadTimetableResourceData($this->TimetableResource_id);
		}
		return $this->_timetableResourceData;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function getTimetableStacData()
	{
		if (empty($this->TimetableStac_id)) {
			return [];
		}
		if (empty($this->_timetableStacData)) {
			$this->_timetableStacData = $this->loadTimetableStacData($this->TimetableStac_id);
		}
		return $this->_timetableStacData;
	}

	/**
	 * @return bool|float|int|mixed|string|null
	 * @throws Exception
	 */
	function getEvnQueue_id()
	{
		if ($this->isNewRecord) {
			return null;
		}
		$evnqueue_id = $this->getAttribute("evnqueue_id");
		if ($evnqueue_id) {
			return $evnqueue_id;
		}
		if (isset($this->_evnqueue_id)) {
			if (false === $this->_evnqueue_id) {
				return null;
			}
			return $this->_evnqueue_id;
		}
		$sql = "
			select evnqueue_id as \"EvnQueue_id\"
			from v_EvnQueue
			where evndirection_id = :evndirection_id
			limit 1
		";
		$sqlParams = ["evndirection_id" => $this->id];
		$this->_evnqueue_id = $this->getFirstResultFromQuery($sql, $sqlParams);
		return $this->_evnqueue_id;
	}

	/**
	 * @param int $TimetableGraf_id
	 * @return array
	 * @throws Exception
	 */
	function loadTimetableGrafData($TimetableGraf_id)
	{
		if (empty($TimetableGraf_id)) {
			return [];
		}
		$sql = "
			select
				MSF.LpuUnit_id as \"LpuUnit_id\",
				ttg.pmUser_updId as \"pmUser_updId\",
				ttg.TimetableGraf_id as \"TimetableGraf_id\",
				ttg.RecClass_id as \"RecClass_id\",
				ttg.TimeTableGraf_IsModerated as \"TimeTableGraf_IsModerated\",
				ttg.Evn_id as \"Evn_id\",
				ttg.TimeTableGraf_Mark as \"TimeTableGraf_Mark\",
				ttg.TimetableGraf_IsDop as \"TimetableGraf_IsDop\",
				ttg.TimetableType_id as \"TimetableType_id\",
				ttg.TimetableGraf_Time as \"TimetableGraf_Time\",
				ttg.TimetableGraf_begTime as \"TimetableGraf_begTime\",
				ttg.TimetableGraf_factTime as \"TimetableGraf_factTime\",
				ttg.TimetableGraf_Day as \"TimetableGraf_Day\",
				ttg.EvnDirection_id as \"EvnDirection_id\",
				ttg.MedStaffFact_id as \"MedStaffFact_id\",
				ttg.Person_id as \"Person_id\"
			from
				v_TimetableGraf_lite ttg
				inner join v_MedStaffFact MSF on MSF.MedStaffFact_id = ttg.MedStaffFact_id
			where ttg.TimetableGraf_id = :TimetableGraf_id
			limit 1
		";
		$sqlParams = ["TimetableGraf_id" => $TimetableGraf_id];
		$data = $this->getFirstRowFromQuery($sql, $sqlParams);
		if (empty($data) || !is_array($data)) {
			throw new Exception("Не удалось получить данные бирки расписания врача");
		}
		return $data;
	}

	/**
	 * @param int $TimetableMedService_id
	 * @return array
	 * @throws Exception
	 */
	function loadTimetableMedServiceData($TimetableMedService_id)
	{
		if (empty($TimetableMedService_id)) {
			return [];
		}
		$sql = "
			select
				MS.LpuUnit_id as \"LpuUnit_id\",
				ttms.pmUser_updId as \"pmUser_updId\",
				ttms.TimetableMedService_id as \"TimetableMedService_id\",
				ttms.RecClass_id as \"RecClass_id\",
				ttms.Evn_id as \"Evn_id\",
				ttms.TimetableMedService_IsDop as \"TimetableMedService_IsDop\",
				ttms.TimetableType_id as \"TimetableType_id\",
				ttms.TimetableMedService_Time as \"TimetableMedService_Time\",
				ttms.TimetableMedService_begTime as \"TimetableMedService_begTime\",
				ttms.TimetableMedService_factTime as \"TimetableMedService_factTime\",
				ttms.TimetableMedService_Day as \"TimetableMedService_Day\",
				ttms.EvnDirection_id as \"EvnDirection_id\",
				ttms.MedService_id as \"MedService_id\",
				ttms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				ttms.Person_id as \"Person_id\",
				MST.MedServiceType_SysNick as \"MedServiceType_SysNick\"
			from
				v_TimetableMedService_lite ttms
				left join v_UslugaComplexMedService UCMS on UCMS.UslugaComplexMedService_id = ttms.UslugaComplexMedService_id
				inner join v_MedService MS on MS.MedService_id = coalesce(ttms.MedService_id, UCMS.MedService_id)
				inner join v_MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
			where ttms.TimetableMedService_id = :TimetableMedService_id
			limit 1
		";
		$sqlParams = ["TimetableMedService_id" => $TimetableMedService_id];
		$data = $this->getFirstRowFromQuery($sql, $sqlParams);
		if (empty($data) || !is_array($data)) {
			throw new Exception("Не удалось получить данные бирки расписания врача");
		}
		return $data;
	}

	/**
	 * @param int $TimetableResource_id
	 * @return array
	 * @throws Exception
	 */
	function loadTimetableResourceData($TimetableResource_id)
	{
		if (empty($TimetableResource_id)) {
			return [];
		}
		$sql = "
			select
				ttms.pmUser_updId as \"pmUser_updId\",
				ttms.TimetableResource_id as \"TimetableResource_id\",
				ttms.RecClass_id as \"RecClass_id\",
				ttms.Evn_id as \"Evn_id\",
				ttms.TimetableResource_IsDop as \"TimetableResource_IsDop\",
				ttms.TimetableType_id as \"TimetableType_id\",
				ttms.TimetableResource_Time as \"TimetableResource_Time\",
				ttms.TimetableResource_begTime as \"TimetableResource_begTime\",
				ttms.TimetableResource_Day as \"TimetableResource_Day\",
				ttms.EvnDirection_id as \"EvnDirection_id\",
				ttms.Resource_id as \"Resource_id\",
				ttms.Person_id as \"Person_id\"
			from v_TimetableResource_lite ttms
			where ttms.TimetableResource_id = :TimetableResource_id
			limit 1
		";
		$sqlParams = ["TimetableResource_id" => $TimetableResource_id];
		$data = $this->getFirstRowFromQuery($sql, $sqlParams);
		if (empty($data) || !is_array($data)) {
			throw new Exception("Не удалось получить данные бирки расписания врача");
		}
		return $data;
	}

	/**
	 * @param int $TimetableStac_id
	 * @return array
	 * @throws Exception
	 */
	function loadTimetableStacData($TimetableStac_id)
	{
		if (empty($TimetableStac_id)) {
			return [];
		}
		$sql = "
			select
				LS.LpuUnit_id as \"LpuUnit_id\",
				tts.pmUser_updId as \"pmUser_updId\",
				tts.TimetableStac_id as \"TimetableStac_id\",
				tts.RecClass_id as \"RecClass_id\",
				tts.Evn_id as \"Evn_id\",
				tts.TimetableType_id as \"TimetableType_id\",
				tts.TimetableStac_Day as \"TimetableStac_Day\",
				tts.LpuSection_id as \"LpuSection_id\",
				tts.LpuSectionBedType_id as \"LpuSectionBedType_id\",
				tts.TimeTableStac_setDate as \"TimeTableStac_setDate\",
				tts.TimetableStac_EmStatus as \"TimetableStac_EmStatus\",
				tts.EmergencyData_id as \"EmergencyData_id\",
				tts.Evn_pid as \"Evn_pid\",
				tts.EvnDirection_id as \"EvnDirection_id\",
				tts.Person_id as \"Person_id\"
			from
				v_TimetableStac_lite tts
				inner join v_LpuSection LS on LS.LpuSection_id = tts.LpuSection_id
			where tts.TimetableStac_id = :TimetableStac_id
			limit 1
		";
		$sqlParams = ["TimetableStac_id" => $TimetableStac_id];
		$data = $this->getFirstRowFromQuery($sql, $sqlParams);
		if (empty($data) || !is_array($data)) {
			throw new Exception("Не удалось получить данные бирки расписания врача");
		}
		return $data;
	}

	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList([
			self::SCENARIO_SET_ATTRIBUTE,
			"cancel",
			"reject",
			"returnToQueue",
		]);
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		if (in_array($this->scenario, ["cancel", "reject"])) {
			$this->_params["EvnStatusHistory_Cause"] = !empty($data["EvnStatusHistory_Cause"]) ? $data["EvnStatusHistory_Cause"] : null;
			$this->_params["EvnStatusCause_id"] = !empty($data["EvnStatusCause_id"]) ? $data["EvnStatusCause_id"] : null;
		}
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);
		switch ($name) {
			case "returnToQueue":
				$rules = [
					["field" => "EvnDirection_id", "label" => "Направление", "rules" => "required", "type" => "id"],
				];
				break;
			case "cancel":
			case "reject":
				$rules = [
					["field" => "EvnDirection_id", "label" => "Направление", "rules" => "required", "type" => "id"],
					["field" => "DirType_id", "label" => "Тип направления", "rules" => "", "type" => "id"],
					["field" => "DirFailType_id", "label" => "Причина", "rules" => "trim", "type" => "id"],
					["field" => "EvnStatusHistory_Cause", "label" => "Комментарий", "rules" => "trim", "type" => "string"],
					["field" => "EvnStatusCause_id", "label" => "Причина", "rules" => "trim", "type" => "id"],
				];
				break;
		}
		return $rules;
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if ($this->isNewRecord && in_array($this->scenario, ["returnToQueue", "cancel", "reject"])) {
			throw new Exception("Ошибка выбора направления", 400);
		}
		if (in_array($this->scenario, ["returnToQueue"]) && empty($this->TimetableGraf_id) && empty($this->TimetableMedService_id) && empty($this->TimetableStac_id) && empty($this->TimetableResource_id)) {
			throw new Exception("Ошибка выбора бирки", 400);
		}
		if (in_array($this->scenario, ["cancel", "reject"]) && 15 == $this->EvnStatus_id) {
			throw new Exception("Направление обслужено, отмена/отклонение невозможно", 400);
		}
		if (in_array($this->scenario, ["cancel", "reject"]) && $this->DirType_id != 20
			&& (!empty($this->TimetableGraf_id) || !empty($this->TimetableMedService_id) || !empty($this->TimetableStac_id) || !empty($this->TimetableResource_id) || !empty($this->EvnQueue_id))
		) {
			//в данный момент можно назначать направления в опер. блок в очередь
			//доработка, чтобы направление из очереди можно было отменить (#194628)
			if (empty($this->TimetableGraf_id) && empty($this->EvnQueue_id)) {
				throw new Exception("Неправильно выбран метод отмены/отклонения направления", 500);
			} elseif (!empty($this->TimetableGraf_id)) {
				// проверка свободна ли бирка, возможно кривое направление
				$sql = "
					select TimetableGraf_id as \"TimetableGraf_id\"
					from v_TimetableGraf_lite
					where EvnDirection_id = :EvnDirection_id
					  and TimetableGraf_id = :TimetableGraf_id
					limit 1
				";
				$sqlParams = [
					"EvnDirection_id" => $this->id,
					"TimetableGraf_id" => $this->TimetableGraf_id
				];
				$resp = $this->queryResult($sql, $sqlParams);
				if (!empty($resp[0]["TimetableGraf_id"])) {
					throw new Exception("Неправильно выбран метод отмены/отклонения направления", 500);
				}
			}
		}
		if (in_array($this->scenario, ["cancel", "reject"]) && !empty($this->_params["EvnStatusCause_id"])) {
			// значит DirFailType_id вычисляем на основе EvnStatusCause_id
			$sql = "
				select escl.DirFailType_id as \"DirFailType_id\"
				from v_EvnStatusCauseLink escl
				where escl.EvnStatusCause_id = :EvnStatusCause_id
				limit 1
			";
			$sqlParams = ["EvnStatusCause_id" => $this->_params["EvnStatusCause_id"]];
			$this->DirFailType_id = $this->getFirstResultFromQuery($sql, $sqlParams);
		}
		if (in_array($this->scenario, ["cancel", "reject"]) && empty($this->DirFailType_id)) {
			throw new Exception("Ошибка выбора причины", 400);
		}
		if (in_array($this->scenario, ["returnToQueue"]) && !empty($this->TimetableMedService_id)) {
			if (empty($this->timetableMedServiceData["Person_id"])) {
				throw new Exception("Выбранная вами бирка уже свободна.", 400);
			}
			$this->load->helper("Reg");
			if (false == (
					$this->timetableMedServiceData["MedServiceType_SysNick"] == "konsult" ||
					($this->timetableMedServiceData["pmUser_updId"] == $this->promedUserId) ||
					isCZAdmin() ||
					isLpuRegAdmin($this->sessionParams["org_id"]) ||
					isInetUser($this->timetableMedServiceData["pmUser_updId"])
				)) {
				throw new Exception("У вас нет прав отменить запись на прием, <br/>так как она сделана не вами.", 400);
			}
			if (!isSuperAdmin() && $this->timetableMedServiceData["TimetableMedService_Day"] < $this->currentDay) {
				throw new Exception("У вас нет прав отменить запись на прием, <br/>так как запись была создана раньше текущего дня.", 400);
			}
		}
		if (in_array($this->scenario, ["returnToQueue"]) && !empty($this->TimetableResource_id)) {
			if (empty($this->timetableResourceData["Person_id"])) {
				throw new Exception("Выбранная вами бирка уже свободна.", 400);
			}
			$this->load->helper("Reg");
			if (false == (
					($this->timetableResourceData["pmUser_updId"] == $this->promedUserId) ||
					isCZAdmin() ||
					isLpuRegAdmin($this->sessionParams["org_id"]) ||
					isInetUser($this->timetableResourceData["pmUser_updId"])
				)) {
				throw new Exception("У вас нет прав отменить запись на прием, <br/>так как она сделана не вами.", 400);
			}
			if (!isSuperAdmin() && $this->timetableResourceData["TimetableResource_Day"] < $this->currentDay) {
				throw new Exception("У вас нет прав отменить запись на прием, <br/>так как запись была создана раньше текущего дня.", 400);
			}
		}
		if (in_array($this->scenario, ["returnToQueue"]) && !empty($this->TimetableStac_id)) {
			if (empty($this->timetableStacData["Person_id"])) {
				throw new Exception("Выбранная вами бирка уже свободна.", 400);
			}
			$this->load->helper("Reg");
			if (false == (
					($this->timetableStacData["pmUser_updId"] == $this->promedUserId) ||
					isCZAdmin() ||
					isLpuRegAdmin($this->sessionParams["org_id"]) ||
					isInetUser($this->timetableStacData["pmUser_updId"])
				)) {
				throw new Exception("У вас нет прав отменить запись на прием, <br/>так как она сделана не вами.", 400);
			}
			if (!isSuperAdmin() && $this->timetableStacData["TimetableStac_Day"] < $this->currentDay) {
				throw new Exception("У вас нет прав отменить запись на прием, <br/>так как запись была создана раньше текущего дня.", 400);
			}
		}
		if (in_array($this->scenario, ["returnToQueue"]) && !empty($this->TimetableGraf_id)) {
			if (empty($this->timetableGrafData["Person_id"])) {
				throw new Exception("Выбранная вами бирка уже свободна.", 400);
			}
			$this->load->helper("Reg");
			if (false == (
					($this->timetableGrafData["pmUser_updId"] == $this->promedUserId) ||
					isCZAdmin() ||
					isLpuRegAdmin($this->sessionParams["org_id"]) ||
					isInetUser($this->timetableGrafData["pmUser_updId"]) ||
					(isset($this->sessionParams["CurMedStaffFact_id"]) && $this->timetableGrafData["MedStaffFact_id"] == $this->sessionParams["CurMedStaffFact_id"])
				)) {
				throw new Exception("У вас нет прав отменить запись на прием, <br/>так как она сделана не вами.", 400);
			}
		}
		if (in_array($this->scenario, ["returnToQueue"]) && !empty($this->TimetableGraf_id) && !empty($this->timetableGrafData["Evn_id"])) {
			if (empty($this->timetableGrafData["Person_id"])) {
				throw new Exception("Выбранная вами бирка уже свободна.", 400);
			}
			$this->load->model($this->getRegistryModel(), "Registry_model");
			$Registry_id = $this->Registry_model->getRegistryIdForEvnVizit([
				"Evn_id" => $this->timetableGrafData["Evn_id"]
			]);
			if (!empty($Registry_id)) {
				throw new Exception("Освобождение бирки невозможно, поскольку прием уже осуществлен и посещение подано в реестр на оплату", 400);
			}
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = [])
	{
		parent::_beforeSave($data);
		if (in_array($this->scenario, ["returnToQueue"]) && !empty($this->TimetableGraf_id)) {
			$ttg_data = $this->timetableGrafData;
			/*
			 * p_TimeTableGraf_cancel не подходит, т.к.
			 * 1) при отмене/отклонении не записывается причина и записывается один и тот же статус Отменено
			 * 2) если бирка создана на человека без записи, то она не удаляется
			 * 3) при отмене постановки в очередь не записывается причина "Ошибочное направление"
			 * 4) не очищается ссылка на посещение Evn_id, которая сохраняется по задаче #64480
			 */
			if ($ttg_data["TimetableGraf_IsDop"] == 1 && empty($ttg_data["TimetableGraf_begTime"])) {
				// удалять бирку, если она создана на человека без записи
				if (empty($ttg_data["TimetableGraf_factTime"])) {
					$this->_params["TimetableGraf_id_for_del"] = $ttg_data["TimetableGraf_id"];
				} else {
					$this->swUpdate("TimetableGraf", [
						"TimetableGraf_id" => $ttg_data["TimetableGraf_id"],
						"pmUser_id" => $this->promedUserId,
						"EvnDirection_id" => null
					], true);
				}
			} else {
				// освобождаю бирку без использования p_TimetableGraf_upd, т.к. в ней нет работы с историей и есть изменение поля TimetableGraf_updDT
				$tmp = $this->swUpdate("TimetableGraf", [
					"TimetableGraf_id" => $ttg_data["TimetableGraf_id"],
					"pmUser_id" => $this->promedUserId,
					"EvnDirection_id" => null,
					"Evn_id" => null,
					"Person_id" => null,
					"RecClass_id" => null,
					"TimetableGraf_factTime" => null,
					"TimetableGraf_IsModerated" => null,
					"RecMethodType_id" => null
				], true);
				if (!empty($ttg_data["TimetableGraf_factTime"])) {
					// если был осуществлен приём, освобождая бирку, переносим талон на бирку без записи
					// осторожно, костыли!
					$tmp = $this->execCommonSP("p_TimetableGraf_ins", [
						"TimetableGraf_id" => [
							"value" => null,
							"out" => true,
							"type" => "bigint",
						],
						"RecClass_id" => 1,
						"TimetableGraf_IsDop" => 1,
						"TimetableType_id" => 1,
						"TimetableGraf_Time" => 0,
						"TimetableGraf_begTime" => null,
						"TimetableGraf_factTime" => $ttg_data["TimetableGraf_factTime"],
						"TimetableGraf_Day" => $ttg_data["TimetableGraf_Day"],
						"EvnDirection_id" => null,
						"Evn_id" => $ttg_data["Evn_id"],
						"MedStaffFact_id" => $ttg_data["MedStaffFact_id"],
						"Person_id" => $ttg_data["Person_id"],
						"pmUser_id" => $this->promedUserId,
					], "array_assoc");
					$query = "
						update EvnVizit
						set TimetableGraf_id = :TimetableGraf_id
						where evn_id = :EvnVizit_id
					";
					$queryParams = [
						"EvnVizit_id" => $ttg_data["Evn_id"],
						"TimetableGraf_id" => $tmp["TimetableGraf_id"]
					];
					$this->db->query($query, $queryParams);
				}
				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (false == empty($tmp[0]["Error_Msg"])) {
					throw new Exception($tmp[0]["Error_Msg"], 500);
				}
				// Обновляем кэш по дню
				$tmp = $this->execCommonSP("p_MedPersonalDay_recount", [
					"MedStaffFact_id" => $ttg_data["MedStaffFact_id"],
					"Day_id" => $ttg_data["TimetableGraf_Day"],
					"pmUser_id" => $this->promedUserId,
				], "array_assoc");
				if (empty($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (isset($tmp["Error_Msg"])) {
					throw new Exception($tmp["Error_Msg"], 500);
				}
				// Заносим изменения бирки в историю
				$tmp = $this->execCommonSP("p_AddTTGToHistory", [
					"TimeTableGraf_id" => $ttg_data["TimetableGraf_id"],
					"TimeTableGrafAction_id" => 3, // Освобождение бирки
					"pmUser_id" => $this->promedUserId,
				], "array_assoc");
				if (empty($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (isset($tmp["Error_Msg"])) {
					throw new Exception($tmp["Error_Msg"], 500);
				}
			}
			$query = "
				update EvnPL
				set
					EvnDirection_id = null,
					EvnDirection_Num = null,
					EvnDirection_setDT = null,
					PrehospDirect_id = null,
					Org_did = null,
					LpuSection_did = null,
					MedStaffFact_did = null,
					Diag_did = null
				where EvnDirection_id = :EvnDirection_id
			";
			$queryParams = ["EvnDirection_id" => $this->id];
			$this->db->query($query, $queryParams);
			$this->setAttribute("TimetableGraf_id", null);
			if (empty($this->LpuUnit_did)) {
				$this->setAttribute("LpuUnit_did", $ttg_data["LpuUnit_id"]);
			}
		}
		if (in_array($this->scenario, ["returnToQueue"]) && !empty($this->TimetableMedService_id)) {
			$ttms_data = $this->timetableMedServiceData;
			/*
			 * p_TimeTableMedService_cancel не подходит, т.к.
			 * 1) при отмене/отклонении не записывается причина и записывается один и тот же статус Отменено
			 * 2) если бирка создана на человека без записи, то она не удаляется
			 * 3) при отмене постановки в очередь не записывается причина "Ошибочное направление"
			 * 4) не очищается Evn_id
			 */
			// освобождаю бирку без использования p_TimeTableMedService_upd, т.к. в ней нет работы с историей и есть изменение поля TimeTableMedService_updDT
			$tmp = $this->swUpdate("TimetableMedService", [
				"TimetableMedService_id" => $ttms_data["TimetableMedService_id"],
				"pmUser_id" => $this->promedUserId,
				"EvnDirection_id" => null,
				"Evn_id" => null,
				"Person_id" => null,
				"RecClass_id" => null,
				"TimetableMedService_factTime" => null,
			], true);
			if (empty($tmp) || false == is_array($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (false == empty($tmp[0]["Error_Msg"])) {
				throw new Exception($tmp[0]["Error_Msg"], 500);
			}
			// Обновляем кэш по дню
			if (empty($ttms_data["UslugaComplexMedService_id"])) {
				$tmp = $this->execCommonSP("p_MedServiceDay_recount", [
					"MedService_id" => $ttms_data["MedService_id"],
					"Day_id" => $ttms_data["TimetableMedService_Day"],
					"pmUser_id" => $this->promedUserId,
				], "array_assoc");
			} else {
				$tmp = $this->execCommonSP("p_MedServiceUslugaComplexDay_recount", [
					"UslugaComplexMedService_id" => $ttms_data["UslugaComplexMedService_id"],
					"Day_id" => $ttms_data["TimetableMedService_Day"],
					"pmUser_id" => $this->promedUserId,
				], "array_assoc");
			}
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"], 500);
			}
			// Заносим изменения бирки в историю
			$tmp = $this->execCommonSP("p_AddTTMSToHistory", [
				"TimetableMedService_id" => $ttms_data["TimetableMedService_id"],
				"TimeTableActionType_id" => 3, // Освобождение бирки
				"pmUser_id" => $this->promedUserId,
			], "array_assoc");
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"], 500);
			}
			$this->setAttribute("TimetableMedService_id", null);
			if (empty($this->LpuUnit_did)) {
				$this->setAttribute("LpuUnit_did", $ttms_data["LpuUnit_id"]);
			}
		}
		if (in_array($this->scenario, ["returnToQueue"]) && !empty($this->TimetableResource_id)) {
			$ttr_data = $this->timetableResourceData;
			if ($ttr_data["TimetableResource_IsDop"] == 1 && empty($ttr_data["TimetableResource_begTime"])) {
				// удалять бирку, если она создана на человека без записи
				$this->_params["TimetableResource_id_for_del"] = $ttr_data["TimetableResource_id"];
			} else {
				$tmp = $this->swUpdate("TimetableResource", [
					"TimetableResource_id" => $ttr_data["TimetableResource_id"],
					"pmUser_id" => $this->promedUserId,
					"EvnDirection_id" => null,
					"Evn_id" => null,
					"Person_id" => null,
					"RecClass_id" => null
				], true);
				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (false == empty($tmp[0]["Error_Msg"])) {
					throw new Exception($tmp[0]["Error_Msg"], 500);
				}
				// Обновляем кэш по дню
				$tmp = $this->execCommonSP("p_ResourceDay_recount", [
					"Resource_id" => $ttr_data["Resource_id"],
					"Day_id" => $ttr_data["TimetableResource_Day"],
					"pmUser_id" => $this->promedUserId,
				], "array_assoc");
				if (empty($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (isset($tmp["Error_Msg"])) {
					throw new Exception($tmp["Error_Msg"], 500);
				}
				// Заносим изменения бирки в историю
				$tmp = $this->execCommonSP("p_AddTTRToHistory", [
					"TimetableResource_id" => $ttr_data["TimetableResource_id"],
					"TimeTableActionType_id" => 3, // Освобождение бирки
					"pmUser_id" => $this->promedUserId,
				], "array_assoc");
				if (empty($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (isset($tmp["Error_Msg"])) {
					throw new Exception($tmp["Error_Msg"], 500);
				}
			}
			$this->setAttribute("TimetableResource_id", null);
		}
		if (in_array($this->scenario, ["returnToQueue"]) && !empty($this->TimetableStac_id)) {
			$tts_data = $this->timetableStacData;
			/*
			 * p_TimeTableStac_cancel не подходит, т.к.
			 * 1) при отмене/отклонении не записывается причина и записывается один и тот же статус Отменено
			 * 2) при отмене постановки в очередь не записывается причина "Ошибочное направление"
			 * 3) не очищается Evn_id
			 */
			// освобождаю бирку без использования p_TimetableStac_upd, т.к. в ней нет работы с историей и есть изменение поля TimetableStac_updDT
			$tmp = $this->swUpdate("TimetableStac", [
				"TimetableStac_id" => $tts_data["TimetableStac_id"],
				"pmUser_id" => $this->promedUserId,
				"EvnDirection_id" => null,
				"Evn_id" => null,
				"Person_id" => null,
				"RecClass_id" => null,
			], true);
			if (empty($tmp) || false == is_array($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (false == empty($tmp[0]["Error_Msg"])) {
				throw new Exception($tmp[0]["Error_Msg"], 500);
			}
			// Обновляем кэш по дню
			$tmp = $this->execCommonSP("p_LpuSectionDay_recount", [
				"LpuSection_id" => $tts_data["LpuSection_id"],
				"Day_id" => $tts_data["TimetableStac_Day"],
				"pmUser_id" => $this->promedUserId,
			], "array_assoc");
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"], 500);
			}
			// Заносим изменения бирки в историю
			$tmp = $this->execCommonSP("p_AddTTSToHistory", [
				"TimetableStac_id" => $tts_data["TimetableStac_id"],
				"TimeTableActionType_id" => 3, // Освобождение бирки
				"pmUser_id" => $this->promedUserId,
			], "array_assoc");
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"], 500);
			}
			$this->setAttribute("TimetableStac_id", null);
			if (empty($this->LpuUnit_did)) {
				$this->setAttribute("LpuUnit_did", $tts_data["LpuUnit_id"]);
			}
		}
		if (in_array($this->scenario, ["returnToQueue"])) {
			// создаём объект очереди если его нет
			if (empty($this->EvnQueue_id)) {
				$this->load->model("EvnQueue_model");
				$result = $this->EvnQueue_model->doSave([
					"scenario" => self::SCENARIO_DO_SAVE,
					"session" => $this->sessionParams,
					"EvnQueue_id" => null,
					"Lpu_id" => $this->Lpu_id,
					"Server_id" => $this->Server_id,
					"PersonEvn_id" => $this->PersonEvn_id,
					"EvnQueue_setDT" => $this->setDT,
					"LpuSectionProfile_did" => $this->LpuSectionProfile_id,
					"LpuUnit_did" => $this->LpuUnit_did,
					"EvnDirection_id" => $this->id,
					"MedService_did" => $this->MedService_id,
					"Resource_did" => $this->Resource_id,
					"MedPersonal_did" => $this->MedPersonal_did,
					"LpuSection_did" => $this->LpuSection_did,
					"RecMethodType_id" => $this->RecMethodType_id
				], false);
				if (!empty($result["EvnQueue_id"])) {
					$this->setAttribute("EvnQueue_id", $result["EvnQueue_id"]);
				} else if (isset($result["Error_Msg"])) {
					throw new Exception($result["Error_Msg"], $result["Error_Code"]);
				} else {
					throw new Exception("Ошибка сохранения объекта очереди", 500);
				}
			} else {

				$updateData = array(
					'key_field' => 'Evn_id',
					'Evn_id' => $this->EvnQueue_id,
					'LpuUnit_did' => $this->LpuUnit_did,
					'pmUser_recID' => null,
					'EvnQueue_recDT' => null,
					'TimeTableGraf_id' => null,
					'TimeTableMedService_id' => null,
					'TimeTableResource_id' => null,
					'TimeTablePar_id' => null,
					'TimeTableStac_id' => null
				);

				$EvnQueueData = $this->getFirstRowFromQuery("
					select
						RecMethodType_id as \"RecMethodType_id\",
						EvnQueueStatus_id as \"EvnQueueStatus_id\",
						Person_id as \"Person_id\",
						pmUser_insID as \"pmUser_insID\"
					from v_EvnQueue
					where EvnQueue_id = :EvnQueue_id
					limit 1
				", array('EvnQueue_id' => $this->EvnQueue_id));

				// если используется сценарий возврата в очередь для ЛО,
				// и тип записи в очередь "Портал" или "МП"
				// то ставим соответсвующий статус
				if (!empty($EvnQueueData['RecMethodType_id']) && in_array($EvnQueueData['RecMethodType_id'],array(1,2))
					&& !empty($EvnQueueData['EvnQueueStatus_id'])
				) {
					// изменяем статус на "Ожидает подтверждения"
					$EvnQueueStatus_id = 1;

					$updateData = array_merge($updateData,
						array(
							'EvnQueueStatus_id' => $EvnQueueStatus_id,
							'QueueFailCause_id' => null,
							'pmUser_failID' => null,
							'EvnQueue_failDT' => null,
						)
					);
				}
				// чистим поля, которые могли измениться при записи из очереди
				$tmp = $this->swUpdate('EvnQueue', $updateData, false);

				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (false == empty($tmp[0]["Error_Msg"])) {
					throw new Exception($tmp[0]["Error_Msg"], 500);
				}

				if (!empty($EvnQueueData) && !empty($EvnQueueStatus_id) && $EvnQueueStatus_id == 1) {
					$this->load->model("Queue_model");
					$this->Queue_model->sendNotify(array(
						'notify_type' => 'rollback',
						'Person_id' => $EvnQueueData['Person_id'],
						'pmUser_id' => $this->promedUserId
					));
				}
			}
			$this->setAttribute('EvnQueue_id', $this->EvnQueue_id);
		}
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		// перекрываю _updateMorbus в parent::_afterSave
		if (!empty($this->_params["TimetableGraf_id_for_del"])) {
			$tmp = $this->execCommonSP("p_TimetableGraf_del", [
				"pmUser_id" => $this->promedUserId,
				"TimetableGraf_id" => $this->_params["TimetableGraf_id_for_del"]
			], "array_assoc");
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"], 500);
			}
			unset($this->_params["TimetableGraf_id_for_del"]);
		}
		if (!empty($this->_params["TimetableMedService_id_for_del"])) {
			$tmp = $this->execCommonSP("p_TimetableMedService_del", [
				"pmUser_id" => $this->promedUserId,
				"TimetableMedService_id" => $this->_params["TimetableMedService_id_for_del"]
			], "array_assoc");
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"], 500);
			}
			unset($this->_params["TimetableMedService_id_for_del"]);
		}
		if (!empty($this->_params["TimetableStac_id_for_del"])) {
			$tmp = $this->execCommonSP("p_TimetableStac_del", [
				"pmUser_id" => $this->promedUserId,
				"TimetableStac_id" => $this->_params["TimetableStac_id_for_del"]
			], "array_assoc");
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"], 500);
			}
			unset($this->_params["TimetableStac_id_for_del"]);
		}
		if (!empty($this->_params["TimetableResource_id_for_del"])) {
			$tmp = $this->execCommonSP("p_TimetableResource_del", [
				"pmUser_id" => $this->promedUserId,
				"TimetableResource_id" => $this->_params["TimetableResource_id_for_del"]
			], "array_assoc");
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"], 500);
			}
			unset($this->_params["TimetableResource_id_for_del"]);
		}
		if ("returnToQueue" == $this->scenario) {
			$this->setStatus([
				"Evn_id" => $this->id,
				"EvnStatus_SysNick" => self::EVN_STATUS_DIRECTION_IN_QUEUE,
				"EvnClass_id" => $this->evnClassId,
				"pmUser_id" => $this->promedUserId,
			]);
		}
		// если направление было связано с EvnLabRequest, нужно перекешировать EvnLabRequest_prmTime - время записи
		$query = "
			UPDATE EvnLabRequest elr
			SET EvnLabRequest_prmTime = ttms.TimetableMedService_begTime
			FROM v_TimetableMedService_lite ttms
			WHERE
				elr.EvnDirection_id = :EvnDirection_id
				AND ttms.EvnDirection_id = elr.EvnDirection_id
		";
		$queryParams = ["EvnDirection_id" => $this->id];
		$this->db->query($query, $queryParams);

		$this->load->model('ApprovalList_model');
		$this->ApprovalList_model->saveApprovalList(array(
			'ApprovalList_ObjectName' => 'EvnDirection',
			'ApprovalList_ObjectId' => $this->id,
			'pmUser_id' => $this->promedUserId
		));
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]["alias"] = "EvnDirection_id";
		$arr[self::ID_KEY]["label"] = "Идентификатор события выписки направления";
		$arr["pid"]["alias"] = "EvnDirection_pid";
		$arr["setdate"]["label"] = "Дата выписки направления";
		$arr["setdate"]["alias"] = "EvnDirection_setDate";
		$arr["settime"]["label"] = "Время выписки направления";
		$arr["settime"]["alias"] = "EvnDirection_setTime";
		$arr["diddt"]["alias"] = "EvnDirection_didDT";
		$arr["disdt"]["alias"] = "EvnDirection_disDT";
		$arr["statusdate"]["alias"] = "EvnDirection_statusDate";
		$arr["istransit"]["properties"][] = self::PROPERTY_NOT_LOAD;// нет во вьюхе v_EvnDirection_all
		$arr["lpusectionprofile_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// профиль направления (в полке === профилю отделения, в которое направлен)
			"alias" => "LpuSectionProfile_id",
		];
		$arr["medservice_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// служба, в которую направлен
			"alias" => "MedService_id",
		];
		$arr["lpusection_did"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// отделение, в которое направлен
			"alias" => "LpuSection_did",
		];
		$arr["prehosptype_did"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// Куда направили
			"alias" => "PrehospType_did",
		];
		$arr["lpuunit_did"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// подразделение, в которое направлен
			"alias" => "LpuUnit_did",
		];
		$arr["medpersonal_did"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// врач, к которому направлен
			"alias" => "MedPersonal_did",
		];
		$arr["lpu_did"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// МО, в которую направлен
			"alias" => "Lpu_did",
		];
		$arr["lpu_sid"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			], // Направившее ЛПУ
			"alias" => "Lpu_sid",
		];
		$arr["org_sid"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			], // Направившая организация
			"alias" => "Org_sid",
		];
		$arr["lpusection_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// отделение того, кто направил; направившее отделение
			"alias" => "LpuSection_id",
		];
		$arr["medpersonal_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// кто направил; направивший врач
			"alias" => "MedPersonal_id",
		];
		$arr["medstafffact_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// кто направил; направивший врач
			"alias" => "MedStaffFact_id",
		];
		$arr["medpersonal_zid"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// зав.отделением того, кто направил; Заведующий отделением
			"alias" => "MedPersonal_zid",
		];
		$arr["lpu_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// МО того, кто создал направление, до добавления поля Lpu_sid Направившее МО
			"alias" => "Lpu_id",
		];
		$arr["dirtype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			], // тип направления
			"alias" => "DirType_id",
		];
		$arr["descr"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			], // Описание направления
			"alias" => "EvnDirection_Descr",
		];
		$arr["num"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_NULL,
			], // Номер направления
			"alias" => "EvnDirection_Num",
		];
		$arr["diag_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "Diag_id",
		];
		$arr["desdt"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			], // желаемая дата направления
			"alias" => "EvnDirection_desDT",
		];
		$arr["isauto"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],// Признак системного направления
			"alias" => "EvnDirection_IsAuto",
		];
		$arr["iscito"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			], // Срочность
			"alias" => "EvnDirection_IsCito",
		];
		$arr["post_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			], // должность врача, который направил
			"alias" => "Post_id",
		];
		$arr["prehospdirect_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			], // Кем направлен
			"alias" => "PrehospDirect_id",
		];
		$arr["timetablegraf_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "TimetableGraf_id",
		];
		$arr["timetablestac_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "TimetableStac_id",
		];
		$arr["timetablepar_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			], // не используется
			"alias" => "TimeTablePar_id",
		];
		$arr["timetablemedservice_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			], // Расписание службы
			"alias" => "TimetableMedService_id",
		];
		$arr["evnqueue_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "EvnQueue_id",
			"label" => "Идентификатор записи о постановке в очередь",
			"save" => "trim",
			"type" => "id"
		];
		$arr["dirfailtype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// причина отмены
			"alias" => "DirFailType_id",
		];
		$arr["faildt"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],// дата отмены
			"alias" => "EvnDirection_failDT",
		];
		$arr["pmuser_failid"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// кто отменил
			"alias" => "pmUser_failID",
		];
		$arr["ser"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],// серия направления
			"alias" => "EvnDirection_Ser",
		];
		$arr["isconfirmed"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],// подтверждено
			"alias" => "EvnDirection_IsConfirmed",
		];
		$arr["pmuser_confid"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// кто подтвердил
			"alias" => "pmUser_confID",
		];
		$arr["confdt"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],// дата подтверждения
			"alias" => "EvnDirection_confDT",
		];
		$arr["isreceive"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			], // признак создания направления принимающей стороной
			"alias" => "EvnDirection_IsReceive",
		];
		$arr["paytype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM
			], // тип оплаты
			"alias" => "PayType_id",
		];
		$arr["remoteconsultcause_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			], // Цель консультации, обязательна для направления в ЦУК
			"alias" => "RemoteConsultCause_id",
		];
		$arr["resource_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			], //
			"alias" => "Resource_id",
		];
		$arr["timetableresource_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			], //
			"alias" => "TimetableResource_id",
		];
		$arr["isneedoper"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			], //
			"alias" => "EvnDirection_IsNeedOper",
		];
		$arr["armtype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "ARMType_id",
		];
		$arr["evncourse_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "EvnCourse_id",
		];
		$arr["recmethodtype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "RecMethodType_id",
		];
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 27;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return "EvnDirection";
	}

	/**
	 * Определение имени представления данных как электронных, так и системных направлений
	 * Представление v_EvnDirection возвращает данные только электронных направлений
	 * @return string
	 */
	protected function viewName()
	{
		return "v_" . $this->tableName() . "_all";
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsCito($id, $value = null)
	{
		return $this->_updateAttribute($id, "iscito", $value);
	}

	/**
	 * Обработка госпитализации по направлению
	 * Должна выполняться внутри транзакции
	 * @param EvnPS_model $eps
	 * @return array
	 * @throws Exception
	 */
	function onCreateEvnPS(EvnPS_model $eps)
	{
		if (!empty($eps->EvnDirection_id) && $eps->isNewRecord) {
			// КВС создана и теперь нужно обновить запись в очереди или связать КВС с биркой
			$this->setParams(["session" => $eps->sessionParams]);
			$this->setAttributes(["EvnDirection_id" => $eps->EvnDirection_id]);
			if (isset($this->TimetableStac_id)) {
				// надо связать КВС с биркой, по аналогии с приемом в поликлинике по записи
				$this->load->model("TimetableStac_model");
				$this->TimetableStac_model->doSave([
					"scenario" => self::SCENARIO_SET_ATTRIBUTE,
					"session" => $eps->sessionParams,
					"TimetableStac_id" => $this->TimetableStac_id,
					"Evn_id" => $eps->id,
				], false);
			}
		}
		return $this->_saveResponse;
	}

	/**
	 * Обработка смены направления в КВС
	 * Должна выполняться внутри транзакции
	 * @param EvnPS_model $eps
	 * @throws Exception
	 */
	function onBeforeSetAnotherDirectionEvnPS(EvnPS_model $eps)
	{
		if (!empty($eps->_savedData["evndirection_id"]) && $eps->_savedData["evndirection_id"] != $eps->EvnDirection_id) {
			$this->setParams(["session" => $eps->sessionParams]);
			$this->setAttributes(["EvnDirection_id" => $eps->_savedData["evndirection_id"]]);
			switch (true) {
				case (in_array($this->EvnStatus_id, [13]) && !empty($eps->PrehospWaifRefuseCause_id)): // если был отказ, отменяем статус отклонено
				case (in_array($this->EvnStatus_id, [14, 15])): // если был обслужен, отменяем статус обслужено
				case (empty($this->EvnStatus_id)): // если статус не указан, ставим правильный статус
					$evnstatus_sysnick = $this->TimetableStac_id ? self::EVN_STATUS_DIRECTION_RECORDED : self::EVN_STATUS_DIRECTION_IN_QUEUE;
					break;
				default:
					// не отменяем никакой статус
					$evnstatus_sysnick = null;
					break;
			}
			if (isset($evnstatus_sysnick)) {
				$this->setStatus([
					"Evn_id" => $eps->_savedData["evndirection_id"],
					"EvnStatus_SysNick" => $evnstatus_sysnick,
					"EvnClass_id" => $this->evnClassId,
					"pmUser_id" => $eps->promedUserId,
				]);
			}
		}
	}

	/**
	 * Обработка отмены госпитализации по направлению (перед удалением КВС)
	 * Должна выполняться внутри транзакции
	 * @param EvnPS_model $eps
	 * @return array
	 * @throws Exception
	 */
	function onBeforeDeleteEvnPS(EvnPS_model $eps)
	{
		if (!empty($eps->EvnDirection_id)) {
			$this->setParams(["session" => $eps->sessionParams]);
			$this->setAttributes(["EvnDirection_id" => $eps->EvnDirection_id]);
			switch (true) {
				case (in_array($this->EvnStatus_id, [13]) && !empty($eps->PrehospWaifRefuseCause_id)): // если был отказ, отменяем статус отклонено
				case (in_array($this->EvnStatus_id, [14, 15])): // если был обслужен, отменяем статус обслужено
				case (empty($this->EvnStatus_id)): // если статус не указан, ставим правильный статус
					$evnstatus_sysnick = $this->TimetableStac_id ? self::EVN_STATUS_DIRECTION_RECORDED : self::EVN_STATUS_DIRECTION_IN_QUEUE;
					break;
				default:
					// не отменяем никакой статус
					$evnstatus_sysnick = null;
					break;
			}
			if (isset($evnstatus_sysnick)) {
				// используем setStatus, а не rollbackStatus, т.к. в хранимке удаления КВС сделана хрень с дублированием последнего статуса направления
				$this->setStatus([
					"Evn_id" => $eps->EvnDirection_id,
					"EvnStatus_SysNick" => $evnstatus_sysnick,
					"EvnClass_id" => $this->evnClassId,
					"pmUser_id" => $eps->promedUserId,
				]);
			}
		}
		if (!empty($eps->EmergencyData_id)) {
			// Если человек был принят по экстр.бирке, то отменяем идентификацию в ЛПУ
			$this->load->model("EmergencyData_model");
			$result = $this->EmergencyData_model->doSave([
				"scenario" => self::SCENARIO_SET_ATTRIBUTE,
				"session" => $eps->sessionParams,
				"EmergencyData_id" => $eps->EmergencyData_id,
				"Person_lid" => null,
			], false);
			if (!empty($result["Error_Msg"])) {
				return $result;
			}
		}
		if ($this->regionNick == "astra") {
			// выполняем переидентификацию.
			if (!empty($eps->EvnDirection_id)) {
				$this->load->model("EvnDirectionExt_model");
				$this->EvnDirectionExt_model->reidentEvnDirectionExt([
					"EvnDirection_id" => $eps->EvnDirection_id,
					"pmUser_id" => $this->promedUserId
				]);
			}
		}
		return $this->_saveResponse;
	}

	/**
	 * Загрузка списка направлений для разных вариантов использования
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnDirectionList($data)
	{
		if (empty($data["useCase"])) {
			return false;
		}
		if (empty($data["Person_id"])) {
			return false;
		}
		$queryParams = [
			"Person_id" => $data["Person_id"],
			"Lpu_did" => $data["Lpu_id"],
		];
		$filter = "";
		$select = [
			"MedicalCareFormType_id" => "ED.MedicalCareFormType_id as \"MedicalCareFormType_id\"",
			"EvnDirection_id" => "ED.EvnDirection_id as \"EvnDirection_id\"",
			"EvnDirection_IsAuto" => "coalesce(ED.EvnDirection_IsAuto, 1) as \"EvnDirection_IsAuto\"",
			"EvnDirection_IsReceive" => "coalesce(ED.EvnDirection_IsReceive, 1) as \"EvnDirection_IsReceive\"",
			"EvnDirection_setDate" => "to_char(ED.EvnDirection_setDate, '{$this->dateTimeForm104}') as \"EvnDirection_setDate\"",
			"EvnDirection_Num" => "ED.EvnDirection_Num as \"EvnDirection_Num\"",
			"LpuSection_id" => "sLS.LpuSection_id as \"LpuSection_id\"",
			"MedStaffFact_id" => "sMSF.MedStaffFact_id as \"MedStaffFact_id\"",
			"Diag_id" => "ED.Diag_id as \"Diag_id\"",
			"Diag_Name" => "rtrim(Diag.Diag_FullName) as \"Diag_Name\"",
			"DirType_id" => "DT.DirType_id as \"DirType_id\"",
			"DirType_Name" => "rtrim(DT.DirType_Name) as \"DirType_Name\"",
			"Lpu_sid" => "coalesce(ED.Lpu_sid, sLS.Lpu_id, ED.Lpu_id) as \"Lpu_sid\"",
			"Lpu_id" => "coalesce(ED.Lpu_sid, ED.Lpu_id) as \"Lpu_id\"",
			"EvnStatus_id" => "ED.EvnStatus_id as \"EvnStatus_id\"",
			"Org_id" => "coalesce(ED.Org_sid, Lpu.Org_id) as \"Org_id\"",
			"TimetableGraf_id" => "TTG.TimetableGraf_id as \"TimetableGraf_id\"",
			"TimetableStac_id" => "TTS.TimetableStac_id as \"TimetableStac_id\"",
			"TimetableMedService_id" => "TTMS.TimetableMedService_id as \"TimetableMedService_id\"",
			"TimetableResource_id" => "TTR.TimetableResource_id as \"TimetableResource_id\"",
			"EvnQueue_id" => "EQ.EvnQueue_id as \"EvnQueue_id\"",
			"EmergencyData_CallNum" => "null as \"EmergencyData_CallNum\"",
			"Lpu_Name" => "rtrim(Org.Org_Nick) as \"Lpu_Name\"",
			"UslugaComplex_id" => "null as \"UslugaComplex_id\"",
			"UslugaComplex_Name" => "null as \"UslugaComplex_Name\"",
			"LpuSectionProfile_Name" => "rtrim(LSP.LpuSectionProfile_Name) as \"LpuSectionProfile_Name\"",
			"Timetable_begTime" => "
				case when coalesce(TTG.TimetableGraf_begTime, TTMS.TimetableMedService_begTime, TTR.TimetableResource_begTime, TTS.TimetableStac_setDate) is not null
					then to_char(COALESCE(TTG.TimetableGraf_begTime, TTMS.TimetableMedService_begTime, TTR.TimetableResource_begTime, TTS.TimetableStac_setDate), '{$this->dateTimeForm104}')||
					' '||to_char(COALESCE(TTG.TimetableGraf_begTime, TTMS.TimetableMedService_begTime, TTR.TimetableResource_begTime, TTS.TimetableStac_setDate), '{$this->dateTimeForm108}')
					else null
				end as \"Timetable_begTime\"
			",
			"enabled" => "2 as \"enabled\"",
			"EvnDirection_IsVMP" => "case when ed.DirType_id = 19 then 'true' else 'false' end as \"EvnDirection_IsVMP\"",
		];
		$join = [
			"sMSF" => "left join v_MedStaffFact sMSF on sMSF.MedStaffFact_id = ED.MedStaffFact_id and sMSF.Lpu_id = coalesce(ED.Lpu_sid, sMSF.Lpu_id)",
			"sLS" => "left join v_LpuSection sLS on sLS.LpuSection_id = coalesce(sMSF.LpuSection_id, ED.LpuSection_id) and sLS.Lpu_id = coalesce(ED.Lpu_sid, sLS.Lpu_id)",
			"Lpu" => "left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ED.Lpu_sid, sLS.Lpu_id, ED.Lpu_id)",
			"Org" => "left join v_Org Org on Org.Org_id = coalesce(ED.Org_sid, Lpu.Org_id)",
			"DT" => "left join DirType DT on DT.DirType_id = ED.DirType_id",
			"LSP" => "left join LpuSectionProfile LSP on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id",
			"Diag" => "left join v_Diag Diag on Diag.Diag_id = ED.Diag_id",
			"TTG" => "left join lateral (
					select TimetableGraf_begTime, MedStaffFact_id, TTG.TimetableGraf_id
					from v_TimetableGraf_lite TTG
					where TTG.EvnDirection_id = ED.EvnDirection_id
					limit 1
				) as TTG on true
			",
			"TTS" => "left join lateral (
					select TimetableStac_setDate, TTS.TimetableStac_id, TTS.EmergencyData_id
					from v_TimetableStac_lite TTS
					where TTS.EvnDirection_id = ED.EvnDirection_id
					limit 1
				) as TTS on true
			",
			"TTMS" => "left join lateral (
					select TTMS.TimetableMedService_begTime, TTMS.TimetableMedService_id
					from v_TimetableMedService_lite TTMS
					where TTMS.EvnDirection_id = ED.EvnDirection_id
					limit 1
				) as TTMS on true
			",
			"TTR" => "left join lateral (
					select TTR.TimetableResource_begTime, TTR.TimetableResource_id
					from v_TimetableResource_lite TTR
					where TTR.EvnDirection_id = ED.EvnDirection_id
					limit 1
				) as TTR on true
			",
			"EQ" => "left join lateral (
					select EQ.Evn_id as EvnQueue_id, EQ.LpuSectionProfile_did
					from EvnQueue EQ
					where EQ.EvnDirection_id = ED.EvnDirection_id
						and EQ.EvnQueue_recDT is null
						and EQ.EvnQueue_failDT is null
					limit 1
				) as EQ on true
			",
		];
		switch ($data["useCase"]) {
			case "load_evn_direction_all_info_panel":
				$select["EvnDirection_Descr"] = "ED.EvnDirection_Descr as \"EvnDirection_Descr\"";
				$select["LpuSectionProfile_Code"] = "LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\"";
				unset($join["TTS"]);
				$select["TimetableStac_id"] = "null as \"TimetableStac_id\"";
				unset($join["TTMS"]);
				$select["TimetableMedService_id"] = "null as \"TimetableMedService_id\"";
				unset($join["TTR"]);
				$select["TimetableResource_id"] = "null as \"TimetableResource_id\"";
				unset($join["EQ"]);
				$select["EvnQueue_id"] = "null as \"EvnQueue_id\"";
				$select["Timetable_begTime"] = "
					case when TTG.TimetableGraf_begTime is not null
						then to_char(TTG.TimetableGraf_begTime, '{$this->dateTimeForm104}')||' '||to_char(TTG.TimetableGraf_begTime, '{$this->dateTimeForm108}')
						else null
					end as \"Timetable_begTime\"
				";
				$join["MP"] = "left join v_MedPersonal MP on MP.MedPersonal_id = ED.MedPersonal_id and MP.Lpu_id = ED.Lpu_id";
				$select["MedPersonal_id"] = "ED.MedPersonal_id as \"MedPersonal_id\"";
				$select["MedPersonal_Fio"] = "coalesce(MP.Person_Fio, '') as \"MedPersonal_Fio\"";
				$join["MPZ"] = "left join v_MedPersonal MPZ on MPZ.MedPersonal_id = ED.MedPersonal_zid and MPZ.Lpu_id = ED.Lpu_id";
				$select["MedPersonal_zid"] = "ED.MedPersonal_zid as \"MedPersonal_zid\"";
				$select['MedPersonal_zFio'] = "coalesce(MPZ.Person_Fio, '') as \"MedPersonal_zFio\"";
				if (isset($data["EvnDirection_id"])) {
					$queryParams["EvnDirection_id"] = $data["EvnDirection_id"];
					$filter .= " and ED.EvnDirection_id = :EvnDirection_id";
				} else if (isset($data["TimetableGraf_id"])) {
					$queryParams["TimetableGraf_id"] = $data["TimetableGraf_id"];
					$join["TTG"] = "
						inner join lateral (
							select TTG.TimetableGraf_begTime, MedStaffFact_id, TTG.TimetableGraf_id
							from v_TimetableGraf_lite TTG
							where TTG.EvnDirection_id = ED.EvnDirection_id
							  and TTG.TimetableGraf_id = :TimetableGraf_id
							limit 1
						) as TTG on true
					";
				} elseif (
					isset($data["parentClass"]) &&
					in_array($data["parentClass"], ["EvnPL", "EvnPLStom"]) &&
					isset($data["Evn_id"])
				) {
					$filter .= " and ED.Lpu_did = :Lpu_did";
					$queryParams["Evn_id"] = $data["Evn_id"];
					$filter .= "
						and exists (
							select e.EvnDirection_id
							from v_{$data['parentClass']} e
							where e.{$data['parentClass']}_id = :Evn_id
							  and e.EvnDirection_id = ED.EvnDirection_id
							limit 1
						)
					";
				} else {
					return [];
				}
				$filter .= " and coalesce(ED.EvnStatus_id, 16) not in (12,13)";
				$filter .= " and ED.DirFailType_id is null and ED.EvnDirection_failDT is null";
				break;
			case "addEvnVizitPLStom":
			case "addEvnVizitPL":
			case "load_data_for_auto_create_tap":
			case "load_data_for_create_tap_consult":
				unset($select["Diag_Name"]);
				unset($select["DirType_id"]);
				unset($select["DirType_Name"]);
				unset($select["Lpu_Name"]);
				$select["Timetable_begTime"] = "
					case when TTG.TimetableGraf_begTime is not null
						then to_char(TTG.TimetableGraf_begTime, '{$this->dateTimeForm104}')||' '||to_char(TTG.TimetableGraf_begTime, '{$this->dateTimeForm108}')
						else null
					end as \"Timetable_begTime\"
				";
				unset($join["TTS"]);
				$select["TimetableStac_id"] = "null as \"TimetableStac_id\"";
				unset($join["TTMS"]);
				$select["TimetableMedService_id"] = "null as \"TimetableMedService_id\"";
				unset($join["TTR"]);
				$select["TimetableResource_id"] = "null as \"TimetableResource_id\"";
				unset($join["EQ"]);
				$select["EvnQueue_id"] = "null as \"EvnQueue_id\"";
				unset($join["Diag"]);
				unset($join["DT"]);
				if (isset($data["EvnDirection_id"])) {
					$queryParams["EvnDirection_id"] = $data["EvnDirection_id"];
					$filter .= " and ED.EvnDirection_id = :EvnDirection_id";
				} else if (isset($data["TimetableGraf_id"])) {
					$queryParams["TimetableGraf_id"] = $data["TimetableGraf_id"];
					$filter .= " and ED.TimetableGraf_id = :TimetableGraf_id";
				} else {
					return [];
				}
				$filter .= " and ED.Lpu_did = :Lpu_did";
				if ("load_data_for_create_tap_consult" === $data["useCase"]) {
					$filter .= " and ED.DirType_id in (11)";
				}
				$filter .= "
					and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)
				";
				$filter .= "
					and ED.DirFailType_id is null and ED.EvnDirection_failDT is null
				";
				$join["MSF"] = "left join v_MedStaffFact MSF on MSF.MedStaffFact_id = TTG.MedStaffFact_id and MSF.Lpu_id = ED.Lpu_did";
				$select["MSF_Person_Fin"] = "MSF.Person_Fin as \"MSF_Person_Fin\"";
				break;
			case "choose_for_evnpl_stream_input": // выбираем для ТАП, но при операторском вводе (не из АРМа)
			case "choose_for_evnplstom_stream_input": // выбираем для ТАП, но при операторском вводе (не из АРМа)
				// получить список ЭН, если ТАП заводит оператор (раньше фильтровались только по Person_id, Lpu_did)
				$filter .= " and ED.Lpu_did = :Lpu_did";
				$filter .= " and coalesce(ED.EvnDirection_IsAuto, 1) = 1"; // только ЭН
				$filter .= " and ED.DirFailType_id is null and ED.EvnDirection_failDT is null";
				$filter .= " and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)";
				unset($join["TTS"]);
				$select["TimetableStac_id"] = "null as \"TimetableStac_id\"";
				unset($join["TTMS"]);
				$select["TimetableMedService_id"] = "null as \"TimetableMedService_id\"";
				unset($join["TTR"]);
				$select["TimetableResource_id"] = "null as \"TimetableResource_id\"";
				unset($join["EQ"]);
				$select["EvnQueue_id"] = "null as \"EvnQueue_id\"";
				$select["Timetable_begTime"] = "
					case when TTG.TimetableGraf_begTime is not null
						then to_char(TTG.TimetableGraf_begTime, '{$this->dateTimeForm104}')||' '||to_char(TTG.TimetableGraf_begTime, '{$this->dateTimeForm108}')
						else null
					end as \"Timetable_begTime\"
				";
				$join["MSF"] = "left join v_MedStaffFact MSF on MSF.MedStaffFact_id = TTG.MedStaffFact_id and MSF.Lpu_id = ED.Lpu_did";
				$select["MSF_Person_Fin"] = "MSF.Person_Fin as \"MSF_Person_Fin\"";
				break;
			case "choose_for_evnpl": // выбираем для ТАП из АРМа врача поликлиники
			case "choose_for_evnplstom": // выбираем для ТАП из АРМа стоматолога
			case "create_evnplstom_without_recording": // тоже самое, только выбираем при приеме без записи из формы АРМа врача
			case "create_evnpl_without_recording": // тоже самое, только выбираем при приеме без записи из формы АРМа врача
				// получить список направлений из очереди по профилю этого отделения в данное МО или записанных к этому врачу (на это рабочее место врача)
				if (empty($data["MedStaffFact_id"])) {
					return false;
				}
				$filter .= " and ED.Lpu_did = :Lpu_did";
				$filter .= " and ED.DirType_id in (2, 3, 4, 16)";
				$filter .= " and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)";
				$filter .= " and ED.DirFailType_id is null and ED.EvnDirection_failDT is null";
				$join["MSF"] = "inner join v_MedStaffFact MSF on MSF.MedStaffFact_id = :MedStaffFact_id and MSF.Lpu_id = ED.Lpu_did";
				$queryParams["MedStaffFact_id"] = $data["MedStaffFact_id"];
				unset($join["TTG"]);
				$select["TimetableGraf_id"] = "TTGEQ.TimetableGraf_id as \"TimetableGraf_id\"";
				unset($join["TTS"]);
				$select["TimetableStac_id"] = "null as \"TimetableStac_id\"";
				unset($join["TTMS"]);
				$select["TimetableMedService_id"] = "null as \"TimetableMedService_id\"";
				unset($join["TTR"]);
				$select["TimetableResource_id"] = "null as \"TimetableResource_id\"";
				unset($join["EQ"]);
				$select["EvnQueue_id"] = "TTGEQ.EvnQueue_id as \"EvnQueue_id\"";
				$join["TTGEQ"] = "
					inner join lateral (
						select
							TTG.EvnDirection_id,
							TTG.TimetableGraf_id,
							TTG.MedStaffFact_id as MedStaffFact_did,
							TTG.TimetableGraf_begTime,
							null as EvnQueue_id,
							MSFTTG.Person_Fin as MSF_Person_Fin
						from
							v_TimetableGraf_lite TTG
							inner join v_MedStaffFact MSFTTG on MSFTTG.MedStaffFact_id = TTG.MedStaffFact_id
						where TTG.EvnDirection_id = ED.EvnDirection_id
						  and MSFTTG.Lpu_id = MSF.Lpu_id
						  and ED.LpuSectionProfile_id = MSF.LpuSectionProfile_id
						  and coalesce(TTG.TimetableGraf_begTime, (tzgetdate() - ('3 months')::interval)) >= (tzgetdate() - ('3 months')::interval)
						union all
						select
							EQ.EvnDirection_id,
							null as TimetableGraf_id,
							null as MedStaffFact_did,
							null as TimetableGraf_begTime,
							EQ.Evn_id as EvnQueue_id,
							null as MSF_Person_Fin
						from EvnQueue EQ
						where EQ.EvnDirection_id = ED.EvnDirection_id
							and ED.LpuSectionProfile_id = MSF.LpuSectionProfile_id
							and EQ.EvnQueue_recDT is null
							and EQ.EvnQueue_failDT is null
						limit 1
					) as TTGEQ on true
				";
				$select["MedStaffFact_did"] = "TTGEQ.MedStaffFact_did as \"MedStaffFact_did\"";
				$select["Timetable_begTime"] = "
					case when TTGEQ.TimetableGraf_begTime is not null
						then to_char(TTGEQ.TimetableGraf_begTime, '{$this->dateTimeForm104} {$this->dateTimeForm108}')
						else null
					end as \"Timetable_begTime\"
				";
				$select["MSF_Person_Fin"] = "TTGEQ.MSF_Person_Fin as \"MSF_Person_Fin\"";
				break;
			case "choose_for_evnps_stream_input": // выбираем для КВС из форма редактирования КВС, но при операторском вводе (не из АРМа)
			case "self_treatment": // тоже самое, только выбираем при приеме без записи из рабочего места врача приемного отделения
				$filter .= " and ED.Lpu_did = :Lpu_did";
				$dirTypeArray = [1, 2, 4, 5, 6];
				if (in_array(getRegionNick(), ["penza", "perm"])) {
					$dirTypeArray[] = 19;
				}
				if (getRegionNick() == "perm") {
					$dirTypeArray[] = 27;
					$dirTypeArray[] = 28;
				}
				$dirTypeArrayString = implode(',', $dirTypeArray);
				$filter .= " and ED.DirType_id in ({$dirTypeArrayString})";
				$filter .= " and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)";
				$filter .= " and ED.DirFailType_id is null and ED.EvnDirection_failDT is null";
				unset($join["TTG"]);
				$select["TimetableGraf_id"] = "null as \"TimetableGraf_id\"";
				unset($join["TTMS"]);
				$select["TimetableMedService_id"] = "null as \"TimetableMedService_id\"";
				unset($join["TTR"]);
				$select["TimetableResource_id"] = "null as \"TimetableResource_id\"";
				$select['Timetable_begTime'] = "
					case when TTS.TimetableStac_setDate is not null
						then to_char(TTS.TimetableStac_setDate, '{$this->dateTimeForm104}')
						else null
					end as \"Timetable_begTime\"
				";
				if (in_array($data["useCase"], ["choose_for_evnps_stream_input", "self_treatment"])) {
					$filter .= " and coalesce(ED.EvnDirection_IsAuto, 1) = 1"; // только ЭН
				}
				if (in_array($data["useCase"], ["self_treatment"])) {
					$join["EmD"] = "left join EmergencyData EmD on TTS.EmergencyData_id = EmD.EmergencyData_id";
					$select["EmergencyData_CallNum"] = "EmD.EmergencyData_CallNum as \"EmergencyData_CallNum\"";
				}
				$join["EDH"] = "left join v_EvnDirectionHTM EDH on EDH.EvnDirectionHTM_id = ED.EvnDirection_id";
				$select["EvnDirectionHTM_id"] = "EDH.EvnDirectionHTM_id as \"EvnDirectionHTM_id\"";
				if ($this->regionNick == 'kz') {
					$select['PayType_id'] = 'ED.PayType_id as "PayType_id"';
				}
				break;
			case "choose_for_evnps": // выбираем для КВС из форма редактирования КВС, открытой из АРМа врача стационара
			case "create_evnps_from_workplacestac": // тоже самое, только выбираем при приеме из формы АРМа врача стационара
				// получить список направлений из очереди или записанных по профилю этого отделения в данное МО
				if (empty($data['LpuSection_id'])) {
					return false;
				}
				$filter .= " and ED.Lpu_did = :Lpu_did";

				$dirTypeArray = [1, 2, 4, 5, 6];
				if (in_array(getRegionNick(), ["penza", "perm"])) {
					$dirTypeArray[] = 19;
				}
				if (getRegionNick() == "perm") {
					$dirTypeArray[] = 27;
					$dirTypeArray[] = 28;
				}
				$dirTypeArrayString = implode(",", $dirTypeArray);
				$filter .= " and ED.DirType_id in ({$dirTypeArrayString})";
				$filter .= " and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)";
				$filter .= " and ED.DirFailType_id is null and ED.EvnDirection_failDT is null";
				if (in_array($data["useCase"], ["create_evnps_from_workplacestac", "choose_for_evnps"])) {
					$filter .= " and coalesce(ED.EvnDirection_IsAuto, 1) = 1"; // только ЭН
				}
				$join["HOSP"] = "inner join v_LpuSection HOSP on HOSP.LpuSection_id = :LpuSection_id and HOSP.Lpu_id = ED.Lpu_did";
				$queryParams["LpuSection_id"] = $data["LpuSection_id"];
				unset($join["TTG"]);
				$select["TimetableGraf_id"] = "null as \"TimetableGraf_id\"";
				unset($join["TTS"]);
				$select["TimetableStac_id"] = "TTSEQ.TimetableStac_id as \"TimetableStac_id\"";
				unset($join["TTMS"]);
				$select["TimetableMedService_id"] = "null as \"TimetableMedService_id\"";
				unset($join["TTR"]);
				$select["TimetableResource_id"] = "null as \"TimetableResource_id\"";
				unset($join["EQ"]);
				$select["EvnQueue_id"] = "TTSEQ.EvnQueue_id as \"EvnQueue_id\"";
				$join["TTSEQ"] = "
					inner join lateral (
						select TTS.EvnDirection_id, TTS.TimetableStac_setDate, TTS.TimetableStac_id, null as EvnQueue_id
						from
							v_TimetableStac_lite TTS
							inner join v_LpuSection LS on LS.LpuSection_id = TTS.LpuSection_id and LS.LpuSectionProfile_id = HOSP.LpuSectionProfile_id
						where TTS.EvnDirection_id = ED.EvnDirection_id
						union all
						select EQ.EvnDirection_id, null as TimetableStac_setDate, null as TimetableStac_id, EQ.Evn_id as EvnQueue_id
						from EvnQueue EQ
						where EQ.EvnDirection_id = ED.EvnDirection_id
						  and ED.LpuSectionProfile_id = HOSP.LpuSectionProfile_id
						  and EQ.EvnQueue_recDT is null
						  and EQ.EvnQueue_failDT is null
						union all
						select ED.EvnDirection_id, null as TimetableStac_setDate, null as TimetableStac_id, null as EvnQueue_id
						from DirType
						where DirType.DirType_id = 5
						  and DirType.DirType_id = ED.DirType_id
						limit 1
					) as TTSEQ on true
				";
				$select["Timetable_begTime"] = "
					case when TTSEQ.TimetableStac_setDate is not null
						then to_char(TTSEQ.TimetableStac_setDate, '{$this->dateTimeForm104}')
						else null
					end as \"Timetable_begTime\"
				";
				$join["EDH"] = "left join v_EvnDirectionHTM EDH on EDH.EvnDirectionHTM_id = ED.EvnDirection_id";
				$select["EvnDirectionHTM_id"] = "EDH.EvnDirectionHTM_id as \"EvnDirectionHTM_id\"";
				break;
			case "check_exists_dir_stac_in_evn":
				if (empty($data["Lpu_did"]) || empty($data["EvnDirection_pid"])) {
					return false;
				}
				$select = [
					"EvnDirection_id" => "ED.EvnDirection_id as \"EvnDirection_id\"",
				];
				$join = [];
				$queryParams["Lpu_did"] = $data["Lpu_did"];
				$filter .= " and ED.Lpu_did = :Lpu_did";
				$queryParams["EvnDirection_pid"] = $data["EvnDirection_pid"];
				$filter .= " and ED.EvnDirection_pid = :EvnDirection_pid";
				$filter .= " and ED.DirType_id in (1, 2, 4, 5, 6)";
				$filter .= " and ED.DirFailType_id is null and ED.EvnDirection_failDT is null";
				$filter .= " and coalesce(ED.EvnDirection_IsAuto, 1) = 1"; // только ЭН
				if (!empty($data["LpuSectionProfile_did"])) {
					$queryParams["LpuSectionProfile_did"] = $data["LpuSectionProfile_did"];
					$filter .= " and ED.LpuSectionProfile_id = :LpuSectionProfile_did";
				}
				break;
			case "choose_for_evnvizitpl_link":
				$filter .= " and ED.EvnDirection_pid is null";
				break;
		}
		$select = implode(",", $select);
		$join = implode(" ", $join);

		$unionall = "";
		if (in_array(getRegionNick(), ["penza", "perm"])) {
			$unionall = "
		        union all
				select
					ED.MedicalCareFormType_id,
					ED.EvnDirectionHTM_id as EvnDirection_id,
					ED.Lpu_sid,
					Lpu_id,
					Org_sid,
					19 as DirType_id,
					LpuSectionProfile_id,
					Diag_id,
					Lpu_did,
					EvnDirectionHTM_IsAuto as EvnDirection_IsAuto,
					EvnDirectionHTM_IsReceive as EvnDirection_IsReceive,
					EvnDirectionHTM_setDate as EvnDirection_setDate,
					EvnDirectionHTM_Num as EvnDirection_Num,
					LpuSection_id,
					EvnStatus_id,
					EvnDirectionHTM_Descr as EvnDirection_Descr,
					MedStaffFact_id,
					MedPersonal_id,
					MedPersonal_zid
				from v_EvnDirectionHTM ED
				where ED.Person_id = :Person_id
		    ";
		}
		$query = "
			with EvnDirectionAll as (
				select
					ED.MedicalCareFormType_id,
					ED.EvnDirection_id,
					ED.Lpu_sid,
					Lpu_id,
					Org_sid,
					DirType_id,
					LpuSectionProfile_id,
					Diag_id,
					Lpu_did,
					EvnDirection_IsAuto,
					EvnDirection_IsReceive,
					EvnDirection_setDate,
					EvnDirection_Num,
					LpuSection_id,
					EvnStatus_id,
					EvnDirection_Descr,
					MedStaffFact_id,
					MedPersonal_id,
					MedPersonal_zid
				from v_EvnDirection_all ED
				where ED.Person_id = :Person_id
				{$filter}
				{$unionall}
			)
			select {$select}
			from EvnDirectionAll ED
			{$join}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * МАРМ-версия
	 * Загрузка списка направлений для разных вариантов использования
	 */
	function mLoadEvnDirectionList($data)
	{
		if (empty($data['useCase'])) {
			return false;
		}
		if (empty($data['Person_id'])) {
			return false;
		}
		$prequery = "";
		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Lpu_did' => $data['Lpu_id'],
		);
		$filter = '';
		$select = array(
			'MedicalCareFormType_id' => 'ED.MedicalCareFormType_id as "MedicalCareFormType_id"',
			'EvnDirection_id' => 'ED.EvnDirection_id as "EvnDirection_id"',
			'EvnDirection_IsAuto' => 'coalesce(ED.EvnDirection_IsAuto,1) as "EvnDirection_IsAuto"',
			'EvnDirection_IsReceive' => 'coalesce(ED.EvnDirection_IsReceive,1) as "EvnDirection_IsReceive"',
			'EvnDirection_setDate' => 'to_char(ED.EvnDirection_setDate, \'DD.MM.YYYY\') as "EvnDirection_setDate"',
			'EvnDirection_Num' => 'ED.EvnDirection_Num as "EvnDirection_Num"',
			'LpuSection_id' => 'sLS.LpuSection_id as "LpuSection_id"',
			'MedStaffFact_id' => 'sMSF.MedStaffFact_id as "MedStaffFact_id"',
			'Diag_id' => 'ED.Diag_id as "Diag_id"',
			'Diag_Name' => 'RTRIM(Diag.Diag_FullName) as "Diag_Name"',
			'DirType_id' => 'DT.DirType_id as "DirType_id"',
			'DirType_Name' => 'RTRIM(DT.DirType_Name) as "DirType_Name"',
			/*'PrehospDirect_id' => 'case
				when 2 = coalesce(ED.EvnDirection_IsAuto,1) then null
				when :Lpu_did = coalesce(ED.Lpu_sid,ED.Lpu_id) then 1
				else 2 end
				as "PrehospDirect_id"',*/
			'Lpu_sid' => 'coalesce(ED.Lpu_sid,sLS.Lpu_id,ED.Lpu_id) as "Lpu_sid"',
			'Lpu_id' => 'coalesce(ED.Lpu_sid,ED.Lpu_id) as "Lpu_id"',
			'EvnStatus_id' => 'ED.EvnStatus_id as "EvnStatus_id"',
			'Org_id' => 'coalesce(ED.Org_sid,Lpu.Org_id) as "Org_id"',
			'TimetableGraf_id' => 'TTG.TimetableGraf_id as "TimetableGraf_id"',
			'TimetableStac_id' => 'TTS.TimetableStac_id as "TimetableStac_id"',
			'TimetableMedService_id' => 'TTMS.TimetableMedService_id as "TimetableMedService_id"',
			'TimetableResource_id' => 'TTR.TimetableResource_id as "TimetableResource_id"',
			'EvnQueue_id' => 'EQ.EvnQueue_id as "EvnQueue_id"',
			'EmergencyData_CallNum' => 'null as "EmergencyData_CallNum"',
			'Lpu_Name' => 'RTRIM(Org.Org_Nick) as "Lpu_Name"',
			'UslugaComplex_id' => 'null as "UslugaComplex_id"',
			'UslugaComplex_Name' => 'null as "UslugaComplex_Name"',
			'LpuSectionProfile_Name' => 'RTRIM(LSP.LpuSectionProfile_Name) as "LpuSectionProfile_Name"',
			'Timetable_begTime' => "case when COALESCE(TTG.TimetableGraf_begTime, TTMS.TimetableMedService_begTime, TTR.TimetableResource_begTime, TTS.TimetableStac_setDate) is not null
			then to_char(COALESCE(TTG.TimetableGraf_begTime, TTMS.TimetableMedService_begTime, TTR.TimetableResource_begTime, TTS.TimetableStac_setDate), 'DD.MM.YYYY HH24:MI') else null end as \"Timetable_begTime\"",
			'enabled' => '2 as "enabled"',
			'EvnDirection_IsVMP' => "case when ed.DirType_id = 19 then 'true' else 'false' end as \"EvnDirection_IsVMP\"",
		);
		$join = array(
			'sMSF' => 'left join v_MedStaffFact sMSF on sMSF.MedStaffFact_id = ED.MedStaffFact_id and sMSF.Lpu_id = coalesce(ED.Lpu_sid,sMSF.Lpu_id)',
			'sLS' => 'left join v_LpuSection sLS on sLS.LpuSection_id = coalesce(sMSF.LpuSection_id,ED.LpuSection_id) and sLS.Lpu_id = coalesce(ED.Lpu_sid,sLS.Lpu_id)',
			'Lpu' => 'left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ED.Lpu_sid,sLS.Lpu_id,ED.Lpu_id)',
			'Org' => 'left join v_Org Org on Org.Org_id = coalesce(ED.Org_sid,Lpu.Org_id)',
			'DT' => 'left join DirType DT on DT.DirType_id = ED.DirType_id',
			'LSP' => 'left join LpuSectionProfile LSP on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id',
			'Diag' => 'left join v_Diag Diag on Diag.Diag_id = ED.Diag_id',
			'TTG' => 'left join lateral (
					Select TimetableGraf_begTime, MedStaffFact_id, TTG.TimetableGraf_id from v_TimetableGraf_lite TTG
					where TTG.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTG on true',
			'TTS' => 'left join lateral (
					Select TimetableStac_setDate, TTS.TimetableStac_id, TTS.EmergencyData_id from v_TimetableStac_lite TTS
					where TTS.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTS on true',
			'TTMS' => 'left join lateral (
					Select TTMS.TimetableMedService_begTime, TTMS.TimetableMedService_id from v_TimetableMedService_lite TTMS
					where TTMS.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTMS on true',
			'TTR' => 'left join lateral (
					Select TTR.TimetableResource_begTime, TTR.TimetableResource_id from v_TimetableResource_lite TTR
					where TTR.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTR on true',
			'EQ' => 'left join lateral (
					select EQ.EvnQueue_id, EQ.LpuSectionProfile_did
					from EvnQueue EQ
					where EQ.EvnDirection_id = ED.EvnDirection_id
						and EQ.EvnQueue_recDT is null
						and EQ.EvnQueue_failDT is null
					limit 1
				) EQ on true',
		);

		switch ($data['useCase']) {
			case 'load_evn_direction_all_info_panel':
				$select['EvnDirection_Descr'] = 'ED.EvnDirection_Descr as "EvnDirection_Descr"';
				$select['LpuSectionProfile_Code'] = 'LSP.LpuSectionProfile_Code as "LpuSectionProfile_Code"';
				unset($join['TTS']);
				$select['TimetableStac_id'] = 'null as "TimetableStac_id"';
				unset($join['TTMS']);
				$select['TimetableMedService_id'] = 'null as "TimetableMedService_id"';
				unset($join['TTR']);
				$select['TimetableResource_id'] = 'null as "TimetableResource_id"';
				unset($join['EQ']);
				$select['EvnQueue_id'] = 'null as "EvnQueue_id"';
				$select['Timetable_begTime'] = "case when TTG.TimetableGraf_begTime is not null
			then to_char(TTG.TimetableGraf_begTime, 'DD.MM.YYYY')
			|| ' ' || to_char(TTG.TimetableGraf_begTime, 'HH24:MI') else null end as \"Timetable_begTime\"";
				$join['MP'] = 'left join v_MedPersonal MP on MP.MedPersonal_id = ED.MedPersonal_id and MP.Lpu_id = ED.Lpu_id';
				$select['MedPersonal_id'] = 'ED.MedPersonal_id as "MedPersonal_id"';
				$select['MedPersonal_Fio'] = "coalesce(MP.Person_Fio, '') as \"MedPersonal_Fio\"";
				$join['MPZ'] = 'left join v_MedPersonal MPZ on MPZ.MedPersonal_id = ED.MedPersonal_zid and MPZ.Lpu_id = ED.Lpu_id';
				$select['MedPersonal_zid'] = 'ED.MedPersonal_zid as "MedPersonal_zid"';
				$select['MedPersonal_zFio'] = "coalesce(MPZ.Person_Fio, '') as \"MedPersonal_zFio\"";

				if (isset($data['EvnDirection_id'])) {
					$queryParams['EvnDirection_id'] = $data['EvnDirection_id'];
					$filter .= ' and ED.EvnDirection_id = :EvnDirection_id';
				} else if (isset($data['TimetableGraf_id'])) {
					$queryParams['TimetableGraf_id'] = $data['TimetableGraf_id'];
					//$filter .= ' and ED.TimetableGraf_id = :TimetableGraf_id';
					$join['TTG'] = 'inner join lateral (
						select TTG.TimetableGraf_begTime, MedStaffFact_id, TTG.TimetableGraf_id
						from v_TimetableGraf_lite TTG
						where TTG.EvnDirection_id = ED.EvnDirection_id and TTG.TimetableGraf_id = :TimetableGraf_id
						limit 1
					) TTG on true';
				} else if (isset($data['parentClass'])
					//&& in_array($data['parentClass'], array('EvnPL','EvnVizitPL','EvnPLStom','EvnVizitPLStom'))
					&& in_array($data['parentClass'], array('EvnPL','EvnPLStom'))
					&& isset($data['Evn_id'])
				) {
					// убрал фильтр and ED.DirType_id in (2, 3, 4, 16), пока не выпилена возможность создать в БД направление/запись в поликлинику с пустым или не тем DirType_id
					$filter .= ' and ED.Lpu_did = :Lpu_did';
					$queryParams['Evn_id'] = $data['Evn_id'];
					$filter .= " and exists (
						select e.EvnDirection_id
						from v_{$data['parentClass']} e
						where e.{$data['parentClass']}_id = :Evn_id AND e.EvnDirection_id = ED.EvnDirection_id
						limit 1
					)";
				} else {
					return array();
				}
				// (Статус <> отменено, отклонено)
				$filter .= ' and coalesce(ED.EvnStatus_id, 16) not in (12,13)';
				$filter .= ' and ED.DirFailType_id is null and ED.EvnDirection_failDT is null';
				break;
			case 'addEvnVizitPLStom':
			case 'addEvnVizitPL':
			case 'load_data_for_auto_create_tap':
			case 'load_data_for_create_tap_consult':
				unset($select['Diag_Name']);
				unset($select['DirType_id']);
				unset($select['DirType_Name']);
				unset($select['Lpu_Name']);
				$select['Timetable_begTime'] = "case when TTG.TimetableGraf_begTime is not null
				then to_char(TTG.TimetableGraf_begTime, 'DD.MM.YYYY')
				|| ' ' || to_char(TTG.TimetableGraf_begTime, 'HH24:MI') else null end as \"Timetable_begTime\"";
				unset($join['TTS']);
				$select['TimetableStac_id'] = 'null as "TimetableStac_id"';
				unset($join['TTMS']);
				$select['TimetableMedService_id'] = 'null as "TimetableMedService_id"';
				unset($join['TTR']);
				$select['TimetableResource_id'] = 'null as "TimetableResource_id"';
				unset($join['EQ']);
				$select['EvnQueue_id'] = 'null as "EvnQueue_id"';
				unset($join['Diag']);
				unset($join['DT']);
				if (isset($data['EvnDirection_id'])) {
					$queryParams['EvnDirection_id'] = $data['EvnDirection_id'];
					$filter .= ' and ED.EvnDirection_id = :EvnDirection_id';
				} else if (isset($data['TimetableGraf_id'])) {
					$queryParams['TimetableGraf_id'] = $data['TimetableGraf_id'];
					$filter .= ' and ED.TimetableGraf_id = :TimetableGraf_id';
				}
				//зачем это условие?
				/*else {
					return array();
				}*/
				$filter .= ' and ED.Lpu_did = :Lpu_did';
				if ('load_data_for_create_tap_consult' === $data['useCase']) {
					$filter .= ' and ED.DirType_id in (11)';
				} else {
					// убрал фильтр, пока не выпилена возможность создать в БД направление/запись в поликлинику с пустым или не тем DirType_id
					// $filter .= ' and ED.DirType_id in (2, 3, 4, 16)';
				}
				//  активные направления (Статус <> обслужено, отменено, отклонено)
				$filter .='
				 and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)';
				$filter .='
				 and ED.DirFailType_id is null and ED.EvnDirection_failDT is null';
				$join['MSF'] = 'left join v_MedStaffFact MSF on MSF.MedStaffFact_id = TTG.MedStaffFact_id and MSF.Lpu_id = ED.Lpu_did';
				$select['MSF_Person_Fin'] = 'MSF.Person_Fin as "MSF_Person_Fin"';
				break;
			case 'choose_for_evnpl_stream_input': // выбираем для ТАП, но при операторском вводе (не из АРМа)
			case 'choose_for_evnplstom_stream_input': // выбираем для ТАП, но при операторском вводе (не из АРМа)
				// получить список ЭН, если ТАП заводит оператор (раньше фильтровались только по Person_id, Lpu_did)
				$filter .= ' and ED.Lpu_did = :Lpu_did';
				$filter .= ' and coalesce(ED.EvnDirection_IsAuto,1) = 1'; // только ЭН
				$filter .= ' and ED.DirFailType_id is null and ED.EvnDirection_failDT is null';
				//  активные направления (Статус <> обслужено, отменено, отклонено)
				$filter .= ' and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)';
				unset($join['TTS']);
				$select['TimetableStac_id'] = 'null as "TimetableStac_id"';
				unset($join['TTMS']);
				$select['TimetableMedService_id'] = 'null as "TimetableMedService_id"';
				unset($join['TTR']);
				$select['TimetableResource_id'] = 'null as "TimetableResource_id"';
				unset($join['EQ']);
				$select['EvnQueue_id'] = 'null as "EvnQueue_id"';
				$select['Timetable_begTime'] = "case when TTG.TimetableGraf_begTime is not null
					then to_char(TTG.TimetableGraf_begTime, 'DD.MM.YYYY')
					|| ' ' || to_char(TTG.TimetableGraf_begTime, 'HH24:MI') else null end as \"Timetable_begTime\"";
				$join['MSF'] = 'left join v_MedStaffFact MSF on MSF.MedStaffFact_id = TTG.MedStaffFact_id and MSF.Lpu_id = ED.Lpu_did';
				$select['MSF_Person_Fin'] = 'MSF.Person_Fin as "MSF_Person_Fin"';
				break;
			case 'choose_for_evnpl': // выбираем для ТАП из АРМа врача поликлиники
			case 'choose_for_evnplstom': // выбираем для ТАП из АРМа стоматолога
			case 'create_evnplstom_without_recording': // тоже самое, только выбираем при приеме без записи из формы АРМа врача
			case 'create_evnpl_without_recording': // тоже самое, только выбираем при приеме без записи из формы АРМа врача
				// получить список направлений из очереди по профилю этого отделения в данное МО или записанных к этому врачу (на это рабочее место врача)
				if (empty($data['MedStaffFact_id'])) {
					return false;
				}
				$filter .= ' and ED.Lpu_did = :Lpu_did';
				$filter .= ' and ED.DirType_id in (2, 3, 4, 16)';
				//  активные направления (Статус <> обслужено, отменено, отклонено)
				$filter .= ' and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)';
				$filter .= ' and ED.DirFailType_id is null and ED.EvnDirection_failDT is null';
				$join['MSF'] = 'inner join v_MedStaffFact MSF on MSF.MedStaffFact_id = :MedStaffFact_id and MSF.Lpu_id = ED.Lpu_did';
				$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
				unset($join['TTG']);
				$select['TimetableGraf_id'] = 'TTGEQ.TimetableGraf_id as "TimetableGraf_id"';
				unset($join['TTS']);
				$select['TimetableStac_id'] = 'null as "TimetableStac_id"';
				unset($join['TTMS']);
				$select['TimetableMedService_id'] = 'null as "TimetableMedService_id"';
				unset($join['TTR']);
				$select['TimetableResource_id'] = 'null as "TimetableResource_id"';
				unset($join['EQ']);
				$select['EvnQueue_id'] = 'TTGEQ.EvnQueue_id as "EvnQueue_id"';
				$prequery = "
					cte as (
						select DATEADD('month', -3, dbo.tzGetDate()) as mindate
					),
				";
				$join['TTGEQ'] = '
					inner join lateral (
						select *
						from (
							(select
								TTG.EvnDirection_id,
								TTG.TimetableGraf_id,
								TTG.MedStaffFact_id as MedStaffFact_did,
								TTG.TimetableGraf_begTime,
								EQ.EvnQueue_id,
								MSFTTG.Person_Fin as MSF_Person_Fin,
								EQ.EvnQueueStatus_id,
								EQ.RecMethodType_id as QueueRecMethodType_id
							from v_TimetableGraf_lite TTG
								inner join v_MedStaffFact MSFTTG on MSFTTG.MedStaffFact_id = TTG.MedStaffFact_id
								left join v_EvnQueue EQ on ED.EvnDirection_id = EQ.EvnDirection_id
							where TTG.EvnDirection_id = ED.EvnDirection_id
								and MSFTTG.Lpu_id = MSF.Lpu_id
								and ED.LpuSectionProfile_id = MSF.LpuSectionProfile_id
								and coalesce(TTG.TimetableGraf_begTime, (select mindate from cte)) >= (select mindate from cte)
							limit 1)

							union
							(select
								EQ.EvnDirection_id,
								TTG.TimetableGraf_id,
								TTG.MedStaffFact_id as MedStaffFact_did,
								TTG.TimetableGraf_begTime,
								EQ.EvnQueue_id,
								MSFTTG.Person_Fin as MSF_Person_Fin,
								EQ.EvnQueueStatus_id,
								EQ.RecMethodType_id as QueueRecMethodType_id
							from EvnQueue EQ
								left join v_TimetableGraf_lite TTG on EQ.TimetableGraf_id = TTG.TimetableGraf_id
								left join v_MedStaffFact MSFTTG on MSFTTG.MedStaffFact_id = TTG.MedStaffFact_id
							where EQ.EvnDirection_id = ED.EvnDirection_id
								and MSF.LpuSectionProfile_id = ED.LpuSectionProfile_id
								and (EQ.EvnQueue_recDT is null
									or (EQ.EvnQueue_recDT is not null and EQ.EvnQueueStatus_id = 2)
								)
								and EQ.EvnQueue_failDT is null
								and EQ.QueueFailCause_id is null
								and coalesce(EQ.EvnQueueStatus_id, 1) < 3
							limit 1)
						) t
					) TTGEQ on true
				';
				$select['MedStaffFact_did'] = 'TTGEQ.MedStaffFact_did as "MedStaffFact_did"';
				$select['Timetable_begTime'] = "case when TTGEQ.TimetableGraf_begTime is not null
					then to_char(TTGEQ.TimetableGraf_begTime, 'DD.MM.YYYY')
					|| ' ' || to_char(TTGEQ.TimetableGraf_begTime, 'HH24:MI') else null end as \"Timetable_begTime\"";
				$select['MSF_Person_Fin'] = 'TTGEQ.MSF_Person_Fin as "MSF_Person_Fin"';
				break;
			case 'choose_for_evnps_stream_input': // выбираем для КВС из форма редактирования КВС, но при операторском вводе (не из АРМа)
			case 'self_treatment': // тоже самое, только выбираем при приеме без записи из рабочего места врача приемного отделения
				$filter .= ' and ED.Lpu_did = :Lpu_did';

				$dirTypeArray = array(1, 2, 4, 5, 6);
				if (!in_array(getRegionNick(), array('ufa','kz'))) {
					$dirTypeArray[] = 19;
				}
				if (getRegionNick() == 'perm') {
					$dirTypeArray[] = 27;
					$dirTypeArray[] = 28;
				}

				$filter .= ' and ED.DirType_id in (' . implode(',', $dirTypeArray) . ')';

				//  активные направления (Статус <> обслужено, отменено, отклонено)
				$filter .= ' and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)';
				$filter .= ' and ED.DirFailType_id is null and ED.EvnDirection_failDT is null';
				unset($join['TTG']);
				$select['TimetableGraf_id'] = 'null as "TimetableGraf_id"';
				unset($join['TTMS']);
				$select['TimetableMedService_id'] = 'null as "TimetableMedService_id"';
				unset($join['TTR']);
				$select['TimetableResource_id'] = 'null as "TimetableResource_id"';
				$select['Timetable_begTime'] = "case
						when TTS.TimetableStac_setDate is not null
						then to_char(TTS.TimetableStac_setDate, 'DD.MM.YYYY')
						else null
					end as \"Timetable_begTime\"";
				if (in_array($data['useCase'], array('choose_for_evnps_stream_input','self_treatment'))) {
					$filter .= ' and coalesce(ED.EvnDirection_IsAuto,1) = 1'; // только ЭН
				}
				if (in_array($data['useCase'], array('self_treatment'))) {
					$join['EmD'] = 'left join EmergencyData EmD on TTS.EmergencyData_id = EmD.EmergencyData_id';
					$select['EmergencyData_CallNum'] = 'EmD.EmergencyData_CallNum as "EmergencyData_CallNum"';
				}
				$join['EDH'] = 'left join v_EvnDirectionHTM EDH on EDH.EvnDirectionHTM_id = ED.EvnDirection_id';
				$select['EvnDirectionHTM_id'] = 'EDH.EvnDirectionHTM_id as "EvnDirectionHTM_id"';
				break;
			case 'choose_for_evnps': // выбираем для КВС из форма редактирования КВС, открытой из АРМа врача стационара
			case 'create_evnps_from_workplacestac': // тоже самое, только выбираем при приеме из формы АРМа врача стационара
				// получить список направлений из очереди или записанных по профилю этого отделения в данное МО
				if (empty($data['LpuSection_id'])) {
					return false;
				}
				$filter .= ' and ED.Lpu_did = :Lpu_did';

				$dirTypeArray = array(1, 2, 4, 5, 6);
				if (!in_array(getRegionNick(), array('ufa','kz'))) {
					$dirTypeArray[] = 19;
				}
				if (getRegionNick() == 'perm') {
					$dirTypeArray[] = 27;
					$dirTypeArray[] = 28;
				}

				$filter .= ' and ED.DirType_id in (' . implode(',', $dirTypeArray) . ')';

				//  активные направления (Статус <> обслужено, отменено, отклонено)
				$filter .= ' and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)';
				$filter .= ' and ED.DirFailType_id is null and ED.EvnDirection_failDT is null';
				if (in_array($data['useCase'], array('create_evnps_from_workplacestac', 'choose_for_evnps'))) {
					$filter .= ' and coalesce(ED.EvnDirection_IsAuto,1) = 1'; // только ЭН
				}
				$join['HOSP'] = 'inner join v_LpuSection HOSP on HOSP.LpuSection_id = :LpuSection_id and HOSP.Lpu_id = ED.Lpu_did';
				$queryParams['LpuSection_id'] = $data['LpuSection_id'];
				unset($join['TTG']);
				$select['TimetableGraf_id'] = 'null as "TimetableGraf_id"';
				unset($join['TTS']);
				$select['TimetableStac_id'] = 'TTSEQ.TimetableStac_id as "TimetableStac_id"';
				unset($join['TTMS']);
				$select['TimetableMedService_id'] = 'null as "TimetableMedService_id"';
				unset($join['TTR']);
				$select['TimetableResource_id'] = 'null as "TimetableResource_id"';
				unset($join['EQ']);
				$select['EvnQueue_id'] = 'TTSEQ.EvnQueue_id as "EvnQueue_id"';
				$join['TTSEQ'] = 'inner join lateral (
						(select TTS.EvnDirection_id, TTS.TimetableStac_setDate, TTS.TimetableStac_id, null as EvnQueue_id
						from v_TimetableStac_lite TTS
						inner join v_LpuSection LS on LS.LpuSection_id = TTS.LpuSection_id and LS.LpuSectionProfile_id = HOSP.LpuSectionProfile_id
						where TTS.EvnDirection_id = ED.EvnDirection_id
						limit 1)
						union all
						(select EQ.EvnDirection_id, null as TimetableStac_setDate, null as TimetableStac_id, EQ.EvnQueue_id
						from EvnQueue EQ
						where EQ.EvnDirection_id = ED.EvnDirection_id
							and ED.LpuSectionProfile_id = HOSP.LpuSectionProfile_id
							and EQ.EvnQueue_recDT is null
							and EQ.EvnQueue_failDT is null
						limit 1)
						union all
						(select ED.EvnDirection_id, null as TimetableStac_setDate, null as TimetableStac_id, null as EvnQueue_id
						from DirType
						where DirType.DirType_id = 5 and DirType.DirType_id = ED.DirType_id
						limit 1)
					) TTSEQ on true';
				$select['Timetable_begTime'] = "case
					when TTSEQ.TimetableStac_setDate is not null
					then to_char(TTSEQ.TimetableStac_setDate, 'DD.MM.YYYY')
					else null
				end as \"Timetable_begTime\"";
				$join['EDH'] = 'left join v_EvnDirectionHTM EDH on EDH.EvnDirectionHTM_id = ED.EvnDirection_id';
				$select['EvnDirectionHTM_id'] = 'EDH.EvnDirectionHTM_id as "EvnDirectionHTM_id"';
				break;
			case 'check_exists_dir_stac_in_evn':
				if (empty($data['Lpu_did']) || empty($data['EvnDirection_pid'])) {
					return false;
				}
				$select = array(
					'EvnDirection_id' => 'ED.EvnDirection_id as "EvnDirection_id"',
				);
				$join = array(
				);
				$queryParams['Lpu_did'] = $data['Lpu_did'];
				$filter .= ' and ED.Lpu_did = :Lpu_did';
				$queryParams['EvnDirection_pid'] = $data['EvnDirection_pid'];
				$filter .= ' and ED.EvnDirection_pid = :EvnDirection_pid';
				$filter .= ' and ED.DirType_id in (1, 2, 4, 5, 6)';
				$filter .= ' and ED.DirFailType_id is null and ED.EvnDirection_failDT is null';
				$filter .= ' and coalesce(ED.EvnDirection_IsAuto,1) = 1'; // только ЭН
				if (!empty($data['LpuSectionProfile_did'])) {
					$queryParams['LpuSectionProfile_did'] = $data['LpuSectionProfile_did'];
					$filter .= ' and ED.LpuSectionProfile_id = :LpuSectionProfile_did';
				}
				break;
			case 'choose_for_evnvizitpl_link':
				$filter .= ' and ED.EvnDirection_pid is null';
				break;
		}
		$select = implode(',', $select);
		$join = implode(' ', $join);

		$unionall = '';
		if (!in_array(getRegionNick(), array('ufa','kz'))) {
			$unionall = '
		        union all

				select ED.MedicalCareFormType_id, ED.EvnDirectionHTM_id as EvnDirection_id, ED.Lpu_sid, Lpu_id, Org_sid, 19 as DirType_id, LpuSectionProfile_id, Diag_id, Lpu_did, EvnDirectionHTM_IsAuto as EvnDirection_IsAuto, PayType_id,
					EvnDirectionHTM_IsReceive as EvnDirection_IsReceive, EvnDirectionHTM_setDate as EvnDirection_setDate, EvnDirectionHTM_Num as EvnDirection_Num, LpuSection_id, EvnStatus_id, EvnDirectionHTM_Descr as EvnDirection_Descr, MedStaffFact_id, MedPersonal_id, MedPersonal_zid
				from v_EvnDirectionHTM ED
				where ED.Person_id = :Person_id
		    ';
		}

		$query = "
			with {$prequery}
			EvnDirectionAll as (
				select ED.MedicalCareFormType_id, ED.EvnDirection_id, ED.Lpu_sid, Lpu_id, Org_sid, DirType_id, LpuSectionProfile_id, Diag_id, Lpu_did, EvnDirection_IsAuto, PayType_id,
					EvnDirection_IsReceive, EvnDirection_setDate, EvnDirection_Num, LpuSection_id, EvnStatus_id, EvnDirection_Descr, MedStaffFact_id, MedPersonal_id, MedPersonal_zid
				from v_EvnDirection_all ED
				where ED.Person_id = :Person_id
					{$filter}

				{$unionall}
			)

			select {$select}
			from EvnDirectionAll ED
				{$join}
		";
		//echo getDebugSQL($query, $queryParams);exit();
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Убрать в очередь и освободить время приема
	 * @param $data
	 * @return array
	 */
	function returnToQueue($data)
	{
		$className = get_class($this);
		/**@var EvnDirectionAll_model $instance */
		$instance = new $className();
		$doSaveParams = [
			"scenario" => "returnToQueue",
			"session" => $data["session"],
			"EvnDirection_id" => $data["EvnDirection_id"],
		];
		return $instance->doSave($doSaveParams, true);
	}

	/**
	 * Отменить исходящее направление на экстренную госпитализацию c указанием причины
	 * @param $data
	 * @return array
	 */
	function cancel($data)
	{
		try {
			$applyDataParams = [
				"scenario" => "cancel",
				"session" => $data["session"],
				"EvnDirection_id" => $data["EvnDirection_id"],
				"DirFailType_id" => $data["DirFailType_id"],
				"EvnStatusHistory_Cause" => $data["EvnStatusHistory_Cause"],
				"EvnStatusCause_id" => $data["EvnStatusCause_id"],
			];
			$this->applyData($applyDataParams);
			$this->_validate();
			$execCommonSPParams = [
				"EvnDirection_id" => $this->id,
				"DirFailType_id" => $this->DirFailType_id,
				"EvnComment_Comment" => $this->_params["EvnStatusHistory_Cause"],
				"EvnStatusCause_id" => $this->_params["EvnStatusCause_id"],
				"pmUser_id" => $this->promedUserId,
				"MedStaffFact_fid" => $data["session"]["CurMedStaffFact_id"],
				"Lpu_cid" => $data["session"]["lpu_id"]
			];
			$tmp = $this->execCommonSP("p_EvnDirection_cancel", $execCommonSPParams, "array_assoc");
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"], 500);
			}

			$this->load->model('ApprovalList_model');
			$this->ApprovalList_model->deleteApprovalList(array(
				'ApprovalList_ObjectName' => 'EvnDirection',
				'ApprovalList_ObjectId' => $this->id
			));
		} catch (Exception $e) {
			$this->_saveResponse = [
				"Error_Msg" => $e->getMessage(),
				"Error_Code" => $e->getCode(),
			];
		}
		return $this->_saveResponse;
	}

	/**
	 * Отклонить входящее направление на экстренную госпитализацию c указанием причины
	 * @param array $data Массив входящих параметров
	 * @return array
	 */
	function reject($data)
	{
		try {
			$applyDataParams = [
				"scenario" => "cancel",
				"session" => $data["session"],
				"EvnDirection_id" => $data["EvnDirection_id"],
				"DirFailType_id" => $data["DirFailType_id"],
				"EvnStatusHistory_Cause" => $data["EvnStatusHistory_Cause"],
				"EvnStatusCause_id" => $data["EvnStatusCause_id"],
			];
			$this->applyData($applyDataParams);
			$this->_validate();
			$execCommonSPParams = [
				"EvnDirection_id" => $this->id,
				"DirFailType_id" => $this->DirFailType_id,
				"EvnComment_Comment" => $this->_params["EvnStatusHistory_Cause"],
				"EvnStatusCause_id" => $this->_params["EvnStatusCause_id"],
				"pmUser_id" => $this->promedUserId,
				"Lpu_cid" => $data["session"]["lpu_id"],
				"MedStaffFact_fid" => $data["session"]["CurMedStaffFact_id"]
			];
			$tmp = $this->execCommonSP("p_EvnDirection_decline", $execCommonSPParams, "array_assoc");
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"], 500);
			}

			$this->load->model('ApprovalList_model');
			$this->ApprovalList_model->deleteApprovalList(array(
				'ApprovalList_ObjectName' => 'EvnDirection',
				'ApprovalList_ObjectId' => $this->id
			));
		} catch (Exception $e) {
			$this->_saveResponse = [
				"Error_Msg" => $e->getMessage(),
				"Error_Code" => $e->getCode(),
			];
		}
		return $this->_saveResponse;
	}

	/**
	 * Функция выполняющаяся перед сохранением статуса направления
	 */
	function onBeforeSetStatus($params){
		parent::onBeforeSetStatus($params);

		$this->load->model('Queue_model');

		$Lpu_id = !empty($this->sessionParams['CurARM']['Lpu_id']) ? $this->sessionParams['CurARM']['Lpu_id'] : null;

		// проверяем включены ли ЛО
		$checkWaitingList = $this->Queue_model->checkLpuQueueIsAllowed(
			array(
				'Lpu_id' => !empty($Lpu_id) ? $Lpu_id : null,
				'Evn_id' => $params['Evn_id']
			)
		);

		if ($checkWaitingList) {

			$EvnQueue_data = $this->getFirstRowFromQuery("
				select
					eq.EvnQueue_id as \"EvnQueue_id\",
					eq.EvnQueueStatus_id as \"EvnQueueStatus_id\",
					eq.RecMethodType_id as \"RecMethodType_id\",
					eq.QueueFailCause_id as \"QueueFailCause_id\",
					eq.TimetableGraf_id as \"TimetableGraf_id\",
					eq.EvnDirection_id as \"EvnDirection_id\",
					eq.Person_id as \"Person_id\"
				from v_EvnQueue eq
				where eq.EvnDirection_id = :Evn_id
					and eq.EvnQueueStatus_id is not null
					and eq.RecMethodType_id in (1,2)
				order by EvnQueue_insDT desc
				limit 1
			", $params);

			if (!empty($EvnQueue_data)) {

				// если мы пытаемся выставить направление в статус "обслужено"
				if (!empty($params['EvnStatus_SysNick']) && $params['EvnStatus_SysNick'] == EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED) {
					// если есть лист ожидания находящийся "в очереди" или "ожидающий подтверждения"
					// оформленный через портал или мобильное приложение
					if (in_array($EvnQueue_data['EvnQueueStatus_id'], array(1,2))) {

						// если пациенту была предложена бирка
						// то отменяем запись на бирку
						if ($EvnQueue_data['EvnQueueStatus_id'] == 2 && !empty($EvnQueue_data['TimetableGraf_id'])) {
							$this->load->model("Timetable_model");
							$cancelResult = $this->Timetable_model->Clear(array(
								'pmUser_id' => $params['pmUser_id'],
								'TimetableGraf_id' => $EvnQueue_data['TimetableGraf_id'],
								'dontCancelDirection' => true,
								'checkEvnQueueData' => false,
								'object' => 'TimetableGraf'
							));
						}

						// тогда меняем статус у листа ожидания на "обслужен вне очереди"
						$this->Queue_model->updateEvnQueueData(array(
							'EvnQueue_id' => $EvnQueue_data['EvnQueue_id'],
							// обслужен вне очереди
							'EvnQueueStatus_id' => 5,
							// Обслужен вне очереди
							'QueueFailCause_id' => 6,
							'pmUser_failID' =>$params['pmUser_id'],
							'EvnQueue_failDT' => date('Y-m-d H:i:s'),
						));

						$this->Queue_model->sendNotify(array(
							'notify_type' => 'reject',
							'Person_id' => $EvnQueue_data['Person_id'],
							// причина исключения
							'QueueFailCause_id' => 6,
							'pmUser_id' => $params['pmUser_id']
						));
					}
				}

				// когда пытаемся вернуть ЛО обслуженный вне очереди
				if ((!empty($params['EvnStatus_id']) && $params['EvnStatus_id'] == 10) ||
					(!empty($params['EvnStatus_SysNick']) && $params['EvnStatus_SysNick'] == EvnDirectionAll_model::EVN_STATUS_DIRECTION_IN_QUEUE)
				){
					if ($EvnQueue_data['EvnQueueStatus_id'] == 5 && $EvnQueue_data['QueueFailCause_id'] == 6) {
						// тогда меняем статус у листа ожидания на "в очереди"
						// todo: после выполнения задачи 193680, искать последний статус по истории статусов ЛО
						$this->Queue_model->updateEvnQueueData(array(
							'EvnQueue_id' => $EvnQueue_data['EvnQueue_id'],
							// в очереди
							'EvnQueueStatus_id' => 1,
							'QueueFailCause_id' => null,
							'pmUser_failID' => null,
							'EvnQueue_failDT' => null
						));

						$this->Queue_model->sendNotify(array(
							'notify_type' => 'rollback',
							'Person_id' => $EvnQueue_data['Person_id'],
							'pmUser_id' => $params['pmUser_id']
						));
					}
				}
			}
		}
	}
}
