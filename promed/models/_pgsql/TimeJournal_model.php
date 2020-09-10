<?php
/**
* TimeJournal_model - модель для работы с записями журнала учета рабочего
* времени сотрудников
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author
* @version      12.2019
*/
class TimeJournal_model extends swPgModel
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает записи, соответствующие параметрам:
	 *  Lpu_id - идентификатор ЛПУ (Server_id)
	 *  pmUser_tid - идентификатор пользователя, чьи смены нужно вернуть.
	 *  currentDateTime - текущие дата и время. Если задано, ищет записи, в которых
	 *   это время попадает в промежуток между TimeJournal_BegDT и TimeJournal_EndDT
	 *  minBegDT, maxBegDT - минимальные и максимальные дата и время начала смены:
	 *   TimeJournal_BegDT >= minBegDT
	 *   TimeJournal_BegDT <= maxBegDT
	 *  minEndDT, maxEndDT - минимальные и максимальные дата и время завершения:
	 *   TimeJournal_EndDT >= minEndDT
	 *   TimeJournal_EndDT <= minEndDT
	 *
	 *  Если передан параметр fullInfo, включает в ответ, помимо данных, имеющихся
	 *  непосредственно в TimeJournal, также ФИО и табельный № врача
	 */
	function loadTimeJournal($data)
	{
		$fields =
			"
			TimeJournal_id as \"TimeJournal_id\",
			tj.pmUser_tid as \"pmUser_tid\",
			tj.pmUser_insID as \"pmUser_insID\",
			tj.pmUser_updID as \"pmUser_updID\",
			TimeJournal_BegDT as \"TimeJournal_BegDT\",
			TimeJournal_EndDT as \"TimeJournal_EndDT\",
			to_char(TimeJournal_BegDT, 'dd.mm.yyyy') as \"BegDT_date\",
			to_char(TimeJournal_EndDT, 'dd.mm.yyyy') as \"EndDT_date\",
			to_char(TimeJournal_BegDT, 'hh24:mi:ss') as \"BegDT_time\",
			to_char(TimeJournal_EndDT, 'hh24:mi:ss') as \"EndDT_time\",
			tj.Server_id as \"Server_id\"
		";

		$from = "v_TimeJournal tj";

		$filter = "";

		// Соберем условия для WHERE:
		if (isset($data['Lpu_id']))
			$filter = $filter .
				"AND tj.Server_id = :Lpu_id
				";

		if (isset($data['pmUser_tid']))
			$filter = $filter .
				"AND tj.pmUser_tid = :pmUser_tid
				";

		if (isset($data['MedStaffFact_id']))
		{
			// Если ид. пользователя не передан, но известен ид. врача,
			// пытаемся найти ид. пользователя по ид. врача:
			$filter = $filter .
				"AND msf.MedStaffFact_id = :MedStaffFact_id
				";


			$from = $from .
				" LEFT JOIN v_pmUserCache uc ON uc.pmUser_id = tj.pmUser_tid
				  LEFT JOIN v_MedStaffFact msf ON msf.MedPersonal_id = uc.MedPersonal_id";

			$pmUserCacheJoined = true;
		}

		if (isset($data['currentDateTime']))
			$filter = $filter .
				"AND TimeJournal_BegDT <= :currentDateTime
				 AND TimeJournal_EndDT >= :currentDateTime
				";

		if (isset($data['minBegDT']))
			$filter = $filter .
				"AND TimeJournal_BegDT >= :minBegDT
				";

		if (isset($data['maxBegDT']))
			$filter = $filter .
				"AND TimeJournal_BegDT <= :maxBegDT
				";

		if (isset($data['minEndDT']))
			$filter = $filter .
				"AND TimeJournal_EndDT >= :minEndDT
				";

		if (isset($data['maxEndDT']))
			$filter = $filter .
				"AND TimeJournal_EndDT <= :maxEndDT
				";

		if (isset($data['fullInfo']))
		{
			$fields = $fields .
				",
				mp.Person_FIO as \"MedPersonal_FIO\",
				mp.MedPersonal_TabCode as \"MedPersonal_TabCode\"
				";

			if (!isset($pmUserCacheJoined))
				$from = $from .
					" LEFT JOIN v_pmUserCache uc ON uc.pmUser_id = tj.pmUser_tid
					";

			$from = $from .
				" LEFT JOIN v_MedPersonal mp ON mp.MedPersonal_id = uc.MedPersonal_id";
		}

		$orderby = "TimeJournal_BegDT";
		
		if (isset($data['fullInfo']))
			$orderby = "mp.Person_FIO, " . $orderby;

		$sql =
			"SELECT " . $fields .
			" FROM " . $from .
			" WHERE 1 = 1 " . $filter .
			"ORDER BY " .$orderby;

		$res = $this->db->query($sql, $data);

		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Создает запись в журнале или редактирует имеющуюся запись, в зависимости
	 * от того, передан ли идентификатор записи.
	 */
	function saveTimeJournalRecord($data)
	{
		if ((!isset($data['TimeJournal_id'])) || ($data['TimeJournal_id'] <= 0))
			$procedure = 'p_TimeJournal_ins';
		else
			$procedure = 'p_TimeJournal_upd';

		$query = "
			SELECT
				TimeJournal_id as \"TimeJournal_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from " . $procedure . "(
				TimeJournal_id := :TimeJournal_id,
				pmUser_tid := :pmUser_tid,
				pmUser_id := :pmUser_id,
				TimeJournal_BegDT := :TimeJournal_BegDT,
				TimeJournal_EndDT := :TimeJournal_EndDT,
				Server_id := :Server_id
			)
			";

		$params =
			array(
				'TimeJournal_id' => $data['TimeJournal_id'],
				'pmUser_tid' => $data['pmUser_tid'],
				'pmUser_id' => $data['pmUser_id'],
				'TimeJournal_BegDT' => $data['TimeJournal_BegDT'],
				'TimeJournal_EndDT' => $data['TimeJournal_EndDT'],
				'Server_id' => $data['Server_id']
			);

		$result = $this->db->query($query, $params);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}
}

