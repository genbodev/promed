<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * OkeiType_model - модель для работы с мерами измерения
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

class OkeiType_model extends swPgModel {

	/**
	 * Конструктор
	 */
	function __construct()
    {
        parent::__construct();
    }

	/**
	 * Возвращает список мер измерения
	 */
    function loadOkeiTypeGrid($data)
    {
        $query = "
			select 
			    OT.OkeiType_id as \"OkeiType_id\",
                OT.OkeiType_Code as \"OkeiType_Code\",
                OT.OkeiType_Name as \"OkeiType_Name\"
            from v_OkeiType OT
		";
        $res = $this->db->query($query);

        if ( is_object($res) ) {
            return $res->result('array');
        }
        else {
            return false;
        }
    }
}