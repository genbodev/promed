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
class Limit_model extends SwPgModel {
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
				Limit_id as \"Limit_id\",
				LimitType_id as \"LimitType_id\",
				Limit_Values as \"Limit_Values\",
				RefValues_id as \"RefValues_id\",
				Limit_ValuesFrom as \"Limit_ValuesFrom\",
				Limit_ValuesTo as \"Limit_ValuesTo\",
				Limit_IsActiv as \"Limit_IsActiv\"
			from
				v_Limit
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
			left join v_Limit l on 1 = 0
		";
		if (!empty($data['AnalyzerTestRefValues_id'])) {
			$join = "
				left join v_Limit l on l.LimitType_id = lt.LimitType_id and l.RefValues_id = :RefValues_id
			";
			$data['RefValues_id'] = $this->getFirstResultFromQuery("SELECT RefValues_id as \"RefValues_id\" FROM lis.v_AnalyzerTestRefValues WHERE AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id", $data);
		}
		$q = "
			SELECT
				lt.LimitType_id as \"LimitType_id\",
				l.Limit_id as \"Limit_id\",
				COALESCE(lt.LimitType_isCatalog, 1) as \"LimitType_isCatalog\",
				case when lt.LimitType_isCatalog = 2 then 'Справочник' else 'Период' end as \"LimitType_isCatalogText\",
				case when lt.LimitType_isCalculate = 2 then 1 else 0 end as \"LimitType_isCalculate\",
				lt.LimitType_Name as \"LimitType_Name\",
				lt.LimitType_SysNick as \"LimitType_SysNick\",
				case when lt.LimitType_isCatalog = 2 then l.Limit_Values else null end as \"Limit_Values\",
				'' as \"Limit_ValuesText\",
				l.Limit_ValuesFrom as \"Limit_ValuesFrom\",
				l.Limit_ValuesTo as \"Limit_ValuesTo\",
				case when lt.LimitType_isCatalog = 1 then l.Limit_Values else null end as \"Limit_Unit\",
				'' as \"Limit_UnitText\",
				case when l.Limit_IsActiv = 2 then 1 else 0 end as \"Limit_IsActiv\"
			FROM
				v_LimitType lt
				{$join}
		";
		$result = $this->db->query($q, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach ($resp as &$respone) {
				if (!empty($respone['LimitType_SysNick'])) {
					if (!empty($respone['Limit_Values'])) {
						$respone['Limit_ValuesText'] = $this->getFirstResultFromQuery("SELECT {$respone['LimitType_SysNick']}_Name FROM v_{$respone['LimitType_SysNick']} WHERE {$respone['LimitType_SysNick']}_id = :Limit_Values", $respone);
					}
					
					if (!empty($respone['Limit_Unit'])) {
						$respone['Limit_UnitText'] = $this->getFirstResultFromQuery("SELECT {$respone['LimitType_SysNick']}_Name FROM v_{$respone['LimitType_SysNick']} WHERE {$respone['LimitType_SysNick']}_id = :Limit_Unit", $respone);
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
			select
				Limit_id as \"Limit_id\"
			from
				v_Limit
			where
				RefValues_id = :RefValues_id
				and LimitType_id = :LimitType_id
				and COALESCE(Limit_Values, 0) = COALESCE(:Limit_Values, 0)
				and COALESCE(Limit_ValuesFrom, 0) = COALESCE(:Limit_ValuesFrom, 0)
				and COALESCE(Limit_ValuesTo, 0) = COALESCE(:Limit_ValuesTo, 0)
				and (Limit_id <> :Limit_id OR :Limit_id IS NULL)
            limit 1
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
			select 
			    Limit_id as \"Limit_id\", 
			    Error_Code as \"Error_Code\", 
			    Error_Message as \"Error_Msg\"
			from " . $procedure . " (
				Limit_id := :Limit_id,
				LimitType_id := :LimitType_id,
				Limit_Values := :Limit_Values,
				RefValues_id := :RefValues_id,
				Limit_ValuesFrom := :Limit_ValuesFrom,
				Limit_ValuesTo := :Limit_ValuesTo,
				Limit_IsActiv := :Limit_IsActiv,
				pmUser_id := :pmUser_id
			)
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
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_Limit_del (
				Limit_id := :Limit_id
			)
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