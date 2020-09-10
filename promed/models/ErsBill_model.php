<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ErsBill_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 */

class ErsBill_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Загрузка списка счетов МО
	 * @param $data
	 * @return array|false
	 */
	function getOrgRSchet($data) {

		return $this->queryResult("
			select 
				ors.OrgRSchet_id,
				ors.OrgRSchet_Name,
				ors.OrgRSchet_RSchet,
				ob.Org_Name,
				ob.OrgBank_BIK,
				ob.OrgBank_KSchet
			from OrgRSchet ors (nolock)
			inner join v_OrgBank ob (nolock) on ors.OrgBank_id = ob.OrgBank_id
			inner join v_Lpu lpu (nolock) on lpu.Org_id = ors.Org_id
			where 
				ors.OrgRSchetType_id = 1 and 
				lpu.Lpu_id = :Lpu_id
		", $data);
	}

	/**
	 * Получение суммы счета
	 * @param $data
	 * @return array|false
	 */
	function gerBillAmount($data) {

		/* Минаев Евгений (15:07:56 3/12/2019)
		Талон 1 - 3000 р
		Талон 2 - 6000 р
		Талоны 3 по 1000р на КАЖДОГО наблюдаемого ребенка */

		return $this->getFirstRowFromQuery("
			with t as (
				select ert.ERSTicketType_id, eec.cnt
				from v_EvnERSTicket ert (nolock)
				outer apply (
					select count(*) as [cnt]
					from v_EvnErsChild (nolock)
					where EvnErsChild_pid = ert.EvnERSTicket_pid
				) eec
				where ert.ERSRegistry_id = :ErsRegistry_id
			)

			select 
				count(case when t.ERSTicketType_id = 1 then 1 end) * 3000 + 
				count(case when t.ERSTicketType_id = 2 then 1 end) * 6000 + 
				isnull(sum(case when t.ERSTicketType_id in(3, 4) then t.cnt end), 0) * 1000 
			as ErsBill_BillAmount
			from t
		", $data);
	}

	/**
	 * Загрузка счета
	 * @param $data
	 * @return array|false
	 */
	function load($data) {
		
		return $this->queryResult("
			select 
				ers.ErsBill_id,
				ers.ErsRegistry_id,
				ers.Lpu_id,
				ers.LpuFSSContract_id,
				ers.ErsBill_Name,
				ers.ErsBill_Number,
				convert(varchar(10), ers.ErsBill_Date, 104) as ErsBill_Date,
				ers.ErsBill_BankCheckingAcc,
				ers.ErsBill_BankName,
				ers.ErsBill_BankBIK,
				ers.ErsBill_BankCorrAcc,
				ers.ErsBill_BillAmount,
				ers.OrgRSchet_id
			from v_ErsBill ers (nolock)
			where 
				ers.ErsBill_id = :ErsBill_id
		", $data);
	}

	/**
	 * Сохранение счета
	 * @param $data
	 * @return mixed
	 */
	function save($data) {
		
		$prs = $this->getFirstRowFromQuery("select top 1 PersonEvn_id, Server_id from v_PersonState (nolock)");

		if (empty($data['ErsBill_id'])) {

			$this->db->query("
				update EvnERS with(rowlock) 
				set ERSStatus_id = 19
				where EvnERS_id = :EvnERS_id
			", [
				'EvnERS_id' => $data['ErsRegistry_id']
			]);
		}
		
		$proc = empty($data['ErsBill_id']) ? 'p_ErsBill_ins' : 'p_ErsBill_upd';
		return $this->execCommonSP($proc, [
			'ErsBill_id' => $data['ErsBill_id'] > 0 ? $data['ErsBill_id'] : null,
			'ErsRegistry_id' => $data['ErsRegistry_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuFSSContract_id' => $data['LpuFSSContract_id'],
			'ErsBill_Name' => $data['ErsBill_Name'],
			'ErsBill_Number' => $data['ErsBill_Number'],
			'ErsBill_Date' => $data['ErsBill_Date'],
			'ErsBill_BankCheckingAcc' => $data['ErsBill_BankCheckingAcc'],
			'ErsBill_BankName' => $data['ErsBill_BankName'],
			'ErsBill_BankBIK' => $data['ErsBill_BankBIK'],
			'ErsBill_BankCorrAcc' => $data['ErsBill_BankCorrAcc'],
			'ErsBill_BillAmount' => $data['ErsBill_BillAmount'],
			'ERSStatus_id' => 21,
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
	}

	/**
	 * отправка счета в ФСС
	 * @param $data
	 * @return array
	 */
	function sendToFss($data) {
		
		$res = $this->execCommonSP('p_ERSRequest_ins', [
			'ERSRequest_id' => array(
				'value' => null,
				'out' => true,
				'type' => 'bigint',
			),
			'EvnERS_id' => null,
			'ERSRequestType_id' => 5, // Счет на оплату и реестр талонов
			'ERSRequestStatus_id' => 7, // в очереди
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
		
		$this->db->query("
			update 
				EvnERS with(rowlock) 
			set 
				ERSStatus_id = 25, 
				ERSRequest_id = :ERSRequest_id
			where 
				EvnERS_id = :EvnERS_id
		", [
			'EvnERS_id' => $data['EvnERS_pid'],
			'ERSRequest_id' => $res['ERSRequest_id']
		]);
		
		$this->db->query("
			update 
				EvnERS with(rowlock) 
			set 
				ERSStatus_id = 25, 
				ERSRequest_id = :ERSRequest_id
			where 
				EvnERS_id = :EvnERS_id
		", [
			'EvnERS_id' => $data['EvnERS_id'],
			'ERSRequest_id' => $res['ERSRequest_id']
		]);
		
		return ['success' => 1];
	}

	/**
	 * запрос результата
	 * @param $data
	 * @return array
	 */
	function getFssResult($data) {
		
		$res = $this->execCommonSP('p_ERSRequest_ins', [
			'ERSRequest_id' => array(
				'value' => null,
				'out' => true,
				'type' => 'bigint',
			),
			'EvnERS_id' => null,
			'ERSRequestType_id' => 7, // Запрос результатов обработки из ФСС 
			'ERSRequestStatus_id' => 7, // в очереди
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
		
		$this->db->query("
			update 
				EvnERS with(rowlock) 
			set 
				ERSStatus_id = 25, 
				ERSRequest_id = :ERSRequest_id
			where 
				EvnERS_id = :EvnERS_id
		", [
			'EvnERS_id' => $data['EvnERS_id'],
			'ERSRequest_id' => $res['ERSRequest_id']
		]);
		
		return ['success' => 1];
	}

	function delete($data) {

		return $this->execCommonSP('p_ErsBill_del', [
			'ErsBill_id' => $data['ErsBill_id'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
	}
}