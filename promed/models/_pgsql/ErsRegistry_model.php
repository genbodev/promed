<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ErsRegistry_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 */

class ErsRegistry_model extends swPgModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Првоерка возможности создания
	 * @param $data
	 * @return array|false
	 */
	function checkCanCreate($data) {

		$chk = $this->getFirstResultFromQuery("
			select
				EvnERSTicket_id as \"EvnERSTicket_id\"
			from v_EvnERSTicket ert
				left join v_ErsRegistry eer on eer.ErsRegistry_id = ert.ErsRegistry_id
			where
				eer.ErsRegistry_id is null
				and ert.ERSStatus_id = 13
				-- 147820
				-- and ert.Lpu_id = :Lpu_id
			limit 1
		", $data);

		if ($chk === false) {
			return [
				'success' => false,
				'Error_Message' => "Отсутствуют талоны для включения в реестр"
			];
		}

		return ['success' => true];
	}

	/**
	 * Получение номера реестра
	 * @param $data
	 * @return array|false
	 */
	function getNumber($data) {

		return $this->getFirstRowFromQuery("
			select
				coalesce(max(ErsRegistry_Number), 0) + 1 as \"ErsRegistry_Number\"
			from
				v_ErsRegistry
			where
				Lpu_id = :Lpu_id and
				ErsRegistry_Month = :ErsRegistry_Month and
				ErsRegistry_Year = :ErsRegistry_Year
		", $data);
	}

	/**
	 * Загрузка списка реестров
	 * @param $data
	 * @return array|false
	 */
	function loadJournal($data) {
		
		$filters = '1 = 1';
		$queryParams = [];
		
		// ---- фильтры по реестрам ----

		if (!empty($data['ErsRegistry_Date_Range'])
			&& count($data['ErsRegistry_Date_Range']) == 2
			&& !empty($data['ErsRegistry_Date_Range'][0])
			&& !empty($data['ErsRegistry_Date_Range'][1])) {
			$filters .= ' and eet.ErsRegistry_Date between :ErsRegistry_Date_RangeStart and :ErsRegistry_Date_RangeEnd ';
			$queryParams['ErsRegistry_Date_RangeStart'] = $data['ErsRegistry_Date_Range'][0];
			$queryParams['ErsRegistry_Date_RangeEnd'] = $data['ErsRegistry_Date_Range'][1];
		}

		
		if (!empty($data['ERSStatus_id'])) {
			$filters .= ' and eet.ERSStatus_id = :ERSStatus_id ';
			$queryParams['ERSStatus_id'] = $data['ERSStatus_id'];
		}

		if (!empty($data['ErsRegistry_Number'])) {
			$filters .= ' and eet.ErsRegistry_Number = :ErsRegistry_Number ';
			$queryParams['ErsRegistry_Number'] = $data['ErsRegistry_Number'];
		}

		// ---- фильтры по счетам ----

		if (!empty($data['ErsBill_Date_Range'])
				&& count($data['ErsBill_Date_Range']) == 2
				&& !empty($data['ErsBill_Date_Range'][0])
				&& !empty($data['ErsBill_Date_Range'][1])) {
			$filters .= ' and erb.ErsBill_Date between :ErsBill_Date_RangeStart and :ErsBill_Date_RangeEnd ';
			$queryParams['ErsBill_Date_RangeStart'] = $data['ErsBill_Date_Range'][0];
			$queryParams['ErsBill_Date_RangeEnd'] = $data['ErsBill_Date_Range'][1];
		}

		if (!empty($data['ERSStatus_id'])) {
			$filters .= ' and erb.ERSStatus_id = :ERSStatus_id ';
			$queryParams['ERSStatus_id'] = $data['ERSStatus_id'];
		}

		if (!empty($data['ErsBill_Number'])) {
			$filters .= ' and erb.ErsBill_Number = :ErsBill_Number ';
			$queryParams['ErsBill_Number'] = $data['ErsBill_Number'];
		}

		// ---- фильтры по запросам ----

		if (!empty($data['ERSRequestType_id'])) {
			$filters .= ' and ER.ERSRequestType_id = :ERSRequestType_id ';
			$queryParams['ERSRequestType_id'] = $data['ERSRequestType_id'];
		}
		
		if (!empty($data['ERSRequestStatus_id'])) {
			$filters .= ' and ER.ERSRequestStatus_id = :ERSRequestStatus_id ';
			$queryParams['ERSRequestStatus_id'] = $data['ERSRequestStatus_id'];
		}
		
		return $this->queryResult("
			select 
				eet.ErsRegistry_id as \"ErsRegistry_id\",
				eet.ERSStatus_id as \"ERSStatus_id\",
				erb.ErsBill_id as \"ErsBill_id\",
				to_char(erb.ErsBill_Date, 'DD.MM.YYYY') as \"ErsBill_Date\",
				eet.ErsRegistry_Number as \"ErsRegistry_Number\",
				to_char(eet.ErsRegistry_Date, 'DD.MM.YYYY') as \"ErsRegistry_Date\",
				eetc.cnt as \"ErsRegistry_TicketsCount\",
				es.ERSStatus_Name as \"ERSStatus_Name\",
				erb.ErsBill_Number as \"ErsBill_Number\",
				esb.ERSStatus_id as \"ERSStatus_BillId\",
				esb.ERSStatus_Name as \"ERSStatus_BillName\",
				ERT.ErsRequestType_Name as \"ErsRequestType_Name\",
				ERSt.ErsRequestStatus_Name as \"ErsRequestStatus_Name\",
				substring(ERE.ERSRequestError, 1, length(ERE.ERSRequestError)-1) as \"ErsRequestError\"
			from v_ErsRegistry eet
				left join v_ErsBill erb on erb.ErsRegistry_id = eet.ErsRegistry_id
				left join v_ERSStatus es on es.ERSStatus_id = eet.ERSStatus_id
				left join v_ERSStatus esb on esb.ERSStatus_id = erb.ERSStatus_id
				left join v_ErsRequest ER on ER.ErsRequest_id = erb.ErsRequest_id
				left join v_ErsRequestType ERT on ERT.ErsRequestType_id = ER.ErsRequestType_id
				left join v_ErsRequestStatus ERSt on ERSt.ErsRequestStatus_id = ER.ErsRequestStatus_id
				left join v_Lpu lpu on lpu.Lpu_id = eet.Lpu_id
				left join lateral (
					select count(*) as cnt
					from v_EvnErsTicket
					where ErsRegistry_id = eet.ErsRegistry_id
				) as eetc on true
				left join lateral (
					select
						string_agg(ere.ERSRequestError_Descr, ', ') as ERSRequestError
					from ERSRequestError ere
					where ere.ERSRequest_id = ER.ErsRequest_id
				) as ERE on true
			where
				{$filters}
			order by 
				eet.ErsRegistry_Date desc
		", $queryParams);
	}

	/**
	 * Загрузка списка талонов в реестре
	 * @param $data
	 * @return array|false
	 */
	function loadTickets($data) {

		return $this->queryResult("
			select 
				eet.EvnERSTicket_id as \"EvnERSTicket_id\",
				ers.EvnERSBirthCertificate_Number as \"EvnERSBirthCertificate_Number\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				to_char(ers.EvnErsBirthCertificate_PregnancyRegDate, 'dd.mm.yyyy') as \"EvnErsBirthCertificate_PregnancyRegDate\",
				eet.EvnERSTicket_pid as \"EvnERSTicket_pid\",
				eet.ERSTicketType_id as \"ERSTicketType_id\",
				eet.ERSRequest_id as \"ERSRequest_id\",
				to_char(eet.EvnERSTicket_setDate, 'dd.mm.yyyy') as \"EvnERSTicket_setDate\",
				ertt.ERSTicketType_Name as \"ERSTicketType_Name\",
				es.ERSStatus_Name as \"ERSStatus_Name\"
			from v_EvnERSTicket eet
				inner join v_ERSTicketType ertt on ertt.ERSTicketType_id = eet.ERSTicketType_id
				inner join v_EvnERSBirthCertificate ers on ers.EvnERSBirthCertificate_id = eet.EvnERSTicket_pid
				inner join v_Person_all PS on eet.PersonEvn_id = ps.PersonEvn_id and eet.Server_id = ps.Server_id
				left join v_ERSStatus es on es.ERSStatus_id = eet.ERSStatus_id
				left join v_ErsRequest ER on ER.ErsRequest_id = eet.ErsRequest_id
				left join v_ErsRequestType ERT on ERT.ErsRequestType_id = ER.ErsRequestType_id
				left join v_ErsRequestStatus ERSt on ERSt.ErsRequestStatus_id = ER.ErsRequestStatus_id
			where 
				eet.ErsRegistry_id = :ErsRegistry_id
			order by 
				eet.EvnERSTicket_setDate desc
		", $data);
	}

	/**
	 * Загрузка реестра
	 * @param $data
	 * @return array|false
	 */
	function load($data) {
		
		return $this->queryResult("
			select 
				ers.ErsRegistry_id as \"ErsRegistry_id\",
				ers.Lpu_id as \"Lpu_id\",
				ers.ErsRegistry_Number as \"ErsRegistry_Number\",
				to_char(ers.ErsRegistry_Date, 'dd.mm.yyyy') as \"ErsRegistry_Date\",
				ers.ErsRegistry_Month as \"ErsRegistry_Month\",
				ers.ErsRegistry_Year as \"ErsRegistry_Year\",
				ers.ErsRegistry_TicketsCount as \"ErsRegistry_TicketsCount\"
			from v_ErsRegistry ers
			where 
				ers.ErsRegistry_id = :ErsRegistry_id
		", $data);
	}

	/**
	 * Сохранение реестра
	 * @param $data
	 * @return mixed
	 */
	function save($data) {
		
		$prs = $this->getFirstRowFromQuery("
			select
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\"
			from v_PersonState
			limit 1
		");
		
		$proc = empty($data['ErsRegistry_id']) ? 'p_ErsRegistry_ins' : 'p_ErsRegistry_upd';
		$res = $this->execCommonSP($proc, [
			'ErsRegistry_id' => array(
				'value' => $data['ErsRegistry_id'] > 0 ? $data['ErsRegistry_id'] : null,
				'out' => true,
				'type' => 'bigint',
			),
			'ErsRegistry_Number' => $data['ErsRegistry_Number'],
			'ErsRegistry_Date' => $data['ErsRegistry_Date'],
			'ErsRegistry_Month' => $data['ErsRegistry_Month'],
			'ErsRegistry_Year' => $data['ErsRegistry_Year'],
			'ErsRegistry_TicketsCount' => $data['ErsRegistry_TicketsCount'],
			'ERSStatus_id' => 21,
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');

		$data['ErsRegistry_id'] = $res['ErsRegistry_id'];

		$this->makeRegistry($data);

		return $res;
	}

	/**
	 * Формирование реестра
	 * @param $data
	 * @return void
	 */
	private function makeRegistry($data) {

		$this->db->query("
				update 
					EvnErsTicket
				set 
					ErsRegistry_id = null
				where 
					ErsRegistry_id = :ErsRegistry_id
			", [
			'ErsRegistry_id' => $data['ErsRegistry_id']
		]);

		$limit = '';

		if ($data['ErsRegistry_TicketsCount'] > 0) {
			$limit = "limit {$data['ErsRegistry_TicketsCount']} ";
		}

		$tickets = $this->queryList("
			select
				EvnERSTicket_id as \"EvnERSTicket_id\"
			from v_EvnERSTicket ert
				left join v_ErsRegistry eer on eer.ErsRegistry_id = ert.ErsRegistry_id
			where
				eer.ErsRegistry_id is null and
				ert.ERSStatus_id = 13 and
				ert.Lpu_id = :Lpu_id
			{$limit}
		", $data);

		if (count($tickets)) {
			$tickets = join(',', $tickets);
			$this->db->query("
				update 
					EvnErsTicket
				set 
					ErsRegistry_id = :ErsRegistry_id
				where 
					EvnERSTicket_id in ({$tickets})
			", [
				'ErsRegistry_id' => $data['ErsRegistry_id']
			]);
		}
	}

	function delete($data) {

		$this->db->query("
			update 
				EvnErsTicket
			set 
				ErsRegistry_id = null
			where 
				ErsRegistry_id = :ErsRegistry_id
		", [
			'ErsRegistry_id' => $data['ErsRegistry_id']
		]);

		return $this->execCommonSP('p_ErsRegistry_del', [
			'ErsRegistry_id' => $data['ErsRegistry_id'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
	}
}