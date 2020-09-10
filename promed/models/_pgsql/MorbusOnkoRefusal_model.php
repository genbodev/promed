<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Специфика (онкология). Данные об отказах / противопоказаниях
 *
 * @package      MorbusOnko
 * @author       Быков Станислав
 * @version      03.2019
 *
 * @property int $MorbusOnko_id
 * @property int $MorbusOnkoVizitPLDop_id
 * @property int $MorbusOnkoDiagPLStom_id
 * @property int $MorbusOnkoLeave_id
 * @property DateTime $setDT дата регистрации отказа / противопоказания
 * @property int $MorbusOnkoRefusalType_id тип лечения
 */
class MorbusOnkoRefusal_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->_setScenarioList(array(
			self::SCENARIO_VIEW_DATA,
			self::SCENARIO_LOAD_EDIT_FORM,
			self::SCENARIO_DELETE,
			self::SCENARIO_DO_SAVE,
		));
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName() {
		return 'MorbusOnkoRefusal';
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
					self::PROPERTY_NOT_NULL,
				),
				'alias' => 'MorbusOnkoRefusal_id',
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
			'morbusonkorefusal_id' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'MorbusOnkoRefusal_id',
				'label' => 'MorbusOnkoRefusal_id',
				'save' => '',
				'type' => 'id'
			),
			'setdt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MorbusOnkoRefusal_setDT',
				'label' => 'Дата регистрации отказа / противопоказания',
				'applyMethod' => '_applySetDate',
				'save' => 'trim|required',
				'type' => 'date'
			),
			'morbusonkorefusaltype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MorbusOnkoRefusalType_id',
				'label' => 'Тип лечения',
				'save' => 'trim|required',
				'type' => 'id'
			),
		);
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
		}
		return $rules;
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate() {
		parent::_validate();

		if ( self::SCENARIO_VIEW_DATA == $this->scenario ) {
			if ( empty($this->_params['Morbus_id']) || empty($this->_params['Evn_id'])) {
				throw new Exception('Не указаны обязательные параметры для получения списка объектов специального лечения', 500);
			}
		}
		if (in_array($this->scenario, array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
		))) {
			if ( empty($this->MorbusOnko_id) && empty($this->MorbusOnkoVizitPLDop_id) && empty($this->MorbusOnkoLeave_id) && empty($this->MorbusOnkoDiagPLStom_id) ) {
				throw new Exception('Не указано заболевание', 500);
			}
			if ( ConvertDateFormat($this->setDT,'Y-m-d') > date('Y-m-d')) {
				throw new Exception('Дата регистрации отказа / противопоказания не должна быть больше текущей даты', 400);
			}

			if ( !empty($this->MorbusOnkoVizitPLDop_id) ) {
				$Evn_id = $this->getFirstResultFromQuery("
					select EvnVizit_id as \"EvnVizit_id\"
					from v_MorbusOnkoVizitPLDop
					where MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id
					limit 1
				", array(
					'MorbusOnkoVizitPLDop_id' => $this->MorbusOnkoVizitPLDop_id
				));
			}
			else if ( !empty($this->MorbusOnkoLeave_id) ) {
				$Evn_id = $this->getFirstResultFromQuery("
					select EvnSection_id as \"EvnSection_id\"
					from v_MorbusOnkoLeave
					where MorbusOnkoLeave_id = :MorbusOnkoLeave_id
					limit 1
				", array(
					'MorbusOnkoLeave_id' => $this->MorbusOnkoLeave_id
				));
			}
			else if ( !empty($this->MorbusOnkoDiagPLStom_id) ) {
				$Evn_id = $this->getFirstResultFromQuery("
					select EvnDiagPLStom_id as \"EvnDiagPLStom_id\"
					from v_MorbusOnkoDiagPLStom
					where MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id
					limit 1
				", array(
					'MorbusOnkoDiagPLStom_id' => $this->MorbusOnkoDiagPLStom_id
				));
			}
			else {
				$Evn_id = $this->getFirstResultFromQuery("
					select Evn_pid as \"Evn_pid\"
					from v_MorbusOnko mo
					where mo.MorbusOnko_id = :MorbusOnko_id
					limit 1
				", array(
					'MorbusOnko_id' => $this->MorbusOnko_id
				));
			}

			if (!empty($Evn_id)) {
				$this->load->model('MorbusOnkoSpecifics_model');
				$check = $this->MorbusOnkoSpecifics_model->checkDatesBeforeSave(array(
					'Evn_id' => $Evn_id,
					'dateOnko' => ConvertDateFormat($this->setDT,'Y-m-d'),
					'object' => $this->tableName(),
				));
				if (!empty($check['Err_Msg'])) {
					throw new Exception($check['Err_Msg']);
				}
			}
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array()) {
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
	protected function _afterSave($result) {
		parent::_afterSave($result);
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 *
	 * Перед вызовом должен быть указан сценарий
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data) {
		parent::setParams($data);

		if ( self::SCENARIO_VIEW_DATA == $this->scenario ) {
			$this->_params['Morbus_id'] = empty($data['Morbus_id']) ? null : $data['Morbus_id'];
			$this->_params['MorbusOnkoVizitPLDop_id'] = empty($data['MorbusOnkoVizitPLDop_id']) ? null : $data['MorbusOnkoVizitPLDop_id'];
			$this->_params['MorbusOnkoLeave_id'] = empty($data['MorbusOnkoLeave_id']) ? null : $data['MorbusOnkoLeave_id'];
			$this->_params['MorbusOnkoDiagPLStom_id'] = empty($data['MorbusOnkoDiagPLStom_id']) ? null : $data['MorbusOnkoDiagPLStom_id'];
			$this->_params['Evn_id'] = empty($data['Evn_id']) ? null : $data['Evn_id'];
		}
		if (swModel::SCENARIO_DO_SAVE == $this->scenario) {
			$this->_params['pmUser_id'] = empty($data['pmUser_id']) ? null : $data['pmUser_id'];
		}
	}

	/**
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	function getViewData($data) {
		$this->setScenario(self::SCENARIO_VIEW_DATA);
		$this->setParams($data);
		$this->_validate();

		if (!empty($data['MorbusOnkoVizitPLDop_id'])) {
			$filter = 'MOR.MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id';
		} elseif (!empty($data['MorbusOnkoLeave_id'])) {
			$filter = 'MOR.MorbusOnkoLeave_id = :MorbusOnkoLeave_id';
		} elseif (!empty($data['MorbusOnkoDiagPLStom_id'])) {
			$filter = 'MOR.MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id';
		} elseif (!empty($data['Morbus_id'])) {
			$filter = 'Morbus.Morbus_id = :Morbus_id';
		}

		$query = "
			SELECT
				case
					when 1=1 then 'edit'
					else 'view'
				end as \"accessType\",
				MOR.MorbusOnkoRefusal_id as \"MorbusOnkoRefusal_id\",
				MO.MorbusOnko_id as \"MorbusOnko_id\",
				to_char(MOR.MorbusOnkoRefusal_setDT, 'dd.mm.yyyy') as \"MorbusOnkoRefusal_setDT\",
				MORT.MorbusOnkoRefusalType_Name as \"MorbusOnkoRefusalType_id_Name\",
				:Evn_id as \"MorbusOnko_pid\",
				Morbus.Morbus_id as \"Morbus_id\",
				MOR.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\",
				MOR.MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\",
				MOR.MorbusOnkoDiagPLStom_id as \"MorbusOnkoDiagPLStom_id\"
			FROM
				dbo.v_Morbus Morbus
				INNER JOIN dbo.v_MorbusOnko MO on Morbus.Morbus_id = MO.Morbus_id
				INNER JOIN dbo.v_MorbusOnkoRefusal MOR on MO.MorbusOnko_id = MOR.MorbusOnko_id
				inner join dbo.v_MorbusOnkoRefusalType MORT ON MORT.MorbusOnkoRefusalType_id = MOR.MorbusOnkoRefusalType_id
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
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			log_message('error', var_export(array('query' => $query, 'params' => $params, 'error' => sqlsrv_errors()), true));
			return false;
		}
	}

	/**
	 * Получение данных для формы
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function doLoadEditForm($data) {
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		$this->applyData($data);
		$this->_validate();
		return array(array(
			$this->tableName() . '_id' => $this->id,
			'MorbusOnko_id' => $this->MorbusOnko_id,
			'MorbusOnkoLeave_id' => $this->MorbusOnkoLeave_id,
			'MorbusOnkoVizitPLDop_id' => $this->MorbusOnkoVizitPLDop_id,
			'MorbusOnkoDiagPLStom_id' => $this->MorbusOnkoDiagPLStom_id,
			$this->tableName() . '_setDT' => ConvertDateFormat($this->setDT,'d.m.Y'),
			'MorbusOnkoRefusalType_id' => $this->MorbusOnkoRefusalType_id,
			'minDate' => null,
			'maxDate' => ConvertDateFormat($this->currentDT,'d.m.Y'),
		));
	}

	/**
	 * Удаление данных
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function deleteMorbusOnkoRefusal($data) {
		if(empty($data['MorbusOnkoRefusal_id'])){
			return false;
		}
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as\" Error_Msg\"
			from dbo.p_MorbusOnkoRefusal_del(
				MorbusOnkoRefusal_id := :MorbusOnkoRefusal_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$res = $result->result('array');
			$res = $res[0];
			$res['success'] = true;
			return $res;
		} else {
			return false;
		}
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applySetDate($data) {
		return $this->_applyDate($data, 'setdt');
	}
}
