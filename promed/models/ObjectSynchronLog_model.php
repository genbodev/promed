<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ObjectSynchronLog_model - модель для работы с журналом синхронизации данных со сторонними сервисами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			15.04.2015
 *
 * @property-read int $serviceId
 * @property-read int $serviceSysNick
 */

class ObjectSynchronLog_model extends swModel {
	protected $_serviceId = null;
	protected $_serviceSysNick = null;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Указание стороннего сервиса в модели для работы с журналом синхронизации
	 */
	function setServiceSysNick($serviceSysNick) {
		$this->_serviceSysNick = $serviceSysNick;
	}

	/**
	 * Возвращает системное наименование текущего сервиса, установленного в модели
	 */
	function getServiceSysNick() {
		return $this->_serviceSysNick;
	}

	/**
	 * Возвращает идентификатор текущего сервиса, установленного в модели
	 */
	function getServiceId() {
		if (empty($this->_serviceId)) {
			$this->_serviceId = $this->getFirstResultFromQuery("
				select top 1 ObjectSynchronLogService_id
				from v_ObjectSynchronLogService with(nolock)
				where ObjectSynchronLogService_SysNick = :ObjectSynchronLogService_SysNick
			", array('ObjectSynchronLogService_SysNick' => $this->serviceSysNick));
			if (!$this->_serviceId) {
				throw new Exception('Не удалось получить идентификатор сервиса', 500);
			}
		}
		return $this->_serviceId;
	}

	/**
	 * Получение данных из журнала синхронизации со сторонними сервисами
	 */
	function getObjectSynchronLog($name, $id, $field = 'Object_id') {
		$join = "";
		$valueField = 'Object_sid';
		if ($this->serviceSysNick == 'RmisEkb' && in_array($name, array('Person', 'Person_Patient'))) {
			$valueField = 'Object_guid';
		}

		//Если поиск по идентификатору человека из стороннего сервиса, то пропускать удаленных
		if ($name == 'Person' && $field == 'Object_sid') {
			$join .= "
				inner join v_Person P with(nolock) on P.Person_id = OSL.Object_id
			";
		}

		$object = null;
		$query = "
			select top 1
				OSL.ObjectSynchronLog_id,
				OSL.Object_setDT,
				OSL.Object_Name,
				OSL.Object_id,
				OSL.{$valueField} as Object_Value
			from 
				v_ObjectSynchronLog OSL with(nolock)
				{$join}
			where 
				OSL.ObjectSynchronLogService_id = :serviceId 
				and OSL.Object_Name = :name 
				and isnull(OSL.{$field}, 0) = isnull(:id, 0)
				--and (OSL.Object_sid is not null or OSL.Object_guid is not null)
			order by 
				OSL.Object_setDT desc
		";
		$params = array(
			'serviceId' => $this->serviceId,
			'name' => $name,
			'id' => $id
		);
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при поиске записи в журнале синхронизации объекта', 500);
		}
		if (count($resp) > 0) {
			$object = $resp[0];
		}
		return $object;
	}

	/**
	 * @param string $name
	 * @param string $guid
	 * @return array
	 * @throws Exception
	 */
	function getObjectSynchronLogByGuid($name, $guid) {
		$params = array(
			'serviceId' => $this->serviceId,
			'name' => $name,
			'guid' => $guid
		);

		$query = "
			select top 1
				OSL.ObjectSynchronLog_id,
				OSL.Object_setDT,
				OSL.Object_Name,
				OSL.Object_id
			from 
				v_ObjectSynchronLog OSL with(nolock)
			where
				OSL.ObjectSynchronLogService_id = :serviceId 
				and OSL.Object_Name = :name
				and OSL.Object_Guid = :guid
			order by
				OSL.Object_setDT desc
		";

		$object = $this->getFirstRowFromQuery($query, $params, true);
		if ($object === false) {
			throw new Exception('Ошибка при поиске записи в журнале синхронизации объекта', 500);
		}
		return $object;
	}

	/**
	 * Удаление строки из журнала синхронизации объекта
	 */
	function deleteObjectSynchronLog($ObjectSynchronLog_id) {
		$params = array('ObjectSyncgronLog_id' => $ObjectSynchronLog_id);
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_ObjectSynchronLog_del
				@ObjectSynchronLog_id = :ObjectSynchronLog_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при удалении строки из журнала синхронизации объекта', 500);
		}
		return $resp;
	}

	/**
	 * Получение данных из журнала синхронизации со сторонними сервисами
	 */
	function saveObjectSynchronLog($name, $id, $value, $ins = false) {
		$ObjectSynchronLogData = $this->getObjectSynchronLog($name, $id);

		$valueField = 'Object_sid';
		if (
			in_array($this->serviceSysNick, ['IszlKrym', 'TFOMSAutoInteract', 'MSEExp', 'MSEImp']) ||
			($this->serviceSysNick == 'RmisEkb' && in_array($name, array('Person', 'Person_Patient')))
		) {
			$valueField = 'Object_guid';
		}

		$proc = "p_ObjectSynchronLog_ins";
		$ObjectSynchronLog_id = null;
		if (!$ins && !empty($ObjectSynchronLogData['ObjectSynchronLog_id'])) {
			$ObjectSynchronLog_id = $ObjectSynchronLogData['ObjectSynchronLog_id'];
			if ((!empty($ObjectSynchronLogData['Object_Value']) || !empty($value)) && $ObjectSynchronLogData['Object_Value'] == $value) {
				// если не изменилось то и не обновляем.
				return array(array('ObjectSynchronLog_id' => $ObjectSynchronLog_id, 'Error_Code' => '', 'Error_Msg' => ''));
			}
			$proc = "p_ObjectSynchronLog_upd";
		}

		$query = "
			declare
				@ObjectSynchronLog_id bigint = :ObjectSynchronLog_id,
				@Object_setDT datetime = dbo.tzGetDate(),
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec {$proc}
				@ObjectSynchronLog_id = @ObjectSynchronLog_id output,
				@Object_Name = :Object_Name,
				@Object_setDT = @Object_setDT,
				@Object_id = :Object_id,
				@ObjectSynchronLogService_id = :ObjectSynchronLogService_id,
				@{$valueField} = :{$valueField},
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ObjectSynchronLog_id as ObjectSynchronLog_id, @Object_setDT as Object_setDT, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$params = array(
			'ObjectSynchronLog_id' => $ObjectSynchronLog_id,
			'ObjectSynchronLogService_id' => $this->serviceId,
			'Object_Name' => $name,
			'Object_id' => $id,
			$valueField => $value,
			'pmUser_id' => $this->promedUserId
		);
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);

		if (!is_array($resp)) {
			throw new Exception('Ошибка при сохранении в журнале синхронизации объекта', 500);
		}

		return $resp;
	}
}
