<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH . 'models/_pgsql/Drug_model.php');

class Ufa_Drug_model extends Drug_model
{
	/**
	 * construct
	 */
	function __construct()
	{
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

		//  Получаем данные сессии
		$sp = getSessionParams();

		if (isset($data['mnn'])) {
			$filter .= "  AND DrugMnn.DrugMnn_Name ilike :Mnn";
			$queryParams['Mnn'] = $data['mnn'] . '%';
		}

		//  вытаскиваем ЛПУ из сессии
		if (isset($sp['Lpu_id'])) {
			//echo '<pre>' . print_r('Lpu_id =' .$sp['Lpu_id'], 1) . '</pre>'; exit;
			$queryParams['Lpu_id'] = $sp['Lpu_id'];
		};

		if (isset($data['torg'])) {
			$filter .= "  AND Drug.Drug_Name ilike :Torg";
			$queryParams['Torg'] = $data['torg'] . '%';
		}

		if (isset($data['WhsDocumentCostItemType_id'])) {
			$filter .= " and drugostat.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}

		$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];

		// UPD: 2009-11-10 by Savage
		// Закомментировал строки, относящиеся к резерву, ибо некторые строки в результате двоились
		$sql = "
			SELECT
				1 as \"DrugOstat_id\",
				farm.OrgFarmacy_id as \"OrgFarmacy_id\",
				drugostat.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				Drug.Drug_id as \"Drug_id\",
				Drug.Drug_Name as \"Drug_Name\",
				Drug.Drug_CodeG as \"Drug_CodeG\",
				DrugMnn.DrugMnn_Name as \"DrugMnn_Name\",
				sum(case when drugostat.WhsDocumentCostItemType_id = 1 then drugostat.DrugOstatRegistry_Kolvo end) as \"DrugOstat_Fed\",
				sum(case when drugostat.WhsDocumentCostItemType_id = 2 then drugostat.DrugOstatRegistry_Kolvo end) as \"DrugOstat_Reg\",
				sum(case when drugostat.WhsDocumentCostItemType_id = 3 then drugostat.DrugOstatRegistry_Kolvo end) as \"DrugOstat_7Noz\",
				Sum(case when drugostat.WhsDocumentCostItemType_id = 101 then drugostat.DrugOstatRegistry_Kolvo end) as \"DrugOstat_Dializ\"
				,  dus.DocumentUcStr_godnDate as \"DocumentUcStr_godnDate\"
				, Case  --  Устанавливаем 'Критичность' срока годности
					when dus.DocumentUcStr_godnDate IS Null
						then 1
					when DATEADD('month', -3, dus.DocumentUcStr_godnDate) < GETDATE ()
						Then 1
					else 0
				end \"GodnDate_Ctrl\"
				--STR(0, 18, 2) as DrugOstat_7Noz
				--null DrugOstat_7Noz
			FROM v_DrugOstatRegistry drugostat
				inner JOIN v_Drug Drug on drugostat.Drug_did = Drug.Drug_id
				inner join OrgFarmacy farm on drugostat.Org_id = farm.Org_id
				--  отбираем позиции, досупные для МО
				left join lateral (
					Select distinct 1 idx, lpu_id,
					case  when drugostat.Storage_id is null then null else storage_id end storage_id
					from  OrgFarmacyIndex OrgFarmacyIndex
					where OrgFarmacyIndex.OrgFarmacy_id = farm.OrgFarmacy_id
					and ( OrgFarmacyIndex.Lpu_id is null or OrgFarmacyIndex.Lpu_id = :Lpu_id)
					and COALESCE(OrgFarmacyIndex_deleted, 1) = 1
				) ofix on true
				LEFT JOIN v_DrugMnn DrugMnn on Drug.DrugMnn_id = DrugMnn.DrugMnn_id
				left join lateral (
					select
					s.DrugFinance_id
					from dbo.DocumentUcStr s
					where  s.Drug_id = drugostat.Drug_did
					limit 1
				) s on true
				left join lateral(
					Select dus.DocumentUcStr_id, dus.DocumentUcStr_Ser, dus.DocumentUcStr_godnDate
					from documentUcStr dus
					inner join v_DrugShipmentLink ln on ln.DrugShipment_id = drugostat.DrugShipment_id
						and ln.DocumentUcStr_id = dus.DocumentUcStr_id
					where dus.Drug_id = drugostat.Drug_did
					limit 1
				) dus on true
			WHERE
				farm.OrgFarmacy_id=:OrgFarmacy_id
				and COALESCE(drugostat.SubAccountType_id, 1) in (1, 4)
				and ( COALESCE(drugostat.storage_id, 0) = COALESCE (ofix.storage_id, 0) or COALESCE(drugostat.storage_id, 0) = 0)
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

	    with t as (
		{$sql}
		    ),
		   er as (
			Select OrgFarmacy_id, WhsDocumentCostItemType_id, Drug_id, sum(EvnRecept_Kolvo) Reserve_Kolvo from v_EvnRecept er
				where  er.EvnRecept_setDate >= DATEADD('day', -10, getdate())
					and ReceptDelayType_id is null
					and er.lpu_id = :Lpu_id
					and OrgFarmacy_id = :OrgFarmacy_id
				Group by OrgFarmacy_id, Drug_id, WhsDocumentCostItemType_id
			)

			select
				t.OrgFarmacy_id as \"OrgFarmacy_id\",
				t.Drug_id as \"Drug_id\",
				t.Drug_Name as \"Drug_Name\",
				t.Drug_CodeG as \"Drug_CodeG\",
				t.DrugMnn_Name as \"DrugMnn_Name\",
					case
						when COALESCE(er.Reserve_Kolvo, 0) > (COALESCE(t.DrugOstat_Fed, 0) + COALESCE(t.DrugOstat_Reg, 0) + COALESCE(t.DrugOstat_7Noz, 0) + COALESCE(t.DrugOstat_Spec, 0))
							then NULL
					else
						STR(COALESCE(t.DrugOstat_Fed, 0) + COALESCE(t.DrugOstat_Reg, 0) + COALESCE(t.DrugOstat_7Noz, 0) + COALESCE(t.DrugOstat_Spec, 0) - COALESCE(er.Reserve_Kolvo, 0), 18, 2)
				end
				 as \"DrugOstat_all\",
				cast(er.Reserve_Kolvo as numeric(18, 2)) as \"Reserve_Kolvo\",
				cast(t.DrugOstat_Fed as numeric(18, 2)) as \"DrugOstat_Fed\",
				cast(t.DrugOstat_Reg as numeric(18, 2)) as \"DrugOstat_Reg\",
				cast(t.DrugOstat_7Noz as numeric(18, 2)) as \"DrugOstat_7Noz\",
				cast(t.DrugOstat_Dializ as numeric(18, 2)) as \"DrugOstat_Dializ\",
				to_char(t.DocumentUcStr_godnDate, 'dd.mm.yyyy') as \"DocumentUcStr_godnDate\"
				--DocumentUcStr_godnDate,
				t.GodnDate_Ctrl as \"GodnDate_Ctrl\"

			from t
			left join er on er.Drug_id = t.Drug_id
				and er.WhsDocumentCostItemType_id = t.WhsDocumentCostItemType_id
			ORDER BY Drug_Name

		";

		//echo getDebugSQL($sql,$queryParams);exit;

		$res = $this->db->query($sql, $queryParams);

		if (is_object($res)) {
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
		$queryParams = array('Lpu_id' => null);
		$torg_filter = "";
		$filter = "";

		//  Получаем данные сессии
		$sp = getSessionParams();

		if (isset($data['mnn']))  {
			$filter .= "  AND DrugMnn.DrugMnn_Name ilike :Mnn";
			$queryParams['Mnn'] = $data['mnn'].'%';
		}

		//  вытаскиваем ЛПУ из сессии
		if (isset($sp['Lpu_id'])) {
			//echo '<pre>' . print_r('Lpu_id =' .$sp['Lpu_id'], 1) . '</pre>'; exit;
			$queryParams['Lpu_id'] = $sp['Lpu_id'];
		};

		if ( isset($data['torg']) ) {
			$filter .= "  AND Drug.Drug_Name ilike :Torg";
			$queryParams['Torg'] = $data['torg'].'%';
		}

		if ( isset($data['WhsDocumentCostItemType_id']) ) {
			$filter .= " and drugostat.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}

		$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];

		//  Расчет остатков с учетом выписанных рецептов
		$sql_pay = "
			CREATE OR REPLACE FUNCTION pg_temp.exp_Query()
            LANGUAGE 'plpgsql' 
            AS $$
            DECLARE
				p_DrugOstatRegistry_id bigint;
				p_WhsDocumentCostItemType_id bigint;
				p_Drug_Name varchar(200);
				p_DrugOstat_all numeric(18, 2);
				p_Reserve_Kolvo numeric(18, 2);
				p_RecReserve_Kolvo numeric(18, 2);
				p_RecKolvo numeric (18, 2);
				p_Kol int;
				cur SCROLL CURSOR FOR
				SELECT DrugOstatRegistry_id, WhsDocumentCostItemType_id, OrgFarmacy_id, Drug_Name, DrugOstat_all, Reserve_Kolvo FROM Ost
					ORDER BY OrgFarmacy_id, Drug_Name, godnDate;
            BEGIN
				OPEN cur;
				LOOP
					FETCH cur INTO (
						p_DrugOstatRegistry_id,
						p_WhsDocumentCostItemType_id,
						p_OrgFarmacy_id,
						p_Drug_Name,
						p_DrugOstat_all,
						p_Reserve_Kolvo
					);
					EXIT WHEN NOT FOUND;
					
					p_RecReserve_Kolvo := null;
					p_RecKolvo := null;

					select Reserve_Kolvo, Kolvo
					into (p_RecReserve_Kolvo, p_RecKolvo)
					from er
					where
						OrgFarmacy_id = p_OrgFarmacy_id
						and WhsDocumentCostItemType_id = p_WhsDocumentCostItemType_id
						and Drug_Name = p_Drug_Name
						and coalesce(Reserve_Kolvo, 0) > 0;
						
					if p_RecReserve_Kolvo is not null then
						if p_DrugOstat_all >= p_RecReserve_Kolvo then
							p_DrugOstat_all := p_DrugOstat_all - p_RecReserve_Kolvo;
							p_Reserve_Kolvo := p_Reserve_Kolvo + p_RecReserve_Kolvo;
							p_RecReserve_Kolvo := 0;
						else
							select Count(8) from Ost
							into p_Kol
							where
								OrgFarmacy_id = p_OrgFarmacy_id
								and WhsDocumentCostItemType_id = p_WhsDocumentCostItemType_id
								and Drug_Name = p_Drug_Name
								and coalesce(p_DrugOstat_all, 0) > 0
								and DrugOstatRegistry_id <> p_DrugOstatRegistry_id;
									
							if p_Kol > 0 then
								p_RecReserve_Kolvo := p_RecReserve_Kolvo - p_DrugOstat_all;
								p_Reserve_Kolvo := p_DrugOstat_all;
								p_DrugOstat_all := 0;
							else
								p_Reserve_Kolvo := p_Reserve_Kolvo + p_RecReserve_Kolvo;
								p_RecReserve_Kolvo := 0;
								p_DrugOstat_all := 0;
							end if;
						end if;
						
						if coalesce(p_RecReserve_Kolvo, 0) <> coalesce(p_RecKolvo, 0) then
							update er
							set Reserve_Kolvo = p_RecReserve_Kolvo
							where OrgFarmacy_id = p_OrgFarmacy_id
							and WhsDocumentCostItemType_id = p_WhsDocumentCostItemType_id
							and Drug_Name = p_Drug_Name;
							
							update ost
							set Reserve_Kolvo = p_Reserve_Kolvo,
							DrugOstat_all = p_DrugOstat_all
							where DrugOstatRegistry_id = p_DrugOstatRegistry_id;
						end if;
					end if;
				END LOOP;
            END;
            $$;
			
			select pg_temp.exp_Query();
			
			select
				t.DrugOstatRegistry_id as \"DrugOstatRegistry_id\",
				t.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				t.OrgFarmacy_id as \"OrgFarmacy_id\",
				t.Drug_id as \"Drug_id\",
				t.Drug_Name as \"Drug_Name\",
				t.Drug_CodeG as \"Drug_CodeG\",
				t.DrugMnn_Name as \"DrugMnn_Name\",
				case when t.DrugOstat_all = 0 then  null else STR(t.DrugOstat_all, 10, 2) end  \"DrugOstat_all\",
				case when t.Reserve_Kolvo = 0 then  null else STR(t.Reserve_Kolvo, 10, 2) end \"Reserve_Kolvo\",
				case when t.DrugOstat_Fed = 0 then  null else STR(t.DrugOstat_Fed, 10, 2) end \"DrugOstat_Fed\",
				case when t.DrugOstat_Reg = 0 then  null else STR(t.DrugOstat_Reg, 10, 2) end \"DrugOstat_Reg\",
				case when t.DrugOstat_7Noz = 0 then  null else STR(t.DrugOstat_7Noz, 10, 2) end \"DrugOstat_7Noz\",
				case when t.DrugOstat_Dializ = 0 then  null else STR(t.DrugOstat_Dializ, 10, 2) end \"DrugOstat_Dializ\",
				case when t.DrugOstat_Vich = 0 then  null else STR(t.DrugOstat_Vich, 10, 2) end DrugOstat_Vich,
				case when t.DrugOstat_Gepatit = 0 then  null else STR(t.DrugOstat_Gepatit, 10, 2) end DrugOstat_Gepatit,
				case when t.DrugOstat_BSK = 0 then  null else STR(t.DrugOstat_BSK, 10, 2) end DrugOstat_BSK,
				/*
				t.DrugOstat_Fed as \"DrugOstat_Fed\",
				t.DrugOstat_Reg as \"DrugOstat_Reg\",
				t.DrugOstat_7Noz as \"DrugOstat_7Noz\",
				t.DrugOstat_Dializ as \"DrugOstat_Dializ\",
				*/
				t.DocumentUcStr_godnDate as \"DocumentUcStr_godnDate\",
				t.GodnDate_Ctrl as \"GodnDate_Ctrl\"
			from Ost t
			ORDER BY Drug_Name, godnDate;
		";

		$sql = "
			SELECT
				max(drugostat.DrugOstatRegistry_id) as DrugOstatRegistry_id,  --New
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
				,dus.DocumentUcStr_godnDate
				,Case  --  Устанавливаем 'Критичность' срока годности
					when dus.DocumentUcStr_godnDate IS Null
						then 1
					when DATEADD('month', -3, dus.DocumentUcStr_godnDate) < GETDATE()
						Then 1
					else 0
				end as GodnDate_Ctrl
			FROM
				v_DrugOstatRegistry drugostat
				inner JOIN v_Drug Drug on drugostat.Drug_did = Drug.Drug_id
				inner join OrgFarmacy farm on drugostat.Org_id = farm.Org_id
				--  отбираем позиции, досупные для МО
				left join lateral (
					select distinct 1 as idx, lpu_id,
					case when drugostat.Storage_id is null then null else storage_id end as storage_id
					from OrgFarmacyIndex OrgFarmacyIndex
					where OrgFarmacyIndex.OrgFarmacy_id = farm.OrgFarmacy_id
					and (OrgFarmacyIndex.Lpu_id is null or OrgFarmacyIndex.Lpu_id = :Lpu_id)
					and coalesce(OrgFarmacyIndex_deleted, 1) = 1
				) ofix on true
				LEFT JOIN v_DrugMnn DrugMnn on Drug.DrugMnn_id = DrugMnn.DrugMnn_id
				left join lateral (
					select s.DrugFinance_id
					from dbo.DocumentUcStr s
					where  s.Drug_id = drugostat.Drug_did
					limit 1
				) s on true
				left join lateral (
					select dus.DocumentUcStr_id, dus.DocumentUcStr_Ser, dus.DocumentUcStr_godnDate
					from documentUcStr dus
					inner join v_DrugShipmentLink ln on ln.DrugShipment_id = drugostat.DrugShipment_id
						and ln.DocumentUcStr_id = dus.DocumentUcStr_id
					where dus.Drug_id = drugostat.Drug_did
					limit 1
				) dus
			WHERE
				farm.OrgFarmacy_id = :OrgFarmacy_id
				and coalesce(drugostat.SubAccountType_id, 1) in (1, 4)
				and (coalesce(drugostat.storage_id, 0) = coalesce(ofix.storage_id, 0) or coalesce(drugostat.storage_id, 0) = 0)
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
			HAVING
				sum(coalesce(drugostat.DrugOstatRegistry_Kolvo, 0)) > 0
		";

	    $sql = "
			create temp table er as
			select
				OrgFarmacy_id,
				WhsDocumentCostItemType_id,
				EvnRecept.Drug_id,
				dr.Drug_Name,
				sum(EvnRecept_Kolvo) as Reserve_Kolvo,
				sum(EvnRecept_Kolvo) as Kolvo
			from
				v_EvnRecept EvnRecept
				inner join v_Drug dr on dr.Drug_id = EvnRecept.Drug_id
			where
				EvnRecept.EvnRecept_setDate >= DATEADD('day', -10, getdate())
				and EvnRecept.lpu_id = :Lpu_id
				and OrgFarmacy_id = :OrgFarmacy_id
			Group by OrgFarmacy_id, EvnRecept.Drug_id, dr.Drug_Name, WhsDocumentCostItemType_id;

			create temp table Ost as
			select
				t.DrugOstatRegistry_id,
				t.WhsDocumentCostItemType_id,
				t.OrgFarmacy_id,
				t.Drug_id,
				t.Drug_Name,
				t.Drug_CodeG,
				t.DrugMnn_Name,
				COALESCE(t.DrugOstat_Fed, 0) + COALESCE(t.DrugOstat_Reg, 0) + COALESCE(t.DrugOstat_7Noz, 0) + COALESCE(t.DrugOstat_Dializ, 0)
					 + COALESCE(t.DrugOstat_Vich, 0) + COALESCE(t.DrugOstat_Gepatit, 0) + COALESCE(t.DrugOstat_BSK, 0) DrugOstat_all,
				0 as Reserve_Kolvo,
				cast(t.DrugOstat_Fed as numeric (18, 2)) as DrugOstat_Fed,
				cast(t.DrugOstat_Reg as numeric(18, 2)) as DrugOstat_Reg,
				cast(t.DrugOstat_7Noz as numeric(18, 2)) as DrugOstat_7Noz,
				cast(t.DrugOstat_Dializ as numeric(18, 2)) as DrugOstat_Dializ,				
				cast(t.DrugOstat_Vich as numeric(18, 2)) as DrugOstat_Vich,
				cast(t.DrugOstat_Gepatit as numeric(18, 2)) as DrugOstat_Gepatit,
				cast(t.DrugOstat_BSK as numeric(18, 2)) as DrugOstat_BSK,
				to_char(t.DocumentUcStr_godnDate, 'DD.MM.YYYY') as DocumentUcStr_godnDate,
				t.DocumentUcStr_godnDate as godnDate,
				t.GodnDate_Ctrl
			from (
				{$sql}
			) t

			{$sql_pay}

			drop table er;
			drop table ost;
		";

		//echo getDebugSQL($sql,$queryParams);exit;

		$res = $this->db->query($sql, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка открытых медикаментов
	 */
	function getDrugGrid($data)
	{
		$filter = "";
		$ostat_filter = "";
		$org_farm_filter = "";
		$queryParams = array();

		if (isset($data['mnn'])) {
			$filter .= " and dm.DrugMnn_Name ilike :mnn";
			$queryParams['mnn'] = $data['mnn'] . "%";
		}

		if (isset($data['torg'])) {
			$filter .= " and d.Drug_Name ilike :torg";
			$queryParams['torg'] = $data['torg'] . "%";
		}

		if (isset($data['org_farm_filter'])) {
			$org_farm_filter .= " and exists( select 1 from v_OrgFarmacy OrgF where OrgF.Orgfarmacy_Name ilike :org_farm_filter or OrgF.Orgfarmacy_HowGo like :org_farm_filter and OrgF.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id)";
			$queryParams['org_farm_filter'] = "%" . $data['org_farm_filter'] . "%";
		}

		if (isset($data['WhsDocumentCostItemType_id'])) {
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
				v_DrugOstatRegistry drugostat
				inner join OrgFarmacy farm  on drugostat.Org_id = farm.Org_id
				INNER JOIN v_OrgFarmacy OrgFarmacy on farm.OrgFarmacy_id=OrgFarmacy.OrgFarmacy_id  " . $org_farm_filter . "
				left join lateral (
					Select distinct 1 idx, lpu_id, OrgFarmacy_Name,
					case  when drugostat.Storage_id is null then null else OrgFarmacyIndex.storage_id end storage_id
					from  OrgFarmacyIndex OrgFarmacyIndex
					where OrgFarmacyIndex.OrgFarmacy_id = farm.OrgFarmacy_id
					and ( OrgFarmacyIndex.Lpu_id is null or OrgFarmacyIndex.Lpu_id = :Lpu_id)
					and COALESCE(OrgFarmacyIndex_deleted, 1) = 1
				) ofix on true

			WHERE
				drugostat.DrugOstatRegistry_kolvo > 0 and d.Drug_id = drugostat.Drug_did
				and COALESCE(drugostat.SubAccountType_id, 1) in (1, 4)
				and ( COALESCE(drugostat.storage_id, 0) = COALESCE (ofix.storage_id, 0) or COALESCE(drugostat.storage_id, 0) = 0)
				{$ostat_filter}
		)";


		$sql = "
			SELECT
			-- select
				max(d.Drug_id) as \"Drug_id\" ,
				dm.DrugMnn_Name as \"DrugMnn_Name\",
				d.Drug_Name as \"Drug_Name\"
			-- end select
			FROM
				-- from
				v_Drug d
				INNER JOIN v_DrugMnn dm ON dm.DrugMnn_id = d.DrugMnn_id
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
			if (count($response['data']) == $data['limit']) {
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
			$filter .= " and ( ofix.OrgFarmacy_Name ilike :OrgFarmacyFilter)";
			$queryParams['OrgFarmacyFilter'] = '%' . $data['org_farm_filter'] . '%';
		}

		$queryParams['Drug_id'] = $data['Drug_id'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		$sql_pay = "
			CREATE OR REPLACE FUNCTION pg_temp.exp_Query()
            LANGUAGE 'plpgsql' 
            AS $$
            DECLARE
				p_DrugOstatRegistry_id bigint;
				p_WhsDocumentCostItemType_id bigint;
				p_OrgFarmacy_id bigint;
				p_DrugOstat_all numeric(18, 2);
				p_Reserve_Kolvo numeric(18, 2);
				p_RecReserve_Kolvo numeric(18, 2);
				p_RecKolvo numeric(18, 2);
				p_Kol int;
				cur SCROLL CURSOR FOR
				SELECT DrugOstatRegistry_id, WhsDocumentCostItemType_id, OrgFarmacy_id, Drug_Name, DrugOstat_all, Reserve_Kolvo FROM Ost
					ORDER BY OrgFarmacy_id, Drug_Name, godnDate;
            BEGIN
				OPEN cur;
				LOOP
					FETCH cur INTO (
						p_DrugOstatRegistry_id,
						p_WhsDocumentCostItemType_id,
						p_OrgFarmacy_id,
						p_Drug_Name,
						p_DrugOstat_all,
						p_Reserve_Kolvo
					);
					EXIT WHEN NOT FOUND;
					
					if coalesce(p_DrugOstat_all, 0) > 0 then
						p_RecReserve_Kolvo := null;
						p_RecKolvo := null;

						select Reserve_Kolvo, Kolvo
						into (p_RecReserve_Kolvo, p_RecKolvo)
						from er
						where
							OrgFarmacy_id = p_OrgFarmacy_id
							and WhsDocumentCostItemType_id = p_WhsDocumentCostItemType_id
							and Drug_Name = p_Drug_Name
							and coalesce(Reserve_Kolvo, 0) > 0;
				
						if p_RecReserve_Kolvo is not null then
							if p_DrugOstat_all >= p_RecReserve_Kolvo then
								p_DrugOstat_all := p_DrugOstat_all - p_RecReserve_Kolvo;
								p_Reserve_Kolvo := p_Reserve_Kolvo + p_RecReserve_Kolvo;
								p_RecReserve_Kolvo := 0;
							else
								select Count(8)
								into p_kol
								from ost
								where
									OrgFarmacy_id = p_OrgFarmacy_id
									and WhsDocumentCostItemType_id = p_WhsDocumentCostItemType_id
									and Drug_Name = p_Drug_Name
									and coalesce(p_DrugOstat_all, 0) > 0
									and DrugOstatRegistry_id <> p_DrugOstatRegistry_id;
							end if;
									
							if p_Kol > 0 then
								p_RecReserve_Kolvo := p_RecReserve_Kolvo - p_DrugOstat_all;
								p_Reserve_Kolvo := p_DrugOstat_all;
								p_DrugOstat_all := 0;
							else
								p_Reserve_Kolvo := p_Reserve_Kolvo + p_RecReserve_Kolvo;
								p_RecReserve_Kolvo := 0;
								p_DrugOstat_all := 0;
							end if;
						end if;

						if coalesce(p_RecReserve_Kolvo, 0) <> coalesce(p_RecKolvo, 0) then
							update er
							set 
								Reserve_Kolvo = @RecReserve_Kolvo
							where
								OrgFarmacy_id = p_OrgFarmacy_id
								and WhsDocumentCostItemType_id = p_WhsDocumentCostItemType_id
								and Drug_Name = p_Drug_Name;
								
							update ost
							set
								Reserve_Kolvo = p_Reserve_Kolvo,
								DrugOstat_all = p_DrugOstat_all
							where
								DrugOstatRegistry_id = p_DrugOstatRegistry_id;	
						end if;
					end if;			
				END LOOP;
            END;
            $$;
            
            select pg_temp.exp_Query()

			select
				t.DrugOstatRegistry_id as \"DrugOstatRegistry_id\",
				t.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				t.OrgFarmacy_id as \"OrgFarmacy_id\",
				t.OrgFarmacy_Name as \"OrgFarmacy_Name\",
				Storage_id as \"Storage_id\",
				Lpu_Nick as \"Lpu_Nick\",
				t.OrgFarmacy_HowGo as \"OrgFarmacy_HowGo\",
				t.Drug_id as \"Drug_id\",
				t.Drug_Name as \"Drug_Name\",
				case when t.DrugOstat_all = 0 then  null else STR(t.DrugOstat_all, 10, 2) end as \"DrugOstat_all\",
				case when t.Reserve_Kolvo = 0 then  null else STR(t.Reserve_Kolvo, 10, 2) end as \"Reserve_Kolvo\",
				case when t.DrugOstat_Fed = 0 then  null else STR(t.DrugOstat_Fed, 10, 2) end as \"DrugOstat_Fed\",
				case when t.DrugOstat_Reg = 0 then  null else STR(t.DrugOstat_Reg, 10, 2) end as \"DrugOstat_Reg\",
				case when t.DrugOstat_7Noz = 0 then  null else STR(t.DrugOstat_7Noz, 10, 2) end as \"DrugOstat_7Noz\",
				case when t.DrugOstat_Dializ = 0 then  null else STR(t.DrugOstat_Dializ, 10, 2) end as \"DrugOstat_Dializ\",
				case when t.DrugOstat_Vich = 0 then  null else STR(t.DrugOstat_Vich, 10, 2) end as \"DrugOstat_Vich\",
				case when t.DrugOstat_Gepatit = 0 then  null else STR(t.DrugOstat_Gepatit, 10, 2) end as \"DrugOstat_Gepatit\",
				case when t.DrugOstat_BSK = 0 then  null else STR(t.DrugOstat_BSK, 10, 2) end as \"DrugOstat_BSK\",
				t.DocumentUcStr_godnDate as \"DocumentUcStr_godnDate\",
				t.GodnDate_Ctrl as \"GodnDate_Ctrl\",
				t.OrgFarmacy_IsVkl as \"OrgFarmacy_IsVkl\"
			from Ost t
			ORDER BY OrgFarmacy_Name, godnDate;
		";

		$sql = "
			SELECT
				1 as DrugOstat_id,
				max(drugostat.DrugOstatRegistry_id) as DrugOstatRegistry_id,
				farm.OrgFarmacy_id,
				farm.OrgFarmacy_Name,
				drugostat.WhsDocumentCostItemType_id,
				ofix.Storage_id,
				case
					when ofix.Storage_id IS null then null else Lpu_Nick
				end as Lpu_Nick,
				coalesce(RTRIM(farm.OrgFarmacy_HowGo),'Адрес аптеки не указан') as OrgFarmacy_HowGo,
				max(drugostat.Drug_did) Drug_did,
				dr.Drug_Name,
				sum(case when drugostat.WhsDocumentCostItemType_id = 1 then drugostat.DrugOstatRegistry_Kolvo end) as DrugOstat_Fed,
				sum(case when drugostat.WhsDocumentCostItemType_id = 2 then drugostat.DrugOstatRegistry_Kolvo end) as DrugOstat_Reg,
				sum(case when drugostat.WhsDocumentCostItemType_id = 3 then drugostat.DrugOstatRegistry_Kolvo end) as DrugOstat_7Noz,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 101 then drugostat.DrugOstatRegistry_Kolvo end) as DrugOstat_Dializ,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 99 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_Vich,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 100 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_Gepatit,
				Sum(case when drugostat.WhsDocumentCostItemType_id = 103 then drugostat.DrugOstatRegistry_Kolvo else 0 end) as DrugOstat_BSK,
                CASE WHEN ofix.idx IS Not null  THEN 'true' ELSE 'false' END as OrgFarmacy_IsVkl,
                to_char(dus.DocumentUcStr_godnDate, 'DD.MM.YYYY') as DocumentUcStr_godnDate,
				dus.DocumentUcStr_godnDate as godnDate,
				case  --  Устанавливаем 'Критичность' срока годности
					when dus.DocumentUcStr_godnDate IS Null then 1
					when DATEADD(m, -3, dus.DocumentUcStr_godnDate) < GETDATE () Then 1
					else 0
				end as GodnDate_Ctrl
			FROM
				v_DrugOstatRegistry drugostat
				inner join v_Drug dr on drugostat.Drug_did = dr.Drug_id
					and dr.Drug_Name = (select Drug_Name from v_Drug dr where Drug_id = :Drug_id)
				inner join v_OrgFarmacy farm on drugostat.Org_id = farm.Org_id
				left join lateral (
					select distinct 1 as idx, lpu_id, OrgFarmacy_Name,
					case  when drugostat.Storage_id is null then null else OrgFarmacyIndex.storage_id end storage_id
					from  OrgFarmacyIndex OrgFarmacyIndex
					where OrgFarmacyIndex.OrgFarmacy_id = farm.OrgFarmacy_id
					and (OrgFarmacyIndex.Lpu_id is null or OrgFarmacyIndex.Lpu_id = :Lpu_id)
					and coalesce(OrgFarmacyIndex_deleted, 1) = 1
				) ofix on true
                left join v_Lpu lpu on lpu.lpu_id = ofix.lpu_id
				left join lateral (
					select s.DrugFinance_id
					from dbo.DocumentUcStr s
					where s.Drug_id = drugostat.Drug_did
					limit 1
				) s on true
				inner join lateral (
					select dus.DocumentUcStr_id, dus.DocumentUcStr_Ser, dus.DocumentUcStr_godnDate 
					from documentUcStr dus
					inner join v_DrugShipmentLink ln on ln.DrugShipment_id = drugostat.DrugShipment_id
						and ln.DocumentUcStr_id = dus.DocumentUcStr_id
					where dus.Drug_id = drugostat.Drug_did
					limit 1
				) dus on true
			WHERE
				COALESCE(drugostat.SubAccountType_id, 1) in (1, 4)
                and ( COALESCE(drugostat.storage_id, 0) = COALESCE (ofix.storage_id, 0) or COALESCE(drugostat.storage_id, 0) = 0)
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
			HAVING
				sum(drugostat.DrugOstatRegistry_Kolvo) > 0
		";

		$sql = "
			create temp table er as
			select 
				OrgFarmacy_id,
				WhsDocumentCostItemType_id, 
				max(er.Drug_id) as Drug_id,
				dr.Drug_Name,
				sum(EvnRecept_Kolvo) as Reserve_Kolvo,
				sum(EvnRecept_Kolvo) as Kolvo
			into er
			from
				v_EvnRecept EvnRecept
				inner join v_drug dr on EvnRecept.Drug_id = dr.Drug_id 
					and dr.Drug_Name = (select Drug_Name from v_Drug dr where Drug_id = :Drug_id)
			where
				EvnRecept.EvnRecept_setDate >= DATEADD('day', -10, getdate())
				and ReceptDelayType_id is null
				and EvnRecept.lpu_id = :Lpu_id
			Group by OrgFarmacy_id, dr.Drug_Name, WhsDocumentCostItemType_id;

			create temp table ost as
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
				COALESCE(t.DrugOstat_Fed, 0) + COALESCE(t.DrugOstat_Reg, 0) + COALESCE(t.DrugOstat_7Noz, 0) + COALESCE(t.DrugOstat_Dializ, 0)
					+ COALESCE(t.DrugOstat_Vich, 0) + COALESCE(t.DrugOstat_Gepatit, 0) + COALESCE(t.DrugOstat_BSK, 0) as DrugOstat_all,
				0 as Reserve_Kolvo,
				cast(t.DrugOstat_Fed as numeric(18, 2)) as DrugOstat_Fed,
				cast(t.DrugOstat_Reg as numeric(18, 2)) as DrugOstat_Reg,
				cast(t.DrugOstat_7Noz as numeric(18, 2)) as DrugOstat_7Noz,
				cast(t.DrugOstat_Dializ as numeric(18, 2)) as DrugOstat_Dializ,
				cast(t.DrugOstat_Vich as numeric(18, 2)) as DrugOstat_Vich,
				cast(t.DrugOstat_Gepatit as numeric(18, 2)) as DrugOstat_Gepatit,
				cast(t.DrugOstat_BSK as numeric(18, 2)) as DrugOstat_BSK,
				t.OrgFarmacy_IsVkl,
				t.DocumentUcStr_godnDate,
				t.godnDate,
				t.GodnDate_Ctrl
			from (
				{$sql}
			) t
			ORDER BY
				t.OrgFarmacy_Name;

			{$sql_pay}

			drop table er;
			drop table ost;
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
	public function saveReceptWrong($data)
	{
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
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_ReceptWrong_ins (
				ReceptWrong_id       := :ReceptWrong_id, -- Идентификатор записи
				EvnRecept_id         := :EvnRecept_id,  -- Идентификатор рецепта
				OrgFarmacy_id        := :OrgFarmacy_id, --  Индификатор аптека
				Org_id               := :Org_id, -- идентификатор организации (аптеки)
				ReceptWrong_decr     := :ReceptWrong_decr, -- Причина отказа
				pmUser_id            := :pmUser_id
			);
		";

		$queryParams['ReceptWrong_id'] = $data['ReceptWrong_id'];
		$queryParams['EvnRecept_id'] = $data['EvnRecept_id'];
		$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
		$queryParams['Org_id'] = $data['Org_id'];
		$queryParams['ReceptWrong_decr'] = $data['ReceptWrong_decr'];
		$queryParams['pmUser_id'] = $_SESSION['pmuser_id'];

		$response = $this->queryResult($query, $queryParams);

		if (!is_array($response)) {
			$this->rollbackTransaction();
			return $this->createError('', 'Ошибка при выполнении запроса к базе данных (Сохранение записи о признании рецепта недействительным)');
		}
		if (!empty($response[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			return $response;
		}

		$this->commitTransaction();

		return $response;
	}

	/**
	 * Загрузка записи о признании рецепта недействительным
	 */
	public function loadReceptWrongInfo($data)
	{
		$queryParams = array();

		$query = "
		SELECT ReceptWrong_id as \"ReceptWrong_id\"
			  ,EvnRecept_id as \"EvnRecept_id\"
			  ,Org_id as \"Org_id\"
			  ,ReceptWrong_Decr as \"ReceptWrong_Decr\"
		  FROM dbo.ReceptWrong
				where EvnRecept_id = :EvnRecept_id
			LIMIT 1
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
	function getOrgFarmacyGrid($data)
	{
		$filter = "";
		$join = "";
		$queryParams = array();

		if (isset($data['orgfarm'])) {
			$filter .= " and (ofr.OrgFarmacy_Name ilike :OrgFarmacy_Name or ofr.OrgFarmacy_HowGo ilike :OrgFarmacy_Name)";
			$queryParams['OrgFarmacy_Name'] = "%" . $data['orgfarm'] . "%";
		}

		if (isset($data['OrgFarmacys'])) {
			$filter .= " and (ofr.OrgFarmacy_id in (:OrgFarmacyList))";
			$queryParams['OrgFarmacyList'] = $data['OrgFarmacys'];
		}

		if (isset($data['LLO_program'])) {
			$filter .= " and (coalesce(ofix.WhsDocumentCostItemType_id, 0) = :LLO_program)";
			$queryParams['LLO_program'] = $data['LLO_program'];
		}

		if ((isset($data['mnn'])) || (isset($data['torg'])) || (isset($data['WhsDocumentCostItemType_id']))) {
			$join .= "
				--inner join v_DrugOstat drugostat_mnn with (nolock) on drugostat_mnn.OrgFarmacy_id = ofr.OrgFarmacy_id
				--	and DrugOstat_Kolvo > 0
                                inner join v_DrugOstatRegistry drugostat_mnn on drugostat_mnn.Org_id = ofr.Org_id
                                    and DrugOstatRegistry_Kolvo > 0
				inner join v_Drug drug1 on drugostat_mnn.Drug_did = drug1.Drug_id
					and COALESCE(drug1.Drug_IsDel, 1) = 1
			";

			if (isset($data['torg'])) {
				$filter .= " and drug1.Drug_Name like :Drug_Name";
				$queryParams['Drug_Name'] = $data['torg'] . "%";
			}

			if (isset($data['mnn'])) {
				$join .= "
					inner join v_DrugMnn drugmnn1 on drug1.DrugMnn_id = drugmnn1.DrugMnn_id
						and drugmnn1.DrugMnn_Name ilike :DrugMnn_Name
				";
				$queryParams['DrugMnn_Name'] = $data['mnn'] . "%";
			}

			if (isset($data['WhsDocumentCostItemType_id'])) {
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
		$query = "
                  SELECT
				ofr.OrgFarmacy_id as \"OrgFarmacy_id\",
				ofr.Org_id as \"Org_id\",
				ofr.OrgFarmacy_Code as \"OrgFarmacy_Code\",
				ofr.OrgFarmacy_Name as \"OrgFarmacy_Name\",
                case when COALESCE(ofr.OrgFarmacy_IsNarko, 1) = 1 then 'false' else 'true' end as \"OrgFarmacy_IsNarko\",
				--bil.LpuBuilding_Name,
			--	, bil.LpuBuilding_Name for xml path('')
				(
					select string_agg(bil.LpuBuilding_Name, '$')
					FROM v_OrgFarmacy ofr2
					left join OrgFarmacyIndex ofix2 on ofr2.OrgFarmacy_id = ofix2.OrgFarmacy_id and COALESCE(ofix2.OrgFarmacyIndex_deleted, 1) = 1
					left join LpuBuilding bil on bil.LpuBuilding_id = ofix2.LpuBuilding_id
					where ofr2.Org_id = ofr.Org_id and ofix2.Lpu_id = ofix.Lpu_id
					and coalesce(ofix2.WhsDocumentCostItemType_id, 0) = coalesce(ofix.WhsDocumentCostItemType_id, 0)
					order by bil.LpuBuilding_Name
				) as \"LpuBuilding_Name\"
				ofr.OrgFarmacy_HowGo as \"OrgFarmacy_HowGo\",
				(case when COALESCE(ofix.OrgFarmacyIndex_id, 1) = 1 then 0 else 1 end) as \"OrgFarmacy_Vkl\",
				(case when COALESCE(ofix.OrgFarmacyIndex_id, 1) = 1 then 'false' else 'true' end) as \"OrgFarmacy_IsVkl\",
				max(ofix.OrgFarmacyIndex_Index) as \"OrgFarmacyIndex_Index\",
				min(ofix.OrgFarmacyIndex_id) as \"OrgFarmacyIndex_id\",
				--ofix.storage_id,
				ofix.Lpu_id as \"Lpu_id\",
				ofix.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				coalesce(wdc.WhsDocumentCostItemType_Name, 'Все' || 
					case when coalesce(Nowdc.WhsDocumentCostItemType_name, '') = '' then '' else ', кроме ' || substring(Nowdc.WhsDocumentCostItemType_name, 1, length(Nowdc.WhsDocumentCostItemType_name) - 1) end
				) as \"WhsDocumentCostItemType_Name\"
			FROM v_OrgFarmacy ofr
				left join v_OrgFarmacyIndex ofix on ofr.OrgFarmacy_id = ofix.OrgFarmacy_id
                    and ofix.Lpu_id = :Lpu_id
                left join v_WhsDocumentCostItemType wdc on wdc.WhsDocumentCostItemType_id = ofix.WhsDocumentCostItemType_id
				left join lateral (
					Select string_agg(distinct WhsDocumentCostItemType_name, ',') as WhsDocumentCostItemType_name
					from v_OrgFarmacyIndex ofix 
					join  WhsDocumentCostItemType wdc on wdc.WhsDocumentCostItemType_id = ofix.WhsDocumentCostItemType_id
					WHERE ofix.WhsDocumentCostItemType_id is not null
				) Nowdc on true
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
				(case when COALESCE(ofix.OrgFarmacyIndex_id, 1) = 1 then 0 else 1 end),
				(case when COALESCE(ofix.OrgFarmacyIndex_id, 1) = 1 then 'false' else 'true' end),
				--ofix.storage_id,
				ofix.Lpu_id,
				ofix.WhsDocumentCostItemType_id,
				wdc.WhsDocumentCostItemType_name, Nowdc.WhsDocumentCostItemType_name
                ";

		$query = "
                     Select
				t.OrgFarmacyIndex_id as \"OrgFarmacyIndex_id\",
				t.OrgFarmacyIndex_Index as \"OrgFarmacyIndex_Index\",
				t.OrgFarmacy_id as \"OrgFarmacy_id\",
				t.Org_id as \"Org_id\",
				t.OrgFarmacy_Code as \"OrgFarmacy_Code\",
				t.OrgFarmacy_Name as \"OrgFarmacy_Name\",
				t.OrgFarmacy_HowGo as \"OrgFarmacy_HowGo\",
				t.OrgFarmacy_Vkl as \"OrgFarmacy_Vkl\",
				t.OrgFarmacy_IsVkl as \"OrgFarmacy_IsVkl\",
				--t.OrgFarmacyIndex_Index,
				--t.OrgFarmacyIndex_id,
				--t.storage_id,
                t.OrgFarmacy_IsNarko as \"OrgFarmacy_IsNarko\",
				t.Lpu_id as \"Lpu_id\",
				case
					when OrgFarmacy_Vkl = 1 and LENGTH(LpuBuilding_Name) = 0
						then 'Все подразделения'
				else
					replace(T.LpuBuilding_Name, '$',  '<br />')
				end	 as \"LpuBuilding_Name\",
				t.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				case when OrgFarmacy_Vkl = 1  then WhsDocumentCostItemType_Name else null end WhsDocumentCostItemType_Name

from (" . $query . "
    )t ";

		if ( isset($data['typeList']) && $data['typeList'] == 'Остатки')  {
			$query = "
					with farm as (
					{$query}
						)
			Select 
				OrgFarmacy_id as \"OrgFarmacy_id\",
				Org_id as \"Org_id\",
				OrgFarmacy_Code as \"OrgFarmacy_Code\",
				OrgFarmacy_Name as \"OrgFarmacy_Name\",
				OrgFarmacy_HowGo as \"OrgFarmacy_HowGo\",
				max(OrgFarmacyIndex_Index) as \"OrgFarmacyIndex_Index\",
				OrgFarmacy_Vkl as \"OrgFarmacy_Vkl\",
				OrgFarmacy_IsVkl as \"OrgFarmacy_IsVkl\"
			from farm
			group by 
					OrgFarmacy_id,
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

		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Загрузка списка МНН
	 */
	function loadDrugMnnList($data, $options)
	{
		$queryParams = array();
		$table = '';
		$filter = '';
		$vznFld = '';
		$vznJoin = '';

		$recept_drug_ostat_control = $options['recept_drug_ostat_control'];

		switch ($data['mode']) {
			case 'any':
				$data['query'] = $data['query'] . "%";
				break;

			case 'start':
				$data['query'] .= "%";
				break;
		}
		//echo 'DrugMnn_id = ' .$data['DrugMnn_id'];
		if (isset($data['DrugMnn_id'])) {
			$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
			$table = "v_Drug";

			$filter .= " and Drug.DrugMnn_id = :DrugMnn_id";

			$query = "
				SELECT DISTINCT
					DrugMnn.DrugMnn_id as \"DrugMnn_id\",
					DrugMnn.DrugMnn_Code as \"DrugMnn_Code\",
					RTRIM(DrugMnn.DrugMnn_Name) as \"DrugMnn_Name\",
                                        case when vzn.DrugMnn_id is not null then 1 else 0 end vzn
				FROM " . $table . " Drug
					inner join v_DrugMnn DrugMnn on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
                                        left join SicknessDrug vzn on vzn.DrugMnn_id = Drug.DrugMnn_id
				WHERE (1 = 1)
					" . $filter . "
				ORDER BY RTRIM(DrugMnn.DrugMnn_Name)
			";
		} else {
			if ($data['EvnRecept_Is7Noz_Code'] == 1) {
				$table = "v_Drug7noz";
				$vznFld = "1 vzn";
			} else {
				$vznFld = "case when vzn.DrugMnn_id is not null then 1 else 0 end vzn ";
				$vznJoin = 'left join SicknessDrug vzn on vzn.DrugMnn_id = Drug.DrugMnn_id';
				if ($data['ReceptFinance_Code'] == 1) {
					//$table = "r2.v_DrugFedMnn";
					$table = "v_DrugFedMnn";
				} else {
					$table = "v_DrugRegMnn";
				}

				if ($data['ReceptType_Code'] != 1 && $recept_drug_ostat_control && $data['session']['region']['nick'] != 'ufa') {
					// Контроль остатков только по РАС
					$filter .= " and exists (
						select 1
						from v_DrugOstat DrugOstat
							inner join v_OrgFarmacy OrgFarmacy on OrgFarmacy.OrgFarmacy_id = DrugOstat.OrgFarmacy_id
								and COALESCE(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
							inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
							inner join v_ReceptFinance ReceptFinance on ReceptFinance.ReceptFinance_id = DrugOstat.ReceptFinance_id
							left join v_DrugReserv DrugReserv on DrugOstat.Drug_id = DrugReserv.Drug_id
								and DrugOstat.OrgFarmacy_id = DrugReserv.OrgFarmacy_id
								and DrugOstat.ReceptFinance_id = DrugReserv.ReceptFinance_id
						where
							DrugOstat.DrugOstat_Kolvo>0
							and DrugOstat.DrugOstat_Kolvo>COALESCE(DrugReserv.DrugReserv_Kolvo,0)
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
			$filter .= " and Drug.DrugMnn_Name iLIKE :query";

			$query = "
				SELECT DISTINCT
					Drug.DrugMnn_id as \"DrugMnn_id\",
					Drug.DrugMnn_Code as \"DrugMnn_Code\",
					RTRIM(Drug.DrugMnn_Name) as \"DrugMnn_Name\",
                                       {$vznFld}
				FROM " . $table . " Drug
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

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}


}