<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusNephroDrug_model - модель "Лабораторные исследования" регистра по нефрологии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      04.2019
 *
 * @property CI_DB_driver $db
 */
class MorbusNephroDrug_model extends swPgModel
{
	/**
	 * @return string
	 */
	function getObjectSysNick()
	{
		return 'MorbusNephroDrug';
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
			self::SCENARIO_DELETE
		));
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusNephroDrug';
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
				'alias' => 'MorbusNephroDrug_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			),
			'morbusnephro_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MorbusNephro_id',
				'label' => 'Заболевание',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'morbusnephrodrug_begdt' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME
				),
				'alias' => 'MorbusNephroDrug_begDT',
				'label' => 'Дата с',
				'save' => 'trim|required',
				'type' => 'date'
			),
			'morbusnephrodrug_enddt' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME
				),
				'alias' => 'MorbusNephroDrug_endDT',
				'label' => 'Дата по',
				'save' => 'trim|required',
				'type' => 'date'
			),
			'morbusnephrodrug_dose' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MorbusNephroDrug_Dose',
				'label' => 'Разовая доза',
				'save' => 'trim|required|max_length[10]',
				'type' => 'string'
			),
			'morbusnephrodrug_multi' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MorbusNephroDrug_Multi',
				'label' => 'Кратность',
				'save' => 'trim|required|max_length[30]',
				'type' => 'string'
			),
			'morbusnephrodrug_sumdose' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MorbusNephroDrug_SumDose',
				'label' => 'Суммарная доза',
				'save' => 'trim|required|max_length[10]',
				'type' => 'string'
			),
			'unit_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Unit_id',
				'label' => 'Единица измерения',
				'save' => 'trim|required',
				'type' => 'id',
				'select' => 'u.Unit_id',
				'join' => 'inner join v_Unit u on u.Unit_id = {ViewName}.Unit_id',
			),
			'evnvk_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'EvnVK_id',
				'label' => 'Идентификатор протокола ВК',
				'save' => 'trim',
				'type' => 'id'
			),
			'drugcomplexmnn_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'DrugComplexMnn_id',
				'label' => 'Медикамент',
				'save' => 'trim|required',
				'type' => 'id',
				'select' => 'dcm.DrugComplexMnn_id',
				'join' => 'inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = {ViewName}.DrugComplexMnn_id',
			),
			'nephrodrugscheme_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'NephroDrugScheme_id',
				'label' => 'Схема',
				'save' => 'trim|required',
				'type' => 'id',
				'select' => 'ds.NephroDrugScheme_id',
				'join' => 'inner join v_NephroDrugScheme ds on ds.NephroDrugScheme_id = {ViewName}.NephroDrugScheme_id',
			),
			
			'insdt' => array(
				'properties' => array(
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
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name) {
		$rules = parent::getInputRules($name);
		switch ($name) {
			case self::SCENARIO_LOAD_GRID:
				$rules['isOnlyLast'] = array(
					'field' => 'isOnlyLast',
					'default' => 0, // по умолчанию «Все»
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
			case 'doLoadVKProtocolList':
				$rules['Person_id'] = array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пользователя',
					'rules' => 'trim|required',
					'type' => 'int'
				);
				break;
			case 'doLoadUsedSchemeList':
				$rules['MorbusNephro_id'] = array(
					'field' => 'MorbusNephro_id',
					'rules' => 'trim',
					'type' => 'int'
				);
				break;
			case 'deleteMorbusNephroDrug':
				$rules['MorbusNephroDrug_id'] = array(
					'field' => 'MorbusNephroDrug_id',
					'label' => 'Идентификатор лечения',
					'rules' => 'required',
					'type' => 'id'
				);
				break;
			case 'doLoadDispList':
			case 'doLoadSchemeRuleList':
			case 'doLoadMnnList':
			case 'doLoadParentList':
			case 'doLoadNoeffectList':
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
			if (empty($this->MorbusNephroDrug_begDT) || empty($this->MorbusNephroDrug_endDT)) {
				throw new Exception('Не указана дата!', 500);
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
	 * @param $data
	 * @return array|bool
	 */
	function doLoadEditForm($data)
	{
		$params = ['MorbusNephroDrug_id' => $data['MorbusNephroDrug_id']];
		$query = "
			select
				mnd.MorbusNephroDrug_id as \"MorbusNephroDrug_id\",
				mnd.MorbusNephro_id as \"MorbusNephro_id\",
				to_char(mnd.MorbusNephroDrug_begDT, 'dd.mm.yyyy') as \"MorbusNephroDrug_begDT\",
				to_char(mnd.MorbusNephroDrug_endDT, 'dd.mm.yyyy') as \"MorbusNephroDrug_endDT\",
				mnd.MorbusNephroDrug_Dose as \"MorbusNephroDrug_Dose\",
				mnd.MorbusNephroDrug_SumDose as \"MorbusNephroDrug_SumDose\",
				mnd.MorbusNephroDrug_Multi as \"MorbusNephroDrug_Multi\",
				mnd.Unit_id as \"Unit_id\",
				mnd.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				mnd.NephroDrugScheme_id as \"NephroDrugScheme_id\",
				mnd.EvnVk_id as \"EvnVk_id\",
				'№'||EvnVK_NumProtocol::varchar||' от '||to_char(EvnVK_setDate, 'dd.mm.yyyy') as \"EvnVK_Description\"
			from
				v_MorbusNephroDrug mnd (nolock)
				left join v_EvnVK vk on vk.EvnVK_id = mnd.EvnVK_id
			where MorbusNephroDrug_id = :MorbusNephroDrug_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array()[0];
		return [[
			'MorbusNephroDrug_id' => $result['MorbusNephroDrug_id'],
			'MorbusNephro_id' => $result['MorbusNephro_id'],
			'MorbusNephroDrug_begDT' => $result['MorbusNephroDrug_begDT'],
			'MorbusNephroDrug_endDT' => $result['MorbusNephroDrug_endDT'],
			'MorbusNephroDrug_Dose' => $result['MorbusNephroDrug_Dose'],
			'MorbusNephroDrug_SumDose' => $result['MorbusNephroDrug_SumDose'],
			'MorbusNephroDrug_Multi' => $result['MorbusNephroDrug_Multi'],
			'Unit_id' => $result['Unit_id'],
			'DrugComplexMnn_id' => $result['DrugComplexMnn_id'],
			'NephroDrugScheme_id' => $result['NephroDrugScheme_id'],
			'EvnVk_id' => $result['EvnVk_id'],
			'EvnVK_Description' => $result['EvnVK_Description']
		]];
	}

	/**
	 *  Читает для грида и панели просмотра
	 */
	function doLoadGrid($data) {
		if (empty($data['scenario'])) {
			$data['scenario'] = self::SCENARIO_LOAD_GRID;
		}
		$this->applyData($data);
		$queryParams = array(
			'MorbusNephro_id' => $this->MorbusNephro_id
		);
		$add_join = '';
		$filters = 't.MorbusNephro_id = :MorbusNephro_id';
		$isOnlyLast = !empty($data['isOnlyLast']) ? 'limit 3' :  '';

		$queryParams['Evn_id'] = isset($data['Evn_id']) ? $data['Evn_id'] : null;
		if (false && $this->scenario == self::SCENARIO_VIEW_DATA) {
			$add_join .= '
			inner join v_MorbusBase MB on MB.MorbusBase_id = MV.MorbusBase_id';
			$add_join .= '
			left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id';
		}
		$sql = "
		select
		* 
		from (
			select
				case when MV.Morbus_disDT is null then 'edit' else 'view' end as \"accessType\",
				t.MorbusNephroDrug_id as \"MorbusNephroDrug_id'\",
				t.MorbusNephro_id as \"MorbusNephro_id'\",
				t.MorbusNephroDrug_Dose as \"MorbusNephroDrug_Dose'\",
				t.MorbusNephroDrug_Multi as \"MorbusNephroDrug_Multi'\",
				t.MorbusNephroDrug_SumDose as \"MorbusNephroDrug_SumDose'\",
				t.NephroDrugScheme_id as \"NephroDrugScheme_id'\",
				ds.NephroDrugScheme_Name as \"NephroDrugScheme_Name\",
				to_char(t.MorbusNephroDrug_begDT, 'dd.mm.yyyy') as \"MorbusNephroDrug_begDT\",
				to_char(t.MorbusNephroDrug_endDT, 'dd.mm.yyyy') as \"MorbusNephroDrug_endDT\",
				u.Unit_id as \"Unit_id\",
				u.Unit_Name as \"Unit_Name\",
				mnn.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
				:Evn_id as \"MorbusNephro_pid\"
			from v_MorbusNephroDrug t
			inner join v_MorbusNephro MV on MV.MorbusNephro_id = t.MorbusNephro_id
			inner join v_NephroDrugScheme ds on ds.NephroDrugScheme_id = t.NephroDrugScheme_id
			inner join v_Unit u on u.Unit_id = t.Unit_id
			inner join rls.v_DrugComplexMnn mnn on  mnn.DrugComplexMnn_id = t.DrugComplexMnn_id
			{$add_join}
			where {$filters}
		) t
		order by cast(\"MorbusNephroDrug_begDT\" as date) DESC
		{$isOnlyLast}
		";
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
	protected function _beforeDelete($data = array()) {
		parent::_beforeDelete($data);
		// значения параметра удаляются в хранимке
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array()) {
		parent::_beforeSave($data);
		return true;
	}

	/**
	 * Сохранение
	 */
	protected function _save($queryParams = array()) {
		if (empty($queryParams)) {
			$queryParams = array();
			$queryParams[$this->primaryKey()] = array(
				'value' => $this->id,
				'out' => true,
				'type' => 'bigint',
			);
			$queryParams['pmUser_id'] = $this->promedUserId;
			foreach ($this->defAttribute as $key => $info) {
				if (in_array(self::PROPERTY_IS_SP_PARAM, $info['properties'])) {
					$queryParams[$this->_getColumnName($info['alias'], $info)] = $this->getAttribute($key);
				}
			}
		}
		if (
			empty($queryParams[$this->primaryKey()])
			|| !array_key_exists('value', $queryParams[$this->primaryKey()])
		) {
			throw new Exception('Неправильный формат параметров запроса', 500);
		}
		// Конвертируем даты в строки
		foreach ($queryParams as $key => $value) {
			if ($value instanceof DateTime) {
				$queryParams[$key] = $value->format('Y-m-d H:i:s');
			}
		}

		if (empty($queryParams[$this->primaryKey()]['value'])) {
			$sp_name = $this->createProcedureName();
		} else {
			$sp_name = $this->updateProcedureName();
		}

		$tmp = $this->execCommonSP($sp_name, $queryParams);
		if (empty($tmp)) {
			throw new Exception('Ошибка запроса записи данных объекта в БД', 500);
		}
		if (isset($tmp[0]['Error_Msg'])) {
			throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
		}
		return $tmp;
	}

	/**
	 * Получение списка схем
	 */
	function doLoadSchemeList($data) {
		$query = "select 
			NephroDrugScheme_id as \"id\",
			NephroDrugScheme_Group as \"schemeGroup\",
			NephroDrugScheme_SubGroup as \"schemeSubGroup\",
			NephroDrugScheme_Name as \"schemeName\",
			NephroDrugScheme_Description as \"schemeDescription\",
			NephroDrugScheme_Dose as \"dose\",
			NephroDrugScheme_Multi as \"multi\",
			NephroDrugScheme_SumDose as \"sumDose\",
			Unit_id as \"unitTypeId\",
			NephroDrugScheme_IsResistance as \"isResistance\",
			NephroDrugScheme_IsNoControl as \"isNoControl\",
			NephroDrugScheme_IsSideEffect as \"isSideEffect\",
			LogicalConnective_nid as \"noEffectLC\",
			LogicalConnective_rid as \"resistanceLC\",
			LogicalConnective_tid as \"noTabletLC\",
			LogicalConnective_sid as \"sideEffectLC\"
		from v_NephroDrugScheme
		where NephroDrugScheme_Group <> 0
		order by NephroDrugScheme_Group, NephroDrugScheme_SubGroup
		";
		return $this->queryResult($query);
	}

	/**
	 * Получение списка использованных схем
	 */
	function doLoadUsedSchemeList($data) {
		$params = ['MorbusNephro_id' => $data['MorbusNephro_id']];
		$query = "
			select
				mnd.NephroDrugScheme_id as \"schemeId\",
				nds.NephroDrugScheme_Name as \"schemeName\",
				nds.NephroDrugScheme_Group as \"schemeGroup\",
				nds.NephroDrugScheme_SubGroup as \"schemeSubGroup\",
			    to_char(mnd.MorbusNephroDrug_begDT, 'mm/dd/yy') as \"begDT\"
			from (
				select
					NephroDrugScheme_id as \"NephroDrugScheme_id\",
					max(MorbusNephroDrug_begDT) as \"MorbusNephroDrug_begDT\"
				from v_MorbusNephroDrug mnd
				where MorbusNephro_id = :MorbusNephro_id
				group by NephroDrugScheme_id
			) mnd
			inner join v_NephroDrugScheme nds on nds.NephroDrugScheme_id = mnd.NephroDrugScheme_id
			order by nds.NephroDrugScheme_Group, nds.NephroDrugScheme_SubGroup
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка правил
	 */
	function doLoadSchemeRuleList($data) {
		$query = "
			select
				NephroDrugSchemeRule_id as \"id\",
				NephroDrugSchemeRule_MinValue as \"minValue\",
				NephroDrugSchemeRule_MaxValue as \"maxValue\",
	            NephroDrugSchemeRule_IsVK as \"isVK\",
				NephroDrugScheme_id as \"schemeId\",
				Unit_id as \"rateUnitTypeId\",
				RateType_id as \"rateTypeId\"
			from v_NephroDrugSchemeRule
		";
		return $this->queryResult($query);
	}

	/**
	 * Получение списка медикаментов
	 * @param $data
	 * @return array|false
	 */
	function doLoadMnnList($data) {
		$query = "
			select
				t.NephroDrugSchemeAllowedMnn_id as \"id\",
				t.NephroDrugScheme_id as \"schemeId\",
				dcm.DrugComplexMnn_id as \"drugId\",
				dcm.DrugComplexMnn_RusName as \"drugName\"
			from
				v_NephroDrugSchemeAllowedMnn t
				inner join rls.DrugComplexMnn dcm on dcm.DrugComplexMnn_id = t.DrugComplexMnn_id
		";
		return $this->queryResult($query);
	}

	/**
	 * Получение списка предшествующих схем
	 * @param $data
	 * @return array|false
	 */
	function doLoadParentList($data)
	{
		$query = "
			select
				NephroDrugSchemeParent_id as \"id\",
				NephroDrugScheme_id as \"schemeId\",
				NephroDrugScheme_pid as \"pid\"
			from v_NephroDrugSchemeParent
		";
		return $this->queryResult($query);
	}

	/**
	 * Получение списка схем для дополнительного условия "Отсутствие эффекта"
	 * @param $data
	 * @return array|false
	 */
	function doLoadNoeffectList($data)
	{
		$query = "
			select
				NephroDrugSchemeNoEffect_id as \"id\",
				NephroDrugScheme_id as \"schemeId\",
				NephroDrugScheme_nid as \"nid\"
			from v_NephroDrugSchemeNoEffect
		";
		return $this->queryResult($query);
	}

	/**
	 * Получение списка протоколов ВК
	 * @param $data
	 * @return array|false
	 */
	function doLoadVKProtocolList($data)
	{
		$params = ['Person_id' => $data['Person_id']];
		$query = "
			select
				EvnVK_id as \"EvnVK_id\",
				'№'||EvnVK_NumProtocol::varchar||' от '||to_char(EvnVK_setDate, 'dd.mm.yyyy') as \"EvnVK_Description\",
				EvnVK_NumProtocol as \"EvnVK_NumProtocol\",
				EvnVK_setDate as \"EvnVK_setDate\",
				EvnVK_isResult as \"EvnVK_isResult\",
				CauseTreatmentType_id as \"CauseTreatmentType_id\"
			from v_EvnVK
			where Person_id = :Person_id
			  and CauseTreatmentType_id = 3
		";
		return $this->queryResult($query, $params);
	}
}