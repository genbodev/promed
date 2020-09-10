<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'models/EvnPrescr_model.php');

class Ufa_EvnPrescr_model extends EvnPrescr_model {
	/**
	 * construct
	 */
	function __construct() {
            parent::__construct();
	}

	/**
	 * Загрузка списка назначений / выполнений
	 */
	function loadPrescrPerformanceList($data) {
		$filter = "";
		

		if (isset ($data['PrescrPerform_FIO'])) {
			$filter .= " and Person_Fio like  '%' + :PrescrPerform_FIO + '%'";
			$params['PrescrPerform_FIO'] = $data['PrescrPerform_FIO'];		   
		};
		if (isset ($data['PrescrPerform_DrugNameNazn'])) {
			$filter .= " and DrugName_Plan like  '%' + :PrescrPerform_DrugNameNazn + '%'";
			$params['PrescrPerform_DrugNameNazn'] = $data['PrescrPerform_DrugNameNazn'];		   
		};
		if (isset ($data['PrescrPerform_DrugCodeNazn'])) {
			$filter .= ' and DrugCode_Plan = :PrescrPerform_DrugCodeNazn ';
			$params['PrescrPerform_DrugCodeNazn'] = $data['PrescrPerform_DrugCodeNazn'];		   
		};
		
		if (isset ($data['PrescrPerform_DrugNameIspoln'])) {
			$filter .= " and DrugName_DocUc like  '%' + :PrescrPerform_DrugNameIspoln + '%'";
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
			$filter .= ' and isnull(EvnPrescrTreatDrug_Kolvo, 0) <> isnull(t.DocumentUcStr_EdCount, -1) ';	   
		};
			
				
		$query = "
				Declare

					@begDate date,
					@endDate date,
					@LpuSection_id bigint,
					@Lpu_id bigint;
					
				Set @Lpu_id = :Lpu_id;
				Set @LpuSection_id = :LpuSection_id;
				Set @begDate = :BegDate;
				Set @endDate = :EndDate;
				
				Select 
					RowNumber,
					EvnPrescrTreatDrug_id,
					EvnPrescrDay_id, 
					convert(varchar, EvnPrescr_planDate, 104) EvnPrescr_planDate, --  Дата назначения
					convert(varchar, DocumentUc_diddate, 104) DocumentUc_diddate,  -- Дата исполнения
					Person_Fio, --Drug_Code,
					DrugCode_Plan,  -- Код ЛС назначения
					DrugName_Plan,   -- Наименование ЛС назначения
					DrugCode_DocUc,	-- Код ЛС выполнения
					DrugName_DocUc,	--  ННаименование ЛС выполнения
					CourseGoodsUnit_Nick,  -- ед. измерения
					--convert( NUMERIC (38,3), EvnPrescrTreatDrug_Kolvo) EvnPrescrTreatDrug_Kolvo, --  Назначение в ед. измерения - Кол-во
					STR(EvnPrescrTreatDrug_Kolvo, 10, 3) EvnPrescrTreatDrug_Kolvo,
					--convert( NUMERIC (38,3), DocumentUcStr_EdCount) DocumentUcStr_EdCount,	--  Выполнение в ед. измерения - Кол-во
					STR(DocumentUcStr_EdCount, 10, 3) DocumentUcStr_EdCount,
					--convert( NUMERIC (38,3), EvnCourseTreatDrug_Count) EvnCourseTreatDrug_Count,  --  Назначение в упаковках - Кол-во
					STR(EvnCourseTreatDrug_Count, 10, 3) EvnCourseTreatDrug_Count,
					--convert(NUMERIC (38,3), DocumentUcStr_Count) DocumentUcStr_Count,	--  Выполнение в упаковках - Кол-во
					STR(DocumentUcStr_Count, 10, 3) DocumentUcStr_Count,
					EvnPrescrDay_IsExec,
					comment,
					ctrl,
					CourseGoodsUnitNick_Ctrl,
					pmUser_execName
					--and isnull(EvnPrescrTreatDrug_Kolvo, 0) <> isnull(t.DocumentUcStr_EdCount, 0)  -- Если необходимо вывести записи с разными значениями назначения и исполнения
				from r2.fn_LsPurposePerformanceMO (@begDate, @endDate, @Lpu_id, @LpuSection_id) t
				   where 1=1 
				   {$filter}
				   --and isnull(EvnPrescrTreatDrug_Kolvo, 0) <> isnull(t.DocumentUcStr_EdCount, 0)  -- Если необходимо вывести записи с разными значениями назначения и исполнения
				   order by Person_fio, EvnPrescr_planDate, DrugName_Plan, DrugName_DocUc;
			";
		
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['LpuSection_id'] = $data['LpuSection_id'];
		$params['BegDate'] = $data['PeriodRange'][0];
		$params['EndDate'] = $data['PeriodRange'][1];
		
		//echo getDebugSql($query, $params); exit;
		
		$this->db->query_timeout = 10000;
		
		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
				$response['data'] = $result->result('array');
				//$response[0]['success'] = true;
				return $response;
		}
		else
		{
				return false;
		}
	}
 
}        