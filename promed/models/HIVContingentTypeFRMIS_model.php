<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * HIVContingentTypeFRMIS - Тип контингента для ФРМИС
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
class HIVContingentTypeFRMIS_model extends swModel {
	static function defAttributes() {
		return [
			self::ID_KEY => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'HIVContingentTypeFRMIS_id',
				'label' => 'Идентификатор записи',
				'save' => 'trim',
				'type' => 'int'
			],
			'hivcontingenttypefrmis_code' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'HIVContingentTypeFRMIS_Code',
				'label' => 'Код записи',
				'save' => 'trim',
				'type' => 'string'
			],
			'hivcontingenttypefrmis_name' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'HIVContingentTypeFRMIS_Name',
				'label' => 'Наименование записи',
				'save' => 'trim',
				'type' => 'string'
			]
		];
	}
	public function __construct() {
		parent::__construct();
	}
	public function tableName() {
		return 'HIVContingentTypeFRMIS';
	}
	protected function _validate() {
		return true;
	}
	/**
	 * Возвращает данные для грида
	 * @return Array Данные для грида
	 * @throws Exception
	 */
	public function getAll() {
		$query = "select
				HIVContingentTypeFRMIS_id,
				HIVContingentTypeFRMIS_Code,
				HIVContingentTypeFRMIS_Name
			from HIVContingentTypeFRMIS
			where HIVContingentTypeFRMIS_Code != 100
		";
		try {
			return $this->queryResult($query);
		} catch (Exception $e) {
			throw $e;
		}
	}
}
