<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Класс TerritoryService
 * 
 * Обслуживаемая территория
 */
class TerritoryService extends swController {
	
	/**
	 * @inheritdoc
	 */
	public $inputRules = array(
		'saveLpuBuildingTerritoryService' => array(
			array('field' => 'LpuBuildingStreet_id', 'label' => 'Идентификатор обслуживаемой территории', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'type' => 'id', 'rules' => 'required'),
			array('field' => 'KLCountry_id', 'label' => 'Страна', 'type' => 'id', 'rules' => 'required'),
			array('field' => 'KLRegion_id', 'label' => 'Регион', 'type' => 'id', 'rules' => 'required'),
			array('field' => 'KLSubRegion_id', 'label' => 'Район', 'type' => 'id'),
			array('field' => 'KLCity_id', 'label' => 'Город', 'type' => 'id'),
			array('field' => 'KLTown_id', 'label' => 'Населенный пункт', 'type' => 'id'),
			array('field' => 'KLStreet_id', 'label' => 'Улица', 'type' => 'id'),
			array('field' => 'LpuBuildingStreet_IsAll', 'label' => 'Вся указанная территориия', 'type' => 'string'),
			//array('field' => 'TerritoryService_id', 'label' => 'Ид территории', 'type' => 'string'),
			array('field' => 'LpuBuildingStreet_HouseSet', 'label' => 'Список диапазонов и номеров домов', 'type' => 'string'), // JSON
		),
		
		'loadLpuBuildingTerritoryServiceList' => array(
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'type' => 'id', 'rules' => 'required')
		),
		
		'getKLAreaStatLpuByUAddress' => array(),
		
		'getLpuBuildingTerritoryServiceForEdit' => array(
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'type' => 'id', 'rules' => 'required'),
			array('field' => 'LpuBuildingStreet_id', 'label' => 'Идентификатор обслуживаемой территории', 'type' => 'id', 'rules' => 'required'),
			/*array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'type' => 'id', 'rules' => 'required'),
			array('field' => 'LpuBuildingTerritoryServiceRel_id', 'label' => 'Идентификатор связи обслуживаемой территории с подразделением', 'type' => 'id', 'rules' => 'required'),*/
		),
		
		'getLpuBuildingTerritoryServiceHousesForEdit' => array(
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'type' => 'id', 'rules' => 'required'),
			array('field' => 'LpuBuildingTerritoryServiceRel_id', 'label' => 'Идентификатор связи обслуживаемой территории с подразделением', 'type' => 'id', 'rules' => 'required'),
		),
		
		'getLpuBuildingIdByAddress' => array(
			array('field' => 'KLStreet_id', 'label' => 'Улица', 'type' => 'id', 'rules' => 'required'),
			array('field' => 'house', 'label' => 'Дом', 'type' => 'string'),
			array('field' => 'building', 'label' => 'Корпус', 'type' => 'string'),
			array('field' => 'city', 'label' => 'Нас. пункт', 'type' => 'string'),
			array('field' => 'town', 'label' => 'Нас. пункт', 'type' => 'string'),
			array('field' => 'allRegion', 'label' => 'Поиск по всему региону', 'type' => 'boolean'),
			array('field' => 'Area_pid', 'label' => 'Регион', 'type' => 'id')
		),
	);
	
	/**
	 * @inheritdoc
	 */
	public function __construct(){
		parent::__construct();
		
		$this->load->database();
     	$this->load->model('TerritoryService_model', 'dbmodel');
	}
	
	/**
	 * Сохранение территории обслуживаемой подразделением
	 */
	public function saveLpuBuildingTerritoryService(){
		$data = $this->ProcessInputData('saveLpuBuildingTerritoryService', true);
		if (!$data) {
			return false;
		}
		
		$result = $this->dbmodel->saveLpuBuildingTerritoryService($data);
		return $this->ProcessModelSave($result, true, 'Не удалось сохранить информацию о территории, обслуживаемой подразделением.')->ReturnData();
	}
	
	/**
	 * Получение списка обслуживаемых территоий для указанного подразделения
	 */
	public function loadLpuBuildingTerritoryServiceList(){
		$data = $this->ProcessInputData('loadLpuBuildingTerritoryServiceList', false);
		if (!$data) {
			return false;
		}
		
		$result = $this->dbmodel->loadTerritoryServiceListByLpuBuildingId($data['LpuBuilding_id']);
		return $this->ProcessModelList($result, true, true)->ReturnData();
	}	
	
	/**
	 * Получение списка обслуживаемых территоий для указанного подразделения
	 */
	public function getKLAreaStatLpuByUAddress(){
		$data = $this->ProcessInputData('getKLAreaStatLpuByUAddress', true);

		$result = $this->dbmodel->getKLAreaStatLpuByUAddress($data);
		return $this->ProcessModelList($result, true, true)->ReturnData();
	}
	
	/**
	 * Возвращает территорию обслуживания для редактирования
	 * Для удобства возвращаются все дома для указанной улицы
	 */
	public function getLpuBuildingTerritoryServiceForEdit(){
		$data = $this->ProcessInputData('getLpuBuildingTerritoryServiceForEdit', false);
		if (!$data) {
			return false;
		}
		
		$result = $this->dbmodel->getLpuBuildingTerritoryServiceForEdit($data['LpuBuilding_id'], $data['LpuBuildingStreet_id'], $data);
		//$result = $this->dbmodel->getLpuBuildingTerritoryServiceForEdit($data['LpuBuilding_id'], $data['LpuBuildingTerritoryServiceRel_id'], $data);
		
		// Замена значения чекбокса для правильного отображения в форме
		$dbmodel = $this->dbmodel;
		$result[0]['LpuBuildingStreet_IsAll'] = $result[0]['LpuBuildingStreet_IsAll'] == $dbmodel::YES_ID ? 1 : 0;
		
		return $this->ProcessModelList($result)->ReturnData();
	}
	
	/**
	 * Возвращает список домов территорию обслуживания для редактирования
	 * Для удобства возвращаются все дома для указанной улицы
	 */
	public function getLpuBuildingTerritoryServiceHousesForEdit(){
		$data = $this->ProcessInputData('getLpuBuildingTerritoryServiceHousesForEdit', false);
		if (!$data) {
			return false;
		}
		
		$result = $this->dbmodel->getLpuBuildingTerritoryServiceHousesForEdit($data['LpuBuilding_id'], $data['LpuBuildingTerritoryServiceRel_id'], $data);
		$this->ProcessModelList($result)->ReturnData();
	}
	
	/**
	 * Возвращает ID подразделения по указанному адресу
	 */
	public function getLpuBuildingIdByAddress(){
		$data = $this->ProcessInputData('getLpuBuildingIdByAddress');
		if (!$data) {
			return false;
		}

		$session_data = getSessionParams();
		
		$result = $this->dbmodel->getLpuBuildingIdByAddress($data);
		return $this->ProcessModelList($result, true, true)->ReturnData();
	}
}