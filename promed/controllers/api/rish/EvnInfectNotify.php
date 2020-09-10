<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с Журналом Извещений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Vyacheslav Gluchov
 * @version			11.2018
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnInfectNotify extends SwREST_Controller {
	protected  $inputRules = array(
		'getEvnInfectNotify' => array(
			array(
				'field' => 'EvnInfectNotify_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			)
		),
		'createEvnInfectNotify' => array(
			/*array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),*/
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),			
			array(
				'field' => 'Evn_pid',
				'label' => 'Идентификатор события родителя',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnInfectNotify_DiseaseDate',
				'label' => 'Дата заболевания',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnInfectNotify_FirstMeasures',
				'label' => 'Проведенные первичные противоэпидемические мероприятия и дополнительные сведения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnInfectNotify_FirstSESDT',
				'label' => 'Дата первичной сигнализации в СЭС',
				'rules' => '',
				'type' => 'datetime'
			),
			array(
				'field' => 'EvnInfectNotify_FirstTreatDate',
				'label' => 'Дата первичного обращения (выявления)',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnInfectNotify_IsLabDiag',
				'label' => 'Подтвержден лабораторно',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnInfectNotify_NextVizitDate',
				'label' => 'Дата последнего посещения детского учреждения, школы',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Место госпитализации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnInfectNotify_PoisonDescr',
				'label' => 'Где произошло отравление, чем',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnInfectNotify_ReceiverMessage',
				'label' => 'Кто принял сообщение',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnInfectNotify_SetDiagDate',
				'label' => 'Дата установления диагноза',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Ссылка на учетный документ (движение, посещение)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач, создавший извещение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор диагноза',
				'rules' => '',
				'type' => 'int'
			),			
			/*
			array(
				'field' => 'EvnInfectNotify_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnInfectNotify_pid',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			),			
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			*/
		),
		'updateEvnInfectNotify' => array(
			array(
				'field' => 'EvnInfectNotify_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNotifyBase_id',
				'label' => 'Идентификатор базового извещения',
				'rules' => '',
				'type' => 'id'
			),			
			/*array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),*/
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnInfectNotify_DiseaseDate',
				'label' => 'Дата заболевания',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnInfectNotify_FirstMeasures',
				'label' => 'Проведенные первичные противоэпидемические мероприятия и дополнительные сведения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnInfectNotify_FirstSESDT',
				'label' => 'Дата первичной сигнализации в СЭС',
				'rules' => '',
				'type' => 'datetime'
			),
			array(
				'field' => 'EvnInfectNotify_FirstTreatDate',
				'label' => 'Дата первичного обращения (выявления)',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnInfectNotify_IsLabDiag',
				'label' => 'Подтвержден лабораторно',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnInfectNotify_NextVizitDate',
				'label' => 'Дата последующего посещения детского учреждения, школы',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Место госпитализации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnInfectNotify_PoisonDescr',
				'label' => 'Где произошло отравление, чем',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnInfectNotify_ReceiverMessage',
				'label' => 'Кто принял сообщение',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnInfectNotify_SetDiagDate',
				'label' => 'Дата установления диагноза',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Ссылка на учетный документ (движение, посещение)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач, создавший извещение',
				'rules' => '',
				'type' => 'id'
			),		
			/*
			
			array(
				'field' => 'EvnInfectNotify_pid',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			),			
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			*/
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnInfectNotify_model', 'dbmodel');
	}
	
	/**
	 * Создание извещения об инфекционном заболевании (ВИЧ)
	 */
	function index_post(){
		$data = $this->ProcessInputData('createEvnInfectNotify');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		if(!empty($data['EvnInfectNotify_IsLabDiag']) && !in_array($data['EvnInfectNotify_IsLabDiag'], array(1,2)) ){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'параметр EvnInfectNotify_IsLabDiag может иметь только значения 1 или 2'
			));
		}
		
		$result = $this->db->query("
			SELECT TOP 1 PersonEvn_id, Evn_id, Server_id FROM v_Evn WHERE Evn_id = :Evn_pid AND EvnClass_id != 13
		", $data);
		$res = (is_object($result)) ? $result->result('array') : false;
		
		if(!empty($res[0]) && !empty($res[0]['PersonEvn_id']) && !empty($res[0]['Evn_id'])){	
			$isIsset = $this->dbmodel->isIsset(array('EvnInfectNotify_pid' => $res[0]['Evn_id']));
			if(!empty($isIsset) && count($isIsset) > 0){
				$this->response(array(
					'error_code' => 1,
					'Error_Msg' => 'извещение уже существует'
				));
			}
			$params = array(
				'EvnInfectNotify_id' => null,
				'Lpu_id' => $data['Lpu_id'],
				'EvnInfectNotify_DiseaseDate' => $data['EvnInfectNotify_DiseaseDate'],
				'EvnInfectNotify_FirstMeasures' => (!empty($data['EvnInfectNotify_FirstMeasures'])) ? $data['EvnInfectNotify_FirstMeasures'] : null,
				'EvnInfectNotify_FirstSESDT' => (!empty($data['EvnInfectNotify_FirstSESDT'])) ? $data['EvnInfectNotify_FirstSESDT'] : null,
				'EvnInfectNotify_FirstTreatDate' => (!empty($data['EvnInfectNotify_FirstTreatDate'])) ? $data['EvnInfectNotify_FirstTreatDate'] : null,
				'EvnInfectNotify_IsLabDiag' => (!empty($data['EvnInfectNotify_IsLabDiag'])) ? $data['EvnInfectNotify_IsLabDiag'] : null,
				'EvnInfectNotify_NextVizitDate' => (!empty($data['EvnInfectNotify_NextVizitDate'])) ? $data['EvnInfectNotify_NextVizitDate'] : null,			
				'EvnInfectNotify_PoisonDescr' => (!empty($data['EvnInfectNotify_PoisonDescr'])) ? $data['EvnInfectNotify_PoisonDescr'] : null,
				'EvnInfectNotify_ReceiverMessage' => (!empty($data['EvnInfectNotify_ReceiverMessage'])) ? $data['EvnInfectNotify_ReceiverMessage'] : null,
				'EvnInfectNotify_SetDiagDate' => (!empty($data['EvnInfectNotify_SetDiagDate'])) ? $data['EvnInfectNotify_SetDiagDate'] : null,
				'EvnSection_id' => (!empty($data['EvnSection_id'])) ? $data['EvnSection_id'] : null,
				'MedPersonal_id' => (!empty($data['MedPersonal_id'])) ? $data['MedPersonal_id'] : null,
				'Server_id' => $res[0]['Server_id'],
				'pmUser_id' => $data['pmUser_id'],
				'EvnInfectNotify_pid' => $res[0]['Evn_id'],
				'PersonEvn_id' => $res[0]['PersonEvn_id']
				//'Diag_id' => (!empty($data['Diag_id'])) ? $data['Diag_id'] : null,
				//'Person_id' => (!empty($data['Person_id'])) ? $data['Person_id'] : null

			);
		}else{
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'событие Evn_pid не найдено'
			));
		}
		
		$resp = $this->dbmodel->save($params);
		if (!empty($resp[0]['EvnInfectNotify_id'])) {
			$this->response(array(
				'error_code' => 0,
				'data' => array(
					'EvnInfectNotify_id' => $resp[0]['EvnInfectNotify_id']
				)
			));
		} else {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
	
	/**
	 * Получение извещения об инфекционном заболевании (ВИЧ)
	 */
	function index_get(){
		$data = $this->ProcessInputData('getEvnInfectNotify', null, true);
		if(empty($data['Person_id']) && empty($data['EvnInfectNotify_id'])){
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id или EvnInfectNotify_id) должен быть задан',
				'error_code' => '3'
			));
		}
		
		$resp = $this->dbmodel->getEvnInfectNotifyAPI($data);
		
		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Изменение извещения об инфекционном заболевании (ВИЧ)
	 */
	function index_put(){
		$data = $this->ProcessInputData('updateEvnInfectNotify', null, true);
		if(!empty($data['EvnInfectNotify_IsLabDiag']) && !in_array($data['EvnInfectNotify_IsLabDiag'], array(1,2)) ){
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => 'параметр EvnInfectNotify_IsLabDiag может иметь только значения 1 или 2'
			));
		}
		
		$res = $this->dbmodel->load(array('EvnInfectNotify_id'=>$data['EvnInfectNotify_id']));
		if(!empty($res[0]) && !empty($res[0]['EvnInfectNotify_id'])){
			$params = array(
				'EvnInfectNotify_id' => $data['EvnInfectNotify_id'],
				'Lpu_id' => $data['Lpu_id'],
				'EvnInfectNotify_DiseaseDate' => (!empty($data['EvnInfectNotify_DiseaseDate'])) ? $data['EvnInfectNotify_DiseaseDate'] : $res[0]['EvnInfectNotify_DiseaseDate'],
				'EvnInfectNotify_FirstMeasures' => (!empty($data['EvnInfectNotify_FirstMeasures'])) ? $data['EvnInfectNotify_FirstMeasures'] : $res[0]['EvnInfectNotify_FirstMeasures'],
				'EvnInfectNotify_FirstSESDT' => (!empty($data['EvnInfectNotify_FirstSESDT'])) ? $data['EvnInfectNotify_FirstSESDT'] : $res[0]['EvnInfectNotify_FirstSESDT'],
				'EvnInfectNotify_FirstTreatDate' => (!empty($data['EvnInfectNotify_FirstTreatDate'])) ? $data['EvnInfectNotify_FirstTreatDate'] : $res[0]['EvnInfectNotify_FirstTreatDate'],
				'EvnInfectNotify_IsLabDiag' => (!empty($data['EvnInfectNotify_IsLabDiag'])) ? $data['EvnInfectNotify_IsLabDiag'] : $res[0]['EvnInfectNotify_IsLabDiag'],
				'EvnInfectNotify_NextVizitDate' => (!empty($data['EvnInfectNotify_NextVizitDate'])) ? $data['EvnInfectNotify_NextVizitDate'] : $res[0]['EvnInfectNotify_NextVizitDate'],			
				'EvnInfectNotify_PoisonDescr' => (!empty($data['EvnInfectNotify_PoisonDescr'])) ? $data['EvnInfectNotify_PoisonDescr'] : $res[0]['EvnInfectNotify_PoisonDescr'],
				'EvnInfectNotify_ReceiverMessage' => (!empty($data['EvnInfectNotify_ReceiverMessage'])) ? $data['EvnInfectNotify_ReceiverMessage'] : $res[0]['EvnInfectNotify_ReceiverMessage'],
				'EvnInfectNotify_SetDiagDate' => (!empty($data['EvnInfectNotify_SetDiagDate'])) ? $data['EvnInfectNotify_SetDiagDate'] : $res[0]['EvnInfectNotify_SetDiagDate'],
				'EvnSection_id' => (!empty($data['EvnSection_id'])) ? $data['EvnSection_id'] : $res[0]['EvnSection_id'],
				'MedPersonal_id' => (!empty($data['MedPersonal_id'])) ? $data['MedPersonal_id'] : $res[0]['MedPersonal_id'],
				'Server_id' => $data['Server_id'],
				'pmUser_id' => $data['pmUser_id'],
				'EvnInfectNotify_pid' => (!empty($data['EvnInfectNotify_pid'])) ? $data['EvnInfectNotify_pid'] : $res[0]['EvnInfectNotify_pid'],
				'PersonEvn_id' => (!empty($data['PersonEvn_id'])) ? $data['PersonEvn_id'] : $res[0]['PersonEvn_id'],
			);
		}else{
			$this->response(array(
				'error_msg' => 'извещение об инфекционном заболевании (ВИЧ) EvnInfectNotify_id='.$data['EvnInfectNotify_id'].' не найдено',
				'error_code' => '3'
			));
		}
		
		$resp = $this->dbmodel->save($params);
		if (!empty($resp[0]['EvnInfectNotify_id'])) {
			$this->response(array(
				'error_code' => 0
			));
		} else {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
}