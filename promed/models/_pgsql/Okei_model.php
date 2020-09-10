<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Okei_model - модель для работы с единицами измерения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.10.2013
 */

class Okei_model extends SwPgModel
{

	/**
	 * Конструктор
	 */
	function __construct()
    {
        parent::__construct();
    }

    /**
     * Возвращает список единиц измерения
     * @param $data array
     * @return array|bool
     */
    function loadOkeiGrid($data)
    {
        if ( !($data['start'] >= 0 && $data['limit'] >= 0) )
        {
            return false;
        }

        $filters = '(1 = 1)';
        $params = [];
        if (isset($data['OkeiType_id']) && $data['OkeiType_id'])
        {
            $filters .= " and OT.OkeiType_id = :OkeiType_id";
            $params['OkeiType_id'] = $data['OkeiType_id'];
        }

        $query = "
			select
			  -- select
			  O.Okei_id as \"Okei_id\",
			  O.Okei_Code as \"Okei_Code\",
			  O.Okei_NationSymbol as \"Okei_NationSymbol\",
			  O.Okei_Name as \"Okei_Name\",
			  OT.OkeiType_id as \"OkeiType_id\",
			  OT.OkeiType_Name as \"OkeiType_Name\",
			  O.Okei_cid as \"Okei_cid\",
			  (select Oc.Okei_Name from Okei Oc  where Oc.Okei_id = O.Okei_cid) as \"Okei_cName\",
			  (case when Okei_cid > 0 then 0 else 1 end) as \"Okei_IsMain\",
			  O.Okei_UnitConversion as \"Okei_UnitConversion\"
			  -- end select
			from
			  -- from
			  v_Okei O
			  left join v_OkeiType OT on OT.OkeiType_id = O.OkeiType_id
			  -- end from
            where
              -- where
              {$filters}
              -- end where
            order by
              -- order by
              cast(O.Okei_Code as int)
              -- end order by
		";

        $result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']),$params);
        $result_count = $this->db->query(getCountSQLPH($query),$params);

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (is_object($result)) {
            $response = [];
            $response['data'] = $result->result('array');
            $response['totalCount'] = $count;
            return $response;
        }
        return false;
    }

}