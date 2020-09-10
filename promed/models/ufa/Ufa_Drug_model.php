<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'models/Drug_model.php');

class Ufa_Drug_model extends Drug_model {
	/**
	 * construct
	 */
	function __construct() {
		//parent::__construct();
            parent::__construct();
	}

	/**
	* Получение списка остатков по медикаментам по выбранной аптеке
	* Старый метод
	*/
	function getDrugOstatByFarmacyGrid_old($data) {
		$mnn_filter = "";
		$queryParams = array();
		$torg_filter = "";
		$filter = "";
		$lpu = '';
                
		//  Получаем данные сессии
		$sp = getSessionParams();

		if ( isset($data['mnn']) )  {
			$filter .= "  AND DrugMnn.DrugMnn_Name like :Mnn";
			$queryParams['Mnn'] = $data['mnn'].'%';
		}
		
		//  вытаскиваем ЛПУ из сессии 
		if (isset($sp['Lpu_id'])) {                  
			//echo '<pre>' . print_r('Lpu_id =' .$sp['Lpu_id'], 1) . '</pre>'; exit;
			$queryParams['Lpu_id'] = $sp['Lpu_id'];
			$lpu = "Set @Lpu_id = :Lpu_id;
					";                       
		};

		if ( isset($data['torg']) ) {
			$filter .= "  AND Drug.Drug_Name like :Torg";
			$queryParams['Torg'] = $data['torg'].'%';
		}
		
		if ( isset($data['WhsDocumentCostItemType_id']) ) {
				$filter .= " and drugostat.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
				$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
			}

		$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];

		// UPD: 2009-11-10 by Savage
		// Закомментировал строки, относящиеся к резерву, ибо некторые строки в результате двоились
		$sql = " 
			SELECT
				1 as DrugOstat_id,
				farm.OrgFarmacy_id,
				drugostat.WhsDocumentCostItemType_id,
				Drug.Drug_id,
				Drug.Drug_Name,
				Drug.Drug_CodeG,
				DrugMnn.DrugMnn_Name,
				sum(case when drugostat.WhsDocumentCostItemType_id = 1 then drugostat.DrugOstatRegistry_Kolvo end) as DrugOstat_Fed,
				sum(case when drugostat.WhsDocumentCostItemType_id = 2 then drugostat.DrugOstatRegistry_Kolvo end) as DrugOstat_Reg,
				sum(case when drugostat.WhsDocumentCostItemType_id = 3 then drugostat.DrugOstatRegistry_Kolvo end) as DrugOstat_7Noz,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 101 then drugostat.DrugOstatRegistry_Kolvo end) as DrugOstat_Dializ
				,  dus.DocumentUcStr_godnDate
				, Case  --  Устанавливаем 'Критичность' срока годности
					when dus.DocumentUcStr_godnDate IS Null
						then 1        
					when DATEADD(m, -3, dus.DocumentUcStr_godnDate) < GETDATE ()
						Then 1
					else 0	
				end GodnDate_Ctrl
				--STR(0, 18, 2) as DrugOstat_7Noz
				--null DrugOstat_7Noz
			FROM v_DrugOstatRegistry drugostat with (NOLOCK) 
				inner JOIN v_Drug Drug with (nolock) on drugostat.Drug_did = Drug.Drug_id
				inner join OrgFarmacy farm  with (nolock) on drugostat.Org_id = farm.Org_id
                                --  отбираем позиции, досупные для МО
                                Outer apply (  
                                    Select distinct 1 idx, lpu_id, 
                                    case  when drugostat.Storage_id is null then null else storage_id end storage_id
                                     from  OrgFarmacyIndex OrgFarmacyIndex with (nolock)
                                                                            where OrgFarmacyIndex.OrgFarmacy_id = farm.OrgFarmacy_id 
                                                                            and ( OrgFarmacyIndex.Lpu_id is null or OrgFarmacyIndex.Lpu_id = @Lpu_id) 
                                                                            and ISNULL(OrgFarmacyIndex_deleted, 1) = 1
                                                                                                            ) ofix
				LEFT JOIN v_DrugMnn DrugMnn with (nolock) on Drug.DrugMnn_id = DrugMnn.DrugMnn_id
				outer apply (
					select top 1 
						s.DrugFinance_id 
						from dbo.DocumentUcStr s with(nolock)
							where  s.Drug_id = drugostat.Drug_did
					) s
				cross apply(Select top 1 dus.DocumentUcStr_id, dus.DocumentUcStr_Ser, dus.DocumentUcStr_godnDate from documentUcStr dus  with(nolock)

						inner join v_DrugShipmentLink ln with(nolock) on ln.DrugShipment_id = drugostat.DrugShipment_id
							and ln.DocumentUcStr_id = dus.DocumentUcStr_id
						where dus.Drug_id = drugostat.Drug_did
					) dus	
			WHERE
				farm.OrgFarmacy_id=@OrgFarmacy_id
				and isnull(drugostat.SubAccountType_id, 1) in (1, 4)
                                and ( isnull(drugostat.storage_id, 0) = isnull (ofix.storage_id, 0) or isnull(drugostat.storage_id, 0) = 0)
				and drugostat.WhsDocumentCostItemType_id in (1, 2, 3, 101)
				" . $filter . "
			GROUP BY
				farm.OrgFarmacy_id,
				Drug.Drug_id,
				Drug.Drug_Name,
				Drug.Drug_CodeG,
				DrugMnn.DrugMnn_Name,
				dus.DocumentUcStr_godnDate,
				drugostat.WhsDocumentCostItemType_id
			HAVING sum(drugostat.DrugOstatRegistry_Kolvo) > 0
			--ORDER BY Drug.Drug_Name 
		";
	    
	    $sql = " 
			Declare @Lpu_id bigint,
			@OrgFarmacy_id bigint,
			@Dt datetime;
			Set @Dt = DATEADD(day, -10, getdate());  -- Дата 10 дней до текущего дня 
			Set @OrgFarmacy_id = :OrgFarmacy_id;
			{$lpu}
		
	    with t as (
		{$sql}
		    ),
		   er as (
				Select OrgFarmacy_id, WhsDocumentCostItemType_id, Drug_id, sum(EvnRecept_Kolvo) Reserve_Kolvo from v_EvnRecept er  with(nolock)	
					where  er.EvnRecept_setDate >= @Dt 	
						and ReceptDelayType_id is null
						and er.lpu_id = @Lpu_id
						and OrgFarmacy_id = @OrgFarmacy_id
					Group by OrgFarmacy_id, Drug_id, WhsDocumentCostItemType_id
				)

			select 
				t.OrgFarmacy_id,
				t.Drug_id,
				t.Drug_Name,
				t.Drug_CodeG,
				t.DrugMnn_Name,
				case 
						when isnull(er.Reserve_Kolvo, 0) > (isnull(t.DrugOstat_Fed, 0) + isnull(t.DrugOstat_Reg, 0) + isnull(t.DrugOstat_7Noz, 0) + isnull(t.DrugOstat_Dializ, 0))
							then NULL
					else
						STR(isnull(t.DrugOstat_Fed, 0) + isnull(t.DrugOstat_Reg, 0) + isnull(t.DrugOstat_7Noz, 0) + isnull(t.DrugOstat_Dializ, 0) - isnull(er.Reserve_Kolvo, 0), 18, 2)
				end as  DrugOstat_all,
				convert(numeric (18, 2), er.Reserve_Kolvo) as Reserve_Kolvo,
				convert(numeric (18, 2), t.DrugOstat_Fed) as DrugOstat_Fed,
				convert(numeric (18, 2), t.DrugOstat_Reg) as DrugOstat_Reg,
				convert(numeric (18, 2), t.DrugOstat_7Noz) as DrugOstat_7Noz,
				convert(numeric (18, 2), t.DrugOstat_Dializ) as DrugOstat_Dializ,  
				convert(varchar, t.DocumentUcStr_godnDate, 104) DocumentUcStr_godnDate,
				convert(varchar, t.DocumentUcStr_godnDate, 104) DocumentUcStr_godnDate,
				--DocumentUcStr_godnDate,
				t.GodnDate_Ctrl
			
			from t 
			left join er	on er.Drug_id = t.Drug_id
				and er.WhsDocumentCostItemType_id = t.WhsDocumentCostItemType_id
			ORDER BY Drug_Name  
		    
		";
	    
		//echo getDebugSQL($sql,$queryParams);exit;
	    
		$res = $this->db->query($sql, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	* Получение списка остатков по медикаментам по выбранной аптеке
	*/
	function getDrugOstatByFarmacyGrid($data) {
		$mnn_filter = "";
		$queryParams = array();
		$torg_filter = "";
		$filter = "";
		$lpu = '';
                
		//  Получаем данные сессии
		$sp = getSessionParams();

		if ( isset($data['mnn']) )  {
			$filter .= "  AND DrugMnn.DrugMnn_Name like :Mnn";
			$queryParams['Mnn'] = $data['mnn'].'%';
		}
		
		//  вытаскиваем ЛПУ из сессии 
		if (isset($sp['Lpu_id'])) {                  
			//echo '<pre>' . print_r('Lpu_id =' .$sp['Lpu_id'], 1) . '</pre>'; exit;
			$queryParams['Lpu_id'] = $sp['Lpu_id'];
			$lpu = "Set @Lpu_id = :Lpu_id;
					";                       
		};

		if ( isset($data['torg']) ) {
			$filter .= "  AND Drug.Drug_Name like :Torg";
			$queryParams['Torg'] = $data['torg'].'%';
		}
		
		if ( isset($data['WhsDocumentCostItemType_id']) ) {
				$filter .= " and drugostat.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
				$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
			}

		$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];

		//  Расчет остатков с учетом выписанных рецептов	
		$sql_pay = "
			Declare
				@DrugOstatRegistry_id bigint,  
				@WhsDocumentCostItemType_id bigint, 
				@Drug_Name varchar (200), 
				@DrugOstat_all numeric (18, 2), 
				@Reserve_Kolvo numeric (18, 2),
				@RecReserve_Kolvo numeric (18, 2),
				@RecKolvo numeric (18, 2),
				@Kol int;
	 
			DECLARE cur CURSOR LOCAL SCROLL FOR  
				SELECT  DrugOstatRegistry_id,  WhsDocumentCostItemType_id, OrgFarmacy_id, Drug_Name, DrugOstat_all, Reserve_Kolvo FROM  #Ost
					ORDER BY OrgFarmacy_id, Drug_Name, godnDate --desc

				OPEN cur

				Set @DrugOstatRegistry_id = null;
				Set	@WhsDocumentCostItemType_id = null;
				Set	@OrgFarmacy_id = null;
				Set	@Drug_Name = null;
				Set	@DrugOstat_all = null;
				Set	@Reserve_Kolvo = null;
				
			 FETCH First FROM Cur INTO
				@DrugOstatRegistry_id,
				@WhsDocumentCostItemType_id,
				@OrgFarmacy_id,
				@Drug_Name,
				@DrugOstat_all,
				@Reserve_Kolvo;

			   WHILE @@FETCH_STATUS = 0 
				begin
					if isnull(@DrugOstat_all, 0) > 0
					begin
						Set @RecReserve_Kolvo = null;
						Set @RecKolvo = null;

						SElect @RecReserve_Kolvo = Reserve_Kolvo, @RecKolvo = Kolvo from #er 
							where OrgFarmacy_id = @OrgFarmacy_id
								and WhsDocumentCostItemType_id = @WhsDocumentCostItemType_id
								and Drug_Name = @Drug_Name
								and isnull(Reserve_Kolvo, 0) > 0;
								if @RecReserve_Kolvo is not null begin
									if @DrugOstat_all >= @RecReserve_Kolvo begin
										Set @DrugOstat_all = @DrugOstat_all - @RecReserve_Kolvo;
										Set @Reserve_Kolvo = @Reserve_Kolvo + @RecReserve_Kolvo;
										SEt @RecReserve_Kolvo = 0;
									end;
									else begin
										SElect @kol = Count(8) from #Ost
											where OrgFarmacy_id = @OrgFarmacy_id
												and WhsDocumentCostItemType_id = @WhsDocumentCostItemType_id
												and Drug_Name = @Drug_Name
												and isnull(@DrugOstat_all, 0) > 0
												and DrugOstatRegistry_id <> @DrugOstatRegistry_id;
											if @Kol > 0 begin
												Set @RecReserve_Kolvo = @RecReserve_Kolvo - @DrugOstat_all;
												Set @Reserve_Kolvo = @DrugOstat_all; 
												Set @DrugOstat_all = 0;
											end;
											else begin
												Set @Reserve_Kolvo = @Reserve_Kolvo + @RecReserve_Kolvo; 
												Set @RecReserve_Kolvo = 0;
												Set @DrugOstat_all = 0; 
											end;

										end;  
											if isnull(@RecReserve_Kolvo, 0) <> isnull( @RecKolvo, 0) begin

												update #er
													set Reserve_Kolvo = @RecReserve_Kolvo
													where OrgFarmacy_id = @OrgFarmacy_id
														and WhsDocumentCostItemType_id = @WhsDocumentCostItemType_id
														and Drug_Name = @Drug_Name;

												Update #ost	
													set Reserve_Kolvo = @Reserve_Kolvo,
														DrugOstat_all = @DrugOstat_all 
												where DrugOstatRegistry_id = @DrugOstatRegistry_id;	

											end;
								end; 

					end; 

					Set @DrugOstatRegistry_id = null;
					Set	@WhsDocumentCostItemType_id = null;
					Set	@OrgFarmacy_id = null;
					Set	@Drug_Name = null;
					Set	@DrugOstat_all = null;
					Set	@Reserve_Kolvo = null;
					FETCH NEXT FROM cur INTO 
						@DrugOstatRegistry_id,
						@WhsDocumentCostItemType_id,
						@OrgFarmacy_id,
						@Drug_Name,
						@DrugOstat_all,
						@Reserve_Kolvo;

				end;

				CLOSE Cur -- Закрываем курсор
				DEALLOCATE Cur -- Удаляем курсор

				select 
						t.DrugOstatRegistry_id,
						t.WhsDocumentCostItemType_id,
						t.OrgFarmacy_id,
						t.Drug_id,
						t.Drug_Name,
						t.Drug_CodeG,
						t.DrugMnn_Name,
						case when t.DrugOstat_all = 0 then  null else STR(t.DrugOstat_all, 10, 2) end  DrugOstat_all,
						case when t.Reserve_Kolvo = 0 then  null else STR(t.Reserve_Kolvo, 10, 2) end Reserve_Kolvo,
						
						case when t.DrugOstat_Fed = 0 then  null else STR(t.DrugOstat_Fed, 10, 2) end DrugOstat_Fed,
						case when t.DrugOstat_Reg = 0 then  null else STR(t.DrugOstat_Reg, 10, 2) end DrugOstat_Reg,
						case when t.DrugOstat_7Noz = 0 then  null else STR(t.DrugOstat_7Noz, 10, 2) end DrugOstat_7Noz,
						case when t.DrugOstat_Dializ = 0 then  null else STR(t.DrugOstat_Dializ, 10, 2) end DrugOstat_Dializ,
						case when t.DrugOstat_Vich = 0 then  null else STR(t.DrugOstat_Vich, 10, 2) end DrugOstat_Vich,
						case when t.DrugOstat_Gepatit = 0 then  null else STR(t.DrugOstat_Gepatit, 10, 2) end DrugOstat_Gepatit,
						case when t.DrugOstat_BSK = 0 then  null else STR(t.DrugOstat_BSK, 10, 2) end DrugOstat_BSK,
						/*
						t.DrugOstat_Fed,
						t.DrugOstat_Reg,
						t.DrugOstat_7Noz,
						t.DrugOstat_Dializ,
						*/
						t.DocumentUcStr_godnDate,
						t.GodnDate_Ctrl
					from #Ost t 
						ORDER BY Drug_Name, godnDate
			";
		
		$sql = " 
			SELECT
				max(drugostat.DrugOstatRegistry_id) DrugOstatRegistry_id,  --New
				1 as DrugOstat_id,
				farm.OrgFarmacy_id,
				drugostat.WhsDocumentCostItemType_id,
				Drug.Drug_id,
				Drug.Drug_Name,
				Drug.Drug_CodeG,
				DrugMnn.DrugMnn_Name,
				sum(case when drugostat.WhsDocumentCostItemType_id = 1 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_Fed,
				sum(case when drugostat.WhsDocumentCostItemType_id = 2 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_Reg,
				sum(case when drugostat.WhsDocumentCostItemType_id = 3 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_7Noz,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 101 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_Dializ,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 99 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_Vich,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 100 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_Gepatit,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 103 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_BSK
				,  dus.DocumentUcStr_godnDate
				, Case  --  Устанавливаем 'Критичность' срока годности
					when dus.DocumentUcStr_godnDate IS Null
						then 1        
					when DATEADD(m, -3, dus.DocumentUcStr_godnDate) < GETDATE ()
						Then 1
					else 0	
				end GodnDate_Ctrl
			FROM v_DrugOstatRegistry drugostat with (NOLOCK) 
				inner JOIN v_Drug Drug with (nolock) on drugostat.Drug_did = Drug.Drug_id
				inner join OrgFarmacy farm  with (nolock) on drugostat.Org_id = farm.Org_id
                                --  отбираем позиции, досупные для МО
                                Outer apply (  
                                    Select distinct 1 idx, lpu_id, 
                                    case  when drugostat.Storage_id is null then null else storage_id end storage_id
                                     from  OrgFarmacyIndex OrgFarmacyIndex with (nolock)
                                                                            where OrgFarmacyIndex.OrgFarmacy_id = farm.OrgFarmacy_id 
                                                                            and ( OrgFarmacyIndex.Lpu_id is null or OrgFarmacyIndex.Lpu_id = @Lpu_id) 
                                                                            and ISNULL(OrgFarmacyIndex_deleted, 1) = 1
                                                                                                            ) ofix
				LEFT JOIN v_DrugMnn DrugMnn with (nolock) on Drug.DrugMnn_id = DrugMnn.DrugMnn_id
				outer apply (
					select top 1 
						s.DrugFinance_id 
						from dbo.DocumentUcStr s with(nolock)
							where  s.Drug_id = drugostat.Drug_did
					) s
				outer apply(Select top 1 dus.DocumentUcStr_id, dus.DocumentUcStr_Ser, dus.DocumentUcStr_godnDate from documentUcStr dus  with(nolock)

						inner join v_DrugShipmentLink ln with(nolock) on ln.DrugShipment_id = drugostat.DrugShipment_id
							and ln.DocumentUcStr_id = dus.DocumentUcStr_id
						where dus.Drug_id = drugostat.Drug_did
					) dus	
			WHERE
				farm.OrgFarmacy_id=@OrgFarmacy_id
				and isnull(drugostat.SubAccountType_id, 1) in (1, 4)
                                and ( isnull(drugostat.storage_id, 0) = isnull (ofix.storage_id, 0) or isnull(drugostat.storage_id, 0) = 0)
				and drugostat.WhsDocumentCostItemType_id in (1, 2, 3, 99, 100, 101, 103)
				 {$filter}
			GROUP BY
				farm.OrgFarmacy_id,
				Drug.Drug_id,
				Drug.Drug_Name,
				Drug.Drug_CodeG,
				DrugMnn.DrugMnn_Name,
				dus.DocumentUcStr_godnDate,
				drugostat.WhsDocumentCostItemType_id
			HAVING sum(isnull(drugostat.DrugOstatRegistry_Kolvo, 0)) > 0
			";
	    
	    $sql = " 
			Declare @Lpu_id bigint,
			@OrgFarmacy_id bigint,
			@Dt datetime;
			Set @Dt = DATEADD(day, -10, getdate());  -- Дата 10 дней до текущего дня 
			Set @OrgFarmacy_id = :OrgFarmacy_id;
			{$lpu}
				
		set nocount on
		
		Select  OrgFarmacy_id, WhsDocumentCostItemType_id, er.Drug_id, dr.Drug_Name, sum(EvnRecept_Kolvo) Reserve_Kolvo, sum(EvnRecept_Kolvo) Kolvo
		into #er
		from v_EvnRecept er  with(nolock)	
					inner join v_Drug dr  with(nolock) on dr.Drug_id = er.Drug_id
					where  er.EvnRecept_setDate >= @Dt 	
						and er.lpu_id = @Lpu_id
						and OrgFarmacy_id = @OrgFarmacy_id
						and er.ReceptDelayType_id is null
					Group by OrgFarmacy_id, er.Drug_id, dr.Drug_Name, WhsDocumentCostItemType_id;
					
	    with t as (
		{$sql}
		    )
		
		select 
				t.DrugOstatRegistry_id,
				t.WhsDocumentCostItemType_id,
				t.OrgFarmacy_id,
				t.Drug_id,
				t.Drug_Name,
				t.Drug_CodeG,
				t.DrugMnn_Name,
				isnull(t.DrugOstat_Fed, 0) + isnull(t.DrugOstat_Reg, 0) + isnull(t.DrugOstat_7Noz, 0) + isnull(t.DrugOstat_Dializ, 0)
					 + isnull(t.DrugOstat_Vich, 0) + isnull(t.DrugOstat_Gepatit, 0) + isnull(t.DrugOstat_BSK, 0) DrugOstat_all,
				0 Reserve_Kolvo,
				convert(numeric (18, 2), t.DrugOstat_Fed) as DrugOstat_Fed,
				convert(numeric (18, 2), t.DrugOstat_Reg) as DrugOstat_Reg,
				convert(numeric (18, 2), t.DrugOstat_7Noz) as DrugOstat_7Noz,
				convert(numeric (18, 2), t.DrugOstat_Dializ) as DrugOstat_Dializ,  
				convert(numeric (18, 2), t.DrugOstat_Vich) as DrugOstat_Vich,  
				convert(numeric (18, 2), t.DrugOstat_Gepatit) as DrugOstat_Gepatit,  
				convert(numeric (18, 2), t.DrugOstat_BSK) as DrugOstat_BSK,  
				convert(varchar, t.DocumentUcStr_godnDate, 104) DocumentUcStr_godnDate,
				t.DocumentUcStr_godnDate godnDate,
				t.GodnDate_Ctrl
				into #Ost
			from t 
			
			{$sql_pay}
				
			drop table #er;
			drop table #ost
		    
		";
	    
		//echo getDebugSQL($sql,$queryParams);exit;
	    
		$res = $this->db->query($sql, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	* Получение списка открытых медикаментов
	*/
	function getDrugGrid($data) {
		$filter = "";
		$ostat_filter = "";
		$org_farm_filter = "";
		$queryParams = array();

		if ( isset($data['mnn']) ) {
			$filter .= " and dm.DrugMnn_Name like :mnn";
			$queryParams['mnn'] = $data['mnn'] . "%";
		}

		if ( isset($data['torg']) ) {
			$filter .= " and d.Drug_Name like :torg";
			$queryParams['torg'] = $data['torg'] . "%";
		}

		if ( isset($data['org_farm_filter']) ) {
			$org_farm_filter .= " and exists( select 1 from v_OrgFarmacy OrgF with (nolock) where OrgF.Orgfarmacy_Name like :org_farm_filter or OrgF.Orgfarmacy_HowGo like :org_farm_filter and OrgF.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id)";
			$queryParams['org_farm_filter'] = "%" . $data['org_farm_filter'] . "%";
		}

		if ( isset($data['WhsDocumentCostItemType_id']) ) {
			$ostat_filter = " and drugostat.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		
		
		//  Получаем данные сессии
		$sp = getSessionParams();
		$queryParams['Lpu_id'] = $sp['Lpu_id'];
		$ostat_filter = " and exists(
			SELECT
				Drug_did
			FROM
				v_DrugOstatRegistry drugostat with (nolock)
                                inner join OrgFarmacy farm  with (nolock) on drugostat.Org_id = farm.Org_id
				INNER JOIN v_OrgFarmacy OrgFarmacy with (nolock) on farm.OrgFarmacy_id=OrgFarmacy.OrgFarmacy_id  " . $org_farm_filter . "
				    Outer apply (  
                                    Select distinct 1 idx, lpu_id, OrgFarmacy_Name,
                                        case  when drugostat.Storage_id is null then null else OrgFarmacyIndex.storage_id end storage_id 
                                    from  OrgFarmacyIndex OrgFarmacyIndex with (nolock)
                                                                            where OrgFarmacyIndex.OrgFarmacy_id = farm.OrgFarmacy_id 
                                                                            and ( OrgFarmacyIndex.Lpu_id is null or OrgFarmacyIndex.Lpu_id = :Lpu_id) 
                                                                            and ISNULL(OrgFarmacyIndex_deleted, 1) = 1
									    ) ofix 
				    
			WHERE
				drugostat.DrugOstatRegistry_kolvo > 0 and d.Drug_id = drugostat.Drug_did
				and isnull(drugostat.SubAccountType_id, 1) in (1, 4)
				and ( isnull(drugostat.storage_id, 0) = isnull (ofix.storage_id, 0) or isnull(drugostat.storage_id, 0) = 0) 
				{$ostat_filter}
		)";
		
		

		
		$sql = "
			SELECT 
			-- select
				max(d.Drug_id) as Drug_id , dm.DrugMnn_Name, d.Drug_Name--, d.Drug_CodeG
			-- end select
			FROM
				-- from
				v_Drug d with (nolock)
				INNER JOIN v_DrugMnn dm with (nolock) ON dm.DrugMnn_id = d.DrugMnn_id
			-- end from
			WHERE 
			-- where
			1 = 1 " . $filter . $ostat_filter . " 
				group by DrugMnn_Name, Drug_Name
			-- end where
			--group by
			-- group by
			--DrugMnn_Name, Drug_Name
			-- end group by 
			order by 
				-- order by 
				DrugMnn_Name, Drug_Name 
				-- end order by 
		";
		$count_sql = getCountSQLPH($sql);
		if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
		}
		//echo getDebugSQL($sql,$queryParams);exit;
		$result = $this->db->query($sql, $queryParams);

		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			// Если количество записей запроса равно limit, то, скорее всего еще есть страницы и каунт надо посчитать
			if (count($response['data'])==$data['limit']) {
				// считаем каунт запроса по БД
				$result_count = $this->db->query($count_sql, $queryParams);
				if (is_object($result_count)) {
					$cnt_arr = $result_count->result('array');
					$count = $cnt_arr[0]['cnt'];
					unset($cnt_arr);
				} else {
					$count = 0;
				}
			} else { // Иначе считаем каунт по реальному количеству + start
				$count = $data['start'] + count($response['data']);
			}
			$response['totalCount'] = $count;
			return $response;
		} else
			return false;
	}
     
	/**
	* Еще одно получение списка аптек с остатками по выбранному медикаменту
	* Нельзя ли все в одну функцию сделать?
	*/
	function getDrugOstatGrid($data) {
		$filter = "";
		$queryParams = array();

		if ( isset($data['org_farm_filter']) ) {
			//$filter .= " and ( drugostat.OrgFarmacy_Name like :OrgFarmacyFilter or drugostat.OrgFarmacy_HowGo like :OrgFarmacyFilter )";
			$filter .= " and ( ofix.OrgFarmacy_Name like :OrgFarmacyFilter)";
			$queryParams['OrgFarmacyFilter'] = '%' . $data['org_farm_filter'] . '%';
		}

		$queryParams['Drug_id'] = $data['Drug_id'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		
		$sql_pay = "	
			Declare
				 @DrugOstatRegistry_id bigint,  
				 @WhsDocumentCostItemType_id bigint, 
				 @OrgFarmacy_id bigint,  
				 @DrugOstat_all numeric (18, 2), 
				 @Reserve_Kolvo numeric (18, 2),
				 @RecReserve_Kolvo numeric (18, 2),
				 @RecKolvo numeric (18, 2),
				 @Kol int; 	

				DECLARE cur CURSOR LOCAL SCROLL FOR  
				SELECT  DrugOstatRegistry_id,  WhsDocumentCostItemType_id, OrgFarmacy_id, Drug_Name, DrugOstat_all, Reserve_Kolvo FROM  #Ost
					ORDER BY OrgFarmacy_id, Drug_Name, godnDate --desc

				OPEN cur

				Set @DrugOstatRegistry_id = null;
				Set	@WhsDocumentCostItemType_id = null;
				Set	@OrgFarmacy_id = null;
				Set	@Drug_Name = null;
				Set	@DrugOstat_all = null;
				Set	@Reserve_Kolvo = null;

				 FETCH First FROM Cur INTO
					@DrugOstatRegistry_id,
					@WhsDocumentCostItemType_id,
					@OrgFarmacy_id,
					@Drug_Name,
					@DrugOstat_all,
					@Reserve_Kolvo;

				   WHILE @@FETCH_STATUS = 0 
					begin
						if isnull(@DrugOstat_all, 0) > 0
						begin
							Set @RecReserve_Kolvo = null;
							Set @RecKolvo = null;

							SElect @RecReserve_Kolvo = Reserve_Kolvo, @RecKolvo = Kolvo from #er 
								where OrgFarmacy_id = @OrgFarmacy_id
									and WhsDocumentCostItemType_id = @WhsDocumentCostItemType_id
									and Drug_Name = @Drug_Name
									and isnull(Reserve_Kolvo, 0) > 0;
									if @RecReserve_Kolvo is not null begin
										if @DrugOstat_all >= @RecReserve_Kolvo begin
											Set @DrugOstat_all = @DrugOstat_all - @RecReserve_Kolvo;
											Set @Reserve_Kolvo = @Reserve_Kolvo + @RecReserve_Kolvo;
											SEt @RecReserve_Kolvo = 0;
										end;
										else begin
											SElect @kol = Count(8) from #Ost
												where OrgFarmacy_id = @OrgFarmacy_id
													and WhsDocumentCostItemType_id = @WhsDocumentCostItemType_id
													and Drug_Name = @Drug_Name
													and isnull(@DrugOstat_all, 0) > 0
													and DrugOstatRegistry_id <> @DrugOstatRegistry_id;
												if @Kol > 0 begin
													Set @RecReserve_Kolvo = @RecReserve_Kolvo - @DrugOstat_all;
													Set @Reserve_Kolvo = @DrugOstat_all; 
													Set @DrugOstat_all = 0;
												end;
												else begin
													Set @Reserve_Kolvo = @Reserve_Kolvo + @RecReserve_Kolvo; 
													Set @RecReserve_Kolvo = 0;
													Set @DrugOstat_all = 0; 
												end;

											end;  
												if isnull(@RecReserve_Kolvo, 0) <> isnull( @RecKolvo, 0) begin

													update #er
														set Reserve_Kolvo = @RecReserve_Kolvo
														where OrgFarmacy_id = @OrgFarmacy_id
															and WhsDocumentCostItemType_id = @WhsDocumentCostItemType_id
															and Drug_Name = @Drug_Name;

													Update #ost	
														set Reserve_Kolvo = @Reserve_Kolvo,
															DrugOstat_all = @DrugOstat_all 
													where DrugOstatRegistry_id = @DrugOstatRegistry_id;	

												end;
									end; 

						end; 

						Set @DrugOstatRegistry_id = null;
						Set	@WhsDocumentCostItemType_id = null;
						Set	@OrgFarmacy_id = null;
						Set	@Drug_Name = null;
						Set	@DrugOstat_all = null;
						Set	@Reserve_Kolvo = null;
						FETCH NEXT FROM cur INTO 
							@DrugOstatRegistry_id,
							@WhsDocumentCostItemType_id,
							@OrgFarmacy_id,
							@Drug_Name,
							@DrugOstat_all,
							@Reserve_Kolvo;

					end;

					CLOSE Cur -- Закрываем курсор
					DEALLOCATE Cur -- Удаляем курсор

					select 
							t.DrugOstatRegistry_id,
							t.WhsDocumentCostItemType_id,
							t.OrgFarmacy_id,
							t.OrgFarmacy_Name,
							Storage_id,
							Lpu_Nick,
							t.OrgFarmacy_HowGo,
							t.Drug_id,
							t.Drug_Name,
							case when t.DrugOstat_all = 0 then  null else STR(t.DrugOstat_all, 10, 2) end  DrugOstat_all,
							case when t.Reserve_Kolvo = 0 then  null else STR(t.Reserve_Kolvo, 10, 2) end Reserve_Kolvo,
							case when t.DrugOstat_Fed = 0 then  null else STR(t.DrugOstat_Fed, 10, 2) end DrugOstat_Fed,
							case when t.DrugOstat_Reg = 0 then  null else STR(t.DrugOstat_Reg, 10, 2) end DrugOstat_Reg,
							case when t.DrugOstat_7Noz = 0 then  null else STR(t.DrugOstat_7Noz, 10, 2) end DrugOstat_7Noz,
							case when t.DrugOstat_Dializ = 0 then  null else STR(t.DrugOstat_Dializ, 10, 2) end DrugOstat_Dializ, 
							case when t.DrugOstat_Vich = 0 then  null else STR(t.DrugOstat_Vich, 10, 2) end DrugOstat_Vich,
							case when t.DrugOstat_Gepatit = 0 then  null else STR(t.DrugOstat_Gepatit, 10, 2) end DrugOstat_Gepatit,
							case when t.DrugOstat_BSK = 0 then  null else STR(t.DrugOstat_BSK, 10, 2) end DrugOstat_BSK,
							t.DocumentUcStr_godnDate,
							t.GodnDate_Ctrl,
							t.OrgFarmacy_IsVkl
						from #Ost t 
							ORDER BY  OrgFarmacy_Name, godnDate
			";
		
		$sql = "
			SELECT
				1 as DrugOstat_id,
				max(drugostat.DrugOstatRegistry_id) DrugOstatRegistry_id,
				farm.OrgFarmacy_id,
				farm.OrgFarmacy_Name,
				drugostat.WhsDocumentCostItemType_id,
				ofix.Storage_id, 
				case
					when ofix.Storage_id IS null
						then null
				else 
					Lpu_Nick
				end Lpu_Nick,
				ISNULL(RTRIM(farm.OrgFarmacy_HowGo),'Адрес аптеки не указан') as OrgFarmacy_HowGo,
				max(drugostat.Drug_did) Drug_did,
				dr.Drug_Name,
				sum(case when drugostat.WhsDocumentCostItemType_id = 1 then drugostat.DrugOstatRegistry_Kolvo end) as DrugOstat_Fed,
				sum(case when drugostat.WhsDocumentCostItemType_id = 2 then drugostat.DrugOstatRegistry_Kolvo end) as DrugOstat_Reg,
				sum(case when drugostat.WhsDocumentCostItemType_id = 3 then drugostat.DrugOstatRegistry_Kolvo end) as DrugOstat_7Noz,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 101 then drugostat.DrugOstatRegistry_Kolvo end) as DrugOstat_Dializ,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 99 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_Vich,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 100 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_Gepatit,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 103 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_BSK,
                CASE WHEN ofix.idx IS Not null  THEN 'true' ELSE 'false' END as OrgFarmacy_IsVkl
		, convert(varchar, dus.DocumentUcStr_godnDate, 104) DocumentUcStr_godnDate
		, dus.DocumentUcStr_godnDate godnDate
		, Case  --  Устанавливаем 'Критичность' срока годности
			when dus.DocumentUcStr_godnDate IS Null
				then 1        
			when DATEADD(m, -3, dus.DocumentUcStr_godnDate) < GETDATE ()
				Then 1
			else 0	
		end GodnDate_Ctrl
			FROM v_DrugOstatRegistry drugostat with (nolock)
				inner join v_Drug dr with (nolock) on drugostat.Drug_did = dr.Drug_id
					and dr.Drug_Name = @Drug_Name
				inner join v_OrgFarmacy farm  with (nolock) on drugostat.Org_id = farm.Org_id
                                Outer apply (  
                                    Select distinct 1 idx, lpu_id, OrgFarmacy_Name,
                                        case  when drugostat.Storage_id is null then null else OrgFarmacyIndex.storage_id end storage_id 
                                    from  OrgFarmacyIndex OrgFarmacyIndex with (nolock)
                                                                            where OrgFarmacyIndex.OrgFarmacy_id = farm.OrgFarmacy_id 
                                                                            and ( OrgFarmacyIndex.Lpu_id is null or OrgFarmacyIndex.Lpu_id = @Lpu_id) 
                                                                            and ISNULL(OrgFarmacyIndex_deleted, 1) = 1
                                                                                                            ) ofix        
                                left join v_Lpu lpu  with (nolock) on lpu.lpu_id = ofix.lpu_id         
				outer apply (
					select top 1 
						s.DrugFinance_id 
						from dbo.DocumentUcStr s with(nolock)
							where  s.Drug_id = drugostat.Drug_did
					) s	
				cross apply(Select top 1 dus.DocumentUcStr_id, dus.DocumentUcStr_Ser, dus.DocumentUcStr_godnDate from documentUcStr dus  with(nolock)

						inner join v_DrugShipmentLink ln with(nolock) on ln.DrugShipment_id = drugostat.DrugShipment_id
							and ln.DocumentUcStr_id = dus.DocumentUcStr_id
						where dus.Drug_id = drugostat.Drug_did
					) dus	
			WHERE 
				 isnull(drugostat.SubAccountType_id, 1) in (1, 4)
                  and ( isnull(drugostat.storage_id, 0) = isnull (ofix.storage_id, 0) or isnull(drugostat.storage_id, 0) = 0)
				and drugostat.WhsDocumentCostItemType_id in (1, 2, 3, 99, 100, 101, 103)
				{$filter}
			GROUP BY
				farm.OrgFarmacy_id,
				farm.OrgFarmacy_Name,
				farm.OrgFarmacy_HowGo,
				drugostat.Drug_did,
				ofix.idx,
				ofix.Storage_id,
				lpu.Lpu_Nick,
				dus.DocumentUcStr_godnDate,
				drugostat.WhsDocumentCostItemType_id,
				dr.Drug_Name

			HAVING sum(drugostat.DrugOstatRegistry_Kolvo) > 0
			";
                
		$sql = "
		    Declare
			@Lpu_id bigint = :Lpu_id,
			@Drug_id bigint = :Drug_id,
			@Drug_Name varchar(200),
			@Dt datetime;
			
			set nocount on

		    Set @Dt = DATEADD(day, -10, getdate());  -- Дата 10 дней до текущего дня  
			Select @Drug_Name = Drug_Name from v_Drug dr with(nolock) where Drug_id = @Drug_id;
			
			Select OrgFarmacy_id, WhsDocumentCostItemType_id, max(er.Drug_id) Drug_id, dr.Drug_Name, sum(EvnRecept_Kolvo) Reserve_Kolvo, sum(EvnRecept_Kolvo) Kolvo 
				into #er
				from v_EvnRecept er  with(nolock)	
					inner join v_drug dr with(nolock) on er.Drug_id = dr.Drug_id
						and dr.Drug_Name = @Drug_Name
					where  er.EvnRecept_setDate >= @Dt 	
						and ReceptDelayType_id is null
						and er.lpu_id = @Lpu_id
					Group by OrgFarmacy_id, dr.Drug_Name, WhsDocumentCostItemType_id;
		    
		    with t as (
			{$sql}   
			)
			SElect 
				t.DrugOstat_id,
				t.DrugOstatRegistry_id,
				t.WhsDocumentCostItemType_id,
				t.OrgFarmacy_id,
				t.OrgFarmacy_Name,
				t.Storage_id, 
				t.Lpu_Nick,
				t.OrgFarmacy_HowGo,
				t.Drug_did Drug_id,
				t.Drug_Name,
				isnull(t.DrugOstat_Fed, 0) + isnull(t.DrugOstat_Reg, 0) + isnull(t.DrugOstat_7Noz, 0) + isnull(t.DrugOstat_Dializ, 0)
					+ isnull(t.DrugOstat_Vich, 0) + isnull(t.DrugOstat_Gepatit, 0) + isnull(t.DrugOstat_BSK, 0) DrugOstat_all,
				--convert(numeric (18, 2), er.Reserve_Kolvo) as Reserve_Kolvo,
				0 Reserve_Kolvo,
				convert(numeric (18, 2), t.DrugOstat_Fed) as DrugOstat_Fed,
				convert(numeric (18, 2), t.DrugOstat_Reg) as DrugOstat_Reg,
				convert(numeric (18, 2), t.DrugOstat_7Noz) as DrugOstat_7Noz,
				convert(numeric (18, 2), t.DrugOstat_Dializ) as DrugOstat_Dializ,
				convert(numeric (18, 2), t.DrugOstat_Vich) as DrugOstat_Vich,  
				convert(numeric (18, 2), t.DrugOstat_Gepatit) as DrugOstat_Gepatit,  
				convert(numeric (18, 2), t.DrugOstat_BSK) as DrugOstat_BSK,  
				t.OrgFarmacy_IsVkl,
				t.DocumentUcStr_godnDate,
				t.godnDate,
				t.GodnDate_Ctrl
				into #ost
			 from t 
			ORDER BY
				t.OrgFarmacy_Name;
				
				{$sql_pay}
					
				drop table #er;
				drop table #ost;
			";
            //   echo getDebugSQL($sql,$queryParams);exit;
                
		$res = $this->db->query($sql, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Сохранение записи о признании рецепта недействительным
	 */
    public function saveReceptWrong($data) {
		$this->beginTransaction();

		$this->load->model('Dlo_EvnRecept_model', 'ermodel');
		$this->isAllowTransaction = false;
		$resp = $this->ermodel->deleteEvnReceptDrugOstReg($data);
		$this->isAllowTransaction = true;
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$queryParams = array();
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000),
				@JournalMantu_id BIGINT;

			exec [dbo].[p_ReceptWrong_ins]
				@ReceptWrong_id       = :ReceptWrong_id, -- Идентификатор записи
				@EvnRecept_id         = :EvnRecept_id,  -- Идентификатор рецепта
				@OrgFarmacy_id        = :OrgFarmacy_id, --  Индификатор аптека
				@Org_id               = :Org_id, -- идентификатор организации (аптеки)
				@ReceptWrong_decr     = :ReceptWrong_decr, -- Причина отказа
				@pmUser_id            = :pmUser_id, -- ID пользователя, который назначил прививку
				@Error_Code           = @ErrCode output,    -- Код ошибки
				@Error_Message        = @ErrMessage output -- Тект ошибки


			select @ErrCode as Error_Code, @ErrMessage as Error_Msg
		";

		$queryParams['ReceptWrong_id'] = $data['ReceptWrong_id'];
		$queryParams['EvnRecept_id'] = $data['EvnRecept_id'];
		$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
		$queryParams['Org_id'] = $data['Org_id'];
		$queryParams['ReceptWrong_decr'] = $data['ReceptWrong_decr'];
		$queryParams['pmUser_id'] = $_SESSION['pmuser_id'];

		$response = $this->queryResult($query, $queryParams);

		if ( !is_array($response) ) {
			$this->rollbackTransaction();
			return $this->createError('', 'Ошибка при выполнении запроса к базе данных (Сохранение записи о признании рецепта недействительным)');
		}
		if ( !empty($response[0]['Error_Msg']) ) {
			$this->rollbackTransaction();
			return $response;
		}

		$this->commitTransaction();

		return $response;
    }
    
    /**
     * Загрузка записи о признании рецепта недействительным
     */
    public function loadReceptWrongInfo($data) {
		$queryParams = array();

		$query = "
		  Declare
				@EvnRecept_id bigint = :EvnRecept_id;

		SELECT TOP 1 ReceptWrong_id
			  ,EvnRecept_id
			  ,Org_id
			  ,ReceptWrong_Decr
		  FROM dbo.ReceptWrong
				where EvnRecept_id = @EvnRecept_id
		";

		$queryParams['EvnRecept_id'] = $data['EvnRecept_id'];

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (доп инфа для формы медотводов)'));
		}
     }

     
	/**
	* Получение полного списка аптек
	*/
	function getOrgFarmacyGrid($data) {
		$filter = "";
		$join = "";
		$queryParams = array();

		if ( isset($data['orgfarm']) ) {
			$filter .= " and (ofr.OrgFarmacy_Name like :OrgFarmacy_Name or ofr.OrgFarmacy_HowGo like :OrgFarmacy_Name)";
			$queryParams['OrgFarmacy_Name'] = "%" . $data['orgfarm'] . "%";
		}
		
		if ( isset($data['OrgFarmacys']) ) {
			$filter .= " and (ofr.OrgFarmacy_id in (:OrgFarmacyList))";
			$queryParams['OrgFarmacyList'] = $data['OrgFarmacys'];
		} 
		
		if (isset($data['LLO_program'])) {
			$filter .= " and (isnull(ofix.WhsDocumentCostItemType_id, 0) = :LLO_program)";
			$queryParams['LLO_program'] = $data['LLO_program'];
		}

		if ( (isset($data['mnn'])) || (isset($data['torg'])) || (isset($data['WhsDocumentCostItemType_id']))  ) {
			$join .= "
				--inner join v_DrugOstat drugostat_mnn with (nolock) on drugostat_mnn.OrgFarmacy_id = ofr.OrgFarmacy_id
				--	and DrugOstat_Kolvo > 0
                                inner join v_DrugOstatRegistry drugostat_mnn with (nolock) on drugostat_mnn.Org_id = ofr.Org_id
                                    and DrugOstatRegistry_Kolvo > 0
				inner join v_Drug drug1 on drugostat_mnn.Drug_did = drug1.Drug_id
					and isnull(drug1.Drug_IsDel, 1) = 1
			";

			if ( isset($data['torg']) ) {
				$filter .= " and drug1.Drug_Name like :Drug_Name";
				$queryParams['Drug_Name'] = $data['torg'] . "%";
			}

			if ( isset($data['mnn']) ) {
				$join .= "
					inner join v_DrugMnn drugmnn1 on drug1.DrugMnn_id = drugmnn1.DrugMnn_id
						and drugmnn1.DrugMnn_Name like :DrugMnn_Name
				";
				$queryParams['DrugMnn_Name'] = $data['mnn'] . "%";
			}
			
			if ( isset($data['WhsDocumentCostItemType_id']) ) {
				$filter .= " and drugostat_mnn.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
				$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
			}
		}
		
		

		$queryParams['Lpu_id'] = $data['Lpu_id'];
                /*
		$query = "
			SELECT DISTINCT
				ofr.OrgFarmacy_id,
				ofr.Org_id,
				ofr.OrgFarmacy_Code,
				ofr.OrgFarmacy_Name,
				ofr.OrgFarmacy_HowGo,
				(case when isnull(ofix.OrgFarmacyIndex_id, 1) = 1 then 0 else 1 end) as OrgFarmacy_Vkl,
				(case when isnull(ofix.OrgFarmacyIndex_id, 1) = 1 then 'false' else 'true' end) as OrgFarmacy_IsVkl,
				ofix.OrgFarmacyIndex_Index,
				ofix.OrgFarmacyIndex_id
			FROM v_OrgFarmacy ofr with (nolock) 
				left join v_OrgFarmacyIndex ofix with (nolock) on ofr.OrgFarmacy_id = ofix.OrgFarmacy_id
					and ofix.Lpu_id = :Lpu_id
				" . $join . "
			WHERE (1 = 1) 
				-- and OrgFarmacy_IsFarmacy = 2
				" . $filter . "
		";
                */
                $query  = "
                  SELECT 
				ofr.OrgFarmacy_id,
				ofr.Org_id,
				ofr.OrgFarmacy_Code,
				ofr.OrgFarmacy_Name,
                                case when isnull(ofr.OrgFarmacy_IsNarko, 1) = 1 then 'false' else 'true' end OrgFarmacy_IsNarko,
				--bil.LpuBuilding_Name,
			--	, bil.LpuBuilding_Name for xml path('')
				(select  bil.LpuBuilding_Name + '$ ' 
				FROM v_OrgFarmacy ofr2 with (nolock) 
				left join OrgFarmacyIndex ofix2 with (nolock) on ofr2.OrgFarmacy_id = ofix2.OrgFarmacy_id and ISNULL(ofix2.OrgFarmacyIndex_deleted, 1) = 1
				left join LpuBuilding bil with (nolock) on bil.LpuBuilding_id = ofix2.LpuBuilding_id
				where ofr2.Org_id = ofr.Org_id and ofix2.Lpu_id = ofix.Lpu_id
					and isnull(ofix2.WhsDocumentCostItemType_id, 0) = isnull(ofix.WhsDocumentCostItemType_id, 0)
				order by bil.LpuBuilding_Name
				for xml path('')) LpuBuilding_Name,
				ofr.OrgFarmacy_HowGo,
				(case when isnull(ofix.OrgFarmacyIndex_id, 1) = 1 then 0 else 1 end) as OrgFarmacy_Vkl,
				(case when isnull(ofix.OrgFarmacyIndex_id, 1) = 1 then 'false' else 'true' end) as OrgFarmacy_IsVkl,
				max(ofix.OrgFarmacyIndex_Index) OrgFarmacyIndex_Index,
				min(ofix.OrgFarmacyIndex_id) OrgFarmacyIndex_id,
				--ofix.storage_id,
				ofix.Lpu_id,
				ofix.WhsDocumentCostItemType_id,
				isnull(wdc.WhsDocumentCostItemType_Name, 'Все' + 
					case when isnull(Nowdc.WhsDocumentCostItemType_name, '') = '' then '' else ', кроме ' + substring(Nowdc.WhsDocumentCostItemType_name, 1, len(Nowdc.WhsDocumentCostItemType_name) - 1) end
				) WhsDocumentCostItemType_Name
			FROM v_OrgFarmacy ofr with (nolock) 
				left join v_OrgFarmacyIndex ofix with (nolock) on ofr.OrgFarmacy_id = ofix.OrgFarmacy_id
                                    and ofix.Lpu_id = :Lpu_id
				left join v_WhsDocumentCostItemType wdc with (nolock) on wdc.WhsDocumentCostItemType_id = ofix.WhsDocumentCostItemType_id
				outer apply (  SElect
					 (SELECT WhsDocumentCostItemType_name + ', ' FROM  
						(Select distinct WhsDocumentCostItemType_name from v_OrgFarmacyIndex ofix 
							join  WhsDocumentCostItemType wdc on wdc.WhsDocumentCostItemType_id = ofix.WhsDocumentCostItemType_id
							WHERE ofix.WhsDocumentCostItemType_id is not null) wdc FOR XML PATH(''))  WhsDocumentCostItemType_name
				) Nowdc
                                " . $join . "
			WHERE (1 = 1) 
                            " . $filter . "
			
				group by ofr.OrgFarmacy_id,
					ofr.Org_id,
					ofr.OrgFarmacy_Code,
					ofr.OrgFarmacy_Name,
                                        ofr.OrgFarmacy_IsNarko,
					ofix.Lpu_id,
					ofr.OrgFarmacy_HowGo,
				(case when isnull(ofix.OrgFarmacyIndex_id, 1) = 1 then 0 else 1 end),
				(case when isnull(ofix.OrgFarmacyIndex_id, 1) = 1 then 'false' else 'true' end),
				--ofix.storage_id,
				ofix.Lpu_id,
				ofix.WhsDocumentCostItemType_id,
				wdc.WhsDocumentCostItemType_name, Nowdc.WhsDocumentCostItemType_name
                ";
                
                 $query  = "
                     SElect 
				t.OrgFarmacyIndex_id,
				t.OrgFarmacyIndex_Index,
				t.OrgFarmacy_id,
				t.Org_id,
				t.OrgFarmacy_Code,
				t.OrgFarmacy_Name, 
				t.OrgFarmacy_HowGo,
				t.OrgFarmacy_Vkl,
				t.OrgFarmacy_IsVkl,
				--t.OrgFarmacyIndex_Index,
				--t.OrgFarmacyIndex_id,
				--t.storage_id,
                                t.OrgFarmacy_IsNarko,
				t.Lpu_id,
				case
					when OrgFarmacy_Vkl = 1 and len(LpuBuilding_Name) = 0
						then 'Все подразделения' 
				else 
					replace (Substring(T.LpuBuilding_Name,0,Len(T.LpuBuilding_Name)), '$',  '<br />')
				end	 LpuBuilding_Name,
				t.WhsDocumentCostItemType_id,
				case when OrgFarmacy_Vkl = 1  then WhsDocumentCostItemType_Name else null end WhsDocumentCostItemType_Name

from (" .$query ."
    )t "; 
				 
		if ( isset($data['typeList']) && $data['typeList'] == 'Остатки')  {
			$query = "
					with farm as (
					{$query}
						)
			Select 
				OrgFarmacy_id,
				Org_id,
				OrgFarmacy_Code,
				OrgFarmacy_Name, 
				OrgFarmacy_HowGo,
				max(OrgFarmacyIndex_Index) OrgFarmacyIndex_Index,
				OrgFarmacy_Vkl,
				OrgFarmacy_IsVkl
			from farm
			group by OrgFarmacy_id,
					Org_id,
					OrgFarmacy_Code,
					OrgFarmacy_Name, 
					OrgFarmacy_HowGo,
					OrgFarmacy_Vkl,
					OrgFarmacy_IsVkl
			order by OrgFarmacy_Vkl desc, OrgFarmacy_Name
				";
					
		}
			

                //echo getDebugSQL($query,$queryParams);exit;
                 
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	
    /**
	 * Загрузка списка МНН
	*/
	function loadDrugMnnList($data, $options) {
		$queryParams = array();
		$table       = '';
		$filter      = '';
                $vznFld      = '';
                $vznJoin     = '';

		$recept_drug_ostat_control = $options['recept_drug_ostat_control'];

		switch ( $data['mode'] ) {
			case 'any':
				$data['query'] = $data['query'] . "%";
			break;

			case 'start':
				$data['query'] .= "%";
			break;
		}
                //echo 'DrugMnn_id = ' .$data['DrugMnn_id'];
		if ( isset($data['DrugMnn_id']) ) {
			$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
			$table = "v_Drug";
                       
			$filter .= " and Drug.DrugMnn_id = :DrugMnn_id";
			
			$query = "
				SELECT DISTINCT
					DrugMnn.DrugMnn_id,
					DrugMnn.DrugMnn_Code,
					RTRIM(DrugMnn.DrugMnn_Name) as DrugMnn_Name,
                                        case when vzn.DrugMnn_id is not null then 1 else 0 end vzn
				FROM " . $table . " Drug with (nolock)
					inner join v_DrugMnn DrugMnn with (nolock) on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
                                        left join SicknessDrug vzn with (nolock) on vzn.DrugMnn_id = Drug.DrugMnn_id
				WHERE (1 = 1)
					" . $filter . "
				ORDER BY RTRIM(DrugMnn.DrugMnn_Name) 
			";
		}
		else {
			if ( $data['EvnRecept_Is7Noz_Code'] == 1 ) {
				$table = "v_Drug7noz";
                                $vznFld = "1 vzn";
			}
			else {
				$vznFld = "case when vzn.DrugMnn_id is not null then 1 else 0 end vzn ";
				$vznJoin = 'left join SicknessDrug vzn with (nolock) on vzn.DrugMnn_id = Drug.DrugMnn_id';
				if ( $data['ReceptFinance_Code'] == 1 ) {
					//$table = "r2.v_DrugFedMnn";
					$table = "v_DrugFedMnn";
				}
				else {
					$table = "v_DrugRegMnn";
				}

				if ($data['ReceptType_Code'] != 1 && $recept_drug_ostat_control &&$data['session']['region']['nick'] != 'ufa' ) {
					// Контроль остатков только по РАС
					$filter .= " and exists (
						select 1
						from v_DrugOstat DrugOstat with (nolock)
							inner join v_OrgFarmacy OrgFarmacy with (nolock) on OrgFarmacy.OrgFarmacy_id = DrugOstat.OrgFarmacy_id
								and isnull(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
							inner join v_Org Org with (nolock) on Org.Org_id = OrgFarmacy.Org_id
							inner join v_ReceptFinance ReceptFinance with (nolock) on ReceptFinance.ReceptFinance_id = DrugOstat.ReceptFinance_id
							left join v_DrugReserv DrugReserv with (nolock) on DrugOstat.Drug_id = DrugReserv.Drug_id
								and DrugOstat.OrgFarmacy_id = DrugReserv.OrgFarmacy_id
								and DrugOstat.ReceptFinance_id = DrugReserv.ReceptFinance_id
						where
							DrugOstat.DrugOstat_Kolvo>0
							and DrugOstat.DrugOstat_Kolvo>isnull(DrugReserv.DrugReserv_Kolvo,0)
							and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
							and DrugOstat.Drug_id = Drug.Drug_id
					 )
					";
					$queryParams['Lpu_id'] = $data['Lpu_id'];
				}
			}

			$queryParams['Date'] = $data['Date'];
			$queryParams['query'] = $data['query'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];

			$filter .= " and Drug.Drug_begDate < :Date";
			$filter .= " and (Drug.Drug_endDate is null or Drug.Drug_endDate > :Date)";
			$filter .= " and Drug.DrugMnn_Name LIKE :query";

			$query = "
				SELECT DISTINCT
					Drug.DrugMnn_id,
					Drug.DrugMnn_Code,
					RTRIM(Drug.DrugMnn_Name) as DrugMnn_Name,
                                       {$vznFld}
				FROM " . $table . " Drug with (nolock)
                                    {$vznJoin}
				WHERE (1 = 1)
					" . $filter . "
				ORDER BY RTRIM(Drug.DrugMnn_Name) 
			";
		}

					
                //echo getDebugSQL($query,$queryParams);exit;
                
                //$dbrep = $this->load->database('bdwork', true);
                $dbrep = $this->db;
                
                //$result = $this->db->query($query, $queryParams);
                  
		$result = $dbrep->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
        
 
}        