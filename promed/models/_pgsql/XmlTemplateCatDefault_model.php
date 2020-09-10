<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		31.10.2014
 *
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
 * @property CI_DB_driver $db
 */
class XmlTemplateCatDefault_model extends swPgModel
{
	/**@var bool Требуется ли параметр pmUser_id для хранимки удаления */
	protected $_isNeedPromedUserIdForDel = false;
	
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList([
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,
			"search",
		]);
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return "XmlTemplateCatDefault";
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		return [
			self::ID_KEY => [
				"properties" => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL,
				],
				"alias" => "XmlTemplateCatDefault_id",
				"label" => "Идентификатор",
				"save" => "trim",
				"type" => "id"
			],
			"insdt" => [
				"properties" => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				],
			],
			"pmuser_insid" => [
				"properties" => [
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				],
			],
			"upddt" => [
				"properties" => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				],
			],
			"pmuser_updid" => [
				"properties" => [
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				],
			],
			"server_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_SAFE,
				],
			],
			"xmltemplatecat_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "XmlTemplateCat_id",
				"label" => "Папка",
				"save" => "trim|required",
				"type" => "id"
			],
			"xmltype_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "XmlType_id",
				"label" => "Тип документа",
				"save" => "trim|required",
				"type" => "id"
			],
			"evnclass_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "EvnClass_id",
				"label" => "Категория документа",
				"save" => "trim|required",
				"type" => "id"
			],
			"lpu_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "Lpu_id",
			],
			"medpersonal_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "MedPersonal_id",
				"label" => "Медицинский сотрудник",
				"save" => "trim",
				"type" => "id"
			],
			"lpusection_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "LpuSection_id",
				"label" => "Отделение",
				"save" => "trim",
				"type" => "id"
			],
			"medstafffact_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "MedStaffFact_id",
				"label" => "Рабочее место врача",
				"save" => "trim",
				"type" => "id"
			],
			"medservice_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "MedService_id",
				"label" => "Служба",
				"save" => "trim",
				"type" => "id"
			],
		];
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
			case "search":
				$rules = [
					["field" => "XmlType_id", "label" => "Идентификатор типа документа", "rules" => "trim|required", "type" => "id"],
					["field" => "EvnClass_id", "label" => "Идентификатор категории документа", "rules" => "trim|required", "type" => "id"],
					["field" => "MedStaffFact_id", "label" => "Идентификатор рабочего места", "rules" => "trim", "type" => "id"],
					["field" => "MedService_id", "label" => "Идентификатор службы", "rules" => "trim", "type" => "id"],
					["field" => "MedPersonal_id", "label" => "Идентификатор врача", "rules" => "trim", "type" => "id"],
					["field" => "LpuSection_id", "label" => "Идентификатор отделения пользователя", "rules" => "trim", "type" => "id"],
				];
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
		if (in_array($this->scenario, [
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			"search"
		])) {
			if (empty($this->XmlType_id)) {
				throw new Exception("Не указан тип документа", 500);
			}
			if (empty($this->EvnClass_id)) {
				throw new Exception("Не указана категория документа", 500);
			}
			$emptyKey = true;
			if ($this->MedStaffFact_id > 0) {
				$emptyKey = false;
			}
			if ($this->MedService_id > 0 && $this->MedPersonal_id) {
				$emptyKey = false;
			}
			if ($emptyKey) {
				throw new Exception("Не указана связка место работы/служба+врач", 500);
			}
		}
		if (in_array($this->scenario, [
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
		])) {
			if (empty($this->XmlTemplateCat_id)) {
				throw new Exception("Не указана папка", 500);
			}
		}
	}

	/**
	 * Формируется запрос поиска папки по умолчанию с учетом фильтров
	 * @param string $add_select
	 * @param string $add_join
	 * @param string $filters
	 * @param array $params
	 * @return array
	 */
	private function _getLoadQuery($add_select = "", $add_join = "", $filters = "", $params = [])
	{
		$query = [];
		$query["params"] = $params;
		$query["params"]["EvnClass_id"] = $this->EvnClass_id;
		$query["params"]["XmlType_id"] = $this->XmlType_id;
		if (!empty($this->MedStaffFact_id)) {
			$filters .= "
				and xtcd.MedStaffFact_id = :MedStaffFact_id
			";
			$query["params"]["MedStaffFact_id"] = $this->MedStaffFact_id;
		} else {
			$filters .= "
				and xtcd.MedService_id = :MedService_id
				and xtcd.MedPersonal_id = :MedPersonal_id
			";
			$query["params"]["MedService_id"] = $this->MedService_id;
			$query["params"]["MedPersonal_id"] = $this->MedPersonal_id;
		}
		$selectString = "
			xtcd.XmlTemplateCatDefault_id as \"XmlTemplateCatDefault_id\" {$add_select}
		";
		$query["sql"] = "
			select {$selectString}
			from
				dbo.v_XmlTemplateCatDefault xtcd
				{$add_join}
			where xtcd.EvnClass_id = :EvnClass_id
			  and xtcd.XmlType_id = :XmlType_id
			  {$filters}
			limit 1
		";
		return $query;
	}

	/**
	 * Поиск папки по умолчанию
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function search($data)
	{
		$data["scenario"] = "search";
		$this->applyData($data);
		$this->_validate();
		$query = $this->_getLoadQuery(",xtcd.XmlTemplateCat_id as \"XmlTemplateCat_id\"");
		/**@var CI_DB_result $result */
		$result = $this->db->query($query["sql"], $query["params"]);
		if (!is_object($result)) {
			throw new Exception("Ошибка БД, не удалось получить идентификатор папки по умолчанию.", 500);
		}
		return $result->result("array");
	}

	/**
	 * Поиск папки по умолчанию c выводом пути к ней или пути к ближайшей папке, доступной для редактирования
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function getPath($data)
	{
		$data["scenario"] = "search";
		$this->applyData($data);
		$this->_validate();

		$data["scenario"] = "search";
		$this->applyData($data);
		$this->_validate();
		$query = $this->_getLoadQuery(",xtcd.XmlTemplateCat_id as \"XmlTemplateCat_id\"");
		$result = $this->db->query($query["sql"], $query["params"]);
		if (false == is_object($result)) {
			throw new Exception("Ошибка БД при запросе идентификатора папки по умолчанию.", 500);
		}
		$tmp = $result->result("array");
		$query = [];
		if (empty($tmp)) {
			// ищем ближайшую папку, доступную для редактирования
			$this->load->model("XmlTemplateCat_model");
			$tmp = $this->XmlTemplateCat_model->search($data);
			if (empty($tmp)) {
				return [];
			}
		}
		$query["params"]["id"] = $tmp[0]["XmlTemplateCat_id"];
		$this->load->library("swXmlTemplate");
		$query["params"] = array_merge($query["params"],
			swXmlTemplate::getAccessRightsQueryParams($data["session"]["lpu_id"], $data["LpuSection_id"], $data["session"]["pmuser_id"])
		);
		$accessType = swXmlTemplate::getAccessRightsQueryPart("xtc", "XmlTemplateCat", false);
		$accessType0 = swXmlTemplate::getAccessRightsQueryPart("p0", "XmlTemplateCat", false);
		$accessType1 = swXmlTemplate::getAccessRightsQueryPart("p1", "XmlTemplateCat", false);
		$accessType2 = swXmlTemplate::getAccessRightsQueryPart("p2", "XmlTemplateCat", false);
		$accessType3 = swXmlTemplate::getAccessRightsQueryPart("p3", "XmlTemplateCat", false);
		$accessType4 = swXmlTemplate::getAccessRightsQueryPart("p4", "XmlTemplateCat", false);
		$accessType5 = swXmlTemplate::getAccessRightsQueryPart("p5", "XmlTemplateCat", false);
		$accessType6 = swXmlTemplate::getAccessRightsQueryPart("p6", "XmlTemplateCat", false);
		$query["sql"] = "
			select
				xtc.XmlTemplateCat_id as \"XmlTemplateCat_id\",
				xtc.XmlTemplateCat_Name as \"XmlTemplateCat_Name\",
				{$accessType} as \"accessType\",
				p0.XmlTemplateCat_id as \"XmlTemplateCat_pid0\",
				p0.XmlTemplateCat_Name as \"XmlTemplateCat_Name0\",
				{$accessType0} as \"accessType0\",
				p1.XmlTemplateCat_id as \"XmlTemplateCat_pid1\",
				p1.XmlTemplateCat_Name as \"XmlTemplateCat_Name1\",
				{$accessType1} as \"accessType1\",
				p2.XmlTemplateCat_id as \"XmlTemplateCat_pid2\",
				p2.XmlTemplateCat_Name as \"XmlTemplateCat_Name2\",
				{$accessType2} as \"accessType2\",
				p3.XmlTemplateCat_id as \"XmlTemplateCat_pid3\",
				p3.XmlTemplateCat_Name as \"XmlTemplateCat_Name3\",
				{$accessType3} as \"accessType3\",
				p4.XmlTemplateCat_id as \"XmlTemplateCat_pid4\",
				p4.XmlTemplateCat_Name as \"XmlTemplateCat_Name4\",
				{$accessType4} as \"accessType4\",
				p5.XmlTemplateCat_id as \"XmlTemplateCat_pid5\",
				p5.XmlTemplateCat_Name as \"XmlTemplateCat_Name5\",
				{$accessType5} as \"accessType5\",
				p6.XmlTemplateCat_id as \"XmlTemplateCat_pid6\",
				p6.XmlTemplateCat_Name as \"XmlTemplateCat_Name6\",
				{$accessType6} as \"accessType6\"
			from
				v_XmlTemplateCat xtc
				left join v_XmlTemplateCat p0 on xtc.XmlTemplateCat_pid = p0.XmlTemplateCat_id
				left join v_XmlTemplateCat p1 on p0.XmlTemplateCat_pid = p1.XmlTemplateCat_id
				left join v_XmlTemplateCat p2 on p1.XmlTemplateCat_pid = p2.XmlTemplateCat_id
				left join v_XmlTemplateCat p3 on p2.XmlTemplateCat_pid = p3.XmlTemplateCat_id
				left join v_XmlTemplateCat p4 on p3.XmlTemplateCat_pid = p4.XmlTemplateCat_id
				left join v_XmlTemplateCat p5 on p4.XmlTemplateCat_pid = p5.XmlTemplateCat_id
				left join v_XmlTemplateCat p6 on p5.XmlTemplateCat_pid = p6.XmlTemplateCat_id
			where xtc.XmlTemplateCat_id = :id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query["sql"], $query["params"]);
		if (!is_object($result)) {
			throw new Exception("Ошибка БД, не удалось получить данные папки по умолчанию.", 500);
		}
		return $result->result("array");
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
			select
				xtc.XmlTemplateCat_id as \"XmlTemplateCat_id\",
				xtc.XmlTemplateCat_Name as \"XmlTemplateCat_Name\",
				{$accessType} as \"accessType\",
				p0.XmlTemplateCat_id as \"XmlTemplateCat_pid0\",
				p0.XmlTemplateCat_Name as \"XmlTemplateCat_Name0\",
				{$accessType0} as \"accessType0\",
				p1.XmlTemplateCat_id as \"XmlTemplateCat_pid1\",
				p1.XmlTemplateCat_Name as \"XmlTemplateCat_Name1\",
				{$accessType1} as \"accessType1\",
				p2.XmlTemplateCat_id as \"XmlTemplateCat_pid2\",
				p2.XmlTemplateCat_Name as \"XmlTemplateCat_Name2\",
				{$accessType2} as \"accessType2\",
				p3.XmlTemplateCat_id as \"XmlTemplateCat_pid3\",
				p3.XmlTemplateCat_Name as \"XmlTemplateCat_Name3\",
				{$accessType3} as \"accessType3\",
				p4.XmlTemplateCat_id as \"XmlTemplateCat_pid4\",
				p4.XmlTemplateCat_Name as \"XmlTemplateCat_Name4\",
				{$accessType4} as \"accessType4\",
				p5.XmlTemplateCat_id as \"XmlTemplateCat_pid5\",
				p5.XmlTemplateCat_Name as \"XmlTemplateCat_Name5\",
				{$accessType5} as \"accessType5\",
				p6.XmlTemplateCat_id as \"XmlTemplateCat_pid6\",
				p6.XmlTemplateCat_Name as \"XmlTemplateCat_Name6\",
				{$accessType6} as \"accessType6\"
			from
				dbo.v_XmlTemplateCat xtc
				left join dbo.v_XmlTemplateCat p0 on xtc.XmlTemplateCat_pid = p0.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p1 on p0.XmlTemplateCat_pid = p1.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p2 on p1.XmlTemplateCat_pid = p2.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p3 on p2.XmlTemplateCat_pid = p3.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p4 on p3.XmlTemplateCat_pid = p4.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p5 on p4.XmlTemplateCat_pid = p5.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p6 on p5.XmlTemplateCat_pid = p6.XmlTemplateCat_id
			where
				xtc.XmlTemplateCat_id = :id
			limit 1
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
	protected function _beforeSave($data = [])
	{
		parent::_beforeSave($data);
		if ($this->MedService_id > 0) {
			$query = "
				select
					Lpu_id as \"Lpu_id\",
				    LpuSection_id as \"LpuSection_id\"
				from v_MedService
				where MedService_id = :id
			";
			$queryParams = ["id" => $this->MedService_id];
			$tmp = $this->getFirstRowFromQuery($query, $queryParams);
			if (empty($tmp)) {
				throw new Exception("Служба не найдена", 400);
			}
			$this->setAttribute("lpu_id", $tmp["Lpu_id"]);
			$this->setAttribute("lpusection_id", $tmp["LpuSection_id"]);
		} else if (empty($this->MedPersonal_id) && empty($this->LpuSection_id)) {
			$query = "
				select
					Lpu_id as \"Lpu_id\",
				    LpuSection_id as \"LpuSection_id\",
				    MedPersonal_id as \"MedPersonal_id\"
				from v_MedStaffFact
				where MedStaffFact_id = :id
			";
			$queryParams = ["id" => $this->MedStaffFact_id];
			$tmp = $this->getFirstRowFromQuery($query, $queryParams);
			if (empty($tmp)) {
				throw new Exception("Рабочее место не найдено", 400);
			}
			$this->setAttribute("lpu_id", $tmp["Lpu_id"]);
			$this->setAttribute("lpusection_id", $tmp["LpuSection_id"]);
			$this->setAttribute("medpersonal_id", $tmp["MedPersonal_id"]);
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
		$data["scenario"] = "search";
		$data["XmlTemplateCatDefault_id"] = null;
		$this->applyData($data);
		$this->_validate();
		$query = $this->_getLoadQuery();
		$result = $this->db->query($query["sql"], $query["params"]);
		if (!is_object($result)) {
			throw new Exception("Ошибка запроса к БД при попытке получить идентификатор папки по умолчанию.", 500);
		}
		$tmp = $result->result("array");
		if (!empty($tmp)) {
			$data["XmlTemplateCatDefault_id"] = $tmp[0]["XmlTemplateCatDefault_id"];
		}
		$data["scenario"] = self::SCENARIO_DO_SAVE;
		return $this->doSave($data);
	}
}