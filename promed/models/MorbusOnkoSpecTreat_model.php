<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		31.10.2014
 */

/**
 * Специфика (онкология). Специальное лечение
 *
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2015
 *
 * @property int $MorbusOnko_id
 * @property DateTime $specSetDT дата начала
 * @property DateTime $specDisDT дата окончания
 * @property int $TumorPrimaryTreatType_id проведенное лечение первичной опухоли
 * @property int $TumorRadicalTreatIncomplType_id причины незавершенности радикального лечения
 * @property int $OnkoCombiTreatType_id сочетание видов лечения
 * @property int $OnkoLateComplTreatType_id позднее осложнение специального лечения
 */
class MorbusOnkoSpecTreat_model extends swModel
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_VIEW_DATA,
			self::SCENARIO_LOAD_EDIT_FORM,
			self::SCENARIO_DELETE,
			self::SCENARIO_DO_SAVE,
			'doLoadDisabledDates',
		));
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusOnkoSpecTreat';
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
				'alias' => 'MorbusOnkoSpecTreat_id',
				'label' => 'Идентификатор',
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
			'morbusonko_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MorbusOnko_id',
				'label' => 'Заболевание',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'morbusonkoleave_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MorbusOnkoLeave_id',
				'label' => 'Специфика',
				'save' => '',
				'type' => 'id'
			),
			'morbusonkovizitpldop_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MorbusOnkoVizitPLDop_id',
				'label' => 'Специфика',
				'save' => '',
				'type' => 'id'
			),
			'morbusonkodiagplstom_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MorbusOnkoDiagPLStom_id',
				'label' => 'Специфика',
				'save' => '',
				'type' => 'id'
			),
            'morbusonkospectreat_id' => array(
                'properties' => array(
                    self::PROPERTY_READ_ONLY,
                ),
                'alias' => 'MorbusOnkoSpecTreat_id',
                'label' => 'MorbusOnkoSpecTreat_id',
                'save' => '',
                'type' => 'id'
            ),
			'specsetdt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MorbusOnkoSpecTreat_specSetDT',
				'label' => 'Дата начала специального лечения',
				'applyMethod'=>'_applySpecSetDate',
				'save' => 'trim|required',
				'type' => 'date'
			),
			'specdisdt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MorbusOnkoSpecTreat_specDisDT',
				'label' => 'Дата окончания специального лечения',
				'applyMethod'=>'_applySpecDisDate',
				'save' => 'trim',
				'type' => 'date'
			),
			'tumorprimarytreattype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'TumorPrimaryTreatType_id',
				'label' => 'Проведенное лечение первичной опухоли',
				'save' => 'trim',
				'type' => 'id'
			),
			'tumorradicaltreatincompltype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'TumorRadicalTreatIncomplType_id',
				'label' => 'Причины незавершенности радикального лечения',
				'save' => 'trim',
				'type' => 'id'
			),
			'onkocombitreattype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'OnkoCombiTreatType_id',
				'label' => 'Сочетание видов лечения',
				'save' => 'trim',
				'type' => 'id'
			),
			'onkolatecompltreattype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'OnkoLateComplTreatType_id',
				'label' => 'Позднее осложнение лечения',
				'save' => 'trim',
				'type' => 'id'
			),
			'onkospectreattype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'OnkoSpecTreatType_id',
				'label' => 'Тип лечения',
				'save' => 'trim',
				'type' => 'id'
			),
			'onkospecplacetype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'OnkoSpecPlaceType_id',
				'label' => 'Место проведения',
				'save' => 'trim',
				'type' => 'id'
			),
			'spectreathepb_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'SpecTreatHepB_id',
				'label' => 'Наличие гепатита В',
				'save' => 'trim',
				'type' => 'id'
			),
			'spectreathepc_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'SpecTreatHepC_id',
				'label' => 'Наличие гепатита C',
				'save' => 'trim',
				'type' => 'id'
			),
		);
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applySpecSetDate($data)
	{
		return $this->_applyDate($data, 'specsetdt');
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applySpecDisDate($data)
	{
		return $this->_applyDate($data, 'specdisdt');
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
			case swModel::SCENARIO_VIEW_DATA:
				$rules = array(
					'Evn_id' => array(
						'field' => 'Evn_id',
						'label' => 'Документ',
						'rules' => 'trim',
						'type' => 'id'
					),
					'Morbus_id' => array(
						'field' => 'Morbus_id',
						'label' => 'Заболевание',
						'rules' => 'trim',
						'type' => 'id'
					),
					'MorbusOnkoVizitPLDop_id' => array(
						'field' => 'MorbusOnkoVizitPLDop_id',
						'label' => 'Специфика',
						'rules' => 'trim',
						'type' => 'id'
					),
					'MorbusOnkoLeave_id' => array(
						'field' => 'MorbusOnkoLeave_id',
						'label' => 'Специфика',
						'rules' => 'trim',
						'type' => 'id'
					),
					'MorbusOnkoDiagPLStom_id' => array(
						'field' => 'MorbusOnkoDiagPLStom_id',
						'label' => 'Специфика',
						'rules' => 'trim',
						'type' => 'id'
					),
				);
				break;
			case 'doLoadDisabledDates':
				$rules = array(
					'MorbusOnko_id' => array(
						'field' => 'MorbusOnko_id',
						'label' => 'Заболевание',
						'rules' => 'trim',
						'type' => 'id'
					),
					'Morbus_id' => array(
						'field' => 'Morbus_id',
						'label' => 'Заболевание',
						'rules' => 'trim',
						'type' => 'id'
					),
					'MorbusOnkoVizitPLDop_id' => array(
						'field' => 'MorbusOnkoVizitPLDop_id',
						'label' => 'Специфика',
						'rules' => 'trim',
						'type' => 'id'
					),
					'MorbusOnkoLeave_id' => array(
						'field' => 'MorbusOnkoLeave_id',
						'label' => 'Специфика',
						'rules' => 'trim',
						'type' => 'id'
					),
					'MorbusOnkoDiagPLStom_id' => array(
						'field' => 'MorbusOnkoDiagPLStom_id',
						'label' => 'Специфика',
						'rules' => 'trim',
						'type' => 'id'
					),
				);
				break;
			case swModel::SCENARIO_DO_SAVE:
				$rules[] = array(
					'field' => 'lateCompls',
					'label' => 'осложнения',
					'rules' => 'trim',
					'type' => 'string'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (self::SCENARIO_VIEW_DATA == $this->scenario) {
			if ( empty($this->_params['Morbus_id']) || empty($this->_params['Evn_id'])) {
				throw new Exception('Не указаны обязательные параметры для получения списка объектов специального лечения', 500);
			}
		}
		if ( 'doLoadDisabledDates' == $this->scenario 
			&& empty($this->MorbusOnko_id)
			&& empty($this->MorbusOnkoVizitPLDop_id)
			&& empty($this->MorbusOnkoLeave_id)
			&& empty($this->MorbusOnkoDiagPLStom_id)
			&& empty($this->_params['Morbus_id'])
		) {
			throw new Exception('Не указано заболевание', 500);
		}
		if (in_array($this->scenario, array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
		))) {
			if ( empty($this->MorbusOnko_id) && empty($this->MorbusOnkoVizitPLDop_id) && empty($this->MorbusOnkoLeave_id) && empty($this->MorbusOnkoDiagPLStom_id) ) {
				throw new Exception('Не указано заболевание', 500);
			}
			if ( empty($this->specSetDT) || !($this->specSetDT instanceof DateTime) ) {
				throw new Exception('Не указана дата начала специального лечения', 400);
			}
			if ( !empty($this->specDisDT) && !($this->specDisDT instanceof DateTime) ) {
				throw new Exception('Не указана дата окончания специального лечения', 500);
			}
			if ( $this->specSetDT->format('Y-m-d') > date('Y-m-d')) {
				throw new Exception('Дата начала специального лечения не должна быть больше текущей даты', 400);
			}
			if ( !empty($this->specDisDT) && $this->specSetDT->format('Y-m-d') > $this->specDisDT->format('Y-m-d')) {
				throw new Exception('Дата начала специального лечения должна быть меньше или равна дате окончания', 400);
			}

			if ( !empty($this->MorbusOnkoVizitPLDop_id) ) {
				$Evn_id = $this->getFirstResultFromQuery("
					select top 1 EvnVizit_id
					from v_MorbusOnkoVizitPLDop with(nolock)
					where MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id
				", array(
					'MorbusOnkoVizitPLDop_id' => $this->MorbusOnkoVizitPLDop_id
				));
			}
			else if ( !empty($this->MorbusOnkoLeave_id) ) {
				$Evn_id = $this->getFirstResultFromQuery("
					select top 1 EvnSection_id
					from v_MorbusOnkoLeave with(nolock)
					where MorbusOnkoLeave_id = :MorbusOnkoLeave_id
				", array(
					'MorbusOnkoLeave_id' => $this->MorbusOnkoLeave_id
				));
			}
			else if ( !empty($this->MorbusOnkoDiagPLStom_id) ) {
				$Evn_id = $this->getFirstResultFromQuery("
					select top 1 EvnDiagPLStom_id
					from v_MorbusOnkoDiagPLStom with(nolock)
					where MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id
				", array(
					'MorbusOnkoDiagPLStom_id' => $this->MorbusOnkoDiagPLStom_id
				));
			}
			else {
				$Evn_id = $this->getFirstResultFromQuery("
					select top 1 Evn_pid
					from v_MorbusOnko mo with(nolock)
					where mo.MorbusOnko_id = :MorbusOnko_id
				", array(
					'MorbusOnko_id' => $this->MorbusOnko_id
				));
			}

			if (!empty($Evn_id)) {
				$this->load->model('MorbusOnkoSpecifics_model');
				$check = $this->MorbusOnkoSpecifics_model->checkDatesBeforeSave(array(
					'Evn_id' => $Evn_id,
					'dateOnko' => $this->specSetDT->format('Y-m-d')
				));
				if (!empty($check['Err_Msg'])) {
					throw new Exception($check['Err_Msg']);
				}
			}

			// контроль на непересечение периодов специального лечения.
            //var_dump($this->MorbusOnkoSpecTreat_id);die;
            //morbusonkospectreat_id
			$filter = '';
			if (!empty($this->MorbusOnkoVizitPLDop_id)) {
				$filter .= ' and MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id ';
			} elseif (!empty($this->MorbusOnkoLeave_id)) {
				$filter .= ' and MorbusOnkoLeave_id = :MorbusOnkoLeave_id ';
			} elseif (!empty($this->MorbusOnkoDiagPLStom_id)) {
				$filter .= ' and MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id ';
			}
			$params = array(
				'MorbusOnko_id' => $this->MorbusOnko_id,
				'MorbusOnkoVizitPLDop_id' => $this->MorbusOnkoVizitPLDop_id,
				'MorbusOnkoLeave_id' => $this->MorbusOnkoLeave_id,
				'MorbusOnkoDiagPLStom_id' => $this->MorbusOnkoDiagPLStom_id,
				'specSetDT' => $this->specSetDT->format('Y-m-d'),
                'MorbusOnkoSpecTreat_id' => !empty($this->MorbusOnkoSpecTreat_id) ? $this->MorbusOnkoSpecTreat_id : 0
			);
			$query = "
				with SpecTreat as (
					select
					MorbusOnko_id,
					convert(varchar(10),MorbusOnkoSpecTreat_specSetDT,120) as MorbusOnkoSpecTreat_specSetDT,
					convert(varchar(10),MorbusOnkoSpecTreat_specDisDT,120) as MorbusOnkoSpecTreat_specDisDT
					from v_MorbusOnkoSpecTreat with (nolock)
					WHERE MorbusOnko_id = :MorbusOnko_id
					AND MorbusOnkoSpecTreat_id <> :MorbusOnkoSpecTreat_id
					{$filter}
				)
				
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				FROM SpecTreat
				WHERE MorbusOnkoSpecTreat_specDisDT is null
					/* Дата начала нового должна быть всегда меньше даты начала старого */
					AND :specSetDT >= MorbusOnkoSpecTreat_specSetDT
				union all
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				FROM SpecTreat
				WHERE MorbusOnkoSpecTreat_specDisDT is not null
					/* Дата начала нового должна быть вне существующего периода */
					AND :specSetDT >= MorbusOnkoSpecTreat_specSetDT
					AND :specSetDT <= MorbusOnkoSpecTreat_specDisDT
			";
			if (!empty($this->specDisDT)) {
				$params['specDisDT'] = $this->specDisDT->format('Y-m-d');
				$query .= "
				union all
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				FROM SpecTreat
				WHERE MorbusOnkoSpecTreat_specDisDT is null
					/* Дата окончания нового должна быть всегда меньше даты начала старого */
					AND :specDisDT >= MorbusOnkoSpecTreat_specSetDT
				union all
				SELECT  MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				FROM SpecTreat
				WHERE MorbusOnkoSpecTreat_specDisDT is not null
					/* Дата окончания нового должна быть вне существующего периода */
					AND :specDisDT >= MorbusOnkoSpecTreat_specSetDT
					AND :specDisDT <= MorbusOnkoSpecTreat_specDisDT
				union all
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				FROM SpecTreat
				WHERE MorbusOnkoSpecTreat_specDisDT is not null
					/* существующий период не должен находиться внутри нового */
					AND
					(
						(MorbusOnkoSpecTreat_specSetDT >= :specSetDT AND MorbusOnkoSpecTreat_specDisDT <= :specDisDT)
						OR
						((:specSetDT between MorbusOnkoSpecTreat_specSetDT and MorbusOnkoSpecTreat_specDisDT) AND MorbusOnkoSpecTreat_specDisDT <= :specDisDT)
						OR
						(MorbusOnkoSpecTreat_specSetDT >= :specSetDT AND (:specDisDT between MorbusOnkoSpecTreat_specSetDT and MorbusOnkoSpecTreat_specDisDT))
					)
				";
			} else {
				$query = "
				with SpecTreat as (
					select
					MorbusOnko_id,
					convert(varchar(10),MorbusOnkoSpecTreat_specSetDT,120) as MorbusOnkoSpecTreat_specSetDT,
					convert(varchar(10),MorbusOnkoSpecTreat_specDisDT,120) as MorbusOnkoSpecTreat_specDisDT
					from v_MorbusOnkoSpecTreat with (nolock)
					WHERE MorbusOnko_id = :MorbusOnko_id
					AND MorbusOnkoSpecTreat_id <> :MorbusOnkoSpecTreat_id
					{$filter}
				)
				
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				FROM SpecTreat
				WHERE MorbusOnkoSpecTreat_specDisDT is not null
					/* Дата начала нового должна быть больше даты окончания периодов */
					AND :specSetDT <= MorbusOnkoSpecTreat_specDisDT
			";
			}
            //echo getDebugSQL($query, $params);die;
			$tmp = $this->getFirstRowFromQuery($query, $params);
			if (!empty($tmp)) {
				throw new Exception('Периоды проведенного специального лечения не должны пересекаться', 400);
			}
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 *
	 * Если сохранение выполняется внутри транзакции,
	 * то при запросах данных этого объекта из БД будут возвращены старые данные!
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		parent::_afterSave($result);
		if(!empty($result[0]['MorbusOnkoSpecTreat_id'])){
			$compls = $this->loadOnkoLateComplTreatTypeList($result[0]);
			if(!empty($compls[0]['MorbusOnkoSpecTreatLink_id'])){
				$res = $this->deleteOnkoLateComplTreatTypeList($compls);
				if(!empty($res[0]['Error_Msg'])){
					throw new Exception($res[0]['Error_Msg']);
				}
			}
			$compls = $this->saveOnkoLateComplTreatTypeList($result[0]);
			if(!empty($compls['Error_Msg'])){
				throw new Exception($compls['Error_Msg']);
			}
		}
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 *
	 * Перед вызовом должен быть указан сценарий
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		if (self::SCENARIO_VIEW_DATA == $this->scenario) {
			$this->_params['Morbus_id'] = empty($data['Morbus_id']) ? null : $data['Morbus_id'];
			$this->_params['MorbusOnkoVizitPLDop_id'] = empty($data['MorbusOnkoVizitPLDop_id']) ? null : $data['MorbusOnkoVizitPLDop_id'];
			$this->_params['MorbusOnkoLeave_id'] = empty($data['MorbusOnkoLeave_id']) ? null : $data['MorbusOnkoLeave_id'];
			$this->_params['MorbusOnkoDiagPLStom_id'] = empty($data['MorbusOnkoDiagPLStom_id']) ? null : $data['MorbusOnkoDiagPLStom_id'];
			$this->_params['Evn_id'] = empty($data['Evn_id']) ? null : $data['Evn_id'];
		}
		if ('doLoadDisabledDates' == $this->scenario) {
			$this->_params['Morbus_id'] = empty($data['Morbus_id']) ? null : $data['Morbus_id'];
			$this->_params['MorbusOnkoVizitPLDop_id'] = empty($data['MorbusOnkoVizitPLDop_id']) ? null : $data['MorbusOnkoVizitPLDop_id'];
			$this->_params['MorbusOnkoLeave_id'] = empty($data['MorbusOnkoLeave_id']) ? null : $data['MorbusOnkoLeave_id'];
			$this->_params['MorbusOnkoDiagPLStom_id'] = empty($data['MorbusOnkoDiagPLStom_id']) ? null : $data['MorbusOnkoDiagPLStom_id'];
		}
		if (swModel::SCENARIO_DO_SAVE == $this->scenario) {
			$this->_params['lateCompls'] = empty($data['lateCompls']) ? null : $data['lateCompls'];
			$this->_params['pmUser_id'] = empty($data['pmUser_id']) ? null : $data['pmUser_id'];
		}
	}

	/**
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	function getViewData($data)
	{
		$this->setScenario(self::SCENARIO_VIEW_DATA);
		$this->setParams($data);
		$this->_validate();
		if (!empty($data['MorbusOnkoVizitPLDop_id'])) {
			$filter = 'MOST.MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id';
		} elseif (!empty($data['MorbusOnkoLeave_id'])) {
			$filter = 'MOST.MorbusOnkoLeave_id = :MorbusOnkoLeave_id';
		} elseif (!empty($data['MorbusOnkoDiagPLStom_id'])) {
			$filter = 'MOST.MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id';
		} elseif (!empty($data['Morbus_id'])) {
			$filter = 'Morbus.Morbus_id = :Morbus_id';
		}
		if (isset($data['accessType']) && $data['accessType'] == 'view') {
			$accessType = " 'view' as accessType ";
		} else {
			$accessType = " 
				case
					when 1=1 then 'edit'
					else 'view'
				end as accessType 
			";
		}
		$query = "
			SELECT
				{$accessType}
				,MOST.MorbusOnkoSpecTreat_id
				,MO.MorbusOnko_id
				,convert(varchar(10), MOST.MorbusOnkoSpecTreat_specSetDT, 104) as MorbusOnkoSpecTreat_specSetDT
				,convert(varchar(10), MOST.MorbusOnkoSpecTreat_specDisDT, 104) as MorbusOnkoSpecTreat_specDisDT
				,TPTT.TumorPrimaryTreatType_Name as TumorPrimaryTreatType_id_Name
				,OCTT.OnkoCombiTreatType_Name as OnkoCombiTreatType_id_Name
				,OSTT.OnkoSpecTreatType_Name as OnkoSpecTreatType_id_Name
				,TRTIT.TumorRadicalTreatIncomplType_Name as TumorRadicalTreatIncomplType_id_Name
				,:Evn_id as MorbusOnko_pid
				,Morbus.Morbus_id
				,MOST.MorbusOnkoVizitPLDop_id
				,MOST.MorbusOnkoLeave_id
				,MOST.MorbusOnkoDiagPLStom_id
			FROM
				dbo.v_Morbus Morbus WITH (NOLOCK)
				INNER JOIN dbo.v_MorbusOnko MO WITH (NOLOCK) on Morbus.Morbus_id = MO.Morbus_id
				INNER JOIN dbo.v_MorbusOnkoSpecTreat MOST WITH (NOLOCK) on MO.MorbusOnko_id = MOST.MorbusOnko_id
				left join dbo.v_TumorPrimaryTreatType TPTT WITH (NOLOCK) ON MOST.TumorPrimaryTreatType_id = TPTT.TumorPrimaryTreatType_id
				left join dbo.v_OnkoCombiTreatType OCTT WITH (NOLOCK) ON MOST.OnkoCombiTreatType_id = OCTT.OnkoCombiTreatType_id
				left join dbo.v_OnkoSpecTreatType OSTT WITH (NOLOCK) ON MOST.OnkoSpecTreatType_id = OSTT.OnkoSpecTreatType_id
				left join dbo.v_TumorRadicalTreatIncomplType TRTIT WITH (NOLOCK) ON MOST.TumorRadicalTreatIncomplType_id = TRTIT.TumorRadicalTreatIncomplType_id
			where
				{$filter}
		";
		$params = array(
			'Morbus_id' => $this->_params['Morbus_id'],
			'MorbusOnkoVizitPLDop_id' => $this->_params['MorbusOnkoVizitPLDop_id'],
			'MorbusOnkoLeave_id' => $this->_params['MorbusOnkoLeave_id'],
			'MorbusOnkoDiagPLStom_id' => $this->_params['MorbusOnkoDiagPLStom_id'],
			'Evn_id' => $this->_params['Evn_id'],
		);
		
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных для формы
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function doLoadEditForm($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		$this->applyData($data);
		$this->_validate();
		$compls = $this->loadOnkoLateComplTreatTypeList(array($this->tableName() . '_id' => $this->id));
		return array(array(
			$this->tableName() . '_id' => $this->id,
			'MorbusOnko_id' => $this->MorbusOnko_id,
			'MorbusOnkoLeave_id' => $this->MorbusOnkoLeave_id,
			'MorbusOnkoVizitPLDop_id' => $this->MorbusOnkoVizitPLDop_id,
			'MorbusOnkoDiagPLStom_id' => $this->MorbusOnkoDiagPLStom_id,
			$this->tableName() . '_specSetDT' => $this->specSetDT->format('d.m.Y'),
			$this->tableName() . '_specDisDT' => ($this->specDisDT instanceof DateTime) ? $this->specDisDT->format('d.m.Y') : null,
			'TumorPrimaryTreatType_id' => $this->TumorPrimaryTreatType_id,
			'TumorRadicalTreatIncomplType_id' => $this->TumorRadicalTreatIncomplType_id,
			'OnkoCombiTreatType_id' => $this->OnkoCombiTreatType_id,
			'OnkoLateComplTreatType_id' => $this->OnkoLateComplTreatType_id,
			'disabledDates' => $this->loadDisabledDates($this->MorbusOnko_id, $this->id),
			'lateCompls' => $compls,
			'minDate' => null,
			'maxDate' => $this->currentDT->format('d.m.Y'),
			'OnkoSpecTreatType_id' => $this->OnkoSpecTreatType_id,
			'OnkoSpecPlaceType_id' => $this->OnkoSpecPlaceType_id,
			'SpecTreatHepB_id' => $this->SpecTreatHepB_id,
			'SpecTreatHepC_id' => $this->SpecTreatHepC_id,
		));
	}

	/**
	 * Получение данных для формы
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function doLoadDisabledDates($data)
	{
		$data['scenario'] = 'doLoadDisabledDates';
		$this->applyData($data);
		$this->_validate();
		return array(array(
			'disabledDates' => $this->loadDisabledDates($this->MorbusOnko_id, null, $this->_params['Morbus_id'], $this->_params['MorbusOnkoVizitPLDop_id'], $this->_params['MorbusOnkoLeave_id'], $this->_params['MorbusOnkoDiagPLStom_id']),
			'disabledDatePeriods' => $this->loadDisabledDatePeriods($this->MorbusOnko_id, null, $this->_params['Morbus_id'], $this->_params['MorbusOnkoVizitPLDop_id'], $this->_params['MorbusOnkoLeave_id'], $this->_params['MorbusOnkoDiagPLStom_id']),
			'minDate' => null,
			'maxDate' => $this->currentDT->format('d.m.Y'),
			'unclosedTreat' => $this->findUnclosedTreat($this->MorbusOnko_id, null, $this->_params['Morbus_id'], $this->_params['MorbusOnkoVizitPLDop_id'], $this->_params['MorbusOnkoLeave_id'], $this->_params['MorbusOnkoDiagPLStom_id'])
		));
	}

	/**
	 * Удаление данных
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function deleteMorbusOnkoSpecTreat($data)
	{
		if(empty($data['MorbusOnkoSpecTreat_id'])){
			return false;
		} else {
			$compls = $this->loadOnkoLateComplTreatTypeList($data);
			if(!empty($compls[0]['MorbusOnkoSpecTreatLink_id'])){
				$res = $this->deleteOnkoLateComplTreatTypeList($compls);
				if(!empty($res[0]['Error_Msg'])){
					return $res;
				}
			}
		}
		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec dbo.p_MorbusOnkoSpecTreat_del
				@MorbusOnkoSpecTreat_id = :MorbusOnkoSpecTreat_id, 
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для формы
	 * @param int $morbusonko_id
	 * @param int $id MorbusOnkoSpecTreat_id
	 * @param int $morbus_id
	 * @return array
	 * @throws Exception
	 */
	function loadDisabledDates($morbusonko_id, $id = null, $morbus_id = null, $morbusonkovizitpldop_id = null, $morbusonkoleave_id = null, $morbusonkodiagplstom_id = null)
	{
		$add_where = '';
		if ($morbusonkovizitpldop_id) {
			$params = array(
				'morbusonkovizitpldop_id' => $morbusonkovizitpldop_id,
			);
		} else if ($morbusonkoleave_id) {
			$params = array(
				'morbusonkoleave_id' => $morbusonkoleave_id,
			);
		} else if ($morbusonkodiagplstom_id) {
			$params = array(
				'morbusonkodiagplstom_id' => $morbusonkodiagplstom_id,
			);
		} else if ($morbusonko_id) {
			$params = array(
				'morbusonko_id' => $morbusonko_id,
			);
		} else if ($morbus_id) {
			$params = array(
				'morbus_id' => $morbus_id,
			);
		} else {
			return array();
		}
		if ($id) {
			$params['MorbusOnkoSpecTreat_id'] = $id;
			$add_where .= ' and MorbusOnkoSpecTreat_id != :MorbusOnkoSpecTreat_id';
		}
		if ($morbusonkovizitpldop_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnkoSpecTreat with (nolock)
				WHERE MorbusOnkoVizitPLDop_id = :morbusonkovizitpldop_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		} else if ($morbusonkoleave_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnkoSpecTreat with (nolock)
				WHERE MorbusOnkoLeave_id = :morbusonkoleave_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		} else if ($morbusonkodiagplstom_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnkoSpecTreat with (nolock)
				WHERE MorbusOnkoDiagPLStom_id = :morbusonkodiagplstom_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		} else if ($morbusonko_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnkoSpecTreat with (nolock)
				WHERE MorbusOnko_id = :morbusonko_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		} else if ($morbus_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnko with (nolock)
				inner join v_MorbusOnkoSpecTreat with (nolock) on v_MorbusOnkoSpecTreat.MorbusOnko_id = v_MorbusOnko.MorbusOnko_id
				WHERE v_MorbusOnko.Morbus_id = :morbus_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		}
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$tmp = $result->result('array');
		} else {
			log_message('error', var_export(array('query' => $query, 'params' => $params, 'error' => sqlsrv_errors()), true));
			$tmp = array();
		}
		$response = array();
		foreach($tmp as $row) {
			$start = null;
			$end = null;//$this->currentDT;
			if ($row['MorbusOnkoSpecTreat_specSetDT'] instanceof DateTime) {
				$response[] = $row['MorbusOnkoSpecTreat_specSetDT']->format('d.m.Y');
				$start = $row['MorbusOnkoSpecTreat_specSetDT'];
			}
			if ($row['MorbusOnkoSpecTreat_specDisDT'] instanceof DateTime) {
				$end = $row['MorbusOnkoSpecTreat_specDisDT'];
			}
			if($end !== null){
				$diff = $start->diff($end)->days+1;
				if ($diff > 1) {
					$interval = new DateInterval("P1D");
					$day_cnt = 1;
					while ($diff != $day_cnt) {
						$response[] = $start->add($interval)->format('d.m.Y');
						$day_cnt++;
					}
				}
			}
		}
		$response = array_unique($response);
		return $response;
	}

	/**
	 * Получение данных для формы
	 * @param int $morbusonko_id
	 * @param int $id MorbusOnkoSpecTreat_id
	 * @param int $morbus_id
	 * @return array
	 * @throws Exception
	 */
	function loadDisabledDatePeriods($morbusonko_id, $id = null, $morbus_id = null, $morbusonkovizitpldop_id = null, $morbusonkoleave_id = null, $morbusonkodiagplstom_id = null)
	{
		$add_where = '';
		if ($morbusonkovizitpldop_id) {
			$params = array(
				'morbusonkovizitpldop_id' => $morbusonkovizitpldop_id,
			);
		} else if ($morbusonkoleave_id) {
			$params = array(
				'morbusonkoleave_id' => $morbusonkoleave_id,
			);
		} else if ($morbusonkodiagplstom_id) {
			$params = array(
				'morbusonkodiagplstom_id' => $morbusonkodiagplstom_id,
			);
		} else if ($morbusonko_id) {
			$params = array(
				'morbusonko_id' => $morbusonko_id,
			);
		} else if ($morbus_id) {
			$params = array(
				'morbus_id' => $morbus_id,
			);
		} else {
			return array();
		}
		if ($id) {
			$params['MorbusOnkoSpecTreat_id'] = $id;
			$add_where .= ' and MorbusOnkoSpecTreat_id != :MorbusOnkoSpecTreat_id';
		}
		if ($morbusonkovizitpldop_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnkoSpecTreat with (nolock)
				WHERE MorbusOnkoVizitPLDop_id = :morbusonkovizitpldop_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		} else if ($morbusonkoleave_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnkoSpecTreat with (nolock)
				WHERE MorbusOnkoLeave_id = :morbusonkoleave_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		} else if ($morbusonkodiagplstom_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnkoSpecTreat with (nolock)
				WHERE MorbusOnkoDiagPLStom_id = :morbusonkodiagplstom_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		} else if ($morbusonko_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnkoSpecTreat with (nolock)
				WHERE MorbusOnko_id = :morbusonko_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		} else if ($morbus_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnko with (nolock)
				inner join v_MorbusOnkoSpecTreat with (nolock) on v_MorbusOnkoSpecTreat.MorbusOnko_id = v_MorbusOnko.MorbusOnko_id
				WHERE v_MorbusOnko.Morbus_id = :morbus_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		}
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$tmp = $result->result('array');
		} else {
			log_message('error', var_export(array('query' => $query, 'params' => $params, 'error' => sqlsrv_errors()), true));
			$tmp = array();
		}
		$response = array();
		foreach($tmp as $row) {
			$oneresp = array();
			$start = null;
			$end = $this->currentDT;
			if ($row['MorbusOnkoSpecTreat_specSetDT'] instanceof DateTime) {
				$oneresp[] = $row['MorbusOnkoSpecTreat_specSetDT']->format('d.m.Y');
				$start = $row['MorbusOnkoSpecTreat_specSetDT'];
			}
			if ($row['MorbusOnkoSpecTreat_specDisDT'] instanceof DateTime) {
				$end = $row['MorbusOnkoSpecTreat_specDisDT'];
			}
			$diff = $start->diff($end)->days+1;
			if ($diff > 1) {
				$interval = new DateInterval("P1D");
				$day_cnt = 1;
				while ($diff != $day_cnt) {
					$oneresp[] = $start->add($interval)->format('d.m.Y');
					$day_cnt++;
				}
			}

			$response [] = array_unique($oneresp);
		}
		return $response;
	}

	/**
	 * Получение данных для формы
	 * @param int $morbusonko_id
	 * @param int $id MorbusOnkoSpecTreat_id
	 * @param int $morbus_id
	 * @return array
	 * @throws Exception
	 */
	function findUnclosedTreat($morbusonko_id, $id = null, $morbus_id = null, $morbusonkovizitpldop_id = null, $morbusonkoleave_id = null, $morbusonkodiagplstom_id = null)
	{
		$add_where = '';
		if ($morbusonkovizitpldop_id) {
			$params = array(
				'morbusonkovizitpldop_id' => $morbusonkovizitpldop_id,
			);
		} else if ($morbusonkoleave_id) {
			$params = array(
				'morbusonkoleave_id' => $morbusonkoleave_id,
			);
		} else if ($morbusonkodiagplstom_id) {
			$params = array(
				'morbusonkodiagplstom_id' => $morbusonkodiagplstom_id,
			);
		} else if ($morbusonko_id) {
			$params = array(
				'morbusonko_id' => $morbusonko_id,
			);
		} else if ($morbus_id) {
			$params = array(
				'morbus_id' => $morbus_id,
			);
		} else {
			return array();
		}
		if ($id) {
			$params['MorbusOnkoSpecTreat_id'] = $id;
			$add_where .= ' and MorbusOnkoSpecTreat_id != :MorbusOnkoSpecTreat_id';
		}
		if ($morbusonkovizitpldop_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnkoSpecTreat with (nolock)
				WHERE MorbusOnkoVizitPLDop_id = :morbusonkovizitpldop_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		} else if ($morbusonkoleave_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnkoSpecTreat with (nolock)
				WHERE MorbusOnkoLeave_id = :morbusonkoleave_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		} else if ($morbusonkodiagplstom_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnkoSpecTreat with (nolock)
				WHERE MorbusOnkoDiagPLStom_id = :morbusonkodiagplstom_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		} else if ($morbusonko_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnkoSpecTreat with (nolock)
				WHERE MorbusOnko_id = :morbusonko_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		} else if ($morbus_id) {
			$query = "
				SELECT MorbusOnkoSpecTreat_specSetDT, MorbusOnkoSpecTreat_specDisDT
				from v_MorbusOnko with (nolock)
				inner join v_MorbusOnkoSpecTreat with (nolock) on v_MorbusOnkoSpecTreat.MorbusOnko_id = v_MorbusOnko.MorbusOnko_id
				WHERE v_MorbusOnko.Morbus_id = :morbus_id {$add_where}
				order by MorbusOnkoSpecTreat_specSetDT
			";
		}
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$tmp = $result->result('array');
		} else {
			log_message('error', var_export(array('query' => $query, 'params' => $params, 'error' => sqlsrv_errors()), true));
			$tmp = array();
		}
		$existUnclosed = false;
		foreach($tmp as $row) {
			if ($row['MorbusOnkoSpecTreat_specDisDT'] == null) {
				$existUnclosed = true;
				return $existUnclosed;
			}
		}
		return $existUnclosed;
	}

	/**
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	function loadOnkoLateComplTreatTypeList($data)
	{
		if(empty($data['MorbusOnkoSpecTreat_id'])){
			return false;
		}
		$query = "
			SELECT
				MorbusOnkoSpecTreatLink_id
				,OnkoLateComplTreatType_id
			FROM
				dbo.v_MorbusOnkoSpecTreatLink WITH (NOLOCK)
			where
				MorbusOnkoSpecTreat_id = :MorbusOnkoSpecTreat_id
		";
		$params = array(
			'MorbusOnkoSpecTreat_id' => $data['MorbusOnkoSpecTreat_id']
		);
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	function deleteOnkoLateComplTreatTypeList($data)
	{
		if(empty($data[0]['MorbusOnkoSpecTreatLink_id']) ){
			return false;
		}
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec dbo.p_MorbusOnkoSpecTreatLink_del
				@MorbusOnkoSpecTreatLink_id = :MorbusOnkoSpecTreatLink_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		foreach ($data as $value) {
			if(empty($value['MorbusOnkoSpecTreatLink_id']) ){
				return false;
			}
			$params = array(
				'MorbusOnkoSpecTreatLink_id' => $value['MorbusOnkoSpecTreatLink_id']
			);
			$result = $this->db->query($query, $params);
			if ( is_object($result) ) {
				$result = $result->result('array');
				if(!empty($result[0]['Error_Msg'])){
					return $result;
				}
			} else {
				return false;
			}
		}
		return true;
	}

	/**
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	function saveOnkoLateComplTreatTypeList($data)
	{
		if(empty($data['MorbusOnkoSpecTreat_id']) || empty($this->_params['lateCompls'])){
			return false;
		}
		$query = "
			declare
				@MorbusOnkoSpecTreatLink_id bigint,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec dbo.p_MorbusOnkoSpecTreatLink_ins
				@MorbusOnkoSpecTreatLink_id = @MorbusOnkoSpecTreatLink_id output,
				@MorbusOnkoSpecTreat_id = :MorbusOnkoSpecTreat_id,
				@OnkoLateComplTreatType_id = :OnkoLateComplTreatType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @MorbusOnkoSpecTreatLink_id as MorbusOnkoSpecTreatLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$compls = $this->_params['lateCompls'];
		if(strpos($compls, ',') > 0){
			$compls = explode(',', $compls);
		} else {
			$compls = array('0'=>$compls);
		}

		foreach ($compls as $value) {
			$params = array(
				'pmUser_id' => $this->_params['pmUser_id'],
				'MorbusOnkoSpecTreat_id' => $data['MorbusOnkoSpecTreat_id'],
				'OnkoLateComplTreatType_id' => $value
			);
			$result = $this->db->query($query, $params);
			if ( is_object($result) ) {
				$result = $result->result('array');
				if(!empty($result[0]['Error_Msg'])){
					return $result[0];
				}
			} else {
				return false;
			}
		}
		return true;
	}
}
