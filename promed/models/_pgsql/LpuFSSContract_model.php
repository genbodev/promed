<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LpuFSSContract_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 */

class LpuFSSContract_model extends SwPgModel
{
    /**
     * Удаление
     * @param $data
     * @return bool
     */
	public function delete($data)
    {

		$query = "
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_LpuFSSContract_del
			(
				LpuFSSContract_id := :id
            );
		";

		$result = $this->db->query($query, $data);

		if (!is_object($result)) {
            return false;
		}

        return $result->result('array');
	}

    /**
     * Возвращает список
     * @param $data
     * @return array|false
     */
	public function loadList($data)
    {
		
		$filter = '';

		if (!empty($data['LpuFSSContractType_id'])) {
			$filter .= ' and lc.LpuFSSContractType_id = :LpuFSSContractType_id ';
		}

		if (!empty($data['LpuFSSContract_Num'])) {
			$filter .= ' and lc.LpuFSSContract_Num ilike :LpuFSSContract_Num ';
			$data['LpuFSSContract_Num'] .= '%';
		}

		if ($data['isClose'] == 1) {
			$filter .= ' and coalesce(lc.LpuFSSContract_endDate, (select curDate from cte)) >= (select curDate from cte) ';
		} elseif ($data['isClose'] == 2) {
			$filter .= ' and lc.LpuFSSContract_endDate < (select curDate from cte) ';
		}
		
		return $this->queryResult("
		    with cte as (
		        select dbo.tzGetDate() as curDate
		    )
			
			select 
				lc.LpuFSSContract_id as \"LpuFSSContract_id\",
				lc.LpuFSSContract_Num as \"LpuFSSContract_Num\",
				lct.LpuFSSContractType_Name as \"LpuFSSContractType_Name\",
				to_char(lc.LpuFSSContract_begDate, 'dd.mm.yyyy') as \"LpuFSSContract_begDate\",
				to_char(lc.LpuFSSContract_endDate, 'dd.mm.yyyy') as \"LpuFSSContract_endDate\"
			from v_LpuFSSContract lc
				inner join v_LpuFSSContractType lct on lct.LpuFSSContractType_id = lc.LpuFSSContractType_id
			where 
				lc.Lpu_id = :Lpu_id
				{$filter}
        ", $data);
	}

    /**
     * Возвращает договор
     * @param $data
     * @return array|false
     */
	public function load($data)
    {

		$query = "
			select
				A.LpuFSSContract_id as \"LpuFSSContract_id\",
				A.Lpu_id as \"Lpu_id\",
				A.LpuFSSContractType_id as \"LpuFSSContractType_id\",
				A.LpuFSSContract_Num as \"LpuFSSContract_Num\",
				to_char(A.LpuFSSContract_begDate, 'dd.mm.yyyy') as \"LpuFSSContract_begDate\",
				to_char(A.LpuFSSContract_endDate, 'dd.mm.yyyy') as \"LpuFSSContract_endDate\"
			from
				v_LpuFSSContract A
			where
				A.LpuFSSContract_id = :LpuFSSContract_id
		";
		
		return $this->queryResult($query, $data);
	}

    /**
     * Сохраняет договор
     * @param $data
     * @return array|false
     */
	public function save($data)
    {

		$procedure = empty($data['LpuFSSContract_id']) ? 'p_LpuFSSContract_ins' : 'p_LpuFSSContract_upd';
        $data['LpuFSSContract_id'] = $data['LpuFSSContract_id'] ?? null;
        
		$query = "
			select
			    LpuFSSContract_id as \"LpuFSSContract_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from {$procedure}
			(
				LpuFSSContract_id := :LpuFSSContract_id,
				Lpu_id := :Lpu_id,
				LpuFSSContractType_id := :LpuFSSContractType_id,
				LpuFSSContract_Num := :LpuFSSContract_Num,
				LpuFSSContract_begDate := :LpuFSSContract_begDate,
				LpuFSSContract_endDate := :LpuFSSContract_endDate,
				pmUser_id := :pmUser_id
			)
		";
		
		return $this->queryResult($query, $data);
	}
	
}