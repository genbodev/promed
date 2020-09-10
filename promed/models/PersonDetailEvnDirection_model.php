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
class PersonDetailEvnDirection_model extends swModel {
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
				'label' => 'Код контингента ВИЧ',
				'save' => 'trim',
				'type' => 'int'
			],
			'covidcontingenttype_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'CovidContingentType_id',
				'label' => 'Код контингента COVID',
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
			'left join v_PersonDetailEvnDirection (nolock) edpd on edpd.PersonDetailEvnDirection_id = (select top 1 PersonDetailEvnDirection_id from v_PersonDetailEvnDirection (nolock) where EvnDirection_id = :EvnDirection_id)' : '';
		$fields = ($params['EvnDirection_id']) ?
			'edpd.PersonDetailEvnDirection_id, edpd.CovidContingentType_id, edpd.pmUser_insID, edpd.HIVContingentTypeFRMIS_id, edpd.HormonalPhaseType_id' : 'null as HIVContingentTypeFRMIS_id, null as CovidContingentType_id, null as HormonalPhaseType_id';
		$query = "select
				ps.Person_id,
				ps.Sex_id,
				pr.RaceType_id,
				substring(
       			    convert(varchar, ph.PersonHeight_Height), 0, (
       			        datalength(convert(varchar, ph.PersonHeight_Height)) - 1
					)
				) as PersonHeight_Height,
				ph.PersonHeight_setDT,
				IIF(pw.PersonWeight_Weight is not null, concat(substring(
       			    convert(varchar, pw.PersonWeight_Weight), 0, (
       			        datalength(convert(varchar, pw.PersonWeight_Weight)) - 1
					)
				), ' ', wo.Okei_NationSymbol), null) as PersonWeight_WeightText,
				pw.PersonWeight_setDT,
				{$fields}

			from v_PersonState (nolock) ps
			{$join}
			left join (select top 1 * from v_PersonRace (nolock) where Person_id = :Person_id order by PersonRace_setDT desc) pr on pr.Person_id = ps.Person_id
			left join (select top 1 * from v_PersonHeight (nolock) where Person_id = :Person_id order by PersonHeight_setDT desc) ph on ph.Person_id = ps.Person_id
			left join (select top 1 * from v_PersonWeight (nolock) where Person_id = :Person_id order by PersonWeight_setDT desc) pw on pw.Person_id = ps.Person_id
			left join v_Okei (nolock) ho on ho.Okei_id = ph.Okei_id
			left join v_Okei (nolock) wo on wo.Okei_id = pw.Okei_id
			where ps.Person_id = :Person_id
		";
		try {
			return $this->getFirstRowFromQuery($query, $params);
		} catch (Exception $e) {
			throw $e;
		}
	}
}
