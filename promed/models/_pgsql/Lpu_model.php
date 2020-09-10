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

class Lpu_model extends swPgModel {

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
				L0.Lpu_id as \"Lpu_id\",
				L0.Lpu_Name as \"Lpu_Name\",
				L0.Lpu_Nick as \"Lpu_Nick\",
				--L0.Level_id,
				L0.Lpu_id as \"id\",
				(case when ronmk.lpusectiondoptype_id = 2 then '<font color=blue><b>РСЦ' || ' ' || L0.Lpu_Nick || '</b></font>' when ronmk.lpusectiondoptype_id = 1 then '<font color=red><b>ПСО ' || L0.Lpu_Nick || '</b></font>' else L0.Lpu_Nick end) as \"text\",
				--L0.Lpu_Nick as text,
				(case when ronmk.lpusectiondoptype_id is null then 1 else 0 end) as \"leaf\"
			from
				v_Lpu L0 , dbo.RoutingONMK ronmk 
			where L0.lpu_id = ronmk.lpu_id
				";
		
		$query.=$where; 
		
		
		if ($data['node'] == 'root') {
			$query.= " union
				select
					20000 as \"Lpu_id\",
					'Прочие МО' as \"Lpu_Name\",
					'Прочие МО' as \"Lpu_Nick\",
					20000 as \"id\",
					'Прочие МО' as \"text\",
					1 as \"leaf\"";
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
			 ls.LpuSection_id as \"LpuSection_id\",
			 lb.LpuBuilding_id as \"LpuBuilding_id\",
			 lu.LpuUnit_id as \"LpuUnit_id\",
			 ls.LpuSectionAge_id as \"LpuSectionAge_id\",
			 ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			 ls.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
			 ls.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
			 ls.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
			 lut.LpuUnitType_id as \"LpuUnitType_id\",
			 ls.LpuSection_Code as \"LpuSection_Code\",
			 ls.LpuSection_Name as \"LpuSection_Name\",
			 lut.LpuUnitType_Code as \"LpuUnitType_Code\",
			 lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
			 to_char(ls.LpuSection_setDate, 'YYYY-MM-DD')|| ' ' ||  to_char(ls.LpuSection_setDate, 'HH24:MI:SS') as \"LpuSection_setDate\",
			 to_char(ls.LpuSection_disDate, 'YYYY-MM-DD')|| ' ' ||  to_char(ls.LpuSection_disDate, 'HH24:MI:SS') as \"LpuSection_disDate\",
			 ls.Lpu_id as \"Lpu_id\"
		from v_LpuSection ls 
			left join v_LpuUnit lu  on lu.LpuUnit_id = ls.LpuUnit_id
			left join v_LpuBuilding lb  on lb.LpuBuilding_id = lu.LpuBuilding_id
			left join v_LpuUnitType lut  on lut.LpuUnitType_id = lu.LpuUnitType_id
		where 
			ls.Lpu_id = :Lpu_id
			{$filter}
		", array('Lpu_id'=> $data['Lpu_id']));
	}

	function getDataForPersonCardLogText($data) {
		$text_arr = [];
		if (!empty($data['Lpu_id'])) {
			$Lpu_Nick = $this->pcmodel->getFirstResultFromQuery("
				select Lpu_Nick  as \"Lpu_Nick\" from v_Lpu  where Lpu_id = :Lpu_id limit 1
			", $data);
			if (!empty($Lpu_Nick)) {
				$text_arr[] = 'МО прикрепления: '.$Lpu_Nick;
			}
		}
		if (!empty($data['LpuRegionType_id'])) {
			$LpuRegionType_Name = $this->pcmodel->getFirstResultFromQuery("
				select LpuRegionType_Name  as \"LpuRegionType_Name\" from v_LpuRegionType  where LpuRegionType_id = :LpuRegionType_id limit 1
			", $data);
			if (!empty($LpuRegionType_Name)) {
				$text_arr[] = 'Тип участка: '.$LpuRegionType_Name;
			}
		}
		if (!empty($data['LpuRegion_id'])) {
			$LpuRegion_Name = $this->pcmodel->getFirstResultFromQuery("
				select LpuRegion_Name  as \"LpuRegion_Name\" from v_LpuRegion  where LpuRegion_id = :LpuRegion_id limit 1
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
			select 
				Lpu_id as \"Lpu_id\"
			from v_Lpu 
			where Org_id = :Org_id
			limit 1
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
		$result = $this->getFirstRowFromQuery("
           with LpuRegions as (
                select
                    msr.Lpu_id
                from v_MedStaffRegion msr
                inner join LpuRegionStreet lrs on msr.LpuRegion_id = lrs.LpuRegion_id
                inner join ERTerr Terr  on
                    ((lrs.KLCountry_id = Terr.KLCountry_id) or (lrs.KLCountry_id is null)) and
                    ((lrs.KLRGN_id = Terr.KLRGN_id) or (lrs.KLRGN_id is null)) and
                    ((lrs.KLSubRGN_id = Terr.KLSubRGN_id) or (lrs.KLSubRGN_id is null)) and
                    ((lrs.KLCity_id = Terr.KLCity_id) or (lrs.KLCity_id is null)) and
                    ((lrs.KLTown_id = Terr.KLTown_id) or (lrs.KLTown_id is null))
                    and Terr.ERTerr_id = :ERTerr_id and lrs.KLCountry_id is not null and lrs.KLRGN_id is not null
            )
            select
                1 as interr
            from v_Lpu l
            where
                l.Lpu_id = :Lpu_id 
				and (
				exists(
					select
							1
					from ERTerr Terr
					inner join OrgServiceTerr ost  on
						(
							((ost.KLCountry_id = Terr.KLCountry_id) or (ost.KLCountry_id is null)) and
							((ost.KLRGN_id = Terr.KLRGN_id) or (ost.KLRGN_id is null)) and
							((ost.KLSubRGN_id = Terr.KLSubRGN_id) or (ost.KLSubRGN_id is null)) and
							((ost.KLCity_id = Terr.KLCity_id) or (ost.KLCity_id is null)) and
							((ost.KLTown_id = Terr.KLTown_id) or (ost.KLTown_id is null))
						) and Terr.ERTerr_id = :ERTerr_id and ost.KLCountry_id is not null and ost.KLRGN_id is not null and ost.Org_id = l.Org_id
					) or l.Lpu_id in (select Lpu_id from LpuRegions)
				)",$params);
		return isset($result['interr']);
	}
}
