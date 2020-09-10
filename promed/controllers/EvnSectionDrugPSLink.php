<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnSectionDrugPSLink - контроллер для работы с медикаментами/мероприятиями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package				Polka
 * @copyright			Copyright (c) 2017 Swan Ltd.
 * @link				http://swan.perm.ru/PromedWeb
 */
class EvnSectionDrugPSLink extends swController {
	public $inputRules = array(
		'deleteEvnSectionDrugPSLink' => array(
			array('field' => 'EvnSectionDrugPSLink_id', 'label' => 'Идентификатор медикамента/мероприятия', 'rules' => 'required', 'type' => 'id')
		),
		'loadEvnSectionDrugPSLinkGrid' => array(
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => 'required', 'type' => 'id')
		),
		'saveEvnSectionDrugPSLink' => array(
			array('field' => 'EvnSectionDrugPSLink_id', 'label' => 'Идентификатор медикамента/мероприятия', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DrugPS_id', 'label' => 'Медикамент/мероприятие', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugPSForm_id', 'label' => 'Форма', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnSectionDrugPSLink_Dose', 'label' => 'Дозировка (курсовая)', 'rules' => '', 'type' => 'float')
		),
		'loadDrugPSList' => array(
			array('field' => 'DrugPS_id', 'label' => 'Медикамент/мероприятие', 'rules' => '', 'type' => 'id'),
			array('field' => 'onDate', 'label' => 'Дата', 'rules' => '', 'type' => 'date'),
			array('field' => 'MesTariff_id', 'label' => 'Коэффициент', 'rules' => '', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnSectionDrugPSLink_model', 'dbmodel');
	}

	/**
	*  Удаление
	*/
	function deleteEvnSectionDrugPSLink() {
		$data = $this->ProcessInputData('deleteEvnSectionDrugPSLink', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteEvnSectionDrugPSLink($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления медикамента/мероприятия')->ReturnData();

		return true;
	}

	/**
	*  Получение списка
	*/
	function loadEvnSectionDrugPSLinkGrid() {
		$data = $this->ProcessInputData('loadEvnSectionDrugPSLinkGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnSectionDrugPSLinkGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	*  Получение списка
	*/
	function loadDrugPSList() {
		$data = $this->ProcessInputData('loadDrugPSList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadDrugPSList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	*  Сохранение
	*/
	function saveEvnSectionDrugPSLink() {
		$data = $this->ProcessInputData('saveEvnSectionDrugPSLink', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvnSectionDrugPSLink($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения медикамента/мероприятия')->ReturnData();
		return true;
	}
}
