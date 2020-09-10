<?php

defined('BASEPATH') or die('No direct script access allowed');
/**
 * VaccinectrlfilterGrid_model - модель для работы фильтра грида
 * клиентская часть amm_PresenceVacForm.js
 * 
 * @package      Admin
 * @access       public
 * @version      26/04/2013
 */
require("OnkoCtrl_model.php");


class OnkoctrlfilterGrid_model extends OnkoCtrl_model {

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * 2 метода из хелпера SQL Sql_helper.php - переписаны под себя - для пагинации данных в окне фильтра
     */
    function _getLimitSQLPH($query, $distinct, $field, $start = 0, $limit = 100, $like, $order_row = '') {
        $start = ($start == 0) ? (int) 0 : $start;
        if ($start == 0) 
            $end = $start + $limit;
        else
            $end = $start + $limit - 1;
        $exp = preg_match("/--[\s]*select([\w\W]*)--[\s]*end select/i", $query, $maches);
        $select = $maches[1];

        $exp = preg_match("/--[\s]*from([\w\W]*)--[\s]*end from/i", $query, $maches);
        $from = $maches[1];

        $exp = preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $query, $maches);
        if (isset($maches[1])) {
            $where = $maches[1] . " " . $like;
        }
         //log_message('debug', '_getLimitSQLPH: $maches=' . $maches[1]);
        $query = "WITH temptable AS
				(
					Select field,   
                                            ROW_NUMBER() OVER (ORDER BY field) AS RowNumber
                                        from (
                                        SELECT   distinct " . $order_row . " field
					FROM " . $from . "  
					-- WHERE " . $order_row . " IS NOT NULL 
					" . (empty($where) ? "" : "WHERE " . $where) . " 
                                            ) t
				) 
				SELECT  " . $distinct . " field
					FROM temptable with(nolock)
				WHERE RowNumber BETWEEN " . $start  . " AND " . $end . " " . (!empty($order_row) ? "" : "field") . ";";


        //log_message('debug', '_getLimitSQLPH: $query=' . $query);

        return $query;
    }

    /**
     * comments
     */
    function _getCountSQLPH($sql, $field, $distinct, $like, $orderBy) {
        $where = '';
        $sql = preg_replace("/--[\s]*select[\w\W]*--[\s]*end select/i", " count( " . $distinct . " " . $orderBy . " ) AS cnt ", $sql);

        $exp = preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $sql, $maches);
        if (isset($maches[1])) {
            $where = $maches[1] .' ' .$like;
        }
        
        $sql = preg_replace("/--[\s]*where([\w\W]*)--[\s]*end where/i", $where, $sql);

        $sql = preg_replace("/ORDER BY[\s]*--[\s]*order by[\w\W]*--[\s]*end order by/i", "", $sql);

        $sql = preg_replace("/GROUP BY[\s]*--[\s]*group by[\w\W]*--[\s]*end group by/i", "", $sql);
        
        //$sql = $sql .(empty($where) ? "" : "WHERE " . $where);
        
        //log_message('debug', '_getCountSQLPH: $sql=' . $sql);

        return $sql;
    }

    /** end */

    /**
     * Построение фильтра грида для журнала анкетирования по онкоконтролю
     */
    
    function GetOnkoCtrlProfileJurnalFilter($data) {

        $params = "";
        $queryParams = array();
        $filters = array();
        $Lpu_id = '';
        $Surname = "NULL";
        $Firname = "NULL";
        $SecName = "NULL";
        $BirthDay = "NULL";
        $BirthDayBeg = "NULL";
        $BirthDayEnd = "NULL";
        $Empty = "0";
        //log_message('debug', 'start0=' . $data['start']);
        if (!isset($data['start'])) {
            $data['start'] = 0;
        }
         //log_message('debug', 'start1=' . $data['start']);
        //$data['start'] = 0;
        $data['limit'] = 100;
        $function = 'onko.fn_GetOnkoCtrlProfileJurnal';

        $filter = '(1=1)';

        if ($data['OnkoType_id'] == 2) {
            $function = 'onko.fn_GetOnkoCtrlProfileJurnalFull';
        }

        if (isset($data['Lpu_id'])) {
            $Lpu_id = $data['Lpu_id'];
            $filter .= " and Lpu_id = " .$data['Lpu_id']; 
        }
        if (isset($data['SurName'])) {
            $Surname = "'" . $data['SurName'] . "%'";
            $filter .= " and SurName like  " .$data['SurName'] ."%"; 
        };

        if (isset($data['FirName'])) {
            $Firname = "'" . $data['FirName'] . "%'";
            $filter .= " and FirName like  " .$data['FirName'] ."%"; 
        };

        if (isset($data['SecName'])) {
            $SecName = "'" . $data['SecName'] . "%'";
            $filter .= " and SecName like  " .$data['SecName'] ."%"; 
        };

        if (isset($data['BirthDayRange'][1])) {
            $BirthDayEnd = "'" . $data['BirthDayRange'][1] . "'";
            $filter .= " and BirthDay  <= '" . $data['BirthDayRange'][1] . "'";
        }

        if (isset($data['BirthDayRange'][0])) {
            $BirthDayBeg = "'" . $data['BirthDayRange'][0] . "'";
            $filter .= " and BirthDay  >= '" . $data['BirthDayRange'][0] . "'";
        } // PeriodRange             } 

        if (isset($data['BirthDay'])) {
            $BirthDay = "'" . $data['BirthDay'] . "'";
            $filter .= " and BirthDay  = '" . $data['BirthDay'] ."'";
        }
   

        //log_message('debug', '$filter=' . $filter);


        //Фильтр грида
        $json = isset($data['Filter']) ? iconv('windows-1251', 'utf-8', trim($data['Filter'], '"')) : false;
        //echo $json.'<br/>';
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;
        //var_dump($filter_mode); 
        //echo '<pre>' . print_r($filter_mode, 1) . '</pre>';
        // Взависимости от типа реестра возвращаем разные наборы данных
        $params = array(
            //'Registry_id' => $data['Registry_id'],
            //'Lpu_id'=>$data['session']['lpu_id'],
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim(iconv('utf-8', 'windows-1251', $filter_mode['value'])) . "%"
        );

        //echo '<pre>' . print_r($params, 1) . '</pre>';
        //var_dump($params);

        $distinct = "distinct";


        //$filter="(1=1)";

        $join = "";
        $fields = "";

        $field = $filter_mode['cell'];
        /*
        if ($field == 'StatusOnkoProfile') {
            $field = "case when StatusOnkoProfile = ' ' then 'Анкета не заполнена' else StatusOnkoProfile end	StatusOnkoProfile";
        }
        */
        //log_message('debug', '$field=' . $field);
        //log_message('debug', '$params=' . $params);


        if ($field == 'ProfileResult') {
            $query = " Select 
                        -- select
                        distinct {$this->getNameColumn($field)}
                        -- end select    
					FROM 
					-- from
					 (SElect q.OnkoQuestions_Nick, p.* from onko.v_ProfileJurnal p with(nolock)
                                            join onko.PersonOnkoQuestions t with(nolock) on t.PersonOnkoProfile_id = p.PersonOnkoProfile_id
                                            join onko.S_OnkoQuestions Q with(nolock) on q.OnkoQuestions_id = t.OnkoQuestions_id 
							)t
							-- end from 
                                                where
                                                    --  where
                                                    {$filter}
                                                    --  end where        
						order by 
						-- order by
						   {$this->getNameColumn($field)}
                                                      
						-- end order by   ";
        } else {
            $query = " Select 
					-- select
					distinct " . $field . "
					-- end select    
			FROM 
			-- from 
                        onko.v_ProfileJurnal p with(nolock)
				
                                    {$join}
			-- end from    
			where
                        --  where
                        {$filter}
                        --  end where
		order by 
		-- order by
		   {$field}
		-- end order by   "; // .$params.Lpu_id;
        }

       


        $Like = ($filter_mode['specific'] === false) ? "" : " and " . $this->getNameColumn($field) . " like  :Value";
        //log_message('debug', '$Like=' . $Like);

        //function _getLimitSQLPH($query, $distinct, $field, $start = 0, $limit = 1000, $like, $order_row = '')
        
                
        $result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $this->getNameColumn($field), $data['start'], $data['limit'], $Like, $this->getNameColumn($field)), $params);


        $result_count = $this->db->query($this->_getCountSQLPH($query, $this->getNameColumn($field), $distinct, $Like, $this->getNameColumn($field)), $params);
        

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (is_object($result)) {
            $response = array();
            $response['data'] = $result->result('array');
            $response['totalCount'] = $count;
            //log_message('debug', '$count = ' . $count);
            return $response;
        } else {
            return false;
        }
    }  // end GetOnkoCtrlProfileJurnaleFilter
    
     /**
     * Построение фильтра грида для журнала анкетирования по онкоконтролю
     *  (прежняя версия) 
     */
    
    function GetOnkoCtrlProfileJurnalFilter_old($data) {

        $params = "";
        $queryParams = array();
        $filters = array();
        $Lpu_id = '';
        $Surname = "NULL";
        $Firname = "NULL";
        $SecName = "NULL";
        $BirthDay = "NULL";
        $BirthDayBeg = "NULL";
        $BirthDayEnd = "NULL";
        $Empty = "0";
        //log_message('debug', 'start0=' . $data['start']);
        if (!isset($data['start'])) {
            $data['start'] = 0;
        }
         //log_message('debug', 'start1=' . $data['start']);
        //$data['start'] = 0;
        $data['limit'] = 100;
        $function = 'onko.fn_GetOnkoCtrlProfileJurnal';

        $filter = '(1=1)';

        if ($data['OnkoType_id'] == 2) {
            $function = 'onko.fn_GetOnkoCtrlProfileJurnalFull';
        }

        if (isset($data['Lpu_id'])) {
            $Lpu_id = $data['Lpu_id'];
        }
        if (isset($data['SurName'])) {
            $Surname = "'" . $data['SurName'] . "%'";
        };

        if (isset($data['FirName'])) {
            $Firname = "'" . $data['FirName'] . "%'";
        };

        if (isset($data['SecName'])) {
            $SecName = "'" . $data['SecName'] . "%'";
        };

        if (isset($data['BirthDayRange'][1])) {
            $BirthDayEnd = "'" . $data['BirthDayRange'][1] . "'";
        }

        if (isset($data['BirthDayRange'][0])) {
            $BirthDayBeg = "'" . $data['BirthDayRange'][0] . "'";
        } // PeriodRange             } 

        if (isset($data['BirthDay'])) {
            $BirthDay = "'" . $data['BirthDay'] . "'";
        }
   

        //log_message('debug', '$filter=' . $filter);


        //Фильтр грида
        $json = isset($data['Filter']) ? iconv('windows-1251', 'utf-8', trim($data['Filter'], '"')) : false;
        //echo $json.'<br/>';
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;
        //var_dump($filter_mode); 
        //echo '<pre>' . print_r($filter_mode, 1) . '</pre>';
        // Взависимости от типа реестра возвращаем разные наборы данных
        $params = array(
            //'Registry_id' => $data['Registry_id'],
            //'Lpu_id'=>$data['session']['lpu_id'],
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim(iconv('utf-8', 'windows-1251', $filter_mode['value'])) . "%"
        );

        //echo '<pre>' . print_r($params, 1) . '</pre>';
        //var_dump($params);

        $distinct = "distinct";


        //$filter="(1=1)";

        $join = "";
        $fields = "";

        $field = $filter_mode['cell'];
        /*
        if ($field == 'StatusOnkoProfile') {
            $field = "case when StatusOnkoProfile = ' ' then 'Анкета не заполнена' else StatusOnkoProfile end	StatusOnkoProfile";
        }
        */
        //log_message('debug', '$field=' . $field);
        //log_message('debug', '$params=' . $params);


        if ($field == 'ProfileResult') {
            $query = " Select 
                        -- select
                        distinct {$this->getNameColumn($field)}
                        -- end select    
					FROM 
					-- from
					 (SElect q.OnkoQuestions_Nick, p.* from " . $function . " ({$Lpu_id}, {$Surname}, {$Firname}, {$SecName}, {$BirthDay}, 
                                                                {$BirthDayBeg}, {$BirthDayEnd}, {$Empty}) p
                                            join onko.PersonOnkoQuestions t with(nolock) on t.PersonOnkoProfile_id = p.PersonOnkoProfile_id
                                            join onko.S_OnkoQuestions Q with(nolock) on q.OnkoQuestions_id = t.OnkoQuestions_id 
							)t
							-- end from 
                                                where
                                                    --  where
                                                    {$filter}
                                                    --  end where        
						order by 
						-- order by
						   {$this->getNameColumn($field)}
                                                      
						-- end order by   ";
        } else {
            $query = " Select 
					-- select
					distinct " . $field . "
					-- end select    
			FROM 
			-- from
				" . $function . "  ({$Lpu_id}, {$Surname}, {$Firname}, {$SecName}, {$BirthDay}, {$BirthDayBeg},
                                        {$BirthDayEnd}, {$Empty})  
                                    {$join}
			-- end from    
			where
                        --  where
                        {$filter}
                        --  end where
		order by 
		-- order by
		   {$field}
		-- end order by   "; // .$params.Lpu_id;
        }

        //log_message('debug', '$query=' . $query);

        // log_message('debug', '$orderBy=' .$this->getNameColumn($field));


        //log_message('debug', 'start=' . $data['start']);

        //log_message('debug', 'limit=' . $data['limit']);
        



        $Like = ($filter_mode['specific'] === false) ? "" : " and " . $this->getNameColumn($field) . " like  :Value";
        //log_message('debug', '$Like=' . $Like);

        //function _getLimitSQLPH($query, $distinct, $field, $start = 0, $limit = 1000, $like, $order_row = '')
        
                
        $result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $this->getNameColumn($field), $data['start'], $data['limit'], $Like, $this->getNameColumn($field)), $params);


        $result_count = $this->db->query($this->_getCountSQLPH($query, $this->getNameColumn($field), $distinct, $Like, $this->getNameColumn($field)), $params);
        

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (is_object($result)) {
            $response = array();
            $response['data'] = $result->result('array');
            $response['totalCount'] = $count;
            //log_message('debug', '$count = ' . $count);
            return $response;
        } else {
            return false;
        }
    }  // end GetOnkoCtrlProfileJurnaleFilter


    /**
     * Получение списка Вакцин из справочника вакцин
     * Используется: окно просмотра и редактирования справочника вакцин
     */
    function getVaccineGridDetailFilter($data) {

        $params = "";
		if (isset($data['start'])) {
            $data['start'] = 0;
        }
        $data['limit'] = 100;


        //Фильтр грида
        $json = isset($data['Filter']) ? trim($data['Filter'], '"') : false;
        //echo $json.'<br/>';
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;
        //var_dump($filter_mode); 
        //echo '<pre>' . print_r($filter_mode, 1) . '</pre>';
        // Взависимости от типа реестра возвращаем разные наборы данных
        $params = array(
            //'Registry_id' => $data['Registry_id'],
            //'Lpu_id'=>$data['session']['lpu_id'],
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim($filter_mode['value']) . "%"
        );


        $distinct = "distinct";


        $filter = "(1=1)";

        $join = "";
        $fields = "";

        $field = $filter_mode['cell'];

        if ($field == 'ProfileResult') {
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
        } else {
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



        $Like = ($filter_mode['specific'] === false) ? "" : " and " . $this->getNameColumn($field) . " like  :Value";

        $result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $this->getNameColumn($field), $data['start'], $data['limit'], $Like, $this->getNameColumn($field)), $params);


        $result_count = $this->db->query($this->_getCountSQLPH($query, $this->getNameColumn($field), $distinct, $this->getNameColumn($field)), $params);

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (is_object($result)) {
            $response = array();
            $response['data'] = $result->result('array');
            $response['totalCount'] = $count;
            //log_message('debug', '$count = ' . $count);
            return $response;
        } else {
            return false;
        }
    }

    /**
     * Генерирует по переданным данным набор фильтров и джойнов
     */
    /*
      function genSearchFilters($type, $data, &$filters, &$queryParams, &$join) {
      // 1. Основной фильтр
      If (ArrayVal($data, 'Lpu_id') != '') {
      $filters[] = $this->getFieldName($type, 'Lpu_id') . " = :lpuId";
      $queryParams['lpuId'] = $data['Lpu_id'];

      }

      }
     */
}

?>