<?php
defined('BASEPATH') or die('No direct script access allowed');

/**
 * Класс TerritoryService_model
 * 
 * Модель для работы с обслуживаемыми территориями
 */
class TerritoryService_model extends swPgModel {

	/**
	 * Чётные
	 */
	const TYPE_EVEN = 2;

	/**
	 * Нечётные
	 */
	const TYPE_ODD = 1;

	/**
	 * Алиас четных
	 */
	const ALIAS_EVEN = 'Ч';

	/**
	 * Алиас нечетных
	 */
	const ALIAS_ODD = 'Н';


	/**
	 * Сохранение номера дома
	 * 
	 * @param int $TerritoryServiceHouseRange_From Начало диапазона нумерации домов
	 * @param int $TerritoryServiceHouseRange_To Окончание диапазона нумерации домов
	 * @param int $TerritoryServiceHouseRange_OddEven Чётность (1-нечетные, 2-четные)
	 * @param int $pmUser_id ID пользователя
	 * @return type
	 */
	public function insertTerritoryServiceHouseRange($TerritoryServiceHouseRange_From, $TerritoryServiceHouseRange_To, $TerritoryServiceHouseRange_OddEven, $pmUser_id, $TerritoryServiceHouseRange_id){
		
		if($TerritoryServiceHouseRange_id){
			$rangeId = $TerritoryServiceHouseRange_id;
			$proc = 'p_TerritoryServiceHouseRange_upd';
			$p = 'TerritoryServiceHouseRange_id := :rangeId,';
			$h = $TerritoryServiceHouseRange_id;
		}
		else{
			$rangeId = 'TerritoryServiceHouseRange_id';
			$proc = 'p_TerritoryServiceHouseRange_ins';
			$p = 'TerritoryServiceHouseRange_id = :TerritoryServiceHouseRange_id,';
			$h = 'TerritoryServiceHouseRange_id';
		}
		$sql = "
		    select 
		        $h as \"TerritoryServiceHouseRange_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from $proc(
				$p
				TerritoryServiceHouseRange_From := :TerritoryServiceHouseRange_From,
				TerritoryServiceHouseRange_To := :TerritoryServiceHouseRange_To,
				TerritoryServiceHouseRange_OddEven := :TerritoryServiceHouseRange_OddEven,
				pmUser_id := :pmUser_id
                )
			
		";
		
		$params = array(
			'TerritoryServiceHouseRange_From' => (int) $TerritoryServiceHouseRange_From,
			'TerritoryServiceHouseRange_To' => (int) $TerritoryServiceHouseRange_To,
			'TerritoryServiceHouseRange_OddEven' => (int) $TerritoryServiceHouseRange_OddEven,
			'pmUser_id' => $pmUser_id,
			'rangeId' => $rangeId
		);
		
		//var_dump(getDebugSql($sql, $params)); exit;

		return $this->db->query($sql,$params)->row_array();
	}

	/**
	 * Сохранение территории обслуживаемой подразделением
	 * 
	 * @param array $data
	 * @return array|null
	 */
	public function saveLpuBuildingTerritoryService($data){
		if (empty($data['LpuBuilding_id'])) {
			return;
		}
		
		$is_all = !empty($data['LpuBuildingStreet_IsAll']) && ($data['LpuBuildingStreet_IsAll'] == self::YES_ID || $data['LpuBuildingStreet_IsAll'] == self::CHECKBOX_VAL) ? true : false;
		
		$params = array(	
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'KLCountry_id' => $data['KLCountry_id'],
			'KLRGN_id' => $data['KLRegion_id'],
			'KLSubRGN_id' => !empty($data['KLSubRegion_id']) ? $data['KLSubRegion_id'] : null,
			'KLCity_id' => !empty($data['KLCity_id']) ? $data['KLCity_id'] : null,
			'KLTown_id' => !empty($data['KLTown_id']) ? $data['KLTown_id'] : null,
			'KLStreet_id' => !empty($data['KLStreet_id']) ? $data['KLStreet_id'] : null,
			'LpuBuildingStreet_HouseSet' => !empty($data['LpuBuildingStreet_HouseSet']) ? $data['LpuBuildingStreet_HouseSet'] : null,
			'LpuBuildingStreet_IsAll' => $is_all ? self::YES_ID : self::NO_ID,
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => $data['Server_id']
		);
		
		if(isset($data['LpuBuildingStreet_id'])){
			$proc = 'p_LpuBuildingStreet_upd';
			$p = 'LpuBuildingStreet_id := :LpuBuildingStreet_id,';
			$params['LpuBuildingStreet_id'] = $data['LpuBuildingStreet_id'];
			$h = $data['LpuBuildingStreet_id'];
		}
		else{
			$proc = 'p_LpuBuildingStreet_ins';
			$p = 'LpuBuildingStreet_id := null,';
			$h = 'LpuBuildingStreet_id';
		}
		
		$sql = "
		    select
		        $h as \"TerritoryServiceHouseRange_id\",
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			from $proc(
				$p				
				LpuBuilding_id := :LpuBuilding_id,
				KLCountry_id := :KLCountry_id,
				KLRGN_id := :KLRGN_id,
				KLCity_id := :KLCity_id,
				KLTown_id := :KLTown_id,
				KLStreet_id := :KLStreet_id,
				KLSubRGN_id := :KLSubRGN_id,
				LpuBuildingStreet_HouseSet := :LpuBuildingStreet_HouseSet,
				LpuBuildingStreet_IsAll := :LpuBuildingStreet_IsAll,
				pmUser_id := :pmUser_id,
				Server_id := :Server_id
                )
		";
		
		$result = $this->db->query($sql,$params)->row_array();
		
		return $result;

	}

	/**
	 * Возвращает список обслуживаемых территоий по ID подразделения
	 * 
	 * @param int $LpuBuilding_id
	 * @return array
	 */
	public function loadTerritoryServiceListByLpuBuildingId($LpuBuilding_id){
		
		$sql = "
			SELECT
				lbs.LpuBuildingStreet_id as \"LpuBuildingStreet_id\",
				lbs.LpuBuilding_id as \"LpuBuilding_id\",
				lbs.KLCountry_id as \"KLCountry_id\",
				lbs.KLRGN_id as \"KLRGN_id\",
				lbs.KLSubRGN_id as \"KLSubRGN_id\",
				lbs.KLCity_id as \"KLCity_id\",
				lbs.KLTown_id as \"KLTown_id\",
				lbs.KLStreet_id as \"KLStreet_id\",
				-- ts.KLHouse_id, -- пока нигде не используется
				lbs.LpuBuildingStreet_IsAll as \"LpuBuildingStreet_IsAll\",
				lbs.LpuBuildingStreet_HouseSet as \"LpuBuildingStreet_HouseSet\",
				SRGN.KLSubRgn_Name as \"KLSubRgn_Name\",
				COALESCE(City.KLCity_Name, RGNCity.KLRgn_Name) as \"KLCity_Name\",
				Town.KLTown_Name as \"KLTown_Name\",
				kls.KLStreet_FullName as \"KLStreet_FullName\"
			FROM
				v_LpuBuildingStreet lbs
				LEFT JOIN v_KLRgn RGNCity on RGNCity.KLRgn_id = lbs.KLCity_id
				LEFT JOIN v_KLSubRgn SRGN on SRGN.KLSubRgn_id = lbs.KLSubRGN_id
				LEFT JOIN v_KLCity City on City.KLCity_id = lbs.KLCity_id
				LEFT JOIN v_KLTown Town on Town.KLTown_id = lbs.KLTown_id
				LEFT JOIN v_KLStreet kls ON(kls.KLStreet_id=lbs.KLStreet_id)
			WHERE
				lbs.LpuBuilding_id=:LpuBuilding_id
		";
		
			return $this->db->query($sql, array('LpuBuilding_id' => $LpuBuilding_id))->result_array();

	}
	
	/**
	 * Возвращает данные для формы редактирования
	 * 
	 * @param int $LpuBuilding_id ID подразделения
	 * @param int $KLStreet_id ID улицы
	 * @param array $filter Дополнительные параметры фильтрации
	 * @return array
	 */
	public function getLpuBuildingTerritoryServiceForEdit($LpuBuilding_id, $LpuBuildingStreet_id/*, $LpuBuildingTerritoryServiceRel_id, $data=array()*/){
		
		$sql = "
			SELECT 
				lbs.LpuBuildingStreet_id as \"LpuBuildingStreet_id\",
				lbs.LpuBuilding_id as \"LpuBuilding_id\",
				lbs.KLCountry_id as \"KLCountry_id\",
				lbs.KLRGN_id as \"KLRegion_id\",
				lbs.KLSubRGN_id as \"KLSubRegion_id\",
				lbs.KLCity_id as \"KLCity_id\",
				lbs.KLTown_id as \"KLTown_id\",
				lbs.KLStreet_id as \"KLStreet_id\",
				-- ts.KLHouse_id, -- пока нигде не используется
				lbs.LpuBuildingStreet_IsAll as \"LpuBuildingStreet_IsAll\",
				lbs.LpuBuildingStreet_HouseSet as \"LpuBuildingStreet_HouseSet\",
				SRGN.KLSubRgn_Name as \"KLSubRgn_Name\",
				COALESCE(City.KLCity_Name, RGNCity.KLRgn_Name) as \"KLCity_Name\",
				Town.KLTown_Name as \"KLTown_Name\",
				kls.KLStreet_FullName as \"KLStreet_FullName\"
			FROM
				v_LpuBuildingStreet lbs
				LEFT JOIN v_KLRgn RGNCity on RGNCity.KLRgn_id = lbs.KLCity_id
				LEFT JOIN v_KLSubRgn SRGN on SRGN.KLSubRgn_id = lbs.KLSubRGN_id
				LEFT JOIN v_KLCity City on City.KLCity_id = lbs.KLCity_id
				LEFT JOIN v_KLTown Town on Town.KLTown_id = lbs.KLTown_id
				LEFT JOIN v_KLStreet kls on (kls.KLStreet_id=lbs.KLStreet_id)
			WHERE
				lbs.LpuBuilding_id=:LpuBuilding_id
				and lbs.LpuBuildingStreet_id =:LpuBuildingStreet_id
			limit 1
		";
		
		return $this->db->query($sql, array(
			'LpuBuildingStreet_id' => $LpuBuildingStreet_id,
			'LpuBuilding_id' => $LpuBuilding_id
		))->result_array();
		
	}

    /**
     * получение KLAreaStat_id адреса по юр адресу
     * @param $data
     * @return array
     */
	public function getKLAreaStatLpuByUAddress($data)
    {
		
		$sql = "
            select 
				addr.KLCountry_id as \"KLCountry_id\",
				addr.KLRgn_id as \"KLRgn_id\",
				addr.KLSubRgn_id as \"KLSubRgn_id\",
				addr.KLCity_id as \"KLCity_id\",
				addr.KLTown_id as \"KLTown_id\"
            from
                v_Lpu lpu
				left join Address addr on addr.Address_id = lpu.UAddress_id
            where
                lpu.Lpu_id = :lpu_id
		";
				
		$params = ['lpu_id' => $data['Lpu_id']];
		$result = $this->db->query($sql, $params)->result_array();
		if(isset($result[0])){
			
			$where = array();
			
			if ( isset($result[0]['KLCountry_id']) )
			{ $where[] = ' KLArea.KLCountry_id = ' . $result[0]['KLCountry_id']; }
		
			if ( isset($result[0]['KLRgn_id']) )
			{ $where[] = ' KLArea.KLRgn_id = ' . $result[0]['KLRgn_id']; }
		
			if ( isset($result[0]['KLSubRgn_id']) )
			{ $where[] = ' KLArea.KLSubRGN_id = ' . $result[0]['KLSubRgn_id']; }
			
			if ( isset($result[0]['KLCity_id']) )
			{ $where[] = ' KLArea.KLCity_id = ' . $result[0]['KLCity_id']; }
			
			if ( isset($result[0]['KLTown_id']) )
			{ $where[] = ' KLArea.KLTown_id = ' . $result[0]['KLTown_id']; }
			if(count($where) > 0){
			    $where = ImplodeWherePH($where);
				$dsql = "
                    select 
                        KLAreaStat_id as \"KLAreaStat_id\",
                        KLArea.KLCountry_id as \"KLCountry_id\",
                        KLArea.KLRgn_id as \"KLRgn_id\",
                        KLArea.KLSubRGN_id as \"KLSubRGN_id\",
                        KLArea.KLCity_id as \"KLCity_id\",
                        KLArea.KLTown_id as \"KLTown_id\"
                    from
                        v_KLAreaStat as KLArea
                        {$where}
                    limit 1
		        ";

				$res = $this->db->query($dsql);
				if(is_object($res)) {
				    return $res->result_array();
                }
			}
			  
		}

		return $result;
	}
	
	/**
	 * Возвращает данные для грида в форме редактирования
	 * 
	 * @param int $LpuBuilding_id ID подразделения
	 * @param int $LpuBuildingTerritoryServiceRel_id ID связи подразделения и территории обслуживания
	 * @return array
	 */
	public function getLpuBuildingTerritoryServiceHousesForEdit($LpuBuilding_id, $LpuBuildingTerritoryServiceRel_id){
		$sql = "
			SELECT
				lbtsr.LpuBuildingTerritoryServiceRel_id as \"LpuBuildingTerritoryServiceRel_id\",
				lbtsr.LpuBuilding_id as \"LpuBuilding_id\",
				ts.TerritoryService_id as \"TerritoryService_id\",
				ts.KLCountry_id as \"KLCountry_id\",
				ts.KLRegion_id as \"KLRegion_id\",
				ts.KLSubRegion_id as \"KLSubRegion_id\",
				ts.KLCity_id as \"KLCity_id\",
				ts.KLTown_id as \"KLTown_id\",
				ts.KLStreet_id as \"KLStreet_id\",
				-- ts.KLHouse_id, -- пока нигде не используется
				ts.TerritoryService_All as \"TerritoryService_All\",
				tsh.TerritoryServiceHouse_id as \"TerritoryServiceHouse_id\",
				tsh.TerritoryServiceHouse_Name as \"TerritoryServiceHouse_Name\",
				tshr.TerritoryServiceHouseRange_id as \"TerritoryServiceHouseRange_id\",
				tshr.TerritoryServiceHouseRange_OddEven as \"TerritoryServiceHouseRange_OddEven\",
				CASE
					WHEN tshr.TerritoryServiceHouseRange_OddEven=:TYPE_ODD THEN :ALIAS_ODD
					WHEN tshr.TerritoryServiceHouseRange_OddEven=:TYPE_EVEN THEN :ALIAS_EVEN
					ELSE null
				END as \"TerritoryServiceHouseRange_OddEvenStr\", -- строковое значние
				tshr.TerritoryServiceHouseRange_From as \"TerritoryServiceHouseRange_From\",
				tshr.TerritoryServiceHouseRange_To as \"TerritoryServiceHouseRange_To\"
			FROM
				v_LpuBuildingTerritoryServiceRel lbtsr
				INNER JOIN v_TerritoryService ts_tmp ON(ts_tmp.TerritoryService_id=lbtsr.TerritoryService_id)
				
				-- Выгружаем для грида все похожие записи на указанную LpuBuildingTerritoryServiceRel_id
				INNER JOIN v_TerritoryService ts ON(
					COALESCE(ts.KLCountry_id, 0)=COALESCE(ts_tmp.KLCountry_id, 0)
					AND COALESCE(ts.KLRegion_id, 0)=COALESCE(ts_tmp.KLRegion_id, 0)
					AND COALESCE(ts.KLSubRegion_id, 0)=COALESCE(ts_tmp.KLSubRegion_id, 0)
					AND COALESCE(ts.KLCity_id, 0)=COALESCE(ts_tmp.KLCity_id, 0)
					AND COALESCE(ts.KLTown_id, 0)=COALESCE(ts_tmp.KLTown_id, 0)
					AND COALESCE(ts.KLStreet_id, 0)=COALESCE(ts_tmp.KLStreet_id, 0)
				)
				-- и только для указанного подразделения
				INNER JOIN v_LpuBuildingTerritoryServiceRel lbtsr_tmp ON(lbtsr_tmp.TerritoryService_id=ts.TerritoryService_id AND lbtsr_tmp.LpuBuilding_id=lbtsr.LpuBuilding_id)

				LEFT JOIN v_TerritoryServiceHouse tsh ON(tsh.TerritoryServiceHouse_id=ts.TerritoryServiceHouse_id)
				LEFT JOIN v_TerritoryServiceHouseRange tshr ON(tshr.TerritoryServiceHouseRange_id=ts.TerritoryServiceHouseRange_id)
			WHERE
				lbtsr.LpuBuildingTerritoryServiceRel_id=:LpuBuildingTerritoryServiceRel_id
				AND lbtsr.LpuBuilding_id=:LpuBuilding_id
		";
		
		return $this->db->query($sql, array(
			'LpuBuildingTerritoryServiceRel_id' => $LpuBuildingTerritoryServiceRel_id,
			'LpuBuilding_id' => $LpuBuilding_id,
			'TYPE_ODD' => self::TYPE_ODD,
			'TYPE_EVEN' => self::TYPE_EVEN,
			'ALIAS_ODD' => self::ALIAS_ODD,
			'ALIAS_EVEN' => self::ALIAS_EVEN,
		))->result_array();
	}
	
	/**
	 * Возвращает ID подразделения по указанному адресу
	 * 
	 * @param int $KLStreet_id ID улицы
	 * @param string $house Дом
	 * @param string $building Корпус
	 * @return array
	 */
	public function getLpuBuildingIdByAddress($data)
	{
		$params = array(
			'KLStreet_id' => $data["KLStreet_id"],
			'Area_pid' => $data['Area_pid'] ? $data['Area_pid']:'',
			'houseNum' => $data["house"] . (!empty($data['building']) ? '/' . $data['building'] : '')
		);

		$where = array();
		$location = '(1 = 1)';

		if ( isset($data['KLCountry_id']) ) {
			$where[] = ' KLArea.KLCountry_id = ' . $data['KLCountry_id'];
		}

		if(!empty($data['Lpu_id']) && !$data['allRegion']){
			$where[] = ' lb.Lpu_id = :Lpu_id';
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if(isset($data["town"])){
			$location = ' lbs.KLTown_id = :town';
			$params['town'] = $data['town'];
		}elseif ( isset($data["city"]) ) {
			$location = ' (lbs.KLCity_id = :city AND lbs.KLTown_id is null)';
			$params['city'] = $data['city'];
		}

		$where[] = 'sup.SmpUnitType_id in (1,2,3,8)';
		$where[] = '
			( (lbs.KLStreet_id=:KLStreet_id AND
			(dbo.GetHouse(lbs.LpuBuildingStreet_HouseSet, :houseNum) = 1 or LpuBuildingStreet_IsAll = 2) AND
			'.$location.') 
			OR  
			(lbs.KLStreet_id is NULL  AND 
			LpuBuildingStreet_IsAll = 2 AND
			'.$location.')
			OR 
			(lbs.KLSubRGN_id = :Area_pid 
			AND lbs.KLTown_id is null
			AND lbs.KLCity_id is null
			AND lbs.KLStreet_id is null
			AND LpuBuildingStreet_IsAll = 2 
			) )';
		
		$sql = "
			select *
				from v_LpuBuildingStreet lbs
				left join v_LpuBuilding lb on lbs.LpuBuilding_id = lb.LpuBuilding_id 
                left join v_SmpUnitParam sup on sup.LpuBuilding_id = lbs.LpuBuilding_id
                where
				" . Implode(" AND ", $where) . " 
				ORDER BY lbs.LpuBuildingStreet_HouseSet DESC, lbs.KLStreet_id DESC, lbs.KLTown_id DESC, lbs.KLCity_id DESC
			limit 1 
		";
		
		return $this->db->query($sql, $params)->result_array();
	}

	
	/**
	* Удаляем запись TerritoryService
	*
	*/
	public function deleteTerritoryService($data){
		$sql = "
		    select 
		       TerritoryService_id as \"TerritoryService_id\", 
		       Error_Code as \"Error_Code\", 
		       Error_Message as \"Error_Msg\"
			from p_LpuBuildingStreet_del(
				LpuBuildingStreet_id := :LpuBuildingStreet_id
				)
		";
		
		$params = array(
			'LpuBuildingStreet_id' => $data['LpuBuildingStreet_id']
		);

		$result = $this->db->query($sql, $params);
		
		return $result->row_array();
	}
}
