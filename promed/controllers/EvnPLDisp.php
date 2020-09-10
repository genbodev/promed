<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * EvnPLDisp - контроллер для работы со всеми типами диспансеризации и проф. осмотров
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package			Polka
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Stanislav Bykov
 * @version			06.12.2013
 *
 * @property EvnPLDisp_model $dbmodel
 * @property SwParser $parser
*/

class EvnPLDisp extends swController
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model("EvnPLDisp_model", "dbmodel");
		$this->inputRules = [
			"createEvnPLDisp" => [
				["field" => "DispClass_id", "label" => "Тип диспансеризации", "rules" => "required", "type" => "id"],
				["field" => "Person_id", "label" => "Идентификатор пациента", "rules" => "required", "type" => "id"]
			],
			"getDispClassListAvailable" => [
				["field" => "Person_id", "label" => "Идентификатор пациента", "rules" => "required", "type" => "id"],
				["field" => "getAllDispInfo", "label" => "Полная информация о диспансеризации", "rules" => "", "type" => "boolean", "default" => false]
			],
			"getEvnPLDispInfo" => [
				["field" => "Person_id", "label" => "Идентификатор пациента", "rules" => "required", "type" => "id"]
			],
			"getEvnPLDispYears" => [
				["field" => "DispClass_id", "label" => "Тип диспансеризации", "rules" => "required", "type" => "id"]
			],
			"exportEvnPLDispToXml" => [
				["field" => "Lpu_id", "label" => "Идентификатор МО", "rules" => "required", "type" => "id"],
				["field" => "EvnPLDisp_disDate_Range", "label" => "Диапазон дат", "rules" => "required", "type" => "daterange"],
				["field" => "DispClass_id", "label" => "Идентификатор класса диспансеризации", "rules" => "required", "type" => "id"],
				["field" => "EvnPLDisp_IsPaid", "label" => "Признак оплаченности карты", "rules" => "", "type" => "id"],
				["field" => "checkByXSD", "label" => "Признак необходимости проверки файла по XSD", "rules" => "", "type" => "checkbox"],
			],
			"loadEvnPLDispList" => [
				["field" => "DispClass_id", "label" => "Тип диспансеризации", "rules" => "required", "type" => "id"],
				["field" => "Person_id", "label" => "Идентификатор пациента", "rules" => "required", "type" => "id"]
			],
			"updateDopDispInfoConsent" => [
				["field" => "EvnPLDisp_id", "label" => "Карта диспансеризации", "rules" => "required", "type" => "id"],
				["field" => "DopDispInfoConsentData", "label" => "Данные грида по информир. добр. согласию", "rules" => "", "type" => "string"]
			],
			"printEvnPLDisp" => [
				["field" => "EvnPLDisp_id", "label" => "Карта диспансеризации", "rules" => "required", "type" => "id"],
			],
			"getPrevHealthGroupType" => [
				["field" => "EvnPLDispTeenInspection_id", "label" => "Идентификатор осмотра", "rules" => "", "type" => "int"],
				["field" => "Person_id", "label" => "Идентификатор пациента", "rules" => "", "type" => "int"],
				["field" => "Lpu_id", "label" => "Идентификатор МО", "rules" => "", "type" => "int"],
				["field" => "DispClass_id", "label" => "Тип диспансеризации", "rules" => "", "type" => "int"]
			],
			"Refuse" => [
				["field" => "Person_id", "label" => "Идентификатор пациента", "rules" => "required", "type" => "int"],
				["field" => "DispClass_id", "label" => "Идентификатор типа диспансеризации", "rules" => "required", "type" => "int"],
				["field" => "MedStaffFact_id", "label" => "Идентификатор рабочего места пользователя", "rules" => "required", "type" => "int"],
			]
		];
	}

	/**
	 * Получение числа талонов с распределением по годам, для формирования списка на клиенте
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма поиска/потокового ввода талонов по ДД
	 * @return bool
	 */
	function getEvnPLDispYears()
	{
		$data = $this->ProcessInputData("getEvnPLDispYears", true);
		if ($data === false) {
			return false;
		}
		$info = $this->dbmodel->getEvnPLDispYears($data);
		$outdata = $this->ProcessModelList($info, true, true)->GetOutData();
		$this->ReturnData($outdata);
		return true;
	}

	/**
	 * Получение списка доступных для создания типов диспансеризации
	 * @return bool
	 */
	function getDispClassListAvailable()
	{
		$data = $this->ProcessInputData("getDispClassListAvailable", true);
		if ($data === false) {
			return false;
		}
		$info = $this->dbmodel->getDispClassListAvailable($data);
		$this->ProcessModelList($info, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение подробной информации о диспансеризации пациента
	 * Используется: панель ЭМК (ext6), кнопка-меню диспансеризации
	 * @return bool
	 */
	function getEvnPLDispInfo()
	{
		$data = $this->ProcessInputData("getEvnPLDispInfo", true);
		if ($data === false) {
			return false;
		}
		$info = $this->dbmodel->getEvnPLDispInfo($data);
		$this->ProcessModelList($info, true, true)->ReturnData();
		return true;
	}

	/**
	 * Создание карты диспансеризации
	 * @return bool
	 */
	function createEvnPLDisp()
	{
		$data = $this->ProcessInputData("createEvnPLDisp", true);
		if ($data === false) {
			return false;
		}
		$info = $this->dbmodel->createEvnPLDisp($data);
		$this->ProcessModelSave($info, true)->ReturnData();
		return true;
	}

	/**
	 * Экспорт карт диспансеризации несовершеннолетних в формате XML
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма экспорта карт диспансеризации несовершеннолетних
	 * @task https://redmine.swan.perm.ru/issues/28587
	 * @return bool
	 */
	function exportEvnPLDispToXml()
	{
		$data = $this->ProcessInputData('exportEvnPLDispToXml', true);
		if ($data === false) {
			return false;
		}

		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		$list_data = $this->dbmodel->loadEvnPLDispDataForXml($data);

		$this->load->library('parser');
		$this->load->library('SwXMLValidator');

		$path = EXPORTPATH_ROOT . "disp_list/";

		if (!file_exists($path)) {
			mkdir($path);
		}

		$out_dir = "re_xml_" . time() . "_" . "dispList";
		mkdir($path . $out_dir);
		$disp_list_error_file_name = "DISP_LIST_ERR";
		$disp_list_error_file_path = $path . $out_dir . "/" . $disp_list_error_file_name . ".xml";
		$disp_list_file_name = "DISP_LIST";
		$disp_list_file_path = $path . $out_dir . "/" . $disp_list_file_name . ".xml";
		$xml_validation_file_name = "DISP_LIST_XML_VALIDATION";
		$xml_validation_file_path = $path . $out_dir . "/" . $xml_validation_file_name . ".xml";

		$templ = 'evn_pl_disp_dd';
		$templError = 'evn_pl_disp_dd_error';

		$dispClass = $data['DispClass_id'];

		$error_list_data = $this->dbmodel->checkXmlDataOnErrors($list_data, $dispClass); //Добавил параметр dispClass - https://redmine.swan.perm.ru/issues/54950

		//$list_data = toAnsiR($list_data, true);
		//$error_list_data = toAnsiR($error_list_data, true);

		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n" . $this->parser->parse('export_xml/' . $templ, $list_data, true);
		$xml = str_replace('&', '&amp;', $xml);

		$errorXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n" . $this->parser->parse('export_xml/' . $templError, $error_list_data, true);
		$errorXml = str_replace('&', '&amp;', $errorXml);
		/*
		// Убираем пустые теги
		$emptyTagsToDelete = array(
			'middle', 'snils', 'index', 'kladrStreet', 'idEducationOrg', 'idOrphHabitation', 'dateOrphHabitation', 'idStacOrg',
			'headSize', 'healthProblems', 'pshycDevelopment', 'poznav', 'motor', 'emot', 'rech', 'pshycState', 'psihmot', 'intel', 'emotveg', 'sexFormulaMale',
			'P', 'Ax', 'Fa', 'sexFormulaFemale', 'Ma', 'Me', 'menses', 'menarhe', 'characters', 'char', 'fizkultGroupBefore', 'diagnosisBefore', 'lechen',
			'notDone', 'reason', 'reasonOther', 'reabil', 'healthyMKB', 'diagnosisAfter', 'consul', 'invalid', 'other', 'fizkultGroup', 'date', 'state',
			'privs', 'priv', 'errors', 'reabilitation', 'polisSer', //'idInsuranceCompany'
		);
		*/
		// Тег issled нужно сохранить, даже если он пустой
		$xml = preg_replace("/<issled>/", "[[ISSLED]]", $xml);
		$xml = preg_replace("/<\/issled>/", "[[/ISSLED]]", $xml);

		// Аналогично с тегом result https://redmine.swan.perm.ru/issues/108980
		$xml = preg_replace("/<result>/", "[[RESULT]]", $xml);
		$xml = preg_replace("/<\/result>/", "[[/RESULT]]", $xml);

		// Аналогично с тегом medSanName и medSanAddress https://redmine.swan.perm.ru/issues/136357
		$xml = preg_replace("/<medSanName>/", "[[medSanName]]", $xml);
		$xml = preg_replace("/<\/medSanName>/", "[[/medSanName]]", $xml);
		$xml = preg_replace("/<medSanAddress>/", "[[medSanAddress]]", $xml);
		$xml = preg_replace("/<\/medSanAddress>/", "[[/medSanAddress]]", $xml);

		//По всей видимости, теги  тоже надо сохранить
		/*$xml = preg_replace("/<kladrDistr>/", "[[KLADRDISTR]]", $xml);
		$xml = preg_replace("/<\/kladrDistr>/", "[[/KLADRDISTR]]", $xml);
		$xml = preg_replace("/<educOrgName>/", "[[EDUCORGNAME]]", $xml);
		$xml = preg_replace("/<\/educOrgName>/", "[[/EDUCORGNAME]]", $xml);*/

		// Чистим пустые теги
		$xml = preg_replace("/<\w+>\s*<\/\w+>/", "", $xml);
		$xml = preg_replace("/<\w+>\s*<\/\w+>/", "", $xml);
		$xml = preg_replace("/<\w+>\s*<\/\w+>/", "", $xml);

		// Возвращаем issled
		$xml = preg_replace("/\[\[ISSLED\]\]/", "<issled>", $xml);
		$xml = preg_replace("/\[\[\/ISSLED\]\]/", "</issled>", $xml);
		$xml = preg_replace("/<issled>\s*<\/issled>/", "<issled></issled>", $xml);

		// Аналогично с тегом result https://redmine.swan.perm.ru/issues/108980
		$xml = preg_replace("/\[\[RESULT\]\]/", "<result>", $xml);
		$xml = preg_replace("/\[\[\/RESULT\]\]/", "</result>", $xml);
		$xml = preg_replace("/<result>\s*<\/result>/", "<result></result>", $xml);

		// Аналогично с тегом child https://redmine.swan.perm.ru/issues/136357
		$xml = preg_replace("/\[\[medSanName\]\]/", "<medSanName>", $xml);
		$xml = preg_replace("/\[\[\/medSanName\]\]/", "</medSanName>", $xml);
		$xml = preg_replace("/<medSanName>\s*<\/medSanName>/", "<medSanName></medSanName>", $xml);
		$xml = preg_replace("/\[\[medSanAddress\]\]/", "<medSanAddress>", $xml);
		$xml = preg_replace("/\[\[\/medSanAddress\]\]/", "</medSanAddress>", $xml);
		$xml = preg_replace("/<medSanAddress>\s*<\/medSanAddress>/", "<medSanAddress></medSanAddress>", $xml);

		//Возвращаем kladrDistr и educOrgName
		/*$xml = preg_replace("/\[\[KLADRDISTR\]\]/", "<kladrDistr>", $xml);
		$xml = preg_replace("/\[\[\/KLADRDISTR\]\]/", "</kladrDistr>", $xml);
		$xml = preg_replace("/<kladrDistr>\s*<\/kladrDistr>/", "<kladrDistr></kladrDistr>", $xml);
		$xml = preg_replace("/\[\[EDUCORGNAME\]\]/", "<educOrgName>", $xml);
		$xml = preg_replace("/\[\[\/EDUCORGNAME\]\]/", "</educOrgName>", $xml);
		$xml = preg_replace("/<educOrgName>\s*<\/educOrgName>/", "<educOrgName></educOrgName>", $xml);*/

		// Убираем пробелы между тегами
		//$xml = preg_replace("/>\s*</", "><", $xml);
		// Заменил на более удобный вариант удаления пустых строк
		$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
		/*
		foreach ( $emptyTagsToDelete as $tag ) {
			$errorXml = preg_replace("/<{$tag}>\s*<\/{$tag}>/", "", $errorXml);
			$xml = preg_replace("/<{$tag}>\s*<\/{$tag}>/", "", $xml);
		}

		foreach ( $emptyTagsToDelete as $tag ) {
			$errorXml = preg_replace("/<{$tag}>\s*<\/{$tag}>/", "", $errorXml);
			$xml = preg_replace("/<{$tag}>\s*<\/{$tag}>/", "", $xml);
		}

		foreach ( $emptyTagsToDelete as $tag ) {
			$errorXml = preg_replace("/<{$tag}>\s*<\/{$tag}>/", "", $errorXml);
			$xml = preg_replace("/<{$tag}>\s*<\/{$tag}>/", "", $xml);
		}

		$errorXml = preg_replace("/>\s*</", "><", $errorXml);
		$xml = preg_replace("/>\s*</", "><", $xml);
		*/
		$errorXml = preg_replace("/\R\s*\R/", "\r\n", $errorXml);

		file_put_contents($disp_list_error_file_path, $errorXml);
		file_put_contents($disp_list_file_path, $xml);

		$file_zip_sign = $disp_list_file_name;
		$file_zip_name = $path . $out_dir . "/" . $file_zip_sign . ".zip";
		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile($disp_list_file_path, $disp_list_file_name . ".xml");
		$zip->AddFile($disp_list_error_file_path, $disp_list_error_file_name . ".xml");
		if (!empty($data['checkByXSD'])) {
			$xmlValidator = new SwXMLValidator();
			$xmlValidator->setParams($xml, $_SERVER['DOCUMENT_ROOT'] . '/documents/xsd/orph-card-schema.xsd', 'string', $xml_validation_file_path);
			if ($xmlValidator->validate() === false) {
				$zip->AddFile($xml_validation_file_path, $xml_validation_file_name . ".xml");
			}
		}
		$zip->close();

		unlink($disp_list_error_file_path);
		unlink($disp_list_file_path);
		if (file_exists($xml_validation_file_path)) {
			unlink($xml_validation_file_path);
		}
		$this->ReturnData(
			(file_exists($file_zip_name))
				? ["success" => true, "Link" => $file_zip_name]
				: ["success" => false, "Error_Msg" => toUtf("Ошибка создания архива!")]
		);
		return true;
	}

	/**
	 * Получение списка карт диспансеризации определенного типа для пациента
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования посещения
	 * @return bool
	 */
	function loadEvnPLDispList()
	{
		$data = $this->ProcessInputData("loadEvnPLDispList", true);
		if ($data === false) {
			return false;
		}
		$info = $this->dbmodel->loadEvnPLDispList($data);
		$outdata = $this->ProcessModelList($info, true, true)->GetOutData();
		$this->ReturnData($outdata);
		return true;
	}

	/**
	 * Сохранение согласия из ЭМК
	 * @return bool
	 */
	function updateDopDispInfoConsent()
	{
		$data = $this->ProcessInputData("updateDopDispInfoConsent", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->updateDopDispInfoConsent($data);
		$this->ProcessModelSave($response, true, "Ошибка сохранения согласия")->ReturnData();
		return true;
	}

	/**
	 * Получение мед группы для занятий физкультурой из предыдущего осмотра
	 * @return bool
	 */
	function getPrevHealthGroupType()
	{
		$data = $this->ProcessInputData("getPrevHealthGroupType", true);
		if ($data === false) {
			return false;
		}
		$result = $this->dbmodel->getPrevHealthGroupType($data);
		$this->ProcessModelList($result, true, true)->ReturnData();
		return true;
	}

	/**
	 * Печать рекомендаций маршрутной карты (протоколов осмотра)
	 * @return bool
	 */
	function printEvnPLDisp()
	{
		$data = $this->ProcessInputData("printEvnPLDisp", true);
		if ($data === false) {
			return false;
		}
		$print_data = $this->dbmodel->getEvnPLDispPrintData($data);
		if (!empty($print_data["Error_Msg"])) {
			$this->ReturnData(["success" => false, "Error_Msg" => toUtf($print_data["Error_Msg"])]);
		} else {
			$this->load->library("parser");
			$template = "evn_pl_disp_usluga_disp_dop_list_print_template";
			return $this->parser->parse($template, $print_data);
		}
		return true;
	}

	/**
	 * Отказ от любого типа диспансеризации или профосмотра
	 * @return bool
	 */
	function Refuse()
	{
		$data = $this->ProcessInputData("Refuse", true);
		if ($data === false) {
			return false;
		}
		$res = $this->dbmodel->Refuse($data);
		$this->ReturnData((empty($res) || !empty($res["Error_Msg"]))
			? ["success" => false, "Error_Msg" => $res["Error_Msg"], "data" => null]
			: ["success" => true, "Error_Msg" => "", "data" => $res["data"]]
		);
		return true;
	}
}