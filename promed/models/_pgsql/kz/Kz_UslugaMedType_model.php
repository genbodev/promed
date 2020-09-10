<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH . 'models/_pgsql/UslugaMedType_model.php');

/**
 * Kz_UslugaMedType_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Common
 * @access            public
 * @copyright        Copyright (c) 2019 Swan Ltd.

 *
 */
class Kz_UslugaMedType_model extends UslugaMedType_model
{

    /**
     * Kz_UslugaMedType_model constructor.
     */
    public function __construct()
    {
        parent::__construct();

        //$this->load->library('textlog', ['file' => 'Kz_UslugaMedType_' . date('Y-m-d') . '.log']);
    }


    /**
     * @param array $data
     * @return array
     */
    public function saveUslugaMedTypeLink($data)
    {
        $query = $this->queryResult("SELECT UslugaMedTypeLink_id  as \"UslugaMedTypeLink_id\" FROM r101.UslugaMedTypeLink  WHERE Evn_id=:Evn_id", [
            'Evn_id' => $data['Evn_id'],
        ]);

        foreach ($query as $v) {
            $query_delete = $this->queryResult("
                SELECT Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                FROM r101.p_UslugaMedTypeLink_del(
                    UslugaMedTypeLink_id := :UslugaMedTypeLink_id);
            ", [
                'UslugaMedTypeLink_id' => $v['UslugaMedTypeLink_id']
            ]);

            if (!empty($query_delete[0]['Error_Msg'])) {
                if (!is_array($query_delete)) {
                    return $this->createError('', 'Ошибка при удалении вида услуги');
                }
            }
        }

        $data['UslugaMedType_id'] = $this->getUslugaMedTypeIdValue($data);

        $sql = "
            SELECT 
            	UslugaMedTypeLink_id as \"UslugaMedTypeLink_id\", 
            	Error_Code as \"Error_Code\", 
            	Error_Message as \"Error_Msg\"                
            FROM r101.p_UslugaMedTypeLink_ins(
                UslugaMedTypeLink_id := null,
                UslugaMedType_id := :UslugaMedType_id,
                Evn_id := :Evn_id,
                pmUser_id := :pmUser_id
            );
        ";

        $response = $this->queryResult($sql, [
            'UslugaMedType_id' => $data['UslugaMedType_id'],
            'Evn_id' => $data['Evn_id'],
            'pmUser_id' => $data['pmUser_id'],
        ]);
        if (!is_array($response)) {
            return $this->createError('', 'Ошибка при сохранении вида услуги');
        }

        return $response;
    }

    /**
     * @param int $Evn_id
     * @return int|null
     */
    public function getUslugaMedTypeIdByEvnId($Evn_id)
    {
        $sql = "SELECT UslugaMedType_id  as \"UslugaMedType_id\" FROM r101.v_UslugaMedTypeLink  WHERE Evn_id=:Evn_id";

        $UslugaMedType_id = $this->getFirstResultFromQuery($sql, ['Evn_id' => $Evn_id]);

        return $UslugaMedType_id ? (int)$UslugaMedType_id : null;
    }

    /**
     * @param array $data
     * @param int $default
     * @return int
     * @throws Exception
     */
    private function getUslugaMedTypeIdValue($data, $default=1400)
    {
        if (empty($data['UslugaMedType_id'])) {
            $sql = "SELECT UslugaMedType_id  as \"UslugaMedType_id\" FROM r101.v_UslugaMedType  WHERE UslugaMedType_Code=:UslugaMedType_Code";

            $data['UslugaMedType_id'] = $this->getFirstResultFromQuery($sql, ['UslugaMedType_Code' => $default]);
            if (!$data['UslugaMedType_id']) {
                throw new Exception('Unable to find UslugaMedType_id for UslugaMedType_Code=' . $default . '.');
            }
        }

        return (int)$data['UslugaMedType_id'];
    }
}