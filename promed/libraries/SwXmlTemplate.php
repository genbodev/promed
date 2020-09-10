<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		private
 * @copyright	Copyright (c) 2009-2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		07.2013
 */

/**
 * Вспомогательная библиотека для работы с XML-шаблонами и XML-преобразованиями
 *
 * @package		XmlTemplate
 * @author		Alexander Permyakov
 */
class swXmlTemplate
{
	/**
	 * Идентификатор типа "XML-шаблон формы ввода и HTML-шаблон отображения"
	 */
	const OLD_TYPE_ID = 1;
	/**
	 * Идентификатор типа "HTML-шаблон без разметки областей ввода данных"
	 */
	const OLD_SIMPLE_TYPE_ID = 2;
	/**
	 * Идентификатор типа "HTML-шаблон с разметкой областей ввода данных"
	 */
	const OLD_MULTIPLE_PART_TYPE_ID = 3;
	/**
	 * Идентификатор типа "HTML-шаблон комплексной услуги"
	 */
	const OLD_EVN_USLUGA_TYPE_ID = 4;
	/**
	 * Идентификатор типа "Документ в свободной форме"
	 */
	const OLD_FREE_TYPE_ID = 5;
	/**
	 * Идентификатор типа "Шаблон документов с множеством разделов"
	 */
	const MULTIPLE_PART_TYPE_ID = 6;
	/**
	 * Идентификатор типа "Шаблон протоколов услуг", частный случай шаблона документов с множеством разделов
	 */
	const EVN_USLUGA_TYPE_ID  = 7;
	/**
	 * Идентификатор типа "Шаблон плана назначений"
	 */
	const EVN_PRESCR_PLAN_TYPE_ID  = 8;
	/**
	 * Идентификатор типа "Шаблон протокола лабораторной услуги"
	 */
	const LAB_USLUGA_PROTOCOL_TYPE_ID  = 9;

	/**
	 *
	 */
	const EVN_XML_DATA_ROOT_ELEMENT = 'data';

	/**
	 * @var array
	 */
	private static $_xml_data_cache = array();

	private static $ci_instance;

	private static $foundDataTag_result_arr = array();

	private static $foundDataTag_option_arr = array(
		'use_stripslashes' => true,
		'use_htmlspecialchars_decode' => true,
		'create_datatag_list' => true,
		'return_template' => true
	);

	/**
	 * @return CI_Controller
	 */
	private static function getCiInstance() {
		if (isset(self::$ci_instance)) {
			return self::$ci_instance;
		}
		self::$ci_instance =& get_instance();
		return self::$ci_instance;
	}

	/**
	 * Получение значения по умолчанию из Xml-шаблона
	 * @param array $child_node_list utf-8
	 * @return string
	 */
	private static function getDefaultValue($child_node_list)
	{
		foreach ( $child_node_list as $child_node )
		{
			if ($child_node->nodeName == 'defaultValue')
				return $child_node->nodeValue;
		}
		return '';
	}

	/**
	 * Преобразование HTML-шаблона к виду для просмотра или редактирования
	 * @param string $html_template Строка в кодировке utf-8
	 * @param string $xml_template Строка в кодировке utf-8
	 * @return string Строка в кодировке utf-8
	 */
	public static function restoreDataTagAndContent($html_template,$xml_template)
	{
		if(empty($html_template) || empty($xml_template))
		{
			return '';
		}

		$data_tags = array();
		$tpl = new DOMDocument;
		$tpl->loadXML($xml_template);
		$node_list = $tpl->getElementsByTagName ('arrayNode');
		foreach ( $node_list as $node )
		{
			$child_node_list = $node->childNodes;
			foreach ( $child_node_list as $child_node )
			{
				if ($child_node->nodeName == 'name')
					$data_tags[$child_node->nodeValue] = self::getDefaultValue($child_node_list);
			}
		}
		//var_dump($data_tags);
		return self::parseHtmlTemplate($html_template, $data_tags);
	}

	/**
	 * Преобразование HTML-шаблона к виду для печати или редактирования или предпросмотра
	 * @param array $data_tags Массив в кодировке utf-8
	 * @param string $html_template в кодировке utf-8
	 * @return string Строка в кодировке utf-8
	 */
	private static function parseHtmlTemplate($html_template, $data_tags) {
		$search = array();
		$replace = array();
		foreach($data_tags as $k => $v)
		{
			$search[] = '{'.$k.'}';
			$replace[] = '<data class="data" id="'.$k.'">'.$v.'</data>';
		}
		$html_template = str_replace($search, $replace, $html_template);
		return $html_template;
	}

	/**
	 * Преобразование HTML-шаблона к виду для печати или редактирования или предпросмотра
	 * @param array $xml_template Массив атрибутов шаблона в кодировке utf-8, полученный методом XmlTemplateBase_model::doLoadEditForm()
	 * @param bool $is_for_print Признак, что шаблон нужен для предпросмотра или печати
	 * @return string Строка в кодировке utf-8
	 * @throws Exception
	 */
	public static function getHtmlTemplate(&$xml_template, $is_for_print = false) {
		if (empty($xml_template['XmlTemplate_Data'])) {
			throw new Exception('Шаблон разделов документа отсутствует', 500);
		}
		switch(true)
		{
			case ($xml_template['XmlTemplateType_id'] == self::EVN_USLUGA_TYPE_ID):
			case ($xml_template['XmlTemplateType_id'] == self::LAB_USLUGA_PROTOCOL_TYPE_ID):
				// Шаблон протоколов услуг, частный случай шаблона документов с множеством разделов
			case ($xml_template['XmlTemplateType_id'] == self::MULTIPLE_PART_TYPE_ID):
				// Шаблон документов с множеством разделов
				// это XML-шаблон формы ввода (XmlTemplate_Data) и HTML-шаблон отображения (XmlTemplate_HtmlTemplate), Редактируется HTML-шаблон отображения
				if (empty($xml_template['XmlTemplate_HtmlTemplate'])) {
					throw new Exception('HTML-шаблон отображения отсутствует', 404);
				}
				$html = $xml_template['XmlTemplate_HtmlTemplate'];

				$xml_data_arr = self::getXmlTemplateValues($xml_template['XmlTemplate_Data']);

				self::getCiInstance()->load->library('swMarker');
				$html = swMarker::createInputBlocks($html, $xml_data_arr, $xml_template, $is_for_print);

				if (!$is_for_print) {
					$html = self::restoreDataTagAndContent($html,$xml_template['XmlTemplate_Data']);
				}
				break;

			// конвертируем старые типы шаблонов в новый формат,
			// если загружаем для редактирования шаблона
			// После редактирования шаблон будет сохранен в новом формате
			case ($xml_template['XmlTemplateType_id'] == self::OLD_TYPE_ID && !empty($xml_template['XmlTemplate_HtmlTemplate'])):
				$html = $xml_template['XmlTemplate_HtmlTemplate'];
				if (!$is_for_print) {
					$html = self::restoreDataTagAndContent($html,$xml_template['XmlTemplate_Data']);
					$xml_template['XmlTemplateType_id'] = self::MULTIPLE_PART_TYPE_ID;
				}
				break;
			case ($xml_template['XmlTemplateType_id'] == self::OLD_SIMPLE_TYPE_ID):
				// извлекаем HTML из defaultValue поля с именем UserTemplateData
				$html = self::extractUserTemplateData($xml_template['XmlTemplate_Data']);
				if (!$is_for_print) {
					// конвертируем шаблон документа с одним разделов в формат шаблона с множеством разделов
					// создаем область для ввода данных с именем autoname1 и помещаем туда этот HTML
					$html_template = '<br><div><span style="font-weight: bold; font-size:10px;">Осмотр: </span> <div class="template-block" id="block_autoname1">  <div class="template-block-data" id="data_autoname1">   {autoname1}</div> </div></div><br>';
					$html = self::parseHtmlTemplate($html_template, array('autoname1' => $html));
					$xml_template['XmlTemplateType_id'] = self::MULTIPLE_PART_TYPE_ID;
				}
				break;
			case ($xml_template['XmlTemplateType_id'] == self::OLD_MULTIPLE_PART_TYPE_ID):
				// нужно конвертировать, используя содержимое тегов разметки областей ввода данных
				$html = self::extractUserTemplateData($xml_template['XmlTemplate_Data']);
				if (!$is_for_print) {
					$xml_template['XmlTemplateType_id'] = self::MULTIPLE_PART_TYPE_ID;
				}
				break;
			case ($xml_template['XmlTemplateType_id'] == self::OLD_EVN_USLUGA_TYPE_ID):
				// нужно конвертировать, используя содержимое тегов разметки областей ввода данных
				$html = self::extractUserTemplateData($xml_template['XmlTemplate_Data']);
				if (!$is_for_print) {
					$xml_template['XmlTemplateType_id'] = self::EVN_USLUGA_TYPE_ID;
				}
				break;
			default:
				throw new Exception('Эта функция не реализована для шаблонов данного типа', 501);
				break;
		}
		return $html;
	}

	/**
	 * Создание содержания документа из значений по умолчанию в шаблоне
	 * @param string $xml_template_data Строка в кодировке utf-8
	 * @param string $name Строка в кодировке utf-8
	 * @return string Строка в кодировке utf-8
	 * @throws Exception
	 */
	public static function getDefaultValueByName($xml_template_data, $name) {
		if (empty($xml_template_data)) {
			throw new Exception('XML-шаблон пуст', 500);
		}
		$tpl = new DOMDocument;
		$tpl->loadXML($xml_template_data);
		$node_list = $tpl->getElementsByTagName ('arrayNode');
		$value = '';
		foreach ( $node_list as $node )
		{
			$child_node_list = $node->childNodes;
			foreach ( $child_node_list as $child_node )
			{
				if ($child_node->nodeName == 'name' && $child_node->nodeValue == $name)
				{
					$value = self::getDefaultValue($child_node_list);
					if(trim($value) == '-') {
						$value = '';
					}
					break 2;
				}
			}
		}
		return $value;
	}

	/**
	 * Создание содержания документа из значений по умолчанию в шаблоне
	 * @param string $xml_template_data Строка в кодировке utf-8
	 * @param string $xml_schema_data Строка в кодировке utf-8
	 * @param bool $isHTML Флаг, что содержание раздела может содержать HTML-разметку
	 * @return array Значения в кодировке utf-8
	 * @throws Exception
	 */
	public static function createEvnXmlDataArray($xml_template_data, $xml_schema_data = null, $isHTML = true) {
		if (empty($xml_template_data)) {
			throw new Exception('XML-шаблон пуст', 500);
		}
		if (!empty($xml_schema_data)) {
			//
		}
		$tpl = new DOMDocument;
		$tpl->loadXML($xml_template_data);
		$node_list = $tpl->getElementsByTagName ('arrayNode');
		$evn_data_arr = array();
		foreach ( $node_list as $node )
		{
			$child_node_list = $node->childNodes;
			foreach ( $child_node_list as $child_node )
			{
				if ($child_node->nodeName == 'name')
				{
					if($isHTML) {
						$value = self::getDefaultValue($child_node_list);
					} else {
						$value = strip_tags(self::getDefaultValue($child_node_list));
					}
					if(trim($value) == '-')
						$value = '';
					$evn_data_arr[$child_node->nodeValue] = $value;
				}
			}
		}
		return $evn_data_arr;
	}

	/**
	 * Создание содержания документа из значений по умолчанию в шаблоне
	 * @param string $xml_template_data Строка в кодировке utf-8
	 * @param string $xml_schema_data Строка в кодировке utf-8
	 * @param bool $isHTML Флаг, что содержание раздела может содержать HTML-разметку
	 * @return string Строка в кодировке utf-8
	 * @throws Exception
	 */
	public static function createEvnXmlData($xml_template_data, $xml_schema_data = null, $isHTML = true)
	{
		$evn_data_arr = self::createEvnXmlDataArray($xml_template_data, $xml_schema_data, $isHTML);
		$evn_data_xml = self::convertFormDataArrayToXml(array($evn_data_arr));
		if (empty($xml_schema_data) && $xml_schema_data !== false) {
			$template_field_data = array();
			foreach ($evn_data_arr as $key => $value) {
				$template_field_data[] = array('id'=>$key);
			}
			$xml_schema_data = self::createXmlSchemaData($template_field_data);
		}
		// проверяем правильность данных по схеме
		$xml = new DOMDocument();
		@$xml->loadXML($evn_data_xml);
		if (!empty($xml_schema_data)) {
			$res = @$xml->schemaValidateSource($xml_schema_data);
			if ( !$res ) {
				// или схема содержит не уникальные имена разделов
				// или документ содержит спецсимволы, которые не были обработаны
				// var_dump($evn_data_arr);
				// var_dump(toAnsi($xml_schema_data));
				throw new Exception('XML-данные нового документа не прошли проверку по схеме!', 500);
			}
		}
		//дополнительная обработка
		return str_replace(array('<br>', '<br/>'), PHP_EOL, $evn_data_xml);
	}

	/**
	 * @param string $xml_template_data
	 * @return array
	 * @throws Exception
	 */
	public static function getXmlTemplateLabels($xml_template_data) {
		if (empty($xml_template_data)) {
			return array();
		}
		$tpl = new DOMDocument;
		$tpl->loadXML($xml_template_data);
		$node_list = $tpl->getElementsByTagName('arrayNode');
		$response = array();
		foreach($node_list as $node) {
			$name_list = $node->getElementsByTagName('name');
			$label_list = $node->getElementsByTagName('fieldLabel');
			foreach($name_list as $name_one) {
				$name = $name_one->nodeValue;
			}
			foreach($label_list as $label_one) {
				$label = $label_one->nodeValue;
			}
			if (!empty($name) && !empty($label)) {
				$response[$name] = $label;
			}
		}
		return $response;
	}

	/**
	 * @param string $xml_template_data
	 * @return array
	 * @throws Exception
	 */
	public static function getXmlTemplateSettings($xml_template_data) {
		if (empty($xml_template_data)) {
			return array();
		}
		$tpl = new DOMDocument;
		$tpl->loadXML($xml_template_data);
		$node_list = $tpl->getElementsByTagName('arrayNode');
		$response = array();
		$tags = array(
			'name', 'xtype', 'fieldLabel'
		);
		foreach($node_list as $node) {
			$tmp = array();
			foreach($tags as $tag) {
				foreach($node->getElementsByTagName($tag) as $tagValue) {
					$tmp[$tag] = $tagValue->nodeValue;
				}
			}
			if (!empty($tmp['name'])) {
				$response[$tmp['name']] = $tmp;
			}
		}
		return $response;
	}

	/**
	 * @param string $xml_template_data
	 * @return array
	 * @throws Exception
	 */
	public static function getXmlTemplateValues($xml_template_data) {
		if (empty($xml_template_data)) {
			throw new Exception('XML-шаблон пуст', 500);
		}
		$tpl = new DOMDocument;
		$tpl->loadXML($xml_template_data);
		$node_list = $tpl->getElementsByTagName('arrayNode');
		$response = array();
		foreach($node_list as $node) {
			$name_list = $node->getElementsByTagName('name');
			$value_list = $node->getElementsByTagName('defaultValue');

			$name = null;
			foreach($name_list as $name_one) {
				$name = $name_one->nodeValue;
			}

			if (!empty($name)) {
				$value = '';
				foreach($value_list as $value_one) {
					$value = htmlspecialchars(stripslashes($value_one->nodeValue), ENT_COMPAT, 'UTF-8');
				}
				$response[$name] = $value;
			}
		}
		return $response;
	}

	/**
	 * Преобразует массив данных формы в XML
	 *
	 * @param array $arr Массив с XML-объектами в кодировке utf-8
	 * @param string $rootElem Строка в кодировке utf-8
	 * @return string Строка в кодировке utf-8
	 * @throws Exception
	 */
	public static function convertFormDataArrayToXml($arr, $rootElem = self::EVN_XML_DATA_ROOT_ELEMENT )
	{
		$xml = ( $rootElem == '' ) ? "" : "<". $rootElem .">";
		if ( is_array($arr) )
		{
			for ( $i=0; $i < count($arr); $i++ )
			{
				if (isset($arr[$i]->UserTemplateData))
				{
					throw new Exception('Эта функция не реализована для шаблонов данного типа', 501);
				}
				$xml .= self::processingKeyValueList($arr[$i]);
			}
		}
		$xml .= ( $rootElem == '' ) ? "" : "</". $rootElem .">";
		return $xml;
	}

	/**
	 * Создание узлов XML из ассоциативного массива
	 * fromNodesArrayToXmlNodes
	 * @param array $arr Массив пар "имя раздела - содержание раздела" в кодировке utf-8
	 * @return string Строка в кодировке utf-8
	 */
	public static function processingKeyValueList($arr)
	{
		$xml = '';
		foreach($arr as $key => $value)
		{
			// булены
			if ( $value === true )
				$value = 'true';
			if ( $value === false )
				$value = 'false';
			// To-Do можно в схеме определить тип данных строка с кодом HTML, чтобы не использовать htmlentities() и данные успешно проходили проверку по схеме
			if(empty($value)) {
				$xml .= '<' . $key . ' />';
			} else {
				//$xml .= '<' . $key . '>' . htmlentities($value,ENT_NOQUOTES,'UTF-8') . '</' . $key . '>';
				$xml .= '<' . $key . '>' . htmlspecialchars($value,  ENT_COMPAT, 'UTF-8') . '</' . $key . '>';
			}
		}
		return $xml;
	}

	/**
	 * Заменяет некоторые html-мнемоники, из-за которых выходит ошибка "Данные шаблона не прошли проверку по схеме"
	 * htmlEntitiesReplace
	 * @see http://housecomputer.ru/programming/html/mnemonics.html
	 * @param string Строка в кодировке $str utf-8
	 * @return string Строка в кодировке utf-8
	 */
	public static function replaceHtmlEntities($str)
	{
		return str_replace(
			array(
				'&amp;nbsp;',
				'&nbsp;',
				'&amp;laquo;',
				'&laquo;',
				'&amp;raquo;',
				'&raquo;',
				'&ndash;',
				'&amp;reg;',
				'&reg;',
			),
			array(
				'&#160;',
				'&#160;',
				'&#171;',
				'&#171;',
				'&#187;',
				'&#187;',
				'&#8211;',
				'&#174;',
				'&#174;'
			),
			$str
		);
	}

	/**
	 * Обновляет содержание разделов документа
	 * 
	 * @param string $evn_xml_data Строка в кодировке utf-8
	 * @param string $xml_schema_data Строка в кодировке utf-8
	 * @param array $data Массив новых значений в кодировке utf-8 с именами разделов в качестве ключей
	 * @param bool $isHTML Флаг, что содержание раздела может содержать HTML-разметку
	 * @return string Строка в кодировке utf-8
	 * @throws Exception
	 */
	public static function updateEvnXmlData($evn_xml_data, $xml_schema_data = null, $data, $isHTML) {
		$evn_data_arr = self::transformEvnXmlDataToArr($evn_xml_data);
		foreach($data as $k=>$v)
		{
			if ( false == self::replaceSectionContent($evn_data_arr, $k, $v, $isHTML) )
			{
				//throw new Exception('Данных с указанным именем в документе нет!', 400);
				continue;
			}
		}

		$evn_xml_data = self::convertFormDataArrayToXml(array($evn_data_arr));
		//$evn_xml_data = self::replaceHtmlEntities($evn_xml_data);

		//Убрал проверку соответствия документа схеме https://redmine.swan.perm.ru/issues/145423#note-20
		/*if (empty($xml_schema_data)) {
			$template_field_data = array();
			foreach ($evn_data_arr as $key => $value) {
				$template_field_data[] = array('id'=>$key);
			}
			$xml_schema_data = self::createXmlSchemaData($template_field_data);
		}
		// проверяем правильность данных по схеме
		$xml = new DOMDocument();
		@$xml->loadXML($evn_xml_data);
		$res = @$xml->schemaValidateSource($xml_schema_data);
		if ( !$res )
		{
			// или схема содержит не уникальные имена разделов
			// или документ содержит спецсимволы, которые не были обработаны
			// var_dump(toAnsi($evn_xml_data));
			//var_dump(toAnsi($xml_schema_data));
			throw new Exception('XML-данные обновленного документа не прошли проверку по схеме!', 500);
		}*/

		return $evn_xml_data;
	}

	/**
	 * Вспомогательная функция для преобразования $evn_xml_data
	 * Извлекает из $evn_xml_data контент по имени узла
	 * Предполагается подобный формат $evn_xml_data: <data><anamnes>Без особенностей</anamnes><obstatus>&lt;u&gt; &lt;/u&gt;Все показатели в норме</obstatus><diagnos> здоров</diagnos></data>
	 * $is_for_emk Признак, что данные нужны для ЭМК
	 *
	 * @param string $evn_xml_data Строка в кодировке utf-8
	 * @return array
	 */
	public static function transformEvnXmlDataToArr($evn_xml_data, $is_for_emk = false)
	{
		self::getCiInstance()->load->helper('xml');
		self::$_xml_data_cache = array();
		$doc = new DOMDocument();
		@$doc->loadXML($evn_xml_data);
		if (empty($doc->documentElement)) {
			return array();
		}
		foreach($doc->documentElement->childNodes as $node)
		{
			if($node->nodeName == '#text')
				continue;
			self::$_xml_data_cache[$node->nodeName] = str_replace("	", '', $node->nodeValue);
		}
		return self::$_xml_data_cache;
	}

	/**
	 * Вспомогательная функция для обновления разделов документа
	 *
	 * @param array $arr Массив значений в кодировке utf-8 с именами разделов в качестве ключей
	 * @param string $k Ключ (имя раздела) в кодировке utf-8
	 * @param string $v Значение в кодировке utf-8
	 * @param bool $isHTML
	 * @return bool
	 */
	private static function replaceSectionContent(&$arr, $k, $v, $isHTML) {
		if ( !isset($arr[$k]) )
			return false;
		if(empty($v)) $v = '';
		if ($isHTML) {
			$v = swEvnXml::cleaningHtml($v, array(
				'commentWithoutTag' => 1,
				'commentWithoutExclamation' => 1,
				'commentWithIf' => 1,
				//'styles' => 1,
				'styleMso' => 1,
				'userLocalFiles' => 1,
			)); // #52118
			$arr[$k] = $v;
		} else {
			$arr[$k] = strip_tags($v);
		}
		return true;
	}

	/**
	 * Генерирует XML-схему разделов документа
	 * $template_field_data - данные полученные методами getXmlTemplateFieldData
	 * @param array $template_field_data Конфигурация разделов документа в кодировке utf-8
	 * @param string $rootElem Строка в кодировке utf-8
	 * @return string Строка в кодировке utf-8
	 * @throws Exception
	 */
	public static function createXmlSchemaData($template_field_data, $rootElem = self::EVN_XML_DATA_ROOT_ELEMENT)
	{
		$xmlSchema = '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">';
		$xmlSchema .= ( $rootElem == '' ) ? '' : '
  <xsd:element name="'. $rootElem .'">
    <xsd:annotation>
      <xsd:documentation>Исходный документ</xsd:documentation>
    </xsd:annotation>
    <xsd:complexType>
      <xsd:all>';
		if(count($template_field_data) > 0)
		{
			foreach($template_field_data as $row)
			{
				$xmlSchema .= '
        <xsd:element name="'. $row['id'] .'" type="xsd:string">
          <xsd:annotation>
            <xsd:documentation />
          </xsd:annotation>
        </xsd:element>';
			}
		}
		$xmlSchema .= ( $rootElem == '' ) ? '' : '
      </xsd:all>
    </xsd:complexType>
  </xsd:element>';
		$xmlSchema .= '
</xsd:schema>';
		return $xmlSchema;
	}

	/**
	 * Генерирует XML-шаблон на основании HTML-шаблона.
	 * $template_field_data - данные полученные методами getXmlTemplateFieldData
	 * @param array $template_field_data Конфигурация разделов документа в кодировке utf-8
	 * @param string $labelAlign
	 * @param string $style
	 * @return string Строка в кодировке utf-8
	 */
	public static function createXmlTemplateData($template_field_data, $labelAlign = 'top', $style = 'border: 0;')
	{
		$xml_template_data = '<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:template match="/">
    <data>
      <xtype>fieldset</xtype>
      <autoHeight>true</autoHeight>
      <labelAlign>'. $labelAlign .'</labelAlign>
      <region>center</region>
      <style>'. $style .'</style>
      <items>';
		// это будет шаблон 6,7-го типа
		foreach ( $template_field_data as $row )
		{
			$xml_template_data .= '
			<arrayNode>
			  <fieldLabel>'.$row['fieldLabel'].'</fieldLabel>
			  <xtype>'.$row['xtype'].'</xtype>
			  <name>'.$row['id'].'</name>
			  <defaultValue>'.$row['defaultValue'].'</defaultValue>';
			$xml_template_data .= (isset($row['hideLabel']))?'
			  <hideLabel>'.$row['hideLabel'].'</hideLabel>':'';
			$xml_template_data .= (isset($row['width']))?'
			  <width>'.$row['width'].'</width>':'';
			$xml_template_data .= (isset($row['height']))?'
			  <height>'.$row['height'].'</height>':'';
			$xml_template_data .= '
			  <value>
				<xsl:value-of select="//'.$row['id'].'" />
			  </value>
			</arrayNode>';
		}
		$xml_template_data .= '
      </items>
    </data>
  </xsl:template>
</xsl:stylesheet>';
		return $xml_template_data;
	}

	/**
	 * Удаляет раздел из XML-шаблона формы ввода данных документа
	 *
	 * @param string $xml_template_data Строка в кодировке utf-8
	 * @param string $section_name Строка в кодировке utf-8
	 * @return string Строка в кодировке utf-8
	 * @throws Exception
	 */
	public static function destroyFieldXmlTemplateData($xml_template_data, $section_name)
	{
		$doc = new DOMDocument();
		@$doc->loadXML($xml_template_data);
		$root = $doc->documentElement;
		if(isset($root))
		{
			$items_node = $root->getElementsByTagName('items')->item(0);
			foreach($root->getElementsByTagName('arrayNode') as $arraynode)
			{
				foreach($arraynode->childNodes as $node)
				{
					if($node->nodeName == 'name')
					{
						if($node->nodeValue == $section_name)
						{
							$oldnode = $items_node->removeChild($arraynode);
							return $doc->saveXML();
						}
					}
				}
			}
		}
		else
		{
			throw new Exception('Ошибка при удалении раздела из формы!', 500);
		}
		throw new Exception('Раздел в форме не найден!', 400);
	}

	/**
	 * Удаляет раздел из HTML-шаблона отображения документа
	 *
	 * @param string $html_template Строка в кодировке utf-8
	 * @param string $section_name Строка в кодировке utf-8
	 * @return string Строка в кодировке utf-8
	 */
	public static function destroyFieldAreaHtmlTemplate($html_template, $section_name)
	{
		if ( false === strpos($section_name, 'parameter') ) {
			$id = 'id="block_' . $section_name . '"';
			if (false === strpos($html_template, $id)) {
				return $html_template;
			}
			$new_format = 'class="template-block" ' . $id;
			if (false === strpos($html_template, $new_format)) {
				return preg_replace(
					'/<div ' . $id . '>.+<div id="data_'.$section_name.'">[ ]*\{'.$section_name.'\}[ ]*<\/div>[ ]*<\/div>/iu',
					'',
					str_replace(array("\n","\r","\t"), '', $html_template)
				);

			} else {
				return preg_replace(
					'/<div ' . $new_format . '>.+<div class="template-block-data" id="data_'.$section_name.'">[ ]*\{'.$section_name.'\}[ ]*<\/div>[ ]*<\/div>/iu',
					'',
					str_replace(array("\n","\r","\t"), '', $html_template)
				);
			}
		} else {
			$id = str_replace('parameter', '', $section_name);
			return preg_replace(
				'/@#@_'.$id.'([а-яА-ЯёЁ]*)/iu',
				'',
				$html_template
			);
		}
	}

	/**
	 * Обработка параметров с целью сохранения шаблона в корректном виде
	 * @param array $xml_template Массив атрибутов шаблона в кодировке utf-8
	 * @param int $pmUser_id
	 * @param array $doc_data Массив данных документа в кодировке utf-8
	 * @return void
	 * @throws Exception
	 */
	public static function processingData(&$xml_template, $pmUser_id, $doc_data = array()) {
		if (empty($xml_template['XmlTemplateType_id'])) {
			// по умолчанию сохраняем как шаблон с множеством разделов
			$xml_template['XmlTemplateType_id'] = self::MULTIPLE_PART_TYPE_ID;
		}
		if (1 == $xml_template['XmlTemplateType_id']) {
			// конвертируем просто изменением типа шаблона
			$xml_template['XmlTemplateType_id'] = self::MULTIPLE_PART_TYPE_ID;
		}
		switch($xml_template['XmlTemplateType_id'])
		{
			case self::EVN_USLUGA_TYPE_ID: // Шаблон протоколов услуг, частный случай шаблона документов с множеством разделов
			case self::LAB_USLUGA_PROTOCOL_TYPE_ID: // Шаблон протоколов лаб услуг
			case self::MULTIPLE_PART_TYPE_ID: // Шаблон документов с множеством разделов
				// это XML-шаблон формы ввода (XmlTemplate_Data) и HTML-шаблон отображения (XmlTemplate_HtmlTemplate), Редактируется HTML-шаблон отображения
				if (empty($xml_template['XmlTemplate_HtmlTemplate'])) {
					throw new Exception('HTML-шаблон отображения отсутствует', 404);
				}

				// ищем разметку областей ввода данных для создания разделов
				self::$foundDataTag_result_arr = array();
				self::foundDataTag($xml_template['XmlTemplate_HtmlTemplate']);

				// ищем маркеры с типом "Параметр-значения" для создания разделов
				self::getCiInstance()->load->library('swMarker');
				$markers = swMarker::foundParameterMarkers($xml_template['XmlTemplate_HtmlTemplate']);

				if (0 == count(self::$foundDataTag_result_arr['datatag_list']) && 0 == count($markers))
				{
					//var_dump($xml_template['XmlTemplate_HtmlTemplate']);
					throw new Exception('Необходимо или вставить маркер с типом "Параметр-значения" или разметить хотя бы одну область для ввода данных!', 400);
				}

				if (count($doc_data) > 0) {
					foreach (self::$foundDataTag_result_arr['datatag_list'] as $i=>$row) {
						//$row['content'] $row['id']
						if (isset($doc_data[$row['id']])) {
							self::$foundDataTag_result_arr['datatag_list'][$i]['content'] = htmlspecialchars($doc_data[$row['id']], ENT_COMPAT, 'UTF-8');
						} else {
							unset(self::$foundDataTag_result_arr['datatag_list'][$i]);
							//удаляем из XmlTemplate_HtmlTemplate
							$xml_template['XmlTemplate_HtmlTemplate'] = swXmlTemplate::destroyFieldAreaHtmlTemplate(
								$xml_template['XmlTemplate_HtmlTemplate'],
								$row['id']
							);
						}
					}
					foreach ($markers as $i=>$row) {
						$name = 'parameter'.$row['Parameter_id'];
						if (!isset($doc_data[$name])) {
							unset($markers[$i]);
							//удаляем из XmlTemplate_HtmlTemplate
							$xml_template['XmlTemplate_HtmlTemplate'] = swXmlTemplate::destroyFieldAreaHtmlTemplate(
								$xml_template['XmlTemplate_HtmlTemplate'],
								$name
							);
						}
					}
				}

				$instance = self::getXmlTemplateModelInstance();
				$template_data = $instance->getXmlTemplateFieldData(self::$foundDataTag_result_arr['datatag_list']);
				$xml_template['XmlTemplate_HtmlTemplate'] = self::$foundDataTag_result_arr['result_preg_replace_callback'];
				self::getCiInstance()->load->model('ParameterValue_model', 'ParameterValue_model');
				$template_data = array_merge($template_data, self::getCiInstance()->ParameterValue_model->getXmlTemplateFieldData($markers));

				/**
				 * Для шаблонов этого типа хранение схемы xml-документа не требуется,
				 * т.к. при сохранении документа для проверки по схеме её можно генерировать на лету из списка имен разделов
				 * и значения всех разделов имеют строковый тип,
				 * никаких дополнительных правил проверки схема не содержит
				 */
				$xml_template['XmlSchema_id'] = null;
				// Генерируем XML-шаблон
				$xml_template['XmlTemplate_Data'] = self::createXmlTemplateData($template_data);
				break;
			default:
				throw new Exception('Эта функция не реализована для шаблонов данного типа', 501);
				break;
		}
	}

	/**
	* Производит поиск тегов data в шаблоне и что-то делает в зависимости от опций self::$foundDataTag_option_arr
	* Перед вызовом функции опции могут быть переопределены
	* По умолчанию установлены опции:
	self::$foundDataTag_option_arr = array(
		'use_stripslashes' => true,
		'use_htmlspecialchars_decode' => true,
		'create_datatag_list' => true,
		'return_template' => true
	);
	*/
	private static function foundDataTag($str)
	{
		self::$foundDataTag_result_arr['datatag_list'] = array();
		if (!empty(self::$foundDataTag_option_arr['use_stripslashes'])) $str = stripslashes($str);
		if (!empty(self::$foundDataTag_option_arr['use_htmlspecialchars_decode'])) $str = htmlspecialchars_decode($str);
		$res = preg_replace_callback(
			'/<data\s*class="data"\s*id="([a-zA-Z0-9_\-]+)"(\s*name="[a-zA-Z0-9_\-]{0,}+"){0,}(\s*value="[a-zA-Z0-9_\-]{0,}+"){0,}>(.*?)(<\/data>)/siu',
			'self::onFoundDataTag',
			$str);
		$res = str_replace('</data></div>','',$res);
		self::$foundDataTag_result_arr['result_preg_replace_callback'] = $res;
	}

	/**
	 * callback-функция для preg_replace_callback
	 */
	private static function onFoundDataTag($matches)
	{
		if (!empty(self::$foundDataTag_option_arr['create_datatag_list']))
		{
			$matches[4] = preg_replace('#(<data[^>]+>|<div\s+class="template-block-data"[^>]+>)#uis', '', $matches[4]);
			self::$foundDataTag_result_arr['datatag_list'][] = array(
				'id' => $matches[1],
				'name' => $matches[2],
				'value' => $matches[3],
				'content' => htmlspecialchars($matches[4], ENT_COMPAT, 'UTF-8')
			);
		}
		if (!empty(self::$foundDataTag_option_arr['return_template']))
		{
			return '{'.$matches[1].'}';
		}
		// не производим замены
		return $matches[0];
	}


	/**
	 * Извлекает HTML из XML-шаблона
	 *
	 * Используется для конвертации в новый формат
	 * шаблонов с одним разделом под именем UserTemplateData
	 *
	 * @param $xml_template_data Строка в кодировке utf-8
	 * @return string Строка в кодировке utf-8
	 * @throws Exception
	 */
	private static function extractUserTemplateData($xml_template_data)
	{
		// Есть ли поле UserTemplateData в $xml_template_data
		if ( false === strpos($xml_template_data, '<xtype>ckeditor</xtype><name>UserTemplateData</name>') ) {
			throw new Exception('Неправильная структура шаблона с одним разделом');
		}
		preg_match('/<defaultValue>(.+?)<\/defaultValue>/siu', $xml_template_data, $matches);
		if(empty($matches[1]))
		{
			return '';
		}
		return htmlspecialchars_decode($matches[1]);
	}

	/**
	 * Определяет тип шаблона по его атрибутам
	 * @param array $xml_template Массив атрибутов шаблона в кодировке utf-8
	 * @return integer
	 * @throws Exception
	 */
	static function defineType($xml_template)
	{
		// сначала определяем простейшие типы шаблонов
		if (false !== strpos($xml_template['XmlTemplate_Data'],'{"PrescriptionType_id":')) {
			return self::EVN_PRESCR_PLAN_TYPE_ID;
		}

		$has_ref_values = (false !== strpos($xml_template['XmlTemplate_Data'], 'data class="data" id="UslugaComplex_'));
		$has_section = (false !== strpos($xml_template['XmlTemplate_Data'], 'class="section" id="UslugaComplexList_'));
		//
		if ($has_ref_values || $has_section) {
			return self::OLD_EVN_USLUGA_TYPE_ID;
		}

		$type = self::OLD_TYPE_ID;
		$has_autoname = (false !== strpos($xml_template['XmlTemplate_Data'], '<name>autoname'));
		// ищем разметку областей ввода данных для создания разделов
		self::$foundDataTag_result_arr = array();
		self::foundDataTag($xml_template['XmlTemplate_HtmlTemplate']);
		$has_data_tag = (count(self::$foundDataTag_result_arr['datatag_list']) > 0);

		// ищем маркеры с типом "Параметр-значения" для создания разделов
		$has_parameter = (false !== strpos($xml_template['XmlTemplate_Data'], '<name>parameter'));
		//self::getCiInstance()->load->library('swMarker');
		//$markers = swMarker::foundParameterMarkers($xml_template['XmlTemplate_HtmlTemplate']);
		//$has_marker = (count($markers) > 0);

		$has_usertemplatedata = (false !== strpos($xml_template['XmlTemplate_Data'], '<name>UserTemplateData</name>'));

		switch(true) {
			case (!$has_usertemplatedata && ($has_data_tag || $has_autoname || $has_parameter) && !empty($xml_template['XmlTemplate_HtmlTemplate'])):
				$type = self::MULTIPLE_PART_TYPE_ID;
				if (!empty($xml_template['UslugaComplex_id_list'])) {
					$type = self::EVN_USLUGA_TYPE_ID;
				}
				break;
			case ($has_data_tag && $has_usertemplatedata && !empty($xml_template['XmlTemplate_HtmlTemplate'])):
				$type = self::OLD_MULTIPLE_PART_TYPE_ID;
				break;
			case (!$has_data_tag && $has_usertemplatedata && empty($xml_template['XmlTemplate_HtmlTemplate'])):
				$type = self::OLD_SIMPLE_TYPE_ID;
				break;
		}
		return $type;
	}

	/**
	 * Преобразование документа к виду для печати или редактирования
	 * @param array $xml_data Массив атрибутов документа в кодировке utf-8 полученных методом EvnXmlBase_model::doLoadPrintData
	 * @param bool $is_for_print Признак, что документ нужен для печати
	 * @return string Строка в кодировке utf-8
	 * @throws Exception
	 */
	public static function getHtmlDoc($xml_data, $is_for_print = false, $nestedCount = 0) {
		$evn_xml_data = toUTF($xml_data['EvnXml_Data']);
		$html = self::processingXmlToHtml($evn_xml_data,
			toUTF($xml_data['XmlTemplate_Data'])
		);
		if (empty($html)) {
			$xml_data_arr = self::transformEvnXmlDataToArr($evn_xml_data, true);
			array_walk($xml_data_arr,'toAnsi');
			/*
			закомментировал, т.к. этот код добавляет пустые строки, там где это не надо
			function processingNodeValue(&$doc)
			{
				// https://redmine.swan.perm.ru/issues/12618
				// В некоторых местах замена переноса строки на <br /> не требуется, т.к. в самом шаблоне уже указан <br />
				// Поэтому поголовная замена \n на <br /> заменена регуляркой

				// $doc = str_replace("\n", "<br />\n", $doc);
				$doc = preg_replace("/(\<br\>|\<br\/\>|\<br \/\>)?(\n|\n\r|\r\n)+/", "<br />", $doc);
			}
			array_walk($xml_data_arr,'processingNodeValue');
			*/
			if (empty($xml_data['XmlTemplate_HtmlTemplate']) || strpos($xml_data['EvnXml_Data'], '<UserTemplateData>')) {
				//есть UserTemplateData в EvnXml_Data. Используется устаревший шаблон без разметки областей ввода данных и областей только для печати
				$html = $xml_data_arr['UserTemplateData'];
			} else {
				//документ нового формата с множеством разделов и шаблоном отображения в XmlTemplate_HtmlTemplate
				$html = $xml_data['XmlTemplate_HtmlTemplate'];
				$xml_data['Evn_id'] = (empty($xml_data['Evn_id'])) ? null : $xml_data['Evn_id'];
				$xml_data['EvnXml_id'] = (empty($xml_data['EvnXml_id'])) ? null : $xml_data['EvnXml_id'];
				//это нужно для печати или редактирования объектов с типом Параметр и список значений
				self::getCiInstance()->load->library('swMarker');
				$html = swMarker::createMarkerBlocks($html, $xml_data_arr, $is_for_print);
				$html = swMarker::createInputBlocks($html, $xml_data_arr, $xml_data, $is_for_print);
				$html = swMarker::createParameterValueFields($html, $xml_data['EvnXml_id'], $xml_data_arr, $is_for_print);
				$html = swMarker::createAnketa($html, $xml_data_arr, $xml_data, $is_for_print); //TAG: печать 2

				//удаляются лишние переносы строки
				if ($is_for_print) {
					$html = preg_replace('/(<br\/>|<br \/>)/', '<br>', $html);
					$html = preg_replace('/<\/div>(\s*<br>)+/', '</div>', $html);
					$html = preg_replace('/<br>(\s*<br>)+/', '<br>', $html);
				}

				//убираем переносы строк, разделяющие параметры
				$html = str_replace("</div><br />", "</div>", $html);
				self::getCiInstance()->load->library('parser');
				$html = self::getCiInstance()->parser->parse_string($html, $xml_data_arr, true);

				$html = swMarker::processingTextWithMarkers($html, $xml_data['Evn_id'], array(
					'isPrint' => true,
					'From_Evn_id' => $xml_data['Evn_id'],
					'cacheEvnXml' => !empty($xml_data['EvnXml_id']) ? $xml_data['EvnXml_id'] : null,
					'EvnXml_id' => (empty($xml_data['EvnXml_id'])) ? null : $xml_data['EvnXml_id'],
					'restore' => 'full'
				), $nestedCount, $xml_data_arr, $is_for_print);
			}
		}

		$html = preg_replace("/@#@EAN13\_(.*?)@#@/uis", "<img height=100 src='/barcode.php?s=$1&type=ean13' />", $html); // подменяем штрих-код на картинку

		return $html;
	}

	/**
	 * Устанавливает значения из $evn_xml_data в теги data в $xml_template_data и возвращает Html шаблон для просмотра (печати)
	 * в том случае, когда
	 * имеются теги data в $xml_template_data
	 * должен быть узел UserTemplateData в $evn_xml_data
	 * но нет узла UserTemplateData в $evn_xml_data
	 * @param string $evn_xml_data Строка в кодировке utf-8
	 * @param string $xml_template_data Строка в кодировке utf-8
	 * @return string Строка в кодировке utf-8
	 */
	public static function processingXmlToHtml($evn_xml_data,$xml_template_data)
	{
		if (!$evn_xml_data || !$xml_template_data) {
			return false;
		}
		// есть ли узел <UserTemplateData> в $EvnXml_Data
		$UserTemplateDataInEvnXml_Data = (strpos($evn_xml_data, '<UserTemplateData>') !== false);
		// должен ли быть узел <UserTemplateData> в $EvnXml_Data
		$UserTemplateDataInXmlTemplate_Data = (strpos($xml_template_data, '<xtype>ckeditor</xtype><name>UserTemplateData</name>') !== false);
		// есть ли теги data в $XmlTemplate_Data
		$DataTagsInXmlTemplate_Data = self::useDataTags($xml_template_data);
		if ($UserTemplateDataInEvnXml_Data === false AND $UserTemplateDataInXmlTemplate_Data === true AND $DataTagsInXmlTemplate_Data === true)
		{
			$HtmlTemplate = self::dataTagsTransform($evn_xml_data, $xml_template_data, false);
			return $HtmlTemplate;
		}
		return '';
	}

	/**
	 * Определяет имеются ли теги data в $xml_template_data
	 * @param string $xml_template_data Строка в кодировке utf-8
	 * @return bool
	 */
	private static function useDataTags($xml_template_data)
	{
		return (strpos($xml_template_data, 'data class="data" id=') !== false);
	}

	/**
	 * Осуществляет преобразование $evn_xml_data
	 * @param string $evn_xml_data Строка в кодировке utf-8
	 * @param string $xml_template_data Строка в кодировке utf-8
	 * @param bool $htmlspecialchars
	 * @return string Строка в кодировке utf-8
	 */
	private static function dataTagsTransform($evn_xml_data, $xml_template_data, $htmlspecialchars = true)
	{
		self::$_xml_data_cache = array();
		preg_match('/<defaultValue>(.*)<\/defaultValue>/siu', $xml_template_data, $matches);
		$HtmlTemplate = htmlspecialchars_decode($matches[1]);
		$HtmlTemplate = str_replace('$','',$HtmlTemplate);
		$HtmlTemplate = str_replace('</data>','$</data>',$HtmlTemplate); // здесь это не обязательно, поскольку тут нас интересуют только идешники
		//$pattern = '/(<data class="data" id=")([a-z0-9_\-]+)(">)([^\$]*)(\$<\/data>)/e';
		//$pattern = '/(<data\s*class="data"\s*id="([a-zA-Z0-9_\-]+)">)(.*?)(<\/data>)/e';
		$pattern = '/(<data\s*class="data"\s*id="([a-zA-Z0-9_\-]+)"(\s*name="[a-zA-Z0-9_\-]{0,}+"){0,}(\s*value="[a-zA-Z0-9_\-]{0,}+"){0,}>)([^\$]*)(\$<\/data>)/eu';
		//$replacement = "'\\1\\2\\3'.getItemEvnXmlData('\\2','$EvnXml_Data').'</data>'";
		$replacement = "'\\1'.self::getItemEvnXmlData('\\2','$evn_xml_data').'</data>'";
		$HtmlTemplate = preg_replace($pattern, $replacement, $HtmlTemplate);
		$HtmlTemplate = stripslashes($HtmlTemplate);
		if ($htmlspecialchars) {
			$HtmlTemplate = htmlspecialchars($HtmlTemplate, ENT_COMPAT, 'UTF-8');
		}
		return $HtmlTemplate;
	}

	/**
	 * Извлекает из $evn_xml_data контент по имени узла
	 * Предполагается подобный формат $evn_xml_data: <data><anamnes>Без особенностей</anamnes><obstatus>&lt;u&gt; &lt;/u&gt;Все показатели в норме</obstatus><diagnos> здоров</diagnos></data>
	 *
	 * @param string $key Строка в кодировке utf-8
	 * @param string $evn_xml_data Строка в кодировке utf-8
	 * @return string Строка в кодировке utf-8
	 */
	private static function getItemEvnXmlData($key, $evn_xml_data)
	{
		if (empty(self::$_xml_data_cache))
		{
			self::$_xml_data_cache = self::transformEvnXmlDataToArr($evn_xml_data);
		}
		return (isset($xml_data_cache[$key]))?$xml_data_cache[$key]:'&nbsp;';
	}

	/**
	 * Генерирует параметры части запроса, отвечающего за права видимости/редактирования объекта
	 * @param int $Lpu_uid МО пользователя
	 * @param int $LpuSection_uid Отделение пользователя
	 * @param int $pmUser_id
	 * @return array
	 */
	static function getAccessRightsQueryParams($Lpu_uid, $LpuSection_uid, $pmUser_id)
	{
		return array(
			'isSuperadmin' => (isSuperadmin() ? 2 : 1 ),
			'isLpuAdmin' => (isLpuAdmin($Lpu_uid) ? 2 : 1 ),
			'Lpu_uid' => $Lpu_uid,
			'LpuSection_uid' => $LpuSection_uid,
			'pmUser_id' => $pmUser_id,
		);
	}

	/**
	 * Генерирует часть запроса, отвечающего за права видимости/редактирования объекта
	 * @param string $mainAlias Псевдоним таблицы с полями XmlTemplateScope_id, XmlTemplateScope_eid, pmUser_insID, Lpu_id, LpuSection_id
	 * @param string $object Объект
	 * @param bool $isFilter Это фильтр для видимости или часть выборки accessType
	 * @return string
	 */
	static function getAccessRightsQueryPart($mainAlias, $object, $isFilter = true)
	{
		if ($isFilter) {
			// Superadmin видит все шаблоны/папки/параметры
			// поэтому нет необходимости в when {$mainAlias}.XmlTemplateScope_id = 1 and 2 = :isSuperadmin then 1
			// Автор всегда видит свои шаблоны/папки/параметры
			// администратор ЛПУ видит все шаблоны/папки/параметры своего ЛПУ
			// кроме тех, что в настройках указано видимость только суперадмином
			return "(1 = (case
					when 2 = :isSuperadmin then 1
					when {$mainAlias}.pmUser_insID = :pmUser_id then 1
					when {$mainAlias}.XmlTemplateScope_id = 2 then 1 -- Все видят
					when {$mainAlias}.XmlTemplateScope_id > 2 and 2 = :isLpuAdmin and {$mainAlias}.Lpu_id is not null and {$mainAlias}.Lpu_id = :Lpu_uid then 1
					when {$mainAlias}.XmlTemplateScope_id = 3 and {$mainAlias}.Lpu_id is not null and {$mainAlias}.Lpu_id = :Lpu_uid then 1 --ЛПУ автора
					when {$mainAlias}.XmlTemplateScope_id = 4 and {$mainAlias}.LpuSection_id is not null and {$mainAlias}.LpuSection_id = :LpuSection_uid then 1 --Отделение автора
					else 0
				end))";
		}
		// супер администратор может редактировать/удалять любые шаблоны/папки/параметры
		// поэтому нет необходимости в when {$mainAlias}.XmlTemplateScope_eid = 1 and 2 = :isSuperadmin then 1
		// администратор ЛПУ может редактировать/удалять любые шаблоны/папки/параметры своего ЛПУ
		// кроме тех, что в настройках указано редактирование только суперадмином
		// автор всегда может редактировать свои шаблоны/папки/параметры
		return "case when 2 = :isSuperadmin then 'edit'
					when {$mainAlias}.pmUser_insID = :pmUser_id then 'edit'
					when {$mainAlias}.XmlTemplateScope_eid = 2 then 'edit' -- Все могут редактировать/удалять
					when {$mainAlias}.XmlTemplateScope_eid > 2 and 2 = :isLpuAdmin and {$mainAlias}.Lpu_id is not null and {$mainAlias}.Lpu_id = :Lpu_uid then 'edit'
					when {$mainAlias}.XmlTemplateScope_eid = 3 and {$mainAlias}.Lpu_id is not null and {$mainAlias}.Lpu_id = :Lpu_uid then 'edit' --ЛПУ автора
					when {$mainAlias}.XmlTemplateScope_eid = 4 and {$mainAlias}.LpuSection_id is not null and {$mainAlias}.LpuSection_id = :LpuSection_uid then 'edit' --Отделение автора
					else 'view'
				end";
	}

	/**
	 * Проверка доступа на добавление, редактирование или удаление
	 * @param int $pmUser_id
	 * @param int $Lpu_uid МО пользователя
	 * @param int $LpuSection_uid Отделение пользователя
	 * @param array $objData Должны быть ключи: xmltemplatescope_eid, pmuser_insid, lpu_id, lpusection_id
	 * @param int $id
	 * @return bool
	 * @throws Exception
	 */
	static function hasAccessWrite($pmUser_id, $Lpu_uid, $LpuSection_uid, $objData, $id)
	{
		if (empty($id)) {
			// Проверка прав на добавление
			// Все авторизованные пользователи могут добавлять
			return true;
		}
		if ( empty($objData) || !is_array($objData) ) {
			throw new Exception('Модель не загружена!', 500);
		}
		if (!isSuperadmin() && isLpuAdmin($Lpu_uid)) {
			if ( empty($Lpu_uid) ) {
				throw new Exception('Недостаточно параметров для проверки доступа администратора МО!', 400);
			}
		}
		if (!isSuperadmin() && !isLpuAdmin($Lpu_uid)) {
			if ( empty($LpuSection_uid) || empty($Lpu_uid) || empty($pmUser_id) ) {
				throw new Exception('Недостаточно параметров для проверки доступа пользователя МО!', 400);
			}
		}
		$allow = false;
		switch (true) {
			case (isSuperadmin()):
				// супер администратор может все
				$allow = true;
				break;
			case ($objData['pmuser_insid'] == $pmUser_id):
				// Автор всегда может редактировать свой шаблон
				$allow = true;
				break;
			case (2 == $objData['xmltemplatescope_eid']): // Все
				$allow = true;
				break;
			case (isLpuAdmin($Lpu_uid)
				&& $objData['lpu_id'] == $Lpu_uid
				&& (empty($objData['xmltemplatescope_eid']) || $objData['xmltemplatescope_eid'] > 1)
			):
				// администратор ЛПУ может редактировать/удалять только записи своего ЛПУ
				// кроме тех, что в настройках указано редактирование только суперадмином
				$allow = true;
				break;
			case (3 == $objData['xmltemplatescope_eid']
				&& $objData['lpu_id'] == $Lpu_uid
			): // ЛПУ автора
				$allow = true;
				break;
			case (4 == $objData['xmltemplatescope_eid']
				&& $objData['lpusection_id'] == $LpuSection_uid
			): // Отделение автора
				$allow = true;
				break;
		}
		return $allow;
	}

	/**
	 * @param array $session
	 * @return bool
	 */
	static function isAllowRootFolder($session)
	{
		// Редактировать корневую папку можно только из АРМа ЦОД
		return (isSuperadmin()
			&& empty($session['CurMedStaffFact_id'])
			&& empty($session['CurMedService_id'])
		);
	}

	/**
	 * Проверка возможности логики папок и шаблонов по умолчанию
	 * @param array $session
	 * @return bool
	 */
	static function isDisableDefaults($session)
	{
		$isDisable = false;
		switch (true) {
			case (isSuperadmin() && empty($session['CurMedStaffFact_id']) && empty($session['CurMedService_id'])):
				// работа из АРМа ЦОД
				$isDisable = true;
				break;
			case (isLpuAdmin($session['lpu_id']) && empty($session['CurMedStaffFact_id']) && empty($session['CurMedService_id'])):
				// работа из АРМа администратора ЛПУ
				$isDisable = true;
				break;
		}
		return $isDisable;
	}

	/**
	 * Проверка папки верхнего уровня при сохранении папки/шаблона.
	 * Если не указана
	 * или недоступна для редактирования
	 * или нет папки по умолчанию
	 * или нет ни одной папки доступной для редактирования,
	 * то создается папка по умолчанию
	 * @param XmlTemplateCatDefault_model $dm
	 * @param XmlTemplateCat_model $fm
	 * @param array $data
	 * В $data должны быть ключи: MedStaffFact_id, LpuSection_id, MedService_id,
	 * EvnClass_id, XmlType_id, XmlTemplateCat_id, session
	 * @return array
	 * @throws Exception
	 */
	static function checkFolder(XmlTemplateCatDefault_model $dm, XmlTemplateCat_model $fm, $data)
	{
		$data['pmUser_id'] = $data['session']['pmuser_id'];
		$data['Lpu_id'] = $data['session']['lpu_id'];
		$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
		if (!empty($data['session']['CurMedService_id'])) {
			$data['MedService_id'] = $data['session']['CurMedService_id'];
		}
		if (!empty($data['session']['CurMedStaffFact_id'])) {
			$data['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}
		if (!empty($data['session']['CurLpuSection_id'])) {
			$data['LpuSection_id'] = $data['session']['CurLpuSection_id'];
		}
		if (!empty($data['XmlTemplateCat_id']) && $data['XmlTemplateCat_id'] > 0) {
			// проверяем доступность для редактирования
			$sql = "
				select
					xmltemplatescope_eid, pmuser_insid, lpu_id, lpusection_id
				from
					dbo.v_XmlTemplateCat xt
				where
					xt.XmlTemplateCat_id = :id
			";
			$result = $fm->db->query($sql, array('id' => $data['XmlTemplateCat_id']));
			if ( false == is_object($result) ) {
				throw new Exception('Ошибка БД, не удалось проверить доступность для редактирования!', 500);
			}
			$tmp = $result->result('array');
			if (count($tmp) == 0) {
				$data['XmlTemplateCat_id'] = null;
			} else {
				$allow = self::hasAccessWrite($data['pmUser_id'], $data['Lpu_id'], $data['LpuSection_id'], $tmp[0], $data['XmlTemplateCat_id']);
				if (false == $allow) {
					$data['XmlTemplateCat_id'] = null;
				}
			}
		}
		if (empty($data['XmlTemplateCat_id'])) {
			// получаем папку по умолчанию
			$tmp = $dm->search($data);
			if (empty($tmp)) {
				// нет папки по умолчанию доступной для редактирования
			} else {
				if (!empty($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
				}
				if (empty($data['XmlTemplateCat_eid']) || $data['XmlTemplateCat_eid'] != $tmp[0]['XmlTemplateCat_id']) {
					$data['XmlTemplateCat_id'] = $tmp[0]['XmlTemplateCat_id'];
				}
			}
		}
		if (empty($data['XmlTemplateCat_id'])) {
			// Поиск папок доступных для редактирования
			$tmp = $fm->search($data);
			if (empty($tmp)) {
				// нет папок доступных для редактирования
			} else {
				if (!empty($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
				}
				if (empty($data['XmlTemplateCat_eid']) || $data['XmlTemplateCat_eid'] != $tmp[0]['XmlTemplateCat_id']) {
					$data['XmlTemplateCat_id'] = $tmp[0]['XmlTemplateCat_id'];
				}
			}
		}
		if (empty($data['XmlTemplateCat_id'])) {
			// папка не указана или недоступна для редактирования или нет папки по умолчанию
			$tmp = $fm->createDefault($data);
			if (count($tmp) && !empty($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
			}
			$data['XmlTemplateCat_id'] = $tmp[0]['XmlTemplateCat_id'];
		}
		return $data;
	}

	/**
	 * @return bool
	 */
	static public function isAllowNewTables()
	{
		return false;
		//данные ещё не переносили
		//return ('khak' == self::getCiInstance()->load->getRegionNick());
	}

	/**
	 * @return XmlTemplateBase_model
	 */
	static public function getXmlTemplateModelInstance()
	{
		$instanceModelName = 'XmlTemplateBase_model';
		self::getCiInstance()->load->model($instanceModelName);
		// могут быть региональные модели типа ufa_XmlTemplateBase_model, наследующие XmlTemplateBase_model
		$className = get_class(self::getCiInstance()->{$instanceModelName});
		return new $className();
	}

	/**
	 * @return EvnXmlBase_model
	 */
	static public function getEvnXmlModelInstance()
	{
		$instanceModelName = 'EvnXmlBase_model';
		self::getCiInstance()->load->model($instanceModelName);
		// могут быть региональные модели типа ufa_EvnXmlBase_model, наследующие EvnXmlBase_model
		$className = get_class(self::getCiInstance()->{$instanceModelName});
		return new $className();
	}

	/**
	 * Метод удаляет id html тегов вида id="keyword_paramname12_12345" из html шаблона.
	 * Нужно для того, чтобы документы с одинаковыми EvnXml_id в одной области просмотра корректно подставляли поля для ввода
	 * Точнее, чтобы документы в разделе направления всегда были в режиме просмотра, если в другой секции есть такой же документ, но в режиме редактирования
	 * Чтобы избежать одинаковых id на странице
	 */
	static public function destroyInputParamIds($html)
	{
		$keyWords = array('block', 'caption', 'data', 'wrap', 'output', 'input', 'json', 'buttons');
		$pattern = '/(id="(' . implode('|', $keyWords) .')[a-zA-Z0-9_]*")/';
		$replacement = ' ';

		return preg_replace($pattern, $replacement, $html);
	}
}
