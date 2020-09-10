<?php
/**
 * Xml_helper - для работы с XML - преобразованиями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Pshenitcyn Ivan (ipshon@rambler.ru)
 * @version      ?
 */

defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Вспомогательная функция для преобразования $EvnXml_Data
 * Извлекает Html шаблон из узла defaultValue $XmlTemplate_Data
 * Предполагается подобный формат $EvnXml_Data: <data><anamnes>Без особенностей</anamnes><obstatus>&lt;u&gt; &lt;/u&gt;Все показатели в норме</obstatus><diagnos> здоров</diagnos></data>
 */
function getHtmlTemplate($XmlTemplate_Data)
{
	preg_match('/<defaultValue>(.*)<\/defaultValue>/siu', $XmlTemplate_Data, $matches);
	if (isset($matches[1])) {
		return htmlspecialchars_decode($matches[1]);
	} else {
		return null;
	}
}
/**
 * Вспомогательная функция для преобразования $EvnXml_Data
 * Извлекает из $EvnXml_Data контент по имени узла
 * Предполагается подобный формат $EvnXml_Data: <data><anamnes>Без особенностей</anamnes><obstatus>&lt;u&gt; &lt;/u&gt;Все показатели в норме</obstatus><diagnos> здоров</diagnos></data>
 */
function transformEvnXmlDataToArr($EvnXml_Data)
{
	$xml_data_cache = array();
	$doc = new DOMDocument();
	@$doc->loadXML($EvnXml_Data);
	foreach($doc->documentElement->childNodes as $node)
	{
		if($node->nodeName == '#text')
			continue;
		//@to-do делать str_replace("	", '', $node->nodeValue) при соxранении данных из шаблона
		$xml_data_cache[$node->nodeName] = str_replace("	", '', $node->nodeValue);
	}
	/*
	$EvnXml_Data = str_replace(array('<data>','</data>'),'',$EvnXml_Data);
	preg_match_all("|<([^>]+)>(.*)</[^>]+>|Us",$EvnXml_Data,$matches, PREG_SET_ORDER);
	foreach($matches as $match)
	{
		$xml_data_cache[$match[1]]=htmlspecialchars_decode($match[2]);
	}
	preg_match_all("|<([^>]+) /[^>]+>|Us",$EvnXml_Data,$matches, PREG_SET_ORDER);
	foreach($matches as $match)
	{
		$xml_data_cache[$match[1]]='';
	}
	*/
	return $xml_data_cache;
}
/**
 * getItemEvnXmlData
 */
function getItemEvnXmlData($key,$EvnXml_Data)
{
	global $xml_data_cache;
	if (empty($xml_data_cache))
	{
		$xml_data_cache = transformEvnXmlDataToArr($EvnXml_Data);
	}
	return (isset($xml_data_cache[$key]))?$xml_data_cache[$key]:'&nbsp;';
}

/**
 * Осуществляет преобразование $EvnXml_Data
 */
function dataTagsTransform($EvnXml_Data,$XmlTemplate_Data,$htmlspecialchars = true)
{
	global $xml_data_cache;
	$xml_data_cache = array();
	preg_match('/<defaultValue>(.*)<\/defaultValue>/siu', $XmlTemplate_Data, $matches);
	$HtmlTemplate = htmlspecialchars_decode($matches[1]);
	//$HtmlTemplate = getHtmlTemplate($XmlTemplate_Data);
	$HtmlTemplate = str_replace('$','',$HtmlTemplate);
	$HtmlTemplate = str_replace('</data>','$</data>',$HtmlTemplate); // здесь это не обязательно, поскольку тут нас интересуют только идешники 
	//$pattern = '/(<data class="data" id=")([a-z0-9_\-]+)(">)([^\$]*)(\$<\/data>)/e';
	//$pattern = '/(<data\s*class="data"\s*id="([a-zA-Z0-9_\-]+)">)(.*?)(<\/data>)/e';
	$pattern = '/(<data\s*class="data"\s*id="([a-zA-Z0-9_\-]+)"(\s*name="[a-zA-Z0-9_\-]{0,}+"){0,}(\s*value="[a-zA-Z0-9_\-]{0,}+"){0,}>)([^\$]*)(\$<\/data>)/eu';
	//$replacement = "'\\1\\2\\3'.getItemEvnXmlData('\\2','$EvnXml_Data').'</data>'";
	$replacement = "'\\1'.getItemEvnXmlData('\\2','$EvnXml_Data').'</data>'";
	$HtmlTemplate = preg_replace($pattern, $replacement, $HtmlTemplate);
	$HtmlTemplate = stripslashes($HtmlTemplate);
	if ($htmlspecialchars)
		$HtmlTemplate = htmlspecialchars($HtmlTemplate);
	return $HtmlTemplate;
}

/**
 * Определяет имеются ли теги data в $XmlTemplate_Data
 */
function useDataTags($XmlTemplate_Data)
{
	return (strpos($XmlTemplate_Data, 'data class="data" id=') !== false);
}

/**
 * Осуществляет преобразование $EvnXml_Data в случае, когда
 * имеются теги data в $XmlTemplate_Data
 * должен быть узел UserTemplateData в $EvnXml_Data
 * но нет узла UserTemplateData в $EvnXml_Data
 * Преобразование заключается в том, что берутся данные из EvnXml
 * С помощью функции dataTagsTransform данные по умолчанию, находящиеся внутри тегов data в $XmlTemplate_Data,
 * Заменяются на данные из узлов $EvnXml_Data с именами соответствующими идентификатору тэга data.
 * @example
 * Данные внутри тэга <data class="data" id="zhaloby">нет</data> шаблона $XmlTemplate_Data, где "нет" - значение по умолчанию
 * будут замененны на данные внутри узла <zhaloby> на головную боль</zhaloby>
 * Результат: <data class="data" id="zhaloby"> на головную боль</data> в документе полученном из шаблона $XmlTemplate_Data
 */
function beforeTransformToXML($EvnXml_Data,$XmlTemplate_Data)
{
	// есть ли узел <UserTemplateData> в $EvnXml_Data
	$UserTemplateDataInEvnXml_Data = (strpos($EvnXml_Data, '<UserTemplateData>') !== false);
	// должен ли быть узел <UserTemplateData> в $EvnXml_Data
	$UserTemplateDataInXmlTemplate_Data = (strpos($XmlTemplate_Data, '<xtype>ckeditor</xtype><name>UserTemplateData</name>') !== false);
	// есть ли теги data в $XmlTemplate_Data
	$DataTagsInXmlTemplate_Data = useDataTags($XmlTemplate_Data);
	if ($UserTemplateDataInEvnXml_Data === false AND $UserTemplateDataInXmlTemplate_Data === true AND $DataTagsInXmlTemplate_Data === true)
	{
		$HtmlTemplate = dataTagsTransform($EvnXml_Data,$XmlTemplate_Data,true);
		return "<data><UserTemplateData>{$HtmlTemplate}</UserTemplateData></data>";
	}
	return $EvnXml_Data;
}

/**
 * Устанавливает значения из $EvnXml_Data в теги data в $XmlTemplate_Data и возвращает Html шаблон для просмотра (печати)
 * в том случае, когда
 * имеются теги data в $XmlTemplate_Data
 * должен быть узел UserTemplateData в $EvnXml_Data
 * но нет узла UserTemplateData в $EvnXml_Data
 */
function processingXmlToHtml($EvnXml_Data,$XmlTemplate_Data)
{
	if ($EvnXml_Data === false OR $XmlTemplate_Data === false)
		return false;
	// есть ли узел <UserTemplateData> в $EvnXml_Data
	$UserTemplateDataInEvnXml_Data = (strpos($EvnXml_Data, '<UserTemplateData>') !== false);
	// должен ли быть узел <UserTemplateData> в $EvnXml_Data
	$UserTemplateDataInXmlTemplate_Data = (strpos($XmlTemplate_Data, '<xtype>ckeditor</xtype><name>UserTemplateData</name>') !== false);
	// есть ли теги data в $XmlTemplate_Data
	$DataTagsInXmlTemplate_Data = useDataTags($XmlTemplate_Data);
	if ($UserTemplateDataInEvnXml_Data === false AND $UserTemplateDataInXmlTemplate_Data === true AND $DataTagsInXmlTemplate_Data === true)
	{
		$HtmlTemplate = dataTagsTransform($EvnXml_Data,$XmlTemplate_Data,false);
		return $HtmlTemplate;
	}
	return false;
}

/**
 * Преобразует элементы 'arrayNode' в обычный, порядковый массив
 */
function replaceArrayNodeToSimpleArray( $arr )
{
	if ( !is_array( $arr ) || count($arr) == 0 )
		return $arr;
	$flag_of_array_node = false;
	$array_of_array_node = array();
	foreach ( $arr as $key => $value )
	{
		if ( is_array($arr[$key]) && $key != 'arrayNode' )
		{
			$arr[$key] = replaceArrayNodeToSimpleArray($arr[$key]);
		}
		if ( $key == 'arrayNode' )
		{
			$flag_of_array_node = true;
			foreach( $arr[$key] as $arr_nodes ) {
				if ( !is_array($arr_nodes) )
				{
					//$array_of_array_node[] = $arr_nodes; //было, тогда одинарные не получались
					$array_of_array_node = $arr[$key];
					//echo "<pre>";
					//var_dump($arr_nodes);
				}
				else					
					$array_of_array_node[] = replaceArrayNodeToSimpleArray( $arr_nodes );
			}
		}			
	}
	if ( $flag_of_array_node === true )
		$arr = $array_of_array_node;		
	return $arr;
}
/**
 * replaceTypesDef
 */
function replaceTypesDef($arr)
{
	if ( !is_array( $arr ) || count($arr) == 0 )
		return $arr;
	$flag_of_array_node = false;
	$array_of_array_node = array();
	foreach ( $arr as $key => $value )
	{
		// при необходимости добавить преобразования для других типов
		if ( is_array($arr[$key]) && isset($arr[$key]['int']) )
		{
			$arr[$key] = (int)$arr[$key]['int'];
		}
		else
		{
			if ( is_array($arr[$key]) )
				$arr[$key] = replaceTypesDef($arr[$key]);		
		}
		// В стандартных шаблонах устанавливаем значения по умолчанию defaultValue в поля textarea, ckeditor при условии что пусто value
		//$arr['data']["items"][$i]['xtype'] $arr['data']["items"][$i]["defaultValue"] $arr['data']["items"][$i]["value"]
		if ( is_array($arr[$key]) && isset($arr[$key]['items']) && is_array($arr[$key]['items']) && ! empty($arr[$key]['items']) )
		{
			foreach ( $arr[$key]['items'] as $i => $field ) {
				if ( isset($field['defaultValue']) && isset($field['xtype']) && in_array($field['xtype'], array('textarea','ckeditor')) && empty($field['value']) )
				{
					$arr[$key]['items'][$i]['value']=$field['defaultValue'];
					unset($arr[$key]['items'][$i]['defaultValue']);
				}
				if ( isset($field['xtype']) && 'ckeditor' == $field['xtype'] )
				{
					// прячем fieldLabel для ckeditor
					$arr[$key]['items'][$i]['hideLabel'] = (!isset($field['hideLabel']) || $field['hideLabel'] == 'true');
				}
			}
		}
	}	
	return $arr;
}
/**
 * deleteEmptyArrayNode
 */
function deleteEmptyArrayNode( $arr )
{
	if ( is_array($arr) )
	{
		foreach ($arr as $key => $value)
		{
			if ( $key == 1 && is_array ($value) && count($value) == 0 )
			{
				unset($arr[$key]);
			}
			else
				if ( is_array($value) )
					$arr[$key] = deleteEmptyArrayNode($value);
		}
	}
	return $arr;
}
/**
 * backToArrayNodeFromArrayNode1
 */
function backToArrayNodeFromArrayNode1($arr)
{
	if ( is_array($arr) )
	{
		foreach ($arr as $key => $value)
		{
			// arrayNode не состоит из подмассивов
			if ( $key == 'arrayNode1' )
			{
				$tmp = $arr[$key];				
				unset($arr[$key]);
				$arr['arrayNode'] = backToArrayNodeFromArrayNode1($tmp);
				
			}
			else
				if ( is_array($value) )
					$arr[$key] = backToArrayNodeFromArrayNode1($value);				
		}
	}
	return $arr;
}

/**
 * Пока функция делает замену ассоциативного ключа arrayNode на элементы неассоциативного массива
 * Еще заменяет 'true' и 'false' булинами true и false
 */
function XmlTemplateArrayPostProcess( &$arr )
{
	if ( !is_array( $arr ) )
		return false;
	//echo "<pre>";
	//var_dump($arr);
	
	//$arr = convertArrayNodeItemsToSimpleArray($arr);
	//$arr = backToArrayNodeFromArrayNode1($arr);
	//var_dump($arr);
	//var_dump($arr);
	if ( !$arr = replaceArrayNodeToSimpleArray($arr) )
		return false;
	// удаляем пустые arrayNode
	$arr = deleteEmptyArrayNode($arr);
	//var_dump($arr);
	// для преобразования значений с определенным типом
	$arr = replaceTypesDef($arr);

	//echo "<pre>";
	//var_dump($arr);
		
	return true;
}

/**
 * Преобразует XML-строку в массив.
 */
function XmlToArray($data, $get_attributes = 1, $priority = 'tag')
{
	$contents = "";
	if (!function_exists('xml_parser_create'))
		{return array ();}

	$isUTF8 = mb_detect_encoding($data, "UTF-8",true);

	//Дебаг для отловли неверной кодировки
	if (!empty($_REQUEST['getDebug']) && $_REQUEST['getDebug'] == 2) {
		echo "<pre>XmlToArray()".PHP_EOL;
		echo 'кодировка: '.mb_detect_encoding($data,'auto').PHP_EOL;
		echo '$data =  '.htmlentities($data).PHP_EOL;
	}

	if(!$isUTF8 && mb_detect_encoding($data, "WINDOWS-1251",true)){
		$data = iconv('WINDOWS-1251', 'UTF-8', $data);
	}elseif(!$isUTF8){
		throw new Exception('Кодировка XML не UTF-8');
	}

	$data = preg_replace('/(windows-1251)/i', 'utf-8', $data);
	$parser = xml_parser_create('UTF-8');
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($data), $xml_values);
	xml_parser_free($parser);
	
	if (!$xml_values)
	return xml_get_error_code($parser);
	
	$xml_array = array ();
	$parents = array ();
	$opened_tags = array ();
	$arr = array ();
	$current = & $xml_array;
	$repeated_tag_index = array ();
	
	foreach ($xml_values as $data)
  {
		unset ($attributes, $value);
		extract($data);
		$result = array ();
		$attributes_data = array ();
		if (isset ($value))
		{
			if ($priority == 'tag')
				$result = $value;
			else
				$result['value'] = $value;
		}
		if (isset ($attributes) and $get_attributes)
		{
			foreach ($attributes as $attr => $val)
			{
				if ($priority == 'tag')
					$attributes_data[$attr] = $val;
				else
					$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
			}
		}
		if ($type == "open")
		{
			$parent[$level -1] = & $current;
			if (!is_array($current) or (!in_array($tag, array_keys($current))))
			{
				$current[$tag] = $result;
				if ($attributes_data)
					$current[$tag . '_attr'] = $attributes_data;
				$repeated_tag_index[$tag . '_' . $level] = 1;
				$current = & $current[$tag];
			}
			else
			{
				if (isset ($current[$tag][0]))
				{
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					$repeated_tag_index[$tag . '_' . $level]++;
				}
				else
				{
					$current[$tag] = array (
						$current[$tag],
						$result
					);
					$repeated_tag_index[$tag . '_' . $level] = 2;
					if (isset ($current[$tag . '_attr']))
					{
						$current[$tag]['0_attr'] = $current[$tag . '_attr'];
						unset ($current[$tag . '_attr']);
					}
				}
				$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
				$current = & $current[$tag][$last_item_index];
			}
		}
		elseif ($type == "complete")
		{
			if (!isset ($current[$tag]))
			{
				$current[$tag] = $result;
				$repeated_tag_index[$tag . '_' . $level] = 1;
				if ($priority == 'tag' and $attributes_data)
					$current[$tag . '_attr'] = $attributes_data;
			}
			else
			{
				if (isset ($current[$tag][0]) and is_array($current[$tag]))
				{
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					if ($priority == 'tag' and $get_attributes and $attributes_data)
					{
						$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
					}
					$repeated_tag_index[$tag . '_' . $level]++;
				}
				else
				{
					$current[$tag] = array (
						$current[$tag],
						$result
					);
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $get_attributes)
					{
						if (isset ($current[$tag . '_attr']))
						{
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset ($current[$tag . '_attr']);
						}
						if ($attributes_data)
						{
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
					}
					$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
				}
			}
		}
		elseif ($type == 'close')
		{
			$current = & $parent[$level -1];
		}
	}
	return ($xml_array);
}


/**
 * Преобразует XML-строку в массив, упрощенный вариант
 */
function xml_to_array($XML)
{
	$XML = trim($XML);
	$returnVal = $XML;

	// Expand empty tags
	$emptyTag = '<(.*)/>';
	$fullTag = '<\\1></\\1>';
	$XML = preg_replace ("|$emptyTag|", $fullTag, $XML);

	$matches = array();
	if (preg_match_all('|<(.*)>(.*)</\\1>|Ums', trim($XML), $matches))
	{
		// Если есть элементы, тогда вернуть массив, иначе текст
		if (count($matches[1]) > 0) $returnVal = array();
		foreach ($matches[1] as $index => $outerXML)
		{
			$attribute = $outerXML;
			$value = xml_to_array($matches[2][$index]);
			if (! isset($returnVal[$attribute])) $returnVal[$attribute] = array();
				$returnVal[$attribute][] = $value;
		}
	}
	// Bring un-indexed singular arrays to a non-array value.
	if (is_array($returnVal)) foreach ($returnVal as $key => $value)
	{
		if (is_array($value) && count($value) == 1 && key($value) === 0)
		{
			$returnVal[$key] = $returnVal[$key][0];
		}
	}
	return $returnVal;
}

/**
 * Преобразует данные из формата SimpleXML в массив
 */
function simpleXMLToArray(SimpleXMLElement $xml,$attributesKey=null,$childrenKey=null,$valueKey=null){ 
	if($childrenKey && !is_string($childrenKey)){$childrenKey = '@children';} 
	if($attributesKey && !is_string($attributesKey)){$attributesKey = '@attributes';} 
	if($valueKey && !is_string($valueKey)){$valueKey = '@values';} 

	$return = array(); 
	$name = $xml->getName(); 
	$_value = trim((string)$xml); 
	if(!strlen($_value)){$_value = null;}; 

	if($_value!==null){ 
		if($valueKey){$return[$valueKey] = $_value;} 
		else{$return = $_value;} 
	} 

	$children = array(); 
	$first = true; 
	$el = "";
	foreach($xml->children() as $elementName => $child){ 
		$value = simpleXMLToArray($child,$attributesKey,$childrenKey,$valueKey); 
		
		if ($el != $elementName) { // Если элемент меняется и такой же еще не существует, то считаем, что это первый
			$el = $elementName;
			if  (!isset($children[$elementName])) {
				$first = true; 
			} else {
				$first = false; 
			}
		}
		
		if(isset($children[$elementName])){ 
			if(is_array($children[$elementName])){ 
				if($first){ 
					$temp = $children[$elementName]; 
					unset($children[$elementName]); 
					$children[$elementName][] = $temp; 
					$first=false; 
				} 
				$children[$elementName][] = $value; 
			}else{ 
				$children[$elementName] = array($children[$elementName],$value); 
			}
		}
		else{
			$children[$elementName] = $value; 
		}
	}
	if($children){ 
		if($childrenKey){$return[$childrenKey] = $children;} 
		else{$return = array_merge($return,$children);} 
	}

	$attributes = array(); 
	foreach($xml->attributes() as $name=>$value){ 
		$attributes[$name] = trim($value); 
	} 
	if($attributes){ 
		if($attributesKey){$return[$attributesKey] = $attributes;} 
		else{
			if (is_array($return)) {
				$return = array_merge($return, $attributes);
			} else {
				// TODO: добавить обработку вместо с $return
				print $return;
			}
		} 
	} 

	return $return; 
}

/**
 * Из строки $arr['UserTemplateData'], содержащей теги data, формируется массив для узлов XML.
 * @example
 * $nodes = fromDataTagsToNodesArray('<data class="data" id="diagnos">здоров</data>');
 * echo $nodes['diagnos']; // output здоров
 */
function fromDataTagsToNodesArray($str)
{
	$str = stripslashes($str);
	$str = htmlspecialchars_decode($str);
	$str = str_replace('$','',$str);
	$str = str_replace('&nbsp;','',$str);
	$str = str_replace('</data>','$',$str);
	
	$count_matches = preg_match_all('/<data\s*class="data"\s*id="([a-zA-Z0-9_\-]+)"(\s*name="[a-zA-Z0-9_\-]{0,}+"){0,}(\s*value="[a-zA-Z0-9_\-]{0,}+"){0,}>([^\$]*)/iu',$str,$matches,PREG_SET_ORDER);
	$nodes = array();
	foreach($matches as $match)
	{
		$nodes[$match[1]] = $match[4];
	}
	return $nodes;
}


/**
 * fromNodesArrayToXmlNodes
 */
function fromNodesArrayToXmlNodes($arr)
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
			$xml .= '<' . $key . '>' . htmlentities($value,ENT_NOQUOTES,'UTF-8') . '</' . $key . '>';
		}
	}
	return $xml;
}

/**
 * Заменяем некоторые html-мнемоники, изза которых выходит ошибка "Данные шаблона не прошли проверку по схеме"
 */
function htmlEntitiesReplace($str)
{
	return str_replace(
		array(
			'&amp;nbsp;',
			'&nbsp;',
			'&amp;laquo;',
			'&laquo;',
			'&amp;raquo;',
			'&raquo;',
			'&ndash;'
		),
		array(
			' ',
			' ',
			'«',
			'«',
			'»',
			'»',
			'-'
		),
		$str
	);
}

/**
 * Преобразует массив в XML
 */
function ArrayToXml($data, $rootNodeName = 'data', $xml=null)
{
	// turn off compatibility mode as simple xml throws a wobbly if you don't.
	if (ini_get('zend.ze1_compatibility_mode') == 1)
	{
		ini_set ('zend.ze1_compatibility_mode', 0);
	}
	if ($xml == null)
	{
		$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
	}
	// loop through the data passed in.
	foreach($data as $key => $value)
	{
		// no numeric keys in our xml please!
		if (is_numeric($key))
		{
			// make string key...
			$key = "arrayNode_". (string) $key;
		}
		// replace anything not alpha numeric
		$key = preg_replace('/[^a-z_0-9]/iu', '', $key);
		// if there is another array found recrusively call this function
		if (is_array($value))
		{
			$node = $xml->addChild($key);
			// recrusive call.
			ArrayToXml($value, $rootNodeName, $node);
		}
		else
		{
			// add single node.
			$value = htmlentities($value);
			$xml->addChild($key,$value);
		}
	}
	// pass back as string. or simple xml object if you want!
	return $xml->asXML();
}

/**
 * Преобразует xml шаблон в JSON
 */
function xml_template_to_json($xml_template)
{
	$tpl = new DOMDocument;
	$tpl->loadXML($xml_template);
	$xpath = new DOMXPath($tpl);
	$arts = $xpath->query("//*[count(arrayNode)=1]");
	foreach ( $arts as $dt )
	{
		$dt->appendChild($tpl->createElement('arrayNode'));
	}
	$xml_template = $tpl->saveXml($tpl->documentElement);
	//die();
	
	$templ_arr = XmlToArray($xml_template);
	XmlTemplateArrayPostProcess($templ_arr);
	$return = $templ_arr['data'];
	array_walk_recursive($return, 'ConvertFromUTF8ToWin1251');
	//var_dump($return);
	return $return;
}

/**
 * Очистка документа от лишних атрибутов и элементов
 */
function clearEvnDocument($document, $data = NULL) {

	// преобразуем поле дата в div чтобы loadHTML не ругался (если есть такой тэг)
	$document = str_replace("<data ", '<div ', $document);
	$document = str_replace("</data>", '</div>', $document);

	$dom = new DOMDocument;
	$dom->loadHTML('<?xml encoding="utf-8" ?>' . $document);

	// уберем лишниее элементы по имени класса
	removeDocumentNodes(array('right', 'noprint'), new DomXPath($dom));

	// уберем лишние атрибуты
	$elements = $dom->getElementsByTagName('*');
	$attributeList = array(
		'class',
		'id',
		'onmouseout',
		'onmouseover',
		'style',
		'title',
		'align'
	);

	foreach ($elements as $element) {
		removeHtmlAttributes($element, $attributeList);
	}

	// уберем все ненужные теги
	$tagList = array('br');
	removeTags($tagList, $dom);

	// особое форматирование для типа события EvnUslugaPar
	if (!empty($data) && !empty($data['EvnClass_SysNick']) && $data['EvnClass_SysNick'] === "EvnUslugaPar") {

		$mainDiv = $dom->getElementsByTagName('body')->item(0)->childNodes->item(0);
		$divCnt = 1;

		foreach ($mainDiv->childNodes as $node) {
			if (isset($node->tagName) && $node->tagName === 'div') {
				switch ($divCnt) {
					case 1:
						$node->setAttribute('class', 'info');
						break;
					case 2:
						$node->setAttribute('class', 'protocol');
						break;
				}
				$divCnt++;
			}
			if ($divCnt > 2) break;
		}
	}

	$document = $dom->saveHTML($dom->documentElement);
	$document = str_replace(array("\r", "\n", "\t"), '', $document); // очистим символы переноса строки

	return $document;
}

/**
 * Убрать атрибуты по списку
 */
function removeHtmlAttributes($element, $attributeList)
{
	foreach ($attributeList as $a) {
		$element->removeAttribute($a);
	}
}

/**
 * Убрать классы по списку
 */
function removeDocumentNodes($classList, $finder)
{
	foreach ($classList as $c) {
		$classname = $c;
		$removeNode = $finder->query("//*[contains(@class, '$classname')]")->item(0);
		if (!empty($removeNode)) $removeNode->parentNode->removeChild($removeNode);

	}
}

/**
 * Убрать теги по списку
 */
function removeTags($tagsList, $dom)
{
	foreach($tagsList as $tag) {
		foreach($dom->getElementsByTagName($tag) as $element) {
			$element->parentNode->removeChild($element);
		}
	}
}
