<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* VaccineListFilterGrid.php - контроллер для работы фильтра грида 
*
* 
* @package      
* @access       public
* @author       Нигматуллин Тагир
 *              за основу взят RegistryUfaFilterGrid (Васинский Игорь)
*/

require("Vaccine_List.php");

class Vaccine_ListFilterGrid extends Vaccine_List  {
	var $model_name = "Vaccine_ListFilterGrid_model";
        
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