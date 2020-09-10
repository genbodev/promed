<?php

require_once(APPPATH.'models/Privilege_model.php');

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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonPrivilege_del
				@PersonPrivilege_id = :PersonPrivilege_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		return $this->db->query($query, array('PersonPrivilege_id' => $data['PersonPrivilege_id']));
	}
    /**
	 * getPrivilegeTypes
	 */
    function getPrivilegeTypes() {		
		$query = "
			SELECT * 
			from PrivilegeType
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
			SELECT pp.*, pt.PrivilegeType_Name 
			from PersonPrivilege pp
				join PrivilegeType pt on pp.PrivilegeType_id = pt.PrivilegeType_id
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
			SELECT * 
			from ReceptFinance
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonPrivilege_id ;
			exec " . $procedure . "
				@Server_id = :Server_id,
				@PersonPrivilege_id = @Res output,
				@Person_id = :Person_id,
				@PrivilegeType_id = :PrivilegeType_id,
				@Lpu_id = :Lpu_id,
				@PersonPrivilege_begDate =  :PersonPrivilege_begDate,
				@PersonPrivilege_endDate =  :PersonPrivilege_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output
                -- samara
                ,
                @Diag_id =   :Diag_id,
                @PersonPrivilege_Serie =    :PersonPrivilege_Serie,
                @PersonPrivilege_Number =   :PersonPrivilege_Number,
                @PersonPrivilege_IssuedBy = :PersonPrivilege_IssuedBy,
                @PersonPrivilege_Group =    :PersonPrivilege_Group
                -- 
                ;
			select @Res as PersonPrivilege_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			    PP.PersonPrivilege_id, 
			    PP.Person_id,
			    PP.PrivilegeType_id,
			    convert(varchar, cast(PP.PersonPrivilege_begDate as datetime),104) as PersonPrivilege_begDate,  
			    convert(varchar, cast(PP.PersonPrivilege_endDate as datetime),104) as PersonPrivilege_endDate,  
			    PP.Diag_id,
			    PP.PersonPrivilege_Serie,
			    PP.PersonPrivilege_Number,
			    PP.PersonPrivilege_IssuedBy,
			    PP.PersonPrivilege_Group
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

