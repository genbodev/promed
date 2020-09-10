<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * StructuredParams - модель для работы со структурированными параметрами
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
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

class StructuredParams_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает данные для дерева структурных параметров
	 * @return bool
	 */
	function getStructuredParamsTreeBranch($data)
	{
		$params = array();
		$where = '';

		if ($data['node'] == 'root') {
			$where .= ' and sp.StructuredParams_pid is null';
		} else {
			$where .= ' and sp.StructuredParams_pid = :StructuredParams_pid';
			$params['StructuredParams_pid'] = $data['node'];
		}

		$query = "
			select
				sp.StructuredParams_id as id,
				sp.StructuredParams_Name as text,
				(case when sp.StructuredParamsType_id = 4 then 1 else 0 end) as leaf,
				sp.StructuredParams_pid as pid,
				sp.StructuredParams_rid as rid,
				sp.StructuredParams_Order as pos
			from
				v_StructuredParams sp with(nolock)
			where
				(1=1)
				{$where}
			order by
				sp.StructuredParams_Order
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function getStructuredParamsByType($data){
		$object = $data['object'];
		if($object !='Age'){
			$query = "
			select
				sp.StructuredParams".$object."_id as id,
				o.".$object."_Code as Code,
				o.".$object."_Name as Name
			from
				v_StructuredParams".$object." sp with(nolock)
			left join v_".$object." o with (nolock) on sp.".$object."_id = o.".$object."_id
			where
				sp.StructuredParams_id =:StructuredParams_id
		";
		}else{
			$query = "
			select
				sp.StructuredParamsAge_id as id,
				StructuredParamsAge_From as AgeFrom,
				StructuredParamsAge_To as AgeTo
			from
				v_StructuredParamsAge sp with(nolock)
			where
				sp.StructuredParams_id =:StructuredParams_id
		";
		}
		
		$result = $this->db->query($query, array('StructuredParams_id'=>$data['StructuredParams_id']));
		
		if (is_object($result)) {
			$res = $result->result('array');
			/*foreach($res as &$item){
				if($item['StructuredParams_id']>0){
					$this->getDiags(&$item);
					$this->getDocumentTypes(&$item);
				}
			}*/
			
			
			return array('data' => $res);
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список одного уровня структурированных параметров в виде грида
	 * @param $data
	 * @return array|bool
	 */
	function getStructuredParamsGridBranch($data)
	{
		$where = [];
		$params = [];
		if (isset($data["StructuredParams_pid"])) {
			$params["StructuredParams_pid"] = $data["StructuredParams_pid"];
			$where[] = "StructuredParams_pid = :StructuredParams_pid";
		} else {
			$where[] = "StructuredParams_pid is null";
		}
		if (!empty($data["StructuredParams_Name"])) {
			$where[] = "sp.StructuredParams_Name like '%'+:StructuredParams+'%'";
			$params["StructuredParams"] = $data["StructuredParams"];
		}
		$whereString = implode(" and ", $where);
		$query = "
			select
				sp.StructuredParams_id,
				sp.StructuredParams_SysNick,
				sp.StructuredParams_Name,
				sp.StructuredParamsType_id,
				StructuredParamsType_Name,
				sp.StructuredParamsPrintType_id,
				StructuredParamsPrintType_Name,
				(
				    select cast(M.MedSpecOms_Code as varchar(10)) + ', '
				    from
				    	StructuredParamsMedSpecOms SPM with(nolock)  
						inner join v_MedSpecOms M with(nolock) on M.MedSpecOms_id=SPM.MedSpecOms_id
					where sp.StructuredParams_id=SPM.StructuredParams_id for xml path('')
				) as MedSpecOms_Text,
				(
				    select D.Diag_code + ', '
				    from
				    	StructuredParamsDiag SPD with(nolock)  
						inner join v_Diag D with(nolock) on D.Diag_id=SPD.Diag_id
					where sp.StructuredParams_id=SPD.StructuredParams_id for xml path('')
				) as MedSpecOms_DiagText,
				(
				    select cast(X.XmlType_Code as varchar(10)) + ', '
				    from
				    	StructuredParamsxmlType SPX with(nolock)  
						inner join v_xmlType X with(nolock) on X.xmlType_id=SPX.xmlType_id
					where sp.StructuredParams_id=SPX.StructuredParams_id for xml path('')
				) as MedSpecOms_DocumentTypeText,
				sp.StructuredParams_Order
			from
				v_StructuredParams sp with(nolock)
				left join StructuredParamsType spt with (nolock) on sp.StructuredParamsType_id = spt.StructuredParamsType_id
				left join StructuredParamsPrintType sppt with (nolock) on sp.StructuredParamsPrintType_id = sppt.StructuredParamsPrintType_id
			where {$whereString}
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
			with q as (
			    select
			        v_StructuredParams.StructuredParams_id,
			        v_StructuredParams.StructuredParams_pid,
			        v_StructuredParams.StructuredParams_Name,
			        v_StructuredParams.StructuredParamsType_id,
			        0 as level,
			        v_StructuredParams.StructuredParams_Order,
			        row_number()over(partition by v_StructuredParams.StructuredParams_pid order by v_StructuredParams.StructuredParams_Order) / power(10.0, 0) as x
			    from v_StructuredParams with(nolock)
			    where v_StructuredParams.StructuredParams_id = {$data["StructuredParams_pid"]}
			    union all
			    select
			        p.StructuredParams_id,
			        p.StructuredParams_pid,
			        p.StructuredParams_Name,
			        p.StructuredParamsType_id,
			        q.level + 1 as level,
			        p.StructuredParams_Order,
			        x + row_number()over(partition by p.StructuredParams_pid order by p.StructuredParams_Order) / power(10.0, q.level + 1)
			    from v_StructuredParams p with(nolock)
			    join q on p.StructuredParams_pid = q.StructuredParams_id
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
		$idxs = [];
		$dataList = [];
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
			from v_StructuredParams sp with(nolock)
			where sp.StructuredParams_id in ({$filterValue})
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		$template = false;
		if (is_object($result)) {
			$template = $result->result_array();
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
	 */
	function getStructuredParam($data)
	{
		$result = array(array());
		$params = array(
				'StructuredParams_id' => (isset($data['StructuredParams_id'])?$data['StructuredParams_id']:NULL)
			);
		if(isset($data['StructuredParams_id'])&&$data['StructuredParams_id']>0){
			$query = "
				select
					sp.StructuredParams_id,
					sp.StructuredParams_SysNick,
					sp.StructuredParams_Name,
					sp.StructuredParamsType_id,
					spt.StructuredParamsType_Name,
					sp.StructuredParamsPrintType_id,
					sppt.StructuredParamsPrintType_Name,
					sp.MedSpecOms_Text,
					Sex_id,
					sp.PersonAgeGroup_id,
					sp.StructuredParams_pid,
					sp.StructuredParams_rid,
					sp.Region_id,
					sp.StructuredParams_Order
				from
					v_StructuredParams sp with(nolock)
				left join StructuredParamsType spt with (nolock) on sp.StructuredParamsType_id = spt.StructuredParamsType_id
				left join StructuredParamsPrintType sppt with (nolock) on sp.StructuredParamsPrintType_id = sppt.StructuredParamsPrintType_id
				where
					sp.StructuredParams_id = :StructuredParams_id
			";
			
			$result = $this->db->query($query, $params);
			if (is_object($result)) {
				$result = $result->result('array');
			}
		}
		if(isset($data['StructuredParams_pid'])&&$data['StructuredParams_pid']=='root'){
			$query="select 
						case when SPT.StructuredParamsxmlType_id >0 then 1 else 0 end as checked, 
						XT.XmlType_Name as boxLabel,
						XT.XmlType_id as value
					from v_XmlType XT with(nolock)
					left join StructuredParamsxmlType SPT with(nolock) on SPT.XmlType_id=XT.XmlType_id and SPT.StructuredParams_id = :StructuredParams_id
			";
			$res = $this->db->query($query, $params);
			$res = $res->result('array');
			$result[0]['XmlTypes'] = $res;

			$query="select 
						case when SPD.StructuredParamsxmlDataSection_id >0 then 1 else 0 end as checked, 
						XD.xmlDataSection_Name as boxLabel,
						XD.xmlDataSection_id as value
					from v_xmlDataSection XD with(nolock)
					left join StructuredParamsxmlDataSection SPD with(nolock) on SPD.xmlDataSection_id=XD.xmlDataSection_id and SPD.StructuredParams_id = :StructuredParams_id
			";
			$res = $this->db->query($query, $params);
			$res = $res->result('array');
			$result[0]['XmlDataSection'] = $res;
		}
		if (is_array($result)) {
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * Сохранение одного параметра в БД
	 */
	function SaveParam($proc, $params) {
		$sql = "
			declare
				@ErrCode bigint,
				@ErrMsg varchar(4000),
				@StructuredParams_id bigint = :StructuredParams_id,
				@StructuredParams_Order bigint = :StructuredParams_Order;
		
			-- Если порядковый номер передан пустым, определяем его по родителю
			if(@StructuredParams_id is not null)
			begin
				set @StructuredParams_Order = (select StructuredParams_Order from v_StructuredParams where StructuredParams_id = @StructuredParams_id  )
			end
			if (@StructuredParams_Order is null)
			begin
				set @StructuredParams_Order = (
					select
						isnull(max(StructuredParams_Order), 0) + 1
					from v_StructuredParams with(nolock)
					where StructuredParams_pid = :StructuredParams_pid 
						or (StructuredParams_pid is null and :StructuredParams_pid is null)
				)
			end
		
			exec {$proc}
				@StructuredParams_id = @StructuredParams_id output,
				@StructuredParams_pid = :StructuredParams_pid,
				@StructuredParams_Name = :StructuredParams_Name,
				@StructuredParamsType_id = :StructuredParamsType_id,
				@StructuredParamsPrintType_id = :StructuredParamsPrintType_id,
				@Sex_id = :Sex_id,
				@StructuredParams_rid = :StructuredParams_rid,
				@StructuredParams_Order = @StructuredParams_Order,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @StructuredParams_id as StructuredParams_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		
		$res = $this->db->query(
		//echo getDebugSQL(
			$sql,
			$params
		);
		
		if ( is_object($res) ) {
			$result = $res->result('array');
			
			if ( !isset($result[0]['Error_Msg']) && isset($result[0]['StructuredParams_id']) ) {
				return $result[0];
			} else {
				
				return array(
					'Error_Msg' => $result[0]['Error_Msg']
				);
			}
		}
	}

	/**
	 * Получение родительского параметра
	 */
	function getParentParam($data) {
		$query = "
			select StructuredParams_id, StructuredParams_pid
			from v_StructuredParams sp with (nolock)
			where StructuredParams_id = :StructuredParams_pid
		";
		$res = $this->db->query($query,array('StructuredParams_pid'=>$data));
		if(is_object($res)){
			return $res->result('array');
		} else {
			return array();
		}
	}
		
	/**
	 * Сохранение одного или нескольких параметров
	 */
	function saveStructuredParams($data) {
		
		if ( $data['StructuredParams_pid'] == 'root' ) {
			$data['StructuredParams_pid'] = null;
		}
		if($data['StructuredParams_pid'] > 0){
			$parent = array(array('StructuredParams_pid'=>$data['StructuredParams_pid']));
			do {
				$parent = $this->getParentParam($parent[0]['StructuredParams_pid']);
			} while (count($parent)>0 && !empty($parent[0]['StructuredParams_pid']));
			
			if(count($parent)>0 && !empty($parent[0]['StructuredParams_id'])){
				$data['StructuredParams_rid'] = $parent[0]['StructuredParams_id'];
			}
		}
		$proc = "p_StructuredParams_ins";	
		/*if (isset($data['records'])) {
			$proc = "p_StructuredParams_upd";
			
			foreach($data['records'] as $record) {
				$params = $this->getStructuredParam(array('StructuredParams_id' => $record));
				$params = $params[0];
				$params['pmUser_id'] = $data['pmUser_id'];
				foreach( $data as $in_param_name => $in_param_val ) {
					if ( array_key_exists($in_param_name, $params) ) {
						$params[$in_param_name] = $in_param_val;
					}
				}

				$this->SaveParam($proc, $params);
			}
		} else {*/
		$params = $data;
		if(isset($data['StructuredParams_id'])&&$data['StructuredParams_id']>0){
			$proc = "p_StructuredParams_upd";
			$params['StructuredParams_Order'] = null;
		}else{
			$params['PersonAgeGroup_id'] = null;
			$params['StructuredParams_Order'] = null;
			$params['StructuredParams_id']=null;
		}
		$params['Region_id'] = $data['session']['region']['number'];
		$res =  $this->SaveParam($proc, $params);
		if(!isset($res['Error_Msg'])){
			$data['StructuredParams_id'] = $res['StructuredParams_id'];
			if($data['StructuredParams_pid'] == null){
				$this->saveXmlTypes($data);
				$this->saveXmlDataSections($data);
			}
		}else{
			return $res;
		}
		//}
		return $res;
	}

	/**
	 *
	 * @param type $data 
	 */
	function saveXmlTypes($data){
		$XmlTypeArr = explode(',',$data['XmlTypes']);
		$query ="declare cur1 cursor read_only for
select StructuredParamsxmlType_id 
from StructuredParamsxmlType SPT with(nolock) 
where SPT.XmlType_id not in(".implode(',',$XmlTypeArr).") and SPT.StructuredParams_id=:StructuredParams_id

declare @StructuredParamsxmlType_id bigint
declare @Error_Code bigint
declare @Error_Message varchar(4000)
open cur1
fetch next from cur1 into @StructuredParamsxmlType_id
while @@FETCH_STATUS = 0
begin
	exec p_StructuredParamsxmlType_del
	@StructuredParamsxmlType_id=@StructuredParamsxmlType_id,
	@Error_Code = @Error_Code output,
	@Error_Message = @Error_Message output
	
	fetch next from cur1 into @StructuredParamsxmlType_id
end

close cur1
deallocate cur1
			";
		$res = $this->db->query($query,array('StructuredParams_id'=>$data['StructuredParams_id']));
		foreach($XmlTypeArr as $val){
			$query="declare @ins bigint = (select StructuredParamsxmlType_id from StructuredParamsxmlType with(nolock) where xmlType_id = :XmlType_id and StructuredParams_id=:StructuredParams_id)
				declare @Error_Code bigint
				declare @Error_Message varchar(4000)
				if(@ins is null)
				begin
				exec p_StructuredParamsxmlType_ins
				@StructuredParamsxmlType_id=null,
				@XmlType_id=:XmlType_id,
				@StructuredParams_id=:StructuredParams_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
				end";
			$res = $this->db->query($query,array(
				'StructuredParams_id'=>$data['StructuredParams_id'],
				'pmUser_id'=>$data['pmUser_id'],
				'XmlType_id'=>$val
				));
		}
		
	}
	/**
	 *
	 * @param type $data 
	 */
	function saveXmlDataSections($data){
		$XmlDataSection = explode(',',$data['XmlDataSections']);
		$query ="declare cur1 cursor read_only for
select StructuredParamsxmlDataSection_id 
from StructuredParamsxmlDataSection SPT with(nolock) 
where SPT.xmlDataSection_id not in(".implode(',',$XmlDataSection).") and SPT.StructuredParams_id=:StructuredParams_id

declare @StructuredParamsxmlDataSection_id bigint
declare @Error_Code bigint
declare @Error_Message varchar(4000)
open cur1
fetch next from cur1 into @StructuredParamsxmlDataSection_id
while @@FETCH_STATUS = 0
begin
	exec p_StructuredParamsxmlDataSection_del
	@StructuredParamsxmlDataSection_id=@StructuredParamsxmlDataSection_id,
	@Error_Code = @Error_Code output,
	@Error_Message = @Error_Message output
	
	fetch next from cur1 into @StructuredParamsxmlDataSection_id
end

close cur1
deallocate cur1
			";
		$res = $this->db->query($query,array('StructuredParams_id'=>$data['StructuredParams_id']));
		foreach($XmlDataSection as $val){
			$query="declare @ins bigint = (select StructuredParamsxmlDataSection_id from StructuredParamsxmlDataSection with(nolock) where xmlDataSection_id = :XmlDataSection_id and StructuredParams_id=:StructuredParams_id)
				declare @Error_Code bigint
				declare @Error_Message varchar(4000)
				if(@ins is null)
				begin
				exec p_StructuredParamsxmlDataSection_ins
				@StructuredParamsxmlDataSection_id=null,
				@XmlDataSection_id=:XmlDataSection_id,
				@StructuredParams_id=:StructuredParams_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
				end";
			$res = $this->db->query($query,array(
				'StructuredParams_id'=>$data['StructuredParams_id'],
				'pmUser_id'=>$data['pmUser_id'],
				'XmlDataSection_id'=>$val
				));
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function addStructuredParamsType($data){
		$object = $data['object'];
		$result = array();
		if($object !='Age'){
			$query="
				declare @Error_Code bigint
				declare @Error_Message varchar(4000)
				
				exec p_StructuredParams".$object."_ins
				@StructuredParams".$object."_id=null,
				@".$object."_id=:".$object."_id,
				@StructuredParams_id=:StructuredParams_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
			$res = $this->db->query($query,array(
				'StructuredParams_id'=>$data['StructuredParams_id'],
				'pmUser_id'=>$data['pmUser_id'],
				$object.'_id'=>$data[$object."_id"]
				));
			if ( is_object($res) ) {
				$result = $res->result('array');
			}else{
				return false;
			}
		}else{
			$query="
				declare @Error_Code bigint
				declare @Error_Message varchar(4000)
				
				exec p_StructuredParamsAge_ins
				@StructuredParamsAge_id=null,
				@StructuredParamsAge_From=:AgeFrom,
				@StructuredParamsAge_To=:AgeTo,
				@StructuredParams_id=:StructuredParams_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
			$res = $this->db->query($query,array(
				'StructuredParams_id'=>$data['StructuredParams_id'],
				'pmUser_id'=>$data['pmUser_id'],
				'AgeFrom'=>$data['AgeFrom'],
				'AgeTo'=>$data['AgeTo'],
			));
			if ( is_object($res) ) {
				$result = $res->result('array');
			}else{
				return false;
			}
		}
		return $result;
		
		
	}
	/**
	 * Сохранение одного или нескольких параметров при редактировании прямо в гриде
	 */
	function saveStructuredParamsInline($data) {
		return false;
		$proc = "p_StructuredParams_upd";
		
		foreach($data['records'] as $record) {
			$params = $this->getStructuredParam(array('StructuredParams_id' => $record['StructuredParams_id']));
			$params = $params[0];
			$params['pmUser_id'] = $data['pmUser_id'];
			foreach( $record as $in_param_name => $in_param_val ) {
				if ( array_key_exists($in_param_name, $params) /*&& (isset($in_param_val) || count($data['records']) == 1)*/ ) {
					$params[$in_param_name] = toAnsi($in_param_val);
				}
			}

			$this->SaveParam($proc, $params);
		}
		
		return array(
			'Error_Msg' => ''
		);
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function deleteStructuredParamsType($data) {
		
		$object = $data['object'];
		$sql = "
			declare
				@ErrCode bigint,
				@ErrMsg varchar(4000);

			exec p_StructuredParams".$object."_del
				@StructuredParams".$object."_id = :Main_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		$params = array(
			"Main_id" => $data["Main_id"]
		);
		$res = $this->db->query(
		//echo getDebugSQL(
			$sql,
			$params
		);

		if ( is_object($res) ) {
			$result = $res->result('array');
			if ( isset($result[0]['Error_Msg']) ) {
				return array(
					'Error_Msg' => $result[0]['Error_Msg']
				);
			}
		}
		
		
		return array(
			'Error_Msg' => ''
		);
	}
	
	/**
	 * Удаление структурированного параметра со всеми потомками, никого не жалко
	 */
	function deleteStructuredParam($data) {
		
		foreach($data['records'] as $record) {
			$sql = "
				declare
					@ErrCode bigint,
					@ErrMsg varchar(4000);
			
				exec p_StructuredParams_delAll
					@Delete_id = :StructuredParams_id
				select null as Error_Code, null as Error_Msg;
			";
			$params = array(
				'StructuredParams_id' => $record
			);
			$res = $this->db->query(
			//echo getDebugSQL(
				$sql,
				$params
			);
			
			if ( is_object($res) ) {
				$result = $res->result('array');
				if ( isset($result[0]['Error_Msg']) ) {
					return array(
						'Error_Msg' => $result[0]['Error_Msg']
					);
				}
			}
		}
		
		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Изменение порядка следования структурированного параметра
	 */
	function moveStructuredParam($data) {
		
		$params = array(
			'StructuredParams_id' => $data['StructuredParams_id']
		);
		
		if (empty($data['pid'])) { // перемещения внутри одной ветви
			if ($data['position'] === '+1') { // смещение на 1 позицию вперед
				$sql = "
					-- сдвигаем следующий за искомым элемент на 1 назад
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order - 1
					where 
						StructuredParams_Order = (select StructuredParams_Order from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id) + 1
						and (
							StructuredParams_pid = (select StructuredParams_pid from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id)
							or StructuredParams_pid is null and (select StructuredParams_pid from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id) is null
						)
					-- и сдвигаем искомый элемент на 1 вперед
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order + 1
					where 
						StructuredParams_id = :StructuredParams_id
				";
			} else if ($data['position'] === '-1') { // смещение на 1 позицию назад
				$sql = "
					-- сдвигаем стоящий перед искомым элемент на 1 вперед
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order + 1
					where 
						StructuredParams_Order = (select StructuredParams_Order from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id) - 1
						and (
							StructuredParams_pid = (select StructuredParams_pid from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id)
							or StructuredParams_pid is null and (select StructuredParams_pid from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id) is null
						)
					-- и сдвигаем искомый элемент на 1 назад
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order - 1
					where 
						StructuredParams_id = :StructuredParams_id
				";
			} else { // смещение на конкретную позицию
				if ($data['position'] < $data['position_old']) { // если новая позиция меньше старой, то есть смещаем назад
					$sql = "
						-- смещаем все элементы между новой и старой позицией на 1 позицию вперед
						update StructuredParams
						set StructuredParams_Order = StructuredParams_Order + 1
						where 
							StructuredParams_Order between :StructuredParams_Order and :StructuredParams_Order_Old - 1
							and (
								StructuredParams_pid = (select StructuredParams_pid from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id)
								or StructuredParams_pid is null and (select StructuredParams_pid from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id) is null
							)
						-- сдвигаем искомый элемент на заданную позицию
						update StructuredParams
						set StructuredParams_Order = :StructuredParams_Order
						where 
							StructuredParams_id = :StructuredParams_id
					";
				} else { // если новая позиция больше старой, то есть смещаем вперед
					$sql = "
						-- смещаем все элементы между старой и новой позицией на 1 позицию назад
						update StructuredParams
						set StructuredParams_Order = StructuredParams_Order - 1
						where 
							StructuredParams_Order between :StructuredParams_Order_Old + 1 and :StructuredParams_Order
							and (
								StructuredParams_pid = (select StructuredParams_pid from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id)
								or StructuredParams_pid is null and (select StructuredParams_pid from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id) is null
							)
						-- сдвигаем искомый элемент на заданную позицию
						update StructuredParams
						set StructuredParams_Order = :StructuredParams_Order
						where 
							StructuredParams_id = :StructuredParams_id
					";
				}
				$params['StructuredParams_Order'] = $data['position'];
				$params['StructuredParams_Order_Old'] = $data['position_old'];
			}
		} else { // перемещение в другую ветвь
			if ($data['pid'] != 'root') { // перемещаем не в корень
				$sql = "
					-- в старом разделе сдвигаем индексы у элементов стоящих после перемещаемого
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order - 1
					where 
						StructuredParams_Order > :StructuredParams_Order_Old
						and (
							StructuredParams_pid = (select StructuredParams_pid from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id)
							or StructuredParams_pid is null and (select StructuredParams_pid from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id) is null
						)
				
					-- в новом разделе сдвигаем все элементы начиная с переданного индекса, чтобы освободить позицию для нового элемента
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order + 1
					where 
						StructuredParams_Order >= :index
						and StructuredParams_pid = :parent
					
					-- Меняем индекс и родителя элемента
					update StructuredParams
					set StructuredParams_Order = :index,
						StructuredParams_pid = :parent,
						StructuredParams_rid = (select top 1 StructuredParams_rid from v_StructuredParams with(nolock) where StructuredParams_id = :parent)
					where 
						StructuredParams_id = :StructuredParams_id
				";
				$params['index'] = $data['position'];
				$params['parent'] = $data['pid'];
				$params['StructuredParams_Order_Old'] = $data['position_old'];
			} else { // перемещаем в корень
				$sql = "
					-- в старом разделе сдвигаем индексы у элементов стоящих после перемещаемого
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order - 1
					where 
						StructuredParams_Order > :StructuredParams_Order_Old
						and (
							StructuredParams_pid = (select StructuredParams_pid from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id)
							or StructuredParams_pid is null and (select StructuredParams_pid from StructuredParams with(nolock) where StructuredParams_id = :StructuredParams_id) is null
						)
				
					-- в новом разделе сдвигаем все элементы начиная с переданного индекса, чтобы освободить позицию для нового элемента
					update StructuredParams
					set StructuredParams_Order = StructuredParams_Order + 1
					where 
						StructuredParams_Order >= :index
						and StructuredParams_pid is null
					
					-- Меняем индекс и родителя элемента
					update StructuredParams
					set StructuredParams_Order = :index,
						StructuredParams_pid = null,
						StructuredParams_rid = :StructuredParams_id
					where 
						StructuredParams_id = :StructuredParams_id
				";
				$params['index'] = $data['position'];
				$params['StructuredParams_Order_Old'] = $data['position_old'];
			}
		}

		$res = $this->db->query(
		//echo getDebugSQL(
			$sql,
			$params
		);
		
		return array(
				'Error_Msg' => ''
			);
	}
	
	/**
     * Получение справочника симптомов в виде иерархической структуры
     */
	function getStructuredParamsTree($data) {
		$this->load->model('MedPersonal_model', 'mpmodel');
		if ( isset($data['session']) && isset($data['session']['CurMedStaffFact_id']) ) {
			$MedSpec = $this->mpmodel->getMedStaffFactMedSpecOmsInfo($data['session']['CurMedStaffFact_id']);
		} else {
			$MedSpec = false;
		}

		$filterList = array();
		$queryParams = array();
		$joins = "";

		/*if ( $MedSpec !== false && isset($MedSpec[0]) && isset($MedSpec[0]['MedSpecOms_Code']) ) {
			$filterList[] = "(MedSpecOms_Text like '".$MedSpec[0]['MedSpecOms_Code'].",%' or MedSpecOms_Text like '%,".$MedSpec[0]['MedSpecOms_Code'].",%' or MedSpecOms_Text like '%,".$MedSpec[0]['MedSpecOms_Code']."' or MedSpecOms_Text = 'все' or MedSpecOms_Text = '' or MedSpecOms_Text = '".$MedSpec[0]['MedSpecOms_Code']."')";
		}
		else {
			$filterList[] = "(MedSpecOms_Text = 'все' or MedSpecOms_Text = '')";
		}

		if ( !empty($data['branch']) ) {
			// либо такая ветка существует, либо ветки не существует, тогда показываются все данные
			$filterList[] = " (StructuredParams_rid in (select StructuredParams_id from v_StructuredParams with (nolock) where StructuredParams_sysnick = :branch_name) or not exists(select StructuredParams_id from v_StructuredParams with (nolock) where StructuredParams_sysnick = :branch_name) )";
			$queryParams['branch_name'] = $data['branch'];
		}
		
		if ( !empty($data['Person_Birthdate']) ) {
			if (getCurrentAge($data['Person_Birthdate']) <18 ) {
				$filterList[] = "isnull(PersonAgeGroup_id, 2) = 2";
			} else {
				$filterList[] = "isnull(PersonAgeGroup_id, 1) = 1";
			}
		}
		
		if ( !empty($data['EvnClass_id']) ) {
			$filterList[] = " StructuredParams_id in (select  StructuredParams_id from  v_StructuredParamsEvnClass with(nolock) where EvnClass_id = :EvnClass_id) ";
			$queryParams['EvnClass_id'] = $data['EvnClass_id'];
		}*/

		//фильтр по типу документа
		if ( !empty($data['EvnXml_id']) ) {
			$joins .= " outer apply(
            	select 
					stp.StructuredParams_id 
				from v_EvnXml EX with (nolock)
					left join v_XmlType XT with (nolock) on XT.XmlType_id = EX.XmlType_id
					left join v_StructuredParamsXmlType SPXT with (nolock) on SPXT.XmlType_id = XT.XmlType_id
					left join v_StructuredParams stp with (nolock) on stp.StructuredParams_id = SPXT.StructuredParams_id
				where EX.EvnXml_id = :EvnXml_id
            	) xtSTP ";
			$filterList[] = "(
			(SP.StructuredParams_rid in 
				(
            	xtSTP.StructuredParams_id
            	)
			) or (SP.StructuredParams_id in 
				(
            	xtSTP.StructuredParams_id
            	)
			)
			)";
			$queryParams['EvnXml_id'] = $data['EvnXml_id'];
		}

		//фильтр по разделу документа
		if ( !empty($data['branch']) ) {
			$joins .= " outer apply(
            	select 
					stp.StructuredParams_id 
				from v_XmlDataSection XDS with (nolock)
					left join v_StructuredParamsXmlDataSection SPXDS with (nolock) on SPXDS.XmlDataSection_id = XDS.XmlDataSection_id
					left join v_StructuredParams stp with (nolock) on stp.StructuredParams_id = SPXDS.StructuredParams_id
				where XDS.XmlDataSection_SysNick = :branch_name
            	) STP ";
			$filterList[] = "(
			(SP.StructuredParams_rid in 
				(
            	STP.StructuredParams_id
            	)
			) or (SP.StructuredParams_id in 
				(
            	STP.StructuredParams_id
            	)
			)
			)";
			$queryParams['branch_name'] = $data['branch'];
		}

		//фильтр по специальности
		if ( $MedSpec !== false && isset($MedSpec[0]) && isset($MedSpec[0]['MedSpecOms_Code']) ) {
			$joins .= " outer apply(
				select 
					stp2.StructuredParams_id 
				from v_StructuredParams stp2 with (nolock)
				left join v_StructuredParamsMedSpecOms SPMSO with (nolock) on SPMSO.StructuredParams_id = stp2.StructuredParams_id
				left join v_MedSpecOms MSO with (nolock) on MSO.MedSpecOms_id = SPMSO.MedSpecOms_id
				where SPMSO.StructuredParams_id is null or MSO.MedSpecOms_Code = :MedSpecOms_Code
            	) msSTP ";
			$filterList[] = "(
			 (SP.StructuredParams_id in 
				(
            	msSTP.StructuredParams_id
            	)
			)
			)";
			$queryParams['MedSpecOms_Code'] = $MedSpec[0]['MedSpecOms_Code'];
		}

		//фильтры по диагнозу и услуге
		if ( !empty($data['Evn_id']) && !empty($data['EvnClass_id'])) {
			switch ($data['EvnClass_id']) {
				case '11':
					$evnType = 'EvnVizitPL';
					break;
				case '13':
					$evnType = 'EvnVizitPLStom';
					break;
				case '32':
					$evnType = 'EvnSection';
					break;
				default:
					$evnType = 'EvnVizitPL';
					break;
			}
			$sql1 = "
				select top 1
					evn.Diag_id,
					evn.UslugaComplex_id
	            from v_{$evnType} evn with (nolock)
	            where evn.{$evnType}_id = :Evn_id
			";
			$result1 = $this->db->query($sql1, array('Evn_id' => $data['Evn_id']));
			$result1 = $result1->result('array');

			if(is_array($result1) && count($result1)>0){
				if(isset($result1[0]['Diag_id']) && ($result1[0]['Diag_id']>0)){
					$joins .= " outer apply(
						select 
							stp2.StructuredParams_id 
						from v_StructuredParams stp2 with (nolock)
						left join v_StructuredParamsDiag SPD with (nolock) on SPD.StructuredParams_id = stp2.StructuredParams_id
						where SPD.StructuredParams_id is null or SPD.Diag_id = :Diag_id
		            	) dSTP ";
					$filterList[] = "(
					 (SP.StructuredParams_id in 
						(
		            	dSTP.StructuredParams_id
		            	)
					)
					)";
					$queryParams['Diag_id'] = $result1[0]['Diag_id'];
				}

				if ( !empty($data['EvnXml_id']) && isset($result1[0]['UslugaComplex_id']) && ($result1[0]['UslugaComplex_id']>0) ) {
					$joins .= " outer apply(
						select 
							stp2.StructuredParams_id 
						from v_StructuredParams stp2 with (nolock)
						left join v_StructuredParamsUslugaComplex SPUC with (nolock) on SPUC.StructuredParams_id = stp2.StructuredParams_id
						outer apply (select top 1 xt2.XmlType_id from v_EvnXml ex2 with (nolock) left join v_XmlType xt2 with (nolock) on xt2.XmlType_id = ex2.XmlType_id 
							where ex2.EvnXml_id = :EvnXml_id) xmltype
						where  SPUC.StructuredParams_id is null or SPUC.UslugaComplex_id = :UslugaComplex_id or xmltype.XmlType_id = 4
		            	) uSTP ";
					$filterList[] = "(
					 (SP.StructuredParams_id in 
						(
		            	uSTP.StructuredParams_id
		            	)
					)
					)";
					$queryParams['UslugaComplex_id'] = $result1[0]['UslugaComplex_id'];
					$queryParams['EvnXml_id'] = $data['EvnXml_id'];
				}
			}
		}

		//фильтр по возрасту
		if ( !empty($data['Person_Birthdate']) ) {
			$joins .= " outer apply(
				select 
					stp2.StructuredParams_id 
				from v_StructuredParams stp2 with (nolock)
				left join v_StructuredParamsAge SPA with (nolock) on SPA.StructuredParams_id = stp2.StructuredParams_id
				where 
					SPA.StructuredParams_id is null 
					or (
							((SPA.StructuredParamsAge_From < :age) and (SPA.StructuredParamsAge_To > :age)) 
							or (SPA.StructuredParamsAge_From = :age) 
							or (SPA.StructuredParamsAge_To = :age)
						)	
            	) aSTP 
			";
			$filterList[] = "(
			(SP.StructuredParams_id in 
				(
            	aSTP.StructuredParams_id
            	)
			)
			)";
			$queryParams['age'] = getCurrentAge($data['Person_Birthdate']);
		}

		//фильтр по полу
		if ( !empty($data['Person_id']) ) {
			$joins .= " outer apply(
				select top 1
					pall.Sex_id
	            from v_Person_all pall with (nolock)
	            where pall.Person_id = :Person_id 
	        ) sex
			";
			$filterList[] = "(SP.Sex_id is null or SP.Sex_id = 3 or SP.Sex_id = sex.Sex_id)";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		
        $sql = "
			select
				SP.StructuredParams_id as id,
                SP.StructuredParams_pid as pid,
                SP.StructuredParams_Name as name,
                SP.StructuredParamsType_id as type,
				SP.StructuredParamsPrintType_id as [print]
            from v_StructuredParams SP with (nolock)
            {$joins}
			" . (count($filterList) > 0 ? "where " . implode(' and ', $filterList) : "") . "
			order by SP.StructuredParams_id
		";
		//echo getDebugSql($sql, $queryParams);die();
		$result = $this->db->query($sql, $queryParams);
		
		$result = $result->result('array');
        
        $params_arr = array();
		
		$params_arr[0] = array(
			'id' => 99999999, // магическое число, означающее все группы параметров
			'name' => 'Все параметры',
			'type' => 1,
			'pid' => null,
			'print' => 2
		);
        
		// массив для запоминания идентификаторов разделов
		$sections = array();
        foreach($result as $row) {
			if ( !isset($row['pid']) ) $sections[] = $row['id'];
            $params_arr[$row['id']] = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'type' => $row['type'],
                'pid' => isset($row['pid'])?$row['pid']:99999999,
				'print' => $row['print'],
            );
        }
		
		if (count($sections) == 1) {
			// если в выборке только один раздел, то удаляем верхний уровень
			unset($params_arr[0]);
			// и чистим pid этого раздела
			$params_arr[$sections[0]]['pid'] = null;
		}

        /**
         * Генерация дерева симптомов
         */
        function buildTree(array $elements, $path = '', $parentId = 0) {
            $branch = array();

            foreach ($elements as $element) {
                if ($element['pid'] == $parentId) {
					if ($path == '') {
						$children = buildTree($elements, $element['name'], $element['id']);
					} else {
						$children = buildTree($elements, $path . ' >> ' . $element['name'], $element['id']);
					}
                    if ($children) {
                        $element['children'] = $children;
                    }
					$element['path'] = $path;
					
                    $branch[] = $element;
                }
            }

            return $branch;
        }
        $tree = buildTree($params_arr);
        return $tree;
    }
}