<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * MethodsIFA_model - модель "Методики ИФА"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat
 * @version      18.11.2019
 */

require_once('Scenario_model.php');
class MethodsIFA_model extends Scenario_model
{
	const SCENARIO_LOAD_FIRMS = 'loadFIRMS';
	const loadFilterCombo = 'loadFilterCombo';
	var $table_name = 'MethodsIFA';
	var $scheme = 'dbo';
	var $saveAsNewObject = true;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_LOAD_COMBO_BOX,
			self::SCENARIO_LOAD_FIRMS,
			self::loadFilterCombo
		));
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		return array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'Tablet_id',
				'label' => 'Идентификатор',
				'save' => '',
				'type' => 'id'
			),
			'medservice_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MedService_id',
				'label' => 'Лаборатория',
				'save' => '',
				'type' => 'int'
			),
			'analyzer_id' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'Analyzer_id',
				'label' => 'Анализатор',
				'type' => 'int'
			)
		);
	}

	/**
	 * Загрузка комбобокса
	 * @param array $data
	 * @return array|false
	 */
	function doLoadCombo($data = array())
	{
		$query = "
			SELECT
				MethodsIFA_id as \"MethodsIFA_id\",
				MethodsIFA_Code as \"MethodsIFA_Code\",
				MethodsIFA_Name as \"MethodsIFA_Name\",
				FIRMS_id as \"FIRMS_id\"
			FROM
				v_MethodsIFA
		";
		return $this->queryResult($query);
	}

	/**
	 * Получение правил для входящих параметров
	 * @param $name
	 * @return array
	 * @throws Exception
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);

		switch ($name) {
			case self::loadFilterCombo:
				$attributes = ['medservice_id', 'analyzer_id'];
				$rules = $this->getInputRulesByAttributes($attributes);
				break;
		}
		return $rules;
	}

	/**
	 * Загрузка справочника для фильтра в АРМе лаборанта ИФА
	 * @param $data
	 * @return array|false
	 */
	function loadFilterCombo($data) {
		$params = [];
		$where = [ "1=1" ];

		$params['MedService_id'] = $data['MedService_id'];
		$where[] = "A.MedService_id = :MedService_id";

		if(!empty($data['Analyzer_id'])) {
			$params['Analyzer_id'] = $data['Analyzer_id'];
			$where[] = "A.Analyzer_id = :Analyzer_id";
		}

		$where = implode($where, " and ");
		$query = "
			select distinct
				MI.MethodsIFA_id as \"MethodsIFA_id\",
				MI.MethodsIFA_Code as \"MethodsIFA_Code\",
				MI.MethodsIFA_Name as \"MethodsIFA_Name\"
			from v_MethodsIFAAnalyzerTest MIAT
			inner join v_MethodsIFA MI on MI.MethodsIFA_id = MIAT.MethodsIFA_id
			inner join lis.v_AnalyzerTest AnT on AnT.AnalyzerTest_id = MIAT.AnalyzerTest_id
			inner join lis.v_Analyzer A on A.Analyzer_id = AnT.Analyzer_id
			where $where
		";
		return $this->queryResult($query,$params);
	}

	/**
	 * Загрузка производителей методик ИФА
	 * @param array $data
	 * @return array|false
	 */
	function loadFIRMS($data = array()) {
		$query = "
			select DISTINCT
				F.FIRMS_id as \"FIRMS_id\",
				F.FULLNAME as \"FIRMS_Name\"
			from rls.v_FIRMS F
				inner join v_MethodsIFA MI on MI.FIRMS_ID = F.FIRMS_ID
		";
		return $this->queryResult($query);
	}
}
