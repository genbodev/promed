<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с классификатором Медицинского изделия
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Dmitriy Vlasenko
 * @version			11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class MedProductClass extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('LpuPassport_model', 'dbmodel');
		$this->inputRules = array(
			'loadMedProductClass' => array(
				array('field' => 'MedProductClass_id', 'label' => 'Идентификатор записи', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductClass_Name', 'label' => 'Наименование МИ', 'rules' => '', 'type' => 'string'),
				array('field' => 'MedProductClass_Model', 'label' => 'Модель МИ', 'rules' => '', 'type' => 'string'),
				array('field' => 'CardType_id', 'label' => 'Идентификатор справочника "Тип МИ"', 'rules' => '', 'type' => 'id'),
				array('field' => 'ClassRiskType_id', 'label' => 'Идентификатор справочника "Класс потенциального риска применения"', 'rules' => '', 'type' => 'id'),
				array('field' => 'FuncPurpType_id', 'label' => 'Идентификатор справочника "Функциональное назначение"', 'rules' => '', 'type' => 'id'),
				array('field' => 'UseAreaType_id', 'label' => 'Идентификатор "Область применения"', 'rules' => '', 'type' => 'id'),
				array('field' => 'UseSphereType_id', 'label' => 'Идентификатор "Сфера применения"', 'rules' => '', 'type' => 'id'),
			),
			'createMedProductClass' => array(
				array('field' => 'MedProductClass_Name', 'label' => 'Наименование МИ', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'MedProductClass_Model', 'label' => 'Модель МИ', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'MedProductType_id', 'label' => 'Идентификатор справочника "Вид МИ"', 'rules' => '', 'type' => 'id'),
				array('field' => 'CardType_id', 'label' => 'Идентификатор справочника "Тип МИ"', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'ClassRiskType_id', 'label' => 'Идентификатор справочника "Класс потенциального риска применения"', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'FuncPurpType_id', 'label' => 'Идентификатор справочника "Функциональное назначение"', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'UseAreaType_id', 'label' => 'Идентификатор "Область применения"', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'UseSphereType_id', 'label' => 'Идентификатор "Сфера применения"', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'FZ30Type_id', 'label' => 'Идентификатор "30й ФЗ"', 'rules' => '', 'type' => 'id'),
				array('field' => 'TNDEDType_id', 'label' => 'Идентификатор "ТН ВЭД"', 'rules' => '', 'type' => 'id'),
				array('field' => 'GMDNType_id', 'label' => 'Идентификатор справочника GMDN', 'rules' => '', 'type' => 'id'),
				array('field' => 'MT97Type_id', 'label' => 'Идентификатор МТ по 97пр', 'rules' => '', 'type' => 'id'),
				array('field' => 'OKOFType_id', 'label' => 'Идентификатор ОКОФ', 'rules' => '', 'type' => 'id'),
				array('field' => 'OKPType_id', 'label' => 'Идентификатор OKP', 'rules' => '', 'type' => 'id'),
				array('field' => 'OKPDType_id', 'label' => 'Идентификатор ОКПД', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductClass_IsAmbulNovor', 'label' => 'Признак "Ренимобиль для новорожденных и детей раннего возраста"', 'rules' => '', 'type' => 'api_flag_nc'),
				array('field' => 'MedProductClass_IsAmbulTerr', 'label' => 'Признак "Реанимобиль повышенной необходимости"', 'rules' => '', 'type' => 'api_flag_nc'),
			),
			'updateMedProductClass' => array(
				array('field' => 'MedProductClass_id', 'label' => 'Идентификатор записи', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedProductClass_Name', 'label' => 'Наименование', 'rules' => '', 'type' => 'string'),
				array('field' => 'MedProductClass_Model', 'label' => 'Модель', 'rules' => '', 'type' => 'string'),
				array('field' => 'MedProductType_id', 'label' => 'Идентификатор справочника "Вид МИ"', 'rules' => '', 'type' => 'id'),
				array('field' => 'CardType_id', 'label' => 'Идентификатор справочника "Тип МИ"', 'rules' => '', 'type' => 'id'),
				array('field' => 'ClassRiskType_id', 'label' => 'Идентификатор справочника "Класс потенциального риска применения"', 'rules' => '', 'type' => 'id'),
				array('field' => 'FuncPurpType_id', 'label' => 'Идентификатор справочника "Функциональное назначение"', 'rules' => '', 'type' => 'id'),
				array('field' => 'UseAreaType_id', 'label' => 'Идентификатор "Область применения"', 'rules' => '', 'type' => 'id'),
				array('field' => 'UseSphereType_id', 'label' => 'Идентификатор "Сфера применения"', 'rules' => '', 'type' => 'id'),
				array('field' => 'FZ30Type_id', 'label' => 'Идентификатор "30й ФЗ"', 'rules' => '', 'type' => 'id'),
				array('field' => 'TNDEDType_id', 'label' => 'Идентификатор "ТН ВЭД"', 'rules' => '', 'type' => 'id'),
				array('field' => 'GMDNType_id', 'label' => 'Идентификатор справочника GMDN', 'rules' => '', 'type' => 'id'),
				array('field' => 'MT97Type_id', 'label' => 'Идентификатор МТ по 97пр', 'rules' => '', 'type' => 'id'),
				array('field' => 'OKOFType_id', 'label' => 'Идентификатор ОКОФ', 'rules' => '', 'type' => 'id'),
				array('field' => 'OKPType_id', 'label' => 'Идентификатор OKP', 'rules' => '', 'type' => 'id'),
				array('field' => 'OKPDType_id', 'label' => 'Идентификатор ОКПД', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductClass_IsAmbulNovor', 'label' => 'Признак "Ренимобиль для новорожденных и детей раннего возраста"', 'rules' => '', 'type' => 'api_flag_nc'),
				array('field' => 'MedProductClass_IsAmbulTerr', 'label' => 'Признак "Реанимобиль повышенной необходимости"', 'rules' => '', 'type' => 'api_flag_nc'),
			)
		);
	}

	/**
	 * Получение "Классификатор Медицинского изделия"
	 */
	function index_get() {
		$data = $this->ProcessInputData('loadMedProductClass');
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->loadMedProductClassForAPI($data);
		if (!is_array($resp) && empty($resp[0]['MedProductClass_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание "Классификатор Медицинского изделия"
	 */
	function index_post() {
		$data = $this->ProcessInputData('createMedProductClass');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		if ($data['MedProductClass_IsAmbulNovor'] != 1) { unset($data['MedProductClass_IsAmbulNovor']); }
		if ($data['MedProductClass_IsAmbulTerr'] != 1) { unset($data['MedProductClass_IsAmbulTerr']); }

		$resp = $this->dbmodel->saveMedProductClass(array_merge($data, array(
			'MedProductClass_id' => null,
			'Lpu_id' => $sp['Lpu_id']
		)));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['MedProductClass_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('MedProductClass_id'=>$resp[0]['MedProductClass_id'])
		));
	}

	/**
	 * Изменение "Классификатор Медицинского изделия"
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateMedProductClass');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createMedProductClass');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$old_data = $this->dbmodel->loadMedProductClassForAPI(array(
			'MedProductClass_id' => $data['MedProductClass_id']
		));
		if (empty($old_data[0])) {
			//$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не найдена запись мединциского изделия'
			));
		}
		if(isset($old_data[0]['Lpu_id']) && $old_data[0]['Lpu_id'] != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}		

		$data = array_merge($old_data[0], $data);

		if ($data['MedProductClass_IsAmbulNovor'] != 1) { unset($data['MedProductClass_IsAmbulNovor']); }
		if ($data['MedProductClass_IsAmbulTerr'] != 1) { unset($data['MedProductClass_IsAmbulTerr']); }
		
		$resp = $this->dbmodel->saveMedProductClass($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['MedProductClass_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}
}