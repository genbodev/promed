<?php
class CardioRiskCalc_model extends SwPgModel {
    /**
     * CardioRiskCalc_model constructor.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * @param $data
     * @return bool|mixed
     */
    function deleteCardioRiskCalc($data) {
        $query = "
            select 
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
			from p_CardioRiskCalc_del (
				CardioRiskCalc_id := :CardioRiskCalc_id
				)
		";
        $result = $this->db->query($query, array(
            'CardioRiskCalc_id' => $data['CardioRiskCalc_id']
        ));

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * @param $data
     * @return bool|mixed
     */
    function loadCardioRiskCalcEditForm($data) {
        $query = "
			select
				CRC.CardioRiskCalc_id as \"CardioRiskCalc_id\",
				CRC.Person_id as \"Person_id\",
				CardioRiskCalc_SistolPress as \"CardioRiskCalc_SistolPress\",
				CardioRiskCalc_IsSmoke as \"CardioRiskCalc_IsSmoke\",
				CardioRiskCalc_Chol as \"CardioRiskCalc_Chol\",
				CardioRiskCalc_Percent as \"CardioRiskCalc_Percent\",
				RiskType_id as \"RiskType_id\",
				to_char (CRC.CardioRiskCalc_setDT, 'dd.mm.yyyy') as \"CardioRiskCalc_setDT\"
			from
				v_CardioRiskCalc CRC
			where (1 = 1)
				and CRC.CardioRiskCalc_id = :CardioRiskCalc_id
            limit 1
		";
        $result = $this->db->query($query, array(
            'CardioRiskCalc_id' => $data['CardioRiskCalc_id']
        ));

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * @param $data
     * @return bool|mixed
     */
    function saveCardioRiskCalc($data) {

        if ( (!isset($data['CardioRiskCalc_id'])) || ($data['CardioRiskCalc_id'] <= 0) ) {
            $procedure = 'p_CardioRiskCalc_ins';
        }
        else {
            $procedure = 'p_CardioRiskCalc_upd';
        }

        $query = "
            select 
                CardioRiskCalc_id as \"CardioRiskCalc_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from " . $procedure . " (
				CardioRiskCalc_id := :CardioRiskCalc_id,
				Person_id := :Person_id,
				CardioRiskCalc_setDT := :CardioRiskCalc_setDT,
				CardioRiskCalc_SistolPress := :CardioRiskCalc_SistolPress,
				CardioRiskCalc_IsSmoke := :CardioRiskCalc_IsSmoke,
				CardioRiskCalc_Chol := :CardioRiskCalc_Chol,
				CardioRiskCalc_Percent := :CardioRiskCalc_Percent,
				RiskType_id := :RiskType_id,
				pmUser_id := :pmUser_id
				)
		";

        $queryParams = array(
            'Server_id' => $data['Server_id'],
            'CardioRiskCalc_id' => $data['CardioRiskCalc_id'],
            'Person_id' => $data['Person_id'],
            'CardioRiskCalc_IsSmoke' => $data['CardioRiskCalc_IsSmoke'],
            'CardioRiskCalc_SistolPress' => $data['CardioRiskCalc_SistolPress'],
            'CardioRiskCalc_setDT' => $data['CardioRiskCalc_setDT'],
            'CardioRiskCalc_Chol' => $data['CardioRiskCalc_Chol'],
            'CardioRiskCalc_Percent' => $data['CardioRiskCalc_Percent'],
            'RiskType_id' => $data['RiskType_id'],
            'pmUser_id' => $data['pmUser_id']
        );

        $result = $this->db->query($query, $queryParams);

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Расчет процента сердечно-сосудистого риска риска
     */
    function calcCargioRiskPercent($data) {

        $query = "
                select
					sv.ScoreValues_Values as \"ScoreValues_Values\"
				from
					v_ScoreValues sv
					left join v_PersonState ps on ps.Person_id = :Person_id
				where
					(cast (:CardioRiskCalc_SistolPress as float) BETWEEN COALESCE(sv.ScoreValues_MinPress,0) and COALESCE(sv.ScoreValues_MaxPress,900)) and
					(dbo.Age(ps.Person_BirthDay, dbo.tzGetdate()) BETWEEN COALESCE(sv.ScoreValues_AgeFrom,0) and COALESCE(sv.ScoreValues_AgeTo,900)) and
					(cast (:CardioRiskCalc_Chol as float) BETWEEN COALESCE(sv.ScoreValues_MinChol,0) and COALESCE(sv.ScoreValues_MaxChol,900)) and
					ps.Sex_id = COALESCE(sv.Sex_id, ps.Sex_id) and
					:CardioRiskCalc_IsSmoke = ScoreValues_IsSmoke
		";

        return $this->getFirstRowFromQuery($query, $data, true);

    }


    /**
     * Получение списка рисков пациента для ЭМК
     */
    function loadCardioRiskCalcPanel($data) {

        $filter = " crc.Person_id = :Person_id "; $select = "";

        // для оффлайн режима
        if (!empty($data['person_in'])) {
            $filter = " crc.Person_id in ({$data['person_in']}) ";
            $select = " ,crc.Person_id as \"Person_id\"";
        }

        return $this->queryResult("
			select
				crc.CardioRiskCalc_id as \"CardioRiskCalc_id\",
				crc.CardioRiskCalc_SistolPress as \"CardioRiskCalc_SistolPress\",
				crc.CardioRiskCalc_IsSmoke as \"CardioRiskCalc_IsSmoke\",
				crc.CardioRiskCalc_Chol as \"CardioRiskCalc_Chol\",
				crc.CardioRiskCalc_Percent as \"CardioRiskCalc_Percent\",
				crc.RiskType_id as \"RiskType_id\",
				rt.RiskType_Name as \"RiskType_Name\",
				coalesce(to_char (crc.CardioRiskCalc_setDT, 'dd.mm.yyyy'), '') as \"CardioRiskCalc_setDT\"
				{$select}
			from
				v_CardioRiskCalc crc
				left join v_RiskType rt on rt.RiskType_id = crc.RiskType_id
			where {$filter}
		", array(
            'Person_id' => $data['Person_id']
        ));
    }
}
