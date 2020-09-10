<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * UnitSpr_model - модель для работы со справочниками единиц измерения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Dmitry Vlasenko
 * @version			28.01.2014
 *
 */

class UnitSpr_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка связанных единиц измерения
	 */
	function loadUnitLinkGrid($data)
	{
		$params = array();

		if (!empty($data['Okei_id'])) {
			$params['UnitLink_Fir'] = $data['Okei_id'];
			$params['UnitType_fid'] = 1;
		} else {
			$params['UnitLink_Fir'] = $data['Unit_id'];
			$params['UnitType_fid'] = 2;
		}
		
		$query = "
			(select
				1 as \"UnitLinkType_id\",
				ul.UnitLink_id as \"UnitLink_id\",
				ut.UnitType_Name as \"UnitType_Name\",
				case when ul.UnitType_sid = 1 then o.Okei_Name else u.Unit_Name end as \"UnitSpr_Name\",
				case when ul.UnitType_sid = 1 then o.Okei_id else null end as \"Okei_id\",
				case when ul.UnitType_sid = 2 then u.Unit_id else null end as \"Unit_id\",
				ul.UnitLink_UnitConv as \"UnitLink_UnitConv\"
			from
				v_UnitLink ul
				left join lis.v_Unit u on u.Unit_id = ul.UnitLink_Sec
				left join v_Okei o on o.Okei_id = ul.UnitLink_Sec
				left join v_UnitType ut on ut.UnitType_id = ul.UnitType_sid
			where
				ul.UnitLink_Fir = :UnitLink_Fir
				and ul.UnitType_fid = :UnitType_fid)
				
			union all
			
			(select
				2 as \"UnitLinkType_id\",
				ul.UnitLink_id as \"UnitLink_id\",
				ut.UnitType_Name as \"UnitType_Name\",
				case when ul.UnitType_fid = 1 then o.Okei_Name else u.Unit_Name end as \"UnitSpr_Name\",
				case when ul.UnitType_fid = 1 then o.Okei_id else null end as \"Okei_id\",
				case when ul.UnitType_fid = 2 then u.Unit_id else null end as \"Unit_id\",
				1 / ul.UnitLink_UnitConv as \"UnitLink_UnitConv\"
			from
				v_UnitLink ul
				left join lis.v_Unit u on u.Unit_id = ul.UnitLink_Fir
				left join v_Okei o on o.Okei_id = ul.UnitLink_Fir
				left join v_UnitType ut on ut.UnitType_id = ul.UnitType_fid
			where
				ul.UnitLink_Sec = :UnitLink_Fir
				and ul.UnitType_sid = :UnitType_fid)
			
			order by
				\"UnitSpr_Name\"
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение списка единиц измерения
	 */
	function loadUnitSprGrid($data)
	{
		$params = array();

		$query = "
			select
				us.UnitSpr_id as \"UnitSpr_id\",
				us.Okei_id as \"Okei_id\",
				us.Unit_id as \"Unit_id\",
				us.UnitSpr_Code as \"UnitSpr_Code\",
				us.UnitType_Name as \"UnitType_Name\",
				us.UnitSpr_Name as \"UnitSpr_Name\",
				us.UnitSpr_begDate as \"UnitSpr_begDate\",
				us.UnitSpr_endDate as \"UnitSpr_endDate\",
				us.IsLinked as \"IsLinked\"
			from
			(
				select
					'o_' || cast(Okei_id as varchar) as UnitSpr_id,
					o.Okei_id,
					null as Unit_id,
					Okei_Code as UnitSpr_Code,
					OKei_Name as UnitSpr_Name,
					null as UnitSpr_begDate,
					null as UnitSpr_endDate,
					ut.UnitType_Name,
					case when ul.cnt > 0 then 'true' else 'false' end as IsLinked
				from
					v_Okei o
					left join v_UnitType ut on ut.UnitType_id = 1
					left join lateral(
						select count(*) as cnt from v_UnitLink where UnitLink_Fir = o.Okei_id and UnitType_fid = 1
					) ul on true
					
				union all

				select
					'u_' || cast(Unit_id as varchar) as UnitSpr_id,
					null as Okei_id,
					u.Unit_id,
					Unit_Code as UnitSpr_Code,
					Unit_Name as UnitSpr_Name,
					to_char(Unit_begDate, 'dd.mm.yyyy') as UnitSpr_begDate,
					to_char(Unit_endDate, 'dd.mm.yyyy') as UnitSpr_endDate,
					ut.UnitType_Name,
					case when ul.cnt > 0 then 'true' else 'false' end as IsLinked
				from
					lis.v_Unit u
					left join v_UnitType ut on ut.UnitType_id = 2
					left join lateral(
						select count(*) as cnt from v_UnitLink where UnitLink_Fir = u.Unit_id and UnitType_fid = 2
					) ul on true
			) us
			order by
				us.UnitSpr_Name
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}
	
	/**
	 * Сохранение единицы измерения
	 */
	function save($data)
	{
		if ($data['UnitType_id'] == 1) {
			$proc = 'p_Okei_ins';
			if (!empty($data['Okei_id'])) {
				$proc = 'p_Okei_upd';
			} else {
				$data['Okei_id'] = null;
			}

			if (!empty($data['Okei_id'])) {
				$query = "
					select
						OkeiType_id as \"OkeiType_id\",
						Okei_NationSymbol as \"Okei_NationSymbol\",
						Okei_InterNationSymbol as \"Okei_InterNationSymbol\",
						Okei_NationCode as \"Okei_NationCode\",
						Okei_InterNationCode as \"Okei_InterNationCode\",
						Okei_cid as \"Okei_cid\",
						Okei_UnitConversion as \"Okei_UnitConversion\"
					from v_Okei
					where Okei_id = :Okei_id
					limit 1
				";

				$resp = $this->queryResult($query, $data);
				if (!is_array($resp) && count($resp) == 0) {
					return $this->createError('', 'Ошибка при получении данных единицы измерения');
				}
				$data = array_merge($data, $resp[0]);
			}

			$data['OkeiType_id'] = (empty($data['OkeiType_id'])) ? null : $data['OkeiType_id'];
			$data['Okei_NationSymbol'] = (empty($data['Okei_NationSymbol'])) ? null : $data['Okei_NationSymbol'];
			$data['Okei_InterNationSymbol'] = (empty($data['Okei_InterNationSymbol'])) ? null : $data['Okei_InterNationSymbol'];
			$data['Okei_NationCode'] = (empty($data['Okei_NationCode'])) ? null : $data['Okei_NationCode'];
			$data['Okei_InterNationCode'] = (empty($data['Okei_InterNationCode'])) ? null : $data['Okei_InterNationCode'];
			$data['Okei_cid'] = (empty($data['Okei_cid'])) ? null : $data['Okei_cid'];
			$data['Okei_UnitConversion'] = (empty($data['Okei_UnitConversion'])) ? null : $data['Okei_UnitConversion'];
			$query = "
				select
					null as \"Unit_id\",
					Okei_id as \"Okei_id\",
					Error_Code as \"Error_Code\",
					Error_Message as  \"Error_Msg\"
				from {$proc}(
					Okei_id := :Okei_id,
					OkeiType_id := :OkeiType_id,
					Okei_Code := :UnitSpr_Code,
					Okei_Name := :UnitSpr_Name,
					Okei_NationSymbol := :Okei_NationSymbol,
					Okei_InterNationSymbol := :Okei_InterNationSymbol,
					Okei_NationCode := :Okei_NationCode,
					Okei_InterNationCode := :Okei_InterNationCode,
					Okei_cid := :Okei_cid,
					Okei_UnitConversion := :Okei_UnitConversion,
					pmUser_id := :pmUser_id
				)
			";
		} else {
		
			$proc = 'p_Unit_ins';
			if (!empty($data['Unit_id'])) {
				$proc = 'p_Unit_upd';
			} else {
				$data['Unit_id'] = null;
			}

			$query = "
				select
					null as \"Okei_id\",
					Unit_id as \"Unit_id\",
					Error_Code as \"Error_Code\",
					Error_Message as  \"Error_Msg\"
				from lis.{$proc}(
					Unit_id := :Unit_id,
					Unit_Code := :UnitSpr_Code,
					Unit_Name := :UnitSpr_Name,
					Unit_begDate := :Unit_begDate,
					Unit_endDate := :Unit_endDate,
					Unit_SysNick := NULL,
					Unit_Deleted := 1,
					pmUser_id := :pmUser_id
				)
			";
		}
		
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}
	
	/**
	 * Сохранение связи единицы измерения
	 */
	function saveUnitLink($data)
	{
		if ($data['UnitType_sid'] == 1) {
			$data['UnitLink_Sec'] = $data['Okei_id'];
		} else {
			$data['UnitLink_Sec'] = $data['Unit_id'];
		}
		
		if ($data['UnitLink_Fir'] == $data['UnitLink_Sec'] && $data['UnitType_sid'] == $data['UnitType_fid']) {
			return array('Error_Msg' => 'Нельзя связать единицу измерения саму с собой');
		}
		
		// проверка на дубли
		$query = "
			select
				UnitLink_id as \"UnitLink_id\"
			from
				v_UnitLink
			where
				UnitLink_Fir = :UnitLink_Fir
				and UnitType_fid = :UnitType_fid
				and UnitLink_Sec = :UnitLink_Sec
				and UnitType_sid = :UnitType_sid
				and (UnitLink_id <> :UnitLink_id OR :UnitLink_id IS NULL)
				
			union all
			
			select
				UnitLink_id as \"UnitLink_id\"
			from
				v_UnitLink
			where
				UnitLink_Fir = :UnitLink_Sec
				and UnitType_fid = :UnitType_sid
				and UnitLink_Sec = :UnitLink_Fir
				and UnitType_sid = :UnitType_fid
				and (UnitLink_id <> :UnitLink_id OR :UnitLink_id IS NULL)
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array('Error_Msg' => 'Указанные единицы измерения уже связаны');
			}
		}
		
		$proc = 'p_UnitLink_ins';
		if (!empty($data['UnitLink_id'])) {
			$proc = 'p_UnitLink_upd';
		} else {
			$data['UnitLink_id'] = null;
		}
		
		$query = "
			select
				UnitLink_id as \"UnitLink_id\",
				Error_Code as \"Error_Code\",
				Error_Message as  \"Error_Msg\"
			from {$proc}(
				UnitLink_id := :UnitLink_id,
				UnitLink_Fir := :UnitLink_Fir,
				UnitType_fid := :UnitType_fid,
				UnitLink_Sec := :UnitLink_Sec,
				UnitType_sid := :UnitType_sid,
				UnitLink_UnitConv := :UnitLink_UnitConv,
				pmUser_id := :pmUser_id
			)
		";
		
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}
	
	/**
	 * Получение данных формы редактирования единиц измерения
	 */
	function load($data)
	{
		if (!empty($data['Okei_id'])) {
			$query = "
				select
					'o_' || cast(Okei_id as varchar) as \"UnitSpr_id\",
					Okei_id as \"Okei_id\",
					null as \"Unit_id\",
					Okei_Code as \"UnitSpr_Code\",
					OKei_Name as \"UnitSpr_Name\",
					1 as \"UnitType_id\"
				from
					v_Okei
				where
					Okei_id = :Okei_id
			";
		} else {
			$query = "
				select
					'u_' || cast(Unit_id as varchar) as \"UnitSpr_id\",
					null as \"Okei_id\",
					Unit_id as \"Unit_id\",
					Unit_Code as \"UnitSpr_Code\",
					Unit_Name as \"UnitSpr_Name\",
					to_char(Unit_begDate, 'dd.mm.yyyy') as \"UnitSpr_begDate\",
					to_char(Unit_endDate, 'dd.mm.yyyy') as \"UnitSpr_endDate\",
					2 as \"UnitType_id\"
				from
					lis.v_Unit
				where
					Unit_id = :Unit_id
			";	
		}

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}
	
	
	/**
	 * Получение данных формы редактирования единиц измерения
	 */
	function loadUnitLink($data)
	{
		$query = "
			select
				UnitLink_id as \"UnitLink_id\",
				UnitLink_Fir as \"UnitLink_Fir\",
				UnitType_fid as \"UnitType_fid\",
				case when UnitType_sid = 1 then UnitLink_Sec else null end as \"Okei_id\",
				case when UnitType_sid = 2 then UnitLink_Sec else null end as \"Unit_id\",
				UnitType_sid as \"UnitType_sid\",
				UnitLink_UnitConv as \"UnitLink_UnitConv\"
			from
				v_UnitLink
			where
				UnitLink_id = :UnitLink_id
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}
	
	/**
	 * Получение данных формы редактирования единиц измерения
	 */
	function loadUnitLinkObr($data)
	{
		$query = "
			select
				UnitLink_id as \"UnitLink_id\",
				UnitLink_Sec as \"UnitLink_Fir\",
				UnitType_sid as \"UnitType_fid\",
				case when UnitType_fid = 1 then UnitLink_Fir else null end as \"Okei_id\",
				case when UnitType_fid = 2 then UnitLink_Fir else null end as \"Unit_id\",
				UnitType_fid as \"UnitType_sid\",
				1 / UnitLink_UnitConv as \"UnitLink_UnitConv\"
			from
				v_UnitLink
			where
				UnitLink_id = :UnitLink_id
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Удаление единицы измерения
	 */
	function deleteUnit($data)
	{
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from lis.p_Unit_del(
				Unit_id := :Unit_id
			)
		";
		
		$queryParams = array(
			'Unit_id' => $data['Unit_id']
		);
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Error_Code']) && $resp[0]['Error_Code'] == '23503') {
				$resp[0]['Error_Msg'] = 'Удаление невозможно, т.к. существуют связанные записи';
			}
			return $resp;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Удаление единицы измерения
	 */
	function deleteUnitLink($data)
	{
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as  \"Error_Msg\"
			from p_UnitLink_del(
				UnitLink_id := :UnitLink_id
			)
		";
		
		$queryParams = array(
			'UnitLink_id' => $data['UnitLink_id']
		);
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Error_Code']) && $resp[0]['Error_Code'] == '547') {
				$resp[0]['Error_Msg'] = 'Удаление невозможно, т.к. существуют связанные записи';
			}
			return $resp;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Удаление единицы измерения
	 */
	function deleteOkei($data)
	{
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as  \"Error_Msg\"
			from p_Okei_del(
				Okei_id := :Okei_id
			)
		";

		$queryParams = array(
			'Okei_id' => $data['Okei_id']
		);
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Error_Code']) && $resp[0]['Error_Code'] == '547') {
				$resp[0]['Error_Msg'] = 'Удаление невозможно, т.к. существуют связанные записи';
			}
			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение коэфициента для конвертации единици измерения
	 */
	function getOkeiConv($data) {
		$params = array(
			'fid' => $data['Okei_fid'],
			'sid' => $data['Okei_sid'],
			'UnitType_fid' => 1,
			'UnitType_sid' => 1
		);

		$query = "
			select
				case
					when :fid = UnitLink_Fir then UL.UnitLink_UnitConv
					else 1/UL.UnitLink_UnitConv
				end as \"UnitConv\"
			from
				v_UnitLink UL
			where
				:fid in (UnitLink_Fir, UnitLink_Sec)
				and :sid in (UnitLink_Fir, UnitLink_Sec)
				and UnitType_fid = :UnitType_fid
				and UnitType_sid = :UnitType_sid
			limit 1
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result('array');

		$response = array('success' => true, 'Error_Msg' => '');
		if (count($resp) == 0) {
			$response['UnitConv'] = null;
		} else {
			$response['UnitConv'] = $resp[0]['UnitConv'];
		}
		return $response;
	}

	/**
	 * Проверка даты окончания единицы измерения
	 */
	function checkUnitSprEndDate($data) {
		$params = array(
			'Unit_id' => $data['Unit_id'],
			'UnitSpr_endDate' => $data['UnitSpr_endDate']
		);

		$query = "
			select
				to_char(t.begDT, 'dd.mm.yyyy') as \"begDT\"
			from (

				(select
					at.AnalyzerTest_begDT as begDT
				from
					lis.v_AnalyzerTest at
				where
					at.Unit_id = :Unit_id
						and (at.AnalyzerTest_begDT > :UnitSpr_endDate)
				order by
					at.AnalyzerTest_begDT desc
				limit 1)

				union all

				(select
					eup.EvnUslugaPar_setDT as begDT
				from
					v_EvnUslugaPar eup
				where
					eup.Unit_id = :Unit_id
						and (eup.EvnUslugaPar_setDT > :UnitSpr_endDate)
				order by
					eup.EvnUslugaPar_setDT desc
				limit 1)
			) as t
			order by
				t.begDT desc
			limit 1
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение коэффициента для конвертации едениц измерения
	 * @param array $data
	 * @return array
	 */
	function getUnitLinkUnitConv($data) {
		$params = array(
			'Unit_id' => $data['Unit_id'],
			'baseUnit_id' => $data['baseUnit_id'],
			'UnitType_id' => $data['UnitType_id'],
		);

		$query = "
			select
				ul.UnitLink_UnitConv as \"UnitLink_UnitConv\"
			from
				v_UnitLink ul
			where
				ul.UnitLink_Fir = :Unit_id
				and ul.UnitType_fid = :UnitType_id
				and ul.UnitLink_Sec = :baseUnit_id
				and ul.UnitType_sid = :UnitType_id
				
			union all
			
			select
				1 / ul.UnitLink_UnitConv as \"UnitLink_UnitConv\"
			from
				v_UnitLink ul
			where
				ul.UnitLink_Sec = :Unit_id
				and ul.UnitType_sid = :UnitType_id
				and ul.UnitLink_Fir = :baseUnit_id
				and ul.UnitType_fid = :UnitType_id
		";

		$UnitLink_UnitConv = $this->getFirstResultFromQuery($query, $params, true);
		if ($UnitLink_UnitConv === false) {
			return $this->createError('','Ошибка при получении коэффициента для конвертации едениц измерения');
		}

		return array(array(
			'success' => true,
			'UnitLink_UnitConv' => $UnitLink_UnitConv
		));
	}
}
