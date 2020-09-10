<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property Vaccine_List_model $Vaccine_List_model
*/
class Vaccine_List extends swController {

    /**
     * Описание правил для входящих параметров
     * @var array
     */
    public $inputRules = array();
    var $model_name = "Vaccine_List_model";
    
    /**
    * Vaccine_List - контроллер для учета и планирования вакцинации
     */
    function __construct () {
        parent::__construct();
        $this->load->database();
        $this->load->model($this->model_name, "dbmodel");
        
        $this->inputRules['FilterGridPanel'] = array(
                array(
                        'default' => 0,
                        'field' => 'start',
                        'label' => 'Начальный номер записи',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'default' => 1000,
                        'field' => 'limit',
                        'label' => 'Количество возвращаемых записей',
                                'rules' => '',
                'type' => 'int'
                ),
                array(
                        'field' => 'RegistryType_id',
                        'label' => 'Тип реестра',
                        'rules' => 'required',
                        'type' => 'id'
                ),                          
                array(
                        'field' => 'Filter',
                        'label' => 'Строка с параметрами фильтрации',
                        'rules' => '',
                        'type' => 'string'
           )                                 
        ); 

        $this->inputRules = array(
        'getVaccineGridDetail' => array(
            array(
                    'field' => 'Filter',
                    'label' => 'Json строка для фильтра',
                    'rules' => '',
                    'type' => 'string'                   
                )
            
         /*
                array('field' => 'Vaccine_id','label' => 'Идентификатор вакцины','rules' => 'required','type' => 'id'),
                array('field' => 'GRID_NAME_VAC','label' => 'Название вакцины','rules' => '','type' => 'string'),
                array('field' => 'NAME_TYPE_VAC','label' => 'Прививка','rules' => '','type' => 'string'),
                 array('field' => 'Name','label' => 'Наименование вакцины','rules' => '','type' => 'string'),
                array('field' => 'SignComb','label' => 'Признак комбинированности вакцины','rules' => '','type' => 'integer'),
                array('field' => 'CodeInf','CodeInf' => 'Признак комбинированности вакцины','rules' => '','type' => 'int'),
                array('field' => 'NameVac','label' => 'Признак комбинированности вакцины','rules' => '','type' => 'int'),
                array('field' => 'AgeRange','label' => 'Возрастной диапазон','rules' => '','type' => 'string'),
                array('field' => 'AgeRange','label' => 'Возрастной диапазон','rules' => '','type' => 'string'),
                array('field' => 'AgeRange','label' => 'Возраст','rules' => '','type' => 'string'),
                array('field' => 'AgeRange2Sim','label' => 'Возрастной диапазон','rules' => '','type' => 'string'),
                array('field' => 'WAY_PLACE','label' => 'Способ и место введения','rules' => '','type' => 'string')
		*/

        ),
            
        'getNCGrid' => array(
                array('field' => 'NationalCalendarVac_id','label' => 'Идентификатор','rules' => 'required','type' => 'id'),
                array('field' => 'Scheme_id','label' => 'Идентификатор схемы','rules' => '','type' => 'string'),
                array('field' => 'vaccineTypeName','label' => 'Название прививки','rules' => '','type' => 'string'),
                array('field' => 'type_name','label' => 'Вид иммунизации','rules' => '','type' => 'string'),
                 array('field' => 'PeriodVacName','label' => 'Периодичность','rules' => '','type' => 'string'),
                array('field' => 'Age_range','label' => 'Возрастной диапазон ','rules' => '','type' => 'integer')		
        ),

        'GetListJournals' => array(
                array('field' => 'List_Journals_Id','label' => 'Идентификатор','rules' => 'required','type' => 'id'),
                array('field' => 'Name','label' => 'Название журнала','rules' => '','type' => 'string')
        ),

          'FilterPersonVac' => array(
                array(
                        'field' => 'Id',
                        'label' => 'Идентификатор записи',
                        'rules' => '',
                        'type' => 'id'), 
             array(
                        'field' => 'Person_id',
                        'label' => 'Идентификатор пациента',
                        'rules' => '',
                        'type' => 'id'),
              array(
                        'field' => 'VaccineType_id',
                        'label' => 'Идентификатор прививки',
                        'rules' => '',
                        'type' => 'id')

                 ) ,
            'GetListNumSchemeCombo' => array(
                array(
                        'field' => 'VaccineType_id',
                        'label' => 'Идентификатор прививки',
                        'rules' => '',
                        'type' => 'id')
                ),    
            'GetSprInoculation' => array(
                array(
                        'field' => 'Trunc',
                        'label' => 'Trunc',
                        'rules' => '',
                        'type' => 'int')
                ),
            'formPlanVac' => array(
                array(
                        'field' => 'Person_id',
                        'label' => 'Идентификатор пациента',
                        'rules' => '',
                        'type' => 'id'),
                array(
                    'field' => 'DateStart',
                    'label' => 'DateStart',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'DateEnd',
                    'label' => 'DateEnd',
                    'rules' => '',
                    'type' => 'date'), 
                 array(
                    'field' => 'pmUser_id',
                    'label' => 'pmUser_id',
                    'rules' => '',
                    'type' => 'id')
                ),                          
            'RunformPlanVac' => array(
                array(
                        'field' => 'Lpu_id',
                        'label' => 'Идентификатор ЛПУ',
                        'rules' => '',
                        'type' => 'id'),
                array(
                        'field' => 'pmUser_id',
                        'label' => 'Идентификатор пользователя',
                        'rules' => '',
                        'type' => 'id'),
                array(
                    'field' => 'Plan_begDT',
                    'label' => 'Начало периода планирования',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'Plan_endDT',
                    'label' => 'Окончание периода планирования',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'Mode',
                    'label' => 'Параметры планирования',
                    'rules' => '',
                    'type' => 'int'),
                array(
                        'field' => 'Org_id',
                        'label' => 'Место работы/учебы',
                        'rules' => '',
                        'type' => 'int')
                ),

            'vacFormReport_5' => array(
                array(
                    'field' => 'DateStart',
                    'label' => 'DateStart',
                    'rules' => '',
                    'type' => 'date'),
                array(
                    'field' => 'DateEnd',
                    'label' => 'DateEnd',
                    'rules' => '',
                    'type' => 'date'),
                 array(
                        'field' => 'Lpu_id',
                        'label' => 'Идентификатор ЛПУ прикрепления',
                        'rules' => '',
                        'type' => 'int'), 
                array(
                        'field' => 'lpuMedService_id',
                        'label' => 'Идентификатор ЛПУ исполнения',
                        'rules' => '',
                        'type' => 'int'),
                array(
                        'field' => 'Organized',
                        'label' => 'Население',
                        'rules' => '',
                        'type' => 'int'),
                array(
                        'field' => 'MedService_id',
                        'label' => 'Идентификатор службы',
                        'rules' => '',
                        'type' => 'int'),
                array(
                        'field' => 'LpuBuilding_id',
                        'label' => 'Идентификатор подразделения',
                        'rules' => '',
                        'type' => 'id'),
                array(
                        'field' => 'LpuSection_id',
                        'label' => 'Идентификатор отделения',
                        'rules' => '',
                        'type' => 'id'),
                 array(
                        'field' => 'LpuRegion_id',
                        'label' => 'Идентификатор участка',
                        'rules' => '',
                        'type' => 'int')
                ),

            'vacFormReport_5Detail' => array(
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                        'field' => 'Num_Str',
                        'label' => 'Номер строки',
                        'rules' => '',
                        'type' => 'string'),
                array(
                    'field' => 'DateStart',
                    'label' => 'DateStart',
                    'rules' => '',
                    'type' => 'date'),
                array(
                    'field' => 'DateEnd',
                    'label' => 'DateEnd',
                    'rules' => '',
                    'type' => 'date'),
                 array(
                        'field' => 'Lpu_id',
                        'label' => 'Идентификатор ЛПУ прикрепления',
                        'rules' => '',
                        'type' => 'int'),
                 array(
                        'field' => 'lpuMedService_id',
                        'label' => 'Идентификатор ЛПУ исполнения',
                        'rules' => '',
                        'type' => 'int'),
                array(
                        'field' => 'Organized',
                        'label' => 'Население',
                        'rules' => '',
                        'type' => 'int'),
                array(
                        'field' => 'MedService_id',
                        'label' => 'Идентификатор службы',
                        'rules' => '',
                        'type' => 'int'),
                array(
                        'field' => 'LpuBuilding_id',
                        'label' => 'Идентификатор подразделения',
                        'rules' => '',
                        'type' => 'id'),
                array(
                        'field' => 'LpuUnit_id',
                        'label' => 'Идентификатор отделения',
                        'rules' => '',
                        'type' => 'id'),
                array(
                        'default' => null,
                        'field' => 'LpuRegion_id',
                        'label' => 'Идентификатор участка',
                        'rules' => '',
                        'type' => 'int'),
                array(
                        'field' => 'LpuSection_id',
                        'label' => 'Идентификатор отделения',
                        'rules' => '',
                        'type' => 'id')
                ),

            'GetVacListTasks' => array(
                 array(
                    'field' => 'Date_View',
                    'label' => 'Дата постановки задания',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                /*
                array(
                    'field' => 'DateStart',
                    'label' => 'DateStart',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'DateEnd',
                    'label' => 'DateEnd',
                    'rules' => '',
                    'type' => 'date'
                ),
                */
               array(
                        'field' => 'Lpu_id',
                        'label' => 'Идентификатор ЛПУ',
                        'rules' => '',
                        'type' => 'id')

                 ),
              'DelVacRecTasks' => array(
                array(
                        'field' => 'vacFormPlanRun_id',
                        'label' => 'Идентификатор записи',
                        'rules' => 'required|trim',
                        'type' => 'id'
                    )
                ),
            'Vac_Presence_save' => array(
                array(
                    'field' => 'VacPresence_id',
                    'label' => 'VacPresence_id',
                    'rules' => '',
                    'type' => 'id'), 
                array(
                    'field' => 'NewVacPresence_id',
                    'label' => 'NewVacPresence_id',
                    'rules' => '',
                    'type' => 'id'),
                array(
                    'field' => 'Vaccine_id',
                    'label' => 'Vaccine_id',
                    'rules' => '',
                    'type' => 'id'),
                array(
                    'field' => 'Seria',
                    'label' => 'Seria',
                    'rules' => '',
                    'type' => 'string'),
                array(
                    'field' => 'Period',
                    'label' => 'Period',
                    'rules' => '',
                    'type' => 'string'),
                array(
                    'field' => 'toHave',
                    'label' => 'toHave',
                    'rules' => '',
                    'type' => 'int'),
                array(
                    'field' => 'Manufacturer',
                    'label' => 'Manufacturer',
                    'rules' => '',
                    'type' => 'string'),
                array(
                    'field' => 'pmUser_id',
                    'label' => 'pmUser_id',
                    'rules' => '',
                    'type' => 'id'),
                array(
                    'field' => 'action',
                    'label' => 'action',
                    'rules' => '',
                    'type' => 'string')
                 ),
            'Vac_saveSprNC' => array(
                array(
                    'field' => 'NationalCalendarVac_id',
                    'label' => 'NationalCalendarVac_id',
                    'rules' => '',
                    'type' => 'id'),  
                array(
                    'field' => 'VaccineType_id',
                    'label' => 'VaccineType_id',
                    'rules' => '',
                    'type' => 'id'),
                array(
                    'field' => 'SequenceVac',
                    'label' => 'SequenceVac',
                    'rules' => '',
                    'type' => 'int'),
                array(
                    'field' => 'Type_id',
                    'label' => 'Type_id',
                    'rules' => '',
                    'type' => 'int'),
                array(
                    'field' => 'SignPurpose',
                    'label' => 'SignPurpose',
                    'rules' => '',
                    'type' => 'string'),
                array(
                    'field' => 'Scheme_id',
                    'label' => 'Scheme_id',
                    'rules' => '',
                    'type' => 'string'),
                array(
                    'field' => 'Scheme_Num',
                    'label' => 'Scheme_Num',
                    'rules' => '',
                    'type' => 'int'),    
                array(
                    'field' => 'AgeTypeS',
                    'label' => 'AgeTypeS',
                    'rules' => '',
                    'type' => 'int'),
                array(
                    'field' => 'AgeS',
                    'label' => 'AgeS',
                    'rules' => '',
                    'type' => 'int'),
                array(
                    'field' => 'AgeTypeE',
                    'label' => 'AgeTypeE',
                    'rules' => '',
                    'type' => 'int'),
                array(
                    'field' => 'AgeE',
                    'label' => 'AgeE',
                    'rules' => '',
                    'type' => 'int'),
                array(
                    'field' => 'PeriodVac',
                    'label' => 'PeriodVac',
                    'rules' => '',
                    'type' => 'int'),
                 array(
                    'field' => 'PeriodVacType',
                    'label' => 'PeriodVacType',
                    'rules' => '',
                    'type' => 'int'),
                array(
                    'field' => 'Additional',
                    'label' => 'Additional',
                    'rules' => '',
                    'type' => 'int')
                 )

        );

        $this->load->database();
        $this->load->model('Vaccine_List_model', 'Vaccine_List_model');
    }
		
    	/**
	 * Получение списка Вакцин из справочника вакцин
	 * Используется: окно просмотра и редактирования справочника вакцин
	 */
	
    function getVaccineGridDetail() {
        log_message('debug', 'getVaccineGridDetail_Control');
        $data = $this->ProcessInputData('getVaccineGridDetail', true, true); 
        $response = $this->dbmodel->getVaccineGridDetail($data);
        if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
            return false;
        }                        
    }
                
    /**
     * Просмотр Национального календаря прививок	 
     */
    
    function getNCGrid() {
        $response = $this->Vaccine_List_model->getNCGrid();
        if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
                return false;
        }                        
    }
                
    /**
     * Просмотр списка журналов
     */
    function GetListJournals() {
        $response = $this->Vaccine_List_model->GetListJournals();
        if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
                return false;
        }                        
    }
         
    /**
     * Просмотр манту
     */                            
    function getPersonVacMantuAll() {           
        $data = $this->ProcessInputData('FilterPersonVac', true, true);              
        $response = $this->Vaccine_List_model->getPersonVacMantuAll($data);                   
        if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
                return false;
        }                        
    }

    /**
     * @return bool
     */
    function getPersonDiaskinAll() {
        $data = $this->ProcessInputData('FilterPersonVac', true, true);
        $response = $this->Vaccine_List_model->getPersonDiaskinAll($data);
        if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
            return false;
        }
    }

                
    /**
    * Формирование плана для конкретного пациента
    */
    public function formPlanVac() { 
        $data = $this->ProcessInputData('formPlanVac', true);        
        if ($data === false) { return false; }

        $val  = array();
        $response = $this->Vaccine_List_model->formPlanVac($data);

        foreach ($response as $row) {
            array_walk($row, 'ConvertFromWin1251ToUTF8');
            $val[] = $row;
        }

        Echo '{rows:'.json_encode($val).'}';

        return true;    
    }      
            
    /**
    * Формирование плана для ЛПУ
    */
    public function RunformPlanVac() {
        $data = $this->ProcessInputData('RunformPlanVac', true);
        if ($data === false) { return false; }   
        //log_message('debug', 'Org_id2='.$data['Org_id']);
        $val  = array();
        $response = $this->Vaccine_List_model->RunformPlanVac($data);

        foreach ($response as $row) {
            array_walk($row, 'ConvertFromWin1251ToUTF8');
            $val[] = $row;
        }
		Echo '{rows:'.json_encode($val).'}';
        return true;    
    }      

                /**
            * Формирование списка заданий
            */
    public function GetVacListTasks() {
        $data = $this->ProcessInputData('GetVacListTasks', true, true); 
        $response = $this->Vaccine_List_model->GetVacListTasks($data);
        if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
            return false;
        }   
    }
            
                /**
                 * Снять задание с выполнения
                 */
    public function DelVacRecTasks() {
        $data = $this->ProcessInputData('DelVacRecTasks', true);
        if ($data === false) {
            return false;
        }
        $val = array();
        $response = $this->Vaccine_List_model->DelVacRecTasks($data);

        foreach ($response as $row) {
            array_walk($row, 'ConvertFromWin1251ToUTF8');
            $val[] = $row;
        }
        echo json_encode(array('success' => true, 'rows' => $val));
        return true;
    }
    
                 /**
            * Формирование отчета формы № 5
            */
            
    public function vacFormReport_5() {
        $data = $this->ProcessInputData('vacFormReport_5', true);
        if ($data === false) { return false; }
        if (isset($data['Lpu_id'])) {
            log_message('debug', 'Lpu_id='.$data['Lpu_id']);
        }
        
        $val  = array();
        $response = $this->Vaccine_List_model->vacFormReport_5($data); 
        if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
            return false;
        }   
    }
           
            /**
            * Детализация строки  отчета формы № 5
            */
            
    public function vacFormReport_5Detail() {
               
        $data = $this->ProcessInputData('vacFormReport_5Detail', true);
        if ($data === false) { return false; }  
         log_message('debug', 'lpuMedService_id0='.$data['lpuMedService_id']);
        $listData = $this->Vaccine_List_model->vacFormReport_5Detail($data);
        if (is_array($listData) && count($listData) > 0) {
            $val = null;
            $val = array();
            $count = 0;
            foreach ($listData as $row) {
                if (isset($row['__countOfAllRows'])) {
                    $count = $row['__countOfAllRows'];
                }
				else {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val['data'][] = $row;
					$count++;
				}
			}	
			$val['totalCount'] = $count;
        }

        if ($val['totalCount'] == 0 && !isset($val['data'])) {
            //data обязательно должен присутствовать, иначе листалка глючит при пустом storage
            $val['data'] = array();
        }
            $this->ReturnData($val);
        }

     /**
            * Формирование списка наличия вакцин
            */
    public function GetVacPresence() {
              
        $response = $this->Vaccine_List_model->GetVacPresence();
        if(is_array($response)) {
                    $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
                        return false;
                }   
            }
            
    /**
    * Формирование списка  вакцин для комбобокса
    */
    public function getVaccine4Combo() {
                
        $val = array();
        $response = $this->Vaccine_List_model->getVaccine4Combo();
        foreach ($response as $row)
    		{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
                }

		 Echo '{rows:'.json_encode($val).'}';

		return true;
            }
            
            /**
            * Редактирование наличия вакцин
            */
    public function Vac_Presence_save() {
        $data = $this->ProcessInputData('Vac_Presence_save', true);
        if ($data === false) { return false; }

                $val  = array();
                $response = $this->Vaccine_List_model->Vac_Presence_save($data);

        foreach ($response as $row) {
                array_walk($row, 'ConvertFromWin1251ToUTF8');
                $val[] = $row;
                }
                echo json_encode(array('success' => true, 'rows' => $val));

                return true;    
            }   //  end Vac_Presence_save   
            
            /**
            * Получаем список прививок
            */
            
    Public function GetSprInoculation(){
                 $data = $this->ProcessInputData('GetSprInoculation', true);
		$val  = array();
                $this->load->database();
                $response = $this->Vaccine_List_model->GetSprInoculation($data);

        foreach ($response as $row)
    		{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
                }

		 Echo '{rows:'.json_encode($val).'}';

		return true;
        }
         
            /**
            * Получаем список номеров схем
            */
            
    Public function GetListNumSchemeCombo(){
 
               $data = $this->ProcessInputData('GetListNumSchemeCombo', true); 
                $val  = array();
                $this->load->database();
                $this->load->model('Vaccine_List_model', 'dbmodel');
		$response = $this->dbmodel->GetListNumSchemeCombo($data);

              
        foreach ($response as $row)
    		{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
                }

		 Echo '{rows:'.json_encode($val).'}';

		return true;
        }
     
         /**
            * Получаем список типов иммунизации
            */
            
    Public function getVaccineTypeImmunization(){
 
		
                $data = array();
                $val  = array();
                $this->load->database();
                $this->load->model('Vaccine_List_model', 'dbmodel');
		$response = $this->dbmodel->getVaccineTypeImmunization($data);

              
        foreach ($response as $row)
    		{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
                }

		 Echo '{rows:'.json_encode($val).'}';

		return true;
        }  //  end getVaccineTypeImmunization
        
        
            /**
            * Редактирование Национального Календаря прививок
            */
        
    public function Vac_saveSprNC() {
        $data = $this->ProcessInputData('Vac_saveSprNC', true);
        log_message('debug', 'Additional = '.$data['Additional']);
        if ($data === false) { return false; }

        $val  = array();
        $response = $this->Vaccine_List_model->Vac_saveSprNC($data);

		foreach ($response as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}

            echo json_encode(array('success' => true, 'rows' => $val));

            return true;
        }   //  end Vac_saveSprNC  
        
        
}

?>
