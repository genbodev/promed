<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с организациями
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

class Org extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Org_model', 'dbmodel');
		$this->inputRules = array(
			'getOrgByPar' => array(
				array('field' => 'Org_id', 'label' => 'Идентификатор записи "Организация"', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_Code', 'label' => 'Код организации', 'rules' => '', 'type' => 'int'),
				array('field' => 'Org_Name', 'label' => 'Наименование организации', 'rules' => '', 'type' => 'string'),
				array('field' => 'Org_Nick', 'label' => 'Краткое наименование организации', 'rules' => '', 'type' => 'string'),
				array('field' => 'start', 'default' => 0, 'label' => 'начальная позиция', 'rules' => '', 'type' => 'int'),
				array('field' => 'limit', 'default' => 100, 'label' => 'количество записей', 'rules' => '', 'type' => 'int'),
			),
			'createOrg' => array(
				array('field' => 'Org_Code', 'label' => 'Код организации', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'Org_Name', 'label' => 'Наименование организации', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'Org_Nick', 'label' => 'Краткое наименование организации', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'OrgType_id', 'label' => 'Идентификатор типа организации', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'UAddress_id', 'label' => 'Юридический адрес', 'rules' => '', 'type' => 'id'),
			),
			'updateOrg' => array(
				array('field' => 'Org_id', 'label' => 'Идентификатор записи "Организация"', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Org_Code', 'label' => 'Код организации', 'rules' => '', 'type' => 'int'),
				array('field' => 'Org_Name', 'label' => 'Наименование организации', 'rules' => '', 'type' => 'string'),
				array('field' => 'Org_Nick', 'label' => 'Краткое наименование организации', 'rules' => '', 'type' => 'string'),
				array('field' => 'OrgType_id', 'label' => 'Идентификатор типа организации', 'rules' => '', 'type' => 'id'),
				array('field' => 'UAddress_id', 'label' => 'Юридический адрес', 'rules' => '', 'type' => 'id'),
			),
			'getAddress' => array(
				array('field' => 'Org_id', 'label' => 'Идентификатор записи "Организация"', 'rules' => 'required', 'type' => 'id'),
			),
			'createAddress' => array(
				array('field' => 'Address_Address', 'label' => 'Строка адреса при ручном вводе', 'rules' => '', 'type' => 'string'),
				array('field' => 'KLCountry_id', 'label' => 'Идентификатор страны', 'rules' => '', 'type' => 'id')
			),
			'updateAddress' => array(
				array('field' => 'Address_id', 'label' => 'Идентификатор записи "Адрес"', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Address_Address', 'label' => 'Строка адреса при ручном вводе', 'rules' => '', 'type' => 'string'),
				array('field' => 'KLCountry_id', 'label' => 'Идентификатор страны', 'rules' => '', 'type' => 'id')
			)
		);
	}

	/**
	 * Поиск организации по параметрам
	 */
	function OrgByPar_get() {
		$data = $this->ProcessInputData('getOrgByPar');

		$resp = $this->dbmodel->getOrgForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Список организаций
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
	 * 			"Org_id": "Идентификатор организации",
	 *			"OrgType_id": "Идентификатор",
	 * 			"Org_Nick": "Сокращенное наименование организации",
	 * 			"Org_Name": "Наименование организации"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
	 * 				"Org_id": 9400001980,
	 * 				"OrgType_id": null,
	 * 				"Org_Nick": "ОАО \"РАЙТЕПЛОЭНЕРГО-СЕРВИС\"",
	 * 				"Org_Name": "ОАО \"РАЙТЕПЛОЭНЕРГО-СЕРВИС\""
	 * 			}
	 * 		}
	 * }
	 */
	function mgetOrgList_get() {

		$data = $this->ProcessInputData('getOrgByPar');

		if (!isset($data['start'])) $data['start'] = 0;
		if (!isset($data['limit'])) $data['limit'] = 100;

		$resp = $this->dbmodel->getOrgListForApi($data);
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Создание организации
	 */
	function index_post() {
		$data = $this->ProcessInputData('createOrg');

		$data['fromAPI'] = 1;

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$resp = $this->dbmodel->saveOrg(array_merge($data, array(
			'fromAPI' => true,
			'PAddress_id' => null,
			'Org_id' => null,
			'Org_INN' => null,
			'Org_OGRN' => null,
			'Server_id' => 0,
			'Org_Description' => null,
			'Org_rid' => null,
			'Org_begDate' => null,
			'Org_endDate' => null,
			'Okved_id' => null,
			'Oktmo_id' => null,
			'Okopf_id' => null,
			'Okfs_id' => null,
			'Org_OKATO' => null,
			'Org_KPP' => null,
			'Org_OKPO' => null,
			'Org_Phone' => null,
			'Org_Email' => null,
			'KLCountry_id' => null,
			'KLRGN_id' => null,
			'KLSubRGN_id' => null,
			'KLCity_id' => null,
			'KLTown_id' => null,
			'OrgType_SysNick' => null
		)));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['Org_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Org_id'=>$resp[0]['Org_id'])
		));
	}

	/**
	 * Редактирование организации
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateOrg');

		$data['fromAPI'] = 1;

		foreach($data as $key => $value) {
			if (empty($value) && !array_key_exists($key, $this->_args)) {
				unset($data[$key]);
			}
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$old_data = $this->dbmodel->getOrgForAPI($data);
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$data = array_merge($old_data[0], $data);

		$resp = $this->dbmodel->saveOrg(array_merge($data, array(
			'fromAPI' => true,
			'PAddress_id' => null,
			'Org_INN' => null,
			'Org_OGRN' => null,
			'Server_id' => 0,
			'Org_Description' => null,
			'Org_rid' => null,
			'Org_begDate' => null,
			'Org_endDate' => null,
			'Okved_id' => null,
			'Oktmo_id' => null,
			'Okopf_id' => null,
			'Okfs_id' => null,
			'Org_OKATO' => null,
			'Org_KPP' => null,
			'Org_OKPO' => null,
			'Org_Phone' => null,
			'Org_Email' => null,
			'KLCountry_id' => null,
			'KLRGN_id' => null,
			'KLSubRGN_id' => null,
			'KLCity_id' => null,
			'KLTown_id' => null,
			'OrgType_SysNick' => null
		)));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['Org_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Org_id'=>$resp[0]['Org_id'])
		));
	}

	/**
	 * Получение записи Адрес для организации
	 */
	function Address_get() {
		$data = $this->ProcessInputData('getAddress');

		$resp = $this->dbmodel->getAddressForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание записи «Адрес»
	 */
	function Address_post() {
		$data = $this->ProcessInputData('createAddress');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$resp = $this->dbmodel->saveAddress('Address_id', array_merge($data, array(
			'Address_id' => null
		)));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['Address_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Address_id'=>$resp[0]['Address_id'])
		));
	}

	/**
	 * Редактирование записи «Адрес»
	 */
	function Address_put() {
		$data = $this->ProcessInputData('updateAddress');

		foreach($data as $key => $value) {
			if (empty($value) && !array_key_exists($key, $this->_args)) {
				unset($data[$key]);
			}
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$old_data = $this->dbmodel->getFirstRowFromQuery("
			select
				Address_id,
				Address_Address,
				KLCountry_id
			from
				v_Address (nolock)
			where
				Address_id = :Address_id
		", $data);
		if (empty($old_data)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$resp = $this->dbmodel->saveAddress('Address_id', array_merge($old_data, $data));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['Address_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Address_id'=>$resp[0]['Address_id'])
		));
	}

	/**
	 * Получение кода для новой организации
	 */
	public function GenOrgCode_get() {
		$resp = $this->dbmodel->getMaxOrgCode();

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['Org_Code'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Org_Code' => $resp[0]['Org_Code'])
		));
	}
}