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
 * Модель папок по умолчанию.
 *
 * Папка по умолчанию - это прежде всего папка,
 * с которой начинается обзор шаблонов документов.
 * Папка по умолчанию назначается для связки место работы/служба+врач,
 * класс события, тип документа.
 * В качестве папки по умолчанию можно выбрать папку, недоступную для записи.
 * Одну и ту же папку можно использовать по умолчанию для разных типов документов, мест работы, пользователей.
 *
 * @package		XmlTemplate
 * @author		Александр Пермяков
 *
 * @property int $XmlTemplateCat_id
 * @property int $XmlType_id
 * @property int $EvnClass_id
 * @property int $Server_id
 * @property int $Lpu_id
 * @property int $MedPersonal_id
 * @property int $LpuSection_id
 * @property int $MedStaffFact_id
 * @property int $MedService_id
 *
 * @property XmlTemplateCat_model $XmlTemplateCat_model
 */
class XmlTemplateCatDefault_model extends swModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = false;

	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,
			'search',
		));
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'XmlTemplateCatDefault';
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
				'alias' => 'XmlTemplateCatDefault_id',
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
			'server_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'xmltemplatecat_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'XmlTemplateCat_id',
				'label' => 'Папка',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'xmltype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'XmlType_id',
				'label' => 'Тип документа',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'evnclass_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'EvnClass_id',
				'label' => 'Категория документа',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'lpu_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'Lpu_id',
			),
			'medpersonal_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MedPersonal_id',
				'label' => 'Медицинский сотрудник',
				'save' => 'trim',
				'type' => 'id'
			),
			'lpusection_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'LpuSection_id',
				'label' => 'Отделение',
				'save' => 'trim',
				'type' => 'id'
			),
			'medstafffact_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'save' => 'trim',
				'type' => 'id'
			),
			'medservice_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MedService_id',
				'label' => 'Служба',
				'save' => 'trim',
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
			case 'search':
				$rules = array(
					array('field' => 'XmlType_id','label' => 'Идентификатор типа документа','rules' => 'trim|required','type' => 'id'),
					array('field' => 'EvnClass_id','label' => 'Идентификатор категории документа','rules' => 'trim|required','type' => 'id'),
					array('field' => 'MedStaffFact_id','label' => 'Идентификатор рабочего места','rules' => 'trim','type' => 'id'),
					array('field' => 'MedService_id','label' => 'Идентификатор службы','rules' => 'trim','type' => 'id'),
					array('field' => 'MedPersonal_id','label' => 'Идентификатор врача','rules' => 'trim','type' => 'id'),
					array('field' => 'LpuSection_id','label' => 'Идентификатор отделения пользователя','rules' => 'trim','type' => 'id'),
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
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			'search'
		))) {
			if ( empty($this->XmlType_id) ) {
				throw new Exception('Не указан тип документа', 500);
			}
			if ( empty($this->EvnClass_id) ) {
				throw new Exception('Не указана категория документа', 500);
			}
			$emptyKey = true;
			if ( $this->MedStaffFact_id > 0 ) {
				$emptyKey = false;
			}
			if ( $this->MedService_id > 0 && $this->MedPersonal_id ) {
				$emptyKey = false;
			}
			if ( $emptyKey ) {
				throw new Exception('Не указана связка место работы/служба+врач', 500);
			}
		}
		if (in_array($this->scenario, array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
		))) {
			if ( empty($this->XmlTemplateCat_id) ) {
				throw new Exception('Не указана папка', 500);
			}
		}
	}

	/**
	 * Формируется запрос поиска папки по умолчанию с учетом фильтров
	 */
	private function _getLoadQuery($add_select = '', $add_join = '', $filters = '', $params = array())
	{
		$query = array();
		$query['params'] = $params;
		$query['params']['EvnClass_id'] = $this->EvnClass_id;
		$query['params']['XmlType_id'] = $this->XmlType_id;
		if (!empty($this->MedStaffFact_id)) {
			$filters .= "
				AND xtcd.MedStaffFact_id = :MedStaffFact_id";
			$query['params']['MedStaffFact_id'] = $this->MedStaffFact_id;
		} else {
			$filters .= "
				AND xtcd.MedService_id = :MedService_id
				AND xtcd.MedPersonal_id = :MedPersonal_id";
			$query['params']['MedService_id'] = $this->MedService_id;
			$query['params']['MedPersonal_id'] = $this->MedPersonal_id;
		}
		$query['sql'] = "
			select top 1
				xtcd.XmlTemplateCatDefault_id {$add_select}
			from
				dbo.v_XmlTemplateCatDefault xtcd with (NOLOCK)
				{$add_join}
			where
				xtcd.EvnClass_id = :EvnClass_id
				AND xtcd.XmlType_id = :XmlType_id {$filters}
		";
		return $query;
	}

	/**
	 * Поиск папки по умолчанию
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	public function search($data)
	{
		$data['scenario'] = 'search';
		$this->applyData($data);
		$this->_validate();
		$query = $this->_getLoadQuery(",xtcd.XmlTemplateCat_id");
		$result = $this->db->query($query['sql'], $query['params']);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			throw new Exception('Ошибка БД, не удалось получить идентификатор папки по умолчанию.', 500);
		}
	}

	/**
 * Поиск папки по умолчанию c выводом пути к ней
 * или пути к ближайшей папке, доступной для редактирования
 * @param array $data
 * @return array
 * @throws Exception
 */
	public function getPath($data)
	{
		$data['scenario'] = 'search';
		$this->applyData($data);
		$this->_validate();

		$data['scenario'] = 'search';
		$this->applyData($data);
		$this->_validate();
		$query = $this->_getLoadQuery(",xtcd.XmlTemplateCat_id");
		$result = $this->db->query($query['sql'], $query['params']);
		if ( false == is_object($result) ) {
			throw new Exception('Ошибка БД при запросе идентификатора папки по умолчанию.', 500);
		}
		$tmp = $result->result('array');
		$query = array();
		if (empty($tmp)) {
			// ищем ближайшую папку, доступную для редактирования
			$this->load->model('XmlTemplateCat_model');
			$tmp = $this->XmlTemplateCat_model->search($data);
			if (empty($tmp)) {
				return array();
			}
		}
		$query['params']['id'] = $tmp[0]['XmlTemplateCat_id'];
		$this->load->library('swXmlTemplate');
		$query['params'] = array_merge($query['params'],
			swXmlTemplate::getAccessRightsQueryParams($data['session']['lpu_id'], $data['LpuSection_id'], $data['session']['pmuser_id'])
		);
		$accessType = swXmlTemplate::getAccessRightsQueryPart('xtc', 'XmlTemplateCat', false);
		$accessType0 = swXmlTemplate::getAccessRightsQueryPart('p0', 'XmlTemplateCat', false);
		$accessType1 = swXmlTemplate::getAccessRightsQueryPart('p1', 'XmlTemplateCat', false);
		$accessType2 = swXmlTemplate::getAccessRightsQueryPart('p2', 'XmlTemplateCat', false);
		$accessType3 = swXmlTemplate::getAccessRightsQueryPart('p3', 'XmlTemplateCat', false);
		$accessType4 = swXmlTemplate::getAccessRightsQueryPart('p4', 'XmlTemplateCat', false);
		$accessType5 = swXmlTemplate::getAccessRightsQueryPart('p5', 'XmlTemplateCat', false);
		$accessType6 = swXmlTemplate::getAccessRightsQueryPart('p6', 'XmlTemplateCat', false);
		$query['sql'] = "
			select top 1
				xtc.XmlTemplateCat_id
				,xtc.XmlTemplateCat_Name
				,{$accessType} as accessType
				,p0.XmlTemplateCat_id as XmlTemplateCat_pid0
				,p0.XmlTemplateCat_Name as XmlTemplateCat_Name0
				,{$accessType0} as accessType0
				,p1.XmlTemplateCat_id as XmlTemplateCat_pid1
				,p1.XmlTemplateCat_Name as XmlTemplateCat_Name1
				,{$accessType1} as accessType1
				,p2.XmlTemplateCat_id as XmlTemplateCat_pid2
				,p2.XmlTemplateCat_Name as XmlTemplateCat_Name2
				,{$accessType2} as accessType2
				,p3.XmlTemplateCat_id as XmlTemplateCat_pid3
				,p3.XmlTemplateCat_Name as XmlTemplateCat_Name3
				,{$accessType3} as accessType3
				,p4.XmlTemplateCat_id as XmlTemplateCat_pid4
				,p4.XmlTemplateCat_Name as XmlTemplateCat_Name4
				,{$accessType4} as accessType4
				,p5.XmlTemplateCat_id as XmlTemplateCat_pid5
				,p5.XmlTemplateCat_Name as XmlTemplateCat_Name5
				,{$accessType5} as accessType5
				,p6.XmlTemplateCat_id as XmlTemplateCat_pid6
				,p6.XmlTemplateCat_Name as XmlTemplateCat_Name6
				,{$accessType6} as accessType6
			from
				dbo.v_XmlTemplateCat xtc with (NOLOCK)
				left join dbo.v_XmlTemplateCat p0 with (nolock) on xtc.XmlTemplateCat_pid = p0.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p1 with (nolock) on p0.XmlTemplateCat_pid = p1.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p2 with (nolock) on p1.XmlTemplateCat_pid = p2.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p3 with (nolock) on p2.XmlTemplateCat_pid = p3.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p4 with (nolock) on p3.XmlTemplateCat_pid = p4.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p5 with (nolock) on p4.XmlTemplateCat_pid = p5.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p6 with (nolock) on p5.XmlTemplateCat_pid = p6.XmlTemplateCat_id
			where
				xtc.XmlTemplateCat_id = :id
		";
		$result = $this->db->query($query['sql'], $query['params']);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			throw new Exception('Ошибка БД, не удалось получить данные папки по умолчанию.', 500);
		}
	}

	/**
	 * МАРМ-версия
	 * Поиск папки по умолчанию c выводом пути к ней
	 * или пути к ближайшей папке, доступной для редактирования
	 */
	public function mGetPath($data)
	{
		$data['scenario'] = 'search';
		$this->applyData($data);
		$this->_validate();

		$data['scenario'] = 'search';
		$this->applyData($data);
		$this->_validate();
		$query = $this->_getLoadQuery(",xtcd.XmlTemplateCat_id");
		$result = $this->db->query($query['sql'], $query['params']);
		if ( false == is_object($result) ) {
			throw new Exception('Ошибка БД при запросе идентификатора папки по умолчанию.', 500);
		}
		$tmp = $result->result('array');
		$query = array();
		if (empty($tmp)) {
			// ищем ближайшую папку, доступную для редактирования
			$this->load->model('XmlTemplateCat_model');
			$tmp = $this->XmlTemplateCat_model->search($data);
			if (empty($tmp)) {
				return array();
			}
		}
		$query['params']['id'] = $tmp[0]['XmlTemplateCat_id'];
		$this->load->library('swXmlTemplate');
		$query['params'] = array_merge($query['params'],
			swXmlTemplate::getAccessRightsQueryParams($data['session']['lpu_id'], $data['LpuSection_id'], $data['session']['pmuser_id'])
		);
		$accessType = swXmlTemplate::getAccessRightsQueryPart('xtc', 'XmlTemplateCat', false);
		$accessType0 = swXmlTemplate::getAccessRightsQueryPart('p0', 'XmlTemplateCat', false);
		$accessType1 = swXmlTemplate::getAccessRightsQueryPart('p1', 'XmlTemplateCat', false);
		$accessType2 = swXmlTemplate::getAccessRightsQueryPart('p2', 'XmlTemplateCat', false);
		$accessType3 = swXmlTemplate::getAccessRightsQueryPart('p3', 'XmlTemplateCat', false);
		$accessType4 = swXmlTemplate::getAccessRightsQueryPart('p4', 'XmlTemplateCat', false);
		$accessType5 = swXmlTemplate::getAccessRightsQueryPart('p5', 'XmlTemplateCat', false);
		$accessType6 = swXmlTemplate::getAccessRightsQueryPart('p6', 'XmlTemplateCat', false);
		$query['sql'] = "
			select top 1
				xtc.XmlTemplateCat_id
				,xtc.XmlTemplateCat_Name
				,{$accessType} as accessType
				,p0.XmlTemplateCat_id as XmlTemplateCat_pid0
				,p0.XmlTemplateCat_Name as XmlTemplateCat_Name0
				,{$accessType0} as accessType0
				,p1.XmlTemplateCat_id as XmlTemplateCat_pid1
				,p1.XmlTemplateCat_Name as XmlTemplateCat_Name1
				,{$accessType1} as accessType1
				,p2.XmlTemplateCat_id as XmlTemplateCat_pid2
				,p2.XmlTemplateCat_Name as XmlTemplateCat_Name2
				,{$accessType2} as accessType2
				,p3.XmlTemplateCat_id as XmlTemplateCat_pid3
				,p3.XmlTemplateCat_Name as XmlTemplateCat_Name3
				,{$accessType3} as accessType3
				,p4.XmlTemplateCat_id as XmlTemplateCat_pid4
				,p4.XmlTemplateCat_Name as XmlTemplateCat_Name4
				,{$accessType4} as accessType4
				,p5.XmlTemplateCat_id as XmlTemplateCat_pid5
				,p5.XmlTemplateCat_Name as XmlTemplateCat_Name5
				,{$accessType5} as accessType5
				,p6.XmlTemplateCat_id as XmlTemplateCat_pid6
				,p6.XmlTemplateCat_Name as XmlTemplateCat_Name6
				,{$accessType6} as accessType6
			from
				dbo.v_XmlTemplateCat xtc with (NOLOCK)
				left join dbo.v_XmlTemplateCat p0 with (nolock) on xtc.XmlTemplateCat_pid = p0.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p1 with (nolock) on p0.XmlTemplateCat_pid = p1.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p2 with (nolock) on p1.XmlTemplateCat_pid = p2.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p3 with (nolock) on p2.XmlTemplateCat_pid = p3.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p4 with (nolock) on p3.XmlTemplateCat_pid = p4.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p5 with (nolock) on p4.XmlTemplateCat_pid = p5.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p6 with (nolock) on p5.XmlTemplateCat_pid = p6.XmlTemplateCat_id
			where
				xtc.XmlTemplateCat_id = :id
		";
		$result = $this->db->query($query['sql'], $query['params']);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			throw new Exception('Ошибка БД, не удалось получить данные папки по умолчанию.', 500);
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
		if ($this->MedService_id > 0) {
			$tmp = $this->getFirstRowFromQuery('
				select Lpu_id, LpuSection_id
				from v_MedService (nolock)
				where MedService_id = :id
			', array('id'=>$this->MedService_id));
			if (empty($tmp)) {
				throw new Exception('Служба не найдена', 400);
			}
			$this->setAttribute('lpu_id', $tmp['Lpu_id']);
			$this->setAttribute('lpusection_id', $tmp['LpuSection_id']);
		} else if (empty($this->MedPersonal_id) && empty($this->LpuSection_id)) {
			$tmp = $this->getFirstRowFromQuery('
				select Lpu_id, LpuSection_id, MedPersonal_id
				from v_MedStaffFact (nolock)
				where MedStaffFact_id = :id
			', array('id'=>$this->MedStaffFact_id));
			if (empty($tmp)) {
				throw new Exception('Рабочее место не найдено', 400);
			}
			$this->setAttribute('lpu_id', $tmp['Lpu_id']);
			$this->setAttribute('lpusection_id', $tmp['LpuSection_id']);
			$this->setAttribute('medpersonal_id', $tmp['MedPersonal_id']);
		}
	}

	/**
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	public function save($data)
	{
		// ищем папку по умолчанию
		$data['scenario'] = 'search';
		$data['XmlTemplateCatDefault_id'] = null;
		$this->applyData($data);
		$this->_validate();
		$query = $this->_getLoadQuery();
		$result = $this->db->query($query['sql'], $query['params']);
		if ( is_object($result) ) {
			$tmp = $result->result('array');
			if (!empty($tmp)) {
				$data['XmlTemplateCatDefault_id'] = $tmp[0]['XmlTemplateCatDefault_id'];
			}
		} else {
			throw new Exception('Ошибка запроса к БД при попытке получить идентификатор папки по умолчанию.', 500);
		}

		$data['scenario'] = self::SCENARIO_DO_SAVE;
		return $this->doSave($data);
	}
}