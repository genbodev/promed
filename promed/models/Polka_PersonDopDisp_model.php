<?php
/**
* Polka_PersonDopDisp_model - модель, для работы с таблицей PersonDopDisp
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      22.06.2009
*/

class Polka_PersonDopDisp_model extends CI_Model {
	/**
	 * Polka_PersonDopDisp_model constructor.
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function loadPersonDopDispListForDbf($data)
	{
		if ( isSuperadmin() ) {
			$sql = "
				select
					pdd.Person_id as PERSON_ID,
					rtrim(ps.Person_SurName) as SURNAME,
					rtrim(ps.Person_FirName) as FIRNAME,
					rtrim(isnull(ps.Person_SecName, '')) as SECNAME,
					sx.Sex_Code as SEX,
					rtrim(isnull(convert(varchar, cast(ps.Person_BirthDay as datetime),104),'')) as BIRTHDAY,
					rtrim(isnull(addr.Address_Address, '')) as ADDR,
					rtrim(isnull(Polis.Polis_Ser, '')) as POL_SER,
					rtrim(isnull(Polis.Polis_Num, '')) as POL_NUM,
					rtrim(isnull(ps.Person_Snils, '')) as SNILS,
					rtrim(isnull(okvd.Okved_Code, '')) as O_OKVED,
					rtrim(isnull(og.Org_OGRN, '')) as ORG_OGRN,
					OST.OMSSprTerr_Code as TERR_ID_A,
					isnull(OMSSprTerr3.OMSSprTerr_Code, OMSSprTerr3.OMSSprTerr_Code) as TERR_ID_B,
					rtrim(isnull(og1.Org_OGRN, '')) as LPU_OGRN,				
					osmo.OrgSMO_RegNomC as REGION_CD,
					smoorg.Org_OGRN as Q_OGRN
				from
					v_PersonDopDisp pdd with (nolock)
					inner join v_PersonState ps with (nolock) on ps.Person_id = pdd.Person_id
					outer apply (
						Select top 1 PersonState.Polis_id
						from v_EvnVizitDispDop EVDD  with (nolock)
						inner join v_Person_all PersonState with (NOLOCK) on PersonState.PersonEvn_id = EVDD.PersonEvn_id and PersonState.Server_id = EVDD.Server_id
						where ps.Person_id = EVDD.Person_id and DopDispSpec_id = 1 and pdd.PersonDopDisp_Year = YEAR(EVDD.EvnVizitDispDop_setDate) and pdd.Lpu_id = EVDD.Lpu_id
						order by EVDD.EvnVizitDispDop_setDate desc
					) as EvnVizitTera
					left join v_Polis Polis with (nolock) on Polis.Polis_id = IsNull(EvnVizitTera.Polis_id, ps.Polis_id)
					left join Sex sx with (nolock) on sx.Sex_id = ps.Sex_id
					left join Address addr with (nolock) on ps.PAddress_id = addr.Address_id
					left join Address uaddr with (nolock) on ps.UAddress_id = uaddr.Address_id
					left join Job jb with (nolock) on jb.Job_id = ps.Job_id
					left join Org og with (nolock) on og.Org_id = jb.Org_id
					left join Okved okvd with (nolock) on okvd.Okved_id = og.Okved_id
					left join Lpu lp with (nolock) on lp.Lpu_id = pdd.Lpu_id
					left join Org og1 with (nolock) on og1.Org_id = lp.Org_id
					left join OMSSprTerr with (nolock) on
					((addr.KLCountry_id = OMSSprTerr.KLCountry_id) or (OMSSprTerr.KLCountry_id is null)) and
					((addr.KLRGN_id = OMSSprTerr.KLRGN_id) or (OMSSprTerr.KLRGN_id is null)) and
					((addr.KLSubRGN_id = OMSSprTerr.KLSubRGN_id) or (OMSSprTerr.KLSubRGN_id is null)) and
					((addr.KLCity_id = OMSSprTerr.KLCity_id) or (OMSSprTerr.KLCity_id is null)) and
					((addr.KLTown_id = OMSSprTerr.KLTown_id) or (OMSSprTerr.KLTown_id is null))
					left join OMSSprTerr OMSSprTerr3 with (nolock) on
					((uaddr.KLCountry_id = OMSSprTerr3.KLCountry_id) or (OMSSprTerr3.KLCountry_id is null)) and
					((uaddr.KLRGN_id = OMSSprTerr3.KLRGN_id) or (OMSSprTerr3.KLRGN_id is null)) and
					((uaddr.KLSubRGN_id = OMSSprTerr3.KLSubRGN_id) or (OMSSprTerr3.KLSubRGN_id is null)) and
					((uaddr.KLCity_id = OMSSprTerr3.KLCity_id) or (OMSSprTerr3.KLCity_id is null)) and
					((uaddr.KLTown_id = OMSSprTerr3.KLTown_id) or (OMSSprTerr3.KLTown_id is null))
					left join Address org_addr with (nolock) on og.UAddress_id = org_addr.Address_id				
					left join OMSSprTerr OST with (nolock) on OST.OMSSprTerr_id = Polis.OMSSprTerr_id
					left join v_OrgSMO osmo with (nolock) on osmo.OrgSMO_id = Polis.OrgSMO_id
					left join Org smoorg with (nolock) on smoorg.Org_id = osmo.Org_id
				where
					pdd.PersonDopDisp_Year = :PersonDopDisp_Year
				order by 
					SURNAME,
					FIRNAME,
					SECNAME,
					BIRTHDAY
			";
		}
		else {
			$sql = "
				select
					pdd.Person_id as PERSON_ID,
					rtrim(ps.Person_SurName) as SURNAME,
					rtrim(ps.Person_FirName) as FIRNAME,
					rtrim(isnull(ps.Person_SecName, '')) as SECNAME,
					rtrim(isnull(convert(varchar, cast(ps.Person_BirthDay as datetime),104),'')) as BIRTHDAY,
					isnull(sx.Sex_Code, 0) as SEX,
					isnull(os.OrgSMO_Name, '') as SMO_NAME,
					rtrim(isnull(Polis.Polis_Ser, '')) as POLIS_S,
					rtrim(isnull(Polis.Polis_Num, '')) as POLIS_N,
					rtrim(isnull(ps.Person_Snils, '')) as SNILS,
					rtrim(isnull(og.Org_Name, '')) as O_NAME,
					rtrim(isnull(og.Org_INN, '')) as O_INN,
					rtrim(isnull(og.Org_OGRN, '')) as O_OGRN,
					rtrim(isnull(addr.Address_Address, '')) as O_ADDR,
					rtrim(isnull(okvd.Okved_Code, '')) as O_OKVED,
					case when epldd.EvnPLDispDop_id is not null then 1 else 0 end as TAL_DD
				from
					v_PersonDopDisp pdd with (nolock)
					inner join v_PersonState ps with (nolock) on ps.Person_id = pdd.Person_id
					outer apply (
						Select top 1 PersonState.Polis_id
						from v_EvnVizitDispDop EVDD with (nolock)
						inner join v_Person_all PersonState with (NOLOCK) on PersonState.PersonEvn_id = EVDD.PersonEvn_id and PersonState.Server_id = EVDD.Server_id
						where ps.Person_id = EVDD.Person_id and DopDispSpec_id = 1 and pdd.PersonDopDisp_Year = YEAR(EVDD.EvnVizitDispDop_setDate) and pdd.Lpu_id = EVDD.Lpu_id
						order by EVDD.EvnVizitDispDop_setDate desc
					) as EvnVizitTera
					left join v_Polis Polis on Polis.Polis_id = IsNull(EvnVizitTera.Polis_id, ps.Polis_id)
					left join Sex sx with (nolock) on sx.Sex_id = ps.Sex_id
					left join Job jb with (nolock) on jb.Job_id = ps.Job_id
					left join Org og with (nolock) on og.Org_id = jb.Org_id
					left join [Address] addr with (nolock) on addr.Address_id = og.UAddress_id
					left join Okved okvd with (nolock) on okvd.Okved_id = og.Okved_id
					left join v_OrgSMO os with (nolock) on os.OrgSMO_id = polis.OrgSMO_id
					outer apply (
						select 
							top 1 EvnPLDispDop_id
						from
							v_EvnPLDispDop with (nolock)
						where
							Person_id = ps.Person_id
							and Lpu_id = pdd.Lpu_id
							and year(EvnPLDispDop_setDate) = pdd.PersonDopDisp_Year
					) as epldd
				where
					pdd.PersonDopDisp_Year = :PersonDopDisp_Year
					and pdd.Lpu_id = :Lpu_id
				order by 
					SURNAME,
					FIRNAME,
					SECNAME,
					BIRTHDAY
			";
		}

		$res = $this->db->query($sql, array('PersonDopDisp_Year' => $data['PersonDopDisp_Year'], 'Lpu_id' => $data['Lpu_id']));

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function loadPersonDopDispLpuReportForDbf($data)
	{
		$sql = "
			select
				lp.Lpu_Name,
				count(PersonDopDisp_id) as cnt
			from
				v_PersonDopDisp pdd				
				inner join v_Lpu lp on lp.Lpu_id = pdd.Lpu_id				
			where
				PersonDopDisp_Year = ?
			group by lp.Lpu_Name
			order by Lpu_Name
		";
		$res=$this->db->query($sql, array($data['PersonDopDisp_Year']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Возвращает список людей в регистре ДД по заданным фильтрам для поточного ввода
	*/
	function getPersonDopDispStreamInputList($data)
	{
		$sql = "
			SELECT DISTINCT
				v_PersonDopDisp.PersonDopDisp_id,
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
			FROM v_PersonDopDisp
			INNER JOIN
				v_PersonState on v_PersonDopDisp.Person_id=v_PersonState.Person_id
			LEFT JOIN
				Sex on v_PersonState.Sex_id = Sex.Sex_id
			LEFT JOIN
				v_Job as job1 ON v_PersonState.Job_id=job1.Job_id
			LEFT JOIN
				v_Org as org1 ON job1.Org_id=org1.Org_id
			LEFT JOIN
				v_Okved as okved1 ON okved1.Okved_id=org1.Okved_id
			LEFT JOIN
				v_Address as addr1 ON v_PersonState.UAddress_id=addr1.Address_id
			LEFT JOIN
				v_KLAreaStat as astat1 ON (
				((addr1.KLCountry_id = astat1.KLCountry_id) or (astat1.KLCountry_id is null)) and
				((addr1.KLRGN_id = astat1.KLRGN_id) or (astat1.KLRGN_id is null)) and
				((addr1.KLSubRGN_id = astat1.KLSubRGN_id) or (astat1.KLSubRGN_id is null)) and
				((addr1.KLCity_id = astat1.KLCity_id) or (astat1.KLCity_id is null)) and
				((addr1.KLTown_id = astat1.KLTown_id) or (astat1.KLTown_id is null))
				) 
			LEFT JOIN
				v_Address as addr2 ON org1.UAddress_id=addr2.Address_id 
			LEFT JOIN
				v_KLAreaStat as astat2 ON (
				((addr2.KLCountry_id = astat2.KLCountry_id) or (astat2.KLCountry_id is null)) and
				((addr2.KLRGN_id = astat2.KLRGN_id) or (astat2.KLRGN_id is null)) and
				((addr2.KLSubRGN_id = astat2.KLSubRGN_id) or (astat2.KLSubRGN_id is null)) and
				((addr2.KLCity_id = astat2.KLCity_id) or (astat2.KLCity_id is null)) and
				((addr2.KLTown_id = astat2.KLTown_id) or (astat2.KLTown_id is null))
				) 
			LEFT JOIN
				v_PersonCard ON v_PersonCard.Person_id = v_PersonState.Person_id
			LEFT JOIN
				v_LpuRegion ON v_LpuRegion.LpuRegion_id = v_PersonCard.LpuRegion_id
			WHERE
				v_PersonDopDisp.pmUser_updID = :pmUser_id and
				v_PersonDopDisp.Lpu_id = :Lpu_id and
				PersonDopDisp_updDT >= :BegDate";

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
	function getPersonDopDispList($data)
	{
   		$this->load->helper('Text');
		$this->load->helper('Main');
		$this->load->helper('Date');

        $join = '';
		$filters=array();
		$filters[]="v_PersonDopDisp.Lpu_id = {$data['session']['lpu_id']} and v_PersonDopDisp.PersonDopDisp_Year = ".ArrayVal($data, 'PersonDopDisp_Year', 'null');
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
			$join .= "INNER JOIN v_Document ON v_PersonState.Document_id=v_Document.Document_id ";
			if (ArrayVal($data,'DocumentType_id')!= '')
				$join .= "and v_Document.DocumentType_id = {$data['DocumentType_id']} ";
			if (ArrayVal($data,'OrgDep_id')!= '')
				$join .= "and v_Document.OrgDep_id = {$data['OrgDep_id']} ";
		}
		if (ArrayVal($data,'OrgSmo_id')!= '' || ArrayVal($data,'PolisType_id')!= '' || ArrayVal($data,'OmsSprTerr_id')!= '' )
		{
			$join .= "INNER JOIN v_Polis ON v_PersonState.Polis_id=v_Polis.Polis_id ";
			if (ArrayVal($data,'OrgSmo_id')!= '')
				$join .= "and v_Polis.OrgSmo_id = {$data['OrgSmo_id']} ";
			if (ArrayVal($data,'PolisType_id')!= '')
				$join .= "and v_Polis.PolisType_id = {$data['PolisType_id']} ";
			if (ArrayVal($data,'OmsSprTerr_id')!= '')
				$join .= "and v_Polis.OmsSprTerr_id = {$data['OmsSprTerr_id']} ";
		}
		if (ArrayVal($data,'Post_id')!= '' )
		{
			$join .= "INNER JOIN v_Job ON v_PersonState.Job_id=v_Job.Job_id and v_Job.Post_id = {$data['Post_id']} ";
		}

		if (ArrayVal($data,'PrivilegeType_id')!= '' )
		{
			$join .= "INNER JOIN v_PersonPrivilege ON v_PersonState.Person_id=v_PersonPrivilege.Person_id and v_PersonPrivilege.Lpu_id = {$data['session']['lpu_id']} and v_PersonPrivilege.PrivilegeType_id = {$data['PrivilegeType_id']} ";
		}

		if (ArrayVal($data,'Org_id')!= '')
			$filters[] = "v_PersonState.Org_id = {$data['Org_id']}";

		if (ArrayVal($data,'PersonRefuse_IsRefuse')!= '')
		{
			$filters[] = "v_PersonState.PersonRefuse_IsRefuse = {$data['PersonRefuse_IsRefuse']}";
		}

		if (ArrayVal($data,'IsRefuseNextYear')!= '')
		{
			$year = 1 + date('Y');
			$filters[] = "v_PersonState.PersonRefuse_IsRefuse = 2 and v_PersonState.PersonRefuse_Year = {$year}";
		}

		// Вкладка Адрес
		if (ArrayVal($data,'KLCountry_id')!= '' || ArrayVal($data,'KLRgn_id')!= '' || ArrayVal($data,'KLSubRgn_id')!= '' ||ArrayVal($data,'KLCity_id')!= '' || ArrayVal($data,'KLTown_id')!= '' || ArrayVal($data,'KLStreet_id')!= '' || ArrayVal($data,'Address_House')!= '' || ArrayVal($data,'KLAreaType_id')!= '' )
		{
			$join .= "INNER JOIN v_Address ON v_PersonState.UAddress_id=v_Address.Address_id ";
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
			v_PersonDopDisp.PersonDopDisp_id,
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
			v_PersonDopDisp INNER JOIN
			v_PersonState on v_PersonDopDisp.Person_id=v_PersonState.Person_id LEFT JOIN
			Sex on v_PersonState.Sex_id = Sex.Sex_id LEFT JOIN
			v_Job as job1 ON v_PersonState.Job_id=job1.Job_id LEFT JOIN
			v_Org as org1 ON job1.Org_id=org1.Org_id LEFT JOIN
			v_Okved as okved1 ON okved1.Okved_id=org1.Okved_id LEFT JOIN
			v_Address as addr1 ON v_PersonState.UAddress_id=addr1.Address_id LEFT JOIN
			v_KLAreaStat as astat1 ON (
			((addr1.KLCountry_id = astat1.KLCountry_id) or (astat1.KLCountry_id is null)) and
			((addr1.KLRGN_id = astat1.KLRGN_id) or (astat1.KLRGN_id is null)) and
			((addr1.KLSubRGN_id = astat1.KLSubRGN_id) or (astat1.KLSubRGN_id is null)) and
			((addr1.KLCity_id = astat1.KLCity_id) or (astat1.KLCity_id is null)) and
			((addr1.KLTown_id = astat1.KLTown_id) or (astat1.KLTown_id is null))
			) LEFT JOIN
			v_Address as addr2 ON org1.UAddress_id=addr2.Address_id LEFT JOIN
			v_KLAreaStat as astat2 ON (
			((addr2.KLCountry_id = astat2.KLCountry_id) or (astat2.KLCountry_id is null)) and
			((addr2.KLRGN_id = astat2.KLRGN_id) or (astat2.KLRGN_id is null)) and
			((addr2.KLSubRGN_id = astat2.KLSubRGN_id) or (astat2.KLSubRGN_id is null)) and
			((addr2.KLCity_id = astat2.KLCity_id) or (astat2.KLCity_id is null)) and
			((addr2.KLTown_id = astat2.KLTown_id) or (astat2.KLTown_id is null))
			) LEFT JOIN
			v_PersonCard ON v_PersonCard.Person_id = v_PersonState.Person_id LEFT JOIN
			v_LpuRegion ON v_LpuRegion.LpuRegion_id = v_PersonCard.LpuRegion_id {$join} ";
		$sql .= ImplodeWhere($filters);
		$res=$this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function getPersonDopDispYearsCombo($data)
	{
  		$sql = "
			SELECT
				count(PersonDopDisp_id) as count,
				PersonDopDisp_Year
			FROM
				v_PersonDopDisp
			WHERE
				Lpu_id = {$data['Lpu_id']}
				and PersonDopDisp_Year < 2013
			GROUP BY
				PersonDopDisp_Year
			ORDER BY
				PersonDopDisp_Year
		";

		$res = $this->db->query($sql);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
	}

	/**
	 * @param $data
	 * @return bool|mixed
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
				polis.OmsSprTerr_id,
				ost.KLRgn_id,
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
			FROM v_PersonState pstate 
			LEFT JOIN
				Polis as polis on polis.Polis_id = pstate.Polis_id
			LEFT JOIN
				Job on Job.Job_id=pstate.Job_id
			LEFT JOIN
				v_Org as og on og.Org_id = Job.Org_id
			LEFT JOIN
				v_OmsSprTerr as ost on ost.OmsSprTerr_id = polis.OmsSprTerr_id
			WHERE
				pstate.Person_id = {$data['Person_id']}
		";
   		$res = $this->db->query($sql);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;

	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getYearInOldRegistry($data)
	{

		/* старая проверка, вариант 2010 года
		 * $sql = '
			SELECT
				PersonDopDisp_Year
			FROM PersonDopDispRegOld pddro 
			left join v_PersonDisp pd on pd.Person_id = pddro.Person_id and PersonDopDisp_Year = 2006 and Year(pd.PersonDisp_begDate) <=2007 
			WHERE
				pddro.Person_id = ? and not (PersonDopDisp_Year = 2006 and pd.PersonDisp_begDate between pddro.DKL and DATEADD("Month", 1,pddro.DKL))
				
				-- and IsNull(HealthGroup_id,0) in (1,2))
		';*/

		/*
		 * При формировании сводного Регистра необходимо учесть, что граждане, 
		 * в отношении которых проводилась дополнительная диспансеризация в 
		 * 2008, 2009, 2010 годах, 
		 * повторно дополнительной диспансеризации в 2011 году не подлежат.
		 * 
		 * Граждане, прошедшие дополнительную диспансеризацию в 2007 году,
		 * взятые под диспансерное наблюдение, не должны включаться в Регистр
		 * граждан, подлежащих дополнительной диспансеризации в 2011 году
		 * (независимо от группы здоровья).
		 * 
		 */
		
		/*
		 * Проверяем 2009 - 2011 год.
		 * Просто факт включения в регистр.
		 * Из актуального регистра.
		 */
		$sql = '
			select
				max(PersonDopDisp_Year) as DispDop_Year
			from
				v_PersonDopDisp pdd with (nolock)					
			where
				pdd.Person_id = ?
				and pdd.PersonDopDisp_Year in (2009, 2010, 2011)
		';
		$res = $this->db->query($sql, array($data['Person_id']));
		if ( is_object($res) )
		{
 	    	$sel = $res->result('array');
			if ( $sel[0]['DispDop_Year'] > 0 )
				return $sel[0]['DispDop_Year'];
		}
		else
			return false;

		/*
		 * Проверяем 2009 - 2011 года.
		 * Просто факт включения в регистр.
		 * Из старого регистра.
		 */
		$sql = '
			select
				max(pdd.PersonDopDisp_Year) as DispDop_Year
			from
				v_PersonState pst with (nolock)
				inner join PersonDopDispRegOld pdd with (nolock) on pdd.PersonDopDisp_Year in (2009, 2010, 2011)
					and (pdd.Person_id = pst.Person_id or (pdd.Person_SurName=pst.Person_SurName and pdd.Person_FirName=pst.Person_FirName and pdd.Person_SecName=pst.Person_SecName and pdd.Person_BirthDay=pst.Person_BirthDay))
			where
				pst.Person_id = ?
		';
		$res = $this->db->query($sql, array($data['Person_id']));
		if ( is_object($res) )
		{
 	    	$sel = $res->result('array');
			if ( $sel[0]['DispDop_Year'] > 0 )
				return $sel[0]['DispDop_Year'];
		}
		else
			return false;

		/*
		 * Проверяем 2006 - 2008 год.
		 * Факт включения в регистр и взятие под диспансерное наблюдение.
		 * Из старого регистра.
		 */

		$sql = '
			SELECT
				max(PersonDopDisp_Year) as DispDop_Year
			FROM
				v_PersonState pst with (nolock)
				inner join PersonDopDispRegOld pddro with (nolock) on pddro.PersonDopDisp_Year in (2006, 2007, 2008)
					and (pddro.Person_id = pst.Person_id or (pddro.Person_SurName=pst.Person_SurName and pddro.Person_FirName=pst.Person_FirName and pddro.Person_SecName=pst.Person_SecName and pddro.Person_BirthDay=pst.Person_BirthDay))
				inner join v_PersonDisp pd with (nolock) on pd.Person_id = pst.Person_id and pd.PersonDisp_begDate between DATEADD("Day", -31, pddro.DKL) and pddro.DKL
			WHERE
				pst.Person_id = ?
		';				
		
		$res = $this->db->query($sql, array($data['Person_id']));
		if ( is_object($res) )
		{
 	    	$sel = $res->result('array');
			if ( $sel[0]['DispDop_Year'] > 0 )
				return $sel[0]['DispDop_Year'];
		}
		else
			return false;

		return true;
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function addPersonDopDisp($data)
	{				
		// проверка на присутствие человека в регистре
		$sql = "
			select
				count(Person_id) as count
			from
				v_PersonDopDisp
			where
				Person_id = {$data['Person_id']} and
				Lpu_id = {$data['session']['lpu_id']} and
				PersonDopDisp_Year = '{$data['PersonDopDisp_Year']}'
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
		{
 	    	$sel = $res->result('array');
			if ($sel[0]['count'] > 0)
			{
				$sel[0]['Error_Code'] = 666;
				$sel[0]['Error_Msg'] = 'Данный пациент уже внесен в регистр по ДД в вашем ЛПУ';
				$sel[0]['cancelErrorHandle'] = 'true';
				return $sel;
			}
		}
		else
		{
			$sel[0]['Error_Code'] = 1;
			$sel[0]['Error_Msg'] = 'Не удалось проверить наличие человека в регистре';
			$sel[0]['cancelErrorHandle'] = 'true';
			return $sel;
		}
		
		// проверка на присутствие человека в регистре другого ЛПУ
		if ( !isset($data['cancel_check_other_lpu']) )
		{
			$sql = "
				select
					count(Person_id) as count
				from
					v_PersonDopDisp
				where
					Person_id = {$data['Person_id']} and
					Lpu_id <> {$data['session']['lpu_id']} and
					PersonDopDisp_Year = '{$data['PersonDopDisp_Year']}'
			";
			$res = $this->db->query($sql);
			if ( is_object($res) )
			{
				$sel = $res->result('array');
				if ($sel[0]['count'] > 0)
				{
					$sel[0]['Error_Code'] = '666';
					$sel[0]['Error_Msg'] = 'Данный пациент добавлен в регистр другого ЛПУ.';
					$sel[0]['cancelErrorHandle'] = 'true';
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
				$sel[0]['cancelErrorHandle'] = 'true';
				return $sel;
			}
		}

		$sql = "
			declare @ErrCode bigint
			declare @ErrMsg varchar(4000)

			exec p_PersonDopDisp_ins
				@Server_id = {$data['Server_id']},
				@Person_id = {$data['Person_id']},
				@Lpu_id = {$data['session']['lpu_id']},
				@PersonDopDisp_Year = {$data['PersonDopDisp_Year']},
				@pmUser_id = {$data['session']['pmuser_id']},
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
            select @ErrCode as Error_Code, @ErrMsg as Error_Msg
		";
   		$res = $this->db->query($sql);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
	}

	/**
	 * @param $data
	 * @return array|mixed
	 */
	function deletePersonDopDisp($data)
	{
		// проверяем наличие талона по этому человеку
		$sql = "
			select 
				count(*) as cnt
			from
				v_EvnPLDispDop epldd
				inner join v_PersonDopDisp pdd on pdd.PersonDopDisp_id = ? and pdd.Person_id = epldd.Person_id and year(epldd.EvnPLDispDop_setDate) = pdd.PersonDopDisp_Year and epldd.Lpu_id = pdd.Lpu_id
		";
		$res = $this->db->query($sql, array($data['PersonDopDisp_id']));
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
			exec p_PersonDopDisp_del 
				@PersonDopDisp_id = {$data['PersonDopDisp_id']},
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;";
        $result = $this->db->query($sql);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

}