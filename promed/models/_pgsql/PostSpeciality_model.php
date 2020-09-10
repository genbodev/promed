<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PostSpeciality_model - модель для работы cо стыковочной таблицей должностей и специальностей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 *
 * @property CI_DB_driver $db
 */
class PostSpeciality_model extends swPgModel
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение списка соответствий
	 * @param $data
	 * @return array|bool
	 */
	function loadPostSpecialityList($data)
	{
		$filter = "";
		if (isset($data["Post_Name"])) {
			$filter .= "
				and p.name ilike case when :Post_Name is not null
					then '%'||:Post_Name||'%'
					else ''
				end
			";
		}
		if (isset($data["Speciality_Name"])) {
			$filter .= "
				and spec.name ilike case when :Speciality_Name is not null
					then '%'||:Speciality_Name||'%'
					else ''
				end
			";
		}
		$query = "
			select
				--select
				ps.PostSpeciality_id as \"PostSpeciality_id\",
				ps.Post_id as \"Post_id\",
				p.name as \"Post_Name\",
				ps.Speciality_id as \"Speciality_id\",
				spec.name as \"Speciality_Name\"
				--end select
			from
				--from
				persis.PostSpeciality ps
				left join persis.Post p on ps.Post_id = p.id
				left join persis.Speciality spec on ps.Speciality_id = spec.id
				--end from
			where
				--where
				ps.Speciality_id is not null 
				and ps.Post_id is not null
				{$filter}
				--end where
			order by
				--order by
				ps.PostSpeciality_id
				--end order by
		";
		return $this->getPagingResponse($query, $data, $data["start"], $data["limit"], true);
	}

	/**
	 * Получение списка специальностей
	 * @return array|false
	 */
	function loadSpecialityList()
	{
		$query = "
			select
				s.id as \"id\",
				s.name as \"name\",
				fp.id as \"code\",
				fp.fullname as \"fullname\"
			from
				persis.Speciality s
				inner join persis.FRMPSertificateSpeciality fp on s.frmpEntry_id = fp.id
		";
		return $this->queryResult($query);
	}

	/**
	 * Сохранение нового соответствия
	 * @param $data
	 * @return array|false
	 */
	function savePostSpecialityPair($data)
	{
		$query = "
			insert into persis.PostSpeciality (
			    Post_id,
			    Speciality_id,
			    pmUser_insID,
			    pmUser_updID,
			    PostSpeciality_insDT,
			    PostSpeciality_updDT
			) values (
			    :Post_id,
			    :Speciality_id,
			    :pmUser_id,
			    :pmUser_id,
			    tzgetdate(),
			    tzgetdate()
			) returning postspeciality_id as \"PostSpeciality_id\", 0 as \"Error_Code\", '' as \"Error_Msg\"
		";
		$queryParams = [
			"Post_id" => $data["Post_id"],
			"Speciality_id" => $data["Speciality_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $this->queryResult($query, $queryParams);
		return $result;
	}

	/**
	 * Удаление соответствия
	 * @param $data
	 * @return array|false
	 */
	function deletePostSpecialityPair($data)
	{
		$query = "delete from persis.PostSpeciality where PostSpeciality_id = :PostSpeciality_id;";
		$queryParams = ["PostSpeciality_id" => $data["PostSpeciality_id"]];
		$this->queryResult($query, $queryParams);
		return [["Error_Code" => "", "Error_Msg" => ""]];
	}

	/**
	 * Редактирование соответствия
	 * @param $data
	 * @return array|false
	 */
	function editPostSpecialityPair($data)
	{
		$query = "
			update persis.PostSpeciality
			set
				Post_id = :Post_id,
				Speciality_id = :Speciality_id,
				pmUser_updID = :pmUser_id,
				PostSpeciality_updDT = tzgetdate()
			where PostSpeciality_id = :PostSpeciality_id
		";
		$queryParams = [
			"Post_id" => $data["Post_id"],
			"Speciality_id" => $data["Speciality_id"],
			"PostSpeciality_id" => $data["PostSpeciality_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$this->queryResult($query, $queryParams);
		return [["Error_Code" => "", "Error_Msg" => ""]];
	}
}