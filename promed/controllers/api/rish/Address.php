<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с адресом
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

class Address extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Address_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 *  Получение адреса
	 */
	function index_get() {
		$data = $this->ProcessInputData('loadAddress');
		if(empty($data['Person_id']) && empty($data['Address_id'])){
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id, Address_id) должен быть задан',
				'error_code' => '3',
				'data' => ''
			));
		}

		if(!empty($data['Address_id'])){
			$resp = $this->dbmodel->loadAddress($data);
		} else if(!empty($data['Person_id'])){
			$resp = array();
			if(!empty($data['AddressType_id'])){
				$types = array($data['AddressType_id']);
			} else {
				$types = array('1','2','3');
			}
			foreach ($types as $val) {
				switch ($val) {
					case '1':
						$res = $this->dbmodel->loadUAddressId($data);
						if(!empty($res[0]['Address_id'])){
							foreach ($res as $key => $value) {
								$rs = $this->dbmodel->loadAddress($value,$val);
								if(!empty($rs[0]['Address_id'])){
									$resp = array_merge($resp,$rs);
								}
							}
						}
						break;
					case '2':
						$res = $this->dbmodel->loadPAddressId($data);
						if(!empty($res[0]['Address_id'])){
							foreach ($res as $key => $value) {
								$rs = $this->dbmodel->loadAddress($value,$val);
								if(!empty($rs[0]['Address_id'])){
									$resp = array_merge($resp,$rs);
								}
							}
						}
						break;
					case '3':
						$res = $this->dbmodel->getPersonBirthPlace($data);
						if(!empty($res[0]['Address_id'])){
							foreach ($res as $key => $value) {
								$rs = $this->dbmodel->loadAddress($value,$val);
								if(!empty($rs[0]['Address_id'])){
									$resp = array_merge($resp,$rs);
								}
							}
						}
						break;
				}
			}
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Создание адреса
	 */
	function index_post() {
		$data = $this->ProcessInputData('createAddress');
		if( empty($data['KLSubRgn_id']) && empty($data['KLCity_id']) && empty($data['KLTown_id']) && empty($data['AoidArea']) && empty($data['AoidStreet']) ) {
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (KLSubRgn_id, KLCity_id, KLTown_id, AoidArea, AoidStreet) должен быть задан',
				'error_code' => '3',
				'data' => ''
			));
		}
		
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];
		$data['Address_id'] = null;
		$data['KLAreaType_id'] = null;
		$data['Address_Address'] = null;
		$data['KLAreaStat_id'] = null;
		$data['Address_Nick'] = null;
		$data['PersonSprTerrDop_id'] = null;
		$data['AddressSpecObject_id'] = null;
		$data['Address_begDate'] = null;

		if ( !empty($data['AoidArea']) || !empty($data['AoidStreet']) ) {
			$addressParamsFIAS = $this->dbmodel->getAddressParamsByFIASCode($data['AoidArea'], $data['AoidStreet']);

			if ( $addressParamsFIAS === false ) {
				$this->response(array(
					'error_msg' => 'Ошибка при разборе адреса по AoidArea/AoidStreet',
					'error_code' => '3',
					'data' => ''
				));
				return false;
			}

			$data = array_merge($data, $addressParamsFIAS);
		}

		if(!empty($data['Person_id']) && !empty($data['AddressType_id'])){
			switch ($data['AddressType_id']) {
				case '1':
					$data['PersonUAddress_id'] = null;
					$data['PersonUAddress_Index'] = null;
					$data['PersonUAddress_Count'] = null;
					$data['PersonUAddress_begDT'] = null;

					$resp = $this->dbmodel->savePersonUAddress($data);
					break;
				case '2':
					$data['PersonPAddress_id'] = null;
					$data['PersonPAddress_Index'] = null;
					$data['PersonPAddress_Count'] = null;
					$data['PersonPAddress_begDT'] = null;

					$resp = $this->dbmodel->savePersonPAddress($data);
					break;
				case '3':
					$resp = $this->dbmodel->saveAddress($data);
					if(!empty($resp[0]['Address_id'])){
						$place = $this->dbmodel->getPersonBirthPlace(array('Person_id'=>$data['Person_id']));
						$data['PersonBirthPlace_id'] = (!empty($place[0]['PersonBirthPlace_id'])?$place[0]['PersonBirthPlace_id']:null);
						$data['Address_id'] = $resp[0]['Address_id'];
						$res = $this->dbmodel->savePersonBirthPlace($data);
						if(empty($res[0]['PersonBirthPlace_id'])){
							$resp = $res;
						}
					}
					break;
			}
		} else {
			$resp = $this->dbmodel->saveAddress($data);	
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['Address_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'Person_id'=>$data['Person_id'],
				'Address_id'=>$resp[0]['Address_id'],
				'AddressType_id'=>$data['AddressType_id']
			)
		));
	}

	/**
	 *  Редактирование адреса
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateAddress');
		if( empty($data['KLSubRgn_id']) && empty($data['KLCity_id']) && empty($data['KLTown_id']) && empty($data['AoidArea']) && empty($data['AoidStreet']) ) {
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (KLSubRgn_id, KLCity_id, KLTown_id, AoidArea, AoidStreet) должен быть задан',
				'error_code' => '3',
				'data' => ''
			));
		}

		$resp = $this->dbmodel->loadAddress($data,null,true);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['Address_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора адреса',
				'error_code' => '6',
				'data' => ''
			));
		}
		
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$data['Server_id'] = $resp[0]['Server_id'];
		$data['KLAreaType_id'] = $resp[0]['KLAreaType_id'];
		$data['Address_Address'] = null;
		$data['KLAreaStat_id'] = $resp[0]['KLAreaStat_id'];
		$data['Address_Nick'] = null;
		$data['PersonSprTerrDop_id'] = $resp[0]['PersonSprTerrDop_id'];
		$data['AddressSpecObject_id'] = $resp[0]['AddressSpecObject_id'];
		$data['Address_begDate'] = $resp[0]['Address_begDate'];

		if ( !empty($data['AoidArea']) || !empty($data['AoidStreet']) ) {
			$addressParamsFIAS = $this->dbmodel->getAddressParamsByFIASCode($data['AoidArea'], $data['AoidStreet']);

			if ( $addressParamsFIAS === false ) {
				$this->response(array(
					'error_msg' => 'Ошибка при разборе адреса по AoidArea/AoidStreet',
					'error_code' => '3',
					'data' => ''
				));
				return false;
			}

			$data = array_merge($data, $addressParamsFIAS);
		}

		$resp = $this->dbmodel->saveAddress($data);	

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
			'data' => ''
		));
	}

	/**
	 *  Получение списка детей какого либо объекта адреса
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"data": [
					{
						"KLArea_id": "Идентификатор территории",
						"KLSocr_id": "Идентификатор сокращенного наименования",
						"KLArea_Name": "Наименование территории",
						"KLAreaLevel_id": "Идентификатор уровня территории",
	 					"KLAreaLevel_Name": "Уровень территории"
					}
	 * 			]
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"KLArea_id": "3312",
					"KLSocr_id": "84",
					"KLArea_Name": "АЛЕКСАНДРОВСК",
					"KLAreaLevel_id": "3",
	 				"KLAreaLevel_Name": "Город"
				}
	 * 		}
	 * }
	 */
	function mLoadChildLists_get() {

		$data = $this->ProcessInputData('loadChildLists');

		$KLAreaLevel = array(
			'Регион',
			'Район',
			'Город',
			'Населенный пункт',
			'Улица',
			'Дом',
		);

		$KLChildLists = $this->dbmodel->loadChildLists($data);
		$response = array();

		if (!empty($KLChildLists)) {
			foreach ($KLChildLists as $rows) {
				$response[] = array(
					'KLArea_id' => $rows['KLArea_id'],
					'KLSocr_id' => $rows['KLSocr_id'],
					'KLArea_Name' => toUTF($rows['KLArea_Name']),
					'KLAreaLevel_id' => $rows['KLAreaLevel_id'],
					'KLAreaLevel_Name' => $KLAreaLevel[intval($rows['KLAreaLevel_id'])-1]
				);
			}
		}

		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	 *  Поиск адреса по вхождению символов в строку
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
					"data": [
					{
						"KladrCache_id": "Идентификатор КЛАДР",
						"KLCountry_id": "Идентификатор страны",
						"KLRgn_id": "Идентификатор региона",
						"KLSubRgn_id": "Идентификатор района",
						"KLCity_id": "Идентификатор города",
						"KLTown_id": "Идентификатор населенного пункта",
						"KLStreet_id": "Идентификатор улицы",
						"KladrCache_Text": "Полный адрес"
					}
	 * 			]
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"KladrCache_id": "600987",
					"KLCountry_id": "643",
					"KLRgn_id": "59",
					"KLSubRgn_id": "1471",
					"KLCity_id": null,
					"KLTown_id": "118097",
					"KLStreet_id": "728052",
					"KladrCache_Text": "РОССИЯ, ПЕРМСКИЙ КРАЙ, ЧЕРНУШИНСКИЙ Р-Н, АЗИНСКИЙ П, НОВОАЗИНСКАЯ УЛ, "
				}
	 * 		}
	 * }
	 */
	function mSearchAddress_get() {

		$data = $this->ProcessInputData('searchAddress');

		$result = $this->dbmodel->searchAddress($data);
		$this->response(array('error_code' => 0,'data' => $result));
	}

	/**
	 *  Возврат всех территорий ЛПУ
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"KLTown_Name": "Название населенного пункта",
				"KLTown_id": "Идентификатор населенного пункта",
				"KLCity_id": "Идентификатор города",
				"streets": [
					{
						"Street_id": "Идентификатор улицы",
						"Street_Name": "Наименование улицы"
					}
				]
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"KLTown_Name": "ГАРЕВЛЯНА Д",
					"KLTown_id": "116977",
					"KLCity_id": null,
					"streets": [
						{
						"Street_id": "471436",
						"Street_Name": "БЕРЕЗОВЫЙ ПЕР"
						},{
						"Street_id": "471437",
						"Street_Name": "ПОЛЕВАЯ УЛ"
						},{
						"Street_id": "471438",
						"Street_Name": "НАГОРНАЯ УЛ"
						},{
						"Street_id": "471435",
						"Street_Name": "ЛОСКУТОВА УЛ"
						}
	 				]
				}
	 * 		}
	 * }
	 */
	function mGetLpuStreetsByLpuRegion_get() {

		$data = $this->ProcessInputData('mGetLpuStreetsByLpuRegion');

		$result = $this->dbmodel->getLpuServedStreets($data);
		$this->response(array('error_code' => 0,'data' => $result));
	}
}