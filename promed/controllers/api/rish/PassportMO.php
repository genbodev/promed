<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PassportMO - контроллер API для работы с паспортом МО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Alexander Kurakin
 * @version			12.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class PassportMO extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('LpuPassport_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 *  Изменение паспорта МО
	 */
	function PassportMO_put() {
		$data = $this->ProcessInputData('updatePassportMO');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createPassportMO');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$resp = $this->dbmodel->getLpuPassport(array('Lpu_id'=>$data['Lpu_id']));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['Lpu_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует МО для переданного идентификатора организации',
				'error_code' => '6'
			));
		}
		$data['fromAPI'] = 1;
		if (isset($data['Org_Phone'])) {
			$data['Lpu_Phone'] = $data['Org_Phone'];
		}
		if (isset($data['Org_Email'])) {
			$data['Lpu_Email'] = $data['Org_Email'];
		}
		if (isset($data['Org_Www'])) {
			$data['Lpu_Www'] = $data['Org_Www'];
		}
		if (isset($data['Org_OKATO'])) {
			$data['Lpu_Okato'] = $data['Org_OKATO'];
		}
		if (isset($data['Org_id'])) {
			$data['Org_lid'] = $data['Org_id'];
		}
		if (isset($data['PasportMO_IsTerLimited'])) {
			$data['PasportMO_IsTerLimited'] = (($data['PasportMO_IsTerLimited'] == '1') ? ('true') : ('false'));
		}
		if (isset($data['PasportMO_IsFenceTer'])) {
			$data['PasportMO_IsFenceTer'] = (($data['PasportMO_IsFenceTer'] == '1') ? ('true') : ('false'));
		}
		$data['PasportMO_IsAssignNasel'] = null;
		if (isset($data['PasportMO_IsSecur'])) {
			$data['PasportMO_IsSecur'] = (($data['PasportMO_IsSecur'] == '1') ? ('true') : ('false'));
		}
		if (isset($data['PasportMO_IsMetalDoors'])) {
			$data['PasportMO_IsMetalDoors'] = (($data['PasportMO_IsMetalDoors'] == '1') ? ('true') : ('false'));
		}
		if (isset($data['PasportMO_IsVideo'])) {
			$data['PasportMO_IsVideo'] = (($data['PasportMO_IsVideo'] == '1') ? ('true') : ('false'));
		}
		if (isset($data['PasportMO_IsAccompanying'])) {
			$data['PasportMO_IsAccompanying'] = (($data['PasportMO_IsAccompanying'] == '1') ? ('true') : ('false'));
		}
		
		if(empty($data['Lpu_begDate'])){
			$data['Lpu_begDate'] = ($resp[0]['Lpu_begDate']) ? date_format(date_create($resp[0]['Lpu_begDate']), 'Y-m-d H:i:s') : null;
		}
		if(empty($data['Lpu_RegDate'])){
			$data['Lpu_RegDate'] = ($resp[0]['Lpu_RegDate']) ? date_format(date_create($resp[0]['Lpu_RegDate']), 'Y-m-d H:i:s') : null;
		}
		if(empty($data['Lpu_endDate'])){
			$data['Lpu_endDate'] = ($resp[0]['Lpu_endDate']) ? date_format(date_create($resp[0]['Lpu_endDate']), 'Y-m-d H:i:s') : null;
		}
		
		$res_array = array_merge($resp[0],$data);
		$res_array['LpuAgeType_id'] = $res_array['MesAgeLpuType_id'];
		if(empty($res_array['Lpu_endDate'])){
			$res_array['Lpu_endDate'] = null;
		}
		
		$resp = $this->dbmodel->saveLpuPassport($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление периода по ОМС
	 */
	function LpuPeriodOMS_post() {
		$data = $this->ProcessInputData('createLpuPeriodOMS');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$data['LpuPeriodOMS_id'] = null;
		$data['LpuPeriodOMS_DogNum'] = null;
		$data['LpuPeriodOMS_RegNumC'] = null;
		$data['LpuPeriodOMS_RegNumN'] = null;

		$resp = $this->dbmodel->saveLpuPeriodOMS($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuPeriodOMS_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'LpuPeriodOMS_id'=>$resp[0]['LpuPeriodOMS_id']
			)
		));
	}

	/**
	 *  Изменение периода по ОМС
	 */
	function LpuPeriodOMS_put() {
		$data = $this->ProcessInputData('updateLpuPeriodOMS', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuPeriodOMS');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$data['filterByMO'] = true;
		$resp = $this->dbmodel->loadLpuPeriodOMS($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuPeriodOMS_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора периода ОМС',
				'error_code' => '6'
			));
		}
		if (!empty($resp[0]['LpuPeriodOMS_begDate'])) {
			$resp[0]['LpuPeriodOMS_begDate'] = date('Y-m-d', strtotime($resp[0]['LpuPeriodOMS_begDate']));
		} else {
			$resp[0]['LpuPeriodOMS_begDate'] = null;
		}
		if (!empty($resp[0]['LpuPeriodOMS_endDate'])) {
			$resp[0]['LpuPeriodOMS_endDate'] = date('Y-m-d', strtotime($resp[0]['LpuPeriodOMS_endDate']));
		} else {
			$resp[0]['LpuPeriodOMS_endDate'] = null;
		}
		$res_array = array_merge($resp[0], $data);

		$resp = $this->dbmodel->saveLpuPeriodOMS($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuPeriodOMS_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Получение списка периодов по ОМС в МО
	 */
	function LpuPeriodOMSlistByMo_get() {
		$data = $this->ProcessInputData('getLpuPeriodOMSlistByMo');

		$resp = $this->dbmodel->getLpuPeriodOMSlistForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение атрибутов периода по ОМС по идентификатору
	 */
	function LpuPeriodOMSbyId_get() {
		$data = $this->ProcessInputData('getLpuPeriodOMS', null, true);

		$resp = $this->dbmodel->getLpuPeriodOMSForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение списка периодов по ЛЛО в МО
	 */
	function LpuPeriodDLObyMo_get() {
		$data = $this->ProcessInputData('getLpuPeriodDLOlistByMo');

		$resp = $this->dbmodel->getLpuPeriodDLOlistForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение атрибутов периода по ЛЛО по идентификатору
	 */
	function LpuPeriodDLObyId_get() {
		$data = $this->ProcessInputData('getLpuPeriodDLO');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getLpuPeriodDLOForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка информационных систем по МО
	 */
	function MOInfoSysListByMo_get() {
		$data = $this->ProcessInputData('getMOInfoSysListByMo');

		$resp = $this->dbmodel->getMOInfoSysListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов информационной системы по идентификатору
	 */
	function MOInfoSysById_get() {
		$data = $this->ProcessInputData('getMOInfoSys', null, true);

		$resp = $this->dbmodel->getMOInfoSysForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка специализаций по МО
	 */
	function SpecializationMOListByMO_get() {
		$data = $this->ProcessInputData('getSpecializationMOListByMO');

		$resp = $this->dbmodel->getSpecializationMOListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов специализации по идентификатору
	 */
	function SpecializationMOById_get() {
		$data = $this->ProcessInputData('getSpecializationMO', null, true);

		$resp = $this->dbmodel->getSpecializationMOForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка медтехнологий по МО
	 */
	function MedTechnologyListByMO_get() {
		$data = $this->ProcessInputData('getMedTechnologyListByMO');

		$resp = $this->dbmodel->getMedTechnologyListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка медтехнологий по зданию
	 */
	function MedTechnologyListByLpuBuildingPass_get() {
		$data = $this->ProcessInputData('getMedTechnologyListByLpuBuildingPass', null, true);

		$resp = $this->dbmodel->getMedTechnologyListByLpuBuildingPassForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов медтехнологии по идентификатору
	 */
	function MedTechnologyById_get() {
		$data = $this->ProcessInputData('getMedTechnology', null, true);

		$resp = $this->dbmodel->getMedTechnologyForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка медицинских услуг по МО
	 */
	function MedUslugaListByMO_get() {
		$data = $this->ProcessInputData('getMedUslugaListByMO');

		$resp = $this->dbmodel->getMedUslugaListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов медицинской услуги по идентификатору
	 */
	function MedUslugaById_get() {
		$data = $this->ProcessInputData('getMedUsluga', null, true);

		$resp = $this->dbmodel->getMedUslugaForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка направлений оказания медицинской помощи по МО
	 */
	function UslugaComplexLpuListByMO_get() {
		$data = $this->ProcessInputData('getUslugaComplexLpuListByMO');

		$sp = getSessionParams();
		if($sp['Lpu_id'] != $data['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$resp = $this->dbmodel->getUslugaComplexLpuListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов направления оказания медицинской помощи по идентификатору
	 */
	function UslugaComplexLpuById_get() {
		$data = $this->ProcessInputData('getUslugaComplexLpu');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getUslugaComplexLpuForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка объектов/мест использования природного лечебного фактора по МО
	 */
	function PlfObjectsListByMO_get() {
		$data = $this->ProcessInputData('getPlfObjectsListByMO');

		$sp = getSessionParams();
		if($sp['Lpu_id'] != $data['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$resp = $this->dbmodel->getPlfObjectsListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов объекта/места использования природного лечебного фактора по идентификатору
	 */
	function PlfObjectsById_get() {
		$data = $this->ProcessInputData('getPlfObjects');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getPlfObjectsForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка периодов функционирования по МО
	 */
	function FunctionTimeListByMO_get() {
		$data = $this->ProcessInputData('getFunctionTimeListByMO');

		$sp = getSessionParams();
		if($sp['Lpu_id'] != $data['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$resp = $this->dbmodel->getFunctionTimeListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов периода функционирования по идентификатору
	 */
	function FunctionTimeById_get() {
		$data = $this->ProcessInputData('getFunctionTime');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getFunctionTimeForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка питаний по МО
	 */
	function PitanListByMO_get() {
		$data = $this->ProcessInputData('getPitanListByMO');

		$sp = getSessionParams();
		if($sp['Lpu_id'] != $data['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$resp = $this->dbmodel->getPitanListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов питания по идентификатору
	 */
	function PitanById_get() {
		$data = $this->ProcessInputData('getPitan');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getPitanForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка природных лечебных факторов по МО
	 */
	function PlfListByMO_get() {
		$data = $this->ProcessInputData('getPlfListByMO');

		$sp = getSessionParams();
		if($sp['Lpu_id'] != $data['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$resp = $this->dbmodel->getPlfListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов природного лечебного фактора по идентификатору
	 */
	function PlfById_get() {
		$data = $this->ProcessInputData('getPlf');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getPlfForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка территорий обслуживания
	 */
	function OrgServiceTerrListByMO_get() {
		$data = $this->ProcessInputData('getOrgServiceTerrListByMO', null, true);

		$resp = $this->dbmodel->getOrgServiceTerrListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов территории обслуживания по идентификатору
	 */
	function OrgServiceTerrById_get() {
		$data = $this->ProcessInputData('getOrgServiceTerr', null, true);

		$resp = $this->dbmodel->getOrgServiceTerrForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка руководства по МО
	 */
	function OrgHeadListByMO_get() {
		$data = $this->ProcessInputData('getOrgHeadListByMO');

		$sp = getSessionParams();
		if ( $data['Lpu_id'] != $sp['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$resp = $this->dbmodel->getOrgHeadListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение руководства организации по идентификатору
	 */
	function OrgHeadById_get() {
		$data = $this->ProcessInputData('getOrgHead');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getOrgHeadForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка заездов по МО
	 */
	function MOArrivalListByMO_get() {
		$data = $this->ProcessInputData('getMOArrivalListByMO');
		
		$sp = getSessionParams();
		if($sp['Lpu_id'] != $data['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$resp = $this->dbmodel->getMOArrivalListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов заезда по идентификатору
	 */
	function MOArrivalById_get() {
		$data = $this->ProcessInputData('getMOArrival');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getMOArrivalForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка округов горно-санитарной охраны
	 */
	function DisSanProtectionListByMO_get() {
		$data = $this->ProcessInputData('getDisSanProtectionListByMO');
		
		$sp = getSessionParams();
		if ( $data['Lpu_id'] != $sp['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$resp = $this->dbmodel->getDisSanProtectionListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов округа горно-санитарной охраны по идентификатору
	 */
	function DisSanProtectionById_get() {
		$data = $this->ProcessInputData('getDisSanProtection');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getDisSanProtectionForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка статусов курорта по МО
	 */
	function KurortStatusDocListByMO_get() {
		$data = $this->ProcessInputData('getKurortStatusDocListByMO');

		$sp = getSessionParams();
		if ( $data['Lpu_id'] != $sp['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$resp = $this->dbmodel->getKurortStatusDocListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрубутов статуса курорта по идентификатору
	 */
	function KurortStatusDocById_get() {
		$data = $this->ProcessInputData('getKurortStatusDoc');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getKurortStatusDocForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка типов курорта по МО
	 */
	function KurortTypeLinkListByMO_get() {
		$data = $this->ProcessInputData('getKurortTypeLinkListByMO');

		$sp = getSessionParams();
		if ( $data['Lpu_id'] != $sp['Lpu_id'] ) {
			$this->response(array('error_code' => 6, 'error_msg' => 'Данный метод доступен только для своей МО'));
		}
		$resp = $this->dbmodel->getKurortTypeLinkListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение типа курорта по идентификатору
	 */
	function KurortTypeLinkById_get() {
		$data = $this->ProcessInputData('getKurortTypeLink');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getKurortTypeLinkForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка объектов инфраструктуры по МО
	 */
	function MOAreaObjectListByMO_get() {
		$data = $this->ProcessInputData('getMOAreaObjectListByMO');

		$sp = getSessionParams();
		if ( $data['Lpu_id'] != $sp['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$resp = $this->dbmodel->getMOAreaObjectListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение объекта инфраструктуры по идентификатору
	 */
	function MOAreaObjectById_get() {
		$data = $this->ProcessInputData('getMOAreaObject');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getMOAreaObjectForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка площадок, занимаемых организацией
	 */
	function MOAreaListByMO_get() {
		$data = $this->ProcessInputData('getMOAreaListByMO');

		$sp = getSessionParams();
		if ( $data['Lpu_id'] != $sp['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$resp = $this->dbmodel->getMOAreaListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение идентификатора площадки по наименованию площадки и идентификатору участка
	 */
	function MOAreaByNameAndMember_get() {
		$data = $this->ProcessInputData('getMOAreaByNameAndMember');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->getMOAreaListByNameAndMemberForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов площадки, занимаемой организацией, по идентификатору
	 */
	function MOAreaById_get() {
		$data = $this->ProcessInputData('getMOArea');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getMOAreaForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка связей площадки с транспортными узлами
	 */
	function TransportConnectListByMOArea_get() {
		$data = $this->ProcessInputData('getTransportConnectListByMOArea');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getTransportConnectListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов связи с транспортными узлами по идентификатору
	 */
	function TransportConnectById_get() {
		$data = $this->ProcessInputData('getTransportConnect');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getTransportConnectForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка зданий МО
	 */
	function LpuBuildingPassListByMO_get() {
		$data = $this->ProcessInputData('getLpuBuildingPassListByMO');

		$resp = $this->dbmodel->getLpuBuildingPassListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов здания МО по идентификатору
	 */
	function LpuBuildingPassById_get() {
		$data = $this->ProcessInputData('getLpuBuildingPass');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getLpuBuildingPassForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение здания МО по номеру учета
	 */
	function LpuBuildingPassByBuildingIdent_get() {
		$data = $this->ProcessInputData('getLpuBuildingPassByBuildingIdent');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$data['forIdent'] = 1;

		$resp = $this->dbmodel->getLpuBuildingPassListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Удаление периода по ОМС
	 */
	function LpuPeriodOMS_delete() {
		$data = $this->ProcessInputData('deleteLpuPeriodOMS', null, true);

		$data['filterByMO'] = true;
		$resp = $this->dbmodel->loadLpuPeriodOMS($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuPeriodOMS_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора периода ОМС',
				'error_code' => '6'
			));
		}

		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'LpuPeriodOMS', false, array($data['LpuPeriodOMS_id']), 'dbo', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление периода по ДЛО
	 */
	function LpuPeriodDLO_post() {
		$data = $this->ProcessInputData('createLpuPeriodDLO');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$data['LpuPeriodDLO_id'] = null;
		$data['LpuPeriodDLO_Code'] = null;

		$resp = $this->dbmodel->saveLpuPeriodDLO($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuPeriodDLO_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'LpuPeriodDLO_id'=>$resp[0]['LpuPeriodDLO_id']
			)
		));
	}

	/**
	 *  Изменение периода по ДЛО
	 */
	function LpuPeriodDLO_put() {
		$data = $this->ProcessInputData('updateLpuPeriodDLO', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuPeriodDLO');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$data['filterByMO'] = true;
		$resp = $this->dbmodel->loadLpuPeriodDLO($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuPeriodDLO_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора периода ДЛО',
				'error_code' => '6'
			));
		}
		if (!empty($resp[0]['LpuPeriodDLO_begDate'])) {
			$resp[0]['LpuPeriodDLO_begDate'] = date('Y-m-d', strtotime($resp[0]['LpuPeriodDLO_begDate']));
		} else {
			$resp[0]['LpuPeriodDLO_begDate'] = null;
		}
		if (!empty($resp[0]['LpuPeriodDLO_endDate'])) {
			$resp[0]['LpuPeriodDLO_endDate'] = date('Y-m-d', strtotime($resp[0]['LpuPeriodDLO_endDate']));
		} else {
			$resp[0]['LpuPeriodDLO_endDate'] = null;
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->saveLpuPeriodDLO($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuPeriodDLO_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление периода по ДЛО
	 */
	function LpuPeriodDLO_delete() {
		$data = $this->ProcessInputData('deleteLpuPeriodDLO', null, true);

		$data['filterByMO'] = true;
		$resp = $this->dbmodel->loadLpuPeriodDLO($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuPeriodDLO_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора периода ДЛО',
				'error_code' => '6'
			));
		}

		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'LpuPeriodDLO', false, array($data['LpuPeriodDLO_id']), 'dbo', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Добавление объекта инфраструктуры
	 */
	function MOAreaObject_post() {
		$data = $this->ProcessInputData('createMOAreaObject');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$data['MOAreaObject_id'] = null;

		$resp = $this->dbmodel->saveMOAreaObject($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MOAreaObject_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'MOAreaObject_id'=>$resp[0]['MOAreaObject_id']
			)
		));
	}

	/**
	 * Изменение объекта инфраструктуры
	 */
	function MOAreaObject_put() {
		$data = $this->ProcessInputData('updateMOAreaObject', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createMOAreaObject');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadMOAreaObject($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MOAreaObject_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->saveMOAreaObject($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MOAreaObject_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление объекта инфраструктуры
	 */
	function MOAreaObject_delete() {
		$data = $this->ProcessInputData('deleteMOAreaObject', null, true);

		$resp = $this->dbmodel->loadMOAreaObject($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MOAreaObject_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}

		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'MOAreaObject', false, array($data['MOAreaObject_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Добавление площадки, занимаемой организацией
	 */
	function MOArea_post() {
		$data = $this->ProcessInputData('createMOArea');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];

		$data['MOArea_id'] = null;
		$data['MoArea_OKATO'] = null;
		$data['KLAreaType_id'] = null;

		$resp = $this->dbmodel->saveMOArea($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MOArea_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'MOArea_id'=>$resp[0]['MOArea_id']
			)
		));
	}

	/**
	 * Изменение площадки, занимаемой организацией
	 */
	function MOArea_put() {
		$data = $this->ProcessInputData('updateMOArea', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createMOAreat');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$data['KLAreaType_id'] = null;

		$resp = $this->dbmodel->loadMOArea($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MOArea_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}
		if (!empty($resp[0]['MoArea_OrgDT'])) {
			$resp[0]['MoArea_OrgDT'] = date('Y-m-d', strtotime($resp[0]['MoArea_OrgDT']));
		} else {
			$resp[0]['MoArea_OrgDT'] = null;
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->saveMOArea($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MOArea_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление площадки, занимаемой организацией
	 */
	function MOArea_delete() {
		$data = $this->ProcessInputData('deleteMOArea', null, true);

		$resp = $this->dbmodel->loadMOArea($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MOArea_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}

		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'MOArea', false, array($data['MOArea_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление периода по ДЛО
	 */
	function TransportConnect_post() {
		$data = $this->ProcessInputData('createTransportConnect', null, true);

		$resp = $this->dbmodel->loadMOArea($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MOArea_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора MOArea_id',
				'error_code' => '6'
			));
		}

		$data['TransportConnect_id'] = null;
		$data['TransportConnect_AreaIdent'] = null;

		$resp = $this->dbmodel->saveTransportConnect($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['TransportConnect_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'TransportConnect_id'=>$resp[0]['TransportConnect_id']
			)
		));
	}

	/**
	 *  Изменение периода по ДЛО
	 */
	function TransportConnect_put() {
		$data = $this->ProcessInputData('updateTransportConnect', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createTransportConnect');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadTransportConnect($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['TransportConnect_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->saveTransportConnect($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['TransportConnect_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление периода по ДЛО
	 */
	function TransportConnect_delete() {
		$data = $this->ProcessInputData('deleteTransportConnect', null, true);

		$resp = $this->dbmodel->loadTransportConnect($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['TransportConnect_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}

		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'TransportConnect', false, array($data['TransportConnect_id']), 'passport', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление здания МО
	 */
	function LpuBuildingPass_post() {
		$data = $this->ProcessInputData('createLpuBuildingPass');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$data['LpuBuildingPass_id'] = null;
		$data['LpuBuildingPass_RespProtect'] = null;
		$data['LpuBuildingPass_Number'] = null;
		$data['LpuBuildingPass_Project'] = null;
		$data['LpuBuildingPass_WorkArea'] = null;
		$data['LpuBuildingPass_RegionArea'] = null;
		$data['LpuBuildingPass_WorkAreaWardSect'] = null;
		$data['LpuBuildingPass_WorkAreaWard'] = null;
		$data['LpuBuildingPass_OfficeCount'] = null;
		$data['LpuBuildingPass_IsPhone'] = null;
		$data['LpuBuildingPass_IsHeat'] = null;
		$data['LpuBuildingPass_IsHotWater'] = null;
		$data['LpuBuildingPass_IsSewerage'] = null;
		$data['LpuBuildingPass_HostLiftReplace'] = null;
		$data['LpuBuildingPass_PassLiftReplace'] = null;
		$data['LpuBuildingPass_TechLift'] = null;
		$data['LpuBuildingPass_TechLiftReplace'] = null;
		$data['LpuBuildingPass_IsInsulFacade'] = null;
		$data['LpuBuildingPass_IsFireAlarm'] = null;
		$data['LpuBuildingPass_IsHeatMeters'] = null;
		$data['LpuBuildingPass_IsWaterMeters'] = null;
		$data['LpuBuildingPass_IsRequirImprovement'] = null;
		$data['LpuBuildingPass_IsDetached'] = null;

		$resp = $this->dbmodel->saveLpuBuilding($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuBuildingPass_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'LpuBuildingPass_id'=>$resp[0]['LpuBuildingPass_id']
			)
		));
	}

	/**
	 *  Изменение здания МО
	 */
	function LpuBuildingPass_put() {
		$data = $this->ProcessInputData('updateLpuBuildingPass', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuBuildingPass');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$data['LpuBuildingPass_RespProtect'] = null;
		$data['LpuBuildingPass_Number'] = null;
		$data['LpuBuildingPass_Project'] = null;
		$data['LpuBuildingPass_WorkArea'] = null;
		$data['LpuBuildingPass_RegionArea'] = null;
		$data['LpuBuildingPass_WorkAreaWardSect'] = null;
		$data['LpuBuildingPass_WorkAreaWard'] = null;
		$data['LpuBuildingPass_OfficeCount'] = null;
		$data['LpuBuildingPass_IsPhone'] = null;
		$data['LpuBuildingPass_IsHeat'] = null;
		$data['LpuBuildingPass_IsHotWater'] = null;
		$data['LpuBuildingPass_IsSewerage'] = null;
		$data['LpuBuildingPass_HostLiftReplace'] = null;
		$data['LpuBuildingPass_PassLiftReplace'] = null;
		$data['LpuBuildingPass_TechLift'] = null;
		$data['LpuBuildingPass_TechLiftReplace'] = null;
		$data['LpuBuildingPass_IsInsulFacade'] = null;
		$data['LpuBuildingPass_IsFireAlarm'] = null;
		$data['LpuBuildingPass_IsHeatMeters'] = null;
		$data['LpuBuildingPass_IsWaterMeters'] = null;
		$data['LpuBuildingPass_IsRequirImprovement'] = null;
		$data['LpuBuildingPass_IsDetached'] = null;

		$resp = $this->dbmodel->getLpuBuildingPassForAPIPut($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}
		unset($resp[0]['LpuBuildingPass_endDate']);
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->saveLpuBuilding($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuBuildingPass_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление здания МО
	 */
	function LpuBuildingPass_delete() {
		$data = $this->ProcessInputData('deleteLpuBuildingPass', null, true);

		$resp = $this->dbmodel->loadLpuBuilding($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuBuildingPass_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}

		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'LpuBuildingPass', false, array($data['LpuBuildingPass_id']), 'dbo', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление информационной системы
	 */
	function MOInfoSys_post() {
		$data = $this->ProcessInputData('createMOInfoSys');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$data['MOInfoSys_id'] = null;

		$resp = $this->dbmodel->saveMOInfoSys($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MOInfoSys_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'MOInfoSys_id'=>$resp[0]['MOInfoSys_id']
			)
		));
	}

	/**
	 *  Изменение информационной системы
	 */
	function MOInfoSys_put() {
		$data = $this->ProcessInputData('updateMOInfoSys', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createMOInfoSys');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadMOInfoSysById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MOInfoSys_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора информационной системы',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->saveMOInfoSys($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MOInfoSys_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление информационной системы
	 */
	function MOInfoSys_delete() {
		$data = $this->ProcessInputData('deleteMOInfoSys', null, true);

		$resp = $this->dbmodel->loadMOInfoSysById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MOInfoSys_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора информационной системы',
				'error_code' => '6'
			));
		}

		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'MOInfoSys', false, array($data['MOInfoSys_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление специализации организации
	 */
	function SpecializationMO_post() {
		$data = $this->ProcessInputData('createSpecializationMO');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$data['SpecializationMO_id'] = null;
		$data['Mkb10Code_id'] = null;
		$data['Mkb10CodeClass_id'] = $data['Mkb10Code_cid'];

		$resp = $this->dbmodel->saveSpecializationMO($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['SpecializationMO_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'SpecializationMO_id'=>$resp[0]['SpecializationMO_id']
			)
		));
	}

	/**
	 *  Изменение специализации организации
	 */
	function SpecializationMO_put() {
		$data = $this->ProcessInputData('updateSpecializationMO', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createSpecializationMO');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadSpecializationMOById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['SpecializationMO_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора специализации',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);
		$res_array['Mkb10CodeClass_id'] = $res_array['Mkb10Code_cid'];

		$resp = $this->dbmodel->saveSpecializationMO($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['SpecializationMO_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление специализации организации
	 */
	function SpecializationMO_delete() {
		$data = $this->ProcessInputData('deleteSpecializationMO', null, true);

		$resp = $this->dbmodel->loadSpecializationMOById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['SpecializationMO_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора специализации',
				'error_code' => '6'
			));
		}

		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'SpecializationMO', false, array($data['SpecializationMO_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление лицензии МО
	 */
	function LpuLicence_post() {
		$data = $this->ProcessInputData('createLpuLicence');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];

		$data['LpuLicence_id'] = null;
		$data['VidDeat_id'] = null;

		if(!empty($data['Org_id'])){
			$this->load->model('Org_model', 'orgmodel');
			$list = $this->orgmodel->getOrgList(array('Org_id'=>$data['Org_id']));
			if(empty($list[0]['Org_id'])){
				$this->response(array(
					'error_msg' => 'Org_id: Данной организации нет в справочнике Огранизаций',
					'error_code' => '6'
				));
			}
		}

		$resp = $this->dbmodel->saveLpuLicence($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuLicence_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'LpuLicence_id'=>$resp[0]['LpuLicence_id']
			)
		));
	}

	/**
	 *  Изменение лицензии МО
	 */
	function LpuLicence_put() {
		$data = $this->ProcessInputData('updateLpuLicence', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuLicence');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadLpuLicenceById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuLicence_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора лицензии',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		if(!empty($data['Org_id'])){
			$this->load->model('Org_model', 'orgmodel');
			$list = $this->orgmodel->getOrgList(array('Org_id'=>$data['Org_id']));
			if(empty($list[0]['Org_id'])){
				$this->response(array(
					'error_msg' => 'Org_id: Данной организации нет в справочнике Огранизаций',
					'error_code' => '6'
				));
			}
		}

		$resp = $this->dbmodel->saveLpuLicence($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuLicence_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление лицензии МО
	 */
	function LpuLicence_delete() {
		$data = $this->ProcessInputData('deleteLpuLicence', null, true);

		$resp = $this->dbmodel->loadLpuLicenceById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuLicence_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора лицензии',
				'error_code' => '6'
			));
		}
		$data['Lpu_id'] = $resp[0]['Lpu_id'];
		$data['fromAPI'] = 1;

		$resp = $this->dbmodel->deleteLpuLicence($data);
		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Получение списка лицензий по МО
	 */
	function LpuLicenceListByMO_get() {
		$data = $this->ProcessInputData('getLpuLicenceByLpu');
		$res = array();
		$resp = $this->dbmodel->loadLpuLicenceGrid($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else if(count($resp)>0){
			foreach ($resp as $value) {
				if(!empty($value['LpuLicence_id'])){
					array_push($res, array('LpuLicence_id'=>$value['LpuLicence_id']));
				}
			}
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $res
		));
	}

	/**
	 *  Получение списка лицензий по МО, номеру лицензии, выдавей организации и дате выдачи
	 */
	function LpuLicenceByMOLicenceNumSetOrgSetDate_get() {
		$data = $this->ProcessInputData('getLpuLicenceByLpuLicenceNumSetOrgSetDate');
		$res = array();
		$resp = $this->dbmodel->loadLpuLicenceGrid($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else if(count($resp)>0){
			foreach ($resp as $value) {
				if(!empty($value['LpuLicence_id'])){
					array_push($res, array('LpuLicence_id'=>$value['LpuLicence_id']));
				}
			}
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $res
		));
	}

	/**
	 *  Получение атрибутов лицензии МО по идентификатору
	 */
	function LpuLicenceById_get() {
		$data = $this->ProcessInputData('getLpuLicence', null, true);
		$res = array();
		$resp = $this->dbmodel->loadLpuLicenceById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else if(!empty($resp[0]['LpuLicence_id'])){
			$res[0] = array(
				'LpuLicence_Ser' => $resp[0]['LpuLicence_Ser'],
				'LpuLicence_RegNum' => $resp[0]['LpuLicence_RegNum'],
				'LpuLicence_Num' => $resp[0]['LpuLicence_Num'],
				'Org_id' => $resp[0]['Org_id'],
				'LpuLicence_setDate' => $resp[0]['LpuLicence_setDate'],
				'LpuLicence_begDate' => $resp[0]['LpuLicence_begDate'],
				'LpuLicence_endDate' => $resp[0]['LpuLicence_endDate'],
				'KLCountry_id' => $resp[0]['KLCountry_id'],
				'KLRgn_id' => $resp[0]['KLRgn_id'],
				'KLSubRgn_id' => $resp[0]['KLSubRgn_id'],
				'KLCity_id' => $resp[0]['KLCity_id'],
				'KLTown_id' => $resp[0]['KLTown_id']
			);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $res
		));
	}

	/**
	 *  Добавление вида лицензии по профилю
	 */
	function LpuLicenceProfile_post() {
		$data = $this->ProcessInputData('createLpuLicenceProfile', null, true);

		$resp = $this->dbmodel->loadLpuLicenceById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuLicence_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора лицензии',
				'error_code' => '6'
			));
		}

		$data['LpuLicenceProfile_id'] = null;
	
		$resp = $this->dbmodel->saveLpuLicenceProfile($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuLicenceProfile_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'LpuLicenceProfile_id'=>$resp[0]['LpuLicenceProfile_id']
			)
		));
	}

	/**
	 *  Изменение вида лицензии по профилю
	 */
	function LpuLicenceProfile_put() {
		$data = $this->ProcessInputData('updateLpuLicenceProfile', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuLicenceProfile');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadLpuLicenceProfileById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuLicenceProfile_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора вида лицензии',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->saveLpuLicenceProfile($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuLicenceProfile_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление вида лицензии по профилю
	 */
	function LpuLicenceProfile_delete() {
		$data = $this->ProcessInputData('deleteLpuLicenceProfile', null, true);

		$resp = $this->dbmodel->loadLpuLicenceProfileById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuLicenceProfile_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора вида лицензии',
				'error_code' => '6'
			));
		}

		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'LpuLicenceProfile', false, array($data['LpuLicenceProfile_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Получение списка видов профилей по лицензии
	 */
	function LpuLicenceProfileListByLicence_get() {
		$data = $this->ProcessInputData('deleteLpuLicence');
		$res = array();
		$resp = $this->dbmodel->loadLpuLicenceProfile($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else if(count($resp)>0){
			foreach ($resp as $key => $value) {
				$res[$key] = array('LpuLicenceProfile_id'=>null,'LpuLicenceProfileType_id'=>null);
				if(!empty($value['LpuLicenceProfile_id'])){
					$res[$key]['LpuLicenceProfile_id'] = $value['LpuLicenceProfile_id'];
				}
				if(!empty($value['LpuLicenceProfileType_id'])){
					$res[$key]['LpuLicenceProfileType_id'] = $value['LpuLicenceProfileType_id'];
				}
			}
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $res
		));
	}

	/**
	 *  Добавление операции, проведенной с лицензией
	 */
	function LpuLicenceOperationLink_post() {
		$data = $this->ProcessInputData('createLpuLicenceOperationLink', null, true);

		$resp = $this->dbmodel->loadLpuLicenceById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuLicence_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора лицензии',
				'error_code' => '6'
			));
		}

		$data['LpuLicenceOperationLink_id'] = null;
	
		$resp = $this->dbmodel->saveLpuLicenceOperationLink($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuLicenceOperationLink_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'LpuLicenceOperationLink_id'=>$resp[0]['LpuLicenceOperationLink_id']
			)
		));
	}

	/**
	 *  Изменение операции, проведенной с лицензией
	 */
	function LpuLicenceOperationLink_put() {
		$data = $this->ProcessInputData('updateLpuLicenceOperationLink', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createLpuLicenceOperationLink');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadLpuLicenceOperationLinkById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuLicenceOperationLink_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора операции',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->saveLpuLicenceOperationLink($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuLicenceOperationLink_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление операции, проведенной с лицензией
	 */
	function LpuLicenceOperationLink_delete() {
		$data = $this->ProcessInputData('deleteLpuLicenceOperationLink', null, true);

		$resp = $this->dbmodel->loadLpuLicenceOperationLinkById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['LpuLicenceOperationLink_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора операции',
				'error_code' => '6'
			));
		}

		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'LpuLicenceOperationLink', false, array($data['LpuLicenceOperationLink_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Получение списка операций, проведенных с лицензией
	 */
	function LpuLicenceOperationLinkListByLicence_get() {
		$data = $this->ProcessInputData('deleteLpuLicence');
		
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$res = array();
		$resp = $this->dbmodel->loadLpuLicenceOperationLink($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else if(count($resp)>0){
			$fields = array('LpuLicenceOperationLink_id','LpuLicenceOperationLink_Date','LicsOperation_id');
			foreach ($resp as $key => $value) {
				$res[$key] = array();
				foreach ($fields as $field) {
					if(!empty($value[$field])){
						$res[$key][$field] = $value[$field];
					} else {
						$res[$key][$field] = null;
					}
				}
			}
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $res
		));
	}

	/**
	 *  Добавление медтехнологии
	 */
	function MedTechnology_post() {
		$data = $this->ProcessInputData('createMedTechnology');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['MedTechnology_id'] = null;
	
		$resp = $this->dbmodel->saveMedTechnology($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MedTechnology_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'MedTechnology_id'=>$resp[0]['MedTechnology_id']
			)
		));
	}

	/**
	 *  Изменение медтехнологии
	 */
	function MedTechnology_put() {
		$data = $this->ProcessInputData('updateMedTechnology', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createMedTechnology');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadMedTechnologyById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MedTechnology_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора медтехнологии',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->saveMedTechnology($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MedTechnology_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление медтехнологии
	 */
	function MedTechnology_delete() {
		$data = $this->ProcessInputData('deleteMedTechnology', null, true);

		$resp = $this->dbmodel->loadMedTechnologyById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MedTechnology_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора медтехнологии',
				'error_code' => '6'
			));
		}

		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'MedTechnology', false, array($data['MedTechnology_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление медицинской услуги в паспорт МО
	 */
	function MedUsluga_post() {
		$data = $this->ProcessInputData('createMedUsluga');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['MedUsluga_id'] = null;

		$resp = $this->dbmodel->saveMedUsluga($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MedUsluga_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'MedUsluga_id'=>$resp[0]['MedUsluga_id']
			)
		));
	}

	/**
	 *  Изменение медицинской услуги в паспорте МО
	 */
	function MedUsluga_put() {
		$data = $this->ProcessInputData('updateMedUsluga', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createMedUsluga');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadMedUslugaById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MedUsluga_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора услуги',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->saveMedUsluga($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MedUsluga_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление медицинской услуги в паспорте МО
	 */
	function MedUsluga_delete() {
		$data = $this->ProcessInputData('deleteMedUsluga', null, true);

		$resp = $this->dbmodel->loadMedUslugaById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MedUsluga_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора услуги',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'MedUsluga', false, array($data['MedUsluga_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление направления оказания медицинской помощи
	 */
	function UslugaComplexLpu_post() {
		$data = $this->ProcessInputData('createUslugaComplexLpu');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['UslugaComplexLpu_id'] = null;

		$resp = $this->dbmodel->saveUslugaComplexLpu($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['UslugaComplexLpu_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'UslugaComplexLpu_id'=>$resp[0]['UslugaComplexLpu_id']
			)
		));
	}

	/**
	 *  Изменение направления оказания медицинской помощи
	 */
	function UslugaComplexLpu_put() {
		$data = $this->ProcessInputData('updateUslugaComplexLpu', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createUslugaComplexLpu');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadUslugaComplexLpuById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['UslugaComplexLpu_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора направления',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->saveUslugaComplexLpu($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['UslugaComplexLpu_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление направления оказания медицинской помощи
	 */
	function UslugaComplexLpu_delete() {
		$data = $this->ProcessInputData('deleteUslugaComplexLpu', null, true);

		$resp = $this->dbmodel->loadUslugaComplexLpuById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['UslugaComplexLpu_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора направления',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'UslugaComplexLpu', false, array($data['UslugaComplexLpu_id']), 'passport', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление объекта/места использования природного лечебного фактора
	 */
	function PlfObjects_post() {
		$data = $this->ProcessInputData('createPlfObjects');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['PlfObjectCount_id'] = null;

		$resp = $this->dbmodel->savePlfObjectCount($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['PlfObjectCount_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'PlfObjectCount_id'=>$resp[0]['PlfObjectCount_id']
			)
		));
	}

	/**
	 *  Изменение объекта/места использования природного лечебного фактора
	 */
	function PlfObjects_put() {
		$data = $this->ProcessInputData('updatePlfObjects', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createPlfObjects');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadPlfObjectCountBuId($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['PlfObjectCount_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора места/объекта',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->savePlfObjectCount($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['PlfObjectCount_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление объекта/места использования природного лечебного фактора
	 */
	function PlfObjects_delete() {
		$data = $this->ProcessInputData('deletePlfObjects', null, true);

		$resp = $this->dbmodel->loadPlfObjectCountBuId($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['PlfObjectCount_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора места/объекта',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'PlfObjectCount', false, array($data['PlfObjectCount_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление периода функционирования МО 
	 */
	function FunctionTime_post() {
		$data = $this->ProcessInputData('createFunctionTime');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['FunctionTime_id'] = null;

		$resp = $this->dbmodel->saveFunctionTime($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['FunctionTime_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'FunctionTime_id'=>$resp[0]['FunctionTime_id']
			)
		));
	}

	/**
	 *  Изменение периода функционирования МО 
	 */
	function FunctionTime_put() {
		$data = $this->ProcessInputData('updateFunctionTime', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createFunctionTime');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadFunctionTimeById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['FunctionTime_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора периода',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->saveFunctionTime($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['FunctionTime_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление периода функционирования МО 
	 */
	function FunctionTime_delete() {
		$data = $this->ProcessInputData('deleteFunctionTime', null, true);

		$resp = $this->dbmodel->loadFunctionTimeById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['FunctionTime_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора периода',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'FunctionTime', false, array($data['FunctionTime_id']), 'passport', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление питания
	 */
	function Pitan_post() {
		$data = $this->ProcessInputData('createPitan');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['PitanFormTypeLink_id'] = null;

		$resp = $this->dbmodel->savePitanFormTypeLink($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['PitanFormTypeLink_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'PitanFormTypeLink_id'=>$resp[0]['PitanFormTypeLink_id']
			)
		));
	}

	/**
	 *  Изменение питания
	 */
	function Pitan_put() {
		$data = $this->ProcessInputData('updatePitan', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createPitan');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadPitanFormTypeLinkById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['PitanFormTypeLink_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->savePitanFormTypeLink($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['PitanFormTypeLink_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление питания
	 */
	function Pitan_delete() {
		$data = $this->ProcessInputData('deletePitan', null, true);

		$resp = $this->dbmodel->loadPitanFormTypeLinkById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['PitanFormTypeLink_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'PitanFormTypeLink', false, array($data['PitanFormTypeLink_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление природного лечебного фактора
	 */
	function Plf_post() {
		$data = $this->ProcessInputData('createPlf');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['PlfDocTypeLink_id'] = null;

		$resp = $this->dbmodel->savePlfDocTypeLink($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['PlfDocTypeLink_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'PlfDocTypeLink_id'=>$resp[0]['PlfDocTypeLink_id']
			)
		));
	}

	/**
	 *  Изменение природного лечебного фактора
	 */
	function Plf_put() {
		$data = $this->ProcessInputData('updatePlf', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createPlf');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadPlfDocTypeLinkById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['PlfDocTypeLink_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора фактора',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0],$data);

		$resp = $this->dbmodel->savePlfDocTypeLink($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['PlfDocTypeLink_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление природного лечебного фактора
	 */
	function Plf_delete() {
		$data = $this->ProcessInputData('deletePlf', null, true);

		$resp = $this->dbmodel->loadPlfDocTypeLinkById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['PlfDocTypeLink_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора фактора',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'PlfDocTypeLink', false, array($data['PlfDocTypeLink_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление расчетного счета
	 */
	function OrgRSchet_post() {
		$this->load->model('Org_model', 'orgmodel');
		$data = $this->ProcessInputData($this->orgmodel->inputRules['createOrgRSchet']);
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];
		$data['OrgRSchet_id'] = null;

		if ($data['Org_id'] != $sp['session']['org_id']) {
			$this->response(array(
				'error_msg' => 'Данный метод доступен только для своей организации',
				'error_code' => '6'
			));
		}
		
		$resp = $this->orgmodel->saveOrgRSchet($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['OrgRSchet_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'OrgRSchet_id'=>$resp[0]['OrgRSchet_id']
			)
		));
	}

	/**
	 *  Изменение расчетного счета
	 */
	function OrgRSchet_put() {
		$this->load->model('Org_model', 'orgmodel');
		$data = $this->ProcessInputData($this->orgmodel->inputRules['updateOrgRSchet'], null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields($this->orgmodel->inputRules['createOrgRSchet']);
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$data['fromAPI'] = 1;
		
		$resp = $this->orgmodel->loadOrgRSchet($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['OrgRSchet_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора счета',
				'error_code' => '6'
			));
		}
		if (!empty($resp[0]['OrgRSchet_begDate'])) {
			$resp[0]['OrgRSchet_begDate'] = date('Y-m-d', strtotime($resp[0]['OrgRSchet_begDate']));
		} else {
			$resp[0]['OrgRSchet_begDate'] = null;
		}
		if (!empty($resp[0]['OrgRSchet_endDate'])) {
			$resp[0]['OrgRSchet_endDate'] = date('Y-m-d', strtotime($resp[0]['OrgRSchet_endDate']));
		} else {
			$resp[0]['OrgRSchet_endDate'] = null;
		}
		$res_array = array_merge($resp[0], $data);

		$resp = $this->orgmodel->saveOrgRSchet($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['OrgRSchet_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление расчетного счета
	 */
	function OrgRSchet_delete() {
		$this->load->model('Org_model', 'orgmodel');
		$data = $this->ProcessInputData($this->orgmodel->inputRules['deleteOrgRSchet'], null, true);

		$data['fromAPI'] = 1;

		$resp = $this->orgmodel->loadOrgRSchet($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['OrgRSchet_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора счета',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'OrgRSchet', false, array($data['OrgRSchet_id']), 'dbo', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Получение списка расчетных счетов по МО
	 */
	function OrgRSchetListByMO_get() {
		$this->load->model('Org_model', 'orgmodel');
		$data = $this->ProcessInputData($this->orgmodel->inputRules['getOrgRSchetList']);
		$res = array();
		
		$sp = getSessionParams();
		if($sp['Lpu_id'] != $data['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$resp = $this->orgmodel->loadOrgRSchetListForAPI(array('Lpu_id' => $data['Lpu_id']));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else if(count($resp)>0){
			foreach ($resp as $key => $value) {
				array_push($res, array(
					'OrgRSchet_id' => $value['OrgRSchet_id'],
					'OrgRSchet_RSchet' => $value['OrgRSchet_RSchet'],
					'OrgBank_id' => $value['OrgBank_id'],
					'Okv_id' => $value['Okv_id'],
					'OrgRSchet_Name' => $value['OrgRSchet_Name'],
					'OrgRSchet_begDate' => $value['OrgRSchet_begDate'],
					'OrgRSchet_endDate' => $value['OrgRSchet_endDate']
				));
			}
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $res
		));
	}

	/**
	 *  Получение атрибутов расчетного счета по идентификатору
	 */
	function OrgRSchetByMO_get() {
		$this->load->model('Org_model', 'orgmodel');
		$data = $this->ProcessInputData($this->orgmodel->inputRules['deleteOrgRSchet']);
		
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->orgmodel->loadOrgRSchetListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['OrgRSchet_id'])) {
			$resp[0] = array();
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp[0]
		));
	}

	/**
	 *  Добавление КБК для расчетного счета
	 */
	function OrgRSchetKBK_post() {
		$this->load->model('Org_model', 'orgmodel');
		$data = $this->ProcessInputData($this->orgmodel->inputRules['createOrgRSchetKBK'], null, true);
		$data['OrgRSchetKBK_id'] = null;

		$data['fromAPI'] = 1;

		$resp = $this->orgmodel->loadOrgRSchet($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['OrgRSchet_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора счета',
				'error_code' => '6'
			));
		}
		
		$resp = $this->orgmodel->saveOrgRSchetKBK($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['OrgRSchetKBK_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'OrgRSchetKBK_id'=>$resp[0]['OrgRSchetKBK_id']
			)
		));
	}

	/**
	 *  Изменение КБК для расчетного счета
	 */
	function OrgRSchetKBK_put() {
		$this->load->model('Org_model', 'orgmodel');
		$data = $this->ProcessInputData($this->orgmodel->inputRules['updateOrgRSchetKBK'], null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields($this->orgmodel->inputRules['createOrgRSchetKBK']);
		$data = $this->unsetEmptyFields($data, $requiredFields);
		
		$data['fromAPI'] = 1;

		$resp = $this->orgmodel->loadOrgRSchetKBK($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['OrgRSchetKBK_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0], $data);

		$resp = $this->orgmodel->saveOrgRSchetKBK($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['OrgRSchetKBK_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление КБК для расчетного счета
	 */
	function OrgRSchetKBK_delete() {
		$this->load->model('Org_model', 'orgmodel');
		$data = $this->ProcessInputData($this->orgmodel->inputRules['deleteOrgRSchetKBK'], null, true);

		$data['fromAPI'] = 1;

		$resp = $this->orgmodel->loadOrgRSchetKBK($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['OrgRSchetKBK_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'OrgRSchetKBK', false, array($data['OrgRSchetKBK_id']), 'dbo', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Получение списка КБК по расчетному счету
	 */
	function OrgRSchetKBKListByRSchet_get() {
		$this->load->model('Org_model', 'orgmodel');
		$data = $this->ProcessInputData($this->orgmodel->inputRules['deleteOrgRSchet']);
		$res = array();
		
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$data['fromAPI'] = 1;
		$resp = $this->orgmodel->loadOrgRSchetKBKGrid($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Добавление территории обслуживания
	 */
	function OrgServiceTerr_post() {
		$this->load->model('OrgServiceTerr_model', 'orgservmodel');
		$data = $this->ProcessInputData($this->orgservmodel->inputRules['createOrgServiceTerr']);
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['OrgServiceTerr_id'] = null;

		if ($data['Org_id'] != $sp['session']['org_id']) {
			$this->response(array(
				'error_msg' => 'Данный метод доступен только для своей организации',
				'error_code' => '6'
			));
		}
		
		$resp = $this->orgservmodel->saveOrgServiceTerr($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['OrgServiceTerr_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'OrgServiceTerr_id'=>$resp[0]['OrgServiceTerr_id']
			)
		));
	}

	/**
	 *  Изменение территории обслуживания
	 */
	function OrgServiceTerr_put() {
		$this->load->model('OrgServiceTerr_model', 'orgservmodel');
		$data = $this->ProcessInputData($this->orgservmodel->inputRules['updateOrgServiceTerr'], null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields($this->orgservmodel->inputRules['createOrgServiceTerr']);
		$data = $this->unsetEmptyFields($data, $requiredFields);
		
		$resp = $this->orgservmodel->loadOrgServiceTerrEditForm($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['OrgServiceTerr_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора территории',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0], $data);

		$resp = $this->orgservmodel->saveOrgServiceTerr($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['OrgServiceTerr_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление территории обслуживания
	 */
	function OrgServiceTerr_delete() {
		$this->load->model('OrgServiceTerr_model', 'orgservmodel');
		$data = $this->ProcessInputData($this->orgservmodel->inputRules['deleteOrgServiceTerr'], null, true);

		$resp = $this->orgservmodel->loadOrgServiceTerrEditForm($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['OrgServiceTerr_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора территории',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'OrgServiceTerr', false, array($data['OrgServiceTerr_id']), 'dbo', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление руководства организации
	 */
	function OrgHead_post() {
		$this->load->model('Org_model', 'orgmodel');
		$data = $this->ProcessInputData($this->orgmodel->inputRules['createOrgHead']);
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];
		$data['OrgHead_id'] = null;
		$data['LpuUnit_id'] = null;
		$data['OrgHead_Mobile'] = null;
		$data['OrgHead_CommissNum'] = null;
		$data['OrgHead_Address'] = null;
		
		$resp = $this->orgmodel->saveOrgHead($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['OrgHead_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'OrgHead_id'=>$resp[0]['OrgHead_id']
			)
		));
	}

	/**
	 *  Изменение руководства организации
	 */
	function OrgHead_put() {
		$this->load->model('Org_model', 'orgmodel');
		$data = $this->ProcessInputData($this->orgmodel->inputRules['updateOrgHead'], null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields($this->orgmodel->inputRules['createOrgHead']);
		$data = $this->unsetEmptyFields($data, $requiredFields);
		
		$resp = $this->orgmodel->loadOrgHead($data);
		if ( $data['Lpu_id'] != $resp[0]['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['OrgHead_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}
		if (!empty($resp[0]['OrgHead_CommissDate'])) {
			$resp[0]['OrgHead_CommissDate'] = date('Y-m-d', $resp[0]['OrgHead_CommissDate']);
		} else {
			$resp[0]['OrgHead_CommissDate'] = null;
		}
		$res_array = array_merge($resp[0], $data);

		$resp = $this->orgmodel->saveOrgHead($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['OrgHead_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление руководства организации
	 */
	function OrgHead_delete() {
		$this->load->model('Org_model', 'orgmodel');
		$data = $this->ProcessInputData($this->orgmodel->inputRules['deleteOrgHead'], null, true);

		$resp = $this->orgmodel->loadOrgHead($data);
		if ( $data['Lpu_id'] != $resp[0]['Lpu_id'] ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['OrgHead_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}

		$resp = $this->orgmodel->deleteOrgHead($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление заезда
	 */
	function MOArrival_post() {
		$data = $this->ProcessInputData('createMOArrival');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['MOArrival_id'] = null;

		$resp = $this->dbmodel->saveMOArrival($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MOArrival_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'MOArrival_id'=>$resp[0]['MOArrival_id']
			)
		));
	}

	/**
	 *  Изменение заезда
	 */
	function MOArrival_put() {
		$data = $this->ProcessInputData('updateMOArrival', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createMOArrival');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadMOArrivalById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MOArrival_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора заезда',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0], $data);

		$resp = $this->dbmodel->saveMOArrival($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MOArrival_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление заезда
	 */
	function MOArrival_delete() {
		$data = $this->ProcessInputData('deleteMOArrival', null, true);

		$resp = $this->dbmodel->loadMOArrivalById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['MOArrival_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора заезда',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'MOArrival', false, array($data['MOArrival_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление округа горно-санитарной охраны
	 */
	function DisSanProtection_post() {
		$data = $this->ProcessInputData('createDisSanProtection');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['DisSanProtection_id'] = null;

		$resp = $this->dbmodel->saveDisSanProtection($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['DisSanProtection_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'DisSanProtection_id'=>$resp[0]['DisSanProtection_id']
			)
		));
	}

	/**
	 *  Изменение округа горно-санитарной охраны
	 */
	function DisSanProtection_put() {
		$data = $this->ProcessInputData('updateDisSanProtection', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createDisSanProtection');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadDisSanProtectionById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['DisSanProtection_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора округа',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0], $data);

		$resp = $this->dbmodel->saveDisSanProtection($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['DisSanProtection_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление округа горно-санитарной охраны
	 */
	function DisSanProtection_delete() {
		$data = $this->ProcessInputData('deleteDisSanProtection', null, true);

		$resp = $this->dbmodel->loadDisSanProtectionById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['DisSanProtection_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора округа',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'DisSanProtection', false, array($data['DisSanProtection_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление статуса курорта
	 */
	function KurortStatusDoc_post() {
		$data = $this->ProcessInputData('createKurortStatusDoc');

		if(!empty($data['KurortStatusDoc_IsStatus']) && $data['KurortStatusDoc_IsStatus'] == 2){
			$data['KurortStatusDoc_IsStatus'] = 'on';
		} else {
			$data['KurortStatusDoc_IsStatus'] = 1;
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['KurortStatusDoc_id'] = null;

		$resp = $this->dbmodel->saveKurortStatus($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['KurortStatusDoc_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'KurortStatusDoc_id'=>$resp[0]['KurortStatusDoc_id']
			)
		));
	}

	/**
	 *  Изменение статуса курорта
	 */
	function KurortStatusDoc_put() {
		$data = $this->ProcessInputData('updateKurortStatusDoc', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createKurortStatusDoc');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadKurortStatusById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['KurortStatusDoc_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора статуса',
				'error_code' => '6'
			));
		}

		$res_array = array_merge($resp[0], $data);

		if(!empty($res_array['KurortStatusDoc_IsStatus']) && $res_array['KurortStatusDoc_IsStatus'] == 2){
			$res_array['KurortStatusDoc_IsStatus'] = 'on';
		} else {
			$res_array['KurortStatusDoc_IsStatus'] = 1;
		}

		$resp = $this->dbmodel->saveKurortStatus($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['KurortStatusDoc_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление статуса курорта
	 */
	function KurortStatusDoc_delete() {
		$data = $this->ProcessInputData('deleteKurortStatusDoc', null, true);

		$resp = $this->dbmodel->loadKurortStatusById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['KurortStatusDoc_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора статуса',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'KurortStatusDoc', false, array($data['KurortStatusDoc_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Добавление типа курорта
	 */
	function KurortTypeLink_post() {
		$data = $this->ProcessInputData('createKurortTypeLink');

		if(!empty($data['KurortTypeLink_IsKurortTypeLink']) && $data['KurortTypeLink_IsKurortTypeLink'] == 2){
			$data['KurortTypeLink_IsKurortTypeLink'] = 'on';
		} else {
			$data['KurortTypeLink_IsKurortTypeLink'] = 1;
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['KurortTypeLink_id'] = null;

		$resp = $this->dbmodel->saveKurortTypeLink($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['KurortTypeLink_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'KurortTypeLink_id'=>$resp[0]['KurortTypeLink_id']
			)
		));
	}

	/**
	 *  Изменение типа курорта
	 */
	function KurortTypeLink_put() {
		$data = $this->ProcessInputData('updateKurortTypeLink', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createKurortTypeLink');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$resp = $this->dbmodel->loadKurortTypeLinkById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['KurortTypeLink_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}
		$res_array = array_merge($resp[0], $data);

		if(!empty($res_array['KurortTypeLink_IsKurortTypeLink']) && $res_array['KurortTypeLink_IsKurortTypeLink'] == 2){
			$res_array['KurortTypeLink_IsKurortTypeLink'] = 'on';
		} else {
			$res_array['KurortTypeLink_IsKurortTypeLink'] = 1;
		}

		$resp = $this->dbmodel->saveKurortTypeLink($res_array);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['KurortTypeLink_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Удаление типа курорта
	 */
	function KurortTypeLink_delete() {
		$data = $this->ProcessInputData('deleteKurortTypeLink', null, true);

		$resp = $this->dbmodel->loadKurortTypeLinkById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['KurortTypeLink_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора',
				'error_code' => '6'
			));
		}
		
		$this->load->model('Utils_model', 'umodel');
		$resp = $this->umodel->ObjectRecordsDelete(false, 'KurortTypeLink', false, array($data['KurortTypeLink_id']), 'fed', false);

		if(!empty($resp[0]['Error_Message'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Message'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Добавление домового хозяйства
	 */
	public function LpuHousehold_post() {
		$data = $this->ProcessInputData('createLpuHousehold');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$resp = $this->dbmodel->saveLpuHousehold($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuHousehold_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'LpuHousehold_id' => $resp[0]['LpuHousehold_id']
			)
		));
	}

	/**
	 * Изменение домового хозяйства
	 */
	public function LpuHousehold_put() {
		$data = $this->ProcessInputData('updateLpuHousehold');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$resp = $this->dbmodel->saveLpuHouseholdAPI($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['LpuHousehold_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление домового хозяйства
	 */
	public function LpuHousehold_delete() {
		$data = $this->ProcessInputData('deleteLpuHousehold');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$resp = $this->dbmodel->deleteLpuHouseholdAPI($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение списка домовых хозяйств МО
	 */
	public function LpuHouseHoldByMO_get() {
		$data = $this->ProcessInputData('getLpuHouseHoldByMO');

		$resp = $this->dbmodel->getLpuHouseHoldByMOAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}