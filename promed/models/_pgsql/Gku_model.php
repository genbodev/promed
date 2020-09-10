<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Модель частичный функционал АРМ ГКУ (клиент Dlo/swWorkPlaceGkuWindow.js)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Gku
 * @access       public
 * @author       Vasinsky Igor (igor.vasinsky@gmail.com)
 * @version      16.06.2015
 *
 */

class Gku_model extends SwPgModel
{
    protected $dateTimeForm104 = "'dd.mm.yyyy'";

    /**
     * Список сотрудников организации
     *
     * @param array $data
     * @return bool|array
     */
    public function getListEmployes($data)
    {
        $params = [
            'Org_id' => $data['Org_id'],
            'pmUser_id' => $data['pmUser_id']
        ];
        
        $query = "
            select 
                U.PMUser_id as \"PMUser_id\", 
                U.PMUser_Name as \"PMUser_Name\" 
            from
                dbo.v_pmUser U
                left join dbo.pmUserCacheOrg pUCO on pUCO.pmUserCache_id = U.PMUser_id
            where 
                pUCO.Org_id = :Org_id
            and
                U.pmUser_id != :pmUser_id
            order by U.PMUser_Name 
        ";

        $result = $this->db->query($query, $params);

		if ( !is_object($result) )
			return false;

		return $result->result('array');
    }

    /**
     * Возвращает данные для постраничного вывода
     *
     * @param string $q query
     * @param array $p
     * @return array|bool
     */
	public function returnPagingData($q, &$p)
    {
		$get_count_result = $this->db->query(getCountSQLPH($q), $p);
		if( !is_object($get_count_result) ) {
			return false;
		}
		$get_count_result = $get_count_result->result('array')[0]['cnt'];
		
		$result = $this->db->query(getLimitSQLPH($q, $p['start'], $p['limit']), $p);
		if( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		return [
			'data' => $result,
			'totalCount' => $get_count_result
		];
	}

    /**
     * Чтение списка лотов
     * @param $data
     * @return array|bool
     */
	public function loadUnitOfTradingList($data)
    {
		$params = [];
		$filter = "";
		$fixationFilter = "";

		if($data['pmUser_did'] != '') {
            $fixationFilter = " and WDUP.pmUser_did = :pmUser_did";
        }

		if( !empty($data['DrugFinance_id']) ) {
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
			$filter .= " and WDPR.DrugFinance_id = :DrugFinance_id";
		}

		if( !empty($data['WhsDocumentCostItemType_id']) ) {
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
			$filter .= " and WDPR.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
		}
		
		$query = "
			select
				-- select
				WDUP.WhsDocumentUcPMUser_id as \"WhsDocumentUcPMUser_id\",
				WDUP.PMUser_did as \"PMUser_did\",
				U.PMUser_Name as \"PMUser_Name\",
				WDPR.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				WDPR.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
				WDPR.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				WDPR.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				case when coalesce(WDPR.WhsDocumentStatusType_id,1) = 2
					then REPLACE(WDPR.WhsDocumentUc_Sum::money::varchar, ',', ' ')
					else REPLACE((select sum(WhsDocumentProcurementRequestSpec_Kolvo * WhsDocumentProcurementRequestSpec_PriceMax) from v_WhsDocumentProcurementRequestSpec where WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id )::money::varchar, ',', ' ')
				end as \"WhsDocumentUc_Sum\",
				to_char(WDPR.WhsDocumentUc_Date::timestamp, {$this->dateTimeForm104}) as \"WhsDocumentUc_Date\",
				WDPR.DrugFinance_id as \"DrugFinance_id\",
				DF.DrugFinance_Name as \"DrugFinance_Name\",
				WDPR.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				WDCIT.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				case when coalesce(WDPR.WhsDocumentStatusType_id,1) = 2 then 'true' else 'false' end as \"isSigned\",
				coalesce(supply.WhsDocumentUc_Num, '') || coalesce(' от ' || to_char(supply.WhsDocumentUc_Date, {$this->dateTimeForm104}), '') || coalesce(' ' || supply.Org_sNick, '') as \"Supply_Data\"
				-- end select
			from
				-- from
				v_WhsDocumentProcurementRequest WDPR
				left join v_DrugFinance DF on DF.DrugFinance_id = WDPR.DrugFinance_id
				left join v_WhsDocumentCostItemType WDCIT on WDCIT.WhsDocumentCostItemType_id = WDPR.WhsDocumentCostItemType_id
				left join v_WhsDocumentUcPMUser WDUP on WDUP.WhsDocumentUc_id = WDPR.WhsDocumentUc_id
				left join v_pmUserCache U on U.pmUser_id = WDUP.pmUser_did
				left join lateral (
					select
						WDS.WhsDocumentUc_Num,
						WDS.WhsDocumentUc_Date,
						OS.Org_Nick as Org_sNick
					from
						v_WhsDocumentSupply WDS
						left join v_Org OS on OS.Org_id = WDS.Org_sid
					where
						WDS.WhsDocumentUc_pid = WDPR.WhsDocumentUc_id
					limit 1
				) supply on true
				-- end from
			where
				-- where
				WDPR.WhsDocumentType_id = 5
            and
                WDPR.WhsDocumentProcurementRequest_id in (
                    select distinct
                        WhsDPRS.WhsDocumentProcurementRequest_id 
                    from
                        v_DrugRequestPurchaseSpec DrugRPS
                        left join v_WhsDocumentProcurementRequestSpec WhsDPRS on WhsDPRS.DrugRequestPurchaseSpec_id = DrugRPS.DrugRequestPurchaseSpec_id
                     where DrugRPS.DrugRequest_id = :DrugRequest_id 
                )
				{$filter}{$fixationFilter}
				-- end where
			order by
				-- order by
				WDPR.WhsDocumentUc_Date --desc
				-- end order by
		";
		$params['start'] = $data['start'];
		$params['limit'] = $data['limit'];
		$params['DrugRequest_id'] = $data['DrugRequest_id'];
        $params['pmUser_did'] = $data['pmUser_did'];

        return $this->returnPagingData($query, $params);
    }

    /**
     * Получение списка статусов лота
     *
     * @return bool
     */
    public function getWhsDocumentUcStatusType()
    {
        $params = [];
        $query = "
            select 
                WhsDocumentUcStatusType_id as \"WhsDocumentUcStatusType_id\",
                WhsDocumentUcStatusType_Name as \"WhsDocumentUcStatusType_Name\"
            from
                v_WhsDocumentUcStatusType
        ";

		$result = $this->db->query($query, $params);
		if (! is_object($result) )
		    return false;

		return $result->result('array');
     }

    /**
     * Получение списка лотов со статусами в главное окно арма ГКУ
     *
     * @param $data
     * @return array|bool
     */
    function loadUnitOfTradingListWithStatus($data){
		$params = [];
		$filter = "";
        $fixationFilter = "";

        if($data['pmUser_did'] != '') {
            $fixationFilter = ' and WDUP.pmUser_did =:pmUser_did';
        }
        
		if( !empty($data['DrugFinance_id']) ) {
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
			$filter .= " and WDPR.DrugFinance_id = :DrugFinance_id";
		}

		if( !empty($data['WhsDocumentCostItemType_id']) ) {
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
			$filter .= " and WDPR.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
		}
		
		$query = "
			select
				-- select
				WDUP.WhsDocumentUcPMUser_id as \"WhsDocumentUcPMUser_id\",
				WDUP.PMUser_did as \"PMUser_did\",
				U.PMUser_Name as \"PMUser_Name\",
				WDPR.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				WDUST.WhsDocumentUcStatusType_id as \"WhsDocumentUcStatusType_id\",
				WDUST.WhsDocumentUcStatusType_Name as \"WhsDocumentUcStatusType_Name\",
				WDPR.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
				WDPR.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				WDPR.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				WDPR.Org_aid as \"Org_aid\",
				case when WDPR.Org_aid = 0 then 'МЗ' else org.Org_Nick end as \"Org_Nick\",
				case when coalesce(WDPR.WhsDocumentStatusType_id,1) = 2
					then REPLACE(WDPR.WhsDocumentUc_Sum::money::varchar, ',', ' ')
					else REPLACE(( select sum(WhsDocumentProcurementRequestSpec_Kolvo * WhsDocumentProcurementRequestSpec_PriceMax) from v_WhsDocumentProcurementRequestSpec where WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id )::money::varchar, ',', ' ')
				end as \"WhsDocumentUc_Sum\",
				to_char(WDPR.WhsDocumentUc_Date::timestamp, {$this->dateTimeForm104}) as \"WhsDocumentUc_Date\",
				WDPR.DrugFinance_id as \"DrugFinance_id\",
				DF.DrugFinance_Name as \"DrugFinance_Name\",
				WDPR.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				WDCIT.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				WDPR.BudgetFormType_id as \"BudgetFormType_id\",
				WDPR.WhsDocumentPurchType_id as \"WhsDocumentPurchType_id\",
				case when coalesce (WDPR.WhsDocumentStatusType_id,1) = 2 then 'true' else 'false' end as \"isSigned\",
				coalesce(supply.WhsDocumentUc_Num, '') || coalesce (' от ' || to_char(supply.WhsDocumentUc_Date, {$this->dateTimeForm104}), '') || coalesce (' ' || supply.Org_sNick, '') as \"Supply_Data\"
				-- end select
			from
				-- from
				v_WhsDocumentProcurementRequest WDPR
				left join v_DrugFinance DF on DF.DrugFinance_id = WDPR.DrugFinance_id
				left join v_WhsDocumentCostItemType WDCIT on WDCIT.WhsDocumentCostItemType_id = WDPR.WhsDocumentCostItemType_id
				left join v_WhsDocumentUcPMUser WDUP on WDUP.WhsDocumentUc_id = WDPR.WhsDocumentUc_id
				left join v_pmUserCache U on U.pmUser_id = WDUP.pmUser_did
                left join v_WhsDocumentUcStatusType WDUST on WDUST.WhsDocumentUcStatusType_id = WDPR.WhsDocumentUcStatusType_id
                left join Org org on org.Org_id = WDPR.Org_aid
				left join lateral (
					select
						WDS.WhsDocumentUc_Num,
						WDS.WhsDocumentUc_Date,
						OS.Org_Nick as Org_sNick
					from
						v_WhsDocumentSupply WDS
						left join v_Org OS on OS.Org_id = WDS.Org_sid
					where
						WDS.WhsDocumentUc_pid = WDPR.WhsDocumentUc_id
					limit 1
				) supply on true
				-- end from
			where
				-- where
				WDPR.WhsDocumentType_id = 5
                and WDPR.WhsDocumentProcurementRequest_id in (
                    select distinct
                        WhsDPRS.WhsDocumentProcurementRequest_id 
                    from
                        v_DrugRequestPurchaseSpec DrugRPS
                        left join v_WhsDocumentProcurementRequestSpec WhsDPRS on WhsDPRS.DrugRequestPurchaseSpec_id = DrugRPS.DrugRequestPurchaseSpec_id
                    where
                        DrugRPS.DrugRequest_id = :DrugRequest_id
                )
				{$filter}{$fixationFilter}
				-- end where
			order by
				-- order by
				WDPR.WhsDocumentUc_Date --desc
				-- end order by
		";
		$params['start'] = $data['start'];
		$params['limit'] = $data['limit'];
		$params['DrugRequest_id'] = $data['DrugRequest_id'];
        $params['pmUser_did'] = $data['pmUser_did'];
        $params['pmUser_id'] = $data['pmUser_id'];

		$result = $this->returnPagingData($query, $params);        
     
        return $result;
     }

    /**
     * Назначение, снятие назначения с лота
     * @param array $data
     * @return bool
     */
    public function manageLot($data)
    {
		$query = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from dbo.p_GkuManageLot
			(
				WhsDocumentUc_id := :WhsDocumentUc_id,
                WhsDocumentUcPMUser_id := :WhsDocumentUcPMUser_id,
				pmUser_id := :pmUser_id,
                pmUser_did := :pmUser_did,
                unassign := :unassign
			)
		";

		$res = $this->db->query($query, $data);
		
		if (!is_object($res) )
			return false;

        return $res->result('array');
    }    
}