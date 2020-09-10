<?php

require_once(APPPATH.'models/_pgsql/Privilege_model.php');

class Samara_Privilege_model extends Privilege_model {
    /**
	 * Samara_Privilege_model
	 */
	function __construct() {
		parent::__construct();
    }
    /**
	 * delete
	 */
	function delete($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonPrivilege_del(
				PersonPrivilege_id := :PersonPrivilege_id
			)
		";
		
		return $this->db->query($query, array('PersonPrivilege_id' => $data['PersonPrivilege_id']));
	}
    /**
	 * getPrivilegeTypes
	 */
    function getPrivilegeTypes() {		
		$query = "
			SELECT
				PrivilegeType_id as \"PrivilegeType_id\",
				PrivilegeType_Code as \"PrivilegeType_Code\",
				PrivilegeType_Name as \"PrivilegeType_Name\",
				PrivilegeType_Descr as \"PrivilegeType_Descr\",
				PrivilegeType_Med as \"PrivilegeType_Med\",
				ReceptDiscount_id as \"ReceptDiscount_id\",
				ReceptFinance_id as \"ReceptFinance_id\",
				PrivilegeType_SysNick as \"PrivilegeType_SysNick\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				PrivilegeType_insDT as \"PrivilegeType_insDT\",
				PrivilegeType_updDT as \"PrivilegeType_updDT\",
				Region_id as \"Region_id\",
				DrugFinance_id as \"DrugFinance_id\",
				PrivilegeType_begDate as \"PrivilegeType_begDate\",
				PrivilegeType_endDate as \"PrivilegeType_endDate\",
				PrivilegeType_VCode as \"PrivilegeType_VCode\",
				PrivilegeType_EGISSOid as \"PrivilegeType_EGISSOid\",
				PrivilegeType_IsNoz as \"PrivilegeType_IsNoz\",
				PrivilegeType_IsDoc as \"PrivilegeType_IsDoc\",
				WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
			from v_PrivilegeType
		";		
		$result = $this->db->query($query);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}	
	}
    /**
	 * getPersonPrivilegesList
	 */	
	function getPersonPrivilegesList($person_id) {		
		$query = "
			SELECT
				pp.PrivilegeType_id as \"PrivilegeType_id\",
				pp.PrivilegeType_Code as \"PrivilegeType_Code\",
				pp.PrivilegeType_Name as \"PrivilegeType_Name\",
				pp.PrivilegeType_Descr as \"PrivilegeType_Descr\",
				pp.PrivilegeType_Med as \"PrivilegeType_Med\",
				pp.ReceptDiscount_id as \"ReceptDiscount_id\",
				pp.ReceptFinance_id as \"ReceptFinance_id\",
				pp.PrivilegeType_SysNick as \"PrivilegeType_SysNick\",
				pp.pmUser_insID as \"pmUser_insID\",
				pp.pmUser_updID as \"pmUser_updID\",
				pp.PrivilegeType_insDT as \"PrivilegeType_insDT\",
				pp.PrivilegeType_updDT as \"PrivilegeType_updDT\",
				pp.Region_id as \"Region_id\",
				pp.DrugFinance_id as \"DrugFinance_id\",
				pp.PrivilegeType_begDate as \"PrivilegeType_begDate\",
				pp.PrivilegeType_endDate as \"PrivilegeType_endDate\",
				pp.PrivilegeType_VCode as \"PrivilegeType_VCode\",
				pp.PrivilegeType_EGISSOid as \"PrivilegeType_EGISSOid\",
				pp.PrivilegeType_IsNoz as \"PrivilegeType_IsNoz\",
				pp.PrivilegeType_IsDoc as \"PrivilegeType_IsDoc\",
				pp.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				pt.PrivilegeType_Name as \"PrivilegeType_Name\"
			from v_PersonPrivilege pp
				join v_PrivilegeType pt on pp.PrivilegeType_id = pt.PrivilegeType_id
			where Person_id = :Person_id
		";		
		$result = $this->db->query($query, array('Person_id' => $person_id));
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}	
	}
	/**
	 * getReceptFinances
	 */	
	function getReceptFinances() {		
		$query = "
			SELECT
				ReceptFinance_id as \"ReceptFinance_id\",
				ReceptFinance_Code as \"ReceptFinance_Code\",
				ReceptFinance_Name as \"ReceptFinance_Name\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				ReceptFinance_insDT as \"ReceptFinance_insDT\",
				ReceptFinance_updDT as \"ReceptFinance_updDT\",
				ReceptFinance_BegDate as \"ReceptFinance_BegDate\",
				ReceptFinance_EndDate as \"ReceptFinance_EndDate\",
				KLCountry_id as \"KLCountry_id\"
			from v_ReceptFinance
		";		
		$result = $this->db->query($query);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}	
	}
	/**
	 * save
	 */	
	function save($data)
	{		
        $procedure = "p_PersonPrivilege_ins";
		if ( isset($data['PersonPrivilege_id']) && $data['PersonPrivilege_id'] != 0 ) {
			$procedure = "p_PersonPrivilege_upd";
		}

		$query = "
			select
				PersonPrivilege_id as \"PersonPrivilege_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from " . $procedure . "(
				Server_id := :Server_id,
				PersonPrivilege_id := :PersonPrivilege_id,
				Person_id := :Person_id,
				PrivilegeType_id := :PrivilegeType_id,
				Lpu_id := :Lpu_id,
				PersonPrivilege_begDate := :PersonPrivilege_begDate,
				PersonPrivilege_endDate := :PersonPrivilege_endDate,
				pmUser_id := :pmUser_id,
                Diag_id := :Diag_id,
                PersonPrivilege_Serie := :PersonPrivilege_Serie,
                PersonPrivilege_Number := :PersonPrivilege_Number,
                PersonPrivilege_IssuedBy := :PersonPrivilege_IssuedBy,
                PersonPrivilege_Group := :PersonPrivilege_Group
            )
		";
		
		$params = array(
			'PersonPrivilege_id' => $data['PersonPrivilege_id'],
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'PrivilegeType_id' => $data['PrivilegeType_id'],
			'Lpu_id' => $data['Lpu_id'],
			'PersonPrivilege_begDate' => $data['PersonPrivilege_begDate'],
			'PersonPrivilege_endDate' => $data['PersonPrivilege_endDate'],				
			'pmUser_id' => $data['pmUser_id']
            // samara
            ,
            'Diag_id' => $data['Diag_id'],
			'PersonPrivilege_Serie' => $data['PersonPrivilege_Serie'],
			'PersonPrivilege_Number' => $data['PersonPrivilege_Number'],
			'PersonPrivilege_IssuedBy' => $data['PersonPrivilege_IssuedBy'],
			'PersonPrivilege_Group' => $data['PersonPrivilege_Group'],
            //
		);

		$result = $this->db->query($query, $data);
		
		return $result;
	}
	
	/**
	 * load
	 */	
	function load($data)
	{
		$query = "
			SELECT 
			    PP.PersonPrivilege_id, as \"PersonPrivilege_id\",
			    PP.Person_id as \"Person_id\",
			    PP.PrivilegeType_id as \"PrivilegeType_id\",
			    to_char(PP.PersonPrivilege_begDate, 'dd.mm.yyyy') as \"PersonPrivilege_begDate\",  
			    to_char(PP.PersonPrivilege_endDate, 'dd.mm.yyyy') as \"PersonPrivilege_endDate\",  
			    PP.Diag_id as \"Diag_id\",
			    PP.PersonPrivilege_Serie as \"PersonPrivilege_Serie\",
			    PP.PersonPrivilege_Number as \"PersonPrivilege_Number\",
			    PP.PersonPrivilege_IssuedBy as \"PersonPrivilege_IssuedBy\",
			    PP.PersonPrivilege_Group as \"PersonPrivilege_Group\"
			FROM
                PersonPrivilege PP
			WHERE
                PP.PersonPrivilege_id = ?
		";
	
        $result = $this->db->query($query, array($data['PersonPrivilege_id']));
        

		if (is_object($result)){
			return $result->result('array');
		}else{
			return false;
		}
	}

}
