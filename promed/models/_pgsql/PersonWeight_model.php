<?php defined('BASEPATH') or die ('No direct script access allowed');
class PersonWeight_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление записи о весе
	 */
	function deletePersonWeight($data) {
		$query = "			
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonWeight_del (
				PersonWeight_id := :PersonWeight_id
			);
		";

		$result = $this->db->query($query, array(
			'PersonWeight_id' => $data['PersonWeight_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных об измерениях массы человека
	 */
	function getPersonWeightViewData($data) {
		$query = "
			select
				PW.Person_id as \"Person_id\",
				0 as \"Children_Count\",
				PW.PersonWeight_id as \"PersonWeight_id\",
				PW.PersonWeight_id as \"Anthropometry_id\",
				to_char(PW.PersonWeight_setDT, 'DD.MM.YYYY') as \"PersonWeight_setDate\",
				case when pw.Okei_id = 36 then
					pw.PersonWeight_Weight::numeric / 1000
				else
					pw.PersonWeight_Weight
				end as \"PersonWeight_Weight\",
				case 
					when coalesce(PH.PersonHeight_Height, 0) > 0 and pw.PersonWeight_Weight is not null
				then
					cast(ROUND(cast(
						case when pw.Okei_id = 36 then
							cast(pw.PersonWeight_Weight as numeric) / 1000
						else
							pw.PersonWeight_Weight
						end
					as numeric)/POWER(0.01*cast(PH.PersonHeight_Height as numeric),2),2) as varchar(10))
				else
					''
				end as \"Weight_Index\",
				coalesce(WMT.WeightMeasureType_Name, '') as \"WeightMeasureType_Name\",
				coalesce(IsAbnorm.YesNo_Name, '') as \"PersonWeight_IsAbnorm\",
				coalesce(WAT.WeightAbnormType_Name, '') as \"WeightAbnormType_Name\",
				PW.pmUser_insID as \"pmUser_insID\",
				coalesce(PU.pmUser_Name, '') as \"pmUser_Name\"
			from
				v_PersonWeight PW
				inner join WeightMeasureType WMT on WMT.WeightMeasureType_id = PW.WeightMeasureType_id
				left join YesNo IsAbnorm on IsAbnorm.YesNo_id = PW.PersonWeight_IsAbnorm
				left join WeightAbnormType WAT on WAT.WeightAbnormType_id = PW.WeightAbnormType_id
				left join v_pmUser PU on PU.pmUser_id = coalesce(PW.pmUser_updID, PW.pmUser_insID)
				left join lateral (
					select PersonHeight_Height
					from v_PersonHeight
					where Person_id = :Person_id
						and HeightMeasureType_id is not null
						and PersonHeight_setDT <= PW.PersonWeight_setDT
					order by PersonHeight_setDT desc
					limit 1
				) PH on true
			where
				PW.Person_id = :Person_id
			order by
				PW.PersonWeight_setDT
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
	 * Получение данных для просмотра/редактирования веса
	 */
	function loadPersonWeightEditForm($data) {
		$query = "
			select
				PW.PersonWeight_id as \"PersonWeight_id\",
				PW.Person_id as \"Person_id\",
				PW.Evn_id as \"Evn_id\",
				PW.Server_id as \"Server_id\",
				PW.WeightAbnormType_id as \"WeightAbnormType_id\",
				PW.WeightMeasureType_id as \"WeightMeasureType_id\",
				PW.PersonWeight_IsAbnorm as \"PersonWeight_IsAbnorm\",
				PW.Okei_id as \"Okei_id\",
				pw.PersonWeight_Weight as \"PersonWeight_Weight\",
				to_char(PW.PersonWeight_setDT, 'DD.MM.YYYY') as \"PersonWeight_setDate\"
			from
				v_PersonWeight PW
			where (1 = 1)
				and PW.PersonWeight_id = :PersonWeight_id
			limit 1
		";
		$result = $this->db->query($query, array(
			'PersonWeight_id' => $data['PersonWeight_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка измерений массы пациента для ЭМК
	 */
	function loadPersonWeightPanel($data) {

		$limit = '';
		$order = '';
		if(isset($data['limiter'])) {
			$limit = " limit {$data['limiter']} ";
			$order = " order by pw.PersonWeight_setDT desc, pw.PersonWeight_id desc ";
		}
		$filter = " pw.Person_id = :Person_id ";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter = " pw.Person_id in ({$data['person_in']}) ";
		}

		return $this->queryResult("
    		select
    			pw.PersonWeight_id as \"PersonWeight_id\",
    			case when pw.Okei_id = 36 then
					pw.PersonWeight_Weight::numeric / 1000
				else
					pw.PersonWeight_Weight::numeric
				end as \"PersonWeight_Weight\",
    			to_char(pw.PersonWeight_setDT, 'DD.MM.YYYY') as \"PersonWeight_setDate\",
    			wmt.WeightMeasureType_Name as \"WeightMeasureType_Name\",
    			wat.WeightAbnormType_Name as \"WeightAbnormType_Name\",
    			case 
					when coalesce(PH.PersonHeight_Height, 0) > 0 and pw.PersonWeight_Weight is not null then
						cast(ROUND(cast(
							case when pw.Okei_id = 36 then
								cast(pw.PersonWeight_Weight as numeric) / 1000
							else
								pw.PersonWeight_Weight
							end
						as numeric)/POWER(0.01*cast(PH.PersonHeight_Height as numeric),2),2) as varchar(10))
					else ''
				end as \"PersonWeight_Imt\",
    			pw.Person_id as \"Person_id\"
    		from
    			v_PersonWeight pw
    			left join v_WeightMeasureType wmt on wmt.WeightMeasureType_id = pw.WeightMeasureType_id
    			left join v_WeightAbnormType wat on wat.WeightAbnormType_id = pw.WeightAbnormType_id
    			left join lateral (
					select PersonHeight_Height
					from v_PersonHeight
					where Person_id = :Person_id
						and HeightMeasureType_id is not null
						and PersonHeight_setDT <= PW.PersonWeight_setDT
					order by PersonHeight_setDT desc
					limit 1
				) PH on true
    		where {$filter}
    		{$order}
    		{$limit}
    	", array(
			'Person_id' => $data['Person_id']
		));
	}

	/**
	 * Получение последнего измерения массы пациента для антропометрических параметров
	 */
	function loadLastPersonWeightMeasure($data) {
		$filter = " pw.Person_id = :Person_id ";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter = " pw.Person_id in ({$data['person_in']}) ";
		}

		$result = $this->queryResult("
    		select
    		    pw.PersonWeight_id as \"PersonWeight_id\",
    			case when pw.Okei_id = 36 then
					cast(pw.PersonWeight_Weight as float) / 1000
				else
					pw.PersonWeight_Weight::float
				end as \"PersonWeight_Weight\",
    			to_char(pw.PersonWeight_setDT, 'DD.MM.YYYY') as \"PersonWeight_setDate\",
    			wmt.WeightMeasureType_Name as \"WeightMeasureType_Name\",
    			wat.WeightAbnormType_Name as \"WeightAbnormType_Name\",
    			pw.Person_id as \"Person_id\"
    		from
    			v_PersonWeight pw
    			left join v_WeightMeasureType wmt on wmt.WeightMeasureType_id = pw.WeightMeasureType_id
    			left join v_WeightAbnormType wat on wat.WeightAbnormType_id = pw.WeightAbnormType_id
    		where {$filter}
    		order by PersonWeight_setDT desc
			limit 1
    	", array(
			'Person_id' => $data['Person_id']
		));
		return $result;
	}

	/**
	 * Получение грида с массой
	 */
	function loadPersonWeightGrid($data) {
		$filter = "(1 = 1)";
		$join_str = "";

		if ( !empty($data['mode']) && $data['mode'] == 'child' ) {
			$filter .= "
				and (PWSD.PersonWeight_setDT is null or PW.PersonWeight_setDT <= PWSD.PersonWeight_setDT)
			";
			$join_str = "
				left join lateral (
					select
						PWtmp.PersonWeight_setDT
					from
						v_PersonWeight PWtmp
						inner join WeightMeasureType WMTtmp on WMTtmp.WeightMeasureType_id = PWtmp.WeightMeasureType_id
					where PWtmp.Person_id = :Person_id
						and WMTtmp.WeightMeasureType_Code = 2
					limit 1
				) PWSD on true
			";
		}
		$params = array(
			'Person_id' => $data['Person_id']
		);

		if ( isset($data['WeightMeasureType_id']) && (!empty($data['WeightMeasureType_id']))) {
			$filter = $filter.' AND PW.WeightMeasureType_id = :WeightMeasureType_id ';
			$params['WeightMeasureType_id'] = $data['WeightMeasureType_id'];
		}

		$query = "
			select
				PW.PersonWeight_id as \"PersonWeight_id\",
				PW.Person_id as \"Person_id\",
				PW.Evn_id as \"Evn_id\",
				PW.PersonWeight_IsAbnorm as \"PersonWeight_IsAbnorm\",
				PW.WeightAbnormType_id as \"WeightAbnormType_id\",
				PW.WeightMeasureType_id as \"WeightMeasureType_id\",
				coalesce(WMT.WeightMeasureType_Code, 0) as \"WeightMeasureType_Code\",
				to_char(PW.PersonWeight_setDT, 'DD.MM.YYYY') as \"PersonWeight_setDate\",
				pw.PersonWeight_Weight as \"PersonWeight_Weight\",
				cast(cast(pw.PersonWeight_Weight AS numeric) as varchar(10)) + ' ' + ok.Okei_NationSymbol as \"PersonWeight_text\",
				pw.Okei_id as \"Okei_id\",
				1 as \"RecordStatus_Code\"
			from
				v_PersonWeight PW
				inner join WeightMeasureType WMT on WMT.WeightMeasureType_id = PW.WeightMeasureType_id
				left join v_Okei ok on pw.Okei_id = ok.Okei_id
				" . $join_str . "
			where " . $filter . "
				and PW.Person_id = :Person_id
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
	 * Сохранение веса
	 */
	function savePersonWeight($data) {
		$procedure = "p_PersonWeight_ins";

		if ( !empty($data['PersonWeight_Weight']) ) {
			$data['PersonWeight_Weight'] = floatval(str_replace(',','.',$data['PersonWeight_Weight']));
		}


		if ( !empty($data['PersonWeight_id']) ) {
			$procedure = "p_PersonWeight_upd";
		}
		else if($data['WeightMeasureType_id']==1){
			$query = 'select PersonWeight_id as "PersonWeight_id"  from v_PersonWeight where WeightMeasureType_id=1 and Person_id=:Person_id';
			$result = $this->db->query($query, array('Person_id'=>$data['Person_id']));
			if ( is_object($result) ) {
				$res = $result->result('array');
				if(count($res)>0){
					$procedure = "p_PersonWeight_upd";
					$data['PersonWeight_id'] = $res[0]['PersonWeight_id'];
				}
			}
		}
		$query = "
			select
				PersonWeight_id as \"PersonWeight_id\",
				Error_Code as \"Error_Code\", 
				Error_Message as \"Error_Msg\"
			from {$procedure} (
				Server_id := :Server_id,
				PersonWeight_id := :PersonWeight_id,
				Person_id := :Person_id,
				PersonWeight_setDT := :PersonWeight_setDate,
				PersonWeight_Weight := :PersonWeight_Weight,
				PersonWeight_IsAbnorm := :PersonWeight_IsAbnorm,
				WeightAbnormType_id := :WeightAbnormType_id,
				WeightMeasureType_id := :WeightMeasureType_id,
				Okei_id := :Okei_id,
				Evn_id := :Evn_id,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'PersonWeight_id' => (!empty($data['PersonWeight_id']) ? $data['PersonWeight_id'] : NULL),
			'Person_id' => $data['Person_id'],
			'PersonWeight_setDate' => $data['PersonWeight_setDate'],
			'PersonWeight_Weight' => $data['PersonWeight_Weight'],
			'PersonWeight_IsAbnorm' => (!empty($data['PersonWeight_IsAbnorm']) ? $data['PersonWeight_IsAbnorm'] : NULL),
			'WeightAbnormType_id' => (!empty($data['WeightAbnormType_id']) ? $data['WeightAbnormType_id'] : NULL),
			'WeightMeasureType_id' => $data['WeightMeasureType_id'],
			'Evn_id'=>(!empty($data['Evn_id']) ? $data['Evn_id'] : NULL),
			'Okei_id' => $data['Okei_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения массы пациента)'));
		}
	}
}
