<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ParameterValue_model - модель редактируемого справочника "Параметр и список значений"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       Пермяков Александр
 * @version      09.2014
 *
 * @property-read string $name Наименование параметра для печати
 * @property-read string $sysNick Системное имя параметра,
 * удовлетворяет регулярному выражению parameter[0-9]{1,20}
 * @property-read string $alias Наименование параметра
 * @property-read int $ParameterValueListType_id Тип списка значений
 * @property-read string $ParameterValueListType_Name
 * @property-read int $XmlTemplateScope_eid тип доступа для изменения
 * @property-read int $XmlTemplateScope_id тип доступа для видимости
 * @property-read int $LpuSection_id отделение автора
 * @property-read int $Lpu_id МО автора
 * @property-read string $LpuSection_Name
 * @property-read string $Lpu_Name
 * @property-read string $PMUser_Name
 *
 * @property-read array $valueList Список значений
 */
class ParameterValue_model extends SwPgModel
{
	private $_valueList = null;

	public static $listTypes = array(
		1	=> 'Комбобокс',
		2	=> 'ГруппаЧекбоксов',
		3	=> 'Радиогруппа',
	);

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
			self::SCENARIO_DELETE
		));
		$this->load->library('swXmlTemplate');
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'ParameterValue';
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'ParameterValue_id';
		$arr[self::ID_KEY]['label'] = 'Параметр';
		unset($arr['code']);
		$arr['name']['alias'] = 'ParameterValue_Name';
		$arr['name']['label'] = 'Наименование параметра для печати';
		$arr['name']['save'] = 'ban_percent|trim|required|max_length[400]';
		$arr['insdt']['alias'] = 'ParameterValue_insDT';
		$arr['upddt']['alias'] = 'ParameterValue_updDT';
		$arr['alias'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ParameterValue_Alias',
			'label' => 'Наименование параметра',
			'save' => 'ban_percent|trim|required|max_length[100]',
			'type' => 'string'
		);
		$arr['sysnick'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'ParameterValue_SysNick',
		);
		$arr['pid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'ParameterValue_pid',
		);
		$arr['parametervaluelisttype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ParameterValueListType_id',
			'label' => 'Тип списка значений',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['parametervaluelisttype_name'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
			),
			'alias' => 'ParameterValueListType_Name',
			'select' => 't.ParameterValueListType_Name as "ParameterValueListType_Name"',
			'join' => 'left join v_ParameterValueListType t on t.ParameterValueListType_id = {ViewName}.ParameterValueListType_id',
		);
		$arr['xmltemplatescope_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'XmlTemplateScope_id',
			'label' => 'Тип доступа для видимости',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['xmltemplatescope_eid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'XmlTemplateScope_eid',
			'label' => 'Тип доступа для изменения',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['pmuser_name'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
			),
			'alias' => 'PMUser_Name',
			'select' => "case when v_pmUserCache.PMUser_Login is null then ''
else rtrim(v_pmUserCache.PMUser_surName) ||' '||left(v_pmUserCache.PMUser_firName,1) || (case when length(v_pmUserCache.PMUser_firName) > 0 then '.' else '' end) || left(v_pmUserCache.PMUser_secName,1) || (case when length(v_pmUserCache.PMUser_secName) > 0 then '.' else '' end) ||  ' (' || rtrim(v_pmUserCache.PMUser_Login) || ')'
end as \"PMUser_Name\"",
			'join' => 'left join v_pmUserCache on v_pmUserCache.PMUser_id = {ViewName}.pmUser_insID',
		);
		$arr['lpu_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE
			),
			'alias' => 'Lpu_id',
			'select' => 'v_Lpu.Lpu_id as "Lpu_id"',
			'join' => 'left join v_Lpu on v_Lpu.Lpu_id = coalesce({ViewName}.Lpu_id, v_pmUserCache.Lpu_id)',
			'label' => 'МО автора',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpu_name'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
			),
			'alias' => 'Lpu_Name',
			'select' => 'v_Lpu.Lpu_Nick as "Lpu_Name"',
		);
		$arr['lpusection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE
			),
			'alias' => 'LpuSection_id',
			'select' => 'v_LpuSection.LpuSection_id as "LpuSection_id"',
			'join' => 'left join v_LpuSection on v_LpuSection.LpuSection_id = {ViewName}.LpuSection_id and v_LpuSection.Lpu_id = v_Lpu.Lpu_id',
			'label' => 'Отделение автора',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusection_name'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
			),
			'alias' => 'LpuSection_Name',
			'select' => 'v_LpuSection.LpuSection_FullName as "LpuSection_Name"',
		);
		return $arr;
	}

	/**
	 * Правила для контроллера для извлечения входящих параметров при сохранении
	 * @return array
	 */
	protected function _getSaveInputRules()
	{
		$all = parent::_getSaveInputRules();
		// параметры
		$all['values_change'] = array(
			'field' => 'values_change',
			'label' => 'Список измененных значений параметра в виде JSON строки',
			'rules' => 'trim|required',
			'type' => 'string'
		);
		return $all;
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
				$rules['alias'] = array(
					'field' => 'ParameterValue_Alias',
					'label' => 'Наименование параметра',
					'rules' => 'ban_percent|trim',
					'type' => 'string'
				);
				$rules['name'] = array(
					'field' => 'ParameterValue_Name',
					'label' => 'Наименование для печати',
					'rules' => 'ban_percent|trim',
					'type' => 'string'
				);
				$rules['ParameterValueListType_id'] = array(
					'field' => 'ParameterValueListType_id',
					'label' => 'Тип списка значений',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['start'] = array(
					'field' => 'start',
					'default' => 0,
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				);
				$rules['limit'] = array(
					'field' => 'limit',
					'default' => 100,
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				);
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
			case self::SCENARIO_DELETE:
				$rules['id'] = array(
					'field' => 'ParameterValue_id',
					'label' => 'Идентификатор параметра',
					'rules' => 'required',
					'type' => 'id'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		$this->_params['values_change'] = empty($data['values_change']) ? null : $data['values_change'];
		$this->_params['start'] = empty($data['start']) ? 0 : $data['start'];
		$this->_params['limit'] = empty($data['limit']) ? 100 : $data['limit'];
		$this->_params['LpuSection_id'] = empty($data['LpuSection_id']) ? null : $data['LpuSection_id'];
		if ( empty($this->_params['LpuSection_id']) && isset($data['session']['CurLpuSection_id']) ) {
			$this->_params['LpuSection_id'] = $data['session']['CurLpuSection_id'];
		}
		$this->_params['Lpu_id'] = empty($data['Lpu_id']) ? null : $data['Lpu_id'];
		if ( empty($this->_params['Lpu_id']) && isset($data['session']['lpu_id']) ) {
			$this->_params['Lpu_id'] = $data['session']['lpu_id'];
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();

		if (in_array($this->scenario, array(
				self::SCENARIO_LOAD_EDIT_FORM,
				self::SCENARIO_DELETE,
			)) && empty($this->id)
		) {
			throw new Exception('Не указан параметр', 400);
		}

		if (in_array($this->scenario, array(
				self::SCENARIO_LOAD_GRID,
				self::SCENARIO_DO_SAVE,
			))
			&& !empty($this->alias)
			&& false == preg_match('/^[а-яА-ЯёЁ]*$/u', $this->alias)
		) {
			throw new Exception('Наименование параметра может содержать только русские буквы!', 400);
		}

		if (in_array($this->scenario, array(
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,
		))) {
			if (empty($this->promedUserId)) {
				throw new Exception('Нет параметра pmUser_id!', 500);
			}
			if ( empty($this->_params['Lpu_id']) ) {
				throw new Exception('Нет параметра МО для проверки доступа!', 500);
			}
			if (empty($this->_params['LpuSection_id'])
				&& !isSuperadmin()
				&& !isLpuAdmin($this->_params['Lpu_id'])
			) {
				throw new Exception('Нет параметра отделение для проверки доступа!', 500);
			}
		}

		if (in_array($this->scenario, array(
			self::SCENARIO_DO_SAVE
		))) {
			if ( $this->_isAttributeChanged('lpu_id') && false == $this->isNewRecord ) {
				throw new Exception('Нельзя изменить МО автора', 400);
			}
			if ( $this->_isAttributeChanged('lpusection_id') && false == $this->isNewRecord ) {
				throw new Exception('Нельзя изменить отделение автора', 400);
			}
		}

		if ($this->scenario == self::SCENARIO_LOAD_GRID) {
			if (empty($this->_params['start'])) {
				$this->_params['start'] = 0;
			}
			if (empty($this->_params['limit'])) {
				$this->_params['limit'] = 100;
			}
			if ($this->_params['start'] < 0 || $this->_params['limit'] > 100) {
				throw new Exception('Неправильные параметры пейджинга!', 400);
			}
		}

		// проверяем доступ для изменения
		if (in_array($this->scenario, array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,
		)) && false == $this->_hasAccessWrite()
		) {
			throw new Exception('Нет доступа на редактирование/удаление.', 403);
		}

		if ($this->scenario == self::SCENARIO_DO_SAVE) {
			if (empty($this->_params['values_change'])) {
				throw new Exception('Нужно указать изменения значений параметра!', 400);
			}

			ConvertFromWin1251ToUTF8($this->_params['values_change']);
			$this->_valueList = json_decode($this->_params['values_change'], true);
			$valuesCnt = 0;
			foreach ($this->valueList as $val) {
				if (empty($val['ParameterValue_status'])
					|| !in_array($val['ParameterValue_status'], array('saved','deleted','inserted','changed'))
					|| (in_array($val['ParameterValue_status'], array('inserted','changed')) && empty($val['ParameterValue_Name']))
					|| (in_array($val['ParameterValue_status'], array('deleted','changed')) && empty($val['ParameterValue_id']))
				) {
					throw new Exception('Неправильный формат списка значений параметра!', 400);
				}
				if ($val['ParameterValue_status'] != 'deleted') {
					$valuesCnt++;
				}
			}
			if (0 == $valuesCnt) {
				throw new Exception('Нужно указать значения параметра!', 400);
			}

			if (empty($this->alias)) {
				throw new Exception('Наименование параметра обязательно к заполнению!', 400);
			}
			// проверяем дублирование параметра
			$params = array();
			$filters = '';
			if (!empty($this->id)) {
				$filters = ' and v_ParameterValue.ParameterValue_id != :ParameterValue_id';
				$params['ParameterValue_id'] = $this->id;
			}
			$query = $this->_getLoadQuery('', '', $filters, $params);
			$result = $this->db->query($query['sql'], $query['params']);
			if ( !is_object($result) ) {
				throw new Exception('Проверка дублирования. Ошибка БД.', 500);
			}
			$tmp = $result->result('array');
			if (count($tmp) > 0) {
				throw new Exception('Данный параметр уже имеется!', 400);
				// . getDebugSQL($query['sql'], $query['params'])
			}
		}

	}

	/**
	 *  Проверка доступа на добавление, редактирование или удаление
	 */
	protected function _hasAccessWrite()
	{
		return swXmlTemplate::hasAccessWrite($this->promedUserId, $this->_params['Lpu_id'], $this->_params['LpuSection_id'], $this->_savedData, $this->id);
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
			'ParameterValue_id' => $this->id,
			'ParameterValue_Alias' => $this->alias,
			'ParameterValue_Name' => $this->name,
			'ParameterValueListType_id' => $this->ParameterValueListType_id,
			'XmlTemplateScope_id' => $this->XmlTemplateScope_id,
			'XmlTemplateScope_eid' => $this->XmlTemplateScope_eid,
			'ParameterValue_SysNick' => $this->sysNick,
			'values_change' => json_encode($this->valueList),
			'Lpu_id' => $this->Lpu_id,
			'LpuSection_id' => $this->LpuSection_id,
			'LpuSection_Name' => $this->LpuSection_Name,
			'Lpu_Name' => $this->Lpu_Name,
			'PMUser_Name' => $this->PMUser_Name,
			'pmUser_insID' => $this->pmUser_insID,
		));
	}

	/**
	 * Возвращает список значений параметра
	 * @return array
	 */
	function getValueList()
	{
		if (!isset($this->_valueList)) {
			$query = "
				Select
					ParameterValue_id as \"ParameterValue_id\",
					ParameterValue_pid as \"ParameterValue_pid\",
					ParameterValue_Name as \"ParameterValue_Name\",
					'saved' as \"ParameterValue_status\"
				from
					v_ParameterValue
				where
					ParameterValue_pid = :id
			";
			$result = $this->db->query($query, array('id' => $this->id));
			if ( is_object($result) ) {
				$this->_valueList = $result->result('array');
			} else {
				throw new Exception('Ошибка запроса списка значений параметра', 500);
			}
		}
		return $this->_valueList;
	}

	/**
	 * Формируется запрос списка параметров с учетом фильтров
	 */
	private function _getLoadQuery($add_select = '', $add_join = '', $filters = '', $params = array())
	{
		$query = array();
		$query['params'] = array_merge($params,
			swXmlTemplate::getAccessRightsQueryParams($this->_params['Lpu_id'], $this->_params['LpuSection_id'], $this->promedUserId)
		);
		if (!empty($this->alias)) {
			$filters .= "
				and v_ParameterValue.ParameterValue_Alias ilike :alias";
			$query['params']['alias'] = '%' . $this->alias . '%';
		}
		if (!empty($this->name)) {
			$filters .= "
				and v_ParameterValue.ParameterValue_Name ilike :name";
			$query['params']['name'] = '%' . $this->name . '%';
		}
		if (!empty($this->ParameterValueListType_id)) {
			$filters .= "
				and v_ParameterValue.ParameterValueListType_id = :ParameterValueListType_id";
			$query['params']['ParameterValueListType_id'] = $this->ParameterValueListType_id;
		}
		$visibleFilter = swXmlTemplate::getAccessRightsQueryPart('v_ParameterValue', 'ParameterValue', true);
		$query['sql'] = "
			Select
				-- select
				v_ParameterValue.ParameterValue_id as \"ParameterValue_id\" {$add_select}
				-- end select
			from
				-- from
				v_ParameterValue {$add_join}
				-- end from
			where
				-- where
				v_ParameterValue.ParameterValue_pid is null {$filters}
				and {$visibleFilter}
				-- end where
			order by
				-- order by
				v_ParameterValue.ParameterValue_Alias
				-- end order by
		";
		return $query;
	}

	/**
	 *  Читает часть данных (используя пейджинг)
	 */
	function doLoadGrid($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_GRID;
		$this->applyData($data);
		$this->_validate();
		$accessType = swXmlTemplate::getAccessRightsQueryPart('v_ParameterValue', 'ParameterValue', false);
		$query = $this->_getLoadQuery(",
				{$accessType} as \"accessType\",
				v_ParameterValue.ParameterValue_Alias as \"ParameterValue_Alias\",
				v_ParameterValue.ParameterValue_Name as \"ParameterValue_Name\",
				ListType.ParameterValueListType_Name as \"ParameterValueListType_Name\",
				(select COUNT(val.ParameterValue_id) from v_ParameterValue val where val.ParameterValue_pid = v_ParameterValue.ParameterValue_id) as \"ParameterValue_valueCnt\",
				v_ParameterValue.ParameterValueListType_id as \"ParameterValueListType_id\",
				v_ParameterValue.ParameterValue_SysNick as \"ParameterValue_SysNick\"
		", '
				left join v_ParameterValueListType ListType on ListType.ParameterValueListType_id = v_ParameterValue.ParameterValueListType_id');
		/*
		echo getDebugSql(getLimitSQLPH($query['sql'], $data['start'], $data['limit']), $query['params']);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($query['sql'], $data['start'], $data['limit']), $query['params']);
		if (!is_object($result)) {
			return false;
		}
		$response = array();
		$response['data'] = $result->result('array');
		$response['totalCount'] = count($response['data']);
		if ($this->_params['limit'] == $response['totalCount']) {
			$result_count = $this->db->query(getCountSQLPH($query['sql']), $query['params']);
			if (!is_object($result_count)) {
				return false;
			}
			$cnt_arr = $result_count->result('array');
			$response['totalCount'] = $cnt_arr[0]['cnt'];
		}
		foreach ($response['data'] as &$row) {
			$row['ParameterValue_SysNick'] = $this->getParameterFieldName($row['ParameterValue_id']);
			$row['ParameterValue_Marker'] = $this->getParameterMarker($row['ParameterValue_id'], $row['ParameterValueListType_id'], $row['ParameterValue_Alias']);
		}
		return $response;
	}

	/**
	 * Генерирует системное имя объекта с типом Параметр и список значений также как ParameterValue_SysNick генерируется в хранимках
	 *
	 * Это имя используется в шаблоне, схеме, в документе, в компоненте редактирования
	 *
	 * @access	private
	 * @param	int		$parameter_id Идентификатор параметра
	 * @return	string
	 */
	private function getParameterFieldName($parameter_id)
	{
		if (empty($parameter_id)) {
			return '';
		}
		return ('parameter'.$parameter_id);
	}

	/**
	 * Генерирует имя маркера для объекта с типом Параметр и список значений
	 *
	 * Это имя используется в шаблоне
	 *
	 * @access	public
	 * @param	int		    $parameter_id Идентификатор параметра
	 * @param	int		    $parametervaluelisttype_id Идентификатор типа списка
	 * @param	string		$parametervalue_alias Наименование параметра
	 * @return	string
	 */
	function getParameterMarker($parameter_id, $parametervaluelisttype_id, $parametervalue_alias)
	{
		if (!$parameter_id || !$parametervalue_alias || !$parametervaluelisttype_id) {
			return '';
		}
		if (empty(self::$listTypes[$parametervaluelisttype_id])) {
			return '';
		}
		return ('@#@_'.$parameter_id.self::$listTypes[$parametervaluelisttype_id].$parametervalue_alias);
	}

	/**
	 * Возвращает массив параметров с конфигурацией полей
	 *
	 * @access	public
	 * @param	array	$markers Массив созданный в swMarker::foundParameterMarkers
	 * @return	array
	 */
	public function getParameterFieldData($markers)
	{
		$result = array();
		$id_list = array();
		$params = array();
		foreach($markers as $row) {
			$id = intval($row['Parameter_id']);
			$id_list[] = $id;
			$params[$id] = $row;
		}
		if(empty($id_list))
		{
			return $result;
		}
		$id_list = implode(',',$id_list);
		$query = '
			select
				Parameter.ParameterValue_id as "Parameter_id"
				,Parameter.ParameterValue_Name as "Parameter_Name" 
				,Parameter.ParameterValue_SysNick as "Parameter_SysNick"
				,Parameter.ParameterValueListType_id as "ParameterValueListType_id"
				,Value.ParameterValue_id as "Value_id"
				,Value.ParameterValue_Name as "Value_Name"
			from 
				v_ParameterValue Parameter
				inner join v_ParameterValue Value on Value.ParameterValue_pid = Parameter.ParameterValue_id
			where
				Parameter.ParameterValue_id in ('.$id_list.')
			order by
				Parameter.ParameterValue_id
		';
		//return getDebugSql($query);
		$res = $this->db->query($query);
		if ( is_object($res) )
		{
			$tmp = $res->result('array');
			foreach($tmp as $row) {
				$id = intval($row['Parameter_id']);
				if(empty($result[$id]))
				{
					$result[$id] = array(
						'Parameter_id' => $row['Parameter_id']
						,'Parameter_Name' => $row['Parameter_Name']
						,'Parameter_SysNick' => $row['Parameter_SysNick']
						,'ParameterValueListType_id' => $params[$id]['ParameterValueListType_id']
						,'marker' => $params[$id]['marker']
						,'field_name' => $this->getParameterFieldName($row['Parameter_id'])
						,'values' => array()
					);
				}
				$result[$id]['values'][intval($row['Value_id'])] = addslashes($row['Value_Name']);
			}
			$r2 = array();
			foreach($markers as $row) {
				if(isset($result[$row['Parameter_id']])) {
					$id = $row['Parameter_id'] . (empty($row['Parameter_suffix']) ? '' : "_{$row['Parameter_suffix']}");
					$r2[$id] = $result[$row['Parameter_id']];
					$r2[$id]['marker'] = $row['marker'];
					if (!empty($row['Parameter_suffix'])) {
						$r2[$id]['Parameter_SysNick'] .= "_{$row['Parameter_suffix']}";
						$r2[$id]['field_name'] .= "_{$row['Parameter_suffix']}";
					}
				}
			}
			$result = $r2;
		}
		return $result;
	}

	/**
	 * Возвращает массив с конфигурацией полей для шаблона
	 *
	 * @param	array	$markers Массив созданный в swMarker::foundParameterMarkers
	 * @return	array
	 */
	public function getXmlTemplateFieldData($markers, $xmlData = array())
	{
		$result = array();
		foreach($markers as $row)
		{
			//@to-do достать из ParameterValue название параметра для fieldLabel
			//@to-do указать xtype, дополнительные параметры конфигурации в зависимости от $row['ParameterValueListType_id'] - это сейчас не надо т.к. документ редактируется только в ЭМК
			$value = null;
			$xtype = null;
			$data = array(); //array(array(value_id,value_name),array(value_id,value_name),array(value_id,value_name))
			switch(intval($row['ParameterValueListType_id'])) {
				case 1: 
					$xtype = 'swparametervaluecombo';
					$comboData = $data;
					break;
				case 2: 
					$xtype = 'swparametervaluecheckboxgroup';
					$itemsData = $data;
					break;
				case 3: 
					$xtype = 'swparametervalueradiogroup';
					$itemsData = $data;
					break;
			}
			$id = $this->getParameterFieldName($row['Parameter_id']) . (empty($row['Parameter_suffix']) ? '' : "_{$row['Parameter_suffix']}");
			$result[] = array(
				'id' => $id,
				'fieldLabel'=>'',
				//'fieldLabel'=>$row['Parameter_Name'],
				'xtype'=>$xtype,
				'hideLabel'=>'true',
				'defaultValue'=>isset($xmlData[$id])?$xmlData[$id]:null,
			);
		}
		return $result;
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
		// указываем не редактируемые значения
		$this->setAttribute('pid', null);
		// ParameterValue_SysNick записывается в хранимке
		// if (!$this->isNewRecord) $this->setAttribute('sysnick', $this->getParameterFieldName($this->id));
		// указываем параметры для проверки доступа
		/*
		if (!isSuperadmin()) {
			// при редактировании суперадмином параметры для проверки доступа не должны затереться
			if ($this->isNewRecord || empty($this->Lpu_id)) {
				$this->setAttribute('Lpu_id', $this->_params['Lpu_id']);
			}
			if (!isLpuAdmin($this->_params['Lpu_id']) && ($this->isNewRecord || empty($this->LpuSection_id))) {
				// при редактировании админом ЛПУ отделение автора не должно затереться
				$this->setAttribute('LpuSection_id', $this->_params['LpuSection_id']);
			}
		}
		*/
		// параметры для проверки доступа записываются только при создании записи
		if ($this->isNewRecord) {
			$this->setAttribute('Lpu_id', $this->_params['Lpu_id']);
			$this->setAttribute('LpuSection_id', $this->_params['LpuSection_id']);
		}
		// корректируем свойства видимости/доступности для редактирования в зависимости от наличия параметров для проверки доступа
		if (empty($this->Lpu_id) && (3 == $this->XmlTemplateScope_id || 4 == $this->XmlTemplateScope_id)) {
			// Нельзя выбрать МО автора, отделение автора, если исторически не записан Lpu_id
			$this->setAttribute('XmlTemplateScope_id', 5);
		}
		if (empty($this->LpuSection_id) && 4 == $this->XmlTemplateScope_id) {
			// Нельзя выбрать отделение автора, если исторически не записан LpuSection_id
			$this->setAttribute('XmlTemplateScope_id', 3);
		}
		//свойство редактирования всегда должно быть более жестким, либо таким же
		if ($this->XmlTemplateScope_id == 1) {
			// Видимость Суперадмин - редактировать только Суперадмин
			$this->setAttribute('XmlTemplateScope_eid', $this->XmlTemplateScope_id);
		}
		if ($this->XmlTemplateScope_id > 2 && $this->XmlTemplateScope_eid < $this->XmlTemplateScope_id) {
			/*
			Видимость Автор - редактировать только автор, если редактировать было не только автор
			Видимость отделение автора - редактировать отделения автора, если редактировать было не только автор или отделение автора
			Видимость МО автора - редактировать МО автора, если редактировать было не только автор или отделение автора или МО автора
			 */
			$this->setAttribute('XmlTemplateScope_eid', $this->XmlTemplateScope_id);
		}
	}

	/**
	 * Логика после успешного сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		//throw new Exception(var_export($this->valueList, true));
		//Сохранение/удаление значений
		foreach ($this->valueList as $i => $val) {
			$params = array();
			array_walk($val, 'ConvertFromUTF8ToWin1251');
			if ('changed' == $val['ParameterValue_status']) {
				$params['ParameterValue_id'] = array(
					'value' => $val['ParameterValue_id'],
					'out' => true,
					'type' => 'bigint',
				);
				$params['ParameterValue_pid'] = $this->id;
				$params['ParameterValue_Name'] = $val['ParameterValue_Name'];
				$params['pmUser_id'] = $this->promedUserId;
				$tmp = $this->_save($params);
			}
			if ('inserted' == $val['ParameterValue_status']) {
				$params['ParameterValue_id'] = array(
					'value' => null,
					'out' => true,
					'type' => 'bigint',
				);
				$params['ParameterValue_pid'] = $this->id;
				$params['ParameterValue_Name'] = $val['ParameterValue_Name'];
				$params['pmUser_id'] = $this->promedUserId;
				$tmp = $this->_save($params);
				$val['ParameterValue_id'] = $tmp[0]['ParameterValue_id'];
			}
			if ('deleted' == $val['ParameterValue_status']) {
				$params['ParameterValue_id'] = $val['ParameterValue_id'];
				$tmp = $this->_delete($params);
			}
			$this->_saveResponse[$val['ParameterValue_status'].'Value_id'.$i] = $val['ParameterValue_id'];
		}
		$this->_saveResponse['ParameterValue_Alias'] = $this->alias;
		$this->_saveResponse['ParameterValue_SysNick'] = $this->getParameterFieldName($this->id);
		$this->_saveResponse['ParameterValue_Marker'] = $this->getParameterMarker($this->id, $this->ParameterValueListType_id, $this->alias);
	}
	
	/**
	 * @return array|false
	 */
	function getTypeList() {
		$query = "
			select
				PVLT.ParameterValueListType_id as \"ParameterValueListType_id\",
				PVLT.ParameterValueListType_Code as \"ParameterValueListType_Code\",
				PVLT.ParameterValueListType_Name as \"ParameterValueListType_Name\",
				PVLT.ParameterValueListType_SysNick as \"ParameterValueListType_SysNick\"
			from v_ParameterValueListType PVLT
		";
		return $this->queryResult($query);
	}
}