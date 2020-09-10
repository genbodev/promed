<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* DispAppoint_model - модель для работы с записями в 'Назначение'
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      11.2016
*/

class DispAppoint_model extends SwPgModel
{
	/**
	 * DispAppoint_model constructor.
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function loadDispAppointGrid($data)
	{
		$filter = " and DA.EvnPLDisp_id = :EvnPLDisp_id";
		
		if (!empty($data['DispAppoint_id'])) {
			$filter = " and DA.DispAppoint_id = :DispAppoint_id";
		}
		
		$query = "
			select 
				DA.DispAppoint_id as \"DispAppoint_id\",
				DA.EvnPLDisp_id as \"EvnPLDisp_id\",
				DA.DispAppointType_id as \"DispAppointType_id\",
				DA.MedSpecOms_id as \"MedSpecOms_id\",
				DA.ExaminationType_id as \"ExaminationType_id\",
				DA.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				DA.LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
				DAT.DispAppointType_Name as \"DispAppointType_Name\",
				case
					when DA.DispAppointType_id IN (1,2) then MSO.MedSpecOms_Name
					when DA.DispAppointType_id = 3 then ET.ExaminationType_Name
					when DA.DispAppointType_id IN (4,5) then LSP.LpuSectionProfile_Name
					when DA.DispAppointType_id = 6 then LSBP.LpuSectionBedProfile_Name
				end as \"DispAppoint_Comment\",
				1 as \"RecordStatus_Code\"
			from
				v_DispAppoint DA 
				left join v_DispAppointType DAT  on DAT.DispAppointType_id = DA.DispAppointType_id
				left join v_MedSpecOms MSO  on MSO.MedSpecOms_id = DA.MedSpecOms_id
				left join v_ExaminationType ET  on ET.ExaminationType_id = DA.ExaminationType_id
				left join fed.v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = DA.LpuSectionProfile_id
				left join fed.v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = DA.LpuSectionBedProfile_id
			where
				(1=1) {$filter}
			order by
				DA.DispAppoint_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для ЭМК
	 */
	function getDispAppointViewData($data) {
		if (empty($data['DispAppoint_pid'])) {
			return array();
		}

		$filter = "";
		$queryParams = array(
			'EvnPLDisp_id' => $data['DispAppoint_pid']
		);

		$query = "
			select
				DA.DispAppoint_id as \"DispAppoint_id\",
				DA.EvnPLDisp_id as \"EvnPLDisp_id\",
				DAT.DispAppointType_Name as \"DispAppointType_Name\",
				case
					when DA.DispAppointType_id IN (1,2) then MSO.MedSpecOms_Name
					when DA.DispAppointType_id = 3 then ET.ExaminationType_Name
					when DA.DispAppointType_id IN (4,5) then LSP.LpuSectionProfile_Name
					when DA.DispAppointType_id = 6 then LSBP.LpuSectionBedProfile_Name
				end as \"DispAppoint_Comment\"
			from
				v_DispAppoint DA 
				left join v_DispAppointType DAT  on DAT.DispAppointType_id = DA.DispAppointType_id
				left join v_MedSpecOms MSO  on MSO.MedSpecOms_id = DA.MedSpecOms_id
				left join v_ExaminationType ET  on ET.ExaminationType_id = DA.ExaminationType_id
				left join fed.v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = DA.LpuSectionProfile_id
				left join fed.v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = DA.LpuSectionBedProfile_id
			where
				DA.EvnPLDisp_id = :EvnPLDisp_id
				{$filter}
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function checkDispAppointExists($data) {
		$query = "
			select 
				DispAppoint_id as \"DispAppoint_id\"
			from
				v_DispAppoint 
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and DispAppointType_id = :DispAppointType_id
				and COALESCE(MedSpecOms_id, 0) = COALESCE(CAST(:MedSpecOms_id as bigint), 0)
				and COALESCE(ExaminationType_id, 0) = COALESCE(CAST(:ExaminationType_id as bigint), 0)
				and COALESCE(LpuSectionProfile_id, 0) = COALESCE(CAST(:LpuSectionProfile_id as bigint), 0)
				and COALESCE(LpuSectionBedProfile_id, 0) = COALESCE(CAST(:LpuSectionBedProfile_id as bigint), 0)
				and DispAppoint_id <> COALESCE(CAST(:DispAppoint_id as bigint),0)
            limit 1
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
		function deleteDispAppoint($data) {

            $sql = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\"
        from p_DispAppoint_del
            (
 			    DispAppoint_id := :DispAppoint_id
            )";


		$res = $this->db->query($sql, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function saveDispAppoint($data) {
		if (!empty($data['DispAppoint_id']) && $data['DispAppoint_id'] > 0) {
			$proc = "p_DispAppoint_upd";
		} else {
			$proc = "p_DispAppoint_ins";
		}

		if ( (empty($data['mode']) || $data['mode'] != 'local') && !$this->checkDispAppointExists($data) ) {
			return array(array('Error_Msg' => 'Обнаружено дублирование назначений, сохранение невозможно.'));
		}
		
 
        $sql = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            DispAppoint_id as \"DispAppoint_id\"
        from {$proc}
            (
				DispAppoint_id := :DispAppoint_id,
				EvnPLDisp_id := :EvnPLDisp_id,
				DispAppointType_id := :DispAppointType_id,
				MedSpecOms_id := :MedSpecOms_id,
				ExaminationType_id := :ExaminationType_id,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				LpuSectionBedProfile_id := :LpuSectionBedProfile_id,
				pmUser_id := :pmUser_id          
        )";


		$queryParams = array(
			'DispAppoint_id' => (!empty($data['DispAppoint_id']) && $data['DispAppoint_id'] > 0 ? $data['DispAppoint_id'] : null),
			'EvnPLDisp_id' => $data['EvnPLDisp_id'],
			'DispAppointType_id' => $data['DispAppointType_id'],
			'MedSpecOms_id' => (!empty($data['MedSpecOms_id']) ? $data['MedSpecOms_id'] : null),
			'ExaminationType_id' => (!empty($data['ExaminationType_id']) ? $data['ExaminationType_id'] : null),
			'LpuSectionProfile_id' => (!empty($data['LpuSectionProfile_id']) ? $data['LpuSectionProfile_id'] : null),
			'LpuSectionBedProfile_id' => (!empty($data['LpuSectionBedProfile_id']) ? $data['LpuSectionBedProfile_id'] : null),
			'pmUser_id' => $data['pmUser_id'],
		);
   		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) ) {
 	    	return $res->result('array');
		} else {
 	    	return false;
		}
	}
}
?>