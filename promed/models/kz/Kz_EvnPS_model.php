<?php
/**
 * Class Kz_EvnPS_model
 */
require_once(APPPATH.'models/EvnPS_model.php');

class Kz_EvnPS_model extends EvnPS_model {
	
	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateLpuSectionWardId($id, $value = null) {
		
		$getbedevnlink_id = $this->getFirstResultFromQuery("select GetBedEvnLink_id from r101.GetBedEvnLink (nolock) where Evn_id = ?", [$id]);
		if ($value != null) {
			$proc = !$getbedevnlink_id ? 'r101.p_GetBedEvnLink_ins' : 'r101.p_GetBedEvnLink_upd';
			
			return $this->execCommonSP($proc, [
				'GetBedEvnLink_id' => $getbedevnlink_id ? $getbedevnlink_id : null,
				'Evn_id' => $id,
				'GetBed_id' => $value,
				'pmUser_id' => $this->promedUserId
			], 'array_assoc');
		} elseif ($getbedevnlink_id != false) {
			return $this->execCommonSP('r101.p_GetBedEvnLink_del', [
				'GetBedEvnLink_id' => $getbedevnlink_id
			], 'array_assoc');
		}
	}
}
