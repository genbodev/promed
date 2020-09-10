<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * ONMKRegister - контроллер для регистра ОНМК
 * @author			Гильмияров Артур
 * @version			01.11.2018
 */

class ONMKRegister extends swController
{
    var $model = "ONMKRegister_model";    
    
    /**
     * comment
     */
    function __construct()
    {
        $this->result = array();
        $this->start  = true;
        
        parent::__construct();
        
        
        
        $this->load->database();
        $this->load->model($this->model, 'dbmodel');
        
        $this->inputRules = array(
            'getOKSdata'=>array(
                array(
                    'field' => 'CmpCallCard_id',
                    'label' => 'CmpCallCard_id',
                    'rules' => '',
                    'type' => 'id'
                )
            ), 			
            'saveOnmkFromKvc' => array(
                array(
                    'field' => 'MorbusType_id',
                    'label' => 'Тип заболевания',
                    'rules' => '',
                    'type' => 'int'
                ),  
                array(
                    'field' => 'Diag_id',
                    'label' => 'Код диагноза',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PainDT',
                    'label' => 'Время начала болевых симптомов',
                    'rules' => '',
                    'type' => 'string',
                ),
                 array(
                    'field' => 'TLTDT',
                    'label' => 'Время проведения ТЛТ',
                    'rules' => '',
                    'type' => 'string',
                ),
                 array(
                    'field' => 'LpuDT',
                    'label' => 'Время прибытия в медицинскую организацию',
                    'rules' => '',
                    'type' => 'string',
                ),
                array(
                    'field' => 'MOHospital',
                    'label' => 'МО госпитализации',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'LeaveType_Name',
                    'label' => 'Исход госпитализации',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'EvnPS_NumCard',
                    'label' => 'Номер КВС',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MRTDT',
                    'label' => 'Исход госпитализации',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'KTDT',
                    'label' => 'Исход госпитализации',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'LpuSection_pid',
                    'label' => 'LpuSection_pid',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedStaffFact_pid',
                    'label' => 'MedStaffFact_pid',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'RankinScale_id',
                    'label' => 'шкалал рэнкина',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'RankinScale_sid',
                    'label' => 'шкалал рэнкина',
                    'rules' => '',
                    'type' => 'int'
                ),				
                array(
                    'field' => 'EvnSection_InsultScale',
                    'label' => 'шкала инсульта',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'LeaveType_id',
                    'label' => 'Исход госпитализации',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'evn_section_id',
                    'label' => 'Идентификатор движения',
                    'rules' => '',
                    'type' => 'int'
                ),
				array(
                    'field' => 'EvnPS_id',
                    'label' => 'Идентификатор КВС',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'saveONMKStatus' => array(
                array(
                    'field' => 'ONMKRegistry_id',
                    'label' => 'Идентификатор случая ОНМК',
                    'rules' => '',
                    'type' => 'int'
                ),  
			),
            'loadSluch' => array(
                array(
                        'field' => 'Person_id',
                        'label' => 'Идентификатор человека',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'ONMKRegister_id',
                        'label' => 'Идентификатор случая',
                        'rules' => '',
                        'type' => 'int'
                )
            ),
            'loadSluchData' => array(
                array(
                        'field' => 'ONMKRegistry_id',
                        'label' => 'Идентификатор случая ОНМК',
                        'rules' => 'required',
                        'type' => 'int'
                ),
                array(
                        'field' => 'DateAdd',
                        'label' => 'дата чего то там',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'VidOplod',
                        'label' => 'вид оплодотворения',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'VidOplat',
                        'label' => 'вид оплаты',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'GenetigDiag',
                        'label' => 'GenetigDiag',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'EnbrionCount',
                        'label' => 'EnbrionCount',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'Result',
                        'label' => 'Result',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'ResultDate',
                        'label' => 'Result',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'dsOsn',
                        'label' => 'основной диагноз',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'vidBer',
                        'label' => 'вид беременности',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'countPlod',
                        'label' => 'количество плодов',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                    'field' => 'pmUser_id',
                    'label' => 'пользователь',
                    'rules' => '',
                    'type' => 'int'
                ),array(
                    'field' => 'lpu_id',
                    'label' => 'ид лпу',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
			'loadEvnUslugaGrid' => array(
                array(
                        'field' => 'EvnPS_id',
                        'label' => 'Идентификатор КВС',
                        'rules' => 'required',
                        'type' => 'string'
                )
			),
			'getDiagList' => array(
                array(
                        'field' => 'Person_id',
                        'label' => 'Идентификатор',
                        'rules' => 'required',
                        'type' => 'int'
                )
			)
			
        );
        
    }
	
    /**
     * Добавление/редактирование случая ОНМК из карточки КВС
     */
    function saveOnmkFromKvc(){
		
        $data = $this->ProcessInputData('saveOnmkFromKvc', true);
		
        if ( $data === false ) { return false; }
		
        $response = $this->dbmodel->saveOnmkFromKvc($data);        
		$this->ReturnData($response);
    }	
		
	
    /**
     * загрузка случаев ОНМК
     */
    function loadSluch() { 
        $data = $this->ProcessInputData('loadSluch', true, true); 
        if ($data) { 
            $this->load->model('ONMKRegister_model', 'ONMKRegister_model'); 
            $response = $this->ONMKRegister_model->loadSluch($data); 
            $this->ProcessModelList($response, true, true)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }
    
    /**
     * загрузка данных случая
     */
    function loadSluchData() { 
        $data = $this->ProcessInputData('loadSluchData', true, true); 
        if ($data) { 
            $this->load->model('ONMKRegister_model', 'ONMKRegister_model'); 
            $response = $this->ONMKRegister_model->loadSluchData($data); 
            $this->ProcessModelList($response, true, $response)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }
	
	/**
	 * 
	 * загрузка услуг
	 */
    function loadEvnUslugaGrid() { 
        $data = $this->ProcessInputData('loadEvnUslugaGrid', true, true); 
        if ($data) { 
            $this->load->model('ONMKRegister_model', 'ONMKRegister_model'); 
            $response = $this->ONMKRegister_model->loadEvnUslugaGrid($data); 
            $this->ProcessModelList($response, true, true)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }	

    /**
     * обновление статуса ОНМК
     */	
	function saveONMKStatus(){
        $data = $this->ProcessInputData('saveONMKStatus', true);
		
        if ( $data === false ) { return false; }
		
        $response = $this->dbmodel->saveONMKStatus($data);        
		$this->ReturnData($response);		
	}
	
    /**
     * загрузка уточненных диагнозов
     */
    function getDiagList() { 
        $data = $this->ProcessInputData('getDiagList', true, true); 
        if ($data) { 
            $this->load->model('ONMKRegister_model', 'ONMKRegister_model'); 
            $response = $this->ONMKRegister_model->getDiagList($data); 
            $this->ProcessModelList($response, true, true)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
	}
	
    /**
     * обновление признаков Подтвержден и Мониторинг
     */
    function updateSluchData() { 

		$this->load->model('ONMKRegister_model', 'ONMKRegister_model'); 
		$response = $this->ONMKRegister_model->updateSluchData();             
		$this->ProcessModelList($response, true, $response)->ReturnData(); 
		return true; 

	}	
}
