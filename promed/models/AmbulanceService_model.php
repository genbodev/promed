<?php
defined('BASEPATH') or die ('No direct script access allowed');

class AmbulanceService_model extends CI_Model
{
	/**
	 * Comment
	 */
    function __construct()
    {
        parent::__construct();
    }
	
	/**
	 * Comment
	 */
	function getLpu($data) {
		$queryParams = array();
		$sql = "
			select
				Lpu_Ouz as lpuCode,
				Lpu_Name as lpuName,
				Lpu_Nick as lpuNick,
				convert(varchar(10), Lpu_begDate, 104)+' '+substring(convert(varchar,Lpu_updDT,108),1,5) as lpuBegDT,
				convert(varchar(10), Lpu_endDate, 104)+' '+substring(convert(varchar,Lpu_updDT,108),1,5) as lpuEndDT,
				LpuType_Code as lpuTypeCode,
				LpuType_Name as lpuTypeName,
				ls.LpuSubjectionLevel_Code as lpuSubjLevelCode,
				ls.LpuSubjectionLevel_Name as lpuSubjLevelName,
				convert(varchar(10), Lpu_updDT, 104)+' '+substring(convert(varchar,Lpu_updDT,108),1,5) as updDT,
				ua.KLRGN_Name as addressRgn,
				ua.KLSubRGN_Name as addressSubRgn,
				ua.KLCity_Name as addressCity,
				ua.KLTown_Name as addressTown,
				ua.Address_House as addressHome	
			from
				v_Lpu l with (nolock)
				left join LpuSubjectionLevel ls with (nolock) on l.LpuSubjectionLevel_id = ls.LpuSubjectionLevel_id
				left join v_Address_all ua with (nolock) on l.UAddress_id = ua.Address_id
			where
				(1=1)
		";
		
		if (isset($data['lpuCode'])) {
			$sql .= " and Lpu_Ouz = :lpuCode";
			$queryParams['lpuCode'] = $data['lpuCode'];
		}
		if (isset($data['updDT'])) {
			$sql .= " and Lpu_updDT >= :updDT";
			$queryParams['updDT'] = $data['updDT'];
		}
		
		$result = $this->db->query($sql, $queryParams);

		if (is_object($result)) {
			$val = array(
				'Error_Code' => 0,
				'Error_Msg' => '',
				'data' => $result->result('array')				
			);
		} else {
			$val = array(
				'Error_Code' => '',
				'Error_Msg' => 'Не найдено ни одного ЛПУ по заданным условиям.'
			);
		}
		
		return $val;
	}
	
	/**
	 * Comment
	 */
	function getPersonByFIOPolis($data) {
		$queryParams = array();
		$sql = "
			declare @Person_id bigint = null
			select @Person_id = dbo.getPersonByFIOPolis(:Person_SurName, :Person_FirName, :Person_SecName, :Person_BirthDay, :Polis_Ser, :Polis_Num)
			select Person_id, Person_SurName, Person_FirName, Person_SecName, cast(cast(Person_BirthDay as date) as varchar) as Person_BirthDay, 
			Person_EdNum, OrgSmo_Code, OrgSmo_Nick, OrgSmo_Name,
			Polis_Ser, Polis_Num, UAddress_Address, PAddress_Address from dbo.GetPersonFioPolisAddress(@Person_id)
		";
		
		$queryParams['Person_SurName'] = isset($data['Person_SurName']) ? $data['Person_SurName'] : null;
		$queryParams['Person_FirName'] = isset($data['Person_FirName']) ? $data['Person_FirName'] : null;
		$queryParams['Person_SecName'] = isset($data['Person_SecName']) ? $data['Person_SecName'] : null;
		$queryParams['Person_BirthDay'] = isset($data['Person_BirthDay']) ? $data['Person_BirthDay'] : null;
		$queryParams['Polis_Ser'] = isset($data['Polis_Ser']) ? $data['Polis_Ser'] : null;
		$queryParams['Polis_Num'] = isset($data['Polis_Num']) ? $data['Polis_Num'] : null;
		
		$result = $this->db->query($sql, $queryParams);

		if (is_object($result)) {
			$data = $result->result('array');
			if(isset($data[0]))
				$data = $data[0];
			if (isset($data['Person_id']) && $data['Person_id'] != null && $data['Person_id'] > 0) {
				$val = array(
					'success' => true,
					'identity' => (int)$data['Person_id'],
					'person_SurName' => (string)$data['Person_SurName'],
					'person_FirName' => (string)$data['Person_FirName'],
					'person_SecName' => (string)$data['Person_SecName'],
					'person_BirthDay' => (string)$data['Person_BirthDay'],
					'person_EdNum' => (string)$data['Person_EdNum'],
					'orgSmo_Code' => (string)$data['OrgSmo_Code'],
					'orgSmo_Nick' => (string)$data['OrgSmo_Nick'],
					'orgSmo_Name' => (string)$data['OrgSmo_Name'],
					'polis_Ser' => (string)$data['Polis_Ser'],
					'polis_Num' => (string)$data['Polis_Num'],
					'uAddress_Address' => (string)$data['UAddress_Address'],
					'pAddress_Address' => (string)$data['PAddress_Address'],
					'Error_Code' => 0,
					'Error_Msg' => ''
				);
			} else {
				$val = array(
					'success' => false,
					'identity' => 0,
					'Error_Code' => '',
					'Error_Msg' => 'Пациент не идентифицирован.'
				);
			}
		} else {
			$val = array(
				'success' => false,
				'identity' => 0,
				'Error_Code' => '',
				'Error_Msg' => 'Пациент не идентифицирован.'
			);
		}
		
		return array($val);
	}

	/**
	 * Comment
	 */
	function getPersonByPolis($data) {
		$queryParams = array();
		$sql = "
			declare @Person_id bigint
			select @Person_id = dbo.getPersonByPolis(:Polis_Ser, :Polis_Num)
			select Person_id, Person_SurName, Person_FirName, Person_SecName, cast(cast(Person_BirthDay as date) as varchar) as Person_BirthDay, 
			Person_EdNum, OrgSmo_Code, OrgSmo_Nick, OrgSmo_Name,
			Polis_Ser, Polis_Num, UAddress_Address, PAddress_Address from dbo.GetPersonFioPolisAddress(@Person_id)
		";
		$queryParams['Polis_Ser'] = $data['Polis_Ser'];
		$queryParams['Polis_Num'] = $data['Polis_Num'];

		$result = $this->db->query($sql, $queryParams);

		$val = array(
			'Error_Code' => '',
			'Error_Msg' => 'Пациент не идентифицирован.'
		);
		
		if (is_object($result)) {
			$data = $result->result('array');
			if (count($data)>0) {
				$data = $data[0];
			}
			if (isset($data['Person_id']) && $data['Person_id'] != null && $data['Person_id'] > 0) {
				$val = array(
					'success' => true,
					'identity' => (int)$data['Person_id'],
					'person_SurName' => (string)$data['Person_SurName'],
					'person_FirName' => (string)$data['Person_FirName'],
					'person_SecName' => (string)$data['Person_SecName'],
					'person_BirthDay' => (string)$data['Person_BirthDay'],
					'person_EdNum' => (string)$data['Person_EdNum'],
					'orgSmo_Code' => (string)$data['OrgSmo_Code'],
					'orgSmo_Nick' => (string)$data['OrgSmo_Nick'],
					'orgSmo_Name' => (string)$data['OrgSmo_Name'],
					'polis_Ser' => (string)$data['Polis_Ser'],
					'polis_Num' => (string)$data['Polis_Num'],
					'uAddress_Address' => (string)$data['UAddress_Address'],
					'pAddress_Address' => (string)$data['PAddress_Address'],					
					'Error_Code' => 0,
					'Error_Msg' => ''
				);
			}
		}
		return array($val);
	}

	/**
	 * Comment
	 */
	function getPersonByAddress($data) {
		$queryParams = array();
		$sql = "
			declare @Person_id bigint
			select @Person_id = dbo.GetPersonByAddress(:Person_SurName, :Person_FirName, :Person_Age, :KLStreet_Name, :Address_House, :Address_Flat)
			select Person_id, Person_SurName, Person_FirName, Person_SecName, cast(cast(Person_BirthDay as date) as varchar) as Person_BirthDay, 
			Person_EdNum, OrgSmo_Code, OrgSmo_Nick, OrgSmo_Name,
			Polis_Ser, Polis_Num, UAddress_Address, PAddress_Address from dbo.GetPersonFioPolisAddress(@Person_id)
		
		";
		$result = $this->db->query($sql, $data);

		$val = array(
			'success' => false,
			'identity' => 0,
			'Error_Code' => '',
			'Error_Msg' => 'Пациент не идентифицирован.'
		);
		if (is_object($result)) {
			$data = $result->result('array');
			if (count($data)>0) {
				$data = $data[0];
			}			
			if (isset($data['Person_id']) && $data['Person_id'] != null && $data['Person_id'] > 0) {
				$val = array(
					'success' => true,
					'identity' => (int)$data['Person_id'],
					'person_SurName' => (string)$data['Person_SurName'],
					'person_FirName' => (string)$data['Person_FirName'],
					'person_SecName' => (string)$data['Person_SecName'],
					'person_BirthDay' => (string)$data['Person_BirthDay'],
					'person_EdNum' => (string)$data['Person_EdNum'],
					'orgSmo_Code' => (string)$data['OrgSmo_Code'],
					'orgSmo_Nick' => (string)$data['OrgSmo_Nick'],
					'orgSmo_Name' => (string)$data['OrgSmo_Name'],
					'polis_Ser' => (string)$data['Polis_Ser'],
					'polis_Num' => (string)$data['Polis_Num'],
					'uAddress_Address' => (string)$data['UAddress_Address'],
					'pAddress_Address' => (string)$data['PAddress_Address'],					
					'Error_Code' => 0,
					'Error_Msg' => ''
				);
			}

		}
		return array($val);
	}

	/**
	 * Comment
	 */
	function getPolisByPerson($data) {
		$queryParams = array();
		$sql = "
			select
		  pl.Polis_id,
		  pl.Polis_Ser,
		  pl.Polis_Num,
		  smo.OrgSmo_Nick
		  from v_PersonState ps with (nolock)
		  left join v_Polis pl with (nolock) on ps.Polis_id = pl.Polis_id
		  left join v_OrgSmo smo with (nolock) on pl.OrgSmo_id = smo.OrgSmo_id
		  where ps.Person_id = :Person_id
		";
		
		$queryParams['Person_id'] = isset($data['Person_id']) ? $data['Person_id'] : null;

		$result = $this->db->query($sql, $queryParams);
		
		$val = array(
			'success' => false,
			'identity' => 0,
			'Error_Code' => '',
			'Error_Msg' => 'Полис не найден'
		);
		
		if (is_object($result)) {
			$res = $result->result('array');
			if (count($res)>0) {
				if ($res[0]['Polis_id']>0) {					
					$val = array(
						'success' => true,
						'identity' => (int)$res[0]['Polis_id'],
						'Error_Code' => 0,
						'Error_Msg' => '',
					  'polis_Ser' => (string)$res[0]['Polis_Ser'],
					  'polis_Num' => (string)$res[0]['Polis_Num'],
					  'smo_Name' => (string)$res[0]['OrgSmo_Nick']
					);
				}				
			}
		}
			
		return array($val);	
	}
	
	
	/**
	 * Comment
	 */
	function getProfileList($data) {
		$queryParams = array();
		$sql = "
			select
				LpuSectionProfile_OMSCode,
				LpuSectionProfile_Name
			from LpuSectionProfile with (nolock)
			where
				LpuSectionProfile_IsEmergencyDir = 2
		";
		$result = $this->db->query($sql);
		$val = array(
			'success' => false,
			'identity' => 0,
			'Error_Code' => '',
			'Error_Msg' => 'Ошибка при получении списка профилей'
		);
		if (is_object($result)) {
			$res = $result->result('array');
			if (count($res)>0) {
				$r = array();
				foreach ($res as $row) {
					if (is_array($row))
						array_walk($row, 'ConvertFromWin1251ToUTF8');
					$r[] = $row;
				}
				$val = array(
					'success' => true,
					'Error_Code' => 0,
					'Error_Msg' => '',
					'data' => $r
				);
			} else {
				$val = array(
					'success' => true,
					'Error_Code' => 0,
					'Error_Msg' => '',
					'data' => array()
				);
			}
		}
		return array($val);
	}

	/**
	 * Comment
	 */
	function getStacList($data) {
		$sql = "
			Select
				Lpu.Lpu_id,
				Lpu.Lpu_Name,
				Lpu.Lpu_Nick,
				LS.LpuSection_id, 
				LpuSection_Name,
				Addr.Address_Address,
				COUNT(v_TimetableStac_lite.TimetableStac_id) as EmergencyBedTotal,
				COUNT(case when (v_TimetableStac_lite.Person_id is null) then v_TimetableStac_lite.TimetableStac_id else Null end) as EmergencyBedFree
			from v_LpuSection LS with (nolock)
			left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
			left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = LB.Lpu_id
			left join v_Address Addr with (nolock) on Addr.Address_id = LB.Address_id
			left join v_TimetableStac_lite with (nolock) on TimetableType_id = 6 and LS.LpuSection_id = v_TimetableStac_lite.LpuSection_id
			where
				LU.LpuUnitType_id = 1
				and TimetableStac_setDate = cast(dbo.tzGetDate() as DATE)
				and isnull(TimetableType_id, 1) = 6
				and LS.LpuSectionProfile_OMSCode = :LpuSectionProfile_Code
				and isnull(LS.LpuSectionHospType_id, 1) in (6, 7)
			group by
				Lpu.Lpu_id,
				Lpu.Lpu_Name,
				Lpu.Lpu_Nick,
				LS.LpuSection_id,
				LpuSection_Name,
				Addr.Address_Address
		";
		$result = $this->db->query($sql, $data);
		
		$val = array(
			'success' => false,
			'identity' => 0,
			'Error_Code' => '',
			'Error_Msg' => 'Ошибка при получении списка стационаров'
		);
		//echo getDebugSql($sql, $data); exit;
		
		if (is_object($result)) {
			$res = $result->result('array');
			if (count($res)>0) {
				$r = array();
				foreach ($res as $row) {
					if (is_array($row))
						array_walk($row, 'ConvertFromWin1251ToUTF8');
					$r[] = $row;
				}
				$val = array(
					'success' => true,
					'Error_Code' => 0,
					'Error_Msg' => '',
					'data' => $r
				);
			} else {
				$val = array(
					'success' => true,
					'Error_Code' => 0,
					'Error_Msg' => '',
					'data' => array()
				);
			}
		}
		return array($val);
	}
	
	/**
	 * Comment
	 */
	function saveEmergencyData($data) {
		if (!isset($data['Diag_id'])) {
			$data['Diag_id'] = null;
		}
		$sql = "
			Declare @Res_id bigint;
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec p_EmergencyData_ins
				@Res_id output,
				@TimetableStac_id = :TimetableStac_id,
				@Person_id = :Person_id,
				@EmergencyData_BrigadeNum = :EmergencyData_BrigadeNum,
				@EmergencyData_CallNum = :EmergencyData_CallNum,
				@Diag_id = :Diag_id,
				@pmUser_id = :pmUser_id, 
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res_id as EmergencyData_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
		";
		/*
		echo getDebugSql($sql, $data);
		exit;
		*/
		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Comment
	 */
	function getEmergencyData($data) {
		$sql = "
			Select top 1 EmergencyData_id 
				from EmergencyData with (nolock) 
				where EmergencyData_BrigadeNum = :EmergencyData_BrigadeNum
				and EmergencyData_CallNum = :EmergencyData_CallNum
		";
		/*
		echo getDebugSql($sql, $data);
		exit;
		*/
		$result = $this->db->query($sql, $data);
		
		if ( is_object($result) )
		{
			$r = $result->result('array');
			if (count($r)>0) {
				return $r[0]['EmergencyData_id'];
			}
			else {
				return null;
			}
		}
		else
		{
			return null;
		}
	}
}
?>
