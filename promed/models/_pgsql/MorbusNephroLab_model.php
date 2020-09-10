<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusNephroLab_model - модель "Лабораторные исследования" регистра по нефрологии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      11.2014
 *
 * Поля
 * @property-read int $MorbusNephro_id
 * @property-read DateTime $rateDT Дата
 * @property-read int $Rate_id
 * @property-read int $RateType_id Показатель
 * @property-read string $value Значение
 * @property-read int $isDinamic
 *
 * Статические свойства
 * @property-read string $objectSysNick
 * @property-read array $listRateTypes
 */
class MorbusNephroLab_model extends swPgModel
{
	/**
	 * Возможные показатели
	 * @param int $isDinamic
	 * @return array
	 */
	function getListRateTypes($isDinamic = null)
	{
		if (!isset($isDinamic)) {
			$isDinamic = $this->isDinamic;
		}
		if (2 == $isDinamic) {
			return array(
				4,   // Гемоглобин
				7,   // Лейкоциты
				16,  // СОЭ
				92,  // Билирубин
				94,  // Эритроциты
				105, // Артериальное давление (АД)
				106, // Диурез (ml)
				107, // ЦП
				108, // Лимфоциты
				109, // Креатинин крови
				110, // Мочевина крови
				111, // Сахар крови
				112, // K+ крови
				113, // Na+ крови
				114, // Общий белок
				115, // Альбумин
				116, // АЛТ
				117, // АСТ
				118, // Р крови
				119, // Ca (общ.) крови
				120, // Ca (++) крови
				121, // Щелочная фосфотаза
				122, // Fe сыворотки
				123, // ПТГ
				124, // Т3
				125, // Т4
				126, // Диурез (min)
				127, // Креатинин мочи
				128, // Клубочковая фильтрация
				129, // Кан. реабсорбция
				130, // СПБ
				204, // Насыщения трансферрина
				205, // Ферритин
				206  // Фосфор
			);
		} else {
			return array(
				4,   // Гемоглобин
				7,   // Лейкоциты
				88,  // Удельный вес
				94,  // Эритроциты
				103, // Соли
				104, // Бактерии
				109, // Креатинин крови
				110, // Мочевина крови
				128, // Клубочковая фильтрация
				131, // Суточная протеинурия
				132, // Культура мочи
				133, // Белок мочи
				134, // Цилиндры
			);
		}
	}

	/**
	 * @return string
	 */
	function getObjectSysNick()
	{
		return 'MorbusNephroLab';
	}

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
		return 'MorbusNephroRate';
	}

	/**
	 * Определение Лабораторные исследования или Динамическое наблюдение
	 * @return int
	 */
	function getIsDinamic()
	{
		return 1;
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
				'alias' => 'MorbusNephroLab_id',
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
			'ratedt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
				),
				'applyMethod'=>'_applyRateDT',
				'alias' => 'MorbusNephroLab_Date',
				'label' => 'Дата с',
				'save' => 'trim|required',
				'type' => 'date'
			),
			'endratedt' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'MorbusNephroDisp_EndDate',
				'label' => 'Дата по',
				'save' => '',
				'type' => 'date'
			),
			'rate_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'Rate_id',
				'label' => 'Показатель',
				'save' => 'trim',
				'type' => 'id'
			),
			'ratetype_id' => array(
				'properties' => array(),
				'alias' => 'RateType_id',
				'label' => 'Показатель',
				'save' => 'trim|required',
				'type' => 'id',
				'select' => 'r.RateType_id',
				'join' => 'inner join v_Rate r with on r.Rate_id = {ViewName}.Rate_id',
			),
			'unit_id' => array(
				'properties' => array(),
				'alias' => 'Unit_id',
				'label' => 'Единица измерения',
				'save' => 'trim|required',
				'type' => 'id',
				'select' => 'r.Unit_id',
			),
			'value' => array(
				'properties' => array(),
				'alias' => 'Rate_ValueStr',
				'label' => 'Значение',
				'save' => 'trim|required|max_length[50]',
				'type' => 'string',
				'select' => 'r.Rate_ValueStr as value',
			),
			'isdinamic' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
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
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyRateDT($data)
	{
		return $this->_applyDate($data, 'ratedt');
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
				$rules['isOnlyLast'] = array(
					'field' => 'isOnlyLast',
					'default' => 0,// по умолчанию «Все»
					'label' => 'Tолько последние',
					'rules' => 'trim',
					'type' => 'int'
				);
				$rules['morbusnephro_id'] = array(
					'field' => 'MorbusNephro_id',
					'rules' => 'trim|required',
					'label' => 'Заболевание',
					'type' => 'id'
				);
				break;
			case 'doLoadRateTypeList':
				$rules['isDinamic'] = array(
					'field' => 'isDinamic',
					'default' => 0,
					'label' => 'Тип списка показателей',
					'rules' => 'trim',
					'type' => 'int'
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

		if (in_array($this->scenario, array(
			self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,
		))) {
			if (empty($this->promedUserId)) {
				throw new Exception('Нет параметра pmUser_id!', 500);
			}
		}

		if (in_array($this->scenario, array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
		))) {
			if (empty($this->MorbusNephro_id)) {
				throw new Exception('Не указано заболевание!', 500);
			}
			if (empty($this->rateDT)) {
				throw new Exception('Не указана дата!', 500);
			}
			if (empty($this->RateType_id)) {
				throw new Exception('Не указан показатель!', 500);
			}
			if (false == in_array($this->RateType_id, $this->listRateTypes)) {
				throw new Exception('Указан недопустимый показатель!', 400);
			}
			if (empty($this->value)) {
				throw new Exception('Не указано значение!', 500);
			}
		}

		if (in_array($this->scenario, array(
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_VIEW_DATA,
		))) {
			if (empty($this->MorbusNephro_id)) {
				throw new Exception('Не указано заболевание!', 500);
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
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		$this->applyData($data);
		$this->_validate();
		return array(array(
			$this->objectSysNick . '_id' => $this->id,
			'Rate_id' => $this->Rate_id,
			'MorbusNephro_id' => $this->MorbusNephro_id,
			$this->objectSysNick . '_Date' => $this->rateDT->format('d.m.Y'),
			'RateType_id' => $this->RateType_id,
			'Rate_ValueStr' => $this->value,
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
			'MorbusNephro_id' => $this->MorbusNephro_id,
			'isDinamic' => $this->isDinamic,
		);
		$add_join = '';
		$filters = 't.MorbusNephro_id = :MorbusNephro_id
			and t.MorbusNephroRate_IsDinamic = :isDinamic';
		if (!empty($data['isOnlyLast'])) {
			$add_join .= '
			left join lateral(
					select t2.MorbusNephroRate_id
					from v_MorbusNephroRate t2
					inner join v_Rate r2 on r2.Rate_id = t2.Rate_id
					and r2.RateType_id = r.RateType_id
					where t2.MorbusNephro_id = t.MorbusNephro_id
					and t2.MorbusNephroRate_IsDinamic = :isDinamic
					order by t2.MorbusNephroRate_rateDT desc
					limit 1
			) lastType on true';
			$filters .= '
				and lastType.MorbusNephroRate_id = t.MorbusNephroRate_id';
		}
		$queryParams['Evn_id'] = isset($data['Evn_id']) ? $data['Evn_id'] : null;
		if (false && $this->scenario == self::SCENARIO_VIEW_DATA) {
			// когда вренется подписание, нужно будет убрать false &&
			$add_join .= '
			inner join v_MorbusBase MB with on MB.MorbusBase_id = MV.MorbusBase_id';
			$add_join .= '
			left join v_Evn EvnEdit with on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id';
		}
		$idAlias = $this->objectSysNick . '_id';
		$dateAlias = $this->objectSysNick . '_Date';
		$sql = "
			select
				case when MV.Morbus_disDT is null then 'edit' else 'view' end as \"accessType\",
				t.MorbusNephroRate_id as \"{$idAlias}\",
				t.MorbusNephro_id as \"MorbusNephro_id\",
				t.Rate_id as \"Rate_id\",
				r.RateType_id as \"RateType_id\",
				rt.RateType_Name as \"RateType_Name\",
				r.Rate_ValueStr as \"Rate_ValueStr\",
				to_char(t.MorbusNephroRate_rateDT, 'dd.mm.yyyy') as \"{$dateAlias}\",
				:Evn_id as \"MorbusNephro_pid\"
			from v_MorbusNephroRate t
				inner join v_Rate r on r.Rate_id = t.Rate_id
				inner join v_RateType rt on rt.RateType_id = r.RateType_id
				inner join v_MorbusNephro MV on MV.MorbusNephro_id = t.MorbusNephro_id
				{$add_join}
			where {$filters}
			order by t.MorbusNephroRate_rateDT
		";
		// echo getDebugSql($sql, $queryParams);die();
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = array())
	{
		parent::_beforeDelete($data);
		// значения параметра удаляются в хранимке
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
		// модель загружена и проверена в _validate
		// сохраняем Rate
		if (empty($this->Rate_id)) {
			$sp_name = 'p_Rate_ins';
		} else {
			$sp_name = 'p_Rate_upd';
		}
		$params = array();
		$params['Rate_id'] = array(
			'value' => $this->Rate_id,
			'out' => true,
			'type' => 'bigint',
		);
		$params['RateType_id'] = $this->RateType_id;
		$params['Unit_id'] = $this->Unit_id;
		$params['Rate_ValueStr'] = $this->value;
		$params['Server_id'] = $this->sessionParams['server_id'];
		$params['pmUser_id'] = $this->promedUserId;
		$res = $this->execCommonSP($sp_name, $params);
		if (empty($res)) {
			throw new Exception('Не удалось сохранить значение показателя', 500);
		}
		if (!empty($res[0]['Error_Msg'])) {
			throw new Exception($res[0]['Error_Msg']);
		}
		if (empty($res[0]['Rate_id'])) {
			throw new Exception('Не удалось получить идентификатор значения показателя', 500);
		}
		$this->setAttribute('rate_id', $res[0]['Rate_id']);
		$this->setAttribute('isdinamic', $this->isDinamic);
	}

	/**
	 * Загрузка комбобокса показателей
	 */
	function doLoadRateTypeList($data)
	{
		$idList = implode(',', $this->getListRateTypes($data['isDinamic']));
		if (empty($idList)) {
			return array();
		}
		$sql = "
			select
				RateType_id as \"RateType_id\",
				RateType_Name as \"RateType_Name\",
				RateType_SysNick as \"RateType_SysNick\"
			from v_RateType
			where RateType_id in ({$idList})
			order by RateType_Name
		";
		$result = $this->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}
}