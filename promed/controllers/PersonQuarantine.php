<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * PersonQuarantine - Контроллер для работы с таблицей "Данные о нахождении пациента на карантине"
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat
 * @version      11.03.20
 */
require_once('Scenario.php');

class PersonQuarantine extends Scenario
{

	var $model_name = "PersonQuarantine_model";
	var $fields = [
		'PersonQuarantine_id' => [
			'label' => 'Идентификатор',
			'type' => 'int'
		],
		'Person_id' => [
			'label' => 'Идентификатор пациента',
			'rules' => 'required',
			'type' => 'int'
		],
		'PersonQuarantine_begDT' => [
			'label' => 'Дата создания контрольной карты',
			'rules' => 'required',
			'type' => 'date'
		],
		'PersonQuarantine_endDT' => [
			'label' => 'Дата закрытия контрольной карты',
			'type' => 'date'
		],
		'PersonQuarantineOpenReason_id' => [
			'label' => 'Причина открытия контрольной карты',
			'type' => 'int'
		],
		'PersonQuarantineCloseReason_id' => [
			'label' => 'Причина закрытия контрольной карты',
			'type' => 'int'
		],
		'PersonQuarantine_approveDT' => [
			'label' => '',
			'type' => 'date'
		],
		'MedStaffFact_id' => [
			'label' => '',
			'type' => 'int'
		],
		'RepositoryObesrv_contactDate' => [
			'label' => 'Дата контакта',
			'type' => 'date'
		],
		'RepositoryObserv_arrivalDate' => [
			'label' => 'Дата контакта',
			'type' => 'date'
		],
		'PlaceArrival_id' => [
			'label' => 'Место прибытия',
			'type' => 'int'
		],
		'KLCountry_id' => [
			'label' => 'Страна прибытия',
			'type' => 'int'
		],
		'KLRgn_id' => [
			'label' => 'Регион прибытия',
			'type' => 'int'
		],
		'TransportMeans_id' => [
			'label' => 'Средство передвижения при въезде в РФ',
			'type' => 'int'
		],
		'RepositoryObserv_TransportDesc' => [
			'label' => 'Средство передвижения при въезде в РФ (детально)',
			'type' => 'string'
		],
		'RepositoryObserv_TransportPlace' => [
			'label' => 'Место въезда на территорию РФ',
			'type' => 'string'
		],
		'RepositoryObserv_TransportRoute' => [
			'label' => 'Маршрут передвижения по РФ.',
			'type' => 'string'
		],
		'RepositoryObserv_FlightNumber' => [
			'label' => 'Рейс',
			'type' => 'string'
		]
	];

	/**
	 * Описание входящих параметров
	 * @param string $name
	 * @return array|bool|null
	 * @throws Exception
	 */
	function getInputRulesByName($name = '') {
		switch ($name) {
			case Scenario_model::SCENARIO_LOAD_EDIT_FORM:
				return [ $this->getRuleByFieldName('PersonQuarantine_id', 'required') ];
			case Scenario_model::SCENARIO_DO_SAVE:
				return $this->getFieldsRules();
			case PersonQuarantine_model::GET_LAST_OPENED_QUARANTINE_CARD:
			case Scenario_model::SCENARIO_LOAD_GRID:
				return [ $this->getRuleByFieldName('Person_id', 'required') ];
			default:
				return null;
		}
	}

	/**
	 * Получение последней открытой карты карантина
	 */
	function getLastOpenedQuarantineCard() {
		$this->doScenarioLoad(PersonQuarantine_model::GET_LAST_OPENED_QUARANTINE_CARD);
	}
}