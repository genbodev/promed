<?php defined('BASEPATH') or die ('No direct script access allowed');

class UnitOfTrading_model extends swModel {
	
	var $objectName = "";
	var $objectKey = "";

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

    /**
     *  Копирование лотов из предыдущего периода 
     */ 
    function copyLots(){
        
    }

    /**
     *  Получение данных по предыдущему рабочему периоду
     */ 
    function getPrevDrugRequestPeriod($data){
        $params = array(
            'DrugRequest_id'=>$data['DrugRequest_id'],
            'DrugFinance_id' => $data['DrugFinance_id'],
            'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']
        );
        
        $query = "
           select
				-- select
				WDUC.WhsDocumentUc_id
                
				,WDUC.WhsDocumentUc_pid
				,WDUC.WhsDocumentUc_Num
				,WDUC.WhsDocumentUc_Name
				,case when signed.cnt > 0
					then WDUC.WhsDocumentUc_Sum
					else ( select cast(sum(DrugRequestPurchaseSpec_Sum) as float) from DrugRequestPurchaseSpec with(nolock) where WhsDocumentUc_id = WDUC.WhsDocumentUc_id )
				end as WhsDocumentUc_Sum
				,convert(varchar(10), cast(WDUC.WhsDocumentUc_Date as datetime),104) as WhsDocumentUc_Date
				,WDPR.DrugFinance_id
				,DF.DrugFinance_Name
				,WDPR.WhsDocumentCostItemType_id
				,WDCIT.WhsDocumentCostItemType_Name
				,case when signed.cnt > 0 then 'true' else 'false' end as isSigned
				,isnull(supply.WhsDocumentUc_Num, '')+isnull(' от '+convert(varchar(10), supply.WhsDocumentUc_Date, 104), '')+isnull(' '+supply.Org_sNick, '') as Supply_Data
               
				-- end select
			from
				-- from
				WhsDocumentUc WDUC with(nolock)
				left join WhsDocumentProcurementRequest WDPR with(nolock) on WDPR.WhsDocumentProcurementRequest_id = WDUC.WhsDocumentUc_id
				left join DrugFinance DF with(nolock) on DF.DrugFinance_id = WDPR.DrugFinance_id
				left join WhsDocumentCostItemType WDCIT with(nolock) on WDCIT.WhsDocumentCostItemType_id = WDPR.WhsDocumentCostItemType_id
				outer apply(
					select count(WhsDocumentProcurementRequest_id) as cnt from WhsDocumentProcurementRequestSpec with(nolock) where WhsDocumentProcurementRequest_id = WDUC.WhsDocumentUc_id
				) as signed
				outer apply (
					select top 1
						WDS.WhsDocumentUc_Num,
						WDS.WhsDocumentUc_Date,
						OS.Org_Nick as Org_sNick
					from
						v_WhsDocumentSupply WDS with (nolock)
						left join v_Org OS with (nolock) on OS.Org_id = WDS.Org_sid
					where
						WDS.WhsDocumentUc_pid = WDUC.WhsDocumentUc_id
				) supply
				-- end from
			where
				-- where
				1=1 and WDUC.WhsDocumentType_id = 5 and WDUC.WhsDocumentUc_id in ( 
                    select distinct 
                        WhsDocumentUc_id 
                    from DrugRequestPurchaseSpec with(nolock) 
                   
                   where DrugRequest_id = (select top 1 DrugRequest_id from dbo.DrugRequest with(nolock) where DrugRequest_id < :DrugRequest_id order by DrugRequest_id DESC)		 
                   --
                   --
                   --
                )
                --and WDPR.DrugFinance_id = :DrugFinance_id
                --and WDPR.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
				-- end where
			order by
				-- order by
				WDUC.WhsDocumentUc_Date --desc
				-- end order by
		";
        // -- where DrugRequest_id = :DrugRequest_id 
        // -- where DrugRequest_id = (select top 1 DrugRequest_id from dbo.DrugRequest with(nolock) where DrugRequest_id < :DrugRequest_id order by DrugRequest_id DESC)
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}   
    } 

    /**
     *  Получение данных о ЛС по конкретному лоту
     */ 
  
    function getUnitsOfTrading($data){
        
        $params = array(
            'WhsDocumentUc_id'=>$data['WhsDocumentUc_id']
        );   
        
        $query = "
      		select 
              * 
            from dbo.DrugRequestPurchaseSpec DRPS with(nolock)
            where DRPS.WhsDocumentUc_id = :WhsDocumentUc_id
        ";
        
		$res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}         
    }   
    
    /**
     *  Возвращает медикаменты по текущей заявке при копировании лотов
     */  
    function getDrugRequestPurchaseSpesCurDrugRequest($data){
        $params = array(
            'DrugRequest_id'=>$data['DrugRequest_id']
        );
        $query = "select * from dbo.DrugRequestPurchaseSpec DRPS with(nolock) where DRPS.DrugRequest_id = :DrugRequest_id";
        
		$res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}         
     }
   
	
	/**
	 *	Возвращает медикаменты сводной заявки с различными признаками
	 */
     /*
	function getDrugsOfDrugRequestForFormationCopy($data) {
		if( empty($data['DrugRequest_id']) ) {
			return false;
		}
        
        $params = array(
            'DrugRequest_id'=>$data['DrugRequest_id']
        );
        
        $query = "
			select
				DRPS.DrugRequestPurchaseSpec_id,
				DRPS.DrugFinance_id,
				case when DRPS.DrugComplexMnn_id is not null then DCM.DrugComplexMnn_RusName else TN.NAME end as Drug_Name,
				0 as isSUM,
				case when ISVK.cnt > 0 then 1 else 0 end as isVK,
				case when DRPS.TRADENAMES_id is not null then 1 else 0 end as isTN,
				--case when ACTMATTERS.STRONGGROUPID > 0 or ACTMATTERS.NARCOGROUPID > 0 then 1 else 0 end as isNARC,
				0 as isNARC,
				CPG.CLSPHARMAGROUP_ID as Pharmagroup_id,
				CPG.NAME as Pharmagroup_Name,
				CATC.NAME as Atc_Name,
				ACTMATTERS.ACTMATTERS_ID as Actmatters_id,
				ACTMATTERS.RUSNAME as Actmatters_Name,
				CMPG.CLS_MZ_PHGROUP_ID as MzPharmagroup_id,
				CMPG.NAME as MzPharmagroup_Name,
				firm_count.cnt as firm_count
			from
				DrugRequestPurchaseSpec DRPS with(nolock)
				left join rls.DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = DRPS.DrugComplexMnn_id
				left join rls.ACTMATTERS ACTMATTERS with(nolock) on ACTMATTERS.ACTMATTERS_ID = DCM.ACTMATTERS_id
				left join rls.TRADENAMES TN with(nolock) on TN.TRADENAMES_ID = DRPS.TRADENAMES_id
				outer apply (
					select top 1
						*
					from
						rls.Drug with(nolock)
					where
						Drug.DrugComplexMnn_id = DRPS.DrugComplexMnn_id
					order by
						Drug_id
				) Drug
				outer apply ( -- подсчет количества производителей медикамента
					select
						count(FIRMID) as cnt
					from (
						select distinct
							p.FIRMID
						from
							rls.Drug d with(nolock)
							left join rls.PREP p with(nolock) on p.Prep_id = d.DrugPrep_id
							left join rls.FIRMS f with(nolock) on f.FIRMS_ID = p.FIRMID
						where
							d.DrugComplexMnn_id = DRPS.DrugComplexMnn_id
					) cntq
				) firm_count
				left join rls.PREP Prep with(nolock) on Prep.Prep_id = Drug.DrugPrep_id
				--left join rls.PREP_PHARMAGROUP PP with(nolock) on PP.PREPID = Prep.Prep_id
				outer apply (
					select top 1
						UNIQID
					from
						rls.PREP_PHARMAGROUP with(nolock)
					where
						PREPID = Prep.Prep_id
					order by
						UNIQID
				) PP
				left join rls.CLSPHARMAGROUP CPG with(nolock) on CPG.CLSPHARMAGROUP_ID = PP.UNIQID
				left join rls.PREP_ATC PATC with(nolock) on PATC.PREPID = Prep.Prep_id
				left join rls.CLSATC CATC with(nolock) on CATC.CLSATC_ID = PATC.UNIQID
				outer apply (
					select top 1
						iCMPG.CLS_MZ_PHGROUP_ID,
						iCMPG.NAME
					from
						rls.ACTMATTERS_DRUGFORMS AD with(nolock)
						left join rls.CLS_MZ_PHGROUP iCMPG with(nolock) on iCMPG.CLS_MZ_PHGROUP_ID = AD.MZ_PHGR_ID
					where
						ACTMATTERID = ACTMATTERS.ACTMATTERS_ID and
						iCMPG.CLS_MZ_PHGROUP_ID > 0
					order by
						iCMPG.CLS_MZ_PHGROUP_ID
				) CMPG
				outer apply (
					select
						count(*) as cnt
					from
						rls.ACTMATTERS_DRUGFORMS AD with(nolock)
					where
						ACTMATTERID = ACTMATTERS.ACTMATTERS_ID and
						MZ_PHGR_ID = 66
				) ISVK
			where
				DRPS.DrugRequest_id = :DrugRequest_id
				and DRPS.WhsDocumentUc_id is null
				and DRPS.DrugFinance_id is not null		        
        ";
        
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}           
    }        
    */
	
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
		
		if( !empty($data['DrugFinance_id']) ) {
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
			$filter .= " and WDPR.DrugFinance_id = :DrugFinance_id";
		}

		if( !empty($data['WhsDocumentCostItemType_id']) ) {
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
			$filter .= " and WDPR.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
		}

		if( !empty($data['BudgetFormType_id']) ) {
			$params['BudgetFormType_id'] = $data['BudgetFormType_id'];
			$filter .= " and WDPR.BudgetFormType_id = :BudgetFormType_id";
		}

		if( !empty($data['WhsDocumentPurchType_id']) ) {
			$params['WhsDocumentPurchType_id'] = $data['WhsDocumentPurchType_id'];
			$filter .= " and WDPR.WhsDocumentPurchType_id = :WhsDocumentPurchType_id";
		}

        if( !empty($data['FinanceSource_id']) ) {
            $params['FinanceSource_id'] = $data['FinanceSource_id'];
            $filter .= " and WDPR.FinanceSource_id = :FinanceSource_id";
        }
		
		$query = "
			select
				-- select
				WDPR.WhsDocumentUc_id
				,WDPR.WhsDocumentUc_pid
				,WDPR.WhsDocumentUc_Num 
				,WDPR.WhsDocumentUc_Name 
				,case when isnull(WDPR.WhsDocumentStatusType_id,1) = 2
					-- then cast(WDPR.WhsDocumentUc_Sum as decimal(12,2))
					then REPLACE(CONVERT(varchar, CONVERT(money, WDPR.WhsDocumentUc_Sum), 1), ',', ' ')
					else REPLACE(CONVERT(varchar, CONVERT(money, ( select sum(WhsDocumentProcurementRequestSpec_Kolvo * WhsDocumentProcurementRequestSpec_PriceMax) from v_WhsDocumentProcurementRequestSpec with(nolock) where WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id )), 1), ',', ' ')
					-- else cast(( select sum(WhsDocumentProcurementRequestSpec_Kolvo * WhsDocumentProcurementRequestSpec_PriceMax) from v_WhsDocumentProcurementRequestSpec with(nolock) where WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id ) as decimal(12,2))
				end as WhsDocumentUc_Sum
				,convert(varchar(10), cast(WDPR.WhsDocumentUc_Date as datetime),104) as WhsDocumentUc_Date
				,WDPR.DrugFinance_id
				,DF.DrugFinance_Name
				,WDPR.WhsDocumentCostItemType_id
				,WDCIT.WhsDocumentCostItemType_Name
				,WDPR.PurchObjType_id
				,POT.PurchObjType_Name
				,WDPR.BudgetFormType_id
				,BFT.BudgetFormType_Name
				,convert(varchar(10), cast(WDPR.WhsDocumentProcurementRequest_setDate as datetime),104) as WhsDocumentProcurementRequest_setDate
				,WDPR.WhsDocumentPurchType_id
				,WDPRSD.WhsDocumentProcurementRequestSpecDop_id
				,okved.Okved_id
				,okved.Okved_Code
				,WDPRSD.Okpd_id
				,WDPRSD.WhsDocumentProcurementRequestSpecDop_CodeKOSGU
				,WDPRSD.WhsDocumentProcurementRequestSpecDop_Count
				,WDPRSD.SupplyPlaceType_id
				,WDPRSD.ProvSizeType_id
				,case when isnull(WDPR.WhsDocumentStatusType_id,1) = 2 then 'true' else 'false' end as isSigned
				,isnull(supply.WhsDocumentUc_Num, '')+isnull(' от '+convert(varchar(10), supply.WhsDocumentUc_Date, 104), '')+isnull(' '+supply.Org_sNick, '') as Supply_Data
				,wdust.WhsDocumentUcStatusType_Name
				,WDPR.WhsDocumentUcStatusType_id
				,WDPR.WhsDocumentStatusType_id
				,pmuc.PMUser_Name
				,WDPT.WhsDocumentPurchType_Name
				,WDPR.Org_aid
				,FS.FinanceSource_id
				,FS.FinanceSource_Name
				-- end select
			from
				-- from
				v_WhsDocumentProcurementRequest WDPR with (nolock)
				left join DrugFinance DF with(nolock) on DF.DrugFinance_id = WDPR.DrugFinance_id
				left join WhsDocumentCostItemType WDCIT with(nolock) on WDCIT.WhsDocumentCostItemType_id = WDPR.WhsDocumentCostItemType_id
				left join v_PurchObjType POT with (nolock) on POT.PurchObjType_id = WDPR.PurchObjType_id
				left join v_BudgetFormType BFT with (nolock) on BFT.BudgetFormType_id = WDPR.BudgetFormType_id
				left join v_WhsDocumentPurchType WDPT with (nolock) on WDPT.WhsDocumentPurchType_id = WDPR.WhsDocumentPurchType_id
				left join v_WhsDocumentProcurementRequestSpecDop WDPRSD with (nolock) on WDPRSD.WhsDocumentUc_id = WDPR.WhsDocumentUc_id
				left join v_Okved okved with (nolock) on okved.Okved_id = WDPRSD.Okved_id
				left join v_WhsDocumentUcStatusType wdust with (nolock) on wdust.WhsDocumentUcStatusType_id = WDPR.WhsDocumentUcStatusType_id
				left join v_WhsDocumentUcPMUser wdupmu with (nolock) on wdupmu.WhsDocumentUc_id = WDPR.WhsDocumentUc_id
				left join v_pmUserCache pmuc with (nolock) on pmuc.PMUser_id = wdupmu.pmUser_did
				left join v_FinanceSource FS with (nolock) on FS.FinanceSource_id = WDPR.FinanceSource_id
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
				{$filter}
				-- end where
			order by
				-- order by
				WDPR.WhsDocumentUc_Date --desc
				-- end order by
		";
		$params['start'] = $data['start'];
		$params['limit'] = $data['limit'];
		$params['DrugRequest_id'] = $data['DrugRequest_id'];
        
        //echo getDebugSql($query, $params); die();
        
		return $this->returnPagingData($query, $params);
	}
	
	/**
	 *	Сохранение лота
	 */
	function saveUnitOfTrading($data) {
		$procedure = "p_WhsDocumentProcurementRequest_" . ( empty($data['WhsDocumentUc_id']) ? 'ins' : 'upd' );
		$isOutput = empty($data['WhsDocumentUc_id']) ? "output" : "";
		
		$data['WhsDocumentUc_Sum'] = isset($data['WhsDocumentUc_Sum']) ? $data['WhsDocumentUc_Sum'] : 0;
		
		$query = "
			declare
				@WhsDocumentUc_id bigint,
				@WhsDocumentProcurementRequest_id bigint,
				@cur_date datetime,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentUc_id = :WhsDocumentUc_id;
			set @WhsDocumentProcurementRequest_id = :WhsDocumentUc_id;
			set @cur_date = (select dbo.tzGetDate());
			exec dbo." . $procedure . "
				@WhsDocumentUc_id = @WhsDocumentUc_id output,
				@WhsDocumentUc_pid = null,
				@WhsDocumentUc_Num = :WhsDocumentUc_Num,
				@WhsDocumentUc_Name = :WhsDocumentUc_Name,
				@WhsDocumentType_id = :WhsDocumentType_id,
				@WhsDocumentUc_Date = @cur_date,
				@WhsDocumentUc_Sum = :WhsDocumentUc_Sum,
				@WhsDocumentStatusType_id = :WhsDocumentStatusType_id,
				@WhsDocumentProcurementRequest_id = @WhsDocumentProcurementRequest_id {$isOutput},
				@DrugFinance_id = :DrugFinance_id,
				@Org_aid = :Org_aid,
				@WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id,
				@WhsDocumentUcStatusType_id = :WhsDocumentUcStatusType_id,
				@PurchObjType_id = :PurchObjType_id,
				@BudgetFormType_id = :BudgetFormType_id,
				@WhsDocumentPurchType_id = :WhsDocumentPurchType_id,
				@WhsDocumentProcurementRequest_setDate = :WhsDocumentProcurementRequest_setDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentUc_id as WhsDocumentUc_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Удаление лота
	 */
	function deleteUnitOfTrading($data) {
		//предварительно удаляем спецификации лота если они есть
		$query = "
			select w.WhsDocumentProcurementRequestSpec_id from
				WhsDocumentProcurementRequestSpec w with (nolock)
			where
				w.WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id;
		";
		//echo getDebugSql($query, $data); //die();
		$res = $this->db->query($query, $data);
		if( !is_object($res) ) {
			DieWithError("Ошибка при получении спецификаций лота");
		} else {
			$res = $res->result('array');//var_dump($res);die;
			if(count($res) > 0){
				$query = "
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec dbo.p_WhsDocumentProcurementRequestSpec_del
						@WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
				foreach ($res as $key) {
					$qdata = array('WhsDocumentProcurementRequestSpec_id'=>$key['WhsDocumentProcurementRequestSpec_id']);
					$resl = $this->db->query($query, $qdata);
					if ( !is_object($resl) ) {
						DieWithError("Ошибка при удалении спецификаций лота");
					}
				}
			}
		}
		//удаляем данные для документации по лоту
		$query = "
			select WhsDocumentProcurementRequestSpecDop_id from
				WhsDocumentProcurementRequestSpecDop
			where
				WhsDocumentUc_id= :WhsDocumentProcurementRequest_id;
		";
		$res = $this->db->query($query, $data);
		if( !is_object($res) ) {
			DieWithError("Ошибка при получении данных для документации по лоту");
		} else {
			$res = $res->result('array');
			if(count($res) > 0){
				$query = "
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec dbo.p_WhsDocumentProcurementRequestSpecDop_del
						@WhsDocumentProcurementRequestSpecDop_id = :WhsDocumentProcurementRequestSpecDop_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
				foreach ($res as $key) {
					$qdata = array('WhsDocumentProcurementRequestSpecDop_id'=>$key['WhsDocumentProcurementRequestSpecDop_id']);
					$resl = $this->db->query($query, $qdata);
					if ( !is_object($resl) ) {
						DieWithError("Ошибка при удалении данных для документации по лоту");
					}
				}
			}
		}

		//удаляем данные по лоту из таблицы связи с пользователем (руководителем проекта)
		$query = "
			select WhsDocumentUcPMUser_id from
				WhsDocumentUcPMUser
			where
				WhsDocumentUc_id= :WhsDocumentProcurementRequest_id;
		";
		$res = $this->db->query($query, $data);
		if( !is_object($res) ) {
			DieWithError("Ошибка при получении данных по лоту");
		} else {
			$res = $res->result('array');
			if(count($res) > 0){
				$query = "
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec dbo.p_WhsDocumentUcPMUser_del
						@WhsDocumentUcPMUser_id = :WhsDocumentUcPMUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
				foreach ($res as $key) {
					$qdata = array('WhsDocumentUcPMUser_id'=>$key['WhsDocumentUcPMUser_id']);
					$resl = $this->db->query($query, $qdata);
					if ( !is_object($resl) ) {
						DieWithError("Ошибка при удалении данных по лоту");
					}
				}
			}
		}
		
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec dbo.p_WhsDocumentProcurementRequest_del
				@WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Message;
		";
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $data);
		
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Читает список медикаментов сводной заявки, у которых разница между количеством к закупу и количеством, включенным в  лот (в упаковках), больше нуля
	 */
	function loadDrugList($data) {
		$params = array();
		
		$filter = "1=1";
		$filter .= " and DRPS.DrugRequest_id = :DrugRequest_id";
		$apply = " outer apply (
					select sum(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo) as sum
					from v_WhsDocumentProcurementRequestSpec WDPRS with (nolock) 
					where WDPRS.DrugRequestPurchaseSpec_id = DRPS.DrugRequestPurchaseSpec_id) wdprsSum ";
		$filter .= " and (
					isnull(DRPS.DrugRequestPurchaseSpec_pKolvo,0) > isnull(wdprsSum.sum,0)
				)";
		$query = "
			select
				-- select
				DRPS.DrugRequestPurchaseSpec_id				
				,(isnull(TN.NAME + '; ', '') + isnull(DCM.DrugComplexMnn_RusName, '')) as Drug_Name
				,TN.NAME as Tradenames_Name
				,cast(DRPS.DrugRequestPurchaseSpec_Kolvo as decimal(12,2)) as 'DrugRequestPurchaseSpec_Kolvo'
				,cast(DRPS.DrugRequestPurchaseSpec_Sum as decimal(12,2)) as 'DrugRequestPurchaseSpec_Sum'
				,cast(DRPS.DrugRequestPurchaseSpec_Price as decimal(12,2)) as 'DrugRequestPurchaseSpec_Price'
				,DRPS.WhsDocumentUc_id
				,Okei.Okei_Name
				-- end select
			from
				-- from
				v_DrugRequestPurchaseSpec DRPS with (nolock)
				left join rls.DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = DRPS.DrugComplexMnn_id
				left join rls.TRADENAMES TN with(nolock) on TN.TRADENAMES_ID = DRPS.Tradenames_id
				left join v_Okei Okei with(nolock) on Okei.Okei_id = 120 -- пока так
				{$apply}
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				DRPS.DrugRequestPurchaseSpec_id desc
				-- end order by
		";
		$params['DrugRequest_id'] = $data['DrugRequest_id'];
		//echo getDebugSql($query, $params); die();
		if( !$data['paging'] == 1 ) {
			$params['start'] = $data['start'];
			$params['limit'] = $data['limit'];
			return $this->returnPagingData($query, $params);
		} else {
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
		}
	}
	
	/**
	 *	Читает список медикаментов лота
	 */
	function loadDrugListOnUnitOfTrading($data) {
		$filter = "1=1";
		$params = array();

		if( !empty($data['WhsDocumentUc_id']) ) {
			$filter .= " and WDPRS.WhsDocumentProcurementRequest_id = :WhsDocumentUc_id";
			$params['WhsDocumentUc_id'] = $data['WhsDocumentUc_id'];
		}
		
		$query = "
			select
				-- select
				WDPRS.WhsDocumentProcurementRequestSpec_id
				,WDPRS.WhsDocumentProcurementRequestSpec_id as WhsDocumentUc_id
				,case when DRPS.DrugComplexMnn_id is not null then DCM.DrugComplexMnn_RusName else TN.NAME end as Drug_Name
				,TN.NAME as Tradenames_Name	
				--,Okei.Okei_Name
				,REPLACE(CONVERT(varchar, CONVERT(money, isnull(WDPRS.WhsDocumentProcurementRequestSpec_PriceMax,0)), 1), ',', ' ') as WhsDocumentProcurementRequestSpec_PriceMax
				--,cast(isnull(WDPRS.WhsDocumentProcurementRequestSpec_PriceMax,0) as decimal(12,2)) as WhsDocumentProcurementRequestSpec_PriceMax
				,WDPRS.CalculatPriceType_id
				,CPT.CalculatPriceType_Name
				,case 
					when ( isnull(WDPRS.WhsDocumentProcurementRequestSpec_PriceMax,0) / isnull(WDPRS.WhsDocumentProcurementRequestSpec_Count,1) ) > 0
					then case when ( isnull(WDPRS.WhsDocumentProcurementRequestSpec_PriceMax,0) / isnull(WDPRS.WhsDocumentProcurementRequestSpec_Count,1) ) < 1
						then cast(cast(( isnull(WDPRS.WhsDocumentProcurementRequestSpec_PriceMax,0) / isnull(WDPRS.WhsDocumentProcurementRequestSpec_Count,1) ) as decimal(12,2)) as float)
						else cast(cast(( isnull(WDPRS.WhsDocumentProcurementRequestSpec_PriceMax,0) / isnull(WDPRS.WhsDocumentProcurementRequestSpec_Count,1) ) as float) as decimal(12,2)) end
					else 0 end
				as PriceForOkei
				--,cast(DRPS.DrugRequestPurchaseSpec_Sum as decimal(12,2)) as DrugRequestPurchaseSpec_Sum
				--,cast(( isnull(WDPRS.WhsDocumentProcurementRequestSpec_PriceMax,0) * isnull(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo,0) ) as decimal(12,2)) as DrugRequestPurchaseSpec_Sum
				,REPLACE(CONVERT(varchar, CONVERT(money, ( isnull(WDPRS.WhsDocumentProcurementRequestSpec_PriceMax,0) * isnull(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo,0) )), 1), ',', ' ') as DrugRequestPurchaseSpec_Sum
				,cast(isnull(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo,0) as decimal(12,2)) as WhsDocumentProcurementRequestSpec_Kolvo
				,convert(varchar,cast(WDPRS.WhsDocumentProcurementRequestSpec_CalcPriceDate as datetime),104) as WhsDocumentProcurementRequestSpec_CalcPriceDate
				,DCMC.DrugComplexMnnCode_Code
				,WDPRS.WhsDocumentProcurementRequestSpec_Name
				,case when (isnull(DRPS.DrugRequestPurchaseSpec_pKolvo,0) > isnull(wdprsSum.sum,0)) then (isnull(DRPS.DrugRequestPurchaseSpec_pKolvo,0) - isnull(wdprsSum.sum,0)) else 0 end as maxKolvo
				,WDPRS.GoodsUnit_id
				,gu.GoodsUnit_Name
				,WDPRS.WhsDocumentProcurementRequestSpec_Count
				,WDPRS.DrugComplexMnn_id
				,WDPRS.Tradenames_id as TRADENAMES_ID
	            ,(case when PhGr.ACTMATTERID is not null then 1 else 0 end) as InJnvlp
				-- end select
			from
				-- from
				v_WhsDocumentProcurementRequestSpec WDPRS with (nolock)
				left join rls.v_DrugComplexMnn DCM with (nolock) on DCM.DrugComplexMnn_id = WDPRS.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName DCMN with (nolock) on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
				left join rls.v_DrugComplexMnnCode DCMC with (nolock) on DCMC.DrugComplexMnn_id = WDPRS.DrugComplexMnn_id
				left join rls.v_TRADENAMES TN with (nolock) on TN.TRADENAMES_ID = WDPRS.Tradenames_id
				-- left join v_Okei Okei with (nolock) on Okei.Okei_id = WDPRS.Okei_id
				left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = WDPRS.GoodsUnit_id
				left join v_CalculatPriceType CPT with (nolock) on CPT.CalculatPriceType_id = WDPRS.CalculatPriceType_id
				left join v_DrugRequestPurchaseSpec DRPS with (nolock) on DRPS.DrugRequestPurchaseSpec_id = WDPRS.DrugRequestPurchaseSpec_id
				outer apply (
					select sum(WhsDPRS.WhsDocumentProcurementRequestSpec_Kolvo) as sum
					from v_WhsDocumentProcurementRequestSpec WhsDPRS with (nolock) 
					where WhsDPRS.DrugRequestPurchaseSpec_id = WDPRS.DrugRequestPurchaseSpec_id) wdprsSum
				outer apply (
                    select top 1
                        adl.ACTMATTERID
                    from
                        rls.AM_DF_LIMP adl with(nolock)
                    where
                        adl.ACTMATTERID = DCMN.ActMatters_id and
                        adl.DRUGFORMID = DCM.CLSDRUGFORMS_ID
                ) PhGr
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				WDPRS.WhsDocumentProcurementRequestSpec_id desc
				-- end order by
		";
		
		$params['start'] = $data['start'];
		$params['limit'] = $data['limit'];

		return $this->returnPagingData($query, $params);
	}
	
	/**
	 *	Возвращает параметры процедуры
	 */
	function getParamsByProcedure($data) {
		$filter = "1=1";
		$filter .= " and s.name = :scheme";
		$filter .= " and p.name = :proc";
		$filter .= " and t.is_user_defined = 0";

		$query = "
			select
				substring(ps.name, 2, len(ps.name)) as name,
				t.name as type,
				ps.is_output
			from
				sys.parameters ps with(nolock)
				inner join sys.procedures p on p.object_id = ps.object_id
				inner join sys.schemas s with(nolock) on s.schema_id = p.schema_id
				inner join sys.types t with(nolock) on t.system_type_id = ps.system_type_id and t.user_type_id = ps.user_type_id
			where
				{$filter}
			order by
				ps.parameter_id
		";
		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных
	 */
	function getDrugDataForCopy($data) {
		$query = "
			select top 1
				DRPS.DrugComplexMnn_id
				,Drug.Drug_id
				,120 as Okei_id
				,DRPS.DrugRequestPurchaseSpec_Kolvo as WhsDocumentProcurementRequestSpec_Kolvo
				,DRPS.DrugRequestPurchaseSpec_Price as WhsDocumentProcurementRequestSpec_PriceMax
				,DRPS.TRADENAMES_id as Tradenames_id
			from
				DrugRequestPurchaseSpec DRPS with(nolock)
				left join rls.Drug Drug with(nolock) on Drug.DrugComplexMnn_id = DRPS.DrugComplexMnn_id
			where
				DRPS.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id
		";
		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			return false;
		}
		return $result->result('array');
	}
	
	/**
	 *	Добавляет медикамент в лот
	 */
	function addDrugInUnitOfTrading($data) {
		if(isset($data['Lpu_id'])){
			$join = " left join v_Lpu lpu with (nolock) on lpu.Org_id = gpc.Org_id ";
			$where = " lpu.Lpu_id = :Lpu_id";
		} else {
			$data['Region_Number'] = $this->getRegionNumber();
			$join = "";
			$where = " gpc.Region_id = :Region_Number";
		}

		$query = "
			select top 1
				DRPS.DrugRequestPurchaseSpec_id
				,DRPS.DrugComplexMnn_id
				,Drug.Drug_id
				,120 as Okei_id
				,case when (isnull(DRPS.DrugRequestPurchaseSpec_Kolvo,0) > isnull(wdprsSum.sum,0)) then (DRPS.DrugRequestPurchaseSpec_Kolvo - isnull(wdprsSum.sum,0)) else 0 end as WhsDocumentProcurementRequestSpec_Kolvo
				,DRPS.DrugRequestPurchaseSpec_Price as WhsDocumentProcurementRequestSpec_PriceMax
				,DRPS.TRADENAMES_id as Tradenames_id
				,DRPS.CalculatPriceType_id
				,null as WhsDocumentProcurementRequestSpec_CalcPriceDate
				,null as WhsDocumentProcurementRequestSpec_Name
				,GU.GoodsUnit_id as GoodsUnit_id
				,GU.GoodsPackCount_Count as WhsDocumentProcurementRequestSpec_Count
			from
				v_DrugRequestPurchaseSpec DRPS with (nolock)
				left join rls.Drug Drug with(nolock) on Drug.DrugComplexMnn_id = DRPS.DrugComplexMnn_id
				outer apply (
					select top 1 
						gpc.GoodsUnit_id,
						gpc.GoodsPackCount_Count
					from v_GoodsPackCount gpc with (nolock)
					{$join}
					where gpc.DrugComplexMnn_id = DRPS.DrugComplexMnn_id and {$where}
				) GU
				outer apply (
					select sum(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo) as sum
					from v_WhsDocumentProcurementRequestSpec WDPRS with (nolock) 
					where WDPRS.DrugRequestPurchaseSpec_id = DRPS.DrugRequestPurchaseSpec_id
				) wdprsSum
			where
				DRPS.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id
		";
		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			return false;
		}
		$drugData = $result->result('array');
		$drugData = $drugData[0];
		$drugData['WhsDocumentProcurementRequest_id'] = $data['WhsDocumentUc_id'];
		$drugData['pmUser_id'] = $data['pmUser_id'];

		$procedure = "p_WhsDocumentProcurementRequestSpec_ins";
		
		$query = "
			declare
				@WhsDocumentProcurementRequestSpec_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo." . $procedure . "
				@WhsDocumentProcurementRequestSpec_id = @WhsDocumentProcurementRequestSpec_id output,
				@WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id,
				@DrugComplexMnn_id = :DrugComplexMnn_id,
				@Drug_id = :Drug_id,
				@Okei_id = :Okei_id,
				@WhsDocumentProcurementRequestSpec_Kolvo = :WhsDocumentProcurementRequestSpec_Kolvo,
				@WhsDocumentProcurementRequestSpec_PriceMax = :WhsDocumentProcurementRequestSpec_PriceMax,
				@Tradenames_id = :Tradenames_id,
				@CalculatPriceType_id = :CalculatPriceType_id,
				@WhsDocumentProcurementRequestSpec_CalcPriceDate = :WhsDocumentProcurementRequestSpec_CalcPriceDate,
				@DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id,
				@WhsDocumentProcurementRequestSpec_Name = :WhsDocumentProcurementRequestSpec_Name,
				@GoodsUnit_id = :GoodsUnit_id,
				@WhsDocumentProcurementRequestSpec_Count = :WhsDocumentProcurementRequestSpec_Count,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentProcurementRequestSpec_id as WhsDocumentProcurementRequestSpec_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		//echo getDebugSql($query, $drugData); die();
		$res = $this->db->query($query, $drugData);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Удаляет медикамент из лота
	 */
	function deleteDrugOfUnitOfTrading($data) {
		//return $this->setObject('DrugRequestPurchaseSpec')->setRow($data['DrugRequestPurchaseSpec_id'])->setValue('WhsDocumentUc_id', null);
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec dbo.p_WhsDocumentProcurementRequestSpec_del
				@WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Message;
		";
		
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
	}

	/**
	 *	Сохраняет медикамент в лоте с формы редактирования медикамента
	 */
	function saveDrugOfUnitOfTrading($data,$merge = false) {
		if(empty($data['WhsDocumentProcurementRequestSpec_id']))
			return false;
		$query = "
			select top 1
				*
			from
				v_WhsDocumentProcurementRequestSpec with (nolock)
			where
				WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id
		";
		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			return false;
		}
		$drugData = $result->result('array');
		$drugData = $drugData[0];

		if($merge){
			$drugData['WhsDocumentProcurementRequest_id'] = $data['WhsDocumentProcurementRequest_id'];
		} else {
			// Если не задать Okei_id апдейт не пройдет
			if(isset($data['Okei_id'])){
				$drugData['Okei_id'] = $data['Okei_id'];
			} else if(!isset($drugData['Okei_id'])){
				$drugData['Okei_id'] = 120; // Задаем дефолтное значение 120 - упаковка
			}
			$drugData['WhsDocumentProcurementRequestSpec_Kolvo'] = $data['WhsDocumentProcurementRequestSpec_Kolvo'];
			$drugData['WhsDocumentProcurementRequestSpec_Name'] = $data['WhsDocumentProcurementRequestSpec_Name'];
			$drugData['GoodsUnit_id'] = $data['GoodsUnit_id'];
			$drugData['WhsDocumentProcurementRequestSpec_Count'] = $data['WhsDocumentProcurementRequestSpec_Count'];
		}
		$drugData['pmUser_id'] = $data['pmUser_id'];

		$procedure = "p_WhsDocumentProcurementRequestSpec_upd";
		
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo." . $procedure . "
				@WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id,
				@WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id,
				@DrugComplexMnn_id = :DrugComplexMnn_id,
				@Drug_id = :Drug_id,
				@Okei_id = :Okei_id,
				@WhsDocumentProcurementRequestSpec_Kolvo = :WhsDocumentProcurementRequestSpec_Kolvo,
				@WhsDocumentProcurementRequestSpec_PriceMax = :WhsDocumentProcurementRequestSpec_PriceMax,
				@Tradenames_id = :Tradenames_id,
				@CalculatPriceType_id = :CalculatPriceType_id,
				@WhsDocumentProcurementRequestSpec_CalcPriceDate = :WhsDocumentProcurementRequestSpec_CalcPriceDate,
				@DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id,
				@WhsDocumentProcurementRequestSpec_Name = :WhsDocumentProcurementRequestSpec_Name,
				@GoodsUnit_id = :GoodsUnit_id,
				@WhsDocumentProcurementRequestSpec_Count = :WhsDocumentProcurementRequestSpec_Count,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $drugData);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Перемещает медикамент в другой лот
	 */
	function moveDrugToUnitOfTrading($data) {

		$this->setObject('WhsDocumentProcurementRequestSpec')->setRow($data['WhsDocumentProcurementRequest_id']);
		$record = $this->getRecordById($data['WhsDocumentProcurementRequestSpec_id']);
		if( !is_array($record) ) {
			return false;
		}
		$record['WhsDocumentProcurementRequest_id'] = $data['WhsDocumentProcurementRequest_id'];
		return $this->saveDrugOfUnitOfTrading(array_merge($record, array('pmUser_id' => $data['pmUser_id'])),true);
	}
	
	/**
	 *	Получение данных записи таблицы по идентификатору
	 */
	private function getRecordById($id) {
		if( !isset($id) || empty($id) || $id < 0 ) {
			return null;
		}
		
		$query = "
			select name from sys.views with(nolock) where name like 'v_{$this->objectName}' and schema_id = 1
		";
		$result = $this->db->query($query);
		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		if( count($result) > 0 ) {
			$from = "dbo.v_{$this->objectName}";
		} else {
			$from = "dbo.{$this->objectName}";
		}
		
		$query = "
			select top 1
				*
			from
				{$from}
			where
				{$this->objectName}_id = {$id}
		";
		$result = $this->db->query($query);
		if ( is_object($result) ) {
			$result = $result->result('array');
			return isset($result[0]) ? $result[0] : null;
		} else {
			return false;
		}
	}
	
	/**
	 *	Устанавливает значение объекта БД и значение ключа строки с которой работаем в дальнейшем
	 */
	function setObject($objectName) {
		$this->objectName = $objectName;
		return $this;
	}
	
	/**
	 *	Устанавливает значение ключа строки с которой работаем в дальнейшем
	 */
	function setRow($objectKey) {
		$this->objectKey = $objectKey;
		return $this;
	}
	
	/**
	 *	Устанавливает значение поля объекта БД
	 */
	function setValue($field, $value) {
		if( empty($this->objectName) || empty($this->objectKey) )
			return false;
		
		$procedure = "p_" . $this->objectName . "_upd";
		$params = $this->getParamsByProcedure(array('scheme' => 'dbo', 'proc' => $procedure));
		//print_r($params);
	
		$query = "
			declare
				@{$this->objectName}_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			set @{$this->objectName}_id = :{$this->objectName}_id;
			exec dbo." . $procedure . "\n";
		
		foreach($params as $k=>$param) {
			$query .= "\t\t\t\t@" . $param['name'] . " = " . ( $param['is_output'] ? "@".$param['name']." output" : ":".$param['name'] );
			$query .= ( count($params) == ++$k ? ";" : "," ) . "\n";
		}
		$query .= "\t\t\tselect @Error_Code as Error_Code, @Error_Message as Error_Message;";
		//var_dump($query);
		
		$record = $this->getRecordById($this->objectKey);
		if( !is_array($record) ) {
			return false;
		}
		
		$record[$this->objectName.'_id'] = $this->objectKey;
		$sp = getSessionParams();
		$record['pmUser_id'] = $sp['pmUser_id'];
		
		if( array_key_exists($field, $record) ) {
			$record[$field] = $value;
			$result = $this->db->query($query, $record);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 *	Загружает данные о сводных заявках на закуп
	 */
	function loadDrugRequest($data) {
		$filter = "1=1";
		$filter .= " and DR.DrugRequestCategory_id = 3"; // категория - "сводная заявка"
		$filter .= " and DR.DrugRequestStatus_id = 3"; // статус - "утвержденная"
		if( !empty($data['begDate']) && !empty($data['endDate']) ) {
			$filter .= "
				and DR.DrugRequest_id in (
					select DrugRequestPurchase.DrugRequest_id from DrugRequestPurchase  with (nolock)
						left join DrugRequest with (nolock) on DrugRequest.DrugRequest_id = DrugRequestPurchase.DrugRequest_lid
					where
						DrugRequestPeriod_id in (
							select DrugRequestPeriod_id from DrugRequestPeriod with (nolock)
							where
								DrugRequestPeriod_begDate <= :endDate and
								DrugRequestPeriod_endDate >= :begDate
						)
				)			
			";
		}
		
		$query = "
			select
				DR.DrugRequest_id
				,DR.DrugRequest_Name
			from
				DrugRequest DR with(nolock)
				left join DrugRequestPeriod DRP with(nolock) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
			where
				{$filter}
		";
		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Чтение данных по лоту для экспорта
	 */
	function getUnitOfTradingForExport($data) {
		$query = "
			select
				WDU.WhsDocumentUc_Num as U_NUM
				,WDU.WhsDocumentUc_Name as U_NAME
				,case when isnull(WDU.WhsDocumentStatusType_id,1) = 2
					then cast(WDU.WhsDocumentUc_Sum as decimal(12,2))
					else cast(( select sum(WhsDocumentProcurementRequestSpec_Kolvo * WhsDocumentProcurementRequestSpec_PriceMax) from v_WhsDocumentProcurementRequestSpec with(nolock) where WhsDocumentProcurementRequest_id = WDU.WhsDocumentProcurementRequest_id ) as decimal(12,2))
				end as U_SUM
				,convert(varchar(10), cast(WDU.WhsDocumentUc_Date as datetime),104) as U_DATE
			from
				v_WhsDocumentProcurementRequest WDU with(nolock)
			where
				WDU.WhsDocumentType_id = 5
				and WDU.WhsDocumentUc_id = :WhsDocumentUc_id
		";
		$result = $this->db->query($query, array(
			'WhsDocumentUc_id' => $data['WhsDocumentProcurementRequest_id']
		));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}	
	}
	
	/**
	 *	Чтение данных по медикаментам лота для экспорта
	 */
	function getDrugListForExport($data) {
		$query = "
			select
				case when WDPRS.DrugComplexMnn_id is not null then DCM.DrugComplexMnn_RusName else TN.NAME end as D_NAME
				,Okei.Okei_Name as D_OKEI
				,cast(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo as decimal(12,2)) as D_KOLVO
				,cast(WDPRS.WhsDocumentProcurementRequestSpec_PriceMax as decimal(12,2)) as D_PRICE
				,cast((WDPRS.WhsDocumentProcurementRequestSpec_Kolvo * WDPRS.WhsDocumentProcurementRequestSpec_PriceMax) as decimal(12,2)) as D_SUM
			from
				WhsDocumentProcurementRequestSpec WDPRS with(nolock)
				left join rls.DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = WDPRS.DrugComplexMnn_id
				left join rls.TRADENAMES TN with(nolock) on TN.TRADENAMES_ID = WDPRS.Tradenames_id
				left join v_Okei Okei with(nolock) on Okei.Okei_id = WDPRS.Okei_id			
			where
				WDPRS.WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id
		";
		$result = $this->db->query($query, array(
			'WhsDocumentProcurementRequest_id' => $data['WhsDocumentProcurementRequest_id']
		));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Чтение данных по медикаментам лота для печати
	 */
	function getDrugListForPrint($data) {
		$query = "
			declare
				@Year numeric,
				@WhsDocumentCostItemType_SubContract varchar(max);

			set @Year = (
				select
					year(min(period.DrugRequestPeriod_begDate))
				from
					WhsDocumentProcurementRequestSpec wdprs with(nolock)
					left join DrugRequestPurchaseSpec drps with(nolock) on drps.DrugComplexMnn_id = wdprs.DrugComplexMnn_id and drps.DrugRequestPurchaseSpec_id = wdprs.DrugRequestPurchaseSpec_id
					left join DrugRequestPurchase drp with(nolock) on drp.DrugRequest_id = drps.DrugRequest_id
					left join DrugRequest dr with(nolock) on dr.DrugRequest_id = drp.DrugRequest_lid --региональные заявки
					left join DrugRequestPeriod period with(nolock) on period.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
				where
					wdprs.WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id
			);

			set @WhsDocumentCostItemType_SubContract = (
				select  top 1
					wdcit.WhsDocumentCostItemType_SubContract
				from
					WhsDocumentProcurementRequest wdpr with (nolock)
					inner join WhsDocumentCostItemType wdcit with(nolock) on wdcit.WhsDocumentCostItemType_id  = wdpr.WhsDocumentCostItemType_id
				where
					wdpr.WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id
			);

			select
				row_number() over(order by WDPRS.WhsDocumentProcurementRequest_id desc) as Spec_Num
				,WDU.WhsDocumentUc_Name as Uot_Name
				,WDU.WhsDocumentUc_Num as Uot_Num
				,@Year as Year
				,@WhsDocumentCostItemType_SubContract as WhsDocumentCostItemType_SubContract
				,case when WDPRS.DrugComplexMnn_id is not null then DCM.DrugComplexMnn_RusName else TN.NAME end as Spec_Name
				,Okei.Okei_Name as Spec_Okei
				,CDF.FULLNAME as Spec_Drugform
				,isnull(TN.NAME, '') as Spec_TNName
				,cast(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo as decimal(12,2)) as Spec_Kolvo
				,cast(WDPRS.WhsDocumentProcurementRequestSpec_PriceMax as decimal(12,2)) as Spec_PriceMax
				,cast((WDPRS.WhsDocumentProcurementRequestSpec_Kolvo * WDPRS.WhsDocumentProcurementRequestSpec_PriceMax) as decimal(12,2)) as Spec_Sum
			from
				WhsDocumentProcurementRequestSpec WDPRS with(nolock)
				left join WhsDocumentUc WDU with(nolock) on WDU.WhsDocumentUc_id = WDPRS.WhsDocumentProcurementRequest_id and WDU.WhsDocumentType_id = 5
				left join DrugRequestPurchaseSpec DRPS with(nolock) on DRPS.DrugComplexMnn_id = WDPRS.DrugComplexMnn_id and DRPS.DrugRequestPurchaseSpec_id = WDPRS.DrugRequestPurchaseSpec_id
				left join rls.Drug Drug with(nolock) on Drug.Drug_id = WDPRS.Drug_id
				left join rls.DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = WDPRS.DrugComplexMnn_id
				left join rls.CLSDRUGFORMS CDF with(nolock) on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
				left join rls.TRADENAMES TN with(nolock) on TN.TRADENAMES_ID = WDPRS.Tradenames_id
				left join v_Okei Okei with(nolock) on Okei.Okei_id = WDPRS.Okei_id
			where
				WDPRS.WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id
				
		";
		$result = $this->db->query($query, array(
			'WhsDocumentProcurementRequest_id' => $data['WhsDocumentProcurementRequest_id']
		));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Проверяет подписан ли лот
	 *	Возвращает true если лот подписан, иначе false
	 */
	function isSignedUnitOfTrading($data) {

		$query = "
			select
				case when isnull(WhsDocumentStatusType_id,1) = 2 then 1 else 0 end as cnt
			from
				v_WhsDocumentProcurementRequest with (nolock)
			where
				WhsDocumentProcurementRequest_id = :WhsDocumentUc_id
		";
		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		return $result[0]['cnt'] > 0;
	}
	
	/**
	 *	Подписывает лот
	 */
	function signUnitOfTrading($data) {

		$this->setObject('WhsDocumentProcurementRequest')->setRow($data['WhsDocumentUc_id']);
		$record = $this->getRecordById($data['WhsDocumentUc_id']);
		if( !is_array($record) ) {
			return false;
		}
		$record['WhsDocumentStatusType_id'] = 2;
		return $this->saveUnitOfTrading(array_merge($record, array('pmUser_id' => $data['pmUser_id'])));
	}
	
	/**
	 *	Отмена подписания лота
	 */
	function unsignUnitOfTrading($data) {

		$this->setObject('WhsDocumentProcurementRequest')->setRow($data['WhsDocumentUc_id']);
		$record = $this->getRecordById($data['WhsDocumentUc_id']);
		if( !is_array($record) ) {
			return false;
		}
		$record['WhsDocumentStatusType_id'] = 1;
		return $this->saveUnitOfTrading(array_merge($record, array('pmUser_id' => $data['pmUser_id'])));
	}

	/**
	 *	Смена статуса лота
	 */
	function changeUcStatusUnitOfTrading($data) {

		$this->setObject('WhsDocumentProcurementRequest')->setRow($data['WhsDocumentUc_id']);
		$record = $this->getRecordById($data['WhsDocumentUc_id']);
		if( !is_array($record) ) {
			return false;
		}
		$record['WhsDocumentUcStatusType_id'] = $data['WhsDocumentUcStatusType_id'];
		return $this->saveUnitOfTrading(array_merge($record, array('pmUser_id' => $data['pmUser_id'])));
	}
	
	/**
	 *	Возвращает идентификатор лота, которому принадлежит переданный медикамент
	 */
	function getUnitOfTradingIdByDrugId($data) {
		$query = "
			select top 1 WhsDocumentProcurementRequest_id from WhsDocumentProcurementRequestSpec with(nolock) where WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id
		";
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) > 0 ) {
			return $res[0]['WhsDocumentProcurementRequest_id'];
		} else {
			return false;
		}
	}
	
	/**
	 *	Возвращает список лотов по сводной заявке на закуп
	 */
	function getUnitOfTradingListOnDrugRequest($data) {
		if( empty($data['DrugRequest_id']) ) {
			return false;
		}
		$query = "
			select
				WDUC.WhsDocumentUc_id
			from
				WhsDocumentUc WDUC with(nolock)
			where
				WDUC.WhsDocumentUc_id in ( select distinct WhsDPRS.WhsDocumentProcurementRequest_id 
			from v_DrugRequestPurchaseSpec DrugRPS with (nolock)
			left join v_WhsDocumentProcurementRequestSpec WhsDPRS with (nolock) on WhsDPRS.DrugRequestPurchaseSpec_id = DrugRPS.DrugRequestPurchaseSpec_id
			 where DrugRPS.DrugRequest_id = :DrugRequest_id )
		";
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 *	Возврашает массив идентификаторов медикаментов, включенных в лот
	 */
	function getDrugsOfUnitOfTrading($data) {
		$query = "
			select WhsDocumentProcurementRequestSpec_id from v_WhsDocumentProcurementRequestSpec with(nolock) where WhsDocumentProcurementRequest_id = :WhsDocumentUc_id
		";
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		$results = array();
		foreach($res as $r) {
			$results[] = $r['WhsDocumentProcurementRequestSpec_id'];
		}
		return $results;
	}
	
	/**
	 *	Возврашает кол-во медикаментов в лоте (не подписанном!!!)
	 */
	function getCountDrugsOnUnitOfTrading($data) {
		$query = "
			select count(*) as cnt from WhsDocumentProcurementRequestSpec with(nolock) where WhsDocumentProcurementRequest_id = :WhsDocumentUc_id
		";
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( isset($res[0]) ) {
			return $res[0]['cnt'];
		} else {
			return false;
		}
	}
	
	/**
	 *	Возвращает медикаменты сводной заявки с различными признаками
	 */
	function getDrugsOfDrugRequestForFormation($data) {
		if( empty($data['DrugRequest_id']) ) {
			return false;
		}
		$query = "
			select
				DRPS.DrugRequestPurchaseSpec_id,
				DRPS.DrugFinance_id,
				case when DRPS.DrugComplexMnn_id is not null then DCM.DrugComplexMnn_RusName else TN.NAME end as Drug_Name,
				case when DRPS.DrugRequestPurchaseSpec_Sum >= :sum then 1 else 0 end as isSUM,
				case when ISVK.cnt > 0 then 1 else 0 end as isVK,
				case when DRPS.TRADENAMES_id is not null then 1 else 0 end as isTN,
				--case when ACTMATTERS.STRONGGROUPID > 0 or ACTMATTERS.NARCOGROUPID > 0 then 1 else 0 end as isNARC,
				0 as isNARC,
				CPG.CLSPHARMAGROUP_ID as Pharmagroup_id,
				CPG.NAME as Pharmagroup_Name,
				CATC.NAME as Atc_Name,
				ACTMATTERS.ACTMATTERS_ID as Actmatters_id,
				ACTMATTERS.RUSNAME as Actmatters_Name,
				CMPG.CLS_MZ_PHGROUP_ID as MzPharmagroup_id,
				CMPG.NAME as MzPharmagroup_Name,
				firm_count.cnt as firm_count
			from
				DrugRequestPurchaseSpec DRPS with(nolock)
				left join rls.DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = DRPS.DrugComplexMnn_id
				left join rls.ACTMATTERS ACTMATTERS with(nolock) on ACTMATTERS.ACTMATTERS_ID = DCM.ACTMATTERS_id
				left join rls.TRADENAMES TN with(nolock) on TN.TRADENAMES_ID = DRPS.TRADENAMES_id
				outer apply (
					select top 1
						*
					from
						rls.Drug with(nolock)
					where
						Drug.DrugComplexMnn_id = DRPS.DrugComplexMnn_id
					order by
						Drug_id
				) Drug
				outer apply ( -- подсчет количества производителей медикамента
					select
						count(FIRMID) as cnt
					from (
						select distinct
							p.FIRMID
						from
							rls.Drug d with(nolock)
							left join rls.PREP p with(nolock) on p.Prep_id = d.DrugPrep_id
							left join rls.FIRMS f with(nolock) on f.FIRMS_ID = p.FIRMID
						where
							d.DrugComplexMnn_id = DRPS.DrugComplexMnn_id
					) cntq
				) firm_count
				left join rls.PREP Prep with(nolock) on Prep.Prep_id = Drug.DrugPrep_id
				--left join rls.PREP_PHARMAGROUP PP with(nolock) on PP.PREPID = Prep.Prep_id
				outer apply (
					select top 1
						UNIQID
					from
						rls.PREP_PHARMAGROUP with(nolock)
					where
						PREPID = Prep.Prep_id
					order by
						UNIQID
				) PP
				left join rls.CLSPHARMAGROUP CPG with(nolock) on CPG.CLSPHARMAGROUP_ID = PP.UNIQID				
				left join rls.PREP_ATC PATC with(nolock) on PATC.PREPID = Prep.Prep_id
				left join rls.CLSATC CATC with(nolock) on CATC.CLSATC_ID = PATC.UNIQID
				outer apply (
					select top 1
						iCMPG.CLS_MZ_PHGROUP_ID,
						iCMPG.NAME
					from
						rls.ACTMATTERS_DRUGFORMS AD with(nolock)
						left join rls.CLS_MZ_PHGROUP iCMPG with(nolock) on iCMPG.CLS_MZ_PHGROUP_ID = AD.MZ_PHGR_ID
					where
						ACTMATTERID = ACTMATTERS.ACTMATTERS_ID and
						iCMPG.CLS_MZ_PHGROUP_ID > 0
					order by
						iCMPG.CLS_MZ_PHGROUP_ID
				) CMPG
				outer apply (
					select
						count(*) as cnt
					from
						rls.ACTMATTERS_DRUGFORMS AD with(nolock)
					where
						ACTMATTERID = ACTMATTERS.ACTMATTERS_ID and
						MZ_PHGR_ID = 66
				) ISVK
				outer apply (
					select sum(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo) as sum
					from v_WhsDocumentProcurementRequestSpec WDPRS with (nolock) 
					where WDPRS.DrugRequestPurchaseSpec_id = DRPS.DrugRequestPurchaseSpec_id
				) wdprsSum 
			where
				DRPS.DrugRequest_id = :DrugRequest_id
				-- and DRPS.WhsDocumentUc_id is null
				-- and DRPS.DrugFinance_id is not null
				-- все медикаменты из списка медикаментов сводной заявки на закуп, у которых больше нуля разница между количеством, указанным в колонке «на закуп» и количеством, уже включенным в лоты
				and (isnull(DRPS.DrugRequestPurchaseSpec_pKolvo,0) > isnull(wdprsSum.sum,0))
		";
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}

	/**
	 * Получение списка статей расхода
	 */
	function getCostItemTypeList($data) {
		if( empty($data['PersonRegisterType_id']) ) {
			return array();
		}
		$result = array();
		$query = "
			select
				WhsDocumentCostItemType_id,
				DrugFinance_id
			from
				v_WhsDocumentCostItemType with(nolock)
			where
				PersonRegisterType_id = :PersonRegisterType_id
		";
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return array();
		}
		$res = $res->result('array');
		foreach($res as $item) {
			$result[$item['DrugFinance_id']] = $item['WhsDocumentCostItemType_id'];
		}
		return $result;
	}

	/**
	 * Получение наименования группы
	 */
	function getClsAtcName($atc) {
		$name = "Группа ".$atc;

		if (!empty($atc)) {
			$query = "
				select top 1
					ca.NAME
				from
					rls.CLSATC ca with (nolock)
				where
					ca.NAME like :atc
			";
			$res = $this->db->query($query, array(
				'atc' => $atc.' %'
			));
			if (is_object($res)) {
				$res = $res->result('array');
				if (count($res) > 0 && !empty($res[0]['NAME'])) {
					$name = $res[0]['NAME'];
				}
			}
		}
		return $name;
	}

	/**
	 * Получение списка дочерних ГК
	 */
	function getAssociatedWhsDocumentSupply($data) {
		if( empty($data['WhsDocumentUc_id']) ) {
			return false;
		}
		$query = "
			select
				WhsDocumentSupply_id,
				WhsDocumentUc_Num
			from
				v_WhsDocumentSupply with (nolock)
			where
				WhsDocumentUc_pid = :WhsDocumentUc_id
		";
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}

	/**
	 * Получение идентификаторов ГК по идентификаторам лотов
	 */
	function getWhsDocumentSupplyByUotId($data) {
		if( !isset($data['UotList']) || count($data['UotList']) < 1 ) {
			return false;
		}

		$uot_list = join($data['UotList'], ', ');

		$query = "
			select
				WhsDocumentUc_id as WhsDocumentSupply_id,
				WhsDocumentUc_pid as WhsDocumentProcurementRequest_id
			from
				v_WhsDocumentSupply with(nolock)
			where
				WhsDocumentUc_pid in ({$uot_list});
		";

		$res = $this->db->query($query, array());
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для обоснования цены
	 */
	function getWhsDocumentUcRationalFields($data) {
		if( !isset($data['lot_id']) ) {
			return false;
		}
		$resarr = array();

		//Количество медикаментов в лоте для шапки отчета и подсчет медикаментов с типами расчета цен
		$query = "
			select
				POT.PurchObjType_Name,
				cnt1.cn as calctype1,
				cnt2.cn as calctype2,
				WDPRS.WhsDocumentProcurementRequestSpec_Name as WName,
				WDPR.WhsDocumentUc_Name as LotName
			from
				v_WhsDocumentProcurementRequest WDPR with (nolock)
				left join v_WhsDocumentProcurementRequestSpec WDPRS with (nolock) on WDPRS.WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id
				outer apply(
					select count(WhsDocumentProcurementRequestSpec_id) as cn
					from v_WhsDocumentProcurementRequestSpec WDPRS1 with (nolock)
					where WDPRS1.WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id
					and WDPRS1.CalculatPriceType_id = 1
					)cnt1
				outer apply(
					select count(WhsDocumentProcurementRequestSpec_id) as cn
					from v_WhsDocumentProcurementRequestSpec WDPRS2 with (nolock)
					where WDPRS2.WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id
					and WDPRS2.CalculatPriceType_id = 2
					)cnt2
				left join v_PurchObjType POT with (nolock) on POT.PurchObjType_id = WDPR.PurchObjType_id
			where
				WDPR.WhsDocumentUc_id = :WhsDocumentUc_id
		";

		$res = $this->db->query($query, array('WhsDocumentUc_id'=>$data['lot_id']));
		if ( is_object($res) ) {
			$resarr['drugslist'] = $res->result('array');
		}

		if(isset($resarr['drugslist']) && $resarr['drugslist'][0]['calctype1'] > 0) {

			//таблица 1 - метод сопоставимых рыночных цен
			$query = "
				select
					WDPRS.WhsDocumentProcurementRequestSpec_id as WDPRS_id,
					rtrim(isnull(okpd.Okpd_Code,'') + ' ' + isnull(okpd.Okpd_Name,'')) as Okpd_Name,
					DCMN.DrugComplexMnnName_Name as DMnnName,
					case when TN.NAME = DCMN.DrugComplexMnnName_Name then null else TN.NAME end as Tradename,
					CLSD.FULLNAME as DrugForm,
					DCMD.DrugComplexMnnDose_Name as DoseName,
					GU.GoodsUnit_Nick as GUNick,
					cast((isnull(WDPRS.WhsDocumentProcurementRequestSpec_Count,0) * isnull(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo,0)) as float) as wWCount,
					cast(isnull(WDPRS.WhsDocumentProcurementRequestSpec_Count,0) as float) as WCount,
					DCMF.DrugComplexMnnFas_Name as FasName,
					cast(isnull(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo,0) as float) as SpecKolvo,
					(isnull(WDPRS.WhsDocumentProcurementRequestSpec_PriceMax,0) * isnull(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo,0)) as rowprice
				from
					v_WhsDocumentProcurementRequest WDPR with (nolock)
					left join v_WhsDocumentProcurementRequestSpec WDPRS with (nolock) on WDPRS.WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id
					left join v_WhsDocumentProcurementRequestSpecDop WDPRSD with (nolock) on WDPRSD.WhsDocumentUc_id = WDPR.WhsDocumentUc_id
					left join v_Okpd okpd with (nolock) on okpd.Okpd_id = WDPRSD.Okpd_id
					left join rls.v_DrugComplexMnn DCM with (nolock) on DCM.DrugComplexMnn_id = WDPRS.DrugComplexMnn_id
					left join rls.v_DrugComplexMnnName DCMN with (nolock) on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
					left join rls.v_TRADENAMES TN with (nolock) on TN.TRADENAMES_ID = WDPRS.Tradenames_id
					left join rls.CLSDRUGFORMS CLSD with (nolock) on CLSD.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
					left join rls.DrugComplexMnnDose DCMD with (nolock) on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id
					left join v_GoodsUnit GU with (nolock) on GU.GoodsUnit_id = WDPRS.GoodsUnit_id
					left join rls.v_DrugComplexMnnFas DCMF with (nolock) on DCMF.DrugComplexMnnFas_id = DCM.DrugComplexMnnFas_id
				where
					WDPR.WhsDocumentUc_id = :WhsDocumentUc_id
					and WDPRS.CalculatPriceType_id = 1
			";

			$res1 = $this->db->query($query, array('WhsDocumentUc_id'=>$data['lot_id']));
			if ( is_object($res1) ) {
				$resarr['drugs1'] = $res1->result('array');
			}

			$query = "
				select 
					COUNT(DISTINCT WDS.WhsDocumentSupply_id) as ContractCount,
					COUNT(DISTINCT CO.Org_id) as SupplierCount
				from
					v_WhsDocumentProcurementRequest WDPR with (nolock)
					left join v_WhsDocumentProcurementRequestSpec WDPRS with (nolock) on WDPRS.WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id
					left join v_DrugRequestExec DRE with (nolock) on DRE.DrugRequestPurchaseSpec_id = WDPRS.DrugRequestPurchaseSpec_id
					left join v_WhsDocumentSupplySpec WDSS with (nolock) on WDSS.WhsDocumentSupplySpec_id = DRE.WhsDocumentSupplySpec_id
					left join v_WhsDocumentSupply WDS with (nolock) on WDS.WhsDocumentSupply_id = WDSS.WhsDocumentSupply_id
					-- left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = WDPRS.Drug_id
					left join rls.v_Drug Drug with (nolock) on Drug.DrugComplexMnn_id = WDPRS.DrugComplexMnn_id
					left join v_CommercialOfferDrug COD with (nolock) on COD.DrugPrepFas_id = Drug.DrugPrepFas_id
					left join v_CommercialOffer CO with (nolock) on CO.CommercialOffer_id = COD.CommercialOffer_id
				where
					WDPR.WhsDocumentUc_id = :WhsDocumentUc_id 
					and WDPRS.CalculatPriceType_id = 1
			";
			$result1 = $this->db->query($query, array('WhsDocumentUc_id'=>$data['lot_id']));
			if ( is_object($result1) ){
				$counts = $result1->result('array');
				$resarr['counts'] = $counts[0];
				$contracts = array();
				$suppliers = array();
				if($counts[0]['ContractCount']>0){
					$query = "
						select 
							WDPRS.WhsDocumentProcurementRequestSpec_id as WDPRS_id,
							WDS.WhsDocumentUc_id as id,
							WDS.WhsDocumentUc_Name as Name,
							isnull(WDSS.WhsDocumentSupplySpec_Price,0) as price
						from
							v_WhsDocumentProcurementRequest WDPR with (nolock)
							left join v_WhsDocumentProcurementRequestSpec WDPRS with (nolock) on WDPRS.WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id
							left join v_DrugRequestExec DRE with (nolock) on DRE.DrugRequestPurchaseSpec_id = WDPRS.DrugRequestPurchaseSpec_id
							left join v_WhsDocumentSupplySpec WDSS with (nolock) on WDSS.WhsDocumentSupplySpec_id = DRE.WhsDocumentSupplySpec_id
							left join v_WhsDocumentSupply WDS with (nolock) on WDS.WhsDocumentSupply_id = WDSS.WhsDocumentSupply_id
						where
							WDPR.WhsDocumentUc_id = :WhsDocumentUc_id
							and WDPRS.CalculatPriceType_id = 1
					";
					$result2 = $this->db->query($query, array('WhsDocumentUc_id'=>$data['lot_id']));
					if ( is_object($result2) ){
						$contracts = $result2->result('array');
					}
				}
				if($counts[0]['SupplierCount']>0){
					$query = "
						select 
							WDPRS.WhsDocumentProcurementRequestSpec_id as WDPRS_id,
							org.Org_id as id,
							org.Org_Nick as Name,
							isnull(COD.CommercialOfferDrug_Price,0) as price
						from
							v_WhsDocumentProcurementRequest WDPR with (nolock)
							left join v_WhsDocumentProcurementRequestSpec WDPRS with (nolock) on WDPRS.WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id
							-- left join rls.v_Drug Drug with (nolock) on Drug.DrugComplexMnn_id = WDPRS.DrugComplexMnn_id
							-- left join v_CommercialOfferDrug COD with (nolock) on COD.DrugPrepFas_id = Drug.DrugPrepFas_id
							left join v_WhsDocumentCommercialOfferDrug wdcod with (nolock) on wdcod.WhsDocumentProcurementRequestSpec_id = WDPRS.WhsDocumentProcurementRequestSpec_id
							left join v_CommercialOfferDrug COD with (nolock) on COD.CommercialOfferDrug_id = wdcod.CommercialOfferDrug_id
							left join v_CommercialOffer CO with (nolock) on CO.CommercialOffer_id = COD.CommercialOffer_id
							left join v_Org org with (nolock) on org.Org_id = CO.Org_id
						where
							WDPR.WhsDocumentUc_id = :WhsDocumentUc_id
							and WDPRS.CalculatPriceType_id = 1
					";
					$result3 = $this->db->query($query, array('WhsDocumentUc_id'=>$data['lot_id']));
					if ( is_object($result3) ){
						$suppliers = $result3->result('array');
					}
				}
				$resarr['contracts'] = $contracts;
				$resarr['suppliers'] = $suppliers;
			}

		}

		if(isset($resarr['drugslist']) && $resarr['drugslist'][0]['calctype2'] > 0) {

			//таблица 2 - тарифный метод 
			$query = "
					select
						WDPRS.WhsDocumentProcurementRequestSpec_id as WDPRS_id,
						rtrim(isnull(okpd.Okpd_Code,'') + ' ' + isnull(okpd.Okpd_Name,'')) as Okpd_Name,
						DCMN.DrugComplexMnnName_Name as DMnnName,
						case when TN.NAME = DCMN.DrugComplexMnnName_Name then null else TN.NAME end as Tradename,
						CLSD.FULLNAME as DrugForm,
						DCMD.DrugComplexMnnDose_Name as DoseName,
						GU.GoodsUnit_Nick as GUNick,
						cast((isnull(WDPRS.WhsDocumentProcurementRequestSpec_Count,0) * isnull(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo,0)) as float) as wWCount,
						cast(isnull(WDPRS.WhsDocumentProcurementRequestSpec_Count,0) as float) as WCount,
						DCMF.DrugComplexMnnFas_Name as FasName,
						cast(isnull(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo,0) as float) as SpecKolvo,
						(isnull(WDPRS.WhsDocumentProcurementRequestSpec_PriceMax,0) * isnull(WDPRS.WhsDocumentProcurementRequestSpec_Kolvo,0)) as rowprice
					from
						v_WhsDocumentProcurementRequest WDPR with (nolock)
						left join v_WhsDocumentProcurementRequestSpec WDPRS with (nolock) on WDPRS.WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id
						left join v_WhsDocumentProcurementRequestSpecDop WDPRSD with (nolock) on WDPRSD.WhsDocumentUc_id = WDPR.WhsDocumentUc_id
						left join v_Okpd okpd with (nolock) on okpd.Okpd_id = WDPRSD.Okpd_id
						left join rls.v_DrugComplexMnn DCM with (nolock) on DCM.DrugComplexMnn_id = WDPRS.DrugComplexMnn_id
						left join rls.v_DrugComplexMnnName DCMN with (nolock) on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
						left join rls.v_TRADENAMES TN with (nolock) on TN.TRADENAMES_ID = WDPRS.Tradenames_id
						left join rls.CLSDRUGFORMS CLSD with (nolock) on CLSD.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
						left join rls.DrugComplexMnnDose DCMD with (nolock) on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id
						left join v_GoodsUnit GU with (nolock) on GU.GoodsUnit_id = WDPRS.GoodsUnit_id
						left join rls.v_DrugComplexMnnFas DCMF with (nolock) on DCMF.DrugComplexMnnFas_id = DCM.DrugComplexMnnFas_id
					where
						WDPR.WhsDocumentUc_id = :WhsDocumentUc_id
						and WDPRS.CalculatPriceType_id = 2
				";

			$res2 = $this->db->query($query, array('WhsDocumentUc_id'=>$data['lot_id']));
			if ( is_object($res2) ) {
				$resarr['drugs2'] = $res2->result('array');
			}

			//Количество цен
			$query = "
				select 
					COUNT(DISTINCT WDPPL.WhsDocumentProcurementPriceLink_id) as LinkCount
				from
					v_WhsDocumentProcurementRequest WDPR with (nolock)
					left join v_WhsDocumentProcurementRequestSpec WDPRS with (nolock) on WDPRS.WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id
					left join v_WhsDocumentProcurementPriceLink WDPPL with (nolock) on WDPPL.WhsDocumentProcurementRequestSpec_id = WDPRS.WhsDocumentProcurementRequestSpec_id
				where
					WDPR.WhsDocumentUc_id = :WhsDocumentUc_id
					and WDPRS.CalculatPriceType_id = 2
			";
			$result4 = $this->db->query($query, array('WhsDocumentUc_id'=>$data['lot_id']));
			if ( is_object($result4) ){
				$linkcounts = $result4->result('array');
				$resarr['linkcounts'] = $linkcounts[0];

				//Значения цен и их даты
				$links = array();
				if($linkcounts[0]['LinkCount']>0){
					$query = "
						select 
							WDPRS.WhsDocumentProcurementRequestSpec_id as WDPRS_id,
							WDPPL.WhsDocumentProcurementPriceLink_id as id,
							convert(varchar,cast(WDPPL.WhsDocumentProcurementPriceLink_PriceDate as datetime),104) as Name,
							isnull(WDPPL.WhsDocumentProcurementPriceLink_PriceRub,0) as price
						from
							v_WhsDocumentProcurementRequest WDPR with (nolock)
							left join v_WhsDocumentProcurementRequestSpec WDPRS with (nolock) on WDPRS.WhsDocumentProcurementRequest_id = WDPR.WhsDocumentProcurementRequest_id
							left join v_WhsDocumentProcurementPriceLink WDPPL with (nolock) on WDPPL.WhsDocumentProcurementRequestSpec_id = WDPRS.WhsDocumentProcurementRequestSpec_id
						where
							WDPR.WhsDocumentUc_id = :WhsDocumentUc_id
							and WDPRS.CalculatPriceType_id = 2
					";
					$result5 = $this->db->query($query, array('WhsDocumentUc_id'=>$data['lot_id']));
					if ( is_object($result5) ){
						$links = $result5->result('array');
					}
				}
				$resarr['links'] = $links;
			}

		}

		return $resarr;
	}

	/**
	 *	Сохранение данных для документации по лоту
	 */
	function saveUnitOfTradingDocsData($data) {
		$procedure = "p_WhsDocumentProcurementRequestSpecDop_" . ( empty($data['WhsDocumentProcurementRequestSpecDop_id']) ? 'ins' : 'upd' );
		$isOutput = empty($data['WhsDocumentProcurementRequestSpecDop_id']) ? "output" : "";
		
		$query = "
			declare
				@WhsDocumentProcurementRequestSpecDop_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentProcurementRequestSpecDop_id = :WhsDocumentProcurementRequestSpecDop_id;
			exec dbo." . $procedure . "
				@WhsDocumentProcurementRequestSpecDop_id = @WhsDocumentProcurementRequestSpecDop_id output,
				@WhsDocumentUc_id = :WhsDocumentUc_id,
				@Okved_id = :Okved_id,
				@Okpd_id = :Okpd_id,
				@WhsDocumentProcurementRequestSpecDop_CodeKOSGU = :WhsDocumentProcurementRequestSpecDop_CodeKOSGU,
				@WhsDocumentProcurementRequestSpecDop_Count = :WhsDocumentProcurementRequestSpecDop_Count,
				@SupplyPlaceType_id = :SupplyPlaceType_id,
				@ProvSizeType_id = :ProvSizeType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentProcurementRequestSpecDop_id as WhsDocumentProcurementRequestSpecDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Копирование данных для документации по лоту
	 */
	function copyUnitOfTradingDocsData($data) {
		if(!isset($data['WhsDocumentUc_id']))
			return false;
		$this->setObject('WhsDocumentProcurementRequestSpecDop')->setRow($data['WhsDocumentUc_id']);
		$record = $this->getRecordById($data['WhsDocumentUc_id']);
		if( !is_array($record) ) {
			return false;
		}
		$record['WhsDocumentUc_id'] = $data['WhsDocumentUc_id'];
		$record['WhsDocumentProcurementRequestSpecDop_id'] = '';
		return $this->saveUnitOfTradingDocsData(array_merge($record, array('pmUser_id' => $data['pmUser_id'])));
	}

	/**
	 * Получение целевой статьи
	 */
	function getBudgetFormType($data) {
		$where = "";
		if( !empty($data['DrugFinance_id']) ) {
			$where .= " and DrugFinance_id = :DrugFinance_id ";
		}
		if( !empty($data['WhsDocumentCostItemType_id']) ) {
			$where .= " and WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id ";
		}
		$query = "
			select top 1
				BudgetFormType_id
			from
				v_FinanceSource with (nolock)
			where
				(1=1)
				{$where}
		";
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}

	/**
	 * Установка суммы для лота
	 */
	function setSumForUnitOfTrading($data) {
		$this->setObject('WhsDocumentProcurementRequest')->setRow($data['WhsDocumentUc_id']);
		$record = $this->getRecordById($data['WhsDocumentUc_id']);
		if( !is_array($record) ) {
			return false;
		}
		
		$query = "
			select 
				sum(isnull(WhsDocumentProcurementRequestSpec_Kolvo,0) * isnull(WhsDocumentProcurementRequestSpec_PriceMax,0)) as sum
			from
				v_WhsDocumentProcurementRequestSpec with (nolock)
			where 
				WhsDocumentProcurementRequest_id = :WhsDocumentUc_id
		";
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		$reslt = $res->result('array');
		$record['WhsDocumentUc_Sum'] = $reslt[0]['sum'];

		return $this->saveUnitOfTrading(array_merge($record, array('pmUser_id' => $data['pmUser_id'])));
	}

	/**
	 * Проверка медикаментов и расчет количества единиц измерения товара в упаковке
	 */
	function calcDrugUnitQuant($data) {
		if( !isset($data['DrugComplexMnn_id']) || !isset($data['GoodsUnit_id'])) {
			return false;
		}
		$query = "
			select top 1
				case when isnull(drug.cnt,0) > 0 then isnull(drug.cnt,0) else 0 end as totalCnt
			from
				rls.v_DrugComplexMnn dcm with (nolock)
				left join rls.v_CLSDRUGFORMS cls with (nolock) on cls.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				outer apply ( 
					select top 1 
						-- p.DFMASS - Дозировка
						-- n.DRUGSINPPACK - кол-во лекарственных форм в первичной упаковке
						-- n.PPACKINUPACK - количество первичных упаковок во вторичной 
						-- n.UPACKINSPACK -  количество вторичных упаковок в третичной 
						(isnull(p.DFMASS,0) * isnull(n.DRUGSINPPACK,1) * isnull(n.PPACKINUPACK,1) * isnull(n.UPACKINSPACK,1)) as cnt
					from rls.v_Drug d with (nolock)
					left join rls.v_prep p with (nolock) on p.Prep_id = d.DrugPrep_id
					left join rls.v_Nomen n with (nolock) on n.NOMEN_ID = d.Drug_id
					where d.DrugComplexMnn_id = dcm.DrugComplexMnn_id 
						and p.DFMASS > 0 and p.DFMASSID > 0 and p.DFMASSID = :GoodsUnit_id
				) drug
			where 
				DrugComplexMnn_id = :DrugComplexMnn_id
				and (cls.NAME in ('капс.','табл.') or cls.NAME like '%табл%')
				-- and isnull(drug.cnt,0) > 0
		";
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		$reslt = $res->result('array');

		return $reslt;
	}

    /**
     * Загрузка комбобокса для выбора кода ОКПД
     */
    function loadOkpdCombo($filter) {
        $where = array();
        $params = array();

        if (!empty($filter['Okpd_id'])) {
            $where[] = "o.Okpd_id = :Okpd_id";
            $params['Okpd_id'] = $filter['Okpd_id'];
        } else {
            if (!empty($filter['query'])) {
                $where[] = "(o.Okpd_Code like :query or o.Okpd_Name like :query)";
                $params['query'] = $filter['query'].'%';
            } else {
                $where[] = "o.Okpd_Code like '24.4%'"; //Сводная заявка
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = 'where '.$where_clause;
        }

        $query = "
			select top 250
                o.Okpd_id,
                o.Okpd_Code,
                o.Okpd_Name
            from
                v_Okpd o with (nolock)
			$where_clause
			order by
			    o.Okpd_Code
		";

        $result = $this->db->query($query, $params);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка названия организации
     */
    function getOrgNick($data) {
    	$params = array();
    	$params['Org_id'] = $data['Org_id'];
        $query = "
			select top 1
                o.Org_Nick
            from
                Org o with (nolock)
			where o.Org_id = :Org_id
		";

        $result = $this->db->query($query, $params);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка единицы измерения товара
     */
    function getGoodsUnitByDrugComplexMnn($data) {
    	$params = array();
    	$params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
    	$where = '';
    	if(!empty($data['GoodsUnit_id'])){
    		$where = " and GoodsUnit_id = :GoodsUnit_id";
    		$params['GoodsUnit_id'] = $data['GoodsUnit_id'];
    	}
        $query = "
			select top 1
                GoodsUnit_id,
                GoodsPackCount_Count
            from
                v_GoodsPackCount with (nolock)
			where DrugComplexMnn_id = :DrugComplexMnn_id
			{$where}
		";

        $result = $this->db->query($query, $params);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
	 * Возвращает список организаций
	 */
	function loadOrgCombo($data) {
		$where_array = array();
		$queryParams = array();

		if (isset($data['UserOrg_id'])) {
			$queryParams['UserOrg_id'] = $data['UserOrg_id'];
		} else {
			$queryParams['UserOrg_id'] = null;
		}

		if (!empty($data['Org_id'])) {
			$where_array[] = "o.Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_id'];
		} else {
			$where_array[] = "
				(
					o.Org_id = @UserOrg_id or
					o.Org_id = @MinzdravOrg_id or
					o.Org_id in (
						select
							Org_id
						from
							v_MedService with (nolock)
						where
							MedServiceType_id = @MedServiceType_id and
							Org_id is not null
					)
				)
			";
			if (!empty($data['query'])) {
				$where_array[] = "o.Org_Name like :query";
				$queryParams['query'] = "%".$data['query']."%";
			}
		}

		$where= "";
		if (count($where_array) > 0) {
			$where = "where ".join(" and ", $where_array);
		}


		$query = "
			declare
				@MedServiceType_id bigint,
				@UserOrg_id bigint = :UserOrg_id,
				@MinzdravOrg_id bigint;
			
			set @MedServiceType_id = (select top 1 MedServiceType_id from v_MedServiceType with (nolock) where MedServiceType_SysNick = 'zakup' order by MedServiceType_id);
			set @MinzdravOrg_id = dbo.GetMinzdravDloOrgId();
			
			select top 500
				o.Org_id,
				o.Org_Name
			from
				v_Org o with (nolock)				
			{$where}
			order by
				Org_Name;				
		";

		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
}
?>