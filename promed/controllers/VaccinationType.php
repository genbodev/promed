<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Контроллер для объектов Виды профилактических прививок
 *
 * @package Common
 * @access public
 * @author Melentyev Anatoliy
 * @property VaccinationType_model $VaccinationType_model
 */

class VaccinationType extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = [
			"loadVaccinationTypes" => [
				["field" => "Vaccination_isNacCal", "label" => "Национальный календарь", "rules" => "", "type" => "int"],
				["field" => "Vaccination_isEpidemic", "label" => "Эпидемиологические показания", "rules" => "", "type" => "int"],
				["field" => "VaccinationType_DateRange", "label" => "Период", "rules" => "", "type" => "daterange"],
			],
			"saveVaccinationType" => [
				["field" => "VaccinationType_id", "label" => "Идентификатор вида вакцинации", "rules" => "", "type" => "id"],
				["field" => "VaccinationType_Code", "label" => "Код", "rules" => "", "type" => "string"],
				["field" => "VaccinationType_Name", "label" => "Название", "rules" => "required", "type" => "string"],
				["field" => "VaccinationType_isReaction", "label" => "Прививка / Реакция", "rules" => "required", "type" => "id"],
				["field" => "VaccinationType_begDate", "label" => "Дата начала", "rules" => "required", "type" => "date"],
				["field" => "VaccinationType_endDate", "label" => "Дата окончания", "rules" => "", "type" => "date"],
			],
			"deleteVaccinationType" => [
				["field" => "VaccinationType_id", "label" => "Идентификатор вида вакцинации", "rules" => "required", "type" => "int"]
			],
			"getVaccinationType" => [
				["field" => "VaccinationType_id", "label" => "Идентификатор вида вакцинации", "rules" => "required", "type" => "id"],
			],
			"getVaccination" => [
				["field" => "Vaccination_id", "label" => "Идентификатор прививки", "rules" => "required", "type" => "id"],
			],
			"saveVaccination" => [
				["field" => "Vaccination_id", "label" => "Идентификатор препарата", "rules" => "", "type" => "id"],
				["field" => "VaccinationType_id", "label" => "Идентификатор вида прививки", "rules" => "required", "type" => "id"],
				["field" => "Vaccination_Name", "label" => "Наименование прививки / реакции", "rules" => "required", "type" => "string"],
				["field" => "Vaccination_Code", "label" => "Код прививки / реакции", "rules" => "", "type" => "string"],
				["field" => "Vaccination_Nick", "label" => "Наименование в Ф063у", "rules" => "", "type" => "string"],
				["field" => "Vaccination_pid", "label" => "Идентификатор предыдущей прививки", "rules" => "", "type" => "id"],
				["field" => "Vaccination_isNacCal", "label" => "Признак отношения к национальному календарю", "rules" => "", "type" => "id"],
				["field" => "Vaccination_isEpidemic", "label" => "Признак проведения по эпидемиологическим показаниям", "rules" => "", "type" => "id"],
				["field" => "Vaccination_begAge", "label" => "Период с предыдущей прививки", "rules" => "", "type" => "int"],
				["field" => "VaccinationAgeType_bid", "label" => "Тип возраста", "rules" => "", "type" => "id"],
				["field" => "Vaccination_endAge", "label" => "Максимальный возраст в котором может быть проведена прививка", "rules" => "", "type" => "int"],
				["field" => "VaccinationAgeType_eid", "label" => "Тип возраста", "rules" => "", "type" => "id"],
				["field" => "VaccinationRiskGroupAccess_id", "label" => "Доступна рививки для групп риска", "rules" => "", "type" => "id"],
				["field" => "Vaccination_isSingle", "label" => "Не сочетается с другими прививками", "rules" => "", "type" => "id"],
				["field" => "Vaccination_isReactionLevel", "label" => "Имеет степень выраженности реакции", "rules" => "", "type" => "id"],
				["field" => "Vaccination_begDate", "label" => "Дата начала", "rules" => "required", "type" => "date"],
				["field" => "Vaccination_endDate", "label" => "Дата окончания", "rules" => "", "type" => "date"],
			],
			"deleteVaccination" => [
				["field" => "Vaccination_id", "label" => "Идентификатор прививки / реакции", "rules" => "required", "type" => "int"]
			],
			"setVaccinationRiskGroup" => [
				["field" => "VaccinationType_id", "label" => "Идентификатор вида прививки", "rules" => "required", "type" => "id"],
				["field" => "VaccinationRiskGroup_id", "label" => "Идентификатор осмотра", "rules" => "required", "type" => "id"],
				["field" => "VaccinationRiskGroupLink_id", "label" => "Идентификатор осмотра", "rules" => "", "type" => "id"],
				["field" => "VaccinationRiskGroupLink_checked", "label" => "Чекбокс осмотра", "rules" => "required", "type" => "id"],
			],
			"setVaccinationExam" => [
				["field" => "VaccinationType_id", "label" => "Идентификатор вида прививки", "rules" => "required", "type" => "id"],
				["field" => "VaccinationExamType_id", "label" => "Идентификатор осмотра", "rules" => "required", "type" => "id"],
				["field" => "VaccinationExamTypeLink_id", "label" => "Идентификатор осмотра", "rules" => "", "type" => "id"],
				["field" => "VaccinationExamTypeLink_checked", "label" => "Чекбокс осмотра", "rules" => "required", "type" => "id"],
			],
			"getVaccinationTypePrep" => [
				["field" => "VaccinationTypePrep_id", "label" => "Идентификатор препарата", "rules" => "required", "type" => "id"],
			],
			"loadVaccinationTypePrepComboList" => [
				["field" => "VaccinationType_isReaction", "label" => "Прививка / Реакция", "rules" => "required", "type" => "id"],
			],
			"saveVaccinationPrep" => [
				["field" => "VaccinationTypePrep_id", "label" => "Идентификатор препарата", "rules" => "", "type" => "id"],
				["field" => "VaccinationType_id", "label" => "Идентификатор вида прививки", "rules" => "required", "type" => "id"],
				["field" => "Prep_id", "label" => "Идентификатор", "rules" => "required", "type" => "id"],
				["field" => "VaccinationTypePrep_begDate", "label" => "Дата начала", "rules" => "required", "type" => "date"],
				["field" => "VaccinationTypePrep_endDate", "label" => "Дата окончания", "rules" => "", "type" => "date"],
			],
			"deletePrep" => [
				["field" => "VaccinationTypePrep_id", "label" => "Идентификатор препарата для вакцинации", "rules" => "required", "type" => "int"]
			],
		];
		$this->load->database();
		$this->load->model("VaccinationType_model", "VaccinationType_model");
	}

	/**
	 * Получение списка видов вакцинации
	 */
	function loadVaccinationTypes()
	{
		$data = $this->ProcessInputData("loadVaccinationTypes", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->loadVaccinationTypes($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение вида вакцинации
	 */
	function saveVaccinationType()
	{
		$data = $this->ProcessInputData("saveVaccinationType", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->saveVaccinationType($data);
		$this->ReturnData(array('VaccinationType_id' => $response[0]['VaccinationType_id'],'success' => true));
		return true;
	}

	/**
	 * Удаление вида вакцинации
	 */
	function deleteVaccinationType()
	{
		$data = $this->ProcessInputData("deleteVaccinationType", true, true);
		if ($data === false) {
			return false;
		}
		$this->VaccinationType_model->deleteVaccinationType($data);
		$this->ReturnData(["success" => true]);
		return true;
	}

	/**
	 * Получение списка прививок / реакций
	 */
	function loadVaccinationList()
	{
		$data = $this->ProcessInputData("getVaccinationType", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->loadVaccinationList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка предыдущих прививок для комбо
	 */
	function loadVaccinationPrevComboList()
	{
		$data = $this->ProcessInputData("getVaccinationType", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->loadVaccinationPrevComboList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка доступности прививок для групп риска
	 */
	function loadVaccinationRiskGroupAccessComboList()
	{
		$response = $this->VaccinationType_model->loadVaccinationRiskGroupAccessComboList();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение прививки / реакции
	 */
	function getVaccination()
	{
		$data = $this->ProcessInputData("getVaccination", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->getVaccination($data);
		$this->ReturnData( $response[0] );
		return true;
	}

	/**
	 * Сохранение прививки / реакции
	 */
	function saveVaccination()
	{
		$data = $this->ProcessInputData("saveVaccination", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->saveVaccination($data);
		$this->ReturnData(array('Vaccination_id' => $response[0]['Vaccination_id'],'success' => true));
		return true;
	}

	/**
	 * Удаление прививки / реакции
	 */
	function deleteVaccination()
	{
		$data = $this->ProcessInputData("deleteVaccination", true, true);
		if ($data === false) {
			return false;
		}
		$this->VaccinationType_model->deleteVaccination($data);
		$this->ReturnData(["success" => true]);
		return true;
	}

	/**
	 * Получение списка групп риска
	 */
	function loadVaccinationRiskGroupList()
	{
		$data = $this->ProcessInputData("getVaccinationType", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->loadVaccinationRiskGroupList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка групп риска для меню
	 */
	function loadVaccinationRiskGroupMenuList()
	{
		$data = $this->ProcessInputData("getVaccinationType", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->loadVaccinationRiskGroupMenuList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Добавление / удаление группы риска для вида вакцинации
	 * @throws Exception
	 */
	function setVaccinationRiskGroup()
	{
		$data = $this->ProcessInputData("setVaccinationRiskGroup", true, true);
		if ($data === false) {
			return false;
		}
		if($data['VaccinationRiskGroupLink_checked'] == '2')
			$this->VaccinationType_model->addVaccinationRiskGroup($data);
		else
			$this->VaccinationType_model->deleteVaccinationRiskGroup($data);

		$this->ReturnData(["success" => true]);
		return true;
	}

	/**
	 * Получение списка осмотров после вакцинации
	 */
	function loadVaccinationExamList()
	{
		$data = $this->ProcessInputData("getVaccinationType", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->loadVaccinationExamList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка осмотров после вакцинации для меню
	 */
	function loadVaccinationExamMenuList()
	{
		$data = $this->ProcessInputData("getVaccinationType", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->loadVaccinationExamMenuList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Добавление / удаление осмотра после вакцинации
	 * @throws Exception
	 */
	function setVaccinationExam()
	{
		$data = $this->ProcessInputData("setVaccinationExam", true, true);
		if ($data === false) {
			return false;
		}
		if($data['VaccinationExamTypeLink_checked'] == '2')
			$this->VaccinationType_model->addVaccinationExam($data);
		else
			$this->VaccinationType_model->deleteVaccinationExam($data);

		$this->ReturnData(["success" => true]);
		return true;
	}

	/**
	 * Получение списка препаратов для вакцинации
	 */
	function loadVaccinationPrepList()
	{
		$data = $this->ProcessInputData("getVaccinationType", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->loadVaccinationPrepList($data);

		if(!empty($response)) {
			foreach ($response as $key => $value) {
				$response[$key]['VaccinationTypePrep_FirmName'] = strip_tags($response[$key]['VaccinationTypePrep_FirmName']);
			}
		}

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение препарата
	 */
	function getVaccinationTypePrep()
	{
		$data = $this->ProcessInputData("getVaccinationTypePrep", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->getVaccinationTypePrep($data);
		$this->ReturnData( $response[0] );
		return true;
	}
	/**
	 * Получение списка препаратов для вакцинации для комбобокса
	 */
	function loadVaccinationTypePrepComboList()
	{
		$data = $this->ProcessInputData("loadVaccinationTypePrepComboList", true);
		if ($data === false) {
			return false;
		}
		$response = $this->VaccinationType_model->loadVaccinationTypePrepComboList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение препарата для вакцинации
	 * @throws Exception
	 */
	function saveVaccinationPrep()
	{
		$data = $this->ProcessInputData("saveVaccinationPrep", true);
		if ($data === false) {
			return false;
		}
		$this->VaccinationType_model->saveVaccinationPrep($data);
		$this->ReturnData(["success" => true]);
		return true;
	}

	/**
	 * Удаление препарата для вакцинации
	 */
	function deletePrep()
	{
		$data = $this->ProcessInputData("deletePrep", true, true);
		if ($data === false) {
			return false;
		}
		$this->VaccinationType_model->deleteVaccinationPrep($data);
		$this->ReturnData(["success" => true]);
		return true;
	}

}
