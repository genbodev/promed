<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* VaccineCtrlFilterGrid.php - контроллер для работы фильтра грида 
* клиентская частьamm_PresenceVacForm.js
* 
* @package      
* @access       public
* @author       Нигматуллин Тагир
 *              за основу взят RegistryUfaFilterGrid (Васинский Игорь)
*/
require("VaccineCtrl.php");


class VaccineCtrlFilterGrid extends VaccineCtrl {
	var $model_name = "VaccinectrlfilterGrid_model";
	/**
	* comments
	*/
	function __construct() {
		parent::__construct();
        
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
				'field' => 'Filter',
				'label' => 'Строка с параметрами фильтрации',
				'rules' => '',
				'type' => 'string'
		   )                                 
		); 
                

		$this->inputRules['GetVacPresenceFilter'] = array_merge(
				$this->inputRules['FilterGridPanel'], array()
		);
                
                $this->inputRules['GetVacAssigned4CabVacFilter'] = array_merge(
				$this->inputRules['FilterGridPanel'], array()
		);
                 $this->inputRules['GetVacAssigned4CabVacFilter'] = array_merge(
				$this->inputRules['GetVacAssigned4CabVac'], array()
		);
                 
                 $this->inputRules['getVaccineGridDetailFilter'] = array_merge(
				$this->inputRules['FilterGridPanel'], array()
		);

		
		$this->inputRules['Filter'] =  array(
			array(
				'field' => 'Filter',
				'label' => 'Json строка для фильтра',
				'rules' => '',
				'type' => 'string'                   
			)
		);

  }
  
                
	/**
	 *  Получение списка вакцин справочника "Наличие вакцин"
         * 
	 * Входящие данные: _POST (Registry_id, (string))Filter),
	 * На выходе: строка в JSON-формате
	 *
	 * @return string
	 */
	function GetVacPresenceFilter()
	{       
		log_message('debug', 'GetVacPresenceFilter');
                $data = $this->ProcessInputData('GetVacPresenceFilter', true);
		if ($data === false) { 
                     log_message('debug', 'Возврат: false');
                    return false; 
                    
                }
		
                //$this->dbmodel="VaccinectrlfilterGrid_model";
                $this->load->model("VaccinectrlfilterGrid_model", "dbmodel");
                //var_dump($this->dbmodel);
		$response = $this->dbmodel->GetVacPresenceFilter($data);
                log_message('debug', 'Return: true');
		//$this->ProcessModelMultiList($response, true, true)->ReturnData();
                
                array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData($response);
	}
        
        /**
	 *  Получение списка вакцин справочника "Наличие вакцин"
         * 
	 * Входящие данные: _POST (Registry_id, (string))Filter),
	 * На выходе: строка в JSON-формате
	 *
	 * @return string
	 */
	function GetVacAssigned4CabVacFilter()
	{       
		log_message('debug', 'GetVacAssigned4CabVacFilter');
                $data = $this->ProcessInputData('GetVacAssigned4CabVacFilter', true);
		if ($data === false) { 
                     log_message('debug', 'Возврат: false');
                    return false; 
                    
                }
		
                //$this->dbmodel="VaccinectrlfilterGrid_model";
                $this->load->model("VaccinectrlfilterGrid_model", "dbmodel");
                //var_dump($this->dbmodel);
		$response = $this->dbmodel->GetVacAssigned4CabVacFilter($data);
                //log_message('debug', 'begDate = '.$data['begDate']);
		//$this->ProcessModelMultiList($response, true, true)->ReturnData();
                
                array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData($response);
	}
	
	/**
	 * Получение списка Вакцин из справочника вакцин
	 * Используется: окно просмотра и редактирования справочника вакцин
	 */
	
	function getVaccineGridDetailFilter() {
		//function GetVacPresenceFilter()
		{       
		log_message('debug', 'getVaccineGridDetailFilter');
                $data = $this->ProcessInputData('getVaccineGridDetailFilter', true);
		if ($data === false) { 
                     log_message('debug', 'Возврат: false');
                    return false; 
                    
                }
		
                //$this->dbmodel="VaccineListFilterGrid_model";
                $this->load->model("VaccineListFilterGrid_model", "dbmodel");
                //var_dump($this->dbmodel);
		$response = $this->dbmodel->getVaccineGridDetailFilter($data);
                log_message('debug', 'Return: true');
		//$this->ProcessModelMultiList($response, true, true)->ReturnData();
                
                array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData($response);
		};
        }

 }