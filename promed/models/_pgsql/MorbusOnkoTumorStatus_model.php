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
 * Список состояний опухолевого процесса (мониторинг опухоли) (MorbusOnkoTumorStatus.MorbusOnkoBasePersonState_id = MorbusOnkoBasePersonState.MorbusOnkoBasePersonState_id)
 * MorbusOnkoBasePersonState has many MorbusOnkoTumorStatus 1:1..*
 * MorbusOnkoTumorStatus с атрибутами:
 *      MorbusOnkoTumorStatus_NumTumor - № опухоли
 *      Diag_id - Топография
 *      OnkoTumorStatusType_id - Состояние опухолевого процесса
 *
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 *
 */
class MorbusOnkoTumorStatus_model extends swPgModel
{
	/**
	 * Пользователь
	 * @var integer
	 */
	public $pmUser_id;

	/**
	 * Дата наблюдения общего состояния пациента
	 * @var string
	 */
	public $MorbusOnkoBasePersonState_setDT;

	/**
	 * Принадлежность общего состояния пациента к общему онкозаболеванию
	 * @var string
	 */
	public $MorbusOnkoBase_id;

	/**
	 * Список служебных параметров, которые должны быть получены из входящих параметров
	 * @var array
	 */
	protected $_params = array(
		'pmUser_id',
		'MorbusOnkoBase_id',
		'MorbusOnkoBasePersonState_setDT',
	);

	/**
	 * Primary key
	 * @var integer
	 * OnkoTumorStatus.OnkoTumorStatus_id
	 */
	protected $_MorbusOnkoTumorStatus_id;
	/**
	 * Принадлежность общему состоянию
	 * @var integer
	 */
	protected $_MorbusOnkoBasePersonState_id;
	/**
	 * Топография из Morbus.Diag_id
	 * @var integer
	 */
	protected $_Diag_id;
	/**
	 * Номер опухоли из MorbusOnko.MorbusOnko_NumTumor
	 * @var integer
	 * OnkoTumorStatus.OnkoTumorStatus_Num
	 */
	protected $_MorbusOnkoTumorStatus_NumTumor;
	/**
	 * состояние опухолевого процесса (перечисление OnkoTumorStatusType)
	 * @var integer
	 */
	protected $_OnkoTumorStatusType_id;

	/**
	 * Список атрибутов, которые могут быть записаны в модель и должны быть получены из входящих параметров
	 * @var array
	 */
	protected $_safeAttributes = array(
		'MorbusOnkoTumorStatus_id',
		'MorbusOnkoBasePersonState_id',
		'Diag_id',
		'MorbusOnkoTumorStatus_NumTumor',
		'OnkoTumorStatusType_id',
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
	 * create_list - Создание списка состояний
	 * read_list - Загрузка списка состояний
	 * create - Создание записи
	 * update - Обновление записи
	 * read - Загрузка данных одной записи по ключу
	 * destroy - Удаление записи из БД
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
	 * @param int $MorbusOnkoTumorStatus_id
	 */
	public function setId($MorbusOnkoTumorStatus_id)
	{
		$this->_MorbusOnkoTumorStatus_id = $MorbusOnkoTumorStatus_id;
	}

	/**
	 * Получение значения первичного ключа из модели
	 * @return int
	 */
	public function getId()
	{
		return $this->_MorbusOnkoTumorStatus_id;
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
		if (in_array($this->_scenario,array('update', 'destroy', 'read')) && empty($this->_MorbusOnkoTumorStatus_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан ключ записи';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->_Diag_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указана топография';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->_MorbusOnkoTumorStatus_NumTumor)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан номер опухоли';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->_OnkoTumorStatusType_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указано состояние опухолевого процесса';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create', 'read_list')) && empty($this->_MorbusOnkoBasePersonState_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указано общее состояние';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->pmUser_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан пользователь';
			return false;
		}
		if ($this->_scenario == 'create_list' && empty($this->MorbusOnkoBase_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указано общее заболевание';
			return false;
		}
		if ($this->_scenario == 'create_list' && empty($this->MorbusOnkoBasePersonState_setDT)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указана дата наблюдения';
			return false;
		}
		return true;
	}

	/**
	 * Создание списка состояний опухолевого процесса
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи setParams и/или setSafeAttributes
	 * @return array Стандартный ответ модели
	 */
	public function createList($data = array())
	{
		if (count($data) > 0) {
			$this->setParams($data);
			$this->setSafeAttributes($data);
		}

		$this->_scenario = 'create_list';
		if ( !$this->_validate() )
		{
			return array(array('Error_Code'=>$this->_errorCode, 'Error_Msg'=>$this->_errorMsg));
		}
		$result = $this->loadTumorList();
		if (empty($result))
		{
			return array(array('Error_Msg'=>'Не удалось получить список опухолей. Возможно, что у опухолей не указана дата установления диагноза.'));
		}

		$this->_scenario = 'create';
		foreach($result as $row) {
			$this->_Diag_id = $row['Diag_id'];
			$this->_MorbusOnkoTumorStatus_NumTumor = $row['MorbusOnko_NumTumor'];
			$this->_OnkoTumorStatusType_id = $row['OnkoTumorStatusType_id'];
			$res = $this->save();
			if (!empty($res[0]['Error_Msg'])) {
				return $res;
			}
		}
		return array(array(
			'MorbusOnkoBasePersonState_id'=>$this->_MorbusOnkoBasePersonState_id,
			'Error_Msg'=>null,
		));
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
				MOTS.OnkoTumorStatus_id as \"MorbusOnkoTumorStatus_id\",
				MOTS.MorbusOnkoBasePersonState_id as \"MorbusOnkoBasePersonState_id\",
				MOTS.OnkoTumorStatus_Num as \"MorbusOnkoTumorStatus_NumTumor\",
				MOTS.Diag_id as \"Diag_id\",
				MOTS.OnkoTumorStatusType_id as \"OnkoTumorStatusType_id\"
			FROM
				v_OnkoTumorStatus MOTS
			WHERE
				OnkoTumorStatus_id = ?
		";
		$result = $this->db->query($sql,  array($this->_MorbusOnkoTumorStatus_id));
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
			if (empty($this->_MorbusOnkoTumorStatus_id)) {
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

		$procedure = 'dbo.p_OnkoTumorStatus_upd';
		if (empty($this->_MorbusOnkoTumorStatus_id)) {
			$procedure = 'dbo.p_OnkoTumorStatus_ins';
		}

		$sql = "
		    select 
		        OnkoTumorStatus_id as \"MorbusOnkoTumorStatus_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			
			from {$procedure}(
				OnkoTumorStatus_id := :MorbusOnkoTumorStatus_id,
				MorbusOnkoBasePersonState_id := :MorbusOnkoBasePersonState_id,
				Diag_id := :Diag_id,
				OnkoTumorStatus_Num := :MorbusOnkoTumorStatus_NumTumor,
				OnkoTumorStatusType_id := :OnkoTumorStatusType_id,
				pmUser_id := :pmUser_id
			)
		";
		$params = array(
			'MorbusOnkoTumorStatus_id' => $this->_MorbusOnkoTumorStatus_id,
			'MorbusOnkoBasePersonState_id' => $this->_MorbusOnkoBasePersonState_id,
			'Diag_id' => $this->_Diag_id,
			'MorbusOnkoTumorStatus_NumTumor' => $this->_MorbusOnkoTumorStatus_NumTumor,
			'OnkoTumorStatusType_id' => $this->_OnkoTumorStatusType_id,
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
	 * Удаление
	 * @param int $MorbusOnkoBasePersonState_id
	 * @return bool
	 */
	public function destroyList($MorbusOnkoBasePersonState_id)
	{
		$query = "
			SELECT
				OnkoTumorStatus_id as \"MorbusOnkoTumorStatus_id\"
			FROM v_OnkoTumorStatus
			WHERE MorbusOnkoBasePersonState_id = :MorbusOnkoBasePersonState_id
		";
		$params = array(
			'MorbusOnkoBasePersonState_id' => $MorbusOnkoBasePersonState_id,
		);
		$res = $this->db->query($query, $params);
		if ( !is_object($res) )
		{
			log_message('error', var_export(array('query' => $query, 'params' => $params, 'error' => sqlsrv_errors()), true));
			return false;
		}
		$status_list = $res->result('array');
		$query = "
		    select 
		        Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_OnkoTumorStatus_del(
				OnkoTumorStatus_id := :MorbusOnkoTumorStatus_id
			)
		";
		foreach ($status_list as $row) {
			$params = array(
				'MorbusOnkoTumorStatus_id' => $row['MorbusOnkoTumorStatus_id'],
			);
			$res = $this->db->query($query, $params);
			if ( !is_object($res) ) {
				log_message('error', var_export(array('query' => $query, 'params' => $params, 'error' => sqlsrv_errors()), true));
				return false;
			}
			$res = $res->result('array');
			if ( !empty($res[0]['Error_Msg']) ) {
				log_message('error', $res[0]['Error_Msg']);
				return false;
			}
		}
		return true;
	}

	/**
	 * Метод получения списка опухолей на дату наблюдения.
	 * Получение данных только тех опухолей, для которых «Дата установления диагноза»
	 * меньше или равна значения поля «Дата наблюдения».
	 * @return array
	 */
	private function loadTumorList()
	{
		$query = "
			SELECT
				M.Diag_id as \"Diag_id\",
				MO.MorbusOnko_NumTumor as \"MorbusOnko_NumTumor\",
				COALESCE(MO.OnkoTumorStatusType_id, 10) as \"OnkoTumorStatusType_id\"
			FROM v_MorbusOnkoBase MOB 
				inner join v_Morbus M on MOB.MorbusBase_id = M.MorbusBase_id
				inner join v_MorbusOnko MO on M.Morbus_id = MO.Morbus_id
			WHERE
				MOB.MorbusOnkoBase_id = :MorbusOnkoBase_id
				and MO.MorbusOnko_setDiagDT <= :MorbusOnkoBasePersonState_setDT::timestamp(3)
		";
		$params = array(
			'MorbusOnkoBase_id' => $this->MorbusOnkoBase_id,
			'MorbusOnkoBasePersonState_setDT' => $this->MorbusOnkoBasePersonState_setDT,
		);
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			log_message('error', var_export(array('query' => $query, 'params' => $params, 'error' => sqlsrv_errors()), true));
			return array();
		}
	}

	/**
	 * Метод получения списка состояний
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи setParams
	 * @return array Стандартный ответ модели
	 */
	function readList($data = array())
	{
		if (count($data) > 0) {
			$this->setSafeAttributes($data);
		}
		$this->_scenario = 'read_list';
		if ( !$this->_validate() )
		{
			return false;
		}
		$query = "
			SELECT
				MOTS.OnkoTumorStatus_id as \"MorbusOnkoTumorStatus_id\",
				MOTS.MorbusOnkoBasePersonState_id as \"MorbusOnkoBasePersonState_id\",
				MOTS.OnkoTumorStatus_Num as \"MorbusOnkoTumorStatus_NumTumor\",
				MOTS.Diag_id as \"Diag_id\",
				MOTS.OnkoTumorStatusType_id as \"OnkoTumorStatusType_id\",
				Diag.Diag_FullName as \"Diag_Name\",
				OTST.OnkoTumorStatusType_Name as \"OnkoTumorStatusType_Name\"
			FROM v_OnkoTumorStatus MOTS
				inner join v_Diag Diag on MOTS.Diag_id = Diag.Diag_id
				left join v_OnkoTumorStatusType OTST on MOTS.OnkoTumorStatusType_id = OTST.OnkoTumorStatusType_id
			WHERE MOTS.MorbusOnkoBasePersonState_id = :MorbusOnkoBasePersonState_id
		";
		$params = array(
			'MorbusOnkoBasePersonState_id' => $this->_MorbusOnkoBasePersonState_id,
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
	 * Загрузка справочника TumorStage
	 * @param $data
	 * @return array|false
	 */
	function loadTumorStageList($data)
	{
		$filters = array('1=1');

		switch($data['mode'])
		{
			// case 0 - все записи
			case 1: // только свой регион
				$filters[] = 'Region_id = dbo.GetRegion()';
				break;
			case 2: // только null
				$filters[] = 'Region_id is null';
				break;
		}

		$filter = implode(' AND ', $filters);

		$query = "
			SELECT
				TumorStage_id as \"TumorStage_id\",
				TumorStage_Code as \"TumorStage_Code\",
				TumorStage_Name as \"TumorStage_Name\",
				Region_id as \"Region_id\",
				KLCountry_id as \"KLCountry_id\"
      		FROM
      			dbo.v_TumorStage
      		WHERE
      			{$filter}
		";

		$result = $this->queryResult($query);

		return $result;
	}

}