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

class DispAppoint_model extends SwModel
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
				DA.DispAppoint_id,
				DA.EvnPLDisp_id,
				DA.DispAppointType_id,
				DA.MedSpecOms_id,
				DA.ExaminationType_id,
				DA.LpuSectionProfile_id,
				DA.LpuSectionBedProfile_id,
				DAT.DispAppointType_Name,
				case
					when DA.DispAppointType_id IN (1,2) then MSO.MedSpecOms_Name
					when DA.DispAppointType_id = 3 then ET.ExaminationType_Name
					when DA.DispAppointType_id IN (4,5) then LSP.LpuSectionProfile_Name
					when DA.DispAppointType_id = 6 then LSBP.LpuSectionBedProfile_Name
				end as DispAppoint_Comment,
				1 as RecordStatus_Code
			from
				v_DispAppoint DA (nolock)
				left join v_DispAppointType DAT (nolock) on DAT.DispAppointType_id = DA.DispAppointType_id
				left join v_MedSpecOms MSO (nolock) on MSO.MedSpecOms_id = DA.MedSpecOms_id
				left join v_ExaminationType ET (nolock) on ET.ExaminationType_id = DA.ExaminationType_id
				left join fed.v_LpuSectionProfile LSP (nolock) on LSP.LpuSectionProfile_id = DA.LpuSectionProfile_id
				left join fed.v_LpuSectionBedProfile LSBP (nolock) on LSBP.LpuSectionBedProfile_id = DA.LpuSectionBedProfile_id
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
				DA.DispAppoint_id,
				DA.EvnPLDisp_id,
				DAT.DispAppointType_Name,
				case
					when DA.DispAppointType_id IN (1,2) then MSO.MedSpecOms_Name
					when DA.DispAppointType_id = 3 then ET.ExaminationType_Name
					when DA.DispAppointType_id IN (4,5) then LSP.LpuSectionProfile_Name
					when DA.DispAppointType_id = 6 then LSBP.LpuSectionBedProfile_Name
				end as DispAppoint_Comment
			from
				v_DispAppoint DA with(nolock)
				left join v_DispAppointType DAT (nolock) on DAT.DispAppointType_id = DA.DispAppointType_id
				left join v_MedSpecOms MSO (nolock) on MSO.MedSpecOms_id = DA.MedSpecOms_id
				left join v_ExaminationType ET (nolock) on ET.ExaminationType_id = DA.ExaminationType_id
				left join fed.v_LpuSectionProfile LSP (nolock) on LSP.LpuSectionProfile_id = DA.LpuSectionProfile_id
				left join fed.v_LpuSectionBedProfile LSBP (nolock) on LSBP.LpuSectionBedProfile_id = DA.LpuSectionBedProfile_id
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
			select top 1
				DispAppoint_id
			from
				v_DispAppoint (nolock)
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and DispAppointType_id = :DispAppointType_id
				and ISNULL(MedSpecOms_id, 0) = ISNULL(:MedSpecOms_id, 0)
				and ISNULL(ExaminationType_id, 0) = ISNULL(:ExaminationType_id, 0)
				and ISNULL(LpuSectionProfile_id, 0) = ISNULL(:LpuSectionProfile_id, 0)
				and ISNULL(LpuSectionBedProfile_id, 0) = ISNULL(:LpuSectionBedProfile_id, 0)
				and DispAppoint_id <> ISNULL(:DispAppoint_id,0)
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_DispAppoint_del
				@DispAppoint_id = :DispAppoint_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
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
			declare
				@DispAppoint_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @DispAppoint_id = :DispAppoint_id;
			exec {$proc}
				@DispAppoint_id = @DispAppoint_id output,
				@EvnPLDisp_id = :EvnPLDisp_id,
				@DispAppointType_id = :DispAppointType_id,
				@MedSpecOms_id = :MedSpecOms_id,
				@ExaminationType_id = :ExaminationType_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@LpuSectionBedProfile_id = :LpuSectionBedProfile_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @DispAppoint_id as DispAppoint_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
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