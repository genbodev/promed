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

class PersonRegister extends SwREST_Controller {
	protected  $inputRules = array(
		'getPersonRegister' => array(
			array(
				'field' => 'PersonRegister_id',
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
		),
		'createPersonRegister' => array(
			/*array(
				'field' => 'ignoreCheckAnotherDiag',
				'label' => 'Флаг игнорирования проверки на наличие записей с другим диагнозом',
				'rules' => '',
				'type' => 'int'
			),*/
			array(
				'field' => 'PersonRegisterType_id',
				'label' => 'PersonRegisterType_id',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusType_id',
				'label' => 'MorbusType_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => 'required',
				'type' => 'id'
			),
			/*array(
				'field' => 'MorbusType_SysNick',
				'label' => 'Тип регистра',
				'rules' => 'required',
				'type' => 'string'
			),*/
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_setDate',
				'label' => 'PersonRegister_setDate',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'MedPersonal_iid',
				'label' => 'Добавил человека в регистр - врач',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_iid',
				'label' => 'Добавил человека в регистр - ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNotifyBase_id',
				'label' => 'EvnNotifyBase_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNotifyHIV_id',
				'label' => 'EvnNotifyHIV_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNotifyTub_id',
				'label' => 'EvnNotifyTub_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotify_id',
				'label' => 'EvnOnkoNotify_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Morbus_confirmDate',
				'label' => 'Morbus_confirmDate',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Morbus_EpidemCode',
				'label' => 'Morbus_EpidemCode',
				'rules' => '',
				'type' => 'string'
			)
		),
		'updatePersonRegister' => array(
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи регистра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
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
				'field' => 'MedPersonal_iid',
				'label' => 'Добавил человека в регистр - врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_setDate',
				'label' => 'Дата включения в регистр',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'PersonRegisterType_id',
				'label' => 'Тип регистра (значение из справочника dbo. PersonRegisterType)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusType_id',
				'label' => 'тип заболевания (значение из справочника dbo. MorbusType)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Morbus_confirmDate',
				'label' => 'Дата подтверждения диагноза',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Morbus_EpidemCode',
				'label' => 'Эпидемиологический код',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnNotifyBase_id',
				'label' => 'EvnNotifyBase_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNotifyHIV_id',
				'label' => 'идентификатор оперативного донесения о лице, в крови которого выявлены антитела к ВИЧ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNotifyTub_id',
				'label' => 'идентификатор извещения о туберкулезе',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotify_id',
				'label' => 'идентификатор извещения об онкобольном (для PersonRegisterType_id =3)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'ЛПУ, исключившее человека из регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_disDate',
				'label' => 'Дата исключения из регистра',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MedPersonal_did',
				'label' => 'врач, исключивший человека из регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegisterOutCause_id',
				'label' => 'Причина исключения из регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonDeathCause_id',
				'label' => 'Причина смерти',
				'rules' => '',
				'type' => 'id'
			),
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
		$this->load->model('PersonRegister_model', 'PersonRegister_model');
	}
	
	/**
	 * Создание записи регистра по онкологии, больных туберкулезом и ВИЧ-инфицированых 
	 */
	function index_post(){
		$data = $this->ProcessInputData('createPersonRegister', null, true);
		if ($data) {
			if (isset($data['Person_id'])) {
				$this->PersonRegister_model->setPerson_id($data['Person_id']);
			}
			if (isset($data['PersonRegisterType_id'])) {
				$this->PersonRegister_model->setPersonRegisterType_id($data['PersonRegisterType_id']);
			}
			if (isset($data['MorbusType_id'])) {
				//$this->PersonRegister_model->setMorbusType_SysNick($data['MorbusType_SysNick']);
				$this->PersonRegister_model->setMorbusType_id($data['MorbusType_id']);
			}
			if (isset($data['Diag_id'])) {
				$this->PersonRegister_model->setDiag_id($data['Diag_id']);
			}
			if (isset($data['Morbus_confirmDate'])) {
				$this->PersonRegister_model->setMorbus_confirmDate($data['Morbus_confirmDate']);
			}
			if (isset($data['Morbus_EpidemCode'])) {
				/*if( in_array($data['Morbus_EpidemCode'], array('B20', 'B21', 'B22', 'B23', 'B24', 'B20')) ){
					$this->PersonRegister_model->setMorbus_EpidemCode($data['Morbus_EpidemCode']);
				}else{
					$this->response(array(
						'error_code' => 3,
						'Error_Msg' => 'Эпидемиологический код не попадает в диаппазон B20-B24'
					));
				}*/
				$this->PersonRegister_model->setMorbus_EpidemCode($data['Morbus_EpidemCode']);
			}
			if (isset($data['ignoreCheckAnotherDiag'])) {
				$this->PersonRegister_model->setignoreCheckAnotherDiag($data['ignoreCheckAnotherDiag']);
			}
			if (isset($data['PersonRegister_setDate'])) {
				$this->PersonRegister_model->setPersonRegister_setDate($data['PersonRegister_setDate']);
			}
			if (isset($data['MedPersonal_iid'])) {
				$this->PersonRegister_model->setMedPersonal_iid($data['MedPersonal_iid']);
			}
			if (isset($data['Lpu_iid'])) {
				$this->PersonRegister_model->setLpu_iid($data['Lpu_iid']);
			}
			
			//$data['EvnNotifyBase_id'] = null;
			if(!empty($data['EvnNotifyHIV_id'])){
				$data['EvnNotifyBase_id'] = $data['EvnNotifyHIV_id'];
			}elseif (!empty($data['EvnNotifyTub_id'])) {
				if($data['PersonRegisterType_id'] != 7){
					$this->response(array(
						'error_code' => 3,
						'Error_Msg' => 'Тип регистра не соответствует извещению о туберкулезе'
					));
				}
				$data['EvnNotifyBase_id'] = $data['EvnNotifyTub_id'];
			}elseif (!empty($data['EvnOnkoNotify_id'])) {
				if($data['PersonRegisterType_id'] != 3){
					$this->response(array(
						'error_code' => 3,
						'Error_Msg' => 'Тип регистра не соответствует извещению об онкобольном'
					));
				}
				$data['EvnNotifyBase_id'] = $data['EvnOnkoNotify_id'];
			}elseif (isset($data['EvnNotifyBase_id'])) {
				$this->PersonRegister_model->setEvnNotifyBase_id($data['EvnNotifyBase_id']);
			}
			
	
			$this->PersonRegister_model->setSessionParams($data['session']);
			$response = $this->PersonRegister_model->save();
			if(!empty($response[0]['PersonRegister_id'])){
				$result = array(
					'error_code' => 0,
					'PersonRegister_id' => $response[0]['PersonRegister_id']
				);
				if(!empty($response[0]['MorbusHIV_id'])){
					$result['MorbusHIV_id'] = $response[0]['MorbusHIV_id'];
				}elseif (!empty($response[0]['MorbusOnko_id'])) {
					$result['MorbusOnko_id'] = $response[0]['MorbusOnko_id'];
				}elseif (!empty($response[0]['MorbusTub_id'])) {
					$result['MorbusTub_id'] = $response[0]['MorbusTub_id'];
				}
				$this->response($result);
			}else{
				$this->response(array(
					'error_code' => 1,
					'error_msg' => (!empty($response[0]['Error_Msg'])) ? $response[0]['Error_Msg'] : $response[0]
				));
			}
		} else {
			$this->response(array(
				'error_code' => 1,
				'error_msg' => 'не верные параметры'
			));
		}		
	}
	
	/**
	 * Получение записи регистра по онкологии, больных туберкулезом и ВИЧ-инфицированых
	 */
	function index_get(){
		$data = $this->ProcessInputData('getPersonRegister', null, true);
		if(empty($data['Person_id']) && empty($data['PersonRegister_id'])){
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id или PersonRegister_id) должен быть задан',
				'error_code' => '3'
			));
		}
		
		$resp = $this->PersonRegister_model->loadAPI($data);		
		$this->response($resp);
	}
	
	/**
	 * Изменение записи регистра по онкологии, больных туберкулезом и ВИЧ-инфицированых
	 */
	function index_put(){
		$data = $this->ProcessInputData('updatePersonRegister', null, true);
		
		if(
			(!empty($data['Lpu_did']) || !empty($data['MedPersonal_did']) || !empty($data['PersonRegister_disDate']) || !empty($data['PersonRegisterOutCause_id'])) 
				&& 
			(empty($data['Lpu_did']) || empty($data['MedPersonal_did']) || empty($data['PersonRegister_disDate']) || empty($data['PersonRegisterOutCause_id']))
		){
			//предполагается, что передаются или не передаются одновременно, если какие–то из данных параметров не переданы, то ошибка 
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'отсутствует обязательный параметр'
			));
		}
		
		if(!empty($data['PersonRegisterOutCause_id']) && $data['PersonRegisterOutCause_id'] == 1 && empty($data['PersonDeathCause_id'])){
			//PersonDeathCause_id обязателен, если передана причина исключения из регистра со значением смерть
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'отсутствует обязательный параметр'
			));
		}
		
		$pesonRegistr = $this->PersonRegister_model->loadAPI(array('PersonRegister_id' => $data['PersonRegister_id']));		
		
		$EvnNotifyBase_id = null;
		if (isset($data['EvnNotifyBase_id'])) {
			$EvnNotifyBase_id = $data['EvnNotifyBase_id'];
		}elseif(!empty($data['EvnNotifyHIV_id'])){
			$EvnNotifyBase_id = $data['EvnNotifyHIV_id'];
		}elseif(!empty($data['EvnNotifyTub_id']) ){
			$EvnNotifyBase_id = $data['EvnNotifyTub_id'];
		}elseif(!empty($data['EvnOnkoNotify_id'])){
			$EvnNotifyBase_id = $data['EvnOnkoNotify_id'];
		}else{
			$EvnNotifyBase_id = $pesonRegistr[0]['EvnNotifyBase_id'];
		}
		
		$this->PersonRegister_model->setSessionParams($data['session']);

		$params= array (
			'session' => $data['session'],
			'PersonRegister_id' => $data['PersonRegister_id'],
			'Person_id' => (!empty($data['Person_id'])) ? $data['Person_id'] : $pesonRegistr[0]['Person_id'],
			'Diag_id' => (!empty($data['Diag_id'])) ? $data['Diag_id'] : $pesonRegistr[0]['Diag_id'],
			'PersonRegister_Code' => (!empty($data['PersonRegister_Code'])) ? $data['PersonRegister_Code'] : $pesonRegistr[0]['PersonRegister_Code'],
			'Lpu_iid' => (!empty($data['Lpu_iid'])) ? $data['Lpu_iid'] : $pesonRegistr[0]['Lpu_iid'],
			'MedPersonal_iid' => (!empty($data['MedPersonal_iid'])) ? $data['MedPersonal_iid'] : $pesonRegistr[0]['MedPersonal_iid'],
			'PersonRegister_setDate' => (!empty($data['PersonRegister_setDate'])) ? $data['PersonRegister_setDate'] : $pesonRegistr[0]['PersonRegister_setDate'],
			'PersonRegisterType_id' => (!empty($data['PersonRegisterType_id'])) ? $data['PersonRegisterType_id'] : $pesonRegistr[0]['PersonRegisterType_id'],
			'MorbusType_id' => (!empty($data['MorbusType_id'])) ? $data['MorbusType_id'] : $pesonRegistr[0]['MorbusType_id'],
			'Morbus_id' => (!empty($data['Morbus_id'])) ? $data['Morbus_id'] : $pesonRegistr[0]['Morbus_id'],
			//'Morbus_confirmDate' => (!empty($data['Morbus_confirmDate'])) ? $data['Morbus_confirmDate'] : $pesonRegistr[0]['Morbus_confirmDate'],
			//'Morbus_EpidemCode' => (!empty($data['Morbus_EpidemCode'])) ? $data['Morbus_EpidemCode'] : $pesonRegistr[0]['Morbus_EpidemCode'],
			'Lpu_did' => (!empty($data['Lpu_did'])) ? $data['Lpu_did'] : $pesonRegistr[0]['Lpu_did'],
			'PersonRegister_disDate' => (!empty($data['PersonRegister_disDate'])) ? $data['PersonRegister_disDate'] : $pesonRegistr[0]['PersonRegister_disDate'],
			'MedPersonal_did' => (!empty($data['MedPersonal_did'])) ? $data['MedPersonal_did'] : $pesonRegistr[0]['MedPersonal_did'],
			'MedPersonal_iid' => (!empty($data['MedPersonal_iid'])) ? $data['MedPersonal_iid'] : $pesonRegistr[0]['MedPersonal_iid'],
			'PersonRegisterOutCause_id' => (!empty($data['PersonRegisterOutCause_id'])) ? $data['PersonRegisterOutCause_id'] : $pesonRegistr[0]['PersonRegisterOutCause_id'],
			'PersonDeathCause_id' => (!empty($data['PersonDeathCause_id'])) ? $data['PersonDeathCause_id'] : $pesonRegistr[0]['PersonDeathCause_id'],
			'PersonRegister_Alcoholemia' => (!empty($data['PersonRegister_Alcoholemia'])) ? $data['PersonRegister_Alcoholemia'] : $pesonRegistr[0]['PersonRegister_Alcoholemia'],
			'RiskType_id' => (!empty($data['RiskType_id'])) ? $data['RiskType_id'] : $pesonRegistr[0]['RiskType_id'],
			'PregnancyResult_id' => (!empty($data['PregnancyResult_id'])) ? $data['PregnancyResult_id'] : $pesonRegistr[0]['PregnancyResult_id'],
			'EvnNotifyBase_id' => $EvnNotifyBase_id
		);
		//return false;
		$result = $this->PersonRegister_model->savePersonRegister($params);
		if(!empty($result[0]['PersonRegister_id'])){
			$this->response(array(
				'error_code' => 0
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'error_msg' => (!empty($result[0]['Error_Msg'])) ? $result[0]['Error_Msg'] : $result
			));
		}
	}
}