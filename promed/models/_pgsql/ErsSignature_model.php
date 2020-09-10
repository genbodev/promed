<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ErsSignature_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 */

class ErsSignature_model extends swPgModel {

	/**
	 * Сохранение
	 * @param $data
	 * @return array
	 */
	function doSignErs($data)
	{
		$res = $this->getFirstRowFromQuery("
			select 
                ErsSignature_id as \"ErsSignature_id\" 
            from v_ErsSignature 
            where EvnErs_id = :EvnErs_id a
               and ErsSignatureType_id = :ErsSignatureType_id
        ", $data);

		if (!isset($res['ErsSignature_id'])) {
			$res = $this->execCommonSP('p_ErsSignature_ins', [
				'ErsSignature_id' => null,
				'EvnErs_id' => $data['EvnErs_id'],
				'ErsSignatureType_id' => $data['ErsSignatureType_id'],
				'ErsSignature_xml' => $data['ErsSignature_xml'],
				'pmUser_id' => $data['pmUser_id']
			], 'array_assoc');
		} else {
			$res = $this->execCommonSP('p_ErsSignature_upd', [
				'ErsSignature_id' => $res['ErsSignature_id'],
				'EvnErs_id' => $data['EvnErs_id'],
				'ErsSignatureType_id' => $data['ErsSignatureType_id'],
				'ErsSignature_xml' => $data['ErsSignature_xml'],
				'pmUser_id' => $data['pmUser_id']
			], 'array_assoc');
		}
		return ['success' => 1];
	}

	/**
	 * Сохранение подписи счёта
	 * @param $data
	 * @return array
	 */
	function doSignBill($data)
	{
		$res = $this->getFirstRowFromQuery("
			select 
                ErsSignature_id as \"ErsSignature_id\" 
            from v_ErsSignature 
            where ErsBill_id = :ErsBill_id
               and ErsSignatureType_id = :ErsSignatureType_id
        ", [
			'ErsBill_id' => $data["ErsBill_id"],
			'ErsSignatureType_id' => $data["ErsSignatureType_id"],
		]);

		if (!isset($res['ErsSignature_id'])) {
			$this->execCommonSP('p_ErsSignature_ins', [
				'ErsSignature_id' => null,
				'ErsBill_id' => $data['ErsBill_id'],
				'ErsSignatureType_id' => $data['ErsSignatureType_id'],
				'ErsSignature_xml' => $data['ErsSignature_xml'],
				'pmUser_id' => $data['pmUser_id']
			], 'array_assoc');
		} else {
			$res = $this->execCommonSP('p_ErsSignature_upd', [
				'ErsSignature_id' => $res['ErsSignature_id'],
				'ErsBill_id' => $data['ErsBill_id'],
				'ErsSignatureType_id' => $data['ErsSignatureType_id'],
				'ErsSignature_xml' => $data['ErsSignature_xml'],
				'pmUser_id' => $data['pmUser_id']
			], 'array_assoc');
		}

		$this->updateBillStatus($data);
		return ['success' => 1];
	}

	/**
	 * Обновление статуса счёта после подписания
	 * @param $data
	 * @return void
	 */
	private function updateBillStatus($data) {

		$sgn = $this->queryList("
			select distinct
				ErsSignatureType_id as \"ErsSignatureType_id\"
			from ErsSignature
			where ErsBill_id = :ErsBill_id
		", $data);

		$status = false;

		if (count($sgn) == 3) $status = 24; // Готов к регистрации в ФСС
		elseif (in_array(1, $sgn) && in_array(2, $sgn)) $status = 5; // Подписан МО и Руководителем МО
		elseif (in_array(1, $sgn) && in_array(3, $sgn)) $status = 6; // Подписан МО и Бухгалтером
		elseif (in_array(2, $sgn) && in_array(3, $sgn)) $status = 7; // Подписан Руководителем МО и Бухгалтером
		elseif (in_array(3, $sgn)) $status = 4; // Подписан Бухгалтером
		elseif (in_array(1, $sgn)) $status = 22; // Подписан МО
		elseif (in_array(2, $sgn)) $status = 23; // Подписан Руководителем МО

		if ($status !== false) {
			$this->db->query("update ErsBill set ErsStatus_id = ? where ErsBill_id = ? ", [
				$status,
				$data['ErsBill_id']
			]);
		}
	}

	/**
	 * Сохранение подписи талона
	 * @param $data
	 * @return array
	 */
	function doSignTicket($data) {
		$res = $this->execCommonSP('p_ErsSignature_ins', [
			'ErsSignature_id' => null,
			'EvnErs_id' => $data['EvnErs_id'],
			'ErsSignatureType_id' => $data['ErsSignatureType_id'],
			'ErsSignature_xml' => $data['ErsSignature_xml'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');

		$this->updateTicketStatus($data);

		return ['success' => 1];
	}

	/**
	 * Обновление статуса талона после подписания
	 * @param $data
	 * @return void
	 */
	private function updateTicketStatus($data) {

		$sgn = $this->queryList("
			select distinct
				ErsSignatureType_id as \"ErsSignatureType_id\"
			from ErsSignature
			where EvnErs_id = :EvnErs_id
		", $data);

		$status = false;

		if (count($sgn) == 2) $status = 24; // Готов к регистрации в ФСС
		elseif (in_array(1, $sgn)) $status = 22; // Подписан МО
		elseif (in_array(2, $sgn)) $status = 23; // Подписан Руководителем МО

		if ($status !== false) {
			$this->db->query("update EvnErs set ErsStatus_id = ? where EvnErs_id = ? ", [
				$status,
				$data['EvnErs_id']
			]);
		}
	}
}
