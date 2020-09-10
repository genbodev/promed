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

class ObjectSynchronLog_model extends SwPgModel {
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
				select ObjectSynchronLogService_id
				from v_ObjectSynchronLogService
				where ObjectSynchronLogService_SysNick = :ObjectSynchronLogService_SysNick
				limit 1
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
				inner join v_Person P on P.Person_id = OSL.Object_id
			";
		}

		$object = null;
		$query = "
			select
				OSL.ObjectSynchronLog_id as \"ObjectSynchronLog_id\",
				OSL.Object_setDT as \"Object_setDT\",
				OSL.Object_Name as \"Object_Name\",
				OSL.Object_id as \"Object_id\",
				OSL.{$valueField} as \"Object_Value\"
			from 
				v_ObjectSynchronLog OSL
				{$join}
			where 
				OSL.ObjectSynchronLogService_id = :serviceId 
				and OSL.Object_Name = :name 
				and coalesce(OSL.{$field}, 0) = coalesce(:id::bigint, 0)
				--and (OSL.Object_sid is not null or OSL.Object_guid is not null)
			order by 
				OSL.Object_setDT desc
			limit 1
		";
		$params = array(
			'serviceId' => $this->serviceId,
			'name' => $name,
			'id' => (int)$id
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
	 * Удаление строки из журнала синхронизации объекта
	 */
	function deleteObjectSynchronLog($ObjectSynchronLog_id) {
		$params = array('ObjectSyncgronLog_id' => $ObjectSynchronLog_id);
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_ObjectSynchronLog_del (
				ObjectSynchronLog_id := :ObjectSynchronLog_id
			);
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
			select
				ObjectSynchronLog_id as \"ObjectSynchronLog_id\",
				dbo.tzGetDate() as \"Object_setDT\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc} (
				ObjectSynchronLog_id := :ObjectSynchronLog_id,
				Object_Name := :Object_Name,
				Object_setDT := dbo.tzGetDate(),
				Object_id := :Object_id,
				ObjectSynchronLogService_id := :ObjectSynchronLogService_id,
				{$valueField} := :{$valueField},
				pmUser_id := :pmUser_id
			)
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