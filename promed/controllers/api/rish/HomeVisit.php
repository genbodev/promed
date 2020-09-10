<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с расписанием
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Alexander Kurakin
 * @version			11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class HomeVisit extends SwREST_Controller {
	protected  $inputRules = array(
		'HomeVisit' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'CallProfType_id','label' => 'Идентификатор профиля вызова','rules' => 'required','type' => 'id'),
			array('field' => 'Address_Address','label' => 'Адрес вызова','rules' => 'required','type' => 'string'),
			array('field' => 'HomeVisitCallType_id','label' => 'Идентификатор типа вызова','rules' => 'required','type' => 'id'),
			array('field' => 'HomeVisit_setDT','label' => 'Дата и время вызова','rules' => 'required','type' => 'datetime'),
			array('field' => 'HomeVisit_Num','label' => 'Номер вызова','rules' => '','type' => 'string'),
			array('field' => 'MedStaffFact_id','label' => 'Место работы врача','rules' => '','type' => 'id'),
			array('field' => 'HomeVisit_Phone','label' => 'Телефон обратной связи','rules' => '','type' => 'string'),
			array('field' => 'HomeVisitWhoCall_id','label' => 'Идентификатор вызвавшего врача','rules' => 'required','type' => 'id'),
			array('field' => 'HomeVisit_Symptoms','label' => 'Симптомы','rules' => 'required','type' => 'string'),
			array('field' => 'HomeVisit_Comment','label' => 'Дополнительная информация','rules' => '','type' => 'string'),
			array('field' => 'HomeVisitStatus_id','label' => 'Идентификатор статуса вызова','rules' => 'required','type' => 'id'),
			array('field' => 'HomeVisit_LpuComment','label' => 'Причина отказа','rules' => '','type' => 'string'),
			array('field' => 'KLStreet_id','label' => 'Идентификатор улицы из КЛАДР','rules' => '','type' => 'id'),
			array('field' => 'HomeVisit_StreetGUID','label' => 'GUID улицы из справочника ФИАС','rules' => '','type' => 'string'),
			array('field' => 'HomeVisit_House','label' => 'Номер дома','rules' => 'required','type' => 'string'),
			array('field' => 'platform', 'label' => 'Источник записи', 'rules' => '', 'type' => 'id')
		),
		'HomeVisitById' => array(
			array('field' => 'HomeVisit_id', 'label' => 'Идентификатор вызова на дом', 'rules' => 'required', 'type' => 'id')
		),
		'mgetHomeVisitList' => array(
			array(
				'field' => 'sess_id', 
				'label' => 'Идентификатор сессии', 
				'rules' => 'required', 
				'type' => 'string'
			),
			array(
				'field' => 'HomeVisit_begDate',
				'label' => 'Дата начала периода журнала',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'HomeVisit_endDate',
				'label' => 'Дата окончания периода журнала',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Врач',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'getHomeVisitSymptoms' => array(),
		'HomeVisitCancel' => array(
			array('field' => 'sess_id', 'label' => 'Идентификатор сессии', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'HomeVisit_id', 'label' => ' Идентификатор вызова на дом', 'rules' => 'required', 'type' => 'id')
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('HomeVisit_model', 'dbmodel');
	}

	/**
	 * Добавление вызова врача на дом
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
	 * 			"HomeVisit_id": "Идентификатор вызова"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
	 * 				"HomeVisit_id": 77777777777
	 * 			}
	 * 		}
	 * }
	 */
	function HomeVisit_post($mobile = false) {
		$data = $this->ProcessInputData('HomeVisit', null, true);

		$data['HomeVisit_id'] = null;
		
		// if (!empty($data['HomeVisit_StreetGUID']) && !empty($data['HomeVisit_House'])) {
		$address = array();

		if (empty($data['HomeVisit_StreetGUID']) && !empty($data['KLStreet_id'])) {
			$street_guid = $this->dbmodel->getGUIDByKLStreet($data['KLStreet_id']);
			if(!empty($street_guid['KLStreet_AOGUID'])) $data['HomeVisit_StreetGUID'] = $street_guid['KLStreet_AOGUID'];
		}

		$address['KLStreet_AOGUID'] = $data['HomeVisit_StreetGUID'];
		$address['Address_House'] = $data['HomeVisit_House'];
		$result = $this->dbmodel->searchRegionsByAddress((object)$address);
		
		if (!empty($data['HomeVisit_StreetGUID']) && empty($data['KLStreet_id'])) {
			$street = $this->dbmodel->getKLStreetByGUID($data['HomeVisit_StreetGUID']);
			if(!empty($street['KLStreet_id'])) $data['KLStreet_id'] = $street['KLStreet_id'];			
		}	
		if (empty($data['KLStreet_id'])) {
			$this->response(array(
				'error_msg' => 'Не возможно получить улицу.',
				'error_code' => '6',
				'data' => ''
			));	
		}
		
		if(!empty($data['session']['region']['number'])) $data['KLRgn_id'] = $data['session']['region']['number'];
		if(!empty($data['HomeVisit_House'])) $data['Address_House'] = $data['HomeVisit_House'];
		
		$lpu_id = null;
		$lpuregion_id = null;

		$this->load->model('Common_model', 'Common_model');
		$person = $this->Common_model->getPersonDataOnDate($data['Person_id']);

		if (!empty($result) && count($result) > 0) foreach ($result as $item) {
			if (!empty($item->regions) && count($item->regions) > 0) foreach ($item->regions as $reg) {
				if ($person['Person_Age'] >= 18) {
					if ($reg->LpuRegionType_SysNick == 'ter') {
						$lpu_id = $reg->Lpu_id;
						$lpuregion_id = $reg->LpuRegion_id;
					}
				} else {
					if ($reg->LpuRegionType_SysNick == 'ped') {
						$lpu_id = $reg->Lpu_id;
						$lpuregion_id = $reg->LpuRegion_id;
					}
				}
			}
		}

		if ($lpu_id == null) {
			$this->response(array(
				'error_msg' => 'Не удалось определить МО, обслуживающую данный адрес',
				'error_code' => '6',
				'data' => ''
			));			
		} elseif ($lpuregion_id == null) {
			$this->response(array(
				'error_msg' => 'Не удалось определить участок, обслуживающий данный адрес',
				'error_code' => '6',
				'data' => ''
			));	
		} else {
			$data['Lpu_id'] = $lpu_id;
			$data['LpuRegion_id'] = $lpuregion_id;
		}
		
				
		if ($this->dbmodel->getAllowTimeHomeVisit($data['Lpu_id']) === false) {
			$dt = $this->dbmodel->getHomeVisitDayWorkTime($data['Lpu_id']);
			$this->response(array(
				'error_msg' => 'Вызов врача на дом может быть оформлен через портал в рабочие дни поликлиники '.((!empty($dt)) ? $dt : ''),
				'error_code' => '6',
				'data' => ''
			));		
		}
		
		if (empty($data['HomeVisit_Num'])) {
			
			$data['NumeratorObject_SysName'] = 'HomeVisit';
			$this->load->model('Numerator_model', 'Numerator_model');
			$numerator = $this->Numerator_model->getActiveNumerator($data);
			
			if (empty($numerator['Numerator_id'])) {
				$this->response(array(
					'error_msg' => 'Не возможно сгенерировать нумератор.',
					'error_code' => '6',
					'data' => ''
				));	
			} else {
				$data['Numerator_id'] = $numerator['Numerator_id'];	
				$data['onDate'] = null;	
			}
			
			$numData = $this->dbmodel->getHomeVisitNum($data);
			if (empty($numData['Numerator_Num'])) {
				$this->response(array(
					'error_msg' => 'Не возможно сгенерировать номер вызова.',
					'error_code' => '6',
					'data' => ''
				));	
			} else {
				$data['HomeVisit_Num'] = $numData['Numerator_Num'];
			}			
		}

		if($mobile){// вызыван из mSaveHomeVisit_post
			if(empty($data['platform'])){
				$data['HomeVisitSource_id'] = 2;// Мобильное приложение: не определено
			}elseif(in_array((int)$data['platform'], array(6, 7))){// 6 - iOS, 7 - Android
				$data['HomeVisitSource_id'] = $data['platform'];
			}
		}
		else{
			$data['HomeVisitSource_id'] = 12;// источник записи - РИШ, справочник HomeVisitSource
		}

		$resp = $this->dbmodel->addHomeVisit($data);
		if (!empty($resp[0]['HomeVisit_id'])) {
			$this->response(array(
				'error_code' => 0,
				'data' => array(
					'HomeVisit_id' => $resp[0]['HomeVisit_id']
				)
			));
		} else {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * Отмена вызова врача на дом
	 */
	function HomeVisitCancel_put() {
		$data = $this->ProcessInputData('HomeVisitCancel', null, true);
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->cancelHomeVisit($data);
		if (!empty($resp[0])) {
			$respStatus = $this->dbmodel->getHomeVisitInfo($data['HomeVisit_id']); 
			if (!empty($respStatus)) {
				$this->response(array(
					'error_code' => 0,
					'data' => array(
						'HomeVisitStatus_id' => $respStatus['HomeVisitStatus_id'],
						'HomeVisitStatus_Name' => $respStatus['HomeVisitStatus_Name']
					)
				));
			} else {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
		} else {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * Добавление вызова врача на дом
	 */
	function HomeVisitById_get() {
		$data = $this->ProcessInputData('HomeVisitById', null, true);

		$resp = $this->dbmodel->getHomeVisitForAPI($data);
		if (!empty($resp[0])) {
			$this->response(array(
				'error_code' => 0,
				'data' => $resp
			));
		} else {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
	
	/**
	 *  Получение списка вызовов на день
	 */
	function mgetHomeVisitList_get(){
		$data = $this->ProcessInputData('mgetHomeVisitList');
		
		$data['begDate'] = $data['HomeVisit_begDate'];
		$data['endDate'] = $data['HomeVisit_endDate'];
		$data['start'] = 0;
		$data['limit'] = 100;
		
		$this->load->model('HomeVisit_model', 'HomeVisit_model');
		$resp = $this->HomeVisit_model->getHomeVisitList($data);
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		
		$this->response(array(
			'error_code' => 0,
			'data' => !empty($resp['data']) ? $resp['data'] : array(),
			'start' => $data['start'],
			'limit' => $data['limit']
		));
	}
	
	/**
	 *  Получение списка симптомов
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
	 * 			"id": "Идентификатор симптома",
	 * 			"name": "Название симптома",
	 *			"radio": "Признак радио-группы",
	 * 			"pid": "Идентификатор симптома-родителя",
	 * 			"visittype": "Тип вызова",
	 * 			"type": "Тип элемента на форме",
	 * 			"children": "array"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": [
	 * 				{
						"id": "3",
						"name": "Дополнительные сведения",
						"radio": null,
						"pid": null,
						"visittype": "ther",
						"type": "maingroup",
						"children": [
							{
								"id": "30",
								"name": "Акушерство",
								"radio": "2",
								"pid": "3",
								"visittype": "ther",
								"type": "check",
								"children": [
									{
									"id": "48",
									"name": "перенесенный аборт",
									"radio": null,
									"pid": "30",
									"visittype": "ther",
									"type": "radio"
									}
								]
							}
						]
					}
	 * 			]
	 * 		}
	 * }
	 */
	function getHomeVisitSymptoms_get(){
		$data = $this->ProcessInputData('getHomeVisitSymptoms');
		$this->load->model('HomeVisit_model', 'HomeVisit_model');
		$resp = $this->HomeVisit_model->getSymptoms();
		$this->response(array(
			'error_code' => 0,
			'data' => !empty($resp) ? $resp : array()
		));
	}

	/**
	 *  Получение списка симптомов
	 */
	function mGetHomeVisitSymptoms_get(){
		$this->getHomeVisitSymptoms_get();
	}

	/**
	 *  Получение списка симптомов
	 */
	function HomeVisitSymptoms_get(){
		$this->getHomeVisitSymptoms_get();
	}

	/**
	 *  Создание вызова на дом
	 */
	function mSaveHomeVisit_post(){
		$this->HomeVisit_post(true);
	}
}