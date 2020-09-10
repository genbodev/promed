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
 * Базовая модель документов с поддержкой новой структуры хранения.
 *
 * @package		XmlTemplate
 * @author		Александр Пермяков
 *
 * @property string $name Наименование документа
 * @property string $data XML-документ
 * @property integer $Evn_id Принадлежность учетному документу
 * @property integer $XmlType_id Тип документа
 * @property integer $XmlTemplate_id Шаблон, на основе которого создан документ, для возможности восстановить документ в исходном виде, если были удалены разделы.
 * @property integer $XmlTemplateType_id Тип шаблона, чтобы знать по какому алгоритму следует обработать шаблон документа для печати или отображения
 * @property-read integer $XmlTemplateData_id
 * @property string $xmlData XML-шаблон группирования элементов формы для ввода данных документа
 * @property-read integer $XmlTemplateHtml_id
 * @property string $htmlTemplate HTML-шаблон отображения документа
 * @property-read integer $XmlTemplateSettings_id
 * @property string $printSettings Настройки для печати документа (Ассоциативный массив настроек в json-формате)
 * @property string $XmlSchema_Data XML-схема данных документа
 *
 * Поля, устареющие после переноса данных из них в соответств. таблицы (true == _isAllowNewTables):
 * @ property string $XmlTemplate_Data XML-шаблон группирования элементов формы для ввода данных документа
 * @ property string $XmlTemplate_HtmlTemplate HTML-шаблон отображения документа
 * @ property string $XmlTemplate_Settings Настройки для печати документа (Ассоциативный массив настроек в json-формате)
 * XmlTemplateData_Hash,
 * XmlTemplateHtml_Hash,
 * XmlTemplateSettings_Hash,
 *
 * Возможно устаревшие поля
 * @property integer $Evn_sid Принадлежность телемед. услуге. Сейчас хранится в Evn_id
 *
 * @property-read int $fromEvnId
 * @property-read int $evnClassId
 * @property-read array $evnHasOneDocXmlTypeList
 *
 * @property XmlTemplateBase_model $XmlTemplateBase_model
 * @property XmlTemplateDefault_model $XmlTemplateDefault_model
 * @property ParameterValue_model $ParameterValue_model
 */
class EvnXmlBase_model extends swModel
{
	/**
	 * @var bool
	 */
	//protected $_isAllowNewTables = false;

	/**
	 * Класс учетного документа, к которому прикреплен документ
	 * @var integer
	 */
	private $_EvnClass_id;
	/**
	 * идентификатор учетного документа,
	 * из которого будут браться данные при обработке маркеров документов
	 * @var integer
	 */
	private $_From_Evn_id;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		// Список имен сценариев, которые реализует модель
		$this->_setScenarioList(array(
			'createEmpty', // Создание нового документа из шаблона
			'resetSection', // Восстановить раздел документа
			'destroySection', // Удаление раздела документа
			'updateSectionContent', // Обновление содержания одного или нескольких разделов документа
			'saveAsTemplate', // Сохранение документа как шаблона
			'doCopy', // Копирование документа
			'restore', // Восстановление исходного состояния документа
			self::SCENARIO_DELETE, // Удаление документа из БД

			'create', // Создание документа
			'update', // Редактирование документа
			self::SCENARIO_SET_ATTRIBUTE, // Редактирование документа

			self::SCENARIO_LOAD_EDIT_FORM, // Получение шаблона документа и данных для его заполнения
			'doLoadPrintData', // Получение данных документа для печати
			'doLoadEvnXmlPanel', // Запрос данных документа для просмотра или редактирования в панели sw.Promed.EvnXmlPanel
			self::SCENARIO_VIEW_DATA, // Получение списка документов для отображения в панели просмотра
			'getXmlTemplateInfo',
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
		return 'EvnXml';
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnXml_id';
		$arr[self::ID_KEY]['label'] = 'XML-документ';
		unset($arr['code']);
		$arr['name']['alias'] = 'EvnXml_Name';
		$arr['name']['save'] = 'trim';
		//'save' => 'ban_percent|trim|required|max_length[200]',
		$arr['insdt']['alias'] = 'EvnXml_insDT';
		$arr['upddt']['alias'] = 'EvnXml_updDT';
		$arr['data'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnXml_Data',
		);
		$arr['evn_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Evn_id',
		);
		$arr['evn_sid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Evn_sid',
		);
		$arr['xmltype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'XmlType_id',
			'label' => 'Тип документа',
			'save' => 'trim',
			'type' => 'id',
		);
		$arr['xmltemplate_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'XmlTemplate_id',
		);
		$arr['xmltemplatetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
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
				self::PROPERTY_NOT_SAFE,
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
				self::PROPERTY_NOT_SAFE,
			),
			'select' => 'xth.XmlTemplateHtml_HtmlTemplate as htmltemplate',
			'join' => 'left join XmlTemplateHtml xth (nolock) on xth.XmlTemplateHtml_id = {ViewName}.XmlTemplateHtml_id',
		);
		$arr['xmltemplatesettings_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['printsettings'] = array(
			'properties' => array(
				self::PROPERTY_NOT_SAFE,
			),
			'select' => 'xts.XmlTemplateSettings_Settings as printsettings',
			'join' => 'left join XmlTemplateSettings xts (nolock) on xts.XmlTemplateSettings_id = {ViewName}.XmlTemplateSettings_id',
		);
		// Поля, устареющие после внедрения новой структуры хранения
		/*$arr['xmltemplate_data'] = array(
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
		);*/
		$arr['xmlschema_data'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'XmlSchema_Data',
		);
		$arr['issigned'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnXml_IsSigned',
		);
		$arr['issigned'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnXml_IsSigned',
		);
		$arr['signdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnXml_signDT',
		);
		$arr['pmuser_signid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'pmUser_signID',
		);
		return $arr;
	}

	/**
	 * @return array
	 */
	public function getEvnHasOneDocXmlTypeList()
	{
		return array(
			swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID,
			swEvnXml::EVN_VIZIT_PROTOCOL_TYPE_ID,
		);
	}

	/**
	 * Получение значения класса учетного документа, к которому прикреплен документ
	 * @return int
	 */
	public function getEvnClassId()
	{
		if (empty($this->Evn_id)) {
			$this->_EvnClass_id = null;
		} else if (!isset($this->_EvnClass_id)) {
			$this->_EvnClass_id = $this->getFirstResultFromQuery('
			select EvnClass_id from Evn with (nolock) where Evn_id = :id
			',array('id'=>$this->Evn_id));
		}
		return $this->_EvnClass_id;
	}

	/**
	 * Получение идентификатора учетного документа,
	 * из которого будут браться данные при обработке маркеров документов
	 * в swMarker::processingTextWithXmlMarkers
	 * @return int
	 */
	public function getFromEvnId()
	{
		if (empty($this->Evn_id)) {
			$this->_From_Evn_id = null;
			return $this->_From_Evn_id;
		} else if ($this->evnClassId == 160) {
			$this->_From_Evn_id = $this->getFirstResultFromQuery('
				select Evn.Evn_pid
				from EvnUsluga with (nolock)
				inner join Evn with (nolock) on Evn.Evn_id = EvnUsluga.EvnDirection_id
				where EvnUsluga_id = :id
			', array(
				'id' => $this->Evn_id
			), true);
			if ($this->_From_Evn_id === false) {
				throw new Exception('Ошибка при получении идентификатора документа');
			}
		} else {
			$this->_From_Evn_id = $this->Evn_id;
		}
		return $this->_From_Evn_id;
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
			// данные перенесены в XmlTemplateHtml, всегда возвращаем htmltemplate
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
		if ('createEmpty' == $this->scenario) {
			$this->_params['EvnClass_id'] = empty($data['EvnClass_id']) ? null : $data['EvnClass_id'];
			$this->_params['MedStaffFact_id'] = empty($data['MedStaffFact_id']) ? null : $data['MedStaffFact_id'];
			$this->_params['EvnXml_id'] = empty($data['EvnXml_id']) ? null : $data['EvnXml_id'];
			if ( empty($this->_params['MedStaffFact_id']) && isset($data['session']['CurMedStaffFact_id']) ) {
				$this->_params['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
			}
		}
		if (in_array($this->scenario, array('resetSection','destroySection','updateSectionContent'))) {
			$this->_params['name'] = empty($data['name']) ? null : $data['name'];
		}
		if ('updateSectionContent' == $this->scenario) {
			$this->_params['value'] = empty($data['value']) ? null : $data['value'];
			$this->_params['XmlData'] = empty($data['XmlData']) ? null : $data['XmlData'];
			$this->_params['isHTML'] = empty($data['isHTML']) ? 0 : $data['isHTML'];
		}
		if ('getXmlTemplateInfo' == $this->scenario) {
			$this->_params['EvnXml_id'] = empty($data['EvnXml_id']) ? null : $data['EvnXml_id'];
			$this->_params['LpuSection_id'] = empty($data['LpuSection_id']) ? null : $data['LpuSection_id'];
			if ( empty($this->_params['LpuSection_id']) && isset($data['session']['CurLpuSection_id']) ) {
				$this->_params['LpuSection_id'] = $data['session']['CurLpuSection_id'];
			}
		}
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
			case 'loadEvnXmlCombo':
				$rules =  array(
					array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
					array('field' => 'XmlType_id', 'label' => 'Тип документа', 'rules' => '', 'type' => 'id'),
					array('field' => 'XmlType_ids', 'label' => 'Типы докуменов', 'rules' => '', 'type' => 'json_array')
				);
				break;
			case 'loadEvnXmlPanel':
				$rules =  array(
					array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
					array('field' => 'XmlType_id', 'label' => 'Тип документа', 'rules' => '', 'type' => 'id'),
					array('field' => 'XmlType_ids', 'label' => 'Типы докуменов', 'rules' => '', 'type' => 'json_array')
				);
				break;
			case 'createEmpty':
				$rules =  array(
					array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'XmlType_id', 'label' => 'Тип документа', 'rules' => 'trim|required', 'type' => 'id'),
					array('field' => 'EvnClass_id', 'label' => 'Категория документа', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'MedStaffFact_id', 'label' => 'Рабочее место врача', 'rules' => 'trim', 'type' => 'id'),
				);
				break;
			case 'destroySection':
			case 'resetSection':
				$rules =  array(
					array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => 'trim|required', 'type' => 'id'),
					array('field' => 'name', 'label' => 'Имя раздела', 'rules' => 'trim|required', 'type' => 'string'),
				);
				break;
			case 'updateSectionContent':
				$rules =  array(
					array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => 'trim|required', 'type' => 'id'),
					array('field' => 'name', 'label' => 'Имя раздела', 'rules' => 'trim', 'type' => 'string'),
					array('field' => 'value', 'label' => 'Содержание раздела', 'rules' => 'trim', 'type' => 'string'),
					array('field' => 'XmlData', 'label' => 'Имена разделов со значениями', 'rules' => 'trim', 'type' => 'string'),
					array('field' => 'isHTML', 'label' => 'Флаг сохраняем с разметкой', 'rules' => 'trim', 'type' => 'int', 'default' => 0),
				);
				break;
			case 'restore':
			case 'saveAsTemplate':
				$rules =  array(
					array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => 'trim|required', 'type' => 'id'),
				);
				break;
			case 'doCopy':
				$rules =  array(
					array('field' => 'Evn_id', 'label' => 'Учетный документ, для которого создается копия', 'rules' => 'trim|required', 'type' => 'id'),
					array('field' => 'EvnXml_id', 'label' => 'Идентификатор исходного документа', 'rules' => 'trim|required', 'type' => 'id'),
				);
				break;
			case 'doLoadEvnXmlPanel':
				$rules =  array(
					array('field' => 'Evn_id', 'label' => 'Учетный документ, для которого создается копия', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'EvnXml_id', 'label' => 'Идентификатор исходного документа', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'XmlType_id', 'label' => 'Тип документа', 'rules' => 'trim', 'type' => 'id'),
				);
				break;
			case 'doLoadPrintData':
				$rules =  array(
					array('field' => 'Evn_id', 'label' => 'Учетный документ, для которого создается копия', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'EvnXml_id', 'label' => 'Идентификатор исходного документа', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'printHtml','label' => 'Печать HTML (да/нет)','rules' => 'trim','type' => 'id','default' => 1 /* нет */),
					array('field' => 'useWkhtmltopdf','label' => 'Вариант печати с помощью wkhtmltopdf','rules' => 'trim','type' => 'string'),
					array('field' => 'doHalf', 'label' => 'Смещение печати', 'rules' => 'trim', 'type' => 'string'),
				);
				break;
			case 'getLastEvnProtocolId':
				$rules =  array(
					array('field' => 'Evn_rid', 'label' => 'Идентификатор талона', 'rules' => 'trim|required', 'type' => 'id'),
					array('field' => 'EvnClass_id', 'label' => 'Идентификатор класса посещения', 'rules' => 'trim|required', 'type' => 'id'),
				);
				break;
			case 'getXmlTemplateInfo':
				$rules =  array(
					array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => 'trim|required', 'type' => 'id'),
					array('field' => 'LpuSection_id','label' => 'Идентификатор отделения пользователя','rules' => 'trim','type' => 'id'),
				);
				break;
			case 'loadXmlDataSectionList':
			case 'loadXmlMarkerTypeList':
				$rules =  array(
					array('field' => 'EvnClass_id', 'label' => 'Идентификатор класса события', 'rules' => 'trim', 'type' => 'id'),
				);
				break;
			case 'doDirectPrint':
				$rules =  array(
					array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'),
					array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
					array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id'),
					array('field' => 'printHtml','label' => 'Печать HTML (да/нет)','rules' => 'trim','type' => 'id','default' => 1 /* нет */),
					array('field' => 'useWkhtmltopdf','label' => 'Вариант печати с помощью wkhtmltopdf','rules' => 'trim','type' => 'string'),
				);
				break;

			case 'checkIsMarkeryBezDannix':
				$rules =  array(
					array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'),
					array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
					array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id')
				);
				break;
			case 'createEvnXmlDirectionLink':
				$rules = array(
					array('field' => 'EvnXml_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
					array('field' => 'EvnDirection_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id')
				);
				break;
			case 'deleteEvnXmlDirectionLink':
				$rules = array(
					array('field' => 'EvnXmlDirectionLink_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id')
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

		if (in_array($this->scenario,array('createEmpty')) && empty($this->XmlType_id)) {
			throw new Exception('Вы не указали тип документа', 400);
		}
		if (in_array($this->scenario,array('createEmpty')) && $this->isNewRecord && empty($this->Evn_id)) {
			throw new Exception('Вы не указали учетный документ!', 400);
		}
		if (in_array($this->scenario,array('createEmpty')) && false == $this->isNewRecord && $this->_isAttributeChanged('Evn_id')) {
			throw new Exception('Нельзя изменить учетный документ', 400);
		}
		if (in_array($this->scenario,array('createEmpty')) && false == $this->isNewRecord && empty($this->XmlTemplate_id)) {
			throw new Exception('Не выбран шаблон', 400);
		}
		if (in_array($this->scenario,array('createEmpty')) && empty($this->Evn_id) && empty($this->id)) {
			throw new Exception('Вы не указали учетный документ или идентификатор документа', 400);
		}
		if (in_array($this->scenario,array('createEmpty', 'create', 'update', self::SCENARIO_SET_ATTRIBUTE, self::SCENARIO_DO_SAVE, 'getXmlTemplateInfo')) && empty($this->promedUserId)) {
			throw new Exception('Не указан пользователь', 500);
		}
	}

	/**
	 * Логика перед удалением
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = array())
	{
		parent::_beforeDelete($data);

		if (!empty($this->id)) {
			$isEMDEnabled = $this->config->item('EMD_ENABLE');
			if (!empty($isEMDEnabled)) {
				$this->load->model('EMD_model');
				$checkResult = $this->EMD_model->getEMDDocumentListByEvn(
					array(
						'EvnXml_id' => $this->id,
						'EvnClass_SysNick' => 'EvnXml'
					)
				);
				if (!empty($checkResult)) {
					throw new Exception("Удаление документа невозможно, т.к. он зарегистрирован в РЭМД", 1);
				}
			}

			// надо очистить ссылку на шаблон в направлениях
			$query = "
				update EvnDirectionOper with (rowlock) set EvnXml_id = null where EvnXml_id = :EvnXml_id
			";
			$this->db->query($query, array(
				'EvnXml_id' => $this->id
			));

			// Удаляем связи с направлениями
			$query = "
				Select EvnXmlDirectionLink_id From v_EvnXmlDirectionLink (nolock) where EvnXml_id = :EvnXml_id
					
			";
			$result = $this->queryList($query, array(
				'EvnXml_id' => $this->id
			));

			if (!empty($result))
			{
				foreach ($result as $EvnXmlDirectionLink_id)
				{
					$this->db->query("
					
				exec p_EvnXmlDirectionLink_del
					@EvnXmlDirectionLink_id = :EvnXmlDirectionLink_id",

						array(
						'EvnXmlDirectionLink_id' => $EvnXmlDirectionLink_id
					));
		}
	}

		}
	}

	/**
	 * Логика перед сохранением, включающая в себя проверку данных
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);

		if (empty($this->XmlTemplateHtml_id) || empty($this->htmlTemplate) || $this->_isAttributeChanged('htmltemplate')) {
			if (false == empty($this->XmlTemplateHtml_id)) {
				$this->_deleteFromHashTable('XmlTemplateHtml', $this->XmlTemplateHtml_id);
			}
			// найти запись с таким же хэшем, если её нет, то создать
			$tmp = $this->_searchInHashTable('XmlTemplateHtml', $this->htmlTemplate);
			if (empty($tmp)) {
				$tmp = $this->_insertToHashTable('XmlTemplateHtml', 'HtmlTemplate', $this->htmlTemplate);
			}
			$this->setAttribute('xmltemplatehtml_id', $tmp);
		}
		if (empty($this->XmlTemplateData_id) || empty($this->xmlData) || $this->_isAttributeChanged('xmldata')) {
			if (false == empty($this->XmlTemplateData_id)) {
				$this->_deleteFromHashTable('XmlTemplateData', $this->XmlTemplateData_id);
			}
			// найти запись с таким же хэшем, если её нет, то создать
			$tmp = $this->_searchInHashTable('XmlTemplateData', $this->xmlData);
			if (empty($tmp)) {
				$tmp = $this->_insertToHashTable('XmlTemplateData', 'Data', $this->xmlData);
			}
			$this->setAttribute('xmltemplatedata_id', $tmp);
		}
		if (empty($this->XmlTemplateSettings_id) || empty($this->printSettings) || $this->_isAttributeChanged('printsettings')) {
			if (false == empty($this->XmlTemplateSettings_id)) {
				$this->_deleteFromHashTable('XmlTemplateSettings', $this->XmlTemplateSettings_id);
			}
			// найти запись с таким же хэшем, если её нет, то создать
			$tmp = $this->_searchInHashTable('XmlTemplateSettings', $this->printSettings);
			if (empty($tmp)) {
				$tmp = $this->_insertToHashTable('XmlTemplateSettings', 'Settings', $this->printSettings);
			}
			$this->setAttribute('xmltemplatesettings_id', $tmp);
		}
		/*if (false == $this->_isAllowNewTables) {
			$this->setAttribute('xmltemplate_htmltemplate', $this->htmlTemplate);
			$this->setAttribute('xmltemplate_data', $this->xmlData);
			$this->setAttribute('xmltemplate_settings', $this->printSettings);
		}*/
	}

	/**
	 * @param string $tableName
	 * @param string $value
	 * @return int
	 */
	protected function _searchInHashTable($tableName, $value)
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
	 * @return int
	 * @throws Exception
	 */
	protected function _insertToHashTable($tableName, $valueField, $value)
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
		$this->_saveResponse['EvnXml_id'] = $this->id;

		$this->load->model('ApprovalList_model');
		$this->ApprovalList_model->saveApprovalList(array(
			'ApprovalList_ObjectName' => 'EvnXml',
			'ApprovalList_ObjectId' => $this->id,
			'pmUser_id' => $this->promedUserId
		));

		if ( $this->isNewRecord && in_array($this->XmlType_id, $this->evnHasOneDocXmlTypeList)) {
			// Если протокол был добавлен, то нужно найти и удалить другие протоколы
			$query = '
				select EvnXml_id
				from v_EvnXml with (nolock)
				where Evn_id = :Evn_id and XmlType_id = :XmlType_id and EvnXml_id != :EvnXml_id
			';
			$result = $this->db->query($query, array(
				'Evn_id' => $this->Evn_id,
				'XmlType_id' => $this->XmlType_id,
				'EvnXml_id' => $this->id,
			));
			if ( !is_object($result) ) {
				throw new Exception('Ошибка запроса поиска протоколов осмотра.');
			}
			$res = $result->result('array');
			foreach ($res as $row) {
				$instance = swXmlTemplate::getEvnXmlModelInstance();
				$row['session'] = $this->sessionParams;
				$tmp = $instance->doDelete($row, false);
				if (!empty($tmp['Error_Msg'])) {
					throw new Exception($tmp['Error_Msg']);
				}
			}
		}
	}

	/**
	 * Создает пустой документ на основании шаблона по умолчанию или базового шаблона
	 * или шаблона переданного в параметрах
	 * или обновляет документ при перевыборе шаблона
	 * @param array $data
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * Если транзакция была начата ранее, то нужно установить false
	 * @return array Стандартный ответ модели
	 */
	public function createEmpty($data, $isAllowTransaction = true) {
		try {
			$this->load->library('swMarker');
			$this->isAllowTransaction = $isAllowTransaction;
			$this->setScenario('createEmpty');
			$this->setParams($data);
			if (empty($data['EvnXml_id'])) {
				$this->setAttributes($data);
			} else {
				// был перевыбран шаблон
				$this->setAttributes(array(
					'EvnXml_id' => $data['EvnXml_id'],
					'XmlTemplate_id' => empty($data['XmlTemplate_id']) ? null : $data['XmlTemplate_id'],
				));
			}
			$this->_validate();
			// Определяем XmlTemplate_id, если он не передан
			if (empty($this->XmlTemplate_id)) {
				$this->load->model('XmlTemplateDefault_model');
				// Получить идентификатор шаблона по умолчанию
				$response = $this->XmlTemplateDefault_model->getXmlTemplateId(array(
					'session' => $this->sessionParams,
					'MedStaffFact_id' => $this->_params['MedStaffFact_id'],
					'XmlType_id' => $this->XmlType_id,
					'EvnClass_id' => $this->evnClassId,
					'pmUser_id' => $this->promedUserId
				));
				if (count($response) > 0) {
					if (!empty($response[0]['Error_Msg'])) {
						throw new Exception($response[0]['Error_Msg']);
					}
					if(isset($response[0]['XmlTemplate_id'])) {
						$this->setAttribute('xmltemplate_id', $response[0]['XmlTemplate_id']);
					}
				}
			}
			$isEmptyTemplate = false;
			if (empty($this->XmlTemplate_id)) {
				if($this->getRegionNick() !== 'msk')
					throw new Exception('Не удалось получить идентификатор базового шаблона');
				else
					$isEmptyTemplate = true;
			}else{
				if($this->getRegionNick() == 'msk' && empty($data['EvnXml_id']))
					$isEmptyTemplate = true;
			}
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось запустить транзакцию', 500);
			}
			// получаем данные шаблона для документа с подсчетом выбора шаблона
			if($isEmptyTemplate){
				$this->setAttribute('name', 'Пустой шаблон');
				$this->setAttribute('XmlTemplateType_id', 5);
				$this->setAttribute('xmlData','<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"></xsl:stylesheet>');
				$this->setAttribute('printSettings', '');
				$this->setAttribute('htmlTemplate', '<div></div>');
				$this->setAttribute('XmlSchema_Data', '');
			} else {
				$tpl = swXmlTemplate::getXmlTemplateModelInstance();
				$response = $tpl->select(array(
					'session' => $this->sessionParams,
					'XmlTemplate_id' => $this->XmlTemplate_id,
				));
				$this->setAttribute('name', $response[0]['XmlTemplate_Caption']);
				$this->setAttribute('XmlTemplateType_id', $response[0]['XmlTemplateType_id']);
				$this->setAttribute('xmlData', $response[0]['XmlTemplate_Data']);
				$this->setAttribute('printSettings', $response[0]['XmlTemplate_Settings']);
				$this->setAttribute('htmlTemplate', $response[0]['XmlTemplate_HtmlTemplate']);
				$this->setAttribute('XmlSchema_Data', $response[0]['XmlSchema_Data']);
			}
			// Создаем содержание документа из defaultValue в XmlTemplate_Data
			$xml = swXmlTemplate::createEvnXmlData(
				toUTF($this->xmlData),
				toUTF($this->XmlSchema_Data),
				true
			);
			$xml = toAnsi($xml);

			// обрабатываем спецмаркерами при создании документа
			// шаблон отображения документа
			$evn_xml_data = SwXmlTemplate::transformEvnXmlDataToArr($xml, true);
			swMarker::processingTextWithMarkers($this->htmlTemplate, $this->Evn_id, array(
				'isPrint' => false,
				'EvnClass_id' => $this->evnClassId,
				'From_Evn_id' => $this->fromEvnId,
				'EvnXml_id' => empty($data['EvnXml_id']) ? null : $data['EvnXml_id']
			), 0, $evn_xml_data);
			// и содержание документа
			swMarker::processingTextWithMarkers($xml, $this->Evn_id, array(
				'isPrint'=>false,
				'htmlentities' =>true,
				'EvnClass_id' => $this->evnClassId,
				'From_Evn_id'=> $this->fromEvnId,
				'EvnXml_id' => empty($data['EvnXml_id']) ? null : $data['EvnXml_id']
			), 0, $evn_xml_data);
			$xml = SwXmlTemplate::convertFormDataArrayToXml(array($evn_xml_data));
			$this->setAttribute('data', $xml);

			// Сохраняем
			$this->setScenario($this->isNewRecord ? 'create' : 'update');
			$this->_beforeSave();
			$tmp = $this->_save();
			$this->setAttribute(self::ID_KEY, $tmp[0][$this->primaryKey()]);
			$this->_afterSave($tmp);
			if ( !$this->commitTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
			if ($this->isDebug) {
				// только на тестовом и только, если что-то пошло не так
				//$this->_saveResponse['Error_Msg'] .= ' ' . $e->getTraceAsString();
			}
		}
		$this->_saveResponse[$this->primaryKey(true)] = $this->id;
		$this->_onSave();
		return $this->_saveResponse;
	}

	/**
	 * МАРМ-версия
	 * Создает пустой документ на основании шаблона по умолчанию или базового шаблона
	 * или шаблона переданного в параметрах
	 * или обновляет документ при перевыборе шаблона

	 */
	public function mCreateEmpty($data, $isAllowTransaction = true) {
		try {
			$this->load->library('swMarker');
			$this->isAllowTransaction = $isAllowTransaction;
			$this->setScenario('createEmpty');
			$this->setParams($data);
			if (empty($data['EvnXml_id'])) {
				$this->setAttributes($data);
			} else {
				// был перевыбран шаблон
				$this->setAttributes(array(
					'EvnXml_id' => $data['EvnXml_id'],
					'XmlTemplate_id' => empty($data['XmlTemplate_id']) ? null : $data['XmlTemplate_id'],
				));
			}
			$this->_validate();
			// Определяем XmlTemplate_id, если он не передан
			if (empty($this->XmlTemplate_id)) {
				$this->load->model('XmlTemplateDefault_model');
				// Получить идентификатор шаблона по умолчанию
				$response = $this->XmlTemplateDefault_model->getXmlTemplateId(array(
					'session' => $this->sessionParams,
					'MedStaffFact_id' => $this->_params['MedStaffFact_id'],
					'XmlType_id' => $this->XmlType_id,
					'EvnClass_id' => $this->evnClassId,
					'pmUser_id' => $this->promedUserId
				));
				if (count($response) > 0) {
					if (!empty($response[0]['Error_Msg'])) {
						throw new Exception($response[0]['Error_Msg']);
					}
					if(isset($response[0]['XmlTemplate_id'])) {
						$this->setAttribute('xmltemplate_id', $response[0]['XmlTemplate_id']);
					}
				}
			}
			$isEmptyTemplate = false;
			if (empty($this->XmlTemplate_id)) {
				if($this->getRegionNick() !== 'msk')
					throw new Exception('Не удалось получить идентификатор базового шаблона');
				else
					$isEmptyTemplate = true;
			}else{
				if($this->getRegionNick() == 'msk' && empty($data['EvnXml_id']))
					$isEmptyTemplate = true;
			}
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось запустить транзакцию', 500);
			}
			// получаем данные шаблона для документа с подсчетом выбора шаблона
			$tpl = swXmlTemplate::getXmlTemplateModelInstance();
			$response = $tpl->select(array(
				'session' => $this->sessionParams,
				'XmlTemplate_id' => $this->XmlTemplate_id,
			));
			if($isEmptyTemplate){
				$this->setAttribute('name', 'Пустой шаблон');
				$this->setAttribute('XmlTemplateType_id', 5);
				$this->setAttribute('xmlData','<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"></xsl:stylesheet>');
				$this->setAttribute('printSettings', '');
				$this->setAttribute('htmlTemplate', '<div></div>');
				$this->setAttribute('XmlSchema_Data', '');
			} else {
				$tpl = swXmlTemplate::getXmlTemplateModelInstance();
				$response = $tpl->select(array(
					'session' => $this->sessionParams,
					'XmlTemplate_id' => $this->XmlTemplate_id,
				));
				$this->setAttribute('name', $response[0]['XmlTemplate_Caption']);
				$this->setAttribute('XmlTemplateType_id', $response[0]['XmlTemplateType_id']);
				$this->setAttribute('xmlData', $response[0]['XmlTemplate_Data']);
				$this->setAttribute('printSettings', $response[0]['XmlTemplate_Settings']);
				$this->setAttribute('htmlTemplate', $response[0]['XmlTemplate_HtmlTemplate']);
				$this->setAttribute('XmlSchema_Data', $response[0]['XmlSchema_Data']);
			}
			// Создаем содержание документа из defaultValue в XmlTemplate_Data
			$xml = swXmlTemplate::createEvnXmlData(
				toUTF($this->xmlData),
				toUTF($this->XmlSchema_Data),
				true
			);
			$xml = toAnsi($xml);

			// обрабатываем спецмаркерами при создании документа
			// шаблон отображения документа
			$evn_xml_data = SwXmlTemplate::transformEvnXmlDataToArr($xml, true);
			swMarker::processingTextWithMarkers($this->htmlTemplate, $this->Evn_id, array(
				'isPrint' => false,
				'EvnClass_id' => $this->evnClassId,
				'From_Evn_id' => $this->fromEvnId,
				'EvnXml_id' => empty($data['EvnXml_id']) ? null : $data['EvnXml_id']
			), 0, $evn_xml_data);
			// и содержание документа
			swMarker::processingTextWithMarkers($xml, $this->Evn_id, array(
				'isPrint'=>false,
				'htmlentities'=>true,
				'EvnClass_id'=>$this->evnClassId,
				'From_Evn_id'=>$this->fromEvnId,
				'EvnXml_id' => empty($data['EvnXml_id']) ? null : $data['EvnXml_id']
			), 0, $evn_xml_data);
			$xml = SwXmlTemplate::convertFormDataArrayToXml(array($evn_xml_data));
			$this->setAttribute('data', $xml);

			// Сохраняем
			$this->setScenario($this->isNewRecord ? 'create' : 'update');
			$this->_beforeSave();
			$tmp = $this->_save();
			$this->setAttribute(self::ID_KEY, $tmp[0][$this->primaryKey()]);
			$this->_afterSave($tmp);
			if ( !$this->commitTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
		$this->_saveResponse[$this->primaryKey(true)] = $this->id;
		$this->_onSave();
		return $this->_saveResponse;
	}



	/**
	 *  Функция "Восстановить раздел"
	 */
	function resetSection($data)
	{
		try {
			if ( empty($data['EvnXml_id']) || empty($data['session']) || empty($data['name']) ) {
				throw new Exception('Неправильные входящие параметры', 500);
			}
			$this->applyData(array(
				'scenario' => 'resetSection',
				'EvnXml_id' => $data['EvnXml_id'],
				'session' => $data['session'],
				'name' => $data['name'],
			));
			$this->_validate();
			$this->load->library('swXmlTemplate');
			$evn_data_arr = swXmlTemplate::transformEvnXmlDataToArr(toUTF($this->data), true);
			if ( false == array_key_exists($this->_params['name'], $evn_data_arr) ) {
				throw new Exception('Данных с указанным именем в документе нет!');
			}
			$value = swXmlTemplate::getDefaultValueByName(
				toUTF($this->xmlData),
				toUTF($this->_params['name'])
			);
			if (!empty($value)) {
				$value = toAnsi($value);
				// обрабатываем спецмаркерами
				$this->load->library('swMarker');
				$value= swMarker::processingTextWithMarkers($value, $this->Evn_id, array(
					'isPrint'=>false,
					'htmlentities'=>true,
					'EvnClass_id'=>$this->evnClassId,
					'From_Evn_id'=>$this->fromEvnId,
				), 0, $evn_data_arr);
			}
			$evn_data_arr[$this->_params['name']] = toUTF($value);
			$new_data = swXmlTemplate::convertFormDataArrayToXml(array($evn_data_arr));
			$new_data = toAnsi($new_data);
			$this->setAttribute('data', $new_data);
			// Сохраняем
			$this->setScenario(self::SCENARIO_SET_ATTRIBUTE);
			$this->_beforeSave();
			$tmp = $this->_save();
			$this->_afterSave($tmp);
			$this->_saveResponse['html'] = html_entity_decode($value,ENT_NOQUOTES,'windows-1251');
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
		return $this->_saveResponse;
	}

	/**
	 *  Удаляет одно поле из EvnXml, из XmlTemplate_Data и из XmlTemplate_HtmlTemplate
	 */
	function destroySection($data)
	{
		try {
			if ( empty($data['EvnXml_id']) || empty($data['session']) || empty($data['name']) ) {
				throw new Exception('Неправильные входящие параметры', 500);
			}
			$this->applyData(array(
				'scenario' => 'destroySection',
				'EvnXml_id' => $data['EvnXml_id'],
				'session' => $data['session'],
				'name' => $data['name'],
			));
			$this->_validate();

			$this->load->library('swXmlTemplate');
			$evn_data_arr = swXmlTemplate::transformEvnXmlDataToArr(toUTF($this->data), true);
			if ( false == array_key_exists($this->_params['name'], $evn_data_arr) ) {
				throw new Exception('Данных с указанным именем в документе нет!');
			}

			//удаляем из EvnXml_Data
			unset($evn_data_arr[$this->_params['name']]);
			$new_data = swXmlTemplate::convertFormDataArrayToXml(array($evn_data_arr));
			$new_data = toAnsi($new_data);
			$this->setAttribute('data', $new_data);

			if (!empty($this->XmlSchema_Data)) {
				//удаляем из XmlSchema_Data
				$template_field_data = array();
				foreach($evn_data_arr as $k=>$v) {
					$template_field_data[] = array('id'=>$k);
				}
				$this->setAttribute('XmlSchema_Data', swXmlTemplate::createXmlSchemaData($template_field_data));
			} else {
				$this->setAttribute('XmlSchema_Data', null);
			}

			//удаляем из XmlTemplate_Data
			//Если бы $template_field_data была получена правильно, то можно было бы просто заново сгенерировать шаблон с помощью createXmlTemplateData
			$new_xmlData = swXmlTemplate::destroyFieldXmlTemplateData(
				toUTF($this->xmlData),
				toUTF($this->_params['name'])
			);
			$new_xmlData = toAnsi($new_xmlData);
			$this->setAttribute('xmlData', $new_xmlData);

			//удаляем из XmlTemplate_HtmlTemplate
			$new_HtmlTemplate = swXmlTemplate::destroyFieldAreaHtmlTemplate(
				$this->htmlTemplate,
				$this->_params['name']
			);
			$this->setAttribute('htmlTemplate', $new_HtmlTemplate);

			// Сохраняем
			$this->setScenario('update');
			$this->_beforeSave();
			$tmp = $this->_save();
			$this->_afterSave($tmp);
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
		return $this->_saveResponse;
	}

	/**
	 *  Записывает одно или несколько полей в EvnXml
	 */
	function updateSectionContent($data)
	{
		try {
			if ( empty($data['EvnXml_id']) ) {
				throw new Exception('Неправильные входящие параметры', 500);
			}
			$this->setScenario('updateSectionContent');
			$this->setParams($data);
			$this->setAttributes(array(
				'EvnXml_id' => $data['EvnXml_id'],
			));
			$this->_validate();

			if (strlen($this->_params['name']) > 0) {
				$evn_data_arr = array();
				$evn_data_arr[$this->_params['name']] = toUTF($this->_params['value']);
			} else if(strlen($this->_params['XmlData']) > 0) {
				$evn_data_arr = json_decode(toUTF($this->_params['XmlData']), true);
				if( !is_array($evn_data_arr) ) {
					throw new Exception('Указаны параметры в неправильном формате!');
				}
			} else {
				throw new Exception('Указаны неправильные параметры!');
			}

			$this->load->library('swXmlTemplate');
			$new_data = swXmlTemplate::updateEvnXmlData(
				toUTF($this->data),
				toUTF($this->XmlSchema_Data),
				$evn_data_arr,
				$this->_params['isHTML']
			);
			$new_data = toAnsi($new_data);
			$this->setAttribute('data', $new_data);
			// Сохраняем
			$this->setScenario(self::SCENARIO_SET_ATTRIBUTE);
			$this->_beforeSave();
			if ($this->IsSigned == 2) {
				// делаем документ неактуальным
				$this->setAttribute('issigned', 1);
			}
			$tmp = $this->_save();
			$this->_afterSave($tmp);
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
		return $this->_saveResponse;
	}

	/**
	 *  МАРМ версия
	 *  Записывает одно или несколько полей в EvnXml
	 */
	function mUpdateSectionContent($data)
	{
	$saveData = $data['template'];

	foreach ($saveData as $item) {
		$data['name'] = !empty($item['name']) ? $item['name'] : "";
		$data['value'] = !empty($item['value']) ? $item['value'] : "";
		$data['XmlData'] = !empty($item['XmlData']) ? $item['XmlData'] : "";
		$data['isHTML'] = !empty($item['isHTML']) ? $item['isHTML'] : "";

		try {
			if ( empty($data['EvnXml_id']) ) {
				throw new Exception('Неправильные входящие параметры', 500);
			}
			$this->setScenario('updateSectionContent');
			$this->setParams($data);
			$this->setAttributes(array(
				'EvnXml_id' => $data['EvnXml_id'],
			));
			$this->_validate();

			if (strlen($this->_params['name']) > 0) {
				$evn_data_arr = array();
				$evn_data_arr[$this->_params['name']] = toUTF($this->_params['value']);
			} else if(strlen($this->_params['XmlData']) > 0) {
				$evn_data_arr = json_decode(toUTF($this->_params['XmlData']), true);
				if( !is_array($evn_data_arr) ) {
					throw new Exception('Указаны параметры в неправильном формате!');
				}
			} else {
				throw new Exception('Указаны неправильные параметры!');
			}

			$this->load->library('swXmlTemplate');
			$new_data = swXmlTemplate::updateEvnXmlData(
				toUTF($this->data),
				toUTF($this->XmlSchema_Data),
				$evn_data_arr,
				$this->_params['isHTML']
			);
			$new_data = toAnsi($new_data);
			$this->setAttribute('data', $new_data);
			// Сохраняем
			$this->setScenario(self::SCENARIO_SET_ATTRIBUTE);
			$this->_beforeSave();
			$tmp = $this->_save();
			$this->_afterSave($tmp);
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
	}
		return $this->_saveResponse;
	}

	/**
	 * Сохранение документа как шаблона
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	public function saveAsTemplate($data) {
		try {
			if ( empty($data['EvnXml_id']) ) {
				throw new Exception('Неправильные входящие параметры', 500);
			}
			$this->setScenario('saveAsTemplate');
			$this->setParams($data);
			$this->setAttributes(array(
				'EvnXml_id' => $data['EvnXml_id'],
			));
			$this->_validate();

			if (empty($this->XmlTemplate_id)) {
				throw new Exception('Исходный шаблон удален!');
			}

			$this->load->library('swXmlTemplate');

			$this_tpl = swXmlTemplate::getXmlTemplateModelInstance();
			$new_tpl_data = $this_tpl->doLoadEditForm(array(
				'session'=>$this->sessionParams,
				'XmlTemplate_id'=>$this->XmlTemplate_id,
			));
			$new_tpl_data = $new_tpl_data[0];
			$new_tpl_data['scenario'] = 'create';
			$new_tpl_data['XmlTemplate_id'] = null;
			$new_tpl_data['XmlTemplate_Caption'] = $this_tpl->caption /*. ' ' . $this->id*/;
			$new_tpl_data['XmlType_id'] = $this->XmlType_id;
			$new_tpl_data['XmlTemplate_HtmlTemplate'] = swXmlTemplate::getHtmlTemplate($new_tpl_data);

			$evn_data_arr = swXmlTemplate::transformEvnXmlDataToArr(toUTF($this->data), true);
			array_walk($evn_data_arr, 'ConvertFromUTF8ToWin1251');
			swXmlTemplate::processingData($new_tpl_data, $this->promedUserId, $evn_data_arr);

			$new_tpl = swXmlTemplate::getXmlTemplateModelInstance();
			$new_tpl_data['htmltemplate'] = $new_tpl_data['XmlTemplate_HtmlTemplate'];
			$new_tpl_data['xmldata'] = $new_tpl_data['XmlTemplate_Data'];
			if (empty($new_tpl_data['XmlTemplate_Settings'])) {
				$this->load->library('swXmlTemplateSettings');
				$new_tpl_data['printsettings'] = swXmlTemplateSettings::getJsonFromArr(array());
				$new_tpl_data['XmlTemplate_Settings'] = '';
			} else {
				$new_tpl_data['printsettings'] = $new_tpl_data['XmlTemplate_Settings'];
			}
			unset($new_tpl_data['XmlTemplate_HtmlTemplate']);
			unset($new_tpl_data['XmlTemplate_Data']);
			unset($new_tpl_data['XmlTemplate_Settings']);

			$new_tpl_data['session'] = $this->sessionParams;
			return $new_tpl->doSave($new_tpl_data);
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
			return $this->_saveResponse;
		}
	}

	/**
	 * Восстановление исходного состояния документа
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	public function restore($data) {
		try {
			if ( empty($data['EvnXml_id']) ) {
				throw new Exception('Неправильные входящие параметры', 500);
			}
			$this->setScenario('restore');
			$this->setParams($data);
			$this->setAttributes(array(
				'EvnXml_id' => $data['EvnXml_id'],
			));
			$this->_validate();
			$this->_restore();
			// сохраняем
			$this->setScenario('update');
			$this->_beforeSave();
			$tmp = $this->_save();
			$this->_afterSave($tmp);
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
		return $this->_saveResponse;
	}

	/**
	 * Копирование документа
	 * @param array $data
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * Если транзакция была начата ранее, то нужно установить false
	 * @return array Стандартный ответ модели
	 */
	public function doCopy($data, $isAllowTransaction = true)
	{
		try {
			if ( empty($data['EvnXml_id']) || empty($data['Evn_id'])) {
				throw new Exception('Неправильные входящие параметры', 500);
			}
			$this->setScenario('doCopy');
			$this->setParams($data);
			$this->setAttributes(array(
				'EvnXml_id' => $data['EvnXml_id'],
			));
			$this->_validate();

			//Проверяем наличие в параметрах тип копирования
			if (!empty($data['copyMethod']) && in_array($data['copyMethod'], array('withDoc','withFullRestore'))) {
				$method = $data['copyMethod'];
			} else {
				$this->load->helper('Options');
				// Получаем настройки
				$options = getOptions();
				$method = 'withFullRestore';
				if (empty($options['polka']['arm_evn_xml_copy'])) {
					$method = 'withFullRestore';
				} else if (2 == $options['polka']['arm_evn_xml_copy']) {
					$method = 'withDoc';
				}
			}

			// Копируем документ
			$new_instance = swXmlTemplate::getEvnXmlModelInstance();
			switch ($method) {
				case 'withDoc':
					$this->_saveResponse = $new_instance->copyWithDoc($this, $data['Evn_id'], $isAllowTransaction);
					break;
				default: // 'withFullRestore'
					$this->_saveResponse = $new_instance->copyWithFullRestore($this, $data['Evn_id'], $isAllowTransaction);
					break;
			}
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
		return $this->_saveResponse;
	}

	/**
	 * Копировать осмотр из предыдущего посещения
	 * @param EvnXmlBase_model $instance
	 * @param int $evn_id
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * @return array Стандартный ответ модели
	 * @throws Exception
	 */
	public function copyWithDoc(EvnXmlBase_model $instance, $evn_id, $isAllowTransaction = true)
	{
		/*
		 Восстанавливаем исходный шаблон без изменения содержания
		 и замещаем спецмаркеры в шаблоне отображения значениями нового учетного документа,
		 если какие-то разделы были удалены - то удаляем их
		*/
		$this->applyData(array(
			'scenario' => 'create',
			'EvnXml_id' => null,
			'Evn_sid' => null,
			'Evn_id' => $evn_id,
			'XmlTemplate_id' => $instance->XmlTemplate_id,
			'XmlType_id' => $instance->XmlType_id,
			'EvnXml_Name' => $instance->name,
			'session' => $instance->sessionParams,
		));
		$this->_restore(array(
			'withoutDoc' => true,
			'newXmlSchema' => $instance->XmlSchema_Data,
			'newXmlTemplateData' => $instance->xmlData,
			'newXmlTemplateHtml' => $instance->htmlTemplate,
			'newData' => $instance->data,
		));
		// сохраняем
		$this->isAllowTransaction = $isAllowTransaction;
		$this->_beforeSave();
		$tmp = $this->_save();
		$this->setAttribute(self::ID_KEY, $tmp[0][$this->primaryKey()]);
		$this->_afterSave($tmp);
		$this->_saveResponse['EvnXml_id'] = $this->id;
		return $this->_saveResponse;
	}

	/**
	 * Копирование документа с восстановлением шаблона осмотра предыдущего посещения
	 * @param EvnXmlBase_model $instance
	 * @param int $evn_id
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * @return array Стандартный ответ модели
	 * @throws Exception
	 */
	public function copyWithFullRestore(EvnXmlBase_model $instance, $evn_id, $isAllowTransaction = true)
	{
		/*
		 Восстанавливаем исходный шаблон
		 Разделы заполняем данными по умолчанию из шаблона
		 и замещаем спецмаркеры в шаблоне и разделах
		 значениями нового учетного документа
		*/
		$this->applyData(array(
			'scenario' => 'create',
			'EvnXml_id' => null,
			'Evn_sid' => null,
			'Evn_id' => $evn_id,
			'XmlTemplate_id' => $instance->XmlTemplate_id,
			'XmlType_id' => $instance->XmlType_id,
			'EvnXml_Name' => $instance->name,
			'session' => $instance->sessionParams,
		));
		$this->_restore();
		// сохраняем
		$this->isAllowTransaction = $isAllowTransaction;
		$this->_beforeSave();
		$tmp = $this->_save();
		$this->setAttribute(self::ID_KEY, $tmp[0][$this->primaryKey()]);
		$this->_afterSave($tmp);
		$this->_saveResponse['EvnXml_id'] = $this->id;
		return $this->_saveResponse;
	}

	/**
	 * Создает документ для печати без сохранения
	 * @param array $data
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * Если транзакция была начата ранее, то нужно установить false
	 * @return array Стандартный ответ модели
	 */
	public function doDirectPrint($data) {
		
		if (empty($data['Evn_id']) && empty($data['Person_id'])) {
			throw new Exception('Не указаны обязательные параметры!');
		}
		
		//$this->setScenario('');
		$this->setParams($data);
		$this->setAttribute('XmlTemplate_id', $data['XmlTemplate_id']);
		$this->setAttribute('Evn_id', $data['Evn_id']);
		$this->setAttribute('Person_id', $data['Person_id']);
		$this->setAttribute('fromEvnId', $data['Evn_id']);
		//$this->_validate();
		// получаем данные шаблона для документа с подсчетом выбора шаблона
		$tpl = swXmlTemplate::getXmlTemplateModelInstance();
		$response = $tpl->select(array(
			'session' => $this->sessionParams,
			'XmlTemplate_id' => $this->XmlTemplate_id,
		));
		if (!$response || !count($response)) {
			return false;
		}
		$this->setAttribute('name', $response[0]['XmlTemplate_Caption']);
		$this->setAttribute('XmlTemplateType_id', $response[0]['XmlTemplateType_id']);
		$this->setAttribute('xmlData', $response[0]['XmlTemplate_Data']);
		$this->setAttribute('printSettings', $response[0]['XmlTemplate_Settings']);
		$this->setAttribute('htmlTemplate', $response[0]['XmlTemplate_HtmlTemplate']);
		$this->setAttribute('XmlSchema_Data', $response[0]['XmlSchema_Data']);
		// Создаем содержание документа из defaultValue в XmlTemplate_Data
		$xml = swXmlTemplate::createEvnXmlData(
			toUTF($this->xmlData),
			toUTF($this->XmlSchema_Data),
			true
		);
		$xml = toAnsi($xml);
		$this->setAttribute('data', $xml);
		// обрабатываем спецмаркерами
		$this->load->library('swMarker');
		// шаблон отображения документа
		$tmp = swMarker::processingTextWithMarkers($this->htmlTemplate, $this->Evn_id, array(
			'isPrint'=>false,
			'EvnClass_id'=>$this->evnClassId,
			'Person_id'=>$data['Person_id'],
			'From_Evn_id'=>$this->fromEvnId
		));

		$this->setAttribute('htmlTemplate', $tmp);
		// и содержание документа
		$tmp = swMarker::processingTextWithMarkers($this->data, $this->Evn_id, array(
			'isPrint'=>false,
			'htmlentities'=>true,
			'Person_id'=>$data['Person_id'],
			'From_Evn_id'=>$this->fromEvnId
		));
		$this->setAttribute('data', $tmp);

		return array(
			'EvnXml_id' => null,
			'XmlTemplate_Data' => $this->xmlData,
			'EvnClass_id' => $this->evnClassId,
			'EvnXml_Data' => $this->data,
			'XmlTemplate_Settings' => $this->printSettings,
			'XmlTemplate_HtmlTemplate' => $this->htmlTemplate
		);
	}


	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function checkIsMarkeryBezDannix($data){

		$markers_empty = array();

		if (empty($data['Evn_id']) && empty($data['Person_id'])) {
			throw new Exception('Не указаны обязательные параметры!');
		}

		//$this->setScenario('');
		$this->setParams($data);
		$this->setAttribute('XmlTemplate_id', $data['XmlTemplate_id']);
		$this->setAttribute('Evn_id', $data['Evn_id']);
		$this->setAttribute('Person_id', $data['Person_id']);
		$this->setAttribute('fromEvnId', $data['Evn_id']);
		//$this->_validate();
		// получаем данные шаблона для документа с подсчетом выбора шаблона
		$tpl = swXmlTemplate::getXmlTemplateModelInstance();
		$response = $tpl->select(array(
			'session' => $this->sessionParams,
			'XmlTemplate_id' => $this->XmlTemplate_id,
		));
		if (!$response || !count($response)) {
			return false;
		}
		$this->setAttribute('name', $response[0]['XmlTemplate_Caption']);
		$this->setAttribute('XmlTemplateType_id', $response[0]['XmlTemplateType_id']);
		$this->setAttribute('xmlData', $response[0]['XmlTemplate_Data']);
		$this->setAttribute('printSettings', $response[0]['XmlTemplate_Settings']);
		$this->setAttribute('htmlTemplate', $response[0]['XmlTemplate_HtmlTemplate']);
		$this->setAttribute('XmlSchema_Data', $response[0]['XmlSchema_Data']);
		// Создаем содержание документа из defaultValue в XmlTemplate_Data
		$xml = swXmlTemplate::createEvnXmlData(
			toUTF($this->xmlData),
			toUTF($this->XmlSchema_Data),
			true
		);
		$xml = toAnsi($xml);
		$this->setAttribute('data', $xml);
		// обрабатываем спецмаркерами
		$this->load->library('swMarker');
		// шаблон отображения документа
		$tmp_markers_empty = swMarker::checkIsDataTextWithMarkers($this->htmlTemplate, $this->Evn_id, array(
			'isPrint'=>false,
			'EvnClass_id'=>$this->evnClassId,
			'Person_id'=>$data['Person_id'],
			'From_Evn_id'=>$this->fromEvnId
		));

		$markers_empty = array_merge($markers_empty, $tmp_markers_empty);

		$tmp_markers_empty = swMarker::checkIsDataTextWithMarkers($this->data, $this->Evn_id, array(
			'isPrint'=>false,
			'htmlentities'=>true,
			'Person_id'=>$data['Person_id'],
			'From_Evn_id'=>$this->fromEvnId
		));

		$markers_empty = array_merge($markers_empty, $tmp_markers_empty);

		return $markers_empty;
	}

	/**
	 * Восстановление исходного состояния документа
	 * полностью (по умолчанию) или частично
	 * @param array $options
	 * @throws Exception
	 */
	protected function _restore($options = array())
	{
		$isFull = empty($options['withoutDoc']);
		if (false == $isFull
			&& (empty($options['newXmlTemplateData']) || empty($options['newData']) || false == array_key_exists('newXmlSchema',$options))
		) {
			throw new Exception('Копировать осмотр. Не указаны обязательные параметры!');
		}
		if (empty($this->XmlTemplate_id)) {
			throw new Exception('Базовый шаблон удален. Выберите другой шаблон!');
		}
		// получаем данные шаблона для документа без подсчета выбора шаблона
		$this_tpl = swXmlTemplate::getXmlTemplateModelInstance();
		$response = $this_tpl->loadData(array(
			'session'=>$this->sessionParams,
			'XmlTemplate_id'=>$this->XmlTemplate_id,
		));

		$this->setAttribute('XmlTemplateType_id', $response[0]['XmlTemplateType_id']);
		$this->setAttribute('printSettings', $response[0]['XmlTemplate_Settings']);
		$htmlTemplate = $response[0]['XmlTemplate_HtmlTemplate'];

		$this->load->library('swMarker');

		if ($isFull) {
			// Создаем содержание документа из defaultValue в XmlTemplate_Data
			$new_data = swXmlTemplate::createEvnXmlData(
				toUTF($response[0]['XmlTemplate_Data']),
				toUTF($response[0]['XmlSchema_Data']),
				true
			);
			$new_data = toAnsi($new_data);
			$new_data_arr = swXmlTemplate::transformEvnXmlDataToArr($new_data, true);
			// обрабатываем спецмаркерами содержание документа
			$new_data = swMarker::processingTextWithMarkers($new_data, $this->Evn_id, array(
				'isPrint'=>false,
				'htmlentities'=>true,
				'EvnClass_id'=>$this->evnClassId,
				'From_Evn_id'=>$this->fromEvnId,
				'cacheEvnXml'=>$this->id
			), 0, $new_data_arr, true);
			$this->setAttribute('data', $new_data);
			$this->setAttribute('XmlSchema_Data', $response[0]['XmlSchema_Data']);
			$this->setAttribute('xmlData', $response[0]['XmlTemplate_Data']);
		} else {
			if (!empty($options['newXmlTemplateHtml'])) {
				$htmlTemplate = $options['newXmlTemplateHtml'];
			}
			// нужно удалить из шаблона отображения разделы, которые были удалены в исходном документе
			$tplDataArr = swXmlTemplate::createEvnXmlDataArray(
				toUTF($response[0]['XmlTemplate_Data']),
				toUTF($response[0]['XmlSchema_Data']),
				true
			);
			$docDataArr = swXmlTemplate::createEvnXmlDataArray(
				toUTF($options['newXmlTemplateData']),
				toUTF($options['newXmlSchema']),
				true
			);
			foreach ($tplDataArr as $key => $value) {
				if (isset($docDataArr[$key])) {
					// проверяем следующий раздел
					continue;
				}
				//удаляем раздел $key из XmlTemplate_HtmlTemplate
				$htmlTemplate = swXmlTemplate::destroyFieldAreaHtmlTemplate(
					$htmlTemplate,
					$key
				);
			}
			// НЕ обрабатываем спецмаркерами содержание документа, т.к. они были замещены данными предыдущего осмотра
			$this->setAttribute('data', $options['newData']);
			$this->setAttribute('XmlSchema_Data', $options['newXmlSchema']);
			$this->setAttribute('xmlData', $options['newXmlTemplateData']);
		}
		// обрабатываем спецмаркерами шаблон отображения документа ДО того как он будет записан в XmlTemplateHtml
		$evn_xml_data = SwXmlTemplate::transformEvnXmlDataToArr($this->data, true);
		$htmlTemplate = swMarker::processingTextWithMarkers($htmlTemplate, $this->Evn_id, array(
			'isPrint'=>false,
			'EvnClass_id'=>$this->evnClassId,
			'From_Evn_id'=>$this->fromEvnId,
			'cacheEvnXml'=>$this->id,
			'restore'=>$isFull?'full':'simple'
		), 0, $evn_xml_data, $isFull);
		$this->setAttribute('data',  SwXmlTemplate::convertFormDataArrayToXml(array($evn_xml_data)));
		$this->setAttribute('htmlTemplate', $htmlTemplate);
	}

	/**
	 * Запрос данных документа для просмотра или редактирования в панели sw.Promed.EvnXmlPanel, в окне swEmkDocumentsListWindow
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function doLoadEvnXmlPanel($data)
	{
		//$this->setScenario('doLoadEvnXmlPanel');
		//$this->setParams($data);
		if ( !empty($data['EvnXml_id']) ) {
			$filters = 'EvnXml.EvnXml_id = :EvnXml_id';
			$params = array('EvnXml_id' => $data['EvnXml_id']);
			$joinType = ($this->usePostgreLis) ? 'left' : 'inner';
			$from = "v_EvnXml EvnXml with (NOLOCK)
					{$joinType} join v_Evn Evn with (NOLOCK) on Evn.Evn_id = EvnXml.Evn_id";
		} else if ( !empty($data['Evn_id']) ) {
			$filters = 'Evn.Evn_id = :Evn_id';
			$params = array(
				'Evn_id' => $data['Evn_id'],
			);
			$from = 'v_Evn Evn with (NOLOCK)
					inner join v_EvnXml EvnXml with (NOLOCK) on EvnXml.Evn_id = Evn.Evn_id';
			if (empty($data['XmlType_id'])) {
				$from .= ' and (EvnXml.XmlType_id <> :XmlType_id or EvnXml.XmlType_id is null)';
				$params['XmlType_id'] = swEvnXml::MULTIPLE_DOCUMENT_TYPE_ID;
			} else if (swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID == $data['XmlType_id']) {
				// EVN_USLUGA_PROTOCOL_TYPE_ID - это общий тип протоколов услуг
				$types = array(swEvnXml::EVN_USLUGA_PROTOCOL_TYPE_ID, swEvnXml::LAB_USLUGA_PROTOCOL_TYPE_ID);
				$from .= ' and EvnXml.XmlType_id in ('. implode(',', $types).')';
			} else {
				$from .= ' and EvnXml.XmlType_id = :XmlType_id';
				$params['XmlType_id'] = $data['XmlType_id'];
			}
			if (isset($data['isLab']) && $data['isLab']) {
				$from .= ' and EvnXml.XmlTemplate_id is null';
			}
		} else {
			throw new Exception('Неправильные параметры для получения данных документа для просмотра или редактирования!');
		}

		if (!empty($data['instance_id'])) {
			$params['instance_id'] = $data['instance_id'];
		} else {
			$params['instance_id'] = null;
		}

		$query = "
			select top 1
				Evn.Evn_id,
				Evn.Evn_pid,
				Evn.Evn_rid,
				Evn.EvnClass_id,
				Evn.EvnClass_SysNick,
				EvnXml.EvnXml_id,
				EvnXml.Evn_id as Evn_id_xml,
				EvnXml.XmlType_id,
				EvnXml.EvnXml_Name,
				EvnXml.EvnXml_Data,
				xts.XmlTemplateSettings_Settings as XmlTemplate_Settings,
				xth.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate,
				xtd.XmlTemplateData_Data as XmlTemplate_Data,
				EvnXml.XmlTemplate_id,
				EvnXml.XmlTemplateType_id,
				EvnXml.EvnXml_IsSigned,
				:instance_id as instance_id
			from {$from}
				left join XmlTemplateData xtd with (NOLOCK) on xtd.XmlTemplateData_id = EvnXml.XmlTemplateData_id
				left join XmlTemplateHtml xth with (NOLOCK) on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
				left join XmlTemplateSettings xts with (NOLOCK) on xts.XmlTemplateSettings_id = EvnXml.XmlTemplateSettings_id
			where {$filters}
			order by EvnXml.EvnXml_Index desc
		";
		// echo getDebugSQL($query, $params);
		$result = $this->queryResult($query, $params);
		if (!is_array($result)) {
			throw new Exception('Не удалось выполнить запрос данных документа');
		}

		// XmlType_id == 7 --> Протокол лабораторной услуги
		if (!empty($result[0]) && empty($result[0]['Evn_id']) && $result[0]['XmlType_id'] == 7) {
			if ($this->usePostgreLis) {
				$this->load->swapi('lis');
				$res = $this->lis->GET('EvnUsluga/EvnParams', [
					'Evn_id' => $result[0]['Evn_id_xml']
				], 'single');
			} else {
				$this->load->model('EvnUsluga_model');
				$res = $this->EvnUsluga_model->getEvnParams([
					'Evn_id' => $result[0]['Evn_id_xml']
				]);
			}
			unset($result[0]['Evn_id_xml']);
			if ($this->isSuccessful($res)) {
				$result[0]['Evn_id'] = $res['Evn_id'];
				$result[0]['Evn_pid'] = $res['Evn_pid'];
				$result[0]['Evn_rid'] = $res['Evn_rid'];
				$result[0]['EvnClass_id'] = $res['EvnClass_id'];
				$result[0]['EvnClass_SysNick'] = $res['EvnClass_SysNick'];
			}
		}

		return $result;
	}

	/**
	 * Получение списка документов для отображения в панели просмотра
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function loadListViewData($data) {
		//$this->setScenario(self::SCENARIO_VIEW_DATA);
		//$this->setParams($data);
		if (empty($data['Evn_id']) && empty($data['EvnXML_id']) ) {
			throw new Exception('Для получения списка документов требуется идентификатор учетного документа');
		}
		if (empty($data['XmlType_id'])) {
			throw new Exception('Для получения списка документов требуется указать тип документов');
		}
		$filter = '(1=1)';

		$params = array(
			'XmlType_id' => $data['XmlType_id'],
		);

		if (!empty($data['Evn_id'])){
			$filter .= " and EvnXml.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if (!empty($data['EvnXML_id'])){
			$filter .= " and EvnXml.EvnXML_id = :EvnXML_id";
			$params['EvnXML_id'] = $data['EvnXML_id'];
		}

		$frame = (!empty($data['Frame']) && $data['Frame'])?'1':'0';
		$query = "
			select
				EvnXml.Evn_id as EvnXml_pid,
				Evn.Evn_pid,
				Evn.Evn_rid,
				Evn.EvnClass_id,
				Evn.EvnClass_SysNick,
				EvnXml.XmlType_id,
				EvnXml.EvnXml_id,
				EvnXml.EvnXml_Name,
				EvnXml.EvnXml_IsSigned,
				convert(varchar, EvnXml.EvnXml_insDT, 104) as EvnXml_Date,
				EvnXml.pmUser_insID,
				RTRIM(LTRIM(ISNULL(pmUserCache.pmUser_Name, ''))) as pmUser_Name,
				{$frame} as frame,
				convert(varchar, EvnXml.EvnXml_setDT, 100) as EvnXml_setDT,
				convert(varchar, EvnXml.EvnXml_setDT, 104) as EvnXml_setDTDate,
				convert(varchar, EvnXml.EvnXml_setDT, 108) as EvnXml_setDTTime
			from
				v_EvnXml EvnXml with (NOLOCK)
				inner join v_Evn Evn with (NOLOCK) on Evn.Evn_id = EvnXml.Evn_id
				left join pmUserCache with (NOLOCK) on pmUserCache.pmUser_id = EvnXml.pmUser_insID
				left join XmlTemplateData xtd with (NOLOCK) on xtd.XmlTemplateData_id = EvnXml.XmlTemplateData_id
				left join XmlTemplateHtml xth with (NOLOCK) on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
				left join XmlTemplateSettings xts with (NOLOCK) on xts.XmlTemplateSettings_id = EvnXml.XmlTemplateSettings_id
			where
				{$filter}
				and EvnXml.XmlType_id = :XmlType_id
			order by EvnXml.EvnXml_insDT
		";

		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			throw new Exception('Не удалось выполнить запрос для получения списка документов');
		}

		$resp = $result->result('array');

		$EvnXmlIds = [];
		foreach($resp as $one) {
			if (!empty($one['EvnXml_id']) && $one['EvnXml_IsSigned'] == 2 && !in_array($one['EvnXml_id'], $EvnXmlIds)) {
				$EvnXmlIds[] = $one['EvnXml_id'];
			}
		}
		
		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (!empty($EvnXmlIds) && !empty($isEMDEnabled)) {
			$this->load->model('EMD_model');
			$signStatus = $this->EMD_model->getSignStatus([
				'EMDRegistry_ObjectName' => 'EvnXml',
				'EMDRegistry_ObjectIDs' => $EvnXmlIds,
				'MedStaffFact_id' => $data['session']['CurMedStaffFact_id'] ?? null
			]);

			foreach($resp as $key => $one) {
				$resp[$key]['EvnXml_SignCount'] = 0;
				$resp[$key]['EvnXml_MinSignCount'] = 0;
				if (!empty($one['EvnXml_id']) && $one['EvnXml_IsSigned'] == 2 && isset($signStatus[$one['EvnXml_id']])) {
					$resp[$key]['EvnXml_SignCount'] = $signStatus[$one['EvnXml_id']]['signcount'];
					$resp[$key]['EvnXml_MinSignCount'] = $signStatus[$one['EvnXml_id']]['minsigncount'];
					$resp[$key]['EvnXml_IsSigned'] = $signStatus[$one['EvnXml_id']]['signed'];
				}
			}
		}

		return $resp;
	}
	/**
	 * Получаем данные документа для печати
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function doLoadPrintData($data)
	{
		//$this->setScenario('doLoadPrintData');
		//$this->setParams($data);
		if ( !empty($data['EvnXml_id']) ) {
			$filters = 'EvnXml.EvnXml_id = :EvnXml_id';
			$params = array('EvnXml_id' => $data['EvnXml_id']);
			$from = 'v_EvnXml EvnXml with (NOLOCK)
					left join v_Evn Evn with (NOLOCK) on Evn.Evn_id = EvnXml.Evn_id';
		} else if ( !empty($data['Evn_id']) ) {
			$filters = 'Evn.Evn_id = :Evn_id';
			$params = array('Evn_id' => $data['Evn_id']);
			$from = 'v_Evn Evn with (NOLOCK)
					inner join v_EvnXml EvnXml with (NOLOCK) on EvnXml.Evn_id = Evn.Evn_id';
			// Это разрешено только для протоколов осмотра и услуг
			$from .= ' and EvnXml.XmlType_id in ('. implode(', ', $this->evnHasOneDocXmlTypeList) .')';
		} else {
			throw new Exception('Неправильные параметры для получения данных документа для печати!');
		}

		$params['region'] = $this->getRegionNick();

		$query = "
			DECLARE @Region varchar(8) = :region;
			select top 1
				Evn.Evn_id,
				Evn.EvnClass_id,
				EvnXml.EvnXml_id,
				EvnXml.XmlType_id,
				EvnXml.EvnXml_Name,
				EvnXml.EvnXml_Data,
				xts.XmlTemplateSettings_Settings as XmlTemplate_Settings,
				xth.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate,
				xtd.XmlTemplateData_Data as XmlTemplate_Data,
				EvnXml.XmlTemplateType_id
			from {$from}
				left join XmlTemplateData xtd with (NOLOCK) on xtd.XmlTemplateData_id = EvnXml.XmlTemplateData_id
				left join XmlTemplateHtml xth with (NOLOCK) on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
			OUTER APPLY ( -- временная заглушка для карелии, для нее печать документов идет с настройками ШАБЛОНА документа, а не настройками самого документа #117830
				SELECT TOP 1
					CASE @Region
						WHEN 'kareliya' THEN (SELECT TOP 1 XmlTemplateSettings_id FROM v_XmlTemplate (nolock) WHERE XmlTemplate_id = EvnXml.XmlTemplate_id)
						ELSE NULL
					END as XmlTemplateSettings_id
			) XT
				left join XmlTemplateSettings xts with (NOLOCK) on xts.XmlTemplateSettings_id = ISNULL(XT.XmlTemplateSettings_id, EvnXml.XmlTemplateSettings_id)
			where {$filters}
		";
		// echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			throw new Exception('Не удалось выполнить запрос данных документа');
		}
		$res = $result->result('array');
		if (empty($res)) {
			throw new Exception('Документ не найден');
		}
		return $res;
	}


	/**
	 * Получение шаблона документа и данных для его заполнения
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	public function loadEvnXmlForm($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		//Получаем данные для отображения
		$this->applyData($data);
		$this->_validate();
		// Обработка почти как в swEvnXml::doHtmlView
		if (swEvnXml::LAB_USLUGA_PROTOCOL_TYPE_ID == $this->XmlType_id) {
			// этот тип документов содержит готовый HTML-документ
			if (empty($this->htmlTemplate)) {
				throw new Exception('Нет данных HTML-документа');
			}
			return array(
				'html'=>$this->htmlTemplate,
				'formData'=>array(),
				'success'=>true
			);
		}

		$evn_xml_data = toUTF($this->data);
		$html = swXmlTemplate::processingXmlToHtml($evn_xml_data, toUTF($this->xmlData));
		$xml_data_arr = swXmlTemplate::transformEvnXmlDataToArr($evn_xml_data, true);
		array_walk($xml_data_arr,'ConvertFromUTF8ToWin1251');
		if (empty($html)) {
			if (empty($this->htmlTemplate) || strpos($this->data, '<UserTemplateData>')) {
				//есть UserTemplateData в EvnXml_Data. Используется устаревший шаблон без разметки областей ввода данных и областей только для печати
				$html = $xml_data_arr['UserTemplateData'];
			} else {
				//документ нового формата с множеством разделов и шаблоном отображения в XmlTemplate_HtmlTemplate
				$html = $this->htmlTemplate;
				$this->load->model('ParameterValue_model');
				$this->load->library('swMarker');
				$markers = swMarker::foundParameterMarkers($html);
				$params = $this->ParameterValue_model->getParameterFieldData($markers);
				$search=array();
				$replace=array();
				foreach($params as $id => $row) {
					$search[] = $row['marker'];
					$replace[] = '{'.$row['field_name'].'}';
				}
				$html = str_replace($search, $replace, $html);
			}
		}

		// Получаем из данных шаблона массив: список полей с дефолтными значениями
		$tpl_data_arr = swXmlTemplate::createEvnXmlDataArray(
			$this->xmlData,
			$this->XmlSchema_Data,
			false
		);

		return array(
			'html'=> $html,
			'formData'=> $this->_processingEvnXmlFormFields($tpl_data_arr, $xml_data_arr),
			'success'=>true
		);
	}


	/**
	 * Получение полей шаблона для генерации полей на клиенте
	 */
	public function getEvnXmlFormFields($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		try {
			//Получаем данные для отображения
			$this->applyData($data);
			$this->_validate();
			$xml_data_arr = swXmlTemplate::transformEvnXmlDataToArr(toUTF($this->data), true);
			array_walk($xml_data_arr,'ConvertFromUTF8ToWin1251');
			// Получаем из данных шаблона массив: список полей с дефолтными значениями
			$tpl_data_arr = swXmlTemplate::createEvnXmlDataArray(
				$this->xmlData,
				$this->XmlSchema_Data,
				false
			);
			return array(
				'success'=>true,
				'response'=>$this->_processingEvnXmlFormFields($tpl_data_arr, $xml_data_arr),
			);
		} catch (Exception $e) {
			return array(array('Error_Msg'=>$e->getMessage()));
		}
	}


	/**
	 * @param array $tpl_data_arr
	 * @param array $xml_data_arr
	 * @return array
	 * @throws Exception
	 */
	protected function _processingEvnXmlFormFields($tpl_data_arr, $xml_data_arr)
	{
		/*
		$html = $this->htmlTemplate;
		$this->load->model('ParameterValue_model');
		$this->load->library('swMarker');
		$markers = swMarker::foundParameterMarkers($html);
		$params = $this->ParameterValue_model->getParameterFieldData($markers);
		*/
		$result = array();
		foreach ($tpl_data_arr as $name => $value) {
			$result[] = array(
				'name'=>$name,
				//Если есть заполненные данные, используем их
				'value'=>(isset($xml_data_arr["$name"]))?$xml_data_arr["$name"]:$value,
			);
		}
		$XmlDataSectionTypeList = $this->loadXmlDataSectionList(array('onlyNamedSection'=>FALSE));
		foreach ($result as $idx => $field) {
			foreach ($XmlDataSectionTypeList as $XmlDataSectionType) {
				// Получаем названия полей textarea
				if ($XmlDataSectionType['XmlDataSection_SysNick'] == $field['name']) {
					$result["$idx"]['fieldLabel'] = $XmlDataSectionType['XmlDataSection_Name'];
					$result["$idx"]['type'] = 'textarea';
				}
			}
			// Получаем названия и значения полей combo,radio и checkbox
			if  (strpos($field['name'], 'parameter') !== FALSE) {
				// Получаем данные по параметру
				// @todo вместо _loadParameterData использовать результат из ParameterValue_model->getParameterFieldData
				$fieldData = $this->_loadParameterData($field['name']);
				$field['items']=array();
				$field['fieldLabel']=array();
				$field['type']='';
				foreach ($fieldData as $val) {
					if ($val['sysnick'] == $field['name'] ) {
						$field['fieldLabel'] = $val['name'];
						$field['type'] = $val['type'];
					} else {
						$field['items'][] = array(
							'id'=>$val['id'],
							'fieldLabel'=>$val['name'],
							'name'=>$val['sysnick']
						);
					}
				}
				$result["$idx"] = $field;
			}
		}
		return $result;
	}

	/**
	 * @param string $fieldName
	 * @return array
	 * @throws Exception
	 */
	private function _loadParameterData($fieldName)
	{
		$fieldName = (string)$fieldName;
		$query = "
			select
				P.ParameterValue_id as id
				,P.ParameterValue_Name as name
				,P.ParameterValue_SysNick as sysnick
				,PT.ParameterValueListType_SysNick as type
			from
				v_ParameterValue P with (NOLOCK)
				left join v_ParameterValueListType PT with(nolock) on P.ParameterValueListType_id = PT.ParameterValueListType_id
			where
				P.ParameterValue_SysNick = :fieldName

		UNION ALL

			select
				VAL.ParameterValue_id as id
				,VAL.ParameterValue_Name as name
				,'parameter'+CAST(VAL.ParameterValue_id AS VARCHAR) as sysnick
				,NULL as type
			from
				v_ParameterValue P with (NOLOCK)
				inner join v_ParameterValue Val with (NOLOCK) on Val.ParameterValue_pid = P.ParameterValue_id
			where
				P.ParameterValue_SysNick = :fieldName
			order by
				P.ParameterValue_id
		";
		$result = $this->db->query($query, array(
			'fieldName'=>$fieldName
		));
		if ( false == is_object($result) ) {
			throw new Exception('Не удалось загрузить список значений и данных параметра');
		}
		$result = $result->result('array');
		if (empty($result)) {
			throw new Exception('Не удалось найти данных параметра');
		}
		return $result;
	}

	/**
	 * Получаем список разделов
	 * @param $data
	 * @return array Стандартный ответ модели
	 * @throws Exception
	 */
	public function loadXmlDataSectionList($data)
	{
		$sql = "
			SELECT
				XmlDataSection_id,
				XmlDataSection_Code,
				XmlDataSection_SysNick,
				XmlDataSection_Name
			FROM
				dbo.v_XmlDataSection with (nolock)
		";
		if (!empty($data['onlyNamedSection'])) {
			$sql .= "
			WHERE
				XmlDataSection_Code > 0
			";
		}
		$result = $this->db->query($sql);
		if ( false == is_object($result) ) {
			throw new Exception('Не удалось загрузить справочник разделов документа');
		}
		$result = $result->result('array');
		if (empty($result)) {
			throw new Exception('Cправочник разделов документа не заполнен.');
		}
		return $result;
	}

	/**
	 * Получаем список типов маркеров документов
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	public function loadXmlMarkerTypeList($data)
	{
		if (!empty($data['EvnClass_id'])) {

		}
		return array(
			array('XmlMarkerType_id'=>1,'XmlMarkerType_Code'=>1,'XmlMarkerType_Name'=>'Маркер списка документов одного типа',),
			array('XmlMarkerType_id'=>2,'XmlMarkerType_Code'=>2,'XmlMarkerType_Name'=>'Маркер одного документа',),
			array('XmlMarkerType_id'=>3,'XmlMarkerType_Code'=>3,'XmlMarkerType_Name'=>'Маркер одного раздела документа',),
			array('XmlMarkerType_id'=>4,'XmlMarkerType_Code'=>10,'XmlMarkerType_Name'=>'Маркер списка протоколов услуг одного типа',),
			array('XmlMarkerType_id'=>5,'XmlMarkerType_Code'=>11,'XmlMarkerType_Name'=>'Маркер одного раздела протоколов услуг одного типа',),
			array('XmlMarkerType_id'=>6,'XmlMarkerType_Code'=>12,'XmlMarkerType_Name'=>'Маркер списка протоколов услуг по коду ГОСТ-2011',),
			array('XmlMarkerType_id'=>7,'XmlMarkerType_Code'=>13,'XmlMarkerType_Name'=>'Маркер одного раздела протоколов услуг по коду ГОСТ-2011',),
		);
	}

	/**
	 * Получаем EvnXml_id протокола последнего посещения в рамках указанного талона
	 * @param array $data
	 * @return array Стандартный ответ модели
	 * @throws Exception
	 */
	public function getLastEvnProtocolId($data)
	{
		$sql = "
			select top 1
				evn.Evn_id,
				evnx.EvnXml_id
			from
				v_Evn evn with (nolock)
				inner join v_EvnXml evnx with (nolock) on evn.Evn_id = evnx.Evn_id
			where
				evn.Evn_rid = ?
				and evn.EvnClass_id = ?
				and evnx.XmlType_id = ?
			order by evn.Evn_insDT desc
		";
		$result = $this->db->query($sql, array($data['Evn_rid'], $data['EvnClass_id'], swEvnXml::EVN_VIZIT_PROTOCOL_TYPE_ID));
		if ( false == is_object($result) ) {
			throw new Exception('Не удалось получить EvnXml_id протокола последнего посещения');
		}
		return $result->result('array');
	}

	/**
	 * Получение данных: какой шаблон и из какой папки используется в документе
	 * @param array $data
	 * @return array Стандартный ответ модели
	 * @throws Exception
	 */
	public function getXmlTemplateInfo($data)
	{
		$this->setScenario('getXmlTemplateInfo');
		$this->setParams($data);
		$this->_validate();
		if ( empty($this->_params['EvnXml_id'])) {
			throw new Exception('Неправильные входящие параметры', 500);
		}
		$params = array('EvnXml_id' => $this->_params['EvnXml_id']);
		$this->load->library('swXmlTemplate');
		$params = array_merge($params,
			swXmlTemplate::getAccessRightsQueryParams($this->sessionParams['lpu_id'], $this->_params['LpuSection_id'], $this->promedUserId)
		);
		$accessType = swXmlTemplate::getAccessRightsQueryPart('xtc', 'XmlTemplateCat', false);
		$accessType0 = swXmlTemplate::getAccessRightsQueryPart('p0', 'XmlTemplateCat', false);
		$accessType1 = swXmlTemplate::getAccessRightsQueryPart('p1', 'XmlTemplateCat', false);
		$accessType2 = swXmlTemplate::getAccessRightsQueryPart('p2', 'XmlTemplateCat', false);
		$accessType3 = swXmlTemplate::getAccessRightsQueryPart('p3', 'XmlTemplateCat', false);
		$accessType4 = swXmlTemplate::getAccessRightsQueryPart('p4', 'XmlTemplateCat', false);
		$accessType5 = swXmlTemplate::getAccessRightsQueryPart('p5', 'XmlTemplateCat', false);
		$accessType6 = swXmlTemplate::getAccessRightsQueryPart('p6', 'XmlTemplateCat', false);
		$sql = "
			SELECT
				xt.XmlTemplate_id
				,xtc.XmlTemplateCat_id
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
			FROM
				dbo.v_EvnXml doc with (nolock)
				inner join dbo.v_XmlTemplate xt with (nolock) on xt.XmlTemplate_id = doc.XmlTemplate_id
				left join dbo.v_XmlTemplateCat xtc with (nolock) on xt.XmlTemplateCat_id = xtc.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p0 with (nolock) on xtc.XmlTemplateCat_pid = p0.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p1 with (nolock) on p0.XmlTemplateCat_pid = p1.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p2 with (nolock) on p1.XmlTemplateCat_pid = p2.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p3 with (nolock) on p2.XmlTemplateCat_pid = p3.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p4 with (nolock) on p3.XmlTemplateCat_pid = p4.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p5 with (nolock) on p4.XmlTemplateCat_pid = p5.XmlTemplateCat_id
				left join dbo.v_XmlTemplateCat p6 with (nolock) on p5.XmlTemplateCat_pid = p6.XmlTemplateCat_id
			WHERE
				EvnXml_id = :EvnXml_id
		";
		$result = $this->db->query($sql, $params);
		if ( false == is_object($result) ) {
			throw new Exception('Не удалось получить информацию о шаблоне');
		}
		return $result->result('array');
	}

	/**
	 * Метод сохранения документа с типом "Протокол лабораторной услуги"
	 * Инкапсулирует логику сохранения документа из заявки
	 * @param EvnLabRequest_model $EvnLabRequest
	 * @return array Стандартный ответ модели
	 */
	function processingEvnLabRequest($data)
	{
		$response = array(array(
			'EvnXml_id' => null,
			'Error_Code' => null,
			'Error_Msg' => null,
		));
		if (empty($data['EvnUslugaPar_oid'])) {
			// это возможно при добавлении заявки без записи,
			// после сохранения заявки заказ создается автоматически,
			// но я пока не разобрался где и как
			$response[0]['Error_Code'] = 400;
			$response[0]['Error_Msg'] = 'Протокол не сохранен, т.к. не указан заказ услуги!
			 <br>Вы можете после сохранения заявки отредактировать результаты исследования,
			 <br>чтобы создать протокол исследования.';
			return $response;
		}
		// выбираем из проб результаты для документа
		$results = array();
		$LabSampleResultList = $data['LabSampleResultList'];
		foreach ($LabSampleResultList as $row) {
			// получаем непосредственно результаты
			if (!empty($row['UslugaTest_Result'])) {
				$row = array_merge($row, json_decode($row['UslugaTest_Result'], true));
			}
			if (
				!empty($row['UslugaComplex_id'])
				&& isset($row['UslugaTest_ResultApproved'])
				&& 2 == $row['UslugaTest_ResultApproved'] // запись одобрена
				&& array_key_exists('UslugaComplex_ACode', $row)
				&& !empty($row['UslugaComplex_Code'])
				&& !empty($row['UslugaComplex_Name'])
				&& array_key_exists('UslugaTest_ResultValue', $row)
				&& array_key_exists('UslugaTest_ResultUnit', $row)
				&& array_key_exists('UslugaTest_ResultUpper', $row)
				&& array_key_exists('UslugaTest_ResultLower', $row)
				&& array_key_exists('UslugaTest_ResultLowerCrit', $row)
				&& array_key_exists('UslugaTest_ResultUpperCrit', $row)
				&& array_key_exists('UslugaTest_Comment', $row)
				&& array_key_exists('EvnLabSample_Comment', $row)
				&& array_key_exists('DefectCauseType_Name', $row)
			) {
				$index = (string) $row['UslugaTest_id'];
				//убираем лишнее
				if (isset($row['UslugaTest_id'])) unset($row['UslugaTest_id']);

				$clr = "#000";
				$addit = "";

				if (!empty($row['UslugaTest_ResultQualitativeNorms'])) {
					$row['UslugaTest_ResultNorm'] = '';
					$row['UslugaTest_ResultCrit'] = '';
					$resp = json_decode($row['UslugaTest_ResultQualitativeNorms'], true);
					if (is_array($resp)) {
						if (!in_array($row['UslugaTest_ResultValue'], $resp)) {
							$clr = "#F00";
						}
						foreach($resp as $norm) {
							if (!empty($row['UslugaTest_ResultNorm'])) {
								$row['UslugaTest_ResultNorm'] .= ', ';
							}
							$row['UslugaTest_ResultNorm'] .= $norm;
						}
					}
				} else {
					$row['UslugaTest_ResultNorm'] = $row['UslugaTest_ResultLower'].' - '.$row['UslugaTest_ResultUpper'];
					$row['UslugaTest_ResultCrit'] = $row['UslugaTest_ResultLowerCrit'].' - '.$row['UslugaTest_ResultUpperCrit'];

					if (isset($row['UslugaTest_ResultLowerCrit']) && floatval(str_replace(",", ".", $row['UslugaTest_ResultValue'])) < floatval(str_replace(",", ".", $row['UslugaTest_ResultLowerCrit']))) {
						$clr = "#F00";
						$addit = "&nbsp;&#x25BC;&#x25BC;";
					} else if (isset($row['UslugaTest_ResultUpperCrit']) && floatval(str_replace(",", ".", $row['UslugaTest_ResultValue'])) > floatval(str_replace(",", ".", $row['UslugaTest_ResultUpperCrit']))) {
						$clr = "#F00";
						$addit = "&nbsp;&#x25B2;&#x25B2;";
					} else if (isset($row['UslugaTest_ResultLower']) && floatval(str_replace(",", ".", $row['UslugaTest_ResultValue'])) < floatval(str_replace(",", ".", $row['UslugaTest_ResultLower']))) {
						$clr = "#F00";
						$addit = "&nbsp;&#x25BC;";
					} else if (isset($row['UslugaTest_ResultUpper']) && floatval(str_replace(",", ".", $row['UslugaTest_ResultValue'])) > floatval(str_replace(",", ".", $row['UslugaTest_ResultUpper']))) {
						$clr = "#F00";
						$addit = "&nbsp;&#x25B2;";
					}
				}

				// в $row['UslugaTest_ResultValue'] запишем выше или ниже нормы

				foreach ($row as $key => $value) $row[$key] = trim(htmlspecialchars($value));

				$row['UslugaTest_ResultValue'] = "<span style='color:{$clr};'>{$row['UslugaTest_ResultValue']}{$addit}</span>";

				$results[htmlspecialchars($index)] = $row;
			}
		}

		if (count($results) > 0) {
			try {
				// ищем документ
				$query = "
					select top 1
						EvnXml_id as EvnXml_id
					from
						v_EvnXml with (nolock)
					where
						Evn_id = :Evn_id
						and XmlType_id = :XmlType_id 
						and XmlTemplate_id is null
				";
				$params = array(
					'Evn_id' => $data['EvnUslugaPar_oid'], // принадлежит только учетному документу EvnUslugaPar с заказом на выполнение лабораторной услуги
					'XmlType_id' => swEvnXml::LAB_USLUGA_PROTOCOL_TYPE_ID,
				);
				$result = $this->db->query($query, $params);
				if ( !is_object($result) )
					throw new Exception('Ошибка запроса данных, необходимых для создания документа',500);
				$tmp = $result->result('array');
				if (!is_array($tmp)) {
					throw new Exception('Ошибка при поиске документа',400);
				}
				//если не создано, то null
				$tmp[0]['EvnXml_id'] = null;

				$response[0]['EvnXml_id'] = $tmp[0]['EvnXml_id'];
				// Загружаем модель
				$this->setParams(array('session' => $data['session']));
				$this->setAttributes(array(
					'EvnXml_id' => $tmp[0]['EvnXml_id'], // создаем или пересоздаем документ
					'Evn_id' => $data['EvnUslugaPar_oid'], // принадлежит только учетному документу EvnUslugaPar с заказом на выполнение лабораторной услуги
					'XmlType_id' => swEvnXml::LAB_USLUGA_PROTOCOL_TYPE_ID, // все документы имеют тип "Протокол лабораторной услуги"
					'EvnXml_Name' => $LabSampleResultList[0]['UslugaComplex_Name'], // Заголовок документа содержит наименование лабораторной услуги из заказа на лабораторное исследование
				));
				// комментарии к пробам (в т.ч. брак)
				$els_comm = array();
				foreach ($LabSampleResultList as $comm) {
					if ( empty($comm['EvnLabSample_Comment']) && empty($comm['DefectCauseType_Name']) ) {
						continue;
					} elseif (!empty($comm['DefectCauseType_Name'])) {
						$els_comm[] = "Брак пробы: {$comm['DefectCauseType_Name']}. {$comm['EvnLabSample_Comment']}";
					} else {
						$els_comm[] = $comm['EvnLabSample_Comment'];
					}
				}
				$els_comm = join('<br>', $els_comm);
				if (empty($this->XmlTemplate_id) || empty($this->htmlTemplate)) {
					$this->load->library('parser');
					// нужен только шаблон отображения
					// получаем массив кодов для генерации строк с маркерами результата
					$acodes = array();
					foreach ($results as $acode => $row) {
						$acodes[] = array('acode'=>$acode);
					}
					$parse_data = array(
						'table_rows'=>$acodes,
						'evn_xml_name'=>$this->name,
						'EvnLabRequest_Comment' => 
							( empty($data['EvnLabRequest_Comment']) ? '' : $data['EvnLabRequest_Comment'] . '<br>' ) .
							( empty($LabSampleResultList[0]['EvnUslugaPar_Comment']) ? '' : $LabSampleResultList[0]['EvnUslugaPar_Comment'] . '<br>' ) .
							( empty($els_comm) ? '' : $els_comm . '<br>' ) 
					);
					// для документа генерируется шаблон отображения на основе базового представления
					$template = $this->parser->parse('lab_diag/base_layout', $parse_data, true);
				} else {
					// @todo В том случае, когда для лабораторной услуги из заказе на лабораторное исследование
					// явно задан шаблон в параметрах
					// или пользователем по умолчанию (в XmlTemplateDefault)
					// или администратором в атрибутах услуги, то для создания документа используется этот шаблон
					$template = $this->htmlTemplate;
				}
				// Вместо HTML-шаблона отображения сохраняется готовый HTML-документ
				$this->load->library('swMarker');
				// обработка маркеров результата
				$html = swMarker::processingTextWithLabMarkers($template, $results);
				// обработка спецмаркерами
				// $options['Lis'] = true => запрос на создание протокола лаб исследования
				$html = swMarker::processingTextWithMarkers($html, $this->Evn_id, ['Lis' => true]);
				$this->setAttribute('htmlTemplate', $html);
				if (empty($this->printSettings)) {
					// берем настройки печати по умолчанию
					$this->load->library('swXmlTemplateSettings');
					$this->setAttribute('printSettings', swXmlTemplateSettings::getJsonFromArr(null));
				}
				if (empty($this->XmlTemplate_id)) {
					// это нормально, поэтому нельзя будет восстановить шаблон
					$this->setAttribute('XmlTemplate_id', null);
				}
				// все документы имеют тип шаблона "Шаблон протокола лабораторной услуги"
				$this->setAttribute('XmlTemplateType_id', swXmlTemplate::LAB_USLUGA_PROTOCOL_TYPE_ID);
				// документ не будет редактироваться, поэтому
				$this->setAttribute('xmlData', ''); // не нужен шаблон формы ввода
				$this->setAttribute('XmlSchema_Data', null); // не нужна схема проверки
				// создаем Xml-документ, содержанием которого является перечень
				// всех результатов собранных из подчиненных заказу услуг.
				$xml =  "<". swXmlTemplate::EVN_XML_DATA_ROOT_ELEMENT .">";
				foreach ($results as $row) {
					$xml .= "<UslugaComplex_id_". $row['UslugaComplex_id'] .">";
					foreach($row as $key => $value) {
						/*// булены
						if ( $value === true )
							$value = 'true';
						if ( $value === false )
							$value = 'false';*/
						if(empty($value)) {
							$xml .= '<' . $key . ' />';
						} else {
							$xml .= '<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>';
						}
					}
					$xml .= "</UslugaComplex_id_". $row['UslugaComplex_id'] .">";
				}
				$xml .= "</". swXmlTemplate::EVN_XML_DATA_ROOT_ELEMENT .">";
				$xml = toAnsi($xml);
				$this->setAttribute('data', $xml);
				// Сохраняем
				$this->setScenario($this->isNewRecord ? 'create' : 'update');
				$this->_beforeSave();
				$tmp = $this->_save();
				$this->setAttribute(self::ID_KEY, $tmp[0][$this->primaryKey()]);
				$this->_afterSave($tmp);
				$response[0]['EvnXml_id'] = $this->id;
				// for debug
				//$response[0]['template'] = $template;
				//$response[0]['results'] = $results;
			} catch (Exception $e) {
				$response[0]['Error_Code'] = $e->getCode();
				$response[0]['Error_Msg'] = $e->getMessage();
				return $response;
			}
		}

		return $response;
	}


	/**
	 * @param $evn_id int
	 * @param $type int XmlMarkerType_Code
	 * @param $markers array
	 * @return array Стандартный ответ модели
	 * @throws Exception Чтобы вывести ошибку, надо выбросить исключение
	 */
	public function buildAndExeQuery($evn_id, $type, $markers, $nestedCount = 0, $EvnXml_id = null)
	{
		$response = array();
		$params = array(
			'Evn_id' => $evn_id,
			'EvnXml_id' => !empty($EvnXml_id)?$EvnXml_id:0,
		);

		switch ($type) {
			case 1: // Маркер списка документов одного типа
			case 2: // Маркер одного документа
				// для маркера нужно вернуть печатную форму одного или нескольких документов
				$query_templ = "
					DECLARE 
						@Evn_id bigint = :Evn_id,
						@EvnXml_id bigint = :EvnXml_id;
					WITH table1 AS (
						SELECT @index as marker,
							Evn.Evn_id,
							Evn.EvnClass_id,
							EvnXml.EvnXml_id,
							EvnXml.XmlType_id,
							EvnXml.XmlTemplateType_id,
							EvnXml_Index,
							XmlTemplateData_id,
							XmlTemplateHtml_id,
							XmlTemplateSettings_id
						FROM
							v_Evn Evn with (nolock)
							cross apply (
								select top 1 *
								from v_EvnXml EvnXml with (nolock)
								where
									Evn.Evn_id = EvnXml.Evn_id 
							) EvnXml
						where
							Evn.Evn_id = @Evn_id and EvnXml.XmlType_id = @XmlType_id and EvnXml.EvnXml_id <> @EvnXml_id
							
						UNION all
						
						SELECT @index as marker,
							Evn.Evn_id,
							Evn.EvnClass_id,
							EvnXml.EvnXml_id,
							EvnXml.XmlType_id,
							EvnXml.XmlTemplateType_id,
							EvnXml_Index,
							XmlTemplateData_id,
							XmlTemplateHtml_id,
							XmlTemplateSettings_id
						FROM
							v_Evn Evn with (nolock)
							inner join v_Evn EvnR with (nolock) on EvnR.Evn_id = Evn.Evn_rid --родитель (если приемное в КВС)
							inner join v_Evn EvnAll with (nolock) on EvnAll.Evn_rid = EvnR.Evn_id -- все события случая
							left join v_EvnSection ES with (nolock) on ES.EvnSection_rid = EvnR.Evn_id and ES.EvnSection_IsPriem = 2 --приёмное (как отдельное движение)
							cross apply (
								select top 1 *
								from v_EvnXml EvnXml with (nolock)
								where
									(1 = 0) --level_filter 
									
							) EvnXml
						where
							Evn.Evn_id = @Evn_id and EvnXml.XmlType_id = @XmlType_id and EvnXml.EvnXml_id <> @EvnXml_id 
							and Evn.Evn_id != EvnXml.Evn_id -- исключаем то, что было в первом запросе
					)

					SELECT 
						table1.marker,
						table1.Evn_id,
						table1.EvnClass_id,
						table1.EvnXml_id,
						table1.XmlType_id,
						EvnXml.EvnXml_Name,
						EvnXml.EvnXml_Data,
						table1.XmlTemplateType_id,
						table1.EvnXml_Index,
						xts.XmlTemplateSettings_Settings as XmlTemplate_Settings,
						xth.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate,
						xtd.XmlTemplateData_Data as XmlTemplate_Data
					FROM table1 
					inner join v_EvnXml AS EvnXml (NOLOCK) ON table1.EvnXml_id = EvnXml.EvnXml_id
					left join XmlTemplateData xtd with (NOLOCK) on xtd.XmlTemplateData_id = table1.XmlTemplateData_id
					left join XmlTemplateHtml xth with (NOLOCK) on xth.XmlTemplateHtml_id = table1.XmlTemplateHtml_id
					left join XmlTemplateSettings xts with (NOLOCK) on xts.XmlTemplateSettings_id = table1.XmlTemplateSettings_id
					left join v_EvnXml CurrEvnXml with (nolock) on CurrEvnXml.EvnXml_id = :EvnXml_id
					where 1=1
					--add_where
				";
				$query_list = array();
				foreach ($markers as $i => $marker) {
					if (isset($marker['XmlDataLevel_SysNick']) && $marker['XmlDataLevel_SysNick'] == 'evn') $level_filter = ' or EvnAll.Evn_id = EvnXml.Evn_id ';
					elseif (isset($marker['XmlDataLevel_SysNick']) && $marker['XmlDataLevel_SysNick'] == 'priem') $level_filter = ' or ES.EvnSection_id = EvnXml.Evn_id or EvnR.Evn_id = EvnXml.Evn_id ';
					else $level_filter = '';
					if ($type == 2) {
						$add_where = '';
						if ('first' == $marker['XmlDataSelectType_SysNick']) {
							$add_where = 'and table1.EvnXml_Index=0';
						}
						if ('last' == $marker['XmlDataSelectType_SysNick']) {
							$add_where = 'and (
								(isnull(CurrEvnXml.XmlType_id, 0) <> EvnXml.XmlType_id and EvnXml.EvnXml_Index = (EvnXml.EvnXml_Count - 1)) or 
								(isnull(CurrEvnXml.XmlType_id, 0) = EvnXml.XmlType_id and EvnXml.EvnXml_Index = (
									case when EvnXml.EvnXml_Index < CurrEvnXml.EvnXml_Index
										then EvnXml.EvnXml_Index - 1 else CurrEvnXml.EvnXml_Index - 1 end
								))
							)';
						}
						$query_list[] = strtr($query_templ, array(
							'@index' => $i,
							'@XmlType_id' => $marker['XmlType_id'],
							'--add_where' => $add_where,
							'--level_filter' => $level_filter,
						));
					} else {
						$query = strtr($query_templ, array(
								'@index' => $i,
								'@XmlType_id' => $marker['XmlType_id'],
								'--add_where' => '',
								'--level_filter' => $level_filter,
							)). ' order by EvnXml.EvnXml_insDT '. $marker['SqlOrderType_SysNick'];
						// echo getDebugSQL($query, $params); exit();
						$result = $this->db->query($query, $params);
						if ( !is_object($result) ) {
							throw new Exception('Ошибка при запросе данных для маркера с типом '.$type);
						}
						$response[$i] = '';
						foreach ($result->result('array') as $row) {
							$response[$i] .= swEvnXml::doPrint(
								$row,
								'',//$data['session']['region']['nick']
								false,
								false,
								true,
								false,
								null,
								$nestedCount
							);
						}
						//var_dump($response); exit();
					}
				}
				if (count($query_list)>0) {
					foreach ($query_list as $query) {
						$result = $this->db->query($query, $params);
						if ( !is_object($result) ) {
							throw new Exception('Ошибка при запросе данных для маркера с типом '.$type);
						}
						foreach ($result->result('array') as $row) {
							$response[$row['marker']] = swEvnXml::doPrint(
								$row,
								'',//$data['session']['region']['nick']
								false,
								false,
								true,
								false,
								null,
								$nestedCount
							);
							$response['map'][$row['marker']] = $row['EvnXml_id'];
						}
					}
				}
				break;
			case 3: // Маркер одного раздела документа
				// для маркера нужно вернуть содержание раздела одного документа
				$query_templ = "
					select * from ( select top 1
					@index as marker,
					EvnXml.EvnXml_id,
					cast(EvnXml.EvnXml_Data.query('data/@XmlDataSection_SysNick/text()') as nvarchar(max)) as value
					from v_Evn Evn with (nolock)
					inner join v_Evn EvnR with (nolock) on EvnR.Evn_id = Evn.Evn_rid
					inner join v_Evn EvnAll with (nolock) on EvnAll.Evn_rid = EvnR.Evn_id
					left join v_EvnSection ES with (nolock) on ES.EvnSection_rid = EvnR.Evn_id and ES.EvnSection_IsPriem = 2
					inner join v_EvnXml EvnXml with (nolock) on Evn.Evn_id = EvnXml.Evn_id --level_filter
					left join v_EvnXml CurrEvnXml with (nolock) on CurrEvnXml.EvnXml_id = :EvnXml_id
					where Evn.Evn_id=:Evn_id and EvnXml.XmlType_id=@XmlType_id
					and (CurrEvnXml.EvnXml_id is null or EvnXml.EvnXml_insDT < CurrEvnXml.EvnXml_insDT)
					--add_where --add_orderby ) as t
				";
				$query_list = array();
				foreach ($markers as $i => $marker) {
					if (isset($marker['XmlDataLevel_SysNick']) && $marker['XmlDataLevel_SysNick'] == 'evn') $level_filter = ' or EvnAll.Evn_id = EvnXml.Evn_id ';
					elseif (isset($marker['XmlDataLevel_SysNick']) && $marker['XmlDataLevel_SysNick'] == 'priem') $level_filter = ' or ES.EvnSection_id = EvnXml.Evn_id or EvnR.Evn_id = EvnXml.Evn_id ';
					else $level_filter = '';
					$add_where = '';
					$add_orderby = '';
					if ('first' == $marker['XmlDataSelectType_SysNick']) {
						//$add_where = 'and EvnXml.EvnXml_Index=0';
						$add_orderby = ' order by EvnXml.EvnXml_Index asc';
					}
					if ('last' == $marker['XmlDataSelectType_SysNick']) {
						//$add_where = 'and EvnXml.EvnXml_Index=(EvnXml_Count-1)';
						$add_orderby = ' order by EvnXml.EvnXml_Index desc';
					}
					if ('firstused' == $marker['XmlDataSelectType_SysNick']) {
						$add_where = " and isnull(cast(EvnXml.EvnXml_Data.query('data/{$marker['XmlDataSection_SysNick']}') as nvarchar(max)), '') != '' ";
						$add_orderby = ' order by EvnXml.EvnXml_Index asc';
					}
					if ('lastused' == $marker['XmlDataSelectType_SysNick']) {
						$add_where = " and isnull(cast(EvnXml.EvnXml_Data.query('data/{$marker['XmlDataSection_SysNick']}') as nvarchar(max)), '') != '' ";
						$add_orderby = ' order by EvnXml.EvnXml_Index desc';
					}
					$query_list[] = strtr($query_templ, array(
						'@index' => $i,
						'@XmlDataSection_SysNick' => $marker['XmlDataSection_SysNick'],
						'@XmlType_id' => $marker['XmlType_id'],
						'--add_where' => $add_where,
						'--add_orderby' => $add_orderby,
						'--level_filter' => $level_filter,
					));
				}
				if (count($query_list) > 0) {
					$query = implode('union all', $query_list);
					// echo getDebugSQL($query, $params); exit();
					$result = $this->db->query($query, $params);
					if ( !is_object($result) ) {
						throw new Exception('Ошибка при запросе данных для маркера с типом '.$type);
					}
					foreach ($result->result('array') as $row) {
						$response[$row['marker']] = htmlspecialchars_decode($row['value']);
						$response['map'][$row['marker']] = $row['EvnXml_id'];
					}
				}
				//var_dump($response); exit();
				break;
			case 10: // Маркер списка протоколов услуг одного типа
			case 12: // Маркер списка протоколов услуг по коду ГОСТ-2011
				$query_templ = "
					with usluga_list as (
						--EvnUslugaList
					)
					select
						EvnXml.Evn_id,
						u.EvnClass_id,
						EvnXml.EvnXml_id,
						EvnXml.XmlType_id,
						EvnXml.EvnXml_Name,
						EvnXml.EvnXml_Data,
						EvnXml.XmlTemplateType_id,
						xts.XmlTemplateSettings_Settings as XmlTemplate_Settings,
						xth.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate,
						xtd.XmlTemplateData_Data as XmlTemplate_Data
					from
						usluga_list u
						cross apply (
							select top 1 EvnXml.*
							from v_EvnXml EvnXml with(nolock)
							where EvnXml.Evn_id = u.EvnUsluga_id
							order by EvnXml_insDT desc
						) EvnXml
						left join XmlTemplateData xtd with (NOLOCK) on xtd.XmlTemplateData_id = EvnXml.XmlTemplateData_id
						left join XmlTemplateHtml xth with (NOLOCK) on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
						left join XmlTemplateSettings xts with (NOLOCK) on xts.XmlTemplateSettings_id = EvnXml.XmlTemplateSettings_id
					order by
						EvnXml.EvnXml_insDT --SqlOrderType_SysNick
				";
				foreach ($markers as $i => $marker) {
					$EvnUslugaList = $this->getEvnUslugaList([
						'EvnUsluga_pid' => $params['Evn_id'],
						'UslugaComplexAttributeType_id' => $marker['UslugaComplexAttributeType_id'],
						'XmlDataLevel_SysNick' => !empty($marker['XmlDataLevel_SysNick'])?$marker['XmlDataLevel_SysNick']:null,
						'code2011list' => !empty($marker['code2011list'])?$marker['code2011list']:null,
					]);
					if (!is_array($EvnUslugaList)) {
						throw new Exception('Ошибка при запросе списка услуг для маркера с типом '.$type);
					}
					if (count($EvnUslugaList) == 0) {
						continue;
					}

					$EvnUslugaList_str = implode(" union\n", array_map(function($EvnUsluga) {
						return "select {$EvnUsluga['EvnUsluga_id']} as EvnUsluga_id, {$EvnUsluga['EvnClass_id']} as EvnClass_id";
					}, $EvnUslugaList));

					$query = strtr($query_templ, array(
						'--SqlOrderType_SysNick' => $marker['SqlOrderType_SysNick'],
						'--EvnUslugaList' => $EvnUslugaList_str,
					));
					//echo getDebugSQL($query, $params);exit;
					$result = $this->db->query($query, $params);
					if ( !is_object($result) ) {
						throw new Exception('Ошибка при запросе данных для маркера с типом '.$type);
					}
					$response[$i] = '';
					foreach ($result->result('array') as $row) {
						$response[$i] .= swEvnXml::doPrint(
							$row,
							'',//$data['session']['region']['nick']
							false,
							false,
							true,
							false,
							null,
							$nestedCount
						);
					}
				}
				break;
			case 11: // Маркер одного раздела протоколов услуг одного типа
			case 13: // Маркер одного раздела протоколов услуг по коду ГОСТ-2011
				$query_templ = "
					with usluga_list as (
						--EvnUslugaList
					)
					select top 1
						cast(doc.EvnXml_Data.query('data/@XmlDataSection_SysNick/text()') as nvarchar(max)) as value
					from 
						usluga_list u
						cross apply (
							select top 1 *
							from v_EvnXml with(nolock)
							where u.EvnUsluga_id = doc.Evn_id
							order by EvnXml_insDT desc
						) doc
					order by
						EvnXml_insDT --SqlOrderType_SysNick
				";
				foreach ($markers as $i => $marker) {
					if (!isset($marker['XmlDataSection_SysNick'])) {
						continue;
					}
					$EvnUslugaList = $this->getEvnUslugaList([
						'EvnUsluga_pid' => $params['Evn_id'],
						'UslugaComplexAttributeType_id' => $marker['UslugaComplexAttributeType_id'],
						'XmlDataLevel_SysNick' => $marker['XmlDataLevel_SysNick'],
						'code2011list' => !empty($marker['code2011list'])?$marker['code2011list']:null,
					]);
					if (!is_array($EvnUslugaList)) {
						throw new Exception('Ошибка при запросе списка услуг для маркера с типом '.$type);
					}
					if (count($EvnUslugaList) == 0) {
						continue;
					}

					$EvnUslugaList_str = implode(" union\n", array_map(function($EvnUsluga) {
						return "select {$EvnUsluga['EvnUsluga_id']} as EvnUsluga_id, {$EvnUsluga['EvnClass_id']} as EvnClass_id";
					}, $EvnUslugaList));

					$query = strtr($query_templ, array(
						'--SqlOrderType_SysNick' => $marker['SqlOrderType_SysNick'],
						'--EvnUslugaList' => $EvnUslugaList_str,
					));
					// echo getDebugSQL($query, $params); exit();
					$result = $this->db->query($query, $params);
					if ( !is_object($result) ) {
						throw new Exception('Ошибка при запросе данных для маркера с типом '.$type);
					}
					$response[$i] = '';
					foreach ($result->result('array') as $row) {
						$response[$i] .= htmlspecialchars_decode($row['value']);
					}
				}
				//var_dump($response); exit();
				break;
		}
		return $response;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getEvnUslugaList($data) {
		$data['EvnUsluga_pids'] = array($data['EvnUsluga_pid']);

		if (!empty($data['XmlDataLevel_SysNick']) && $data['XmlDataLevel_SysNick'] == 'priem') {
			$EvnSection_id = $this->getFirstResultFromQuery("
				select top 1 ES.EvnSection_id
				from v_Evn Evn with (nolock)
				inner join v_EvnSection ES with (nolock) on ES.EvnSection_rid = Evn.Evn_rid and ES.EvnSection_IsPriem = 2
				where Evn.Evn_id = :EvnUsluga_pid
			", $data, true);
			if ($EvnSection_id === false) {
				throw new Exception('Ошибка при получении идентификатора движения в приемное отдлеление');
			}
			if (!empty($EvnSection_id)) {
				$data['EvnUsluga_pids'][] = $EvnSection_id;
			}
		}

		$EvnUslugaList = $this->_getEvnUslugaList($data);
		if (!is_array($EvnUslugaList)) {
			return false;
		}

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$EvnUslugaListLis = $this->lis->get('EvnUsluga/ListForEvnXml', $data, 'list');
			if (!$this->isSuccessful($EvnUslugaListLis)) {
				return false;
			}
			$EvnUslugaList = array_merge($EvnUslugaList, $EvnUslugaListLis);
		}

		return $EvnUslugaList;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function _getEvnUslugaList($data) {
		$params = array(
			'UslugaComplexAttributeType_id' => $data['UslugaComplexAttributeType_id'],
		);

		$EvnUsluga_pids_str = implode(',', $data['EvnUsluga_pids']);

		$add_join = '';
		$add_where = '';
		if (!empty($data['code2011list'])) {
			$code2011list = explode(',', $data['code2011list']);
			foreach ($code2011list as $j => $code) {
				$code = trim($code);
				$code2011list[$j] = "'{$code}'";
			}
			$code2011list = implode(',', $code2011list);
			$add_join = 'left join v_UslugaComplex uc2011 with(nolock) on uc.UslugaComplex_2011id = uc2011.UslugaComplex_id';
			$add_where = 'and uc2011.UslugaComplex_Code in ('.$code2011list.')';
		}

		$query_templ = "
			select
				u.EvnUsluga_id,
				u.EvnClass_id
			from v_EvnUsluga u
			where u.EvnUsluga_pid in ({$EvnUsluga_pids_str})
			and exists (
				select uc.UslugaComplex_id from v_UslugaComplex uc with(nolock)
				inner join v_UslugaComplexAttribute uca with(nolock) on uc.UslugaComplex_id = uca.UslugaComplex_id	
				--add_join
				where uc.UslugaComplex_id = u.UslugaComplex_id
				and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
				--add_where
			)
		";

		$query = strtr($query_templ, array(
			'--add_join' => $add_join,
			'--add_where' => $add_where,
		));

		return $this->queryResult($query, $params);
	}

	/**
	 * Копируем документы старых случаем и прикрепляем к новым
	 *
	 * Логика перенесена из Common::setAnotherPersonForDocument
	 * @param $data
	 * @param $evnLink
	 * @return int
	 * @throws Exception
	 */
	public function onSetAnotherPersonForDocument($data, $evnLink)
	{
		$cntDocuments = 0;
		$tmpArr = array();
		foreach ( $evnLink as $oldEvnId => $newEvnId ) {
			$tmpArr[] = $oldEvnId;
		}
		if (count($tmpArr) > 0) {
			// получаем идешники документов
			$evn_id_list = implode(',', $tmpArr);
			// загружаем данные документов
			$columns = array('pmuser_insid');
			foreach ($this->defAttribute as $key => $info) {
				if (in_array(self::PROPERTY_IS_SP_PARAM, $info['properties'])) {
					$columns[] = $this->_getColumnName($key, $info);
				}
			}
			$columns = implode(',', $columns);
			$sql = "
				SELECT {$columns}
				FROM dbo.v_EvnXml with (nolock)
				WHERE Evn_id in ({$evn_id_list})
			";
			$result = $this->db->query($sql, array());
			if ( is_object($result) ) {
				$tmpArr = $result->result('array');
			} else {
				throw new Exception('Ошибка при получении списка документов', 500);
			}
			$cntDocuments = count($tmpArr);
		}
		foreach ( $tmpArr as $row ) {
			$oldEvnId = $row['evn_id'];
			$oldEvnSid = $row['evn_sid'];
			$row['evn_id'] = $evnLink[$oldEvnId];
			$row['evn_sid'] = isset($evnLink[$oldEvnSid]) ? $evnLink[$oldEvnSid] : null;
			$row['pmuser_id'] = $row['pmuser_insid'];
			unset($row['pmuser_insid']);
			$row[$this->primaryKey()] = array(
				'value' => null,
				'out' => true,
				'type' => 'bigint',
			);
			$response = $this->_save($row);
		}
		return $cntDocuments;
	}

	/**
	 * Загрузка комбо документов для события
	 */
	function loadEvnXmlCombo($data)
	{
		$filter = "";
		if (!empty($data['XmlType_id'])) {
			$filter .= " and XmlType_id = :XmlType_id";
		} 
		elseif (!empty($data['XmlType_ids'])) {
			$filter .= " and XmlType_id in (" . join(',', $data['XmlType_ids']) . ")";
		}

		return $this->queryResult("
			select
				EvnXml_id,
				EvnXml_Name
			from
				v_EvnXml (nolock)
			where
				Evn_id = :Evn_id
				{$filter}
		", $data);
	}

	/**
	 *  Получение списка документов для панели направлений в ЭМК
	 */
	function loadEvnXmlPanel($data)
	{
		$filter = "";
		if (!empty($data['XmlType_id'])) {
			$filter .= " and ex.XmlType_id = :XmlType_id";
		}
		elseif (!empty($data['XmlType_ids'])) {
			$filter .= " and ex.XmlType_id in (" . join(',', $data['XmlType_ids']) . ")";
		}

		return $this->queryResult("
			select
				ex.EvnXml_id,
				ex.EvnXml_Name,
				convert(varchar(10), ex.EvnXml_insDT, 104) as EvnXml_Date,
				convert(varchar(5), ex.EvnXml_insDT, 108) as EvnXml_Time,
				ISNULL(pu.pmUser_Name, '') as pmUser_Name,
				ex.XmlTemplate_id,
				ex.EvnXml_IsSigned
			from
				v_EvnXml ex (nolock)
				left join v_pmUser pu with (NOLOCK) on pu.pmUser_id = ex.pmUser_insID
			where
				ex.Evn_id = :Evn_id
				{$filter}
		", $data);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadEvnXmlList($data) {
		$params = array();
		$where = "";

		if (!empty($data['Evn_id'])) {
			$where .= "EX.Evn_id = :Evn_id";
		}
		if (!empty($data['Evn_ids'])) {
			$ids_str = implode(",", $data['Evn_ids']);
			$where .= "EX.Evn_id in ({$ids_str})";
		}

		$query = "
			select
				EX.Evn_id,
				EX.EvnXml_id
			from
				v_EvnXml EX with(nolock)
			where
				{$where}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка документов в событии. Метод для API
	 */
	function loadEvnXmlListForAPI($data) {
		$filter = "";
		$params = array('Evn_id' => $data['Evn_id']);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and E.Lpu_id = :Lpu_id";
		}
		$query = "
			declare @Evn_id bigint = :Evn_id
			select
				EX.Evn_id,
				EX.EvnXml_id,
				EX.XmlType_id,
				EX.XmlTemplate_id
			from
				v_EvnXml EX with(nolock)
				inner join v_Evn E with(nolock) on E.Evn_id = EX.Evn_id
			where
				(E.Evn_id = @Evn_id or E.Evn_pid = @Evn_id or E.Evn_rid = @Evn_id)
				{$filter}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных документа. Метод для API
	 */
	function getEvnXmlForAPI($data) {
		$params = array();
		$filters = array();

		if (!empty($data['Evn_id'])) {
			$filters[] = "EX.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}
		if (!empty($data['EvnXml_id'])) {
			$filters[] = "EX.EvnXml_id = :EvnXml_id";
			$params['EvnXml_id'] = $data['EvnXml_id'];
		}
		if (!empty($data['XmlType_id'])) {
			$filters[] = "EX.XmlType_id = :XmlType_id";
			$params['XmlType_id'] = $data['XmlType_id'];
		}
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filters[] = "E.Lpu_id = :Lpu_id";
		}

		if (count($filters) == 0) {
			return $this->createError('','Не было передано ни одного параметра');
		}

		$filters_str = implode(" and ", $filters);
		$query = "
			select
				EX.Evn_id,
				EX.EvnXml_id,
				EX.XmlType_id,
				EX.EvnXml_Data,
				XTH.XmlTemplateHtml_HtmlTemplate,
				EX.XmlTemplate_id
			from
				v_EvnXml EX with(nolock)
				inner join v_Evn E with(nolock) on E.Evn_id = EX.Evn_id
				left join v_XmlTemplateHtml XTH with(nolock) on XTH.XmlTemplateHtml_id = EX.XmlTemplateHtml_id
			where
				{$filters_str}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Метод для создания связи между направлением и документом в таблице EvnXmlDirectionLink
	 *
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function createEvnXmlDirectionLink($data)
	{
		$query = "
			SELECT TOP 1
				0 as ValidationError
			FROM
				v_EvnXmlDirectionLink (nolock)
			WHERE
				EvnDirection_id = :EvnDirection_id AND EvnXml_id = :EvnXml_id

			UNION 
			
			SELECT TOP 1
				1 as ValidationError
			FROM
				v_EvnDirection_all ED (nolock)
			WHERE
				ED.EvnDirection_id = :EvnDirection_id AND ED.DirType_id <> 17
				
		";

		$validationMessages = array('Данный документ уже прикреплен к этому направлению', 'Документ можно привязать только к направлению с типом "На удаленную консультацию"');

		$result = $this->queryList($query, $data);

		if ( ! empty($result) )
		{
			$errorNum = in_array(1, $result) ? 1 : 0;
			throw new Exception($validationMessages[$errorNum]);
}

		$query = "
			declare
				@EvnXmlDirectionLink_id BIGINT = null,
				@Error_Code INT,
				@Error_Message VARCHAR(4000);
			exec p_EvnXmlDirectionLink_ins 
				@EvnXmlDirectionLink_id = @EvnXmlDirectionLink_id OUTPUT,
				@EvnXml_id = :EvnXml_id,
				@EvnDirection_id = :EvnDirection_id,
				@pmUser_id = :pmUser_id,
				
				@Error_Code = @Error_Code OUTPUT,
				@Error_Message = @Error_Message OUTPUT;
				
			select @EvnXmlDirectionLink_id as EvnXmlDirectionLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$response = $this->getFirstRowFromQuery($query, $data);

		return $response;
	}

	/**
	 * Метод для удаления связи между направлением и документом в таблице EvnXmlDirectionLink
	 *
	 * @param $data
	 * @return array|bool
	 */
	function deleteEvnXmlDirectionLink($data)
	{
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnXmlDirectionLink_del
				@EvnXmlDirectionLink_id = :EvnXmlDirectionLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->getFirstRowFromQuery($query, $data);

		return $result;
	}

	/**
	 * Получение данных всех документов, прикрепленных к направлению
	 */
	function getEvnXmlForEvnDirectionList($EvnDirection_id)
	{
		if ( empty($EvnDirection_id) )
		{
			return array();
		}

		$query = "
			select
				EXDL.EvnXmlDirectionLink_id,
				ED.EvnDirection_id,
				EvnXml.Evn_id as EvnXml_pid,
				Evn.Evn_pid,
				Evn.Evn_rid,
				Evn.EvnClass_id,
				Evn.EvnClass_SysNick,
				EvnXml.XmlType_id,
				EvnXml.EvnXml_id,
				EvnXml.EvnXml_Name,
				EvnXml.EvnXml_Data,
				xts.XmlTemplateSettings_Settings as XmlTemplate_Settings,
				xth.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate,
				xtd.XmlTemplateData_Data as XmlTemplate_Data,
				convert(varchar, EvnXml.EvnXml_insDT, 104) as EvnXml_Date,
				EvnXml.pmUser_insID,
				RTRIM(LTRIM(ISNULL(pmUserCache.pmUser_Name, ''))) as pmUser_Name,
				0 as frame,
				1 as readOnly
			from
				v_EvnXmlDirectionLink EXDL with (nolock)
			INNER JOIN v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EXDL.EvnDirection_id
			inner join v_EvnXml EvnXml with (NOLOCK) on EvnXml.EvnXml_id = EXDL.EvnXml_id 
			inner join v_Evn Evn with (NOLOCK) on Evn.Evn_id = EvnXml.Evn_id
			left join pmUserCache with (NOLOCK) on pmUserCache.pmUser_id = EvnXml.pmUser_insID
			left join XmlTemplateData xtd with (NOLOCK) on xtd.XmlTemplateData_id = EvnXml.XmlTemplateData_id
			left join XmlTemplateHtml xth with (NOLOCK) on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
			left join XmlTemplateSettings xts with (NOLOCK) on xts.XmlTemplateSettings_id = EvnXml.XmlTemplateSettings_id
			
			where
				 EXDL.EvnDirection_id = :EvnDirection_id AND
				 ED.DirType_id = 17
			order by EvnXml.EvnXml_insDT
		";

		$result = $this->queryResult($query, array('EvnDirection_id' => $EvnDirection_id));

		return $result;
	}

	/**
	 * Удаление протокола
	*/
	function deleteByEvn($data) {
		$params = array(
			'Evn_id' => $data['Evn_id']
		);

		$query = "
			select
				EvnXml_id
			from
				v_EvnXml (nolock)
			where
				Evn_id = :Evn_id
				and XmlTemplate_id is null
		";

		$EvnXmlList = $this->queryResult($query, $params);
		if (!is_array($EvnXmlList)) {
			return $this->createError('','Ошибка при поиске документов события');
		}

		$query = "
			declare
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			exec p_EvnXml_del
				@EvnXml_id = :EvnXml_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$this->beginTransaction();

		foreach($EvnXmlList as $EvnXml) {
			$resp = $this->queryResult($query, $EvnXml);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();

		return array(array(
			'success' => true
		));
	}

	/**
	 * Логика после успешного выполнения запроса удаления объекта внутри транзакции
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterDelete($result)
	{
		parent::_afterDelete($result);

		$this->load->model('ApprovalList_model');
		$this->ApprovalList_model->deleteApprovalList(array(
			'ApprovalList_ObjectName' => 'EvnXml',
			'ApprovalList_ObjectId' => $this->id
		));
	}
	
	function getEvnXmlByEvnVizitPL($data) {
		$query = "
			select top 1 
				EX.EvnXml_id
			from 
				v_EvnXml EX (nolock)
			where 
				EX.Evn_id = :EvnVizitPL_id and
				XmlType_id = 3
		";
		return $this->dbmodel->getFirstResultFromQuery($query, $data);
	}
}
