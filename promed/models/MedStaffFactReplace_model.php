<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MedStaffFactReplace_model - модель для работы с замещениями врачей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Polka
 * @access      public
 * @copyright   Copyright (c) 2017 Swan Ltd.
 * @author		Dmitrii Vlasenko
 * @version     10.09.2017
 */

class MedStaffFactReplace_model extends swModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка
	 * @param $data
	 * @return array|bool
	 */
	function loadMedStaffFactReplaceGrid($data) {
		$params = array();
		$filters = "(1=1)";

		if (!empty($data['Lpu_id'])) {
			$filters .= " and MSF.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($data['MedStaffFact_id'])) {
			$filters .= " and MSFR.MedStaffFact_id = :MedStaffFact_id";
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		if (!empty($data['MedStaffFact_rid'])) {
			$filters .= " and MSFR.MedStaffFact_rid = :MedStaffFact_rid";
			$params['MedStaffFact_rid'] = $data['MedStaffFact_rid'];
		}

		if (!empty($data['MedStaffFactReplace_DateRange'][0]) && !empty($data['MedStaffFactReplace_DateRange'][1])) {
			$filters .= "
				and MSFR.MedStaffFactReplace_BegDate <= :MedStaffFactReplace_Date2
				and MSFR.MedStaffFactReplace_EndDate >= :MedStaffFactReplace_Date1
			";
			$params['MedStaffFactReplace_Date1'] = $data['MedStaffFactReplace_DateRange'][0];
			$params['MedStaffFactReplace_Date2'] = $data['MedStaffFactReplace_DateRange'][1];
		}

		$query = "
			select
				MSFR.MedStaffFactReplace_id,
				MSFR.MedStaffFact_rid,
				MSFR.MedStaffFact_id,
				convert(varchar(10), MSFR.MedStaffFactReplace_BegDate, 104) as MedStaffFactReplace_BegDate,
				convert(varchar(10), MSFR.MedStaffFactReplace_EndDate, 104) as MedStaffFactReplace_EndDate,
				MSF2.Person_Fin + ISNULL(' (' + LS2.LpuSection_Name + ')', '') as MedStaffFact_rDesc,
				MSF.Person_Fin + ISNULL(' (' + LS.LpuSection_Name + ')', '') as MedStaffFact_Desc,
				pu.pmUser_Name
			from
				v_MedStaffFactReplace MSFR with (nolock)
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = MSF.LpuSection_id
				left join v_MedStaffFact MSF2 with (nolock) on MSF2.MedStaffFact_id = MSFR.MedStaffFact_rid
				left join v_LpuSection LS2 with (nolock) on LS2.LpuSection_id = MSF2.LpuSection_id
				left join v_pmUser pu with (nolock) on pu.pmUser_id = MSFR.pmUser_insID
			where
				{$filters}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение
	 * @param $data
	 * @return bool
	 */
	function saveMedStaffFactReplace($data) {
		if (empty($data['MedStaffFactReplace_id'])) {
			$data['MedStaffFactReplace_id'] = null;
			$procedure = 'p_MedStaffFactReplace_ins';
		} else {
			$procedure = 'p_MedStaffFactReplace_upd';
		}

		$params = array(
			'MedStaffFactReplace_id' => $data['MedStaffFactReplace_id'],
			'MedStaffFact_rid' => $data['MedStaffFact_rid'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedStaffFactReplace_BegDate' => $data['MedStaffFactReplace_BegDate'],
			'MedStaffFactReplace_EndDate' => $data['MedStaffFactReplace_EndDate'],
			'pmUser_id' => $data['pmUser_id']
		);

		// Исключить добавление дублирующих значений. Если найдено дублирующее значение, тогда выдается сообщение
		$resp = $this->queryResult("
			select top 1
				MSFR.MedStaffFactReplace_id,
				MSF2.Person_Fin as Person_rFin,
				MSF.Person_Fin,
				convert(varchar(10), MSFR.MedStaffFactReplace_BegDate, 104) as MedStaffFactReplace_BegDate,
				convert(varchar(10), MSFR.MedStaffFactReplace_EndDate, 104) as MedStaffFactReplace_EndDate
			from
				v_MedStaffFactReplace MSFR with (nolock)
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
				left join v_MedStaffFact MSF2 with (nolock) on MSF2.MedStaffFact_id = MSFR.MedStaffFact_rid
			where
				MSFR.MedStaffFact_rid = :MedStaffFact_rid
				and MSFR.MedStaffFact_id = :MedStaffFact_id
				and MSFR.MedStaffFactReplace_BegDate = :MedStaffFactReplace_BegDate
				and MSFR.MedStaffFactReplace_EndDate = :MedStaffFactReplace_EndDate
				and MSFR.MedStaffFactReplace_id <> ISNULL(:MedStaffFactReplace_id, 0)
		", $params);
		if (!empty($resp[0]['MedStaffFactReplace_id'])) {
			return array('Error_Msg' => 'В период c ' .$resp[0]['MedStaffFactReplace_BegDate']. ' по  ' .$resp[0]['MedStaffFactReplace_EndDate']. ' ' .$resp[0]['Person_rFin']. ' уже замещает ' .$resp[0]['Person_Fin']);
		}

		// Два врача не могут замещать одного врача в одно и тоже время. При пересечении периода дат замещения одного врача несколькими другими врачами, выдается сообщение
		$resp = $this->queryResult("
			select top 1
				MSFR.MedStaffFactReplace_id,
				MSF2.Person_Fin as Person_rFin,
				MSF.Person_Fin,
				convert(varchar(10), MSFR.MedStaffFactReplace_BegDate, 104) as MedStaffFactReplace_BegDate,
				convert(varchar(10), MSFR.MedStaffFactReplace_EndDate, 104) as MedStaffFactReplace_EndDate
			from
				v_MedStaffFactReplace MSFR with (nolock)
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
				left join v_MedStaffFact MSF2 with (nolock) on MSF2.MedStaffFact_id = MSFR.MedStaffFact_rid
			where
				MSFR.MedStaffFact_id = :MedStaffFact_id
				and MSFR.MedStaffFactReplace_BegDate >= :MedStaffFactReplace_EndDate
				and MSFR.MedStaffFactReplace_EndDate <= :MedStaffFactReplace_BegDate
				and MSFR.MedStaffFactReplace_id <> ISNULL(:MedStaffFactReplace_id, 0)
		", $params);
		if (!empty($resp[0]['MedStaffFactReplace_id'])) {
			return array('Error_Msg' => $resp[0]['Person_Fin'] . ' в период c ' .$resp[0]['MedStaffFactReplace_BegDate']. ' по  ' .$resp[0]['MedStaffFactReplace_EndDate']. ' замещает врач ' .$resp[0]['Person_rFin']);
		}

		// Замещаемого врача нельзя выбрать как замещающего врача. Если в качестве замещающего врача выбирают врача, который в этом периоде является в каком-либо графике замещения замещающим, тогда выдается сообщение
		$resp = $this->queryResult("
			select top 1
				MSFR.MedStaffFactReplace_id,
				MSF2.Person_Fin as Person_rFin,
				MSF.Person_Fin,
				convert(varchar(10), MSFR.MedStaffFactReplace_BegDate, 104) as MedStaffFactReplace_BegDate,
				convert(varchar(10), MSFR.MedStaffFactReplace_EndDate, 104) as MedStaffFactReplace_EndDate
			from
				v_MedStaffFactReplace MSFR with (nolock)
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
				left join v_MedStaffFact MSF2 with (nolock) on MSF2.MedStaffFact_id = MSFR.MedStaffFact_rid
			where
				MSFR.MedStaffFact_id = :MedStaffFact_rid
				and MSFR.MedStaffFactReplace_BegDate >= :MedStaffFactReplace_EndDate
				and MSFR.MedStaffFactReplace_EndDate <= :MedStaffFactReplace_BegDate
				and MSFR.MedStaffFactReplace_id <> ISNULL(:MedStaffFactReplace_id, 0)
		", $params);
		if (!empty($resp[0]['MedStaffFactReplace_id'])) {
			return array('Error_Msg' => 'Врач ' . $resp[0]['Person_Fin'] . ' в период c ' .$resp[0]['MedStaffFactReplace_BegDate']. ' по  ' .$resp[0]['MedStaffFactReplace_EndDate']. ' уже определён в качестве замещаемого сотрудника. Выберите другого сотрудника.');
		}

		$query = "
			declare
				@MedStaffFactReplace_id bigint = :MedStaffFactReplace_id,
				@Error_Code int,
				@Error_Message varchar(4000);
			exec {$procedure}
				@MedStaffFactReplace_id = @MedStaffFactReplace_id output,
				@MedStaffFact_rid = :MedStaffFact_rid,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedStaffFactReplace_BegDate = :MedStaffFactReplace_BegDate,
				@MedStaffFactReplace_EndDate = :MedStaffFactReplace_EndDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code OUTPUT,
				@Error_Message = @Error_Message OUTPUT
			select @MedStaffFactReplace_id as MedStaffFactReplace_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Удаление
	 */
	function deleteMedStaffFactReplace($data) {
		$params = array(
			'MedStaffFactReplace_id' => $data['id']
		);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_MedStaffFactReplace_del
				@MedStaffFactReplace_id = :MedStaffFactReplace_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Проверка
	 */
	function checkExist($data) {
		$params = array(
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'begDate' => $data['begDate'],
			'endDate' => $data['endDate']
		);

		$query = "
			declare @curDate date = dbo.tzGetDate();
			
			select top 1
				MedStaffFact_id
			from
				v_MedStaffFactReplace with (nolock)
			where
				MedStaffFact_rid = :MedStaffFact_id
				and MedStaffFactReplace_BegDate <= :endDate
				and MedStaffFactReplace_EndDate >= :begDate
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0]['MedStaffFact_id'])) {
			return array('Error_Msg' => '', 'exist' => true);
		} else {
			return array('Error_Msg' => '');
		}
	}

	/**
	 * Получение данных для формы
	 * @param $data
	 * @return bool
	 */
	function loadMedStaffFactReplaceForm($data) {
		$params = array(
			'MedStaffFactReplace_id' => $data['MedStaffFactReplace_id']
		);

		$query = "
			select top 1
				MSFR.MedStaffFactReplace_id,
				MSFR.MedStaffFact_rid,
				MSFR.MedStaffFact_id,
				convert(varchar(10), MSFR.MedStaffFactReplace_BegDate, 104) as MedStaffFactReplace_BegDate,
				convert(varchar(10), MSFR.MedStaffFactReplace_EndDate, 104) as MedStaffFactReplace_EndDate
			from
				v_MedStaffFactReplace MSFR with (nolock)
			where
				MSFR.MedStaffFactReplace_id = :MedStaffFactReplace_id
		";

		return $this->queryResult($query, $params);
	}
}