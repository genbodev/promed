<?php defined('BASEPATH') or die ('No direct script access allowed');

class NormCostItem_model extends SwPgModel {
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
                NCI.NormCostItem_id as \"NormCostItem_id\",
                NCI.UslugaComplex_id as \"UslugaComplex_id\",
				DN.DrugNomen_Code as \"DrugNomen_Code\",
                DN.DrugNomen_Name as \"DrugNomen_Name\",
                NCI.NormCostItem_Kolvo as \"NormCostItem_Kolvo\",
                A.Analyzer_Name as \"Analyzer_Name\",
			    U.Unit_Name as \"Unit_Name\",
                to_char(NCI.NormCostItem_updDT, 'dd.mm.yyyy') as \"NormCostItem_updDT\"
			from
				v_NormCostItem NCI 
				left join lis.v_Analyzer A  on A.Analyzer_id = NCI.Analyzer_id
				left join rls.v_DrugNomen DN  on DN.DrugNomen_id = NCI.DrugNomen_id
				left join lis.v_unit U on U.Unit_id = NCI.Unit_id
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
                NCI.NormCostItem_id as \"NormCostItem_id\",
                NCI.UslugaComplex_id as \"UslugaComplex_id\",
				NCI.DrugNomen_id as \"DrugNomen_id\",
                NCI.NormCostItem_Kolvo as \"NormCostItem_Kolvo\",
                NCI.Analyzer_id as \"Analyzer_id\",
                NCI.AnalyzerTest_id as \"AnalyzerTest_id\",
                NCI.Unit_id as \"Unit_id\"
			from
				v_NormCostItem NCI 
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
                    v_NormCostItem
                where
                    COALESCE(Analyzer_id, 0) = COALESCE(CAST(:Analyzer_id as bigint), 0)
                    and COALESCE(AnalyzerTest_id, 0) = COALESCE(CAST(:UslugaComplex_id as bigint), 0)
                    and COALESCE(DrugNomen_id, 0) = COALESCE(CAST(:DrugNomen_id as bigint), 0)
                    {$filter}
            ", $data);

        if (!empty($resp[0]['NormCostItem_id'])) {
            return array('Error_Msg' => 'Обнаружено дублирование, сохранение не возможно');
        }

        $query = "
	       	 select
	            Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\",
		        NormCostItem_id as \"NormCostItem_id\"
		    from {$proc}
            (
			        NormCostItem_id := :NormCostItem_id,
				    UslugaComplex_id := :UslugaComplex_id,
				    DrugNomen_id := :DrugNomen_id,
				    NormCostItem_Kolvo := :NormCostItem_Kolvo,
					AnalyzerTest_id := :AnalyzerTest_id,
					Unit_id := :Unit_id,
				    Analyzer_id := :Analyzer_id,
				    pmUser_id := :pmUser_id
		    )";


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
			select
	            Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from p_NormCostItem_del
			(
				NormCostItem_id := :id,
				pmUser_id := :pmUser_id
			)";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}
}