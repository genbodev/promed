<?php defined('BASEPATH') or die ('No direct script access allowed');

class Logger_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

    /**
	 * Получение лога по человеку и МО
	 */
	function loadPersonHistory($data) {
		$filter = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['Person_id'])) {
			$filter .= " and Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}

		if (!empty($data['LogPeriod_beg'])) {
			$filter .= " and PersonHistory_insDT >= :LogPeriod_beg";
			$queryParams['LogPeriod_beg'] = $data['LogPeriod_beg'];
		}

		if (!empty($data['LogPeriod_end'])) {
			$filter .= " and PersonHistory_insDT <= :LogPeriod_end";
			$queryParams['LogPeriod_end'] = $data['LogPeriod_end'];
		}

		$resp = $this->queryResult("
			select
				PersonHistory_id as \"PersonHistory_id\",
				to_char(PersonHistory_insDT, 'yyyy-mm-dd hh24:mi:ss') as \"PersonHistory_insDT\",
				to_char(PersonHistory_getDT, 'yyyy-mm-dd hh24:mi:ss') as \"PersonHistory_getDT\"
			from
				iemc.PersonHistory
			where
				Lpu_id = :Lpu_id
				{$filter}
		", $queryParams);

		return $resp;
	}

    /**
	 * Получение лога по паспорту медицинского изделия
	 */
	function loadLoggerPMI($data) {
		$filter = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['LogPeriod_beg'])) {
			$filter .= " and LoggerPMI_insDT >= :LogPeriod_beg";
			$queryParams['LogPeriod_beg'] = $data['LogPeriod_beg'];
		}

		if (!empty($data['LogPeriod_end'])) {
			$filter .= " and LoggerPMI_insDT <= :LogPeriod_end";
			$queryParams['LogPeriod_end'] = $data['LogPeriod_end'];
		}

		$resp = $this->queryResult("
			select
				LoggerPMI_id as \"LoggerPMI_id\",
				LoggerPMI_InventNumber as \"LoggerPMI_InventNumber\",
				case when LoggerPMI_isStatusOK = 2 then 1 else 0 end as \"LoggerPMI_isStatusOK\",
				LoggerPMI_Message as \"LoggerPMI_Message\",
				to_char(LoggerPMI_setDT, 'yyyy-mm-dd hh24:mi:ss') as \"LoggerPMI_setDT\",
				to_char(LoggerPMI_insDT, 'yyyy-mm-dd hh24:mi:ss') as \"LoggerPMI_insDT\",
				to_char(LoggerPMI_updDT, 'yyyy-mm-dd hh24:mi:ss') as \"LoggerPMI_updDT\"
			from
				passport.LoggerPMI
			where
				Lpu_id = :Lpu_id
				{$filter}
		", $queryParams);

		return $resp;
	}

    /**
	 * Получение лога по паспорту МО
	 */
	function loadLoggerPMU($data) {
		$filter = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['LogPeriod_beg'])) {
			$filter .= " and LoggerPMU_insDT >= :LogPeriod_beg";
			$queryParams['LogPeriod_beg'] = $data['LogPeriod_beg'];
		}

		if (!empty($data['LogPeriod_end'])) {
			$filter .= " and LoggerPMU_insDT <= :LogPeriod_end";
			$queryParams['LogPeriod_end'] = $data['LogPeriod_end'];
		}

		$resp = $this->queryResult("
			select
				LoggerPMU_id as \"LoggerPMU_id\",
				case when LoggerPMU_isStatusOK = 2 then 1 else 0 end as \"LoggerPMU_isStatusOK\",
				LoggerPMU_Message as \"LoggerPMU_Message\",
				to_char(LoggerPMU_setDT, 'yyyy-mm-dd hh24:mi:ss') as \"LoggerPMU_setDT\",
				to_char(LoggerPMU_insDT, 'yyyy-mm-dd hh24:mi:ss') as \"LoggerPMU_insDT\",
				to_char(LoggerPMU_updDT, 'yyyy-mm-dd hh24:mi:ss') as \"LoggerPMU_updDT\"
			from
				passport.LoggerPMU
			where
				Lpu_id = :Lpu_id
				{$filter}
		", $queryParams);

		return $resp;
	}

    /**
	 * Получение лога отправки событий (КВС и ТАП)
	 */
	function loadIntegrationServiceEventLog($data) {
		$filter = "";
		$queryParams = array(
			'eventId' => $data['eventId']
		);

		if (!empty($data['LogPeriod_beg'])) {
			$filter .= " and eventDate >= :LogPeriod_beg";
			$queryParams['LogPeriod_beg'] = $data['LogPeriod_beg'];
		}

		if (!empty($data['LogPeriod_end'])) {
			$filter .= " and eventDate <= :LogPeriod_end";
			$queryParams['LogPeriod_end'] = $data['LogPeriod_end'];
		}

		$resp = $this->queryResult("
			select
				bodyType as \"bodyType\",
				description as \"description\",
				to_char(eventDate, 'yyyy-mm-dd hh24:mi:ss') as \"eventDate\",
				id as \"id\",
				level as \"level\",
				title as \"title\"
			from
				iemc.IntegrationServiceEventLog
			where
				eventId = :eventId
				{$filter}
		", $queryParams);

		return $resp;
	}
}