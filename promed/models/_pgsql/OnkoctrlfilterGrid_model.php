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


class OnkoctrlfilterGrid_model extends OnkoCtrl_model
{

    /**
     * 2 метода из хелпера SQL Sql_helper.php - переписаны под себя - для пагинации данных в окне фильтра
     * @param $query
     * @param $distinct
     * @param $field
     * @param int $start
     * @param int $limit
     * @param $like
     * @param string $order_row
     * @return string
     */
    public function _getLimitSQLPH($query, $distinct, $field, $start = 0, $limit = 100, $like, $order_row = '')
    {
        $start = ($start == 0) ? (int)0 : $start;
        if ($start == 0)
            $end = $start + $limit;
        else
            $end = $start + $limit - 1;
        $exp = preg_match("/--[\s]*select([\w\W]*)--[\s]*end select/i", $query, $maches);
        $select = $maches[1];

        $exp = preg_match("/--[\s]*from([\w\W]*)--[\s]*end from/i", $query, $maches);
        $from = $maches[1];

        $exp = preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $query, $maches);
        $where = "";
        if (isset($maches[1])) {
            $where = "WHERE " . $maches[1] . " " . $like;
        }

        $query = "WITH temptable AS
				(
					select
					    {$field},   
                        ROW_NUMBER() OVER (ORDER BY {$field}) AS RowNumber
                    from (
                        SELECT
                            distinct {$order_row}, {$field}
                        FROM {$from}  
					    {$where} 
                    ) t
				) 
				SELECT
				    {$distinct} {$field} as \"{$field}\"
                FROM
                    temptable
				WHERE RowNumber BETWEEN " . $start . " AND " . $end . " " . (!empty($order_row) ? "" : $field) . ";";

        return $query;
    }

    /**
     * comments
     * @param $sql
     * @param $field
     * @param $distinct
     * @param $like
     * @param $orderBy
     * @return string|string[]|null
     */
    public function _getCountSQLPH($sql, $field, $distinct, $like, $orderBy)
    {
        $where = '';
        $sql = preg_replace("/--[\s]*select[\w\W]*--[\s]*end select/i", " count( " . $distinct . " " . $orderBy . " ) AS cnt ", $sql);

        $exp = preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $sql, $maches);
        if (isset($maches[1])) {
            $where = $maches[1] . ' ' . $like;
        }

        $sql = preg_replace("/--[\s]*where([\w\W]*)--[\s]*end where/i", $where, $sql);

        $sql = preg_replace("/ORDER BY[\s]*--[\s]*order by[\w\W]*--[\s]*end order by/i", "", $sql);

        $sql = preg_replace("/GROUP BY[\s]*--[\s]*group by[\w\W]*--[\s]*end group by/i", "", $sql);


        return $sql;
    }

    /** end */

    /**
     * Построение фильтра грида для журнала анкетирования по онкоконтролю
     * @param $data
     * @return array|bool
     */
    public function GetOnkoCtrlProfileJurnalFilter($data)
    {

        if (!isset($data['start'])) {
            $data['start'] = 0;
        }
        $data['limit'] = 100;

        $filter = '(1 = 1)';

        if (isset($data['Lpu_id'])) {
            $filter .= " and Lpu_id = " . $data['Lpu_id'];
        }

        if (isset($data['SurName'])) {
            $filter .= " and SurName ilike  " . $data['SurName'] . "%";
        }

        if (isset($data['FirName'])) {
            $filter .= " and FirName ilike  " . $data['FirName'] . "%";
        }

        if (isset($data['SecName'])) {
            $filter .= " and SecName ilike  " . $data['SecName'] . "%";
        }

        if (isset($data['BirthDayRange'][1])) {
            $filter .= " and BirthDay  <= '" . $data['BirthDayRange'][1] . "'";
        }

        if (isset($data['BirthDayRange'][0])) {
            $filter .= " and BirthDay  >= '" . $data['BirthDayRange'][0] . "'";
        }

        if (isset($data['BirthDay'])) {
            $filter .= " and BirthDay  = '" . $data['BirthDay'] . "'";
        }


        //log_message('debug', '$filter=' . $filter);


        //Фильтр грида
        $json = isset($data['Filter']) ? iconv('windows-1251', 'utf-8', trim($data['Filter'], '"')) : false;

        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;
        // Взависимости от типа реестра возвращаем разные наборы данных
        $params = [
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim(iconv('utf-8', 'windows-1251', $filter_mode['value'])) . "%"
        ];

        $distinct = "distinct";
        $join = "";

        $field = $filter_mode['cell'];
        $column = $this->getNameColumn($field);


        if ($field == 'ProfileResult') {
            $query = "
                select 
                -- select
                    distinct {$column} as \"{$column}\"
                -- end select    
                FROM 
                -- from
                (
                    select
                        q.OnkoQuestions_Nick,
                        p.*
                    from
                        onko.v_ProfileJurnal p
                        join onko.PersonOnkoQuestions t on t.PersonOnkoProfile_id = p.PersonOnkoProfile_id
                        join onko.S_OnkoQuestions Q on q.OnkoQuestions_id = t.OnkoQuestions_id 
                ) t
                -- end from 
                where
                --  where
                    {$filter}
                --  end where        
                order by 
                -- order by
                   {$column}                     
                -- end order by
			";
        } else {
            $query = "
                select 
					-- select
					distinct {$column} as \"{$column}\"
					-- end select    
			    from 
			    -- from
                    onko.v_ProfileJurnal p
				    {$join}
			    -- end from    
                where
                    --  where
                    {$filter}
                    --  end where
                order by 
                -- order by
                {$column}
                -- end order by
		    "; // .$params.Lpu_id;
        }


        $Like = ($filter_mode['specific'] === false) ? "" : " and {$column} like  :Value";


        $result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $column, $data['start'], $data['limit'], $Like, $column), $params);


        $result_count = $this->db->query($this->_getCountSQLPH($query, $column, $distinct, $Like, $column), $params);


        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (!is_object($result)) {
            return false;
        }

        $response = [];
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;

        return $response;
    }  // end GetOnkoCtrlProfileJurnaleFilter

    /**
     * Построение фильтра грида для журнала анкетирования по онкоконтролю
     *  (прежняя версия)
     *
     * @param $data
     * @return array|bool
     */
    public function GetOnkoCtrlProfileJurnalFilter_old($data)
    {

        $params = [];
        $filters = [];
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

        $filter = '(1 = 1)';

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





        //Фильтр грида
        $json = isset($data['Filter']) ? iconv('windows-1251', 'utf-8', trim($data['Filter'], '"')) : false;
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;
        // Взависимости от типа реестра возвращаем разные наборы данных
        $params = [
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim(iconv('utf-8', 'windows-1251', $filter_mode['value'])) . "%"
        ];

        $distinct = "distinct";




        $field = $filter_mode['cell'];
        $column = $this->getNameColumn($field);

        if ($field == 'ProfileResult') {
            $query = "
                select 
                -- select
                    distinct {$column} as \"{$column}\"
                -- end select    
                FROM 
                -- from
                    (
                        select
                            q.OnkoQuestions_Nick,
                            p.*
                        from {$function}
                        (
                            {$Lpu_id},
                            {$Surname},
                            {$Firname},
                            {$SecName},
                            {$BirthDay}, 
                            {$BirthDayBeg},
                            {$BirthDayEnd},
                            {$Empty}
                        ) p
                        join onko.PersonOnkoQuestions t on t.PersonOnkoProfile_id = p.PersonOnkoProfile_id
                        join onko.S_OnkoQuestions Q on q.OnkoQuestions_id = t.OnkoQuestions_id 
                    ) t
                -- end from 
                where
                --  where
                    {$filter}
                --  end where        
                order by 
                -- order by
                    {$column}
                -- end order by
            ";
        } else {
            $query = "
                select 
                    -- select
                    distinct {$column} as \"$column\"
                    -- end select    
                FROM 
                -- from
                {$function}  
                (
                    {$Lpu_id},
                    {$Surname},
                    {$Firname},
                    {$SecName},
                    {$BirthDay},
                    {$BirthDayBeg},
                    {$BirthDayEnd},
                    {$Empty}
                )
                -- end from    
                where
                --  where
                    {$filter}
                --  end where
                order by 
                -- order by
                   {$column}
                -- end order by
		    "; // .$params.Lpu_id;
        }

        $Like = ($filter_mode['specific'] === false) ? "" : " and {$column} ilike  :Value";

        $result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $column, $data['start'], $data['limit'], $Like, $column), $params);


        $result_count = $this->db->query($this->_getCountSQLPH($query, $column, $distinct, $Like, $column), $params);


        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (!is_object($result)) {
            return false;

        }

        $response = [];
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;
        //log_message('debug', '$count = ' . $count);
        return $response;
    }  // end GetOnkoCtrlProfileJurnaleFilter


    /**
     * Получение списка Вакцин из справочника вакцин
     * Используется: окно просмотра и редактирования справочника вакцин
     *
     * @param $data
     * @return array|bool
     */
    public function getVaccineGridDetailFilter($data)
    {
        if (isset($data['start'])) {
            $data['start'] = 0;
        }
        $data['limit'] = 100;


        //Фильтр грида
        $json = isset($data['Filter']) ? trim($data['Filter'], '"') : false;
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;

        // Взависимости от типа реестра возвращаем разные наборы данных
        $params = [
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim($filter_mode['value']) . "%"
        ];


        $distinct = "distinct";


        $field = $filter_mode['cell'];
        $column = $this->getNameColumn($field);
        if ($field == 'ProfileResult') {
            $query = "
                select 
                    -- select
                        distinct {$column} as \"{$column}\"
                    -- end select    
                from 
					-- from
					(
					    select
					        tp.VaccineType_Name,
					        vac.*
                        from
                            vac.v_Vaccine vac
                            join vac.S_VaccineRelType rel on vac.Vaccine_id = rel.Vaccine_id
                            join vac.S_VaccineType tp on rel.VaccineType_id = tp.VaccineType_id
                    ) t
							-- end from     
                order by 
                -- order by
                   {$column}
                -- end order by
           ";
        } else {
            $query = " 
			    select 
                    -- select
                    distinct {$column} as \"{$column}\" 
                    -- end select    
                from 
                    -- from
                    vac.v_Vaccine vac
					-- end from    
                order by 
			        -- order by
			        {$column}
			        -- end order by  
			";
        }


        $Like = ($filter_mode['specific'] === false) ? "" : " and {$column} ilike  :Value";

        $result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $column, $data['start'], $data['limit'], $Like, $column), $params);


        $result_count = $this->db->query($this->_getCountSQLPH($query, $column, $distinct, $column, ""), $params);

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (!is_object($result)) {
            return false;
        }

        $response = [];
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;

        return $response;
    }
}