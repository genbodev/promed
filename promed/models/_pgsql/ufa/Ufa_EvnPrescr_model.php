<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH . 'models/_pgsql/EvnPrescr_model.php');

class Ufa_EvnPrescr_model extends EvnPrescr_model
{
	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Загрузка списка назначений / выполнений
	 */
	function loadPrescrPerformanceList($data)
	{
		$filter = "";


		if (isset ($data['PrescrPerform_FIO'])) {
			$filter .= " and Person_Fio ilike  '%' || :PrescrPerform_FIO || '%'";
			$params['PrescrPerform_FIO'] = $data['PrescrPerform_FIO'];
		};
		if (isset ($data['PrescrPerform_DrugNameNazn'])) {
			$filter .= " and DrugName_Plan ilike  '%' || :PrescrPerform_DrugNameNazn || '%'";
			$params['PrescrPerform_DrugNameNazn'] = $data['PrescrPerform_DrugNameNazn'];
		};
		if (isset ($data['PrescrPerform_DrugCodeNazn'])) {
			$filter .= ' and DrugCode_Plan = :PrescrPerform_DrugCodeNazn ';
			$params['PrescrPerform_DrugCodeNazn'] = $data['PrescrPerform_DrugCodeNazn'];
		};

		if (isset ($data['PrescrPerform_DrugNameIspoln'])) {
			$filter .= " and DrugName_DocUc ilike  '%' || :PrescrPerform_DrugNameIspoln || '%'";
			$params['PrescrPerform_DrugNameIspoln'] = $data['PrescrPerform_DrugNameIspoln'];
		};
		if (isset ($data['PrescrPerform_DrugCodeIspoln'])) {
			$filter .= ' and DrugCode_DocUc = :PrescrPerform_DrugCodeIspoln ';
			$params['PrescrPerform_DrugCodeIspoln'] = $data['PrescrPerform_DrugCodeIspoln'];
		};
		if (isset ($data['PrescrPerform_IspolnCombo']) && $data['PrescrPerform_IspolnCombo'] >= 0) {
			$filter .= ' and EvnPrescrDay_IsExec = :PrescrPerform_IspolnCombo ';
			$params['PrescrPerform_IspolnCombo'] = $data['PrescrPerform_IspolnCombo'];
		};
		if (isset ($data['PrescrPerform_Differences']) && $data['PrescrPerform_Differences'] == 1) {
			$filter .= ' and COALESCE(EvnPrescrTreatDrug_Kolvo, 0) <> COALESCE(t.DocumentUcStr_EdCount, -1) ';
		};


		$query = "
				Select 
					RowNumber as \"RowNumber\",
					EvnPrescrTreatDrug_id as \"EvnPrescrTreatDrug_id\",
					EvnPrescrDay_id as \"EvnPrescrDay_id\", 
					to_char(EvnPrescr_planDate, 'dd.mm.yyyy') EvnPrescr_planDate, --  Дата назначения
					to_char(DocumentUc_diddate, 'dd.mm.yyyy') DocumentUc_diddate,  -- Дата исполнения
					Person_Fio as \"Person_Fio\", --Drug_Code,
					DrugCode_Plan as \"DrugCode_Plan\",  -- Код ЛС назначения
					DrugName_Plan as \"DrugName_Plan\",   -- Наименование ЛС назначения
					DrugCode_DocUc as \"DrugCode_DocUc\",	-- Код ЛС выполнения
					DrugName_DocUc as \"DrugName_DocUc\",	--  ННаименование ЛС выполнения
					CourseGoodsUnit_Nick as \"CourseGoodsUnit_Nick\",  -- ед. измерения
					--convert( NUMERIC (38,3), EvnPrescrTreatDrug_Kolvo) EvnPrescrTreatDrug_Kolvo, --  Назначение в ед. измерения - Кол-во
					STR(EvnPrescrTreatDrug_Kolvo, 10, 3) as \"EvnPrescrTreatDrug_Kolvo\",
					--convert( NUMERIC (38,3), DocumentUcStr_EdCount) DocumentUcStr_EdCount,	--  Выполнение в ед. измерения - Кол-во
					STR(DocumentUcStr_EdCount, 10, 3) as \"DocumentUcStr_EdCount\",
					--convert( NUMERIC (38,3), EvnCourseTreatDrug_Count) EvnCourseTreatDrug_Count,  --  Назначение в упаковках - Кол-во
					STR(EvnCourseTreatDrug_Count, 10, 3) as \"EvnCourseTreatDrug_Count\",
					--convert(NUMERIC (38,3), DocumentUcStr_Count) DocumentUcStr_Count,	--  Выполнение в упаковках - Кол-во
					STR(DocumentUcStr_Count, 10, 3) as \"DocumentUcStr_Count\",
					EvnPrescrDay_IsExec as \"EvnPrescrDay_IsExec\",
					comment as \"comment\",
					ctrl as \"ctrl\",
					CourseGoodsUnitNick_Ctrl as \"CourseGoodsUnitNick_Ctrl\",
					pmUser_execName as \"pmUser_execName\"
					--and COALESCE(EvnPrescrTreatDrug_Kolvo, 0) <> COALESCE(t.DocumentUcStr_EdCount, 0)  -- Если необходимо вывести записи с разными значениями назначения и исполнения
				from r2.fn_LsPurposePerformanceMO (:begDate, :endDate, :Lpu_id, :LpuSection_id) t
				   where 1=1 
				   {$filter}
				   --and COALESCE(EvnPrescrTreatDrug_Kolvo, 0) <> COALESCE(t.DocumentUcStr_EdCount, 0)  -- Если необходимо вывести записи с разными значениями назначения и исполнения
				   order by Person_fio, EvnPrescr_planDate, DrugName_Plan, DrugName_DocUc;
			";

		$params['Lpu_id'] = $data['Lpu_id'];
		$params['LpuSection_id'] = $data['LpuSection_id'];
		$params['BegDate'] = $data['PeriodRange'][0];
		$params['EndDate'] = $data['PeriodRange'][1];

		//echo getDebugSql($query, $params); exit;

		$this->db->query_timeout = 10000;

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$response['data'] = $result->result('array');
			//$response[0]['success'] = true;
			return $response;
		} else {
			return false;
		}
	}

}