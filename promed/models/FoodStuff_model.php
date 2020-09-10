<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * FoodStuff_model - модель для работы с продуктами питания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.10.2013
 */

class FoodStuff_model extends CI_Model {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает список продуктов питания
	 */
	function loadFoodStuffGrid($data)
	{
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}

		$filters = '(1=1)';
		$params = array();

		if (!empty($data['FoodStuff_Code']))
		{
			$filters .= " and FoodStuff_Code like :FoodStuff_Code";
			$params['FoodStuff_Code'] = $data['FoodStuff_Code'].'%';
		}

		if (!empty($data['FoodStuff_Name']))
		{
			$filters .= " and FoodStuff_Name like :FoodStuff_Name";
			$params['FoodStuff_Name'] = $data['FoodStuff_Name'].'%';
		}

		$query = "
			select
				-- select
				FS.FoodStuff_id,
				FS.FoodStuff_Code,
				FS.FoodStuff_Name,
				FS.FoodStuff_Descr,
				FSP.FoodStuffPrice_Price,
				FS.FoodStuff_Protein,
				FS.FoodStuff_Fat,
				FS.FoodStuff_Carbohyd,
				FS.FoodStuff_Caloric
				-- end select
			from
				-- from
				v_FoodStuff FS with (nolock)
				outer apply (
					select top 1 FoodStuffPrice_Price
					from v_FoodStuffPrice with (nolock)
					where FoodStuff_id = FS.FoodStuff_id
					order by FoodStuffPrice_begDate desc
				) FSP
				-- end from
			where
				-- where
				{$filters}
				-- end where
			order by
				-- order by
				FS.FoodStuff_id
				-- end order by
			";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']),$params);
		$result_count = $this->db->query(getCountSQLPH($query),$params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		return false;
	}

	/**
	 * Возвращает данные для редактировния продукта питания
	 */
	function loadFoodStuffEditForm($data)
	{
		$query = "
			select
				FS.FoodStuff_id,
				FS.FoodStuff_Code,
				FS.FoodStuff_Name,
				FS.FoodStuff_Descr,
				FS.Okei_id,
				FS.FoodStuff_StorCond,
				FS.FoodStuff_Protein,
				FS.FoodStuff_Fat,
				FS.FoodStuff_Carbohyd,
				FS.FoodStuff_Caloric
			from
				v_FoodStuff FS with (nolock)
			where
				FS.FoodStuff_id = :FoodStuff_id
			";

		$result = $this->db->query($query, array('FoodStuff_id' => $data['FoodStuff_id']));

		if (is_object($result))
		{
			$response = array();
			$response = $result->result('array');
			return $response;
		}
		return false;
	}

	/**
	 * Возвращает данные о цене продукта питания для формы редактировния
	 */
	function loadFoodStuffPriceEditForm($data)
	{
		$query = "
			select
				FSP.FoodStuff_id,
				FSP.FoodStuffPrice_id,
				CONVERT(varchar(10),FSP.FoodStuffPrice_begDate,104) as FoodStuffPrice_begDate,
				FSP.FoodStuffPrice_Price
			from
				v_FoodStuffPrice FSP with (nolock)
			where
				FSP.FoodStuffPrice_id = :FoodStuffPrice_id
			";

		$result = $this->db->query($query, array('FoodStuffPrice_id' => $data['FoodStuffPrice_id']));

		if (is_object($result))
		{
			$response = array();
			$response = $result->result('array');
			return $response;
		}
		return false;
	}

	/**
	 * Возвращает данные о цзаменителе продукта питания для формы редактировния
	 */
	function loadFoodStuffSubstitEditForm($data)
	{
		$query = "
			select
				FSS.FoodStuff_id,
				FSS.FoodStuffSubstit_id,
				FSS.FoodStuff_sid,
				FSS.FoodStuffSubstit_Priority,
				FSS.FoodStuffSubstit_Coeff
			from
				v_FoodStuffSubstit FSS with (nolock)
			where
				FSS.FoodStuffSubstit_id = :FoodStuffSubstit_id
			";

		$result = $this->db->query($query, array('FoodStuffSubstit_id' => $data['FoodStuffSubstit_id']));

		if (is_object($result))
		{
			$response = array();
			$response = $result->result('array');
			return $response;
		}
		return false;
	}

	/**
	 * Возвращает данные о микронутриентах продукта питания для формы редактировния
	 */
	function loadFoodStuffMicronutrientEditForm($data)
	{
		$query = "
			select
				FSM.FoodStuff_id,
				FSM.FoodStuffMicronutrient_id,
				FSM.Micronutrient_id,
				M.Micronutrient_Name,
				FSM.FoodStuffMicronutrient_Content
			from
				v_FoodStuffMicronutrient FSM with (nolock)
				left join v_Micronutrient M with (nolock) on M.Micronutrient_id = FSM.Micronutrient_id
			where
				FSM.FoodStuffMicronutrient_id = :FoodStuffMicronutrient_id
			";

		$result = $this->db->query($query, array('FoodStuffMicronutrient_id' => $data['FoodStuffMicronutrient_id']));

		if (is_object($result))
		{
			$response = array();
			$response = $result->result('array');
			return $response;
		}
		return false;
	}

	/**
	 * Возвращает данные о пересчетном коэффициенте продукта питания для формы редактировния
	 */
	function loadFoodStuffCoeffEditForm($data)
	{
		$query = "
			select
				FSC.FoodStuff_id,
				FSC.FoodStuffCoeff_id,
				FSC.Okei_id,
				FSC.FoodStuffCoeff_Coeff,
				FSC.FoodStuffCoeff_Descr
			from
				v_FoodStuffCoeff FSC with (nolock)
			where
				FSC.FoodStuffCoeff_id = :FoodStuffCoeff_id
			";

		$result = $this->db->query($query, array('FoodStuffCoeff_id' => $data['FoodStuffCoeff_id']));

		if (is_object($result))
		{
			$response = array();
			$response = $result->result('array');
			return $response;
		}
		return false;
	}

	/**
	 * Сохраняет запись о продукте питания
	 */
	function saveFoodStuff($data)
	{
		$success = true;

		$query = "
			select COUNT(*) as Count
			from v_FoodStuff FS with(nolock)
			where
				FS.FoodStuff_Code = :FoodStuff_Code
				or FS.FoodStuff_Name like :FoodStuff_Name
		";

		$res = $this->db->query($query, array(
			'FoodStuff_Name' => $data['FoodStuff_Name'],
			'FoodStuff_Code' => $data['FoodStuff_Code'],
		));

		if ( !is_object($res) ) {
			$success = false;
		}
		else
		{
			$response = $res->result('array');
			if ( $response[0]['Count'] > 0 ) {
				$msg = 'Значение полей "наименования" и "код" должны быть уникальными';
				return array(0 => array('Error_Msg' => $msg));
			}
		}

		$procedure = '';
		if ( (!isset($data['FoodStuff_id'])) || ($data['FoodStuff_id'] <= 0) ) {
			$data['FoodStuff_id'] = null;
			$procedure = 'p_FoodStuff_ins';
		}
		else {
			$procedure = 'p_FoodStuff_upd';
		}

		$query = "
			declare
				@FoodStuff_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @FoodStuff_id = :FoodStuff_id
			exec " . $procedure . "
				@FoodStuff_id = @FoodStuff_id output,
				@FoodStuff_Name = :FoodStuff_Name,
				@FoodStuff_Code = :FoodStuff_Code,
				@FoodStuff_Descr = :FoodStuff_Descr,
				@Okei_id = :Okei_id,
				@FoodStuff_StorCond = :FoodStuff_StorCond,
				@FoodStuff_Protein = :FoodStuff_Protein,
				@FoodStuff_Fat = :FoodStuff_Fat,
				@FoodStuff_Carbohyd = :FoodStuff_Carbohyd,
				@FoodStuff_Caloric = :FoodStuff_Caloric,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @FoodStuff_id as FoodStuff_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = array(
			'FoodStuff_id' => $data['FoodStuff_id'],
			'FoodStuff_Name' => $data['FoodStuff_Name'],
			'FoodStuff_Code' => $data['FoodStuff_Code'],
			'FoodStuff_Descr' => $data['FoodStuff_Descr'],
			'Okei_id' => $data['Okei_id'],
			'FoodStuff_StorCond' => $data['FoodStuff_StorCond'],
			'FoodStuff_Protein' => $data['FoodStuff_Protein'],
			'FoodStuff_Fat' => $data['FoodStuff_Fat'],
			'FoodStuff_Carbohyd' => $data['FoodStuff_Carbohyd'],
			'FoodStuff_Caloric' => $data['FoodStuff_Caloric'],
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($query, $params);

		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}

	}

	/**
	 * Сохраняет запись о заменителе продукта питания
	 */
	function saveFoodStuffSubstit($data)
	{
		$success = true;
		$where = (!empty($data['FoodStuffSubstit_id'])) ? " AND FSS.FoodStuffSubstit_id <> :FoodStuffSubstit_id" : "";
		$query = "
			select COUNT(*) as Count
			from v_FoodStuffSubstit FSS with(nolock)
			where
				FSS.FoodStuff_id = :FoodStuff_id
				and FSS.FoodStuff_sid = :FoodStuff_sid
				{$where}
		";

		$res = $this->db->query($query, $data);

		if ( !is_object($res) ) {
			$success = false;
		}
		else
		{
			$response = $res->result('array');
			if ( $response[0]['Count'] > 0 ) {
				$msg = 'Выбранный заменитель у продукта уже имеется';
				return array(0 => array('Error_Msg' => $msg));
			}
		}

		$procedure = '';
		if ( (!isset($data['FoodStuffSubstit_id'])) || ($data['FoodStuffSubstit_id'] <= 0) ) {
			$data['FoodStuffSubstit_id'] = null;
			$procedure = 'p_FoodStuffSubstit_ins';
		}
		else {
			$procedure = 'p_FoodStuffSubstit_upd';
		}

		$query = "
			declare
				@FoodStuffSubstit_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @FoodStuffSubstit_id = :FoodStuffSubstit_id
			exec " . $procedure . "
				@FoodStuffSubstit_id = @FoodStuffSubstit_id output,
				@FoodStuff_id = :FoodStuff_id,
				@FoodStuff_sid = :FoodStuff_sid,
				@FoodStuffSubstit_Priority = :FoodStuffSubstit_Priority,
				@FoodStuffSubstit_Coeff = :FoodStuffSubstit_Coeff,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @FoodStuffSubstit_id as FoodStuffSubstit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = array(
			'FoodStuffSubstit_id' => $data['FoodStuffSubstit_id'],
			'FoodStuff_id' => $data['FoodStuff_id'],
			'FoodStuff_sid' => $data['FoodStuff_sid'],
			'FoodStuffSubstit_Priority' => $data['FoodStuffSubstit_Priority'],
			'FoodStuffSubstit_Coeff' => $data['FoodStuffSubstit_Coeff'],
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($query, $params);

		if (is_object($res))
		{
			return $res->result('array');
		}
		else
			return false;
	}

	/**
	 * Сохраняет запись о цене продукта питания
	 */
	function saveFoodStuffPrice($data)
	{
		$success = true;

		if ( (!isset($data['FoodStuffPrice_id'])) || ($data['FoodStuffPrice_id'] <= 0) ) {
			$query = "
				select COUNT(*) as Count
				from v_FoodStuffPrice FSP with(nolock)
				where
					FSP.FoodStuff_id = :FoodStuff_id
					and FSP.FoodStuffPrice_begDate = :FoodStuffPrice_begDate
			";

			$res = $this->db->query($query, array(
				'FoodStuff_id' => $data['FoodStuff_id'],
				'FoodStuffPrice_begDate' => $data['FoodStuffPrice_begDate'],
			));

			if ( !is_object($res) ) {
				$success = false;
			}
			else
			{
				$response = $res->result('array');
				if ( $response[0]['Count'] > 0 ) {
					$msg = 'На одну дату может быть только одна цена';
					return array(0 => array('Error_Msg' => $msg));
				}
			}
		}

		$procedure = '';
		if ( (!isset($data['FoodStuffPrice_id'])) || ($data['FoodStuffPrice_id'] <= 0) ) {
			$data['FoodStuffPrice_id'] = null;
			$procedure = 'p_FoodStuffPrice_ins';
		}
		else {
			$procedure = 'p_FoodStuffPrice_upd';
		}

		$query = "
			declare
				@FoodStuffPrice_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @FoodStuffPrice_id = :FoodStuffPrice_id
			exec " . $procedure . "
				@FoodStuffPrice_id = @FoodStuffPrice_id output,
				@FoodStuff_id = :FoodStuff_id,
				@FoodStuffPrice_begDate = :FoodStuffPrice_begDate,
				@FoodStuffPrice_Price = :FoodStuffPrice_Price,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @FoodStuffPrice_id as FoodStuffPrice_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = array(
			'FoodStuffPrice_id' => $data['FoodStuffPrice_id'],
			'FoodStuff_id' => $data['FoodStuff_id'],
			'FoodStuffPrice_begDate' => $data['FoodStuffPrice_begDate'],
			'FoodStuffPrice_Price' => $data['FoodStuffPrice_Price'],
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($query, $params);

		if (is_object($res))
		{
			return $res->result('array');
		}
		else
			return false;
	}

	/**
	 * Сохраняет запись о заменителе продукта питания
	 */
	function saveFoodStuffMicronutrient($data)
	{
		$success = true;

		$query = "
			select COUNT(*) as Count
			from v_FoodStuffMicronutrient FSM with(nolock)
			where
				FSM.FoodStuff_id = :FoodStuff_id
				and FSM.Micronutrient_id = :Micronutrient_id
		";

		$res = $this->db->query($query, array(
			'FoodStuff_id' => $data['FoodStuff_id'],
			'Micronutrient_id' => $data['Micronutrient_id'],
		));

		if ( !is_object($res) ) {
			$success = false;
		}
		else
		{
			$response = $res->result('array');
			if ( $response[0]['Count'] > 0 ) {
				$msg = 'Выбранный микронутриент у продукта уже имеется';
				return array(0 => array('Error_Msg' => $msg));
			}
		}

		$procedure = '';
		if ( (!isset($data['FoodStuffMicronutrient_id'])) || ($data['FoodStuffMicronutrient_id'] <= 0) ) {
			$data['FoodStuffMicronutrient_id'] = null;
			$procedure = 'p_FoodStuffMicronutrient_ins';
		}
		else {
			$procedure = 'p_FoodStuffMicronutrient_upd';
		}

		$query = "
			declare
				@FoodStuffMicronutrient_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @FoodStuffMicronutrient_id = :FoodStuffMicronutrient_id
			exec " . $procedure . "
				@FoodStuffMicronutrient_id = @FoodStuffMicronutrient_id output,
				@FoodStuff_id = :FoodStuff_id,
				@Micronutrient_id = :Micronutrient_id,
				@FoodStuffMicronutrient_Content = :FoodStuffMicronutrient_Content,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @FoodStuffMicronutrient_id as FoodStuffMicronutrient_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = array(
			'FoodStuffMicronutrient_id' => $data['FoodStuffMicronutrient_id'],
			'FoodStuff_id' => $data['FoodStuff_id'],
			'Micronutrient_id' => $data['Micronutrient_id'],
			'FoodStuffMicronutrient_Content' => $data['FoodStuffMicronutrient_Content'],
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($query, $params);

		if (is_object($res))
		{
			return $res->result('array');
		}
		else
			return false;
	}

	/**
	 * Сохраняет запись о пересчетных коэффициентах продукта питания
	 */
	function saveFoodStuffCoeff($data)
	{
		$success = true;

		$query = "
			select COUNT(*) as Count
			from v_FoodStuffCoeff FSС with(nolock)
			where
				FSС.FoodStuff_id = :FoodStuff_id
				and FSС.Okei_id = :Okei_id
		";

		$res = $this->db->query($query, array(
			'FoodStuff_id' => $data['FoodStuff_id'],
			'Okei_id' => $data['Okei_id'],
		));

		if ( !is_object($res) ) {
			$success = false;
		}
		else
		{
			$response = $res->result('array');
			if ( $response[0]['Count'] > 0 ) {
				$msg = 'Выбранная единица измерения уже указана для продукта';
				return array(0 => array('Error_Msg' => $msg));
			}
		}

		$procedure = '';
		if ( (!isset($data['FoodStuffCoeff_id'])) || ($data['FoodStuffCoeff_id'] <= 0) ) {
			$data['FoodStuffCoeff_id'] = null;
			$procedure = 'p_FoodStuffCoeff_ins';
		}
		else {
			$procedure = 'p_FoodStuffCoeff_upd';
		}

		$query = "
			declare
				@FoodStuffCoeff_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @FoodStuffCoeff_id = :FoodStuffCoeff_id
			exec " . $procedure . "
				@FoodStuffCoeff_id = @FoodStuffCoeff_id output,
				@FoodStuff_id = :FoodStuff_id,
				@Okei_id = :Okei_id,
				@FoodStuffCoeff_Coeff = :FoodStuffCoeff_Coeff,
				@FoodStuffCoeff_Descr = :FoodStuffCoeff_Descr,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @FoodStuffCoeff_id as FoodStuffCoeff_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = array(
			'FoodStuffCoeff_id' => $data['FoodStuffCoeff_id'],
			'FoodStuff_id' => $data['FoodStuff_id'],
			'Okei_id' => $data['Okei_id'],
			'Okei_id' => $data['Okei_id'],
			'FoodStuffCoeff_Coeff' => $data['FoodStuffCoeff_Coeff'],
			'FoodStuffCoeff_Descr' => $data['FoodStuffCoeff_Descr'],
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($query, $params);

		if (is_object($res))
		{
			return $res->result('array');
		}
		else
			return false;
	}

	/**
	 * Возвращает список заменителей продукта питания
	 */
	function loadFoodStuffSubstitGrid($data)
	{
		$query = "
			select
				FSS.FoodStuffSubstit_id,
				FSS.FoodStuff_id,
				FSS.FoodStuff_sid,
				FSS.FoodStuffSubstit_Priority,
				FS.FoodStuff_Name,
				FSS.FoodStuffSubstit_Coeff
			from
				v_FoodStuffSubstit FSS with (nolock)
				left join v_FoodStuff FS with (nolock) on FS.FoodStuff_id = FSS.FoodStuff_sid
			where
				FSS.FoodStuff_id = :FoodStuff_id
			";

		$result = $this->db->query($query, array('FoodStuff_id' => $data['FoodStuff_id']));

		if (is_object($result))
		{
			$response = array();
			$response = $result->result('array');
			return $response;
		}
		return false;
	}

	/**
	 * Возвращает список цен продукта питания
	 */
	function loadFoodStuffPriceGrid($data)
	{
		$query = "
			select
				FSP.FoodStuff_id,
				FSP.FoodStuffPrice_id,
				CONVERT(varchar(10),FSP.FoodStuffPrice_begDate,104) as FoodStuffPrice_begDate,
				FSP.FoodStuffPrice_Price
			from
				v_FoodStuffPrice FSP with (nolock)
			where
				FSP.FoodStuff_id = :FoodStuff_id
			";

		$result = $this->db->query($query, array('FoodStuff_id' => $data['FoodStuff_id']));

		if (is_object($result))
		{
			$response = array();
			$response = $result->result('array');
			return $response;
		}
		return false;
	}

	/**
	 * Возвращает список микронутриентов продукта питания
	 */
	function loadFoodStuffMicronutrientGrid($data)
	{
		$query = "
			select
				FSM.FoodStuff_id,
				FSM.FoodStuffMicronutrient_id,
				M.Micronutrient_Name,
				FSM.FoodStuffMicronutrient_Content
			from
				v_FoodStuffMicronutrient FSM with (nolock)
				left join v_Micronutrient M with (nolock) on M.Micronutrient_id = FSM.Micronutrient_id
			where
				FSM.FoodStuff_id = :FoodStuff_id
			";

		$result = $this->db->query($query, array('FoodStuff_id' => $data['FoodStuff_id']));

		if (is_object($result))
		{
			$response = array();
			$response = $result->result('array');
			return $response;
		}
		return false;
	}

	/**
	 * Возвращает список пересчетных коэффициентов продукта питания
	 */
	function loadFoodStuffCoeffGrid($data)
	{
		$query = "
			select
				FSC.FoodStuff_id,
				FSC.FoodStuffCoeff_id,
				FSC.Okei_id,
				O.Okei_Name,
				FSC.FoodStuffCoeff_Coeff,
				FSC.FoodStuffCoeff_Descr
			from
				v_FoodStuffCoeff FSC with (nolock)
				left join v_Okei O with (nolock) on O.Okei_id = FSC.Okei_id
			where
				FSC.FoodStuff_id = :FoodStuff_id
			";

		$result = $this->db->query($query, array('FoodStuff_id' => $data['FoodStuff_id']));

		if (is_object($result))
		{
			$response = array();
			$response = $result->result('array');
			return $response;
		}
		return false;
	}

	/**
	 * Возвращает список продуктов питания
	 */
	function loadFoodStuffList($data)
	{
		$filters = '(1=1)';
		$params = array();

		if (!empty($data['FoodStuff_id'])) {
			$filters .= " and FoodStuff_id = :FoodStuff_id";
			$params['FoodStuff_id'] = $data['FoodStuff_id'];
		}
		else {
			if (!empty($data['query'])) {
				$filters .= " and FoodStuff_Name like :FoodStuff_Name";
				$params['FoodStuff_Name'] = $data['query'].'%';
			}
		}

		$query = "
			select top 100
				FS.FoodStuff_id,
				FS.FoodStuff_Code,
				FS.FoodStuff_Name,
				FS.FoodStuff_Protein,
				FS.FoodStuff_Fat,
				FS.FoodStuff_Carbohyd,
				FS.FoodStuff_Caloric
			from
				v_FoodStuff FS with (nolock)
			where
				{$filters}
			order by
				FS.FoodStuff_Name
			";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response = array();
			$response = $result->result('array');
			return $response;
		}
		return false;
	}

}