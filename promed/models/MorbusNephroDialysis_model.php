<?php	defined('BASEPATH') or die ('No direct script access allowed');
require_once('MorbusNephroDialysis_model.php');
/**
 * MorbusNephroDialysis_model - модель "Нуждается в диализе" регистра по нефрологии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      11.2014
 */
class MorbusNephroDialysis_model extends swModel
{
	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_LOAD_EDIT_FORM,
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_VIEW_DATA,
			self::SCENARIO_DELETE,
		));
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusNephroDialysis';
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
				'alias' => 'MorbusNephroDialysis_id',
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
			'begdt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
				),
				'applyMethod'=>'_applyRateDT',
				'alias' => 'MorbusNephroDialysis_begDT',
				'label' => 'Дата включения в список',
				'save' => 'trim|required',
				'type' => 'date'
			),
			'enddt' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'MorbusNephroDialysis_endDT',
				'label' => 'Дата исключения из списк',
				'save' => '',
				'type' => 'date'
			),
			'personregisteroutcause_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'PersonRegisterOutCause_id',
				'label' => 'Причина',
				'save' => 'trim',
				'type' => 'id'
			),
			'lpu_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'Lpu_id',
				'label' => 'МО',
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
			),
		);
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();

		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE))) {
			if (!empty($this->endDT) && $this->endDT < $this->begDT) {
				throw new Exception("Внимание! Дата исключения из списка нуждающихся в проведении диализа не может быть раньше, чем Дата включения в список нуждающихся в диализе. Проверьте правильность введенных данных в полях: Дата включения и Дата исключения.");
			}

			$filter = "";
			$queryParams = array(
				'MorbusNephro_id' => $this->MorbusNephro_id,
				'MorbusNephroDialysis_begDT' => $this->begDT
			);
			if (!empty($this->id)) {
				$filter .= " and MorbusNephroDialysis_id <> :MorbusNephroDialysis_id";
				$queryParams['MorbusNephroDialysis_id'] = $this->id;
			}

			if (!empty($this->endDT)) {
				$checkFilter = "
					(MorbusNephroDialysis_begDT <= :MorbusNephroDialysis_begDT and ISNULL(MorbusNephroDialysis_endDT, :MorbusNephroDialysis_begDT) >= :MorbusNephroDialysis_begDT)
					OR
					(MorbusNephroDialysis_begDT <= :MorbusNephroDialysis_endDT and ISNULL(MorbusNephroDialysis_endDT, :MorbusNephroDialysis_endDT) >= :MorbusNephroDialysis_endDT)
				";
				$queryParams['MorbusNephroDialysis_endDT'] = $this->endDT;
			} else {
				$checkFilter = ":MorbusNephroDialysis_begDT <= ISNULL(MorbusNephroDialysis_endDT, :MorbusNephroDialysis_begDT)";
			}

			$resp_mnd = $this->queryResult("
				select top 1
					MorbusNephroDialysis_id
				from
					v_MorbusNephroDialysis (nolock)
				where
					MorbusNephro_id = :MorbusNephro_id
					and (
						{$checkFilter}
					)
					{$filter}
			", $queryParams);

			if (!empty($resp_mnd[0]['MorbusNephroDialysis_id'])) {
				throw new Exception("Внимание! Период включения в список нуждающихся в диализе пересекается с другим периодом данного регистра.");
			}
		}
	}

		/**
	 * Читает одну строку для формы редактирования
	 * @param array $data
	 * @return array
	 */
	function doLoadEditForm($data)
	{
		unset($data['Lpu_id']);
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		$this->applyData($data);
		$this->_validate();
		return array(array(
			'MorbusNephroDialysis_id' => $this->id,
			'MorbusNephro_id' => $this->MorbusNephro_id,
			'Lpu_id' => $this->Lpu_id,
			'MorbusNephroDialysis_begDT' => !empty($this->begDT)?$this->begDT->format('d.m.Y'):null,
			'MorbusNephroDialysis_endDT' => !empty($this->endDT)?$this->endDT->format('d.m.Y'):null,
			'PersonRegisterOutCause_id' => $this->PersonRegisterOutCause_id
		));
	}

	/**
	 *  Читает для грида и панели просмотра
	 */
	function doLoadGrid($data)
	{
		if (empty($data['scenario'])) {
			$data['scenario'] = self::SCENARIO_LOAD_GRID;
		}

		$this->applyData($data);
		$this->_validate();
		$queryParams = array(
			'MorbusNephro_id' => $this->MorbusNephro_id
		);

		$filters = 't.MorbusNephro_id = :MorbusNephro_id';
		$queryParams['Evn_id'] = isset($data['Evn_id']) ? $data['Evn_id'] : null;

		$sql = "
			select
				case when MV.Morbus_disDT is null then 'edit' else 'view' end as accessType,
				t.MorbusNephroDialysis_id,
				t.MorbusNephro_id,
				convert(varchar(10), t.MorbusNephroDialysis_begDT, 104) as MorbusNephroDialysis_begDT,
				convert(varchar(10), t.MorbusNephroDialysis_endDT, 104) as MorbusNephroDialysis_endDT,
				OutCause.PersonRegisterOutCause_Name,
				l.Lpu_Nick,
				:Evn_id as MorbusNephro_pid
			from
				v_MorbusNephroDialysis t (nolock)
				inner join v_MorbusNephro MV (nolock) on MV.MorbusNephro_id = t.MorbusNephro_id
				left join v_PersonRegisterOutCause OutCause (nolock) on OutCause.PersonRegisterOutCause_id = t.PersonRegisterOutCause_id
				left join v_Lpu l (nolock) on l.Lpu_id = t.Lpu_id
			where
				{$filters}
			order by
				t.MorbusNephroDialysis_begDT
		";
		// echo getDebugSql($sql, $queryParams);die();
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}
}