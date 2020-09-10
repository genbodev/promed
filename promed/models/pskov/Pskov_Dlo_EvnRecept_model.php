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

require_once(APPPATH.'models/Dlo_EvnRecept_model.php');

class Pskov_Dlo_EvnRecept_model extends Dlo_EvnRecept_model {
	/**
	 * Возвращает номер для нового рецепта в Пскове (автонумерация)
	 */
	function getReceptNumber($data) {
		$query = "
			select top 1
				case
					when wdcit.WhsDocumentCostItemType_Code = 3 then '5'
					when df.DrugFinance_Code = 1 then '7'
					when df.DrugFinance_Code = 2 then '6'
					else '0'
				end +
				replicate('0', 3 - len(isnull(left(l.Lpu_Ouz, 3), ''))) + isnull(left(l.Lpu_Ouz, 3), '') +
				'01' +
				replicate('0', 7 - len(isnull(er.EvnRecept_Num, ''))) + isnull(er.EvnRecept_Num, '') as rnumber
			from v_Lpu l with (nolock)
				outer apply (
					select top 1 WhsDocumentCostItemType_Code, DrugFinance_id
					from v_WhsDocumentCostItemType with (nolock)
					where WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
				) wdcit
				outer apply (
					select top 1 DrugFinance_Code
					from v_DrugFinance with (nolock)
					where DrugFinance_id = wdcit.DrugFinance_id
				) df
				outer apply (
					select cast(isnull(max(cast(right(t1.EvnRecept_Num, 7) as bigint)), 0) + 1 as varchar(7)) as EvnRecept_Num
					from v_EvnRecept t1
						inner join v_DrugFinance t2 on t2.DrugFinance_id = t1.DrugFinance_id
						inner join v_WhsDocumentCostItemType t3 on t3.WhsDocumentCostItemType_id = t1.WhsDocumentCostItemType_id
					where t1.Lpu_id = :Lpu_id
						and (
							(t3.WhsDocumentCostItemType_Code = 3 and wdcit.WhsDocumentCostItemType_Code = 3) -- ВЗН
							or (t2.DrugFinance_id = wdcit.DrugFinance_id and wdcit.WhsDocumentCostItemType_Code != 3)
						)
						and len(isnull(t1.EvnRecept_Num, '')) = 13
						and substring(t1.EvnRecept_Num, 5, 2) = '01'
						and YEAR(t1.EvnRecept_setDate) = YEAR(:EvnRecept_setDate)
				) er
			where
				l.Lpu_id = :Lpu_id
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
			SELECT TOP 1
				 er.Diag_id
				,er.Drug_rlsid
				,d.DrugComplexMnn_id
				,er.DrugFinance_id
				,er.EvnRecept_id
				,er.Lpu_id
				,ISNULL(er.EvnRecept_Is7Noz, 1) as EvnRecept_Is7Noz
				,er.EvnRecept_IsKEK
				,er.EvnRecept_IsMnn
				,ISNULL(er.EvnRecept_IsSigned, 1) as EvnRecept_IsSigned
				,round(er.EvnRecept_Kolvo, 2) as EvnRecept_Kolvo
				,RTRIM(er.EvnRecept_Num) as EvnRecept_Num
				,er.EvnRecept_pid
				,RTRIM(er.EvnRecept_Ser) as EvnRecept_Ser
				,convert(varchar(10), er.EvnRecept_setDT, 104) as EvnRecept_setDate
				,RTRIM(er.EvnRecept_Signa) as EvnRecept_Signa
				,er.LpuSection_id
				,er.MedPersonal_id
				,er.OrgFarmacy_id
				,er.Person_id
				,er.PersonEvn_id
				,er.PrivilegeType_id
				,er.ReceptDiscount_id
				,er.ReceptFinance_id
				,er.ReceptType_id
				,er.ReceptValid_id
				,er.Server_id
				,er.WhsDocumentCostItemType_id
				,er.WhsDocumentUc_id
				,wdss.WhsDocumentSupplySpec_PriceNDS as Drug_Price
				,er.DrugComplexMnn_id
				,er.ReceptDelayType_id as ReceptWrongDelayType_id
				,convert(varchar(10), wr.ReceptWrong_insDT, 104) ReceptWrong_DT
				,wr.ReceptWrong_Decr
			FROM v_EvnRecept_all er with (nolock)
				left join rls.v_Drug d with (nolock) on d.Drug_id = er.Drug_rlsid
				outer apply (
					select top 1
						WhsDocumentSupplySpec_PriceNDS
					from
						v_WhsDocumentSupplySpec
					where
						WhsDocumentSupply_id = er.WhsDocumentUc_id and
						Drug_id = er.Drug_rlsid
				) wdss
				left join ReceptWrong wr with (nolock) on wr.EvnRecept_id = er.EvnRecept_id
			WHERE
				er.EvnRecept_id = :EvnRecept_id
				" . $lpu_filter . "
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
