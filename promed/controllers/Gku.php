<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Контроллер частичный функционал АРМ ГКУ (клиент Dlo/swWorkPlaceGkuWindow.js)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Gku
 * @access       public
 * @author       Vasinsky Igor (igor.vasinsky@gmail.com)
 * @version      16.06.2015
 *
 * @property Gku_model dbmodel
 */
class Gku extends swController {
    /**
     *  Правила валидации
     */ 
    protected  $inputRules = array(
        'getListEmployes' => array(
            array(
                'field' => 'Org_id',
                'label' => 'Идентификатор организации',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'pmUser_id',
                'label' => 'идентификатор пользователя (руководителя)',
                'rules' => '',
                'type' => 'string'
            )
        ),
		'loadUnitOfTradingList' => array(
			array(
                'field' => 'pmUser_did', 
                'label' => 'сотрудник, закреплённый за лотом', 
                'rules' => '', 
                'type' => 'id' 
            ),
			array(
                'field' => 'DrugRequest_id', 
                'label' => 'сводная заявка', 
                'rules' => 'required', 
                'type' => 'id' 
            ),            
			array(
                'field' => 'DrugFinance_id', 
                'label' => 'Источник финансирования', 
                'rules' => '', 
                'type' => 'id' 
            ),
			array(
                'field' => 'WhsDocumentCostItemType_id', 
                'label' => 'Статья расхода', 
                'rules' => '', 
                'type' => 'id' 
            ),
			array(
                'field' => 'start', 
                'label' => '', 
                'rules' => '', 
                'type' => 'int', 
                'default' => 0 
            ),
			array(
                'field' => 'limit', 
                'label' => '', 
                'rules' => '', 
                'type' => 'int', 
                'default' => 50 
            )
		),    
        'getWhsDocumentUcStatusType'=> array(
        ),
		'loadUnitOfTradingListWithStatus' => array(
			array(
                'field' => 'pmUser_did', 
                'label' => 'сотрудник, закреплённый за лотом', 
                'rules' => '', 
                'type' => 'id' 
            ),
			array(
                'field' => 'isDirector', // т.к. pmUser_id есть всегда, а нам нужен именно сотрудник, а не руководитель
                'label' => 'определение группы пользователя', 
                'rules' => '', 
                'type' => 'id' 
            ),            
			array(
                'field' => 'DrugRequest_id', 
                'label' => 'сводная заявка', 
                'rules' => '', 
                'type' => 'id' 
            ),            
			array(
                'field' => 'DrugFinance_id', 
                'label' => 'Источник финансирования', 
                'rules' => '', 
                'type' => 'id' 
            ),
			array(
                'field' => 'WhsDocumentCostItemType_id', 
                'label' => 'Статья расхода', 
                'rules' => '', 
                'type' => 'id' 
            ),
			array(
                'field' => 'start', 
                'label' => '', 
                'rules' => '', 
                'type' => 'int', 
                'default' => 0 
            ),
			array(
                'field' => 'limit', 
                'label' => '', 
                'rules' => '', 
                'type' => 'int', 
                'default' => 50 
            )
		),            
        'manageLot'=>array(
			array(
                'field' => 'WhsDocumentUc_id', 
                'label' => 'идентификатор лота', 
                'rules' => 'required', 
                'type' => 'id' 
            ),
			array(
                'field' => 'WhsDocumentUcPMUser_id', 
                'label' => 'идентификатор связи лота с пользователем', 
                'rules' => '', 
                'type' => 'id' 
            ),            
			array(
                'field' => 'pmUser_id', 
                'label' => 'идентифактор пользователя - руководителя', 
                'rules' => 'required', 
                'type' => 'id' 
            ), 
			array(
                'field' => 'pmUser_did', 
                'label' => 'иденьифактор пользователя, которого прикрепляют к лоту', 
                'rules' => 'required', 
                'type' => 'id' 
            ),        
			array(
                'field' => 'unassign', 
                'label' => 'признак снятия назначения с лота', 
                'rules' => '', 
                'type' => 'id' 
            ),                          
        ),
		'loadDrugRequest' => array(
			array(
                'field' => 'begDate', 
                'label' => 'Начало периода', 
                'rules' => 'required', 
                'type' => 'date' 
            ),
			array(
                'field' => 'endDate', 
                'label' => 'Окончание периода', 
                'rules' => 'required', 
                'type' => 'date' 
            )
		),   
        'changeWhsDocumentUcStatusType'=> array(
			array(
                'field' => 'WhsDocumentUc_id', 
                'label' => 'Идентификатор лота', 
                'rules' => 'required', 
                'type' => 'string' 
            ),        
			array(
                'field' => 'WhsDocumentUcStatusType_id', 
                'label' => 'Идентификатор статуса лота', 
                'rules' => 'required', 
                'type' => 'id' 
            ),          
        )          
    );    
	/**
	 * Конструктор
	 */
	public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Gku_model', 'dbmodel');
    }
    
    /**
     *  Получение списка статусов лота
     */ 
    function getWhsDocumentUcStatusType(){
		$data = $this->ProcessInputData('getWhsDocumentUcStatusType', true);
		if ( $data === false ) { return false; }

        $list = $this->dbmodel->getWhsDocumentUcStatusType($data);  

        return $this->ReturnData($list);           
     }
     
     /**
      *  Смена статусов лотов
      */ 
    function changeWhsDocumentUcStatusType(){
		$data = $this->ProcessInputData('changeWhsDocumentUcStatusType', true);
		if ($data === false) { return false; }
        $this->load->model('UnitOfTrading_model', 'uotmodel');
        $response = $this->uotmodel->changeUcStatusUnitOfTrading($data);
		$this->ProcessModelSave($response, true)->ReturnData();        
      }
     
     /**
      * Получение списка лотов со статусами в главное окно арма ГКУ
      */ 
    function loadUnitOfTradingListWithStatus(){
		$data = $this->ProcessInputData('loadUnitOfTradingListWithStatus', true);
		if ( $data === false ) { return false; }
        /*
		$response = $this->dbmodel->loadUnitOfTradingListWithStatus($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
        */
        $list = $this->dbmodel->loadUnitOfTradingListWithStatus($data);  

        return $this->ReturnData($list); 		       
     }    
    
    /**
     *  Список сотрудников организации
     */ 
    public function getListEmployes(){
		$data = $this->ProcessInputData('getListEmployes', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getListEmployes($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;        
    }
    
	/**
	 *	Читает список лотов
	 */
	function loadUnitOfTradingList() {
		$data = $this->ProcessInputData('loadUnitOfTradingList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadUnitOfTradingList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}   
    
    /**
     *  Назначение, снятие назначения с лота
     */  
    function manageLot(){
		$data = $this->ProcessInputData('manageLot', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->manageLot($data);
		
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();  
    } 
    
}            