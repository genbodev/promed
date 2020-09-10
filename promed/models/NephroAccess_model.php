<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * NephroAccess_model - модель "Установка коммиссии МЗ"
 *
 * @package      NephroAccess
 * @access       public
 * @copyright    Copyright (c) Emsis.
 * @author       Salavat Magafurov
 * @version      07.2018
 */

class NephroAccess_model extends swModel
{

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_LOAD_EDIT_FORM
		));
	}

	/**
	 * @return string
	 */
	function getObjectSysNick()
	{
		return 'NephroAccess';
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'NephroAccess';
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
				select top 1 {$fields}
				from r2.{$viewName} with (nolock)
				{$joins}
				where {$where}
			",
			'params' => $params,
		);
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
				'alias' => 'NephroAccess_id',
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
			'nephroaccess_setdate' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'NephroAccess_setDate',
				'label' => 'Дата проведения комиссии',
				'save'  => 'trim|required',
				'type'  => 'date'
			),
			'nephroaccesstype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'NephroAccessType_id',
				'label' => 'Тип доступа',
				'save' => 'trim',
				'type' => 'id'
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
						'field' => 'NephroAccess_id',
						'label' => 'Идентификатор записи',
						'rules' => '',
						'type' => 'int'
					);
					$rules['morbusnephro_id'] = array(
						'field' => 'MorbusNephro_id',
						'label' => 'Идентификатор записи в регистре',
						'rules' => '',
						'type' => 'int'
					);
					$rules['nephroaccess_setdate'] = array(
						'field' => 'NephroAccess_setDate',
						'label' => 'Дата проведения комиссии',
						'rules' => 'required',
						'type' => 'date'
					);
					$rules['nephroaccesstype_id'] = array(
						'field' => 'NephroAccessType_id',
						'label' => 'Номер протокола',
						'rules' => 'required',
						'type' => 'id'
					);
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
					$rules[self::ID_KEY] = array(
						'field' => 'NephroAccess_id',
						'label' => 'Идентификатор записи',
						'rules' => 'required',
						'type' => 'int'
					);
				break;
			}
		return $rules;
	}

	/**
	 * Получение данных для панели просмотра
	 * @param array @data
	 */
	function loadViewData($data)
	{
		$isOnlyLast = !empty($data['isOnlyLast']) ? ' TOP 1 ' :  '';
		$params = array(
			'MorbusNephro_id' => $data['MorbusNephro_id'],
			'MorbusNephro_pid' => $data['MorbusNephro_pid']
		);

		$query = "
				select $isOnlyLast
					case when MN.Morbus_disDT is null then 'edit' else 'view' end as accessType,
					NA.NephroAccess_id,
					convert(varchar, NA.NephroAccess_setDate, 104) as NephroAccess_setDate,
					NA.NephroAccessType_Name,
					:MorbusNephro_id as MorbusNephro_id,
					:MorbusNephro_pid as MorbusNephro_pid
				from r2.v_NephroAccess NA with(nolock)
				left join v_MorbusNephro MN with(nolock) on MN.MorbusNephro_id = NA.MorbusNephro_id
				where NA.MorbusNephro_id = :MorbusNephro_id
				order by 
					NA.NephroAccess_setDate desc
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array();
		}
	}

	/**
	 * Загрузка формы редактирования
	 * @param array $data
	 */
	function doLoadEditFormNephroAccess($data) {

		$params = array();
		$params['NephroAccess_id'] = $data['NephroAccess_id'];

		$query = "
			select 
				NephroAccess_id,
				MorbusNephro_id,
				convert(varchar,NephroAccess_setDate,104) as NephroAccess_setDate,
				NephroAccessType_id,
				NephroAccessType_Code,
				NephroAccessType_Name
			from 
				r2.v_NephroAccess with(nolock)
			where
				NephroAccess_id = :NephroAccess_id
		";

		$result = $this->db->query($query,$params);

		if(is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

}