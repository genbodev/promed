<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'controllers/EvnPrescr.php');

class Samara_EvnPrescr extends EvnPrescr {
	
    /**
     * педикулёз
     */
    const PARAM_TYPE_PED = 12;
    
    /**
     * часотка
     */
    const PARAM_TYPE_SCABIES = 13;
    

    /**
     * часотка
     */
    const PARAM_TYPE_ROST = 14;

	/**
	 * __construct
	 */
    function __construct()
	{
		parent::__construct();
		// print_r('hello world')
    }
	/**
	 * _preparePrintEvnObservGraphsDataSamara
	 */
    function _preparePrintEvnObservGraphsDataSamara( $data ){
        $new = array(
            'date_start' => null,
            'date_finish' => null,
            'dates' => array(),
            'pulse' => array(),
            'breath' => array(),
            'temperature' => array(),
        );
        $min_date = null;
        $max_date = null;
        foreach( $data as $k => $v ){
            
            // Получаем минимальную и максимальную даты
            $date = strtotime( $v['EvnObserv_setDate'] );
            if ( $min_date > $date || $min_date === null ) {
                $min_date = $date;
                $new['date_start'] = $min_date;
            }
            if ( $max_date < $date || $max_date === null ) {
                $max_date = $date;
                $new['date_finish'] = $max_date;
            }
            if ( !in_array( $date, $new['dates'] ) ) {
                $new['dates'][] = $date;
            }
            
            switch( $v['ObservParamType_id'] ){
                case self::PARAM_TYPE_PULSE:
                    $new['pulse'][ $date ][ $v['ObservTimeType_id'] ] = $v['EvnObservData_Value'];
                break;
            
                case self::PARAM_TYPE_BREATH:
                    $new['breath'][ $date ][ $v['ObservTimeType_id'] ] = $v['EvnObservData_Value'];
                break;
            
                case self::PARAM_TYPE_TEMPERATURE:
                    $new['temperature'][ $date ][ $v['ObservTimeType_id'] ] = $v['EvnObservData_Value'];
                break;
                
                case self::PARAM_TYPE_DIASTOLIC:
                    $new['blood_pressure'][ $date ][ $v['ObservTimeType_id'] ]['low'] = $v['EvnObservData_Value'];
                break;
            
                case self::PARAM_TYPE_SYSTOLIC:
                    $new['blood_pressure'][ $date ][ $v['ObservTimeType_id'] ]['high'] = $v['EvnObservData_Value'];
                break;
            
                case self::PARAM_TYPE_WEIGHT:
					// стол (?)
                case self::PARAM_TYPE_AQUA:
                case self::PARAM_TYPE_URINE:
					// суточное количество мокроты (?)
                case self::PARAM_TYPE_FECES:
                case self::PARAM_TYPE_BATH:
                case self::PARAM_TYPE_CLOTH:
                case self::PARAM_TYPE_PED:
                case self::PARAM_TYPE_SCABIES:
                case self::PARAM_TYPE_ROST:
                    $new[ 'param_'.$v['ObservParamType_id'] ][ $date ] = $v['EvnObservData_Value'];
                break;
            }
        }
        
        sort( $new['dates'] );
        
        return $new;
    }
	
	/**
	 * saveEvnCourseTreat
	 */
	function saveEvnCourseTreat() {
    	$this->load->model('Samara_EvnPrescrTreat_model', 'Samara_EvnPrescrTreat_model');
    	$this->inputRules['saveEvnCourseTreat'] = $this->getInputRules('doSaveEvnCourseTreat');
    	$data = $this->ProcessInputData('saveEvnCourseTreat', true, true);
    	if ($data === false) { return false; }
    	$response = $this->Samara_EvnPrescrTreat_model->doSaveEvnCourseTreat($data);
    	$this->ProcessModelSave($response,true,'Ошибка при сохранении курса назначений')->ReturnData();
    	return true;
    }
    
	/**
	 * getInputRules
	 */
    function getInputRules($scenario) {
    	$rules = array();
    	switch ($scenario) {
    		case 'doSaveEvnCourseTreat':
    			$rules = array(
    			array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя род.события','rules' => '', 'default' => 'EvnSection','type' => 'string'),
    			array('field' => 'signature','label' => 'Признак для подписания','rules' => '','type' => 'int'),
    
    			array('field' => 'EvnCourseTreat_id','label' => 'Идентификатор курса','rules' => '','type' => 'id'),
    			array('field' => 'EvnCourseTreat_pid','label' => 'Идентификатор род.события','rules' => 'required','type' => 'id'),
    			array('field' => 'MedPersonal_id','label' => 'Идентификатор ','rules' => 'required','type' => 'id'),
    			array('field' => 'LpuSection_id','label' => 'Идентификатор ','rules' => 'required','type' => 'id'),
    			array('field' => 'Morbus_id','label' => 'Идентификатор ','rules' => '','type' => 'id'),
    			array('field' => 'EvnCourseTreat_setDate','label' => 'Начать','rules' => 'required','type' => 'date'),
    			array('field' => 'EvnCourseTreat_IsPrescrInfusion','label' => 'Приемов в сутки','rules' => 'required','type' => 'int'),
    			array('field' => 'EvnCourseTreat_Duration','label' => 'Продолжительность','rules' => '','type' => 'int'),
    			array('field' => 'DurationType_id','label' => 'Тип продолжительности','rules' => 'required','type' => 'id'),
    			array('field' => 'EvnCourseTreat_ContReception','label' => 'Непрерывный прием','rules' => '','type' => 'int'),
    			array('field' => 'DurationType_recid','label' => 'Тип Непрерывный прием','rules' => 'required','type' => 'id'),
    			array('field' => 'EvnCourseTreat_Interval','label' => 'Перерыв','rules' => '','type' => 'int'),
    			array('field' => 'DurationType_intid','label' => 'Тип Перерыв','rules' => '','type' => 'id'),
    			array('field' => 'ResultDesease_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
    			array('field' => 'PerformanceType_id','label' => 'Исполнение','rules' => '','type' => 'id'),
    			array('field' => 'PrescriptionIntroType_id','label' => 'Способ применения','rules' => '','type' => 'id'), // ipavelpetrov
    			array('field' => 'PrescriptionTreatType_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
				array('field' => 'PrescriptionTimeType_id','label' => 'Время приема','rules' => '','type' => 'id'),
				array('field' => 'PrescriptionTreatOrderType_id','label' => 'Порядок приема','rules' => '','type' => 'id'),
                array('field' => 'EvnCourseTreat_IsPrescrInfusion','label' => 'Порядок приема','rules' => '','type' => 'id'), // ipavelpetrov
    			array('field' => 'DrugListData','label' => 'Медикаменты','rules' => 'required','type' => 'string'),
    			array('field' => 'EvnPrescrTreat_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
    			array('field' => 'EvnPrescrTreat_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
    			array('field' => 'PersonEvn_id','label' => 'Идентификатор','rules' => 'required','type' => 'id'),
    			array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int')
    			);
    			break;
    		case 'doSave':
    			$rules = array(
    			array('field' => 'EvnPrescrTreat_id','label' => 'Идентификатор назначения','rules' => '','type' => 'id'),
    			array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя род.события','rules' => '', 'default' => 'EvnSection','type' => 'string'),
    			array('field' => 'EvnPrescrTreat_pid','label' => 'Идентификатор род.события','rules' => 'required','type' => 'id'),
    			array('field' => 'EvnCourse_id','label' => 'Идентификатор курса','rules' => '','type' => 'id'),
    
    			array('field' => 'signature','label' => 'Признак для подписания','rules' => '','type' => 'int'),
    			array('field' => 'DrugListData','label' => 'Медикаменты','rules' => 'required','type' => 'string'),
    
    			array('field' => 'EvnPrescrTreat_PrescrCount','label' => 'Приемов в сутки','rules' => '','type' => 'int'),
    			array('field' => 'EvnPrescrTreat_setDate','label' => 'Начать','rules' => '','type' => 'date'),
    			array('field' => 'EvnPrescrTreat_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
    			array('field' => 'EvnPrescrTreat_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
    			array('field' => 'PersonEvn_id','label' => 'Идентификатор','rules' => 'required','type' => 'id'),
    			array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int')
    			);
    			break;
    		case 'doLoadEvnCourseTreatEditForm':
    			$rules = array(
    			array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя учетного документа', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
    			array('field' => 'EvnCourseTreat_id','label' => 'Идентификатор курса', 'rules' => 'required', 'type' =>  'id'),
    			);
    			break;
    		case 'doLoad':
    			$rules = array(
    			array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя учетного документа', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
    			array('field' => 'EvnPrescrTreat_id','label' => 'Идентификатор назначения', 'rules' => 'required', 'type' =>  'id'),
    			);
    			break;
    		case 'doLoadEvnDrugGrid':
    			$rules = array(
    			array('field' => 'EvnPrescrTreat_id','label' => 'Идентификатор назначения', 'rules' => 'required', 'type' =>  'id'),
    			);
    			break;
    		case 'doLoadEvnPrescrTreatDrugCombo':
    			$rules = array(
    			array('field' => 'EvnPrescrTreat_pid','label' => 'Идентификатор учетного документа', 'rules' => '', 'type' => 'id'),
    			array('field' => 'EvnPrescrTreat_id','label' => 'Идентификатор назначения', 'rules' => '', 'type' =>  'id'),
    			array('field' => 'EvnPrescrTreatDrug_id','label' => 'Медикамент','rules' => '','type' => 'id'),
    			);
    			break;
    	}
    	return $rules;
    }
    /**
	 * loadEvnPrescrJournalGrid
	 */
    function loadEvnPrescrJournalGrid() {
    	$data = $this->ProcessInputData('loadEvnPrescrJournalGrid', true, true);
    	if ( is_array($data) && count($data) > 0 ) {
    		$this->load->model('Samara_EvnPrescr_model', 'samara_dbmodel');
    		
    		$response = $this->samara_dbmodel->loadEvnPrescrJournalGrid($data);
    		$this->ProcessModelList($response, true, true)->ReturnData();
    		return true;
    	}
    	else {
    		return false;
    	}
    }
    
}