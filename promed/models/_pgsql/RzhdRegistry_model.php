<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * @package	  RzhdRegistry
 * @author	  Salavat Magafurov
 * @version	  11 2017
 */

class RzhdRegistry_model extends swPgModel {

	/**
	 * Сценарий поиска
	 */
	const SEARCH_PROFILE = 'search_profile';

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_LOAD_EDIT_FORM,
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_VIEW_DATA,
			self::SCENARIO_DELETE
		));
	}

	/**
	 * Определение схемы БД
	 */
	function getScheme() {
		return 'r2';
	}

	/**
	 * @return string
	 */
	function getObjectSysNick()
	{
		return 'RzhdRegistry';
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'RzhdRegistry';
	}

	/**
	 * Определение имени представления данных объекта
	 * @return string
	 */
	protected function viewName()
	{
		return $this->getScheme().'.v_' . $this->tableName();
	}

	/**
	 * Определение имени хранимой процедуры для создания
	 * @return string
	 */
	protected function createProcedureName()
	{
		return $this->getScheme().'.p_' . $this->tableName() . '_ins';
	}

	/**
	 * Определение имени хранимой процедуры для обновления
	 * @return string
	 */
	protected function updateProcedureName()
	{
		return  $this->getScheme().'.p_' . $this->tableName() . '_upd';
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		return array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL
				),
				'alias' => 'RzhdRegistry_id',
				'label' => 'Идентификатор анкеты',
				'save' => 'trim',
				'type' => 'id'
			),
			'register_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL
				),
				'alias' => 'Register_id',
				'label' => 'Идентификатор записи в регистре',
				'save' => 'required',
				'type' => 'id'
			),
			'rzhdworkercategory_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL
				),
				'alias' => 'RzhdWorkerCategory_id',
				'label' => 'Категория населения',
				'save' => '',
				'type'=> 'id',
			),
			'rzhdworkergroup_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'RzhdWorkerGroup_id',
				'label' => 'Группа рабочего',
				'save' => '',
				'type'=> 'id',
			),
			'rzhdworkersubgroup_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'RzhdWorkerSubgroup_id',
				'label' => 'Подгруппа рабочего',
				'save' => '',
				'type' => 'id',
			),
			'rzhdregistry_pensionbegdate' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME
				),
				'alias' => 'RzhdRegistry_PensionBegDate',
				'label' => 'Дата начала пенсии',
				'save' => '',
				'type' => 'date'
			),
			'org_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'RzhdOrg_id',
				'label' => 'Организация РЖД',
				'save' => '',
				'type' => 'int',
			),
			//аттрибут для выборки данных
			'org_nick' => array(
				'properties' => array(
					self::PROPERTY_NOT_SAFE
				),
				'alias' => 'RzhdOrg_Nick',
				'label' => 'Организация РЖД',
				'save' => '',
				'type' => 'string',
				'select' => 'O.Org_Nick',
				'join' => 'left join v_Org on O.Org_id = {ViewName}.Org_id'
			)
		);
	}

	/**
	 * Получение данных регистра по id
	 */
	function loadEditForm($data) {
		$RzhdRegistry_id = $data['RzhdRegistry_id'];
		$this->_load($RzhdRegistry_id);
		$result = array();
		foreach ($this->defAttribute as $key => $info) {
			$value = $this->getAttribute($key);
			if($value  instanceof DateTime) {
				$value = $value->format('d.m.Y');
			}
			$result[$info['alias']] = $value;
		}
		return $result;
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);
		switch($name) {
			case self::SEARCH_PROFILE:
				$rules = array('register_id' => array(
					'field' => 'Register_id',
					'rules' => 'trim|required',
					'label' => 'Идентификатор в регистрe',
					'type' => 'id'
				));;
				break;
		}
		return $rules;
	}

	/**
	 * Логика перед валидацией
	 */
	protected function _beforeValidate() {
		if(!$this->getAttribute(self::ID_KEY)) {
			$params = [ 'Register_id' => $this->getAttribute('register_id') ];
			$_id = $this->isExistObjectRecord('RzhdRegistry', $params, 'r2');
			if($_id)
				$this->setAttribute(self::ID_KEY, $_id);
		}
	}
}