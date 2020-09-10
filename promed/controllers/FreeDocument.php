<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* FreeDocument - контроллер для работы с документами в свободной форме
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Common
* @access		public
* @copyright	Copyright (c) 2009-2011 Swan Ltd.
* @author		Salakhov Rustam
* @version		10.10.2011
*/
class FreeDocument extends swController {

	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'processingMarkerText' => array(
				array('field' => 'text','label' => 'Исходный текст','rules' => 'trim|required','type' => 'string'),
				array('field' => 'evn_id','label' => 'Идентификатор события','rules' => 'trim|required','type' => 'id')
			),
			'processingMarkerTextArray' => array(
				array('field' => 'text','label' => 'Массив исходных текстов','rules' => 'trim|required','type' => 'string'),
				array('field' => 'evn_id','label' => 'Идентификатор события','rules' => 'trim|required','type' => 'id')
			),
			'deleteFreeDocument' => array(
				array('field' => 'id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id')
			),
			'getFreeDocumentMarkerList' => array(
				array('field' => 'EvnClass_id', 'label' => 'Идентификатор класса события', 'rules' => 'required', 'type' => 'id'),
				// Параметры страничного вывода
				array('default' => 0, 'field' => 'start', 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int'),
				array('default' => 100, 'field' => 'limit', 'label' => 'Количество записей', 'rules' => '', 'type' => 'int'),
			),
			'loadMarkerListByFilters' => array(
				array('default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int'),
				array('default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int'),
				array('field' => 'EvnClass_id', 'label' => 'Класс события', 'rules' => '', 'type' => 'id'),
				array('field' => 'FreeDocMarker_Name', 'label' => 'Название маркера', 'rules' => '', 'type' => 'string'),
				array('field' => 'FreeDocMarker_Description', 'label' => 'Описание маркера', 'rules' => '', 'type' => 'string'),
				array('field' => 'FreeDocMarker_TableAlias', 'label' => 'Связанный алиас', 'rules' => '', 'type' => 'string')
			),
			'loadRelationshipListByFilters' => array(
				array('default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int'),
				array('default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int'),
				array('field' => 'EvnClass_id', 'label' => 'Класс события', 'rules' => '', 'type' => 'id'),
				array('field' => 'FreeDocRelationship_AliasName', 'label' => 'Алиас связи', 'rules' => '', 'type' => 'string'),
				array('field' => 'FreeDocRelationship_AliasTable', 'label' => 'Таблица', 'rules' => '', 'type' => 'string'),
				array('field' => 'FreeDocRelationship_LinkedAlias', 'label' => 'Связанный алиас', 'rules' => '', 'type' => 'string')
			),
			'getFreeDocMarkerData' => array(
				array('field' => 'FreeDocMarker_id','label' => 'Идентификатор маркера','rules' => 'required','type' => 'id')
			),
			'getFreeDocRelationshipData' => array(
				array('field' => 'FreeDocRelationship_id','label' => 'Идентификатор связи','rules' => 'required','type' => 'id')
			),
			'getDebugInformation' => array(
				array('field' => 'FreeDocMarker_id','label' => 'Идентификатор маркера','rules' => '','type' => 'id'),
				array('field' => 'FreeDocRelationship_id','label' => 'Идентификатор связи','rules' => '','type' => 'id'),
				array('field' => 'type','label' => 'Тип информации','rules' => '','type' => 'string')
			),
			'printFreeDocument' => array(
				array(
					'field' => 'FreeDocument_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'printHtml',
					'label' => 'Печать HTML',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveFreeDocMarker' => array(
				array('field' => 'FreeDocMarker_id','default' => 0,'label' => 'Идентификатор маркера','rules' => '','type' => 'id'),
				array('field' => 'EvnClass_id','label' => 'Идентификатор класса события','rules' => 'required','type' => 'id'),
				array('field' => 'FreeDocMarker_Name','label' => 'Название маркера','rules' => 'required','type' => 'string'),
				array('field' => 'FreeDocMarker_TableAlias','label' => 'Связанный алиас','rules' => '','type' => 'string'),
				array('field' => 'FreeDocMarker_Field','label' => 'Поле','rules' => '','type' => 'string'),
				array('field' => 'FreeDocMarker_Query','label' => 'Запрос','rules' => '','type' => 'string'),
				array('field' => 'FreeDocMarker_Description','label' => 'Описание маркера','rules' => '','type' => 'string'),
				array('field' => 'FreeDocMarker_IsTableValue','label' => 'Табличный маркер','rules' => '','type' => 'string'),
				array('field' => 'FreeDocMarker_Options','label' => 'Доп. настройки','rules' => '','type' => 'string')
			),
			'saveFreeDocRelationship' => array(
				array('field' => 'FreeDocRelationship_id','default' => 0,'label' => 'Идентификатор связи','rules' => '','type' => 'id'),
				array('field' => 'EvnClass_id','label' => 'Идентификатор класса события','rules' => 'required','type' => 'id'),
				array('field' => 'FreeDocRelationship_AliasName','label' => 'Алиас связи','rules' => 'required','type' => 'string'),
				array('field' => 'FreeDocRelationship_AliasTable','label' => 'Таблица','rules' => '','type' => 'string'),
				array('field' => 'FreeDocRelationship_AliasQuery','label' => 'Запрос','rules' => '','type' => 'string'),
				array('field' => 'FreeDocRelationship_LinkedAlias','label' => 'Связанный алиас','rules' => '','type' => 'string'),
				array('field' => 'FreeDocRelationship_LinkDescription','label' => 'Описание связи','rules' => '','type' => 'string')
			)
		);
	}

	/**
	 * Description
	 */
	function getTestText() {
		//$val['text'] = 'Пациент: @#@ФамилияИОПациента (@#@ВозрастПациента). Адрес регистрации: @#@АдресРегистрацииПациента. Дата посещения: @#@ДатаПосещения. Тест: @#@тестовыймаркер. Ошибка: @#@ПостороннийМаркер.';
		$val['text'] = 'ФамилияПациента = "@#@ФамилияПациента", 
ИмяПациента = "@#@ИмяПациента", 
ОтчествоПациента = "@#@ОтчествоПациента", 
ФамилияИмяОтчествоПациента = "@#@ФамилияИмяОтчествоПациента", 
ФамилияИОПациента = "@#@ФамилияИОПациента", 
ПолПациентаМЖ = "@#@ПолПациентаМЖ", 
ПолПациентаМужЖен = "@#@ПолПациентаМужЖен", 
ДРПациента = "@#@ДРПациента", 
ДеньДРПациента = "@#@ДеньДРПациента", 
НомерМесяцаДРПациента = "@#@НомерМесяцаДРПациента", 
НаименованиеМесяцаДРПациента = "@#@НаименованиеМесяцаДРПациента", 
ГодДРПациента = "@#@ГодДРПациента", 
ВозрастПациента = "@#@ВозрастПациента", 
АдресРегистрацииПациента = "@#@АдресРегистрацииПациента", 
ГородРегистрацииПациента = "@#@ГородРегистрацииПациента", 
АдресПроживанияПациента = "@#@АдресПроживанияПациента", 
ГородПроживанияПациента = "@#@ГородПроживанияПациента", 
ГородРожденияПациента = "@#@ГородРожденияПациента", 
ТипДокументаПациента = "@#@ТипДокументаПациента", 
СерияДокументаПациента = "@#@СерияДокументаПациента", 
НомерДокументаПациента = "@#@НомерДокументаПациента", 
КемВыданДокументПациента = "@#@КемВыданДокументПациента", 
ДатаВыдачиДокументаПациента = "@#@ДатаВыдачиДокументаПациента", 
МестоРаботыПациента = "@#@МестоРаботыПациента", 
ПодразделениеМестаРаботыПациента = "@#@ПодразделениеМестаРаботыПациента", 
ДолжностьПациента = "@#@ДолжностьПациента", 
СНИЛСПациента = "@#@СНИЛСПациента", 
СоцСтатусПациента = "@#@СоцСтатусПациента", 
ТерриторияПолисаПациента = "@#@ТерриторияПолисаПациента", 
ТипПолисаПациента = "@#@ТипПолисаПациента", 
СерияПолисаПациента = "@#@СерияПолисаПациента", 
НомерПолисаПациента = "@#@НомерПолисаПациента", 
ЕдНомерПолисаПациента = "@#@ЕдНомерПолисаПациента", 
СМОПолисаПациента = "@#@СМОПолисаПациента", 
ДатаВыдачиПолисаПациента = "@#@ДатаВыдачиПолисаПациента", 
ДатаЗакрытияПолисаПациента = "@#@ДатаЗакрытияПолисаПациента", 
СтатусПредставителяПациента = "@#@СтатусПредставителяПациента", 
ПредставительПациента = "@#@ПредставительПациента", 
ПоследняяЛьгота = "@#@ПоследняяЛьгота", 
ПоследняяФедЛьгота = "@#@ПоследняяФедЛьгота", 
ПоследняяРегЛьгота = "@#@ПоследняяРегЛьгота", 
ДатаПоследнейЛьготы = "@#@ДатаПоследнейЛьготы", 
ДатаПоследнейФедЛьготы = "@#@ДатаПоследнейФедЛьготы", 
ДатаПоследнейРегЛьготы = "@#@ДатаПоследнейРегЛьготы", 
НомерАмбКарты = "@#@НомерАмбКарты", 
НомерТалона = "@#@НомерТалона", 
ДатаПосещения = "@#@ДатаПосещения", 
ЛПУПосещения = "@#@ЛПУПосещения", 
ПодразделениеПосещения = "@#@ПодразделениеПосещения", 
ГруппаОтделенийПосещения = "@#@ГруппаОтделенийПосещения", 
ОтделениеПосещения = "@#@ОтделениеПосещения", 
ПрофильПосещения = "@#@ПрофильПосещения", 
ВрачПосещения = "@#@ВрачПосещения", 
ЗавОтделениемПосещения = "@#@ЗавОтделениемПосещения", 
СредМППосещения = "@#@СредМППосещения", 
МестоПосещения = "@#@МестоПосещения", 
ЦельПосещения = "@#@ЦельПосещения", 
ВидОплатыПосещения = "@#@ВидОплатыПосещения", 
КодОсновногоДиагнозаПосещения = "@#@КодОсновногоДиагнозаПосещения", 
НаименованиеОсновногоДиагнозаПосещения = "@#@НаименованиеОсновногоДиагнозаПосещения", 
ХарактерОсновногоДиагнозаПосещения = "@#@ХарактерОсновногоДиагнозаПосещения", 
СопутствующиеДиагнозыПосещения = "@#@СопутствующиеДиагнозыПосещения", 
ТекущаяДата = "@#@ТекущаяДата", 
НаименованиеПоследнейУслугиПосещения = "@#@НаименованиеПоследнейУслугиПосещения", 
ВрачПоследнейУслугиПосещения = "@#@ВрачПоследнейУслугиПосещения", 
ЛПУОказанияПоследнейУслугиПосещения = "@#@ЛПУОказанияПоследнейУслугиПосещения", 
ДатаОказанияПоследнейУслугиПосещения = "@#@ДатаОказанияПоследнейУслугиПосещения", 
ОсложненияПоследнейУслугиПосещения = "@#@ОсложненияПоследнейУслугиПосещения"';
		$val['success'] = true;
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
		return $val;
	}
	
	/**
	 * Обработка текста с маркерами
	 */
	function processingMarkerText() {
		$data = $this->ProcessInputData('processingMarkerText', true);
		
		if (isset($data['text']) && isset($data['evn_id'])) {
			$this->load->library('swMarker'); 
			$val['text'] = swMarker::processingTextWithMarkers($data['text'], $data['evn_id'], array('isPrint'=>0));
			$val['success'] = true;
		} else {
			$val['text'] = '';
			$val['success'] = false;
		}
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
		return $val;
	}
	
	/**
	 * Обработка массива текстов с маркерами
	 */
	function processingMarkerTextArray() {
		$data = $this->ProcessInputData('processingMarkerTextArray', true);
		
		if (isset($data['text']) && isset($data['evn_id'])) {
			$this->load->library('swMarker'); 
			ConvertFromWin1251ToUTF8($data['text']);
			$text_array = (array) json_decode($data['text']);
			for($i = 0; $i < count($text_array); $i++) {
				$text_array[$i] = (array) $text_array[$i];
				ConvertFromUTF8ToWin1251($text_array[$i]['template_text']);
				$text_array[$i]['template_text'] = swMarker::processingTextWithMarkers($text_array[$i]['template_text'], $data['evn_id'], array('isPrint'=>0));
				ConvertFromWin1251ToUTF8($text_array[$i]['template_text']);
			}			
			$val['text_array'] = $text_array;
			$val['success'] = true;
		} else {
			$val['text_array'] = array();
			$val['success'] = false;
		}
		$this->ReturnData($val);		
		return true;//$val;
	}

	/**
	*  Получение списка документов
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*/
	function loadFreeDocumentList() {
		$this->load->database();
		$this->load->model('FreeDocument_model', 'dbmodel');

		$data = array();
		$val  = array();
		
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());		
		$err  = getInputParams($data, $this->inputRules['loadFreeDocumentList']);
		
		/*if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadFreeDocumentList($data);

		if (is_array($response)) {
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			} else if (isset($response['data'])) {
				$val['data'] = array();
				foreach ( $response['data'] as $row ) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val['data'][] = $row;
				}
			}
		}*/

		echo json_encode($val['data']);
		return true;
	}
	
	/**
	 * Description
	 */
	function deleteFreeDocument() {
		$this->load->database();
		$this->load->model('FreeDocument_model', 'dbmodel');	
		$data = array();
		$val  = array();
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		$err = getInputParams($data, $this->inputRules['deleteFreeDocument']);
		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}
		$response = $this->dbmodel->deleteFreeDocument($data);
		if ((is_array($response)) && (count($response) > 0)) {
			if (array_key_exists('Error_Msg', $response[0]) && empty($response[0]['Error_Msg'])) {
				$val['success'] = true;
			} else {
				$val = $response[0];
				$val['success'] = false;
			}
		} else {
			$val['Error_Msg'] = 'При удалении документа возникли ошибки';
			$val['success'] = false;
		}
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
		return true;
	}
	
	/**
	*  Получение списка маркеров по идентификатору класса события
	*  Входящие данные: EvnClass_id
	*  На выходе: JSON-строка
	*/
	function getFreeDocumentMarkerList() {
		$this->load->database();
		$this->load->model('FreeDocument_model', 'dbmodel');

		$data = $this->ProcessInputData('getFreeDocumentMarkerList', true);
		if ($data === false) { return false; }


		$response = $this->dbmodel->getMarkerByEvnClass($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Description
	 */
	function printFreeDocument() {
		$data = $this->ProcessInputData('printFreeDocument', true);
		if ($data === false)
		{
			return false;
		}
		$doc = '';
		$ConvertToUTF8 = false;
		$this->load->library('swFilterResponse'); 
		$this->load->helper("Xml");
		$this->load->helper('Options');
		$this->load->library('parser');
		$this->load->model('Template_model', 'Template_model');
		$this->load->database();
		$this->load->model('FreeDocument_model', 'dbmodel');

		// Получаем настройки
		$options = getOptions();

		$response = $this->dbmodel->getFreeDocumentViewData($data);
		$this->ProcessModelList($response, $ConvertToUTF8, true);
		
		$xml_data = $this->Template_model->getXmlTemplateAndXmlData(array('isFreeDocument'=>1,'EvnXml_id'=>$data['FreeDocument_id']));
		if ($xml_data === false)
		{
			echo '<div>Ошибка получения Xml-данных.</div>';
			return false;
		}
        $docTplSettings = $this->Template_model->getXmlTemplateSettingsArrFromJson($xml_data['XmlTemplate_Settings']);
		$this->OutData[0]['XmlTemplate_Caption'] = '';
		if (isset($xml_data['XmlTemplate_Caption']))
		{
			$this->OutData[0]['XmlTemplate_Caption'] = $xml_data['XmlTemplate_Caption'];
		}
		$html_from_xml = processingXmlToHtml($xml_data['EvnXml_Data'],$xml_data['XmlTemplate_Data']);
		if ($html_from_xml === false)
		{
			if (strpos($xml_data['EvnXml_Data'], '<UserTemplateData>'))
			{
				//Используется устаревший шаблон без разметки областей ввода данных и областей только для печати
				$xml_data_arr = transformEvnXmlDataToArr(toUTF($xml_data['EvnXml_Data']));
				array_walk($xml_data_arr,'ConvertFromUTF8ToWin1251');
				$html_from_xml = $xml_data_arr['UserTemplateData'];
				$xml_data['XmlTemplate_HtmlTemplate'] = '<div id="block_UserTemplateData"><p id="caption_UserTemplateData"></p><div id="data_UserTemplateData">{UserTemplateData}</div></div>';
			}
			//  нет тегов data в XmlTemplate_Data и нет узла UserTemplateData в EvnXml_Data
			else if ($xml_data['XmlTemplate_HtmlTemplate'])
			{
				$xml_data_arr = transformEvnXmlDataToArr(toUTF($xml_data['EvnXml_Data']));
				array_walk($xml_data_arr,'ConvertFromUTF8ToWin1251');
				foreach($xml_data_arr as &$doc) {
					// 2 варианта решения "При вводе переноса строки в раздел обеспечить печать с переносом строки." :) 
					//$doc = '<pre>'.$doc.'</pre>'; 
					$doc=preg_replace("/[\n\r]+/su","<br/>",$doc);
				}
				//это нужно для печати объектов с типом Параметр и список значений 
				$this->load->library('swMarker'); 
				$xml_data['XmlTemplate_HtmlTemplate'] = swMarker::createParameterValueFields($xml_data['XmlTemplate_HtmlTemplate'],$xml_data['EvnXml_id'],$xml_data_arr);
				$html_from_xml = $this->parser->parse_string($xml_data['XmlTemplate_HtmlTemplate'], $xml_data_arr, true);
			}
			else
			{
				echo '<div>Ошибка получения HTML из Xml-документа. Возможно, что в шаблоне отсутствует разметка областей ввода данных или Xml-шаблон имеет неправильный формат.</div>';
				return false;
			}
		}
		if($html_from_xml)
		{
			$doc = $this->parser->parse_string($html_from_xml, $this->OutData[0], true);
		}
		
		if(isset($data['printHtml']))
		{
			echo '<html><head><title>Печатная форма</title><link href="/css/emk.css" rel="stylesheet" type="text/css" /></head><body id="rightEmkPanelPrint">'.$doc.'</body></html>';
			return true;
		}
        $paper_format  = 'A4';
        $paper_orient  = '-L';
        $font_size     = '10';
        $margin_top    = 10;
        $margin_right  = 10;
        $margin_bottom = 10;
        $margin_left   = 10;
        if (isset($docTplSettings['PaperFormat_id']) && (2 == $docTplSettings['PaperFormat_id'])) {
            $paper_format = 'A5';
        }
        if (isset($docTplSettings['PaperOrient_id']) && (2 == $docTplSettings['PaperOrient_id'])) {
            $paper_orient = '';
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
		// При задании формата использовать настройки
		require_once('vendor/autoload.php');
		$mpdf = new \Mpdf\Mpdf([
			'mode' => 'utf-8',
			'format' => $paper_format.$paper_orient,
			'default_font_size' => $font_size,
			'margin_left' => $margin_left,
			'margin_right' => $margin_right,
			'margin_top' => $margin_top,
			'margin_bottom' => $margin_bottom,
			'margin_header' => 10,
			'margin_footer' => 10
		]);
		$mpdf->charset_in = 'cp1251';

		$stylesheet = file_get_contents('css/emk.css');
		$mpdf->WriteHTML($stylesheet, 1);

		$mpdf->list_indent_first_level = 0;
		$mpdf->WriteHTML($doc, 2);
		$mpdf->Output('document.pdf', 'I');

		// echo '<html><head><title>Печатная форма</title><link href="/css/emk.css" rel="stylesheet" type="text/css" /></head><body id="rightEmkPanelPrint">'.$doc.'</body></html>';
		return true;
	}
	
	/**
	 * Description
	 */
	function loadEvnClassList() {
		$this->load->database();
		$this->load->model('FreeDocument_model', 'dbmodel');
		$response = $this->dbmodel->loadEvnClassList();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Description
	 */
	function loadMarkerListByFilters() {
		$this->load->database();
		$this->load->model("FreeDocument_model", "dbmodel");
		$data = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadMarkerListByFilters');
		
		if ($data) {
			$response = $this->dbmodel->loadMarkerListByFilters($data);
			if (is_array($response) && count($response) > 0) {
				$val = array(
					'data' => array(),
					'totalCount' => $response['totalCount']
				);				
				foreach ($response['data'] as $row) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val['data'][] = $row;
				}
				$this->ReturnData($val);
			} else {
				echo json_encode(array());
			}
		}
	}
	
	/**
	 * Description
	 */
	function loadRelationshipListByFilters() {
		$this->load->database();
		$this->load->model("FreeDocument_model", "dbmodel");
		$data = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadRelationshipListByFilters');
		
		if ($data) {
			$response = $this->dbmodel->loadRelationshipListByFilters($data);
			if (is_array($response) && count($response) > 0) {
				$val = array(
					'data' => array(),
					'totalCount' => $response['totalCount']
				);				
				foreach ($response['data'] as $row) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val['data'][] = $row;
				}
				$this->ReturnData($val);
			} else {
				echo json_encode(array());
			}
		}
	}
	
	/**
	 * Description
	 */
	function getFreeDocMarkerData() {
		$data = $this->ProcessInputData('getFreeDocMarkerData', true);
		if ($data) {
			$this->load->database();
			$this->load->model('FreeDocument_model', 'dbmodel');
			$response = $this->dbmodel->getFreeDocMarkerData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Description
	 */
	function getFreeDocRelationshipData() {
		$data = $this->ProcessInputData('getFreeDocRelationshipData', true);
		if ($data) {
			$this->load->database();
			$this->load->model('FreeDocument_model', 'dbmodel');
			$response = $this->dbmodel->getFreeDocRelationshipData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Description
	 */
	function getDebugInformation() {
		$data = $this->ProcessInputData('getDebugInformation', true);
		$response = '';
		if ($data) {
			$marker_id = isset($data['FreeDocMarker_id']) ? $data['FreeDocMarker_id'] : 0;
			$relationship_id = isset($data['FreeDocRelationship_id']) ? $data['FreeDocRelationship_id'] : 0;
			$this->load->database();
			$this->load->model('FreeDocument_model', 'dbmodel');			
			
			if ($marker_id > 0) {
				$markers = array();
				$marker = $this->dbmodel->getFreeDocMarkerData($data);
				$marker = $marker[0];

				switch($data['type']) {
					case 'query':
					case 'relationships':
						$evnclass_data = $this->dbmodel->getEvnClassData($marker['EvnClass_id']);
						$this->dbmodel->insertIntoMarkerArray($markers, $marker);						
						$this->dbmodel->buildTableChains($markers, $evnclass_data['class_list']);
						$d_inf = $this->dbmodel->collectDebugInformation($markers, $evnclass_data);
						if ($data['type'] == 'query') {
							$response = $markers[$marker_id]['error'] ? 'Нарушена последовательность связей: '.$d_inf['links_array'][$marker_id] : nl2br($d_inf['total_query']);
						} else {
							if (!empty($marker['FreeDocMarker_IsTableValue']))
								$response = 'Табличные маркеры не используют механизмы связи.';
							else
								$response = $d_inf['links_array'][$marker_id];
						}
						break;
					case 'table_header':
						$header = $this->dbmodel->renderTableHeader($marker['FreeDocMarker_Options']);
						$response = !empty($header) ? '<table style="border-collapse: collapse;">'.$header.'</table>' : '';
						break;
				}
			}
			
			if ($relationship_id > 0) {
				$markers = array();
				$relationship = $this->dbmodel->getFreeDocRelationshipData($data);
				$relationship = $relationship[0];
				//генерируем фальшивый маркер
				$marker = array(
					'id' => 0,
					'name' => 'empty',
					'original_name' => 'empty',
					'alias' => $relationship['FreeDocRelationship_AliasName'],
					'field' => '',
					'query' => '',
					'is_table' => '',
					'options' => '',
					'table_chain' => array(),
					'error' => false
				);

				switch($data['type']) {
					case 'query':
					case 'relationships':
						$evnclass_data = $this->dbmodel->getEvnClassData($relationship['EvnClass_id']);
						$this->dbmodel->insertIntoMarkerArray($markers, $marker);						
						$this->dbmodel->buildTableChains($markers, $evnclass_data['class_list']);
						$d_inf = $this->dbmodel->collectDebugInformation($markers, $evnclass_data);
						if ($data['type'] == 'query') {
							$response = $markers[$marker_id]['error'] ? 'Нарушена последовательность связей: '.$d_inf['links_array'][$marker_id] : nl2br($d_inf['query_section_from']);
						} else {
							$response = $d_inf['links_array'][$marker_id];
						}
						break;
				}
			}
			
			$val = array(
				'success' => true,
				'data' => $response
			);
			array_walk($val, 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($val);
			//$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Description
	 */
	function saveFreeDocMarker() {
		$data = $this->ProcessInputData('saveFreeDocMarker', true);
		if ($data) {
			//приводим данные маркера в надлежащий вид
			$data['FreeDocMarker_Name'] = preg_replace("/([^а-я^А-Я^ё^Ё^0-9]+)/u", "", $data['FreeDocMarker_Name']);
			if (isset($data['FreeDocMarker_IsTableValue']) && !empty($data['FreeDocMarker_IsTableValue']))
				$data['FreeDocMarker_IsTableValue'] = 2;
				
			$this->load->database();
			$this->load->model('FreeDocument_model', 'dbmodel');
			// проверяем дублирование имени маркера
			$response = $this->dbmodel->checkMarkerDouble($data);
			if (is_array($response)) {
				if (count($response) > 0) {
					$this->ReturnData(array('success' => false,'Error_Msg' => toUTF('Маркер с данными именем уже существует')));
					return false;
				} else {
					$response = $this->dbmodel->saveFreeDocMarker($data);
					$this->ProcessModelSave($response, true, true)->ReturnData();
					return true;
				}
			} else {
				$this->ReturnData(array('success' => false,'Error_Msg' => toUTF('Ошибка БД при проверке дублирования!')));
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Description
	 */
	function saveFreeDocRelationship() {
		$data = $this->ProcessInputData('saveFreeDocRelationship', true);
		if ($data) {
			//приводим данные связи в надлежащий вид
			//$data['FreeDocRelationship_AliasName'] = preg_replace("/([^а-я^А-Я^ё^Ё^0-9]+)/e", "", $data['FreeDocRelationship_AliasName']);

			$this->load->database();
			$this->load->model('FreeDocument_model', 'dbmodel');
			// проверяем дублирование алиаса связи
			$response = $this->dbmodel->checkRelationshipDouble($data);
			if (is_array($response)) {
				if (count($response) > 0) {
					$this->ReturnData(array('success' => false,'Error_Msg' => toUTF('Связь с данными алиасом уже существует')));
					return false;
				} else {
					$response = $this->dbmodel->saveFreeDocRelationship($data);
					$this->ProcessModelSave($response, true, true)->ReturnData();
					return true;
				}
			} else {
				$this->ReturnData(array('success' => false,'Error_Msg' => toUTF('Ошибка БД при проверке дублирования!')));
				return false;
			}
		} else {
			return false;
		}
	}
}

?>