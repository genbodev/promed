<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Единицы измерений теста
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 */
class QuantitativeTestUnit_model extends swModel {

	/**
	 * Загрузка коэффициента пересчёта
	 */
	function loadCoeff($data) {
		$response = array('Error_Msg' => '', 'coeff' => '');

		// 1. ищем базовую единицу измерения для теста
		$baseUnit_id = $this->getFirstResultFromQuery("
			SELECT
				Unit_id as \"Unit_id\"
			FROM lis.QuantitativeTestUnit with(nolock)
			WHERE
				AnalyzerTest_id = :AnalyzerTest_id
				and QuantitativeTestUnit_IsBase = 2
		", $data);
		
		if (empty($baseUnit_id)) {
			return $response;
		}

		// 2. ищем коэффициент пересчёта для заданных Unit_id
		$this->load->model('UnitSpr_model');
		$resp = $this->UnitSpr_model->getUnitLinkUnitConv([
			'Unit_id' => $data['Unit_id'],
			'baseUnit_id' => $baseUnit_id,
			'UnitType_id' => 2
		]);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		
		if (!empty($resp['UnitLink_UnitConv'])) {
			$response['coeff'] = $resp['UnitLink_UnitConv'];
		}
		
		return $response;		
	}
	
	/**
	 * Загрузка единицы измерения
	 */
	function load($data) {
		$query = "
			select
				QuantitativeTestUnit_id as \"QuantitativeTestUnit_id\",
				AnalyzerTest_id as \"AnalyzerTest_id\",
				Unit_id as \"Unit_id\",
				case when QuantitativeTestUnit_IsBase = 2 then 'true' else 'false' end as \"QuantitativeTestUnit_IsBase\",
				QuantitativeTestUnit_CoeffEnum as \"QuantitativeTestUnit_CoeffEnum\"
			from
				lis.v_QuantitativeTestUnit with(nolock)
			where
				QuantitativeTestUnit_id = :QuantitativeTestUnit_id
		";
		$r = $this->db->query($query, array('QuantitativeTestUnit_id' => $data['QuantitativeTestUnit_id']));
		if ( is_object($r) ) {
			return $r->result('array');
		}

		return false;
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$query = "
			SELECT
				qtu.QuantitativeTestUnit_id as \"QuantitativeTestUnit_id\",
				qtu.AnalyzerTest_id as \"AnalyzerTest_id\",
				qtu.Unit_id as \"Unit_id\",
				u.Unit_Name as \"Unit_Name\",
				case when qtu.QuantitativeTestUnit_IsBase = 2
					then 'true'
					else 'false'
				end as \"QuantitativeTestUnit_IsBase\",
				case when qtu.QuantitativeTestUnit_CoeffEnum = 1
					then null
					else qtu.QuantitativeTestUnit_CoeffEnum
				end as \"QuantitativeTestUnit_CoeffEnum\"
			FROM
				lis.v_QuantitativeTestUnit qtu with(nolock)
				left join lis.v_Unit u with(nolock) on u.Unit_id = qtu.Unit_id
			WHERE
				qtu.AnalyzerTest_id = :AnalyzerTest_id
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save($data) {
		$procedure = 'p_QuantitativeTestUnit_ins';
		if ( !empty($data['QuantitativeTestUnit_id']) ) {
			$procedure = 'p_QuantitativeTestUnit_upd';
		}
		
		// проверка на дубли
		$query = "
			select top 1
				QuantitativeTestUnit_id as \"QuantitativeTestUnit_id\"
			from
				lis.v_QuantitativeTestUnit with(nolock)
			where
				AnalyzerTest_id = :AnalyzerTest_id
				and Unit_id = :Unit_id
				and (QuantitativeTestUnit_id <> :QuantitativeTestUnit_id OR :QuantitativeTestUnit_id IS NULL)
		";

		$result = $this->db->query($query, array(
			'QuantitativeTestUnit_id' => $data['QuantitativeTestUnit_id'],
			'Unit_id' => $data['Unit_id'],
			'AnalyzerTest_id' => $data['AnalyzerTest_id']
		));

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array('Error_Msg' => 'Выбранная единица измерения уже добавлена к тесту');
			}
		}

		$koef = null;

		if ($data['QuantitativeTestUnit_IsBase']) {
			$data['QuantitativeTestUnit_IsBase'] = 2;
			$koef = $data['QuantitativeTestUnit_CoeffEnum'];
			$data['QuantitativeTestUnit_CoeffEnum'] = 1;
		} else {
			$data['QuantitativeTestUnit_IsBase'] = 1;
		}
		
		$query = "
			declare
				@QuantitativeTestUnit_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @QuantitativeTestUnit_id = :QuantitativeTestUnit_id;
			exec lis." . $procedure . "
				@QuantitativeTestUnit_id = @QuantitativeTestUnit_id output,
				@AnalyzerTest_id = :AnalyzerTest_id,
				@Unit_id = :Unit_id,
				@QuantitativeTestUnit_IsBase = :QuantitativeTestUnit_IsBase,
				@QuantitativeTestUnit_CoeffEnum = :QuantitativeTestUnit_CoeffEnum,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @QuantitativeTestUnit_id as QuantitativeTestUnit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($query, $data);
		if ( is_object($r) ) {
			$resp = $r->result('array');
			if (!empty($resp[0]['QuantitativeTestUnit_id'])) {
				// если базовая нужно пересчитать все остальные и снять пометку
				if (!empty($koef)) {
					$query = "
						update
							lis.QuantitativeTestUnit with(rowlock)
						set
							QuantitativeTestUnit_IsBase = 1,
							QuantitativeTestUnit_CoeffEnum = (QuantitativeTestUnit_CoeffEnum / :koef)
						where
							AnalyzerTest_id = :AnalyzerTest_id
							and QuantitativeTestUnit_id <> :QuantitativeTestUnit_id
					";

					$this->db->query($query, array(
						'AnalyzerTest_id' => $data['AnalyzerTest_id'],
						'koef' => $koef,
						'QuantitativeTestUnit_id' => $resp[0]['QuantitativeTestUnit_id']
					));
				}
			}
			return $resp;
		}

		return false;
	}

	/**
	 * Удаление
	 */
	function delete($data) {
		// проверка, а не используется ли единица измерения в референсных значениях данного теста
		$query = "
			select
				qtu.QuantitativeTestUnit_id as \"QuantitativeTestUnit_id\"
			from
				lis.v_QuantitativeTestUnit qtu
				inner join lis.v_AnalyzerTestRefValues atrv on atrv.AnalyzerTest_id = qtu.AnalyzerTest_id
				inner join v_RefValues rv on rv.RefValues_id = atrv.RefValues_id and rv.Unit_id = qtu.Unit_id
			where
				qtu.QuantitativeTestUnit_id = :QuantitativeTestUnit_id
		";
		
		$result = $this->db->query($query, array(
			'QuantitativeTestUnit_id' => $data['QuantitativeTestUnit_id']
		));
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array('Error_Msg' => 'Нельзя удалить единицу измерения, т.к. она используется в референсных значениях');
			}
		}
		
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.p_QuantitativeTestUnit_del
				@QuantitativeTestUnit_id = :QuantitativeTestUnit_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($query, array(
			'QuantitativeTestUnit_id' => $data['QuantitativeTestUnit_id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}