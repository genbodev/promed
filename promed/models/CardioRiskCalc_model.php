<?php
class CardioRiskCalc_model extends swModel {
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_CardioRiskCalc_del
				@CardioRiskCalc_id = :CardioRiskCalc_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1
				CRC.CardioRiskCalc_id,
				CRC.Person_id,
				CardioRiskCalc_SistolPress,
				CardioRiskCalc_IsSmoke,
				CardioRiskCalc_Chol,
				CardioRiskCalc_Percent,
				RiskType_id,
				convert(varchar(10), CRC.CardioRiskCalc_setDT, 104) as CardioRiskCalc_setDT
			from
				v_CardioRiskCalc CRC with (nolock)
			where (1 = 1)
				and CRC.CardioRiskCalc_id = :CardioRiskCalc_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :CardioRiskCalc_id;

			exec " . $procedure . "
				@CardioRiskCalc_id = @Res output,
				@Person_id = :Person_id,
				@CardioRiskCalc_setDT = :CardioRiskCalc_setDT,
				@CardioRiskCalc_SistolPress = :CardioRiskCalc_SistolPress,
				@CardioRiskCalc_IsSmoke = :CardioRiskCalc_IsSmoke,
				@CardioRiskCalc_Chol = :CardioRiskCalc_Chol,
				@CardioRiskCalc_Percent = :CardioRiskCalc_Percent,
				@RiskType_id = :RiskType_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CardioRiskCalc_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
                declare @curDT datetime = dbo.tzGetdate();
                select
					sv.ScoreValues_Values
				from
					v_ScoreValues sv (nolock)
					left join v_PersonState ps (nolock) on ps.Person_id = :Person_id
				where
					(cast (:CardioRiskCalc_SistolPress as float) BETWEEN ISNULL(sv.ScoreValues_MinPress,0) and ISNULL(sv.ScoreValues_MaxPress,900)) and
					(dbo.Age(ps.Person_BirthDay, @curDT) BETWEEN ISNULL(sv.ScoreValues_AgeFrom,0) and ISNULL(sv.ScoreValues_AgeTo,900)) and
					(cast (:CardioRiskCalc_Chol as float) BETWEEN ISNULL(sv.ScoreValues_MinChol,0) and ISNULL(sv.ScoreValues_MaxChol,900)) and
					ps.Sex_id = ISNULL(sv.Sex_id, ps.Sex_id) and
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
            $select = " ,crc.Person_id ";
        }

        return $this->queryResult("
			select
				crc.CardioRiskCalc_id,
				crc.CardioRiskCalc_SistolPress,
				crc.CardioRiskCalc_IsSmoke,
				crc.CardioRiskCalc_Chol,
				crc.CardioRiskCalc_Percent,
				crc.RiskType_id,
				rt.RiskType_Name,
				isnull(convert(varchar(10), crc.CardioRiskCalc_setDT, 104), '') as CardioRiskCalc_setDT
				{$select}
			from
				v_CardioRiskCalc crc (nolock)
				left join v_RiskType rt (nolock) on rt.RiskType_id = crc.RiskType_id
			where {$filter}
		", array(
            'Person_id' => $data['Person_id']
        ));
    }
}
