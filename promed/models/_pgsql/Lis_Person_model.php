<?php

defined('BASEPATH') or die('No direct script access allowed');

require_once(APPPATH.'models/Person_model.php');

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
class Lis_Person_model extends Person_model {

	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Метод поиска людей для Postgres
	 */
	function getPersonSearchGrid($data) {
		$filters = [];
		$extra_select = "'edit' as \"accessType\"";
		$join = "";
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$select_person_data = "				coalesce(ps.Person_SurName, '') as \"PersonSurName_SurName\",
				coalesce(ps.Person_FirName, '') as \"PersonFirName_FirName\",
				coalesce(ps.Person_SecName, '') as \"PersonSecName_SecName\",
				pls.Polis_Ser as \"Polis_Ser\",
				pls.PolisFormType_id as \"PolisFormType_id\",
				pls.OrgSMO_id as \"OrgSMO_id\",
				pls.OMSSprTerr_id as \"OMSSprTerr_id\",
				ps.Person_Snils as \"Person_Snils\",
				case
					when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num
				end as \"Polis_Num\",
				ps.Document_Ser as \"Document_Ser\",
				ps.Document_Num as \"Document_Num\",
				ps.Person_edNum as \"Polis_EdNum\",
				Age((select curDT from myvars), ps.Person_BirthDay) as \"Person_Age\",
				to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"PersonBirthDay_BirthDay\",
				to_char(ps.Person_deadDT, 'dd.mm.yyyy') as \"Person_deadDT\",
				ps.Sex_id as \"Sex_id\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				lpu.Lpu_id as \"CmpLpu_id\",
				pcard1.PersonCardState_Code as \"PersonCard_Code\",";

		if (isEncrypHIVRegion($this->regionNick)) {
			if (allowPersonEncrypHIV($data['session'])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data['PersonSurName_SurName']);
				if ($isSearchByEncryp) {
					// нельзя ни редактировать, ни просмотреть
					$extra_select .= "
						,'list' as accessType
					";
					$select_person_data = "				peh.PersonEncrypHIV_Encryp as \"PersonSurName_SurName\",
				'' as \"PersonFirName_FirName\",
				'' as \"PersonSecName_SecName\",
				'' as \"Polis_Ser\",
				'' as \"Polis_Num\",
				'' as \"PolisFormType_id\",
				'' as \"OrgSMO_id\",
				'' as \"OMSSprTerr_id\",
				'' as \"Document_Ser\",
				'' as \"Document_Num\",
				'' as \"Polis_EdNum\",
				null as \"Person_Age\",
				null as \"PersonBirthDay_BirthDay\",
				null as \"Person_deadDT\",
				null as \"Sex_id\",
				'' as \"Lpu_Nick\",
				null as \"CmpLpu_id\",
				'' as \"PersonCard_Code\",";
				}
			} else {
				//Не отображать анонимных шифрованных пациентов
				$filters[] = "not exists(
					select
						peh.PersonEncrypHIV_Encryp
					from v_PersonEncrypHIV peh
					inner join v_EncrypHIVTerr eht on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id
						and isnull(eht.EncrypHIVTerr_Code,0) = 20
					where peh.Person_id = ps.Person_id
					limit 1
				)";
			}
		}

		if (!isSuperAdmin() && strlen($data['PersonSurName_SurName'])>0)  {
			$data['PersonSurName_SurName'] = trim(str_replace(array('%','_'), '', $data['PersonSurName_SurName']));
			if (strlen($data['PersonSurName_SurName'])==0) {
				DieWithError("Поле Фамилия обязательно для заполнения (использование знаков % и _  недопустимо).");
			}
		}

		if (!empty($data['PersonSurName_SurName'])) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$join .= " inner join v_PersonEncrypHIV peh on peh.Person_id = ps.Person_id and peh.PersonEncrypHIV_Encryp like upper(:Person_SurName)";
				$filters[] = "1=1";//чтобы не выходила ошибка "Не указан ни один фильтр. Для поиска заполните хотя бы один параметр в блоке фильтров."
				$filters[] = "peh.PersonEncrypHIV_Encryp like upper(:Person_SurName)";
			} else {
				$filters[] = "ps.Person_SurNameR LIKE upper(:Person_SurName) || '%'";
			}
			$queryParams['Person_SurName'] = $this->prepareSearchSymbol($data['PersonSurName_SurName']);
		}

		if (!empty($data['PersonFirName_FirName'])) {
			$filters[] = "ps.Person_FirNameR LIKE upper(:Person_FirName) || '%'";
			$queryParams['Person_FirName'] =  $this->prepareSearchSymbol($data['PersonFirName_FirName']);
		}

		if (!empty($data['PersonSecName_SecName'])) {
			$filters[] = "ps.Person_SecNameR LIKE upper(:Person_SecName) || '%'";
			$queryParams['Person_SecName'] = $this->prepareSearchSymbol($data['PersonSecName_SecName']);
		}

		if (!empty($data['PersonBirthDay_BirthDay'])) {
			$filters[] = "ps.Person_BirthDay = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['PersonBirthDay_BirthDay'];
		}

		if (!empty($data['Person_Snils'])) {
			$filters[] = "ps.Person_Snils = :Person_Snils";
			$queryParams['Person_Snils'] = $data['Person_Snils'];
		}

		if (!empty($data['Person_Inn'])) {
			$filters[] = "ps.Person_Inn = :Person_Inn";
			$queryParams['Person_Inn'] = $data['Person_Inn'];
		}

		if (!empty($data['Polis_Ser'])) {
			$filters[] = "ps.Polis_Ser = :Polis_Ser";
			$queryParams['Polis_Ser'] = $data['Polis_Ser'];
		}

		if (!empty($data['Polis_Num'])) {
			$filters[] = "ps.Polis_Num = :Polis_Num";
			$queryParams['Polis_Num'] = $data['Polis_Num'];
		}
		if (!empty($data['Polis_EdNum'])) {
			$filters[] = "ps.Person_edNum = :Polis_edNum";
			$queryParams['Polis_edNum'] = $data['Polis_EdNum'];
		}

		if (!empty($data['Sex_id'])) {
			$filters[] = "ps.Sex_id = :Sex_id";
			$queryParams['Sex_id'] = $data['Sex_id'];
		}

		if (!empty($data['PersonBirthYearFrom'])) {
			$filters[] = "(extract(year from ps.Person_BirthDay) >= :PersonBirthYearFrom)";
			$queryParams['PersonBirthYearFrom'] = $data['PersonBirthYearFrom'];
		}

		if (!empty($data['PersonBirthYearTo'])) {
			$filters[] = "(extract(year from ps.Person_BirthDay) <= :PersonBirthYearTo)";
			$queryParams['PersonBirthYearTo'] = $data['PersonBirthYearTo'];
		}

		$queryParams['PersonAge_AgeFrom'] = $data['PersonAge_AgeFrom'];
		$queryParams['PersonAge_AgeTo'] = $data['PersonAge_AgeTo'];

		if (!empty($data['personBirtDayFrom'])) {
			$filters[] = "(ps.Person_BirthDay >= :personBirtDayFrom)";
			$queryParams['personBirtDayFrom'] = $data['personBirtDayFrom'];
		}

		if (!empty($data['personBirtDayTo'])) {
			$filters[] = "(ps.Person_BirthDay <= :personBirtDayTo)";
			$queryParams['personBirtDayTo'] = $data['personBirtDayTo'];
		}

		if (!empty($data['Person_id'])) {
			$filters[] = "ps.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}

		if (!empty($data['PersonCard_id'])) {
			$filters[] = "exists (
				select
					PersonCard_id
				from v_PersonCard
				where
					Person_id = PS.Person_id
					and PersonCard_id = :PersonCard_id
				limit 1
			)";
			$queryParams['PersonCard_id'] = $data['PersonCard_id'];
		}

		if (!empty($data['PersonCard_Code'])) {
			$filters[] = "exists (
				select
					PersonCard_id
				from v_PersonCard
				where
					Person_id = PS.Person_id
					and PersonCard_Code = :PersonCard_Code
					and (PersonCard_endDate is null or PersonCard_endDate >= (select curDT from myvars))
					and Lpu_id = :Lpu_id
				limit 1
			)";
			$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
		}

		$isBDZ = "CASE
					WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= (select curDT from myvars) THEN 'orange'
					ELSE CASE
						WHEN ps.PersonCloseCause_id = 2 and ps.Person_closeDT is not null THEN 'red'
						ELSE CASE
							WHEN ps.Server_pid = 0 THEN 'true'
							ELSE 'false'
						END
					END
				END as Person_IsBDZ,
				";

		if(getRegionNick() == 'perm'){
			$isBDZ ="case
					when ps.Server_pid = 0  then 'true'
					when ps.Person_IsInErz = 1 then 'blue'
					when ps.Server_pid = 0 and pls.Polis_endDate < (select curDT from myvars) then
						case when ps.Person_deadDT is null then 'yellow' else 'red' end
					when ps.Server_pid = 2 and ps.Person_IsInErz <> 1 then 'false'
				end as Person_IsBDZ,";
		}

		if(getRegionNick() == 'penza'){
			$isBDZ ="case
					when ps.Person_IsInErz = 1 then 'orange'
					when ps.Person_IsInErz = 2 then 'true'
					else 'false'
				end as Person_IsBDZ,";
		}

		if (getRegionNick() == 'kz') {
			$isBDZ ="case
					when ps.Person_IsInErz = 1 then 'red'
					when ps.Person_IsInErz = 2 then 'true'
					else 'false'
				end as Person_IsBDZ,";
		}

		$select = "
			select
				ps.Person_id as \"Person_id\",
				ps.Server_id as \"Server_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Person_IsInErz as \"Person_IsInErz\",
				ps.Person_Phone as \"Person_Phone\",
				null as \"Person_isOftenCaller\",
				null as \"Person_IsRefuse\",
				CASE
					WHEN (ps.Person_deadDT is not null) or (ps.Person_IsDead = 2) THEN 'true' ELSE 'false'
				END as \"Person_IsDead\",
				null as \"Person_IsFedLgot\",
				null as \"Person_IsRegLgot\",
				CASE
					WHEN disp.OwnLpu = 1 THEN 'true' ELSE CASE WHEN disp.OwnLpu is not null THEN 'gray' ELSE 'false'
					END
				END as \"Person_Is7Noz\",
				uaddr.Address_Address as \"UAddress_AddressText\",
				paddr.Address_Address as \"PAddress_AddressText\",
				{$isBDZ}
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as \"PersonQuarantine_IsOn\",
				to_char(PQ.PersonQuarantine_begDT, 'DD.MM.YYYY') as \"PersonQuarantine_begDT\",
				CASE
					WHEN exists (
						select
							PersonCard_id
						from
							v_PersonCard
						where
							Person_id = ps.Person_id
							and LpuAttachType_id = 5
							and PersonCard_endDate >= (select curDT from myvars)
							and CardCloseCause_id is null
					) THEN 'true' ELSE 'false' END as \"PersonCard_IsDms\",
				{$select_person_data}
			{$extra_select}
		";

		$orderBy = "
		order by
				ps.Person_SurNameR asc,
				ps.Person_FirNameR asc,
				ps.Person_SecNameR asc
		";

		$limit = "limit {$data['limit']}";
		$offset = "offset {$data['start']}";

		if (isset($data['getCountOnly'])) {
			$select = "
				select
					count(*) as \"totalCount\"
			";
			$orderBy = "";
			$limit = "";
			$offset = "";
		}

		$query = "
			with myvars as (
				select dbo.tzgetdate() as curDT
			)
			{$select}
			from
				v_PersonState ps
				left join v_Person per on per.Person_id = ps.Person_id
				left join lateral (
						select PQ.*
						from v_PersonQuarantine PQ
						where PQ.Person_id = ps.Person_id 
						and PQ.PersonQuarantine_endDT is null
						limit 1
				) PQ on true
				left join lateral(
					select
						PersonCardState_Code
					from v_PersonCardState
					where
						Person_id = ps.Person_id
						and Lpu_id = :Lpu_id
						and LpuAttachType_id = 1
					limit 1
				) pcard1 on true
				left join v_Polis pls on pls.Polis_id = ps.Polis_id
				left join lateral(
					select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id
					from v_PersonCard pc
					where
						pc.Person_id = ps.Person_id
						and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					limit 1	
				) pcard on true
				left join lateral( select * from v_Lpu lpu where   pcard.Lpu_id = lpu.Lpu_id limit 1) lpu on true
				left join lateral(
					select
						max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from v_PersonDisp
					where
						Person_id = ps.Person_id
						and (coalesce(PersonDisp_endDate, (select curDT from myvars)) >= (select curDT from myvars))
						and Sickness_id in (1,3,4,5,6,7,8)
				) disp on true
				left join lateral( select * from v_Address uaddr where  ps.UAddress_id = uaddr.Address_id limit 1) uaddr on true
    			left join lateral( select * from v_Address paddr where  ps.PAddress_id = paddr.Address_id limit 1) paddr on true
			where
				". implode('
				and ',$filters) ."
			{$orderBy}			
			{$limit}
			{$offset}	
		";

		$res = $this->queryResult($query, $queryParams);

		if (!isset($data['getCountOnly'])) {
			return [
				'data' => $res,
				'totalCount' => count($res),
				'overLimit' => count($res) >= $data['limit'] ? true : false
			];
		} else {
			return [
				'data' => [],
				'totalCount' => $res[0]['totalCount']
			];

		}
	}

	function loadPersonData($data) {
		$query = "
			with myvars as (
				select dbo.tzgetdate() as curdate
			)
			SELECT
				ps.Person_id as \"Person_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				ps.Server_pid as \"Server_pid\",
				coalesce(to_char(PS.Person_BirthDay, 'dd.mm.yyyy'), '') as \"Person_Birthday\",
				coalesce(to_char(PS.Person_BirthDay, 'yyyy-mm-dd'), '') as \"Person_Birthday_ISO\",
				PersonState.Lpu_id as \"Lpu_id\",
				coalesce(RTRIM(coalesce(PAddress.Address_Nick, PAddress.Address_Address)),'') as \"Person_PAddress\",
				coalesce(RTRIM(coalesce(UAddress.Address_Nick, UAddress.Address_Address)),'') as \"Person_RAddress\",
				coalesce(RTRIM(dorg.Org_Name), '') as \"OrgDep_Name\",
				CASE
					WHEN (pcard.PersonCard_endDate IS NOT NULL)
						THEN coalesce(RTRIM(lpu_pcard.Lpu_Nick), '') || ' (Прикрепление неактуально. Дата открепления: '||coalesce(to_char(pcard.PersonCard_endDate, 'dd.mm.yyyy'), '')||')'
						ELSE coalesce(RTRIM(lpu_pcard.Lpu_Nick), '')
					end 
				as \"Lpu_Nick\",
				coalesce(to_char(pcard.PersonCard_begDate, 'dd.mm.yyyy'), '') as \"PersonCard_begDate\",
				pcard.PersonCard_id as \"PersonCard_id\",
				coalesce(to_char(pcard.PersonCard_endDate, 'dd.mm.yyyy'), '') as \"PersonCard_endDate\",
				case
					when (KLArea.KLSocr_id = 68) or (KLArea.KLSocr_id = 56)
						then '1'
						else '0'
					end
				as \"KLAreaType_id\",
				coalesce(RTRIM(DocumentType.DocumentType_Name), '') as \"DocumentType_Name\",
				coalesce(RTRIM(Document.Document_Num), '') as \"Document_Num\",
				coalesce(RTRIM(Document.Document_Ser), '') as \"Document_Ser\",
				coalesce(to_char(Document.Document_begDate, 'dd.mm.yyyy'), '') as \"Document_begDate\",
				coalesce(ps.Person_Inn,'') as \"Person_Inn\",
				coalesce(PS.Person_Snils,'') as \"Person_Snils\",
				ps.SocStatus_id as \"SocStatus_id\",
				(date_part('year', (select curdate from myvars)) - date_part('year', PS.Person_Birthday)
				+
				case
					when date_part('month',PS.Person_Birthday) > date_part('month',(select curdate from myvars))
						or (date_part('month',PS.Person_Birthday) = date_part('month',(select curdate from myvars))
						and date_part('day',PS.Person_Birthday) > date_part('day',(select curdate from myvars)))
							then -1
							else 0
					end)
				as \"Person_Age\",
				CASE
					WHEN (PS.Person_BirthDay is not null)
						THEN
						CASE
							WHEN date_part('year', coalesce(Person.Person_deadDT, (select curdate from myvars))) - date_part('year', PS.Person_BirthDay) > 0
							or date_part('month', PS.Person_BirthDay) - date_part('month', coalesce(Person.Person_deadDT, (select curdate from myvars))) > 12
								THEN (date_part('year', coalesce(Person.Person_deadDT, (select curdate from myvars))) - (date_part('year', PS.Person_BirthDay)
								+
								case
									when date_part('month',PS.Person_Birthday) > date_part('month',coalesce(Person.Person_deadDT, (select curdate from myvars)))
										or (date_part('month',PS.Person_Birthday) = date_part('month',coalesce(Person.Person_deadDT, (select curdate from myvars)))
										and date_part('day',PS.Person_Birthday) > date_part('day',coalesce(Person.Person_deadDT, (select curdate from myvars))))
											then 1
											else 0
									end)
								) || ' лет'
								ELSE
									CASE
										WHEN date_part('day', PS.Person_BirthDay) - date_part('day', coalesce(Person.Person_deadDT, (select curdate from myvars))) <=30
											THEN (date_part('day', PS.Person_BirthDay) - date_part('day', coalesce(Person.Person_deadDT, (select curdate from myvars)))) || ' дн.'
											ELSE (date_part('month', PS.Person_BirthDay) - date_part('month', coalesce(Person.Person_deadDT, (select curdate from myvars)))) || ' мес.'
										END
							END
						ELSE ''
					END
				as \"personAgeText\",
				CASE WHEN PS.Server_pid = 0 THEN 1 ELSE 0 END AS \"Person_IsBDZ\",
				coalesce(RTRIM(PP.Post_Name), '') as \"Person_Post\",
				coalesce(job.Org_id, null) as \"JobOrg_id\",
				coalesce(joborg.Org_Name, null) as \"Person_Job\",
				coalesce(RTRIM(PO.Org_Name), '') as \"OrgSmo_Name\",
				coalesce(OrgSmo.OrgSmo_id, null) as \"OrgSmo_id\",
				coalesce(RTRIM(case when Polis.PolisType_id = 4 then '' else Polis.Polis_Ser end), '') as \"Polis_Ser\",
				coalesce(RTRIM(case when Polis.PolisType_id = 4 and PS.Person_EdNum is not null then PS.Person_EdNum else Polis.Polis_Num end), '') as \"Polis_Num\",
				coalesce(to_char(Polis.Polis_begDate, 'dd.mm.yyyy'), '') as \"Polis_begDate\",
				coalesce(to_char(Polis.Polis_endDate, 'dd.mm.yyyy'), '') as \"Polis_endDate\",
				Sex.Sex_Code as \"Sex_Code\",
				Sex.Sex_Name as \"Sex_Name\",
				Sex.Sex_id as \"Sex_id\",
				coalesce(RTRIM(SocStatus.SocStatus_Name), '') as \"SocStatus_Name\",
				Person.Person_IsUnknown as \"Person_IsUnknown\",
				Person.Person_IsDead as \"Person_IsDead\",
				Person.PersonCloseCause_id as \"PersonCloseCause_id\",
				coalesce(to_char(Person.Person_deadDT, 'dd.mm.yyyy'), '') as \"Person_deadDT\",
				coalesce(to_char(Person.Person_closeDT, 'dd.mm.yyyy'), '') as \"Person_closeDT\",
				Person.Person_IsAnonym as \"Person_IsAnonym\",
				coalesce(RTRIM(PS.Person_Surname), '') as \"Person_Surname\",
				coalesce(RTRIM(PS.Person_Firname), '') as \"Person_Firname\",
				coalesce(RTRIM(PS.Person_Secname), '') as \"Person_Secname\",
				coalesce(PS.Person_EdNum,'') as \"Person_EdNum\",
				substring(PS.Person_SurName from 1 for 1) as \"SurNameLetter\"
			FROM
				v_PersonState PS
				left join v_PersonState PersonState on PS.Person_id = PersonState.Person_id
				left join v_Address PAddress on PAddress.Address_id = PS.PAddress_id
				left join v_Address UAddress on UAddress.Address_id = PS.UAddress_id
				left join v_Document Document on Document.Document_id = PS.Document_id
				left join v_OrgDep OrgDep on OrgDep.OrgDep_id = Document.OrgDep_id
				left join v_Org dorg on dorg.Org_id = OrgDep.Org_id
				left join lateral(
					select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.PersonCard_id,
						pc.PersonCard_begDate,
						pc.PersonCard_endDate
					from
						v_PersonCard pc
					where
						pc.Person_id = ps.Person_id and LpuAttachType_id = 1
					order by
						PersonCard_begDate desc, PersonCard_id desc
					limit 1
				) pcard on true
				left join v_Lpu lpu_pcard on lpu_pcard.Lpu_id = pcard.Lpu_id
				left join v_Lpu Lpu on Lpu.Lpu_id = PersonState.Lpu_id
				left join v_KLArea KLArea on KLArea.KLArea_id = UAddress.KLTown_id
				left join v_DocumentType DocumentType on DocumentType.DocumentType_id = Document.DocumentType_id
				left join v_Person Person on Person.Person_id = PS.Person_id
				left join v_Job job on job.Job_id = ps.Job_id
				left join v_Post PP on PP.Post_id = Job.Post_id
				left join v_Org joborg on joborg.Org_id = job.Org_id
				left join v_Polis Polis on Polis.Polis_id = PS.Polis_id
				left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join v_Org PO on PO.Org_id = OrgSmo.Org_id
				left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
				left join v_SocStatus SocStatus on SocStatus.SocStatus_id = PS.SocStatus_id
			WHERE
				(1=1)
				and PS.Person_id = :Person_id
			limit 1
		";

		return $this->queryResult($query, $data);
	}
}
