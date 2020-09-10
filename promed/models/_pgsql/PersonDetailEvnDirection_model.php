<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PersonDetailEvnDirection - Дополнительные сведения о паци
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
class PersonDetailEvnDirection_model extends swPgModel {
	static function defAttributes() {
		return [
			self::ID_KEY => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME
				],
				'alias' => 'PersonDetailEvnDirection_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'int'
			],
			'evndirection_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL
				],
				'alias' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'save' => 'trim|required',
				'type' => 'int'
			],
			'hivcontingenttypefrmis_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'HIVContingentTypeFRMIS_id',
				'label' => 'Идентификатор контингента',
				'save' => 'trim',
				'type' => 'int'
			],
			'hormonalphasetype_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'HormonalPhaseType_id',
				'label' => 'Идентификатор фазы цикла',
				'save' => 'trim',
				'type' => 'int'
			]
		];
	}
	public function __construct() {
		parent::__construct();
	}
	public function tableName() {
		return 'PersonDetailEvnDirection';
	}
	protected function _validate() {
		return true;
	}
	/**
	 * Получение записей по Person_id
	 * @param Array $params Параметры для запроса
	 * @return Array
	 * @throws Exception
	 */
	public function getOne($params) {
		$join = ($params['EvnDirection_id']) ?
			'left join v_PersonDetailEvnDirection edpd on edpd.PersonDetailEvnDirection_id = (select PersonDetailEvnDirection_id from v_PersonDetailEvnDirection where EvnDirection_id = :EvnDirection_id limit 1)' : '';
		$fields = ($params['EvnDirection_id']) ?
			'edpd.HIVContingentTypeFRMIS_id as "HIVContingentTypeFRMIS_id", edpd.CovidContingentType_id as "CovidContingentType_id", edpd.HormonalPhaseType_id as "HormonalPhaseType_id"' : 'null as "HIVContingentTypeFRMIS_id", null as "CovidContingentType_id", null as "HormonalPhaseType_id"';
		$query = "select
				ps.Person_id as \"Person_id\",
				ps.Sex_id as \"Sex_id\",
				pr.RaceType_id as \"RaceType_id\",
				case when pw.personweight_weight is not null 
					then pw.personweight_weight || ' ' || wo.Okei_NationSymbol
					else ''
				end as \"PersonWeight_WeightText\",
				ph.personheight_height as \"PersonHeight_Height\",
				to_char(ph.PersonHeight_setDT,'dd.mm.yyyy') as \"PersonHeight_setDT\",
				to_char(pw.PersonWeight_setDT,'dd.mm.yyyy') as \"PersonWeight_setDT\",
				{$fields}

			from v_PersonState ps
			{$join}
			left join (select * from v_PersonRace where Person_id = :Person_id order by PersonRace_setDT desc limit 1) pr on pr.Person_id = ps.Person_id
			left join (select * from v_PersonHeight where Person_id = :Person_id order by PersonHeight_setDT desc limit 1) ph on ph.Person_id = ps.Person_id
			left join (select * from v_PersonWeight where Person_id = :Person_id order by PersonWeight_setDT desc limit 1) pw on pw.Person_id = ps.Person_id
			left join v_Okei ho on ho.Okei_id = ph.Okei_id
			left join v_Okei wo on wo.Okei_id = pw.Okei_id
			where ps.Person_id = :Person_id
		";
		try {
			return $this->getFirstRowFromQuery($query, $params);
		} catch (Exception $e) {
			throw $e;
		}
	}
}
