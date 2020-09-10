<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Lpu - модель для работы со справочником МО
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2019 ufa
 * @author			gilmiyarov 
 * @version			02042019
 */

class Lpu_model extends swModel {

	/**
	 * Возвращает данные для дерева МО с поиском в дереве
	 * С клиента передаются переменные:
	 * $data['node'], $data['Lpi_id'], $data['Lpu_Name'], $data['LpugLevel_id']
	 * @return bool
	 */
	function getLpuTreeSearchData($data)
	{
		$params = array();
		$where = '';
		// Глубина дерева, где 0 - глубина корня
		$depth = 4;
		// Сколько уровней текущему node до листа. Одновременно совпадает с номером поддерева листа.
		//$N = $depth - $data['DiagLevel_id'] - 1;
		
		
		if ($data['node'] == 'root') {
			$where .= ' and ronmk.LpuSectionDopType_id=2';
		} else {
			$where .= ' and ronmk.lpu_pid = :Lpu_pid';
			$params['Lpu_pid'] = $data['node'];
		}
		
		

		$query = "
			select
				L0.Lpu_id,
				L0.Lpu_Name,
				L0.Lpu_Nick,
				--L0.Level_id,
				L0.Lpu_id as id,
				(case when ronmk.lpusectiondoptype_id = 2 then '<font color=blue><b>РСЦ' + ' ' + L0.Lpu_Nick + '</b></font>' when ronmk.lpusectiondoptype_id = 1 then '<font color=red><b>ПСО ' + L0.Lpu_Nick + '</b></font>' else L0.Lpu_Nick end) as text,
				--L0.Lpu_Nick as text,
				(case when ronmk.lpusectiondoptype_id is null then 1 else 0 end) as leaf
			from
				v_Lpu L0 with(nolock), dbo.RoutingONMK ronmk with(nolock)
			where L0.lpu_id = ronmk.lpu_id
				";
		
		$query.=$where; 
		
		
		if ($data['node'] == 'root') {
			$query.= " union
				select
					20000 as Lpu_id,
					'Прочие МО' as Lpu_Name,
					'Прочие МО' as Lpu_Nick,
					20000 as id,
					'Прочие МО' as text,
					1 as leaf";
		}
		//echo getDebugSql($query, $params); exit;
				
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	function mGetLpuSectionList($data) {
		$filter = "";
		if (!empty($data['LpuUnitType_id'])) {
			$filter = "and lut.LpuUnitType_id in ({$data['LpuUnitType_id']})";
		}
		return $this->queryResult("
		select
			 ls.LpuSection_id,
			 lb.LpuBuilding_id,
			 lu.LpuUnit_id,
			 ls.LpuSectionAge_id,
			 ls.LpuSectionProfile_id,
			 ls.LpuSectionProfile_Code,
			 ls.LpuSectionProfile_Name,
			 ls.LpuSectionProfile_SysNick,
			 lut.LpuUnitType_id,
			 ls.LpuSection_Code,
			 ls.LpuSection_Name,
			 lut.LpuUnitType_Code,
			 lut.LpuUnitType_SysNick,
			 convert(varchar(10), ls.LpuSection_setDate,120)+ ' ' +  convert(varchar(10), ls.LpuSection_setDate,108) as LpuSection_setDate,
			 convert(varchar(10), ls.LpuSection_disDate,120)+ ' ' +  convert(varchar(10), ls.LpuSection_disDate,108) as LpuSection_disDate,
			 ls.Lpu_id
		from v_LpuSection ls with(nolock)
			left join v_LpuUnit lu with(nolock) on lu.LpuUnit_id = ls.LpuUnit_id
			left join v_LpuBuilding lb with(nolock) on lb.LpuBuilding_id = lu.LpuBuilding_id
			left join v_LpuUnitType lut with(nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
		where 
			ls.Lpu_id = :Lpu_id
			{$filter}
		", array('Lpu_id'=> $data['Lpu_id']));
	}

	function getDataForPersonCardLogText($data) {
		$text_arr = [];
		if (!empty($data['Lpu_id'])) {
			$Lpu_Nick = $this->pcmodel->getFirstResultFromQuery("
				select top 1 Lpu_Nick from v_Lpu with(nolock) where Lpu_id = :Lpu_id
			", $data);
			if (!empty($Lpu_Nick)) {
				$text_arr[] = 'МО прикрепления: '.$Lpu_Nick;
			}
		}
		if (!empty($data['LpuRegionType_id'])) {
			$LpuRegionType_Name = $this->pcmodel->getFirstResultFromQuery("
				select top 1 LpuRegionType_Name from v_LpuRegionType with(nolock) where LpuRegionType_id = :LpuRegionType_id
			", $data);
			if (!empty($LpuRegionType_Name)) {
				$text_arr[] = 'Тип участка: '.$LpuRegionType_Name;
			}
		}
		if (!empty($data['LpuRegion_id'])) {
			$LpuRegion_Name = $this->pcmodel->getFirstResultFromQuery("
				select top 1 LpuRegion_Name from v_LpuRegion with(nolock) where LpuRegion_id = :LpuRegion_id
			", $data);
			if (!empty($LpuRegion_Name)) {
				$text_arr[] = 'Номер участка: '.$LpuRegion_Name;
			}
		}

		return $text_arr;
	}

	function getLpuByOrg($data)
	{
		return $this->getFirstResultFromQuery("
			select top 1
				Lpu_id
			from v_Lpu with(nolock)
			where Org_id = :Org_id
		", $data);
	}

	/**
	 * Обслуживает ли МО переданную территорию
	 */
	function TerrInLpuServiceTerr($terr_id, $Lpu_id) {
		$params = array(
			'ERTerr_id' => $terr_id,
			'Lpu_id' => $Lpu_id
		);
		$sql = $this->queryResult("
            with LpuRegions as (
                select
                    msr.Lpu_id
                from v_MedStaffRegion msr with (nolock)
                inner join LpuRegionStreet lrs with (nolock) on msr.LpuRegion_id = lrs.LpuRegion_id
                inner join ERTerr Terr with (nolock) on
                    ((lrs.KLCountry_id = Terr.KLCountry_id) or (lrs.KLCountry_id is null)) and
                    ((lrs.KLRGN_id = Terr.KLRGN_id) or (lrs.KLRGN_id is null)) and
                    ((lrs.KLSubRGN_id = Terr.KLSubRGN_id) or (lrs.KLSubRGN_id is null)) and
                    ((lrs.KLCity_id = Terr.KLCity_id) or (lrs.KLCity_id is null)) and
                    ((lrs.KLTown_id = Terr.KLTown_id) or (lrs.KLTown_id is null))
                    and Terr.ERTerr_id = :ERTerr_id and lrs.KLCountry_id is not null and lrs.KLRGN_id is not null
            )
            select
                1 as interr
            from v_Lpu l with (nolock)
            where
                l.Lpu_id = :Lpu_id and (
				exists(
					select
							1
					from ERTerr Terr with (nolock)
					inner join OrgServiceTerr ost with (nolock) on
						(
							((ost.KLCountry_id = Terr.KLCountry_id) or (ost.KLCountry_id is null)) and
							((ost.KLRGN_id = Terr.KLRGN_id) or (ost.KLRGN_id is null)) and
							((ost.KLSubRGN_id = Terr.KLSubRGN_id) or (ost.KLSubRGN_id is null)) and
							((ost.KLCity_id = Terr.KLCity_id) or (ost.KLCity_id is null)) and
							((ost.KLTown_id = Terr.KLTown_id) or (ost.KLTown_id is null))
						) and Terr.ERTerr_id = :ERTerr_id and ost.KLCountry_id is not null and ost.KLRGN_id is not null and ost.Org_id = l.Org_id
					) or l.Lpu_id in (select Lpu_id from LpuRegions)
				)
                ",$params);
		return isset($result->interr);
	}
}
