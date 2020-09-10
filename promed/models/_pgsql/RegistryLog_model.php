<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * Класс для логгирования операций с реестрами
 *
 * @package				CostPrint
 * @copyright			Copyright (c) 2014 Swan Ltd.
 * @author				Dmitriy Vlasenko
 * @link				http://swan.perm.ru/PromedWeb
 */
class RegistryLog_model extends SwPgModel
{
    /**
     * Запись данных
     * @param $data array
     * @return array|false
     */
	function saveRegistryLog($data) {
	    $proc = "p_RegistryLog_". empty($data['RegistryLog_id']) ? "ins" : "upd";

		$RegistryLog_begDate = ':RegistryLog_begDate';
		if (!empty($data['RegistryLog_begDate']) && $data['RegistryLog_begDate'] == '@curDate') {
			$RegistryLog_begDate = '(select curDate from cte)';
		}

		$RegistryLog_endDate = ':RegistryLog_endDate';
		if (!empty($data['RegistryLog_endDate']) && $data['RegistryLog_endDate'] == '@curDate') {
			$RegistryLog_endDate = '(select curDate from cte)';
		}

		$query = "
		    with cte as (
		        select dbo.tzGetDate() as curDate
		    )
		    
            select
                to_char((select curDate from cte), 'DD.MM.YYYY HH24:MI:SS.MS') as \"curDate\",
                RegistryLog_id as \"RegistryLog_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"		    
			from {$proc}
			(
				RegistryLog_id := :RegistryLog_id,
				Registry_id := :Registry_id,
				RegistryLog_begDate := {$RegistryLog_begDate},
				RegistryLog_endDate := {$RegistryLog_endDate},
				RegistryActionType_id := :RegistryActionType_id,
				RegistryErrorTFOMSType_id := :RegistryErrorTFOMSType_id,
				RegistryLog_CountEvn := :RegistryLog_CountEvn,
				RegistryLog_CountEvnErr := :RegistryLog_CountEvnErr,
				pmUser_id := :pmUser_id
            )
		";

        return $this->queryResult($query, [
            'RegistryLog_id' => !empty($data['RegistryLog_id']) ? $data['RegistryLog_id'] : null,
            'Registry_id' => $data['Registry_id'],
            'RegistryLog_begDate' => !empty($data['RegistryLog_begDate']) ? $data['RegistryLog_begDate'] : null,
            'RegistryLog_endDate' => !empty($data['RegistryLog_endDate']) ? $data['RegistryLog_endDate'] : null,
            'RegistryActionType_id' => !empty($data['RegistryActionType_id']) ? $data['RegistryActionType_id'] : null,
            'RegistryErrorTFOMSType_id' => !empty($data['RegistryErrorTFOMSType_id']) ? $data['RegistryErrorTFOMSType_id'] : null,
            'RegistryLog_CountEvn' => !empty($data['RegistryLog_CountEvn']) ? $data['RegistryLog_CountEvn'] : null,
            'RegistryLog_CountEvnErr' => !empty($data['RegistryLog_CountEvnErr']) ? $data['RegistryLog_CountEvnErr'] : null,
            'pmUser_id' => $data['pmUser_id']
        ]);
	}
}