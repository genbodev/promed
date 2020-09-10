<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* VaccineList_model - модель для работы фильтра грида
* клиентская часть amm_PresenceVacForm.js
* 
* @package      Admin
* @access       public
* @version      26/04/2013
*/

require("Vaccine_List_model.php");

class Vaccine_ListFilterGrid_model extends Vaccine_List_model {
     
	 /**
     * Constructor
     */
	 
    function __construct()
	{
		parent::__construct();
	}        
        
	/**
	* 2 метода из хелпера SQL Sql_helper.php - переписаны под себя - для пагинации данных в окне фильтра
	*/
	function _getLimitSQLPH($query, $distinct, $field, $start = 0, $limit = 1000, $like, $order_row=''){
		$start = ($start == 0) ? (int)0 : $start;
		
		$exp = preg_match("/--[\s]*select([\w\W]*)--[\s]*end select/i", $query, $maches);
		$select = $maches[1];

		$exp = preg_match("/--[\s]*from([\w\W]*)--[\s]*end from/i", $query, $maches);
		$from = $maches[1];
	
		$exp = preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $query, $maches);
		if ( isset($maches[1]) )
		{
			$where = $maches[1] . " ".$like;
		}
                /*
		$query = "WITH temptable AS
				(
					SELECT   ".$order_row." field,
					ROW_NUMBER() OVER (ORDER BY ".$order_row.") AS RowNumber
					FROM ".$from."  
					-- WHERE ".$order_row." IS NOT NULL 
					".(empty($where)?"":"WHERE ".$where)." 
				) 
				SELECT  ".$distinct." field
					FROM temptable with(nolock)
				WHERE RowNumber BETWEEN ".$start." AND ".$limit." ".(!empty($order_row) ? "" : "field").".;";     
		*/
                $query = "WITH temptable AS
				(
					Select field,   
                                            ROW_NUMBER() OVER (ORDER BY field) AS RowNumber
                                        from (
                                        SELECT   distinct ".$order_row." field
					FROM ".$from."  
					-- WHERE ".$order_row." IS NOT NULL 
					".(empty($where)?"":"WHERE ".$where)." 
                                            ) t
				) 
				SELECT ".$distinct."
					field as field
				FROM temptable
				WHERE RowNumber BETWEEN ".$start." AND ".$limit." ".(!empty($order_row) ? "" : "field").";";  
	
		return $query;

	} 
	/**
	* comments
	*/	 
	function _getCountSQLPH($sql, $field, $distinct, $orderBy) {
		$sql = preg_replace("/--[\s]*select[\w\W]*--[\s]*end select/i", " count( ". $distinct ." ".$orderBy." ) AS cnt ", $sql);

		$exp = preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $sql, $maches);
		if ( isset($maches[1]) )
		{
			$where = $maches[1];
		}

		$sql = preg_replace("/ORDER BY[\s]*--[\s]*order by[\w\W]*--[\s]*end order by/i", "", $sql);

		$sql = preg_replace("/GROUP BY[\s]*--[\s]*group by[\w\W]*--[\s]*end group by/i", "", $sql);
	
		return $sql;
	}
	
	/** end*/    

        /**
	 * Получение списка Вакцин из справочника вакцин
	 * Используется: окно просмотра и редактирования справочника вакцин
	 */
        
	function getVaccineGridDetailFilter($data) {
           
                $params = "";
                $data['start'] = 0;
                $data['limit'] = 100;
                    

		//Фильтр грида
		$json = isset($data['Filter']) ? trim($data['Filter'],'"') : false;
		//echo $json.'<br/>';
		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false; 
		//var_dump($filter_mode); 
		//echo '<pre>' . print_r($filter_mode, 1) . '</pre>';
		
		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			//'Registry_id' => $data['Registry_id'],
			//'Lpu_id'=>$data['session']['lpu_id'],
			'Value'=>($filter_mode['value'] == "_") ? "%%" : trim($filter_mode['value'])."%"
		);
                
                
                $distinct = "distinct";

		
		$filter="(1=1)";

		$join = "";
		$fields = "";
                
		$field = $filter_mode['cell'];
                    
		if ($field == 'Vaccine_NameInfection') {
			$query = " Select 
						-- select
						distinct {$this->getNameColumn($field)} as \"{$this->getNameColumn($field)}\"
						-- end select    
					FROM 
					-- from
					 (Select  tp.VaccineType_Name, vac.*
							FROM 
							
								vac.v_Vaccine vac
								join	vac.S_VaccineRelType rel on vac.Vaccine_id = rel.Vaccine_id
								join vac.S_VaccineType tp on rel.VaccineType_id = tp.VaccineType_id
							)t
							-- end from     
						order by 
						-- order by
						   \"{$this->getNameColumn($field)}\"
						-- end order by   ";
		}
		else {
			$query = " 
			SELECT 
				-- select
				distinct {$this->getNameColumn($field)} as \"{$this->getNameColumn($field)}\"
	-- end select    
				  FROM 
				  -- from
					vac.v_Vaccine vac
					-- end from    
				   order by 
			-- order by
			   \"{$this->getNameColumn($field)}\"
			-- end order by  
				  
			";
		}
		   

	   
		$Like = ($filter_mode['specific'] === false) ? "" : " and ".$this->getNameColumn($field)." ilike  :Value";
	   
		$result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $this->getNameColumn($field), $data['start'], $data['limit'], $Like, $this->getNameColumn($field)), $params);
	  
		   
		$result_count = $this->db->query($this->_getCountSQLPH($query, $this->getNameColumn($field), $distinct, $this->getNameColumn($field)), $params);
               
	    if (is_object($result_count))
		{   
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
                        log_message('debug', '$count = ' .$count);
			return $response;
		}
		else
		{
			return false;
		}
                
              
        } // end getVaccineGridDetailFilter
       
     
        
        /**
	 * конвертер dataIndex в name column DB
	 */
	public function getNameColumn($dataIndex){
		switch($dataIndex){
			case 'Vaccine_FullName': 
				$column = 'Vaccine_FullName'; 
				break;
			case 'Vaccine_NameInfection': 
				$column = 'VaccineType_Name'; 
				break;
			
		}
		return $column;
	}    //  end getNameColumn
  

        
}
