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
 * Модель папок шаблонов.
 *
 * @package		XmlTemplateCat
 * @author		Александр Пермяков
 *
 * @property int $pid
 * @property int $code
 * @property string $name Наименование папки
 * @property int $XmlTemplateScope_id Видимость
 * @property int $XmlTemplateScope_eid Доступность для изменения
 * @property int $Lpu_id Принадлежность к ЛПУ. Используется для контроля доступа
 * @property int $LpuSection_id Принадлежность к отделению ЛПУ. Используется для контроля доступа
 * @property int $Server_id Источник данных
 * @property-read string $LpuSection_Name
 * @property-read string $Lpu_Name
 * @property-read string $PMUser_Name
 *
 * @property XmlTemplateCatDefault_model $XmlTemplateCatDefault_model
 */
class XmlTemplateCat_model extends SwPgModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = false;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_LOAD_EDIT_FORM,
			self::SCENARIO_LOAD_COMBO_BOX,
			self::SCENARIO_DELETE,
		));
		$this->load->library('swXmlTemplate');
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'XmlTemplateCat';
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
				'alias' => 'XmlTemplateCat_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			),
			'code' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL,
				),
				'alias' => 'XmlTemplateCat_Сode',
				'label' => 'Код',
				'save' => 'trim',
				'type' => 'int',
			),
			'name' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL,
				),
				'alias' => 'XmlTemplateCat_Name',
				'label' => 'Наименование',
				'save' => 'trim|required',
				'type' => 'string'
			),
			'pid' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'XmlTemplateCat_pid',
				'label' => 'Папка верхнего уровня',
				'save' => 'trim',
				'type' => 'id'
			),
			'xmltemplatescope_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'XmlTemplateScope_id',
				'label' => 'Видимость',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'xmltemplatescope_eid' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'XmlTemplateScope_eid',
				'label' => 'Доступность для изменения',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'pmuser_name' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'PMUser_Name',
				'select' => "case when v_pmUserCache.PMUser_Login is null then ''
else rtrim(v_pmUserCache.PMUser_surName) ||' '||left(v_pmUserCache.PMUser_firName,1) || (case when length(v_pmUserCache.PMUser_firName) > 0 then '.' else '' end) || left(v_pmUserCache.PMUser_secName,1) || (case when length(v_pmUserCache.PMUser_secName) > 0 then '.' else '' end) ||  ' (' || rtrim(v_pmUserCache.PMUser_Login) || ')'
end as \"PMUser_Name\"",
				'join' => 'left join v_pmUserCache on v_pmUserCache.PMUser_id = {ViewName}.pmUser_insID',
			),
			'lpu_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'Lpu_id',
				'select' => 'v_Lpu.Lpu_id as "Lpu_id"',
				'join' => 'left join v_Lpu on v_Lpu.Lpu_id = coalesce({ViewName}.Lpu_id, v_pmUserCache.Lpu_id)',
				'label' => 'МО автора',
				'save' => 'trim',
				'type' => 'id'
			),
			'lpu_name' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'Lpu_Name',
				'select' => 'v_Lpu.Lpu_Nick as "Lpu_Name"',
			),
			'lpusection_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'LpuSection_id',
				'select' => 'v_LpuSection.LpuSection_id as "LpuSection_id"',
				'join' => 'left join v_LpuSection on v_LpuSection.LpuSection_id = {ViewName}.LpuSection_id and v_LpuSection.Lpu_id = v_Lpu.Lpu_id',
				'label' => 'Отделение автора',
				'save' => 'trim',
				'type' => 'id'
			),
			'lpusection_name' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'LpuSection_Name',
				'select' => 'v_LpuSection.LpuSection_FullName as "LpuSection_Name"',
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
			'server_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
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
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);
		switch ($name) {
			case 'createDefault':
				$rules = array(
					array('field' => 'XmlType_id','label' => 'Идентификатор типа документа','rules' => 'trim|required','type' => 'id'),
					array('field' => 'EvnClass_id','label' => 'Идентификатор категории документа','rules' => 'trim|required','type' => 'id'),
					array('field' => 'MedStaffFact_id','label' => 'Идентификатор рабочего места','rules' => 'trim','type' => 'id'),
					array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'trim','type' => 'id'),
					array('field' => 'MedPersonal_id','label' => 'Идентификатор врача','rules' => 'trim','type' => 'id'),
					array('field' => 'LpuSection_id','label' => 'Идентификатор отделения пользователя','rules' => 'trim','type' => 'id'),
				);
				break;
			case self::SCENARIO_DELETE:
				$rules = array(
					array('field' => 'except_list','label' => 'Список папок по умолчанию, которые не надо проверять','rules' => 'trim','type' => 'string'),
					array('field' => 'LpuSection_id','label' => 'Идентификатор отделения пользователя','rules' => 'trim','type' => 'id'),
					array('field' => 'XmlTemplateCat_id','label' => 'Идентификатор папки','rules' => 'trim|required','type' => 'id'),
				);
				break;
			case self::SCENARIO_LOAD_COMBO_BOX:
				$rules = array(
					array('field' => 'LpuSection_id','label' => 'Идентификатор отделения пользователя','rules' => 'trim','type' => 'id'),
					array('field' => 'XmlTemplateCat_id','label' => 'Идентификатор папки','rules' => 'trim','type' => 'id'),
					array('field' => 'XmlTemplateCat_pid','label' => 'Идентификатор папки','rules' => 'trim','type' => 'id'),
					array('field' => 'query','label' => 'Строка поиска','rules' => 'ban_percent|trim','type' => 'string'),
					array('field' => 'needMaxLevelFilter','label' => 'Надо ли исключать папки по уровню вложенности','rules' => 'trim','type' => 'id'),
				);
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
				$rules = array(
					array('field' => 'XmlTemplateCat_id','label' => 'Идентификатор папки','rules' => 'trim|required','type' => 'id'),
				);
				break;
		}
		return $rules;
	}

	/**
	 * Правила для контроллера для извлечения входящих параметров при сохранении
	 * @return array
	 */
	protected function _getSaveInputRules()
	{
		$all = parent::_getSaveInputRules();
		//Параметры для проверки папки верхнего уровня
		$all['MedService_id'] = array(
			'field' => 'MedService_id', 'label' => 'MedService_id',
			'rules' => 'trim', 'type' => 'id',
		);
		$all['MedPersonal_id'] = array(
			'field' => 'MedPersonal_id', 'label' => 'MedPersonal_id',
			'rules' => 'trim', 'type' => 'id',
		);
		$all['MedStaffFact_id'] = array(
			'field' => 'MedStaffFact_id', 'label' => 'MedStaffFact_id',
			'rules' => 'trim', 'type' => 'id',
		);
		$all['EvnClass_id'] = array(
			'field' => 'EvnClass_id', 'label' => 'EvnClass_id',
			'rules' => 'trim', 'type' => 'id',
		);
		$all['XmlType_id'] = array(
			'field' => 'XmlType_id', 'label' => 'XmlType_id',
			'rules' => 'trim', 'type' => 'id',
		);
		return $all;
	}

	/**
	 * Список служебных параметров, которые должны быть получены из входящих параметров
	 * @var array
	 */
	protected $_paramNames = array(
		'MedService_id',
		'MedPersonal_id',
		'EvnClass_id',
		'XmlType_id',
		'MedStaffFact_id',
	);

	/**
	 * Извлечение значений служебных параметров модели из входящих параметров
	 * @param array $data
	 * @throws Exception
	 */
	public function setParams($data)
	{
		parent::setParams($data);
		foreach ($this->_paramNames as $key) {
			if (array_key_exists($key, $data)) {
				$this->_params[$key] = $data[$key];
			} else {
				$this->_params[$key] = null;
			}
		}
		$this->_params['LpuSection_id'] = empty($data['LpuSection_id']) ? null : $data['LpuSection_id'];
		if ( empty($this->_params['LpuSection_id']) && isset($data['session']['CurLpuSection_id']) ) {
			$this->_params['LpuSection_id'] = $data['session']['CurLpuSection_id'];
		}
		$this->_params['Lpu_id'] = empty($data['Lpu_id']) ? null : $data['Lpu_id'];
		if ( empty($this->_params['Lpu_id']) && isset($data['session']['lpu_id']) ) {
			$this->_params['Lpu_id'] = $data['session']['lpu_id'];
		}
		if ( empty($this->_params['MedStaffFact_id']) && isset($data['session']['CurMedStaffFact_id']) ) {
			$this->_params['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}

		if (in_array($this->scenario, array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,
		))) {
			if ( empty($this->_params['LpuSection_id']) && isset($data['session']['CurLpuSection_id']) ) {
				$this->_params['LpuSection_id'] = $data['session']['CurLpuSection_id'];
			}
		}
		if (self::SCENARIO_DELETE == $this->scenario) {
			if (empty($data['except_list'])) {
				$this->_params['except_list'] = array();
			} else {
				$this->_params['except_list'] = explode(',', $data['except_list']);
				foreach($this->_params['except_list'] as $id) {
					if (false == is_numeric($id)) {
						throw new Exception('Неправильные параметры');
					}
				}
			}
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 */
	protected function _validate()
	{
		parent::_validate();
		
		if (in_array($this->scenario, array(
			self::SCENARIO_AUTO_CREATE,
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
			if (false == swXmlTemplate::hasAccessWrite($this->promedUserId,
				$this->_params['Lpu_id'], $this->_params['LpuSection_id'],
				$this->_savedData, $this->id)
			) {
				throw new Exception('Нет доступа на редактирование/удаление папки', 403);
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
			if (empty($this->name)) {
				throw new Exception('Вы не указали наименование папки', 400);
			}
			if (empty($this->pid) && false /*== swXmlTemplate::isAllowRootFolder($this->sessionParams)*/) {
				throw new Exception('Вы не указали папку верхнего уровня', 400);
			}
			if (false == swXmlTemplate::isDisableDefaults($this->sessionParams)
				&& (!empty($this->pid) && $this->_isAttributeChanged('pid'))
			) {
				$this->load->model('XmlTemplateCatDefault_model');
				$this->load->model('XmlTemplateCat_model', 'pidModel');
				$data = swXmlTemplate::checkFolder($this->XmlTemplateCatDefault_model,
					$this->pidModel, array(
						'XmlTemplateCat_eid' => $this->id,
						'XmlTemplateCat_id' => $this->pid,
						'session' => $this->sessionParams,
						'MedStaffFact_id' => $this->_params['MedStaffFact_id'],
						'LpuSection_id'=> $this->_params['LpuSection_id'],
						'MedService_id' => $this->_params['MedService_id'],
						'EvnClass_id' => $this->_params['EvnClass_id'],
						'XmlType_id' => $this->_params['XmlType_id'],
					)
				);
				$this->setAttribute('pid', $data['XmlTemplateCat_id']);
			}
		}

		if (self::SCENARIO_AUTO_CREATE == $this->scenario) {
			if (empty($this->_params['EvnClass_id'])) {
				throw new Exception('Вы не указали категорию папки', 400);
			}
			if (empty($this->_params['XmlType_id'])) {
				throw new Exception('Вы не указали тип документов', 400);
			}
			$emptyKey = true;
			if ( isset($this->_params['MedStaffFact_id']) ) {
				$tmp = $this->getFirstRowFromQuery("
					select
						msf.MedPersonal_id as \"MedPersonal_id\",
						msf.LpuSection_id as \"LpuSection_id\",
						msf.Person_Fin as \"Person_Fin\",
						v_Lpu.Lpu_Nick as \"Lpu_Nick\"
					from
						dbo.v_MedStaffFact msf
						inner join dbo.v_Lpu on v_Lpu.Lpu_id = msf.Lpu_id
					where
						msf.MedStaffFact_id = :id and msf.Lpu_id = :Lpu_id
					limit 1
				", array(
					'Lpu_id'=>$this->_params['Lpu_id'],
					'id' => $this->_params['MedStaffFact_id']
				));
				if (empty($tmp)) {
					throw new Exception('Рабочее место не найдено', 400);
				}
				$this->_params['MedPersonal_id'] = $tmp['MedPersonal_id'];
				$this->setAttribute('lpusection_id', $tmp['LpuSection_id']);
				$this->setAttribute('name', $tmp['Person_Fin'] . ' - ' . $tmp['Lpu_Nick']);
				$emptyKey = false;
			} else if ( isset($this->_params['MedService_id']) && isset($this->_params['MedPersonal_id']) ) {
				$tmp = $this->getFirstRowFromQuery("
					select
						ms.LpuSection_id as \"LpuSection_id\",
						mp.Person_Fin as \"Person_Fin\",
						v_Lpu.Lpu_Nick as \"Lpu_Nick\"
					from
						dbo.v_MedServiceMedPersonal msmp
						inner join dbo.v_MedService ms on ms.MedService_id = msmp.MedService_id
							and ms.Lpu_id = :Lpu_id
						inner join dbo.v_MedPersonal mp on mp.MedPersonal_id = msmp.MedPersonal_id
							and mp.Lpu_id = ms.Lpu_id
						inner join dbo.v_Lpu on v_Lpu.Lpu_id = ms.Lpu_id
					where
						msmp.MedService_id = :MedService_id
						and msmp.MedPersonal_id = :MedPersonal_id
					limit 1
				", array(
					'Lpu_id'=>$this->_params['Lpu_id'],
					'MedService_id' => $this->_params['MedService_id'],
					'MedPersonal_id' => $this->_params['MedPersonal_id'],
				));
				if (empty($tmp)) {
					throw new Exception('Сотрудник службы не найден', 400);
				}
				$this->setAttribute('lpusection_id', $tmp['LpuSection_id']);
				$this->setAttribute('name', $tmp['Person_Fin'] . ' - ' . $tmp['Lpu_Nick']);
				$emptyKey = false;
			}
			if ( $emptyKey ) {
				throw new Exception('Не указана связка место работы/служба+врач', 500);
			}
		}

		if (self::SCENARIO_DELETE == $this->scenario) {
			// проверяем возможность удаления
			$params = array(
				'Lpu_id'=>$this->_params['Lpu_id'],
				'id' => $this->id
			);
			$params = array_merge($params,
				swXmlTemplate::getAccessRightsQueryParams($this->_params['Lpu_id'], $this->_params['LpuSection_id'], $this->promedUserId)
			);
			$accessType = swXmlTemplate::getAccessRightsQueryPart('xtc', 'XmlTemplateCat', false);
			$except = '';
			if (!empty($this->_params['except_list'])) {
				$except = ' and xtcd.XmlTemplateCatDefault_id not in (' . implode(',', $this->_params['except_list']) . ')';
			}
			$tmp = $this->getFirstRowFromQuery("
				select
					{$accessType} as \"accessType\",
					xtcd.Lpu_Nick as \"Lpu_Nick\",
					xtcd.Person_Fio as \"Person_Fio\",
					xtcd.MedService_Name as \"MedService_Name\",
					xtcd.LpuSection_Name as \"LpuSection_Name\",
					xtcd.XmlTemplateCatDefault_id as \"XmlTemplateCatDefault_id\"
				from XmlTemplateCat xtc
				left join lateral (
					select
					xtcd.XmlTemplateCatDefault_id,
					Lpu.Lpu_Nick,
					Medservice.MedService_Name,
					LpuSection.LpuSection_Name,
					mp.Person_Fio
					from XmlTemplateCatDefault xtcd
					left join v_MedStaffFact msf on msf.MedStaffFact_id = xtcd.MedStaffFact_id
					left join Medservice on Medservice.Medservice_id = xtcd.Medservice_id
					left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(xtcd.Lpu_id,Medservice.Lpu_id, msf.Lpu_id)
					left join LpuSection on LpuSection.LpuSection_id = coalesce(xtcd.LpuSection_id,Medservice.LpuSection_id, msf.LpuSection_id)
					left join v_pmUserCache puc on puc.PMUser_id = xtcd.pmUser_insID
					left join v_MedPersonal mp on mp.MedPersonal_id = coalesce(xtcd.MedPersonal_id,msf.MedPersonal_id,puc.MedPersonal_id)
						and mp.Lpu_id = Lpu.Lpu_id
					where xtcd.XmlTemplateCat_id = xtc.XmlTemplateCat_id {$except}
					order by case when Lpu.Lpu_id = :Lpu_id then 2 else 1 end
					limit 1
				) xtcd on true
				where xtc.XmlTemplateCat_id = :id
				and not exists (
					select ch.XmlTemplateCat_id
					from (
						(select ch.XmlTemplateCat_id
						from v_XmlTemplateCat ch
						where ch.XmlTemplateCat_pid = xtc.XmlTemplateCat_id
						limit 1)
						union all
						(select ch.XmlTemplateCat_id
						from v_XmlTemplate ch
						where ch.XmlTemplateCat_id = xtc.XmlTemplateCat_id
							and coalesce(ch.XmlTemplate_IsDeleted,1) = 1
						limit 1)
					) ch
					limit 1
				)
			", $params);
			if (empty($tmp)) {
				throw new Exception('Папка не может быть удалена, т.к. содержит папки или шаблоны', 400);
			}
			if ('view' == $tmp['accessType']) {
				throw new Exception('Удаление/редактирование папки запрещено', 400);
			}
			if (isset($tmp['XmlTemplateCatDefault_id'])) {
				$this->_params['except_list'][] = $tmp['XmlTemplateCatDefault_id'];
				$this->_saveResponse['except_list'] = implode(',', $this->_params['except_list']);
				$this->_saveResponse['Alert_Msg'] = 'Папка используется по умолчанию';
				if (isset($tmp['Person_Fio'])) {
					$this->_saveResponse['Alert_Msg'] .= ' пользователем ' . $tmp['Person_Fio'];
				} else {
					$this->_saveResponse['Alert_Msg'] .= ' неизвестным пользователем';
				}
				if (isset($tmp['MedService_Name'])) {
					$this->_saveResponse['Alert_Msg'] .= ' службы ' . $tmp['MedService_Name'];
				}
				if (isset($tmp['LpuSection_Name'])) {
					$this->_saveResponse['Alert_Msg'] .= ' отделения ' . $tmp['LpuSection_Name'];
				}
				if (isset($tmp['Lpu_Nick'])) {
					$this->_saveResponse['Alert_Msg'] .= ' МО ' . $tmp['Lpu_Nick'];
				}
				throw new Exception('YesNo', 400);
			}
		}
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = array())
	{
		parent::_beforeDelete($data);
		if (!empty($this->_params['except_list'])) {
			// удаляем папки по умолчанию
			$this->load->model('XmlTemplateCatDefault_model');
			// могут быть региональные модели типа ufa_XmlTemplateCatDefault_model, наследующие XmlTemplateCatDefault_model
			$className = get_class($this->XmlTemplateCatDefault_model);
			foreach($this->_params['except_list'] as $id) {
				/**
				 * @var XmlTemplateCatDefault_model $instance
				 */
				$instance = new $className();
				$tmp = $instance->doDelete(array(
					'XmlTemplateCatDefault_id' => $id,
					'session' => $this->sessionParams,
				), false);
				if (false == empty($tmp['Error_Msg'])) {
					throw new Exception($tmp['Error_Msg']);
				}
			}
		}
		// если в папке есть удаленные шаблоны, то зануляем в них ссылку на папку
		$query = "
			select XmlTemplate_id as \"XmlTemplate_id\"
			from XmlTemplate
			where XmlTemplateCat_id = :XmlTemplateCat_id
				and coalesce(XmlTemplate_IsDeleted,1) = 2
		";
		$result = $this->db->query($query, array('XmlTemplateCat_id' => $this->id));
		if ( is_object($result) ) {
			$tmp = $result->result('array');
		} else {
			throw new Exception('Не удалось выполнить запрос списка шаблонов, которые были удалены из папки');
		}
		if (count($tmp) > 0) {
			$id_list = array();
			foreach ($tmp as $row) {
				$id_list[] = $row['XmlTemplate_id'];
			}
			$id_list = implode(',', $id_list);
			$query = "
				update XmlTemplate
				set XmlTemplateCat_id = null
				where XmlTemplate_id in ({$id_list})
			";
			$result = $this->db->query($query, array());
			if (empty($result)) {
				throw new Exception('Ошибка запроса зануления ссылок на папку', 500);
			}
		}
	}

	/**
	 * Функция чтения данных папки для формы редактирования
	 * @author		Александр Пермяков
	 *
	 * @property int $pid
	 * @property int $code
	 * @property string $name Наименование папки
	 * @property int $XmlTemplateScope_id Видимость
	 * @property int $XmlTemplateScope_eid Доступность для изменения
	 */
	function loadEditForm($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		$this->applyData($data);
		$this->_validate();
		return array(array(
			'XmlTemplateCat_id' => $this->id,
			'XmlTemplateCat_pid' => $this->pid,
			'XmlTemplateCat_Name' => $this->name,
			'XmlTemplateScope_eid' => $this->XmlTemplateScope_eid,
			'XmlTemplateScope_id' => $this->XmlTemplateScope_id,
			'Lpu_id' => $this->Lpu_id,
			'LpuSection_id' => $this->LpuSection_id,
			'LpuSection_Name' => $this->LpuSection_Name,
			'Lpu_Name' => $this->Lpu_Name,
			'PMUser_Name' => $this->PMUser_Name,
		));
	}

	/**
	 * Функция чтения списка папок для комбобокса
	 */
	function loadCombo($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_COMBO_BOX;
		$this->applyData($data);
		$this->_validate();

		$params = array();
		$params = array_merge($params,
			swXmlTemplate::getAccessRightsQueryParams($this->_params['Lpu_id'], $this->_params['LpuSection_id'], $this->promedUserId)
		);
		// фильтруем по доступности редактировать
		$visibleFilter = " and ('edit' = ("
			. swXmlTemplate::getAccessRightsQueryPart('xtc', 'XmlTemplateCat', false)
			. '))';
		$queryFilter = '';
		if (!empty($data['query'])) {
			$params['query'] = '%'.$data['query'].'%';
			$queryFilter = ' and xtc.XmlTemplateCat_Name ilike :query';
		}
		$pidFilter = '';
		if (!empty($data['XmlTemplateCat_pid'])) {
			$params['XmlTemplateCat_pid'] = $data['XmlTemplateCat_pid'];
			$pidFilter = ' and xtc.XmlTemplateCat_id = :XmlTemplateCat_pid';
		}
		$idFilter = '';
		if (!empty($data['XmlTemplateCat_id'])) {
			$params['XmlTemplateCat_id'] = $data['XmlTemplateCat_id'];
			$idFilter = ' and xtc.XmlTemplateCat_id <> :XmlTemplateCat_id';
		}
		$maxLevelFilter = '';
		if (!empty($data['needMaxLevelFilter'])) {
			$maxLevelFilter = ' and not exists (
				select p6.XmlTemplateCat_id
				from XmlTemplateCat p0
				left join dbo.XmlTemplateCat p1 on p0.XmlTemplateCat_pid = p1.XmlTemplateCat_id
				left join dbo.XmlTemplateCat p2 on p1.XmlTemplateCat_pid = p2.XmlTemplateCat_id
				left join dbo.XmlTemplateCat p3 on p2.XmlTemplateCat_pid = p3.XmlTemplateCat_id
				left join dbo.XmlTemplateCat p4 on p3.XmlTemplateCat_pid = p4.XmlTemplateCat_id
				left join dbo.XmlTemplateCat p5 on p4.XmlTemplateCat_pid = p5.XmlTemplateCat_id
				left join dbo.XmlTemplateCat p6 on p5.XmlTemplateCat_pid = p6.XmlTemplateCat_id
				where p0.XmlTemplateCat_id = xtc.XmlTemplateCat_pid and p6.XmlTemplateCat_id is not null
				limit 1
			)';
		}

		$query = "
			select
				xtc.XmlTemplateCat_Name as \"XmlTemplateCat_Name\",
				xtc.XmlTemplateCat_id as \"XmlTemplateCat_id\"
			from
				v_XmlTemplateCat xtc
			where 1=1
				{$queryFilter}
				{$pidFilter}
				{$idFilter}
				{$visibleFilter}
				{$maxLevelFilter}
			order by
				xtc.XmlTemplateCat_Name
		";
		//throw new Exception(getDebugSQL($query, $params));
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Логика перед сохранением, включающая в себя проверку данных
	 * @param array $data
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
		$this->setAttribute('code', 1);
		$this->setAttribute('server_id', $this->sessionParams['server_id']);
		if (self::SCENARIO_AUTO_CREATE == $this->scenario) {
			$this->setAttribute('xmltemplatescope_id', 5);
			$this->setAttribute('xmltemplatescope_eid', 5);
		}
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
	 * Логика после успешного выполнения запроса сохранения объекта
	 * @param array $result
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		if (self::SCENARIO_AUTO_CREATE == $this->scenario) {
			$this->load->model('XmlTemplateCatDefault_model');
			$tmp = $this->XmlTemplateCatDefault_model->doSave(array(
				'session' => $this->sessionParams,
				'scenario' => $this->scenario,
				'XmlTemplateCatDefault_id' => null,
				'XmlTemplateCat_id' => $this->id,
				'Lpu_id'=>$this->Lpu_id,
				'LpuSection_id'=>$this->LpuSection_id,
				'MedService_id' => $this->_params['MedService_id'],
				'MedPersonal_id' => $this->_params['MedPersonal_id'],
				'MedStaffFact_id' => $this->_params['MedStaffFact_id'],
				'EvnClass_id' => $this->_params['EvnClass_id'],
				'XmlType_id' => $this->_params['XmlType_id'],
			), false);
			if (false == empty($tmp['Error_Msg'])) {
				throw new Exception($tmp['Error_Msg']);
			}
			$this->_saveResponse['XmlTemplateCat_Name'] = $this->name;
		} else {
			$this->_saveResponse['XmlTemplateCat_pid'] = $this->pid;
		}
	}

	/**
	 * Поиск папок доступных для редактирования
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	public function search($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_COMBO_BOX;
		$this->applyData($data);
		$this->_validate();
		$params = array();
		$params = array_merge($params,
			swXmlTemplate::getAccessRightsQueryParams($this->_params['Lpu_id'], $this->_params['LpuSection_id'], $this->promedUserId)
		);
		// фильтруем по доступности редактировать
		$visibleFilter = "('edit' = ("
			. swXmlTemplate::getAccessRightsQueryPart('xtc', 'XmlTemplateCat', false)
			. '))';
		$orderLocation = 'case when xtc.pmUser_insid = :pmUser_id then 1 else 2 end';
		if (isset($this->_params['LpuSection_id'])) {
			$orderLocation .= ', case when xtc.LpuSection_id = :LpuSection_uid then 1 else 2 end';
		}
		$orderLocation .= ', case when xtc.Lpu_id = :Lpu_uid then 1 else 2 end';
		$query = "
			select
				xtc.XmlTemplateCat_Name as \"XmlTemplateCat_Name\",
				xtc.XmlTemplateCat_id as \"XmlTemplateCat_id\"
			from
				v_XmlTemplateCat xtc
				left join dbo.v_XmlTemplateCat p0 on xtc.XmlTemplateCat_pid = p0.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p1 on p0.XmlTemplateCat_pid = p1.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p2 on p1.XmlTemplateCat_pid = p2.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p3 on p2.XmlTemplateCat_pid = p3.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p4 on p3.XmlTemplateCat_pid = p4.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p5 on p4.XmlTemplateCat_pid = p5.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p6 on p5.XmlTemplateCat_pid = p6.XmlTemplateCat_id
			where
				xtc.Lpu_id = :Lpu_uid and {$visibleFilter}
			order by
				{$orderLocation},
				case when p0.XmlTemplateCat_id is null then 1 
				when p1.XmlTemplateCat_id is null then 2
				when p2.XmlTemplateCat_id is null then 3
				when p3.XmlTemplateCat_id is null then 4
				when p4.XmlTemplateCat_id is null then 5
				when p5.XmlTemplateCat_id is null then 6
				when p6.XmlTemplateCat_id is null then 7
				else 8 end
			limit 1
		";
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Автоматическое создание папки
	 *
	 * Используется, когда у пользователя нет папок по умолчанию
	 * или других папок доступных для редактирования
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	public function createDefault($data)
	{
		// сначала пытаемся найти папки с видимостью для автора или МО
		$data['scenario'] = self::SCENARIO_AUTO_CREATE;
		$data['XmlTemplateCat_id'] = null;
		$data['XmlTemplateCat_pid'] = null;
		$this->applyData($data);
		return $this->doSave();
	}
}
