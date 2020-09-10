<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

/**
 * LpuSectionProfileMedService_model - Модель "Профили консультирования"
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Valery Bondarev
 * @version      11.12.2019
 *
 * @property-read int $MedService_id
 * @property-read int $LpuSectionProfile_id
 */
class LpuSectionProfileMedService_model extends swPgModel
{
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
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
				'alias' => 'LpuSectionProfileMedService_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			),
			'medservice_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MedService_id',
				'label' => 'Служба',
				'save' => 'required',
				'type' => 'id'
			),
			'lpusectionprofile_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'save' => 'required',
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
			),
		);
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	protected function tableName()
	{
		return 'LpuSectionProfileMedService';
	}

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_LOAD_EDIT_FORM,
		));
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (empty($this->MedService_id)
			&& in_array($this->scenario, array(
				self::SCENARIO_LOAD_GRID,
				self::SCENARIO_LOAD_EDIT_FORM,
				self::SCENARIO_DO_SAVE,
				self::SCENARIO_AUTO_CREATE
			))
		) {
			throw new Exception('Не указана служба');
		}
		if (empty($this->LpuSectionProfile_id)
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))
		) {
			throw new Exception('Не указан профиль');
		}
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);
		switch ($name) {
			case self::SCENARIO_LOAD_GRID:
				$rules[] = array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => 'required',
					'type' => 'id'
				);
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
				$rules[] = array(
					'field' => 'LpuSectionProfileMedService_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Читает для формы "Профиль консультирования" в структуре МО
	 */
	function loadEditForm($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		$this->applyData($data);
		$this->_validate();
		return array(array(
			'LpuSectionProfileMedService_id' => $this->id,
			'MedService_id' => $this->MedService_id,
			'LpuSectionProfile_id' => $this->LpuSectionProfile_id,
		));
	}

	/**
	 * Читает для табгрида "Профили консультирования" в структуре МО
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function loadGrid($data)
	{
		if (empty($data['scenario'])) {
			$data['scenario'] = self::SCENARIO_LOAD_GRID;
		}
		$this->applyData($data);
		$this->_validate();
		$queryParams = array(
			'MedService_id' => $this->MedService_id
		);

		$filters = 'p.MedService_id = :MedService_id';

		$query = "
			select
				p.LpuSectionProfileMedService_id as \"LpuSectionProfileMedService_id\",
				p.MedService_id as \"MedService_id\",
				p.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				to_char(lsp.LpuSectionProfile_begDT,'dd.mm.yyyy') as \"LpuSectionProfileMedService_begDT\",
				to_char(lsp.LpuSectionProfile_endDT,'dd.mm.yyyy') as \"LpuSectionProfileMedService_endDT\"
			from
				v_LpuSectionProfileMedService p
				inner join v_LpuSectionProfile lsp on p.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			where {$filters}
			order by lsp.LpuSectionProfile_begDT
			limit 100
		";
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			throw new Exception('Не удалось загрузить список профилей службы');
		}
	}
}