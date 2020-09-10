<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
* RoutingProfile_model - Тип маршрутизации
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
class RoutingProfile_model extends SwPgModel {
	static function defAttributes() {
		return [
			self::ID_KEY => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL
				],
				'alias' => '_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			],
			'name' => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM
				],
				'label' => 'Наименование',
				'save' => 'required',
				'type' => 'string'
			],
			'sysnick' => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM
				],
				'label' => 'Системное наименование',
				'save' => 'required',
				'type' => 'string'
			],
			'begdate' => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME
				],
				'alias' => 'RoutingProfile_begDate',
				'label' => 'Дата начала действия',
				'save' => 'required',
				'type' => 'date'
			],
			'morbustype_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'MorbusType_id',
				'label' => 'Тип заболевания',
				'save' => '',
				'type' => 'id'
			],
			'region_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'Region_id',
				'label' => 'Регион',
				'save' => 'required',
				'type' => 'id'
			]
		];
	}

	public function __construct() {
		parent::__construct();
		if ($this->usePostgreLis)
			$this->load->swapi('lis');
	}

	public function tableName() {
		return 'RoutingProfile';
	}

	protected function _validate() {
		return true;
	}

	/**
	 * Возвращает список типов маршрутизации региона
	 * array['Region_id'] Номер региона полученный из getGlobalOptions().region.number
	 * @param Array $params Параметры для запроса (см. выше)
	 * @return Array Список типов маршрутизации
	 * @throws Exception
	 */
	public function loadProfileList($params) {
		$whereClause = "";

		if (!empty($params['Region_id'])) {
			$whereClause .= " and rp.Region_id = :Region_id";
		}

		$query = "select
				rp.RoutingProfile_id as \"RoutingProfile_id\",
				rp.RoutingProfile_name as \"RoutingProfile_name\",
				rp.RoutingProfile_sysnick as \"RoutingProfile_sysnick\",
				rp.MorbusType_id as \"MorbusType_id\",
				rp.Region_id as \"Region_id\"
			from v_RoutingProfile  rp
			where
				(rp.RoutingProfile_endDate is null or datediff('day',CAST(rp.RoutingProfile_endDate as timestamp), CAST(GETDATE() as timestamp)) > 0)
				{$whereClause}";
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Удаляет тип маршрутизации
	 * array['RoutingProfile_id'] Идентификатор типа маршрутизации
	 * @param Array $params Параметры для запроса (см. выше)
	 * @return Array Состояние запроса
	 * @throws Exception
	 */
	public function delete($params) {
		$query = "
			update RoutingProfile
			set RoutingProfile_endDate = dbo.tzGetDate()
			where RoutingProfile_id = :RoutingProfile_id
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
	 * Проверяет количество записей по переданному атрибуту
	 * array['$attribute'] Значение проверяемого атрибута
	 * @param Array $params Параметры для запроса (см. выше)
	 * @param String $attribute Наименование атрибута
	 * @return Boolean Флаг наличия записей
	 * @throws Exception
	 */
	public function checkAttribute($attribute, $params) {
		$query = "select count(*) as \"count\"
			from v_RoutingProfile 
			where {$attribute} = :{$attribute}
			and (RoutingProfile_endDate > dbo.tzGetDate() or RoutingProfile_endDate is null)";
		try {
			$count = $this->db->query($query, $params)->row()->count;
			return $count > 0 ? false : true;
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}
}
