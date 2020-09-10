<?php
defined("BASEPATH") or die("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Регистр
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       Alexander Chebukin 
 * @version
 *
 * @property int $MorbusType_id
 * @property string $MorbusType_SysNick
 *
 * @property-read Morbus_model $Morbus_model
 * @property-read MorbusHepatitis_model $MorbusHepatitis_model
 * @property-read MorbusOrphan_model $MorbusOrphan_model
 * @property-read EvnNotifyOrphan_model $EvnNotifyOrphan_model
 * @property-read MorbusTub_model $MorbusTub_model
 * @property-read MorbusVener_model $MorbusVener_model
 * @property-read MorbusHIV_model $MorbusHIV_model
 * @property-read MorbusACS_model $MorbusACS_model
 * @property-read MorbusOnkoSpecifics_model $MorbusOnkoSpecifics_model
 * @property-read MorbusProf_model $MorbusProf_model
 * @property-read MorbusCrazy_model $MorbusCrazy_model
 * @property-read MorbusNephro_model $MorbusNephro_model
 * @property-read MorbusIBS_model $MorbusIBS_model
 * @property-read Messages_model $Messages_model
 *
 * @property CI_DB_driver $db
 */
class PersonRegister_model extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";
	public $dateTimeForm113 = "DD mon YYYY HH24:MI:SS:MS";
	public $dateTimeForm126 = "YYYY-MM-DDT HH24:MI:SS:MS";
	public $dateTimeForm120 = "YYYY-MM-DD";
	public $dateTimeForm120full = "YYYY-MM-DD HH24:MI:SS";

	public $numericForm18_2 = "FM999999999999999999.00";

	protected $_isNeedPromedUserIdForDel = true;

	private $PersonRegisterType_id;
	private $PersonRegister_id; //PersonRegister_id
	private $Person_id; //Person_id
	private $_MorbusType_id;
	private $_MorbusType_SysNick;
	private $Diag_id; //Diag_id
	private $MorbusProfDiag_id; //MorbusProfDiag_id
	private $Morbus_confirmDate; //Morbus_confirmDate
	private $Morbus_EpidemCode; //Morbus_EpidemCode
	private $ignoreCheckAnotherDiag; //ignoreCheckAnotherDiag
	private $PersonRegister_Code; //PersonRegister_Code
	private $PersonRegister_setDate; //PersonRegister_setDate
	private $PersonRegister_disDate; //PersonRegister_disDate
	private $PersonRegisterOutCause_id; //PersonRegisterOutCause_id
	private $PersonDeathCause_id; //PersonDeathCause_id
	private $PersonRegister_Alcoholemia; //PersonRegister_Alcoholemia
	private $MedPersonal_iid; //MedPersonal_iid
	private $Lpu_iid; //Lpu_iid
	private $MedPersonal_did; //MedPersonal_did
	private $Lpu_did; //Lpu_did
	private $Morbus_id; //Morbus_id
	private $MorbusHIV_id; //MorbusHIV_id
	private $MorbusTub_id; //MorbusTub_id
	private $MorbusOnko_id;  //MorbusOnko_id
	private $pmUser_id; //Идентификатор пользователя системы Промед
	private $EvnNotifyBase_id; //EvnNotifyBase_id
	private $RiskType_id; //Степень риска
	private $PregnancyResult_id; //Исход беременности
	private $Mode; //особый режим сохранения
	private $CrazyType;
	private $autoExcept;//автоматическое исключение из регистра
	private $histCreated;//запись в истории создана

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return "PersonRegister";
	}

	/**
	 * @param $value
	 */
	public function setMode($value)
	{
		$this->Mode = $value;
	}

	/**
	 * @return mixed
	 */
	public function getPersonRegister_id()
	{
		return $this->PersonRegister_id;
	}

	/**
	 * @param $value
	 */
	public function setPersonRegister_id($value)
	{
		$this->PersonRegister_id = $value;
	}

	/**
	 * @param $value
	 */
	public function setPersonRegisterType_id($value)
	{
		$this->PersonRegisterType_id = $value;
	}

	/**
	 * @param $value
	 * @throws Exception
	 */
 	public function setPersonRegisterType_SysNick($value)
	{
		if (empty($value)) {
			$this->PersonRegisterType_id = null;
		} else {
			$query = "
				select PersonRegisterType_id as \"PersonRegisterType_id\"
				from v_PersonRegisterType
				where PersonRegisterType_SysNick ILike :PersonRegisterType_SysNick
				limit 1
			";
			$queryParams = ["PersonRegisterType_SysNick" => $value];
			$this->PersonRegisterType_id = $this->getFirstResultFromQuery($query, $queryParams);
			if (!$this->PersonRegisterType_id) {
				throw new Exception("Ошибка при получении идентификатора вида регистра", 500);
			}
		}
	}
	/**
	 * @return mixed
	 */
	public function getMorbus_id()
	{
		return $this->Morbus_id;
	}

	/**
	 * @param $value
	 */
	public function setMorbus_id($value)
	{
		$this->Morbus_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getPerson_id()
	{
		return $this->Person_id;
	}

	/**
	 * @param $value
	 */
	public function setPerson_id($value)
	{
		$this->Person_id = $value;
	}

	/**
	 * @return int|null
	 * @throws Exception
	 */
	public function getMorbusType_id()
	{
		if (!empty($this->_MorbusType_SysNick) && empty($this->_MorbusType_id)) {
			$this->load->library("swMorbus");
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->_MorbusType_SysNick);
			if (empty($this->_MorbusType_id)) {
				throw new Exception("Попытка получить неправильный идентификатор типа заболевания", 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public function loadEvnNotifyRegisterInclude($data)
	{
		$query = "
			select 
				enr.Person_id as \"Person_id\",
				enr.Server_id as \"Server_id\",
				enr.PersonEvn_id as \"PersonEvn_id\",
				enr.EvnVK_id as \"EvnVK_id\",
				enr.EvnNotifyRegister_pid as \"EvnNotifyRegister_pid\",
				enr.EvnNotifyRegister_Comment as \"EvnNotifyRegister_Comment\",
				enr.Lpu_id as \"Lpu_did\",
				enr.Diag_id as \"Diag_id\",
				enr.MedPersonal_id as \"MedPersonal_id\"
				from v_EvnNotifyRegister enr
			where EvnNotifyRegister_id=:EvnNotifyRegister_id
		";
		$queryParams = ["EvnNotifyRegister_id" => $data["EvnNotifyRegister_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return (isset($result[0])) ? $result : false;
	}

	/**
	 * @param $value
	 * @throws Exception
	 */
	public function setMorbusType_id($value)
	{
		if (empty($value)) {
			$this->_MorbusType_SysNick = null;
			$this->_MorbusType_id = null;
		} else {
			$this->load->library("swMorbus");
			$arr = swMorbus::getMorbusTypeListAll();
			if (empty($arr[$value])) {
				throw new Exception("Попытка установить неправильный идентификатор типа заболевания", 500);
			}
			$this->_MorbusType_id = $value;
			$this->_MorbusType_SysNick = $arr[$value][0]["MorbusType_SysNick"];
		}
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getMorbusType_SysNick()
	{
		if (empty($this->_MorbusType_SysNick) && !empty($this->_MorbusType_id)) {
			$this->load->library("swMorbus");
			$arr = swMorbus::getMorbusTypeListAll();
			if (empty($arr[$this->_MorbusType_id])) {
				throw new Exception("Попытка получить неправильный тип заболевания", 500);
			}
			$this->_MorbusType_SysNick = $arr[$this->_MorbusType_id][0]["MorbusType_SysNick"];
		}
		return $this->_MorbusType_SysNick;
	}

	/**
	 * @param $value
	 * @throws Exception
	 */
	public function setMorbusType_SysNick($value)
	{
		if (empty($value)) {
			$this->_MorbusType_SysNick = null;
			$this->_MorbusType_id = null;
		} else {
			$this->load->library("swMorbus");
			$this->_MorbusType_id = (($value == "fmba") ? 1 : swMorbus::getMorbusTypeIdBySysNick($value));
			if (empty($this->_MorbusType_id)) {
				throw new Exception("Попытка установить неправильный тип заболевания", 500);
			}
			$this->_MorbusType_SysNick = $value;
		}
	}

	/**
	 * @return mixed
	 */
	public function getDiag_id()
	{
		return $this->Diag_id;
	}

	/**
	 * @return mixed
	 */
	public function getMorbusProfDiag_id()
	{
		return $this->MorbusProfDiag_id;
	}

	/**
	 * @return mixed
	 */
	public function getignoreCheckAnotherDiag()
	{
		return $this->ignoreCheckAnotherDiag;
	}

	/**
	 * @param $value
	 */
	public function setDiag_id($value)
	{
		$this->Diag_id = $value;
	}

	/**
	 * @param $value
	 */
	public function setMorbusProfDiag_id($value)
	{
		$this->MorbusProfDiag_id = $value;
	}

	/**
	 * @param $value
	 */
	public function setMorbus_confirmDate($value)
	{
		$this->Morbus_confirmDate = $value;
	}

	/**
	 * @param $value
	 */
	public function setMorbus_EpidemCode($value)
	{
		$this->Morbus_EpidemCode = $value;
	}

	/**
	 * @param $value
	 */
	public function setignoreCheckAnotherDiag($value)
	{
		$this->ignoreCheckAnotherDiag = $value;
	}

	/**
	 * @return mixed
	 */
	public function getPersonRegister_Code()
	{
		return $this->PersonRegister_Code;
	}

	/**
	 * @param $value
	 */
	public function setPersonRegister_Code($value)
	{
		$this->PersonRegister_Code = $value;
	}

	/**
	 * @return mixed
	 */
	public function getPersonRegister_setDate()
	{
		return $this->PersonRegister_setDate;
	}

	/**
	 * @param $value
	 */
	public function setPersonRegister_setDate($value)
	{
		$this->PersonRegister_setDate = $value;
	}

	/**
	 * @return mixed
	 */
	public function getPersonRegister_disDate()
	{
		return $this->PersonRegister_disDate;
	}

	/**
	 * @param $value
	 */
	public function setPersonRegister_disDate($value)
	{
		$this->PersonRegister_disDate = $value;
	}

	/**
	 * @return mixed
	 */
	public function getPersonRegisterOutCause_id()
	{
		return $this->PersonRegisterOutCause_id;
	}

	/**
	 * @param $value
	 */
	public function setPersonRegisterOutCause_id($value)
	{
		$this->PersonRegisterOutCause_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getPersonDeathCause_id()
	{
		return $this->PersonDeathCause_id;
	}

	/**
	 * @param $value
	 */
	public function setPersonDeathCause_id($value)
	{
		$this->PersonDeathCause_id = $value;
	}

	/**
	 * @param $value
	 * @throws Exception
	 */
	public function setPersonRegisterOutCause_SysNick($value)
	{
		if (empty($value)) {
			$this->PersonRegisterOutCause_id = null;
		} else {
			$query = "
				select PersonRegisterOutCause_id
				from v_PersonRegisterOutCause
				where PersonRegisterOutCause_SysNick ilike :PersonRegisterOutCause_SysNick
				limit 1
			";
			$queryParams = ["PersonRegisterOutCause_SysNick" => $value];
			$this->PersonRegisterOutCause_id = $this->getFirstResultFromQuery($query, $queryParams);
			if (!$this->PersonRegisterOutCause_id) {
				throw new Exception("Ошибка при получении идентификатора причины закрытия записи регистра", 500);
			}
		}
	}

	/**
	 * @param $value
	 * @return mixed
	 */
	public function getPersonRegister_Alcoholemia($value)
	{
		return $this->PersonRegister_Alcoholemia;
	}

	/**
	 * @param $value
	 */
	public function setPersonRegister_Alcoholemia($value)
	{
		$this->PersonRegister_Alcoholemia = $value;
	}

	/**
	 * @return mixed
	 */
	public function getMedPersonal_iid()
	{
		return $this->MedPersonal_iid;
	}

	/**
	 * @param $value
	 */
	public function setMedPersonal_iid($value)
	{
		$this->MedPersonal_iid = $value;
	}

	/**
	 * @return mixed
	 */
	public function getLpu_iid()
	{
		return $this->Lpu_iid;
	}

	/**
	 * @param $value
	 */
	public function setLpu_iid($value)
	{
		$this->Lpu_iid = $value;
	}

	/**
	 * @return mixed
	 */
	public function getMedPersonal_did()
	{
		return $this->MedPersonal_did;
	}

	/**
	 * @param $value
	 */
	public function setMedPersonal_did($value)
	{
		$this->MedPersonal_did = $value;
	}

	/**
	 * @return mixed
	 */
	public function getLpu_did()
	{
		return $this->Lpu_did;
	}

	/**
	 * @param $value
	 */
	public function setLpu_did($value)
	{
		$this->Lpu_did = $value;
	}

	/**
	 * @return mixed
	 */
	public function getEvnNotifyBase_id()
	{
		return $this->EvnNotifyBase_id;
	}

	/**
	 * @param $value
	 */
	public function setEvnNotifyBase_id($value)
	{
		$this->EvnNotifyBase_id = $value;
	}

	/**
	 * @param $value
	 */
	public function setRiskType_id($value)
	{
		$this->RiskType_id = $value;
	}

	/**
	 * @param $value
	 */
	public function setPregnancyResult_id($value)
	{
		$this->PregnancyResult_id = $value;
	}

	/**
	 * @param $value
	 */
	public function setAutoExcept($value)
	{
		$this->autoExcept = $value;
	}

	/**
	 * @param $value
	 */
	public function setHistCreated($value)
	{
		$this->histCreated = $value;
	}

	/**
	 * @return mixed
	 */
	public function getpmUser_id()
	{
		return $this->pmUser_id;
	}

	/**
	 * @param $value
	 */
	public function setpmUser_id($value)
	{
		$this->pmUser_id = $value;
	}

	/**
	 * PersonRegister_model constructor.
	 * @throws Exception
	 */
	function __construct()
	{
		parent::__construct();
		if (!isset($_SESSION["pmuser_id"])) {
			throw new Exception("Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)");
		}
		$this->setpmUser_id($_SESSION["pmuser_id"]);
		$this->setSessionParams($_SESSION);
	}

	/**
	 * @return array|bool
	 */
	function load() {
		$q = "
			select
				PersonRegister_id as \"PersonRegister_id\",
				Person_id as \"Person_id\",
				Diag_id as \"Diag_id\",
				PersonRegister_Code as \"PersonRegister_Code\",
				PersonRegisterType_id as \"PersonRegisterType_id\",
				to_char(PersonRegister_setDate, '{$this->dateTimeForm120}') as \"PersonRegister_setDate\",
				to_char(PersonRegister_disDate, '{$this->dateTimeForm120}') as \"PersonRegister_disDate\",				
				v_MorbusType.MorbusType_id as \"MorbusType_id\",
				v_MorbusType.MorbusType_SysNick as \"MorbusType_SysNick\",
				Morbus_id as \"Morbus_id\",
				PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
				MedPersonal_iid as \"MedPersonal_iid\",
				Lpu_iid as \"Lpu_iid\",
				MedPersonal_did as \"MedPersonal_did\",
				Lpu_did as \"Lpu_did\",
				EvnNotifyBase_id as \"EvnNotifyBase_id\",
				PersonRegister_Alcoholemia as \"PersonRegister_Alcoholemia\",
				RiskType_id as \"RiskType_id\",
				PregnancyResult_id as \"PregnancyResult_id\"
			from
				v_PersonRegister
				left join dbo.v_MorbusType on v_MorbusType.MorbusType_id = v_PersonRegister.MorbusType_id
			where PersonRegister_id = :PersonRegister_id
		";
		$r = $this->db->query($q, array('PersonRegister_id' => $this->PersonRegister_id));
		if (is_object($r)) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->PersonRegister_id = $r[0]['PersonRegister_id'];
				$this->PersonRegisterType_id = $r[0]['PersonRegisterType_id'];
				$this->Person_id = $r[0]['Person_id'];
				$this->_MorbusType_id = $r[0]['MorbusType_id'];
				$this->_MorbusType_SysNick = $r[0]['MorbusType_SysNick'];
				$this->Diag_id = $r[0]['Diag_id'];
				$this->PersonRegister_Code = $r[0]['PersonRegister_Code'];
				$this->PersonRegister_setDate = $r[0]['PersonRegister_setDate'];
				$this->PersonRegister_disDate = $r[0]['PersonRegister_disDate'];
				$this->Morbus_id = $r[0]['Morbus_id'];
				$this->PersonRegisterOutCause_id = $r[0]['PersonRegisterOutCause_id'];
				$this->MedPersonal_iid = $r[0]['MedPersonal_iid'];
				$this->Lpu_iid = $r[0]['Lpu_iid'];
				$this->MedPersonal_did = $r[0]['MedPersonal_did'];
				$this->Lpu_did = $r[0]['Lpu_did'];
				$this->EvnNotifyBase_id = $r[0]['EvnNotifyBase_id'];
				$this->RiskType_id = $r[0]['RiskType_id'];
				$this->PregnancyResult_id = $r[0]['PregnancyResult_id'];
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Метод для API. Получение записи регистра по онкологии, больных туберкулезом и ВИЧ-инфицированых
	 * @param $data
	 * @return array|CI_DB_result|int
	 * @throws Exception
	 */
	function loadAPI($data)
	{
		$params = [];
		$where = [];
		if (!empty($data["PersonRegister_id"])) {
			$params["PersonRegister_id"] = $data["PersonRegister_id"];
			$where[] = "PR.PersonRegister_id = :PersonRegister_id";
		}
		if (!empty($data["Person_id"])) {
			$params["Person_id"] = $data["Person_id"];
			$where[] = "PR.Person_id = :Person_id";
		}
		if (count($params) == 0) {
			throw new Exception("не передан ни один из параметров");
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
			select
				PR.PersonRegister_id as \"PersonRegister_id\",
				PR.Person_id as \"Person_id\",
				PR.Diag_id as \"Diag_id\",
				PR.PersonRegister_Code as \"PersonRegister_Code\",
				PR.PersonRegisterType_id as \"PersonRegisterType_id\",
				to_char(PR.PersonRegister_setDate, '{$this->dateTimeForm120}') as \"PersonRegister_setDate\",
				to_char(PR.PersonRegister_disDate, '{$this->dateTimeForm120}') as \"PersonRegister_disDate\",				
				PR.MorbusType_id as \"MorbusType_id\",
				MT.MorbusType_SysNick as \"MorbusType_SysNick\",
				PR.Morbus_id as \"Morbus_id\",
				PR.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
				PR.MedPersonal_iid as \"MedPersonal_iid\",
				PR.Lpu_iid as \"Lpu_iid\",
				PR.MedPersonal_did as \"MedPersonal_did\",
				PR.Lpu_did as \"Lpu_did\",
				PR.EvnNotifyBase_id as \"EvnNotifyBase_id\",
				PR.PersonRegister_Alcoholemia as \"PersonRegister_Alcoholemia\",
				PR.PersonRegister_Code as \"PersonRegister_Code\",
				PR.RiskType_id as \"RiskType_id\",
				PR.PregnancyResult_id as \"PregnancyResult_id\",
			    to_char(MH.MorbusHIV_confirmDate, '{$this->dateTimeForm120}') as \"Morbus_confirmDate\",
			    MH.MorbusHIV_EpidemCode as \"Morbus_EpidemCode\",
			    PR.PersonDeathCause_id as \"PersonDeathCause_id\",
			    PR.RiskType_id as \"RiskType_id\"
			from
				dbo.v_PersonRegister PR
				left join v_MorbusType MT on MT.MorbusType_id = PR.MorbusType_id
				left join v_MorbusHIV MH on MH.Morbus_id = PR.Morbus_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return 0;
		}
		$result = $result->result("array");
		return (isset($result[0])) ? $result : 0;
	}

	/**
	 * Контроль на существование записи регистра при добавлении записи
	 * @return array|bool|CI_DB_result
	 * @throws Exception
	 */
	private function _checkPersonRegisterExist()
	{
		$queryParams = ['Person_id' => $this->Person_id];
		$add_select = "";
		$add_join = "";
		$add_where = "and PR.MorbusType_id = :MorbusType_id";
		switch (true) {
			case ("narc" == $this->MorbusType_SysNick):
				$error_msg = "Пациент уже включен в регистр по наркологии.";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				break;
			case ("crazy" == $this->MorbusType_SysNick):
				$error_msg = "Пациент уже включен в регистр по психиатрии.";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				break;
			case ("hepa" == $this->MorbusType_SysNick):
				$error_msg = "На выбранного пациента уже существует запись регистра с типом «вирусный гепатит»";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				break;
			case ("orphan" == $this->MorbusType_SysNick):
				$error_msg = "Пациент уже включен в регистр по орфанным заболеваниям. При необходимости измените диагноз в существующей записи регистра";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				break;
			case ("tub" == $this->MorbusType_SysNick):
				$error_msg = "Пациент уже включен в регистр по туберкулезу.";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				break;
			case ("nephro" == $this->MorbusType_SysNick):
				$error_msg = "На выбранного пациента уже существует запись регистра с типом «нефрология»";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				break;
			case ("prof" == $this->MorbusType_SysNick):
				$error_msg = "Выбранный пациент уже включен в регистр по профзаболеваниям с указанным диагнозом";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				if (empty($this->ignoreCheckAnotherDiag)) {
					$add_select = "
						,PR.Diag_id as \"Diag_id\"
						,mpd.MorbusProfDiag_Name as \"MorbusProfDiag_Name\"
						,coalesce(d.Diag_Code||'. ', '')||d.Diag_Name as \"Diag_Name\"
					";
					$add_join = "
						left join v_Diag d on d.Diag_id = PR.Diag_id
						left join v_MorbusProf MO on MO.Morbus_id = PR.Morbus_id
						left join v_MorbusProfDiag mpd on mpd.MorbusProfDiag_id = MO.MorbusProfDiag_id
					";
				} else {
					$queryParams["Diag_id"] = $this->Diag_id;
					$add_where .= " and PR.Diag_id = :Diag_id";
				}
				break;
			case ("ibs" == $this->MorbusType_SysNick):
				$error_msg = "На выбранного пациента уже существует запись регистра с типом «ИБС»";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				break;
			case ("palliat" == $this->MorbusType_SysNick):
				$error_msg = "На выбранного пациента уже существует запись регистра с выбранным диагнозом";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				$queryParams["Diag_id"] = $this->Diag_id;
				$add_where .= " and PR.Diag_id = :Diag_id";
				break;
			case ("geriatrics" == $this->MorbusType_SysNick):
				$error_msg = "На выбранного пациента уже существует запись регистра по гериатрии";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				break;
			case ("hiv" == $this->MorbusType_SysNick):
				$error_msg = "На выбранного пациента уже существует запись регистра с типом «ВИЧ»";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				break;
			case ("diabetes" == $this->MorbusType_SysNick):
				$error_msg = "На выбранного пациента уже существует запись регистра с типом «Сахарный диабет»";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				break;
			case ("large family" == $this->MorbusType_SysNick):
				$error_msg = "На выбранного пациента уже существует запись регистра с типом «Ребенок из многодетной семьи»";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				break;
			case ("nolos" == $this->MorbusType_SysNick):
				$error_msg = "На выбранного пациента уже существует запись регистра с выбранной группой диагнозов.";
				$queryParams["Diag_id"] = $this->Diag_id;
				$add_where .= "
					and PR.Diag_id in (
						select DD.Diag_id
						from
							v_Diag D
							left join v_Diag DD on D.Diag_pid = DD.Diag_pid
						where D.Diag_id = :Diag_id
					)
				";
				break;
			case (3 == $this->PersonRegisterType_id):
				$error_msg = "На выбранного пациента уже существует запись регистра с типом «онкология»";
				$queryParams["PersonRegisterType_id"] = $this->PersonRegisterType_id;
				$queryParams["Diag_id"] = $this->Diag_id;
				$add_where = " and PR.PersonRegisterType_id = :PersonRegisterType_id and PR.Diag_id = :Diag_id";
				break;
			case (49 == $this->PersonRegisterType_id):
				$error_msg = "На выбранного пациента уже существует запись регистра с данным заболеванием по ВЗН";
				$queryParams["PersonRegisterType_id"] = $this->PersonRegisterType_id;
				$queryParams["Diag_id"] = $this->Diag_id;
				$add_where = "
					and PR.PersonRegisterType_id = :PersonRegisterType_id
					and PR.Diag_id in (
						select DD.Diag_id
						from
							v_Diag D
							left join v_Diag DD on D.Diag_pid = DD.Diag_pid
						where D.Diag_id = :Diag_id
					)
				";
				break;
			case (62 == $this->PersonRegisterType_id):
				$error_msg = "Пациент уже включен в регистр лиц, совершивших суицидальные попытки, с указанной датой совершения";
				$queryParams["PersonRegisterType_id"] = $this->PersonRegisterType_id;
				$queryParams["PersonRegister_setDate"] = $this->PersonRegister_setDate;
				$add_where = " and PR.PersonRegisterType_id = :PersonRegisterType_id and PersonRegister_setDate = cast(:PersonRegister_setDate as date)";
				break;
			case (67 == $this->PersonRegisterType_id):
				$error_msg = "На выбранного пациента уже существует запись регистра по гериатрии";
				$queryParams["PersonRegisterType_id"] = $this->PersonRegisterType_id;
				$add_where = " and PR.PersonRegisterType_id = :PersonRegisterType_id";
				break;
			case ("gibt" == $this->MorbusType_SysNick):
				$error_msg = "На выбранного пациента уже существует запись регистра о нуждаемости в лечении с применением ГИБТ по данному диагнозу";
				$queryParams["MorbusType_id"] = $this->MorbusType_id;
				$queryParams["Diag_id"] = $this->Diag_id;
				$add_where .= " and PR.Diag_id = :Diag_id";
				break;
            case ('gibt' == $this->MorbusType_SysNick):
                $error_msg = 'На выбранного пациента уже существует запись регистра о нуждаемости в лечении с применением ГИБТ по данному диагнозу';
                $queryParams['MorbusType_id'] = $this->MorbusType_id;
                $queryParams['Diag_id'] = $this->Diag_id;
                $add_where .= 'AND PR.Diag_id = :Diag_id';
                break;
			default:
				$error_msg = "На выбранного пациента уже существует запись регистра с выбранной группой диагнозов.";
				$queryParams["Diag_id"] = $this->Diag_id;
				$add_where = "
					and PR.Diag_id in (
						select DD.Diag_id
						from
							v_Diag D
							left join v_Diag DD on D.Diag_pid = DD.Diag_pid
						where D.Diag_id = :Diag_id
					)
				";
				break;
		}
		$query = "
			select
				to_char(PR.PersonRegister_disDate, '{$this->dateTimeForm104}') as \"PersonRegister_disDate\"
				,PR.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\"
				,PR.PersonRegister_id as \"PersonRegister_id\"
				{$add_select}
			from
				v_PersonRegister PR
				{$add_join}
			where PR.Person_id = :Person_id {$add_where}
			order by
				PR.PersonRegister_disDate,
				PR.PersonRegister_insDT DESC
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка БД!");
		}
		$response = $result->result('array');
		$result = true;
		if (count($response) > 0) {
			foreach ($response as $line) {
				switch (true) {
					case ("prof" == $this->MorbusType_SysNick && empty($this->ignoreCheckAnotherDiag) && $line["Diag_id"] != $this->Diag_id):
						$result = array(array(
							"Error_Msg" => "", "Alert_Code" => "ProfDiag",
							"Alert_Msg" => "Выбранный пациент уже состоит в регистре по профзаболеваниям с диагнозом {$line["Diag_Name"]} по заболеванию {$line["MorbusProfDiag_Name"]}."
						));
						break;
					case ("narc" == $this->MorbusType_SysNick && isset($line["PersonRegister_disDate"]) && isset($line["PersonRegisterOutCause_id"])):
						break;
					case ("crazy" == $this->MorbusType_SysNick && isset($line["PersonRegister_disDate"]) && isset($line["PersonRegisterOutCause_id"])):
						//Если тип заболевания Crazy(narc) и был исключен из регистра.
						$line["Alert_Msg"] = "Пациент был исключен из регистра. <br />Вернуть пациента в регистр?"; //(Новое/Предыдущее/Отмена)
						$line["Yes_Mode"] = "homecoming";
						$line["Error_Msg"] = null;
						$result = [$line];
						//При нажатии "Новое" создавать новое заболевание. При нажатии "Предыдущее" удалить дату закрытия заболевания (все ранее введенная специфика становится доступна для ввода/редактирования)
						break;
					case (empty($line["PersonRegister_disDate"]) && empty($line["PersonRegisterOutCause_id"])):
						//Если уже есть запись регистра с открытым заболеванием, то выводить сообщение: "На выбранного пациента уже существует запись регистра ...", новую запись регистра не создавать.
						$result = array(array("Error_Msg" => $error_msg));
						break;
					case (isset($line["PersonRegister_disDate"]) && $line["PersonRegisterOutCause_id"] == 1):
						//Если уже есть запись регистра с закрытым заболеванием и причина исключения из регистра "смерть"
						$result = array(array("Error_Msg" => "Пациент был исключен из регистра по причине 'смерть', <br />включение в регистр невозможно"));
						break;
					case (isset($line["PersonRegister_disDate"]) && $line["PersonRegisterOutCause_id"] == 2):
						//Если уже есть запись регистра с закрытым заболеванием и причина исключения из регистра "выехал"
						$line["Alert_Msg"] = "Пациент был исключен из регистра по причине 'выехал'. <br />Вернуть пациента в регистр?"; // Да/Нет
						$line["Yes_Mode"] = "homecoming";
						$line["Error_Msg"] = null;
						$result = [$line];
						//При нажатии "Да" удалить дату закрытия заболевания (все ранее введенная специфика становится доступна для ввода/редактирования). При нажатии "Нет", форму закрывать, новую запись регистра не создавать.
						break;
					case (isset($line["PersonRegister_disDate"]) && $line["PersonRegisterOutCause_id"] == 3):
						//Если уже есть запись регистра с закрытым заболеванием и причина исключения из регистра "Выздоровление"
						$line["Alert_Msg"] = "Пациент был исключен из регистра по причине 'Выздоровление'. <br />У пациента новое заболевание?"; //(Новое/Предыдущее/Отмена)
						$line["Yes_Mode"] = "new";
						$line["No_Mode"] = "relapse";
						$line["Error_Msg"] = null;
						$result = [$line];
						//При нажатии "Новое" создавать новое заболевание. При нажатии "Предыдущее" удалить дату закрытия заболевания (все ранее введенная специфика становится доступна для ввода/редактирования)
						break;
					
				}
				if (!empty($result[0]["Error_Msg"]) || !empty($result[0]["Alert_Msg"])) {
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * @param $filter
	 * @return array|bool
	 */
	function loadList($filter)
	{
		$where = [];
		$queryParams = [];
		if (isset($filter["PersonRegister_id"]) && $filter["PersonRegister_id"]) {
			$where[] = "v_PersonRegister.PersonRegister_id = :PersonRegister_id";
			$queryParams["PersonRegister_id"] = $filter["PersonRegister_id"];
		}
		if (isset($filter["PersonRegisterType_id"]) && $filter["PersonRegisterType_id"]) {
			$where[] = "v_PersonRegister.PersonRegisterType_id = :PersonRegisterType_id";
			$queryParams["PersonRegisterType_id"] = $filter["PersonRegisterType_id"];
		}
		if (isset($filter["PersonRegister_Date"]) && $filter["PersonRegister_Date"]) {
			//добавил для v_PersonRegister.PersonRegister_setDate и :PersonRegister_Date преобразование в тип date, т.к. при типе datetime на форме выписки рецепта неправильно отображалась информация о нахождении
			//пациентов в регистре ВЗН в случае, когда пациент был включен в регистр в день выписки рецепта. https://redmine.swan-it.ru/issues/164708 05.08.2019 Grigorev
			$where[] = "
				(
					(cast(v_PersonRegister.PersonRegister_setDate as date) <= cast(:PersonRegister_Date as date)) and
					(cast(v_PersonRegister.PersonRegister_disDate as date) is null or v_PersonRegister.PersonRegister_disDate >= cast(:PersonRegister_Date as date))
				)
			";
			$queryParams["PersonRegister_Date"] = $filter["PersonRegister_Date"];
		}
		if (isset($filter["Person_id"]) && $filter["Person_id"]) {
			$where[] = "v_PersonRegister.Person_id = :Person_id";
			$queryParams["Person_id"] = $filter["Person_id"];
		}
		if (isset($filter["MorbusType_id"]) && $filter["MorbusType_id"]) {
			$where[] = "v_PersonRegister.MorbusType_id = :MorbusType_id";
			$queryParams["MorbusType_id"] = $filter["MorbusType_id"];
		}
		if (isset($filter["Diag_id"]) && $filter["Diag_id"]) {
			$where[] = "v_PersonRegister.Diag_id = :Diag_id";
			$queryParams["Diag_id"] = $filter["Diag_id"];
		}
		if (isset($filter["PersonRegister_Code"]) && $filter["PersonRegister_Code"]) {
			$where[] = "v_PersonRegister.PersonRegister_Code = :PersonRegister_Code";
			$queryParams["PersonRegister_Code"] = $filter["PersonRegister_Code"];
		}
		if (isset($filter["PersonRegister_setDate"]) && $filter["PersonRegister_setDate"]) {
			$where[] = "v_PersonRegister.PersonRegister_setDate = cast(:PersonRegister_setDate as date)";
			$queryParams["PersonRegister_setDate"] = $filter["PersonRegister_setDate"];
		}
		if (isset($filter["PersonRegister_disDate"]) && $filter["PersonRegister_disDate"]) {
			$where[] = "v_PersonRegister.PersonRegister_disDate = cast(:PersonRegister_disDate as date)";
			$queryParams["PersonRegister_disDate"] = $filter["PersonRegister_disDate"];
		}
		if (isset($filter["Morbus_id"]) && $filter["Morbus_id"]) {
			$where[] = "v_PersonRegister.Morbus_id = :Morbus_id";
			$queryParams["Morbus_id"] = $filter["Morbus_id"];
		}
		if (isset($filter["PersonRegisterOutCause_id"]) && $filter["PersonRegisterOutCause_id"]) {
			$where[] = "v_PersonRegister.PersonRegisterOutCause_id = :PersonRegisterOutCause_id";
			$queryParams["PersonRegisterOutCause_id"] = $filter["PersonRegisterOutCause_id"];
		}
		if (isset($filter["MedPersonal_iid"]) && $filter["MedPersonal_iid"]) {
			$where[] = "v_PersonRegister.MedPersonal_iid = :MedPersonal_iid";
			$queryParams["MedPersonal_iid"] = $filter["MedPersonal_iid"];
		}
		if (isset($filter["Lpu_iid"]) && $filter["Lpu_iid"]) {
			$where[] = "v_PersonRegister.Lpu_iid = :Lpu_iid";
			$queryParams["Lpu_iid"] = $filter["Lpu_iid"];
		}
		if (isset($filter["MedPersonal_did"]) && $filter["MedPersonal_did"]) {
			$where[] = "v_PersonRegister.MedPersonal_did = :MedPersonal_did";
			$queryParams["MedPersonal_did"] = $filter["MedPersonal_did"];
		}
		if (isset($filter["Lpu_did"]) && $filter["Lpu_did"]) {
			$where[] = "v_PersonRegister.Lpu_did = :Lpu_did";
			$queryParams["Lpu_did"] = $filter["Lpu_did"];
		}
		if (isset($filter["EvnNotifyBase_id"]) && $filter["EvnNotifyBase_id"]) {
			$where[] = "v_PersonRegister.EvnNotifyBase_id = :EvnNotifyBase_id";
			$queryParams["EvnNotifyBase_id"] = $filter["EvnNotifyBase_id"];
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
			select
				v_PersonRegister.PersonRegister_id as \"PersonRegister_id\",
			    v_PersonRegister.Person_id as \"Person_id\",
			    v_PersonRegister.MorbusType_id as \"MorbusType_id\",
			    v_PersonRegister.Diag_id as \"Diag_id\",
			    v_PersonRegister.PersonRegister_Code as \"PersonRegister_Code\",
			    v_PersonRegister.PersonRegister_setDate as \"PersonRegister_setDate\",
			    v_PersonRegister.PersonRegister_disDate as \"PersonRegister_disDate\",
			    v_PersonRegister.Morbus_id as \"Morbus_id\",
			    v_PersonRegister.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			    v_PersonRegister.MedPersonal_iid as \"MedPersonal_iid\",
			    v_PersonRegister.Lpu_iid as \"Lpu_iid\",
			    v_PersonRegister.MedPersonal_did as \"MedPersonal_did\",
			    v_PersonRegister.Lpu_did as \"Lpu_did\",
			    v_PersonRegister.EvnNotifyBase_id as \"EvnNotifyBase_id\",
			    MorbusType_id_ref.MorbusType_Name as \"MorbusType_id_Name\",
			    Diag_id_ref.Diag_Name as \"Diag_id_Name\",
			    PersonRegisterOutCause_id_ref.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_id_Name\",
			    Lpu_iid_ref.Lpu_Name as \"Lpu_iid_Name\",
			    Lpu_did_ref.Lpu_Name as \"Lpu_did_Name\",
			    Diag_id_ref.Diag_pid as \"Diag_pid\"
			from
				v_PersonRegister
				left join v_Person Person_id_ref on Person_id_ref.Person_id = v_PersonRegister.Person_id
				left join v_MorbusType MorbusType_id_ref on MorbusType_id_ref.MorbusType_id = v_PersonRegister.MorbusType_id
				left join v_Diag Diag_id_ref on Diag_id_ref.Diag_id = v_PersonRegister.Diag_id
				left join v_PersonRegisterOutCause PersonRegisterOutCause_id_ref on PersonRegisterOutCause_id_ref.PersonRegisterOutCause_id = v_PersonRegister.PersonRegisterOutCause_id
				left join persis.v_MedWorker MedPersonal_iid_ref on MedPersonal_iid_ref.MedWorker_id = v_PersonRegister.MedPersonal_iid
				left join v_Lpu Lpu_iid_ref on Lpu_iid_ref.Lpu_id = v_PersonRegister.Lpu_iid
				left join persis.v_MedWorker MedPersonal_did_ref on MedPersonal_did_ref.MedWorker_id = v_PersonRegister.MedPersonal_did
				left join v_Lpu Lpu_did_ref on Lpu_did_ref.Lpu_id = v_PersonRegister.Lpu_did
				left join v_EvnNotifyBase EvnNotifyBase_id_ref on EvnNotifyBase_id_ref.EvnNotifyBase_id = v_PersonRegister.EvnNotifyBase_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Проверки перед сохранением
	 * @return array|bool|CI_DB_result
	 * @throws Exception
	 */
	function beforeSave()
	{
		$this->CrazyType = null;
		// Проверка типа регистра
        if (isset($this->Diag_id) && !($this->getRegionNick() == 'saratov' && ($this->Diag_id >= 11204) && ($this->Diag_id <= 11213)) && !in_array($this->MorbusType_SysNick, array('suicide', 'palliat', 'geriatrics', 'gibt'))) {
			$this->load->library("swMorbus");
			$arr = swMorbus::getMorbusTypeListByDiag($this->Diag_id, false);
			if (empty($arr)) {
				throw new Exception("Выбранному диагнозу не сопоставлен тип регистра");
			}
			// Одному диагнозу может соответствовать несколько типов регистра
			$MorbusType_id = null;
			$MorbusType_SysNick = null;
			$RegistryType = null;
			foreach ($arr as $id => $tmpArr) {
				foreach ($tmpArr as $row) {
					if (empty($this->MorbusType_id)) {
						$MorbusType_id = $id;
						$MorbusType_SysNick = $row["MorbusType_SysNick"];
						$RegistryType = $row["RegistryType"];
						break;
					} else {
						// Подбираем подходящий
						if ($id == $this->MorbusType_id) {
							$MorbusType_id = $id;
							$MorbusType_SysNick = $row["MorbusType_SysNick"];
							$RegistryType = $row["RegistryType"];
							break;
						}
					}
				}
			}
			if (empty($MorbusType_id) || empty($MorbusType_SysNick) || empty($RegistryType)) {
				throw new Exception("Выбранный диагноз не соответствует данному типу регистра");
			}
			$this->_MorbusType_id = $MorbusType_id;
			$this->_MorbusType_SysNick = $MorbusType_SysNick;
		} else {
			if ($this->getRegionNick() == "saratov" && ($this->Diag_id >= 11204) && ($this->Diag_id <= 11213))
				$this->PersonRegisterType_id = 60;
            if (!in_array($this->_MorbusType_id, array(84, 90, 91, 94, 100, 103))) {
				$this->_MorbusType_id = null;
				$this->_MorbusType_SysNick = null;
			}
		}
		if ($this->MorbusType_SysNick == "crazy") {
			$this->setPersonRegisterType_SysNick("crazy");
		} else if ($this->MorbusType_SysNick == "narc") {
			$this->setPersonRegisterType_SysNick("narc");
		}
		// Проверка на существование записи регистра
		if (empty($this->PersonRegister_id) && empty($this->Mode)) {
			$response = $this->_checkPersonRegisterExist();
			if (is_array($response) && count($response) > 0) {
				return $response;
			}
		}
		/*
		 * Для пользователей, у которых не указана группа «суперадминистратор СВАН»
		 * реализовать возможность добавления новых записей регистра с типом «орфанное» только для прикрепленного населения.
		 */
		if (empty($this->PersonRegister_id) && "orphan" == $this->MorbusType_SysNick) {
			$this->load->model("EvnNotifyOrphan_model");
			$funcParams = [
				"MedPersonal_id" => $this->MedPersonal_iid,
				"Person_id" => $this->Person_id
			];
			$result = $this->EvnNotifyOrphan_model->medpersonalBaseAttach($funcParams);
			if (empty($result)) {
				if (!isSuperadmin()) {
					throw new Exception("Пациент не имеет основного типа прикрепления или вы не являетесь врачом по основному прикреплению пациента");
				}
			} else {
				$this->MedPersonal_iid = $result[0]["MedPersonal_id"];
				$this->Lpu_iid = $result[0]["Lpu_id"];
			}
		}
		//контроль даты включения в регистр (не больше текущей)
		if (trim($this->PersonRegister_setDate) > date("Y-m-d")) {
			throw new Exception("Дата включения в регистр не может быть больше текущей даты");
		}
		if (in_array($this->MorbusType_SysNick, array("ipra"))) {
			//контроль даты исключения из регистра (не меньше даты включения в регистр)
			if (isset($this->PersonRegister_disDate) && isset($this->PersonRegister_setDate) && trim($this->PersonRegister_disDate) < trim($this->PersonRegister_setDate) && !isset($this->autoExcept)) {
				throw new Exception("Дата исключения из регистра не может быть меньше даты включения в регистр");
			}
		} else {
			//контроль даты исключения из регистра (не меньше или равно дате включения в регистр)
			if (isset($this->PersonRegister_disDate) && isset($this->PersonRegister_setDate) && trim($this->PersonRegister_disDate) <= trim($this->PersonRegister_setDate) && !isset($this->autoExcept)) {
				throw new Exception("Дата исключения из регистра не может быть меньше или равна дате включения в регистр");
			}
		}
		//контроль даты исключения из регистра (не больше текущей)
		if (isset($this->PersonRegister_disDate) && trim($this->PersonRegister_disDate) > date('Y-m-d') && !isset($this->autoExcept)) {
			throw new Exception("Дата исключения из регистра не может быть больше текущей даты");
		}
		//это ручной ввод новой записи регистра без извещения
		if (empty($this->PersonRegister_id) && empty($this->EvnNotifyBase_id) && empty($this->Morbus_id) && isset($this->Person_id) && !empty($this->MorbusType_SysNick)) {
			$query = "
				select
					dbo.Age2(ps.Person_BirthDay,:setDate) \"as Person_Age\",
					PS.Server_id as \"Server_id\",
					PS.PersonEvn_id as \"PersonEvn_id\"
				from v_PersonState PS
				where PS.Person_id = :Person_id
				limit 1
			";
			$queryParams = [
				"Person_id" => $this->Person_id,
				"setDate" => $this->PersonRegister_setDate
			];
			$personData = $this->getFirstRowFromQuery($query, $queryParams);
			if (empty($personData)) {
				throw new Exception("Человек не найден");
			}
			if ("acs" == $this->MorbusType_SysNick && $personData["Person_Age"] < 18) {
				throw new Exception("Возраст пациента составляет менее 18 лет на момент поступления в стационар.");
			}
			if (swMorbus::isRequiredPersonRegisterMorbus_id($this->MorbusType_SysNick)) {
				//создание заболевания с проверкой на существование заболевания у человека
				try {
					$funcParams = [
						"isDouble" => (isset($this->Mode) && $this->Mode == "new"),
						"Diag_id" => $this->Diag_id,
						"Person_id" => $this->Person_id,
						"Morbus_setDT" => $this->PersonRegister_setDate,
						"Morbus_confirmDate" => $this->Morbus_confirmDate,
						"Morbus_EpidemCode" => $this->Morbus_EpidemCode,
						"session" => $this->sessionParams
					];
					$result = swMorbus::checkByPersonRegister($this->MorbusType_SysNick, $funcParams, "onBeforeSavePersonRegister");
				} catch (Exception $e) {
					throw new Exception($e->getMessage());
				}
				if (empty($result["Morbus_id"])) {
					throw new Exception("Проверка существования и создание заболевания. По какой-то причине заболевание не найдено и не создано");
				}
				$this->Morbus_id = $result["Morbus_id"];
				if (!empty($result["MorbusHIV_id"])) {
					$this->MorbusHIV_id = $result["MorbusHIV_id"];
				} elseif (!empty($result["MorbusOnko_id"])) {
					$this->MorbusOnko_id = $result["MorbusOnko_id"];
				} elseif (!empty($result["MorbusTub_id"])) {
					$this->MorbusTub_id = $result["MorbusTub_id"];
				}
				/*
				 * При добавлении записи регистра из формы «Регистр по орфанным заболеваниям» автоматически создавать «Направление на включение в регистр»
				 */
				if ("orphan" == $this->MorbusType_SysNick) {
					$this->load->model("EvnNotifyOrphan_model");
					$funcParams = [
						"session" => $this->sessionParams,
						"scenario" => "onAddPersonRegister",
						"Morbus_id" => $this->Morbus_id,
						"MorbusType_id" => $this->MorbusType_id,
						"MedPersonal_id" => $this->MedPersonal_iid,
						"Lpu_id" => $this->Lpu_iid,
						"EvnNotifyOrphan_setDT" => $this->PersonRegister_setDate,
						"Person_id" => $this->Person_id,
						"Server_id" => $personData["Server_id"],
						"PersonEvn_id" => $personData["PersonEvn_id"]
					];
					$result = $this->EvnNotifyOrphan_model->doSave($funcParams, false);
					if (isset($result["Error_Msg"])) {
						throw new Exception($result["Error_Msg"]);
					}
					if (empty($result["EvnNotifyOrphan_id"])) {
						throw new Exception("Ошибка создания направления на включение в регистр");
					}
					$this->EvnNotifyBase_id = $result["EvnNotifyOrphan_id"];
				}
			}
		}
		return true;
	}

	/**
	 * Создание/обновление записи регистра
	 * @return array|bool|CI_DB_result
	 * @throws Exception
	 */
	function save()
	{
		$procedure = (empty($this->PersonRegister_id)) ? "p_PersonRegister_ins" : "p_PersonRegister_upd";
		// Стартуем транзакцию
		if (!$this->beginTransaction()) {
			throw new Exception("Ошибка при попытке запустить транзакцию");
		}
		$result = $this->beforeSave();
		if (is_array($result) && (isset($result[0]["Error_Msg"]) || isset($result[0]["Alert_Msg"]))) {
			$this->rollbackTransaction();
			return $result;
		}
		$selectString = "
		    personregister_id as \"PersonRegister_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    personregister_id := :PersonRegister_id,
			    person_id := :Person_id,
			    morbustype_id := :MorbusType_id,
			    diag_id := :Diag_id,
			    personregister_code := :PersonRegister_Code,
			    personregister_setdate := cast(:PersonRegister_setDate as date),
			    personregisteroutcause_id := :PersonRegisterOutCause_id,
			    medpersonal_iid := :MedPersonal_iid,
			    lpu_iid := :Lpu_iid,
			    evnnotifybase_id := :EvnNotifyBase_id,
			    personregister_disdate := cast(:PersonRegister_disDate as date),
			    medpersonal_did := :MedPersonal_did,
			    lpu_did := :Lpu_did,
			    morbus_id := :Morbus_id,
			    personregistertype_id := :PersonRegisterType_id,
			    personregister_alcoholemia := :PersonRegister_Alcoholemia,
			    pregnancyresult_id := :PregnancyResult_id,
			    risktype_id := :RiskType_id,
			    persondeathcause_id := :PersonDeathCause_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"PersonRegister_id" => $this->PersonRegister_id,
			"Person_id" => $this->Person_id,
			"PersonRegisterType_id" => $this->PersonRegisterType_id,
			"MorbusType_id" => $this->MorbusType_id,
			"Diag_id" => $this->Diag_id,
			"PersonRegister_Code" => $this->PersonRegister_Code,
			"PersonRegister_setDate" => $this->PersonRegister_setDate,
			"PersonRegister_disDate" => $this->PersonRegister_disDate,
			"Morbus_id" => $this->Morbus_id,
			"PersonRegisterOutCause_id" => $this->PersonRegisterOutCause_id,
			"PersonDeathCause_id" => $this->PersonDeathCause_id,
			"PersonRegister_Alcoholemia" => $this->PersonRegister_Alcoholemia,
			"MedPersonal_iid" => $this->MedPersonal_iid,
			"Lpu_iid" => $this->Lpu_iid,
			"MedPersonal_did" => $this->MedPersonal_did,
			"Lpu_did" => $this->Lpu_did,
			"EvnNotifyBase_id" => $this->EvnNotifyBase_id,
			"RiskType_id" => $this->RiskType_id,
			"PregnancyResult_id" => $this->PregnancyResult_id,
			"pmUser_id" => $this->pmUser_id,
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result("array");
		if (!$this->histCreated) {
			//Сохранение истории изменения данных регистра
			$funcParams = [
				"PersonRegister_id" => $result[0]["PersonRegister_id"],
				"PersonRegisterHist_NumCard" => $this->PersonRegister_Code,
				"PersonRegisterHist_begDate" => empty($this->PersonRegister_id) ? $this->PersonRegister_setDate : null,
				"Lpu_id" => $this->Lpu_iid,
				"MedPersonal_id" => $this->MedPersonal_iid,
				"pmUser_id" => $this->pmUser_id,
				"PersonRegisterUpdated" => true
			];
			$resp = $this->createPersonRegisterHist($funcParams);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]["Error_Msg"], 500);
			}
		}
		//Если это исключение записи из регистра, то закрывать соответствующее заболевание
		if (isset($this->Morbus_id) && isset($this->PersonRegister_id) && isset($this->PersonRegisterOutCause_id) && isset($this->PersonRegister_disDate)) {
			$this->load->model("Morbus_model");
			$funcParams = [
				"Morbus_id" => $this->Morbus_id,
				"Morbus_disDT" => $this->PersonRegister_disDate,
				"pmUser_id" => $this->pmUser_id
			];
			$tmp = $this->Morbus_model->closeMorbus($funcParams);
			if (false == empty($tmp[0]["Error_Msg"])) {
				$this->rollbackTransaction();
				throw new Exception($tmp[0]["Error_Msg"], 500);
			}
			if (in_array($this->MorbusType_SysNick, ["crazy", "narc"]) && $this->getRegionNick() != "ufa") {
				$this->load->model("MorbusCrazy_model");
				$funcParams = [
					"Morbus_id" => $this->Morbus_id,
					"pmUser_id" => $this->pmUser_id,
					"CrazyCauseEndSurveyType_id" => $this->PersonRegisterOutCause_id
				];
				$this->MorbusCrazy_model->setCauseEndSurveyType($funcParams);
			}
		}
		// Возвращение пациента в регистр, нужно удалить дату закрытия заболевания
		if (isset($this->Morbus_id) && isset($this->Mode) && isset($this->PersonRegister_id) && in_array($this->Mode, ["homecoming", "relapse"])) {
			$this->load->model("Morbus_model");
			$funcParams = [
				"Morbus_id" => $this->Morbus_id,
				"pmUser_id" => $this->pmUser_id
			];
			$tmp = $this->Morbus_model->openMorbus($funcParams);
			if (false == empty($tmp[0]["Error_Msg"])) {
				$this->rollbackTransaction();
				throw new Exception($tmp[0]["Error_Msg"], 500);
			}
			if (in_array($this->MorbusType_SysNick, ["crazy", "narc"]) && $this->getRegionNick() != "ufa") {
				$this->load->model("MorbusCrazy_model");
				$funcParams = [
					"Morbus_id" => $this->Morbus_id,
					"pmUser_id" => $this->pmUser_id,
					"CrazyCauseEndSurveyType_id" => null
				];
				$this->MorbusCrazy_model->setCauseEndSurveyType($funcParams);
			}
		}
		// Включение пациента в регистр на основе извещения
		if (isset($this->EvnNotifyBase_id) && (empty($this->PersonRegister_id) || (isset($this->Mode) && isset($this->PersonRegister_id) && in_array($this->Mode, ["homecoming", "relapse"])))) {
			$en = $this->loadEvnNotifyBaseData(["EvnNotifyBase_id" => $this->EvnNotifyBase_id]);
			if ("nephro" == $this->MorbusType_SysNick && is_array($en) && count($en) == 1) {
				$this->load->model("Messages_model");
				$funcParams = [
					"autotype" => 1,
					"pmUser_id" => $this->pmUser_id,
					"User_rid" => $en[0]["pmUser_insID"],
					"Lpu_rid" => $en[0]["Lpu_id"],
					"MedPersonal_rid" => $en[0]["MedPersonal_id"],
					"type" => 1,
					"title" => "Включение пациента в регистр",
					"text" => "Пациент {$en[0]["Person_SurName"]} {$en[0]["Person_FirName"]} {$en[0]["Person_SecName"]} {$en[0]["Person_BirthDay"]} включен в регистр по нефрологии.",
				];
				$this->Messages_model->autoMessage($funcParams);
			}
			if ($this->getRegionNick() == "perm" && "onko" == $this->MorbusType_SysNick && is_array($en)) {
				$this->load->model("Messages_model");
				$funcParams = [
					"autotype" => 1,
					"pmUser_id" => $this->pmUser_id,
					"User_rid" => $en[0]["pmUser_insID"],
					"Lpu_rid" => $en[0]["Lpu_id"],
					"MedPersonal_rid" => $en[0]["MedPersonal_id"],
					"type" => 1,
					"title" => "Пациент включен в регистр по онкологии",
					"text" => ("Пациент {$en[0]["Person_SurName"]} {$en[0]["Person_FirName"]} {$en[0]["Person_SecName"]}, {$en[0]["Person_BirthDay"]}, включен в регистр по онкологии. Создано " . date("d.m.Y H:i") . "."),
				];
				$this->Messages_model->autoMessage($funcParams);
			}
		}
		if ("palliat" == $this->MorbusType_SysNick) {
			// Пациент прикреплен к участку, с которым связано место работы врача
			// В разделе «Уведомления по классам событий для врача поликлиники» подят флаг «Включение пациента в регистр паллиативной помощи»
			$query = "
				select
					ps.Person_SurName as \"Person_SurName\",
					ps.Person_FirName as \"Person_FirName\",
					ps.Person_SecName as \"Person_SecName\",
					to_char(ps.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
					MSR.MedPersonal_id as \"MedPersonal_id\",
					MSR.Lpu_id as \"Lpu_id\",
					pu.pmUser_id as \"pmUser_id\"
				from
					v_PersonState ps
					inner join v_PersonCardState PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					inner join v_MedStaffRegion MSR on MSR.LpuRegion_id = PC.LpuRegion_id
					inner join v_pmUserCache pu on pu.MedPersonal_id = MSR.MedPersonal_id and pu.pmUser_evnclass ilike '%PersonRegisterPalliat%'
				where ps.Person_id = :Person_id
			";
			$queryParams = ["Person_id" => $this->Person_id];
			$resp_msf = $this->queryResult($query, $queryParams);
			$this->load->model("Messages_model");
			foreach ($resp_msf as $one_msf) {
				$funcParams = [
					"autotype" => 1,
					"pmUser_id" => $this->pmUser_id,
					"User_rid" => $one_msf["pmUser_id"],
					"Lpu_rid" => $one_msf["Lpu_id"],
					"MedPersonal_rid" => $one_msf["MedPersonal_id"],
					"type" => 1,
					"title" => "Пациент включен в регистр по паллиативной помощи",
					"text" => ("Пациент {$one_msf["Person_SurName"]} {$one_msf["Person_FirName"]} {$one_msf["Person_SecName"]}, {$one_msf["Person_BirthDay"]} был включен в регистр по паллиативной помощи " . date("d.m.Y H:i") . "."),
				];
				$this->Messages_model->autoMessage($funcParams);
			}
		}

		$this->_afterSave($result);
		$this->commitTransaction();
		if (!empty($this->PersonRegisterType_id) && $this->PersonRegisterType_id == 9 && !empty($this->MorbusHIV_id) && !empty($result[0]["PersonRegister_id"])) {
			$result[0]["MorbusHIV_id"] = $this->MorbusHIV_id;
		}
		if (!empty($this->MorbusHIV_id)) {
			$result[0]["MorbusHIV_id"] = $this->MorbusHIV_id;
		} elseif (!empty($this->MorbusOnko_id)) {
			$result[0]["MorbusOnko_id"] = $this->MorbusOnko_id;
		} elseif (!empty($this->MorbusTub_id)) {
			$result[0]["MorbusTub_id"] = $this->MorbusTub_id;
		}
		$this->PersonRegister_id = $result[0]["PersonRegister_id"];
		return $result;
	}

	/**
	 * Сохранение данных регистра
	 * @param $data
	 * @return array|bool|CI_DB_result
	 * @throws Exception
	 */
	public function savePersonRegister($data)
	{
		try {
			$this->setSessionParams($data["session"]);
			if (!empty($data["PersonRegister_id"])) {
				$this->setPersonRegister_id($data["PersonRegister_id"]);
				if (!$this->load()) {
					throw new Exception("Ошибка при получении данных регистра");
				}
			}
			if (array_key_exists("Person_id", $data)) {
				$this->setPerson_id($data["Person_id"]);
			}
			if (array_key_exists("PersonRegisterType_id", $data)) {
				$this->setPersonRegisterType_id($data["PersonRegisterType_id"]);
			}
			if (array_key_exists("PersonRegisterType_SysNick", $data)) {
				$this->setPersonRegisterType_SysNick($data["PersonRegisterType_SysNick"]);
			}
			if (array_key_exists("MorbusType_id", $data)) {
				$this->setMorbusType_id($data["MorbusType_id"]);
			}
			if (array_key_exists("MorbusType_SysNick", $data)) {
				$this->setMorbusType_SysNick($data["MorbusType_SysNick"]);
			}
			if (array_key_exists("Diag_id", $data)) {
				$this->setDiag_id($data["Diag_id"]);
			}
			if (array_key_exists("MorbusProfDiag_id", $data)) {
				$this->setMorbusProfDiag_id($data["MorbusProfDiag_id"]);
			}
			if (array_key_exists("ignoreCheckAnotherDiag", $data)) {
				$this->setignoreCheckAnotherDiag($data["ignoreCheckAnotherDiag"]);
			}
			if (array_key_exists("PersonRegister_Code", $data)) {
				$this->setPersonRegister_Code($data["PersonRegister_Code"]);
			}
			if (array_key_exists("PersonRegister_setDate", $data)) {
				$this->setPersonRegister_setDate($data["PersonRegister_setDate"]);
			}
			if (array_key_exists("PersonRegister_disDate", $data)) {
				$this->setPersonRegister_disDate($data["PersonRegister_disDate"]);
			}
			if (array_key_exists("Morbus_id", $data)) {
				$this->setMorbus_id($data["Morbus_id"]);
			}
			if (array_key_exists("PersonRegisterOutCause_id", $data)) {
				$this->setPersonRegisterOutCause_id($data["PersonRegisterOutCause_id"]);
			}
			if (array_key_exists("PersonRegisterOutCause_SysNick", $data)) {
				$this->setPersonRegisterOutCause_SysNick($data["PersonRegisterOutCause_SysNick"]);
			}
			if (array_key_exists("PersonRegister_Alcoholemia", $data)) {
				$this->setPersonRegister_Alcoholemia($data["PersonRegister_Alcoholemia"]);
			}
			if (array_key_exists("MedPersonal_iid", $data)) {
				$this->setMedPersonal_iid($data["MedPersonal_iid"]);
			}
			if (array_key_exists("Lpu_iid", $data)) {
				$this->setLpu_iid($data["Lpu_iid"]);
			}
			if (array_key_exists("MedPersonal_did", $data)) {
				$this->setMedPersonal_did($data["MedPersonal_did"]);
			}
			if (array_key_exists("Lpu_did", $data)) {
				$this->setLpu_did($data["Lpu_did"]);
			}
			if (array_key_exists("EvnNotifyBase_id", $data)) {
				$this->setEvnNotifyBase_id($data["EvnNotifyBase_id"]);
			}
			if (array_key_exists("RiskType_id", $data)) {
				$this->setRiskType_id($data["RiskType_id"]);
			}
			if (array_key_exists("PregnancyResult_id", $data)) {
				$this->setPregnancyResult_id($data["PregnancyResult_id"]);
			}
			if (array_key_exists("autoExcept", $data) && $data["autoExcept"]) {
				$this->setAutoExcept(true);
			}
			if (array_key_exists("histCreated", $data) && $data["histCreated"]) {
				$this->setHistCreated(true);
			}
			$response = $this->save();
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
		return $response;
	}

	/**
	 * Сохранение причины невключения в регистр
	 * @param $data
	 * @return array|bool
	 */
	private function saveEvnNotifyBase($data)
	{
		$procedure = (isset($data["EvnNotifyBase_id"])) ? "p_EvnNotifyBase_upd" : "p_EvnNotifyBase_ins";
		$queryParams = [
			"EvnNotifyBase_id" => $data["EvnNotifyBase_id"],
			"EvnNotifyBase_pid" => $data["EvnNotifyBase_pid"],
			"EvnNotifyBase_rid" => $data["EvnNotifyBase_rid"],
			"EvnNotifyBase_setDT" => $data["EvnNotifyBase_setDT"],
			"EvnNotifyBase_disDT" => $data["EvnNotifyBase_disDT"],
			"EvnNotifyBase_didDT" => $data["EvnNotifyBase_didDT"],
			"EvnNotifyBase_niDate" => $data["EvnNotifyBase_niDate"],
			"PersonRegisterFailIncludeCause_id" => $data["PersonRegisterFailIncludeCause_id"],
			"MedPersonal_niid" => $data["MedPersonal_niid"],
			"Lpu_niid" => $data["Lpu_niid"],
			"EvnOnkoNotify_Comment" => (!empty($data["EvnOnkoNotify_Comment"]) ? $data["EvnOnkoNotify_Comment"] : null),
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"Lpu_id" => $data["Lpu_id"],
			"MedPersonal_id" => $data["MedPersonal_id"],
			"EvnNotifyBase_signDT" => $data["EvnNotifyBase_signDT"],
			"EvnNotifyBase_IsSigned" => $data["EvnNotifyBase_IsSigned"],
			"pmUser_signID" => $data["pmUser_signID"],
			"Morbus_id" => $data["Morbus_id"],
			"MorbusType_id" => $data["MorbusType_id"],
			"EvnNotifyBase_IsAuto" => (!empty($data["EvnNotifyBase_IsAuto"]) ? $data["EvnNotifyBase_IsAuto"] : null),
			"pmUser_id" => $data["pmUser_id"]
		];
		$selectString = "
		    evnnotifybase_id as \"EvnNotifyBase_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    evnnotifybase_id := :EvnNotifyBase_id,
			    evnnotifybase_pid := :EvnNotifyBase_pid,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnnotifybase_setdt := :EvnNotifyBase_setDT,
			    evnnotifybase_disdt := :EvnNotifyBase_disDT,
			    evnnotifybase_diddt := :EvnNotifyBase_didDT,
			    morbus_id := :Morbus_id,
			    evnnotifybase_issigned := :EvnNotifyBase_IsSigned,
			    pmuser_signid := :pmUser_signID,
			    evnnotifybase_signdt := :EvnNotifyBase_signDT,
			    morbustype_id := :MorbusType_id,
			    medpersonal_id := :MedPersonal_id,
			    evnnotifybase_nidate := cast(:EvnNotifyBase_niDate as date),
			    medpersonal_niid := :MedPersonal_niid,
			    lpu_niid := :Lpu_niid,
			    personregisterfailincludecause_id := :PersonRegisterFailIncludeCause_id,
			    evnonkonotify_comment := :EvnOnkoNotify_Comment,
			    evnnotifybase_isauto := :EvnNotifyBase_IsAuto,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result("array");
		if ("nephro" == $data["MorbusType_SysNick"] && empty($response[0]["Error_Msg"])) {
			$this->load->model("Messages_model");
			$funcParams = [
				"autotype" => 1,
				"pmUser_id" => $data["pmUser_id"],
				"User_rid" => $data["pmUser_insID"],
				"Lpu_rid" => $data["Lpu_id"],
				"MedPersonal_rid" => $data["MedPersonal_id"],
				"type" => 1,
				"title" => "Не включение пациента в регистр",
				"text" => "Пациент {$data["Person_SurName"]} {$data["Person_FirName"]} {$data["Person_SecName"]} {$data["Person_BirthDay"]} не включен в регистр по нефрологии (по причине {$data["PersonRegisterFailIncludeCause_Name"]}).",
			];
			$this->Messages_model->autoMessage($funcParams);
		}
		if ($this->getRegionNick() == "perm" && "onko" == $data["MorbusType_SysNick"] && empty($response[0]["Error_Msg"])) {
			$this->load->model("Messages_model");
			$funcParams = [
				"autotype" => 1,
				"pmUser_id" => $data["pmUser_id"],
				"User_rid" => $data["pmUser_insID"],
				"Lpu_rid" => $data["Lpu_id"],
				"MedPersonal_rid" => $data["MedPersonal_id"],
				"type" => 1,
				"title" => "Пациент не включен в регистр по онкологии",
				"text" => ("Пациент {$data["Person_SurName"]} {$data["Person_FirName"]} {$data["Person_SecName"]}, {$data["Person_BirthDay"]}, не включен в регистр по онкологии, комментарий: {$queryParams["EvnOnkoNotify_Comment"]}. Создано " . date("d.m.Y H:i") . "."),
			];
			if ($data["PersonRegisterFailIncludeCause_id"] == 1) {
				$funcParams["title"] = "Пациент не включен в регистр по онкологии: Ошибка в извещении";
			} else if ($data["PersonRegisterFailIncludeCause_id"] == 2) {
				$funcParams["title"] = "Пациент не включен в регистр по онкологии: Решение оператора";
			}
			$this->Messages_model->autoMessage($funcParams);
		}
		return $response;
	}

	/**
	 * Чтение данных извещения
	 * @param $data
	 * @return array|bool
	 */
	private function loadEvnNotifyBaseData($data)
	{
		if (empty($data["PersonRegisterFailIncludeCause_id"])) {
			$data["PersonRegisterFailIncludeCause_id"] = null;
		}
		$queryParams = [
			"EvnNotifyBase_id" => $data["EvnNotifyBase_id"],
			"PersonRegisterFailIncludeCause_id" => $data["PersonRegisterFailIncludeCause_id"],
		];
		$query = "
			select
				EN.EvnNotifyBase_id as \"EvnNotifyBase_id\",
				EN.EvnNotifyBase_pid as \"EvnNotifyBase_pid\",
				EN.EvnNotifyBase_rid as \"EvnNotifyBase_rid\",
				to_char(EN.EvnNotifyBase_setDT, '{$this->dateTimeForm120full}') as \"EvnNotifyBase_setDT\",
				to_char(EN.EvnNotifyBase_disDT, '{$this->dateTimeForm120full}') as \"EvnNotifyBase_disDT\",
				to_char(EN.EvnNotifyBase_didDT, '{$this->dateTimeForm120full}') as \"EvnNotifyBase_didDT\",
				to_char(EN.EvnNotifyBase_niDate, '{$this->dateTimeForm120full}') as \"EvnNotifyBase_niDate\",
				PRF.PersonRegisterFailIncludeCause_id as \"PersonRegisterFailIncludeCause_id\",
				EN.MedPersonal_niid as \"MedPersonal_niid\",
				EN.Lpu_niid as \"Lpu_niid\",
				EN.Server_id as \"Server_id\",
				EN.PersonEvn_id as \"PersonEvn_id\",
				EN.Lpu_id as \"Lpu_id\",
				EN.MedPersonal_id as \"MedPersonal_id\",
				to_char(EN.EvnNotifyBase_signDT, '{$this->dateTimeForm120full}') as \"EvnNotifyBase_signDT\",
				EN.EvnNotifyBase_IsSigned as \"EvnNotifyBase_IsSigned\",
				EN.pmUser_signID as \"pmUser_signID\",
				EN.Morbus_id as \"Morbus_id\",
				v_MorbusType.MorbusType_id as \"MorbusType_id\",
				v_MorbusType.MorbusType_SysNick as \"MorbusType_SysNick\",
				EN.pmUser_insID as \"pmUser_insID\",
				to_char(PS.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				PRF.PersonRegisterFailIncludeCause_Name as \"PersonRegisterFailIncludeCause_Name\",
				EN.EvnNotifyBase_IsAuto as \"EvnNotifyBase_IsAuto\"
			from
				v_EvnNotifyBase EN
				left join v_PersonState PS on PS.Person_id = EN.Person_id
				left join v_MorbusType on v_MorbusType.MorbusType_id = EN.MorbusType_id
				left join v_PersonRegisterFailIncludeCause PRF on PRF.PersonRegisterFailIncludeCause_id = :PersonRegisterFailIncludeCause_id
			where EN.EvnNotifyBase_id = :EvnNotifyBase_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Запись причины невключения в регистр
	 * @param $data
	 * @return array|bool
	 */
	function notinclude($data)
	{
		$ra = $this->loadEvnNotifyBaseData($data);
		if (empty($ra) || !is_array($ra[0]) || empty($ra[0]) || empty($ra[0]["PersonRegisterFailIncludeCause_id"])) {
			return false;
		}
		$ra[0]["EvnNotifyBase_niDate"] = date("Y-m-d");
		$ra[0]["MedPersonal_niid"] = $data["MedPersonal_niid"];
		$ra[0]["Lpu_niid"] = $data["Lpu_niid"];
		$ra[0]["EvnOnkoNotify_Comment"] = (!empty($data["EvnOnkoNotify_Comment"]) ? $data["EvnOnkoNotify_Comment"] : null);
		$ra[0]["pmUser_id"] = $data["pmUser_id"];
		return $this->saveEvnNotifyBase($ra[0]);
	}

	/**
	 * Удаление записи регистра
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	function doDelete($data = [], $isAllowTransaction = true)
	{
		if (empty($data)) {
			throw new Exception("Не переданы параметры");
		}
		$this->setScenario(self::SCENARIO_DELETE);
		$this->setParams($data);
		try {
			$this->beginTransaction();
			// контроль
			$query = "
				select
					PR.PersonRegister_id as \"PersonRegister_id\",
					PR.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
					PR.PersonRegister_disDate as \"PersonRegister_disDate\",
					PR.Person_id as \"Person_id\",
					PR.Diag_id as \"Diag_id\",
					PR.EvnNotifyBase_id as \"EvnNotifyBase_id\",
					null as \"MorbusType_id\",
					null as \"MorbusType_SysNick\",
					null as \"Morbus_id\",
					PRT.PersonRegisterType_SysNick as \"PersonRegisterType_SysNick\"
				from
					v_PersonRegister PR
					left join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					left join v_EvnNotifyBase N on N.EvnNotifyBase_id = PR.EvnNotifyBase_id
				where PR.PersonRegister_id = :PersonRegister_id
			";
			$queryParams = ["PersonRegister_id" => $data["PersonRegister_id"]];
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка БД (01)");
			}
			$tmp = $result->result("array");
			if (empty($tmp)) {
				throw new Exception("Запись регистра не найдена");
			}
			if ($tmp[0]["PersonRegisterType_SysNick"] != "fmba") {
				$query = "
					select
						PR.PersonRegister_id as \"PersonRegister_id\",
						PR.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
						PR.PersonRegister_disDate as \"PersonRegister_disDate\",
						PR.Person_id as \"Person_id\",
						PR.Diag_id as \"Diag_id\",
						PR.EvnNotifyBase_id as \"EvnNotifyBase_id\",
						v_MorbusType.MorbusType_id as \"MorbusType_id\",
						v_MorbusType.MorbusType_SysNick as \"MorbusType_SysNick\",
						coalesce(PR.Morbus_id,N.Morbus_id) as \"Morbus_id\"
					from
						v_PersonRegister PR
						inner join v_MorbusType on v_MorbusType.MorbusType_id = PR.MorbusType_id
						left join v_EvnNotifyBase N on N.EvnNotifyBase_id = PR.EvnNotifyBase_id
					where PR.PersonRegister_id = :PersonRegister_id
				";
				$queryParams = ["PersonRegister_id" => $data["PersonRegister_id"]];
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception("Ошибка БД (1)");
				}
				$tmp = $result->result("array");
				if (empty($tmp)) {
					throw new Exception("Запись регистра не найдена");
				}
			}
			$tmpRow = $tmp[0];
			$this->PersonRegister_id = $tmpRow["PersonRegister_id"];
			$this->EvnNotifyBase_id = $tmpRow["EvnNotifyBase_id"];
			$this->Person_id = $tmpRow["Person_id"];
			$this->Diag_id = $tmpRow["Diag_id"];
			$this->Morbus_id = $tmpRow["Morbus_id"];
			$this->PersonRegister_disDate = $tmpRow["PersonRegister_disDate"];
			$this->PersonRegisterOutCause_id = $tmpRow["PersonRegisterOutCause_id"];
			$this->_MorbusType_SysNick = $tmpRow["MorbusType_SysNick"];
			$this->_MorbusType_id = $tmpRow["MorbusType_id"];
			$this->load->library("swMorbus");
			swMorbus::onBeforeDeletePersonRegister($this);
			// Удаление записи регистра
			$response = $this->_delete([
				"PersonRegister_id" => $this->PersonRegister_id,
				"pmUser_id" => $this->promedUserId,
			]);
			$this->commitTransaction();
			return $response;
		} catch (Exception $e) {
			$this->rollbackTransaction();
			throw new Exception("Удаление записи регистра. " . $e->getMessage());
		}
	}

	/**
	 * Исключение из регистра
	 * @param $data
	 * @return array|bool|CI_DB_result
	 * @throws Exception
	 */
	function out($data)
	{
		$queryParams = ["PersonRegister_id" => $data["PersonRegister_id"]];
		$query = "
			select 				
				EvnNotifyBase_id as \"EvnNotifyBase_id\", 
				Morbus_id as \"Morbus_id\",
				v_MorbusType.MorbusType_id as \"MorbusType_id\",
				v_MorbusType.MorbusType_SysNick as \"MorbusType_SysNick\",
				Person_id as \"Person_id\",
				Diag_id as \"Diag_id\",
				PersonRegisterType_id as \"PersonRegisterType_id\",
				PersonRegister_Code as \"PersonRegister_Code\",
				to_char(PersonRegister_setDate, '{$this->dateTimeForm120}') as \"PersonRegister_setDate\", 
				Lpu_iid as \"Lpu_iid\",
				MedPersonal_iid as \"MedPersonal_iid\"
			from
				v_PersonRegister
				left join v_MorbusType on v_MorbusType.MorbusType_id = v_PersonRegister.MorbusType_id
			where
				PersonRegister_id = :PersonRegister_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Исключение из регистра. Ошибка БД.");
		}
		$ra = $result->result('array');
		if (empty($ra) || !is_array($ra[0]) || empty($ra[0])) {
			throw new Exception("Исключение из регистра. Запись регистра не найдена.");
		}
		$raRow = $ra[0];
		$this->PersonRegister_id = $data["PersonRegister_id"];
		$this->Person_id = $raRow["Person_id"];
		$this->_MorbusType_id = $raRow["MorbusType_id"];
		$this->_MorbusType_SysNick = $raRow["MorbusType_SysNick"];
		$this->Morbus_id = $raRow["Morbus_id"];
		$this->EvnNotifyBase_id = $raRow["EvnNotifyBase_id"];
		$this->Diag_id = $raRow["Diag_id"];
		$this->PersonRegisterType_id = $raRow["PersonRegisterType_id"];
		$this->PersonRegister_Code = $raRow["PersonRegister_Code"];
		$this->PersonRegister_setDate = $raRow["PersonRegister_setDate"];
		$this->MedPersonal_iid = $raRow["MedPersonal_iid"];
		$this->Lpu_iid = $raRow["Lpu_iid"];
		$this->PersonRegister_disDate = $data["PersonRegister_disDate"];
		$this->PersonRegisterOutCause_id = $data["PersonRegisterOutCause_id"];
		$this->PersonDeathCause_id = $data['PersonDeathCause_id'] ?? null;
		$this->MedPersonal_did = $data["MedPersonal_did"];
		$this->Lpu_did = $data["Lpu_did"];
		$this->pmUser_id = $data["pmUser_id"];
		if ($this->MorbusType_SysNick == "orphan") {
			// создать извещениe на исключение из регистра
			$this->load->model("EvnNotifyOrphan_model");
			$funcParams = [
				"Lpu_id" => $data["Lpu_id"],
				"session" => $data["session"],
				"pmUser_id" => $this->pmUser_id,
				"Person_id" => $this->Person_id,
				"EvnNotifyOrphanOut_setDT" => $this->PersonRegister_disDate
			];
			$tmp = $this->EvnNotifyOrphan_model->createEvnNotifyOrphanOut($funcParams);
			if (!empty($tmp[0]["Error_Msg"])) {
				throw new Exception("Создание извещения на исключение из регистра. " . $tmp[0]["Error_Msg"]);
			}
		}
		if (!empty($data["autoExcept"])) {
			$this->autoExcept = true;
		}
		$this->setSessionParams($data["session"]);
		return $this->save();
	}

	/**
	 * Возвращение в регистр
	 * @param $data
	 * @return array|bool|CI_DB_result
	 * @throws Exception
	 */
	function back($data)
	{
		$queryParams = ["PersonRegister_id" => $data["PersonRegister_id"]];
		$query = "
			select 				
				EvnNotifyBase_id as \"EvnNotifyBase_id\", 
				Morbus_id as \"Morbus_id\",
				v_MorbusType.MorbusType_id as \"MorbusType_id\",
				v_MorbusType.MorbusType_SysNick as \"MorbusType_SysNick\",
				Person_id as \"Person_id\",
				PersonRegisterType_id as \"PersonRegisterType_id\",
				Diag_id as \"Diag_id\",
				PersonRegister_Code  as \"PersonRegister_Code\",
				to_char(PersonRegister_setDate, '{$this->dateTimeForm120}') as \"PersonRegister_setDate\", 
				Lpu_iid as \"Lpu_iid\",
				MedPersonal_iid as \"MedPersonal_iid\"
			from
				v_PersonRegister
				left join v_MorbusType on v_MorbusType.MorbusType_id = v_PersonRegister.MorbusType_id
			where PersonRegister_id = :PersonRegister_id
		";
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Возвращение в регистр. Ошибка БД.");
		}
		$ra = $result->result("array");
		if (empty($ra) || !is_array($ra[0]) || empty($ra[0])) {
			return array(array("Error_Msg" => "Возвращение в регистр. Запись регистра не найдена."));
		}
		$raRow = $ra[0];
		$this->PersonRegister_id = $data["PersonRegister_id"];
		$this->Person_id = $raRow["Person_id"];
		$this->_MorbusType_id = $raRow["MorbusType_id"];
		$this->_MorbusType_SysNick = $raRow["MorbusType_SysNick"];
		$this->Morbus_id = $raRow["Morbus_id"];
		$this->EvnNotifyBase_id = $raRow["EvnNotifyBase_id"];
		$this->Diag_id = $raRow["Diag_id"];
		$this->PersonRegisterType_id = $raRow["PersonRegisterType_id"];
		$this->PersonRegister_Code = $raRow["PersonRegister_Code"];
		$this->PersonRegister_setDate = $raRow["PersonRegister_setDate"];
		$this->MedPersonal_iid = $raRow["MedPersonal_iid"];
		$this->Lpu_iid = $raRow["Lpu_iid"];
		$this->PersonRegister_disDate = null;
		$this->PersonRegisterOutCause_id = null;
		$this->MedPersonal_did = null;
		$this->Lpu_did = null;
		$this->pmUser_id = $data["pmUser_id"];
		$this->Mode = "homecoming";
		$this->setSessionParams($data["session"]);
		if ($raRow["MorbusType_SysNick"] == "crazy") {
			$query = "
				select
				    morbuscrazydiag_id as \"MorbusCrazyDiag_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_morbuscrazydiag_ins(
				    morbuscrazy_id := (select MorbusCrazy_id from v_morbuscrazy where Morbus_id = :Morbus_id),
				    morbuscrazydiag_setdt := coalesce(cast(:Morbus_setDT as timestamp), dbo.tzgetdate()),
				    crazydiag_id := (select CrazyDiag_id from v_CrazyDiag where Diag_id=:Diag_id limit 1),
				    diag_sid := :Diag_id,
				    morbuscrazydiagdepend_id := 0,
				    morbuscrazydiag_rowversion := null,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"Diag_id" => $data["Diag_id"],
				"pmUser_id" => $data["pmUser_id"],
				"Morbus_setDT" => isset($data["PersonRegister_setDate"]) ? $data["PersonRegister_setDate"] : null,
				"Morbus_id" => $ra[0]["Morbus_id"]
			];
			$this->db->query($query, $queryParams);

		}
		return $this->save();
	}

	/**
	 * Метод получения данных в форму просмотра записи регистра
	 * @param $data
	 * @return array|bool
	 */
	function getPersonRegisterViewData($data)
	{
		$query = "
			select 
				case when PR.PersonRegister_disDate is null and M.Morbus_disDT is null then 'edit' else 'view' end as \"accessType\",
				PR.PersonRegister_id as \"PersonRegister_id\",
				to_char(PR.PersonRegister_setDate, '{$this->dateTimeForm104}') as \"PersonRegister_setDate\",
				to_char(PR.PersonRegister_disDate, '{$this->dateTimeForm104}') as \"PersonRegister_disDate\",
				PR.PersonRegister_Code as \"PersonRegister_Code\",
				PR.EvnNotifyBase_id as \"EvnNotifyBase_id\",
				LpuN.Lpu_Nick as \"LpuN_Name\",
				PR.Morbus_id as \"Morbus_id\",
				M.MorbusBase_id as \"MorbusBase_id\",
				M.Diag_id as \"Diag_id\",
				Diag.Diag_FullName as \"Diag_Name\",
				MO.MorbusOrphan_id as \"MorbusOrphan_id\",
				LpuO.Lpu_id as \"Lpu_oid\",
				LpuO.Lpu_Nick as \"LpuO_Name\",
				PR.Person_id as \"From_id\",
				PR.Person_id as \"Person_id\"
			from 
				v_PersonRegister PR
				inner join v_Morbus M on M.Morbus_id = PR.Morbus_id
				inner join v_MorbusOrphan MO on M.Morbus_id = MO.Morbus_id
				left join v_EvnNotifyOrphan ENO on M.Morbus_id = ENO.Morbus_id
				left join v_Lpu LpuO on MO.Lpu_id = LpuO.Lpu_id
				left join v_Lpu LpuN on ENO.Lpu_id = LpuN.Lpu_id
				left join v_Diag Diag on M.Diag_id = Diag.Diag_id
			where PR.PersonRegister_id = :PersonRegister_id
		";
		try {
			if (empty($data["PersonRegister_id"])) {
				throw new Exception("Не передан идентификатор записи регистра");
			}
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception("Ошибка БД");
			}
			return $result->result("array");
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Получение данных раздела "Выгрузка в федеральный регистр" формы просмотра записи регистра
	 * @param $data
	 * @return array|bool
	 */
	function getPersonRegisterExportViewData($data)
	{
		$query = "
			select 
				'view' as \"accessType\",
			    PRE.PersonRegisterExport_id as \"PersonRegisterExport_id\",
			    to_char(PRE.PersonRegisterExport_setDate, '{$this->dateTimeForm104}') as \"PersonRegisterExport_setDate\",
			    PRET.PersonRegisterExportType_Name as \"PersonRegisterExportType_Name\"
			from 
				v_PersonRegisterExport PRE
				left join v_PersonRegisterExportType PRET on PRE.PersonRegisterExportType_id = PRET.PersonRegisterExportType_id
			where PRE.PersonRegister_id = :PersonRegister_id
			order by PRE.PersonRegisterExport_setDate
		";
		try {
			if (empty($data["PersonRegister_id"])) {
				throw new Exception("Не передан идентификатор записи регистра");
			}
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception("Ошибка БД");
			}
			return $result->result("array");
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Получение данных раздела "Сведения об инвалидности" формы просмотра записи регистра
	 * @param $data
	 * @return array|bool
	 */
	function getPersonPrivilegeViewData($data)
	{
		$query = "
			select 
				'view' as \"accessType\",
				PP.PersonPrivilege_id as \"PersonPrivilege_id\",
				PT.PrivilegeType_Name as \"PrivilegeType_Name\",
				to_char(PP.PersonPrivilege_begDate, '{$this->dateTimeForm104}') as \"PersonPrivilege_begDate\",
				to_char(PP.PersonPrivilege_endDate, '{$this->dateTimeForm104}') as \"PersonPrivilege_endDate\"
			from 
				v_PersonPrivilege PP
				inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
			where PP.Person_id = :Person_id
			order by PP.PersonPrivilege_begDate
		";
		try {
			if (empty($data["Person_id"])) {
				throw new Exception("Не передан идентификатор человека");
			}
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception("Ошибка БД");
			}
			return $result->result("array");
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Получение данных раздела "Федеральная льгота" формы просмотра записи регистра
	 * @param $data
	 * @return array|bool
	 */
	function getPersonPrivilegeFedViewData($data)
	{
		$query = "
			select
				'view' as \"accessType\",
				PP.PersonPrivilege_id as \"PersonPrivilege_id\",
				to_char(PP.PersonPrivilege_begDate, '{$this->dateTimeForm104}') as \"PersonPrivilege_begDate\",
				to_char(PP.PersonPrivilege_endDate, '{$this->dateTimeForm104}') as \"PersonPrivilege_endDate\",
				PT.PrivilegeType_Name as \"PrivilegeType_Name\",
				PT.PrivilegeType_Code as \"PrivilegeType_Code\"
			from
				v_PersonPrivilege PP
				inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
			where PP.Person_id = :Person_id
			  and PT.ReceptFinance_id = 1
			order by PT.PrivilegeType_Code
		";
		try {
			if (empty($data["Person_id"])) {
				throw new Exception("Не передан идентификатор человека");
			}
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception("Ошибка БД");
			}
			return $result->result("array");
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Получение данных раздела "Лекарственные препараты" формы просмотра записи регистра
	 * @param $data
	 * @return array|bool
	 */
	function getDrugOrphanViewData($data)
	{
		try {
			if (empty($data["PersonRegister_id"])) {
				throw new Exception("Не передан идентификатор записи регистра");
			}
			$queryParams = ["PersonRegister_id" => $data["PersonRegister_id"]];
			$this->load->library("swMorbus");
			$queryParams["MorbusType_id"] = swMorbus::getMorbusTypeIdBySysNick("orphan");
			if (empty($queryParams["MorbusType_id"])) {
				throw new Exception("Попытка получить идентификатор типа заболевания провалилась", 500);
			}
			$query = "
				select
					PR.Diag_id as \"Diag_id\",
				    PR.Person_id as \"Person_id\"
				from v_PersonRegister PR
				where PR.PersonRegister_id = :PersonRegister_id 
				  and PR.MorbusType_id = :MorbusType_id
			";
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка БД");
			}
			$tmp = $result->result("array");
			$diag_list = [];
			foreach ($tmp as $row) {
				$diag_list[] = $row["Diag_id"];
				$data["Person_id"] = $row["Person_id"];
			}
			if (empty($diag_list)) {
				throw new Exception("Ошибка получения данных по записи регистра");
			}
			if (empty($data["Person_id"])) {
				throw new Exception("Не передан идентификатор человека");
			}
			$diag_listString = implode(", ", $diag_list);
			$query = "
				select
					'view' as \"accessType\",
					EvnRecept.Entity||'_'||coalesce(EvnRecept.EvnRecept_id, EvnRecept.ReceptOtov_id)::varchar as \"DrugOrphan_id\",
					Result.ReceptResult_Name as \"ReceptResult_Name\",
					rtrim(EvnRecept.EvnRecept_Ser) as \"EvnRecept_Ser\",
					rtrim(EvnRecept.EvnRecept_Num) as \"EvnRecept_Num\",
					DrugMnn.DrugMnn_Name as \"DrugMnn_Name\",
					rtrim(Drug.Drug_Name) as \"DrugTorg_Name\",
					round(EvnRecept.EvnRecept_Kolvo, 3) as \"EvnRecept_Kolvo\",
					to_char(EvnRecept.EvnRecept_setDate, '{$this->dateTimeForm104}') as \"EvnRecept_setDate\",
					to_char(EvnRecept.EvnRecept_otpDate, '{$this->dateTimeForm104}') as \"EvnRecept_otpDate\"
				from
					(
						select
							E.EvnRecept_id as EvnRecept_id,
							null as ReceptOtov_id,
							'EvnRecept' as Entity,
							E.Diag_id as Diag_id,
							E.Person_id as Person_id,
							E.EvnRecept_Ser as EvnRecept_Ser,
							E.EvnRecept_Num as EvnRecept_Num,
							E.EvnRecept_Kolvo as EvnRecept_Kolvo,
							E.EvnRecept_setDate as EvnRecept_setDate,
							E.EvnRecept_otpDT as EvnRecept_otpDate,
							coalesce(E.Drug_oid,E.Drug_id) as Drug_id,
							E.EvnRecept_obrDT as EvnRecept_obrDate,
							E.ReceptValid_id as ReceptValid_id,
							E.ReceptDelayType_id as ReceptDelayType_id,
							E.EvnRecept_deleted as EvnRecept_deleted
						from v_EvnRecept_all E
						union all
						select
							EvnRecept_id as EvnRecept_id,
							ReceptOtov_id as ReceptOtov_id,
							'ReceptOtov' as Entity,
							Diag_id as Diag_id,
							Person_id as Person_id,
							EvnRecept_Ser as EvnRecept_Ser,
							EvnRecept_Num as EvnRecept_Num,
							EvnRecept_Kolvo as EvnRecept_Kolvo,
							EvnRecept_setDate as EvnRecept_setDate,
							EvnRecept_otpDT as EvnRecept_otpDate,
							Drug_id as Drug_id,
							EvnRecept_obrDT as EvnRecept_obrDate,
							ReceptValid_id as ReceptValid_id,
							ReceptDelayType_id as ReceptDelayType_id,
							null as EvnRecept_deleted
						from v_ReceptOtovUnSub
					) EvnRecept
					left join v_Drug Drug on Drug.Drug_id = EvnRecept.Drug_id
					left join DrugMnn on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
					left join v_ReceptResult Result on (
						case 
							when coalesce(EvnRecept.EvnRecept_deleted, 1) = 2
							    then 12
							when EvnRecept.ReceptDelayType_id = 3
							    then 11
							when EvnRecept.EvnRecept_obrDate is not null and EvnRecept.EvnRecept_otpDate is null and EvnRecept.ReceptDelayType_id != 3 and 
								tzgetdate() <= (case when EvnRecept.ReceptValid_id = 1 then dateadd('day', 14, EvnRecept.EvnRecept_setDate) else dateadd('month', case when EvnRecept.ReceptValid_id = 2 then 1 else 3 end, EvnRecept.EvnRecept_setDate) end)
								then 7
							when EvnRecept.EvnRecept_obrDate is not null and EvnRecept.EvnRecept_otpDate is null and EvnRecept.ReceptDelayType_id != 3 and 
								tzgetdate() >= (case when EvnRecept.ReceptValid_id = 1 then dateadd('day', 14, EvnRecept.EvnRecept_setDate) else dateadd('month', case when EvnRecept.ReceptValid_id = 2 then 1 else 3 end, EvnRecept.EvnRecept_setDate) end)
								then 8
							when EvnRecept.EvnRecept_obrDate is null and EvnRecept.EvnRecept_otpDate is null and EvnRecept.ReceptDelayType_id != 3 and 
								tzgetdate() >= (case when EvnRecept.ReceptValid_id = 1 then dateadd('day', 14, EvnRecept.EvnRecept_setDate) else dateadd('month', case when EvnRecept.ReceptValid_id = 2 then 1 else 3 end, EvnRecept.EvnRecept_setDate) end)
								then 9
							when EvnRecept.EvnRecept_obrDate is not null and EvnRecept.EvnRecept_otpDate is null and EvnRecept.ReceptDelayType_id != 3 and 
								tzgetdate() >= (case when EvnRecept.ReceptValid_id = 1 then dateadd('day', 14, EvnRecept.EvnRecept_setDate) else dateadd('month', case when EvnRecept.ReceptValid_id = 2 then 1 else 3 end, EvnRecept.EvnRecept_setDate) end)
								then 10
							when EvnRecept.EvnRecept_otpDate is not null and EvnRecept.EvnRecept_obrDate is not null and EvnRecept.EvnRecept_otpDate > EvnRecept.EvnRecept_obrDate
								then 6
							when EvnRecept.EvnRecept_otpDate is not null and (EvnRecept.EvnRecept_obrDate is null or EvnRecept.EvnRecept_obrDate = EvnRecept.EvnRecept_otpDate)
								then 5
							when EvnRecept.EvnRecept_otpDate is not null
								then 4
							when EvnRecept.EvnRecept_otpDate is null and EvnRecept.EvnRecept_obrDate is not null
								then 3
							when EvnRecept.EvnRecept_obrDate is not null
								then 1
							when EvnRecept.EvnRecept_obrDate is null
								then 2
						end
					) = Result.ReceptResult_id
				where EvnRecept.Person_id = :Person_id
				  and EvnRecept.Diag_id in ({$diag_listString})
				order by EvnRecept.EvnRecept_setDate DESC
			";
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception("Ошибка БД");
			}
			return $result->result("array");
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * @param $morbustype_sysnick
	 * @param $diag_id
	 * @return bool
	 * @throws Exception
	 */
	function isAllowAutoInclude($morbustype_sysnick, $diag_id)
	{
		if (empty($diag_id)) {
			return false;
		}
		if (false == in_array($morbustype_sysnick, ["crazy", "narc", "hepa", "orphan", "tub", "vener", "hiv"])) {
			return false;
		}
		// Проверяем диагноз
		$query = "
			select
				Diag.Diag_pid as \"Diag_pid\",
			    Diag.Diag_Code as \"Diag_Code\"
			from v_Diag Diag
			where Diag.Diag_id = :Diag_id
			limit 1
		";
		$queryParams = ["Diag_id" => $diag_id];
		$diagData = $this->getFirstRowFromQuery($query, $queryParams);
		if (false == is_array($diagData)) {
			throw new Exception("Передан несуществующий диагноз", 500);
		}
		if ("vener" == $morbustype_sysnick) {
			/*
			Для диагнозов В35.0-В35.9 и В86 запись регистра с типом "Венерология" не создавать.
			Включение в регистр производит оператор, т.е. в БД может быть извещение без записи регистра
			*/
			if (394 == $diagData["Diag_pid"] || 441 == $diagData["Diag_pid"]) {
				return false;
			}
		} else {
			// Для остальных ещё в настройках должно быть разрешено автоматическое включение в регистр
			if (empty($this->globalOptions) || empty($this->globalOptions["globals"])) {
				throw new Exception("Не получилось прочитать настройки", 500);
			}
			$setting = "register_{$morbustype_sysnick}_auto_include";
			if ("narc" == $morbustype_sysnick) {
				$setting = "register_narko_auto_include";
			}
			if (false == array_key_exists($setting, $this->globalOptions["globals"]) || 1 != $this->globalOptions["globals"][$setting]) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Автоматическое включение в регистр внутри транзакции при создании извещения
	 * Операция вызывается из swMorbus::onAfterSaveEvnNotify
	 * На клиенте для создания извещения используется функция checkEvnNotifyBaseWithAutoIncludeInPersonRegister
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function autoInclude($data)
	{
		if (empty($data["Person_id"]) ||
			empty($data["Morbus_id"]) ||
			empty($data["MorbusType_id"]) ||
			empty($data["MorbusType_SysNick"]) ||
			empty($data["Diag_id"]) ||
			empty($data["PersonRegister_setDate"]) ||
			empty($data["EvnNotifyBase_id"]) ||
			empty($data["MedPersonal_iid"]) ||
			empty($data["Lpu_iid"])
		) {
			throw new Exception("Переданы неправильные параметры", 500);
		}
		$this->setParams($data);
		$response = [
			"PersonRegister_id" => null,
			"Morbus_id" => $data["Morbus_id"],
			"MorbusType_id" => $data["MorbusType_id"],
			"EvnNotifyBase_id" => $data["EvnNotifyBase_id"],
		];
		if (false == $this->isAllowAutoInclude($data["MorbusType_SysNick"], $data["Diag_id"])) {
			return $response;
		}
		$query = "
			select PersonRegister_id as \"PersonRegister_id\"
			from v_PersonRegister
			where Person_id = :Person_id
			  and Morbus_id = :Morbus_id
			  and PersonRegister_disDate is null
			limit 1
		";
		$queryParams = [
			"Person_id" => $data["Person_id"],
			"Morbus_id" => $data["Morbus_id"]
		];
		$response["PersonRegister_id"] = $this->getFirstResultFromQuery($query, $queryParams);
		if ($response["PersonRegister_id"] > 0) {
			return $response;
		}
		// Если в системе нет Объекта «Запись регистра», то автоматически создавать
		$funcParams = [
			"PersonRegister_id" => [
				"value" => null,
				"out" => true,
				"type" => "bigint",
			],
			"Person_id" => $data["Person_id"],
			"Morbus_id" => $data["Morbus_id"],
			"MorbusType_id" => $data["MorbusType_id"],
			"Diag_id" => $data["Diag_id"],
			"PersonRegister_Code" => null,
			"PersonRegister_setDate" => $data["PersonRegister_setDate"],
			"Lpu_iid" => $data["Lpu_iid"],
			"MedPersonal_iid" => $data["MedPersonal_iid"],
			"EvnNotifyBase_id" => $data["EvnNotifyBase_id"],
			"pmUser_id" => $data["session"]["pmuser_id"]
		];
		$tmp = $this->execCommonSP("p_PersonRegister_ins", $funcParams);
		if (empty($tmp)) {
			throw new Exception("Ошибка запроса записи данных объекта в БД", 500);
		}
		if (isset($tmp[0]["Error_Msg"])) {
			throw new Exception($tmp[0]["Error_Msg"], $tmp[0]["Error_Code"]);
		}
		$response["PersonRegister_id"] = $tmp[0]["PersonRegister_id"];
		return $response;
	}

	/**
	 * Получение списка видов регистров людей
	 * @param $data
	 * @return array|false
	 */
	function loadPersonRegisterTypeGrid($data)
	{
		$query = "
			select
				PRT.PersonRegisterType_id as \"PersonRegisterType_id\",
				PRT.PersonRegisterType_Code as \"PersonRegisterType_Code\",
				PRT.PersonRegisterType_Name as \"PersonRegisterType_Name\",
				PRT.PersonRegisterType_SysNick as \"PersonRegisterType_SysNick\"
			from v_PersonRegisterType PRT
			order by PRT.PersonRegisterType_Code
		";
		return $this->queryResult($query);
	}

	/**
	 * Получение истории изменения данных в регистре
	 */
	function loadPersonRegisterHistList($data)
	{
		$params = ["PersonRegister_id" => $data["PersonRegister_id"]];
		$query = "
			select
				PRH.PersonRegisterHist_id as \"PersonRegisterHist_id\",
				PRH.PersonRegister_id as \"PersonRegister_id\",
				to_char(PRH.PersonRegisterHist_begDate, '{$this->dateTimeForm104}') as \"PersonRegisterHist_begDate\",
				to_char(PRH.PersonRegisterHist_endDate, '{$this->dateTimeForm104}') as \"PersonRegisterHist_endDate\",
				PRH.PersonRegisterHist_NumCard as \"PersonRegisterHist_NumCard\",
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				MP.MedPersonal_id as \"MedPersonal_id\",
				MP.Person_Fio as \"MedPersonal_Fio\"
			from
				v_PersonRegisterHist PRH
				left join v_Lpu_all L on L.Lpu_id = PRH.Lpu_id
				left join lateral (
					select * 
					from v_MedPersonal 
					where MedPersonal_id = PRH.MedPersonal_id
					limit 1
				) as MP on true
			where PRH.PersonRegister_id = :PersonRegister_id
			order by
				PRH.PersonRegisterHist_begDate desc,
				coalesce(PRH.PersonRegisterHist_endDate, dateadd('year', 50, tzgetdate())) desc
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @param $data
	 * @return array|bool* Полученеи последней записи из истории изменения данных регистра
	 */
	function getLastPersonRegisterHist($data)
	{
		$params = ["PersonRegister_id" => $data["PersonRegister_id"]];
		$query = "
			select
				lastPRH.PersonRegisterHist_id as \"PersonRegisterHist_id\",
				lastPRH.PersonRegister_id as \"PersonRegister_id\",
				lastPRH.PersonRegisterHist_NumCard as \"PersonRegisterHist_NumCard\",
				lastPRH.PersonRegisterHist_begDate as \"PersonRegisterHist_begDate\",
				lastPRH.Lpu_id as \"Lpu_id\",
				lastPRH.MedPersonal_id as \"MedPersonal_id\"
			from
				v_PersonRegister PR
				inner join lateral (
					select *
					from v_PersonRegisterHist PRH
					where PRH.PersonRegister_id = PR.PersonRegister_id
					order by
					PRH.PersonRegisterHist_begDate desc,
					coalesce(PRH.PersonRegisterHist_endDate, dateadd('year', 50, tzgetdate())) desc
					limit 1
				) as lastPRH on true
			where PR.PersonRegister_id = :PersonRegister_id
			limit 1
		";
		return $this->getFirstRowFromQuery($query, $params, true);
	}

	/**
	 * Получение последених данных из истории изменения регистра для редактирования (будет создана новая запись в истории)
	 * @param $data
	 * @return array|false
	 */
	function loadPersonRegisterHistForm($data)
	{
		$params = ["PersonRegister_id" => $data["PersonRegister_id"]];
		$query = "
			select
				null as \"PersonRegisterHist_id\",
				PR.PersonRegister_id as \"PersonRegister_id\",
				coalesce(lastPRH.PersonRegisterHist_NumCard, PR.PersonRegister_Code) as \"PersonRegisterHist_NumCard\",
				to_char(coalesce(lastPRH.PersonRegisterHist_begDate, PR.PersonRegister_setDate), '{$this->dateTimeForm104}') as \"PersonRegisterHist_begDate\",
				coalesce(lastPRH.Lpu_id, PR.Lpu_iid) as \"Lpu_id\",
				coalesce(lastPRH.MedPersonal_id, PR.MedPersonal_iid) as \"MedPersonal_id\"
			from
				v_PersonRegister PR
				left join lateral (
					select *
					from v_PersonRegisterHist PRH
					where PRH.PersonRegister_id = PR.PersonRegister_id
					order by 
					PRH.PersonRegisterHist_begDate desc,
					coalesce(PRH.PersonRegisterHist_endDate, dateadd('year', 50, tzGetDate())) desc
					limit 1
				) as lastPRH on true
			where PR.PersonRegister_id = :PersonRegister_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохраненеи записи истории изменения данных регистра
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function savePersonRegisterHist($data)
	{
		$params = [
			"PersonRegisterHist_id" => !empty($data["PersonRegisterHist_id"]) ? $data["PersonRegisterHist_id"] : null,
			"PersonRegister_id" => $data["PersonRegister_id"],
			"PersonRegisterHist_NumCard" => !empty($data["PersonRegisterHist_NumCard"]) ? $data["PersonRegisterHist_NumCard"] : null,
			"PersonRegisterHist_begDate" => $data["PersonRegisterHist_begDate"],
			"PersonRegisterHist_endDate" => !empty($data["PersonRegisterHist_endDate"]) ? $data["PersonRegisterHist_endDate"] : null,
			"Lpu_id" => !empty($data["Lpu_id"]) ? $data["Lpu_id"] : null,
			"MedPersonal_id" => !empty($data["MedPersonal_id"]) ? $data["MedPersonal_id"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];
		$procedure = (empty($params["PersonRegisterHist_id"])) ? "p_PersonRegisterHist_ins" : "p_PersonRegisterHist_upd";
		$selectString = "
		    personregisterhist_id as \"PersonRegisterHist_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    personregisterhist_id := :PersonRegisterHist_id,
			    personregister_id := :PersonRegister_id,
			    medpersonal_id := :MedPersonal_id,
			    personregisterhist_begdate := cast(:PersonRegisterHist_begDate as date),
			    personregisterhist_enddate := cast(:PersonRegisterHist_endDate as date),
			    personregisterhist_numcard := :PersonRegisterHist_NumCard,
			    lpu_id := :Lpu_id,
			    pmuser_id := :pmUser_id
			);
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при сохранении истории редактирования записи регистра");
		}
		return $response;
	}

	/**
	 * Сравнение двух записией истории изменения данных регистра
	 * @param $oldHist
	 * @param $newHist
	 * @return bool
	 */
	function comparePersonRegisterHist($oldHist, $newHist)
	{
		$fields = [
			"PersonRegister_id",
			"PersonRegisterHist_begDate",
			"PersonRegisterHist_NumCard",
			"Lpu_id",
			"MedPersonal_id",
		];
		if ($oldHist["PersonRegisterHist_begDate"] instanceof DateTime) {
			$oldHist["PersonRegisterHist_begDate"] = $oldHist["PersonRegisterHist_begDate"]->format("Y-m-d");
		}
		if ($newHist["PersonRegisterHist_begDate"] instanceof DateTime) {
			$newHist["PersonRegisterHist_begDate"] = $newHist["PersonRegisterHist_begDate"]->format("Y-m-d");
		}
		foreach ($fields as $field) {
			if ((string)$oldHist[$field] !== (string)$newHist[$field]) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Создание записи в истории изменения данных регистра
	 * @param $data
	 * @return array|bool|CI_DB_result|false
	 * @throws Exception
	 */
	function createPersonRegisterHist($data)
	{
		$this->beginTransaction();
		//Если добавляемая и предыдущая записи одинаковые, то предыдущую не закрывать, новую не добавлять
		//Будет обновлена запись регистра последними данными из истории, если не передан PersonRegisterUpdated => true
		$isSame = false;
		$response = [["PersonRegisterHist_id" => null, "Error_Code" => null, "Error_Msg" => null]];
		if (empty($data["PersonRegisterHist_begDate"])) {
			$data["PersonRegisterHist_begDate"] = $this->getFirstResultFromQuery("
				select to_char(tzgetdate(), '{$this->dateTimeForm120}')
			");
			if (!$data["PersonRegisterHist_begDate"]) {
				$this->rollbackTransaction();
				throw new Exception("Ошибка при получении даты добавления записи в историю изменения регистра");
			}
		}
		//Закрытие предыдущей записи в истории
		$LastHist = $this->getLastPersonRegisterHist($data);
		if ($LastHist === false) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при получении данных предыдущей записи в истории");
		}
		if ($LastHist) {
			$isSame = $this->comparePersonRegisterHist($LastHist, $data);
			if ($isSame) {
				$response[0]["PersonRegisterHist_id"] = $LastHist["PersonRegisterHist_id"];
			} else {
				$params = array_merge($LastHist, [
					"PersonRegisterHist_endDate" => $data["PersonRegisterHist_begDate"],
					"pmUser_id" => $data["pmUser_id"],
				]);
				$resp = $this->savePersonRegisterHist($params);
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}
		}
		//Добавление новой записи в истории
		if (!$isSame) {
			$params = [
				"PersonRegisterHist_id" => null,
				"PersonRegister_id" => $data["PersonRegister_id"],
				"PersonRegisterHist_NumCard" => !empty($data["PersonRegisterHist_NumCard"]) ? $data["PersonRegisterHist_NumCard"] : null,
				"PersonRegisterHist_begDate" => $data["PersonRegisterHist_begDate"],
				"Lpu_id" => !empty($data["Lpu_id"]) ? $data["Lpu_id"] : null,
				"MedPersonal_id" => !empty($data["MedPersonal_id"]) ? $data["MedPersonal_id"] : null,
				"pmUser_id" => $data["pmUser_id"],
			];
			$response = $this->savePersonRegisterHist($params);
			if (!$this->isSuccessful($response)) {
				$this->rollbackTransaction();
				return $response;
			}
		}
		//Обновление записи в регистре данными из последней записи в истории
		if (empty($data["PersonRegisterUpdated"]) || !$data["PersonRegisterUpdated"]) {
			$params = [
				"PersonRegister_id" => $data["PersonRegister_id"],
				"session" => $data["session"],
				"histCreated" => true,
			];
			$this->isAllowTransaction = false;
			$resp = $this->updatePersonRegisterFromHist($params);
			$this->isAllowTransaction = true;
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		//Возвращается результат добавления новой записи в историю
		$this->commitTransaction();
		return $response;
	}

	/**
	 * Обновление данных регистра последними данными из истории
	 * @param $data
	 * @return array|bool|CI_DB_result
	 * @throws Exception
	 */
	function updatePersonRegisterFromHist($data)
	{
		$params = ["PersonRegister_id" => $data["PersonRegister_id"]];
		$LastHist = $this->getLastPersonRegisterHist($params);
		if (!is_array($LastHist)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при получении данных предыдущей записи в истории");
		}
		$params = [
			"PersonRegister_id" => $LastHist["PersonRegister_id"],
			"PersonRegister_Code" => $LastHist["PersonRegisterHist_NumCard"],
			"Lpu_iid" => $LastHist["Lpu_id"],
			"MedPersonal_iid" => $LastHist["MedPersonal_id"],
			"session" => $data["session"],
			"histCreated" => !empty($data["histCreated"]) ? $data["histCreated"] : false,
			"autoExcept" => true,
		];
		$resp = $this->savePersonRegister($params);
		if (isset($resp[0]) && !empty($resp[0]["Alert_Msg"])) {
			throw new Exception($resp[0]["Alert_Msg"]);
		}
		if (!isset($resp[0]) || empty($resp[0]["PersonRegister_id"])) {
			throw new Exception("Ошибка при обновлении данных записи регистра");
		}
		return $resp;
	}

	/**
	 * Метод выгружает весь регистр ВЗН без фильтров в виде файла CSV
	 * @return bool
	 */
	function downloadVznRegisterCsv()
	{
		$query = "
			select
				PR.PersonRegister_id as \"RegNum\",
				RTRIM(PS.Person_SurName)||coalesce(' '||RTRIM(PS.Person_FirName),'')||coalesce(' '||RTRIM(PS.Person_SecName),'') as \"FIO\",
				to_char(PS.Person_Birthday, '{$this->dateTimeForm104}') as \"Person_Birthday\",
				to_char(PS.Person_deadDT, '{$this->dateTimeForm104}') as \"Person_Death\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				Diag.Diag_SCode as \"Diag_SCode\",
				Diag.diag_FullName as \"Diag_Name\",
				LpuIns.Lpu_Nick as \"Lpu_insNick\",
				to_char(PR.PersonRegister_setDate, '{$this->dateTimeForm104}') as \"PersonRegister_setDate\",
				to_char(PR.PersonRegister_disDate, '{$this->dateTimeForm104}') as \"PersonRegister_disDate\",
				PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
				ENR.EvnNotifyRegister_OutComment as \"EvnNotifyRegister_OutComment\",
				LpuOut.Lpu_Nick as \"Lpu_delNick\"
			from		
				v_PersonState PS
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_SysNick ilike 'nolos'
				inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.PersonRegisterType_id = PRT.PersonRegisterType_id
				left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
				left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
				left join v_Lpu LpuIns on LpuIns.Lpu_id = pr.Lpu_iid
				left join v_Lpu LpuOut on LpuOut.Lpu_id = pr.Lpu_did
				left join v_Diag Diag on Diag.Diag_id = PR.Diag_id
				left join v_EvnNotifyRegister ENR on ENR.PersonRegister_id = PR.PersonRegister_id and ENR.EvnNotifyRegister_OutComment is not null
			order by rtrim(PS.Person_SurName)||coalesce(' '||rtrim(PS.Person_FirName), '')||coalesce(' '||rtrim(PS.Person_SecName), '')
		";
		$resultObj = $this->db->query($query);
		if (!is_object($resultObj)) {
			return false;
		}
		$result = $resultObj->result("array");
		// Заголовки для таблицы
		$csvHeaders = [
			"Номер регистровой записи",
			"ФИО",
			"Дата рождения",
			"Дата смерти",
			"МО прикрепления",
			"Код МКБ-10",
			"Диагноз МКБ -10",
			"МО, подавшая извещение на включение в регистра",
			"Дата включения в регистр",
			"Дата исключения из регистра",
			"Причина исключения из регистра",
			"Комментарий к исключению из регистра",
			"МО, подавшая извещение на исключение в регистра"
		];
		$csvSeparator = ";"; // Разделитель ; чтобы файл открывался в Excel, запятая не будет работает
		$fileName = "Регистр.csv";
		$report = "\xEF\xBB\xBF"; // записываем в начало последовательность байтов UTF-8 BOM для корректного отображения в Excel
		$report .= implode($csvSeparator, $csvHeaders) . PHP_EOL;
		// Записываем значения ячеек с разделителем для формата csv
		foreach ($result as $arrayValues) {
			$report .= implode($csvSeparator, $arrayValues) . PHP_EOL;
		}
		$this->output->set_content_type("text/csv", "utf-8")
			->set_header("Content-Disposition: attachment; filename=$fileName")
			->set_output($report); // Устанавливаем содержимое файла
		return true;
	}

	/**
	 * Простой вариант проверки наличия записи регистра
	 */
	function simpleCheckPersonRegisterExist($data) {
		
		return $this->queryResult("
			select pr.PersonRegister_id 
			from v_PersonRegister pr
			inner join v_PersonRegisterType prt on prt.PersonRegisterType_id = pr.PersonRegisterType_id
			where 
				pr.Person_id = :Person_id and 
				prt.PersonRegisterType_Code = :PersonRegisterType_Code and
				pr.PersonRegister_setDate >= :PersonRegister_setDate and
				(pr.PersonRegister_disDate <= :PersonRegister_setDate or pr.PersonRegister_disDate is null)
			limit 1
		", $data);
	}
}