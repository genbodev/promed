<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirectionEco - контроллер для с работы с направлениями на ЭКО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Direction
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @author			Stanislav Bykov (savage@swan-it.ru)
 * @version			06.06.2019
 *
 * @property EvnDirectionEco_model dbmodel
 */

class EvnDirectionEco extends swController {
	protected  $inputRules = array(
		'getEvnDirectionEcoNumber' => array(
		
		),
		'loadEvnDirectionEcoEditForm' => array(
			array('field' => 'EvnDirectionEco_id', 'label' => 'Идентификатор направления на ЭКО', 'rules' => 'required', 'type' => 'id'),
		),
		'printEvnDirectionEco' => array(
			array('field' => 'EvnDirectionEco_id', 'label' => 'Идентификатор направления на ЭКО', 'rules' => 'required', 'type' => 'id'),
		),
		'saveEvnDirectionEco' => array(
			array('field' => 'EvnDirectionEco_id', 'label' => 'Идентификатор направления на ЭКО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор периодики человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'EvnDirectionEco_Num', 'label' => 'Номер направления', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnDirectionEco_setDate', 'label' => 'Дата выписки направления', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'KLRgnRF_id', 'label' => 'Регион направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Идентификатор МО направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDirectionEco_NumVKMZ', 'label' => 'Номер протокола ВК МЗ', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnDirectionEco_VKMZDate', 'label' => 'Дата заседания ВК МЗ', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnDirectionEco_CommentVKMZ', 'label' => 'Комментарий ВК МЗ', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnDirectionEco_GiveDate', 'label' => 'Дата выдачи', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnDirectionEco_Comment', 'label' => 'Комментарий к направлению', 'rules' => '', 'type' => 'string'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор направившей МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор направившего врача', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор направившего врача', 'rules' => '', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnDirectionEco_model', 'dbmodel');
	}

	/**
	 * Получение данных направления на ЭКО для редактирования
	 */
	function loadEvnDirectionEcoEditForm() {
		$data = $this->ProcessInputData('loadEvnDirectionEcoEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnDirectionEcoEditForm($data);

		$this->ProcessModelList($response)->ReturnData();
	}

	/**
	 * Сохранение направления на ЭКО
	 */
	function saveEvnDirectionEco() {
		$data = $this->ProcessInputData('saveEvnDirectionEco', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEvnDirectionEco($data);

		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Получение номера направления на ЭКО
	 * Входящие данные: нет
	 * На выходе: JSON-строка
	 * Используется: форма редактирования направления
	 */
	function getEvnDirectionEcoNumber() {
		$data = $this->ProcessInputData('getEvnDirectionEcoNumber', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnDirectionEcoNumber($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения номера направления')->ReturnData();

		return true;
	}

	/**
	 * Печать направления на ЭКО
	 * Входящие данные: $_GET['EvnDirectionEco_id']
	 */
	public function printEvnDirectionEco() {
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnDirectionEco', true);
		if ( $data === false ) { return false; }

		// Получаем данные по направлению
		$response = $this->dbmodel->getEvnDirectionEcoFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по направлению на ЭКО';
			return false;
		}

		$template = 'print_evn_direction_eco';

		$print_data = array(
			'Diag_Code' => returnValidHTMLString($response['Diag_Code']),
			'Document_begDate' => returnValidHTMLString($response['Document_begDate']),
			'Document_Num' => returnValidHTMLString($response['Document_Num']),
			'Document_Ser' => returnValidHTMLString($response['Document_Ser']),
			'DocumentType_Name' => returnValidHTMLString($response['DocumentType_Name']),
			'EvnDirectionEco_Num' => returnValidHTMLString($response['EvnDirectionEco_Num']),
			'EvnDirectionEco_setDate' => returnValidHTMLString($response['EvnDirectionEco_setDate']),
			'Person_Address' => str_replace('РОССИЯ, ', '', str_replace('КРАЙ ПЕРМСКИЙ, ', '', str_replace('ПЕРМСКИЙ КРАЙ, ', '', returnValidHTMLString($response['Person_Address'])))),
			'Person_Age' => returnValidHTMLString($response['Person_Age']),
			'Person_Birthday' => returnValidHTMLString($response['Person_Birthday']),
			'Person_Firname' => returnValidHTMLString($response['Person_Firname']),
			'Person_Secname' => returnValidHTMLString($response['Person_Secname']),
			'Person_Snils' => returnValidHTMLString($response['Person_Snils']),
			'Person_Surname' => returnValidHTMLString($response['Person_Surname']),
			'Polis_Num' => returnValidHTMLString($response['Polis_Num']),
			'Polis_Ser' => returnValidHTMLString($response['Polis_Ser']),
			'Lpu_Name' => returnValidHTMLString($response['Lpu_Name']),
		);

		return $this->parser->parse($template, $print_data);
	}
}