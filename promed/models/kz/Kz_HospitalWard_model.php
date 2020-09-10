<?php
/**
* Модель - Больничные палаты (Казахстан)
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
*/

require_once(APPPATH.'models/HospitalWard_model.php');

class Kz_HospitalWard_model extends HospitalWard_model
{

	/**
	* Возвращает палаты отделения действующие и свободные на данный момент времени
	* 
	*/
	function getLpuSectionWardList($data)
	{
		$res = [];
		$data['fpid'] = $this->getFirstResultFromQuery("select FPID from r101.LpuSectionFPIDLink (nolock) where LpuSection_id = :LpuSection_id", $data);
		
		$basequery = "
			select 
				gr.GetRoom_id as LpuSectionWard_id,
				null as LpuSection_id,
				isnull(gr.Number, '') + ' - ' + gr.Name + ' ' + (case when gr.Child = 1 then '(Детская) ' else '' end) + fp.NameRu as LpuSectionWard_Name,
				null as LpuWardType_id,
				sex.Sex_id
			from r101.GetRoom gr (nolock) 
			inner join r101.GetFP fp (nolock) on fp.FPID = gr.FPID
			inner join r101.GetMO mo (nolock) on mo.ID = fp.MOID
			left join r101.hBIOSexLink sex (nolock) on sex.p_ID = gr.Sex
			where mo.Lpu_id = :Lpu_id
				and exists (
					select top 1 GetBed_id 
					from r101.GetBed gb (nolock) 
					where gb.RoomID = gr.ID 
					and gb.LastAction = 1
				)
		";
		
		if ($data['fpid']) {
			$query = $basequery;
			$query .=" and fp.FPID = :fpid ";
			$res = $this->queryResult($query, $data);
		}
		
		if (!count($res)) {
			$res = $this->queryResult($basequery, $data);
		}
		
		foreach($res as &$row) {
			$row['beds'] = $this->queryResult("
				select 
					gb.GetBed_id,
					gb.Name + ' (' + cast(gb.BedProfile as varchar) + ' ' + gb.BedProfileRu + '/' + gb.TypeSrcFinRu + ')' as BedProfileRuFull
				from r101.GetBed gb (nolock) 
				inner join r101.GetRoom gr (nolock) on gr.ID = gb.RoomID
				where 
					gr.GetRoom_id = ?
			", [$row['LpuSectionWard_id']]);
		}
		
		return $res;
	}
	
	/**
	* Возвращает палаты отделения 
	* 
	*/
	function getHospitalWardList($data)
	{
		$query = "
			select distinct 
				t.LpuSectionWard_id,
				t.LpuSection_id,
				isnull(t.Number, '') + ' - ' + t.Name + ' ' + (case when t.Child = 1 then '(Детская) ' else '' end) + fp.NameRu as LpuSectionWard_Name,
				null as LpuWardType_id,
				sex.Sex_id
			from (
				select
					gr.GetRoom_id as LpuSectionWard_id,
					lsfp.LpuSection_id,
					gr.Number,
					gr.Name,
					gr.Sex,
					gr.SexRu,
					gr.Child,
					lsfp.FPID
				from
					r101.LpuSectionFPIDLink lsfp (nolock) 
					inner join r101.GetRoom gr (nolock) on gr.FPID = lsfp.FPID
					left join r101.GetBed gb (nolock) on gb.RoomID = gr.ID
				where
					lsfp.LpuSection_id = :LpuSection_id
					and gb.LastAction != 4
					or gb.LastActionDateBeg > dbo.tzGetDate()
				
				union all
				
				select
					gr.GetRoom_id as LpuSectionWard_id,
					es.LpuSection_id,
					gr.Number,
					gr.Name,
					gr.Sex,
					gr.SexRu,
					gr.Child,
					gr.FPID
				from
					v_EvnSection es (nolock) 
					inner join r101.GetBedEvnLink gbel (nolock) on gbel.Evn_id = es.EvnSection_id
					inner join r101.GetBed gb (nolock) on gb.GetBed_id = gbel.GetBed_id
					inner join r101.GetRoom gr (nolock) on gr.ID = gb.RoomID
				where
					es.EvnSection_disDate is null
					and es.LpuSection_id = :LpuSection_id
					and gb.LastAction != 4
					or gb.LastActionDateBeg > dbo.tzGetDate()
			) as t
			inner join r101.GetFP fp (nolock) on fp.FPID = t.FPID
			left join r101.hBIOSexLink sex (nolock) on sex.p_ID = t.Sex
			order by LpuSectionWard_Name
		";

		$queryParams = array(
			'LpuSection_id' => $data['LpuSection_id'],
		);
		
		return $this->queryResult($query, $queryParams);
	}
	
	/**
	* Возвращает количество коек в палате (всего и свободных на текущий момент)
	*/
	function getCountBedLpuSectionWard($data, $date)
	{
		return true; // пока подсчёт не используется
	}

}