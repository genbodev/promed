<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Pskov_Dlo_EvnRecept_model - модель, для работы с таблицей EvnRecept. Версия для Пскова
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version      31.05.2013
 */

require_once(APPPATH.'models/_pgsql/Dlo_EvnRecept_model.php');

class Pskov_Dlo_EvnRecept_model extends Dlo_EvnRecept_model {
	/**
	 * Возвращает номер для нового рецепта в Пскове (автонумерация)
	 */
	function getReceptNumber($data) {
		$query = "
			select
				case
					when wdcit.WhsDocumentCostItemType_Code = 3 then '5'
					when df.DrugFinance_Code = 1 then '7'
					when df.DrugFinance_Code = 2 then '6'
					else '0'
				end ||
				lpad(left(CAST(l.Lpu_Ouz as varchar), 3), 3, '0') ||
				'01' ||
				lpad(COALESCE(er.EvnRecept_Num, ''), 7, '0') || COALESCE(er.EvnRecept_Num, '') as \"rnumber\"
			from v_Lpu l 
				LEFT JOIN LATERAL (
					select WhsDocumentCostItemType_Code, DrugFinance_id
					from v_WhsDocumentCostItemType 
					where WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
                    limit 1
				) wdcit ON true
				LEFT JOIN LATERAL (
					select DrugFinance_Code
					from v_DrugFinance 
					where DrugFinance_id = wdcit.DrugFinance_id
                    limit 1
				) df ON true
				LEFT JOIN LATERAL (
					select cast(COALESCE(max(cast(right(t1.EvnRecept_Num, 7) as bigint)), 0) + 1 as varchar(7)) as EvnRecept_Num
					from v_EvnRecept t1
						inner join v_DrugFinance t2 on t2.DrugFinance_id = t1.DrugFinance_id
						inner join v_WhsDocumentCostItemType t3 on t3.WhsDocumentCostItemType_id = t1.WhsDocumentCostItemType_id
					where t1.Lpu_id = :Lpu_id
						and (
							(t3.WhsDocumentCostItemType_Code = 3 and wdcit.WhsDocumentCostItemType_Code = 3) -- ВЗН
							or (t2.DrugFinance_id = wdcit.DrugFinance_id and wdcit.WhsDocumentCostItemType_Code != 3)
						)
						and length(COALESCE(t1.EvnRecept_Num, '')) = 13
						and substring(t1.EvnRecept_Num, 5, 2) = '01'
						and date_part('YEAR', t1.EvnRecept_setDate) = date_part('YEAR', CAST(:EvnRecept_setDate as date))
				) er ON true
			where
				l.Lpu_id = :Lpu_id
			limit 1    
		";

		$params = array(
			'EvnRecept_setDate' => $data['EvnRecept_setDate']
		,'Lpu_id' => $data['Lpu_id']
		,'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']
		);
		// echo getDebugSQL($query, $params); die();
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка данных в форму редактирования рецепта
	 */
	function loadEvnReceptEditForm($data) {
		$queryParams = array();

		if ( !isMinZdrav() && !isFarmacy() ) {
			$lpu_filter = "and ER.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		else {
			$lpu_filter = "";
		}

		$query = "
			SELECT 
				 er.Diag_id as \"Diag_id\"
				,er.Drug_rlsid as \"Drug_rlsid\"
				,d.DrugComplexMnn_id as \"DrugComplexMnn_id\"
				,er.DrugFinance_id as \"DrugFinance_id\"
				,er.EvnRecept_id as \"EvnRecept_id\"
				,er.Lpu_id as \"Lpu_id\"
				,COALESCE(er.EvnRecept_Is7Noz, 1) as \"EvnRecept_Is7Noz\"
				,er.EvnRecept_IsKEK as \"EvnRecept_IsKEK\"
				,er.EvnRecept_IsMnn as \"EvnRecept_IsMnn\"
				,COALESCE(er.EvnRecept_IsSigned, 1) as \"EvnRecept_IsSigned\"
				,round(er.EvnRecept_Kolvo, 2) as \"EvnRecept_Kolvo\"
				,RTRIM(er.EvnRecept_Num) as \"EvnRecept_Num\"
				,er.EvnRecept_pid as \"EvnRecept_pid\"
				,RTRIM(er.EvnRecept_Ser) as \"EvnRecept_Ser\"
				,to_char(er.EvnRecept_setDT, 'DD.MM.YYYY') as \"EvnRecept_setDate\"
				,RTRIM(er.EvnRecept_Signa) as \"EvnRecept_Signa\"
				,er.LpuSection_id as \"LpuSection_id\"
				,er.MedPersonal_id as \"MedPersonal_id\"
				,er.OrgFarmacy_id as \"OrgFarmacy_id\"
				,er.Person_id as \"Person_id\"
				,er.PersonEvn_id as \"PersonEvn_id\"
				,er.PrivilegeType_id as \"PrivilegeType_id\"
				,er.ReceptDiscount_id as \"ReceptDiscount_id\"
				,er.ReceptFinance_id as \"ReceptFinance_id\"
				,er.ReceptType_id as \"ReceptType_id\"
				,er.ReceptValid_id as \"ReceptValid_id\"
				,er.Server_id as \"Server_id\"
				,er.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
				,er.WhsDocumentUc_id as \"WhsDocumentUc_id\"
				,wdss.WhsDocumentSupplySpec_PriceNDS as \"Drug_Price\"
				,er.DrugComplexMnn_id as \"DrugComplexMnn_id\"
				,er.ReceptDelayType_id as \"ReceptWrongDelayType_id\"
				,to_char(wr.ReceptWrong_insDT, 'DD.MM.YYYY') as \"ReceptWrong_DT\"
				,wr.ReceptWrong_Decr as \"ReceptWrong_Decr\"
			FROM v_EvnRecept_all er 
				left join rls.v_Drug d  on d.Drug_id = er.Drug_rlsid
				LEFT JOIN LATERAL (
					select 
						WhsDocumentSupplySpec_PriceNDS
					from
						v_WhsDocumentSupplySpec
					where
						WhsDocumentSupply_id = er.WhsDocumentUc_id and
						Drug_id = er.Drug_rlsid
                    limit 1
				) wdss ON true
				left join ReceptWrong wr  on wr.EvnRecept_id = er.EvnRecept_id
			WHERE
				er.EvnRecept_id = :EvnRecept_id            
				" . $lpu_filter . "
			LIMIT 1
		";

		$queryParams['EvnRecept_id'] = $data['EvnRecept_id'];

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
