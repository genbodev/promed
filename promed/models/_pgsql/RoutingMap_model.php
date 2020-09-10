<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
* RoutingMap_model - Карта маршрутизации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Sharipov Fidan
* @version      11.2019
*/
class RoutingMap_model extends swPgModel {
	static function defAttributes() {
		return [
			self::ID_KEY => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL
				],
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			],
			'pid' => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM
				],
				'label' => 'Родительская запись',
				'save' => '',
				'type' => 'id'
			],
			'routingprofile_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'RoutingProfile_id',
				'label' => 'Тип маршрутизации',
				'save' => 'required',
				'type' => 'id'
			],
			'routinglevel_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'RoutingLevel_id',
				'label' => 'Уровень',
				'save' => 'required',
				'type' => 'id'
			],
			'lpu_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'Lpu_id',
				'label' => 'МО',
				'save' => 'required',
				'type' => 'id'
			],
			'begdate' => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME
				],
				'alias' => 'RoutingMap_begDate',
				'label' => 'Дата начала действия',
				'save' => 'required',
				'type' => 'date'
			]
		];
	}

	public function __construct() {
		parent::__construct();
		if ($this->usePostgreLis)
			$this->load->swapi('lis');
	}

	public function tableName() {
		return 'RoutingMap';
	}

	protected function _validate() {
		return true;
	}

	/**
	 * Возвращает вложенное дерево МО в рамках типа маршрутизации
	 * array['RoutingProfile_id'] Идентификатор типа маршрутизации
	 * @param Array $params Параметры для запроса (см. выше)
	 * @return Array Список МО
	 * @throws Exception
	 */
	public function loadRoutingMapList($params) {
		$query = "select
				rm.RoutingMap_id as \"RoutingMap_id\",
				rm.RoutingMap_pid as \"RoutingMap_pid\",
				coalesce(rm.RoutingMap_pid, 0) as \"RoutingMap_pid\",
				rl.RoutingLevel_id as \"RoutingLevel_id\",
				rl.RoutingLevel_name as \"RoutingLevel_name\",
				case
					when (select count(*)
							from v_RoutingMap  rm1
							where RoutingMap_pid = rm.RoutingMap_id
							and (rm1.RoutingMap_endDate is null or datediff('day',CAST(rm.RoutingMap_endDate as timestamp), CAST(GETDATE() as timestamp)) > 0 )
						) = 0
						then 1
					else 0
				end as \"leaf\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				'1' as \"expanded\",
				(rl.RoutingLevel_name || ' ' || lpu.Lpu_Nick) as \"text\"
			from v_RoutingMap  rm
			inner join v_Lpu  lpu on lpu.Lpu_id = rm.Lpu_id
			inner join v_RoutingLevel  rl on rl.RoutingLevel_id = rm.RoutingLevel_id
			where RoutingProfile_id = :RoutingProfile_id
				and (rm.RoutingMap_endDate is null or datediff('day',CAST(rm.RoutingMap_endDate as timestamp), CAST(GETDATE() as timestamp)) > 0)
		";
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Возвращает список подчиненных МО
	 * array['RoutingProfile_id'] Идентификатор типа маршрутизации
	 * array['OnlyActive'] Флаг актуальности
	 * array['RoutingMap_pid'] Идентификатор родительской записи
	 * @param Array $params Параметры для запроса (см. выше)
	 * @return Array Список МО
	 * @throws Exception
	 */
	public function loadGrid($params) {
		$whereClause = "1=1";

		if (!empty($params['RoutingProfile_id'])) {
			$whereClause .= " and rm.RoutingProfile_id = :RoutingProfile_id";
		}
		if (!empty($params['OnlyActive'])) {
			if ($params['OnlyActive'] == 2) {
				$whereClause .= " and (rm.RoutingMap_endDate is null or datediff('day',CAST(rm.RoutingMap_endDate as timestamp), CAST(GETDATE() as timestamp)) > 0)";
			} elseif ($params['OnlyActive'] == 3) {
				$whereClause .= " and rm.RoutingMap_endDate is not null";
			}
		}
		if (!empty($params['RoutingMap_pid'])) {
			$whereClause .= " and rm.RoutingMap_pid = :RoutingMap_pid";
		} else {
			$whereClause .= " and rm.RoutingMap_pid is null";
		}

		$query = "select
				rm.RoutingMap_id as \"RoutingMap_id\",
				rl.RoutingLevel_name as \"RoutingLevel_name\",
				rl.RoutingLevel_id as \"RoutingLevel_id\",
				lpu.Lpu_Name as \"Lpu_Name\",
				to_char(rm.RoutingMap_begDate, 'DD.MM.YYYY') as \"RoutingMap_begDate\",
				to_char(rm.RoutingMap_endDate, 'DD.MM.YYYY') as \"RoutingMap_endDate\"
			from v_RoutingMap  rm
			inner join v_Lpu  lpu on lpu.Lpu_id = rm.Lpu_id
			inner join v_RoutingLevel  rl on rl.RoutingLevel_id = rm.RoutingLevel_id
			where {$whereClause}";
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Удаляет запись
	 * array['RoutingMap_id'] Идентификатор записи
	 * array['permanenteDelete'] Флаг перманентного удаления
	 * @param Array $params Параметры для запроса (см. выше)
	 * @return Array Состояние записи
	 * @throws Exception
	 */
	public function delete($params) {
		if ($params['permanenteDelete'] == 2) {
			$query = "
				delete from RoutingMap
				where RoutingMap_id = :RoutingMap_id;
				";
		} else {
			$query = "
				update RoutingMap
				set RoutingMap_endDate = dbo.tzGetDate()
				where RoutingMap_id = :RoutingMap_id;
				";
		}
		try {
			$response = $this->db->query($query, $params);
			return [
				'success' => $response
			];
		} catch (Exception $e) {
			return [
				'Error_Msg' => $e->getMessage()
			];
		}
	}

	/**
	 * Удаляет все записи по типу маршрутизации
	 * array['RoutingProfile_id'] Идентификатор типа маршрутизации
	 * array['permanenteDelete'] Флаг перманентного удаления
	 * @param Array $params Параметры для запроса (см. выше)
	 * @return Array Состояние записи
	 * @throws Exception
	 */
	public function deleteByProfile($params) {
		if ($params['permanenteDelete'] == 2) {
			$query = "
				delete from RoutingMap
				where RoutingProfile_id = :RoutingProfile_id;
				";
		} else {
			$query = "
				update RoutingMap
				set RoutingMap_endDate = dbo.tzGetDate()
				where RoutingProfile_id = :RoutingProfile_id;
				";
		}
		try {
			$response = $this->db->query($query, $params);
			return [
				'success' => $response
			];
		} catch (Exception $e) {
			return [
				'Error_Msg' => $e->getMessage()
			];
		}
	}

	/**
	 * Восстанавливает запись
	 * array['RoutingMap_id'] Идентификатор записи
	 * @param Array $params Параметры для запроса (см. выше)
	 * @return Array Состояние записи
	 * @throws Exception
	 */
	public function restore($params) {
		$query = "
			update RoutingMap
			set RoutingMap_endDate = null
			where RoutingMap_id = :RoutingMap_id;
			";
		try {
			$response = $this->db->query($query, $params);
			return [
				'success' => $response
			];
		} catch (Exception $e) {
			return [
				'Error_Msg' => $e->getMessage()
			];
		}
	}

	/**
	 * Возвращает количество записей
	 * array['RoutingProfile_id'] Идентификатор типа маршрутизации
	 * array['RoutingMap_pid'] Идентификатор записи карты маршрутизации
	 * array['Lpu_id'] Идентификатор МО
	 * @param Array $params Параметры для запроса (см. выше)
	 * @return Integer Количество записей
	 * @throws Exception
	 */
	public function getLpuCount($params) {
		if (!empty($params['RoutingMap_pid'])) {
			$whereClause = "and RoutingMap_pid = :RoutingMap_pid";
		} else {
			$whereClause = "and RoutingMap_pid is null";
		}
		$query = "select count(RoutingMap_id) as \"count\"
				from v_RoutingMap 
				where RoutingProfile_id = :RoutingProfile_id
					and Lpu_id = :Lpu_id
					{$whereClause}";
		try {
			$response = $this->db->query($query, $params)->row()->count;
			return $response;
		} catch (Exception $e) {
			return [
				'Error_Msg' => $e->getMessage()
			];
		}
	}

	/**
	 * Возвращает количество дочерних МО
	 * array['RoutingMap_id'] Идентификатор записи карты маршрутизации
	 * array['onlyActive'] Флаг активности
	 * @param Array $params Параметры для запроса (см. выше)
	 * @return Integer Количество записей
	 * @throws Exception
	 */
	public function getChildCount($params) {
		$whereClause = "";
		if (!empty($params['onlyActive']) && $params['onlyActive'] == 2) {
			$whereClause .= " and (RoutingMap_endDate is null or datediff('day',CAST(RoutingMap_endDate as timestamp), CAST(GETDATE() as timestamp)) > 0)";
		}
		$query = "select count(RoutingMap_id) as \"c_count\"
			from v_RoutingMap 
			where RoutingMap_pid = :RoutingMap_id
				{$whereClause}";
			try {
				return $this->db->query($query, $params)->row()->c_count;
			} catch (Exception $e) {
				log_message('error', $e->getMessage());
				throw $e;
			}
	}

	/**
	 * Возвращает все МО выше по иерархии
	 * array['Lpu_id'] Идентификатор МО
	 * array['RoutingProfile_id'] Идентификатор типа маршрутизации
	 * @param Array $params Параметры для запроса (см. выше)
	 * @return Array Массив МО
	 * @throws Exception
	 */
	public function getParentList($params) {
		$query = "
			with rec as (
				select rm1.RoutingMap_id, rm1.RoutingMap_pid, rm1.Lpu_id, rm1.RoutingLevel_id
				from v_RoutingMap rm1 
				where rm1.RoutingMap_id = (select RoutingMap_id from v_RoutingMap where Lpu_id = :Lpu_id and RoutingProfile_id = :RoutingProfile_id limit 1)
                ),
                rec2 as (
                select rm2.RoutingMap_id, rm2.RoutingMap_pid, rm2.Lpu_id, rm2.RoutingLevel_id
				from rec, v_RoutingMap rm2  
				where rec.RoutingMap_pid = rm2.RoutingMap_id
                union all
                select * from rec
                )
			select 
              RoutingMap_id as \"RoutingMap_id\",
              RoutingMap_pid as \"RoutingMap_pid\",
              Lpu_id as \"Lpu_id\",
              RoutingLevel_id as \"RoutingLevel_id\"
            from rec2";
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Возвращает все МО ниже по иерархии
	 * array['RoutingMap_id'] Идентификатор записи
	 * @param Array $params Параметры для запроса (см. выше)
	 * @return Array Массив МО
	 * @throws Exception
	 */
	public function getChildList($params) {
		$query = "			
			with cte as (
				select RoutingMap_id, RoutingMap_pid, Lpu_id
				from v_RoutingMap  rm
				where RoutingMap_id = :RoutingMap_id
			),
            cte2 as (
				select rm.RoutingMap_id, rm.RoutingMap_pid, rm.Lpu_id
				from v_RoutingMap  rm
				join cte on rm.RoutingMap_pid = cte.RoutingMap_id
				UNION ALL
                select * from cte                
            )
			select 
              RoutingMap_id as \"RoutingMap_id\", 
              RoutingMap_pid as \"RoutingMap_pid\", 
              Lpu_id as \"Lpu_id\"
            from cte2 
            order by RoutingMap_id;
			";
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}
}
