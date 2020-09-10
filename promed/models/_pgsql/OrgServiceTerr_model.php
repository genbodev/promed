<?php
/**
 * OrgServiceTerr_model - модель, для работы с таблицей OrgServiceTerr
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Марков Андрей
 * @version      май 2010
 *
 * @property CI_DB_driver $db
 */

class OrgServiceTerr_model extends swPgModel
{
	public $inputRules = [
		"createOrgServiceTerr" => [
			["field" => "Org_id", "label" => "Идентификатор организации", "rules" => "required", "type" => "id"],
			["field" => "KLCountry_id", "label" => "Идентификатор страны", "rules" => "required", "type" => "id"],
			["field" => "KLRgn_id", "label" => "Идентификатор региона", "rules" => "required", "type" => "id"],
			["field" => "KLSubRgn_id", "label" => "Идентификатор района", "rules" => "", "type" => "id"],
			["field" => "KLCity_id", "label" => "Идентификатор города", "rules" => "", "type" => "id"],
			["field" => "KLTown_id", "label" => "Идентификатор нас. пункта", "rules" => "", "type" => "id"],
			["field" => "KLAreaType_id", "label" => "Идентификатор типа населенного пункта", "rules" => "", "type" => "id"]
		],
		"updateOrgServiceTerr" => [
			["field" => "OrgServiceTerr_id", "label" => "Идентификатор территории обслуживания", "rules" => "required", "type" => "id"],
			["field" => "KLCountry_id", "label" => "Идентификатор страны", "rules" => "", "type" => "id"],
			["field" => "KLRgn_id", "label" => "Идентификатор региона", "rules" => "", "type" => "id"],
			["field" => "KLSubRgn_id", "label" => "Идентификатор района", "rules" => "", "type" => "id"],
			["field" => "KLCity_id", "label" => "Идентификатор города", "rules" => "", "type" => "id"],
			["field" => "KLTown_id", "label" => "Идентификатор нас. пункта", "rules" => "", "type" => "id"],
			["field" => "KLAreaType_id", "label" => "Идентификатор типа населенного пункта", "rules" => "", "type" => "id"]
		],
		"deleteOrgServiceTerr" => [
			["field" => "OrgServiceTerr_id", "label" => "Идентификатор территории обслуживания", "rules" => "required", "type" => "id"]
		]
	];

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение списка территорий обслуживания
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgServiceTerrGrid($data)
	{
		$params = ["Org_id" => $data["Org_id"]];
		$query = "
			select
				OST.OrgServiceTerr_id as \"OrgServiceTerr_id\",
				Country.KLCountry_Name as \"KLCountry_Name\",
				RGN.KLRgn_Name as \"KLRgn_Name\",
				SRGN.KLSubRgn_Name as \"KLSubRgn_Name\",
				City.KLCity_Name as \"KLCity_Name\",
				Town.KLTown_Name as \"KLTown_Name\",
				KLAT.KLAreaType_Name as \"KLAreaType_Name\"
			from
				v_OrgServiceTerr OST
				left join v_KLCountry Country on Country.KLCountry_id = OST.KLCountry_id
				left join v_KLRgn RGN on RGN.KLRgn_id = OST.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = OST.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = OST.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = OST.KLTown_id
				left join v_KLAreaType KLAT on KLAT.KLAreaType_id = OST.KLAreaType_id
			where OST.Org_id = :Org_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение территории обслуживания
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveOrgServiceTerr($data)
	{
		/**@var CI_DB_result $result */
		$procedure = (empty($data['OrgServiceTerr_id'])) ? "p_OrgServiceTerr_ins" : "p_OrgServiceTerr_upd";
		$query = "
            select count(1) as \"count\"
            from v_OrgServiceTerr
            where coalesce(KLCountry_id, 0) = coalesce(:KLCountry_id, 0)
              and coalesce(KLRgn_id, 0) = coalesce(:KLRgn_id, 0)
              and coalesce(KLSubRgn_id, 0) = coalesce(:KLSubRgn_id, 0)
              and coalesce(KLCity_id, 0) = coalesce(:KLCity_id, 0)
              and coalesce(KLTown_id, 0) = coalesce(:KLTown_id, 0)
              and coalesce(KLAreaType_id, 0) = coalesce(:KLAreaType_id, 0)
              and Org_id = :Org_id
              and OrgServiceTerr_id <> coalesce(:OrgServiceTerr_id::bigint, 0)
        ";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$response = $result->result("array");
			if (!empty($response[0]["count"]) && $response[0]["count"] > 0) {
				throw new Exception("Запись с введенными данными уже существует.");
			}
		}
		$query = "
		    select 
				OrgServiceTerr_id as \"OrgServiceTerr_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure} (
				OrgServiceTerr_id := :OrgServiceTerr_id,
				Org_id := :Org_id,
				KLCountry_id := :KLCountry_id,
				KLRgn_id := :KLRgn_id,
				KLSubRgn_id := :KLSubRgn_id,
				KLCity_id := :KLCity_id,
				KLTown_id := :KLTown_id,
				KLAreaType_id := :KLAreaType_id,
				pmUser_id := :pmUser_id
				);
		";
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение списка территорий обслуживания
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgServiceTerrEditForm($data)
	{
		$filter = "";
		$params = ["OrgServiceTerr_id" => $data["OrgServiceTerr_id"]];
        if (!isSuperadmin() && !empty($data['Lpu_id'])) {
            $params['Lpu_id'] = $data['Lpu_id'];
            $filter .= " and L.Lpu_id = :Lpu_id";
        }
        $query = "
			Select
				OST.OrgServiceTerr_id as \"OrgServiceTerr_id\",
				OST.Org_id as \"Org_id\",
				OST.KLCountry_id as \"KLCountry_id\",
				OST.KLRgn_id as \"KLRgn_id\",
				OST.KLSubRgn_id as \"KLSubRgn_id\",
				OST.KLCity_id as \"KLCity_id\",
				OST.KLTown_id as \"KLTown_id\",
				OST.KLAreaType_id as \"KLAreaType_id\",
				KAS.KLAreaStat_id as \"KLAreaStat_id\"
			from
				v_OrgServiceTerr OST
				left join lateral (
					select KLAreaStat_id
					from v_KLAreaStat
					where (KLCountry_id is null or KLCountry_id = OST.KLCountry_id)
					  and (KLRgn_id is null or KLRgn_id = OST.KLRgn_id)
					  and (KLSubRgn_id is null or KLSubRgn_id = OST.KLSubRgn_id)
					  and (KLCity_id is null or KLCity_id = OST.KLCity_id)
					  and (KLTown_id is null or KLTown_id = OST.KLTown_id)
					limit 1
				) as KAS on true
				left join v_Lpu L on L.Org_id = OST.Org_id
			where OST.OrgServiceTerr_id = :OrgServiceTerr_id
			{$filter}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}