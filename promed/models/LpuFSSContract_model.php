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

class LpuFSSContract_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление
	 */
	function delete($data) {

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_LpuFSSContract_del
				@LpuFSSContract_id = :id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список
	 */
	function loadList($data) {
		
		$filter = '';

		if (!empty($data['LpuFSSContractType_id'])) {
			$filter .= ' and lc.LpuFSSContractType_id = :LpuFSSContractType_id ';
		}

		if (!empty($data['LpuFSSContract_Num'])) {
			$filter .= ' and lc.LpuFSSContract_Num like :LpuFSSContract_Num ';
			$data['LpuFSSContract_Num'] .= '%';
		}

		if ($data['isClose'] == 1) {
			$filter .= ' and isnull(lc.LpuFSSContract_endDate, @curDate) >= @curDate ';
		} elseif ($data['isClose'] == 2) {
			$filter .= ' and lc.LpuFSSContract_endDate < @curDate ';
		}
		
		return $this->queryResult("
			declare @curDate date = dbo.tzGetDate();
			
			select 
				lc.LpuFSSContract_id,
				lc.LpuFSSContract_Num,
				lct.LpuFSSContractType_Name,
				convert(varchar(10), lc.LpuFSSContract_begDate, 104) as LpuFSSContract_begDate,
				convert(varchar(10), lc.LpuFSSContract_endDate, 104) as LpuFSSContract_endDate
			from v_LpuFSSContract lc (nolock)
				inner join v_LpuFSSContractType lct (nolock) on lct.LpuFSSContractType_id = lc.LpuFSSContractType_id
			where 
				lc.Lpu_id = :Lpu_id
				{$filter}
        ", $data);
	}

	/**
	 * Возвращает договор
	 */
	function load($data) {

		$query = "
			select
				A.LpuFSSContract_id
				,A.Lpu_id
				,A.LpuFSSContractType_id
				,A.LpuFSSContract_Num
				,convert(varchar(10), A.LpuFSSContract_begDate, 104) as LpuFSSContract_begDate
				,convert(varchar(10), A.LpuFSSContract_endDate, 104) as LpuFSSContract_endDate
			from
				v_LpuFSSContract A with(nolock)
			where
				A.LpuFSSContract_id = :LpuFSSContract_id
		";
		
		return $this->queryResult($query, $data);
	}

	/**
	 * Сохраняет договор
	 */
	function save($data) {

		$procedure = empty($data['LpuFSSContract_id']) ? 'p_LpuFSSContract_ins' : 'p_LpuFSSContract_upd';

		$query = "
			declare
				@LpuFSSContract_id bigint = :LpuFSSContract_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@LpuFSSContract_id = @LpuFSSContract_id output,
				@Lpu_id = :Lpu_id,
				@LpuFSSContractType_id = :LpuFSSContractType_id,
				@LpuFSSContract_Num = :LpuFSSContract_Num,
				@LpuFSSContract_begDate = :LpuFSSContract_begDate,
				@LpuFSSContract_endDate = :LpuFSSContract_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @LpuFSSContract_id as LpuFSSContract_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		
		return $this->queryResult($query, $data);
	}
	
}