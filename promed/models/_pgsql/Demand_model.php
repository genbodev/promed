<?php
class Demand_model extends SwPgModel {
	/**
	 * Comment
	 */
	function Usluga_model() {
		parent::__construct();
	}
	
	/*function loadAttachmentDemandListGrid($data) {
		return $this->loadDemandListGrid($data, "attachment");
	}
	
	function loadChangeSmoDemandListGrid($data) {
		return $this->loadDemandListGrid($data, "changesmo");
	}*/
	
	/**
	 * Comment
	 */
	function loadDemandListGrid($data, $demand_type) {
		$query = "";	
		$where = "";
		
		//print_r($data);
		
		if ($demand_type == "attachment") {
			if (isset($data['DemandState_id']) && $data['DemandState_id'] > 0) $where .= " AND d.DemandState_id = ".$data['DemandState_id'];
			if (isset($data['Start_Date']) && strlen($data['Start_Date']) > 0) $where .= " AND cast(d.Insert_Date as date) >= '".substr($data['Start_Date'], 0, strpos($data['Start_Date'],"T"))."'";
			if (isset($data['End_Date']) && strlen($data['End_Date']) > 0) $where .= " AND cast(d.Insert_Date as date) <= '".substr($data['End_Date'], 0, strpos($data['End_Date'],"T"))."'";
			if (isset($data['Person_Surname']) && strlen($data['Person_Surname']) > 0) $where .= " AND p.Person_Surname ILIKE '".$data['Person_Surname']."%'";
			if (isset($data['Person_Firname']) && strlen($data['Person_Firname']) > 0) $where .= " AND p.Person_Firname ILIKE '".$data['Person_Firname']."%'";
			if (isset($data['Person_Secname']) && strlen($data['Person_Secname']) > 0) $where .= " AND p.Person_Secname ILIKE '".$data['Person_Secname']."%'";
			if (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) $where .= " AND d.Lpu_id = ".$data['Lpu_id'];
			
			$query = "
				select
					-- select
					d.AttachmentDemand_id as \"AttachmentDemand_id\",
					d.DemandState_id as \"DemandState_id\",
					rtrim(p.Person_Surname) as \"Person_Surname\",
					rtrim(p.Person_Firname) as \"Person_Firname\",
					rtrim(p.Person_Secname) as \"Person_Secname\",
					l.Lpu_Name as \"Lpu_Name\",
					ds.DemandState_Name as \"DemandState_Name\",
					TO_CHAR (d.Insert_Date, 'dd.mm.yyyy') as \"Insert_Date\"
					-- end select
				from
					-- from
					AttachmentDemand d
					left join v_Person_ER p on p.Person_id = d.Person_Id
					left join v_Lpu l on l.Lpu_id = d.Lpu_Id
					left join DemandState ds on ds.DemandState_Id = d.DemandState_id
					-- end from
				where 
					-- where
					(1=1) ".$where." 
					-- end where
				order by 
					-- order by
					d.DemandState_id, d.Insert_Date DESC
					-- end order by
			";
			
			//print($query);
		}
		
		if ($demand_type == "changesmo") { 
			$query = "
				select
					-- select
					d.ChangeSmoDemand_id as \"ChangeSmoDemand_id\",
					rtrim(p.Person_Surname) as \"Person_Surname\",
					rtrim(p.Person_Firname) as \"Person_Firname\",
					rtrim(p.Person_Secname) as \"Person_Secname\",
					o.Org_Name as \"Org_Name\",
					ds.DemandState_Name as \"DemandState_Name\",
					cast(d.Insert_Date as varchar) as \"Insert_Date\"
					-- end select
				from
					-- from
					ChangeSmoDemand d
					left join v_Person_ER p on p.Person_id = d.Person_Id
					left join OrgSmo l on l.OrgSmo_id = d.Smo_Id
					left join Org o on o.Org_id = l.Org_id
					left join DemandState ds on ds.DemandState_Id = d.DemandState_id
					-- end from
				order by 
					-- order by
					d.DemandState_id, d.Insert_Date DESC
					-- end order by
			";
		}
		
		$queryParams = array();		
		$response = array();		
		
		$get_count_query = getCountSQLPH($query);
		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if ( is_object($get_count_result) ) {
			$response['data'] = array();
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		} else {
			return false;
		}

		$query = getLimitSQLPH($query, $data['start'], $data['limit']);
		$result = $this->db->query($query, $queryParams);		
		
		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		} else {
			return false;
		}

		return $response;
	}
	
	/**
	 * Comment
	 */
	function loadDemandEditForm($data, $demand_type) {		
		if ($demand_type == "attachment") {
			$query = "
				SELECT
					d.AttachmentDemand_Id as \"AttachmentDemand_id\",
					COALESCE(l.Lpu_Name, 'информация отсутствует') as \"currentLpu_Name\",
					COALESCE(dl.Lpu_Name, 'информация отсутствует') as \"Lpu_Name\",
					d.Person_Id as \"ADE_Person_id\",
					d.Server_Id as \"ADE_Server_id\",
					d.Lpu_Id as \"Lpu_id\",
					(CASE WHEN DemandState_id = 1 THEN 2 ELSE DemandState_id END) as \"DemandState_id\",
					RTRIM(d.State_Comment) as \"State_Comment\",
					d.Insert_Date as \"Insert_Date\",
					rtrim(d.Polis_Org) as \"Polis_Org\",
					rtrim(d.Polis_Ser) as \"Polis_Ser\",
					rtrim(d.Polis_Num) as \"Polis_Num\"
				FROM
					AttachmentDemand d
					left outer join v_Person_ER p on d.Person_Id = p.Person_id
					left outer join v_Lpu l on p.Lpu_id = l.Lpu_id
					left outer join v_Lpu dl on d.Lpu_id = dl.Lpu_id
				WHERE (1 = 1)
					AND AttachmentDemand_Id = :AttachmentDemand_id
                LIMIT 1";
					
			$result = $this->db->query($query, array('AttachmentDemand_id' => $data['AttachmentDemand_id']));
		}
					
		if ($demand_type == "changesmo") {
			$query = "
				SELECT
					ChangeSmoDemand_Id as \"ChangeSmoDemand_id\",
					Person_Id as \"CSDE_Person_id\",
					Server_Id as \"CSDE_Server_id\",
					Smo_Id as \"CSDE_Smo_id\",
					SmoUnit_Id as \"CSDE_SmoUnit_id\",
					Smo_Id as \"Smo_id\",
					SmoUnit_Id as \"SmoUnit_id\",
					(CASE WHEN DemandState_id = 1 THEN 2 ELSE DemandState_id END) as \"DemandState_id\",
					RTRIM(State_Comment) as \"State_Comment\",
					Insert_Date as \"Insert_Date\",
					Pasport_Inf as \"Pasport_Inf\",
					Pasport_TimeInf as \"Pasport_TimeInf\",
					Pasport_Ser as \"Pasport_Ser\",
					Pasport_Num as \"Pasport_Num\",
					rtrim(Polis_Org) as \"Polis_Org\",
					rtrim(Polis_Ser) as \"Polis_Ser\",
					rtrim(Polis_Num) as \"Polis_Num\"
				FROM 
					ChangeSmoDemand
				WHERE (1 = 1)
					AND ChangeSmoDemand_Id = :ChangeSmoDemand_id
                LIMIT 1";
			
			//print_r($data);
			$result = $this->db->query($query, array('ChangeSmoDemand_id' => $data['ChangeSmoDemand_id']));
		}

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Comment
	 */
	function saveDemand($data, $demand_type) {
		$this->db->trans_begin();
		
		$query = "";
		
		if (isset($data['action']) && (isset($data['AttachmentDemand_id']) || isset($data['ChangeSmoDemand_id'])) && $data['action'] == 'edit') {
			if ($demand_type == "attachment")
				$query = "
					UPDATE AttachmentDemand SET
						".(isset($data['DemandState_id']) && $data['DemandState_id'] != '' ? "DemandState_id = ".$data['DemandState_id'] : "")."
						".(isset($data['State_Comment']) && $data['State_Comment'] != '' ? ", State_Comment = '".$data['State_Comment']."'" : "")."
					WHERE
						AttachmentDemand_Id = ".$data['AttachmentDemand_id']."				
				";
				/*$query = "
					UPDATE AttachmentDemand SET
						".(isset($data['DemandState_id']) && $data['DemandState_id'] != '' ? "DemandState_id = ".$data['DemandState_id']."," : "")."
						".(isset($data['State_Comment']) && $data['State_Comment'] != '' ? "State_Comment = '".$data['State_Comment']."'," : "")."
						Lpu_Id = ".$data['Lpu_Id'].",
						Person_Id = ".$data['Person_id'].",
						Server_Id = ".$data['Server_id'].",
						Polis_Org = '".$data['Polis_Org']."',
						Polis_Ser = '".$data['Polis_Ser']."',
						Polis_Num = '".$data['Polis_Num']."'
					WHERE
						AttachmentDemand_Id = ".$data['AttachmentDemand_id']."				
				";*/
			if ($demand_type == "changesmo")
				$query = "
					UPDATE ChangeSmoDemand SET
						".(isset($data['DemandState_id']) && $data['DemandState_id'] != '' ? "DemandState_id = ".$data['DemandState_id']."," : "")."
						".(isset($data['State_Comment']) && $data['State_Comment'] != '' ? "State_Comment = '".$data['State_Comment']."'," : "")."
						Smo_Id = ".$data['Smo_Id'].",
						SmoUnit_Id = ".($data['SmoUnit_Id'] == "" ? "NULL" : $data['SmoUnit_Id']).",
						Person_Id = ".$data['Person_id'].",
						Server_Id = ".$data['Server_id'].",
						Pasport_Inf = '".$data['Pasport_Inf']."',
						Pasport_TimeInf = '".$data['Pasport_TimeInf']."',
						Pasport_Ser = '".$data['Pasport_Ser']."',
						Pasport_Num = '".$data['Pasport_Num']."',
						Polis_Org = '".$data['Polis_Org']."',
						Polis_Ser = '".$data['Polis_Ser']."',
						Polis_Num = '".$data['Polis_Num']."'
					WHERE
						ChangeSmoDemand_Id = ".$data['ChangeSmoDemand_id']."				
				";
		} 
		if (isset($data['action']) && $data['action'] == 'add') {
			if ($demand_type == "attachment")
				$query = "INSERT INTO AttachmentDemand  
					(DemandState_id,Insert_Date,Lpu_Id,Person_Id,Server_Id,Polis_Org,Polis_Ser,Polis_Num) VALUES 
					(1,dbo.tzGetDate(),".$data['Lpu_Id'].",".$data['Person_id'].",".$data['Server_id'].",'".$data['Polis_Org']."','".$data['Polis_Ser']."','".$data['Polis_Num']."')
				";
			if ($demand_type == "changesmo")
				$query = "INSERT INTO ChangeSmoDemand  
					(
						DemandState_id,Insert_Date,
						Smo_Id,SmoUnit_Id,
						Person_Id,Server_Id,
						Pasport_Inf,Pasport_TimeInf,Pasport_Ser,Pasport_Num,
						Polis_Org,Polis_Ser,Polis_Num
					) VALUES (
						1,dbo.tzGetDate(),
						".$data['Smo_Id'].",".$data['SmoUnit_Id'].",
						".$data['Person_id'].",".$data['Server_id'].",
						'".$data['Pasport_Inf']."','".$data['Pasport_TimeInf']."','".$data['Pasport_Ser']."','".$data['Pasport_Num']."',
						'".$data['Polis_Org']."','".$data['Polis_Ser']."','".$data['Polis_Num']."'
					)
				";
		}
				
		$result = $query != "" ? $this->db->query($query, array()) : false;		
		$this->db->trans_commit();
		
		return $result;
	}
	
	/**
	 * Comment
	 */
	function deleteDemand($data, $demand_type) {
		if ($demand_type == "attachment")
			$query = "delete from AttachmentDemand where DemandState_Id in (1,2,3,4,5,6) and AttachmentDemand_Id = ".$data['AttachmentDemand_id'];
		if ($demand_type == "changesmo")
			$query = "delete from ChangeSmoDemand where DemandState_Id in (1,2,3,4,5,6) and ChangeSmoDemand_Id = ".$data['ChangeSmoDemand_id'];
		$result = $this->db->query($query, array());
		return array(array('Error_Msg' => ''));
	}
	
	/**
	 * Comment
	 */
	function setDemandState($data) {
		$query = "UPDATE AttachmentDemand SET DemandState_id = ".$data['DemandState_id']." WHERE AttachmentDemand_Id = ".$data['AttachmentDemand_id'];
		$result = $this->db->query($query, array());
		return array(array('Error_Msg' => ''));
	}
	
	/**
	 * Comment
	 */
	function getDemandStateList() {
		$query = "
			SELECT
				DemandState_Id as \"DemandState_Id\",
				RTRIM(DemandState_Name) as \"DemandState_Name\"
			FROM
				DemandState
		";
		$res = $this->db->query($query, array());

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Comment
	 */
	function getOrgSmoList() {		
		$query = "
			SELECT 
			    os.OrgSMO_id as \"Smo_id\",
			    o.Org_Name as \"Smo_Name\"
			FROM OrgSMO os 
			left join Org o on o.Org_id = os.Org_id
			WHERE os.OrgSmo_endDate is NULL
			ORDER BY o.Org_Name			
		";
		$res = $this->db->query($query, array());

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Comment
	 */
	function getOrgSmoFilialList($data) {		
		$smoid = 0;
		if (isset($data['Org_id']) && $data['Org_id'] > 0 ) $smoid = $data['Org_id'];
		$query = "
			SELECT 
			    OrgSmoFilial_id as \"OrgSmoFilial_id\",
			    osf.OrgSmoFilial_Name as \"OrgSmoFilial_Name\"
			FROM OrgSMOFilial osf
			left join address a on osf.address_id= a.address_id
			left join KLStreet s on a.KLStreet_id = s.KLStreet_id
			where osf.OrgSmo_id = {$smoid} AND osf.OrgSmoFilial_endDate is NULL	
		";
		$res = $this->db->query($query, array());
			
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Comment
	 */
	function getCountNewDemand($data) {		
		$query = "
			SELECT count(*) as \"cnt\"
			FROM AttachmentDemand
			WHERE DemandState_id = 1 ".(isset($data['Lpu_id']) && $data['Lpu_id'] > 0 ? "AND Lpu_id=".$data['Lpu_id'] : "")."	
		";
		$res = $this->db->query($query, array());

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
}
?>