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

class ErsSignature_model extends swModel {

	/**
	 * Сохранение
	 * @param $data
	 * @return array
	 */
	function doSign($data) {
		
		$this->execCommonSP('p_ERSSignature_ins', [
			'ERSSignature_id' => null,
			'EvnERS_id' => $data['EvnERS_id'],
			'ERSSignatureType_id' => $data['ERSSignatureType_id'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
		
		if (2 <= $this->getFirstResultFromQuery("
			select count(*) [cnt]
			from ERSSignature (nolock)
			where EvnERS_id = :EvnERS_id
		", $data)) {
			$this->db->query("update EvnERS with(rowlock) set ERSStatus_id = 24 where EvnERS_id = ? ", [$data['EvnERS_id']]);
		}
		
		return ['success' => 1];
	}

	/**
	 * Сохранение подписи счёта
	 * @param $data
	 * @return array
	 */
	function doSignBill($data) {
		
		$this->execCommonSP('p_ERSSignature_ins', [
			'ERSSignature_id' => null,
			'EvnERS_id' => $data['EvnERS_id'],
			'ERSSignatureType_id' => $data['ERSSignatureType_id'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');

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
			select distinct ERSSignatureType_id
			from ERSSignature (nolock)
			where EvnERS_id = :EvnERS_id
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
			$this->db->query("update EvnERS with(rowlock) set ERSStatus_id = ? where EvnERS_id = ? ", [
				$status,
				$data['EvnERS_id']
			]);
		}
	}

	/**
	 * Сохранение подписи талона
	 * @param $data
	 * @return array
	 */
	function doSignTicket($data) {
		$this->execCommonSP('p_ERSSignature_ins', [
			'ERSSignature_id' => null,
			'EvnERS_id' => $data['EvnERS_id'],
			'ERSSignatureType_id' => $data['ERSSignatureType_id'],
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
			select distinct ERSSignatureType_id
			from ERSSignature (nolock)
			where EvnERS_id = :EvnERS_id
		", $data);

		$status = false;

		if (count($sgn) == 2) $status = 24; // Готов к регистрации в ФСС
		elseif (in_array(1, $sgn)) $status = 22; // Подписан МО
		elseif (in_array(2, $sgn)) $status = 23; // Подписан Руководителем МО

		if ($status !== false) {
			$this->db->query("update EvnERS with(rowlock) set ERSStatus_id = ? where EvnERS_id = ? ", [
				$status,
				$data['EvnERS_id']
			]);
		}
	}	
}
