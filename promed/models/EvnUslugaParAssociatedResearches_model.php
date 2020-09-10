<?php defined('BASEPATH') or die ('No direct script access allowed');

class EvnUslugaParAssociatedResearches_model extends swModel
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Сохранение исследования, прикрепленного к услуге
	 */
	function saveEvnUslugaParAssociatedResearches($data)
	{
		$proc = "p_EvnUslugaParAssociatedResearches_ins";
		if (!empty($data['EvnUslugaParAssociatedResearches_id'])) {
			$proc = "p_EvnUslugaParAssociatedResearches_upd";
		} else {
			$data['EvnUslugaParAssociatedResearches_id'] = null;
		}

		$sql = "
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
				
			SET @Res = :EvnUslugaParAssociatedResearches_id;

			EXEC {$proc}
				@EvnUslugaParAssociatedResearches_id = @Res output,
				@EvnUslugaPar_id  = :EvnUslugaPar_id,
				@Study_uid  = :Study_uid,
				@Study_date  = :Study_date,
				@Study_time  = :Study_time,
				@Patient_Name  = :Patient_Name,
				@EvnUslugaParConclusion = null,
				@LpuEquipmentPacs_id  = null,
				@pmUser_id = :pmUser_id,			
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnUslugaParAssociatedResearches_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg
		";

		$query = $this->db->query($sql, $data);
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	/**
	 *  Получение исследования, прикрепленного к услуге
	 */
	function getEvnUslugaParAssociatedResearches($data) {
		$query = "
			select
				Study_uid
			from
				v_EvnUslugaParAssociatedResearches (nolock)
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		";

		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 *  Получение исследования, прикрепленного к услуге
	 */
	function getEvnUslugaParAssociatedResearchesForAPI($data) {
		$query = "
			select
				EvnUslugaParAssociatedResearches_id,
				EvnUslugaPar_id,
				Study_uid as Study_uid,
				Study_date as Study_date,
				Study_time as Study_time,
				Patient_Name as Patient_Name
			from
				v_EvnUslugaParAssociatedResearches (nolock)
			where
				EvnUslugaParAssociatedResearches_id = :EvnUslugaParAssociatedResearches_id
		";

		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 * Удаление исследования, прикрепленного к услуге
	 */
	function deleteEvnUslugaParAssociatedResearches($data) {
		$params = array('EvnUslugaParAssociatedResearches_id' => $data['EvnUslugaParAssociatedResearches_id']);

		$query = "
			declare
				@Error_Code int = 0,
				@Error_Message varchar(4000) = '';
			exec p_EvnUslugaParAssociatedResearches_del
				@EvnUslugaParAssociatedResearches_id = :EvnUslugaParAssociatedResearches_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}
}