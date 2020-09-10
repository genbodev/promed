<?php	defined('BASEPATH') or die ('No direct script access allowed');
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

class Okei_model extends CI_Model {

	/**
	 * Конструктор
	 */
	function __construct()
    {
        parent::__construct();
    }

	/**
	 * Возвращает список единиц измерения
	 */
    function loadOkeiGrid($data)
    {
        if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
        {
            return false;
        }

        $filters = '(1=1)';
        $params = array();
        if (isset($data['OkeiType_id']) && $data['OkeiType_id'])
        {
            $filters .= " and OT.OkeiType_id = :OkeiType_id";
            $params['OkeiType_id'] = $data['OkeiType_id'];
        }

        $query = "
			select
			  -- select
			  O.Okei_id,
			  O.Okei_Code,
			  O.Okei_NationSymbol,
			  O.Okei_Name,
			  OT.OkeiType_id,
			  OT.OkeiType_Name,
			  O.Okei_cid,
			  (SELECT Oc.Okei_Name FROM Okei Oc with(nolock) WHERE Oc.Okei_id=O.Okei_cid) AS Okei_cName,
			  (CASE WHEN Okei_cid > 0 THEN 0 ELSE 1 END) AS Okei_IsMain,
			  O.Okei_UnitConversion
			  -- end select
			from
			  -- from
			  v_Okei O with (nolock)
			  left join v_OkeiType OT with (nolock) on OT.OkeiType_id = O.OkeiType_id
			  -- end from
            where
              -- where
              {$filters}
              -- end where
            order by
              -- order by
              CAST(O.Okei_Code AS int)
              -- end order by
		";

        $result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']),$params);
        $result_count = $this->db->query(getCountSQLPH($query),$params);

        if (is_object($result_count))
        {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        }
        else
        {
            $count = 0;
        }
        if (is_object($result))
        {
            $response = array();
            $response['data'] = $result->result('array');
            $response['totalCount'] = $count;
            return $response;
        }
        return false;
    }

}