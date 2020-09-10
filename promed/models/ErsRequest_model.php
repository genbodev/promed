<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ErsRequest_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 */

class ErsRequest_model extends swModel {

	/**
	 * Сохранение
	 */
	function sendErsToFss($data) {
		
		// имитация регистрации
		
		$number = '9'.mt_rand(100000000, 999999999);
		
		$this->db->query("update EvnERS with(rowlock) set ERSStatus_id = 1 where EvnERS_id = ? ", [$data['EvnERS_id']]);
		$this->db->query("update EvnErsBirthCertificate with(rowlock) set EvnERSBirthCertificate_Number = '$number' where EvnERS_id = ? ", [$data['EvnERS_id']]);
		
		return ['sucess' => true];
	}

	/**
	 * Сохранение
	 */
	function save($data) {
		return $this->execCommonSP('p_ERSRequest_ins', [
			'ERSRequest_id' => null,
			'EvnERS_id' => $data['EvnERS_id'],
			'ERSRequestType_id' => 9,
			'ERSRequestStatus_id' => 7, // в очереди
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
	}
}
