<?php
/**
* PersonDispOrp13_model - модель, для работы с таблицей PersonDispOrp
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      май 2010
*/

class PersonDispOrp13_model extends CI_Model {
	/**
	 * Конструктор
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * Загрузка формы
	 */
	function loadPersonDispOrpEditForm($data)
	{
		$query = "
			select
				PDO.PersonDispOrp_id,
				PDO.Person_id,
				PDO.Server_id,
				PDO.Lpu_id,
				PDO.PersonDispOrp_Year,
				PDO.CategoryChildType_id,
				PDO.Org_id,
				PDO.EducationInstitutionType_id,
				PDO.AgeGroupDisp_id,
				convert(varchar,cast(PDO.PersonDispOrp_begDate as datetime),104) as PersonDispOrp_begDate,
				case 
					when PDO.CategoryChildType_id IN (10) then
						case when PDO.Org_id IS NOT NULL then 1 else 0 end
					else
						case when PDO.Org_id IS NOT NULL then 2 else 1 end
				end as OrgExist,
				EPLDTI.EvnPLDispTeenInspection_id,
				convert(varchar,cast(PDO.PersonDispOrp_setDate as datetime),104) as PersonDispOrp_setDate,
				PDO.DisposalCause_id,
				convert(varchar,cast(PDO.PersonDispOrp_DisposDate as datetime),104) as PersonDispOrp_DisposDate
			from
				v_PersonDispOrp PDO (nolock)
				outer apply(
					select top 1 EvnPLDispTeenInspection_id from v_EvnPLDispTeenInspection (nolock) where PersonDispOrp_id = PDO.PersonDispOrp_id
				) EPLDTI
			where
				PDO.PersonDispOrp_id = :PersonDispOrp_id
		";
		$res=$this->db->query($query, $data);
		
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Загрука списка для дбф
	 */
	function loadPersonDispOrpListForDbf($data)
	{
		$sql = "
			select
				rtrim(isnull(og.Org_OGRN, '')) as ORG_OGRN,
				rtrim(isnull(og.Org_name, '')) as LPU_NAME,
				rtrim(ps.Person_SurName) as FAM,
				rtrim(ps.Person_FirName) as IM,
				rtrim(isnull(ps.Person_SecName, '')) as OT,
				--sx.Sex_Code as SEX,
				rtrim(isnull(convert(varchar, cast(ps.Person_BirthDay as datetime),104),'')) as DR,
				/*
				rtrim(isnull(addr.Address_Address, '')) as ADDR,
				rtrim(isnull(ps.Polis_Ser, '')) as POL_SER,
				rtrim(isnull(ps.Polis_Num, '')) as POL_NUM,
				rtrim(isnull(ps.Person_Snils, '')) as SNILS,
				rtrim(isnull(okvd.Okved_Code, '')) as O_OKVED,
				*/
				rtrim(isnull(og1.Org_OGRN, '')) as LPU_OGRN
			from
				v_PersonDispOrp pdd (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = pdd.Person_id
				left join Job jb (nolock) on jb.Job_id = ps.Job_id
				left join Org og (nolock) on og.Org_id = jb.Org_id
				left join Okved okvd (nolock) on okvd.Okved_id = og.Okved_id
				left join Lpu lp (nolock) on lp.Lpu_id = pdd.Lpu_id
				left join Org og1 (nolock) on og1.Org_id = lp.Org_id
			where
				PersonDispOrp_Year = ?
			order by 
				FAM,
				IM,
				OT,
				DR
		";
		$res=$this->db->query($sql, array($data['PersonDispOrp_Year']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Загрузка отчета для дбф
	 */
	function loadPersonDispOrpLpuReportForDbf($data)
	{
		$sql = "
			select
				lp.Lpu_Name,
				count(PersonDispOrp_id) as cnt
			from
				v_PersonDispOrp pdd (nolock)
				inner join v_Lpu lp (nolock) on lp.Lpu_id = pdd.Lpu_id				
			where
				PersonDispOrp_Year = ?
			group by lp.Lpu_Name
			order by Lpu_Name
		";
		$res=$this->db->query($sql, array($data['PersonDispOrp_Year']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Возвращает список людей в регистре ДД по заданным фильтрам для поточного ввода
	*/
	function getPersonDispOrpStreamInputList($data)
	{
		$sql = "
			SELECT DISTINCT
				v_PersonDispOrp.PersonDispOrp_id,
				v_PersonState.Person_id,
				v_PersonState.Server_id,
				rtrim(v_PersonState.Person_SurName) as Person_SurName,
				rtrim(v_PersonState.Person_FirName)as Person_FirName,
				rtrim(v_PersonState.Person_SecName) as Person_SecName,
				Sex.Sex_Name,
				v_PersonState.Polis_Ser,
				v_PersonState.Polis_Num,
				okved1.Okved_Name as PersonOrg_Okved,
				org1.Org_OGRN as PersonOrg_OGRN,
				astat1.KLArea_Name as Person_KLAreaStat_Name,
				astat2.KLArea_Name as PersonOrg_KLAreaStat_Name,
				rtrim(addr1.Address_Address) as UAddress_Address,
				convert(varchar,cast(v_PersonState.Person_BirthDay as datetime),104) as Person_BirthDay
			FROM v_PersonDispOrp (nolock)
			INNER JOIN
				v_PersonState (nolock) on v_PersonDispOrp.Person_id=v_PersonState.Person_id
			LEFT JOIN
				Sex (nolock) on v_PersonState.Sex_id = Sex.Sex_id
			LEFT JOIN
				v_Job as job1 (nolock) ON v_PersonState.Job_id=job1.Job_id
			LEFT JOIN
				v_Org as org1 (nolock) ON job1.Org_id=org1.Org_id
			LEFT JOIN
				v_Okved as okved1 (nolock) ON okved1.Okved_id=org1.Okved_id
			LEFT JOIN
				v_Address as addr1 (nolock) ON v_PersonState.UAddress_id=addr1.Address_id
			LEFT JOIN
				v_KLAreaStat as astat1 (nolock) ON (
				((addr1.KLCountry_id = astat1.KLCountry_id) or (astat1.KLCountry_id is null)) and
				((addr1.KLRGN_id = astat1.KLRGN_id) or (astat1.KLRGN_id is null)) and
				((addr1.KLSubRGN_id = astat1.KLSubRGN_id) or (astat1.KLSubRGN_id is null)) and
				((addr1.KLCity_id = astat1.KLCity_id) or (astat1.KLCity_id is null)) and
				((addr1.KLTown_id = astat1.KLTown_id) or (astat1.KLTown_id is null))
				) 
			LEFT JOIN
				v_Address as addr2 (nolock) ON org1.UAddress_id=addr2.Address_id 
			LEFT JOIN
				v_KLAreaStat as astat2 (nolock) ON (
				((addr2.KLCountry_id = astat2.KLCountry_id) or (astat2.KLCountry_id is null)) and
				((addr2.KLRGN_id = astat2.KLRGN_id) or (astat2.KLRGN_id is null)) and
				((addr2.KLSubRGN_id = astat2.KLSubRGN_id) or (astat2.KLSubRGN_id is null)) and
				((addr2.KLCity_id = astat2.KLCity_id) or (astat2.KLCity_id is null)) and
				((addr2.KLTown_id = astat2.KLTown_id) or (astat2.KLTown_id is null))
				) 
			LEFT JOIN
				v_PersonCard (nolock) ON v_PersonCard.Person_id = v_PersonState.Person_id
			LEFT JOIN
				v_LpuRegion (nolock) ON v_LpuRegion.LpuRegion_id = v_PersonCard.LpuRegion_id
			WHERE
				v_PersonDispOrp.pmUser_updID = :pmUser_id and
				v_PersonDispOrp.Lpu_id = :Lpu_id and
				PersonDispOrp_updDT >= :BegDate";

		$res=$this->db->query(
			$sql,
			array(
				'pmUser_id' => $data['pmUser_id'],
				'Lpu_id' => $data['Lpu_id'],
				'BegDate' => ($data['beg_date']." ".$data['beg_time'])
			)
		);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Возвращает список людей в регистре ДД по заданным фильтрам
	*/
	function getPersonDispOrpList($data)
	{
   		$this->load->helper('Text');
		$this->load->helper('Main');
		$this->load->helper('Date');

        $join = '';
		$filters=array();
		$filters[]="v_PersonDispOrp.Lpu_id = {$data['session']['lpu_id']} and v_PersonDispOrp.PersonDispOrp_Year = ".ArrayVal($data, 'PersonDispOrp_Year', 'null');
		// 1. Основной фильтр
		if (ArrayVal($data,'Person_SurName')!='')
			$filters[] = "v_PersonState.Person_SurName like '{$data['Person_SurName']}%'";
		if (ArrayVal($data,'Person_FirName')!='')
			$filters[] = "v_PersonState.Person_FirName like '{$data['Person_FirName']}%'";
		if (ArrayVal($data,'Person_SecName')!='')
			$filters[] = "v_PersonState.Person_SecName like '{$data['Person_SecName']}%'";
		if (ArrayVal($data,'Person_BirthDay')!='') {
			$ar=ExplodeTwinDate($data['Person_BirthDay']);
			$filters[] = "v_PersonState.Person_BirthDay between '{$ar[0]}' and '{$ar[1]}'";
		}
		if (ArrayVal($data, 'PersonAge_From') != '' && ArrayVal($data, 'PersonAge_To') != '' )
			$filters[] = "((datediff(year,v_PersonState.Person_BirthDay,dbo.tzGetDate())
			+ case when month(v_PersonState.Person_BirthDay)>month(dbo.tzGetDate())
			or (month(v_PersonState.Person_BirthDay)=month(dbo.tzGetDate()) and day(v_PersonState.Person_BirthDay)>day(dbo.tzGetDate()))
			then -1 else 0 end) between '{$data['PersonAge_From']}' and '{$data['PersonAge_To']}')";
		if (ArrayVal($data,'Person_Snils')!='')
			$filters[] = "v_PersonState.Person_Snils = '{$data['Person_Snils']}'";
		if (ArrayVal($data,'PersonCard_begDate')!='') {
			$ar=ExplodeTwinDate($data['PersonCard_begDate']);
			$filters[] = "v_PersonCard.PersonCard_begDate between '{$ar[0]}' and '{$ar[1]}'";
		}
		if (ArrayVal($data,'PersonCard_begDate')!= '') {
			$ar=ExplodeTwinDate($data['PersonCard_endDate']);
			$filters[] = "v_PersonCard.PersonCard_endDate between '{$ar[0]}' and '{$ar[1]}'";
		}
		if (ArrayVal($data,'LpuRegionType_id')!= '')
			$filters[] = "v_PersonCard.LpuRegionType_id = {$data['LpuRegionType_id']}";
		if (ArrayVal($data,'LpuRegion_id')!= '')
			$filters[] = "v_PersonCard.LpuRegion_id = {$data['LpuRegion_id']}";
		if (ArrayVal($data,'PersonCard_Code')!= '')
			$filters[] = "v_PersonCard.PersonCard_Code = '{$data['PersonCard_Code']}'";
		if (ArrayVal($data,'LpuUnit_id')!= '')
			$filters[] = "(v_PersonCard.LpuUnit_id = {$data['LpuUnit_id']} or v_LpuRegion.LpuUnit_id = {$data['LpuUnit_id']})";

		// Вкладка Пациент
		if (ArrayVal($data,'Sex_id')!= '')
			$filters[] = "v_PersonState.Sex_id = {$data['Sex_id']}";
		if (ArrayVal($data,'SocStatus_id')!= '')
			$filters[] = "v_PersonState.SocStatus_id = {$data['SocStatus_id']}";
		if (ArrayVal($data,'DocumentType_id')!= '' || ArrayVal($data,'OrgDep_id')!= '' )
		{
			$join .= "INNER JOIN v_Document (nolock) ON v_PersonState.Document_id=v_Document.Document_id ";
			if (ArrayVal($data,'DocumentType_id')!= '')
				$join .= "and v_Document.DocumentType_id = {$data['DocumentType_id']} ";
			if (ArrayVal($data,'OrgDep_id')!= '')
				$join .= "and v_Document.OrgDep_id = {$data['OrgDep_id']} ";
		}
		if (ArrayVal($data,'OrgSmo_id')!= '' || ArrayVal($data,'PolisType_id')!= '' || ArrayVal($data,'OmsSprTerr_id')!= '' )
		{
			$join .= "INNER JOIN v_Polis (nolock) ON v_PersonState.Polis_id=v_Polis.Polis_id ";
			if (ArrayVal($data,'OrgSmo_id')!= '')
				$join .= "and v_Polis.OrgSmo_id = {$data['OrgSmo_id']} ";
			if (ArrayVal($data,'PolisType_id')!= '')
				$join .= "and v_Polis.PolisType_id = {$data['PolisType_id']} ";
			if (ArrayVal($data,'OmsSprTerr_id')!= '')
				$join .= "and v_Polis.OmsSprTerr_id = {$data['OmsSprTerr_id']} ";
		}
		if (ArrayVal($data,'Post_id')!= '' )
		{
			$join .= "INNER JOIN v_Job (nolock) ON v_PersonState.Job_id=v_Job.Job_id and v_Job.Post_id = {$data['Post_id']} ";
		}

		if (ArrayVal($data,'PrivilegeType_id')!= '' )
		{
			$join .= "INNER JOIN v_PersonPrivilege (nolock) ON v_PersonState.Person_id=v_PersonPrivilege.Person_id and v_PersonPrivilege.Lpu_id = {$data['session']['lpu_id']} and v_PersonPrivilege.PrivilegeType_id = {$data['PrivilegeType_id']} ";
		}

		if (ArrayVal($data,'Org_id')!= '')
			$filters[] = "v_PersonState.Org_id = {$data['Org_id']}";

		if (ArrayVal($data,'PersonRefuse_IsRefuse')!= '')
		{
			$filters[] = "PR.PersonRefuse_IsRefuse = {$data['PersonRefuse_IsRefuse']}";
		}

		if (ArrayVal($data,'IsRefuseNextYear')!= '')
		{
			$year = 1 + date('Y');
			$filters[] = "PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = {$year}";
		} else {
			$filters[] = "PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = YEAR(dbo.tzGetDate())";
		}

		// Вкладка Адрес
		if (ArrayVal($data,'KLCountry_id')!= '' || ArrayVal($data,'KLRgn_id')!= '' || ArrayVal($data,'KLSubRgn_id')!= '' ||ArrayVal($data,'KLCity_id')!= '' || ArrayVal($data,'KLTown_id')!= '' || ArrayVal($data,'KLStreet_id')!= '' || ArrayVal($data,'Address_House')!= '' || ArrayVal($data,'KLAreaType_id')!= '' )
		{
			$join .= "INNER JOIN v_Address (nolock) ON v_PersonState.UAddress_id=v_Address.Address_id ";
			if ( ArrayVal($data,'KLCountry_id')!= '' )
				$join .= "and v_Address.KLCountry_id={$data['KLCountry_id']} ";
			if ( ArrayVal($data,'KLRgn_id')!= '' )
				$join .= "and v_Address.KLRgn_id={$data['KLRgn_id']} ";
			if ( ArrayVal($data,'KLSubRgn_id')!= '' )
				$join .= "and v_Address.KLSubRgn_id={$data['KLSubRgn_id']} ";
			if ( ArrayVal($data,'KLCity_id')!= '' )
				$join .= "and v_Address.KLCity_id={$data['KLCity_id']} ";
			if ( ArrayVal($data,'KLTown_id')!= '' )
				$join .= "and v_Address.KLTown_id={$data['KLTown_id']} ";
			if ( ArrayVal($data,'KLStreet_id')!= '' )
				$join .= "and v_Address.KLStreet_id={$data['KLStreet_id']} ";
			if ( ArrayVal($data,'Address_House')!= '' )
				$join .= "and v_Address.Address_House='{$data['Address_House']}' ";
			if ( ArrayVal($data,'KLAreaType_id')!= '' )
				$join .= "and v_Address.KLAreaType_id={$data['KLAreaType_id']} ";
		}

		$sql = "SELECT DISTINCT TOP 101
			v_PersonDispOrp.PersonDispOrp_id,
			v_PersonState.Person_id,
			v_PersonState.Server_id,
			rtrim(v_PersonState.Person_SurName) as Person_SurName,
			rtrim(v_PersonState.Person_FirName)as Person_FirName,
			rtrim(v_PersonState.Person_SecName) as Person_SecName,
			Sex.Sex_Name,
			v_PersonState.Polis_Ser,
			v_PersonState.Polis_Num,
			okved1.Okved_Name as PersonOrg_Okved,
			org1.Org_OGRN as PersonOrg_OGRN,
			astat1.KLArea_Name as Person_KLAreaStat_Name,
			astat2.KLArea_Name as PersonOrg_KLAreaStat_Name,
			rtrim(addr1.Address_Address) as UAddress_Address,
			convert(varchar,cast(v_PersonState.Person_BirthDay as datetime),104) as Person_BirthDay
			FROM
			v_PersonDispOrp (nolock)
			INNER JOIN
			v_PersonState (nolock) on v_PersonDispOrp.Person_id=v_PersonState.Person_id LEFT JOIN
			v_PersonRefuse PR (nolock) on PR.Person_id = v_PersonDispOrp.Person_id LEFT JOIN
			Sex (nolock) on v_PersonState.Sex_id = Sex.Sex_id LEFT JOIN
			v_Job as job1 (nolock) ON v_PersonState.Job_id=job1.Job_id LEFT JOIN
			v_Org as org1 (nolock) ON job1.Org_id=org1.Org_id LEFT JOIN
			v_Okved as okved1 (nolock) ON okved1.Okved_id=org1.Okved_id LEFT JOIN
			v_Address as addr1 (nolock) ON v_PersonState.UAddress_id=addr1.Address_id LEFT JOIN
			v_KLAreaStat as astat1 (nolock) ON (
			((addr1.KLCountry_id = astat1.KLCountry_id) or (astat1.KLCountry_id is null)) and
			((addr1.KLRGN_id = astat1.KLRGN_id) or (astat1.KLRGN_id is null)) and
			((addr1.KLSubRGN_id = astat1.KLSubRGN_id) or (astat1.KLSubRGN_id is null)) and
			((addr1.KLCity_id = astat1.KLCity_id) or (astat1.KLCity_id is null)) and
			((addr1.KLTown_id = astat1.KLTown_id) or (astat1.KLTown_id is null))
			) LEFT JOIN
			v_Address as addr2 (nolock) ON org1.UAddress_id=addr2.Address_id LEFT JOIN
			v_KLAreaStat as astat2 ON (
			((addr2.KLCountry_id = astat2.KLCountry_id) or (astat2.KLCountry_id is null)) and
			((addr2.KLRGN_id = astat2.KLRGN_id) or (astat2.KLRGN_id is null)) and
			((addr2.KLSubRGN_id = astat2.KLSubRGN_id) or (astat2.KLSubRGN_id is null)) and
			((addr2.KLCity_id = astat2.KLCity_id) or (astat2.KLCity_id is null)) and
			((addr2.KLTown_id = astat2.KLTown_id) or (astat2.KLTown_id is null))
			) LEFT JOIN
			v_PersonCard (nolock) ON v_PersonCard.Person_id = v_PersonState.Person_id LEFT JOIN
			v_LpuRegion (nolock) ON v_LpuRegion.LpuRegion_id = v_PersonCard.LpuRegion_id {$join} ";
		$sql .= ImplodeWhere($filters);
		$res=$this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение комбо годов
	 */
	function getPersonDispOrpYearsCombo($data) {
		$filterList = array();
		$joinList = array();
        $years = array();

		$maxYear = intval(date('Y')) + (date('m') >= 10 ? 1 : 0);

		$minYear = 2013;
		if (getRegionNick() == 'penza') {
			$minYear = 2015;
		}

		for ( $i = $minYear; $i <= $maxYear; $i++ ) {
			$years[$i-$minYear]['PersonDispOrp_Year'] = $i;
			$years[$i-$minYear]['count'] = 0;
		}

		if ( $data['CategoryChildType_SysNick'] == 'orp' ) {
			$filterList[] = 'DO.CategoryChildType_id in (1, 2, 3, 4)';
		}
		else if ( $data['CategoryChildType_SysNick'] == 'orpadopted' ) {
			$filterList[] = 'DO.CategoryChildType_id in (5, 6, 7)';
		}
		else if ( $data['CategoryChildType_SysNick'] == 'orpperiod' ) {
			$filterList[] = 'DO.CategoryChildType_id in (8)';
		}
		else if ( $data['CategoryChildType_SysNick'] == 'orppred' ) {
			$filterList[] = 'DO.CategoryChildType_id in (9)';
		}
		else if ( $data['CategoryChildType_SysNick'] == 'orpprof' ) {
			$filterList[] = 'DO.CategoryChildType_id in (10)';
		}
		else {
			$filterList[] = '(1 = 0)';
		}

		$query = "
			select
				count(PersonDispOrp_id) as count,
				PersonDispOrp_Year
			from
				v_PersonDispOrp DO with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = DO.Person_id
				" . (count($joinList) > 0 ? implode(' ', $joinList) : "") . "
			where
				DO.PersonDispOrp_Year >= 2013
				and DO.Lpu_id = :Lpu_id
				" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
			GROUP BY
				PersonDispOrp_Year
		";
		//echo getDebugSQL($query, $data); die;
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
            $result = $res->result('array');

            for ($j=0; $j < count($result); $j++) {
                for ($i=0; $i < count($years); $i++) {
                    if ($years[$i]['PersonDispOrp_Year'] == $result[$j]['PersonDispOrp_Year']) {
                        $years[$i]['count'] = $result[$j]['count'];
                    }
                }
            }

            return $years;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных о человеке
	 */
	function getPersonData($data)
	{
  		$sql = "
			SELECT
				pstate.Person_SurName,
				pstate.Person_FirName,
				pstate.Person_BirthDay,
				pstate.SocStatus_id,
				pstate.Sex_id,
				pstate.UAddress_id,
				dbo.Age2(Person_BirthDay, cast(:PersonDispOrp_Year as varchar) + '-12-31') as Person_Age, -- на конец года
				polis.Polis_Ser,
				polis.Polis_Num,
				polis.OrgSmo_id,
				dbo.CheckINN(og.Org_INN) as Check_INN,
				og.Org_id,
				og.Org_INN,
				dbo.CheckOGRN(og.Org_OGRN) as Check_OGRN,
				og.Org_OGRN,
				og.Okved_id,
				og.UAddress_id as OrgUAddress_id
			FROM v_PersonState pstate (nolock)
			LEFT JOIN
				Polis as polis (nolock) on polis.Polis_id = pstate.Polis_id
			LEFT JOIN
				Job (nolock) on Job.Job_id=pstate.Job_id
			LEFT JOIN
				v_Org as og (nolock) on og.Org_id = Job.Org_id
			WHERE
				pstate.Person_id = :Person_id
		";
   		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;

	}
	
	/**
	 * Получение года в старом регистре
	 */
	function getYearInOldRegistry($data)
	{
  		$sql = "
			SELECT
				PersonDispOrp_Year
			FROM PersonDispOrpRegOld (nolock)
			WHERE
				Person_id = ? and not (PersonDispOrp_Year = 2006 and HealthGroup_id in (1,2))
		";
   		$res = $this->db->query($sql, array($data['Person_id']));
		if ( is_object($res) )
		{
 	    	$sel = $res->result('array');
			if ( count($sel) == 0 )
				$sel[0]['PersonDispOrp_Year'] = 0;
			return $sel;
		}
 	    else
 	    	return false;
	}

	/**
	 * Сохранение
	 */
	function savePersonDispOrp($data)
	{
		$proc = "p_PersonDispOrp_ins";
		
		$filter = "";
		
		if (empty($data['PersonDispOrp_id'])) {
			if ($data['CategoryChildType_id'] != 10) {
				if (!empty($data['CategoryChildType_id']) && in_array($data['CategoryChildType_id'], array(8))) {
					$filter .= " and CategoryChildType_id = :CategoryChildType_id";
				}
				if (!empty($data['CategoryChildType_id']) && in_array($data['CategoryChildType_id'], array(9))) {
					$filter .= " and CategoryChildType_id = :CategoryChildType_id";
				}
				if (!empty($data['CategoryChildType_id']) && in_array($data['CategoryChildType_id'], array(1,2,3,4,5,6,7))) {
					$filter .= " and CategoryChildType_id IN (1,2,3,4,5,6,7)";
				}
				// проверка на присутствие человека в регистре
				$sql = "
					select
						count(Person_id) as count
					from
						v_PersonDispOrp with(nolock)
					where
						Person_id = :Person_id and
						Lpu_id = :Lpu_id and
						PersonDispOrp_Year = :PersonDispOrp_Year
						{$filter}
				";
				$res = $this->db->query($sql, $data);
				if ( is_object($res) )
				{
					$sel = $res->result('array');
					if ($sel[0]['count'] > 0)
					{
						$sel[0]['Error_Code'] = 666;
						$sel[0]['Error_Msg'] = 'Данный пациент уже включён в регистр в вашем ЛПУ';
						return $sel;
					}
				}
				else
				{
					$sel[0]['Error_Code'] = 1;
					$sel[0]['Error_Msg'] = 'Не удалось проверить наличие человека в регистре';
					return $sel;
				}
				
				// проверка на присутствие человека в регистре другого ЛПУ
				if ( !isset($data['cancel_check_other_lpu']) )
				{
					$sql = "
						select
							count(PDO.Person_id) as count,
							LP.Lpu_Name
						from
							v_PersonDispOrp PDO (nolock)
							inner join v_Lpu LP (nolock) on PDO.Lpu_id = LP.Lpu_id
						where
							PDO.Person_id = :Person_id and
							PDO.Lpu_id <> :Lpu_id and
							PDO.PersonDispOrp_Year = :PersonDispOrp_Year
							{$filter}
						group by LP.Lpu_Name
					";
					$res = $this->db->query($sql, $data);
					if ( is_object($res) )
					{
						$sel = $res->result('array');
						if (is_array($sel) && count($sel) > 0 && !empty($sel[0]['count']))
						{
							$otherLPU = $sel[0]['Lpu_Name'];
							$sel[0]['Error_Code'] = '666';
							$sel[0]['Error_Msg'] = 'Данный пациент добавлен в регистр ЛПУ ' . $otherLPU . '.';
							// сначала так, потом сяк
							//$sel[0]['Error_Code'] = '668';
							//$sel[0]['Error_Msg'] = 'Данный пациент уже внесен в регистр по ДД в другом ЛПУ. Занести пациента в регистр?';
							return $sel;
						}
					}
					else
					{
						$sel[0]['Error_Code'] = 1;
						$sel[0]['Error_Msg'] = 'Не удалось проверить наличие человека в регистре';
						return $sel;
					}
				}
			}
		} else {
			$proc = "p_PersonDispOrp_upd";
		}

		$sql = "
			declare
				@PersonDispOrp_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @PersonDispOrp_id = :PersonDispOrp_id;
			exec {$proc}
				@PersonDispOrp_id = @PersonDispOrp_id output,
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@Lpu_id = :Lpu_id,
				@PersonDispOrp_Year = :PersonDispOrp_Year,
				@CategoryChildType_id = :CategoryChildType_id,
				@EducationInstitutionType_id = :EducationInstitutionType_id,
				@PersonDispOrp_begDate = :PersonDispOrp_begDate,
				@AgeGroupDisp_id = :AgeGroupDisp_id,
				@Org_id = :Org_id,
				@PersonDispOrp_setDate = :PersonDispOrp_setDate,
				@DisposalCause_id = :DisposalCause_id,
				@PersonDispOrp_DisposDate = :PersonDispOrp_DisposDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @PersonDispOrp_id as PersonDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
   		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
	}

	/**
	 * Удаление
	 */
	function deletePersonDispOrp($data)
	{
		// проверяем наличие талона по этому человеку
		$sql = "
			select 
				count(*) as cnt
			from
				v_EvnPLDispDop epldd (nolock)
				inner join v_PersonDispOrp pdd (nolock) on pdd.PersonDispOrp_id = ? and pdd.Person_id = epldd.Person_id and year(epldd.EvnPLDispDop_setDate) = pdd.PersonDispOrp_Year and epldd.Lpu_id = pdd.Lpu_id
		";
		$res = $this->db->query($sql, array($data['PersonDispOrp_id']));
		$sel = $res->result('array');
		if ( $sel[0]['cnt'] > 0 )
		{
			$sel[0]['Error_Code'] = 1;
			$sel[0]['Error_Msg'] = 'На этого человека заведен талон ДД. Его нельзя удалить из регистра.';
			return $sel;
		}
		
		$sql = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
							
			exec p_PersonDispOrp_del 
				@PersonDispOrp_id = :PersonDispOrp_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        
		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
	}

	/**
	 * Получение даты поступления в стационарное учреждение
	 */
	function getPersonDispOrpLastYearData($data) {
  		$sql = "
			select top 1
				 convert(varchar(10), PersonDispOrp_setDate, 104) as PersonDispOrp_setDate
				,Org_id
			from v_PersonDispOrp (nolock)
			where
				Person_id = :Person_id
				and PersonDispOrp_Year = :PersonDispOrp_Year - 1
				and CategoryChildType_id in (" . ($data['CategoryChildType'] == 'orpadopted' ? '5,6,7' : '1,2,3,4') . ")
		";
   		$res = $this->db->query($sql, $data);

		if ( is_object($res) ) {
 	    	return $res->result('array');
		}
 	    else {
 	    	return false;
		}
	}
}