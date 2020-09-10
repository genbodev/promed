<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		private
 * @copyright	Copyright (c) 2009-2011 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		07.2013
 */

/**
 * Класс для работы со спецмаркерами в шаблонах, документах
 *
 * @package		Library
 * @author		Alexander Permyakov, Salakhov Rustam
 *
 * Типы маркеров и их специфика:
 * 1) SQL-спецмаркеры
 * Требуют определения атрибутов для конструктора запросов
 * или должен быть определен запрос в атрибуте query
 * Признак табличного маркера отсутствует
 * Замещаются на значения при создании документа
 * 2) Табличные маркеры
 * Требуют определения атрибутов для конструктора запросов
 * или должен быть определен запрос в атрибуте query
 * Имеют признак табличного маркера, который указывается в атрибуте is_table
 * Замещаются на значения при создании документа
 * 3) Спецмаркеры только для печати
 * Не используют запрос (атрибут query должен быть пуст) и конструктор запроса
 * Для каждого маркера должен быть в коде предопределен запрос и уникальный шаблон отображения результатов
 * Требуют указания имени шаблона для отображения в атрибуте field
 * Имеют признак табличного маркера, который указывается в атрибуте is_table
 * Замещаются на значения при печати документа
 * 4) Спецмаркеры объекта "Параметр и список значений"
 * Данные хранятся в отдельной таблице
 * Замещаются на код компонента ввода при отображении документа, предпросмотре шаблона
 * Замещаются на значения при печати документа
 */
class SwMarker{
	
	/**
	 * Строка для замещения неопознанных маркеров
	 *
	 * @var string
	 * @access public
	 */
	static $msg_undefined_marker = '[неопознанный маркер]';

	/**
	 * Строка для замещения маркеров c неопознанным шаблоном
	 *
	 * @var string
	 * @access public
	 */
	static $msg_undefined_template = '[неопознанный шаблон]';

	/**
	 * Строка для замещения маркеров, по которым не удалось получить данные из БД
	 *
	 * @var string
	 * @access public
	 */
	static $msg_error_marker = '[маркер: ошибка данных]';

	/**
	 * Instantiate the database model
	 */
	static $dbmodel = null;

	/**
	 * Instantiate the codeigniter
	 */
	static $CI = null;

	/**
	 * Конструктор
	 */
	function __construct() {
		$CI =& get_instance();
		$CI->load->database();
		$CI->load->model('FreeDocument_model', 'FreeDocument_model');
		self::$CI = $CI;
		self::$dbmodel = $CI->FreeDocument_model;
	}

	/**
	 * Обработка текста с маркерами документов
	 *
	 * @access	public
	 * @param	string	Исходный текст с маркерами в кодировке utf-8
	 * @param	int		Идентификатор события
	 * @param	array
	 * @param	int
	 * @param	array|null
	 * @return	string
	 */
	static function processingTextWithXmlMarkers($text, $evn_id, $options, $nestedCount = 0, &$xmlData = null, $nestedCountXmlMarkers = 0) {
		$nestedCountXmlMarkers++;
		if ($nestedCountXmlMarkers > 3) {
			return $text;
		}
		
		/*if (!empty($options['htmlentities'])) {
			$search = array('<','>',);
			$replace = array('!##','##!',);
			$text = str_replace($search, $replace, $text);
			$text = htmlspecialchars_decode($text);
		}*/
		$all_markers = self::foundXmlMarkers($text);
		if (false && empty($all_markers)) {
			var_dump($text);
			exit();
		}
		if (isset($options['From_Evn_id'])) {
			$evn_id = $options['From_Evn_id'];
		}
		$_markers = array();
		self::$CI->load->model('XmlTemplate6E_model');
		foreach ($all_markers as $type => $markers) {
			$data = array();
			if (is_array($xmlData)) {
				foreach($markers as $index => $marker) {
					$key = self::$CI->XmlTemplate6E_model->getMarkerKey($marker);
					if (isset($xmlData[$key])) {
						$_markers[] = array_merge($marker, array('content' => $xmlData[$key]));
						unset($markers[$index]);
					}
				}
			}
			if (count($markers) == 0) {
				continue;
			}
			try {
				self::$CI->load->library('swXmlTemplate');
				$instance = swXmlTemplate::getEvnXmlModelInstance();
				$EvnXml_id = null;
				if (!empty($options['EvnXml_id'])) {
					$EvnXml_id = $options['EvnXml_id'];
				} else if (!empty($options['cacheEvnXml'])) {
					$EvnXml_id = $options['cacheEvnXml'];
				}
				$data = $instance->buildAndExeQuery($evn_id, $type, $markers, $nestedCount, $EvnXml_id);

				//Рекурсивно обрабатывать маркеры документов внутри значений маркеров документов
				foreach ($data as $index => $value) {
					if ((string)$index == 'map') continue;

					$rec_markers = self::foundXmlMarkers($value);
					if (!empty($rec_markers) && !empty($data['map'][$index])) {
						$_options = $options;
						$_options['cacheEvnXml'] = $data['map'][$index];
						$data[$index] = self::processingTextWithXmlMarkers($value, $evn_id, $_options, $nestedCount, $xmlData, $nestedCountXmlMarkers);
					}
				}
			} catch (Exception $e) {
				//не пришло в голову лучшего способа вывести ошибку
				$data = array($e->getMessage());
			}
			foreach ($markers as $i => $marker) {
				$content = !empty($data[$i])?$data[$i]:'';
				if (is_array($xmlData)) {
					$key = self::$CI->XmlTemplate6E_model->getMarkerKey($marker);
					$xmlData[$key] = $content;
				}
				$_markers[] = array_merge($marker, array('content' => $content));
			}
		}
		foreach($_markers as $marker) {
			if (empty($marker['content'])) {
				$text = str_replace($marker['text'], '', $text);
			} else {
				// маркеров внутри данных быть не должно
				$marker['content'] = preg_replace('/<span class="XmlMarker([0-9]+) ([0-9a-z_]*)" codelist="([AB0-9\.\,]*)">([ABа-яА-ЯёЁ0-9 \/\,\.\:\-\(\)]*)<\/span>/u', "$4", $marker['content']);
				// Уфимцы попросили при перетягивании разделов из других документов сбрасывать им форматирование текста (размер и шрифт) refs #101124
				$marker['content'] = preg_replace('/font-size:[0-9]*px;/u', "", $marker['content']);
				$marker['content'] = preg_replace('/font-family:[A-Za-z\s,\-]*?;/u', "", $marker['content']);

				$text = str_replace($marker['text'], $marker['content'], $text);
			}
		}
		/*if (!empty($options['htmlentities'])) {
			$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
			$text = str_replace($replace, $search, $text);
		}*/
		return $text;
	}

	/**
	 * Поиск маркеров документов
	 *
	 * @access	public
	 * @param	string	Исходный текст с маркерами в кодировке utf-8
	 * @return	array
	 * @example
	 * 	$this->load->library('swMarker');
	 *	$found_arr = swMarker::foundXmlMarkers($data['text']);
	 */
	static function foundXmlMarkers($text) {
		$markers = array();
		$matches_all = array();

		$pattern = '/<span class="XmlMarker([0-9]+) ([0-9]+_[a-z]+_*[a-z]*_*[a-z]*)" codelist="([AB0-9\.\,]*)">[ABа-яА-ЯёЁ0-9 \/\,\.\:\-\(\)]*<\/span>/u';
		preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
		$matches_all = array_merge($matches_all, $matches);

		$pattern = '/<span class="XmlMarker([0-9]+) ([0-9]+_[a-z]+_*[a-z]*_*[a-z]*)">[ABа-яА-ЯёЁ0-9 \/\,\.\:\-\(\)]*<\/span>/u';
		preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
		$matches_all = array_merge($matches_all, $matches);

		foreach($matches_all as $row) {
			$data = explode('_', $row[2]);
			if (count($data) < 2) {
				continue;
			}
			$type = $row[1];
			$marker = array(
				'text' => $row[0],
			);
			if (in_array($row[1], array(1,2,3))) {
				$marker['XmlType_id'] = $data[0];
				if ( in_array($data[1],array('asc','desc')) ) {
					$marker['SqlOrderType_SysNick'] = $data[1];
					if ( isset($data[2]) ) {
						$marker['XmlDataLevel_SysNick'] = $data[2];
					}
				} else if ( in_array($data[1],array('first','last','firstused','lastused')) ) {
					$marker['XmlDataSelectType_SysNick'] = $data[1];
				} else {
					continue;
				}
				if ( isset($data[2]) ) {
					if (in_array($data[2],array('evn','priem'))) {
						$marker['XmlDataLevel_SysNick'] = $data[2];
					} else {
						$marker['XmlDataSection_SysNick'] = $data[2];
					}
				}
				if ( isset($data[3]) ) {
					$marker['XmlDataLevel_SysNick'] = $data[3];
				}
			} else {
				$marker['UslugaComplexAttributeType_id'] = $data[0];
				if ( in_array($data[1],array('asc','desc')) ) {
					$marker['SqlOrderType_SysNick'] = $data[1];
					if ( isset($data[2]) ) {
						$marker['XmlDataLevel_SysNick'] = $data[2];
					}
				} else {
					continue;
				}
				if ( isset($data[2]) ) {
					$marker['XmlDataSection_SysNick'] = $data[2];
				}
				if ( isset($data[3]) ) {
					$marker['XmlDataLevel_SysNick'] = $data[3];
				}
				if ( !empty($row[3]) ) {
					$marker['code2011list'] = $row[3];
				}
			}
			if (!isset($markers[$type])) {
				$markers[$type] = array();
			}
			$markers[$type][] = $marker;
		}
		return $markers;
	}

	/**
	 * Поиск маркеров строки результата лаборатоного исследования
	 *
	 * @access	public
	 * @param	string	$text Исходный текст с маркерами в кодировке utf-8
	 * @param	array	$data_arr
	 * @return	string
	 */
	static function processingTextWithLabMarkers($text, $data_arr = array()) {
		$matches = array();
		preg_match_all("/@#@([A-Za-z0-9\.]+)_([code|name|value|unit_of_measurement|norm_bound|crit_bound|commentrefvalues|commentlabsample|defectlabsample]+)/u" , $text, $matches, PREG_SET_ORDER);
		foreach($matches as $row) {
			$parameter = $row[2];
			switch ($parameter) {
				case 'value':
					$parameter = 'UslugaTest_ResultValue';
					break;
				case 'unit_of_measurement':
					$parameter = 'UslugaTest_ResultUnit';
					break;
				case 'norm_bound':
					$parameter = 'UslugaTest_ResultNorm';
					break;				
				case 'crit_bound':
					$parameter = 'UslugaTest_ResultCrit';
					break;
				case 'commentrefvalues':
					$parameter = 'UslugaTest_Comment';
					break;
				case 'commentlabsample':
					$parameter = 'EvnLabSample_Comment';
					break;
				case 'defectlabsample':
					$parameter = 'DefectCauseType_Name';
					break;
				case 'code':
					$parameter = 'UslugaComplex_Code';
					break;
				case 'name':
					$parameter = 'UslugaComplex_Name';
					break;
			}
			$index = (string) $row[1];
			//производим замещение
			if (is_array($data_arr[$index]) && array_key_exists($parameter, $data_arr[$index])) {
				$text = str_replace($row[0], $data_arr[$index][$parameter], $text);
			} else {
				$text = str_replace($row[0], self::$msg_error_marker, $text);
			}
		}
		return $text;
	}
	
	/**
	 * Поиск маркеров объекта с типом Анкета
	 */
	static function createAnketa($text, &$xmlData, $data, $isForPrint = false) {
		if(!preg_match_all("/@#@anketa_(\d+)/iu" , $text, $matches, PREG_SET_ORDER)) {
			return $text;
		}
		self::$CI->load->model('XmlTemplate6E_model');
		self::$CI->load->model('MedicalForm_model');

		foreach($matches as $place) {
			$id = array();
			$id[] = $place[1];
			
			$anketa_info = self::$CI->MedicalForm_model->getMedicalFormInfo(array('MedicalFormPerson_id'=>$place[1]));
			
			$html = $place[0];
			$block = '<b>Анкета "'.$anketa_info['MedicalForm_Name'].'"</b>';
			
			$data = self::$CI->XmlTemplate6E_model->getAnketMarkerContent($id);
			
			if($data[0]['success'] === true && !empty($data[0]['data']) && !empty($data[0]['data'][0]) && !empty($data[0]['data'][0]['content']) ) {
				$data = $data[0]['data'][0]['content'];
				$MedicalForm = $data['MedicalForm'];
				$MedicalFormData = $data['MedicalFormData'];
				foreach($MedicalForm as $question) {
					$answer = null;
					foreach($MedicalFormData as $answer1) {
						if($answer1['MedicalFormQuestion_id'] == $question['MedicalFormQuestion_id']) {
							$answer = $answer1;
						}
					}
					
					if($question['AnswerType_id'] == 11) {//дата
						$block .= "<div>".$question['MedicalFormQuestion_Name'].": ".(!empty($answer) ? $answer['DateValue'].' '.$answer['TimeValue']:'')."</div>";	
					} else if($question['AnswerType_id'] == 2) {//текст
						$block .= "<div>".$question['MedicalFormQuestion_Name'].": ".(!empty($answer) ? $answer['MedicalFormData_ValueText']:'')."</div>";
					} else if(!empty($question['children'])) {
						$block .= "<div>".$question['MedicalFormQuestion_Name'].": ";
						$childs_html = array(); //array of answer-html
						foreach($question['children'] as $child) {
							$child_html = "<span";
							if(!empty($answer) && $child['MedicalFormAnswers_id'] == $answer['MedicalFormAnswers_id']) {
								$child_html .=  ' style=text-decoration:underline';
							}
							$child_html .= ">".$child['MedicalFormAnswers_Name']."</span>";
							$childs_html[] = $child_html;
						}
						$block .= implode(', ', $childs_html);
					}
				}
				
			}
			
			$block = "<div ><span>".$block."</span></div>";

			$text = str_replace($html, $block, $text);
		}

		return $text;
	}

	/**
	 * Поиск маркеров объекта с типом Параметр и список значений
	 * с заменой их на html-код для отрисовки компонентов для редактирования на клиенте
	 *
	 * @access	public
	 * @param	string	$text Исходный текст с маркерами в кодировке utf-8
	 * @param	int		$evnxml_id Идентификатор документа
	 * @param	array	$evnxml_data_arr массив полученный из EvnXml.EvnXml_Data с помощью функции transformEvnXmlDataToArr в кодировке utf-8
	 * @return	string
	 */
	static function createParameterValueFields($text, $evnxml_id = 0, $evnxml_data_arr = array(), $is_for_print = false) {
		self::$CI->load->model('ParameterValue_model', 'ParameterValue');
		self::$CI->load->library('parser');
		$markers = self::foundParameterMarkers($text);
		$params = self::$CI->ParameterValue->getParameterFieldData($markers);
		$search=array();
		$replace=array();

		$checkboxGroupTpl = 'print_parameter_checkboxgroup';

		foreach($params as $id => $row) {
			// $id == $row['Parameter_id']
			$search[] = $row['marker'];
			$field_name = $row['field_name'];// == $row['Parameter_SysNick']
			$el_id_key = $field_name.'_'.$evnxml_id;
			$parameter_name = $row['Parameter_Name'];
			$value = empty($evnxml_data_arr[$field_name]) ? '0' : $evnxml_data_arr[$field_name];
			$value_list = array($value);
			//
			$listtype = 'undefined-listtype';
			switch(intval($row['ParameterValueListType_id'])) {
				case 1: 
					$listtype = 'combobox';
					break;
				case 2: 
					$listtype = 'checkboxgroup';
					$value_list = explode(',',$value);
					break;
				case 3: 
					$listtype = 'radiogroup';
					break;
			}
			$print_data = array('parameter_name' => $parameter_name, 'values' => array());
			$value_name_list = array();
			foreach($value_list as $value_id) {
				$value_id = intval($value_id);
				$value_name = empty($row['values'][$value_id]) ? 'не указано' : $row['values'][$value_id];
				$value_name_list[] = $value_name;
				$print_data['values'][] = array('value_id' => $value_id, 'value_name' => $value_name);
			}
			$value_str = implode('; ',$value_name_list);
			//
			foreach($row['values'] as &$name) {
				$name = htmlentities($name);
			}
			toUTF($row['Parameter_Name']);
			$jsondata = json_encode(array(
				'parameter_id' => $id,
				'parameter_name' => '',//сейчас нигде не выводится, если будет нужно, то нужно будет убрать спецсимволы из $row['Parameter_Name']
				'listtype' => $listtype,
				'value' => $value,
				'values' => $row['values']
			));
			if ($is_for_print && empty($value)) {
				$replace[] = '';
			} else if ($is_for_print && !empty($value) && in_array($listtype, array('combobox','radiogroup'))) {
				$replace[] = '<b>'.$parameter_name .':</b>&nbsp;&nbsp;'. $value_str.' ';
			} else if ($is_for_print && !empty($value)) {
				$replace[] = '<b>'.$parameter_name .':</b>&nbsp;&nbsp;'. $value_str.'<br>';
			} else {
				/*
				вариант с блочной версткой, но тут нужно править компонент комбика,
				чтобы выпадающий список позиционировался правильно с учетом float

				$replace[] = '<div class="columns block-parameter" id="wrap_'. $el_id_key .'">
	<div class="left">
		<div class="data-parameter" style="float:left;padding:5px 0px;">
			<span class="label-parameter">'. $parameter_name .'</span> <span id="output_'. $el_id_key .'" class="value-parameter">'. $value_str .'</span>
		</div>
		<div id="input_'. $el_id_key .'" class="'. $listtype .'-parameter input-area" style="display: none;"></div>
	</div>
	<div id="buttons_'. $el_id_key .'" class="toolbar right" style="position:static; display: none;" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById(\'wrap_'. $el_id_key .'\').style[\'backgroundColor\']=\'silver\';" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById(\'wrap_'. $el_id_key .'\').style[\'backgroundColor\']=\'transparent\';">
		<a class="button icon icon-fielddelete16" title="Удалить" onclick="sw.Promed.ParameterValueDelete(\''. $el_id_key .'\');"><span></span></a>
	</div>
</div>
<div id="json_'. $el_id_key .'" class="parametervalue" style="display: none;">'. trim($jsondata) .'</div>
<div style="clear:both;"></div>';
				*/
				/*
				switch ($listtype) {
					case 'combobox':
						$padding_top = 15;
						break;
					case 'radiogroup':
						$padding_top = 15;
						break;
					default:
						$padding_top = 15;
						break;
				}*/
                /*
				$padding_top = 15;
				if ($is_for_print) {
					$padding_top = 0;
				}
                // padding-top:'. $padding_top .'px; vertical-align: top; 
                */
				$replace[] = '<div class="block-parameter" id="wrap_'. $el_id_key .'" style="margin: 0; padding:0; max-width: 700px;">
	<table border="0" cellpadding="0" cellspacing="0" style="margin: 0; padding:0; max-width: 700px;">
		<tr>
			<td style="margin: 0; padding:0; white-space: nowrap; vertical-align: top;">
				<span class="label-parameter">'. $parameter_name .'&nbsp;&nbsp;</span>'.
				(($listtype!='combobox') 
				? '<br>' 
				: '</td><td style="margin: 0; padding:0; '. (($listtype=='combobox')?'width: 220px; ':' ') .'vertical-align: top;">')
				.'<div id="output_'. $el_id_key .'" class="value-parameter">'. $value_str .'</div>
				<div id="input_'. $el_id_key .'" class="'. $listtype .'-parameter input-area" style="display: none;"></div>
				<div id="json_'. $el_id_key .'" class="parametervalue" style="display: none;">'. trim($jsondata) .'</div>
			</td>
			<td style="margin: 0; padding:0; vertical-align: top;">
				<div id="buttons_'. $el_id_key .'" style="display: none;" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById(\'wrap_'. $el_id_key .'\').style[\'backgroundColor\']=\'silver\';" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById(\'wrap_'. $el_id_key .'\').style[\'backgroundColor\']=\'transparent\';">
					<a class="button icon icon-fielddelete16" title="Удалить" onclick="sw.Promed.ParameterValueDelete(\''. $el_id_key .'\');"><span></span></a>
				</div>
			</td>
		</tr>
	</table>
</div>';
			}
		}
		return str_replace($search, $replace, $text);
	}
	
	/**
	* Поиск маркеров объекта с типом Параметр и список значений
	*
	* @access	public
	* @param	string	Исходный текст с маркерами в кодировке utf-8
	* @return	array
	* @example
	* 	$this->load->library('swMarker'); 
	*	$found_arr = swMarker::foundParameterMarkers($data['text']);
	*/
	static function foundParameterMarkers($text) {
		$result = array();
		$matches = array();
		//сначала ищем в старом формате
		preg_match_all("/@#@parameter([0-9]+)_([0-9]+)/iu" , $text, $matches, PREG_SET_ORDER);
		foreach($matches as $row) {
			$result[] = array(
				'marker' => $row[0],
				'Parameter_id' => $row[1],
				'ParameterValueListType_id' => (empty($row[2])?1:$row[2])
			);
		}
		//ищем в новом формате
		self::$CI->load->model('ParameterValue_model', 'ParameterValue');
		$matches = array();
		$listTypes = implode('|', ParameterValue_model::$listTypes);
		preg_match_all("/@#@_([0-9]+)({$listTypes})([а-яА-ЯёЁ]*)/iu" , $text, $matches, PREG_SET_ORDER);
		foreach($matches as $row) {
			$parametervaluelisttype_id = 1;
			if (!empty($row[2]) ) {
				foreach (ParameterValue_model::$listTypes as $id => $alias) {
					if ($alias == $row[2]) {
						$parametervaluelisttype_id = $id;
						break;
					}
				}
			}
			$result[] = array(
				'marker' => $row[0],
				'Parameter_id' => $row[1],
				'ParameterValueListType_id' => $parametervaluelisttype_id
			);
		}
		// формат 2017
		// to-do: возможно, получится оптимизировать и объединить с поиском выше
		$matches = array();
		preg_match_all("/@#@_([0-9]+)_([0-9]+)({$listTypes})([а-яА-ЯёЁ]*)/iu" , $text, $matches, PREG_SET_ORDER);
		foreach($matches as $row) {
			$parametervaluelisttype_id = 1;
			if (!empty($row[3]) ) {
				foreach (ParameterValue_model::$listTypes as $id => $alias) {
					if ($alias == $row[3]) {
						$parametervaluelisttype_id = $id;
						break;
					}
				}
			}
			$result[] = array(
				'marker' => $row[0],
				'Parameter_id' => $row[1],
				'Parameter_suffix' => $row[2],
				'ParameterValueListType_id' => $parametervaluelisttype_id
			);
		}
		//var_export(array($listTypes, $text));
		return $result;
	}

	/**
	 * Обработка текста с маркерами
	 *
	 * @access	public
	 * @param	string	Исходный текст с маркерами в кодировке utf-8
	 * @param	int		Идентификатор события
	 * @param	array	the optional parameters (isPrint)
	 * @param	int
	 * @param	array
	 * @return	string
	 * @example
	 * 	$this->load->library('swMarker');
	 *	$data['text'] = swMarker::processingTextWithMarkers($data['text'], $data['Evn_id']);
	 */
	static function processingTextWithMarkers($text, $evn_id, $options = array(), $nestedCount = 0, &$xmlData = array(), $parseMarkers = false) {
		if (empty($text))
		{
			return 'Отсутствует исходный текст с маркерами (обработка текста с маркерами)';
		}
		/*if (empty($evn_id))
		{
			return 'Отсутствует идентификатор события (обработка текста с маркерами)';
		}*/

		//Признак, что эта функция используется при печати документа
		$options['isPrint'] = empty($options['isPrint'])?false:true;
		$options['htmlentities'] = empty($options['htmlentities'])?false:true;
		$options['EvnClass_id'] = empty($options['EvnClass_id'])?null:$options['EvnClass_id'];
		$options['From_Evn_id'] = empty($options['From_Evn_id'])?null:$options['From_Evn_id'];
		$options['EvnXml_id'] = $options['EvnXml_id'] ?? $options['cacheEvnXml'] ?? null;
		$options['restore'] = empty($options['restore'])?null:$options['restore'];

		$origXmlData = $xmlData;
		$marker_names = array();

		$text = self::processingTextWithXmlMarkers($text, $evn_id, $options, $nestedCount, $xmlData);
		//собираем список маркеров из текста
		$matches = array();
		preg_match_all("/@#@([а-яА-ЯЁё0-9]+)/u" , $text, $matches, PREG_PATTERN_ORDER);

		if (isset($matches[1]) && count($matches[1]) > 0)
		{					
			//если найдены маркеры начинаем обработку данных
			//приводим все маркеры к нижнему регистру
			foreach($matches[1] as &$name) {
				$name = mb_strtolower($name);
			}
			//var_dump($matches[1]);
			
			// Режим работы без Evn
			if (empty($evn_id)) {
				$markers = array();
				$evn_data = array();
				$tmp = self::$dbmodel->getMarkersDataForPerson();
				foreach($tmp as $row) {
					if ( in_array(mb_strtolower($row['FreeDocMarker_Name']), $matches[1]) ) {
						$marker = array(
							'id' => $row['FreeDocMarker_id'],
							'name' => $row['FreeDocMarker_Name'],
							'description' => $row['FreeDocMarker_Description'],
							'alias' => $row['FreeDocMarker_TableAlias'],
							'field' => $row['FreeDocMarker_Field'],
							'query' => $row['FreeDocMarker_Query'],
							'is_table' => $row['FreeDocMarker_IsTableValue'],
							'options' => $row['FreeDocMarker_Options']
						);
						$evn_data['evn_id'] = $options['Person_id'];
						$evn_data['evn_table'] = 'Person';
						$evn_data['evnxml_id'] = $options['EvnXml_id'];
						$evn_data['evnclass_id'] = 1;
						$evn_data['class_list'] = array(1);
						self::insertIntoMarkerArray($markers, $marker);
					}
				}
			}
			else {
				$tmp = self::$dbmodel->getMarkersDataByEvn($evn_id);
				if(empty($tmp) || !is_array($tmp))
				{
					$tmp = self::$dbmodel->getMarkersDataForUslugaPar($evn_id);
					if (empty($tmp))
					return $text;
				}
				$evn_data = array();
				$markers = array();
				foreach($tmp as $row) {
					if(empty($evn_data))
					{
						$evn_data['evn_id'] = $row['Evn_id'];
						$evn_data['evn_rid'] = $row['Evn_rid'];
						$evn_data['evn_pid'] = $row['Evn_pid'];
						$evn_data['evnxml_id'] = $options['EvnXml_id'];
						$evn_data['evn_table'] = $row['EvnClass_SysNick'];
						$evn_data['evnclass_id'] = $row['EvnClass_id0'];
						$evn_data['class_list'] = array();
						$next_id = 0;
						while(!empty($row['EvnClass_id'.$next_id])) {
							$evn_data['class_list'][] = $row['EvnClass_id'.$next_id];
							$next_id ++;
						}
					}
					if ( in_array(mb_strtolower($row['FreeDocMarker_Name']), $matches[1]) )
					{
						$marker = array(
							'id' => $row['FreeDocMarker_id'],
							'name' => $row['FreeDocMarker_Name'],
							'description' => $row['FreeDocMarker_Description'],
							'alias' => $row['FreeDocMarker_TableAlias'],
							'field' => $row['FreeDocMarker_Field'],
							'query' => $row['FreeDocMarker_Query'],
							'is_table' => $row['FreeDocMarker_IsTableValue'],
							'options' => $row['FreeDocMarker_Options']
						);
						self::insertIntoMarkerArray($markers, $marker);
						//var_dump($marker);
					}
				}
			}


			if (count($markers) > 0) {
				foreach($markers as $marker_id => $marker) {
					$marker_names['specMarker_'.$marker_id] = '@#@'.$marker['original_name'];
					if (isset($xmlData['specMarker_'.$marker_id]) && $options['restore'] == 'full' && $marker['original_name'] != 'ДатаВремяДокумента') {
						$value = html_entity_decode($xmlData['specMarker_'.$marker_id]);
						$text = str_replace('@#@'.$marker['original_name'], $value, $text);
						unset($markers[$marker_id]);
					}
				}

				self::buildTableChains($markers, $evn_data['class_list']);
				//var_dump($markers);
				
				//тут получаем данные, которыми будут замещаться спецмаркеры
				$data = array();
				
				//для обычных маркеров формируем и выполняем запрос
				//у маркера должны быть указаны false в $marker['is_table']
				$response = self::$dbmodel->buildAndExeDataQuery($markers, $evn_data, $options);
				//var_dump($response);
				if(isset($response['result']))
				{
					$data = $response['result'];
				}
				
				//для табличных маркеров используем отдельную функцию
				//у маркера должны быть указаны true в $marker['is_table'], строка запроса в $marker['query']
				$tmp_data = self::$dbmodel->getMarkerTableData($markers, $evn_data);
				if (count($data) > 0 || count($tmp_data) > 0)
				{
					$data = array_merge($data, $tmp_data);
				}
				
				if (true || $options['isPrint'])
				{
					//для шаблонных маркеров используем отдельную функцию
					//у маркера $marker['query'] должен быть пуст, должны быть указаны true в $marker['is_table'], шаблон в $marker['field']
					$tmp_data = self::getMarkerTemplateData($markers, $evn_data);
					if (count($data) > 0 || count($tmp_data) > 0)
					{
						$data = array_merge($data, $tmp_data);
					}
				}
				/*var_dump($matches[0]);
				var_dump($markers);
				var_dump($data);
				exit;*/
				//данные получены в $data, производим замещение
				foreach($markers as $marker_id => $marker) {
					$value = '';
					if ($marker['error']) {
						$value = self::$msg_error_marker;
					} else if (isset($data['MarkerData_'.$marker_id])) {
						$value = $data['MarkerData_'.$marker_id];
						if ($value instanceof DateTime) {
							$value = $value->format('d.m.Y');
						}
						if ($options['htmlentities']) {
							$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
						}
					}

					if ($marker['name'] == 'идпаицентштрихкод') {
						$value = "@#@EAN13_{$value}@#@"; // картинка со штрих-кодом будет вставлена при отображении документа
					}

					// http://phpclub.ru/talk/threads/str_ireplace-%D0%B8-%D0%BA%D0%B8%D1%80%D0%B8%D0%BB%D0%BB%D0%B8%D1%86%D0%B0.52884/
					if ($options['restore'] != 'simple') {
						$text = str_replace('@#@' . $marker['original_name'], $value, $text);
					}
					$xmlData['specMarker_'.$marker_id] = $value;	//Кэширование значение спецмаркера
				}
			}
			
			//скрываем неопознанные маркеры
			if ($options['restore'] != 'simple') {
				if (empty($evn_id)) {
					foreach ($matches[0] as $tmp) {
						$text = str_replace($tmp, '', $text);
					}
				} else {
					foreach ($matches[0] as $tmp) {
						$text = str_replace($tmp, self::$msg_undefined_marker, $text);
					}
				}
			}
		}

		//Обновляется кэш данных, если каких-то значений спецмаркеров в кэше ещё нет
		if (!empty($options['cacheEvnXml']) && count(array_diff_assoc($xmlData, $origXmlData)) > 0) {
			$template_field_data = array_map(function($id){
				return array('id' => $id);
			}, array_keys($xmlData));

			self::$CI->load->model('EvnXml6E_model');
			self::$CI->EvnXml6E_model->updateEvnXmlData(array(
				'EvnXml_id' => $options['cacheEvnXml'],
				'EvnXml_Data' => SwXmlTemplate::convertFormDataArrayToXml(array($xmlData)),
				'XmlSchema_Data' => SwXmlTemplate::createXmlSchemaData($template_field_data),
			));
		}

		if ($parseMarkers) {
			foreach($xmlData as $key => &$value) {
				if (isset($marker_names[$key]) || empty($value)) {
					continue;
				}

				$value = self::processingTextWithMarkers($value, $evn_id, $options, $nestedCount, $xmlData);

				foreach($marker_names as $marker_id => $marker_name) {
					if (isset($xmlData[$marker_id])) {
						$value = str_replace($marker_name, $xmlData[$marker_id], $value);
					}
				}
			}
		}

		return $text;//.' '.time();
	}// END processingTextWithMarkers 

	/**
	 * @param string $text
	 * @param array $xmlData
	 * @return string
	 */
	static function createInputBlocks($text, &$xmlData, $data, $isForPrint = false) {
		$regexp = '/{xmltemplateinputblock_(\w+)}/';

		if (!preg_match_all($regexp, $text, $matches)) {
			return $text;
		}

		self::$CI->load->model('XmlTemplate6E_model');
		$_XmlDataSections = self::$CI->XmlTemplate6E_model->loadXmlDataSectionList(array(
			'sysNicks' => $matches[1]
		));
		$XmlDataSections = array();
		foreach ($_XmlDataSections as $item) {
			$key = $item['XmlDataSection_SysNick'];
			$XmlDataSections[$key] = $item;
		}

		self::$CI->load->library('swXmlTemplate');
		$xmlDataLabel = swXmlTemplate::getXmlTemplateLabels($data['XmlTemplate_Data']);

		foreach($matches[1] as $index => $nick) {
			if (!isset($XmlDataSections[$nick]) && strpos($nick, 'autoname') === false) {
				continue;
			}

			if (isset($xmlData[$nick]) && !empty($data['Evn_id'])) {
				$xmlData[$nick] = swMarker::processingTextWithMarkers($xmlData[$nick], $data['Evn_id'], array(
					'isPrint' => true,
					'From_Evn_id' => $data['Evn_id'],
				));
			}

			if (strpos($nick, 'autoname') !== false) {
				$key = 'autoname';
			} else {
				$key = $nick;
			}

			$place = $matches[0][$index];
			$name = $XmlDataSections[$key]['XmlDataSection_Name'];
			$caption = trim(!empty($xmlDataLabel[$nick])?$xmlDataLabel[$nick]:$name);
			$strip_caption = strip_tags($caption);
			if (!empty($strip_caption) && mb_substr($strip_caption, -1) != ':') {
				$caption .= ':';
			}

			$block = "
				<div class=\"template-block\" id=\"block_{$nick}\">
					<p class=\"template-block-caption\" id=\"caption_{$nick}\">
						<span style=\"font-weight: bold; font-size:10px;\">{$caption} </span>
					</p>
					<div class=\"template-block-data\" id=\"data_{$nick}\">
						{{$nick}}
					</div>
				</div>
			";

			$text = str_replace($place, $block, $text);
		}

		return $text;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	static function createMarkerBlocks($text, $xmlData, $isForPrint = false) {
		$placeRe = '/{marker_.+?endmarker}/';
		$markerRe = '/{(marker_.+)_[0-9]+ data="(.*)" endmarker}/';

		if (!preg_match_all($placeRe, $text, $places)) {
			return $text;
		}

		foreach($places[0] as $place) {
			preg_match($markerRe, $place, $matches);

			$markerKey = $matches[1];
			$markerData = json_decode(str_replace('\\\\', '\\', $matches[2]), true);

			$content = isset($xmlData[$markerKey])?$xmlData[$markerKey]:'';
			$code = $markerData['XmlMarkerType_Code']['value'];
			$code2011list = isset($markerData['code2011list'])?$markerData['code2011list']['value']:'';
			$_markerData = array();

			foreach($markerData as $key => $item) {
				if (!in_array($key, array('XmlMarkerType_Code', 'code2011list'))) {
					$_markerData[] = $item['value'];
				}
			}
			$markerDataStr = implode('_', $_markerData);

			$block = "
				<div >
					<span class=\"XmlMarker{$code} $markerDataStr\" codelist=\"{$code2011list}\">
						{$content}
					</span>
				</div>
			";

			$text = str_replace($place, $block, $text);
		}

		return $text;
	}

	static function checkIsDataTextWithMarkers($text, $evn_id, $options = array()){

		$markers_empty = array();
		$markers_found = array();

		if (empty($text))
		{
			return 'Отсутствует исходный текст с маркерами (обработка текста с маркерами)';
		}
		/*if (empty($evn_id))
		{
			return 'Отсутствует идентификатор события (обработка текста с маркерами)';
		}*/

		//Признак, что эта функция используется при печати документа
		$options['isPrint'] = empty($options['isPrint'])?false:true;
		$options['htmlentities'] = empty($options['htmlentities'])?false:true;
		$options['EvnClass_id'] = empty($options['EvnClass_id'])?null:$options['EvnClass_id'];
		$options['From_Evn_id'] = empty($options['From_Evn_id'])?null:$options['From_Evn_id'];

		$text = self::processingTextWithXmlMarkers($text, $evn_id, $options);
		//собираем список маркеров из текста
		$matches = array();
		preg_match_all("/@#@([а-яА-ЯЁё0-9]+)/u" , $text, $matches, PREG_PATTERN_ORDER);

		// Array (
		// [0] => Array (
		// 		[0] => @#@НомерКВС
		// 		[1] => @#@АдресПроживанияПациента
		// 		[2] => @#@АдресРегистрацииПациента
		// )
		//
		// [1] => Array (
		// 		[0] => НомерКВС
		// 		[1] => АдресПроживанияПациента
		// 		[2] => АдресРегистрацииПациента
		// ) )
		// print_r($matches);die;



		if (isset($matches[1]) && count($matches[1]) > 0)
		{

			$matches_initCase = $matches[1];

			//если найдены маркеры начинаем обработку данных
			//приводим все маркеры к нижнему регистру
			foreach($matches[1] as &$name) {
				$name = mb_strtolower($name);
			}
			//var_dump($matches[1]);

			// Режим работы без Evn
			if (empty($evn_id)) {
				$markers = array();
				$evn_data = array();
				$tmp = self::$dbmodel->getMarkersDataForPerson();
				foreach($tmp as $row) {
					if ( in_array(mb_strtolower($row['FreeDocMarker_Name']), $matches[1]) ) {

						$markers_found[] = ($row['FreeDocMarker_Name']);

						$marker = array(
							'id' => $row['FreeDocMarker_id'],
							'name' => $row['FreeDocMarker_Name'],
							'description' => $row['FreeDocMarker_Description'],
							'alias' => $row['FreeDocMarker_TableAlias'],
							'field' => $row['FreeDocMarker_Field'],
							'query' => $row['FreeDocMarker_Query'],
							'is_table' => $row['FreeDocMarker_IsTableValue'],
							'options' => $row['FreeDocMarker_Options']
						);
						$evn_data['evn_id'] = $options['Person_id'];
						$evn_data['evn_table'] = 'Person';
						$evn_data['evnclass_id'] = 1;
						$evn_data['class_list'] = array(1);
						self::insertIntoMarkerArray($markers, $marker);
					}
				}
			}
			else {
				$tmp = self::$dbmodel->getMarkersDataByEvn($evn_id);
				if(empty($tmp) || !is_array($tmp))
				{
					return $text;
				}
				$evn_data = array();
				$markers = array();
				foreach($tmp as $row) {
					if(empty($evn_data))
					{
						$evn_data['evn_id'] = $row['Evn_id'];
						$evn_data['evn_rid'] = $row['Evn_rid'];
						$evn_data['evn_pid'] = $row['Evn_pid'];
						$evn_data['evn_table'] = $row['EvnClass_SysNick'];
						$evn_data['evnclass_id'] = $row['EvnClass_id0'];
						$evn_data['class_list'] = array();
						$next_id = 0;
						while(!empty($row['EvnClass_id'.$next_id])) {
							$evn_data['class_list'][] = $row['EvnClass_id'.$next_id];
							$next_id ++;
						}
					}
					if ( in_array(mb_strtolower($row['FreeDocMarker_Name']), $matches[1]) )
					{

						$markers_found[] = ($row['FreeDocMarker_Name']);

						$marker = array(
							'id' => $row['FreeDocMarker_id'],
							'name' => $row['FreeDocMarker_Name'],
							'description' => $row['FreeDocMarker_Description'],
							'alias' => $row['FreeDocMarker_TableAlias'],
							'field' => $row['FreeDocMarker_Field'],
							'query' => $row['FreeDocMarker_Query'],
							'is_table' => $row['FreeDocMarker_IsTableValue'],
							'options' => $row['FreeDocMarker_Options']
						);
						self::insertIntoMarkerArray($markers, $marker);
						//var_dump($marker);
					}
				}
			}


			if (count($markers) > 0)
			{
				self::buildTableChains($markers, $evn_data['class_list']);
				//var_dump($markers);

				//тут получаем данные, которыми будут замещаться спецмаркеры
				$data = array();

				//для обычных маркеров формируем и выполняем запрос
				//у маркера должны быть указаны false в $marker['is_table']
				$response = self::$dbmodel->buildAndExeDataQuery($markers, $evn_data);
				//var_dump($response);
				if(isset($response['result']))
				{
					$data = $response['result'];
				}

				//для табличных маркеров используем отдельную функцию
				//у маркера должны быть указаны true в $marker['is_table'], строка запроса в $marker['query']
				$tmp_data = self::$dbmodel->getMarkerTableData($markers, $evn_data);
				if (count($data) > 0 || count($tmp_data) > 0)
				{
					$data = array_merge($data, $tmp_data);
				}

				if (true || $options['isPrint'])
				{
					//для шаблонных маркеров используем отдельную функцию
					//у маркера $marker['query'] должен быть пуст, должны быть указаны true в $marker['is_table'], шаблон в $marker['field']
					$tmp_data = self::getMarkerTemplateData($markers, $evn_data);
					if (count($data) > 0 || count($tmp_data) > 0)
					{
						$data = array_merge($data, $tmp_data);
					}
				}
				/*var_dump($matches[0]);
				var_dump($markers);
				var_dump($data);
				exit;*/
				//данные получены в $data, производим замещение
				foreach($markers as $marker_id => $marker) {
					$value = '';
					if ($marker['error']) {
						$value = self::$msg_error_marker;
					} else if (isset($data['MarkerData_'.$marker_id])) {
						$value = $data['MarkerData_'.$marker_id];
						if ($value instanceof DateTime) {
							$value = $value->format('d.m.Y');
						}
						if ($options['htmlentities']) {
							$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
						}
					}

					if ($marker['name'] == 'идпаицентштрихкод') {
						$value = "@#@EAN13_{$value}@#@"; // картинка со штрих-кодом будет вставлена при отображении документа
					}


					if(strlen($value) != 0){
						$markers_found[] = ($marker['original_name']);
					}

					// http://phpclub.ru/talk/threads/str_ireplace-%D0%B8-%D0%BA%D0%B8%D1%80%D0%B8%D0%BB%D0%BB%D0%B8%D1%86%D0%B0.52884/
					$text = str_replace('@#@'.$marker['original_name'], $value, $text);
				}
			}

			//скрываем неопознанные маркеры
			if (empty($evn_id)) {
				foreach($matches[0] as $tmp) {
					$text = str_replace($tmp, '', $text);
				}
			} else {
				foreach($matches[0] as $tmp) {
					$text = str_replace($tmp, self::$msg_undefined_marker, $text);
				}
			}

			foreach($matches_initCase as $marker_name_nosymbols){
				if( ! in_array($marker_name_nosymbols, $markers_found)){
					$markers_empty[] = '@#@'.$marker_name_nosymbols;
				}
			}

		}


		return array_unique($markers_empty);

	}

	/**
	* вспомогательная функция для insertIntoMarkerArray
	*
	* @access	private
	* @param	array	
	* @param	array	
	* @return	array
	*/
	private static function getMarkerTemplateData($markers, $evn_data) {
		$data = array();
		$input_data = getSessionParams();
		self::$CI->load->library('swFilterResponse');
		foreach($markers as $marker_id => $marker) {
			if (!$marker['error'] && $marker['is_table'] && empty($marker['query']) && !empty($marker['field']))
			{
				$html = '';
				$params = array();
				$params['template'] = $marker['field'];
				$params['isList'] = true;
				switch($marker['field']) {
					case 'the_patient_is_being_treated':
						self::$CI->load->model('EvnUslugaTelemed_model');
						$html = self::$CI->EvnUslugaTelemed_model->getThePatientIsBeingTreatedHtml($evn_data['evn_id']);
						break;
					case 'marker_tooth_map':
						//self::$CI->load->model('PersonToothCard_model', 'PersonToothCard_model');
						$input_data['EvnVizitPLStom_id'] = $evn_data['evn_id'];

						// берём адрес из конфига
						$PromedURL = self::$CI->config->item('PromedURL');
						if ( self::$CI->config->item('PROMED_REGION_NAME') == 'ufa' && self::$CI->config->item('IS_DEBUG') == '1') {
							$input_data['base_url'] = 'https://ufa.swn.local';
						} else if ( !empty($PromedURL) && strpos($PromedURL, 'local') >= 0) {
							$input_data['base_url'] = $PromedURL;
						} else {
							self::$CI->load->helper('url');
							$input_data['base_url'] = base_url();
							if (empty($input_data['base_url']) || false !== strpos($input_data['base_url'], 'your-site')) {
								if (isset($_SERVER['HTTPS']) || (isset($_SERVER['SERVER_PORT']) && 443 == $_SERVER['SERVER_PORT'])) {
									$input_data['base_url'] = 'https://';
									$defPort = 443;
								} else {
									$input_data['base_url'] = 'http://';
									$defPort = 80;
								}
								if (false && isset($_SERVER['HTTP_HOST'])) {
									$input_data['base_url'] .= $_SERVER['HTTP_HOST'];
								} else {
									$input_data['base_url'] .= $_SERVER['SERVER_NAME'];
									if (isset($_SERVER['SERVER_PORT']) && $defPort != $_SERVER['SERVER_PORT'] && $defPort != 443) {
										$input_data['base_url'] .= ':' . $_SERVER['SERVER_PORT'];
									}
								}
								$input_data['base_url'] .= '/';
							}
						}
						$params['template'] = 'stom/ToothMap_marker';
						$params['isList'] = false;
						$params['data'] = $input_data;
						//$params['data'] = self::$CI->PersonToothCard_model->doLoadMarkerData($input_data);
						break;
					case 'marker_parodontogram':
						self::$CI->load->model('Parodontogram_model', 'Parodontogram_model');
						$input_data['EvnVizitPLStom_id'] = $evn_data['evn_id'];
						$params['template'] = 'stom/parodontogramma_layout';
						$params['isList'] = false;
						$params['data'] = self::$CI->Parodontogram_model->doLoadMarkerData($input_data);
						break;
					case 'session_medpersonal_fio':
						self::$CI->load->model('MedPersonal_model', 'MedPersonal_model');
						$html = self::$CI->MedPersonal_model->getUserMedPersonalFio($input_data['session']['medpersonal_id']);
						break;
					case 'session_medpersonal_fin':
						$html = '';
						$user = pmAuthUser::find($_SESSION['login']);

						if ($user) {
							// Фамилия
							if (!empty($user->surname)) {
								$html .= $user->surname;
							}
							// Имя
							if (!empty($user->firname)) {
								$html .= ' ' . mb_substr($user->firname, 0, 1) . '.';
							}
							// Отчество
							if (!empty($user->secname)) {
								if (empty($user->firname)) {
									$html .= ' ';
								} 
								$html .= mb_substr($user->secname, 0, 1) . '.';
							}

						}
						break;
					case 'print_toothcard':
						self::$CI->load->model('EvnVizit_model', 'EvnVizit_model');
						$input_data['EvnVizitPLStom_id'] = $evn_data['evn_id'];
						$params['isList'] = false;
						$params['data'] = self::$CI->EvnVizit_model->getToothCard($input_data);
						break;
					case 'print_list_evndiagpl':
						self::$CI->load->model('EvnDiag_model', 'EvnDiag_model');
						$input_data['EvnDiagPL_pid'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->EvnDiag_model->getEvnDiagPLViewData($input_data);
						break;
					case 'print_list_evndiagsection':
						self::$CI->load->model('EvnDiag_model', 'EvnDiag_model');
						$input_data['EvnDiagPS_pid'] = $evn_data['evn_id'];
						$input_data['class'] = 'EvnDiagPSSect';
						$params['data'] = self::$CI->EvnDiag_model->loadEvnDiagPSGrid($input_data);
						break;
					case 'print_list_evndiagps':
						self::$CI->load->model('EvnDiag_model', 'EvnDiag_model');
						$input_data['EvnDiagPS_rid'] = $evn_data['evn_rid'];
						$input_data['class'] = 'EvnDiagPSSect';
						$params['data'] = self::$CI->EvnDiag_model->loadEvnDiagPSGrid($input_data);
						break;
					case 'print_list_evnstick':
						self::$CI->load->model('EvnStick_model', 'EvnStick_model');
						$input_data['EvnStick_pid'] = $evn_data['evn_rid'];
						$params['data'] = self::$CI->EvnStick_model->getEvnStickMarkerData($input_data);
						break;
					case 'print_evnxml_survey_header':
						self::$CI->load->model('EvnXml6E_model');
						$input_data['Evn_id'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->EvnXml6E_model->getSurveyHeaderData($input_data);
						break;
					case 'print_evnprescr':
						self::$CI->load->model('EvnPrescr_model', 'EvnPrescr_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->EvnPrescr_model->getEvnPrescrPrintData($input_data);
						break;
					case 'print_evnprescrregime':
						self::$CI->load->model('EvnPrescr_model', 'EvnPrescr_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->EvnPrescr_model->getEvnPrescrRegimePrintData($input_data);
						break;
					case 'print_evnprescrdiet':
						self::$CI->load->model('EvnPrescr_model', 'EvnPrescr_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->EvnPrescr_model->getEvnPrescrDietPrintData($input_data);
						break;
					case 'print_evnprescroper_list':
						self::$CI->load->model('EvnPrescrOper_model', 'EvnPrescrOper_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->EvnPrescrOper_model->getPrintData($input_data);
						break;
					case 'print_evnprescrproc_list':
						self::$CI->load->model('EvnPrescrProc_model', 'EvnPrescrProc_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->EvnPrescrProc_model->getPrintData($input_data);
						break;
					case 'print_evnprescrtreat_list':
						self::$CI->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->EvnPrescrTreat_model->getPrintData($input_data);
						break;
					case 'print_evnscreening_list':
						self::$CI->load->model('EvnDirection_model', 'EvnDirection_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->EvnDirection_model->getScreeningPrintData($input_data);
						break;
					case 'print_consultation_list':
						self::$CI->load->model('EvnDirection_model', 'EvnDirection_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->EvnDirection_model->getConsultationPrintData($input_data);
						break;
					case 'print_hospitalisation_list':
						self::$CI->load->model('EvnDirection_model', 'EvnDirection_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->EvnDirection_model->getHospitalisationPrintData($input_data);
						break;
					case 'print_direction_list':
						self::$CI->load->model('EvnDirection_model', 'EvnDirection_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->EvnDirection_model->getDirectionPrintData($input_data);
						break;
					case 'print_evnrecept_list':
						self::$CI->load->model('dlo_EvnRecept_model', 'dlo_EvnRecept_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$params['data'] = self::$CI->dlo_EvnRecept_model->getEvnReceptPrintData($input_data);
						break;
					case 'marker_lab_tests':
						self::$CI->load->model('EvnLabRequest_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$params['template'] = 'marker_lab_tests';
						$params['isList'] = false;
						$params['data'] = self::$CI->EvnLabRequest_model->getLabTestsPrintData($input_data);
						break;
					case 'print_lab_tests':
						self::$CI->load->model('EvnLabRequest_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$input_data['EvnClass_SysNick'] = 'EvnVizitPL';
						$params['template'] = 'marker_lab_tests';
						$params['isList'] = false;
						$params['data'] = self::$CI->EvnLabRequest_model->getLabTestsPrintData($input_data);
						break;
					case 'marker_LabProt':
						self::$CI->load->model('EvnLabRequest_model');
						$input_data['Evn_pid'] = $evn_data['evn_id'];
						$input_data['isVert'] = true;
						$params['template'] = 'marker_LabProt';
						$params['isList'] = false;
						$params['data'] = self::$CI->EvnLabRequest_model->getLabTestsPrintData($input_data);
						break;
					default:
						$html = self::$msg_undefined_template;
				}
				
				//если данных нет, то будем замещать спецмаркер пустой строкой 
				if (!empty($params['data']))
				{
					self::$CI->load->library('parser');
					if (isset($params['data'][0])) {
						$parse_data = array(
							'items' => '',
							'client' => !empty($input_data['session'])?$input_data['session']['client']:''
						);
						if ( $params['isList'] == false ) {
							$parse_data = $params['data'];
						}
						else if(empty($params['template_item']))
						{
							$parse_data['items'] = $params['data'];
						}
						else
						{
							foreach($params['data'] as $i => &$object_data)
							{
								$parse_data['items'] .= self::$CI->parser->parse($params['template_item'], $object_data, true);
							}
						}
					} else {
						$parse_data = array_merge($params['data'], array(
							'client' => !empty($input_data['session']['client'])?$input_data['session']['client']:''
						));
					}

					$html = self::$CI->parser->parse($params['template'], $parse_data, true);
					if ($marker['field'] == 'marker_lab_tests' || $marker['field'] == 'print_lab_tests') {
						$html = preg_replace('/[\x00-\x1F\x7F]/u', '', $html); // выпиливаем посторонние символы, т.к. они не сохраняюстя в XML-поле.
					}
					//var_dump($html);
				}
				/*else
				{
					$html = '<!-- '. $marker['field'] .' '. $evn_data['evn_id'] .' -->';
				}*/

				$data['MarkerData_'.$marker_id] = $html;
			}		
		}
		return $data;
	}

	/**
	* вспомогательная функция для insertIntoMarkerArray
	*
	* @access	private
	* @param	array	
	* @param	string	
	* @param	string	
	* @param	string	
	* @return	string
	*/
	private static function selectValue($array, $field, $alternative_field = '', $default_value = ''){
		$value = '';
		if (isset($array[$field])) {
			$value = $array[$field];
		} else if (!empty($alternative_field) && isset($array[$alternative_field])) {
			$value = $array[$alternative_field];
		} else {
			$value = $default_value;
		}
		return $value;
	}
	
	/**
	* вспомогательная функция для processingTextWithMarkers
	* подготовка информации о маркере, для дальнейшей обработке в составе массива
	*
	* @access	private
	* @param	array	markers - Результирущий массив
	* @param	array	marker - данные маркера
	* @return	void
	*/
	private static function insertIntoMarkerArray(&$markers, $marker) { //
		$markers[self::selectValue($marker, 'id', 'FreeDocMarker_id', 0)] = array(
			'name' => mb_strtolower(self::selectValue($marker, 'name', 'FreeDocMarker_Name')),
			'original_name' => self::selectValue($marker, 'name', 'FreeDocMarker_Name'),
			'alias' => self::selectValue($marker, 'alias', 'FreeDocMarker_TableAlias'),
			'field' => self::selectValue($marker, 'field', 'FreeDocMarker_Field'),
			'query' => self::selectValue($marker, 'query', 'FreeDocMarker_Query'),
			'is_table' => ((self::selectValue($marker, 'is_table', 'FreeDocMarker_IsTableValue')) == 2),
			'options' => self::selectValue($marker, 'options', 'FreeDocMarker_Options'),
			'table_chain' => array(),
			'error' => false
		);
	}
	
	/**
	* вспомогательная функция для processingTextWithMarkers
	* заполняем цепочки таблиц для маркеров
	*
	* @access	private
	* @param	array	markers - Результирущий массив
	* @param	mixed	идентификатор или список идентификаторов класса события
	* @return	void
	*/
	static function buildTableChains(&$markers, $evnclass_id) {
		$max_chain_len = 50; //максимальная длинна цепочек, для предотвращения зацикливаний
	
		for ($i = 0; $i < $max_chain_len; $i++) {
			$unfinished = array();
			foreach($markers as $key => $marker) {
				$chain_len = count($marker['table_chain']);
				if (!empty($marker['alias'])) {
					$alias = $chain_len == 0 ? $marker['alias'] : $marker['table_chain'][$chain_len-1]['linked_alias'];										
					if (!empty($alias))
						$unfinished[] = $alias;
				}
			}
			
			if (count($unfinished) > 0) { //если есть неоконченые цепочки извлекаем по ним данные
				$chain_sections = array();
				$c_sec = self::$dbmodel->getChainSections($unfinished, $evnclass_id);				
				foreach($c_sec as $sec) {
					$chain_sections[$sec['alias']] = $sec;
				}
					
				foreach($markers as $key => $marker) {
					$chain_len = count($marker['table_chain']);
					if (!empty($marker['alias'])) {
						$alias = $chain_len == 0 ? $marker['alias'] : $marker['table_chain'][$chain_len-1]['linked_alias'];
						if (!empty($alias) && isset($chain_sections[$alias])) {
							$markers[$key]['table_chain'][] = $chain_sections[$alias];
						}
					}
				}
			} else //иначе прерываем цикл
				$i = $max_chain_len;
		}
		
		//по окончанию обработки ищем неоконченные цепи и помечаем цепочки как ошибочные
		foreach($markers as $key => $marker) {
			$chain_len = count($marker['table_chain']);
			if (!empty($marker['alias'])) {
				if ($chain_len == 0 || !empty($markers[$key]['table_chain'][$chain_len-1]['linked_alias']))
					$markers[$key]['error'] = true;
			}
		}
	}
}
