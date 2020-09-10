<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Utils - модель для работы с медикаментами, ну и до кучи с аптеками
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      15.07.2009
 */

class Drug_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение комбобокса медикамента
	 */
	function loadDrugProtoMnnCombo($data)
	{
		$where = '';
		$params = array();

		if (!empty($data['DrugProtoMnn_id']))
		{
			$where .= " and DrugProtoMnn.DrugProtoMnn_id = :DrugProtoMnn_id" ;
			$params['DrugProtoMnn_id'] = $data['DrugProtoMnn_id'];
		}
		if (!empty($data['ReceptFinance_id']))
		{
			$where .= " and DrugProtoMnn.ReceptFinance_id = :ReceptFinance_id";
			$params['ReceptFinance_id'] = $data['ReceptFinance_id'];
		}
		if (strlen($data['query']) > 0)
		{
			$where .= " and DrugProtoMnn.DrugProtoMnn_Name Like :DrugProtoMnn_Name";
			$params['DrugProtoMnn_Name'] = $data['query'] . "%";
		}
		else if (strlen($data['DrugProtoMnn_Name']) > 0)
		{
			$where .= " and DrugProtoMnn.DrugProtoMnn_Name Like :DrugProtoMnn_Name";
			$params['DrugProtoMnn_Name'] = $data['DrugProtoMnn_Name'] . "%";
		}

		$query = "
			Select top 50
				DrugProtoMnn.DrugProtoMnn_id,
				DrugProtoMnn.DrugProtoMnn_Name,
				DrugProtoMnn.ReceptFinance_id
			From
				v_DrugProtoMnn DrugProtoMnn (nolock)
			Where
				(1=1)
				{$where}
				and DrugProtoMnn.DrugProtoMnn_Name<>'~'
			Order by
				DrugProtoMnn.DrugProtoMnn_Name
		";

		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение списка остатков по медикаменту на аптечном складе
	 */
	function checkDrugOstatOnSklad($data) {
		$queryParams = array();

		$query = "
			select
				case when isnull(sum(DO.DrugOstat_Kolvo), 0) <= 0 then 0 else sum(DO.DrugOstat_Kolvo) end as DrugOstat_Kolvo
			from v_DrugOstat DO with (nolock)
				inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DO.ReceptFinance_id
					and RF.ReceptFinance_Code = :ReceptFinance_Code
			where (1 = 1)
				and DO.OrgFarmacy_id = 1
				and DO.Drug_id = :Drug_id
			group by
				DO.OrgFarmacy_id
		";

		$queryParams['Drug_id'] = $data['Drug_id'];
		$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];

		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Получение списка МНН с возможностью фильтрации по части наименования
	 */
	function loadDrugMnnGrid($data) {
		$drug_mnn_table = 'DrugMnn';
		$filter         = '';
		$queryParams    = array();

		if ( isset($data['DrugMnn_Name']) ) {
			$filter .= " and DrugMnn_Name like :DrugMnn_Name";
			$queryParams['DrugMnn_Name'] = $data['DrugMnn_Name'] . "%";
		}

		switch ( $data['privilegeType'] ) {
			case 'fed':
				$drug_mnn_table = 'v_DrugFed';
				break;

			case 'noz':
				$drug_mnn_table = 'v_Drug7noz';
				break;

			case 'reg':
				$drug_mnn_table = 'v_DrugReg';
				break;
		}

		$query = "
			select distinct
				DrugMnn_id,
				DrugMnn_Code,
				RTRIM(DrugMnn_Name) as DrugMnn_Name,
				RTRIM(DrugMnn_NameLat) as DrugMnn_NameLat
			from " . $drug_mnn_table . " with (nolock)
			where (1 = 1)
				" . $filter . "
			order by DrugMnn_Name
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Получение списка торговых наименований с возможностью фильтрации по части наименования
	 */
	function loadDrugTorgGrid($data) {
		$filter      = '';
		$queryParams = array();

		if ( isset($data['DrugTorg_Name']) ) {
			$filter .= " and DrugTorg_Name like :DrugTorg_Name";
			$queryParams['DrugTorg_Name'] = $data['DrugTorg_Name'] . "%";
		}

		$query = "
			select
				DrugTorg_id,
				DrugTorg_Code,
				RTRIM(DrugTorg_Name) as DrugTorg_Name,
				RTRIM(DrugTorg_NameLat) as DrugTorg_NameLat
			from v_DrugTorg DrugTorg with (nolock)
			where (1 = 1)
				" . $filter . "
			order by DrugTorg_Name
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Получение списка аптек с остатками по выбранному медикаменту
	 */
	function getDrugOstat($data) {
		$finance_type = "";
		$queryParams  = array();

		switch ( $data['ReceptFinance_Code'] ) {
			case 1:
				$finance_type = "Fed";
				break;

			case 2:
				$finance_type = "Reg";
				break;

			case 3:
				$finance_type = "Noz";
				$data['ReceptFinance_Code'] = 1;
				break;

			default:
				return false;
				break;
		}

		switch ( $data['mode'] ) {
			case "all":
				$filter = "";
				if ( isset($data['ReceptType_Code']) && $data['ReceptType_Code'] == 2 && $data['session']['region']['nick'] != 'saratov' ) {
					$filter .= " and ISNULL(Farm_Ostat.DrugOstat_Kolvo, 0) > 0";
				}

				$query = "
					select
						OrgFarmacy.OrgFarmacy_id as OrgFarmacy_id,
						OrgFarmacyIndex.OrgFarmacyIndex_id,
						RTRIM(Org.Org_Name) as OrgFarmacy_Name,
						RTRIM(OrgFarmacy.OrgFarmacy_HowGo) as OrgFarmacy_HowGo,
						YesNo.YesNo_Code as OrgFarmacy_IsFarmacy,
						STR(ISNULL(Farm_Ostat.DrugOstat_Kolvo, 0),18,2) as DrugOstat_Kolvo,
						ISNULL(OST.OMSSprTerr_Code, 0) as OMSSprTerr_Code,
						0 as sort
					from v_OrgFarmacy OrgFarmacy with (nolock)
						inner join v_Org Org with (nolock) on Org.Org_id = OrgFarmacy.Org_id
						outer apply (
							select
								SUM(DO.DrugOstat_Kolvo) as DrugOstat_Kolvo
							from v_DrugOstat DO with (nolock)
								inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DO.ReceptFinance_id
									and RF.ReceptFinance_Code = :ReceptFinance_Code
							where DO.Drug_id = :Drug_id
								and DO.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							group by DO.OrgFarmacy_id
						) Farm_Ostat
						left join OrgFarmacyIndex with (nolock) on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							and OrgFarmacyIndex.Lpu_id = :Lpu_id
						left join YesNo with (nolock) on YesNo.YesNo_id = ISNULL(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
						left join [Address] PAddr with (nolock) on PAddr.Address_id = Org.PAddress_id
						left join OMSSprTerr OST with (nolock) on isnull(OST.KLRgn_id, 0) = isnull(PAddr.KLRgn_id, 0)
							and isnull(OST.KLSubRgn_id, 0) = isnull(PAddr.KLSubRgn_id, 0)
							and isnull(OST.KLCity_id, 0) = isnull(PAddr.KLCity_id, 0)
							and isnull(OST.KLTown_id, 0) = isnull(PAddr.KLTown_id, 0)
					where ISNULL(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
						and ISNULL(OrgFarmacy.OrgFarmacy_Is" . $finance_type . "Lgot, 2) = 2
						and OrgFarmacy.OrgFarmacy_id not in (1, 2)
						{$filter}

					union all

					select
						OrgFarmacy.OrgFarmacy_id as OrgFarmacy_id,
						OrgFarmacyIndex.OrgFarmacyIndex_id,
						'Остатки на аптечном складе' as OrgFarmacy_Name,
						'' as OrgFarmacy_HowGo,
						YesNo.YesNo_Code as OrgFarmacy_IsFarmacy,
						STR(ISNULL(Farm_Ostat.DrugOstat_Kolvo, 0),18,2) as DrugOstat_Kolvo,
						'' as OMSSprTerr_Code,
						1 as sort
					from v_OrgFarmacy OrgFarmacy with (nolock)
						inner join v_Org Org with (nolock) on Org.Org_id = OrgFarmacy.Org_id
						outer apply (
							select
								SUM(DO.DrugOstat_Kolvo) as DrugOstat_Kolvo
							from v_DrugOstat DO with (nolock)
								inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DO.ReceptFinance_id
									and RF.ReceptFinance_Code = :ReceptFinance_Code
							where DO.Drug_id = :Drug_id
								and DO.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							group by DO.OrgFarmacy_id
						) Farm_Ostat
						left join OrgFarmacyIndex with (nolock) on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							and OrgFarmacyIndex.Lpu_id = :Lpu_id
						left join YesNo with (nolock) on YesNo.YesNo_id = ISNULL(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
						left join [Address] PAddr with (nolock) on PAddr.Address_id = Org.PAddress_id
					where
						OrgFarmacy.OrgFarmacy_id = 1
						{$filter}

					order by
						sort
				";
				break;

			default:
				$query = "
					SELECT
						OrgFarmacy.OrgFarmacy_id as OrgFarmacy_id,
						OrgFarmacyIndex.OrgFarmacyIndex_id as OrgFarmacyIndex_id,
						RTRIM(DO.OrgFarmacy_Name) as OrgFarmacy_Name,
						RTRIM(DO.OrgFarmacy_HowGo) as OrgFarmacy_HowGo,
						YesNo.YesNo_Code as OrgFarmacy_IsFarmacy,
						STR(SUM(DO.DrugOstat_Kolvo),18,2) as DrugOstat_Kolvo,
						ISNULL(OST.OMSSprTerr_Code, 0) as OMSSprTerr_Code
					FROM v_DrugOstat DO with (nolock)
						INNER JOIN v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DO.ReceptFinance_id
							and RF.ReceptFinance_Code = :ReceptFinance_Code
						inner join v_OrgFarmacy OrgFarmacy with (nolock) on OrgFarmacy.OrgFarmacy_id = DO.OrgFarmacy_id
						inner join v_Org Org with (nolock) on Org.Org_id = OrgFarmacy.Org_id
						left join OrgFarmacyIndex with (nolock) on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							and OrgFarmacyIndex.Lpu_id = :Lpu_id
						left join YesNo with (nolock) on YesNo.YesNo_id = ISNULL(DO.OrgFarmacy_IsFarmacy, 2)
						left join [Address] PAddr with (nolock) on PAddr.Address_id = Org.PAddress_id
						left join OMSSprTerr OST with (nolock) on isnull(OST.KLRgn_id, 0) = isnull(PAddr.KLRgn_id, 0)
							and isnull(OST.KLSubRgn_id, 0) = isnull(PAddr.KLSubRgn_id, 0)
							and isnull(OST.KLCity_id, 0) = isnull(PAddr.KLCity_id, 0)
							and isnull(OST.KLTown_id, 0) = isnull(PAddr.KLTown_id, 0)
					WHERE (1 = 1)
						and DO.Drug_id = :Drug_id
						and DO.OrgFarmacy_id <> 1
					GROUP BY
						OrgFarmacy.OrgFarmacy_id,
						OrgFarmacyIndex.OrgFarmacyIndex_id,
						DO.OrgFarmacy_Name,
						DO.OrgFarmacy_HowGo,
						YesNo.YesNo_Code,
						OMSSprTerr_Code
					HAVING
						ISNULL(SUM(DO.DrugOstat_Kolvo), 0) > 0
				";
				break;
		}

		$queryParams['Drug_id'] = $data['Drug_id'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];

		$res = $this->db->query($query, $queryParams);

		if (is_object($res))
			return $res->result('array');
		else
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
			$filter .= " and ( drugostat.OrgFarmacy_Name like :OrgFarmacyFilter or drugostat.OrgFarmacy_HowGo like :OrgFarmacyFilter )";
			$queryParams['OrgFarmacyFilter'] = '%' . $data['org_farm_filter'] . '%';
		}

		$queryParams['Drug_id'] = $data['Drug_id'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		// UPD: 2009-11-10 by Savage
		// Закомментировал строки, относящиеся к резерву, ибо некоторые строки в результате двоились
		$sql = "
			SELECT
				1 as DrugOstat_id,
				drugostat.OrgFarmacy_id,
				drugostat.OrgFarmacy_Name,
				drugostat.OrgFarmacy_HowGo,
				drugostat.Drug_id,
				drugostat.DrugOstat_Kolvo,
				STR(sum(case when drugostat.ReceptFinance_id = 1 then case when drugostat.OrgFarmacy_id = 1 then -drugostat.DrugOstat_Kolvo else drugostat.DrugOstat_Kolvo end end), 18, 2) as DrugOstat_Fed,
				STR(sum(case when drugostat.ReceptFinance_id = 2 then case when drugostat.OrgFarmacy_id = 1 then -drugostat.DrugOstat_Kolvo else drugostat.DrugOstat_Kolvo end end), 18, 2) as DrugOstat_Reg,
				STR(sum(case when drugostat.ReceptFinance_id = 3 then case when drugostat.OrgFarmacy_id = 1 then -drugostat.DrugOstat_Kolvo else drugostat.DrugOstat_Kolvo end end), 18, 2) as DrugOstat_7Noz,
				CASE WHEN ofix.OrgFarmacyIndex_Index >= 0 THEN 'true' ELSE 'false' END as OrgFarmacy_IsVkl,
				convert(varchar(10), drugostat.DrugOstat_setDT,104) as DrugOstat_setDT,
				convert(varchar(10), drugostat.DrugOstat_updDT,104) as DrugOstat_updDT
			FROM v_drugostat drugostat with (nolock)
				LEFT JOIN OrgFarmacyIndex ofix with (nolock) on ofix.OrgFarmacy_id = drugostat.OrgFarmacy_id
					and ofix.Lpu_id=:Lpu_id
				-- LEFT JOIN DrugReserv with (nolock) on DrugReserv.Drug_id = drugostat.Drug_id
					-- and DrugReserv.OrgFarmacy_id = drugostat.OrgFarmacy_id
			WHERE 
				drugostat.[Drug_id] = :Drug_id
				" . $filter . "
			GROUP BY
				drugostat.[OrgFarmacy_id],
				drugostat.[OrgFarmacy_Name],
				drugostat.[OrgFarmacy_HowGo],
				drugostat.Drug_id,
				ofix.OrgFarmacyIndex_Index,
				drugostat.DrugOstat_Kolvo--,
				-- DrugReserv_Kolvo
				,drugostat.DrugOstat_setDT
				,drugostat.DrugOstat_updDT
			HAVING sum(drugostat.drugostat_kolvo) > 0
			ORDER BY
				-ofix.OrgFarmacyIndex_Index DESC,
				drugostat.OrgFarmacy_Name
		";
		$res = $this->db->query($sql, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Получение количества открытых медикаментов
	 * deprecated
	 */
	/*function getDrugCount($data) {
		$filters = array();
		$queryParams = array();

		$org_farm_filter = "";
		$filters[] = "Drug_IsDel=1";

		if ( isset($data['mnn']) ) {
			$filters[] = "DrugMnn_Name like :mnn";
			$queryParams['mnn'] = $data['mnn']."%";
		}

		if ( isset($data['torg']) ) {
			$filters[] = "Drug_Name like :torg";
			$queryParams['torg'] = $data['torg']."%";
		}

		if ( isset($data['org_farm_filter']) ) {
			$org_farm_filter .= " and ( v_OrgFarmacy.Orgfarmacy_Name like :org_farm_filter or v_OrgFarmacy.Orgfarmacy_HowGo like :org_farm_filter ) ";
			$queryParams['org_farm_filter'] = "%" . $data['org_farm_filter'] . "%";
		}

		if ( isset($data['ost']) ) {
			if ( $data['ost'] == 1 ) {
				$include = " not in ";
			}
			else {
				$include = " in ";
			}

			$filters[] = "Drug.Drug_id " . $include . " (
				SELECT
					Drug_id
				FROM
					v_DrugOstat drugostat with (nolock)
					INNER JOIN v_OrgFarmacy with (nolock) on drugostat.OrgFarmacy_id = v_OrgFarmacy.OrgFarmacy_id " . $org_farm_filter . "
				WHERE
					drugostat.drugostat_kolvo > 0
			)";
		}

		$sql = "
			SELECT count(*) as cnt
			FROM v_Drug Drug with (nolock)
			INNER JOIN v_DrugMnn DrugMnn with (nolock)
				ON DrugMnn.DrugMnn_id = Drug.DrugMnn_id
			" . ImplodeWhere($filters);
		$res = $this->db->query($sql, $queryParams);

		if ( is_object($res) ) {
			$ct = $res->result('array');
			return $ct[0]['cnt'];
		}
		else {
			return 0;
		}
	}*/


	/**
	 * Получение последней даты обновления остатков
	 */
	function getDrugOstatUpdateTime() {
		$sql = "
			SELECT convert(varchar(10), max(DrugOstat_insDT), 104) + ' ' + convert(varchar, max(DrugOstat_insDT), 108) as DrugOstatUpdateTime
			FROM v_DrugOstat with (nolock)
			WHERE OrgFarmacy_id <> 1
		";
		$res = $this->db->query($sql);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение последней даты обновления остатков
	 */
	function getDrugOstatRASUpdateTime() {
		$sql = "
			SELECT convert(varchar(10), max(DrugOstat_insDT), 104) + ' ' + convert(varchar, max(DrugOstat_insDT), 108) as DrugOstatUpdateTime
			FROM v_DrugOstat with (nolock)
			WHERE OrgFarmacy_id = 1
		";
		$res = $this->db->query($sql);

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

		if ( isset($data['Drug_id']) ) {
			$filter .= " and d.Drug_id = :Drug_id";
			$queryParams['Drug_id'] = $data['Drug_id'];
		}

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

		$ostat_filter = " and exists(
			SELECT
				Drug_id
			FROM
				v_DrugOstat drugostat with (nolock)
				INNER JOIN v_OrgFarmacy OrgFarmacy with (nolock) on drugostat.OrgFarmacy_id=OrgFarmacy.OrgFarmacy_id " . $org_farm_filter . "
			WHERE
				drugostat.drugostat_kolvo > 0 and d.Drug_id = drugostat.Drug_id
		)";


		$sql = "
			SELECT 
			-- select
				d.Drug_id, dm.DrugMnn_Name, d.Drug_Name, d.Drug_CodeG
			-- end select
			FROM
				-- from
				v_Drug d with (nolock)
				INNER JOIN v_DrugMnn dm with (nolock) ON dm.DrugMnn_id = d.DrugMnn_id
			-- end from
			WHERE 
			-- where
			d.Drug_IsDel = 1 " . $filter . $ostat_filter . " 
			-- end where
			order by 
				-- order by 
				DrugMnn_Name, Drug_Name
				-- end order by 
		";
		$count_sql = getCountSQLPH($sql);
		if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
		}

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
	 * Включение и выключение аптек
	 */
	function vklOrgFarmacy($data) {
		$queryParams = array();

		if ( $data['vkl'] == 1 ) {

			$dbl = $this->checkOrgFarmacyDoubles($data);
			if (is_array($dbl) && count($dbl)) {
				throw new Exception('Данная аптека уже включена');
			}

			$sql = "
				select
					max(OrgFarmacyIndex_Index) as max_index
				from
					OrgFarmacyIndex with (nolock)
				where
					Lpu_id = {$data['Lpu_id']}
			";
			$res = $this->db->query($sql);

			$sel = $res->result('array');
			$org_farmacy_index = 0;

			if ( $sel[0]['max_index'] >= 0 ) {
				$org_farmacy_index = $sel[0]['max_index'] + 1;
			}

			$sql = "
				declare
					@ErrCode int,
					@OFIndex_id bigint,
					@ErrMsg varchar(400);
				set @OFIndex_id = null;
				exec p_OrgFarmacyIndex_ins
					@Server_id = :Server_id,
					@OrgFarmacyIndex_id = @OFIndex_id output,
					@OrgFarmacy_id = :OrgFarmacy_id,
					@Lpu_id = :Lpu_id,
					@OrgFarmacyIndex_Index = :OrgFarmacyIndex_Index,
					@OrgFarmacyIndex_IsEnabled = 1,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @OFIndex_id as OrgFarmacyIndex_id, @ErrCode as ErrCode, @ErrMsg as ErrMsg
			";

			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
			$queryParams['OrgFarmacyIndex_Index'] = $org_farmacy_index;
			$queryParams['pmUser_id'] = $data['pmUser_id'];
			$queryParams['Server_id'] = $data['Server_id'];
		}
		else {
			$sql = "
				declare
					@ErrCode int,
					@ErrMsg varchar(400);
				exec p_OrgFarmacyIndex_del
					@OrgFarmacyIndex_id = :OrgFarmacyIndex_id,
					@IsRemove = '2',
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @ErrCode as ErrCode, @ErrMsg as ErrMsg
			";

			$queryParams['OrgFarmacyIndex_id'] = $data['OrgFarmacyIndex_id'];
		}

		$res = $this->db->query($sql, $queryParams);
		$sel = false;

		if ( is_object($res) ) {
			$sel = $res->result('array');
		}

		if ( $res ) {
			if ( $data['vkl'] == 1 ) {
				$sel[0]['OrgFarmacyIndex_Index'] = $org_farmacy_index;
			}
			return $sel;
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка дублирования аптек при включении
	 * Возможно, больше не потребуется, но пускай будет
	 */
	function checkOrgFarmacyDoubles($data) {
		$queryParams = array();

		$query = "
			select
				OrgFarmacyIndex_id
			from v_OrgFarmacyIndex with(nolock)
			where 
				Lpu_id = :Lpu_id and
				OrgFarmacy_id = :OrgFarmacy_id and
				Server_id = :Server_id
		";

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
		$queryParams['Server_id'] = $data['Server_id'];

		//die(getDebugSQL($query, $queryParams));
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Изменение приоритета аптеки
	 */
	function orgFarmacyReplace($data) {
		$org_farmacy_new_index = 0;
		$queryParams = array();

		if ( $data['direction'] == 'down' ) {
			$org_farmacy_new_index = -1;
		}

		$sql = "
			exec p_OrgFarmacyIndex_setIndex
				@OrgFarmacy_id = :OrgFarmacy_id,
				@Lpu_id = :Lpu_id,
				@OrgFarmacy_NewIndex = :OrgFarmacy_NewIndex,
				@pmUser_id = :pmUser_id
		";

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
		$queryParams['OrgFarmacy_NewIndex'] = $org_farmacy_new_index;
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		$res = $this->db->query($sql, $queryParams);

		$sel = false;

		if ( is_object($res) ) {
			$sel = $res->result('array');
		}

		if ( $res ) {
			return $sel;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение полного списка аптек
	 */
	function getOrgFarmacyNetGrid($data) {
		$query = "
			select
				ct.Org_pid as OrgFarmacy_id,
				og.Org_Nick as OrgFarmacy_Name
			from
				v_Contragent ct with (nolock)
				inner join v_Org og with (nolock) on ct.Org_pid = og.Org_id
			where
				ct.Org_pid is not null
			group by
				ct.Org_pid,
				og.Org_Nick
		";

		$res = $this->db->query($query);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
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

		if ( (isset($data['mnn'])) || (isset($data['torg'])) ) {
			$join .= "
				inner join v_DrugOstat drugostat_mnn with (nolock) on drugostat_mnn.OrgFarmacy_id = ofr.OrgFarmacy_id
					and DrugOstat_Kolvo > 0
				inner join v_Drug drug1 with(nolock) on drugostat_mnn.Drug_id = drug1.Drug_id
					and drug1.Drug_IsDel = 1
			";

			if ( isset($data['torg']) ) {
				$filter .= " and drug1.Drug_Name like :Drug_Name";
				$queryParams['Drug_Name'] = $data['torg'] . "%";
			}

			if ( isset($data['mnn']) ) {
				$join .= "
					inner join v_DrugMnn drugmnn1 with(nolock) on drug1.DrugMnn_id = drugmnn1.DrugMnn_id
						and drugmnn1.DrugMnn_Name like :DrugMnn_Name
				";
				$queryParams['DrugMnn_Name'] = $data['mnn'] . "%";
			}
		}

		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if (!empty($data['onlyAttachLpu'])) {
			$filter_pers = "";
			if (!empty($data['Person_id'])) {
				// выводить любимую аптеку человека, даже если она не прикреплена к МО
				$filter_pers = " or exists(select top 1 ofp.OrgFarmacyPerson_id from v_OrgFarmacyPerson ofp (nolock) where ofp.Person_id = :Person_id and ofp.OrgFarmacy_id = ofr.OrgFarmacy_id)";
				$queryParams['Person_id'] = $data['Person_id'];
			}
			$filter .= " and (ofix.OrgFarmacyIndex_id is not null{$filter_pers})";
		}

		$fields = "";
		if (!empty($data['Person_id'])) {
			// отдадим признак является ли аптека любимой для пациента
			$fields .= ", case when exists(select top 1 ofp.OrgFarmacyPerson_id from v_OrgFarmacyPerson ofp (nolock) where ofp.Person_id = :Person_id and ofp.OrgFarmacy_id = ofr.OrgFarmacy_id) then 2 else 1 end as OrgFarmacy_IsFavorite";
			$queryParams['Person_id'] = $data['Person_id'];
		}

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
				{$fields}
			FROM v_OrgFarmacy ofr with (nolock) 
				left join OrgFarmacyIndex ofix with (nolock) on ofr.OrgFarmacy_id = ofix.OrgFarmacy_id
					and ofix.Lpu_id = :Lpu_id
				" . $join . "
			WHERE (1 = 1) 
				-- and OrgFarmacy_IsFarmacy = 2
				" . $filter . "
		";
		//echo getDebugSQL($query, $queryParams);die;
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение полного списка аптек (для формы просмотра прикрепления к МО)
	 */
	function getOrgFarmacyGridByLpu($data) {
		$filter = "";
		$join = "";
		$lb_sub_query = "";
		$queryParams = array();

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];

		if ( isset($data['OrgFarmacy_id']) ) {
			$filter .= " and ofr.OrgFarmacy_id = :OrgFarmacy_id";
			$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
		}


		if (!empty($data['show_storage'])) {
			$lb_sub_query = "
				select
					bil.LpuBuilding_Name + isnull(' ('+is_narko.str+')', '') + isnull(' / '+s.Storage_Name, '') + '$ ' 
				from
					v_OrgFarmacy ofr2 with (nolock) 
					left join v_OrgFarmacyIndex ofix2 with (nolock) on ofr2.OrgFarmacy_id = ofix2.OrgFarmacy_id
					left join v_LpuBuilding bil with (nolock) on bil.LpuBuilding_id = ofix2.LpuBuilding_id
					left join v_Storage s with (nolock) on s.Storage_id = ofix2.Storage_id
					outer apply (
						select (case
							when isnull(ofix2.OrgFarmacyIndex_IsNarko, 0) = 2 then 'НС и ПВ'
							when isnull(ofix2.OrgFarmacyIndex_IsNarko, 0) = 1 then 'Все кроме НС и ПВ'
							else null
						end) as str
					) is_narko 
				where
					ofr2.Org_id = ofr.Org_id and
					ofix2.Lpu_id = ofix.Lpu_id and
					isnull(ofix2.WhsDocumentCostItemType_id, 0) = isnull(:WhsDocumentCostItemType_id, 0)
				order by
					bil.LpuBuilding_Name
				for xml path('')
			";
		} else {
			$lb_sub_query = "
				select
					bil.LpuBuilding_Name + '$ ' 
				from
					v_OrgFarmacy ofr2 with (nolock) 
					left join v_OrgFarmacyIndex ofix2 with (nolock) on ofr2.OrgFarmacy_id = ofix2.OrgFarmacy_id
					left join v_LpuBuilding bil with (nolock) on bil.LpuBuilding_id = ofix2.LpuBuilding_id
				where
					ofr2.Org_id = ofr.Org_id and
					ofix2.Lpu_id = ofix.Lpu_id and
					isnull(ofix2.WhsDocumentCostItemType_id, 0) = isnull(:WhsDocumentCostItemType_id, 0)
				group by
					bil.LpuBuilding_Name 
				order by
					bil.LpuBuilding_Name
				for xml path('')
			";
		}

		$query  = "
			select 
				t.OrgFarmacyIndex_id,
				t.OrgFarmacyIndex_Index,
				t.OrgFarmacy_id,
				t.Org_id,
				t.OrgFarmacy_Code,
				t.OrgFarmacy_Name, 
				t.OrgFarmacy_Nick, 
				t.OrgFarmacy_HowGo,
				t.OrgFarmacy_Vkl,
				t.OrgFarmacy_IsVkl,
                t.OrgFarmacy_IsNarko,
				t.Lpu_id,
				(case
					when
						t.OrgFarmacy_Vkl = 1 and len(t.LpuBuilding_Name) = 0
					then
						'Все подразделения' 
					else 
						replace (substring(t.LpuBuilding_Name, 0 , len(t.LpuBuilding_Name)), '$', '<br/>')
				end) as LpuBuilding_Name,
				wdcit.WhsDocumentCostItemType_Name,
				t.WhsDocumentCostItemType_id,
				(case
					when isnull(ofix.is_narko_cnt, 0) > 0 and isnull(ofix.is_not_narko_cnt, 0) > 0 then 'Все ЛП' 
					when isnull(ofix.is_narko_cnt, 0) = 0 and isnull(ofix.is_not_narko_cnt, 0) > 0 then 'Все кроме НС и ПВ'
					when isnull(ofix.is_narko_cnt, 0) > 0 and isnull(ofix.is_not_narko_cnt, 0) = 0 then 'НС и ПВ'
					else null
				end) as LsGroup_Name
			from (
				select 
					ofr.OrgFarmacy_id,
					ofr.Org_id,
					ofr.OrgFarmacy_Code,
					ofr.OrgFarmacy_Nick,
					ofr.OrgFarmacy_Name,
					ofr.OrgFarmacy_HowGo,
					(case
						when isnull(ofr.OrgFarmacy_IsNarko, 1) = 1 then 'false'
						else 'true'
					end) as OrgFarmacy_IsNarko,
					(
						{$lb_sub_query}
					) as LpuBuilding_Name,
					(case
						when isnull(ofix.OrgFarmacyIndex_id, 1) = 1 then 0
						else 1
					end) as OrgFarmacy_Vkl,
					(case
						when isnull(ofix.OrgFarmacyIndex_id, 1) = 1 then 'false'
						else 'true'
					end) as OrgFarmacy_IsVkl,
					max(ofix.OrgFarmacyIndex_Index) OrgFarmacyIndex_Index,
					min(ofix.OrgFarmacyIndex_id) OrgFarmacyIndex_id,
					ofix.Lpu_id,
					ofix.WhsDocumentCostItemType_id
				from
					v_OrgFarmacy ofr with (nolock) 
					left join v_OrgFarmacyIndex ofix with (nolock) on
						ofr.OrgFarmacy_id = ofix.OrgFarmacy_id and
						ofix.Lpu_id = :Lpu_id and
						isnull(ofix.WhsDocumentCostItemType_id, 0) = isnull(:WhsDocumentCostItemType_id, 0)
					".$join."
				where
					(1 = 1) 
					".$filter."
				group by
					ofr.OrgFarmacy_id,
					ofr.Org_id,
					ofr.OrgFarmacy_Code,
					ofr.OrgFarmacy_Name,
					ofr.OrgFarmacy_Nick,
					ofr.OrgFarmacy_IsNarko,
					ofix.Lpu_id,
					ofix.WhsDocumentCostItemType_id,
					ofr.OrgFarmacy_HowGo,
					(case when isnull(ofix.OrgFarmacyIndex_id, 1) = 1 then 0 else 1 end),
					(case when isnull(ofix.OrgFarmacyIndex_id, 1) = 1 then 'false' else 'true' end)				
    		) t
    		left join v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = t.WhsDocumentCostItemType_id
			outer apply (
				select
					sum(case when isnull(i_ofix.OrgFarmacyIndex_IsNarko, 0) = 2 then 1 else 0 end) as is_narko_cnt,
					sum(case when isnull(i_ofix.OrgFarmacyIndex_IsNarko, 0) = 1 then 1 else 0 end) as is_not_narko_cnt
				from
					v_OrgFarmacyIndex i_ofix with (nolock)
				where
					i_ofix.lpu_id = t.Lpu_id and
					i_ofix.OrgFarmacy_id = t.OrgFarmacy_id and
					isnull(i_ofix.WhsDocumentCostItemType_id, 0) = isnull(t.WhsDocumentCostItemType_id, 0) and
					i_ofix.LpuBuilding_id is not null
			) ofix
    	";

		$res = $this->db->query($query, $queryParams);
		if (is_object($res)) {
			return $res->result('array');
		} else {
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

		if ( isset($data['mnn']) )  {
			$mnn_filter = "  AND DrugMnn.DrugMnn_Name like :Mnn";
			$queryParams['Mnn'] = $data['mnn'].'%';
		}

		if ( isset($data['torg']) ) {
			$torg_filter = "  AND Drug.Drug_Name like :Torg";
			$queryParams['Torg'] = $data['torg'].'%';
		}

		$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];

		// UPD: 2009-11-10 by Savage
		// Закомментировал строки, относящиеся к резерву, ибо некторые строки в результате двоились
		$sql = "
			SELECT
				1 as DrugOstat_id,
				drugostat.OrgFarmacy_id,
				drugostat.Drug_id,
				drugostat.Drug_Name,
				Drug.Drug_CodeG,
				DrugMnn.DrugMnn_Name,
				STR(sum(case when drugostat.ReceptFinance_id = 1 then drugostat.DrugOstat_Kolvo end), 18, 2) as DrugOstat_Fed,
				STR(sum(case when drugostat.ReceptFinance_id = 2 then drugostat.DrugOstat_Kolvo end), 18, 2) as DrugOstat_Reg,
				STR(sum(case when drugostat.ReceptFinance_id = 3 then drugostat.DrugOstat_Kolvo end), 18, 2) as DrugOstat_7Noz,
				convert(varchar(10), drugostat.DrugOstat_setDT,104) as DrugOstat_setDT,
				convert(varchar(10), drugostat.DrugOstat_updDT,104) as DrugOstat_updDT
			FROM v_drugostat drugostat with (nolock)
				LEFT JOIN v_Drug Drug with (nolock) on drugostat.Drug_id = Drug.Drug_id
				LEFT JOIN v_DrugMnn DrugMnn with (nolock) on Drug.DrugMnn_id = DrugMnn.DrugMnn_id
				-- LEFT JOIN DrugReserv with (nolock) on DrugReserv.Drug_id=drugostat.Drug_id
					-- and DrugReserv.OrgFarmacy_id = drugostat.OrgFarmacy_id
			WHERE
				drugostat.OrgFarmacy_id=:OrgFarmacy_id
				" . $torg_filter . $mnn_filter . "
			GROUP BY
				drugostat.OrgFarmacy_id,
				drugostat.Drug_id,
				drugostat.Drug_Name,
				Drug.Drug_CodeG,
				DrugMnn.DrugMnn_Name--,
				-- DrugReserv_Kolvo
				,drugostat.DrugOstat_setDT
				,drugostat.DrugOstat_updDT
			HAVING sum(drugostat.drugostat_kolvo) > 0
			ORDER BY drugostat.Drug_Name 
		";
		$res = $this->db->query($sql, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *  Получение списка остатков по медикаменту в аптеках
	 */
	function loadFarmacyOstatList($data, $options) {
		$queryParams = array();
		$recept_drug_ostat_control = $options['recept_drug_ostat_control'];

		if ( isset($data['OrgFarmacy_id']) ) {
			if ( $data['ReceptFinance_Code'] == 3) {
				$data['ReceptFinance_Code'] = 1;
			}

			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as OrgFarmacy_id,
					RTRIM(Org.Org_Name) as OrgFarmacy_Name,
					RTRIM(OrgFarmacy.OrgFarmacy_HowGo) as OrgFarmacy_HowGo,
					YesNo.YesNo_Code as OrgFarmacy_IsFarmacy,
					case when isnull(DrugOstat.DrugOstat_Kolvo, 0) <= 0 then 0 else STR(isnull(DrugOstat.DrugOstat_Kolvo, 0), 18, 2) end as DrugOstat_Kolvo
				from v_OrgFarmacy OrgFarmacy with (nolock)
					inner join v_Org Org with (nolock) on Org.Org_id = OrgFarmacy.Org_id
					inner join YesNo with (nolock) on YesNo.YesNo_id = ISNULL(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					outer apply (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join v_ReceptFinance ReceptFinance with (nolock) on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id
								and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							and DOA.Drug_id = :Drug_id
					) DrugOstat
				where (1 = 1)
					and OrgFarmacy.OrgFarmacy_id = :OrgFarmacy_id
					and ISNULL(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
			";

			$queryParams['Drug_id'] = $data['Drug_id'];
			$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		}
		else if ( $data['ReceptFinance_Code'] == 3) {
			// 7 нозологий
			$data['ReceptFinance_Code'] = 1;

			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as OrgFarmacy_id,
					RTRIM(Org.Org_Name) as OrgFarmacy_Name,
					RTRIM(OrgFarmacy.OrgFarmacy_HowGo) as OrgFarmacy_HowGo,
					YesNo.YesNo_Code as OrgFarmacy_IsFarmacy,
					case when isnull(DrugOstat.DrugOstat_Kolvo, 0) <= 0 then 0 else isnull(DrugOstat.DrugOstat_Kolvo, 0) end as DrugOstat_Kolvo
				from v_OrgFarmacy OrgFarmacy with (nolock)
					inner join v_Org Org with (nolock) on Org.Org_id = OrgFarmacy.Org_id
					inner join YesNo with (nolock) on YesNo.YesNo_id = ISNULL(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					outer apply (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join v_ReceptFinance ReceptFinance with (nolock) on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id
								and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							and DOA.Drug_id = :Drug_id
					) DrugOstat
				where (1 = 1)
					and ISNULL(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
					and ISNULL(OrgFarmacy.OrgFarmacy_IsNozLgot, 2) = 2
					and OrgFarmacy.OrgFarmacy_id <> 1
			";

			$queryParams['Drug_id'] = $data['Drug_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		}
		else if ( $data['ReceptType_Code'] == 1) {
			// Тип рецепта "На бланке"
			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as OrgFarmacy_id,
					RTRIM(Org.Org_Name) as OrgFarmacy_Name,
					RTRIM(OrgFarmacy.OrgFarmacy_HowGo) as OrgFarmacy_HowGo,
					YesNo.YesNo_Code as OrgFarmacy_IsFarmacy,
					case when isnull(DrugOstat.DrugOstat_Kolvo, 0) <= 0 then 0 else isnull(DrugOstat.DrugOstat_Kolvo, 0) end as DrugOstat_Kolvo
				from v_OrgFarmacy OrgFarmacy with (nolock)
					inner join OrgFarmacyIndex with (nolock) on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
						and OrgFarmacyIndex.Lpu_id = :Lpu_id
					inner join v_Org Org with (nolock) on Org.Org_id = OrgFarmacy.Org_id
					inner join YesNo with (nolock) on YesNo.YesNo_id = ISNULL(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					outer apply (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join v_ReceptFinance ReceptFinance with (nolock) on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id
								and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							and DOA.Drug_id = :Drug_id
					) DrugOstat
				where (1 = 1)
					and ISNULL(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
					and OrgFarmacy.OrgFarmacy_id <> 1
			";

			$queryParams['Drug_id'] = $data['Drug_id'];
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		}
		else {
			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as OrgFarmacy_id,
					RTRIM(Org.Org_Name) as OrgFarmacy_Name,
					RTRIM(OrgFarmacy.OrgFarmacy_HowGo) as OrgFarmacy_HowGo,
					YesNo.YesNo_Code as OrgFarmacy_IsFarmacy,
					case when isnull(DrugOstat.DrugOstat_Kolvo, 0) <= 0 then 0 else isnull(DrugOstat.DrugOstat_Kolvo, 0) end as DrugOstat_Kolvo
				from v_OrgFarmacy OrgFarmacy with (nolock)
					inner join OrgFarmacyIndex with (nolock) on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
						and OrgFarmacyIndex.Lpu_id = :Lpu_id
					inner join v_Org Org with (nolock) on Org.Org_id = OrgFarmacy.Org_id
					inner join YesNo with (nolock) on YesNo.YesNo_id = ISNULL(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					outer apply (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join v_ReceptFinance ReceptFinance with (nolock) on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id
								and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							and DOA.Drug_id = :Drug_id
					) DrugOstat
					outer apply (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join v_ReceptFinance ReceptFinance with (nolock) on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id
								and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = 1
							and DOA.Drug_id = :Drug_id
					) RAS_Ostat
				where ISNULL(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
			";

			if($recept_drug_ostat_control) {
				if ( isSuperadmin() ) {
					$query .= " and (ISNULL(DrugOstat.DrugOstat_Kolvo, 0) > 0 or ISNULL(RAS_Ostat.DrugOstat_Kolvo, 0) > 0)";
				} else {
					$query .= " and ISNULL(RAS_Ostat.DrugOstat_Kolvo, 0) > 0";
				}
			}

			$queryParams['Drug_id'] = $data['Drug_id'];
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		}

		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}


	/**
	 * Получение списка медикаментов в комбобокс в рецепте
	 */
	function loadDrugList($data, $options) {
		$queryParams = array();
		$table       = '';
		$filter      = '';

		$filter .= " and Drug.Drug_begDate < :Date";
		$filter .= " and (Drug.Drug_endDate is null or Drug.Drug_endDate > :Date)";
		$mi_1_join = "";
		$mi_1_where = "";
		$recept_drug_ostat_control = $options['recept_drug_ostat_control'];

		if(isset($data['is_mi_1']) && ($data['is_mi_1'] == 'true') && !isset($data['Drug_id'])){
			$mi_1_join = "  inner join rls.DrugNomen DN with(nolock) on DN.DrugNomen_Code = Drug.Drug_CodeG
							left join rls.Drug RD with(nolock) on RD.Drug_id = DN.Drug_id
							left join rls.Prep P with (nolock) on P.Prep_id = RD.DrugPrep_id
							left join rls.CLSNTFR NTFR with (nolock) on NTFR.CLSNTFR_ID = P.NTFRID
			";
			$mi_1_where = " and NTFR.PARENTID not in (1, 176) and NTFR.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
		}

		if ( isset($data['query']) ) {
			switch ( $data['mode'] ) {
				case 'any':
					$data['query'] = $data['query'] . "%";
					break;

				case 'start':
					$data['query'] .= "%";
					break;
			}

			$filter .= " and Drug.Drug_Name LIKE :query";
			$queryParams['query'] = $data['query'] . "%";
		}

		if ( isset($data['DrugMnn_id']) ) {
			$filter .= " and Drug.DrugMnn_id = :DrugMnn_id";
			$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
		}
		//Если не задан один из этих параметров, не выполняем запрос
		if (!isset($queryParams['query']) && !isset($queryParams['DrugMnn_id']) && !isset($data['Drug_id'])) {
			return false;
		}
		if ( $data['Drug_id'] > 0 ) {
			if ( $data['ReceptFinance_Code'] == 3 ) {
				$data['ReceptFinance_Code'] = 1;
			}

			$query = "
				SELECT TOP 1
					Drug.Drug_id,
					Drug.Drug_Code,
					RTRIM(Drug.Drug_Name) as Drug_Name,
					Drug.DrugMnn_id,
					cast(DrugPrice.DrugState_Price as numeric(18, 2)) as Drug_Price,
					Drug.Drug_IsKek as Drug_IsKEK,
					ISNULL(Drug_IsKEK.YesNo_Code, 0) as Drug_IsKEK_Code,
					0 as DrugOstat_Flag
				FROM v_Drug Drug with (nolock)
					left join v_DrugPrice DrugPrice with (nolock) on DrugPrice.Drug_id = Drug.Drug_id
						and DrugPrice.DrugProto_id = (
							select max(DP.DrugProto_id)
							from v_DrugPrice DP with (nolock)
								inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DP.ReceptFinance_id
									and RF.ReceptFinance_Code = :ReceptFinance_Code
							where DP.Drug_id = Drug.Drug_id
								and DP.DrugProto_begDate <= :Date
						)
					left join YesNo Drug_IsKEK with (nolock) on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek
					left join v_ReceptFinance ReceptFinance with (nolock) on ReceptFinance.ReceptFinance_id = DrugPrice.ReceptFinance_id
					outer apply (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join OrgFarmacyIndex with (nolock) on OrgFarmacyIndex.OrgFarmacy_id = DOA.OrgFarmacy_id
								and OrgFarmacyIndex.Lpu_id = :Lpu_id
							inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DOA.ReceptFinance_id
								and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id <> 1
							and DOA.Drug_id = Drug.Drug_id
					) Farm_Ostat
					outer apply (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DOA.ReceptFinance_id
								and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = 1
							and DOA.Drug_id = Drug.Drug_id
					) RAS_Ostat
				WHERE (1 = 1)
					and Drug.Drug_id = :Drug_id
				ORDER BY Drug_Name
			";

			$queryParams['Drug_id'] = $data['Drug_id'];
		}
		else if ( $data['EvnRecept_Is7Noz_Code'] == 1 ) {
			$query = "
				SELECT
					Drug.Drug_id,
					Drug.Drug_Code,
					RTRIM(Drug.Drug_Name) as Drug_Name,
					Drug.DrugMnn_id,
					cast(DrugPrice.DrugState_Price as numeric(18, 2)) as Drug_Price,
					Drug.Drug_IsKek as Drug_IsKEK,
					ISNULL(Drug_IsKEK.YesNo_Code, 0) as Drug_IsKEK_Code,
					case when ISNULL(Farm_Ostat.DrugOstat_Kolvo, 0) <= 0 then case when ISNULL(RAS_Ostat.DrugOstat_Kolvo, 0) <= 0 then 2 else 1 end else 0 end as DrugOstat_Flag
				FROM v_Drug7Noz Drug with (nolock)
					left join v_DrugPrice DrugPrice with (nolock) on DrugPrice.Drug_id = Drug.Drug_id
						and DrugPrice.DrugProto_id = (
							select max(DP.DrugProto_id)
							from v_DrugPrice DP with (nolock)
								inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DP.ReceptFinance_id
									and RF.ReceptFinance_Code = :ReceptFinance_Code
							where DP.Drug_id = Drug.Drug_id
								and DP.DrugProto_begDate <= :Date
						)
					left join YesNo Drug_IsKEK with (nolock) on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek
					outer apply (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join v_OrgFarmacy OrgFarmacy with (nolock) on OrgFarmacy.OrgFarmacy_id = DOA.OrgFarmacy_id
							inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DOA.ReceptFinance_id
								and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id <> 1
							and ISNULL(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
							and ISNULL(OrgFarmacy.OrgFarmacy_IsNozLgot, 2) = 2
							and DOA.Drug_id = Drug.Drug_id
					) Farm_Ostat
					outer apply (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DOA.ReceptFinance_id
								and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = 1
							and DOA.Drug_id = Drug.Drug_id
					) RAS_Ostat
					".$mi_1_join."
				WHERE (1 = 1)
					" . $filter . $mi_1_where . "
				ORDER BY Drug_Name
			";
		}
		else {
			switch ( $data['ReceptFinance_Code'] ) {
				case 1:
					$table = "v_DrugFed";
					break;

				case 2:
					$table = "v_DrugReg";
					break;

				default:
					return false;
					break;
			}

			if ( $data['ReceptType_Code'] != 1 && $recept_drug_ostat_control ) {
				$filter .= " and ISNULL(RAS_Ostat.DrugOstat_Kolvo, 0) + ISNULL(Farm_Ostat.DrugOstat_Kolvo, 0) > 0";
			}

			$query = "
				SELECT
					Drug.Drug_id,
					Drug.Drug_Code,
					RTRIM(Drug.Drug_Name) as Drug_Name,
					Drug.DrugMnn_id,
					cast(DrugPrice.DrugState_Price as numeric(18, 2)) as Drug_Price,
					Drug.Drug_IsKek as Drug_IsKEK,
					ISNULL(Drug_IsKEK.YesNo_Code, 0) as Drug_IsKEK_Code,
					case when ISNULL(Farm_Ostat.DrugOstat_Kolvo, 0) <= 0 then case when ISNULL(RAS_Ostat.DrugOstat_Kolvo, 0) <= 0 then 2 else 1 end else 0 end as DrugOstat_Flag
				FROM " . $table . " Drug with (nolock)
					left join v_DrugPrice DrugPrice with (nolock) on DrugPrice.Drug_id = Drug.Drug_id
						and DrugPrice.DrugProto_id = (
							select max(DP.DrugProto_id)
							from v_DrugPrice DP with (nolock)
								inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DP.ReceptFinance_id
									and RF.ReceptFinance_Code = :ReceptFinance_Code
							where DP.Drug_id = Drug.Drug_id
								and DP.DrugProto_begDate <= :Date
						)
					left join YesNo Drug_IsKEK with (nolock) on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek
					left join v_ReceptFinance ReceptFinance with (nolock) on ReceptFinance.ReceptFinance_id = DrugPrice.ReceptFinance_id
					outer apply (
						select top 1 DOA.DrugOstat_Kolvo as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DOA.ReceptFinance_id
								and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id <> 1
							and DOA.Drug_id = Drug.Drug_id
							and DOA.DrugOstat_Kolvo > 0
					) Farm_Ostat
					outer apply (
						select top 1 DOA.DrugOstat_Kolvo as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DOA.ReceptFinance_id
								and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = 1
							and DOA.Drug_id = Drug.Drug_id
							and DOA.DrugOstat_Kolvo > 0
					) RAS_Ostat
					".$mi_1_join."
				WHERE (1 = 1)
					" . $filter . $mi_1_where ."
				ORDER BY Drug_Name
			";
			/*
					outer apply (
						select top 1 DOA.DrugOstat_Kolvo as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join OrgFarmacyIndex on OrgFarmacyIndex.OrgFarmacy_id = DOA.OrgFarmacy_id
								and OrgFarmacyIndex.Lpu_id = :Lpu_id
							inner join ReceptFinance RF on RF.ReceptFinance_id = DOA.ReceptFinance_id
								and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id <> 1
							and DOA.Drug_id = Drug.Drug_id
							and DOA.DrugOstat_Kolvo > 0
					) Farm_Ostat
			*/
		}

		$queryParams['Date'] = $data['Date'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		//die(getDebugSQL($query, $queryParams));
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadSicknessDrugList($data)
	{
		$table = '';
		$where = '';

		$table = "v_Drug";

		if ($data['Drug_id'] > 0)
		{
			$where .= " and Drug.Drug_id = " . $data['Drug_id'];
		}
		else
		{
			if ($data['DrugMnn_id'] > 0)
			{
				$where .= " and Drug.DrugMnn_id = " . $data['DrugMnn_id'];
			}
		}

		$query = "
			SELECT distinct top 100
				[Drug].[Drug_id],
				[Drug].[Drug_Code],
				RTRIM([Drug].[Drug_Name]) as [Drug_Name],
				[Drug].[DrugMnn_id],
				cast([DrugPrice].[DrugState_Price] as numeric(18, 2)) as [Drug_Price]
			FROM " . $table . " [Drug] with (nolock)
				left join [v_DrugPrice] [DrugPrice] with (nolock) on [DrugPrice].[Drug_id] = [Drug].[Drug_id]
			WHERE (1 = 1)
				" . $where . "
			ORDER BY [Drug_Name]
		";

		$result = $this->db->query($query);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Загрузка списка МНН
	 */
	function loadDrugMnnList($data, $options) {
		$queryParams = array();
		$table       = '';
		$table_nolock = 'with (nolock)';
		$filter      = '';

		$recept_drug_ostat_control = $options['recept_drug_ostat_control'];

		switch ( $data['mode'] ) {
			case 'any':
				$data['query'] = $data['query'] . "%";
				break;

			case 'start':
				$data['query'] .= "%";
				break;
		}

		if ( isset($data['DrugMnn_id']) ) {
			$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
			$table = "v_Drug";
			$filter .= " and Drug.DrugMnn_id = :DrugMnn_id";

			$query = "
				SELECT DISTINCT
					DrugMnn.DrugMnn_id,
					DrugMnn.DrugMnn_Code,
					RTRIM(DrugMnn.DrugMnn_Name) as DrugMnn_Name
				FROM " . $table . " Drug with (nolock)
					inner join v_DrugMnn DrugMnn with (nolock) on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
				WHERE (1 = 1)
					" . $filter . "
				ORDER BY DrugMnn_Name 
			";
		}
		else {
			if ( $data['EvnRecept_Is7Noz_Code'] == 1 ) {
				$table = "v_Drug7noz";
			}
			else {
				if (empty($data['byDrugRequest']) && !empty($data['WhsDocumentCostItemType_id'])) {
					$table = "dbo.fn_DrugFromDrugNormativeList({$data['WhsDocumentCostItemType_id']})";
					$table_nolock = "";
				} else {
					if ( $data['ReceptFinance_Code'] == 1 ) {
						$table = "v_DrugFedMnn";
					} else {
						$table = "v_DrugRegMnn";
					}
				}

				if (!empty($data['byDrugRequest'])) {
					$drrfilter = "";
					switch ( $data['DrugRequestRow_IsReserve'] ) {
						case 1:
							$drrfilter .= " and DRR.Person_id = :Person_id";
							$queryParams['Person_id'] = $data['Person_id'];
							break;

						case 2:
							$drrfilter .= " and DRR.Person_id is null";
							if (!empty($data['MedPersonal_id'])) {
								$drrfilter .= " and DR.MedPersonal_id = :MedPersonal_id";
								$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
							}

							if (!empty($data['DrugProtoMnn_id'])) {
								$drrfilter .= " and DRR.DrugProtoMnn_id = :DrugProtoMnn_id";
								$queryParams['DrugProtoMnn_id'] = $data['DrugProtoMnn_id'];
							}
							break;

						default:
							return false;
							break;
					}
					if (isset($data['ReceptFinance_id']))
					{
						if($data['ReceptFinance_id']==1)
							$drrfilter .= " and DRT.DrugRequestType_id = 1";
						else if($data['ReceptFinance_id']==2)
							$drrfilter .= " and DRT.DrugRequestType_id = 2";
						else
							return false;
					}
					$filter .= "
						and exists(
							select top 1
								DRR.DrugRequestRow_id
							from
								DrugRequestRow DRR with (nolock)
								inner join v_DrugRequest DR with (nolock) on DR.DrugRequest_id = DRR.DrugRequest_id
								inner join v_DrugRequestPeriod DRP with (nolock) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
									and cast(:Date as datetime) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
								inner join v_DrugRequestType DRT with (nolock) on DRT.DrugRequestType_id = DRR.DrugRequestType_id
								inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = DR.Lpu_id
								left join v_DrugProtoMnn DPM with (nolock) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
							where
								DPM.DrugMnn_id = Drug.DrugMnn_id
								{$drrfilter}
						)
					";
				} else {
					if ($data['ReceptType_Code'] != 1 && $recept_drug_ostat_control && !in_array($data['session']['region']['nick'], array('ufa', 'perm'))) {
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
					RTRIM(Drug.DrugMnn_Name) as DrugMnn_Name
				FROM " . $table . " Drug " . $table_nolock . "
				WHERE (1 = 1)
					" . $filter . "
				ORDER BY DrugMnn_Name 
			";
		}
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Поиск медикаментов по всему справочнику
	 * Используется в фильтре в окне поиске рецепта
	 */
	function searchFullDrugList($data)
	{
		$queryParams = array();
		$table       = 'v_Drug';
		$where       = '';

		if ( $data['Drug_id'] > 0 ) {
			$queryParams['Drug_id'] = $data['Drug_id'];
			$where .= " and Drug.Drug_id = :Drug_id";

			if ( $data['ReceptFinance_Code'] == 3 ) {
				$data['ReceptFinance_Code'] = 1;
			}
		}
		else {

			if ( strlen($data['query']) > 0 ) {
				$queryParams['query'] = $data['query'] . "%";
				$where .= " and Drug.Drug_Name LIKE :query";
			}

			if ( $data['DrugMnn_id'] > 0 ) {
				$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
				$where .= " and Drug.DrugMnn_id = :DrugMnn_id";
			}
		}

		$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];

		$query = "
			SELECT distinct top 100
				[Drug].[Drug_id],
				[Drug].[Drug_Code],
				RTRIM([Drug].[Drug_Name]) as [Drug_Name],
				[Drug].[DrugMnn_id]
			FROM " . $table . " [Drug] with (nolock)
				left join v_DrugPrice DrugPrice with (nolock) on DrugPrice.Drug_id = Drug.Drug_id
					and DrugPrice.DrugProto_begDate = (
						select max(DrugProto_begDate)
						from v_DrugPrice DP with (nolock)
							inner join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = DP.ReceptFinance_id
								and RF.ReceptFinance_Code = :ReceptFinance_Code
						where Drug_id = Drug.Drug_id
					)
				left join [YesNo] [Drug_IsKEK] with (nolock) on [Drug_IsKEK].[YesNo_id] = [Drug].[Drug_IsKek]
				left join v_ReceptFinance ReceptFinance with (nolock) on ReceptFinance.ReceptFinance_id = DrugPrice.ReceptFinance_id
			WHERE (1 = 1)
				" . $where . "
			ORDER BY [Drug_Name]
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 */
	function SearchDrugRlsList($data)
	{
		$queryParams = array();
		$where       = '';
		$queryParams['query'] = $data['query'] . "%";
		$where .= " and D.Drug_Name LIKE :query";
		$query = "
            select
                D.Drug_id,
                D.Drug_Name,
                D.Drug_Code
            from rls.v_Drug D with(nolock)
           WHERE (1 = 1)
				" . $where . "
        ";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Поиск МНН по всему справочнику без учета даты
	 * Используется в фильтре в окне поиске рецепта
	 */
	function searchFullDrugMnnList($data)
	{
		$queryParams = array();
		$table       = 'v_DrugMnn';
		$where       = '';

		if ( $data['DrugMnn_id'] > 0 ) {
			$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
			$where .= " and DrugMnn.DrugMnn_id = :DrugMnn_id";
		}
		else {

			$queryParams['query'] = $data['query'] . "%";
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];

			$where .= " and DrugMnn.DrugMnn_Name LIKE :query";
		}

		$query = "
			SELECT distinct top 100
				DrugMnn.DrugMnn_id,
				DrugMnn.DrugMnn_Code,
				RTRIM(DrugMnn.DrugMnn_Name) as DrugMnn_Name
			FROM " . $table . " DrugMnn with (nolock)
			WHERE (1 = 1)
				" . $where . "
			ORDER BY DrugMnn_Name
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение наименования МНН
	 */
	function saveDrugMnnLatinName($data) {
		$queryParams = array();

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :DrugMnn_id;
			exec p_DrugMnnLat_upd
				@DrugMnn_id = @Res output,
				@DrugMnn_NameLat = :DrugMnn_NameLat,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DrugMnn_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg ;
		";

		$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
		$queryParams['DrugMnn_NameLat'] = $data['DrugMnn_NameLat'];
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * Сохранение торг. наименования
	 */
	function saveDrugTorgLatinName($data) {
		$queryParams = array();

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :DrugTorg_id;
			exec p_DrugTorgLat_upd
				@DrugTorg_id = @Res output,
				@DrugTorg_NameLat = :DrugTorg_NameLat,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DrugTorg_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams['DrugTorg_id'] = $data['DrugTorg_id'];
		$queryParams['DrugTorg_NameLat'] = $data['DrugTorg_NameLat'];
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * Загрузка списка комплексных мнн
	 */
	function loadDrugComplexMnnList($data, $options) {
		$filterList  = array();
		$queryParams = array();
		$region_nick = getRegionNick();

		if ($data['recept_drug_ostat_viewing'] != -1) {
			$options['recept_drug_ostat_viewing'] = $data['recept_drug_ostat_viewing'];
		}
		if ($data['recept_drug_ostat_control'] != -1) {
			$options['recept_drug_ostat_control'] = $data['recept_drug_ostat_control'];
		}
		if ($data['recept_empty_drug_ostat_allow'] != -1) {
			$options['recept_empty_drug_ostat_allow'] = $data['recept_empty_drug_ostat_allow'];
		}
		if (!empty($data['select_drug_from_list'])) {
			$options['select_drug_from_list'] = $data['select_drug_from_list'];
		}

		if ( !empty($data['DrugComplexMnn_id']) && (empty($data['withOptions']) || !$data['withOptions']) ) {
			$query = "
				select
					 dcm.DrugComplexMnn_id
					,RTRIM(dcm.DrugComplexMnn_RusName) as DrugComplexMnn_Name
				from
					rls.v_DrugComplexMnn dcm with (nolock)
				where
					dcm.DrugComplexMnn_id = :DrugComplexMnn_id
			";
			$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
		}
		// По задаче https://redmine.swan.perm.ru/issues/29459 при выписке рецепта на бланке ЛС выбираются также,
		// как и при выписке на листе, пока не будет добавлен выбор соответствующих опций в общие настройки.
		//
		// При выписке на бланке или если отключены все контроли...
		/*else if ( in_array($data['ReceptType_Code'], array(1)) ) {
			// Выбор комп. МНН из списка комплексных МНН, включенных в номенклатурный справочник ЛС, соответствующих МНН и формам выпуска ЛС, включенных в
			// действующий нормативный список по указанной в рецепте Программе ЛЛО

			$filterList[] = "dnl.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id"; // Программа ЛЛО
			$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];

			if ( !empty($data['Date']) ) {
				$filterList[] = "(dnls.DrugNormativeListSpec_BegDT is null or dnls.DrugNormativeListSpec_BegDT <= :Date)";
				$filterList[] = "(dnls.DrugNormativeListSpec_EndDT is null or dnls.DrugNormativeListSpec_EndDT > :Date)";
				$queryParams['Date'] = $data['Date'];
			}

			if ( !empty($data['query']) ) {
				switch ( $data['mode'] ) {
					case 'any':
						$data['query'] = "%" . $data['query'] . "%";
					break;

					default:
						$data['query'] .= "%";
					break;
				}

				$filterList[] = "dcm.DrugComplexMnn_RusName like :query";
				$queryParams['query'] = $data['query'];
			}

			$query = "
				select distinct
					 dcm.DrugComplexMnn_id
					,RTRIM(dcm.DrugComplexMnn_RusName) as DrugComplexMnn_Name
				from dbo.v_DrugNormativeListSpec dnls with (nolock)
					inner join dbo.v_DrugNormativeList dnl with (nolock) on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
					inner join rls.v_DrugComplexMnn dcm with (nolock) on dcm.ActMatters_id = dnls.DrugNormativeListSpecMNN_id
						and dcm.CLSDRUGFORMS_ID = dnls.DrugNormativeListSpecForms_id
				where
					" . implode(" and ", $filterList) . "
				order by
					DrugComplexMnn_Name
			";
		}*/
		// В зависимости от настроек, надо будет вытаскивать данные из WhsDocumentOrderAllocation и DrugRequest
		else {
			$from = 'rls.v_DrugComplexMnn dcm with(nolock)';
			$additionFieldsArr = array();
			$withArr = array();

			if($data['Lpu_id']>0){
				$queryParams['Lpu_id'] = $data['Lpu_id'];
			}

			if (!empty($data['DrugComplexMnn_id'])) {
				$filterList[] = "dcm.DrugComplexMnn_id like :DrugComplexMnn_id";
				$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
			}

			if (!empty($data['is_mi_1']) && ($data['is_mi_1'] == 'true')){
				$filterList[] = "NTFR.PARENTID not in (1, 176)";
				$filterList[] = "NTFR.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
			}

			if ( !empty($data['query']) ) {
				switch ( $data['mode'] ) {
					case 'any':
						$data['query'] = "%" . $data['query'] . "%";
						break;

					default:
						$data['query'] .= "%";
						break;
				}

				$filterList[] = "dcm.DrugComplexMnn_RusName like :query";
				$queryParams['query'] = $data['query'];
			}

			//должен быть передан либо идентификатор комплексного мнн либо строка поиска, иначе запрос получится слишком медленным
			if (empty($data['DrugComplexMnn_id']) && empty($data['query'])) {
				return false;
			}

			$ostat_type = 0;
			if ($options['recept_drug_ostat_viewing']) {
				$ostat_type = 1;
			}
			if ($options['recept_drug_ostat_control']) {
				$ostat_type = 2;
			}
			if ($options['recept_empty_drug_ostat_allow']) {
				$ostat_type = 3;
			}

			$drug_from_list = (!empty($options['select_drug_from_list']) ? $options['select_drug_from_list'] : '');
			$drug_ostat_control = !empty($options['recept_drug_ostat_control']);

			//для Москвы: при выписке из ЖНВЛП, если признак протокола ВК = "да" то в качестве базовый выборки используется таблица с комплексными МНН
			if ($region_nick == 'msk' && $drug_from_list == 'jnvlp' && $data['EvnRecept_IsKEK'] == 2) {
				$drug_from_list = 'drugcomplexmnn_table';
			}

			if (!empty($drug_from_list)) {
				switch ($drug_from_list) {
					case 'drugcomplexmnn_table':
						$from = "
							rls.v_DrugComplexMnn dcm with(nolock)
							inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
						";

						if ($data['is_mi_1'] == 'true') {
							$from .= "
								outer apply ( --для определения медизделий
									select top 1
										i_p.NTFRID
									from
										rls.v_Drug i_d with (nolock)
										left join rls.v_Prep i_p with (nolock) on i_p.Prep_id = i_d.DrugPrep_id
									where
										i_d.DrugComplexMnn_id = dcm.DrugComplexMnn_id and
										i_p.NTFRID is not null
								) p
								left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
							";
						}

						//для Москвы: при выписке из ЖНВЛП, если признак протокола ВК = "да" и признак выписки по МНН, то выбираем только комплексные мнн имеющие действующее вещество
						if ($region_nick == 'msk' && $data['EvnRecept_IsKEK'] == 2 && $data['EvnRecept_IsMnn'] == 2) {
							$filterList[] = "dcmn.ACTMATTERS_id is not null";
						}
						break;
					case 'jnvlp':
						$withArr[] = "
							df_tree (name, id, pid) -- рекурсивный запрос для получения лекарственных форм не относящихся к лекарственным средствам
							as (
								select
									t.NAME, t.CLSDRUGFORMS_ID, t.PARENTID
								from
									rls.CLSDRUGFORMS t with (nolock)
								where
									t.CLSDRUGFORMS_ID > 1 and t.PARENTID = 0 
								union all
								select
									t.NAME, t.CLSDRUGFORMS_ID, t.PARENTID
								from
									rls.CLSDRUGFORMS t with (nolock)
									inner join df_tree tr on t.PARENTID = tr.id
								where
									t.CLSDRUGFORMS_ID not in (979)
							)
						";

						if (!empty($data['EvnRecept_IsMnn']) && $data['EvnRecept_IsMnn'] == 2) { //выписка по МНН = "Да"
							$from = "
								v_DrugNormativeListSpec dnls with(nolock)
								inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.ActMatters_id = dnls.DrugNormativeListSpecMNN_id
								inner join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnnName_id = dcmn.DrugComplexMnnName_id
								inner join v_DrugNormativeList dnl with(nolock) on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
								left join v_DrugNormativeListSpecFormsLink dnlsfl with(nolock) on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
									and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
								outer apply ( --для определения медизделий
									select top 1
										i_p.NTFRID
									from
										rls.v_Drug i_d with (nolock)
										left join rls.v_Prep i_p with (nolock) on i_p.Prep_id = i_d.DrugPrep_id
									where
										i_d.DrugComplexMnn_id = dcm.DrugComplexMnn_id and
										i_p.NTFRID is not null
								) p
								left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
								outer apply (
									select top 1
										i_dnlsfl.DrugNormativeListSpecFormsLink_id
									from
										v_DrugNormativeListSpecFormsLink i_dnlsfl with (nolock)
									where
										i_dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id										
								) dnlsfl_exists
								left join df_tree on df_tree.id = dcm.CLSDRUGFORMS_ID
							";
						} else {
							$from = "
								v_DrugNormativeListSpecTorgLink dnlstl with(nolock)
								inner join v_DrugNormativeListSpec dnls with(nolock) on dnls.DrugNormativeListSpec_id = dnlstl.DrugNormativeListSpec_id
								inner join rls.v_Prep p with(nolock) on p.TRADENAMEID = dnlstl.DrugNormativeListSpecTorg_id
								inner join rls.v_Drug d with(nolock) on d.DrugPrep_id = p.Prep_id
								inner join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
								inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id /*and dcmn.ActMatters_id is null*/
								inner join v_DrugNormativeList dnl with(nolock) on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
								left join v_DrugNormativeListSpecFormsLink dnlsfl with(nolock) on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
									and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
								left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
								outer apply (
									select top 1
										i_dnlsfl.DrugNormativeListSpecFormsLink_id
									from
										v_DrugNormativeListSpecFormsLink i_dnlsfl with (nolock)
									where
										i_dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id										
								) dnlsfl_exists
								left join df_tree on df_tree.id = dcm.CLSDRUGFORMS_ID
							";
						}

						$filterList[] = "
							(
								isnull(dnlsfl.DrugNormativeListSpecForms_id, 0) = isnull(dcm.CLSDRUGFORMS_ID, 0) or
								dnlsfl_exists.DrugNormativeListSpecFormsLink_id is null and df_tree.id is null
							)
						";
						if (!empty($data['Date'])) {
							$filterList[] = "dnl.DrugNormativeList_BegDT <= :Date";
							$filterList[] = "(dnl.DrugNormativeList_EndDT > :Date or dnl.DrugNormativeList_EndDT is null)";
							$queryParams['Date'] = $data['Date'];
						}
						if (!empty($data['PersonRegisterType_id'])) {
							$filterList[] = "dnl.PersonRegisterType_id = :PersonRegisterType_id";
							$queryParams['PersonRegisterType_id'] = $data['PersonRegisterType_id'];
						}
						if (!empty($data['EvnRecept_IsKEK']) && ($region_nick != 'msk' || $data['EvnRecept_IsKEK'] == 1)) {
							$filterList[] = "isnull(dnls.DrugNormativeListSpec_IsVK,1) = :EvnRecept_IsKEK";
							$queryParams['EvnRecept_IsKEK'] = $data['EvnRecept_IsKEK'];
						}

						//для Москвы: при выписке из ЖНВЛП, если признак протокола ВК != "да" и признак выписки по МНН = "нет", то выбираем только комплексные мнн не имеющие действующего вещества
						if ($region_nick == 'msk' && $data['EvnRecept_IsKEK'] != 2 && $data['EvnRecept_IsMnn'] == 1) {
							$filterList[] = "dcmn.ACTMATTERS_id is null";
						}
						break;

					case 'request':
						$from = "
                            v_DrugRequestRow drr with(nolock)
                            inner join v_DrugRequest dr with(nolock) on dr.DrugRequest_id = drr.DrugRequest_id
                            inner join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                            inner join v_DrugRequestPeriod drp with(nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
                            outer apply ( -- признак разрешения всем МО на выписку льготных рецептов из заявок главных внештатных специалистов
                                select
                                    isnull(i_yn.YesNo_Code, 0) as isPrivilegeAllowed -- 0 - Нет; 1 - Да.
                                from
                                    v_WhsDocumentCostItemType i_wdcit with (nolock)
                                    left join YesNo i_yn with (nolock) on i_yn.YesNo_id = i_wdcit.WhsDocumentCostItemType_isPrivilegeAllowed
                                where
                                    i_wdcit.PersonRegisterType_id = dr.PersonRegisterType_id
                            ) is_priv_all
                            outer apply ( -- проверка на включение врача заявки в перечень главных внештатных специалистов
                                select top 1
                                    i_hms.HeadMedSpec_id
                                from
                                    v_MedPersonal i_mp with (nolock)
                                    left join persis.v_MedWorker i_mw with (nolock) on i_mw.Person_id = i_mp.Person_id
                                    inner join v_HeadMedSpec i_hms with (nolock) on i_hms.MedWorker_id = i_mw.MedWorker_id
                                where
                                    i_mp.MedPersonal_id = dr.MedPersonal_id and
                                    drp.DrugRequestPeriod_begDate between i_hms.HeadMedSpec_begDT and i_hms.HeadMedSpec_endDT
                            ) is_hms
                            outer apply (
                                select
                                    sum(i_drpo.DrugRequestPersonOrder_OrdKolvo) as Kolvo,
                                    max(i_drpo.Person_id) as Person_id
                                from
                                    v_DrugRequestPersonOrder i_drpo with(nolock)
                                where
                                    :Person_id is not null and
                                    drr.DrugRequest_id = dr.DrugRequest_id and
                                    i_drpo.Person_id = :Person_id and
                                    (
                                        i_drpo.DrugComplexMnn_id = drr.DrugComplexMnn_id or
                                        i_drpo.Tradenames_id = drr.TRADENAMES_id
                                    )
                            ) drpo
                            outer apply (
                                select
                                    (isnull(drr.DrugRequestRow_Kolvo, 0) - sum(i_drpo.DrugRequestPersonOrder_OrdKolvo)) as Kolvo
                                from
                                    v_DrugRequestPersonOrder i_drpo with(nolock)
                                where
                                    drr.DrugRequest_id = dr.DrugRequest_id and
                                    (
                                        i_drpo.DrugComplexMnn_id = drr.DrugComplexMnn_id or
                                        i_drpo.Tradenames_id = drr.TRADENAMES_id
                                    )
                            ) drpo_reserve
                            inner join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = drr.DrugComplexMnn_id
                            inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                            left join v_PersonState ps with(nolock) on ps.Person_id = drpo.Person_id
                            outer apply ( --для определения медизделий
                                select top 1
                                    i_p.NTFRID
                                from
                                    rls.v_Drug i_d with (nolock)
                                    left join rls.v_Prep i_p with (nolock) on i_p.Prep_id = i_d.DrugPrep_id
                                where
                                    i_d.DrugComplexMnn_id = drr.DrugComplexMnn_id and
                                    i_p.NTFRID is not null
                            ) p
                            left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
						";

						$queryParams['Person_id'] = !empty($data['Person_id']) ? $data['Person_id'] : null;
						if (!empty($data['Person_id']) && !empty($data['fromReserve'])) {
							$filterList[] = "(drpo.Kolvo > 0 or drpo_reserve.Kolvo > 0)";
						} else if (!empty($data['Person_id'])) {
							$filterList[] = "drpo.Kolvo > 0";
						} else if (!empty($data['fromReserve'])) {
							$filterList[] = "drpo_reserve.Kolvo > 0";
						}

						/*if (!empty($data['EvnRecept_IsMnn'])) {
							if ($data['EvnRecept_IsMnn'] == 2) {
								$filterList[] = "dcmn.ActMatters_id is not null";
							} else {
								$filterList[] = "dcmn.ActMatters_id is null";
							}
						}*/

						$filterList[] = "drs.DrugRequestStatus_Code = 3";    //Утвержденные заявки
						if (!empty($data['Date'])) {
							$filterList[] = ":Date between drp.DrugRequestPeriod_begDate and drp.DrugRequestPeriod_endDate";
							$queryParams['Date'] = $data['Date'];
						}

						$filterList[] = "(
							dr.MedPersonal_id = :MedPersonal_id or
							(
								is_hms.HeadMedSpec_id is not null and -- заявка главного внештатного специалиста
								is_priv_all.isPrivilegeAllowed = 1 -- разрешена выписка из заявок главных внештатных специалистов
							)
						)";
						$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];

						$additionFieldsArr[] = "case when ps.Person_id is null then 'Резерв'
							else ps.Person_SurName+' '+ps.Person_FirName+' '+isnull(ps.Person_SecName,'')
						end as Person_Fio";
						$additionFieldsArr[] = "drr.DrugRequestRow_id";
						break;

					/*case 'request':
						$from = "
							v_DrugRequestRow drr with(nolock)
							inner join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = drr.DrugComplexMnn_id
							inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
							inner join v_DrugRequest dr with(nolock) on dr.DrugRequest_id = drr.DrugRequest_id
							inner join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
							inner join v_DrugRequestPeriod drp with(nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
							left join v_PersonState ps with(nolock) on ps.Person_id = drr.Person_id
							outer apply ( --для определения медизделий
								select top 1
									i_p.NTFRID
								from
									rls.v_Drug i_d with (nolock)
									left join rls.v_Prep i_p with (nolock) on i_p.Prep_id = i_d.DrugPrep_id
								where
									i_d.DrugComplexMnn_id = drr.DrugComplexMnn_id and
									i_p.NTFRID is not null
							) p
							left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
						";
						if (!empty($data['EvnRecept_IsMnn'])) {
							if ($data['EvnRecept_IsMnn'] == 2) {
								$filterList[] = "dcmn.ActMatters_id is not null";
							} else {
								$filterList[] = "dcmn.ActMatters_id is null";
							}
						}
						$filterList[] = "drs.DrugRequestStatus_Code = 3";	//Утвержденные заявки
						if (!empty($data['Date'])) {
							$filterList[] = ":Date between drp.DrugRequestPeriod_begDate and drp.DrugRequestPeriod_endDate";
							$queryParams['Date'] = $data['Date'];
						}

						if (!empty($data['Person_id']) && !empty($data['fromReserve']) && $data['fromReserve']) {
							$filterList[] = "(drr.Person_id = :Person_id or drr.Person_id is null)";
							$queryParams['Person_id'] = $data['Person_id'];
							$filterList[] = "(drr.Person_id is not null or dr.MedPersonal_id = :MedPersonal_id)";
							$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
						} else if (!empty($data['Person_id'])) {
							$filterList[] = "drr.Person_id = :Person_id";
							$queryParams['Person_id'] = $data['Person_id'];
						} else if (!empty($data['fromReserve']) && $data['fromReserve']) {
							$filterList[] = "drr.Person_id is null";
							$filterList[] = "dr.MedPersonal_id = :MedPersonal_id";
							$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
						}
						$additionFieldsArr[] = "case when ps.Person_id is null then 'Резерв'
							else ps.Person_SurName+' '+ps.Person_FirName+' '+isnull(ps.Person_SecName,'')
						end as Person_Fio";
						$additionFieldsArr[] = "drr.DrugRequestRow_id";
						break;*/

					case 'allocation':
						$lpuid = "";
						$filterList[] = "sat.SubAccountType_Code = 1"; // "Доступно"
						$filterList[] = "dor.DrugOstatRegistry_Kolvo > 0"; // На остатках должны быть медикаменты
						$filterList[] = "isnull(isdef.YesNo_Code, 0) = 0"; // Исключение забракованных серий
						/*if (!empty($data['EvnRecept_IsMnn'])) {
							if ($data['EvnRecept_IsMnn'] == 2) {
								$filterList[] = "dcmn.ActMatters_id is not null";
							} else {
								$filterList[] = "dcmn.ActMatters_id is null";
							}
						}*/
						if ($data['Lpu_id'] != "" && $data['Lpu_id'] > 0) {
							$filterList[] = "l.Lpu_id = :Lpu_id"; // Текущая МО
							$filterList[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id"; // Программа ЛЛО
							$lpuid = "inner join v_Lpu l with (nolock) on l.Org_id = dor.Org_id";
							$queryParams['Lpu_id'] = $data['Lpu_id'];
						}
						$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];

						$additionFieldsArr[] = "dor.DrugOstatRegistry_id";
						$additionFieldsArr[] = "dsh.WhsDocumentSupply_id";

						$from = "
							v_DrugOstatRegistry dor with (nolock)
							left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
							left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
							left join v_DrugShipment dsh with(nolock) on dsh.DrugShipment_id = dor.DrugShipment_id
							left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
							left join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id							
							left join rls.v_Prep p with(nolock) on p.Prep_id = d.DrugPrep_id
							left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
							left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
							left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
							{$lpuid}
						";

						if (!empty($data['WhsDocumentCostItemType_id'])) {
							//проверка программы ЛЛО
							$wdcit_query = "								
								select
									isnull(yn.YesNo_Code, 0) as isPersonAllocation
								from
									v_WhsDocumentCostItemType wdcit with (nolock) 
									left join v_YesNo yn with (nolock) on yn.YesNo_id = wdcit.WhsDocumentCostItemType_isPersonAllocation
								where
									wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id;
							";
							$wdcit_data = $this->getFirstRowFromQuery($wdcit_query, array(
								'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']
							));

							if (!empty($wdcit_data['isPersonAllocation'])) { //если установлен признак выписки рецепта по персональной разнарядке
								//проверка включения медикамента и пациента в разнарядку

								$wdcit_where = "";
								$drpo_where = "";

								if (!empty($data['DrugFinance_id'])) {
									$wdcit_where .= " and ii_wdcit.DrugFinance_id = :DrugFinance_id ";
									$drpo_where .= " and i_drr.DrugFinance_id = :DrugFinance_id ";
									$queryParams['DrugFinance_id'] = $data['DrugFinance_id'];
								}

								if (!empty($lpuid)) {
									$drpo_where .= " and i_dr.Lpu_id = l.Lpu_id ";
								}

								$from .= "
									outer apply (
										select top 1
											i_drpo.DrugRequestPersonOrder_id
										from
											v_DrugRequestPersonOrder i_drpo with (nolock)
											left join v_DrugRequest i_dr with(nolock) on i_dr.DrugRequest_id = i_drpo.DrugRequest_id
											left join v_DrugRequestPeriod i_drp with(nolock) on i_drp.DrugRequestPeriod_id = i_dr.DrugRequestPeriod_id
											outer apply (
												select top 1
													ii_drr.DrugFinance_id
												from 
													v_DrugRequestRow ii_drr with (nolock)
												where
													ii_drr.DrugRequest_id = i_drpo.DrugRequest_id and
													ii_drr.DrugComplexMnn_id = i_drpo.DrugComplexMnn_id and
													isnull(ii_drr.TRADENAMES_id, 0) = isnull(i_drpo.Tradenames_id, 0)
												order by
													ii_drr.DrugRequestRow_id
											) i_drr
											outer apply (
												select top 1
													ii_wdcit.WhsDocumentCostItemType_id
												from
													v_WhsDocumentCostItemType ii_wdcit with (nolock)
												where
													ii_wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and
													isnull(ii_wdcit.PersonRegisterType_id, 0) = isnull(i_dr.PersonRegisterType_id, 0)
													{$wdcit_where}				
											) i_wdcit
										where
											i_drpo.Person_id = :Person_id and
											i_drpo.DrugComplexMnn_id = dcm.DrugComplexMnn_id and
											i_wdcit.WhsDocumentCostItemType_id is not null and	
											i_drp.DrugRequestPeriod_begDate <= :Date and
											i_drp.DrugRequestPeriod_endDate >= :Date and
											i_drpo.DrugRequestPersonOrder_OrdKolvo > 0
											{$drpo_where}																						
									) drpo
								";

								$filterList[] = "drpo.DrugRequestPersonOrder_id is not null";

								$queryParams['Person_id'] = $data['Person_id'];
								$queryParams['Date'] = $data['Date'];
							}
						}
						break;

					case 'request_and_allocation':
						$lpuid = "";
						$filterList[] = "drs.DrugRequestStatus_Code = 3";	//Утвержденные заявки
						$filterList[] = "sat.SubAccountType_Code = 1"; 		// "Доступно"
						$filterList[] = "dor.DrugOstatRegistry_Kolvo > 0"; 	// На остатках должны быть медикаменты
						$filterList[] = "isnull(isdef.YesNo_Code, 0) = 0"; // Исключение забракованных серий
						/*if (!empty($data['EvnRecept_IsMnn'])) {
							if ($data['EvnRecept_IsMnn'] == 2) {
								$filterList[] = "dcmn.ActMatters_id is not null";
							} else {
								$filterList[] = "dcmn.ActMatters_id is null";
							}
						}*/
						if ($data['Lpu_id'] != "" && $data['Lpu_id'] > 0) {
							$filterList[] = "l.Lpu_id = :Lpu_id"; // Текущая МО
							$filterList[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id"; // Программа ЛЛО
							$lpuid = "inner join v_Lpu l with (nolock) on l.Org_id = dor.Org_id";
							$queryParams['Lpu_id'] = $data['Lpu_id'];
							$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
						}

						if (!empty($data['Date'])) {
							$filterList[] = ":Date between drp.DrugRequestPeriod_begDate and drp.DrugRequestPeriod_endDate";
							$queryParams['Date'] = $data['Date'];
						}

						if (!empty($data['Person_id']) && !empty($data['fromReserve']) && $data['fromReserve']) {
							$filterList[] = "(drr.Person_id = :Person_id or drr.Person_id is null)";
							$queryParams['Person_id'] = $data['Person_id'];
							$filterList[] = "(drr.Person_id is not null or dr.MedPersonal_id = :MedPersonal_id)";
							$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
						} else if (!empty($data['Person_id'])) {
							$filterList[] = "drr.Person_id = :Person_id";
							$queryParams['Person_id'] = $data['Person_id'];
						} else if (!empty($data['fromReserve']) && $data['fromReserve']) {
							$filterList[] = "drr.Person_id is null";
							$filterList[] = "dr.MedPersonal_id = :MedPersonal_id";
							$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
						}
						$additionFieldsArr[] = "case when ps.Person_id is null then 'Резерв'
							else ps.Person_SurName+' '+ps.Person_FirName+' '+isnull(ps.Person_SecName,'')
						end as Person_Fio";
						$additionFieldsArr[] = "drr.DrugRequestRow_id";
						$additionFieldsArr[] = "dor.DrugOstatRegistry_id";
						$additionFieldsArr[] = "dsh.WhsDocumentSupply_id";

						$from = "
							v_DrugOstatRegistry dor with (nolock)
							left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
							inner join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
							inner join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
							inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
							left join v_DrugShipment dsh with(nolock) on dsh.DrugShipment_id = dor.DrugShipment_id
							{$lpuid}
							inner join v_DrugRequestRow drr with(nolock) on drr.DrugComplexMnn_id = d.DrugComplexMnn_id
							inner join v_DrugRequest dr with(nolock) on dr.DrugRequest_id = drr.DrugRequest_id
							inner join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
							inner join v_DrugRequestPeriod drp with(nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
							left join v_PersonState ps with(nolock) on ps.Person_id = drr.Person_id
							left join rls.v_Prep p with(nolock) on p.Prep_id = d.DrugPrep_id
							left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
							left join rls.v_PrepSeries psr with (nolock) on psr.PrepSeries_id = dor.PrepSeries_id
							left join v_YesNo isdef with (nolock) on isdef.YesNo_id = psr.PrepSeries_isDefect
						";
						break;
				}
			}

			if ($drug_ostat_control) { //в настройках включен контроль остатков
				$withFilterList = array('1=1');

				$withFilterList[] = "sat.SubAccountType_Code = 1"; // "Доступно"
				$withFilterList[] = "dor.DrugOstatRegistry_Kolvo > 0"; // На остатках должны быть медикаменты
				$withFilterList[] = "isnull(isdef.YesNo_Code, 0) = 0"; // Исключение забракованных серий

				if (!empty($data['WhsDocumentCostItemType_id'])) {
					$withFilterList[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id"; // Программа ЛЛО
					$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
				}

				$ras = null;
				$apt = null;
				$ofi_subquery = "";
				if (!empty($options['recept_by_ras_drug_ostat']) && $options['recept_by_ras_drug_ostat'] && $ostat_type != 2) {
					$ras = "ct.ContragentType_SysNick = 'store'";
				}
				if (!empty($options['recept_by_farmacy_drug_ostat']) && $options['recept_by_farmacy_drug_ostat']) {
					$apt = "ct.ContragentType_SysNick = 'apt'";
					if (!empty($options['recept_farmacy_type']) && $options['recept_farmacy_type'] == 'mo_farmacy' && !empty($queryParams['Lpu_id'])) {
						$ofi_subquery = "left join v_OrgFarmacyIndex ofi with(nolock) on ofi.OrgFarmacy_id = ofm.OrgFarmacy_id";
						$apt .= " and ofi.Lpu_id = :Lpu_id";

						//если пришел идентификатор отделения, то ищем идентификатор подразделения
						if (!empty($data['LpuSection_id'])) {
							//получение подразделения по отделению
							$query = "
								select top 1
									LpuBuilding_id
								from
									v_LpuSection with (nolock)
								where
									LpuSection_id = :LpuSection_id
							";
							$data['LpuBuilding_id'] = $this->getFirstResultFromQuery($query, array(
								'LpuSection_id' => $data['LpuSection_id']
							));
						}

						//если есть идентификатор подразделения, то проверяем прикрепления к подразделениям
						if (!empty($data['LpuBuilding_id'])) {
							//проверям есть ли прикрепление подразделения к каким либо аптекам
							$query = "
								select
									count(ofi.OrgFarmacyIndex_id) as cnt
								from
									v_OrgFarmacyIndex ofi with (nolock)
								where
									ofi.Lpu_id = :Lpu_id and
									ofi.LpuBuilding_id = :LpuBuilding_id;
							";
							$cnt_data = $this->getFirstRowFromQuery($query, array(
								'Lpu_id' => $data['Lpu_id'],
								'LpuBuilding_id' => $data['LpuBuilding_id']
							));

							if (!empty($cnt_data['cnt'])) {
								$withArr[] = "ofi_list as (
									select
										i_ofi.OrgFarmacy_id,
										i_ofi.Lpu_id,
										i_ofi.WhsDocumentCostItemType_id,
										i_ofi.Storage_id,
										i_ofi.OrgFarmacyIndex_IsNarko,
										ofi_cnt.storage_cnt
									from
										v_OrgFarmacyIndex i_ofi with (nolock)
										outer apply (
											select
												sum(case when ii_ofi.Storage_id is not null then 1 else 0 end) as storage_cnt
											from
												v_OrgFarmacyIndex ii_ofi with (nolock)
											where
												ii_ofi.Lpu_id = i_ofi.Lpu_id and
												ii_ofi.LpuBuilding_id = i_ofi.LpuBuilding_id and
												ii_ofi.OrgFarmacy_id = i_ofi.OrgFarmacy_id and
												ii_ofi.WhsDocumentCostItemType_id = i_ofi.WhsDocumentCostItemType_id
										) ofi_cnt
									where
										i_ofi.Lpu_id = :Lpu_id and
										i_ofi.LpuBuilding_id = :LpuBuilding_id 
								)";
								$ofi_subquery = "
									left join ofi_list ofi with(nolock) on ofi.OrgFarmacy_id = ofm.OrgFarmacy_id		
									left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
									left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
									left join rls.v_ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
									outer apply (
										select (case when isnull(am.NARCOGROUPID, 0) = 2 then 2 else 1 end) as IsNarko
									) IsNarko
								";

								//$apt .= " and ofi.Lpu_id = :Lpu_id"; //это условие проставляется в верхних ветках поэтому дублировать его тут не надо
								$apt .= " and (ofi.storage_cnt = 0 or (ofi.Storage_id = dor.Storage_id and ofi.OrgFarmacyIndex_IsNarko = IsNarko.IsNarko))"; //если для подразделения указано прикрепление к конкретным складам, то склад остатков должен совпадать со складом прикрепления, кроме того должен учитываться признак наркотики
								$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
								if (!empty($data['WhsDocumentCostItemType_id'])) { //прикрепление к подразделению производится всегда по определенной программе ЛЛО, поэтому если с формы передана программа, ищем прикрепление по ней
									$apt .= " and ofi.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
									$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
								}
							}
						}
					}
				}
				if (!empty($ras) && !empty($apt)) {
					$withFilterList[] = "(($ras) or ($apt))";
					$withFilterList[] = "dor.Storage_id is not null";
				} else if (!empty($ras) || !empty($apt)) {
					$withFilterList[] = empty($ras) ? $apt : $ras;
					$withFilterList[] = "dor.Storage_id is not null";
				}

				$withArr[] = "ostat_list as (
					select distinct
						d.Drug_id,
						d.DrugComplexMnn_id
					from
						v_DrugOstatRegistry dor with (nolock)
						inner join rls.v_Drug d with(nolock) on d.Drug_id = dor.Drug_id
						left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						left join v_DrugShipment dsh with(nolock) on dsh.DrugShipment_id = dor.DrugShipment_id
						left join v_Contragent c with(nolock) on c.Org_id = dor.Org_id
						left join v_ContragentType ct with(nolock) on ct.ContragentType_id = c.ContragentType_id
						left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
						left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
						left join v_OrgFarmacy ofm with(nolock) on ofm.Org_id = dor.Org_id
						{$ofi_subquery}
					where ".implode(' and ',$withFilterList)."
				)";

				$filterList[] = "exists(select top 1 DrugComplexMnn_id from ostat_list with (nolock) where DrugComplexMnn_id = dcm.DrugComplexMnn_id)";
			}

			//для Москвы действует дополнительный фильтр на наличие медикаментов с комплексным МНН в номенклатурном справочнике и в справочнике СПО УЛО, при условии что выписка идет из ЖНВЛП
			if ($region_nick == 'msk' && $options['select_drug_from_list'] == 'jnvlp') {
				$spo_ulo_filters = array();

				if (!empty($data['Date'])) {
					$spo_ulo_filters[] = "(
						i_sud.SPOULODrug_begDT is null or
						i_sud.SPOULODrug_begDT <= :Date
					)";
					$spo_ulo_filters[] = "(
						i_sud.SPOULODrug_endDT is null or
						i_sud.SPOULODrug_endDT >= :Date
					)";
					$queryParams['Date'] = $data['Date'];
				}

				if (!empty($data['PrivilegeType_id']) && !empty($data['WhsDocumentCostItemType_id'])) {
					$query = "
						declare
							@PrivilegeType_id bigint = :PrivilegeType_id,
							@WhsDocumentCostItemType_id bigint = :WhsDocumentCostItemType_id,
							@DrugFinance_SysNick varchar(4),
							@WhsDocumentCostItemType_Nick varchar(3),
							@ReceptDiscount_Code int;
						
						set @DrugFinance_SysNick = (
							select top 1
								df.DrugFinance_SysNick
							from
								v_PrivilegeType pt with (nolock)
								left join v_DrugFinance df with (nolock) on df.DrugFinance_id = pt.DrugFinance_id
							where
								pt.PrivilegeType_id = @PrivilegeType_id
						);
						
						set @WhsDocumentCostItemType_Nick = (
							select top 1
								wdcit.WhsDocumentCostItemType_Nick
							from
								v_WhsDocumentCostItemType wdcit with (nolock)
							where
								wdcit.WhsDocumentCostItemType_id = @WhsDocumentCostItemType_id
						);
						
						set @ReceptDiscount_Code = (
							select top 1
								rd.ReceptDiscount_Code
							from
								v_PrivilegeType pt with (nolock)
								left join v_ReceptDiscount rd with (nolock) on rd.ReceptDiscount_id = pt.ReceptDiscount_id
							where
								pt.PrivilegeType_id = @PrivilegeType_id
						);
						
						select @DrugFinance_SysNick as DrugFinance_SysNick, @WhsDocumentCostItemType_Nick as WhsDocumentCostItemType_Nick, @ReceptDiscount_Code as ReceptDiscount_Code;
					";
					$priv_data = $this->getFirstRowFromQuery($query, array(
						'PrivilegeType_id' => $data['PrivilegeType_id'],
						'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']
					));
					if (!empty($priv_data['DrugFinance_SysNick'])) {
						if ($priv_data['DrugFinance_SysNick'] == 'fed' && $priv_data['WhsDocumentCostItemType_Nick'] == 'fl') { //федеральная льготная категория и программа «ОНЛС»
							$spo_ulo_filters[] = "(
								isnull(i_sud.fed, 0) = 1 or
								isnull(i_sud.reg, 0) = 1
							)";
						}
						if ($priv_data['DrugFinance_SysNick'] == 'reg' && $priv_data['WhsDocumentCostItemType_Nick'] == 'rl') { //региональная льготная категория и программа «РЛО»
							$spo_ulo_filters[] = "isnull(i_sud.reg, 0) = 1";
						}
						if (($priv_data['DrugFinance_SysNick'] == 'fed' || $priv_data['DrugFinance_SysNick'] == 'reg') && $priv_data['ReceptDiscount_Code'] == '1') { //указана федеральная льготная категория или региональная льготная категория со 100% скидкой
							$spo_ulo_filters[] = "isnull(i_sud.sale100, 0) = 1";
						}
						if ($priv_data['DrugFinance_SysNick'] == 'reg' && $priv_data['ReceptDiscount_Code'] == '2') { //указана региональная льготная категория с 50% скидкой
							$spo_ulo_filters[] = "isnull(i_sud.sale50, 0) = 1";
						}
					}
				}

				$spo_ulo_where = count($spo_ulo_filters) > 0 ? " and ".implode(" and ", $spo_ulo_filters) : "";

				$from .= "
					outer apply (
						select top 1
							i_d.DrugComplexMnn_id
						from
							rls.v_Drug i_d with (nolock)
							inner join rls.v_DrugNomen i_dn with (nolock) on i_dn.Drug_id = i_d.Drug_id
							inner join r50.SPOULODrug i_sud with (nolock) on cast(i_sud.NOMK_LS as varchar) = i_dn.DrugNomen_Code
						where
							i_d.DrugComplexMnn_id = dcm.DrugComplexMnn_id
							{$spo_ulo_where}
					) drug_nomen
				";
				$filterList[] = "drug_nomen.DrugComplexMnn_id is not null";
			}

			$filterList[] = 'dcm.DrugComplexMnnGroupType_id = 2';
			$additionFields = count($additionFieldsArr)>0 ? ','.implode(',', $additionFieldsArr) : '';
			$with = count($withArr)>0 ? "with\n".implode(",\n",$withArr) : '';

			$query = '';

			if ( !empty($with) ) {
				$query = "
					-- addit with
					{$with}
					-- end addit with
				";
			}

			$query .= "
				select distinct top 500
					-- select
					dcm.DrugComplexMnn_id,
					RTRIM(dcm.DrugComplexMnn_RusName) as DrugComplexMnn_Name,
					dcmn.ACTMATTERS_id as Actmatters_id
					{$additionFields}
					-- end select
				from
					-- from
					{$from}
					-- end from
				where
					-- where
					" . implode(" and ", $filterList) . "
					-- end where
				order by
					-- order by
					DrugComplexMnn_Name
					-- end order by
			";
		}

		//die(getDebugSQL($query, $queryParams));

		if ($data['paging']) {
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit'], 'distinct'), $queryParams);
			$result_count = $this->db->query(getCountSQLPH($query), $queryParams);

			if (is_object($result_count))
			{
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			} else {
				$count = 0;
			}
			if (is_object($result)) {
				$response = array();
				$response['data'] = $result->result('array');
				$response['totalCount'] = $count;
				return $response;
			} else {
				return false;
			}
		} else {
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	 *	Получение списка комплексных МНН (выборка из ЖНВЛП)
	 */
	function loadDrugComplexMnnJnvlpList($data) {
		$filterList  = array();
		$queryParams = array();

		//определение регистра по программе ЛЛО
		$query = "
			select
				PersonRegisterType_id
			from
				v_WhsDocumentCostItemType with (nolock)
			where
				WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id;	
		";
		$wdcit_id = $this->getFirstResultFromQuery($query, array(
			'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']
		));

		$filterList[] = "dnl.PersonRegisterType_id = :PersonRegisterType_id"; // Программа ЛЛО
		$queryParams['PersonRegisterType_id'] = !empty($wdcit_id) ? $wdcit_id : null;

		$filterList[] = "
			(case when exists(select top 1 DrugNormativeListSpec_id from v_DrugNormativeListSpecFormsLink with (nolock) where DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id)
				then dnlsfl.DrugNormativeListSpecForms_id
				else isnull(dcm.CLSDRUGFORMS_ID,0)
			end) = isnull(dcm.CLSDRUGFORMS_ID,0)
		";

		if ( !empty($data['Date']) ) {
			$filterList[] = "(dnls.DrugNormativeListSpec_BegDT is null or dnls.DrugNormativeListSpec_BegDT <= :Date)";
			$filterList[] = "(dnls.DrugNormativeListSpec_EndDT is null or dnls.DrugNormativeListSpec_EndDT > :Date)";
			$queryParams['Date'] = $data['Date'];
		}

		if ( !empty($data['query']) ) {
			switch ( $data['mode'] ) {
				case 'any':
					$data['query'] = "%" . $data['query'] . "%";
					break;

				default:
					$data['query'] .= "%";
					break;
			}

			$filterList[] = "dcm.DrugComplexMnn_RusName like :query";
			$queryParams['query'] = $data['query'];
		}

		$query = "
			select distinct
				-- select
				dcm.DrugComplexMnn_id,
				RTRIM(dcm.DrugComplexMnn_RusName) as DrugComplexMnn_Name,
				dcmn.ACTMATTERS_id as Actmatters_id
				-- end select
			from
				-- from
				v_DrugNormativeListSpec dnls with(nolock)
				inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.ActMatters_id = dnls.DrugNormativeListSpecMNN_id
				inner join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnnName_id = dcmn.DrugComplexMnnName_id
				inner join v_DrugNormativeList dnl with(nolock) on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
				left join v_DrugNormativeListSpecFormsLink dnlsfl with(nolock) on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
					and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id				
				-- end from
			where
				-- where
				" . implode(" and ", $filterList) . "
				-- end where
			order by
			-- order by
				DrugComplexMnn_Name
				-- end order by
		";

		if ($data['paging']) {
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit'], 'distinct'), $queryParams);
			$result_count = $this->db->query(getCountSQLPH($query), $queryParams);

			if (is_object($result_count)) {
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			} else {
				$count = 0;
			}
			if (is_object($result)) {
				$response = array();
				$response['data'] = $result->result('array');
				$response['totalCount'] = $count;
				return $response;
			} else {
				return false;
			}
		} else {
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadDrugRlsList($data, $options) {
		$filterList  = array('1=1');
		$queryParams = array();
		$additionFieldsArr = array();
		$withArr = array();
		$from = '';
		$region_nick = getRegionNick();

		if ($data['recept_drug_ostat_viewing'] != -1) {
			$options['recept_drug_ostat_viewing'] = $data['recept_drug_ostat_viewing'];
		}
		if ($data['recept_drug_ostat_control'] != -1) {
			$options['recept_drug_ostat_control'] = $data['recept_drug_ostat_control'];
		}
		if ($data['recept_empty_drug_ostat_allow'] != -1) {
			$options['recept_empty_drug_ostat_allow'] = $data['recept_empty_drug_ostat_allow'];
		}
		if (!empty($data['select_drug_from_list'])) {
			$options['select_drug_from_list'] = $data['select_drug_from_list'];
		}

		if ( !empty($data['Drug_rlsid']) ) {
			$query = "
				select
					 d.Drug_id as Drug_rlsid
					,d.DrugComplexMnn_id
					,d.Drug_Code
					,RTRIM(d.Drug_Name) as Drug_Name
				from
					rls.v_Drug d with (nolock)
				where
					d.Drug_id = :Drug_rlsid
			";
			$queryParams['Drug_rlsid'] = $data['Drug_rlsid'];
		} else {
			$lpuid="";

			if($data['Lpu_id']>0){
				//$filterList[] = "l.Lpu_id = :Lpu_id"; // Текущая МО
				//$lpuid="inner join v_Lpu l with (nolock) on l.Org_id = dor.Org_id";
				$queryParams['Lpu_id'] = $data['Lpu_id'];
			}

			if ( !empty($data['DrugComplexMnn_id']) ) {
				$filterList[] = "d.DrugComplexMnn_id = :DrugComplexMnn_id";
				$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
			}

			if ( !empty($data['query']) ) {
				switch ( $data['mode'] ) {
					case 'any':
						$data['query'] = "%" . $data['query'] . "%";
						break;

					default:
						$data['query'] .= "%";
						break;
				}

				$filterList[] = "d.Drug_Name like :query";
				$queryParams['query'] = $data['query'];
			}

			$ostat_type = 0;
			if ($options['recept_drug_ostat_viewing']) {
				$ostat_type = 1;
			}
			if ($options['recept_drug_ostat_control']) {
				$ostat_type = 2;
			}
			if ($options['recept_empty_drug_ostat_allow']) {
				$ostat_type = 3;
			}

			$drug_from_list = (!empty($options['select_drug_from_list']) ? $options['select_drug_from_list'] : '');
			if (in_array($options['select_drug_from_list'], array('allocation', 'request_and_allocation'))) {
				$drug_from_list = 'allocation';
			}
			if ($drug_from_list == 'jnvlp' && $region_nick == 'msk') { //для Москвы: если выписка ведется из ЖНВЛП, то базовой выборкой является справочник медикаментов
				$drug_from_list = 'drug_table';
			}

			if (!empty($drug_from_list)) {
				switch($drug_from_list) {
					case 'drug_table':
						$from = "
							rls.v_Drug d with(nolock)
						";
						break;
					case 'jnvlp':
						$additionFieldsArr[] = "isnull(list.DrugNormativeListSpec_IsVK, 1) as Drug_IsKEK";
						$withFilterList = array('1=1');
						$withFilterList[] = "
							(case when exists(select top 1 DrugNormativeListSpec_id from v_DrugNormativeListSpecFormsLink with (nolock) where DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id)
								then dnlsfl.DrugNormativeListSpecForms_id
								else isnull(dcm.CLSDRUGFORMS_ID,0)
							end) = isnull(dcm.CLSDRUGFORMS_ID,0)
						";
						if (!empty($data['Date'])) {
							$withFilterList[] = "dnl.DrugNormativeList_BegDT <= :Date";
							$withFilterList[] = "(dnl.DrugNormativeList_EndDT > :Date or dnl.DrugNormativeList_EndDT is null)";
							$queryParams['Date'] = $data['Date'];
						}
						if (!empty($data['PersonRegisterType_id'])) {
							$withFilterList[] = "dnl.PersonRegisterType_id = :PersonRegisterType_id";
							$queryParams['PersonRegisterType_id'] = $data['PersonRegisterType_id'];
						}
						if (!empty($data['EvnRecept_IsKEK'])) {
							$withFilterList[] = "isnull(dnls.DrugNormativeListSpec_IsVK,1) = :EvnRecept_IsKEK";
							$queryParams['EvnRecept_IsKEK'] = $data['EvnRecept_IsKEK'];
						}
						if (!empty($data['DrugComplexMnn_id'])) {
							$withFilterList[] = "dcm.DrugComplexMnn_id = :DrugComplexMnn_id";
							$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
						}
						if (!empty($data['WhsDocumentCostItemType_id'])){
							$withFilterList[] = "dnl.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id"; // Программа ЛЛО
							$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
						}
						$withFilterList[] = 'dcm.DrugComplexMnnGroupType_id = 2';

						if (!empty($data['is_mi_1']) && ($data['is_mi_1'] == 'true')){
							//$withFilterList[] = "ntfr.PARENTID not in (1, 176)";
							//$withFilterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
							$filterList[] = "ntfr.PARENTID not in (1, 176)";
							$filterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
						}

						if ((!empty($data['EvnRecept_IsMnn']) && $data['EvnRecept_IsMnn'] == 2) || (empty($data['EvnRecept_IsMnn']) && $region_nick == 'msk')) { //для Москвы, отстутсвие значения в поле "выписка по мнн" в двнном блоке кода считается идентичным значению "да"
							$withArr[] = "normativ_list as (
								select
									dcm.DrugComplexMnn_id,
									max(dnls.DrugNormativeListSpec_IsVK) as DrugNormativeListSpec_IsVK
								from
									v_DrugNormativeListSpec dnls with(nolock)
									inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.ActMatters_id = dnls.DrugNormativeListSpecMNN_id
									inner join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnnName_id = dcmn.DrugComplexMnnName_id
									inner join v_DrugNormativeList dnl with(nolock) on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
									left join v_DrugNormativeListSpecFormsLink dnlsfl with(nolock) on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
										and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
									outer apply ( --для определения медизделий
										select top 1
											i_p.NTFRID
										from
											rls.v_Drug i_d with (nolock)
											left join rls.v_Prep i_p with (nolock) on i_p.Prep_id = i_d.DrugPrep_id
										where
											i_d.DrugComplexMnn_id = dcm.DrugComplexMnn_id and
											i_p.NTFRID is not null
									) p
									--left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
								where ".implode(' and ',$withFilterList)."
								group by
									dcm.DrugComplexMnn_id
							)";
						} else {
							$withArr[] = "normativ_list as (
								select
									dcm.DrugComplexMnn_id,
									max(dnls.DrugNormativeListSpec_IsVK) as DrugNormativeListSpec_IsVK
								from
									v_DrugNormativeListSpecTorgLink dnlstl with(nolock)
									inner join v_DrugNormativeListSpec dnls with(nolock) on dnls.DrugNormativeListSpec_id = dnlstl.DrugNormativeListSpec_id
									inner join rls.v_Prep p with(nolock) on p.TRADENAMEID = dnlstl.DrugNormativeListSpecTorg_id
									inner join rls.v_Drug d with(nolock) on d.DrugPrep_id = p.Prep_id
									inner join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
									inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id /*and dcmn.ActMatters_id is null*/
									inner join v_DrugNormativeList dnl with(nolock) on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
									left join v_DrugNormativeListSpecFormsLink dnlsfl with(nolock) on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
										and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
									--left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
								where ".implode(' and ',$withFilterList)."
								group by
									dcm.DrugComplexMnn_id
							)";
						}
						$from = "
							normativ_list list with(nolock)
							inner join rls.v_Drug d with(nolock) on d.DrugComplexMnn_id = list.DrugComplexMnn_id
						";
						break;

					case 'request':
						//блок "нормативный перечень" почти полностью скопирован из раздела ЖНВЛП (выглядит сомнительно, но так прописано в ТЗ #178323)
						$additionFieldsArr[] = "isnull(n_list.DrugNormativeListSpec_IsVK, 1) as Drug_IsKEK";
						$withFilterList = array('1=1');
						$withFilterList[] = "
							(case when exists(select top 1 DrugNormativeListSpec_id from v_DrugNormativeListSpecFormsLink with (nolock) where DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id)
								then dnlsfl.DrugNormativeListSpecForms_id
								else isnull(dcm.CLSDRUGFORMS_ID,0)
							end) = isnull(dcm.CLSDRUGFORMS_ID,0)
						";
						if (!empty($data['Date'])) {
							$withFilterList[] = "dnl.DrugNormativeList_BegDT <= :Date";
							$withFilterList[] = "(dnl.DrugNormativeList_EndDT > :Date or dnl.DrugNormativeList_EndDT is null)";
							$queryParams['Date'] = $data['Date'];
						}
						if (!empty($data['PersonRegisterType_id'])) {
							$withFilterList[] = "dnl.PersonRegisterType_id = :PersonRegisterType_id";
							$queryParams['PersonRegisterType_id'] = $data['PersonRegisterType_id'];
						}
						if (!empty($data['EvnRecept_IsKEK'])) {
							$withFilterList[] = "isnull(dnls.DrugNormativeListSpec_IsVK,1) = :EvnRecept_IsKEK";
							$queryParams['EvnRecept_IsKEK'] = $data['EvnRecept_IsKEK'];
						}
						if (!empty($data['DrugComplexMnn_id'])) {
							$withFilterList[] = "dcm.DrugComplexMnn_id = :DrugComplexMnn_id";
							$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
						}
						if (!empty($data['WhsDocumentCostItemType_id'])){
							$withFilterList[] = "dnl.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id"; // Программа ЛЛО
							$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
						}
						$withFilterList[] = 'dcm.DrugComplexMnnGroupType_id = 2';

						if (!empty($data['is_mi_1']) && ($data['is_mi_1'] == 'true')){
							$filterList[] = "ntfr.PARENTID not in (1, 176)";
							$filterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
						}

						if ((!empty($data['EvnRecept_IsMnn']) && $data['EvnRecept_IsMnn'] == 2) || (empty($data['EvnRecept_IsMnn']) && $region_nick == 'msk')) { //для Москвы, отстутсвие значения в поле "выписка по мнн" в двнном блоке кода считается идентичным значению "да"
							$withArr[] = "normativ_list as (
								select
									dcm.DrugComplexMnn_id,
									max(dnls.DrugNormativeListSpec_IsVK) as DrugNormativeListSpec_IsVK
								from
									v_DrugNormativeListSpec dnls with(nolock)
									inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.ActMatters_id = dnls.DrugNormativeListSpecMNN_id
									inner join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnnName_id = dcmn.DrugComplexMnnName_id
									inner join v_DrugNormativeList dnl with(nolock) on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
									left join v_DrugNormativeListSpecFormsLink dnlsfl with(nolock) on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
										and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
									outer apply ( --для определения медизделий
										select top 1
											i_p.NTFRID
										from
											rls.v_Drug i_d with (nolock)
											left join rls.v_Prep i_p with (nolock) on i_p.Prep_id = i_d.DrugPrep_id
										where
											i_d.DrugComplexMnn_id = dcm.DrugComplexMnn_id and
											i_p.NTFRID is not null
									) p
									--left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
								where ".implode(' and ',$withFilterList)."
								group by
									dcm.DrugComplexMnn_id
							)";
						} else {
							$withArr[] = "normativ_list as (
								select
									dcm.DrugComplexMnn_id,
									max(dnls.DrugNormativeListSpec_IsVK) as DrugNormativeListSpec_IsVK
								from
									v_DrugNormativeListSpecTorgLink dnlstl with(nolock)
									inner join v_DrugNormativeListSpec dnls with(nolock) on dnls.DrugNormativeListSpec_id = dnlstl.DrugNormativeListSpec_id
									inner join rls.v_Prep p with(nolock) on p.TRADENAMEID = dnlstl.DrugNormativeListSpecTorg_id
									inner join rls.v_Drug d with(nolock) on d.DrugPrep_id = p.Prep_id
									inner join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
									inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id /*and dcmn.ActMatters_id is null*/
									inner join v_DrugNormativeList dnl with(nolock) on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
									left join v_DrugNormativeListSpecFormsLink dnlsfl with(nolock) on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
										and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
									--left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
								where ".implode(' and ',$withFilterList)."
								group by
									dcm.DrugComplexMnn_id
							)";
						}

						//блок "заявка"
						$withFilterList = array('1=1');

						/*if (!empty($data['EvnRecept_IsMnn'])) {
							if ($data['EvnRecept_IsMnn'] == 2) {
								$withFilterList[] = "dcmn.ActMatters_id is not null";
							} else {
								$withFilterList[] = "dcmn.ActMatters_id is null";
							}
						}*/
						$withFilterList[] = 'dcm.DrugComplexMnnGroupType_id = 2';
						$withFilterList[] = 'drs.DrugRequestStatus_Code = 3';	//Утвержденные заявки

						if (!empty($data['Date'])) {
							$withFilterList[] = ":Date between drp.DrugRequestPeriod_begDate and drp.DrugRequestPeriod_endDate";
							$queryParams['Date'] = $data['Date'];
						}

						$queryParams['Person_id'] = !empty($data['Person_id']) ? $data['Person_id'] : null;
						if (!empty($data['Person_id'])) {
							$withFilterList[] = "(drpo.Kolvo > 0 or drpo_reserve.Kolvo > 0)";
						} else {
							$withFilterList[] = "drpo_reserve.Kolvo > 0";
						}

						$withFilterList[] = "(
                            dr.MedPersonal_id = :MedPersonal_id or
                            (
                                is_hms.HeadMedSpec_id is not null and -- заявка главного внештатного специалиста
                                is_priv_all.isPrivilegeAllowed = 1 -- разрешена выписка из заявок главных внештатных специалистов
                            )
                        )";
						$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];

						if (!empty($data['is_mi_1']) && ($data['is_mi_1'] == 'true')){
							//$withFilterList[] = "ntfr.PARENTID not in (1, 176)";
							//$withFilterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
							$filterList[] = "ntfr.PARENTID not in (1, 176)";
							$filterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
						}

						$withArr[] = "request_list as (
							select distinct
								dcm.DrugComplexMnn_id
							from
								v_DrugRequestRow drr with(nolock)
								inner join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = drr.DrugComplexMnn_id
								inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
								inner join v_DrugRequest dr with(nolock) on dr.DrugRequest_id = drr.DrugRequest_id
								inner join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
								inner join v_DrugRequestPeriod drp with(nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
                                outer apply ( -- признак разрешения всем МО на выписку льготных рецептов из заявок главных внештатных специалистов
                                    select
                                        isnull(i_yn.YesNo_Code, 0) as isPrivilegeAllowed -- 0 - Нет; 1 - Да.
                                    from
                                        v_WhsDocumentCostItemType i_wdcit with (nolock)
                                        left join YesNo i_yn with (nolock) on i_yn.YesNo_id = i_wdcit.WhsDocumentCostItemType_isPrivilegeAllowed
                                    where
                                        i_wdcit.PersonRegisterType_id = dr.PersonRegisterType_id
                                ) is_priv_all
                                outer apply ( -- проверка на включение врача заявки в перечень главных внештатных специалистов
                                    select top 1
                                        i_hms.HeadMedSpec_id
                                    from
                                        v_MedPersonal i_mp with (nolock)
                                        left join persis.v_MedWorker i_mw with (nolock) on i_mw.Person_id = i_mp.Person_id
                                        inner join v_HeadMedSpec i_hms with (nolock) on i_hms.MedWorker_id = i_mw.MedWorker_id
                                    where
                                        i_mp.MedPersonal_id = dr.MedPersonal_id and
                                        drp.DrugRequestPeriod_begDate between i_hms.HeadMedSpec_begDT and i_hms.HeadMedSpec_endDT
                                ) is_hms
                                outer apply (
                                    select
                                        sum(i_drpo.DrugRequestPersonOrder_OrdKolvo) as Kolvo,
                                        max(i_drpo.Person_id) as Person_id
                                    from
                                        v_DrugRequestPersonOrder i_drpo with(nolock)
                                    where
                                        :Person_id is not null and
                                        drr.DrugRequest_id = dr.DrugRequest_id and
                                        i_drpo.Person_id = :Person_id and
                                        (
                                            i_drpo.DrugComplexMnn_id = drr.DrugComplexMnn_id or
                                            i_drpo.Tradenames_id = drr.TRADENAMES_id
                                        )
                                ) drpo
                                outer apply (
                                    select
                                        (isnull(drr.DrugRequestRow_Kolvo, 0) - sum(i_drpo.DrugRequestPersonOrder_OrdKolvo)) as Kolvo
                                    from
                                        v_DrugRequestPersonOrder i_drpo with(nolock)
                                    where
                                        drr.DrugRequest_id = dr.DrugRequest_id and
                                        (
                                            i_drpo.DrugComplexMnn_id = drr.DrugComplexMnn_id or
                                            i_drpo.Tradenames_id = drr.TRADENAMES_id
                                        )
                                ) drpo_reserve
								left join v_PersonState ps with(nolock) on ps.Person_id = drpo.Person_id
								outer apply ( --для определения медизделий
									select top 1
										i_p.NTFRID
									from
										rls.v_Drug i_d with (nolock)
										left join rls.v_Prep i_p with (nolock) on i_p.Prep_id = i_d.DrugPrep_id
									where
										i_d.DrugComplexMnn_id = drr.DrugComplexMnn_id and
										i_p.NTFRID is not null
								) p
								--left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
							where ".implode(' and ',$withFilterList)."
						)";

						$from = "
							request_list list with(nolock)
							inner join normativ_list n_list with(nolock) on n_list.DrugComplexMnn_id = list.DrugComplexMnn_id
							inner join rls.v_Drug d with(nolock) on d.DrugComplexMnn_id = list.DrugComplexMnn_id 
						";
						break;

					case 'allocation':
						$withFilterList = array('1=1');
						$lpuid = "";
						$a_list_from = "";
						$withFilterList[] = "sat.SubAccountType_Code = 1"; // "Доступно"
						$withFilterList[] = "dor.DrugOstatRegistry_Kolvo > 0"; // На остатках должны быть медикаменты
						$withFilterList[] = "isnull(isdef.YesNo_Code, 0) = 0"; // Исключение забракованных серий
						/*if (!empty($data['EvnRecept_IsMnn'])) {
							if ($data['EvnRecept_IsMnn'] == 2) {
								$withFilterList[] = "dcmn.ActMatters_id is not null";
							} else {
								$withFilterList[] = "dcmn.ActMatters_id is null";
							}
						}*/
						if (!empty($data['DrugOstatRegistry_id'])) {
							$withFilterList[] = "dor.DrugOstatRegistry_id = :DrugOstatRegistry_id";
							$queryParams['DrugOstatRegistry_id'] = $data['DrugOstatRegistry_id'];
						}
						if ($data['Lpu_id']!="" && $data['Lpu_id']>0) {
							$withFilterList[] = "l.Lpu_id = :Lpu_id"; // Текущая МО
							$lpuid = "inner join v_Lpu l with (nolock) on l.Org_id = dor.Org_id";
							$queryParams['Lpu_id'] = $data['Lpu_id'];
							if (!empty($data['WhsDocumentCostItemType_id'])){
								$withFilterList[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id"; // Программа ЛЛО
								$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
							}
						}

						if (!empty($data['is_mi_1']) && ($data['is_mi_1'] == 'true')){
							//$withFilterList[] = "ntfr.PARENTID not in (1, 176)";
							//$withFilterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
							$filterList[] = "ntfr.PARENTID not in (1, 176)";
							$filterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
						}

						if (!empty($data['WhsDocumentCostItemType_id'])) {
							//проверка программы ЛЛО
							$wdcit_query = "
								select
									isnull(yn.YesNo_Code, 0) as isPersonAllocation
								from
									v_WhsDocumentCostItemType wdcit with (nolock)
									left join v_YesNo yn with (nolock) on yn.YesNo_id = wdcit.WhsDocumentCostItemType_isPersonAllocation
								where
									wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id;
							";
							$wdcit_data = $this->getFirstRowFromQuery($wdcit_query, array(
								'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']
							));

							if (!empty($wdcit_data['isPersonAllocation'])) { //если установлен признак выписки рецепта по персональной разнарядке
								//проверка включения медикамента и пациента в разнарядку

								$wdcit_where = "";
								$drpo_where = "";

								if (!empty($data['DrugFinance_id'])) {
									$wdcit_where .= " and ii_wdcit.DrugFinance_id = :DrugFinance_id ";
									$drpo_where .= " and i_drr.DrugFinance_id = :DrugFinance_id ";
									$queryParams['DrugFinance_id'] = $data['DrugFinance_id'];
								}

								if (!empty($lpuid)) {
									$drpo_where .= " and i_dr.Lpu_id = l.Lpu_id ";
								}

								$a_list_from .= "
									outer apply (
										select top 1
											i_drpo.DrugRequestPersonOrder_id
										from
											v_DrugRequestPersonOrder i_drpo with (nolock)
											left join v_DrugRequest i_dr with(nolock) on i_dr.DrugRequest_id = i_drpo.DrugRequest_id
											left join v_DrugRequestPeriod i_drp with(nolock) on i_drp.DrugRequestPeriod_id = i_dr.DrugRequestPeriod_id
											outer apply (
												select top 1
													ii_drr.DrugFinance_id
												from
													v_DrugRequestRow ii_drr with (nolock)
												where
													ii_drr.DrugRequest_id = i_drpo.DrugRequest_id and
													ii_drr.DrugComplexMnn_id = i_drpo.DrugComplexMnn_id and
													isnull(ii_drr.TRADENAMES_id, 0) = isnull(i_drpo.Tradenames_id, 0)
												order by
													ii_drr.DrugRequestRow_id
											) i_drr
											outer apply (
												select top 1
													ii_wdcit.WhsDocumentCostItemType_id
												from
													v_WhsDocumentCostItemType ii_wdcit with (nolock)
												where
													ii_wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and
													isnull(ii_wdcit.PersonRegisterType_id, 0) = isnull(i_dr.PersonRegisterType_id, 0)
													{$wdcit_where}
											) i_wdcit
										where
											i_drpo.Person_id = :Person_id and
											i_drpo.DrugComplexMnn_id = dcm.DrugComplexMnn_id and
											i_wdcit.WhsDocumentCostItemType_id is not null and
											i_drp.DrugRequestPeriod_begDate <= :Date and
											i_drp.DrugRequestPeriod_endDate >= :Date and
											i_drpo.DrugRequestPersonOrder_OrdKolvo > 0
											{$drpo_where}
									) drpo
								";

								$withFilterList[] = "drpo.DrugRequestPersonOrder_id is not null";

								$queryParams['Person_id'] = $data['Person_id'];
								$queryParams['Date'] = $data['Date'];
							}
						}

						$withArr[] = "allocation_list as (
							select distinct
								d.Drug_id,
								dcm.DrugComplexMnn_id,
								dor.DrugOstatRegistry_Cost as Drug_Price
							from
								v_DrugOstatRegistry dor with (nolock)
								left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
								left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
								left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
								left join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
								left join v_DrugShipment dsh with(nolock) on dsh.DrugShipment_id = dor.DrugShipment_id
								left join rls.v_Prep p with(nolock) on p.Prep_id = d.DrugPrep_id
								--left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
								left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
								left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
								{$lpuid}
								{$a_list_from}
							where ".implode(' and ',$withFilterList)."
						)";

						$additionFieldsArr[] = "list.Drug_Price";

						$from = "
							allocation_list list with(nolock)
							inner join rls.v_Drug d with(nolock) on d.Drug_id = list.Drug_id
						";
						break;

					case 'request_and_allocation':
						$withFilterList = array('1=1');
						$lpuid = "";
						$withFilterList[] = "drs.DrugRequestStatus_Code = 3";	//Утвержденные заявки
						$withFilterList[] = "sat.SubAccountType_Code = 1"; 		// "Доступно"
						$withFilterList[] = "dor.DrugOstatRegistry_Kolvo > 0"; 	// На остатках должны быть медикаменты
						$withFilterList[] = "isnull(isdef.YesNo_Code, 0) = 0"; // Исключение забракованных серий
						/*if (!empty($data['EvnRecept_IsMnn'])) {
							if ($data['EvnRecept_IsMnn'] == 2) {
								$withFilterList[] = "dcmn.ActMatters_id is not null";
							} else {
								$withFilterList[] = "dcmn.ActMatters_id is null";
							}
						}*/
						if ($data['Lpu_id']!="" && $data['Lpu_id']>0) {
							$withFilterList[] = "l.Lpu_id = :Lpu_id"; // Текущая МО
							$lpuid = "inner join v_Lpu l with (nolock) on l.Org_id = dor.Org_id";
							$queryParams['Lpu_id'] = $data['Lpu_id'];
							if (!empty($data['WhsDocumentCostItemType_id'])){
								$withFilterList[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id"; // Программа ЛЛО
								$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
							}
						}

						if (!empty($data['DrugOstatRegistry_id'])) {
							$withFilterList[] = "dor.DrugOstatRegistry_id = :DrugOstatRegistry_id";
							$queryParams['DrugOstatRegistry_id'] = $data['DrugOstatRegistry_id'];
						}

						if (!empty($data['Date'])) {
							$withFilterList[] = ":Date between drp.DrugRequestPeriod_begDate and drp.DrugRequestPeriod_endDate";
							$queryParams['Date'] = $data['Date'];
						}

						if (!empty($data['Person_id'])) {
							$withFilterList[] = "(drr.Person_id = :Person_id or drr.Person_id is null)";
							$queryParams['Person_id'] = $data['Person_id'];
							$withFilterList[] = "(drr.Person_id is not null or dr.MedPersonal_id = :MedPersonal_id)";
							$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
						} else {
							$withFilterList[] = "drr.Person_id is null";
							$withFilterList[] = "dr.MedPersonal_id = :MedPersonal_id";
							$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
						}

						if (!empty($data['is_mi_1']) && ($data['is_mi_1'] == 'true')){
							//$withFilterList[] = "ntfr.PARENTID not in (1, 176)";
							//$withFilterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
							$filterList[] = "ntfr.PARENTID not in (1, 176)";
							$filterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
						}

						$withArr[] = "rna_list as (
							select distinct
								d.Drug_id,
								dcm.DrugComplexMnn_id,
								dor.DrugOstatRegistry_Cost as Drug_Price
							from
								v_DrugOstatRegistry dor with (nolock)
								left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
								inner join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
								inner join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
								inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
								left join v_DrugShipment dsh with(nolock) on dsh.DrugShipment_id = dor.DrugShipment_id
								{$lpuid}
								inner join v_DrugRequestRow drr with(nolock) on drr.DrugComplexMnn_id = d.DrugComplexMnn_id
								inner join v_DrugRequest dr with(nolock) on dr.DrugRequest_id = drr.DrugRequest_id
								inner join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
								inner join v_DrugRequestPeriod drp with(nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
								left join v_PersonState ps with(nolock) on ps.Person_id = drr.Person_id
								left join rls.v_Prep p with(nolock) on p.Prep_id = d.DrugPrep_id
								--left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
								left join rls.v_PrepSeries psr with (nolock) on psr.PrepSeries_id = dor.PrepSeries_id
								left join v_YesNo isdef with (nolock) on isdef.YesNo_id = psr.PrepSeries_isDefect
							where ".implode(' and ',$withFilterList)."
						)";

						$additionFieldsArr[] = "list.Drug_Price";

						$from = "
							rna_list list with(nolock)
							inner join rls.v_Drug d with(nolock) on d.Drug_id = list.Drug_id
						";
						break;
				}
			} else {
				$from = "
					rls.v_Drug d with(nolock)
				";
			}

			if (
				$region_nick != 'msk' && ( //для Москвы контроль наличия остатков отключен вне зависимости от настроек
					($ostat_type == 1 && in_array($options['select_drug_from_list'], array('jnvlp','request')))
					|| $ostat_type == 2 || $ostat_type == 3
				)
			) {
				$withFilterList = array('1=1');

				$withFilterList[] = "sat.SubAccountType_Code = 1"; // "Доступно"
				$withFilterList[] = "dor.DrugOstatRegistry_Kolvo > 0"; // На остатках должны быть медикаменты
				$withFilterList[] = "isnull(isdef.YesNo_Code, 0) = 0"; // Исключение забракованных серий

				if (!empty($data['WhsDocumentCostItemType_id'])) {
					$withFilterList[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id"; // Программа ЛЛО
					$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
				}

				if (!empty($data['WhsDocumentSupply_id'])) {
					$withFilterList[] = "dsh.WhsDocumentSupply_id = :WhsDocumentSupply_id"; // Контракт на поставку
					$queryParams['WhsDocumentSupply_id'] = $data['WhsDocumentSupply_id'];
				}

				if (!empty($data['is_mi_1']) && ($data['is_mi_1'] == 'true')){
					//$withFilterList[] = "ntfr.PARENTID not in (1, 176)";
					//$withFilterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
					$filterList[] = "ntfr.PARENTID not in (1, 176)";
					$filterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
				}

				$ras = null;
				$apt = null;
				$ofi_subquery = "";
				if (!empty($options['recept_by_ras_drug_ostat']) && $options['recept_by_ras_drug_ostat'] && $ostat_type != 2) {
					$ras = "ct.ContragentType_SysNick = 'store'";
				}
				if (!empty($options['recept_by_farmacy_drug_ostat']) && $options['recept_by_farmacy_drug_ostat']) {
					$apt = "ct.ContragentType_SysNick = 'apt'";
					if (!empty($options['recept_farmacy_type']) && $options['recept_farmacy_type'] == 'mo_farmacy' && !empty($queryParams['Lpu_id'])) {
						$ofi_subquery = "left join v_OrgFarmacyIndex ofi with(nolock) on ofi.OrgFarmacy_id = ofm.OrgFarmacy_id";
						$apt .= " and ofi.Lpu_id = :Lpu_id";

						//если пришел идентификатор отделения, то ищем идентификатор подразделения
						if (!empty($data['LpuSection_id'])) {
							//получение подразделения по отделению
							$query = "
								select top 1
									LpuBuilding_id
								from
									v_LpuSection with (nolock)
								where
									LpuSection_id = :LpuSection_id
							";
							$data['LpuBuilding_id'] = $this->getFirstResultFromQuery($query, array(
								'LpuSection_id' => $data['LpuSection_id']
							));
						}

						//если есть идентификатор подразделения, то проверяем прикрепления к подразделениям
						if (!empty($data['LpuBuilding_id'])) {
							//проверям есть ли прикрепление подразделения к каким либо аптекам
							$query = "
								select
									count(ofi.OrgFarmacyIndex_id) as cnt
								from
									v_OrgFarmacyIndex ofi with (nolock)
								where
									ofi.Lpu_id = :Lpu_id and
									ofi.LpuBuilding_id = :LpuBuilding_id;
							";
							$cnt_data = $this->getFirstRowFromQuery($query, array(
								'Lpu_id' => $data['Lpu_id'],
								'LpuBuilding_id' => $data['LpuBuilding_id']
							));

							if (!empty($cnt_data['cnt'])) {
								$withArr[] = "ofi_list as (
									select
										i_ofi.OrgFarmacy_id,
										i_ofi.Lpu_id,
										i_ofi.WhsDocumentCostItemType_id,
										i_ofi.Storage_id,
										i_ofi.OrgFarmacyIndex_IsNarko,
										ofi_cnt.storage_cnt
									from
										v_OrgFarmacyIndex i_ofi with (nolock)
										outer apply (
											select
												sum(case when ii_ofi.Storage_id is not null then 1 else 0 end) as storage_cnt
											from
												v_OrgFarmacyIndex ii_ofi with (nolock)
											where
												ii_ofi.Lpu_id = i_ofi.Lpu_id and
												ii_ofi.LpuBuilding_id = i_ofi.LpuBuilding_id and
												ii_ofi.OrgFarmacy_id = i_ofi.OrgFarmacy_id and
												ii_ofi.WhsDocumentCostItemType_id = i_ofi.WhsDocumentCostItemType_id
										) ofi_cnt
									where
										i_ofi.Lpu_id = :Lpu_id and
										i_ofi.LpuBuilding_id = :LpuBuilding_id 
								)";
								$ofi_subquery = "
									left join ofi_list ofi with(nolock) on ofi.OrgFarmacy_id = ofm.OrgFarmacy_id		
									left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
									left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
									left join rls.v_ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
									outer apply (
										select (case when isnull(am.NARCOGROUPID, 0) = 2 then 2 else 1 end) as IsNarko
									) IsNarko
								";

								//$apt .= " and ofi.Lpu_id = :Lpu_id"; //это условие проставляется в верхних ветках поэтому дублировать его тут не надо
								$apt .= " and (ofi.storage_cnt = 0 or (ofi.Storage_id = dor.Storage_id and ofi.OrgFarmacyIndex_IsNarko = IsNarko.IsNarko))"; //если для подразделения указано прикрепление к конкретным складам, то склад остатков должен совпадать со складом прикрепления, кроме того должен учитываться признак наркотики
								$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
								if (!empty($data['WhsDocumentCostItemType_id'])) { //прикрепление к подразделению производится всегда по определенной программе ЛЛО, поэтому если с формы передана программа, ищем прикрепление по ней
									$apt .= " and ofi.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
									$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
								}
							}
						}
					}
				}
				if (!empty($ras) && !empty($apt)) {
					$withFilterList[] = "(($ras) or ($apt))";
					$withFilterList[] = "dor.Storage_id is not null";
				} else if (!empty($ras) || !empty($apt)) {
					$withFilterList[] = empty($ras) ? $apt : $ras;
					$withFilterList[] = "dor.Storage_id is not null";
				}

				$withArr[] = "ostat_list as (
					select distinct
						dor.Drug_id
					from
						v_DrugOstatRegistry dor with (nolock)
						left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						left join v_DrugShipment dsh with(nolock) on dsh.DrugShipment_id = dor.DrugShipment_id
						left join v_Contragent c with(nolock) on c.Org_id = dor.Org_id
						left join v_ContragentType ct with(nolock) on ct.ContragentType_id = c.ContragentType_id
						left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
						left join rls.v_Prep p with(nolock) on p.Prep_id = d.DrugPrep_id
						--left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID
						left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
						left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
						left join v_OrgFarmacy ofm with(nolock) on ofm.Org_id = dor.Org_id
						{$ofi_subquery}
					where ".implode(' and ',$withFilterList)."
				)";

				if ($options['recept_drug_ostat_control']) {
					$from .= "inner join ostat_list os with (nolock) on os.Drug_id = d.Drug_id";
				} else {
					$from .= "left join ostat_list os with (nolock) on os.Drug_id = d.Drug_id";
				}
			}

			if ($region_nick == 'msk') { //для Москвы действует дополнительный фильтр на наличие медикамента в номенклатурном справочнике
				$spo_ulo_filters = array();

				if (!empty($data['Date'])) {
					$spo_ulo_filters[] = "(
						i_sud.SPOULODrug_begDT is null or
						i_sud.SPOULODrug_begDT <= :Date
					)";
					$spo_ulo_filters[] = "(
						i_sud.SPOULODrug_endDT is null or
						i_sud.SPOULODrug_endDT >= :Date
					)";
					$queryParams['Date'] = $data['Date'];
				}

				if (!empty($data['PrivilegeType_id']) && !empty($data['WhsDocumentCostItemType_id'])) {
					$query = "
						declare
							@PrivilegeType_id bigint = :PrivilegeType_id,
							@WhsDocumentCostItemType_id bigint = :WhsDocumentCostItemType_id,
							@DrugFinance_SysNick varchar(4),
							@WhsDocumentCostItemType_Nick varchar(3),
							@ReceptDiscount_Code int;
						
						set @DrugFinance_SysNick = (
							select top 1
								df.DrugFinance_SysNick
							from
								v_PrivilegeType pt with (nolock)
								left join v_DrugFinance df with (nolock) on df.DrugFinance_id = pt.DrugFinance_id
							where
								pt.PrivilegeType_id = @PrivilegeType_id
						);
						
						set @WhsDocumentCostItemType_Nick = (
							select top 1
								wdcit.WhsDocumentCostItemType_Nick
							from
								v_WhsDocumentCostItemType wdcit with (nolock)
							where
								wdcit.WhsDocumentCostItemType_id = @WhsDocumentCostItemType_id
						);
						
						set @ReceptDiscount_Code = (
							select top 1
								rd.ReceptDiscount_Code
							from
								v_PrivilegeType pt with (nolock)
								left join v_ReceptDiscount rd with (nolock) on rd.ReceptDiscount_id = pt.ReceptDiscount_id
							where
								pt.PrivilegeType_id = @PrivilegeType_id
						);
						
						select @DrugFinance_SysNick as DrugFinance_SysNick, @WhsDocumentCostItemType_Nick as WhsDocumentCostItemType_Nick, @ReceptDiscount_Code as ReceptDiscount_Code;
					";
					$priv_data = $this->getFirstRowFromQuery($query, array(
						'PrivilegeType_id' => $data['PrivilegeType_id'],
						'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']
					));
					if (!empty($priv_data['DrugFinance_SysNick'])) {
						if ($priv_data['DrugFinance_SysNick'] == 'fed' && $priv_data['WhsDocumentCostItemType_Nick'] == 'fl') { //федеральная льготная категория и программа «ОНЛС»
							$spo_ulo_filters[] = "(
								isnull(i_sud.fed, 0) = 1 or
								isnull(i_sud.reg, 0) = 1
							)";
						}
						if ($priv_data['DrugFinance_SysNick'] == 'reg' && $priv_data['WhsDocumentCostItemType_Nick'] == 'rl') { //региональная льготная категория и программа «РЛО»
							$spo_ulo_filters[] = "isnull(i_sud.reg, 0) = 1";
						}
						if (($priv_data['DrugFinance_SysNick'] == 'fed' || $priv_data['DrugFinance_SysNick'] == 'reg') && $priv_data['ReceptDiscount_Code'] == '1') { //указана федеральная льготная категория или региональная льготная категория со 100% скидкой
							$spo_ulo_filters[] = "isnull(i_sud.sale100, 0) = 1";
						}
						if ($priv_data['DrugFinance_SysNick'] == 'reg' && $priv_data['ReceptDiscount_Code'] == '2') { //указана региональная льготная категория с 50% скидкой
							$spo_ulo_filters[] = "isnull(i_sud.sale50, 0) = 1";
						}
					}
				}

				$spo_ulo_where = count($spo_ulo_filters) > 0 ? " and ".implode(" and ", $spo_ulo_filters) : "";

				$from .= "
					outer apply (
						select top 1
							i_dn.DrugNomen_id
						from
							rls.v_DrugNomen i_dn with (nolock)
							inner join r50.SPOULODrug i_sud with (nolock) on cast(i_sud.NOMK_LS as varchar) = i_dn.DrugNomen_Code
						where
							i_dn.Drug_id = d.Drug_id
							{$spo_ulo_where}
					) drug_nomen
				";
				$filterList[] = "drug_nomen.DrugNomen_id is not null";
			}

			$with = count($withArr)>0 ? "with\n".implode(",\n",$withArr) : '';
			$additionFields = count($additionFieldsArr)>0 ? ','.implode(',', $additionFieldsArr) : '';

			if(!empty($data['is_mi_1']) && ($data['is_mi_1'] == 'true')){
				$from .= " left join rls.v_Prep p with(nolock) on p.Prep_id = d.DrugPrep_id";
				$from .= " left join rls.CLSNTFR ntfr with (nolock) on ntfr.CLSNTFR_ID = p.NTFRID";
			}

			$query = "
				{$with}
				select top 500
					d.Drug_id as Drug_rlsid,
					d.DrugComplexMnn_id,
					d.Drug_Code,
					RTRIM(d.Drug_Name) as Drug_Name
					{$additionFields}
				from
					{$from}
				where
					" . implode(" and ", $filterList) . "
				order by
					d.Drug_Name;
			";
		}

		//die(getDebugSQL($query, $queryParams));

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение списка остатков по медикаменту в аптеках
	 */
	function loadFarmacyRlsOstatList($data, $options) {
		$region_nick = getRegionNick();
		$with = array();
		$order_by = array();
		$queryParams = array();
		//$recept_drug_ostat_control = $options['recept_drug_ostat_control'];
		$recept_drug_ostat_control = false;
		$get_storage_kolvo = ($region_nick == 'penza'); //флаг отображения остатков склада (если true то остатки складов отображаются)

		//длительность пребывания медикаментов "в резерве" выраженная в днях
		$day_count = 3;

		//для Пензы обнуляем длительность резервирования, в таком случае резервирование будет актуально для рецепта на протяжении его действия
		if (getRegionNick() == 'penza') {
			$day_count = null;
		}

		if ($day_count > 0) {
			$from_res = "
					outer apply (
						select
							dateadd(day, :Day_Count, er.EvnRecept_setDate) as endDate
					) end_dt
				";
			$queryParams['Day_Count'] = $day_count;
		} else { //если количество дней не задано, то дата окончания резерва считается по сроку действия
			$from_res = "
					left join dbo.v_ReceptValid rv with (nolock) on rv.ReceptValid_id = er.Receptvalid_id
					outer apply (
						select
							(case
								when rv.ReceptValidType_id = 1 then dateadd(day, rv.ReceptValid_Value, er.EvnRecept_setDate) --day
								when rv.ReceptValidType_id = 2 then dateadd(month, rv.ReceptValid_Value, er.EvnRecept_setDate) --month
								when rv.ReceptValidType_id = 3 then dateadd(year, rv.ReceptValid_Value, er.EvnRecept_setDate) --year
								else null
							end) as endDate
					) end_dt
				";
		}

		//если пришел идентификатор отделения, то ищем идентификатор подразделения (понадобится при определении прикреплений)
		if (!empty($data['LpuSection_id'])) {
			//получение подразделения по отделению
			$query = "
				select top 1
					LpuBuilding_id
				from
					v_LpuSection with (nolock)
				where
					LpuSection_id = :LpuSection_id
			";
			$data['LpuBuilding_id'] = $this->getFirstResultFromQuery($query, array(
				'LpuSection_id' => $data['LpuSection_id']
			));
		}

		if ( !empty($data['OrgFarmacy_id']) ) {
			//определение прикреплений
			if ($get_storage_kolvo) {
				$query_ofi = "
					select
						1 as exst,
						min(OrgFarmacyIndex_Index) as OrgFarmacyIndex_Index,
						OrgFarmacy_id,
						Storage_id
					from
						v_OrgFarmacyIndex with (nolock)
					where
						OrgFarmacy_id = :OrgFarmacy_id and
						Lpu_id = :Lpu_id
					group by
						OrgFarmacy_id, Storage_id
				";
			} else {
				$query_ofi = "
					select top 1
						1 as exst,
						OrgFarmacyIndex_Index,
						OrgFarmacy_id,
						null as Storage_id
					from
						v_OrgFarmacyIndex with (nolock)
					where
						OrgFarmacy_id = :OrgFarmacy_id and
						Lpu_id = :Lpu_id
					order by
						OrgFarmacyIndex_Index
				";
			}
			if (!empty($options['recept_by_farmacy_drug_ostat']) && !empty($options['recept_farmacy_type']) && $options['recept_farmacy_type'] == 'mo_farmacy') {
				//если есть идентификатор подразделения, то проверяем прикрепления к подразделениям
				if (!empty($data['LpuBuilding_id'])) {
					//проверям есть ли прикрепление подразделения к каким либо аптекам
					$query = "
						select
							count(ofi.OrgFarmacyIndex_id) as cnt
						from
							v_OrgFarmacyIndex ofi with (nolock)
						where
							ofi.Lpu_id = :Lpu_id and
							ofi.LpuBuilding_id = :LpuBuilding_id;
					";
					$cnt_data = $this->getFirstRowFromQuery($query, array(
						'Lpu_id' => $data['Lpu_id'],
						'LpuBuilding_id' => $data['LpuBuilding_id']
					));

					if (!empty($cnt_data['cnt'])) {
						if ($get_storage_kolvo) {
							$query_ofi = "
								select
									1 as exst,
									i_ofi.OrgFarmacyIndex_Index,
									i_ofi.OrgFarmacy_id,
									i_ofi.Storage_id
								from
									v_OrgFarmacyIndex i_ofi with (nolock)
								where
									i_ofi.OrgFarmacy_id = :OrgFarmacy_id and
									i_ofi.Lpu_id = :Lpu_id and
									i_ofi.LpuBuilding_id = :LpuBuilding_id and
									(:WhsDocumentCostItemType_id is null or i_ofi.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)
							";
						} else {
							$query_ofi = "
								select top 1
									1 as exst,
									i_ofi.OrgFarmacyIndex_Index,
									i_ofi.OrgFarmacy_id,
									null as Storage_id
								from
									v_OrgFarmacyIndex i_ofi with (nolock)
								where
									i_ofi.OrgFarmacy_id = :OrgFarmacy_id and
									i_ofi.Lpu_id = :Lpu_id and
									i_ofi.LpuBuilding_id = :LpuBuilding_id and
									(:WhsDocumentCostItemType_id is null or i_ofi.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)
								order by
									i_ofi.OrgFarmacyIndex_Index
							";
						}
						$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
						$queryParams['WhsDocumentCostItemType_id'] = !empty($data['WhsDocumentCostItemType_id']) ? $data['WhsDocumentCostItemType_id'] : null;
					}
				}
			}

			$reserve_enabled = ((!$data['isKardio'] || $region_nick == 'perm') && $region_nick != 'msk'); //флаг "учет резервирования при расчете количества"
			$select_dor_kolvo = "str(isnull(do.DrugOstatRegistry_Kolvo, 0), 18, 2)";
			$from_res_subquery = "";

			$from_res_subquery_drug_str = "er.Drug_rlsid = :Drug_rlsid and";
			$do_subquery_drug_str = "and dor.Drug_id = :Drug_rlsid";

			if (empty($data['Drug_rlsid'])) {
				if (!empty($data['DrugComplexMnn_id'])) {
					$with[] = "
						drug_id_list as (
							select
								Drug_id 
							from
								rls.v_Drug d with (nolock)
							where
								d.DrugComplexMnn_id = :DrugComplexMnn_id
						)
					";
					$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];

					$from_res_subquery_drug_str = "er.Drug_rlsid in (select Drug_id from drug_id_list) and";
					$do_subquery_drug_str = "and dor.Drug_id in (select Drug_id from drug_id_list)";
				} else { //в противном случае собрать запрос не получится
					return false;
				}
			}

			if ($reserve_enabled) {
				$select_dor_kolvo = "str(case when isnull(do.DrugOstatRegistry_Kolvo-isnull(rd.kolvo, 0), 0) <= 0 then 0 else isnull(do.DrugOstatRegistry_Kolvo-isnull(rd.kolvo, 0), 0) end, 18, 2)";
				$from_res_subquery = "
					outer apply (
						select
							isnull(sum(EvnRecept_Kolvo),0) as kolvo
						from
							v_EvnRecept er with (nolock)
							{$from_res}
						where
							{$from_res_subquery_drug_str}
							er.ReceptDelayType_id is null and
							end_dt.endDate >= @current_date and
							(er.EvnRecept_IsNotOstat is null or EvnRecept_IsNotOstat = @No_id) and
							er.OrgFarmacy_id = :OrgFarmacy_id and
							(
								ofi.Storage_id is null or
								er.Storage_id = ofi.Storage_id 
							)
					) rd
				";
			}

			$do_subquery = "
				outer apply (
					select
						SUM(dor.DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo,
						MAX(dor.DrugOstatRegistry_updDT) as DrugOstatRegistry_updDT
					from
						v_DrugOstatRegistry dor with (nolock)
						left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
					where
						dor.Org_id = o.Org_id
						{$do_subquery_drug_str}
						and dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
						and (ps.PrepSeries_IsDefect is null or ps.PrepSeries_IsDefect = @No_id)
						and (
							(
								ofi.Storage_id is null
								and dor.Storage_id is not null
							) or
							ofi.Storage_id = dor.Storage_id
						) 
				) do
			";

			if ($region_nick == 'msk') {
				if (!empty($data['Drug_rlsid'])) {
					$with[] = "
						drug_list as (
							select distinct
								dn2.Drug_id 
							from
								rls.v_DrugNomen dn with (nolock)
								inner join r50.SPOULODrug sud with (nolock) on cast(sud.NOMK_LS as varchar) = dn.DrugNomen_Code
								inner join r50.SPOULODrug sud2 with (nolock) on
									isnull(sud2.C_MNN, '0') = isnull(sud.C_MNN, '0') and
									isnull(sud2.C_LF, '0') = isnull(sud.C_LF, '0') and
									isnull(sud2.DosageId, '0') = isnull(sud.DosageId, '0')
								inner join rls.v_DrugNomen dn2 with (nolock) on dn2.DrugNomen_Code = cast(sud2.NOMK_LS as varchar)
							where
								dn.Drug_id = :Drug_rlsid and
								dn2.Drug_id is not null
						)
					";
				} else if (!empty($data['DrugComplexMnn_id'])) {
					$with[] = "
						drug_list as (
							select distinct
								dn2.Drug_id 
							from
								rls.Drug d with (nolock)
								inner join rls.v_DrugNomen dn with (nolock) on dn.Drug_id = d.Drug_id
								inner join r50.SPOULODrug sud with (nolock) on cast(sud.NOMK_LS as varchar) = dn.DrugNomen_Code
								inner join r50.SPOULODrug sud2 with (nolock) on
									isnull(sud2.C_MNN, '0') = isnull(sud.C_MNN, '0') and
									isnull(sud2.C_LF, '0') = isnull(sud.C_LF, '0') and
									isnull(sud2.DosageId, '0') = isnull(sud.DosageId, '0')
								inner join rls.v_DrugNomen dn2 with (nolock) on dn2.DrugNomen_Code = cast(sud2.NOMK_LS as varchar)
							where
								d.DrugComplexMnn_id = :DrugComplexMnn_id and
								dn2.Drug_id is not null
						)
					";
					$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
				} else { //в противном случае собрать запрос не получится
					return false;
				}

				$do_subquery_from_array = array();

				//этот блок предназначен для поиска остатков по источнику финансирования не соответвтующему программе лло (статье расхода)
				if (!empty($data['DrugFinance_id'])) {
					$do_subquery_from_array[] = "dor.DrugFinance_id = :DrugFinance_id";
					$queryParams['DrugFinance_id'] = $data['DrugFinance_id'];
				} else {
					$do_subquery_from_array[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
				}

				$do_subquery_from = count($do_subquery_from_array) > 0 ? " and ".implode(" and ", $do_subquery_from_array) : "";

				$do_subquery = "
					outer apply (
						select
							SUM(dor.DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo,
							MAX(dor.DrugOstatRegistry_updDT) as DrugOstatRegistry_updDT
						from
							drug_list dl
							inner join v_DrugOstatRegistry dor with (nolock) on
								dor.Drug_id = dl.Drug_id and
								dor.Org_id = o.Org_id
								{$do_subquery_from}
							left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
						where
							(ps.PrepSeries_IsDefect is null or ps.PrepSeries_IsDefect = @No_id)
							and (
								(
									ofi.Storage_id is null
									and dor.Storage_id is not null
								) or
								ofi.Storage_id = dor.Storage_id
							) 
					) do
				";
			}

			$with_clause = count($with) > 0 ? 'with '.implode(', ', $with) : '';

			$query = "
				declare
					@No_id bigint,
					@current_date date;					

				set @No_id = (select YesNo_id from v_YesNo with (nolock) where YesNo_Code = 0); --Нет	
				set @current_date = dbo.tzGetDate();
				
				{$with_clause}
				select
					 ofarm.OrgFarmacy_id
					,RTRIM(o.Org_Name) as OrgFarmacy_Name
					,RTRIM(ofarm.OrgFarmacy_HowGo) as OrgFarmacy_HowGo
					,IsFarmacy.YesNo_Code as OrgFarmacy_IsFarmacy
					,{$select_dor_kolvo} as DrugOstatRegistry_Kolvo
					,isnull(convert(varchar(10), do.DrugOstatRegistry_updDT, 104) + ' ' + convert(varchar(5), do.DrugOstatRegistry_updDT, 108), '') as DrugOstatRegistry_updDT
					,null as index_exists
					,s.Storage_id
					,s.Storage_Name
				from v_OrgFarmacy ofarm with (nolock)
					inner join v_Org o with (nolock) on o.Org_id = ofarm.Org_id
					inner join v_YesNo IsFarmacy with (nolock) on IsFarmacy.YesNo_id = ISNULL(ofarm.OrgFarmacy_IsFarmacy, 2)
					outer apply (
						{$query_ofi}
					) ofi
					{$do_subquery}
					{$from_res_subquery}
					left join v_Storage s with (nolock) on s.Storage_id = ofi.Storage_id
				where
					ofarm.OrgFarmacy_id = :OrgFarmacy_id
					and ISNULL(ofarm.OrgFarmacy_IsEnabled, 2) = 2
			";

			$queryParams['Lpu_id'] = isset($data['Lpu_id']) ? $data['Lpu_id'] : null;
			$queryParams['Drug_rlsid'] = $data['Drug_rlsid'];
			$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
			$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		} else {
			/*$query = "
				select
					 ofarm.OrgFarmacy_id as OrgFarmacy_id
					,RTRIM(Org.Org_Name) as OrgFarmacy_Name
					,RTRIM(ofarm.OrgFarmacy_HowGo) as OrgFarmacy_HowGo
					,YesNo.YesNo_Code as OrgFarmacy_IsFarmacy
					,case when isnull(do.DrugOstatRegistry_Kolvo, 0) <= 0 then 0 else isnull(do.DrugOstatRegistry_Kolvo, 0) end as DrugOstatRegistry_Kolvo
				from v_OrgFarmacy ofarm with (nolock)
					inner join OrgFarmacyIndex on OrgFarmacyIndex.OrgFarmacy_id = ofarm.OrgFarmacy_id
						and OrgFarmacyIndex.Lpu_id = :Lpu_id
					inner join v_Org Org on Org.Org_id = ofarm.Org_id
					inner join YesNo on YesNo.YesNo_id = ISNULL(ofarm.OrgFarmacy_IsFarmacy, 2)
					outer apply (
						select SUM(dor.DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo
						from v_DrugOstatRegistry dor with (nolock)
						where dor.OrgFarmacy_id = ofarm.OrgFarmacy_id
							and DOA.Drug_id = :Drug_id
					) DrugOstat
					outer apply (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA with (nolock)
							inner join v_ReceptFinance ReceptFinance on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id
								and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = 1
							and DOA.Drug_id = :Drug_id
					) RAS_Ostat
				where ISNULL(ofarm.OrgFarmacy_IsEnabled, 2) = 2
			";

			switch ( $recept_drug_ostat_control ) {
				case 1:
					// Контроль остатков отключен
				break;

				case 2:
					// Контроль остатков только по РАС
					$query .= " and ISNULL(RAS_Ostat.DrugOstat_Kolvo, 0) > 0";
				break;

				case 3:
					// Контроль остатков включен для суперадмина
					if ( isSuperadmin() ) {
						$query .= " and (ISNULL(DrugOstat.DrugOstat_Kolvo, 0) > 0 or ISNULL(RAS_Ostat.DrugOstat_Kolvo, 0) > 0)";
					}
					else {
						$query .= " and ISNULL(RAS_Ostat.DrugOstat_Kolvo, 0) > 0";
					}
				break;

				case 4:
					$query .= " and (ISNULL(DrugOstat.DrugOstat_Kolvo, 0) > 0 or ISNULL(RAS_Ostat.DrugOstat_Kolvo, 0) > 0)";
				break;
			}

			$queryParams['Drug_id'] = $data['Drug_rlsid'];
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
			*/
			$contrList = array('(1=1)');
			$ostatList = array('(1=1)');
			$only_attached = false; //флаг, отображение только прикрепленных аптек
			$where = '';

			if ($region_nick == 'msk') { //для Москвы отображаем только прикрепленные аптеки
				$only_attached = true;
			}

			$ostat_type = 0;
			if ($options['recept_drug_ostat_viewing']) {
				$ostat_type = 1;
			}
			if ($options['recept_drug_ostat_control']) {
				$ostat_type = 2;
			}
			if ($options['recept_empty_drug_ostat_allow']) {
				$ostat_type = 3;
			}

			//Возможен выбор только из таких контрагентов
			$contrList[] = "ct.ContragentType_SysNick in ('store','apt')";
			$contr_join = '';
			//Выбор конкретного типа контрагента в зависимости от настроек
			if ($ostat_type > 0) {
				$ras = null; $apt = null;
				if (!empty($options['recept_by_ras_drug_ostat']) && $options['recept_by_ras_drug_ostat']) {
					$ras = "ct.ContragentType_SysNick = 'store'";
				}
				if (!empty($options['recept_by_farmacy_drug_ostat']) && $options['recept_by_farmacy_drug_ostat']) {
					$apt = "ct.ContragentType_SysNick = 'apt'";
					if (!empty($options['recept_farmacy_type']) && $options['recept_farmacy_type'] == 'mo_farmacy') {
						//Поправил условие по задаче https://redmine.swan.perm.ru/issues/98904
						//$apt .= " and exists(select top 1 Lpu_id from v_OrgFarmacyIndex with(nolock) where Lpu_id = :Lpu_id)";
						$contr_join = " inner join v_OrgFarmacyIndex OFI on (OFI.Org_id = c.Org_id and OFI.Lpu_id = :Lpu_id)";
					}
				}
				if (!empty($ras) && !empty($apt)) {
					$contrList[] = "(($ras) or ($apt))";
				} else if (!empty($ras) || !empty($apt)) {
					$contrList[] = empty($ras) ? $apt : $ras;
				}
			}

			if (
				!empty($options['select_drug_from_list'])
				&& in_array($options['select_drug_from_list'], array('allocation', 'request_and_allocation'))
				&& !empty($data['WhsDocumentSupply_id'])
			) {
				$ostatList[] = "dsh.WhsDocumentSupply_id = :WhsDocumentSupply_id";
				$queryParams['WhsDocumentSupply_id'] = $data['WhsDocumentSupply_id'];
			}

			$with_ofi = "ofi_list as (
				select
					1 as exst,
					OrgFarmacyIndex_Index,
					OrgFarmacy_id,
					Storage_id
				from
					v_OrgFarmacyIndex with (nolock)
				where
					Lpu_id = :Lpu_id
			)";
			if (!empty($options['recept_by_farmacy_drug_ostat']) && !empty($options['recept_farmacy_type']) && $options['recept_farmacy_type'] == 'mo_farmacy') {
				//если есть идентификатор подразделения, то проверяем прикрепления к подразделениям
				if (!empty($data['LpuBuilding_id']) && $region_nick != 'msk') { //Для москвы не учитываем прикрепление к подразделению
					//проверям есть ли прикрепление подразделения к каким либо аптекам
					$query = "
						select
							count(ofi.OrgFarmacyIndex_id) as cnt
						from
							v_OrgFarmacyIndex ofi with (nolock)
						where
							ofi.Lpu_id = :Lpu_id and
							ofi.LpuBuilding_id = :LpuBuilding_id;
					";
					$cnt_data = $this->getFirstRowFromQuery($query, array(
						'Lpu_id' => $data['Lpu_id'],
						'LpuBuilding_id' => $data['LpuBuilding_id']
					));

					if (!empty($cnt_data['cnt'])) {
						$with_ofi = "ofi_list as (
							select
								1 as exst,
								i_ofi.OrgFarmacyIndex_Index,
								i_ofi.OrgFarmacy_id,
								i_ofi.Storage_id
							from
								v_OrgFarmacyIndex i_ofi with (nolock)
							where
								i_ofi.Lpu_id = :Lpu_id and
								i_ofi.LpuBuilding_id = :LpuBuilding_id and
								(:WhsDocumentCostItemType_id is null or i_ofi.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)
						)";
						$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
						$queryParams['WhsDocumentCostItemType_id'] = !empty($data['WhsDocumentCostItemType_id']) ? $data['WhsDocumentCostItemType_id'] : null;
					}
				}

				//выводим только прикрепленные аптеки
				$only_attached = true;
			}
			$with[] = $with_ofi;

			//выводим только прикрепленные аптеки
			if ($only_attached) {
				$where .= " and ofi.exst = 1 ";
			}

			$reserve_enabled = ((!$data['isKardio'] || $region_nick == 'perm') && $region_nick != 'msk'); //флаг "учет резервирования при расчете количества"
			$select_dor_kolvo = "str(isnull(do.DrugOstatRegistry_Kolvo, 0), 18, 2)";
			$from_res_subquery = "";

			$from_res_subquery_drug_str = "er.Drug_rlsid = :Drug_rlsid and";
			$do_subquery_drug_str = "and dor.Drug_id = :Drug_rlsid";

			if (empty($data['Drug_rlsid'])) {
				if (!empty($data['DrugComplexMnn_id'])) {
					$with[] = "
						drug_id_list as (
							select
								Drug_id 
							from
								rls.v_Drug d with (nolock)
							where
								d.DrugComplexMnn_id = :DrugComplexMnn_id
						)
					";
					$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];

					$from_res_subquery_drug_str = "er.Drug_rlsid in (select Drug_id from drug_id_list) and";
					$do_subquery_drug_str = "and dor.Drug_id in (select Drug_id from drug_id_list)";
				} else { //в противном случае собрать запрос не получится
					return false;
				}
			}

			if ($reserve_enabled) {
				$select_dor_kolvo = "str(case when isnull(do.DrugOstatRegistry_Kolvo-isnull(rd.kolvo, 0), 0) <= 0 then 0 else isnull(do.DrugOstatRegistry_Kolvo-isnull(rd.kolvo, 0), 0) end, 18, 2)";
				$from_res_subquery = "
					outer apply (
						select
							isnull(sum(EvnRecept_Kolvo),0) as kolvo
						from
							v_EvnRecept er with (nolock)
							{$from_res}
						where
							{$from_res_subquery_drug_str}
							er.ReceptDelayType_id is null and
							end_dt.endDate >= @current_date and
							(er.EvnRecept_IsNotOstat is null or EvnRecept_IsNotOstat = @No_id) and
							er.OrgFarmacy_id = ofarm.OrgFarmacy_id and
							(
								ofi.Storage_id is null or
								er.Storage_id = ofi.Storage_id 
							)
					) rd
				";
			}

			$do_subquery = "
				outer apply (
					select
						SUM(dor.DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo,
						MAX(dor.DrugOstatRegistry_Cost) as DrugOstatRegistry_Cost,
						MAX(dor.DrugOstatRegistry_updDT) as DrugOstatRegistry_updDT
					from v_DrugOstatRegistry dor with (nolock)
						left join v_DrugShipment dsh with(nolock) on dsh.DrugShipment_id = dor.DrugShipment_id
						left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
					where
						dor.Org_id = o.Org_id
						{$do_subquery_drug_str}
						and dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id							
						and (ps.PrepSeries_IsDefect is null or ps.PrepSeries_IsDefect = @No_id)
						and ".implode(' and ', $ostatList)."
						and (
							(
								ofi.Storage_id is null
								and dor.Storage_id is not null
							) or
							ofi.Storage_id = dor.Storage_id
						) 
				) do
			";

			if ($region_nick == 'msk') {
				if (!empty($data['Drug_rlsid'])) {
					$with[] = "
						drug_list as (
							select distinct
								dn2.Drug_id 
							from
								rls.v_DrugNomen dn with (nolock)
								inner join r50.SPOULODrug sud with (nolock) on cast(sud.NOMK_LS as varchar) = dn.DrugNomen_Code
								inner join r50.SPOULODrug sud2 with (nolock) on
									isnull(sud2.C_MNN, '0') = isnull(sud.C_MNN, '0') and
									isnull(sud2.C_LF, '0') = isnull(sud.C_LF, '0') and
									isnull(sud2.DosageId, '0') = isnull(sud.DosageId, '0')
								inner join rls.v_DrugNomen dn2 with (nolock) on dn2.DrugNomen_Code = cast(sud2.NOMK_LS as varchar)
							where
								dn.Drug_id = :Drug_rlsid and
								dn2.Drug_id is not null
						)
					";
				} else if (!empty($data['DrugComplexMnn_id'])) {
					$with[] = "
						drug_list as (
							select distinct
								dn2.Drug_id 
							from
								rls.Drug d with (nolock)
								inner join rls.v_DrugNomen dn with (nolock) on dn.Drug_id = d.Drug_id
								inner join r50.SPOULODrug sud with (nolock) on cast(sud.NOMK_LS as varchar) = dn.DrugNomen_Code
								inner join r50.SPOULODrug sud2 with (nolock) on
									isnull(sud2.C_MNN, '0') = isnull(sud.C_MNN, '0') and
									isnull(sud2.C_LF, '0') = isnull(sud.C_LF, '0') and
									isnull(sud2.DosageId, '0') = isnull(sud.DosageId, '0')
								inner join rls.v_DrugNomen dn2 with (nolock) on dn2.DrugNomen_Code = cast(sud2.NOMK_LS as varchar)
							where
								d.DrugComplexMnn_id = :DrugComplexMnn_id and
								dn2.Drug_id is not null
						)
					";
					$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
				} else { //в противном случае собрать запрос не получится
					return false;
				}

				$do_subquery = "
					outer apply (
						select
							SUM(dor.DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo,
							MAX(dor.DrugOstatRegistry_Cost) as DrugOstatRegistry_Cost,
							MAX(dor.DrugOstatRegistry_updDT) as DrugOstatRegistry_updDT
						from
							drug_list dl
							inner join v_DrugOstatRegistry dor with (nolock) on
								dor.Drug_id = dl.Drug_id and
								dor.Org_id = o.Org_id and
								dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
							left join v_DrugShipment dsh with(nolock) on dsh.DrugShipment_id = dor.DrugShipment_id
							left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
						where
							(ps.PrepSeries_IsDefect is null or ps.PrepSeries_IsDefect = @No_id)
							and ".implode(' and ', $ostatList)."
							and (
								(
									ofi.Storage_id is null
									and dor.Storage_id is not null
								) or
								ofi.Storage_id = dor.Storage_id
							)
					) do
				";
			}

			$with[] = "
				contr_org as (
					select distinct
						c.Org_id,
						ct.ContragentType_id,
						ct.ContragentType_SysNick
					from
						v_Contragent c with(nolock)
						left join v_ContragentType ct with(nolock) on ct.ContragentType_id = c.ContragentType_id
						{$contr_join}
					where
						".implode(' and ', $contrList)."
				)
			";
			$with_clause = count($with) > 0 ? 'with '.implode(', ', $with) : '';

			if (!empty($options['recept_by_farmacy_drug_ostat']) && $region_nick == 'msk') {
				$order_by[] = "do.DrugOstatRegistry_Kolvo desc";
			}

			$order_by[] = "ofi.exst desc";
			$order_by[] = "ofi.OrgFarmacyIndex_Index";
			$order_by[] = "o.Org_Name";

			$order_by_clause = count($with) > 0 ? 'order by '.implode(', ', $order_by) : '';

			$query = "
				declare
					@No_id bigint,
					@current_date date;					

				set @No_id = (select YesNo_id from v_YesNo with (nolock) where YesNo_Code = 0); --Нет	
				set @current_date = dbo.tzGetDate();

				{$with_clause}
				select
					 isnull(ofarm.OrgFarmacy_id,-1*c.Org_id) as OrgFarmacy_id
					,RTRIM(o.Org_Name) as OrgFarmacy_Name
					,rtrim(isnull(ofarm.OrgFarmacy_HowGo,'')) as OrgFarmacy_HowGo
					,IsFarmacy.YesNo_Code as OrgFarmacy_IsFarmacy
					,{$select_dor_kolvo} as DrugOstatRegistry_Kolvo
					,do.DrugOstatRegistry_Cost
					,isnull(convert(varchar(10), do.DrugOstatRegistry_updDT, 104) + ' ' + convert(varchar(5), do.DrugOstatRegistry_updDT, 108), '') as DrugOstatRegistry_updDT
					,ofi.exst as index_exists
					,s.Storage_id
					,s.Storage_Name
				from
					contr_org c with(nolock)
					inner join v_Org o with (nolock) on o.Org_id = c.Org_id
					left join v_OrgFarmacy ofarm with(nolock) on ofarm.Org_id = c.Org_id
					inner join v_YesNo IsFarmacy with (nolock) on IsFarmacy.YesNo_id = ISNULL(ofarm.OrgFarmacy_IsFarmacy, 2)
					outer apply (
						select ".($get_storage_kolvo ? "" : "top 1")."
							exst,
							".($get_storage_kolvo ? "min(OrgFarmacyIndex_Index) as OrgFarmacyIndex_Index," : "OrgFarmacyIndex_Index,")."
							".($get_storage_kolvo ? "Storage_id" : "null as Storage_id")."
						from
							ofi_list
						where
							ofi_list.OrgFarmacy_id = ofarm.OrgFarmacy_id
						".($get_storage_kolvo ? "group by Storage_id, exst" : "order by OrgFarmacyIndex_Index")."
					) ofi
					{$do_subquery}
					{$from_res_subquery}
					left join v_Storage s with (nolock) on s.Storage_id = ofi.Storage_id
				where
					isnull(ofarm.OrgFarmacy_IsEnabled, 2) = 2
					{$where}
				{$order_by_clause}
			";

			$queryParams['Lpu_id'] = isset($data['Lpu_id']) ? $data['Lpu_id'] : null;
			$queryParams['Drug_rlsid'] = $data['Drug_rlsid'];
			$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
			$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		$of_list = $this->queryResult($query, $queryParams);

		if (is_array($of_list) && count($of_list) > 0) {
			$of_array = array();

			//перепаковка данных таким образом, чтобы идентификатор аптеки был уникален

			//группировка данных по аптекам
			foreach ($of_list as $of_data) {
				$id = $of_data['OrgFarmacy_id'];
				if (!isset($of_array[$id])) {
					$of_array[$id] = $of_data;
					$of_array[$id]['storage_list'] = array();
				}
				if (!empty($of_data['Storage_id'])) {
					$of_array[$id]['storage_list'][] = array(
						'Storage_id' => $of_data['Storage_id'],
						'Storage_Name' => $of_data['Storage_Name'],
						'Storage_Kolvo' => $of_data['DrugOstatRegistry_Kolvo']
					);
				} else {
					if (!empty($of_array[$id]['Storage_id'])) {
						$of_array[$id]['Storage_id'] = null;
						$of_array[$id]['Storage_Name'] = null;
						$of_array[$id]['DrugOstatRegistry_Kolvo'] = $of_data['DrugOstatRegistry_Kolvo'];
					}
				}
			}

			//приведение массива данных, к виду пригодному для вывода
			foreach ($of_array as $of_id => $of_data) {
				$id_str = '';
				$name_str = '';
				$kolvo_str = '';
				if (count($of_data['storage_list']) > 0) {
					foreach($of_data['storage_list'] as $storage_data) {
						$id_str .= (!empty($id_str) ? '<br/>' : '').$storage_data['Storage_id'];
						$name_str .= (!empty($name_str) ? '<br/>' : '').$storage_data['Storage_Name'];
						$kolvo_str .= (!empty($kolvo_str) ? '<br/>' : '').(!empty($storage_data['Storage_Kolvo']) ? $storage_data['Storage_Kolvo'] : '0');
					}
				} else {
					$kolvo_str = $of_data['DrugOstatRegistry_Kolvo'];
				}
				$of_array[$of_id]['Storage_id'] = $id_str;
				$of_array[$of_id]['Storage_Name'] = $name_str;
				$of_array[$of_id]['Storage_Kolvo'] = $kolvo_str;
			}
			$of_array = array_values($of_array);

			return $of_array;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка прикрепления МО/подразделений МО к аптеке
	 */
	public function GetMoByFarmacy($data) {
		$queryParams = array();
		$setWhsDocumentCostItemType = '';
		
		if (isset($data['WhsDocumentCostItemType_id'])) {
			$setWhsDocumentCostItemType = 'Set @WhsDocumentCostItemType_id = '. $data['WhsDocumentCostItemType_id'];
		}

		$sql = "
             Declare
                @OrgFarmacy_id bigint = :OrgFarmacy_id,
                @Lpu_id bigint = :Lpu_id,
				@WhsDocumentCostItemType_id bigint;
				
				{$setWhsDocumentCostItemType}
                
        Select 
		LpuBuilding_id,
		LpuBuilding_Name,
                case 
                    when( moAttachLS = 1 and OrgFarmacyLS_id = @OrgFarmacy_id)
                    or (moAttachNS = 1 and OrgFarmacyNS_id = @OrgFarmacy_id)
                        then 1 
                    else 0 
                end moAttach,
		moAttachLS,
		OrgFarmacyLS_id,
		OrgFarmacyIndexLS_id,
		moAttachNS,
		OrgFarmacyNS_id,
		OrgFarmacyIndexNS_id,
                
                Attach_Other  
            from fn_MoByFarmacy (@Lpu_id, @OrgFarmacy_id, @WhsDocumentCostItemType_id)     
        ";

		$queryParams['Lpu_id']						= $data['Lpu_id'];
		$queryParams['OrgFarmacy_id']				= $data['OrgFarmacy_id'];
			
		//echo '<pre>' . print_r(getDebugSQL($sql, $queryParams), 1) . '</pre>'; exit;

		$result = $this->db->query($sql,$queryParams);
		if(is_object($result))
		{
			$result = $result->result('array');
			return $result;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Получение списка подразделений МО прикрепленных к аптеке
	 */
	function getLpuBuildingLinkedByOrgFarmacy($data) {
		$queryParams = array();

		$sql = "
			declare
				@OrgFarmacy_id bigint = :OrgFarmacy_id,
				@Lpu_id bigint = :Lpu_id,
				@WhsDocumentCostItemType_id bigint = :WhsDocumentCostItemType_id;
			
			select
				lb.LpuBuilding_id,
				lb.LpuBuilding_Name,
				(case
					when isnull(ofix.is_narko_cnt, 0) > 0 and isnull(ofix.is_not_narko_cnt, 0) > 0 then 1 -- все
					when isnull(ofix.is_narko_cnt, 0) = 0 and isnull(ofix.is_not_narko_cnt, 0) > 0 then 2 -- кроме НС и ПВ
					when isnull(ofix.is_narko_cnt, 0) > 0 and isnull(ofix.is_not_narko_cnt, 0) = 0 then 3 -- НС и ПВ
					else null
				end) as LsGroup_id,
				(case
					when ofix.OrgFarmacyIndex_id is not null then 'true'
					else 'false'
				end) as IsVkl,
				(case
					when ofix.OrgFarmacyIndex_id is not null then 'saved'
					else ''
				end) as state
			from
				v_LpuBuilding lb with (nolock)
				outer apply (
					select
						max(i_ofix.OrgFarmacyIndex_id) as OrgFarmacyIndex_id,
						sum(case when isnull(i_ofix.OrgFarmacyIndex_IsNarko, 0) = 2 then 1 else 0 end) as is_narko_cnt,
						sum(case when isnull(i_ofix.OrgFarmacyIndex_IsNarko, 0) = 1 then 1 else 0 end) as is_not_narko_cnt
					from
						v_OrgFarmacyIndex i_ofix with (nolock)
					where
						i_ofix.lpu_id = @Lpu_id and
						i_ofix.OrgFarmacy_id = @OrgFarmacy_id and
						isnull(i_ofix.WhsDocumentCostItemType_id, 0) = isnull(@WhsDocumentCostItemType_id, 0) and
						i_ofix.LpuBuilding_id = lb.LpuBuilding_id
				) ofix
				outer apply (
					select top 1
						i_lu.LpuUnit_id
					from
						v_LpuUnit i_lu with (nolock)
					where
						i_lu.LpuBuilding_id = lb.LpuBuilding_id and
						i_lu.LpuUnitType_SysNick in ('fap', 'polka')
				) lu
			where
				lb.Lpu_id = @Lpu_id and
				lu.LpuUnit_id is not null
			order by
				lb.LpuBuilding_Name;
		";

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
		$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];

		$result = $this->queryResult($sql,$queryParams);
		return $result;
	}

	/**
	 * Получение списка подразделений МО прикрепленных к аптеке склада
	 */
	function getLpuBuildingStorageLinkedByOrgFarmacy($data) {
		$queryParams = array();

		$sql = "
			declare
				@OrgFarmacy_id bigint = :OrgFarmacy_id,
				@Lpu_id bigint = :Lpu_id,
				@WhsDocumentCostItemType_id bigint = :WhsDocumentCostItemType_id;
			
			select
				ofix.OrgFarmacyIndex_id,
				(case
					when isnull(ofix.OrgFarmacyIndex_IsNarko, 0) = 1 then 1
					when isnull(ofix.OrgFarmacyIndex_IsNarko, 0) = 2 then 2
					else 3
				end) as LsGroup_id,
				(case
					when isnull(ofix.OrgFarmacyIndex_IsNarko, 0) = 1 then 'Все кроме НС и ПВ'
					when isnull(ofix.OrgFarmacyIndex_IsNarko, 0) = 2 then 'НС и ПВ'
					else ''
				end) as LsGroup_Name,
				lb.LpuBuilding_id,
				lb.LpuBuilding_Name,
				ofix.Storage_id,
				s.Storage_Name
			from
				v_OrgFarmacyIndex ofix with (nolock)
				left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = ofix.LpuBuilding_id
				left join v_Storage s with (nolock) on s.Storage_id = ofix.Storage_id
			where
				ofix.lpu_id = @Lpu_id and
				ofix.OrgFarmacy_id = @OrgFarmacy_id and
				isnull(ofix.WhsDocumentCostItemType_id, 0) = isnull(@WhsDocumentCostItemType_id, 0) and
				ofix.LpuBuilding_id is not null
			order by
				lb.LpuBuilding_Name, 
				lb.LpuBuilding_id,
				isnull(ofix.OrgFarmacyIndex_IsNarko, 0);
		";

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
		$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];

		$result = $this->queryResult($sql,$queryParams);
		return $result;
	}

	/**
	 * Запись прикрепления подразделений МО к аптеке
	 */
	public function saveMoByFarmacy3($data) {
		//echo '<pre>' . print_r($data, 1) . '</pre>';
		$arr_data = json_decode($data['arr'], 1);
		// echo '<pre>' . print_r($arr_data, 1) . '</pre>'; exit;

		$xml = '<RD>';
		if (isset($data['pmuser_id']))
			$pmUser = $data['pmuser_id'];
		else
			$pmUser = '';

		foreach($arr_data as $item) {
			$LpuBuilding_id = "";
			$Lpu_id = "";
			$OrgFarmacyIndex_id = "";
			$typeLs = "";
			$action = "";
			$OrgFarmacy_id = "";

			if (isset($item['LpuBuilding_id']))
				$LpuBuilding_id = $item['LpuBuilding_id'];
			if (isset($item['Lpu_id']))
				$Lpu_id = $item['Lpu_id'];
			if (isset($item['OrgFarmacyIndex_id']))
				$OrgFarmacyIndex_id = $item['OrgFarmacyIndex_id'];
			if (isset($item['typeLs']))
				$typeLs = $item['typeLs'];
			if (isset($item['action']))
				$action = $item['action'];
			if (isset($item['OrgFarmacy_id']))
				$OrgFarmacy_id = $item['OrgFarmacy_id'];

			$xml .='<R|*|v1="'.$LpuBuilding_id.'" 
                 |*|v2="'.$Lpu_id.'"
                 |*|v3="'.$OrgFarmacyIndex_id.'"
                 |*|v4="'.$typeLs.'"
                 |*|v5="'.$action.'" 
                 |*|v6="'.$pmUser.'"
                 |*|v7="'.$OrgFarmacy_id.'" ></R>';

			;

		}

		$xml .= '</RD>';

		$xml = strtr($xml, array(PHP_EOL=>'', " "=>""));
		$xml = str_replace("|*|", " ", $xml);
		//echo htmlspecialchars($xml);
		//$xml = htmlspecialchars($xml);
		//echo '<pre>' . print_r($xml, 1) . '</pre>';
		//exit;

		$params = array('xml'=>(string)$xml);

		$query = "
		Declare 
		@xml nvarchar(max),
		@Error_Code int,
		@Error_Message varchar(4000)

			Set @xml = :xml;

			exec [r2].[p_saveMoByFarmacy] @xml, @Error_Code, @Error_Message
			Select @Error_Code as Error_Code, @Error_Message as Error_Msg ;  
			 
				";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return array(0 => array('success' => true));
			//                        if ( is_object($result) ) {
			//			$res = $result->result('array');
			//
			//			if ( is_array($res) && count($res) > 0 && !empty($res[0]['ResponseText']) ) {
			//				$res_arr = strtr($res[0]['ResponseText'], array(']'=>', {"success":"true"}]'));
			//			}
			//			else {
			//				$res_arr = false;
			//			}
			//		}
		}
		else {
			return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

	}

	/**
	 * Запись прикрепления подразделений МО к аптеке
	 */
	public function saveMoByFarmacy($data) {
		$setData = '';
		$arr_data = json_decode($data['arr'], 1);

		$xml = '<RD>';
		if (isset($data['pmuser_id']))
			$pmUser = $data['pmuser_id'];
		else
			$pmUser = '';

		foreach($arr_data as $item) {
			$LpuBuilding_id = "";
			$Lpu_id = "";
			$OrgFarmacyIndex_id = "";
			$typeLs = "";
			$action = "";
			$OrgFarmacy_id = "";

			if (isset($item['LpuBuilding_id']))
				$LpuBuilding_id = $item['LpuBuilding_id'];
			if (isset($item['Lpu_id']))
				$Lpu_id = $item['Lpu_id'];
			if (isset($item['OrgFarmacyIndex_id']))
				$OrgFarmacyIndex_id = $item['OrgFarmacyIndex_id'];
			if (isset($item['typeLs']))
				$typeLs = $item['typeLs'];
			if (isset($item['action']))
				$action = $item['action'];
			if (isset($item['OrgFarmacy_id']))
				$OrgFarmacy_id = $item['OrgFarmacy_id'];

			$xml .='<R|*|v1="'.$LpuBuilding_id.'" 
				 |*|v2="'.$Lpu_id.'"
				 |*|v3="'.$OrgFarmacyIndex_id.'"
				 |*|v4="'.$typeLs.'"
				 |*|v5="'.$action.'" 
				 |*|v6="'.$pmUser.'"
				 |*|v7="'.$OrgFarmacy_id.'" ></R>';

			;

		}

		$xml .= '</RD>';

		$xml = strtr($xml, array(PHP_EOL=>'', " "=>""));
		$xml = str_replace("|*|", " ", $xml);
		//echo htmlspecialchars($xml);
		//echo '<pre>' . print_r($xml, 1) . '</pre>';
		//exit;

		$params = array('xml'=>(string)$xml);
		if ($data['WhsDocumentCostItemType_id']) {
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
			$setData = 'Set @WhsDocumentCostItemType_id = ' .$params['WhsDocumentCostItemType_id'] .';';
		}
				
		//echo '<pre>' . print_r($setData, 1) . '</pre>'; exit;
		
		$query = "
		Declare 
		@xml nvarchar(max),
		@WhsDocumentCostItemType_id bigint,
		@Error_Code int,
		@Error_Message varchar(4000)
		
			{$setData}
			Set @xml = :xml;

			 exec [r2].[p_saveMoByFarmacy] @xml, @WhsDocumentCostItemType_id, @Error_Code, @Error_Message

			 Select @Error_Code as Error_Code, @Error_Message as Error_Msg ;  
			 
				";
			
		//echo '<pre>' . print_r(getDebugSQL($query, $params), 1) . '</pre>'; exit;

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return array(0 => array('success' => true));

		}
		else {
			return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
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
				@ReceptWrong_id		= :ReceptWrong_id, -- Идентификатор записи
				@EvnRecept_id		= :EvnRecept_id,  -- Идентификатор рецепта
				@OrgFarmacy_id		= :OrgFarmacy_id, --  Индификатор аптека
				@Org_id				= :Org_id, -- идентификатор организации (аптеки)
				@ReceptWrong_decr	= :ReceptWrong_decr, -- Причина отказа
				@pmUser_id			= :pmUser_id, -- ID пользователя, который назначил прививку
				@Error_Code			= @ErrCode output,    -- Код ошибки
				@Error_Message		= @ErrMessage output -- Тект ошибки
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg
		";

		$queryParams['ReceptWrong_id'] = !empty($data['ReceptWrong_id'])?$data['ReceptWrong_id']:null;
		$queryParams['EvnRecept_id'] = $data['EvnRecept_id'];
		$queryParams['OrgFarmacy_id'] = !empty($data['OrgFarmacy_id'])?$data['OrgFarmacy_id']:null;
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
	 * Получение данных для формы признания рецепта неправильно выписанным
	 */
	function loadReceptWrongInfo($data) {
		$queryParams = array();

		$query = "
			Declare
				@EvnRecept_id bigint = :EvnRecept_id;

			SELECT TOP 1
				ReceptWrong_id
				,EvnRecept_id
				,Org_id
				,ReceptWrong_Decr
			FROM dbo.ReceptWrong with(nolock)
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
	 * Сохранение данных о прикреплении подразделений МО к аптеке
	 */
	function saveLpuBuildingLinkDataFromJSON($data) {
		$result = array();

		try {
			$this->beginTransaction();

			foreach ($data['LinkDataJSON'] as $record) {
				$ofi_add_list = array(); //массив содержащий идентификаторы признака OrgFarmacyIndex_IsNarko, которые нужно добавить
				$ofi_del_list = array(); //массив содержащий идентификаторы записей, которые необходимо удалить

				//получение данных о текущих прикреплениях подраздаеления МО к аптеке
				$query = "
					select
						ofi.OrgFarmacyIndex_id,
						ofi.OrgFarmacyIndex_IsNarko
					from
						v_OrgFarmacyIndex ofi with (nolock)
					where
						ofi.Lpu_id = :Lpu_id and
						ofi.OrgFarmacy_id = :OrgFarmacy_id and
						isnull(ofi.WhsDocumentCostItemType_id, 0) = isnull(:WhsDocumentCostItemType_id, 0) and
						ofi.LpuBuilding_id = :LpuBuilding_id
				";
				$ofi_list = $this->queryResult($query, array(
					'Lpu_id' => $data['Lpu_id'],
					'OrgFarmacy_id' => $data['OrgFarmacy_id'],
					'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
					'LpuBuilding_id' => $record->LpuBuilding_id
				));

				$isnarko_list = array(); //массив значений OrgFarmacyIndex_IsNarko которые полагается иметь для указанной группы (либо для данного режима работы функции)

				switch ($record->state) {
					case 'add':
					case 'edit':
						switch ($record->LsGroup_id) {
							case 1: //Все ЛП
								$isnarko_list = array(1, 2);
								break;
							case 2: //Все кроме НС и ПВ
								$isnarko_list = array(1);
								break;
							case 3: //НС и ПВ
								$isnarko_list = array(2);
								break;
						}
						break;
					case 'delete':
						$isnarko_list = array(); //если нам нужно удалить все записи, указываем пустой список допустимых значений OrgFarmacyIndex_IsNarko
						break;
				}

				//ищем лишние записи
				foreach ($ofi_list as $ofi_data) {
					if (!in_array($ofi_data['OrgFarmacyIndex_IsNarko'], $isnarko_list)) {
						$ofi_del_list[] = $ofi_data['OrgFarmacyIndex_id'];
					}
				}

				//ищем отсутствующие записи
				foreach ($isnarko_list as $isnarko_id) {
					$id_exists = false;
					foreach ($ofi_list as $ofi_data) {
						if ($ofi_data['OrgFarmacyIndex_IsNarko'] == $isnarko_id) {
							$id_exists = true;
						}
					}
					if (!$id_exists) {
						$ofi_add_list[] = $isnarko_id;
					}
				}

				//добавление записей
				if (is_array($ofi_add_list) && count($ofi_add_list)) {
					foreach ($ofi_add_list as $ofi_isnarko_id) {
						//проверка наличия прикрепления с данными параметрами к другой аптеке
						/*$query = "
							select
								ofi.OrgFarmacyIndex_id,
								ofr.OrgFarmacy_Name,
								lb.LpuBuilding_Name,(
								case
									when isnull(ofi.OrgFarmacyIndex_IsNarko, 0) = 1 then 'Все кроме НС и ПВ'
									when isnull(ofi.OrgFarmacyIndex_IsNarko, 0) = 2 then 'НС и ПВ'
									else ''
								end) as LsGroup_Name
							from
								v_OrgFarmacyIndex ofi with (nolock)
								left join v_OrgFarmacy ofr with (nolock) on ofr.OrgFarmacy_id = ofi.OrgFarmacy_id
								left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = ofi.LpuBuilding_id
							where
								ofi.Lpu_id = :Lpu_id and
								ofi.OrgFarmacy_id <> :OrgFarmacy_id and
								isnull(ofi.WhsDocumentCostItemType_id, 0) = isnull(:WhsDocumentCostItemType_id, 0) and
								ofi.LpuBuilding_id = :LpuBuilding_id and
								ofi.OrgFarmacyIndex_IsNarko = :OrgFarmacyIndex_IsNarko
						";
						$check_data = $this->getFirstRowFromQuery($query, array(
							'Lpu_id' => $data['Lpu_id'],
							'OrgFarmacy_id' => $data['OrgFarmacy_id'],
							'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
							'LpuBuilding_id' => $record->LpuBuilding_id,
							'OrgFarmacyIndex_IsNarko' => $ofi_isnarko_id
						));
						if (!empty($check_data['OrgFarmacyIndex_id'])) {
							$err_msg = "Подразделение {$check_data['LpuBuilding_Name']} уже имеет прикрепление к аптеке {$check_data['OrgFarmacy_Name']} по данной программе ЛЛО и группе \"{$check_data['LsGroup_Name']}\"";
							throw new Exception($err_msg);
						}*/

						$save_result = $this->saveObject('OrgFarmacyIndex', array(
							'Server_id' => $this->sessionParams['server_id'],
							'Lpu_id' => $data['Lpu_id'],
							'OrgFarmacy_id' => $data['OrgFarmacy_id'],
							'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
							'LpuBuilding_id' => $record->LpuBuilding_id,
							'OrgFarmacyIndex_IsNarko' => $ofi_isnarko_id,
							'OrgFarmacyIndex_Index' => 0,
							'OrgFarmacyIndex_IsEnabled' => 1
						));
						if (empty($save_result['OrgFarmacyIndex_id'])) {
							throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных о прикреплении произошла ошибка");
						}
					}
				}

				//удаление записей
				if (is_array($ofi_del_list) && count($ofi_del_list)) {
					foreach ($ofi_del_list as $ofi_id) {
						$query = "
							declare
								@Error_Code int,
								@Error_Message varchar(4000);
				
							execute dbo.p_OrgFarmacyIndex_del
								@OrgFarmacyIndex_id = :OrgFarmacyIndex_id,
								@IsRemove = :IsRemove,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;
				
							select @Error_Code as Error_Code, @Error_Message as Error_Msg;
						";
						$delete_result = $this->getFirstRowFromQuery($query, array(
							'OrgFarmacyIndex_id' => $ofi_id,
							'IsRemove' => 2
						));
						if ($delete_result && is_array($delete_result)) {
							if(!empty($delete_result['Error_Msg'])) {
								throw new Exception($delete_result['Error_Msg']);
							}
						} else {
							throw new Exception('При удалении произошла ошибка');
						}
					}
				}
			}
			$result['success'] = true;
			$this->commitTransaction();
		} catch (Exception $e) {
			$result['success'] = false;
			$result['Error_Msg'] = $e->getMessage();
			$this->rollbackTransaction();
		}

		return $result;
	}

	/**
	 * Сохранение данных о прикреплении подразделений МО к складам аптеки
	 */
	function saveLpuBuildingStorageLinkDataFromJSON($data) {
		$result = array();
		$edited_data = array();

		//шаблон данных содержит часть неизменяемых данных
		$ofi_data = array(
			'OrgFarmacyIndex_id' => null,
			'Server_id' => $this->sessionParams['server_id'],
			'Lpu_id' => $data['Lpu_id'],
			'OrgFarmacy_id' => $data['OrgFarmacy_id'],
			'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
			'LpuBuilding_id' => null,
			'OrgFarmacyIndex_IsNarko' => null,
			'OrgFarmacyIndex_Index' => 0,
			'OrgFarmacyIndex_IsEnabled' => 1,
			'Storage_id' => null
		);

		try {
			$this->beginTransaction();

			foreach ($data['LinkDataJSON'] as $record) {
				//по умолчанию действие определяется через state
				$action = $record->state;

				//определяем изменяемую часть данных
				$ofi_data['OrgFarmacyIndex_id'] = !empty($record->OrgFarmacyIndex_id) ? $record->OrgFarmacyIndex_id : null;
				$ofi_data['LpuBuilding_id'] = $record->LpuBuilding_id;
				$ofi_data['OrgFarmacyIndex_IsNarko'] = ($record->LsGroup_id == 1 || $record->LsGroup_id == 2) ? $record->LsGroup_id : null;
				$ofi_data['Storage_id'] = !empty($record->Storage_id) ? $record->Storage_id : null;


				//ищем среди данных уже существующую запись с заданными параметрами и исходя из этого определяем действие
				$query = "
					select
						count(ofi.OrgFarmacyIndex_id) as cnt
					from
						v_OrgFarmacyIndex ofi with (nolock)
					where
						ofi.Lpu_id = :Lpu_id and
						ofi.OrgFarmacy_id = :OrgFarmacy_id and
						isnull(ofi.WhsDocumentCostItemType_id, 0) = isnull(:WhsDocumentCostItemType_id, 0) and
						isnull(ofi.LpuBuilding_id, 0) = isnull(:LpuBuilding_id, 0) and
						isnull(ofi.OrgFarmacyIndex_IsNarko, 0) = isnull(:OrgFarmacyIndex_IsNarko, 0) and
						isnull(ofi.Storage_id, 0) = isnull(:Storage_id, 0);
				";
				$ofi_cnt = $this->getFirstResultFromQuery($query, $ofi_data);

				if ($ofi_cnt > 0) { //если обнаружено дублирование
					if ($action == 'add') { //при попытке добавления дубля, пропускаем запись
						$action = null;
					}
					if ($action == 'edit') { //если редактирование записи приведет к дублированию, заменяем редактирование на удаление
						$action = 'delete';
					}
				}

				switch ($action) {
					case 'add':
						$ofi_data['OrgFarmacyIndex_id'] = null;
					case 'edit':
						$save_result = $this->saveObject('OrgFarmacyIndex', $ofi_data);
						if (empty($save_result['OrgFarmacyIndex_id'])) {
							throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных о прикреплении произошла ошибка");
						}
						break;
					case 'delete':
						if (!empty($ofi_data['OrgFarmacyIndex_id'])) {
							$query = "
								declare
									@Error_Code int,
									@Error_Message varchar(4000);
					
								execute dbo.p_OrgFarmacyIndex_del
									@OrgFarmacyIndex_id = :OrgFarmacyIndex_id,
									@IsRemove = :IsRemove,
									@Error_Code = @Error_Code output,
									@Error_Message = @Error_Message output;
					
								select @Error_Code as Error_Code, @Error_Message as Error_Msg;
							";
							$delete_result = $this->getFirstRowFromQuery($query, array(
								'OrgFarmacyIndex_id' => $ofi_data['OrgFarmacyIndex_id'],
								'IsRemove' => 2
							));
							if ($delete_result && is_array($delete_result)) {
								if(!empty($delete_result['Error_Msg'])) {
									throw new Exception($delete_result['Error_Msg']);
								}
							} else {
								throw new Exception('При удалении произошла ошибка');
							}
						}
						break;
				}

				//сбор массива редактируемых наборов
				if (!empty($action)) {
					if (empty($edited_data[$ofi_data['LpuBuilding_id']])) {
						$edited_data[$ofi_data['LpuBuilding_id']] = array();
						$edited_data[$ofi_data['LpuBuilding_id']][$record->LsGroup_id] = 1;
					}
				}
			}

			//контроль наличия дефолтной записи для каждого набора отредактированных данных
			foreach($edited_data as $lb_id => $lsg_arr) {
				foreach($lsg_arr as $lsg_id => $itm) {
					$ofi_data['LpuBuilding_id'] = $record->LpuBuilding_id;
					$is_narko_id = ($lsg_id == 1 || $lsg_id == 2) ? $lsg_id : null;

					//подсчет обычных и дефолтных записей для набора данных
					$query = "
						select
							sum(case when ofi.Storage_id is not null then 1 else 0 end) as stg_cnt,
							sum(case when ofi.Storage_id is null then 1 else 0 end) as null_cnt,
							max(case when ofi.Storage_id is null then ofi.OrgFarmacyIndex_id else 0 end) as max_null_id
						from
							v_OrgFarmacyIndex ofi with (nolock)
						where
							ofi.Lpu_id = :Lpu_id and
							ofi.OrgFarmacy_id = :OrgFarmacy_id and
							isnull(ofi.WhsDocumentCostItemType_id, 0) = isnull(:WhsDocumentCostItemType_id, 0) and
							isnull(ofi.LpuBuilding_id, 0) = isnull(:LpuBuilding_id, 0) and
							isnull(ofi.OrgFarmacyIndex_IsNarko, 0) = isnull(:OrgFarmacyIndex_IsNarko, 0)
					";
					$cnt_data = $this->getFirstRowFromQuery($query, array(
						'Lpu_id' => $ofi_data['Lpu_id'],
						'OrgFarmacy_id' => $ofi_data['OrgFarmacy_id'],
						'WhsDocumentCostItemType_id' => $ofi_data['WhsDocumentCostItemType_id'],
						'LpuBuilding_id' => $lb_id,
						'OrgFarmacyIndex_IsNarko' => $is_narko_id
					));

					//корректировка
					if ($cnt_data['stg_cnt'] > 0) {
						if ($cnt_data['null_cnt'] > 0 && $cnt_data['max_null_id'] > 0) { //нужно удалить дефолтную запись (считаем что такая может быть только одна в пределах набора)
							$query = "
								declare
									@Error_Code int,
									@Error_Message varchar(4000);
					
								execute dbo.p_OrgFarmacyIndex_del
									@OrgFarmacyIndex_id = :OrgFarmacyIndex_id,
									@IsRemove = :IsRemove,
									@Error_Code = @Error_Code output,
									@Error_Message = @Error_Message output;
					
								select @Error_Code as Error_Code, @Error_Message as Error_Msg;
							";
							$delete_result = $this->getFirstRowFromQuery($query, array(
								'OrgFarmacyIndex_id' => $ofi_data['OrgFarmacyIndex_id'],
								'IsRemove' => 2
							));
							if ($delete_result && is_array($delete_result)) {
								if(!empty($delete_result['Error_Msg'])) {
									throw new Exception($delete_result['Error_Msg']);
								}
							} else {
								throw new Exception('При удалении произошла ошибка');
							}
						}
					} else {
						if ($cnt_data['null_cnt'] == 0) { //нужно добавить дефолтную запись
							$ofi_data['OrgFarmacyIndex_id'] = null;
							$ofi_data['LpuBuilding_id'] = $lb_id;
							$ofi_data['OrgFarmacyIndex_IsNarko'] = $is_narko_id;
							$ofi_data['Storage_id'] = null;

							$save_result = $this->saveObject('OrgFarmacyIndex', $ofi_data);
							if (empty($save_result['OrgFarmacyIndex_id'])) {
								throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении данных о прикреплении произошла ошибка");
							}
						}
					}
				}
			}

			$result['success'] = true;
			$this->commitTransaction();
		} catch (Exception $e) {
			$result['successs'] = false;
			$result['Error_Msg'] = $e->getMessage();
			$this->rollbackTransaction();
		}

		return $result;
	}

	/**
	 * Удаление данных о прикреплении подразделений МО к аптеке
	 */
	function deleteLpuBuildingLinkData($data) {
		$result = array();

		try {
			$this->beginTransaction();

			//получение данных о текущих прикреплениях подраздаеления МО к аптеке
			$query = "
				select
					ofi.OrgFarmacyIndex_id,
					ofi.OrgFarmacyIndex_IsNarko
				from
					v_OrgFarmacyIndex ofi with (nolock)
				where
					ofi.Lpu_id = :Lpu_id and
					ofi.OrgFarmacy_id = :OrgFarmacy_id and
					isnull(ofi.WhsDocumentCostItemType_id, 0) = isnull(:WhsDocumentCostItemType_id, 0) and
					ofi.LpuBuilding_id is not null
			";
			$ofi_list = $this->queryResult($query, array(
				'Lpu_id' => $data['Lpu_id'],
				'OrgFarmacy_id' => $data['OrgFarmacy_id'],
				'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']
			));

			//удаление записей
			if (is_array($ofi_list) && count($ofi_list)) {
				foreach ($ofi_list as $ofi_data) {
					$query = "
						declare
							@Error_Code int,
							@Error_Message varchar(4000);
			
						execute dbo.p_OrgFarmacyIndex_del
							@OrgFarmacyIndex_id = :OrgFarmacyIndex_id,
							@IsRemove = :IsRemove,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;
			
						select @Error_Code as Error_Code, @Error_Message as Error_Msg;
					";
					$delete_result = $this->getFirstRowFromQuery($query, array(
						'OrgFarmacyIndex_id' => $ofi_data['OrgFarmacyIndex_id'],
						'IsRemove' => 2
					));
					if ($delete_result && is_array($delete_result)) {
						if(!empty($delete_result['Error_Msg'])) {
							throw new Exception($delete_result['Error_Msg']);
						}
					} else {
						throw new Exception('При удалении произошла ошибка');
					}
				}
			}

			$result['success'] = true;
			$this->commitTransaction();
		} catch (Exception $e) {
			$result['success'] = false;
			$result['Error_Msg'] = $e->getMessage();
			$this->rollbackTransaction();
		}

		return $result;
	}

	/**
	 * Получение списка аптек для комбобокса
	 */
	function loadOrgFarmacyCombo($data) {
		$where = array();
		$params = array();

		if (!empty($data['OrgFarmacy_id'])) {
			$where[] = "ofr.OrgFarmacy_id = :OrgFarmacy_id";
			$params['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
		} else {
			if (!empty($data['query'])) {
				$where[] = "(ofr.OrgFarmacy_Code like :query or ofr.OrgFarmacy_Nick like :query)";
				$params['query'] = $data['query']."%";
			}
		}

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
					{$where_clause}
			";
		}

		$query = "
			select
				ofr.OrgFarmacy_id,
				isnull(ofr.OrgFarmacy_Code, '') as OrgFarmacy_Code,
				isnull(ofr.OrgFarmacy_Name, '') as OrgFarmacy_Name,
				isnull(ofr.OrgFarmacy_Nick, '') as OrgFarmacy_Nick,
				isnull(ofr.OrgFarmacy_HowGo, '') as OrgFarmacy_HowGo
			from
				v_OrgFarmacy ofr with (nolock)
			{$where_clause}
		";
		$result = $this->queryResult($query, $params);

		return is_array($result) && count($result) ? $result : false;
	}

	/**
	 * Получение списка складов аптеки для комбобокса
	 */
	function loadOrgFarmacyStorageCombo($data) {
		$query = "
			select distinct
				s.Storage_id,
				s.Storage_Name
			from
				v_OrgFarmacy ofr with (nolock)
				left join v_StorageStructLevel ssl with (nolock) on ssl.Org_id = ofr.Org_id
				left join v_Storage s with (nolock) on s.Storage_id = ssl.Storage_id
			where
				ofr.OrgFarmacy_id = :OrgFarmacy_id and
				ssl.Storage_id is not null;
		";
		$result = $this->queryResult($query, $data);

		return is_array($result) && count($result) ? $result : false;
	}
}
