<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * RaceType - Тип расы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author
 * @version
 */
class RaceType_model extends swModel {
	static function defAttributes() {
		return [
			self::ID_KEY => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'RaceType_id',
				'label' => 'Идентификатор расы',
				'save' => 'trim',
				'type' => 'int'
			],
			'RaceType_Name' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'RaceType_Name',
				'label' => 'Название',
				'save' => 'trim',
				'type' => 'string'
			],
			'RaceType_SysNick' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'RaceType_SysNick',
				'label' => 'Системное наименование',
				'save' => 'trim',
				'type' => 'string'
			]
		];
	}
	public function __construct() {
		parent::__construct();
	}
	public function tableName() {
		return 'RaceType';
	}
	protected function _validate() {
		return true;
	}
	/**
	 * Возвращает данные для грида
	 * @param Array $params Параметры для запроса
	 * @return Array Данные для грида
	 * @throws Exception
	 */
	public function loadGrid() {
		$query = 'select
				RaceType_id,
				RaceType_SysNick,
				RaceType_Name
			from v_RaceType
		';
		try {
			return $this->queryResult($query);
		} catch (Exception $e) {
			throw $e;
		}
	}
}
