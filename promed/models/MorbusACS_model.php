<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * MorbusOrphan_model - модель для MorbusOrphan
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       Пермяков Александр
 * @version      10.2012
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
 */
class MorbusACS_model extends swModel
{
	private $entityFields = array(
		'MorbusOrphan' => array(
			'Morbus_id'
			, 'Lpu_id'
		),
		'Morbus' => array(//allow Deleted
			'MorbusBase_id'
			, 'Evn_pid' //Учетный документ, в рамках которого добавлено заболевание
			, 'Diag_id'
			, 'MorbusKind_id'
			, 'Morbus_Name'
			, 'Morbus_Nick'
			, 'Morbus_disDT'
			, 'Morbus_setDT'
			, 'MorbusResult_id'
		),
		'MorbusBase' => array(//allow Deleted
			'Person_id'
			, 'Evn_pid'
			, 'MorbusType_id'
			, 'MorbusBase_setDT'
			, 'MorbusBase_disDT'
			, 'MorbusResult_id'
		)
	);

	protected $_MorbusType_id = null;//19,31

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'acs';
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusTypeId()
	{
		if (empty($this->_MorbusType_id)) {
			$this->load->library('swMorbus');
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->getMorbusTypeSysNick());
			if (empty($this->_MorbusType_id)) {
				throw new Exception('Не удалось определить тип заболевания', 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * @return string
	 */
	function getGroupRegistry()
	{
		return null;
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusACS';
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function loadACSGrid($data) {
		$params = array();
		$filter = "";
		if (isset($data["Person_id"])) {
			$filter = " and MB.Person_id = :Person_id";
			$params["Person_id"] = $data["Person_id"];
		}
		$query = "
		select
			MA.MorbusACS_id,
			MA.Morbus_id,
			CONVERT(varchar(10),MA.Morbus_setDT,104) as Morbus_setDT,
			CONVERT(varchar(10),MA.Morbus_disDT,104) as Morbus_disDT,
			diag.Diag_Name,
			MA.MorbusACS_Result
		from 
			v_MorbusACS MA with(nolock)
			left join v_MorbusBase MB with(nolock) on MA.MorbusBase_id = MB.MorbusBase_id
			left join v_Diag diag with(nolock) on MA.Diag_id = diag.Diag_id
		where 
			(1=1) " . $filter;

		//echo getDebugSQL($query, $params);exit();
		$result = $this->db->query($query, $params);
		$response = $result->result('array');
		if (is_array($response) && count($response) > 0) {
			return $response;
		}
		return array();
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function loadMorbusACSEditWindow($data) {
		$params = array();
		$filter = "";
		if (isset($data["MorbusACS_id"])) {
			$filter = " and MorbusACS_id = :MorbusACS_id";
			$params["MorbusACS_id"] = $data["MorbusACS_id"];
		} else {
			return false;
		}
		$query = "
		select top 1
			convert(varchar(10),Morbus_setDT,104) as Morbus_setDT,
			convert(varchar(10),Morbus_disDT,104) as Morbus_disDT,
			MorbusACS_TimeDesease,
			MorbusACS_isST,
			PrehospArrive_id,
			MorbusACS_isTrombPrehosp,
			MorbusACS_isTrombStac,
			Diag_id,
			Diag_did,
			MorbusACS_isCoronary,
			MorbusACS_isTransderm,
			MorbusACS_Result,
			MorbusACS_isPso,
			MorbusACS_isLpu,
			MorbusACS_isTinaki,
			MorbusACS_isFCSSH,
			MorbusACS_Comment,
			Morbus_id
		from 
			v_MorbusACS with(nolock)
		where 
			(1=1) " . $filter;

		//echo getDebugSQL($query, $params);exit();
		$result = $this->db->query($query, $params);
		$response = $result->result('array');
		if (is_array($response) && count($response) > 0) {
			return $response;
		}
		return array();
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	function checkMorbusCrossTime($data) {
		$queryParams = array(
			"Person_id" => $data["Person_id"],
			"Morbus_setDT" => $data["Morbus_setDT"],
			"Morbus_disDT" => $data["Morbus_disDT"]
		);
		$where = "";
		$where .=" and (CAST(:Morbus_setDT as date) > CAST(M.Morbus_setDT as datetime) and CAST(:Morbus_setDT as date) < CAST(M.Morbus_disDT as datetime)";
		if (isset($data["Morbus_disDT"]) && $data['Morbus_disDT'] != "") {
			$where .=" or (CAST(M.Morbus_setDT as date) > CAST(:Morbus_setDT as datetime) and CAST(M.Morbus_setDT as date) < CAST(:Morbus_disDT as datetime)))";
		} else {
			$where .=" or CAST(M.Morbus_setDT as date)>CAST(:Morbus_setDT as datetime))";
		}
		if(isset($data["Morbus_id"])&& $data['Morbus_id'] != ""){
			$where .=" and M.Morbus_id != :Morbus_id";
			$queryParams['Morbus_id'] =$data['Morbus_id'];
		}
		$query = "Select count(*) as cnt
			From v_morbus M with(nolock)
			inner join v_MorbusACS MA with(nolock) on M.Morbus_id = MA.Morbus_id
			where M.morbusType_id = :MorbusType_id and M.Person_id = :Person_id
			" . $where."";

		$queryParams['MorbusType_id'] = $this->getMorbusTypeId();
		/*echo getDebugSQL($query, $queryParams);
		exit();*/
		$result = $this->db->query($query, $queryParams)->result('array');
		if ($result[0]["cnt"] > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function saveMorbusACSEditWindow($data) {

		$checkMorbusCrossTime = $this->checkMorbusCrossTime($data);
		if (!$checkMorbusCrossTime) {
			return array(array('Error_Code' => 1,'Error_Msg' => "Указанный период госпитализации имеет пересечение с предыдущей госпитализацией. ОК"));
		}

		if(!empty($data['Morbus_disDT']) && $data['Morbus_disDT'] < $data['Morbus_setDT']) {
			$data['Morbus_disDT'] = null; // сбрасываем дату выписки если дата выписки раньше даты поступления  #169158
		}

		if ((!isset($data['MorbusACS_id']) && !isset($data['Morbus_id']))
				|| ($data['MorbusACS_id'] <= 0 && $data['Morbus_id'] <= 0)) {
			$procedure = 'ins';
		} else {
			$procedure = 'upd';
		}
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@MorbusBase_id bigint ,
				@Evn_pid bigint = null,
				@Person_id bigint =:Person_id,
				@MorbusType_id bigint = :MorbusType_id,
				@Morbus_id bigint = :Morbus_id,
				@Diag_id bigint = :Diag_id,
				@Morbus_setDT datetime = :Morbus_setDT,
				@Morbus_disDT datetime = :Morbus_disDT,
				@MorbusACS_id bigint = :MorbusACS_id,
				
				@pmUser_id bigint = :pmUser_id
			
			
			--базовое заболевание данного типа должно быть одно на человека
			select top 1 @MorbusBase_id = MorbusBase_id from v_MorbusBase with (nolock) where Person_id = @Person_id and MorbusType_id = @MorbusType_id and MorbusBase_disDT is null

				exec p_Morbus_" . $procedure . "
					@Morbus_id = @Morbus_id output,
					@MorbusBase_id = @MorbusBase_id,
					@Morbus_setDT = @Morbus_setDT,
					@Morbus_disDT = @Morbus_disDT,
					@Evn_pid = @Evn_pid,
					@Diag_id = @Diag_id,
					@pmUser_id = @pmUser_id,
					@Error_Code = @Error_Code output


			exec p_MorbusACS_" . $procedure . "
				@MorbusACS_id = @MorbusACS_id output,
				@Diag_did=:Diag_did,
				@MorbusACS_Comment=:MorbusACS_Comment,
				@MorbusACS_TimeDesease=:MorbusACS_TimeDesease,
				@MorbusACS_isCoronary=:MorbusACS_isCoronary,
				@MorbusACS_Result=:MorbusACS_Result,
				@MorbusACS_isFCSSH=:MorbusACS_isFCSSH,
				@MorbusACS_isLpu=:MorbusACS_isLpu,
				@MorbusACS_isPso=:MorbusACS_isPso,
				@MorbusACS_isST=:MorbusACS_isST,
				@MorbusACS_isTinaki=:MorbusACS_isTinaki,
				@MorbusACS_isTransderm=:MorbusACS_isTransderm,
				@MorbusACS_isTrombPrehosp=:MorbusACS_isTrombPrehosp,
				@MorbusACS_isTrombStac=:MorbusACS_isTrombStac,
				@PrehospArrive_id=:PrehospArrive_id,
				@Morbus_id=@Morbus_id,
				@pmUser_id=:pmUser_id
				
			select @Res as MorbusACS_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'MorbusACS_id' => ((!isset($data['MorbusACS_id'])) || ($data['MorbusACS_id'] <= 0) ? NULL : $data['MorbusACS_id']),
			'Morbus_setDT' => $data['Morbus_setDT'],
			'Morbus_disDT' => $data['Morbus_disDT'],
			'Diag_did' => $data['Diag_did'],
			'Diag_id' => $data['Diag_id'],
			'MorbusACS_Comment' => $data['MorbusACS_Comment'],
			'MorbusACS_TimeDesease' => $data['MorbusACS_TimeDesease'],
			'MorbusACS_isCoronary' => $data['MorbusACS_isCoronary'],
			'MorbusACS_isFCSSH' => $data['MorbusACS_isFCSSH'],
			'MorbusACS_isLpu' => $data['MorbusACS_isLpu'],
			'MorbusACS_isPso' => $data['MorbusACS_isPso'],
			'MorbusACS_isST' => $data['MorbusACS_isST'],
			'MorbusACS_isTinaki' => $data['MorbusACS_isTinaki'],
			'MorbusACS_isTransderm' => $data['MorbusACS_isTransderm'],
			'MorbusACS_isTrombPrehosp' => $data['MorbusACS_isTrombPrehosp'],
			'MorbusACS_isTrombStac' => $data['MorbusACS_isTrombStac'],
			'MorbusACS_Result' => $data['MorbusACS_Result'],
			'PrehospArrive_id' => $data['PrehospArrive_id'],
			'Morbus_id' => $data['Morbus_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Person_id' => $data['Person_id'],
		);
		$queryParams['MorbusType_id'] = $this->getMorbusTypeId();

		//echo getDebugSQL($query, $queryParams);exit();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$result =$result->result('array');
			if($result[0]['Error_Msg']!=null){
				return false;
			}else{
				return array('Error_Msg'=>'');
			}
		} else {
			return false;
		}
	}

	/**
	 * Сохранение специфики
	 * return array Идентификаторы объектов, которые были обновлены или ошибка
	 * comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	private function updateMorbusSpecific($data) {
		$err_arr = array();
		$entity_saved_arr = array();
		$not_edit_fields = array('Evn_pid', 'Person_id', 'MorbusOrphan_id', 'Morbus_id', 'MorbusBase_id', 'MorbusType_id', 'Morbus_setDT', 'Morbus_disDT', 'MorbusBase_setDT', 'MorbusBase_disDT');
		if (isset($data['field_notedit_list']) && is_array($data['field_notedit_list'])) {
			$not_edit_fields = array_merge($not_edit_fields, $data['field_notedit_list']);
		}
		foreach ($this->entityFields as $entity => $l_arr) {
			$allow_save = false;
			foreach ($data as $key => $value) {
				if (in_array($key, $l_arr) && !in_array($key, $not_edit_fields)) {
					$allow_save = true;
					break;
				}
			}

			if ($allow_save && !empty($data[$entity . '_id'])) {
				$q = 'select top 1 ' . implode(', ', $l_arr) . ' from dbo.v_' . $entity . ' WITH (NOLOCK) where ' . $entity . '_id = :' . $entity . '_id';
				$p = array($entity . '_id' => $data[$entity . '_id']);
				$r = $this->db->query($q, $data);
				if (is_object($r)) {
					$result = $r->result('array');
					if (empty($result) || !is_array($result[0]) || count($result[0]) == 0) {
						$err_arr[] = 'Получение данных ' . $entity . ' По идентификатору ' . $data[$entity . '_id'] . ' данные не получены';
						continue;
					}
					foreach ($result[0] as $key => $value) {
						if (is_object($value) && $value instanceof DateTime) {
							$value = $value->format('Y-m-d H:i:s');
						}
						//в $data[$key] может быть null
						$p[$key] = array_key_exists($key, $data) ? $data[$key] : $value;
						// ситуация, когда пользователь удалил какое-то значение
						$p[$key] = (empty($p[$key]) || $p[$key] == '0') ? null : $p[$key];
					}
				} else {
					$err_arr[] = 'Получение данных ' . $entity . ' Ошибка при выполнении запроса к базе данных';
					continue;
				}
				$field_str = '';
				foreach ($l_arr as $key) {
					$field_str .= '
						@' . $key . ' = :' . $key . ',';
				}
				$q = '
					declare
						@' . $entity . '_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @' . $entity . '_id = :' . $entity . '_id;
					exec dbo.p_' . $entity . '_upd
						@' . $entity . '_id = @' . $entity . '_id output, ' . $field_str . '
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @' . $entity . '_id as ' . $entity . '_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				';
				$p['pmUser_id'] = $data['pmUser_id'];
				//if($entity == 'MorbusBase') { echo getDebugSQL($q, $p); break; }
				$r = $this->db->query($q, $p);
				if (is_object($r)) {
					$result = $r->result('array');
					if (!empty($result[0]['Error_Msg'])) {
						$err_arr[] = 'Сохранение данных ' . $entity . ' ' . $result[0]['Error_Msg'];
						continue;
					}
					$entity_saved_arr[$entity . '_id'] = $data[$entity . '_id'];
				} else {
					$err_arr[] = 'Сохранение данных ' . $entity . ' Ошибка при выполнении запроса к базе данных';
					continue;
				}
			} else {
				continue;
			}
		}
		$entity_saved_arr['Morbus_id'] = $data['Morbus_id'];
		$entity_saved_arr['Error_Msg'] = (count($err_arr) > 0) ? implode('<br />', $err_arr) : null;
		return array($entity_saved_arr);
	}

	/**
	 * Создание специфики заболевания
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		if (empty($data['MorbusBase_id']) ||empty($data['Person_id'])
			|| empty($data['Morbus_id']) || empty($data['Diag_id']) || empty($data['Morbus_setDT'])
			|| empty($data['mode'])
			|| false == in_array($data['mode'], array('onBeforeViewData', 'onBeforeSavePersonRegister', 'onBeforeSaveEvnNotify'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$this->setParams($data);
		$tableName = $this->tableName();
		$queryParams = array();
		$queryParams['pmUser_id'] = $this->promedUserId;
		$queryParams['Morbus_id'] = $data['Morbus_id'];

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@pmUser_id bigint = :pmUser_id,
				@Morbus_id bigint = :Morbus_id,
				@{$tableName}_id bigint = null,
				@IsCreate int = 1;

			-- должно быть одно на Morbus
			select top 1 @{$tableName}_id = {$tableName}_id from v_{$tableName} with (nolock) where Morbus_id = @Morbus_id

			if isnull(@{$tableName}_id, 0) = 0
			begin
				exec p_{$tableName}_ins
					@{$tableName}_id = @{$tableName}_id output,
					@Morbus_id = @Morbus_id,
					@pmUser_id = @pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				if isnull(@{$tableName}_id, 0) > 0
				begin
					set @IsCreate = 2;
				end
			end
			select @{$tableName}_id as {$tableName}_id, @IsCreate as IsCreate, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $queryParams); exit();
		// Стартуем транзакцию
		$this->isAllowTransaction = $isAllowTransaction;
		if ( !$this->beginTransaction() ) {
			$this->isAllowTransaction = false;
			throw new Exception('Ошибка при попытке запустить транзакцию');
		}
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			$this->rollbackTransaction();
			throw new Exception('Ошибка БД', 500);
		}
		$resp = $result->result('array');
		if (!empty($resp[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			throw new Exception($resp[0]['Error_Msg'], 500);
		}
		if (empty($resp[0][$tableName . '_id'])) {
			$this->rollbackTransaction();
			throw new Exception('Что-то пошло не так', 500);
		}
		$this->commitTransaction();
		$this->_saveResponse[$tableName . '_id'] = $resp[0][$tableName . '_id'];
		return $this->_saveResponse;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function deleteMorbusACS($data){
		$query ="
			Declare @Error_Code bigint;
					Declare @Error_Message varchar(4000);
					exec dbo.p_MorbusACS_del
						@MorbusACS_id=:MorbusACS_id,
						@pmUser_id =:pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select
						@Error_Code as Error_Code,
						@Error_Message as Error_Message;
		";
		$queryParams = array(
			"pmUser_id"=>$data['pmUser_id'],
			"MorbusACS_id"=>$data["MorbusACS_id"]
		);
		$result = $this->db->query($query,$queryParams);
		$response = $result->result('array');
		if (is_array($response) && $response[0]["Error_Message"]!=null) {
			return array(array("Error_Message"=>$response[0]["Error_Message"]));
		} else {
			return array(array("success"=>"true"));
		}
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	function getACSDiag($data) {
		$filter = "(1=1)";

		if (strlen($data['query'])>0) {
			$filter .= " and (Diag_Code like :query+'%'  or Diag_Name like '%'+:query+'%')";
		} else {
			if (strlen($data['Diag_id'])>0) {
				$filter .= " and Diag_id = :Diag_id";
			}
		}

		$query = "
			select
				D.Diag_id,
				D.Diag_Code,
				D.Diag_Name
			from
				v_Diag D with (nolock)
				inner join v_MorbusDiag MD with (nolock) on D.Diag_id = MD.Diag_id
				inner join v_MorbusType MT with (nolock) on MT.MorbusType_id = MD.MorbusType_id
			where
				MT.MorbusType_SysNick = 'acs'
				and {$filter}
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

}