<?php defined('BASEPATH') or die ('No direct script access allowed');

class NormCostItem_model extends swModel {
	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *  Получение списка нормативов
     */
	function loadNormCostItemGrid($data) {
		$query = "
			select
                NCI.NormCostItem_id,
                NCI.UslugaComplex_id,
				DN.DrugNomen_Code,
                DN.DrugNomen_Name,
                NCI.NormCostItem_Kolvo,
                A.Analyzer_Name,
                U.Unit_Name,
                convert(varchar, NCI.NormCostItem_updDT, 104) as NormCostItem_updDT
			from
				v_NormCostItem NCI (nolock)
				left join lis.v_Analyzer A (nolock) on A.Analyzer_id = NCI.Analyzer_id
				left join rls.v_DrugNomen DN (nolock) on DN.DrugNomen_id = NCI.DrugNomen_id
				left join lis.v_unit U (nolock) on U.Unit_id = NCI.Unit_id
			where (1 = 1)
				and NCI.AnalyzerTest_id = :AnalyzerTest_id
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
	 *  Загрузка формы редактирования
     */
	function loadNormCostItemEditForm($data) {
		$query = "
			select
                NCI.NormCostItem_id,
                NCI.UslugaComplex_id,
				NCI.DrugNomen_id,
                NCI.NormCostItem_Kolvo,
                NCI.Analyzer_id,
                NCI.AnalyzerTest_id,
                NCI.Unit_id
			from
				v_NormCostItem NCI (nolock)
			where (1 = 1)
				and NCI.NormCostItem_id = :NormCostItem_id
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
	 *  Сохранение норматива
     */
	function saveNormCostItem($data) {
		$filter = "";
		$proc = 'p_NormCostItem_ins';
		if (!empty($data['NormCostItem_id'])) {
			$filter .= " and NormCostItem_id <> :NormCostItem_id";
			$proc = 'p_NormCostItem_upd';
		}

		$resp = $this->queryResult("
			select
				NormCostItem_id
			from
				v_NormCostItem (nolock)
			where
				ISNULL(Analyzer_id, 0) = ISNULL(:Analyzer_id, 0)
				and ISNULL(AnalyzerTest_id, 0) = ISNULL(:AnalyzerTest_id, 0)
				and ISNULL(DrugNomen_id, 0) = ISNULL(:DrugNomen_id, 0)
				{$filter}
		", $data);

		if (!empty($resp[0]['NormCostItem_id'])) {
			return array('Error_Msg' => 'Обнаружено дублирование, сохранение не возможно');
		}
		
		$query = "
			declare
				@NormCostItem_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @NormCostItem_id = :NormCostItem_id;
			exec {$proc}
				@NormCostItem_id = @NormCostItem_id output,
				@UslugaComplex_id = :UslugaComplex_id,
				@DrugNomen_id = :DrugNomen_id,
				@NormCostItem_Kolvo = :NormCostItem_Kolvo,
				@Analyzer_id = :Analyzer_id,
				@AnalyzerTest_id = :AnalyzerTest_id,
				@Unit_id = :Unit_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @NormCostItem_id as NormCostItem_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
   		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
 	    	return $result->result('array');
		}
 	    
		return false;
	}

	/**
	 * Удаление Нормы
	 * @param $data
	 * @return bool|object
	 */
	function deleteNormCostItem($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_NormCostItem_del
				@NormCostItem_id = :id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}
}
