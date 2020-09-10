<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Privilege - модель для работы со льготами людей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Common
* @access			public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author			Stas Bykov aka Savage (savage1981@gmail.com)
* @version			?
*/
class Privilege_model extends SwPgModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}


	/**
	* Получение данных для экспорта РРЛ
	*/
	function getDataForExportRrl($data) {

		$code_mo = 'Lpu_RegNomC as "CODE_MO",';
		$join = "";
		$filters = "and PT.ReceptFinance_id = 2";
		if ($this->getRegionNick() == 'ufa') {
			$code_mo = 'LPDLO.LpuPeriodDLO_Code as "CODE_MO",';
			$join = 'left join v_LpuPeriodDLO LPDLO on LPDLO.Lpu_id = L.Lpu_id and (dbo.tzGetDate() >= LPDLO.LpuPeriodDLO_begDate and (LPDLO.LpuPeriodDLO_endDate is null or LPDLO.LpuPeriodDLO_endDate >= dbo.tzGetDate()))';
			$filters .= " and PT.PrivilegeType_Code not in ('101', '102') ";
		}

		$query = "
			select
				cast(L.Lpu_Nick as varchar(30)) as \"SHORTNAME\",
				L.Lpu_OGRN as \"OGRN\",
				{$code_mo}
				L.Lpu_Name as \"LPU_NAME\",
				to_char(dbo.tzGetDate(), 'YYYY-MM-DD') as \"CREATE_DATE\"
			from v_Lpu L
			{$join}
			where L.Lpu_id = :Lpu_id
		  	limit 1
		";
		
		$params = array('Lpu_id' => $data['Lpu_id']);

		if (!empty($data['PersonPrivilege_begDate'])) {
			$params['PersonPrivilege_begDate'] = $data['PersonPrivilege_begDate'];
			$filters .= " AND PP.PersonPrivilege_begDate = CAST(:PersonPrivilege_begDate  as date) ";
		}
		
		if (!empty($data['PersonPrivilege_endDate'])) {
			$params['PersonPrivilege_endDate'] = $data['PersonPrivilege_endDate'];
			$filters .= " AND PP.PersonPrivilege_endDate = CAST(:PersonPrivilege_endDate  as date) ";
		}
		
		if (!empty($data['PersonPrivilege_begDateFrom'])) {
			$params['PersonPrivilege_begDateFrom'] = $data['PersonPrivilege_begDateFrom'];
			$filters .= " AND PP.PersonPrivilege_begDate >= CAST(:PersonPrivilege_begDateFrom  as date) ";
		}
		
		if (!empty($data['PersonPrivilege_endDateFrom'])) {
			$params['PersonPrivilege_endDateFrom'] = $data['PersonPrivilege_endDateFrom'];
			$filters .= " AND PP.PersonPrivilege_endDate >= CAST(:PersonPrivilege_endDateFrom  as date) ";
		}
		
		if (!empty($data['PersonPrivilege_begDateTo'])) {
			$params['PersonPrivilege_begDateTo'] = $data['PersonPrivilege_begDateTo'];
			$filters .= " AND PP.PersonPrivilege_begDate <= CAST(:PersonPrivilege_begDateTo  as date) ";
		}
		
		if (!empty($data['PersonPrivilege_endDateTo'])) {
			$params['PersonPrivilege_endDateTo'] = $data['PersonPrivilege_endDateTo'];
			$filters .= " AND PP.PersonPrivilege_endDate <= CAST(:PersonPrivilege_endDateTo  as date) ";
		}
		
		if (!empty($data['PersonPrivilege_onlyValid']) && $data['PersonPrivilege_onlyValid'] == 1) {
			$filters .= " AND (PP.PersonPrivilege_endDate IS NULL OR PP.PersonPrivilege_endDate >= dbo.tzGetDate())";
		}

		//echo getDebugSQL($query, array('Lpu_id' => $data['Lpu_id']));die;
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));
		
		$xmldata = array();
		
		if ( is_object($result) ) {
			$lpuinfo = $result->result('array');
			if (count($lpuinfo)>0){
				$xmldata = $lpuinfo[0];
			}
		}
		
		$xmldata['PersonPrivilegies'] = array();
		
		$query = "
			with PPData (
				PersonPrivilege_id,
				Person_id,
				PrivilegeType_id,
				PersonPrivilege_BegDate,
				PersonPrivilege_EndDate
			) as (
				select
					PP.PersonPrivilege_id,
					PP.Person_id,
					PP.PrivilegeType_id,
					PP.PersonPrivilege_BegDate,
					PP.PersonPrivilege_EndDate
				from v_PersonPrivilege PP
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
				where 
					PP.PrivilegeType_id = 2574
					and PP.Lpu_id = :Lpu_id
					" . $filters . "

				union all

				select
					PP.PersonPrivilege_id,
					PP.Person_id,
					PP.PrivilegeType_id,
					PP.PersonPrivilege_BegDate,
					PP.PersonPrivilege_EndDate
				from v_PersonPrivilege PP
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
					left join lateral (
						select Lpu_id
						from v_PersonCard
						where Person_id = PP.Person_id
							and LpuAttachType_id = 1
							and PersonCard_endDate is null
						limit 1
					) PC on true
				where PP.PrivilegeType_id != 2574
					and coalesce(PC.Lpu_id, PP.Lpu_id) = :Lpu_id
					" . $filters . "

				union all

				select
					PP.PersonPrivilege_id,
					PP.Person_id,
					PP.PrivilegeType_id,
					PP.PersonPrivilege_BegDate,
					PP.PersonPrivilege_EndDate
				from v_PersonPrivilege PP
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
					left join lateral (
						select Lpu_id
						from v_PersonCard
						where Person_id = PP.Person_id
							and LpuAttachType_id = 1
							and PersonCard_endDate is null
						limit 1
					) PC on true
					left join lateral (
						select Lpu_id
						from v_PersonCard_all
						where Person_id = PP.Person_id
							and LpuAttachType_id = 4
							and PersonCard_endDate is null
							and Lpu_id = :Lpu_id
						limit 1
					) SLUZH on true
				where PP.PrivilegeType_id != 2574
					and coalesce(PC.Lpu_id, PP.Lpu_id) != :Lpu_id
					" . $filters . "
			)

			select
				PP.Person_id as \"ID\",
				PS.Person_Snils as \"SNILS\",
				coalesce(P.Polis_Ser,'') || ' ' || coalesce(P.Polis_Num,'') as \"SN_POL\",
				PS.Person_SurName as \"LASTNAME\",
				PS.Person_FirName as \"NAME\",
				case when coalesce(PS.Person_SecName,'') = '' then 'НЕТ' else PS.Person_SecName end as \"PATRONYMIC\",
				LEFT(SX.Sex_Name, 1) as \"SEX\",
				to_char(PS.Person_Birthday, 'YYYY-MM-DD') as \"BDAY\",
				DCT.DocumentType_Name as \"DOC\",
				DC.Document_Ser as \"DOC_SER\",
				DC.Document_Num as \"DOC_NMB\",
				CASE
					WHEN street.KLStreet_id is not null and street.KLAdr_Ocatd is not null THEN street.KLAdr_Ocatd
					WHEN town.KLArea_id is not null and town.KLAdr_Ocatd is not null THEN town.KLAdr_Ocatd
					WHEN city.KLArea_id is not null and city.KLAdr_Ocatd is not null THEN city.KLAdr_Ocatd
					WHEN srgn.KLArea_id is not null and srgn.KLAdr_Ocatd is not null THEN srgn.KLAdr_Ocatd
					WHEN rgn.KLArea_id is not null and rgn.KLAdr_Ocatd is not null THEN rgn.KLAdr_Ocatd
					WHEN country.KLArea_id is not null and country.KLAdr_Ocatd is not null THEN country.KLAdr_Ocatd
					ELSE ''
				END as \"ADDR_REG\",
				CASE
					WHEN city.KLArea_Name is not null then city.KLArea_Name
					WHEN srgn.KLArea_Name is not null then srgn.KLArea_Name
					WHEN town.KLArea_Name is not null then town.KLArea_Name
					WHEN rgn.KLArea_Name is not null then rgn.KLArea_Name
				END as \"REGION\",
				addr.Address_Address as \"ADDR_TEXT\",
				coalesce(PRT.PrivilegeRegType_Code, PT.PrivilegeType_Code) as \"CODE\",
				to_char(PP.PersonPrivilege_BegDate, 'YYYY-MM-DD') as \"START\",
				to_char(PP.PersonPrivilege_EndDate, 'YYYY-MM-DD') as \"STOP\"
			from PPData PP
				inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
				inner join v_PersonState PS on PS.Person_id = PP.Person_id
				left join r2.PrivilegeRegType PRT on PRT.PrivilegeType_id = PP.PrivilegeType_id
				left join v_Sex SX on SX.Sex_id = PS.Sex_id
				left join v_Polis P on P.Polis_id = PS.Polis_id
				left join v_Document DC on PS.Document_id  = DC.Document_id
				left join v_DocumentType DCT on DCT.DocumentType_id = DC.DocumentType_id
				left join v_Address addr on addr.Address_id = PS.UAddress_id
				left join KLArea country on country.KLArea_id = addr.KLCountry_id
				left join KLArea rgn on rgn.KLArea_id = addr.KLRgn_id
				left join KLArea srgn on srgn.KLArea_id = addr.KLSubRgn_id
				left join KLArea city on city.KLArea_id = addr.KLCity_id
				left join KLArea town on town.KLArea_id = addr.KLSubRgn_id
				left join KLStreet street on street.KLStreet_id = addr.KLStreet_id
		";
		 //echo getDebugSql($query, $params); exit;
		$result = $this->db->query($query, $params);
		
		if ( is_object($result) ) {
			$rllinfo = $result->result('array');
			$xmldata['PersonPrivilegies'] = $rllinfo;
		}

		return $xmldata;
	}

	/**
	 * Проверка прав пользователя на редактирование льготы
	 */
	function CheckPrivilegeAccessRights($data) {
		// теперь в хелпере AccessRights_helper.php
		return checkPrivilegeTypeAccessRights($data['PrivilegeType_id']);
	}

	/**
	* Проверка есть ли у человека действующая льгота данного типа
	*/
	function CheckPersonPrivilege($data) {
		$query = "
			select
			    max(PersonPrivilege_id) as \"PersonPrivilege_id\",
				count(*) as \"Privilege_Count\"
			from v_PersonPrivilege
			where PrivilegeType_id = :PrivilegeType_id
				and Person_id = :Person_id
				and (PersonPrivilege_endDate is null or PersonPrivilege_endDate > CAST(:Privilege_begDate as date))
		";
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			'PrivilegeType_id' => $data['PrivilegeType_id'],
			'Privilege_begDate' => $data['Privilege_begDate']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	* Проверка есть ли у человека действующая льгота на текущую дату
	*/
	function checkPersonPrivilegeExists($data) {
		// если уже передано в БГ, не проверяем
		if (!empty($data['EvnDirection_id']) && $this->getRegionNick() == 'kz') {
			$query = "
				select
					count(*) as \"Privilege_Count\"
				from r101.EvnDirectionLink
				where 
					EvnDirection_id = :EvnDirection_id 
					and Referral_id is not null
			";
			$result = $this->queryResult($query, array(
				'EvnDirection_id' => $data['EvnDirection_id']
			));
			
			if ($result[0]['Privilege_Count'] > 0) {
				return $result;
			}
		}
		
		$query = "
			select
				count(*) as \"Privilege_Count\"
			from v_PersonPrivilege
			where 
				Person_id = :Person_id 
				and coalesce(PersonPrivilege_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
		";
		
		return $this->queryResult($query, array(
			'Person_id' => $data['Person_id']
		));
	}

	/**
	* Особая проверка есть ли у человека действующая льгота данного типа (для Уфы)
	*/
	function CheckPersonPrivilegeByMOGroup($data) {
	
		$lpuFilter = getAccessRightsLpuFilter("PPC.Lpu_id");
		$lpuFilter = !empty($lpuFilter) ? " and $lpuFilter" : '';
	
		$query = "
			select
				count(*) as \"Privilege_Count\",
				case when count(PP.PersonPrivilege_id) > 0 and count(PPC.PersonPrivilege_id) = 0 then 1 else 0 end as \"DenyAdd\"
			from v_PersonPrivilege PP
			left join lateral (
				select PPC.PersonPrivilege_id 
				from v_PersonPrivilege PPC
				where PPC.PersonPrivilege_id = PP.PersonPrivilege_id 
				{$lpuFilter}
			  	limit 1
			) as PPC on true
			where 
				PrivilegeType_id = :PrivilegeType_id
				and Person_id = :Person_id
				and (PersonPrivilege_endDate is null or PersonPrivilege_endDate > CAST(:Privilege_begDate as date))
		";
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			'PrivilegeType_id' => $data['PrivilegeType_id'],
			'Privilege_begDate' => $data['Privilege_begDate']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Проверка наличия у пациента федеральной льготы
	 */
	function CheckPersonHaveActiveFederalPrivilege($data) {
		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Privilege_begDate' => $data['Privilege_begDate']
		);
		$filter = '';
		if (!empty($data['PrivilegeTypeCodeList'])) {
			$codeList_arr = json_decode($data['PrivilegeTypeCodeList']);
			$key_arr = array();
			for ($index = 0; count($codeList_arr) > $index ; $index++) {
				$queryParams['PrivilegeType_Code'.$index] = $codeList_arr[$index];
				$key_arr[] = ':PrivilegeType_Code'.$index;

			}

			$PrivilegeType_Codes = implode(', ', $key_arr);
			$filter .= " and PT.PrivilegeType_Code in ({$PrivilegeType_Codes})";
		}

		$query = "
			SELECT
				count(*) as \"Privilege_Count\"
			FROM
				v_PersonPrivilege
			RIGHT JOIN
				dbo.PrivilegeType PT
				on v_PersonPrivilege.PrivilegeType_id = PT.PrivilegeType_id
			WHERE
				ReceptFinance_id = 1
				AND person_id = :Person_id
				{$filter}
				and (PersonPrivilege_endDate is null or PersonPrivilege_endDate > CAST(:Privilege_begDate as date))
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Проверка наличия у пациента региональной льготы
	 */
	function CheckPersonHaveActiveRegionalPrivilege($data) {
		$query = "
			SELECT
				count(*) as \"Privilege_Count\"
			FROM
				v_PersonPrivilege
			RIGHT JOIN
				dbo.PrivilegeType
				on v_PersonPrivilege.PrivilegeType_id = dbo.PrivilegeType.PrivilegeType_id
			WHERE
				ReceptFinance_id = 2
				AND person_id = :Person_id
				and (PersonPrivilege_endDate is null or PersonPrivilege_endDate > :Privilege_begDate)
		";
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			'Privilege_begDate' => $data['Privilege_begDate']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Контроль на наличие федеральной льготы или отказа от него
	 */
	function checkPrivilegeAcsFedPrivilege($data) {
		$result = array();

		try {
			//определяем наличие пересечений с федеральными льготами
			$query = "
				select
					count(pp.PersonPrivilege_id) as \"cnt\"
				from
					v_PersonPrivilege pp
					left join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
					left join v_WhsDocumentCostItemType wdcit ON wdcit.WhsDocumentCostItemType_id = pt.WhsDocumentCostItemType_id
				where
					pp.Person_id = :Person_id and
					pp.PersonPrivilege_id <> coalesce(:PersonPrivilege_id, 0) and
					(
						(
							pp.PersonPrivilege_begDate <= :Privilege_begDate and
							(pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= :Privilege_begDate)
						) or
						(
							pp.PersonPrivilege_begDate > :Privilege_begDate and
							(:Privilege_endDate is null or pp.PersonPrivilege_begDate <= :Privilege_endDate)
						)
					) and
					wdcit.WhsDocumentCostItemType_Nick = 'fl' -- Федеральный бюджет
			";
			$priv_cnt = $this->getFirstResultFromQuery($query, array(
				'Person_id' => $data['Person_id'],
				'PersonPrivilege_id' => $data['PersonPrivilege_id'],
				'Privilege_begDate' => $data['Privilege_begDate'],
				'Privilege_endDate' => $data['Privilege_endDate']
			));

			//определяем наличиепересечения с отказом от льгот
			$query = "
				select
					count(pr.PersonRefuse_id) as \"cnt\"
				from
					v_PersonRefuse pr
				where
					pr.Person_id = :Person_id and
					pr.PersonRefuse_IsRefuse = 2 and
					pr.PersonRefuse_Year >= extract(year from :Privilege_begDate) and
					(
						:Privilege_endDate is null or
						pr.PersonRefuse_Year  <= extract(year from :Privilege_endDate)
					)
			";
			$refuse_cnt = $this->getFirstResultFromQuery($query, array(
				'Person_id' => $data['Person_id'],
				'Privilege_begDate' => $data['Privilege_begDate'],
				'Privilege_endDate' => $data['Privilege_endDate']
			));

			if ($priv_cnt > 0 || $refuse_cnt > 0) { //если есть льгота или отказ, возвращаем ошибку
				throw new Exception("Добавление льготы невозможно. Льгота для обеспечения пациентов, больных ССЗ, может быть присвоена при отсутствии федеральной льготы");
			}
		} catch (Exception $e) {
			if (!empty($e->getMessage())) {
				$result['Error_Msg'] = $e->getMessage();
			}
		}

		$result['check_result'] = empty($result['Error_Msg']);
		$result['success'] = true;

		return $result;
	}

	/**
	 * Контроль на наличие федеральной льготы для Карелии
	 */
	function checkPrivilegeAcsFedPrivilegeKareliya($data) {
		$result = array();

		try {
			//определяем наличие пересечения с федеральными льготами
			$query = "
				select
					count(pp.PersonPrivilege_id) as \"cnt\"
				from
					v_PersonPrivilege pp
					left join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
					left join v_WhsDocumentCostItemType wdcit ON wdcit.WhsDocumentCostItemType_id = pt.WhsDocumentCostItemType_id
				where
					pp.Person_id = :Person_id and
					pp.PersonPrivilege_id <> coalesce(:PersonPrivilege_id, 0) and
					pt.PrivilegeType_Code between 1 and 150 and
					(
						(
							pp.PersonPrivilege_begDate <= :Privilege_begDate and
							(pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= :Privilege_begDate)
						) or
						(
							pp.PersonPrivilege_begDate > :Privilege_begDate and
							(:Privilege_endDate is null or pp.PersonPrivilege_begDate <= :Privilege_endDate)
						)
					) and
					wdcit.WhsDocumentCostItemType_Nick = 'fl'
			";
			$priv_cnt = $this->getFirstResultFromQuery($query, array(
				'Person_id' => $data['Person_id'],
				'PersonPrivilege_id' => $data['PersonPrivilege_id'],
				'Privilege_begDate' => $data['Privilege_begDate'],
				'Privilege_endDate' => $data['Privilege_endDate']
			));

			if ($priv_cnt > 0 ) {
				throw new Exception("У пациента уже есть федеральная льгота, и льгота по программе Сердечно-сосудистые заболевания не может быть присвоена");
			}
		} catch (Exception $e) {
			if (!empty($e->getMessage())) {
				$result['Error_Msg'] = $e->getMessage();
			}
		}

		$result['check_result'] = empty($result['Error_Msg']);
		$result['success'] = true;

		return $result;
	}

	/**
	 * Контроль на наличие ранее открытой льготы по программе
	 */
	function checkPrivilegeAcsDoublePrivilege($data) {
		$result = array();

		try {
			//определяем наличия пересечения со сроком действия существующей льготы по ССЗ
			$query = "
				select
					count(pp.PersonPrivilege_id) as \"cnt\"
				from
					v_PersonPrivilege pp
					left join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
					left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = pt.WhsDocumentCostItemType_id
				where
					pp.Person_id = :Person_id and
					pp.PersonPrivilege_id <> coalesce(:PersonPrivilege_id, 0) and
					(
						(
							pp.PersonPrivilege_begDate <= :Privilege_begDate and
							(pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= :Privilege_begDate)
						) or
						(
							pp.PersonPrivilege_begDate > :Privilege_begDate and
							(:Privilege_endDate is null or pp.PersonPrivilege_begDate <= :Privilege_endDate)
						)
					) and
					wdcit.WhsDocumentCostItemType_Nick = 'acs' -- ССЗ
			";
			$priv_cnt = $this->getFirstResultFromQuery($query, array(
				'Person_id' => $data['Person_id'],
				'PersonPrivilege_id' => $data['PersonPrivilege_id'],
				'Privilege_begDate' => $data['Privilege_begDate'],
				'Privilege_endDate' => $data['Privilege_endDate']
			));

			if ($priv_cnt > 0) {
				throw new Exception("Добавление льготы невозможно. Пациент уже включен в программу ЛЛО Сердечно-сосудистые заболевания. Если у пациента случилось новое острое состояние, и участие в программе нужно продлить, то закройте имеющуюся льготу по программе, затем откройте новую");
			}
		} catch (Exception $e) {
			if (!empty($e->getMessage())) {
				$result['Error_Msg'] = $e->getMessage();
			}
		}

		$result['check_result'] = empty($result['Error_Msg']);
		$result['success'] = true;

		return $result;
	}

	/**
	 * Контроль на наличие основного прикрепления к МО региона
	 */
	function checkPrivilegeAcsMainAttachment($data) {
		$this->load->model("Options_model", "opmodel");
		$region = $this->opmodel->getRegion();
		$result = array();

		try {
			//определяем наличие действующего прикрепления
			$query = "
				select
					count(pc.PersonCard_id) as \"cnt\"
				from
					v_PersonCard pc
					inner join v_Lpu l on l.Lpu_id = pc.Lpu_id
				where
					pc.Person_id = :Person_id and
					pc.LpuAttachType_id = 1 and -- основное
					(pc.PersonCard_begDate is null or pc.PersonCard_begDate <= :Privilege_begDate) and
					(pc.PersonCard_endDate is null or pc.PersonCard_endDate >= :Privilege_begDate)
			";
			$att_cnt = $this->getFirstResultFromQuery($query, array(
				'Person_id' => $data['Person_id'],
				'Privilege_begDate' => $data['Privilege_begDate']
			));

			if ($att_cnt < 1) {
				throw new Exception("Добавление льготы невозможно. Льгота для обеспечения пациентов, больных ССЗ, может быть присвоена лицам, имеющим прикрепление МО  региона {$region['name']}");
			}
		} catch (Exception $e) {
			if (!empty($e->getMessage())) {
				$result['Error_Msg'] = $e->getMessage();
			}
		}

		$result['check_result'] = empty($result['Error_Msg']);
		$result['success'] = true;

		return $result;
	}

	/**
	 * Контроль на наличие карты диспансерного наблюдения
	 */
	function checkPrivilegeAcsPersonDisp($data) {
		$result = array();

		try {
			//определяем наличие карты диспансерного наблюдения
			$query = "
				select
					count(pd.PersonDisp_id) as \"cnt\"
				from
					v_PersonDisp pd
					left join v_Diag d on d.Diag_id = pd.Diag_id
				where
					pd.Person_id = :Person_id and
					pd.Lpu_id = :Lpu_id and
					(pd.PersonDisp_begDate is null or pd.PersonDisp_begDate <= :Privilege_begDate) and
					(pd.PersonDisp_endDate is null or pd.PersonDisp_endDate >= :Privilege_begDate) and
					d.Diag_Code ilike 'I%' and
					d.Diag_Code <> 'I20.0'
			";
			$disp_cnt = $this->getFirstResultFromQuery($query, array(
				'Person_id' => $data['Person_id'],
				'Lpu_id' => $data['Lpu_id'],
				'Privilege_begDate' => $data['Privilege_begDate']
			));

			if ($disp_cnt < 1) {
				throw new Exception("Добавление льготы невозможно. Пациент не поставлен на диспансерное наблюдение по диагнозам программы. Создайте карту диспансерного наблюдения на пациента и повторите действия по включению пациента в льготный регистр Сердечно-сосудистые заболевания");
			}
		} catch (Exception $e) {
			if (!empty($e->getMessage())) {
				$result['Error_Msg'] = $e->getMessage();
			}
		}

		$result['check_result'] = empty($result['Error_Msg']);
		$result['success'] = true;

		return $result;
	}

	/**
	 *	Удаление льготы
	 */
	function deletePersonPrivilege($data) {
		$params = array(
			'PersonPrivilege_id' => $data['PersonPrivilege_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
			select
				PP.Server_id as \"Server_id\",
				RF.ReceptFinance_Code as \"ReceptFinance_Code\",
				EU.EvnUdost_Count as \"EvnUdost_Count\",
				ER.EvnRecept_Count as \"EvnRecept_Count\"
			from
				v_PersonPrivilege PP
				left join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
				left join v_ReceptFinance RF on RF.ReceptFinance_id = PT.ReceptFinance_id
				left join lateral (
					select count(*) as EvnUdost_Count
					from v_EvnUdost EU
					where EU.PrivilegeType_id = PT.PrivilegeType_id and EU.Person_id = PP.Person_id
					and EU.EvnUdost_setDate between PP.PersonPrivilege_begDate and coalesce(PP.PersonPrivilege_endDate, EU.EvnUdost_setDate)
					limit 1
				) EU on true
				left join lateral (
					select count(*) as EvnRecept_Count
					from v_EvnRecept ER
					where ER.PersonPrivilege_id = PP.PersonPrivilege_id
					limit 1
				) ER on true
			where
				PP.PersonPrivilege_id = :PersonPrivilege_id
			limit 1
		";
		//echo getDebugSQL($query, $params);exit;
		$PersonPrivilege = $this->getFirstRowFromQuery($query, $params);
		if (empty($PersonPrivilege)) {
			return $this->createError('Ошибка при получении данных льготы');
		}
		if ($PersonPrivilege['ReceptFinance_Code'] != 2 && $PersonPrivilege['Server_id'] == 3) {
			return $this->createError('','Удаление данных о льготе из регионального сегмента ФРЛ, полученного от ПФР, невозможно');
		}
		if ($PersonPrivilege['EvnUdost_Count'] > 0) {
			return $this->createError('','Удаление льготы не возможно, т.к. по льготе выдано льготное удостоверение.  До удаления данных о льготе, удалите льготное удостоверение');
		}
		if ($PersonPrivilege['EvnRecept_Count'] > 0) {
			return $this->createError('','Удалить льготную категорию у пациента невозможно, т.к. по этой льготе пациенту были выписаны льготные рецепты');
		}

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonPrivilege_del (
				PersonPrivilege_id := :PersonPrivilege_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->queryResult($query, $params);
		if (!is_array($result)) {
			return $this->createError('','Ошибка при выполнении запроса к базе данных (удаление льготы)');
		}

		return $result;
	}

	/**
	* Получение дерева льгот
	*/
	function getLgotTree($data) {
		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("PT.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$privilegeFilter = " and $privilegeFilter";
		}

		$query = "
			SELECT DISTINCT
				PT.PrivilegeType_id as \"PrivilegeType_id\",
				PT.PrivilegeType_Code as \"PrivilegeType_Code\",
				coalesce(PT.PrivilegeType_VCode, cast(PT.PrivilegeType_Code as varchar)) as \"PrivilegeType_VCode\",
				RTRIM(PT.PrivilegeType_Name) as \"PrivilegeType_Name\"
			FROM
				v_PrivilegeType PT
				inner join v_ReceptFinance RF on PT.ReceptFinance_id = RF.ReceptFinance_id
			WHERE
				RF.ReceptFinance_Code = 2
				AND ( PT.PrivilegeType_begDate is null OR PT.PrivilegeType_begDate <= GetDate() )
				AND ( PT.PrivilegeType_endDate is null OR PT.PrivilegeType_endDate > GetDate() )
				{$privilegeFilter}
			ORDER BY
				coalesce(PT.PrivilegeType_VCode, cast(PT.PrivilegeType_Code as varchar))
		";

		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if (is_object($result)) {
			$res_array = $result->result('array');
			$code_array = array();
			$need_sort = true;

			foreach($res_array as $res_data) { //проверяем не встречаются ли среди кодов строки, паралельно формируем массив кодов для сортировки
				if (!empty($res_data['PrivilegeType_VCode'])) {
					if ($res_data['PrivilegeType_VCode'].'' != ((int) $res_data['PrivilegeType_VCode']).'') {
						$need_sort = false;
						break;
					}
				}
				$code_array[] = $res_data['PrivilegeType_VCode'];
			}

			if ($need_sort) {
				array_multisort($code_array, SORT_NUMERIC, $res_array);
			}

			return $res_array;
		} else {
			return false;
		}
	}

	/**
	 *	Получение списка льготников по выбранной категории
	 */
	function getLgotList($data) {
		
		$filter = "";
		$attach_filter = "";

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("PT.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and $privilegeFilter";
		}

		if ( $data['PrivilegeStateType_id'] == 1 ) {
			$filter .= " and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= dbo.tzGetDate()::date)";
		}
		
		if ($this->getRegionNick() == 'ufa') {
			$lpuFilter = getAccessRightsLpuFilter('PP.Lpu_id');
			$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : '';
			$filter .= $lpuFilter;
		} 

		// Прикрепление к МО по основному или служебному признаку
		if ( !empty($data['Lpu_prid'])) {
			$filter .= "
			and exists (
				select *
				from v_PersonCard PC 
				left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id 
				left join v_LpuRegion LR_Fap on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid 
				WHERE PC.Person_id = PS.Person_id 
				{$attach_filter}
				and PC.Lpu_id = :Lpu_prid) 
			";

			switch ($data['PrivilegeSearchType_id']){
				case 1:
					$attach_filter .= " and PC.PersonCard_begDate <= dbo.tzGetDate() and (PC.PersonCard_endDate is null or PC.PersonCard_endDate >= dbo.tzGetDate())";
					break;
			}
		}

		$query = "
			SELECT
				PP.PersonPrivilege_id as \"PersonPrivilege_id\",
				PP.Lpu_id as \"Lpu_id\",
				PS.Lpu_id as \"Lpu_did\",
				case
					when PP.Server_id = 3 then 'ПФР'
					when PP.Server_id = 7 then 'Минздрав'
					else coalesce(LPU.Lpu_Nick,'') 
				end as \"Lpu_Nick\",
				PP.Server_id as \"Server_id\",
				PS.Person_id as \"Person_id\",
				PS.Person_Snils as \"Person_Snils\",
				PS.Polis_Ser as \"Polis_Ser\",
				PS.Polis_Num as \"Polis_Num\",
				uaddr.Address_Address as \"Person_UAddress\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PT.PrivilegeType_Code as \"PrivilegeType_Code\",
				coalesce(PT.PrivilegeType_VCode, cast(PT.PrivilegeType_Code as varchar)) as \"PrivilegeType_VCode\",
				PT.PrivilegeType_id as \"PrivilegeType_id\",
				RTRIM(PS.Person_Surname) as \"Person_Surname\",
				RTRIM(PS.Person_Firname) as \"Person_Firname\",
				RTRIM(PS.Person_Secname) as \"Person_Secname\",
				to_char(PS.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\",
				to_char(PP.PersonPrivilege_begDate, 'DD.MM.YYYY') as \"Privilege_begDate\",
				to_char(PP.PersonPrivilege_endDate, 'DD.MM.YYYY') as \"Privilege_endDate\",
				RF.ReceptFinance_id as \"ReceptFinance_id\",
				RF.ReceptFinance_Code as \"ReceptFinance_Code\",
				coalesce (PCT.PrivilegeCloseType_Name, '') as \"PrivilegeCloseType_Name\",
				coalesce (DP.DocumentPrivilege_Data, '') as \"DocumentPrivilege_Data\",
				coalesce (D.Diag_Code || ' ' || D.Diag_Name, '') as \"Diag_Name\"
			FROM
				v_PrivilegeType PT
				inner join v_PersonPrivilege PP on PT.PrivilegeType_id = PP.PrivilegeType_id
				inner join v_PersonState PS on PS.Person_id = PP.Person_id
				left join v_Lpu LPU on LPU.Lpu_id = PP.Lpu_id
				left join v_ReceptFinance RF on RF.ReceptFinance_id = PT.ReceptFinance_id
				left join v_PrivilegeCloseType PCT on PCT.PrivilegeCloseType_id = PP.PrivilegeCloseType_id
				left join v_Diag D on D.Diag_id = PP.Diag_id
				left join lateral (
					select
						uaddr.Address_Address
					from
						v_Address uaddr
					where
						uaddr.Address_id = PS.UAddress_id
					limit 1
				) uaddr on true
				left join lateral(
					select
						(
							i_DP.DocumentPrivilege_Ser ||' '||
							i_DP.DocumentPrivilege_Num ||' '||
							to_char( i_DP.DocumentPrivilege_begDate, 'dd.mm.yyyy')||' '||
							coalesce(i_O.Org_Nick, i_DP.DocumentPrivilege_Org, '')	
						) as DocumentPrivilege_Data
					from
					 	v_DocumentPrivilege i_DP
						left join v_Org i_O on i_O.Org_id = i_DP.Org_id
					where
						i_DP.PersonPrivilege_id = PP.PersonPrivilege_id
					order by
						i_DP.DocumentPrivilege_id
					limit 1
				) DP on true
			WHERE
				PT.PrivilegeType_id = :PrivilegeType_id
				and PT.ReceptFinance_id = 2
				{$filter}
			ORDER BY
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname
		";

		//echo getDebugSQL($query, $data); die;
		$result = $this->db->query(
			$query,
			array(
				'PrivilegeType_id' => $data['PrivilegeType_id'],
				'Lpu_prid' => $data['Lpu_prid'],
				'Lpu_id' => $data['Lpu_id']
			)
		);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка сохраняемой льготы
	 */
	function validatePrivilege($data) {
		$resp = $this->getFirstRowFromQuery("
			select
				PrivilegeType_Code as \"PrivilegeType_Code\",
				PrivilegeType_SysNick as \"PrivilegeType_SysNick\"
			from PrivilegeType 
			where PrivilegeType_id = :PrivilegeType_id
			limit 1
		", $data);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при определении кода льготы');
		}
		$PrivilegeType_Code = $resp['PrivilegeType_Code'];
		$PrivilegeType_SysNick = $resp['PrivilegeType_SysNick'];

		$query = "
			select
				PS.Person_Snils as \"Person_Snils\",
				PS.Person_BirthDay as \"Person_BirthDay\",
				PS.Person_BirthDay + interval '3 year' as \"ThirdBirthDay\",
				--PS.Person_BirthDay - interval '1 day' + interval '3 year' as \"ThirdBirthDayMinusDay\",
				PS.Person_BirthDay + interval '3 year' - interval '1 day' as \"ThirdBirthDayMinusDay\", -- переставил что бы не нарваться на високосный год
				PS.Person_BirthDay + interval '6 year' as \"SixthBirthDay\",
				PS.Person_BirthDay + interval '6 year' - interval '1 day' as \"SixthBirthDayMinusDay\",
				dbo.Age(PS.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
				PS.Person_deadDT as \"Person_deadDT\",
				Polis.Polis_id as \"Polis_id\",
				PC.PersonCard_id as \"PersonCard_id\",
				to_char(ER.maxEvnRecept_setDate, 'dd.mm.yyyy') as \"maxEvnRecept_setDate\"
			from
				v_PersonState PS
				left join lateral (
					select Polis.Polis_id
					from v_PersonPolis Polis
					where Polis.Person_id = PS.Person_id
					and Polis.Polis_begDate <= :PersonPrivilege_begDate
					and (Polis.Polis_endDate is null or Polis.Polis_endDate > CAST(:PersonPrivilege_begDate as date))
					limit 1
				) Polis on true
				left join lateral (
					select PC.PersonCard_id
					from v_PersonCard PC
					where PC.Person_id = PS.Person_id
					and PC.LpuAttachType_id = 1
					and PC.PersonCard_begDate <= :PersonPrivilege_begDate
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > CAST(:PersonPrivilege_begDate as date))
					limit 1
				) PC on true
				left join lateral (
					select max(ER.EvnRecept_setDate) as maxEvnRecept_setDate
					from v_EvnRecept ER
					where ER.PersonPrivilege_id = :PersonPrivilege_id
					limit 1
				) ER on true
			where PS.Person_id = :Person_id
			limit 1
		";
		$info = $this->getFirstRowFromQuery($query, $data);
		if (!is_array($info)) {
			return $this->createError('','Ошибка при получении данных человека');
		}

		if (getRegionNick() != 'kz') {
			if (!empty($data['PersonPrivilege_endDate']) && date_create($data['PersonPrivilege_begDate']) > date_create($data['PersonPrivilege_endDate'])) {
				return $this->createError('','Дата окончания льготы не может быть раньше даты начала');
			}

			if (date_create($data['PersonPrivilege_begDate']) < $info['Person_BirthDay']) {
				return $this->createError('','Дата начала льготы не может быть раньше даты рождения');
			}

			if (!havingGroup(array('SuperAdmin','ChiefLLO','minzdravdlo')) &&
				!in_array($PrivilegeType_SysNick, array('child_und_three_year','deti_6_mnogod','infarkt')) &&
				empty($info['Person_deadDT']) && !empty($data['PersonPrivilege_endDate']) &&
				date_create($data['PersonPrivilege_endDate']) < date_create( date('d.m.Y') )
			) {
				return $this->createError('','Дата закрытия льготы не может быть меньше текущей даты');
			}

			if (!empty($data['PersonPrivilege_endDate']) && !empty($info['maxEvnRecept_setDate']) &&
				date_create($data['PersonPrivilege_endDate']) < $info['maxEvnRecept_setDate']
			) {
				$this->_saveResponse['maxEvnRecept_setDate'] = DateTime::createFromFormat('d.m.Y', $info['maxEvnRecept_setDate'])->format('d.m.Y');
				$this->_setAlertMsg("
					Дата окончания льготы не может быть меньше " . DateTime::createFromFormat('d.m.Y', $info['maxEvnRecept_setDate'])->format('d.m.Y') . " - даты выписки последнего льготного рецепта по выбранной льготе.<br/>
					Установить эту дату в качестве даты окончания льготы?
				");
				return $this->createError(201, 'YesNo');
			}

			$query = "
				with vars as (
					select
						'2080-01-01'::date as bigDate,
						:PersonPrivilege_begDate::date as begdate,
						:PersonPrivilege_endDate::date as endDate,
						:PersonPrivilege_id::bigint as id
				)
				select
					count(*) as \"Privilege_Count\"
				from
					v_PersonPrivilege
				where
					PrivilegeType_id = :PrivilegeType_id
					and Person_id = :Person_id
					and PersonPrivilege_begDate < coalesce((select endDate from vars), (select bigDate from vars))
					and coalesce(PersonPrivilege_endDate, (select bigDate from vars)) > (select begDate from vars)
					and PersonPrivilege_id <> coalesce((select id from vars), 0)
			";
			$doubleCount = $this->getFirstResultFromQuery($query, $data);
			if ($doubleCount === false) {
				return $this->createError('','Ошибка при проверке наличия льготы у человека');
			}
			if ($doubleCount > 0) {
                if ($resp['PrivilegeType_SysNick'] != 'kardio') {
                    return $this->createError('priv_exists','Создать льготу невозможно, т.к. у пациента уже есть такая льгота');
			    }
			}

			if (
				$PrivilegeType_SysNick == 'child_und_three_year' && (
					empty($data['PersonPrivilege_endDate']) || (
						date_create($data['PersonPrivilege_endDate'])->format('Y-m-d') != date_create($info['ThirdBirthDay'])->format('Y-m-d') &&
						date_create($data['PersonPrivilege_endDate'])->format('Y-m-d') != date_create($info['ThirdBirthDayMinusDay'])->format('Y-m-d')
					)
				)
			) {
				$privName = getRegionNick() == 'perm' ? '253' : '«Дети первых 3 лет»';
				return $this->createError('',"Для добавления льготы {$privName} необходимо указать дату окончания.");
			}

			if (
				$PrivilegeType_SysNick == 'deti_6_mnogod' && (
					empty($data['PersonPrivilege_endDate']) || (
						date_create($data['PersonPrivilege_endDate'])->format('Y-m-d') != date_create($info['SixthBirthDay'])->format('Y-m-d') &&
						date_create($data['PersonPrivilege_endDate'])->format('Y-m-d') != date_create($info['SixthBirthDayMinusDay'])->format('Y-m-d')
					)
				)
			) {
				$privName = getRegionNick() == 'perm'? '258' : '«Дети из многодетных семей в возрасте до 6 лет»';
				return $this->createError('',"Для добавления льготы {$privName} необходимо указать дату окончания.");
			}

			if (in_array($PrivilegeType_SysNick, array('infarkt','infarkt_miok')) && empty($data['PersonPrivilege_endDate'])) {
				return $this->createError('',"Для льготной категории «Инфаркт миокарда (первые шесть месяцев)» должна быть указана дата окончания.");
			}

			if ($PrivilegeType_Code == 253 && getRegionNick() == 'perm') {
				$query = "
					select
						count(PP.PersonPrivilege_id) as cnt
					from
						v_PersonPrivilege PP
						inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
					where
						PP.Person_id = :Person_id
						and PP.PersonPrivilege_begDate <= CAST(:PersonPrivilege_begDate as date)
						and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate > CAST(:PersonPrivilege_begDate as date))
						and (
							(PT.ReceptFinance_id = 1 and isnumeric(PT.PrivilegeType_Code) = 1 and PT.PrivilegeType_Code between 1 and 150)
							or (PT.ReceptFinance_id = 2 and PT.PrivilegeType_Code = '{$PrivilegeType_Code}')
						)
					limit 1
				";
				$cnt = $this->getFirstResultFromQuery($query, $data);
				if ($cnt === false) {
					return $this->createError('','Ошибка при поиске действующих льгот');
				}

				$this->load->helper('Person');

				if (empty($data['PersonPrivilege_id']) || $data['PersonPrivilege_id'] < 0) {
					if ($info['Person_Age'] >= 3
						|| empty($info['Polis_id'])
						|| empty($info['PersonCard_id'])
						|| !checkPersonSnils($info['Person_Snils'])
						|| $cnt > 0
					) {
						return $this->createError('',"
							Создание льготы невозможно. Не выполняется одно из условий:<br/>
							•	Возраст пациента менее 3-х лет<br/>
							•	Отсутствие федеральных льгот с кодами от 1 до 150<br/>
							•	Отсутствие региональной льготы 253<br/>
							•	Имеется корректный СНИЛС<br/>
							•	Имеется действующий полис<br/>
							•	Имеется действующее основное прикрепление
						");
					}
				}
			}
		}

		return array(array('success' => true));
	}

	/**
	 *	Сохранение льготы у человека
	 */
	function savePrivilege($data) {
		$this->beginTransaction();

		$responseCheck = $this->CheckPrivilegeAccessRights($data);

		if ( $responseCheck == false ) {
			$this->rollbackTransaction();
			return array(0 => array('success' => false, 'Error_Msg' => 'Нет прав на редактирование выбранного типа льгот'));
		}
		
		if ($this->getRegionNick() == 'kz' && empty($data['PersonPrivilege_id'])) {
			$responseCheck = $this->CheckPersonPrivilege($data);
			if (is_array($responseCheck) && $responseCheck[0]['Privilege_Count'] > 0) {
				$this->rollbackTransaction();
				return array(0 => array('success' => false, 'Error_Msg' => 'Создать льготу невозможно, т.к. у пациента уже есть такая льгота'));
			}
		}

		if ( (!isSuperAdmin()) && (!isMinzdrav()) && (!in_array($data['session']['region']['nick'],array('khak','penza','saratov','ufa','kz','krym'))) ) {
			//если не минздрав, не хакасия, не Пенза, не саратов, не уфа и не суперадмин, проверим наличие активных федеральных льгот - CheckPersonHaveActiveFederalPrivilege
			// Хакасия исключена по задаче https://redmine.swan.perm.ru/issues/35158
			// Пенза исключена по задаче https://redmine.swan-it.ru/issues/183067
			// Крым исключена по задаче https://redmine.swan-it.ru/issues/183067
			$response = $this->CheckPersonHaveActiveFederalPrivilege($data);

			if ( (is_array($response)) && (count($response) > 0) ) {
				if ( $response[0]['Privilege_Count'] > 0 ) {
					$this->rollbackTransaction();
					return array(0 => array('success' => false, 'Error_Code' => 'priv_exists_fed', 'Error_Msg' => 'Человеку нельзя добавить льготу, так как есть действующая федеральная льгота'));
				}
			}
			else {
				$this->rollbackTransaction();
				return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при проверке наличия федеральных льгот у человека'));
			}
			$query = "
				select
					ReceptFinance_id as \"ReceptFinance_id\"
				from dbo.PrivilegeType
				where PrivilegeType_id = :PrivilegeType_id
				limit 1
			";
			$result = $this->db->query($query, array(
				'PrivilegeType_id' => $data['PrivilegeType_id']
			));

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (2)'));
			}

			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				$this->rollbackTransaction();
				return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (2.1)'));
			}
			else if ( $response[0]['ReceptFinance_id'] == 1 ) {
				$query = "
					UPDATE PersonPrivilege
					SET PersonPrivilege_endDate = (:PersonPrivilege_begDate::timestamp - interval '1 day')
					FROM v_PrivilegeType PT
					WHERE
						PT.PrivilegeType_id = PersonPrivilege.PrivilegeType_id
						and PersonPrivilege.Person_id = :Person_id
						and (PersonPrivilege.PersonPrivilege_endDate IS NULL or PersonPrivilege.PersonPrivilege_endDate > CAST(:PersonPrivilege_begDate as date))
						and PT.ReceptFinance_id = 2;
				";
				$this->db->query($query, array(
					'Person_id' => $data['Person_id'],
					'PersonPrivilege_begDate' => $data['Privilege_begDate']
				));
			}
		}
		
		if($this->getRegionNick() == 'msk' && !empty($data['ReceptFinance_id']) && $data['ReceptFinance_id'] == 1){
			$res = $this->CheckPersonHaveActiveRegionalPrivilege($data);
			if ( (is_array($res)) && (count($res) > 0) ) {
				if ( $res[0]['Privilege_Count'] > 0 ) {
					$resCloseRegionalPrivileges = $this->closeRegionalActivePrivilegesForPerson($data);
					if(!empty($resCloseRegionalPrivileges['Error_Msg'])){
						$this->rollbackTransaction();
						return array(0 => array('success' => false, 'Error_Msg' => $resCloseRegionalPrivileges['Error_Msg']));
					}
				}
			}
		}

		$data['PersonPrivilege_begDate'] = $data['Privilege_begDate'];
		$data['PersonPrivilege_endDate'] = $data['Privilege_endDate'];

		$resp = $this->validatePrivilege($data);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$response = $this->savePersonPrivilege($data);
		if ($this->isSuccessful($response)) {
			$this->commitTransaction();
		} else {
			$this->rollbackTransaction();
		}
		return $response;
	}

	/**
	 *	Сохранение льготы у человека
	 */
	function savePrivilegeConsent($data) {
		$result = array();

		$this->beginTransaction();
		try {
			$session_data = array_merge($data, getSessionParams());

			$data['pmUser_id'] = $this->getPromedUserId();
			$data['Server_id'] = $session_data['Server_id'];
			$data['Lpu_id'] = $session_data['Lpu_id'];
            !empty($data['PersonPrivilege_id']) ? $data['PersonPrivilege_id'] : null;

			if ($data['is_stac'] == 0 && empty($data['PersonPrivilege_id'])) { //проверка льготы на дублирование актуальная только в поликлинике и только при добавлении
				$responseCheck = $this->CheckPersonPrivilege($data);
				if (is_array($responseCheck) && $responseCheck[0]['Privilege_Count'] > 0) {
					throw new Exception('Пациент уже включен в программу ЛКО Кардио. Повторное включение не может быть выполнено.');
				}
			}

			$data['PersonPrivilege_begDate'] = $data['Privilege_begDate'];
			$data['PersonPrivilege_endDate'] = $data['Privilege_endDate'];

			$response = $this->validatePrivilege($data);
			if (!$this->isSuccessful($response)) {
				throw new Exception(!empty($response[0]['Error_Msg']) ? $response[0]['Error_Msg'] : 'Ошибка при проверке включения в программу');
			}

			$response = $this->savePersonPrivilege($data);
			if (!$this->isSuccessful($response)) {
				throw new Exception(!empty($response[0]['Error_Msg']) ? $response[0]['Error_Msg'] : 'Ошибка при сохранении информации о включении в программу');
			} else {
				if (is_array($response) && count($response) > 0 && !empty($response[0]['PersonPrivilege_id'])) {
					$result['PersonPrivilege_id'] = $response[0]['PersonPrivilege_id'];
				}
			}

			$result['success'] = true;
			$this->commitTransaction();
		} catch (Exception $e) {
			$result['success'] = false;
			$result['Error_Msg'] = $e->getMessage();
			$this->rollbackTransaction();
		}

		return $result;
	}

	/**
	 * Сохранение льготы у человека
	 */
	function savePersonPrivilege($data) {
		$params = array(
			'PersonPrivilege_id' => !empty($data['PersonPrivilege_id'])?$data['PersonPrivilege_id']:null,
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'PersonPrivilege_IsAddMZ' => isset($data['PersonPrivilege_IsAddMZ'])?$data['PersonPrivilege_IsAddMZ']:null,
			'PrivilegeType_id' => $data['PrivilegeType_id'],
			'Lpu_id' => !empty($data['Lpu_id'])?$data['Lpu_id']:null,
			'PersonPrivilege_begDate' => $data['PersonPrivilege_begDate'],
			'PersonPrivilege_endDate' => !empty($data['PersonPrivilege_endDate'])?$data['PersonPrivilege_endDate']:null,
            'Diag_id' => !empty($data['Diag_id'])?$data['Diag_id']:null,
            'PrivilegeCloseType_id' => !empty($data['PrivilegeCloseType_id'])?$data['PrivilegeCloseType_id']:null,
            'pmUser_id' => $data['pmUser_id']
		);

		if ($params['PersonPrivilege_IsAddMZ'] == 2) {
			$params['Server_id'] = 7;
		}

		if (!empty($params['PersonPrivilege_id'])) {
			$Lpu_id = $this->getFirstResultFromQuery("
				select Lpu_id from v_PersonPrivilege where PersonPrivilege_id = :PersonPrivilege_id limit 1
			", $params, true);
			if ($Lpu_id === false) {
				return $this->createError('','Ошибка при получении МО из льготы человека');
			}
			$params['Lpu_id'] = $Lpu_id;
		}

		if (empty($params['PersonPrivilege_id'])) {
			$procedure = "p_PersonPrivilege_ins";
		} else {
			$procedure = "p_PersonPrivilege_upd";
		}

		$query = "
			select
				PersonPrivilege_id as \"PersonPrivilege_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure} (
				Server_id := :Server_id,
				PersonPrivilege_id := :PersonPrivilege_id,
				Person_id := :Person_id,
				PersonPrivilege_IsAddMZ := :PersonPrivilege_IsAddMZ,
				PrivilegeType_id := :PrivilegeType_id,
				Lpu_id := :Lpu_id,
				PersonPrivilege_begDate := CAST(:PersonPrivilege_begDate as date),
				PersonPrivilege_endDate := CAST(:PersonPrivilege_endDate as date),
				Diag_id := :Diag_id,
				PrivilegeCloseType_id := :PrivilegeCloseType_id,
				pmUser_id := :pmUser_id
			);
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении льготы человека');
		}
		return $response;
	}

	/**
	* Получение списка льгот человека
	*/
	function loadPersonPrivilegeList($data) {
		$filter = null;

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("PT.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and $privilegeFilter";
		}
		
		if ($this->getRegionNick() == 'ufa') {	
			$lpuFilter = getAccessRightsLpuFilter('PP.Lpu_id');
			$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : '';
			$filter .= $lpuFilter;
		} 

		$query = "
			select
				Lpu.Lpu_id as \"Lpu_id\",
				PP.Person_id as \"Person_id\",
				PP.PersonEvn_id as \"PersonEvn_id\",
				PP.PersonPrivilege_id as \"PersonPrivilege_id\",
				PP.Server_id as \"Server_id\",
				PP.PrivilegeType_id as \"PrivilegeType_id\",
				PT.PrivilegeType_Code as \"PrivilegeType_Code\",
				coalesce(PT.PrivilegeType_VCode, cast(PT.PrivilegeType_Code as varchar)) as \"PrivilegeType_VCode\",
				RTRIM(PP.PrivilegeType_Name) as \"PrivilegeType_Name\",
				to_char(PP.PersonPrivilege_begDate, 'DD.MM.YYYY') as \"Privilege_begDate\",
				to_char(PP.PersonPrivilege_endDate, 'DD.MM.YYYY') as \"Privilege_endDate\",
				-- использовать ID не очень хорошо, но приходится - но лучше добавить во вьюху ReceptFinance_id
				case when WDCIT.WhsDocumentCostItemType_Nick = 'fl' then case when PR.PersonRefuse_IsRefuse = 2 then 'true' else 'false' end else '' end as \"Privilege_Refuse\",
				case when WDCIT.WhsDocumentCostItemType_Nick = 'fl' then case when PR2.PersonRefuse_IsRefuse = 2 then 'true' else 'false' end else '' end as \"Privilege_RefuseNextYear\",
				case
					when PPs.Server_id = 3 then 'ПФР'
					when PPs.Server_id = 7 then 'Минздрав'
					else COALESCE(Lpu.Lpu_Nick,Lpu.Lpu_Name,'') 
				end as \"Lpu_Name\",
				PPs.Server_id as \"Server_id\",
				RF.ReceptFinance_id as \"ReceptFinance_id\",
				RF.ReceptFinance_Code as \"ReceptFinance_Code\",
				coalesce(PCT.PrivilegeCloseType_Name, '') as \"PrivilegeCloseType_Name\",
				coalesce(DP.DocumentPrivilege_Data, '') as \"DocumentPrivilege_Data\",
				coalesce(D.Diag_Code || ' ' || D.Diag_Name, '') as \"Diag_Name\"
			from v_PersonPrivilege PP -- здесь во вьюху надо добавить ReceptFinance_id 
				inner join PersonPrivilege PPs on PPs.PersonPrivilege_id = PP.PersonPrivilege_id
				inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
				left join v_WhsDocumentCostItemType WDCIT on WDCIT.WhsDocumentCostItemType_id = PT.WhsDocumentCostItemType_id
				left join v_ReceptFinance RF on RF.ReceptFinance_id = PT.ReceptFinance_id
				left join v_PersonRefuse PR on PR.Person_id = PP.Person_id
					and PR.PersonRefuse_Year = date_part('year', dbo.tzGetDate())
				left join v_PersonRefuse PR2 on PR2.Person_id = PP.Person_id
					and PR2.PersonRefuse_Year = date_part('year', dbo.tzGetDate()) +1 
				left join v_Lpu Lpu on Lpu.Lpu_id = PP.Lpu_id
				left join v_PrivilegeCloseType PCT on PCT.PrivilegeCloseType_id = PPs.PrivilegeCloseType_id
				left join v_Diag D on D.Diag_id = PPs.Diag_id
				left join lateral (
					select 
						(
							i_DP.DocumentPrivilege_Ser||' '||
							i_DP.DocumentPrivilege_Num||' '||
							to_char(i_DP.DocumentPrivilege_begDate, 'dd.mm.yyyy')||' '||
							coalesce(i_O.Org_Nick, i_DP.DocumentPrivilege_Org, '')	
						) as DocumentPrivilege_Data
					from
					 	v_DocumentPrivilege i_DP
						left join v_Org i_O on i_O.Org_id = i_DP.Org_id
					where
						i_DP.PersonPrivilege_id = PPs.PersonPrivilege_id
					order by
						i_DP.DocumentPrivilege_id
					limit 1
				) DP on true
			where (1 = 1)
				and PP.Person_id = :Person_id
				{$filter}
		";
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для формы редактирования льготы
	*/
	function loadPrivilegeEditForm($data) {
		$params = array(
			'PersonPrivilege_id' => $data['PersonPrivilege_id']
		);
		$query = "
			select
				pp.PrivilegeType_id as \"PrivilegeType_id\",
				pp.PersonPrivilege_id as \"PersonPrivilege_id\",
				pp.PersonPrivilege_IsAddMZ as \"PersonPrivilege_IsAddMZ\",
				to_char(pp.PersonPrivilege_begDate, 'DD.MM.YYYY') as \"Privilege_begDate\",
				to_char(pp.PersonPrivilege_endDate, 'DD.MM.YYYY') as \"Privilege_endDate\",
				case when er.EvnRecept_Count > 0 then 1 else 0 end as \"hasRecepts\",
				pt.PrivilegeType_SysNick as \"PrivilegeType_SysNick\",
				pp.Diag_id as \"Diag_id\",
				pp.PrivilegeCloseType_id as \"PrivilegeCloseType_id\",
				dp.DocumentPrivilege_id as \"DocumentPrivilege_id\",
				dp.DocumentPrivilegeType_id as \"DocumentPrivilegeType_id\",
				dp.DocumentPrivilege_Ser as \"DocumentPrivilege_Ser\",
				dp.DocumentPrivilege_Num as \"DocumentPrivilege_Num\",
				to_char(dp.DocumentPrivilege_begDate, 'dd.mm.yyyy') as \"DocumentPrivilege_begDate\",
				dp.DocumentPrivilege_Org as \"DocumentPrivilege_Org\"
			from 
				PersonPrivilege pp
				left join PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
				left join lateral (
					select count(*) as EvnRecept_Count
					from v_EvnRecept er
					where er.PersonPrivilege_id = pp.PersonPrivilege_id
					limit 1
				) er on true
				left join lateral (
					select
						i_dp.DocumentPrivilege_id,
						i_dp.DocumentPrivilegeType_id,
						i_dp.DocumentPrivilege_Ser,
						i_dp.DocumentPrivilege_Num,
						i_dp.DocumentPrivilege_begDate,
						i_dp.DocumentPrivilege_Org
					from
						v_DocumentPrivilege i_dp
					where
						i_dp.PersonPrivilege_id = pp.PersonPrivilege_id
					order by
						i_dp.DocumentPrivilege_id
					limit 1
				) dp on true
			where 
				pp.PersonPrivilege_id = coalesce(:PersonPrivilege_id::bigint, 0)
			order by 
				pp.PersonPrivilege_id desc
			limit 1
		";
		return $this->queryResult($query, $params);
	}


	/**
	* Получение списка льгот у человека для комбобокса
	*/
	function loadPersonCategoryList($data) {
		$fields = '';
		$filter = '';
		$join = '';
		$queryParams = array();

		$queryParams['Date'] = $data['Date'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['Person_id'] = $data['Person_id'];

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("PT.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and $privilegeFilter";
		}
		
		if ($this->getRegionNick() == 'ufa') {	
			$lpuFilter = getAccessRightsLpuFilter('PP.Lpu_id');
			$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : '';
			$filter .= $lpuFilter;
		}

		if ($this->getRegionNick() == 'kz') {
			$fields .= "SCPT.SubCategoryPrivType_id as \"SubCategoryPrivType_id\",\n";
			$fields .= "SCPT.SubCategoryPrivType_Code as \"SubCategoryPrivType_Code\",\n";
			$fields .= "SCPT.SubCategoryPrivType_Name as \"SubCategoryPrivType_Name\",\n";
			$join .= " left join r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT on PPSCPT.PersonPrivilege_id = PP.PersonPrivilege_id";
			$join .= " left join r101.v_SubCategoryPrivType SCPT on SCPT.SubCategoryPrivType_id = PPSCPT.SubCategoryPrivType_id";
			$filter .= " and RD.ReceptDiscount_Name <> '0%'";
		}

		$query = "
			select
				PT.PrivilegeType_id as \"PrivilegeType_id\",
				PT.PrivilegeType_Code as \"PrivilegeType_Code\",
				coalesce(PT.PrivilegeType_VCode, cast(PT.PrivilegeType_Code as varchar)) as \"PrivilegeType_VCode\",
				PT.PrivilegeType_Name as \"PrivilegeType_Name\",
				PT.PrivilegeType_SysNick as \"PrivilegeType_SysNick\",
				PT.ReceptDiscount_id as \"ReceptDiscount_id\",
				PT.ReceptFinance_id as \"ReceptFinance_id\",
				PT.DrugFinance_id as \"DrugFinance_id\",
				PT.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				PP.PersonPrivilege_id as \"PersonPrivilege_id\",
				PP.PersonPrivilege_IsClosed as \"PersonPrivilege_IsClosed\",
				PP.PersonPrivilege_IsNoPfr as \"PersonPrivilege_IsNoPfr\",
				coalesce(PD.PersonPrivilege_IsPersonDisp,M.PersonPrivilege_IsPersonDisp) as \"PersonPrivilege_IsPersonDisp\",
				PR.PersonRefuse_IsRefuse as \"PersonRefuse_IsRefuse\",
				{$fields}
				PP.PersonPrivilege_IsAddMZ as \"PersonPrivilege_IsAddMZ\"
			from v_PrivilegeType PT
				inner join lateral (
					SELECT
						PersonPrivilege_id,
						PrivilegeType_id,
						CASE WHEN PersonPrivilege_endDate is null or PersonPrivilege_endDate >= CAST(:Date  as date) THEN 1 ELSE 2 END as PersonPrivilege_IsClosed,
						PersonPrivilege_IsAddMZ,
						PersonPrivilege_IsNoPfr,
						Lpu_id
					from v_PersonPrivilege
					where Person_id = :Person_id
						and PrivilegeType_id = PT.PrivilegeType_id
						and PersonPrivilege_begDate is not null
						and PersonPrivilege_begDate <= CAST(:Date as date)
					order by PersonPrivilege_IsClosed
					limit 1
				) PP on true
				left join lateral (
					select 2 as PersonPrivilege_IsPersonDisp
					from v_PersonDisp
					where Person_id = :Person_id
						and Sickness_id in (1, 3, 4, 5, 6, 7, 8)
						and PersonDisp_begDate <= CAST(:Date as date)
						and (PersonDisp_endDate is null or PersonDisp_endDate > CAST(:Date as date))
					limit 1
				) PD on true
				left join lateral (
					select 2 as PersonPrivilege_IsPersonDisp
					from v_Morbus M
					inner join v_Diag D on D.Diag_id = M.Diag_id
					where M.Person_id = :Person_id
						and D.Diag_Code in ('C92.1', 'C88.0', 'C82', 'C82.0', 'C82.1', 'C82.2', 'C82.7', 'C82.9', 'C83.0', 'C83.1', 'C83.3', 'C83.4', 'C83.8', 'C83.9', 'C85', 'C85.0', 'C85.1', 'C85.7', 'C85.9', 'C91.1')
						and M.Morbus_setDT <= CAST(:Date as date)
						and (M.Morbus_disDT is null or M.Morbus_disDT > CAST(:Date as date))
					limit 1
				) M on true
				left join lateral (
					select PersonRefuse_IsRefuse
					from v_PersonRefuse
					where Person_id = :Person_id
						and PersonRefuse_Year = date_part('year', CAST(:Date as date))
					limit 1
				) PR on true
				left join v_ReceptDiscount RD on RD.ReceptDiscount_id = PT.ReceptDiscount_id
				{$join}
			WHERE
				(1=1)
				{$filter}
			ORDER BY
				PP.PersonPrivilege_IsClosed,
				PT.PrivilegeType_Code
		";
		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение списка льгот человека для просмотра в ЭМК
	*/
	function getPersonPrivilegeViewData($data) {
		$filter = null;

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("PT.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and $privilegeFilter";
		}
		
		if ($this->getRegionNick() == 'ufa') {	
			$lpuFilter = getAccessRightsLpuFilter('PP.Lpu_id');
			$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : '';
			$filter .= $lpuFilter;
		} 

		$query = "
			select
				PP.Person_id as \"Person_id\",
				PP.PersonPrivilege_id as \"PersonPrivilege_id\",
				PP.PersonPrivilege_id as \"ExpertHistory_id\",
				to_char(PP.PersonPrivilege_begDate, 'DD.MM.YYYY') as \"PersonPrivilege_begDate\",
				to_char(PP.PersonPrivilege_endDate, 'DD.MM.YYYY') as \"PersonPrivilege_endDate\",
				coalesce(PT.PrivilegeType_Name, '') as \"PrivilegeType_Name\",
				PP.pmUser_insID as \"pmUser_insID\",
				PP.Lpu_id as \"Lpu_id\",
				'' as \"PersonPrivilege_IsActual\",
				null as \"SubCategoryPrivType_Name\",
				coalesce(PCT.PrivilegeCloseType_Name, '') as \"PrivilegeCloseType_Name\",
				coalesce(DP.DocumentPrivilege_Data, '') as \"DocumentPrivilege_Data\",
				coalesce(D.Diag_Code || ' ' || D.Diag_Name, '') as \"Diag_Name\"
			from v_PersonPrivilege PP
				inner join PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
				left join v_PrivilegeCloseType PCT on PCT.PrivilegeCloseType_id = PP.PrivilegeCloseType_id
				left join v_Diag D on D.Diag_id = PP.Diag_id
				left join lateral (
					select
						(
							i_DP.DocumentPrivilege_Ser||' '||
							i_DP.DocumentPrivilege_Num||' '||
							to_char(i_DP.DocumentPrivilege_begDate, 'dd.mm.yyyy')||' '||
							coalesce(i_O.Org_Nick, i_DP.DocumentPrivilege_Org, '')	
						) as DocumentPrivilege_Data
					from
					 	v_DocumentPrivilege i_DP
						left join v_Org i_O on i_O.Org_id = i_DP.Org_id
					where
						i_DP.PersonPrivilege_id = PP.PersonPrivilege_id
					order by
						i_DP.DocumentPrivilege_id
					limit 1
				) as DP on true
			where 
				PP.Person_id = coalesce(:Person_id::bigint, 0)
				{$filter}
			order by
				PP.PersonPrivilege_begDate
		";

		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	*	Получение кода типа финансирования льготы
	*/
	function getPrivilegeReceptFinance($data) {
		$query = "
			select
				RF.ReceptFinance_Code as \"ReceptFinance_Code\"
			from
				v_PrivilegeType PT
				inner join v_ReceptFinance RF on RF.ReceptFinance_id = PT.ReceptFinance_id
			where 
				PT.PrivilegeType_id = :PrivilegeType_id
			limit 1
		";

		$result = $this->db->query($query, array(
			'PrivilegeType_id' => $data['PrivilegeType_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение номера СНИЛС  (Если СНИЛС отсутствует, льготу добавлять нельзя)
	 */
	function getSnilsNumber($data) {
		$query = "
			select
				vper.Person_Snils as \"Person_Snils\"
			from v_PersonState vper
			where vper.Person_id = :Person_id
			limit 1
		";
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));

		return $result->result('array');
	}

	/**
	 *	Получение идентификатора категории льготы по системному наименованию
	 */
	function getPrivilegeTypeIdBySysNick($PrivilegeType_SysNick, $onDate = null) {
		$filterList = array('PrivilegeType_SysNick = :PrivilegeType_SysNick');
		$queryParams = array(
			'PrivilegeType_SysNick' => (!empty($PrivilegeType_SysNick) ? $PrivilegeType_SysNick : NULL)
		);

		if ( !empty($onDate) ) {
			$filterList[] = '(PrivilegeType_begDate is null or PrivilegeType_begDate <= CAST(:onDate as date))';
			$filterList[] = '(PrivilegeType_endDate is null or PrivilegeType_endDate > CAST(:onDate as date))';
			$queryParams['onDate'] = $onDate;
		}

		$query = "
			select PrivilegeType_id as \"PrivilegeType_id\"
			from v_PrivilegeType
			where " . implode(' and ', $filterList) . "
			limit 1
		";

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return false;
		}

		return $response[0]['PrivilegeType_id'];
	}

	/**
	 * Получение идентификатора категории льготы по коду
	 */
	function getPrivilegeTypeIdByCode($PrivilegeType_Code, $onDate = null) {
		$filterList = array('PrivilegeType_Code = :PrivilegeType_Code');
		$queryParams = array('PrivilegeType_Code' => $PrivilegeType_Code);

		if ( !empty($onDate) ) {
			$filterList[] = '(PrivilegeType_begDate is null or PrivilegeType_begDate <= CAST(:onDate as date))';
			$filterList[] = '(PrivilegeType_endDate is null or PrivilegeType_endDate > CAST(:onDate as date))';
			$queryParams['onDate'] = $onDate;
		}

		$query = "
			select PrivilegeType_id as \"PrivilegeType_id\"
			from v_PrivilegeType
			where " . implode(' and ', $filterList) . "
			limit 1
		";

		return $this->getFirstResultFromQuery($query, $queryParams);
	}

	/**
	 * Получение программы ЛЛО по идентификатору категории льготы
	 */
	function getWhsDocumentCostItemTypeByPrivilegeType($data) {
		$query = "
			select
				wdcit.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				wdcit.WhsDocumentCostItemType_Code as \"WhsDocumentCostItemType_Code\",
				wdcit.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				wdcit.WhsDocumentCostItemType_Nick as \"WhsDocumentCostItemType_Nick\"
			from
				v_PrivilegeType pt
				left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = pt.WhsDocumentCostItemType_id
			where
				pt.PrivilegeType_id = :PrivilegeType_id
			limit 1
		";

		$result = $this->getFirstRowFromQuery($query, array(
			'PrivilegeType_id' => $data['PrivilegeType_id']
		));

		if (!is_array($result)) {
			$result['success'] = true;
		}

		return $result;
	}

	/**
	*	Получение списка территорий для текущего региона
	*/
	function getKLAreaStatList($data) {

		$query = "
			select
				KLAreaStat_id as \"KLAreaStat_idEdit\",
				KLArea_Name as \"KLArea_Name\"
			from
				v_KLAreaStat
			where
				KLRGN_id = {$data['session']['region']['number']}
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Определение необходимости получения подтверждения для включения в программу ДЛО Кардио
	 */
	function getKardioPrivilegeConsentData($data) {
		$result = array(
			'success' => false,
			'need_consent' => '0',
			'recept_edit_allowed' => '0',
			'EvnPS_id' => !empty($data['EvnPS_id']) ? $data['EvnPS_id'] : null,
			'EvnPS_disDate' => null
		);

		if (!empty($data['EvnPS_id'])) {
			$query = "					
				with vars as (
					select
						(
							select Lpu_id from v_EvnPS where EvnPS_id = :EvnPS_id limit 1
						) as Lpu_id,
						(
							select Diag_id from v_EvnPS where EvnPS_id = :EvnPS_id limit 1
						) as Diag_id,
						(
							select VolumeType_id from v_VolumeType where VolumeType_Code = '2019_kardio' limit 1
						) as VolumeType_id,
						(
							select DiagSetClass_id from v_DiagSetClass where DiagSetClass_SysNick = 'osn' limit 1
						) as Osn_DiagSetClass,
						(
							select DiagSetClass_id from v_DiagSetClass where DiagSetClass_SysNick = 'sop' limit 1
						) as Sop_DiagSetClass
				),
				attribute_info as (
					select
						min(case when a.Attribute_SysNick = 'Value' then a.Attribute_id else null end) as Value_Attribute_id,
						min(case when a.Attribute_SysNick = 'Lpu' then a.Attribute_id else null end) as Lpu_Attribute_id,
						min(case when a.Attribute_SysNick = 'Diag' then a.Attribute_id else null end) as Diag_Attribute_id,
						min(case when a.Attribute_SysNick = 'UslugaComplex' then a.Attribute_id else null end) as UslugaComplex_Attribute_id
					from
						v_AttributeVision av
						left join v_Attribute a on a.Attribute_id = av.Attribute_id
					where
						av.AttributeVision_TablePKey = (select VolumeType_id from vars)
					limit 1
				),
				usluga_list as ( -- получение списка услуг из КВС
					select distinct
						eu.UslugaComplex_id
					from
						v_EvnUsluga eu
					where
						eu.EvnUsluga_pid = :EvnPS_id or
						eu.EvnUsluga_rid = :EvnPS_id
				),
				diag_list as ( -- получение списка основных и сопутствующих диагнозов из КВС
					select distinct
						ed.Diag_id
					from
						v_EvnDiagPS ed
					where
						(
							ed.EvnDiagPS_pid = :EvnPS_id or
							ed.EvnDiagPS_rid = :EvnPS_id
						) and (
							ed.DiagSetClass_id = (select Osn_DiagSetClass from vars) or
							ed.DiagSetClass_id = (select Sop_DiagSetClass from vars)
						)
					union
					select
						(select Diag_id from vars) as Diag_id
				)
				select
					av_value.AttributeValue_id as \"AttributeValue_id\"
				from
					diag_list dl
					inner join dbo.AttributeValue av_diag on av_diag.AttributeValue_ValueIdent = dl.Diag_id and av_diag.Attribute_id = (select Diag_Attribute_id from attribute_info)
					inner join dbo.AttributeValue av_value on av_value.AttributeValue_id = av_diag.AttributeValue_rid and av_value.Attribute_id = (select Value_Attribute_id from attribute_info) and av_value.AttributeValue_TablePKey = (select VolumeType_id from vars)
					inner join dbo.AttributeValue av_lpu on av_lpu.AttributeValue_rid = av_value.AttributeValue_id and av_lpu.Attribute_id = (select Lpu_Attribute_id from attribute_info) and av_lpu.AttributeValue_ValueIdent = (select Lpu_id from vars)
					left join dbo.AttributeValue av_usluga_complex on av_usluga_complex.AttributeValue_rid = av_value.AttributeValue_id and av_usluga_complex.Attribute_id = (select UslugaComplex_Attribute_id from attribute_info)
					left join lateral (
						select
							i_ul.UslugaComplex_id
						from
							usluga_list i_ul
						where
							i_ul.UslugaComplex_id = av_usluga_complex.AttributeValue_ValueIdent
						limit 1
					) ul on true
				where
					av_usluga_complex.AttributeValue_ValueIdent is null or
					ul.UslugaComplex_id is not null
				limit 1;
				
			";
			$av_data = $this->getFirstRowFromQuery($query, array(
				'EvnPS_id' => $data['EvnPS_id']
			));
			if (!empty($av_data['AttributeValue_id'])) {
				$result['need_consent'] = '1';
			}
			$result['recept_edit_allowed'] = '1'; //по факту на форме не используется, оставил тут на всякий случай
			$result['success'] = true;
		} else if (!empty($data['Person_id'])) {
			//проверка наличия дествующей льготы по программе ДЛО Кардио
			$query = "
				select
					pp.PersonPrivilege_id as \"PersonPrivilege_id\"
				from
					v_PersonPrivilege pp
				where
					pp.Person_id = :Person_id 
					and pp.PrivilegeType_id = (
						select PrivilegeType_id from v_PrivilegeType 
						where PrivilegeType_SysNick = 'kardio' limit 1
					)
					and pp.PersonPrivilege_begDate <= dbo.tzGetDate()::date
					and (
						pp.PersonPrivilege_endDate is null or
						pp.PersonPrivilege_endDate >= dbo.tzGetDate()::date
					)
				limit 1;
			";
			$pp_data = $this->getFirstRowFromQuery($query, array(
				'Person_id' => $data['Person_id']
			));

			if (!empty($pp_data['PersonPrivilege_id'])) {
				$result['recept_edit_allowed'] = '1'; //если льгота есть значит подтверждение не требуется и рецепты можно редактировать
			} else {
				//проверяем доступно ли редактирование рецептов по программе Кардио  с данным пациентом, для этого необходимо найти КВС с заданными параметрами
				$query = "				
					with vars as (
						select
							:Person_id::bigint as Person_id,
							'2019-01-01'::date as EvnPSMinDate,
							(
								select VolumeType_id from v_VolumeType where VolumeType_Code = '2019_kardio' limit 1
							) as VolumeType_id,
							(
								select DiagSetClass_id from v_DiagSetClass where DiagSetClass_SysNick = 'osn' limit 1
							) as Osn_DiagSetClass,
							(
								select DiagSetClass_id from v_DiagSetClass where DiagSetClass_SysNick = 'sop' limit 1
							) as Sop_DiagSetClass
					),
					attribute_info as (
						select
							min(case when a.Attribute_SysNick = 'Value' then a.Attribute_id else null end) as Value_Attribute_id,
							min(case when a.Attribute_SysNick = 'Diag' then a.Attribute_id else null end) as Diag_Attribute_id,
							min(case when a.Attribute_SysNick = 'UslugaComplex' then a.Attribute_id else null end) as UslugaComplex_Attribute_id
						from
							v_AttributeVision av
							left join v_Attribute a on a.Attribute_id = av.Attribute_id
						where
							av.AttributeVision_TablePKey = (select VolumeType_id from vars)
						limit 1
					),
					evnps_list as ( -- получение списка КВС
						select
							eps.EvnPS_id,
							eps.Lpu_id,
							eps.Diag_id
						from
							v_EvnPS eps
							left join v_EvnSection es on es.EvnSection_rid = eps.EvnPS_id
						where
							eps.Person_id = (select Person_id from vars) and
							es.EvnSection_disDate is not null and
							es.EvnSection_disDate >= (select EvnPSMinDate from vars)
					),
					usluga_list as ( -- получение списка услуг из КВС
						select
							evnps_list.EvnPS_id,
							eu.UslugaComplex_id
						from
							evnps_list
							left join v_EvnUsluga eu on eu.EvnUsluga_pid = evnps_list.EvnPS_id or eu.EvnUsluga_rid = evnps_list.EvnPS_id
					),
					diag_list as ( -- получение списка основных и сопутствующих диагнозов из КВС
						select
							epl.EvnPS_id,
							ed.Diag_id
						from
							evnps_list epl
							left join v_EvnDiagPS ed on ed.EvnDiagPS_pid = epl.EvnPS_id or ed.EvnDiagPS_rid = epl.EvnPS_id
						where
							(
								ed.DiagSetClass_id = (select Osn_DiagSetClass from vars) or
								ed.DiagSetClass_id = (select Sop_DiagSetClass from vars)
							)
						union
						select
							epl2.EvnPS_id,
							epl2.Diag_id
						from
							evnps_list epl2
					)
					select
						dl.EvnPS_id as \"EvnPS_id\"
					from
						diag_list dl
						inner join dbo.AttributeValue av_diag on av_diag.AttributeValue_ValueIdent = dl.Diag_id and av_diag.Attribute_id = (select Diag_Attribute_id from attribute_info)
						inner join dbo.AttributeValue av_value on av_value.AttributeValue_id = av_diag.AttributeValue_rid and av_value.Attribute_id = (select Value_Attribute_id from attribute_info) and av_value.AttributeValue_TablePKey = (select VolumeType_id from vars)
						left join dbo.AttributeValue av_usluga_complex on av_usluga_complex.AttributeValue_rid = av_value.AttributeValue_id and av_usluga_complex.Attribute_id = (select UslugaComplex_Attribute_id from attribute_info)
						left join lateral (
							select
								i_ul.UslugaComplex_id
							from
								usluga_list i_ul
							where
								i_ul.EvnPS_id = dl.EvnPS_id and
								i_ul.UslugaComplex_id = av_usluga_complex.AttributeValue_ValueIdent
							limit 1
						) ul on true
					where
						av_usluga_complex.AttributeValue_ValueIdent is null or
						ul.UslugaComplex_id is not null
					limit 1;
				";
				$eps_data = $this->getFirstRowFromQuery($query, array(
					'Person_id' => $data['Person_id']
				));
				if (!empty($eps_data['EvnPS_id'])) { //КВС есть, но нет льготы, поэтому нужно подтверждение включения в программу
					$result['EvnPS_id'] = $eps_data['EvnPS_id'];
					$result['need_consent'] = '1';
					$result['recept_edit_allowed'] = '1';
				}
			}

			$result['success'] = true;
		}

		if (!empty($result['EvnPS_id'])) { //если есть идентификатор КВС, получаем для неё дату выписки
			$query = "
				select
					to_char(cast(eps.EvnPS_disDate as date), 'DD.MM.YYYY') as \"EvnPS_disDate\"
				from
					v_EvnPS eps
				where
					eps.EvnPS_id = :EvnPS_id;
			";
			$eps_data = $this->getFirstRowFromQuery($query, array(
				'EvnPS_id' => $result['EvnPS_id']
			));
			if (!empty($eps_data['EvnPS_disDate'])) {
				$result['EvnPS_disDate'] = $eps_data['EvnPS_disDate'];
			}
		}

		return $result;
	}

	/**
	*	Получение данных выгрузки для Министерства труда
	*/
	function getLaborDepExportData($data) {

		$filter = 'DUC.DrugDocumentType_id = 11';
		$queryParams = array();

		if (!empty($data['PrivilegeType_id'])) {
			$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];
			$filter .= ' and RO.PrivilegeType_id = :PrivilegeType_id';
		}

		if (!empty($data['LabExp_Period'][0])) {
			$queryParams['LabExp_From'] = $data['LabExp_Period'][0];
			$filter .= ' and RO.EvnRecept_otpDate >= CAST(:LabExp_From as date)';
		}

		if (!empty($data['LabExp_Period'][1])) {
			$queryParams['LabExp_To'] = $data['LabExp_Period'][1];
			$filter .= ' and RO.EvnRecept_otpDate <= CAST(:LabExp_To as date)';
		}

		if (!empty($data['KLAreaStat_idEdit'])) {
			$queryParams['KLAreaStat_idEdit'] = $data['KLAreaStat_idEdit'];
			$filter .= ' and (A.KLAreaStat_id = :KLAreaStat_idEdit or (
					coalesce(KAS.KLCountry_id, \' \') = coalesce(OST.KLCountry_id, \' \') and
					coalesce(KAS.KLRgn_id, \' \') = coalesce(OST.KLRgn_id, \' \') and
					coalesce(KAS.KLSubRgn_id, \' \') = coalesce(OST.KLSubRgn_id, \' \') and
					coalesce(KAS.KLCity_id, \' \') = coalesce(OST.KLCity_id, \' \') and
					coalesce(KAS.KLTown_id, \' \') = coalesce(OST.KLTown_id, \' \')
					)
				)';
		}

		$query = "
			select
				RTRIM(PS.Person_SurName) as \"FAM\",
				RTRIM(PS.Person_FirName) as \"IM\",
				RTRIM(PS.Person_SecName) as \"OT\",
				to_char(PS.Person_BirthDay, 'YYYYMMDD') as \"DR\",
				PA.Address_Address as \"ADRES_R\",
				to_char(RO.EvnRecept_otpDate, 'YYYYMMDD') as \"DATE_LS\",
 				Summ.RecSum as \"SUM\"
			from
				ReceptOtov RO
				left join v_OrgFarmacy OFa on OFa.OrgFarmacy_id = RO.OrgFarmacy_id
				left join v_Org Org on Org.Org_id = OFa.Org_id
				left join v_OrgServiceTerr OST on OST.Org_id = OFa.Org_id
				left join v_Address A on coalesce(Org.UAddress_id, Org.PAddress_id) = A.Address_id
				left join v_KLAreaStat KAS on KAS.KLAreaStat_id = :KLAreaStat_idEdit
				left join v_DocumentUCStr DUCS on DUCS.ReceptOtov_id = RO.ReceptOtov_id
				left join v_DocumentUC DUC on DUC.DocumentUC_id = DUCS.DocumentUC_id
				left join lateral (
					select
						case
							when DUCS.DocumentUcStr_IsNDS = 2 then Sum(DUC.DocumentUc_Sum)
							else sum(DUC.DocumentUc_Sum + DUC.DocumentUc_SumNds)
						end as RecSum
					from
						v_DocumentUCStr DUCS
						left join v_DocumentUC DUC on DUC.DocumentUC_id = DUCS.DocumentUC_id
					where
						DUCS.ReceptOtov_id = RO.ReceptOtov_id
					group by DocumentUcStr_IsNDS
				) Summ on true
				left join v_PersonState PS on PS.Person_id = RO.Person_id
				left join v_Address PA on coalesce(PS.PAddress_id, PS.UAddress_id) = PA.Address_id
			where
				{$filter}
		";

		//echo getDebugSQL($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result;
		}
		else {
			return false;
		}
	}

	/**
	 * Автоматическое создание льгот
	 */
	function autoCreatePersonPrivilege($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'Date' => $this->getCurrentDT()->format('Y-m-d')
		);
		$response = array('success' => true, 'list' => array());

		$query = "
			with vars as (
				select
					:Date::date as date,
					:Person_id::bigint as Person_id
			)
			select
				PS.Person_Snils as \"Person_Snils\",
				to_char(PS.Person_birthDay, 'dd.mm.yyyy') as \"Person_birthDay\",
				dbo.Age(PS.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
				PP.Polis_id as \"Polis_id\",
				PC.PersonCard_id as \"PersonCard_id\",
				PC.Lpu_id as \"Lpu_id\"
			from
				v_PersonState PS
				left join lateral (
					select PP.Polis_id
					from v_PersonPolis PP
					where PP.Person_id = PS.Person_id
					and PP.Polis_begDate <= (select date from vars)
					and (PP.Polis_endDate is null or PP.Polis_endDate > (select date from vars))
					limit 1
				) PP on true
				left join lateral (
					select PC.PersonCard_id, PC.Lpu_id
					from v_PersonCard PC
					where PC.Person_id = PS.Person_id
					and PC.LpuAttachType_id = 1
					and PC.PersonCard_begDate <= (select date from vars)
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > (select date from vars))
					limit 1
				) PC on true
			where PS.Person_id = (select Person_id from vars)
			limit 1
		";
		$personInfo = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($personInfo)) {
			return $this->createError('','Не найден человек для автоматического создания льгот');
		}

		$this->load->helper('Person');

		if (getRegionNick() == 'perm'
			&& $personInfo['Person_Age'] < 3
			&& !empty($personInfo['Polis_id'])
			&& !empty($personInfo['PersonCard_id'])
			&& checkPersonSnils($personInfo['Person_Snils'], false)
		) {
			$PrivilegeType_Code = 253;
			$query = "
				with vars as (
					select
						:Date::date as date,
						:Person_id::bigint as Person_id
				)
				select
					count(PP.PersonPrivilege_id) as \"cnt\"
				from
					v_PersonPrivilege PP
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
				where
					PP.Person_id = (select Person_id from vars)
					and PP.PersonPrivilege_begDate <= (select date from vars)
					and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate > (select date from vars))
					and (
						(PT.ReceptFinance_id = 1 and isnumeric(PT.PrivilegeType_Code) = 1 and PT.PrivilegeType_Code between 1 and 150)
						or (PT.ReceptFinance_id = 2 and PT.PrivilegeType_Code = '{$PrivilegeType_Code}')
					)
				limit 1
			";
			$cnt = $this->getFirstResultFromQuery($query, $params);
			if ($cnt === false) {
				return $this->createError('Ошибка при поиске действующих льгот');
			}
			if ($cnt == 0) {
				$PrivilegeType_id = $this->getPrivilegeTypeIdByCode($PrivilegeType_Code, $params['Date']);
				if (!$PrivilegeType_id) {
					return $this->createError('',"Не найден найдена льгота с кодом $PrivilegeType_Code");
				}

				$resp = $this->savePersonPrivilege(array(
					'PersonPrivilege_id' => null,
					'Person_id' => $params['Person_id'],
					'PrivilegeType_id' => $PrivilegeType_id,
					'PersonPrivilege_begDate' => $params['Date'],
					'PersonPrivilege_endDate' => DateTime::createFromFormat('d.m.Y', $personInfo['Person_birthDay'])->modify('+3 year')->modify('-1 day')->format('Y-m-d'),
					'PersonPrivilege_IsAddMZ' => null,
					'Lpu_id' => $personInfo['Lpu_id'],
					'Server_id' => 0,
					'pmUser_id' => 1
				));
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
				$response['list'][] = array(
					'PersonPrivilege_id' => $resp[0]['PersonPrivilege_id'],
					'PrivilegeType_id' => $PrivilegeType_id,
					'PrivilegeType_Code' => $PrivilegeType_Code
				);
			}
		}

		return array($response);
	}

	/**
	*	Проверка прикрепления для возможности добавить льготу для Крыма для задачи https://redmine.swan.perm.ru/issues/104566
	*/
	function checkPersonCard($data) {
		$params = array(
			'Person_id'	=> $data['Person_id'],
			'Lpu_id'	=> $data['Lpu_id']
		);
		$query = "
			select count(PC.PersonCard_id) as \"cntPC\"
			from v_PersonCard PC
			inner join v_LpuAttachType LAT on LAT.LpuAttachType_id = PC.LpuAttachType_id
			where PC.Person_id = :Person_id
			and PC.Lpu_id = :Lpu_id
			and LAT.LpuAttachType_SysNick in ('main','slug')
		";
		$result = $this->db->query($query,$params);
		if(is_object($result))
		{
			$result = $result->result('array');
			return $result;
		}
		else
			return false;
	}

	/**
	 * Выгрузка данных для сверки с ПФР
	 */
	public function getPFRValidationDataForExport($data) {
		$timestamp = time();

		// каталог в котором лежат выгружаемые файлы
		$out_dir = "exportPFRValidationData_" . $timestamp;
		mkdir(EXPORTPATH_ROOT . $out_dir) or die('Ошибка при создании папки для хранения выгружаемых файлов');

		$filterList = array('(1 = 1)');

		if ( !empty($data['rejectEmptySNILS']) ) {
			//$filterList[] = 'Person_Snils is not null';
		}

		$query = "
			select
				 Person_id as \"Person_id\"
				,ltrim(rtrim(Person_Snils)) as \"Person_Snils\"
				,ltrim(rtrim(Person_SurName)) as \"Person_SurName\"
				,ltrim(rtrim(Person_FirName)) as \"Person_FirName\"
				,ltrim(rtrim(Person_SecName)) as \"Person_SecName\"
				,to_char(Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\"
				,left(sx.Sex_Name, 1) as \"Sex_Name\"
			from
				{$data['tableScheme']}.{$data['tableName']} t
				left join Sex sx on sx.Sex_id = t.Sex_id
			where
				" . implode(' and ', $filterList) . "
		";
		$result = $this->db->query($query);

		if ( !is_object($result) ) {
			return 'Ошибка при получении данных';
		}

		// шаблон для записи
		$recordTemplate = '
	{PFR_DATA}
	<Валидация_запрос>
		<ИдентификаторЗапроса>{Person_id}</ИдентификаторЗапроса>
		<СНИЛС>{Person_Snils}</СНИЛС>
		<Фамилия>{Person_SurName}</Фамилия>
		<Имя>{Person_FirName}</Имя>
		<Отчество>{Person_SecName}</Отчество>
		<ДатаРождения>{Person_BirthDay}</ДатаРождения>
		<Пол>{Sex_Name}</Пол>
	</Валидация_запрос>
	{/PFR_DATA}';

		$filesCount = 0;
		$ERROR_DATA = array();
		$PFR_DATA = array();
		$recordsCount = 0;

		while ( $record = $result->_fetch_assoc()) {
			if ( !empty($data['rejectEmptySNILS']) && mb_strlen(trim($record['Person_Snils'])) == 0 ) {
				$record['error'] = 'Отсутствует СНИЛС';
				$ERROR_DATA[] = $record;
				continue;
			}

			if (
				mb_strlen(trim($record['Sex_Name'])) == 0
				|| mb_strlen($record['Person_SurName']) <= 1
				|| mb_strlen($record['Person_FirName']) <= 1
				|| !preg_match("/^[а-яА-ЯёЁ]([-а-яА-Я ёЁ])*[^ ]$/u", $record['Person_SurName'])
				|| !preg_match("/^[а-яА-ЯёЁ]([-а-яА-Я ёЁ])*[^ ]$/u", $record['Person_FirName'])
			) {
				$error = '';

				if ( mb_strlen(trim($record['Sex_Name'])) == 0 ) {
					$error .= 'не указан пол. ';
				}

				if ( mb_strlen($record['Person_SurName']) <= 1 || !preg_match("/^[а-яА-ЯёЁ]([-а-яА-Я ёЁ])*[^ ]$/u", $record['Person_SurName']) ) {
					$error .= 'Ошибка в фамилии. ';
				}

				if ( mb_strlen($record['Person_FirName']) <= 1 || !preg_match("/^[а-яА-ЯёЁ]([-а-яА-Я ёЁ])*[^ ]$/u", $record['Person_FirName']) ) {
					$error .= 'Ошибка в имени. ';
				}

				$record['error'] = $error;

				$ERROR_DATA[] = $record;
				continue;
			}

			if ( !empty($record['Person_Snils']) && strlen($record['Person_Snils']) == 11 ) {
				$record['Person_Snils'] = substr($record['Person_Snils'], 0, 3) . '-' . substr($record['Person_Snils'], 3, 3) . '-' . substr($record['Person_Snils'], 6, 3) . ' ' . substr($record['Person_Snils'], -2);
			}

			$PFR_DATA[] = $record;
			$recordsCount++;

			if ( $recordsCount >= $data['maxRecordsPerFile'] ) {
				$filesCount++;

				// имя файла
				$fileSign = 'VALIDATION-REQ-' . sprintf('%03d', $data['CCC']) . '-' . sprintf('%03d', $data['KKK']) . '-01-' . sprintf('%03d', $filesCount);

				// основной файл
				$xmlFile = EXPORTPATH_ROOT . $out_dir . "/" . $fileSign . ".XML";

				// файл с ошибками
				$errorFile = EXPORTPATH_ROOT . $out_dir . "/" . $fileSign . "_ERRORS.CSV";

				// пишем в файл
				$xmlHeader = '<?xml version="1.0" encoding="Windows-1251"?>
<ФайлПФР>
	<ИмяФайла>' . $fileSign . '.XML</ИмяФайла>
	<ДатаФормирования>' . date('d.m.Y') . '</ДатаФормирования>
	<ВерсияФормата>1.0</ВерсияФормата>
	<ТипФайла>ВАЛИДАЦИЯ_ЗАПРОС</ТипФайла>
	<КоличествоЗаписейВфайле>' . $recordsCount . '</КоличествоЗаписейВфайле>
	';

				file_put_contents($xmlFile, iconv('UTF-8', 'CP1251//IGNORE', $xmlHeader));

				$xml = $this->parser->parse_from_string($recordTemplate, array('PFR_DATA' => $PFR_DATA), true);
				file_put_contents($xmlFile, iconv('UTF-8', 'CP1251//IGNORE', $xml), FILE_APPEND);
				file_put_contents($xmlFile, iconv('UTF-8', 'CP1251//IGNORE', "\r\n</ФайлПФР>"), FILE_APPEND);

				// Пишем ошибки
				if ( count($ERROR_DATA) > 0 ) {
					file_put_contents($errorFile, iconv('UTF-8', 'CP1251//IGNORE', "Person_id;СНИЛС;Фамилия;Имя;Отчество;Дата рождения;Пол;Ошибка\r\n"), FILE_APPEND);
					
					foreach ( $ERROR_DATA as $row ) {
						file_put_contents($errorFile, iconv('UTF-8', 'CP1251//IGNORE', implode(';', $row) . "\r\n"), FILE_APPEND);
					}
				}

				unset($xml);
				unset($ERROR_DATA);
				unset($PFR_DATA);

				$ERROR_DATA = array();
				$PFR_DATA = array();
				$recordsCount = 0;
			}
		}

		if ( count($PFR_DATA) > 0 || count($ERROR_DATA) > 0 ) {
			$filesCount++;

			// имя файла
			$fileSign = 'VALIDATION-REQ-' . sprintf('%03d', $data['CCC']) . '-' . sprintf('%03d', $data['KKK']) . '-01-' . sprintf('%03d', $filesCount);

			// основной файл
			$xmlFile = EXPORTPATH_ROOT . $out_dir . "/" . $fileSign . ".XML";

			// файл с ошибками
			$errorFile = EXPORTPATH_ROOT . $out_dir . "/" . $fileSign . "_ERRORS.CSV";

			// пишем в файл
			$xmlHeader = '<?xml version="1.0" encoding="Windows-1251"?>
<ФайлПФР>
	<ИмяФайла>' . $fileSign . '.XML</ИмяФайла>
	<ДатаФормирования>' . date('d.m.Y') . '</ДатаФормирования>
	<ВерсияФормата>1.0</ВерсияФормата>
	<ТипФайла>ВАЛИДАЦИЯ_ЗАПРОС</ТипФайла>
	<КоличествоЗаписейВфайле>' . $recordsCount . '</КоличествоЗаписейВфайле>
	';

			file_put_contents($xmlFile, iconv('UTF-8', 'CP1251//IGNORE', $xmlHeader));

			$xml = $this->parser->parse_from_string($recordTemplate, array('PFR_DATA' => $PFR_DATA), true);
			file_put_contents($xmlFile, iconv('UTF-8', 'CP1251//IGNORE', $xml), FILE_APPEND);
			file_put_contents($xmlFile, iconv('UTF-8', 'CP1251//IGNORE', "\r\n</ФайлПФР>"), FILE_APPEND);

			// Пишем ошибки
			if ( count($ERROR_DATA) > 0 ) {
				file_put_contents($errorFile, iconv('UTF-8', 'CP1251//IGNORE', "Person_id;СНИЛС;Фамилия;Имя;Отчество;Дата рождения;Пол;Ошибка\r\n"), FILE_APPEND);
				
				foreach ( $ERROR_DATA as $row ) {
					file_put_contents($errorFile, iconv('UTF-8', 'CP1251//IGNORE', implode(';', $row) . "\r\n"), FILE_APPEND);
				}
			}

			unset($xml);
			unset($ERROR_DATA);
			unset($PFR_DATA);
		}

		return true;
	}

	/**
	 * Выгрузка данных для идентификации в ПФР
	 */
	public function getPFRIdentificationDataForExport($data) {
		$timestamp = time();

		// каталог в котором лежат выгружаемые файлы
		$out_dir = "exportPFRIdentificationData_" . $timestamp;
		mkdir(EXPORTPATH_ROOT . $out_dir) or die('Ошибка при создании папки для хранения выгружаемых файлов');

		$filterList = array('(1 = 1)');

		/*$query = "
			select
				 Person_id
				,ltrim(rtrim(Person_Snils)) as Person_Snils
				,ltrim(rtrim(Person_SurName)) as Person_SurName
				,ltrim(rtrim(Person_FirName)) as Person_FirName
				,ltrim(rtrim(Person_SecName)) as Person_SecName
				,to_char(Person_BirthDay, 'DD.MM.YYYY') as Person_BirthDay
				,left(sx.Sex_Name, 1) as Sex_Name
				,t.place
			from
				{$data['tableScheme']}.{$data['tableName']} t
				left join Sex sx on sx.Sex_id = t.Sex_id
			where
				" . implode(' and ', $filterList) . "
		";*/
		$query = "
			select
				 person_id as \"Person_id\"
				,ltrim(rtrim(Person_Snils)) as \"Person_Snils\"
				,ltrim(rtrim(Person_SurName)) as \"Person_SurName\"
				,ltrim(rtrim(Person_FirName)) as \"Person_FirName\"
				,ltrim(rtrim(Person_SecName)) as \"Person_SecName\"
				,to_char(cast(Person_BirthDay as date), 'DD.MM.YYYY') as \"Person_BirthDay\"
				,left(sx.Sex_Name, 1) as \"Sex_Name\"
				,null as \"place\"
			from
				{$data['tableScheme']}.{$data['tableName']} t
				left join Sex sx on sx.Sex_id = t.Sex_id
			where
				" . implode(' and ', $filterList) . "
		";
		$result = $this->db->query($query);

		if ( !is_object($result) ) {
			return 'Ошибка при получении данных';
		}

		// шаблон для записи
		$recordTemplate = '
	{PFR_DATA}
	<Идентификация_запрос>
		<ИдентификаторЗапроса>{Person_id}</ИдентификаторЗапроса>
		<Фамилия>{Person_SurName}</Фамилия>
		<Имя>{Person_FirName}</Имя>
		<Отчество>{Person_SecName}</Отчество>
		<ДатаРождения>{Person_BirthDay}</ДатаРождения>
		<Пол>{Sex_Name}</Пол>
		<МестоРождения>
			<ГородРождения>{BirthCity}</ГородРождения>
			<РайонРождения>{BirthSubRgn}</РайонРождения>
			<ОбластьРождения>{BirthRgn}</ОбластьРождения>
			<СтранаРождения>{BirthCountry}</СтранаРождения>
		</МестоРождения>
	</Идентификация_запрос>
	{/PFR_DATA}';

		$filesCount = 0;
		$PFR_DATA = array();
		$recordsCount = 0;

		while ( $record = $result->_fetch_assoc()) {
			$record['BirthCity']  = '';
			$record['BirthSubRgn']  = '';
			$record['BirthRgn']  = '';
			$record['BirthCountry']  = '';

			if ( !empty($record['place']) ) {
				list($record['BirthCountry'], $record['BirthRgn'], $record['BirthCity']) = explode('/', $record['place']);
			}

			//var_dump($record); die();

			$PFR_DATA[] = $record;
			$recordsCount++;

			if ( $recordsCount >= $data['maxRecordsPerFile'] ) {
				$filesCount++;

				// имя файла
				$fileSign = 'IDENTIFICATION-REQ-' . sprintf('%03d', $data['CCC']) . '-' . sprintf('%03d', $data['KKK']) . '-01-' . sprintf('%03d', $filesCount);

				// основной файл
				$xmlFile = EXPORTPATH_ROOT . $out_dir . "/" . $fileSign . ".XML";

				// пишем в файл
				$xmlHeader = '<?xml version="1.0" encoding="Windows-1251"?>
<ФайлПФР>
	<ИмяФайла>' . $fileSign . '.XML</ИмяФайла>
	<ДатаФормирования>' . date('d.m.Y') . '</ДатаФормирования>
	<ВерсияФормата>1.0</ВерсияФормата>
	<ТипФайла>ИДЕНТИФИКАЦИЯ_ЗАПРОС</ТипФайла>
	<КоличествоЗаписейВфайле>' . $recordsCount . '</КоличествоЗаписейВфайле>
	';

				file_put_contents($xmlFile, iconv('UTF-8', 'CP1251//IGNORE', $xmlHeader));

				$xml = $this->parser->parse_from_string($recordTemplate, array('PFR_DATA' => $PFR_DATA), true);
				file_put_contents($xmlFile, iconv('UTF-8', 'CP1251//IGNORE', $xml), FILE_APPEND);
				file_put_contents($xmlFile, iconv('UTF-8', 'CP1251//IGNORE', "\r\n</ФайлПФР>"), FILE_APPEND);

				unset($xml);
				unset($PFR_DATA);

				$PFR_DATA = array();
				$recordsCount = 0;
			}
		}

		if ( count($PFR_DATA) > 0 ) {
			$filesCount++;

			// имя файла
			$fileSign = 'IDENTIFICATION-REQ-' . sprintf('%03d', $data['CCC']) . '-' . sprintf('%03d', $data['KKK']) . '-01-' . sprintf('%03d', $filesCount);

			// основной файл
			$xmlFile = EXPORTPATH_ROOT . $out_dir . "/" . $fileSign . ".XML";

			// пишем в файл
			$xmlHeader = '<?xml version="1.0" encoding="Windows-1251"?>
<ФайлПФР>
	<ИмяФайла>' . $fileSign . '.XML</ИмяФайла>
	<ДатаФормирования>' . date('d.m.Y') . '</ДатаФормирования>
	<ВерсияФормата>1.0</ВерсияФормата>
	<ТипФайла>ИДЕНТИФИКАЦИЯ_ЗАПРОС</ТипФайла>
	<КоличествоЗаписейВфайле>' . $recordsCount . '</КоличествоЗаписейВфайле>
	';

			file_put_contents($xmlFile, iconv('UTF-8', 'CP1251//IGNORE', $xmlHeader));

			$xml = $this->parser->parse_from_string($recordTemplate, array('PFR_DATA' => $PFR_DATA), true);
			file_put_contents($xmlFile, iconv('UTF-8', 'CP1251//IGNORE', $xml), FILE_APPEND);
			file_put_contents($xmlFile, iconv('UTF-8', 'CP1251//IGNORE', "\r\n</ФайлПФР>"), FILE_APPEND);

			unset($xml);
			unset($PFR_DATA);
		}

		return true;
	}

	/**
	 * Формирование и передача данных для ЕГИССО
	 */
	function createEgissoData($data) {
		$this->load->helper('CURL');
		$result = array(
			'success' => false
		);

		//определение адреса сервиса
		if (!defined('EGISSO_REST_URL') && empty($data['url'])) {
			$result['Error_Msg'] = 'Не задан адрес сервиса';
			return $result;
		}
		$egisso_url = !empty($data['url']) ? $data['url'] : EGISSO_REST_URL;

		$send_options = array();
		if (!empty($data['options'])) {
			foreach((array) $data['options'] as $key => $val) {
				$send_options[constant($key)] = $val;
			}
		} else {
			$send_options = array(
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json; charset=UTF-8",
				)
			);
		}

		if (!empty($data['debug'])) {
			print 'send_options';
			print_r($send_options);
		}

		//шаблон даных для отправки (по умолчанию содержит тестовые данные)
		$fact_data_tpl = array(
			"id_msz" => 35,
			"id_onmsz" => 4,
			"receiver" => array(
				"snils" => "15523534855",
				"id_gender" => 1,
				"birth_date" => "2005-07-10",
				"first_name" => "ТЕСТ",
				"patronymic" => "ТЕСТ",
				"birth_place" => null,
				"family_name" => "ТЕСТ",
				"phone_number" => null,
				"citizenship" => null,
				"maiden_family_name" => null
			),
			"documents" => array(),
			"date_start" => "2018-12-19",
			"date_finish" => null,
			"decision_date" => "2018-12-19",
			"id_local_category" => 261,
			"id_provision_form" => 3,
			"assignment_natural" => array(
				"amount" => "1",
				"comment" => "",
				"content" => "содержание",
				"id_measury" => 4,
				"equivalentAmount" => "134.6"
			)
		);
		$send_data_tpl = array(
			"request" => "git_egisso.fact_set",
			"ver" => 1,
			"data" => array(
				"params" => array(
					"id_user" => 0,
					"auth" => array(
						"id_user" => 0,
						"logic_role" => "admin",
						"user_display" => "Для проверки ЕГИССО",
						"promed_Lpu_id" => 101,
						"promed_MedPersonal_id" => 0
					),
					"fact" => $fact_data_tpl
				)
			)
		);

		//получение списка рецептов
		$query = "
			select
				er.EvnRecept_id as \"EvnRecept_id\",
				to_char(er.EvnRecept_setDate, 'YYYY-MM-DD') as \"EvnRecept_setDate\",
				to_char(e_dt.EvnRecept_endDate, 'YYYY-MM-DD') as \"EvnRecept_endDate\",
				er.EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
				pt.PrivilegeType_egissoid as \"PrivilegeType_egissoid\",
				coalesce(ps.Person_FirName, '') as \"Person_FirName\",
				coalesce(ps.Person_SurName, '') as \"Person_SurName\",
				coalesce(ps.Person_SecName, '') as \"Person_SecName\",
				coalesce(ps.Person_Snils, '') as \"Person_Snils\",
				to_char(ps.Person_BirthDay, 'YYYY-MM-DD') as \"Person_BirthDay\",
				s.Sex_Code as \"Sex_Code\",
				d.Drug_Name as \"Drug_Name\",
				dp.Drug_Price as \"Drug_Price\"
			from
				v_EvnRecept er
				left join PrivilegeType pt on pt.PrivilegeType_id = er.PrivilegeType_id
				left join v_Drug d on d.Drug_id = er.Drug_id
				left join v_PersonState ps on ps.Person_id = er.Person_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join lateral (
					select
						i_dp.DrugState_Price as Drug_Price
					from
						v_DrugPrice i_dp
					where
						i_dp.ReceptFinance_id = er.ReceptFinance_id and
						i_dp.Drug_id = d.Drug_id and i_dp.DrugProto_begDate <= er.EvnRecept_setDate and
						(
							i_dp.DrugProto_EndDate is null or
							i_dp.DrugProto_EndDate >= er.EvnRecept_setDate
						)
					order by
						i_dp.DrugProto_id desc
					limit 1
				) dp on true
				left join lateral (
					select
						(case
							when i_rv.ReceptValid_Code = 1 then er.EvnRecept_setDate + interval '1 month'
							when i_rv.ReceptValid_Code = 2 then er.EvnRecept_setDate + interval '3 month'
							when i_rv.ReceptValid_Code = 3 then er.EvnRecept_setDate + interval '14 day'
							when i_rv.ReceptValid_Code = 4 then er.EvnRecept_setDate + interval '5 day'
							when i_rv.ReceptValid_Code = 5 then er.EvnRecept_setDate + interval '2 month'
							when i_rv.ReceptValid_Code = 7 then er.EvnRecept_setDate + interval '10 day'
							when i_rv.ReceptValid_Code = 8 then er.EvnRecept_setDate + interval '60 day'
							when i_rv.ReceptValid_Code = 9 then er.EvnRecept_setDate + interval '30 day'
							when i_rv.ReceptValid_Code = 10 then er.EvnRecept_setDate + interval '90 day'
							when i_rv.ReceptValid_Code = 11 then er.EvnRecept_setDate + interval '15 day'
							else null
						end) as EvnRecept_endDate
					from
						dbo.v_ReceptValid i_rv
					where
						i_rv.ReceptValid_id = er.ReceptValid_id	
					limit 1
				) e_dt on true
			where
				pt.PrivilegeType_egissoid is not null and
				er.Drug_id is not null and
				cast(EvnRecept_setDate as date) = :EvnRecept_setDate
			limit 1
		";
		$recept_list = $this->queryResult($query, array(
			'EvnRecept_setDate' => $data['EvnRecept_setDate']
		));
		$send_cnt = 0;
		$response = null;

		foreach($recept_list as $recept_data) {
			if (!empty($recept_data['EvnRecept_id'])) {
				//обнуляем данные при помощи шаблона
				$fact_data = $fact_data_tpl;

				//формирование общих данных
				$fact_data['date_start'] =  $recept_data['EvnRecept_setDate']."";
				$fact_data['date_finish'] = $recept_data['EvnRecept_endDate']."";
				$fact_data['decision_date'] = $recept_data['EvnRecept_setDate']."";
				$fact_data['id_local_category'] = $recept_data['PrivilegeType_egissoid'];

				//формирование данных по пациенту
				$fact_data['receiver'] = array(
					'snils' => $recept_data['Person_Snils']."",
					'id_gender' => !empty($recept_data['Sex_Code']) ? $recept_data['Sex_Code']*1 : null,
					'birth_date' => $recept_data['Person_BirthDay']."",
					'first_name' => $recept_data['Person_FirName']."",
					'patronymic' => $recept_data['Person_SecName']."",
					'birth_place' => null,
					'family_name' => $recept_data['Person_SurName']."",
					'phone_number' => null,
					'citizenship' => null,
					'maiden_family_name' => null
				);

				//формирование данных по медикаменту
				$fact_data['assignment_natural'] = array(
					'amount' => $recept_data['EvnRecept_Kolvo']."",
					'comment' => "",
					'content' => $recept_data['Drug_Name']."",
					'id_measury' => 4,
					'equivalentAmount' => $recept_data['Drug_Price'].""
				);

				$send_data = $send_data_tpl;
				$send_data['data']['params']['fact'] = $fact_data;

				if (!empty($data['debug'])) {
					print 'send_data';
					print_r($send_data);
				}

				$send_data_json = json_encode($send_data);
				$send_data_json = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
					return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
				}, $send_data_json);

				$send_result = CURL(
					$egisso_url,
					$send_data_json,
					'POST',
					null,
					$send_options
				);

				if (!empty($data['debug'])) {
					print 'send_result';
					print_r($send_result);
				}

				if (!empty($send_result['info'])) {
					if ($send_result['info']['http_code'] == '200') {
						$send_cnt++;
						$result['success'] = true;
						if ($send_result['data']) {
							preg_match("/\{(?:[^{}]|(?R))*\}/",$send_result['data'],$m);
							if (is_array($m) && count($m) > 0) {
								$response = $m[0];
							}
						}
					}
				}
			}
		}

		$result['response'] = $response;
		$result['send_cnt'] = $send_cnt;
		$result['recept_cnt'] = count($recept_list);
		$result['success'] = ($send_cnt == count($recept_list));

		return $result;
	}

	/**
	 * Закрыть льготы пациента, связанныые с его нахождением в регистре ВЗН, при удалении пациента из регистра
	 */
	function closeVZNPrivilege($data) {
		//получение льготы
		$query = "
			select 
                PersonServer_id as \"PersonServer_id\",
                Server_pid as \"Server_pid\",
                Person_id as \"Person_id\",
                PersonEvn_id as \"PersonEvn_id\",
                PersonSurName_id as \"PersonSurName_id\",
                PersonSurName_SurName as \"PersonSurName_SurName\",
                PersonFirName_id as \"PersonFirName_id\",
                PersonFirName_FirName as \"PersonFirName_FirName\",
                PersonSecName_id as \"PersonSecName_id\",
                PersonSecName_SecName as \"PersonSecName_SecName\",
                PersonBirthDay_id as \"PersonBirthDay_id\",
                PersonBirthDay_BirthDay as \"PersonBirthDay_BirthDay\",
                PersonSnils_id as \"PersonSnils_id\",
                PersonSnils_Snils as \"PersonSnils_Snils\",
                Lpu_id as \"Lpu_id\",
                PrivilegeType_id as \"PrivilegeType_id\",
                PrivilegeType_Code as \"PrivilegeType_Code\",
                PrivilegeType_Name as \"PrivilegeType_Name\",
                PersonPrivilege_begDate as \"PersonPrivilege_begDate\",
                PersonPrivilege_endDate as \"PersonPrivilege_endDate\",
                PersonRefuse_IsRefuse as \"PersonRefuse_IsRefuse\",
                PersonRefuse_Year as \"PersonRefuse_Year\",
                PersonPrivilege_IsNoPfr as \"PersonPrivilege_IsNoPfr\",
                Diag_id as \"Diag_id\",
                PersonPrivilege_Serie as \"PersonPrivilege_Serie\",
                PersonPrivilege_Number as \"PersonPrivilege_Number\",
                PersonPrivilege_IssuedBy as \"PersonPrivilege_IssuedBy\",
                PersonPrivilege_Group as \"PersonPrivilege_Group\",
                PersonPrivilege_IsAddMZ as \"PersonPrivilege_IsAddMZ\",
                PersonPrivilege_insDT as \"PersonPrivilege_insDT\",
                PersonPrivilege_updDT as \"PersonPrivilege_updDT\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                PersonPrivilege_id as \"PersonPrivilege_id\",
                Server_id as \"Server_id\"
			from v_PersonPrivilege PP
			where PP.Person_id = {$data['Person_id']}
			and PP.PrivilegeType_id in (1024, 1025, 1026, 1027, 1028, 1029, 1030, 3193, 3194, 3195, 3196, 3197)	--идентификаторы ВЗН в Карелии
			and PP.Diag_id = {$data['Diag_id']}
		";

		$result = $this->db->query($query);

		if(is_object($result)) {
			$privilegeVZNList = $result->result('array');
		}

		if(!empty($privilegeVZNList)){
			$data['Privilege_endDate'] = $data['PersonRegister_disDate'];
			foreach ($privilegeVZNList as $privilegeVZN){
				if (empty($privilegeVZN['PersonPrivilege_endDate'])){
					$data['PrivilegeType_id'] = $privilegeVZN['PrivilegeType_id'];
					$data['Privilege_begDate'] = date_format($privilegeVZN['PersonPrivilege_begDate'], 'Y-m-d');
					$data['PersonPrivilege_id'] = $privilegeVZN['PersonPrivilege_id'];
					$this->savePrivilege($data);
				}
			}
		} else {
			return $this->createError('','Ошибка при закрытии льгот, связанных с ВЗН');
		}
	}


    /**
     * Закрыть активные льготы пациента
     */
    function closeAllActivePrivilegesForPerson($data, $disable_trans = false) {
        $result = [
            'success' => false
        ];

        try {
            if (!$disable_trans) {
                $this->beginTransaction();
            }

            //обязательно наличие следующих данных: пациент, дата закрытия, причина
            if (empty($data['Person_id']) || empty($data['PersonPrivilege_endDate']) || empty($data['PrivilegeCloseType_id'])) {
                throw new Exception("Отсутствуют обязательные параметры");
            }

            //получение льготы
            $query = "
				select
					pp.PersonPrivilege_id as \"PersonPrivilege_id\"
				from
					v_PersonPrivilege pp
				where
					pp.Person_id = :Person_id and
					cast(pp.PersonPrivilege_begDate as date) <= dbo.tzGetDate() and
					(
						pp.PersonPrivilege_endDate is null or
						cast(pp.PersonPrivilege_endDate as date) >= dbo.tzGetDate()
					)
			";
            $priv_list = $this->queryList($query, array(
                'Person_id' => $data['Person_id']
            ));

            $save_data = array(
                'PersonPrivilege_endDate' => $data['PersonPrivilege_endDate'],
                'PrivilegeCloseType_id' => $data['PrivilegeCloseType_id']
            );
            foreach ($priv_list as $priv_id){
                if (!empty($priv_id)) {
                    $save_data['PersonPrivilege_id'] = $priv_id;
                    $save_result = $this->saveObject('PersonPrivilege', $save_data);
                    if (empty($save_result['PersonPrivilege_id']) || !empty($save_result['Error_Msg'])) {
                        throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "Ошибка при закрытии льготы");
                    }
                }
            }

            if (!$disable_trans) {
                $this->commitTransaction();
            }
            $result['success'] = true;
        } catch (Exception $e) {
            if (!$disable_trans) {
                $this->rollbackTransaction();
            }
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
        }

        return $result;
    }
    
    /**
	 * Закрыть региональную льготу пациента
	 */
	function closeRegionalActivePrivilegesForPerson($data, $disable_trans = false) {
		$result = array(
			'success' => false
		);

		try {
			if (!$disable_trans) {
				$this->beginTransaction();
			}
			
			if (empty($data['Person_id']) || empty($data['Privilege_begDate'])) throw new Exception("Отсутствуют обязательные параметры");

			//получение льготы
			$query = "
				select
					pp.PersonPrivilege_id as \"PersonPrivilege_id\",
					ER.maxEvnRecept_setDate as \"maxEvnRecept_setDate\"
				from
					v_PersonPrivilege pp
					INNER JOIN v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
					left join lateral (
						--получение даты последнего выписанного не обеспеченного рецепта
						--если есть рецепт выписанный в этот период то закрывать будем его числом, что бы не оказались рецепты в закрытый период 
						select max(ER.EvnRecept_setDate) as maxEvnRecept_setDate
						from v_EvnRecept ER
						where ER.PersonPrivilege_id = pp.PersonPrivilege_id
							AND er.EvnRecept_otpDT is null
							AND er.ReceptRemoveCauseType_id is NULL
							AND er.Person_id = pp.Person_id
							AND ER.EvnRecept_setDate BETWEEN pp.PersonPrivilege_begDate AND :Privilege_begDate
						limit 1
					) ER ON true
				where
					pp.Person_id = :Person_id
					and PT.ReceptFinance_id = 2
					and (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate > :Privilege_begDate)
			";
			
			$res = $this->db->query($query, array(
				'Person_id' => $data['Person_id'],
				'Privilege_begDate' => $data['Privilege_begDate']
			));
			if(is_object($res)) {
				$resultPrivs = $res->result('array');
			}

			$save_data = array(
				'PersonPrivilege_endDate' => $data['Privilege_begDate'],
				'PrivilegeCloseType_id' => 4 //прочее
			);
			foreach ($resultPrivs as $resultPriv){
				if (!empty($resultPriv['PersonPrivilege_id'])) {
					$save_data['PersonPrivilege_id'] = $resultPriv['PersonPrivilege_id'];
					if(!empty($data['maxEvnRecept_setDate']) ) $save['PersonPrivilege_endDate'] = $data['maxEvnRecept_setDate'];
					$save_result = $this->saveObject('PersonPrivilege', $save_data);
					if (empty($save_result['PersonPrivilege_id']) || !empty($save_result['Error_Msg'])) {
						throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "Ошибка при закрытии льготы");
					}
				}
			}

			if (!$disable_trans) {
				$this->commitTransaction();
			}
			$result['success'] = true;
		} catch (Exception $e) {
			if (!$disable_trans) {
				$this->rollbackTransaction();
			}
			$result['success'] = false;
			$result['Error_Msg'] = $e->getMessage();
		}

		return $result;
	}

    /**
     * Сохранение типа документа о праве на льготу
     */
    function saveDocumentPrivilegeType($data) {
        $result = [];

        try {
            $this->beginTransaction();

            //проверка наличия наименования в справочнике
            $query = "
				select
					DocumentPrivilegeType_id as \"DocumentPrivilegeType_id\"
				from
					v_DocumentPrivilegeType
				where
					pmUser_insID != 1 and
					DocumentPrivilegeType_Name = :DocumentPrivilegeType_Name
				order by
					DocumentPrivilegeType_id
				limit 1
			";
            $check_result = $this->getFirstRowFromQuery($query, array(
                'DocumentPrivilegeType_Name' => !empty($data['DocumentPrivilegeType_Name']) ? $data['DocumentPrivilegeType_Name'] : ''
            ));
            if (!empty($check_result['DocumentPrivilegeType_id'])) { //если наименование найдено, возвращаем его
                $result['DocumentPrivilegeType_id'] = $check_result['DocumentPrivilegeType_id'];
            } else { //иначе добавляем наименование в справочник
                $save_result = $this->saveObject('DocumentPrivilegeType', array(
                    'DocumentPrivilegeType_id' => !empty($data['DocumentPrivilegeType_id']) ? $data['DocumentPrivilegeType_id'] : null,
                    'DocumentPrivilegeType_Code' => !empty($data['DocumentPrivilegeType_Code']) ? $data['DocumentPrivilegeType_Code'] : 0,
                    'DocumentPrivilegeType_Name' => !empty($data['DocumentPrivilegeType_Name']) ? $data['DocumentPrivilegeType_Name'] : '',
                ));
                if (!empty($save_result['DocumentPrivilegeType_id'])) {
                    $result['DocumentPrivilegeType_id'] = $save_result['DocumentPrivilegeType_id'];
                } else {
                    throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных произошла ошибка");
                }

                //при добавлении устанавливаем код равный идентификатору
                if (empty($data['DocumentPrivilegeType_id'])) {
                    $save_result = $this->saveObject('DocumentPrivilegeType', array(
                        'DocumentPrivilegeType_id' => $result['DocumentPrivilegeType_id'],
                        'DocumentPrivilegeType_Code' => $result['DocumentPrivilegeType_id']
                    ));
                    if (empty($save_result['DocumentPrivilegeType_id']) || !empty($save_result['Error_Msg'])) {
                        throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных произошла ошибка");
                    }
                }
            }

            $result['success'] = true;
            $this->commitTransaction();
        } catch (Exception $e) {
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
            $this->rollbackTransaction();
        }

        return $result;
    }

    /**
     * Сохранение запроса на включение в региональный регистр льготников
     */
    function savePersonPrivilegeReq($data) {
        $result = [];
        $delete_document = false; //флаг, отражающий необходимость удаления документа о праве на льготу

        try {
            $this->beginTransaction();

            //получение данных о типе льготы
            $query = "
				select
					PrivilegeType_Name as \"PrivilegeType_Name\"
				from
					v_PrivilegeType
				where
					PrivilegeType_id = :PrivilegeType_id;
			";
            $privilege_type_data = $this->getFirstRowFromQuery($query, [
                'PrivilegeType_id' => $data['PrivilegeType_id']
            ]);
            if (empty($privilege_type_data['PrivilegeType_Name'])) {
                throw new Exception('Не удалось получить данные о типе льготы');
            }

            //проверка на наличие действующей льготы
            $check_result = $this->CheckPersonPrivilege([
                'Person_id' => $data['Person_id'],
                'PrivilegeType_id' => $data['PrivilegeType_id'],
                'Privilege_begDate' => $data['DocumentPrivilege_begDate']
            ]);
            if (is_array($check_result) && $check_result[0]['Privilege_Count'] > 0) {
                throw new Exception("Создание запроса по льготе «{$privilege_type_data['PrivilegeType_Name']}» невозможно, так как у пациента уже есть такая льгота");
            }

            //проверка на наличие открытого запроса с такой же льготой
            $query = "
				select
					count(ppr.PersonPrivilegeReq_id) as cnt
				from
					v_PersonPrivilegeReq ppr
					left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
				where
					ppr.PersonPrivilegeReq_id <> coalesce(:PersonPrivilegeReq_id::bigint, 0) and
					ppr.Person_id = :Person_id and
					ppr.PrivilegeType_id = :PrivilegeType_id and 
					ppra.PersonPrivilegeReqStatus_id in (1, 2); -- 1 - Новый; 2 - На рассмотрении
			";
            $check_data = $this->getFirstRowFromQuery($query, [
                'PersonPrivilegeReq_id' => $data['PersonPrivilegeReq_id'],
                'Person_id' => $data['Person_id'],
                'PrivilegeType_id' => $data['PrivilegeType_id']
            ]);
            if (!empty($check_data['cnt'])) {
                throw new Exception("Создание запроса по льготе «{$privilege_type_data['PrivilegeType_Name']}» невозможно, так как уже подан запрос на включение в регистр по этой льготе. Проверьте данные о результатах запроса");
            }
            
            
			if($this->getRegionNick() == 'msk' && !empty($data['ReceptFinance_id']) ){
				$data['Privilege_begDate'] = $data['PersonPrivilegeReq_begDT'];
				if($data['ReceptFinance_id'] == 1){
					$res = $this->CheckPersonHaveActiveRegionalPrivilege($data);
					if ( (is_array($res)) && (count($res) > 0) ) {
						if ( $res[0]['Privilege_Count'] > 0 ) {
							$resCloseRegionalPrivileges = $this->closeRegionalActivePrivilegesForPerson($data);
							if(!empty($resCloseRegionalPrivileges['Error_Msg'])){
								throw new Exception($resCloseRegionalPrivileges['Error_Msg']);
							}
						}
					}
				}elseif( $data['ReceptFinance_id'] == 2 && !isSuperAdmin() && (!haveARMType('spec_mz') || !haveARMType('minzdravdlo')) ) {
					$res = $this->CheckPersonHaveActiveFederalPrivilege(array(
						'Privilege_begDate' => $data['PersonPrivilegeReq_begDT'],
						'Person_id' => $data['Person_id']
					));
					if ( (is_array($res)) && (count($res) > 0) ) {
						if ( $res[0]['Privilege_Count'] > 0 ) {
							throw new Exception('У пользователя отсутствуют права на выполнение операции.');
						}
					}
				}
			}

            //сохранение документа о праве на льготу
            if (!empty($data['DocumentPrivilegeType_id']) || !empty($data['DocumentPrivilege_id'])) {
                if (empty($data['DocumentPrivilegeType_id'])) { //если с формы пришел пустой тип документа, значит его нужно удалить (так же по условию выше подразумеваем что пришел идентификатор документа)
                    //выставляем флаг удаления, чтобы удалить документ после редактирования данных запроса
                    $delete_document = true;
                } else {
                    $save_data = [
                        'DocumentPrivilege_id' => !empty($data['DocumentPrivilege_id']) ? $data['DocumentPrivilege_id'] : null,
                        'DocumentPrivilegeType_id' => !empty($data['DocumentPrivilegeType_id']) ? $data['DocumentPrivilegeType_id'] : null,
                        'DocumentPrivilege_Ser' => !empty($data['DocumentPrivilege_Ser']) ? $data['DocumentPrivilege_Ser'] : null,
                        'DocumentPrivilege_Num' => !empty($data['DocumentPrivilege_Num']) ? $data['DocumentPrivilege_Num'] : null,
                        'DocumentPrivilege_begDate' => !empty($data['DocumentPrivilege_begDate']) ? $data['DocumentPrivilege_begDate'] : null,
                        'DocumentPrivilege_Org' => !empty($data['DocumentPrivilege_Org']) ? $data['DocumentPrivilege_Org'] : null
                    ];
                    $save_result = $this->saveObject('DocumentPrivilege', $save_data);
                    if (!empty($save_result['DocumentPrivilege_id'])) {
                        $data['DocumentPrivilege_id'] = $save_result['DocumentPrivilege_id'];
                    } else {
                        throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных произошла ошибка");
                    }
                }
            }

			//сохранение данных о смене фамилии
			$save_result = $this->savePersonSurNameAtBirth($data);
			if (!empty($save_result['Error_Msg'])) {
				throw new Exception($save_result['Error_Msg']);
			}

            //сохранение данных запроса на включение в Региональный регистр
            $save_data = [
                'PersonPrivilegeReq_id' => !empty($data['PersonPrivilegeReq_id']) ? $data['PersonPrivilegeReq_id'] : null,
                'PrivilegeType_id' => !empty($data['PrivilegeType_id']) ? $data['PrivilegeType_id'] : null,
                'Diag_id' => !empty($data['Diag_id']) ? $data['Diag_id'] : null,
                'PersonPrivilegeReq_begDT' => !empty($data['PersonPrivilegeReq_begDT']) ? $data['PersonPrivilegeReq_begDT'] : null,
                'PersonPrivilegeReq_endDT' => !empty($data['PersonPrivilegeReq_endDT']) ? $data['PersonPrivilegeReq_endDT'] : null,
                'DocumentPrivilege_id' => !empty($data['DocumentPrivilege_id']) && !$delete_document ? $data['DocumentPrivilege_id'] : null
            ];
            $ans_save_data = [
                'PersonPrivilegeReqAns_DeclCause' => !empty($data['PersonPrivilegeReqAns_DeclCause']) ? $data['PersonPrivilegeReqAns_DeclCause'] : null
            ];
            if (empty($data['PersonPrivilegeReq_id'])) { //часть полей сохраняется только при добавлении записи и более не редактируется
                $save_data['PersonPrivilegeReq_setDT'] = $this->dbmodel->getFirstResultFromQuery('select dbo.tzGetDate()'); //текущие время и дата
                $save_data['Person_id'] = !empty($data['Person_id']) ? $data['Person_id'] : null;
                $ans_save_data['PersonPrivilegeReqAns_IsInReg'] = $this->getObjectIdByCode('YesNo', '0'); //при добавлении указываем признак включения в регистр = "нет"
                $ans_save_data['PersonPrivilegeReqStatus_id'] = 1; //1 - Новый

                if (!empty($data['MedStaffFact_id'])) {
                    $save_data['MedStaffFact_id'] = $data['MedStaffFact_id'];
                } else if (!empty($data['Lpu_id']) && !empty($data['MedPersonal_id'])) { //если не передан идентификатор рабочего места, пробуем определить его по косвенным данным
                    $query = "
						select
							msf.MedStaffFact_id as \"MedStaffFact_id\"
						from
							v_MedStaffFact msf
						where
							msf.Lpu_id = :Lpu_id and
							msf.MedPersonal_id = :MedPersonal_id and 
							(:LpuSection_id is null or msf.LpuSection_id = :LpuSection_id) and
							(:LpuUnit_id is null or msf.LpuUnit_id = :LpuUnit_id) and
							(:Post_id is null or msf.Post_id = :Post_id)
						order by
							msf.MedStaffFact_id;
					";
                    $msf_data = $this->getFirstRowFromQuery($query, array(
                        'Lpu_id' => $data['Lpu_id'],
                        'MedPersonal_id' => $data['MedPersonal_id'],
                        'LpuSection_id' => !empty($data['LpuSection_id']) ? $data['LpuSection_id'] : null,
                        'LpuUnit_id' => !empty($data['LpuUnit_id']) ? $data['LpuUnit_id'] : null,
                        'Post_id' => !empty($data['PostMed_id']) ? $data['PostMed_id'] : null
                    ));
                    if (!empty($msf_data['MedStaffFact_id'])) {
                        $save_data['MedStaffFact_id'] = $msf_data['MedStaffFact_id'];
                    }
                }

                if (empty($save_data['MedStaffFact_id'])) {
                    throw new Exception('Не удалось определить рабочее место врача, сохранение прервано');
                }
            }
            if (!empty($data['send_to_expertise'])) { //если указан признак отправки на экспертизу
                $ans_save_data['PersonPrivilegeReqStatus_id'] = 2; //2 - На рассмотрении
            }

            //сохранение данных запроса
            $save_result = $this->saveObject('PersonPrivilegeReq', $save_data);
            if (!empty($save_result['PersonPrivilegeReq_id'])) {
                $result['PersonPrivilegeReq_id'] = $save_result['PersonPrivilegeReq_id'];
                $ans_save_data['PersonPrivilegeReq_id'] = $save_result['PersonPrivilegeReq_id'];
            } else {
                throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных запроса произошла ошибка");
            }

            //поиск существующего идентификатора ответа
            $query = "
				select
					ppra.PersonPrivilegeReqAns_id as \"PersonPrivilegeReqAns_id\"
				from
					v_PersonPrivilegeReqAns ppra
				where
					ppra.PersonPrivilegeReq_id = :PersonPrivilegeReq_id
				order by
					ppra.PersonPrivilegeReqAns_id
				limit 1
			";
            $ans_data = $this->getFirstRowFromQuery($query, array(
                'PersonPrivilegeReq_id' => $result['PersonPrivilegeReq_id']
            ));
            $ans_save_data['PersonPrivilegeReqAns_id'] = !empty($ans_data['PersonPrivilegeReqAns_id']) ? $ans_data['PersonPrivilegeReqAns_id'] : null;

            //сохранение данных ответа
            $save_result = $this->saveObject('PersonPrivilegeReqAns', $ans_save_data);
            if (!empty($save_result['PersonPrivilegeReqAns_id'])) {
                $result['PersonPrivilegeReqAns_id'] = $save_result['PersonPrivilegeReqAns_id'];
            } else {
                throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных ответа произошла ошибка");
            }

            //если установлен соответствующий флаг, удаляем документ
            if ($delete_document) {
                $delete_result = $this->deleteObject('DocumentPrivilege', array(
                    'DocumentPrivilege_id' => $data['DocumentPrivilege_id']
                ));
                if (!empty($delete_result['Error_Msg'])) {
                    throw new Exception($delete_result['Error_Msg']);
                }
            }

            $result['success'] = true;
            $this->commitTransaction();
        } catch (Exception $e) {
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
            $this->rollbackTransaction();
        }

        return $result;
    }

    /**
     * Сохранение запроса на включение в региональный регистр льготников
     */
    function savePersonPrivilegeReqPM($data) {
        $result = array();
        $msf_data = array();
        $session_data = getSessionParams();
        $need_save_privilege_in_doc = false; //необходимость сохранения ссылки льготу в документе о праве на льготу

        try {
            $this->beginTransaction();

            if (empty($data['PersonPrivilegeReq_id'])) { //в режиме постмодерации почти все данные сохраняются только при добавлении запроса
                //получение данных о типе льготы
                $query = "
					select
						PrivilegeType_Name as \"PrivilegeType_Name\"
					from
						v_PrivilegeType
					where
						PrivilegeType_id = :PrivilegeType_id;
				";
                $privilege_type_data = $this->getFirstRowFromQuery($query, array(
                    'PrivilegeType_id' => $data['PrivilegeType_id']
                ));
                if (empty($privilege_type_data['PrivilegeType_Name'])) {
                    throw new Exception('Не удалось получить данные о типе льготы');
                }

                //проверка на наличие открытого запроса с такой же льготой
                $query = "
					select
						count(ppr.PersonPrivilegeReq_id) as cnt
					from
						v_PersonPrivilegeReq ppr
						left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
					where
						ppr.PersonPrivilegeReq_id <> coalesce(:PersonPrivilegeReq_id::bigint, 0) and
						ppr.Person_id = :Person_id and
						ppr.PrivilegeType_id = :PrivilegeType_id and 
						ppra.PersonPrivilegeReqStatus_id in (1, 2); -- 1 - Новый; 2 - На рассмотрении
				";
                $check_data = $this->getFirstRowFromQuery($query, array(
                    'PersonPrivilegeReq_id' => $data['PersonPrivilegeReq_id'],
                    'Person_id' => $data['Person_id'],
                    'PrivilegeType_id' => $data['PrivilegeType_id']
                ));
                if (!empty($check_data['cnt'])) {
                    throw new Exception("Создание запроса по льготе «{$privilege_type_data['PrivilegeType_Name']}» невозможно, так как уже подан запрос на включение в регистр по этой льготе. Проверьте данные о результатах запроса");
                }

                //поиск действующей льготы
                $check_result = $this->CheckPersonPrivilege(array(
                    'Person_id' => $data['Person_id'],
                    'PrivilegeType_id' => $data['PrivilegeType_id'],
                    'Privilege_begDate' => $data['DocumentPrivilege_begDate']
                ));
                if (is_array($check_result) && $check_result[0]['Privilege_Count'] > 0 && $check_result[0]['PersonPrivilege_id'] > 0) {
                    $data['PersonPrivilege_id'] = $check_result[0]['PersonPrivilege_id'];
                }
                
                if($this->getRegionNick() == 'msk' && !empty($data['ReceptFinance_id']) ){
					$data['Privilege_begDate'] = $data['PersonPrivilegeReq_begDT'];
					if($data['ReceptFinance_id'] == 1){
						$res = $this->CheckPersonHaveActiveRegionalPrivilege($data);
						if ( (is_array($res)) && (count($res) > 0) ) {
							if ( $res[0]['Privilege_Count'] > 0 ) {
								$resCloseRegionalPrivileges = $this->closeRegionalActivePrivilegesForPerson($data);
								if(!empty($resCloseRegionalPrivileges['Error_Msg'])){
									throw new Exception($resCloseRegionalPrivileges['Error_Msg']);
								}
							}
						}
					}elseif( $data['ReceptFinance_id'] == 2 && !isSuperAdmin() && (!haveARMType('spec_mz') || !haveARMType('minzdravdlo')) ) {
						$res = $this->CheckPersonHaveActiveFederalPrivilege(array(
							'Privilege_begDate' => $data['PersonPrivilegeReq_begDT'],
							'Person_id' => $data['Person_id']
						));
						if ( (is_array($res)) && (count($res) > 0) ) {
							if ( $res[0]['Privilege_Count'] > 0 ) {
								throw new Exception('У пользователя отсутствуют права на выполнение операции.');
							}
						}
					}
				}

                //сохранение документа о праве на льготу
                if (!empty($data['DocumentPrivilegeType_id'])) {
                    $save_data = array(
                        'DocumentPrivilege_id' => !empty($data['DocumentPrivilege_id']) ? $data['DocumentPrivilege_id'] : null,
                        'DocumentPrivilegeType_id' => !empty($data['DocumentPrivilegeType_id']) ? $data['DocumentPrivilegeType_id'] : null,
                        'DocumentPrivilege_Ser' => !empty($data['DocumentPrivilege_Ser']) ? $data['DocumentPrivilege_Ser'] : null,
                        'DocumentPrivilege_Num' => !empty($data['DocumentPrivilege_Num']) ? $data['DocumentPrivilege_Num'] : null,
                        'DocumentPrivilege_begDate' => !empty($data['DocumentPrivilege_begDate']) ? $data['DocumentPrivilege_begDate'] : null,
                        'DocumentPrivilege_Org' => !empty($data['DocumentPrivilege_Org']) ? $data['DocumentPrivilege_Org'] : null,
                        'PersonPrivilege_id' => !empty($data['PersonPrivilege_id']) ? $data['PersonPrivilege_id'] : null
                    );
                    $save_result = $this->saveObject('DocumentPrivilege', $save_data);
                    if (!empty($save_result['DocumentPrivilege_id'])) {
                        $data['DocumentPrivilege_id'] = $save_result['DocumentPrivilege_id'];
                    } else {
                        throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных произошла ошибка");
                    }

                    $need_save_privilege_in_doc = empty($data['PersonPrivilege_id']); //если ссылка на льготу не известна на момент сохранения документа, значит нужно будет сохранить её позже
                }

				//сохранение данных о смене фамилии
				$save_result = $this->savePersonSurNameAtBirth($data);
				if (!empty($save_result['Error_Msg'])) {
					throw new Exception($save_result['Error_Msg']);
				}

                //сохранение данных запроса на включение в Региональный регистр
                $save_data = array(
                    'PersonPrivilegeReq_id' => !empty($data['PersonPrivilegeReq_id']) ? $data['PersonPrivilegeReq_id'] : null,
                    'PrivilegeType_id' => !empty($data['PrivilegeType_id']) ? $data['PrivilegeType_id'] : null,
                    'Diag_id' => !empty($data['Diag_id']) ? $data['Diag_id'] : null,
                    'PersonPrivilegeReq_begDT' => !empty($data['PersonPrivilegeReq_begDT']) ? $data['PersonPrivilegeReq_begDT'] : null,
                    'PersonPrivilegeReq_endDT' => !empty($data['PersonPrivilegeReq_endDT']) ? $data['PersonPrivilegeReq_endDT'] : null,
                    'DocumentPrivilege_id' => !empty($data['DocumentPrivilege_id']) ? $data['DocumentPrivilege_id'] : null
                );
                $ans_save_data = array(
                    'PersonPrivilegeReqAns_DeclCause' => !empty($data['PersonPrivilegeReqAns_DeclCause']) ? $data['PersonPrivilegeReqAns_DeclCause'] : null
                );

                $save_data['PersonPrivilegeReq_setDT'] = $this->dbmodel->getFirstResultFromQuery('select dbo.tzGetDate()'); //текущие время и дата
                $save_data['Person_id'] = !empty($data['Person_id']) ? $data['Person_id'] : null;
                $ans_save_data['PersonPrivilegeReqAns_IsInReg'] = $this->getObjectIdByCode('YesNo', '1'); //при добавлении в режиме постмодерации указываем признак включения в регистр = "да"
                $ans_save_data['PersonPrivilegeReqStatus_id'] = 1; //1 - Новый

                if (!empty($data['MedStaffFact_id'])) {
                    $save_data['MedStaffFact_id'] = $data['MedStaffFact_id'];
                } else if (!empty($data['Lpu_id']) && !empty($data['MedPersonal_id'])) { //если не передан идентификатор рабочего места, пробуем определить его по косвенным данным
                    $query = "
						select
							msf.MedStaffFact_id as \"MedStaffFact_id\",
							msf.Lpu_id as \"Lpu_id\"
						from
							v_MedStaffFact msf
						where
							msf.Lpu_id = :Lpu_id and
							msf.MedPersonal_id = :MedPersonal_id and 
							(:LpuSection_id is null or msf.LpuSection_id = :LpuSection_id) and
							(:LpuUnit_id is null or msf.LpuUnit_id = :LpuUnit_id) and
							(:Post_id is null or msf.Post_id = :Post_id)
						order by
							msf.MedStaffFact_id;
					";
                    $msf_data = $this->getFirstRowFromQuery($query, array(
                        'Lpu_id' => $data['Lpu_id'],
                        'MedPersonal_id' => $data['MedPersonal_id'],
                        'LpuSection_id' => !empty($data['LpuSection_id']) ? $data['LpuSection_id'] : null,
                        'LpuUnit_id' => !empty($data['LpuUnit_id']) ? $data['LpuUnit_id'] : null,
                        'Post_id' => !empty($data['PostMed_id']) ? $data['PostMed_id'] : null
                    ));
                    if (!empty($msf_data['MedStaffFact_id'])) {
                        $save_data['MedStaffFact_id'] = $msf_data['MedStaffFact_id'];
                    }
                }

                if (empty($save_data['MedStaffFact_id'])) {
                    throw new Exception('Не удалось определить рабочее место врача, сохранение прервано');
                } else if (empty($msf_data) || empty($msf_data['Lpu_id'])) { //если идентификатор ЛПУ еще не известен, получаем его из места работы
                    $query = "
						select
							msf.MedStaffFact_id as \"MedStaffFact_id\",
							msf.Lpu_id as \"Lpu_id\"
						from
							v_MedStaffFact msf
						where
							msf.MedStaffFact_id = :MedStaffFact_id;
					";
                    $msf_data = $this->getFirstRowFromQuery($query, array(
                        'MedStaffFact_id' => $save_data['MedStaffFact_id']
                    ));
                }

                if (!empty($data['send_to_expertise'])) { //если указан признак отправки на экспертизу
                    $ans_save_data['PersonPrivilegeReqStatus_id'] = 2; //2 - На рассмотрении
                }

                //сохранение данных запроса
                $save_result = $this->saveObject('PersonPrivilegeReq', $save_data);
                if (!empty($save_result['PersonPrivilegeReq_id'])) {
                    $result['PersonPrivilegeReq_id'] = $save_result['PersonPrivilegeReq_id'];
                    $result['PrivilegeType_id'] = $data['PrivilegeType_id'];
                    $ans_save_data['PersonPrivilegeReq_id'] = $save_result['PersonPrivilegeReq_id'];
                } else {
                    throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных запроса произошла ошибка");
                }

                //поиск существующего идентификатора ответа
                $query = "
					select
						ppra.PersonPrivilegeReqAns_id as \"PersonPrivilegeReqAns_id\"
					from
						v_PersonPrivilegeReqAns ppra
					where
						ppra.PersonPrivilegeReq_id = :PersonPrivilegeReq_id
					order by
						ppra.PersonPrivilegeReqAns_id
					limit 1
				";
                $ans_data = $this->getFirstRowFromQuery($query, array(
                    'PersonPrivilegeReq_id' => $result['PersonPrivilegeReq_id']
                ));
                $ans_save_data['PersonPrivilegeReqAns_id'] = !empty($ans_data['PersonPrivilegeReqAns_id']) ? $ans_data['PersonPrivilegeReqAns_id'] : null;

                //сохранение данных ответа
                $save_result = $this->saveObject('PersonPrivilegeReqAns', $ans_save_data);
                if (!empty($save_result['PersonPrivilegeReqAns_id'])) {
                    $result['PersonPrivilegeReqAns_id'] = $save_result['PersonPrivilegeReqAns_id'];
                } else {
                    throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных ответа произошла ошибка");
                }

                if (!empty($data['PersonPrivilege_id'])) { //редактирвание льготы
                    //редактируем только дату окончания и диагноз
                    $privilege_data = array(
                        'PersonPrivilege_id' => $data['PersonPrivilege_id'],
                        'Diag_id' => $data['Diag_id'],
                        'PersonPrivilege_endDate' => !empty($data['PersonPrivilegeReq_endDT']) ? $data['PersonPrivilegeReq_endDT'] : null
                    );
                    $save_result = $this->saveObject('PersonPrivilege', $privilege_data);
                    if (empty($save_result['PersonPrivilege_id'])) {
                        throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных льготы произошла ошибка");
                    }
                } else { //добавление льготы
                    //формирование данных льготы
                    $privilege_data = array(
                        'pmUser_id' => $this->getPromedUserId(),
                        'Server_id' => $session_data['Server_id'],
                        'PersonPrivilege_id' => null,
                        'Lpu_id' => $msf_data['Lpu_id'],
                        'Person_id' => $data['Person_id'],
                        'PrivilegeType_id' => $data['PrivilegeType_id'],
                        'Diag_id' => $data['Diag_id'],
                        'Privilege_begDate' => !empty($data['PersonPrivilegeReq_begDT']) ? $data['PersonPrivilegeReq_begDT'] : null,
                        'Privilege_endDate' => !empty($data['PersonPrivilegeReq_endDT']) ? $data['PersonPrivilegeReq_endDT'] : null,
                        'PersonPrivilege_begDate' => !empty($data['PersonPrivilegeReq_begDT']) ? $data['PersonPrivilegeReq_begDT'] : null,
                        'PersonPrivilege_endDate' => !empty($data['PersonPrivilegeReq_endDT']) ? $data['PersonPrivilegeReq_endDT'] : null
                    );

                    //проверки добавляемой льготы
                    $check_result = $this->validatePrivilege($privilege_data);
                    if (!$this->isSuccessful($check_result)) {
                        throw new Exception(!empty($check_result[0]['Error_Msg']) ? $check_result[0]['Error_Msg'] : 'Ошибка при проверке включения в льготный регистр');
                    }

                    //непосредственно включение в регистр
                    $save_result = $this->savePersonPrivilege($privilege_data);
                    if (!$this->isSuccessful($save_result) || !is_array($save_result) || empty($save_result[0]['PersonPrivilege_id'])) {
                        throw new Exception(!empty($save_result[0]['Error_Msg']) ? $save_result[0]['Error_Msg'] : 'Ошибка при сохранении информации о включении в льготный регистр');
                    } else {
                        $data['PersonPrivilege_id'] = $save_result[0]['PersonPrivilege_id'];
                    }
                }

                //обновление данных документа, если установлен соответвтующий флаг и если идентификатор документа указан в данных запроса
                if ($need_save_privilege_in_doc && !empty($data['DocumentPrivilege_id'])) {
                    $save_result = $this->saveObject('DocumentPrivilege', array(
                        'DocumentPrivilege_id' => $data['DocumentPrivilege_id'],
                        'PersonPrivilege_id' => $data['PersonPrivilege_id']
                    ));
                    if (empty($save_result['DocumentPrivilege_id']) || !empty($save_result['Error_Msg'])) {
                        throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных документа произошла ошибка");
                    }
                }
            } else if (!empty($data['send_to_expertise'])) { //если указан признак отправки на экспертизу
                $ans_save_data = array(
                    'PersonPrivilegeReq_id' => $data['PersonPrivilegeReq_id'],
                    'PersonPrivilegeReqStatus_id' => 2 //2 - На рассмотрении
                );
                //получение данных запроса
                $query = "
					select
						ppr.PersonPrivilegeReq_id as \"PersonPrivilegeReq_id\",
						ppr.PrivilegeType_id as \"PrivilegeType_id\",
						ppra.PersonPrivilegeReqAns_id as \"PersonPrivilegeReqAns_id\"
					from
						v_PersonPrivilegeReq ppr
						left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
					where
						ppr.PersonPrivilegeReq_id = :PersonPrivilegeReq_id
					order by
						ppra.PersonPrivilegeReqAns_id
					limit 1
				";
                $req_data = $this->getFirstRowFromQuery($query, array(
                    'PersonPrivilegeReq_id' => $data['PersonPrivilegeReq_id']
                ));
                if (empty($req_data['PersonPrivilegeReq_id'])) {
                    throw new Exception("При получении данных запроса произошла ошибка");
                }

                $ans_save_data['PersonPrivilegeReqAns_id'] = !empty($req_data['PersonPrivilegeReqAns_id']) ? $req_data['PersonPrivilegeReqAns_id'] : null;

                //сохранение данных ответа
                $save_result = $this->saveObject('PersonPrivilegeReqAns', $ans_save_data);
                if (!empty($save_result['PersonPrivilegeReqAns_id'])) {
                    $result['PersonPrivilegeReq_id'] = $req_data['PersonPrivilegeReq_id'];
                    $result['PrivilegeType_id'] = $req_data['PrivilegeType_id'];
                    $result['PersonPrivilegeReqAns_id'] = $save_result['PersonPrivilegeReqAns_id'];
                } else {
                    throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных ответа произошла ошибка");
                }
            }

            $result['success'] = true;
            $this->commitTransaction();
        } catch (Exception $e) {
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
            $this->rollbackTransaction();
        }

        return $result;
    }

    /**
     * Сохранение результат экспертизы запроса на включение в региональный регистр льготников
     */
    function savePersonPrivilegeReqExpertise($data) {
        $result = array();
        $session_data = getSessionParams();

        try {
            $this->beginTransaction();

            //получение данных запроса
            $query = "
					select
						ppr.Person_id as \"Person_id\",
						ppr.PrivilegeType_id as \"PrivilegeType_id\",
						ppr.Diag_id as \"Diag_id\",
						to_char(ppr.PersonPrivilegeReq_begDT, 'dd.mm.yyyy') as \"PersonPrivilegeReq_begDT\",
						to_char(ppr.PersonPrivilegeReq_endDT, 'dd.mm.yyyy') as \"PersonPrivilegeReq_endDT\",
						ppr.DocumentPrivilege_id as \"DocumentPrivilege_id\",
						ppr.pmUser_insID as \"pmUser_id\",
						ppra.PersonPrivilegeReqAns_id as \"PersonPrivilegeReqAns_id\",
						ppra.PersonPrivilegeReqStatus_id as \"PersonPrivilegeReqStatus_id\",
						(
							coalesce(rtrim(ps.Person_Surname)||' ','')||
							coalesce(rtrim(ps.Person_FirName)||' ','')||
							coalesce(rtrim(ps.Person_SecName)||' ','')
						) as \"Person_Fio\",
						msf.Lpu_id as \"Lpu_id\"
					from
						v_PersonPrivilegeReq ppr
						left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
						left join v_PersonState ps on ps.Person_id = ppr.Person_id
						left join v_MedStaffFact msf on msf.MedStaffFact_id = ppr.MedStaffFact_id
					where
						ppr.PersonPrivilegeReq_id = :PersonPrivilegeReq_id;
				";
            $req_data = $this->getFirstRowFromQuery($query, array(
                'PersonPrivilegeReq_id' => $data['PersonPrivilegeReq_id']
            ));
            if (empty($req_data['pmUser_id'])) {
                throw new Exception("При получении данных запроса произошла ошибка");
            }

            //проверка статуса запроса
            if ($req_data['PersonPrivilegeReqStatus_id'] != 2) { //2 - На рассмотрении
                throw new Exception("Текущий статус запроса не предусматривает проведения экспертизы");
            }

            $notice = null;
            $save_data = array(
                'PersonPrivilegeReqAns_id' => $req_data['PersonPrivilegeReqAns_id'],
                'PersonPrivilegeReqStatus_id' => 3 //3 - Ответ получен
            );
            if ($data['action'] == 'insert') { //результат экспертизы - включение в регистр
                $save_data['PersonPrivilegeReqAns_IsInReg'] = $this->getObjectIdByCode('YesNo', '1'); //признак включения в регистр = "да"
                $notice = "Включен в регистр";

                //формирование данных льготы
                $privilege_data = array(
                    'pmUser_id' => $this->getPromedUserId(),
                    'Server_id' => $session_data['Server_id'],
                    'PersonPrivilege_id' => null,
                    'Lpu_id' => $req_data['Lpu_id'],
                    'Person_id' => $req_data['Person_id'],
                    'PrivilegeType_id' => $req_data['PrivilegeType_id'],
                    'Diag_id' => $req_data['Diag_id'],
                    'Privilege_begDate' => !empty($req_data['PersonPrivilegeReq_begDT']) ? DateTime::createFromFormat('d.m.Y', $req_data['PersonPrivilegeReq_begDT'])->format('Y-m-d') : null,
                    'Privilege_endDate' => !empty($req_data['PersonPrivilegeReq_endDT']) ? DateTime::createFromFormat('d.m.Y', $req_data['PersonPrivilegeReq_endDT'])->format('Y-m-d') : null,
                    'PersonPrivilege_begDate' => !empty($req_data['PersonPrivilegeReq_begDT']) ? DateTime::createFromFormat('d.m.Y', $req_data['PersonPrivilegeReq_begDT'])->format('Y-m-d') : null,
                    'PersonPrivilege_endDate' => !empty($req_data['PersonPrivilegeReq_endDT']) ? DateTime::createFromFormat('d.m.Y', $req_data['PersonPrivilegeReq_endDT'])->format('Y-m-d') : null
                );

                //проверка на наличие льготы
                $check_result = $this->CheckPersonPrivilege($privilege_data);
                if (is_array($check_result) && $check_result[0]['Privilege_Count'] > 0) {
                    throw new Exception('Включить пациента в программу невозможно, он уже включен');
                }

                //проверки добавляемой льготы
                $check_result = $this->validatePrivilege($privilege_data);
                if (!$this->isSuccessful($check_result)) {
                    throw new Exception(!empty($check_result[0]['Error_Msg']) ? $check_result[0]['Error_Msg'] : 'Ошибка при проверке включения в льготный регистр');
                }

                //непосредственно включение в регистр
                $save_result = $this->savePersonPrivilege($privilege_data);
                if (!$this->isSuccessful($save_result) || !is_array($save_result) || empty($save_result[0]['PersonPrivilege_id'])) {
                    throw new Exception(!empty($save_result[0]['Error_Msg']) ? $save_result[0]['Error_Msg'] : 'Ошибка при сохранении информации о включении в льготный регистр');
                } else {
                    $req_data['PersonPrivilege_id'] = $save_result[0]['PersonPrivilege_id'];
                }

                //обновление данных документа, если он указан в данных запроса
                if (!empty($req_data['DocumentPrivilege_id'])) {
                    $save_result = $this->saveObject('DocumentPrivilege', array(
                        'DocumentPrivilege_id' => $req_data['DocumentPrivilege_id'],
                        'PersonPrivilege_id' => $req_data['PersonPrivilege_id']
                    ));
                    if (empty($save_result['DocumentPrivilege_id']) || !empty($save_result['Error_Msg'])) {
                        throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных документа произошла ошибка");
                    }
                }
            }
            if ($data['action'] == 'reject') { //результат экспертизы - отказ
                $save_data['PersonPrivilegeReqAns_IsInReg'] = $this->getObjectIdByCode('YesNo', '0'); //признак включения в регистр = "нет"
                $save_data['PersonPrivilegeReqAns_DeclCause'] = !empty($data['PersonPrivilegeReqAns_DeclCause']) ? $data['PersonPrivilegeReqAns_DeclCause'] : null;
                $notice = "Отказано";
            }

            //обновление данных ответа на запрос
            $save_result = $this->saveObject('PersonPrivilegeReqAns', $save_data);
            if (!empty($save_result['PersonPrivilegeReqAns_id'])) {
                $result['PersonPrivilegeReq_id'] = $data['PersonPrivilegeReq_id'];
                $result['PersonPrivilegeReqAns_id'] = $save_result['PersonPrivilegeReqAns_id'];
            } else {
                throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных произошла ошибка");
            }

            //отправка уведомления
            if (!empty($notice)) {
                $header = "Результат рассмотрения запроса";
                $text = "{$req_data['Person_Fio']}\r\nРезультат: {$notice}";

                $send_result = $this->sendNotice($req_data['pmUser_id'], $header, $text);
                if (empty($send_result['Message_id'])) {
                    throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При отправке уведомления произошла ошибка");
                }
            }

            $result['success'] = true;
            $this->commitTransaction();
        } catch (Exception $e) {
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
            $this->rollbackTransaction();
        }

        return $result;
    }

    /**
     * Сохранение результат экспертизы запроса на включение в региональный регистр льготников (режим постмодерации)
     */
    function savePersonPrivilegeReqExpertisePM($data) {
        $result = array();

        try {
            $this->beginTransaction();

            //получение данных запроса
            $query = "
				select
					ppr.PersonPrivilegeReq_id as \"PersonPrivilegeReq_id\",
					ppr.Person_id as \"Person_id\",
					ppr.PrivilegeType_id as \"PrivilegeType_id\",
					ppr.Diag_id as \"Diag_id\",
					ppr.PersonPrivilegeReq_begDT as \"PersonPrivilegeReq_begDT\",
					ppr.PersonPrivilegeReq_endDT as \"PersonPrivilegeReq_endDT\",
					ppr.DocumentPrivilege_id as \"DocumentPrivilege_id\",
					ppr.pmUser_insID as \"pmUser_id\",
					ppra.PersonPrivilegeReqAns_id as \"PersonPrivilegeReqAns_id\",
					ppra.PersonPrivilegeReqStatus_id as \"PersonPrivilegeReqStatus_id\",
					dp.PersonPrivilege_id as \"PersonPrivilege_id\",
					(
						coalesce (rtrim(ps.Person_Surname)||' ','')||
						coalesce (rtrim(ps.Person_FirName)||' ','')||
						coalesce (rtrim(ps.Person_SecName)||' ','')
					) as \"Person_Fio\",
					msf.Lpu_id as \"Lpu_id\"
				from
					v_PersonPrivilegeReq ppr
					left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
					left join v_DocumentPrivilege dp on dp.DocumentPrivilege_id = ppr.DocumentPrivilege_id
					left join v_PersonState ps on ps.Person_id = ppr.Person_id
					left join v_MedStaffFact msf on msf.MedStaffFact_id = ppr.MedStaffFact_id
				where
					ppr.PersonPrivilegeReq_id = :PersonPrivilegeReq_id;
			";
            $req_data = $this->getFirstRowFromQuery($query, array(
                'PersonPrivilegeReq_id' => $data['PersonPrivilegeReq_id']
            ));
            if (empty($req_data['pmUser_id'])) {
                throw new Exception("При получении данных запроса произошла ошибка");
            }

            //проверка статуса запроса
            if ($req_data['PersonPrivilegeReqStatus_id'] != 2) { //2 - На рассмотрении
                throw new Exception("Текущий статус запроса не предусматривает проведения экспертизы");
            }

            $notice = null;
            $save_data = array(
                'PersonPrivilegeReqAns_id' => $req_data['PersonPrivilegeReqAns_id'],
                'PersonPrivilegeReqStatus_id' => 3 //3 - Ответ получен
            );
            if ($data['action'] == 'insert') { //результат экспертизы - включение в регистр
                $save_data['PersonPrivilegeReqAns_IsInReg'] = $this->getObjectIdByCode('YesNo', '1'); //признак включения в регистр = "да"
                $notice = "Включен в регистр";
            }
            if ($data['action'] == 'reject') { //результат экспертизы - отказ
                $save_data['PersonPrivilegeReqAns_IsInReg'] = $this->getObjectIdByCode('YesNo', '0'); //признак включения в регистр = "нет"
                $save_data['PersonPrivilegeReqAns_DeclCause'] = !empty($data['PersonPrivilegeReqAns_DeclCause']) ? $data['PersonPrivilegeReqAns_DeclCause'] : null;
                $notice = "Отказано";
            }

            //обновление данных ответа на запрос
            $save_result = $this->saveObject('PersonPrivilegeReqAns', $save_data);
            if (!empty($save_result['PersonPrivilegeReqAns_id'])) {
                $result['PersonPrivilegeReq_id'] = $data['PersonPrivilegeReq_id'];
                $result['PersonPrivilegeReqAns_id'] = $save_result['PersonPrivilegeReqAns_id'];
            } else {
                throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных произошла ошибка");
            }

            //отправка уведомления
            if (!empty($notice)) {
                $header = "Результат рассмотрения запроса";
                $text = "{$req_data['Person_Fio']}\r\nРезультат: {$notice}";

                $send_result = $this->sendNotice($req_data['pmUser_id'], $header, $text);
                if (empty($send_result['Message_id'])) {
                    throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При отправке уведомления произошла ошибка");
                }
            }

            if ($data['action'] == 'reject') {
                $save_result = $this->closePersonPrivilegeByPersonPrivilegeReqPM(array(
                    'PersonPrivilegeReq_id' => $data['PersonPrivilegeReq_id'],
                    'PersonPrivilege_endDate' => !empty($data['PersonPrivilegeReq_endDT']) ? $data['PersonPrivilegeReq_endDT'] : null
                ));
                if (!empty($save_result['Error_Msg'])) {
                    throw new Exception($save_result['Error_Msg']);
                }
            }

            $result['success'] = true;
            $this->commitTransaction();
        } catch (Exception $e) {
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
            $this->rollbackTransaction();
        }

        return $result;
    }

	/**
	 * Сохранение данных о фамилии при рождении
	 */
	function savePersonSurNameAtBirth($data) {
		$result = [];

		if (!empty($data['Person_id']) && $data['PersonSurNameAtBirth_SurName']) {
			$need_save = true;
			$save_data = [
				'Person_id' => $data['Person_id'],
				'PersonSurNameAtBirth_SurName' => $data['PersonSurNameAtBirth_SurName']
			];

			//поиск существующих данных
			$query = "
				select
					psnab.PersonSurNameAtBirth_id as \"PersonSurNameAtBirth_id\",
					psnab.PersonSurNameAtBirth_SurName as \"PersonSurNameAtBirth_SurName\"
				from
					v_PersonSurNameAtBirth psnab
				where
					psnab.Person_id = :Person_id
				order by
					psnab.PersonSurNameAtBirth_id
				limit 1
			";
			$psnab_data = $this->getFirstRowFromQuery($query, [
				'Person_id' => $data['Person_id']
			]);
			if (is_array($psnab_data) && !empty($psnab_data['PersonSurNameAtBirth_id'])) {
				if ($psnab_data['PersonSurNameAtBirth_SurName'] != $data['PersonSurNameAtBirth_SurName']) {
					//проверка возможности редактирования фамилии при рождении
					if (!isSuperAdmin() && !havingGroup('LpuAdmin')) {
						//поиск одобренных запросов на включение в регистр
						$query = "
							select
								count(ppr.PersonPrivilegeReq_id) as \"cnt\"
							from
								v_PersonPrivilegeReq ppr
								left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
							where
								ppr.Person_id = :Person_id and
								PersonPrivilegeReqStatus_id = 3 and -- 3 - ответ получен
								PersonPrivilegeReqAns_IsInReg = 2; -- признак включения в регистр
						";
						$req_data = $this->getFirstRowFromQuery($query, [
							'Person_id' => $data['Person_id']
						]);
						if (is_array($req_data) && !empty($req_data['cnt'])) { //если запрос найден, сохранять данные нельзя
							$result['Error_Msg'] = "Смена фамилии не доступна. В системе есть одобренные запросы на включение в программу для данного пациента.";
							$need_save = false;
						}
					}
					$save_data['PersonSurNameAtBirth_id'] = $psnab_data['PersonSurNameAtBirth_id'];
				} else {
					$need_save = false;
				}
			}

			//сохранение данных
			if ($need_save) {
				$save_result = $this->saveObject('PersonSurNameAtBirth', $save_data);
				if (empty($save_result['PersonSurNameAtBirth_id']) || !empty($save_result['Error_Msg'])) {
					$result['Error_Msg'] = !empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "Ошибка при сохранении данных о смене фамилии";
				}
			}
		}

		return $result;
	}

    /**
     * Удаление льготы и рецептов связанных с запросом о включении вльготные регистры (для режима постмодерации)
     * наличие транзакции предполагается во внешних функциях
     */
    function closePersonPrivilegeByPersonPrivilegeReqPM($data) {
        $result = array();

        try {
            //получение данных запроса
            $query = "
				select
					ppr.PersonPrivilegeReq_id as \"PersonPrivilegeReq_id\",
					ppr.Person_id as \"Person_id\",
					ppr.PrivilegeType_id as \"PrivilegeType_id\",
					dp.PersonPrivilege_id as \"PersonPrivilege_id\"
				from
					v_PersonPrivilegeReq ppr
					left join v_DocumentPrivilege dp on dp.DocumentPrivilege_id = ppr.DocumentPrivilege_id
				where
					ppr.PersonPrivilegeReq_id = :PersonPrivilegeReq_id;
			";
            $req_data = $this->getFirstRowFromQuery($query, array(
                'PersonPrivilegeReq_id' => $data['PersonPrivilegeReq_id']
            ));
            if (empty($req_data['PersonPrivilegeReq_id'])) {
                throw new Exception("При получении данных запроса произошла ошибка");
            }

            //поиск льготы в случае, если её идентификатор не был сохранен в документе о праве на льготу
            if (empty($req_data['PersonPrivilege_id'])) {
                $query = "
					select
						pp.PersonPrivilege_id as \"PersonPrivilege_id\"
					from
						v_PersonPrivilegeReq ppr
						left join v_PersonPrivilege pp on
							pp.Person_id = ppr.Person_id and 
							pp.PrivilegeType_id = ppr.PrivilegeType_id and
							coalesce (pp.PersonPrivilege_begDate, '') = coalesce (ppr.PersonPrivilegeReq_begDT, '') and
							coalesce (pp.PersonPrivilege_endDate, '') = coalesce (ppr.PersonPrivilegeReq_endDT, '')
					where
						ppr.PersonPrivilegeReq_id = :PersonPrivilegeReq_id
					order by
						pp.PersonPrivilege_id desc
					limit 1
				";
                $priv_data = $this->getFirstRowFromQuery($query, array(
                    'PersonPrivilegeReq_id' => $req_data['PersonPrivilegeReq_id']
                ));
                if (!empty($priv_data['PersonPrivilege_id'])) {
                    $req_data['PersonPrivilege_id'] = $priv_data['PersonPrivilege_id'];
                }
            }

            //удалять данные о льготе нужно только если она есть
            if (!empty($req_data['PersonPrivilege_id'])) {
                $this->load->model('Dlo_EvnRecept_model', 'Dlo_EvnRecept_model');
                $this->Dlo_EvnRecept_model->isAllowTransaction = false;

                //получение идентификаторов причин для удаления
                $query = "
                        select 
                            -- 5 - Льгота не прошла постмодерацию
                            (select PrivilegeCloseType_id from v_PrivilegeCloseType where PrivilegeCloseType_Code = '5' order by PrivilegeCloseType_id limit 1) as \"PrivilegeCloseType_id\", 
                            -- postmoderationreject - Льгота не прошла постмодерацию
                            (select ReceptRemoveCauseType_id from v_ReceptRemoveCauseType where ReceptRemoveCauseType_SysNick = 'postmoderationreject' order by ReceptRemoveCauseType_id limit 1) as \"ReceptRemoveCauseType_id\"
				";
                $close_type_data = $this->getFirstRowFromQuery($query);
                if (empty($close_type_data['PrivilegeCloseType_id']) || empty($close_type_data['ReceptRemoveCauseType_id'])) {
                    throw new Exception("Не удалось получить идентификторы причин закрытия");
                }

                //получение списка выписаных но еще не обеспеченных рецептов
                $query = "
					select distinct
						er.EvnRecept_id as \"EvnRecept_id\"
					from
						v_EvnRecept er
					where
						er.Person_id = :Person_id and
						er.PersonPrivilege_id = :PersonPrivilege_id and 
						er.EvnRecept_otpDT is null and
						er.ReceptRemoveCauseType_id is null
				";
                $recept_list = $this->queryList($query, array(
                    'Person_id' => $req_data['Person_id'],
                    'PersonPrivilege_id' => $req_data['PersonPrivilege_id']
                ));

                //удаление рецептов
                foreach($recept_list as $recept_id) {
                    $delete_result = $this->Dlo_EvnRecept_model->deleteEvnRecept(array(
                        'EvnRecept_id' => $recept_id,
                        'DeleteType' => 1,
                        'ReceptRemoveCauseType_id' => $close_type_data['ReceptRemoveCauseType_id'],
                        'pmUser_id' => $this->getPromedUserId()
                    ));
                    if (!empty($delete_result['Error_Msg'])) {
                        throw new Exception($delete_result['Error_Msg']);
                    }
                }

                //закрытие льготы
                $save_result = $this->saveObject('PersonPrivilege', array(
                    'PersonPrivilege_id' => $req_data['PersonPrivilege_id'],
                    'PersonPrivilege_endDate' => !empty($data['PersonPrivilegeReq_endDT']) ? $data['PersonPrivilegeReq_endDT'] : date('Y-m-d'),
                    'PrivilegeCloseType_id' => $close_type_data['PrivilegeCloseType_id']
                ));
                if (empty($save_result['PersonPrivilege_id']) || !empty($save_result['Error_Msg'])) {
                    throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "Ошибка при закрытии льготы");
                }
            }

            $result['success'] = true;
        } catch (Exception $e) {
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Удаление запроса на включение в региональный регистр льготников
     */
    function deletePersonPrivilegeReq($data) {
        $this->load->model("Options_model", "opmodel");
        $options = $this->opmodel->getOptionsGlobals($data);
        $result = array();

        try {
            $this->beginTransaction();

            //полученеи данных запроса
            $query = "
				select
					ppra.PersonPrivilegeReqAns_id as \"PersonPrivilegeReqAns_id\",
					ppra.PersonPrivilegeReqStatus_id as \"PersonPrivilegeReqStatus_id\",
					ppr.pmUser_insID as \"pmUser_id\",
					msf.Lpu_id as \"Lpu_id\"
				from
					v_PersonPrivilegeReq ppr
					left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
					left join v_MedStaffFact msf on msf.MedStaffFact_id = ppr.MedStaffFact_id
				where
					ppr.PersonPrivilegeReq_id = :PersonPrivilegeReq_id;
			";
            $req_data = $this->getFirstRowFromQuery($query, array(
                'PersonPrivilegeReq_id' => $data['id']
            ));
            if (empty($req_data['pmUser_id'])) {
                throw new Exception("При получении данных запроса произошла ошибка");
            }

            //проверка статуса записи
            if ($req_data['PersonPrivilegeReqStatus_id'] != 1) { //1 - Новый
                throw new Exception("Текущий статус запроса не предусматривает удаления");
            }

            //проверка наличия прав для удаления записи
            $delete_enabled = false;
            if ($req_data['pmUser_id'] == $this->getPromedUserId()) { //если пользователь является тем, кто подал запрос
                $delete_enabled = true;
            } else if (isSuperAdmin()) { //если пользователь является администратором ЦОД
                $delete_enabled = true;
            } else if (havingGroup(array('LpuAdmin')) && $req_data['Lpu_id'] == $this->sessionParams['lpu_id']) { //если пользователь входит группу "Администратор МО" и МО пользователя совпадает с МО подачи
                $delete_enabled = true;
            }
            if (!$delete_enabled) {
                throw new Exception("Удаление невозможно, так как у Вас нет прав на удаление этой записи");
            }

            //если активен режим постмодерации, то при удалении запроса нужно удалять и льготу/рецепты

            if ($options['globals']['person_privilege_add_request_postmoderation'] == 1) { //активен режим постмодерации
                $save_result = $this->closePersonPrivilegeByPersonPrivilegeReqPM(array(
                    'PersonPrivilegeReq_id' => $data['PersonPrivilegeReq_id'],
                    'PersonPrivilege_endDate' => !empty($data['PersonPrivilegeReq_endDT']) ? $data['PersonPrivilegeReq_endDT'] : null
                ));
                if (!empty($save_result['Error_Msg'])) {
                    throw new Exception($save_result['Error_Msg']);
                }
            }

            //удаление данных ответа
            $delete_result = $this->deleteObject('PersonPrivilegeReqAns', array(
                'PersonPrivilegeReqAns_id' => $req_data['PersonPrivilegeReqAns_id']
            ));
            if (!empty($delete_result['Error_Msg'])) {
                throw new Exception($delete_result['Error_Msg']);
            }

            //удаление основных данных запроса
            $delete_result = $this->deleteObject('PersonPrivilegeReq', array(
                'PersonPrivilegeReq_id' => $data['id']
            ));
            if (!empty($delete_result['Error_Msg'])) {
                throw new Exception($delete_result['Error_Msg']);
            }

            $result['success'] = true;
            $this->commitTransaction();
        } catch (Exception $e) {
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
            $this->rollbackTransaction();
        }

        return $result;
    }

    /**
     * Получение данных запроса на включение в региональный регистр льготников
     */
    function loadPersonPrivilegeReq($data) {
        $query = "
			select
				ppr.PersonPrivilegeReq_id as \"PersonPrivilegeReq_id\",
				to_char(ppr.PersonPrivilegeReq_setDT, 'dd.mm.yyyy HH24:MI') as \"PersonPrivilegeReq_setDT\",
				ppr.MedStaffFact_id as \"MedStaffFact_id\",
				ppr.Person_id as \"Person_id\",
				ppr.PrivilegeType_id as \"PrivilegeType_id\",
				ppr.Diag_id as \"Diag_id\",
				to_char(ppr.PersonPrivilegeReq_begDT, 'dd.mm.yyyy') as \"PersonPrivilegeReq_begDT\",
				to_char(ppr.PersonPrivilegeReq_endDT, 'dd.mm.yyyy') as \"PersonPrivilegeReq_endDT\",
				ppr.DocumentPrivilege_id as \"DocumentPrivilege_id\",
				ppra.PersonPrivilegeReqStatus_id as \"PersonPrivilegeReqStatus_id\",
				ppra.PersonPrivilegeReqAns_IsInReg as \"PersonPrivilegeReqAns_IsInReg\",
				ppra.PersonPrivilegeReqAns_DeclCause as \"PersonPrivilegeReqAns_DeclCause\",
				dp.DocumentPrivilegeType_id as \"DocumentPrivilegeType_id\",
				dp.DocumentPrivilege_Ser as \"DocumentPrivilege_Ser\",
				dp.DocumentPrivilege_Num as \"DocumentPrivilege_Num\",
				to_char(dp.DocumentPrivilege_begDate, 'dd.mm.yyyy') as \"DocumentPrivilege_begDate\",
				dp.DocumentPrivilege_Org as \"DocumentPrivilege_Org\",
				msf.Person_Fio as \"Msf_Person_Fio\",
				l.Lpu_Nick as \"Msf_Lpu_Nick\",
				pm.PostMed_Name as \"Msf_PostMed_Name\",
				(
					coalesce (msf.Person_Fio, '') ||
					coalesce (' ' || pm.PostMed_Name, '')||
					coalesce (' ' || ls.LpuSection_Name, '')
				) as \"Msf_FullName\",
				dpt.DocumentPrivilegeType_Name as \"DocumentPrivilegeType_Name\",
				pt.PrivilegeType_Name as \"PrivilegeType_Name\",
				(case when psnab.Person_id is not null then 1 else 0 end) as \"SurName_isChanged\",
				psnab.PersonSurNameAtBirth_SurName as \"PersonSurNameAtBirth_SurName\",
				ar.ApprovedReq_Cnt as \"ApprovedReq_Cnt\"
			from
				v_PersonPrivilegeReq ppr
				left join v_PersonPrivilegeReqAns ppra  on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
				left join v_DocumentPrivilege dp  on dp.DocumentPrivilege_id = ppr.DocumentPrivilege_id
				left join v_DocumentPrivilegeType dpt  on dpt.DocumentPrivilegeType_id = dp.DocumentPrivilegeType_id
				left join v_PrivilegeType pt  on pt.PrivilegeType_id = ppr.PrivilegeType_id
				left join v_MedStaffFact msf  on msf.MedStaffFact_id = ppr.MedStaffFact_id
				left join v_Lpu l  on l.Lpu_id = msf.Lpu_id
				left join v_LpuSection ls  on ls.LpuSection_id = msf.LpuSection_id
				left join v_PostMed pm  on pm.PostMed_id = msf.Post_id
				left join v_PersonSurNameAtBirth psnab on psnab.Person_id = ppr.Person_id
				left join lateral (
					select
						count(ppr.PersonPrivilegeReq_id) as ApprovedReq_Cnt
					from
						v_PersonPrivilegeReq ppr
						left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
					where
						ppr.Person_id = psnab.Person_id and
						PersonPrivilegeReqStatus_id = 3 and -- 3 - ответ получен
						PersonPrivilegeReqAns_IsInReg = 2 -- признак включения в регистр
				) ar on true
			where
				ppr.PersonPrivilegeReq_id = :PersonPrivilegeReq_id;
		";
        $result = $this->queryResult($query, $data);

        return $result;
    }

    /**
     * Получение списка запросов на включение в региональный регистр льготников
     */
    function loadPersonPrivilegeReqList($data) {
        $where = array();
        $params = array();

        if (!empty($data['begDate'])) {
            $where[] = 'set_dt.val >= CAST(:begDate as date)';
            $params['begDate'] = $data['begDate'];
        }
        if (!empty($data['endDate'])) {
            $where[] = 'set_dt.val <= CAST(:endDate as date)';
            $params['endDate'] = $data['endDate'];
        }
        if (!empty($data['Lpu_id'])) {
            $where[] = 'l.Lpu_id = :Lpu_id';
            $params['Lpu_id'] = $data['Lpu_id'];
        }
        if (!empty($data['Person_SurName'])) {
            $where[] = 'ps.Person_SurName ilike :Person_SurName';
            $params['Person_SurName'] = '%'.$data['Person_SurName'].'%';
        }
        if (!empty($data['Person_FirName'])) {
            $where[] = 'ps.Person_FirName ilike :Person_FirName';
            $params['Person_FirName'] = '%'.$data['Person_FirName'].'%';
        }
        if (!empty($data['Person_SecName'])) {
            $where[] = 'ps.Person_SecName ilike :Person_SecName';
            $params['Person_SecName'] = '%'.$data['Person_SecName'].'%';
        }
        if (!empty($data['Person_BirthDay_Range'])) {
            if (!empty($data['Person_BirthDay_Range'][0])) {
                $where[] = 'ps.Person_BirthDay >= CAST(:Person_BirthDay_begDate as date)';
                $params['Person_BirthDay_begDate'] = $data['Person_BirthDay_Range'][0];
            }
            if (!empty($data['Person_BirthDay_Range'][1])) {
                $where[] = 'ps.Person_BirthDay <= CAST(:Person_BirthDay_endDate as date)';
                $params['Person_BirthDay_endDate'] = $data['Person_BirthDay_Range'][1];
            }
        }
        if (!empty($data['PrivilegeType_id'])) {
            $where[] = 'ppr.PrivilegeType_id = :PrivilegeType_id';
            $params['PrivilegeType_id'] = $data['PrivilegeType_id'];
        }
        if (!empty($data['PersonPrivilegeReqStatus_id'])) {
            $where[] = 'ppra.PersonPrivilegeReqStatus_id = :PersonPrivilegeReqStatus_id';
            $params['PersonPrivilegeReqStatus_id'] = $data['PersonPrivilegeReqStatus_id'];
        }
        if (!empty($data['Result_Type'])) {
            $where[] = 'pprs.PersonPrivilegeReqStatus_id = 3';
            $where[] = 'in_reg.YesNo_Code = :YesNo_Code';
            $params['YesNo_Code'] = $data['Result_Type'] == 'insert' ? '1' : '0';
        }
        if (!empty($data['exclude_new_requests'])) {
            $where[] = 'pprs.PersonPrivilegeReqStatus_id <> 1'; //1 - Новый
        }


        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					-- where
					{$where_clause}
					-- end where
			";
        }

        $query = "
		    select
		    	-- select
				ppr.PersonPrivilegeReq_id as \"PersonPrivilegeReq_id\",
				ppra.PersonPrivilegeReqAns_id as \"PersonPrivilegeReqAns_id\",
				ppra.PersonPrivilegeReqStatus_id as \"PersonPrivilegeReqStatus_id\",
				to_char(ppr.PersonPrivilegeReq_setDT, 'dd.mm.yyyy HH24:MI') as \"PersonPrivilegeReq_setDT\",				
				(
					coalesce (rtrim(ps.Person_SurName)||' ','')||
					coalesce (rtrim(ps.Person_FirName)||' ','')||
					coalesce (rtrim(ps.Person_SecName)||' ','')||
					coalesce (to_char(ps.Person_Birthday, 'dd.mm.yyyy'), '')
				) as \"Person_FullName\",
				pt.PrivilegeType_Name as \"PrivilegeType_Name\",
				'' as \"MedStaffFact_FullName\",
				pprs.PersonPrivilegeReqStatus_Name as \"PersonPrivilegeReqStatus_Name\",
				(case
					when
						ppra.PersonPrivilegeReqStatus_id = 3 and in_reg.YesNo_Code = 0
					then
						(
							'Отказано' || coalesce (': '||ppra.PersonPrivilegeReqAns_DeclCause, '')||' '||
							to_char(ppra.PersonPrivilegeReqAns_updDT, 'dd.mm.yyyy HH24:MI')	
						)
					when
						ppra.PersonPrivilegeReqStatus_id = 3 and in_reg.YesNo_Code = 1
					then
						(
							'Включен ' || to_char(ppra.PersonPrivilegeReqAns_updDT, 'dd.mm.yyyy HH24:MI')	
						)
					else
						''
				end) as \"Result_Data\",
				(
					coalesce (l.Lpu_Nick ||', ', '') ||
					coalesce (pm.PostMed_Name || ', ', '') ||
					coalesce (msf.Person_Fio, '')
				) as \"MedStaffFact_FullName\",
				(
					case when ppr.PersonPrivilegeReq_IsSigned = 2 or (ppr.pmUser_signID is not null and ppr.PersonPrivilegeReq_signDT is not null) 
						then 2 else 
						case when ppr.PersonPrivilegeReq_IsSigned = 2 AND (ppr.pmUser_signID is not null and ppr.PersonPrivilegeReq_signDT >= ppr.PersonPrivilegeReq_setDT) 
							then 1
							else null
						end
					end
				) as \"PersonPrivilegeReq_IsSigned\",
				to_char(ppr.PersonPrivilegeReq_signDT, 'dd.mm.yyyy') as \"PersonPrivilegeReq_signDT\",
				puc.pmUser_Name as \"PPRSignPmUser_Name\",
				(
					case when ppra.PersonPrivilegeReqAns_IsSigned = 2 or (ppra.pmUser_signID is not null and ppra.PersonPrivilegeReqAns_signDT is not null) 
						then 2 else 
						case when ppra.PersonPrivilegeReqAns_IsSigned = 2 AND (ppra.pmUser_signID is not null and ppra.PersonPrivilegeReqAns_signDT >= ppra.PersonPrivilegeReqAns_updDT) 
							then 1
							else null
						end
					end
				) as \"PersonPrivilegeReqAns_IsSigned\",
				to_char(ppra.PersonPrivilegeReqAns_signDT, 'dd.mm.yyyy') as \"PersonPrivilegeReqAnssignDT\",
				puca.pmUser_Name as \"PPRASignPmUser_Name\"
				-- end select
			from
				-- from
				v_PersonPrivilegeReq ppr
				left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
				left join v_PersonPrivilegeReqStatus pprs on pprs.PersonPrivilegeReqStatus_id = ppra.PersonPrivilegeReqStatus_id
				left join v_PrivilegeType pt on pt.PrivilegeType_id = ppr.PrivilegeType_id
				left join v_PersonState ps on ps.Person_id = ppr.Person_id 
				left join v_YesNo in_reg on in_reg.YesNo_id = ppra.PersonPrivilegeReqAns_IsInReg
				left join v_MedStaffFact msf on msf.MedStaffFact_id = ppr.MedStaffFact_id
				left join v_Lpu l on l.Lpu_id = msf.Lpu_id
				left join v_PostMed pm on pm.PostMed_id = msf.Post_id
				left join v_pmUserCache puc on puc.pmUser_id = ppr.pmUser_signID
				left join v_pmUserCache puca on puca.pmUser_id = ppra.pmUser_signID
				left join lateral (
					select
						cast(ppr.PersonPrivilegeReq_setDT as date) as val 					
				) set_dt on true
				-- end from
		    {$where_clause}
		    order by
		    	-- order by
		        ppr.PersonPrivilegeReq_id
		        -- end order by
		";

        return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
    }

	/**
	 * Получение данных о фамилии при рождении
	 */
	function loadPersonSurNameAtBirth($data) {
		$result = [
			'success' => true
		];

		$query = "
			select
				psnab.PersonSurNameAtBirth_id as \"PersonSurNameAtBirth_id\",
				psnab.PersonSurNameAtBirth_SurName as \"PersonSurNameAtBirth_SurName\",
				ar.ApprovedReq_Cnt as \"ApprovedReq_Cnt\"
			from
				v_PersonSurNameAtBirth psnab
				left join lateral (
					select
						count(ppr.PersonPrivilegeReq_id) as ApprovedReq_Cnt
					from
						v_PersonPrivilegeReq ppr
						left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
					where
						ppr.Person_id = psnab.Person_id and
						PersonPrivilegeReqStatus_id = 3 and -- 3 - ответ получен
						PersonPrivilegeReqAns_IsInReg = 2 -- признак включения в регистр
				) ar on true
			where
				psnab.Person_id = :Person_id;
		";
		$psnab_data = $this->getFirstRowFromQuery($query, $data);

		if (!empty($psnab_data['PersonSurNameAtBirth_id'])) {
			$result['PersonSurNameAtBirth_id'] = $psnab_data['PersonSurNameAtBirth_id'];
			$result['PersonSurNameAtBirth_SurName'] = $psnab_data['PersonSurNameAtBirth_SurName'];
			$result['ApprovedReq_Cnt'] = $psnab_data['ApprovedReq_Cnt'];
		}

		return $result;
	}

    /**
     * Отправка уведомления
     */
    function sendNotice($recipient_id, $header, $text) {
        $this->load->model('Messages_model', 'Messages_model');
        $result = array();

        try {
            if (empty($recipient_id) || empty($header) || empty($text)) {
                throw new Exception("Не переданы параметры для формирования уведомления");
            }

            // Формируем данные для сообщения
            $message_id = null;
            $message_data = array();
            $message_data['action'] = 'ins';
            $message_data['Message_id'] = null;
            $message_data['Message_pid'] = null;
            $message_data['pmUser_id'] = $this->getPromedUserId();
            $message_data['Message_Subject'] = $header;
            $message_data['Message_Text'] = $text;
            $message_data['Message_isSent'] = 1;
            $message_data['NoticeType_id'] = 1;
            $message_data['Message_isFlag'] = null;
            $message_data['Message_isDelete'] = null;
            $message_data['RecipientType_id'] = 1;
            $message_data['MessageRecipient_id'] = null;
            $message_data['Message_isRead'] = null;

            // добавляем само сообщение
            $response = $this->Messages_model->insMessage($message_data);
            if (!empty($response[0]['Error_Msg'])) {
                throw new Exception("Ошибка при формировании уведомления");
            } else {
                $message_id = $response[0]['Message_id'];
            }

            $response = $this->Messages_model->insMessageLink($message_id, $recipient_id, $message_data);
            if (!empty($response[0]['Error_Msg'])) {
                throw new Exception("Ошибка при сохранении данных уведомления");
            }

            // отправляем сообщение
            $this->Messages_model->sendMessage($message_data, $recipient_id, $message_id);

            $result['Message_id'] = $message_id;
            $result['success'] = true;
        } catch (Exception $e) {
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadDocumentPrivilegeTypeCombo($data) {
        $where = array();
        $params = array();

        if (!empty($data['DocumentPrivilegeType_id'])) {
            $where[] = "dpt.DocumentPrivilegeType_id = :DocumentPrivilegeType_id";
            $params['DocumentPrivilegeType_id'] = $data['DocumentPrivilegeType_id'];
        } else {
            $where[] = "dpt.pmUser_insID <> 1"; //тип добавлен пользователем
            if (!empty($data['query'])) {
				if(is_numeric($data['query'])){
					$where[] = "dpt.DocumentPrivilegeType_Code = :query";
					$params['query'] = (int)$data['query'];
				} else {
					$where[] = "dpt.DocumentPrivilegeType_Name ilike :query";
					$params['query'] = $data['query']."%";
				}
            }
        }

        $where_clause = implode(" and ", $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            select
                dpt.DocumentPrivilegeType_id as \"DocumentPrivilegeType_id\",
                dpt.DocumentPrivilegeType_Code as \"DocumentPrivilegeType_Code\",
                dpt.DocumentPrivilegeType_Name as \"DocumentPrivilegeType_Name\"
            from
                v_DocumentPrivilegeType dpt
            {$where_clause}
            order by
            	dpt.DocumentPrivilegeType_Code
            limit 500
		";
        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadDiagByPrivilegeTypeCombo($data) {
        $where = array();
        $join = array();
        $params = array();

        if (!empty($data['Diag_id'])) {
            $where[] = "d.Diag_id = :Diag_id";
            $params['Diag_id'] = $data['Diag_id'];
        } else {
            if (!empty($data['PrivilegeType_id'])) {
                $query = "
					select
						pdl.PrivilegeDiagLink_id as \"PrivilegeDiagLink_id\"
					from
						v_PrivilegeDiagLink pdl
						left join v_Diag d on d.Diag_id = pdl.Diag_id
					where
						pdl.PrivilegeType_id = :PrivilegeType_id
					and 
						d.Diag_id is not null
					limit 1
				";
                $check_data = $this->getFirstRowFromQuery($query, array(
                    'PrivilegeType_id' => $data['PrivilegeType_id']
                ));
                if (!empty($check_data['PrivilegeDiagLink_id'])) {
                    $join[] = "left join lateral (
						select
							i_pdl.PrivilegeDiagLink_id
						from
							v_PrivilegeDiagLink i_pdl
						where
							i_pdl.PrivilegeType_id = :PrivilegeType_id and 
							i_pdl.Diag_id = d.Diag_id
						limit 1
					) pdl on true";
                    $where[] = "pdl.PrivilegeDiagLink_id is not null";
                    $params['PrivilegeType_id'] = $data['PrivilegeType_id'];
                }
            }
            if (!empty($data['query'])) {
                $where[] = "(d.Diag_Code ilike :query or d.Diag_Name ilike :query)";
                $params['query'] = $data['query']."%";
            }
        }

        $join_clause = implode(" ", $join);
        $where_clause = implode(" and ", $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            select
                d.Diag_id as \"Diag_id\",
                d.Diag_Code as \"Diag_Code\",
                d.Diag_Name as \"Diag_Name\"
            from
                v_Diag d
            	{$join_clause}
            {$where_clause}
            order by
            	d.Diag_Code
            limit 1
		";
        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadPersonPrivilegeReqMedStaffFactCombo($data) {
        $where = array();
        $params = array();

        if (!empty($data['MedStaffFact_id'])) {
            $where[] = "msf.MedStaffFact_id = :MedStaffFact_id";
            $params['MedStaffFact_id'] = $data['MedStaffFact_id'];
        } else {
            if (!empty($data['MedPersonal_id'])) {
                $where[] = "msf.MedPersonal_id = :MedPersonal_id";
                $params['MedPersonal_id'] = $data['MedPersonal_id'];
            }
            if (!empty($data['Lpu_id'])) {
                $where[] = "msf.Lpu_id = :Lpu_id";
                $params['Lpu_id'] = $data['Lpu_id'];
            }
            if (!empty($data['LpuSection_id'])) {
                $where[] = "msf.LpuSection_id = :LpuSection_id";
                $params['LpuSection_id'] = $data['LpuSection_id'];
            }
            if (!empty($data['query'])) {
                $where[] = "(msf_name.MedStaffFact_Name ilike :query)";
                $params['query'] = "%".$data['query']."%";
            }
        }

        $where_clause = implode(" and ", $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            select
                min(msf.MedStaffFact_id) as \"MedStaffFact_id\",
				l.Lpu_Nick as \"Msf_Lpu_Nick\",
				msf_name.MedStaffFact_Name as \"MedStaffFact_Name\"
            from
                v_MedStaffFact msf
				left join v_Lpu l on l.Lpu_id = msf.Lpu_id
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				left join v_PostMed pm on pm.PostMed_id = msf.Post_id
				left join lateral (
					select
						(
							coalesce(msf.Person_Fio, '') ||
							coalesce(' ' || pm.PostMed_Name, '') ||
							coalesce(' ' || ls.LpuSection_Name, '')
						) as MedStaffFact_Name
				) msf_name on true
            {$where_clause}
            group by
            	l.Lpu_Nick,
            	msf_name.MedStaffFact_Name
            order by
            	msf_name.MedStaffFact_Name
            limit 1
		";
        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

	/**
	 * Загрузка списка для комбобокса
	 */
	function loadPersonSurNameCombo($data) {
		$params = array();

		if (!empty($data['query'])) {
			$params['query'] = $data['query']."%";
		} else {
			return false;
		}

		$query = "
			select
				p.Person_SurName
			from
				(
					select
						psn.PersonSurName_SurName as Person_SurName
					from
						v_PersonSurName psn
					where
						lower(psn.PersonSurName_SurName) ilike lower(:query)
					union
					select
						psnab.PersonSurNameAtBirth_SurName as Person_SurName
					from
						v_PersonSurNameAtBirth psnab
					where
						lower(psnab.PersonSurNameAtBirth_SurName) ilike lower(:query)
				) p
			order by
				p.Person_SurName
			limit 100
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка наличия у пациента основного или службеного прикрепления к МО
	 */
	function checkPrivilegeMainOrServiceAttachment($data) {
		$query = "
				select 
					count(pc.PersonCard_id) as \"cnt\"
				from
					v_PersonCard pc
					inner join v_Lpu l on l.Lpu_id = pc.Lpu_id
				where
					pc.Person_id = :Person_id and
					pc.LpuAttachType_id in (1, 4) and -- основное или служебное
					pc.Lpu_id = :Lpu_id and
					(pc.PersonCard_begDate is null or pc.PersonCard_begDate <= dbo.tzGetDate()) and
					(pc.PersonCard_endDate is null or pc.PersonCard_endDate >= dbo.tzGetDate())
			";
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

}