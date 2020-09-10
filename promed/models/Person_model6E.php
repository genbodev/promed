<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * Person_model - модель, для работы с людьми
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       SWAN Developers
 * @version      ?
 */
require_once(APPPATH.'models/Person_model.php');

class Person_model6E extends Person_model {
	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Поиск людей
	 */
	function getPersonGrid($data, $print = false, $get_count = false) {
		$addrFilter = '';
		$filters = array('(1 = 1)');
		$queryParams = array();
		$includePerson_ids = '';
		// Разбиваем запрос на несколько частей
		// Сначала собираем фильтры по PersonState
		$filterfio = '';
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if ( !empty($data['Person_id']) ) {
			$queryParams['Person_id'] = $data['Person_id'];
			$filterfio .= "and ps.Person_id = :Person_id ";
		}

		if ( !empty($data['Double_ids']) ) {
			$arr = json_decode($data['Double_ids']);
			$err = false;
			if (is_array($arr)) {
				foreach ($arr as $item) {
					if (!is_integer(0 + $item) ) {
						$err = true;
						break;
					}
				}
			} else {
				$err = true;
			}
			
			if (!$err && count($arr) > 0) {
				$Person_idsStr = implode(', ', $arr);
				$includePerson_ids = " or ps.Person_id in ({$Person_idsStr}) ";
			}
		}

		$isSearchByEncryp = false;
		$select_person_data = "
				case when PC.Lpu_id = :Lpu_id then PC.PersonCard_Code else null end as PersonCard_Code,
				PAC.PersonAmbulatCard_Num as PersonAmbulatCard_Num,
				PAC.PersonAmbulatCard_id,
				isnull('<a href=''#'' onClick=''getWnd(\"swPolisInfoWindow\").show({Person_id:' + CAST(ps.Person_id as varchar) + '});''>'+ case when pls.PolisType_id = 4 and isnull(ps.Person_EdNum, '') != '' then ps.Person_EdNum else isnull(ps.Polis_Ser, '') + ' ' + isnull(ps.Polis_Num, '') end +'</a>','') as Polis_Num,
				isnull(ps.Person_Inn,'') as Person_Inn,
				case
					when
						ISNULL(ps.Person_Phone, Pinf.PersonInfo_InternetPhone) IS NOT NULL
					then
						isnull('<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' + CAST(ps.Person_id as varchar) + '});''>'+ isnull(Pinf.PersonInfo_InternetPhone + ' (портал самозаписи); ','') + isnull(ps.Person_Phone + ' (БД)','') +'</a>','')
					else
						'<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' + CAST(ps.Person_id as varchar) + '});''>'+ 'Отсутствует' +'</a>'
				end as Person_Phone,
				ps.Person_Surname,
				ps.Person_Firname,
				ps.Person_Secname,
				convert(varchar(10), ps.Person_Birthday, 104) as Person_Birthday,
				dbo.Age2(ps.Person_Birthday, @curDT) as Person_Age,
				ps.Sex_id,
				convert(varchar(10), PS.Person_DeadDT, 104) as Person_deadDT,
				ISNULL(AttachLpu.Lpu_Nick, 'Не прикреплен') as AttachLpu_Name,
				ISNULL(AttachLpu.Lpu_id, 0) as AttachLpu_id,
				convert(varchar(10), cast(PC.PersonCard_begDate as datetime), 104) as PersonCard_begDate,
				convert(varchar(10), cast(PC.PersonCard_endDate as datetime), 104) as PersonCard_endDate,
				PC.LpuAttachType_Name,
				PC.LpuRegionType_Name,
				LR.LpuRegion_Name,
				ISNULL(LR_Fap.LpuRegion_Name,'') as LpuRegion_FapName,
				isnull(paddr.Address_Nick, paddr.Address_Address) as Person_PAddress,
				isnull(uaddr.Address_Nick, uaddr.Address_Address) as Person_UAddress,
		";
		$join = '';
		if (isEncrypHIVRegion($this->regionNick)) {
			if (allowPersonEncrypHIV($data['session'])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_Surname']);
				if ($isSearchByEncryp) {
					$select_person_data = "'' as PersonCard_Code,
						'' as Polis_Num,
						'' as Person_Inn,
						'' as Person_Phone,
						case when PEH.PersonEncrypHIV_id is not null then isnull(rtrim(PEH.PersonEncrypHIV_Encryp),'') else ps.Person_Surname end as Person_Surname,
						case when PEH.PersonEncrypHIV_id is null then ps.Person_Firname else '' end as Person_Firname,
						case when PEH.PersonEncrypHIV_id is null then ps.Person_Secname else '' end as Person_Secname,
						case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), ps.Person_Birthday, 104) else null end as Person_Birthday,
						case when PEH.PersonEncrypHIV_id is null then dbo.Age2(ps.Person_Birthday, @curDT) else null end as Person_Age,
						case when PEH.PersonEncrypHIV_id is null then ps.Sex_id end as Sex_id,
						null as Person_deadDT,
						'' as AttachLpu_Name,
						null as AttachLpu_id,
						null as PersonCard_begDate,
						null as PersonCard_endDate,
						'' as LpuAttachType_Name,
						'' as LpuRegionType_Name,
						'' as LpuRegion_Name,
						'' as Person_PAddress,
						'' as Person_UAddress,
					";
				}
			} else {
				//Не отображать анонимных шифрованных пациентов
				$filters[] = "not exists(
					select top 1 peh.PersonEncrypHIV_Encryp
					from v_PersonEncrypHIV peh with(nolock)
					inner join v_EncrypHIVTerr eht with(nolock) on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id
						and isnull(eht.EncrypHIVTerr_Code,0) = 20
					where peh.Person_id = ps.Person_id
				)";
			}
		}

		if ( !empty($data['Person_Surname']) && $data['Person_Surname'] != '_' ) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$join .= " inner join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ps.Person_id and peh.PersonEncrypHIV_Encryp like :Person_Surname";
			} else {
				$filterfio .= "and ps.Person_SurNameR LIKE :Person_Surname + '%' ";
			}
			$queryParams['Person_Surname'] = $this->prepareSearchSymbol($data['Person_Surname']);
		}

		if ( !empty($data['Person_Firname']) && $data['Person_Firname'] != '_' ) {
			$queryParams['Person_Firname'] = $this->prepareSearchSymbol($data['Person_Firname']);
			$filterfio .= "and ps.Person_FirnameR LIKE :Person_Firname + '%' ";
		}

		if ( !empty($data['Person_Secname']) && $data['Person_Secname'] != '_' ) {
			$queryParams['Person_Secname'] = $this->prepareSearchSymbol($data['Person_Secname']);
			$filterfio .= "and ps.Person_SecnameR LIKE :Person_Secname + '%' ";
		}

		if ( !empty($data['Person_Birthday']) ) {
			$filters[] = "ps.Person_Birthday = :Person_Birthday";
			$queryParams['Person_Birthday'] = $data['Person_Birthday'];
		}

		if ( !empty($data['Person_Code']) ) {
			$filters[] = "ps.Person_EdNum = :Person_EdNum";
			$queryParams['Person_EdNum'] = $data['Person_Code'];
		}

		if ( !empty($data['Person_Inn']) ) {
			$filters[] = "exists (select top 1 Person_id from v_PersonState with (nolock) where Person_id = ps.Person_id and Person_Inn = :Person_Inn)";
			$queryParams['Person_Inn'] = $data['Person_Inn'];
		}

		if ( !empty($data['Polis_Num']) ) {
			$filters[] = "pls.Polis_Num = :Polis_Num";
			$queryParams['Polis_Num'] = $data['Polis_Num'];
		}

		if ( !empty($data['Polis_Ser']) ) {
			$filters[] = "pls.Polis_Ser = :Polis_Ser";
			$queryParams['Polis_Ser'] = $data['Polis_Ser'];
		}

		if (!empty($data['showAll']) && $data['showAll'] == 1) {
			$filters[] .= "ps.Person_deadDT is null";
		}

		// Фильтр по адресу 
		// todo: Есть еще один момент, можно забить номер дома, но не указывать улицу, это долго :)
		if ( !empty($data['Address_Street']) || !empty($data['Address_House']) ) {
			if (
				(empty($data['Person_Surname']) || !preg_match("/^[\w\-\s]+$/iu", $data['Person_Surname']))
				&& (empty($data['Person_Firname']) || !preg_match("/^[\w\-\s]+$/iu", $data['Person_Firname']))
				&& (empty($data['Person_Secname']) || !preg_match("/^[\w\-\s]+$/iu", $data['Person_Secname']))
			) {
				// Для поиска по адресу требуется заполнить хотя бы одно поле из ФИО
				return false; 
			}

			$addrFilters = array();
			if ( !empty($data['Address_Street']) ) {
				$addrFilters[] = "ks.KLStreet_Name like :Address_Street";
				$queryParams['Address_Street'] = $data['Address_Street'] . '%';
			}
			if ( !empty($data['Address_House']) ) {
				$addrFilters[] = "a.Address_House = :Address_House";
				$queryParams['Address_House'] = $data['Address_House'];
			}

			$filters[] = "
				exists(select top 1 Address_id
					from [Address] a with (nolock)
					left join v_KLStreet ks with (nolock) on ks.KLStreet_id = a.KLStreet_id
					where a.Address_id in (ps.UAddress_id, ps.PAddress_id) and " . implode(' and ', $addrFilters) . "
				)
			";
		}

		$orderby = "";
		// Фильтры по прикреплению
		if ( !empty($data['PersonCard_Code']) ) {
			$personCardFilters = array('Person_id = ps.Person_id');
			$personCardFilters[] = 'Lpu_id = :Lpu_id'; // только в рамках своей МО

			if (!empty($data['PartMatchSearch'])) {
				// включен чекбокс "Поиск по частичному совпадению"
				if (!empty($this->config->config['blockSlowDownFunctions'])) {
					return array('Error_Msg' => 'Функционал поиска по частичному совпадению временно заблокирован. Приносим извинения за доставленные неудобства.');
				}

				//$personCardFilters[] = "PersonCard_Code LIKE '%'+:PersonCard_Code+'%'";
				$personCardFilters[] = "PersonAmbulatCard_Num LIKE '%'+:PersonCard_Code+'%'";
				$orderby = "case when ISNULL(CHARINDEX(:PersonCard_Code, pc.PersonCard_Code), 0) > 0 then CHARINDEX(:PersonCard_Code, pc.PersonCard_Code) else 99 end,";
			} else {
				//$personCardFilters[] = "PersonCard_Code = :PersonCard_Code";
				$personCardFilters[] = "PersonAmbulatCard_Num = :PersonCard_Code";
			}

			$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];

			/*$filters[] = "exists (
				select top 1 PersonCard_id
				from v_PersonCard with (nolock)
				where " . implode(' and ', $personCardFilters) . "
			)";*/
			$filters[] = "exists (
				select top 1 PersonAmbulatCard_id
				from v_PersonAmbulatCard with (nolock)
				where " . implode(' and ', $personCardFilters) . "
			)";
		}

		if (!empty($data['dontShowUnknowns'])) {// #158923 показывать ли неизвестных в АРМ регистратора поликлиники (ExtJS 6)
			$filters[] = 'isnull(PS.Person_IsUnknown,1) != 2';
		}
		
		
		If (count($queryParams)<=1) { // Если указан только обязательный фильтр по ЛПУ
			// Сообщим пользователю что нужно ввести хотя бы одно значение в фильтрах (при текущих проверках по этой ветке не должно пойти, но на всякий случай)
			return array('success' =>false,'Error_Msg' => toUtf('Не указан ни один фильтр. Для поиска заполните хотя бы один параметр в блоке фильтров.'));
		}
		$isPerm = $data['session']['region']['nick'] == 'perm';
		$isBDZ = "CASE
					WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= cast(convert(char(10), @curDT, 112) as datetime) THEN 'orange'
					ELSE CASE
						WHEN ps.PersonCloseCause_id = 2 and ps.Person_closeDT is not null THEN 'red'
						ELSE CASE
							WHEN ps.Server_pid = 0 THEN 'true'
							ELSE 'false'
						END
					END
				END as [Person_IsBDZ],";
		if($isPerm){
			$isBDZ ="case 
				when ps.Server_pid = 0 then 
	case when ps.Person_IsInErz = 1  then 'blue' 
	else case when pls.Polis_endDate is not null and pls.Polis_endDate <= cast(convert(char(10), @curDT, 112) as datetime) THEN 
		case when ps.Person_deadDT is not null then 'red' else 'yellow' end
	else 'true' end end 
	else 'false' end as [Person_IsBDZ],";
		}
		// Основной поисковый запрос
		$query = "
			-- variables
			declare @curDT datetime = dbo.tzGetDate();
			-- end variables
			select
				-- select
				PC.PersonCard_id,
				PS.Person_id,
				PS.Server_id,
				PS.PersonEvn_id,
				{$select_person_data}
				CASE WHEN PS.Person_DeadDT is not null  THEN 'true' ELSE 'false' END as Person_IsDead,
				CASE WHEN ISNULL(PC.PersonCard_IsAttachCondit, 1) = 2 then 'true' else 'false' end as PersonCard_IsAttachCondit,
				CASE WHEN persdata.agree = 2 THEN 'V' WHEN persdata.agree = 1 THEN 'X' else '' END as PersonLpuInfo_IsAgree,
				NA.NewslatterAccept_id,
				ISNULL(convert(varchar(11), NA.NewslatterAccept_begDate, 104), 'Отсутствует') as NewslatterAccept,
				CASE WHEN PC.PersonCardAttach_id IS NULL then 'false' else 'true' end as PersonCardAttach,
				CASE WHEN PS.Person_IsRefuse = 1 THEN 'true' ELSE 'false' END as Person_IsRefuse,
				CASE WHEN PRef.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as Person_NextYearRefuse,
				CASE WHEN PS.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as Person_IsFedLgot,
				CASE WHEN PS.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as Person_IsRegLgot,
				".$isBDZ."
				convert(varchar(10), PQ.PersonQuarantine_begDT, 104) as PersonQuarantine_begDT,
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as PersonQuarantine_IsOn,
				CASE 
					WHEN disp.OwnLpu = 1 THEN 'true'
					WHEN disp.OwnLpu is not null THEN 'gray'
					ELSE 'false'
				END as Person_Is7Noz
				--CASE WHEN PS.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				
				-- end select
			from
				-- from
				v_PersonState_All PS with (nolock)
				outer apply (
					select top 1 
						PQ.PersonQuarantine_id,
						PQ.PersonQuarantine_begDT
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = PS.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) PQ
				OUTER APPLY(
					SELECT TOP 1
						RTRIM(PersonInfo_InternetPhone) AS PersonInfo_InternetPhone
					FROM
						v_PersonInfo with (nolock)
					WHERE
						Person_id = PS.Person_id
				) Pinf
                outer apply (
                    select top 1 
                        case 
                            when LpuAttachType_id = 4 and Lpu_id = :Lpu_id then PersonCard_id -- если есть активное прикрепление к этой МО, то до этой ветки 
                            when LpuAttachType_id = 1 then PersonCard_id
                            when LpuAttachType_id in (2,3) and Lpu_id = :Lpu_id then PersonCard_id
                            else null
                        end as PersonCard_id
                    from v_PersonCard_all with(nolock)
                    where Person_id = PS.Person_id
						and PersonCard_endDate is null
						and LpuAttachType_id is not null
                    order by
						case when LpuAttachType_id = 4 and Lpu_id = :Lpu_id then 0 else LpuAttachType_id end,
						PersonCard_begDate
                ) as PersonCard
				left join v_PersonCard_all PC with (nolock) on PC.PersonCard_id = PersonCard.PersonCard_id
                left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
                left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
				left join v_Lpu AttachLpu with (nolock) on AttachLpu.Lpu_id = PC.Lpu_id 
                --left join v_PersonState Inn with(nolock) on Inn.Person_id = ps.Person_id 
                left join Address uaddr with (nolock) on uaddr.Address_id = ps.UAddress_id
                left join Address paddr with (nolock) on paddr.Address_id = ps.PAddress_id
                left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
				left join v_Person pers with (nolock) on pers.Person_id = ps.Person_id
				left join PersonRefuse PRef with (nolock) on (PRef.Person_id = ps.Person_id and PRef.PersonRefuse_Year=YEAR(@curDT)+1) 
				left join v_NewslatterAccept NA with (nolock) on NA.Person_id = PS.Person_id and NA.Lpu_id = :Lpu_id and NA.NewslatterAccept_endDate is null
				outer apply (
                    select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
                    from PersonDisp with (nolock)
                    where Person_id = ps.Person_id
					and (PersonDisp_endDate is null or PersonDisp_endDate > @curDT)
                    and Sickness_id IN (1,3,4,5,6,7,8)
                ) as disp
				outer apply (
					select top 1
						PersonLpuInfo_IsAgree as agree
					from v_PersonLpuInfo pli with (nolock)
					where pli.Person_id = PS.Person_id and pli.Lpu_id = :Lpu_id
					order by pli.PersonLpuInfo_setDT desc
				) persdata
				outer apply (
					select top 1
						PersonAmbulatCard_id,
						PersonAmbulatCard_Num
					from v_PersonAmbulatCard with (nolock)
					where Person_id = PS.Person_id and Lpu_id = :Lpu_id
					order by Person_id desc
				) PAC
				{$join}
				-- end from
			where
				-- where
				(" . implode(" and ", $filters) . " " . $filterfio . ")
				{$includePerson_ids}
				-- end where
			order by
				-- order by
				{$orderby}
				PS.Person_SurNameR,
				PS.Person_FirNameR,
				PS.Person_SecNameR
				-- end order by
		";

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
	}
}