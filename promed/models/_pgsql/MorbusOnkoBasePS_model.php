<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 */

/**
 * Сведения о госпитализациях
 *
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 *
 */
class MorbusOnkoBasePS_model extends swPgModel
{
	/**
	 * Пользователь
	 * @var integer
	 */
	public $pmUser_id;

	/**
	 * Простое заболевание в рамках, которого просматривается запись регистра в форме просмотра ЭМК
	 * или в форме просмотра регистра по онкологии
	 * @var integer
	 */
	public $Morbus_id;

	/**
	 * Учетный документ в рамках, которого просматривается запись регистра в форме просмотра ЭМК
	 * или идентификатор человека, если запись регистра просматривается
	 * в форме просмотра регистра по онкологии (не в ЭМК, вне контекста учетного документа)
	 * @var integer
	 */
	public $Evn_id;

	/**
	 * Список служебных параметров, которые должны быть получены из входящих параметров
	 * @var array
	 */
	protected $_params = array(
		'pmUser_id',
		'Morbus_id',
		'Evn_id',
	);

	/**
	 * Primary key
	 * @var integer
	 */
	protected $_MorbusOnkoBasePS_id;
	/**
	 * Принадлежность общему онкозаболеванию
	 * @var integer
	 */
	protected $_MorbusOnkoBase_id;
	/**
	 * Дата поступления
	 * @var datetime
	 */
	protected $_MorbusOnkoBasePS_setDT;
	/**
	 * Дата выписки
	 * @var datetime
	 */
	protected $_MorbusOnkoBasePS_disDT;
	/**
	 * цель госпитализации	(перечисление OnkoPurposeHospType)
	 * @var integer
	 */
	protected $_OnkoPurposeHospType_id;
	/**
	 * тип госпитализации: Первичная/повторная (перечисление OnkoHospType)
	 * @var integer
	 */
	protected $_OnkoHospType_id;
	/**
	 * Диагноз (справочник МКБ-10)
	 * @var integer
	 */
	protected $_Diag_id;
	/**
	 * место проведения (справочник Lpu)
	 * @var integer
	 */
	protected $_Lpu_id;
	/**
	 * отделение (справочник LpuSection)
	 * @var integer
	 */
	protected $_LpuSection_id;
	/**
	 * Проведено специальное лечение: Обследование, лечение отстрочено
	 * @var integer
	 */
	protected $_MorbusOnkoBasePS_IsTreatDelay;
	/**
	 * Проведено специальное лечение: Обследование, лечение не предусмотрено
	 * @var integer
	 */
	protected $_MorbusOnkoBasePS_IsNotTreat;
	/**
	 * Проведено специальное лечение: Лучевая терапия
	 * @var integer
	 */
	protected $_MorbusOnkoBasePS_IsBeam;
	/**
	 * Проведено специальное лечение: Химиотерапия
	 * @var integer
	 */
	protected $_MorbusOnkoBasePS_IsChem;
	/**
	 * Проведено специальное лечение: Гормонотерапия
	 * @var integer
	 */
	protected $_MorbusOnkoBasePS_IsGormun;
	/**
	 * Проведено специальное лечение: Иммунотерапия
	 * @var integer
	 */
	protected $_MorbusOnkoBasePS_IsImmun;
	/**
	 * Проведено специальное лечение: Хирургическое лечение при госпитализации
	 * @var integer
	 */
	protected $_MorbusOnkoBasePS_IsSurg;
	/**
	 * Проведено специальное лечение: Хирургическое лечение при госпитализации: Предоперационная лучевая терапия
	 * @var integer
	 */
	protected $_MorbusOnkoBasePS_IsPreOper;
	/**
	 * Проведено специальное лечение: Хирургическое лечение при госпитализации: Интраоперационная лучевая терапия
	 * @var integer
	 */
	protected $_MorbusOnkoBasePS_IsIntraOper;
	/**
	 * Проведено специальное лечение: Хирургическое лечение при госпитализации: Послеоперационная лучевая терапия
	 * @var integer
	 */
	protected $_MorbusOnkoBasePS_IsPostOper;
	/**
	 * Проведено специальное лечение: Другое
	 * @var integer
	 */
	protected $_MorbusOnkoBasePS_IsOther;
	/**
	 * состояние при выписке (перечисление OnkoLeaveType)
	 * @var integer
	 */
	protected $_OnkoLeaveType_id;
	/**
	 * случай госпитализции (учетный документ EvnPS)
	 * @var integer
	 */
	protected $_Evn_id;

	/**
	 * Список атрибутов, которые могут быть записаны в модель и должны быть получены из входящих параметров
	 * @var array
	 */
	protected $_safeAttributes = array(
		'MorbusOnkoBasePS_id',
		'MorbusOnkoBase_id',
		'MorbusOnkoBasePS_setDT',
		'MorbusOnkoBasePS_disDT',
		'OnkoPurposeHospType_id',
		'OnkoHospType_id',
		'Lpu_id',
		'LpuSection_id',
		'MorbusOnkoBasePS_IsTreatDelay',
		'MorbusOnkoBasePS_IsNotTreat',
		'MorbusOnkoBasePS_IsBeam',
		'MorbusOnkoBasePS_IsChem',
		'MorbusOnkoBasePS_IsGormun',
		'MorbusOnkoBasePS_IsImmun',
		'MorbusOnkoBasePS_IsPreOper',
		'MorbusOnkoBasePS_IsIntraOper',
		'MorbusOnkoBasePS_IsPostOper',
		'MorbusOnkoBasePS_IsOther',
		'OnkoLeaveType_id',
		'Evn_id',
		'Diag_id',
		'MorbusOnkoBasePS_IsSurg',
	);

	/**
	 * Текст ошибки
	 * @var string
	 */
	protected $_errorMsg;
	/**
	 * Код ошибки
	 * @var integer
	 */
	protected $_errorCode;
	/**
	 * Имя сценария, определяющего правила валидации модели
	 *
	 * Возможные сценарии:
	 * create - Создание записи
	 * update - Обновление записи
	 * read - Загрузка данных одной записи по ключу
	 * destroy - Удаление записи из БД
	 * view_data - Загрузка данных для формы просмотра
	 *
	 * @var string
	 */
	protected $_scenario;

	/**
	 * Конструктор
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Запись значения первичного ключа в модель
	 * @param int $MorbusOnkoBasePS_id
	 */
	public function setId($MorbusOnkoBasePS_id)
	{
		$this->_MorbusOnkoBasePS_id = $MorbusOnkoBasePS_id;
	}

	/**
	 * Получение значения первичного ключа из модели
	 * @return int
	 */
	public function getId()
	{
		return $this->_MorbusOnkoBasePS_id;
	}

	/**
	 * Извлечение значений атрибутов модели из входящих параметров
	 * @param array $data
	 * @return void
	 */
	public function setSafeAttributes($data)
	{
		foreach ($this->_safeAttributes as $key) {
			$property = '_'.$key;
			if (property_exists($this, $property) && array_key_exists($key, $data)) {
				$this->{$property} = $data[$key];
			}
		}
	}

	/**
	 * Извлечение значений служебных параметров модели из входящих параметров
	 * @param array $data
	 * @return void
	 */
	public function setParams($data)
	{
		foreach ($this->_params as $key) {
			if (property_exists($this, $key) && array_key_exists($key, $data)) {
				$this->{$key} = $data[$key];
			}
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @return boolean
	 */
	protected function _validate()
	{
		if (empty($this->_scenario)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан сценарий';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'destroy', 'read')) && empty($this->_MorbusOnkoBasePS_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан ключ записи';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->_MorbusOnkoBasePS_setDT)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указана дата поступления';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->_OnkoPurposeHospType_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указана цель госпитализации';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->pmUser_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан пользователь';
			return false;
		}
		if ($this->_scenario == 'view_data' && empty($this->Morbus_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан объект просмотра';
			return false;
		}
		if ($this->_scenario == 'view_data' && empty($this->Evn_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан контекст просмотра';
			return false;
		}
		return true;
	}

	/**
	 * Получение данных для редактирования
	 * Параметры должны быть установлены в контроллере
	 * @return array Стандартный ответ модели
	 */
	public function read()
	{
		$this->_scenario = 'read';
		if ( !$this->_validate() )
		{
			return array(array('Error_Code'=>$this->_errorCode, 'Error_Msg'=>$this->_errorMsg));
		}
		$sql = "
				SELECT
					MorbusOnkoBasePS_id as \"MorbusOnkoBasePS_id\",
					MorbusOnkoBase_id as \"MorbusOnkoBase_id\",
					OnkoPurposeHospType_id as \"OnkoPurposeHospType_id\",
					to_char(MorbusOnkoBasePS_setDT, 'dd.mm.yyyy') as \"MorbusOnkoBasePS_setDT\",
					to_char(MorbusOnkoBasePS_disDT, 'dd.mm.yyyy') as \"MorbusOnkoBasePS_disDT\",
				    OnkoHospType_id as \"OnkoHospType_id\",
				    Diag_id as \"Diag_id\",
				    Lpu_id as \"Lpu_id\",
					LpuSection_id as \"LpuSection_id\",
					MorbusOnkoBasePS_IsTreatDelay as \"MorbusOnkoBasePS_IsTreatDelay\",
					MorbusOnkoBasePS_IsNotTreat as \"MorbusOnkoBasePS_IsNotTreat\",
					MorbusOnkoBasePS_IsBeam as \"MorbusOnkoBasePS_IsBeam\",
					MorbusOnkoBasePS_IsChem as \"MorbusOnkoBasePS_IsChem\",
					MorbusOnkoBasePS_IsGormun as \"MorbusOnkoBasePS_IsGormun\",
					MorbusOnkoBasePS_IsImmun as \"MorbusOnkoBasePS_IsImmun\",
					MorbusOnkoBasePS_IsSurg as \"MorbusOnkoBasePS_IsSurg\",
					MorbusOnkoBasePS_IsPreOper as \"MorbusOnkoBasePS_IsPreOper\",
					MorbusOnkoBasePS_IsIntraOper as \"MorbusOnkoBasePS_IsIntraOper\",
					MorbusOnkoBasePS_IsPostOper as \"MorbusOnkoBasePS_IsPostOper\",
					MorbusOnkoBasePS_IsOther as \"MorbusOnkoBasePS_IsOther\",
					OnkoLeaveType_id as \"OnkoLeaveType_id\",
					Evn_id as \"Evn_id\"
				FROM
					dbo.v_MorbusOnkoBasePS
				WHERE
					MorbusOnkoBasePS_id = ?
			";
		$result = $this->db->query($sql,  array($this->_MorbusOnkoBasePS_id));
		if ( is_object($result) )
			return $result->result('array');
		else
			return array(array('Error_Msg'=>'Ошибка БД, не удалось получить запись'));
	}

	/**
	 * Логика перед сохранением, включающая в себя проверку данных
	 */
	protected function _beforeSave($data = array())
	{
		if (empty($this->_scenario)) {
			$this->_scenario = 'update';
			if (empty($this->_MorbusOnkoBasePS_id)) {
				$this->_scenario = 'create';
			}
		}
		return $this->_validate();
	}

	/**
	 * Сохранение данных
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи setParams и/или setSafeAttributes
	 * @return array Стандартный ответ модели
	 */
	public function save($data = array())
	{
		if (count($data) > 0) {
			$this->setParams($data);
			$this->setSafeAttributes($data);
		}

		if (!$this->_beforeSave())
		{
			return array(array('Error_Code'=>$this->_errorCode, 'Error_Msg'=>$this->_errorMsg));
		}

		$procedure = 'dbo.p_MorbusOnkoBasePS_upd';
		if (empty($this->_MorbusOnkoBasePS_id)) {
			$procedure = 'dbo.p_MorbusOnkoBasePS_ins';
		}

		$sql = "
			select 
			    MorbusOnkoBasePS_id as \"MorbusOnkoBasePS_id\", 
			    Error_Code as \"Error_Code\", 
			    Error_Message as \"Error_Msg\"
			from {$procedure} (
				MorbusOnkoBasePS_id := :MorbusOnkoBasePS_id,
				MorbusOnkoBase_id := :MorbusOnkoBase_id,
				MorbusOnkoBasePS_setDT := :MorbusOnkoBasePS_setDT,
				MorbusOnkoBasePS_disDT := :MorbusOnkoBasePS_disDT,
				Lpu_id := :Lpu_id,
				LpuSection_id := :LpuSection_id,
				OnkoPurposeHospType_id := :OnkoPurposeHospType_id,
				OnkoHospType_id := :OnkoHospType_id,
				MorbusOnkoBasePS_IsBeam := :MorbusOnkoBasePS_IsBeam,
				MorbusOnkoBasePS_IsChem := :MorbusOnkoBasePS_IsChem,
				MorbusOnkoBasePS_IsGormun := :MorbusOnkoBasePS_IsGormun,
				MorbusOnkoBasePS_IsImmun := :MorbusOnkoBasePS_IsImmun,
				MorbusOnkoBasePS_IsNotTreat := :MorbusOnkoBasePS_IsNotTreat,
				MorbusOnkoBasePS_IsTreatDelay := :MorbusOnkoBasePS_IsTreatDelay,
				MorbusOnkoBasePS_IsPreOper := :MorbusOnkoBasePS_IsPreOper,
				MorbusOnkoBasePS_IsIntraOper := :MorbusOnkoBasePS_IsIntraOper,
				MorbusOnkoBasePS_IsPostOper := :MorbusOnkoBasePS_IsPostOper,
				MorbusOnkoBasePS_IsOther := :MorbusOnkoBasePS_IsOther,
				OnkoLeaveType_id := :OnkoLeaveType_id,
				Evn_id := :Evn_id,
				Diag_id := :Diag_id,
				MorbusOnkoBasePS_IsSurg := :MorbusOnkoBasePS_IsSurg,
				pmUser_id := :pmUser_id
			)
		";
		$params = array(
			'MorbusOnkoBasePS_id' => $this->_MorbusOnkoBasePS_id,
			'MorbusOnkoBase_id' => $this->_MorbusOnkoBase_id,
			'MorbusOnkoBasePS_setDT' => $this->_MorbusOnkoBasePS_setDT,
			'MorbusOnkoBasePS_disDT' => $this->_MorbusOnkoBasePS_disDT,
			'Lpu_id' => $this->_Lpu_id,
			'LpuSection_id' => $this->_LpuSection_id,
			'OnkoPurposeHospType_id' => $this->_OnkoPurposeHospType_id,
			'OnkoHospType_id' => $this->_OnkoHospType_id,
			'MorbusOnkoBasePS_IsBeam' => $this->_MorbusOnkoBasePS_IsBeam,
			'MorbusOnkoBasePS_IsChem' => $this->_MorbusOnkoBasePS_IsChem,
			'MorbusOnkoBasePS_IsGormun' => $this->_MorbusOnkoBasePS_IsGormun,
			'MorbusOnkoBasePS_IsImmun' => $this->_MorbusOnkoBasePS_IsImmun,
			'MorbusOnkoBasePS_IsNotTreat' => $this->_MorbusOnkoBasePS_IsNotTreat,
			'MorbusOnkoBasePS_IsTreatDelay' => $this->_MorbusOnkoBasePS_IsTreatDelay,
			'MorbusOnkoBasePS_IsPreOper' => $this->_MorbusOnkoBasePS_IsPreOper,
			'MorbusOnkoBasePS_IsIntraOper' => $this->_MorbusOnkoBasePS_IsIntraOper,
			'MorbusOnkoBasePS_IsPostOper' => $this->_MorbusOnkoBasePS_IsPostOper,
			'MorbusOnkoBasePS_IsOther' => $this->_MorbusOnkoBasePS_IsOther,
			'OnkoLeaveType_id' => $this->_OnkoLeaveType_id,
			'Evn_id' => $this->_Evn_id,
			'Diag_id' => $this->_Diag_id,
			'MorbusOnkoBasePS_IsSurg' => $this->_MorbusOnkoBasePS_IsSurg,
			'pmUser_id' => $this->pmUser_id,
		);
		$res = $this->db->query($sql, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			log_message('error', var_export(array('query' => $sql, 'params' => $params, 'error' => sqlsrv_errors()), true));
			return array(array('Error_Code'=>500, 'Error_Msg'=>'Ошибка запроса сохранения записи!'));
		}
	}

	/**
	 * Логика перед удалением, может включать в себя проверки данных
	 * @return boolean
	 */
	protected function _beforeDestroy()
	{
		$this->_scenario = 'destroy';
		return $this->_validate();
	}

	/**
	 * Удаление шаблона
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи setSafeAttributes
	 * @return array Стандартный ответ модели
	 */
	public function destroy($data = array())
	{
		if (count($data) > 0) {
			$this->setSafeAttributes($data);
		}
		if (!$this->_beforeDestroy())
		{
			return array(array('Error_Code'=>$this->_errorCode, 'Error_Msg'=>$this->_errorMsg));
		}
		$sql = '
		        select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Message\"
				from dbo.p_MorbusOnkoBasePS_del (
					MorbusOnkoBasePS_id := :MorbusOnkoBasePS_id
				)
			';
		$params = array(
			'MorbusOnkoBasePS_id' => $this->_MorbusOnkoBasePS_id,
		);
		$res = $this->db->query($sql, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			log_message('error', var_export(array('query' => $sql, 'params' => $params, 'error' => sqlsrv_errors()), true));
			return array(array('Error_Code'=>500, 'Error_Msg'=>'Ошибка запроса удаления записи!'));
		}
	}

	/**
	 * Метод получения данных подразделa «Госпитализация» формы просмотра записи регистра
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи setParams
	 * @return array Стандартный ответ модели
	 */
	function getViewData($data = array())
	{
		if (count($data) > 0) {
			$this->setParams($data);
		}
		$this->_scenario = 'view_data';
		if ( !$this->_validate() )
		{
			return false;
		}
		$query = "
			SELECT
				'edit' as \"accessType\",
				MOBPS.MorbusOnkoBasePS_id as \"MorbusOnkoBasePS_id\",
				MOBPS.MorbusOnkoBase_id as \"MorbusOnkoBase_id\",
				to_char(MOBPS.MorbusOnkoBasePS_setDT, 'dd.mm.yyyy') as \"MorbusOnkoBasePS_setDT\",
				to_char(MOBPS.MorbusOnkoBasePS_disDT, 'dd.mm.yyyy') as \"MorbusOnkoBasePS_disDT\",
				OPST.OnkoPurposeHospType_id as \"OnkoPurposeHospType_id\",
				OPST.OnkoPurposeHospType_Name as \"OnkoPurposeHospType_id_Name\"
				,:Evn_id as \"MorbusOnko_pid\"
				,Morbus.Morbus_id as \"Morbus_id\"
			FROM
				dbo.v_Morbus Morbus
				INNER JOIN dbo.v_MorbusOnkoBase MOB on Morbus.MorbusBase_id = MOB.MorbusBase_id
				INNER JOIN dbo.v_MorbusOnkoBasePS MOBPS on MOB.MorbusOnkoBase_id = MOBPS.MorbusOnkoBase_id
				INNER JOIN dbo.v_OnkoPurposeHospType OPST ON MOBPS.OnkoPurposeHospType_id = OPST.OnkoPurposeHospType_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and Morbus.Person_id != :Evn_id
			where
				Morbus.Morbus_id = :Morbus_id
		";
		$params = array(
			'Morbus_id' => $this->Morbus_id,
			'Evn_id' => $this->Evn_id,
		);
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			log_message('error', var_export(array('query' => $query, 'params' => $params, 'error' => sqlsrv_errors()), true));
			return false;
		}
	}

	/**
	 * Метод получения сопутствующих заболеваний
	 * @return array Стандартный ответ модели
	 */
	function getMorbusOnkoSopDiagData($data = array())
	{
		if (count($data) > 0) {
			$this->setParams($data);
		}
		$this->_scenario = 'view_data';
		if ( !$this->_validate() )
		{
			return false;
		}
		$query = "
			SELECT
				case when
					(
						(MOBPS.Evn_id is null and EvnEdit.Evn_id is null)
						or (MOBPS.Evn_id = EvnEdit.Evn_id and COALESCE(EvnEdit.Evn_IsSigned,1) = 1)
					) and Morbus.Morbus_disDT is null
					then 'edit'
					else 'view'
				end as \"accessType\",
				MOBPS.MorbusOnkoBasePS_id as \"MorbusOnkoBasePS_id\",
				MOBPS.MorbusOnkoBase_id as \"MorbusOnkoBase_id\",
				rtrim(d.Diag_Code ||' '|| d.Diag_Name) as \"SopDiag_Name\"
				,:Evn_id as \"MorbusOnko_pid\"
				,Morbus.Morbus_id as \"Morbus_id\"
			FROM
				dbo.v_Morbus Morbus
				INNER JOIN dbo.v_MorbusOnkoBase MOB on Morbus.MorbusBase_id = MOB.MorbusBase_id
				INNER JOIN dbo.v_MorbusOnkoBasePS MOBPS on MOB.MorbusOnkoBase_id = MOBPS.MorbusOnkoBase_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and Morbus.Person_id != :Evn_id
				INNER JOIN dbo.v_MorbusOnkoBaseDiagLink mobdl on MOB.MorbusOnkoBase_id = mobdl.MorbusOnkoBase_id
				left join v_Diag d d.Diag_id = mobdl.Diag_id
			where
				Morbus.Morbus_id = :Morbus_id
		";
		$params = array(
			'Morbus_id' => $this->Morbus_id,
			'Evn_id' => $this->Evn_id,
		);
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			log_message('error', var_export(array('query' => $query, 'params' => $params, 'error' => sqlsrv_errors()), true));
			return false;
		}
	}

	function getHosp($data) {
		$query = "
			select MB.MorbusBase_id
			from v_MorbusBase MB
			join v_MorbusOnkoBase MOB on MOB.MorbusBase_id = MB.MorbusBase_id
			join v_MorbusOnkoBasePS MOBPS on MOBPS.MorbusOnkoBase_id = MOB.MorbusOnkoBase_id 
			where MB.Person_id= :Person_id
			and (MOBPS.MorbusOnkoBasePS_setDT <= :EvnSection_setDate and MOBPS.MorbusOnkoBasePS_disDT >= :EvnSection_disDate)
			and (MOBPS.LpuSection_id is null or MOBPS.LpuSection_id = :LpuSection_id) limit 1
		";
		return $this->getFirstRowFromQuery($query, $data);
	}
	function getMorbusBaseData($data) {
		$query = "
			select MOB.MorbusOnkoBase_id as \"MorbusOnkoBase_id\",
			MO.OnkoTreatment_id as \"OnkoTreatment_id\"
			 from v_MorbusBase MB
			 join v_MorbusOnkoBase MOB on MOB.MorbusBase_id = MB.MorbusBase_id
			 join v_MorbusOnko MO on MO.MorbusBase_id = MOB.MorbusBase_id 
			 where MB.Evn_pid in (:EvnSection_id, :EvnSection_pid)
			 limit 1
		 ";
		return $this->getFirstRowFromQuery($query, $data);
	}
}