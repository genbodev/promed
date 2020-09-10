<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TableDirect_model - модель для работы с базовыми справочниками
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.07.2014
 */

class TableDirect_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Сохранение иформации о базовых справочниках
	 */
	function saveTableDirectInfo($data) {
		$params = array(
			'TableDirectInfo_id' => empty($data['TableDirectInfo_id']) ? null : $data['TableDirectInfo_id'],
			'TableDirectInfo_Code' => $data['TableDirectInfo_Code'],
			'TableDirectInfo_Name' => $data['TableDirectInfo_Name'],
			'TableDirectInfo_SysNick' => $data['TableDirectInfo_SysNick'],
			'TableDirectInfo_Descr' => empty($data['TableDirectInfo_Descr']) ? null : $data['TableDirectInfo_Descr'],
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = 'p_TableDirectInfo_ins';
		if (!empty($params['TableDirectInfo_id'])) {
			$procedure = 'p_TableDirectInfo_upd';
		}

		$query = "
			declare
				@TableDirectInfo_id bigint = :TableDirectInfo_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@TableDirectInfo_id = @TableDirectInfo_id output,
				@TableDirectInfo_Code = :TableDirectInfo_Code,
				@TableDirectInfo_Name = :TableDirectInfo_Name,
				@TableDirectInfo_SysNick = :TableDirectInfo_SysNick,
				@TableDirectInfo_Descr = :TableDirectInfo_Descr,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @TableDirectInfo_id as TableDirectInfo_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение базового справочника
	 */
	function saveTableDirect($data) {
		$params = array(
			'TableDirect_id' => empty($data['TableDirect_id']) ? null : $data['TableDirect_id'],
			'TableDirectInfo_id' => $data['TableDirectInfo_id'],
			'TableDirect_Name' => $data['TableDirect_Name'],
			'TableDirect_Code' => $data['TableDirect_Code'],
			'TableDirect_SysNick' => $data['TableDirect_SysNick'],
			'TableDirect_begDate' => $data['TableDirect_begDate'],
			'TableDirect_endDate' => empty($data['TableDirect_endDate']) ? null : $data['TableDirect_endDate'],
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = 'p_TableDirect_ins';
		if (!empty($params['TableDirect_id'])) {
			$procedure = 'p_TableDirect_upd';
		}

		$query = "
			declare
				@TableDirect_id bigint = :TableDirect_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@TableDirect_id = @TableDirect_id output,
				@TableDirectInfo_id = :TableDirectInfo_id,
				@TableDirect_Code = :TableDirect_Code,
				@TableDirect_Name = :TableDirect_Name,
				@TableDirect_SysNick = :TableDirect_SysNick,
				@TableDirect_begDate = :TableDirect_begDate,
				@TableDirect_endDate = :TableDirect_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @TableDirect_id as TableDirect_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление информации о базовых справочниках
	 */
	function deleteTableDirectInfo($data) {
		$params = array('TableDirectInfo_id' => $data['TableDirectInfo_id']);

		$query = "
			select top 1 count(*) as Count
			from v_TableDirect TD with(nolock)
			where TD.TableDirectInfo_id = :TableDirectInfo_id
		";

		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return false;
		}
		if ($count > 0) {
			return array('Error_Msg' => 'Невозможно удалить информацию о базовых справочниках. <br/>Существуют связанные с ней базовые справочники.');
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_TableDirectInfo_del
				@TableDirectInfo_id = :TableDirectInfo_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление базового справочника
	 */
	function deleteTableDirect($data) {
		$params = array('TableDirect_id' => $data['TableDirect_id']);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_TableDirect_del
				@TableDirect_id = :TableDirect_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка информации о базовых справочниках
	 */
	function loadTableDirectInfoGrid($data) {
		$params = array();
		$filters = "(1=1)";

		if (!empty($data['TableDirectInfo_Name'])) {
			$filters .= " and TDI.TableDirectInfo_Name like :TableDirectInfo_Name+'%'";
			$params['TableDirectInfo_Name'] = $data['TableDirectInfo_Name'];
		}

		$query = "
			select
				TDI.TableDirectInfo_id,
				TDI.TableDirectInfo_Code,
				TDI.TableDirectInfo_Name,
				TDI.TableDirectInfo_SysNick,
				TDI.TableDirectInfo_Descr
			from
				v_TableDirectInfo TDI with(nolock)
			where
				{$filters}
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$response = $result->result('array');
			return array('data' => $response);
		} else {
			return false;
		}
	}

	/**
	 * Получение списка базовых справочников
	 */
	function loadTableDirectGrid($data) {
		$params = array('TableDirectInfo_id' => $data['TableDirectInfo_id']);
		$filters = "";

		if (!empty($data['TableDirect_Name'])) {
			$filters .= " and TD.TableDirect_Name like :TableDirect_Name+'%'";
			$params['TableDirect_Name'] = $data['TableDirect_Name'];
		}

		$query = "
			select
				-- select
				TD.TableDirect_id,
				TD.TableDirect_Code,
				TD.TableDirect_Name,
				TD.TableDirect_SysNick,
				convert(varchar(10), TD.TableDirect_begDate, 104) as TableDirect_begDate,
				convert(varchar(10), TD.TableDirect_endDate, 104) as TableDirect_endDate,
				TD.TableDirectInfo_id
				-- end select
			from
				-- from
				v_TableDirect TD with(nolock)
				-- end from
			where
				-- where
				TD.TableDirectInfo_id = :TableDirectInfo_id
				{$filters}
				-- end where
			order by
				-- order by
				TD.TableDirect_Code
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
	 * Получение данных для редактирования информации о базовых справочниках
	 */
	function loadTableDirectInfoForm($data) {
		$params = array('TableDirectInfo_id' => $data['TableDirectInfo_id']);

		$query = "
			select top 1
				TDI.TableDirectInfo_id,
				TDI.TableDirectInfo_Code,
				TDI.TableDirectInfo_Name,
				TDI.TableDirectInfo_SysNick,
				TDI.TableDirectInfo_Descr
			from
				v_TableDirectInfo TDI with(nolock)
			where
				TDI.TableDirectInfo_id = :TableDirectInfo_id
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение данных для редактирования базового справочника
	 */
	function loadTableDirectForm($data) {
		$params = array('TableDirect_id' => $data['TableDirect_id']);

		$query = "
			select top 1
				TD.TableDirect_id,
				TD.TableDirectInfo_id,
				TD.TableDirect_Code,
				TD.TableDirect_Name,
				TD.TableDirect_SysNick,
				convert(varchar(10), TD.TableDirect_begDate, 104) as TableDirect_begDate,
				convert(varchar(10), TD.TableDirect_endDate, 104) as TableDirect_endDate
			from
				v_TableDirect TD with(nolock)
			where
				TD.TableDirect_id = :TableDirect_id
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение списка записей из базового справочника
	 */
	function loadTableDirectList($data) {
		$params = array('TableDirectInfo_id' => $data['TableDirectionInfo_id']);

		$query = "
			select
				TD.TableDirect_id,
				TD.TableDirect_Code,
				TD.TableDirect_Name,
				TD.TableDirect_SysNick,
				TD.TableDirectInfo_id
			from v_TableDirect TD with(nolock)
				TD.TableDirectInfo_id = :TableDirectInfo_id
				and TD.TableDirect_begDate <= dbo.tzGetDate()
				and (TD.TableDirect_endDate is null or TD.TableDirect_endDate > dbo.tzGetDate())
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Полечкние списка базовых справочников
	 */
	function loadTableDirectInfoList($data) {
		$params = array();

		$query = "
			select
				TDI.TableDirectInfo_id,
				TDI.TableDirectInfo_Code,
				TDI.TableDirectInfo_Name,
				TDI.TableDirectInfo_SysNick,
				TDI.TableDirectInfo_Descr
			from v_TableDirectInfo TDI with(nolock)
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение данных базовых справочников для атрибутов
	 */
	function loadTableDirectData($tableDirectInfo) {
		$params = array();
		if (!is_array($tableDirectInfo)) {
			$tableDirectInfo = array($tableDirectInfo);
		}
		$info_ids = implode(',', $tableDirectInfo);

		$query = "
			select
				TD.TableDirect_id,
				TD.TableDirect_Code,
				TD.TableDirect_Name,
				TD.TableDirect_SysNick,
				TD.TableDirectInfo_id
			from v_TableDirect TD with(nolock)
			where
				TD.TableDirectInfo_id in ({$info_ids})
				and TD.TableDirect_begDate <= dbo.tzGetDate()
				and (TD.TableDirect_endDate is null or TD.TableDirect_endDate > dbo.tzGetDate())
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result('array');

		$tableDirectData = array();
		foreach($resp as $item) {
			$key = $item['TableDirectInfo_id'];
			if (!isset($tableDirectData[$key])) {
				$tableDirectData[$key] = array();
			}
			$tableDirectData[$key][] = $item;
		}

		return $tableDirectData;
	}
}