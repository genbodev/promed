<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * LpuOrgServed_model - модель для работы с обслуживаемыми организациями
 *http://redmine.swan.perm.ru/projects/promedweb-dlo/repository/revisions/10303
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Tokarev Sergey
 * @version      22.02.2012
 * 
 * @property-read CI_DB_driver $db
 */
class LpuPacsSettings_model extends SwPgModel
{
    /**
     * Получение настроек ПАКС
     *
     * @param array $data
     * @return bool
     */
    public function getCurrentPacsSettings($data)
    {
        $params = ['LpuSection_id' => $data['LpuSection_id']];
        $query = "
            SELECT
                LpuPacs_id as \"LpuPacs_id\",
                LpuPacs_aetitle as \"LpuPacs_aetitle\",
                LpuPacs_desc as \"LpuPacs_desc\",
                LpuPacs_ip as \"LpuPacs_ip\",
                LpuPacs_port as \"LpuPacs_port\",
                LpuSection_id as \"LpuSection_id\",
                pmUser_insID as \"pmUser_insID\",
                pmuser_updid as \"pmUser_updID\",
                LpuPacs_insDT as \"LpuPacs_insDT\",
                LpuPacs_updDT as \"LpuPacs_updDT\",
                LpuPacs_wadoPort as \"LpuPacs_wadoPort\",
                LpuPacs_Interval as \"LpuPacs_Interval\",
                LpuPacs_Interval_TimeType_id as \"LpuPacs_Interval_TimeType_id\",
                LpuPacs_CronIntervalFrom as \"LpuPacs_CronIntervalFrom\",
                LpuPacs_CronIntervalTo as \"LpuPacs_CronIntervalTo\",
                LpuPacsCompressionType_id as \"LpuPacsCompressionType_id\",
                LpuPacs_StudyAge as \"LpuPacs_StudyAge\",
                LpuPacs_Age_TimeType_id as \"LpuPacs_Age_TimeType_id\",
                LpuPacs_DeleteFromDb as \"LpuPacs_DeleteFromDb\",
                LpuPacs_DeleteFromHdd as \"LpuPacs_DeleteFromHdd\"
            FROM
                v_LpuPacs LP
            WHERE
                LP.LpuSection_id = :LpuSection_id
        ";
        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }
        return $result->result('array');
    }

    /**
     * @param array $data
     * @return bool|CI_DB_sqlsrv_result|void
     */
    public function saveLpuPacsData($data)
    {
        $params = [
            'LpuPacs_aetitle' => $data['LpuPacs_aetitle'],
            'LpuPacs_desc' => $data['LpuPacs_desc'],
            'LpuPacs_port' => $data['LpuPacs_port'],
            'LpuPacs_ip' => $data['LpuPacs_ip'],
            'LpuSection_id' => $data['LpuSection_id'],
            'LpuPacs_wadoPort' => $data['LpuPacs_wadoPort']
        ];
        if (!isset($data['LpuPacs_id'])) {
            $storedProc = 'ins';
            $params['LpuPacs_id'] = 0;
        } else {
            $storedProc = 'upd';
            $params['LpuPacs_id'] = $data['LpuPacs_id'];
        }
        $query = "
			select
			    LpuPacs_id as \"LpuPacs_id\", 
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_LpuPacs_{$storedProc}
			(
                LpuPacs_id := :LpuPacs_id,
                LpuSection_id := :LpuSection_id,
                LpuPacs_aetitle := :LpuPacs_aetitle,
                LpuPacs_desc := :LpuPacs_desc,
                LpuPacs_port := :LpuPacs_port,
                LpuPacs_wadoPort := :LpuPacs_wadoPort,
                LpuPacs_ip := :LpuPacs_ip,			
                pmUser_id := :pmUser_id
            )
		";
        
        
        $params['pmUser_id'] = $data['pmUser_id'];
        
        $result = $this->db->query($query, $params);
        return $result;
    }

    /**
     * @param array $data
     * @return bool|CI_DB_sqlsrv_result|void
     */
    public function deleteLpuPacsData($data)
    {
        $params = [
            'LpuPacs_id' => $data['LpuPacs_id']
        ];
        $query = "
			select 
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_LpuPacs_del
			(
			    LpuPacs_id := :LpuPacs_id,
			)
		";
        $result = $this->db->query($query, $params);
        return $result;
    }
}