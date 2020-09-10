<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* VaccinectrlfilterGrid_model - модель для работы фильтра грида
* клиентская часть amm_PresenceVacForm.js
* 
* @package      Admin
* @access       public
* @version      26/04/2013
*/

require("VaccineCtrl_model.php"); 

class VaccinectrlfilterGrid_model extends VaccineCtrl_model {
     
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
				SELECT  ".$distinct." field
					FROM temptable with(nolock)
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
	 * Получение списка вакцин справочника "Наличие вакцин"
	 */ 
      
        
        /**
	* Построение фильтра грида для справочника "Наличие вакцин"
	*/	
        
	function GetVacPresenceFilter($data) {
           
                $params = "";
                $data['start'] = 0;
                $data['limit'] = 100;
                    

		//Фильтр грида
		$json = isset($data['Filter']) ? iconv('windows-1251', 'utf-8', trim($data['Filter'],'"')) : false;
		//echo $json.'<br/>';
		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false; 
		//var_dump($filter_mode); 
		//echo '<pre>' . print_r($filter_mode, 1) . '</pre>';
		
		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			//'Registry_id' => $data['Registry_id'],
			'Lpu_id'=>$data['session']['lpu_id'],
			'Value'=>($filter_mode['value'] == "_") ? "%%" : trim(iconv('utf-8','windows-1251',$filter_mode['value']))."%"
		);
                
                
                $distinct = "distinct";

		
		$filter="(1=1)";

		$join = "";
		$fields = "";
                
		$field = $filter_mode['cell'];
					  
		$query = " Select 
					-- select
					distinct {$this->getNameColumn($field)} 
					-- end select    
			FROM 
			-- from
				vac.v_VacPresence  WITH (NOLOCK)
			-- end from    
			where 
			-- where
				lpu_id = {$data['session']['lpu_id']}
			-- end where    
		order by 
		-- order by
		   {$this->getNameColumn($field)}
		-- end order by   ";// .$params.Lpu_id;
		
		 log_message('debug', '$query=' .$query);
		 
		 // log_message('debug', '$orderBy=' .$this->getNameColumn($field));
		  
		 
		  log_message('debug', 'start=' . $data['start']);
		  
		   log_message('debug', 'limit=' . $data['limit']);
		   

	   
		$Like = ($filter_mode['specific'] === false) ? "" : " and ".$this->getNameColumn($field)." like  :Value";  
	   
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
                
              
        } // end loadRegistryDataFilter
       
        /**
	* Построение фильтра грида для АРМ каб. вакцинации
	*/	
        
	function GetVacAssigned4CabVacFilter($data) {
            
                $params = "";
                $data['start'] = 0;
                $data['limit'] = 100;
                
                $filters = array();
                $join = '';
                $queryParams = array();
                
                $this->genSearchFilters(TYPE_JOURNAL::VAC_4CabVac, $data, $filters, $queryParams, $join);
                $filter = "" .Implode(' and ', $filters);
                log_message('debug', 'GetVacAssigned4CabVacFilter: $filter =' .$filter);

		//Фильтр грида
		$json = isset($data['Filter']) ? iconv('windows-1251', 'utf-8', trim($data['Filter'],'"')) : false;
		//echo $json.'<br/>';
		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false; 
		//var_dump($filter_mode); 
		//echo '<pre>' . print_r($filter_mode, 1) . '</pre>';
		
		// Взависимости от типа реестра возвращаем разные наборы данных
               // $queryParams
		$params = array(
			//'Registry_id' => $data['Registry_id'],
			'Lpu_id'=>$data['session']['lpu_id'],
			'Value'=>($filter_mode['value'] == "_") ? "%%" : trim(iconv('utf-8','windows-1251',$filter_mode['value']))."%"
		);
                
                
                $distinct = "distinct";

		
		//$filter="(1=1)";

		$join = "";
		$fields = "";
                
		$field = $filter_mode['cell'];
		
		if ($field == 'Infection') {
			$query = " Select 
									-- select
									distinct {$this->getNameColumn($field)}
									-- end select    
					FROM 
					-- from
					 (Select  tp.VaccineType_Name, ac.*
							FROM 
							
								vac.v_JournalVac ac  WITH (NOLOCK)
								join	vac.Inoculation i  WITH (NOLOCK) on ac.vacJournalAccount_id = i.vacJournalAccount_id
								join vac.S_VaccineType tp  WITH (NOLOCK) on i.VaccineType_id = tp.VaccineType_id
							 where " . $filter. " )t
							-- end from     
						order by 
						-- order by
						   {$this->getNameColumn($field)}
						-- end order by   ";
		}
		else {
			$query = " Select 
									-- select
									distinct {$this->getNameColumn($field)}
									-- end select    
					FROM 
					-- from
							vac.v_JournalVac  WITH (NOLOCK)
					-- end from    
					where 
					-- where
							" . $filter. "
					-- end where    
			order by 
			-- order by
			   {$this->getNameColumn($field)}
			-- end order by   ";// .$params.Lpu_id;
		}
		 log_message('debug', '$query=' .$query);
                  log_message('debug', '$field=' .$field);
		 
		 // log_message('debug', '$orderBy=' .$this->getNameColumn($field));
		  
		 
		  log_message('debug', 'start=' . $data['start']);
		  
		   log_message('debug', 'limit=' . $data['limit']);
		   

	   
		$Like = ($filter_mode['specific'] === false) ? "" : " and ".$this->getNameColumn($field)." like  :Value";  
	   
		$result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $this->getNameColumn($field), $data['start'], $data['limit'], $Like, $this->getNameColumn($field)), $queryParams);
	  
		   
		$result_count = $this->db->query($this->_getCountSQLPH($query, $this->getNameColumn($field), $distinct, $this->getNameColumn($field)), $queryParams);
               
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
                
        }  //  end GetVacAssigned4CabVacFilter
		
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
									distinct {$this->getNameColumn($field)}
									-- end select    
					FROM 
					-- from
					 (Select  tp.VaccineType_Name, vac.*
							FROM 
							
								vac.v_Vaccine vac  WITH (NOLOCK)
								join	vac.S_VaccineRelType rel   WITH (NOLOCK) on vac.Vaccine_id = rel.Vaccine_id
								join vac.S_VaccineType tp  WITH (NOLOCK) on rel.VaccineType_id = tp.VaccineType_id
							)t
							-- end from     
						order by 
						-- order by
						   {$this->getNameColumn($field)}
						-- end order by   ";
		}
		else {
			$query = " 
			SELECT 
				-- select
				distinct {$this->getNameColumn($field)} 
	-- end select    
				  FROM 
				  -- from
					vac.v_Vaccine vac  WITH (NOLOCK)
					-- end from    
				   order by 
			-- order by
			   {$this->getNameColumn($field)}
			-- end order by  
				  
			";
		}
		   

	   
		$Like = ($filter_mode['specific'] === false) ? "" : " and ".$this->getNameColumn($field)." like  :Value";  
	   
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
                              
        }
        
}
?>