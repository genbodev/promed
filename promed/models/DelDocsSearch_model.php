<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");
/**
 * Класс модели "Просмотр удаленных документов"
 *
 * @package Common
 * @author Melentyev Anatoliy
 */

class DelDocsSearch_model extends swModel
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получаем удаленных документов
	 * @param $VaccinationType_id
	 * @return array
	 */
	function LoadDelDocs($data)
	{

		if (!empty($data['CreateDocs_DateRange'][0])) {
			if (!empty($data['CreateDocs_DateRange'][1])) {
				$filterList[] = 'cast(E.Evn_insDT as date) >= :CreateDocs_begDate and cast(E.Evn_insDT as date) <= :CreateDocs_endDate';
				$filterCmpList[] = 'cast(CCC.CmpCloseCard_insDT as date) >= :CreateDocs_begDate and cast(CCC.CmpCloseCard_insDT as date) <= :CreateDocs_endDate';
				$queryParams = ['CreateDocs_endDate' => $data['CreateDocs_DateRange'][1]];
			}else{
				$filterList[] = 'cast(E.Evn_insDT as date) = :CreateDocs_begDate';
				$filterCmpList[] = 'cast(CCC.CmpCloseCard_insDT as date) = :CreateDocs_begDate';
			}
			$queryParams['CreateDocs_begDate'] = $data['CreateDocs_DateRange'][0];
		}

		if (!empty($data['DeleteDocs_DateRange'][0])) {
			if (!empty($data['DeleteDocs_DateRange'][1])) {
				$filterList[] = 'cast(E.Evn_delDT as date) >= :DeleteDocs_begDate and cast(E.Evn_delDT as date) <= :DeleteDocs_endDate';
				$filterCmpList[] = 'cast(CCC.CmpCloseCard_delDT as date) >= :DeleteDocs_begDate and cast(CCC.CmpCloseCard_delDT as date) <= :DeleteDocs_endDate';
				$queryParams['DeleteDocs_endDate'] = $data['DeleteDocs_DateRange'][1];
			}else{
				$filterList[] = 'cast(E.Evn_delDT as date) = :DeleteDocs_begDate';
				$filterCmpList[] = 'cast(CCC.CmpCloseCard_delDT as date) = :DeleteDocs_begDate';
			}
			$queryParams['DeleteDocs_begDate'] = $data['DeleteDocs_DateRange'][0];
		}
		
		if (!empty($data['Lpu_id'])) {
			$filterList[] = 'E.Lpu_id = :Lpu_id';
			$filterCmpList[] = 'CCC.Lpu_id = :Lpu_id';
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		
		if (!empty($data['DocsType_id']) && $data['DocsType_id'] !== 0) {
			$filterList[] = 'E.EvnClass_id = :DocsType_id';
			$filterCmpList[] = '110 = :DocsType_id';
			$queryParams['DocsType_id'] = $data['DocsType_id'];
		}

		if (!empty($data['Person_FirName'])) {
			$filterList[] = 'lower(PS.Person_FirName) like :Person_FirName';
			$filterCmpList[] = 'lower(CCC.Name) like :Person_FirName';
			$queryParams['Person_FirName'] = mb_strtolower($data['Person_FirName']).'%';
		}

		if (!empty($data['Person_SecName'])) {
			$filterList[] = 'lower(PS.Person_SecName) like :Person_SecName';
			$filterCmpList[] = 'lower(CCC.Fam) like :Person_SecName';
			$queryParams['Person_SecName'] = mb_strtolower($data['Person_SecName']).'%';
		}
		
		if (!empty($data['Person_SurName'])) {
			$filterList[] = 'lower(PS.Person_SurName) like :Person_SurName';
			$filterCmpList[] = 'lower(CCC.Middle) like :Person_SurName';
			$queryParams['Person_SurName'] = mb_strtolower($data['Person_SurName']).'%';
		}

		if (!empty($data['Person_BirthDay'])) {
			$filterList[] = 'cast(PS.Person_BirthDay as date) = :Person_BirthDay';
			$filterCmpList[] = 'cast(PS.Person_BirthDay as date) = :Person_BirthDay';
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		
		$whereEvn = isset($filterList) ? implode('
				and ', $filterList) : '';
		$whereCmp = isset($filterCmpList) ? implode('
				and ', $filterCmpList) : '';
		
		$queryEvn = "
			select  
				E.Evn_id as Docs_id,
				E.EvnClass_id as EvnClass_id,
				E.Server_id as Server_id,
				case
					when E.EvnClass_id = 3  then 'ТАП'
					when E.EvnClass_id = 6  then 'ТАП (стоматология)'
					when E.EvnClass_id = 30 then 'КВС'
					when E.EvnClass_id = 20 then 'ЛВН'
					when E.EvnClass_id = 78 then 'Заявка на лабораторное исследование'
				end as DocsType_Name,
				case when E.EvnClass_id = 78 then ELR.EvnDirection_id else '' end as EvnDirection_id,
				case when E.EvnClass_id = 78 then ELR.MedService_id else '' end as MedService_id,
				case
					when E.EvnClass_id = 3  then cast(EPL.EvnPL_NumCard as varchar)
					when E.EvnClass_id = 6  then cast(EPL.EvnPL_NumCard as varchar)
					when E.EvnClass_id = 30 then cast(EPS.EvnPS_NumCard as varchar)
					when E.EvnClass_id = 20 then cast(ESB.EvnStickBase_Num as varchar)
					when E.EvnClass_id = 78 then cast(ELR.EvnLabRequest_RegNum as varchar)
				end as Docs_Num,
				E.Lpu_id as Lpu_id,
				coalesce(L.Lpu_Nick,L.Lpu_Name) as Lpu_Name,
				E.Person_id as Person_id,
					isnull(upper(left(PS.Person_SurName, 1)) + lower(stuff(PS.Person_SurName, 1, 1, '')),'') + ' ' + 
					isnull(upper(left(PS.Person_FirName, 1)) + lower(stuff(PS.Person_FirName, 1, 1, '')),'') + ' ' + 
					isnull(upper(left(PS.Person_SecName, 1)) + lower(stuff(PS.Person_SecName, 1, 1, '')),'') 
				as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				convert(varchar(10), E.Evn_insDT, 104) as CreateDocs_Date,
				E.Evn_delDT as DeleteDocs_Date
			from	Evn E with(nolock) 
				inner join v_EvnClass EC with(nolock) on EC.EvnClass_id = E.EvnClass_id and E.EvnClass_id in (3,6,30,20,78)
				left join v_PersonState PS with(nolock) on PS.Person_id = E.Person_id
				left join v_Lpu L with(nolock) on L.Lpu_id = E.Lpu_id
				left join EvnPL EPL with(nolock) on EPL.EvnPL_id = E.Evn_id
				left join EvnPS EPS with(nolock) on EPS.EvnPS_id = E.Evn_id
				left join EvnStickBase ESB with(nolock) on ESB.EvnStickBase_id = E.Evn_id
				left join EvnLabRequest ELR with(nolock) on ELR.EvnLabRequest_id = E.Evn_id
			where 
				E.Evn_deleted = 2
				and {$whereEvn}
		";
		
		$queryCmp = "
			select  
				CCC.CmpCloseCard_id as Docs_id,
				'110' as EvnClass_id,
				0 as Server_id,
				'Карты вызова 110у'	as DocsType_Name,
				'' as EvnDirection_id,
				'' as MedService_id,
				cast(CCC.Year_Num as varchar) as Docs_Num,
				CCC.Lpu_id as Lpu_id,
				coalesce(L.Lpu_Nick,L.Lpu_Name) as Lpu_Name,
				CCC.Person_id as Person_id,
					isnull(upper(left(CCC.Fam, 1)) + lower(stuff(CCC.Fam, 1, 1, '')),'') + ' ' + 
					isnull(upper(left(CCC.Name, 1)) + lower(stuff(CCC.Name, 1, 1, '')),'') + ' ' + 
					isnull(upper(left(CCC.Middle, 1)) + lower(stuff(CCC.Middle, 1, 1, '')),'') 
				as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				convert(varchar(10), CCC.CmpCloseCard_insDT, 104) as CreateDocs_Date,
				CCC.CmpCloseCard_delDT as DeleteDocs_Date
			from	CmpCloseCard CCC with(nolock)
			 	left join v_Lpu L with(nolock) on L.Lpu_id = CCC.Lpu_id
				left join v_PersonState PS with(nolock) on PS.Person_id = CCC.Person_id
			where 
				CCC.CmpCloseCard_deleted = 2
				and {$whereCmp}
		";
		
		$query = "
			select  
				-- select
				D.Docs_id,
				D.EvnClass_id,
				D.Server_id,
				D.DocsType_Name,
				D.EvnDirection_id,
				D.MedService_id,
				D.Docs_Num,
				D.Lpu_id,
				D.Lpu_Name,
				D.Person_id,
				D.Person_Fio,
				D.Person_BirthDay,
				D.CreateDocs_Date,
				convert(varchar(10), D.DeleteDocs_Date, 104) as DeleteDocs_Date,
				D.DeleteDocs_Date as Sort_Date
				-- end select
			from 
				-- from
				(
				{$queryEvn} 
				union all 
				{$queryCmp}
				) D
				-- end from
			order by 
				-- order by
				D.DeleteDocs_Date desc
				-- end order by
		";

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true, true);
	}

}

