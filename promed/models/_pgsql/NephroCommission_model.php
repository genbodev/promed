<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * NephroCommission_model - модель "Установка коммиссии МЗ"
 *
 * @package      NephroCommission
 * @access       public
 * @copyright    Copyright (c) Emsis.
 * @author       Salavat Magafurov
 * @version      07.2018
 */

class NephroCommission_model extends SwPgModel
{
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
	 * @return string
	 */
	function getObjectSysNick()
	{
		return 'NephroCommission';
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'NephroCommission';
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
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL,
				),
				'alias' => 'NephroCommission_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			),
			'morbusnephro_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MorbusNephro_id',
				'label' => 'Заболевание',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'nephrocommission_date' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'NephroCommission_date',
				'label' => 'Дата проведения комиссии',
				'save'  => 'trim|required',
				'type'  => 'date'
			),
			'nephrocommission_protocolnumber' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'NephroCommission_protocolNumber',
				'label' => 'Номер протокола',
				'save' => 'trim',
				'type' => 'string'
			),
			'insdt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'pmuser_insid' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'upddt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'pmuser_updid' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			)
		);
	}

	/**
	 * Получение списка правил
	 * @param string $name
	 */
	function getInputRules($name) {
		$rules = array();
		switch ($name) {
			case self::SCENARIO_DO_SAVE:
					$rules[self::ID_KEY] = array(
						'field' => 'NephroCommission_id',
						'label' => 'Идентификатор записи',
						'rules' => '',
						'type' => 'int'
					);
					$rules['MorbusNephro_id'] = array(
						'field' => 'MorbusNephro_id',
						'label' => 'Идентификатор записи в регистре',
						'rules' => '',
						'type' => 'int'
					);
					$rules['NephroCommission_date'] = array(
						'field' => 'NephroCommission_date',
						'label' => 'Дата проведения комиссии',
						'rules' => 'required',
						'type' => 'date'
					);
					$rules['NephroCommission_protocolNumber'] = array(
						'field' => 'NephroCommission_protocolNumber',
						'label' => 'Номер протокола',
						'rules' => 'required',
						'type' => 'string'
					);
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
					$rules[self::ID_KEY] = array(
						'field' => 'NephroCommission_id',
						'label' => 'Идентификатор записи',
						'rules' => 'required',
						'type' => 'int'
					);
				break;
			}
		return $rules;
	}

	/**
	 * Определение имени хранимой процедуры для создания
	 * @return string
	 */
	protected function createProcedureName()
	{
		return 'r2.p_' . $this->tableName() . '_ins';
	}

	/**
	 * Определение имени хранимой процедуры для обновления
	 * @return string
	 */
	protected function updateProcedureName()
	{
		return 'r2.p_' . $this->tableName() . '_upd';
	}

	/**
	 * @param string $fields
	 * @param string $from
	 * @param string $joins
	 * @param string $where
	 * @param array $params
	 * @return array
	 */
	protected function _beforeQuerySavedData($fields, $viewName, $joins, $where, $params)
	{
		return array(
			'sql' => "
				select {$fields}
				from r2.{$viewName} 
				{$joins}
				where {$where}
				limit 1
			",
			'params' => $params,
		);
	}

	/**
	 * Получение данных для панели просмотра
	 * @param array $data
	 */
	function loadViewData($data)
	{
		$isOnlyLast = !empty($data['isOnlyLast']) ? ' Limit 1 ' :  '';
		$params = array(
			'MorbusNephro_id' => $data['MorbusNephro_id'],
			'MorbusNephro_pid' => $data['MorbusNephro_pid']
		);
		$query = "
				select 
					case when MN.Morbus_disDT is null then 'edit' else 'view' end as \"accessType\",
					NC.NephroCommission_id as \"NephroCommission_id\",
					to_char(NC.NephroCommission_date,'dd.mm.yyyy') as \"NephroCommission_date\",
					NC.NephroCommission_protocolNumber as \"NephroCommission_protocolNumber\",
					:MorbusNephro_id as \"MorbusNephro_id\",
					:MorbusNephro_pid as \"MorbusNephro_pid\"
				from r2.v_NephroCommission NC 
				left join v_MorbusNephro MN  on MN.MorbusNephro_id = NC.MorbusNephro_id
				where NC.MorbusNephro_id = :MorbusNephro_id
				order by 
					NC.NephroCommission_date desc
                $isOnlyLast
		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array();
		}
	}

	/**
	 * Загрузка данных для формы редактирования
	 * @param array $data
	 */
	function doLoadEditFormNephroCommission($data) {

		$params = array();
		$params['NephroCommission_id'] = $data['NephroCommission_id'];

		$query = "
			select 
				NephroCommission_id as \"NephroCommission_id\",
				MorbusNephro_id as \"MorbusNephro_id\",
				to_char(NephroCommission_date,'dd.mm.yyyy') as \"NephroCommission_date\",
				NephroCommission_protocolNumber as \"NephroCommission_protocolNumber\"
			from 
				r2.v_NephroCommission 
			where
				NephroCommission_id = :NephroCommission_id
		";

		$result = $this->db->query($query,$params);

		if(is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
		
	}
}