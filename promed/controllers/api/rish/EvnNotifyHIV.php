<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с таблицей EvnNotifyHIV ОПЕРАТИВНОЕ ДОНЕСЕНИЕ о лице в крови которого при исследовании в реакции иммуноблота выявлены антитела к ВИЧ
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

class EvnNotifyHIV extends SwREST_Controller {
	protected  $inputRules = array(
		'getEvnNotifyHIV' => array(
			array(
				'field' => 'EvnNotifyHIV_id',
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
		'createEvnNotifyHIV' => array(
					/*array(
						'field' => 'EvnNotifyHIV_pid',
						'label' => 'Идентификатор движения или посещения',
						'rules' => 'required',
						'type' => 'id'
					),*/
			array(
				'field' => 'Evn_pid',
				'label' => 'Идентификатор события родителя',
				'rules' => 'required',
				'type' => 'id'
			),
					/*array(
						'field' => 'PersonEvn_id',
						'label' => 'Идентификатор состояния человека',
						'rules' => 'required',
						'type' => 'id'
					),*/
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Diag_id',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'HIVContingentTypeP_id',
				'label' => 'Гражданство (значения 100 или 200 из справочника dbo.HIVContingentType)',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNotifyHIV_setDT',
				'label' => 'Дата заполнения извещения',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач, заполнивший извещение',
				'rules' => 'required',
				'type' => 'id'
			),
			array('field' => 'HIVContingentType_Name_list','label' => 'Код контингента','rules' => '','type' => 'string'),//id через запятую
			array('field' => 'MorbusHIVLab_BlotDT','label' => 'Дата постановки реакции иммуноблота','rules' => '','type' => 'date'),
			array('field' => 'MorbusHIVLab_TestSystem','label' => 'Тип тест-системы','rules' => 'max_length[64]','type' => 'string'),
			array('field' => 'MorbusHIVLab_BlotNum','label' => 'N серии','rules' => 'max_length[64]','type' => 'string'),
			array('field' => 'MorbusHIVLab_BlotResult','label' => 'Выявленные белки и гликопротеиды','rules' => 'max_length[100]','type' => 'string'),
			array('field' => 'Lpu_id','label' => 'Учреждение, первично выявившее положительный результат в ИФА','rules' => '','type' => 'id'), //(Lpuifa_id)
			array('field' => 'MorbusHIVLab_IFADT','label' => 'Дата ИФА','rules' => '','type' => 'date'),
			array('field' => 'MorbusHIVLab_IFAResult','label' => 'Результат ИФА','rules' => 'max_length[30]','type' => 'string'),
					//array('field' => 'MorbusHIVLab_PCRDT','label' => 'Дата ПЦР','rules' => '','type' => 'date'),
					//array('field' => 'MorbusHIVLab_PCRResult','label' => 'Результат ПЦР','rules' => 'max_length[30]','type' => 'string'),
			array('field' => 'LabAssessmentResult_iid','label' => 'Результат рекации иммуноблота','rules' => '','type' => 'id'),
			array('field' => 'MorbusHIV_confirmDate','label' => 'Дата подтверждения диагноза','rules' => '','type' => 'date'),
			array('field' => 'MorbusHIV_EpidemCode','label' => 'Эпидемиологический код','rules' => 'max_length[100]','type' => 'string'),
			array(
				'field' => 'PersonRegister_setDate',
				'label' => 'PersonRegister_setDate',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MedPersonal_iid',
				'label' => 'Добавил человека в регистр - врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_iid',
				'label' => 'Добавил человека в регистр - ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegisterType_id',
				'label' => 'PersonRegisterType_id',
				'rules' => '',
				'type' => 'id'
			),
		),
		'updateEvnNotifyHIV' => array(
			
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
		$this->load->model('EvnNotifyHIV_model', 'dbmodel');
	}
	
	/**
	 * Создание извещения об инфекционном заболевании (ВИЧ)
	 */
	function index_post(){		
		$data = $this->ProcessInputData('createEvnNotifyHIV');
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		
		if(!empty($data['MorbusHIVLab_BlotDT']) && empty($data['LabAssessmentResult_iid'])){
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'параметр LabAssessmentResult_iid обязателен'
			));
		}
		if(!empty($data['HIVContingentTypeP_id']) && !in_array($data['HIVContingentTypeP_id'], array(100,200))){
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'параметр HIVContingentTypeP_id может иметь только значения 100 или 200'
			));
		}
		$data['HIVContingentType_pid'] = $data['HIVContingentTypeP_id'];
		$data['EvnNotifyHIV_pid'] = $data['Evn_pid'];
		$data['Lpuifa_id'] = (empty($data['Lpu_id'])) ? null : $data['Lpu_id'];
		$data['HIVContingentType_id_list'] = $data['HIVContingentType_Name_list'];
		$data['MorbusHIVLab_PCRDT'] = null;
		$data['MorbusHIVLab_PCRResult'] = null;		
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];
		$data['session'] = $sp['session'];
		$data['Lpu_id'] = $sp['Lpu_id'];
		
		$result = $this->db->query("
			SELECT TOP 1 PersonEvn_id, Evn_id, Server_id FROM v_Evn WHERE Evn_id = :Evn_pid AND EvnClass_id != 13
		", $data);
		$res = (is_object($result)) ? $result->result('array') : false;
		
		if(!empty($res[0]) && !empty($res[0]['PersonEvn_id']) && !empty($res[0]['Evn_id'])){
			$data['PersonEvn_id'] = $res[0]['PersonEvn_id'];
			$data['Server_id'] = $res[0]['Server_id'];
			
			$autoSaveRegistry = (!empty($data['session']['setting']['server']['register_hiv_auto_include'])) ? true : false;
			if($autoSaveRegistry && (empty($data['PersonRegister_setDate']) || empty($data['MedPersonal_iid']) || empty($data['Lpu_iid']) || empty($data['PersonRegisterType_id']))){
				$this->response(array(
					'error_code' => 3,
					'error_msg' => 'парамтеры PersonRegister_setDate, MedPersonal_iid, Lpu_iid, PersonRegisterType_id обязательны для передачи'
				));
			}
			
			$response = $this->dbmodel->doSave($data);
			if(!empty($response['Error_Msg'])){
				$this->response(array(
					'error_code' => 3,
					'error_msg' => $response['Error_Msg']
				));
			}
			$result = array();
			$result['EvnNotifyHIV_id'] = (!empty($response['EvnNotifyHIV_id'])) ? $response['EvnNotifyHIV_id'] : null;
			$result['Morbus_id'] = (!empty($response['Morbus_id'])) ? $response['Morbus_id'] : null;
			
			if(!empty($response['EvnNotifyBase_id']) && $autoSaveRegistry){
				$result['EvnNotifyHIV_id'] = $response['EvnNotifyHIV_id'];
				
				$this->load->model('PersonRegister_model','PersonRegister_model');
				$this->PersonRegister_model->setPerson_id($data['Person_id']);
				$this->PersonRegister_model->setPersonRegisterType_id($data['PersonRegisterType_id']);
				$this->PersonRegister_model->setDiag_id($data['Diag_id']);
				$this->PersonRegister_model->setPersonRegister_setDate($data['PersonRegister_setDate']);
				$this->PersonRegister_model->setMedPersonal_iid($data['MedPersonal_iid']);
				$this->PersonRegister_model->setLpu_iid($data['Lpu_iid']);
				$this->PersonRegister_model->setEvnNotifyBase_id($response['EvnNotifyBase_id']);
				$saveRegister = $this->PersonRegister_model->save();
				
				if(!empty($saveRegister[0]['PersonRegister_id'])){
					$result['PersonRegister_id'] = $saveRegister[0]['PersonRegister_id'];
				}else{
					$result['PersonRegister'] = (!empty($saveRegister[0]['Error_Msg'])) ? $saveRegister[0]['Error_Msg'] : $saveRegister;
				}
			}
			$this->response($result);
		}else{
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'событие Evn_pid не найдено'
			));
		}
	}
	
	/**
	 * Получение извещения об инфекционном заболевании (ВИЧ)
	 */
	function index_get(){
		$data = $this->ProcessInputData('getEvnNotifyHIV', null, true);
		if(empty($data['Person_id']) && empty($data['EvnNotifyHIV_id'])){
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id или EvnNotifyHIV_id) должен быть задан',
				'error_code' => '3'
			));
		}
		
		$resp = $this->dbmodel->getEvnNotifyHivAPI($data);		
		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Изменение извещения об инфекционном заболевании (ВИЧ)
	 */
	function index_put(){
		$this->response(array(
			'error_code' => 1,
			'Error_Msg' => 'Редактирование извещения не предусмотрено!'
		));
	}
}