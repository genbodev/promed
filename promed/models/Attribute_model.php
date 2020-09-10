<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Attribute_model - модель для работы с атрибутами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.07.2014
 */

class Attribute_model extends swModel {
	private $_attributeSignCache = array();

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление атрибута
	 */
	function deleteAttribute($data) {

		$isAttributeInUse = $this->getFirstResultFromQuery("select top 1 Attribute_id from dbo.AttributeValue with(nolock) where Attribute_id = :Attribute_id", $data);

		if (!empty($isAttributeInUse)) {
			return array(array('success' => false, 'Error_Msg' => 'В БД есть хотя бы одна запись со ссылкой на атрибут. Удаление невозможно.'));
		}

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_Attribute_del
				@Attribute_id = :Attribute_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Удаление атрибута
	 */
	function deleteAttributeVision($data) {
		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_AttributeVision_del
				@AttributeVision_id = :AttributeVision_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		//echo getDebugSQL($query, $data); die;
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if (!empty($response[0]['Error_Msg'])) {
				return Array(0 => Array('success' => false, 'Error_Msg' => 'Ошибка при удалении области видимости атрибута. Возможно атрибут используется в значении тарифа или объема.'));
			} else {
				return $response;
			}
		}

		return false;
	}

	/**
	 * Удаление значения атрибута с признаком
	 */
	function deleteAttributeSignValue($data) {
		$this->beginTransaction();

		$params = array('AttributeSignValue_id' => $data['AttributeSignValue_id']);

		$query = "
			select AttributeValue_id
			from v_AttributeValue with(nolock)
			where AttributeSignValue_id = :AttributeSignValue_id
		";
		$AttributeValues = $this->queryResult($query, $params);
		if (!is_array($AttributeValues)) {
			$this->rollbackTransaction();
			return $this->createError('', 'Ошибка при запросе списка значения атрибутов по признаку');
		}
		foreach($AttributeValues as $AttributeValue) {
			$resp = $this->deleteAttributeValue($AttributeValue);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);
			exec p_AttributeSignValue_del
				@AttributeSignValue_id = :AttributeSignValue_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;
			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		$response = $this->queryResult($query, $params);

		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}

		$this->commitTransaction();
		return $response;
	}

	/**
	 * Удаление значения атрибута
	 */
	function deleteAttributeValue($data) {
		$params = array('AttributeValue_id' => $data['AttributeValue_id']);

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);
			exec p_AttributeValue_del
				@AttributeValue_id = :AttributeValue_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;
			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Возвращает список атрибутов
	 */
	function loadAttributeGrid($data) {
		$params = array();
		$fitler = "(1=1)";

		if (!empty($data['Attribute_Name'])) {
			$fitler .= " and A.Attribute_Name like :Attribute_Name+'%'";
			$params['Attribute_Name'] = $data['Attribute_Name'];
		}
		if (!empty($data['Attribute_SysNick'])) {
			$fitler .= " and A.Attribute_SysNick like :Attribute_SysNick+'%'";
			$params['Attribute_SysNick'] = $data['Attribute_SysNick'];
		}
		if (!empty($data['AttributeValueType_id'])) {
			$fitler .= " and A.AttributeValueType_id = :AttributeValueType_id";
			$params['AttributeValueType_id'] = $data['AttributeValueType_id'];
		}

		if (!empty($data['Attribute_begDate_From'])) {
			$fitler .= " and A.Attribute_begDate >= :Attribute_begDate_From";
			$params['Attribute_begDate_From'] = $data['Attribute_begDate_From'];
		}

		if (!empty($data['Attribute_begDate_To'])) {
			$fitler .= " and A.Attribute_begDate <= :Attribute_begDate_To";
			$params['Attribute_begDate_To'] = $data['Attribute_begDate_To'];
		}

		if (!empty($data['Attribute_endDate_From'])) {
			$fitler .= " and A.Attribute_endDate >= :Attribute_endDate_From";
			$params['Attribute_endDate_From'] = $data['Attribute_endDate_From'];
		}

		if (!empty($data['Attribute_endDate_To'])) {
			$fitler .= " and A.Attribute_endDate <= :Attribute_endDate_To";
			$params['Attribute_endDate_To'] = $data['Attribute_endDate_To'];
		}

		if (!empty($data['Attribute_Code'])) {
			$fitler .= " and Attribute_Code like '%'+:Attribute_Code+'%'";
			$params['Attribute_Code'] = $data['Attribute_Code'];
		}

		if (!empty($data['Attribute_isKeyValue']) && $data['Attribute_isKeyValue'] == 'true') {
			$fitler .= " and exists(select top 1 AttributeVision_id from v_AttributeVision AV with(nolock) where AV.Attribute_id = A.Attribute_id and AV.AttributeVision_IsKeyValue = 2)";
		}

		if (!empty($data['isClose'])) {
			if ($data['isClose'] == 2) {
				$fitler .= " and A.Attribute_endDate <= @curdate";
			} else {
				$fitler .= " and ISNULL(A.Attribute_endDate, @curdate) >= @curdate";
			}
		}

		$query = "
			-- variables
			declare @curdate datetime = dbo.tzGetDate();
			-- end variables

			select
				-- select
				A.Attribute_id,
				A.Attribute_Code,
				A.Attribute_Name,
				A.Attribute_SysNick,
				convert(varchar(10), A.Attribute_begDate, 104) as Attribute_begDate,
				convert(varchar(10), A.Attribute_endDate, 104) as Attribute_endDate,
				A.Attribute_TableName,
				AVT.AttributeValueType_id,
				AVT.AttributeValueType_SysNick,
				AVT.AttributeValueType_Name
				-- end select
			from
				-- from
				v_Attribute A with(nolock)
				left join v_AttributeValueType AVT with(nolock) on AVT.AttributeValueType_id = A.AttributeValueType_id
				-- end from
			where
				-- where
				{$fitler}
				-- end where
			order by
				-- order by
				A.Attribute_Code
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Возвращает список атрибутов
	 */
	function loadAttributeVisionGrid($data) {
		$params = array();
		$fitler = "(1=1)";

		if (!empty($data['Attribute_Name'])) {
			$fitler .= " and A.Attribute_Name like :Attribute_Name+'%'";
			$params['Attribute_Name'] = $data['Attribute_Name'];
		}
		if (!empty($data['AttributeVision_TableName'])) {
			$fitler .= " and AV.AttributeVision_TableName = :AttributeVision_TableName"; // like :AttributeVision_TableName+'%'
			$params['AttributeVision_TableName'] = $data['AttributeVision_TableName'];
		}
		if (!empty($data['AttributeVision_TablePKey'])) {
			$fitler .= " and AV.AttributeVision_TablePKey = :AttributeVision_TablePKey";
			$params['AttributeVision_TablePKey'] = $data['AttributeVision_TablePKey'];
		}
		if (!empty($data['Region_id'])) {
			$fitler .= " and AV.Region_id = :Region_id";
			$params['Region_id'] = $data['Region_id'];
		}
		if (!empty($data['Org_id'])) {
			$fitler .= " and AV.Org_id = :Org_id";
			$params['Org_id'] = $data['Org_id'];
		}

		$query = "
			select
				-- select
				AV.AttributeVision_id,
				AV.AttributeVision_TableName,
				AV.AttributeVision_Sort,
				AV.AttributeVision_isKeyValue,
				A.Attribute_id,
				A.Attribute_Name,
				O.Org_id,
				O.Org_Name,
				AV.Region_id,
				case
					when AV.Region_id = 59 then 'Пермь'
					when AV.Region_id = 2 then 'Уфа'
					when AV.Region_id = 10 then 'Карелия'
					when AV.Region_id = 19 then 'Хакасия'
					when AV.Region_id = 30 then 'Астрахань'
					when AV.Region_id = 60 then 'Псков'
					when AV.Region_id = 63 then 'Самара'
					when AV.Region_id = 64 then 'Саратов'
					when AV.Region_id = 77 then 'Москва'
					when AV.Region_id = 101 then 'Казахстан'
					when AV.Region_id = 201 then 'Беларусь'
					when AV.Region_id = 66 then 'Екатеринбург'
					else ''
				end as Region_Name
				-- end select
			from
				-- from
				v_AttributeVision AV with(nolock)
				inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
				left join v_Org O with(nolock) on O.Org_id = AV.Org_id
				-- end from
			where
				-- where
				{$fitler}
				-- end where
			order by
				-- order by
				AV.AttributeVision_Sort
				-- end order by
		";

		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение списка значений атрибутов
	 */
	function loadAttributeSignValueGrid($data) {
		$params = array(
			'AttributeSign_TableName' => $data['AttributeSign_TableName'],
			'AttributeSignValue_TablePKey' => $data['AttributeSignValue_TablePKey'],
		);

		$filter = "";
		if (getRegionNick() == 'msk' && !isSuperAdmin()) {
			$filter .= " and [AS].AttributeSign_Code not in (23, 24, 25, 26, 27, 28, 29)";
		}

		$query = "
			select
				ASV.AttributeSignValue_id,
				ASV.AttributeSignValue_TablePKey,
				convert(varchar(10), ASV.AttributeSignValue_begDate, 104) as AttributeSignValue_begDate,
				convert(varchar(10), ASV.AttributeSignValue_endDate, 104) as AttributeSignValue_endDate,
				[AS].AttributeSign_id,
				[AS].AttributeSign_Code,
				[AS].AttributeSign_Name,
				[AS].AttributeSign_TableName,
				1 as RecordStatus_Code
			from
				v_AttributeSignValue ASV with(nolock)
				inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
			where
				[AS].AttributeSign_TableName like :AttributeSign_TableName
				and ASV.AttributeSignValue_TablePKey like :AttributeSignValue_TablePKey
				{$filter}
			order by
				[AS].AttributeSign_Code,
				ASV.AttributeSignValue_begDate
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return false;
		}

		if ($data['formMode'] == 'local') {
			$ids = array();
			foreach($response as $item) {
				$ids[] = $item['AttributeSignValue_id'];
			}
			$resp = $this->loadAttributeValueDataBySign(array('AttributeSignValue_ids' => $ids));
			$AttributeValueLoadParams = array();
			foreach($resp as $item) {
				$key = $item['AttributeSignValue_id'];
				$AttributeValueLoadParams[$key][] = $item;
			}
			foreach($response as &$item) {
				$key = $item['AttributeSignValue_id'];
				if (isset($AttributeValueLoadParams[$key])) {
					$item['AttributeValueLoadParams'] = json_encode($AttributeValueLoadParams[$key]);
				}
			}
		}

		return array('data' => $response);
	}

	/**
	 * Получение данных для редатирования значения атрибута по признаку
	 */
	function loadAttributeSignValueForm($data) {
		$params = array('AttributeSignValue_id' => $data['AttributeSignValue_id']);

		$query = "
			select top 1
				ASV.AttributeSignValue_id,
				ASV.AttributeSignValue_TablePKey,
				ASV.AttributeSign_id,
				convert(varchar(10), ASV.AttributeSignValue_begDate, 104) as AttributeSignValue_begDate,
				convert(varchar(10), ASV.AttributeSignValue_endDate, 104) as AttributeSignValue_endDate
			from
				v_AttributeSignValue ASV with(nolock)
			where
				ASV.AttributeSignValue_id = :AttributeSignValue_id
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return false;
		}

		$AttributeValueData = $this->loadAttributeValueDataBySign(array(
			'AttributeSignValue_id' => $response[0]['AttributeSignValue_id']
		));
		if (!is_array($AttributeValueData)) {
			return false;
		}
		$response[0]['AttributeValueData'] = json_encode($AttributeValueData);

		return $response;
	}

	/**
	 * Получение значений для атрибутов по признакам
	 */
	function loadAttributeValueDataBySign($data) {
		$AttributeSignValue_ids = array();

		if (!empty($data['AttributeSignValue_ids'])) {
			$AttributeSignValue_ids = $data['AttributeSignValue_ids'];
		}
		if (!empty($data['AttributeSignValue_id'])) {
			$AttributeSignValue_ids[] = $data['AttributeSignValue_id'];
		}
		if (count($AttributeSignValue_ids) == 0) {
			return array();
		}

		$query = "
			select
				AV.AttributeSignValue_id,
				AV.AttributeValue_id,
				A.Attribute_SysNick,
				1 as RecordStatus_Code,
				coalesce(
					cast(AV.AttributeValue_ValueIdent as varchar),
					cast(AV.AttributeValue_ValueInt as varchar),
					cast(AV.AttributeValue_ValueFloat as varchar),
					AV.AttributeValue_ValueString,
					cast(AV.AttributeValue_ValueBoolean as varchar),
					convert(varchar(10), AV.AttributeValue_ValueDate, 104)
				) as AttributeValue_Value,
				AttributeValue_ValueText
			from
				v_AttributeValue AV with(nolock)
				inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
			where AV.AttributeSignValue_id in (".implode(',', $AttributeSignValue_ids).")
		";

		return $this->queryResult($query);
	}

	/**
	 * Возвращает список атрибутов
	 */
	function loadAttributeList($data) {
		$params = array();
		$where = '(1=1)';

		if (!empty($data['Attribute_id'])) {
			$where .= " and Attribute_id = :Attribute_id";
			$params['Attribute_id'] = $data['Attribute_id'];
		}
		if (!empty($data['AttributeSign_id'])) {
			$where .= " and exists(
				select top 1 AttributeVision_id
				from v_AttributeVision with(nolock)
				where Attribute_id = A.Attribute_id and AttributeSign_id = :AttributeSign_id
			)";
			$params['AttributeSign_id'] = $data['AttributeSign_id'];
		}
		if (!empty($data['query'])) {
			$where .= " and Attribute_Name like :Attribute_Name+'%'";
			$params['Attribute_Name'] = $data['query'];
		}

		$query = "
			select
				A.Attribute_id,
				A.Attribute_Code,
				A.Attribute_Name,
				A.Attribute_SysNick,
				A.AttributeValueType_id,
				convert(varchar(10), A.Attribute_begDate, 104) as Attribute_begDate,
				convert(varchar(10), A.Attribute_endDate, 104) as Attribute_endDate,
				A.Attribute_TableName,
				A.Attribute_TablePKey
			from
				v_Attribute A with(nolock)
			where
				{$where}
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Возвращает список признаков атрибутов
	 */
	function loadAttributeSignList($data) {
		$params = array();
		$where = '(1=1)';

		if (!empty($data['AttributeSign_id'])) {
			$where .= " and AttributeSign_id = :AttributeSign_id";
			$params['AttributeSign_id'] = $data['AttributeSign_id'];
		}
		if (!empty($data['query'])) {
			$where .= " and AttributeSign_Name like :AttributeSign_Name+'%'";
			$params['AttributeSign_Name'] = $data['query'];
		}
		if (!empty($data['AttributeSign_TableName'])) {
			$where .= " and AttributeSign_TableName like :AttributeSign_TableName";
			$params['AttributeSign_TableName'] = $data['AttributeSign_TableName'];
		}

		$disallowed = array();
		if (getRegionNick() != 'perm') {
			$disallowed[] = 2;
		}
		if (!in_array(getRegionNick(), [ 'astra', 'kareliya', 'khak', 'krym', 'penza', 'perm', 'ufa', 'msk' ])) {
			$disallowed[] = 13;
		}
		$allowStacAttributes = false;
		if (!empty($data['AttributeSign_TableName']) && $data['AttributeSign_TableName'] == 'dbo.LpuSection' && !empty($data['AttributeSignValue_TablePKey'])) {
			$resp_check = $this->queryResult("select LpuUnitType_id from v_LpuSection ls (nolock) inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id where ls.LpuSection_id = :LpuSection_id", [
				'LpuSection_id' => $data['AttributeSignValue_TablePKey']
			]);
			if (!empty($resp_check[0]['LpuUnitType_id']) && in_array($resp_check[0]['LpuUnitType_id'], [1, 6, 7, 9])) {
				$allowStacAttributes = true;
			}
		}
		if (!$allowStacAttributes) {
			$disallowed[] = 19;
			$disallowed[] = 20;
		}
		if (getRegionNick() == 'msk' && !isSuperAdmin()) {
			$disallowed[] = 23;
			$disallowed[] = 24;
			$disallowed[] = 25;
			$disallowed[] = 26;
			$disallowed[] = 27;
			$disallowed[] = 28;
			$disallowed[] = 29;
		}
		if (getRegionNick() != 'kz') {
			$disallowed[] = 3;
		}
		if (count($disallowed) > 0) {
			$where .= " and ASign.AttributeSign_Code not in (".implode(",", $disallowed).")";
		}

		if (!empty($data['UslugaComplex_Code'])) {
			switch($data['UslugaComplex_Code']) {
				case 'A05.10.002':
				case 'A05.10.006':
					$where .= " and ASign.AttributeSign_Code in (4)";
					break;
				case 'A11.20.017.001':
					$where .= " and ASign.AttributeSign_Code in (7,8,9)";
					break;
				case 'A11.20.017.002':
					$where .= " and ASign.AttributeSign_Code in (7,8,9,10,11)";
					break;
				case 'A11.20.017.003':
					$where .= " and ASign.AttributeSign_Code in (7,8,9,11)";
					break;
				case 'A11.20.030.001':
					$where .= " and ASign.AttributeSign_Code in (10)";
					break;
				case 'A11.20.017':
					$where .= " and ASign.AttributeSign_Code in (7,8,9,10,11)";
					break;
				case 'A04.20.001':
					$where .= " and ASign.AttributeSign_Code in (12)";
					break;
				case 'A04.20.001.001':
					$where .= " and ASign.AttributeSign_Code in (12)";
					break;
				case 'A06.10.006':
				case 'A06.10.006.002':
					$where .= " and ASign.AttributeSign_Code in (15, 16)";
					break;
				case 'A26.05.066.002':
				case 'A26.08.027.001':
				case 'A26.08.027.001.1':
				case 'A26.08.027.001.2':
				case 'A26.08.046.002':
				case 'A26.09.044.002':
				case 'A26.09.060.002':
				case 'A26.08.027.004':
				case 'A26.08.027.004.001':
				case 'A26.08.027.004.002':
				case 'A26.08.027.005':
				case 'A26.08.027.006':
					$where .= " and ASign.AttributeSign_Code in (31)";
					break;
			}
		}

		$query = "
			select
				ASign.AttributeSign_id,
				ASign.AttributeSign_Code,
				ASign.AttributeSign_Name,
				ASign.AttributeSign_TableName
			from
				v_AttributeSign ASign with(nolock)
			where
				{$where}
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Возвращает данные атрибута для редактирования
	 */
	function loadAttributeForm($data) {
		$params = array('Attribute_id' => $data['Attribute_id']);

		$query = "
			select
				A.Attribute_id,
				A.Attribute_Code,
				A.Attribute_Name,
				A.Attribute_SysNick,
				A.AttributeValueType_id,
				convert(varchar(10), A.Attribute_begDate, 104) as Attribute_begDate,
				convert(varchar(10), A.Attribute_endDate, 104) as Attribute_endDate,
				A.Attribute_TableName,
				A.Attribute_TablePKey,
				A.Attribute_pid
			from
				v_Attribute A with(nolock)
			where
				A.Attribute_id = :Attribute_id
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Возвращает данные области видимости атрибута для редактирования
	 */
	function loadAttributeVisionForm($data) {
		$params = array('AttributeVision_id' => $data['AttributeVision_id']);

		$query = "
			select
				AV.AttributeVision_id,
				AV.Attribute_id,
				AV.AttributeVision_TableName,
				AV.AttributeVision_TablePKey,
				case when AV.AttributeVision_IsKeyValue = 2 then 1 else 0 end as AttributeVision_IsKeyValue,
				AV.AttributeVision_Sort,
				AV.Region_id,
				AV.Org_id,
				AV.AttributeVision_AppCode,
				convert(varchar(10), AV.AttributeVision_begDate, 104) as AttributeVision_begDate,
				convert(varchar(10), AV.AttributeVision_endDate, 104) as AttributeVision_endDate,
				AV.AttributeSign_id
			from
				v_AttributeVision AV with(nolock)
			where
				AV.AttributeVision_id = :AttributeVision_id
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Сохранение атрибута
	 */
	function saveAttribute($data) {
		$params = array(
			'Attribute_id' => empty($data['Attribute_id']) ? null : $data['Attribute_id'],
			'Attribute_Code' => $data['Attribute_Code'],
			'Attribute_Name' => $data['Attribute_Name'],
			'Attribute_SysNick' => $data['Attribute_SysNick'],
			'AttributeValueType_id' => $data['AttributeValueType_id'],
			'Attribute_begDate' => $data['Attribute_begDate'],
			'Attribute_endDate' => empty($data['Attribute_endDate']) ? null : $data['Attribute_endDate'],
			'Attribute_TableName' => empty($data['Attribute_TableName']) ? null : $data['Attribute_TableName'],
			'Attribute_TablePKey' => empty($data['Attribute_TablePKey']) ? null : $data['Attribute_TablePKey'],
			'pmUser_id' => $data['pmUser_id']
		);

		// Проверка дубликатов кода атрибутов
		$response = $this->checkAttributeDoubles($params);
		if ( !is_array($response) ) {
			throw new Exception('Ошибка при проверке дублей атрибутов по коду');
		}
		if ( count($response) > 0 ) {
			throw new Exception('Указанный код атрибута в данном периоде уже используется');
		}

		$procedure = 'p_Attribute_ins';
		if (!empty($params['Attribute_id'])) {
			$procedure = 'p_Attribute_upd';
		}

		$query = "
			declare
				@Attribute_id bigint = :Attribute_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@Attribute_id = @Attribute_id output,
				@Attribute_Code = :Attribute_Code,
				@Attribute_Name = :Attribute_Name,
				@Attribute_SysNick = :Attribute_SysNick,
				@AttributeValueType_id = :AttributeValueType_id,
				@Attribute_begDate = :Attribute_begDate,
				@Attribute_endDate = :Attribute_endDate,
				@Attribute_TableName = :Attribute_TableName,
				@Attribute_TablePKey = :Attribute_TablePKey,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Attribute_id as Attribute_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка атрибутов на дубли
	 */
	function checkAttributeDoubles($data) {

		$query = "
			select top 1 Attribute_id
			from v_Attribute with (nolock)
			where Attribute_id != isnull(:Attribute_id, 0)
			and Attribute_Code = :Attribute_Code 
			and (
				(Attribute_begDate <= :Attribute_begDate AND
				(Attribute_endDate >= :Attribute_endDate OR Attribute_endDate IS NULL))
			OR
				(:Attribute_begDate BETWEEN Attribute_begDate AND Attribute_endDate)
			OR
				(Attribute_begDate BETWEEN :Attribute_begDate AND :Attribute_endDate)
			OR
				(Attribute_endDate BETWEEN :Attribute_begDate AND :Attribute_endDate)
			OR
				(Attribute_begDate >= :Attribute_begDate AND :Attribute_endDate IS NULL)
			)
		";

		$queryParams = array(
			'Attribute_id' => $data['Attribute_id'],
			'Attribute_Code' => $data['Attribute_Code'],
			'Attribute_begDate' => $data['Attribute_begDate'],
			'Attribute_endDate' => $data['Attribute_endDate']
		);

		// echo getDebugSQL($query, $queryParams); die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение области видимости атрибута
	 */
	function saveAttributeVision($data) {
		$params = array(
			'AttributeVision_id' => empty($data['AttributeVision_id']) ? null : $data['AttributeVision_id'],
			'Attribute_id' => $data['Attribute_id'],
			'AttributeVision_TableName' => empty($data['AttributeVision_TableName']) ? null : $data['AttributeVision_TableName'],
			'AttributeVision_TablePKey' => empty($data['AttributeVision_TablePKey']) ? null : $data['AttributeVision_TablePKey'],
			'AttributeVision_Sort' => $data['AttributeVision_Sort'],
			'Region_id' => $data['Region_id'],
			'Org_id' => empty($data['Org_id']) ? null : $data['Org_id'],
			'AttributeVision_begDate' => $data['AttributeVision_begDate'],
			'AttributeVision_endDate' => empty($data['AttributeVision_endDate']) ? null : $data['AttributeVision_endDate'],
			'AttributeVision_AppCode' => empty($data['AttributeVision_AppCode']) ? null : $data['AttributeVision_AppCode'],
			'AttributeSign_id' => empty($data['AttributeSign_id']) ? null : $data['AttributeSign_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		if ($data['AttributeVision_IsKeyValue']) {
			$params['AttributeVision_IsKeyValue'] = 2;
		} else {
			$params['AttributeVision_IsKeyValue'] = 1;
		}

		//Для каждого AttributeVision_TableName по одному AttributeVision_IsKeyValue = 2
		if ($params['AttributeVision_IsKeyValue'] == 2) {
			$existsKeyValue = $this->getFirstResultFromQuery('
				select top 1 AttributeVision_id
				from v_AttributeVision with(nolock)
				where AttributeVision_TableName = :AttributeVision_TableName
					and AttributeVision_IsKeyValue = 2
					and ISNULL(AttributeVision_TablePKey, 0) = ISNULL(:AttributeVision_TablePKey, 0)
					and AttributeVision_id != ISNULL(:AttributeVision_id, 0)
			', $params);

			if ($existsKeyValue !== false && !empty($existsKeyValue)) {
				return array(array('success' => false, 'Error_Msg' => 'Флаг "Является значением" можно установить только для одного атрибута.'));
			}
		}

		$procedure = 'p_AttributeVision_ins';
		if (!empty($params['AttributeVision_id'])) {
			$procedure = 'p_AttributeVision_upd';
		}

		$query = "
			declare
				@AttributeVision_id bigint = :AttributeVision_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@AttributeVision_id = @AttributeVision_id output,
				@Attribute_id = :Attribute_id,
				@AttributeVision_TableName = :AttributeVision_TableName,
				@AttributeVision_TablePKey = :AttributeVision_TablePKey,
				@AttributeVision_IsKeyValue = :AttributeVision_IsKeyValue,
				@AttributeVision_Sort = :AttributeVision_Sort,
				@Region_id = :Region_id,
				@Org_id = :Org_id,
				@AttributeVision_begDate = :AttributeVision_begDate,
				@AttributeVision_endDate = :AttributeVision_endDate,
				@AttributeVision_AppCode = :AttributeVision_AppCode,
				@AttributeSign_id = :AttributeSign_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @AttributeVision_id as AttributeVision_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных признака атрибутов
	 */
	function getAttributeSign($AttributeSign_id) {
		if (empty($this->_attributeSignCache[$AttributeSign_id])) {
			$this->_attributeSignCache[$AttributeSign_id] = $this->getFirstRowFromQuery("
				select top 1
					AttributeSign_id,
					AttributeSign_Code,
					AttributeSign_Name,
					AttributeSign_TableName
				from v_AttributeSign with(nolock)
				where AttributeSign_id = :AttributeSign_id
			", array('AttributeSign_id' => $AttributeSign_id));
		}
		return $this->_attributeSignCache[$AttributeSign_id];
	}

	/**
	 * Проверка значения атрибута с признаком
	 */
	function checkAttributeSignValue($data, $AttributeValues) {
		if (!empty($data['AttributeSignValue_endDate'])) {
			$begDate = DateTime::createFromFormat('Y-m-d', $data['AttributeSignValue_begDate']);
			$endDate = DateTime::createFromFormat('Y-m-d', $data['AttributeSignValue_endDate']);

			if ($begDate > $endDate) {
				return $this->createError('', 'Дата начала должна быть меньше, чем дата окончания');
			}
		}

		$AttributeSign = $this->getAttributeSign($data['AttributeSign_id']);
		if ($AttributeSign === false) {
			return $this->createError('', 'Ошибка при получении признака атрибутов');
		}

		$values = array();
		if (is_array($AttributeValues)) {
			foreach($AttributeValues as $AttributeValue) {
				$values[$AttributeValue['Attribute_SysNick']] = array(
					'id' => !empty($AttributeValue['AttributeValue_id'])?$AttributeValue['AttributeValue_id']:null,
					'value' => !empty($AttributeValue['AttributeValue_Value'])?$AttributeValue['AttributeValue_Value']:null
				);
			}
		}

		$params = array(
			'AttributeSignValue_id' => !empty($data['AttributeSignValue_id'])?$data['AttributeSignValue_id']:null,
			'AttributeSign_id' => $data['AttributeSign_id'],
			'AttributeSignValue_TablePKey' => $data['AttributeSignValue_TablePKey'],
			'AttributeSignValue_begDate' => $data['AttributeSignValue_begDate'],
			'AttributeSignValue_endDate' => !empty($data['AttributeSignValue_endDate'])?$data['AttributeSignValue_endDate']:null,
		);
		$additFilter = "";

		if ($AttributeSign['AttributeSign_Code'] == 2
			&& isset($values['StructureUnitNomen']) && !empty($values['StructureUnitNomen']['value'])
		) {
			$additFilter .= "and exists(
					select top 1 AttributeValue_id
					from v_AttributeValue AV with(nolock)
					inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
					where
						A.Attribute_SysNick = 'StructureUnitNomen'
						and AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						and AV.AttributeValue_ValueIdent = :AttributeValue_Value
						and (:AttributeValue_id is null or AV.AttributeValue_id <> :AttributeValue_id)
				)
			";
			$params['AttributeValue_id'] = $values['StructureUnitNomen']['id'];
			$params['AttributeValue_Value'] = $values['StructureUnitNomen']['value'];
		}
		
		if ($AttributeSign['AttributeSign_Code'] == 21
			&& isset($values['StacCode']) && !empty($values['StacCode']['value'])
		) {
			$additFilter .= "
				and exists(
					select *
					from v_AttributeValue AV with(nolock)  
					inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
					where
						A.Attribute_SysNick = 'StacCode'
						and AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						and AV.AttributeValue_ValueString = :AttributeValue_Value
						and (:AttributeValue_id is null or AV.AttributeValue_id <> :AttributeValue_id)
				)
			";
			$params['AttributeValue_id'] = $values['StacCode']['id'];
			$params['AttributeValue_Value'] = $values['StacCode']['value'];
		}

		$query = "
			declare @begDate date = :AttributeSignValue_begDate
			declare @endDate date = :AttributeSignValue_endDate
			select top 1
				count(ASV.AttributeSignValue_id) as Count
			from
				v_AttributeSignValue ASV with(nolock)
				inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
			where
				ASV.AttributeSignValue_id <> isnull(:AttributeSignValue_id, 0)
				and [AS].AttributeSign_id = :AttributeSign_id
				and ASV.AttributeSignValue_TablePKey = :AttributeSignValue_TablePKey
				and ASV.AttributeSignValue_begDate <= isnull(@endDate, ASV.AttributeSignValue_begDate)
				and (ASV.AttributeSignValue_endDate > @begDate or ASV.AttributeSignValue_endDate is null)
				{$additFilter}
		";

		//echo getDebugSQL($query, $params);exit;
		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return $this->createError('', 'Ошибка при проверке пересечения периодов действия признака');
		}
		if ($count > 0) {
			return $this->createError('', "Уже сущестует набор атрибутов по признаку \"{$AttributeSign['AttributeSign_Name']}\", действующий в указанный период времени");
		}

		return array(array('success' => true, 'Error_Msg' => ''));
	}

	/**
	 * Сохранение значения атрибута с признаком
	 */
	function saveAttributeSignValue($data) {
		$this->beginTransaction();

		$AttributeValueSaveParams = !empty($data['AttributeValueSaveParams'])?json_decode($data['AttributeValueSaveParams'], true):null;
		$resp = $this->checkAttributeSignValue($data, $AttributeValueSaveParams);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		if (!empty($data['AttributeSignValue_id']) && $data['AttributeSignValue_id'] > 0) {
			$procedure = 'p_AttributeSignValue_upd';
		} else {
			$procedure = 'p_AttributeSignValue_ins';
		}

		$query = "
			declare
				@AttributeSignValue_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			set @AttributeSignValue_id = :AttributeSignValue_id;
			exec {$procedure}
				@AttributeSignValue_id = @AttributeSignValue_id output,
				@AttributeSign_id = :AttributeSign_id,
				@AttributeSignValue_TablePKey = :AttributeSignValue_TablePKey,
				@AttributeSignValue_begDate = :AttributeSignValue_begDate,
				@AttributeSignValue_endDate = :AttributeSignValue_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @AttributeSignValue_id as AttributeSignValue_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$params = array(
			'AttributeSignValue_id' => (!empty($data['AttributeSignValue_id']) && $data['AttributeSignValue_id'] > 0)?$data['AttributeSignValue_id']:null,
			'AttributeSign_id' => $data['AttributeSign_id'],
			'AttributeSignValue_TablePKey' => $data['AttributeSignValue_TablePKey'],
			'AttributeSignValue_begDate' => $data['AttributeSignValue_begDate'],
			'AttributeSignValue_endDate' => !empty($data['AttributeSignValue_endDate'])?$data['AttributeSignValue_endDate']:null,
			'pmUser_id' => $data['pmUser_id']
		);
		$response = $this->queryResult($query, $params);
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}

		if (is_array($AttributeValueSaveParams)) {
			foreach($AttributeValueSaveParams as $AttributeValue) {
				switch($AttributeValue['RecordStatus_Code']) {
					case 0:
					case 2:
						if ($AttributeValue['AttributeValue_TableName'] == $data['AttributeSign_TableName']) {
							$AttributeValue['AttributeValue_TablePKey'] = $data['AttributeSignValue_TablePKey'];
						}
						$AttributeValue['AttributeValue_begDate'] = $data['AttributeSignValue_begDate'];
						$AttributeValue['AttributeValue_endDate'] = $data['AttributeSignValue_endDate'];
						$AttributeValue['AttributeSignValue_id'] = $response[0]['AttributeSignValue_id'];
						$AttributeValue['pmUser_id'] = $data['pmUser_id'];

						$resp = $this->saveAttributeValue($AttributeValue);
						if (!$this->isSuccessful($resp)) {
							$this->rollbackTransaction();
							return $resp;
						}
						break;
					case 3:
						$resp = $this->deleteAttributeValue($AttributeValue);
						if (!$this->isSuccessful($resp)) {
							$this->rollbackTransaction();
							return $resp;
						}
				}
			}
		}

		//Для одного атрибута на объекте должно быть только одно значение. Лишние удаляются
		$query = "
			select
				AV.AttributeValue_id
			from v_AttributeValue AV with(nolock)
			where
				AV.AttributeSignValue_id = :AttributeSignValue_id
				and AV.AttributeValue_id <> (
					select top 1 AttributeValue_id
					from v_AttributeValue with(nolock)
					where Attribute_id = AV.Attribute_id
					and AttributeSignValue_id = AV.AttributeSignValue_id
					order by AttributeValue_updDT desc
				)
		";
		$resp = $this->queryResult($query, array('AttributeSignValue_id' => $response[0]['AttributeSignValue_id']));
		if (!is_array($resp)) {
			$this->rollbackTransaction();
			return $this->createError('', 'Ошибка при запросе повторяющихся значений');
		}
		foreach($resp as $item) {
			$resp = $this->deleteAttributeValue($item);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();

		return $response;
	}

	/**
	 * Получения данных для создания поля редактирования атрибута по признаку
	 */
	function getAttributesBySign($data) {
		$params = array(
			'AttributeSign_id' => $data['AttributeSign_id'],
			'Org_id' => (isset($data['session']) && !empty($data['session']['org_id']))?$data['session']['org_id']:null,
			'Region_id' => $this->getRegionNumber()
		);

		$query = "
			declare @date date = dbo.tzGetDate()
			declare @Region_id int = isnull(:Region_id,0)
			declare @Org_id bigint = isnull(:Org_id,0)
			select
				A.Attribute_id,
				A.Attribute_Code,
				A.Attribute_Name,
				A.Attribute_SysNick,
				A.Attribute_TableName,
				A.Attribute_TablePKey,
				AV.AttributeVision_id,
				AV.AttributeVision_Sort,
				AV.AttributeVision_AppCode,
				[AS].AttributeSign_TableName as AttributeVision_TableName,
				convert(varchar(10), AV.AttributeVision_begDate, 104) as AttributeVision_begDate,
				convert(varchar(10), AV.AttributeVision_endDate, 104) as AttributeVision_endDate,
				case when
					@date >= AV.AttributeVision_begDate
					and (AV.AttributeVision_endDate is null or @date < AV.AttributeVision_endDate)
				then 1 else 0 end as AttributeVision_InDate,
				AV.Org_id,
				AV.Region_id,
				AVT.AttributeValueType_SysNick
			from
				v_AttributeVision AV with(nolock)
				inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
				inner join v_AttributeValueType AVT with(nolock) on AVT.AttributeValueType_id = A.AttributeValueType_id
				inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = AV.AttributeSign_id
			where
				AV.AttributeSign_id = :AttributeSign_id
				and isnull(AV.Org_id, @Org_id) = @Org_id
				and isnull(AV.Region_id, @Region_id) = @Region_id
			order by
				case when isnull(AV.AttributeVision_begDate, @date) <= @date then 1 else 0 end,
				case when isnull(AV.AttributeVision_endDate, @date) > @date then 1 else 0 end,
				AV.AttributeVision_begDate desc
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение атрибутов для объекта
	 */
	function getAttributesForObject($data) {
		$params = array();
		if (empty($data['object']) && empty($data['attrObjects'])) {
			return false;
		}
		$attr_arr = array();
		if (empty($data['attrObjects'])) {
			$attr_arr[] = $data['object'];
		} else {
			$attr_arr = $data['attrObjects'];
		}

		$objects_str = "'".implode("','", $attr_arr)."'";

		$query = "
			declare @date date = dbo.tzGetDate()
			select
				A.Attribute_id,
				A.Attribute_Code,
				A.Attribute_Name,
				A.Attribute_SysNick,
				A.Attribute_TableName,
				A.Attribute_TablePKey,
				AV.AttributeVision_id,
				AV.AttributeVision_Sort,
				AV.AttributeVision_AppCode,
				AV.AttributeVision_TableName,
				convert(varchar(10), AV.AttributeVision_begDate, 104) as AttributeVision_begDate,
				convert(varchar(10), AV.AttributeVision_endDate, 104) as AttributeVision_endDate,
				case when
					@date >= AV.AttributeVision_begDate
					and (AV.AttributeVision_endDate is null or @date < AV.AttributeVision_endDate)
				then 1 else 0 end as AttributeVision_InDate,
				AV.Org_id,
				AV.Region_id,
				AVT.AttributeValueType_SysNick
			from v_Attribute A with(nolock)
				inner join v_AttributeVision AV with(nolock) on AV.Attribute_id = A.Attribute_id
				inner join v_AttributeValueType AVT with(nolock) on AVT.AttributeValueType_id = A.AttributeValueType_id
			where
				AV.AttributeVision_TableName in ({$objects_str})
				and @date >= A.Attribute_begDate
				and (A.Attribute_endDate is null or @date < A.Attribute_endDate)
			order by
				AV.AttributeVision_Sort
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Получение значений атрибутов
	 */
	function getAttributesValues($attributes) {
		$params = array();
		if (!is_array($attributes) || count($attributes) == 0) {
			return false;
		}

		$where_arr = array();
		foreach($attributes as $attribute) {
			if (!empty($attribute['AttributeVision_id']) && !empty($attribute['AttributeValue_ValueIdent'])) {
				$vision_id = $attribute['AttributeVision_id'];
				$value_ident = $attribute['AttributeValue_ValueIdent'];
				$where_arr[] = "(AVal.AttributeVision_id = {$vision_id} and AVal.AttributeValue_ValueIdent = {$value_ident})";
			}
		}
		if (count($where_arr) == 0) {
			return false;
		}
		$where = "(".implode(' or ', $where_arr).")";

		$query = "
			select
				A.Attribute_SysNick,
				AVal.AttributeValue_id,
				coalesce(
					cast(AVal.AttributeValue_ValueIdent as varchar),
					cast(AVal.AttributeValue_ValueInt as varchar),
					cast(AVal.AttributeValue_ValueFloat as varchar),
					AVal.AttributeValue_ValueString,
					cast(AVal.AttributeValue_ValueBoolean as varchar),
					convert(varchar(10), AVal.AttributeValue_ValueDate, 104)
				) as AttributeValue_Value
			from v_AttributeValue AVal with(nolock)
				inner join v_Attribute A with(nolock) on A.Attribute_id = AVal.Attribute_id
			where
				{$where}
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Сохранение значения атрибута
	 */
	function saveAttributeValue($data) {
		$params = array(
			'AttributeValue_id' => !empty($data['AttributeValue_id'])?$data['AttributeValue_id']:null,
			'AttributeValue_pid' => !empty($data['AttributeValue_pid'])?$data['AttributeValue_pid']:null,
			'AttributeValue_rid' => !empty($data['AttributeValue_rid'])?$data['AttributeValue_rid']:null,
			'AttributeValue_begDate' => !empty($data['AttributeValue_begDate'])?$data['AttributeValue_begDate']:null,
			'AttributeValue_endDate' => !empty($data['AttributeValue_endDate'])?$data['AttributeValue_endDate']:null,
			'AttributeValue_ValueText' => !empty($data['AttributeValue_ValueText'])?$data['AttributeValue_ValueText']:null,
			'Attribute_id' => $data['Attribute_id'],
			'AttributeValue_TableName' => $data['AttributeValue_TableName'],
			'AttributeValue_TablePKey' => !empty($data['AttributeValue_TablePKey'])?$data['AttributeValue_TablePKey']:null,
			'AttributeVision_id' => $data['AttributeVision_id'],
			'AttributeValue_ValueIdent' => !empty($data['AttributeValue_ValueIdent'])?$data['AttributeValue_ValueIdent']:null,
			'AttributeValue_ValueInt' => null,
			'AttributeValue_ValueFloat' => null,
			'AttributeValue_ValueString' => null,
			'AttributeValue_ValueBoolean' => null,
			'AttributeValue_ValueDate' => null,
			'AttributeSignValue_id' => !empty($data['AttributeSignValue_id'])?$data['AttributeSignValue_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);
		switch ($data['AttributeValueType_SysNick']) {
			case 'ident':
			case 'baseident':
				$params['AttributeValue_ValueIdent'] = $data['AttributeValue_Value'];
			break;

			case 'int':
				$params['AttributeValue_ValueInt'] = $data['AttributeValue_Value'];
			break;

			case 'float':
			case 'money':
				$params['AttributeValue_ValueFloat'] = $data['AttributeValue_Value'];
			break;

			case 'string':
				$params['AttributeValue_ValueString'] = $data['AttributeValue_Value'];
			break;

			case 'date':
				$params['AttributeValue_ValueDate'] = $data['AttributeValue_Value'];
			break;

			case 'bool':
				$params['AttributeValue_ValueBoolean'] = $data['AttributeValue_Value'];
			break;
		}

		$procedure = 'p_AttributeValue_ins';
		if (!empty($params['AttributeValue_id'])) {
			$procedure = 'p_AttributeValue_upd';
		}

		$query = "
			declare
				@AttributeValue_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			set @AttributeValue_id = :AttributeValue_id;
			exec {$procedure}
				@AttributeValue_id = @AttributeValue_id output,
				@AttributeValue_pid = :AttributeValue_pid,
				@AttributeValue_rid = :AttributeValue_rid,
				@AttributeValue_begDate = :AttributeValue_begDate,
				@AttributeValue_endDate = :AttributeValue_endDate,
				@AttributeValue_ValueText = :AttributeValue_ValueText,
				@Attribute_id = :Attribute_id,
				@AttributeValue_TableName = :AttributeValue_TableName,
				@AttributeValue_TablePKey = :AttributeValue_TablePKey,
				@AttributeVision_id = :AttributeVision_id,
				@AttributeValue_ValueIdent = :AttributeValue_ValueIdent,
				@AttributeValue_ValueInt = :AttributeValue_ValueInt,
				@AttributeValue_ValueFloat = :AttributeValue_ValueFloat,
				@AttributeValue_ValueString = :AttributeValue_ValueString,
				@AttributeValue_ValueBoolean = :AttributeValue_ValueBoolean,
				@AttributeValue_ValueDate = :AttributeValue_ValueDate,
				@AttributeSignValue_id = :AttributeSignValue_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @AttributeValue_id as AttributeValue_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}

		return false;
	}
}
