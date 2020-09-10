<?php

class Msk_BedDowntimeLog_model extends SwPgModel
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'BedDowntimeLog';
	}

	/**
	 *    Выборка списка направлений для журнала направлений
	 */
	function loadBedDowntimeJournal($data)
	{
		$params = [
			'begDate' => $data['begDate']
			, 'endDate' => $data['endDate']
			, 'LpuSection_id' => $data['LpuSection_id']
		];

		$filter = '';
		if (!empty($data['BedProfile_id'])) {
			$params['BedProfile_id'] = $data['BedProfile_id'];
			$filter .= 'AND bdl.LpuSectionBedProfile_id = :BedProfile_id';
		}

		$query = "
			SELECT
			 -- select
			 bdl.BedDowntimeLog_id as \"BedDowntimeLog_id\",
			 bdl.LpuSectionBedProfile_id as \"BedProfile_id\",
			 bdl.BedDowntimeLog_Count as \"BedDowntimeLog_Count\",
			 to_char(bdl.BedDowntimeLog_endDate, 'dd.mm.YYYY')  as \"endDate\",
			 to_char(bdl.BedDowntimeLog_begDate, 'dd.mm.YYYY')  as \"begDate\",
			 EXTRACT(DAY FROM bdl.BedDowntimeLog_endDate - bdl.BedDowntimeLog_begDate) + 1 as \"durationOfPeriod\",
			 bdl.BedDowntimeLog_RepairCount + bdl.BedDowntimeLog_ReasonsCount as \"plainBeds\",
		     bdl.BedDowntimeLog_RepairCount as \"BedDowntimeLog_RepairCount\",
		     bdl.BedDowntimeLog_ReasonsCount as \"BedDowntimeLog_ReasonsCount\",
		     bdl.BedDowntimeLog_Reasons as \"BedDowntimeLog_Reasons\"
			 -- end select
			 FROM 
			 -- from
			 dbo.BedDowntimeLog bdl
			 -- end from
			WHERE 
			-- where
			(1=1) AND
			bdl.BedDowntimeLog_endDate <= :endDate AND
    		bdl.BedDowntimeLog_begDate >= :begDate AND 
    		bdl.LpuSection_id = :LpuSection_id
    		{$filter}
    		-- end where
    		order by
    			-- order by
				bdl.BedDowntimeLog_id desc 
				-- end order by
			";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}


	public function getDataForXLS($data)
	{
		$params = [
			'begDate' => $data['begDate']
			, 'endDate' => $data['endDate']
			, 'LpuSection_id' => $data['LpuSection_id']
		];

		$sort = '';
		if (!empty($data['sortField']) && !empty($data['sortDirection'])) {
			$sort = '"' . $data['sortField'] . '"' . "{$data['sortDirection']}";
		}

		$filter = '';
		if (!empty($data['Export_BedProfile_id'])) {
			$params['BedProfile_id'] = $data['Export_BedProfile_id'];
			$filter .= 'AND bdl.LpuSectionBedProfile_id = :BedProfile_id';
		}

		$query = "
			SELECT
			 -- select
			 bdl.BedDowntimeLog_id as \"BedDowntimeLog_id\",
			 bdl.LpuSectionBedProfile_id as \"BedProfile_id\",
			 to_char(bdl.BedDowntimeLog_endDate, 'dd.mm.YYYY')  as \"endDate\",
			 to_char(bdl.BedDowntimeLog_begDate, 'dd.mm.YYYY')  as \"begDate\",
			 EXTRACT(DAY FROM bdl.BedDowntimeLog_endDate - bdl.BedDowntimeLog_begDate) + 1 as \"durationOfPeriod\",
			 bdl.BedDowntimeLog_RepairCount + bdl.BedDowntimeLog_ReasonsCount as \"plainBeds\",
		     bdl.BedDowntimeLog_RepairCount as \"BedDowntimeLog_RepairCount\",
		     bdl.BedDowntimeLog_ReasonsCount as \"BedDowntimeLog_ReasonsCount\",
		     bdl.BedDowntimeLog_Reasons as \"BedDowntimeLog_Reasons\",
		     bdl.BedDowntimeLog_Count as \"BedDowntimeLog_Count\",
		     ls.LpuSection_FullName as \"LpuSection\",
		     *
			 -- end select
			 FROM 
			 -- from
			 dbo.BedDowntimeLog bdl
			 LEFT JOIN v_LpuSection as ls ON ls.LpuSection_id = bdl.LpuSection_id
			 -- end from
			WHERE 
			-- where
			(1=1) AND
			bdl.BedDowntimeLog_endDate <= :endDate AND
    		bdl.BedDowntimeLog_begDate >= :begDate AND 
    		bdl.LpuSection_id = :LpuSection_id 
    		{$filter}
    		-- end where
    		ORDER BY
    			-- order by
				{$sort}
				-- end order by
			";

		return $this->queryResult($query, $params);
	}

	public function getSumEnvPS($data)
	{
		$params = [
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionBedProfile_id' => $data['LpuSectionBedProfile_id'],
			'begDate' => $data['begDate'],
			'endDate' => $data['endDate'],
		];

		$query = "
		SELECT
			 -- select	
		COALESCE(SUM(EXTRACT(DAY FROM '{$params['begDate']}' - EvnDirection_setDT)), 0) as \"sumEnvPS\"
			 -- end select
		FROM
		 	-- from
			dbo.EvnPS ERP
			LEFT JOIN dbo.v_LpuSection LS on LS.LpuSection_id = ERP.LpuSection_id
			-- end from 
		WHERE 
			-- where
			(1=1) 
			AND ERP.LpuSection_id = :LpuSection_id
			AND LS.LpuSectionBedProfile_id = :LpuSectionBedProfile_id
			AND ERP.EvnDirection_setDT >= :begDate
			AND ERP.EvnDirection_setDT <= :endDate
    		-- end where
		";

		return $this->queryResult($query, $params);
	}

	public function getBedDowntimeLog_Count($data)
	{
		$params = [
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionBedProfile_id' => $data['LpuSectionBedProfile_id']
		];

		$query = "
		SELECT 
		-- select
		COALESCE(SUM(LpuSectionWard_BedCount), 0) as bedCount
		-- end select
		FROM 
		-- from
		dbo.v_LpuSectionWard LSW
		-- end from
		LEFT JOIN dbo.LpuSectionBedProfile LSBP ON LSBP.LpuSectionBedProfile_Code = CAST(LSW.LpuWardType_id AS CHAR(25))
		WHERE 
		-- where
		LSW.LpuSection_id = :LpuSection_id
		AND LSBP.LpuSectionBedProfile_id = :LpuSectionBedProfile_id
		-- end where
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * @param $data
	 */
	public function saveBedDowntimeLog($data)
	{
		$action = $data['action'];

		$params = [
			'BedDowntimeLog_id' => $data['BedDowntimeLog_id'],
			'pmUser_id' => $data['pmuser_id'],
			'pmUser_updID' => $data['pmuser_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionBedProfile_id' => $data['LpuSectionBedProfile_id'],
			'BedDowntimeLog_Count' => $data['BedDowntimeLog_Count'],
			'BedDowntimeLog_begDate' => $data['begDate'],
			'BedDowntimeLog_endDate' => $data['endDate'],
			'BedDowntimeLog_RepairCount' => $data['BedDowntimeLog_RepairCount'],
			'BedDowntimeLog_ReasonsCount' => $data['BedDowntimeLog_ReasonsCount'],
			'BedDowntimeLog_Reasons' => $data['BedDowntimeLog_Reasons'],
		];

		$procedure = 'p_BedDowntimeLog_ins';
		if ($action === 'edit') {
			$procedure = 'p_BedDowntimeLog_upd';
		}

		$query = "  
  			SELECT
				BedDowntimeLog_id as \"BedDowntimeLog_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			FROM {$procedure} (
				beddowntimelog_id := :BedDowntimeLog_id,
				lpusection_id := :LpuSection_id,
				lpusectionbedprofile_id := :LpuSectionBedProfile_id,
				beddowntimelog_count := :BedDowntimeLog_Count,
				beddowntimelog_begdate := :BedDowntimeLog_begDate,
				beddowntimelog_enddate := :BedDowntimeLog_endDate,
				beddowntimelog_repaircount := :BedDowntimeLog_RepairCount,
				beddowntimelog_reasonscount := :BedDowntimeLog_ReasonsCount,
				beddowntimelog_reasons := :BedDowntimeLog_Reasons,
				pmuser_id := :pmUser_id
			) 
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	public function deleteBedDowntimeRecord($data)
	{
		$params = [
			'BedDowntimeLog_id' => $data['BedDowntimeLog_id'],
			'IsRemove' => 2
		];

		$query = "  
  			SELECT
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			FROM p_BedDowntimeLog_del (
				beddowntimelog_id := :BedDowntimeLog_id,
				isremove := :IsRemove
			) 
		";

		$res = $this->db->query($query, $params);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array|bool|false
	 */
	public function loadBedDowntimeJournalForm($data)
	{
		$params = [
			'BedDowntimeLog_id' => $data['BedDowntimeLog_id']
		];

		$query = "
			SELECT
			 -- select
    	     CONCAT(to_char(bdl.BedDowntimeLog_begDate, 'dd.mm.YYYY'), ' - ', to_char(bdl.BedDowntimeLog_endDate, 'dd.mm.YYYY'))  as \"BedDowntime_PeriodDate\",
			 EXTRACT(DAY FROM bdl.BedDowntimeLog_endDate - bdl.BedDowntimeLog_begDate) + 1 as \"durationOfPeriod\",
			 bdl.BedDowntimeLog_RepairCount + bdl.BedDowntimeLog_ReasonsCount as \"plainBeds\",
			 ls.LpuSection_FullName as \"LpuSection\",
			 bdl.LpuSection_id as \"LpuSection_id\",
			 bdl.BedDowntimeLog_id as \"BedDowntimeLog_id\",
			 bdl.BedDowntimeLog_Count as \"BedDowntimeLog_Count\",
			 bdl.LpuSectionBedProfile_id as \"BedProfile_id\",
			 bdl.BedDowntimeLog_RepairCount as \"BedDowntimeLog_RepairCount\",
			 bdl.BedDowntimeLog_ReasonsCount as \"BedDowntimeLog_ReasonsCount\",
			 bdl.BedDowntimeLog_Reasons as \"BedDowntimeLog_Reasons\",
			 *
			 -- end select
			 FROM 
			 -- from
			 dbo.BedDowntimeLog bdl
			 LEFT JOIN v_LpuSection ls ON ls.LpuSection_id = bdl.LpuSection_id
			 -- end from
			WHERE 
			-- where
			(1=1) AND
			bdl.BedDowntimeLog_id = :BedDowntimeLog_id
    		-- end where
    		order by
    			-- order by
				bdl.BedDowntimeLog_id desc 
				-- end order by
			";

		return $this->queryResult($query, $params);
	}

	public function getFieldsMapForXLS()
	{
		return array(
			'LpuSection' => 'Отделение',
			'BedProfile_id' => 'Профиль коек',
			'BedDowntimeLog_Count' => 'Количество коек',
			'begDate' => 'Дата начала',
			'endDate' => 'Дата окончания',
			'durationOfPeriod' => 'Длительность периода, д',
			'plainBeds' => 'Простой коек, КД',
			'BedDowntimeLog_RepairCount' => 'Из них на ремонте, КД',
			'BedDowntimeLog_ReasonsCount' => 'По другим причинам, КД',
			'BedDowntimeLog_Reasons' => 'Причины',
		);
	}
}