<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* OnkoCtrlFilterGrid.php - контроллер для работы фильтра грида 
* 
* @package      
* @access       public
* @author       Нигматуллин Тагир
 *              за основу взят RegistryUfaFilterGrid (Васинский Игорь)
*/
require("OnkoCtrl.php");


class OnkoCtrlFilterGrid extends OnkoCtrl {
	var $model_name = "OnkoctrlfilterGrid_model";
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
                

		$this->inputRules['GetOnkoCtrlProfileJurnalFilter'] = array_merge(
				$this->inputRules['FilterGridPanel'], array()
		);
                
                $this->inputRules['GetOnkoCtrlProfileJurnalFilter'] = array_merge(
				$this->inputRules['GetOnkoCtrlProfileJurnal'], array()
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
	 *  Журнал анкетирования
         * 
	 * Входящие данные: _POST (Registry_id, (string))Filter),
	 * На выходе: строка в JSON-формате
	 *
	 * @return string
	 */
	function GetOnkoCtrlProfileJurnalFilter()
	{       
		log_message('debug', 'GetOnkoCtrlProfileJurnalFilter');
                $data = $this->ProcessInputData('GetOnkoCtrlProfileJurnalFilter', true);
                
		if ($data === false) { 
                     log_message('debug', 'Возврат: false');
                    return false; 
                    
                }
		
                //$this->dbmodel="VaccinectrlfilterGrid_model";
                $this->load->model("OnkoctrlfilterGrid_model", "dbmodel");
                //var_dump($this->dbmodel);
		$response = $this->dbmodel->GetOnkoCtrlProfileJurnalFilter($data);
                log_message('debug', 'Return: true');
		//$this->ProcessModelMultiList($response, true, true)->ReturnData();
                
                array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData($response);
	}


 }