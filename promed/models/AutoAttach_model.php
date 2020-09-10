<?php
/**
* AutoAttach_model - модель, для работы с автоматическим прикреплением
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      11.12.2009
*/

class AutoAttach_model extends CI_Model {

	/**
	 * Comment
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * название говорит за себя )))) lulz
	 */
	function getLpuRegionsDataWithStreetsAndHouses($lpu_id,$LpuAttachType_id)
	{
		$list_LpuRegionType = '';
		switch ( $LpuAttachType_id ) {
			case 1:
				$list_LpuRegionType = '1,2,4';
			break;
			case 2:
				$list_LpuRegionType = '3';
			break;
		}
		if (empty($list_LpuRegionType))
		{
			echo "Автоприкрепление возможно для основного или гинекологического типов прикрепления!";
			return false;
		}
		$query = "
			SELECT
				KLCity_id, 
				KLTown_id, 
				KLStreet_id, 
				RTRIM(LpuRegionStreet_HouseSet) as LpuRegionStreet_HouseSet,
				lr.LpuRegion_id,
				lr.LpuRegion_Name,
				lr.LpuRegionType_id
			FROM 
				LpuRegionStreet lrs  with (nolock)
				inner join v_LpuRegion lr with (nolock)
					on lrs.LpuRegion_id = lr.LpuRegion_id and 
					lr.LpuRegionType_id in ({$list_LpuRegionType}) and
					lr.Lpu_id = ? and 
					( (lrs.KLTown_id is not null) or (lrs.KLStreet_id is not null) or (lrs.KLCity_id is not null) )
		";
		$res = $this->db->query($query, array($lpu_id));
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
			return false;
	}
	
	/**
	 * Comment
	 */
	function otkatAutoAttach($Lpu_id, $LpuAttachType_id)
	{
		if ( !isSuperAdmin() )
			return false;
		if (!in_array($LpuAttachType_id, array(1,2)))
		{
			echo "Откатить автоматически проставленные участки возможно только основного или гинекологического типов прикрепления!";
			return false;
		}
		if ( $Lpu_id > 0 )
		{
			$sql = "
				UPDATE PersonCard SET LpuRegion_id = null, PersonCard_updDT = dbo.tzGetDate(), PersonCard_IsAttachAuto = null, PersonCard_AttachAutoDT = null 
				WHERE 
					Lpu_id = ? 
					and LpuAttachType_id = ? 
					and PersonCard_IsAttachAuto = 2 
					and cast(convert(varchar(10), PersonCard_AttachAutoDT, 112) as datetime) = cast(convert(varchar(10), dbo.tzGetDate(), 112) as datetime)
			";
			//echo getDebugSQL($sql, array($Lpu_id))."<br/>GO<br/>";
			$this->db->trans_begin();
			$res = $this->db->query($sql, array($Lpu_id,$LpuAttachType_id));
			if ( $res > 0 )
			{
				$sql = "
					UPDATE PersonCardState SET LpuRegion_id = null, PersonCardState_updDT = dbo.tzGetDate(), PersonCard_IsAttachAuto = null, PersonCard_AttachAutoDT = null 
					WHERE 
						Lpu_id = ? 
						and LpuAttachType_id = ? 
						and PersonCard_IsAttachAuto = 2
						and cast(convert(varchar(10), PersonCard_AttachAutoDT, 112) as datetime) = cast(convert(varchar(10), dbo.tzGetDate(), 112) as datetime)
				";
				//echo getDebugSQL($sql, array($Lpu_id))."<br/>GO<br/>";
				$res = $this->db->query($sql, array($Lpu_id,$LpuAttachType_id));
				if ( $res > 0 )
				{
					$this->db->trans_commit();
					echo "Номера участков были сброшены.";
					return true;
				}
			}
			$this->db->trans_rollback();
			return false;
		}		
	}
	
	/**
	 * Comment
	 */
	function doPersonCardAttachUpdateAll(&$upd_candidates)
	{
		$sql = "";
		$cnt = 0;
		$params = array();
		$ct = 0;
		foreach ( $upd_candidates as $card )
		{
			$sql .= "
				UPDATE PersonCard SET LpuRegion_id = ?, PersonCard_updDT = dbo.tzGetDate(), PersonCard_IsAttachAuto = 2, PersonCard_AttachAutoDT = dbo.tzGetDate() WHERE PersonCard_id = ?
				UPDATE PersonCardState SET LpuRegion_id = ?, PersonCardState_updDT = dbo.tzGetDate(), PersonCard_IsAttachAuto = 2, PersonCard_AttachAutoDT = dbo.tzGetDate() WHERE PersonCardState_id = ?
			";
			$params[] = $card["lpuregion_id"];
			$params[] = $card["personcard_id"];
			$params[] = $card["lpuregion_id"];
			$params[] = $card["personcardstate_id"];
			$cnt++;
			$ct++;
			if ( $cnt >= 50 )
			{
				$cnt = 0;
				//echo "Следующая пачка:<br><br>";
				$sql = "
				BEGIN TRAN
				".$sql;
				$sql .= "
				COMMIT
				";
				//echo getDebugSQL($sql, $params)."<br><br>";
				$res = $this->db->query($sql, $params);
				if ( $res == true )
					echo "Уже обновлено ".$ct." карт.<br>";
				else
				{
					echo "Произошла ошибка при на количестве карт: ".$ct;
					return false;
				}
				$sql = "";
				$params = array();
			}			
		}
		if ( $sql != "" )
		{
			//echo "Следующая пачка:<br><br>";
			$sql = "
			BEGIN TRAN
			".$sql;
			$sql .= "
			COMMIT
			";
			//echo getDebugSQL($sql, $params)."<br>";
			$res = $this->db->query($sql, $params);
			if ( $res == true )
				echo "<font color='red'>Всего обновлено ".$ct." карт.</font><br>";
			else
			{
				echo "Произошла ошибка при на количестве карт: ".$ct;
				return false;
			}
		}
		else
		{
			echo "<font color='red'>Всего обновлено ".$ct." карт.</font><br>";
		}
	}
	
	/**
	 * Comment
	 */
	function doPersonCardAttachUpdate($person_card_id, $person_card_state_id, $lpu_region_id)
	{
		if ( !isSuperAdmin() )
			return false;
		$sql = "
			UPDATE PersonCard SET LpuRegion_id = ?, PersonCard_updDT = dbo.tzGetDate(), PersonCard_IsAttachAuto = 2, PersonCard_AttachAutoDT = dbo.tzGetDate() WHERE PersonCard_id = ?
		";
		//echo getDebugSQL($sql, array($lpu_region_id, $person_card_id))."<br/>GO<br/>";
		$this->db->trans_begin();
		$res = $this->db->query($sql, array($lpu_region_id, $person_card_id));
		if ( $res > 0 )
		{
			$sql = "
				UPDATE PersonCardState SET LpuRegion_id = ?, PersonCardState_updDT = dbo.tzGetDate(), PersonCard_IsAttachAuto = 2, PersonCard_AttachAutoDT = dbo.tzGetDate() WHERE PersonCardState_id = ?
			";
			//echo getDebugSQL($sql, array($lpu_region_id, $person_card_state_id))."<br/>GO<br/>";
			$res = $this->db->query($sql, array($lpu_region_id, $person_card_state_id));
			if ( $res > 0 )
			{
				$this->db->trans_commit();
				return true;
			}
		}
		$this->db->trans_rollback();
		return false;
	}
	
	/**
	 * Comment
	 */
	function doAutoAttach($lpu_id,$LpuAttachType_id, &$lpuregions_areas)
	{
		if ( !isSuperAdmin() )
			return false;
		$updated_person_cards_ids = array();
		$cnt = 0;
		// список карт на обновление
		$upd_candidates = array();
		// выбираем неприкрепленных людей, сначала с заполненым адресом проживания
		$query = "
			SELECT 
				pc.PersonCardState_id,
				pc.PersonCard_id,
				ps.Person_id,
				PAddress.KLCity_id,
				PAddress.KLTown_id,
				PAddress.KLStreet_id,
				rtrim(PAddress.Address_House) as Address_House,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				(datediff(year,ps.Person_BirthDay,dbo.tzGetDate())
				+ case when month(ps.Person_BirthDay)>month(dbo.tzGetDate())
				or (month(ps.Person_BirthDay)=month(dbo.tzGetDate()) and day(ps.Person_BirthDay)>day(dbo.tzGetDate()))
				then -1 else 0 end) as Person_Age
			FROM
				v_PersonCard pc with (nolock)
				inner join v_PersonState ps with (nolock) on 
					pc.Person_id = ps.Person_id 
					and pc.LpuRegion_id is null 
					and pc.Lpu_id = ? 
					and pc.LpuAttachType_id = ?
				inner join Address PAddress with (nolock) on ps.PAddress_id = PAddress.Address_id		
		";

		$res = $this->db->query($query, array($lpu_id,$LpuAttachType_id));
		if ( is_object($res) )
		{
			while ( $data = $res->_fetch_assoc() )
			{
				$lpu_region_id = $this->getLpuRegionIdToPersonAttach ( $data, $lpuregions_areas,$LpuAttachType_id);
				if ( $lpu_region_id !== false )
				{
					//$ret = $this->doPersonCardAttachUpdate($data['PersonCard_id'], $data['PersonCardState_id'], $lpu_region_id);
					$upd_candidates[] = array("personcard_id" => $data['PersonCard_id'], "personcardstate_id" => $data['PersonCardState_id'], "lpuregion_id" => $lpu_region_id);										
					$updated_person_cards_ids[$data['PersonCard_id']] = true;
					$cnt++;
				}
			}
		}
		else
			return false;
			
		// выбираем неприкрепленных людей с заполненым адресом регистрации
		$query = "
			SELECT 
				pc.PersonCardState_id,
				pc.PersonCard_id,
				ps.Person_id,
				UAddress.KLCity_id,
				UAddress.KLTown_id,
				UAddress.KLStreet_id,
				rtrim(UAddress.Address_House) as Address_House,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				convert(varchar,cast(ps.Person_BirthDay as datetime),104) as Person_BirthDay,
				(datediff(year,ps.Person_BirthDay,dbo.tzGetDate())
				+ case when month(ps.Person_BirthDay)>month(dbo.tzGetDate())
				or (month(ps.Person_BirthDay)=month(dbo.tzGetDate()) and day(ps.Person_BirthDay)>day(dbo.tzGetDate()))
				then -1 else 0 end) as Person_Age
			FROM
				v_PersonCard pc with (nolock)
				inner join v_PersonState ps with (nolock) on 
					pc.Person_id = ps.Person_id 
					and pc.LpuRegion_id is null 
					and pc.Lpu_id = ? 
					and pc.LpuAttachType_id = ?
				inner join Address UAddress with (nolock) on ps.UAddress_id = UAddress.Address_id
		";
		$res = $this->db->query($query, array($lpu_id,$LpuAttachType_id));
		if ( is_object($res) )
		{
			while ( $data = $res->_fetch_assoc() )
			{
				if ( !array_key_exists($data['PersonCard_id'], $updated_person_cards_ids) )
				{
					$lpu_region_id = $this->getLpuRegionIdToPersonAttach ( $data, $lpuregions_areas,$LpuAttachType_id);
					if ( $lpu_region_id !== false )
					{
						//$ret = $this->doPersonCardAttachUpdate($data['PersonCard_id'], $data['PersonCardState_id'], $lpu_region_id);
						$upd_candidates[] = array("personcard_id" => $data['PersonCard_id'], "personcardstate_id" => $data['PersonCardState_id'], "lpuregion_id" => $lpu_region_id);
						$cnt++;
					}
				}
			}
		}
		else
			return false;

		if ( count($upd_candidates) > 0 )
		{
			//echo "Обновлено карт: ".$cnt.".";
			$this->doPersonCardAttachUpdateAll($upd_candidates);
		}
		else
		{
			echo "Не было обновлено ни одной карты.";
		}
	}
	
	/**
	 * определение к какому участку прикреплять человека
	 * возарвщает идентификатор участки или false, если участок не определен
	 */
	function getLpuRegionIdToPersonAttach ( &$person_data, &$lpuregions_areas,$LpuAttachType_id)
	{
		// если есть улица 
		if ( isset($person_data['KLStreet_id']) && array_key_exists($person_data['KLStreet_id'], $lpuregions_areas['streets']) )
		{
			// решаем, к какому участку бы его прикрепить, кидаем дайс )))
			foreach ( $lpuregions_areas['streets'][$person_data['KLStreet_id']] as $region_id => $region_arr )
			{
				// есть дома в участке
				if ( strlen($region_arr['houses']) > 0 )
				{
					// у человека задан дом
					if ( strlen($person_data['Address_House']) > 0 )
					{
						if  ( $this->HouseExist(array($person_data['Address_House']), $region_arr['houses']) === true )
						{
							switch ( $LpuAttachType_id ) {
								case 1:
									// При основном типе прикрепления проверяем дату рождения
									if ( $person_data['Person_Age'] <= 17 && $region_arr['LpuRegionType_id'] == 2 )
										return $region_id;
									if ( $person_data['Person_Age'] > 17 && $region_arr['LpuRegionType_id'] != 2 )
										return $region_id;
								break;
								case 2:
									// При гинекологическом типе прикрепления нужна ли проверка на пол, если у человека уже был проставлен гинекологический тип прикрепления?
									return $region_id;
								break;
								default:
									return false;
								break;
							}
						}
					}
				}
				// если не указан диапазон, а только улица
				else
				{					
					switch ( $LpuAttachType_id ) {
						case 1:
							// При основном типе прикрепления проверяем дату рождения
							if ( $person_data['Person_Age'] <= 17 && $region_arr['LpuRegionType_id'] == 2 )
								return $region_id;
							if ( $person_data['Person_Age'] > 17 && $region_arr['LpuRegionType_id'] != 2 )
								return $region_id;
						break;
						case 2:
							// При гинекологическом типе прикрепления нужна ли проверка на пол, если у человека уже был проставлен гинекологический тип прикрепления?
							return $region_id;
						break;
						default:
							return false;
						break;
					}
				}
			}
		}
		else {
			// если только таун
			if ( isset($person_data['KLTown_id'])  && array_key_exists($person_data['KLTown_id'], $lpuregions_areas['towns']) )
			{
				// решаем, к какому участку бы его прикрепить, кидаем дайс )))
				foreach ( $lpuregions_areas['towns'][$person_data['KLTown_id']] as $region_id => $region_arr )
				{
					// есть дома в участке
					if ( strlen($region_arr['houses']) > 0 )
					{
						// у человека задан дом
						if ( strlen($person_data['Address_House']) > 0 )
						{
							if  ( $this->HouseExist(array($person_data['Address_House']), $region_arr['houses']) === true )
							{
								switch ( $LpuAttachType_id ) {
									case 1:
										// При основном типе прикрепления проверяем дату рождения
										if ( $person_data['Person_Age'] <= 17 && $region_arr['LpuRegionType_id'] == 2 )
											return $region_id;
										if ( $person_data['Person_Age'] > 17 && $region_arr['LpuRegionType_id'] != 2 )
											return $region_id;
									break;
									case 2:
										// При гинекологическом типе прикрепления нужна ли проверка на пол, если у человека уже был проставлен гинекологический тип прикрепления?
										return $region_id;
									break;
									default:
										return false;
									break;
								}
							}
						}
					}
					// если не указан диапазон, а только улица
					else
					{					
						switch ( $LpuAttachType_id ) {
							case 1:
								// При основном типе прикрепления проверяем дату рождения
								if ( $person_data['Person_Age'] <= 17 && $region_arr['LpuRegionType_id'] == 2 )
									return $region_id;
								if ( $person_data['Person_Age'] > 17 && $region_arr['LpuRegionType_id'] != 2 )
									return $region_id;
							break;
							case 2:
								// При гинекологическом типе прикрепления нужна ли проверка на пол, если у человека уже был проставлен гинекологический тип прикрепления?
								return $region_id;
							break;
							default:
								return false;
							break;
						}
					}
				}
			}
			// есть город
			else
			{
				if ( isset($person_data['KLCity_id'])  && array_key_exists($person_data['KLCity_id'], $lpuregions_areas['cityes']) )
				{
					// решаем, к какому участку бы его прикрепить, кидаем дайс )))
					foreach ( $lpuregions_areas['cityes'][$person_data['KLCity_id']] as $region_id => $region_arr )
					{
						// есть дома в участке
						if ( strlen($region_arr['houses']) > 0 )
						{
							// у человека задан дом
							if ( strlen($person_data['Address_House']) > 0 )
							{
								if  ( $this->HouseExist(array($person_data['Address_House']), $region_arr['houses']) === true )
								{
									switch ( $LpuAttachType_id ) {
										case 1:
											// При основном типе прикрепления проверяем дату рождения
											if ( $person_data['Person_Age'] <= 17 && $region_arr['LpuRegionType_id'] == 2 )
												return $region_id;
											if ( $person_data['Person_Age'] > 17 && $region_arr['LpuRegionType_id'] != 2 )
												return $region_id;
										break;
										case 2:
											// При гинекологическом типе прикрепления нужна ли проверка на пол, если у человека уже был проставлен гинекологический тип прикрепления?
											return $region_id;
										break;
										default:
											return false;
										break;
									}
								}
							}
						}
						// если не указан диапазон, а только улица
						else
						{					
							switch ( $LpuAttachType_id ) {
								case 1:
									// При основном типе прикрепления проверяем дату рождения
									if ( $person_data['Person_Age'] <= 17 && $region_arr['LpuRegionType_id'] == 2 )
										return $region_id;
									if ( $person_data['Person_Age'] > 17 && $region_arr['LpuRegionType_id'] != 2 )
										return $region_id;
								break;
								case 2:
									// При гинекологическом типе прикрепления нужна ли проверка на пол, если у человека уже был проставлен гинекологический тип прикрепления?
									return $region_id;
								break;
								default:
									return false;
								break;
							}
						}
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * Comment
	 */
	function getHouseArray($arr)
	{
		$arr = trim($arr);
		//print $arr.": ";
		if (preg_match( "/^([Ч|Н])\((\d+)([а-яА-Я]*)\-(\d+)([а-яА-Я]?)\)$/i", $arr, $matches))
		{
			// Четный или нечетный 
			$matches[count($matches)] = 1;
			return $matches;
		}
		elseif (preg_match( "/^([\s]?)(\d+)([а-яА-Я]*)\-(\d+)([а-яА-Я]?)$/i", $arr, $matches))
		{
			// Обычный диапазон
			$matches[count($matches)] = 2;
			return $matches;
		}
		elseif (preg_match( "/^(\d+[а-яА-Я]?[\/]?\d{0,3}[а-яА-Я]?(\s[к]\d{0,3})?)$/i", $arr, $matches))
		{
			//print $arr." ";
			if (preg_match( "/^(\d+)/i", $matches[1], $ms))
			{
				$matches[count($matches)] = $ms[1];
			}
			else 
			{
				$matches[count($matches)] = '';
			}
			$matches[count($matches)] = 3;
			return $matches;
		}
		return array();
	}
	
	/**
	 * Возвращает признак вхождения в диапазон домов
	 */
	function HouseExist($h_arr, $houses)
	{
		// Сначала разбираем h_arr и определяем: 
		// 1. Обычный диапазон 
		// 2. Четный диапазон
		// 3. Нечетный диапазон
		// 4. Перечисление 
		
		// Разбиваем на номера домов и диапазоны с которым будем проверять
		$hs_arr = preg_split('[,|;]', $houses, -1, PREG_SPLIT_NO_EMPTY);
		$i = 0;
		foreach ($h_arr as $row_arr)
		{
			//print $row_arr."   | ";
			$ch = $this->getHouseArray($row_arr); // сохраняемый 
			//print_r($ch);
			if (count($ch)>0)
			{
				//print $i."-";
				foreach ($hs_arr as $rs_arr)
				{
					$chn = $this->getHouseArray($rs_arr); // выбранный
					if (count($chn)>0) 
					{
						// Проверка на правильность указания диапазона
						if ((($ch[count($ch)-1] == 1) || ($ch[count($ch)-1] == 2)) && ($ch[2]>$ch[4]))
						{
							return false;
						}
						
						if ((($ch[count($ch)-1] == 1) && ($chn[count($chn)-1] == 1) && ($ch[1]=='Ч') && ($chn[1]=='Ч')) || // сверяем четный с четным
								(($ch[count($ch)-1] == 1) && ($chn[count($chn)-1] == 1) && ($ch[1]=='Н') && ($chn[1]=='Н')) || // сверяем нечетный с нечетным
								((($ch[count($ch)-1] == 1) || ($ch[count($ch)-1] == 2)) && ($chn[count($chn)-1] == 2)))        // или любой диапазон с обычным
						{
							if (($ch[2]<=$chn[4]) && ($ch[4]>=$chn[2]))
							{
								return true; // Перечесение (С) и (В) диапазонов
							}
						}
						if ((($ch[count($ch)-1] == 1) || ($ch[count($ch)-1] == 2)) && ($chn[count($chn)-1] == 3)) // Любой диапазон с домом 
						{
							if ((($ch[1]=='Ч') && ($chn[2]%2==0)) || // если четный
									(($ch[1]=='Н') && ($chn[2]%2<>0)) || // нечетный 
									($ch[count($ch)-1] == 2)) // обычный
							{
								if (($ch[2]<=$chn[2]) && ($ch[4]>=$chn[2]))
								{
									return true; // Перечесение диапазона с конкретным домом
								}
							}
						}
						if ((($chn[count($chn)-1] == 1) || ($chn[count($chn)-1] == 2)) && ($ch[count($ch)-1] == 3)) // Любой дом с диапазоном
						{
							if ((($chn[1]=='Ч') && ($ch[2]%2==0)) || // если четный
									(($chn[1]=='Н') && ($ch[2]%2<>0)) || // нечетный 
									($chn[count($chn)-1] == 2)) // обычный
							{
								if (($chn[2]<=$ch[2]) && ($chn[4]>=$ch[2]))
								{
									return true; // Перечесение дома с каким-либо диапазоном
								}
							}
						}
						if (($ch[count($ch)-1] == 3) && ($chn[count($chn)-1] == 3)) // Дом с домом
						{
							if (strtolower($ch[0])==strtolower($chn[0]))
							{
								return true; // Перечесение дома с домом
							}
						}
					}
				}
			}
			else 
			{
				return false; // Перечесение дома с домом
			}
		}
		return "";
	}
}