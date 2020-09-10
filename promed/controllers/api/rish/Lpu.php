<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Lpu - контроллер API для работы с МО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			10.10.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class Lpu extends SwREST_Controller {
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('LpuStructure_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 * Получение списка МО региона
	 */
	public function LpuList_get() {
		$data = $this->ProcessInputData('getLpuListByRegion');

		$resp = $this->dbmodel->getLpuListByRegion($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка МО региона по району обслуживания
	 */
	public function LpuListBySubRgn_get() {
		$data = $this->ProcessInputData('getLpuListBySubRgn');

		$resp = $this->dbmodel->getLpuListBySubRgn($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание подразделения МО
	 */
	public function LpuBuilding_post() {
		$data = $this->ProcessInputData('createLpuBuilding', null, true);

		$data['LpuBuilding_id'] = null;
		$data['fromAPI'] = 1;

		$resp = $this->dbmodel->saveLpuBuilding($data);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('LpuBuilding_id' => $resp[0]['LpuBuilding_id'])
			)
		));
	}

	/**
	 * Изменение данных подразделения МО
	 */
	public function LpuBuilding_put() {
		$data = $this->ProcessInputData('updateLpuBuilding', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuBuilding');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$old_data = $this->dbmodel->getLpuBuildingById($data);
		if (empty($old_data[0])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора подразделения',
				'error_code' => '6'
			));
		}

		// Из getLpuBuildingById прилетает значение LpuBuilding_IsExport, соответствующее коду, а не идентификатору
		// Меняем на идентификатор
		// @task https://redmine.swan.perm.ru/issues/111345
		if ( array_key_exists('LpuBuilding_IsExport', $old_data[0]) && in_array($old_data[0]['LpuBuilding_IsExport'], array(0, 1)) ) {
			$old_data[0]['LpuBuilding_IsExport']++;
		}

		$data = array_merge($old_data[0], $data);

		$data['fromAPI'] = 1;

		$resp = $this->dbmodel->saveLpuBuilding($data);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение параметров подразделения по идентификатору
	 */
	public function LpuBuildingByID_get() {
		$data = $this->ProcessInputData('getLpuBuildingById');

		$resp = $this->dbmodel->getLpuBuildingById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание участка
	 */
	public function LpuRegion_post() {
		$data = $this->ProcessInputData('createLpuRegion', null, true);

		$resp = $this->dbmodel->saveLpuRegion(array_merge($data, array(
			'LpuRegion_id' => null,
			'allowEmptyMedPersonalData' => 1,
			'LpuRegion_Descr' => null
		)));
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('LpuRegion_id' => $resp[0]['LpuRegion_id'])
			)
		));
	}

	/**
	 * Получение списка участков по номеру
	 */
	public function LpuRegionListByName_get() {
		$data = $this->ProcessInputData('getLpuRegionListByName', null, true);

		$resp = $this->dbmodel->getLpuRegionListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка участков по МО
	 */
	public function LpuRegionListByMO_get() {
		$data = $this->ProcessInputData('getLpuRegionListByMO');

		$resp = $this->dbmodel->getLpuRegionListByMOForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Изменение данных участка
	 */
	public function LpuRegion_put() {
		$data = $this->ProcessInputData('updateLpuRegion', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuRegion');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$old_data = $this->dbmodel->getLpuRegionByID($data);
		if (empty($old_data[0])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора участка',
				'error_code' => '6'
			));
		}

		$data = array_merge($old_data[0], $data);

		$resp = $this->dbmodel->saveLpuRegion(array_merge($data, array(
			'allowEmptyMedPersonalData' => 1,
			'LpuRegion_Descr' => null
		)));
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Создание периода работы врача на участке
	 */
	public function LpuRegionWorkerPlace_post() {
		$data = $this->ProcessInputData('createLpuRegionWorkerPlace', null, true, true, false);

		$medPerson = $this->dbmodel->getFirstRowFromQuery('SELECT MedPersonal_id, Lpu_id FROM v_MedStaffFact (nolock) WHERE MedStaffFact_id = :MedStaffFact_id', $data, true);
		if(!is_array($medPerson) || count($medPerson) == 0){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не найдено идентификатора справочника медицинских работников'
			));
		}
		if ($data['Lpu_id'] != $medPerson['Lpu_id']) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		
		$resp = $this->dbmodel->saveMedStaffRegion(array_merge($data, array(
			'MedStaffRegion_id' => null,
			'MedPersonal_id' => $medPerson['MedPersonal_id']
		)));
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('MedStaffRegion_id' => $resp[0]['MedStaffRegion_id'])
			)
		));
	}

	/**
	 * Изменение периода работы врача на участке
	 */
	public function LpuRegionWorkerPlace_put() {
		$data = $this->ProcessInputData('updateLpuRegionWorkerPlace', null, true, true, false);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuRegionWorkerPlace');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$old_data = $this->dbmodel->getMedStaffRegion($data);
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$data = array_merge($old_data[0], $data);

		$resp = $this->dbmodel->saveMedStaffRegion(array_merge($data, array(
			'LpuRegion_id' => $this->dbmodel->getFirstResultFromQuery('SELECT LpuRegion_id FROM MedStaffRegion (nolock) WHERE MedStaffRegion_id = :MedStaffRegion_id', $data),
			'MedPersonal_id' => $this->dbmodel->getFirstResultFromQuery('SELECT MedPersonal_id FROM v_MedStaffFact (nolock) WHERE MedStaffFact_id = :MedStaffFact_id', $data)
		)));
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение общих данных по участку
	 */
	public function LpuRegionByID_get() {
		$data = $this->ProcessInputData('getLpuRegionByID', null, true);

		$resp = $this->dbmodel->getLpuRegionByID($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение информации о периоде работы врача по идентификатору
	 */
	public function LpuRegionWorkerPlaceByID_get() {
		$data = $this->ProcessInputData('getLpuRegionWorkerPlaceByID');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getLpuRegionWorkerPlaceByID($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение информации о периоде работы врача на участке по датам начала и окончания
	 */
	public function LpuRegionWorkerPlaceListByTime_get() {
		$data = $this->ProcessInputData('getLpuRegionWorkerPlaceListByTime');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getLpuRegionWorkerPlaceList($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка периодов работы врачей на участке
	 */
	public function LpuRegionWorkerPlaceListByRegion_get() {
		$data = $this->ProcessInputData('getLpuRegionWorkerPlaceByRegion');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getLpuRegionWorkerPlaceList($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка периодов работы врачей на участке
	 */
	public function LpuRegionWorkerPlaceListByMedStaffFact_get() {
		$data = $this->ProcessInputData('getLpuRegionWorkerPlaceListByMedStaffFact');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getLpuRegionWorkerPlaceList($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение общих данных по участку
	 */
	public function LpuRegionByMO_get() {
		$data = $this->ProcessInputData('getLpuRegionByМО');

		$resp = $this->dbmodel->getLpuRegionByМО($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка подразделений по коду и наименованию
	 */
	public function LpuBuildingListByCodeAndName_get() {
		$data = $this->ProcessInputData('getLpuBuildingListByCodeAndName');

		$resp = $this->dbmodel->getLpuBuildingListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка подразделений МО
	 */
	public function LpuBuildingListByMO_get() {
		$data = $this->ProcessInputData('getLpuBuildingListByMO');
		if(
			(getRegionNick() == 'kz' && empty($data['Lpu_id']))
			||
			(empty($data['Lpu_id']) && empty($data['Lpu_OID']))
		){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'не переданы обязательные параметры'
			));
		}

		$resp = $this->dbmodel->getLpuBuildingListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание группы отделений
	 */
	public function LpuUnit_post() {
		$data = $this->ProcessInputData('createLpuUnit', null, true, true, false);

		$data['source'] = 'API';

		$resp = $this->dbmodel->saveLpuUnit($data);

		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('LpuUnit_id' => $resp[0]['LpuUnit_id'])
			)
		));
	}

	/**
	 * Изменение группы отделений
	 */
	public function LpuUnit_put() {
		$data = $this->ProcessInputData('updateLpuUnit', null, true, true, false);

		$data['source'] = 'API';

		$resp = $this->dbmodel->saveLpuUnit($data);

		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('LpuUnit_id' => $resp[0]['LpuUnit_id'])
			)
		));
	}

	/**
	 * Получение данных о группе отделений МО по идентификатору
	 */
	public function LpuUnitById_get() {
		$data = $this->ProcessInputData('getLpuUnitById', null, false);
		if(
			(getRegionNick() == 'kz' && empty($data['LpuUnit_id']))
			||
			(empty($data['LpuUnit_id']) && empty($data['LpuUnit_OID']))
		){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'не переданы обязательные параметры'
			));
		}
		if(
			(getRegionNick() == 'kz' && empty($data['LpuUnit_id']))
			||
			(empty($data['LpuUnit_id']) && empty($data['LpuUnit_OID']))
		){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'не переданы обязательные параметры'
			));
		}
		
		$resp = $this->dbmodel->getLpuUnitByIdForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка групп отделений МО по идентификатору подразделения
	 */
	public function LpuUnitList_get() {
		$data = $this->ProcessInputData('getLpuUnitList', null, true);
		
		$resp = $this->dbmodel->getLpuUnitListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание отделения
	 */
	public function LpuSection_post() {
		$data = $this->ProcessInputData('createLpuSection', null, true, true, false);

		switch ( getRegionNick() ) {
			case 'pskov':
				if ( empty($data['LpuSectionCode_id']) ) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не указан код отделения РМИС'
					));
				}
				break;

			default:
				if ( empty($data['LpuSection_Code']) ) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не указан код отделения'
					));
				}
				break;
		}

		$data['ignoreLpuSectionAttributes'] = 1;
		$data['source'] = 'API';

		$resp = $this->dbmodel->saveLpuSection(array_merge($data, array(
			'LpuSection_id' => null,
			'LpuSection_pid' => null,
			'LpuSection_Descr' => null,
			'LpuSection_Contacts' => null,
			'LpuSectionHospType_id' => null,
			'LpuSection_IsCons' => null,
			'LpuSection_IsExportLpuRegion' => null,
			'LpuSection_IsNoKSG' => null,
			'LevelType_id' => null,
			'LpuSectionType_id' => null,
			'LpuSection_Area' => null,
			'LpuSection_CountShift' => null,
			'LpuSectionDopType_id' => null,
			'LpuCostType_id' => null,
		)));
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('LpuSection_id' => $resp[0]['LpuSection_id'])
			)
		));
	}

	/**
	 * Изменение данных отделения
	 */
	public function LpuSection_put() {
		$data = $this->ProcessInputData('updateLpuSection', null, true, true, false);

		if ( getRegionNick() == 'pskov' && empty($data['LpuSectionCode_id']) ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не указан код отделения РМИС'
			));
		}

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuSection');

		if ( getRegionNick() != 'pskov' ) {
			$requiredFields[0][] = 'LpuSection_Code';
			$requiredFields[1]['LpuSection_Code'] = 'string';
		}
		
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$old_data = $this->dbmodel->getLpuSectionForAPI($data);
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$data = array_merge($old_data[0], $data);

		$data['ignoreLpuSectionAttributes'] = 1;
		$data['source'] = 'API';

		$resp = $this->dbmodel->saveLpuSection(array_merge($data, array(
			'LpuSection_pid' => null,
			'LpuSection_Descr' => null,
			'LpuSection_Contacts' => null,
			'LpuSectionHospType_id' => null,
			'LpuSection_IsCons' => null,
			'LpuSection_IsExportLpuRegion' => null,
			'LpuSection_IsNoKSG' => null,
			'LevelType_id' => null,
			'LpuSectionType_id' => null,
			'LpuSection_Area' => null,
			'LpuSection_CountShift' => null,
			'LpuSectionDopType_id' => null,
			'LpuCostType_id' => null,
		)));
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение спсика отделений по коду и наименвоанию
	 */
	public function LpuSectionListByCodeAndName_get() {
		$data = $this->ProcessInputData('getLpuSectionListByCodeAndName');

		switch ( getRegionNick() ) {
			case 'pskov':
				if ( empty($data['LpuSectionCode_Code']) ) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не указан код отделения РМИС'
					));
				}
				break;

			default:
				if ( empty($data['LpuSection_Code']) ) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не указан код отделения'
					));
				}
				break;
		}

		$resp = $this->dbmodel->getLpuSectionListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение спсика отделений в МО
	 */
	public function LpuSectionListByMO_get() {
		$data = $this->ProcessInputData('getLpuSectionListByMO');
		if(
			(getRegionNick() == 'kz' && empty($data['Lpu_id']))
			||
			(empty($data['Lpu_id']) && empty($data['Lpu_OID']))
		){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'не переданы обязательные параметры'
			));
		}

		$resp = $this->dbmodel->getLpuSectionListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка отделений в подразделении
	 */
	public function LpuSectionListByBuilding_get() {
		$data = $this->ProcessInputData('getLpuSectionListByBuilding');

		$resp = $this->dbmodel->getLpuSectionListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение отделения по его идентификатору
	 */
	public function LpuSectionById_get() {
		$data = $this->ProcessInputData('getLpuSectionById', null, false);
		
		if (!isset($data['LpuSection_id']) && !isset($data['LpuSectionOuter_id']) && (getRegionNick() != 'kz' && !isset($data['LpuSection_OID']))) {
			$error_msg = (getRegionNick() != 'kz') ? ' или LpuSection_OID' : '';
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Должен быть указан хотя бы один входящий параметр: LpuSection_id или LpuSectionOuter_id'.$error_msg
			));
		}

		$resp = $this->dbmodel->getLpuSectionByIdForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Удаление отделения
	 */
	public function LpuSection_delete() {
		$data = $this->ProcessInputData('deleteLpuSection', null, true);

		$params = array(
			'session' => $data['session'],
		);

		$resp = $this->dbmodel->getLpuSectionByIdForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if (empty($resp[0]['LpuSection_id'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не существует записи для переданного идентификатора отделения'
			));
		}

		$this->load->model('Utils_model');
		$resp = $this->Utils_model->ObjectRecordDelete($params, 'LpuSection', 'true', $data['LpuSection_id']);

		if (!is_array($resp) || !isset($resp[0]) || !empty($resp[0]['Error_Msg'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение данных дополнительного профиля отделения
	 */
	public function LpuSectionDopProfileList_get() {
		$data = $this->ProcessInputData('getLpuSectionDopProfileList', null, false);
		if(
			(getRegionNick() == 'kz' && empty($data['LpuSection_id']))
			||
			(empty($data['LpuSection_id']) && empty($data['LpuSection_OID']))
		){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'не переданы обязательные параметры'
			));
		}
		if(
			(getRegionNick() == 'kz' && empty($data['LpuSection_id']))
			||
			(empty($data['LpuSection_id']) && empty($data['LpuSection_OID']))
		){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'не переданы обязательные параметры'
			));
		}

		$resp = $this->dbmodel->getLpuSectionLpuSectionProfileListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление дополнительного профиля к отделению
	 */
	public function LpuSectionDopProfile_post() {
		$data = $this->ProcessInputData('createLpuSectionDopProfile', null, true);

		$info = $this->dbmodel->getFirstRowFromQuery("
			select top 1
				LS.LpuSectionProfile_id,
				case when LS.LpuSection_setDate is not null and LS.LpuSection_setDate > :LpuSectionLpuSectionProfile_begDate then 1 else 0 end as dateError
			from v_LpuSection LS with(nolock)
			where LS.LpuSection_id = :LpuSection_id
				and LS.Lpu_id = :Lpu_id
		", $data);
		if (!$info) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не существует записи для переданного идентификатора отделения'
			));
		}
		if ($info['LpuSectionProfile_id'] == $data['LpuSectionProfile_id']) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Профиль является основным'
			));
		}
		else if ($info['dateError'] === 1) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Дата начала действия доп. профиля не может быть раньше даты создания отделения'
			));
		}

		$params = array(
			'LpuSectionLpuSectionProfile_id' => null,
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'LpuSectionLpuSectionProfile_begDate' => $data['LpuSectionLpuSectionProfile_begDate'],
			'LpuSectionLpuSectionProfile_endDate' => $data['LpuSectionLpuSectionProfile_endDate'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$resp = $this->dbmodel->saveLpuSectionLpuSectionProfile($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('LpuSectionLpuSectionProfile_id' => $resp[0]['LpuSectionLpuSectionProfile_id'])
			)
		));
	}

	/**
	 * Редактирование периода действия дополнительного профиля отделения
	 */
	public function LpuSectionDopProfile_put() {
		$data = $this->ProcessInputData('updateLpuSectionDopProfile', null, true, true, false);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuSectionDopProfile');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$info = $this->dbmodel->getFirstRowFromQuery("
			select top 1
				LSLSP.LpuSectionLpuSectionProfile_id,
				LSLSP.LpuSection_id,
				LSLSP.LpuSectionProfile_id,
				convert(varchar(10), LSLSP.LpuSectionLpuSectionProfile_begDate, 120) as LpuSectionLpuSectionProfile_begDate,
				convert(varchar(10), LSLSP.LpuSectionLpuSectionProfile_endDate, 120) as LpuSectionLpuSectionProfile_endDate,
				LS.Lpu_id
			from v_LpuSectionLpuSectionProfile LSLSP with(nolock)
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = LSLSP.LpuSection_id
			where 
				LSLSP.LpuSectionLpuSectionProfile_id = :LpuSectionLpuSectionProfile_id
		", $data);
		if (!$info) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не найден дополнительный профиль'
			));
		}
		else if ( $info['Lpu_id'] != $data['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Редактирование дополнительного профиля запрещено'
			));
		}

		$params = array_merge($info, $data);

		$resp = $this->dbmodel->saveLpuSectionLpuSectionProfile($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление дополнительного профиля
	 */
	public function LpuSectionDopProfile_delete() {
		$data = $this->ProcessInputData('deleteLpuSectionDopProfile', null, true, true, false);

		$info = $this->dbmodel->getFirstRowFromQuery("
			select top 1
				LS.Lpu_id
			from v_LpuSectionLpuSectionProfile LSLSP with(nolock)
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = LSLSP.LpuSection_id
			where 
				LSLSP.LpuSectionLpuSectionProfile_id = :LpuSectionLpuSectionProfile_id 
		", $data);
		if (!$info) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не найден дополнительный профиль'
			));
		}
		else if ( $info['Lpu_id'] != $data['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Удаление дополнительного профиля запрещено'
			));
		}

		$resp = $this->dbmodel->deleteLpuSectionLpuSectionProfile($data);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение списка палат в отделении
	 */
	public function LpuSectionWardListBySection_get() {
		$data = $this->ProcessInputData('getLpuSectionWardListBySection');
		
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];		
		$resp = $this->dbmodel->getLpuSectionWardListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов палаты по идентификатору
	 */
	public function LpuSectionWardById_get() {
		$data = $this->ProcessInputData('getLpuSectionWardById');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getLpuSectionWardByIdForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка палат отделения по наименованию
	 */
	public function LpuSectionWardListByName_get() {
		$data = $this->ProcessInputData('getLpuSectionWardListByName');
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getLpuSectionWardListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание палаты в отделении
	 */
	public function LpuSectionWard_post() {
		$data = $this->ProcessInputData('createLpuSectionWard', null, true, true, false);
		
		$sp = $this->dbmodel->getLpuSectionData($data);
		if ( $data['Lpu_id'] != $sp['data']['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$resp = $this->dbmodel->saveLpuSectionWard(array_merge($data, array(
			'LpuSectionWard_id' => null,
			'LpuSectionWard_CountRoom' => null
		)));
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('LpuSectionWard_id' => $resp[0]['LpuSectionWard_id'])
			)
		));
	}

	/**
	 * Редактирование палаты
	 */
	public function LpuSectionWard_put() {
		$data = $this->ProcessInputData('updateLpuSectionWard', null, true, true, false);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuSectionWard');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		//$old_data = $this->dbmodel->getLpuSectionWardByIdForAPI($data);
		$old_data = $this->dbmodel->getLpuSectionWardByIdForAPI( array('LpuSectionWard_id'=>$data['LpuSectionWard_id']) );
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		
		if ($data['Lpu_id'] != $old_data[0]['Lpu_id']) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$data = array_merge($old_data[0], $data);

		$resp = $this->dbmodel->saveLpuSectionWard(array_merge($data, array(
			'LpuSectionWard_CountRoom' => null
		)));
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление палаты
	 */
	public function LpuSectionWard_delete() {
		$data = $this->ProcessInputData('deleteLpuSectionWard', null, true, true, false);

		$resp = $this->dbmodel->deleteLpuSectionWard($data);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение объектов комфортности палаты по наименованию
	 */
	public function LpuSectionWardComfortLinkByName_get() {
		$data = $this->ProcessInputData('getLpuSectionWardComfortLinkByName');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getLpuSectionWardComfortLinkListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка объектов комфортности по палате
	 */
	public function LpuSectionWardComfotLinkListByWard_get() {
		$data = $this->ProcessInputData('getLpuSectionWardComfotLinkListByWard');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getLpuSectionWardComfortLinkListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание коек в Коечном фонде отделения
	 */
	public function LpuSectionBedState_post() {
		$data = $this->ProcessInputData('createLpuSectionBedState', null, true, true, false);

		$data['source'] = 'API';
		$data['LpuSectionBedProfileLink_id'] = $data['LpuSectionBedProfileLink_fedid'];

		$resp = $this->dbmodel->saveLpuSectionBedState($data);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('LpuSectionBedState_id' => $resp[0]['LpuSectionBedState_id'])
			)
		));
	}

	/**
	 * Изменение коек в коечном фонде отделения
	 */
	public function LpuSectionBedState_put() {
		$data = $this->ProcessInputData('updateLpuSectionBedState', null, true, true, false);

		$data['source'] = 'API';
		$data['LpuSectionBedProfileLink_id'] = $data['LpuSectionBedProfileLink_fedid'];

		$resp = $this->dbmodel->saveLpuSectionBedState($data);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('LpuSectionBedState_id' => $resp[0]['LpuSectionBedState_id'])
			)
		));
	}

	/**
	 * Удаление в отделении МО коек по профилю
	 */
	public function LpuSectionBedState_delete() {
		$data = $this->ProcessInputData('deleteLpuSectionBedState', null, true, true, false);

		$LpuSectionBedState_id = $this->dbmodel->getFirstResultFromQuery("select top 1 LpuSectionBedState_id from LpuSectionBedState with (nolock) where LpuSectionBedState_id = :LpuSectionBedState_id", $data, true);
		if ($LpuSectionBedState_id === false) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if (empty($LpuSectionBedState_id)) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не существует записи для переданного идентификатора'
			));
		}

		$resp = $this->dbmodel->deleteLpuSectionBedState($data);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение списка коек коечного фонда отделения МО
	 */
	public function LpuSectionBedStateListBySection_get() {
		$data = $this->ProcessInputData('getLpuSectionBedStateListBySection');

		$resp = $this->dbmodel->getLpuSectionBedStateListBySectionForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление объектов комфортности в палате
	 */
	public function LpuSectionWardComfortLink_post() {
		$data = $this->ProcessInputData('createLpuSectionWardComfortLink', null, true, true, false);

		$resp = $this->dbmodel->saveLpuSectionWardComfortLink(array_merge($data, array(
			'LpuSectionWardComfortLink_id' => null
		)));
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('LpuSectionWardComfortLink_id' => $resp[0]['LpuSectionWardComfortLink_id'])
			)
		));
	}

	/**
	 * Изменение объектов комфортности в палате
	 */
	public function LpuSectionWardComfortLink_put() {
		$data = $this->ProcessInputData('updateLpuSectionWardComfortLink', null, true, true, false);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuSectionWardComfortLink');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$old_data = $this->dbmodel->getLpuSectionWardComfortLinkForAPI($data);
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		
		if (isset($old_data[0]['Lpu_id']) && $data['Lpu_id'] != $old_data[0]['Lpu_id']) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$data = array_merge($old_data[0], $data);

		$resp = $this->dbmodel->saveLpuSectionWardComfortLink(array_merge($data, array(

		)));
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление объектов комфортности по идентификатору
	 */
	public function LpuSectionWardComfortLink_delete() {
		$data = $this->ProcessInputData('deleteLpuSectionWardComfortLink', null, true, true, false);

		$resp = $this->dbmodel->deleteSectionWardComfortLink($data);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение МО по идентификатору
	 */
	public function MOById_get() {
		$data = $this->ProcessInputData('getMOById', null, true, true, false);

		$resp = $this->dbmodel->getMOById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение Таблицы связка регионального и ФЕД профиля коек
	 */
	public function LpuSectionBedProfileLinkFed_get() {
		$resp = $this->dbmodel->getLpuSectionBedProfileLinkFed();
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}