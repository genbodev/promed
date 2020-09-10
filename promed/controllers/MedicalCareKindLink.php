<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * swMedicalCareKindLinkViewWindow - контроллер для работы настройками кодов видов медицинской помощи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.11.2013
 *
 * @property MedicalCareKindLink_model dbmodel
 */

class MedicalCareKindLink extends swController {
	protected  $inputRules = array(
		'loadMedicalCareKindLinkGrid' => array(
			array(
				'field' => 'MedicalCareKindLink_id',
				'label' => 'Идентификатор настройки кодов видов медицинской помощи',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadMedicalCareKindLinkForm' => array(
			array(
				'field' => 'MedicalCareKindLink_id',
				'label' => 'Идентификатор настройки кодов видов медицинской помощи',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveMedicalCareKindLink' => array(
			array(
				'field' => 'MedicalCareKindLink_id',
				'label' => 'Идентификатор настройки кодов видов медицинской помощи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedicalCareKind_id',
				'label' => 'Вид медицинской помощи',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль отделения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnClass_id',
				'label' => 'Вид документа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Источник финансирования',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_id',
				'label' => 'Тип группы отделений',
				'rules' => '',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('MedicalCareKindLink_model', 'dbmodel');
	}

	/**
	 * Возвращает список настроек кодов видов медицинской помощи
	 * @return bool
	 */
	function loadMedicalCareKindLinkGrid()
	{
		$data = $this->ProcessInputData('loadMedicalCareKindLinkGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMedicalCareKindLinkGrid($data);
		$this->ProcessModelMultiList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные для формы настроек кодов видов медицинской помощи
	 * @return bool
	 */
	function loadMedicalCareKindLinkForm()
	{
		$data = $this->ProcessInputData('loadMedicalCareKindLinkForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMedicalCareKindLinkForm($data);
		$this->ProcessModelList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Сохраняет настройку кода вида медицинской помощи
	 * @return bool
	 */
	function saveMedicalCareKindLink()
	{
		$data = $this->ProcessInputData('saveMedicalCareKindLink', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveMedicalCareKindLink($data);
		$this->ProcessModelSave($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
}