<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
* RoutingLevel_model - Уровень маршрутизации
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
class RoutingLevel_model extends swModel {
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
			'pid' => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM
				],
				'label' => 'Идентификатор родительского уровня',
				'save' => 'required',
				'type' => 'string'
			],
			'cid' => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM
				],
				'label' => 'Идентификатор дочернего уровня',
				'save' => 'required',
				'type' => 'string'
			],
			'begDT' => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME
				],
				'alias' => 'RoutingLevel_begDT',
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
		return 'RoutingLevel';
	}

	protected function _validate() {
		return true;
	}

	/**
	 * Возвращает список уровней ниже, чем переданный RoutingLevel_id
	 * array['RoutingLevel_id'] Идентификатор уровня маршрутизации
	 * @param Array $params Параметры для запроса
	 * @return Array Список уровней
	 * @throws Exception
	 */
	public function doLoad($params) {
		$query = "";
		if (!empty($params['RoutingLevel_id'])) {
			$query = "declare @level int = :RoutingLevel_id;
				with rec (RoutingLevel_id, RoutingLevel_cid, RoutingLevel_name) as (
					select rl1.RoutingLevel_id, rl1.RoutingLevel_cid, rl1.RoutingLevel_name
					from v_RoutingLevel rl1 (nolock)
					where rl1.RoutingLevel_id = @level

					union all

					select rl2.RoutingLevel_id, rl2.RoutingLevel_cid, rl2.RoutingLevel_name
					from rec, v_RoutingLevel rl2 (nolock) 
					where rec.RoutingLevel_cid = rl2.RoutingLevel_id
				)
				select * from rec where RoutingLevel_id <> @level
			";
		} else {
			 $query = "select RoutingLevel_id, RoutingLevel_name
			 	from v_RoutingLevel (nolock)";
		}
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}
}
