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
 * Общее состояние пациента
 *
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 *
 * Список состояний опухолевого процесса (мониторинг опухоли) (MorbusOnkoTumorStatus.MorbusOnkoBasePersonState_id = MorbusOnkoBasePersonState.MorbusOnkoBasePersonState_id)
 * MorbusOnkoBasePersonState has many MorbusOnkoTumorStatus 1:1..*
 * @property MorbusOnkoTumorStatus_model MorbusOnkoTumorStatus Список состояний опухолевого процесса (мониторинг опухоли)
 */
class MorbusOnkoBasePersonState_model extends swModel
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
	protected $_MorbusOnkoBasePersonState_id;
	/**
	 * Дата наблюдения
	 * @var datetime
	 */
	protected $_MorbusOnkoBasePersonState_setDT;
	/**
	 * Принадлежность общему онкозаболеванию
	 * @var integer
	 */
	protected $_MorbusOnkoBase_id;
	/**
	 * Общее состояние пациента (перечисление OnkoPersonStateType)
	 * @var integer
	 */
	protected $_OnkoPersonStateType_id;

	/**
	 * Список атрибутов, которые могут быть записаны в модель и должны быть получены из входящих параметров
	 * @var array
	 */
	protected $_safeAttributes = array(
		'MorbusOnkoBasePersonState_id',
		'MorbusOnkoBase_id',
		'MorbusOnkoBasePersonState_setDT',
		'OnkoPersonStateType_id',
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
	 * @param int $MorbusOnkoBasePersonState_id
	 */
	public function setId($MorbusOnkoBasePersonState_id)
	{
		$this->_MorbusOnkoBasePersonState_id = $MorbusOnkoBasePersonState_id;
	}

	/**
	 * Получение значения первичного ключа из модели
	 * @return int
	 */
	public function getId()
	{
		return $this->_MorbusOnkoBasePersonState_id;
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
		if (in_array($this->_scenario,array('update', 'destroy', 'read')) && empty($this->_MorbusOnkoBasePersonState_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан ключ записи';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->_MorbusOnkoBasePersonState_setDT)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указана дата наблюдения';
			return false;
		}
		if (in_array($this->_scenario,array('update')) && empty($this->_OnkoPersonStateType_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указано общее состояние пациента';
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
	 * Получение данных шаблона для редактирования
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
				MorbusOnkoBasePersonState_id,
				MorbusOnkoBase_id,
				CONVERT(varchar(10), MorbusOnkoBasePersonState_setDT, 104) as MorbusOnkoBasePersonState_setDT,
				OnkoPersonStateType_id
			FROM
				dbo.v_MorbusOnkoBasePersonState with (nolock)
			WHERE
				MorbusOnkoBasePersonState_id = ?
		";
		$result = $this->db->query($sql,  array($this->_MorbusOnkoBasePersonState_id));
		if ( is_object($result) )
			return $result->result('array');
		else
			return array(array('Error_Msg'=>'Ошибка БД, не удалось получить запись'));
	}

	/**
	 * Логика перед сохранением, включающая в себя проверку данных
	 * @return boolean
	 */
	protected function _beforeSave($data = array())
	{
		if (empty($this->_scenario)) {
			$this->_scenario = 'update';
			if (empty($this->_MorbusOnkoBasePersonState_id)) {
				$this->_scenario = 'create';
			}
		}
		return $this->_validate();
	}

	/**
	 * Создание записи со списком состояний опухолевого процесса
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи setParams и/или setSafeAttributes
	 * @return array Стандартный ответ модели
	 */
	public function create($data = array())
	{
		if (count($data) > 0) {
			$this->setParams($data);
			$this->setSafeAttributes($data);
		}
		$result = $this->save();
		if (!empty($result[0]['Error_Msg'])) {
			return $result;
		}
		$data['MorbusOnkoBasePersonState_id'] = $result[0]['MorbusOnkoBasePersonState_id'];
		$this->load->model('MorbusOnkoTumorStatus_model', 'MorbusOnkoTumorStatus');
		return $this->MorbusOnkoTumorStatus->createList($data);
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

		$procedure = 'dbo.p_MorbusOnkoBasePersonState_upd';
		if (empty($this->_MorbusOnkoBasePersonState_id)) {
			$procedure = 'dbo.p_MorbusOnkoBasePersonState_ins';
		}

		$sql = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMsg varchar(4000);
			set @Res = :MorbusOnkoBasePersonState_id;
			exec {$procedure}
				@MorbusOnkoBasePersonState_id = @Res output,
				@MorbusOnkoBase_id = :MorbusOnkoBase_id,
				@MorbusOnkoBasePersonState_setDT = :MorbusOnkoBasePersonState_setDT,
				@OnkoPersonStateType_id = :OnkoPersonStateType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as MorbusOnkoBasePersonState_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		$params = array(
			'MorbusOnkoBasePersonState_id' => $this->_MorbusOnkoBasePersonState_id,
			'MorbusOnkoBase_id' => $this->_MorbusOnkoBase_id,
			'MorbusOnkoBasePersonState_setDT' => $this->_MorbusOnkoBasePersonState_setDT,
			'OnkoPersonStateType_id' => $this->_OnkoPersonStateType_id,
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
		if (!$this->_validate()) {
			return false;
		}
		$this->load->model('MorbusOnkoTumorStatus_model', 'MorbusOnkoTumorStatus');
		if (!$this->MorbusOnkoTumorStatus->destroyList($this->_MorbusOnkoBasePersonState_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не удалось удалить список состояний опухолевых процессов';
			return false;
		}
		return true;
	}

	/**
	 * Удаление
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
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec dbo.p_MorbusOnkoBasePersonState_del
				@MorbusOnkoBasePersonState_id = :MorbusOnkoBasePersonState_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		';
		$params = array(
			'MorbusOnkoBasePersonState_id' => $this->_MorbusOnkoBasePersonState_id,
			'pmUser_id' => $this->pmUser_id
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
	 * Метод получения данных подразделa «Общее состояние пациента» формы просмотра записи регистра
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
				case when Morbus.Morbus_disDT is null then 'edit' else 'view' end as accessType,
				MOBPS.MorbusOnkoBasePersonState_id,
				MOBPS.MorbusOnkoBase_id,
				convert(varchar(10), MOBPS.MorbusOnkoBasePersonState_setDT, 104) as MorbusOnkoBasePersonState_setDT,
				OPST.OnkoPersonStateType_id,
				OPST.OnkoPersonStateType_Name OnkoPersonStateType_id_Name
				,:Evn_id as MorbusOnko_pid
				,Morbus.Morbus_id
			FROM
				dbo.v_Morbus Morbus WITH (NOLOCK)
				INNER JOIN dbo.v_MorbusOnkoBase MOB WITH (NOLOCK) on Morbus.MorbusBase_id = MOB.MorbusBase_id
				INNER JOIN dbo.v_MorbusOnkoBasePersonState MOBPS WITH (NOLOCK) on MOB.MorbusOnkoBase_id = MOBPS.MorbusOnkoBase_id
				INNER JOIN dbo.v_OnkoPersonStateType OPST WITH (NOLOCK) ON MOBPS.OnkoPersonStateType_id = OPST.OnkoPersonStateType_id
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

}