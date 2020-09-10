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
 * Вспомогательная библиотека для работы с Xml-документами
 *
 * @package		XmlTemplate
 * @author		Alexander Permyakov
 *
 * @property EvnXmlVizit_model EvnXmlVizit_model
 */
class SwEvnXml
{
	/**
	 * Тип документа "Документ в свободной форме" и его разновидности для стационара
	 * Этот тип должны иметь все записи из списка раздела "Документы"
	 * Учетный документ может иметь множество документов этого типа.
	 */
	const MULTIPLE_DOCUMENT_TYPE_ID = 2;
	const STAC_PROTOCOL_TYPE_ID = 8;
	const STAC_RECORD_TYPE_ID = 9;
	const STAC_EPIKRIZ_TYPE_ID = 10;
	/**
	 * Тип документа "Протокол осмотра"
	 * Этот тип должен иметь каждый документ из раздела "Осмотр" посещения
	 * Каждое посещение может иметь только один документ этого типа
	 */
	const EVN_VIZIT_PROTOCOL_TYPE_ID = 3;
	/**
	 * Тип документа "Протокол операции"
	 */
	const EVN_USLUGA_OPER_PROTOCOL_TYPE_ID = 17;
	
	/**
	 * Тип документа "Протокол анестезии"
	 */
	const EVN_USLUGA_NARCOSIS_PROTOCOL_TYPE_ID = 22;
	
	/**
	 * Тип документа "Протокол оказания услуги"
	 * Этот тип должен иметь каждый документ из раздела "Специфика" события оказания услуги
	 * Каждое событие оказания услуги может иметь только один документ этого типа
	 */
	const EVN_USLUGA_PROTOCOL_TYPE_ID = 4;
	/**
	 * Тип документа "Протокол лабораторной услуги"
	 */
	const LAB_USLUGA_PROTOCOL_TYPE_ID = 7;

	private static $ci_instance;

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
	 * @param $doc
	 */
	public static function processingNodeValue(&$doc)
	{
		// https://redmine.swan.perm.ru/issues/12618
		// В некоторых местах замена переноса строки на <br /> не требуется, т.к. в самом шаблоне уже указан <br />
		// Поэтому поголовная замена \n на <br /> заменена регуляркой

		// $doc = str_replace("\n", "<br />\n", $doc);
		$doc = preg_replace("/(\<br\>|\<br\/\>|\<br \/\>)?(\n|\n\r|\r\n)+/u", "<br />", $doc);
	}

	/**
	 * Получение HTML-документа для отображения в панели просмотра
	 * @param array $xml_data Массив атрибутов документа полученных методом EvnXmlBase_model::doLoadEvnXmlPanel
	 * @param array $parse_data
	 * @param array $object_data
	 * @return string
	 * @throws Exception Чтобы вывести ошибку, надо выбросить исключение
	 */
	public static function doHtmlView($xml_data,&$parse_data,&$object_data, $allowDefaultSettings = true, $parse_markers = true) {
		if ($xml_data instanceof Exception) {
			throw $xml_data;
		}
		if (empty($xml_data) || !is_array($xml_data)) {
			//var_dump($xml_data);
			throw new Exception('Нет данных документа');
		}
		$xml_data = $xml_data[0];

		if (self::LAB_USLUGA_PROTOCOL_TYPE_ID == $xml_data['XmlType_id'] && empty($xml_data['XmlTemplate_id'])) {
			// этот тип документов содержит готовый HTML-документ
			if (empty($xml_data['XmlTemplate_HtmlTemplate'])) {
				//var_dump($xml_data);
				throw new Exception('Нет данных HTML-документа');
			}
			return $xml_data['XmlTemplate_HtmlTemplate'];
		}

		self::getCiInstance()->load->library('swXmlTemplate');

		//костыль от поломки вёрстки ЭМК
		$xml_data['XmlTemplate_HtmlTemplate'] = self::closeTags($xml_data['XmlTemplate_HtmlTemplate']);

		//$template = swXmlTemplate::getHtmlDoc($xml_data, false);
		$evn_xml_data = toUTF($xml_data['EvnXml_Data']);
		$html = swXmlTemplate::processingXmlToHtml($evn_xml_data,
			toUTF($xml_data['XmlTemplate_Data'])
		);
		if (empty($html)) {
			$xml_data_arr = swXmlTemplate::transformEvnXmlDataToArr($evn_xml_data, true);
			$object_data['xml_data'] = &$xml_data_arr;
			$object_data['EvnClass_id'] = $xml_data['EvnClass_id'];
			$object_data['Evn_id'] = $xml_data['Evn_id'];
			$object_data['Evn_pid'] = $xml_data['Evn_pid'];
			$object_data['Evn_rid'] = $xml_data['Evn_rid'];
			$object_data['EvnXml_id'] = $xml_data['EvnXml_id'];
			$object_data['XmlType_id'] = $xml_data['XmlType_id'];
			array_walk($xml_data_arr,'ConvertFromUTF8ToWin1251');
			//array_walk($xml_data_arr,'swEvnXml::processingNodeValue');
			if (empty($xml_data['XmlTemplate_HtmlTemplate']) && !empty($xml_data_arr['UserTemplateData'])) {
				//есть UserTemplateData в EvnXml_Data. Используется устаревший шаблон без разметки областей ввода данных и областей только для печати
				$html = $xml_data_arr['UserTemplateData'];
				$html = self::cleaningHtml($html, array(
					'charactersListToBeEscaped' => '\\',
				));
			} else if (!empty($xml_data['XmlTemplate_HtmlTemplate'])) {
				//документ нового формата с множеством разделов и шаблоном отображения в XmlTemplate_HtmlTemplate
				$html = $xml_data['XmlTemplate_HtmlTemplate'];
				$html = self::cleaningHtml($html, array(
					'charactersListToBeEscaped' => '\\',
				));
				//это нужно для печати или редактирования объектов с типом Параметр и список значений
				self::getCiInstance()->load->library('swMarker');
				if ($parse_markers) {
					$html = swMarker::createMarkerBlocks($html, $xml_data_arr);
					$html = swMarker::createInputBlocks($html, $xml_data_arr, $xml_data);
					$html = swMarker::createParameterValueFields($html, $xml_data['EvnXml_id'], $xml_data_arr);
					$iid = !empty($xml_data['instance_id'])?'_'.$xml_data['instance_id']:'';
					$search = array();
					$replace = array();
					foreach ($xml_data_arr as $k => $v) {
						$search[] = 'block_' . $k . '"';
						$replace[] = 'block_' . $k . '_' . $xml_data['EvnXml_id'] . $iid . '"';
						$search[] = 'caption_' . $k . '"';
						$replace[] = 'caption_' . $k . '_' . $xml_data['EvnXml_id'] . $iid . '"';
						$search[] = 'data_' . $k . '"';
						$replace[] = 'data_' . $k . '_' . $xml_data['EvnXml_id'] . $iid . '"';
						$search[] = '{' . $k . '}';
						$replace[] = self::cleaningHtml($v, array(
							'charactersListToBeEscaped' => '\\',
						));
					}
					$html = str_replace($search, $replace, $html);
				}
			}
		} else {
			$html = self::cleaningHtml($html, array(
				'charactersListToBeEscaped' => '\\',
			));
		}

		if ($parse_markers) {
			self::onBeforeParse($xml_data, '', $parse_data);
			self::getCiInstance()->load->library('parser');
			$html = self::getCiInstance()->parser->parse_string($html, $parse_data, true);
			//это нужно для печати шаблонных маркеров
			self::getCiInstance()->load->library('swMarker');

			$html = swMarker::processingTextWithMarkers($html, $xml_data['Evn_id'], array(
				'isPrint' => true,
				'cacheEvnXml' => !empty($xml_data['EvnXml_id'])?$xml_data['EvnXml_id']:null,
				'EvnXml_id' => !empty($xml_data['EvnXml_id'])?$xml_data['EvnXml_id']:null,
				'restore' => 'full'
			), 0, $xml_data_arr, $parse_markers);

			$html = preg_replace("/@#@EAN13\_(.*?)@#@/uis", "<img height=100 src='/barcode.php?s=$1&type=ean13' />", $html); // подменяем штрих-код на картинку
		}

		// Добавляем базовый шрифт и размер шрифта
		self::getCiInstance()->load->library('swXmlTemplateSettings');
		$docTplSettings = swXmlTemplateSettings::getArrFromJson($xml_data['XmlTemplate_Settings'], $allowDefaultSettings);
		if ((!empty($docTplSettings['base_fontsize']) || !empty($docTplSettings['base_fontfamily'])) && $parse_markers) {
			$fontstyle = "";
			if (!empty($docTplSettings['base_fontsize'])) {
				$fontstyle .= "font-size:" . $docTplSettings['base_fontsize'] . "px;";
			}

			$fonts = array(
				'Arial, Helvetica, sans-serif',
				'Comic Sans MS, cursive',
				'Courier New, Courier, monospace',
				'Georgia, serif',
				'Lucida Sans Unicode, sans-serif',
				'Lucida Grande, sans-serif',
				'Tahoma, Geneva, sans-serif',
				'Times New Roman, Times, serif',
				'Trebuchet MS, Helvetica, sans-serif',
				'Verdana, Geneva, sans-serif'
			);

			if (!empty($docTplSettings['base_fontfamily']) && !empty($fonts[$docTplSettings['base_fontfamily'] - 1])) {
				$fontstyle .= "font-family:" . $fonts[$docTplSettings['base_fontfamily'] - 1] . ";";
			}
			$html = "<div style='{$fontstyle}'>{$html}</div>";
		}

		// символ разрыва ломает XTemplate
		$html = str_replace("\xe2\x80\xa8", '\\u2028', $html);
		$html = str_replace("\xe2\x80\xa9", '\\u2029', $html);

		return $html;
	}
	
	/**
	 * Печать документа
	 * @param array $xml_data Массив атрибутов документа полученных методом EvnXmlBase_model::doLoadPrintData
	 * @param string $region_nick
	 * @param bool $isPrintHtml
	 * @param bool $isUseWkHtmlToPdf
	 * @return bool
	 * @throws Exception Чтобы вывести ошибку, надо выбросить исключение
	 */
	public static function doPrint($xml_data, $region_nick, $isPrintHtml = false, $isUseWkHtmlToPdf = false, $isReturnString = false, $isPrintHalf = false, $EMDCertificate_id = null, $nestedCount = 0) {
		$nestedCount++;
		if ($nestedCount > 3) {
			return '';
		}

		if (!empty($xml_data['Error_Msg'])) {
			throw new Exception($xml_data['Error_Msg']);
		}

		self::getCiInstance()->load->library('swXmlTemplate');
		$template = swXmlTemplate::getHtmlDoc($xml_data, true, $nestedCount);

		$parse_data = array();
		self::onBeforeParse($xml_data, $region_nick, $parse_data);
		self::getCiInstance()->load->library('parser');
		$doc = self::getCiInstance()->parser->parse_string($template, $parse_data, true);

		//это нужно для печати шаблонных маркеров
		//self::getCiInstance()->load->library('swMarker');
		//$doc = swMarker::processingTextWithMarkers($doc, $xml_data['Evn_id'], array('isPrint'=>true,));

		if($isPrintHalf == 'true') {
			$doc = str_pad($doc, strlen($doc)+124, "<br>", STR_PAD_LEFT);
		}

		if (getRegionNick() == 'ufa') {
			// какой то странный Башкирский код, который выводит "уголок" при печати документов
			if ($isPrintHalf == 'true' || $isPrintHalf == 'false') {
				$doc = $doc . '<marker><div style="position:absolute; border-style:dashed; top:535px; right:40px; width:30px; height:30px; background:none; 
				border-right:0.5px solid rgba(47, 46, 46, 0.5);border-bottom:0.5px solid rgba(47, 46, 46, 0.5);"></div></marker>';
			}
		}
	
		if ($isReturnString) {
			return $doc;
		}
		self::getCiInstance()->load->library('swXmlTemplateSettings');
		// Получаем настройки печати документа
		$docTplSettings = swXmlTemplateSettings::getArrFromJson($xml_data['XmlTemplate_Settings']);
		self::getCiInstance()->load->helper('Options');
		// Получаем общие настройки
		$options = getOptions();
		if (is_array($options['print'])
			&& isset($options['print']['evnxml_print_type'])
			&& 2 == $options['print']['evnxml_print_type']
		) {
			$isPrintHtml = true;
		}

		if (!empty($EMDCertificate_id)) {
			$PromedURL = self::getCiInstance()->config->item('PromedURL');
			if (empty($PromedURL)) {
				$PromedURL = 'http' . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . '://' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT'];
			}

			$doc .= "<img style='width:40%;' src='{$PromedURL}/?c=EMDStamp&m=printStamp&EMDCertificate_id={$EMDCertificate_id}'></img>";
		}

		if (!empty($xml_data['Evn_id']) && isset($xml_data['EvnClass_id']) && $xml_data['EvnClass_id'] == 11) {
			// метка "Профилактический медицинский осмотр" или "Диспансеризация" refs #164932
			$dispText = null;

			self::getCiInstance()->load->model('EvnVizitPL_model');
			$resp_disp = self::getCiInstance()->EvnVizitPL_model->getDataForDispPrint(array(
				'EvnVizitPL_id' => $xml_data['Evn_id']
			));

			if (getRegionNick() != 'kareliya' && !empty($resp_disp[0]['TreatmentClass_Code'])) {
				switch($resp_disp[0]['TreatmentClass_Code']) {
					case '2.1':
						$dispText = 'Профилактический медицинский осмотр';
						break;
					case '2.2':
						$dispText = 'Диспансеризация';
						break;
				}
			} else if (getRegionNick() == 'kareliya' && !empty($resp_disp[0]['VizitType_Code'])) {
				switch($resp_disp[0]['VizitType_Code']) {
					case '2.1':
						$dispText = 'Профилактический медицинский осмотр';
						break;
					case '2.2':
						$dispText = 'Диспансеризация';
						break;
				}
			}

			if (!empty($dispText)) {
				$doc = "<div style='float:right; padding: 10px;'>{$dispText}</div>" . $doc;
			}
		}

		if (empty($EMDCertificate_id) && $isPrintHtml) {
			$font_size = '10';
			if (isset($docTplSettings['FontSize_id']) && (in_array($docTplSettings['FontSize_id'], array('6', '8', '10', '12', '14')))) {
				$font_size = $docTplSettings['FontSize_id'];
			}
			/*
			возможно, что при печати нужно применять те же стили, что при отображении в ЭМК
			<link href="/extjs/resources/css/ext-all.css" rel="stylesheet" type="text/css" />
			<link href="/css/themes/blue/xtheme.css" rel="stylesheet" type="text/css" />
			<link href="/css/form.css" rel="stylesheet" type="text/css" />
			<link href="/css/customext.css" rel="stylesheet" type="text/css" />
			<link href="/css/iconcls.css" rel="stylesheet" type="text/css" />
			<link href="/css/messages.css" rel="stylesheet" type="text/css" />
			<link href="/css/grid.css" rel="stylesheet" type="text/css" />
			<link href="/css/panel.css" rel="stylesheet" type="text/css" />
			<link href="/css/tree.css" rel="stylesheet" type="text/css" />
			<link href="/css/daterangepicker.css" rel="stylesheet" type="text/css" />
			<link href="/css/er.css" rel="stylesheet" type="text/css" />
			<link href="/css/spinner.css" rel="stylesheet" type="text/css" />
			*/
			//Если не поможет замена по маске, удалим скриптом на нативном js
			$addScript = '<script>
			document.addEventListener("DOMContentLoaded", function(){});
			</script>';
			echo '<html><head><title>Печатная форма</title>
			<link href="/css/emk.css" rel="stylesheet" type="text/css" />
			</head><body id="rightEmkPanelPrint" class="print-page-view" style="font-size: '.$font_size.'pt;">'.$doc.'</body>'.$addScript.'</html>'; // Здесь мог бы быть Ваш $addScript
			return true;
		}

		// Печать в PDF
		if($region_nick == 'kaluga'){
			if(!is_array($docTplSettings)){
				$docTplSettings = array();
			}
			$docTplSettings['xml_line_height'] = 1;
		}
		
		$file_name = (!empty($xml_data['EvnXml_Name'])) ? $xml_data['EvnXml_Name'] : null;
		$resp = self::doPrintPdf($file_name, $docTplSettings, $doc, $isUseWkHtmlToPdf, !empty($EMDCertificate_id));
		if (!empty($EMDCertificate_id)) {
			return $resp;
		}

		return true;
	}

	/**
	 * Печать HTML-документа в PDF
	 * @param string $name
	 * @param string $docTplSettings JSON
	 * @param string $html HTML-документ
	 * @param bool $isUseWkHtmlToPdf
	 * @return void
	 */
	public static function doPrintPdf($name, $docTplSettings, $html, $isUseWkHtmlToPdf = false, $isReturnString = false) {
		$file_name = 'document.pdf';
		if (!empty($name)) {
			$file_name = $name.'.pdf';
		}
		//по умолчанию
		$paper_format  = 'A4';//1
		$paper_orient  = 'portrait';//2
		$font_size     = '8';
		$margin_top    = 10;
		$margin_right  = 10;
		$margin_bottom = 10;
		$margin_left   = 10;
		$line_height = 1.3;
		if (isset($docTplSettings['PaperFormat_id']) && (2 == $docTplSettings['PaperFormat_id'])) {
			$paper_format = 'A5';
		}
		if (isset($docTplSettings['PaperOrient_id']) && (1 == $docTplSettings['PaperOrient_id'])) {
			$paper_orient = 'landscape';
		}
		if (isset($docTplSettings['FontSize_id']) && (in_array($docTplSettings['FontSize_id'], array('6', '8', '10', '12', '14')))) {
			$font_size = $docTplSettings['FontSize_id'];
		}
		if (isset($docTplSettings['margin_top']) && ($docTplSettings['margin_top'])) {
			$margin_top = $docTplSettings['margin_top'];
		}
		if (isset($docTplSettings['margin_right']) && ($docTplSettings['margin_right'])) {
			$margin_right = $docTplSettings['margin_right'];
		}
		if (isset($docTplSettings['margin_bottom']) && ($docTplSettings['margin_bottom'])) {
			$margin_bottom = $docTplSettings['margin_bottom'];
		}
		if (isset($docTplSettings['margin_left']) && ($docTplSettings['margin_left'])) {
			$margin_left = $docTplSettings['margin_left'];
		}
		if (!empty($docTplSettings['xml_line_height'])) {
			$line_height = $docTplSettings['xml_line_height'];
		}

		$styles = file_get_contents('css/emk.css');
		$html = "<style type='text/css'>{$styles}</style>".$html;

		$plugin = 'mpdf';

		if ($isUseWkHtmlToPdf) {
			$plugin = 'wkpdf';
		}
		// При задании формата использовать настройки
		if (!function_exists('print_pdf')) {
			self::getCiInstance()->load->helper('PDF');
		}
		return print_pdf($plugin, $paper_orient, $paper_format, $font_size, $margin_left, $margin_right, $margin_top, $margin_bottom, $html, $file_name, $isReturnString ? 'S' : 'I', $line_height);
	}

	/**
	 * Обработка сохранения документа
	 * @param string $scenario
	 * @param array $params
	 * @param array $response Чтобы вывести уведомление, надо создать сообщение с ключом alert_msg
	 * @return bool
	 * @throws Exception Чтобы вывести ошибку, надо выбросить исключение
	 * @todo убрать этот метод, в EvnXmlBase_model::_afterSave реализовано правильнее
	 */
	public static function onAfterSave($scenario, $params, &$response) {
		switch ($params['XmlType_id']) {
			case self::MULTIPLE_DOCUMENT_TYPE_ID:
				break;
			case self::EVN_VIZIT_PROTOCOL_TYPE_ID:
				self::getCiInstance()->load->model('EvnXmlVizit_model', 'EvnXmlVizit_model');
				self::getCiInstance()->EvnXmlVizit_model->onAfterSave($scenario, $params, $response);
				break;
			case self::EVN_USLUGA_PROTOCOL_TYPE_ID:
				break;
			case self::LAB_USLUGA_PROTOCOL_TYPE_ID:
				break;
			default:
				break;
		}
	}

	/**
	 * Получение дополнительных данных перед обработкой шаблона документа
	 * @param array $xml_data Массив атрибутов документа полученных методом EvnXmlBase_model::doLoadEvnXmlPanel
	 * @param string $region_nick
	 * @param array $parse_data Массив данных
	 * @return void
	 * @throws Exception Чтобы вывести ошибку, надо выбросить исключение
	 */
	public static function onBeforeParse($xml_data, $region_nick, &$parse_data) {
		switch (true) {
			case (empty($xml_data['XmlType_id']) || in_array($xml_data['XmlType_id'], array(
				self::MULTIPLE_DOCUMENT_TYPE_ID,
				self::EVN_VIZIT_PROTOCOL_TYPE_ID,
			))):
				// чтобы отработали старые подстановочные маркеры данных посещения
				if ($xml_data['EvnClass_id'] == 11) {
					self::getCiInstance()->load->model('EvnVizit_model', 'EvnVizit_model');
					self::getCiInstance()->load->library('swFilterResponse');
					$session_params = getSessionParams();
					$res = self::getCiInstance()->EvnVizit_model->getEvnVizitPLViewData(array(
						'Lpu_id' => $session_params['Lpu_id'],
						'EvnVizitPL_id' => $xml_data['Evn_id'],
					));
					if ($res && count($res) > 0) {
						$res[0]['XmlTemplate_Caption'] = '';
						$parse_data = array_merge($res[0], $parse_data);
					}
				}
				break;
			default:
				break;
		}
	}

	/**
	 * Чистка HTML-кода
	 * @param string $html
	 * @param array $options
	 * @return string
	 */
	public static function cleaningHtml($html, $options = array())
	{
		if (!empty($options['withSpecChars'])) { // spec_chars
			$html = htmlspecialchars_decode($html);
		}
		if (!empty($options['charactersListToBeEscaped'])) {
			$html = addcslashes($html, $options['charactersListToBeEscaped']);
		}

		// добавляем к div class='swdeletable' кнопку удаления (refs #52936)
		$html = preg_replace('/<div class="deleteButton".*?\/div>/','',$html); // удаляем если есть
		$html = preg_replace('/(<div class="swdeletable".*?>)/','$1<div class="deleteButton" onmousedown="this.parentNode.parentNode.removeChild(this.parentNode);"></div>',$html); // добавляем

		if (!empty($options['commentWithoutTag'])) {
			$pattern = "/\<\!\-\-([^\<]+)\-\->/";
			$html = preg_replace($pattern, '', $html);
		}
		if (!empty($options['commentWithoutExclamation'])) {
			$pattern = "/\<\!\-\-([^\!]+)\-\->/";
			$html = preg_replace($pattern, '', $html);
		}
		if (!empty($options['commentWithIf'])) {
			$pattern = "/\<\!\-\-\[if([^\[]+)\<\!\[endif\]\-\->/";
			$html = preg_replace($pattern, '', $html);
		}
		if (!empty($options['styles'])) {
			$pattern = "/\<style([^\<]+)\<\/style\>/";
			$html = preg_replace($pattern, '', $html);
		}
		if (!empty($options['styleMso'])) {
			$pattern = '/style\="mso\-([^\=]+)"/';
			$html = preg_replace($pattern, '', $html);
		}
		if (!empty($options['userLocalFiles'])) {
			// чистим элементы вида <img src="file:///C:\Users..." > т.к. их наличие ломает просмотр всего
			$pattern = "/\<img([^\>]*)file\:([^\>]*)\>/";
			$html = preg_replace($pattern, '', $html);
		}
		if (!empty($options['withSpecChars'])) {
			$html = htmlspecialchars($html);
		}
		return $html;
	}

	/**
	 * Закрывает все открытые теги
	 */
	public static function closeTags($content) {
		$position = 0;
		$open_tags = array();
		//теги для игнорирования
		$ignored_tags = array('br', 'hr', 'img', 'input');

		preg_match_all("|<(/?)([a-z\d]+)\b[^><]*(>?)|ius", $content, $tag_matches, PREG_OFFSET_CAPTURE);

		if (empty($tag_matches)) {
			return $content;
		}

		foreach ($tag_matches[2] as $k => $v) {
			$tag = strtolower($v[0]);
			if (in_array($tag, $ignored_tags) == false){
				//тег открыт
				if ($tag_matches[1][$k][0] == '') {
					if (isset($open_tags[$tag]))
						$open_tags[$tag]++;
					else
						$open_tags[$tag] = 1;
				}
				//тег закрыт
				if ($tag_matches[1][$k][0] == '/') {
					if (isset($open_tags[$tag]))
						$open_tags[$tag]--;
				}
			}
		}

		foreach ($open_tags as $tag => $count_not_closed) {
			if ($count_not_closed > 0) {
				// закрываем открытое
				$content .= str_repeat("</{$tag}>", $count_not_closed);
			} elseif ($count_not_closed < 0) {
				// открываем закрытое
				$content = str_repeat("<{$tag}>", abs($count_not_closed)) . $content;
			}
		}

		return $content;
	}

}
