<?php defined('BASEPATH') or die ('No direct script access allowed');
class PersonWeight_model extends swModel {
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PersonWeight_del
				@PersonWeight_id = :PersonWeight_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				PW.Person_id,
				0 as Children_Count,
				PW.PersonWeight_id,
				PW.PersonWeight_id as Anthropometry_id,
				convert(varchar(10), PW.PersonWeight_setDT, 104) as PersonWeight_setDate,
				case when pw.Okei_id = 36 then
					cast(pw.PersonWeight_Weight as float) / 1000
				else
					pw.PersonWeight_Weight
				end as PersonWeight_Weight,
				case 
					when ISNULL(PH.PersonHeight_Height, 0) > 0 and pw.PersonWeight_Weight is not null
				then
					convert(varchar(10),ROUND(cast(
						case when pw.Okei_id = 36 then
							cast(pw.PersonWeight_Weight as float) / 1000
						else
							pw.PersonWeight_Weight
						end
					as float)/POWER(0.01*cast(PH.PersonHeight_Height as float),2),2))
				else
					''
				end as Weight_Index,
				ISNULL(WMT.WeightMeasureType_Name, '') as WeightMeasureType_Name,
				ISNULL(IsAbnorm.YesNo_Name, '') as PersonWeight_IsAbnorm,
				ISNULL(WAT.WeightAbnormType_Name, '') as WeightAbnormType_Name,
				PW.pmUser_insID,
				ISNULL(PU.pmUser_Name, '') as pmUser_Name
			from
				v_PersonWeight PW with (nolock)
				inner join WeightMeasureType WMT with (nolock) on WMT.WeightMeasureType_id = PW.WeightMeasureType_id
				left join YesNo IsAbnorm with (nolock) on IsAbnorm.YesNo_id = PW.PersonWeight_IsAbnorm
				left join WeightAbnormType WAT with (nolock) on WAT.WeightAbnormType_id = PW.WeightAbnormType_id
				left join v_pmUser PU with (nolock) on PU.pmUser_id = ISNULL(PW.pmUser_updID, PW.pmUser_insID)
				outer apply (
					select top 1 PersonHeight_Height
					from v_PersonHeight with (nolock)
					where Person_id = :Person_id
						and HeightMeasureType_id is not null
						and PersonHeight_setDT <= PW.PersonWeight_setDT
					order by PersonHeight_setDT desc
				) PH
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
			select top 1
				PW.PersonWeight_id,
				PW.Person_id,
				PW.Evn_id,
				PW.Server_id,
				PW.WeightAbnormType_id,
				PW.WeightMeasureType_id,
				PW.PersonWeight_IsAbnorm,
				PW.Okei_id,
				pw.PersonWeight_Weight as PersonWeight_Weight,
				convert(varchar(10), PW.PersonWeight_setDT, 104) as PersonWeight_setDate
			from
				v_PersonWeight PW with (nolock)
			where (1 = 1)
				and PW.PersonWeight_id = :PersonWeight_id
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
    		$limit = " top {$data['limiter']} ";
		    $order = " order by pw.PersonWeight_setDT desc, pw.PersonWeight_id desc ";
	    }
		$filter = " pw.Person_id = :Person_id ";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter = " pw.Person_id in ({$data['person_in']}) ";
		}

    	return $this->queryResult("
    		select {$limit}
    			pw.PersonWeight_id,
    			case when pw.Okei_id = 36 then
					cast(pw.PersonWeight_Weight as float) / 1000
				else
					pw.PersonWeight_Weight
				end as PersonWeight_Weight,
    			convert(varchar(10), pw.PersonWeight_setDT, 104) as PersonWeight_setDate,
    			wmt.WeightMeasureType_Name,
    			wat.WeightAbnormType_Name,
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
				end as PersonWeight_Imt,
    			pw.Person_id
    		from
    			v_PersonWeight pw (nolock)
    			left join v_WeightMeasureType wmt (nolock) on wmt.WeightMeasureType_id = pw.WeightMeasureType_id
    			left join v_WeightAbnormType wat (nolock) on wat.WeightAbnormType_id = pw.WeightAbnormType_id
    			outer apply (
					select top 1 
					PersonHeight_Height
					from v_PersonHeight with (nolock)
					where Person_id = :Person_id
						and HeightMeasureType_id is not null
						and PersonHeight_setDT <= PW.PersonWeight_setDT
					order by PersonHeight_setDT desc
				) PH
    		where {$filter}
    		{$order}
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

		return $this->queryResult("
    		select top 1
    			pw.PersonWeight_id,
    			case when pw.Okei_id = 36 then
					cast(pw.PersonWeight_Weight as float) / 1000
				else
					pw.PersonWeight_Weight
				end as PersonWeight_Weight,
    			convert(varchar(10), pw.PersonWeight_setDT, 104) as PersonWeight_setDate,
    			wmt.WeightMeasureType_Name,
    			wat.WeightAbnormType_Name,
    			pw.Person_id
    		from
    			v_PersonWeight pw (nolock)
    			left join v_WeightMeasureType wmt (nolock) on wmt.WeightMeasureType_id = pw.WeightMeasureType_id
    			left join v_WeightAbnormType wat (nolock) on wat.WeightAbnormType_id = pw.WeightAbnormType_id
    		where {$filter}
    		order by PersonWeight_setDT desc
    	", array(
			'Person_id' => $data['Person_id']
		));
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
				outer apply(
					select top 1
						PWtmp.PersonWeight_setDT
					from
						v_PersonWeight PWtmp with (nolock)
						inner join WeightMeasureType WMTtmp with (nolock) on WMTtmp.WeightMeasureType_id = PWtmp.WeightMeasureType_id
					where PWtmp.Person_id = :Person_id
						and WMTtmp.WeightMeasureType_Code = 2
				) PWSD
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
				PW.PersonWeight_id,
				PW.Person_id,
				PW.Evn_id,
				PW.PersonWeight_IsAbnorm,
				PW.WeightAbnormType_id,
				PW.WeightMeasureType_id,
				ISNULL(WMT.WeightMeasureType_Code, 0) as WeightMeasureType_Code,
				convert(varchar(10), PW.PersonWeight_setDT, 104) as PersonWeight_setDate,
				pw.PersonWeight_Weight,
				cast(cast(pw.PersonWeight_Weight AS float) as varchar(10)) + ' ' + ok.Okei_NationSymbol as PersonWeight_text,
				pw.Okei_id,
				1 as RecordStatus_Code
			from
				v_PersonWeight PW with (nolock)
				inner join WeightMeasureType WMT with (nolock) on WMT.WeightMeasureType_id = PW.WeightMeasureType_id
				left join v_Okei ok with (nolock) on pw.Okei_id = ok.Okei_id
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
			$query = 'select PersonWeight_id from v_PersonWeight with(nolock) where WeightMeasureType_id=1 and Person_id=:Person_id';
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :PersonWeight_id;

			exec " . $procedure . "
				@Server_id = :Server_id,
				@PersonWeight_id = @Res output,
				@Person_id = :Person_id,
				@PersonWeight_setDT = :PersonWeight_setDate,
				@PersonWeight_Weight = :PersonWeight_Weight,
				@PersonWeight_IsAbnorm = :PersonWeight_IsAbnorm,
				@WeightAbnormType_id = :WeightAbnormType_id,
				@WeightMeasureType_id = :WeightMeasureType_id,
				@Okei_id = :Okei_id,
				@Evn_id = :Evn_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PersonWeight_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
