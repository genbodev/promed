<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
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
 */
class PersonRegister_model extends swModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
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
		return 'PersonRegister';
	}

	/**
	 *
	 * @param type $value 
	 */

	public function setMode($value) {
		$this->Mode = $value;
	}

	/**
	 *
	 * @return type 
	 */
	public function getPersonRegister_id() {
		return $this->PersonRegister_id;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setPersonRegister_id($value) {
		$this->PersonRegister_id = $value;
	}
	/**
	 *
	 * @param type $value 
	 */
	public function setPersonRegisterType_id($value) {
		$this->PersonRegisterType_id = $value;
	}
	/**
	 *
	 * @param type $value
	 */
	public function setPersonRegisterType_SysNick($value) {
		if (empty($value)) {
			$this->PersonRegisterType_id = null;
		} else {
			$this->PersonRegisterType_id = $this->getFirstResultFromQuery("
				select top 1 PersonRegisterType_id from v_PersonRegisterType with(nolock)
				where PersonRegisterType_SysNick like :PersonRegisterType_SysNick
			", array('PersonRegisterType_SysNick' => $value));
			if (!$this->PersonRegisterType_id) {
				throw new Exception('Ошибка при получении идентификатора вида регистра', 500);
			}
		}
	}
	/**
	 *
	 * @return type 
	 */
	public function getMorbus_id() {
		return $this->Morbus_id;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setMorbus_id($value) {
		$this->Morbus_id = $value;
	}

	/**
	 *
	 * @return type 
	 */
	public function getPerson_id() {
		return $this->Person_id;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setPerson_id($value) {
		$this->Person_id = $value;
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	public function getMorbusType_id()
	{
		if (!empty($this->_MorbusType_SysNick) && empty($this->_MorbusType_id)) {
			$this->load->library('swMorbus');
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->_MorbusType_SysNick);
			if (empty($this->_MorbusType_id)) {
				throw new Exception('Попытка получить неправильный идентификатор типа заболевания', 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * @comment
	 */
	public function loadEvnNotifyRegisterInclude($data){
		$query = "
			select 
enr.Person_id,
enr.Server_id,
enr.PersonEvn_id,
enr.EvnVK_id,
enr.EvnNotifyRegister_pid,
enr.EvnNotifyRegister_Comment,
enr.Lpu_id as Lpu_did,
enr.Diag_id,
enr.MedPersonal_id
from v_EvnNotifyRegister enr with(nolock) where EvnNotifyRegister_id=:EvnNotifyRegister_id";
		$r = $this->db->query($query, array('EvnNotifyRegister_id' => $data['EvnNotifyRegister_id']));
		if (is_object($r)) {
			$r = $r->result('array');
			if (isset($r[0])) {
				
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/**
	 * @param int $value
	 * @throws Exception
	 */
	public function setMorbusType_id($value)
	{
		if (empty($value)) {
			$this->_MorbusType_SysNick = null;
			$this->_MorbusType_id = null;
		} else {
			$this->load->library('swMorbus');
			$arr = swMorbus::getMorbusTypeListAll();
			if (empty($arr[$value])) {
				throw new Exception('Попытка установить неправильный идентификатор типа заболевания', 500);
			}
			$this->_MorbusType_id = $value;
			$this->_MorbusType_SysNick = $arr[$value][0]['MorbusType_SysNick'];
		}
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function getMorbusType_SysNick()
	{
		if (empty($this->_MorbusType_SysNick) && !empty($this->_MorbusType_id)) {
			$this->load->library('swMorbus');
			$arr = swMorbus::getMorbusTypeListAll();
			if (empty($arr[$this->_MorbusType_id])) {
				throw new Exception('Попытка получить неправильный тип заболевания', 500);
			}
			$this->_MorbusType_SysNick = $arr[$this->_MorbusType_id][0]['MorbusType_SysNick'];
		}
		return $this->_MorbusType_SysNick;
	}

	/**
	 * @param string $value
	 * @throws Exception
	 */
	public function setMorbusType_SysNick($value)
	{
		if (empty($value)) {
			$this->_MorbusType_SysNick = null;
			$this->_MorbusType_id = null;
		} else {
			$this->load->library('swMorbus');
			$this->_MorbusType_id = (($value == 'fmba') ? 1 : swMorbus::getMorbusTypeIdBySysNick($value));
			if (empty($this->_MorbusType_id)) {
				throw new Exception('Попытка установить неправильный тип заболевания', 500);
			}
			$this->_MorbusType_SysNick = $value;
		}
	}

	/**
	 *
	 * @return type 
	 */
	public function getDiag_id() {
		return $this->Diag_id;
	}

	/**
	 *
	 * @return type
	 */
	public function getMorbusProfDiag_id() {
		return $this->MorbusProfDiag_id;
	}

	/**
	 *
	 * @return type
	 */
	public function getignoreCheckAnotherDiag() {
		return $this->ignoreCheckAnotherDiag;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setDiag_id($value) {
		$this->Diag_id = $value;
	}

	/**
	 *
	 * @param type $value
	 */
	public function setMorbusProfDiag_id($value) {
		$this->MorbusProfDiag_id = $value;
	}

	/**
	 *
	 * @param type $value
	 */
	public function setMorbus_confirmDate($value) {
		$this->Morbus_confirmDate = $value;
	}

	/**
	 *
	 * @param type $value
	 */
	public function setMorbus_EpidemCode($value) {
		$this->Morbus_EpidemCode = $value;
	}

	/**
	 *
	 * @param type $value
	 */
	public function setignoreCheckAnotherDiag($value) {
		$this->ignoreCheckAnotherDiag = $value;
	}

	/**
	 *
	 * @return type 
	 */
	public function getPersonRegister_Code() {
		return $this->PersonRegister_Code;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setPersonRegister_Code($value) {
		$this->PersonRegister_Code = $value;
	}

	/**
	 *
	 * @return type 
	 */
	public function getPersonRegister_setDate() {
		return $this->PersonRegister_setDate;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setPersonRegister_setDate($value) {
		$this->PersonRegister_setDate = $value;
	}

	/**
	 *
	 * @return type 
	 */
	public function getPersonRegister_disDate() {
		return $this->PersonRegister_disDate;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setPersonRegister_disDate($value) {
		$this->PersonRegister_disDate = $value;
	}

	/**
	 *
	 * @return type 
	 */
	public function getPersonRegisterOutCause_id() {
		return $this->PersonRegisterOutCause_id;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setPersonRegisterOutCause_id($value) {
		$this->PersonRegisterOutCause_id = $value;
	}

	/**
	 *
	 * @return type
	 */
	public function getPersonDeathCause_id() {
		return $this->PersonDeathCause_id;
	}

	/**
	 *
	 * @param type $value
	 */
	public function setPersonDeathCause_id($value) {
		$this->PersonDeathCause_id = $value;
	}

	/**
	 *
	 * @param type $value
	 */
	public function setPersonRegisterOutCause_SysNick($value) {
		if (empty($value)) {
			$this->PersonRegisterOutCause_id = null;
		} else {
			$this->PersonRegisterOutCause_id = $this->getFirstResultFromQuery("
				select top 1 PersonRegisterOutCause_id from v_PersonRegisterOutCause with(nolock)
				where PersonRegisterOutCause_SysNick like :PersonRegisterOutCause_SysNick
			", array('PersonRegisterOutCause_SysNick' => $value));
			if (!$this->PersonRegisterOutCause_id) {
				throw new Exception('Ошибка при получении идентификатора причины закрытия записи регистра', 500);
			}
		}
	}

	/**
	 *
	 * @param type $value 
	 */
	public function getPersonRegister_Alcoholemia($value) {
		return $this->PersonRegister_Alcoholemia;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setPersonRegister_Alcoholemia($value) {
		$this->PersonRegister_Alcoholemia = $value;
	}

	/**
	 *
	 * @return type 
	 */
	public function getMedPersonal_iid() {
		return $this->MedPersonal_iid;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setMedPersonal_iid($value) {
		$this->MedPersonal_iid = $value;
	}

	/**
	 *
	 * @return type 
	 */
	public function getLpu_iid() {
		return $this->Lpu_iid;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setLpu_iid($value) {
		$this->Lpu_iid = $value;
	}

	/**
	 *
	 * @return type 
	 */
	public function getMedPersonal_did() {
		return $this->MedPersonal_did;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setMedPersonal_did($value) {
		$this->MedPersonal_did = $value;
	}

	/**
	 *
	 * @return type 
	 */
	public function getLpu_did() {
		return $this->Lpu_did;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setLpu_did($value) {
		$this->Lpu_did = $value;
	}

	/**
	 *
	 * @return type 
	 */
	public function getEvnNotifyBase_id() {
		return $this->EvnNotifyBase_id;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setEvnNotifyBase_id($value) {
		$this->EvnNotifyBase_id = $value;
	}

	/**
	 *
	 * @param type $value
	 */
	public function setRiskType_id($value) {
		$this->RiskType_id = $value;
	}

	/**
	 *
	 * @param type $value
	 */
	public function setPregnancyResult_id($value) {
		$this->PregnancyResult_id = $value;
	}

	/**
	 *
	 * @param type $value
	 */
	public function setAutoExcept($value) {
		$this->autoExcept = $value;
	}

	/**
	 *
	 * @param type $value
	 */
	public function setHistCreated($value) {
		$this->histCreated = $value;
	}

	/**
	 *
	 * @return type 
	 */
	public function getpmUser_id() {
		return $this->pmUser_id;
	}

	/**
	 *
	 * @param type $value 
	 */
	public function setpmUser_id($value) {
		$this->pmUser_id = $value;
	}

	/**
	 * sdfg
	 */
	function __construct() {
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
			$this->setSessionParams($_SESSION);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * @return array|bool 
	 */
	function load() {
		$q = "
			select
				PersonRegister_id,
				Person_id,
				Diag_id,
				PersonRegister_Code,
				PersonRegisterType_id,
				substring(convert(varchar,PersonRegister_setDate,120),1,10) as PersonRegister_setDate,
				substring(convert(varchar,PersonRegister_disDate,120),1,10) as PersonRegister_disDate,				
				v_MorbusType.MorbusType_id,
				v_MorbusType.MorbusType_SysNick,
				Morbus_id,
				PersonRegisterOutCause_id,
				MedPersonal_iid,
				Lpu_iid,
				MedPersonal_did,
				Lpu_did,
				EvnNotifyBase_id,
				PersonRegister_Alcoholemia,
				RiskType_id,
				PregnancyResult_id
			from
				dbo.v_PersonRegister with (nolock)
				LEFT JOIN dbo.v_MorbusType with (nolock) on v_MorbusType.MorbusType_id = v_PersonRegister.MorbusType_id
			where
				PersonRegister_id = :PersonRegister_id
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
	 */
	function loadAPI($data) {
		$params = array();
		$where = '';
		if(!empty($data['PersonRegister_id'])){
			$params['PersonRegister_id'] = $data['PersonRegister_id'];
			$where .= ' AND PR.PersonRegister_id = :PersonRegister_id ';
		}
		if(!empty($data['Person_id'])){
			$params['Person_id'] = $data['Person_id'];
			$where .= ' AND PR.Person_id = :Person_id ';
		}
		if(count($params) == 0){
			return array('Error_Msg' => 'не передан ни один из параметров');
		}
		$q = "
			select
				PR.PersonRegister_id,
				PR.Person_id,
				PR.Diag_id,
				PR.PersonRegister_Code,
				PR.PersonRegisterType_id,
				substring(convert(varchar,PR.PersonRegister_setDate,120),1,10) as PersonRegister_setDate,
				substring(convert(varchar,PR.PersonRegister_disDate,120),1,10) as PersonRegister_disDate,				
				PR.MorbusType_id,
				MT.MorbusType_SysNick,
				PR.Morbus_id,
				PR.PersonRegisterOutCause_id,
				PR.MedPersonal_iid,
				PR.Lpu_iid,
				PR.MedPersonal_did,
				PR.Lpu_did,
				PR.EvnNotifyBase_id,
				PR.PersonRegister_Alcoholemia,
				PR.PersonRegister_Code,
				PR.RiskType_id,
				PR.PregnancyResult_id
				,substring(convert(varchar,MH.MorbusHIV_confirmDate,120),1,10) as Morbus_confirmDate
				,MH.MorbusHIV_EpidemCode as Morbus_EpidemCode
				,PR.PersonDeathCause_id
				,PR.RiskType_id
			from
				dbo.v_PersonRegister PR with (nolock)
				LEFT JOIN dbo.v_MorbusType MT with (nolock) on MT.MorbusType_id = PR.MorbusType_id
				left join v_MorbusHIV MH with(nolock) on MH.Morbus_id = PR.Morbus_id
			where 1=1
				{$where}
		";
		$r = $this->db->query($q, $params);
		if (is_object($r)) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}

	/**
	 * Контроль на существование записи регистра при добавлении записи
	 */
	private function _checkPersonRegisterExist()
	{
		$queryParams = array(
			'Person_id' => $this->Person_id,
		);
		$add_select = '';
		$add_join = '';
		$add_where = 'AND PR.MorbusType_id = :MorbusType_id
		';
		switch (true) {
			case ('narc' == $this->MorbusType_SysNick):
				$error_msg = 'Пациент уже включен в регистр по наркологии.';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				break;
			case ('crazy' == $this->MorbusType_SysNick):
				$error_msg = 'Пациент уже включен в регистр по психиатрии.';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				break;
			case ('hepa' == $this->MorbusType_SysNick):
				$error_msg = 'На выбранного пациента уже существует запись регистра с типом «вирусный гепатит»';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				break;
			case ('orphan' == $this->MorbusType_SysNick):
				$error_msg = 'Пациент уже включен в регистр по орфанным заболеваниям. При необходимости измените диагноз в существующей записи регистра';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				//У пациента может быть только одно заболевание (запись регистра) с типом «орфанное».
				/*
				  and PR.Diag_id in (select case when DD.Diag_id is not null then DD.Diag_id else D.Diag_id end as Diag_id from v_Diag D with (nolock) left join v_Diag DD with (nolock) on (D.Diag_Code like \'E70.0\' or D.Diag_Code like \'E70.1\') and (DD.Diag_Code like \'E70.0\' or DD.Diag_Code like \'E70.1\') where D.Diag_id = :Diag_id)
				 */
				break;
			case ('tub' == $this->MorbusType_SysNick):
				$error_msg = 'Пациент уже включен в регистр по туберкулезу.';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				break;
			case ('nephro' == $this->MorbusType_SysNick):
				$error_msg = 'На выбранного пациента уже существует запись регистра с типом «нефрология»';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				break;
			case ('prof' == $this->MorbusType_SysNick):
				$error_msg = 'Выбранный пациент уже включен в регистр по профзаболеваниям с указанным диагнозом';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				if (empty($this->ignoreCheckAnotherDiag)) {
					$add_select = "
						,PR.Diag_id
						,mpd.MorbusProfDiag_Name
						,ISNULL(d.Diag_Code + '. ','') + d.Diag_Name as Diag_Name
					";
					$add_join = "
						left join v_Diag d with (nolock) on d.Diag_id = PR.Diag_id
						left join v_MorbusProf MO with (nolock) on MO.Morbus_id = PR.Morbus_id
						left join v_MorbusProfDiag mpd (nolock) on mpd.MorbusProfDiag_id = MO.MorbusProfDiag_id
					";
				} else {
					$queryParams['Diag_id'] = $this->Diag_id;
					$add_where .= 'AND PR.Diag_id = :Diag_id
					';
				}
				break;
			case ('ibs' == $this->MorbusType_SysNick):
				$error_msg = 'На выбранного пациента уже существует запись регистра с типом «ИБС»';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				break;
			case ('palliat' == $this->MorbusType_SysNick):
				$error_msg = 'На выбранного пациента уже существует запись регистра с выбранным диагнозом';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				$queryParams['Diag_id'] = $this->Diag_id;
				$add_where .= 'AND PR.Diag_id = :Diag_id';
				break;
			case ('geriatrics' == $this->MorbusType_SysNick):
				$error_msg = 'На выбранного пациента уже существует запись регистра по гериатрии';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				break;
			case ('hiv' == $this->MorbusType_SysNick):
				$error_msg = 'На выбранного пациента уже существует запись регистра с типом «ВИЧ»';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				break;
			case ('diabetes' == $this->MorbusType_SysNick):
				$error_msg = 'На выбранного пациента уже существует запись регистра с типом «Сахарный диабет»';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				break;
			case ('large family' == $this->MorbusType_SysNick):
				$error_msg = 'На выбранного пациента уже существует запись регистра с типом «Ребенок из многодетной семьи»';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				break;
			case ('nolos' == $this->MorbusType_SysNick):
				$error_msg = 'На выбранного пациента уже существует запись регистра с выбранной группой диагнозов.';
				$queryParams['Diag_id'] = $this->Diag_id;
				$add_where .= 'AND PR.Diag_id in (select DD.Diag_id from v_Diag D with(nolock) left join v_Diag DD with(nolock) on D.Diag_pid = DD.Diag_pid where D.Diag_id = :Diag_id)
				';
				break;
			case (3 == $this->PersonRegisterType_id):
				$error_msg = 'На выбранного пациента уже существует запись регистра с типом «онкология»';
				$queryParams['PersonRegisterType_id'] = $this->PersonRegisterType_id;
				$queryParams['Diag_id'] = $this->Diag_id;
				$add_where = 'AND PR.PersonRegisterType_id = :PersonRegisterType_id AND PR.Diag_id = :Diag_id
				';
				break;
			case (49 == $this->PersonRegisterType_id):
				$error_msg = 'На выбранного пациента уже существует запись регистра с данным заболеванием по ВЗН';
				$queryParams['PersonRegisterType_id'] = $this->PersonRegisterType_id;
				$queryParams['Diag_id'] = $this->Diag_id;
				$add_where = 'AND PR.PersonRegisterType_id = :PersonRegisterType_id AND PR.Diag_id in (select DD.Diag_id from v_Diag D with(nolock) left join v_Diag DD with(nolock) on D.Diag_pid = DD.Diag_pid where D.Diag_id = :Diag_id)';
				break;
			case (62 == $this->PersonRegisterType_id):
				$error_msg = 'Пациент уже включен в регистр лиц, совершивших суицидальные попытки, с указанной датой совершения';
				$queryParams['PersonRegisterType_id'] = $this->PersonRegisterType_id;
				$queryParams['PersonRegister_setDate'] = $this->PersonRegister_setDate;
				$add_where = 'AND PR.PersonRegisterType_id = :PersonRegisterType_id AND PersonRegister_setDate = :PersonRegister_setDate';
				break;
			case (67 == $this->PersonRegisterType_id):
				$error_msg = 'На выбранного пациента уже существует запись регистра по гериатрии';
				$queryParams['PersonRegisterType_id'] = $this->PersonRegisterType_id;
				$add_where = 'AND PR.PersonRegisterType_id = :PersonRegisterType_id';
				break;
			case ('gibt' == $this->MorbusType_SysNick):
				$error_msg = 'На выбранного пациента уже существует запись регистра о нуждаемости в лечении с применением ГИБТ по данному диагнозу';
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				$queryParams['Diag_id'] = $this->Diag_id;
				$add_where .= 'AND PR.Diag_id = :Diag_id';
				break;
			default:
				$error_msg = 'На выбранного пациента уже существует запись регистра с выбранной группой диагнозов.';
				$queryParams['Diag_id'] = $this->Diag_id;
				$add_where = 'AND PR.Diag_id in (select DD.Diag_id from v_Diag D with(nolock) left join v_Diag DD with(nolock) on D.Diag_pid = DD.Diag_pid where D.Diag_id = :Diag_id)
				';
				break;
		}
		$q = "
			-- @file " . __FILE__ . "
			-- @line " . __LINE__ . "

			SELECT
				CONVERT(varchar,PR.PersonRegister_disDate,104) as PersonRegister_disDate
				,PR.PersonRegisterOutCause_id
				,PR.PersonRegister_id
				{$add_select}
			FROM
				v_PersonRegister PR WITH (NOLOCK)
				{$add_join}
			WHERE
				PR.Person_id = :Person_id
				{$add_where}
			ORDER BY
				PR.PersonRegister_disDate ASC, PR.PersonRegister_insDT DESC
		";
		//echo getDebugSQL($q, $queryParams);exit();
		$result = $this->db->query($q, $queryParams);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка БД!'));
		}
		$response = $result->result('array');
		//echo $response[0]['PersonRegister_disDate']." ".$response[0]['PersonRegisterOutCause_id'];
		$result = true;
		if (count($response) > 0) {
			foreach ($response as $line) {
				switch (true) {
					case ('prof' == $this->MorbusType_SysNick && empty($this->ignoreCheckAnotherDiag) && $line['Diag_id'] != $this->Diag_id):
						$result = array(array(
							'Error_Msg' => '', 'Alert_Code' => 'ProfDiag',
							'Alert_Msg' => "Выбранный пациент уже состоит в регистре по профзаболеваниям с диагнозом {$line['Diag_Name']} по заболеванию {$line['MorbusProfDiag_Name']}."
						));
						break;
					case ('narc' == $this->MorbusType_SysNick && isset($line['PersonRegister_disDate']) && isset($line['PersonRegisterOutCause_id'])):
						break;
					case ('crazy' == $this->MorbusType_SysNick && isset($line['PersonRegister_disDate']) && isset($line['PersonRegisterOutCause_id'])):
						//echo"dsfsd";//Если тип заболевания Crazy(narc) и был исключен из регистра.
						$line['Alert_Msg'] = 'Пациент был исключен из регистра. <br />Вернуть пациента в регистр?'; //(Новое/Предыдущее/Отмена)
						$line['Yes_Mode'] = 'homecoming';
						$line['Error_Msg'] = null;
						$result = [$line];
						//При нажатии "Новое" создавать новое заболевание. При нажатии "Предыдущее" удалить дату закрытия заболевания (все ранее введенная специфика становится доступна для ввода/редактирования)
						break;
					case (empty($line['PersonRegister_disDate']) && empty($line['PersonRegisterOutCause_id'])):
						//Если уже есть запись регистра с открытым заболеванием, то выводить сообщение: "На выбранного пациента уже существует запись регистра ...", новую запись регистра не создавать.
						$result = array(array('Error_Msg' => $error_msg));
						break;
					case (isset($line['PersonRegister_disDate']) && $line['PersonRegisterOutCause_id'] == 1):
						//Если уже есть запись регистра с закрытым заболеванием и причина исключения из регистра "смерть"
						$result = array(array('Error_Msg' => 'Пациент был исключен из регистра по причине "смерть", <br />включение в регистр невозможно'));
						break;
					case (isset($line['PersonRegister_disDate']) && $line['PersonRegisterOutCause_id'] == 2):
						//Если уже есть запись регистра с закрытым заболеванием и причина исключения из регистра "выехал"
						$line['Alert_Msg'] = 'Пациент был исключен из регистра по причине "выехал". <br />Вернуть пациента в регистр?'; // Да/Нет
						$line['Yes_Mode'] = 'homecoming';
						$line['Error_Msg'] = null;
						$result = [$line];
						//При нажатии "Да" удалить дату закрытия заболевания (все ранее введенная специфика становится доступна для ввода/редактирования). При нажатии "Нет", форму закрывать, новую запись регистра не создавать.
						break;
					case (isset($line['PersonRegister_disDate']) && $line['PersonRegisterOutCause_id'] == 3):
						//Если уже есть запись регистра с закрытым заболеванием и причина исключения из регистра "Выздоровление"
						$line['Alert_Msg'] = 'Пациент был исключен из регистра по причине "Выздоровление". <br />У пациента новое заболевание?'; //(Новое/Предыдущее/Отмена)
						$line['Yes_Mode'] = 'new';
						$line['No_Mode'] = 'relapse';
						$line['Error_Msg'] = null;
						$result = [$line];
						//При нажатии "Новое" создавать новое заболевание. При нажатии "Предыдущее" удалить дату закрытия заболевания (все ранее введенная специфика становится доступна для ввода/редактирования)
						break;
					
				}
				if (!empty($result[0]['Error_Msg']) || !empty($result[0]['Alert_Msg'])) {
					break;
				}
			}
		}
		return $result;
	}

	/**
	 *
	 * @param type $filter
	 * @return type 
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['PersonRegister_id']) && $filter['PersonRegister_id']) {
			$where[] = 'v_PersonRegister.PersonRegister_id = :PersonRegister_id';
			$p['PersonRegister_id'] = $filter['PersonRegister_id'];
		}
		if (isset($filter['PersonRegisterType_id']) && $filter['PersonRegisterType_id']) {
			$where[] = 'v_PersonRegister.PersonRegisterType_id = :PersonRegisterType_id';
			$p['PersonRegisterType_id'] = $filter['PersonRegisterType_id'];
		}
		if(isset($filter['PersonRegister_Date']) && $filter['PersonRegister_Date']) {
			//добавил для v_PersonRegister.PersonRegister_setDate и :PersonRegister_Date преобразование в тип date, т.к. при типе datetime на форме выписки рецепта неправильно отображалась информация о нахождении
			//пациентов в регистре ВЗН в случае, когда пациент был включен в регистр в день выписки рецепта. https://redmine.swan-it.ru/issues/164708 05.08.2019 Grigorev
			$where[] = '
				( (cast(v_PersonRegister.PersonRegister_setDate as Date) <= cast(:PersonRegister_Date as Date)) and (v_PersonRegister.PersonRegister_disDate is null or v_PersonRegister.PersonRegister_disDate >= :PersonRegister_Date) )
			';
			$p['PersonRegister_Date'] = $filter['PersonRegister_Date'];
		}
		if (isset($filter['Person_id']) && $filter['Person_id']) {
			$where[] = 'v_PersonRegister.Person_id = :Person_id';
			$p['Person_id'] = $filter['Person_id'];
		}
		if (isset($filter['MorbusType_id']) && $filter['MorbusType_id']) {
			$where[] = 'v_PersonRegister.MorbusType_id = :MorbusType_id';
			$p['MorbusType_id'] = $filter['MorbusType_id'];
		}
		if (isset($filter['Diag_id']) && $filter['Diag_id']) {
			$where[] = 'v_PersonRegister.Diag_id = :Diag_id';
			$p['Diag_id'] = $filter['Diag_id'];
		}
		if (isset($filter['PersonRegister_Code']) && $filter['PersonRegister_Code']) {
			$where[] = 'v_PersonRegister.PersonRegister_Code = :PersonRegister_Code';
			$p['PersonRegister_Code'] = $filter['PersonRegister_Code'];
		}
		if (isset($filter['PersonRegister_setDate']) && $filter['PersonRegister_setDate']) {
			$where[] = 'v_PersonRegister.PersonRegister_setDate = :PersonRegister_setDate';
			$p['PersonRegister_setDate'] = $filter['PersonRegister_setDate'];
		}
		if (isset($filter['PersonRegister_disDate']) && $filter['PersonRegister_disDate']) {
			$where[] = 'v_PersonRegister.PersonRegister_disDate = :PersonRegister_disDate';
			$p['PersonRegister_disDate'] = $filter['PersonRegister_disDate'];
		}
		if (isset($filter['Morbus_id']) && $filter['Morbus_id']) {
			$where[] = 'v_PersonRegister.Morbus_id = :Morbus_id';
			$p['Morbus_id'] = $filter['Morbus_id'];
		}
		if (isset($filter['PersonRegisterOutCause_id']) && $filter['PersonRegisterOutCause_id']) {
			$where[] = 'v_PersonRegister.PersonRegisterOutCause_id = :PersonRegisterOutCause_id';
			$p['PersonRegisterOutCause_id'] = $filter['PersonRegisterOutCause_id'];
		}
		if (isset($filter['MedPersonal_iid']) && $filter['MedPersonal_iid']) {
			$where[] = 'v_PersonRegister.MedPersonal_iid = :MedPersonal_iid';
			$p['MedPersonal_iid'] = $filter['MedPersonal_iid'];
		}
		if (isset($filter['Lpu_iid']) && $filter['Lpu_iid']) {
			$where[] = 'v_PersonRegister.Lpu_iid = :Lpu_iid';
			$p['Lpu_iid'] = $filter['Lpu_iid'];
		}
		if (isset($filter['MedPersonal_did']) && $filter['MedPersonal_did']) {
			$where[] = 'v_PersonRegister.MedPersonal_did = :MedPersonal_did';
			$p['MedPersonal_did'] = $filter['MedPersonal_did'];
		}
		if (isset($filter['Lpu_did']) && $filter['Lpu_did']) {
			$where[] = 'v_PersonRegister.Lpu_did = :Lpu_did';
			$p['Lpu_did'] = $filter['Lpu_did'];
		}
		if (isset($filter['EvnNotifyBase_id']) && $filter['EvnNotifyBase_id']) {
			$where[] = 'v_PersonRegister.EvnNotifyBase_id = :EvnNotifyBase_id';
			$p['EvnNotifyBase_id'] = $filter['EvnNotifyBase_id'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE ' . $where_clause;
		}
		$q = "
			SELECT
				v_PersonRegister.PersonRegister_id, v_PersonRegister.Person_id, v_PersonRegister.MorbusType_id, v_PersonRegister.Diag_id, v_PersonRegister.PersonRegister_Code, v_PersonRegister.PersonRegister_setDate, v_PersonRegister.PersonRegister_disDate, v_PersonRegister.Morbus_id, v_PersonRegister.PersonRegisterOutCause_id, v_PersonRegister.MedPersonal_iid, v_PersonRegister.Lpu_iid, v_PersonRegister.MedPersonal_did, v_PersonRegister.Lpu_did, v_PersonRegister.EvnNotifyBase_id
				,MorbusType_id_ref.MorbusType_Name MorbusType_id_Name, Diag_id_ref.Diag_Name Diag_id_Name, PersonRegisterOutCause_id_ref.PersonRegisterOutCause_Name PersonRegisterOutCause_id_Name, Lpu_iid_ref.Lpu_Name Lpu_iid_Name, Lpu_did_ref.Lpu_Name Lpu_did_Name
				, Diag_id_ref.Diag_pid
			FROM
				dbo.v_PersonRegister WITH (NOLOCK)
				LEFT JOIN dbo.v_Person Person_id_ref WITH (NOLOCK) ON Person_id_ref.Person_id = v_PersonRegister.Person_id
				LEFT JOIN dbo.v_MorbusType MorbusType_id_ref WITH (NOLOCK) ON MorbusType_id_ref.MorbusType_id = v_PersonRegister.MorbusType_id
				LEFT JOIN dbo.v_Diag Diag_id_ref WITH (NOLOCK) ON Diag_id_ref.Diag_id = v_PersonRegister.Diag_id
				LEFT JOIN dbo.v_PersonRegisterOutCause PersonRegisterOutCause_id_ref WITH (NOLOCK) ON PersonRegisterOutCause_id_ref.PersonRegisterOutCause_id = v_PersonRegister.PersonRegisterOutCause_id
				LEFT JOIN persis.v_MedWorker MedPersonal_iid_ref WITH (NOLOCK) ON MedPersonal_iid_ref.MedWorker_id = v_PersonRegister.MedPersonal_iid
				LEFT JOIN dbo.v_Lpu Lpu_iid_ref WITH (NOLOCK) ON Lpu_iid_ref.Lpu_id = v_PersonRegister.Lpu_iid
				LEFT JOIN persis.v_MedWorker MedPersonal_did_ref WITH (NOLOCK) ON MedPersonal_did_ref.MedWorker_id = v_PersonRegister.MedPersonal_did
				LEFT JOIN dbo.v_Lpu Lpu_did_ref WITH (NOLOCK) ON Lpu_did_ref.Lpu_id = v_PersonRegister.Lpu_did
				LEFT JOIN dbo.v_EvnNotifyBase EvnNotifyBase_id_ref WITH (NOLOCK) ON EvnNotifyBase_id_ref.EvnNotifyBase_id = v_PersonRegister.EvnNotifyBase_id
			$where_clause
		";
		//echo getDebugSQL($q, $filter);die;
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверки перед сохранением
	 */
	function beforeSave() {
		$this->CrazyType = null;
		// Проверка типа регистра
		if (isset($this->Diag_id) && !($this->getRegionNick() == 'saratov' && ($this->Diag_id >= 11204) && ($this->Diag_id <= 11213)) && !in_array($this->MorbusType_SysNick, array('suicide', 'palliat', 'geriatrics', 'gibt'))) {
			$this->load->library('swMorbus');
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
						$MorbusType_SysNick = $row['MorbusType_SysNick'];
						$RegistryType = $row['RegistryType'];
						break;
					} else {
						// Подбираем подходящий
						if ($id == $this->MorbusType_id) {
							$MorbusType_id = $id;
							$MorbusType_SysNick = $row['MorbusType_SysNick'];
							$RegistryType = $row['RegistryType'];
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
			if($this->getRegionNick() == 'saratov' && ($this->Diag_id >= 11204) && ($this->Diag_id <= 11213))
				$this->PersonRegisterType_id = 60;
			if (!in_array($this->_MorbusType_id, array(84, 90, 91, 94, 100, 103))) {
					$this->_MorbusType_id = null;
					$this->_MorbusType_SysNick = null;
				}
		}
		if ($this->MorbusType_SysNick == 'crazy') {
			$this->setPersonRegisterType_SysNick('crazy');
		} else if ($this->MorbusType_SysNick == 'narc') {
			$this->setPersonRegisterType_SysNick('narc');
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
		if (empty($this->PersonRegister_id) && 'orphan' == $this->MorbusType_SysNick) {
			$this->load->model('EvnNotifyOrphan_model');
			$result = $this->EvnNotifyOrphan_model->medpersonalBaseAttach(array(
				'MedPersonal_id' => $this->MedPersonal_iid
				, 'Person_id' => $this->Person_id
			));
			if (empty($result)) {
				if (!isSuperadmin()) {
					throw new Exception("Пациент не имеет основного типа прикрепления или вы не являетесь врачом по основному прикреплению пациента");
				}
			} else {
				$this->MedPersonal_iid = $result[0]['MedPersonal_id'];
				$this->Lpu_iid = $result[0]['Lpu_id'];
			}
		}

		//контроль даты включения в регистр (не больше текущей)
		if (trim($this->PersonRegister_setDate) > date('Y-m-d')) {
			throw new Exception("Дата включения в регистр не может быть больше текущей даты");
		}

		if (in_array($this->MorbusType_SysNick, array('ipra'))) {
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
		if (empty($this->PersonRegister_id) && empty($this->EvnNotifyBase_id) && empty($this->Morbus_id) && isset($this->Person_id)
			&& !empty($this->MorbusType_SysNick)
		) {
			$personData = $this->getFirstRowFromQuery('
				select top 1
				dbo.Age2(ps.Person_BirthDay,:setDate) as Person_Age,
				PS.Server_id,
				PS.PersonEvn_id
				from v_PersonState PS with (nolock)
				where PS.Person_id = :Person_id
			', array('Person_id'=>$this->Person_id, 'setDate'=>$this->PersonRegister_setDate ));
			if (empty($personData)) {
				throw new Exception("Человек не найден");
			}
			if ('acs' == $this->MorbusType_SysNick && $personData['Person_Age'] < 18 && $this->getRegionNick() != 'buryatiya') {
				throw new Exception("Возраст пациента составляет менее 18 лет на момент поступления в стационар.");
			}
			if (swMorbus::isRequiredPersonRegisterMorbus_id($this->MorbusType_SysNick)) {
				//создание заболевания с проверкой на существование заболевания у человека
				try {
					$result = swMorbus::checkByPersonRegister($this->MorbusType_SysNick, array(
						'isDouble' => (isset($this->Mode) && $this->Mode == 'new'),
						'Diag_id' => $this->Diag_id,
						'Person_id' => $this->Person_id,
						'Morbus_setDT' => $this->PersonRegister_setDate,
						'Morbus_confirmDate' => $this->Morbus_confirmDate,
						'Morbus_EpidemCode' => $this->Morbus_EpidemCode,
						'session' => $this->sessionParams,
					), 'onBeforeSavePersonRegister');
				} catch (Exception $e) {
					throw new Exception($e->getMessage());
				}
				if (empty($result['Morbus_id'])) {
					throw new Exception("Проверка существования и создание заболевания. По какой-то причине заболевание не найдено и не создано");
				}
				$this->Morbus_id = $result['Morbus_id']; 
				
				if(!empty($result['MorbusHIV_id'])){
					$this->MorbusHIV_id = $result['MorbusHIV_id'];
				}elseif (!empty($result['MorbusOnko_id'])) {
					$this->MorbusOnko_id = $result['MorbusOnko_id'];
				}elseif (!empty($result['MorbusTub_id'])) {
					$this->MorbusTub_id = $result['MorbusTub_id'];
				}
				/*
				 * При добавлении записи регистра из формы «Регистр по орфанным заболеваниям»
				 * автоматически создавать «Направление на включение в регистр»
				 */
				if ('orphan' == $this->MorbusType_SysNick) {
					$this->load->model('EvnNotifyOrphan_model');
					$result = $this->EvnNotifyOrphan_model->doSave(array(
						'session' => $this->sessionParams,
						'scenario' => 'onAddPersonRegister',
						'Morbus_id' => $this->Morbus_id,
						'MorbusType_id' => $this->MorbusType_id,
						'MedPersonal_id' => $this->MedPersonal_iid,
						'Lpu_id' => $this->Lpu_iid,
						'EvnNotifyOrphan_setDT' => $this->PersonRegister_setDate,
						'Person_id' => $this->Person_id,
						'Server_id' => $personData['Server_id'],
						'PersonEvn_id' => $personData['PersonEvn_id'],
					), false);
					if (isset($result['Error_Msg'])) {
						throw new Exception($result["Error_Msg"]);
					}
					if (empty($result['EvnNotifyOrphan_id'])) {
						throw new Exception("Ошибка создания направления на включение в регистр");
					}
					$this->EvnNotifyBase_id = $result['EvnNotifyOrphan_id'];
				}
			}
		}
		return true;
	}

	/**
	 * Создание/обновление записи регистра
	 * @return array 
	 */
	function save() {
		$procedure = 'p_PersonRegister_upd';
		if (empty($this->PersonRegister_id)) {
			$procedure = 'p_PersonRegister_ins';
		}
		// Стартуем транзакцию
		if ( !$this->beginTransaction() ) {
			throw new Exception('Ошибка при попытке запустить транзакцию');
		}
		$result = $this->beforeSave();
		if (is_array($result) && (isset($result[0]['Error_Msg']) || isset($result[0]['Alert_Msg']))) {
			$this->rollbackTransaction();
			return $result;
		}
		$q = "
			declare
				@PersonRegister_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @PersonRegister_id = :PersonRegister_id;
			exec dbo." . $procedure . "
				@PersonRegister_id = @PersonRegister_id output,
				@Person_id = :Person_id,
				@MorbusType_id = :MorbusType_id,
				@Morbus_id = :Morbus_id,
				@Diag_id = :Diag_id,
				@PersonRegister_Code = :PersonRegister_Code,
				@PersonRegister_setDate = :PersonRegister_setDate,
				@PersonRegister_disDate = :PersonRegister_disDate,
				@PersonRegisterType_id = :PersonRegisterType_id,
				@PersonRegisterOutCause_id = :PersonRegisterOutCause_id,
				@PersonDeathCause_id = :PersonDeathCause_id,
				@PersonRegister_Alcoholemia = :PersonRegister_Alcoholemia,
				@MedPersonal_iid = :MedPersonal_iid,
				@Lpu_iid = :Lpu_iid,
				@MedPersonal_did = :MedPersonal_did,
				@Lpu_did = :Lpu_did,
				@EvnNotifyBase_id = :EvnNotifyBase_id,
				@RiskType_id = :RiskType_id,
				@PregnancyResult_id = :PregnancyResult_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PersonRegister_id as PersonRegister_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'PersonRegister_id' => $this->PersonRegister_id,
			'Person_id' => $this->Person_id,
			'PersonRegisterType_id'=>$this->PersonRegisterType_id,
			'MorbusType_id' => $this->MorbusType_id,
			'Diag_id' => $this->Diag_id,
			'PersonRegister_Code' => $this->PersonRegister_Code,
			'PersonRegister_setDate' => $this->PersonRegister_setDate,
			'PersonRegister_disDate' => $this->PersonRegister_disDate,
			'Morbus_id' => $this->Morbus_id,
			'PersonRegisterOutCause_id' => $this->PersonRegisterOutCause_id,
			'PersonDeathCause_id' => $this->PersonDeathCause_id,
			'PersonRegister_Alcoholemia' => $this->PersonRegister_Alcoholemia,
			'MedPersonal_iid' => $this->MedPersonal_iid,
			'Lpu_iid' => $this->Lpu_iid,
			'MedPersonal_did' => $this->MedPersonal_did,
			'Lpu_did' => $this->Lpu_did,
			'EvnNotifyBase_id' => $this->EvnNotifyBase_id,
			'RiskType_id' => $this->RiskType_id,
			'PregnancyResult_id' => $this->PregnancyResult_id,
			'pmUser_id' => $this->pmUser_id,
		);
		//echo getDebugSQL($q, $p);exit;
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$result = $r->result('array');
			if (!$this->histCreated) {
				//Сохранение истории изменения данных регистра
				$resp = $this->createPersonRegisterHist(array(
					'PersonRegister_id' => $result[0]['PersonRegister_id'],
					'PersonRegisterHist_NumCard' => $this->PersonRegister_Code,
					'PersonRegisterHist_begDate' => empty($this->PersonRegister_id)?$this->PersonRegister_setDate:null,
					'Lpu_id' => $this->Lpu_iid,
					'MedPersonal_id' => $this->MedPersonal_iid,
					'pmUser_id' => $this->pmUser_id,
					'PersonRegisterUpdated' => true
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg'], 500);
				}
			}
			//Если это исключение записи из регистра, то закрывать соответствующее заболевание
			if (isset($this->Morbus_id) && isset($this->PersonRegister_id) && isset($this->PersonRegisterOutCause_id) && isset($this->PersonRegister_disDate)) {
				$this->load->model('Morbus_model');
				$tmp = $this->Morbus_model->closeMorbus(array(
					'Morbus_id' => $this->Morbus_id
					, 'Morbus_disDT' => $this->PersonRegister_disDate
					, 'pmUser_id' => $this->pmUser_id
				));
				if (false == empty($tmp[0]['Error_Msg'])) {
					$this->rollbackTransaction();
					throw new Exception($tmp[0]['Error_Msg'], 500);
				}

				if (in_array($this->MorbusType_SysNick, array('crazy','narc')) && $this->getRegionNick() != 'ufa'){
					$this->load->model('MorbusCrazy_model');
					$this->MorbusCrazy_model->setCauseEndSurveyType(array(
						'Morbus_id' => $this->Morbus_id,
						'pmUser_id' => $this->pmUser_id,
						'CrazyCauseEndSurveyType_id' => $this->PersonRegisterOutCause_id
					));
				}
			}
			// Возвращение пациента в регистр, нужно удалить дату закрытия заболевания
			if (isset($this->Morbus_id) && isset($this->Mode) && isset($this->PersonRegister_id) && in_array($this->Mode, array('homecoming', 'relapse'))) {
				$this->load->model('Morbus_model');
				$tmp = $this->Morbus_model->openMorbus(array(
					'Morbus_id' => $this->Morbus_id
					, 'pmUser_id' => $this->pmUser_id
				));
				if (false == empty($tmp[0]['Error_Msg'])) {
					$this->rollbackTransaction();
					throw new Exception($tmp[0]['Error_Msg'], 500);
				}
				if (in_array($this->MorbusType_SysNick, array('crazy','narc')) && $this->getRegionNick() != 'ufa'){
					$this->load->model('MorbusCrazy_model');
					$this->MorbusCrazy_model->setCauseEndSurveyType(array(
						'Morbus_id' => $this->Morbus_id,
						'pmUser_id' => $this->pmUser_id,
						'CrazyCauseEndSurveyType_id' =>null
					));
				}
			}
			// Включение пациента в регистр на основе извещения
			if (isset($this->EvnNotifyBase_id) && (empty($this->PersonRegister_id) ||
				(isset($this->Mode) && isset($this->PersonRegister_id) && in_array($this->Mode, array('homecoming', 'relapse')))
			)) {

				$en = $this->loadEvnNotifyBaseData(array(
					'EvnNotifyBase_id' => $this->EvnNotifyBase_id,
				));
				if ('nephro' == $this->MorbusType_SysNick && is_array($en) && count($en) == 1) {
					$this->load->model('Messages_model');
					$noticeResponse = $this->Messages_model->autoMessage(array(
						'autotype' => 1,
						'pmUser_id' => $this->pmUser_id,
						'User_rid' => $en[0]['pmUser_insID'],
						'Lpu_rid' => $en[0]['Lpu_id'],
						'MedPersonal_rid' => $en[0]['MedPersonal_id'],
						'type' => 1,
						'title' => 'Включение пациента в регистр',
						'text' => "Пациент {$en[0]['Person_SurName']} {$en[0]['Person_FirName']} {$en[0]['Person_SecName']}
				        {$en[0]['Person_BirthDay']} включен в регистр по нефрологии.",
					));
				}
				if ($this->getRegionNick() == 'perm' && 'onko' == $this->MorbusType_SysNick && is_array($en)) {
					$this->load->model('Messages_model');
					$mesParams = array(
						'autotype' => 1,
						'pmUser_id' => $this->pmUser_id,
						'User_rid' => $en[0]['pmUser_insID'],
						'Lpu_rid' => $en[0]['Lpu_id'],
						'MedPersonal_rid' => $en[0]['MedPersonal_id'],
						'type' => 1,
						'title' => 'Пациент включен в регистр по онкологии',
						'text' => ("Пациент {$en[0]['Person_SurName']} {$en[0]['Person_FirName']} {$en[0]['Person_SecName']},
						 {$en[0]['Person_BirthDay']}, включен в регистр по онкологии.
						Создано ".date('d.m.Y H:i')."."),
					);
					$noticeResponse = $this->Messages_model->autoMessage($mesParams);
				}
			}


			if ('palliat' == $this->MorbusType_SysNick) {
				// Пациент прикреплен к участку, с которым связано место работы врача
				// В разделе «Уведомления по классам событий для врача поликлиники» подят флаг «Включение пациента в регистр паллиативной помощи»
				$resp_msf = $this->queryResult("
					select
						ps.Person_SurName,
						ps.Person_FirName,
						ps.Person_SecName,
						convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
						MSR.MedPersonal_id,
						MSR.Lpu_id,
						pu.pmUser_id
					from
						v_PersonState ps (nolock)
						inner join v_PersonCardState PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
						inner join v_MedStaffRegion MSR with (nolock) on MSR.LpuRegion_id = PC.LpuRegion_id
						inner join v_pmUserCache pu with (nolock) on pu.MedPersonal_id = MSR.MedPersonal_id and pu.pmUser_evnclass like '%PersonRegisterPalliat%'
					where
						ps.Person_id = :Person_id
				", array(
					'Person_id' => $this->Person_id
				));
				$this->load->model('Messages_model');
				foreach($resp_msf as $one_msf) {
					$mesParams = array(
						'autotype' => 1,
						'pmUser_id' => $this->pmUser_id,
						'User_rid' => $one_msf['pmUser_id'],
						'Lpu_rid' => $one_msf['Lpu_id'],
						'MedPersonal_rid' => $one_msf['MedPersonal_id'],
						'type' => 1,
						'title' => 'Пациент включен в регистр по паллиативной помощи',
						'text' => ("Пациент {$one_msf['Person_SurName']} {$one_msf['Person_FirName']} {$one_msf['Person_SecName']},
						 {$one_msf['Person_BirthDay']} был включен в регистр по паллиативной помощи " . date('d.m.Y H:i') . "."),
					);
					$noticeResponse = $this->Messages_model->autoMessage($mesParams);
				}
			}

			$this->_afterSave($r);
			$this->commitTransaction();
			if(!empty($this->PersonRegisterType_id) && $this->PersonRegisterType_id == 9 && !empty($this->MorbusHIV_id) && !empty($result[0]['PersonRegister_id'])){
				$result[0]['MorbusHIV_id'] = $this->MorbusHIV_id;
			}
			if(!empty($this->MorbusHIV_id)){
				$result[0]['MorbusHIV_id'] = $this->MorbusHIV_id;
			}elseif (!empty($this->MorbusOnko_id)) {
				$result[0]['MorbusOnko_id'] = $this->MorbusOnko_id;
			}elseif (!empty($this->MorbusTub_id)) {
				$result[0]['MorbusTub_id'] = $this->MorbusTub_id;
			}
			$this->PersonRegister_id = $result[0]['PersonRegister_id'];
		} else {
			$this->rollbackTransaction();
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Сохранение данных регистра
	 */
	public function savePersonRegister($data) {
		try {
			$this->setSessionParams($data['session']);

			if (!empty($data['PersonRegister_id'])) {
				$this->setPersonRegister_id($data['PersonRegister_id']);
				if (!$this->load()) {
					throw new Exception('Ошибка при получении данных регистра');
				}
			}
			if (array_key_exists('Person_id', $data)) {
				$this->setPerson_id($data['Person_id']);
			}
			if (array_key_exists('PersonRegisterType_id', $data)) {
				$this->setPersonRegisterType_id($data['PersonRegisterType_id']);
			}
			if (array_key_exists('PersonRegisterType_SysNick', $data)) {
				$this->setPersonRegisterType_SysNick($data['PersonRegisterType_SysNick']);
			}
			if (array_key_exists('MorbusType_id', $data)) {
				$this->setMorbusType_id($data['MorbusType_id']);
			}
			if (array_key_exists('MorbusType_SysNick', $data)) {
				$this->setMorbusType_SysNick($data['MorbusType_SysNick']);
			}
			if (array_key_exists('Diag_id', $data)) {
				$this->setDiag_id($data['Diag_id']);
			}
			if (array_key_exists('MorbusProfDiag_id', $data)) {
				$this->setMorbusProfDiag_id($data['MorbusProfDiag_id']);
			}
			if (array_key_exists('ignoreCheckAnotherDiag', $data)) {
				$this->setignoreCheckAnotherDiag($data['ignoreCheckAnotherDiag']);
			}
			if (array_key_exists('PersonRegister_Code', $data)) {
				$this->setPersonRegister_Code($data['PersonRegister_Code']);
			}
			if (array_key_exists('PersonRegister_setDate', $data)) {
				$this->setPersonRegister_setDate($data['PersonRegister_setDate']);
			}
			if (array_key_exists('PersonRegister_disDate', $data)) {
				$this->setPersonRegister_disDate($data['PersonRegister_disDate']);
			}
			if (array_key_exists('Morbus_id', $data)) {
				$this->setMorbus_id($data['Morbus_id']);
			}
			if (array_key_exists('PersonRegisterOutCause_id', $data)) {
				$this->setPersonRegisterOutCause_id($data['PersonRegisterOutCause_id']);
			}
			if (array_key_exists('PersonRegisterOutCause_SysNick', $data)) {
				$this->setPersonRegisterOutCause_SysNick($data['PersonRegisterOutCause_SysNick']);
			}
			if (array_key_exists('PersonRegister_Alcoholemia', $data)) {
				$this->setPersonRegister_Alcoholemia($data['PersonRegister_Alcoholemia']);
			}
			if (array_key_exists('MedPersonal_iid', $data)) {
				$this->setMedPersonal_iid($data['MedPersonal_iid']);
			}
			if (array_key_exists('Lpu_iid', $data)) {
				$this->setLpu_iid($data['Lpu_iid']);
			}
			if (array_key_exists('MedPersonal_did', $data)) {
				$this->setMedPersonal_did($data['MedPersonal_did']);
			}
			if (array_key_exists('Lpu_did', $data)) {
				$this->setLpu_did($data['Lpu_did']);
			}
			if (array_key_exists('EvnNotifyBase_id', $data)) {
				$this->setEvnNotifyBase_id($data['EvnNotifyBase_id']);
			}
			if (array_key_exists('RiskType_id', $data)) {
				$this->setRiskType_id($data['RiskType_id']);
			}
			if (array_key_exists('PregnancyResult_id', $data)) {
				$this->setPregnancyResult_id($data['PregnancyResult_id']);
			}
			if (array_key_exists('autoExcept', $data) && $data['autoExcept']) {
				$this->setAutoExcept(true);
			}
			if (array_key_exists('histCreated', $data) && $data['histCreated']) {
				$this->setHistCreated(true);
			}
			$response = $this->save();
		} catch(Exception $e) {
			$response = $this->createError($e->getCode(), $e->getMessage());
		}

		return $response;
	}

	/**
	 * Сохранение причины невключения в регистр
	 * @param array $data
	 * @return array
	 */
	private function saveEvnNotifyBase($data) {
		$action = (isset($data['EvnNotifyBase_id'])) ? 'upd' : 'ins';
		$p = array(
			'EvnNotifyBase_id' => $data['EvnNotifyBase_id'],
			'EvnNotifyBase_pid' => $data['EvnNotifyBase_pid'],
			'EvnNotifyBase_rid' => $data['EvnNotifyBase_rid'],
			'EvnNotifyBase_setDT' => $data['EvnNotifyBase_setDT'],
			'EvnNotifyBase_disDT' => $data['EvnNotifyBase_disDT'],
			'EvnNotifyBase_didDT' => $data['EvnNotifyBase_didDT'],
			'EvnNotifyBase_niDate' => $data['EvnNotifyBase_niDate'],
			'PersonRegisterFailIncludeCause_id' => $data['PersonRegisterFailIncludeCause_id'],
			'MedPersonal_niid' => $data['MedPersonal_niid'],
			'Lpu_niid' => $data['Lpu_niid'],
			'EvnOnkoNotify_Comment' => (!empty($data['EvnOnkoNotify_Comment'])?$data['EvnOnkoNotify_Comment']:null),
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'EvnNotifyBase_signDT' => $data['EvnNotifyBase_signDT'],
			'EvnNotifyBase_IsSigned' => $data['EvnNotifyBase_IsSigned'],
			'pmUser_signID' => $data['pmUser_signID'],
			'Morbus_id' => $data['Morbus_id'],
			'MorbusType_id' => $data['MorbusType_id'],
			'EvnNotifyBase_IsAuto' => (!empty($data['EvnNotifyBase_IsAuto'])?$data['EvnNotifyBase_IsAuto']:null),
			'pmUser_id' => $data['pmUser_id']
		);
		$q = '
			declare
				@EvnNotifyBase_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @EvnNotifyBase_id = :EvnNotifyBase_id;
			exec dbo.p_EvnNotifyBase_' . $action . '
				@EvnNotifyBase_id = @EvnNotifyBase_id output,
				@EvnNotifyBase_pid = :EvnNotifyBase_pid,
				@EvnNotifyBase_rid = :EvnNotifyBase_rid,
				@EvnNotifyBase_setDT = :EvnNotifyBase_setDT,
				@EvnNotifyBase_disDT = :EvnNotifyBase_disDT,
				@EvnNotifyBase_didDT = :EvnNotifyBase_didDT,
				@EvnNotifyBase_niDate = :EvnNotifyBase_niDate,
				@PersonRegisterFailIncludeCause_id = :PersonRegisterFailIncludeCause_id,
				@MedPersonal_niid = :MedPersonal_niid,
				@Lpu_niid = :Lpu_niid,
				@EvnOnkoNotify_Comment = :EvnOnkoNotify_Comment,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@Lpu_id = :Lpu_id,
				@MedPersonal_id = :MedPersonal_id,
				@EvnNotifyBase_signDT = :EvnNotifyBase_signDT,
				@EvnNotifyBase_IsSigned = :EvnNotifyBase_IsSigned,
				@pmUser_signID = :pmUser_signID,
				@Morbus_id = :Morbus_id,
				@MorbusType_id = :MorbusType_id,
				@EvnNotifyBase_IsAuto = :EvnNotifyBase_IsAuto,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnNotifyBase_id as EvnNotifyBase_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		';
		$r = $this->db->query($q, $p);
		if (false == is_object($r)) {
			return false;
		}
		$response = $r->result('array');
		if ('nephro' == $data['MorbusType_SysNick'] && empty($response[0]['Error_Msg'])) {
			$this->load->model('Messages_model');
			$noticeResponse = $this->Messages_model->autoMessage(array(
				'autotype' => 1,
				'pmUser_id' => $data['pmUser_id'],
				'User_rid' => $data['pmUser_insID'],
				'Lpu_rid' => $data['Lpu_id'],
				'MedPersonal_rid' => $data['MedPersonal_id'],
				'type' => 1,
				'title' => 'Не включение пациента в регистр',
				'text' => "Пациент {$data['Person_SurName']} {$data['Person_FirName']} {$data['Person_SecName']}
				 {$data['Person_BirthDay']} не включен в регистр по нефрологии
				(по причине {$data['PersonRegisterFailIncludeCause_Name']}).",
			));
		}
		if ($this->getRegionNick() == 'perm' && 'onko' == $data['MorbusType_SysNick'] && empty($response[0]['Error_Msg'])) {
			$this->load->model('Messages_model');
			$mesParams = array(
				'autotype' => 1,
				'pmUser_id' => $data['pmUser_id'],
				'User_rid' => $data['pmUser_insID'],
				'Lpu_rid' => $data['Lpu_id'],
				'MedPersonal_rid' => $data['MedPersonal_id'],
				'type' => 1,
				'title' => 'Пациент не включен в регистр по онкологии',
				'text' => ("Пациент {$data['Person_SurName']} {$data['Person_FirName']} {$data['Person_SecName']},
				 {$data['Person_BirthDay']}, не включен в регистр по онкологии, комментарий: {$p['EvnOnkoNotify_Comment']}.
				Создано ".date('d.m.Y H:i')."."),
			);
			if($data['PersonRegisterFailIncludeCause_id'] == 1){
				$mesParams['title'] = 'Пациент не включен в регистр по онкологии: Ошибка в извещении';
			} else if($data['PersonRegisterFailIncludeCause_id'] == 2){
				$mesParams['title'] = 'Пациент не включен в регистр по онкологии: Решение оператора';
			}
			$noticeResponse = $this->Messages_model->autoMessage($mesParams);
		}
		return $response;
	}

	/**
	 * Чтение данных извещения
	 * @param array $data
	 * @return array
	 */
	private function loadEvnNotifyBaseData($data)
	{
		if (empty($data['PersonRegisterFailIncludeCause_id'])) {
			$data['PersonRegisterFailIncludeCause_id'] = null;
		}
		$p = array(
			'EvnNotifyBase_id' => $data['EvnNotifyBase_id'],
			'PersonRegisterFailIncludeCause_id' => $data['PersonRegisterFailIncludeCause_id'],
		);
		$q = '
			select
				EN.EvnNotifyBase_id
				,EN.EvnNotifyBase_pid
				,EN.EvnNotifyBase_rid
				,convert(varchar,EN.EvnNotifyBase_setDT,120) as EvnNotifyBase_setDT
				,convert(varchar,EN.EvnNotifyBase_disDT,120) as EvnNotifyBase_disDT
				,convert(varchar,EN.EvnNotifyBase_didDT,120) as EvnNotifyBase_didDT
				,convert(varchar,EN.EvnNotifyBase_niDate,120) as EvnNotifyBase_niDate
				,PRF.PersonRegisterFailIncludeCause_id
				,EN.MedPersonal_niid
				,EN.Lpu_niid
				,EN.Server_id
				,EN.PersonEvn_id
				,EN.Lpu_id
				,EN.MedPersonal_id
				,convert(varchar,EN.EvnNotifyBase_signDT,120) as EvnNotifyBase_signDT
				,EN.EvnNotifyBase_IsSigned
				,EN.pmUser_signID
				,EN.Morbus_id
				,v_MorbusType.MorbusType_id
				,v_MorbusType.MorbusType_SysNick
				,EN.pmUser_insID
				,convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay
				,PS.Person_SurName
				,PS.Person_FirName
				,PS.Person_SecName
				,PRF.PersonRegisterFailIncludeCause_Name
				,EN.EvnNotifyBase_IsAuto
			from
				v_EvnNotifyBase EN WITH (NOLOCK)
				left join v_PersonState PS WITH (NOLOCK) on PS.Person_id = EN.Person_id
				left join v_MorbusType WITH (NOLOCK) on v_MorbusType.MorbusType_id = EN.MorbusType_id
				left join v_PersonRegisterFailIncludeCause PRF WITH (NOLOCK) on PRF.PersonRegisterFailIncludeCause_id = :PersonRegisterFailIncludeCause_id
			where
				EN.EvnNotifyBase_id = :EvnNotifyBase_id
		';
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Запись причины невключения в регистр
	 * @param array $data
	 * @return array
	 */
	function notinclude($data)
	{
		$ra = $this->loadEvnNotifyBaseData($data);
		if (empty($ra) || !is_array($ra[0]) || empty($ra[0]) || empty($ra[0]['PersonRegisterFailIncludeCause_id'])) {
			return false;
		}
		$ra[0]['EvnNotifyBase_niDate'] = date('Y-m-d');
		$ra[0]['MedPersonal_niid'] = $data['MedPersonal_niid'];
		$ra[0]['Lpu_niid'] = $data['Lpu_niid'];
		$ra[0]['EvnOnkoNotify_Comment'] = (!empty($data['EvnOnkoNotify_Comment'])?$data['EvnOnkoNotify_Comment']:null);
		$ra[0]['pmUser_id'] = $data['pmUser_id'];
		return $this->saveEvnNotifyBase($ra[0]);
	}

	/**
	 * Удаление записи регистра
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * Если транзакция была начата ранее, то нужно установить false
	 * @return array
	 * @throws Exception
	 */
	function doDelete($data = array(), $isAllowTransaction = true)
	{
		if (empty($data)) {
			throw new Exception('Не переданы параметры');
		}
		$this->setScenario(self::SCENARIO_DELETE);
		$this->setParams($data);
		try {
			$this->beginTransaction();
			// контроль
			$query = '
				select
					PR.PersonRegister_id
					,PR.PersonRegisterOutCause_id
					,PR.PersonRegister_disDate
					,PR.Person_id
					,PR.Diag_id
					,PR.EvnNotifyBase_id
					,null as MorbusType_id
					,null as MorbusType_SysNick
					,null as Morbus_id
					,PRT.PersonRegisterType_SysNick
				from v_PersonRegister PR with (nolock)
					left join v_PersonRegisterType PRT with (nolock) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					left join v_EvnNotifyBase N with (nolock) on N.EvnNotifyBase_id = PR.EvnNotifyBase_id
				where
					PR.PersonRegister_id = :PersonRegister_id
			';
			$queryParams = array('PersonRegister_id' => $data['PersonRegister_id']);
			//throw new Exception(getDebugSQL($query, $queryParams), 600);
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД (01)');
			}
			$tmp = $result->result('array');
			if (empty($tmp)) {
				throw new Exception('Запись регистра не найдена');
			}
			if($tmp[0]['PersonRegisterType_SysNick'] != 'fmba'){
				$query = '
					select
						PR.PersonRegister_id
						,PR.PersonRegisterOutCause_id
						,PR.PersonRegister_disDate
						,PR.Person_id
						,PR.Diag_id
						,PR.EvnNotifyBase_id
						,v_MorbusType.MorbusType_id
						,v_MorbusType.MorbusType_SysNick
						,isnull(PR.Morbus_id,N.Morbus_id) as Morbus_id
					from v_PersonRegister PR with (nolock)
						INNER JOIN dbo.v_MorbusType with (nolock) on v_MorbusType.MorbusType_id = PR.MorbusType_id
						left join v_EvnNotifyBase N with (nolock) on N.EvnNotifyBase_id = PR.EvnNotifyBase_id
					where
						PR.PersonRegister_id = :PersonRegister_id
				';
				$queryParams = array('PersonRegister_id' => $data['PersonRegister_id']);
				//throw new Exception(getDebugSQL($query, $queryParams), 600);
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception('Ошибка БД (1)');
				}
				$tmp = $result->result('array');
				if (empty($tmp)) {
					throw new Exception('Запись регистра не найдена');
				}
			}
			$this->PersonRegister_id = $tmp[0]['PersonRegister_id'];
			$this->EvnNotifyBase_id = $tmp[0]['EvnNotifyBase_id'];
			$this->Person_id = $tmp[0]['Person_id'];
			$this->Diag_id = $tmp[0]['Diag_id'];
			$this->Morbus_id = $tmp[0]['Morbus_id'];
			$this->PersonRegister_disDate = $tmp[0]['PersonRegister_disDate'];
			$this->PersonRegisterOutCause_id = $tmp[0]['PersonRegisterOutCause_id'];
			$this->_MorbusType_SysNick = $tmp[0]['MorbusType_SysNick'];
			$this->_MorbusType_id = $tmp[0]['MorbusType_id'];

			$this->load->library('swMorbus');
			swMorbus::onBeforeDeletePersonRegister($this);

			// Удаление записи регистра
			$response = $this->_delete(array(
				'PersonRegister_id' => $this->PersonRegister_id,
				'pmUser_id' => $this->promedUserId,
			));

			$this->commitTransaction();
			return $response;
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Удаление записи регистра. ' . $e->getMessage()));
		}
	}

	/**
	 * Исключение из регистра
	 * @param array $data
	 * @return array
	 */
	function out($data)
	{
		$p = array(
			'PersonRegister_id' => $data['PersonRegister_id']
		);
		$q = '
			select 				
				EvnNotifyBase_id 
				,Morbus_id 
				,v_MorbusType.MorbusType_id
				,v_MorbusType.MorbusType_SysNick
				,Person_id 
				,Diag_id
				,PersonRegisterType_id
				,PersonRegister_Code 
				,substring(convert(varchar,PersonRegister_setDate,120),1,10) as PersonRegister_setDate 
				,Lpu_iid 
				,MedPersonal_iid 
			from
				v_PersonRegister  WITH (NOLOCK)
				LEFT JOIN dbo.v_MorbusType with (nolock) on v_MorbusType.MorbusType_id = v_PersonRegister.MorbusType_id
			where
				PersonRegister_id = :PersonRegister_id
		';
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$ra = $r->result('array');
			if (empty($ra) || !is_array($ra[0]) || empty($ra[0])) {
				return array(array('Error_Msg' => 'Исключение из регистра. Запись регистра не найдена.'));
			}
			$this->PersonRegister_id = $data['PersonRegister_id'];
			$this->Person_id = $ra[0]['Person_id'];
			$this->_MorbusType_id = $ra[0]['MorbusType_id'];
			$this->_MorbusType_SysNick = $ra[0]['MorbusType_SysNick'];
			$this->Morbus_id = $ra[0]['Morbus_id'];
			$this->EvnNotifyBase_id = $ra[0]['EvnNotifyBase_id'];
			$this->Diag_id = $ra[0]['Diag_id'];
			$this->PersonRegisterType_id = $ra[0]['PersonRegisterType_id'];
			$this->PersonRegister_Code = $ra[0]['PersonRegister_Code'];
			$this->PersonRegister_setDate = $ra[0]['PersonRegister_setDate'];
			$this->MedPersonal_iid = $ra[0]['MedPersonal_iid'];
			$this->Lpu_iid = $ra[0]['Lpu_iid'];
			$this->PersonRegister_disDate = $data['PersonRegister_disDate'];
			$this->PersonRegisterOutCause_id = $data['PersonRegisterOutCause_id'];
			$this->PersonDeathCause_id = $data['PersonDeathCause_id'] ?? null;
			$this->MedPersonal_did = $data['MedPersonal_did'];
			$this->Lpu_did = $data['Lpu_did'];
			$this->pmUser_id = $data['pmUser_id'];
			if ($this->MorbusType_SysNick == 'orphan') {
				// создать извещениe на исключение из регистра
				$this->load->model('EvnNotifyOrphan_model');
				$tmp = $this->EvnNotifyOrphan_model->createEvnNotifyOrphanOut(array(
					'Lpu_id' => $data['Lpu_id']
					, 'session' => $data['session']
					, 'pmUser_id' => $this->pmUser_id
					, 'Person_id' => $this->Person_id
					, 'EvnNotifyOrphanOut_setDT' => $this->PersonRegister_disDate
				));
				if (!empty($tmp[0]['Error_Msg'])) {
					return array(array('Error_Msg' => 'Создание извещения на исключение из регистра. ' . $tmp[0]['Error_Msg']));
				}
			}
			if(!empty($data['autoExcept'])){
				$this->autoExcept = true;
			}
			$this->setSessionParams($data['session']);
			return $this->save();
		} else {
			return array(array('Error_Msg' => 'Исключение из регистра. Ошибка БД.'));
		}
	}

	/**
	 * Возвращение в регистр
	 * @param array $data
	 * @return array
	 */
	function back($data)
	{
		$p = array(
			'PersonRegister_id' => $data['PersonRegister_id']
		);
		$q = '
			select 				
				EvnNotifyBase_id 
				,Morbus_id 
				,v_MorbusType.MorbusType_id
				,v_MorbusType.MorbusType_SysNick
				,Person_id 
				,PersonRegisterType_id
				,Diag_id
				,PersonRegister_Code 
				,substring(convert(varchar,PersonRegister_setDate,120),1,10) as PersonRegister_setDate 
				,Lpu_iid 
				,MedPersonal_iid 
			from
				v_PersonRegister  WITH (NOLOCK)
				LEFT JOIN dbo.v_MorbusType with (nolock) on v_MorbusType.MorbusType_id = v_PersonRegister.MorbusType_id
			where
				PersonRegister_id = :PersonRegister_id
		';
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$ra = $r->result('array');
			if (empty($ra) || !is_array($ra[0]) || empty($ra[0])) {
				return array(array('Error_Msg' => 'Возвращение в регистр. Запись регистра не найдена.'));
			}
			$this->PersonRegister_id = $data['PersonRegister_id'];
			$this->Person_id = $ra[0]['Person_id'];
			$this->_MorbusType_id = $ra[0]['MorbusType_id'];
			$this->_MorbusType_SysNick =$ra[0]['MorbusType_SysNick'];
			$this->Morbus_id = $ra[0]['Morbus_id'];
			$this->EvnNotifyBase_id = $ra[0]['EvnNotifyBase_id'];
			$this->Diag_id = $ra[0]['Diag_id'];
			$this->PersonRegisterType_id = $ra[0]['PersonRegisterType_id'];
			$this->PersonRegister_Code = $ra[0]['PersonRegister_Code'];
			$this->PersonRegister_setDate = $ra[0]['PersonRegister_setDate'];
			$this->MedPersonal_iid = $ra[0]['MedPersonal_iid'];
			$this->Lpu_iid = $ra[0]['Lpu_iid'];
			$this->PersonRegister_disDate = null;
			$this->PersonRegisterOutCause_id = null;
			$this->MedPersonal_did = null;
			$this->Lpu_did = null;
			$this->pmUser_id = $data['pmUser_id'];
			$this->Mode = 'homecoming';
			$this->setSessionParams($data['session']);
			if($ra[0]['MorbusType_SysNick']=='crazy'){
				
				$query = "
			declare
				@Res bigint,
				@CrazyDiag bigint,
				@ErrCode int,
				@Morbus_setDT datetime = :Morbus_setDT,
				@MorbusCrazy_id bigint = (select MorbusCrazy_id from v_morbuscrazy with(nolock) where Morbus_id = :Morbus_id),
				@ErrMessage varchar(4000);
			set @Res = 0;
			if isnull(@Morbus_setDT, 0) = 0
			begin
				set @Morbus_setDT = GetDate();
			end
			set @CrazyDiag = (select top 1 CrazyDiag_id from v_CrazyDiag with(nolock) where Diag_id=:Diag_id);
			exec p_MorbusCrazyDiag_ins
				@MorbusCrazyDiag_id = @Res,
				@MorbusCrazy_id = @MorbusCrazy_id,
				@MorbusCrazyDiag_setDT = @Morbus_setDT,
				@CrazyDiag_id =  @CrazyDiag,
				@Diag_sid = :Diag_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
		";

				$params = array(
					"Diag_id"=>$data['Diag_id'],
					'pmUser_id'=>$data['pmUser_id'],
					'Morbus_setDT'=>isset($data['PersonRegister_setDate'])?$data['PersonRegister_setDate']:null,
					'Morbus_id'=>$ra[0]['Morbus_id']
				);
				$res = $this->db->query($query, $params);
			
			}
			return $this->save();
		} else {
			return array(array('Error_Msg' => 'Возвращение в регистр. Ошибка БД.'));
		}
	}

	/**
	 * Метод получения данных в форму просмотра записи регистра
	 */
	function getPersonRegisterViewData($data) {
		$query = '
			select 
				case when PR.PersonRegister_disDate is null and M.Morbus_disDT is null then \'edit\' else \'view\' end as accessType
				,PR.PersonRegister_id
				,CONVERT(varchar,PR.PersonRegister_setDate,104) as PersonRegister_setDate
				,CONVERT(varchar,PR.PersonRegister_disDate,104) as PersonRegister_disDate
				,PR.PersonRegister_Code
				
				,PR.EvnNotifyBase_id
				,LpuN.Lpu_Nick as LpuN_Name -- ЛПУ создания Извещения
				
				,PR.Morbus_id
				,M.MorbusBase_id
				,M.Diag_id -- диагноз заболевания (может не совпадать с диагнозом записи регистра, но должно быть соответствовать заболеванию)
				,Diag.Diag_FullName as Diag_Name
				
				-- специфика
				,MO.MorbusOrphan_id
				,LpuO.Lpu_id as Lpu_oid
				,LpuO.Lpu_Nick as LpuO_Name
				
				,PR.Person_id as From_id
				,PR.Person_id as Person_id
			from 
				v_PersonRegister PR with (nolock)
				inner join v_Morbus M with (nolock) on M.Morbus_id = PR.Morbus_id
				inner join v_MorbusOrphan MO with (nolock) on M.Morbus_id = MO.Morbus_id
				left join v_EvnNotifyOrphan ENO with (nolock) on M.Morbus_id = ENO.Morbus_id
				left join v_Lpu LpuO with (nolock) on /*isnull(ENO.Lpu_oid,MO.Lpu_id)*/ MO.Lpu_id = LpuO.Lpu_id
				left join v_Lpu LpuN with (nolock) on ENO.Lpu_id = LpuN.Lpu_id
				left join v_Diag Diag with (nolock) on M.Diag_id = Diag.Diag_id
			where
				PR.PersonRegister_id = :PersonRegister_id
		';
		try {
			if (empty($data['PersonRegister_id'])) {
				throw new Exception('Не передан идентификатор записи регистра');
			}
			// echo getDebugSQL($query, $data); exit();
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			//$e->getMessage()
			return false;
		}
	}

	/**
	 * Получение данных раздела "Выгрузка в федеральный регистр" формы просмотра записи регистра
	 */
	function getPersonRegisterExportViewData($data) {
		$query = '
			select 
				\'view\' as accessType
				,PRE.PersonRegisterExport_id
				,CONVERT(varchar,PRE.PersonRegisterExport_setDate,104) as PersonRegisterExport_setDate
				,PRET.PersonRegisterExportType_Name
			from 
				v_PersonRegisterExport PRE with (nolock)
				left join v_PersonRegisterExportType PRET with (nolock) on PRE.PersonRegisterExportType_id = PRET.PersonRegisterExportType_id
			where
				PRE.PersonRegister_id = :PersonRegister_id
			order by
				PRE.PersonRegisterExport_setDate
		';
		try {
			if (empty($data['PersonRegister_id'])) {
				throw new Exception('Не передан идентификатор записи регистра');
			}
			// echo getDebugSQL($query, $data); exit();
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			//$e->getMessage()
			return false;
		}
	}

	/**
	 * Получение данных раздела "Сведения об инвалидности" формы просмотра записи регистра
	 */
	function getPersonPrivilegeViewData($data) {
		$query = '
			select 
				\'view\' as accessType
				,PP.PersonPrivilege_id
				,PT.PrivilegeType_Name
				,CONVERT(varchar,PP.PersonPrivilege_begDate,104) as PersonPrivilege_begDate
				,CONVERT(varchar,PP.PersonPrivilege_endDate,104) as PersonPrivilege_endDate
			from 
				v_PersonPrivilege PP with (nolock)
				inner join v_PrivilegeType PT with(nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
			where
				PP.Person_id = :Person_id
			order by
				PP.PersonPrivilege_begDate
		';
		try {
			if (empty($data['Person_id'])) {
				throw new Exception('Не передан идентификатор человека');
			}
			// echo getDebugSQL($query, $data); exit();
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			//$e->getMessage()
			return false;
		}
	}

	/**
	 * Получение данных раздела "Федеральная льгота" формы просмотра записи регистра
	 */
	function getPersonPrivilegeFedViewData($data) {
		$query = '
			SELECT 
				\'view\' as accessType
				,PP.PersonPrivilege_id
				,CONVERT(varchar,PP.PersonPrivilege_begDate,104) as PersonPrivilege_begDate
				,CONVERT(varchar,PP.PersonPrivilege_endDate,104) as PersonPrivilege_endDate
				,PT.PrivilegeType_Name
				,PT.PrivilegeType_Code
			FROM
				v_PersonPrivilege PP with(nolock)
				inner join v_PrivilegeType PT with(nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
			WHERE
				PP.Person_id = :Person_id
				and PT.ReceptFinance_id = 1
				--and PT.PrivilegeType_Code < 500
			ORDER BY 
				PT.PrivilegeType_Code ASC
		';
		try {
			if (empty($data['Person_id'])) {
				throw new Exception('Не передан идентификатор человека');
			}
			// echo getDebugSQL($query, $data); exit();
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			//$e->getMessage()
			return false;
		}
	}

	/**
	 * Получение данных раздела "Лекарственные препараты" формы просмотра записи регистра
	 */
	function getDrugOrphanViewData($data) {
		try {
			if (empty($data['PersonRegister_id'])) {
				throw new Exception('Не передан идентификатор записи регистра');
			}
			$queryParams = array(
				'PersonRegister_id' => $data['PersonRegister_id'],
			);
			$this->load->library('swMorbus');
			$queryParams['MorbusType_id'] = swMorbus::getMorbusTypeIdBySysNick('orphan');
			if (empty($queryParams['MorbusType_id'])) {
				throw new Exception('Попытка получить идентификатор типа заболевания провалилась', 500);
			}
			$query = '
				SELECT
					PR.Diag_id
					,PR.Person_id
				FROM
					v_PersonRegister PR WITH (NOLOCK)
				WHERE 
					PR.PersonRegister_id = :PersonRegister_id 
					AND PR.MorbusType_id = :MorbusType_id
			';
			//У пациента может быть только одно заболевание (запись регистра) с типом «орфанное».
			/*
			  // Для записей регистра с диагнозом Е70.0 или Е70.1 отображать информацию по обоим диагнозам.
			  case when DD.Diag_id is not null then DD.Diag_id else D.Diag_id end as Diag_id
			  inner join v_Diag D with (nolock) on PR.Diag_id = D.Diag_id
			  left join v_Diag DD with (nolock) on (D.Diag_Code like \'E70.0\' or D.Diag_Code like \'E70.1\') and (DD.Diag_Code like \'E70.0\' or DD.Diag_Code like \'E70.1\')

			 */
			// echo getDebugSQL($query, $queryParams); exit();
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			$tmp = $result->result('array');
			$diag_list = array();
			foreach ($tmp as $row) {
				$diag_list[] = $row['Diag_id'];
				$data['Person_id'] = $row['Person_id'];
			}
			if (empty($diag_list)) {
				throw new Exception('Ошибка получения данных по записи регистра');
			}
			if (empty($data['Person_id'])) {
				throw new Exception('Не передан идентификатор человека');
			}

			$query = "
				SELECT
					'view' as accessType
					,EvnRecept.Entity + '_' + convert(varchar,isnull(EvnRecept.EvnRecept_id,EvnRecept.ReceptOtov_id)) as DrugOrphan_id
					,Result.ReceptResult_Name -- Статус
					,rtrim(EvnRecept.EvnRecept_Ser) as EvnRecept_Ser -- Серия
					,rtrim(EvnRecept.EvnRecept_Num) as EvnRecept_Num -- Номер
					,DrugMnn.DrugMnn_Name -- МНН
					,rtrim(Drug.Drug_Name) as DrugTorg_Name -- Торговое наименование
					,ROUND(EvnRecept.EvnRecept_Kolvo, 3) as EvnRecept_Kolvo -- Количество
					,convert(varchar,EvnRecept.EvnRecept_setDate,104) as EvnRecept_setDate -- Дата выписки
					,convert(varchar,EvnRecept.EvnRecept_otpDate,104) as EvnRecept_otpDate -- Дата отоваривания
				FROM
					(
						select 
							E.EvnRecept_id
							,null as ReceptOtov_id
							,'EvnRecept' as Entity
							,E.Diag_id
							,E.Person_id
							,E.EvnRecept_Ser
							,E.EvnRecept_Num
							,E.EvnRecept_Kolvo
							,E.EvnRecept_setDate as EvnRecept_setDate
							,E.EvnRecept_otpDT as EvnRecept_otpDate
							,isnull(E.Drug_oid,E.Drug_id) as Drug_id
							,E.EvnRecept_obrDT as EvnRecept_obrDate
							,E.ReceptValid_id
							,E.ReceptDelayType_id
							,E.EvnRecept_deleted
						from v_EvnRecept_all E with (nolock)
						union all
						select
							EvnRecept_id
							,ReceptOtov_id
							,'ReceptOtov' as Entity
							,Diag_id
							,Person_id
							,EvnRecept_Ser
							,EvnRecept_Num
							,EvnRecept_Kolvo
							,EvnRecept_setDate as EvnRecept_setDate
							,EvnRecept_otpDT as EvnRecept_otpDate
							,Drug_id
							,EvnRecept_obrDT as EvnRecept_obrDate
							,ReceptValid_id
							,ReceptDelayType_id
							,null as EvnRecept_deleted
						from v_ReceptOtovUnSub with (nolock)
					) EvnRecept
					LEFT JOIN v_Drug Drug with (nolock) on Drug.Drug_id = EvnRecept.Drug_id
					LEFT JOIN DrugMnn with (nolock) on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
					LEFT JOIN v_ReceptResult Result with (nolock) on (
						case 
							when isnull(EvnRecept.EvnRecept_deleted,1) = 2
							then 12 -- Удалённый МО

							when EvnRecept.ReceptDelayType_id = 3
							then 11 -- Отказ

							when EvnRecept.EvnRecept_obrDate is not null and EvnRecept.EvnRecept_otpDate is null and EvnRecept.ReceptDelayType_id != 3 and 
								dbo.tzGetDate() <= (case when EvnRecept.ReceptValid_id = 1 then dateadd(day, 14, EvnRecept.EvnRecept_setDate) else dateadd(month, case when EvnRecept.ReceptValid_id = 2 then 1 else 3 end, EvnRecept.EvnRecept_setDate) end)
							then 7 -- Рецепт отсрочен - имеется дата обращения, нет даты отоваривания, рецепт не просрочен и нет отказа
							
							when EvnRecept.EvnRecept_obrDate is not null and EvnRecept.EvnRecept_otpDate is null and EvnRecept.ReceptDelayType_id != 3 and 
								dbo.tzGetDate() >= (case when EvnRecept.ReceptValid_id = 1 then dateadd(day, 14, EvnRecept.EvnRecept_setDate) else dateadd(month, case when EvnRecept.ReceptValid_id = 2 then 1 else 3 end, EvnRecept.EvnRecept_setDate) end)
							then 8 -- Рецепт просрочен - имеется дата обращения, нет даты отоваривания, нет отказа и Текущая дата > Даты выписки + Срок действия рецепта
							
							when EvnRecept.EvnRecept_obrDate is null and EvnRecept.EvnRecept_otpDate is null and EvnRecept.ReceptDelayType_id != 3 and 
								dbo.tzGetDate() >= (case when EvnRecept.ReceptValid_id = 1 then dateadd(day, 14, EvnRecept.EvnRecept_setDate) else dateadd(month, case when EvnRecept.ReceptValid_id = 2 then 1 else 3 end, EvnRecept.EvnRecept_setDate) end)
							then 9 -- Рецепт просрочен без обращения - нет даты обращения и даты отоваривания, нет отказа и Текущая дата > Даты выписки + Срок действия рецепта
							
							when EvnRecept.EvnRecept_obrDate is not null and EvnRecept.EvnRecept_otpDate is null and EvnRecept.ReceptDelayType_id != 3 and 
								dbo.tzGetDate() >= (case when EvnRecept.ReceptValid_id = 1 then dateadd(day, 14, EvnRecept.EvnRecept_setDate) else dateadd(month, case when EvnRecept.ReceptValid_id = 2 then 1 else 3 end, EvnRecept.EvnRecept_setDate) end)
							then 10 -- Рецепт просрочен после отсрочки - нет даты отоваривания, есть дата обращения и нет отказа и Текущая дата > Даты выписки + Срок действия рецепта.
							
							when EvnRecept.EvnRecept_otpDate is not null and EvnRecept.EvnRecept_obrDate is not null and EvnRecept.EvnRecept_otpDate > EvnRecept.EvnRecept_obrDate
							then 6 -- Рецепт отоварен после отсрочки - имеются даты отоваривания и обращения и дата отоваривания > даты обращения
							
							when EvnRecept.EvnRecept_otpDate is not null and (EvnRecept.EvnRecept_obrDate is null or EvnRecept.EvnRecept_obrDate = EvnRecept.EvnRecept_otpDate)
							then 5 -- Рецепт отоварен без отсрочки - (имеются даты отоваривания и обращения и дата отоваривания = дате обращения) или (имеется дата отоваривания и нет даты обращения)
							
							when EvnRecept.EvnRecept_otpDate is not null
							then 4 -- Рецепт отоварен
							
							when EvnRecept.EvnRecept_otpDate is null and EvnRecept.EvnRecept_obrDate is not null
							then 3 -- Рецепт не отоварен
							
							when EvnRecept.EvnRecept_obrDate is not null
							then 1 -- Было обращение
							
							when EvnRecept.EvnRecept_obrDate is null
							then 2 -- Не было обращения
														
						end
					) = Result.ReceptResult_id
				WHERE
					EvnRecept.Person_id = :Person_id AND EvnRecept.Diag_id in (" . implode(', ', $diag_list) . ")
				ORDER BY
					EvnRecept.EvnRecept_setDate DESC
			";
			// echo getDebugSQL($query, $data); exit();
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			//$e->getMessage()
			return false;
		}
	}


	/**
	 *  Импорт данных регистра ВЗН из xls файла.
	function importVznRegistryFromXls($data) {
		require_once("promed/libraries/Spreadsheet_Excel_Reader/Spreadsheet_Excel_Reader.php");

		$result = array(array('Error_Msg' => null));
		$data_start = false;

		$xls_data = new Spreadsheet_Excel_Reader();
		$xls_data->setOutputEncoding('CP1251');
		$xls_data->read($data['FileFullName']);

		if (isset($xls_data->sheets[0])) {
			for ($i = 1; $i <= $xls_data->sheets[0]['numRows']; $i++) {
				if (isset($xls_data->sheets[0]['cells'][$i])) {
					$row = $xls_data->sheets[0]['cells'][$i];

					if ($data_start) {
						//разбор файла
					} else {
						if (isset($row[1]) && strpos($row[1], '/') > -1) {
							$data_start = true;
						}
					}
				}
			}
		}

		return array('success' => true);
	}
	*/

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
		// Список заболеваний с возможностью автоматического включения в регистр при создании извещения
		if (false == in_array($morbustype_sysnick, array('crazy','narc','hepa','orphan','tub','vener','hiv'))) {
			return false;
		}
		// Проверяем диагноз
		$diagData = $this->getFirstRowFromQuery('
				select top 1 Diag.Diag_pid, Diag.Diag_Code
				from v_Diag Diag with (nolock)
				where Diag.Diag_id = :Diag_id',array(
			'Diag_id' => $diag_id,
		));
		if (false == is_array($diagData)) {
			throw new Exception('Передан несуществующий диагноз', 500);
		}

		if ('vener' == $morbustype_sysnick) {
			/*
			Для диагнозов В35.0-В35.9 и В86 запись регистра с типом "Венерология" не создавать.
			Включение в регистр производит оператор, т.е. в БД может быть извещение без записи регистра
			*/
			if (394 == $diagData['Diag_pid'] || 441 == $diagData['Diag_pid']) {
				return false;
			}
		} else {
			// Для остальных ещё в настройках должно быть разрешено автоматическое включение в регистр
			if (empty($this->globalOptions) || empty($this->globalOptions['globals'])) {
				throw new Exception('Не получилось прочитать настройки', 500);
			}
			$setting = "register_{$morbustype_sysnick}_auto_include";
			if ('narc' == $morbustype_sysnick) {
				$setting = "register_narko_auto_include";
			}
			/*if (false == array_key_exists($setting, $this->globalOptions['globals'])) {
				throw new Exception("Настройка {$setting} не обнаружена!", 500);
			}*/
			if (false == array_key_exists($setting, $this->globalOptions['globals']) || 1 != $this->globalOptions['globals'][$setting]) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Автоматическое включение в регистр внутри транзакции при создании извещения
	 *
	 * Операция вызывается из swMorbus::onAfterSaveEvnNotify
	 * На клиенте для создания извещения используется функция checkEvnNotifyBaseWithAutoIncludeInPersonRegister
	 *
	 * @param array $data
	 * @return array Если не надо автоматом включать, вернется PersonRegister_id = null
	 * @throws Exception Отменяет сохранение извещения
	 */
	function autoInclude($data)
	{
		if (empty($data['Person_id'])
			|| empty($data['Morbus_id'])
			|| empty($data['MorbusType_id'])
			|| empty($data['MorbusType_SysNick'])
			|| empty($data['Diag_id'])
			|| empty($data['PersonRegister_setDate'])
			|| empty($data['EvnNotifyBase_id'])
			|| empty($data['MedPersonal_iid'])
			|| empty($data['Lpu_iid'])
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$this->setParams($data);
		$response = array(
			'PersonRegister_id' => null,
		);
		$response['Morbus_id'] = $data['Morbus_id'];
		$response['MorbusType_id'] = $data['MorbusType_id'];
		$response['EvnNotifyBase_id'] = $data['EvnNotifyBase_id'];

		if (false == $this->isAllowAutoInclude($data['MorbusType_SysNick'], $data['Diag_id'])) {
			return $response;
		}

		$response['PersonRegister_id'] = $this->getFirstResultFromQuery('
				select top 1 PersonRegister_id
				from v_PersonRegister with (nolock)
				where Person_id = :Person_id
				and Morbus_id = :Morbus_id
				and PersonRegister_disDate is null',array(
			'Person_id' => $data['Person_id'],
			'Morbus_id' => $data['Morbus_id'],
		));
		if ($response['PersonRegister_id'] > 0) {
			return $response;
		}
		// Если в системе нет Объекта «Запись регистра», то автоматически создавать
		$tmp = $this->execCommonSP('p_PersonRegister_ins',array(
			'PersonRegister_id' => array(
				'value' => null,
				'out' => true,
				'type' => 'bigint',
			),
			'Person_id' => $data['Person_id'],
			'Morbus_id' => $data['Morbus_id'],
			'MorbusType_id' => $data['MorbusType_id'],
			'Diag_id' => $data['Diag_id'],
			'PersonRegister_Code' => null,
			'PersonRegister_setDate' => $data['PersonRegister_setDate'],
			'Lpu_iid' => $data['Lpu_iid'],
			'MedPersonal_iid' => $data['MedPersonal_iid'],
			'EvnNotifyBase_id' => $data['EvnNotifyBase_id'],
			'pmUser_id' => $data['session']['pmuser_id']
		));
		if (empty($tmp)) {
			throw new Exception('Ошибка запроса записи данных объекта в БД', 500);
		}
		if (isset($tmp[0]['Error_Msg'])) {
			throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
		}
		$response['PersonRegister_id'] = $tmp[0]['PersonRegister_id'];
		return $response;
	}

	/**
	 * Получение списка видов регистров людей
	 */
	function loadPersonRegisterTypeGrid($data) {
		$params = array();
		$query = "
			select
				PRT.PersonRegisterType_id,
				PRT.PersonRegisterType_Code,
				PRT.PersonRegisterType_Name,
				PRT.PersonRegisterType_SysNick
			from v_PersonRegisterType PRT with(nolock)
			order by PRT.PersonRegisterType_Code
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение истории изменения данных в регистре
	 */
	function loadPersonRegisterHistList($data) {
		$params = array('PersonRegister_id' => $data['PersonRegister_id']);

		$query = "
			declare @bigdate date = dateadd(year, 50, dbo.tzGetDate())
			select
				PRH.PersonRegisterHist_id,
				PRH.PersonRegister_id,
				convert(varchar(10), PRH.PersonRegisterHist_begDate, 104) as PersonRegisterHist_begDate,
				convert(varchar(10), PRH.PersonRegisterHist_endDate, 104) as PersonRegisterHist_endDate,
				PRH.PersonRegisterHist_NumCard,
				L.Lpu_id,
				L.Lpu_Nick,
				MP.MedPersonal_id,
				MP.Person_Fio as MedPersonal_Fio
			from
				v_PersonRegisterHist PRH with(nolock)
				left join v_Lpu_all L with(nolock) on L.Lpu_id = PRH.Lpu_id
				outer apply(
					select top 1 * 
					from v_MedPersonal with(nolock) 
					where MedPersonal_id = PRH.MedPersonal_id
				) MP
			where
				PRH.PersonRegister_id = :PersonRegister_id
			order by
				PRH.PersonRegisterHist_begDate desc,
				isnull(PRH.PersonRegisterHist_endDate, @bigdate) desc
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Полученеи последней записи из истории изменения данных регистра
	 */
	function getLastPersonRegisterHist($data) {
		$params = array('PersonRegister_id' => $data['PersonRegister_id']);

		$query = "
			declare @bigdate date = dateadd(year, 50, dbo.tzGetDate())
			select top 1
				lastPRH.PersonRegisterHist_id,
				lastPRH.PersonRegister_id,
				lastPRH.PersonRegisterHist_NumCard,
				lastPRH.PersonRegisterHist_begDate,
				lastPRH.Lpu_id,
				lastPRH.MedPersonal_id
			from
				v_PersonRegister PR with(nolock)
				cross apply (
					select top 1 *
					from v_PersonRegisterHist PRH with(nolock)
					where PRH.PersonRegister_id = PR.PersonRegister_id
					order by 
					PRH.PersonRegisterHist_begDate desc,
					isnull(PRH.PersonRegisterHist_endDate, @bigdate) desc
				) as lastPRH
			where
				PR.PersonRegister_id = :PersonRegister_id
		";

		return $this->getFirstRowFromQuery($query, $params, true);
	}

	/**
	 * Получение последених данных из истории изменения регистра для редактирования (будет создана новая запись в истории)
	 */
	function loadPersonRegisterHistForm($data) {
		$params = array('PersonRegister_id' => $data['PersonRegister_id']);

		$query = "
			declare @bigdate date = dateadd(year, 50, dbo.tzGetDate())
			select top 1
				null as PersonRegisterHist_id,
				PR.PersonRegister_id,
				isnull(lastPRH.PersonRegisterHist_NumCard, PR.PersonRegister_Code) as PersonRegisterHist_NumCard,
				convert(varchar(10), isnull(lastPRH.PersonRegisterHist_begDate, PR.PersonRegister_setDate), 104) as PersonRegisterHist_begDate,
				isnull(lastPRH.Lpu_id, PR.Lpu_iid) as Lpu_id,
				isnull(lastPRH.MedPersonal_id, PR.MedPersonal_iid) as MedPersonal_id
			from
				v_PersonRegister PR with(nolock)
				outer apply (
					select top 1 *
					from v_PersonRegisterHist PRH with(nolock)
					where PRH.PersonRegister_id = PR.PersonRegister_id
					order by 
					PRH.PersonRegisterHist_begDate desc,
					isnull(PRH.PersonRegisterHist_endDate, @bigdate) desc
				) as lastPRH
			where
				PR.PersonRegister_id = :PersonRegister_id
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Сохраненеи записи истории изменения данных регистра
	 */
	function savePersonRegisterHist($data) {
		$params = array(
			'PersonRegisterHist_id' => !empty($data['PersonRegisterHist_id'])?$data['PersonRegisterHist_id']:null,
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PersonRegisterHist_NumCard' => !empty($data['PersonRegisterHist_NumCard'])?$data['PersonRegisterHist_NumCard']:null,
			'PersonRegisterHist_begDate' => $data['PersonRegisterHist_begDate'],
			'PersonRegisterHist_endDate' => !empty($data['PersonRegisterHist_endDate'])?$data['PersonRegisterHist_endDate']:null,
			'Lpu_id' => !empty($data['Lpu_id'])?$data['Lpu_id']:null,
			'MedPersonal_id' => !empty($data['MedPersonal_id'])?$data['MedPersonal_id']:null,
			'pmUser_id' => $data['pmUser_id'],
		);

		if (empty($params['PersonRegisterHist_id'])) {
			$procedure = 'p_PersonRegisterHist_ins';
		} else {
			$procedure = 'p_PersonRegisterHist_upd';
		}

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :PersonRegisterHist_id;
			exec {$procedure}
				@PersonRegisterHist_id = @Res output,
				@PersonRegister_id = :PersonRegister_id,
				@PersonRegisterHist_NumCard = :PersonRegisterHist_NumCard,
				@PersonRegisterHist_begDate = :PersonRegisterHist_begDate,
				@PersonRegisterHist_endDate = :PersonRegisterHist_endDate,
				@Lpu_id = :Lpu_id,
				@MedPersonal_id = :MedPersonal_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as PersonRegisterHist_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при сохранении истории редактирования записи регистра');
		}
		return $response;
	}

	/**
	 * Сравнение двух записией истории изменения данных регистра
	 */
	function comparePersonRegisterHist($oldHist, $newHist) {
		$fields = array(
			'PersonRegister_id',
			'PersonRegisterHist_begDate',
			'PersonRegisterHist_NumCard',
			'Lpu_id',
			'MedPersonal_id',
		);
		if ($oldHist['PersonRegisterHist_begDate'] instanceof DateTime) {
			$oldHist['PersonRegisterHist_begDate'] = $oldHist['PersonRegisterHist_begDate']->format('Y-m-d');
		}
		if ($newHist['PersonRegisterHist_begDate'] instanceof DateTime) {
			$newHist['PersonRegisterHist_begDate'] = $newHist['PersonRegisterHist_begDate']->format('Y-m-d');
		}
		foreach($fields as $field) {
			if ((string)$oldHist[$field] !== (string)$newHist[$field]) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Создание записи в истории изменения данных регистра
	 */
	function createPersonRegisterHist($data) {
		$this->beginTransaction();

		//Если добавляемая и предыдущая записи одинаковые, то предыдущую не закрывать, новую не добавлять
		//Будет обновлена запись регистра последними данными из истории, если не передан PersonRegisterUpdated => true
		$isSame = false;
		$response = array(array('PersonRegisterHist_id' => null, 'Error_Code' => null,  'Error_Msg' => null));

		if (empty($data['PersonRegisterHist_begDate'])) {
			$data['PersonRegisterHist_begDate'] = $this->getFirstResultFromQuery("
				select convert(varchar(10), dbo.tzGetDate(), 120)
			");
			if (!$data['PersonRegisterHist_begDate']) {
				$this->rollbackTransaction();
				return $this->createError('','Ошибка при получении даты добавления записи в историю изменения регистра');
			}
		}

		//Закрытие предыдущей записи в истории
		$LastHist = $this->getLastPersonRegisterHist($data);
		if ($LastHist === false) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении данных предыдущей записи в истории');
		}
		if ($LastHist) {
			$isSame = $this->comparePersonRegisterHist($LastHist, $data);
			if ($isSame) {
				$response[0]['PersonRegisterHist_id'] = $LastHist['PersonRegisterHist_id'];
			} else {
				$params = array_merge($LastHist, array(
					'PersonRegisterHist_endDate' => $data['PersonRegisterHist_begDate'],
					'pmUser_id' => $data['pmUser_id'],
				));
				$resp = $this->savePersonRegisterHist($params);
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}
		}

		//Добавление новой записи в истории
		if (!$isSame) {
			$params = array(
				'PersonRegisterHist_id' => null,
				'PersonRegister_id' => $data['PersonRegister_id'],
				'PersonRegisterHist_NumCard' => !empty($data['PersonRegisterHist_NumCard'])?$data['PersonRegisterHist_NumCard']:null,
				'PersonRegisterHist_begDate' => $data['PersonRegisterHist_begDate'],
				'Lpu_id' => !empty($data['Lpu_id'])?$data['Lpu_id']:null,
				'MedPersonal_id' => !empty($data['MedPersonal_id'])?$data['MedPersonal_id']:null,
				'pmUser_id' => $data['pmUser_id'],
			);
			$response = $this->savePersonRegisterHist($params);
			if (!$this->isSuccessful($response)) {
				$this->rollbackTransaction();
				return $response;
			}
		}

		//Обновление записи в регистре данными из последней записи в истории
		if (empty($data['PersonRegisterUpdated']) || !$data['PersonRegisterUpdated']) {
			$params = array(
				'PersonRegister_id' => $data['PersonRegister_id'],
				'session' => $data['session'],
				'histCreated' => true,
			);
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
	 */
	function updatePersonRegisterFromHist($data) {
		$params = array('PersonRegister_id' => $data['PersonRegister_id']);

		$LastHist = $this->getLastPersonRegisterHist($params);
		if (!is_array($LastHist)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении данных предыдущей записи в истории');
		}

		$params = array(
			'PersonRegister_id' => $LastHist['PersonRegister_id'],
			'PersonRegister_Code' => $LastHist['PersonRegisterHist_NumCard'],
			'Lpu_iid' => $LastHist['Lpu_id'],
			'MedPersonal_iid' => $LastHist['MedPersonal_id'],
			'session' => $data['session'],
			'histCreated' => !empty($data['histCreated'])?$data['histCreated']:false,
			'autoExcept' => true,
		);

		$resp = $this->savePersonRegister($params);
		if (isset($resp[0]) && !empty($resp[0]['Alert_Msg'])) {
			return $this->createError('',$resp[0]['Alert_Msg']);
		}
		if (!isset($resp[0]) || empty($resp[0]['PersonRegister_id'])) {
			return $this->createError('','Ошибка при обновлении данных записи регистра');
		}
		return $resp;
	}

	/**
	 * Метод выгружает весь регистр ВЗН без фильтров в виде файла CSV
	 *
	 * @return bool | csv-file
	 */
	function downloadVznRegisterCsv()
	{
		$query = "
			SELECT
				PR.PersonRegister_id as RegNum,
				RTRIM(PS.Person_SurName)+isnull(' '+RTRIM(PS.Person_FirName),'')+isnull(' '+RTRIM(PS.Person_SecName),'') as FIO,
				
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				convert(varchar(10), PS.Person_deadDT, 104) as Person_Death,
				
				Lpu.Lpu_Nick as Lpu_Nick,
				Diag.Diag_SCode as Diag_SCode,
				Diag.diag_FullName as Diag_Name,
				LpuIns.Lpu_Nick as Lpu_insNick,
				
				convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
				convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate,
				
				PROUT.PersonRegisterOutCause_Name,
				ENR.EvnNotifyRegister_OutComment,
				LpuOut.Lpu_Nick as Lpu_delNick

					
			FROM		
				v_PersonState PS with (nolock)
						
			INNER JOIN
				v_PersonRegisterType PRT with (nolock) on PRT.PersonRegisterType_SysNick like 'nolos'
			INNER JOIN
				v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.PersonRegisterType_id = PRT.PersonRegisterType_id
			LEFT JOIN
				v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			LEFT JOIN
				v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			LEFT JOIN
				v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
			LEFT JOIN
				v_Lpu LpuIns with (nolock) on LpuIns.Lpu_id = pr.Lpu_iid
			LEFT JOIN
				v_Lpu LpuOut with (nolock) on LpuOut.Lpu_id = pr.Lpu_did
			
			LEFT JOIN
				v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id
			LEFT JOIN
				v_EvnNotifyRegister ENR with (nolock) on ( ENR.PersonRegister_id = PR.PersonRegister_id and ENR.EvnNotifyRegister_OutComment IS NOT NULL )
								
			ORDER BY 
				FIO
			";

		$resultObj = $this->db->query($query);


		if ( ! is_object($resultObj))
		{
			return false;
		}

		$result = $resultObj->result('array');



		// Заголовки для таблицы
		$csvHeaders = array(
			'Номер регистровой записи', 'ФИО', 'Дата рождения', 'Дата смерти', 'МО прикрепления',
			'Код МКБ-10', 'Диагноз МКБ -10', 'МО, подавшая извещение на включение в регистра', 'Дата включения в регистр',
			'Дата исключения из регистра', 'Причина исключения из регистра', 'Комментарий к исключению из регистра',
			'МО, подавшая извещение на исключение в регистра');

		$csvSeparator = ';'; // Разделитель ; чтобы файл открывался в Excel, запятая не будет работает
		$fileName = 'Регистр.csv';

		$report =  "\xEF\xBB\xBF"; // записываем в начало последовательность байтов UTF-8 BOM для корректного отображения в Excel
		$report .= implode($csvSeparator, $csvHeaders) . PHP_EOL;


		// Записываем значения ячеек с разделителем для формата csv
		foreach ($result as $arrayValues)
		{
			$report .= implode($csvSeparator, $arrayValues) . PHP_EOL;
		}


		$this->output->set_content_type('text/csv', 'utf-8')
			->set_header("Content-Disposition: attachment; filename=$fileName")
			->set_output($report); // Устанавливаем содержимое файла


		return true;
	}

	/**
	 * Простой вариант проверки наличия записи регистра
	 */
	function simpleCheckPersonRegisterExist($data) {
		
		return $this->queryResult("						
			select top 1 pr.PersonRegister_id 
			from v_PersonRegister pr (nolock)
			inner join v_PersonRegisterType prt (nolock) on prt.PersonRegisterType_id = pr.PersonRegisterType_id
			where 
				pr.Person_id = :Person_id and 
				prt.PersonRegisterType_Code = :PersonRegisterType_Code and
				pr.PersonRegister_setDate >= :PersonRegister_setDate and
				(pr.PersonRegister_disDate <= :PersonRegister_setDate or pr.PersonRegister_disDate is null)
		", $data);
	}
}