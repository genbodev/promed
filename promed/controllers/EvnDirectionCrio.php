<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirectionCrio - контроллер для с работы с направлениями на перенос эмбрионов
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
 * @property EvnDirectionCrio_model dbmodel
 */

class EvnDirectionCrio extends swController {
	protected  $inputRules = array(
		'getEvnDirectionCrioNumber' => array(
		
		),
		'loadEvnDirectionCrioEditForm' => array(
			array('field' => 'EvnDirectionCrio_id', 'label' => 'Идентификатор направления на перенос эмбриона', 'rules' => 'required', 'type' => 'id'),
		),
		'printEvnDirectionCrio' => array(
			array('field' => 'EvnDirectionCrio_id', 'label' => 'Идентификатор направления на перенос эмбриона', 'rules' => 'required', 'type' => 'id'),
		),
		'saveEvnDirectionCrio' => array(
			array('field' => 'EvnDirectionCrio_id', 'label' => 'Идентификатор направления на перенос эмбриона', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор периодики человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'EvnDirectionCrio_Num', 'label' => 'Номер направления', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnDirectionCrio_setDate', 'label' => 'Дата выписки направления', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'KLRgnRF_id', 'label' => 'Регион направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Идентификатор МО направления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDirectionCrio_NumVKMZ', 'label' => 'Номер протокола ВК МЗ', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnDirectionCrio_VKMZDate', 'label' => 'Дата заседания ВК МЗ', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnDirectionCrio_CommentVKMZ', 'label' => 'Комментарий ВК МЗ', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnDirectionCrio_GiveDate', 'label' => 'Дата выдачи', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnDirectionCrio_Comment', 'label' => 'Комментарий к направлению', 'rules' => '', 'type' => 'string'),
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
		$this->load->model('EvnDirectionCrio_model', 'dbmodel');
	}

	/**
	 * Получение данных направления на перенос эмбрионов для редактирования
	 */
	function loadEvnDirectionCrioEditForm() {
		$data = $this->ProcessInputData('loadEvnDirectionCrioEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnDirectionCrioEditForm($data);

		$this->ProcessModelList($response)->ReturnData();
	}

	/**
	 * Сохранение направления на перенос эмбрионов
	 */
	function saveEvnDirectionCrio() {
		$data = $this->ProcessInputData('saveEvnDirectionCrio', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEvnDirectionCrio($data);

		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Получение номера направления на перенос эмбрионов
	 * Входящие данные: нет
	 * На выходе: JSON-строка
	 * Используется: форма редактирования направления
	 */
	function getEvnDirectionCrioNumber() {
		$data = $this->ProcessInputData('getEvnDirectionCrioNumber', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnDirectionCrioNumber($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения номера направления')->ReturnData();

		return true;
	}

	/**
	 * Печать направления на перенос эмбрионов
	 * Входящие данные: $_GET['EvnDirectionCrio_id']
	 */
	public function printEvnDirectionCrio() {
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnDirectionCrio', true);
		if ( $data === false ) { return false; }

		// Получаем данные по направлению
		$response = $this->dbmodel->getEvnDirectionCrioFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по направлению на перенос эмбриона';
			return false;
		}

		$template = 'print_evn_direction_crio';

		$print_data = array(
			'Diag_Code' => returnValidHTMLString($response['Diag_Code']),
			'Document_begDate' => returnValidHTMLString($response['Document_begDate']),
			'Document_Num' => returnValidHTMLString($response['Document_Num']),
			'Document_Ser' => returnValidHTMLString($response['Document_Ser']),
			'DocumentType_Name' => returnValidHTMLString($response['DocumentType_Name']),
			'EvnDirectionCrio_Num' => returnValidHTMLString($response['EvnDirectionCrio_Num']),
			'EvnDirectionCrio_setDate' => returnValidHTMLString($response['EvnDirectionCrio_setDate']),
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