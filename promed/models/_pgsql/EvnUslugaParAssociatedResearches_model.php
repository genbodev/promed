<?php
defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Class EvnUslugaParAssociatedResearches_model
 * @property-read CI_DB_driver $db
 */
class EvnUslugaParAssociatedResearches_model extends SwPgModel
{

    /**
     * Сохранение исследования, прикрепленного к услуге
     * @param array $data
     * @return array|bool
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
			select
			    EvnUslugaParAssociatedResearches_id as \"EvnUslugaParAssociatedResearches_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from {$proc}
			(
				EvnUslugaParAssociatedResearches_id := :EvnUslugaParAssociatedResearches_id,
				EvnUslugaPar_id  := :EvnUslugaPar_id,
				Study_uid  := :Study_uid,
				Study_date  := :Study_date,
				Study_time  := :Study_time,
				Patient_Name  := :Patient_Name,
				EvnUslugaParConclusion := null,
				LpuEquipmentPacs_id  := null,
				pmUser_id := :pmUser_id
			)
		";

        $query = $this->db->query($sql, $data);
        if (!is_object($query)) {
            return false;
        }
        
        return $query->result_array();
    }

    /**
     *  Получение исследования, прикрепленного к услуге
     * @param array $data
     * @return array|false
     */
    public function getEvnUslugaParAssociatedResearches($data)
    {
        $query = "
			select
				Study_uid as \"Study_uid\" 
			from
				v_EvnUslugaParAssociatedResearches
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		";

        $resp = $this->queryResult($query, $data);

        return $resp;
    }

    /**
     *  Получение исследования, прикрепленного к услуге
     * @param array $data
     * @return array|false
     */
    public function getEvnUslugaParAssociatedResearchesForAPI($data)
    {
        $query = "
			select
				EvnUslugaParAssociatedResearches_id as \"EvnUslugaParAssociatedResearches_id\",
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				Study_uid as \"Study_uid\",
				Study_date as \"Study_date\",
				Study_time as \"Study_time\",
				Patient_Name as \"Patient_Name\"
			from
				v_EvnUslugaParAssociatedResearches
			where
				EvnUslugaParAssociatedResearches_id = :EvnUslugaParAssociatedResearches_id
		";

        $resp = $this->queryResult($query, $data);

        return $resp;
    }

    /**
     * Удаление исследования, прикрепленного к услуге
     * @param array $data
     * @return bool
     */
    public function deleteEvnUslugaParAssociatedResearches($data)
    {
        $params = ['EvnUslugaParAssociatedResearches_id' => $data['EvnUslugaParAssociatedResearches_id']];

        $query = "
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_EvnUslugaParAssociatedResearches_del
			(
				EvnUslugaParAssociatedResearches_id := :EvnUslugaParAssociatedResearches_id
            )
		";

        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }
        
        return $result->result('array');
    }
}