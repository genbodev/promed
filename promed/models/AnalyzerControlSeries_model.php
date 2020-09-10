<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AnalyzerControlSeries_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 */

class AnalyzerControlSeries_model extends swModel {

	/**
	 * Удаление
	 */
	function delete($data) {

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec r59.p_AnalyzerControlSeries_del
				@AnalyzerControlSeries_id = :AnalyzerControlSeries_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		
		return $this->queryResult($query, $data);
	}

	/**
	 * Возвращает список
	 */
	function loadList($data) {
	
		$filter = '(1 = 1)';
		$params = array();
		
		if (!empty($data['AnalyzerTest_id'])) {
			$params['AnalyzerTest_id'] = $data['AnalyzerTest_id'];
			$filter = 'AnalyzerTest_id = :AnalyzerTest_id';
		}
		
		if (is_array($data['AnalyzerControlSeries_regDateRange']) && count($data['AnalyzerControlSeries_regDateRange']) == 2 && !empty($data['AnalyzerControlSeries_regDateRange'][0])) {
			$filter .= ' and cast(acs.AnalyzerControlSeries_regDT as date) between :AnalyzerControlSeries_regDateRangeStart and :AnalyzerControlSeries_regDateRangeEnd ';
			$params['AnalyzerControlSeries_regDateRangeStart'] = $data['AnalyzerControlSeries_regDateRange'][0];
			$params['AnalyzerControlSeries_regDateRangeEnd'] = $data['AnalyzerControlSeries_regDateRange'][1];
		}

		$query = "
			select
				acs.AnalyzerControlSeries_id as \"AnalyzerControlSeries_id\",
				convert(varchar(10), acs.AnalyzerControlSeries_regDT, 104) as \"AnalyzerControlSeries_regDT\",
				acs.AnalyzerControlSeries_Value as \"AnalyzerControlSeries_Value\",
				yn.YesNo_Name as \"AnalyzerControlSeries_IsControlPassed\",
				acs.AnalyzerControlSeries_Comment as \"AnalyzerControlSeries_Comment\",
				msmp.MedPersonal_id as \"MedPersonal_id\"
			from
				r59.v_AnalyzerControlSeries acs with(nolock)
				inner join v_YesNo yn with(nolock) on yn.YesNo_id = acs.AnalyzerControlSeries_IsControlPassed
				inner join v_MedServiceMedPersonal msmp with(nolock) on msmp.MedServiceMedPersonal_id = acs.MedServiceMedPersonal_id
			where
				{$filter}
			order by
				acs.AnalyzerControlSeries_regDT desc
		";

		$res = $this->queryResult($query, $params);
		if (isset($res[0])) {
			$this->load->model('MedPersonal_model');
			$resp = $this->MedPersonal_model->getFioFromMedPersonal([
				'MedPersonal_id' => $res[0]['MedPersonal_id']
			]);

			$res[0]['Person_Fin'] = $resp['Person_Fin'];
		}
		return $res;
	}

	/**
	 * Возвращает
	 */
	function load($data) {

		$query = "
			select
				acs.AnalyzerControlSeries_id as \"AnalyzerControlSeries_id\",
				acs.AnalyzerTest_id as \"AnalyzerTest_id\",
				convert(varchar(10), acs.AnalyzerControlSeries_regDT, 104) as \"AnalyzerControlSeries_regDT\",
				acs.AnalyzerControlSeries_Value as \"AnalyzerControlSeries_Value\",
				acs.AnalyzerControlSeries_Comment as \"AnalyzerControlSeries_Comment\",
				acs.AnalyzerControlSeries_IsControlPassed as \"AnalyzerControlSeries_IsControlPassed\",
				msmp.MedService_id as \"MedService_id\"
			from
				r59.v_AnalyzerControlSeries acs with(nolock)
				inner join v_MedServiceMedPersonal msmp with(nolock) on msmp.MedServiceMedPersonal_id = acs.MedServiceMedPersonal_id
			where
				AnalyzerControlSeries_id = :AnalyzerControlSeries_id
		";
		
		return $this->queryResult($query, $data);
	}

	/**
	 * Сохраняет
	 */
	function save($data) {
		
		$data['MedServiceMedPersonal_id'] = $this->getFirstResultFromQuery("
			select
				MedServiceMedPersonal_id as \"MedServiceMedPersonal_id\"
			from
				v_MedServiceMedPersonal with(nolock)
			where
				MedPersonal_id = :MedPersonal_id
				and MedService_id = :MedService_id", $data, true);
		
		$procedure = empty($data['AnalyzerControlSeries_id']) ? 'p_AnalyzerControlSeries_ins' : 'p_AnalyzerControlSeries_upd';

		$query = "
			declare
				@AnalyzerControlSeries_id bigint = :AnalyzerControlSeries_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec r59.{$procedure}
				@AnalyzerControlSeries_id = @AnalyzerControlSeries_id output,
				@AnalyzerTest_id = :AnalyzerTest_id,
				@AnalyzerControlSeries_regDT = :AnalyzerControlSeries_regDT,
				@AnalyzerControlSeries_Value = :AnalyzerControlSeries_Value,
				@AnalyzerControlSeries_Comment = :AnalyzerControlSeries_Comment,
				@AnalyzerControlSeries_IsControlPassed = :AnalyzerControlSeries_IsControlPassed,
				@MedServiceMedPersonal_id = :MedServiceMedPersonal_id,
				@MedServiceMedPersonal_did = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @AnalyzerControlSeries_id as AnalyzerControlSeries_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		return $this->queryResult($query, $data);
	}
}