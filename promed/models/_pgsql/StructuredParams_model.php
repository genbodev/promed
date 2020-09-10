<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * StructuredParams - модель для работы со структурированными параметрами
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Petukhov Ivan (ethereallich@gmail.com)
 * @version			07.02.2013
 *
 * @property CI_DB_driver $db
 * @property MedPersonal_model $mpmodel
 */
class StructuredParams_model extends swPgModel
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает данные для дерева структурных параметров
	 * @param $data
	 * @return array|bool
	 */
	function getStructuredParamsTreeBranch($data)
	{
		$params = [];
		$whereString = ($data["node"] == "root") ? "where sp.StructuredParams_pid is null" : "where sp.StructuredParams_pid = :StructuredParams_pid";
		if ($data["node"] != "root") {
			$params["StructuredParams_pid"] = $data["node"];
		}
		$query = "
			select
				sp.StructuredParams_id as id,
				sp.StructuredParams_Name as text,
				(case when sp.StructuredParamsType_id = 4 then 1 else 0 end) as leaf,
				sp.StructuredParams_pid as pid,
				sp.StructuredParams_rid as rid,
				sp.StructuredParams_Order as pos
			from v_StructuredParams sp
			{$whereString}
			order by sp.StructuredParams_Order
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		leafToInt($result);
		return $result;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getStructuredParamsByType($data)
	{
		$object = $data["object"];
		if ($object != "Age") {
			$whereString = "sp.StructuredParams_id = :StructuredParams_id";
			$query = "
				select
					sp.StructuredParams{$object}_id as \"id\",
					o.{$object}_Code as \"Code\",
					o.{$object}_Name as \"Name\"
				from
					v_StructuredParams{$object} sp
					left join v_{$object} o on sp.{$object}_id = o.{$object}_id
				where {$whereString}
			";
		} else {
			$query = "
				select
					sp.StructuredParamsAge_id as \"id\",
					StructuredParamsAge_From as \"AgeFrom\",
					StructuredParamsAge_To as \"AgeTo\"
				from v_StructuredParamsAge sp
				where sp.StructuredParams_id =:StructuredParams_id
			";
		}
		$queryParams = ["StructuredParams_id" => $data["StructuredParams_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return ["data" => $result->result("array")];
	}

	/**
	 * Возвращает список одного уровня структурированных параметров в виде грида
	 * @param $data
	 * @return array|bool
	 */
	function getStructuredParamsGridBranch($data)
	{
		$whereArray = [];
		$params = [];
		if (isset($data["StructuredParams_pid"])) {
			$params["StructuredParams_pid"] = $data["StructuredParams_pid"];
			$whereArray[] = "StructuredParams_pid = :StructuredParams_pid ";
		} else {
			$whereArray[] = "StructuredParams_pid is null ";
		}
		if (!empty($data["StructuredParams_Name"])) {
			$whereArray[] = "sp.StructuredParams_Name ilike '%'||:StructuredParams||'%'";
			$params["StructuredParams"] = $data["StructuredParams"];
		}
		$whereString = (count($whereArray) != 0) ? "where " . implode(" and ", $whereArray) : "";
		$query = "
			select
				sp.StructuredParams_id as \"StructuredParams_id\",
				sp.StructuredParams_SysNick as \"StructuredParams_SysNick\",
				sp.StructuredParams_Name as \"StructuredParams_Name\",
				sp.StructuredParamsType_id as \"StructuredParamsType_id\",
				StructuredParamsType_Name as \"StructuredParamsType_Name\",
				sp.StructuredParamsPrintType_id as \"StructuredParamsPrintType_id\",
				StructuredParamsPrintType_Name as \"StructuredParamsPrintType_Name\",
				(
				    select string_agg(cast(M.MedSpecOms_Code as varchar), ', ')
				    from
				    	StructuredParamsMedSpecOms SPM  
						inner join v_MedSpecOms M on M.MedSpecOms_id=SPM.MedSpecOms_id
					where sp.StructuredParams_id=SPM.StructuredParams_id
				) as \"MedSpecOms_Text\",
				(
				    select string_agg(D.Diag_code, ', ')
				    from
				    	StructuredParamsDiag SPD  
						inner join v_Diag D on D.Diag_id=SPD.Diag_id
					where sp.StructuredParams_id=SPD.StructuredParams_id
				) as \"MedSpecOms_DiagText\",
				(
					select string_agg(cast(X.XmlType_Code as varchar), ', ')
					from
						StructuredParamsxmlType SPX  
						inner join v_xmlType X on X.xmlType_id=SPX.xmlType_id
					where sp.StructuredParams_id=SPX.StructuredParams_id
				) as \"MedSpecOms_DocumentTypeText\",
				sp.StructuredParams_Order as \"StructuredParams_Order\"
			from
				v_StructuredParams sp
				left join StructuredParamsType spt on sp.StructuredParamsType_id = spt.StructuredParamsType_id
				left join StructuredParamsPrintType sppt on sp.StructuredParamsPrintType_id = sppt.StructuredParamsPrintType_id
			{$whereString}
			order by sp.StructuredParams_Order
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if(!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		$returnResult = [];
		foreach ($result as $resultItem) {
			$newItem = [
				"StructuredParams_id" => $resultItem["StructuredParams_id"],
				"StructuredParams_SysNick" => $resultItem["StructuredParams_SysNick"],
				"StructuredParams_Name" => $resultItem["StructuredParams_Name"],
				"StructuredParamsType_id" => $resultItem["StructuredParamsType_id"],
				"StructuredParamsType_Name" => $resultItem["StructuredParamsType_Name"],
				"StructuredParamsPrintType_id" => $resultItem["StructuredParamsPrintType_id"],
				"StructuredParamsPrintType_Name" => $resultItem["StructuredParamsPrintType_Name"],
				"MedSpecOms_Text" => $resultItem["MedSpecOms_Text"],
				"MedSpecOms_DiagText" => $resultItem["MedSpecOms_DiagText"],
				"MedSpecOms_DocumentTypeText" => $resultItem["MedSpecOms_DocumentTypeText"],
				"StructuredParams_Order" => $resultItem["StructuredParams_Order"]
			];
			if($resultItem["StructuredParamsType_id"] == 4) {
				$newItem["controls"] = [];
				$list = explode("[--]", $resultItem["StructuredParams_Name"]);
				for ($i = 0; $i < count($list); $i++) {
					if($i == 0) {
						$newItem["controls"][] = [
							"type" => "checkbox",
							"value" => trim($list[$i])
						];
					} else {
						$newItem["controls"][] = [
							"type" => "edit",
							"value" => trim($list[$i])
						];
					}
				}
			}
			$returnResult[] = $newItem;
		}
		return ["data" => $returnResult];
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getStructuredParamsExtJS6($data)
	{
		if (!isset($data["StructuredParams_pid"])) {
			return false;
		}
		$query = "
			with recursive q as (
			    select
			        v_StructuredParams.StructuredParams_id,
			        v_StructuredParams.StructuredParams_pid,
			        v_StructuredParams.StructuredParams_Name,
			        v_StructuredParams.StructuredParamsType_id,
			        0 as level,
			        v_StructuredParams.StructuredParams_Order,
			        row_number()over(partition by v_StructuredParams.StructuredParams_pid order by v_StructuredParams.StructuredParams_Order) / power(10.0, 0) as x
			    from structuredparams v_StructuredParams
			    where v_StructuredParams.StructuredParams_id = {$data["StructuredParams_pid"]}
			      and (v_StructuredParams.region_id = getregion() or v_StructuredParams.region_id is null)
			    union all
			    select
			        p.StructuredParams_id,
			        p.StructuredParams_pid,
			        p.StructuredParams_Name,
			        p.StructuredParamsType_id,
			        q.level + 1 as level,
			        p.StructuredParams_Order,
			        x + row_number()over(partition by p.StructuredParams_pid order by p.StructuredParams_Order) / power(10.0, q.level + 1)
			    from
			        structuredparams p
			        join q on p.StructuredParams_pid = q.StructuredParams_id
			    where p.region_id = 59 or p.region_id is null
			)
			select
			    q.StructuredParams_id as \"id\",
			    q.StructuredParams_pid as \"pid\",
			    q.StructuredParams_Name as \"name\",
			    q.StructuredParamsType_id as \"type\",
			    q.level as \"level\",
			    q.StructuredParams_Order as \"order\",
			    q.x
			from q
			order by q.x
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $subResult
		 */
		$result = $this->db->query($query, []);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		$returnResult = [];
		$resultCount = count($result);
		for ($i = 0; $i < $resultCount; $i++) {
			$resultItem = $result[$i];
			if ($resultItem["level"] === 1) {
				$newItem = [
					"id" => $resultItem["id"],
					"pid" => $resultItem["pid"],
					"name" => $resultItem["name"],
					"type" => $resultItem["type"],
					"level" => $resultItem["level"],
					"items" => $this->_recurciveTree($result, $resultItem["id"], $i),
				];
				$returnResult[] = $newItem;
			}

		}
		return ["data" => $returnResult];
	}

	/**
	 * @param $tree
	 * @param $parentId
	 * @param $order
	 * @return array
	 */
	private function _recurciveTree($tree, $parentId, $order)
	{
		$result = [];
		$treeCount = count($tree);
		for ($i = $order; $i < $treeCount; $i++) {
			$treeItem = $tree[$i];
			if (!in_array($treeItem["level"], [0, 1])) {
				if ($treeItem["pid"] == $parentId) {
					$newItem = [
						"id" => $treeItem["id"],
						"pid" => $treeItem["pid"],
						"name" => $treeItem["name"],
						"type" => $treeItem["type"],
						"level" => $treeItem["level"],
						"items" => $this->_recurciveTree($tree, $treeItem["id"], $i),
					];
					$result[] = $newItem;
				}
			}
		}
		return $result;
	}

	/**
	 * Отправка данных на сервер из Помощника
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function sendStructuredParamData($data)
	{
		$postData = json_decode($data["data"]);
		$dataList = [];
		$template = [];
		if (!empty($postData)) {
			$idxs = [];
			$replaceList = ["checkbox_", "textfield_", "radioButton"];
			foreach ($postData as $postDataItem) {
				$dataKey = str_replace($replaceList, "", $postDataItem->id);
				$idxs[] = $dataKey;
				$dataList[$dataKey] = $postDataItem->value;
			}
			$filterValue = implode(",", $idxs);
			$query = "
				select
					sp.StructuredParams_id,
					sp.StructuredParams_Name
				from v_StructuredParams sp
				where sp.StructuredParams_id in ({$filterValue})
			";
			/**@var CI_DB_result $result */
			$result = $this->db->query($query);
			if (is_object($result)) {
				$template = $result->result_array();
			}
		}
		$returnTemplate = [
			"success" => true,
			"template" => "<div>{xmltemplateinputblock_opros}</div>",
			"xmlData" => ["opros" => "1111"],
			"xmlDataSettings" => [
				"opros" => [
					"fieldLabel" => "<strong>Опрос</strong>",
					"name" => "complaint",
					"xtype" => "ckeditor"
				]
			],
			"originalXmlData" => ["opros" => "222"],
			"XmlDataSections" => [
				[
					"XmlDataSection_Code" => 1,
					"XmlDataSection_Name" => "Опрос",
					"XmlDataSection_SysNick" => "opros",
					"XmlDataSection_id" => "2"
				]
			]
		];
		$templateString = "";
		foreach ($template as $templateItem) {
			$replaceValue = "";
			foreach ($dataList as $dataListItemKey => $dataListItemValue) {
				if ($templateItem["StructuredParams_id"] == $dataListItemKey) {
					if ($dataListItemValue !== true) {
						$replaceValue = $dataListItemValue;
					}
				}
			}
			if ($replaceValue != "") {
				$replacedValue = str_replace("[--]", $replaceValue, $templateItem["StructuredParams_Name"]);
				$templateString .= "<span>{$replacedValue}</span>&nbsp;";
			} else {
				$templateString .= "<span>{$templateItem["StructuredParams_Name"]}</span>&nbsp;";
			}
		}
		$returnTemplate["template"] = "<div>{$templateString}</div>";
		return $returnTemplate;
	}

	/**
	 * Получение данных по одному параметру
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getStructuredParam($data)
	{
		/**@var CI_DB_result $res */
		$result = [[]];
		$params = ["StructuredParams_id" => (isset($data["StructuredParams_id"]) ? $data["StructuredParams_id"] : null)];
		if (isset($data["StructuredParams_id"]) && $data["StructuredParams_id"] > 0) {
			$query = "
				select
					sp.StructuredParams_id as \"StructuredParams_id\",
					sp.StructuredParams_SysNick as \"StructuredParams_SysNick\",
					sp.StructuredParams_Name as \"StructuredParams_Name\",
					sp.StructuredParamsType_id as \"StructuredParamsType_id\",
					spt.StructuredParamsType_Name as \"StructuredParamsType_Name\",
					sp.StructuredParamsPrintType_id as \"StructuredParamsPrintType_id\",
					sppt.StructuredParamsPrintType_Name as \"StructuredParamsPrintType_Name\",
					sp.MedSpecOms_Text as \"MedSpecOms_Text\",
					Sex_id as \"Sex_id\",
					sp.PersonAgeGroup_id as \"PersonAgeGroup_id\",
					sp.StructuredParams_pid as \"StructuredParams_pid\",
					sp.StructuredParams_rid as \"StructuredParams_rid\",
					sp.Region_id as \"Region_id\",
					sp.StructuredParams_Order as \"StructuredParams_Order\"
				from
					v_StructuredParams sp
					left join StructuredParamsType spt on sp.StructuredParamsType_id = spt.StructuredParamsType_id
					left join StructuredParamsPrintType sppt on sp.StructuredParamsPrintType_id = sppt.StructuredParamsPrintType_id
				where sp.StructuredParams_id = :StructuredParams_id
			";
			$result = $this->db->query($query, $params);
			if (is_object($result)) {
				$result = $result->result('array');
			}
		}
		if (isset($data["StructuredParams_pid"]) && $data["StructuredParams_pid"] == "root") {
			$query = "
				select 
					case when SPT.StructuredParamsxmlType_id > 0
						then 1
						else 0
					end as \"checked\", 
					XT.XmlType_Name as \"boxLabel\",
					XT.XmlType_id as \"value\"
				from
					v_XmlType XT
					left join StructuredParamsxmlType SPT on SPT.XmlType_id=XT.XmlType_id
						and SPT.StructuredParams_id = :StructuredParams_id
			";
			$res = $this->db->query($query, $params);
			$res = $res->result("array");
			foreach ($res as $key => $value) {
				if (isset($value['checked'])) {
					$res[$key]['checked'] = intval($value['checked']);
				}
			}
			$result[0]["XmlTypes"] = $res;
			$query = "
				select
					case when SPD.StructuredParamsxmlDataSection_id >0
						then 1
						else 0
					end as \"checked\",
					XD.xmlDataSection_Name as \"boxLabel\",
					XD.xmlDataSection_id as \"value\"
				from
					v_xmlDataSection XD
					left join StructuredParamsxmlDataSection SPD on SPD.xmlDataSection_id=XD.xmlDataSection_id
						and SPD.StructuredParams_id = :StructuredParams_id
			";
			$res = $this->db->query($query, $params);
			$res = $res->result("array");
			foreach ($res as $key => $value) {
				if (isset($value['checked'])) {
					$res[$key]['checked'] = intval($value['checked']);
				}
			}
			$result[0]["XmlDataSection"] = $res;
		}
		if (!is_array($result)) {
			return false;
		}
		return $result;
	}

	/**
	 * Сохранение одного параметра в БД
	 * @param $proc
	 * @param $params
	 * @return mixed|bool
	 * @throws Exception
	 */
	function SaveParam($proc, $params)
	{
		$sql = "
			select
				StructuredParams_id as \"StructuredParams_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from {$proc}(
			    structuredparams_id := :StructuredParams_id,
			    structuredparams_pid := :StructuredParams_pid,
			    structuredparams_name := :StructuredParams_Name,
			    structuredparamstype_id := :StructuredParamsType_id,
			    structuredparamsprinttype_id := :StructuredParamsPrintType_id,
			    sex_id := :Sex_id,
			    structuredparams_rid := :StructuredParams_rid,
			    region_id := :Region_id,
			    structuredparams_order := coalesce(
			        (select StructuredParams_Order from v_StructuredParams where StructuredParams_id = :StructuredParams_id),
			        (
						select coalesce(max(StructuredParams_Order), 0) + 1
						from v_StructuredParams
						where StructuredParams_pid = :StructuredParams_pid
						  or (StructuredParams_pid is null and :StructuredParams_pid is null)
			        )
			    ),
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $res */
		$res = $this->db->query($sql, $params);
		if (!is_object($res)) {
			return false;
		}
		$result = $res->result("array");
		if (isset($result[0]["Error_Msg"]) || !isset($result[0]["StructuredParams_id"])) {
			throw new Exception($result[0]["Error_Msg"]);
		}
		return $result[0];
	}

	/**
	 * Получение родительского параметра
	 * @param $data
	 * @return array
	 */
	function getParentParam($data)
	{
		$query = "
			select
				StructuredParams_id as \"StructuredParams_id\",
			    StructuredParams_pid as \"StructuredParams_pid\"
			from v_StructuredParams sp
			where StructuredParams_id = :StructuredParams_pid
		";
		$queryParams = ["StructuredParams_pid" => $data];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return [];
		}
		return $result->result("array");
	}

	/**
	 * Сохранение одного или нескольких параметров
	 * @param $data
	 * @return bool|mixed
	 * @throws Exception
	 */
	function saveStructuredParams($data)
	{
		if ($data["StructuredParams_pid"] == "root") {
			$data["StructuredParams_pid"] = null;
		}
		if ($data["StructuredParams_pid"] > 0) {
			$parent = [["StructuredParams_pid" => $data["StructuredParams_pid"]]];
			do {
				$parent = $this->getParentParam($parent[0]["StructuredParams_pid"]);
			} while (count($parent) > 0 && !empty($parent[0]["StructuredParams_pid"]));
			if (count($parent) > 0 && !empty($parent[0]["StructuredParams_id"])) {
				$data["StructuredParams_rid"] = $parent[0]["StructuredParams_id"];
			}
		}
		$proc = (!isset($data["StructuredParams_id"]) || $data["StructuredParams_id"] == 0) ? "p_StructuredParams_ins" : "p_StructuredParams_upd";
		$params = $data;
		if (isset($data["StructuredParams_id"]) && $data["StructuredParams_id"] > 0) {
			$params["StructuredParams_Order"] = null;
		} else {
			$params["PersonAgeGroup_id"] = null;
			$params["StructuredParams_Order"] = null;
			$params["StructuredParams_id"] = null;
		}
		$params["Region_id"] = $data["session"]["region"]["number"];
		$res = $this->SaveParam($proc, $params);
		if (isset($res["Error_Msg"])) {
			return $res;
		}
		$data["StructuredParams_id"] = $res["StructuredParams_id"];
		if ($data["StructuredParams_pid"] == null) {
			$this->saveXmlTypes($data);
			$this->saveXmlDataSections($data);
		}
		return $res;
	}

	/**
	 * @param $data
	 */
	function saveXmlTypes($data)
	{
		$XmlTypeArr = explode(",", $data["XmlTypes"]);
		$XmlTypeArrString = implode(", ", $XmlTypeArr);
		$queryParams = ["StructuredParams_id" => $data["StructuredParams_id"]];
		$query = "
			select StructuredParamsxmlType_id as \"StructuredParamsxmlType_id\"
			from StructuredParamsxmlType SPT
			where SPT.XmlType_id not in({$XmlTypeArrString})
			  and SPT.StructuredParams_id=:StructuredParams_id
		";
		$result = $this->queryResult($query, $queryParams);
		foreach ($result as $resultRow) {
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_structuredparamsxmltype_del(
				    structuredparamsxmltype_id := {$resultRow["StructuredParamsxmlType_id"]}
				);
			";
			$this->db->query($query);
		}
		foreach ($XmlTypeArr as $val) {
			$queryParams = [
				"StructuredParams_id" => $data["StructuredParams_id"],
				"pmUser_id" => $data["pmUser_id"],
				"XmlType_id" => $val
			];
			$query = "
				select StructuredParamsxmlType_id as \"StructuredParamsxmlType_id\"
				from StructuredParamsxmlType
				where xmlType_id = :XmlType_id
				  and StructuredParams_id=:StructuredParams_id
			";
			$result = $this->queryResult($query, $queryParams);
			if (@$result[0]["StructuredParamsxmlType_id"] == null) {
				$query = "
					select
						structuredparamsxmltype_id as \"StructuredParamsxmlType_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_structuredparamsxmltype_ins(
					    structuredparams_id := :StructuredParams_id,
					    xmltype_id := :XmlType_id,
					    pmuser_id := :pmUser_id
					);
				";
				$this->db->query($query, $queryParams);
			}
		}
	}

	/**
	 * @param $data
	 */
	function saveXmlDataSections($data)
	{
		$XmlDataSection = explode(",", $data["XmlDataSections"]);
		$XmlDataSectionString = implode(",", $XmlDataSection);
		$queryParams = ["StructuredParams_id" => $data["StructuredParams_id"]];
		$query = "
			select StructuredParamsxmlDataSection_id as \"StructuredParamsxmlDataSection_id\" 
			from StructuredParamsxmlDataSection SPT
			where SPT.xmlDataSection_id not in({$XmlDataSectionString})
			  and SPT.StructuredParams_id=:StructuredParams_id
		";
		$result = $this->queryResult($query, $queryParams);
		foreach ($result as $resultRow) {
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_structuredparamsxmldatasection_del(
					structuredparamsxmldatasection_id := {$resultRow["StructuredParamsxmlDataSection_id"]}
				);
			";
			$this->db->query($query, $queryParams);
		}
		foreach ($XmlDataSection as $val) {
			$queryParams = [
				"StructuredParams_id" => $data["StructuredParams_id"],
				"pmUser_id" => $data["pmUser_id"],
				"XmlDataSection_id" => $val
			];
			$query = "
				select
					StructuredParamsxmlDataSection_id as \"StructuredParamsxmlDataSection_id\"
				from StructuredParamsxmlDataSection
				where xmlDataSection_id = :XmlDataSection_id
				  and StructuredParams_id=:StructuredParams_id
			";
			$result = $this->queryResult($query, $queryParams);
			if (@$result[0]["StructuredParamsxmlDataSection_id"] == null) {
				$query = "
					select
					    structuredparamsxmldatasection_id as \"StructuredParamsxmlDataSection_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Msg\"
					from p_structuredparamsxmldatasection_ins(
					    structuredparamsxmldatasection_id := 0,
					    structuredparams_id := :StructuredParams_id,
					    xmldatasection_id := :XmlDataSection_id,
					    pmuser_id := :pmUser_id
					);
				";
				$this->db->query($query, $queryParams);
			}
		}
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function addStructuredParamsType($data)
	{
		$object = $data["object"];
		if ($object != "Age") {
			$query = "
				select
				    StructuredParams{$object}_id as \"StructuredParams{$object}_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_structuredparams{$object}_ins
				(
				    {$object}_id := :{$object}_id,
				    structuredparams_id := :StructuredParams_id,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"StructuredParams_id" => $data["StructuredParams_id"],
				"pmUser_id" => $data["pmUser_id"],
				"{$object}_id" => $data["{$object}_id"]
			];
			/**@var CI_DB_result $res */
			$res = $this->db->query($query, $queryParams);
			if (!is_object($res)) {
				return false;
			}
			$result = $res->result("array");
		} else {
			$query = "
				select
				    structuredparamsage_id as \"StructuredParamsAge_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_structuredparamsage_ins(
				    structuredparams_id := :StructuredParams_id,
				    structuredparamsage_from := :AgeFrom,
				    structuredparamsage_to := :AgeTo,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"StructuredParams_id" => $data["StructuredParams_id"],
				"pmUser_id" => $data["pmUser_id"],
				"AgeFrom" => $data["AgeFrom"],
				"AgeTo" => $data["AgeTo"],
			];
			/**@var CI_DB_result $res */
			$res = $this->db->query($query, $queryParams);
			if (!is_object($res)) {
				return false;
			}
			$result = $res->result("array");
		}
		return $result;
	}

	/**
	 * Сохранение одного или нескольких параметров при редактировании прямо в гриде
	 * @param $data
	 * @return bool
	 */
	function saveStructuredParamsInline($data)
	{
		return false;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function deleteStructuredParamsType($data)
	{
		$object = $data["object"];
		$sql = "
			select
				error_code as \"Error_Code\",
		    	error_message as \"Error_Msg\"
			from p_structuredparams{$object}_del(
			    structuredparams{$object}_id := :Main_id
			);
		";
		$params = ["Main_id" => $data["Main_id"]];
		$res = $this->db->query($sql, $params);
		if (is_object($res)) {
			$result = $res->result("array");
			if (isset($result[0]["Error_Msg"])) {
				return ["Error_Msg" => $result[0]["Error_Msg"]];
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Удаление структурированного параметра со всеми потомками, никого не жалко
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function deleteStructuredParam($data)
	{
		foreach ($data["records"] as $record) {
			$sql = "
				select p_structuredparams_delall(
					delete_id := :StructuredParams_id
				);
			";
			$params = ["StructuredParams_id" => $record];
			$res = $this->db->query($sql, $params);
			if (is_object($res)) {
				$result = $res->result("array");
				if (isset($result[0]["Error_Msg"])) {
					throw new Exception($result[0]["Error_Msg"]);
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Изменение порядка следования структурированного параметра
	 * @param $data
	 * @return array
	 */
	function moveStructuredParam($data)
	{
		$params = ["StructuredParams_id" => $data["StructuredParams_id"]];
		if (empty($data["pid"])) {
			// перемещения внутри одной ветви
			if ($data["position"] === "+1") {
				// смещение на 1 позицию вперед
				$sql = "
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order - 1
					where StructuredParams_Order = (select StructuredParams_Order from StructuredParams where StructuredParams_id = :StructuredParams_id) + 1
					  and (
							StructuredParams_pid = (select StructuredParams_pid from StructuredParams where StructuredParams_id = :StructuredParams_id)
							or StructuredParams_pid is null and (select StructuredParams_pid from StructuredParams where StructuredParams_id = :StructuredParams_id) is null
					      );
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order + 1
					where  StructuredParams_id = :StructuredParams_id;
				";
			} else if ($data["position"] === "-1") {
				// смещение на 1 позицию назад
				$sql = "
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order + 1
					where StructuredParams_Order = (select StructuredParams_Order from StructuredParams where StructuredParams_id = :StructuredParams_id) - 1
					  and (
							StructuredParams_pid = (select StructuredParams_pid from StructuredParams where StructuredParams_id = :StructuredParams_id)
							or StructuredParams_pid is null and (select StructuredParams_pid from StructuredParams where StructuredParams_id = :StructuredParams_id) is null
					      );
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order - 1
					where StructuredParams_id = :StructuredParams_id;
				";
			} else {
				// смещение на конкретную позицию
				if ($data["position"] < $data["position_old"]) {
					// если новая позиция меньше старой, то есть смещаем назад
					$sql = "
						update StructuredParams
						set StructuredParams_Order = StructuredParams_Order + 1
						where StructuredParams_Order between :StructuredParams_Order and :StructuredParams_Order_Old - 1
						  and (
								StructuredParams_pid = (select StructuredParams_pid from StructuredParams where StructuredParams_id = :StructuredParams_id)
								or StructuredParams_pid is null and (select StructuredParams_pid from StructuredParams where StructuredParams_id = :StructuredParams_id) is null
						      );
						update StructuredParams
						set StructuredParams_Order = :StructuredParams_Order
						where StructuredParams_id = :StructuredParams_id;
					";
				} else {
					// если новая позиция больше старой, то есть смещаем вперед
					$sql = "
						update StructuredParams
						set StructuredParams_Order = StructuredParams_Order - 1
						where StructuredParams_Order between :StructuredParams_Order_Old + 1 and :StructuredParams_Order
						  and (
								StructuredParams_pid = (select StructuredParams_pid from StructuredParams where StructuredParams_id = :StructuredParams_id)
								or StructuredParams_pid is null and (select StructuredParams_pid from StructuredParams where StructuredParams_id = :StructuredParams_id) is null
						      );
						update StructuredParams
						set StructuredParams_Order = :StructuredParams_Order
						where  StructuredParams_id = :StructuredParams_id;
					";
				}
				$params["StructuredParams_Order"] = $data["position"];
				$params["StructuredParams_Order_Old"] = $data["position_old"];
			}
		} else {
			// перемещение в другую ветвь
			if ($data["pid"] != "root") {
				// перемещаем не в корень
				$sql = "
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order - 1
					where StructuredParams_Order > :StructuredParams_Order_Old
					  and (
							StructuredParams_pid = (select StructuredParams_pid from StructuredParams where StructuredParams_id = :StructuredParams_id)
							or StructuredParams_pid is null and (select StructuredParams_pid from StructuredParams where StructuredParams_id = :StructuredParams_id) is null
					      );
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order + 1
					where StructuredParams_Order >= :index
					  and StructuredParams_pid = :parent;
					update StructuredParams
					set StructuredParams_Order = :index,
						StructuredParams_pid = :parent,
						StructuredParams_rid = (select StructuredParams_rid from v_StructuredParams where StructuredParams_id = :parent limit 1)
					where StructuredParams_id = :StructuredParams_id;
				";
				$params["index"] = $data["position"];
				$params["parent"] = $data["pid"];
				$params["StructuredParams_Order_Old"] = $data["position_old"];
			} else {
				// перемещаем в корень
				$sql = "
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order - 1
					where StructuredParams_Order > :StructuredParams_Order_Old
					  and (
							StructuredParams_pid = (select StructuredParams_pid from StructuredParams where StructuredParams_id = :StructuredParams_id)
							or StructuredParams_pid is null and (select StructuredParams_pid from StructuredParams where StructuredParams_id = :StructuredParams_id) is null
					      );
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order + 1
					where StructuredParams_Order >= :index
					  and StructuredParams_pid is null;
					update StructuredParams
					set StructuredParams_Order = :index,
						StructuredParams_pid = null,
						StructuredParams_rid = :StructuredParams_id
					where StructuredParams_id = :StructuredParams_id;
				";
				$params["index"] = $data["position"];
				$params["StructuredParams_Order_Old"] = $data["position_old"];
			}
		}
		$this->db->query($sql, $params);
		return ["Error_Msg" => ""];
	}

	/**
	 * Получение справочника симптомов в виде иерархической структуры
	 */
	function getStructuredParamsTree($data)
	{
		$this->load->model("MedPersonal_model", "mpmodel");
		$MedSpec = (isset($data["session"]) && isset($data["session"]["CurMedStaffFact_id"])) ? $this->mpmodel->getMedStaffFactMedSpecOmsInfo($data["session"]["CurMedStaffFact_id"]) : null;
		$filterList = [];
		$queryParams = [];
		$joins = "";
		//фильтр по типу документа
		if (!empty($data["EvnXml_id"])) {
			$joins .= "
				left join lateral (
	                select stp.StructuredParams_id 
					from
						v_EvnXml EX
						left join v_XmlType XT on XT.XmlType_id = EX.XmlType_id
						left join v_StructuredParamsXmlType SPXT on SPXT.XmlType_id = XT.XmlType_id
						left join v_StructuredParams stp on stp.StructuredParams_id = SPXT.StructuredParams_id
					where EX.EvnXml_id = :EvnXml_id
				) as xtSTP on true
			";
			$filterList[] = "
				(SP.StructuredParams_rid in (xtSTP.StructuredParams_id) or SP.StructuredParams_id in (xtSTP.StructuredParams_id))
			";
			$queryParams["EvnXml_id"] = $data["EvnXml_id"];
		}
		//фильтр по разделу документа
		if (!empty($data["branch"])) {
			$joins .= "
				left join lateral (
	                select stp.StructuredParams_id 
					from
						v_XmlDataSection XDS
						left join v_StructuredParamsXmlDataSection SPXDS on SPXDS.XmlDataSection_id = XDS.XmlDataSection_id
						left join v_StructuredParams stp on stp.StructuredParams_id = SPXDS.StructuredParams_id
					where XDS.XmlDataSection_SysNick = :branch_name
            	) as STP on true
            ";
			$filterList[] = "
				(SP.StructuredParams_rid in (STP.StructuredParams_id) or SP.StructuredParams_id in (STP.StructuredParams_id))
			";
			$queryParams["branch_name"] = $data["branch"];
		}
		//фильтр по специальности
		if ($MedSpec !== false && isset($MedSpec[0]) && isset($MedSpec[0]["MedSpecOms_Code"])) {
			$joins .= "
				left join lateral (
					select stp2.StructuredParams_id 
					from
						v_StructuredParams stp2
						left join v_StructuredParamsMedSpecOms SPMSO on SPMSO.StructuredParams_id = stp2.StructuredParams_id
						left join v_MedSpecOms MSO on MSO.MedSpecOms_id = SPMSO.MedSpecOms_id
					where SPMSO.StructuredParams_id is null or MSO.MedSpecOms_Code = :MedSpecOms_Code
            	) as msSTP on true
            ";
			$filterList[] = "
				(SP.StructuredParams_id in (msSTP.StructuredParams_id))
			";
			$queryParams["MedSpecOms_Code"] = $MedSpec[0]["MedSpecOms_Code"];
		}
		//фильтры по диагнозу и услуге
		if (!empty($data["Evn_id"]) && !empty($data["EvnClass_id"])) {
			switch ($data["EvnClass_id"]) {
				case "13":
					$evnType = "EvnVizitPLStom";
					break;
				case "32":
					$evnType = "EvnSection";
					break;
				default:
					$evnType = "EvnVizitPL";
					break;
			}

			$sql1 = "
				select
					evn.Diag_id as \"Diag_id\",
					evn.UslugaComplex_id as \"UslugaComplex_id\"
	            from v_{$evnType} evn
	            where evn.{$evnType}_id = :Evn_id
				limit 1
			";
			$sql1Params = ["Evn_id" => $data["Evn_id"]];
			/**@var CI_DB_result $result1 */
			$result1 = $this->db->query($sql1, $sql1Params);
			$result1 = $result1->result("array");
			if (is_array($result1) && count($result1) > 0) {
				if (isset($result1[0]["Diag_id"]) && ($result1[0]["Diag_id"] > 0)) {
					$joins .= "
						left join lateral (
							select stp2.StructuredParams_id 
							from
								v_StructuredParams stp2
								left join v_StructuredParamsDiag SPD on SPD.StructuredParams_id = stp2.StructuredParams_id
							where SPD.StructuredParams_id is null or SPD.Diag_id = :Diag_id
		                ) as dSTP on true
					";
					$filterList[] = "
						(SP.StructuredParams_id in (dSTP.StructuredParams_id))
					";
					$queryParams["Diag_id"] = $result1[0]["Diag_id"];
				}
				if (!empty($data["EvnXml_id"]) && isset($result1[0]["UslugaComplex_id"]) && ($result1[0]["UslugaComplex_id"] > 0)) {
					$joins .= "
						left join lateral (
							select stp2.StructuredParams_id 
							from
								v_StructuredParams stp2
								left join v_StructuredParamsUslugaComplex SPUC on SPUC.StructuredParams_id = stp2.StructuredParams_id
								left join lateral (
									select xt2.XmlType_id
									from
										v_EvnXml ex2
										left join v_XmlType xt2 on xt2.XmlType_id = ex2.XmlType_id 
									where ex2.EvnXml_id = :EvnXml_id
									limit 1
								) as xmltype on true
							where  SPUC.StructuredParams_id is null or SPUC.UslugaComplex_id = :UslugaComplex_id or xmltype.XmlType_id = 4
		            	) uSTP on true
		            ";
					$filterList[] = "
						(SP.StructuredParams_id in (uSTP.StructuredParams_id))
					";
					$queryParams["UslugaComplex_id"] = $result1[0]["UslugaComplex_id"];
					$queryParams["EvnXml_id"] = $data["EvnXml_id"];
				}
			}
		}
		//фильтр по возрасту
		if (!empty($data["Person_Birthdate"])) {
			$joins .= "
				left join lateral (
					select stp2.StructuredParams_id 
					from
						v_StructuredParams stp2
						left join v_StructuredParamsAge SPA on SPA.StructuredParams_id = stp2.StructuredParams_id
					where SPA.StructuredParams_id is null 
					   or ((SPA.StructuredParamsAge_From < :age and SPA.StructuredParamsAge_To > :age) or SPA.StructuredParamsAge_From = :age or SPA.StructuredParamsAge_To = :age)
            	) as aSTP on true 
			";
			$filterList[] = "
				(SP.StructuredParams_id in (aSTP.StructuredParams_id))
			";
			$queryParams["age"] = getCurrentAge($data["Person_Birthdate"]);
		}
		//фильтр по полу
		if (!empty($data["Person_id"])) {
			$joins .= "
				left join lateral (
					select pall.Sex_id
		            from v_Person_all pall
		            where pall.Person_id = :Person_id
		            limit 1 
	            ) as sex on true
			";
			$filterList[] = "(SP.Sex_id is null or SP.Sex_id = 3 or SP.Sex_id = sex.Sex_id)";
			$queryParams["Person_id"] = $data["Person_id"];
		}
		$whereString = (count($filterList) != 0) ? "where " . implode(" and ", $filterList) : "";
		$sql = "
			select
				SP.StructuredParams_id as id,
                SP.StructuredParams_pid as pid,
                SP.StructuredParams_Name as name,
                SP.StructuredParamsType_id as type,
				SP.StructuredParamsPrintType_id as print
            from
            	v_StructuredParams SP
            	{$joins}
			{$whereString}
			order by SP.StructuredParams_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		$result = $result->result("array");
		$params_arr = [];
		$params_arr[0] = [
			"id" => 99999999, // магическое число, означающее все группы параметров
			"name" => "Все параметры",
			"type" => 1,
			"pid" => null,
			"print" => 2
		];
		// массив для запоминания идентификаторов разделов
		$sections = [];
		foreach ($result as $row) {
			if (!isset($row["pid"])) $sections[] = $row["id"];
			$params_arr[$row["id"]] = [
				"id" => $row["id"],
				"name" => $row["name"],
				"type" => $row["type"],
				"pid" => isset($row["pid"]) ? $row["pid"] : 99999999,
				"print" => $row["print"],
			];
		}
		if (count($sections) == 1) {
			// если в выборке только один раздел, то удаляем верхний уровень
			unset($params_arr[0]);
			// и чистим pid этого раздела
			$params_arr[$sections[0]]["pid"] = null;
		}
		/**Генерация дерева симптомов*/
		function buildTree(array $elements, $path = "", $parentId = 0)
		{
			$branch = [];
			foreach ($elements as $element) {
				if ($element["pid"] == $parentId) {
					$children = ($path == "") ? buildTree($elements, $element["name"], $element["id"]) : buildTree($elements, $path . " >> " . $element["name"], $element["id"]);
					if ($children) {
						$element["children"] = $children;
					}
					$element["path"] = $path;
					$branch[] = $element;
				}
			}
			return $branch;
		}

		$tree = buildTree($params_arr);
		return $tree;
	}
}
