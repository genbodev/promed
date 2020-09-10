<?php	defined('BASEPATH') or die ('No direct script access allowed');
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

class Gku_model extends CI_Model {

	/**
	 * Конструктор
	 */
	function __construct() {
        parent::__construct();
    }

    /**
     *  Список сотрудников организации
     */ 
    public function getListEmployes($data){
        $params = array(
            'Org_id'=>$data['Org_id'],
            'pmUser_id'=>$data['pmUser_id']
        );
        
        $query = "select 
                    U.PMUser_id, 
                    U.PMUser_Name 
                from dbo.v_pmUser U with(nolock)
				left join 
                    dbo.pmUserCacheOrg pUCO with(nolock) on pUCO.pmUserCache_id = U.PMUser_id
				where  pUCO.Org_id = :Org_id
				and U.pmUser_id != :pmUser_id
                order by U.PMUser_Name 
        ";
        
        //echo getDebugSql($query,$params);
        
        $result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
    }   

	/**
	 *	Возвращает данные для постраничного вывода
	 */
	function returnPagingData($q, &$p) {
		$get_count_result = $this->db->query(getCountSQLPH($q), $p);
		if( !is_object($get_count_result) ) {
			return false;
		}
		$get_count_result = $get_count_result->result('array');
		
		$result = $this->db->query(getLimitSQLPH($q, $p['start'], $p['limit']), $p);
		if( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		return array(
			'data' => $result,
			'totalCount' => $get_count_result[0]['cnt']
		);
	}
    
	/**
	 *	Чтение списка лотов
	 */
	function loadUnitOfTradingList($data) {
		$params = array();
		$filter = "1=1";
		$filter .= " and WDPR.WhsDocumentType_id = 5";
		$filter .= " and WDPR.WhsDocumentProcurementRequest_id in ( select distinct WhsDPRS.WhsDocumentProcurementRequest_id 
			from v_DrugRequestPurchaseSpec DrugRPS with (nolock)
			left join v_WhsDocumentProcurementRequestSpec WhsDPRS with (nolock) on WhsDPRS.DrugRequestPurchaseSpec_id = DrugRPS.DrugRequestPurchaseSpec_id
			 where DrugRPS.DrugRequest_id = :DrugRequest_id )";
		
        $fixationFilter = $data['pmUser_did'] != '' ? ' and WDUP.pmUser_did =:pmUser_did' : '';
        
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
				WDUP.WhsDocumentUcPMUser_id
				,WDUP.PMUser_did
				,U.PMUser_Name
				,WDPR.WhsDocumentUc_id
				,WDPR.WhsDocumentUc_pid
				,WDPR.WhsDocumentUc_Num
				,WDPR.WhsDocumentUc_Name
				,case when isnull(WDPR.WhsDocumentStatusType_id,1) = 2
					then REPLACE(CONVERT(varchar, CONVERT(money, WDPR.WhsDocumentUc_Sum), 1), ',', ' ')
					else REPLACE(CONVERT(varchar, CONVERT(money, ( select sum(WhsDocumentProcurementRequestSpec_Kolvo * WhsDocumentProcurementRequestSpec_PriceMax) from v_WhsDocumentProcurementRequestSpec with(nolock) where WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id )), 1), ',', ' ')
				end as WhsDocumentUc_Sum
				,convert(varchar(10), cast(WDPR.WhsDocumentUc_Date as datetime),104) as WhsDocumentUc_Date
				,WDPR.DrugFinance_id
				,DF.DrugFinance_Name
				,WDPR.WhsDocumentCostItemType_id
				,WDCIT.WhsDocumentCostItemType_Name
				,case when isnull(WDPR.WhsDocumentStatusType_id,1) = 2 then 'true' else 'false' end as isSigned
				,isnull(supply.WhsDocumentUc_Num, '')+isnull(' от '+convert(varchar(10), supply.WhsDocumentUc_Date, 104), '')+isnull(' '+supply.Org_sNick, '') as Supply_Data
				-- end select
			from
				-- from
				v_WhsDocumentProcurementRequest WDPR with(nolock)
				left join v_DrugFinance DF with(nolock) on DF.DrugFinance_id = WDPR.DrugFinance_id
				left join v_WhsDocumentCostItemType WDCIT with(nolock) on WDCIT.WhsDocumentCostItemType_id = WDPR.WhsDocumentCostItemType_id
				left join v_WhsDocumentUcPMUser WDUP with(nolock) on WDUP.WhsDocumentUc_id = WDPR.WhsDocumentUc_id
				left join v_pmUserCache U with(nolock) on U.pmUser_id = WDUP.pmUser_did
				outer apply (
					select top 1
						WDS.WhsDocumentUc_Num,
						WDS.WhsDocumentUc_Date,
						OS.Org_Nick as Org_sNick
					from
						v_WhsDocumentSupply WDS with (nolock)
						left join v_Org OS with (nolock) on OS.Org_id = WDS.Org_sid
					where
						WDS.WhsDocumentUc_pid = WDPR.WhsDocumentUc_id
				) supply
				-- end from
			where
				-- where
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
        
        //echo getDebugSql($query, $params); die();
        
        return $this->returnPagingData($query, $params);
	    /*
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$result = $result->result('array');
			return array(
				'data' => $result,
				'totalCount' => count($result)
			);
		} else {
			return false;
		} 
        */   
    }  
    
    /**
     *  Получение списка статусов лота
     */ 
    function getWhsDocumentUcStatusType($data){
        $params = array();
        $query = "
            select 
                WhsDocumentUcStatusType_id,
                WhsDocumentUcStatusType_Name
            from v_WhsDocumentUcStatusType with(nolock)
        ";

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		} 
     }
     
     /**
      * Получение списка лотов со статусами в главное окно арма ГКУ
      */ 
    function loadUnitOfTradingListWithStatus($data){
		$params = array();
		$filter = "1=1";
		$filter .= " and WDPR.WhsDocumentType_id = 5";
		$filter .= " and WDPR.WhsDocumentProcurementRequest_id in ( select distinct WhsDPRS.WhsDocumentProcurementRequest_id 
			from v_DrugRequestPurchaseSpec DrugRPS with (nolock)
			left join v_WhsDocumentProcurementRequestSpec WhsDPRS with (nolock) on WhsDPRS.DrugRequestPurchaseSpec_id = DrugRPS.DrugRequestPurchaseSpec_id
			 where DrugRPS.DrugRequest_id = :DrugRequest_id )";

        //Определение сотрудника от руководителя организации
        //$filter .= ($data['isDirector'] == 1) ?  " and WDUP.pmUSer_did = :pmUser_id " : ""; 

        $fixationFilter = $data['pmUser_did'] != '' ? ' and WDUP.pmUser_did =:pmUser_did' : '';
        
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
				WDUP.WhsDocumentUcPMUser_id
				,WDUP.PMUser_did
				,U.PMUser_Name
				,WDPR.WhsDocumentUc_id
				,WDUST.WhsDocumentUcStatusType_id
				,WDUST.WhsDocumentUcStatusType_Name                
				,WDPR.WhsDocumentUc_pid
				,WDPR.WhsDocumentUc_Num
				,WDPR.WhsDocumentUc_Name
				,WDPR.Org_aid
				,case when WDPR.Org_aid = 0 then 'МЗ' else org.Org_Nick end as Org_Nick
				,case when isnull(WDPR.WhsDocumentStatusType_id,1) = 2
					then REPLACE(CONVERT(varchar, CONVERT(money, WDPR.WhsDocumentUc_Sum), 1), ',', ' ')
					else REPLACE(CONVERT(varchar, CONVERT(money, ( select sum(WhsDocumentProcurementRequestSpec_Kolvo * WhsDocumentProcurementRequestSpec_PriceMax) from v_WhsDocumentProcurementRequestSpec with(nolock) where WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id )), 1), ',', ' ')
				end as WhsDocumentUc_Sum
				,convert(varchar(10), cast(WDPR.WhsDocumentUc_Date as datetime),104) as WhsDocumentUc_Date
				,WDPR.DrugFinance_id
				,DF.DrugFinance_Name
				,WDPR.WhsDocumentCostItemType_id
				,WDCIT.WhsDocumentCostItemType_Name
				,WDPR.BudgetFormType_id
				,WDPR.WhsDocumentPurchType_id
				,case when isnull(WDPR.WhsDocumentStatusType_id,1) = 2 then 'true' else 'false' end as isSigned
				,isnull(supply.WhsDocumentUc_Num, '')+isnull(' от '+convert(varchar(10), supply.WhsDocumentUc_Date, 104), '')+isnull(' '+supply.Org_sNick, '') as Supply_Data
				-- end select
			from
				-- from
				v_WhsDocumentProcurementRequest WDPR with(nolock)
				left join v_DrugFinance DF with(nolock) on DF.DrugFinance_id = WDPR.DrugFinance_id
				left join v_WhsDocumentCostItemType WDCIT with(nolock) on WDCIT.WhsDocumentCostItemType_id = WDPR.WhsDocumentCostItemType_id
				left join v_WhsDocumentUcPMUser WDUP with(nolock) on WDUP.WhsDocumentUc_id = WDPR.WhsDocumentUc_id
				left join v_pmUserCache U with(nolock) on U.pmUser_id = WDUP.pmUser_did
                left join v_WhsDocumentUcStatusType WDUST with(nolock) on WDUST.WhsDocumentUcStatusType_id = WDPR.WhsDocumentUcStatusType_id
                left join Org org with (nolock) on org.Org_id = WDPR.Org_aid
				outer apply (
					select top 1
						WDS.WhsDocumentUc_Num,
						WDS.WhsDocumentUc_Date,
						OS.Org_Nick as Org_sNick
					from
						v_WhsDocumentSupply WDS with (nolock)
						left join v_Org OS with (nolock) on OS.Org_id = WDS.Org_sid
					where
						WDS.WhsDocumentUc_pid = WDPR.WhsDocumentUc_id
				) supply
				-- end from
			where
				-- where
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
        
        //echo getDebugSql($query, $params); die();
        
		$result = $this->returnPagingData($query, $params);        
     
        return $result;
		/*
        $result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$result = $result->result('array');
			return array(
				'data' => $result,
				'totalCount' => count($result)
			);
            
            //echo '<pre>' . print_r($result, 1) . '</pre>';
		} else {
			return false;
		}  
        */    
     } 
    
    /**
     *  Назначение, снятие назначения с лота
     */  
    function manageLot($data){
        $params = array(
            'WhsDocumentUc_id'=>$data['WhsDocumentUc_id'],
            'pmUser_id'=>$data['pmUser_id'],
            'pmUser_did'=>$data['pmUser_did'],
            'WhsDocumentUcPMUser_id'=>$data['WhsDocumentUcPMUser_id'],
            'unassign'=>$data['unassign']
        );
        
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec dbo.p_GkuManageLot
				@WhsDocumentUc_id = :WhsDocumentUc_id,
                @WhsDocumentUcPMUser_id = :WhsDocumentUcPMUser_id,
				@pmUser_id = :pmUser_id,
                @pmUser_did = :pmUser_did,
                @unassign = :unassign,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Message;
		";
		//echo getDebugSql($query, $params); die();
		$res = $this->db->query($query, $data);
		
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}        
    }    
}