<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'models/MorbusNephroDisp_model.php');

class Ufa_MorbusNephroDisp_model extends MorbusNephroDisp_model {
	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr['endratedt']['alias'] = 'MorbusNephroDisp_EndDate';
		return $arr;
	}

	/**
	 *  Читает для грида и панели просмотра
	 */
	function doLoadGrid($data)
	{
		if (empty($data['scenario'])) {
			$data['scenario'] = self::SCENARIO_LOAD_GRID;
		}
		$this->applyData($data);
		$this->_validate();
		$queryParams = array(
			'MorbusNephro_id' => $this->MorbusNephro_id,
			'isDinamic' => $this->isDinamic,
		);

		$queryParams['Evn_id'] = isset($data['Evn_id']) ? $data['Evn_id'] : null;

		$idAlias = $this->objectSysNick . '_id';
		$dateAlias = $this->objectSysNick . '_Date';

		if (empty($data['isOnlyLast'])) {
			$sql = "
				SELECT
					accessType,
					$idAlias,
					MorbusNephro_id,
					Rate_id,
					RateType_Name,
					Rate_ValueStr,
					MorbusNephro_pid,
					NephroCkdEpi_value,
					Unit_Name as CreatinineUnitType_Name,
					convert(varchar(10),a.date,104) as $dateAlias,
					convert(varchar(10),a.MorbusNephroDisp_EndDate,104) as MorbusNephroDisp_EndDate
				FROM
				(
					select
						case when MV.Morbus_disDT is null then 'edit' else 'view' end as accessType,
						t.MorbusNephroRate_id as $idAlias,
						t.MorbusNephro_id,
						t.Rate_id,
						r.RateType_id,
						rt.RateType_Name,
						r.Rate_ValueStr,
						t.MorbusNephroRate_rateDT as date,
						:Evn_id as MorbusNephro_pid,
						NCE.NephroCkdEpi_value,
						u.Unit_Name,
						r.Unit_id as Unit_id,
						t.MorbusNephroRate_endRateDT as MorbusNephroDisp_EndDate
					from 
						v_MorbusNephroRate t (nolock)
						inner join v_Rate r (nolock) on r.Rate_id = t.Rate_id
						inner join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
						inner join v_MorbusNephro MV (nolock) on MV.MorbusNephro_id = t.MorbusNephro_id
						left join r2.v_NephroCkdEpi NCE with(nolock) on NCE.MorbusNephroRate_id = t.MorbusNephroRate_id
						left join unit u with(nolock) on u.Unit_id = r.Unit_id
					where
						t.MorbusNephro_id = :MorbusNephro_id
						and t.MorbusNephroRate_IsDinamic = :isDinamic

					UNION

					select
						'view' as accessType,
						NephroCRITypeHistory_id as $idAlias,
						:MorbusNephro_id as MorbusNephro_id,
						'' as Rate_id,
						'' as RateType_id,
						'Стадия ХБП' as RateType_Name,
						NephroCRIType_Name as Rate_ValueStr,
						NephroCRITypeHistory_insDT as date,
						:Evn_id as MorbusNephro_rid,
						'' as NephroCkdEpi_value,
						'' as Unit_Name,
						'' as Unit_id,
						'' as endRateDT
					from
						r2.v_NephroCRITypeHistory with(nolock)
					where
						MorbusNephro_id = :MorbusNephro_id
				) a
				order by date desc, $idAlias desc
			";
		} else {
			$sql = "
				SELECT * FROM (
					SELECT
						t.accessType,
						t.$idAlias,
						t.MorbusNephro_id,
						t.Rate_id,
						case when t.RateType_Name is null
							then rt.RateType_Name
							else t.RateType_Name
						end as RateType_Name,
						t.Rate_ValueStr,
						t.MorbusNephro_pid,
						t.NephroCkdEpi_value,
						t.Unit_Name as CreatinineUnitType_Name,
						convert(varchar(10), t.date, 104) as $dateAlias,
						convert(varchar(10), t.MorbusNephroDisp_EndDate, 104) as MorbusNephroDisp_EndDate
					FROM v_ratetype rt CROSS apply
					(
						SELECT top 1
							case when MV.Morbus_disDT is null then 'edit' else 'view' end as accessType,
							mnr.MorbusNephroRate_id as $idAlias,
							mnr.MorbusNephro_id,
							r.Rate_id,
							r.RateType_id,
							null as RateType_Name,
							r.Rate_ValueStr,
							mnr.MorbusNephroRate_rateDT as date,
							:Evn_id as MorbusNephro_pid,
							NCE.NephroCkdEpi_value,
							u.Unit_Name,
							r.Unit_id,
							mnr.MorbusNephroRate_endRateDT as MorbusNephroDisp_EndDate
						FROM v_Rate r
						INNER JOIN v_Unit u ON u.Unit_id = r.Unit_id
						INNER JOIN v_MorbusNephroRate mnr ON mnr.Rate_id = r.Rate_id
						INNER JOIN v_MorbusNephro MV (nolock) ON MV.MorbusNephro_id = mnr.MorbusNephro_id
						LEFT JOIN r2.v_NephroCkdEpi NCE with(nolock) ON NCE.MorbusNephroRate_id = mnr.MorbusNephroRate_id
						WHERE
							mnr.Rate_id = r.Rate_id
							AND mnr.MorbusNephro_id = :MorbusNephro_id
							AND rt.RateType_id = r.RateType_id
							AND mnr.MorbusNephroRate_IsDinamic = :isDinamic
						order by MorbusNephroRate_rateDT desc
					) t
					union
					select top 1
						'view' as accessType,
						NephroCRITypeHistory_id as $idAlias,
						:MorbusNephro_id as MorbusNephro_id,
						'' as Rate_id,
						'Стадия ХБП' as RateType_Name,
						NephroCRIType_Name as Rate_ValueStr,
						:Evn_id as MorbusNephro_pid,
						'' as NephroCkdEpi_value,
						'' as CreatinineUnitType_Name,
						convert(varchar(10), NephroCRITypeHistory_insDT, 104) as $dateAlias,
						'' as  MorbusNephroDisp_EndDate
					from r2.v_NephroCRITypeHistory with(nolock)
					where MorbusNephro_id = :MorbusNephro_id
				) tt
				order by convert(datetime, MorbusNephroDisp_Date, 104) desc
			";
		}

		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Читает одну строку для формы редактирования
	 * @param array $data
	 * @return array
	 */
	function doLoadEditForm($data)
	{
		$params = array();
		$params['MorbusNephroDisp_id'] = $data['MorbusNephroDisp_id'];
		$query = "
			select
				t.MorbusNephroRate_id as MorbusNephroDisp_id,
				t.MorbusNephro_id,
				t.Rate_id,
				r.RateType_id,
				r.Unit_id as Unit_id,
				r.Rate_ValueStr,
				convert(varchar(10), t.MorbusNephroRate_endRateDT, 104) as MorbusNephroDisp_EndDate,
				convert(varchar(10), t.MorbusNephroRate_rateDT, 104) as MorbusNephroDisp_Date,
				NCE.NephroCkdEpi_id,
				NCE.NephroCkdEpi_value
			from 
				v_MorbusNephroRate t (nolock)
				inner join v_Rate r (nolock) on r.Rate_id = t.Rate_id
				left join r2.v_NephroCkdEpi NCE with(nolock) on NCE.MorbusNephroRate_id = t.MorbusNephroRate_id
			where
				t.MorbusNephroRate_id = :MorbusNephroDisp_id
		";
		$result = $this->db->query($query, $params);

		if (!is_object($result)) return false;

		$result = $result->result('array')[0];

		return array(array(
			'MorbusNephroDisp_id' => $result['MorbusNephroDisp_id'],
			'Rate_id'             => $result['Rate_id'],
			'MorbusNephro_id'     => $result['MorbusNephro_id'],
			'MorbusNephroDisp_Date' => $result['MorbusNephroDisp_Date'],
			'RateType_id'           => $result['RateType_id'],
			'Unit_id'     => $result['Unit_id'],
			'Rate_ValueStr'         => $result['Rate_ValueStr'],
			'NephroCkdEpi_id'       => $result['NephroCkdEpi_id'],
			'MorbusNephroDisp_EndDate' => $result['MorbusNephroDisp_EndDate']
		));
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
	}

	/**
	 * Результаты услуг креатинин крови
	 * @param array $data
	 */
	function getUslugaCreatineResult($data){

		if (empty($data['scenario'])) {
			$data['scenario'] = self::SCENARIO_LOAD_GRID;
		}
		$this->applyData($data);
		$this->_validate();
		$params = array();
		$params['MorbusNephro_id'] = $this->MorbusNephro_id;
		$params['isDinamic'] = $this->isDinamic;
		$params['Evn_id'] = $data['Evn_id'];

		$isOnlyLast = !empty($data['isOnlyLast']) ? ' TOP 1 ' :  '';

		$query = "
			SELECT $isOnlyLast *
			FROM
			(
				select
					'view' as accessType,
					t.MorbusNephroRate_id as NephroBloodCreatinine_id,
					t.MorbusNephro_id,
					rt.RateType_Name as uslugaName,
					r.Rate_ValueStr as uslugaValue,
					convert(varchar(10), t.MorbusNephroRate_rateDT, 104) as uslugaSetDate,
					CUT.CreatinineUnitType_Name as uslugaUnit,
					:Evn_id as MorbusNephro_pid
				from 
					v_MorbusNephroRate t (nolock)
					inner join v_Rate r (nolock) on r.Rate_id = t.Rate_id
					inner join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
					inner join v_MorbusNephro MV (nolock) on MV.MorbusNephro_id = t.MorbusNephro_id
					left join r2.v_NephroCkdEpi NCE with(nolock) on NCE.MorbusNephroRate_id = t.MorbusNephroRate_id
					left join r2.v_CreatinineUnitType CUT with(nolock) on CUT.CreatinineUnitType_Code = NCE.CreatinineUnitType_id
				where
					t.MorbusNephro_id = :MorbusNephro_id
					and t.MorbusNephroRate_IsDinamic = :isDinamic
					and r.RateType_id = 109
				
				union

				select
					'view' as accessType,
					us.UslugaComplex_id as NephroBloodCreatinine_id,
					:MorbusNephro_id as MorbusNephro_id,
					'Креатинин крови' as uslugaName,
					us.EvnUsluga_Result as uslugaValue,
					convert(varchar(10), us.EvnUsluga_setDate, 104) as uslugaSetDate,
					'' as uslugaUnit,
					:Evn_id as MorbusNephro_rid

				from v_UslugaComplex uc with(nolock)
				inner join v_EvnUsluga us with(nolock) on uc.UslugaComplex_id = us.UslugaComplex_id 
				
				where 
					us.EvnUsluga_setDate is not null  
					and uc.UslugaComplex_Code = 'A09.05.020' 
					and Person_id = :Evn_id
			) Usluga
			
			order by uslugaSetDate desc
			";


		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result('array');

		foreach ($result as $key=>$res){

			$uslugaValue = $res['uslugaValue'];

			if(empty($uslugaValue)) continue;

			$uslugaResult = json_decode($uslugaValue);

			if(!is_object($uslugaResult)) continue;

			$result[$key]['uslugaValue'] = $uslugaResult->EUD_value;
			$result[$key]['uslugaUnit'] = $uslugaResult->EUD_unit_of_measurement;
			
		}

		return $result;
	}

	/**
	 * Сохранение результата расчета СКФ
	 * @param array $data
	 */
	function saveCkdEpiResult($data) {

		$params = array();
		$params['MorbusNephroRate_id'] = $data['MorbusNephroRate_id'];
		$params['CreatinineUnitType_id'] = $data['CreatinineUnitType_id'];
		$params['NephroCkdEpi_value'] =  $data['NephroCkdEpi_value'];
		$params['pmUser_id'] = $data['session']['pmuser_id'];
		$params['NephroCkdEpi_id'] = $data['NephroCkdEpi_id'] ? $data['NephroCkdEpi_id'] : null;

		$procedure = $data['NephroCkdEpi_id'] ? 'p_NephroCkdEpi_upd' : 'p_NephroCkdEpi_ins';
		$NephroCkdEpi_id = $data['NephroCkdEpi_id'] ? '@NephroCkdEpi_id = :NephroCkdEpi_id output,' : '@NephroCkdEpi_id = @NephroCkdEpi_id output,';
		$query = " 
			DECLARE
				@Error_Code int,
				@Error_Message varchar(4000),
				@NephroCkdEpi_id bigint;
			EXEC r2.$procedure
				@MorbusNephroRate_id   = :MorbusNephroRate_id,
				@CreatinineUnitType_id = :CreatinineUnitType_id,
				@NephroCkdEpi_value    = :NephroCkdEpi_value,
				@pmUser_id             = :pmUser_id,
				$NephroCkdEpi_id
				@Error_Code            = @Error_Code output,
				@Error_Message         = @Error_Message output
			SELECT
				@NephroCkdEpi_id as NephroCkdEpi_id,
				@Error_Code      as Error_Code,
				@Error_Message   as Error_Message
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}

		$result = $result->result('array');

		
		$lastRate = $this->getLastRate($data);

		if($lastRate)
			$result[0]['isLastRate'] = $lastRate['MorbusNephroRate_id'] == $data['MorbusNephroRate_id'];


		return $result;
		
	}

	/**
	 * Получение последней записи "Креатинин крови"
	 */
	function getLastRate($data) {
		$params = array();
		$params['MorbusNephro_id'] = $data['MorbusNephro_id'];
		$query = "
			select TOP 1
				MNR.MorbusNephro_id,
				MNR.MorbusNephroRate_id,
				MNR.Rate_id,
				convert(varchar,MNR.MorbusNephroRate_rateDT,104) as MorbusNephroRate_rateDT
			from v_MorbusNephroRate MNR with(nolock)
			inner join v_Rate R with(nolock) on R.Rate_id = MNR.Rate_id and RateType_id = 109
			where MorbusNephro_id = :MorbusNephro_id
			order by MNR.MorbusNephroRate_rateDT desc";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) return 0;
		$result = $result->result('array');

		return empty($result) ? false : $result[0]; //['MorbusNephroRate_id']
	}

	/**
	 * Получение данных регистра по MorbusNephro_id
	 */
	function doLoadDispList($data) {
		$params = array(
			'MorbusNephro_id' => $data['MorbusNephro_id']
		);
		$query = "
		select
			mnr.MorbusNephro_id as id,
			mnr.Rate_id as rateId,
			convert(varchar, mnr.MorbusNephroRate_rateDT, 2) as begDT,
			convert(varchar, mnr.MorbusNephroRate_endRateDT, 2) as endDT,
			r.RateType_id as rateTypeId,
			r.Rate_ValueStr as value,
			r.Unit_id as rateUnitTypeId
		from
			(
				select
					max(mnr.MorbusNephroRate_rateDT) as MorbusNephroRate_rateDT,
					r.RateType_id
				from  v_MorbusNephroRate mnr
				inner join v_Rate r on mnr.Rate_id = r.Rate_id
				where
					mnr.MorbusNephro_id = :MorbusNephro_id
				group by RateType_id
			) fr
		inner join MorbusNephroRate mnr on mnr.MorbusNephroRate_rateDT = fr.MorbusNephroRate_rateDT
		inner join v_Rate r on mnr.Rate_id = r.Rate_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка использованных схем
	 */
	function doLoadUsedSchemeList($data) {
		$params = array(
			'MorbusNephro_id' => $data['MorbusNephro_id']
		);
		$query = "select
			distinct NephroDrugScheme_id
		from v_MorbusNephroDrug
		where MorbusNephro_id = :MorbusNephro_id";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка входящих правил
	 */
	function getInputRules($name) {
		$rules = parent::getInputRules($name);
		switch ($name) {
			case 'doLoadDispList':
				$rules['MorbusNephro_id'] = array(
					'field' => 'MorbusNephro_id',
					'rules' => 'trim',
					'type' => 'int'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Выполняет запрос к БД
	 */
	function execQuery($query,$data=null) {

		if ($data) {
			$result = $this->db->query($query,$data);
		} else {
			$result = $this->db->query($query);
		}

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}