<?php
/**
* MorbusHepatitisCureEffMonitoring_model - модель, для работы с таблицей MorbusHepatitisCureEffMonitoring
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
*/

class MorbusHepatitisCureEffMonitoring_model extends CI_Model {
	/**
	 * MorbusHepatitisCureEffMonitoring_model constructor.
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function loadList($data)
	{
		$query = "
			select 
				MHCEM.MorbusHepatitisCureEffMonitoring_id,
				MHCEM.MorbusHepatitisCure_id,
				1 RecordStatus_Code,
				MHCEM.HepatitisCurePeriodType_id,
				HCPT.HepatitisCurePeriodType_Name,
				MHCEM.HepatitisQualAnalysisType_id,
				HQAT.HepatitisQualAnalysisType_Name,
				MHCEM.MorbusHepatitisCureEffMonitoring_VirusStress
			from
				v_MorbusHepatitisCureEffMonitoring MHCEM with(nolock)
			left join v_HepatitisCurePeriodType HCPT with (nolock) on HCPT.HepatitisCurePeriodType_id = MHCEM.HepatitisCurePeriodType_id
			left join v_HepatitisQualAnalysisType HQAT with (nolock) on HQAT.HepatitisQualAnalysisType_id = MHCEM.HepatitisQualAnalysisType_id
			where
				MorbusHepatitisCure_id = ?
		";
		$res = $this->db->query($query, array($data['MorbusHepatitisCure_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;		
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function load($data)
	{
		$query = "
			select 
				MorbusHepatitisCureEffMonitoring_id,
				MorbusHepatitisCure_id,
				HepatitisCurePeriodType_id,
				HepatitisQualAnalysisType_id,
				MorbusHepatitisCureEffMonitoring_VirusStress
			from
				v_MorbusHepatitisCureEffMonitoring with(nolock)
			where
				MorbusHepatitisCureEffMonitoring_id = ?
		";
		$res = $this->db->query($query, array($data['MorbusHepatitisCureEffMonitoring_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;		
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function save($data)
	{

		if ( !isset($data['MorbusHepatitisCureEffMonitoring_id']) || $data['MorbusHepatitisCureEffMonitoring_id'] <=0 ) {
			$data['MorbusHepatitisCureEffMonitoring_id'] = null;
			$procedure_action = "ins";
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :MorbusHepatitisCureEffMonitoring_id;
			exec p_MorbusHepatitisCureEffMonitoring_" . $procedure_action . "
				@MorbusHepatitisCureEffMonitoring_id = @Res output,
				@MorbusHepatitisCure_id = :MorbusHepatitisCure_id,
				@HepatitisCurePeriodType_id = :HepatitisCurePeriodType_id,
				@HepatitisQualAnalysisType_id = :HepatitisQualAnalysisType_id,
				@MorbusHepatitisCureEffMonitoring_VirusStress = :MorbusHepatitisCureEffMonitoring_VirusStress,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as MorbusHepatitisCureEffMonitoring_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		
		$queryParams = array(
			'MorbusHepatitisCureEffMonitoring_id' => $data['MorbusHepatitisCureEffMonitoring_id'],
			'MorbusHepatitisCure_id' => $data['MorbusHepatitisCure_id'],
			'HepatitisCurePeriodType_id' => $data['HepatitisCurePeriodType_id'],
			'HepatitisQualAnalysisType_id' => $data['HepatitisQualAnalysisType_id'],
			'MorbusHepatitisCureEffMonitoring_VirusStress' => $data['MorbusHepatitisCureEffMonitoring_VirusStress'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function delete($data)
	{
		$params = array(
			'MorbusHepatitisCureEffMonitoring_id' => $data['MorbusHepatitisCureEffMonitoring_id']
		);
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_MorbusHepatitisCureEffMonitoring_del
				@MorbusHepatitisCureEffMonitoring_id = :MorbusHepatitisCureEffMonitoring_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	
}