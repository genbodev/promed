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

class NephroAccess_model extends SwPgModel
{

    protected $dateTimeFormat104 = "'dd.mm.yyyy'";

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
				select {$fields}
				from
				    r2.{$viewName}
				    {$joins}
				where {$where}
				limit 1
			",
			'params' => $params,
		);
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	public static function defAttributes()
	{
		return array(
			self::ID_KEY => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL,
				],
				'alias' => 'NephroAccess_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			],
			'morbusnephro_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM,
				],
				'alias' => 'MorbusNephro_id',
				'label' => 'Заболевание',
				'save' => 'trim|required',
				'type' => 'id'
			],
			'nephroaccess_setdate' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'NephroAccess_setDate',
				'label' => 'Дата проведения комиссии',
				'save'  => 'trim|required',
				'type'  => 'date'
			],
			'nephroaccesstype_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM,
				],
				'alias' => 'NephroAccessType_id',
				'label' => 'Тип доступа',
				'save' => 'trim',
				'type' => 'id'
			],
			'insdt' => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				],
			],
			'pmuser_insid' => [
				'properties' => [
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				],
			],
			'upddt' => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				],
			],
			'pmuser_updid' => [
				'properties' => [
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				],
			]
		);
	}

    /**
     * Получение списка правил
     * @param string $name
     * @return array
     */
	public function getInputRules($name)
    {
		$rules = [];
		switch ($name) {
			case self::SCENARIO_DO_SAVE:
					$rules[self::ID_KEY] = [
						'field' => 'NephroAccess_id',
						'label' => 'Идентификатор записи',
						'rules' => '',
						'type' => 'int'
					];
					$rules['morbusnephro_id'] = [
						'field' => 'MorbusNephro_id',
						'label' => 'Идентификатор записи в регистре',
						'rules' => '',
						'type' => 'int'
					];
					$rules['nephroaccess_setdate'] = [
						'field' => 'NephroAccess_setDate',
						'label' => 'Дата проведения комиссии',
						'rules' => 'required',
						'type' => 'date'
					];
					$rules['nephroaccesstype_id'] = [
						'field' => 'NephroAccessType_id',
						'label' => 'Номер протокола',
						'rules' => 'required',
						'type' => 'id'
					];
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
					$rules[self::ID_KEY] = [
						'field' => 'NephroAccess_id',
						'label' => 'Идентификатор записи',
						'rules' => 'required',
						'type' => 'int'
					];
				break;
			}
		return $rules;
	}

    /**
     * Получение данных для панели просмотра
     * @param array @data
     * @return array
     */
	public function loadViewData($data)
	{
		$isOnlyLast = !empty($data['isOnlyLast']) ? ' limit 1 ' :  '';
		$params = array(
			'MorbusNephro_id' => $data['MorbusNephro_id'],
			'MorbusNephro_pid' => $data['MorbusNephro_pid']
		);

		$query = "
				select
					case when MN.Morbus_disDT is null then 'edit' else 'view' end as \"accessType\",
					NA.NephroAccess_id as \"NephroAccess_id\",
					to_char(NA.NephroAccess_setDate, {$this->dateTimeFormat104}) as \"NephroAccess_setDate\",
					NA.NephroAccessType_Name as \"NephroAccessType_Name\",
					:MorbusNephro_id as \"MorbusNephro_id\",
					:MorbusNephro_pid as \"MorbusNephro_pid\"
				from
				    r2.v_NephroAccess NA
				    left join v_MorbusNephro MN on MN.MorbusNephro_id = NA.MorbusNephro_id
				where
				    NA.MorbusNephro_id = :MorbusNephro_id
				order by 
					NA.NephroAccess_setDate desc
				$isOnlyLast
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result) )
			return [];

        return $result->result('array');
	}

    /**
     * Загрузка формы редактирования
     * @param array $data
     * @return bool
     */
	public function doLoadEditFormNephroAccess($data)
    {
		$params = [];
		$params['NephroAccess_id'] = $data['NephroAccess_id'];

		$query = "
			select 
				NephroAccess_id as \"NephroAccess_id\",
				MorbusNephro_id as \"MorbusNephro_id\",
				to_char(NephroAccess_setDate, {$this->dateTimeFormat104}) as \"NephroAccess_setDate\",
				NephroAccessType_id as \"NephroAccessType_id\",
				NephroAccessType_Code as \"NephroAccessType_Code\",
				NephroAccessType_Name as \"NephroAccessType_Name\"
			from 
				r2.v_NephroAccess
			where
				NephroAccess_id = :NephroAccess_id
		";

		$result = $this->db->query($query,$params);

		if(!is_object($result))
			return false;

        return $result->result('array');
	}

}