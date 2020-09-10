<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * FarmacyDrugOstat - модель для работы с остатками медикаментов для модуля "Аптека"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2010 Swan Ltd.
 * @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
 * @version      14.01.2010
 */

class FarmacyDrugOstat_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение остатков на дату
	 */
	function loadDrugOatatByDate($data) {
		$queryParams = array();

		$queryParams['Contragent_id'] = $data['Contragent_id'];
		$queryParams['MOL_id'] = NULL;
		$queryParams['Drug_id'] = NULL;
		$queryParams['DrugFinance_id'] = $data['FarmacyOtdel_id'];
		$end_date = strtotime($data['OstatDate']);
		$queryParams['begDate'] = date('Y-m-d', strtotime("+0 day", $end_date));
		$queryParams['endDate'] = date('Y-m-d', strtotime("+1 day", $end_date));

		$query = "
			select
				-- select
				cast(d.Drug_id as varchar) || cast(DocumentUcSTR.DocumentUcSTR_Price as varchar) as \"row_id\",
				d.Drug_id as \"Drug_id\",
				Rtrim(d.Drug_Name) as \"val1\",
				Rtrim(du.DrugUnit_Name) as \"val2\",
				DocumentUcSTR.DocumentUcSTR_Price as \"val3\",
				round(OstNach.val4,5)as \"val4\",
				round(OstNach.val5,2)as \"val5\",
				round(Prihod.val6,5) as \"val6\",
				round(Prihod.val7,2) as \"val7\",
				round(Rashod.val8,5) as \"val8\",
				round(Rashod.val9,2) as \"val9\",
				round(Spisanie.val10,5) as \"val10\",
				round(Spisanie.val11,2) as \"val11\",
				round(OstKon.val12,5) as \"val12\",
				round(OstKon.val13,2) as \"val13\"
				-- end select
			from 
			-- from
			v_DocumentUcStr as DocumentUcStr 
			Inner join Drug as d on (DocumentUcStr.Drug_id = d.Drug_id)
			inner join DrugUnit du on d.DrugUnit_id = du.DrugUnit_id
			Inner join v_DocumentUc as DocumentUc on (DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id) and DocumentUc.DrugDocumentType_id<>6
			left join 
			(select -- начальный остаток
				p.val0 as val0,
				p.val1 as val1,
				p.val2 as val2,
				p.val3,
				sum(p.val4) as val4,
				sum(p.val5) as val5
			   from 
			(select
			d.Drug_id as val0,
			Rtrim(d.Drug_Name)as val1,
			Rtrim(du.DrugUnit_Name) as val2,
			DocumentUcSTR.DocumentUcSTR_Price as val3,
			coalesce(DocumentUcStr.DocumentUcStr_Count,0) - coalesce(sum(DocumentUcStrRash.DocumentUcStr_Count),0) as val4,
			coalesce(DocumentUcStr.DocumentUcStr_Sum,0) - coalesce(sum(DocumentUcStrRash.DocumentUcStr_Sum),0) as val5
			from v_DocumentUcStr DocumentUcStr
			inner join Drug d on d.Drug_id = DocumentUcStr.Drug_id
			inner join DrugUnit du on d.DrugUnit_id = du.DrugUnit_id
			Inner join v_DocumentUc as DocumentUc on (DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id) and DocumentUc.DrugDocumentType_id<>6
			left outer join v_DocumentUcStr DocumentUcStrRash on DocumentUcStrRash.DocumentUcStr_oid = DocumentUcStr.DocumentUcStr_id
			left join v_DocumentUc DocumentUcRash on DocumentUcStrRash.DocumentUc_id = DocumentUcRash.DocumentUc_id
			and ((DocumentUcRash.DocumentUc_didDate < :begDate ) or ( :begDate is null))
			where (1=1)
			and((DocumentUc.DrugFinance_id = :DrugFinance_id )or( :DrugFinance_id is null))
			and(DocumentUc.Contragent_tid = :Contragent_id )
			and((DocumentUc.DocumentUc_didDate < :begDate ) or (:begDate is null))
			group by 
			DocumentUcStr.DocumentUcStr_id,
			d.Drug_id,
			Rtrim(d.Drug_Name),
			Rtrim(du.DrugUnit_Name),
			DocumentUcSTR.DocumentUcSTR_Price,
			DocumentUcStr.DocumentUcStr_Count,
			DocumentUcStr.DocumentUcStr_Sum
			having (coalesce(DocumentUcStr.DocumentUcStr_Count,0) - coalesce(sum(DocumentUcStrRash.DocumentUcStr_Count),0)) > 0
			) as p
			group by p.val1, p.val2, p.val3,p.val0
			) as OstNach on (d.Drug_id = OstNach.val0 and Rtrim(d.Drug_Name) = OstNach.val1 and Rtrim(du.DrugUnit_Name) = OstNach.val2 and DocumentUcSTR.DocumentUcSTR_Price = OstNach.val3)

			left join 
			(select -- приход
				d.Drug_id as val0,
				Rtrim(d.Drug_Name)as val1,
				Rtrim(du.DrugUnit_Name) as val2,
				DocumentUcSTR.DocumentUcSTR_Price as val3,
				sum(DocumentUcStr.DocumentUcStr_Count) as val6,
				sum(DocumentUcStr.DocumentUcStr_Sum) as val7
			from v_DocumentUcStr as DocumentUcStr
			Inner join Drug as d on (DocumentUcStr.Drug_id = d.Drug_id)
			inner join DrugUnit du on d.DrugUnit_id = du.DrugUnit_id
			Inner join v_DocumentUc as DocumentUc on (DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id) and DocumentUc.DrugDocumentType_id<>6
			where (1=1)
			and (((DocumentUc.DocumentUc_didDate >= :begDate )or( :begDate is null)))and(((DocumentUc.DocumentUc_didDate <= :endDate )or( :endDate is null)))
			and (DocumentUc.Contragent_tid= :Contragent_id )
			and((DocumentUc.DrugFinance_id = :DrugFinance_id )or( :DrugFinance_id is null))
			group by 
			Rtrim(d.Drug_Name), 
			Rtrim(du.DrugUnit_Name), 
			DocumentUcSTR.DocumentUcSTR_Price, 
			d.Drug_id
			) as Prihod on (d.Drug_id = Prihod.val0 and Rtrim(d.Drug_Name) = Prihod.val1 and Rtrim(du.DrugUnit_Name) = Prihod.val2 and DocumentUcSTR.DocumentUcSTR_Price = Prihod.val3)

			left join 
			(select -- расход
				d.Drug_id as val0,
				Rtrim(d.Drug_Name)as val1,
				Rtrim(du.DrugUnit_Name) as val2,
				DocumentUcStrRash.DocumentUcStr_Price as val3,
				sum(DocumentUcStr.DocumentUcStr_RashCount) as val8,
				sum(DocumentUcStr.DocumentUcStr_Sum)as val9
			from v_DocumentUcStr as DocumentUcStr
			inner join DocumentUcStr DocumentUcStrRash on DocumentUcStr.DocumentUcStr_oid = DocumentUcStrRash.DocumentUcStr_id
			inner join DocumentUc DocumentUcRash on DocumentUcStrRash.DocumentUc_id = DocumentUcRash.DocumentUc_id and DocumentUcRash.DrugDocumentType_id<>6
			Inner join Drug as d on (DocumentUcStrRash.Drug_id = d.Drug_id)
			inner join DrugUnit du on d.DrugUnit_id = du.DrugUnit_id
			Inner join v_DocumentUc as DocumentUc on (DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id) and DocumentUc.DrugDocumentType_id<>6
			where (1=1)
			and(((DocumentUc.DocumentUc_didDate >= :begDate )or( :begDate is null)))and(((DocumentUc.DocumentUc_didDate <= :endDate )or( :endDate is null)))
			and(DocumentUc.Contragent_sid= :Contragent_id )
			and((DocumentUc.DrugFinance_id = :DrugFinance_id )or( :DrugFinance_id is null))
			and(DocumentUc.DrugDocumentType_id = 1)
			group by 
			Rtrim(d.Drug_Name), 
			Rtrim(du.DrugUnit_Name), 
			DocumentUcStrRash.DocumentUcStr_Price,
			d.Drug_id
			) as Rashod on (d.Drug_id = Rashod.val0 and Rtrim(d.Drug_Name) = Rashod.val1 and Rtrim(du.DrugUnit_Name) = Rashod.val2 and DocumentUcStr.DocumentUcStr_Price = Rashod.val3)

			left join 
			(select -- списание
				d.Drug_id as val0,
				Rtrim(d.Drug_Name)as val1,
				Rtrim(du.DrugUnit_Name) as val2,
				DocumentUcSTR.DocumentUcSTR_Price as val3,
				sum(DocumentUcStr.DocumentUcStr_Count) as val10,
				sum(DocumentUcStr.DocumentUcStr_Sum)as val11
			from v_DocumentUcStr as DocumentUcStr
			Inner join Drug as d on (DocumentUcStr.Drug_id = d.Drug_id)
			inner join DrugUnit du on d.DrugUnit_id = du.DrugUnit_id
			Inner join v_DocumentUc as DocumentUc on (DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id) and DocumentUc.DrugDocumentType_id<>6
			where (1=1)
			and(DocumentUc.DrugDocumentType_id = 2)
			and(((DocumentUc.DocumentUc_didDate >= :begDate )or( :begDate is null)))and(((DocumentUc.DocumentUc_didDate <= :endDate )or( :endDate is null)))
			and((DocumentUc.Contragent_sid= :Contragent_id or :Contragent_id is null))
			and((DocumentUc.DrugFinance_id = :DrugFinance_id )or( :DrugFinance_id is null))
			group by 
			Rtrim(d.Drug_Name), 
			Rtrim(du.DrugUnit_Name), 
			DocumentUcSTR.DocumentUcSTR_Price,
			d.Drug_id 
			) as Spisanie on (d.Drug_id = Spisanie.val0 and Rtrim(d.Drug_Name) = Spisanie.val1 and Rtrim(du.DrugUnit_Name) = Spisanie.val2 and DocumentUcSTR.DocumentUcSTR_Price = Spisanie.val3)

			left join 
			(select -- конечный остаток
				k.val0 as val0,
				k.val1 as val1,
				k.val2 as val2,
				k.val3,
				sum(k.val12) as val12,
				sum(k.val13) as val13
			from 
			(select 
			d.Drug_id as val0,
			Rtrim(d.Drug_Name)as val1,
			Rtrim(du.DrugUnit_Name) as val2,
			DocumentUcSTR.DocumentUcSTR_Price as val3,
			coalesce(DocumentUcStr.DocumentUcStr_Count,0) - coalesce(sum(DocumentUcStrRash.DocumentUcStr_RashCount),0) as val12,
			coalesce(DocumentUcStr.DocumentUcStr_Sum,0) - coalesce(sum(DocumentUcStrRash.DocumentUcStr_Sum),0) as val13
			from v_DocumentUcStr DocumentUcStr
			inner join Drug d on d.Drug_id = DocumentUcStr.Drug_id
			inner join DrugUnit du on d.DrugUnit_id = du.DrugUnit_id
			Inner join v_DocumentUc as DocumentUc on (DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id) and DocumentUc.DrugDocumentType_id<>6
			left outer join v_DocumentUcStr DocumentUcStrRash on DocumentUcStrRash.DocumentUcStr_oid = DocumentUcStr.DocumentUcStr_id
			left join v_DocumentUc DocumentUcRash on DocumentUcStrRash.DocumentUc_id = DocumentUcStr.DocumentUc_id
			 and ((DocumentUcRash.DocumentUc_didDate <= :endDate) or ( :endDate is null))
			where (1=1)
			and(DocumentUc.Contragent_tid = :Contragent_id )
			and((DocumentUc.DrugFinance_id = :DrugFinance_id)or( :DrugFinance_id is null))
			and((DocumentUc.DocumentUc_didDate <= :endDate)or ( :endDate is null))
			group by 
			DocumentUcStr.DocumentUcStr_id,
			d.Drug_id, 
			Rtrim(d.Drug_Name), 
			Rtrim(du.DrugUnit_Name), 
			DocumentUcSTR.DocumentUcSTR_Price,
			DocumentUcStr.DocumentUcStr_Count, 
			DocumentUcStr.DocumentUcStr_Sum
			having (coalesce(DocumentUcStr.DocumentUcStr_Count,0) - coalesce(sum(DocumentUcStrRash.DocumentUcStr_Count),0)) > 0
			) as k
			group by k.val1, k.val2, k.val3, k.val0
			) as OstKon on (d.Drug_id = OstKon.val0 and Rtrim(d.Drug_Name) = OstKon.val1 and Rtrim(du.DrugUnit_Name) = OstKon.val2 and DocumentUcSTR.DocumentUcSTR_Price = OstKon.val3)
			-- end from
			where 
			-- where
			(1=1)
			and ((DocumentUc.DrugFinance_id = :DrugFinance_id )or( :DrugFinance_id is null))
			and ((OstNach.val4 is not null and OstNach.val5 is not null) 
			   or (Prihod.val6 is not null and Prihod.val7 is not null) 
			   or (Rashod.val8 is not null and Rashod.val9 is not null)
			   or (Spisanie. val10 is not null and Spisanie.val11 is not null)
			   or (OstKon.val12 is not null and OstKon.val13 is not null))
			group by d.Drug_id, d.Drug_Name, du.DrugUnit_Name, DocumentUcStr.DocumentUcStr_Price, val4, val5, val6, val7, val8, val9, val10, val11, val12, val13
			-- end where
			order by 
			-- order by
			Rtrim(d.Drug_Name)
			-- end order by
		";

		// костыль для количества
		$cnt_query = "
			select
				-- select
				cast(d.Drug_id as varchar) || cast(DocumentUcSTR.DocumentUcSTR_Price as varchar) as \"row_id\",
				d.Drug_id as \"Drug_id\",
				Rtrim(d.Drug_Name) as \"val1\",
				Rtrim(du.DrugUnit_Name) as \"val2\",
				DocumentUcSTR.DocumentUcSTR_Price as \"val3\",
				round(OstNach.val4,5)as \"val4\",
				round(OstNach.val5,2)as \"val5\",
				round(Prihod.val6,5) as \"val6\",
				round(Prihod.val7,2) as \"val7\",
				round(Rashod.val8,5) as \"val8\",
				round(Rashod.val9,2) as \"val9\",
				round(Spisanie.val10,5) as \"val10\",
				round(Spisanie.val11,2) as \"val11\",
				round(OstKon.val12,5) as \"val12\",
				round(OstKon.val13,2) as \"val13\"
				-- end select
			from 
			-- from
			v_DocumentUcStr as DocumentUcStr 
			Inner join Drug as d on (DocumentUcStr.Drug_id = d.Drug_id)
			inner join DrugUnit du on d.DrugUnit_id = du.DrugUnit_id
			Inner join v_DocumentUc as DocumentUc on (DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id) and DocumentUc.DrugDocumentType_id<>6
			left join 
			(select -- начальный остаток
				p.val0 as val0,
				p.val1 as val1,
				p.val2 as val2,
				p.val3,
				sum(p.val4) as val4,
				sum(p.val5) as val5
			   from 
			(select
			d.Drug_id as val0,
			Rtrim(d.Drug_Name)as val1,
			Rtrim(du.DrugUnit_Name) as val2,
			DocumentUcSTR.DocumentUcSTR_Price as val3,
			coalesce(DocumentUcStr.DocumentUcStr_Count,0) - coalesce(sum(DocumentUcStrRash.DocumentUcStr_Count),0) as val4,
			coalesce(DocumentUcStr.DocumentUcStr_Sum,0) - coalesce(sum(DocumentUcStrRash.DocumentUcStr_Sum),0) as val5
			from v_DocumentUcStr DocumentUcStr
			inner join Drug d on d.Drug_id = DocumentUcStr.Drug_id
			inner join DrugUnit du on d.DrugUnit_id = du.DrugUnit_id
			Inner join v_DocumentUc as DocumentUc on (DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id) and DocumentUc.DrugDocumentType_id<>6
			left outer join v_DocumentUcStr DocumentUcStrRash on DocumentUcStrRash.DocumentUcStr_oid = DocumentUcStr.DocumentUcStr_id
			left join v_DocumentUc DocumentUcRash on DocumentUcStrRash.DocumentUc_id = DocumentUcRash.DocumentUc_id
			and ((DocumentUcRash.DocumentUc_didDate < :begDate ) or ( :begDate is null))
			where (1=1)
			and((DocumentUc.DrugFinance_id = :DrugFinance_id )or( :DrugFinance_id is null))
			and(DocumentUc.Contragent_tid = :Contragent_id )
			and((DocumentUc.DocumentUc_didDate < :begDate ) or (:begDate is null))
			group by 
			DocumentUcStr.DocumentUcStr_id,
			d.Drug_id,
			Rtrim(d.Drug_Name),
			Rtrim(du.DrugUnit_Name),
			DocumentUcSTR.DocumentUcSTR_Price,
			DocumentUcStr.DocumentUcStr_Count,
			DocumentUcStr.DocumentUcStr_Sum
			having (coalesce(DocumentUcStr.DocumentUcStr_Count,0) - coalesce(sum(DocumentUcStrRash.DocumentUcStr_Count),0)) > 0
			) as p
			group by p.val1, p.val2, p.val3,p.val0
			) as OstNach on (d.Drug_id = OstNach.val0 and Rtrim(d.Drug_Name) = OstNach.val1 and Rtrim(du.DrugUnit_Name) = OstNach.val2 and DocumentUcSTR.DocumentUcSTR_Price = OstNach.val3)

			left join 
			(select -- приход
				d.Drug_id as val0,
				Rtrim(d.Drug_Name)as val1,
				Rtrim(du.DrugUnit_Name) as val2,
				DocumentUcSTR.DocumentUcSTR_Price as val3,
				sum(DocumentUcStr.DocumentUcStr_Count) as val6,
				sum(DocumentUcStr.DocumentUcStr_Sum) as val7
			from v_DocumentUcStr as DocumentUcStr
			Inner join Drug as d on (DocumentUcStr.Drug_id = d.Drug_id)
			inner join DrugUnit du on d.DrugUnit_id = du.DrugUnit_id
			Inner join v_DocumentUc as DocumentUc on (DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id) and DocumentUc.DrugDocumentType_id<>6
			where (1=1)
			and (((DocumentUc.DocumentUc_didDate >= :begDate )or( :begDate is null)))and(((DocumentUc.DocumentUc_didDate <= :endDate )or( :endDate is null)))
			and (DocumentUc.Contragent_tid= :Contragent_id )
			and((DocumentUc.DrugFinance_id = :DrugFinance_id )or( :DrugFinance_id is null))
			group by 
			Rtrim(d.Drug_Name), 
			Rtrim(du.DrugUnit_Name), 
			DocumentUcSTR.DocumentUcSTR_Price, 
			d.Drug_id
			) as Prihod on (d.Drug_id = Prihod.val0 and Rtrim(d.Drug_Name) = Prihod.val1 and Rtrim(du.DrugUnit_Name) = Prihod.val2 and DocumentUcSTR.DocumentUcSTR_Price = Prihod.val3)

			left join 
			(select -- расход
				d.Drug_id as val0,
				Rtrim(d.Drug_Name)as val1,
				Rtrim(du.DrugUnit_Name) as val2,
				DocumentUcStrRash.DocumentUcStr_Price as val3,
				sum(DocumentUcStr.DocumentUcStr_RashCount) as val8,
				sum(DocumentUcStr.DocumentUcStr_Sum)as val9
			from v_DocumentUcStr as DocumentUcStr
			inner join DocumentUcStr DocumentUcStrRash on DocumentUcStr.DocumentUcStr_oid = DocumentUcStrRash.DocumentUcStr_id
			inner join DocumentUc DocumentUcRash on DocumentUcStrRash.DocumentUc_id = DocumentUcRash.DocumentUc_id and DocumentUcRash.DrugDocumentType_id<>6
			Inner join Drug as d on (DocumentUcStrRash.Drug_id = d.Drug_id)
			inner join DrugUnit du on d.DrugUnit_id = du.DrugUnit_id
			Inner join v_DocumentUc as DocumentUc on (DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id) and DocumentUc.DrugDocumentType_id<>6
			where (1=1)
			and(((DocumentUc.DocumentUc_didDate >= :begDate )or( :begDate is null)))and(((DocumentUc.DocumentUc_didDate <= :endDate )or( :endDate is null)))
			and(DocumentUc.Contragent_sid= :Contragent_id )
			and((DocumentUc.DrugFinance_id = :DrugFinance_id )or( :DrugFinance_id is null))
			and(DocumentUc.DrugDocumentType_id = 1)
			group by 
			Rtrim(d.Drug_Name), 
			Rtrim(du.DrugUnit_Name), 
			DocumentUcStrRash.DocumentUcStr_Price,
			d.Drug_id
			) as Rashod on (d.Drug_id = Rashod.val0 and Rtrim(d.Drug_Name) = Rashod.val1 and Rtrim(du.DrugUnit_Name) = Rashod.val2 and DocumentUcStr.DocumentUcStr_Price = Rashod.val3)

			left join 
			(select -- списание
				d.Drug_id as val0,
				Rtrim(d.Drug_Name)as val1,
				Rtrim(du.DrugUnit_Name) as val2,
				DocumentUcSTR.DocumentUcSTR_Price as val3,
				sum(DocumentUcStr.DocumentUcStr_Count) as val10,
				sum(DocumentUcStr.DocumentUcStr_Sum)as val11
			from v_DocumentUcStr as DocumentUcStr
			Inner join Drug as d on (DocumentUcStr.Drug_id = d.Drug_id)
			inner join DrugUnit du on d.DrugUnit_id = du.DrugUnit_id
			Inner join v_DocumentUc as DocumentUc on (DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id) and DocumentUc.DrugDocumentType_id<>6
			where (1=1)
			and(DocumentUc.DrugDocumentType_id = 2)
			and(((DocumentUc.DocumentUc_didDate >= :begDate )or( :begDate is null)))and(((DocumentUc.DocumentUc_didDate <= :endDate )or( :endDate is null)))
			and((DocumentUc.Contragent_sid= :Contragent_id or :Contragent_id is null))
			and((DocumentUc.DrugFinance_id = :DrugFinance_id )or( :DrugFinance_id is null))
			group by 
			Rtrim(d.Drug_Name), 
			Rtrim(du.DrugUnit_Name), 
			DocumentUcSTR.DocumentUcSTR_Price,
			d.Drug_id 
			) as Spisanie on (d.Drug_id = Spisanie.val0 and Rtrim(d.Drug_Name) = Spisanie.val1 and Rtrim(du.DrugUnit_Name) = Spisanie.val2 and DocumentUcSTR.DocumentUcSTR_Price = Spisanie.val3)

			left join 
			(select -- конечный остаток
				k.val0 as val0,
				k.val1 as val1,
				k.val2 as val2,
				k.val3,
				sum(k.val12) as val12,
				sum(k.val13) as val13
			from 
			(select 
			d.Drug_id as val0,
			Rtrim(d.Drug_Name)as val1,
			Rtrim(du.DrugUnit_Name) as val2,
			DocumentUcSTR.DocumentUcSTR_Price as val3,
			coalesce(DocumentUcStr.DocumentUcStr_Count,0) - coalesce(sum(DocumentUcStrRash.DocumentUcStr_RashCount),0) as val12,
			coalesce(DocumentUcStr.DocumentUcStr_Sum,0) - coalesce(sum(DocumentUcStrRash.DocumentUcStr_Sum),0) as val13
			from v_DocumentUcStr DocumentUcStr
			inner join Drug d on d.Drug_id = DocumentUcStr.Drug_id
			inner join DrugUnit du on d.DrugUnit_id = du.DrugUnit_id
			Inner join v_DocumentUc as DocumentUc on (DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id) and DocumentUc.DrugDocumentType_id<>6
			left outer join v_DocumentUcStr DocumentUcStrRash on DocumentUcStrRash.DocumentUcStr_oid = DocumentUcStr.DocumentUcStr_id
			left join v_DocumentUc DocumentUcRash on DocumentUcStrRash.DocumentUc_id = DocumentUcStr.DocumentUc_id
			 and ((DocumentUcRash.DocumentUc_didDate <= :endDate) or ( :endDate is null))
			where (1=1)
			and(DocumentUc.Contragent_tid = :Contragent_id )
			and((DocumentUc.DrugFinance_id = :DrugFinance_id)or( :DrugFinance_id is null))
			and((DocumentUc.DocumentUc_didDate <= :endDate)or ( :endDate is null))
			group by 
			DocumentUcStr.DocumentUcStr_id,
			d.Drug_id, 
			Rtrim(d.Drug_Name), 
			Rtrim(du.DrugUnit_Name), 
			DocumentUcSTR.DocumentUcSTR_Price,
			DocumentUcStr.DocumentUcStr_Count, 
			DocumentUcStr.DocumentUcStr_Sum
			having (coalesce(DocumentUcStr.DocumentUcStr_Count,0) - coalesce(sum(DocumentUcStrRash.DocumentUcStr_Count),0)) > 0
			) as k
			group by k.val1, k.val2, k.val3, k.val0
			) as OstKon on (d.Drug_id = OstKon.val0 and Rtrim(d.Drug_Name) = OstKon.val1 and Rtrim(du.DrugUnit_Name) = OstKon.val2 and DocumentUcSTR.DocumentUcSTR_Price = OstKon.val3)
			-- end from
			where 
			-- where
			(1=1)
			and ((DocumentUc.DrugFinance_id = :DrugFinance_id )or( :DrugFinance_id is null))
			and ((OstNach.val4 is not null and OstNach.val5 is not null) 
			   or (Prihod.val6 is not null and Prihod.val7 is not null) 
			   or (Rashod.val8 is not null and Rashod.val9 is not null)
			   or (Spisanie. val10 is not null and Spisanie.val11 is not null)
			   or (OstKon.val12 is not null and OstKon.val13 is not null))
			-- end where
			group by 
			-- group by
			d.Drug_id, d.Drug_Name, du.DrugUnit_Name, DocumentUcStr.DocumentUcStr_Price, val4, val5, val6, val7, val8, val9, val10, val11, val12, val13
			-- end group by
			order by 
			-- order by
			Rtrim(d.Drug_Name)
			-- end order by
		";

		$response = array();

		$get_count_query = getCountSQLPH($cnt_query, 'cast(d.Drug_id as varchar) || cast(DocumentUcSTR.DocumentUcSTR_Price as varchar)', 'distinct');
		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if ( is_object($get_count_result) ) {
			$response['data'] = array();
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		}
		else {
			return false;
		}

		//if ( $print === false && isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0 ) {
		if ( isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0 ) {
			$query = getLimitSQLPH($query, $data['start'], $data['limit'], 'distinct', 'ORDER BY val1');
		}
		//echo getDebugSQL($query, $queryParams);
		//die();
		//if ( $getCount == false )

		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		} else {
			return false;
		}


		return $response;
	}

	/**
	 * Получение остатков на дату
	 */
	function loadDrugOstatByFilters($data) {
		$queryParams = array();
		$select = '';
		$join = '';
		$filters = '';

		$queryParams['Contragent_id'] = $data['Contragent_id'];

		if (isset($data['Drug_id']) && $data['Drug_id'] > 0) {
			$filters .= ' and od.Drug_id = :Drug_id';
			$queryParams['Drug_id'] = $data['Drug_id'];
		}

		if (isset($data['DrugFinance_id']) && $data['DrugFinance_id'] != '' && $data['DrugFinance_id'] > 0) {
			$filters .= ' and od.DrugFinance_id = :DrugFinance_id';
			$queryParams['DrugFinance_id'] = $data['DrugFinance_id'];
		}

		if (isset($data['WhsDocumentCostItemType_id']) && $data['WhsDocumentCostItemType_id'] != '' && $data['WhsDocumentCostItemType_id'] > 0) {
			$filters .= ' and od.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
			$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}

		if (!empty($data['STRONGGROUPS_ID'])) {
			$filters .= ' and am.STRONGGROUPID = :STRONGGROUPS_ID';
			$queryParams['STRONGGROUPS_ID'] = $data['STRONGGROUPS_ID'];
		}

		if (!empty($data['NARCOGROUPS_ID'])) {
			$filters .= ' and am.NARCOGROUPID = :NARCOGROUPS_ID';
			$queryParams['NARCOGROUPS_ID'] = $data['NARCOGROUPS_ID'];
		}

		if (!empty($data['CLSATC_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with Rec(CLSATC_ID)
				as
				(
					select t.CLSATC_ID
					from rls.v_CLSATC t
					where
						t.CLSATC_ID = :CLSATC_ID
					union all
					select t.CLSATC_ID
					from rls.v_CLSATC t
						join Rec R on t.PARENTID = R.CLSATC_ID
				)
				select
					R.CLSATC_ID as \"CLSATC_ID\"
				from Rec R
			";
			$result = $this->db->query($query,array(
				'CLSATC_ID' => $data['CLSATC_ID']
			));
			if (is_object($result)) {
				$res_arr = $result->result('array');
				if (is_array($res_arr) && !empty($res_arr)) {
					$atc_arr = array();
					foreach($res_arr as $row) {
						$atc_arr[] = $row['CLSATC_ID'];
					}
					$atc_arr = empty($atc_arr)?'null':implode(',', $atc_arr);

					$join .= ' inner join rls.v_PREP_ATC pa on pa.PREPID = d.DrugPrep_id';
					$filters .= " and pa.UNIQID in ({$atc_arr})";
				}
			}
		}

		if (!empty($data['CLSPHARMAGROUP_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with Rec(CLSPHARMAGROUP_ID)
				as
				(
					select t.CLSPHARMAGROUP_ID
					from rls.v_CLSPHARMAGROUP t
					where
						t.CLSPHARMAGROUP_ID = :CLSPHARMAGROUP_ID
					union all
					select t.CLSPHARMAGROUP_ID
					from rls.v_CLSPHARMAGROUP t
						join Rec R on t.PARENTID = R.CLSPHARMAGROUP_ID
				)
				select
					R.CLSPHARMAGROUP_ID as \"CLSPHARMAGROUP_ID\"
				from Rec R
			";
			$result = $this->db->query($query,array(
				'CLSPHARMAGROUP_ID' => $data['CLSPHARMAGROUP_ID']
			));
			if (is_object($result)) {
				$res_arr = $result->result('array');
				if (is_array($res_arr) && !empty($res_arr)) {
					$ph_gr_arr = array();
					foreach($res_arr as $row) {
						$ph_gr_arr[] = $row['CLSPHARMAGROUP_ID'];
					}
					$ph_gr_str = empty($ph_gr_arr)?'null':implode(',', $ph_gr_arr);

					$join .= ' inner join rls.v_PREP_PHARMAGROUP pp on pp.PREPID = d.DrugPrep_id';
					$filters .= " and pp.UNIQID in ({$ph_gr_str})";
				}
			}
		}

		if (!empty($data['CLS_MZ_PHGROUP_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with Rec(CLS_MZ_PHGROUP_ID)
				as
				(
					select t.CLS_MZ_PHGROUP_ID
					from rls.v_CLS_MZ_PHGROUP t
					where
						t.CLS_MZ_PHGROUP_ID = :CLS_MZ_PHGROUP_ID
					union all
					select t.CLS_MZ_PHGROUP_ID
					from rls.v_CLS_MZ_PHGROUP t
						join Rec R on t.PARENTID = R.CLS_MZ_PHGROUP_ID
				)
				select
					R.CLS_MZ_PHGROUP_ID as \"CLS_MZ_PHGROUP_ID\"
				from Rec R
			";
			$result = $this->db->query($query,array(
				'CLS_MZ_PHGROUP_ID' => $data['CLS_MZ_PHGROUP_ID']
			));
			if (is_object($result)) {
				$res_arr = $result->result('array');
				if (is_array($res_arr) && !empty($res_arr)) {
					$mz_pg_arr = array();
					foreach($res_arr as $row) {
						$mz_pg_arr[] = $row['CLS_MZ_PHGROUP_ID'];
					}
					$mz_pg_str = empty($mz_pg_arr)?'null':implode(',', $mz_pg_arr);

					$join .= ' inner join rls.TRADENAMES_DRUGFORMS td on td.TRADENAMEID = p.TRADENAMEID and td.DRUGFORMID = dcm.CLSDRUGFORMS_ID';
					$filters .= " and td.MZ_PHGR_ID in ({$mz_pg_str})";
				}
			}
		}

		if (empty($data['searchByDrug']) || !$data['searchByDrug']) {
			$select .= ",ps.PrepSeries_id as \"PrepSeries_id\",
			ps.PrepSeries_Ser as \"PrepSeries_Ser\",
			block.PrepBlockCause_id as \".PrepBlockCause_id\",
			block.PrepBlockCause_Name as \".PrepBlockCause_Name\"";
			$join .= " left join rls.v_PrepSeries ps on ps.PrepSeries_id = dus.PrepSeries_id";
			$join .= " left join lateral(
				select
					pb.PrepBlock_id,
					pbc.PrepBlockCause_id,
					pbc.PrepBlockCause_Code,
					pbc.PrepBlockCause_Name
				from rls.v_PrepBlock pb
				left join rls.v_PrepBlockCause pbc on pbc.PrepBlockCause_id = pb.PrepBlockCause_id
				where
					pb.PrepSeries_id = ps.PrepSeries_id
					and pb.PrepBlock_begDate <= dbo.tzGetDate()
					and (pb.PrepBlock_endDate is null or pb.PrepBlock_endDate > dbo.tzGetDate())
				order by
					pb.PrepBlock_begDate desc
				limit 1
			) block on true";
		}

		//без условия "du.Contragent_id = :Contragent_id", которое в данный момент закомментировано, в новых армах (фармацевта и оп. склада) остатки будут двоиться R. Salakhov
		$query = "
			select
				-- select
				od.DocumentUcStr_id as \"DocumentUcStr_id\",
				d.Drug_id as \"Drug_id\",
				RTRIM(coalesce(d.Drug_Name, '')) as \"Drug_Name\",
				to_char(od.DocumentUcStr_godnDate, 'dd.mm.yyyy') as \"godnDate\",
				RTRIM(coalesce(d.Drug_PackName, '')) as \"unit\",
				od.DocumentUcStr_Price as \"Price\",
				od.DocumentUcStr_PriceR as \"PriceR\",
				coalesce(od.DocumentUcStr_Ost,0) as ostat,
				(coalesce(od.DocumentUcStr_Ost,0) * coalesce(od.DocumentUcStr_Price,0)) as \"Sum\",
				(coalesce(od.DocumentUcStr_Ost,0) * coalesce(od.DocumentUcStr_PriceR,0)) as \"SumR\",
				od.DrugFinance_id as \"DrugFinance_id\",
				od.DrugFinance_Name as \"DrugFinance_Name\",
				od.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				od.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				'' as \"quantity\",
				am.STRONGGROUPID as \"STRONGGROUPID\"
				{$select}
				-- end select
			from 
				-- from
				v_DocumentUcOst_Lite od
				inner join rls.v_Drug d on d.Drug_id = od.Drug_id
				left join DocumentUcStr dus on dus.DocumentUcStr_id = od.DocumentUcStr_id
				left join DocumentUc du on du.DocumentUc_id = od.DocumentUc_id
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
				left join rls.v_prep p on p.Prep_id = d.DrugPrep_id
				{$join}
				-- end from
			where
				-- where
				od.Contragent_tid = :Contragent_id and od.DocumentUcStr_Ost <> 0
				{$filters}
				-- end where
			order by 
				-- order by
				d.Drug_NameIndex
				-- end order by
		";
		// --du.Contragent_id = :Contragent_id and
		//echo getDebugSQL($query, $queryParams);exit;
		$response = array();
		$get_count_query = getCountSQLPH($query);
		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if ( is_object($get_count_result) ) {
			$response['data'] = array();
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		} else {
			return false;
		}


		if ( isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0 ) {
			$query = getLimitSQLPH($query, $data['start'], $data['limit']);
		}
		//echo getDebugSQL($query, $queryParams); die();

		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		} else {
			return false;
		}

		return $response;
	}
}
