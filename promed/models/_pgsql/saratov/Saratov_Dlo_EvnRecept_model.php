<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Saratov_Dlo_EvnRecept_model - модель, для работы с таблицей EvnRecept. Версия для Саратова
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

class Saratov_Dlo_EvnRecept_model extends Dlo_EvnRecept_model {

	/**
	 * Получение условия по просроченным рецептам для запроса
	 */
	function getReceptValidCondition() {
		return "
			dbo.tzGetDate() >= case
				when RV.ReceptValid_Code = 1 then evnRecept_all.EvnRecept_setDate + interval '1 month'
				when RV.ReceptValid_Code = 2 then evnRecept_all.EvnRecept_setDate + interval '3 months'
				when RV.ReceptValid_Code = 3 then evnRecept_all.EvnRecept_setDate + interval '14 months'
				when RV.ReceptValid_Code = 4 then evnRecept_all.EvnRecept_setDate + interval '5 months'
				when RV.ReceptValid_Code = 5 then evnRecept_all.EvnRecept_setDate + interval '2 months'
				when RV.ReceptValid_Code = 7 then evnRecept_all.EvnRecept_setDate + interval '10 months'
				when RV.ReceptValid_Code = 8 then evnRecept_all.EvnRecept_setDate + interval '60 months'
				when RV.ReceptValid_Code = 9 then evnRecept_all.EvnRecept_setDate + interval '30 months'
				when RV.ReceptValid_Code = 10 then evnRecept_all.EvnRecept_setDate + interval '90 months'
				when RV.ReceptValid_Code = 11 then evnRecept_all.EvnRecept_setDate + interval '15 months'
			end
			and EvnRecept.EvnRecept_otpDT is null
		";
	}

	/**
	* Возвращает номер для нового рецепта в Саратове (автонумерация)
	*/
	function getReceptNumber($data) {
		$query = "
			select
				case
					when wdcit.WhsDocumentCostItemType_Code = 3 then '5'
					when df.DrugFinance_SysNick = 'fed' then '7'
					when df.DrugFinance_SysNick = 'reg' then '6'
					else '0'
				end ||
				repeat('0', 3 - length(coalesce(left(l.Lpu_Ouz, 3), ''))) || coalesce(left(l.Lpu_Ouz, 3), '') ||
				'01' ||
				repeat('0', 7 - length(coalesce(er.EvnRecept_Num, ''))) + coalesce(er.EvnRecept_Num, '') as \"rnumber\"
			from v_Lpu l
				left join lateral (
					select WhsDocumentCostItemType_Code, DrugFinance_id
					from v_WhsDocumentCostItemType
					where WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
					limit 1
				) wdcit on true
				left join lateral (
					select DrugFinance_SysNick
					from v_DrugFinance
					where DrugFinance_id = wdcit.DrugFinance_id
					limit 1
				) df on true
				left join lateral (
					select cast(coalesce(max(cast(right(t1.EvnRecept_Num, 7) as bigint)), 0) + 1 as varchar(7)) as EvnRecept_Num
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
						and t1.EvnRecept_Num ~ '^[0-9]+'
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

	/**
	 * Загрузка данных в форму редактирования рецепта
	 */
	function loadEvnReceptEditForm($data) {
		$queryParams = array();

		if ( !isMinZdrav() && !isFarmacy()&&$data['Lpu_id']!="" ) {
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
				,ERec.ReceptForm_id as \"ReceptForm_id\"
				,coalesce(er.EvnRecept_Is7Noz, 1) as \"EvnRecept_Is7Noz\"
				,er.EvnRecept_IsKEK as \"EvnRecept_IsKEK\"
				,er.EvnRecept_IsMnn as \"EvnRecept_IsMnn\"
				,coalesce(er.EvnRecept_IsSigned, 1) as \"EvnRecept_IsSigned\"
				,round(er.EvnRecept_Kolvo, 2) as \"EvnRecept_Kolvo\"
				,RTRIM(er.EvnRecept_Num) as \"EvnRecept_Num\"
				,er.EvnRecept_pid as \"EvnRecept_pid\"
				,RTRIM(er.EvnRecept_Ser) as \"EvnRecept_Ser\"
				,to_char(er.EvnRecept_setDT, 'dd.mm.yyyy') as \"EvnRecept_setDate\"
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
				,er.ReceptDelayType_id as \"ReceptDelayType_id\"
				,coalesce(er.DrugComplexMnn_id,d.DrugComplexMnn_id) as \"DrugComplexMnn_id\"
				,ERec.ReceptDelayType_id as \"ReceptWrongDelayType_id\"
				,to_char(wr.ReceptWrong_insDT, 'dd.mm.yyyy') as \"ReceptWrong_DT\"
				,wr.ReceptWrong_Decr as \"ReceptWrong_Decr\"
				,coalesce(RDT.ReceptDelayType_Code,-1) as \"Recept_Result_Code\"
				,'' as \"Recept_Result\"
				,'' as \"Recept_Delay_Info\"
				,'' as \"EvnRecept_Drugs\"
				,to_char(RO.ReceptOtov_insDT, 'dd.mm.yyyy') as \"ReceptOtov_insDT\"
				,to_char(RO.EvnRecept_obrDate, 'dd.mm.yyyy') as \"ReceptOtov_obrDate\"
				,to_char(RO.EvnRecept_otpDate, 'dd.mm.yyyy') as \"ReceptOtov_otpDate\"
				,coalesce(OrgF.Org_Name,'') as \"ReceptOtov_Farmacy\"
				,coalesce(er.EvnRecept_deleted,1) as \"EvnRecept_deleted\"
			FROM v_EvnRecept_all er
				left join rls.v_Drug d on d.Drug_id = er.Drug_rlsid
				left join v_EvnRecept ERec on ERec.EvnRecept_id = er.EvnRecept_id
				left join v_ReceptDelayType RDT on RDT.ReceptDelayType_id = ERec.ReceptDelayType_id
				left join ReceptOtov RO on RO.EvnRecept_id = ERec.EvnRecept_id
				left join v_OrgFarmacy OrgF on OrgF.OrgFarmacy_id = RO.OrgFarmacy_id
				left join lateral (
					select
						WhsDocumentSupplySpec_PriceNDS
					from
						v_WhsDocumentSupplySpec
					where
						WhsDocumentSupply_id = er.WhsDocumentUc_id and
						Drug_id = er.Drug_rlsid
					limit 1
				) wdss on true
				left join ReceptWrong wr on wr.EvnRecept_id = er.EvnRecept_id
			WHERE
				er.EvnRecept_id = :EvnRecept_id
				" . $lpu_filter . "
			LIMIT 1
		";

		$queryParams['EvnRecept_id'] = $data['EvnRecept_id'];

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$result = $result->result('array');
			$result[0]['ReceptOtov_Date'] = '';
			if($result[0]['EvnRecept_deleted'] == 2)
				$result[0]['Recept_Result_Code'] = 4;
			if(count($result) > 0){
				switch ($result[0]['Recept_Result_Code'])
				{
					case 0:
						$result[0]['Recept_Result'] = 'Обслужен';
						$query_drug = "
							select distinct coalesce(D.Drug_Name,'') as \"Drug_Name\"
							from ReceptOtov RO
							left join rls.v_Drug D on D.Drug_id = RO.Drug_cid
							where RO.EvnRecept_id = :EvnRecept_id
						";
						$result_drug = $this->db->query($query_drug,$queryParams);
						if(is_object($result_drug)){
							$result_drug = $result_drug->result('array');
							for ($i=0; $i < count($result_drug); $i++){
								$result[0]['EvnRecept_Drugs'] .= $result_drug[$i]['Drug_Name'].PHP_EOL;
							}
						}
						$result[0]['ReceptOtov_Date'] = $result[0]['ReceptOtov_otpDate'];
						break;
					case 1:
						$result[0]['Recept_Result'] = 'На отсроченном обеспечении';
						$result[0]['Recept_Delay_Info'] = 'Рецепт на отсроченном обеспечении '.$result[0]['ReceptDelay_1_days'].' дн.';
						$result[0]['ReceptOtov_Date'] = $result[0]['ReceptOtov_obrDate'];
						break;
					case 2:
						$result[0]['Recept_Result'] = 'Признан неправильно выписанным';
						$query_info = "
							select
								to_char(RW.ReceptWrong_insDT, 'dd.mm.yyyy') as \"Wrong_Date\",
								coalesce(RW.ReceptWrong_Decr,'') as \"Wrong_Cause\"
							from v_ReceptWrong RW
							where RW.EvnRecept_id = :EvnRecept_id
							limit 1
						";
						$result_info = $this->db->query($query_info,$queryParams);
						if(is_object($result_info)){
							$result_info = $result_info->result('array');
							if(count($result_info) > 0){
								$result[0]['Recept_Delay_Info'] = 'От '.$result_info[0]['Wrong_Date'].'. Причина: '.$result_info[0]['Wrong_Cause'];
							}
						}
						break;
					case 4:
						$result[0]['Recept_Result'] = 'Удален';
						$query_info = "
							select
								RRCT.ReceptRemoveCauseType_Name as \"Del_Cause\",
								RTRIM(PUC.PMUser_Name) as \"Del_User\",
								to_char(ER.EvnRecept_updDT, 'dd.mm.yyyy') as \"Del_Date\"
							from v_EvnRecept_all ER
							left join v_pmUserCache PUC on PUC.pmUser_id = ER.pmUser_updID
							left join v_ReceptRemoveCauseType RRCT on RRCT.ReceptRemoveCauseType_id = ER.ReceptRemoveCauseType_id
							where ER.EvnRecept_id = :EvnRecept_id
						";
						$result_info = $this->db->query($query_info, $queryParams);
						if(is_object($result_info)){
							$result_info = $result_info->result('array');
							if(count($result_info) > 0){
								$result[0]['Recept_Delay_Info'] = 'Дата удаления: '.$result_info[0]['Del_Date'].PHP_EOL.'Пользователь: '.$result_info[0]['Del_User'].PHP_EOL.'Причина:'.$result_info[0]['Del_Cause'];
							};
						}
						break;
					case 5:
						$result[0]['Recept_Result'] = 'Снят с отсроченного обеспечения';
						$query_info = "
							select
								coalesce(wdu.WhsDocumentUc_Num,0) as \"Act_Num\",
								to_char(wduaro.WhsDocumentUcActReceptOut_setDT, 'dd.mm.yyyy') as \"Act_Date\",
								coalesce(wduarl.WhsDocumentUcActReceptList_outCause,'') as \"Act_Cause\"
							from v_WhsDocumentUcActReceptList wduarl
							inner join v_WhsDocumentUcActReceptOut wduaro on wduaro.WhsDocumentUcActReceptOut_id = wduarl.WhsDocumentUcActReceptOut_id
							inner join v_WhsDocumentUc wdu with on wdu.WhsDocumentUc_id = wdu.WhsDocumentUc_id
							where wduarl.EvnRecept_id = :EvnRecept_id
							and wdu.WhsDocumentType_id = 24
							limit 1
						";
						$result[0]['ReceptOtov_Date'] = $result[0]['ReceptOtov_obrDate'];
						$result_info = $this->db->query($query_info,$queryParams);
						if(is_object($result_info)){
							$result_info = $result_info->result('array');
							if(count($result_info) > 0){
								$result[0]['Recept_Delay_Info'] = 'Акт №'.$result_info[0]['Act_Num'].' от '.$result_info[0]['Act_Date'].'. Причина: '.$result_info[0]['Act_Cause'];
							}
						}
						break;
				}
			}
			return $result;
			//return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Генерирует по переданным данным фильтр по полю "7 нозологий".
	 */
	function genEvnReceptListIs7NozFilter($data, &$filters, &$queryParams, &$join) {
		If (ArrayVal($data,'EvnRecept_Is7Noz')!='') {
			$filters[] = "Is7Noz.Value = :EvnRecept_Is7Noz";
			$queryParams['EvnRecept_Is7Noz'] = $data['EvnRecept_Is7Noz'];
			$join .= " left join lateral (
				select
					(case when WhsDocumentCostItemType.WhsDocumentCostItemType_Nick = 'vzn' then '2' else '1' end) as Value
				from
					dbo.v_WhsDocumentCostItemType WhsDocumentCostItemType
				where
					WhsDocumentCostItemType.WhsDocumentCostItemType_id = EvnRecept.WhsDocumentCostItemType_id
			) Is7Noz on true";
		}
	}
}
