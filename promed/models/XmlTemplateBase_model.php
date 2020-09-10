<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		13.05.2013
 */

/**
 * Базовая модель шаблонов с поддержкой новой структуры хранения.
 *
 * @package		XmlTemplate
 * @author		Александр Пермяков
 *
 * @property-read integer $XmlTemplateType_id Тип шаблона
 * @property-read integer $XmlTemplateData_id
 * @property string $xmlData XML-шаблон группирования элементов формы для ввода данных документа
 * @property-read integer $XmlTemplateHtml_id
 * @property string $htmlTemplate HTML-шаблон отображения документа
 * @property-read integer $isDeleted Признак удаления
 * @property-read integer $Region_id Принадлежность к региону
 *
 * Настройки шаблона
 * @property string $caption Наименование шаблона
 * @property integer $XmlTemplateCat_id Папка
 * @property integer $EvnClass_id Категория учетного документа
 * @property integer $XmlType_id Тип документа, который может быть создан на основе этого шаблона
 * @property string $printSettings Настройки для печати документа (Ассоциативный массив настроек в json-формате)
 * @property-read integer $XmlTemplateSettings_id
 * @property string $uslugaIdList Список идентификаторов услуг, с которыми ассоциирован шаблон
 * @property integer $XmlSchema_id XML-схема данных XML-документа
 * @property string $XmlSchema_Data XML-схема данных XML-документа
 *
 * Используется для контроля доступа
 * @property integer $XmlTemplateScope_id Тип доступа для чтения шаблона
 * @property integer $XmlTemplateScope_eid Тип доступа для редактирования шаблона
 * @property-read integer $LpuSection_id Отделение автора
 * @property-read integer $Lpu_id МО автора
 * @property-read string $LpuSection_Name
 * @property-read string $Lpu_Name
 * @property-read string $PMUser_Name
 *
 * Используется для шаблона плана назначений
 * @property integer $PersonAgeGroup_id Принадлежность шаблона плана назначений к возрастной группе
 * @property integer $Diag_id Принадлежность шаблона плана назначений к диагнозу или группе диагнозов
 *
 * Поля, устареющие после переноса данных из них в соответств. таблицы (true == _isAllowNewTables):
 * @ property string $XmlTemplate_Data XML-шаблон группирования элементов формы для ввода данных документа
 * @ property string $XmlTemplate_HtmlTemplate HTML-шаблон отображения документа
 * @ property string $XmlTemplate_Settings Настройки для печати документа (Ассоциативный массив настроек в json-формате)
 * XmlTemplateData_Hash,
 * XmlTemplateHtml_Hash,
 * XmlTemplateSettings_Hash,
 *
 * Давно устаревшие поля:
 * @ property string $XmlTemplate_patch Путь к шаблону от корневой папки
 * @ property string $XmlTemplate_Field
 * @ property int $MedSpec_id
 * @ property int $MedSpecOms_id
 * @ property int $Usluga_id
 *
 * @property XmlTemplateCatDefault_model $XmlTemplateCatDefault_model
 * @property XmlTemplateCat_model $XmlTemplateCat_model
 */
class XmlTemplateBase_model extends swModel
{
	/**
	 * @var bool
	 */
	//protected $_isAllowNewTables = false;

	/**
	 * Список категорий учетных документов,
	 * которые могут содержать XML-документы
	 *
	 * @var array
	 */
	protected $_supportEvnClassList = array(
		//2, // Талон амбулаторного пациента
		//3, // Лечение в поликлинике	EvnPL
		//6, // Лечение в стоматологии	EvnPLStom
		//10, // Базовое посещение	EvnVizit
		11, // Посещение поликлиники	EvnVizitPL
		13, // Посещение в стоматологии	EvnVizitPLStom
		14, // Посещение по дополнительной диспансеризации	EvnVizitDispDop
		//15, // Посещение по диспансеризации детей сирот	EvnVizitDispOrp
		//21, // Оказание услуги	EvnUsluga
		22, // Оказание общей услуги	EvnUslugaCommon
		27, // Направление EvnDirection
		//23, // Оказание услуги по дополнительной диспансеризации	EvnUslugaDispDop
		//24, // Оказание услуги по диспансеризации детей сирот	EvnUslugaDispOrp
		29, // Стоматологическая услуга	EvnUslugaStom
		43, // Оперативная услуга	EvnUslugaOper
		47, // Параклиническая услуга	EvnUslugaPar
		160, // Оказание телемедицинской услуги	EvnUslugaTelemed
		//62, // Оказание услуги по диспансеризации 14 летних подростков	EvnUslugaDispTeen14
		30, // Карта выбывшего из стационара	EvnPS
		32, // Движение в отделении	EvnSection
		120, //Судебно-медицинская экспертиза
	);

	/**
	 * Список классов учетных документов,
	 * для которых можно создавать спецмаркеры
	 * @var array
	 */
	protected $_supportEvnClassListWithBase = array(
		1, // Событие Evn
		2, // Талон амбулаторного пациента EvnPLBase
		3, // Лечение в поликлинике	EvnPL
		6, // Лечение в стоматологии	EvnPLStom
		10, // Базовое посещение	EvnVizit
		11, // Посещение поликлиники	EvnVizitPL
		13, // Посещение в стоматологии	EvnVizitPLStom
		14, // Посещение по дополнительной диспансеризации	EvnVizitDispDop
		//15, // Посещение по диспансеризации детей сирот	EvnVizitDispOrp
		21, // Оказание услуги	EvnUsluga
		22, // Оказание общей услуги	EvnUslugaCommon
		//23, // Оказание услуги по дополнительной диспансеризации	EvnUslugaDispDop
		//24, // Оказание услуги по диспансеризации детей сирот	EvnUslugaDispOrp
		27, // Направление EvnDirection
		29, // Стоматологическая услуга	EvnUslugaStom
		43, // Оперативная услуга	EvnUslugaOper
		47, // Параклиническая услуга	EvnUslugaPar
		160, // Оказание телемедицинской услуги	EvnUslugaTelemed
		//62, // Оказание услуги по диспансеризации 14 летних подростков	EvnUslugaDispTeen14
		30, // Карта выбывшего из стационара	EvnPS
		32, // Движение в отделении	EvnSection
		111, // Карта СМП	EvnCmp
		120, // Судебно-медицинская экспертиза	EvnForensic
		121, // Судебно-медицинская экспертиза потерпевших, обвиняемых и других лиц EvnForensicSub
	);

	protected $_supportXmlTypeEvnClassLink = array(
		1 => array(160),
		2 => array(10,11,13,22,27,29,30,32,43,47,120),
		3 => array(10,11,13,14),
		4 => array(10,11,13,22,29,43,47),
		5 => array(47),
		8 => array(30,32),
		9 => array(30,32),
		10 => array(32),
		11 => array(120),
		12 => array(120),
		13 => array(120),
		14 => array(120),
		15 => array(120),
		16 => array(29,43),
		18 => array(27),
		19 => array(27),
		20 => array(27),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		// Список имен сценариев, которые реализует модель
		$this->_setScenarioList(array(
			self::SCENARIO_LOAD_EDIT_FORM,
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_DELETE, // Удаление шаблона
			'saveSettings',
			'create', // Создание шаблона в методе save
			'update', // Редактирование шаблона в методе save
			'select', // Выбор шаблона для создания документа в методе select
		));
		$this->load->library('swXmlTemplate');
		$this->load->library('swEvnXml');
		//$this->_isAllowNewTables = swXmlTemplate::isAllowNewTables();
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'XmlTemplate';
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'XmlTemplate_id';
		$arr[self::ID_KEY]['label'] = 'Шаблон';
		unset($arr['code']);
		unset($arr['name']);
		$arr['insdt']['alias'] = 'XmlTemplate_insDT';
		$arr['upddt']['alias'] = 'XmlTemplate_updDT';
		$arr['xmltemplatetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'XmlTemplateType_id',
		);
		$arr['xmltemplatedata_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['xmldata'] = array(
			'properties' => array(
			),
			'select' => 'xtd.XmlTemplateData_Data as xmldata',
			'join' => 'left join XmlTemplateData xtd (nolock) on xtd.XmlTemplateData_id = {ViewName}.XmlTemplateData_id',
		);
		$arr['xmltemplatehtml_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['htmltemplate'] = array(
			'properties' => array(
			),
			'select' => 'xth.XmlTemplateHtml_HtmlTemplate as htmltemplate',
			'join' => 'left join XmlTemplateHtml xth (nolock) on xth.XmlTemplateHtml_id = {ViewName}.XmlTemplateHtml_id',
			'label' => 'HTML-шаблон отображения документа',
			'save' => 'trim|required|min_length[1]|spec_chars',
			'type' => 'string'
		);
		$arr['region_id'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['isdeleted'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
		);
		// Настройки шаблона
		$arr['caption'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'XmlTemplate_Caption',
			'label' => 'Наименование шаблона',
			'save' => 'ban_percent|trim|required|max_length[200]',
			'type' => 'string'
		);
		$arr['xmltemplatecat_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'XmlTemplateCat_id',
			'label' => 'Папка',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['evnclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnClass_id',
			'label' => 'Категория учетного документа',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['xmltype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'XmlType_id',
			'label' => 'Тип документа',
			'save' => 'trim',
			'type' => 'id',
			'default' => 2,
		);
		$arr['xmltypekind_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'XmlTypeKind_id',
			'label' => 'Вид документа',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['xmltemplatesettings_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['printsettings'] = array(
			'properties' => array(
			),
			'select' => 'xts.XmlTemplateSettings_Settings as printsettings',
			'join' => 'left join XmlTemplateSettings xts (nolock) on xts.XmlTemplateSettings_id = {ViewName}.XmlTemplateSettings_id',
		);
		$arr['xmltemplatescope_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'XmlTemplateScope_id',
			'label' => 'Тип доступа для видимости',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['xmltemplatescope_eid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'XmlTemplateScope_eid',
			'label' => 'Тип доступа для изменения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['pmuser_name'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
			),
			'alias' => 'PMUser_Name',
			'select' => "case when v_pmUserCache.PMUser_Login is null then ''
else rtrim(v_pmUserCache.PMUser_surName) +' '+left(v_pmUserCache.PMUser_firName,1) + (case when len(v_pmUserCache.PMUser_firName) > 0 then '.' else '' end) + left(v_pmUserCache.PMUser_secName,1) + (case when len(v_pmUserCache.PMUser_secName) > 0 then '.' else '' end) +  ' (' + rtrim(v_pmUserCache.PMUser_Login) + ')'
end as PMUser_Name",
			'join' => 'left join v_pmUserCache (nolock) on v_pmUserCache.PMUser_id = {ViewName}.pmUser_insID',
		);
		$arr['lpu_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_id',
			'select' => 'v_Lpu.Lpu_id',
			'join' => 'left join v_Lpu (nolock) on v_Lpu.Lpu_id = isnull({ViewName}.Lpu_id, v_pmUserCache.Lpu_id)',
			'label' => 'МО автора',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpu_name'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
			),
			'alias' => 'Lpu_Name',
			'select' => 'v_Lpu.Lpu_Nick as Lpu_Name',
		);
		$arr['lpusection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_id',
			'select' => 'v_LpuSection.LpuSection_id',
			'join' => 'left join v_LpuSection (nolock) on v_LpuSection.LpuSection_id = {ViewName}.LpuSection_id and v_LpuSection.Lpu_id = v_Lpu.Lpu_id',
			'label' => 'Отделение автора',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusection_name'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
			),
			'alias' => 'LpuSection_Name',
			'select' => 'v_LpuSection.LpuSection_FullName as LpuSection_Name',
		);
		$arr['uslugaidlist'] = array(
			'properties' => array(
			),
			'alias' => 'UslugaComplex_id_list',
			'label' => 'Услуги',
			'save' => 'trim',
			'type' => 'string',
			'select' => "STUFF(
				(
					SELECT ','+CAST(xtl.UslugaComplex_id as varchar)
					FROM XmlTemplateLink xtl WITH (nolock)
					WHERE xtl.XmlTemplate_id = v_XmlTemplate.XmlTemplate_id
					FOR XML PATH ('')
				), 1, 1, ''
			) as uslugaidlist",
		);
		// для шаблона плана назначений
		$arr['personagegroup_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PersonAgeGroup_id',
			'label' => 'Возрастная группа',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
			'label' => 'Диагноз или группа диагнозов',
			'save' => 'trim',
			'type' => 'id'
		);
		// Поля, устареющие после внедрения новой структуры хранения
		/*
		$arr['xmltemplate_data'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['xmltemplate_htmltemplate'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['xmltemplate_settings'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
		);
		*/
		$arr['xmlschema_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'XmlSchema_id',
		);
		$arr['xmlschema_data'] = array(
			'properties' => array(
				self::PROPERTY_NOT_SAFE,
			),
			'select' => 'xs.xmlschema_data',
			'join' => 'left join XmlSchema xs (nolock) on xs.XmlSchema_id = {ViewName}.XmlSchema_id',
		);
		return $arr;
	}

	/**
	 * @param string $fields
	 * @param string $viewName
	 * @param string $joins
	 * @param string $where
	 * @param array $params
	 * @return array
	 */
	protected function _beforeQuerySavedData($fields, $viewName, $joins, $where, $params)
	{
		$sql = "
			select top 1 {$fields}
			from {$viewName} with (nolock)
			{$joins}
			where {$where}
				and ISNULL({$viewName}.{$this->tableName()}_IsDeleted, 1) = 1
		";
		// throw new Exception(getDebugSql($sql, $params));
		return array(
			'sql' => $sql,
			'params' => $params,
		);
	}

	/**
	 * @return string
	 */
	function getXmlData()
	{
		$res = $this->getAttribute('xmldata');
		/*if ($this->_isAllowNewTables) {
			// данные перенесены в XmlTemplateData, всегда возвращаем xmldata
			return $res;
		}
		if (empty($res)) {
			// данные НЕ перенесены или ещё не создана запись в XmlTemplateData
			$this->setAttribute('xmldata', $this->XmlTemplate_Data);
			$res = $this->XmlTemplate_Data;
		}*/
		return $res;
	}

	/**
	 * @return string
	 */
	function getHtmlTemplate()
	{
		$res = $this->getAttribute('htmltemplate');
		/*if ($this->_isAllowNewTables) {
			// данные перенесены в XmlTemplateHtml, всегда возвращаем printsettings
			return $res;
		}
		if (empty($res)) {
			// данные НЕ перенесены или ещё не создана запись в XmlTemplateHtml
			$this->setAttribute('htmltemplate', $this->XmlTemplate_HtmlTemplate);
			$res = $this->XmlTemplate_HtmlTemplate;
		}*/
		return $res;
	}

	/**
	 * @return string
	 */
	function getPrintSettings()
	{
		$res = $this->getAttribute('printsettings');
		/*if ($this->_isAllowNewTables) {
			// данные перенесены в XmlTemplateSettings, всегда возвращаем printsettings
			return $res;
		}
		if (empty($res)) {
			// данные НЕ перенесены или ещё не создана запись в XmlTemplateSettings
			$this->setAttribute('printsettings', $this->XmlTemplate_Settings);
			$res = $this->XmlTemplate_Settings;
		}*/
		return $res;
	}

	/**
	 * Извлечение значений служебных параметров модели из входящих параметров
	 * @param array $data
	 * @return void
	 */
	public function setParams($data)
	{
		parent::setParams($data);
		if (true) {
			$this->_params['MedPersonal_id'] = empty($data['MedPersonal_id']) ? null : $data['MedPersonal_id'];
			if ( empty($this->_params['MedPersonal_id']) && isset($data['session']['medpersonal_id']) ) {
				$this->_params['MedPersonal_id'] = $data['session']['medpersonal_id'];
			}

			$this->_params['LpuSection_id'] = empty($data['LpuSection_id']) ? null : $data['LpuSection_id'];
			if ( empty($this->_params['LpuSection_id']) && isset($data['session']['CurLpuSection_id']) ) {
				$this->_params['LpuSection_id'] = $data['session']['CurLpuSection_id'];
			}

			$this->_params['Lpu_id'] = empty($data['Lpu_id']) ? null : $data['Lpu_id'];
			if ( empty($this->_params['Lpu_id']) && isset($data['session']['lpu_id']) ) {
				$this->_params['Lpu_id'] = $data['session']['lpu_id'];
			}

			$this->_params['MedStaffFact_id'] = empty($data['MedStaffFact_id']) ? null : $data['MedStaffFact_id'];
			if ( empty($this->_params['MedStaffFact_id']) && isset($data['session']['CurMedStaffFact_id']) ) {
				$this->_params['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
			}

			$this->_params['MedService_id'] = empty($data['MedService_id']) ? null : $data['MedService_id'];
			if ( empty($this->_params['MedService_id']) && isset($data['session']['CurMedService_id']) ) {
				$this->_params['MedService_id'] = $data['session']['CurMedService_id'];
			}
		}
		if (self::SCENARIO_LOAD_GRID == $this->scenario) {
			$this->_params['showXmlTemplate_id'] = empty($data['showXmlTemplate_id']) ? null : $data['showXmlTemplate_id'];
			$this->_params['UslugaComplex_id'] = empty($data['UslugaComplex_id']) ? null : $data['UslugaComplex_id'];
			$this->_params['templName'] = empty($data['templName']) ? null : $data['templName'];
			$this->_params['templType'] = empty($data['templType']) ? null : $data['templType'];
			$this->_params['XmlTemplate_onlyOld'] = empty($data['XmlTemplate_onlyOld']) ? null : $data['XmlTemplate_onlyOld'];
			$this->_params['start'] = empty($data['start']) ? 0 : $data['start'];
			$this->_params['limit'] = empty($data['limit']) ? 50 : $data['limit'];
		}
		if ('select' == $this->scenario) {
			$this->_params['Server_id'] = null;
			if ( isset($data['session']['server_id']) ) {
				$this->_params['Server_id'] = $data['session']['server_id'];
			}
		}
		if ('' == $this->scenario) {
			$this->_params['Evn_id'] = empty($data['Evn_id']) ? null : $data['Evn_id'];
		}
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = array();
		switch ($name) {
			case self::SCENARIO_DO_SAVE:
				$rules =  array(
					array('field' => 'XmlTemplate_id','label' => 'Идентификатор шаблона','rules' => 'trim','type' => 'id'),
					//array('field' => 'XmlTemplate_Caption','label' => 'Наименование','rules' => 'trim|required|min_length[1]','type' => 'string'),
					array('field' => 'EvnClass_id','label' => 'Категория','rules' => 'trim','type' => 'id'),
					array('field' => 'XmlTemplateCat_id','label' => 'Папка','rules' => 'trim','type' => 'id'),
					array('field' => 'XmlType_id','label' => 'Тип документа','rules' => 'trim','type' => 'id', 'default' => 2),
					array('field' => 'XmlTypeKind_id','label' => 'Вид документа','rules' => 'trim','type' => 'id'),
					array('field' => 'XmlTemplateScope_id','label' => 'Видимость','rules' => 'trim','type' => 'id'),
					array('field' => 'XmlTemplateScope_eid','label' => 'Доступность для изменения','rules' => 'trim','type' => 'id'),

					array('field' => 'XmlTemplate_HtmlTemplate','label' => 'Шаблон','rules' => 'trim|required|min_length[1]|spec_chars','type' => 'string'),
					// для возможности конвертировать
					array('field' => 'XmlTemplateType_id','label' => 'Тип шаблона','rules' => 'trim','type' => 'id'),
				);
				break;
			case 'saveSettings':
				$rules =  array(
					array('field' => 'XmlTemplate_id','label' => 'Идентификатор шаблона','rules' => 'trim|required','type' => 'id'),
					array('field' => 'XmlTemplate_Caption','label' => 'Наименование','rules' => 'trim|required|min_length[1]','type' => 'string'),
					array('field' => 'EvnClass_id','label' => 'Категория','rules' => 'trim|required','type' => 'id'),
					array('field' => 'XmlTemplateCat_id','label' => 'Папка','rules' => 'trim','type' => 'id'),
					array('field' => 'XmlType_id','label' => 'Тип документа','rules' => 'trim|required','type' => 'id'),
					array('field' => 'XmlTypeKind_id','label' => 'Вид документа','rules' => 'trim','type' => 'id'),
					array('field' => 'XmlTemplateScope_id','label' => 'Видимость','rules' => 'trim|required','type' => 'id'),
					array('field' => 'XmlTemplateScope_eid','label' => 'Доступность для изменения','rules' => 'trim|required','type' => 'id'),

					array('field' => 'UslugaComplex_id_list','label' => 'Услуги','rules' => 'trim','type' => 'string'),

					array('field' => 'PaperFormat_id','label' => 'Размер бумаги','rules' => 'trim|required','type' => 'id'),
					array('field' => 'PaperOrient_id','label' => 'Ориентация','rules' => 'trim|required','type' => 'id'),
					array('field' => 'FontSize_id','label' => 'Шрифт','rules' => 'trim|required','type' => 'id'),
					array('field' => 'margin_left','label' => '','rules' => 'trim|required','type' => 'int'),
					array('field' => 'margin_top','label' => '','rules' => 'trim|required','type' => 'int'),
					array('field' => 'margin_bottom','label' => '','rules' => 'trim|required','type' => 'int'),
					array('field' => 'margin_right','label' => '','rules' => 'trim|required','type' => 'int'),
					array('field' => 'base_fontsize','label' => '','rules' => 'trim','type' => 'int'),
					array('field' => 'base_fontfamily','label' => '','rules' => 'trim','type' => 'int')
				);
				break;
			case 'doCopy': // merge self::SCENARIO_DO_SAVE and 'saveSettings'
				$rules =  array(
					array('field' => 'XmlTemplate_id','label' => 'Идентификатор шаблона','rules' => 'trim','type' => 'id'),
					array('field' => 'XmlTemplate_Caption','label' => 'Наименование','rules' => 'trim|required|min_length[1]','type' => 'string'),
					array('field' => 'EvnClass_id','label' => 'Категория','rules' => 'trim|required','type' => 'id'),
					array('field' => 'XmlTemplateCat_id','label' => 'Папка','rules' => 'trim','type' => 'id'),
					array('field' => 'XmlType_id','label' => 'Тип документа','rules' => 'trim|required','type' => 'id'),
					array('field' => 'XmlTemplateScope_id','label' => 'Видимость','rules' => 'trim|required','type' => 'id'),
					array('field' => 'XmlTemplateScope_eid','label' => 'Доступность для изменения','rules' => 'trim|required','type' => 'id'),
					array('field' => 'XmlTemplate_HtmlTemplate','label' => 'Шаблон','rules' => 'trim|required|min_length[1]|spec_chars','type' => 'string'),
					// для возможности конвертировать
					array('field' => 'XmlTemplateType_id','label' => 'Тип шаблона','rules' => 'trim','type' => 'id'),

					array('field' => 'UslugaComplex_id_list','label' => 'Услуги','rules' => 'trim','type' => 'string'),

					array('field' => 'PaperFormat_id','label' => 'Размер бумаги','rules' => 'trim|required','type' => 'id'),
					array('field' => 'PaperOrient_id','label' => 'Ориентация','rules' => 'trim|required','type' => 'id'),
					array('field' => 'FontSize_id','label' => 'Шрифт','rules' => 'trim|required','type' => 'id'),
					array('field' => 'margin_left','label' => '','rules' => 'trim|required','type' => 'int'),
					array('field' => 'margin_top','label' => '','rules' => 'trim|required','type' => 'int'),
					array('field' => 'margin_bottom','label' => '','rules' => 'trim|required','type' => 'int'),
					array('field' => 'margin_right','label' => '','rules' => 'trim|required','type' => 'int'),
					array('field' => 'base_fontsize','label' => '','rules' => 'trim','type' => 'int'),
					array('field' => 'base_fontfamily','label' => '','rules' => 'trim','type' => 'int')
				);
				break;
			case self::SCENARIO_LOAD_GRID:
				$rules = array(
					array('field' => 'EvnClass_id', 'label' => 'Идентификатор', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'XmlType_id', 'label' => 'Идентификатор', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'XmlTypeKind_id', 'label' => 'Идентификатор', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'LpuSection_id', 'label' => 'Идентификатор', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'XmlTemplateCat_id', 'label' => 'Идентификатор', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'showXmlTemplate_id', 'label' => 'Идентификатор', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'templName', 'label' => 'Строка поиска', 'rules' => 'ban_percent|trim', 'type' => 'string'),
					array('field' => 'templType', 'label' => 'Тип Поиска', 'rules' => 'trim', 'type' => 'int'),
					array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => 'trim', 'default' => 0, 'type' => 'int'),
					array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => 'trim', 'default' => 50, 'type' => 'int'),
					array('field' => 'XmlTemplate_onlyOld', 'label' => 'Признак того, что нужно отобразить только шаблоны, которые нуждаются в пересоздании вручную', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => 'trim', 'type' => 'id'),
				);
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
				$rules['id'] = array(
					'field' => 'XmlTemplate_id',
					'label' => 'Идентификатор шаблона',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				break;
			case self::SCENARIO_DELETE:
				$rules['id'] = array(
					'field' => 'XmlTemplate_id',
					'label' => 'Идентификатор шаблона',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['LpuSection_id'] = array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор',
					'rules' => 'trim',
					'type' => 'id'
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
		if (!in_array($this->scenario, $this->scenarioList)) {
			throw new Exception('Эта функция не реализована', 500);
		}
		if (in_array($this->scenario,array('saveSettings','update', 'select', self::SCENARIO_DELETE, self::SCENARIO_LOAD_EDIT_FORM)) && empty($this->id)) {
			throw new Exception('Вы не указали шаблон', 400);
		}
		if (in_array($this->scenario,array('saveSettings','update', 'create')) && empty($this->XmlTemplateType_id)) {
			throw new Exception('Вы не указали тип шаблона', 400);
		}
		if (in_array($this->scenario,array('saveSettings','update', 'create')) && empty($this->caption)
			&& swXmlTemplate::EVN_PRESCR_PLAN_TYPE_ID != $this->XmlTemplateType_id
		) {
			throw new Exception('Вы не указали наименование шаблона', 400);
		}
		if (in_array($this->scenario,array('update', 'create')) && empty($this->XmlSchema_id)
			&& swXmlTemplate::OLD_TYPE_ID == $this->XmlTemplateType_id
		) {
			// Первые шаблоны, которые ещё делал Ваня Пшеницын имеют нестандартную схему проверки
			throw new Exception('Вы не указали схему проверки данных формы', 400);
		}
		if (in_array($this->scenario,array('update', 'create')) && empty($this->xmlData)) {
			throw new Exception('Вы не указали шаблон формы', 400);
		}
		if (in_array($this->scenario,array('saveSettings','update', 'create', 'select', self::SCENARIO_DELETE)) && empty($this->promedUserId)) {
			throw new Exception('Не указан пользователь', 500);
		}
		if (in_array($this->scenario,array('update', 'create')) && empty($this->htmlTemplate)
			&& swXmlTemplate::EVN_PRESCR_PLAN_TYPE_ID != $this->XmlTemplateType_id
		) {
			throw new Exception('Вы не указали шаблон документа', 400);
		}
		if (in_array($this->scenario,array('update', 'create')) && empty($this->XmlType_id)
			&& swXmlTemplate::EVN_PRESCR_PLAN_TYPE_ID != $this->XmlTemplateType_id
		) {
			throw new Exception('Вы не указали тип документа', 400);
		}
		/*if (in_array($this->scenario,array('update', 'create'))
			&& swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID == $this->XmlType_id
			&& !$this->_isHasSection('resolution')
		) {
			throw new Exception('Протокол оказания услуги должен содержать раздел "Заключение"', 400);
		}*/
		if ( in_array($this->scenario,array('saveSettings','update', 'create', self::SCENARIO_DELETE))
			&& swXmlTemplate::EVN_PRESCR_PLAN_TYPE_ID != $this->XmlTemplateType_id
			&& false == $this->_hasAccessWrite()
		) {
			throw new Exception('Нет доступа на редактирование/удаление шаблона', 403);
		}
		if ( in_array($this->scenario,array('saveSettings','update'))
			&& $this->_isAttributeChanged('lpu_id')
			&& false == $this->isNewRecord
		) {
			throw new Exception('Нельзя изменить МО автора', 400);
		}
		if ( $this->isNewRecord && empty($this->_params['Lpu_id'])
			&& false == swXmlTemplate::isDisableDefaults($this->sessionParams)
		) {
			throw new Exception('Не указано МО автора', 400);
		}
		if ( in_array($this->scenario,array('saveSettings','update'))
			&& $this->_isAttributeChanged('lpusection_id')
			&& false == $this->isNewRecord
		) {
			throw new Exception('Нельзя изменить отделение автора', 400);
		}
		if ( $this->isNewRecord && empty($this->_params['LpuSection_id'])
			&& false == swXmlTemplate::isDisableDefaults($this->sessionParams)
			// службы могут быть не на отделении
			&& empty($this->_params['MedService_id'])
		) {
			throw new Exception('Не указано отделение автора', 400);
		}
		if (in_array($this->scenario,array('saveSettings','update', 'create'))
			&& empty($this->XmlTemplateCat_id)
			&& swXmlTemplate::EVN_PRESCR_PLAN_TYPE_ID != $this->XmlTemplateType_id
			&& false == swXmlTemplate::isAllowRootFolder($this->sessionParams)
		) {
			throw new Exception('Вы не указали папку', 400);
		}
		if (swXmlTemplate::EVN_PRESCR_PLAN_TYPE_ID == $this->XmlTemplateType_id && in_array($this->scenario,array('update', 'create','checkExistsEvnPrescrPlan'))) {
			if (empty($this->Diag_id)) {
				throw new Exception('Вы не указали группу диагнозов', 400);
			}
			if (empty($this->PersonAgeGroup_id)) {
				throw new Exception('Вы не указали возрастную группу', 400);
			}
		}
		if (in_array($this->scenario,array('saveSettings','update', 'create'))
			&& swXmlTemplate::EVN_PRESCR_PLAN_TYPE_ID != $this->XmlTemplateType_id
			&& false == swXmlTemplate::isDisableDefaults($this->sessionParams)
		) {
			$this->load->model('XmlTemplateCatDefault_model');
			$this->load->model('XmlTemplateCat_model');
			$data = swXmlTemplate::checkFolder($this->XmlTemplateCatDefault_model,
				$this->XmlTemplateCat_model,
				array(
					'session' => $this->sessionParams,
					'MedStaffFact_id' => $this->_params['MedStaffFact_id'],
					'MedService_id' => $this->_params['MedService_id'],
					'LpuSection_id'=>$this->_params['LpuSection_id'],
					'EvnClass_id' => $this->EvnClass_id,
					'XmlType_id' => $this->XmlType_id,
					'XmlTemplateCat_id' => $this->XmlTemplateCat_id,
				)
			);
			$this->setAttribute('xmltemplatecat_id', $data['XmlTemplateCat_id']);
		}
		if (self::SCENARIO_LOAD_GRID == $this->scenario
			&& ($this->_params['start'] < 0 || $this->_params['limit'] < 0)
		) {
			throw new Exception('Указаны плохие параметры постраничного вывода', 400);
		}
		if (in_array($this->scenario, array('select')) && !isset($this->_params['Server_id'])) {
			throw new Exception('Не указан источник данных', 500);
		}
		if (in_array($this->scenario, array('update', 'create'))) {
			if (preg_match_all('#<div\s+class="printonly"(.*?)</div>#siu', $this->htmlTemplate, $matches)) {
				if (!empty($matches[1]) && is_array($matches[1])) {
					foreach($matches[1] as $match) {
						if (mb_strpos($match, 'template-block-data') !== false) {
							throw new Exception('Области ввода нельзя размещать внутри полей «Только для печати»', 400);
						}
					}
				}
			}
		}

		return true;
	}
	/**
	 * Проверка наличия и целостности в шаблоне указанного раздела
	 * @param string $name
	 * @return bool
	 */
	protected function _isHasSection($name)
	{
		if (false === strpos($this->htmlTemplate, 'div class="template-block" id="block_'.$name.'"')) {
			return false;
		}
		if (false === strpos($this->htmlTemplate, 'p class="template-block-caption" id="caption_'.$name.'"')) {
			return false;
		}
		if (false === strpos($this->htmlTemplate, 'div class="template-block-data" id="data_'.$name.'"')) {
			return false;
		}
		if (false === strpos($this->htmlTemplate, '{'.$name.'}')) {
			return false;
		}
		return true;
	}

	/**
	 * Чтение списка шаблонов и папок для грида,
	 * из которого производится редактирование и выбор шаблонов для документа
	 *
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	public function loadGrid($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_GRID;
		$this->applyData($data);
		$this->_validate();

		$params = array(
			'Lpu_id'=>$this->_params['Lpu_id'],
			'LpuSection_id'=>$this->_params['LpuSection_id'],
			'MedStaffFact_id'=>$this->_params['MedStaffFact_id'],
		);

		$filter = '';
		$filter_tpl = '';
		$filter_cat = '';
		$add_joins = '';
		$add_select = '';
		$add_orders = '';
		if (empty($this->EvnClass_id)) {
			$filter_tpl .= " and (xt.EvnClass_id is null OR xt.EvnClass_id in (".implode(",",$this->_supportEvnClassList).")) ";
		} else if(!in_array($this->EvnClass_id, $this->_supportEvnClassList)) {
			$filter .= " and (2=1) ";
		} else {
			$params['EvnClass_id'] = $this->EvnClass_id;
			$filter_tpl .= "
			 and (xt.EvnClass_id is null OR xt.EvnClass_id = :EvnClass_id) ";
		}
		$isNeedItemPath = false;
		$html_join = '';
		if (!empty($this->_params['templName'])) {
			$params['templName'] = "%".$this->_params['templName']."%";
			if ($this->_params['templType']==1) {
				$filter_tpl .= " and xt.XmlTemplate_Caption like :templName";
				$filter_cat .= " and xtc.XmlTemplateCat_Name like :templName";
			}
			if ($this->_params['templType']==2) {
				//if ($this->_isAllowNewTables) {
					$html_join = 'inner join XmlTemplateHtml xth (nolock) on xth.XmlTemplateHtml_id = xt.XmlTemplateHtml_id
						and xth.XmlTemplateHtml_HtmlTemplate like :templName';
				/*} else {
					$html_join = 'left join XmlTemplateHtml xth (nolock) on xth.XmlTemplateHtml_id = xt.XmlTemplateHtml_id';
					$filter_tpl .= " and ISNULL(xth.XmlTemplateHtml_HtmlTemplate, xt.XmlTemplate_HtmlTemplate) like :templName";
				}*/
				$filter_cat .= " and 1=2";
			}
			$isNeedItemPath = true;
		} else {
			if (empty($this->XmlTemplateCat_id)) {
				//показываем содержимое корневой папки
				$filter_tpl .= "
				 and (xt.XmlTemplateCat_id is null) ";
				$filter_cat .= "
				 and (xtc.XmlTemplateCat_pid is null) ";
			} else {
				$params['XmlTemplateCat_id'] = $this->XmlTemplateCat_id;
				$filter_tpl .= "
				 and (xt.XmlTemplateCat_id = :XmlTemplateCat_id) ";
				$filter_cat .= "
				 and (xtc.XmlTemplateCat_pid = :XmlTemplateCat_id) ";
			}
		}
		if (!empty($this->XmlType_id)) {
			$params['XmlType_id'] = $this->XmlType_id;
			$filter_tpl .= "
			 and (xt.XmlType_id is null or xt.XmlType_id = :XmlType_id) ";

			// оставляем возможность видеть старые шаблоны суперадмину и авторам
			if (isSuperadmin()) {
				if (!empty($this->_params['XmlTemplate_onlyOld'])) {
					$filter_tpl .= "
						 and (xt.XmlTemplateType_id in (1,2,3,5)) ";//4,
				}
			} else {
				switch ($this->XmlType_id) {
					case swEvnXml::MULTIPLE_DOCUMENT_TYPE_ID:
					case swEvnXml::STAC_EPIKRIZ_TYPE_ID:
					case swEvnXml::STAC_PROTOCOL_TYPE_ID:
					case swEvnXml::STAC_RECORD_TYPE_ID:
					case swEvnXml::EVN_VIZIT_PROTOCOL_TYPE_ID: // то же условие
						$filter_tpl .= "
						 and (xt.pmUser_insID = :pmUser_id or xt.XmlTemplateType_id = 6) ";
						break;
					case swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID:
						$filter_tpl .= "
						 and (xt.pmUser_insID = :pmUser_id or xt.XmlTemplateType_id = 7) ";
						break;
					case swEvnXml::LAB_USLUGA_PROTOCOL_TYPE_ID:
						$filter_tpl .= "
						 and (xt.pmUser_insID = :pmUser_id or xt.XmlTemplateType_id = 9) ";
						break;
				}
			}
		} else {
			// оставляем возможность видеть старые шаблоны суперадмину и авторам
			if (isSuperadmin()) {
				if (!empty($this->_params['XmlTemplate_onlyOld'])) {
					$filter_tpl .= "
						 and (xt.XmlTemplateType_id in (1,2,3,5)) ";//4,
				}
			} else {
				$filter_tpl .= "
			 and (xt.pmUser_insID = :pmUser_id or xt.XmlTemplateType_id in (6,7))";
			}
		}
		
		if (!empty($this->XmlTypeKind_id)) {
			$params['XmlTypeKind_id'] = $this->XmlTypeKind_id;
			$filter_tpl .= " and xt.XmlTypeKind_id = :XmlTypeKind_id ";
		}

		// по умолчанию нет необходимости цеплять услуги шаблона
		$filter_xtuc = 'and 1=2';
		if (!empty($this->_params['UslugaComplex_id'])) {
			// надо осуществлять фильтрацию и сортировку шаблонов протоколов следующим образом
			// (с учетом свойств видимости шаблонов):
			// сначала шаблоны, в которых есть оказываемая услуга,
			// потом шаблоны без привязки к услуге,
			// затем все прочие
			$params['UslugaComplex_id'] = $this->_params['UslugaComplex_id'];
			// надо подцепить услугу шаблона, которая равна параметру UslugaComplex_id
			$filter_xtuc = 'and xtl.UslugaComplex_id=:UslugaComplex_id';
			$add_orders .= 'case
				when xtu.UslugaComplex_id = :UslugaComplex_id then 1
				when xtu.UslugaComplex_id is null then 2
				else 3
			end,';
			/*
			$filter_tpl .= "
			 and (
			    xt.UslugaComplex_id = :UslugaComplex_id
			    or xt.UslugaComplex_id is null
			    or xt.XmlTemplate_id in (
				    select t.XmlTemplate_id from v_UslugaComplex ucs with(nolock)
					inner join  v_UslugaComplex uc11 with(nolock) on ucs.UslugaComplex_2011id = uc11.UslugaComplex_2011id
					inner join  v_XmlTemplate t with(nolock) on t.UslugaComplex_id = uc11.UslugaComplex_id
					where ucs.UslugaComplex_id = :UslugaComplex_id
				)
			 )";
			*/
		}

		if (!empty($this->_params['showXmlTemplate_id'])) {
			$params['showXmlTemplate_id'] = $this->_params['showXmlTemplate_id'];
			$add_orders .= ' case when xtu.XmlTemplate_id = :showXmlTemplate_id then 1 else 2 end, ';
		}

		$params = array_merge($params,
			swXmlTemplate::getAccessRightsQueryParams($this->_params['Lpu_id'], $this->_params['LpuSection_id'], $this->promedUserId)
		);
		$accessType = swXmlTemplate::getAccessRightsQueryPart('xtu', 'XmlTemplate', false);
		$visibleFilter = swXmlTemplate::getAccessRightsQueryPart('xtu', 'XmlTemplate', true);

		if ($isNeedItemPath) {
			$add_joins .= 'left join dbo.v_XmlTemplateCat p0 with (nolock) on xtu.XmlTemplateCat_pid = p0.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p1 with (nolock) on p0.XmlTemplateCat_pid = p1.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p2 with (nolock) on p1.XmlTemplateCat_pid = p2.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p3 with (nolock) on p2.XmlTemplateCat_pid = p3.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p4 with (nolock) on p3.XmlTemplateCat_pid = p4.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p5 with (nolock) on p4.XmlTemplateCat_pid = p5.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p6 with (nolock) on p5.XmlTemplateCat_pid = p6.XmlTemplateCat_id';
			$accessType0 = swXmlTemplate::getAccessRightsQueryPart('p0', 'XmlTemplateCat', false);
			$accessType1 = swXmlTemplate::getAccessRightsQueryPart('p1', 'XmlTemplateCat', false);
			$accessType2 = swXmlTemplate::getAccessRightsQueryPart('p2', 'XmlTemplateCat', false);
			$accessType3 = swXmlTemplate::getAccessRightsQueryPart('p3', 'XmlTemplateCat', false);
			$accessType4 = swXmlTemplate::getAccessRightsQueryPart('p4', 'XmlTemplateCat', false);
			$accessType5 = swXmlTemplate::getAccessRightsQueryPart('p5', 'XmlTemplateCat', false);
			$accessType6 = swXmlTemplate::getAccessRightsQueryPart('p6', 'XmlTemplateCat', false);
			$add_select = "
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
				,{$accessType6} as accessType6";
		}
		/*$settings_select = 'ISNULL(xts.XmlTemplateSettings_Settings, xt.XmlTemplate_Settings) as XmlTemplate_Settings';
		if ($this->_isAllowNewTables) {*/
			$settings_select = 'xts.XmlTemplateSettings_Settings as XmlTemplate_Settings';
		//}
		$query = "
			Select
			-- select
				xtu.Item_Key,
				xtu.XmlTemplate_id,
				xtu.XmlTemplateType_id,
				{$accessType} as accessType,
				EvnClass.EvnClass_Name,
				xtu.Item_Name,
				puc.pmUser_Name,
				XTCV.XmlTemplateScope_Name as XmlTemplateScope_Name,
				convert(varchar(10),xtu.Item_updDate,104) as Item_updDate,
				xtu.XmlTemplate_Settings,
				xtu.EvnClass_id,
				xtu.XmlType_id,
				xtu.XmlTemplateScope_id,
				xtu.XmlTemplateScope_eid,
				xtu.Lpu_id,
				xtu.LpuSection_id,
				xtu.pmUser_insID,
				xtu.XmlTemplateCat_id,
				case when xtu.Item_Type = 'XmlTemplate' then
					case when xtd.XmlTemplateDefault_id is null then 1 else 2 end
				else
					case when xtdcat.XmlTemplateCatDefault_id is null then 1 else 2 end
				end  as XmlTemplate_Default,
				xtu.Item_Type
				{$add_select}
			-- end select
			from
			-- from
				(
					Select
						'XmlTemplate' as Item_Type,
						'XmlTemplate_'+ convert(varchar,xt.XmlTemplate_id) as Item_Key,
						xt.XmlTemplate_id,
						xt.Lpu_id,
						xt.LpuSection_id,
						xt.pmUser_insID,
						xt.XmlTemplateScope_eid,
						xt.XmlTemplate_Caption as Item_Name,
						xt.XmlTemplate_updDT as Item_updDate,
						{$settings_select},
						xt.EvnClass_id,
						isnull(xt.XmlType_id,1) as XmlType_id,
						xt.XmlTemplateCat_id,
						xt.XmlTemplateScope_id,
						xt.XmlTemplateCat_id as XmlTemplateCat_pid,
						xtuc.UslugaComplex_id,
						xt.XmlTemplateType_id
					from
						v_XmlTemplate xt with (NOLOCK)
						left join XmlTemplateSettings xts (nolock) on xts.XmlTemplateSettings_id = xt.XmlTemplateSettings_id
						{$html_join}
						outer apply (
							select top 1 xtl.UslugaComplex_id
							from XmlTemplateLink xtl with (NOLOCK)
							where xtl.XmlTemplate_id = xt.XmlTemplate_id
							{$filter_xtuc}
						) xtuc
					where
						isnull(xt.XmlTemplate_IsDeleted,1) = 1 {$filter_tpl}
				union all
					Select
						'XmlTemplateCat' as Item_Type,
						'XmlTemplateCat_'+ convert(varchar,xtc.XmlTemplateCat_id) as Item_Key,
						null as XmlTemplate_id,
						xtc.Lpu_id,
						xtc.LpuSection_id,
						xtc.pmUser_insID,
						xtc.XmlTemplateScope_eid,
						xtc.XmlTemplateCat_Name as Item_Name,
						xtc.XmlTemplateCat_updDT as Item_updDate,
						'' as XmlTemplate_Settings,
						null as EvnClass_id,
						null as XmlType_id,
						xtc.XmlTemplateCat_id,
						xtc.XmlTemplateScope_id,
						xtc.XmlTemplateCat_pid,
						null as UslugaComplex_id,
						null as XmlTemplateType_id
					from
						v_XmlTemplateCat xtc with (NOLOCK)
					where
						(1=1) {$filter_cat}

				) xtu
				left join EvnClass with (NOLOCK) on EvnClass.EvnClass_id = xtu.EvnClass_id
				left join XmlTemplateScope XTCV with (NOLOCK) on XTCV.XmlTemplateScope_id = xtu.XmlTemplateScope_id
				left join v_pmUserCache puc with (NOLOCK) on puc.PMUser_id = xtu.pmUser_insID
				left join v_XmlTemplateDefault xtd with (NOLOCK) on xtd.XmlTemplate_id = xtu.XmlTemplate_id and
					xtd.MedStaffFact_id = :MedStaffFact_id and
					isnull(xtd.XmlType_id,1) = xtu.XmlType_id and
					xtd.pmUser_insID = :pmUser_id
				left join v_XmlTemplateCatDefault xtdcat with (NOLOCK) on xtdcat.MedStaffFact_id = :MedStaffFact_id and
					xtdcat.EvnClass_id = xtu.EvnClass_id and
					isnull(xtdcat.XmlType_id,1) = xtu.XmlType_id and
					xtdcat.pmUser_insID = :pmUser_id
				left join XmlTemplate t with (NOLOCK) on t.XmlTemplate_id = xtu.XmlTemplate_id
				{$add_joins}
			-- end from
			where
			-- where
				(1=1) {$filter}
				and {$visibleFilter}
			-- end where
			order by
			-- order by
				{$add_orders} xtu.Item_Type desc, xtu.Item_Name
			-- end order by
			";

		//echo getDebugSql($query, $params);exit;

		$response = $this->getPagingResponse($query, $params, $this->_params['start'], $this->_params['limit'], true);

		if (is_array($response)) {
			$this->load->library('swXmlTemplateSettings');
			foreach($response['data'] as &$row){
				$row['XmlTemplate_Settings'] = swXmlTemplateSettings::getStringFromJson($row['XmlTemplate_Settings']);
				$row['XmlTemplate_Preview'] = $row['XmlTemplate_Settings'];
				if ($isNeedItemPath) {
					$row['Item_Path'] = array();
					$i = 0;
					while (array_key_exists('XmlTemplateCat_pid'.$i, $row)
						&& array_key_exists('XmlTemplateCat_Name'.$i, $row)
						&& array_key_exists('accessType'.$i, $row)
					) {
						if (isset($row['XmlTemplateCat_pid'.$i])
							&& isset($row['XmlTemplateCat_Name'.$i])
							&& isset($row['accessType'.$i])
						) {
							$row['Item_Path'][] = array(
								'XmlTemplateCat_id' => $row['XmlTemplateCat_pid'.$i],
								'XmlTemplateCat_Name' => $row['XmlTemplateCat_Name'.$i],
								'accessType' => $row['accessType'.$i],
							);
						}
						unset($row['XmlTemplateCat_pid'.$i]);
						unset($row['XmlTemplateCat_Name'.$i]);
						unset($row['accessType'.$i]);
						$i++;
					}
					$row['Item_Path'] = json_encode($row['Item_Path']);
				} else {
					$row['Item_Path'] = null;
				}
			}

			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * МАРМ-версия
	 * Чтение списка шаблонов и папок для грида,
	 * из которого производится редактирование и выбор шаблонов для документа
	 *
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	public function mLoadGrid($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_GRID;
		$this->applyData($data);
		$this->_validate();

		$params = array(
			'Lpu_id'=>$this->_params['Lpu_id'],
			'LpuSection_id'=>$this->_params['LpuSection_id'],
			'MedStaffFact_id'=>$this->_params['MedStaffFact_id'],
		);

		$filter = '';
		$filter_tpl = '';
		$filter_cat = '';
		$add_joins = '';
		$add_select = '';
		$add_orders = '';
		if (empty($this->EvnClass_id)) {
			$filter_tpl .= " and (xt.EvnClass_id is null OR xt.EvnClass_id in (".implode(",",$this->_supportEvnClassList).")) ";
		} else if(!in_array($this->EvnClass_id, $this->_supportEvnClassList)) {
			$filter .= " and (2=1) ";
		} else {
			$params['EvnClass_id'] = $this->EvnClass_id;
			$filter_tpl .= "
			 and (xt.EvnClass_id is null OR xt.EvnClass_id = :EvnClass_id) ";
		}
		$isNeedItemPath = false;
		$html_join = '';
		if (!empty($this->_params['templName'])) {
			$params['templName'] = "%".$this->_params['templName']."%";
			if ($this->_params['templType']==1) {
				$filter_tpl .= " and xt.XmlTemplate_Caption like :templName";
				$filter_cat .= " and xtc.XmlTemplateCat_Name like :templName";
			}
			if ($this->_params['templType']==2) {
				//if ($this->_isAllowNewTables) {
				$html_join = 'inner join XmlTemplateHtml xth (nolock) on xth.XmlTemplateHtml_id = xt.XmlTemplateHtml_id
						and xth.XmlTemplateHtml_HtmlTemplate like :templName';
				/*} else {
					$html_join = 'left join XmlTemplateHtml xth (nolock) on xth.XmlTemplateHtml_id = xt.XmlTemplateHtml_id';
					$filter_tpl .= " and ISNULL(xth.XmlTemplateHtml_HtmlTemplate, xt.XmlTemplate_HtmlTemplate) like :templName";
				}*/
				$filter_cat .= " and 1=2";
			}
			$isNeedItemPath = true;
		} else {
			if (empty($this->XmlTemplateCat_id)) {
				//показываем содержимое корневой папки
				$filter_tpl .= "
				 and (xt.XmlTemplateCat_id is null) ";
				$filter_cat .= "
				 and (xtc.XmlTemplateCat_pid is null) ";
			} else {
				$params['XmlTemplateCat_id'] = $this->XmlTemplateCat_id;
				$filter_tpl .= "
				 and (xt.XmlTemplateCat_id = :XmlTemplateCat_id) ";
				$filter_cat .= "
				 and (xtc.XmlTemplateCat_pid = :XmlTemplateCat_id) ";
			}
		}
		if (!empty($this->XmlType_id)) {
			$params['XmlType_id'] = $this->XmlType_id;
			$filter_tpl .= "
			 and (xt.XmlType_id is null or xt.XmlType_id = :XmlType_id) ";

			// оставляем возможность видеть старые шаблоны суперадмину и авторам
			if (isSuperadmin()) {
				if (!empty($this->_params['XmlTemplate_onlyOld'])) {
					$filter_tpl .= "
						 and (xt.XmlTemplateType_id in (1,2,3,5)) ";//4,
				}
			} else {
				switch ($this->XmlType_id) {
					case swEvnXml::MULTIPLE_DOCUMENT_TYPE_ID:
					case swEvnXml::STAC_EPIKRIZ_TYPE_ID:
					case swEvnXml::STAC_PROTOCOL_TYPE_ID:
					case swEvnXml::STAC_RECORD_TYPE_ID:
					case swEvnXml::EVN_VIZIT_PROTOCOL_TYPE_ID: // то же условие
						$filter_tpl .= "
						 and (xt.pmUser_insID = :pmUser_id or xt.XmlTemplateType_id = 6) ";
						break;
					case swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID:
						$filter_tpl .= "
						 and (xt.pmUser_insID = :pmUser_id or xt.XmlTemplateType_id = 7) ";
						break;
					case swEvnXml::LAB_USLUGA_PROTOCOL_TYPE_ID:
						$filter_tpl .= "
						 and (xt.pmUser_insID = :pmUser_id or xt.XmlTemplateType_id = 9) ";
						break;
				}
			}
		} else {
			// оставляем возможность видеть старые шаблоны суперадмину и авторам
			if (isSuperadmin()) {
				if (!empty($this->_params['XmlTemplate_onlyOld'])) {
					$filter_tpl .= "
						 and (xt.XmlTemplateType_id in (1,2,3,5)) ";//4,
				}
			} else {
				$filter_tpl .= "
			 and (xt.pmUser_insID = :pmUser_id or xt.XmlTemplateType_id in (6,7))";
			}
		}

		if (!empty($this->XmlTypeKind_id)) {
			$params['XmlTypeKind_id'] = $this->XmlTypeKind_id;
			$filter_tpl .= " and xt.XmlTypeKind_id = :XmlTypeKind_id ";
		}

		// по умолчанию нет необходимости цеплять услуги шаблона
		$filter_xtuc = 'and 1=2';
		if (!empty($this->_params['UslugaComplex_id'])) {
			// надо осуществлять фильтрацию и сортировку шаблонов протоколов следующим образом
			// (с учетом свойств видимости шаблонов):
			// сначала шаблоны, в которых есть оказываемая услуга,
			// потом шаблоны без привязки к услуге,
			// затем все прочие
			$params['UslugaComplex_id'] = $this->_params['UslugaComplex_id'];
			// надо подцепить услугу шаблона, которая равна параметру UslugaComplex_id
			$filter_xtuc = 'and xtl.UslugaComplex_id=:UslugaComplex_id';
			$add_orders .= 'case
				when xtu.UslugaComplex_id = :UslugaComplex_id then 1
				when xtu.UslugaComplex_id is null then 2
				else 3
			end,';
		}

		if (!empty($this->_params['showXmlTemplate_id'])) {
			$params['showXmlTemplate_id'] = $this->_params['showXmlTemplate_id'];
			$add_orders .= ' case when xtu.XmlTemplate_id = :showXmlTemplate_id then 1 else 2 end, ';
		}

		$params = array_merge($params,
			swXmlTemplate::getAccessRightsQueryParams($this->_params['Lpu_id'], $this->_params['LpuSection_id'], $this->promedUserId)
		);
		$accessType = swXmlTemplate::getAccessRightsQueryPart('xtu', 'XmlTemplate', false);
		$visibleFilter = swXmlTemplate::getAccessRightsQueryPart('xtu', 'XmlTemplate', true);

		if ($isNeedItemPath) {
			$add_joins .= 'left join dbo.v_XmlTemplateCat p0 with (nolock) on xtu.XmlTemplateCat_pid = p0.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p1 with (nolock) on p0.XmlTemplateCat_pid = p1.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p2 with (nolock) on p1.XmlTemplateCat_pid = p2.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p3 with (nolock) on p2.XmlTemplateCat_pid = p3.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p4 with (nolock) on p3.XmlTemplateCat_pid = p4.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p5 with (nolock) on p4.XmlTemplateCat_pid = p5.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p6 with (nolock) on p5.XmlTemplateCat_pid = p6.XmlTemplateCat_id';
			$accessType0 = swXmlTemplate::getAccessRightsQueryPart('p0', 'XmlTemplateCat', false);
			$accessType1 = swXmlTemplate::getAccessRightsQueryPart('p1', 'XmlTemplateCat', false);
			$accessType2 = swXmlTemplate::getAccessRightsQueryPart('p2', 'XmlTemplateCat', false);
			$accessType3 = swXmlTemplate::getAccessRightsQueryPart('p3', 'XmlTemplateCat', false);
			$accessType4 = swXmlTemplate::getAccessRightsQueryPart('p4', 'XmlTemplateCat', false);
			$accessType5 = swXmlTemplate::getAccessRightsQueryPart('p5', 'XmlTemplateCat', false);
			$accessType6 = swXmlTemplate::getAccessRightsQueryPart('p6', 'XmlTemplateCat', false);
			$add_select = "
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
				,{$accessType6} as accessType6";
		}
		$settings_select = 'xts.XmlTemplateSettings_Settings as XmlTemplate_Settings';
		$query = "
			Select
			-- select
				xtu.Item_Key,
				xtu.XmlTemplate_id,
				xtu.XmlTemplateType_id,
				{$accessType} as accessType,
				EvnClass.EvnClass_Name,
				xtu.Item_Name,
				puc.pmUser_Name,
				XTCV.XmlTemplateScope_Name as XmlTemplateScope_Name,
				convert(varchar(10),xtu.Item_updDate,104) as Item_updDate,
				xtu.XmlTemplate_Settings,
				xtu.EvnClass_id,
				xtu.XmlType_id,
				xtu.XmlTemplateScope_id,
				xtu.XmlTemplateScope_eid,
				xtu.Lpu_id,
				xtu.LpuSection_id,
				xtu.pmUser_insID,
				xtu.XmlTemplateCat_id,
				case when xtu.Item_Type = 'XmlTemplate' then
					case when xtd.XmlTemplateDefault_id is null then 1 else 2 end
				else
					case when xtdcat.XmlTemplateCatDefault_id is null then 1 else 2 end
				end  as XmlTemplate_Default,
				xtu.Item_Type
				{$add_select}
			-- end select
			from
			-- from
				(
					Select
						'XmlTemplate' as Item_Type,
						'XmlTemplate_'+ convert(varchar,xt.XmlTemplate_id) as Item_Key,
						xt.XmlTemplate_id,
						xt.Lpu_id,
						xt.LpuSection_id,
						xt.pmUser_insID,
						xt.XmlTemplateScope_eid,
						xt.XmlTemplate_Caption as Item_Name,
						xt.XmlTemplate_updDT as Item_updDate,
						{$settings_select},
						xt.EvnClass_id,
						isnull(xt.XmlType_id,1) as XmlType_id,
						xt.XmlTemplateCat_id,
						xt.XmlTemplateScope_id,
						xt.XmlTemplateCat_id as XmlTemplateCat_pid,
						xtuc.UslugaComplex_id,
						xt.XmlTemplateType_id
					from
						v_XmlTemplate xt with (NOLOCK)
						left join XmlTemplateSettings xts (nolock) on xts.XmlTemplateSettings_id = xt.XmlTemplateSettings_id
						{$html_join}
						outer apply (
							select top 1 xtl.UslugaComplex_id
							from XmlTemplateLink xtl with (NOLOCK)
							where xtl.XmlTemplate_id = xt.XmlTemplate_id
							{$filter_xtuc}
						) xtuc
					where
						isnull(xt.XmlTemplate_IsDeleted,1) = 1 {$filter_tpl}
				union all
					Select
						'XmlTemplateCat' as Item_Type,
						'XmlTemplateCat_'+ convert(varchar,xtc.XmlTemplateCat_id) as Item_Key,
						null as XmlTemplate_id,
						xtc.Lpu_id,
						xtc.LpuSection_id,
						xtc.pmUser_insID,
						xtc.XmlTemplateScope_eid,
						xtc.XmlTemplateCat_Name as Item_Name,
						xtc.XmlTemplateCat_updDT as Item_updDate,
						'' as XmlTemplate_Settings,
						null as EvnClass_id,
						null as XmlType_id,
						xtc.XmlTemplateCat_id,
						xtc.XmlTemplateScope_id,
						xtc.XmlTemplateCat_pid,
						null as UslugaComplex_id,
						null as XmlTemplateType_id
					from
						v_XmlTemplateCat xtc with (NOLOCK)
					where
						(1=1) {$filter_cat}

				) xtu
				left join EvnClass with (NOLOCK) on EvnClass.EvnClass_id = xtu.EvnClass_id
				left join XmlTemplateScope XTCV with (NOLOCK) on XTCV.XmlTemplateScope_id = xtu.XmlTemplateScope_id
				left join v_pmUserCache puc with (NOLOCK) on puc.PMUser_id = xtu.pmUser_insID
				left join v_XmlTemplateDefault xtd with (NOLOCK) on xtd.XmlTemplate_id = xtu.XmlTemplate_id and
					xtd.MedStaffFact_id = :MedStaffFact_id and
					isnull(xtd.XmlType_id,1) = xtu.XmlType_id and
					xtd.pmUser_insID = :pmUser_id
				left join v_XmlTemplateCatDefault xtdcat with (NOLOCK) on xtdcat.MedStaffFact_id = :MedStaffFact_id and
					xtdcat.EvnClass_id = xtu.EvnClass_id and
					isnull(xtdcat.XmlType_id,1) = xtu.XmlType_id and
					xtdcat.pmUser_insID = :pmUser_id
				left join XmlTemplate t with (NOLOCK) on t.XmlTemplate_id = xtu.XmlTemplate_id
				{$add_joins}
			-- end from
			where
			-- where
				(1=1) {$filter}
				and {$visibleFilter}
			-- end where
			order by
			-- order by
				{$add_orders} xtu.Item_Type desc, xtu.Item_Name
			-- end order by
			";

		//echo getDebugSql($query, $params);exit;

		$response = $this->getPagingResponse($query, $params, $this->_params['start'], $this->_params['limit'], true);

		if (is_array($response)) {
			$this->load->library('swXmlTemplateSettings');
			foreach($response['data'] as &$row){
				$row['XmlTemplate_Settings'] = swXmlTemplateSettings::getStringFromJson($row['XmlTemplate_Settings']);
				$row['XmlTemplate_Preview'] = $row['XmlTemplate_Settings'];
				if ($isNeedItemPath) {
					$row['Item_Path'] = array();
					$i = 0;
					while (array_key_exists('XmlTemplateCat_pid'.$i, $row)
						&& array_key_exists('XmlTemplateCat_Name'.$i, $row)
						&& array_key_exists('accessType'.$i, $row)
					) {
						if (isset($row['XmlTemplateCat_pid'.$i])
							&& isset($row['XmlTemplateCat_Name'.$i])
							&& isset($row['accessType'.$i])
						) {
							$row['Item_Path'][] = array(
								'XmlTemplateCat_id' => $row['XmlTemplateCat_pid'.$i],
								'XmlTemplateCat_Name' => $row['XmlTemplateCat_Name'.$i],
								'accessType' => $row['accessType'.$i],
							);
						}
						unset($row['XmlTemplateCat_pid'.$i]);
						unset($row['XmlTemplateCat_Name'.$i]);
						unset($row['accessType'.$i]);
						$i++;
					}
					$row['Item_Path'] = json_encode($row['Item_Path']);
				} else {
					$row['Item_Path'] = null;
				}
			}

			return $response;
		}
		else {
			return false;
		}
	}



	/**
	 * Получение простого списка шаблонов
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	public function loadGridForPrint($data)
	{
		
		$filter = '';
		$params = array();
		
		if (!empty($data['EvnClass_id'])) {
			$filter .= ' and xt.EvnClass_id = :EvnClass_id ';
			$params['EvnClass_id'] = $data['EvnClass_id'];
		}
		
		if (!empty($data['XmlType_id'])) {
			$filter .= ' and xt.XmlType_id = :XmlType_id ';
			$params['XmlType_id'] = $data['XmlType_id'];
		}
		
		$params = array_merge($params,
			swXmlTemplate::getAccessRightsQueryParams($data['Lpu_id'], $data['LpuSection_id'], $this->promedUserId)
		);
		$accessType = swXmlTemplate::getAccessRightsQueryPart('xt', 'XmlTemplate', false);
		$visibleFilter = swXmlTemplate::getAccessRightsQueryPart('xt', 'XmlTemplate', true);
		
		$query = "
			Select
			-- select
				xt.XmlTemplate_id,
				xt.XmlTemplateType_id,
				xt.XmlTemplate_Caption,
				xt.XmlTemplateCat_id,
				xtc.XmlTemplateCat_Name,
				xt.EvnClass_id,
				xt.XmlType_id,
				xtype.XmlType_Name,
				puc.pmUser_Name,
				{$accessType} as accessType
			-- end select
			from
			-- from
			v_XmlTemplate xt (nolock)
			inner join v_XmlType (nolock) xtype on xtype.XmlType_id = xt.XmlType_id
			left join v_XmlTemplateCat (nolock) xtc on xtc.XmlTemplateCat_id = xt.XmlTemplateCat_id
			left join v_XmlTemplateEvnClass (nolock) xtew on xtew.XmlTemplate_id = xt.XmlTemplate_id
			left join v_pmUserCache puc (nolock) on puc.PMUser_id = xt.pmUser_insID
			-- end from
			where
			-- where
				(1=1) {$filter} 
				and {$visibleFilter}
				and isnull(xt.XmlTemplate_IsDeleted,1) = 1
			-- end where
			order by
			-- order by
				xt.XmlTemplate_Caption
			-- end order by";

		//echo getDebugSql($query, $params);exit;

		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		
		if (is_array($response)) {
			return $response;
		}
		else {
			return false;
		}
	}
	 

	/**
	 * Получение данных шаблона для редактирования
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	public function doLoadEditForm($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		$this->applyData($data);
		$this->_validate();
		return array(array(
			'XmlTemplate_id' => $this->id,
			'XmlTemplate_Caption' => $this->caption,
			'XmlTemplate_HtmlTemplate' => $this->htmlTemplate,
			'XmlTemplate_Data' => $this->xmlData,
			'XmlSchema_id' => $this->XmlSchema_id,
			'XmlTemplateCat_id' => $this->XmlTemplateCat_id,
			'EvnClass_id' => $this->EvnClass_id,
			'XmlType_id' => $this->XmlType_id,
			'XmlTypeKind_id' => $this->XmlTypeKind_id,
			'XmlTemplateType_id' => $this->XmlTemplateType_id,
			'XmlTemplate_Settings' => $this->printSettings,
			'XmlTemplateScope_id' => $this->XmlTemplateScope_id,
			'XmlTemplateScope_eid' => $this->XmlTemplateScope_eid,
			'Lpu_id' => $this->Lpu_id,
			'LpuSection_id' => $this->LpuSection_id,
			'LpuSection_Name' => $this->LpuSection_Name,
			'Lpu_Name' => $this->Lpu_Name,
			'PMUser_Name' => $this->PMUser_Name,
			'Diag_id' => $this->Diag_id,
			'PersonAgeGroup_id' => $this->PersonAgeGroup_id,
			'UslugaComplex_id_list' => $this->uslugaIdList,
		));
	}

	/**
	 * Логика перед сохранением, включающая в себя проверку данных
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);

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

		if (swXmlTemplate::OLD_TYPE_ID != $this->XmlTemplateType_id) {
			// XML-схема не требуется, т.к. её можно генерировать при необходимости
			$this->setAttribute('XmlSchema_id', null);
		}

		if (swXmlTemplate::EVN_PRESCR_PLAN_TYPE_ID == $this->XmlTemplateType_id) {
			// Все принадлежат к категории шаблонов "Назначение"
			$this->setAttribute('EvnClass_id', 63);
			// XML-схема не может описать структуру данных и потому не требуется
			$this->setAttribute('XmlSchema_id', null);
			// Наименование шаблона не требуется
			// Но в таблице XmlTemplate это поле не нулл,
			// поэтому для всех используется липовое значение
			$this->setAttribute('caption', 'Назначение');
		} else {
			$this->setAttribute('diag_id', null);
			$this->setAttribute('personagegroup_id', null);
		}

		if (empty($this->XmlTemplateHtml_id) || empty($this->htmlTemplate) || $this->_isAttributeChanged('htmltemplate')) {
			if (false == empty($this->XmlTemplateHtml_id)) {
				$this->_deleteFromHashTable('XmlTemplateHtml', $this->XmlTemplateHtml_id);
			}
			// найти запись с таким же хэшем, если её нет, то создать
			$type = 'nvarchar(max)';//XmlTemplateHtml_HtmlTemplate
			$tmp = $this->_searchInHashTable('XmlTemplateHtml', $this->htmlTemplate, $type);
			if (empty($tmp)) {
				$tmp = $this->_insertToHashTable('XmlTemplateHtml', 'HtmlTemplate', $this->htmlTemplate, $type);
			}
			$this->setAttribute('xmltemplatehtml_id', $tmp);
		}
		if (empty($this->XmlTemplateData_id) || empty($this->xmlData) || $this->_isAttributeChanged('xmldata')) {
			if (false == empty($this->XmlTemplateData_id)) {
				$this->_deleteFromHashTable('XmlTemplateData', $this->XmlTemplateData_id);
			}
			// найти запись с таким же хэшем, если её нет, то создать
			$type = 'xml';//XmlTemplateData_Data
			$tmp = $this->_searchInHashTable('XmlTemplateData', $this->xmlData, $type);
			if (empty($tmp)) {
				$tmp = $this->_insertToHashTable('XmlTemplateData', 'Data', $this->xmlData, $type);
			}
			$this->setAttribute('xmltemplatedata_id', $tmp);
		}
		if (empty($this->XmlTemplateSettings_id) || empty($this->printSettings) || $this->_isAttributeChanged('printsettings')) {
			if (false == empty($this->XmlTemplateSettings_id)) {
				$this->_deleteFromHashTable('XmlTemplateSettings', $this->XmlTemplateSettings_id);
			}
			// найти запись с таким же хэшем, если её нет, то создать
			$type = 'varchar(100)';//XmlTemplateSettings_Settings
			$tmp = $this->_searchInHashTable('XmlTemplateSettings', $this->printSettings, $type);
			if (empty($tmp)) {
				$tmp = $this->_insertToHashTable('XmlTemplateSettings', 'Settings', $this->printSettings, $type);
			}
			$this->setAttribute('xmltemplatesettings_id', $tmp);
		}
		/*if (false == $this->_isAllowNewTables) {
			$this->setAttribute('xmltemplate_htmltemplate', $this->htmlTemplate);
			$this->setAttribute('xmltemplate_data', $this->xmlData);
			$this->setAttribute('xmltemplate_settings', $this->printSettings);
		}*/

		switch ($this->XmlType_id ) {
			case swEvnXml::MULTIPLE_DOCUMENT_TYPE_ID:
			case swEvnXml::STAC_EPIKRIZ_TYPE_ID:
			case swEvnXml::STAC_PROTOCOL_TYPE_ID:
			case swEvnXml::STAC_RECORD_TYPE_ID:
			case swEvnXml::EVN_VIZIT_PROTOCOL_TYPE_ID:
				$this->setAttribute('xmltemplatetype_id', swXmlTemplate::MULTIPLE_PART_TYPE_ID);
				break;
			case swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID:
				$this->setAttribute('xmltemplatetype_id', swXmlTemplate::EVN_USLUGA_TYPE_ID);
				break;
			case swEvnXml::LAB_USLUGA_PROTOCOL_TYPE_ID:
				$this->setAttribute('xmltemplatetype_id', swXmlTemplate::LAB_USLUGA_PROTOCOL_TYPE_ID);
				break;
		}

		if ( false == in_array($this->XmlType_id, array(
				swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID,
				swEvnXml::LAB_USLUGA_PROTOCOL_TYPE_ID,
			))
		) {
			$this->setAttribute('uslugaidlist', '');
		}

		if (('saveSettings' == $this->scenario && $this->_isAttributeChanged('uslugaidlist'))
			|| $this->isNewRecord
		) {
			if (false === $this->isNewRecord) {
				$this->_clearXmlTemplateLink();
			}
			if (false == empty($this->uslugaIdList)) {
				if (!is_string($this->uslugaIdList)) {
					throw new Exception('Список услуг, с которыми ассоциирован шаблон, должен быть в формате перечня идентификаторов, разделенных запятой', 400);
				}
				$UslugaComplex_id_list = explode(',', $this->uslugaIdList);

				$isListValid = true;
				foreach ($UslugaComplex_id_list as $UslugaComplex_id) {
					if (!is_numeric($UslugaComplex_id) || $UslugaComplex_id < 0) {
						$isListValid = false;
					}
				}
				if (!$isListValid) {
					throw new Exception('Неправильный тип данных в списке услуг', 400);
				}

				if ($this->isNewRecord) {
					$this->_params['validated_uslugacomplex_id_list'] = $UslugaComplex_id_list;
				} else {
					foreach ($UslugaComplex_id_list as $UslugaComplex_id) {
						$this->_addXmlTemplateLink($UslugaComplex_id);
					}
				}
			}
		}
	}

	/**
	 * @param string $tableName
	 * @param string $value
	 * @param string $type
	 * @return int
	 */
	protected function _searchInHashTable($tableName, $value, $type = null)
	{
		if (empty($value)) {
			$value = '';
		}
		$hash = md5($value);
		return $this->getFirstResultFromQuery("
			select top 1 {$tableName}_id
			from {$tableName} (nolock)
			where {$tableName}_HashData = :hash
		", array(
			'hash' => $hash,
		));
	}

	/**
	 * @param string $tableName
	 * @param string $id
	 */
	protected function _deleteFromHashTable($tableName, $id)
	{
		/* такая реализацию откатывает транзакцию в БД
		надо делать проверку используется ли перед вызовом хранимки для удаления
		или периодически скриптом чистить эти таблицы
		$query = "
			declare
				@{$tableName}_id bigint = :id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_{$tableName}_del
				@{$tableName}_id = @{$tableName}_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$params = array(
			'id' => $id,
		);
		try {
			// удалится, если нигде не используется
			$this->db->query($query, $params);
		} catch (Exception $e) {
			// не удалилось - значит используется
		}
		*/
	}

	/**
	 * @param string $tableName
	 * @param string $valueField
	 * @param string $value
	 * @param string $type
	 * @return int
	 * @throws Exception
	 */
	protected function _insertToHashTable($tableName, $valueField, $value, $type = null)
	{
		if (empty($value)) {
			$value = '';
		}
		$hash = md5($value);
		$query = "
			declare
				@{$tableName}_id bigint = NULL,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_{$tableName}_ins
				@{$tableName}_id = @{$tableName}_id output,
				@{$tableName}_{$valueField} = :value,
				@{$tableName}_HashData = :hash,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @{$tableName}_id as {$tableName}_id,  @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$params = array(
			'value' => $value,
			'hash' => $hash,
			'pmUser_id' => $this->promedUserId,
		);
		$result = $this->db->query($query, $params);
		if ( ! is_object($result) ) {
			throw new Exception("Ошибка запроса записи данных объекта {$tableName} в БД", 500);
		}
		$tmp = $result->result('array');
		if (is_array($tmp) && count($tmp) > 0) {
			$tmp = $tmp[0];
		} else {
			$tmp = array();
			$tmp['Error_Code'] = 500;
			$tmp['Error_Msg'] = "Неправильный формат ответа при выполнении хранимой процедуры  p_{$tableName}_ins";
		}
		if (!empty($tmp['Error_Msg'])) {
			throw new Exception($tmp['Error_Msg'], $tmp['Error_Code']);
		}
		if (empty($tmp[$tableName . '_id'])) {
			throw new Exception("Хранимая процедура p_{$tableName}_ins не вернула {$tableName}_id", 500);
		}
		return $tmp[$tableName . '_id'];
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
		if ($this->isNewRecord && isset($this->_params['validated_uslugacomplex_id_list'])) {
			foreach ($this->_params['validated_uslugacomplex_id_list'] as $UslugaComplex_id) {
				$this->_addXmlTemplateLink($UslugaComplex_id);
			}
		} 
		$this->_saveResponse['XmlTemplateCat_id'] = $this->XmlTemplateCat_id;
	}

	/**
	 *  Добавление услуги в список услуг шаблона
	 */
	private function _addXmlTemplateLink($UslugaComplex_id)
	{
		return $this->db->query("
			INSERT INTO dbo.XmlTemplateLink with (rowlock)
			(UslugaComplex_id,XmlTemplate_id,pmUser_insID,pmUser_updID,XmlTemplateLink_insDT,XmlTemplateLink_updDT)
			VALUES (:UslugaComplex_id, :XmlTemplate_id, :pmUser_id, :pmUser_id, GETDATE(), GETDATE())
		", array(
			'UslugaComplex_id' => $UslugaComplex_id,
			'XmlTemplate_id' => $this->id,
			'pmUser_id' => $this->promedUserId,
		));
	}

	/**
	 * Очистка списка услуг шаблона
	 */
	private function _clearXmlTemplateLink()
	{
		if (empty($this->id)) {
			//очищать нечего
			return true;
		}
		return $this->db->query("
			DELETE FROM dbo.XmlTemplateLink with (rowlock)
			WHERE XmlTemplate_id = :XmlTemplate_id
		", array(
			'XmlTemplate_id' => $this->id,
		));
	}

	/**
	 *  Проверка доступа на добавление, редактирование или удаление
	 */
	protected function _hasAccessWrite()
	{
		if (isSuperadmin()) {
			return true;
		}
		return swXmlTemplate::hasAccessWrite($this->promedUserId, $this->_params['Lpu_id'], $this->_params['LpuSection_id'], $this->_savedData, $this->id);
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = array())
	{
		parent::_beforeDelete($data);
		$result = $this->db->query("
			select top 1 XmlTemplate_id from v_XmlTemplateBase (nolock)
			where XmlTemplate_id = :id
		", array('id' => $this->id));
		if ( !is_object($result) ) {
			throw new Exception('Не удалось выполнить проверку, является ли шаблон базовым', 500);
		}
		$res = $result->result('array');
		if (count($res) > 0) {
			throw new Exception('Нельзя удалить шаблон, который является базовым', 400);
		}
	}

	/**
	 * Удаление шаблона
	 *
	 * Изменяется поле XmlTemplate_IsDeleted (внешний ключ на YesNo).
	 * Устанавливается значение 2 - удалено. 1 или null - не удалено.
	 *
	 * @param array $queryParams Параметры запроса
	 * @return array Результат выполнения запроса
	 * @throws Exception В случае ошибки запроса или ошибки возвращенной хранимкой
	 */
	protected function _delete($queryParams = array())
	{
		$result = $this->db->query("
			UPDATE
				XmlTemplate
			SET
				pmUser_updID = :pmUser_id
				,XmlTemplate_updDT = dbo.tzGetDate()
				,XmlTemplateCat_id = null
				,XmlTemplate_IsDeleted = 2
			WHERE
				XmlTemplate_id = :XmlTemplate_id
		", array(
			'XmlTemplate_id' => $this->id,
			'pmUser_id' => $this->promedUserId,
		));
		if (empty($result)) {
			throw new Exception('Ошибка запроса удаления записи из БД', 500);
		}
		return array($this->_saveResponse);
	}


	/**
	 * Получение данных шаблона для создания документа
	 * с подсчетом использования шаблона
	 *
	 * @param array $data
	 * @return array Стандартный ответ модели
	 * @throws Exception
	 */
	public function select($data)
	{
		$data['scenario'] = 'select';
		$this->applyData($data);
		$this->_validate();
		$sql = "
 			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = (SELECT XmlTemplateFavorites_id FROM dbo.v_XmlTemplateFavorites with (nolock) WHERE XmlTemplate_id = :XmlTemplate_id AND pmUser_insID = :pmUser_id);

			if isnull(@Res, 0) = 0
			begin

				exec dbo.p_XmlTemplateFavorites_ins
					@Server_id = :Server_id,
					@XmlTemplateFavorites_id = @Res output,
					@XmlTemplate_id = :XmlTemplate_id,
					@XmlTemplateFavorites_CountLoad = 1,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;

				select @Res as XmlTemplateFavorites_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			end
			else
			begin
				declare
					@CountLoad bigint;
				set @CountLoad = 1 + (SELECT XmlTemplateFavorites_CountLoad FROM dbo.v_XmlTemplateFavorites with (nolock) WHERE XmlTemplate_id = :XmlTemplate_id AND pmUser_insID = :pmUser_id);

				exec dbo.p_XmlTemplateFavorites_upd
					@Server_id = :Server_id,
					@XmlTemplateFavorites_id = @Res output,
					@XmlTemplate_id = :XmlTemplate_id,
					@XmlTemplateFavorites_CountLoad = @CountLoad,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;

				select @Res as XmlTemplateFavorites_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			end;
		";
		$result = $this->db->query($sql, array(
			'Server_id' => $this->_params['Server_id'],
			'XmlTemplate_id' => $this->id,
			'pmUser_id' => $this->promedUserId,
		));
		if ( false == is_object($result) ) {
			throw new Exception('Ошибка БД, не удалось подсчитать шаблон.');
		}
		$response = $result->result('array');
		if ( !empty($response[0]['Error_Msg']) ) {
			throw new Exception($response[0]['Error_Msg']);
		}
		return $this->loadData();
	}

	/**
	 * Получение данных шаблона со схемой проверки
	 *
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи applyData
	 * @return array Стандартный ответ модели
	 * @throws Exception
	 */
	public function loadData($data = array())
	{
		if (false == empty($data)) {
			$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
			$this->applyData($data);
			$this->_validate();
		}
		if (empty($this->xmlData)) {
			throw new Exception('Шаблон не загружен.');
		}
		return array(array(
			'XmlTemplate_id' => $this->id,
			'XmlTemplate_Caption' => $this->caption,
			'XmlTemplate_HtmlTemplate' => $this->htmlTemplate,
			'XmlTemplate_Data' => $this->xmlData,
			'XmlTemplateType_id' => $this->XmlTemplateType_id,
			'XmlTemplate_Settings' => $this->printSettings,
			'XmlSchema_Data' => $this->XmlSchema_Data,
		));
	}

	/**
	 * Возвращает для комбобокса список категорий, которые могут быть выбраны для папок и шаблонов документов
	 * @param array $data
	 * @return array
	 */
	function loadEvnClassList($data)
	{
		if (empty($data['withBase'])) {
			$supportEvnClassList = implode(',', $this->_supportEvnClassList);
		} else {
			$supportEvnClassList = implode(',', $this->_supportEvnClassListWithBase);
		}
		$query = "
			select
				EvnClass_id,
				EvnClass_Name,
				EvnClass_SysNick
			from
				EvnClass with(nolock)
			where
				EvnClass_id in ({$supportEvnClassList})
			order by
				EvnClass_id
		";
		$result = $this->db->query($query, array());
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array();
		}
	}

	/**
	 * Получение списка шаблонов для комбобокса
	 */
	public function loadCombo($data)
	{
		$params = array();
		$filter = "(1=1)";
		if ($data['XmlTemplate_id']>0) {
			$params['XmlTemplate_id'] = $data['XmlTemplate_id'];
			$filter .= "and XmlTemplate_id = :XmlTemplate_id";
		} else {
			return false;
		}
		$sql = "
			SELECT
				XmlTemplate_Caption,
				XmlTemplate_id
			FROM
				v_XmlTemplate with (nolock)
			WHERE
				{$filter} and ISNULL(XmlTemplate_IsDeleted, 1) = 1
			ORDER BY
				XmlTemplate_Caption ASC
		";
		$result = $this->db->query($sql, $params);
		if ( is_object($result) )
			return $result->result('array');
		else
			return false;
	}

	/**
	 * Получение списка для грида услуги шаблона
	 */
	public function loadXmlTemplateLinkList($data)
	{
		$params = array('XmlTemplate_id' => $data['XmlTemplate_id']);
		$sql = "
			SELECT
				uc.UslugaComplex_id,
				ucat.UslugaCategory_Name,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name
			FROM
				XmlTemplateLink xtl with (NOLOCK)
				inner join v_UslugaComplex uc with (NOLOCK) on uc.UslugaComplex_id = xtl.UslugaComplex_id
				left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
			WHERE
				xtl.XmlTemplate_id = :XmlTemplate_id
			ORDER BY
				uc.UslugaCategory_id, uc.UslugaComplex_Code ASC
		";
		$result = $this->db->query($sql, $params);
		if ( is_object($result) )
			return $result->result('array');
		else
			return false;
	}

	/**
	 * Получаем список разделов и формируем конфигурация этих разделов
	 * @return array
	 */
	private function _processingXmlDataSectionList()
	{
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		//Получаем список именованных разделов
		$XmlDataSectionList = $instance->loadXmlDataSectionList(array('onlyNamedSection'=>1));
		$response = array();
		if (!is_array($XmlDataSectionList)) {
			return $response;
		}
		//формируем конфигурация этих разделов
		foreach ($XmlDataSectionList as $row) {
			switch ($row['XmlDataSection_SysNick']) {
				case 'anamnesvitae':
				case 'anamnesmorbi':
				case 'objectivestatus':
					$response[] = array(
						'id'=>$row['XmlDataSection_SysNick'],
						'fieldLabel'=>$row['XmlDataSection_Name'],
						'xtype'=>'ckeditor',
						'hideLabel'=>'false',
						'defaultValue'=>null,
						'width'=>null,
						'height'=>'300'
					);
					break;
				case 'complaint':
				case 'diagnos':
				case 'resolution':
					$response[] = array(
						'id'=>$row['XmlDataSection_SysNick'],
						'fieldLabel'=>$row['XmlDataSection_Name'],
						'xtype'=>'ckeditor',
						'hideLabel'=>'false',
						'defaultValue'=>null,
						'width'=>null,
						'height'=>'70'
					);
					break;
				default: // recommendations, localstatus and other
					$response[] = array(
						'id'=>$row['XmlDataSection_SysNick'],
						'fieldLabel'=>$row['XmlDataSection_Name'],
						'xtype'=>'ckeditor',
						'hideLabel'=>'false',
						'defaultValue'=>null,
						'width'=>null,
						'height'=>'200'
					);
					break;
			}
		}
		return $response;
	}

	/**
	 * Возвращает массив с конфигурацией полей для шаблона
	 *
	 * @access	public
	 * @param	$datatag_list array	Массив созданный функцией foundDataTag
	 * @return	array
	 */
	public function getXmlTemplateFieldData($datatag_list)
	{
		$datatag_content_arr = array();
		$is_assoc = !empty($datatag_list) && !isset($datatag_list[0]);

		if ($is_assoc) {
			foreach ($datatag_list as $data_tag_id => $content) {
				$datatag_content_arr[$data_tag_id] = (empty($content) || !is_string($content)) ? '' : $content;
			}
		} else {
			foreach ($datatag_list as $row) {
				$data_tag_id = $row['id'];
				$datatag_content_arr[$data_tag_id] = (empty($row['content']) || !is_string($row['content'])) ? '' : $row['content'];
			}
		}

		// преобразуем данные, как бы полученные из БД для именованных полей
		$result = array();
		$field_data = $this->_processingXmlDataSectionList();
		foreach($field_data as $row) {
			$data_tag_id = $row['id'];
			if (array_key_exists($data_tag_id, $datatag_content_arr)) {
				$row['defaultValue'] = $datatag_content_arr[$data_tag_id];
				$result[] = $row;
				unset($datatag_content_arr[$data_tag_id]);
			}
		}
		// определяем конфигурацию для неименованных полей
		$autonamefld = array(
			'id'=>null,
			'xtype'=>'ckeditor',
			'fieldLabel'=>'Автоматически именованное поле',
			'hideLabel'=>'true',
			'defaultValue'=>null,
			'width'=>null,
			'height'=>'200'
		);
		foreach($datatag_content_arr as $data_tag_id => $content) {
			if (strpos($data_tag_id, 'autoname') !== false) {
				$autonamefld['id'] = $data_tag_id;
				$autonamefld['defaultValue'] = $datatag_content_arr[$data_tag_id];
				$result[] = $autonamefld;
			}
		}
		return $result;
	}

	/**
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function loadEvnPrescrPlan($data)
	{
		if (empty($data['XmlTemplate_id'])) {
			$data['XmlTemplate_id'] = $this->checkExistsEvnPrescrPlan($data, false);
			if ( empty($data['XmlTemplate_id']) ) {
				throw new Exception('Нет сохраненных шаблонов для данной нозологии', 400);
			}
		}
		$this->setAttributes(array(
			'XmlTemplate_id' =>$data['XmlTemplate_id'],
		));
		return json_decode($this->xmlData, true);
	}

	/**
	 * @param array $data
	 * @param bool $onlyWithPersonAgeGroup
	 * @return int
	 * @throws Exception
	 */
	function checkExistsEvnPrescrPlan($data, $onlyWithPersonAgeGroup = true)
	{
		if (isset($data['XmlTemplate_id'])) {
			unset($data['XmlTemplate_id']);
		}
		$data['scenario'] = 'checkExistsEvnPrescrPlan';
		$data['XmlTemplateType_id'] = swXmlTemplate::EVN_PRESCR_PLAN_TYPE_ID;
		$this->applyData($data);
		$this->_validate();
		$withoutPersonAgeGroup = '';
		if (false == $onlyWithPersonAgeGroup) {
			$withoutPersonAgeGroup = 'union
			select top 1 tpl.XmlTemplate_id from XmlTemplate tpl with (nolock)
			where tpl.LpuSection_id = @LpuSection_id and exists (
				select top 1 DD.Diag_id from v_Diag DD with(nolock) where DD.Diag_id = @Diag_id and DD.Diag_pid = tpl.Diag_id
			)';
		}
		return $this->getFirstResultFromQuery( "
			declare
				@PersonAgeGroup_id int = :PersonAgeGroup_id,
				@LpuSection_id bigint  = :LpuSection_id,
				@Diag_id int = :Diag_id;

			select top 1 tpl.XmlTemplate_id from XmlTemplate tpl with (nolock)
			where tpl.LpuSection_id = @LpuSection_id and tpl.Diag_id = @Diag_id and tpl.PersonAgeGroup_id = @PersonAgeGroup_id
			union
			select top 1 tpl.XmlTemplate_id from XmlTemplate tpl with (nolock)
			where tpl.LpuSection_id = @LpuSection_id and exists (
				select top 1 DD.Diag_id from v_Diag DD with(nolock) where DD.Diag_id = @Diag_id and DD.Diag_pid = tpl.Diag_id
			) and tpl.PersonAgeGroup_id = @PersonAgeGroup_id
			{$withoutPersonAgeGroup}
		", array(
			'LpuSection_id' => $this->_params['LpuSection_id'],
			'Diag_id' => $this->Diag_id,
			'PersonAgeGroup_id' => $this->PersonAgeGroup_id,
		));
	}

	/**
	 * @param int $EvnClass_id
	 * @return bool
	 */
	function isEvnClassSupport($EvnClass_id) {
		return in_array($EvnClass_id, $this->_supportEvnClassList);
	}

	/**
	 * @param int $XmlType_id
	 * @param int $EvnClass_id
	 * @return array|null
	 */
	function getEvnClassListForXmlType($XmlType_id) {
		$XmlType_Code = $this->getFirstResultFromQuery("
			select top 1 XmlType_Code from v_XmlType with(nolock) where XmlType_id = :XmlType_id 
		", array('XmlType_id' => $XmlType_id));
		if (!$XmlType_Code) return false;
		return isset($this->_supportXmlTypeEvnClassLink[$XmlType_Code])?$this->_supportXmlTypeEvnClassLink[$XmlType_Code]:null;
	}

	/**
	 * Получение списка шаблонов. Метод для API
	 * @param array $data
	 * @return array|false
	 */
	function loadXmlTamplateListForAPI($data) {
		$params = array();
		$filters = array('1=1');

		$filters[] = "isnull(XT.Region_id, :Region_id) = :Region_id";
		$params['Region_id'] = $this->getRegionNumber();

		if (!empty($data['XmlTemplate_id'])) {
			$filters[] = "XT.XmlTemplate_id = :XmlTemplate_id";
			$params['XmlTemplate_id'] = $data['XmlTemplate_id'];
		} else {
			if (!empty($data['Lpu_id'])) {
				$filters[] = "XT.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			} else if (!empty($data['Lpu_oid'])) {
				$filters[] = "(XTB.XmlTemplateBase_id is not null or XT.Lpu_id = :Lpu_oid)";
				$params['Lpu_oid'] = $data['Lpu_oid'];
			}
			if (!empty($data['XmlTemplateBaseFlag']) && $data['XmlTemplateBaseFlag']) {
				$filters[] = "XTB.XmlTemplateBase_id is not null";
			}
			if (!empty($data['EvnClass_id']) && $this->isEvnClassSupport($data['EvnClass_id'])) {
				$filters[] = "XT.EvnClass_id = :EvnClass_id";
				$params['EvnClass_id'] = $data['EvnClass_id'];
			}
			if (!empty($data['XmlType_id'])) {

				$evnClassList = $this->getEvnClassListForXmlType($data['XmlType_id']);
				if ($evnClassList && (empty($data['EvnClass_id']) || in_array($data['EvnClass_id'], $evnClassList))) {
					$filters[] = "XT.XmlType_id = :XmlType_id";
					$params['XmlType_id'] = $data['XmlType_id'];
				}
			}
			if (!empty($data['MedPersonal_id'])) {
				$filters[] = "exists(
					select * from v_XmlTemplateDefault XTD with(nolock)
					where XTD.XmlTemplate_id = XT.XmlTemplate_id 
					and XTD.MedPersonal_id = :MedPersonal_id
				)";
				$params['MedPersonal_id'] = $data['MedPersonal_id'];
			}
			if (!empty($data['MedStaffFact_id'])) {
				$filters[] = "exists(
					select * from v_XmlTemplateDefault XTD with(nolock)
					where XTD.XmlTemplate_id = XT.XmlTemplate_id 
					and XTD.MedStaffFact_id = :MedStaffFact_id
				)";
				$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			}
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select
				XT.XmlTemplate_id,
				XT.XmlTemplate_Caption,
				XT.EvnClass_id,
				XT.XmlType_id,
				XTD.XmlTemplateData_Data
			from
				v_XmlTemplate XT with(nolock)
				left join v_XmlTemplateData XTD with(nolock) on XTD.XmlTemplateData_id = XT.XmlTemplateData_id
				left join v_XmlTemplateBase XTB with(nolock) on XTB.XmlTemplate_id = XT.XmlTemplate_id
			where
				{$filters_str}
		";

		return $this->queryResult($query, $params);
	}
}
