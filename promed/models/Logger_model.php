<?php defined('BASEPATH') or die ('No direct script access allowed');

class Logger_model extends swModel {
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
				PersonHistory_id,
				convert(varchar(19), PersonHistory_insDT, 120) as PersonHistory_insDT,
				convert(varchar(19), PersonHistory_getDT, 120) as PersonHistory_getDT
			from
				iemc.PersonHistory (nolock)
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
				LoggerPMI_id,
				LoggerPMI_InventNumber,
				case when LoggerPMI_isStatusOK = 2 then 1 else 0 end as LoggerPMI_isStatusOK,
				LoggerPMI_Message,
				convert(varchar(19), LoggerPMI_setDT, 120) as LoggerPMI_setDT,
				convert(varchar(19), LoggerPMI_insDT, 120) as LoggerPMI_insDT,
				convert(varchar(19), LoggerPMI_updDT, 120) as LoggerPMI_updDT
			from
				passport.LoggerPMI (nolock)
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
				LoggerPMU_id,
				case when LoggerPMU_isStatusOK = 2 then 1 else 0 end as LoggerPMU_isStatusOK,
				LoggerPMU_Message,
				convert(varchar(19), LoggerPMU_setDT, 120) as LoggerPMU_setDT,
				convert(varchar(19), LoggerPMU_insDT, 120) as LoggerPMU_insDT,
				convert(varchar(19), LoggerPMU_updDT, 120) as LoggerPMU_updDT
			from
				passport.LoggerPMU (nolock)
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
				bodyType,
				description,
				convert(varchar(19), eventDate, 120) as eventDate,
				id,
				level,
				title
			from
				iemc.IntegrationServiceEventLog (nolock)
			where
				eventId = :eventId
				{$filter}
		", $queryParams);

		return $resp;
	}
}