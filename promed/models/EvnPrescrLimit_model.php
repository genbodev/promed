<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Ограничения
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2014 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version
 */
class EvnPrescrLimit_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Загрузка
	 */
	function load($data) {
		$q = "
			select
				EvnPrescrLimit_id,
				LimitType_id,
				EvnPrescrLimit_Values,
				EvnPrescr_id,
				EvnPrescrLimit_ValuesNum
			from
				v_EvnPrescrLimit (nolock)
			where
				EvnPrescrLimit_id = :EvnPrescrLimit_id
		";
		$r = $this->db->query($q, array('EvnPrescrLimit_id' => $data['EvnPrescrLimit_id']));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка лимитов
	 */
	function checkLimits($data) {
		$resp = $this->loadList($data);
		if (is_array($resp) && count($resp) > 0) {
			return array('Error_Msg' => '', 'success' => true, 'IsLimits' => 1);
		} else {
			return array('Error_Msg' => '', 'success' => true, 'IsLimits' => 0);
		}
	}
	
	/**
	 * Загрузка списка
	 */
	function loadList($data) {
		$join = "
			left join v_EvnPrescrLimit epl (nolock) on 1 = 0
		";
		if (!empty($data['EvnPrescr_id'])) {
			$join = "
				left join v_EvnPrescrLimit epl (nolock) on epl.LimitType_id = lt.LimitType_id and epl.EvnPrescr_id = :EvnPrescr_id
			";
		}
		
		$filter = "";
		$data['Sex_id'] = null;
		$data['Person_Age'] = null;
		
		$query = "
			select top 1
				Sex_id,
				dbo.Age2(Person_BirthDay, getdate()) as Person_Age
			from
				v_PersonState (nolock)
			where
				Person_id = :Person_id
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Sex_id'] = $resp[0]['Sex_id'];
				$data['Person_Age'] = $resp[0]['Person_Age'];
			}
		}
		
		// 6. Ограничение «Беременность» отображать только для персанов, у которых указан пол «женский» и возраст 14 лет и старше
		if ($data['Sex_id'] != 2 || $data['Person_Age'] < 14) {
			$filter .= " and lt.LimitType_SysNick <> 'PregnancyUnitType'";
		}
		
		// 7. Ограничение «Фаза цикла» отображать только для персанов, у которых указан пол «женский» и возраст 12 лет и старше
		if ($data['Sex_id'] != 2 || $data['Person_Age'] < 12) {
			$filter .= " and lt.LimitType_SysNick <> 'HormonalPhaseType'";
		}
		
		$q = "
			SELECT
				lt.LimitType_id,
				epl.EvnPrescrLimit_id,
				ISNULL(lt.LimitType_isCatalog, 1) as LimitType_isCatalog,
				lt.LimitType_Name,
				lt.LimitType_SysNick,
				case when lt.LimitType_isCatalog = 2 then epl.EvnPrescrLimit_Values else null end as EvnPrescrLimit_Values,
				'' as EvnPrescrLimit_ValuesText,
				epl.EvnPrescrLimit_ValuesNum,
				case when lt.LimitType_isCatalog = 1 then limit.Limit_Values else null end as Limit_Unit,
				'' as Limit_UnitText
			FROM
				v_LimitType lt (nolock)
				{$join}
				cross apply(
					select top 1
						Limit_Values
					from
						v_Limit l (nolock)
						inner join lis.v_AnalyzerTestRefValues atrv (nolock) on atrv.RefValues_id = l.RefValues_id
						inner join lis.v_AnalyzerTest at (nolock) on at.AnalyzerTest_id = atrv.AnalyzerTest_id
						inner join v_UslugaComplexMedService ucms_at (nolock) on ucms_at.UslugaComplexMedService_id = at.UslugaComplexMedService_id
						left join lis.v_AnalyzerTest at_parent (nolock) on at_parent.AnalyzerTest_id = at.AnalyzerTest_pid
						left join v_UslugaComplexMedService ucms_at_parent (nolock) on ucms_at_parent.UslugaComplexMedService_id = at_parent.UslugaComplexMedService_id
						inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
					where
						a.MedService_id = :MedService_id
						and ISNULL(ucms_at_parent.UslugaComplex_id, ucms_at.UslugaComplex_id) = :UslugaComplex_id
						and l.LimitType_id = lt.LimitType_id
						and (
							(l.Limit_Values IS NOT NULL AND lt.LimitType_IsCatalog = 2)
							OR
							((l.Limit_ValuesTo IS NOT NULL OR l.Limit_ValuesFrom IS NOT NULL) AND lt.LimitType_IsCatalog = 1)
						)
				) limit
			WHERE
				ISNULL(lt.LimitType_IsCalculate, 1) = 1
				{$filter}
		";
		// echo getDebugSql($q, $data);die();
		$result = $this->db->query($q, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach ($resp as &$respone) {
				if (!empty($respone['LimitType_SysNick'])) {
					if (!empty($respone['EvnPrescrLimit_Values'])) {
						$respone['EvnPrescrLimit_ValuesText'] = $this->getFirstResultFromQuery("SELECT {$respone['LimitType_SysNick']}_Name FROM v_{$respone['LimitType_SysNick']} (nolock) WHERE {$respone['LimitType_SysNick']}_id = :EvnPrescrLimit_Values", $respone);
					}
					
					if (!empty($respone['Limit_Unit'])) {
						$respone['Limit_UnitText'] = $this->getFirstResultFromQuery("SELECT {$respone['LimitType_SysNick']}_Name FROM v_{$respone['LimitType_SysNick']} (nolock) WHERE {$respone['LimitType_SysNick']}_id = :Limit_Unit", $respone);
					}
				}
			}
			
			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save($data) {
		$procedure = 'p_EvnPrescrLimit_ins';
		if ( !empty($data['EvnPrescrLimit_id']) ) {
			$procedure = 'p_EvnPrescrLimit_upd';
		}
		
		// проверка на дубли
		$query = "
			select top 1
				EvnPrescrLimit_id
			from
				v_EvnPrescrLimit (nolock)
			where
				EvnPrescr_id = :EvnPrescr_id
				and LimitType_id = :LimitType_id
				and ISNULL(EvnPrescrLimit_Values, 0) = ISNULL(:EvnPrescrLimit_Values, 0)
				and ISNULL(EvnPrescrLimit_ValuesNum, 0) = ISNULL(:EvnPrescrLimit_ValuesNum, 0)
				and (EvnPrescrLimit_id <> :EvnPrescrLimit_id OR :EvnPrescrLimit_id IS NULL)
		";
		
		$result = $this->db->query($query, array(
			'EvnPrescrLimit_id' => $data['EvnPrescrLimit_id'],
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'LimitType_id' => $data['LimitType_id'],
			'EvnPrescrLimit_Values' => $data['EvnPrescrLimit_Values'],
			'EvnPrescrLimit_ValuesNum' => $data['EvnPrescrLimit_ValuesNum']
		));
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array('Error_Msg' => 'Указанное значение уже добавлено к референсному значению');
			}
		}
		
		$query = "
			declare
				@EvnPrescrLimit_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @EvnPrescrLimit_id = :EvnPrescrLimit_id;
			exec " . $procedure . "
				@EvnPrescrLimit_id = @EvnPrescrLimit_id output,
				@LimitType_id = :LimitType_id,
				@EvnPrescrLimit_Values = :EvnPrescrLimit_Values,
				@EvnPrescr_id = :EvnPrescr_id,
				@EvnPrescrLimit_ValuesNum = :EvnPrescrLimit_ValuesNum,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnPrescrLimit_id as EvnPrescrLimit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Удаление
	 */
	function delete($data) {
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPrescrLimit_del
				@EvnPrescrLimit_id = :EvnPrescrLimit_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'EvnPrescrLimit_id' => $data['EvnPrescrLimit_id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка списка ограничений в АРМе лаборанта/Заявки
	 * @param $data
	 * @return array|false
	 */
	function loadGrid($data) {
		$params = [];
		$params['EvnDirection_id'] = $data['EvnDirection_id'];

		$query = "
			select
				EVL.EvnPrescrLimit_id,
				EVL.LimitType_id,
				EVL.EvnPrescrLimit_Values,
				LT.LimitType_Name,
				LT.LimitType_IsCalculate,
				LT.LimitType_SysNick,
				LT.LimitType_IsCatalog,
				EP.EvnClass_Name,
				EP.EvnPrescr_setDate
			from
				v_EvnPrescrLimit EVL with(nolock)
				inner join v_EvnPrescr EP with(nolock) on EP.EvnPrescr_id = EVL.EvnPrescr_id
				inner join v_LimitType LT with(nolock) on LT.LimitType_id = EVL.LimitType_id
				inner join v_EvnPrescrDirection EPD with(nolock) on EPD.EvnPrescr_id = EP.EvnPrescr_id
			where 
				isnull(LT.LimitType_IsCalculate,1) = 1
				and (EVL.EvnPrescrLimit_Values is not null or EVL.EvnPrescrLimit_ValuesNum is not null)
				and EvnDirection_id = :EvnDirection_id
		";

		$result = $this->queryResult($query, $params);

		//для каждой записи вытащим данные из справочника
		foreach ($result as $key => $limit) {
			if($limit['LimitType_IsCatalog'] != 2) {
				$result[$key]['limitObj'] = $limit['EvnPrescrLimit_ValuesNum'];
				continue;
			};
			if(!$limit['LimitType_SysNick'] || !$limit['EvnPrescrLimit_Values']) continue;
			$limitObj = $limit['LimitType_SysNick'];
			$params = [];
			$params[$limitObj.'_id'] = $limit['EvnPrescrLimit_Values'];
			$query = "
				select {$limitObj}_Name
				from {$limitObj} with(nolock)
				where {$limitObj}_id = :{$limitObj}_id
			";
			$limitTypeUnitName = $this->getFirstResultFromQuery($query,$params);
			$result[$key]['limitObj'] = $limitTypeUnitName;
		}

		return $result;
	}
}