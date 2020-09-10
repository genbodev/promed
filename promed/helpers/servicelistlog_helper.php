<?php
/**
 * Class ServiceListLog
 *
 * @property ServiceList_model $model
 */

class ServiceListLog {
	private $model = null;
	private $ServiceListLog_id = null;
	private $ServiceList_id = null;
	private $pmUser_id = null;
	private $isFinished = false;

	public $begDT = null;
	public $endDT = null;

	private $packageTypeList = null;
	private $procDataTypeList = null;

	/**
	 * ServiceListLog constructor.
	 * @param int $ServiceList_id
	 * @param int $pmUser_id
	 */
	function __construct($ServiceList_id, $pmUser_id) {
		$CI = &get_instance();
		$CI->load->model('ServiceList_model');
		$this->model = $CI->ServiceList_model;
		$this->ServiceList_id = $ServiceList_id;
		$this->pmUser_id = $pmUser_id;
	}

	/**
	 * @return DateTime|false
	 */
	private function getDT() {
		$dt = $this->model->getFirstResultFromQuery("select dbo.tzGetDate()");
		if ($dt instanceof DateTime) return $dt;
		return date_create($dt);
	}

	/**
	 * @return null|int
	 */
	public function getId() {
		return $this->ServiceListLog_id;
	}

	/**
	 * @return array|null
	 */
	public function getPackageTypeList() {
		if (empty($this->packageTypeList)) {
			$this->packageTypeList = $this->model->loadServiceListPackageTypeList();
		}
		return is_array($this->packageTypeList)?$this->packageTypeList:array();
	}

	/**
	 * @param $packageType
	 * @return null
	 */
	public function getPackageTypeId($packageType) {
		foreach ($this->getPackageTypeList() as $item) {
			if ($item['ServiceListPackageType_Name'] == $packageType) {
				return $item['ServiceListPackageType_id'];
			}
		}
		return null;
	}

	/**
	 * @return array|null
	 */
	public function getProcDataTypeList() {
		if (empty($this->procDataTypeList)) {
			$this->procDataTypeList = $this->model->loadServiceListProcDataTypeList();
		}
		return is_array($this->procDataTypeList)?$this->procDataTypeList:array();
	}

	/**
	 * @param $procDataType
	 * @return null
	 */
	public function getProcDataTypeId($procDataType) {
		foreach ($this->getProcDataTypeList() as $item) {
			if ($item['ServiceListProcDataType_Name'] == $procDataType) {
				return $item['ServiceListProcDataType_id'];
			}
		}
		return null;
	}

	/**
	 * @param string $begDT
	 * @return array
	 */
	function start($begDT = null) {
		if (!empty($this->ServiceListLog_id)) {
			return $this->model->createError('','Нельзя начать лог, поскольку он уже был начат');
		}

		$this->begDT = !empty($begDT)?$begDT:$this->getDT();
		if (!$this->begDT) return $this->model->createError('','Ошибка при получении времени запуска сервиса');
		$this->endDT = null;
		$this->isFinished = false;

		$resp = $this->model->saveServiceListLog(array(
			'ServiceListLog_id' => null,
			'ServiceList_id' => $this->ServiceList_id,
			'ServiceListLog_begDT' => $this->begDT,
			'ServiceListResult_id' => 2,
			'pmUser_id' => $this->pmUser_id
		));
		if ($this->model->isSuccessful($resp)) {
			$this->ServiceListLog_id = $resp[0]['ServiceListLog_id'];
		}
		return $resp;
	}

	/**
	 * @param bool $isSuccess
	 * @param string $endDT
	 * @return array
	 */
	function finish($isSuccess, $endDT = null) {
		if (empty($this->ServiceListLog_id)) {
			return $this->model->createError('','Нельзя закончить лог, поскольку он не был начат');
		}
		if ($this->isFinished) {
			return $this->model->createError('','Нельзя закончить лог, поскольку он уже закончен');
		}

		$this->endDT = !empty($endDT)?$endDT:$this->getDT();
		if (!$this->endDT) {
			return $this->model->createError('','Ошибка при получении времени окончания работы сервиса');
		}

		$resp = $this->model->saveServiceListLog(array(
			'ServiceListLog_id' => $this->ServiceListLog_id,
			'ServiceList_id' => $this->ServiceList_id,
			'ServiceListLog_begDT' => $this->begDT,
			'ServiceListLog_endDT' => $this->endDT,
			'ServiceListResult_id' => $isSuccess?1:3,
			'pmUser_id' => $this->pmUser_id
		));
		if ($this->model->isSuccessful($resp)) {
			$this->isFinished = true;
		}
		return $resp;
	}

	/**
	 * @param bool $isMessage
	 * @param string|array $message
	 * @param int|null
	 * @return array
	 */
	function add($isMessage, $message, $ServiceListPackage_id = null) {
		if (empty($this->ServiceListLog_id)) {
			return $this->model->createError('','Нельзя добавить запись в лог, поскольку он не был начат');
		}
		if ($this->isFinished) {
			return $this->model->createError('','Нельзя добавить запись в лог, поскольку он был закончен');
		}
		if (is_array($message)) {
			$message = implode("<br/>", $message);
		}
		$resp = $this->model->saveServiceListDetailLog(array(
			'ServiceListLog_id' => $this->ServiceListLog_id,
			'ServiceListPackage_id' => $ServiceListPackage_id,
			'ServiceListLogType_id' => $isMessage?1:2,
			'ServiceListDetailLog_Message' => $message,
			'pmUser_id' => $this->pmUser_id
		));
		return $resp;
	}

    /**
     * @param string $objectName
     * @param int|string $objectID
     * @param string|null $GUID
     * @param int|null $Lpu_id
     * @param string|null $packageType
     * @param string|null $procDataType
     * @param string|null $packageData
     * @param bool $isResp
     * @return array
     */
	function addPackage($objectName, $objectID, $GUID = null, $Lpu_id = null, $packageType = null, $procDataType = null, $packageData = null, $isResp = false) {
		if (empty($this->ServiceListLog_id)) {
			return $this->model->createError('','Нельзя добавить запись в лог, поскольку он не был начат');
		}
		if ($this->isFinished) {
			return $this->model->createError('','Нельзя добавить запись в лог, поскольку он был закончен');
		}
		$resp = $this->model->addServiceListPackage([
			'ServiceListLog_id' => $this->ServiceListLog_id,
			'ServiceListPackage_ObjectName' => $objectName,
			'ServiceListPackage_ObjectID' => $objectID,
			'ServiceListPackage_GUID' => $GUID,
			'Lpu_oid' => $Lpu_id,
			'ServiceListPackageType_id' => $this->getPackageTypeId($packageType),
			'ServiceListProcDataType_id' => $this->getProcDataTypeId($procDataType),
			'ServicePackage_Data' => $packageData,
			'ServicePackage_IsResp' => $isResp?2:1,
			'pmUser_id' => $this->pmUser_id,
		]);
		return $resp;
	}

	/**
	 * @param int $packageId
	 * @param string $packageData
	 * @param bool $isResp
	 * @return array|false
	 */
	function addPackageData($packageId, $packageData, $isResp = true) {
		if (empty($this->ServiceListLog_id)) {
			return $this->model->createError('','Нельзя добавить запись в лог, поскольку он не был начат');
		}
		if ($this->isFinished) {
			return $this->model->createError('','Нельзя добавить запись в лог, поскольку он был закончен');
		}
		$resp = $this->model->addServicePackage([
			'ServiceListPackage_id' => $packageId,
			'ServicePackage_Data' => $packageData,
			'ServicePackage_IsResp' => $isResp?2:1,
			'pmUser_id' => $this->pmUser_id
		]);
		return $resp;
	}

	/**
	 * @param int $packageId
	 * @param string $status
	 * @return array
	 */
	function setPackageStatus($packageId, $status) {
		if (empty($this->ServiceListLog_id)) {
			return $this->model->createError('','Нельзя изменить статус пакета, поскольку лог не начат');
		}
		if ($this->isFinished) {
			return $this->model->createError('','Нельзя изменить статус пакета, поскольку лог закончен');
		}
		$resp = $this->model->setServiceListPackageStatus([
			'ServiceListPackage_id' => $packageId,
			'PackageStatus_SysNick' => $status,
			'pmUser_id' => $this->pmUser_id
		]);
		return $resp;
	}

	/**
	 * @param string $packageType
	 * @param string $objectId
	 * @param string|null $status
	 * @return false|int|null
	 */
	function findPackageId($packageType, $objectId, $status = null) {
		return $this->model->findServiceListPackageId([
			'ServiceList_id' =>  $this->ServiceList_id,
			'ServiceListPackageType_Name' => $packageType,
			'ServiceListPackage_Object_id' => $objectId,
			'PackageStatus_SysNick' => $status
		]);
	}

	/**
	 * Метод для определения типа логируемого сообщения в зависимости от содержания ответа
	 *
	 * @param array $arr
	 * @return int
	 */
	function getServiceListLogType(array $arr)
	{
		if ($arr['Success'] == true) {
			$ServiceListLogType_id = 1;
		} elseif (!empty($result['ValidationResult']))
		{
			$ServiceListLogType_id = 3;
		} else
		{
			$ServiceListLogType_id = 2;
		}

		return $ServiceListLogType_id;
	}
}
