<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusHIV - контроллер API для работы со спецификой по ВИЧ
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

class MorbusHIV extends SwREST_Controller {
	protected  $inputRules = array(
		'saveMorbusSpecific' => array(
				//array('field' => 'Morbus_id','label' => 'Идентификатор заболевания','rules' => 'required', 'type' => 'id'),
				//array('field' => 'Person_id','label' => 'Пациент','rules' => 'required', 'type' => 'id'),
			array('field' => 'Diag_id','label' => 'идентификатор диагноза','rules' => '', 'type' => 'id'),
				//array('field' => 'Evn_pid','label' => 'Учетный документ (движение/посещение поликлиники)','rules' => '', 'type' => 'id'),// из которого редактируется специфика
			array('field' => 'PersonRegister_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),

			array('field' => 'MorbusHIV_id','label' => 'Специфика','rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusHIV_DiagDT','label' => 'Дата установления диагноза','rules' => '', 'type' => 'date'),			
			array('field' => 'HIVPregPathTransType_id','label' => 'Предполагаемый путь инфицирования','rules' => '', 'type' => 'id'),
			array('field' => 'HIVPregInfectStudyType_id','label' => 'Стадия ВИЧ-инфекции','rules' => '', 'type' => 'id'),
			array('field' => 'MorbusHIV_CountCD4','label' => 'Количество CD4 Т-лимфоцитов (мм)','rules' => '', 'type' => 'int'),
			array('field' => 'MorbusHIV_PartCD4','label' => 'Процент содержания CD4 Т-лимфоцитов','rules' => '', 'type' => 'float'),
			array('field' => 'MorbusHIVOut_endDT','label' => 'Дата снятия с диспансерного наблюдения','rules' => '', 'type' => 'date'),
			array('field' => 'HIVDispOutCauseType_id','label' => 'Причина снятия с диспансерного наблюдения','rules' => '', 'type' => 'id'),
			array('field' => 'DiagD_id','label' => 'Причина смерти','rules' => '', 'type' => 'id'),
			array('field' => 'MorbusHIV_NumImmun','label' => '№ иммуноблота','rules' => '', 'type' => 'int'),

			array('field' => 'HIVContingentTypeP_id','label' => 'Гражданство','rules' => '', 'type' => 'id'),
			array('field' => 'HIVContingentType_id_list','label' => 'Код контингента','rules' => '', 'type' => 'string'),//id через запятую (HIVContingentType_Name_list)
			array('field' => 'MorbusHIV_confirmDate','label' => 'Дата подтверждения диагноза','rules' => '', 'type' => 'date'),
			array('field' => 'MorbusHIV_EpidemCode','label' => 'Эпидемиологический код','rules' => '', 'type' => 'string'),

			array('field' => 'MorbusHIVLab_id','label' => 'Лабораторная диагностика ВИЧ-инфекции','rules' => '', 'type' => 'id'),			
			array('field' => 'MorbusHIVLab_BlotDT','label' => 'Дата постановки реакции иммуноблота','rules' => '','type' => 'date'),
			array('field' => 'MorbusHIVLab_TestSystem','label' => 'Тип тест-системы','rules' => 'max_length[64]','type' => 'string'),
			array('field' => 'MorbusHIVLab_BlotNum','label' => 'N серии','rules' => 'max_length[64]','type' => 'string'),
			array('field' => 'MorbusHIVLab_BlotResult','label' => 'Выявленные белки и гликопротеиды','rules' => 'max_length[100]','type' => 'string'),
			array('field' => 'Lpu_id','label' => 'Учреждение, первично выявившее положительный результат в ИФА','rules' => '','type' => 'id'), /*Lpuifa_id*/
			array('field' => 'MorbusHIVLab_IFADT','label' => 'Дата ИФА','rules' => '','type' => 'date'),
			array('field' => 'MorbusHIVLab_IFAResult','label' => 'Результат ИФА','rules' => 'max_length[30]','type' => 'string'),
			array('field' => 'MorbusHIVLab_PCRDT','label' => 'Дата ПЦР','rules' => '', 'type' => 'date'),
			array('field' => 'MorbusHIVLab_PCRResult','label' => 'Результат ПЦР','rules' => '', 'type' => 'string'),
				//array('field' => 'LabAssessmentResult_iid','label' => 'Результат рекции иммунноблота','rules' => '', 'type' => 'id'),
			array('field' => 'LabAssessmentResult_cid','label' => 'Результат полимеразной цепной реакции','rules' => '', 'type' => 'id'),

			array('field' => 'HIVInfectType_id','label' => 'Тип вируса','rules' => '','type' => 'id'),
				//array('field' => 'Mode','label' => 'Режим сохранения','rules' => '', 'type' => 'string')
		),
		'getMorbusHIV' => array(
			array('field' => 'PersonRegister_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusHIV_id','label' => 'Специфика','rules' => '', 'type' => 'id'),
		),
		'saveMorbusHIVChem' => array(
			array('field' => 'MorbusHIVChem_id','label' => 'Идентификатор записи','rules' => '', 'type' => 'id'),
			array('field' => 'MorbusHIV_id','label' => 'Идентификатор специфики заболевания ВИЧ','rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnNotifyBase_id','label' => 'Идентификатор извещения','rules' => '', 'type' => 'id'),
			array('field' => 'Drug_id','label' => 'Препарат','rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusHIVChem_Dose','label' => 'Доза','rules' => 'required', 'type' => 'string'),
			array('field' => 'MorbusHIVChem_begDT','label' => 'Дата начала','rules' => 'required', 'type' => 'date'),
			array('field' => 'MorbusHIVChem_endDT','label' => 'Дата окончания','rules' => '', 'type' => 'date'),
			array('field' => 'Evn_id','label' => 'Учетный документ (движение/посещение поликлиники)','rules' => '', 'type' => 'id'),
		),
		'updateMorbusHIVChem' => array(
			array('field' => 'MorbusHIVChem_id','label' => 'Идентификатор записи','rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusHIV_id','label' => 'Идентификатор специфики заболевания ВИЧ','rules' => '', 'type' => 'id'),
			array('field' => 'EvnNotifyBase_id','label' => 'Идентификатор извещения','rules' => '', 'type' => 'id'),
			array('field' => 'Drug_id','label' => 'Препарат','rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusHIVChem_Dose','label' => 'Доза','rules' => '', 'type' => 'string'),
			array('field' => 'MorbusHIVChem_begDT','label' => 'Дата начала','rules' => '', 'type' => 'date'),
			array('field' => 'MorbusHIVChem_endDT','label' => 'Дата окончания','rules' => '', 'type' => 'date'),
			array('field' => 'Evn_id','label' => 'Учетный документ (движение/посещение поликлиники)','rules' => '', 'type' => 'id'),
		),
		'getMorbusHIVChem' => array(
			array('field' => 'MorbusHIVChem_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusHIV_id','label' => 'Специфика','rules' => '', 'type' => 'id'),
		),
		'saveMorbusHIVVac' => array(
			array('field' => 'MorbusHIVVac_id','label' => 'Идентификатор записи','rules' => '', 'type' => 'id'),
			array('field' => 'MorbusHIV_id','label' => 'Идентификатор специфики заболевания ВИЧ','rules' => 'required', 'type' => 'id'),
			array('field' => 'Drug_id','label' => 'Препарат','rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusHIVVac_setDT','label' => 'Дата записи','rules' => 'required', 'type' => 'date')
		),
		'updateMorbusHIVVac' => array(
			array('field' => 'MorbusHIVVac_id','label' => 'Идентификатор записи','rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusHIV_id','label' => 'Идентификатор специфики заболевания ВИЧ','rules' => '', 'type' => 'id'),
			array('field' => 'Drug_id','label' => 'Препарат','rules' => '', 'type' => 'id'),
			array('field' => 'MorbusHIVVac_setDT','label' => 'Дата записи','rules' => '', 'type' => 'date')
		),
		'getMorbusHIVVac' => array(
			array('field' => 'MorbusHIVVac_id','label' => 'Идентификатор записи','rules' => '', 'type' => 'id'),
			array('field' => 'MorbusHIV_id','label' => 'Идентификатор специфики заболевания ВИЧ','rules' => '', 'type' => 'id')
		),
		'saveMorbusHIVSecDiag' => array(
			array('field' => 'MorbusHIVSecDiag_id','label' => 'Идентификатор записи','rules' => '', 'type' => 'id'),
			array('field' => 'MorbusHIV_id','label' => 'Идентификатор специфики заболевания ВИЧ','rules' => 'required', 'type' => 'id'),
			array('field' => 'Diag_id','label' => 'Заболевание','rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusHIVSecDiag_setDT','label' => 'Дата записи','rules' => 'required', 'type' => 'date')
		),
		'updateMorbusHIVSecDiag' => array(
			array('field' => 'MorbusHIVSecDiag_id','label' => 'Идентификатор записи','rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusHIV_id','label' => 'Идентификатор специфики заболевания ВИЧ','rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
			array('field' => 'MorbusHIVSecDiag_setDT','label' => 'Дата записи','rules' => '', 'type' => 'date')
		),
		'getMorbusHIVSecDiag' => array(
			array('field' => 'MorbusHIVSecDiag_id','label' => 'Идентификатор записи','rules' => '', 'type' => 'id'),
			array('field' => 'MorbusHIV_id','label' => 'Идентификатор специфики заболевания ВИЧ','rules' => '', 'type' => 'id')
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
		$this->load->model('MorbusHIV_model', 'MorbusHIV_model');
	}
	
	/**
	 * Создание специфики по ВИЧ
	 */
	function index_post(){
		$this->response(array(
			'error_code' => 1,
			'error_msg' => 'метод не предусмотрен'
		));	
	}
	
	/**
	 * Получение специфики по ВИЧ
	 */
	function index_get(){
		$data = $this->ProcessInputData('getMorbusHIV', null, true);
		if(empty($data['MorbusHIV_id']) && empty($data['PersonRegister_id'])){
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (MorbusHIV_id или PersonRegister_id) должен быть задан',
				'error_code' => '3'
			));
		}
		
		$resp = $this->MorbusHIV_model->loadMorbusHIV_API($data);		
		if(is_array($resp) && count($resp)>0){
			$this->response($resp);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Изменение специфики по ВИЧ
	 */
	function index_put(){	
		$data = $this->ProcessInputData('saveMorbusSpecific', null, true);
		
		$res = $this->MorbusHIV_model->loadMorbusHIV_API($data);
		if( is_array($res) && count($res) > 0 && !empty($res[0]['Person_id']) && !empty($res[0]['Morbus_id'])){
			$data['Morbus_id'] = $res[0]['Morbus_id'];
			$data['Person_id'] = $res[0]['Person_id'];
			$data['Lpuifa_id'] = (!empty($data['Lpu_id'])) ? $data['Lpu_id'] : $res[0]['Lpu_id'];
			if(!empty($res[0]['MorbusHIVLab_id'])) $data['MorbusHIVLab_id'] = $res[0]['MorbusHIVLab_id'];
			if(!empty($data['HIVContingentTypeP_id'])) $data['HIVContingentType_pid'] = $data['HIVContingentTypeP_id']; //гражданство
			if(empty($data['HIVContingentType_Name_list'])) $data['HIVContingentType_id_list'] = $res[0]['HIVContingentType_Name_list'];
			$data['Mode'] = 'personregister_viewform';
			
			$result = $this->MorbusHIV_model->saveMorbusHivAPI($data);
			if(!empty($result[0]['MorbusHIV_id'])){
				$this->response(array(
					'error_code' => 0
				));
			}else{
				$this->response(array(
					'error_code' => 1,
					'error_msg' => (!empty($result[0]['Error_Msg'])) ? $result[0]['Error_Msg'] : $result
				));
			}
		}else{
			$this->response(array(
				'error_code' => 1,
				'error_msg' => 'случай заболевания не найден'
			));
		}
	}
	
	/**
	 * Создание химиопрофилактики ВИЧ–инфекции
	 */
	function MorbusHIVChem_post(){
		$data = $this->ProcessInputData('saveMorbusHIVChem', null, true);
		if ($data) {
			$response = $this->MorbusHIV_model->saveMorbusHIVChem($data);
			if(!empty($response[0]['MorbusHIVChem_id'])){
				$this->response(array(
					'error_code' => 0,
					'MorbusHIVChem_id' => $response[0]['MorbusHIVChem_id']
				));
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
	 * Изменение химиопрофилактики ВИЧ–инфекции
	 */
	function MorbusHIVChem_put(){
		$data = $this->ProcessInputData('updateMorbusHIVChem', null, true);
		if(empty($data['MorbusHIVChem_id'])){
			$this->response(array(
				'error_code' => 1,
				'error_msg' => 'Отсутствует оябзательный параметр MorbusHIVChem_id'
			));
		}
		
		$result = $this->MorbusHIV_model->getMorbusHIVChemAPI(array('MorbusHIVChem_id' => $data['MorbusHIVChem_id']));
		if(is_array($result) && count($result)>0){
			$MorbusHIVChem_endDT = (!empty($result[0]['MorbusHIVChem_endDT'])) ? date_create($result[0]['MorbusHIVChem_endDT']) : null;
			$params = array(
				'MorbusHIVChem_id' => $data['MorbusHIVChem_id'],
				'MorbusHIV_id' => (!empty($data['MorbusHIV_id'])) ? $data['MorbusHIV_id'] : $result[0]['MorbusHIV_id'],
				'Drug_id' => (!empty($data['Drug_id'])) ? $data['Drug_id'] : $result[0]['Drug_id'],
				'MorbusHIVChem_Dose' => (!empty($data['MorbusHIVChem_Dose'])) ? $data['MorbusHIVChem_Dose'] : $result[0]['MorbusHIVChem_Dose'],
				'MorbusHIVChem_begDT' => (!empty($data['MorbusHIVChem_begDT'])) ? $data['MorbusHIVChem_begDT'] : date_create($result[0]['MorbusHIVChem_begDT']),
				'MorbusHIVChem_endDT' => (!empty($data['MorbusHIVChem_endDT'])) ? $data['MorbusHIVChem_endDT'] : $MorbusHIVChem_endDT,
				'pmUser_id' => $data['pmUser_id']
			);
			$response = $this->MorbusHIV_model->saveMorbusHIVChem($params);
			if(!empty($response[0]['MorbusHIVChem_id'])){
				$this->response(array(
					'error_code' => 0
				));
			}else{
				$this->response(array(
					'error_code' => 1,
					'error_msg' => (!empty($response[0]['Error_Msg'])) ? $response[0]['Error_Msg'] : $response[0]
				));
			}
		}else{
			$this->response(array(
				'error_code' => 1,
				'error_msg' => 'не найдена специфика MorbusHIVChem_id='.$data['MorbusHIVChem_id'].' «Проведение химиопрофилактики ВИЧ-инфекции»'
			));
		}
	}
	
	/**
	 * Получение химиопрофилактики ВИЧ–инфекции
	 */
	function MorbusHIVChem_get(){
		$data = $this->ProcessInputData('getMorbusHIVChem', null, true);
		if(empty($data['MorbusHIV_id']) && empty($data['MorbusHIVChem_id'])){
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (MorbusHIV_id или MorbusHIVChem_id) должен быть задан',
				'error_code' => '3'
			));
		}
		
		$resp = $this->MorbusHIV_model->getMorbusHIVChemAPI($data);		
		
		if(is_array($resp) && count($resp)>0){
			$this->response($resp);
		}else{
			$this->response(0);
		}
	}
	
	/**
	 * Создание вакцинации в рамках специфики ВИЧ
	 */
	function MorbusHIVVac_post(){
		$data = $this->ProcessInputData('saveMorbusHIVVac', null, true);
		$response = $this->MorbusHIV_model->saveMorbusHIVVac($data);
		if(!empty($response[0]['MorbusHIVVac_id'])){
			$this->response(array(
				'error_code' => 0,
				'MorbusHIVVac_id' => $response[0]['MorbusHIVVac_id']
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'error_msg' => (!empty($response[0]['Error_Msg'])) ? $response[0]['Error_Msg'] : $response[0]
			));
		}
	}
	
	/**
	 * Изменение вакцинации в рамках специфики ВИЧ
	 */
	function MorbusHIVVac_put(){
		$data = $this->ProcessInputData('updateMorbusHIVVac', null, true);
		if(empty($data['MorbusHIVVac_id'])){
			$this->response(array(
				'error_code' => 1,
				'error_msg' => 'Отсутствует оябзательный параметр MorbusHIVVac_id'
			));
		}
		
		$result = $this->MorbusHIV_model->getMorbusHIVVacAPI(array('MorbusHIVVac_id' => $data['MorbusHIVVac_id']));
		if(is_array($result) && count($result)>0){
			$params = array(
				'MorbusHIVVac_id' => $data['MorbusHIVVac_id'],
				'MorbusHIV_id' => (!empty($data['MorbusHIV_id'])) ? $data['MorbusHIV_id'] : $result[0]['MorbusHIV_id'],
				'Drug_id' => (!empty($data['Drug_id'])) ? $data['Drug_id'] : $result[0]['Drug_id'],
				'MorbusHIVVac_setDT' => (!empty($data['MorbusHIVVac_setDT'])) ? $data['MorbusHIVVac_setDT'] : date_create($result[0]['MorbusHIVVac_setDT']),
				'EvnNotifyBase_id' => (!empty($data['EvnNotifyBase_id'])) ? $data['EvnNotifyBase_id'] : $result[0]['EvnNotifyBase_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$response = $this->MorbusHIV_model->saveMorbusHIVVac($params);
			if(!empty($response[0]['MorbusHIVVac_id'])){
				$this->response(array(
					'error_code' => 0
				));
			}else{
				$this->response(array(
					'error_code' => 1,
					'error_msg' => (!empty($response[0]['Error_Msg'])) ? $response[0]['Error_Msg'] : $response[0]
				));
			}
		}else{
			$this->response(array(
				'error_code' => 1,
				'error_msg' => 'не найдена специфика вакцинация MorbusHIVVac_id='.$data['MorbusHIVVac_id']
			));
		}
	}
	
	/**
	 * Получение вакцинации в рамках специфики ВИЧ
	 */
	function MorbusHIVVac_get(){
		$data = $this->ProcessInputData('getMorbusHIVVac', null, true);
		if(empty($data['MorbusHIV_id']) && empty($data['MorbusHIVVac_id'])){
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (MorbusHIV_id или MorbusHIVVac_id) должен быть задан',
				'error_code' => '3'
			));
		}		
		$resp = $this->MorbusHIV_model->getMorbusHIVVacAPI($data);		
		$this->response($resp);	
	}
	
	/**
	 * Создание вторичных заболеваний и оппортунистических инфекций в рамках специфики ВИЧ
	 */
	function MorbusHIVSecDiag_post(){
		$data = $this->ProcessInputData('saveMorbusHIVSecDiag', null, true);
		$response = $this->MorbusHIV_model->saveMorbusHIVSecDiag($data);
		if(!empty($response[0]['MorbusHIVSecDiag_id'])){
			$this->response(array(
				'error_code' => 0,
				'MorbusHIVSecDiag_id' => $response[0]['MorbusHIVSecDiag_id']
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'error_msg' => (!empty($response[0]['Error_Msg'])) ? $response[0]['Error_Msg'] : $response[0]
			));
		}
	}
	
	/**
	 * Изменение вторичных заболеваний и оппортунистических инфекций в рамках специфики ВИЧ
	 */
	function MorbusHIVSecDiag_put(){
		$data = $this->ProcessInputData('updateMorbusHIVSecDiag', null, true);
		if(empty($data['MorbusHIVSecDiag_id'])){
			$this->response(array(
				'error_code' => 1,
				'error_msg' => 'Отсутствует оябзательный параметр MorbusHIVSecDiag_id'
			));
		}
		
		$result = $this->MorbusHIV_model->getMorbusHIVSecDiagAPI(array('MorbusHIVSecDiag_id' => $data['MorbusHIVSecDiag_id']));
		if(is_array($result) && count($result)>0){
			$params = array(
				'MorbusHIVSecDiag_id' => $data['MorbusHIVSecDiag_id'],
				'MorbusHIV_id' => (!empty($data['MorbusHIV_id'])) ? $data['MorbusHIV_id'] : $result[0]['MorbusHIV_id'],
				'Diag_id' => (!empty($data['Diag_id'])) ? $data['Diag_id'] : $result[0]['Diag_id'],
				'MorbusHIVSecDiag_setDT' => (!empty($data['MorbusHIVSecDiag_setDT'])) ? $data['MorbusHIVSecDiag_setDT'] : date_create($result[0]['MorbusHIVSecDiag_setDT']),
				'EvnNotifyBase_id' => (!empty($data['EvnNotifyBase_id'])) ? $data['EvnNotifyBase_id'] : $result[0]['EvnNotifyBase_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$response = $this->MorbusHIV_model->saveMorbusHIVSecDiag($params);
			if(!empty($response[0]['MorbusHIVSecDiag_id'])){
				$this->response(array(
					'error_code' => 0
				));
			}else{
				$this->response(array(
					'error_code' => 1,
					'error_msg' => (!empty($response[0]['Error_Msg'])) ? $response[0]['Error_Msg'] : $response[0]
				));
			}
		}else{
			$this->response(array(
				'error_code' => 1,
				'error_msg' => 'не найдена специфика вакцинация MorbusHIVSecDiag_id='.$data['MorbusHIVSecDiag_id']
			));
		}
	}
	
	/**
	 * Создание вторичных заболеваний и оппортунистических инфекций в рамках специфики ВИЧ
	 */
	function MorbusHIVSecDiag_get(){
		$data = $this->ProcessInputData('getMorbusHIVSecDiag', null, true);
		if(empty($data['MorbusHIV_id']) && empty($data['MorbusHIVSecDiag_id'])){
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (MorbusHIV_id или MorbusHIVSecDiag_id) должен быть задан',
				'error_code' => '3'
			));
		}		
		$resp = $this->MorbusHIV_model->getMorbusHIVSecDiagAPI($data);		
		$this->response($resp);
	}
}