<?php
/**
 * MorbusHepatitisDiag_model - модель, для работы с таблицей MorbusHepatitisDiag
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Chebukin
 * @version      07.2012
 * @property-read CI_DB_driver $db
 */
class MorbusHepatitisDiag_model extends SwPgModel
{
    protected $dateTimeForm104 = "'dd.mm.yyyy'";

    /**
     * @param $data
     * @return bool|mixed
     */
    public function load($data)
    {
        $query = "
			select 
				MorbusHepatitisDiag_id as \"MorbusHepatitisDiag_id\",
				MorbusHepatitis_id as \"MorbusHepatitis_id\",
				Evn_id as \"EvnSection_id\",
				to_char(cast(MorbusHepatitisDiag_setDT as timestamp ), {$this->dateTimeForm104}) as \"MorbusHepatitisDiag_setDT\",
				MedPersonal_id as \"MedPersonal_id\",
				HepatitisDiagType_id as \"HepatitisDiagType_id\",
				to_char(cast(MorbusHepatitisDiag_ConfirmDT as timestamp ), {$this->dateTimeForm104}) as \"MorbusHepatitisDiag_ConfirmDT\",
				HepatitisDiagActiveType_id as \"HepatitisDiagActiveType_id\",
				HepatitisFibrosisType_id as \"HepatitisFibrosisType_id\"
			from
				v_MorbusHepatitisDiag
			where
				MorbusHepatitisDiag_id = :MorbusHepatitisDiag_id
		";
        $res = $this->db->query($query, ['MorbusHepatitisDiag_id' => $data['MorbusHepatitisDiag_id']]);
        if (!is_object($res)) {
            return false;
        }
        
        return $res->result('array');
    }

    /**
     * @param $data
     * @return bool|mixed
     */
    public function save($data)
    {

        $action = isset($data['MorbusHepatitisDiag_id']) ? 'upd' : 'ins';

        $query = "
		    select
		        MorbusHepatitisDiag_id as \"MorbusHepatitisDiag_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from p_MorbusHepatitisDiag_{$action}
			(
				MorbusHepatitisDiag_id := :MorbusHepatitisDiag_id,
				MorbusHepatitis_id := :MorbusHepatitis_id,
				MorbusHepatitisDiag_setDT := :MorbusHepatitisDiag_setDT,
				MedPersonal_id := :MedPersonal_id,				
				HepatitisDiagType_id := :HepatitisDiagType_id,
				MorbusHepatitisDiag_ConfirmDT := :MorbusHepatitisDiag_ConfirmDT,
				HepatitisDiagActiveType_id := :HepatitisDiagActiveType_id,
				HepatitisFibrosisType_id := :HepatitisFibrosisType_id,	
				Evn_id := :Evn_id,			
				pmUser_id := :pmUser_id
			)
		";

        $queryParams = [
            'MorbusHepatitisDiag_id' => $data['MorbusHepatitisDiag_id'],
            'MorbusHepatitis_id' => $data['MorbusHepatitis_id'],
            'MorbusHepatitisDiag_setDT' => $data['MorbusHepatitisDiag_setDT'],
            'MedPersonal_id' => $data['MedPersonal_id'],
            'HepatitisDiagType_id' => $data['HepatitisDiagType_id'],
            'MorbusHepatitisDiag_ConfirmDT' => $data['MorbusHepatitisDiag_ConfirmDT'],
            'HepatitisDiagActiveType_id' => $data['HepatitisDiagActiveType_id'],
            'HepatitisFibrosisType_id' => $data['HepatitisFibrosisType_id'],
            'Evn_id' => $data['EvnSection_id'],
            'pmUser_id' => $data['pmUser_id']
        ];

        $res = $this->db->query($query, $queryParams);

        if (!is_object($res)) {
            return false;
        }
        
        return $response = $res->result('array');
    }


}