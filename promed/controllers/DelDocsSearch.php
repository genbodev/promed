<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Контроллер для объекта поиска удаленных документов
 *
 * @package Common
 * @access public
 * @author Melentyev Anatoliy
 * @property DelDocsSearch_model $DelDocsSearch_model
 */

class DelDocsSearch extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = [
			"LoadDelDocs" => [
				["field" => "Lpu_id", "label" => "Медицинская организация", "rules" => "", "type" => "int"],
				["field" => "DocsType_id", "label" => "Тип документа", "rules" => "", "type" => "int"],
				["field" => "Person_FirName", "label" => "Тип документа", "rules" => "", "type" => "string"],
				["field" => "Person_SecName", "label" => "Тип документа", "rules" => "", "type" => "string"],
				["field" => "Person_SurName", "label" => "Тип документа", "rules" => "", "type" => "string"],
				["field" => "Person_BirthDay", "label" => "Дата рождения", "rules" => "", "type" => "date"],
				["field" => "CreateDocs_DateRange", "label" => "Период создания", "rules" => "", "type" => "daterange"],
				["field" => "DeleteDocs_DateRange", "label" => "Период удаления", "rules" => "", "type" => "daterange"],
				["field" => "page", "label" => "Страница", "rules" => "", "type" => "int"],
				["field" => "start", "label" => "Начало", "rules" => "", "type" => "int"],
				["field" => "limit", "label" => "Количество", "rules" => "", "type" => "int"],
			],
		];
		$this->load->database();
		$this->load->model("DelDocsSearch_model", "DelDocsSearch_model");
	}

	/**
	 * Получение списка удаленных документов
	 */
	function LoadDelDocs()
	{
		$data = $this->ProcessInputData("LoadDelDocs", true);
		if ($data === false) {
			return false;
		}
		$response = $this->DelDocsSearch_model->LoadDelDocs($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

}
