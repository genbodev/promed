<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для поручений
 *
 * @package      BSME
 * @package
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 *
 * @property Evn_model $Evn_model
 */
class EvnDirectionForensic_model extends SwPgModel
{

    /**
     * Получение номера поручения
     * @param array $data
     * @return array|bool
     */
    public function getNextNumber($data)
    {
        $response = [
            'EvnDirectionForensic_Num' => 1,
            'Error_Msg' => ''
        ];
        $query = '
			select 
				coalesce(MAX(EDF.EvnDirectionForensic_Num), 0) + 1 as "EvnDirectionForensic_Num"
			FROM 
				v_EvnDirectionForensic EDF
			WHERE
				date_part(\'year\', EvnDirectionForensic_insDT) = date_part(\'year\', dbo.tzGetDate()) -- за текущий год
		';

        $result = $this->db->query($query);
        if (!is_object($result))
            return false;

        $resp = $result->result('array');
        if (!empty($resp[0]['EvnDirectionForensic_Num'])) {
            $response['EvnDirectionForensic_Num'] = $resp[0]['EvnDirectionForensic_Num'];
        }
        return $response;
    }

    /**
     * Сохранение поручения
     *
     * @param $data
     * @return bool
     */
    public function saveEvnDirectionForensic($data)
    {
        $procedure = "p_EvnDirectionForensic_" . empty($data['EvnDirectionForensic_id']) ? 'ins' : 'upd';

        $sql = "
		    select
		        EvnDirectionForensic_id as \"EvnDirectionForensic_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from " . $procedure . "
		    (
		        EvnDirectionForensic_id := :EvnDirectionForensic_id,
				Lpu_id := :Lpu_id,
				EvnDirectionForensic_Num := :EvnDirectionForensic_Num,
				EvnDirectionForensic_begDate := :EvnDirectionForensic_begDate,
				EvnDirectionForensic_endDate := :EvnDirectionForensic_endDate,
				EvnForensicType_id := :EvnForensicType_id,
				MedPersonal_id := :MedPersonal_id,
				EvnForensic_id := :EvnForensic_id,
				pmUser_id := :pmUser_id
		    )
		";

        $query = $this->db->query($sql, $data);
        if (!is_object($query))
            return false;


        $result = $query->result_array();

        // Изменяем статус заявки на Назначенные
        $this->load->model('Evn_model');
        $params = [
            'Evn_id' => $data['EvnForensic_id'],
            'EvnStatus_SysNick' => 'Appoint',
            'EvnClass_SysNick' => 'EvnForensic',
            'pmUser_id' => $data['pmUser_id']
        ];

        $this->Evn_model->updateEvnStatus($params);
        return $result;
    }
}
