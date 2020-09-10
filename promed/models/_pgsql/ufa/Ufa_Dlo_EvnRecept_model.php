<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Ufa_Dlo_EvnRecept_model - модель, для работы с таблицей EvnRecept. Версия для Уфы
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

class Ufa_Dlo_EvnRecept_model extends Dlo_EvnRecept_model {
	/**
	 * Проверка уникальности рецепта по серии и номеру для выбранного ЛПУ за год
	 */
	function checkReceptSerNum($data) {
		$check_recept_ser_num = -1;
		$queryParams = array();

		$query = "
			select
				EvnRecept.EvnRecept_id as \"EvnRecept_id\"
			from v_EvnRecept_all EvnRecept
				left join Evn on Evn.Evn_id = EvnRecept.EvnRecept_id 
			where Evn.Lpu_id = :Lpu_id
				and Evn.EvnClass_id = 4
				and EvnRecept.EvnRecept_Ser = :EvnRecept_Ser
				and EvnRecept.EvnRecept_Num = cast(:EvnRecept_Num as varchar)
				and date_part('year', Evn.Evn_setDT) = :EvnRecept_setYear
				and Evn.Evn_setDT between :EvnRecept_setDateStart and :EvnRecept_setDateEnd
			limit 1
		";

		$EvnRecept_setDate = DateTime::createFromFormat('Y-m-d', $data['EvnRecept_setDate']);

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['EvnRecept_Ser'] = $data['EvnRecept_Ser'];
		$queryParams['EvnRecept_Num'] = $data['EvnRecept_Num'];
		$queryParams['EvnRecept_setYear'] = $EvnRecept_setDate->format('Y');
		$queryParams['EvnRecept_setDateStart'] = $EvnRecept_setDate->format('Y') . '-01-01';
		$queryParams['EvnRecept_setDateEnd'] = $EvnRecept_setDate->format('Y') . '-12-31 23:59:59';

		if ( $data['EvnRecept_id'] > 0 ) {
			$query .= " and EvnRecept.EvnRecept_id <> :EvnRecept_id";
			$queryParams['EvnRecept_id'] = $data['EvnRecept_id'];
		}

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( is_array($response) && count($response) > 0 && !empty($response[0]['EvnRecept_id']) ) {
				$check_recept_ser_num = 1;
			}
			else {
				$check_recept_ser_num = 0;
			}
		}

		return $check_recept_ser_num;
	}


	/**
	 * Возвращает номер для нового рецепта в Уфе (автонумерация)
	 */
	function getReceptNumber($data) {
		$query = "
			SELECT
				coalesce(MAX(cast(EvnRecept.EvnRecept_Num as bigint)), 0) + 1 as \"rnumber\"
			FROM v_EvnRecept_all EvnRecept
				left join Evn on Evn.Evn_id = EvnRecept.EvnRecept_id 
			WHERE Evn.Lpu_id = :Lpu_id
				and Evn.EvnClass_id = 4
				and EvnRecept.EvnRecept_Ser in (:EvnRecept_Ser, :MiEvnRecept_Ser)
				and cast(EvnRecept.EvnRecept_Num as bigint) between cast(:MinValue as bigint) and cast(:MaxValue as bigint)
				and date_part('year', Evn.Evn_setDT) = :EvnRecept_setYear
				and Evn.Evn_setDT between :EvnRecept_setDateStart and :EvnRecept_setDateEnd
		";

		$EvnRecept_setDate = DateTime::createFromFormat('Y-m-d', $data['EvnRecept_setDate']);

		$params = array(
			 'Lpu_id' => $data['Lpu_id']
			,'EvnRecept_setYear' => $EvnRecept_setDate->format('Y')
			,'EvnRecept_setDateStart' => $EvnRecept_setDate->format('Y') . '-01-01'
			,'EvnRecept_setDateEnd' => $EvnRecept_setDate->format('Y') . '-12-31 23:59:59'
			,'EvnRecept_Ser' => $data['EvnRecept_Ser']
			,'MiEvnRecept_Ser' => 'МИ'.$data['EvnRecept_Ser']	// #122894. Если форма рецепта 1-МИ, то серия рецепта, дополняется в начале строки строкой 'МИ'. 
			,'MinValue' => $data['MinValue']
			,'MaxValue' => $data['MaxValue']
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
	 * Method description
	 */
	function getMedPersonalCodeField() {
		return 'MedPersonal_TabCode';
	}
        
        
    /**
     * Возвращает список медикоментов
     */
	function loadDrugList($data) { 
		$filter = "(1 = 1)";
		$Select = "";
		$queryParams = array();
		
		if ( $data['ReceptType_Code'] == 1 )
		    //Если Тип рецепта  "на бланке", обрабатываем, как "на листе"
		    $data['ReceptType_Code'] = 2;
			
        if(isset($data['DopRequest']) && $data['DopRequest'] == 2){
           
            //$data['Lpu_id'] = 111;
            $query = "
                select distinct
                    D.Drug_id as \"Drug_id\",
                    D.Drug_Name as \"Drug_Name\"
                from v_Drug D
                left join v_DrugOstat_all DO on DO.Drug_id = D.Drug_id
                	and DO.Lpu_id = ".$data['Lpu_id']."
                where D.DrugMnn_id = :DrugMnn_id
                	and coalesce(DO.DrugOstat_Kolvo, 0) = 0
                order by D.Drug_Name
            ";
            $queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
            $result = $this->db->query($query, $queryParams);

            if ( is_object($result) ) {
                return $result->result('array');
            }
            else {
                return false;
            }
        }
                //$data['Lpu_id'] = 92;
		$queryParams['Lpu_id'] = $data['Lpu_id'];
        $mi_1_join = "";
        $mi_1_where = "";
        if(isset($data['is_mi_1']) && ($data['is_mi_1'] == 'true') && !isset($data['Drug_id'])){
			if ($data['session']['region']['nick'] == 'ufa') {
                //  Будем искать из изделий мед. назначегния
				$filter .= " and Drug.DrugMnn_id = 3497 ";
            }
			else {
				$mi_1_join = "
					inner join rls.DrugNomen DN on DN.DrugNomen_Code = cast(Drug.Drug_CodeG as varchar(20))
					left join rls.Drug RD on RD.Drug_id = DN.Drug_id
					left join rls.Prep P on P.Prep_id = RD.DrugPrep_id
					left join rls.CLSNTFR NTFR on NTFR.CLSNTFR_ID = P.NTFRID
				";
				$mi_1_where = " and NTFR.CLSNTFR_ID <> 1 and NTFR.PARENTID <> 1 and NTFR.CLSNTFR_ID <> 176 and NTFR.PARENTID <> 176 and NTFR.CLSNTFR_ID <> 137 and NTFR.CLSNTFR_ID <> 138 and NTFR.CLSNTFR_ID <> 139 and NTFR.CLSNTFR_ID <> 140 and NTFR.CLSNTFR_ID <> 141 and NTFR.CLSNTFR_ID <> 142 and NTFR.CLSNTFR_ID <> 144";	
			}
        }
		switch ( $data['mode'] ) {
			case 'all':
				if ( $data['Drug_id'] > 0 ) {
					// Загрузка на редактирование

					$query = "
						SELECT
							Drug.Drug_id as \"Drug_id\",
							null as \"DrugRequestRow_id\",
							Drug.DrugMnn_id as \"DrugMnn_id\",
							null as \"DrugFormGroup_id\",
							Drug.Drug_IsKek as \"Drug_IsKEK\",
							Drug.Drug_Name as \"Drug_Name\",
							null as \"Drug_DoseCount\",
							null as \"Drug_Dose\",
							null as \"Drug_Fas\",
							cast(DrugPrice.DrugState_Price as numeric(18, 2)) as \"Drug_Price\",
							coalesce(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",
							0 as \"DrugOstat_Flag\"
						FROM v_Drug Drug
							left join lateral(
								SELECT MAX(DP.DrugProto_id) AS DrugProto_MAXid
								FROM v_DrugPrice DP
									inner join v_ReceptFinance RF on RF.ReceptFinance_id = DP.ReceptFinance_id
										and RF.ReceptFinance_Code = :ReceptFinance_Code
								where DP.Drug_id = Drug.Drug_id
									and DP.DrugProto_begDate <= :Date
							) AS DrugProtoMAX on true
							left join v_DrugPrice DrugPrice on DrugPrice.Drug_id = Drug.Drug_id
								and DrugPrice.DrugProto_id = DrugProtoMAX.DrugProto_MAXid
							left join YesNo Drug_IsKEK on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek
							left join ReceptFinance on ReceptFinance.ReceptFinance_id = DrugPrice.ReceptFinance_id
						WHERE (1 = 1)
							and Drug.Drug_id = :Drug_id
						ORDER BY Drug_Name
						limit 1
					";

					$queryParams['Drug_id'] = $data['Drug_id'];
				}
				else {
					$farmacy_filter = "";

					$filter .= "and (Drug.Drug_begDate is null or Drug.Drug_begDate < dbo.tzgetdate()) and (Drug.Drug_endDate is null or Drug.Drug_endDate > dbo.tzgetdate())";
                                        
					if ( $data['EvnRecept_Is7Noz_Code'] == 1 ) {
						$drug_table = "v_Drug7Noz";
						$farmacy_filter = " and coalesce(OrgFarmacy.OrgFarmacy_IsNozLgot, 2) = 2";
					}
					else {
						switch ( $data['ReceptFinance_Code'] ) {
							case 1:
								if ( $data['ReceptType_Code'] == 2 ) {
                                    if ( ($data['session']['region']['nick'] != 'saratov') && ($data['session']['region']['nick'] != 'ufa'))
										$filter .= " and coalesce(Ostat.Farm_Ostat, 0) + coalesce(Ostat.RAS_Ostat, 0) > 0";
									$drug_table = "v_Drug";
								}
								else {
									$drug_table = "v_DrugFed";
								}
							break;

							case 2:
								if ( $data['ReceptType_Code'] == 2 ) {
                                    if ( ($data['session']['region']['nick'] != 'saratov') && ($data['session']['region']['nick'] != 'ufa'))
										$filter .= " and coalesce(Ostat.Farm_Ostat, 0) + coalesce(Ostat.RAS_Ostat, 0) > 0";
									$drug_table = "v_Drug";
								}
								else {
									if ($data['session']['region']['nick'] == 'ufa')
										$drug_table = "v_Drug";
									else
										$drug_table = "v_DrugReg";
								}
							break;

							default:
								return false;
							break;
						}
					}
					
					if(!isset($data['DrugMnn_id']) && (!isset($data['query']) || strlen($data['query']) < 2)){
						return false;
					}
					if ( isset($data['DrugMnn_id']) ) {
						// Выбрана запись из комбо "МНН"
                        $filter .= " and Drug.DrugMnn_id = :DrugMnn_id";
						$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
                                          // var_dump ($queryParams['DrugMnn_id']);
 
					}
					if ( isset($data['query']) && strlen($data['query']) >= 2 ) {
						//$filter .= " and Drug.Drug_Name ilike :Drug_Name";
                        $filter .= " and Drug.Drug_Name ilike :Drug_Name";
						$queryParams['Drug_Name'] = "%" . $data['query'] . "%";
					}
					
					if ( isset($data['PrivilegeType_id']) ) {
						// исключаем препараты которые не для этой категории (PrivilegeType_id в соотв. таблце не задан или равен выбранной категории)
						$filter .= "
							and coalesce(PD.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id
							and coalesce(PD2.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id
						";
						$Select = '
							Select
								coalesce(WhsDocumentCostItemType_id,
									case when DrugFinance_id = 3
										then 1
									when DrugFinance_id = 27
										then 2
								end) as WhsDocumentCostItemType_id, 
								DrugFinance_id,
								:ReceptFinance_Code as ReceptFinance_Code
							from PrivilegeType where PrivilegeType_id = :PrivilegeType_id;
						';
						$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];
						$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
					} else {
						$Select = '
							Select
								null as WhsDocumentCostItemType_id, 
								null as DrugFinance_id
						';
					}

					$ufa_farm_join = "";
					if($data['session']['region']['nick'] == 'ufa')
					{
						$queryParams['Lpu_id'] = $data['Lpu_id'];
						$ufa_farm_join = "
							inner join v_OrgFarmacyIndex OrgFarmacyIndex on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
								and  OrgFarmacyIndex.Lpu_id = :Lpu_id
						";
					}
					$query = "
						with tmp as (
				{$Select}
			), mv as (
				-- https://jira.is-mis.ru/browse/PROMEDWEB-16392
				Select 
					case 
						when ReceptFinance_Code = 2 and WhsDocumentCostItemType_id = 1
							then 2
						else WhsDocumentCostItemType_id
					end as WhsDocumentCostItemType_id,
					case	
						when ReceptFinance_Code = 2 and WhsDocumentCostItemType_id = 1
							then 27
						else DrugFinance_id
					end as DrugFinance_id
				 from tmp
						), er as (
							Select er.Drug_id, OrgFarmacy_id, WhsDocumentCostItemType_id, sum(EvnRecept_Kolvo) as Reserve_Kolvo
							from v_EvnRecept er	
							where er.EvnRecept_setDate >= (dbo.tzgetdate() - interaval '10 days')
								and ReceptDelayType_id is null
								and er.lpu_id = :Lpu_id
							Group by er.Drug_id, OrgFarmacy_id, WhsDocumentCostItemType_id
						),
						ListWhs as (
							SElect WhsDocumentCostItemType_id from PrivilegeType 
							where WhsDocumentCostItemType_id is not null
								and (select WhsDocumentCostItemType_id from mv) is null
						),
						res as (
							select DISTINCT
								Drug.Drug_id as \"Drug_id\",
								null as \"DrugRequestRow_id\",
								Drug.DrugMnn_id as \"DrugMnn_id\",
								null as \"DrugFormGroup_id\",
								Drug.Drug_IsKek as \"Drug_IsKEK\",
								Drug.Drug_Name as \"Drug_Name\",
								null as \"Drug_DoseCount\",
								null as \"Drug_Dose\",
								null as \"Drug_Fas\",
								cast(DrugPrice.DrugState_Price as numeric(18, 2)) as \"Drug_Price\",
								coalesce(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",
								case when coalesce(Ostat.Farm_Ostat, 0) <= 0
									then case when coalesce(Ostat.RAS_Ostat, 0) <= 0
										then 2
										else 1
									end
									else 0
								end as \"DrugOstat_Flag\"
							from " . $drug_table . " Drug
								left join v_DrugState DS ON DS.Drug_id = Drug.Drug_id
								left join PrivilegeDrug PD on Drug.Drug_id = PD.Drug_id
								left join PrivilegeDrug PD2 on PD.Drug_id IS NULL
									AND DS.DrugProtoMnn_id = PD2.DrugProtoMnn_id
								left join lateral ( Select 1 as idx from v_OrgFarmacyIndex i  where i.WhsDocumentCostItemType_id = (select WhsDocumentCostItemType_id from mv) limit 1) cntrl
								left join lateral(
									SELECT MAX(DP.DrugProto_id) AS DrugProto_MAXid
									FROM v_DrugPrice DP
										inner join v_ReceptFinance RF on RF.ReceptFinance_id = DP.ReceptFinance_id
											and RF.ReceptFinance_Code = :ReceptFinance_Code
									where DP.Drug_id = Drug.Drug_id
								) AS DrugProtoMAX on true
								left join v_DrugPrice DrugPrice on DrugPrice.Drug_id = Drug.Drug_id
									and DrugPrice.DrugProto_id = DrugProtoMAX.DrugProto_MAXid
								left join YesNo Drug_IsKEK on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek
								left join lateral(
									select
										case when coalesce(sum(Farm_Ostat), 0) >= coalesce(sum(Reserve_Kolvo), 0)
											then coalesce(sum(Farm_Ostat), 0) - coalesce(sum(Reserve_Kolvo), 0)
											else 0
										end Farm_Ostat,
										case when coalesce(sum(RAS_Ostat), 0) >= coalesce(sum(Reserve_Kolvo), 0)
											then coalesce(sum(RAS_Ostat), 0) - coalesce(sum(Reserve_Kolvo), 0)
											else 0
										end RAS_Ostat
									from (
										select
											doa.Drug_did,
											doa.WhsDocumentCostItemType_id,
											farm.OrgFarmacy_id,
											SUM(case when DOA.DrugOstatRegistry_id != 1 and coalesce(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
												then DOA.DrugOstatRegistry_Kolvo
											end) as Farm_Ostat,
											SUM(case when 1 = 1 then DOA.DrugOstatRegistry_Kolvo end) as RAS_Ostat
										from v_DrugOstatRegistry DOA
											left join v_OrgFarmacy OrgFarmacy on OrgFarmacy.Org_id = DOA.Org_id--DOA.OrgFarmacy_id
											inner join lateral (
												Select distinct
													OrgFarmacy.OrgFarmacy_id,
													Storage_id
												from OrgFarmacyIndex OrgFarmacyIndex
												where OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
													and ( OrgFarmacyIndex.Lpu_id is null or OrgFarmacyIndex.Lpu_id = :Lpu_id)
													and coalesce(OrgFarmacyIndex_deleted, 1) = 1
													and COALESCE(OrgFarmacyIndex.WhsDocumentCostItemType_id, 0) = 
													--Если есть ходь одно прикрепление по статье расхода - берем (select WhsDocumentCostItemType_id from mv)
													case when COALESCE(cntrl.idx, 0) = 1 then  (select WhsDocumentCostItemType_id from mv) else 0 end
											) farm on true
										where drug.Drug_id = DOA.Drug_did
											and (DOA.Storage_id is null or DOA.Storage_id = coalesce(farm.Storage_id, 0))
											and doa.DrugFinance_id = (select DrugFinance_id from mv)
											and doa.WhsDocumentCostItemType_id = coalesce((select WhsDocumentCostItemType_id from mv), doa.WhsDocumentCostItemType_id)
											and not exists (Select 1 from ListWhs where doa.WhsDocumentCostItemType_id = ListWhs.WhsDocumentCostItemType_id)
										group by  doa.Drug_did, doa.WhsDocumentCostItemType_id, farm.OrgFarmacy_id, doa.DrugOstatRegistry_id
										) t
										left join er on er.Drug_id = t.Drug_did and er.WhsDocumentCostItemType_id = t.WhsDocumentCostItemType_id
								and er.OrgFarmacy_id = t.OrgFarmacy_id
								) Ostat on true
								".$mi_1_join."
							where " . $filter . $mi_1_where . "
						)
						select *
						from res
						order by
							\"DrugOstat_Flag\", 
							\"Drug.Drug_Name\" 
					";
				}

				log_message('debug', 'loadDrugList: $query ='.$query);

				$queryParams['Date'] = $data['Date'];
				$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code']; 
				$queryParams['Lpu_id'] = $data['Lpu_id'];
			break;

			case 'request':

				if ( $data['ReceptType_Code'] == 2 ) {
					// На листе
					// Только медикаменты из заявки, имеющиеся на остатках
					if ( ($data['session']['region']['nick'] != 'saratov') && ($data['session']['region']['nick'] != 'ufa')) {
						$filter .= " and coalesce(Ostat.Farm_Ostat, 0) + coalesce(Ostat.RAS_Ostat, 0) > 0";
					}
				}

				switch ( $data['DrugRequestRow_IsReserve'] ) {
					case 1:
						// Не из резерва
						$filter .= " and DRR.Person_id = :Person_id";
						// $filter .= " and ((DR.MedPersonal_id = :MedPersonal_id and DR.Lpu_id = :Lpu_id) or (DR.Lpu_id in (select id from MinZdravList())) or (DR.Lpu_id in (select id from OnkoList())))";
						$filter .= " and (DR.MedPersonal_id = :MedPersonal_id or (DR.Lpu_id in (select id from MinZdravList())) or (DR.Lpu_id in (select id from OnkoList())))";
					break;

					case 2:
						// Из резерва
						$filter .= " and DRR.Person_id is null";
						$filter .= " and DR.MedPersonal_id = :MedPersonal_id";
						// $filter .= " and DR.Lpu_id = :Lpu_id";
					break;

					default:
						return false;
					break;
				}

				switch ( $data['ReceptFinance_Code'] ) {
					case 1:
						$data['ReceptFinance_id'] = 1;
						// $drug_table = "v_DrugFed";
						$filter .= " and DRR.DrugRequestType_id = 1";
					break;

					case 2:
						$data['ReceptFinance_id'] = 2;
						// $drug_table = "v_DrugReg";
						$filter .= " and DRR.DrugRequestType_id = 2";
					break;

					default:
						return false;
					break;
				}

				if ( isset($data['RequestDrug_id']) ) {
					if ( isset($data['PrivilegeType_id']) ) {
						// исключаем препараты которые не для этой категории (PrivilegeType_id в соотв. таблце не задан или равен выбранной категории)
						$filter .= "
							and coalesce(PD.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id
							and coalesce(PD2.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id
						";
						$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];					
					}
					// Выбрана запись из комбо "Заявка" с заявкой по Drug_id
					$filter .= " and Drug.Drug_id = :RequestDrug_id";
					$filter .= " and (Drug.Drug_begDate is null or Drug.Drug_begDate < cast(:Date as timestamp))";
					$filter .= " and (Drug.Drug_endDate is null or Drug.Drug_endDate > cast(:Date as timestamp))";

					$query = "
						select DISTINCT
							Drug.Drug_id as \"Drug_id\",
							DRR.DrugRequestRow_id as \"DrugRequestRow_id\",
							Drug.DrugMnn_id as \"DrugMnn_id\",
							DF.DrugFormGroup_id as \"DrugFormGroup_id\",
							Drug.Drug_IsKek as Drug_IsKEK as \"Drug_IsKek as Drug_IsKEK\",
							Drug.Drug_Name as \"Drug_Name\",
							Drug.Drug_DoseCount as \"Drug_DoseCount\",
							coalesce(Drug.Drug_DoseQ, '') || coalesce(Drug.Drug_DoseEi, '') as \"Drug_Dose\",
							Drug.Drug_Fas as \"Drug_Fas\",
							cast(DrugPrice.DrugState_Price as numeric(18, 2)) as \"Drug_Price\",
							coalesce(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",
							case when coalesce(Ostat.Farm_Ostat, 0) <= 0
								then case when coalesce(Ostat.RAS_Ostat, 0) <= 0
									then 2
									else 1
								end
								else 0
							end as \"DrugOstat_Flag\"
						from v_DrugRequestRow DRR
							inner join v_DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id
							inner join v_DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
								and CAST(:Date as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
							inner join v_Drug Drug on Drug.Drug_id = DRR.Drug_id
							left join PrivilegeDrug PD on PD.Drug_id = DRR.Drug_id
							left join PrivilegeDrug PD2 on PD.Drug_id IS NULL
								AND DRR.DrugProtoMnn_id = PD2.DrugProtoMnn_id
							left join lateral(
								SELECT
									MAX(DP.DrugProto_id) AS DrugProto_MAXid
								FROM v_DrugPrice DP
									inner join v_ReceptFinance RF on RF.ReceptFinance_id = DP.ReceptFinance_id
										and RF.ReceptFinance_Code = :ReceptFinance_Code
								where DP.Drug_id = Drug.Drug_id
									and DP.DrugProto_begDate <= CAST(:Date as timestamp)
							) AS DrugProtoMAX on true
							left join v_DrugPrice DrugPrice on DrugPrice.Drug_id = Drug.Drug_id
								and DrugPrice.DrugProto_id = DrugProtoMAX.DrugProto_MAXid
							inner join DrugForm DF on DF.DrugForm_id = Drug.DrugForm_id
							left join YesNo Drug_IsKEK on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek
							left join lateral(
								select
									SUM(case when DOA.OrgFarmacy_id != 1 then DOA.DrugOstat_Kolvo end) as Farm_Ostat,
									SUM(case when DOA.OrgFarmacy_id = 1 then DOA.DrugOstat_Kolvo end) as RAS_Ostat
								from v_DrugOstat DOA
									inner join v_ReceptFinance RF on RF.ReceptFinance_id = DOA.ReceptFinance_id
										and RF.ReceptFinance_Code = :ReceptFinance_Code
								where DOA.Drug_id = Drug.Drug_id
									and doa.SubAccountType_id in (1, 4)
							) Ostat on true
							".$mi_1_join."
						where " . $filter . $mi_1_where .  "
						order by
							Drug.Drug_Name
					";

					$queryParams['RequestDrug_id'] = $data['RequestDrug_id'];
				}
				else { 
					if ( isset($data['DrugMnn_id']) ) {
						// Выбрана запись из комбо "Заявка"
						$filter .= " and DPM.DrugMnn_id = :DrugMnn_id";
						$filter .= " and coalesce(Drug.Drug_DoseCount, 0) = coalesce(:Drug_DoseCount, 0)";
						// $filter .= " and coalesce(DrugO.Drug_Dose, '') = coalesce(:Drug_Dose, '')";
						// $filter .= " and coalesce(DrugO.Drug_Fas, 0) = coalesce(:Drug_Fas, 0)";

						$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
						$queryParams['Drug_DoseCount'] = $data['Drug_DoseCount'];
						// $queryParams['Drug_DoseQ'] = $data['Drug_DoseQ'];
						// $queryParams['Drug_Fas'] = $data['Drug_Fas'];

						if ( isset($data['DrugFormGroup_id']) ) {
							$filter .= " and coalesce(DF.DrugFormGroup_id, 0) = coalesce(:DrugFormGroup_id, 0)";
							$queryParams['DrugFormGroup_id'] = $data['DrugFormGroup_id'];
						}
					}
					else if ( isset($data['query']) && strlen($data['query']) >= 2 ) {
						$filter .= " and Drug.Drug_Name ilike :Drug_Name";
						$queryParams['Drug_Name'] = $data['query'] . "%";
					}
					else {
						return false;
					}

					$filter .= " and (Drug.Drug_begDate is null or Drug.Drug_begDate < cast(:Date as timestamp))";
					$filter .= " and (Drug.Drug_endDate is null or Drug.Drug_endDate > cast(:Date as timestamp))";

					if ( isset($data['PrivilegeType_id']) ) {
						// исключаем препараты которые не для этой категории (PrivilegeType_id в соотв. таблце не задан или равен выбранной категории)
						$filter .= "
							and coalesce(PD.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id
							and coalesce(PD2.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id
						";
						$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];					
					}
						
					$query = "
						select DISTINCT
							Drug.Drug_id as \"Drug_id\",
							DRR.DrugRequestRow_id as \"DrugRequestRow_id\",
							DPM.DrugMnn_id as \"DrugMnn_id\",
							DPM.DrugFormGroup_id as \"DrugFormGroup_id\",
							Drug.Drug_IsKek as \"Drug_IsKEK\",
							Drug.Drug_Name as \"Drug_Name\",
							DPM.Drug_DoseCount as \"Drug_DoseCount\",
							DPM.Drug_Dose as \"Drug_Dose\",
							DPM.Drug_Fas as \"Drug_Fas\",
							cast(DS.DrugState_Price as numeric(18, 2)) as \"Drug_Price\",
							coalesce(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",
							case when coalesce(Ostat.Farm_Ostat, 0) <= 0
								then
									case when coalesce(Ostat.RAS_Ostat, 0) <= 0
										then 2
										else 1
									end
								else 0
							end as \"DrugOstat_Flag\"
						from v_DrugRequestRow DRR
							inner join v_DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id
							inner join v_DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
								and CAST(:Date as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
							inner join v_DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
							inner join v_DrugState DS on DS.DrugProtoMnn_id = DPM.DrugProtoMnn_id
							inner join v_DrugProto DP on DP.DrugProto_id = DS.DrugProto_id
								and DP.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
							inner join v_Drug Drug on Drug.Drug_id = DS.Drug_id
							inner join DrugForm DF on DF.DrugForm_id = Drug.DrugForm_id
								and coalesce(DF.DrugFormGroup_id, 0) = coalesce(DPM.DrugFormGroup_id, 0)
							left join PrivilegeDrug PD on Drug.Drug_id = PD.Drug_id
							left join PrivilegeDrug PD2 on PD.Drug_id IS NULL
								AND DPM.DrugProtoMnn_id = PD2.DrugProtoMnn_id
							left join YesNo Drug_IsKEK on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek
							left join lateral (
								select
									SUM(case when DOA.OrgFarmacy_id != 1 then DOA.DrugOstat_Kolvo end) as Farm_Ostat,
									SUM(case when DOA.OrgFarmacy_id = 1 then DOA.DrugOstat_Kolvo end) as RAS_Ostat
								from v_DrugOstat DOA
									inner join ReceptFinance RF on RF.ReceptFinance_id = DOA.ReceptFinance_id
										and RF.ReceptFinance_Code = :ReceptFinance_Code
								where DOA.Drug_id = Drug.Drug_id
								  and doa.SubAccountType_id in (1, 4)
							) Ostat on true
							".$mi_1_join."
						where " . $filter . $mi_1_where . "
						order by
							Drug.Drug_Name
					";
					//		inner join " . $drug_table . " DrugTmp on DrugTmp.DrugMnn_id = DrugMnn.DrugMnn_id
				}

				$queryParams['Date'] = $data['Date'];
				$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
				$queryParams['Person_id'] = $data['Person_id'];
				$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
			break;

			default:
				return false;
			break;
		}

		if ( strlen($query) == 0 ) {
			return false;
		}

                //echo getDebugSQL($query, $queryParams); exit();
              
                //$dbrep = $this->load->database('bdwork', true);
 
                $dbrep = $this->db;
                
                //echo '<pre>' . print_r($this->db, 1) . '</pre>';
                
		$result = $dbrep->query($query, $queryParams);
                

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

        	/**
     *  Получение списка остатков по медикаменту в аптеках
     */
	function loadOrgFarmacyList($data) {
		$queryParams = array();
        $Select = "";

		if ( isset($data['OrgFarmacy_id']) ) {
        
			$query = "
				select
					0 as \"sort\",
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					RTRIM(Org.Org_Name) as \"OrgFarmacy_Name\",
					RTRIM(coalesce(OrgFarmacy.OrgFarmacy_HowGo, '')) as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					case when coalesce(DrugOstat.DrugOstat_Kolvo, 0) <= 0
						then 0
						else coalesce(DrugOstat.DrugOstat_Kolvo, 0)
					end as \"DrugOstat_Kolvo\",
					WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
				from v_OrgFarmacy OrgFarmacy
					inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
					inner join YesNo on YesNo.YesNo_id = coalesce(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					left join lateral (
						select
							WhsDocumentCostItemType_id,
							SUM(DOA.DrugOstatRegistry_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstatRegistry DOA
							inner join v_DrugFinance ReceptFinance on DOA.DrugFinance_id = ReceptFinance.DrugFinance_id
						where DOA.Org_id = OrgFarmacy.Org_id
							and DOA.Drug_did = :Drug_id
							and DOA.SubAccountType_id in (1, 4)
						group by WhsDocumentCostItemType_id
					) DrugOstat on true
				where (1 = 1)
					and OrgFarmacy.OrgFarmacy_id = :OrgFarmacy_id
					and coalesce(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
			";

			$queryParams['Drug_id'] = $data['Drug_id'];
			$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		}
		else if ( $data['EvnRecept_IsExtemp'] == 2 ) {
			// Экстемпоральный рецепт. Загрузка списка включенных аптек
			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					RTRIM(Org.Org_Name) as \"OrgFarmacy_Name\",
					RTRIM(coalesce(OrgFarmacy.OrgFarmacy_HowGo, '')) as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					0 as \"DrugOstat_Kolvo\"
				from v_OrgFarmacy OrgFarmacy
					inner join v_OrgFarmacyIndex OrgFarmacyIndex on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
						and OrgFarmacyIndex.Lpu_id = :Lpu_id
					inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
					inner join YesNo on YesNo.YesNo_id = coalesce(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
				where coalesce(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
					and OrgFarmacy.OrgFarmacy_id not in (1, 2)
				order by \"OrgFarmacy_Name\"
			";

			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		else if ( $data['EvnRecept_Is7Noz_Code'] == 1 ) {
			// 7 нозологий
			$query = "
				select
					0 as \"sort\",
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					RTRIM(Org.Org_Name) as \"OrgFarmacy_Name\",
					RTRIM(coalesce(OrgFarmacy.OrgFarmacy_HowGo, '')) as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					case when coalesce(DrugOstat.DrugOstat_Kolvo, 0) <= 0 then 0 else coalesce(DrugOstat.DrugOstat_Kolvo, 0) end as \"DrugOstat_Kolvo\",
					WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
				from v_OrgFarmacy OrgFarmacy
					inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
					inner join YesNo on YesNo.YesNo_id = coalesce(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					left join lateral (
						select
							WhsDocumentCostItemType_id,
							SUM(DOA.DrugOstatRegistry_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstatRegistry DOA
							inner join v_Contragent c  on c.Contragent_id = DOA.Contragent_id
						where c.Org_id = OrgFarmacy.Org_id
							and DOA.Drug_did = :Drug_id
							and WhsDocumentCostItemType_id = 3
							and DOA.SubAccountType_id in (1, 4)
						group by WhsDocumentCostItemType_id
					) DrugOstat on true
				where (1 = 1)
					and coalesce(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
					and coalesce(OrgFarmacy.OrgFarmacy_IsNozLgot, 2) = 2
					and OrgFarmacy.OrgFarmacy_id not in (1, 2)
			";

			$queryParams['Drug_id'] = $data['Drug_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		}

		else { 
			$query = "
				select
					rank() OVER (ORDER BY DrugOstat_Kolvo desc, OrgFarmacy_Name ) as \"Sort\",
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					RTRIM(Org.Org_Name) as \"OrgFarmacy_Name\",
					coalesce(RTRIM(OrgFarmacy.OrgFarmacy_HowGo),'Адрес аптеки не указан') as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					case when coalesce(DrugOstat.DrugOstat_Kolvo, 0) <= 0
							  then 0
						when OrgFarmacy.OrgFarmacy_id <> coalesce(DrugOstat.OrgFarmacy_id, 0)
							  then 0
						else coalesce(DrugOstat.DrugOstat_Kolvo, 0)
						end as \"DrugOstat_Kolvo\",
					WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
				from v_OrgFarmacy OrgFarmacy
					inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
					inner join YesNo on YesNo.YesNo_id = coalesce(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					--  Проверяем, есть ли прикрепление по статье расхода
					left join lateral ( Select 1 as idx from v_OrgFarmacyIndex i where i.WhsDocumentCostItemType_id = (select WhsDocumentCostItemType_id from mv)limit 1) cntrl
					left join lateral (
						select
							OrgFarmacy_id,
							WhsDocumentCostItemType_id,
							SUM(DOA.DrugOstatRegistry_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstatRegistry  DOA
							inner join dr on dr.Drug_id = DOA.Drug_did
							inner join v_OrgFarmacy OrgFarm on OrgFarm.Org_id = DOA.Org_id--DOA.OrgFarmacy_id
							innner join lateral (
								Select distinct
									storage_id
								from  OrgFarmacyIndex OrgFarmacyIndex
								where OrgFarmacyIndex.OrgFarmacy_id = OrgFarm.OrgFarmacy_id
									and COALESCE(OrgFarmacyIndex.WhsDocumentCostItemType_id, 0) = 
										--Если есть ходь одно прикрепление по статье расхода - берем (select WhsDocumentCostItemType_id from mv)
										  case when COALESCE(cntrl.idx, 0) = 1 then  (select WhsDocumentCostItemType_id from mv) else 0 end and 
									and (OrgFarmacyIndex.Lpu_id is null or OrgFarmacyIndex.Lpu_id = :Lpu_id)
									and coalesce(OrgFarmacyIndex_deleted, 1) = 1
							) farm on true
						where DOA.Drug_did = :Drug_id
							and (coalesce(DOA.storage_id, 0) = coalesce(farm.storage_id, 0)  or coalesce(DOA.storage_id, 0) = 0)
							and doa.DrugFinance_id = (select DrugFinance_id from mv)
							and doa.WhsDocumentCostItemType_id = coalesce((select WhsDocumentCostItemType_id from mv), doa.WhsDocumentCostItemType_id)
							and not exists (Select 1 from ListWhs where doa.WhsDocumentCostItemType_id = ListWhs.WhsDocumentCostItemType_id)
							and DOA.SubAccountType_id in (1, 4)
							and coalesce(DOA.DrugOstatRegistry_Kolvo, 0) > 0
							and OrgFarm.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
						group by OrgFarmacy_id, WhsDocumentCostItemType_id
					) DrugOstat on true
				where exists (
						Select 1
						from v_OrgFarmacyIndex i
						where i.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id 
							and ( i.Lpu_id is null or i.Lpu_id = :Lpu_id)
							and COALESCE(i.WhsDocumentCostItemType_id, 0) = 
								case when COALESCE(cntrl.idx, 0) = 1 then  (select WhsDocumentCostItemType_id from mv) else 0 end
					)      
                	AND coalesce(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
					" . (($data['session']['region']['nick'] != 'saratov' && $data['session']['region']['nick'] != 'ufa') ? "and coalesce(DrugOstat.DrugOstat_Kolvo, 0) > 0" : "") . "
					and OrgFarmacy.OrgFarmacy_id not in (1, 2)
			";

			$queryParams['Drug_id'] = $data['Drug_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		};
		if ( isset($data['Lpu_id']) ) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		};
		if ( isset($data['PrivilegeType_id']) ) {
			$Select = '
				Select
					coalesce(WhsDocumentCostItemType_id, case when DrugFinance_id = 3
							then 1
						when DrugFinance_id = 27
							then 2
						end) as WhsDocumentCostItemType_id, 
					DrugFinance_id,
					:ReceptFinance_Code as ReceptFinance_Code
				from PrivilegeType
				where PrivilegeType_id = :PrivilegeType_id
			';
		    $queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		} else {
			$Select = '
				Select
					null as WhsDocumentCostItemType_id, 
					null as DrugFinance_id
			';
		}

		$query = "
			with tmp as (
				{$Select}
			), mv as (
				-- https://jira.is-mis.ru/browse/PROMEDWEB-16392
				Select 
					case 
						when ReceptFinance_Code = 2 and WhsDocumentCostItemType_id = 1
							then 2
						else WhsDocumentCostItemType_id
					end as WhsDocumentCostItemType_id,
					case	
						when ReceptFinance_Code = 2 and WhsDocumentCostItemType_id = 1
							then 27
						else DrugFinance_id
					end as DrugFinance_id
				 from tmp
			), ListWhs as (
				select WhsDocumentCostItemType_id
				from PrivilegeType 
				where WhsDocumentCostItemType_id is not null
					and (select WhsDocumentCostItemType_id from mv) is null
			), dr as (
				Select dr.Drug_id, dr.Drug_Name from v_Drug Drug
					inner join v_Drug dr on dr.Drug_Name = Drug.Drug_Name
				where Drug.Drug_id = :Drug_id
			), t as (
				{$query}
			), er as (
				Select
					OrgFarmacy_id,
					WhsDocumentCostItemType_id,
					sum(EvnRecept_Kolvo) as Reserve_Kolvo
				from v_EvnRecept er
				inner join dr on dr.Drug_id = er.Drug_id
				where  er.EvnRecept_setDate >= (dbo.tzgetdate() - interval '10 days')
					and ReceptDelayType_id is null
					and er.lpu_id = :Lpu_id
					--and er.Drug_id = :Drug_id
				Group by OrgFarmacy_id,
					WhsDocumentCostItemType_id
			)

			select
				t.Sort as \"Sort\",
				t.OrgFarmacy_id as \"OrgFarmacy_id\",
				t.OrgFarmacy_Name as \"OrgFarmacy_Name\",
				t.OrgFarmacy_HowGo as \"OrgFarmacy_HowGo\",
				t.OrgFarmacy_IsFarmacy as \"OrgFarmacy_IsFarmacy\",
				t.DrugOstat_Kolvo Farm_Ostat as \"DrugOstat_Kolvo Farm_Ostat\",
				Reserve_Kolvo as \"Reserve_Kolvo\",
				case	
					when coalesce(DrugOstat_Kolvo, 0) < coalesce(Reserve_Kolvo, 0)
						then 0
					else 
						coalesce(DrugOstat_Kolvo, 0) - coalesce(Reserve_Kolvo, 0)
				end as \"DrugOstat_Kolvo\"
			from t
				left join er on er.OrgFarmacy_id = t.OrgFarmacy_id
					and er.WhsDocumentCostItemType_id = t.WhsDocumentCostItemType_id
			order by Sort, DrugOstat_Kolvo desc, OrgFarmacy_Name  
		";
				    
		$dbrep = $this->db;
                
		$res = $dbrep->query($query , $queryParams);

                // echo '<pre>' . print_r($res, 1) . '</pre>';
                         
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
        
        
    /**
     * Возвращает данные для формы редактирования рецепта
     */
	function loadEvnReceptEditForm($data) {
		$queryParams = array();

		$lpu_filter = null;

		$query = "
			SELECT
				er.EvnRecept_id as \"EvnRecept_id\",
				er.EvnRecept_pid as \"EvnRecept_pid\",
				er.Lpu_id as \"Lpu_id\",
				er.ReceptType_id as \"ReceptType_id\",
				ERec.ReceptForm_id as \"ReceptForm_id\",
				er.PrivilegeType_id as \"PrivilegeType_id\",
				to_char(er.EvnRecept_setDT, 'dd.mm.yyyy') as \"EvnRecept_setDate\",
				er.Diag_id as \"Diag_id\",
				er.ReceptFinance_id as \"ReceptFinance_id\",
				er.DrugFinance_id as \"DrugFinance_id\",
				er.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				er.ReceptDiscount_id as \"ReceptDiscount_id\",
				RTRIM(er.EvnRecept_Ser) as \"EvnRecept_Ser\",
				RTRIM(er.EvnRecept_Num) as \"EvnRecept_Num\",
				er.EvnRecept_IsMnn as \"Drug_IsMnn\",
				er.EvnRecept_IsMnn as \"EvnRecept_IsMnn\",
				er.Drug_rlsid as \"Drug_rlsid\",
				coalesce(er.EvnRecept_IsKEK,1) as \"Drug_IsKEK\",
				er.Drug_id as \"Drug_id\",
				case when DRR.DrugRequestRow_id is null
					then dg.DrugMnn_id
					else null
				end as \"DrugMnn_id\",
				RTRIM(er.EvnRecept_Signa) as \"EvnRecept_Signa\",
				er.LpuSection_id as \"LpuSection_id\",
				er.MedPersonal_id as \"MedPersonal_id\",
				er.OrgFarmacy_id as \"OrgFarmacy_id\",
				er.ReceptValid_id as \"ReceptValid_id\",
				DR.Lpu_id as \"Lpu_rid\",
				MP.MedPersonal_id as \"MedPersonal_rid\",
				DRR.DrugRequestRow_id as \"DrugRequestMnn_id\",
				round(er.EvnRecept_Kolvo, 2) as \"EvnRecept_Kolvo\",
				coalesce(er.EvnRecept_IsExtemp, 1) as \"EvnRecept_IsExtemp\",
				RTRIM(er.EvnRecept_ExtempContents) as \"EvnRecept_ExtempContents\",
				coalesce(er.EvnRecept_Is7Noz, 1) as \"EvnRecept_Is7Noz\",
				er.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				er.EvnRecept_IsSigned as \"EvnRecept_IsSigned\",
                ERec.ReceptDelayType_id as \"ReceptWrongDelayType_id\",
				to_char(wr.ReceptWrong_insDT, 'dd.mm.yyyy') as \"ReceptWrong_DT\",
				wr.ReceptWrong_Decr as \"ReceptWrong_Decr\",
				er.Person_id as \"Person_id\",
				coalesce(RDT.ReceptDelayType_Code, -1) as \"Recept_Result_Code\",
				'' as \"Recept_Result\",
				'' as \"Recept_Delay_Info\",
				'' as \"EvnRecept_Drugs\",
				DATEDIFF('day', ERec.EvnRecept_obrDT,dbo.tzGetDate()) as \"ReceptDelay_1_days\",
				RO.ReceptOtov_insDT as \"ReceptOtov_insDT\",
				to_char(RO.ReceptOtov_insDT, 'dd.mm.yyyy') as \"ReceptOtov_insDT\",
				to_char(RO.EvnRecept_obrDate, 'dd.mm.yyyy') as \"ReceptOtov_obrDate\",
				to_char(RO.EvnRecept_otpDate, 'dd.mm.yyyy') as \"ReceptOtov_otpDate\",
				coalesce(OrgF.Org_Name,'') as \"ReceptOtov_Farmacy\",
				coalesce(er.EvnRecept_deleted,1) as \"EvnRecept_deleted\",
				CAST(coalesce(er.EvnRecept_oPrice,ERec.EvnRecept_Price) as decimal(12,2)) as \"Drug_Price\",
				er.PrescrSpecCause_id as \"PrescrSpecCause_id\",
				er.ReceptUrgency_id as \"ReceptUrgency_id\",
				er.EvnRecept_IsExcessDose as \"EvnRecept_IsExcessDose\",
				er.EvnRecept_VKProtocolNum as \"EvnRecept_VKProtocolNum\",
				to_char(er.EvnRecept_VKProtocolDT, 'dd.mm.yyyy') as \"EvnRecept_VKProtocolDT\",
				er.CauseVK_id as \"CauseVK_id\"
			FROM v_EvnRecept_all er
				left join v_Drug dg on er.Drug_id = dg.Drug_id
				left join v_DrugRequestRow DRR on DRR.DrugRequestRow_id = er.DrugRequestRow_id
				left join v_DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id
				left join v_EvnRecept ERec on ERec.EvnRecept_id = er.EvnRecept_id
				left join v_MedPersonal MP on MP.MedPersonal_id = DR.MedPersonal_id
					and MP.Lpu_id = DR.Lpu_id
                left join ReceptWrong wr on wr.EvnRecept_id = er.EvnRecept_id
                left join v_ReceptDelayType RDT on RDT.ReceptDelayType_id = ERec.ReceptDelayType_id
				left join ReceptOtov RO on RO.EvnRecept_id = ERec.EvnRecept_id
				left join v_OrgFarmacy OrgF on OrgF.OrgFarmacy_id = RO.OrgFarmacy_id
			WHERE (1 = 1) 
				and er.EvnRecept_id = :EvnRecept_id
				" . $lpu_filter . "
			limit 1
		";

		$queryParams['EvnRecept_id'] = $data['EvnRecept_id'];
		//echo getDebugSQL($query,$queryParams);exit;
		$response = $this->getFirstRowFromQuery($query, $queryParams);


		if ( $response !== false ) {
			$result = array($response);
			$result[0]['ReceptOtov_Date'] = '';
			if($result[0]['EvnRecept_deleted'] == 2)
				$result[0]['Recept_Result_Code'] = 4;
			switch ($result[0]['Recept_Result_Code'])
			{
				case 0:
					$result[0]['Recept_Result'] = 'Обслужен';
					$query_drug = "
						select distinct
							coalesce(D.Drug_Name,'') as \"Drug_Name\"
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
						from v_EvnRecept ER
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
							inner join v_WhsDocumentUc wdu on wdu.WhsDocumentUc_id = wdu.WhsDocumentUc_id
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
			return $result;
		}
		else {
			return false;
		}
	}
		
		
	/**
	* Получить список рецептов
	*/
	function getEvnReceptList4Provider($data) {
		$queryParams = array();
		$filter = ' (1 = 1)'; // and er.EvnRecept_id = 430694508 ';
		$main_alias = "ER";
		
		$queryParams['Org_id'] = $data ['session']['org_id'];
		
		if (isset($data['ReceptDateType_id'])) 
			  $ReceptDateType_id = $data['ReceptDateType_id'];
		else
			$ReceptDateType_id = 1;
            
		if (isset($data['ReceptDelayType_id'])) 
			$ReceptDelayType_id = $data['ReceptDelayType_id'];   

		if (isset($data['EvnRecept_setDate_Range'][0])) {
				if ($ReceptDateType_id == 1)
					$filter .= " and ER.EvnRecept_setDT >= cast(:EvnRecept_setDate_Range_0 as timestamp)";
				else
					$filter .= " and ER.EvnRecept_otpDT >= cast(:EvnRecept_setDate_Range_0 as timestamp)";
				$queryParams['EvnRecept_setDate_Range_0'] = $data['EvnRecept_setDate_Range'][0];
		}

		// Рецепт
		if (isset($data['EvnRecept_setDate_Range'][1])) {
			if ($ReceptDateType_id == 1)
				$filter .= " and ER.EvnRecept_setDT <= cast(:EvnRecept_setDate_Range_1 as timestamp)";
			else
				$filter .= " and ER.EvnRecept_otpDT <= cast(:EvnRecept_setDate_Range_1 as timestamp)";
			$queryParams['EvnRecept_setDate_Range_1'] = $data['EvnRecept_setDate_Range'][1];
		}

		if (strlen($data['EvnRecept_Num']) > 0) {
			$num1 = $data['EvnRecept_Num'];
			$num2 = str_pad ($num1, 13,"0",STR_PAD_LEFT);
			$num3 = str_pad ($num1, 8,"0",STR_PAD_LEFT);
			$filter .= " and ER.EvnRecept_Num in('{$num1}', '{$num2}', '{$num3}')";		
		}
		// Рецепт
		if ($data['Drug_id'] > 0) {
				$filter .= " and ERDrug.Drug_id = :Drug_id";
				$queryParams['Drug_id'] = $data['Drug_id'];
		}
		// Рецепт
		if (strlen($data['EvnRecept_Ser']) > 0) {
				$filter .= " and ER.EvnRecept_Ser = :EvnRecept_Ser";
				$queryParams['EvnRecept_Ser'] = $data['EvnRecept_Ser'];
		}
		/*
		// Рецепт
		if ($data['DrugMnn_id'] > 0) {
				$filter .= " and ERDrug.DrugMnn_id = :DrugMnn_id";
				$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
		}
		*/

		// Рецепт (доп.)
		if (isset($data['OrgFarmacyIndex_OrgFarmacy_id'])) {
			$queryParams['OrgFarmacy_id'] = $data['OrgFarmacyIndex_OrgFarmacy_id'];
			if ($ReceptDelayType_id == -1)
				$filter .= " and (ER.OrgFarmacy_id = :OrgFarmacy_id or dr_otp.OrgFarmacy_id = :OrgFarmacy_id)";
			 else if ($ReceptDelayType_id == 1)
				$filter .= " and dr_otp.OrgFarmacy_id = :OrgFarmacy_id";
			 else //if ($ReceptDelayType_id = 0 ||  $ReceptDelayType_id = 2 || $ReceptDelayType_id = 3)
				$filter .= " and ER.OrgFarmacy_id = :OrgFarmacy_id";
		}

		// Рецепт
		if ($data['ER_MedPersonal_id'] > 0) {
				$filter .= " and ER.MedPersonal_id = :ER_MedPersonal_id";
				$queryParams['ER_MedPersonal_id'] = $data['ER_MedPersonal_id'];
		}

		// Пациент
		if (strlen($data['Person_Surname']) > 0) {
				$queryParams['Person_Surname'] = rtrim($data['Person_Surname']) . '%';
				$filter .= " and Person_SurName ilike :Person_Surname";
		}
		 // Пациент
		if (strlen($data['Person_Firname']) > 0) {
			$filter .= " and Person_FirName ilike :Person_Firname";
			$queryParams['Person_Firname'] = rtrim($data['Person_Firname']) . '%';
		}

		// Пациент
		if (strlen($data['Person_Secname']) > 0) {
			$filter .= " and Person_SecName ilike :Person_Secname";
			$queryParams['Person_Secname'] = rtrim($data['Person_Secname']) . '%';
		}       

		// Пациент
		if (isset($data['Person_Birthday'])) {
			$filter .= " and Person_BirthDay = cast(:Person_Birthday as timestamp)";
			$queryParams['Person_Birthday'] = $data['Person_Birthday'];
		}
		
		if (isset($data['ReceptDelayType_id'])) {
            if ($data['ReceptDelayType_id'] >= 0) {
                $queryParams['ReceptDelayType_id'] = $data['ReceptDelayType_id'];
                $filter .= " and coalesce(ER.ReceptDelayType_id, 0) = coalesce(:ReceptDelayType_id, 0)";
            }         
        }
               
        if (isset($data['Lpu_id']) && $data['Lpu_id'] != 0) {
            $filter .= " and er.Lpu_id = :Lpu_id";
            $queryParams['Lpu_id'] = $data['Lpu_id'];
        }
			
							
		$query = "
			select
			-- select
				ER.EvnRecept_id as \"EvnRecept_id\",
				ER.Person_id as \"Person_id\",
				ER.PersonEvn_id as \"PersonEvn_id\",
				ER.Server_id as \"Server_id\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				ER.ReceptDelayType_id as \"ReceptDelayType_id\",
				ER.Drug_id as \"Drug_id\",
				ER.Drug_rlsid as \"Drug_rlsid\",
				ER.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				ER.OrgFarmacy_oid as \"OrgFarmacy_oid\",
				(select ReceptDelayType_Name
					from ReceptDelayType ER_RDT
					where ER_RDT.ReceptDelayType_id = ER.ReceptDelayType_id
					limit 1
				) as ReceptDelayType_Name,
				(select Org_Name
					from v_OrgFarmacy ER_OF
					where ER_OF.OrgFarmacy_id = ER.OrgFarmacy_oid
					limit 1
				) as OrgFarmacy_oName,
				(case when ER.ReceptDelayType_id  > 0
					then (
						select ReceptDelayType_Name
						from ReceptDelayType ER_RDT
						where ER_RDT.ReceptDelayType_id = ER.ReceptDelayType_id
					) || coalesce(' ' || coalesce((
						select Org_Name
						from v_OrgFarmacy ER_OF
						where ER_OF.OrgFarmacy_id = ER.OrgFarmacy_oid
					), '') || case when ER.ReceptDelayType_id = 3
						and Wr.ReceptWrong_Decr is not null
							then ' (' || Wr.ReceptWrong_Decr || ')'
							else ''
						end, '')
					else ''
				end) as \"Delay_info\",
				RTRIM(PS.Person_Surname) as \"Person_Surname\",
				RTRIM(PS.Person_Firname) as \"Person_Firname\",
				RTRIM(PS.Person_Secname) as \"Person_Secname\",
				coalesce(Person_SurName, '') || ' ' || case when Person_FirName is not null
					and length(Person_FirName) > 0
						then SUBSTRING(Person_FirName, 1, 1) || '. '
						else '_.'
					end || case when Person_SecName is not null
						and length(Person_SecName) > 0
							then SUBSTRING(Person_SecName, 1, 1) || '. '
							else '_.'
				end as \"Person_FIO\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_Birthday\",
				to_char(PS.Person_DeadDT, 'dd.mm.yyyy') as \"Person_deadDT\",
				to_char(ER.EvnRecept_setDate, 'dd.mm.yyyy') as \"EvnRecept_setDate\",
				to_char(ER.EvnRecept_otpDT, 'dd.mm.yyyy')  as \"EvnRecept_otpDT\",
				RTRIM(ER.EvnRecept_Ser) as \"EvnRecept_Ser\",
				RTRIM(ER.EvnRecept_Num) as \"EvnRecept_Num\",
				ROUND(ER.EvnRecept_Kolvo, 3) as \"EvnRecept_Kolvo\",
				RTRIM(ERMP.Person_Fin) as \"MedPersonal_Fin\",
				case when EvnRecept_IsMnn = 2
					then ''
					else 'Торг.'
				end as \"EvnRecept_IsMnn\",
				-- Наименование ЛС
				case when EvnRecept_IsMnn = 2
					and mnn.DrugMnn_id <> 3379
						then mnn.DrugMnn_Name
							|| ' ' ||  coalesce(df.DrugForm_Name, '')
							|| ' ' || ltrim(rtrim(coalesce(Drug_DoseQ, '')))
							|| ' ' || coalesce(Drug_DoseEi, '')
							|| case when Drug_Vol IS not null
								and Drug_Vol <> 0
									then ' '  || to_char( Drug_Vol) || ' ' || coalesce (ev.DrugEdVol_Name, '' )
									else ''
								end
							|| case when Drug_DoseCount is Not null
								and Drug_DoseCount > 0
									then to_char( Drug_DoseCount) || case when Drug_DoseCount >= 5
										and Drug_DoseCount <= 20
											then ' доз'
										when Drug_DoseCount % 10 =  1
											then ' доза'
										when Drug_DoseCount % 10 in (2, 3, 4)
											then ' дозы'
											else ' доз'
										end
									else ''
							end
						|| case when Drug_fas is not null
							then ' №'  || Drug_fas
							else ''
						end
					else ERDrug.Drug_Name
				end as \"Drug_Name\",
				coalesce(dr_otp.Drug_Name, '') as \"Drug_NameOtp\",
				dr_otp.DocumentUcStr_Count as \"EvnRecept_KolvoOtp\",
				dr_otp.DocumentUc_id as \"DocumentUc_id\",
				ERDrug.Drug_Code as \"DrugNomen_Code\",
				CASE WHEN PS.Server_pid = 0
					THEN 'true'
					ELSE 'false'
				END as \"Person_IsBDZ\",
				RecF.ReceptForm_Code as \"ReceptForm_Code\",
				ER.ReceptRemoveCauseType_id as \"ReceptRemoveCauseType_id\",
				coalesce(wdcit.MorbusType_id, 1) as \"MorbusType_id\",
				--Дата окончания срока действия рецепта
				case when ER.ReceptDelayType_id IS Not null
					then null-- Рецепт имеет статус
					when   RV.ReceptValidType_id = 1 --'day'
						then to_char(ER.EvnRecept_setDT + cast(RV.ReceptValid_Value || ' days' as interval)), 'dd.mm.yyyy')
					when RV.ReceptValidType_id = 2 --'month'
						then to_char(ER.EvnRecept_setDT + cast(RV.ReceptValid_Value || ' months' as interval)), 'dd.mm.yyyy')
					else to_char(ER.EvnRecept_setDate, 'dd.mm.yyyy')
				end as \"EvnRecept_DateCtrl\",
				-- Превышение срока действия рецепта
				case when ER.ReceptDelayType_id IS Not null
					then 0 -- Рецепт имеет статус
				when RV.ReceptValidType_id = 1 and (ER.EvnRecept_setDT + cast(RV.ReceptValid_Value || ' days' as interval))  < GETDATE()--'day'
					then 1
				when RV.ReceptValidType_id = 2 and (ER.EvnRecept_setDT + cast(RV.ReceptValid_Value || ' months' as interval)) <  GETDATE()--'month'
					then 1
					else 0
				end as \"EvnRecept_Shelf\",
				to_char(persPriv.PersonPrivilege_begDate, 'dd.mm.yyyy') as \"PersonPrivilege_begDate\",
				case when persPriv.PersonPrivilege_begDate is not null
					then to_char(coalesce(persPriv.PersonPrivilege_endDate, '3000-01-01'), 'dd.mm.yyyy')
					else null
				end as \"PersonPrivilege_endDate\",
				ER.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				wdcit.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				(case when ER.EvnRecept_IsSigned = 2 or (ER.pmUser_signID is not null and ER.EvnRecept_signDT is not null)
					then 2
					else 1
				end) as \"EvnRecept_IsSigned\",
				to_char(ER.EvnRecept_signDT, 'dd.mm.yyyy') as \"EvnRecept_signDT\",
				puc.pmUser_Name as \"ERSignPmUser_Name\",
				(case when ER.EvnRecept_IsOtvSigned = 2 or (ER.pmUser_signOtvID is not null and ER.EvnRecept_signOtvDT is not null)
					then 2
					else 1
				end) as \"EvnRecept_IsOtvSigned\",
				to_char(ER.EvnRecept_signotvDT, 'dd.mm.yyyy') as \"EvnRecept_signotvDT\",
				puc2.pmUser_Name as \"ROSignPmUser_Name\",
				DrugOstatRegistry_Kolvo as \"DrugOstatRegistry_Kolvo\",
				notif.receptNotification_phone as \"receptNotification_phone\",
				coalesce(to_char( notif.receptNotification_setDate, 'dd.mm.yyyy'), '') as \"receptNotification_setDate\",
				ER.Signatures_id as \"Signatures_id\"
			-- end select
			FROM
			-- from
				v_PersonState PS
				inner join v_EvnRecept ER on ER.Person_id = PS.Person_id
				left join v_pmUserCache puc on puc.pmUser_id = ER.pmUser_signID
				left join v_pmUserCache puc2 on puc2.pmUser_id = ER.pmUser_signotvID
				left join dbo.ReceptValid RV on RV.ReceptValid_id = ER.ReceptValid_id
				left join lateral(
					select
						PersonPrivilege_begDate,
						coalesce(persPriv.PersonPrivilege_endDate, '3000-01-01') as PersonPrivilege_endDate
					from v_PersonPrivilege persPriv
					where persPriv.Person_id = PS.Person_id
						and persPriv.PrivilegeType_id = er.PrivilegeType_id
					order by PersonPrivilege_endDate desc
					limit 1
				) persPriv on true
				left join v_Lpu lpu on lpu.Lpu_id = er.lpu_id
				left join v_ReceptForm RecF on RecF.ReceptForm_id = ER.ReceptForm_id
				left join v_Drug ERDrug on ERDrug.Drug_id = ER.Drug_id
				left join DrugMnn mnn on mnn.DrugMnn_id = ERDrug.DrugMnn_id
				left join lateral(
					select
						coalesce(Sum(dor.DrugOstatRegistry_Kolvo), 0) as DrugOstatRegistry_Kolvo
					from v_Drug Dr
						inner join DrugOstatRegistry dor  on dor.Drug_did = Dr.Drug_id
						inner join  dbo.v_OrgFarmacyIndex farmI on farmI.Org_id = dor.Org_id
							and coalesce(farmI.Storage_id, 0) = coalesce(dor.Storage_id, 0)
							and farmI.lpu_id = er.lpu_id
							and dor.Org_id = :Org_id
							and dor.WhsDocumentCostItemType_id = er.WhsDocumentCostItemType_id
							and dor.DrugOstatRegistry_Kolvo > 0
							and SubAccountType_id in (1, 2)
							and 1 = case when er.ReceptDelayType_id is null or er.ReceptDelayType_id = 2
								then 1
								else 0
							end
					where Dr.DrugMnn_id = mnn.DrugMnn_id
				) dor on true
				left join lateral(
					select
						receptNotification_phone,
						receptNotification_setDate
					from receptNotification notif
					where notif.evnRecept_id = er.EvnRecept_id
						and coalesce(notif.receptNotification_deleted, 1) = 1
						and er.ReceptDelayType_id = 2
					order by receptNotification_id desc
					limit 1
				) notif on true
				left join DrugEdVol ev on ev.DrugEdVol_id = ERDrug.DrugEdVol_id
				left join drugForm df on df.DrugForm_id = ERDrug.DrugForm_id
				left join lateral(
					select
						du.DocumentUc_id,
						dr.Drug_Name,
						farm.OrgFarmacy_id,
						DUS.DocumentUcStr_Count
					from DocumentUcStr DUS
						inner join v_Drug dr on dr.Drug_id = DUS.Drug_id
						inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
						inner join OrgFarmacy farm on farm.Org_id = du.Org_id
					where DUS.EvnRecept_id = er.EvnRecept_id
						and (du.DrugDocumentStatus_id = 2
							or (du.DrugDocumentStatus_id = 1 and er.ReceptDelayType_id = 2)
						)
					limit 1
				) dr_otp on true
				left join dbo.v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id
				left join lateral(
					select
						Person_Fin
					from v_MedPersonal
					where MedPersonal_id = ER.MedPersonal_id
					limit 1
				) as ERMP on true
				left join Diag ERDiag on ERDiag.Diag_id = ER.Diag_id
				left join ReceptWrong Wr on Wr.EvnRecept_id = ER.EvnRecept_id
			-- end from
			where
			-- where
				{$filter}
			-- end where
			Order by
			-- order by
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName
			limit 10000
			-- end order by
		";

		//echo '<pre>' . print_r($data, 1) . '</pre>';
		if ($data['start'] >= 0 && $data['limit'] >= 0) {
			$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
			//die(getDebugSQL($limit_query, $queryParams));
			//$result = $this->db->query($limit_query, $queryParams);
			 //$dbrep = $this->load->database('bdwork', true);
			$dbrep = $this->db;
			$result = $dbrep->query($limit_query, $queryParams);
		} else {
                        $result = $this->db->query($query, $queryParams);
		}

		if (is_object($result)) {
			$res = $result->result('array');
			if (is_array($res)) {
				$response['data'] = $res;
				if (count($res)==$data['limit']) {
					$get_count_query = getCountSQLPH($query);
					$get_count_result = $dbrep->query($get_count_query, $queryParams);


					if (is_object($get_count_result)) {
							$response['totalCount'] = $get_count_result->result('array');
							$response['totalCount'] = $response['totalCount'][0]['cnt'];
					} else {
							return false;
					}
				} else {
						$response['totalCount'] = $data['start'] + count($res);
				}
			} else {
					return false;
			}
		} else {
				return false;
		}

		return $response;
								
								
	}
	
	
	/**
     * Сохранение рецепта
     */
	function saveEvnRecept($data) {
                 $region = $data['session']['region']['nick'];
		// Сохранение нового рецепта
		if ( empty($data['EvnRecept_id']) ) {
			$action = 'ins';
		}
		else if ( 0 < $data['EvnRecept_id'] ) {
			$action = 'upd';
		}
		else {
			return array(array('success' => false, 'Error_Msg' => 'Неверное значение идентификатора рецепта'));
		}

		$options = getOptions();
		$set_Is7Noz = '';

		// Проверки входящих данных
		// @task https://redmine.swan.perm.ru/issues/101876
		if (
			($data['EvnRecept_Ser'] == $options['recepts']['evn_recept_reg_ser'] && $data['ReceptFinance_id'] != 2)
			|| ($data['EvnRecept_Ser'] == $options['recepts']['evn_recept_fed_ser'] && $data['ReceptFinance_id'] != 1)
		) {
			return array(array('success' => false, 'Error_Msg' => 'Несоответствие серии рецепта и типа финансирования'));
		}

		$this->db->trans_begin();

		// Проверка на уникальность серии и номера рецепта
		// Реализовал безусловную проверку на уникальность серии и номера рецепта
		// https://redmine.swan.perm.ru/issues/88878
		$check_recept_ser_num = $this->checkReceptSerNum($data);

		if ( $check_recept_ser_num == -1 ) {
			$this->db->trans_rollback();
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при проверке уникальности серии и номера рецепта'));
		}
		else if ( $check_recept_ser_num > 0 ) {
			$this->db->trans_rollback();
			return array(array('success' => false, 'Error_Msg' => 'Рецепт с такими серией и номером уже был выписан ранее'));
		}

		$DrugComplexMnn_id = null;
		$WhsDocumentCostItemType_id = null;
		$DrugFinance_id = null;
                          
		if ($this->getRegionNick() == 'perm') {
			$query = "
				select
					rlsDrug.DrugComplexMnn_id as \"DrugComplexMnn_id\"
				from
					v_Drug Drug
					inner join rls.v_DrugNomen DN on DN.DrugNomen_Code = cast(Drug.Drug_CodeG as varchar(20))
					inner join rls.v_Drug rlsDrug on rlsDrug.Drug_id = DN.Drug_id
				where
					Drug.Drug_id = :Drug_id
				limit 1
			";
			$params = array('Drug_id' => $data['Drug_id']);
			$resp = $this->queryResult($query, $params);
			if (!$this->isSuccessful($resp)) {
				$this->db->trans_rollback();
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при определении комплексного МНН'));
			} else if (count($resp) == 1) {
				$DrugComplexMnn_id = $resp[0]['DrugComplexMnn_id'];
			}
		}   
                   
                    
		if ($this->getRegionNick() == 'perm' || $region == 'ufa') {
			//$WhsDocumentCostItemType_Nick = '';
                        $WhsDocumentCostItemType_Name = '';
			switch ($data['ReceptFinance_id']) {
				case 1:
					//$WhsDocumentCostItemType_Nick = 'fl';
                                        $WhsDocumentCostItemType_Name = 'ОНЛС';
					break;
				case 2:
					//$WhsDocumentCostItemType_Nick = 'rl';
                                        $WhsDocumentCostItemType_Name = 'Региональная льгота';
					break;
				case 3:
					//$WhsDocumentCostItemType_Nick = 'vzn';
                                        $WhsDocumentCostItemType_Name = 'ВЗН';
					break;
			}
			if ($data['EvnRecept_Is7Noz'] == 2) {
				//$WhsDocumentCostItemType_Nick = 'vzn';
                            $WhsDocumentCostItemType_Name = 'ВЗН';
							$set_Is7Noz = 'Set @EvnRecept_Is7Noz = 2;';
			}
			$query = "
				select
					WDCIT.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
					WDCIT.DrugFinance_id as \"DrugFinance_id\"
				from v_WhsDocumentCostItemType WDCIT 
				where WDCIT.WhsDocumentCostItemType_Name = :WhsDocumentCostItemType_Name 
				limit 1
			";

			$query = "
				Declare
					@EvnRecept_Is7Noz int;
					
				{$set_Is7Noz}
				With tmp as (
					-- Старый алгоритм
					(select
						WDCIT.WhsDocumentCostItemType_id,
						WDCIT.DrugFinance_id,
						NULL privWhsDocumentCostItemType_id,
						NULL privDrugFinance_id
					from v_WhsDocumentCostItemType WDCIT
					where WDCIT.WhsDocumentCostItemType_Name = :WhsDocumentCostItemType_Name
					limit 1)
					Union
						-- Учитываем WhsDocumentCostItemType_id в таблице PrivilegeType 
					(select
						NULL as WhsDocumentCostItemType_id,
						NULL as DrugFinance_id,
						case when @EvnRecept_Is7Noz = 2 and PrivilegeType_IsNoz = 2 then 3 else WhsDocumentCostItemType_id end privWhsDocumentCostItemType_id,
						case when @EvnRecept_Is7Noz = 2 and PrivilegeType_IsNoz = 2 then 3 else DrugFinance_id end  privDrugFinance_id
					from PrivilegeType
					where PrivilegeType_id = :PrivilegeType_id
					limit 1)
				), main as (
					SElect
						max(WhsDocumentCostItemType_id) WhsDocumentCostItemType_id ,
						max(DrugFinance_id) DrugFinance_id,
						max(privWhsDocumentCostItemType_id) privWhsDocumentCostItemType_id,
						max(privDrugFinance_id) privDrugFinance_id
					from tmp
				)
				select
					case 
						when WhsDocumentCostItemType_id = 2 and privWhsDocumentCostItemType_id = 1
							then WhsDocumentCostItemType_id
						else coalesce(privWhsDocumentCostItemType_id, WhsDocumentCostItemType_id) 
					end as \"WhsDocumentCostItemType_id\",
					case 
						when WhsDocumentCostItemType_id = 2 and privWhsDocumentCostItemType_id = 1
							then 27
						when privWhsDocumentCostItemType_id is not null 
							then privDrugFinance_id 
						else DrugFinance_id 
					end as \"DrugFinance_id\"
				from main
			";

			//$params = array('WhsDocumentCostItemType_Name' => $WhsDocumentCostItemType_Name);
			$params = array();
			$params ['WhsDocumentCostItemType_Name'] = $WhsDocumentCostItemType_Name;
			$params ['PrivilegeType_id'] = $data['PrivilegeType_id'];
                        //echo getDebugSql($query, $params);
			$resp = $this->queryResult($query, $params);
			if (!$this->isSuccessful($resp)) {
				$this->db->trans_rollback();
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при определении статьи расходов'));
			}
			$WhsDocumentCostItemType_id = $resp[0]['WhsDocumentCostItemType_id'];
			$DrugFinance_id = $resp[0]['DrugFinance_id'];
		}
		//  Проверяем, является ли статья расхода спецпитанием
		if ( $region == 'ufa' && $WhsDocumentCostItemType_id == 2) {
			$query = "
				Select
					case when DrugClass_id = 7
						then 34
						else du.WhsDocumentCostItemType_id
					end as \"WhsDocumentCostItemType_id\",
					dr.Drug_id as \"Drug_id\"
				from v_Drug dr
					inner join OrgFarmacy farm on farm.OrgFarmacy_id = :OrgFarmacy_id
					left join lateral (
						Select
							Du.WhsDocumentCostItemType_id
						from  v_DocumentUcStr dus
							inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
								and du.Org_id = farm.Org_id
						where dus.Drug_id = dr.Drug_id
						order by  Du.WhsDocumentCostItemType_id desc
						limit 1
					) Du on true
				where dr.Drug_id = :Drug_id
				limit 1
			";
			$params = array();
			$params ['Drug_id'] = $data['Drug_id'];
			$params ['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
			//echo getDebugSQL($query, $params); 
			//$dbrep = $this->load->database('bdwork', true);
			//$resWhs =  $dbrep->query($query, $params);
			$resWhs = $this->queryResult($query, $params);
			//var_dump($resWhs); 
			if ( is_array($resWhs) && count($resWhs) > 0 ) {
				 if ($resWhs[0]['WhsDocumentCostItemType_id'] == 34)
					  //echo 'WhsDocumentCostItemType_id = ' .$resWhs[0]['WhsDocumentCostItemType_id'];
					$WhsDocumentCostItemType_id = $resWhs[0]['WhsDocumentCostItemType_id'];
			}
                   
                   
		}

		$data['EvnRecept_IsPrinted'] = null;
		// если на бланке, считаем что распечатан
		if ($data['ReceptType_id'] == 1) {
			$data['EvnRecept_IsPrinted'] = 2;
		}

		$query = "
			select
				EvnRecept_id as \"EvnRecept_id\",
				Error_Code as \"Error_Code\",
				Error_Msg as \"Error_Msg\"
			from p_EvnRecept_" . $action . "(
				EvnRecept_id := :EvnRecept_id,
				EvnRecept_pid := :EvnRecept_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnRecept_setDT := :EvnRecept_setDate,
				EvnRecept_Num := :EvnRecept_Num,
				EvnRecept_Ser := :EvnRecept_Ser,
				EvnRecept_Price := :Drug_Price,
				Diag_id := :Diag_id,
				ReceptDiscount_id := :ReceptDiscount_id,
				ReceptFinance_id := :ReceptFinance_id,
				ReceptValid_id := :ReceptValid_id,
				PersonPrivilege_id := :PersonPrivilege_id,
				PrivilegeType_id := :PrivilegeType_id,
				EvnRecept_IsKEK := :Drug_IsKEK,
				EvnRecept_Kolvo := :EvnRecept_Kolvo,
				MedPersonal_id := :MedPersonal_id,
				LpuSection_id := :LpuSection_id,
				Drug_id := :Drug_id,
				DrugComplexMnn_id := :DrugComplexMnn_id,
				WhsDocumentCostItemType_id := :WhsDocumentCostItemType_id,
				DrugFinance_id := :DrugFinance_id,
				ReceptForm_id := :ReceptForm_id,
				ReceptType_id := :ReceptType_id,
				EvnRecept_IsMnn := :Drug_IsMnn,
				EvnRecept_Signa := :EvnRecept_Signa,
				OrgFarmacy_id := :OrgFarmacy_id,
				EvnRecept_IsNotOstat := :EvnRecept_IsNotOstat,
				DrugRequestRow_id := :DrugRequestRow_id,
				EvnRecept_ExtempContents := :EvnRecept_ExtempContents,
				EvnRecept_IsExtemp := :EvnRecept_IsExtemp,
				EvnRecept_Is7Noz := :EvnRecept_Is7Noz,
				EvnRecept_IsPrinted := :EvnRecept_IsPrinted,
				PrescrSpecCause_id := :PrescrSpecCause_id,
				ReceptUrgency_id := :ReceptUrgency_id,
				EvnRecept_IsExcessDose := :EvnRecept_IsExcessDose,
				EvnRecept_VKProtocolNum := :EvnRecept_VKProtocolNum,
				EvnRecept_VKProtocolDT := :EvnRecept_VKProtocolDT,
				CauseVK_id := :CauseVK_id,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, array(
			'EvnRecept_id' => $data['EvnRecept_id'],
			'EvnRecept_pid' => $data['EvnRecept_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnRecept_setDate' => $data['EvnRecept_setDate'],
			'EvnRecept_Num' => $data['EvnRecept_Num'],
			'EvnRecept_Ser' => $data['EvnRecept_Ser'],
			'Drug_Price' => $data['Drug_Price'],
			'Diag_id' => $data['Diag_id'],
			'ReceptDiscount_id' => $data['ReceptDiscount_id'],
			'ReceptFinance_id' => $data['ReceptFinance_id'],
			'ReceptValid_id' => $data['ReceptValid_id'],
			'PersonPrivilege_id' => $data['PersonPrivilege_id'],
			'PrivilegeType_id' => $data['PrivilegeType_id'],
			'Drug_IsKEK' => $data['Drug_IsKEK'],
			'EvnRecept_Kolvo' => $data['EvnRecept_Kolvo'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Drug_id' => $data['Drug_id'],
			'DrugComplexMnn_id' => $DrugComplexMnn_id,
			'WhsDocumentCostItemType_id' => $WhsDocumentCostItemType_id,
			'DrugFinance_id' => $DrugFinance_id,
			'EvnRecept_Signa' => $data['EvnRecept_Signa'],
            'ReceptForm_id' => $data['ReceptForm_id'],
			'ReceptType_id' => $data['ReceptType_id'],
			'Drug_IsMnn' => $data['Drug_IsMnn'],
			'OrgFarmacy_id' => $data['OrgFarmacy_id'],
			'EvnRecept_IsNotOstat' => $data['EvnRecept_IsNotOstat'],
			'DrugRequestRow_id' => $data['DrugRequestRow_id'],
			'EvnRecept_ExtempContents' => $data['EvnRecept_ExtempContents'],
			'EvnRecept_IsExtemp' => $data['EvnRecept_IsExtemp'],
			'EvnRecept_Is7Noz' => $data['EvnRecept_Is7Noz'],
			'EvnRecept_IsPrinted' => $data['EvnRecept_IsPrinted'],
			'PrescrSpecCause_id' => !empty($data['PrescrSpecCause_id']) ? $data['PrescrSpecCause_id'] : null,
			'ReceptUrgency_id' => !empty($data['ReceptUrgency_id']) ? $data['ReceptUrgency_id'] : null,
			'EvnRecept_IsExcessDose' => !empty($data['EvnRecept_IsExcessDose']) ? 2 : 1,
			'EvnRecept_VKProtocolNum' => $data['EvnRecept_VKProtocolNum'],
			'EvnRecept_VKProtocolDT' => $data['EvnRecept_VKProtocolDT'],
			'CauseVK_id' => $data['CauseVK_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 || empty($response[0]['EvnRecept_id']) ) {
			$this->db->trans_rollback();
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		$data['EvnRecept_id'] = $response[0]['EvnRecept_id'];
		
		// Повторно проверяем на уникальность серии и номера рецепта
		// https://redmine.swan.perm.ru/issues/25626
		// Реализовал безусловную проверку на уникальность серии и номера рецепта
		// https://redmine.swan.perm.ru/issues/88878
		$check_recept_ser_num = $this->checkReceptSerNum($data);

		if ( $check_recept_ser_num == -1 ) {
			$this->db->trans_rollback();
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при проверке уникальности серии и номера рецепта'));
		}
		else if ( $check_recept_ser_num > 0 ) {
			$this->db->trans_rollback();
			return array(array('success' => false, 'Error_Msg' => 'Рецепт с такими серией и номером уже был выписан ранее'));
		}

		$this->db->trans_commit();

		return $response;		
	}

	/**
	 * Возвращает список невалидных рецептов по заданным фильтрам
	 */
        
	function getEvnReceptInCorrectList($data)
	{
		//$this->writeToLog(ImplodeAssoc('=', '|', $data)."\r\n");
		$basefilters=array();
		$join='';
		$queryParams = array();
		
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		
		if ($data['Person_Surname']) $data['Person_Surname'] = rtrim($data['Person_Surname']);
		if ($data['Person_Secname']) $data['Person_Secname'] = rtrim($data['Person_Secname']);
		if ($data['Person_Firname']) $data['Person_Firname'] = rtrim($data['Person_Firname']);

		$this->genEvnReceptListFilters($data, $basefilters, $queryParams, $join);
               
		if (ArrayVal($data,'PersonSex_id')!='' ||
			ArrayVal($data,'SocStatus_id')!='' ||
			ArrayVal($data,'DocumentType_id')!='' ||
			ArrayVal($data,'OrgDep_id')!='' ||
			ArrayVal($data,'OMSSprTerr_id')!='' ||
			ArrayVal($data,'PolisType_id')!='' ||
			ArrayVal($data,'OrgSMO_id')!='' ||
			ArrayVal($data,'Org_id')!='' ||
			ArrayVal($data,'Post_id')!='' ||
			($data['KLRgn_id'] > 0) ||
			($data['KLSubRgn_id'] > 0) ||
			($data['KLCity_id'] > 0) ||
			($data['KLTown_id'] > 0) ||
			($data['KLStreet_id'] > 0) ||
			(strlen($data['Address_House']) > 0)
		) {
			$tableName = 'v_Person_pfr';
		} else {
			$tableName = 'v_Person_FIO';
		}
		
		// фильтр по ЛПУ в джойне медперсонала если под минздравом и не выбрано ЛПУ, то показывает по всем ЛПУ
		$med_personal_lpu_filter = " and Lpu_id = :Lpu_id ";
		
		if ( (($data['SearchedLpu_id'] == 0) || ($data['SearchedLpu_id'] == '')) && isMinZdravOrNotLpu() )
		{
			$med_personal_lpu_filter = " and Lpu_id = EvnRecept.Lpu_id ";
		}
		
		$resArray = array();

		//Поле Lpu_Nick отображается во всех случаях по #25293
		/*$get_lpu_nick = "";
		if ( isMinZdravOrNotLpu() )
			$get_lpu_nick = " rtrim(Lpu.Lpu_Nick) as Lpu_Nick, ";*/

		$sql = null;
	
		// если не суперадмин и у пользователя есть арм ТОУЗ и нет армов МЭК ЛЛО и специалист ЛЛО, то фильтруем по ЛПУ со схожей территорией обслуживания
		if (!isSuperAdmin() && isset($data['session']['ARMList']) && in_array('touz',$data['session']['ARMList']) && !in_array('mekllo',$data['session']['ARMList']) && !in_array('minzdravdlo',$data['session']['ARMList'])) {
			if (!empty($data['session']['org_id'])) {
				// получаем список лпу с такой же территорией обслуживания, как и у организации пользователя
				// если у организации задана страна + регион то по их равенству, если задан ещё и город то и по городу тоже
				$basefilters[] = "
					EvnRecept.Lpu_id IN (
						select
							l.Lpu_id
						from
							v_OrgServiceTerr ost
							left join v_OrgServiceTerr ost2 on
								coalesce(ost2.KLCountry_id, 0) = coalesce(ost.KLCountry_id, 0)
								and coalesce(ost2.KLRGN_id, 0) = coalesce(ost.KLRGN_id, 0)
								and coalesce(ost2.KLSubRgn_id, 0) = coalesce(ost.KLSubRgn_id, 0)
								and (coalesce(ost2.KLCity_id, 0) = coalesce(ost.KLCity_id, 0) or ost.KLCity_id is NULL)
							inner join v_Lpu l on l.Org_id = ost2.Org_id
						where
							ost.Org_id = :Org_id
					)
				";
				
				$queryParams['Org_id'] = $data['session']['org_id'];
			} else {
				return false;
			}
		}

		$isExpertise = (
			ArrayVal($data,'ReceptStatusType_id') > 0 || ArrayVal($data,'ReceptStatusFLKMEK_id')
			|| ArrayVal($data,'RegistryReceptErrorType_id') || ArrayVal($data,'AllowRegistryDataRecept')
			|| ArrayVal($data,'RegistryDataRecept_IsReceived') || ArrayVal($data,'RegistryDataRecept_IsPaid')
		);
	
		$basefilters = array_merge($basefilters, getAccessRightsDiagFilter('Diag.Diag_Code', true));

		$ReceptValidCond = $this->getReceptValidCondition();

		// Поиск в таблице EvnRecept
		// tag If ((ArrayVal($data,'ReceptYes_id') == 2 || ArrayVal($data,'ReceptYes_id') == '') && !$isExpertise ) {
		If ((ArrayVal($data,'ReceptYes_id') != 2 && ArrayVal($data,'ReceptYes_id') != '') && $isExpertise )
				return false;
		$filters = $basefilters;                        
		$med_personal_lpu_filter = preg_replace('/EvnRecept\./','evnRecept_all.',$med_personal_lpu_filter);
		$ReceptValidCondFirst = preg_replace('/EvnRecept\./','evnRecept_all.',$ReceptValidCond);
		$join = preg_replace('/evnRecept_all\./','evnRecept.',$join); 
		foreach($filters as $key=>$filter) {
				$filters[$key] = preg_replace('/evnRecept_all\./','evnRecept.',$filters[$key]);
				$filters[$key] = preg_replace('/EvnRecept_Num \= \:EvnRecept_Num/', "EvnRecept_Num ilike '%' || :EvnRecept_Num ",$filters[$key]);
                }
		if(isset($data['ReceptForm_id'])){
			$filters[] = "RecF.ReceptForm_id = :ReceptForm_id";
			$queryParams['ReceptForm_id'] = $data['ReceptForm_id'];
		} 
		unset($filters['MedPersonalRec_id']);
		unset($filters['Person_SNILS_Recept']);  

		$sql = "
			select
				EvnRecept.ReceptDelayType_id as \"ReceptDelayType_id\",
				CASE WHEN EvnRecept.EvnRecept_deleted = 2
						THEN 'Удалённый МО'
					WHEN dbo.tzgetdate() >= case when RV.ReceptValidType_id = 1 --'day'
							then  dateadd(day, RV.ReceptValid_Value, EvnRecept.EvnRecept_setDT)
						when RV.ReceptValidType_id = 2 --'month'
							then dateadd(month, RV.ReceptValid_Value, EvnRecept.EvnRecept_setDT)
						end and EvnRecept.ReceptDelayType_id IS null
						then 'Просрочен'
					WHEN EvnRecept.ReceptDelayType_id IS null
						then 'Выписан'
					WHEN EvnRecept.ReceptDelayType_id = 1
						then 'Отоварен'
					WHEN EvnRecept.ReceptDelayType_id = 2
						then 'Отсрочен'
						else rdt.ReceptDelayType_Name
				end as \"ReceptDelayType_Name\",
				CASE WHEN EvnRecept.EvnRecept_IsSigned = 2
					THEN 'ДА'
					ELSE 'НЕТ'
				END as \"EvnRecept_IsSigned\",
				RT.ReceptType_Code as \"ReceptType_Code\",
				CASE WHEN RT.ReceptType_Code = 3
					THEN 'ЭД'
					ELSE RT.ReceptType_Name
				END as \"ReceptType_Name\",
				EvnRecept.ReceptForm_id as \"ReceptForm_id\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				EvnRecept.EvnRecept_id as \"EvnRecept_id\",
				EvnRecept.Person_id as \"Person_id\",
				EvnRecept.PersonEvn_id as \"PersonEvn_id\",
				EvnRecept.Server_id as \"Server_id\",
				EvnRecept.OrgFarmacy_id as \"OrgFarmacy_id\",
				EvnRecept.OrgFarmacy_oid as \"OrgFarmacy_oid\",
				Person.Person_Surname as \"Person_Surname\",
				Person.Person_Firname as \"Person_Firname\",
				Person.Person_Secname as \"Person_Secname\",
				coalesce (Person.Person_Surname, '')
					|| ' ' || coalesce (Person.Person_Firname, '')
					|| ' ' || coalesce (Person.Person_Secname, ''
				) as \"Person_FIO\",
				to_char(Person.Person_BirthDay, 'dd.mm.yyyy') as \"Person_Birthday\",
				case when Person.Sex_id = 1 then 'М' when  Person.Sex_id = 2 then 'Ж' else '' end as \"Sex\",
				Person.Person_Snils as \"Person_Snils\",
				EvnRecept.EvnRecept_Ser as \"EvnRecept_Ser\",
				EvnRecept.EvnRecept_Num as \"EvnRecept_Num\",
				ReceptFinance.ReceptFinance_Name as \"ReceptFinance_Name\",
				ReceptFinance.ReceptFinance_id as \"ReceptFinance_id\",
				MedPersonal.Person_Surname 
					|| ' ' || MedPersonal.Person_Firname
					|| ' ' || MedPersonal.Person_Secname
				as \"MedPersonal_Fio\",
				case when EvnRecept_IsMnn = 2 then '' else 'Торг.' end as \"EvnRecept_IsMnn\",
				case when EvnRecept_IsMnn = 2 then mnn.DrugMnn_Name else Drug.Drug_Name end as \"DrugMnn_Name\",
				coalesce(ro.Drug_Name,'') as \"Drug_Name\",
				EvnRecept.Drug_id as \"Drug_id\",
				cast(ROUND(EvnRecept.EvnRecept_Kolvo, 3) as varchar) as \"EvnRecept_firKolvo\",
				ro.EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
				ro.EvnRecept_Sum as \"EvnRecept_Sum\",
				to_char(EvnRecept.EvnRecept_setDate, 'dd.mm.yyyy') as \"EvnRecept_setDate\",
				to_char(case when RV.ReceptValidType_id = 1
						then EvnRecept.EvnRecept_setDate + cast(RV.ReceptValid_Value || ' days' as interval)
					when RV.ReceptValidType_id = 2
						then EvnRecept.EvnRecept_setDate + cast(RV.ReceptValid_Value || ' months' as interval)
				end + interval '1 day', 'dd.mm.yyyy') as \"EvnRecept_Godn\",
				to_char(EvnRecept.EvnRecept_obrDT, 'dd.mm.yyyy') as \"EvnRecept_obrDate\",
				to_char(EvnRecept.EvnRecept_otpDT, 'dd.mm.yyyy') as \"EvnRecept_otpDate\",
				datediff('day', EvnRecept.EvnRecept_setDT, EvnRecept.EvnRecept_obrDT) as \"EvnRecept_obrDay\",
				datediff('day', EvnRecept.EvnRecept_obrDT, coalesce(EvnRecept.EvnRecept_otpDT, dbo.tzgetdate())) as \"EvnRecept_otsDay\",
				datediff('day', EvnRecept.EvnRecept_setDT, EvnRecept.EvnRecept_otpDT) as \"EvnRecept_otovDay\",
				case when EvnRecept.DrugRequestRow_id is null then 'НЕТ' else 'ДА' end as \"EvnRecept_InRequest\",
				coalesce(OrgFarmacyOtp.OrgFarmacy_Name,'') as \"OrgFarmacy_Name\",
				RecF.ReceptForm_Code as \"ReceptForm_Code\",
				adr.Address_Address as \"Address_Address\",
				Diag.Diag_Code as \"Diag_Code\",
				Diag.Diag_Name as \"Diag_Name\",
				whs.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				EvnRecept.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				ro.Drug_Code as \"Drug_Code\"
			from v_EvnRecept_all EvnRecept
				left join ReceptDelayType rdt on rdt.ReceptDelayType_id = EvnRecept.ReceptDelayType_id
				left join dbo.ReceptValid RV on RV.ReceptValid_id = EvnRecept.ReceptValid_id
				left join  v_Lpu as Lpu on Lpu.Lpu_id = EvnRecept.Lpu_id
				inner join v_PersonState Person on Person.Person_id = EvnRecept.Person_id
				inner join v_Address  adr  on adr.Address_id = Person.PAddress_id
				left join lateral(
					select
						replace (Substring (Drug_Code, 0, length(Drug_Code)) , ',', '<br/>') as Drug_Code,
						replace (Substring (Drug_Name, 0, length(Drug_Name)) , ',', '<br/>') as Drug_Name,
						replace (Substring (EvnRecept_Kolvo, 0, length(EvnRecept_Kolvo)) , ',', '<br/>') as EvnRecept_Kolvo,
						replace (Substring (EvnRecept_Sum, 0, length(EvnRecept_Sum)) , ',', '<br/>') as EvnRecept_Sum
					from (
						select
							(
								select
									string_agg(to_char( Dr.Drug_Code), ',')
								from v_DocumentUcStr dus
									inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
									inner join v_Drug Dr  on Dr.Drug_id = dus.Drug_id
								where dus.EvnRecept_id = EvnRecept.EvnRecept_id
									and du.DrugDocumentStatus_id = 2
							) as Drug_Code,
							(
								select
									string_agg(Dr.Drug_Name, ',')
								from v_DocumentUcStr dus
									inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
									inner join v_Drug Dr  on Dr.Drug_id = dus.Drug_id
								where dus.EvnRecept_id = EvnRecept.EvnRecept_id
									and du.DrugDocumentStatus_id = 2
							) as Drug_Name,
							(
								select
									string_agg(to_char(round(coalesce(dus.DocumentUcStr_Count, 0), 2)), ',')
								from v_DocumentUcStr dus
									inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
								where dus.EvnRecept_id = EvnRecept.EvnRecept_id
									and du.DrugDocumentStatus_id = 2
							) as EvnRecept_Kolvo,
							(
								select
									string_agg(to_char(ROUND(coalesce(dus.DocumentUcStr_Sum, 0), 3)), ',')
								from v_DocumentUcStr dus
									inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
								where dus.EvnRecept_id = EvnRecept.EvnRecept_id
									and du.DrugDocumentStatus_id = 2
							) as EvnRecept_Sum
					) t
				)ro on true
				left join v_ReceptFinance as ReceptFinance on ReceptFinance.ReceptFinance_id = EvnRecept.ReceptFinance_id
				left join v_WhsDocumentCostItemType as Whs on whs.WhsDocumentCostItemType_id = EvnRecept.WhsDocumentCostItemType_id
				left join lateral(
					select
						Person_Surname,
						Person_Firname,
						Person_Secname
					from v_MedPersonal
					where MedPersonal_id = EvnRecept.MedPersonal_id
						and Lpu_id = EvnRecept.Lpu_id
					limit 1
				) MedPersonal on true
				left join v_Diag as Diag on Diag.Diag_id = EvnRecept.Diag_id
				left join v_OrgFarmacy as OrgFarmacyOtp on OrgFarmacyOtp.OrgFarmacy_id = EvnRecept.OrgFarmacy_oid
				left join v_ReceptForm RecF on RecF.ReceptForm_id = EvnRecept.ReceptForm_id
				left join v_ReceptType RT on RT.ReceptType_id = EvnRecept.ReceptType_id
				inner join v_Drug Drug  on Drug.Drug_id = EvnRecept.Drug_id
				inner join DrugMnn mnn  on mnn.DrugMnn_id = Drug.DrugMnn_id
			".$join." " .ImplodeWhere($filters) ;

                        
		if ($sql!=null) {
			$sql = "
			SELECT
			-- select
				*
			-- end select
			FROM
			-- from
			(
                $sql
            ) as Recept
			-- end from
			ORDER BY 
			-- order by
				Person_Surname,
				Person_Firname,
				Person_Secname, 
                Person_id,
                EvnRecept_setDate,
                EvnRecept_id
			-- end order by";
		} else {
			return false;
		}       
                        
		//echo getDebugSql($sql, $queryParams);exit;
		//echo getDebugSql(getLimitSQLPH($sql, $data['start'], $data['limit']), $queryParams);exit;
		if (!empty($data['print']) && ($data['print'] == true)) { // если список для печати, то надо печатать весь список, а не только 100 записей
			$result = $this->db->query($sql, $queryParams);
			if (is_object($result))
			{
				//return $result->result('array');
				$response = array();
				$response['data'] =  $result->result('array');
				return $response;
			}
			
			return false;
		}
		
		$dbrep = $this->db;
		//$dbrep = $this->load->database('bdwork', true);
		
		$count = 0;
		// Отдельно для количества 
		if (!empty($data['onlyCount']) && ($data['onlyCount'] == true)) {
			$result_count = $dbrep->query(getCountSQLPH($sql), $queryParams);
			if (is_object($result_count)) {
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			}
			return $count;
		}
		
		$result = $dbrep->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $queryParams);
		
		if (is_object($result)) {
			$res = $result->result('array');
			// Если количество записей запроса равно limit, то, скорее всего еще есть страницы и каунт надо посчитать
			if (count($res)==$data['limit']) {
				// определение общего количества записей
				$result_count = $dbrep->query(getCountSQLPH($sql), $queryParams);
				if (is_object($result_count)) {
					$cnt_arr = $result_count->result('array');
					$count = $cnt_arr[0]['cnt'];
					unset($cnt_arr);
				} else {
					$count = 0;
				}
			} else { // Иначе считаем каунт по реальному количеству + start
				$count = $data['start'] + count($res);
			}
			$response = array();
			$response['totalCount'] = $count;
			$response['data'] =  $res;
			return $response;
		} else {
			return false;
		}
	}

    /**
	 * Возвращает список невалидных рецептов по заданным фильтрам (прежняя версия)
	 */
	 
	function getEvnReceptInCorrectList_Old($data)
	{
		//$this->writeToLog(ImplodeAssoc('=', '|', $data)."\r\n");
		$basefilters=array();
		$join='';
		$queryParams = array();
		
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		
		if ($data['Person_Surname']) $data['Person_Surname'] = rtrim($data['Person_Surname']);
		if ($data['Person_Secname']) $data['Person_Secname'] = rtrim($data['Person_Secname']);
		if ($data['Person_Firname']) $data['Person_Firname'] = rtrim($data['Person_Firname']);

		$this->genEvnReceptListFilters($data, $basefilters, $queryParams, $join);             

		if (ArrayVal($data,'PersonSex_id')!='' ||
			ArrayVal($data,'SocStatus_id')!='' ||
			ArrayVal($data,'DocumentType_id')!='' ||
			ArrayVal($data,'OrgDep_id')!='' ||
			ArrayVal($data,'OMSSprTerr_id')!='' ||
			ArrayVal($data,'PolisType_id')!='' ||
			ArrayVal($data,'OrgSMO_id')!='' ||
			ArrayVal($data,'Org_id')!='' ||
			ArrayVal($data,'Post_id')!='' ||
			($data['KLRgn_id'] > 0) ||
			($data['KLSubRgn_id'] > 0) ||
			($data['KLCity_id'] > 0) ||
			($data['KLTown_id'] > 0) ||
			($data['KLStreet_id'] > 0) ||
			(strlen($data['Address_House']) > 0)
		) {
			$tableName = 'v_Person_pfr';
		} else {
			$tableName = 'v_Person_FIO';
		}
		
		// фильтр по ЛПУ в джойне медперсонала если под минздравом и не выбрано ЛПУ, то показывает по всем ЛПУ
		$med_personal_lpu_filter = " and Lpu_id = :Lpu_id ";
		
		if ( (($data['SearchedLpu_id'] == 0) || ($data['SearchedLpu_id'] == '')) && isMinZdravOrNotLpu() )
		{
			$med_personal_lpu_filter = " and Lpu_id = EvnRecept.Lpu_id ";
		}
		
		$resArray = array();

		//Поле Lpu_Nick отображается во всех случаях по #25293
		/*$get_lpu_nick = "";
		if ( isMinZdravOrNotLpu() )
			$get_lpu_nick = " rtrim(Lpu.Lpu_Nick) as Lpu_Nick, ";*/

		$sql = null;
		
		// если не суперадмин и у пользователя есть арм ТОУЗ и нет армов МЭК ЛЛО и специалист ЛЛО, то фильтруем по ЛПУ со схожей территорией обслуживания
		if (!isSuperAdmin() && isset($data['session']['ARMList']) && in_array('touz',$data['session']['ARMList']) && !in_array('mekllo',$data['session']['ARMList']) && !in_array('minzdravdlo',$data['session']['ARMList'])) {
			if (!empty($data['session']['org_id'])) {
				// получаем список лпу с такой же территорией обслуживания, как и у организации пользователя
				// если у организации задана страна + регион то по их равенству, если задан ещё и город то и по городу тоже
				$basefilters[] = "
					EvnRecept.Lpu_id IN (
						select
							l.Lpu_id
						from
							v_OrgServiceTerr ost
							left join v_OrgServiceTerr ost2 on
								coalesce(ost2.KLCountry_id, 0) = coalesce(ost.KLCountry_id, 0)
								and coalesce(ost2.KLRGN_id, 0) = coalesce(ost.KLRGN_id, 0)
								and coalesce(ost2.KLSubRgn_id, 0) = coalesce(ost.KLSubRgn_id, 0)
								and (coalesce(ost2.KLCity_id, 0) = coalesce(ost.KLCity_id, 0) or ost.KLCity_id is NULL)
							inner join v_Lpu l on l.Org_id = ost2.Org_id
						where
							ost.Org_id = :Org_id
					)
				";
				
				$queryParams['Org_id'] = $data['session']['org_id'];
			} else {
				return false;
			}
		}

		$isExpertise = (
			ArrayVal($data,'ReceptStatusType_id') > 0 || ArrayVal($data,'ReceptStatusFLKMEK_id')
			|| ArrayVal($data,'RegistryReceptErrorType_id') || ArrayVal($data,'AllowRegistryDataRecept')
			|| ArrayVal($data,'RegistryDataRecept_IsReceived') || ArrayVal($data,'RegistryDataRecept_IsPaid')
		);

		$basefilters = array_merge($basefilters, getAccessRightsDiagFilter('Diag.Diag_Code', true));

		$ReceptValidCond = $this->getReceptValidCondition();

		// Поиск в таблице EvnRecept
		If ((ArrayVal($data,'ReceptYes_id') == 2 || ArrayVal($data,'ReceptYes_id') == '') && !$isExpertise ) {
			$filters = $basefilters;  
			$med_personal_lpu_filter = preg_replace('/EvnRecept\./','evnRecept_all.',$med_personal_lpu_filter);
			$ReceptValidCondFirst = preg_replace('/EvnRecept\./','evnRecept_all.',$ReceptValidCond);
			$join = preg_replace('/EvnRecept\./','evnRecept_all.',$join);
			foreach($filters as $key=>$filter) {
				$filters[$key] = preg_replace('/EvnRecept\./','evnRecept_all.',$filters[$key]);
			} 
            if(isset($data['ReceptForm_id'])){
                $filters[] = "RecF.ReceptForm_id = :ReceptForm_id";
                $queryParams['ReceptForm_id'] = $data['ReceptForm_id'];
            }
			unset($filters['MedPersonalRec_id']);
			unset($filters['Person_SNILS_Recept']);     
			$sql = "
			SELECT 
				evnRecept_all.ReceptDelayType_id as \"ReceptDelayType_id\",
				CASE
					WHEN
						evnRecept_all.EvnRecept_deleted = 2
					THEN
						'Удалённый МО'
					WHEN
						{$ReceptValidCondFirst}
					THEN
						'Просрочен'
					WHEN
						evnRecept_all.EvnRecept_otpDT is null AND evnRecept_all.EvnRecept_obrDT is null
					THEN
						'Выписан'
					WHEN
						evnRecept_all.EvnRecept_otpDT is not null
					THEN
						'Отоварен'
					WHEN
						evnRecept_all.EvnRecept_otpDT is null and evnRecept_all.EvnRecept_obrDT is not null
					THEN
						'Отсрочен'
					WHEN
						evnRecept_all.ReceptDelayType_id = 3
					THEN
						'Отказ'
					WHEN
						evnRecept_all.ReceptDelayType_id is not  null
					THEN
						'Выписан'
				END as \"ReceptDelayType_Name\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				evnRecept_all.EvnRecept_id as \"EvnRecept_id\",
				evnRecept_all.Person_id as \"Person_id\",
				evnRecept_all.PersonEvn_id as \"PersonEvn_id\",
				evnRecept_all.Server_id as \"Server_id\",
				evnRecept_all.OrgFarmacy_id as \"OrgFarmacy_id\",
				evnRecept_all.OrgFarmacy_oid as \"OrgFarmacy_oid\",
                Convert(numeric(19,2),Summ.Summa) as \"EvnRecept_Suma\",
				Person.Person_Surname as \"Person_Surname\",
				Person.Person_Firname as \"Person_Firname\",
				Person.Person_Secname as \"Person_Secname\",
				to_char(Person.Person_BirthDay, 'dd.mm.yyyy') as \"Person_Birthday\",
                case when Person.Sex_id = 1 then 'М' when  Person.Sex_id = 2 then 'Ж' else '' end  as \"Sex\",
				Person.Person_Snils as \"Person_Snils\",
				evnRecept_all.EvnRecept_Ser as \"EvnRecept_Ser\",
				evnRecept_all.EvnRecept_Num as \"EvnRecept_Num\",
				ReceptFinance.ReceptFinance_Name as \"ReceptFinance_Name\",
				ReceptFinance.ReceptFinance_id as \"ReceptFinance_id\",
				MedPersonal.Person_Surname || ' ' || MedPersonal.Person_Firname || ' ' || MedPersonal.Person_Secname as \"MedPersonal_Fio\",
				case
					when
						coalesce(evnRecept_all.Drug_rlsid, evnRecept_all.DrugComplexMnn_id) IS Not null
					then
						coalesce(rlsActmatters.RUSNAME,rlsDrugComplexMnnName.DrugComplexMnnName_Name,'')
					else
						DrugMnn.DrugMnn_Name || case when evnRecept_all.EvnRecept_otpDT is not null then '' || coalesce(DrugMnnOtp.DrugMnn_Name,'') else '' end
				end as \"DrugMnn_Name\",
				coalesce(ro.Drug_Name,'') as \"Drug_Name\",
                ro.Drug_Code as \"Drug_Code\",
				evnRecept_all.Drug_id as \"Drug_id\",
				evnRecept_all.Drug_rlsid as \"Drug_rlsid\",
				evnRecept_all.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				cast(ROUND(evnRecept_all.EvnRecept_Kolvo, 3) as varchar) as \"EvnRecept_firKolvo\",
				cast(ROUND(ro.EvnRecept_Kolvo, 3) as varchar) as \"EvnRecept_secKolvo\",
                cast(ROUND(ro.EvnRecept_Price, 3) as varchar) as \"EvnRecept_Price\",
				cast(ROUND(ro.EvnRecept_Sum, 3) as varchar) as \"EvnRecept_Sum\",
                cast(ROUND(evnRecept_all.EvnRecept_Kolvo, 3) as varchar) || '<br/>' ||
				case when ro.EvnRecept_Kolvo is not null then cast(ROUND(ro.EvnRecept_Kolvo, 3) as varchar) else '&nbsp;' end as \"EvnRecept_Kolvo\",
				coalesce(OrgFarmacy.OrgFarmacy_Name,'') || '<br/>' || coalesce(OrgFarmacyOtp.OrgFarmacy_Name,'') as \"OrgFarmacy_Name\",
				to_char(evnRecept_all.EvnRecept_setDate, 'dd.mm.yyyy') as \"EvnRecept_setDate\",
				to_char(case 
					when RV.ReceptValid_Code = 1 then (evnRecept_all.EvnRecept_setDate + interval '1 month')
					when RV.ReceptValid_Code = 2 then (evnRecept_all.EvnRecept_setDate + interval '3 months')
					when RV.ReceptValid_Code = 3 then (evnRecept_all.EvnRecept_setDate + interval '14 days')
					when RV.ReceptValid_Code = 4 then (evnRecept_all.EvnRecept_setDate + interval '5 days')
					when RV.ReceptValid_Code = 5 then (evnRecept_all.EvnRecept_setDate + interval '2 months')
					when RV.ReceptValid_Code = 7 then (evnRecept_all.EvnRecept_setDate + interval '10 days')
					when RV.ReceptValid_Code = 8 then (evnRecept_all.EvnRecept_setDate + interval '60 days')
					when RV.ReceptValid_Code = 9 then (evnRecept_all.EvnRecept_setDate + interval '30 days')
					when RV.ReceptValid_Code = 10 then (evnRecept_all.EvnRecept_setDate + interval '90 days')
					when RV.ReceptValid_Code = 11 then (evnRecept_all.EvnRecept_setDate + interval '15 days')
				end - interval '1 day'), 'dd.mm.yyyy') as \"EvnRecept_Godn\",
				to_char(evnRecept_all.EvnRecept_obrDT, 'dd.mm.yyyy') as \"EvnRecept_obrDate\",
				to_char(evnRecept_all.EvnRecept_otpDT, 'dd.mm.yyyy') as \"EvnRecept_otpDate\",
				datediff('day', evnRecept_all.EvnRecept_setDT, evnRecept_all.EvnRecept_obrDT) as \"EvnRecept_obrDay\",
				datediff('day', evnRecept_all.EvnRecept_obrDT, coalesce(evnRecept_all.EvnRecept_otpDT, dbo.tzgetdate())) as \"EvnRecept_otsDay\",
				datediff('day', evnRecept_all.EvnRecept_setDT, evnRecept_all.EvnRecept_otpDT) as \"EvnRecept_otovDay\",
				case when evnRecept_all.DrugRequestRow_id is null then 'НЕТ' else 'ДА' end as \"EvnRecept_InRequest\",
				coalesce(OrgFarmacyOtp.OrgFarmacy_Name,'') as \"OrgFarmacyOtp_Name\",
				RecF.ReceptForm_Code as \"ReceptForm_Code\",
                adr.Address_Address as \"Address_Address\",
                Diag.Diag_Code as \"Diag_Code\",
				Diag.Diag_Name as \"Diag_Name\",
                whs.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				evnRecept_all.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
			FROM v_EvnRecept_all evnRecept_all
				left join dbo.v_ReceptValid RV on RV.ReceptValid_id = evnRecept_all.ReceptValid_id
				left join v_ReceptForm RecF on RecF.ReceptForm_id = evnRecept_all.ReceptForm_id
				inner join v_PersonState Person on Person.Person_id = evnRecept_all.Person_id
                inner join v_Address  adr  on adr.Address_id = Person.PAddress_id        
                left join lateral(
                	select
                		Dr.Drug_Code, Dr.Drug_Name,
                    	dus.DocumentUcStr_Count EvnRecept_Kolvo,
                        dus.DocumentUcStr_Price EvnRecept_Price,
                        dus.DocumentUcStr_Sum EvnRecept_Sum
                	from v_DocumentUcStr dus
                	    inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
                	    inner join v_Drug Dr on Dr.Drug_id = dus.Drug_id
                	where dus.EvnRecept_id = evnRecept_all.EvnRecept_id
                		and du.DrugDocumentStatus_id = 2
                ) ro on true
				left join lateral(
					select
						 WDSS.WhsDocumentSupplySpec_PriceNDS*EVN.EvnRecept_Kolvo  as Summa -- для неотоваренных
					from v_EvnRecept EVN
						left join WhsDocumentSupply WDS on WDS.WhsDocumentUc_id = EVN.WhsDocumentUc_id
						left join WhsDocumentSupplySpec WDSS on WDSS.WhsDocumentSupply_id = WDS.WhsDocumentSupply_id
					where
						(Evn.Drug_rlsid=WDSS.Drug_id or Evn.DrugComplexMnn_id=WDSS.DrugComplexMnn_id) and
						EVN.WhsDocumentUc_id is not null and
						--EVN.EvnRecept_otpDT is null and
						EVN.EvnRecept_id = evnRecept_all.EvnRecept_id
                ) Summ on true
				left join lateral(
					select
						Person_Surname, Person_Firname, Person_Secname
					from v_MedPersonal
					where
						MedPersonal_id = evnRecept_all.MedPersonal_id
						{$med_personal_lpu_filter}
					limit 1
				) MedPersonal on true
			LEFT JOIN
				v_Drug as Drug on Drug.Drug_id = evnRecept_all.Drug_id
			LEFT JOIN
				v_Drug as DrugOtp on DrugOtp.Drug_id = evnRecept_all.Drug_oid
			LEFT JOIN
				v_DrugMnn as DrugMnn on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
			LEFT JOIN
				v_DrugMnn as DrugMnnOtp on DrugMnnOtp.DrugMnn_id = DrugOtp.DrugMnn_id
			LEFT JOIN
				rls.v_Drug as rlsDrug on rlsDrug.Drug_id = evnRecept_all.Drug_rlsid
			LEFT JOIN
				rls.v_DrugComplexMnn as rlsDrugComplexMnn on rlsDrugComplexMnn.DrugComplexMnn_id = coalesce(rlsDrug.DrugComplexMnn_id, evnRecept_all.DrugComplexMnn_id)
			LEFT JOIN
				rls.v_DrugComplexMnnName as rlsDrugComplexMnnName on rlsDrugComplexMnnName.DrugComplexMnnName_id = rlsDrugComplexMnn.DrugComplexMnnName_id
			LEFT JOIN
				rls.v_Actmatters as rlsActmatters on rlsActmatters.Actmatters_id = rlsDrugComplexMnnName.Actmatters_id
			LEFT JOIN
				v_OrgFarmacy as OrgFarmacy on OrgFarmacy.OrgFarmacy_id = evnRecept_all.OrgFarmacy_id
			LEFT JOIN
				v_OrgFarmacy as OrgFarmacyOtp on OrgFarmacyOtp.OrgFarmacy_id = evnRecept_all.OrgFarmacy_oid
			LEFT JOIN
				v_ReceptFinance as ReceptFinance on ReceptFinance.ReceptFinance_id = evnRecept_all.ReceptFinance_id
                        LEFT JOIN	
				v_WhsDocumentCostItemType as Whs on whs.WhsDocumentCostItemType_id = evnRecept_all.WhsDocumentCostItemType_id
			LEFT JOIN
				v_ReceptDelayType as ReceptDelayType on ReceptDelayType.ReceptDelayType_id = evnRecept_all.ReceptDelayType_id
			LEFT JOIN
				v_Lpu as Lpu on Lpu.Lpu_id = evnRecept_all.Lpu_id
			LEFT JOIN
				v_Diag as Diag on Diag.Diag_id = evnRecept_all.Diag_id
			".$join." ".ImplodeWhere($filters);
					
		}
		// Поиск в таблице ReceptOtov
		If (((ArrayVal($data,'ReceptYes_id') == 1 || ArrayVal($data,'ReceptYes_id') == '') &&
			ArrayVal($data,'ReceptType_id') == '' &&//поля тип рецепта нет в отоваренных, просто не делаем запрос
			ArrayVal($data,'ReceptMismatch_id') == '' && ArrayVal($data,'ReceptResult_id') != 12) ||
			$isExpertise
		) {
			$this->genEvnReceptListExpertiseFilters($data, $basefilters, $queryParams, $join);
			$filters = $basefilters;
			unset($filters['MedPersonal_id']);
			unset($filters['Person_SNILS_Person']);
			unset($filters['EvnRecept_deleted']);
			if ( (($data['SearchedLpu_id'] == 0) || ($data['SearchedLpu_id'] == '')) && isMinZdravOrNotLpu() )
				$filters[] = "coalesce(EvnRecept.EvnRecept_id, 0) not in (select EvnRecept_id from EvnRecept inner join Evn on Evn.Evn_id = EvnRecept.Evn_id and coalesce(Evn_deleted, 1) = 1)";
			else
				$filters[] = "coalesce(EvnRecept.EvnRecept_id, 0) not in (select EvnRecept_id from EvnRecept inner join Evn on Evn.Evn_id = EvnRecept.Evn_id and coalesce(Evn_deleted, 1) = 1 where Evn.Lpu_id = :Lpu_id)";
			
			// <!-- start костылина убогая, не ну а куле?
			$ptrs = array('/EvnRecept.EvnRecept_IsNotOstat/', '/\(EvnRecept_Is7Noz/','/EvnRecept_setDate/','/:EvnRecept.EvnRecept_setDateStart/','/:EvnRecept.EvnRecept_setDateEnd/','/EvnRecept.EvnRecept.EvnRecept_setDate/','/EvnRecept_Num/','/:EvnRecept.EvnRecept_Num/','/EvnRecept_Ser/','/:EvnRecept.EvnRecept_Ser/'); // Поля, наименование которых надо заменить
			$repls = array('ER.EvnRecept_IsNotOstat', '(ER.EvnRecept_Is7Noz', 'EvnRecept_setDate',':EvnRecept_setDateStart',':EvnRecept_setDateEnd','EvnRecept_setDate','EvnRecept_Num',':EvnRecept_Num','EvnRecept_Ser',':EvnRecept_Ser'); // Поля на которые надо заменить
			foreach($filters as $k=>$f) {
				$filters[$k] = preg_replace($ptrs, $repls, $f);
			}
			// --> end костылина
			$filters_otov = $filters; //https://redmine.swan.perm.ru/issues/81039
			foreach($filters_otov as $key=>$filter) {
				if(
					strpos($filter,':EvnRecept_updDT') > 0 || 
					strpos($filter,':EvnRecept_updDTEnd') > 0 ||
					strpos($filter,':EvnRecept_insDTStart') > 0 || 
					strpos($filter,':EvnRecept_insDTEnd') > 0
				) {
					$filters_otov[$key] = preg_replace('/evnRecept_all\./','ER.',$filters_otov[$key]);
				}
				else
					$filters_otov[$key] = preg_replace('/evnRecept_all\./','EvnRecept.',$filters_otov[$key]);
			}
			$sql_recept_otov = "
			SELECT
				EvnRecept.ReceptDelayType_id as \"ReceptDelayType_id\",
				CASE
					WHEN
						{$ReceptValidCond}
					THEN
						'Просрочен'
					WHEN
						EvnRecept.ReceptDelayType_id is null
					THEN
						'Выписан'
					WHEN
						EvnRecept.EvnRecept_otpDT is not null
					THEN
						'Отоварен'
					WHEN
						EvnRecept.EvnRecept_otpDT is null and EvnRecept.EvnRecept_obrDT is not null
					THEN
						'Отсрочен'
					WHEN
						EvnRecept.ReceptDelayType_id = 3
					THEN
						'Отказ'
				END as \"ReceptDelayType_Name\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				null as \"EvnRecept_id\",
				EvnRecept.Person_id as \"Person_id\",
				evnRecept_all.OrgFarmacy_id as \"OrgFarmacy_id\",
				evnRecept_all.OrgFarmacy_oid as \"OrgFarmacy_oid\",
				null as \"PersonEvn_id\",
				null as \"Server_id\",
				Convert(numeric(19,2),Summ.DocumentUc_SumNdsR) as \"EvnRecept_Suma\",
				Person.Person_Surname as \"Person_Surname\",
				Person.Person_Firname as \"Person_Firname\",
				Person.Person_Secname as \"Person_Secname\",
				to_char(Person.Person_BirthDay, 'dd.mm.yyyy') as \"Person_Birthday\",
                case when Person.Sex_id = 1 then 'М' when  Person.Sex_id = 2 then 'Ж' else '' end as \"Sex\",
				EvnRecept.Person_Snils as \"Person_Snils\",
				EvnRecept.EvnRecept_Ser as \"EvnRecept_Ser\",
				EvnRecept.EvnRecept_Num as \"EvnRecept_Num\",
				ReceptFinance.ReceptFinance_Name as \"ReceptFinance_Name\",
				ReceptFinance.ReceptFinance_id as \"ReceptFinance_id\",
				MedPersonal.Person_Surname || ' ' || MedPersonal.Person_Firname || ' ' || MedPersonal.Person_Secname as \"MedPersonal_Fio\",
				'&nbsp;<br/>' || DrugMnn.DrugMnn_Name as \"DrugMnn_Name\",
                '' as \"Drug_Name\",
                '' as \"Drug_Code\",
				EvnRecept.Drug_id as \"Drug_id\",
				evnRecept_all.Drug_rlsid as \"Drug_rlsid\",
				evnRecept_all.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				cast(ROUND(EvnRecept.EvnRecept_Kolvo, 3)as varchar) as \"EvnRecept_firKolvo\",
				'' as \"EvnRecept_secKolvo\",
                '' as \"EvnRecept_Price\",
				'' as \"EvnRecept_Sum\",
				cast(ROUND(EvnRecept.EvnRecept_Kolvo, 3)as varchar) as \"EvnRecept_Kolvo\",
				'&nbsp;<br/>' || coalesce(OrgFarmacy.OrgFarmacy_Name,'') as \"OrgFarmacy_Name\",
				to_char(EvnRecept.EvnRecept_setDT, 'dd.mm.yyyy') as \"EvnRecept_setDate\",
				to_char(case 
					when RV.ReceptValid_Code = 1 then (evnRecept_all.EvnRecept_setDate + interval '1 month')
					when RV.ReceptValid_Code = 2 then (evnRecept_all.EvnRecept_setDate + interval '3 months')
					when RV.ReceptValid_Code = 3 then (evnRecept_all.EvnRecept_setDate + interval '14 days')
					when RV.ReceptValid_Code = 4 then (evnRecept_all.EvnRecept_setDate + interval '5 days')
					when RV.ReceptValid_Code = 5 then (evnRecept_all.EvnRecept_setDate + interval '2 months')
					when RV.ReceptValid_Code = 7 then (evnRecept_all.EvnRecept_setDate + interval '10 days')
					when RV.ReceptValid_Code = 8 then (evnRecept_all.EvnRecept_setDate + interval '60 days')
					when RV.ReceptValid_Code = 9 then (evnRecept_all.EvnRecept_setDate + interval '30 days')
					when RV.ReceptValid_Code = 10 then (evnRecept_all.EvnRecept_setDate + interval '90 days')
					when RV.ReceptValid_Code = 11 then (evnRecept_all.EvnRecept_setDate + interval '15 days')
				end - interval '1 day'), 'dd.mm.yyyy') as \"EvnRecept_Godn\",
				to_char(EvnRecept.EvnRecept_obrDT, 'dd.mm.yyyy') as \"EvnRecept_obrDate\",
				to_char(EvnRecept.EvnRecept_otpDT, 'dd.mm.yyyy') as \"EvnRecept_otpDate\",
				datediff('day', EvnRecept.EvnRecept_setDT, EvnRecept.EvnRecept_obrDT) as \"EvnRecept_obrDay\",
				datediff('day', EvnRecept.EvnRecept_obrDT, coalesce(EvnRecept.EvnRecept_otpDT, dbo.tzgetdate())) as \"EvnRecept_otsDay\",
				datediff('day', EvnRecept.EvnRecept_setDT, EvnRecept.EvnRecept_otpDT) as \"EvnRecept_otovDay\",
				'НЕТ' as \"EvnRecept_InRequest\",
				'' as \"OrgFarmacyOtp_Name\",
				null as \"ReceptForm_Code\",
                adr.Address_Address as \"Address_Address\",
                Diag.Diag_Code as \"Diag_Code\",
				Diag.Diag_Name as \"Diag_Name\",
                whs.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				evnRecept_all.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
			FROM
				v_ReceptOtovUnSub EvnRecept
			left join v_evnrecept_all  evnRecept_all on evnRecept_all.EvnRecept_id=EvnRecept.EvnRecept_id
			left join dbo.v_ReceptValid RV on RV.ReceptValid_id = EvnRecept.ReceptValid_id
			inner join 
				v_PersonState Person on Person.Person_id = EvnRecept.Person_id
                        inner join v_Address  adr  on adr.Address_id = Person.PAddress_id        
            left join lateral (
            	select
                    du.DocumentUc_SumNdsR-- для отоваренных
                from ReceptOtov otv
                    left join DocumentUcStr DUS on otv.receptotov_id=DUS.ReceptOtov_id
                    left join DocumentUc du on DUS.DocumentUc_id=du.DocumentUc_id
                where otv.EvnRecept_otpDate is not null and
                    otv.ReceptOtov_id = EvnRecept.ReceptOtov_id
            ) Summ on true
			left join lateral(
				select
					Person_Surname, Person_Firname, Person_Secname
				from v_MedPersonal
				where
					MedPersonal_id = EvnRecept.MedPersonalRec_id
					{$med_personal_lpu_filter}
				limit 1
			) MedPersonal on true
			LEFT JOIN
				v_Drug Drug on Drug.Drug_id=EvnRecept.Drug_id
			LEFT JOIN
				v_DrugMnn DrugMnn on DrugMnn.DrugMnn_id=Drug.DrugMnn_id
			LEFT JOIN
				v_OrgFarmacy as OrgFarmacy on OrgFarmacy.OrgFarmacy_id=EvnRecept.OrgFarmacy_id
			LEFT JOIN
				ReceptFinance on ReceptFinance.ReceptFinance_id = EvnRecept.ReceptFinance_id
            LEFT JOIN	
				v_WhsDocumentCostItemType as Whs on whs.WhsDocumentCostItemType_id = evnRecept_all.WhsDocumentCostItemType_id
			left join
				v_EvnRecept ER on ER.EvnRecept_id = EvnRecept.EvnRecept_id
			LEFT JOIN
				v_Lpu as Lpu on Lpu.Lpu_id = EvnRecept.Lpu_id
			LEFT JOIN
				v_Diag as Diag on Diag.Diag_id = EvnRecept.Diag_id
			".$join." ".ImplodeWhere($filters_otov);

			if ($sql!=null) {
				$sql = "($sql 
					UNION
				$sql_recept_otov
				) as Recept";
			} else {
				$sql = "($sql_recept_otov) as Recept";
			}
		
		} elseif ($sql!=null) {
			$sql = "($sql) as Recept";
		}

		if ($sql!=null) {
			$sql = "
			SELECT
			-- select
				*
			-- end select
			FROM
			-- from
				$sql
			-- end from
			ORDER BY 
			-- order by
				Person_Surname, Person_Firname, Person_Secname 
			-- end order by";
		} else {
			return false;
		}

		//echo getDebugSql($sql, $queryParams);exit;
		//echo getDebugSql(getLimitSQLPH($sql, $data['start'], $data['limit']), $queryParams);exit;
		if (!empty($data['print']) && ($data['print'] == true)) { // если список для печати, то надо печатать весь список, а не только 100 записей
			$result = $this->db->query($sql, $queryParams);
			if (is_object($result))
			{
				//return $result->result('array');
				$response = array();
				$response['data'] =  $result->result('array');
				return $response;
			}
			
			return false;
		}
		
		$count = 0;
		// Отдельно для количества 
		if (!empty($data['onlyCount']) && ($data['onlyCount'] == true)) {
			$result_count = $this->db->query(getCountSQLPH($sql), $queryParams);
			if (is_object($result_count)) {
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			}
			return $count;
		}
		
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $queryParams);
		
		if (is_object($result)) {
			$res = $result->result('array');
			// Если количество записей запроса равно limit, то, скорее всего еще есть страницы и каунт надо посчитать
			if (count($res)==$data['limit']) {
				// определение общего количества записей
				$result_count = $this->db->query(getCountSQLPH($sql), $queryParams);
				if (is_object($result_count)) {
					$cnt_arr = $result_count->result('array');
					$count = $cnt_arr[0]['cnt'];
					unset($cnt_arr);
				} else {
					$count = 0;
				}
			} else { // Иначе считаем каунт по реальному количеству + start
				$count = $data['start'] + count($res);
			}
			$response = array();
			$response['totalCount'] = $count;
			$response['data'] =  $res;
			return $response;
		} else {
			return false;
		}
	}
}
