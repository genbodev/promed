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

class VaccinectrlfilterGrid_model extends VaccineCtrl_model
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
    public function _getLimitSQLPH($query, $distinct, $field, $start = 0, $limit = 1000, $like, $order_row = '')
    {
        $start = ($start == 0) ? (int)0 : $start;

        $exp = preg_match("/--[\s]*select([\w\W]*)--[\s]*end select/i", $query, $maches);
        $field = $maches[1];

        preg_match("/--[\s]*from([\w\W]*)--[\s]*end from/i", $query, $maches);
        $from = $maches[1];

        preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $query, $maches);

        if (isset($maches[1])) {
            $where = $maches[1] . " " . $like;
        }

        $where = empty($where) ? "" : "where " . $where;
        $order_row = (!empty($order_row) ? $order_row : $exp);
        $query = '
            WITH temptable AS (
                SELECT
                    ' . $field . ',
                    ROW_NUMBER() OVER (ORDER BY ' . $field . ') AS "row_number"
                FROM (
                    SELECT DISTINCT ' . $order_row . '
                    FROM ' . $from . '
                ' . $where . '
                ) AS t
            )
            SELECT ' . $order_row . ' AS "field"
            FROM
                temptable
            WHERE
                row_number BETWEEN ' . $start . ' AND ' . $limit;

        return $query;
    }

    /**
     * comments
     * @param $sql
     * @param $field
     * @param $distinct
     * @param $orderBy
     * @return string|string[]|null
     */
    public function _getCountSQLPH($sql, $field, $distinct, $orderBy)
    {
        $sql = preg_replace("/--[\s]*select[\w\W]*--[\s]*end select/i", " count( " . $distinct . " " . $orderBy . " ) AS cnt ", $sql);

        $exp = preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $sql, $maches);
        if (isset($maches[1])) {
            $where = $maches[1];
        }

        $sql = preg_replace("/ORDER BY[\s]*--[\s]*order by[\w\W]*--[\s]*end order by/i", "", $sql);

        $sql = preg_replace("/GROUP BY[\s]*--[\s]*group by[\w\W]*--[\s]*end group by/i", "", $sql);

        return $sql;
    }

    /** end*/


    /**
     * Построение фильтра грида для справочника "Наличие вакцин"
     */
    function GetVacPresenceFilter($data)
    {
        $data['start'] = 0;
        $data['limit'] = 100;


        //Фильтр грида
        $json = isset($data['Filter']) ? iconv('windows-1251', 'utf-8', trim($data['Filter'], '"')) : false;
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;

        // Взависимости от типа реестра возвращаем разные наборы данных
        $params = [
            'Lpu_id' => $data['session']['lpu_id'],
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim(iconv('utf-8', 'windows-1251', $filter_mode['value'])) . "%"
        ];


        $distinct = "distinct";

        $field = $filter_mode['cell'];
        $column = $this->getNameColumn($field);
        $query = "
            select 
                -- select
                {$column}
                -- end select    
			FROM 
			-- from
				vac.v_VacPresence
			-- end from    
			where 
			-- where
				lpu_id = {$data['session']['lpu_id']}
			-- end where    
		order by 
		-- order by
		   {$column}
		-- end order by   ";// .$params.Lpu_id;

        log_message('debug', '$query=' . $query);
        log_message('debug', 'start=' . $data['start']);
        log_message('debug', 'limit=' . $data['limit']);

        $Like = ($filter_mode['specific'] === false) ? "" : " and " . $column . " ilike  :Value";

        $result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $column, $data['start'], $data['limit'], $Like, $column), $params);


        $result_count = $this->db->query($this->_getCountSQLPH($query, $column, $distinct, $column), $params);

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }

        if (!is_object($result))
            return false;

        $response = [];
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;
        log_message('debug', '$count = ' . $count);
        return $response;
    } // end loadRegistryDataFilter

    /**
     * Построение фильтра грида для АРМ каб. вакцинации
     * @param $data
     * @return array|bool
     */
    public function GetVacAssigned4CabVacFilter($data)
    {

        $data['start'] = 0;
        $data['limit'] = 100;

        $filters = [];
        $join = '';
        $queryParams = [];

        $this->genSearchFilters(TYPE_JOURNAL::VAC_4CabVac, $data, $filters, $queryParams, $join);
        $filter = "" . Implode(' and ', $filters);
        log_message('debug', 'GetVacAssigned4CabVacFilter: $filter =' . $filter);

        //Фильтр грида
        $json = isset($data['Filter']) ? iconv('windows-1251', 'utf-8', trim($data['Filter'], '"')) : false;
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;
        $distinct = "distinct";


        $field = $filter_mode['cell'];
        $column = $this->getNameColumn($field);

        if ($field == 'Infection') {
            $query = "
                select 
                    -- select
                    distinct {$column} as \"{$column}\"
                    -- end select    
                FROM 
					-- from
                        (
                            select
                                tp.VaccineType_Name,
                                ac.*
                            from
                                vac.v_JournalVac ac
                                join vac.Inoculation i on ac.vacJournalAccount_id = i.vacJournalAccount_id
                                join vac.S_VaccineType tp on i.VaccineType_id = tp.VaccineType_id
                             where " . $filter . " 
                        ) t
                    -- end from     
                    order by 
                    -- order by
                       {$column}
                    -- end order by
            ";
        } else {
            $query = "
                Select 
                    -- select
                    distinct {$column} as \"{$column}\"
                    -- end select    
                FROM 
					-- from
						vac.v_JournalVac
					-- end from    
					where 
					-- where
                        {$filter}
					-- end where    
			order by 
			-- order by
			   {$column}
			-- end order by
		    ";
        }
        log_message('debug', '$query=' . $query);
        log_message('debug', '$field=' . $field);
        log_message('debug', 'start=' . $data['start']);
        log_message('debug', 'limit=' . $data['limit']);


        $Like = ($filter_mode['specific'] === false) ? "" : " and " . $this->getNameColumn($field) . " ilike  :Value";

        $result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $column, $data['start'], $data['limit'], $Like, $column), $queryParams);


        $result_count = $this->db->query($this->_getCountSQLPH($query, $column, $distinct, $column), $queryParams);
        $count = 0;

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        }

        if (!is_object($result))
            return false;

        $response = [];
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;
        log_message('debug', '$count = ' . $count);
        return $response;
    }  //  end GetVacAssigned4CabVacFilter

    /**
     * Получение списка Вакцин из справочника вакцин
     * Используется: окно просмотра и редактирования справочника вакцин
     * @param $data
     * @return array|bool
     */
    public function getVaccineGridDetailFilter($data)
    {
        $data['start'] = 0;
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
        if ($field == 'Vaccine_NameInfection') {
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
				distinct {$column}  as \"{$column}\"
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


        $Like = ($filter_mode['specific'] === false) ? "" : " and " . $column . " ilike  :Value";

        $result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $column, $data['start'], $data['limit'], $Like, $column), $params);


        $result_count = $this->db->query($this->_getCountSQLPH($query, $column, $distinct, $column), $params);

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (!is_object($result))
            return false;

        $response = [];
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;
        log_message('debug', '$count = ' . $count);
        return $response;
    }

}