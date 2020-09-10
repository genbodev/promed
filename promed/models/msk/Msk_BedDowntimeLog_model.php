<?php

class Msk_BedDowntimeLog_model extends swModel
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
			 convert(varchar(10), bdl.BedDowntimeLog_endDate, 104)  as endDate, 
			 convert(varchar(10), bdl.BedDowntimeLog_begDate, 104)  as begDate, 
			 DATEDIFF(day, bdl.BedDowntimeLog_begDate, bdl.BedDowntimeLog_endDate) + 1 as durationOfPeriod,
			 bdl.BedDowntimeLog_RepairCount + bdl.BedDowntimeLog_ReasonsCount as plainBeds,
			 bdl.LpuSectionBedProfile_id as BedProfile_id,
			 *
			 -- end select
			 FROM 
			 -- from
			 dbo.BedDowntimeLog bdl with (nolock)
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

	/**
	 * @param $data
	 * @return array|bool|false
	 */
	public function getDataForXLS($data)
	{
		$params = [
			'begDate' => $data['begDate']
			, 'endDate' => $data['endDate']
			, 'LpuSection_id' => $data['LpuSection_id']
		];

		$sort = '';
		if (!empty($data['sortField']) && !empty($data['sortDirection'])) {
			$sort = "{$data['sortField']} {$data['sortDirection']}";
		}

		$filter = '';
		if (!empty($data['Export_BedProfile_id'])) {
			$params['BedProfile_id'] = $data['Export_BedProfile_id'];
			$filter .= 'AND bdl.LpuSectionBedProfile_id = :BedProfile_id';
		}

		$query = "
			SELECT
			 -- select
			 convert(varchar(10), bdl.BedDowntimeLog_endDate, 104)  as endDate, 
			 convert(varchar(10), bdl.BedDowntimeLog_begDate, 104)  as begDate, 
			 DATEDIFF(day, bdl.BedDowntimeLog_begDate, bdl.BedDowntimeLog_endDate) + 1 as durationOfPeriod,
			 bdl.BedDowntimeLog_RepairCount + bdl.BedDowntimeLog_ReasonsCount as plainBeds,
		     ls.LpuSection_FullName as LpuSection,
		     bdl.LpuSectionBedProfile_id as BedProfile_id,
			 *
			 -- end select
			 FROM 
			 -- from
7			 dbo.BedDowntimeLog bdl with (nolock)
			 LEFT JOIN v_LpuSection ls with (nolock) ON ls.LpuSection_id = bdl.LpuSection_id
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

	public function getBedDowntimeLog_Count($data)
	{
		$params = [
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionBedProfile_id' => $data['LpuSectionBedProfile_id']
		];

		$query = "
		SELECT 
		-- select
		ISNULL(SUM(LpuSectionWard_BedCount), 0) as bedCount
		-- end select
		FROM 
		-- from
		dbo.v_LpuSectionWard LSW with (nolock)
		-- end from
		LEFT JOIN dbo.LpuSectionBedProfile LSBP with (nolock) ON LSBP.LpuSectionBedProfile_Code = CAST(LSW.LpuWardType_id AS CHAR(25))
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
			 CONCAT(convert(varchar(10), bdl.BedDowntimeLog_begDate, 104), ' - ',convert(varchar(10), bdl.BedDowntimeLog_endDate, 104))  as BedDowntime_PeriodDate, 
			 DATEDIFF(day, bdl.BedDowntimeLog_begDate, bdl.BedDowntimeLog_endDate) + 1 as durationOfPeriod,
			 bdl.BedDowntimeLog_RepairCount + bdl.BedDowntimeLog_ReasonsCount as plainBeds,
			 ls.LpuSection_FullName as LpuSection,
			 bdl.LpuSectionBedProfile_id as BedProfile_id,
			 *
			 -- end select
			 FROM 
			 -- from
			 dbo.BedDowntimeLog bdl with (nolock)
			 LEFT JOIN v_LpuSection ls with (nolock) ON ls.LpuSection_id = bdl.LpuSection_id
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
			declare
				@BedDowntimeLog_id bigint = :BedDowntimeLog_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				 @BedDowntimeLog_id = @BedDowntimeLog_id output,
				 @LpuSection_id = :LpuSection_id,
				 @LpuSectionBedProfile_id = :LpuSectionBedProfile_id,
				 @BedDowntimeLog_Count = :BedDowntimeLog_Count,
				 @BedDowntimeLog_begDate = :BedDowntimeLog_begDate,
				 @BedDowntimeLog_endDate = :BedDowntimeLog_endDate,
				 @BedDowntimeLog_RepairCount = :BedDowntimeLog_RepairCount,
				 @BedDowntimeLog_ReasonsCount = :BedDowntimeLog_ReasonsCount,
				 @BedDowntimeLog_Reasons = :BedDowntimeLog_Reasons,
				 @pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @BedDowntimeLog_id as Attribute_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
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
		ISNULL(SUM(DATEDIFF(day, '{$params['begDate']}', EvnDirection_setDT)), 0) as sumEnvPS
			 -- end select
		FROM
		 	-- from
			dbo.EvnPS ERP with (nolock)
			LEFT JOIN dbo.v_LpuSection LS with (nolock) on LS.LpuSection_id = ERP.LpuSection_id
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

	public function deleteBedDowntimeRecord($data)
	{
		$params = [
			'BedDowntimeLog_id' => $data['BedDowntimeLog_id'],
			'IsRemove' => 2
		];

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);
			exec p_BedDowntimeLog_del
				@BedDowntimeLog_id = :BedDowntimeLog_id,
				@IsRemove = :IsRemove,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;
			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		$res = $this->db->query($query, $params);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @return string[]
	 */
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