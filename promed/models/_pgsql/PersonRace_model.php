<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PersonRace - Раса пациента
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
class PersonRace_model extends swPgModel {
	static function defAttributes() {
		return [
			self::ID_KEY => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME
				],
				'alias' => 'PersonRace_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'int'
			],
			'racetype_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'RaceType_id',
				'label' => 'Идентификатор типа расы',
				'save' => 'trim',
				'type' => 'int'
			],
			'personrace_setdt' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME
				],
				'alias' => 'PersonRace_setDT',
				'label' => 'Дата установки',
				'save' => 'trim',
				'type' => 'datetime'
			],
			'person_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'save' => 'trim',
				'type' => 'int'
			]
		];
	}
	public function __construct() {
		parent::__construct();
	}
	public function tableName() {
		return 'PersonRace';
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
	public function loadGrid($params) {
		$query = "
			select
				ps.Person_id as \"Person_id\",
				pr.PersonRace_id as \"PersonRace_id\",
				to_char(PersonRace_setDT, 'dd.mm.yyyy') as \"PersonRace_setDT\",
				rt.RaceType_id as \"RaceType_id\",
				rt.RaceType_Name as \"RaceType_Name\",
				rt.RaceType_SysNick as \"RaceType_SysNick\",
				coalesce(pr.pmUser_updID, pr.pmUser_insID) as \"pmUser_insID\",
				coalesce(PU.pmUser_Name, '') as \"pmUser_Name\"
			from v_PersonRace pr
			inner join v_PersonState ps on ps.Person_id = pr.Person_id
			inner join v_RaceType rt on rt.RaceType_id = pr.RaceType_id
			left join v_pmUser PU on PU.pmUser_id = coalesce(pr.pmUser_updID, pr.pmUser_insID)
			where ps.Person_id = :Person_id
		";
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * Проверка перед сохранением
	 * @throws Exception
	 */
	protected function _beforeValidate()
	{
		if ( empty( $this->getAttribute(self::ID_KEY) ) ) {
			$params = [
				'Person_id'=> $this->getAttribute('person_id')
			];
			$id = $this->isExistObjectRecord('PersonRace', $params);
			if($id) {
				throw new Exception('У пациента уже добавлена раса');
			}
		}
	}
}
