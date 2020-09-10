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
class Limit_model extends swModel {
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
				Limit_id,
				LimitType_id,
				Limit_Values,
				RefValues_id,
				Limit_ValuesFrom,
				Limit_ValuesTo,
				Limit_IsActiv
			from
				v_Limit (nolock)
			where
				Limit_id = :Limit_id
		";
		$r = $this->db->query($q, array('Limit_id' => $data['Limit_id']));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($data) {
		$join = "
			left join v_Limit l (nolock) on 1 = 0
		";
		if (!empty($data['AnalyzerTestRefValues_id'])) {
			$join = "
				left join v_Limit l (nolock) on l.LimitType_id = lt.LimitType_id and l.RefValues_id = :RefValues_id
			";
			$data['RefValues_id'] = $this->getFirstResultFromQuery("SELECT RefValues_id FROM lis.v_AnalyzerTestRefValues (nolock) WHERE AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id", $data);
		}
		$q = "
			SELECT
				lt.LimitType_id,
				l.Limit_id,
				ISNULL(lt.LimitType_isCatalog, 1) as LimitType_isCatalog,
				case when lt.LimitType_isCatalog = 2 then 'Справочник' else 'Период' end as LimitType_isCatalogText,
				case when lt.LimitType_isCalculate = 2 then 1 else 0 end as LimitType_isCalculate,
				lt.LimitType_Name,
				lt.LimitType_SysNick,
				case when lt.LimitType_isCatalog = 2 then l.Limit_Values else null end as Limit_Values,
				'' as Limit_ValuesText,
				l.Limit_ValuesFrom,
				l.Limit_ValuesTo,
				case when lt.LimitType_isCatalog = 1 then l.Limit_Values else null end as Limit_Unit,
				'' as Limit_UnitText,
				case when l.Limit_IsActiv = 2 then 1 else 0 end as Limit_IsActiv
			FROM
				v_LimitType lt (nolock)
				{$join}
		";
		$result = $this->db->query($q, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach ($resp as &$respone) {
				if (!empty($respone['LimitType_SysNick'])) {
					if (!empty($respone['Limit_Values'])) {
						$respone['Limit_ValuesText'] = $this->getFirstResultFromQuery("SELECT {$respone['LimitType_SysNick']}_Name FROM v_{$respone['LimitType_SysNick']} (nolock) WHERE {$respone['LimitType_SysNick']}_id = :Limit_Values", $respone);
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
		$procedure = 'p_Limit_ins';
		if ( !empty($data['Limit_id']) ) {
			$procedure = 'p_Limit_upd';
		}
		
		// проверка на дубли
		$query = "
			select top 1
				Limit_id
			from
				v_Limit (nolock)
			where
				RefValues_id = :RefValues_id
				and LimitType_id = :LimitType_id
				and ISNULL(Limit_Values, 0) = ISNULL(:Limit_Values, 0)
				and ISNULL(Limit_ValuesFrom, 0) = ISNULL(:Limit_ValuesFrom, 0)
				and ISNULL(Limit_ValuesTo, 0) = ISNULL(:Limit_ValuesTo, 0)
				and (Limit_id <> :Limit_id OR :Limit_id IS NULL)
		";
		
		$result = $this->db->query($query, array(
			'Limit_id' => $data['Limit_id'],
			'RefValues_id' => $data['RefValues_id'],
			'LimitType_id' => $data['LimitType_id'],
			'Limit_Values' => $data['Limit_Values'],
			'Limit_ValuesFrom' => $data['Limit_ValuesFrom'],
			'Limit_ValuesTo' => $data['Limit_ValuesTo']
		));
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array('Error_Msg' => 'Указанное значение уже добавлено к референсному значению');
			}
		}
		
		$query = "
			declare
				@Limit_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Limit_id = :Limit_id;
			exec " . $procedure . "
				@Limit_id = @Limit_id output,
				@LimitType_id = :LimitType_id,
				@Limit_Values = :Limit_Values,
				@RefValues_id = :RefValues_id,
				@Limit_ValuesFrom = :Limit_ValuesFrom,
				@Limit_ValuesTo = :Limit_ValuesTo,
				@Limit_IsActiv = :Limit_IsActiv,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Limit_id as Limit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			exec p_Limit_del
				@Limit_id = :Limit_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'Limit_id' => $data['Limit_id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}