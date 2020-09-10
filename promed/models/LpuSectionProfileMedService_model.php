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
 * @author       Александр Пермяков
 * @version      10.2014
 *
 * @property-read int $MedService_id
 * @property-read int $LpuSectionProfile_id
 */
class LpuSectionProfileMedService_model extends swModel
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
				$rules[] = array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'required','type' => 'id');
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
				$rules[] = array('field' => 'LpuSectionProfileMedService_id','label' => 'Идентификатор','rules' => 'required','type' => 'id');
				break;
		}
		return $rules;
	}

	/**
	 * Читает для формы "Профиль консультирования" в структуре МО
	 */
	function loadEditForm($data)
	{
		$this->applyData($data);
		$this->setScenario(self::SCENARIO_LOAD_EDIT_FORM);
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
		$this->applyData($data);
		$this->setScenario(self::SCENARIO_LOAD_GRID);
		$this->_validate();
		$query = "
			select top 100
				p.LpuSectionProfileMedService_id,
				p.MedService_id,
				p.LpuSectionProfile_id,
				lsp.LpuSectionProfile_Name,
				convert(varchar(10),lsp.LpuSectionProfile_begDT,104) as LpuSectionProfileMedService_begDT,
				convert(varchar(10),lsp.LpuSectionProfile_endDT,104) as LpuSectionProfileMedService_endDT
			from
				v_LpuSectionProfileMedService p with (NOLOCK)
				inner join v_LpuSectionProfile lsp with (NOLOCK) on p.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			where
				p.MedService_id = :MedService_id
		";
		$result = $this->db->query($query, array(
			'MedService_id' => $this->MedService_id
		));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			throw new Exception('Не удалось загрузить список профилей службы');
		}
	}
}