<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Org_model - модель, для работы с таблицей Org и производными (OrgDep, OrgSmo)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      14.05.2009
*/

class Lis_Org_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Возвращает список организаций
	 */
	function getLpuList($data)
	{
		$filter = "";
		$filterorg = "";
		$queryParams = array();

		if (isset($data['Org_id'])) {
			$filter .= " and L.Org_id = :Org_id";
			$filterorg .= " and O.Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_id'];

			$query = "
				SELECT
					O.Org_id as \"Org_id\",
					L.Lpu_id as \"Lpu_id\",
					O.Org_Code as \"Org_Code\",
					O.Org_Nick as \"Org_Nick\",
					O.Org_Name as \"Org_Name\",
					null as \"Lpu_f003mcod\"
				FROM
					Org O
					left join v_Lpu L on L.Org_id = O.Org_id
				WHERE (O.OrgType_id=11 or L.Lpu_id is not null)		-- Не у всех ЛПУ в таблице Org проставлен OrgType_id
					" . $filterorg . "
			";
		} elseif (isset($data['Lpu_oid'])) {
			$filter .= " and L.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_oid'];

			$query = "
				SELECT
					L.Org_id as \"Org_id\",
					L.Lpu_id as \"Lpu_id\",
					null as \"Org_Code\",
					trim(from L.Lpu_Nick) as \"Org_Nick\",
					trim(from L.Lpu_Name) as \"Org_Name\",
					L.Lpu_f003mcod as \"Lpu_f003mcod\"
				FROM
					v_Lpu L
				WHERE (1 = 1)
					" . $filter . "
			";
		} else {
			if (isset($data['Org_Name'])) {
				$filter .= " and L.Lpu_Name LIKE '%'||upper(:Lpu_Name)||'%'";
				$filterorg .= " and O.Org_Name LIKE '%'||upper(:Lpu_Name)||'%'";
				$queryParams['Lpu_Name'] = $data['Org_Name'];
			}

			if (isset($data['Org_Nick'])) {
				$filter .= " and L.Lpu_Nick LIKE '%'||upper(:Lpu_Nick)||'%'";
				$filterorg .= " and O.Org_Nick LIKE '%'||upper(:Lpu_Nick)||'%'";
				$queryParams['Lpu_Nick'] = $data['Org_Nick'];
			}

			if (isset($data['DispClass_id'])) {
				if (!empty($data['Disp_consDate'])) {
					$queryParams['Disp_consDate'] = $data['Disp_consDate'];
				} else {
					$queryParams['Disp_consDate'] = NULL;
				}
				//$filter .= " and exists(select top 1 LMTL.LpuMobileTeamLink_id from v_LpuMobileTeamLink LMTL (nolock) inner join v_LpuMobileTeam LMT (nolock) on LMT.LpuMobileTeam_id = LMTL.LpuMobileTeam_id where LMT.Lpu_id = L.Lpu_id and LMTL.DispClass_id = :DispClass_id and LMT.LpuMobileTeam_begDate <= :Disp_consDate and (LMT.LpuMobileTeam_endDate >= :Disp_consDate or LMT.LpuMobileTeam_endDate is NULL) )";
				$queryParams['DispClass_id'] = $data['DispClass_id'];
			}

			$query = "";
			if (empty($data['onlyFromDictionary']) || $data['onlyFromDictionary'] == false) {
				$query .= "
				SELECT
					O.Org_id as \"Org_id\",
					null as \"Lpu_id\",
					O.Org_Code as \"Org_Code\",
					O.Org_Nick as \"Org_Nick\",
					O.Org_Name as \"Org_Name\",
					null as \"Lpu_f003mcod\",
					to_char(o.Org_begDate, 'dd.mm.yyyy') as \"Org_begDate\",
					to_char(o.Org_endDate, 'dd.mm.yyyy') as \"Org_endDate\"
				FROM
					Org O
				WHERE O.OrgType_id=11
					and not exists (select Lpu_id from v_Lpu where Org_id = O.Org_id limit 1)
					" . $filterorg . "
					union all
				";
			}

			$query .= "
				SELECT
					L.Org_id as \"Org_id\",
					L.Lpu_id as \"Lpu_id\",
					null as \"Org_Code\",
					trim(from L.Lpu_Nick) as \"Org_Nick\",
					trim(from L.Lpu_Name) as \"Org_Name\",
					coalesce(d.Lpu_f003mcod, o.Org_f003mcod) as \"Lpu_f003mcod\",
					to_char(o.Org_begDate, 'dd.mm.yyyy') as \"Org_begDate\",
					to_char(o.Org_endDate, 'dd.mm.yyyy') as \"Org_endDate\"
				FROM
					v_Lpu L
					left join v_Org o on o.Org_id = l.Org_id
					left join lateral(
						select
							Lpu_f003mcod
						from v_Lpu lp
						where lp.Lpu_id = L.Lpu_id
					) d on true
				WHERE (1 = 1)
					" . $filter . "
			";
		}

		//echo getDebugSQL($query, $queryParams);exit();
		$res = $this->db->query($query, $queryParams);

		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}
}
