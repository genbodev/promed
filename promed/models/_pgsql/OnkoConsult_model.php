<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @package			MorbusOnko
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 */

class OnkoConsult_model extends swPgModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление консилиума
	 */
	function delete($data) {

		$query = "
		    select 
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			from p_OnkoConsult_del(
				OnkoConsult_id := :OnkoConsult_id
			)
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление консилиума //А.И.Г. 05.12.2019 #169863
	 */
	function delete_new($data) {

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_OnkoConsult_del_new(
				OnkoConsult_id := :OnkoConsult_id,
				pmUser_id := :pmUser_id
			)
		";
		sql_log_message('error', 'deleteOncoCons: ', getDebugSql($query, $data));
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список
	 */
	function loadList($data) {
	
		$filter = '';
		$params['Evn_id'] = isset($data['Evn_id']) ? $data['Evn_id'] : null;

		if (!empty($data['MorbusOnkoVizitPLDop_id'])) {
			$params['MorbusOnkoVizitPLDop_id'] = $data['MorbusOnkoVizitPLDop_id'];
			$filter = 'OC.MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id';
		} elseif (!empty($data['MorbusOnkoLeave_id'])) {
			$params['MorbusOnkoLeave_id'] = $data['MorbusOnkoLeave_id'];
			$filter = 'OC.MorbusOnkoLeave_id = :MorbusOnkoLeave_id';
		} elseif (!empty($data['MorbusOnkoDiagPLStom_id'])) {
			$params['MorbusOnkoDiagPLStom_id'] = $data['MorbusOnkoDiagPLStom_id'];
			$filter = 'OC.MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id';
		} elseif (!empty($data['Morbus_id'])) {
			$params['Morbus_id'] = $data['Morbus_id'];
			$filter = 'MO.Morbus_id = :Morbus_id';
		} else {
			return false;
		}

		$query = "
			select
				-- select
				OC.OnkoConsult_id as \"OnkoConsult_id\"
				,'edit' as \"accessType\"
				,OC.MorbusOnko_id as \"MorbusOnko_id\"
				,OC.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\"
				,OC.MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\"
				,OC.MorbusOnkoDiagPLStom_id as \"MorbusOnkoDiagPLStom_id\"
				,to_char(OC.OnkoConsult_consDate, 'dd.mm.yyyy') as \"OnkoConsult_consDate\"
				,to_char(OC.OnkoConsult_PlanDT, 'dd.mm.yyyy') as \"OnkoConsult_PlanDT\"
				,OHT.OnkoHealType_Name as \"OnkoHealType_Name\"
				,OCR.OnkoConsultResult_Name as \"OnkoConsultResult_Name\"
				,:Evn_id as \"MorbusOnko_pid\"
				,MO.Morbus_id as \"Morbus_id\"
				-- end select
			from
				-- from
				v_OnkoConsult OC
				inner join v_MorbusOnko MO on MO.MorbusOnko_id = OC.MorbusOnko_id
				left join v_OnkoHealType OHT on OHT.OnkoHealType_id = OC.OnkoHealType_id
				left join v_OnkoConsultResult OCR on OCR.OnkoConsultResult_id = OC.OnkoConsultResult_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				\"OnkoConsult_consDate\"
				-- end order by
		";

		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает один консилиум
	 */
	function load($data) {
        $fields = "";
        $joins = "";

        if(getRegionNick() == 'ufa') {
            $fields .= "
				,OC.UslugaComplex_id as \"UslugaComplex_id\"
				,OC.DrugTherapyScheme_id as \"DrugTherapyScheme_id\"
				,OC.OnkoChemForm_id as \"OnkoChemForm_id\"
				,OC.OnkoConsult_CouseCount as \"OnkoConsult_CouseCount\"
				,OC.OnkoConsult_Commentary as \"OnkoConsult_Commentary\"
				,OC.Lpu_id as \"Lpu_hid\"
				,OC.MedStaffFact_id as \"MedStaffFact_id\"
				,OC.MedStaffFact_pid as \"MedStaffFact_pid\"
				,OC.MedStaffFact_rid as \"MedStaffFact_rid\"
				,MSF.Lpu_id as \"MSFLpu_id\"
			";
            $joins .= "left join v_MedStaffFact MSF on MSF.MedStaffFact_id = OC.MedStaffFact_id";
        }

        if(getRegionNick() == 'ekb') {
            $fields .= "
				,OC.DrugTherapyScheme_id as \"DrugTherapyScheme_id\"
				,to_char(OC.OnkoConsult_PlanDT, 'dd.mm.yyyy') as \"OnkoConsult_PlanDT\"
			";
            $joins .= "left join v_MedStaffFact MSF on MSF.MedStaffFact_id = OC.MedStaffFact_id";
        }
		$query = "
			select
				-- select
				OC.OnkoConsult_id as \"OnkoConsult_id\"
				,OC.MorbusOnko_id as \"MorbusOnko_id\"
				,OC.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\"
				,OC.MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\"
				,OC.MorbusOnkoDiagPLStom_id as \"MorbusOnkoDiagPLStom_id\"
				,to_char(OC.OnkoConsult_consDate, 'dd.mm.yyyy') as \"OnkoConsult_consDate\"
				,OC.OnkoHealType_id as \"OnkoHealType_id\"
				,OC.OnkoConsultResult_id as \"OnkoConsultResult_id\"
				{$fields}
				-- end select
			from
				-- from
				v_OnkoConsult OC
				{$joins}
				-- end from
			where
				-- where
				OC.OnkoConsult_id = :OnkoConsult_id
				-- end where
		";
        $result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save($data) {

		$procedure = empty($data['OnkoConsult_id']) ? 'p_OnkoConsult_ins' : 'p_OnkoConsult_upd';
	
		// Проверка дубликатов
		$response = $this->checkDoubles($data);
		if(count($response)) {
			throw new Exception('В данной специфике уже имеется запись с аналогичными данными. Сохранение невозможно');
		}
		
		$query = "
			select 
			    OnkoConsult_id as \"OnkoConsult_id\", 
			    Error_Code as \"Error_Code\", 
			    Error_Message as \"Error_Msg\"
			from {$procedure} (
			    OnkoConsult_id := :OnkoConsult_id,
				MorbusOnko_id := :MorbusOnko_id,
				MorbusOnkoVizitPLDop_id := :MorbusOnkoVizitPLDop_id,
				MorbusOnkoLeave_id := :MorbusOnkoLeave_id,
				MorbusOnkoDiagPLStom_id := :MorbusOnkoDiagPLStom_id,
				OnkoConsult_consDate := :OnkoConsult_consDate,
				OnkoHealType_id := :OnkoHealType_id,
				OnkoConsultResult_id := :OnkoConsultResult_id,
				UslugaComplex_id := :UslugaComplex_id,
				DrugTherapyScheme_id := :DrugTherapyScheme_id,
				OnkoChemForm_id := :OnkoChemForm_id,
				OnkoConsult_CouseCount := :OnkoConsult_CouseCount,
				OnkoConsult_Commentary := :OnkoConsult_Commentary,
				Lpu_id := :Lpu_hid,
				MedStaffFact_id := :MedStaffFact_id,
				MedStaffFact_pid := :MedStaffFact_pid,
				MedStaffFact_rid := :MedStaffFact_rid,
				OnkoConsult_PlanDT := :OnkoConsult_PlanDT,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение новое - //А.И.Г. 28.11.2019 #169863
	 */
	function save_new($data) {

		// Проверка дубликатов
		$response = $this->checkDoubles($data);
		if(count($response)) {
			throw new Exception('В данной специфике уже имеется запись с аналогичными данными. Сохранение невозможно');
		}

		$query = "
			select
				OnkoConsult_id as \"OnkoConsult_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_OnkoConsultSave(
				MorbusOnko_id := :MorbusOnko_id,
				MorbusOnkoVizitPLDop_id := :MorbusOnkoVizitPLDop_id,
				MorbusOnkoLeave_id := :MorbusOnkoLeave_id,
				MorbusOnkoDiagPLStom_id := :MorbusOnkoDiagPLStom_id,
				OnkoConsult_consDate := :OnkoConsult_consDate,
				OnkoHealType_id := :OnkoHealType_id,
				OnkoConsultResult_id := :OnkoConsultResult_id,
				OnkoChemForm_id := :OnkoChemForm_id,
				OnkoConsult_CouseCount := :OnkoConsult_CouseCount,
				OnkoConsult_Commentary := :OnkoConsult_Commentary,
				Lpu_id := :Lpu_hid,
				MedStaffFact_id := :MedStaffFact_id,
				MedStaffFact_pid := :MedStaffFact_pid,
				MedStaffFact_rid := :MedStaffFact_rid,
				OnkoConsult_PlanDT := :OnkoConsult_PlanDT,
				ListUsluga := :ListUsluga,
				ListDrugTherapyScheme := :ListDrugTherapyScheme,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Список Услуг - //А.И.Г. 02.12.2019 #169863
	 */
	function loadUslugaList($data) {
		$query = "
			select 
				oc.UslugaComplex_id as \"UslugaComplex_id\" 
			from  v_OnkoConsultUsluga OC
			where OnkoConsult_id = :OnkoConsult_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}

	}

	/**
	 * Список лекарственных схем - //А.И.Г. 04.12.2019 #169863
	 */
	function loadListDrugTherapySchemeList($data) {
		$query = "
			select 
				oc.DrugTherapyScheme_id as \"DrugTherapyScheme_id\" 
			from  v_OnkoConsultDrugScheme OC
			where OnkoConsult_id = :OnkoConsult_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}

	}

	/**
	 * Проверка на дубли
	 */
	function checkDoubles($data) {

		$params = array(
			'OnkoConsult_id' => empty($data['OnkoConsult_id']) ? null : $data['OnkoConsult_id'],
			'OnkoConsult_consDate' => $data['OnkoConsult_consDate'] ?: null,
			'OnkoHealType_id' => $data['OnkoHealType_id'] ?: null,
			'OnkoConsultResult_id' => $data['OnkoConsultResult_id'] ?: null,
		);

		if (!empty($data['MorbusOnkoVizitPLDop_id'])) {
			$params['MorbusOnkoVizitPLDop_id'] = $data['MorbusOnkoVizitPLDop_id'];
			$filter = 'MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id';
		} elseif (!empty($data['MorbusOnkoLeave_id'])) {
			$params['MorbusOnkoLeave_id'] = $data['MorbusOnkoLeave_id'];
			$filter = 'MorbusOnkoLeave_id = :MorbusOnkoLeave_id';
		} elseif (!empty($data['MorbusOnko_id'])) {
			$params['MorbusOnko_id'] = $data['MorbusOnko_id'];
			$filter = 'MorbusOnko_id = :MorbusOnko_id';
		} else {
			return array();
		}

		$query = "
			select
				OnkoConsult_id as \"OnkoConsult_id\"
			from
				v_OnkoConsult
			where
				{$filter} and
				OnkoConsult_id != COALESCE(CAST(:OnkoConsult_id as bigint), 0 ) and
				OnkoConsult_consDate = :OnkoConsult_consDate and
				OnkoHealType_id = :OnkoHealType_id and
				OnkoConsultResult_id = :OnkoConsultResult_id
		";

		return $this->queryResult($query, $params);
	}

}
