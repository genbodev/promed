<?php defined('BASEPATH') or die ('No direct script access allowed');
class PersonHeight_model extends SwPgModel {
	/**
	 * constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *
	 * @param array $data
	 * @return array|bool
	 */
	function deletePersonHeight($data) {
		$query = "			
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonHeight_del (
				PersonHeight_id := :PersonHeight_id
			)
		";

		$result = $this->db->query($query, array(
			'PersonHeight_id' => $data['PersonHeight_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных об измерениях роста человека
	 */
	function getPersonHeightViewData($data) {
		$query = "
			select
				PH.Person_id as \"Person_id\",
				0 as \"Children_Count\",
				PH.PersonHeight_id as \"PersonHeight_id\",
				PH.PersonHeight_id as \"Anthropometry_id\",
				to_char(PH.PersonHeight_setDT, 'DD.MM.YYYY') as \"PersonHeight_setDate\",
				PH.PersonHeight_Height::numeric as \"PersonHeight_Height\",
				coalesce(HMT.HeightMeasureType_Name, '') as \"HeightMeasureType_Name\",
				coalesce(IsAbnorm.YesNo_Name, '') as \"PersonHeight_IsAbnorm\",
				coalesce(HAT.HeightAbnormType_Name, '') as \"HeightAbnormType_Name\",
				PH.pmUser_insID as \"pmUser_insID\",
				coalesce(PU.pmUser_Name, '') as \"pmUser_Name\"
			from
				v_PersonHeight PH
				inner join HeightMeasureType HMT on HMT.HeightMeasureType_id = PH.HeightMeasureType_id
				left join YesNo IsAbnorm on IsAbnorm.YesNo_id = PH.PersonHeight_IsAbnorm
				left join HeightAbnormType HAT on HAT.HeightAbnormType_id = PH.HeightAbnormType_id
				left join v_pmUser PU on PU.pmUser_id = coalesce(PH.pmUser_updID, PH.pmUser_insID)
			where
				PH.Person_id = :Person_id
			order by
				PH.PersonHeight_setDT
		";

		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadPersonHeightEditForm($data) {
		$query = "
			select
				PH.PersonHeight_id as \"PersonHeight_id\",
				PH.Person_id as \"Person_id\",
				PH.Evn_id as \"Evn_id\",
				PH.Server_id as \"Server_id\",
				PH.HeightAbnormType_id as \"HeightAbnormType_id\",
				PH.HeightMeasureType_id as \"HeightMeasureType_id\",
				PH.PersonHeight_IsAbnorm as \"PersonHeight_IsAbnorm\",
				PH.PersonHeight_Height as \"PersonHeight_Height\",
				to_char(PH.PersonHeight_setDT, 'DD.MM.YYYY') as \"PersonHeight_setDate\"
			from
				v_PersonHeight PH
			where (1 = 1)
				and PH.PersonHeight_id = :PersonHeight_id
			limit 1
		";
		$result = $this->db->query($query, array(
			'PersonHeight_id' => $data['PersonHeight_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadPersonHeightGrid($data) {
		$filter = "(1 = 1)";
		$join_str = "";

		if ( !empty($data['mode']) && $data['mode'] == 'child' ) {
			$filter .= "
				and (PHSD.PersonHeight_setDT is null or PH.PersonHeight_setDT <= PHSD.PersonHeight_setDT)
			";
			$join_str = "
				left join lateral (
					select
						PHtmp.PersonHeight_setDT
					from
						v_PersonHeight PHtmp
						inner join HeightMeasureType HMTtmp on HMTtmp.HeightMeasureType_id = PHtmp.HeightMeasureType_id
					where PHtmp.Person_id = :Person_id
						and HMTtmp.HeightMeasureType_Code = 2
					limit 1
				) PHSD on true
			";
		}
		$params = array(
			'Person_id' => $data['Person_id']
		);
		if ( isset($data['HeightMeasureType_id']) && (!empty($data['HeightMeasureType_id']))) {
			$filter = $filter.' AND PH.HeightMeasureType_id = :HeightMeasureType_id ';
			$params['HeightMeasureType_id'] = $data['HeightMeasureType_id'];
		}

		$query = "
			select
				PH.PersonHeight_id as \"PersonHeight_id\",
				PH.Person_id as \"Person_id\",
				PH.Evn_id as \"Evn_id\",
				PH.PersonHeight_IsAbnorm as \"PersonHeight_IsAbnorm\",
				PH.HeightAbnormType_id as \"HeightAbnormType_id\",
				PH.HeightMeasureType_id as \"HeightMeasureType_id\",
				coalesce(HMT.HeightMeasureType_Code, 0) as \"HeightMeasureType_Code\",
				to_char(PH.PersonHeight_setDT, 'DD.MM.YYYY') as \"PersonHeight_setDate\",
				PH.PersonHeight_Height as \"PersonHeight_Height\",
				1 as \"RecordStatus_Code\"
			from
				v_PersonHeight PH
				inner join HeightMeasureType HMT on HMT.HeightMeasureType_id = PH.HeightMeasureType_id
				" . $join_str . "
			where " . $filter . "
				and PH.Person_id = :Person_id
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *
	 * @param array $data
	 * @return array
	 */
	function savePersonHeight($data) {
		$procedure = "p_PersonHeight_ins";

		if ( !empty($data['PersonHeight_Height']) ) {
			$data['PersonHeight_Height'] = floatval(str_replace(',','.',$data['PersonHeight_Height']));
		}

		if ( !empty($data['PersonHeight_id']) ) {
			$procedure = "p_PersonHeight_upd";
		}else if($data['HeightMeasureType_id']==1){
			$query = 'select PersonHeight_id as "PersonHeight_id" from v_PersonHeight where HeightMeasureType_id=1 and Person_id=:Person_id';
			$result = $this->db->query($query, array('Person_id'=>$data['Person_id']));
			if ( is_object($result) ) {
				$res = $result->result('array');
				if(count($res)>0){
					$procedure = "p_PersonHeight_upd";
					$data['PersonHeight_id'] = $res[0]['PersonHeight_id'];
				}
			}
		}

		$query = "		
			select
				PersonHeight_id as \"PersonHeight_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure} (
				Server_id := :Server_id,
				PersonHeight_id := :PersonHeight_id,
				Person_id := :Person_id,
				PersonHeight_setDT := :PersonHeight_setDate,
				PersonHeight_Height := :PersonHeight_Height,
				PersonHeight_IsAbnorm := :PersonHeight_IsAbnorm,
				HeightAbnormType_id := :HeightAbnormType_id,
				HeightMeasureType_id := :HeightMeasureType_id,
				Okei_id := 2,
				Evn_id := :Evn_id,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'PersonHeight_id' => (!empty($data['PersonHeight_id']) ? $data['PersonHeight_id'] : NULL),
			'Person_id' => $data['Person_id'],
			'PersonHeight_setDate' => $data['PersonHeight_setDate'],
			'PersonHeight_Height' => $data['PersonHeight_Height'],
			'PersonHeight_IsAbnorm' => (!empty($data['PersonHeight_IsAbnorm']) ? $data['PersonHeight_IsAbnorm'] : NULL),
			'HeightAbnormType_id' => (!empty($data['HeightAbnormType_id']) ? $data['HeightAbnormType_id'] : NULL),
			'HeightMeasureType_id' => $data['HeightMeasureType_id'],
			'Evn_id'=>(!empty($data['Evn_id']) ? $data['Evn_id'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения роста пациента)'));
		}
	}

	/**
	 * Получение списка измерений массы пациента для ЭМК
	 */
	function loadPersonHeightPanel($data) {

		$limit = '';
		$order = '';
		if(isset($data['limiter'])) {
			$limit = " limit {$data['limiter']} ";
			$order = " order by ph.PersonHeight_setDT desc, ph.PersonHeight_id desc ";
		}
		$filter = " ph.Person_id = :Person_id ";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter = " ph.Person_id in ({$data['person_in']}) ";
		}

		return $this->queryResult("
    		select
    			ph.PersonHeight_id as \"PersonHeight_id\",
    			ph.PersonHeight_Height as \"PersonHeight_Height\",
    			to_char(ph.PersonHeight_setDT, 'DD.MM.YYYY') as \"PersonHeight_setDate\",
    			wmt.HeightMeasureType_Name as \"HeightMeasureType_Name\",
    			wat.HeightAbnormType_Name as \"HeightAbnormType_Name\",
    			ph.Person_id as \"Person_id\"
    		from
    			v_PersonHeight ph
    			left join v_HeightMeasureType wmt on wmt.HeightMeasureType_id = ph.HeightMeasureType_id
    			left join v_HeightAbnormType wat on wat.HeightAbnormType_id = ph.HeightAbnormType_id
    		where {$filter}
    		{$order}
    		{$limit}
    	", array(
			'Person_id' => $data['Person_id']
		));
	}

	/**
	 * Получение последнего измерения роста пациента для панели антропометрических данных
	 */
	function loadLastPersonHeightMeasure($data) {
		$filter = " ph.Person_id = :Person_id";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter = " ph.Person_id in ({$data['person_in']}) ";
		}

		return $this->queryResult("
    		select
    			ph.PersonHeight_id,
    			ph.PersonHeight_Height as \"PersonHeight_Height\",
    			to_char(ph.PersonHeight_setDT, 'DD.MM.YYYY') as \"PersonHeight_setDate\",
    			wmt.HeightMeasureType_Name,
    			wat.HeightAbnormType_Name,
    			ph.Person_id
    		from
    			v_PersonHeight ph
    			left join v_HeightMeasureType wmt on wmt.HeightMeasureType_id = ph.HeightMeasureType_id
    			left join v_HeightAbnormType wat on wat.HeightAbnormType_id = ph.HeightAbnormType_id
    		where {$filter}
    		order by ph.PersonHeight_setDT desc
    		limit 1
    	", array(
			'Person_id' => $data['Person_id']
		));
	}
}