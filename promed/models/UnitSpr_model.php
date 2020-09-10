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

class UnitSpr_model extends swModel {
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
			select
				1 as UnitLinkType_id,
				ul.UnitLink_id,
				ut.UnitType_Name,
				case when ul.UnitType_sid = 1 then o.Okei_Name else u.Unit_Name end as UnitSpr_Name,
				case when ul.UnitType_sid = 1 then o.Okei_id else null end as Okei_id,
				case when ul.UnitType_sid = 2 then u.Unit_id else null end as Unit_id,
				ul.UnitLink_UnitConv
			from
				v_UnitLink ul (nolock)
				left join lis.v_Unit u (nolock) on u.Unit_id = ul.UnitLink_Sec
				left join v_Okei o (nolock) on o.Okei_id = ul.UnitLink_Sec
				left join v_UnitType ut (nolock) on ut.UnitType_id = ul.UnitType_sid
			where
				ul.UnitLink_Fir = :UnitLink_Fir
				and ul.UnitType_fid = :UnitType_fid
				
			union all
			
			select
				2 as UnitLinkType_id,
				ul.UnitLink_id,
				ut.UnitType_Name,
				case when ul.UnitType_fid = 1 then o.Okei_Name else u.Unit_Name end as UnitSpr_Name,
				case when ul.UnitType_fid = 1 then o.Okei_id else null end as Okei_id,
				case when ul.UnitType_fid = 2 then u.Unit_id else null end as Unit_id,
				1 / ul.UnitLink_UnitConv as UnitLink_UnitConv
			from
				v_UnitLink ul (nolock)
				left join lis.v_Unit u (nolock) on u.Unit_id = ul.UnitLink_Fir
				left join v_Okei o (nolock) on o.Okei_id = ul.UnitLink_Fir
				left join v_UnitType ut (nolock) on ut.UnitType_id = ul.UnitType_fid
			where
				ul.UnitLink_Sec = :UnitLink_Fir
				and ul.UnitType_sid = :UnitType_fid
			
			order by
				UnitSpr_Name
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
				us.UnitSpr_id,
				us.Okei_id,
				us.Unit_id,
				us.UnitSpr_Code,
				us.UnitType_Name,
				us.UnitSpr_Name,
				us.UnitSpr_begDate,
				us.UnitSpr_endDate,
				us.IsLinked
			from
			(
				select
					'o_' + cast(Okei_id as varchar) as UnitSpr_id,
					o.Okei_id,
					null as Unit_id,
					Okei_Code as UnitSpr_Code,
					OKei_Name as UnitSpr_Name,
					null as UnitSpr_begDate,
					null as UnitSpr_endDate,
					ut.UnitType_Name,
					case when ul.cnt > 0 then 'true' else 'false' end as IsLinked
				from
					v_Okei o (nolock)
					left join v_UnitType ut (nolock) on ut.UnitType_id = 1
					outer apply(
						select count(*) as cnt from v_UnitLink (nolock) where UnitLink_Fir = o.Okei_id and UnitType_fid = 1
					) ul
					
				union all

				select
					'u_' + cast(Unit_id as varchar) as UnitSpr_id,
					null as Okei_id,
					u.Unit_id,
					Unit_Code as UnitSpr_Code,
					Unit_Name as UnitSpr_Name,
					convert(varchar(10), Unit_begDate, 104) as UnitSpr_begDate,
					convert(varchar(10), Unit_endDate, 104) as UnitSpr_endDate,
					ut.UnitType_Name,
					case when ul.cnt > 0 then 'true' else 'false' end as IsLinked
				from
					lis.v_Unit u (nolock)
					left join v_UnitType ut (nolock) on ut.UnitType_id = 2
					outer apply(
						select count(*) as cnt from v_UnitLink (nolock) where UnitLink_Fir = u.Unit_id and UnitType_fid = 2
					) ul
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
			/*
			$Okei_id = $this->getFirstResultFromQuery("
				select top 1
					Okei_id
				from
					v_Okei (nolock)
				where
					Okei_Code = :UnitSpr_Code
					and (Okei_id <> :Okei_id OR :Okei_id IS NULL)
			", $data);

			if (!empty($Okei_id)) {
				return array('Error_Msg' => 'Указанный код уже используется в другой единице измерения');
			}
			*/

			if (!empty($data['Okei_id'])) {
				$query = "
					select top 1
						OkeiType_id,
						Okei_NationSymbol,
						Okei_InterNationSymbol,
						Okei_NationCode,
						Okei_InterNationCode,
						Okei_cid,
						Okei_UnitConversion
					from v_Okei with(nolock)
					where Okei_id = :Okei_id
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
				declare
					@Okei_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Okei_id = :Okei_id;
				exec {$proc}
					@Okei_id = @Okei_id output,
					@OkeiType_id = :OkeiType_id,
					@Okei_Code = :UnitSpr_Code,
					@Okei_Name = :UnitSpr_Name,
					@Okei_NationSymbol = :Okei_NationSymbol,
					@Okei_InterNationSymbol = :Okei_InterNationSymbol,
					@Okei_NationCode = :Okei_NationCode,
					@Okei_InterNationCode = :Okei_InterNationCode,
					@Okei_cid = :Okei_cid,
					@Okei_UnitConversion = :Okei_UnitConversion,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Okei_id as Okei_id, null as Unit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		} else {
		
			$proc = 'p_Unit_ins';
			if (!empty($data['Unit_id'])) {
				$proc = 'p_Unit_upd';
			} else {
				$data['Unit_id'] = null;
			}
			/*
			$Unit_id = $this->getFirstResultFromQuery("
				select top 1
					Unit_id
				from
					lis.v_Unit (nolock)
				where
					Unit_Code = :UnitSpr_Code
					and (Unit_id <> :Unit_id OR :Unit_id IS NULL)
			", $data);
			
			if (!empty($Unit_id)) {
				return array('Error_Msg' => 'Указанный код уже используется в другой единице измерения');
			}
			*/
			$query = "
				declare
					@Unit_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Unit_id = :Unit_id;
				exec lis.{$proc}
					@Unit_id = @Unit_id output,
					@Unit_Code = :UnitSpr_Code,
					@Unit_Name = :UnitSpr_Name,
					@Unit_begDate = :Unit_begDate,
					@Unit_endDate = :Unit_endDate,
					@Unit_SysNick = NULL,
					@Unit_Deleted = 1,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Unit_id as Unit_id, null as Okei_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				UnitLink_id
			from
				v_UnitLink (nolock)
			where
				UnitLink_Fir = :UnitLink_Fir
				and UnitType_fid = :UnitType_fid
				and UnitLink_Sec = :UnitLink_Sec
				and UnitType_sid = :UnitType_sid
				and (UnitLink_id <> :UnitLink_id OR :UnitLink_id IS NULL)
				
			union all
			
			select
				UnitLink_id
			from
				v_UnitLink (nolock)
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
			declare
				@UnitLink_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @UnitLink_id = :UnitLink_id;
			exec {$proc}
				@UnitLink_id = @UnitLink_id output,
				@UnitLink_Fir = :UnitLink_Fir,
				@UnitType_fid = :UnitType_fid,
				@UnitLink_Sec = :UnitLink_Sec,
				@UnitType_sid = :UnitType_sid,
				@UnitLink_UnitConv = :UnitLink_UnitConv,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @UnitLink_id as UnitLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
					'o_' + cast(Okei_id as varchar) as UnitSpr_id,
					Okei_id,
					null as Unit_id,
					Okei_Code as UnitSpr_Code,
					OKei_Name as UnitSpr_Name,
					1 as UnitType_id
				from
					v_Okei (nolock)
				where
					Okei_id = :Okei_id
			";
		} else {
			$query = "
				select
					'u_' + cast(Unit_id as varchar) as UnitSpr_id,
					null as Okei_id,
					Unit_id,
					Unit_Code as UnitSpr_Code,
					Unit_Name as UnitSpr_Name,
					convert(varchar(10), Unit_begDate, 104) as UnitSpr_begDate,
					convert(varchar(10), Unit_endDate, 104) as UnitSpr_endDate,
					2 as UnitType_id
				from
					lis.v_Unit (nolock)
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
				UnitLink_id,
				UnitLink_Fir,
				UnitType_fid,
				case when UnitType_sid = 1 then UnitLink_Sec else null end as Okei_id,
				case when UnitType_sid = 2 then UnitLink_Sec else null end as Unit_id,
				UnitType_sid,
				UnitLink_UnitConv
			from
				v_UnitLink (nolock)
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
				UnitLink_id,
				UnitLink_Sec as UnitLink_Fir,
				UnitType_sid as UnitType_fid,
				case when UnitType_fid = 1 then UnitLink_Fir else null end as Okei_id,
				case when UnitType_fid = 2 then UnitLink_Fir else null end as Unit_id,
				UnitType_fid as UnitType_sid,
				1 / UnitLink_UnitConv as UnitLink_UnitConv
			from
				v_UnitLink (nolock)
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.p_Unit_del
				@Unit_id = :Unit_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$queryParams = array(
			'Unit_id' => $data['Unit_id']
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
	function deleteUnitLink($data)
	{
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_UnitLink_del
				@UnitLink_id = :UnitLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_Okei_del
				@Okei_id = :Okei_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1
				case
					when :fid = UnitLink_Fir then UL.UnitLink_UnitConv
					else 1/UL.UnitLink_UnitConv
				end as UnitConv
			from
				v_UnitLink UL with(nolock)
			where
				:fid in (UnitLink_Fir, UnitLink_Sec)
				and :sid in (UnitLink_Fir, UnitLink_Sec)
				and UnitType_fid = :UnitType_fid
				and UnitType_sid = :UnitType_sid
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
			select top 1
				convert(varchar(10), t.begDT, 104) as begDT
			from (

				select top 1
					at.AnalyzerTest_begDT as begDT
				from
					lis.v_AnalyzerTest at with(nolock)
				where
					at.Unit_id = :Unit_id and (at.AnalyzerTest_begDT > :UnitSpr_endDate)
				order by
					at.AnalyzerTest_begDT desc

				union all

				select top 1
					eup.EvnUslugaPar_setDT as begDT
				from
					v_EvnUslugaPar eup with(nolock)
				where
					eup.Unit_id = :Unit_id and (eup.EvnUslugaPar_setDT > :UnitSpr_endDate)
				order by
					eup.EvnUslugaPar_setDT desc
			) as t
			order by
				t.begDT desc
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
				ul.UnitLink_UnitConv
			from
				v_UnitLink ul (nolock)
			where
				ul.UnitLink_Fir = :Unit_id
				and ul.UnitType_fid = :UnitType_id
				and ul.UnitLink_Sec = :baseUnit_id
				and ul.UnitType_sid = :UnitType_id
				
			union all
			
			select
				1 / ul.UnitLink_UnitConv as UnitLink_UnitConv
			from
				v_UnitLink ul (nolock)
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