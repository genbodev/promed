<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Buryatiya_Dlo_EvnRecept_model - модель, для работы с таблицей EvnRecept. Версия для Бурятии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Bykov Stanislav (savage@swan.perm.ru)
* @version      15.10.2014
*/

require_once(APPPATH.'models/_pgsql/Dlo_EvnRecept_model.php');

class Buryatiya_Dlo_EvnRecept_model extends Dlo_EvnRecept_model {
	/**
	* Возвращает номер для нового рецепта в Бурятии (автонумерация)
	*/
	function getReceptNumberRls($data) {

		$query = "
			select
				case
					when wdcit.WhsDocumentCostItemType_Code = 3 then '5'
					when df.DrugFinance_SysNick = 'fed' then '7'
					when df.DrugFinance_SysNick = 'reg' then '6'
					else '0'
				end ||
				repeate('0', 3 - length(coalesce(left(l.Lpu_Ouz, 3), ''))) || coalesce(left(l.Lpu_Ouz, 3), '') ||
				'01' ||
				repeate('0', 7 - length(coalesce(er.EvnRecept_Num, ''))) || coalesce(er.EvnRecept_Num, '') as  rnumber
			from v_Lpu l
				left join lateral (
					select WhsDocumentCostItemType_Code, DrugFinance_id
					from v_WhsDocumentCostItemType 
					where WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
					limit 1
				) wdcit on true
				left join lateral (
					select  DrugFinance_SysNick
					from v_DrugFinance 
					where DrugFinance_id = wdcit.DrugFinance_id
					limit 1
				) df on true
				left join lateral (
					select to_char(coalesce(max(cast(right(t1.EvnRecept_Num, 7) as bigint)), 0) + 1 as varchar(7)) as \"EvnRecept_Num\",
					from v_EvnRecept t1
						inner join v_DrugFinance t2 on t2.DrugFinance_id = t1.DrugFinance_id
						inner join v_WhsDocumentCostItemType t3 on t3.WhsDocumentCostItemType_id = t1.WhsDocumentCostItemType_id
					where t1.Lpu_id = :Lpu_id
						and (
							(t3.WhsDocumentCostItemType_Code = 3 and wdcit.WhsDocumentCostItemType_Code = 3) -- ВЗН
							or (t2.DrugFinance_id = wdcit.DrugFinance_id and wdcit.WhsDocumentCostItemType_Code != 3)
						)
						and length(coalesce(t1.EvnRecept_Num, '')) = 13
						and substring(t1.EvnRecept_Num, 5, 2) = '01'
						and isnumeric(t1.EvnRecept_Num) = 1
				) er on true
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
}
