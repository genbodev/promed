<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
* 
* Абстрактная модель предназначена для работы в формате HL7 и должна быть расширена моделью-адаптером
*
* @package      EMD
* @access       public
* @copyright    Copyright (c) 2020 Swan Ltd.
* @author       
* @version      17.07.2020
*/

abstract class DocAbstract extends swModel
{


	/**
	* папка где хранятся файлы стилей и проверки схемы
	* она должны быть переоопределена в самих классах-обработчиках
	* используется 2 папки:
	* xsd - проверка схемы
	* xls - файл стилей
	* @var $folder string
	*/
	protected $folder;
	
	/**
	* имя файла xls которое поставляют федералы
	*/
	protected $file_xls_name;

	/**
	* имя файла xsd которое поставляют федералы
	*/
	protected $file_xsd_name;

	/**
	* имя view которое собственно создает XML
	*/
	protected $view;
	
	/**
	* соединение с EMD базой
	*/
	protected $emddb;

	/**
	 *	Конструктор
	 * @throws Exception - если не активирована база EMD
	 */
	function __construct() {
		parent::__construct();
		$this->load->library('parser');
		
		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (!empty($isEMDEnabled)) {
			$this->emddb = $this->load->database('emd', true);
		} else {
			throw new Exception('Не настроена база данных для хранения электронных медицинских документов');
		}
	}
	

	/**
	* предназначена для вывода на печать в браузер
	* основное назначение это подмена пути к файлу со стилями
	* возвращает текстовое представление XML с верным путем для стиля
	* @return string собственно XML в виде текста
	*/
	public function createHL7(array $info)
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<?xml-stylesheet type="text/xsl" href="/documents/HL7/'.$this->folder."/".$this->file_xls_name.'"?>';
		$xml .= '<?valbuddy_schematron Schematron.sch?>';
		$xml .= $this->parser->parse("EMDHL7OutDoc/".$this->view, $info, true);
		return $xml;
	}
	
	/**
	* проверка входящего файла
	* @param xml строковое представление XML
	* @return void
	* @throws Exception
	*/
	public function ValidateDoc(string $xml)
	{
		// проверяем xml по xsd схеме
		$xsd = realpath('documents/HL7/'.$this->folder.'/'.$this->file_xsd_name);
		if (empty($xsd)){
			throw new Exception('Не найдены файлы проверки схемы. Должены быть в папке documents/HL7/'.$this->folder);
		}
		$domDoc = new DOMDocument();
		$domDoc->loadXML($xml);
		libxml_use_internal_errors(true);
		if (!$domDoc->schemaValidate($xsd)) {
			$errors = array_map(function ($error) {
				return trim($error->message) . ' on line ' . $error->line;
			}, libxml_get_errors());
			libxml_clear_errors();
			throw new Exception('Ошибка при проверке документа в формате HL7 по XSD схеме documents/HL7/'.$this->folder.': <br><br>' . implode("<br>\n", $errors) . 
								'<br><br>HL7:<br><textarea cols="50" rows="10">'.$xml.'</textarea>');
		}
	}
	
	/**
	* получить очередной номер версии подписанного документа и его будущий идентификатор
	* 
	* @param $EMDRegistry_ObjectName string Имя объекта
	* @param $EMDRegistry_ObjectID int идентификатор объекта
	* @return array
	*/
	protected function getNextVersion($EMDRegistry_ObjectName,$EMDRegistry_ObjectID)
	{
		$emd = $this->queryResult('
			select
				COALESCE(MAX("EMDVersion_VersionNum") + 1, 1) as "Version",
				COALESCE((select MAX("EMDVersion_id") from "EMD"."EMDVersion" ) + 1, 1) as "EMDVersion_id"
					from
						"EMD"."EMDVersion" e2
						inner join "EMD"."EMDRegistry" emdr on e2."EMDRegistry_id" = emdr."EMDRegistry_id"
							where
								emdr."EMDRegistry_ObjectName" = :EMDRegistry_ObjectName
								and emdr."EMDRegistry_ObjectID" = :EMDRegistry_ObjectID', 
								  [
									  "EMDRegistry_ObjectName"=>$EMDRegistry_ObjectName,
									  "EMDRegistry_ObjectID"=>$EMDRegistry_ObjectID
								  ]
				, $this->emddb);
		return $emd[0];
	}

	/**
	* получить метаданные по подписываемому документу
	* вызывается из модели EMD_model и для каждого типа документа оно индивидуально
	* перегружается в адаптерах
	*/
	public function getMetaDoc(array $data)
	{
		return false;
	}
}
