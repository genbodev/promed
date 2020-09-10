<?php defined('BASEPATH') or die ('No direct script access allowed');
class PersonHeight_model extends swModel {
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PersonHeight_del
				@PersonHeight_id = :PersonHeight_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				PH.Person_id,
				0 as Children_Count,
				PH.PersonHeight_id,
				PH.PersonHeight_id as Anthropometry_id,
				convert(varchar(10), PH.PersonHeight_setDT, 104) as PersonHeight_setDate,
				cast(PH.PersonHeight_Height as float) as PersonHeight_Height,
				ISNULL(HMT.HeightMeasureType_Name, '') as HeightMeasureType_Name,
				ISNULL(IsAbnorm.YesNo_Name, '') as PersonHeight_IsAbnorm,
				ISNULL(HAT.HeightAbnormType_Name, '') as HeightAbnormType_Name,
				PH.pmUser_insID,
				ISNULL(PU.pmUser_Name, '') as pmUser_Name
			from
				v_PersonHeight PH with (nolock)
				inner join HeightMeasureType HMT with (nolock) on HMT.HeightMeasureType_id = PH.HeightMeasureType_id
				left join YesNo IsAbnorm with (nolock) on IsAbnorm.YesNo_id = PH.PersonHeight_IsAbnorm
				left join HeightAbnormType HAT with (nolock) on HAT.HeightAbnormType_id = PH.HeightAbnormType_id
				left join v_pmUser PU with (nolock) on PU.pmUser_id = ISNULL(PH.pmUser_updID, PH.pmUser_insID)
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
			select top 1
				PH.PersonHeight_id,
				PH.Person_id,
				PH.Evn_id,
				PH.Server_id,
				PH.HeightAbnormType_id,
				PH.HeightMeasureType_id,
				PH.PersonHeight_IsAbnorm,
				PH.PersonHeight_Height,
				convert(varchar(10), PH.PersonHeight_setDT, 104) as PersonHeight_setDate
			from
				v_PersonHeight PH with (nolock)
			where (1 = 1)
				and PH.PersonHeight_id = :PersonHeight_id
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
				outer apply(
					select top 1
						PHtmp.PersonHeight_setDT
					from
						v_PersonHeight PHtmp with (nolock)
						inner join HeightMeasureType HMTtmp with (nolock) on HMTtmp.HeightMeasureType_id = PHtmp.HeightMeasureType_id
					where PHtmp.Person_id = :Person_id
						and HMTtmp.HeightMeasureType_Code = 2
				) PHSD
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
				PH.PersonHeight_id,
				PH.Person_id,
				PH.Evn_id,
				PH.PersonHeight_IsAbnorm,
				PH.HeightAbnormType_id,
				PH.HeightMeasureType_id,
				ISNULL(HMT.HeightMeasureType_Code, 0) as HeightMeasureType_Code,
				convert(varchar(10), PH.PersonHeight_setDT, 104) as PersonHeight_setDate,
				PH.PersonHeight_Height,
				1 as RecordStatus_Code,
				case 
					when ISNULL(PH.PersonHeight_Height, 0) > 0 and pw.PersonWeight_Weight is not null then
						convert(varchar(10),ROUND(cast(
							case when pw.Okei_id = 36 then
								cast(pw.PersonWeight_Weight as float) / 1000
							else
								pw.PersonWeight_Weight
							end
						as float)/POWER(0.01*cast(PH.PersonHeight_Height as float),2),2))
					else ''
				end as PersonHeight_Imt
			from
				v_PersonHeight PH with (nolock)
			outer apply (
					select top 1 
					PersonWeight_Weight,
					Okei_id
					from v_PersonWeight with (nolock)
					where Person_id = :Person_id
						and HeightMeasureType_id is not null
						and PersonWeight_setDT <= PH.PersonHeight_setDT
					order by PersonWeight_setDT desc
			) pw
				inner join HeightMeasureType HMT with (nolock) on HMT.HeightMeasureType_id = PH.HeightMeasureType_id
				" . $join_str . "
			where " . $filter . "
				and PH.Person_id = :Person_id
			order by PH.PersonHeight_setDT
		";

//		var_dump($query);
//		var_dump($params);
//		die();

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
			$query = 'select PersonHeight_id from v_PersonHeight with(nolock) where HeightMeasureType_id=1 and Person_id=:Person_id';
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :PersonHeight_id;

			exec " . $procedure . "
				@Server_id = :Server_id,
				@PersonHeight_id = @Res output,
				@Person_id = :Person_id,
				@PersonHeight_setDT = :PersonHeight_setDate,
				@PersonHeight_Height = :PersonHeight_Height,
				@PersonHeight_IsAbnorm = :PersonHeight_IsAbnorm,
				@HeightAbnormType_id = :HeightAbnormType_id,
				@HeightMeasureType_id = :HeightMeasureType_id,
				@Okei_id = 2,
				@Evn_id = :Evn_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PersonHeight_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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


//		var_dump($queryParams);
//		die();
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения роста пациента)'));
		}
	}

	/**
	 * Получение списка измерений роста пациента для ЭМК
	 */
	function loadPersonHeightPanel($data) {

		$limit = '';
		$order = '';
		if(isset($data['limiter'])) {
			$limit = " top {$data['limiter']} ";
			$order = " order by ph.PersonHeight_setDT desc, ph.PersonHeight_id desc ";
		}
		$filter = " ph.Person_id = :Person_id ";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter = " ph.Person_id in ({$data['person_in']}) ";
		}

		return $this->queryResult("
    		select {$limit}
    			ph.PersonHeight_id,
    			ph.PersonHeight_Height,
    			convert(varchar(10), ph.PersonHeight_setDT, 104) as PersonHeight_setDate,
    			wmt.HeightMeasureType_Name,
    			wat.HeightAbnormType_Name,
    			ph.Person_id
    		from
    			v_PersonHeight ph (nolock)
    			left join v_HeightMeasureType wmt (nolock) on wmt.HeightMeasureType_id = ph.HeightMeasureType_id
    			left join v_HeightAbnormType wat (nolock) on wat.HeightAbnormType_id = ph.HeightAbnormType_id
    		where {$filter}
    		{$order}
    	", array(
			'Person_id' => $data['Person_id']
		));
	}

	/**
	 * Получение последнего измерения роста пациента для панели антропометрических данных
	 */
	function loadLastPersonHeightMeasure($data) {
		$filter = " ph.Person_id = :Person_id";

		if ( !empty($data['PersonHeight_Height']) ) {
			$data['PersonHeight_Height'] = floatval(str_replace(',','.',$data['PersonHeight_Height']));
		}

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter = " ph.Person_id in ({$data['person_in']}) ";
		}

		return $this->queryResult("
    		select top 1
    			ph.PersonHeight_id,
    			ph.PersonHeight_Height,
    			convert(varchar(10), ph.PersonHeight_setDT, 104) as PersonHeight_setDate,
    			wmt.HeightMeasureType_Name,
    			wat.HeightAbnormType_Name,
    			ph.Person_id
    		from
    			v_PersonHeight ph (nolock)
    			left join v_HeightMeasureType wmt (nolock) on wmt.HeightMeasureType_id = ph.HeightMeasureType_id
    			left join v_HeightAbnormType wat (nolock) on wat.HeightAbnormType_id = ph.HeightAbnormType_id
    		where {$filter}
    		order by ph.PersonHeight_setDT desc
    	", array(
			'Person_id' => $data['Person_id']
		));
	}
}